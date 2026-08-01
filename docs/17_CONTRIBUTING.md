# Contributing

Terima kasih telah berkontribusi pada JejakBahari.

## Sebelum Mulai

1. Baca `AGENTS.md`.
2. Baca `docs/01_PRD.md` dan `docs/02_MVP.md`.
3. Cari issue atau backlog yang relevan.
4. Diskusikan perubahan besar sebelum implementasi.

## Branch

- `main`: stabil dan siap rilis.
- `develop`: integrasi pengembangan jika workflow membutuhkannya.
- `feat/<scope>-<name>`: fitur.
- `fix/<scope>-<name>`: perbaikan.
- `docs/<name>`: dokumentasi.

## Commit

Gunakan Conventional Commits:

```text
feat(map): add vessel popup
fix(worker): ignore stale AIS messages
docs(api): document vessel history endpoint
```

## Pull Request

PR harus memuat:

- masalah;
- solusi;
- perubahan utama;
- cara pengujian;
- screenshot untuk UI;
- migration bila ada;
- perubahan dokumentasi;
- risiko tersisa.

## Kualitas

Sebelum membuka PR:

- formatter berhasil;
- lint berhasil;
- typecheck berhasil;
- test relevan berhasil;
- build berhasil;
- tidak ada secret;
- dokumentasi diperbarui.

## Data Kapal dan Pelabuhan

Kontribusi data harus menyertakan sumber, tanggal akses, confidence, dan catatan lisensi. Lihat `docs/20_RORO_VESSEL_REGISTRY.md` dan `docs/22_OPEN_SOURCE_GUIDE.md`.
