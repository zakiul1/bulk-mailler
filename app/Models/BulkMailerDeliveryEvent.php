<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkMailerDeliveryEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulk_mailer_campaign_id',
        'bulk_mailer_contact_id',
        'bulk_mailer_campaign_recipient_id',
        'email',
        'event_type',
        'provider',
        'provider_event_id',
        'message',
        'payload',
        'event_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'event_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(BulkMailerCampaign::class, 'bulk_mailer_campaign_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(BulkMailerContact::class, 'bulk_mailer_contact_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(BulkMailerCampaignRecipient::class, 'bulk_mailer_campaign_recipient_id');
    }
}