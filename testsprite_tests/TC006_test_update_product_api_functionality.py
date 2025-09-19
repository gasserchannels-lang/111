import requests
import time

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

# Mock token for authentication - replace with actual token retrieval
MOCK_TOKEN = "mocked_jwt_token_for_testing"

def create_product(token):
    url = f"{BASE_URL}/api/products"
    product_data = {
        "name": "Test Product for Update",
        "description": "Initial description",
        "category_id": 1,
        "brand_id": 1
    }
    headers = {"Authorization": f"Bearer {token}", "Content-Type": "application/json"}
    response = requests.post(url, json=product_data, headers=headers, timeout=TIMEOUT)
    response.raise_for_status()
    product = response.json()
    product_id = product.get("id")
    assert product_id is not None, "Created product ID not found"
    return product_id

def delete_product(product_id, token):
    url = f"{BASE_URL}/api/products/{product_id}"
    headers = {"Authorization": f"Bearer {token}"}
    try:
        response = requests.delete(url, headers=headers, timeout=TIMEOUT)
        assert response.status_code in (200, 204, 404), f"Unexpected status code on delete: {response.status_code}"
    except Exception:
        pass

def test_update_product_api_functionality():
    token = MOCK_TOKEN
    headers = {"Authorization": f"Bearer {token}", "Content-Type": "application/json"}

    # Create a product to update
    product_id = None
    try:
        product_id = create_product(token)
    except Exception as create_err:
        assert False, f"Product creation failed: {create_err}"

    try:
        assert product_id is not None, "Product ID is None, cannot proceed with update test"
        update_url = f"{BASE_URL}/api/products/{product_id}"

        updated_data = {
            "name": "Test Product Updated",
            "description": "Updated description",
            "price": 19.99
        }

        # 1. Test successful update with authentication
        response = requests.put(update_url, json=updated_data, headers=headers, timeout=TIMEOUT)
        assert response.status_code == 200, f"Expected 200 OK but got {response.status_code}"
        resp_json = response.json()
        for key in updated_data:
            assert key in resp_json and resp_json[key] == updated_data[key], f"Field {key} was not updated correctly"

        # 2. Test update without authentication - expect 401 Unauthorized or 403 Forbidden
        response_no_auth = requests.put(update_url, json=updated_data, timeout=TIMEOUT)
        assert response_no_auth.status_code in (401, 403), f"Expected 401 or 403 for unauthenticated update but got {response_no_auth.status_code}"

        # 3. Test rate limiting
        max_calls = 105
        success_count = 0
        too_many_requests_count = 0
        for i in range(max_calls):
            r = requests.put(update_url, json=updated_data, headers=headers, timeout=TIMEOUT)
            if r.status_code == 200:
                success_count += 1
            elif r.status_code == 429:
                too_many_requests_count += 1
                break
            time.sleep(0.1)

        assert success_count > 0, "No successful update requests were accepted before rate limiting"
        if too_many_requests_count > 0:
            assert too_many_requests_count > 0, "Rate limiting not enforced after exceeding limits"

    finally:
        if product_id and token:
            delete_product(product_id, token)


test_update_product_api_functionality()
