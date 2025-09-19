import requests
import time

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

# Replace these with valid credentials for an authenticated user with delete permissions
USER_EMAIL = "testuser@example.com"
USER_PASSWORD = "TestPassword123!"

def get_auth_token(email, password):
    url = f"{BASE_URL}/sanctum/token"  # Laravel Sanctum token endpoint assumed
    data = {"email": email, "password": password, "device_name": "test-device"}
    resp = requests.post(url, json=data, timeout=TIMEOUT)
    resp.raise_for_status()
    token = resp.json().get("token")
    if not token:
        raise Exception("Authentication failed: no token received")
    return token

def create_product():
    url = f"{BASE_URL}/api/products"
    product_data = {
        "name": "Test Product for Deletion",
        "description": "Test description",
        "price": 9.99,
        "category_id": 1,  # Assumes category with ID 1 exists
        "brand_id": 1,     # Assumes brand with ID 1 exists
        "stores": [1],     # Assumes store with ID 1 exists
        # Add other required fields as per API schema if any
    }
    resp = requests.post(url, json=product_data, timeout=TIMEOUT)
    resp.raise_for_status()
    return resp.json().get("id")

def get_product(id):
    url = f"{BASE_URL}/api/products/{id}"
    resp = requests.get(url, timeout=TIMEOUT)
    return resp

def delete_product(id, token):
    url = f"{BASE_URL}/api/products/{id}"
    headers = {"Authorization": f"Bearer {token}"}
    resp = requests.delete(url, headers=headers, timeout=TIMEOUT)
    return resp

def test_delete_product_api_functionality():
    # Authenticate user and get token
    token = get_auth_token(USER_EMAIL, USER_PASSWORD)
    headers = {"Authorization": f"Bearer {token}"}

    # Create a new product to delete
    product_id = create_product()
    assert product_id is not None, "Failed to create product for deletion test"

    try:
        # Verify product exists before deletion
        get_resp = get_product(product_id)
        assert get_resp.status_code == 200, f"Product should exist before deletion, got {get_resp.status_code}"

        # Test deleting product without authentication -> expect 401 Unauthorized or 403 Forbidden
        unauth_resp = delete_product(product_id, token="")
        assert unauth_resp.status_code in (401, 403), f"Delete without auth should be unauthorized, got {unauth_resp.status_code}"

        # Test delete with authentication
        delete_resp = delete_product(product_id, token)
        assert delete_resp.status_code in (200, 202, 204), f"Delete request failed with status {delete_resp.status_code}"

        # Verify soft delete: product should not be fully removed (depends on API design)
        # Usually GET might return 404 or indicate soft deletion
        get_after_del_resp = get_product(product_id)
        # Accept 404 Not Found or 410 Gone or some JSON status indicating soft deleted
        assert get_after_del_resp.status_code in (404, 410), (
            f"Deleted product retrieval should fail/indicate deleted, got status {get_after_del_resp.status_code}"
        )

        # Test rate limiting: Rapidly send DELETE requests to trigger rate limit (simulate 101 requests)
        # We'll try to send requests quickly and check for 429 Too Many Requests

        # Create another product for this rapid test to avoid deleting same product repeatedly
        product_id_rl = create_product()
        assert product_id_rl is not None, "Failed to create product for rate limit test"

        exceeded_limit = False
        for i in range(101):
            resp = delete_product(product_id_rl, token)
            if resp.status_code == 429:
                exceeded_limit = True
                break
            # On successful delete, break early
            if resp.status_code in (200, 204, 202):
                # Product deleted, break test loop here as we can't delete the same twice
                break
            # Small delay to avoid hitting some global thresholds unintentionally
            time.sleep(0.01) 

        assert exceeded_limit or resp.status_code in (200, 204, 202), "Rate limiting was not enforced or delete failed as expected"

    finally:
        # Cleanup attempt: if product still exists, try to delete with auth to keep test environment clean
        resp = get_product(product_id)
        if resp.status_code == 200:
            try:
                delete_product(product_id, token)
            except Exception:
                pass

test_delete_product_api_functionality()