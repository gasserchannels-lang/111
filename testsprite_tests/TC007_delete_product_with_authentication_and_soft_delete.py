import requests
import time

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

# Credentials for authentication (should be replaced with valid test credentials)
TEST_USER_EMAIL = "admin@example.com"
TEST_USER_PASSWORD = "AdminPassword123!"

def test_delete_product_with_authentication_and_soft_delete():
    session = requests.Session()
    session.headers.update({'Accept': 'application/json'})

    # Helper functions
    def authenticate():
        login_url = f"{BASE_URL}/api/login"
        payload = {
            "email": TEST_USER_EMAIL,
            "password": TEST_USER_PASSWORD
        }
        resp = session.post(login_url, json=payload, timeout=TIMEOUT)
        assert resp.status_code == 200, f"Authentication failed: {resp.status_code} {resp.text}"
        data = resp.json()
        assert 'token' in data or 'access_token' in data, "Auth token not in response"
        token = data.get('token') or data.get('access_token')
        session.headers.update({'Authorization': f"Bearer {token}"})

    def create_product():
        url = f"{BASE_URL}/api/products"
        # Minimal valid product data that may be accepted by creation endpoint
        payload = {
            "name": f"Test Product {int(time.time()*1000)}",
            "description": "Test product description for deletion test",
            "price": 9.99,
            "category_id": 1,  # Assuming category 1 exists
            "brand_id": 1,     # Assuming brand 1 exists
            "stores": [1]      # Assuming store 1 exists
        }
        resp = session.post(url, json=payload, timeout=TIMEOUT)
        assert resp.status_code == 201, f"Failed to create product: {resp.status_code} {resp.text}"
        product = resp.json()
        assert 'id' in product, "Created product does not contain id"
        return product['id']

    def get_product(product_id):
        url = f"{BASE_URL}/api/products/{product_id}"
        resp = session.get(url, timeout=TIMEOUT)
        return resp

    def delete_product_unauthenticated(product_id):
        url = f"{BASE_URL}/api/products/{product_id}"
        # Remove Authorization header to test unauthenticated deletion
        headers = session.headers.copy()
        headers.pop('Authorization', None)
        resp = requests.delete(url, headers=headers, timeout=TIMEOUT)
        return resp

    def delete_product_authenticated(product_id):
        url = f"{BASE_URL}/api/products/{product_id}"
        resp = session.delete(url, timeout=TIMEOUT)
        return resp

    # Begin test
    try:
        # 1. Create product without auth (public endpoint allows)
        product_id = create_product()

        # 2. Attempt to delete without authentication -> expect 401 Unauthorized or 403 Forbidden
        resp_unauth = delete_product_unauthenticated(product_id)
        assert resp_unauth.status_code in (401, 403), f"Unauthenticated delete did not fail as expected, got {resp_unauth.status_code}"

        # 3. Authenticate user (admin or authorized role)
        authenticate()

        # 4. Delete product with authentication -> expect 200 OK or 204 No Content
        resp_delete = delete_product_authenticated(product_id)
        assert resp_delete.status_code in (200, 204), f"Authenticated delete failed: {resp_delete.status_code} {resp_delete.text}"

        # 5. Validate soft delete: the product should not be accessible or marked deleted
        # First, attempt to GET product again
        resp_get_after_delete = get_product(product_id)
        # Soft deleted product may be excluded (404) or marked with a flag (e.g. deleted_at non-null)
        if resp_get_after_delete.status_code == 404:
            # Considered deleted / hidden
            pass
        elif resp_get_after_delete.status_code == 200:
            product_data = resp_get_after_delete.json()
            # Check soft delete flag e.g. deleted_at or is_deleted field
            deleted_at = product_data.get('deleted_at') or product_data.get('is_deleted')
            assert deleted_at not in (None, False, "", 0), "Product not soft deleted - delete flag missing or false"
        else:
            assert False, f"Unexpected status code fetching product after delete: {resp_get_after_delete.status_code}"

    finally:
        # Cleanup: try hard delete if API supports or ignore if soft deleted
        # Authenticated user may hard delete? Trying to force clean-up
        try:
            authenticate()
            url = f"{BASE_URL}/api/products/{product_id}/hard-delete"
            resp_hd = session.delete(url, timeout=TIMEOUT)
            # Ignore response, best effort
        except Exception:
            pass

test_delete_product_with_authentication_and_soft_delete()