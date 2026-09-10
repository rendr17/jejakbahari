"""E2E test: landing page load dan navigasi."""
import pytest
from pages.landing_page import LandingPage


def test_landing_page_loads(driver, base_url):
    """TC-LAND-001: Landing page load dengan hero dan CTA."""
    page = LandingPage(driver, base_url)
    page.open_landing()
    assert page.find(page.HERO_TITLE)
    assert page.find(page.CTA_VIEW_MAP)


def test_disclaimer_visible(driver, base_url):
    """TC-LAND-002: Disclaimer bukan alat navigasi tampil."""
    page = LandingPage(driver, base_url)
    page.open_landing()
    assert page.find(page.DISCLAIMER)


def test_cta_navigates_to_map(driver, base_url):
    """TC-LAND-003: CTA 'Lihat peta' navigasi ke /peta."""
    page = LandingPage(driver, base_url)
    page.open_landing()
    page.click_view_map()
    assert "/peta" in driver.current_url
