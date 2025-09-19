import requests
import time

BASE_URL = "http://localhost:8000"
BEST_OFFER_ENDPOINT = "/api/price-search/best-offer"
PRODUCTS_ENDPOINT = "/api/products"
TIMEOUT = 30

def test_best_offer_endpoint_returns_lowest_price():
    # Step 1: Create multiple products with different prices to test best offer
    # Since schema details for product creation are not fully specified, use minimal required fields with unique names/prices
    
    products_data = [
        {
            "name": "Test Product A " + str(int(time.time()*1000)),
            "description": "Test product A description",
            "price": 10.99,
            "category": "TestCategory",
            "brand": "TestBrand",
            "stores": ["Store1"]
        },
        {
            "name": "Test Product B " + str(int(time.time()*1000)),
            "description": "Test product B description",
            "price": 5.49,
            "category": "TestCategory",
            "brand": "TestBrand",
            "stores": ["Store2"]
        },
        {
            "name": "Test Product C " + str(int(time.time()*1000)),
            "description": "Test product C description",
            "price": 7.25,
            "category": "TestCategory",
            "brand": "TestBrand",
            "stores": ["Store3"]
        },
    ]
    
    created_product_ids = []
    try:
        headers = {
            "Content-Type": "application/json"
        }
        # Create products
        for pd in products_data:
            # Include price in payload since it's needed to determine best offer,
            # The PRD doesn't specify exact product schema but price must be specified in some way or via store prices
            # Assuming price included in product creation, else extended logic needed
            response = requests.post(
                BASE_URL + PRODUCTS_ENDPOINT,
                json=pd,
                headers=headers,
                timeout=TIMEOUT
            )
            assert response.status_code == 201 or response.status_code == 200, f"Product creation failed: {response.status_code}, {response.text}"
            product = response.json()
            assert "id" in product, f"No product ID returned: {product}"
            created_product_ids.append(product["id"])

        # Step 2: Query the best offer endpoint for the product with lowest price
        # Since the endpoint is /api/price-search/best-offer and requires a product, we need to provide product identification
        # Let's query for the product B which has the lowest price by name parameter (assuming API accepts query param 'name')
        params = {
            "name": products_data[1]["name"]  # Test Product B name
        }

        start = time.time()
        r = requests.get(
            BASE_URL + BEST_OFFER_ENDPOINT,
            params=params,
            headers={"Accept": "application/json"},
            timeout=TIMEOUT
        )
        elapsed = (time.time() - start) * 1000  # ms

        assert r.status_code == 200, f"Best offer GET failed: {r.status_code}, {r.text}"
        assert elapsed < 200, f"API response time too slow: {elapsed}ms (limit <200ms)"
        
        best_offer = r.json()
        # Validate response contains expected keys and lowest price
        # Expecting something like: { product_id, price, store, ... }
        assert isinstance(best_offer, dict), f"Invalid best offer response type: {type(best_offer)}"
        # Check price field presence and type
        assert "price" in best_offer, "Best offer response missing 'price'"
        assert isinstance(best_offer["price"], (int, float)), "Price is not numeric"

        # The price should be equal to or less than any created products with this name
        # Because the searched name matches Product B exactly, best offer price should be 5.49
        # Allow small float tolerance
        expected_price = products_data[1]["price"]
        actual_price = float(best_offer["price"])
        assert abs(actual_price - expected_price) < 0.01, f"Best offer price {actual_price} does not match expected lowest {expected_price}"

        # Check security headers if any (e.g. no sensitive data leakage)
        security_headers = ["X-Content-Type-Options", "X-Frame-Options", "Strict-Transport-Security"]
        for sh in security_headers:
            # Just warn if missing (not mandatory in PRD but good security practice)
            if sh not in r.headers:
                print(f"Warning: Missing security header {sh}")

    finally:
        # Cleanup created products
        for pid in created_product_ids:
            try:
                # No auth required for deletion in PRD? DELETE requires authentication, so skip deleting to avoid unauthorized error.
                # If auth token were available, implement deletion here.
                pass
            except Exception:
                pass


test_best_offer_endpoint_returns_lowest_price()