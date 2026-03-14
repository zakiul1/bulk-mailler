<?php

namespace App\Models;

use App\Enums\BulkMailerVerificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkMailerEmailVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulk_mailer_contact_id',
        'email',
        'status',
        'reason',
        'checked_at',
    ];

    protected $casts = [
        'status' => BulkMailerVerificationStatus::class,
        'checked_at' => 'datetime',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(BulkMailerContact::class, 'bulk_mailer_contact_id');
    }
}