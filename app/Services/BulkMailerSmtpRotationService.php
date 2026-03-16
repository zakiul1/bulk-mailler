<?php

namespace App\Services;

use App\Models\BulkMailerCampaign;
use App\Models\BulkMailerSmtpAccount;
use App\Models\BulkMailerSmtpGroup;
use Illuminate\Support\Collection;

class BulkMailerSmtpRotationService
{
    public function resolveForCampaign(BulkMailerCampaign $campaign): ?BulkMailerSmtpAccount
    {
        if (! $campaign->smtpGroup || ! $campaign->smtpGroup->is_active) {
            return null;
        }

        return $this->resolveForGroup($campaign->smtpGroup->fresh('smtpAccounts'));
    }

    public function resolveForGroup(BulkMailerSmtpGroup $group): ?BulkMailerSmtpAccount
    {
        $available = $group->smtpAccounts
            ->filter(fn (BulkMailerSmtpAccount $smtp) => $smtp->isHealthyForSending())
            ->values();

        if ($available->isEmpty()) {
            return null;
        }

        return match ($group->rotation_mode) {
            'random' => $this->resolveRandom($available),
            'round_robin' => $this->resolveRoundRobin($group, $available),
            'least_used' => $this->resolveLeastUsed($available),
            default => $this->resolveRandom($available),
        };
    }

    protected function resolveRandom(Collection $available): ?BulkMailerSmtpAccount
    {
        return $available->shuffle()->first();
    }

    protected function resolveLeastUsed(Collection $available): ?BulkMailerSmtpAccount
    {
        return $available
            ->sortBy([
                fn (BulkMailerSmtpAccount $smtp) => $smtp->sent_today,
                fn (BulkMailerSmtpAccount $smtp) => $smtp->id,
            ])
            ->first();
    }

    protected function resolveRoundRobin(BulkMailerSmtpGroup $group, Collection $available): ?BulkMailerSmtpAccount
    {
        $ordered = $available->sortBy('id')->values();

        if (blank($group->last_used_smtp_account_id)) {
            $selected = $ordered->first();
            $this->rememberLastUsed($group, $selected);

            return $selected;
        }

        $currentIndex = $ordered->search(function (BulkMailerSmtpAccount $smtp) use ($group) {
            return $smtp->id === $group->last_used_smtp_account_id;
        });

        if ($currentIndex === false) {
            $selected = $ordered->first();
            $this->rememberLastUsed($group, $selected);

            return $selected;
        }

        $nextIndex = $currentIndex + 1;

        if ($nextIndex >= $ordered->count()) {
            $nextIndex = 0;
        }

        $selected = $ordered->get($nextIndex);
        $this->rememberLastUsed($group, $selected);

        return $selected;
    }

    protected function rememberLastUsed(BulkMailerSmtpGroup $group, ?BulkMailerSmtpAccount $smtp): void
    {
        if (! $smtp) {
            return;
        }

        $group->update([
            'last_used_smtp_account_id' => $smtp->id,
        ]);
    }
}