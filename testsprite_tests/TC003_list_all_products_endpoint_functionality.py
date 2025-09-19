import requests
import time

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

def test_list_all_products_endpoint_functionality():
    url = f"{BASE_URL}/api/products"
    headers = {
        "Accept": "application/json"
    }
    start_time = time.time()
    try:
        response = requests.get(url, headers=headers, timeout=TIMEOUT)
    except requests.RequestException as e:
        assert False, f"Request to {url} failed with exception: {e}"
    elapsed_time_ms = (time.time() - start_time) * 1000

    # Performance: API response time less than 200ms (allowing some slack for local testing)
    assert elapsed_time_ms < 300, f"API response time too slow: {elapsed_time_ms:.2f}ms"

    # Status code 200 OK
    assert response.status_code == 200, f"Expected 200 OK, got {response.status_code}"

    # JSON Content-Type header present and correct
    content_type = response.headers.get("Content-Type", "")
    assert "application/json" in content_type.lower(), f"Expected JSON response, got Content-Type: {content_type}"

    try:
        products = response.json()
    except ValueError:
        assert False, "Response is not valid JSON"

    # Products should be a list (zero or more products)
    assert isinstance(products, list), f"Expected list of products, got {type(products)}"

    # Detailed validation for each product
    for product in products:
        # Each product must be a dict
        assert isinstance(product, dict), "Product entry is not a dictionary"

        # Required fields present and not null
        required_str_fields = ["id", "name", "description"]
        for field in required_str_fields:
            assert field in product, f"Product missing required field '{field}'"
            assert isinstance(product[field], (str, int)), f"Field '{field}' must be str or int"
            assert product[field] != "" and product[field] is not None, f"Field '{field}' is empty or None"

        # Categories: must be list of dicts with id and name
        assert "categories" in product, "Product missing 'categories' field"
        categories = product["categories"]
        assert isinstance(categories, list), "'categories' field must be a list"
        for category in categories:
            assert isinstance(category, dict), "Category is not a dictionary"
            assert "id" in category and category["id"] is not None, "Category missing 'id'"
            assert "name" in category and isinstance(category["name"], str) and category["name"] != "", "Category missing/invalid 'name'"

        # Brands: must be list of dicts or single dict, handle both
        assert "brands" in product, "Product missing 'brands' field"
        brands = product["brands"]
        assert isinstance(brands, (list, dict)), "'brands' field must be a list or dict"
        if isinstance(brands, dict):
            brands = [brands]
        for brand in brands:
            assert isinstance(brand, dict), "Brand is not a dictionary"
            assert "id" in brand and brand["id"] is not None, "Brand missing 'id'"
            assert "name" in brand and isinstance(brand["name"], str) and brand["name"] != "", "Brand missing/invalid 'name'"

        # Stores associations: must be list of dicts with id, name, and location (optional but recommended)
        assert "stores" in product, "Product missing 'stores' field"
        stores = product["stores"]
        assert isinstance(stores, list), "'stores' field must be a list"
        for store in stores:
            assert isinstance(store, dict), "Store is not a dictionary"
            assert "id" in store and store["id"] is not None, "Store missing 'id'"
            assert "name" in store and isinstance(store["name"], str) and store["name"] != "", "Store missing/invalid 'name'"

        # Optional fields validations for security and correctness
        # No unexpected keys like passwords, tokens, or secrets in product dict
        forbidden_keys = {"password", "token", "secret", "api_key"}
        assert not any(key in product for key in forbidden_keys), "Sensitive fields leaked in product data"

    # Rate limiting header check (if provided)
    # Expect headers like X-RateLimit-Limit and X-RateLimit-Remaining for security
    rate_limit = response.headers.get("X-RateLimit-Limit")
    rate_remaining = response.headers.get("X-RateLimit-Remaining")
    assert rate_limit is None or rate_limit.isdigit(), "X-RateLimit-Limit header invalid"
    assert rate_remaining is None or rate_remaining.isdigit(), "X-RateLimit-Remaining header invalid"

test_list_all_products_endpoint_functionality()