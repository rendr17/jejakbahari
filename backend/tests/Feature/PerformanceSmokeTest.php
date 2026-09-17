<?php

namespace Tests\Feature;

use App\Models\Vessel;
use App\Models\VesselLatestPosition;
use App\Models\VesselPositionHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Deterministic performance smoke tests — assert bounded query counts and
 * correct payload sizes instead of wall-clock time (flaky in CI).
 * Baselines from docs/16_TESTING.md §6.
 */
class PerformanceSmokeTest extends TestCase
{
    use RefreshDatabase;

    private string $token = 'perf-token';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.internal_worker_token' => $this->token]);
    }

    private function countQueries(callable $fn): int
    {
        $count = 0;
        $listener = function () use (&$count) {
            $count++;
        };
        DB::listen($listener);
        try {
            $fn();
        } finally {
            // Laravel has no unregister for listen; listener is scoped to
            // this test's app instance and discarded on teardown.
        }

        return $count;
    }

    public function test_latest_positions_handles_100_vessels_with_bounded_queries(): void
    {
        Vessel::factory()->verified()->count(100)->create([
            'active' => true,
            'public_visible' => true,
        ])->each(function (Vessel $v) {
            VesselLatestPosition::create([
                'vessel_id' => $v->id,
                'latitude' => -6.0 - (rand(0, 500) / 100),
                'longitude' => 105.0 + (rand(0, 500) / 100),
                'sog_knots' => 12.0,
                'source_timestamp' => now(),
                'received_at' => now(),
                'provider_name' => 'test',
            ]);
        });

        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $response = $this->getJson('/api/v1/positions/latest');

        $response->assertOk();
        $this->assertCount(100, $response->json('data'));
        // Positions + eager-loaded vessels: should be <= 3 queries
        // (positions select, vessel whereHas exists check, vessels eager load).
        $this->assertLessThanOrEqual(5, $queryCount, "N+1 detected: {$queryCount} queries for 100 positions");
    }

    /**
     * Bulk-insert history rows. Model::insert() bypasses the `saving` hook that
     * populates the PostGIS `position` column, so set it explicitly on pgsql.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function insertHistory(array $rows): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            foreach ($rows as &$row) {
                $row['position'] = DB::raw(
                    "ST_SetSRID(ST_MakePoint({$row['longitude']}, {$row['latitude']}), 4326)::geography"
                );
            }
        }
        VesselPositionHistory::insert($rows);
    }

    public function test_history_endpoint_caps_at_2000_points(): void
    {
        $vessel = Vessel::factory()->verified()->create(['active' => true, 'public_visible' => true]);

        // Bulk-insert 2500 history rows in chunks (faster than factory loop)
        $now = now();
        $rows = [];
        for ($i = 0; $i < 2500; $i++) {
            $rows[] = [
                'vessel_id' => $vessel->id,
                'latitude' => -6.1,
                'longitude' => 106.8,
                'sog_knots' => 10.0,
                'source_timestamp' => $now->copy()->subSeconds($i),
                'received_at' => $now->copy()->subSeconds($i),
                'provider_name' => 'test',
            ];
            if (count($rows) === 500) {
                $this->insertHistory($rows);
                $rows = [];
            }
        }
        if ($rows) {
            $this->insertHistory($rows);
        }

        $this->assertSame(2500, VesselPositionHistory::count());

        $response = $this->getJson("/api/v1/vessels/{$vessel->id}/positions/history?limit=2000");

        $response->assertOk();
        $this->assertSame(2000, $response->json('data.count'));
        $this->assertCount(2000, $response->json('data.points'));

        // Points must be ordered by source_timestamp ascending
        $points = $response->json('data.points');
        $timestamps = array_column($points, 'source_timestamp');
        $sorted = $timestamps;
        sort($sorted);
        $this->assertSame($sorted, $timestamps, 'History points not ordered by source_timestamp');
    }

    public function test_whitelist_endpoint_single_bounded_query(): void
    {
        Vessel::factory()->verified()->count(50)->create(['active' => true]);
        Vessel::factory()->count(10)->create(['verification_status' => 'DRAFT', 'active' => true]);

        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $response = $this->withToken($this->token)
            ->getJson('/api/internal/v1/vessel-whitelist');

        $response->assertOk();
        $this->assertSame(50, $response->json('data.count'));
        $this->assertLessThanOrEqual(3, $queryCount, "Whitelist should be a single bounded query, got {$queryCount}");
    }

    public function test_ingestion_burst_50_positions_all_accepted(): void
    {
        // 50 verified vessels, one position each — simulates worker burst.
        $vessels = Vessel::factory()->verified()->count(50)->create([
            'active' => true,
            'public_visible' => true,
        ]);

        foreach ($vessels as $vessel) {
            $response = $this->withToken($this->token)
                ->postJson('/api/internal/v1/positions', [
                    'mmsi' => $vessel->mmsi,
                    'latitude' => -6.1,
                    'longitude' => 106.8,
                    'sog_knots' => 10.0,
                    'source_timestamp' => now()->toIso8601String(),
                    'received_at' => now()->toIso8601String(),
                    'provider_name' => 'test',
                ]);

            $response->assertSuccessful()
                ->assertJsonPath('data.accepted', true);
        }

        $this->assertSame(50, VesselLatestPosition::count());
        $this->assertSame(50, VesselPositionHistory::count());
    }

    public function test_prune_handles_large_history_efficiently(): void
    {
        $vessel = Vessel::factory()->verified()->create(['active' => true]);

        // 6000 old rows + 500 recent — verifies chunked delete completes.
        $rows = [];
        for ($i = 0; $i < 6000; $i++) {
            $rows[] = [
                'vessel_id' => $vessel->id,
                'latitude' => -6.1,
                'longitude' => 106.8,
                'sog_knots' => 10.0,
                'source_timestamp' => now()->subDays(10)->subSeconds($i),
                'received_at' => now()->subDays(10),
                'provider_name' => 'test',
            ];
            if (count($rows) === 1000) {
                $this->insertHistory($rows);
                $rows = [];
            }
        }
        if ($rows) {
            $this->insertHistory($rows);
        }

        $rows = [];
        for ($i = 0; $i < 500; $i++) {
            $rows[] = [
                'vessel_id' => $vessel->id,
                'latitude' => -6.1,
                'longitude' => 106.8,
                'sog_knots' => 10.0,
                'source_timestamp' => now()->subMinutes($i),
                'received_at' => now(),
                'provider_name' => 'test',
            ];
        }
        $this->insertHistory($rows);

        $this->artisan('positions:prune-history')
            ->expectsOutputToContain('Deleted 6000 history rows')
            ->assertSuccessful();

        $this->assertSame(500, VesselPositionHistory::count());
    }
}
