import requests
import time

BASE_URL = "http://localhost:8000"
USER_ENDPOINT = "/api/user"
LOGIN_ENDPOINT = "/api/login"  # Assuming /api/login exists for obtaining token
TIMEOUT = 30

# These credentials should be valid for the test environment
TEST_USER_CREDENTIALS = {
    "email": "testuser@example.com",
    "password": "TestPassword123!"
}

def test_get_authenticated_user_api_functionality():
    session = requests.Session()

    # Step 1: Authenticate to obtain JWT token
    try:
        login_resp = session.post(
            BASE_URL + LOGIN_ENDPOINT,
            json=TEST_USER_CREDENTIALS,
            timeout=TIMEOUT
        )
        assert login_resp.status_code == 200, f"Login failed with status {login_resp.status_code}"
        login_data = login_resp.json()
        assert "token" in login_data or "access_token" in login_data, "No token found in login response"

        token = login_data.get("token") or login_data.get("access_token")
        headers = {"Authorization": f"Bearer {token}"}

        # Step 2: Access /api/user with valid token
        user_resp = session.get(
            BASE_URL + USER_ENDPOINT,
            headers=headers,
            timeout=TIMEOUT
        )
        assert user_resp.status_code == 200, f"Authenticated user endpoint failed with status {user_resp.status_code}"
        user_data = user_resp.json()
        assert isinstance(user_data, dict), "User data should be a dictionary"
        # Basic checks for user fields presence
        assert "id" in user_data, "User data missing 'id'"
        assert "email" in user_data, "User data missing 'email'"

        # Step 3: Access /api/user without token (should be unauthorized)
        unauth_resp = session.get(
            BASE_URL + USER_ENDPOINT,
            timeout=TIMEOUT
        )
        assert unauth_resp.status_code in [401, 403], \
            f"Unauthenticated access should be unauthorized but got {unauth_resp.status_code}"

        # Step 4: Test rate limiting by sending multiple requests rapidly
        # Assuming rate limit is 100/minute, so sending 105 requests quickly to trigger limit
        rate_limit_hit = False
        for i in range(105):
            r = session.get(BASE_URL + USER_ENDPOINT, headers=headers, timeout=TIMEOUT)
            if r.status_code == 429:  # HTTP 429 Too Many Requests
                rate_limit_hit = True
                break
            time.sleep(0.1)  # small delay to avoid immediate spamming (optional)

        assert rate_limit_hit, "Rate limiting not enforced (expected HTTP 429 Too Many Requests)"

    finally:
        session.close()

test_get_authenticated_user_api_functionality()