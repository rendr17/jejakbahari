# 21 — Port Geofence Specification

## 1. Tujuan

Mendeteksi transisi kapal terhadap area pelabuhan untuk menghasilkan event informasi seperti arrived dan departed.

## 2. Geometry

Pilihan:

1. Polygon: paling akurat.
2. Circle radius dari center point: fallback MVP.

Simpan geometry sebagai PostGIS geography SRID 4326.

## 3. Event Types

- `ENTERED_GEOFENCE`
- `ARRIVED`
- `DEPARTED`
- `EXITED_GEOFENCE`

`ARRIVED` dan `DEPARTED` adalah interpretasi sistem, bukan AIS raw status.

## 4. Baseline Rules

### Entered

Posisi berpindah dari outside menjadi inside.

### Arrived

- inside geofence;
- speed di bawah threshold, misalnya 2 knot;
- bertahan selama dwell time, misalnya 10 menit.

### Departed

- sebelumnya arrived;
- speed meningkat atau posisi keluar inner zone;
- bertahan dalam kondisi moving selama threshold.

### Exited

Posisi berpindah dari inside menjadi outside.

Nilai threshold harus configurable per port.

## 5. Noise Handling

- Gunakan hysteresis: inner dan outer radius.
- Abaikan loncatan koordinat tidak masuk akal.
- Butuhkan lebih dari satu sample untuk event penting.
- Jangan menghasilkan event berulang dalam cooldown window.

## 6. State per Vessel-Port

Simpan state:

- current zone;
- entered_at;
- arrived_at;
- last_position_at;
- last_speed;
- last_event;

## 7. Confidence

Confidence event dipengaruhi:

- kualitas geometry;
- freshness posisi;
- jumlah sample;
- speed consistency;
- timestamp continuity.

## 8. Admin Tools

- Edit center/radius atau polygon.
- Preview geofence pada peta.
- Simulate dengan sample track.
- Lihat event history.
- Disable geofence tanpa menghapus.

## 9. Testing

- clean enter/exit;
- boundary jitter;
- stale gap;
- teleport position;
- vessel berhenti di dekat tetapi di luar port;
- multiple nearby ports.
