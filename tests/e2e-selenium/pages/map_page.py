"""Page object untuk map page JejakBahari."""
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from pages.base_page import BasePage


class MapPage(BasePage):
    MAP_PAGE = (By.CSS_SELECTOR, "[data-testid='map-page']")
    MAP_CONTAINER = (By.CSS_SELECTOR, "[data-testid='map-container']")
    MAP_LOADING = (By.CSS_SELECTOR, "[data-testid='map-loading']")
    MAP_ERROR = (By.CSS_SELECTOR, "[data-testid='map-error']")
    MAP_ERROR_RETRY = (By.CSS_SELECTOR, "[data-testid='map-error-retry']")
    MAP_EMPTY = (By.CSS_SELECTOR, "[data-testid='map-empty']")
    FRESHNESS_LEGEND = (By.CSS_SELECTOR, "[data-testid='freshness-legend']")
    FRESHNESS_ITEM_LIVE = (By.CSS_SELECTOR, "[data-testid='freshness-item-LIVE']")
    FRESHNESS_ITEM_DELAYED = (By.CSS_SELECTOR, "[data-testid='freshness-item-DELAYED']")
    FRESHNESS_ITEM_STALE = (By.CSS_SELECTOR, "[data-testid='freshness-item-STALE']")
    FRESHNESS_ITEM_OFFLINE = (By.CSS_SELECTOR, "[data-testid='freshness-item-OFFLINE']")
    VESSEL_MARKER = (By.CSS_SELECTOR, "[data-testid='vessel-marker']")
    VESSEL_CARD = (By.CSS_SELECTOR, "[data-testid='vessel-card']")
    VESSEL_CARD_NAME = (By.CSS_SELECTOR, "[data-testid='vessel-card-name']")
    VESSEL_CARD_CLOSE = (By.CSS_SELECTOR, "[data-testid='vessel-card-close']")

    def open_map(self):
        self.open("/peta")

    def wait_for_map_loaded(self, timeout=30):
        """Wait for map container to be present and loading overlay to disappear."""
        WebDriverWait(self.driver, timeout).until(
            EC.presence_of_element_located(self.MAP_CONTAINER)
        )
        # Wait for loading to disappear (if it was shown)
        try:
            WebDriverWait(self.driver, 5).until_not(
                EC.presence_of_element_located(self.MAP_LOADING)
            )
        except Exception:
            pass  # Loading might have already disappeared

    def wait_for_markers(self, timeout=30):
        """Wait for at least one vessel marker to appear."""
        WebDriverWait(self.driver, timeout).until(
            EC.presence_of_element_located(self.VESSEL_MARKER)
        )

    def get_markers(self):
        """Return all vessel marker elements."""
        return self.driver.find_elements(*self.VESSEL_MARKER)

    def click_first_marker(self):
        """Click the first vessel marker to open vessel card."""
        markers = self.get_markers()
        if markers:
            markers[0].click()

    def wait_for_vessel_card(self, timeout=10):
        """Wait for vessel card to appear after clicking a marker."""
        WebDriverWait(self.driver, timeout).until(
            EC.presence_of_element_located(self.VESSEL_CARD)
        )

    def close_vessel_card(self):
        """Close the vessel card."""
        self.click(self.VESSEL_CARD_CLOSE)

    def get_freshness_items(self):
        """Return all freshness legend items."""
        return self.driver.find_elements(
            By.CSS_SELECTOR, "[data-testid^='freshness-item-']"
        )

    def click_retry(self):
        """Click the retry button on error state."""
        self.click(self.MAP_ERROR_RETRY)
