<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PortRouteSeeder extends Seeder
{
    public function run(): void
    {
        $ports = [
            ['code' => 'MERAK', 'name' => 'Pelabuhan Merak', 'city' => 'Merak', 'province' => 'Banten', 'lat' => -5.8891, 'lon' => 106.0044],
            ['code' => 'BAKAUHENI', 'name' => 'Pelabuhan Bakkauheni', 'city' => 'Bakkauheni', 'province' => 'Lampung', 'lat' => -5.8600, 'lon' => 105.7600],
            ['code' => 'PADANG_BAI', 'name' => 'Pelabuhan Padang Bai', 'city' => 'Padang Bai', 'province' => 'Bali', 'lat' => -8.5289, 'lon' => 115.5011],
            ['code' => 'LEMBAR', 'name' => 'Pelabuhan Lembar', 'city' => 'Lembar', 'province' => 'Nusa Tenggara Barat', 'lat' => -8.3617, 'lon' => 116.0472],
            ['code' => 'KUPANG', 'name' => 'Pelabuhan Tenau Kupang', 'city' => 'Kupang', 'province' => 'Nusa Tenggara Timur', 'lat' => -10.1700, 'lon' => 123.6000],
            ['code' => 'LARANTUKA', 'name' => 'Pelabuhan Larantuka', 'city' => 'Larantuka', 'province' => 'Nusa Tenggara Timur', 'lat' => -8.3483, 'lon' => 122.9750],
            ['code' => 'SIBOLGA', 'name' => 'Pelabuhan Sibolga', 'city' => 'Sibolga', 'province' => 'Sumatera Utara', 'lat' => 1.7500, 'lon' => 98.7667],
            ['code' => 'GUNUNG_SITOLI', 'name' => 'Pelabuhan Gunung Sitoli', 'city' => 'Gunung Sitoli', 'province' => 'Sumatera Utara', 'lat' => 1.2833, 'lon' => 97.6333],
        ];

        $portIds = [];
        foreach ($ports as $p) {
            $id = Str::uuid()->toString();
            $portIds[$p['code']] = $id;
            DB::table('ports')->updateOrInsert(
                ['code' => $p['code']],
                [
                    'id' => $id,
                    'name' => $p['name'],
                    'city_name' => $p['city'],
                    'province_name' => $p['province'],
                    'geofence_radius_m' => 2000,
                    'verification_status' => 'VERIFIED',
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            // Set PostGIS center_point directly.
            if (DB::connection()->getDriverName() === 'pgsql') {
                DB::statement(
                    'UPDATE ports SET center_point = ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography WHERE id = ?',
                    [$p['lon'], $p['lat'], $id]
                );
            }
        }

        $routes = [
            ['origin' => 'MERAK', 'destination' => 'BAKAUHENI', 'name' => 'Merak – Bakkauheni', 'type' => 'ROPAX'],
            ['origin' => 'PADANG_BAI', 'destination' => 'LEMBAR', 'name' => 'Padang Bai – Lembar', 'type' => 'ROPAX'],
            ['origin' => 'KUPANG', 'destination' => 'LARANTUKA', 'name' => 'Kupang – Larantuka', 'type' => 'RORO'],
            ['origin' => 'SIBOLGA', 'destination' => 'GUNUNG_SITOLI', 'name' => 'Sibolga – Gunung Sitoli', 'type' => 'ROPAX'],
        ];

        foreach ($routes as $r) {
            $originId = $portIds[$r['origin']];
            $destId = $portIds[$r['destination']];
            DB::table('routes')->updateOrInsert(
                ['origin_port_id' => $originId, 'destination_port_id' => $destId, 'route_type' => $r['type']],
                [
                    'id' => Str::uuid()->toString(),
                    'name' => $r['name'],
                    'bidirectional' => true,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
