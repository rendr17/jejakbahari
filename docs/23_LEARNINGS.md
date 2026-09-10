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
