# 13 — Product Backlog

Status: `PROPOSED`, `READY`, `IN_PROGRESS`, `BLOCKED`, `IN_REVIEW`, `DONE`, `CANCELLED`.

## Foundation

| ID | Task | Priority | Status |
|---|---|---:|---|
| FND-001 | Initialize monorepo folders | P0 | DONE |
| FND-002 | Configure frontend lint/typecheck | P0 | DONE |
| FND-003 | Configure backend formatter/test | P0 | DONE |
| FND-004 | Configure worker lint/test | P0 | DONE |
| FND-005 | Add CI workflow | P0 | DONE |
| FND-006 | Add environment examples | P0 | DONE |
| FND-007 | Define JejakBahari brand and UI baseline | P0 | DONE |

## Database

| ID | Task | Priority | Status |
|---|---|---:|---|
| DB-001 | Enable PostGIS extension | P0 | DONE |
| DB-002 | Create operators table | P0 | DONE |
| DB-003 | Create vessels table | P0 | DONE |
| DB-004 | Create latest positions table | P0 | DONE |
| DB-005 | Create history table and indexes | P0 | DONE |
| DB-006 | Create ports and routes | P1 | DONE |
| DB-007 | Create sources and evidence | P0 | DONE |
| DB-008 | Create audit logs | P1 | DONE |

## Registry Data

| ID | Task | Priority | Status |
|---|---|---:|---|
| REG-001 | Seed real Indonesian RoRo vessels with MMSI + multi-source evidence | P0 | IN_REVIEW |
| REG-002 | Reach MVP target of 20 verified RoRo vessels | P0 | IN_PROGRESS |
| REG-003 | Cross-verify REVIEW vessels with second independent source | P0 | PROPOSED |
| REG-004 | Run seeder + quality gate + rebuild backend image | P0 | IN_PROGRESS |

## Backend

| ID | Task | Priority | Status |
|---|---|---:|---|
| API-001 | Admin authentication | P0 | DONE |
| API-002 | Vessel CRUD | P0 | DONE |
| API-003 | Vessel verification workflow | P0 | DONE |
| API-004 | Public vessel list/detail | P0 | DONE |
| API-005 | Latest positions endpoint | P0 | DONE |
| API-006 | Internal whitelist endpoint | P0 | DONE |
| API-007 | Internal position ingestion endpoint | P0 | DONE |
| API-008 | History endpoint | P1 | DONE |
| API-009 | Port and route endpoints | P1 | DONE |
| API-010 | Worker heartbeat endpoint | P1 | DONE |
| API-011 | Geofence evaluation service | P1 | DONE |
| API-012 | Port events endpoint | P1 | DONE |
| API-013 | Reverb broadcasting | P1 | DONE |

## Worker

| ID | Task | Priority | Status |
|---|---|---:|---|
| WRK-001 | Define provider adapter | P0 | DONE |
| WRK-002 | Connect WebSocket provider | P0 | DONE |
| WRK-003 | Implement Zod schemas | P0 | DONE |
| WRK-004 | Implement whitelist cache | P0 | DONE |
| WRK-005 | Implement dedupe | P0 | DONE |
| WRK-006 | Implement backoff and jitter | P0 | DONE |
| WRK-007 | Deliver to internal API | P0 | DONE |
| WRK-008 | Heartbeat and metrics | P1 | DONE |
| WRK-009 | Graceful shutdown | P1 | DONE |

## Frontend

| ID | Task | Priority | Status |
|---|---|---:|---|
| FE-001 | App shell and routing | P0 | DONE |
| FE-002 | Design tokens and base components | P0 | DONE |
| MAP-001 | Initialize MapLibre | P0 | DONE |
| MAP-002 | Render vessel markers | P0 | DONE |
| MAP-003 | Freshness legend | P0 | DONE |
| MAP-004 | Vessel detail card | P0 | DONE |
| FE-003 | Search | P0 | DONE |
| FE-004 | Vessel detail page | P0 | DONE |
| FE-005 | History layer | P1 | DONE |
| FE-006 | Port and route pages | P1 | DONE |
| FE-007 | Admin layout | P0 | DONE |
| FE-008 | Landing hero dan CTA | P0 | DONE |
| FE-009 | Landing maritime scrollytelling | P1 | DONE |
| FE-010 | Landing transparency dan disclaimer | P0 | DONE |
| FE-011 | Landing responsive dan accessibility QA | P0 | DONE |
| FE-012 | Realtime WebSocket subscription | P1 | DONE |
| FE-013 | Port events page | P1 | DONE |

## Security and Ops

| ID | Task | Priority | Status |
|---|---|---:|---|
| SEC-001 | Internal API token | P0 | DONE |
| SEC-002 | Rate limiting | P0 | DONE |
| SEC-003 | CORS policy | P0 | DONE |
| SEC-004 | Security headers middleware | P0 | DONE |
| SEC-005 | Security regression test suite | P0 | DONE |
| OPS-001 | Nginx config | P1 | DONE |
| OPS-002 | Process supervisor | P1 | DONE |
| OPS-003 | Backup script | P1 | DONE |
| OPS-004 | Health monitoring (`/api/v1/status`) | P1 | DONE |
| OPS-005 | History retention scheduler | P1 | DONE |
| OPS-006 | JMeter load test plans | P1 | DONE |
| OPS-007 | PHPUnit performance smoke tests | P1 | DONE |
