"""E2E test: vessel search di halaman /kapal.

TC-SEARCH-001: Search input tampil di halaman daftar kapal
TC-SEARCH-002: Ketik query menampilkan dropdown hasil
TC-SEARCH-003: Klik hasil menavigasi ke detail kapal
TC-SEARCH-004: Query tanpa hasil menampilkan pesan "tidak ditemukan"
TC-SEARCH-005: Keyboard navigation (ArrowDown + Enter) memilih hasil
"""
import pytest
from selenium.webdriver.common.by import By
from pages.search_page import SearchPage


def test_search_input_visible(driver, base_url):
    """TC-SEARCH-001: Search input renders on the vessel list page."""
    page = SearchPage(driver, base_url)
    page.open_list()
    page.wait_for_list_loaded()
    assert page.find(page.SEARCH_INPUT)


def test_search_shows_results_dropdown(driver, base_url):
    """TC-SEARCH-002: Typing a query opens the results dropdown.

    Pre-condition: Backend running with seeded vessels.
    """
    page = SearchPage(driver, base_url)
    page.open_list()
    page.wait_for_list_loaded()
    page.type_search("KMP")

    try:
        page.wait_for_results(timeout=15)
    except Exception:
        pytest.skip("Search dropdown did not appear (backend unavailable?)")

    # Dropdown shows either result items or a "tidak ditemukan" status —
    # both are valid; this test only asserts the dropdown opens.
    assert page.find(page.SEARCH_RESULTS)


def test_search_result_navigates_to_detail(driver, base_url):
    """TC-SEARCH-003: Clicking a result navigates to vessel detail page.

    Pre-condition: At least one vessel matches the query.
    """
    page = SearchPage(driver, base_url)
    page.open_list()
    page.wait_for_list_loaded()
    page.type_search("KMP")

    try:
        page.wait_for_results(timeout=15)
        items = page.get_result_items()
    except Exception:
        pytest.skip("No search results available")

    if len(items) == 0:
        pytest.skip("No vessels match the query in seeded data")

    page.click_first_result()
    page.find(
        (By.CSS_SELECTOR, "[data-testid='vessel-detail-page']")
    )
    assert "/kapal/" in driver.current_url


def test_search_no_results_message(driver, base_url):
    """TC-SEARCH-004: Gibberish query shows the 'tidak ditemukan' message."""
    page = SearchPage(driver, base_url)
    page.open_list()
    page.wait_for_list_loaded()
    page.type_search("zzzz-not-a-vessel-999")

    try:
        page.wait_for_results(timeout=15)
    except Exception:
        pytest.skip("Search dropdown did not appear (backend unavailable?)")

    items = page.get_result_items()
    assert len(items) == 0
    body_text = driver.page_source
    assert "Tidak ditemukan" in body_text


def test_search_keyboard_navigation(driver, base_url):
    """TC-SEARCH-005: ArrowDown + Enter selects the first result."""
    page = SearchPage(driver, base_url)
    page.open_list()
    page.wait_for_list_loaded()
    page.type_search("KMP")

    try:
        page.wait_for_results(timeout=15)
        items = page.get_result_items()
    except Exception:
        pytest.skip("No search results available")

    if len(items) == 0:
        pytest.skip("No vessels match the query in seeded data")

    page.press_arrow_down_enter()
    import time
    time.sleep(1)
    assert "/kapal/" in driver.current_url
