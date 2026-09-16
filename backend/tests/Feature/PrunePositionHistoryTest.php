<?php

namespace Tests\Feature;

use App\Models\Vessel;
use App\Models\VesselPositionHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrunePositionHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_prunes_history_older_than_retention_window(): void
    {
        $vessel = Vessel::factory()->create();

        $old = VesselPositionHistory::create([
            'vessel_id' => $vessel->id,
            'latitude' => -6.1,
            'longitude' => 106.8,
            'sog_knots' => 10.0,
            'source_timestamp' => now()->subDays(10),
            'received_at' => now()->subDays(10),
            'provider_name' => 'test',
        ]);

        $recent = VesselPositionHistory::create([
            'vessel_id' => $vessel->id,
            'latitude' => -6.1,
            'longitude' => 106.8,
            'sog_knots' => 10.0,
            'source_timestamp' => now()->subDays(2),
            'received_at' => now()->subDays(2),
            'provider_name' => 'test',
        ]);

        $this->artisan('positions:prune-history')
            ->expectsOutputToContain('Deleted 1 history rows')
            ->assertSuccessful();

        $this->assertNull(VesselPositionHistory::find($old->id));
        $this->assertNotNull(VesselPositionHistory::find($recent->id));
    }

    public function test_dry_run_counts_without_deleting(): void
    {
        $vessel = Vessel::factory()->create();

        VesselPositionHistory::create([
            'vessel_id' => $vessel->id,
            'latitude' => -6.1,
            'longitude' => 106.8,
            'sog_knots' => 10.0,
            'source_timestamp' => now()->subDays(10),
            'received_at' => now()->subDays(10),
            'provider_name' => 'test',
        ]);

        $this->artisan('positions:prune-history', ['--dry-run' => true])
            ->expectsOutputToContain('Dry run: 1 rows would be deleted')
            ->assertSuccessful();

        $this->assertSame(1, VesselPositionHistory::count());
    }

    public function test_rejects_invalid_retention_window(): void
    {
        $this->artisan('positions:prune-history', ['--days' => 0])
            ->expectsOutputToContain('Retention window must be at least 1 day')
            ->assertFailed();
    }

    public function test_respects_custom_days_override(): void
    {
        $vessel = Vessel::factory()->create();

        VesselPositionHistory::create([
            'vessel_id' => $vessel->id,
            'latitude' => -6.1,
            'longitude' => 106.8,
            'sog_knots' => 10.0,
            'source_timestamp' => now()->subDays(5),
            'received_at' => now()->subDays(5),
            'provider_name' => 'test',
        ]);

        // With --days=3, a 5-day-old row should be pruned even though
        // default retention is 7 days.
        $this->artisan('positions:prune-history', ['--days' => 3])
            ->expectsOutputToContain('Deleted 1 history rows')
            ->assertSuccessful();

        $this->assertSame(0, VesselPositionHistory::count());
    }
}
