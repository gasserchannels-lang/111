import requests
import time

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

def test_ai_text_analysis_api():
    url = f"{BASE_URL}/api/ai/analyze"
    headers = {
        "Content-Type": "application/json",
        "Accept": "application/json"
    }
    payload = {
        "text": "This is a sample text to analyze with AI to check sentiment and key topics."
    }
    max_retries = 5
    retry_delay = 1  # seconds

    for attempt in range(max_retries):
        try:
            response = requests.post(url, json=payload, headers=headers, timeout=TIMEOUT)
            if response.status_code == 429:
                # Handle rate limiting, wait and retry
                retry_after = int(response.headers.get("Retry-After", retry_delay))
                time.sleep(retry_after)
                continue
            # For other status codes, break the retry loop and validate
            break
        except requests.RequestException as e:
            if attempt < max_retries - 1:
                time.sleep(retry_delay)
                continue
            else:
                raise e

    assert response.status_code == 200, f"Expected status code 200, got {response.status_code}"

    try:
        data = response.json()
    except ValueError:
        assert False, "Response is not valid JSON"

    # Validate expected analysis results presence
    assert "sentiment" in data, "'sentiment' field not found in response"
    assert data["sentiment"] in ("positive", "neutral", "negative"), "Unexpected sentiment value"
    assert "key_topics" in data, "'key_topics' field not found in response"
    assert isinstance(data["key_topics"], list), "'key_topics' should be a list"

test_ai_text_analysis_api()