# 26 — Definition of Done

Sebuah task dianggap selesai jika:

- Acceptance criteria terpenuhi.
- Implementasi berada dalam scope.
- Tidak ada hardcode secret.
- Input dan error state ditangani.
- Test relevan ditambahkan atau diperbarui.
- Formatter, lint, typecheck, test, dan build relevan lulus.
- Dokumentasi diperbarui.
- Backlog/sprint diperbarui.
- Security impact ditinjau.
- Risiko tersisa dijelaskan.

Tambahan UI:

- Responsive.
- Accessible.
- Loading, empty, error, stale, offline states tersedia.
- Sesuai design system.

Tambahan API:

- Authorization benar.
- Kontrak terdokumentasi.
- Rate limit dan pagination bila relevan.

Tambahan database:

- Migration tersedia.
- Index dan rollback dipertimbangkan.

Tambahan worker:

- Reconnect, invalid payload, duplicate, stale, dan shutdown diuji.
