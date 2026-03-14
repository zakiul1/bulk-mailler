<?php

namespace App\Models;

use App\Enums\BulkMailerContactStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BulkMailerContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'status',
        'notes',
        'last_verified_at',
        'unsubscribed_at',
        'bounced_at',
        'suppression_reason',
    ];

    protected $casts = [
        'status' => BulkMailerContactStatus::class,
        'last_verified_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'bounced_at' => 'datetime',
    ];

    public function lists(): BelongsToMany
    {
        return $this->belongsToMany(
            BulkMailerContactList::class,
            'bulk_mailer_contact_list_items',
            'bulk_mailer_contact_id',
            'bulk_mailer_contact_list_id'
        )->withTimestamps();
    }

    public function verification(): HasOne
    {
        return $this->hasOne(BulkMailerEmailVerification::class, 'bulk_mailer_contact_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
    }

    public function getVerificationStatusAttribute(): string
    {
        return $this->verification?->status?->value
            ?? $this->verification?->status
            ?? 'pending';
    }

    public function getCanReceiveCampaignsAttribute(): bool
    {
        return $this->status?->value === 'active'
            && blank($this->unsubscribed_at)
            && blank($this->bounced_at)
            && $this->verification_status === 'valid';
    }
}