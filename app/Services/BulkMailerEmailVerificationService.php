<?php

namespace App\Services;

use App\Enums\BulkMailerVerificationStatus;
use App\Models\BulkMailerContact;
use App\Models\BulkMailerEmailVerification;

class BulkMailerEmailVerificationService
{
    public function verifyContact(BulkMailerContact $contact): BulkMailerEmailVerification
    {
        $email = strtolower(trim($contact->email));
        $status = BulkMailerVerificationStatus::Unknown;
        $reason = null;

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $status = BulkMailerVerificationStatus::Invalid;
            $reason = 'Invalid email syntax.';
        } else {
            $domain = substr(strrchr($email, '@'), 1) ?: '';

            if ($this->isRiskyRoleAddress($email)) {
                $status = BulkMailerVerificationStatus::Risky;
                $reason = 'Role-based email detected.';
            } elseif (blank($domain)) {
                $status = BulkMailerVerificationStatus::Invalid;
                $reason = 'Missing email domain.';
            } elseif (! $this->hasDnsRecords($domain)) {
                $status = BulkMailerVerificationStatus::Invalid;
                $reason = 'Domain has no MX or A record.';
            } else {
                $status = BulkMailerVerificationStatus::Valid;
                $reason = 'Email passed syntax and DNS checks.';
            }
        }

        $verification = BulkMailerEmailVerification::updateOrCreate(
            [
                'bulk_mailer_contact_id' => $contact->id,
            ],
            [
                'email' => $email,
                'status' => $status->value,
                'reason' => $reason,
                'checked_at' => now(),
            ]
        );

        $contact->update([
            'last_verified_at' => now(),
        ]);

        return $verification;
    }

    protected function isRiskyRoleAddress(string $email): bool
    {
        $localPart = strtolower(strstr($email, '@', true) ?: '');

        $roles = [
            'admin',
            'support',
            'info',
            'contact',
            'hello',
            'sales',
            'billing',
            'office',
            'team',
            'careers',
            'hr',
            'jobs',
            'noreply',
            'no-reply',
        ];

        return in_array($localPart, $roles, true);
    }

    protected function hasDnsRecords(string $domain): bool
    {
        return checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
    }
}