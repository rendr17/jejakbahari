# Security Test Checklist — JejakBahari

Sesuai `14_SECURITY.md` dan `16_TESTING.md` §7.

## 1. Authentication & Authorization

- [x] Admin login menolak credential invalid — `SecurityTest::test_admin_login_rejects_invalid_credentials`
- [x] Admin login rate limit 5 attempt per menit — `SecurityTest::test_admin_login_is_rate_limited`
- [ ] Token expired ditolak *(manual: butuh wait expiry)*
- [ ] Role admin vs reviewer: reviewer tidak bisa delete *(belum ada role enforcement test)*
- [x] Endpoint admin menolak request tanpa token — `SecurityTest::test_admin_endpoints_reject_unauthenticated_requests`
- [x] Endpoint internal worker menolak token salah — `SecurityTest`, `InternalApiTest`
- [x] Token worker berbeda dari token admin — bearer token vs Sanctum, terpisah
- [ ] Logout membatalkan token aktif *(covered by AuthTest — verifikasi)*

## 2. Input Validation

- [x] MMSI harus tepat 9 digit — `SecurityTest::test_ingestion_rejects_invalid_mmsi`
- [x] Coordinate latitude [-90, 90], longitude [-180, 180] — `SecurityTest::test_ingestion_rejects_out_of_range_coordinates`
- [x] String AIS (destination, name) dibatasi panjangnya — `StorePositionRequest` max:200/max:60
- [ ] JSON payload size limit diterapkan *(Nginx `client_max_body_size` — dokumentasi di deployment)*
- [ ] Unknown fields ditolak/ diabaikan konsisten *(Laravel ignores unknown JSON keys by default)*
- [x] Form Request validasi semua input admin — StorePortRequest, StorePositionRequest, dll

## 3. API Protection

- [x] Rate limiting pada endpoint publik — `throttle:60,1`
- [x] Rate limiting pada internal worker endpoints — `throttle:3000,1` (headroom untuk 50/s burst)
- [x] CORS allowlist (bukan `*`) — env `CORS_ALLOWED_ORIGINS`, production harus set explicit domain
- [x] HTTPS wajib di produksi — Nginx config + `REVERB_SCHEME=wss`
- [x] Security headers — `SecurityHeaders` middleware (nosniff, DENY, Referrer-Policy, Permissions-Policy)
- [x] Pagination maksimum 100 per page — semua endpoint di-cap `min(per_page, 100)`
- [x] Error produksi tidak verbose — `APP_DEBUG=false` + `shouldRenderJsonWhen` untuk API

## 4. XSS & Injection

- [x] XSS string pada AIS destination/name tidak dieksekusi — `SecurityTest::test_xss_string_in_destination_stored_literally`; frontend render via text interpolation
- [x] XSS string pada vessel name tidak dieksekusi di popup — Vue `{{ }}` escapes by default; `highlightMatch` di VesselSearch escape HTML dulu
- [x] SQL injection pada search query ditolak — `SecurityTest::test_sql_injection_attempt_in_search_is_safe`; ORM parameterized
- [x] HTML rendering konten AIS di-escape — Vue default escaping; satu `v-html` di highlight sudah escape input

## 5. Worker Security

- [x] API key provider tidak di-log — worker logger tidak log env secrets
- [x] Credential di-redact dari log worker — config tidak masuk log payload
- [x] Queue memory dibatasi — worker queue depth limit ada di worker.ts
- [x] HTTP timeout untuk semua call ke backend — fetch timeout di heartbeat/delivery
- [x] Payload invalid tidak crash worker — Zod schema validation, per-message try/catch
- [ ] Oversized payload ditolak *(worker drops non-conforming; backend max field lengths)*
- [x] Internal endpoint rate limit — `throttle:3000,1` sebagai defense-in-depth

## 6. Database

- [x] Parameterized query / ORM — Eloquent + whereRaw dengan `?` bindings
- [ ] User database least privilege *(deployment concern — dokumentasi)*
- [x] Audit log mencatat semua aksi admin — audit_logs pada verify/reject/CRUD
- [x] Migration destructive butuh review — convention di AGENTS.md

## 7. Secrets

- [x] Tidak ada secret di repository — `.env` di `.gitignore`, `.env.example` kosong
- [x] Tidak ada API key di source code
- [x] `.env` di `.gitignore`
- [x] `.env.example` tidak berisi nilai rahasia
- [x] Token worker dan admin terpisah — `INTERNAL_WORKER_TOKEN` vs Sanctum tokens

## 8. Frontend

- [x] Tidak ada secret di frontend bundle — hanya `VITE_REVERB_APP_KEY` (public key by design)
- [ ] Token admin tidak di localStorage — **N/A untuk MVP**: admin UI belum diimplementasi (FE-007 READY). Saat admin UI dibuat, prefer httpOnly cookie atau minimal document risiko XSS→token theft.
- [ ] CSP header diterapkan *(Nginx-level, dokumentasi deployment)*
- [x] Konten AIS di-sanitize sebelum render — Vue escaping + explicit escape di highlight

## 9. Dependency

- [ ] Lockfile ada dan digunakan
- [ ] Dependency audit: `pnpm audit` / `composer audit`
- [ ] Tidak ada package dengan vulnerability tinggi
- [ ] Dependabot/Renovate aktif (opsional)

## 10. Incident Response Readiness

- [ ] Prosedur rotasi secret terdokumentasi
- [ ] Prosedur rollback terdokumentasi (`15_DEPLOYMENT.md` §10)
- [ ] Log retention dan backup tersedia

## Cara Eksekusi

1. Jalankan checklist ini sebelum setiap release (Sprint 7 hardening).
2. Untuk fitur baru, jalankan bagian relevan saat sprint tersebut.
3. Catat hasil di TestRail/QASE atau di JIRA sebagai task.
4. Bug security selalu P0 — fix sebelum release.

## Tools

- **Bruno**: test auth flow, input validation, API protection
- **Manual**: inspect log worker, check `.gitignore`, check frontend bundle
- **JMeter**: test rate limit dan oversized payload
- **CI**: dependency audit otomatis
