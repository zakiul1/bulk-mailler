<?php

namespace App\Services;

use App\Enums\BulkMailerContactStatus;
use App\Models\BulkMailerContact;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class BulkMailerContactCsvImportService
{
    public function import(UploadedFile $file, array $listIds = []): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if (! $handle) {
            return [
                'total' => 0,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'invalid' => 0,
            ];
        }

        $summary = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'invalid' => 0,
        ];

        $header = fgetcsv($handle);

        if (! $header) {
            fclose($handle);

            return $summary;
        }

        $header = collect($header)
            ->map(fn ($value) => strtolower(trim((string) $value)))
            ->values()
            ->all();

        DB::transaction(function () use ($handle, $header, $listIds, &$summary) {
            while (($row = fgetcsv($handle)) !== false) {
                $summary['total']++;

                $rowData = $this->mapRow($header, $row);

                $email = strtolower(trim((string) ($rowData['email'] ?? '')));

                if (blank($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $summary['invalid']++;
                    continue;
                }

                $status = strtolower(trim((string) ($rowData['status'] ?? 'active')));
                $allowedStatuses = ['active', 'unsubscribed', 'bounced', 'suppressed'];

                if (! in_array($status, $allowedStatuses, true)) {
                    $status = BulkMailerContactStatus::Active->value;
                }

                $existing = BulkMailerContact::where('email', $email)->first();

                $payload = [
                    'email' => $email,
                    'first_name' => $this->nullableString($rowData['first_name'] ?? null),
                    'last_name' => $this->nullableString($rowData['last_name'] ?? null),
                    'status' => $status,
                    'notes' => $this->nullableString($rowData['notes'] ?? null),
                    'unsubscribed_at' => $status === 'unsubscribed' ? now() : null,
                    'bounced_at' => in_array($status, ['bounced', 'suppressed'], true) ? now() : null,
                ];

                if ($existing) {
                    $existing->update($payload);
                    $contact = $existing;
                    $summary['updated']++;
                } else {
                    $contact = BulkMailerContact::create($payload);
                    $summary['created']++;
                }

                if (! empty($listIds)) {
                    $contact->lists()->syncWithoutDetaching($listIds);
                }
            }
        });

        fclose($handle);

        return $summary;
    }

    protected function mapRow(array $header, array $row): array
    {
        $mapped = [];

        foreach ($header as $index => $column) {
            $mapped[$column] = $row[$index] ?? null;
        }

        return $mapped;
    }

    protected function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}