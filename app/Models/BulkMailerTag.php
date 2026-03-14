<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BulkMailerTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'color',
    ];

    public function smtpAccounts(): BelongsToMany
    {
        return $this->belongsToMany(
            BulkMailerSmtpAccount::class,
            'bulk_mailer_smtp_account_tag',
            'bulk_mailer_tag_id',
            'bulk_mailer_smtp_account_id'
        );
    }
}