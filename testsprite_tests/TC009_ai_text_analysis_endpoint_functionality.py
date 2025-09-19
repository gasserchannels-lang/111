import requests
import time

BASE_URL = "http://localhost:8000"
AI_ANALYZE_ENDPOINT = f"{BASE_URL}/api/ai/analyze"
HEADERS = {"Content-Type": "application/json"}
TIMEOUT = 30

def test_tc009_ai_text_analysis_endpoint_functionality():
    # Prepare a valid payload for AI text analysis
    valid_payload = {
        "text": "Analyze the sentiment and key topics of this example text for AI testing purposes."
    }

    # Strong input validation tests: empty text, too long, invalid types
    invalid_payloads = [
        {"text": ""},  # empty text
        {"text": "a" * 10001},  # very long text (assuming limit < 10k chars)
        {"text": 12345},  # invalid type
        {},  # missing text
        {"text": None}
    ]

    # Check successful response & validate structure and latency
    start_time = time.time()
    response = requests.post(AI_ANALYZE_ENDPOINT, json=valid_payload, headers=HEADERS, timeout=TIMEOUT)
    duration = (time.time() - start_time) * 1000  # ms

    # Assert success status code
    assert response.status_code == 200, f"Expected 200 OK but got {response.status_code}"
    # Assert response time < 200ms as per quality requirements (API response time)
    assert duration < 200, f"Response time too slow: {duration} ms"

    # Validate JSON schema of response (basic expected fields)
    try:
        data = response.json()
    except Exception as e:
        assert False, f"Response is not valid JSON: {e}"

    # Validate presence and types of expected keys (example: sentiment, topics)
    assert isinstance(data, dict), "Response JSON root is not a dict"
    assert "sentiment" in data and isinstance(data["sentiment"], str), "Missing or invalid 'sentiment'"
    assert "topics" in data and isinstance(data["topics"], list), "Missing or invalid 'topics'"
    for topic in data["topics"]:
        assert isinstance(topic, str), "Each topic should be a string"

    # Validate no sensitive info and basic security (content length reasonable)
    assert len(response.content) < 5000, "Response payload is unexpectedly large"

    # Test invalid payloads to confirm proper validation and error handling
    for ipayload in invalid_payloads:
        r = requests.post(AI_ANALYZE_ENDPOINT, json=ipayload, headers=HEADERS, timeout=TIMEOUT)
        # Expecting 400 Bad Request or similar for invalid inputs
        assert r.status_code in (400, 422), f"Invalid input did not produce error, payload: {ipayload}"

    # Rate limiting test: Exceed limit by firing requests quickly
    # Given rate limit is 100/minute, test burst over limit (e.g., 110 requests)
    limit = 100
    burst_count = 110
    success_responses = 0
    too_many_requests_responses = 0
    other_errors = 0

    for i in range(burst_count):
        r = requests.post(AI_ANALYZE_ENDPOINT, json=valid_payload, headers=HEADERS, timeout=TIMEOUT)
        if r.status_code == 200:
            success_responses += 1
        elif r.status_code == 429:
            too_many_requests_responses += 1
        else:
            other_errors += 1
        # To better simulate rapid requests but avoid test time blow-up, no sleep here.

    # Assert some requests got 429 Too Many Requests once the limit exceeded
    assert too_many_requests_responses > 0, "Rate limiting not enforced under burst requests"

    # Assert at least some requests succeeded (non-zero 200)
    assert success_responses > 0, "No successful requests during burst"

    # Assert no unexpected errors
    assert other_errors == 0, f"Unexpected error responses received: {other_errors}"

test_tc009_ai_text_analysis_endpoint_functionality()