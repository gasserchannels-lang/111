import requests

BASE_URL = "http://localhost:8000"
PRODUCTS_ENDPOINT = "/api/products"
TIMEOUT = 30

def test_list_all_products_api_functionality():
    url = BASE_URL + PRODUCTS_ENDPOINT
    headers = {
        "Accept": "application/json"
    }
    try:
        response = requests.get(url, headers=headers, timeout=TIMEOUT)
        response.raise_for_status()
    except requests.RequestException as e:
        assert False, f"Request to list all products failed: {e}"

    assert response.status_code == 200, f"Expected 200 OK but got {response.status_code}"

    try:
        products = response.json()
    except ValueError:
        assert False, "Response is not a valid JSON"

    assert isinstance(products, list), "Products response should be a list"

    # Verify each product has associations to categories, brands, and stores
    for product in products:
        assert isinstance(product, dict), "Each product should be a dictionary"
        # Check presence of essential keys
        assert "id" in product, "Product should contain 'id'"
        assert "name" in product, "Product should contain 'name'"
        # category and brand are optional or nullable
        if "category" in product:
            category = product["category"]
            assert category is None or isinstance(category, dict), "Category should be dict or None"
            if isinstance(category, dict):
                assert "id" in category, "Category should have 'id'"
                assert "name" in category, "Category should have 'name'"
        if "brand" in product:
            brand = product["brand"]
            assert brand is None or isinstance(brand, dict), "Brand should be dict or None"
            if isinstance(brand, dict):
                assert "id" in brand, "Brand should have 'id'"
                assert "name" in brand, "Brand should have 'name'"

        # Validate stores structure
        assert "stores" in product, "Product should contain 'stores' association"
        stores = product["stores"]
        assert isinstance(stores, list), "Stores should be a list"
        for store in stores:
            assert isinstance(store, dict), "Each store should be a dictionary"
            assert "id" in store, "Store should have 'id'"
            assert "name" in store, "Store should have 'name'"

test_list_all_products_api_functionality()