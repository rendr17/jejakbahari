"""Page object untuk vessel detail page JejakBahari."""
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from pages.base_page import BasePage


class VesselDetailPage(BasePage):
    DETAIL_PAGE = (By.CSS_SELECTOR, "[data-testid='vessel-detail-page']")
    BACK_BUTTON = (By.CSS_SELECTOR, "[data-testid='vessel-detail-back']")
    LOADING = (By.CSS_SELECTOR, "[data-testid='vessel-detail-loading']")
    ERROR = (By.CSS_SELECTOR, "[data-testid='vessel-detail-error']")
    RETRY = (By.CSS_SELECTOR, "[data-testid='vessel-detail-retry']")
    VESSEL_NAME = (By.CSS_SELECTOR, "[data-testid='vessel-detail-page'] h1")
    EVIDENCE_ITEM_PREFIX = "evidence-"

    def open_detail(self, vessel_id):
        self.open(f"/kapal/{vessel_id}")

    def wait_for_loaded(self, timeout=30):
        """Wait for detail page, then for loading spinner to disappear."""
        WebDriverWait(self.driver, timeout).until(
            EC.presence_of_element_located(self.DETAIL_PAGE)
        )
        try:
            WebDriverWait(self.driver, 10).until_not(
                EC.presence_of_element_located(self.LOADING)
            )
        except Exception:
            pass

    def is_error(self):
        return len(self.driver.find_elements(*self.ERROR)) > 0

    def get_vessel_name(self):
        return self.get_text(self.VESSEL_NAME)

    def get_evidence_items(self):
        return self.driver.find_elements(
            By.CSS_SELECTOR, f"[data-testid^='{self.EVIDENCE_ITEM_PREFIX}']"
        )

    def click_back(self):
        self.click(self.BACK_BUTTON)

    def click_retry(self):
        self.click(self.RETRY)
