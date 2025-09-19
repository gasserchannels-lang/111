import requests
import time

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

# Using admin or test user credentials for authentication
AUTH_EMAIL = "testadmin@example.com"
AUTH_PASSWORD = "StrongP@ssw0rd!"

def authenticate(email, password):
    url = f"{BASE_URL}/login"
    payload = {"email": email, "password": password}
    headers = {"Accept": "application/json"}
    try:
        resp = requests.post(url, json=payload, headers=headers, timeout=TIMEOUT)
        resp.raise_for_status()
        data = resp.json()
        # Laravel Sanctum likely uses cookies, but assuming JWT token in response for this test:
        # if token is in data['token'] or data['access_token']
        token = data.get("token") or data.get("access_token")
        if not token:
            raise Exception("Authentication token not found in response")
        return token
    except requests.RequestException as e:
        raise Exception(f"Authentication failed: {e}")

def create_product():
    url = f"{BASE_URL}/api/products"
    product_data = {
        "name": "Test Product Update Auth",
        "description": "Test product description for update with auth",
        "price": 19.99,
        "category_id": 1,
        "brand_id": 1,
        "stores": [1],
        "image": None  # Assuming image upload not required here; if required, adjust accordingly.
    }
    headers = {"Accept": "application/json"}
    try:
        resp = requests.post(url, json=product_data, headers=headers, timeout=TIMEOUT)
        resp.raise_for_status()
        product = resp.json()
        product_id = product.get("id") or product.get("data", {}).get("id")
        if not product_id:
            raise Exception("Created product ID not found.")
        return product_id
    except requests.RequestException as e:
        raise Exception(f"Product creation failed: {e}")

def delete_product(product_id, auth_token):
    url = f"{BASE_URL}/api/products/{product_id}"
    headers = {
        "Accept": "application/json",
        "Authorization": f"Bearer {auth_token}"
    }
    try:
        resp = requests.delete(url, headers=headers, timeout=TIMEOUT)
        # Assuming soft delete returns 200 or 204
        if resp.status_code not in (200, 204):
            raise Exception(f"Unexpected status code on delete: {resp.status_code}")
    except requests.RequestException as e:
        raise Exception(f"Failed to delete product {product_id}: {e}")

def get_product(product_id):
    url = f"{BASE_URL}/api/products/{product_id}"
    headers = {"Accept": "application/json"}
    try:
        resp = requests.get(url, headers=headers, timeout=TIMEOUT)
        resp.raise_for_status()
        return resp.json()
    except requests.RequestException as e:
        raise Exception(f"Get product failed: {e}")

def test_update_product_with_authentication():
    # Step 1: Authenticate to get token
    auth_token = authenticate(AUTH_EMAIL, AUTH_PASSWORD)
    headers_auth = {
        "Accept": "application/json",
        "Authorization": f"Bearer {auth_token}"
    }

    # Step 2: Create product to update
    product_id = create_product()

    try:
        # Step 3: Validate updating product without auth is rejected
        url_update = f"{BASE_URL}/api/products/{product_id}"
        update_data = {
            "name": "Should Not Update Without Auth"
        }
        headers_no_auth = {"Accept": "application/json"}
        resp_no_auth = requests.put(url_update, json=update_data, headers=headers_no_auth, timeout=TIMEOUT)
        assert resp_no_auth.status_code in (401, 403), f"Expected 401 or 403 for unauthenticated update, got {resp_no_auth.status_code}"

        # Step 4: Validate updating product with auth is successful
        full_update_data = {
            "name": "Updated Product Name Auth",
            "description": "Updated description with authentication",
            "price": 29.95,
            "category_id": 1,
            "brand_id": 1,
            "stores": [1]
        }

        start_time = time.time()
        resp = requests.put(url_update, json=full_update_data, headers=headers_auth, timeout=TIMEOUT)
        duration_ms = (time.time() - start_time) * 1000
        assert resp.status_code == 200, f"Expected 200 OK on update, got {resp.status_code}"
        assert duration_ms < 200, f"API response time {duration_ms}ms exceeded 200ms limit"

        updated_product = resp.json()
        # Validate that updated fields match
        for key in ["name", "description", "price"]:
            assert key in updated_product, f"Response missing '{key}' after update"
            assert updated_product[key] == full_update_data[key], f"Field {key} mismatch after update"

        # Step 5: Confirm persistence by GET
        fetched_product = get_product(product_id)
        for key in ["name", "description", "price"]:
            assert key in fetched_product, f"Fetched product missing '{key}'"
            assert fetched_product[key] == full_update_data[key], f"Fetched product field {key} mismatch"

    finally:
        # Clean up by deleting the product
        delete_product(product_id, auth_token)

test_update_product_with_authentication()