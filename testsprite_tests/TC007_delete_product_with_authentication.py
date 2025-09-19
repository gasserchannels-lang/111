import requests
import time

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

# Assuming admin user credentials for authentication (adjust as needed)
ADMIN_EMAIL = "admin@example.com"
ADMIN_PASSWORD = "adminpassword"

def authenticate(email: str, password: str):
    url = f"{BASE_URL}/api/login"
    data = {"email": email, "password": password}
    try:
        response = requests.post(url, json=data, timeout=TIMEOUT)
        response.raise_for_status()
        token = response.json().get("token") or response.json().get("access_token")
        if not token:
            raise ValueError("Authentication token not found in response")
        return token
    except Exception as e:
        raise RuntimeError(f"Authentication failed: {e}")

def create_product():
    url = f"{BASE_URL}/api/products"
    product_data = {
        "name": "Test Product Delete",
        "description": "Product used for delete test case",
        "price": 9.99,
        "category_id": 1,
        "brand_id": 1,
        "stores": [1],
        "image_url": "https://example.com/image.jpg"
    }
    try:
        response = requests.post(url, json=product_data, timeout=TIMEOUT)
        response.raise_for_status()
        product = response.json()
        product_id = product.get("id") or product.get("product").get("id") if product.get("product") else None
        if not product_id:
            raise ValueError("Created product ID not returned")
        return product_id
    except Exception as e:
        raise RuntimeError(f"Product creation failed: {e}")

def get_product(product_id, token=None):
    url = f"{BASE_URL}/api/products/{product_id}"
    headers = {}
    if token:
        headers["Authorization"] = f"Bearer {token}"
    response = requests.get(url, headers=headers, timeout=TIMEOUT)
    return response

def delete_product(product_id, token):
    url = f"{BASE_URL}/api/products/{product_id}"
    headers = {"Authorization": f"Bearer {token}"}
    response = requests.delete(url, headers=headers, timeout=TIMEOUT)
    return response

def test_delete_product_with_authentication():
    # Authenticate as admin user
    token = authenticate(ADMIN_EMAIL, ADMIN_PASSWORD)
    assert token, "Authentication token is required"

    product_id = None
    try:
        # Create a new product to delete
        product_id = create_product()
        assert product_id, "Product ID must be obtained after creation"

        # Confirm product exists before delete
        response = get_product(product_id)
        assert response.status_code == 200, f"Expected 200 OK before delete, got {response.status_code}"
        product_data = response.json()
        assert product_data.get("id") == product_id or product_data.get("product", {}).get("id") == product_id

        # Attempt delete without auth token - should fail
        url = f"{BASE_URL}/api/products/{product_id}"
        response_unauth = requests.delete(url, timeout=TIMEOUT)
        assert response_unauth.status_code in (401, 403), f"Unauthorized delete request should fail, got {response_unauth.status_code}"

        # Delete the product with authentication
        response_delete = delete_product(product_id, token)
        assert response_delete.status_code in (200, 204), f"Expected 200 or 204 on delete, got {response_delete.status_code}"

        # Verify soft delete - product should not be fully removed, but flagged as deleted
        # Depending on API, it might return 404 or a flag on GET.
        # We'll do GET and expect 404 or a "deleted" flag (soft delete)
        time.sleep(0.5)
        get_after_delete = get_product(product_id, token)
        if get_after_delete.status_code == 404:
            # Product is not visible - acceptable for soft delete
            pass
        elif get_after_delete.status_code == 200:
            # Check for soft delete flag if present
            json_data = get_after_delete.json()
            deleted_at = json_data.get("deleted_at") or json_data.get("product", {}).get("deleted_at")
            assert deleted_at is not None, "Product should be soft deleted with 'deleted_at' timestamp"
        else:
            assert False, f"Unexpected status code after delete {get_after_delete.status_code}"

    finally:
        # Cleanup: attempt to hard delete the product forcibly if API supports (not specified)
        # We'll try to delete again with auth in case product still exists
        if product_id:
            try:
                _ = delete_product(product_id, token)
            except Exception:
                pass

test_delete_product_with_authentication()