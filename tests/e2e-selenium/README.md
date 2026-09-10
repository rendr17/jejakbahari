# E2E Testing with Selenium — JejakBahari

Automated E2E testing untuk user flow utama JejakBahari. Sesuai `10_USER_FLOW.md` dan `16_TESTING.md` §2.

## Prasyarat

- Python 3.11+ (untuk Selenium WebDriver)
- Browser: Chrome / Firefox
- ChromeDriver / GeckoDriver

## Install

```bash
cd tests/e2e-selenium
python -m venv .venv
.venv\Scripts\activate        # Windows
# source .venv/bin/activate   # macOS/Linux
pip install -r requirements.txt
```

## Struktur

```text
tests/e2e-selenium/
├── README.md
├── requirements.txt
├── conftest.py               # pytest fixtures (driver, base url)
├── pages/                    # Page Object Model
│   ├── base_page.py
│   ├── landing_page.py
│   ├── map_page.py
│   ├── search_page.py
│   ├── vessel_detail_page.py
│   └── admin_page.py
├── tests/
│   ├── test_landing.py
│   ├── test_map.py
│   ├── test_search.py
│   ├── test_vessel_detail.py
│   └── test_admin_login.py
└── reports/                  # HTML report output (gitignored)
```

## Menjalankan Test

```bash
# Semua test
pytest

# Test specific module
pytest tests/test_map.py

# Dengan HTML report
pytest --html=reports/report.html

# Headless mode
pytest --headless
```

## Page Object Model

Setiap halaman direpresentasikan sebagai class. Element locator menggunakan `data-testid` yang harus ditambahkan di komponen Vue.

Contoh:
```python
class MapPage(BasePage):
    MAP_CONTAINER = (By.CSS_SELECTOR, "[data-testid='map-container']")
    VESSEL_MARKER = (By.CSS_SELECTOR, "[data-testid='vessel-marker']")
    FRESHNESS_LEGEND = (By.CSS_SELECTOR, "[data-testid='freshness-legend']")
```

## Test Case Mapping

| Test File | User Flow | Test Case ID |
|-----------|-----------|--------------|
| test_landing.py | Landing page load | TC-LAND-001 |
| test_map.py | Public Map Flow | TC-MAP-001–004 |
| test_search.py | Search Flow | TC-SEARCH-001 |
| test_vessel_detail.py | Vessel detail | TC-DETAIL-001 |
| test_admin_login.py | Admin login + CRUD | TC-AUTH-001, TC-VESSEL-001 |

## Catatan

- Tambahkan `data-testid` di komponen Vue untuk selector yang stabil.
- Tunggu tile MapLibre selesai load sebelum assert marker.
- Gunakan explicit wait (`WebDriverWait`), hindari `sleep`.
- Untuk Katalon alternative, lihat `tests/e2e-katalon/` (opsional).
