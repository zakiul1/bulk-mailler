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
            return $this->emptySummary();
        }

        $summary = $this->emptySummary();

        $header = fgetcsv($handle);

        if (! $header) {
            fclose($handle);

            return $summary;
        }

        $header = collect($header)
            ->map(fn ($value) => strtolower(trim((string) $value)))
            ->values()
            ->all();

        $normalizedListIds = collect($listIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        DB::transaction(function () use ($handle, $header, $normalizedListIds, &$summary) {
            while (($row = fgetcsv($handle)) !== false) {
                $summary['total']++;

                $rowData = $this->mapRow($header, $row);

                $email = strtolower(trim((string) ($rowData['email'] ?? '')));

                if (blank($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $summary['invalid']++;
                    continue;
                }

                $status = $this->normalizeStatus($rowData['status'] ?? null);

                $payload = [
                    'email' => $email,
                    'first_name' => $this->nullableString($rowData['first_name'] ?? null),
                    'last_name' => $this->nullableString($rowData['last_name'] ?? null),
                    'status' => $status,
                    'notes' => $this->nullableString($rowData['notes'] ?? null),
                    'unsubscribed_at' => $status === BulkMailerContactStatus::Unsubscribed->value ? now() : null,
                    'bounced_at' => in_array($status, [
                        BulkMailerContactStatus::Bounced->value,
                        BulkMailerContactStatus::Suppressed->value,
                    ], true) ? now() : null,
                    'suppression_reason' => $this->resolveSuppressionReason($status, $rowData),
                ];

                if (empty($normalizedListIds)) {
                    $this->upsertContactForList(null, $payload, $summary);
                    continue;
                }

                foreach ($normalizedListIds as $listId) {
                    $this->upsertContactForList($listId, $payload, $summary);
                }
            }
        });

        fclose($handle);

        return $summary;
    }

    protected function upsertContactForList(?int $listId, array $payload, array &$summary): void
    {
        $query = BulkMailerContact::query()
            ->where('email', $payload['email']);

        if ($listId) {
            $query->where('bulk_mailer_contact_list_id', $listId);
        } else {
            $query->whereNull('bulk_mailer_contact_list_id');
        }

        $existing = $query->first();

        $attributes = array_merge($payload, [
            'bulk_mailer_contact_list_id' => $listId,
        ]);

        if ($existing) {
            $existing->update($attributes);
            $summary['updated']++;

            return;
        }

        BulkMailerContact::create($attributes);
        $summary['created']++;
    }

    protected function normalizeStatus(mixed $value): string
    {
        $status = strtolower(trim((string) $value));

        $allowedStatuses = [
            BulkMailerContactStatus::Active->value,
            BulkMailerContactStatus::Unsubscribed->value,
            BulkMailerContactStatus::Bounced->value,
            BulkMailerContactStatus::Suppressed->value,
        ];

        if (! in_array($status, $allowedStatuses, true)) {
            return BulkMailerContactStatus::Active->value;
        }

        return $status;
    }

    protected function resolveSuppressionReason(string $status, array $rowData): ?string
    {
        $csvReason = $this->nullableString($rowData['suppression_reason'] ?? null);

        if ($csvReason) {
            return $csvReason;
        }

        return match ($status) {
            BulkMailerContactStatus::Unsubscribed->value => 'Imported as unsubscribed',
            BulkMailerContactStatus::Bounced->value => 'Imported as bounced',
            BulkMailerContactStatus::Suppressed->value => 'Imported as suppressed',
            default => null,
        };
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

    protected function emptySummary(): array
    {
        return [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'invalid' => 0,
        ];
    }
}