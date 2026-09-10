<?php

namespace Database\Seeders;

use App\Models\DataSource;
use App\Models\Operator;
use App\Models\User;
use App\Models\Vessel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

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

        $operator = Operator::factory()->create(['name' => 'ASDP Indonesia Ferry']);
        DataSource::factory()->create(['name' => 'AIS Stream Provider']);

        Vessel::factory()->count(3)->create([
            'operator_id' => $operator->id,
        ]);
        Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'name' => 'KMP Example',
            'mmsi' => '525123456',
        ]);
    }
}
