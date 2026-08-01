# 03 — Tech Stack

## 1. Prinsip Pemilihan

- Gratis dan open-source sebisa mungkin.
- Ringan untuk tim kecil.
- Mudah dipelajari dan dirawat.
- Mendukung koneksi persisten dan data geospasial.
- Mudah diintegrasikan dengan sistem Laravel lain di masa depan.

## 2. Frontend

### Vue 3

Digunakan untuk UI publik dan admin.

Alasan:

- component model sederhana;
- ekosistem stabil;
- cocok dengan Vite;
- mudah untuk tim yang familiar web.

### Vite

- dev server cepat;
- build sederhana;
- dukungan TypeScript baik.

### TypeScript

- kontrak data lebih aman;
- mengurangi bug payload peta dan API;
- shared types dapat dibuat bila diperlukan.

### Tailwind CSS

- cepat untuk membuat UI konsisten;
- mudah menerapkan responsive design;
- tidak membutuhkan runtime besar.

### Pinia

Digunakan untuk state global terbatas seperti filter, selected vessel, dan preferensi UI.

### MapLibre GL JS

Digunakan untuk peta, marker kapal, layer histori, port, route, dan geofence.

## 3. Backend

### Laravel

Tanggung jawab:

- REST API;
- authentication/authorization;
- master data;
- business rules;
- geofence processing;
- realtime event;
- admin operations.

### Laravel Reverb

Digunakan bila realtime browser diaktifkan. MVP dapat memulai dengan polling ringan lalu menambahkan Reverb.

### Laravel Queue dan Scheduler

Digunakan untuk proses non-request, retention cleanup, agregasi, dan retry internal.

## 4. AIS Worker

### Node.js + TypeScript

Worker hanya menangani ingestion dan normalisasi data AIS.

Komponen:

- WebSocket client;
- Zod validation;
- retry/backoff;
- structured logger;
- health endpoint atau heartbeat;
- internal HTTP client ke backend.

Worker tidak menjadi sumber business truth.

## 5. Database

### PostgreSQL

Menyimpan master dan event.

### PostGIS

Digunakan untuk:

- titik kapal;
- geofence pelabuhan;
- jarak kapal ke pelabuhan;
- point-in-polygon;
- histori trajectory.

## 6. Infrastructure

### Cloudflare Pages

Untuk frontend statis.

### VPS Linux

Untuk Nginx, Laravel, Reverb, queue worker, AIS worker, dan PostgreSQL pada fase awal.

### Supervisor atau systemd

Menjaga proses worker tetap hidup.

### GitHub Actions

CI untuk lint, typecheck, test, build, dan deployment.

## 7. Tooling

- pnpm untuk frontend dan worker.
- Composer untuk backend.
- ESLint dan Prettier.
- PHP CS Fixer/Pint.
- PHPUnit/Pest.
- Vitest.
- Playwright untuk E2E.
- Docker Compose opsional untuk local development.

## 8. Dependency Policy

Dependency baru harus:

- memiliki lisensi kompatibel;
- aktif dipelihara;
- tidak menduplikasi kemampuan yang ada;
- memiliki manfaat jelas;
- dipertimbangkan ukuran dan risiko supply chain-nya.

## 9. Alternatif yang Ditolak untuk MVP

- Kubernetes: terlalu kompleks.
- Kafka: belum diperlukan.
- Redis cluster: belum diperlukan; Redis tunggal dapat ditambahkan bila antrean atau cache membutuhkannya.
- Go worker: efisien tetapi menambah bahasa baru.
- FastAPI: baik untuk data science, tetapi belum dibutuhkan.
- PocketBase/SQLite: terlalu terbatas untuk kebutuhan PostGIS dan histori.
