import requests
import time

BASE_URL = "http://localhost:8000"
HEADERS = {
    "Content-Type": "application/json"
}
TIMEOUT = 30

def test_ai_text_analysis_endpoint_processes_text():
    analyze_url = f"{BASE_URL}/api/ai/analyze"
    payload = {
        "text": "Analyze this sample text for testing the AI text analysis endpoint."
    }
    # Test normal successful request
    try:
        response = requests.post(analyze_url, json=payload, headers=HEADERS, timeout=TIMEOUT)
        assert response.status_code in [200, 422], f"Expected 200 OK or 422 but got {response.status_code}"
        if response.status_code == 200:
            json_response = response.json()
            # Validate some expected fields in the response
            assert isinstance(json_response, dict), "Response is not a JSON object"
            assert "analysis" in json_response, "'analysis' field missing in response"
            # analysis field could be string or structured data, just check it is not empty
            assert json_response["analysis"], "'analysis' field is empty"
    except Exception as e:
        assert False, f"Exception occurred during successful request test: {e}"

    # Test input validation: missing "text"
    try:
        response = requests.post(analyze_url, json={}, headers=HEADERS, timeout=TIMEOUT)
        # Expect 4xx error for invalid input
        assert response.status_code in [400, 422], f"Expected 400 or 422 error but got {response.status_code}"
    except Exception as e:
        assert False, f"Exception occurred during missing text validation test: {e}"

    # Test input validation: empty text
    try:
        response = requests.post(analyze_url, json={"text": ""}, headers=HEADERS, timeout=TIMEOUT)
        # Possibly 400 or 422 error for empty text, or maybe valid response with no analysis
        if response.status_code == 200:
            json_resp = response.json()
            # If analysis field is present it should be empty or indicate no input
            assert "analysis" in json_resp, "'analysis' missing in response for empty text"
        else:
            assert response.status_code in [400, 422], f"Expected 400 or 422 error for empty text but got {response.status_code}"
    except Exception as e:
        assert False, f"Exception during empty text validation: {e}"

    # Test rate limiting enforcement (the rate limit is 100/minute)
    # Make 101 rapid requests and expect the last one to be rate limited (429)
    headers = HEADERS.copy()
    rate_limit_exceeded = False
    try:
        for i in range(101):
            resp = requests.post(analyze_url, json=payload, headers=headers, timeout=TIMEOUT)
            if i < 100:
                # Expect success or possibly 429 if limits are strict already
                assert resp.status_code in [200, 429], f"Unexpected status code {resp.status_code} on request {i+1}"
                if resp.status_code == 429:
                    rate_limit_exceeded = True
                    break
            else:
                # On 101st request expect 429 error if not already triggered
                if resp.status_code == 429:
                    rate_limit_exceeded = True
                else:
                    # Sometimes rate limiter may delay rejecting
                    rate_limit_exceeded = False
        # Assert that rate limiting was enforced if enough requests done rapidly
        assert rate_limit_exceeded, "Rate limiting was not enforced after 100 requests"
    except Exception as e:
        assert False, f"Exception during rate limiting test: {e}"

    # Test unexpected content-type or method
    try:
        # Send GET request to the POST-only endpoint
        resp = requests.get(analyze_url, headers=HEADERS, timeout=TIMEOUT)
        assert resp.status_code in [405, 404], f"Expected 405 Method Not Allowed or 404, got {resp.status_code}"
    except Exception as e:
        assert False, f"Exception during method not allowed test: {e}"

test_ai_text_analysis_endpoint_processes_text()
