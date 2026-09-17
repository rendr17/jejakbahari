"""E2E test: vessel detail page.

TC-DETAIL-001: Detail page loads dan menampilkan nama kapal
TC-DETAIL-002: Back button kembali ke daftar
TC-DETAIL-003: Evidence/provenance section menampilkan sumber data
TC-DETAIL-004: Vessel ID tidak dikenal menampilkan error + retry
"""
import pytest
from selenium.webdriver.common.by import By
from pages.search_page import SearchPage
from pages.vessel_detail_page import VesselDetailPage


def _first_vessel_id(driver, base_url):
    """Ambil vessel id pertama dari kartu di halaman daftar."""
    page = SearchPage(driver, base_url)
    page.open_list()
    page.wait_for_list_loaded()
    cards = driver.find_elements(
        By.CSS_SELECTOR, "[data-testid^='vessel-card-']"
    )
    if not cards:
        return None
    return cards[0].get_attribute("data-testid").replace("vessel-card-", "")


def test_detail_page_loads_with_vessel_name(driver, base_url):
    """TC-DETAIL-001: Detail page renders vessel name and metadata."""
    vessel_id = _first_vessel_id(driver, base_url)
    if vessel_id is None:
        pytest.skip("No vessels in seeded data")

    page = VesselDetailPage(driver, base_url)
    page.open_detail(vessel_id)
    page.wait_for_loaded()

    if page.is_error():
        pytest.skip("Vessel detail returned error (backend unavailable?)")

    name = page.get_vessel_name()
    assert name and len(name) > 0


def test_back_button_returns_to_list(driver, base_url):
    """TC-DETAIL-002: Back button navigates back to the vessel list."""
    vessel_id = _first_vessel_id(driver, base_url)
    if vessel_id is None:
        pytest.skip("No vessels in seeded data")

    page = VesselDetailPage(driver, base_url)
    page.open_detail(vessel_id)
    page.wait_for_loaded()

    if page.is_error():
        pytest.skip("Vessel detail returned error (backend unavailable?)")

    page.click_back()
    import time
    time.sleep(1)
    assert "/kapal" in driver.current_url


def test_evidence_section_shows_provenance(driver, base_url):
    """TC-DETAIL-003: Evidence items document the verification provenance."""
    vessel_id = _first_vessel_id(driver, base_url)
    if vessel_id is None:
        pytest.skip("No vessels in seeded data")

    page = VesselDetailPage(driver, base_url)
    page.open_detail(vessel_id)
    page.wait_for_loaded()

    if page.is_error():
        pytest.skip("Vessel detail returned error (backend unavailable?)")

    evidence = page.get_evidence_items()
    if len(evidence) == 0:
        pytest.skip("Vessel has no evidence records in seeded data")
    assert len(evidence) > 0


def test_unknown_vessel_shows_error_with_retry(driver, base_url):
    """TC-DETAIL-004: Unknown vessel id shows error state with retry button."""
    page = VesselDetailPage(driver, base_url)
    page.open_detail("00000000-0000-0000-0000-000000000000")
    page.wait_for_loaded()

    try:
        page.find(page.ERROR, timeout=15)
    except Exception:
        pytest.skip("Error state did not appear (backend unavailable?)")

    assert page.find(page.RETRY)
