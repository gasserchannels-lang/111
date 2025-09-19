import requests

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

# Replace this with a valid JWT token for an authenticated user having permission to update products
# Since the PRD does not define a login endpoint, using a placeholder token here.
AUTH_TOKEN = "your_valid_jwt_token_here"

def get_auth_token():
    """Return the authentication token."""
    # Return the placeholder token directly
    return AUTH_TOKEN

def create_product():
    """Create a product to update later."""
    url = f"{BASE_URL}/api/products"
    product_data = {
        "name": "Test Product",
        "description": "Test product description",
        "price": 19.99,
        "categories": [1],  # assuming category with ID 1 exists
        "brand": 1,          # assuming brand with ID 1 exists
        "stores": [1],       # assuming store with ID 1 exists
        "image_url": "http://example.com/image.jpg"
    }
    resp = requests.post(url, json=product_data, timeout=TIMEOUT)
    resp.raise_for_status()
    product = resp.json()
    product_id = product.get("id")
    assert product_id, "Created product ID is missing"
    return product_id

def delete_product(product_id, token):
    """Delete the test product to clean up."""
    url = f"{BASE_URL}/api/products/{product_id}"
    headers = {"Authorization": f"Bearer {token}"}
    resp = requests.delete(url, headers=headers, timeout=TIMEOUT)
    # Soft delete expected, allow 200 or 204 status codes
    assert resp.status_code in (200, 204), f"Failed to delete product with status {resp.status_code}"

def test_update_product_api():
    token = get_auth_token()
    headers = {
        "Authorization": f"Bearer {token}",
        "Content-Type": "application/json"
    }
    product_id = create_product()
    try:
        url = f"{BASE_URL}/api/products/{product_id}"
        updated_data = {
            "name": "Updated Test Product",
            "description": "Updated product description",
            "price": 29.99,
            "categories": [2],  # changed category ID to 2
            "brand": 2,         # changed brand ID to 2
            "stores": [2,3],    # changed stores to multiple stores
            "image_url": "http://example.com/updated_image.jpg"
        }
        resp = requests.put(url, json=updated_data, headers=headers, timeout=TIMEOUT)
        assert resp.status_code == 200, f"Expected 200 OK for update, got {resp.status_code}"
        updated_product = resp.json()
        assert updated_product.get("name") == updated_data["name"]
        assert updated_product.get("description") == updated_data["description"]
        assert float(updated_product.get("price", 0)) == updated_data["price"]
        # Validate categories, brand, stores keys exist and match the request
        assert set(updated_product.get("categories", [])) == set(updated_data["categories"])
        assert updated_product.get("brand") == updated_data["brand"]
        assert set(updated_product.get("stores", [])) == set(updated_data["stores"])
        assert updated_product.get("image_url") == updated_data["image_url"]

        # Test authorization by attempting update without token
        resp_unauth = requests.put(url, json=updated_data, timeout=TIMEOUT)
        assert resp_unauth.status_code in (401, 403), "Unauthorized update should be rejected"

        # Test rate limiting: simulate more than allowed requests quickly
        for _ in range(101):
            r = requests.put(url, json=updated_data, headers=headers, timeout=TIMEOUT)
            if r.status_code == 429:
                break
        else:
            raise AssertionError("Rate limiting not enforced on update endpoint")
    finally:
        delete_product(product_id, token)

test_update_product_api()
