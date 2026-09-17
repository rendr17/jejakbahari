# Performance Testing with JMeter — JejakBahari

Performance & load testing untuk endpoint kritis dan worker burst. Sesuai `16_TESTING.md` §6.

## Prasyarat

- Java 17+ (JRE/JDK)
- Apache JMeter 5.6+ — download dari https://jmeter.apache.org/download_jmeter.cgi

## Struktur

```text
tests/performance-jmeter/
├── README.md
├── plans/
│   ├── latest-positions-load.jmx       # Load test /positions/latest
│   ├── vessel-list-load.jmx            # Load test /vessels
│   ├── history-query-load.jmx          # Load test history 2000 points
│   └── worker-burst.jmx                # Simulasi 50 msg/sec ke internal API
├── data/
│   └── positions-burst.csv             # CSV data feed untuk worker burst
└── results/
    └── .gitkeep                        # Output JTL/HTML (gitignored)
```

## Menjalankan Test

### GUI mode (untuk develop/debug test plan)
```bash
jmeter -t plans/latest-positions-load.jmx
```

### CLI mode (untuk eksekusi dan CI)
```bash
jmeter -n -t plans/latest-positions-load.jmx \
  -l results/latest-positions.jtl \
  -e -o results/latest-positions-report/
```

### Parameter via command line
```bash
jmeter -n -t plans/latest-positions-load.jmx \
  -JbaseUrl=localhost -Jport=8000 \
  -Jusers=100 -Jrampup=10 -Jduration=60 \
  -l results/latest-positions.jtl
```

Parameter per plan:

| Plan | Parameter tambahan |
|------|--------------------|
| `latest-positions-load.jmx` | `-JmaxMs=500` (duration assertion threshold) |
| `vessel-list-load.jmx` | `-JmaxMs=500` |
| `history-query-load.jmx` | `-JvesselId=<uuid>` (wajib), `-JmaxMs=2000` |
| `worker-burst.jmx` | `-JinternalToken=<INTERNAL_API_TOKEN>` (wajib), `-Jloops=60`, `-JcsvFile=data/positions-burst.csv` |

Jalankan dari direktori `tests/performance-jmeter/` agar path CSV default `data/positions-burst.csv` resolve dengan benar.

> Catatan: `DurationAssertion` bersifat strict per-request (bukan p95). Untuk evaluasi p95,
> lihat percentiles di HTML report (`-e -o results/<name>-report/`).

## Baseline Performance Target

Sesuai `16_TESTING.md` §6:

| Skenario | Target | Metrik |
|----------|--------|--------|
| 100 vessel markers di map | < 2s render | Response time p99 |
| 50 updates/sec worker burst | 0 message loss | Error rate 0% |
| Latest positions API (bbox) | < 500ms p95 | Response time |
| History 2000 points | < 2s p95 | Response time |
| Database retention job | < 30s | Execution time |

## Test Plan Description

### latest-positions-load.jmx
- Thread Group: `${__P(users,100)}` users, ramp-up `${__P(rampup,10)}`s, duration `${__P(duration,60)}`s
- HTTP Request: GET `/api/v1/positions/latest?bbox=95,-11,141,6`
- Assertions: response code 200, body `"success":true`, duration < 500ms

### vessel-list-load.jmx
- Thread Group: `${__P(users,50)}` users, ramp-up 10s, duration 60s
- HTTP Requests: GET `/api/v1/vessels` + GET `/api/v1/vessels?q=KMP&per_page=10`
- Assertions: response code 200, duration < 500ms

### history-query-load.jmx
- Thread Group: `${__P(users,20)}` users, ramp-up 10s, duration 60s
- HTTP Request: GET `/api/v1/vessels/${vesselId}/positions/history?limit=2000`
- **Wajib:** `-JvesselId=<uuid>` — UUID vessel public dengan data history
- Assertions: response code 200, duration < 2000ms

### worker-burst.jmx
- Thread Group: `${__P(users,50)}` producers, ramp-up 1s, duration 60s
- **ConstantThroughputTimer**: `${__P(rate,3000)}` samples/min (~50 msg/sec)
- HTTP Request: POST `/api/internal/v1/positions` (JSON body)
- Header: `Authorization: Bearer ${__P(internalToken,CHANGE_ME)}` — **wajib** `-JinternalToken=<token>`
- CSV Data Set: `positions-burst.csv` — **MMSI harus kapal VERIFIED + active** di database
- `source_timestamp`/`received_at` di-generate dinamis via `${__time()}` (payload CSV tidak berisi timestamp agar selalu fresh, max age 300 detik)
- `raw_message_id` unik via `${__UUID()}` untuk menghindari dedupe
- Assertions: response code matches `^(200|422|429)$`
  - `200` = accepted
  - `422` = STALE_MESSAGE/UNKNOWN_MMSI (perilaku dedupe yang benar, bukan crash)
  - `429` = internal throttle `3000/min` bekerja (perilaku rate-limit yang benar)
  - **5xx = FAIL** — indikasi crash di bawah load

## Mendapatkan Internal Token

```bash
# Token ada di backend/.env — INTERNAL_API_TOKEN
# atau generate untuk test:
php artisan tinker --execute="echo config('services.internal.token');"
```

## Integrasi CI

JMeter bisa dijalankan di Jenkins pipeline:
```groovy
stage('Performance Test') {
    sh 'jmeter -n -t tests/performance-jmeter/plans/latest-positions-load.jmx -l results/latest.jtl -e -o results/report/'
    publishHTML([reportDir: 'results/report', reportFiles: 'index.html', reportName: 'Performance Report'])
}
```

## Catatan

- Jalankan performance test di staging, bukan production.
- Pastikan database memiliki seed data (minimal 20 vessel, 2000 history points).
- Worker burst test butuh internal token yang valid.
- Simpan hasil di `results/` (sudah di .gitignore).
