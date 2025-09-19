import requests
import time

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

# Dummy admin credentials for authentication (should be replaced with valid test creds)
ADMIN_EMAIL = "admin@example.com"
ADMIN_PASSWORD = "adminpassword"

def get_auth_token(email, password):
    url = f"{BASE_URL}/api/login"
    payload = {"email": email, "password": password}
    headers = {"Content-Type": "application/json"}
    try:
        response = requests.post(url, json=payload, headers=headers, timeout=TIMEOUT)
        response.raise_for_status()
        token = response.json().get("token") or response.json().get("access_token")
        if not token:
            raise Exception("Authentication token not found in login response.")
        return token
    except Exception as e:
        raise Exception(f"Failed to authenticate: {str(e)}")

def create_product():
    url = f"{BASE_URL}/api/products"
    # Minimal valid product data for creation
    product_data = {
        "name": f"Test Product {int(time.time())}",
        "description": "Test product description",
        "price": 19.99,
        "category_id": 1,
        "brand_id": 1,
        "stores": [1],
        "image_url": "https://example.com/test-image.jpg"
    }
    headers = {"Content-Type": "application/json"}
    response = requests.post(url, json=product_data, headers=headers, timeout=TIMEOUT)
    response.raise_for_status()
    product = response.json()
    product_id = product.get("id")
    if not product_id:
        raise Exception("Created product ID not returned.")
    return product_id

def get_product(product_id):
    url = f"{BASE_URL}/api/products/{product_id}"
    response = requests.get(url, timeout=TIMEOUT)
    response.raise_for_status()
    return response.json()

def delete_product(product_id, token):
    url = f"{BASE_URL}/api/products/{product_id}"
    headers = {"Authorization": f"Bearer {token}"}
    response = requests.delete(url, headers=headers, timeout=TIMEOUT)
    return response

def test_delete_product_soft_delete():
    # Authenticate as admin user to get token required for delete
    token = get_auth_token(ADMIN_EMAIL, ADMIN_PASSWORD)
    assert token, "Authentication token is required for deleting a product."

    product_id = create_product()
    assert product_id, "Failed to create a product for deletion test."

    try:
        # Attempt to delete product without authentication - should fail
        url = f"{BASE_URL}/api/products/{product_id}"
        response_unauth = requests.delete(url, timeout=TIMEOUT)
        assert response_unauth.status_code in (401, 403), f"Expected 401/403 for unauthenticated delete, got {response_unauth.status_code}"

        # Delete product with valid token - should succeed and soft delete product
        response = delete_product(product_id, token)
        assert response.status_code == 200 or response.status_code == 204, f"Expected 200 or 204 on deletion, got {response.status_code}"

        # Validate product still exists but is marked as deleted (soft delete)
        # This assumes soft delete field may be 'deleted_at' or 'is_deleted'
        product = get_product(product_id)
        # Check typical soft delete indicators
        # Accept either a null or non-null deleted_at timestamp or a boolean flag
        deleted_at = product.get("deleted_at")
        is_deleted = product.get("is_deleted")

        assert deleted_at is not None or is_deleted is True, "Product was not soft deleted (deleted_at or is_deleted flag missing)."

        # Verify product data still intact (e.g. name, description remain)
        assert "name" in product and product["name"].startswith("Test Product"), "Product data integrity compromised after soft delete."

    finally:
        # Cleanup: attempt hard delete if possible by authentication or API design - if not exists, ignore
        # Some APIs do not allow hard delete via public endpoints; skip if 403/404
        try:
            # Assume token allows permanent deletion via same endpoint with query parameter hard=true or similar? unknown, so skip.
            # If production supports, implement here.
            pass
        except Exception:
            pass

test_delete_product_soft_delete()