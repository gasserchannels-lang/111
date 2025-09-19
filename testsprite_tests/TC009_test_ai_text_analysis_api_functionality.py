import requests
import time
from requests.exceptions import JSONDecodeError

BASE_URL = "http://localhost:8000"
AI_ANALYZE_PATH = "/api/ai/analyze"
TIMEOUT = 30

def test_ai_text_analysis_api_functionality():
    headers = {
        "Content-Type": "application/json"
    }
    payload = {
        "text": "Analyze this text using AI to extract meaningful insights."
    }

    # Test a successful AI text analysis request
    response = requests.post(
        f"{BASE_URL}{AI_ANALYZE_PATH}",
        json=payload,
        headers=headers,
        timeout=TIMEOUT
    )
    assert response.status_code == 200, f"Expected 200 OK, got {response.status_code}"
    try:
        json_response = response.json()
    except ValueError:
        assert False, f"Response is not valid JSON. Response content: {response.text}"
    # Basic validation that response contains some expected keys
    assert isinstance(json_response, dict), "Response is not a JSON object"
    assert "analysis" in json_response, "Response missing 'analysis' field"

    # Test rate limiting - send multiple requests to exceed the rate limit
    # Assuming rate limit is 100/minute, send 105 requests quickly and expect some 429 responses
    rate_limit_exceeded = False
    for _ in range(105):
        resp = requests.post(
            f"{BASE_URL}{AI_ANALYZE_PATH}",
            json=payload,
            headers=headers,
            timeout=TIMEOUT
        )
        if resp.status_code == 429:
            rate_limit_exceeded = True
            break
        else:
            # Small delay to avoid hitting limit too fast if server uses sliding window
            time.sleep(0.1)
    assert rate_limit_exceeded, "Rate limiting not enforced or no 429 response received"

test_ai_text_analysis_api_functionality()
