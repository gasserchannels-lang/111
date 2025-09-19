import requests
import time

BASE_URL = "http://localhost:8000"
CLASSIFY_ENDPOINT = "/api/ai/classify-product"
TIMEOUT = 30
HEADERS = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

def test_ai_product_classification_api_functionality():
    # Updated single text field payload for classification
    sample_payload = {
        "text": "Ultra HD 4K Television - A 55-inch smart TV with HDR and voice control"
    }

    # Function to call classify-product endpoint
    def classify_product():
        response = requests.post(
            BASE_URL + CLASSIFY_ENDPOINT,
            json=sample_payload,
            headers=HEADERS,
            timeout=TIMEOUT
        )
        return response

    # Initial successful call to verify classification works
    response = classify_product()
    assert response.status_code == 200, f"Expected 200 OK, got {response.status_code}"
    json_data = response.json()
    assert "classification" in json_data, "Response JSON missing 'classification'"
    assert isinstance(json_data["classification"], dict), "'classification' should be a dict"
    assert "category" in json_data["classification"], "'classification' missing 'category'"
    assert "confidence" in json_data["classification"], "'classification' missing 'confidence'"

    # Test rate limiting by sending multiple rapid requests until limit reached.
    # Since exact limit unknown but max is 100/minute, we test sending bursts.
    max_attempts = 105
    success_responses = 1
    rate_limited_responses = 0

    for i in range(max_attempts - 1):
        r = classify_product()
        if r.status_code == 200:
            success_responses += 1
        elif r.status_code == 429:
            rate_limited_responses += 1
        else:
            # Unexpected status code, fail test
            assert False, f"Unexpected status code {r.status_code} at attempt {i+2}"

        time.sleep(0.2)

    assert success_responses + rate_limited_responses == max_attempts, \
        "Sum of success and 429 responses should equal total attempts"

    assert rate_limited_responses > 0, "Expected some requests to be rate limited (429)"

test_ai_product_classification_api_functionality()