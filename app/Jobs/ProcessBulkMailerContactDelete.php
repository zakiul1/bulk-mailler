<?php

namespace App\Jobs;

use App\Models\BulkMailerContact;
use App\Models\BulkMailerContactDeleteJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessBulkMailerContactDelete implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    protected int $chunkSize = 1000;

    public function __construct(public int $deleteJobId)
    {
    }

    public function handle(): void
    {
        $deleteJob = BulkMailerContactDeleteJob::find($this->deleteJobId);

        if (!$deleteJob) {
            return;
        }

        $deleteJob->update([
            'status' => 'processing',
            'error_message' => null,
            'completed_at' => null,
        ]);

        try {
            $query = $this->buildQuery($deleteJob);

            $totalCount = (clone $query)->count();

            $deleteJob->update([
                'total_count' => $totalCount,
                'processed_count' => 0,
                'deleted_count' => 0,
            ]);

            if ($totalCount === 0) {
                $deleteJob->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                return;
            }

            $processedCount = 0;
            $deletedCount = 0;

            do {
                $ids = (clone $query)
                    ->limit($this->chunkSize)
                    ->pluck('id');

                $batchCount = $ids->count();

                if ($batchCount === 0) {
                    break;
                }

                $deletedInBatch = DB::table('bulk_mailer_contacts')
                    ->whereIn('id', $ids->all())
                    ->delete();

                $processedCount += $batchCount;
                $deletedCount += $deletedInBatch;

                $deleteJob->update([
                    'processed_count' => $processedCount,
                    'deleted_count' => $deletedCount,
                ]);
            } while ($batchCount > 0);

            $deleteJob->update([
                'status' => 'completed',
                'processed_count' => $processedCount,
                'deleted_count' => $deletedCount,
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $deleteJob->update([
                'status' => 'failed',
                'error_message' => mb_substr($e->getMessage(), 0, 2000),
            ]);

            throw $e;
        }
    }

    protected function buildQuery(BulkMailerContactDeleteJob $deleteJob): Builder
    {
        $filters = $deleteJob->filters ?? [];

        $query = BulkMailerContact::query();

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where('email', 'like', '%' . $search . '%');
        }

        if (!empty($filters['list_id']) && $filters['list_id'] !== 'all') {
            $query->where('bulk_mailer_contact_list_id', (int) $filters['list_id']);
        }

        if ($deleteJob->selection_type === 'selected' && !empty($filters['ids']) && is_array($filters['ids'])) {
            $ids = collect($filters['ids'])
                ->map(fn($id) => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all();

            $query->whereIn('id', $ids);
        }

        if ($deleteJob->selection_type === 'matching') {
            // Uses search/list filters only.
        }

        return $query->orderBy('id');
    }
}