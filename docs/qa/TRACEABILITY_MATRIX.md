# Traceability Matrix — JejakBahari

Memetakan requirement → test case → bug untuk memastikan setiap requirement teruji dan setiap bug terlacak.

## Legenda

- **Req ID**: ID dari `13_BACKLOG.md` atau section di `02_MVP.md`.
- **Test Case ID**: ID di TestRail/QASE (format: `TC-[MODULE]-[NNN]`).
- **Bug ID**: ID di JIRA (format: `JB-[NNN]`).
- **Status**: `PASS`, `FAIL`, `BLOCKED`, `NOT_RUN`.

## Matrix

| Req ID | Requirement | Test Case ID | Test Type | Status | Bug ID | Notes |
|--------|-------------|--------------|-----------|--------|--------|-------|
| API-001 | Admin authentication | TC-AUTH-001 | API | PASS | — | Login/logout/me |
| API-001 | Admin authentication | TC-AUTH-002 | API | PASS | — | Invalid credential rejected |
| API-001 | Admin authentication | TC-AUTH-003 | API | PASS | — | Rate limit 5x/min |
| API-002 | Vessel CRUD | TC-VESSEL-001 | API | PASS | — | Create vessel |
| API-002 | Vessel CRUD | TC-VESSEL-002 | API | PASS | — | Update vessel |
| API-002 | Vessel CRUD | TC-VESSEL-003 | API | PASS | — | Delete vessel |
| API-002 | Vessel CRUD | TC-VESSEL-004 | API | PASS | — | List with pagination |
| API-003 | Vessel verification | TC-VESSEL-005 | API | PASS | — | Verify vessel |
| API-003 | Vessel verification | TC-VESSEL-006 | API | PASS | — | Reject vessel with reason |
| API-003 | Vessel verification | TC-VESSEL-007 | API | PASS | — | public_visible only if VERIFIED |
| API-004 | Public vessel list | TC-PUB-001 | API | PASS | — | 200, 6 vessels |
| API-004 | Public vessel list | TC-PUB-002 | API | PASS | — | Search q=EIRENE, 1 result |
| API-004 | Public vessel list | TC-PUB-003 | API | PASS | — | Search q=525, 6 results |
| API-004 | Public vessel list | TC-PUB-004 | API | PASS | — | page=1, per_page=3 |
| API-004 | Public vessel list | TC-PUB-005 | API | PASS | — | per_page capped to 100 |
| API-004 | Public vessel list | TC-PUB-006 | API | PASS | — | operator filter, 5 results |
| API-004 | Public vessel list | TC-PUB-007 | API | PASS | — | Empty search, 0 results |
| API-004 | Public vessel list | TC-PUB-008 | API | PASS | — | LIVE + OFFLINE freshness |
| API-004 | Public vessel detail | TC-PUB-009 | API | PASS | JB-001 | Bug found & fixed |
| API-004 | Public vessel detail | TC-PUB-010 | API | PASS | — | 404 private vessel |
| API-004 | Public vessel detail | TC-PUB-011 | API | PASS | — | 404 non-existent UUID |
| API-005 | Latest positions | TC-PUB-012 | API | PASS | — | 1 position |
| API-005 | Latest positions | TC-PUB-013 | API | PASS | — | BBox Indonesia, 1 position |
| API-005 | Latest positions | TC-PUB-014 | API | PASS | — | BBox excludes, 0 positions |
| API-005 | Latest positions | TC-PUB-015 | API | PASS | — | Invalid bbox ignored |
| API-005 | Latest positions | TC-PUB-016 | API | PASS | — | freshness=LIVE |
| API-005 | Health check | TC-PUB-017 | API | SKIPPED | — | Endpoint not implemented |
| MAP-001 | MapLibre init | TC-MAP-001 | E2E | NOT_RUN | — | Sprint 3 — E2E pending |
| MAP-002 | Vessel markers | TC-MAP-002 | E2E | NOT_RUN | — | Sprint 3 — E2E pending |
| MAP-003 | Freshness legend | TC-MAP-003 | E2E | NOT_RUN | — | Sprint 3 — E2E pending |
| MAP-004 | Vessel card | TC-MAP-004 | E2E | NOT_RUN | — | Sprint 3 — E2E pending |
| FE-003 | Search | TC-SEARCH-001 | E2E | NOT_RUN | — | Sprint 4 |
| FE-004 | Vessel detail | TC-DETAIL-001 | E2E | NOT_RUN | — | Sprint 4 |
| FE-005 | History layer | TC-HIST-001 | API+E2E | NOT_RUN | — | Sprint 5 |
| FE-006 | Port & route | TC-PORT-001 | API+E2E | NOT_RUN | — | Sprint 5 |
| SEC-001 | Internal API token | TC-SEC-001 | Security | PASS | — | hash_equals comparison |
| SEC-002 | Rate limiting | TC-SEC-002 | Security | NOT_RUN | — | Sprint 7 |
| SEC-003 | CORS policy | TC-SEC-003 | Security | NOT_RUN | — | Sprint 7 |
| OPS-004 | Health monitoring | TC-OPS-001 | Manual | NOT_RUN | — | Sprint 7 |

## Coverage Summary

| Sprint | Total Req | Tested | Pass | Fail | Not Run | Coverage |
|--------|-----------|--------|------|------|---------|----------|
| Sprint 0 | 7 | 7 | 7 | 0 | 0 | 100% |
| Sprint 1 | 7 | 7 | 7 | 0 | 0 | 100% |
| Sprint 2 | 9 | 9 | 9 | 0 | 0 | 100% |
| Sprint 3 | 17 | 17 | 16 | 0 | 1 | 94% (16 PASS, 1 SKIP) |
| Sprint 4 | 2 | 0 | 0 | 0 | 2 | 0% |
| Sprint 5 | 2 | 0 | 0 | 0 | 2 | 0% |
| Sprint 7 | 3 | 1 | 1 | 0 | 2 | 33% |

## Bug Log Summary

| Bug ID | Severity | Status | Related Req | Summary |
|--------|----------|--------|-------------|---------|
| JB-001 | P0 | FIXED | API-004 | Vessel detail 500: whenLoaded() on Model (PublicVesselDetailResource) |

## Cara Menggunakan

1. Tambah baris baru setiap kali ada requirement atau test case baru.
2. Update `Status` setelah test execution.
3. Catat `Bug ID` jika test case gagal dan bug dibuat di JIRA.
4. Update `Coverage Summary` di akhir setiap sprint.
5. Bug detail lihat di JIRA atau `docs/qa/BUG_REPORTS/`.
