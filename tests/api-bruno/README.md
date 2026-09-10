# API Testing with Bruno — JejakBahari

Collection Bruno untuk testing API JejakBahari. Bruno adalah open-source API client alternatif Postman, menyimpan collection sebagai file (version-control friendly).

## Install Bruno

Download: https://www.usebruno.com/downloads

 atau via package manager:

```bash
# Windows
winget install bruno.bruno

# macOS
brew install --cask bruno
```

## Struktur Collection

```text
tests/api-bruno/
├── README.md                  # Dokumen ini
├── bruno.json                 # Bruno config
└── jejakbahari-api/
    ├── bruno.json             # Collection config
    ├── Public/
    │   ├── Health.bru
    │   ├── Vessel List.bru
    │   ├── Vessel Detail.bru
    │   ├── Latest Positions.bru
    │   └── History.bru
    ├── Admin/
    │   ├── Auth/
    │   │   ├── Login.bru
    │   │   ├── Logout.bru
    │   │   └── Me.bru
    │   ├── Operators.bru
    │   ├── Vessels.bru
    │   ├── Verify Vessel.bru
    │   └── Audit Logs.bru
    └── Internal/
        ├── Whitelist.bru
        ├── Post Position.bru
        └── Heartbeat.bru
```

## Environment

Buat environment di Bruno:

### Local
```json
{
  "baseUrl": "http://localhost:8000/api/v1",
  "internalUrl": "http://localhost:8000/api/internal/v1",
  "adminToken": "",
  "internalToken": ""
}
```

### Staging
```json
{
  "baseUrl": "https://staging.jejakbahari.example/api/v1",
  "internalUrl": "https://staging.jejakbahari.example/api/internal/v1",
  "adminToken": "",
  "internalToken": ""
}
```

## Workflow Testing

1. **Login sebagai admin** → simpan token ke variable `adminToken`
2. **Test CRUD operator/vessel** dengan header `Authorization: Bearer {{adminToken}}`
3. **Test public endpoint** tanpa auth
4. **Test internal endpoint** dengan `internalToken` (dari `.env` worker)

## Test Case Mapping

Lihat `docs/qa/TRACEABILITY_MATRIX.md` untuk mapping test case ID ke request Bruno.

## Catatan

- Jangan commit token real. Gunakan environment variable Bruno.
- File `.bru` adalah format text, aman untuk version control.
- Untuk automated run di CI, gunakan Bruno CLI: `bru run --env Local`
