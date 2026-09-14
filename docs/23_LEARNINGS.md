# 23 — Learnings

Dokumen ini menyimpan pembelajaran reusable yang telah divalidasi.

## Cara Menambah

```markdown
## LRN-YYYYMMDD-001 — Judul

**Tanggal:** YYYY-MM-DD  
**Area:** Frontend | Backend | Worker | Database | Infrastructure | Data  
**Status:** Validated | Needs Verification | Superseded  
**Sumber:** Issue, test, dokumentasi, atau eksperimen

**Masalah:**

**Temuan:**

**Dampak:**

**Aturan ke depan:**

**Referensi:**
```

## Baseline Learnings

### LRN-20260801-001 — AIS bukan sumber identitas tunggal

**Tanggal:** 2026-08-01  
**Area:** Data  
**Status:** Validated  
**Sumber:** Requirement proyek dan karakteristik umum AIS

**Masalah:** Static AIS dapat tidak lengkap atau tidak konsisten.

**Temuan:** Kapal RoRo harus ditentukan melalui whitelist dan evidence tambahan.

**Dampak:** Worker hanya memproses MMSI verified.

**Aturan ke depan:** Jangan mengklasifikasikan kapal publik hanya dari ship type AIS.

### LRN-20260801-002 — Latest position dan history harus dipisah

**Tanggal:** 2026-08-01  
**Area:** Database  
**Status:** Validated

**Masalah:** Query peta akan mahal jika membaca tabel histori.

**Temuan:** Satu row latest per vessel membuat query publik lebih sederhana.

**Aturan ke depan:** Upsert latest dan sampling history pada alur ingestion.

### LRN-20260801-003 — Deklarasi font harus disertai aset font

**Tanggal:** 2026-08-01  
**Area:** Frontend  
**Status:** Validated  
**Sumber:** Inspeksi computed style referensi dan production build frontend

**Masalah:** Menuliskan nama font di `font-family` tidak memuat font tersebut dan dapat diam-diam memakai fallback sistem.

**Temuan:** Variable font Plus Jakarta Sans dan JetBrains Mono dapat di-host bersama aplikasi dengan hanya membawa subset Latin yang dipakai.

**Dampak:** Tipografi konsisten tanpa request font pihak ketiga saat runtime.

**Aturan ke depan:** Font desain wajib memiliki sumber aset eksplisit dan hasil build harus diperiksa agar tidak membawa subset yang tidak diperlukan.

### LRN-20260802-001 — Toolchain backend dapat diisolasi dengan container resmi

**Tanggal:** 2026-08-02
**Area:** Infrastructure
**Status:** Validated
**Sumber:** Scaffolding Laravel 13 melalui image Composer resmi pada host tanpa PHP/Composer

**Masalah:** Host pengembangan tidak selalu memiliki versi PHP dan Composer yang sesuai baseline backend.

**Temuan:** Image Composer resmi dapat membuat skeleton Laravel dan lockfile secara reproducible tanpa memasang toolchain PHP global.

**Dampak:** Setup backend tetap dapat dimulai pada host yang hanya memiliki Docker.

**Aturan ke depan:** Gunakan toolchain native bila tersedia; gunakan container resmi dengan versi terkunci sebagai fallback, dan tetap validasi migration pada PostGIS nyata.

### LRN-20260910-001 — Lockfile Laravel 13 dengan Symfony 8.x membutuhkan PHP 8.4

**Tanggal:** 2026-09-10
**Area:** Backend
**Status:** Validated
**Sumber:** Validasi Sprint 0 — `composer install` pada PHP 8.3 gagal, PHP 8.4 lulus

**Masalah:** `composer.lock` yang dihasilkan oleh Laravel 13 dapat mengunci Symfony 8.x yang membutuhkan PHP >=8.4.1, sementara `composer.json` awal menyatakan `^8.3`.

**Temuan:**
- `composer install` pada PHP 8.3 gagal dengan 17 constraint violations dari komponen Symfony 8.x.
- `composer install` pada PHP 8.4 lulus tanpa perubahan paket.
- CI workflow awal menggunakan PHP 8.3 dan akan gagal.

**Dampak:** PHP constraint `composer.json` dan CI workflow diperbarui ke `^8.4` / `8.4`.

**Aturan ke depan:** Setelah menjalankan `composer install` atau `composer update`, verifikasi bahwa PHP version constraint di `composer.json` dan CI workflow konsisten dengan versi yang dipakai. Jangan biarkan lockfile dan deklarasi platform berbeda.

### LRN-20260910-002 — Worker memerlukan .prettierignore untuk lockfile

**Tanggal:** 2026-09-10
**Area:** Worker
**Status:** Validated
**Sumber:** Validasi Sprint 0 — `pnpm format:check` gagal pada `pnpm-lock.yaml`

**Masalah:** Tanpa `.prettierignore`, `pnpm format:check` memeriksa `pnpm-lock.yaml` dan melaporkan style violation.

**Temuan:** Frontend tidak memiliki `.prettierignore` tetapi lockfile-nya sudah terformat. Worker perlu `.prettierignore` dengan `pnpm-lock.yaml`, `dist`, `node_modules`, dan `coverage`.

**Aturan ke depan:** Setiap service pnpm baru harus memiliki `.prettierignore` yang mengecualikan lockfile, build output, dan dependency directory.

### LRN-20260910-003 — league/commonmark <2.10 memiliki 10 security advisories

**Tanggal:** 2026-09-10
**Area:** Backend
**Status:** Validated
**Sumber:** `composer audit` setelah `composer install` pada Sprint 0

**Masalah:** `league/commonmark` 2.8.3 (transitive dependency Laravel) memiliki 10 security advisories (DoS dan XSS).

**Temuan:** Update ke 2.10.1 menghilangkan semua advisories. `composer update league/commonmark --with-dependencies` cukup.

**Aturan ke depan:** Setelah `composer install`, selalu jalankan `composer audit` dan perbaiki advisories sebelum melanjutkan.

### LRN-20260910-004 — SQLite tidak mendukung ALTER TABLE ADD CONSTRAINT

**Tanggal:** 2026-09-10
**Area:** Database
**Status:** Validated
**Sumber:** Sprint 1 feature tests — migration gagal di SQLite dengan `ALTER TABLE ADD CONSTRAINT`

**Masalah:** Migration menggunakan `DB::statement('ALTER TABLE ... ADD CONSTRAINT ... CHECK (...)')` untuk validasi range dan enum. SQLite tidak mendukung syntax ini.

**Temuan:** Bungkus semua `ALTER TABLE ADD CONSTRAINT` dalam `if (DB::getDriverName() === 'pgsql')`. Validasi tetap berjalan di PostgreSQL produksi, sementara SQLite digunakan untuk feature tests yang cepat.

**Dampak:** Feature tests dapat berjalan di SQLite in-memory tanpa PostGIS, sementara PostGIS test tetap berjalan terpisah.

**Aturan ke depan:** Pisahkan DDL PostgreSQL-specific (CHECK constraints, PostGIS columns, GIST indexes) dari DDL generik. Gunakan conditional berdasarkan driver.

### LRN-20260910-005 — Laravel 13 authorizeResource tidak kompatibel dengan controller constructor

**Tanggal:** 2026-09-10
**Area:** Backend
**Status:** Validated
**Sumber:** Sprint 1 — `Call to undefined method Controller::middleware()` saat menggunakan `authorizeResource`

**Masalah:** `AuthorizesRequests::authorizeResource()` memanggil `$this->middleware()` di constructor, yang tidak tersedia di Laravel 13 controller base class.

**Temuan:** Gunakan manual `$this->authorize('action', Model::class)` di setiap method controller alih-alih `authorizeResource` di constructor.

**Aturan ke depan:** Hindari `authorizeResource` di Laravel 13. Panggil `$this->authorize()` manual di setiap method untuk kontrol yang eksplisit.

### LRN-20260910-006 — Stringable object selalu truthy di when()

**Tanggal:** 2026-09-10
**Area:** Backend
**Status:** Validated
**Sumber:** Sprint 1 — query filter `when($request->string('q')->trim(), ...)` menambahkan kondisi empty string

**Masalah:** `$request->string('key')` mengembalikan `Stringable` object yang selalu truthy, sehingga `when()` selalu mengeksekusi callback bahkan saat parameter kosong.

**Temuan:** Gunakan `$request->filled('key')` sebagai kondisi `when()`, lalu akses nilai di dalam callback.

**Aturan ke depan:** Jangan gunakan `$request->string('key')` sebagai kondisi boolean. Gunakan `$request->filled('key')` atau `$request->has('key')`.

### LRN-20260910-007 — Stringable tidak identik dengan string di perbandingan strict

**Tanggal:** 2026-09-10
**Area:** Backend
**Status:** Validated
**Sumber:** Sprint 1 — validasi `public_visible` di Form Request gagal karena `Stringable !== 'VERIFIED'` selalu true

**Masalah:** `$this->string('verification_status')` mengembalikan `Stringable` object. Perbandingan `$status !== 'VERIFIED'` selalu `true` karena `Stringable` tidak identik dengan `string` (tipe berbeda).

**Temuan:** Gunakan `->toString()` untuk konversi ke `string` sebelum perbandingan strict: `$this->string('key')->toString() !== 'value'`.

**Aturan ke depan:** Selalu konversi `Stringable` ke `string` dengan `->toString()` sebelum perbandingan `===` atau `!==` dengan string literal.

### LRN-20260910-008 — Route-model binding mengembalikan Model, bukan ID

**Tanggal:** 2026-09-10
**Area:** Backend
**Status:** Validated
**Sumber:** Sprint 1 — `Vessel::find($this->route('vessel'))` gagal karena route parameter sudah berupa Model

**Masalah:** `$this->route('vessel')` mengembalikan instance `Vessel` (karena route-model binding), bukan UUID string. `Vessel::find(Model)` menyebabkan error atau query yang salah.

**Temuan:** Gunakan `$this->route('vessel')` langsung sebagai Model, atau cek `instanceof` sebelum operasi:
```php
$vessel = $this->route('vessel');
$status = $vessel instanceof Vessel ? (string) $vessel->verification_status : '';
```

**Aturan ke depan:** Jangan bungkus `$this->route('param')` dengan `Model::find()` ketika route-model binding aktif. Akses langsung sebagai Model instance.

### LRN-20260910-009 — Carbon diffInSeconds dapat return negatif

**Tanggal:** 2026-09-10
**Area:** Backend
**Status:** Validated
**Sumber:** Sprint 2 — position ingestion stale check gagal karena diffInSeconds return negatif

**Masalah:** `Carbon::now()->diffInSeconds($pastTimestamp)` dapat return nilai negatif di beberapa versi Carbon, sehingga perbandingan `$ageSeconds > $maxAgeSeconds` tidak pernah true untuk pesan stale.

**Temuan:** Gunakan `abs(Carbon::now()->getTimestamp() - $sourceTimestamp->getTimestamp())` untuk perhitungan age yang reliable.

**Aturan ke depan:** Untuk perhitungan age/duration di Laravel, gunakan `getTimestamp()` subtraction dengan `abs()` alih-alih `diffInSeconds()`.

### LRN-20260910-010 — Eloquent auto-pluralize table names tidak selalu cocok

**Tanggal:** 2026-09-10
**Area:** Backend
**Status:** Validated
**Sumber:** Sprint 2 — VesselPositionHistory model mencari tabel `vessel_position_histories` padahal tabel sebenarnya `vessel_position_history`

**Masalah:** Eloquent convention menpluralisasi nama model menjadi nama tabel (`VesselPositionHistory` -> `vessel_position_histories`), tetapi tabel migration menggunakan `vessel_position_history` (tidak dipluralisasi).

**Temuan:** Selalu specify `$table` property secara eksplisit di model ketika nama tabel tidak mengikuti convention Eloquent.

**Aturan ke depan:** Untuk tabel dengan nama irregular (tidak mengikuti plural convention), set `protected $table = 'nama_tabel';` di model.

### LRN-20260910-011 — MMSI nyata tidak boleh dikarang; verifikasi multi-sumber wajib

**Tanggal:** 2026-09-10
**Area:** Data
**Status:** Needs Verification
**Sumber:** Penyusunan `RealRoroVesselSeeder` dari agregator AIS publik

**Masalah:** MMSI adalah identifier 9-digit presisi. Mengarang angka MMSI akan menginjeksi data palsu ke registry yang justru tujuannya verifikasi multi-sumber, melanggar `docs/20_RORO_VESSEL_REGISTRY.md` §5.

**Temuan:**
- MMSI/IMO kapal RoRo Indonesia dapat diperoleh dari agregator AIS publik (VesselFinder, MagicPort, MarineLink, MaritimeOptima) dan situs resmi operator (asdp.id, Wikipedia fleet list).
- `525` adalah prefix MID Indonesia untuk MMSI kapal.
- Satu agregator = "sumber komunitas" (confidence 50–69, status REVIEW). Dua agregator independen yang konsisten, atau satu sumber yang menghubungkan nama+MMSI+IMO+operator+kategori RoRo, memenuhi syarat VERIFIED (confidence 72–80).
- Operator tidak boleh diasumsikan dari nama kapal saja (mis. FERRINDO adalah operator privat, bukan ASDP).

**Dampak:** `RealRoroVesselSeeder` mengisi 8 kapal nyata: 4 VERIFIED + public_visible (LAKAAN, ILELABALEKAN, FERRINDO 5, EIRENE), 4 REVIEW (NUSA AGUNG, NUSA MULIA, MUFIDAH, ILE MANDIRI). Target MVP 20 kapal belum tercapai.

**Aturan ke depan:**
- Jangan pernah mengarang MMSI/IMO. Setiap nilai harus ditautkan ke `RegistryEvidence` dengan `source_reference` nyata.
- Seeder registry harus idempotent (`updateOrCreate` by MMSI, `firstOrCreate` evidence by vessel+source+reference).
- Sebelum mempromosikan REVIEW → VERIFIED, tambahkan sumber independen kedua dan jalankan workflow verify via admin API.
- Atribusi dan terms setiap agregator harus dicatat di `data_sources`; periksa ulang terms sebelum redistribusi.

**Referensi:** `backend/database/seeders/RealRoroVesselSeeder.php`, `docs/19_DATA_SOURCE.md`, `docs/20_RORO_VESSEL_REGISTRY.md`

### LRN-20260910-012 — MapLibre GL JS v6: named imports, no default export

**Tanggal:** 2026-09-10
**Area:** Frontend
**Status:** Confirmed
**Sumber:** Implementasi Sprint 3 Public Map

**Masalah:** MapLibre GL JS v6 tidak lagi menyediakan default export. `import maplibregl from 'maplibre-gl'` menghasilkan `TS1192: has no default export`.

**Temuan:**
- Gunakan named imports: `import { Map, Marker, Popup } from 'maplibre-gl'`.
- `Map` dari maplibre-gl bentrok dengan `Map` JavaScript built-in. Alias: `import { Map as MapLibreMap } from 'maplibre-gl'` dan gunakan `globalThis.Map` untuk JS Map.
- CSS wajib diimport: `import 'maplibre-gl/dist/maplibre-gl.css'`.

**Aturan ke depan:** Selalu gunakan named imports untuk maplibre-gl v6+. Alias `MapLibreMap` untuk menghindari bentrok dengan `globalThis.Map`.

**Referensi:** `frontend/src/components/VesselMap.vue`

### LRN-20260910-013 — Freshness computation: source_timestamp-based, not received_at

**Tanggal:** 2026-09-10
**Area:** Backend
**Status:** Confirmed
**Sumber:** Implementasi FreshnessService Sprint 3

**Masalah:** Freshness (LIVE/DELAYED/STALE/OFFLINE) harus dihitung dari `source_timestamp` (waktu AIS asli), bukan `received_at` (waktu worker menerima). Jika dihitung dari `received_at`, delay jaringan atau queue masking akan menyembunyikan stale data.

**Temuan:**
- LIVE: source_timestamp ≤ 5 menit yang lalu.
- DELAYED: source_timestamp ≤ 30 menit yang lalu.
- STALE: source_timestamp ≤ 6 jam yang lalu.
- OFFLINE: source_timestamp > 6 jam atau tidak ada posisi.
- Threshold configurable via `config/positions.php` dan env vars.

**Aturan ke depan:** Freshness selalu berdasarkan `source_timestamp`. Jangan pernah menggunakan `received_at` untuk freshness display.

**Referensi:** `backend/app/Services/FreshnessService.php`, `config/positions.php`

---

### LRN-20260914-014: Public API detail resource pattern dengan evidence dan data source

**Tanggal:** 2026-09-14
**Area:** Backend
**Status:** Confirmed
**Sumber:** Implementasi Sprint 4 — Vessel detail

**Masalah:** Public vessel detail perlu menampilkan source summary dan verification status tanpa membocorkan data internal admin (reviewer, internal notes).

**Temuan:**
- Gunakan resource terpisah (`PublicVesselDetailResource`) untuk detail, bukan reuse `PublicVesselResource` yang lebih ringan untuk list.
- Eager-load `evidence.dataSource` untuk hindari N+1 saat menampilkan source summary.
- Exposure publik hanya: `evidence_type`, `source_reference`, `observed_value`, `confidence_score`, dan `data_source` (name, source_type, url, license_name, attribution_text).
- Jangan expose `reviewed_by` atau `reviewed_at` ke publik (internal admin info).
- `verification_status` boleh ditampilkan di detail (publik user perlu tahu status verifikasi), tapi tidak di list (terlalu verbose).

**Aturan ke depan:** Pisahkan resource untuk list (ringan) dan detail (lengkap). Eager-load nested relationships untuk hindari N+1.

**Referensi:** `backend/app/Http/Resources/PublicVesselDetailResource.php`, `backend/app/Http/Controllers/Api/Public/PublicVesselController.php`

---

### LRN-20260914-015: Search component debounce dan keyboard navigation pattern

**Tanggal:** 2026-09-14
**Area:** Frontend
**Status:** Confirmed
**Sumber:** Implementasi Sprint 4 — VesselSearch

**Masalah:** Search perlu debounce 250-400ms, minimal 2 karakter, keyboard accessible (Arrow/Enter/Escape), dan menangani loading/empty/error states.

**Temuan:**
- Debounce 300ms dengan `setTimeout` + `clearTimeout` di `watch`.
- `hasQuery` computed: `query.value.trim().length >= 2` untuk validasi minimal 2 karakter.
- Keyboard navigation: `ArrowDown`/`ArrowUp` untuk navigasi hasil, `Enter` untuk pilih, `Escape` untuk tutup.
- `activeIndex` tracking dengan `aria-selected` untuk screen reader.
- `handleBlur` dengan `setTimeout(200)` untuk allow click result sebelum dropdown tertutup.
- Empty state membedakan "belum mengetik", "tidak ditemukan", dan "gagal memuat".

**Aturan ke depan:** Gunakan pattern ini untuk semua search component. Jangan lupa `aria-label`, `role=listbox/option`, dan `sr-only` label.

**Referensi:** `frontend/src/components/VesselSearch.vue`

## LRN-20260914-016 — Geofence radius-based detection dengan hysteresis cooldown

**Tanggal:** 2026-09-14  
**Area:** Backend  
**Status:** Validated  
**Sumber:** Sprint 6 implementation

**Masalah:** Geofence evaluation perlu mencegah event berulang dari noise posisi AIS.

**Temuan:**
- Radius-based detection (Haversine distance to port center) cukup untuk MVP.
- Cooldown window 30 menit per vessel-port pair mencegah event berulang.
- State machine: ENTERED → ARRIVED (low speed) → DEPARTED (high speed) → EXITED (outside).
- GeofenceService sebagai singleton dengan config-driven thresholds.

**Aturan ke depan:** Gunakan cooldown untuk semua event detection. Polygon geofence ditangguhkan ke post-MVP.

**Referensi:** `backend/app/Services/GeofenceService.php`

## LRN-20260914-018 — Polygon geofence dengan radius fallback

**Tanggal:** 2026-09-14  
**Area:** Backend  
**Status:** Validated  
**Sumber:** Sprint 6 hardening

**Masalah:** Spec meminta polygon geofence (paling akurat) tapi MVP hanya butuh radius.

**Temuan:**
- GeofenceService cek `geofence_geometry` (polygon) dulu, fallback ke `geofence_radius_m` (radius).
- PostGIS `ST_Contains` untuk polygon point-in-polygon test.
- Haversine untuk radius distance calculation (driver-agnostic, works on SQLite tests).
- `detection_method` di PortEvent: `POLYGON` atau `RADIUS` — traceability untuk audit.
- Port model saving hook: `geofence_polygon` virtual attribute → PostGIS `ST_MakePolygon`.
- SQLite tests tidak bisa test polygon (no PostGIS), tapi radius fallback testable.

**Aturan ke depan:** Selalu sediakan radius fallback untuk polygon geofence. Test radius path di SQLite, polygon path di PostgreSQL CI.

**Referensi:** `backend/app/Services/GeofenceService.php`, `backend/app/Models/Port.php`

## LRN-20260914-019 — Event naming alignment ke spec

**Tanggal:** 2026-09-14  
**Area:** Backend  
**Status:** Validated  
**Sumber:** Sprint 6 hardening

**Masalah:** DB menggunakan `ENTERED`/`EXITED` tapi spec `21_PORT_GEOFENCE_SPEC.md` menggunakan `ENTERED_GEOFENCE`/`EXITED_GEOFENCE`.

**Temuan:**
- Constants di PortEvent model adalah single source of truth — semua kode dan tests pakai constants.
- Migration CHECK constraint harus update: `'ENTERED_GEOFENCE', 'ARRIVED', 'DEPARTED', 'EXITED_GEOFENCE'`.
- Frontend label map dan type union harus update sinkron.
- Tidak ada data migration needed (fresh DB di CI, belum ada production data).

**Aturan ke depan:** Selalu gunakan constants untuk event types, bukan string literals. Update spec, DB constraint, dan frontend type sinkron.

**Referensi:** `backend/app/Models/PortEvent.php`, `docs/21_PORT_GEOFENCE_SPEC.md`

## LRN-20260914-017 — Reverb WebSocket dengan REST fallback pattern

**Tanggal:** 2026-09-14  
**Area:** Frontend  
**Status:** Validated  
**Sumber:** Sprint 6 implementation

**Masalah:** Realtime WebSocket bisa terputus; perlu fallback ke REST polling.

**Temuan:**
- Composable `useRealtimePositions` mengelola WebSocket lifecycle dengan exponential backoff.
- Setelah 5 reconnect attempts gagal, fallback ke REST polling 30 detik.
- REST resync 60 detik tetap berjalan bahkan saat WebSocket connected (belt-and-suspenders).
- Status indicator (connecting/connected/disconnected/fallback) memberi feedback ke user.

**Aturan ke depan:** Selalu sediakan REST fallback untuk WebSocket. Jangan andalkan koneksi realtime saja.

**Referensi:** `frontend/src/composables/useRealtimePositions.ts`
