import mysql.connector
import asyncio
from telegram import Bot
import requests
import logging
from datetime import datetime
import html
import os
import subprocess
import sys
import json
import time

# =====================
# LOGGING
# =====================
RUN_TIME = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s | %(levelname)s | %(message)s"
)

logging.info("=" * 60)
logging.info("🚀 Script started via CRON")
logging.info(f"🕒 Run time: {RUN_TIME}")
logging.info("=" * 60)

# =====================
# CONFIG
# =====================
DB_CONFIG = {
    "host": "127.0.0.1",
    "user": "deals_db",
    "password": "tybMaJLPRXmBfJn4",
    "database": "deals_db",
}

BOT_TOKEN = "8477015476:AAGKvIdxdmbJ7LVk4f5JHyjUl1BJ40KOo-I"
CHAT_ID = -1003314829979
CHANNEL_LINK = "https://t.me/dealsegypt"

SCRAPER_SCRIPT = os.path.join(os.path.dirname(os.path.abspath(__file__)), "scrap_one_product.py")

bot = Bot(token=BOT_TOKEN)

logging.info("✅ Config loaded")

# =====================
# DB HELPERS
# =====================
def get_db():
    logging.info("🔌 Connecting to database")
    return mysql.connector.connect(**DB_CONFIG)


def get_active_categories_ordered():
    logging.info("📚 Fetching active categories ordered by last_posted_at")

    conn = get_db()
    cur = conn.cursor(dictionary=True)

    cur.execute("""
        SELECT id
        FROM amazon_categories
        WHERE is_active = 1
          AND is_private_channel = 0
        ORDER BY
            CASE WHEN last_posted_at IS NULL THEN 0 ELSE 1 END ASC,
            last_posted_at ASC,
            id ASC
    """)

    rows = cur.fetchall() or []
    cur.close()
    conn.close()

    cat_ids = [r["id"] for r in rows]
    logging.info(f"✅ Active categories count: {len(cat_ids)}")
    return cat_ids


def touch_category_posted(cat_id: int):
    logging.info(f"⏱️ Touching category timestamp | category_id: {cat_id}")
    conn = get_db()
    cur = conn.cursor()
    cur.execute("UPDATE amazon_categories SET last_posted_at = NOW() WHERE id = %s", (cat_id,))
    conn.commit()
    cur.close()
    conn.close()


def get_next_product():
    logging.info("📦 Fetching next product (dynamic categories + smart score ordering)")

    categories = get_active_categories_ordered()
    if not categories:
        logging.warning("⚠️ No active categories found")
        return None

    for _ in range(len(categories)):
        categories = get_active_categories_ordered()
        if not categories:
            return None

        cat_id = categories[0]
        logging.info(f"🔎 Trying category: {cat_id}")

        conn = get_db()
        cur = conn.cursor(dictionary=True)

        cur.execute("""
                    SELECT p.*,
               (
                   ( (p.price_after * c.commission_percent / 100) /
                     NULLIF((SELECT MAX(p2.price_after * c2.commission_percent / 100)
                             FROM products p2
                             JOIN amazon_categories c2 ON c2.id = p2.category_id
                             WHERE p2.category_id = p.category_id
                               AND p2.posted = 0 AND p2.post_failed = 0
                               AND p2.batch = p.batch), 0)
                   ) * 0.33
        
                   +
        
                   ( p.discount /
                     NULLIF((SELECT MAX(p2.discount)
                             FROM products p2
                             WHERE p2.category_id = p.category_id
                               AND p2.posted = 0 AND p2.post_failed = 0
                               AND p2.batch = p.batch), 0)
                   ) * 0.33
        
                   +
        
                   ( 1 - (p.rank_in_category - 1) /
                     NULLIF((SELECT MAX(p2.rank_in_category)
                             FROM products p2
                             WHERE p2.category_id = p.category_id
                               AND p2.posted = 0 AND p2.post_failed = 0
                               AND p2.batch = p.batch), 0)
                   ) * 0.34
        
               ) AS score
        
        FROM products p
        JOIN amazon_categories c ON c.id = p.category_id
        
        WHERE p.posted = 0
          AND p.post_failed = 0
          AND p.is_blocked  = 0
          AND p.category_id = %s
          AND p.batch = (
              SELECT MAX(p3.batch)
              FROM products p3
              WHERE p3.category_id = %s
                AND p3.posted = 0
                AND p3.post_failed = 0
          )
        
        ORDER BY
            COALESCE(p.post_counter, 0) ASC,   -- الأولوية الأولى: الأقل نشراً
            score DESC                          -- الأولوية الثانية: الأعلى سكور
        
        LIMIT 1;
        """, (cat_id, cat_id))

        product = cur.fetchone()
        cur.close()
        conn.close()

        if product:
            logging.info(f"✅ Product found | ID: {product['id']} | category: {cat_id} | score: {product.get('score')}")
            product["_picked_category_id"] = cat_id
            return product

        logging.warning(f"⚠️ No products in category {cat_id} => touching & skipping")
        touch_category_posted(int(cat_id))

    logging.warning("⚠️ No products to post across all active categories")
    return None


def save_product_url(product_id, url):
    logging.info(f"💾 Saving URL | Product ID: {product_id}")
    conn = get_db()
    cur = conn.cursor()
    cur.execute("UPDATE products SET url = %s WHERE id = %s", (url, product_id))
    conn.commit()
    cur.close()
    conn.close()
    logging.info("✅ URL saved")


def mark_as_posted(product_id):
    logging.info(f"📌 Marking product as posted | ID: {product_id}")
    conn = get_db()
    cur = conn.cursor()
    cur.execute("""
        UPDATE products
        SET posted = 1,
            post_failed = 0,
            post_counter = COALESCE(post_counter, 0) + 1
        WHERE id = %s
    """, (product_id,))
    conn.commit()
    cur.close()
    conn.close()
    logging.info("✅ Product marked as posted (+counter)")


def mark_as_failed(product_id):
    logging.error(f"❌ Marking product as FAILED | ID: {product_id}")
    conn = get_db()
    cur = conn.cursor()
    cur.execute("""
        UPDATE products
        SET post_failed = 1,
            post_counter = COALESCE(post_counter, 0) + 1
        WHERE id = %s
    """, (product_id,))
    conn.commit()
    cur.close()
    conn.close()
    logging.info("⚠️ Product marked as failed (+counter)")


def update_product_price_and_discount(product_id, price_after, price_before, discount):
    """تحديث السعر والخصم في الداتا بيز لما الخصم يتغير"""
    conn = get_db()
    cur = conn.cursor()
    cur.execute("""
        UPDATE products
        SET price_after  = %s,
            price_before = %s,
            discount     = %s,
            last_updated = NOW()
        WHERE id = %s
    """, (price_after, price_before, discount, product_id))
    conn.commit()
    cur.close()
    conn.close()
    logging.info(f"🔄 Updated price/discount for: {product_id} | new_discount={discount}%")


def get_active_amazon_account():
    logging.info("🔑 Fetching active Amazon affiliate account")
    conn = get_db()
    cur = conn.cursor(dictionary=True)
    cur.execute("""
        SELECT *
        FROM amazon_credentials
        WHERE is_active = 1
        ORDER BY id ASC
        LIMIT 1
    """)
    account = cur.fetchone()
    cur.close()
    conn.close()
    if account:
        logging.info(f"✅ Active tag found: {account['tag']}")
    else:
        logging.error("❌ No active Amazon account")
    return account


# =====================
# SITESTRIPE LINK
# =====================
def get_sitestripe_shortlink(long_url, tag, cookie):
    logging.info("🔗 Generating SiteStripe short link")

    headers = {
        "Cookie": cookie,
        "User-Agent": "Mozilla/5.0",
        "X-Requested-With": "XMLHttpRequest",
        "Accept": "application/json",
        "Referer": "https://www.amazon.eg/"
    }

    params = {
        "longUrl": f"{long_url}?tag={tag}",
        "marketplaceId": "623225021"
    }

    resp = requests.get(
        "https://www.amazon.eg/associates/sitestripe/getShortUrl",
        headers=headers,
        params=params,
        timeout=20
    )

    logging.info(f"📡 Status Code: {resp.status_code}")

    if resp.status_code != 200:
        raise Exception("SiteStripe HTTP error")

    data = resp.json()

    if data.get("isOk"):
        logging.info("✅ SiteStripe link generated")
        return data.get("shortUrl")

    raise Exception("SiteStripe failed")


def build_fallback_link(asin, tag):
    logging.info("🔁 Using fallback affiliate link")
    ascsubtag = "telegram"
    return f"https://www.amazon.eg/dp/{asin}/?tag={tag}&ascsubtag={ascsubtag}"


# =====================
# SAFE HTML ESCAPE
# =====================
def safe(value):
    if value is None:
        return ""
    return html.escape(str(value))


# =====================
# CAPTION TEMPLATE
# =====================
def build_caption(p, link):
    logging.info("📝 Building caption")
    name         = safe(p.get("name"))
    price_after  = safe(p.get("price_after"))
    price_before = safe(p.get("price_before"))
    link         = safe(link)
    try:
        discount_value = round(abs(float(p.get("discount") or 0)))
    except Exception:
        discount_value = 0

    try:
        price_after_clean = int(float(price_after))
    except Exception:
        price_after_clean = price_after

    if discount_value >= 20:
        caption = f"""خصم <b>{discount_value}%</b> على {name} بسعر <b>{price_after_clean} ج</b>
        
    لينك العرض: {link}"""
    else:
        caption = f"""{name} بسعر <b>{price_after_clean} ج</b>
        
    لينك العرض: {link}"""

    logging.info("✅ Caption ready")
    return caption.strip()


# =====================
# SCRAPER
# =====================
def run_scraper(asin: str) -> dict:
    logging.info(f"🔍 Running scraper for ASIN={asin}")
    try:
        result = subprocess.run(
            [sys.executable, SCRAPER_SCRIPT, asin],
            timeout=90,
            stdout=subprocess.PIPE,
            stderr=None,  # stderr يطلع مباشرة في الـ terminal
            text=True
        )

        output = (result.stdout or "").strip()
        if not output:
            logging.error("❌ Scraper returned empty output")
            return {"success": False, "error": "empty output"}

        for line in reversed(output.splitlines()):
            line = line.strip()
            if line.startswith("{"):
                return json.loads(line)

        logging.error(f"❌ No JSON found in scraper output: {output[:200]}")
        return {"success": False, "error": "no JSON in output"}

    except subprocess.TimeoutExpired:
        logging.error("⏱️ Scraper timed out")
        return {"success": False, "error": "timeout"}
    except Exception as e:
        logging.error(f"❌ Scraper exception: {e}")
        return {"success": False, "error": str(e)}


def delete_screenshot(path: str):
    try:
        if path and os.path.exists(path):
            os.remove(path)
            logging.info(f"🧹 Screenshot deleted: {path}")
    except Exception as e:
        logging.warning(f"⚠️ Could not delete screenshot: {e}")


# =====================
# MAIN
# =====================
async def main():
    logging.info("▶️ Main process started")

    account = get_active_amazon_account()
    if not account:
        logging.error("⛔ Stopping – no active Amazon account")
        return

    # =====================
    # LOOP: جرب منتج بعد منتج لحد ما تنشر واحد أو ما يبقاش فيه منتجات
    # =====================
    while True:

        product = get_next_product()
        if not product:
            logging.warning("⛔ Stopping – no product")
            return

        asin      = product["id"]
        shot_path = None

        try:
            # 1) سكرب المنتج + screenshot
            scraped = run_scraper(asin)

            # ✅ عين shot_path فوراً عشان الـ finally يمسحها في أي حالة
            shot_path = scraped.get("screenshot_path")

            # ❌ لو السكرابر فشل كلياً → علّم المنتج failed وجرب اللي بعده
            if not scraped.get("success"):
                logging.error(f"❌ Scraper failed: {scraped.get('error')}")
                mark_as_failed(asin)
                continue  # ✅ جرب المنتج اللي بعده

            # ❌ لو المنتج مش متاح → علّم المنتج failed وجرب اللي بعده
            if not scraped.get("in_stock"):
                logging.warning(f"⚠️ Product not available: {asin} → marking as failed")
                mark_as_failed(asin)
                continue  # ✅ جرب المنتج اللي بعده

            # 2) تحقق من الخصم
            scraped_discount = float(scraped.get("discount") or 0)
            db_discount      = float(product.get("discount") or 0)

            logging.info(f"📊 Discount check — DB: {db_discount}% | Live: {scraped_discount}%")

            if scraped_discount < db_discount:
                # الخصم اتقلل → حدّث الداتا بيز وجرب المنتج اللي بعده
                logging.warning(
                    f"📉 Discount dropped {db_discount}% → {scraped_discount}% "
                    f"for {asin} | updating DB and trying next product"
                )
                update_product_price_and_discount(
                    asin,
                    scraped.get("price_after"),
                    scraped.get("price_before"),
                    scraped_discount
                )
                delete_screenshot(shot_path)
                shot_path = None
                continue  # ✅ جرب المنتج اللي بعده

            # 3) الخصم تمام — حدّث السعر في الداتا بيز
            update_product_price_and_discount(
                asin,
                scraped.get("price_after"),
                scraped.get("price_before"),
                scraped_discount
            )

            # 4) رابط الأفلييت
            amazon_url = f"https://www.amazon.eg/dp/{asin}"
            short_link = product.get("url")

            if short_link and str(short_link).strip():
                logging.info("🔁 Using existing affiliate link")
            else:
                logging.info("🆕 No affiliate link found – trying SiteStripe")
                try:
                    short_link = get_sitestripe_shortlink(amazon_url, account["tag"], account["cookie"])
                except Exception as e:
                    logging.warning(f"⚠️ SiteStripe failed: {e} → using fallback")
                    short_link = build_fallback_link(asin, account["tag"])
                save_product_url(asin, short_link)

            # 5) build caption بالبيانات الجديدة من السكرابر
            product_for_caption = {
                "name":         scraped.get("title") or product.get("name"),
                "price_after":  scraped.get("price_after") or product.get("price_after"),
                "price_before": scraped.get("price_before") or product.get("price_before"),
                "discount":     scraped_discount,
            }
            caption = build_caption(product_for_caption, short_link)

            # 6) إرسال لتيليجرام
            if shot_path and os.path.exists(shot_path):
                logging.info(f"📸 Sending with screenshot: {shot_path}")
                with open(shot_path, "rb") as f:
                    await bot.send_photo(chat_id=CHAT_ID, photo=f, caption=caption, parse_mode="HTML")
            else:
                db_image = scraped.get("image") or product.get("image")
                if not db_image:
                    raise Exception("No screenshot and no image available")
                logging.warning("⚠️ No screenshot – using image URL")
                await bot.send_photo(chat_id=CHAT_ID, photo=db_image, caption=caption, parse_mode="HTML")

            logging.info("✅ Telegram post sent")

            # 7) حدّث الداتا بيز
            mark_as_posted(asin)

            picked_cat = product.get("_picked_category_id") or product.get("category_id")
            if picked_cat:
                touch_category_posted(int(picked_cat))
                logging.info(f"⏱️ Category touched => {picked_cat}")

            logging.info("🎉 Script finished successfully")
            logging.info("=" * 60)
            return  # ✅ نجح النشر — اخرج

        except Exception as e:
            logging.exception(f"🔥 Unhandled error for product {asin}: {e}")
            mark_as_failed(asin)
            continue  # ✅ جرب المنتج اللي بعده

        finally:
            if shot_path:
                delete_screenshot(shot_path)


# =====================
# RUN
# =====================
if __name__ == "__main__":
    asyncio.run(main())