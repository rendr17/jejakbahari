# 23 — Learnings

Dokumen ini menyimpan pembelajaran reusable yang telah divalidasi.

## Cara Menambah

```markdown
## LRN-YYYYMMDD-001 — Judul

**Tanggal:** YYYY-MM-DD  
**Area:** Frontend | Backend | Worker | Database | Infrastructure | Data  
**Status:** Validated | Needs Verification | Superseded  
**Sumber:** Issue, test, dokumentasi, atau eksperimen

**Masalah:**

**Temuan:**

**Dampak:**

**Aturan ke depan:**

**Referensi:**
```

## Baseline Learnings

### LRN-20260801-001 — AIS bukan sumber identitas tunggal

**Tanggal:** 2026-08-01  
**Area:** Data  
**Status:** Validated  
**Sumber:** Requirement proyek dan karakteristik umum AIS

**Masalah:** Static AIS dapat tidak lengkap atau tidak konsisten.

**Temuan:** Kapal RoRo harus ditentukan melalui whitelist dan evidence tambahan.

**Dampak:** Worker hanya memproses MMSI verified.

**Aturan ke depan:** Jangan mengklasifikasikan kapal publik hanya dari ship type AIS.

### LRN-20260801-002 — Latest position dan history harus dipisah

**Tanggal:** 2026-08-01  
**Area:** Database  
**Status:** Validated

**Masalah:** Query peta akan mahal jika membaca tabel histori.

**Temuan:** Satu row latest per vessel membuat query publik lebih sederhana.

**Aturan ke depan:** Upsert latest dan sampling history pada alur ingestion.

### LRN-20260801-003 — Deklarasi font harus disertai aset font

**Tanggal:** 2026-08-01  
**Area:** Frontend  
**Status:** Validated  
**Sumber:** Inspeksi computed style referensi dan production build frontend

**Masalah:** Menuliskan nama font di `font-family` tidak memuat font tersebut dan dapat diam-diam memakai fallback sistem.

**Temuan:** Variable font Plus Jakarta Sans dan JetBrains Mono dapat di-host bersama aplikasi dengan hanya membawa subset Latin yang dipakai.

**Dampak:** Tipografi konsisten tanpa request font pihak ketiga saat runtime.

**Aturan ke depan:** Font desain wajib memiliki sumber aset eksplisit dan hasil build harus diperiksa agar tidak membawa subset yang tidak diperlukan.
