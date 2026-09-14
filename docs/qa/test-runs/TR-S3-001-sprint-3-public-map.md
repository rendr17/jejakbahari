# Test Run Report — Sprint 3: Public Map

**Test Run ID:** TR-S3-001
**Sprint:** Sprint 3 — Public Map
**Tanggal Eksekusi:** [isi tanggal]
**Tester:** [nama]
**Environment:** Local
**Base URL:** http://localhost:8000/api/v1

## 1. Ringkasan Eksekusi

| Metrik | Nilai |
|--------|-------|
| Total Test Case | 17 |
| Pass | [isi] |
| Fail | [isi] |
| Blocked | [isi] |
| Skipped | [isi] |
| Pass Rate | [isi]% |
| Durasi | [isi] menit |

## 2. Hasil per Test Case

| TC ID | Title | Priority | Status | Response Time | Notes |
|-------|-------|----------|--------|---------------|-------|
| TC-PUB-001 | Vessel list - Default | P0 | [PASS/FAIL] | [ms] | |
| TC-PUB-002 | Vessel list - Search by name | P0 | [PASS/FAIL] | [ms] | |
| TC-PUB-003 | Vessel list - Search by MMSI | P1 | [PASS/FAIL] | [ms] | |
| TC-PUB-004 | Vessel list - Pagination | P0 | [PASS/FAIL] | [ms] | |
| TC-PUB-005 | Vessel list - Per page max 100 | P1 | [PASS/FAIL] | [ms] | |
| TC-PUB-006 | Vessel list - Filter by operator | P1 | [PASS/FAIL] | [ms] | |
| TC-PUB-007 | Vessel list - Empty search | P1 | [PASS/FAIL] | [ms] | |
| TC-PUB-008 | Vessel list - Includes freshness | P0 | [PASS/FAIL] | [ms] | |
| TC-PUB-009 | Vessel detail - Valid | P0 | [PASS/FAIL] | [ms] | |
| TC-PUB-010 | Vessel detail - 404 private | P0 | [PASS/FAIL] | [ms] | |
| TC-PUB-011 | Vessel detail - 404 non-existent | P1 | [PASS/FAIL] | [ms] | |
| TC-PUB-012 | Latest positions - No filter | P0 | [PASS/FAIL] | [ms] | |
| TC-PUB-013 | Latest positions - BBox filter | P0 | [PASS/FAIL] | [ms] | |
| TC-PUB-014 | Latest positions - BBox excludes | P1 | [PASS/FAIL] | [ms] | |
| TC-PUB-015 | Latest positions - Invalid bbox | P2 | [PASS/FAIL] | [ms] | |
| TC-PUB-016 | Latest positions - Has freshness | P0 | [PASS/FAIL] | [ms] | |
| TC-PUB-017 | Health check | P1 | [PASS/FAIL] | [ms] | |

## 3. Bug Ditemukan

| Bug ID | TC ID | Severity | Summary | Status |
|--------|-------|----------|---------|--------|
| — | — | — | [isi jika ada] | — |

## 4. Hasil per Priority

| Priority | Total | Pass | Fail | Pass Rate |
|----------|-------|------|------|-----------|
| P0 | 8 | [isi] | [isi] | [isi]% |
| P1 | 7 | [isi] | [isi] | [isi]% |
| P2 | 2 | [isi] | [isi] | [isi]% |

## 5. Exit Criteria Check

- [ ] 100% test case P0 lulus
- [ ] Tidak ada bug P0 open
- [ ] Bug P1 open <= 2 (dengan workaround)
- [ ] CI hijau (lint, typecheck, build)
- [ ] Regression suite lulus

## 6. Catatan

[catatan tambahan, temuan, observasi]

## 7. Sign-off

- **QA Engineer:** [nama] — [tanggal]
- **Status:** APPROVED / REJECTED / CONDITIONAL

---

## Contoh Hasil Eksekusi (Template — isi setelah run)

Berikut contoh format hasil setelah Bruno CLI dijalankan:

```bash
# Jalankan semua test Sprint 3
bru run tests/api-bruno/jejakbahari-api/Public --env Local

# Output:
# ✓ Vessel List - Default (200, 45ms)
# ✓ Vessel List - Search by Name (200, 38ms)
# ✓ Vessel List - Search by MMSI (200, 42ms)
# ✓ Vessel List - Pagination (200, 51ms)
# ✓ Vessel List - Per Page Max 100 (200, 39ms)
# ✗ Vessel List - Filter by Operator (200, 44ms) — Assertion failed: operator_id not set
# ✓ Vessel List - Empty Search (200, 35ms)
# ✓ Vessel List - Includes Freshness (200, 47ms)
# ...
#
# Results: 16 passed, 1 failed, 0 skipped
```

### Cara Merekam Hasil

1. Jalankan `bru run` dengan flag `--reporter-json` untuk output terstruktur
2. Atau jalankan dengan `--reporter-html` untuk report HTML
3. Simpan output di `docs/qa/test-runs/`
4. Update tabel di atas dengan hasil aktual
5. Jika ada fail, buat bug di JIRA dan catat Bug ID
