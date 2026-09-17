# 25 — Development Progress

**Terakhir diperbarui:** 2026-09-10  
**Status produk:** Sprint 1 registry dan admin dasar selesai; MVP belum dapat dijalankan end-to-end.

Dokumen ini adalah checklist eksekusi. Requirement tetap mengikuti `01_PRD.md` dan `02_MVP.md`, sedangkan ID task dan status formal tetap mengikuti `13_BACKLOG.md`.

## 1. Ringkasan Kondisi Saat Ini

- [x] Frontend Vue 3, Vite, TypeScript, Tailwind CSS, dan Vue Router tersedia.
- [x] App shell, landing hero, maritime scrollytelling, transparansi data, responsive QA, accessibility, metadata sosial, dan ilustrasi lintasan tersedia.
- [x] Formatter, lint, typecheck, unit test routing, dan production build frontend tersedia.
- [x] Skeleton backend Laravel 13 dan dependency lock tersedia.
- [x] Skeleton AIS worker Node.js/TypeScript dengan validasi environment dan lockfile tersedia.
- [x] Docker Compose PostGIS dan migration schema inti tersedia.
- [x] Migration PostGIS dan quality gate backend/worker/frontend tervalidasi.
- [x] API admin: autentikasi, CRUD operator/vessel/data-source, verification workflow, dan audit log.
- [ ] Live map, data AIS, dan deployment belum tersedia.

## 2. Landing Page

### Phase 1 — Foundation `DONE`

- [x] Inisialisasi frontend Vue 3 + Vite + TypeScript.
- [x] Integrasi Tailwind CSS.
- [x] Routing `/` dan placeholder `/peta`.
- [x] App shell: header, navigasi desktop, menu mobile native, dan footer.
- [x] Design tokens warna dan typography baseline.
- [x] Metadata title, description, language, dan theme color.
- [x] Script format, lint, typecheck, test, dan build.

### Phase 2 — Hero `DONE`

- [x] Headline dan deskripsi produk.
- [x] CTA `Lihat peta kapal` dan `Tentang data`.
- [x] Badge transparansi usia dan sumber data.
- [x] Ilustrasi lintasan maritim berbasis CSS tanpa dependency visual tambahan.
- [x] Label bahwa data visual adalah ilustrasi, bukan posisi aktual.
- [x] Disclaimer bahwa produk bukan alat navigasi atau keselamatan.
- [x] Plus Jakarta Sans dan JetBrains Mono di-host bersama aplikasi.

### Phase 3 — Maritime Scrollytelling `DONE`

- [x] Section `Posisi Kapal dalam Satu Peta`.
- [x] Section `Tahu Seberapa Baru Datanya`.
- [x] Section `Hanya Kapal RoRo Terverifikasi`.
- [x] Section `Pelabuhan dan Lintasan yang Relevan`.
- [x] Section `Riwayat Perjalanan Hingga 24 Jam`.
- [x] Visual sticky pada desktop dengan alur satu kolom pada mobile.
- [x] Navigasi anchor menuju setiap section yang benar-benar tersedia.
- [x] Hindari parallax dan animasi yang menyiratkan posisi aktual.

### Phase 4 — Trust dan Data Transparency `DONE`

- [x] Penjelasan tekstual status `LIVE`, `DELAYED`, `STALE`, dan `OFFLINE`.
- [x] Contoh timestamp relatif dan absolut.
- [x] Penjelasan whitelist MMSI dan verifikasi multi-sumber.
- [x] Penjelasan provenance, confidence, dan keterbatasan AIS.
- [x] Disclaimer dasar tersedia pada hero dan footer.
- [x] CTA akhir menuju peta.
- [x] Status dapat dipahami tanpa bergantung pada warna.

### Phase 5 — Responsive, Accessibility, dan QA `DONE`

- [x] Target sentuh CTA dan navigasi mobile minimum 44 px.
- [x] Focus ring dasar tersedia.
- [x] `prefers-reduced-motion` tersedia.
- [x] Visual QA pada viewport mobile, tablet, dan desktop.
- [x] Audit keyboard navigation dan urutan fokus.
- [x] Audit kontras WCAG AA.
- [x] Uji loaded shell tanpa server; cold offline/PWA tetap di luar scope MVP.
- [x] Uji stabilitas layout setelah font lokal selesai dimuat.
- [x] Browser smoke test untuk landing dan navigasi ke `/peta`.
- [x] Metadata sosial dan favicon final tersedia.

## 3. Fase Implementasi MVP

### Sprint 0 — Foundation `DONE`

- [x] Folder dan tooling frontend.
- [x] Baseline identitas dan UI JejakBahari.
- [x] Landing Phase 1–5.
- [x] Folder dan tooling backend.
- [x] Folder dan konfigurasi tooling AIS worker.
- [x] Docker Compose PostgreSQL/PostGIS lokal.
- [x] Migration awal dan constraint penting tersedia dan tervalidasi.
- [x] `.env.example` tanpa secret produksi untuk setiap service.
- [x] Workflow CI untuk format, lint, typecheck, test, dan build tersedia dan tervalidasi.
- [x] Worker lockfile (`pnpm-lock.yaml`) tersedia.
- [x] Migration PostGIS tervalidasi terhadap `postgis/postgis:17-3.5`.
- [x] Backend quality gate lulus: Pint (28 files), 3 tests (14 assertions).
- [x] Worker quality gate lulus: format, lint, typecheck, test, build.
- [x] Frontend quality gate lulus: format, lint, typecheck, test, build.
- [x] Security advisories `league/commonmark` diperbaiki (2.8.3 → 2.10.1).
- [x] PHP version constraint diperbarui ke `^8.4` untuk kompatibilitas Symfony 8.x.

**Exit gap:** tidak ada. Semua proyek build dan test baseline lulus.

### Sprint 1 — Registry dan Admin Dasar `DONE`

- [x] Autentikasi dan authorization admin/reviewer (Sanctum token, login/logout/me).
- [x] CRUD operator (model, form request, resource, controller, policy).
- [x] CRUD vessel (model, form request, resource, controller, policy).
- [x] CRUD data source (model, form request, resource, controller, policy).
- [x] CRUD registry evidence (nested under vessel, model, form request, resource, controller, policy).
- [x] Verification status workflow (verify/reject endpoints dengan reason).
- [x] Audit log dasar (service class, log semua aksi admin).
- [x] Migration SQLite-compatible untuk feature tests.
- [x] Login rate limiting (throttle:5,1).
- [x] Token abilities berbasis role.
- [x] public_visible validation (hanya vessel VERIFIED yang dapat dipublikasi).
- [x] 49 feature tests lulus (151 assertions) di SQLite; PostGIS test lulus di PostgreSQL.
- [x] `RealRoroVesselSeeder` mengisi 8 kapal RoRo/RoPax Indonesia nyata dengan MMSI + evidence multi-sumber (4 VERIFIED + public_visible, 4 REVIEW). Lihat `docs/23_LEARNINGS.md` LRN-20260910-011.
- [x] Seeder diperluas untuk total **20 kapal** (6 VERIFIED + public_visible, 14 REVIEW). Semua MMSI berasal dari sumber publik dengan `RegistryEvidence`. Lihat `backend/database/seeders/RealRoroVesselSeeder.php`.
- [ ] Verifikasi seed dan rebuild backend image tertunda karena Docker Desktop tidak tersedia (pipeline API npipe gagal). Jalankan manual per instruksi di laporan agent.

**Exit gap:** Admin dapat membuat kapal terverifikasi dengan MMSI dan evidence multi-sumber. Kode seeder mencakup target MVP 20 kapal, namun seed di database dan rebuild image belum terverifikasi.

### Sprint 2 — AIS Worker `DONE`

- [x] Backend internal API: vessel-whitelist, positions, worker-heartbeat endpoints.
- [x] Internal token middleware (SEC-001) dengan hash_equals comparison.
- [x] Provider adapter interface dan koneksi WebSocket.
- [x] Validasi dan normalisasi payload dengan Zod.
- [x] Whitelist cache dengan version hash dan refresh.
- [x] Deduplication berbasis message id / mmsi+timestamp+coords.
- [x] Stale dan out-of-order message handling di backend.
- [x] Reconnect, exponential backoff dengan jitter.
- [x] Delivery ke internal API dengan retry terbatas (3x, 5xx/network only).
- [x] Heartbeat setiap 30 detik dengan status HEALTHY/DEGRADED/DISCONNECTED.
- [x] Graceful shutdown (SIGTERM/SIGINT) dengan queue flush dan heartbeat final.
- [x] Worker metrics: messages received, invalid, unknown MMSI, duplicate, delivered, failures.
- [x] 59 backend feature tests (184 assertions) lulus di SQLite; PostGIS test lulus di PostgreSQL.
- [x] 74 worker tests lulus (12 test files).

**Exit gap:** tidak ada. Posisi AIS dari provider dapat diterima, divalidasi, difilter whitelist, dan disimpan ke backend. Worker siap untuk koneksi provider AIS nyata. Mock provider tersedia untuk development tanpa API key.

### Sprint 3 — Public Map `DONE`

- [x] Route `/peta` dengan MapLibre GL JS dan basemap gelap.
- [x] Public vessel list endpoint (`GET /api/v1/vessels`) dengan search, pagination, status filter, dan freshness.
- [x] Public vessel detail endpoint (`GET /api/v1/vessels/{id}`) dengan 404 untuk private/inactive vessel.
- [x] Latest positions endpoint (`GET /api/v1/positions/latest`) dengan bbox, operator_id, dan freshness filter.
- [x] FreshnessService: LIVE/DELAYED/STALE/OFFLINE berdasarkan source_timestamp (singleton, config-driven).
- [x] Rate limiting `throttle:60,1` untuk public endpoints (SEC-002).
- [x] CORS policy config (SEC-003).
- [x] Vessel markers dengan heading arrow (fallback: heading → COG → utara).
- [x] Freshness legend dengan warna (success/warning/attention/neutral sesuai design system).
- [x] Vessel popup dan detail card dengan nama, MMSI, SOG, COG, tujuan, timestamp.
- [x] Loading, empty, error, dan auto-refresh 30 detik.
- [x] Tile-failure fallback: daftar kapal + pesan error saat tile gagal.
- [x] Accessibility: marker keyboard-navigable, aria-label, role=status/alert, prefers-reduced-motion.
- [x] Mobile-first: vessel card sebagai bottom card di mobile, panel di desktop.
- [x] Design tokens: --color-danger, --color-map-water, --color-map-land, --color-map-route, --color-map-port.
- [x] Security: filter active=true, no verification_status exposure, XSS-safe popup (setDOMContent).
- [x] 14 public API tests (40 assertions) lulus di PostgreSQL.
- [x] 75 total backend tests (235 assertions) lulus.
- [x] Frontend typecheck, lint, format, test, build lulus.

**Exit gap:** Pengguna dapat melihat posisi terakhir kapal di peta dengan status kesegaran data. Kontrol lokasi/reset bearing dan clustering low-zoom belum diimplementasi (ditangguhkan ke Sprint 4/5). Port dan route layer belum diimplementasi (Sprint 5).

### Sprint 4 — Search dan Detail `DONE`

- [x] Public vessel detail endpoint dengan source summary, verification, dan evidence.
- [x] PublicVesselDetailResource: latest_position, verification status, confidence_score, evidence dengan data_source.
- [x] VesselSearch component: debounce 300ms, minimal 2 karakter, keyboard navigation (Arrow/Enter/Escape), grouped results.
- [x] VesselListPage: grid responsif, freshness badge, pagination, loading/empty/error states.
- [x] VesselDetailPage: posisi terakhir, identitas, verifikasi, sumber bukti, disclaimer, sidebar ringkasan.
- [x] FreshnessBadge component reusable untuk list dan detail.
- [x] Router: /kapal (list) dan /kapal/:id (detail) dengan navigasi di header.
- [x] Accessibility: sr-only label, role=listbox/option, aria-selected, keyboard navigable cards.
- [x] Responsive: grid 1-2-3 kolom di list, 2 kolom di detail dengan sticky sidebar.
- [x] 3 backend tests baru untuk detail (verification, evidence, latest_position).
- [x] Frontend typecheck, lint, format, test, build lulus.

**Exit gap:** Pengguna dapat menemukan dan memahami satu kapal dengan sumber dan status verifikasi. Search global belum mendukung grouping pelabuhan/operator (port belum ada, Sprint 5). History layer belum diimplementasi (Sprint 5).

### Sprint 5 — History dan Ports `DONE`

- [x] Histori posisi maksimum 24 jam dan 2.000 titik dengan sampling.
- [x] History API (`GET /api/v1/vessels/{id}/positions/history`) dengan from/to/limit.
- [x] MapLibre HistoryLayer component untuk vessel position history.
- [x] CRUD pelabuhan (Port model, factory, policy, controller, form request, resource).
- [x] CRUD lintasan (Route model, factory, policy, controller, form request, resource).
- [x] Public port dan route endpoints (`GET /api/v1/ports`, `/routes`).
- [x] Port markers dan route lines di MapPage (PortLayer + RouteLayer integrated).
- [x] PortListPage dan RouteListPage dengan pagination dan loading/empty/error states.
- [x] VesselDetailPage dengan history section (10 titik terbaru dari 24h).
- [x] Seeder 8 pelabuhan Indonesia + 4 lintasan.
- [x] 11 backend tests untuk port, route, dan history endpoints.
- [x] Frontend typecheck, lint, format, test, build lulus.
- [x] CI 3/3 jobs green.

**Exit gap:** Histori 24 jam dan konteks port tersedia. PortLayer/RouteLayer/HistoryLayer sudah di-integrate ke MapPage.

### Sprint 6 — Geofence dan Realtime `DONE`

- [x] Laravel Reverb installed dan broadcasting configured.
- [x] GeofenceService: evaluasi transisi port (ENTERED_GEOFENCE → ARRIVED → DEPARTED → EXITED_GEOFENCE) dengan polygon + radius fallback.
- [x] PortEvent model dengan factory dan relationship.
- [x] GeofenceService wired ke PositionIngestionController — setiap posisi AIS dievaluasi terhadap port geofence.
- [x] PositionUpdated broadcast event via Reverb channel `vessel-positions`.
- [x] PortEventDetected broadcast event via Reverb channel `port-events`.
- [x] Public port events endpoint (`GET /api/v1/ports/{id}/events`) dengan pagination.
- [x] Frontend WebSocket subscription dengan REST resync fallback (useRealtimePositions composable).
- [x] Realtime status indicator di MapPage (connecting/connected/disconnected/fallback).
- [x] PortEventListPage dengan event history dan color-coded event types.
- [x] 8 geofence service tests (entered, arrived, departed, exited, cooldown, inactive port, outside, radius fallback).
- [x] 4 public port event tests (list, 404 inactive, 404 unknown, pagination).
- [x] 4 polygon geofence tests (PostgreSQL/PostGIS: inside, outside, precedence, resource type).
- [x] 8 realtime composable tests (fallback, connect, receive, reconnect, max attempts, resync, malformed, non-position events).
- [x] Polygon geofence support (PostGIS ST_Contains) dengan radius fallback (Haversine).
- [x] Event naming aligned ke spec: ENTERED_GEOFENCE/EXITED_GEOFENCE.
- [x] Reverb deployment guide (Supervisor, systemd, Nginx WebSocket proxy).
- [x] Frontend typecheck, lint, format, test, build lulus.

**Exit gap:** Posisi dapat diperbarui tanpa refresh dan event pelabuhan tercatat. Cooldown hysteresis 30 menit mencegah event berulang. Polygon dan radius geofence didukung.

### Sprint 7 — Hardening dan Release `IN_PROGRESS`

- [x] Security headers middleware (nosniff, DENY, Referrer-Policy, Permissions-Policy) pada semua API response.
- [x] Internal API rate limit `throttle:3000,1` (headroom untuk 50/s burst) sebagai defense-in-depth.
- [x] Admin pagination cap 100 di semua endpoint admin (konsisten dengan public).
- [x] History retention command `positions:prune-history` + daily schedule 02:00 (chunked deletes).
- [x] Security test suite: 14 tests (auth, input validation, API protection, XSS, SQLi).
- [x] Bug fix: `$validated['sog_knots']` crash saat field optional tidak dikirim.
- [x] Public status endpoint `GET /api/v1/status` — pipeline OPERATIONAL/DEGRADED/DOWN + worker heartbeat freshness.
- [x] Worker heartbeat registry (`worker:heartbeat:index`) untuk multi-worker discovery.
- [x] Security checklist diperbarui dengan status dan referensi test.
- [x] Deployment docs: scheduler cron, monitoring endpoints, uptime recommendation.
- [x] Nginx production template (`deploy/nginx/jejakbahari.conf`) — HTTPS, rate limit zones, Reverb WS proxy, security headers.
- [x] Backup script (`scripts/backup-db.sh`) — pg_dump compressed, env-driven, retensi 7 hari.
- [x] PHPUnit performance smoke tests — 5 tests deterministik (query-count bounds, bukan wall-clock): latest 100 vessels, history cap 2000, whitelist bounded query, ingestion burst 50, prune 6500 rows.
- [x] Selenium suite dilengkapi — search/vessel-detail/admin page objects + 12 tests baru (search 5, detail 4, admin 3 gated). Eksekusi tetap butuh live env.
- [x] JMeter plans — 4 .jmx sesuai README (latest-positions, vessel-list, history, worker-burst). Eksekusi tetap butuh live env.
- [ ] Performance test execution (JMeter plans tersedia, butuh environment live).
- [ ] Selenium E2E execution (suite lengkap, butuh environment live).
- [ ] Production deployment actual (tooling lengkap: docker-compose, nginx template, backup script, verify-reverb.sh, docs).
- [ ] Audit secret, debug code, lisensi, serta dokumentasi akhir.

**Exit gap:** MVP belum siap demo atau deploy publik.

## 4. Gap Prioritas

Urutan berikut menjaga dependency tetap sederhana:

1. [x] Tutup gap Sprint 0: backend, worker, database, environment, dan CI.
2. [x] Bangun registry kapal terverifikasi sebelum menghubungkan provider AIS.
3. [ ] Implementasikan ingestion AIS dan latest position API.
4. [ ] Ganti placeholder `/peta` dengan public map berbasis data API.
5. [ ] Tambahkan search, detail, history, port, dan route.
6. [ ] Tambahkan geofence/realtime hanya setelah REST flow stabil.
7. [ ] Hardening, seed data tervalidasi, dan deployment.

## 5. Definition of Done MVP

- [ ] Peta, daftar, search, dan detail kapal bekerja.
- [ ] Semua posisi menampilkan timestamp, freshness, dan sumber.
- [ ] Minimal 20 kapal RoRo memiliki MMSI serta evidence terverifikasi.
- [ ] Worker bertahan dari disconnect dan payload invalid.
- [ ] Admin dapat mengelola serta memverifikasi master data.
- [ ] Histori 24 jam dan disclaimer tersedia.
- [ ] Format, lint, typecheck, test, build, dan security checks lulus.
- [ ] Tidak ada secret atau debug code di repository.
- [ ] Deployment dapat direplikasi dan dimonitor.
- [ ] Sprint, backlog, progress, learning, dan decision record sinkron.

## 6. Aturan Pembaruan

- Centang item hanya jika implementasi tersedia dan validasi relevan lulus.
- Tambahkan link issue/PR pada `13_BACKLOG.md`, bukan menduplikasinya di sini.
- Perbarui tanggal dan gap setiap kali satu phase atau sprint berubah status.
- Catat temuan reusable di `23_LEARNINGS.md` dan keputusan arsitektur di `24_DECISIONS.md`.
