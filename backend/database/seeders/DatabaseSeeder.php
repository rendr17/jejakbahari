<?php

namespace Database\Seeders;

use App\Models\DataSource;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@jejakbahari.test',
            'role' => 'admin',
        ]);

        User::factory()->reviewer()->create([
            'name' => 'Reviewer',
            'email' => 'reviewer@jejakbahari.test',
        ]);

        // Live AIS ingestion source used by the worker.
        DataSource::factory()->create(['name' => 'AIS Stream Provider']);

        // Real Indonesian RoRo/RoPax registry with multi-source evidence.
        $this->call(RealRoroVesselSeeder::class);

        // Indonesian ports and routes.
        $this->call(PortRouteSeeder::class);
    }
}
