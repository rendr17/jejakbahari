"""Base page object untuk JejakBahari."""
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC


class BasePage:
    def __init__(self, driver, base_url):
        self.driver = driver
        self.base_url = base_url

    def open(self, path="/"):
        self.driver.get(f"{self.base_url}{path}")

    def find(self, locator, timeout=10):
        return WebDriverWait(self.driver, timeout).until(
            EC.presence_of_element_located(locator)
        )

    def click(self, locator, timeout=10):
        el = WebDriverWait(self.driver, timeout).until(
            EC.element_to_be_clickable(locator)
        )
        el.click()

    def get_text(self, locator, timeout=10):
        return self.find(locator, timeout).text
