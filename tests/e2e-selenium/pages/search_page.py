"""Page object untuk vessel search (dipakai di halaman /kapal)."""
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from pages.base_page import BasePage


class SearchPage(BasePage):
    LIST_PAGE = (By.CSS_SELECTOR, "[data-testid='vessel-list-page']")
    SEARCH_INPUT = (By.CSS_SELECTOR, "[data-testid='vessel-search-input']")
    SEARCH_RESULTS = (By.CSS_SELECTOR, "[data-testid='vessel-search-results']")
    RESULT_ITEM_PREFIX = "vessel-search-result-"
    LIST_LOADING = (By.CSS_SELECTOR, "[data-testid='vessel-list-loading']")
    LIST_ERROR = (By.CSS_SELECTOR, "[data-testid='vessel-list-error']")
    LIST_EMPTY = (By.CSS_SELECTOR, "[data-testid='vessel-list-empty']")

    def open_list(self):
        """Open the vessel list page that hosts the search component."""
        self.open("/kapal")

    def wait_for_list_loaded(self, timeout=30):
        WebDriverWait(self.driver, timeout).until(
            EC.presence_of_element_located(self.LIST_PAGE)
        )
        try:
            WebDriverWait(self.driver, 10).until_not(
                EC.presence_of_element_located(self.LIST_LOADING)
            )
        except Exception:
            pass

    def type_search(self, query):
        """Focus the search input and type a query."""
        el = self.find(self.SEARCH_INPUT)
        el.click()
        el.clear()
        el.send_keys(query)

    def wait_for_results(self, timeout=15):
        """Wait for the results dropdown to appear (results or no-results)."""
        WebDriverWait(self.driver, timeout).until(
            EC.presence_of_element_located(self.SEARCH_RESULTS)
        )

    def get_result_items(self):
        """Return all search result option elements."""
        return self.driver.find_elements(
            By.CSS_SELECTOR, f"[data-testid^='{self.RESULT_ITEM_PREFIX}']"
        )

    def click_first_result(self):
        items = self.get_result_items()
        if items:
            items[0].click()

    def press_arrow_down_enter(self):
        """Keyboard navigation: ArrowDown then Enter selects first result."""
        el = self.find(self.SEARCH_INPUT)
        el.send_keys(Keys.ARROW_DOWN)
        el.send_keys(Keys.ENTER)
