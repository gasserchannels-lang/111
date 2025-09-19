import requests
import time

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

def test_get_authenticated_user_returns_user_info():
    # Helper function to register a user and get JWT tokens
    def register_and_login_user():
        register_url = f"{BASE_URL}/api/register"
        login_url = f"{BASE_URL}/api/login"
        test_email = f"testuser_{int(time.time())}@example.com"
        password = "StrongP@ssw0rd!"

        # Register user
        register_payload = {
            "name": "Test User",
            "email": test_email,
            "password": password,
            "password_confirmation": password
        }
        r = requests.post(register_url, json=register_payload, timeout=TIMEOUT)
        assert r.status_code in (200,201), f"User registration failed: {r.text}"

        # Login user
        login_payload = {
            "email": test_email,
            "password": password
        }
        r = requests.post(login_url, json=login_payload, timeout=TIMEOUT)
        assert r.status_code == 200, f"User login failed: {r.text}"
        json_resp = r.json()
        assert "access_token" in json_resp or "token" in json_resp, "Login response missing token"
        token = json_resp.get("access_token") or json_resp.get("token")
        return token, test_email

    # Get authenticated user info with valid token
    token, user_email = register_and_login_user()
    headers_auth = {
        "Authorization": f"Bearer {token}"
    }
    url_user = f"{BASE_URL}/api/user"
    r = requests.get(url_user, headers=headers_auth, timeout=TIMEOUT)
    assert r.status_code == 200, f"Authenticated user info request failed: {r.text}"
    json_resp = r.json()
    # Validate user info keys and email matches
    assert isinstance(json_resp, dict), "Response is not a JSON object"
    for key in ("id", "name", "email", "created_at"):
        assert key in json_resp, f"Key '{key}' missing in user info"
    assert json_resp["email"].lower() == user_email.lower(), "Returned user email does not match logged in user"

    # Attempt access without authentication and expect failure
    r_no_auth = requests.get(url_user, timeout=TIMEOUT)
    assert r_no_auth.status_code in (401, 403), f"Unauthorized access should be rejected, got status {r_no_auth.status_code}"

    # Attempt access with invalid token and expect failure
    headers_invalid = {
        "Authorization": "Bearer invalidtoken123"
    }
    r_invalid = requests.get(url_user, headers=headers_invalid, timeout=TIMEOUT)
    assert r_invalid.status_code in (401, 403), f"Access with invalid token should be rejected, got status {r_invalid.status_code}"

test_get_authenticated_user_returns_user_info()