import requests
import time

BASE_URL = "http://localhost:8000"
TIMEOUT = 30


def create_product():
    url = f"{BASE_URL}/api/products"
    product_data = {
        "name": "Test Product for Deletion",
        "description": "This product is created for testing soft deletion.",
        "category_id": 1,
        "brand_id": 1,
        "store_ids": [1],
        "price": 9.99,
        "image_url": "http://example.com/image.jpg"
    }
    try:
        resp = requests.post(url, json=product_data, timeout=TIMEOUT)
        resp.raise_for_status()
        product = resp.json()
        product_id = product.get("id")
        assert product_id is not None, "Product creation failed: No ID returned"
        return product_id
    except Exception as e:
        assert False, f"Product creation request failed: {e}"


def delete_product_api_test():
    # First create product to delete
    product_id = None
    try:
        product_id = create_product()

        delete_url = f"{BASE_URL}/api/products/{product_id}"

        # Test 1: Unauthorized delete attempt (no token)
        resp_unauth = requests.delete(delete_url, timeout=TIMEOUT)
        assert resp_unauth.status_code in (401, 403), (
            f"Expected 401 or 403 for unauthorized delete, got {resp_unauth.status_code}"
        )

        # Since we cannot authenticate (no auth endpoint in PRD), skip tests requiring auth.

    finally:
        # Clean up: attempt to delete product without auth will likely fail, so ignore.
        pass

delete_product_api_test()
