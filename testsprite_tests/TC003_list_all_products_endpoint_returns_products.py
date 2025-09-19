import requests
import time

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

def test_list_all_products_endpoint_returns_products():
    url = f"{BASE_URL}/api/products"
    try:
        start_time = time.time()
        response = requests.get(url, timeout=TIMEOUT)
        elapsed_time = (time.time() - start_time) * 1000  # in milliseconds
        
        # Validate status code
        assert response.status_code == 200, f"Expected 200 OK, got {response.status_code}"

        # Validate response time < 200ms as per quality requirements
        assert elapsed_time < 200, f"Response time too high: {elapsed_time}ms"

        data = response.json()

        # Validate data is a list
        assert isinstance(data, list), f"Expected response to be a list, got {type(data)}"

        for product in data:
            # Each product must be a dict
            assert isinstance(product, dict), f"Each product should be dict, got {type(product)}"

            # Check required keys presence
            required_keys = ['id', 'name', 'categories', 'brand', 'stores']
            for key in required_keys:
                assert key in product, f"Missing key '{key}' in product"

            # Validate non-empty id and name
            assert isinstance(product['id'], int) and product['id'] > 0, "Product ID should be a positive integer"
            assert isinstance(product['name'], str) and product['name'], "Product name should be non-empty string"

            # Validate categories is a list of dicts with id and name
            categories = product['categories']
            assert isinstance(categories, list), "Categories should be a list"
            for cat in categories:
                assert isinstance(cat, dict), "Each category should be a dict"
                assert 'id' in cat and isinstance(cat['id'], int) and cat['id'] > 0, "Category id invalid"
                assert 'name' in cat and isinstance(cat['name'], str) and cat['name'], "Category name invalid"

            # Validate brand is a dict with id and name
            brand = product['brand']
            assert isinstance(brand, dict), "Brand should be a dict"
            assert 'id' in brand and isinstance(brand['id'], int) and brand['id'] > 0, "Brand id invalid"
            assert 'name' in brand and isinstance(brand['name'], str) and brand['name'], "Brand name invalid"

            # Validate stores is a list of dicts with id and name
            stores = product['stores']
            assert isinstance(stores, list), "Stores should be a list"
            for store in stores:
                assert isinstance(store, dict), "Each store should be a dict"
                assert 'id' in store and isinstance(store['id'], int) and store['id'] > 0, "Store id invalid"
                assert 'name' in store and isinstance(store['name'], str) and store['name'], "Store name invalid"
    except requests.Timeout:
        assert False, f"Request timed out after {TIMEOUT} seconds"
    except requests.RequestException as e:
        assert False, f"Request failed: {e}"

test_list_all_products_endpoint_returns_products()