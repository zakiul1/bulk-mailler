<?php

namespace App\Services;

use App\Models\BulkMailerContact;
use App\Models\BulkMailerSegment;
use Illuminate\Database\Eloquent\Builder;

class BulkMailerSegmentService
{
    public function applySegment(Builder $query, ?BulkMailerSegment $segment): Builder
    {
        if (! $segment || ! $segment->is_active) {
            return $query;
        }

        $rules = $segment->rules ?? [];

        if (! empty($rules['list_ids']) && is_array($rules['list_ids'])) {
            $listIds = array_map('intval', $rules['list_ids']);

            $query->whereIn('bulk_mailer_contact_list_id', $listIds);
        }

        if (! empty($rules['emails']) && is_array($rules['emails'])) {
            $emails = collect($rules['emails'])
                ->map(fn ($email) => strtolower(trim((string) $email)))
                ->filter()
                ->values()
                ->all();

            if (! empty($emails)) {
                $query->whereIn('email', $emails);
            }
        }

        if (! empty($rules['exclude_emails']) && is_array($rules['exclude_emails'])) {
            $excludedEmails = collect($rules['exclude_emails'])
                ->map(fn ($email) => strtolower(trim((string) $email)))
                ->filter()
                ->values()
                ->all();

            if (! empty($excludedEmails)) {
                $query->whereNotIn('email', $excludedEmails);
            }
        }

        return $query;
    }

    public function countForSegment(?BulkMailerSegment $segment): int
    {
        return $this->applySegment(BulkMailerContact::query(), $segment)
            ->distinct('bulk_mailer_contacts.id')
            ->count('bulk_mailer_contacts.id');
    }
}