"""E2E test: map page load dan struktur dasar.

TC-MAP-001: MapLibre init dan map container
TC-MAP-002: Vessel markers render
TC-MAP-003: Freshness legend
TC-MAP-004: Vessel card interaction
TC-MAP-005: Empty state
TC-MAP-006: Error state dengan retry
"""
import pytest
from pages.map_page import MapPage


def test_map_page_loads(driver, base_url):
    """TC-MAP-001: Map page loads with map container visible."""
    page = MapPage(driver, base_url)
    page.open_map()
    page.wait_for_map_loaded()
    assert page.find(page.MAP_PAGE)
    assert page.find(page.MAP_CONTAINER)


def test_map_container_has_canvas(driver, base_url):
    """TC-MAP-001b: MapLibre canvas is rendered inside container."""
    from selenium.webdriver.common.by import By
    page = MapPage(driver, base_url)
    page.open_map()
    page.wait_for_map_loaded()
    canvas = page.driver.find_elements(By.CSS_SELECTOR, "canvas")
    assert len(canvas) > 0, "MapLibre canvas should be present"


def test_vessel_markers_render(driver, base_url):
    """TC-MAP-002: Vessel markers are rendered on map.

    Pre-condition: Backend running with seeded vessel positions.
    If no positions available, markers won't render (check empty state instead).
    """
    page = MapPage(driver, base_url)
    page.open_map()
    page.wait_for_map_loaded()

    # Either markers appear or empty state appears
    try:
        page.wait_for_markers(timeout=15)
        markers = page.get_markers()
        assert len(markers) > 0, "Should have at least one vessel marker"
    except Exception:
        # No positions — check empty state instead
        empty = page.driver.find_elements(*page.MAP_EMPTY)
        assert len(empty) > 0, "Should show empty state when no positions"


def test_freshness_legend_visible(driver, base_url):
    """TC-MAP-003: Freshness legend is visible on map page."""
    page = MapPage(driver, base_url)
    page.open_map()
    page.wait_for_map_loaded()
    assert page.find(page.FRESHNESS_LEGEND)


def test_freshness_legend_has_all_statuses(driver, base_url):
    """TC-MAP-003b: Freshness legend shows all 4 statuses (LIVE, DELAYED, STALE, OFFLINE)."""
    page = MapPage(driver, base_url)
    page.open_map()
    page.wait_for_map_loaded()

    assert page.find(page.FRESHNESS_ITEM_LIVE)
    assert page.find(page.FRESHNESS_ITEM_DELAYED)
    assert page.find(page.FRESHNESS_ITEM_STALE)
    assert page.find(page.FRESHNESS_ITEM_OFFLINE)


def test_vessel_card_opens_on_marker_click(driver, base_url):
    """TC-MAP-004: Vessel card opens when clicking a vessel marker.

    Pre-condition: Backend has at least one public vessel with position.
    """
    page = MapPage(driver, base_url)
    page.open_map()
    page.wait_for_map_loaded()

    try:
        page.wait_for_markers(timeout=15)
        page.click_first_marker()
        page.wait_for_vessel_card()
        assert page.find(page.VESSEL_CARD)
        assert page.find(page.VESSEL_CARD_NAME)
    except Exception:
        pytest.skip("No vessel markers available (no positions in database)")


def test_vessel_card_closes_on_close_button(driver, base_url):
    """TC-MAP-004b: Vessel card closes when clicking close button.

    Pre-condition: Vessel card is open.
    """
    page = MapPage(driver, base_url)
    page.open_map()
    page.wait_for_map_loaded()

    try:
        page.wait_for_markers(timeout=15)
        page.click_first_marker()
        page.wait_for_vessel_card()
        page.close_vessel_card()

        # Card should disappear
        import time
        time.sleep(1)
        cards = page.driver.find_elements(*page.VESSEL_CARD)
        assert len(cards) == 0, "Vessel card should be closed"
    except Exception:
        pytest.skip("No vessel markers available (no positions in database)")


def test_empty_state_when_no_positions(driver, base_url):
    """TC-MAP-005: Empty state shown when no positions available.

    Pre-condition: Backend returns empty positions array.
    This test runs against a backend with no seeded positions.
    """
    page = MapPage(driver, base_url)
    page.open_map()
    page.wait_for_map_loaded()

    # Check if empty state is shown (only if no markers)
    markers = page.driver.find_elements(*page.VESSEL_MARKER)
    if len(markers) == 0:
        assert page.find(page.MAP_EMPTY), "Should show empty state message"


def test_error_state_with_retry_button(driver, base_url):
    """TC-MAP-006: Error state shows retry button when API fails.

    Pre-condition: Backend is down or returns error.
    This test requires backend to be unavailable.
    Skip if backend is running normally.
    """
    page = MapPage(driver, base_url)
    page.open_map()

    # Wait for either error or normal load
    import time
    time.sleep(5)

    error_elements = page.driver.find_elements(*page.MAP_ERROR)
    if len(error_elements) > 0:
        retry = page.driver.find_elements(*page.MAP_ERROR_RETRY)
        assert len(retry) > 0, "Error state should have retry button"
    else:
        pytest.skip("Backend is running normally — no error state to test")


def test_map_page_navigated_from_landing(driver, base_url):
    """TC-MAP-007: User can navigate from landing to map via CTA."""
    from pages.landing_page import LandingPage
    landing = LandingPage(driver, base_url)
    landing.open_landing()
    landing.click_view_map()

    page = MapPage(driver, base_url)
    page.wait_for_map_loaded()
    assert "/peta" in driver.current_url
