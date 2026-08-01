# 20 — RoRo Vessel Registry

## 1. Tujuan

Registry adalah whitelist kapal yang boleh tampil sebagai kapal RoRo publik.

## 2. Required Fields

```text
name
mmsi
vessel_category
verification_status
confidence_score
at least one evidence source
```

Recommended:

```text
imo
call_sign
operator
route assignments
photo reference
active status
```

## 3. Categories

- `RORO_CARGO`
- `ROPAX`
- `FERRY_RORO`
- `UNKNOWN_RORO_REVIEW`

Kategori harus memiliki definisi jelas dan tidak hanya mengikuti AIS ship type.

## 4. Verification Workflow

1. Contributor membuat record draft.
2. MMSI format diperiksa.
3. Duplicate name/MMSI/IMO diperiksa.
4. Evidence ditambahkan.
5. Reviewer membandingkan sumber.
6. Confidence ditetapkan.
7. Status menjadi `VERIFIED` atau `REJECTED`.
8. Vessel verified masuk whitelist worker.

## 5. Evidence Minimum

Untuk status verified, idealnya terdapat:

- satu sumber resmi yang menghubungkan nama dan MMSI/IMO; atau
- dua sumber independen yang konsisten;
- bukti bahwa kapal adalah RoRo/RoPax/ferry RoRo.

## 6. CSV Import Template

```csv
name,mmsi,imo,call_sign,operator,vessel_category,verification_status,confidence_score,source_reference
KMP Example,525123456,IMO1234567,ABCD,Example Operator,ROPAX,REVIEW,75,https://example.org/source
```

## 7. Update dan Conflict

- MMSI conflict harus diblokir.
- Rename kapal tidak menghapus history lama.
- Pergantian operator dicatat dengan valid dates bila penting.
- Record tidak dihapus keras bila sudah memiliki history; gunakan inactive.

## 8. Public Display

Hanya record `VERIFIED`, `active`, dan `public_visible` yang tampil.

## 9. Community Contribution

Setiap contribution harus menjelaskan:

- apa yang berubah;
- sumber;
- tanggal akses;
- alasan confidence;
- apakah data boleh dipublikasikan.
