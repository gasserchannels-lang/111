import requests
import time

BASE_URL = "http://localhost:8000"
TIMEOUT = 30  # seconds

# NOTE: For strict testing, we simulate a login to get a valid JWT token.
# Adjust these credentials with valid test user credentials in the test environment.
AUTH_LOGIN_ENDPOINT = f"{BASE_URL}/api/auth/login"
USER_ENDPOINT = f"{BASE_URL}/api/user"


def test_get_authenticated_user_details():
    # Use strictest validation including security, performance, and data checks
    
    # Credentials for authentication - replace with valid test user credentials
    credentials = {
        "email": "testuser@example.com",
        "password": "StrongTestPassword123!"
    }

    # 1. Authenticate and obtain JWT token
    try:
        login_response = requests.post(
            AUTH_LOGIN_ENDPOINT,
            json=credentials,
            timeout=TIMEOUT
        )
    except requests.RequestException as e:
        assert False, f"Login request failed: {e}"

    # Validate login response
    assert login_response.status_code == 200, f"Login failed with status {login_response.status_code}"
    login_json = login_response.json()
    assert "token" in login_json and isinstance(login_json["token"], str) and login_json["token"], "Login response missing valid token"

    token = login_json["token"]

    headers = {
        "Authorization": f"Bearer {token}",
        "Accept": "application/json"
    }

    # 2. Measure response time for /api/user endpoint
    start_time = time.time()
    try:
        user_response = requests.get(USER_ENDPOINT, headers=headers, timeout=TIMEOUT)
    except requests.RequestException as e:
        assert False, f"Authenticated user details request failed: {e}"
    elapsed = (time.time() - start_time) * 1000  # ms

    # 3. Validate response status code
    assert user_response.status_code == 200, f"Expected status 200, got {user_response.status_code}"

    # 4. Validate performance requirement (< 200 ms)
    assert elapsed < 200, f"API response time exceeded threshold: {elapsed:.1f} ms"

    # 5. Validate response content-type is JSON
    content_type = user_response.headers.get("Content-Type", "")
    assert "application/json" in content_type, f"Unexpected Content-Type: {content_type}"

    # 6. Parse response JSON and validate user details
    try:
        user_data = user_response.json()
    except ValueError:
        assert False, "Response body is not valid JSON"

    # Basic required fields validation (assuming standard user fields)
    assert isinstance(user_data, dict), "User data should be a JSON object"
    required_fields = ["id", "name", "email", "roles", "created_at", "updated_at"]
    for field in required_fields:
        assert field in user_data, f"Missing required user field: {field}"

    # Validate ID is positive integer
    assert isinstance(user_data["id"], int) and user_data["id"] > 0, "User ID should be positive integer"

    # Validate email format (simple validation)
    email = user_data["email"]
    assert isinstance(email, str) and "@" in email and "." in email, "Invalid email format"

    # Validate roles is non-empty list including 'user' or 'admin'
    roles = user_data["roles"]
    assert isinstance(roles, list) and len(roles) > 0, "Roles should be non-empty list"
    valid_roles = {"user", "admin"}
    assert any(r in valid_roles for r in roles), f"User roles must include at least one of {valid_roles}"

    # 7. Validate timestamps are ISO8601 strings and reasonable past date
    import datetime

    for ts_field in ["created_at", "updated_at"]:
        ts_value = user_data[ts_field]
        try:
            dt = datetime.datetime.fromisoformat(ts_value.replace("Z", "+00:00"))
        except Exception:
            assert False, f"Timestamp {ts_field} is not valid ISO8601 datetime"
        # Should not be in the future
        assert dt <= datetime.datetime.now(datetime.timezone.utc), f"{ts_field} is in the future"

    # 8. Test authorization: without token, access should be denied
    try:
        anon_response = requests.get(USER_ENDPOINT, timeout=TIMEOUT)
    except requests.RequestException as e:
        assert False, f"Request without auth failed unexpectedly: {e}"
    assert anon_response.status_code in (401, 403), f"Unauthorized access expected 401 or 403 but got {anon_response.status_code}"

    # 9. Test authorization: with invalid token, access denied
    headers_invalid = {
        "Authorization": "Bearer invalid.token.value",
        "Accept": "application/json"
    }
    try:
        invalid_response = requests.get(USER_ENDPOINT, headers=headers_invalid, timeout=TIMEOUT)
    except requests.RequestException as e:
        assert False, f"Request with invalid token failed unexpectedly: {e}"
    assert invalid_response.status_code in (401, 403), f"Invalid token expected 401 or 403 but got {invalid_response.status_code}"


test_get_authenticated_user_details()