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
- [x] Verification status workflow (verify/reject endpoints dengan reason).
- [x] Audit log dasar (service class, log semua aksi admin).
- [x] Migration SQLite-compatible untuk feature tests.
- [x] 38 feature tests lulus (116 assertions) di SQLite; PostGIS test lulus di PostgreSQL.

**Exit gap:** tidak ada. Admin dapat membuat kapal terverifikasi dengan MMSI.

### Sprint 2 — AIS Worker `NOT_STARTED`

- [ ] Provider adapter dan koneksi WebSocket.
- [ ] Validasi dan normalisasi payload dengan Zod.
- [ ] Whitelist, deduplication, stale, dan out-of-order handling.
- [ ] Reconnect, exponential backoff, jitter, dan heartbeat.
- [ ] Delivery ke internal API dengan retry terbatas.
- [ ] Graceful shutdown dan worker metrics.

**Exit gap:** belum ada aliran posisi AIS menuju sistem.

### Sprint 3 — Public Map `NOT_STARTED`

- [x] Route `/peta` tersedia sebagai placeholder.
- [ ] MapLibre dan basemap gelap.
- [ ] Latest positions endpoint.
- [ ] Vessel, port, dan route layer terpisah.
- [ ] Marker arah dengan fallback heading, COG, lalu utara.
- [ ] Freshness legend dan selected vessel card.
- [ ] Loading, empty, error, stale, offline, dan tile-failure state.
- [ ] Alternatif daftar kapal saat peta gagal.

**Exit gap:** placeholder belum menampilkan peta atau posisi kapal.

### Sprint 4 — Search dan Detail `NOT_STARTED`

- [ ] Search kapal, MMSI, IMO, operator, dan pelabuhan.
- [ ] Pengelompokan hasil serta loading, empty, dan error state.
- [ ] Daftar kapal.
- [ ] Detail kapal, sumber, status verifikasi, dan timestamp.
- [ ] Layout responsive dan navigasi keyboard.

**Exit gap:** pengguna belum dapat mencari atau memahami satu kapal.

### Sprint 5 — History dan Ports `NOT_STARTED`

- [ ] Histori posisi maksimum 24 jam dan sampling.
- [ ] History API dan MapLibre history layer.
- [ ] CRUD serta halaman pelabuhan dan lintasan.
- [ ] Port marker dan konteks lintasan kapal.

**Exit gap:** belum ada konteks perjalanan historis, pelabuhan, atau lintasan.

### Sprint 6 — Geofence dan Realtime `NOT_STARTED`

- [ ] Geometry/radius pelabuhan dan evaluasi geofence.
- [ ] Event `ENTERED`, `ARRIVED`, `DEPARTED`, dan `EXITED`.
- [ ] Reverb dan subscription frontend.
- [ ] REST resync setelah koneksi terputus.

**Exit gap:** belum ada event pelabuhan atau pembaruan tanpa refresh.

### Sprint 7 — Hardening dan Release `NOT_STARTED`

- [ ] Security review dan rate limiting.
- [ ] Test unit, integration, E2E, performance, dan failure mode.
- [ ] Minimal 20 kapal, 5 pelabuhan, dan 3 lintasan tervalidasi.
- [ ] Deployment frontend, backend, worker, dan database dapat direplikasi.
- [ ] Monitoring, backup, retention job, dan health check.
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
