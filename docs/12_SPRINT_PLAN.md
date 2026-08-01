# 12 — Sprint Plan

Asumsi sprint dua minggu dan satu developer utama. Sesuaikan kapasitas bila tim berubah.

## Sprint 0 — Foundation

**Goal:** repository siap dikembangkan.

- Buat frontend, backend, worker.
- Setup lint, format, typecheck, test.
- Setup PostgreSQL/PostGIS local.
- Implement migration awal.
- Setup CI.
- Tambahkan environment examples.
- Finalisasi identitas JejakBahari dan baseline UI dari referensi yang disetujui.
- Implementasi landing Phase 1: frontend Vue, app shell, routing, design tokens, dan baseline quality checks. `DONE`
- Implementasi landing Phase 2: hero, CTA, badge transparansi, dan ilustrasi lintasan maritim. `DONE`
- Implementasi landing Phase 3–5: scrollytelling, transparansi data, responsive, dan accessibility. `READY`

**Exit:** semua proyek build dan test baseline lulus.

## Sprint 1 — Registry dan Admin Dasar

- Authentication admin.
- Operator CRUD.
- Vessel CRUD.
- Data source dan evidence.
- Verification status.
- Audit log dasar.

**Exit:** admin dapat membuat vessel terverifikasi dengan MMSI.

## Sprint 2 — AIS Worker

- Provider adapter interface.
- WebSocket connection.
- Whitelist loading.
- Validation dan normalization.
- Reconnect/backoff.
- Internal API.
- Heartbeat.

**Exit:** posisi valid untuk vessel whitelist tersimpan sebagai latest.

## Sprint 3 — Public Map

- MapLibre setup.
- Latest positions endpoint.
- Vessel markers.
- Freshness legend.
- Vessel popup/card.
- Error, loading, empty state.

**Exit:** pengguna dapat melihat posisi terakhir kapal.

## Sprint 4 — Search dan Detail

- Global search.
- Vessel list.
- Vessel detail.
- Source and verification display.
- Responsive layout.

**Exit:** pengguna dapat menemukan dan memahami satu kapal.

## Sprint 5 — History dan Ports

- History sampling.
- History API dan layer.
- Port CRUD.
- Route CRUD.
- Port markers.

**Exit:** histori 24 jam dan konteks port tersedia.

## Sprint 6 — Geofence dan Realtime

- Geofence evaluation.
- Port events.
- Reverb.
- Frontend realtime subscription.
- REST resync fallback.

**Exit:** posisi dapat diperbarui tanpa refresh dan event pelabuhan tercatat.

## Sprint 7 — Hardening dan Release

- Security review.
- Performance test.
- E2E test.
- Deployment.
- Monitoring.
- Data seed awal.
- Documentation review.

**Exit:** MVP siap demo publik.
