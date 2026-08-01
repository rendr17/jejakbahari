# 06 — API Specification

## 1. Konvensi

Base path:

```text
/api/v1
```

Format sukses:

```json
{
  "success": true,
  "data": {},
  "meta": {}
}
```

Format error:

```json
{
  "success": false,
  "error": {
    "code": "VESSEL_NOT_FOUND",
    "message": "Kapal tidak ditemukan.",
    "details": null
  }
}
```

Timestamp menggunakan ISO 8601 UTC.

## 2. Public Endpoints

### GET /vessels

Query:

- `q`
- `operator_id`
- `status`
- `page`
- `per_page` maksimum 100

Response item:

```json
{
  "id": "uuid",
  "name": "KMP Example",
  "mmsi": "525123456",
  "imo": "IMO1234567",
  "operator": {"id": "uuid", "name": "Operator"},
  "freshness": "LIVE",
  "last_position_at": "2026-08-01T12:00:00Z"
}
```

### GET /vessels/{id}

Mengembalikan master data, latest position, routes, source summary, dan disclaimer.

### GET /vessels/{id}/positions/history

Query:

- `from`
- `to`
- `limit`

Batas MVP: maksimum 24 jam dan 2.000 titik.

### GET /positions/latest

Query:

- `bbox=minLng,minLat,maxLng,maxLat`
- `operator_id`
- `freshness`

Response harus ringan untuk peta.

```json
{
  "success": true,
  "data": [
    {
      "vessel_id": "uuid",
      "name": "KMP Example",
      "latitude": -5.87,
      "longitude": 105.77,
      "sog_knots": 12.4,
      "cog_degrees": 95.2,
      "heading_degrees": 92,
      "freshness": "LIVE",
      "source_timestamp": "2026-08-01T12:00:00Z"
    }
  ]
}
```

### GET /ports

Search dan list port.

### GET /ports/{id}

Detail port, route, dan vessel activity ringkas.

### GET /routes

List route aktif.

### GET /health/public

Status aplikasi publik tanpa informasi sensitif.

## 3. Admin Endpoints

Prefix:

```text
/api/v1/admin
```

Membutuhkan authentication.

- `POST /auth/login`
- `POST /auth/logout`
- `GET /me`
- CRUD `/operators`
- CRUD `/vessels`
- CRUD `/ports`
- CRUD `/routes`
- CRUD `/data-sources`
- `POST /vessels/{id}/verify`
- `POST /vessels/{id}/reject`
- `GET /worker-health`
- `GET /audit-logs`

## 4. Internal Worker API

Prefix:

```text
/api/internal/v1
```

Authentication menggunakan bearer token terpisah dan rotatable.

### GET /vessel-whitelist

Mengembalikan daftar MMSI aktif dan version hash.

### POST /positions

Payload:

```json
{
  "mmsi": "525123456",
  "latitude": -5.87,
  "longitude": 105.77,
  "sog_knots": 12.4,
  "cog_degrees": 95.2,
  "heading_degrees": 92,
  "nav_status": "UNDER_WAY_USING_ENGINE",
  "destination_text": "MERAK",
  "source_timestamp": "2026-08-01T12:00:00Z",
  "received_at": "2026-08-01T12:00:02Z",
  "provider_name": "provider",
  "raw_message_id": "optional-id"
}
```

Response:

```json
{
  "success": true,
  "data": {
    "accepted": true,
    "history_saved": true,
    "geofence_events": []
  }
}
```

Error codes:

- `INVALID_POSITION`
- `UNKNOWN_MMSI`
- `STALE_MESSAGE`
- `DUPLICATE_MESSAGE`
- `RATE_LIMITED`
- `INTERNAL_AUTH_FAILED`

### POST /worker-heartbeat

Payload berisi worker id, provider status, counters, dan last message time.

## 5. Freshness Calculation

Backend adalah sumber final freshness.

```text
LIVE: age < 5 minutes
DELAYED: 5 <= age < 30 minutes
STALE: 30 minutes <= age < 6 hours
OFFLINE: age >= 6 hours or no position
```

## 6. Pagination

```json
{
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 100,
    "last_page": 5
  }
}
```

## 7. Versioning

Breaking change menggunakan `/api/v2`. Non-breaking field tambahan tetap pada v1 dengan dokumentasi.
