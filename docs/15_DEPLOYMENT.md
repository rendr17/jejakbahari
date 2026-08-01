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
