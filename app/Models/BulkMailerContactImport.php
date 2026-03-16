<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkMailerContactImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulk_mailer_contact_list_id',
        'source_type',
        'source_name',
        'stored_file_path',
        'status',
        'total_read',
        'processed_count',
        'valid_count',
        'invalid_count',
        'duplicate_count',
        'inserted_count',
        'error_message',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'bulk_mailer_contact_list_id' => 'integer',
        'total_read' => 'integer',
        'processed_count' => 'integer',
        'valid_count' => 'integer',
        'invalid_count' => 'integer',
        'duplicate_count' => 'integer',
        'inserted_count' => 'integer',
        'created_by' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BulkMailerContactList::class, 'bulk_mailer_contact_list_id');
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(BulkMailerContactList::class, 'bulk_mailer_contact_list_id');
    }

    public function getProgressPercentAttribute(): int
    {
        if (($this->total_read ?? 0) <= 0) {
            return 0;
        }

        return (int) min(100, round(($this->processed_count / $this->total_read) * 100));
    }
}