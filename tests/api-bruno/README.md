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

Environment file ada di `environments/` folder. Load di Bruno GUI atau CLI.

### Local (`environments/Local.bru`)
```text
baseUrl: http://localhost:8000/api/v1
internalUrl: http://localhost:8000/api/internal/v1
adminToken: (isi setelah login)
internalToken: (isi dari backend .env WORKER_INTERNAL_TOKEN)
testVesselId: (isi UUID vessel public dari seeder)
privateVesselId: (isi UUID vessel private dari seeder)
testOperatorId: (isi UUID operator dari seeder)
```

### Staging (`environments/Staging.bru`)
```text
baseUrl: https://staging.jejakbahari.example/api/v1
internalUrl: https://staging.jejakbahari.example/api/internal/v1
```

### Cara Mendapatkan Test Data ID

```bash
cd backend
php artisan tinker
>>> $v = App\Models\Vessel::where('public_visible', true)->first();
>>> echo $v->id;  # → testVesselId
>>> $pv = App\Models\Vessel::where('public_visible', false)->first();
>>> echo $pv->id;  # → privateVesselId
>>> $o = App\Models\Operator::first();
>>> echo $o->id;  # → testOperatorId
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
