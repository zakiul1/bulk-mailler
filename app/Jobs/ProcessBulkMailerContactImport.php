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

    public function __construct(public int $importId)
    {
    }

    public function handle(): void
    {
        $import = BulkMailerContactImport::find($this->importId);

        if (! $import) {
            return;
        }

        $import->update([
            'status' => 'processing',
            'error_message' => null,
        ]);

        try {
            $emails = $this->loadEmails($import);

            $import->update([
                'total_read' => count($emails),
            ]);

            $stats = [
                'processed_count' => 0,
                'valid_count' => 0,
                'invalid_count' => 0,
                'duplicate_count' => 0,
                'inserted_count' => 0,
            ];

            $seenInImport = [];

            foreach (array_chunk($emails, 1000) as $chunk) {
                $normalizedValidEmails = [];

                foreach ($chunk as $rawEmail) {
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

                    if (! $this->isValidEmail($normalized)) {
                        $stats['invalid_count']++;
                        continue;
                    }

                    $stats['valid_count']++;
                    $normalizedValidEmails[] = $normalized;
                }

                if (! empty($normalizedValidEmails)) {
                    $inserted = $this->insertChunk(
                        categoryId: $import->bulk_mailer_contact_list_id,
                        emails: $normalizedValidEmails,
                        duplicateCounter: $stats['duplicate_count']
                    );

                    $stats['inserted_count'] += $inserted;
                }

                $import->update([
                    'processed_count' => $stats['processed_count'],
                    'valid_count' => $stats['valid_count'],
                    'invalid_count' => $stats['invalid_count'],
                    'duplicate_count' => $stats['duplicate_count'],
                    'inserted_count' => $stats['inserted_count'],
                ]);
            }

            $import->update([
                'status' => 'completed',
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

    protected function loadEmails(BulkMailerContactImport $import): array
    {
        if (blank($import->stored_file_path)) {
            return [];
        }

        if (! Storage::exists($import->stored_file_path)) {
            throw new \RuntimeException('Import source file not found: ' . $import->stored_file_path);
        }

        if ($import->source_type === 'file') {
            return $this->extractEmailsFromFile(Storage::path($import->stored_file_path));
        }

        if ($import->source_type === 'text') {
            $content = Storage::get($import->stored_file_path);

            return $this->extractEmailsFromText($content ?: '');
        }

        return [];
    }

    protected function extractEmailsFromText(string $text): array
    {
        $parts = preg_split('/[\s,;]+/', $text) ?: [];

        return array_values(array_filter($parts, fn ($value) => filled(trim($value))));
    }

    protected function extractEmailsFromFile(string $path): array
    {
        $emails = [];

        $handle = fopen($path, 'r');

        if ($handle === false) {
            return $emails;
        }

        try {
            while (($line = fgets($handle)) !== false) {
                $columns = str_getcsv($line);

                foreach ($columns as $column) {
                    $parts = preg_split('/[\s,;]+/', (string) $column) ?: [];

                    foreach ($parts as $part) {
                        $part = trim($part);

                        if ($part !== '') {
                            $emails[] = $part;
                        }
                    }
                }
            }
        } finally {
            fclose($handle);
        }

        return $emails;
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

    protected function insertChunk(int $categoryId, array $emails, int &$duplicateCounter): int
    {
        $existingEmails = BulkMailerContact::query()
            ->where('bulk_mailer_contact_list_id', $categoryId)
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
                'bulk_mailer_contact_list_id' => $categoryId,
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