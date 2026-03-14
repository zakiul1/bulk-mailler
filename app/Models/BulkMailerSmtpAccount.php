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
    ];

    protected $casts = [
        'port' => 'integer',
        'daily_limit' => 'integer',
        'priority' => 'integer',
        'is_active' => 'boolean',
        'health_status' => BulkMailerSmtpHealthStatus::class,
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

    public function dailyUsages(): HasMany
    {
        return $this->hasMany(BulkMailerSmtpDailyUsage::class, 'bulk_mailer_smtp_account_id');
    }

    public function getSentTodayAttribute(): int
    {
        return (int) $this->dailyUsages()
            ->whereDate('usage_date', now()->toDateString())
            ->value('emails_sent');
    }

    public function getRemainingTodayAttribute(): int
    {
        return max(0, $this->daily_limit - $this->sent_today);
    }

    public function isAvailable(): bool
    {
        return $this->is_active && $this->remaining_today > 0;
    }
}