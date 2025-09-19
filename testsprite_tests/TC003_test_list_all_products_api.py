import requests
import time

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

def test_list_all_products_api():
    url = f"{BASE_URL}/api/products"
    headers = {
        "Accept": "application/json"
    }

    # Track request times to verify rate limiting
    request_times = []

    # Make a valid GET request to list products
    response = requests.get(url, headers=headers, timeout=TIMEOUT)
    request_times.append(time.time())
    assert response.status_code == 200, f"Expected 200 OK but got {response.status_code}"
    data = response.json()
    assert isinstance(data, dict), "Response JSON should be an object"
    # Validate presence of 'data' key
    assert "data" in data, "Response should contain 'data' key"
    assert isinstance(data["data"], list), "'data' key should be a list"

    # Optionally check pagination metadata if present
    pagination_keys = ["current_page", "last_page", "per_page", "total"]
    for key in pagination_keys:
        if key in data:
            # Basic type checks if key exists
            assert isinstance(data[key], int), f"Pagination key '{key}' should be an integer if present"

    # Check that rate limiting is enforced by making rapid requests to trigger limit
    # The rate limit is 30/minute = 1 request every 2 seconds approximately
    # We try to send 35 requests quickly and expect some to be rate limited

    rate_limit_exceeded = False
    for i in range(35):
        try:
            resp = requests.get(url, headers=headers, timeout=TIMEOUT)
            request_times.append(time.time())
            if resp.status_code == 429:
                rate_limit_exceeded = True
                break
            else:
                assert resp.status_code == 200, f"Expected 200 or 429 but got {resp.status_code}"
            # Small delay to not flood too fast
            time.sleep(0.1)
        except requests.RequestException as e:
            assert False, f"Request failed with exception: {e}"

    assert rate_limit_exceeded, "Rate limiting did not trigger after many rapid requests"

test_list_all_products_api()
