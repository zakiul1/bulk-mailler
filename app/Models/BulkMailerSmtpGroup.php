<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BulkMailerSmtpGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'rotation_mode',
        'last_used_smtp_account_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function smtpAccounts(): BelongsToMany
    {
        return $this->belongsToMany(
            BulkMailerSmtpAccount::class,
            'bulk_mailer_smtp_group_items',
            'bulk_mailer_smtp_group_id',
            'bulk_mailer_smtp_account_id'
        )->withTimestamps();
    }

    public function lastUsedSmtpAccount(): BelongsTo
    {
        return $this->belongsTo(BulkMailerSmtpAccount::class, 'last_used_smtp_account_id');
    }
}