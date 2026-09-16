# Panduan Import JIRA — JejakBahari

## File Import

File: `docs/qa/jira-import.csv`

Isi: 8 Epic, 55 Story, 1 Bug, 8 Task = **72 issues**

## Langkah Import

### 1. Buat Project Scrum di JIRA

1. Login ke https://www.atlassian.com/software/jira/free
2. Klik **Projects** → **Create project** → **Scrum**
3. Project name: `JejakBahari`
4. Project key: `JB`
5. Klik **Create**

### 2. Import CSV

**Cara 1: Via JIRA Cloud (direct import)**

1. Buka project `JejakBahari`
2. Klik **⋯ More** (kanan atas) → **Import issues from CSV**
3. Upload `jira-import.csv`
4. Map columns:
   - `Issue Type` → Issue Type
   - `Summary` → Summary
   - `Description` → Description
   - `Priority` → Priority
   - `Status` → Status
   - `Epic Name` → Epic Name (untuk Epic)
   - `Epic Link` → Epic Link (untuk Story/Task/Bug — link ke Epic)
   - `Sprint` → Sprint (custom field, atau skip dan assign manual)
   - `Labels` → Labels
5. Klik **Next** → **Begin Import**

**Cara 2: Via JIRA Settings (bulk import)**

1. **Settings** (gear icon) → **System** → **External System Import**
2. Pilih **CSV**
3. Upload `jira-import.csv`
4. Pilih project `JejakBahari`
5. Map columns seperti di atas
6. Klik **Begin Import**

### 3. Konfigurasi Sprint

Setelah import, buat sprint di JIRA:

1. **Backlog** → **Create Sprint**
2. Buat sprint:
   - `Sprint 0` — Done (start: 2026-08-01, end: 2026-08-15)
   - `Sprint 1` — Done (start: 2026-08-16, end: 2026-08-30)
   - `Sprint 2` — Done (start: 2026-08-31, end: 2026-09-13)
   - `Sprint 3` — Done (start: 2026-09-14, end: 2026-09-27)
   - `Sprint 4` — Done (start: 2026-09-28, end: 2026-10-11)
   - `Sprint 5` — Done (start: 2026-10-12, end: 2026-10-25)
   - `Sprint 6` — Done (start: 2026-10-26, end: 2026-11-08)
   - `Sprint 7` — Active (start: 2026-09-16, end: 2026-09-30)

3. Assign issue ke sprint sesuai `Sprint` column di CSV

### 4. Konfigurasi Board

1. **Board** → **Configure**
2. Tambahkan kolom:
   - `To Do` → `Backlog`, `To Do`
   - `In Progress` → `In Progress`, `In Review`
   - `QA Review` → `QA Review` (custom status untuk testing)
   - `Done` → `Done`
3. Aktifkan **Burndown Chart** di **Reports**
4. Aktifkan **Velocity Chart** di **Reports**

### 5. Custom Field (Opsional)

Untuk traceability ke test case:

1. **Settings** → **Issues** → **Custom fields**
2. Buat field: `Test Case ID` (text field)
3. Assign ke screen `Default Screen`
4. Isi `Test Case ID` di story yang relevan (contoh: `TC-PUB-001` untuk API-004)

## Issue yang Akan Terlihat Setelah Import

### Sprint 0–6 (DONE)
- 8 Epic → semua DONE
- 55 Story → sebagian besar DONE
- Bug JB-001 → DONE (fixed)

### Sprint 7 (Active)
- Epic: Sprint 7 — Hardening dan Release
- Story: OPS-001 s/d OPS-004, FE-007 → To Do
- Task: QA-S4-001 s/d QA-S4-008 → To Do

## Tips

- Setelah import, drag issue dari backlog ke sprint yang sesuai
- Gunakan **Labels** untuk filter issue by module (frontend, backend, worker, api, qa)
- Gunakan **Epic Link** untuk melihat progress per sprint
- Screenshot **Burndown Chart** dan **Velocity Chart** untuk portofolio
- Bug JB-001 sudah ada di CSV — tinggal link ke commit fix di repository

## Mapping Backlog ID → JIRA Issue Key

Setelah import, JIRA akan assign issue key (contoh: `JB-1`, `JB-2`, dst). Update `docs/qa/TRACEABILITY_MATRIX.md` dengan mapping:

| Backlog ID | JIRA Issue | Notes |
|------------|-----------|-------|
| FND-001 | JB-9 | Initialize monorepo |
| API-004 | JB-38 | Public vessel list |
| JB-001 | JB-64 | Bug — vessel detail 500 |
| ... | ... | ... |

(Lihat `docs/qa/TRACEABILITY_MATRIX.md` untuk mapping lengkap)
