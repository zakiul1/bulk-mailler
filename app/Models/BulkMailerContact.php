<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkMailerContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulk_mailer_contact_list_id',
        'email',
        'first_name',
        'last_name',
        'status',
        'unsubscribed_at',
        'bounced_at',
        'suppression_reason',
        'notes',
    ];

    protected $casts = [
        'bulk_mailer_contact_list_id' => 'integer',
        'unsubscribed_at' => 'datetime',
        'bounced_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BulkMailerContactList::class, 'bulk_mailer_contact_list_id');
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(BulkMailerContactList::class, 'bulk_mailer_contact_list_id');
    }

    public function getNameAttribute(): string
    {
        $fullName = trim(implode(' ', array_filter([
            $this->first_name,
            $this->last_name,
        ])));

        return $fullName !== '' ? $fullName : (string) $this->email;
    }

    public function isSuppressed(): bool
    {
        return ! is_null($this->unsubscribed_at)
            || ! is_null($this->bounced_at)
            || filled($this->suppression_reason);
    }
}