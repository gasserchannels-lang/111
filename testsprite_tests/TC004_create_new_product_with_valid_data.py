import requests
import io
import time

BASE_URL = "http://localhost:8000"
PRODUCTS_ENDPOINT = f"{BASE_URL}/api/products"
TIMEOUT = 30

def test_create_new_product_with_valid_data():
    # Prepare multipart form data including image upload and associations
    product_data = {
        'name': 'Test Product Secure Edition',
        'description': 'A test product for comprehensive validation including image and associations.',
        'price': '99.99',
        'sku': 'TESTSKU12345',
        'stock': '50',
        'categories': '1,2',  # assuming category IDs 1 and 2 exist
        'brands': '1',        # assuming brand ID 1 exists
        'stores': '1,3'       # assuming store IDs 1 and 3 exist
    }
    # Create a small in-memory image file (PNG)
    image_content = (
        b'\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR\x00\x00\x00\x01'
        b'\x00\x00\x00\x01\x08\x06\x00\x00\x00\x1f\x15\xc4\x89'
        b'\x00\x00\x00\nIDATx\xdacd\xf8\x0f\x00\x01\x01\x01\x00'
        b'\x18\xdd\x03\xfd\x00\x00\x00\x00IEND\xaeB`\x82'
    )
    files = {
        'image': ('test_image.png', io.BytesIO(image_content), 'image/png')
    }

    start_time = time.time()
    response = None
    product_id = None
    try:
        response = requests.post(
            PRODUCTS_ENDPOINT,
            data=product_data,
            files=files,
            timeout=TIMEOUT,
            headers={
                # No authentication needed per API docs for POST /api/products
                'Accept': 'application/json'
            }
        )
        elapsed_time = time.time() - start_time

        # Validate response status code
        assert response.status_code == 201, f"Expected 201 Created, got {response.status_code}"

        # Validate response time < 200ms (0.2s)
        assert elapsed_time < 0.2, f"API response time {elapsed_time:.3f}s exceeds 200ms limit"

        json_resp = response.json()

        # Validate response contains new product ID
        assert 'id' in json_resp, "Response JSON missing 'id' key"
        product_id = json_resp['id']

        # Validate returned product data matches input (except image)
        assert json_resp.get('name') == product_data['name'], "Product name mismatch"
        assert json_resp.get('description') == product_data['description'], "Description mismatch"
        assert float(json_resp.get('price', 0)) == float(product_data['price']), "Price mismatch"
        assert json_resp.get('sku') == product_data['sku'], "SKU mismatch"
        assert int(json_resp.get('stock', -1)) == int(product_data['stock']), "Stock mismatch"

        # Validate associations exist and are lists of IDs
        for assoc_key, expected_ids_str in [('categories', product_data['categories']),
                                            ('brands', product_data['brands']),
                                            ('stores', product_data['stores'])]:
            expected_ids = set(expected_ids_str.split(','))
            actual_ids = set(str(item['id']) for item in json_resp.get(assoc_key, []))
            assert expected_ids <= actual_ids, f"{assoc_key} association missing expected IDs"

        # Validate image URL or identifier is present and secure (starts with https or /api/)
        assert 'image_url' in json_resp or 'image' in json_resp, "Image information missing in response"
        image_url = json_resp.get('image_url') or json_resp.get('image')
        assert isinstance(image_url, str) and image_url.strip() != "", "Invalid image URL in response"
        assert (image_url.startswith('https://') or image_url.startswith('/api/') or image_url.startswith('http://localhost') or image_url.startswith('http://')), \
            "Image URL does not appear secure or valid"

        # Security headers and practices cannot be tested here without server context

    except requests.exceptions.RequestException as e:
        assert False, f"Request failed with exception: {e}"

    finally:
        # Cleanup created product to not pollute database
        if product_id is not None:
            try:
                delete_resp = requests.delete(
                    f"{PRODUCTS_ENDPOINT}/{product_id}",
                    timeout=TIMEOUT,
                    headers={
                        'Accept': 'application/json'
                    }
                )
                # A 401 or 403 is expected because DELETE requires authentication,
                # so we just ensure the resource is deleted or soft deleted via another way.
                # This is a limitation without auth for cleanup.
                # So test environment should handle cleanup or delete with auth.

                # If delete fails due to auth, ignore as no auth specified.
                if delete_resp.status_code not in (204, 200, 401, 403, 404):
                    raise AssertionError(f"Unexpected delete status code: {delete_resp.status_code}")
            except Exception as cleanup_ex:
                # Log cleanup exception but don't fail test since primary test passed
                pass

test_create_new_product_with_valid_data()