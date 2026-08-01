# 14 — Security

## 1. Security Goals

- Melindungi provider key dan internal token.
- Mencegah perubahan master data tanpa izin.
- Menolak input AIS berbahaya atau invalid.
- Mengurangi abuse endpoint publik.
- Menjaga audit trail admin.

## 2. Secrets

- Gunakan environment variables.
- Jangan commit `.env`.
- Sediakan `.env.example` tanpa nilai rahasia.
- Pisahkan token worker dari credential admin.
- Rotasi token bila terekspos.

## 3. Authentication dan Authorization

- Admin menggunakan Laravel authentication yang sesuai.
- Role minimal admin dan reviewer.
- Gunakan Policy/Gate.
- Endpoint internal menggunakan bearer token atau signed request.
- Jangan gunakan token provider sebagai token internal.

## 4. Input Validation

- Semua request menggunakan schema/form request.
- MMSI tepat 9 digit.
- Coordinate range diperiksa.
- String AIS dibatasi panjangnya.
- JSON payload memiliki size limit.
- Unknown fields dapat diabaikan atau ditolak konsisten.

## 5. API Protection

- Rate limiting.
- CORS allowlist.
- HTTPS wajib produksi.
- Security headers.
- Pagination dan response limits.
- Hindari verbose error di produksi.

## 6. Database

- User database aplikasi memiliki least privilege.
- Backup terenkripsi bila disimpan di luar server.
- Parameterized query/ORM.
- Audit perubahan master data.

## 7. Frontend

- Tidak ada secret.
- Sanitasi konten sumber eksternal.
- Hindari rendering HTML mentah.
- Gunakan CSP bila feasible.
- Token admin disimpan menggunakan mekanisme aman sesuai arsitektur auth.

## 8. Worker

- Validasi seluruh payload provider.
- Redact credential dari log.
- Batasi queue memory.
- Timeout untuk semua HTTP calls.
- Circuit breaker sederhana dapat ditambahkan jika backend down lama.

## 9. Dependency Security

- Lockfile wajib.
- Dependency audit berkala.
- Renovate/Dependabot opsional.
- Hindari package yang tidak terawat.

## 10. Incident Response

1. Cabut atau rotasi secret.
2. Hentikan service terdampak jika perlu.
3. Simpan log relevan.
4. Identifikasi scope.
5. Perbaiki akar masalah.
6. Dokumentasikan pada ADR/learning bila reusable.
7. Beri tahu pengguna bila data mereka terdampak.

## 11. Threats Utama

- Credential leakage.
- Admin account takeover.
- AIS payload abuse.
- API scraping berlebihan.
- Poisoned vessel registry.
- Supply chain dependency.
- Misleading UI yang menyiratkan alat resmi.
