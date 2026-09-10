# QA Strategy — JejakBahari

**Dokumen ini adalah strategi QA utama untuk JejakBahari.** Berlaku untuk seluruh sprint MVP dan dirancang untuk berfungsi sebagai artifact portofolio QA.

## 1. Tujuan QA

- Memastikan setiap fitur MVP memenuhi acceptance criteria di `02_MVP.md` dan `01_PRD.md`.
- Menjaga kualitas data AIS yang tidak akurat, terlambat, atau hilang tetap ditampilkan dengan transparansi.
- Mencegah bug P0 mencapai produksi.
- Mendokumentasikan aktivitas QA secara lengkap untuk portofolio.

## 2. Model SDLC

**Agile Scrum + Shift-Left Testing.**

- Sprint dua minggu sesuai `12_SPRINT_PLAN.md`.
- QA terlibat sejak fase requirement, bukan menunggu development selesai.
- Setiap sprint memiliki test plan, test execution, dan regression cycle.

### Alur per Sprint

```text
Requirement Analysis
  → Test Design (TestRail/QASE)
  → Development (CI auto test: unit/integration)
  → Test Execution (Bruno/Selenium/Katalon/Manual)
  → Performance & Security (JMeter/Security checklist)
  → Regression
  → Release (Jenkins/Grafana)
```

## 3. Testing Pyramid

Sesuai `16_TESTING.md`:

```text
        E2E (sedikit, kritis)
       ───────────────────
     Integration / Feature (cukup)
    ────────────────────────────
   Unit Test (banyak, cepat)
  ───────────────────────────────
```

### Distribusi Target

| Layer | Tools | Target Coverage |
|-------|-------|-----------------|
| Unit | Vitest (frontend/worker), Pint/PHPUnit (backend) | 80% logic kritis |
| Integration/Feature | Laravel Feature Test, Vitest | Semua endpoint API + worker flow |
| E2E | Selenium/Katalon | User flow utama di `10_USER_FLOW.md` |
| API Manual | Bruno | Semua endpoint di `06_API_SPEC.md` |
| Performance | JMeter | Endpoint kritis + worker burst |
| Manual/Exploratory | Checklist | Edge case, UX, network condition |

## 4. Tools Mapping

| Tool | Kategori | Penggunaan di JejakBahari |
|------|----------|---------------------------|
| JIRA | Test Management & Bug Tracking | Sprint board, epic per sprint, story per backlog ID, bug report |
| TestRail / QASE | Test Case Management | Test case, test run, traceability matrix |
| Bruno | API Testing | Public + admin + internal worker API |
| Selenium | Web UI E2E | Landing, peta, search, detail, admin CRUD |
| Katalon | Web UI + API Hybrid | Admin workflow, regression suite |
| JMeter | Performance & Load | Worker burst, API load, history query |
| Jenkins | CI/CD Orchestration | Build → test → deploy staging → smoke → deploy prod |
| Grafana | Monitoring | Worker heartbeat, API health, queue, AIS freshness |
| Manual Testing | Exploratory & UX | Network condition, edge case, accessibility |

### Catatan Lisensi

- JIRA: Free tier (10 user) — https://www.atlassian.com/software/jira/free
- TestRail: Trial / gunakan QASE sebagai alternatif
- QASE: Free tier — https://qase.io
- Bruno: Open source gratis — https://www.usebruno.com
- Selenium: Open source gratis
- Katalon: Free version — https://katalon.com
- JMeter: Open source gratis
- Jenkins: Open source gratis
- Grafana: Open source gratis (OSS)

## 5. Environment Testing

| Environment | Tujuan | Data |
|-------------|--------|------|
| Local | Dev + manual QA | Fixture fiktif, mock provider |
| Staging | Integration test, E2E, smoke test | Seed data terbatas |
| Production | Monitoring, smoke test post-deploy | Data real |

## 6. Test Data

- Gunakan fixture fiktif, bukan data pribadi (sesuai `16_TESTING.md` §9).
- Vessel example harus jelas ditandai sebagai test.
- MMSI fiktif: gunakan range `525900000`–`525999999` untuk test (non-allocated range).
- Jangan gunakan MMSI kapal real sebagai test data publik.

## 7. Definition of Done QA

Tugas dianggap lulus QA jika:

- [ ] Semua test case P0 lulus.
- [ ] Tidak ada bug P0/P1 open.
- [ ] Lint, typecheck, build lulus di CI.
- [ ] Regression suite lulus.
- [ ] Performance test untuk endpoint kritis lulus baseline.
- [ ] Security checklist untuk fitur terkait selesai.
- [ ] Test case dan traceability matrix diperbarui di TestRail/QASE.
- [ ] Bug terdokumentasi di JIRA dengan repro steps.
- [ ] Known issue terdokumentasi.

## 8. Bug Severity

| Severity | Definisi | SLA |
|----------|----------|-----|
| P0 — Critical | Fitur utama tidak berfungsi, data loss, security breach | Fix sebelum release |
| P1 — High | Fitur utama terganggu, workaround sulit | Fix dalam sprint |
| P2 — Medium | Fitur sekunder terganggu, workaround ada | Fix sprint berikutnya |
| P3 — Low | Cosmetik, typo, minor UX | Backlog |

## 9. Bug Priority vs Severity

- **Severity**: dampak teknis (seberapa rusak).
- **Priority**: urutan fix (seberapa cepat harus diperbaiki).
- P0 severity selalu P0 priority. P1 severity bisa P1/P2 priority tergantung frekuensi.

## 10. Entry & Exit Criteria per Sprint

### Entry Criteria
- Sprint backlog finalized.
- Acceptance criteria setiap story jelas.
- Test environment tersedia.

### Exit Criteria
- Semua test case P0/P1 lulus.
- Tidak ada bug P0 open.
- Regression suite lulus.
- Test run report di TestRail/QASE.
- Release sign-off jika sprint release.

## 11. Dokumen QA Pendukung

- `TEST_PLAN.md` — test plan per sprint.
- `TRACEABILITY_MATRIX.md` — requirement → test case → bug.
- `SECURITY_CHECKLIST.md` — security test checklist.
- `PORTFOLIO_README.md` — overview portofolio QA.
- `16_TESTING.md` — testing strategy teknis (dokumen utama).

## 12. Risiko QA

| Risiko | Mitigasi |
|--------|----------|
| Data AIS tidak tersedia untuk testing | Gunakan mock provider di worker |
| MapLibre sulit di-automate | Gunakan data-testid, wait untuk tile load |
| WebSocket realtime sulit di-test | Test REST fallback dulu, lalu manual untuk WS |
| Provider berbayar untuk load test | Gunakan mock provider + JMeter dummy payload |
| Jenkins butuh infrastruktur | Alternatif: GitHub Actions sudah ada, Jenkins opsional |
