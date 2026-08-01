# 24 — Architecture Decision Records

## ADR-001 — Gunakan Laravel sebagai backend utama

**Tanggal:** 2026-08-01  
**Status:** Accepted  
**Konteks:** Proyek membutuhkan REST API, admin, authentication, queue, dan integrasi masa depan.

**Opsi:** Laravel, FastAPI, Go, full-stack Nuxt.

**Keputusan:** Laravel dipilih sebagai backend utama.

**Alasan:** Familiaritas, produktivitas CRUD, ekosistem, dan konsistensi dengan sistem lain.

**Konsekuensi positif:** Pengembangan admin dan business rules cepat.

**Konsekuensi negatif:** Lebih berat daripada Go; membutuhkan proses terpisah untuk AIS streaming.

## ADR-002 — Gunakan Node.js TypeScript sebagai AIS worker

**Tanggal:** 2026-08-01  
**Status:** Accepted

**Konteks:** Koneksi provider AIS harus persisten dan reconnect otomatis.

**Keputusan:** Worker Node.js terpisah dari request lifecycle Laravel.

**Konsekuensi positif:** WebSocket handling sederhana dan isolasi failure.

**Konsekuensi negatif:** Menambah service yang harus dipantau.

## ADR-003 — Worker mengirim data melalui internal API

**Tanggal:** 2026-08-01  
**Status:** Accepted

**Opsi:** Direct database write atau internal API.

**Keputusan:** Internal API.

**Alasan:** Business validation, audit, dan coupling database lebih terkontrol.

**Konsekuensi:** Ada overhead HTTP tetapi dapat diterima untuk MVP.

## ADR-004 — PostgreSQL dan PostGIS

**Tanggal:** 2026-08-01  
**Status:** Accepted

**Alasan:** Kebutuhan geofence, distance, point-in-polygon, dan histori spasial.

## ADR-005 — Vue 3 dan MapLibre GL JS

**Tanggal:** 2026-08-01  
**Status:** Accepted

**Alasan:** Frontend ringan, open-source, dan cocok untuk layer peta interaktif.

## Template ADR

```markdown
## ADR-XXX — Judul

**Tanggal:** YYYY-MM-DD  
**Status:** Proposed | Accepted | Deprecated | Superseded  
**Pemilik:**

**Konteks:**

**Opsi yang dipertimbangkan:**

**Keputusan:**

**Alasan:**

**Konsekuensi positif:**

**Konsekuensi negatif:**

**Rencana migrasi:**
```
