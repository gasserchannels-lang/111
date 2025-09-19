import requests
import time

BASE_URL = "http://localhost:8000"
TIMEOUT = 30
HEADERS = {
    "Accept": "application/json"
}

def test_get_product_by_id_api():
    # Step 1: Create a new product to get a valid product ID for testing
    product_data = {
        "name": "Test Product for GetById",
        "description": "A product created to test GET /api/products/{id}",
        "price": 99.99,
        "categories": [1],
        "brands": [1],
        "stores": [1],
        "images": []
    }

    created_product_id = None

    try:
        create_response = requests.post(
            f"{BASE_URL}/api/products",
            json=product_data,
            headers=HEADERS,
            timeout=TIMEOUT
        )
        assert create_response.status_code == 201, f"Failed to create product, status code: {create_response.status_code}"
        created_product = create_response.json()
        assert "id" in created_product, "Created product response missing 'id'"
        created_product_id = created_product["id"]

        # Step 2: GET the product by ID and validate the details
        get_response = requests.get(
            f"{BASE_URL}/api/products/{created_product_id}",
            headers=HEADERS,
            timeout=TIMEOUT
        )
        assert get_response.status_code == 200, f"GET product by ID failed, status code: {get_response.status_code}"
        product = get_response.json()
        
        # Validate returned product fields
        assert product["id"] == created_product_id, "Returned product ID does not match requested ID"
        assert product["name"] == product_data["name"], "Returned product name mismatch"
        assert product["description"] == product_data["description"], "Returned product description mismatch"
        assert float(product["price"]) == product_data["price"], "Returned product price mismatch"
        assert isinstance(product.get("categories"), list), "Product categories is not a list"
        assert isinstance(product.get("brands"), list), "Product brands is not a list"
        assert isinstance(product.get("stores"), list), "Product stores is not a list"

        # Step 3: Verify rate limiting enforcement by rapid repeat calls
        # Assuming rate limit is 30/min, call 35 times to exceed the limit and expect some 429 responses eventually
        rate_limit_exceeded = False
        last_status_code = None
        for _ in range(35):
            rl_response = requests.get(
                f"{BASE_URL}/api/products/{created_product_id}",
                headers=HEADERS,
                timeout=TIMEOUT
            )
            last_status_code = rl_response.status_code
            if last_status_code == 429:
                rate_limit_exceeded = True
                break
            time.sleep(1)  # Spread calls one per second to approach limit

        assert rate_limit_exceeded, f"Rate limiting not enforced, last status code: {last_status_code}"

    finally:
        # Cleanup: Delete the created product
        if created_product_id is not None:
            # Delete likely requires authentication, but according to PRD authentication required for DELETE
            # Since no auth in instructions, we attempt delete without auth for cleanup, ignore failure
            try:
                requests.delete(
                    f"{BASE_URL}/api/products/{created_product_id}",
                    headers=HEADERS,
                    timeout=TIMEOUT
                )
            except Exception:
                pass

test_get_product_by_id_api()