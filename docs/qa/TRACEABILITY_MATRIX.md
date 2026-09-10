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
| API-004 | Public vessel list | TC-PUB-001 | API | NOT_RUN | — | Sprint 3 |
| API-005 | Latest positions | TC-PUB-002 | API | NOT_RUN | — | Sprint 3 |
| MAP-001 | MapLibre init | TC-MAP-001 | E2E | NOT_RUN | — | Sprint 3 |
| MAP-002 | Vessel markers | TC-MAP-002 | E2E | NOT_RUN | — | Sprint 3 |
| MAP-003 | Freshness legend | TC-MAP-003 | E2E | NOT_RUN | — | Sprint 3 |
| MAP-004 | Vessel card | TC-MAP-004 | E2E | NOT_RUN | — | Sprint 3 |
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
| Sprint 3 | 6 | 0 | 0 | 0 | 6 | 0% |
| Sprint 4 | 2 | 0 | 0 | 0 | 2 | 0% |
| Sprint 5 | 2 | 0 | 0 | 0 | 2 | 0% |
| Sprint 7 | 3 | 1 | 1 | 0 | 2 | 33% |

## Bug Log Summary

| Bug ID | Severity | Status | Related Req | Summary |
|--------|----------|--------|-------------|---------|
| — | — | — | — | Belum ada bug tercatat (Sprint 0–2 menggunakan test otomatis) |

## Cara Menggunakan

1. Tambah baris baru setiap kali ada requirement atau test case baru.
2. Update `Status` setelah test execution.
3. Catat `Bug ID` jika test case gagal dan bug dibuat di JIRA.
4. Update `Coverage Summary` di akhir setiap sprint.
5. Bug detail lihat di JIRA atau `docs/qa/BUG_REPORTS/`.
