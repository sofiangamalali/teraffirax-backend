import os

LARAVEL_BASE_URL    = os.getenv("LARAVEL_BASE_URL", "https://traffirax.com")
SCRAPER_TOKEN       = os.getenv("SCRAPER_SERVICE_TOKEN", "sk_scraper_mK9pX2qL8nR4vT6wY3zA5bC7dE1fG0hJ_a3f2c1d4e5b6f7a8b9c0d1e2f3a4b5c6d7e8f9a0b1c2d3e4f5a6b7c8d9e0f1a2b3c4d5e6f7a8b9")
VENDOR_SLUG         = "amazon"
BATCH_SIZE          = 48   
REQUEST_TIMEOUT     = 15  