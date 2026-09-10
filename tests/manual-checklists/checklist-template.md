# Manual Test Checklist Template — [Fitur/Sprint]

- **Tester:** [nama]
- **Tanggal:** [tanggal]
- **Environment:** Local / Staging
- **Browser:** [Chrome/Firefox/Safari + version]

## Pre-condition

- [ ] Server berjalan
- [ ] Database ter-seed
- [ ] Login sebagai admin (jika perlu)

## Test Steps

| # | Step | Expected | Result | Bug ID |
|---|------|----------|--------|--------|
| 1 | [action] | [expected result] | PASS/FAIL | [JIRA ID] |

## Network Condition

- [ ] Slow 3G — halaman load < 10s
- [ ] Offline — pesan error tampil graceful
- [ ] Koneksi normal — semua fitur bekerja

## Viewport

- [ ] Mobile 375px
- [ ] Tablet 768px
- [ ] Desktop 1280px

## Accessibility

- [ ] Keyboard navigation
- [ ] Screen reader (opsional)
- [ ] Kontras WCAG AA

## Edge Case

- [ ] [edge case 1]
- [ ] [edge case 2]

## Notes

[temuan tambahan]
