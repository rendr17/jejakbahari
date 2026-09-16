<?php

namespace App\Console\Commands;

use App\Models\VesselPositionHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Deletes vessel position history older than the configured retention window.
 *
 * Retention is based on source_timestamp (logical AIS message age) so that
 * late-arriving data does not extend the storage window. The
 * (vessel_id, source_timestamp) index keeps the DELETE efficient; rows are
 * removed in chunks to avoid long table locks.
 */
class PrunePositionHistory extends Command
{
    protected $signature = 'positions:prune-history {--days= : Override retention window in days} {--dry-run : Count rows without deleting}';

    protected $description = 'Prune vessel_position_history rows older than HISTORY_RETENTION_DAYS';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?? config('positions.history_retention_days', 7));

        if ($days < 1) {
            $this->error('Retention window must be at least 1 day.');

            return self::FAILURE;
        }

        $cutoff = Carbon::now()->subDays($days);
        $this->info("Pruning history rows with source_timestamp < {$cutoff->toIso8601String()} (retention: {$days} days)");

        $query = VesselPositionHistory::where('source_timestamp', '<', $cutoff);

        if ($this->option('dry-run')) {
            $this->info("Dry run: {$query->count()} rows would be deleted.");

            return self::SUCCESS;
        }

        $deleted = 0;
        do {
            // Delete in chunks to keep locks short on large history tables.
            $batch = VesselPositionHistory::where('source_timestamp', '<', $cutoff)
                ->orderBy('id')
                ->limit(5000)
                ->pluck('id');

            if ($batch->isEmpty()) {
                break;
            }

            $deleted += VesselPositionHistory::whereIn('id', $batch)->delete();
        } while ($batch->count() === 5000);

        $this->info("Deleted {$deleted} history rows.");

        return self::SUCCESS;
    }
}
