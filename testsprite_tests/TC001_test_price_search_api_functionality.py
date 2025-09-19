import requests
import time

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

def test_price_search_api_functionality():
    search_endpoint = f"{BASE_URL}/api/price-search"
    headers = {
        "Accept": "application/json"
    }
    
    # Test queries for name, description, and category search
    search_queries = [
        {"query": "laptop"},             # Name search
        {"query": "gaming laptop"},      # Description search
        {"query": "electronics"}         # Category search
    ]

    for params in search_queries:
        try:
            response = requests.get(search_endpoint, headers=headers, params=params, timeout=TIMEOUT)
        except requests.RequestException as e:
            assert False, f"Request failed: {e}"

        # Assert status code 200 OK
        assert response.status_code == 200, f"Unexpected status code: {response.status_code} for params {params}"

        data = None
        try:
            data = response.json()
        except Exception:
            assert False, "Response is not valid JSON"

        # Validate that response is a dict and contains a list of results
        assert isinstance(data, dict), "Response JSON root is not an object"
        # The list of results assumed to be under key 'data' or 'results'
        results = data.get('data') or data.get('results')
        assert isinstance(results, list), "Response does not contain a list of results under 'data' or 'results'"

        # If results returned, check structure of first item
        if len(results) > 0:
            item = results[0]
            # Expected fields: product name, description, category, prices from stores
            expected_keys = {"product_id", "product_name", "description", "category", "prices"}
            assert expected_keys.issubset(item.keys()), f"Result item missing keys: {expected_keys - set(item.keys())}"

            # Prices should be a list of store-price dicts
            prices = item["prices"]
            assert isinstance(prices, list), "'prices' should be a list"
            if prices:
                price_item = prices[0]
                assert "store_id" in price_item and "price" in price_item, "Price item missing 'store_id' or 'price'"

        # Check headers for caching (e.g. Cache-Control header should exist)
        cache_header = response.headers.get("Cache-Control", "")
        assert "max-age" in cache_header or "no-cache" in cache_header, "Caching headers missing or incorrect"

    # To test rate limiting, quickly issue more than 30 requests and expect 429 status code
    for i in range(31):
        try:
            r = requests.get(search_endpoint, headers=headers, params={"query": "test"}, timeout=TIMEOUT)
        except requests.RequestException as e:
            assert False, f"Rate limit test request failed: {e}"

        if i < 30:
            # Before limit is hit, expect 200 or 429 if already hit due to other tests
            assert r.status_code in (200, 429), f"Unexpected status code before hitting limit: {r.status_code}"
        else:
            # Expect 429 Too Many Requests after exceeding 30 requests
            if r.status_code != 429:
                # Wait 1 second and retry to accommodate rate reset window
                time.sleep(1)
                retry_response = requests.get(search_endpoint, headers=headers, params={"query": "test"}, timeout=TIMEOUT)
                assert retry_response.status_code == 429, "Rate limit not enforced after exceeding 30 requests"

test_price_search_api_functionality()
