import requests
import time

BASE_URL = "http://localhost:8000"
CLASSIFY_ENDPOINT = "/api/ai/classify-product"
TIMEOUT = 30


def test_ai_product_classification_api():
    url = f"{BASE_URL}{CLASSIFY_ENDPOINT}"
    headers = {
        "Content-Type": "application/json"
    }
    # Typical product description text for classification
    payload = {
        "text": "Organic extra virgin olive oil, 500ml bottle, cold-pressed, from Spain"
    }

    # Make the first request, expect success 200 with classification result
    try:
        response = requests.post(url, json=payload, headers=headers, timeout=TIMEOUT)
        # Check for HTTP success
        assert response.status_code == 200, f"Expected status 200, got {response.status_code}"
        json_resp = response.json()
        # Basic validation of classification response structure
        assert "classification" in json_resp, "'classification' key missing in response"
        assert isinstance(json_resp["classification"], dict), "'classification' should be a dictionary"
        assert "category" in json_resp["classification"], "'category' key missing in classification"
        assert "confidence" in json_resp["classification"], "'confidence' key missing in classification"
        assert 0 <= json_resp["classification"]["confidence"] <= 1, "Confidence should be between 0 and 1"
    except requests.RequestException as e:
        assert False, f"Request failed: {e}"

    # Immediately send additional requests to test rate limiting (simulate burst)
    rate_limit_hit = False
    for _ in range(10):
        try:
            resp = requests.post(url, json=payload, headers=headers, timeout=TIMEOUT)
            if resp.status_code == 429:
                rate_limit_hit = True
                break
            # Accept 200 or 429 only
            assert resp.status_code in {200, 429}, f"Unexpected status code: {resp.status_code}"
        except requests.RequestException:
            # On request exception, continue to next
            pass
        time.sleep(0.1)  # small delay between requests

    # Assert that rate limiting was properly enforced at some point during burst
    assert rate_limit_hit, "Rate limiting not enforced, expected HTTP 429 during burst requests"


test_ai_product_classification_api()