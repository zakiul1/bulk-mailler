<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkMailerSmtpDailyUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulk_mailer_smtp_account_id',
        'usage_date',
        'emails_sent',
    ];

    protected $casts = [
        'usage_date' => 'date',
        'emails_sent' => 'integer',
    ];

    public function smtpAccount(): BelongsTo
    {
        return $this->belongsTo(BulkMailerSmtpAccount::class, 'bulk_mailer_smtp_account_id');
    }
}