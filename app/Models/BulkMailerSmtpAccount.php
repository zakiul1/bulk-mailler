<?php

namespace App\Models;

use App\Enums\BulkMailerSmtpHealthStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class BulkMailerSmtpAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'from_name',
        'from_email',
        'reply_to_email',
        'daily_limit',
        'priority',
        'is_active',
        'health_status',
        'notes',
        'failure_count',
        'consecutive_failures',
        'last_failed_at',
        'cooldown_until',
        'last_success_at',
        'auto_disabled_at',
        'auto_disabled_reason',
    ];

    protected $casts = [
        'port' => 'integer',
        'daily_limit' => 'integer',
        'priority' => 'integer',
        'is_active' => 'boolean',
        'health_status' => BulkMailerSmtpHealthStatus::class,
        'failure_count' => 'integer',
        'consecutive_failures' => 'integer',
        'last_failed_at' => 'datetime',
        'cooldown_until' => 'datetime',
        'last_success_at' => 'datetime',
        'auto_disabled_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
    ];

    public function setPasswordAttribute(?string $value): void
    {
        $this->attributes['password'] = blank($value) ? null : Crypt::encryptString($value);
    }

    public function getDecryptedPasswordAttribute(): ?string
    {
        if (blank($this->password)) {
            return null;
        }

        return Crypt::decryptString($this->password);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            BulkMailerTag::class,
            'bulk_mailer_smtp_account_tag',
            'bulk_mailer_smtp_account_id',
            'bulk_mailer_tag_id'
        );
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            BulkMailerSmtpGroup::class,
            'bulk_mailer_smtp_group_items',
            'bulk_mailer_smtp_account_id',
            'bulk_mailer_smtp_group_id'
        )->withTimestamps();
    }

    public function dailyUsages(): HasMany
    {
        return $this->hasMany(BulkMailerSmtpDailyUsage::class, 'bulk_mailer_smtp_account_id');
    }

    public function getSentTodayAttribute(): int
    {
        return (int) ($this->dailyUsages()
            ->whereDate('usage_date', now()->toDateString())
            ->value('emails_sent') ?? 0);
    }

    public function getRemainingTodayAttribute(): int
    {
        return max(0, $this->daily_limit - $this->sent_today);
    }

    public function isCoolingDown(): bool
    {
        return $this->cooldown_until !== null && now()->lt($this->cooldown_until);
    }

    public function isAutoDisabled(): bool
    {
        return $this->auto_disabled_at !== null;
    }

    public function operationalStatus(): string
    {
        if ($this->isAutoDisabled()) {
            return 'auto_disabled';
        }

        if (! $this->is_active) {
            return 'inactive';
        }

        if ($this->isCoolingDown()) {
            return 'cooldown';
        }

        return 'ready';
    }

    public function isHealthyForSending(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->isAutoDisabled()) {
            return false;
        }

        if ($this->remaining_today <= 0) {
            return false;
        }

        if ($this->isCoolingDown()) {
            return false;
        }

        if ($this->health_status instanceof BulkMailerSmtpHealthStatus) {
            return in_array($this->health_status->value, ['healthy', 'warning'], true);
        }

        return in_array((string) $this->health_status, ['healthy', 'warning', ''], true);
    }

    public function isAvailable(): bool
    {
        return $this->isHealthyForSending();
    }
}