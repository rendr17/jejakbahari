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
- Thread Group: 100 users, ramp-up 10s, duration 60s
- HTTP Request: GET /api/v1/positions/latest?bbox=95,-11,141,6
- Assertions: Response 200, response time < 500ms

### worker-burst.jmx
- Thread Group: 50 users, ramp-up 1s, loop 60x
- HTTP Request: POST /api/internal/v1/positions
- CSV Data Set: positions-burst.csv (MMSI, lat, lon, timestamp)
- Assertions: Response 200, accepted=true

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
