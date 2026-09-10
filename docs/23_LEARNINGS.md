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
