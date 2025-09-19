import requests
import uuid

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

# Replace these with valid credentials for authentication
USERNAME = "admin@example.com"
PASSWORD = "AdminPassword123!"

def get_auth_token():
    """Authenticate and get JWT token."""
    try:
        response = requests.post(
            f"{BASE_URL}/api/login",
            json={"email": USERNAME, "password": PASSWORD},
            timeout=TIMEOUT
        )
        response.raise_for_status()
        data = response.json()
        token = data.get("token") or data.get("access_token")
        if not token:
            raise ValueError("Authentication token not found in response.")
        return token
    except Exception as e:
        raise RuntimeError(f"Authentication failed: {e}")

def create_product(token):
    """Create a new product to update later."""
    unique_suffix = str(uuid.uuid4())[:8]
    product_data = {
        "name": f"Test Product {unique_suffix}",
        "description": "Test Description",
        "price": 19.99,
        "category_id": 1,
        "brand_id": 1,
        "stores": [1],
        "image_url": "http://example.com/image.jpg"
    }
    headers = {
        "Authorization": f"Bearer {token}",
        "Content-Type": "application/json"
    }
    response = requests.post(
        f"{BASE_URL}/api/products",
        json=product_data,
        headers=headers,
        timeout=TIMEOUT
    )
    response.raise_for_status()
    product = response.json()
    product_id = product.get("id")
    if not product_id:
        raise ValueError("Created product ID not found.")
    return product_id, product_data

def delete_product(token, product_id):
    """Delete a product by ID."""
    headers = {
        "Authorization": f"Bearer {token}",
    }
    response = requests.delete(
        f"{BASE_URL}/api/products/{product_id}",
        headers=headers,
        timeout=TIMEOUT
    )
    # Soft delete is expected, so 200 or 204 acceptable
    if response.status_code not in (200, 204):
        raise RuntimeError(f"Failed to delete product {product_id}. Status code: {response.status_code}")

def get_product(token, product_id):
    """Get product details by ID."""
    headers = {"Authorization": f"Bearer {token}"}
    response = requests.get(
        f"{BASE_URL}/api/products/{product_id}",
        headers=headers,
        timeout=TIMEOUT
    )
    response.raise_for_status()
    return response.json()

def test_update_product_endpoint_updates_product():
    token = get_auth_token()
    product_id = None
    original_data = None
    try:
        # Create product
        product_id, original_data = create_product(token)

        # Prepare update data with changes
        updated_data = {
            "name": original_data["name"] + " Updated",
            "description": original_data["description"] + " Updated with more details",
            "price": original_data["price"] + 5.00,
            "category_id": original_data["category_id"],
            "brand_id": original_data["brand_id"],
            "stores": original_data["stores"],
            "image_url": original_data["image_url"]
        }
        headers = {
            "Authorization": f"Bearer {token}",
            "Content-Type": "application/json"
        }
        response = requests.put(
            f"{BASE_URL}/api/products/{product_id}",
            json=updated_data,
            headers=headers,
            timeout=TIMEOUT
        )

        # Validate status code for update success
        assert response.status_code in (200, 204), f"Unexpected status code {response.status_code} on update."

        # Validate updated product data by fetching it
        product_after_update = get_product(token, product_id)
        for key in ["name", "description", "price", "category_id", "brand_id", "image_url"]:
            assert product_after_update.get(key) == updated_data[key], f"Mismatch on {key}. Expected {updated_data[key]}, got {product_after_update.get(key)}"
        assert "stores" in product_after_update, "Stores field missing in product after update."
        # Assuming stores is a list of store IDs
        assert sorted(product_after_update["stores"]) == sorted(updated_data["stores"]), "Stores data does not match after update."

        # Test error cases:
        # 1. Update without auth
        response_unauth = requests.put(
            f"{BASE_URL}/api/products/{product_id}",
            json=updated_data,
            timeout=TIMEOUT
        )
        assert response_unauth.status_code == 401 or response_unauth.status_code == 403, f"Expected 401/403 on unauthorized update, got {response_unauth.status_code}"

        # 2. Update with invalid product ID
        invalid_id = "invalid-id-999999"
        response_invalid = requests.put(
            f"{BASE_URL}/api/products/{invalid_id}",
            json=updated_data,
            headers=headers,
            timeout=TIMEOUT
        )
        assert response_invalid.status_code == 404 or response_invalid.status_code == 400, f"Expected 404/400 on update with invalid ID, got {response_invalid.status_code}"

        # 3. Update with invalid data schema
        invalid_data = {"name": "", "price": -10}
        response_invalid_data = requests.put(
            f"{BASE_URL}/api/products/{product_id}",
            json=invalid_data,
            headers=headers,
            timeout=TIMEOUT
        )
        assert response_invalid_data.status_code == 400 or response_invalid_data.status_code == 422, f"Expected 400/422 on invalid update data, got {response_invalid_data.status_code}"

    finally:
        # Cleanup created product
        if product_id:
            try:
                delete_product(token, product_id)
            except Exception as ex:
                print(f"Cleanup failed: {ex}")

test_update_product_endpoint_updates_product()