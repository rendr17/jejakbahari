# JejakBahari

JejakBahari adalah proyek web app gratis dan open-source untuk menampilkan posisi, status, dan riwayat pergerakan kapal RoRo di Indonesia berbasis data AIS.

> **Peringatan:** proyek ini bukan alat navigasi, keselamatan, SAR, atau sumber data operasional resmi. Data AIS dapat terlambat, hilang, salah, atau tidak tersedia.

## Tujuan

- Menyediakan peta tracking kapal RoRo Indonesia yang mudah diakses.
- Menggabungkan live position AIS dengan master kapal, pelabuhan, dan lintasan.
- Menjadi fondasi open-source yang dapat dikembangkan komunitas.
- Menjaga arsitektur tetap ringan, murah, dan mudah dirawat.

## Scope MVP

- Live map kapal RoRo yang sudah masuk whitelist.
- Pencarian kapal dan pelabuhan.
- Detail kapal dan status kesegaran data.
- Riwayat posisi terbatas.
- Master operator, kapal, pelabuhan, dan lintasan.
- Geofence sederhana untuk deteksi tiba dan berangkat.
- Panel admin untuk master data dan verifikasi.

## Tech Stack

- **Frontend:** Vue 3, Vite, TypeScript, Tailwind CSS, Pinia, MapLibre GL JS.
- **Backend:** Laravel REST API, Laravel Reverb, Queue, Scheduler.
- **AIS worker:** Node.js, TypeScript, WebSocket client, Zod.
- **Database:** PostgreSQL dan PostGIS.
- **Deployment awal:** Cloudflare Pages dan satu VPS Linux.

## Struktur Repository yang Disarankan

```text
jejakbahari/
├── frontend/
├── backend/
├── worker/
├── database/
├── docs/
├── scripts/
├── tests/
├── .github/
├── AGENTS.md
├── README.md
├── CONTRIBUTING.md
└── LICENSE
```

## Dokumentasi

Mulai dari:

1. [`docs/01_PRD.md`](docs/01_PRD.md)
2. [`docs/02_MVP.md`](docs/02_MVP.md)
3. [`docs/03_TECH_STACK.md`](docs/03_TECH_STACK.md)
4. [`docs/04_SYSTEM_ARCHITECTURE.md`](docs/04_SYSTEM_ARCHITECTURE.md)
5. [`docs/12_SPRINT_PLAN.md`](docs/12_SPRINT_PLAN.md)
6. [`docs/18_AGENTS.md`](docs/18_AGENTS.md)

## Menjalankan Frontend

Fondasi frontend tersedia di folder `frontend`.

```bash
cd frontend
pnpm install
pnpm dev
```

Backend dan AIS worker belum diinisialisasi. Petunjuk validasi frontend tersedia pada `frontend/README.md`.

## Kontribusi

Baca:

- [`docs/17_CONTRIBUTING.md`](docs/17_CONTRIBUTING.md)
- [`docs/22_OPEN_SOURCE_GUIDE.md`](docs/22_OPEN_SOURCE_GUIDE.md)
- [`docs/20_RORO_VESSEL_REGISTRY.md`](docs/20_RORO_VESSEL_REGISTRY.md)

## Lisensi

Kode sumber direncanakan menggunakan lisensi MIT. Data pihak ketiga tetap tunduk pada lisensi, syarat penggunaan, dan atribusi sumber masing-masing.
