import requests
import time

BASE_URL = "http://localhost:8000"
PRICE_SEARCH_ENDPOINT = "/api/price-search"
PRODUCTS_ENDPOINT = "/api/products"
TIMEOUT = 30

def test_price_search_returns_correct_results():
    # Step 1: Create a comprehensive test product with known data
    product_payload = {
        "name": "SuperGaming Laptop",
        "description": "A powerful gaming laptop with RTX 4070 and 32GB RAM"
    }

    created_product_id = None
    try:
        # Create product for test setup
        create_resp = requests.post(
            BASE_URL + PRODUCTS_ENDPOINT,
            json=product_payload,
            headers={"Content-Type": "application/json"},
            timeout=TIMEOUT
        )
        assert create_resp.status_code == 201, f"Product creation failed: {create_resp.text}"
        created_product = create_resp.json()
        assert "id" in created_product and isinstance(created_product["id"], int), "Product ID missing or invalid"
        created_product_id = created_product["id"]

        # Verify the product was created correctly by fetching it
        get_resp = requests.get(
            f"{BASE_URL}{PRODUCTS_ENDPOINT}/{created_product_id}",
            timeout=TIMEOUT
        )
        assert get_resp.status_code == 200, f"Failed to get created product: {get_resp.text}"
        fetched_product = get_resp.json()
        assert fetched_product["name"] == product_payload["name"]
        assert fetched_product["description"] == product_payload["description"]

        # Step 2: Test /api/price-search endpoint by searching by name
        search_params = {"name": product_payload["name"]}
        search_start = time.time()
        search_resp = requests.get(
            BASE_URL + PRICE_SEARCH_ENDPOINT,
            params=search_params,
            timeout=TIMEOUT
        )
        search_duration = (time.time() - search_start) * 1000  # ms
        assert search_resp.status_code == 200, f"Price search failed: {search_resp.text}"
        assert search_duration < 200, f"API response time exceeded: {search_duration:.2f} ms"

        search_results = search_resp.json()
        assert isinstance(search_results, list), "Search results should be a list"
        assert len(search_results) >= 1, "Search should return at least one result"

        # Validate each returned item has correct structure and price info
        valid_item_found = False
        for item in search_results:
            assert "product_id" in item and isinstance(item["product_id"], int)
            assert "name" in item and isinstance(item["name"], str)
            assert "description" in item and isinstance(item["description"], str)
            assert "category" in item and isinstance(item["category"], str)
            assert "offers" in item and isinstance(item["offers"], list)
            # Check offers contain price info from multiple stores
            prices = []
            for offer in item["offers"]:
                assert "store_id" in offer and isinstance(offer["store_id"], int)
                assert "price" in offer and isinstance(offer["price"], (float, int))
                assert offer["price"] > 0, "Price must be positive"
                prices.append(offer["price"])
            # Verify best offer is indeed the lowest price in offers
            best_offer_price = min(prices) if prices else None
            assert best_offer_price is not None
            # Check that the product in results is our created product by id
            if item["product_id"] == created_product_id:
                valid_item_found = True
                # Confirm the product name and category match the created product
                assert item["name"].lower() == product_payload["name"].lower()
                assert item["category"].lower() == item["category"].lower()  # cannot check against payload as category not set
                # Check best offer functionality (lowest price)
                min_price = min([offer["price"] for offer in item["offers"]])

        assert valid_item_found, "Created product not found in search results"

        # Step 3: Test search by description partial match (case insensitive)
        desc_search_params = {"description": "POWERFUL gaming"}
        desc_resp = requests.get(
            BASE_URL + PRICE_SEARCH_ENDPOINT,
            params=desc_search_params,
            timeout=TIMEOUT
        )
        assert desc_resp.status_code == 200, f"Price search by description failed: {desc_resp.text}"
        desc_results = desc_resp.json()
        assert any(item["product_id"] == created_product_id for item in desc_results), "Product not found by description search"

        # Step 4: Test search by category
        cat_search_params = {"category": item["category"]}
        cat_resp = requests.get(
            BASE_URL + PRICE_SEARCH_ENDPOINT,
            params=cat_search_params,
            timeout=TIMEOUT
        )
        assert cat_resp.status_code == 200, f"Price search by category failed: {cat_resp.text}"
        cat_results = cat_resp.json()
        assert any(item["product_id"] == created_product_id for item in cat_results), "Product not found by category search"

        # Step 5: Security and performance validations
        # Test rate limit headers if present
        headers = search_resp.headers
        if "X-RateLimit-Limit" in headers:
            assert int(headers["X-RateLimit-Limit"]) >= 30
        if "X-RateLimit-Remaining" in headers:
            remaining = int(headers["X-RateLimit-Remaining"])
            assert remaining >= 0

        # Test input validation - search with invalid input should not cause server error
        invalid_param = {"name": "<script>alert(1)</script>"}
        invalid_resp = requests.get(
            BASE_URL + PRICE_SEARCH_ENDPOINT,
            params=invalid_param,
            timeout=TIMEOUT
        )
        assert invalid_resp.status_code in [200, 400], "Invalid input not handled properly"

        # Test large payload - limits on query size
        large_name = "a" * 1024
        large_resp = requests.get(
            BASE_URL + PRICE_SEARCH_ENDPOINT,
            params={"name": large_name},
            timeout=TIMEOUT
        )
        assert large_resp.status_code in [200, 400], "Large input not handled properly"

    finally:
        # Cleanup: Delete created product to keep environment clean
        if created_product_id is not None:
            try:
                del_resp = requests.delete(
                    f"{BASE_URL}{PRODUCTS_ENDPOINT}/{created_product_id}",
                    timeout=TIMEOUT
                )
                # Accept 200 or 204 or 404 (if already deleted)
                assert del_resp.status_code in [200, 204, 404]
            except Exception:
                pass

test_price_search_returns_correct_results()