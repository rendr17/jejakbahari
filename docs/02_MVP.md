# 02 — MVP Scope

## 1. Tujuan MVP

Membuktikan bahwa data AIS dari provider dapat difilter menggunakan whitelist kapal RoRo, disimpan secara efisien, dan ditampilkan pada peta dengan transparansi timestamp.

## 2. Deliverables

### 2.1 Publik

- Landing sederhana.
- Live map.
- Daftar kapal.
- Search kapal dan pelabuhan.
- Vessel detail.
- Histori posisi 24 jam.
- Freshness status.
- Disclaimer.

### 2.2 Admin

- Login.
- CRUD operator.
- CRUD kapal.
- CRUD pelabuhan.
- CRUD lintasan.
- Verifikasi registry.
- Melihat health worker dan posisi terakhir.

### 2.3 Sistem

- AIS worker persisten.
- Reconnect dan backoff.
- Validasi Zod.
- Internal API worker.
- PostgreSQL/PostGIS.
- Laravel API.
- Realtime event opsional pada sprint akhir MVP.

## 3. Prioritas MoSCoW

### Must Have

- Whitelist MMSI.
- Latest position.
- Live map.
- Search.
- Detail kapal.
- Timestamp dan freshness.
- Admin master data.
- Worker health.
- Security dasar.

### Should Have

- Histori 24 jam.
- Geofence pelabuhan.
- Deteksi arrival/departure.
- Realtime push ke browser.
- Filter operator dan rute.

### Could Have

- PWA installable.
- Dark mode.
- Favorite lokal tanpa akun.
- Export data admin.

### Won't Have pada MVP

- Ticketing.
- Payment.
- Native mobile.
- Public account.
- Push notification.
- Machine learning ETA.
- Multi-provider otomatis.
- Skala nasional penuh.

## 4. Batas Data MVP

- Kapal awal: 20–50.
- Pelabuhan awal: 5–15.
- Rute awal: 3–10.
- Histori: 24 jam.
- Sampling bergerak: sekitar 1 menit.
- Sampling diam: sekitar 5 menit.

## 5. User Stories

- Sebagai pengguna, saya dapat mencari kapal agar cepat menemukan posisi terakhirnya.
- Sebagai pengguna, saya dapat melihat usia data agar tidak salah menganggap posisi live.
- Sebagai pengguna, saya dapat melihat histori agar memahami jalur kapal.
- Sebagai admin, saya dapat memverifikasi MMSI agar hanya kapal RoRo yang valid ditampilkan.
- Sebagai admin, saya dapat melihat worker health agar tahu ingestion berjalan.

## 6. Exit Criteria

MVP siap demo publik bila:

- tidak ada secret di repository;
- peta, daftar, detail, dan search bekerja;
- minimal 20 vessel records tervalidasi;
- worker bertahan dari disconnect dan payload invalid;
- CI lulus;
- deployment dapat direplikasi;
- disclaimer tampil pada halaman penting;
- dokumentasi utama sinkron.
