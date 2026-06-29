import os
import sys
import json
import subprocess
from datetime import datetime

import requests

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from config import LARAVEL_BASE_URL, SCRAPER_TOKEN, VENDOR_SLUG

BASE_DIR       = os.path.dirname(os.path.abspath(__file__))
SCRAPER_SCRIPT = os.path.join(BASE_DIR, "scrap_amazon_category.py")
PYTHON_BIN     = sys.executable


def get_headers():
    return {
        "Authorization": f"Bearer {SCRAPER_TOKEN}",
        "Accept":        "application/json",
        "Content-Type":  "application/json",
    }


def check_laravel_health():
    try:
        resp = requests.get(
            f"{LARAVEL_BASE_URL}/api/scraper/health",
            headers=get_headers(),
            timeout=5
        )
        return resp.status_code == 200
    except Exception as e:
        print(f"❌ Laravel health check failed: {e}")
        return False


def load_vendor_categories():
    resp = requests.get(
        f"{LARAVEL_BASE_URL}/api/scraper/vendors/{VENDOR_SLUG}/categories",
        headers=get_headers(),
        timeout=10
    )
    resp.raise_for_status()
    return resp.json().get("data", [])


def main():
    print("🚀 START SCRAPING RUN:", datetime.now())

    # health check أول حاجة
    if not check_laravel_health():
        print("❌ Laravel is not reachable. Aborting.")
        sys.exit(1)

    if not os.path.exists(SCRAPER_SCRIPT):
        print(f"❌ Scraper script not found: {SCRAPER_SCRIPT}")
        sys.exit(2)

    # جيب الـ categories من Laravel مش من الـ DB
    try:
        categories = load_vendor_categories()
    except Exception as e:
        print(f"❌ Failed to load categories from Laravel: {e}")
        sys.exit(1)

    if not categories:
        print("❌ No active categories returned from Laravel.")
        sys.exit(1)

    print(f"📋 Found {len(categories)} active vendor categories\n")

    success_count = 0
    fail_count    = 0

    for cat in categories:
        cat_id   = cat["vendor_category_id"]
        cat_name = f"category_{cat_id}"

        print(f"{'─' * 60}")
        print(f"📂 [{cat_id}] {cat_name} | node: {cat['external_id']}")

        # بعت الـ category كـ JSON للـ scraper بدل ما نبعت ID وهو يتصل بالـ DB
        result = subprocess.run(
            [PYTHON_BIN, SCRAPER_SCRIPT, json.dumps(cat)],
            capture_output=True,
            text=True
        )

        if result.returncode != 0:
            fail_count += 1
            print(f"❌ FAILED — [{cat_id}] {cat_name} | exit={result.returncode}")
            if result.stdout:
                print("  STDOUT:", result.stdout[-2000:])
            if result.stderr:
                print("  STDERR:", result.stderr[-2000:])
        else:
            success_count += 1
            lines   = [l for l in result.stdout.strip().splitlines() if l.strip()]
            summary = "\n  ".join(lines[-3:]) if lines else "(no output)"
            print(f"✅ Done → {summary}")

    print(f"\n{'═' * 60}")
    print(f"✅ FINISHED — Success: {success_count} | Failed: {fail_count}")
    print(f"🕐 {datetime.now()}")


if __name__ == "__main__":
    main()