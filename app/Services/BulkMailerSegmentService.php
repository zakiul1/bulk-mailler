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

        if (($rules['only_active'] ?? true) === true) {
            $query->where('status', 'active');
        }

        if (($rules['require_verified_valid'] ?? true) === true) {
            $query->whereHas('verification', function ($verificationQuery) {
                $verificationQuery->where('status', 'valid');
            });
        }

        if (($rules['exclude_unsubscribed'] ?? true) === true) {
            $query->whereNull('unsubscribed_at');
        }

        if (($rules['exclude_bounced'] ?? true) === true) {
            $query->whereNull('bounced_at');
        }

        if (! empty($rules['list_ids']) && is_array($rules['list_ids'])) {
            $listIds = array_map('intval', $rules['list_ids']);

            $query->whereHas('lists', function ($listQuery) use ($listIds) {
                $listQuery->whereIn('bulk_mailer_contact_lists.id', $listIds);
            });
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