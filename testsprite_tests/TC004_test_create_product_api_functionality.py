import requests

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

def test_create_product_api_functionality():
    url = f"{BASE_URL}/api/products"

    # Example product data
    product_data = {
        "name": "Test Product",
        "description": "This is a test product description",
        "price": "19.99",
        "category_id": 1,
        "brand_id": 1,
        "stores": [1, 2]
    }

    # Since API expects 'image' as a string, provide image name as string
    data = {
        "name": product_data["name"],
        "description": product_data["description"],
        "price": product_data["price"],
        "category_id": str(product_data["category_id"]),
        "brand_id": str(product_data["brand_id"]),
        "image": "test_image.png",
    }

    # For 'stores' as array, send multiple values with the same key
    # requests supports this by passing a list of tuples
    stores_fields = [("stores[]", str(store_id)) for store_id in product_data["stores"]]

    # Combine data dict into list of tuples to allow multiple 'stores[]'
    data_items = list(data.items()) + stores_fields

    try:
        response = requests.post(url, data=data_items, timeout=TIMEOUT)
    except requests.RequestException as e:
        assert False, f"Request failed: {e}"

    assert response.status_code == 201 or response.status_code == 200, f"Unexpected status code: {response.status_code} - {response.text}"

    try:
        resp_json = response.json()
    except ValueError:
        assert False, "Response is not a valid JSON"

    assert "id" in resp_json, "Response JSON missing 'id' key"
    assert resp_json.get("name") == product_data["name"], "Product name mismatch in response"
    assert resp_json.get("description") == product_data["description"], "Product description mismatch in response"
    assert "image_url" in resp_json or "image" in resp_json, "Response missing image info"

    product_id = resp_json["id"]

    # Validate input data: test invalid cases (e.g. missing required field)
    invalid_data = {
        "description": "Missing name field",
        "price": "10.00",
        "category_id": "1",
        "brand_id": "1",
        "image": "test_image.png",
        "stores[]": "1"
    }
    try:
        invalid_resp = requests.post(url, data=invalid_data, timeout=TIMEOUT)
    except requests.RequestException as e:
        assert False, f"Invalid request failed unexpectedly: {e}"

    assert 400 <= invalid_resp.status_code < 500, f"Expected client error but got {invalid_resp.status_code}"

    # Cleanup created product
    try:
        del_url = f"{BASE_URL}/api/products/{product_id}"
        del_resp = requests.delete(del_url, timeout=TIMEOUT)
        assert del_resp.status_code in [200, 204, 401, 403, 404], f"Unexpected delete status: {del_resp.status_code}"
    except requests.RequestException:
        pass

test_create_product_api_functionality()