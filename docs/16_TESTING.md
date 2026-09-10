# 16 — Testing Strategy

## 1. Testing Pyramid

- Banyak unit test.
- Cukup integration/feature test.
- Sedikit tetapi penting E2E test.

## 2. Frontend

### Unit/Component

- freshness formatter;
- vessel marker rotation fallback;
- search result grouping;
- loading/empty/error states;
- API error handling.

### E2E

- membuka map;
- mencari kapal;
- membuka detail;
- admin login;
- membuat vessel draft;
- verify vessel.

## 3. Backend

### Unit

- freshness service;
- sampling rule;
- geofence transition;
- verification rule.

### Feature/API

- public vessels list/detail;
- latest positions bbox;
- internal authentication;
- position ingestion;
- duplicate/stale rejection;
- admin authorization.

## 4. Worker

- parser valid/invalid;
- whitelist filter;
- dedupe;
- stale and out-of-order;
- reconnect/backoff;
- backend retry;
- queue limit;
- graceful shutdown.

Gunakan fake WebSocket server dan fake backend.

## 5. Database

- migration up/down bila memungkinkan;
- constraint MMSI;
- unique indexes;
- PostGIS point query;
- history query performance.

## 6. Performance

Baseline test:

- 100 vessel markers.
- 50 updates per second burst pada worker.
- latest positions API untuk bbox.
- history 2.000 points.
- database retention job.

## 7. Security

- unauthorized admin access.
- invalid internal token.
- oversized payload.
- XSS strings pada AIS destination/name.
- rate limit.

## 8. Manual QA

- mobile viewport.
- desktop viewport.
- slow network.
- offline.
- provider disconnected.
- data stale.
- map tile failure.

## 9. Test Data

Gunakan fixture fiktif, bukan data pribadi. Vessel example harus jelas ditandai sebagai test.

## 10. Definition of Passing

- Tidak ada test P0 gagal.
- Lint/typecheck/build lulus.
- Known issue terdokumentasi.
- Regression test ditambahkan untuk bug penting.

## 11. Tools Mapping

Dokumen lengkap strategi QA dan tools ada di `docs/qa/QA_STRATEGY.md`.

| Tool | Kategori | Penggunaan |
|------|----------|------------|
| JIRA | Bug tracking & sprint board | Epic per sprint, story per backlog ID, bug report |
| TestRail / QASE | Test case management | Test case, test run, traceability matrix |
| Bruno | API testing | Public + admin + internal worker API (`tests/api-bruno/`) |
| Selenium | Web UI E2E | User flow di `10_USER_FLOW.md` (`tests/e2e-selenium/`) |
| Katalon | Web UI + API hybrid | Admin workflow, regression suite (opsional) |
| JMeter | Performance & load | Worker burst, API load (`tests/performance-jmeter/`) |
| Jenkins | CI/CD orchestration | Build → test → deploy staging → smoke → prod |
| Grafana | Monitoring | Worker heartbeat, API health, queue, AIS freshness |
| Manual Testing | Exploratory & UX | Network condition, edge case (`tests/manual-checklists/`) |

## 12. Test Case Template

```markdown
### TC-[MODULE]-[NNN]: [Judul Test Case]

- **Priority:** P0 / P1 / P2
- **Type:** Positive / Negative / Boundary / Security
- **Pre-condition:** [kondisi awal]
- **Test Data:** [data yang digunakan]

**Steps:**
1. [action 1]
2. [action 2]
3. [action 3]

**Expected Result:**
- [expected 1]
- [expected 2]

**Actual Result:** [diisi saat eksekusi]
**Status:** PASS / FAIL / BLOCKED
**Bug ID:** [JIRA ID jika FAIL]
```

## 13. Bug Report Template

```markdown
### Bug JB-[NNN]: [Judul singkat]

- **Severity:** P0 / P1 / P2 / P3
- **Priority:** P0 / P1 / P2 / P3
- **Environment:** Local / Staging / Production
- **Browser/OS:** [browser + version, OS]
- **Reporter:** [nama]
- **Date:** [tanggal]
- **Related Req:** [backlog ID]
- **Related Test Case:** [TC ID]

**Steps to Reproduce:**
1. [step 1]
2. [step 2]
3. [step 3]

**Expected:** [hasil yang diharapkan]
**Actual:** [hasil yang terjadi]

**Attachment:** [screenshot/video log]

**Notes:** [informasi tambahan]
```

## 14. QA Definition of Done

Sebuah fitur dinyatakan lulus QA jika:

- [ ] Semua test case P0 lulus.
- [ ] Tidak ada bug P0 open.
- [ ] Bug P1 open ≤ 2 (dengan workaround terdokumentasi).
- [ ] Lint, typecheck, build lulus di CI.
- [ ] Regression suite lulus.
- [ ] Test case tercatat di TestRail/QASE.
- [ ] Traceability matrix diperbarui.
- [ ] Security checklist relevan selesai (lihat `docs/qa/SECURITY_CHECKLIST.md`).
- [ ] Known issue terdokumentasi.

## 15. Dokumen QA Pendukung

- `docs/qa/QA_STRATEGY.md` — strategi QA utama.
- `docs/qa/TEST_PLAN.md` — template test plan per sprint.
- `docs/qa/TRACEABILITY_MATRIX.md` — requirement → test case → bug.
- `docs/qa/SECURITY_CHECKLIST.md` — security test checklist.
- `docs/qa/PORTFOLIO_README.md` — overview portofolio QA.
