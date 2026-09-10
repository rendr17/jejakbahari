# 26 — AIS Provider Guide

## 1. Rekomendasi Provider

| Provider | Tipe | Biaya | Protokol | Coverage | Rekomendasi |
|----------|------|-------|----------|----------|-------------|
| **AISStream.io** | Streaming | **Gratis** | WebSocket | Global | **Utama** |
| OpenWaters/aiscast | Streaming | Gratis (token) | WebSocket | Community-fed | Alternatif open-source |
| MarineTraffic | REST + Streaming | Berbayar | REST API | Global | Komersial |
| VesselFinder | REST | Berbayar | REST API | Global | Komersial |
| ais.now | REST + WebSocket | Berbayar | REST + WS | Global | Komersial |

### AISStream.io — Rekomendasi Utama

AISStream.io adalah pilihan terbaik untuk JejakBahari karena:

- **Gratis** — tidak ada biaya, tidak ada per-message billing
- **WebSocket** — cocok dengan arsitektur worker kita
- **Global coverage** — jaringan AIS station di seluruh dunia
- **Filter bounding box** — bisa batasi ke perairan Indonesia saja
- **Filter MMSI** — bisa filter kapal tertentu (maksimal 200 MMSI)
- **Filter message type** — bisa hanya terima PositionReport
- **Open source friendly** — tidak melarang penggunaan untuk project open-source

**Limit AISStream.io:**
- Maksimal 3 koneksi per akun
- Maksimal 3 koneksi per IP
- Subscription harus dikirim dalam 3 detik setelah connect
- Subscription update maksimal 1 per detik
- Pesan harus dibaca cepat (slow reader akan didrop)
- Tidak boleh connect dari browser (server-side only)

---

## 2. Tutorial: Konfigurasi AISStream.io

### Langkah 1: Daftar dan Dapatkan API Key

1. Buka https://aisstream.io
2. Klik **Sign in with GitHub** (atau metode lain yang didukung)
3. Setelah login, buka halaman **Account > API Keys**
4. Klik **Create API Key**
5. Salin API key (hanya ditampilkan sekali!)

### Langkah 2: Konfigurasi Worker

Buat file `worker/.env` (copy dari `.env.example`):

```bash
cp worker/.env.example worker/.env
```

Edit `worker/.env`:

```env
NODE_ENV=development
WORKER_ID=worker-1

# Gunakan AISStream.io
AIS_PROVIDER_TYPE=aisstream
AIS_PROVIDER_URL=wss://stream.aisstream.io/v0/stream
AIS_PROVIDER_API_KEY=<paste-api-key-anda-disini>

# Bounding box perairan Indonesia
# Format: lat_min,lon_min,lat_max,lon_max
# Multiple box dipisahkan dengan titik koma
AIS_BOUNDING_BOXES=-11,95,6,141

# Backend internal API
BACKEND_INTERNAL_URL=http://localhost:8000/api/internal/v1
BACKEND_INTERNAL_TOKEN=<same-as-backend-INTERNAL_WORKER_TOKEN>

WHITELIST_REFRESH_SECONDS=300
MAX_MESSAGE_AGE_SECONDS=300
BACKOFF_MIN_MS=1000
BACKOFF_MAX_MS=60000
DELIVERY_QUEUE_MAX=1000
LOG_LEVEL=info
```

### Langkah 3: Konfigurasi Backend

Pastikan `backend/.env` memiliki:

```env
INTERNAL_WORKER_TOKEN=<token-yang-sama-dengan-worker>
```

Generate token random:

```bash
openssl rand -hex 32
```

### Langkah 4: Jalankan Stack

```bash
# Start PostgreSQL + PostGIS
docker compose up -d postgres

# Jalankan backend
cd backend
php artisan migrate --force
php artisan serve

# Jalankan worker (di terminal terpisah)
cd worker
pnpm install
pnpm run build
pnpm start
```

Atau gunakan Docker Compose untuk semuanya:

```bash
docker compose up -d
docker compose logs -f worker
```

### Langkah 5: Verifikasi

Worker akan log:

```json
{"level":"info","event":"worker_starting","worker_id":"worker-1"}
{"level":"info","event":"whitelist_loaded","count":3,"version":"abc123"}
{"level":"info","event":"aisstream_connecting","url":"wss://stream.aisstream.io/v0/stream"}
{"level":"info","event":"aisstream_connected"}
{"level":"info","event":"aisstream_subscribed","compression":true}
{"level":"info","event":"position_accepted","mmsi":"525123456","source_timestamp":"2026-09-10T12:00:00Z"}
```

Cek heartbeat:

```bash
curl -H "Authorization: Bearer <INTERNAL_WORKER_TOKEN>" \
  http://localhost:8000/api/internal/v1/worker-heartbeat
```

---

## 3. Bounding Box Indonesia

Perairan Indonesia membentang dari Sabang sampai Merauke. Untuk MVP, fokus pada rute RoRo utama:

### Rute Merak-Bakauheni (Selat Sunda)

```env
AIS_BOUNDING_BOXES=-6.2,105.6,-5.7,106.2
```

### Rute Ketapang-Gilimanuk (Selat Bali)

```env
AIS_BOUNDING_BOXES=-8.3,113.8,-7.7,114.5
```

### Rute Pantoloan-Palopo (Teluk Tomini)

```env
AIS_BOUNDING_BOXES=-0.9,119.7,-0.7,120.0
```

### Kombinasi semua rute utama

```env
AIS_BOUNDING_BOXES=-6.2,105.6,-5.7,106.2;-8.3,113.8,-7.7,114.5;-0.9,119.7,-0.7,120.0
```

### Coverage nasional (lebih banyak data, lebih berat)

```env
AIS_BOUNDING_BOXES=-11,95,6,141
```

---

## 4. Filter MMSI (Opsional)

Worker sudah memfilter MMSI berdasarkan whitelist dari backend (kapal VERIFIED + active). Tapi AISStream.io juga mendukung filter MMSI di sisi provider untuk mengurangi volume data.

Jika ingin menambahkan filter MMSI di sisi provider, edit `aisstream-provider.ts` dan tambahkan `FiltersShipMMSI` di subscription message. Maksimal 200 MMSI per subscription.

**Catatan:** Filter di sisi provider bersifat dinamis. Jika whitelist berubah, provider filter juga harus di-update. Untuk MVP, filter di sisi worker (whitelist cache) sudah cukup.

---

## 5. Mode Development (Mock Provider)

Untuk development tanpa API key:

```env
AIS_PROVIDER_TYPE=mock
```

Mock provider akan simulasi 3 kapal RoRo di rute Indonesia dengan posisi setiap 5 detik. Tidak perlu koneksi internet atau API key.

---

## 6. Troubleshooting

### Worker tidak menerima pesan

1. Cek log worker — apakah `aisstream_connected` dan `aisstream_subscribed` muncul?
2. Cek bounding box — apakah mencakup area kapal RoRo?
3. Cek whitelist — apakah ada kapal VERIFIED di database?
4. Cek `BACKEND_INTERNAL_TOKEN` — apakah sama dengan backend?

### Connection ditutup terus

1. Pastikan subscription dikirim dalam 3 detik (worker sudah handle ini)
2. Jangan buka lebih dari 3 koneksi per akun
3. Baca pesan dengan cepat (worker sudah handle ini dengan queue)
4. Gunakan `perMessageDeflate: true` (worker sudah set ini)

### Pesan didrop

1. AISStream akan drop pesan jika buffer penuh
2. Pastikan worker memproses pesan cukup cepat
3. Kecilkan bounding box jika volume terlalu tinggi
4. Tambah `DELIVERY_QUEUE_MAX` jika perlu

### Stale message

Worker sudah menolak pesan yang lebih lama dari `MAX_MESSAGE_AGE_SECONDS` (default 300 detik / 5 menit). Backend juga melakukan validasi kedua.

---

## 7. Alternatif: OpenWaters/aiscast

OpenWaters adalah alternatif open-source yang compatible dengan AISStream.io protocol:

```env
AIS_PROVIDER_TYPE=aisstream
AIS_PROVIDER_URL=wss://ais.openwaters.io/v0/stream
AIS_PROVIDER_API_KEY=<aiscast-token>
```

Token bisa didapatkan dengan generate Ed25519 keypair dan POST ke `https://ais.openwaters.io/v1/keys`.

Kelebihan:
- Open source
- Community-fed (volunteer receivers)
- Compatible dengan AISStream protocol

Kekurangan:
- Coverage mungkin tidak selengkap AISStream.io
- Token personal berlaku 30 hari
- Maksimal 2 concurrent connections

---

## 8. Catatan Hukum dan Lisensi

- AISStream.io: cek terms of service terbaru di https://aisstream.io
- Jangan redistribusi raw AIS data jika lisensi melarang
- Pisahkan source code license (MIT) dari data license
- Simpan atribusi yang diwajibkan di halaman About Data
- Review terms setiap 6 bulan atau saat ada perubahan
