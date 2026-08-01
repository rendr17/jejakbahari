# 01 — Product Requirements Document

## 1. Ringkasan Produk

**Nama:** JejakBahari  
**Jenis:** Web app/PWA open-source  
**Fokus awal:** Kapal RoRo Indonesia  
**Status:** Draft baseline untuk pengembangan MVP

JejakBahari menampilkan posisi dan status kapal RoRo di peta dengan menggabungkan data AIS, master kapal terverifikasi, master pelabuhan, dan lintasan operasional.

## 2. Latar Belakang

Informasi posisi kapal RoRo tersebar di banyak sumber, tidak selalu konsisten, dan sering sulit dipahami oleh masyarakat umum. Sebagian platform komersial menampilkan seluruh jenis kapal, membutuhkan langganan, atau tidak menyediakan konteks lintasan RoRo Indonesia.

Proyek ini berusaha menyediakan pengalaman yang lebih fokus:

- hanya kapal RoRo yang sudah diverifikasi;
- konteks pelabuhan dan lintasan Indonesia;
- status usia data yang transparan;
- source code yang dapat diaudit dan dikembangkan komunitas.

## 3. Masalah

1. Pengguna sulit mengetahui posisi kapal RoRo tertentu pada satu peta yang sederhana.
2. Data AIS tidak selalu mengklasifikasikan kapal RoRo secara akurat.
3. Nama, MMSI, IMO, operator, dan lintasan dapat berbeda antar sumber.
4. Pengguna sering tidak mengetahui apakah posisi benar-benar baru atau sudah terlambat.
5. Tidak ada fondasi open-source ringan yang khusus untuk domain RoRo Indonesia.

## 4. Visi

Menjadi platform open-source yang transparan, ringan, dan dapat dipercaya untuk eksplorasi serta monitoring non-kritis kapal RoRo Indonesia.

## 5. Sasaran Produk

### 5.1 Sasaran MVP

- Menampilkan kapal RoRo terverifikasi pada peta.
- Menampilkan waktu update dan tingkat kesegaran data.
- Memungkinkan pencarian kapal dan pelabuhan.
- Menyediakan detail kapal dan histori posisi terbatas.
- Menyediakan panel admin untuk kurasi data.
- Menyimpan sumber dan confidence setiap master data.

### 5.2 Sasaran Jangka Menengah

- Deteksi arrival/departure berbasis geofence.
- Halaman lintasan dan pelabuhan.
- Kapal favorit dan notifikasi sederhana.
- Dukungan beberapa provider AIS.
- Kontribusi registry dari komunitas.

## 6. Non-Goals

MVP tidak mencakup:

- navigasi atau keselamatan pelayaran;
- pembelian tiket;
- pembayaran;
- manifest penumpang;
- pelacakan orang atau kendaraan;
- jaminan SLA data;
- prediksi ETA berbasis machine learning;
- seluruh kapal di Indonesia;
- aplikasi mobile native.

## 7. Pengguna Sasaran

### 7.1 Masyarakat Umum

Ingin mencari kapal atau melihat posisi terakhir tanpa memahami detail teknis AIS.

### 7.2 Penggemar Maritim dan Komunitas

Ingin memantau armada, lintasan, dan berkontribusi pada registry.

### 7.3 Tim Operasional Non-Kritis

Ingin melihat gambaran posisi kapal sebagai informasi tambahan, bukan sumber keputusan keselamatan.

### 7.4 Administrator Data

Mengelola kapal, operator, pelabuhan, rute, sumber, dan status verifikasi.

## 8. Use Cases Utama

- Pengguna membuka peta dan melihat kapal RoRo aktif.
- Pengguna mencari nama kapal.
- Pengguna membuka detail kapal dan melihat posisi terakhir.
- Pengguna melihat bahwa data live, delayed, stale, atau offline.
- Pengguna melihat jejak posisi 24 jam.
- Admin menambahkan kapal berdasarkan MMSI dan sumber valid.
- Sistem mendeteksi kapal masuk atau keluar geofence pelabuhan.

## 9. Functional Requirements

### FR-001 Live Map

- Menampilkan marker kapal berdasarkan latest position.
- Marker memiliki arah berdasarkan heading atau COG bila valid.
- Marker dapat dipilih untuk membuka ringkasan.
- Peta menampilkan atribusi sumber peta.

### FR-002 Search

- Pencarian berdasarkan nama kapal, MMSI, IMO, operator, atau pelabuhan.
- Hasil menampilkan jenis entitas dan status relevan.

### FR-003 Vessel Detail

- Nama kapal, MMSI, IMO, operator, tipe, sumber verifikasi.
- Posisi, speed over ground, course, heading, navigational status.
- Source timestamp, received timestamp, dan age.
- Disclaimer data.

### FR-004 Freshness Status

Baseline aturan:

- `LIVE`: kurang dari 5 menit.
- `DELAYED`: 5–30 menit.
- `STALE`: 30 menit–6 jam.
- `OFFLINE`: lebih dari 6 jam atau belum ada posisi.

Nilai harus dapat dikonfigurasi.

### FR-005 Position History

- Menampilkan histori maksimum 24 jam pada MVP.
- History dapat disampling.
- Data tidak boleh membebani query latest position.

### FR-006 Master Data

Admin dapat mengelola:

- operator;
- vessel;
- port;
- route;
- data source;
- registry evidence;
- status verifikasi.

### FR-007 AIS Ingestion

- Worker terhubung ke provider melalui WebSocket atau protokol yang didukung.
- Hanya MMSI whitelist yang diproses.
- Payload divalidasi dan dinormalisasi.
- Pesan duplicate, stale, dan out-of-order ditangani.

### FR-008 Geofence

- Port memiliki geometry atau radius.
- Sistem menghasilkan event `ENTERED`, `ARRIVED`, `DEPARTED`, atau `EXITED` sesuai aturan.
- Event hasil sistem dibedakan dari data AIS asli.

### FR-009 Admin Authentication

- Endpoint admin membutuhkan autentikasi.
- Peran minimum: `admin` dan `reviewer`.

### FR-010 Provenance

- Master vessel dan port menyimpan sumber.
- Setiap klaim penting dapat memiliki confidence dan status verifikasi.

## 10. Non-Functional Requirements

### NFR-001 Performance

- Initial page shell dapat digunakan dalam koneksi seluler menengah.
- Peta tidak re-render penuh pada setiap pesan posisi.
- Latest vessel query ditargetkan di bawah 500 ms pada dataset MVP.

### NFR-002 Reliability

- Worker reconnect otomatis.
- Satu payload invalid tidak boleh menghentikan worker.
- Provider unavailable tidak boleh merusak data terakhir.

### NFR-003 Security

- Secret hanya pada environment variable.
- Endpoint internal worker diautentikasi.
- Rate limit endpoint publik.
- Input tervalidasi.

### NFR-004 Accessibility

- Navigasi keyboard untuk elemen non-peta.
- Kontras teks memadai.
- Status tidak hanya dibedakan dengan warna.

### NFR-005 Observability

- Structured logs.
- Health endpoint.
- Metrik worker: connected, messages received, accepted, rejected, reconnects.

### NFR-006 Maintainability

- TypeScript strict pada frontend dan worker.
- Dokumentasi dan test diperbarui bersama perubahan.
- Dependency minimum dan terawat.

## 11. Success Metrics

MVP dianggap berhasil bila:

- minimal 20 kapal RoRo terverifikasi tersedia;
- minimal 5 pelabuhan dan 3 lintasan dimodelkan;
- posisi latest dapat ditampilkan jika provider menyediakan data;
- 95% payload invalid ditolak tanpa crash;
- pengguna dapat menemukan kapal dalam maksimal tiga interaksi;
- semua data posisi menampilkan timestamp dan freshness;
- pipeline build, lint, dan test berjalan otomatis.

## 12. Constraints

- Cakupan bergantung pada provider AIS.
- Data gratis dapat memiliki keterlambatan atau pembatasan.
- Tidak semua kapal mengirim static data dengan benar.
- Penggunaan tile peta harus mengikuti kebijakan provider.
- Infrastruktur awal harus murah dan sederhana.

## 13. Risiko

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Provider AIS tidak stabil | Kapal hilang dari peta | Reconnect, fallback provider, status stale |
| Salah klasifikasi kapal | Data menyesatkan | Whitelist dan verifikasi multi-sumber |
| Database histori tumbuh cepat | Biaya dan query meningkat | Sampling dan retention policy |
| Tile map diblokir | Peta gagal dimuat | Provider yang sesuai, caching legal, fallback |
| Kontributor memasukkan data buruk | Registry tidak terpercaya | Review, provenance, confidence |
| Pengguna menganggap alat resmi | Risiko keselamatan | Disclaimer kuat dan berulang |

## 14. Asumsi Awal

- MVP dikerjakan oleh tim kecil atau solo developer.
- Satu VPS cukup untuk backend, worker, Reverb, dan database pada fase awal.
- Data kapal dimulai dari whitelist manual.
- Tidak ada kebutuhan multi-tenant.

## 15. Acceptance MVP

- Peta dapat memuat kapal dari endpoint latest positions.
- Detail kapal menampilkan data master dan posisi.
- Worker dapat reconnect dan mengirim posisi valid.
- Admin dapat membuat dan memverifikasi kapal.
- Histori 24 jam dapat ditampilkan.
- Freshness state konsisten di API dan UI.
- Dokumentasi, test, dan deployment baseline tersedia.
