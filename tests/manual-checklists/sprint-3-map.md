# Manual Test Checklist — Sprint 3: Public Map

- **Tester:** [nama]
- **Tanggal:** [tanggal]
- **Environment:** Local / Staging
- **Browser:** [Chrome/Firefox/Safari + version]
- **Backend URL:** http://localhost:8000
- **Frontend URL:** http://localhost:5173

## Pre-condition

- [ ] Backend Laravel berjalan
- [ ] Database PostgreSQL/PostGIS ter-seed (minimal 6 vessel VERIFIED + public_visible)
- [ ] Frontend Vite dev server berjalan
- [ ] Minimal 1 vessel memiliki latest position

---

## 1. Map Page Load

| # | Step | Expected | Result | Bug ID |
|---|------|----------|--------|--------|
| 1 | Buka http://localhost:5173/peta | Halaman peta terbuka | | |
| 2 | Tunggu loading selesai | Loading spinner hilang, peta terlihat | | |
| 3 | Periksa map container | MapLibre canvas ter-render (basemap gelap) | | |
| 4 | Periksa URL | URL berakhir dengan /peta | | |

## 2. Vessel Markers

| # | Step | Expected | Result | Bug ID |
|---|------|----------|--------|--------|
| 5 | Lihat peta setelah loading | Marker kapal muncul (lingkaran berwarna dengan arrow) | | |
| 6 | Hover marker | Cursor berubah jadi pointer | | |
| 7 | Klik marker | Popup muncul dengan nama, MMSI, status, SOG | | |
| 8 | Klik marker lagi | Vessel card muncul di pojok kanan atas | | |
| 9 | Periksa warna marker | Sesuai freshness: hijau (LIVE), kuning (DELAYED), oranye (STALE), abu (OFFLINE) | | |
| 10 | Periksa arah arrow | Arrow menunjuk ke heading/COG kapal, fallback utara | | |

## 3. Vessel Card

| # | Step | Expected | Result | Bug ID |
|---|------|----------|--------|--------|
| 11 | Buka vessel card | Card menampilkan: nama, MMSI, status, koordinat, kecepatan, arah, tujuan, update time | | |
| 12 | Periksa timestamp | Timestamp ditampilkan dalam format id-ID | | |
| 13 | Klik tombol close (✕) | Vessel card tertutup | | |
| 14 | Buka card, buka card lain | Card pertama diganti card kedua (tidak menumpuk) | | |
| 15 | Periksa disclaimer | "Data AIS bersifat indikatif, bukan untuk navigasi" tampil di card | | |

## 4. Freshness Legend

| # | Step | Expected | Result | Bug ID |
|---|------|----------|--------|--------|
| 16 | Lihat pojok kiri bawah | Legenda "Status Data" terlihat | | |
| 17 | Periksa 4 status | LIVE, DELAYED, STALE, OFFLINE semua terlihat | | |
| 18 | Periksa warna indicator | Hijau, kuning, oranye, abu-abu sesuai status | | |
| 19 | Periksa label | Label dapat dibaca tanpa bergantung pada warna saja | | |

## 5. Auto-Refresh

| # | Step | Expected | Result | Bug ID |
|---|------|----------|--------|--------|
| 20 | Tunggu 30 detik | Peta auto-refresh posisi tanpa manual reload | | |
| 21 | Periksa posisi marker | Marker update posisi jika data baru tersedia | | |

## 6. Error State

| # | Step | Expected | Result | Bug ID |
|---|------|----------|--------|--------|
| 22 | Matikan backend, buka /peta | Error message "Gagal memuat data posisi" tampil | | |
| 23 | Periksa tombol retry | Tombol "Coba lagi" tersedia | | |
| 24 | Klik "Coba lagi" (backend masih mati) | Error tetap tampil, tidak crash | | |
| 25 | Nyalakan backend, klik "Coba lagi" | Data berhasil dimuat, error hilang | | |

## 7. Empty State

| # | Step | Expected | Result | Bug ID |
|---|------|----------|--------|--------|
| 26 | Hapus semua latest positions, buka /peta | Pesan "Belum ada posisi kapal yang tersedia" tampil | | |
| 27 | Periksa peta | Peta tetap ter-render, hanya tidak ada marker | | |

## 8. Responsive

| # | Viewport | Step | Expected | Result |
|---|----------|------|----------|--------|
| 28 | Mobile 375px | Buka /peta | Peta full screen, legend dan card tidak overflow | |
| 29 | Tablet 768px | Buka /peta | Layout proporsional | |
| 30 | Desktop 1280px | Buka /peta | Layout optimal, card di pojok kanan | |

## 9. Accessibility

| # | Step | Expected | Result | Bug ID |
|---|------|----------|--------|--------|
| 31 | Tab navigation di /peta | Fokus dapat mencapai marker dengan keyboard | | |
| 32 | Enter/Space pada marker | Vessel card terbuka | | |
| 33 | Screen reader | Legenda terbaca sebagai "Legenda status kesegaran data" | | |
| 34 | Loading state | role="status" terbaca oleh screen reader | | |
| 35 | Error state | role="alert" terbaca oleh screen reader | | |

## 10. Network Condition

| # | Condition | Step | Expected | Result |
|---|-----------|------|----------|--------|
| 36 | Slow 3G | Buka /peta | Loading spinner tampil lebih lama, peta tetap load | |
| 37 | Offline | Buka /peta | Error state tampil graceful | |
| 38 | Map tile gagal | Block tile OSM | Background gelap tetap terlihat, tidak crash | |

## 11. Navigation

| # | Step | Expected | Result | Bug ID |
|---|------|----------|--------|--------|
| 39 | Dari landing, klik "Lihat peta kapal" | Navigasi ke /peta | | |
| 40 | Dari /peta, klik logo/header | Navigasi kembali ke landing | | |

---

## Summary

| Section | Total | Pass | Fail | Blocked |
|---------|-------|------|------|---------|
| Map Page Load | 4 | | | |
| Vessel Markers | 6 | | | |
| Vessel Card | 5 | | | |
| Freshness Legend | 4 | | | |
| Auto-Refresh | 2 | | | |
| Error State | 4 | | | |
| Empty State | 2 | | | |
| Responsive | 3 | | | |
| Accessibility | 5 | | | |
| Network Condition | 3 | | | |
| Navigation | 2 | | | |
| **Total** | **40** | | | |

## Notes

[temuan tambahan, observasi, screenshot reference]
