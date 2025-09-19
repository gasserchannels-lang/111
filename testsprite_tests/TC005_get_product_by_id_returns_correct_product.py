import requests
import time

BASE_URL = "http://localhost:8000"
TIMEOUT = 30


def test_get_product_by_id_returns_correct_product():
    # Step 1: Create a new product to test GET by id endpoint
    create_url = f"{BASE_URL}/api/products"
    product_payload = {
        "name": "Test Product TC005",
        "description": "A test product for TC005 - get product by id",
        "price": 123.45,
        "image": None  # assuming image upload optional for this test; skip image upload if no schema provided
    }
    headers = {
        "Content-Type": "application/json"
    }

    # Create the product
    try:
        create_resp = requests.post(create_url, json=product_payload, headers=headers, timeout=TIMEOUT)
        assert create_resp.status_code == 201 or create_resp.status_code == 200, \
            f"Expected 201/200 response but got {create_resp.status_code} with body: {create_resp.text}"
        created_product = create_resp.json()
        assert "id" in created_product, "Response JSON missing 'id' for created product"
        product_id = created_product["id"]

        # Step 2: Measure GET request duration and validate performance < 200ms
        get_url = f"{BASE_URL}/api/products/{product_id}"
        start_time = time.time()
        get_resp = requests.get(get_url, headers={"Accept": "application/json"}, timeout=TIMEOUT)
        elapsed_ms = (time.time() - start_time) * 1000

        assert get_resp.status_code == 200, f"GET product by id returned status code {get_resp.status_code}"
        assert elapsed_ms < 200, f"GET product by id took too long: {elapsed_ms:.2f}ms, expected <200ms"

        product_data = get_resp.json()
        # Validate returned product data matches what was created
        # validate keys and types for security (no extra fields exposure)
        expected_keys = {"id", "name", "description", "price", "image"}
        received_keys = set(product_data.keys())
        assert expected_keys.issubset(received_keys), f"Returned product keys missing expected keys: {expected_keys - received_keys}"

        # Validate exact values (except id which we already have)
        assert product_data["id"] == product_id, "Product ID mismatch"
        assert product_data["name"] == product_payload["name"], "Product name mismatch"
        assert product_data["description"] == product_payload["description"], "Product description mismatch"
        # Validate price with tolerance for float rounding
        assert abs(float(product_data["price"]) - product_payload["price"]) < 0.01, "Product price mismatch"
        # Image can be None or string URL
        assert product_data["image"] is None or isinstance(product_data["image"], (str, type(None))), "Product image invalid type"

        # Security headers check for GET (best effort)
        # Here we might check if some common security headers are present - optional as not stated in API but good practice
        security_headers = ["Content-Security-Policy", "X-Content-Type-Options", "X-Frame-Options", "Strict-Transport-Security", "Referrer-Policy"]
        for header in security_headers:
            # It is a plus if present, but not mandatory to fail test
            # So no assert here, just check presence and optionally log if needed
            pass

    finally:
        # Cleanup: Delete the created product to maintain test isolation (if DELETE requires auth, skip here)
        # From PRD DELETE /api/products/{id} requires auth, so cannot delete without token, skip cleanup
        # Alternative: If soft-delete is automatic or test environment reset is assumed, no further action.
        pass


test_get_product_by_id_returns_correct_product()
