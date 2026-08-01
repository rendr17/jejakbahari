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
