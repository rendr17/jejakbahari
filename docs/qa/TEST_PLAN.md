# Test Plan — JejakBahari

**Template test plan per sprint.** Duplikasi dokumen ini untuk setiap sprint dan isi bagian dalam kurung siku.

## 1. Informasi Sprint

- **Sprint:** [Sprint N — Nama]
- **Periode:** [tanggal mulai] – [tanggal selesai]
- **Goal:** [sesuai `12_SPRINT_PLAN.md`]
- **QA Engineer:** [nama]
- **Environment:** Local / Staging

## 2. Scope

### In Scope

| Backlog ID | Fitur | Test Type |
|------------|-------|-----------|
| [API-004] | [Public vessel list] | [API + Manual] |
| [MAP-001] | [Initialize MapLibre] | [E2E] |

### Out of Scope

- [fitur yang tidak dites sprint ini]

## 3. Test Approach

### 3.1 Unit & Integration

- Backend: Laravel Feature Test (PHPUnit) — sudah ada di `backend/tests/Feature/`.
- Worker: Vitest — sudah ada di `worker/src/*.test.ts`.
- Frontend: Vitest — sudah ada di `frontend/src/`.

### 3.2 API Testing (Bruno)

- Collection: `tests/api-bruno/jejakbahari-api/`
- Environment: `Local`, `Staging`
- Endpoint target: [list endpoint dari `06_API_SPEC.md`]

### 3.3 E2E (Selenium/Katalon)

- Script: `tests/e2e-selenium/` atau `tests/e2e-katalon/`
- User flow target: [sesuai `10_USER_FLOW.md`]

### 3.4 Performance (JMeter)

- Script: `tests/performance-jmeter/`
- Target: [endpoint/worker burst]

### 3.5 Manual & Exploratory

- Checklist: `tests/manual-checklists/[sprint-n].md`
- Fokus: [edge case, network condition, UX]

## 4. Test Case Summary

| Module | Total Case | P0 | P1 | P2 |
|--------|-----------|----|----|-----|
| [Module] | [N] | [N] | [N] | [N] |

## 5. Test Data

- Fixture: [lokasi file fixture]
- MMSI test range: `525900000`–`525999999`
- Seed: `php artisan db:seed` (bila tersedia)

## 6. Schedule

| Aktivitas | Tanggal | PIC |
|-----------|---------|-----|
| Test design | [tanggal] | QA |
| Test execution start | [tanggal] | QA |
| Regression | [tanggal] | QA |
| Bug fix cutoff | [tanggal] | Dev |
| Final regression | [tanggal] | QA |
| Sign-off | [tanggal] | QA Lead |

## 7. Risks & Mitigation

| Risiko | Dampak | Mitigasi |
|--------|--------|----------|
| [risiko] | [dampak] | [mitigasi] |

## 8. Deliverables

- [ ] Test case di TestRail/QASE
- [ ] Bruno collection update
- [ ] Selenium/Katalon script update
- [ ] JMeter script (jika ada performance test)
- [ ] Test run report
- [ ] Bug report di JIRA
- [ ] Traceability matrix update
- [ ] Release sign-off (jika sprint release)

## 9. Exit Criteria

- [ ] 100% test case P0 lulus
- [ ] ≥ 95% test case P1 lulus
- [ ] 0 bug P0 open
- [ ] ≤ 2 bug P1 open (dengan workaround)
- [ ] Regression suite lulus
- [ ] CI hijau

---

## Contoh: Test Plan Sprint 3 — Public Map

### Informasi Sprint
- **Sprint:** Sprint 3 — Public Map
- **Goal:** Pengguna dapat melihat posisi terakhir kapal.
- **Environment:** Local + Staging

### Scope — In Scope

| Backlog ID | Fitur | Test Type |
|------------|-------|-----------|
| API-004 | Public vessel list | API (Bruno) |
| API-005 | Latest positions endpoint | API (Bruno) + Performance (JMeter) |
| MAP-001 | Initialize MapLibre | E2E (Selenium) |
| MAP-002 | Render vessel markers | E2E (Selenium) + Manual |
| MAP-003 | Freshness legend | Manual + E2E |
| MAP-004 | Vessel detail card | E2E (Selenium) |

### Test Case Summary

| Module | Total | P0 | P1 | P2 |
|--------|-------|----|----|-----|
| Public Vessel API | 12 | 5 | 5 | 2 |
| Latest Positions API | 8 | 4 | 3 | 1 |
| MapLibre Render | 6 | 3 | 2 | 1 |
| Freshness Legend | 4 | 2 | 1 | 1 |
| Vessel Card | 5 | 2 | 2 | 1 |
| Error/Empty State | 6 | 3 | 2 | 1 |
| **Total** | **41** | **19** | **15** | **7** |
