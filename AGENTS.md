# AGENTS.md

Dokumen ini adalah instruksi utama untuk seluruh AI coding agent yang bekerja pada JejakBahari.

## Aturan Utama

1. Baca `AGENTS.md`, `README.md`, `docs/01_PRD.md`, dan `docs/02_MVP.md` sebelum mengubah kode.
2. Baca dokumen teknis yang relevan dengan tugas.
3. Periksa skill yang tersedia dan selalu gunakan skill paling spesifik yang relevan dengan instruksi.
4. Baca instruksi skill sebelum menjalankan workflow atau tool yang diwajibkan skill tersebut.
5. Jangan menambahkan fitur di luar MVP tanpa persetujuan dan pembaruan dokumentasi.
6. Jangan mengganti stack, pola arsitektur, atau sumber data tanpa ADR di `docs/24_DECISIONS.md`.
7. Selalu menjalankan validasi yang proporsional: format, lint, typecheck, test, build, dan pemeriksaan keamanan.
8. Selalu sinkronkan implementasi dengan dokumentasi, sprint, dan backlog.
9. Jangan menyimpan secret, credential, atau API key di repository.
10. Tinggalkan repository dalam kondisi lebih baik daripada sebelum dikerjakan.

## Penggunaan Skill

Sebelum mulai:

1. Pahami output yang diminta.
2. Identifikasi skill yang tersedia.
3. Pilih skill paling spesifik.
4. Baca `SKILL.md` atau `skill.md` skill tersebut.
5. Ikuti workflow dan validasi skill.
6. Gunakan beberapa skill hanya jika tugas memang mencakup beberapa domain.

Contoh:

- Audit repository: gunakan skill codebase review.
- Optimasi performa: gunakan skill performance/instant app optimizer.
- Spreadsheet: gunakan skill spreadsheet.
- Presentasi: gunakan skill slides/presentation.
- Dokumen DOCX: gunakan skill docx.
- PDF: gunakan skill pdf.

Dilarang mengklaim menggunakan skill tanpa benar-benar membaca dan menjalankan instruksinya.

## Urutan Dokumen

Baca sesuai konteks:

```text
AGENTS.md
README.md
docs/01_PRD.md
docs/02_MVP.md
docs/03_TECH_STACK.md
docs/04_SYSTEM_ARCHITECTURE.md
docs/05_DATABASE_SCHEMA.md
docs/06_API_SPEC.md
docs/07_AIS_WORKER_SPEC.md
docs/08_UI_UX_GUIDELINE.md
docs/09_DESIGN_SYSTEM.md
docs/10_USER_FLOW.md
docs/11_FEATURE_ROADMAP.md
docs/12_SPRINT_PLAN.md
docs/13_BACKLOG.md
docs/14_SECURITY.md
docs/15_DEPLOYMENT.md
docs/16_TESTING.md
docs/17_CONTRIBUTING.md
docs/19_DATA_SOURCE.md
docs/20_RORO_VESSEL_REGISTRY.md
docs/21_PORT_GEOFENCE_SPEC.md
docs/22_OPEN_SOURCE_GUIDE.md
docs/23_LEARNINGS.md
docs/24_DECISIONS.md
```

## Prioritas Jika Ada Konflik

1. Instruksi terbaru pemilik proyek.
2. `AGENTS.md`.
3. `01_PRD.md`.
4. `02_MVP.md`.
5. Spesifikasi fitur.
6. Dokumentasi teknis.
7. Implementasi lama.

## Scope MVP

Fokus:

- whitelist kapal RoRo berdasarkan MMSI;
- live map;
- detail kapal;
- pencarian;
- status data live/delayed/stale/offline;
- riwayat posisi terbatas;
- master operator, kapal, pelabuhan, dan lintasan;
- geofence sederhana;
- panel admin;
- sumber data dan provenance.

Di luar scope tanpa persetujuan:

- tiket dan pembayaran;
- manifest penumpang;
- tracking individu atau kendaraan di dalam kapal;
- sistem navigasi atau keselamatan;
- prediksi ETA berbasis machine learning;
- aplikasi mobile native;
- Kafka, Kubernetes, atau microservices kompleks.

## Stack yang Disetujui

- Frontend: Vue 3, Vite, TypeScript, Tailwind CSS, Pinia, MapLibre GL JS.
- Backend: Laravel, REST API, Reverb, Queue, Scheduler.
- Worker: Node.js, TypeScript, WebSocket, Zod.
- Database: PostgreSQL, PostGIS.
- Infrastruktur awal: Cloudflare Pages, VPS, Nginx, Supervisor/systemd, GitHub Actions.

Agent tidak boleh mengganti stack hanya karena preferensi pribadi.

## Workflow Wajib

### Sebelum Implementasi

- Baca dokumen terkait.
- Periksa sprint dan backlog aktif.
- Periksa implementasi dan test yang ada.
- Pilih skill relevan.
- Tentukan perubahan minimum yang cukup.

### Saat Implementasi

- Tetap dalam scope.
- Ikuti pola repository.
- Hindari hardcode dan duplikasi.
- Jaga type safety.
- Validasi input.
- Tangani loading, empty, error, stale, dan offline state.
- Tambahkan atau perbarui test.
- Pertimbangkan koneksi buruk dan data AIS tidak akurat.

### Setelah Implementasi

- Jalankan formatter, lint, typecheck, test, dan build yang relevan.
- Perbarui dokumentasi.
- Perbarui backlog/sprint.
- Catat pembelajaran reusable.
- Laporkan validasi yang dijalankan dan yang belum dijalankan.

## Aturan Frontend

- Mobile-first dan responsif.
- TypeScript strict; hindari `any`.
- Jangan menyimpan secret.
- Frontend tidak boleh terhubung langsung ke provider AIS.
- Gunakan komponen peta terpisah: container, vessel layer, port layer, route layer, history layer, controls, popup, legend.
- Tampilkan timestamp update dan status kesegaran data.
- Jangan menyebut posisi realtime bila data sudah stale.
- Bersihkan event listener dan instance MapLibre saat komponen dilepas.

## Aturan Backend

- Controller tipis.
- Gunakan Form Request, Resource, Policy/Gate, service class, transaction, queue, migration, dan seeder idempotent.
- Jangan mengembalikan model mentah sebagai kontrak API publik.
- Lindungi endpoint internal worker.
- Terapkan rate limit pada endpoint publik.

## Aturan AIS Worker

Worker harus menangani reconnect, exponential backoff, heartbeat, parsing, validasi, normalisasi, whitelist MMSI, deduplication, out-of-order messages, stale messages, backend unavailable, dan graceful shutdown.

Worker tidak boleh:

- menyimpan API key di source code;
- mengirim payload mentah tanpa validasi;
- memproses semua kapal tanpa filter;
- retry tanpa batas dan tanpa jeda;
- crash karena satu payload rusak.

## Data AIS

- MMSI adalah identifier utama untuk pencocokan posisi.
- IMO adalah identifier tambahan.
- Kategori RoRo harus diverifikasi dari lebih dari satu sumber.
- Simpan `source_timestamp` dan `received_at`.
- Pisahkan data sumber dari status hasil interpretasi sistem.
- Jangan gunakan data ini untuk klaim navigasi atau keselamatan.

## Database

- Semua perubahan menggunakan migration.
- Gunakan index dan constraint.
- Gunakan PostGIS untuk operasi spasial.
- Pisahkan `vessel_latest_positions` dari `vessel_position_history`.
- Retensi histori harus terbatas dan terdokumentasi.

## Belajar dan Menjadi Lebih Baik

AI agent tidak boleh mengklaim pembelajaran permanen di luar repository. Setiap pembelajaran yang benar-benar reusable harus dicatat pada:

- `docs/23_LEARNINGS.md` untuk temuan teknis atau operasional.
- `docs/24_DECISIONS.md` untuk keputusan arsitektur.

Tambahkan learning hanya jika didukung bukti. Tandai asumsi sebagai `Needs Verification`. Tandai learning lama sebagai `Superseded` bila tidak berlaku.

Setelah tugas besar, evaluasi:

1. Apa yang berhasil?
2. Apa yang gagal?
3. Bug apa yang dapat dicegah?
4. Pola apa yang reusable?
5. Dokumentasi atau test apa yang kurang?
6. Kompleksitas apa yang dapat dikurangi?

## Definition of Done

Tugas selesai jika:

- acceptance criteria terpenuhi;
- implementasi sesuai scope;
- validasi utama berhasil;
- dokumentasi dan backlog diperbarui;
- tidak ada secret atau debug code;
- risiko tersisa dijelaskan.

## Format Laporan Agent

```markdown
## Ringkasan
## Perubahan
## Validasi
## Dokumentasi
## Learning
## Risiko Tersisa
```
