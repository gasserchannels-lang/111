import requests
import time

BASE_URL = "http://localhost:8000"
CLASSIFY_ENDPOINT = "/api/ai/classify-product"
TIMEOUT = 30
HEADERS = {"Content-Type": "application/json"}

def test_ai_product_classification_rate_limiting():
    # Sample payloads for classification testing
    test_payloads = [
        {"name": "Apple iPhone 14 Pro Max", "description": "Latest Apple smartphone with advanced camera"},
        {"name": "Samsung Galaxy S22 Ultra", "description": "Flagship Samsung phone with S Pen"},
        {"name": "Sony WH-1000XM4", "description": "Noise cancelling wireless headphones"},
        {"name": "Dell XPS 13", "description": "Compact and powerful ultrabook laptop"},
        {"name": "Nike Air Zoom Pegasus 38", "description": "Running shoes for long distance"},
    ]

    classifications = []
    rate_limit_errors = 0
    max_requests = 110  # exceed rate limit (100/minute) testing

    for i in range(max_requests):
        payload = test_payloads[i % len(test_payloads)]
        try:
            response = requests.post(
                f"{BASE_URL}{CLASSIFY_ENDPOINT}",
                json=payload,
                headers=HEADERS,
                timeout=TIMEOUT
            )
        except Exception as e:
            assert False, f"Request exception at iteration {i}: {e}"

        if response.status_code == 200:
            # Validate classification response structure and content
            try:
                data = response.json()
            except Exception:
                assert False, f"Invalid JSON response at iteration {i}"
            assert "category" in data, f"Missing category in response at iteration {i}"
            assert isinstance(data["category"], str) and data["category"], f"Empty category at iteration {i}"
            classifications.append(data["category"])
        elif response.status_code == 429:
            # Rate limiting error expected after threshold reached
            rate_limit_errors += 1
            try:
                error_data = response.json()
                assert "error" in error_data, "Rate limit error response missing 'error' field"
            except Exception:
                assert False, f"Invalid rate limit error JSON at iteration {i}"
            # Once rate limited, break further requests to avoid unnecessary calls
            break
        else:
            assert False, f"Unexpected status code {response.status_code} at iteration {i} with response: {response.text}"

    # Assert that at least one classification succeeded before hitting rate limit
    assert len(classifications) > 0, "No successful classifications before rate limit triggered"
    # Assert that rate limiting did occur
    assert rate_limit_errors > 0, "Rate limiting not enforced as expected"

test_ai_product_classification_rate_limiting()
