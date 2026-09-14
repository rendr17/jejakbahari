<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class PortResource extends JsonResource
{
    public function toArray($request): array
    {
        $latitude = null;
        $longitude = null;
        $hasPolygon = false;

        if (DB::connection()->getDriverName() === 'pgsql' && $this->center_point !== null) {
            $point = DB::selectOne('SELECT ST_X(center_point::geometry) AS lon, ST_Y(center_point::geometry) AS lat FROM ports WHERE id = ?', [$this->id]);
            if ($point) {
                $latitude = (float) $point->lat;
                $longitude = (float) $point->lon;
            }
            $hasPolygon = $this->geofence_geometry !== null;
        }

        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'city_name' => $this->city_name,
            'province_name' => $this->province_name,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'geofence_radius_m' => $this->geofence_radius_m,
            'geofence_type' => $hasPolygon ? 'POLYGON' : 'RADIUS',
            'verification_status' => $this->verification_status,
            'active' => $this->active,
        ];
    }
}
