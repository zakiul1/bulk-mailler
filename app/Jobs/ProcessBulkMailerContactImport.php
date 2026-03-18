<?php

namespace App\Jobs;

use App\Models\BulkMailerContact;
use App\Models\BulkMailerContactImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProcessBulkMailerContactImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    protected int $chunkSize = 500;

    public function __construct(public int $importId)
    {
    }

    public function handle(): void
    {
        $import = BulkMailerContactImport::find($this->importId);

        if (!$import) {
            return;
        }

        $import->update([
            'status' => 'processing',
            'error_message' => null,
            'completed_at' => null,
        ]);

        try {
            if (blank($import->stored_file_path)) {
                throw new \RuntimeException('Import source file path is missing.');
            }

            if (!Storage::exists($import->stored_file_path)) {
                throw new \RuntimeException('Import source file not found: ' . $import->stored_file_path);
            }

            $stats = [
                'total_read' => 0,
                'processed_count' => 0,
                'valid_count' => 0,
                'invalid_count' => 0,
                'duplicate_count' => 0,
                'inserted_count' => 0,
            ];

            $seenInImport = [];

            if ($import->source_type === 'file') {
                $this->processFileInChunks($import, $stats, $seenInImport);
            } elseif ($import->source_type === 'text') {
                $this->processTextInChunks($import, $stats, $seenInImport);
            } else {
                throw new \RuntimeException('Unsupported import source type: ' . $import->source_type);
            }

            $import->update([
                'status' => 'completed',
                'total_read' => $stats['total_read'],
                'processed_count' => $stats['processed_count'],
                'valid_count' => $stats['valid_count'],
                'invalid_count' => $stats['invalid_count'],
                'duplicate_count' => $stats['duplicate_count'],
                'inserted_count' => $stats['inserted_count'],
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $import->update([
                'status' => 'failed',
                'error_message' => mb_substr($e->getMessage(), 0, 2000),
            ]);

            throw $e;
        }
    }

    protected function processFileInChunks(BulkMailerContactImport $import, array &$stats, array &$seenInImport): void
    {
        $path = Storage::path($import->stored_file_path);
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \RuntimeException('Unable to open import file: ' . $path);
        }

        $chunk = [];

        try {
            while (($line = fgets($handle)) !== false) {
                $columns = str_getcsv($line);

                foreach ($columns as $column) {
                    $parts = preg_split('/[\s,;]+/', (string) $column) ?: [];

                    foreach ($parts as $part) {
                        $part = trim($part);

                        if ($part === '') {
                            continue;
                        }

                        $stats['total_read']++;
                        $chunk[] = $part;

                        if (count($chunk) >= $this->chunkSize) {
                            $this->processChunk($import, $chunk, $stats, $seenInImport);
                            $chunk = [];
                            $this->persistProgress($import, $stats);
                        }
                    }
                }
            }

            if (!empty($chunk)) {
                $this->processChunk($import, $chunk, $stats, $seenInImport);
                $this->persistProgress($import, $stats);
            }
        } finally {
            fclose($handle);
        }
    }

    protected function processTextInChunks(BulkMailerContactImport $import, array &$stats, array &$seenInImport): void
    {
        $content = Storage::get($import->stored_file_path) ?: '';
        $parts = preg_split('/[\s,;]+/', $content) ?: [];
        $chunk = [];

        foreach ($parts as $part) {
            $part = trim((string) $part);

            if ($part === '') {
                continue;
            }

            $stats['total_read']++;
            $chunk[] = $part;

            if (count($chunk) >= $this->chunkSize) {
                $this->processChunk($import, $chunk, $stats, $seenInImport);
                $chunk = [];
                $this->persistProgress($import, $stats);
            }
        }

        if (!empty($chunk)) {
            $this->processChunk($import, $chunk, $stats, $seenInImport);
            $this->persistProgress($import, $stats);
        }
    }

    protected function processChunk(
        BulkMailerContactImport $import,
        array $rawEmails,
        array &$stats,
        array &$seenInImport
    ): void {
        $normalizedValidEmails = [];

        foreach ($rawEmails as $rawEmail) {
            $normalized = $this->normalizeEmail($rawEmail);

            if ($normalized === null) {
                continue;
            }

            $stats['processed_count']++;

            if (isset($seenInImport[$normalized])) {
                $stats['duplicate_count']++;
                continue;
            }

            $seenInImport[$normalized] = true;

            if (!$this->isValidEmail($normalized)) {
                $stats['invalid_count']++;
                continue;
            }

            $stats['valid_count']++;
            $normalizedValidEmails[] = $normalized;
        }

        if (empty($normalizedValidEmails)) {
            return;
        }

        $inserted = $this->insertChunk(
            listId: (int) $import->bulk_mailer_contact_list_id,
            emails: $normalizedValidEmails,
            duplicateCounter: $stats['duplicate_count']
        );

        $stats['inserted_count'] += $inserted;
    }

    protected function persistProgress(BulkMailerContactImport $import, array $stats): void
    {
        $import->update([
            'total_read' => $stats['total_read'],
            'processed_count' => $stats['processed_count'],
            'valid_count' => $stats['valid_count'],
            'invalid_count' => $stats['invalid_count'],
            'duplicate_count' => $stats['duplicate_count'],
            'inserted_count' => $stats['inserted_count'],
        ]);
    }

    protected function normalizeEmail(string $email): ?string
    {
        $email = trim(strtolower($email));
        $email = trim($email, "\"'()[]<>");

        return $email === '' ? null : $email;
    }

    protected function isValidEmail(string $email): bool
    {
        return Validator::make(
            ['email' => $email],
            ['email' => ['required', 'email:rfc', 'max:255']]
        )->passes();
    }

    protected function insertChunk(int $listId, array $emails, int &$duplicateCounter): int
    {
        $existingEmails = BulkMailerContact::query()
            ->where('bulk_mailer_contact_list_id', $listId)
            ->whereIn('email', $emails)
            ->pluck('email')
            ->all();

        $existingLookup = array_fill_keys($existingEmails, true);

        $now = now();
        $rows = [];

        foreach ($emails as $email) {
            if (isset($existingLookup[$email])) {
                $duplicateCounter++;
                continue;
            }

            $rows[] = [
                'bulk_mailer_contact_list_id' => $listId,
                'email' => $email,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (empty($rows)) {
            return 0;
        }

        DB::table('bulk_mailer_contacts')->insert($rows);

        return count($rows);
    }
}