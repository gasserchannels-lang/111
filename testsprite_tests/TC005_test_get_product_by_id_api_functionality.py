import requests
import time

BASE_URL = "http://localhost:8000"
TIMEOUT = 30
HEADERS = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

def test_get_product_by_id_api_functionality():
    product_id = None
    created_product_id = None
    try:
        # Step 1: Create a new product to get a valid product ID for testing
        unique_suffix = str(int(time.time()))
        product_data = {
            "name": f"Test Product TC005 {unique_suffix}",
            "description": "Test product description for TC005",
            "category_id": 1,  # Assuming category with ID 1 exists
            "brand_id": 1,     # Assuming brand with ID 1 exists
            "store_ids": [1],  # Assuming store with ID 1 exists
            "price": 19.99,
            "image": None
        }
        # Create product (POST /api/products)
        create_resp = requests.post(
            f"{BASE_URL}/api/products",
            json=product_data,
            headers=HEADERS,
            timeout=TIMEOUT
        )
        assert create_resp.status_code == 201 or create_resp.status_code == 200, f"Product creation failed: {create_resp.text}"
        create_json = create_resp.json()
        # Extract created product id
        if isinstance(create_json, dict):
            created_product_id = create_json.get("id") or create_json.get("data", {}).get("id")
        assert created_product_id is not None, "Created product ID not found in response"

        product_id = created_product_id

        # Step 2: Get product by ID (GET /api/products/{id})
        get_resp = requests.get(
            f"{BASE_URL}/api/products/{product_id}",
            headers=HEADERS,
            timeout=TIMEOUT
        )
        assert get_resp.status_code == 200, f"Failed to get product by ID {product_id}: {get_resp.text}"
        product = get_resp.json()

        # Validate presence of product details and associations: category, brand, stores
        assert isinstance(product, dict), "Product response is not a dictionary"
        # Check core expected fields
        for key in ("id", "name", "description", "category", "brand", "stores"):
            assert key in product, f"'{key}' not in product response"
        
        # Validate that category, brand and stores data exist and have expected structure
        category = product.get("category")
        brand = product.get("brand")
        stores = product.get("stores")

        assert isinstance(category, dict), "Category data is missing or not a dict"
        assert "id" in category and "name" in category, "Category data incomplete"

        assert isinstance(brand, dict), "Brand data is missing or not a dict"
        assert "id" in brand and "name" in brand, "Brand data incomplete"

        assert isinstance(stores, list), "Stores should be a list"
        assert len(stores) > 0, "Stores list is empty"
        for store in stores:
            assert isinstance(store, dict), "Store item is not a dict"
            assert "id" in store and "name" in store, "Store data incomplete"

    finally:
        # Cleanup: delete the created product to avoid clutter
        if created_product_id is not None:
            try:
                requests.delete(
                    f"{BASE_URL}/api/products/{created_product_id}",
                    headers=HEADERS,
                    timeout=TIMEOUT
                )
            except Exception:
                pass


test_get_product_by_id_api_functionality()