<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkMailerContactDeleteJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulk_mailer_contact_list_id',
        'status',
        'selection_type',
        'filters',
        'total_count',
        'processed_count',
        'deleted_count',
        'error_message',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'bulk_mailer_contact_list_id' => 'integer',
        'filters' => 'array',
        'total_count' => 'integer',
        'processed_count' => 'integer',
        'deleted_count' => 'integer',
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
        if (($this->total_count ?? 0) <= 0) {
            return 0;
        }

        return (int) min(100, round(($this->processed_count / $this->total_count) * 100));
    }

    public function getIsFinishedAttribute(): bool
    {
        return in_array($this->status, ['completed', 'failed'], true);
    }
}