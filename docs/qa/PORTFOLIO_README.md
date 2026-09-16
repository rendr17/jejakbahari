# QA Portfolio — JejakBahari

Project **JejakBahari** adalah web app tracking kapal RoRo Indonesia berbasis data AIS. Project ini berfungsi sebagai **portofolio QA** yang mendemonstrasikan penerapan SDLC Agile Scrum + Shift-Left Testing dengan berbagai tools QA industri.

## Tentang Project

- **Produk:** Web app tracking posisi kapal RoRo Indonesia berbasis AIS.
- **Stack:** Vue 3, Laravel 13, Node.js worker, PostgreSQL/PostGIS, MapLibre GL JS.
- **Status:** Sprint 0–2 selesai (foundation, registry admin, AIS worker). Sprint 3–7 planned.
- **Dokumentasi:** Lengkap di `docs/` (PRD, MVP, API spec, database schema, dll).

## SDLC yang Diterapkan

**Agile Scrum + Shift-Left Testing** — sprint 2 minggu, QA terlibat sejak requirement.

```text
Requirement → Test Design → Dev → Test Execution → Regression → Release
```

Detail: `docs/qa/QA_STRATEGY.md`

## Tools QA yang Dipraktikkan

| Tool | Kategori | Artifact di Repo |
|------|----------|------------------|
| **JIRA** | Bug tracking, sprint board | Epic/story/bug (di JIRA), ID dirujuk di traceability matrix, `jira-import.csv` untuk import 90 issues |
| **QASE** | Test case management | `qase-import.csv` — 42 test cases siap import, test case ID di `docs/qa/TRACEABILITY_MATRIX.md` |
| **Bruno** | API testing | `tests/api-bruno/` — collection untuk public, admin, internal API |
| **Selenium** | Web UI E2E | `tests/e2e-selenium/` — Page Object Model, pytest |
| **Katalon** | Web UI + API hybrid | Opsional, untuk admin workflow regression |
| **JMeter** | Performance & load | `tests/performance-jmeter/` — load test plan, CSV data feed |
| **Jenkins** | CI/CD orchestration | Pipeline build → test → deploy (alternatif GitHub Actions) |
| **Grafana** | Monitoring | Dashboard worker heartbeat, API health, AIS freshness |
| **Manual Testing** | Exploratory, UX, edge case | `tests/manual-checklists/` — checklist per sprint |

## Struktur Artifact QA

```text
docs/qa/
├── PORTFOLIO_README.md        # Dokumen ini — overview portofolio
├── QA_STRATEGY.md             # Strategi QA, SDLC, tools mapping
├── TEST_PLAN.md               # Template test plan per sprint
├── TRACEABILITY_MATRIX.md     # Requirement → Test Case → Bug → JIRA
├── SECURITY_CHECKLIST.md      # Security test checklist
├── JIRA_IMPORT_GUIDE.md       # Panduan import CSV ke JIRA Scrum
├── QASE_SETUP_GUIDE.md        # Panduan setup + import ke QASE
├── jira-import.csv            # 90 issues (epic/story/bug/task) untuk JIRA
├── qase-import.csv            # 42 test cases untuk QASE
├── BUG_REPORTS/               # Bug report detail per issue
├── test-cases/                # Test case docs per sprint
└── test-runs/                 # Test run reports

tests/
├── api-bruno/                 # Bruno API collection (.bru files)
│   └── jejakbahari-api/
│       ├── Public/            # Health, vessel list, latest positions
│       ├── Admin/             # Auth, CRUD, verification
│       └── Internal/          # Worker whitelist, post position, heartbeat
├── e2e-selenium/              # Selenium E2E (Python + pytest)
│   ├── pages/                 # Page Object Model
│   └── tests/                 # Test files per user flow
├── performance-jmeter/        # JMeter load test
│   ├── plans/                 # .jmx test plans
│   ├── data/                  # CSV data feed
│   └── results/               # Output (gitignored)
└── manual-checklists/         # Manual test checklist per sprint
```

## Yang Didemonstrasikan Portofolio Ini

### 1. Test Strategy & Planning
- Testing pyramid (unit → integration → E2E).
- Test plan per sprint dengan entry/exit criteria.
- Risk-based testing prioritization.

### 2. Test Case Management
- Test case design dengan template standar.
- Traceability matrix: requirement → test case → bug.
- Coverage tracking per sprint.

### 3. API Testing
- Bruno collection untuk semua endpoint di `06_API_SPEC.md`.
- Positive & negative test case.
- Auth flow: login → token → authenticated request.
- Internal worker API testing dengan bearer token.

### 4. Web UI Automation
- Page Object Model pattern.
- Cross-browser testing (Chrome/Firefox).
- Headless mode untuk CI.
- `data-testid` selector strategy.

### 5. Performance Testing
- Load test endpoint kritis.
- Worker burst simulation (50 msg/sec).
- Baseline target dan metrik (p95, p99, error rate).

### 6. Security Testing
- Security checklist sesuai `14_SECURITY.md`.
- Auth, input validation, XSS, rate limit, secrets.
- OWASP-aligned.

### 7. Manual & Exploratory Testing
- Network condition testing (slow, offline).
- Viewport testing (mobile, tablet, desktop).
- Accessibility checklist (WCAG AA).
- Edge case untuk data AIS (stale, invalid, missing).

### 8. CI/CD & Monitoring
- Jenkins pipeline (build → test → deploy → smoke).
- Grafana monitoring dashboard.
- Release checklist dan sign-off.

### 9. Bug Reporting
- Bug report template dengan severity & priority.
- Repro steps, expected vs actual, attachment.
- Bug tracking di JIRA dengan link ke test case.

## Roadmap Eksekusi Portofolio

| Sprint | Fokus | QA Activity | Tools |
|--------|-------|-------------|-------|
| Sprint 3 | Public Map | API test, E2E map, load test | Bruno, Selenium, JMeter |
| Sprint 4 | Search & Detail | Search E2E, detail page test | Katalon, TestRail |
| Sprint 5 | History & Ports | History load test, port CRUD | JMeter, Bruno |
| Sprint 6 | Geofence & Realtime | WS test, geofence edge case | Manual, Bruno |
| Sprint 7 | Hardening & Release | Security, performance, monitoring | All tools, Jenkins, Grafana |

## Cara Menampilkan Portofolio ini ke Recruiter

1. **README repo** — link ke `docs/qa/PORTFOLIO_README.md` (dokumen ini).
2. **JIRA** — screenshot sprint board, bug list, burndown chart.
3. **TestRail/QASE** — screenshot test run report, coverage report.
4. **Bruno** — tunjukkan collection di repo + video demo run.
5. **Selenium** — tunjukkan script + HTML report output.
6. **JMeter** — tunjukkan .jmx + HTML report (dashboard, response time graph).
7. **Grafana** — screenshot dashboard monitoring.
8. **Jenkins** — screenshot pipeline stage view.

## Catatan

- Tools berbayar (JIRA, TestRail) gunakan free tier / trial.
- Semua artifact di repo adalah file text/markdown — version-control friendly.
- Test data menggunakan fixture fiktif (MMSI range `525900000`–`525999999`).
- Tidak ada data real atau secret di repo.

## Link Dokumen Utama

- [QA Strategy](QA_STRATEGY.md)
- [Test Plan Template](TEST_PLAN.md)
- [Traceability Matrix](TRACEABILITY_MATRIX.md)
- [Security Checklist](SECURITY_CHECKLIST.md)
- [Testing Strategy (16)](../16_TESTING.md)
- [API Spec (06)](../06_API_SPEC.md)
- [User Flow (10)](../10_USER_FLOW.md)
- [Sprint Plan (12)](../12_SPRINT_PLAN.md)
- [Backlog (13)](../13_BACKLOG.md)
