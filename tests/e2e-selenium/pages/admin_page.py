"""Page object untuk admin panel JejakBahari.

NOTE: Admin UI (FE-007) belum diimplementasi di frontend. Page object ini
disiapkan untuk kontrak route /admin dan akan selalu di-skip sampai
halaman admin tersedia.
"""
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from pages.base_page import BasePage


class AdminPage(BasePage):
    LOGIN_PAGE = (By.CSS_SELECTOR, "[data-testid='admin-login-page']")
    EMAIL_INPUT = (By.CSS_SELECTOR, "[data-testid='admin-login-email']")
    PASSWORD_INPUT = (By.CSS_SELECTOR, "[data-testid='admin-login-password']")
    SUBMIT_BUTTON = (By.CSS_SELECTOR, "[data-testid='admin-login-submit']")
    LOGIN_ERROR = (By.CSS_SELECTOR, "[data-testid='admin-login-error']")
    DASHBOARD = (By.CSS_SELECTOR, "[data-testid='admin-dashboard']")

    def open_admin(self):
        self.open("/admin")

    def is_available(self, timeout=5):
        """True jika halaman admin login ada (FE-007 sudah diimplementasi)."""
        try:
            WebDriverWait(self.driver, timeout).until(
                EC.presence_of_element_located(self.LOGIN_PAGE)
            )
            return True
        except Exception:
            return False

    def login(self, email, password):
        self.find(self.EMAIL_INPUT).send_keys(email)
        self.find(self.PASSWORD_INPUT).send_keys(password)
        self.click(self.SUBMIT_BUTTON)

    def wait_for_dashboard(self, timeout=15):
        WebDriverWait(self.driver, timeout).until(
            EC.presence_of_element_located(self.DASHBOARD)
        )
