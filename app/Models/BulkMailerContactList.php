<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BulkMailerContactList extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(BulkMailerContact::class, 'bulk_mailer_contact_list_id');
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(
            BulkMailerCampaign::class,
            'bulk_mailer_campaign_list_items',
            'bulk_mailer_contact_list_id',
            'bulk_mailer_campaign_id'
        )->withTimestamps();
    }
}