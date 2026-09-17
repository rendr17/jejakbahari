"""E2E test: admin login.

TC-ADMIN-001: Admin login page renders
TC-ADMIN-002: Login dengan kredensial salah menampilkan error
TC-ADMIN-003: Login valid menampilkan dashboard

NOTE: Admin UI (FE-007) belum diimplementasi. Semua test di-skip sampai
halaman /admin tersedia — page object sudah disiapkan untuk kontrak
selector yang direncanakan.
"""
import pytest
from pages.admin_page import AdminPage


def _require_admin_page(driver, base_url):
    page = AdminPage(driver, base_url)
    page.open_admin()
    if not page.is_available():
        pytest.skip("Admin UI not implemented yet (FE-007)")
    return page


def test_admin_login_page_renders(driver, base_url):
    """TC-ADMIN-001: /admin shows the login form."""
    page = _require_admin_page(driver, base_url)
    assert page.find(page.EMAIL_INPUT)
    assert page.find(page.PASSWORD_INPUT)
    assert page.find(page.SUBMIT_BUTTON)


def test_admin_login_invalid_credentials(driver, base_url):
    """TC-ADMIN-002: Wrong credentials show an error, no dashboard."""
    page = _require_admin_page(driver, base_url)
    page.login("invalid@example.com", "wrong-password")
    error = page.find(page.LOGIN_ERROR, timeout=10)
    assert error


def test_admin_login_valid_credentials(driver, base_url):
    """TC-ADMIN-003: Valid credentials reach the dashboard.

    Requires a seeded admin user — credentials must come from env vars,
    never hardcoded.
    """
    import os
    email = os.environ.get("JB_ADMIN_EMAIL")
    password = os.environ.get("JB_ADMIN_PASSWORD")
    if not email or not password:
        pytest.skip("JB_ADMIN_EMAIL/JB_ADMIN_PASSWORD env vars not set")

    page = _require_admin_page(driver, base_url)
    page.login(email, password)
    page.wait_for_dashboard()
    assert page.find(page.DASHBOARD)
