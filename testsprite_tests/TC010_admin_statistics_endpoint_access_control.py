import requests
import time

BASE_URL = "http://localhost:8000"
ADMIN_STATS_ENDPOINT = "/api/admin/stats"
TIMEOUT = 30

# Use placeholder JWT tokens for admin and user
ADMIN_TOKEN = "valid_admin_jwt_token_placeholder"
USER_TOKEN = "valid_user_jwt_token_placeholder"


def test_admin_stats_access_control():
    admin_headers = {"Authorization": f"Bearer {ADMIN_TOKEN}"}
    user_headers = {"Authorization": f"Bearer {USER_TOKEN}"}
    no_auth_headers = {}

    # 1. Admin user can access /api/admin/stats successfully
    try:
        admin_response = requests.get(f"{BASE_URL}{ADMIN_STATS_ENDPOINT}", headers=admin_headers, timeout=TIMEOUT)
        assert admin_response.status_code == 200, f"Expected 200 OK for admin, got {admin_response.status_code}"
        assert admin_response.text.strip() != "", "Admin stats response is empty"
        try:
            admin_json = admin_response.json()
        except requests.exceptions.JSONDecodeError:
            raise AssertionError("Admin stats response is not valid JSON")
        assert isinstance(admin_json, dict), "Admin stats response should be a JSON object"
        assert "uptime" in admin_json, "Expected 'uptime' key missing in admin stats response"
    except requests.RequestException as e:
        raise AssertionError(f"Admin request failed: {e}")

    # 2. Regular user is forbidden (403) or unauthorized (401) when accessing admin stats
    try:
        user_response = requests.get(f"{BASE_URL}{ADMIN_STATS_ENDPOINT}", headers=user_headers, timeout=TIMEOUT)
        assert user_response.status_code in (401, 403), f"Expected 401 or 403 for regular user, got {user_response.status_code}"
    except requests.RequestException as e:
        raise AssertionError(f"Regular user request failed: {e}")

    # 3. No authentication returns unauthorized (401)
    try:
        no_auth_response = requests.get(f"{BASE_URL}{ADMIN_STATS_ENDPOINT}", headers=no_auth_headers, timeout=TIMEOUT)
        assert no_auth_response.status_code == 401, f"Expected 401 for no auth, got {no_auth_response.status_code}"
    except requests.RequestException as e:
        raise AssertionError(f"No authentication request failed: {e}")

    # 4. Test rate limiting for admin user: exceed rate limit and expect 429 Too Many Requests
    last_response_status = None
    for i in range(210):
        try:
            resp = requests.get(f"{BASE_URL}{ADMIN_STATS_ENDPOINT}", headers=admin_headers, timeout=TIMEOUT)
            last_response_status = resp.status_code
            if resp.status_code == 429:
                break
            time.sleep(0.1)
        except requests.RequestException as e:
            raise AssertionError(f"Error during rate limit test at iteration {i + 1}: {e}")
    else:
        raise AssertionError("Rate limit not triggered after 210 requests; expected 429 Too Many Requests")

    # Optionally, test response headers for rate limiting (Retry-After)
    try:
        rate_limit_resp = requests.get(f"{BASE_URL}{ADMIN_STATS_ENDPOINT}", headers=admin_headers, timeout=TIMEOUT)
        if rate_limit_resp.status_code == 429:
            retry_after = rate_limit_resp.headers.get("Retry-After")
            assert retry_after is not None, "Retry-After header missing on 429 response"
    except requests.RequestException as e:
        raise AssertionError(f"Error validating Retry-After header: {e}")


test_admin_stats_access_control()
