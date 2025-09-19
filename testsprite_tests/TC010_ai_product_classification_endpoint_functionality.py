import requests
import time

BASE_URL = "http://localhost:8000"
CLASSIFY_ENDPOINT = "/api/ai/classify-product"
TIMEOUT = 30
RATE_LIMIT_REQUESTS = 100  # as per PRD (100/minute)
RATE_LIMIT_INTERVAL = 60   # 60 seconds per minute


def test_ai_product_classification_rate_limit_and_functionality():
    headers = {
        "Content-Type": "application/json",
        # Add additional security headers if available, e.g. X-Content-Type-Options, CSP, etc.
    }

    # Example valid product data for classification
    valid_payload = {
        "product_name": "Wireless Bluetooth Headphones",
        "product_description": "Over-ear, noise-cancelling wireless headphones with 30 hours battery life.",
        "attributes": {
            "brand": "SoundX",
            "category": "Electronics",
            "color": "Black"
        }
    }

    # Validate AI product classification response structure and content functionally and securely
    def validate_response(resp):
        assert resp.status_code == 200, f"Expected HTTP 200 OK, got {resp.status_code}"
        data = resp.json()
        assert isinstance(data, dict), "Response JSON must be a dict"
        assert "classification" in data, "'classification' key missing in response"
        classification = data["classification"]
        assert isinstance(classification, dict), "'classification' should be a dict"
        # Example fields to validate in classification
        assert "category" in classification, "'category' missing in classification"
        assert isinstance(classification["category"], str), "'category' should be a string"
        # Optional confidence score validation
        if "confidence" in classification:
            confidence = classification["confidence"]
            assert isinstance(confidence, float), "'confidence' should be float"
            assert 0.0 <= confidence <= 1.0, "'confidence' should be between 0 and 1"

    # Test rate limiting by sending requests up to the limit and one exceeding it
    success_count = 0
    error_count = 0
    error_429_received = False

    start_time = time.time()
    for i in range(RATE_LIMIT_REQUESTS + 1):  # One request more than limit
        try:
            resp = requests.post(
                f"{BASE_URL}{CLASSIFY_ENDPOINT}",
                json=valid_payload,
                headers=headers,
                timeout=TIMEOUT,
            )
        except requests.RequestException as e:
            assert False, f"Request failed unexpectedly: {e}"

        if resp.status_code == 200:
            validate_response(resp)
            success_count += 1
        elif resp.status_code == 429:
            # Rate limit error should occur exactly once (the last request)
            error_429_received = True
            error_count += 1
            assert "rate limit" in resp.text.lower() or "too many requests" in resp.text.lower(), \
                "429 response missing appropriate rate limit message"
        else:
            # Unexpected error
            assert False, f"Unexpected status code received: {resp.status_code}, response: {resp.text}"

        # To avoid hitting actual limit too fast, small delay can be added (optional)
        time.sleep(0.1)

    duration = time.time() - start_time

    assert success_count == RATE_LIMIT_REQUESTS, f"Expected {RATE_LIMIT_REQUESTS} successful requests, got {success_count}"
    assert error_429_received, "Expected to receive a 429 rate limit error after exceeding limit"
    assert error_count == 1, f"Expected exactly 1 rate limit error, got {error_count}"
    # Ensure test duration is reasonable (<= 60 seconds approximately)
    assert duration <= RATE_LIMIT_INTERVAL + 10, f"Test took too long: {duration} seconds"

    # Security headers check (if endpoint exposes headers or we can check in response headers)
    security_headers = [
        "Content-Type",
        # Further security relevant headers could be checked here
    ]
    for sh in security_headers:
        assert sh in resp.headers, f"Expected security header '{sh}' missing from response"


test_ai_product_classification_rate_limit_and_functionality()
