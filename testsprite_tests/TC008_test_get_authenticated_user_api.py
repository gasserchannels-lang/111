import requests

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

# Replace these with valid user credentials for authentication
TEST_USER_CREDENTIALS = {
    "email": "testuser@example.com",
    "password": "TestPassword123!"
}

def get_auth_token():
    """
    Obtain JWT token by logging in user via assumed /api/login endpoint.
    Adjust if the actual login endpoint or auth details differ.
    """
    login_url = f"{BASE_URL}/api/login"
    try:
        response = requests.post(login_url, json=TEST_USER_CREDENTIALS, timeout=TIMEOUT)
        response.raise_for_status()
        data = response.json()
        # Assuming the token is in 'token' field in JSON response
        token = data.get("token")
        assert token, "Login did not return a token"
        return token
    except requests.RequestException as e:
        raise AssertionError(f"Failed to obtain auth token: {e}")

def test_get_authenticated_user_api():
    token = get_auth_token()

    headers = {
        "Authorization": f"Bearer {token}",
        "Accept": "application/json"
    }

    url = f"{BASE_URL}/api/user"

    # 1. Test successful authenticated request returns user details
    try:
        response = requests.get(url, headers=headers, timeout=TIMEOUT)
        response.raise_for_status()
    except requests.RequestException as e:
        raise AssertionError(f"Authenticated GET /api/user request failed: {e}")

    assert response.status_code == 200, f"Expected 200 OK, got {response.status_code}"

    user_data = response.json()
    assert isinstance(user_data, dict), "Response JSON is not an object"
    # Basic expected fields (adjust according to actual user schema)
    for field in ("id", "email", "name"):
        assert field in user_data, f"User data missing expected field '{field}'"

    # 2. Test unauthenticated request returns 401 Unauthorized
    try:
        unauth_resp = requests.get(url, timeout=TIMEOUT)
    except requests.RequestException as e:
        raise AssertionError(f"Unauthenticated GET /api/user request failed: {e}")

    assert unauth_resp.status_code == 401, f"Expected 401 Unauthorized for unauthenticated request, got {unauth_resp.status_code}"

    # 3. Test rate limiting enforcement by sending multiple requests quickly
    # Assuming rate limit is 100/minute, try to trigger limit by 105 requests
    rate_limit_exceeded = False
    for _ in range(105):
        try:
            rate_resp = requests.get(url, headers=headers, timeout=TIMEOUT)
            if rate_resp.status_code == 429:
                rate_limit_exceeded = True
                break
        except requests.RequestException:
            # Ignore individual request failures for this test
            pass

    assert rate_limit_exceeded, "Rate limiting (429) was not enforced after exceeding limit"

test_get_authenticated_user_api()