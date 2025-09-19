import requests
import time


BASE_URL = "http://localhost:8000"
TIMEOUT = 30


def test_best_offer_api_functionality():
    product_list_resp = requests.get(f"{BASE_URL}/api/products", timeout=TIMEOUT)
    assert product_list_resp.status_code == 200, f"Failed to list products: {product_list_resp.text}"
    products = product_list_resp.json()
    assert isinstance(products, list) and len(products) > 0, "Product list is empty or not a list"
    product_id = products[0].get("id")
    assert product_id is not None, "First product does not have an id"

    params = {"product_id": product_id}
    # Send a request within rate limits to get best offer
    best_offer_resp = requests.get(f"{BASE_URL}/api/price-search/best-offer", params=params, timeout=TIMEOUT)
    assert best_offer_resp.status_code == 200, f"Failed to get best offer: {best_offer_resp.text}"

    best_offer_data = best_offer_resp.json()
    assert isinstance(best_offer_data, dict), "Best offer response is not a dictionary"
    assert "price" in best_offer_data, "Best offer response missing 'price'"
    assert "store" in best_offer_data, "Best offer response missing 'store'"
    assert isinstance(best_offer_data["price"], (int, float)), "'price' is not a number"
    assert best_offer_data["price"] > 0, "'price' is not greater than zero"
    assert isinstance(best_offer_data["store"], str) and best_offer_data["store"], "'store' is not a valid string"

    # Check rate limiting enforcement: exceed 30 requests quickly
    rate_limit_exceeded = False
    for i in range(35):  # Try more than 30 requests rapidly
        resp = requests.get(f"{BASE_URL}/api/price-search/best-offer", params=params, timeout=TIMEOUT)
        if resp.status_code == 429:
            rate_limit_exceeded = True
            break
        # Small delay to not be totally instant but still rapid
        time.sleep(0.05)

    assert rate_limit_exceeded, "Rate limiting not enforced; did not receive HTTP 429 after rapid requests"


test_best_offer_api_functionality()