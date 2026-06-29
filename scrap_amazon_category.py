import sys
import time
import re
import json
from datetime import datetime
from urllib.parse import urlencode

import requests
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager

# استيراد الـ config
sys.path.insert(0, __import__("os").path.dirname(__import__("os").path.abspath(__file__)))
from config import LARAVEL_BASE_URL, SCRAPER_TOKEN, BATCH_SIZE, REQUEST_TIMEOUT

BASE_URL           = "https://www.amazon.eg"
SEARCH_URL         = "https://www.amazon.eg/s"
MAX_PAGES_FALLBACK = 100


# ==========================
# Arabic digits
# ==========================
ARABIC_DIGITS = str.maketrans("٠١٢٣٤٥٦٧٨٩", "0123456789")

def arabic_to_int(text: str):
    try:
        return int(text.translate(ARABIC_DIGITS).strip())
    except Exception:
        return None


# ==========================
# HTTP helpers
# ==========================
def get_headers():
    return {
        "Authorization": f"Bearer {SCRAPER_TOKEN}",
        "Accept":        "application/json",
        "Content-Type":  "application/json",
    }


def send_batch_to_laravel(products: list, vendor_id: int, category_id: int) -> dict:
    """
    بعت batch من المنتجات للـ Laravel ingest endpoint.
    بترجع {"created": x, "updated": y, "skipped": z}
    """
    if not products:
        return {"created": 0, "updated": 0, "skipped": 0}

    try:
        resp = requests.post(
            f"{LARAVEL_BASE_URL}/api/scraper/products/upsert",
            headers=get_headers(),
            json={
                "vendor_id":   vendor_id,
                "category_id": category_id,
                "products":    products,
            },
            timeout=REQUEST_TIMEOUT
        )
        resp.raise_for_status()
        return resp.json()
    except requests.exceptions.Timeout:
        print(f"   ⏱️ Ingest request timed out for batch of {len(products)}")
        return {"created": 0, "updated": 0, "skipped": len(products)}
    except Exception as e:
        print(f"   ❌ Ingest request failed: {e}")
        return {"created": 0, "updated": 0, "skipped": len(products)}


# ==========================
# Selenium setup
# ==========================
def setup_driver():
    options = Options()
    options.add_argument("--headless=new")
    options.add_argument("--disable-gpu")
    options.add_argument("--window-size=1920,1080")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-setuid-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--lang=ar-EG")
    options.add_argument("--disable-extensions")
    options.add_argument("--disable-software-rasterizer")
    options.add_argument("--log-level=3")
    options.add_argument("--silent")
    options.add_argument("--no-first-run")
    options.add_argument("--remote-debugging-port=0")

    service = Service(ChromeDriverManager().install())
    driver  = webdriver.Chrome(service=service, options=options)
    driver.set_page_load_timeout(60)
    return driver


def restart_driver(driver):
    try:
        driver.quit()
    except Exception:
        pass
    print("🔄 Restarting Chrome driver...")
    time.sleep(3)
    return setup_driver()


# ==========================
# URL building
# ==========================
def build_search_url(node: str, page: int) -> str:
    """
    Build the Amazon search URL for a given browse node (external_id) and page number.
    The URL follows the pattern:
    https://www.amazon.eg/s?rh=n%3A{node}&s=popularity-rank&fs=true&page={page}&language=ar_AE
    """
    params = {
        "rh": f"n:{node}",
        "s": "popularity-rank",
        "fs": "true",
        "page": str(page),
        "language": "ar_AE"
    }
    return f"{SEARCH_URL}?{urlencode(params)}"


def get_page_url(category: dict, page: int) -> str:
    # استخدم external_id (browse node) لبناء الرابط مباشرةً
    node = category.get("external_id", "")
    if not node:
        raise ValueError("Category missing external_id")
    return build_search_url(node, page)


def get_max_pages(driver) -> int:
    try:
        items = driver.find_elements(
            By.CSS_SELECTOR,
            "span.s-pagination-item.s-pagination-disabled"
        )
        for item in reversed(items):
            n = arabic_to_int(item.text.strip())
            if n and n > 1:
                print(f"📑 Max pages: {n}")
                return n
    except Exception as e:
        print(f"⚠️ Pagination read failed: {e}")
    print(f"📑 Using fallback max pages: {MAX_PAGES_FALLBACK}")
    return MAX_PAGES_FALLBACK


# ==========================
# Price helpers
# ==========================
def normalize_price_text(text: str):
    if not text:
        return None
    t = text.replace("\u200e", " ").replace("\u200f", " ").strip()
    t = t.replace("٬", "").replace(",", "").replace("٫", ".")
    t = re.sub(r"\s+", " ", t)
    parts = re.findall(r"\d+", t)
    if len(parts) >= 2:
        whole, frac = parts[0], parts[1]
        if len(frac) in (1, 2):
            return f"{whole}.{frac.zfill(2)}"
        return whole
    m = re.search(r"\d+(?:\.\d+)?", t)
    return m.group(0) if m else None


def clean_price(text: str):
    t = normalize_price_text(text)
    if not t:
        return None
    try:
        return float(t)
    except Exception:
        return None


def compute_discount_percent(old_price: float, new_price: float) -> float:
    if not old_price or not new_price:
        return 0.0
    if old_price <= 0 or new_price < 0 or old_price <= new_price:
        return 0.0
    return round(((old_price - new_price) / old_price) * 100, 2)


def make_absolute_url(href: str):
    if not href:
        return None
    if href.startswith("http"):
        return href.split("?")[0]
    return (BASE_URL + href if href.startswith("/") else BASE_URL + "/" + href).split("?")[0]


# ==========================
# Card extraction
# ==========================
def extract_card(card) -> dict | None:
    asin = (card.get_attribute("data-asin") or "").strip()
    if not asin:
        return None

    try:
        old_price_text = card.find_element(
            By.CSS_SELECTOR,
            "span.a-price.a-text-price span[aria-hidden='true']"
        ).text.strip()
        new_price_text = card.find_element(
            By.CSS_SELECTOR,
            "span.a-price[data-a-color='base'] span[aria-hidden='true']"
        ).text.strip()
    except Exception:
        return None

    old_price = clean_price(old_price_text)
    new_price = clean_price(new_price_text)

    if old_price is None or new_price is None or old_price <= new_price:
        return None

    title = None
    try:
        title = card.find_element(
            By.CSS_SELECTOR,
            "div[data-cy='title-recipe'] a.a-link-normal h2"
        ).text.strip()
    except Exception:
        pass

    url = None
    try:
        href = card.find_element(
            By.CSS_SELECTOR,
            "div[data-cy='title-recipe'] a.a-link-normal"
        ).get_attribute("href")
        url = make_absolute_url(href)
    except Exception:
        pass

    image_url = None
    try:
        image_url = card.find_element(
            By.CSS_SELECTOR, "img.s-image"
        ).get_attribute("src")
    except Exception:
        pass

    if not title or not url:
        return None

    return {
        "external_id":         asin,
        "title":               title,
        "url":                 url,
        "image_url":           image_url,
        "price":               new_price,
        "original_price":      old_price,
        "discount_percentage": compute_discount_percent(old_price, new_price),
        # rank بيتحدد بعدين لما بنضيفه في الـ loop
    }


# ==========================
# MAIN
# ==========================
def main():
    if len(sys.argv) < 2:
        print("Usage: python scrap_amazon_category.py '<category_json>'")
        sys.exit(1)

    try:
        category = json.loads(sys.argv[1])
    except json.JSONDecodeError as e:
        print(f"❌ Invalid category JSON: {e}")
        sys.exit(1)

    # البيانات اللي محتاجينها من الـ category object
    vendor_category_id = category["id"]           # ID في vendor_categories
    platform_category_id = category["category_id"] # ID في categories (platform)
    external_id        = category["external_id"]   # Amazon browse node
    cat_name           = category.get("external_name", external_id)

    # vendor_id — محتاجينه للـ ingest API
    # مش جاي في الـ response الحالي، محتاج تضيفه
    vendor_id = category.get("vendor_id", 1)

    print(f"✅ Category: [{vendor_category_id}] {cat_name}")
    print(f"📌 external_id (node): {external_id}")
    print(f"📌 platform category_id: {platform_category_id}")

    seen_asins   = set()
    rank_counter = 0
    total_created = total_updated = total_skipped = 0
    max_pages    = None
    RETRY_DELAY  = 5

    driver = setup_driver()
    batch_buffer = []

    def flush_batch():
        nonlocal total_created, total_updated, total_skipped
        if not batch_buffer:
            return
        result = send_batch_to_laravel(batch_buffer, vendor_id, platform_category_id)
        total_created += result.get("created", 0)
        total_updated += result.get("updated", 0)
        total_skipped += result.get("skipped", 0)
        print(f"   📤 Batch sent ({len(batch_buffer)}) → created:{result.get('created',0)} updated:{result.get('updated',0)} skipped:{result.get('skipped',0)}")
        batch_buffer.clear()

    try:
        page = 1
        while True:
            if max_pages and page > max_pages:
                print("✅ Reached last page.")
                break

            url = get_page_url(category, page)
            print(f"\n📄 Page {page}/{max_pages or '?'} → {url}")

            max_retries = None if page == 1 else 3
            attempt     = 0
            page_success = False

            while True:
                attempt += 1
                if max_retries is not None and attempt > max_retries:
                    print(f"   ❌ Page {page} failed after {max_retries} attempts — skipping")
                    break

                try:
                    driver.get(url)
                    WebDriverWait(driver, 12).until(
                        EC.presence_of_element_located((By.CSS_SELECTOR, "div.s-main-slot"))
                    )

                    if page == 1 and max_pages is None:
                        max_pages = get_max_pages(driver)

                    cards = driver.find_elements(
                        By.CSS_SELECTOR, "div[data-component-type='s-search-result']"
                    )

                    if not cards:
                        print("⚠️ No cards found — stopping.")
                        max_pages = 0
                        page_success = True
                        break

                    page_saved = 0
                    for card in cards:
                        product = extract_card(card)
                        if not product:
                            continue
                        if product["external_id"] in seen_asins:
                            continue

                        rank_counter += 1
                        product["rank"] = rank_counter
                        seen_asins.add(product["external_id"])
                        batch_buffer.append(product)
                        page_saved += 1

                        # ابعت الـ batch لما يوصل للـ BATCH_SIZE
                        if len(batch_buffer) >= BATCH_SIZE:
                            flush_batch()

                    print(f"   💾 Page products: {page_saved} | Total scraped: {rank_counter}")
                    page_success = True
                    break

                except Exception as e:
                    label = f"attempt {attempt}" if max_retries is None else f"attempt {attempt}/{max_retries}"
                    print(f"   ⚠️ Failed ({label}): {e}")
                    driver = restart_driver(driver)
                    time.sleep(RETRY_DELAY)

            if max_pages == 0:
                break

            page += 1
            time.sleep(1.1)

        # ابعت اللي فضل في الـ buffer
        flush_batch()

    finally:
        try:
            driver.quit()
        except Exception:
            pass

    print(f"\n✅ DONE | Pages: {max_pages} | Total scraped: {rank_counter}")
    print(f"📊 Created: {total_created} | Updated: {total_updated} | Skipped: {total_skipped}")


if __name__ == "__main__":
    main()