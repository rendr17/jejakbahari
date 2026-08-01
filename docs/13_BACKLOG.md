# 13 — Product Backlog

Status: `PROPOSED`, `READY`, `IN_PROGRESS`, `BLOCKED`, `IN_REVIEW`, `DONE`, `CANCELLED`.

## Foundation

| ID | Task | Priority | Status |
|---|---|---:|---|
| FND-001 | Initialize monorepo folders | P0 | READY |
| FND-002 | Configure frontend lint/typecheck | P0 | READY |
| FND-003 | Configure backend formatter/test | P0 | READY |
| FND-004 | Configure worker lint/test | P0 | READY |
| FND-005 | Add CI workflow | P0 | READY |
| FND-006 | Add environment examples | P0 | READY |
| FND-007 | Define JejakBahari brand and UI baseline | P0 | DONE |

## Database

| ID | Task | Priority | Status |
|---|---|---:|---|
| DB-001 | Enable PostGIS extension | P0 | READY |
| DB-002 | Create operators table | P0 | READY |
| DB-003 | Create vessels table | P0 | READY |
| DB-004 | Create latest positions table | P0 | READY |
| DB-005 | Create history table and indexes | P0 | READY |
| DB-006 | Create ports and routes | P1 | READY |
| DB-007 | Create sources and evidence | P0 | READY |
| DB-008 | Create audit logs | P1 | READY |

## Backend

| ID | Task | Priority | Status |
|---|---|---:|---|
| API-001 | Admin authentication | P0 | READY |
| API-002 | Vessel CRUD | P0 | READY |
| API-003 | Vessel verification workflow | P0 | READY |
| API-004 | Public vessel list/detail | P0 | READY |
| API-005 | Latest positions endpoint | P0 | READY |
| API-006 | Internal whitelist endpoint | P0 | READY |
| API-007 | Internal position ingestion endpoint | P0 | READY |
| API-008 | History endpoint | P1 | READY |
| API-009 | Port and route endpoints | P1 | READY |
| API-010 | Worker heartbeat endpoint | P1 | READY |

## Worker

| ID | Task | Priority | Status |
|---|---|---:|---|
| WRK-001 | Define provider adapter | P0 | READY |
| WRK-002 | Connect WebSocket provider | P0 | READY |
| WRK-003 | Implement Zod schemas | P0 | READY |
| WRK-004 | Implement whitelist cache | P0 | READY |
| WRK-005 | Implement dedupe | P0 | READY |
| WRK-006 | Implement backoff and jitter | P0 | READY |
| WRK-007 | Deliver to internal API | P0 | READY |
| WRK-008 | Heartbeat and metrics | P1 | READY |
| WRK-009 | Graceful shutdown | P1 | READY |

## Frontend

| ID | Task | Priority | Status |
|---|---|---:|---|
| FE-001 | App shell and routing | P0 | DONE |
| FE-002 | Design tokens and base components | P0 | IN_PROGRESS |
| MAP-001 | Initialize MapLibre | P0 | READY |
| MAP-002 | Render vessel markers | P0 | READY |
| MAP-003 | Freshness legend | P0 | READY |
| MAP-004 | Vessel detail card | P0 | READY |
| FE-003 | Search | P0 | READY |
| FE-004 | Vessel detail page | P0 | READY |
| FE-005 | History layer | P1 | READY |
| FE-006 | Port and route pages | P1 | READY |
| FE-007 | Admin layout | P0 | READY |
| FE-008 | Landing hero dan CTA | P0 | DONE |
| FE-009 | Landing maritime scrollytelling | P1 | READY |
| FE-010 | Landing transparency dan disclaimer | P0 | READY |
| FE-011 | Landing responsive dan accessibility QA | P0 | READY |

## Security and Ops

| ID | Task | Priority | Status |
|---|---|---:|---|
| SEC-001 | Internal API token | P0 | READY |
| SEC-002 | Rate limiting | P0 | READY |
| SEC-003 | CORS policy | P0 | READY |
| OPS-001 | Nginx config | P1 | READY |
| OPS-002 | Process supervisor | P1 | READY |
| OPS-003 | Backup script | P1 | READY |
| OPS-004 | Health monitoring | P1 | READY |
