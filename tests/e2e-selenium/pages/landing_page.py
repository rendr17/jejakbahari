"""Page object untuk landing page JejakBahari."""
from selenium.webdriver.common.by import By
from pages.base_page import BasePage


class LandingPage(BasePage):
    HERO_TITLE = (By.CSS_SELECTOR, "[data-testid='hero-title']")
    CTA_VIEW_MAP = (By.CSS_SELECTOR, "[data-testid='cta-view-map']")
    CTA_ABOUT_DATA = (By.CSS_SELECTOR, "[data-testid='cta-about-data']")
    DISCLAIMER = (By.CSS_SELECTOR, "[data-testid='disclaimer']")

    def open_landing(self):
        self.open("/")

    def click_view_map(self):
        self.click(self.CTA_VIEW_MAP)
