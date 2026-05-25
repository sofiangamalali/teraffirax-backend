import sys
import json
import time
import os
import re
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC


def init_driver():
    options = Options()
    options.add_argument("--headless=new")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--disable-gpu")
    options.add_argument("--window-size=1920,1080")
    options.add_argument("--lang=ar-EG")
    options.add_argument(
        "user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
        "AppleWebKit/537.36 (KHTML, like Gecko) "
        "Chrome/120.0.0.0 Safari/537.36"
    )
    options.binary_location = "/usr/bin/google-chrome"
    return webdriver.Chrome(options=options)


def clean_text(text: str) -> str:
    """إزالة Unicode direction marks وأي رموز غير مرغوبة"""
    return re.sub(r'[\u200e\u200f\u202a-\u202e\u2066-\u2069]', '', text).strip()


def scrape_and_screenshot(asin: str, max_retries: int = 3, retry_delay: int = 5):
    product_url = f"https://www.amazon.eg/dp/{asin}?language=ar_AE"

    for attempt in range(1, max_retries + 1):
        driver = None
        try:
            print(f"[attempt {attempt}/{max_retries}] Starting scrape for {asin}", flush=True)
            driver = init_driver()
            driver.get(product_url)
            wait = WebDriverWait(driver, 25)

            # انتظر productTitle
            try:
                wait.until(EC.presence_of_element_located((By.ID, "productTitle")))
            except Exception:
                print(f"[attempt {attempt}] productTitle not found", flush=True)
                driver.quit()
                driver = None
                if attempt < max_retries:
                    time.sleep(retry_delay)
                    continue
                else:
                    return {"success": False, "in_stock": False, "error": "productTitle not found after retries"}

            # تحقق من زرار السلة
            try:
                driver.find_element(By.ID, "add-to-cart-button")
                in_stock = True
            except Exception:
                in_stock = False

            if not in_stock:
                return {"success": True, "in_stock": False}

            # ───── السعر الحالي ─────
            price_after = None
            try:
                price_whole_el = driver.find_element(
                    By.CSS_SELECTOR, "span.priceToPay span.a-price-whole"
                )
                # get_attribute("textContent") أكثر دقة من .text
                price_whole = clean_text(price_whole_el.get_attribute("textContent"))
                price_whole = re.sub(r'[^\d]', '', price_whole)  # أرقام فقط

                try:
                    price_fraction_el = driver.find_element(
                        By.CSS_SELECTOR, "span.priceToPay span.a-price-fraction"
                    )
                    price_fraction = clean_text(price_fraction_el.get_attribute("textContent"))
                    price_fraction = re.sub(r'[^\d]', '', price_fraction)
                except Exception:
                    price_fraction = "00"

                if price_whole:
                    price_after = f"{price_whole}.{price_fraction or '00'}"
            except Exception:
                price_after = None

            # ───── السعر قبل الخصم ─────
            price_before = None
            try:
                price_before_el = driver.find_element(
                    By.CSS_SELECTOR, "span.basisPrice span.a-price.a-text-price span.a-offscreen"
                )
                raw = clean_text(price_before_el.get_attribute("textContent"))
                # إزالة رمز العملة وأي فواصل
                raw = re.sub(r'[^\d.]', '', raw)
                price_before = raw if raw else None
            except Exception:
                # fallback
                try:
                    price_before_el = driver.find_element(
                        By.CSS_SELECTOR, "span.a-price.a-text-price span.a-offscreen"
                    )
                    raw = clean_text(price_before_el.get_attribute("textContent"))
                    raw = re.sub(r'[^\d.]', '', raw)
                    price_before = raw if raw else None
                except Exception:
                    price_before = None

            # ───── نسبة الخصم ─────
            discount = None
            try:
                discount_el = driver.find_element(By.CLASS_NAME, "savingsPercentage")
                raw_discount = clean_text(discount_el.get_attribute("textContent"))
                # استخرج الأرقام فقط → "21"
                discount = re.sub(r'[^\d]', '', raw_discount)
                discount = discount if discount else None
            except Exception:
                # حساب الخصم يدوياً لو مش موجود
                if price_after and price_before:
                    try:
                        pa = float(price_after)
                        pb = float(price_before)
                        if pb > 0:
                            discount = str(round((pb - pa) / pb * 100))
                    except Exception:
                        discount = None

            # ───── صورة المنتج ─────
            image = None
            try:
                image = driver.find_element(By.ID, "landingImage").get_attribute("src")
            except Exception:
                pass

            # ───── اسم المنتج ─────
            title = None
            try:
                title = clean_text(driver.find_element(By.ID, "productTitle").text)
            except Exception:
                pass

            # ───── Screenshot ─────
            screenshot_path = None
            try:
                script_dir      = os.path.dirname(os.path.abspath(__file__))
                screenshot_path = os.path.join(script_dir, f"{asin}.png")
                time.sleep(2)
                driver.save_screenshot(screenshot_path)
                if not os.path.exists(screenshot_path):
                    screenshot_path = None
            except Exception:
                screenshot_path = None

            return {
                "success":         True,
                "in_stock":        True,
                "title":           title,
                "price_after":     price_after,
                "price_before":    price_before,
                "discount":        discount,
                "image":           image,
                "screenshot_path": screenshot_path,
            }

        except Exception as e:
            print(f"[attempt {attempt}] Exception: {e}", flush=True)
            if attempt < max_retries:
                time.sleep(retry_delay)
            else:
                return {"success": False, "in_stock": False, "error": str(e)}

        finally:
            if driver:
                try:
                    driver.quit()
                except Exception:
                    pass

    return {"success": False, "in_stock": False, "error": "all retries exhausted"}


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "error": "No ASIN provided"}))
        sys.exit(1)

    asin   = sys.argv[1].strip()
    result = scrape_and_screenshot(asin)
    print(json.dumps(result, ensure_ascii=False))
    sys.exit(0 if result.get("success") else 1)