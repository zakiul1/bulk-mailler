<?php

namespace App\Models;

use App\Enums\BulkMailerCampaignStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BulkMailerCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subject',
        'subject_a',
        'subject_b',
        'ab_testing_enabled',
        'status',
        'bulk_mailer_template_id',
        'bulk_mailer_segment_id',
        'scheduled_at',
        'started_at',
        'completed_at',
        'total_recipients',
        'sent_count',
        'failed_count',
        'created_by',
    ];

    protected $casts = [
        'ab_testing_enabled' => 'boolean',
        'status' => BulkMailerCampaignStatus::class,
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
        'created_by' => 'integer',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(BulkMailerTemplate::class, 'bulk_mailer_template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function segment(): BelongsTo
    {
        return $this->belongsTo(BulkMailerSegment::class, 'bulk_mailer_segment_id');
    }

    public function lists(): BelongsToMany
    {
        return $this->belongsToMany(
            BulkMailerContactList::class,
            'bulk_mailer_campaign_list_items',
            'bulk_mailer_campaign_id',
            'bulk_mailer_contact_list_id'
        )->withTimestamps();
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(BulkMailerCampaignRecipient::class, 'bulk_mailer_campaign_id');
    }
}