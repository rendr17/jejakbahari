# 15 — Deployment

## 1. Environments

- Local
- Staging
- Production

Setiap environment memiliki database dan secret terpisah.

## 2. Local Development

Komponen:

- PostgreSQL + PostGIS.
- Laravel backend.
- Vue frontend.
- Node worker dengan mock provider bila API tidak tersedia.

Docker Compose boleh digunakan untuk database dan service pendukung.

## 3. Production Baseline

### Frontend

- Build statis.
- Deploy ke Cloudflare Pages.
- Environment hanya public API URL dan Reverb public config.

### VPS

Menjalankan:

- Nginx.
- PHP-FPM/Laravel.
- Laravel queue worker.
- Laravel Reverb.
- Node AIS worker.
- PostgreSQL/PostGIS.

## 4. Process Management

Gunakan Supervisor atau systemd untuk:

- queue worker;
- Reverb;
- AIS worker.

Harus ada restart policy, log rotation, dan start on boot.

## 5. Nginx

- HTTPS.
- Reverse proxy API dan Reverb.
- Request size limit.
- Timeout sesuai WebSocket.
- Compression untuk respons teks.

## 6. Database Deployment

- Jalankan migration dalam release step.
- Backup sebelum migration breaking.
- Jangan menjalankan destructive migration tanpa review.

## 7. CI/CD

Pipeline minimal:

1. Install dependencies.
2. Lint.
3. Typecheck/static analysis.
4. Test.
5. Build.
6. Deploy staging.
7. Smoke test.
8. Deploy production dengan approval bila diperlukan.

## 8. Backup

- Database backup harian.
- Retensi minimum 7 backup harian dan 4 mingguan.
- Uji restore secara berkala.
- Simpan backup di lokasi berbeda dari VPS.

## 9. Monitoring

- HTTP health API.
- Worker heartbeat.
- Disk usage.
- Database connection.
- Queue failures.
- Reverb process.
- Last AIS message.

## 10. Rollback

- Frontend: rollback deployment sebelumnya.
- Backend: deploy artifact sebelumnya.
- Database: gunakan forward fix; rollback migration hanya jika aman.
- Worker: rollback binary/package dan restart.

## 11. Release Checklist

- CI hijau.
- Migration reviewed.
- Environment variables lengkap.
- Secret tidak terekspos.
- Backup tersedia.
- Smoke test map dan API.
- Worker connected.
- Freshness tampil benar.

## 12. Laravel Reverb (Realtime WebSocket)

### Konfigurasi Environment

Production harus set:

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=jejakbahari
REVERB_APP_KEY=<random-key-min-20-chars>
REVERB_APP_SECRET=<random-secret-min-20-chars>
REVERB_HOST=your-domain.example.com
REVERB_PORT=8080
REVERB_SCHEME=wss
REVERB_SERVER_HOST=127.0.0.1
REVERB_SERVER_PORT=8080
```

Generate key dan secret:

```bash
php artisan reverb:generate
```

Atau manual:

```bash
openssl rand -hex 20   # REVERB_APP_KEY
openssl rand -hex 32   # REVERB_APP_SECRET
```

### Menjalankan Reverb Server

#### Supervisor

`/etc/supervisor/conf.d/jejakbahari-reverb.conf`:

```ini
[program:jejakbahari-reverb]
process_name=%(program_name)s
command=php /var/www/jejakbahari/backend/artisan reverb:start --host=127.0.0.1 --port=8080
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/jejakbahari/reverb.log
stopwaitsecs=10
stopasgroup=true
killasgroup=true
```

#### systemd

`/etc/systemd/system/jejakbahari-reverb.service`:

```ini
[Unit]
Description=JejakBahari Reverb WebSocket Server
After=network.target postgresql.service

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/jejakbahari/backend
ExecStart=/usr/bin/php artisan reverb:start --host=127.0.0.1 --port=8080
Restart=always
RestartSec=5
StandardOutput=append:/var/log/jejakbahari/reverb.log
StandardError=append:/var/log/jejakbahari/reverb-error.log

[Install]
WantedBy=multi-user.target
```

Aktifkan:

```bash
sudo systemctl daemon-reload
sudo systemctl enable jejakbahari-reverb
sudo systemctl start jejakbahari-reverb
```

### Nginx Reverse Proxy untuk WebSocket

Tambahkan ke server block:

```nginx
location /app {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_read_timeout 86400;
    proxy_send_timeout 86400;
}
```

### Frontend Environment (Cloudflare Pages)

```env
VITE_REVERB_APP_KEY=<same-as-backend>
VITE_REVERB_HOST=your-domain.example.com
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=wss
```

### Verifikasi

```bash
# Cek proses berjalan
ps aux | grep reverb

# Cek port mendengarkan
ss -tlnp | grep 8080

# Test WebSocket connection (butuh wscat)
wscat -c "wss://your-domain.example.com/app/<REVERB_APP_KEY>"
```

### Catatan

- `BROADCAST_CONNECTION=log` digunakan untuk CI dan development tanpa Reverb server.
- Production wajib set `BROADCAST_CONNECTION=reverb`.
- Queue worker juga perlu berjalan untuk memproses broadcast jobs jika menggunakan queue.
- Reverb tidak menyimpan history — frontend harus resync dari REST setelah reconnect.

### Local Testing dengan Docker Compose

Untuk testing Reverb secara lokal tanpa setup manual:

```bash
# Start semua service (PostgreSQL, backend, Reverb, queue worker)
docker compose up -d

# Backend API: http://localhost:8000
# Reverb WebSocket: ws://localhost:8080
# Health check: http://localhost:8000/up

# Stop
docker compose down
```

Frontend env untuk local Docker:

```env
VITE_API_BASE_URL=http://localhost:8000/api/v1
VITE_REVERB_APP_KEY=local-dev-key-1234567890
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=ws
```

### Deployment Verification

Setelah deploy, jalankan verification script:

```bash
# Di server production
cd /var/www/jejakbahari/backend
REVERB_APP_KEY=<your-key> ./verify-reverb.sh https://your-domain.example.com
```

Script memeriksa:

1. Backend `/up` health check
2. `REVERB_APP_KEY` ter-set
3. `BROADCAST_CONNECTION=reverb`
4. Reverb process berjalan (Supervisor/systemd)
5. Reverb port mendengarkan
6. WebSocket endpoint reachable (jika `wscat` tersedia)

## 13. Scheduler dan Retention

History retention dijalankan via Laravel scheduler. Tambahkan cron entry di VPS:

```cron
* * * * * cd /var/www/jejakbahari/backend && php artisan schedule:run >> /dev/null 2>&1
```

Atau gunakan `schedule:work` via Supervisor untuk environment tanpa cron:

```ini
[program:jejakbahari-scheduler]
command=php /var/www/jejakbahari/backend/artisan schedule:work
autostart=true
autorestart=true
user=www-data
stdout_logfile=/var/log/jejakbahari/scheduler.log
```

Task yang terjadwal:

| Command | Jadwal | Fungsi |
|---|---|---|
| `positions:prune-history` | Daily 02:00 | Hapus `vessel_position_history` lebih tua dari `HISTORY_RETENTION_DAYS` (default 7 hari), chunked 5000 rows |

Manual run / dry-run:

```bash
php artisan positions:prune-history --dry-run   # hitung tanpa hapus
php artisan positions:prune-history --days=3    # override retention
```

## 14. Monitoring

Endpoint monitoring publik:

- `GET /up` — Laravel health check (proses + DB ready)
- `GET /api/v1/status` — pipeline status (OPERATIONAL/DEGRADED/DOWN), worker heartbeat freshness, tracked vessel count, last position age

Worker heartbeat tersimpan di cache 2 menit; worker mengirim setiap interval. Bila tidak ada heartbeat, status = `OFFLINE` dan pipeline = `DEGRADED`.

Rekomendasi uptime monitor: poll `/api/v1/status` tiap 60s, alert bila `status != OPERATIONAL` lebih dari 5 menit.
