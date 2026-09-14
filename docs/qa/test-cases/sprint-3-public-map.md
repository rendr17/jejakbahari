# Test Cases — Sprint 3: Public Map

**Sprint:** Sprint 3 — Public Map
**Module:** Public API (vessels, positions)
**Total Test Cases:** 17
**Environment:** Local / Staging
**Last Updated:** 2026-09-14

## Test Environment Setup

### Prasyarat
1. Backend Laravel berjalan di `http://localhost:8000`
2. Database PostgreSQL/PostGIS ter-seed (minimal 6 vessel VERIFIED + public_visible)
3. Set environment variable Bruno:
   - `baseUrl` = `http://localhost:8000/api/v1`
   - `testVesselId` = UUID vessel public (dari seeder)
   - `privateVesselId` = UUID vessel private (dari seeder)
   - `testOperatorId` = UUID operator (dari seeder)

### Cara Mendapatkan Test Data ID

```bash
# Jalankan backend lalu query via artisan tinker
php artisan tinker
>>> $v = App\Models\Vessel::where('public_visible', true)->first();
>>> echo $v->id;
>>> $pv = App\Models\Vessel::where('public_visible', false)->first();
>>> echo $pv->id;
>>> $o = App\Models\Operator::first();
>>> echo $o->id;
```

---

## Test Case List

### Module: Public Vessel List (GET /api/v1/vessels)

| TC ID | Bruno File | Priority | Type | Title |
|-------|-----------|----------|------|-------|
| TC-PUB-001 | Vessel List - Default | P0 | Positive | Vessel list returns public visible vessels |
| TC-PUB-002 | Vessel List - Search by Name | P0 | Positive | Vessel list supports search by name |
| TC-PUB-003 | Vessel List - Search by MMSI | P1 | Positive | Vessel list supports search by MMSI |
| TC-PUB-004 | Vessel List - Pagination | P0 | Positive | Vessel list supports pagination |
| TC-PUB-005 | Vessel List - Per Page Max 100 | P1 | Boundary | Vessel list caps per_page at 100 |
| TC-PUB-006 | Vessel List - Filter by Operator | P1 | Positive | Vessel list supports operator_id filter |
| TC-PUB-007 | Vessel List - Empty Search | P1 | Negative | Vessel list returns empty for non-matching search |
| TC-PUB-008 | Vessel List - Includes Freshness | P0 | Positive | Vessel list includes freshness field |

### Module: Public Vessel Detail (GET /api/v1/vessels/{id})

| TC ID | Bruno File | Priority | Type | Title |
|-------|-----------|----------|------|-------|
| TC-PUB-009 | Vessel Detail - Valid | P0 | Positive | Vessel detail returns public vessel |
| TC-PUB-010 | Vessel Detail - 404 Private | P0 | Negative | Vessel detail returns 404 for private vessel |
| TC-PUB-011 | Vessel Detail - 404 Non-existent | P1 | Negative | Vessel detail returns 404 for non-existent UUID |

### Module: Latest Positions (GET /api/v1/positions/latest)

| TC ID | Bruno File | Priority | Type | Title |
|-------|-----------|----------|------|-------|
| TC-PUB-012 | Latest Positions - No Filter | P0 | Positive | Latest positions returns all public vessel positions |
| TC-PUB-013 | Latest Positions - BBox Filter | P0 | Positive | Latest positions supports bbox filter |
| TC-PUB-014 | Latest Positions - BBox Excludes | P1 | Negative | Latest positions excludes positions outside bbox |
| TC-PUB-015 | Latest Positions - Invalid BBox | P2 | Boundary | Latest positions ignores invalid bbox gracefully |
| TC-PUB-016 | Latest Positions - Has Freshness | P0 | Positive | Latest positions include freshness field |

### Module: Health Check

| TC ID | Bruno File | Priority | Type | Title |
|-------|-----------|----------|------|-------|
| TC-PUB-017 | Health | P1 | Positive | Public health endpoint returns 200 |

---

## Detail Test Cases

### TC-PUB-001: Vessel list returns public visible vessels
- **Priority:** P0
- **Type:** Positive
- **Endpoint:** `GET /api/v1/vessels`
- **Pre-condition:** Database has seeded vessels with public_visible=true and false
- **Steps:**
  1. Send GET request to `/api/v1/vessels` without auth
- **Expected:**
  - Status 200
  - `success` = true
  - `data` is array
  - Only vessels with `public_visible=true` are included
  - Private vessels are NOT in response

### TC-PUB-002: Vessel list supports search by name
- **Priority:** P0
- **Type:** Positive
- **Endpoint:** `GET /api/v1/vessels?q=Merak`
- **Pre-condition:** Database has vessel with name containing "Merak"
- **Steps:**
  1. Send GET request with query `q=Merak`
- **Expected:**
  - Status 200
  - `success` = true
  - All results contain "Merak" in name (case-insensitive search via LIKE)
  - Non-matching vessels are excluded

### TC-PUB-003: Vessel list supports search by MMSI
- **Priority:** P1
- **Type:** Positive
- **Endpoint:** `GET /api/v1/vessels?q=525`
- **Pre-condition:** Database has vessel with MMSI containing "525"
- **Steps:**
  1. Send GET request with query `q=525`
- **Expected:**
  - Status 200
  - `success` = true
  - Results contain vessels with MMSI matching "525"

### TC-PUB-004: Vessel list supports pagination
- **Priority:** P0
- **Type:** Positive
- **Endpoint:** `GET /api/v1/vessels?page=1&per_page=5`
- **Pre-condition:** Database has >5 public vessels
- **Steps:**
  1. Send GET request with `page=1&per_page=5`
- **Expected:**
  - Status 200
  - `meta.page` = 1
  - `meta.per_page` = 5
  - `data.length` <= 5

### TC-PUB-005: Vessel list caps per_page at 100
- **Priority:** P1
- **Type:** Boundary
- **Endpoint:** `GET /api/v1/vessels?per_page=200`
- **Pre-condition:** None
- **Steps:**
  1. Send GET request with `per_page=200`
- **Expected:**
  - Status 200
  - `meta.per_page` <= 100 (capped by `min(per_page, 100)`)

### TC-PUB-006: Vessel list supports operator_id filter
- **Priority:** P1
- **Type:** Positive
- **Endpoint:** `GET /api/v1/vessels?operator_id={uuid}`
- **Pre-condition:** Set `testOperatorId` to valid operator UUID
- **Steps:**
  1. Send GET request with `operator_id` parameter
- **Expected:**
  - Status 200
  - All results belong to the specified operator

### TC-PUB-007: Vessel list returns empty for non-matching search
- **Priority:** P1
- **Type:** Negative
- **Endpoint:** `GET /api/v1/vessels?q=ZZZNONEXISTENTZZZ`
- **Pre-condition:** None
- **Steps:**
  1. Send GET request with query that matches no vessel
- **Expected:**
  - Status 200
  - `success` = true
  - `data` is empty array

### TC-PUB-008: Vessel list includes freshness field
- **Priority:** P0
- **Type:** Positive
- **Endpoint:** `GET /api/v1/vessels`
- **Pre-condition:** At least one public vessel has latest position
- **Steps:**
  1. Send GET request
- **Expected:**
  - Status 200
  - Each vessel has `freshness` field
  - Freshness value is one of: LIVE, DELAYED, STALE, OFFLINE

### TC-PUB-009: Vessel detail returns public vessel
- **Priority:** P0
- **Type:** Positive
- **Endpoint:** `GET /api/v1/vessels/{id}`
- **Pre-condition:** Set `testVesselId` to valid public vessel UUID
- **Steps:**
  1. Send GET request with valid public vessel UUID
- **Expected:**
  - Status 200
  - `success` = true
  - `data.id` matches requested UUID
  - `data.name` and `data.mmsi` are present

### TC-PUB-010: Vessel detail returns 404 for private vessel
- **Priority:** P0
- **Type:** Negative
- **Endpoint:** `GET /api/v1/vessels/{id}`
- **Pre-condition:** Set `privateVesselId` to vessel with `public_visible=false`
- **Steps:**
  1. Send GET request with private vessel UUID
- **Expected:**
  - Status 404
  - `success` = false
  - `error.code` = `VESSEL_NOT_FOUND`
  - `error.message` is present
  - **Security:** Private vessel data must NOT be exposed

### TC-PUB-011: Vessel detail returns 404 for non-existent UUID
- **Priority:** P1
- **Type:** Negative
- **Endpoint:** `GET /api/v1/vessels/00000000-0000-0000-0000-000000000000`
- **Pre-condition:** None
- **Steps:**
  1. Send GET request with nil UUID
- **Expected:**
  - Status 404
  - `success` = false

### TC-PUB-012: Latest positions returns all public vessel positions
- **Priority:** P0
- **Type:** Positive
- **Endpoint:** `GET /api/v1/positions/latest`
- **Pre-condition:** At least one public vessel has latest position
- **Steps:**
  1. Send GET request without filter
- **Expected:**
  - Status 200
  - `success` = true
  - `data` is array
  - Each position has: vessel_id, latitude, longitude, freshness, source_timestamp
  - Only public_visible vessels are included

### TC-PUB-013: Latest positions supports bbox filter
- **Priority:** P0
- **Type:** Positive
- **Endpoint:** `GET /api/v1/positions/latest?bbox=95,-11,141,6`
- **Pre-condition:** Public vessel with position in Indonesia
- **Steps:**
  1. Send GET request with bbox covering Indonesia
- **Expected:**
  - Status 200
  - All positions within bbox bounds [95,-11,141,6]
  - Format: `bbox=minLng,minLat,maxLng,maxLat`

### TC-PUB-014: Latest positions excludes positions outside bbox
- **Priority:** P1
- **Type:** Negative
- **Endpoint:** `GET /api/v1/positions/latest?bbox=110,-8,115,-7`
- **Pre-condition:** Public vessel at lat=-5.87, lon=105.77
- **Steps:**
  1. Send GET request with small bbox that excludes the vessel
- **Expected:**
  - Status 200
  - Vessel at (105.77, -5.87) NOT in results

### TC-PUB-015: Latest positions ignores invalid bbox gracefully
- **Priority:** P2
- **Type:** Boundary
- **Endpoint:** `GET /api/v1/positions/latest?bbox=invalid`
- **Pre-condition:** None
- **Steps:**
  1. Send GET request with invalid bbox value
- **Expected:**
  - Status 200 (not 400 or 500)
  - Invalid bbox silently ignored
  - Returns all public positions (no filter applied)

### TC-PUB-016: Latest positions include freshness field
- **Priority:** P0
- **Type:** Positive
- **Endpoint:** `GET /api/v1/positions/latest`
- **Pre-condition:** At least one public vessel has position
- **Steps:**
  1. Send GET request
- **Expected:**
  - Status 200
  - Each position has `freshness` field
  - Freshness value is one of: LIVE, DELAYED, STALE, OFFLINE

### TC-PUB-017: Public health endpoint returns 200
- **Priority:** P1
- **Type:** Positive
- **Endpoint:** `GET /api/v1/health/public`
- **Pre-condition:** Backend running
- **Steps:**
  1. Send GET request to health endpoint
- **Expected:**
  - Status 200
  - `success` = true

---

## Coverage Matrix

| Requirement (Backlog ID) | Test Case ID | Status |
|--------------------------|--------------|--------|
| API-004: Public vessel list | TC-PUB-001, 002, 003, 004, 005, 006, 007, 008 | READY |
| API-004: Public vessel detail | TC-PUB-009, 010, 011 | READY |
| API-005: Latest positions | TC-PUB-012, 013, 014, 015, 016 | READY |
| SEC-002: Rate limiting | (manual verify via JMeter) | READY |
| SEC-003: CORS | (manual verify via browser) | READY |

## Summary

| Priority | Count |
|----------|-------|
| P0 | 8 |
| P1 | 7 |
| P2 | 2 |
| **Total** | **17** |

| Type | Count |
|------|-------|
| Positive | 11 |
| Negative | 4 |
| Boundary | 2 |
