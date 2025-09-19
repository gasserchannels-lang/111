import requests
import base64
import os

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

def test_create_product_api():
    # Prepare product data
    # Provide a base64 encoded image string as image field expects a string
    dummy_image_data = base64.b64encode(b'testimagebytes').decode('utf-8')

    product_data = {
        "name": "Test Product TC004",
        "description": "This is a test product for TC004",
        "price": 19.99,
        "category_id": 1,  # Assuming category with ID 1 exists
        "brand_id": 1,    # Assuming brand with ID 1 exists
        "store_ids": [1, 2],  # Assuming stores with IDs 1 and 2 exist
        "stock": 100,
        "sku": "TC004SKU",
        "image": dummy_image_data
    }

    url = f"{BASE_URL}/api/products"
    headers = {}

    # Send POST request to create product
    response = requests.post(url, json=product_data, headers=headers, timeout=TIMEOUT)

    # Validate response status code 201 Created or 200 OK depending on API
    assert response.status_code in (200, 201), f"Unexpected status code: {response.status_code}, Response: {response.text}"

    json_resp = response.json()
    # Validate response has product ID
    assert "id" in json_resp, "Response JSON missing 'id'"
    product_id = json_resp["id"]

    # Validate product fields in response
    assert json_resp.get("name") == product_data["name"]
    assert json_resp.get("category_id") == product_data["category_id"]
    assert json_resp.get("brand_id") == product_data["brand_id"]

    # Test rate limiting: send multiple quick requests to check 429 status
    # Attempting 31 requests to trigger 30/min limit

    rate_limit_hit = False
    for i in range(31):
        resp = requests.post(url, json=product_data, headers=headers, timeout=TIMEOUT)
        if resp.status_code == 429:
            rate_limit_hit = True
            break
    assert rate_limit_hit, "Rate limiting not enforced, expected 429 status code on excessive requests"

    # Attempt deleting the created product to clean state if possible
    if product_id:
        try:
            delete_url = f"{BASE_URL}/api/products/{product_id}"
            # API delete requires authentication, skipping actual delete due to no token info
            # If admin token was available, we would send DELETE request here for cleanup
            pass
        except Exception:
            pass

test_create_product_api()