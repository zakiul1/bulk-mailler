<?php

namespace App\Services;

use App\Enums\BulkMailerSmtpHealthStatus;
use App\Models\BulkMailerSmtpAccount;

class BulkMailerSmtpHealthService
{
    public function markFailure(BulkMailerSmtpAccount $smtp, ?string $message = null): void
    {
        $smtp->refresh();

        $consecutiveFailures = $smtp->consecutive_failures + 1;
        $failureCount = $smtp->failure_count + 1;

        $cooldownMinutes = $this->resolveCooldownMinutes($consecutiveFailures);
        $healthStatus = $this->resolveHealthStatus($consecutiveFailures);

        $payload = [
            'failure_count' => $failureCount,
            'consecutive_failures' => $consecutiveFailures,
            'last_failed_at' => now(),
            'cooldown_until' => $cooldownMinutes > 0 ? now()->addMinutes($cooldownMinutes) : null,
            'health_status' => $healthStatus,
        ];

        $autoDisableThreshold = (int) config('bulk_mailer.smtp_health.auto_disable_after_consecutive_failures', 10);

        if ($consecutiveFailures >= $autoDisableThreshold) {
            $payload['auto_disabled_at'] = now();
            $payload['auto_disabled_reason'] = $message ?: 'Auto disabled after repeated SMTP failures.';
            $payload['is_active'] = false;
            $payload['health_status'] = BulkMailerSmtpHealthStatus::Critical;
        }

        $smtp->update($payload);
    }

    public function markSuccess(BulkMailerSmtpAccount $smtp): void
    {
        $smtp->update([
            'consecutive_failures' => 0,
            'cooldown_until' => null,
            'last_success_at' => now(),
            'health_status' => BulkMailerSmtpHealthStatus::Healthy,
        ]);
    }

    public function reset(BulkMailerSmtpAccount $smtp): void
    {
        $smtp->update([
            'failure_count' => 0,
            'consecutive_failures' => 0,
            'last_failed_at' => null,
            'cooldown_until' => null,
            'last_success_at' => null,
            'auto_disabled_at' => null,
            'auto_disabled_reason' => null,
            'health_status' => BulkMailerSmtpHealthStatus::Healthy,
        ]);
    }

    public function reEnable(BulkMailerSmtpAccount $smtp): void
    {
        $smtp->update([
            'is_active' => true,
            'failure_count' => 0,
            'consecutive_failures' => 0,
            'last_failed_at' => null,
            'cooldown_until' => null,
            'auto_disabled_at' => null,
            'auto_disabled_reason' => null,
            'health_status' => BulkMailerSmtpHealthStatus::Healthy,
        ]);
    }

    protected function resolveCooldownMinutes(int $consecutiveFailures): int
    {
        $rules = collect(config('bulk_mailer.smtp_health.cooldown_rules', []))
            ->sortBy('failures')
            ->values();

        $selectedMinutes = 0;

        foreach ($rules as $rule) {
            if ($consecutiveFailures >= (int) ($rule['failures'] ?? 0)) {
                $selectedMinutes = (int) ($rule['minutes'] ?? 0);
            }
        }

        return $selectedMinutes;
    }

    protected function resolveHealthStatus(int $consecutiveFailures): BulkMailerSmtpHealthStatus
    {
        return match (true) {
            $consecutiveFailures >= 7 => BulkMailerSmtpHealthStatus::Critical,
            $consecutiveFailures >= 3 => BulkMailerSmtpHealthStatus::Warning,
            default => BulkMailerSmtpHealthStatus::Healthy,
        };
    }
}