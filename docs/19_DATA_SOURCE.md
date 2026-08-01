# 19 — Data Source Strategy

## 1. Tujuan

Mendokumentasikan asal data, izin penggunaan, kualitas, keterbatasan, dan fallback.

## 2. Jenis Data

### Live AIS

Menghasilkan posisi, speed, course, heading, nav status, destination, dan static messages bila tersedia.

### Master Vessel

Nama, MMSI, IMO, call sign, tipe, operator, dimensi, dan status operasional.

### Master Port

Nama pelabuhan, lokasi, boundary/geofence, dan atribut administratif.

### Route

Pasangan pelabuhan dan kapal/operator yang melayani lintasan.

## 3. Candidate Sources

- Provider AIS streaming publik atau berbayar.
- Receiver AIS milik sendiri.
- Portal data pemerintah.
- Website resmi operator.
- Dokumen publik operator atau pelabuhan.
- Kontribusi komunitas dengan evidence.

Setiap penggunaan harus memeriksa syarat dan lisensi terbaru.

## 4. Source Registry Fields

- source name;
- type;
- URL/reference;
- access method;
- license;
- attribution;
- terms URL;
- last reviewed date;
- allowed uses;
- prohibited uses;
- reliability notes.

## 5. Data Quality Dimensions

- Completeness.
- Accuracy.
- Timeliness.
- Consistency.
- Uniqueness.
- Provenance.

## 6. Confidence

Baseline:

- 90–100: dua atau lebih sumber resmi konsisten.
- 70–89: satu sumber resmi atau beberapa sumber kuat.
- 50–69: sumber komunitas dengan evidence cukup.
- di bawah 50: belum layak publik.

## 7. Live AIS Fallback

Prioritas:

1. Provider utama.
2. Provider cadangan jika lisensi dan biaya memungkinkan.
3. Receiver sendiri pada area prioritas.
4. Pertahankan last known position dan tandai stale/offline.

Jangan mensintesis posisi baru ketika provider tidak tersedia.

## 8. Legal dan Attribution

- Jangan scraping jika terms melarang.
- Simpan atribusi yang diwajibkan.
- Jangan redistribusi raw AIS jika lisensi tidak mengizinkan.
- Pisahkan source code license dari data license.
- Sediakan halaman About Data.

## 9. Review Berkala

Setiap source diperiksa ulang minimal tiap enam bulan atau saat terms berubah.
