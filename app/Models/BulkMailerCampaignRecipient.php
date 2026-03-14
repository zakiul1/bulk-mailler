<?php

namespace App\Models;

use App\Enums\BulkMailerCampaignRecipientStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkMailerCampaignRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulk_mailer_campaign_id',
        'bulk_mailer_contact_id',
        'bulk_mailer_smtp_account_id',
        'email',
        'subject_variant',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'status' => BulkMailerCampaignRecipientStatus::class,
        'sent_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(BulkMailerCampaign::class, 'bulk_mailer_campaign_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(BulkMailerContact::class, 'bulk_mailer_contact_id');
    }

    public function smtpAccount(): BelongsTo
    {
        return $this->belongsTo(BulkMailerSmtpAccount::class, 'bulk_mailer_smtp_account_id');
    }
}