<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class CoreSchemaTest extends TestCase
{
    public function test_postgis_schema_and_mmsi_constraint_are_available(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('PostGIS schema test requires PostgreSQL.');
        }

        $this->assertNotEmpty(DB::selectOne('SELECT PostGIS_Version() AS version')->version);

        foreach ([
            'operators',
            'vessels',
            'ports',
            'routes',
            'vessel_latest_positions',
            'vessel_position_history',
            'data_sources',
            'registry_evidence',
            'port_events',
            'audit_logs',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table));
        }

        $this->expectException(QueryException::class);

        DB::table('vessels')->insert([
            'id' => Str::uuid(),
            'mmsi' => 'invalid',
            'name' => 'KMP Test',
            'normalized_name' => 'kmp test',
            'vessel_category' => 'RORO',
            'verification_status' => 'DRAFT',
            'confidence_score' => 0,
            'active' => true,
            'public_visible' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
