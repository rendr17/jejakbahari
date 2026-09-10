# Security Test Checklist — JejakBahari

Sesuai `14_SECURITY.md` dan `16_TESTING.md` §7.

## 1. Authentication & Authorization

- [ ] Admin login menolak credential invalid
- [ ] Admin login rate limit 5 attempt per menit
- [ ] Token expired ditolak
- [ ] Role admin vs reviewer: reviewer tidak bisa delete
- [ ] Endpoint admin menolak request tanpa token
- [ ] Endpoint internal worker menolak token salah
- [ ] Token worker berbeda dari token admin
- [ ] Logout membatalkan token aktif

## 2. Input Validation

- [ ] MMSI harus tepat 9 digit — tolak jika salah
- [ ] Coordinate latitude [-90, 90], longitude [-180, 180]
- [ ] String AIS (destination, name) dibatasi panjangnya
- [ ] JSON payload size limit diterapkan
- [ ] Unknown fields ditolak/ diabaikan konsisten
- [ ] Form Request validasi semua input admin

## 3. API Protection

- [ ] Rate limiting pada endpoint publik
- [ ] CORS allowlist (bukan `*`)
- [ ] HTTPS wajib di produksi
- [ ] Security headers (X-Content-Type-Options, X-Frame-Options, dll)
- [ ] Pagination maksimum 100 per page
- [ ] Error produksi tidak verbose (tidak expose stack trace)

## 4. XSS & Injection

- [ ] XSS string pada AIS destination/name tidak dieksekusi
- [ ] XSS string pada vessel name tidak dieksekusi di popup
- [ ] SQL injection pada search query ditolak
- [ ] HTML rendering konten AIS di-escape

## 5. Worker Security

- [ ] API key provider tidak di-log
- [ ] Credential di-redact dari log worker
- [ ] Queue memory dibatasi
- [ ] HTTP timeout untuk semua call ke backend
- [ ] Payload invalid tidak crash worker
- [ ] Oversized payload ditolak

## 6. Database

- [ ] Parameterized query / ORM (tidak ada raw SQL dengan concatenation)
- [ ] User database least privilege
- [ ] Audit log mencatat semua aksi admin
- [ ] Migration destructive butuh review

## 7. Secrets

- [ ] Tidak ada secret di repository
- [ ] Tidak ada API key di source code
- [ ] `.env` di `.gitignore`
- [ ] `.env.example` tidak berisi nilai rahasia
- [ ] Token worker dan admin terpisah

## 8. Frontend

- [ ] Tidak ada secret di frontend bundle
- [ ] Token admin tidak di localStorage (jika memungkinkan)
- [ ] CSP header diterapkan (jika feasible)
- [ ] Konten AIS di-sanitize sebelum render

## 9. Dependency

- [ ] Lockfile ada dan digunakan
- [ ] Dependency audit: `pnpm audit` / `composer audit`
- [ ] Tidak ada package dengan vulnerability tinggi
- [ ] Dependabot/Renovate aktif (opsional)

## 10. Incident Response Readiness

- [ ] Prosedur rotasi secret terdokumentasi
- [ ] Prosedur rollback terdokumentasi (`15_DEPLOYMENT.md` §10)
- [ ] Log retention dan backup tersedia

## Cara Eksekusi

1. Jalankan checklist ini sebelum setiap release (Sprint 7 hardening).
2. Untuk fitur baru, jalankan bagian relevan saat sprint tersebut.
3. Catat hasil di TestRail/QASE atau di JIRA sebagai task.
4. Bug security selalu P0 — fix sebelum release.

## Tools

- **Bruno**: test auth flow, input validation, API protection
- **Manual**: inspect log worker, check `.gitignore`, check frontend bundle
- **JMeter**: test rate limit dan oversized payload
- **CI**: dependency audit otomatis
