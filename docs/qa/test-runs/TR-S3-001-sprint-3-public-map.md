# Test Run Report — Sprint 3: Public Map

**Test Run ID:** TR-S3-001
**Sprint:** Sprint 3 — Public Map
**Tanggal Eksekusi:** 2026-09-14
**Tester:** QA Automation
**Environment:** Local (Docker)
**Base URL:** http://localhost:8000/api/v1
**Database:** PostgreSQL 17 + PostGIS 3.5 (Docker)
**Seed:** RealRoroVesselSeeder (6 public vessels, 14 private vessels)

## 1. Ringkasan Eksekusi

| Metrik | Nilai |
|--------|-------|
| Total Test Case | 17 |
| Pass | 16 |
| Fail | 0 |
| Blocked | 0 |
| Skipped | 1 (health endpoint not implemented) |
| Pass Rate | 94% (16/17) |
| Bug Ditemukan | 1 (FIXED) |
| Durasi | ~5 menit |

## 2. Hasil per Test Case

| TC ID | Title | Priority | Status | Response | Notes |
|-------|-------|----------|--------|----------|-------|
| TC-PUB-001 | Vessel list - Default | P0 | PASS | 200, 6 vessels | |
| TC-PUB-002 | Vessel list - Search by name | P0 | PASS | 200, 1 result | q=EIRENE |
| TC-PUB-003 | Vessel list - Search by MMSI | P1 | PASS | 200, 6 results | q=525 |
| TC-PUB-004 | Vessel list - Pagination | P0 | PASS | 200, page=1, per_page=3 | |
| TC-PUB-005 | Vessel list - Per page max 100 | P1 | PASS | 200, per_page=100 | Requested 200, capped to 100 |
| TC-PUB-006 | Vessel list - Filter by operator | P1 | PASS | 200, 5 results | ASDP operator |
| TC-PUB-007 | Vessel list - Empty search | P1 | PASS | 200, 0 results | q=ZZZNONEXISTENTZZZ |
| TC-PUB-008 | Vessel list - Includes freshness | P0 | PASS | 200 | LIVE + OFFLINE present |
| TC-PUB-009 | Vessel detail - Valid | P0 | PASS | 200, KMP EIRENE | Bug JB-001 found & fixed before pass |
| TC-PUB-010 | Vessel detail - 404 private | P0 | PASS | 404, VESSEL_NOT_FOUND | Security: private vessel hidden |
| TC-PUB-011 | Vessel detail - 404 non-existent | P1 | PASS | 404 | Nil UUID |
| TC-PUB-012 | Latest positions - No filter | P0 | PASS | 200, 1 position | |
| TC-PUB-013 | Latest positions - BBox filter | P0 | PASS | 200, 1 position | bbox=95,-11,141,6 (Indonesia) |
| TC-PUB-014 | Latest positions - BBox excludes | P1 | PASS | 200, 0 positions | bbox=110,-8,115,-7 |
| TC-PUB-015 | Latest positions - Invalid bbox | P2 | PASS | 200, 1 position | Invalid bbox gracefully ignored |
| TC-PUB-016 | Latest positions - Has freshness | P0 | PASS | 200 | freshness=LIVE |
| TC-PUB-017 | Health check | P1 | SKIPPED | — | Endpoint not implemented in routes |

## 3. Bug Ditemukan

| Bug ID | TC ID | Severity | Summary | Status |
|--------|-------|----------|---------|--------|
| JB-001 | TC-PUB-009 | P0 | Vessel detail 500 error: whenLoaded() called on Model | FIXED |

**Detail:** `PublicVesselDetailResource` called `$e->whenLoaded('dataSource')` on a `RegistryEvidence` model. `whenLoaded()` only exists on `JsonResource`, not `Model`. Fixed by using `$e->relationLoaded('dataSource')` instead.

Lihat: `docs/qa/BUG_REPORTS/JB-001-vessel-detail-whenloaded.md`

## 4. Hasil per Priority

| Priority | Total | Pass | Fail | Skipped | Pass Rate |
|----------|-------|------|------|---------|-----------|
| P0 | 8 | 8 | 0 | 0 | 100% |
| P1 | 7 | 6 | 0 | 1 | 86% |
| P2 | 2 | 2 | 0 | 0 | 100% |

## 5. Exit Criteria Check

- [x] 100% test case P0 lulus (8/8)
- [x] Tidak ada bug P0 open (JB-001 fixed)
- [x] Bug P1 open <= 2 (0 open)
- [x] CI hijau (frontend: typecheck, lint, format, test, build)
- [ ] Backend CI belum diverifikasi (PHP 8.4 di CI, bukan local)
- [ ] Regression suite (Selenium E2E) belum dijalankan

## 6. Catatan

1. **Bug JB-001 ditemukan saat eksekusi** — vessel detail endpoint mengembalikan 500 error. Bug diperbaiki dan re-test lulus. Ini mendemonstrasikan nilai QA testing: bug tidak terdeteksi oleh existing feature test karena test tidak load `evidence` relation.

2. **Health endpoint (TC-PUB-017) tidak ada di routes** — endpoint `/api/v1/health/public` tidak didefinisikan di `routes/api.php`. Test case ini perlu dihapus atau endpoint perlu diimplementasi. Direkomendasikan implementasi endpoint untuk Sprint 7 (hardening).

3. **Test data**: 1 position di-insert manual untuk KMP EIRENE (MMSI 525701487) di koordinat (-5.87, 105.77) untuk memverifikasi freshness=LIVE dan bbox filter.

4. **DatabaseSeeder bug juga ditemukan**: `WithoutModelEvents` trait mencegah UUID generation di User model. Fixed dengan menghapus trait.

## 7. Sign-off

- **QA Engineer:** QA Automation — 2026-09-14
- **Status:** CONDITIONAL — P0 lulus, backend CI belum diverifikasi, Selenium E2E pending
