# 08 — UI/UX Guideline

## 1. Identitas dan Referensi

- Nama produk yang tampil di UI adalah **JejakBahari**.
- Referensi visual utama: [Commute Data Platform](https://data.commute.shiorilabs.id/), diakses 1 Agustus 2026.
- Adaptasi yang dipakai: dark data-centric, grid titik, garis lintasan, aksen terang, tipografi tegas, dan informasi teknis yang ringkas.
- Jangan menyalin logo, ilustrasi, teks, data, atau identitas Commute/Shiori Labs. Seluruh elemen harus diterjemahkan ke konteks maritim JejakBahari.

## 2. Prinsip UX

- Peta dan posisi terakhir adalah fokus utama.
- Usia, sumber, dan keterbatasan data selalu terlihat.
- Mobile-first dan tetap efisien di koneksi buruk.
- Pengguna dapat menemukan kapal dalam maksimal tiga interaksi.
- Tampilan informatif dan modern, tetapi tidak menyerupai sistem navigasi atau keselamatan resmi.
- Dekorasi tidak boleh mengurangi keterbacaan peta, status, atau kontrol.

## 3. Information Architecture

### Public

- Beranda/Peta
- Kapal
- Detail Kapal
- Pelabuhan
- Detail Pelabuhan
- Lintasan
- Tentang Data

### Admin

- Dashboard
- Kapal
- Operator
- Pelabuhan
- Lintasan
- Sumber
- Kesehatan Worker
- Audit Log

Gunakan label navigasi pendek seperti referensi. Pada mobile, prioritaskan Peta, Kapal, dan Cari; menu lain masuk drawer.

## 4. App Shell

- Header gelap, ringkas, dan tidak menutupi peta.
- Wordmark `JejakBahari` menggunakan aksen coral; tanpa meniru logo referensi.
- Desktop memakai navigasi horizontal. Mobile memakai tombol menu berlabel dan search yang mudah dijangkau.
- Latar non-peta boleh memakai grid titik halus atau motif lintasan laut dengan opacity rendah.
- Konten utama memiliki lebar maksimum 72rem; area peta boleh penuh layar.
- Footer dan halaman Tentang Data memuat disclaimer serta provenance.

## 5. Beranda/Peta

Komponen:

- header dan wordmark;
- search global;
- filter bottom sheet pada mobile;
- map canvas;
- vessel, port, route, dan history layer;
- freshness legend;
- selected vessel card;
- kontrol lokasi dan reset bearing;
- atribusi peta dan disclaimer.

Perilaku:

- Pada mobile, search di atas dan detail ringkas sebagai bottom card.
- Pada desktop, gunakan panel maksimal 24rem di atas peta; jangan menutup area peta secara berlebihan.
- Marker kapal berotasi memakai heading, fallback COG, lalu utara.
- Zoom rendah memakai clustering atau simplified layer.
- History memakai garis dan titik start/end; jangan memakai animasi dekoratif yang menyiratkan posisi real-time.
- Jika tile gagal, tampilkan daftar kapal dan tombol coba lagi.

## 6. Halaman Data

Gunakan pola editorial dua kolom dari referensi hanya ketika membantu: narasi/identitas di satu sisi dan data/peta di sisi lain. Pada mobile seluruhnya menjadi satu kolom.

Urutan Detail Kapal:

1. Nama kapal dan operator.
2. Freshness badge dan waktu pembaruan.
3. Posisi terakhir pada peta.
4. Speed, course, heading, dan nav status.
5. MMSI, IMO, dan call sign.
6. Lintasan terkait.
7. Histori maksimum 24 jam.
8. Sumber dan status verifikasi.
9. Disclaimer.

Data teknis pendek seperti MMSI, koordinat, timestamp, dan identifier menggunakan font mono dan tabular numbers.

## 7. States dan Freshness

Setiap halaman data wajib memiliki loading, empty, error, offline, stale, dan permission denied untuk admin.

Status selalu memakai label, ikon, waktu relatif, dan warna:

```text
Live · diperbarui 2 menit lalu
Delayed · diperbarui 18 menit lalu
Stale · diperbarui 2 jam lalu
Offline · tidak ada data baru
```

- Jangan memakai istilah “real-time” saat status bukan `LIVE`.
- Pertahankan posisi terakhir saat koneksi putus, tandai sebagai stale/offline, dan tampilkan timestamp absolut.
- Skeleton mengikuti bentuk konten; hindari layout shift.

## 8. Search UX

- Debounce 250–400 ms dan minimal 2 karakter untuk remote search.
- Kelompokkan hasil menjadi kapal, pelabuhan, dan operator.
- Sorot matched text dan tampilkan MMSI sebagai secondary text.
- Search dapat dibuka dengan keyboard, ditutup dengan Escape, dan memiliki label yang terbaca screen reader.
- Empty state membedakan “belum mengetik”, “tidak ditemukan”, dan “gagal memuat”.

## 9. Bahasa Visual dan Motion

- Gunakan komposisi gelap, ruang lapang, heading tegas, border tipis, dan aksen coral secara hemat.
- Motif titik/garis harus berhubungan dengan lintasan laut atau jejak kapal, bukan jaringan kereta.
- Motion 120–240 ms untuk feedback antarmuka.
- Hormati `prefers-reduced-motion`.
- Jangan memakai parallax, autoplay, atau animasi marker berlebihan.

## 10. Accessibility

- Kontras minimum WCAG AA untuk teks dan kontrol penting.
- Semua action memiliki nama yang jelas, target sentuh minimum 44×44 px, dan focus ring terlihat.
- Status tidak bergantung pada warna.
- Peta memiliki alternatif daftar dan ringkasan posisi tekstual.
- Urutan fokus mengikuti urutan visual; drawer/dialog mengelola focus trap dan pengembalian fokus.
- Pola grid titik bersifat dekoratif dan diabaikan assistive technology.

## 11. Copywriting

- Gunakan Bahasa Indonesia sederhana, ringkas, dan netral.
- Gunakan “Posisi terakhir”, bukan “telemetry state”.
- Gunakan “Diperbarui 4 menit lalu”, disertai timestamp absolut saat detail dibuka.
- Gunakan “Data dapat terlambat atau tidak akurat”, bukan klaim kepastian.
- Nama produk selalu ditulis `JejakBahari` tanpa spasi.

## 12. Admin UX

- Kepadatan data boleh lebih tinggi daripada UI publik, tetapi memakai token dan komponen yang sama.
- Tampilkan status verifikasi, sumber, dan evidence sebelum tindakan verify.
- Tindakan destructive memerlukan konfirmasi yang menyebut objek dan dampaknya.
- Audit perubahan terlihat.
- Form MMSI memvalidasi tepat 9 digit.
