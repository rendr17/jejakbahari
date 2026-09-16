# Panduan Setup QASE — JejakBahari

## File Import

File: `docs/qa/qase-import.csv`

Isi: **42 test cases** terorganisir dalam suite per sprint/module:

| Suite | Jumlah Test Case |
|-------|------------------|
| Sprint 3 - Public Map / Public Vessel List | 8 |
| Sprint 3 - Public Map / Public Vessel Detail | 3 |
| Sprint 3 - Public Map / Latest Positions | 5 |
| Sprint 3 - Public Map / Health Check | 1 |
| Sprint 3 - Public Map / E2E Map | 9 |
| Sprint 3 - Public Map / Manual | 1 |
| Sprint 1 - Registry and Admin / Admin Auth | 3 |
| Sprint 1 - Registry and Admin / Vessel CRUD | 4 |
| Sprint 1 - Registry and Admin / Vessel Verification | 3 |
| Sprint 7 - Hardening / Security | 3 |
| Sprint 7 - Hardening / Ops | 1 |

## Langkah Setup

### 1. Buat Akun QASE

1. Daftar di https://qase.io (free tier tersedia — 3 users, 2 projects, 500 test cases)
2. Verifikasi email dan login

### 2. Buat Project

1. **Projects** → **Create new project**
2. Project name: `JejakBahari`
3. Project code: `JB` (maks 10 karakter — akan jadi prefix case ID seperti `JB-1`)
4. Access: `Private` (atau Public jika ingin showcase ke recruiter — keuntungan portfolio!)
5. Klik **Create project**

> **Tips portfolio**: Project Public di QASE bisa di-link langsung dari CV/LinkedIn —
> recruiter bisa lihat test case structure Anda tanpa perlu akses.

### 3. Import Test Cases dari CSV

1. Di project `JejakBahari`, klik **Test Cases** (sidebar kiri)
2. Klik **⋯** (menu) → **Import data**
3. Pilih format **CSV**
4. Upload `docs/qa/qase-import.csv`
5. Verifikasi column mapping:

| CSV Column | QASE Field |
|------------|------------|
| `title` | Title |
| `suite_title` | Suite (nested dengan separator `/`) |
| `description` | Description |
| `preconditions` | Preconditions |
| `priority` | Priority (critical/high/medium/low) |
| `severity` | Severity (blocker/critical/major/normal) |
| `layer` | Layer (api/e2e/unit) |
| `type` | Type (functional/security/regression/smoke/exploratory) |
| `behavior` | Behavior (positive/negative) |
| `automation_status` | Automation status (automated/to-be-automated/is-not-automated) |
| `status` | Status (actual/draft) |
| `steps_actions` | Steps → Action |
| `steps_expected_result` | Steps → Expected result |

6. Klik **Import** — semua suite dan test case terbuat otomatis

### 4. Buat Test Run

Setelah import, buat test run untuk Sprint 3:

1. **Test Runs** → **Start new test run**
2. Run name: `TR-S3-001 — Sprint 3 Public Map API`
3. Pilih suite: `Sprint 3 - Public Map` (17 API test cases)
4. Environment: `Local`
5. Klik **Start a run**

### 5. Masukkan Hasil Eksekusi

Test run `TR-S3-001` sudah dieksekusi via Bruno (hasil ada di `docs/qa/test-runs/TR-S3-001-sprint-3-public-map.md`):

- 16 test case → mark **Passed**
- TC-PUB-017 (health endpoint) → mark **Skipped** (endpoint belum diimplementasi)
- TC-PUB-009 → mark **Passed** + link ke defect JB-001 (bug found & fixed)

### 6. Integrasi dengan JIRA (Opsional tapi Bagus untuk Portfolio)

QASE free tier mendukung JIRA integration:

1. **Apps** → cari **Jira** → Install
2. Hubungkan ke JIRA site Anda (butuh JIRA API token)
3. Setelah terhubung, defect dari failed test case bisa langsung dibuat sebagai JIRA issue
4. Link JB-001 (bug vessel detail) ke TC-PUB-009 di QASE

### 7. Fitur QASE yang Bagus untuk Portfolio

| Fitur | Cara Pakai |
|-------|-----------|
| **Dashboard** | Screenshot test run results + coverage untuk portfolio |
| **Defects** | Buat defect dari failed test, link ke JIRA issue |
| **Shared Reports** | Generate public report URL — bisa dibagikan ke recruiter |
| **Milestones** | Buat milestone per Sprint (Sprint 3, Sprint 7) |
| **Requirements** | Import requirement IDs (API-004, MAP-001) dan link ke test case |

## Test Case ID Convention

QASE akan auto-assign case ID (`JB-1`, `JB-2`, dst). ID internal kami (`TC-PUB-001`) ada di awal title agar tetap traceable:

```text
TC-PUB-001 — Vessel list returns public visible vessels
^^^^^^^^   ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Backlog     Human-readable title
TC ID
```

Setelah import, update `docs/qa/TRACEABILITY_MATRIX.md` kolom QASE jika perlu mapping `TC-*` → `JB-*` case ID.

## Test Data yang Diperlukan untuk Re-run

Test case API memerlukan environment variables (lihat `tests/api-bruno/environments/Local.bru`):

- `testVesselId`: `b1939b39-aaa1-4b86-886b-66ce47bb25ac`
- `testOperatorId`: `94ff800e-8d37-4bf6-b60c-e2ba30ee4bcf`
- `privateVesselId`: `38a161ab-e5e0-4c7c-8ca1-4aa03cf5f872`

## Alternatif: TestRail

Jika lebih suka TestRail (lebih umum di enterprise):

1. Trial di https://www.testrail.com (30 hari gratis)
2. TestRail CSV import format berbeda — perlu convert kolom
3. Untuk portfolio, QASE lebih baik: free tier permanen + public project + modern UI
