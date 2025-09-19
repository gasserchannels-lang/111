import requests

BASE_URL = "http://localhost:8000"
TIMEOUT = 30


def test_create_product_endpoint_creates_product():
    url = f"{BASE_URL}/api/products"

    product_data = {
        "name": "Test Product for Creation",
        "description": "This is a test product created during automated testing.",
        "price": 19.99,
        "category_id": 1,  # assuming category 1 exists
        "brand_id": 1,     # assuming brand 1 exists
        "store_ids": [1, 2],  # assuming stores 1 and 2 exist
        "stock": 50,
        "sku": "TESTSKU-123",
        "attributes": {
            "color": "red",
            "size": "M"
        }
    }

    try:
        response = requests.post(url, json=product_data, timeout=TIMEOUT)
        assert response.status_code == 201, f"Expected status code 201, got {response.status_code}"
        json_response = response.json()

        assert "id" in json_response, "Response JSON missing 'id'"

        assert json_response["name"] == product_data["name"]
        assert json_response["description"] == product_data["description"]
        assert float(json_response["price"]) == product_data["price"]
        assert int(json_response["category_id"]) == product_data["category_id"]
        assert int(json_response["brand_id"]) == product_data["brand_id"]

        returned_store_ids = json_response.get("store_ids") or json_response.get("stores")
        assert returned_store_ids is not None, "Response JSON missing 'store_ids' or 'stores'"
        # convert all to int for comparison
        returned_store_ids_set = set(map(int, returned_store_ids))
        expected_store_ids_set = set(product_data["store_ids"])
        assert expected_store_ids_set.issubset(returned_store_ids_set), f"Returned store_ids {returned_store_ids_set} do not include all expected store_ids {expected_store_ids_set}"

        assert int(json_response.get("stock", 0)) == product_data["stock"]
        assert json_response.get("sku") == product_data["sku"]

        image_url = json_response.get("image_url") or json_response.get("image")
        if image_url is not None:
            assert isinstance(image_url, str) and image_url.strip() != ""

    except requests.RequestException as e:
        assert False, f"Request to create product failed: {e}"
    except ValueError:
        assert False, "Response is not valid JSON"


test_create_product_endpoint_creates_product()
