import requests
import time

BASE_URL = "http://localhost:80"
ADMIN_STATS_ENDPOINT = "/api/admin/stats"
TIMEOUT = 30


def test_admin_statistics_api_functionality():
    # Use placeholder tokens as PRD does not specify login endpoint
    admin_token = "mocked_admin_jwt_token"
    user_token = "mocked_user_jwt_token"

    admin_headers = {
        "Authorization": f"Bearer {admin_token}",
        "Accept": "application/json",
    }

    user_headers = {
        "Authorization": f"Bearer {user_token}",
        "Accept": "application/json",
    }

    try:
        # 1. Test access to /api/admin/stats as admin (success case)
        admin_resp = requests.get(
            f"{BASE_URL}{ADMIN_STATS_ENDPOINT}", headers=admin_headers, timeout=TIMEOUT
        )
        assert admin_resp.status_code == 200, f"Admin access failed with status {admin_resp.status_code}"
        json_data = admin_resp.json()
        # Validate expected keys in system statistics result (generic keys expected)
        expected_keys = (
            "user_count",
            "product_count",
            "total_sales",
            "active_sessions",
            "server_load",
        )
        # Not all keys may exist, check at least presence of a few important keys
        assert any(key in json_data for key in expected_keys), "Admin stats missing expected keys"

        # 2. Test access to /api/admin/stats as regular user (unauthorized)
        user_resp = requests.get(
            f"{BASE_URL}{ADMIN_STATS_ENDPOINT}", headers=user_headers, timeout=TIMEOUT
        )
        assert user_resp.status_code in [401, 403], f"Non-admin user should not access admin stats but got {user_resp.status_code}"

        # 3. Test access to /api/admin/stats without authentication (unauthorized)
        noauth_resp = requests.get(
            f"{BASE_URL}{ADMIN_STATS_ENDPOINT}", timeout=TIMEOUT
        )
        assert noauth_resp.status_code in [401, 403], f"Unauthenticated access should be denied with status 401 or 403 but got {noauth_resp.status_code}"

        # 4. Test rate limiting enforcement
        # Laravel default rate limit for this endpoint: 200/minute (~3 requests per second)
        # Rapidly hit the endpoint more than 10 times to try to trigger rate limit (considering burst possible)
        max_requests = 15
        rate_limit_triggered = False
        for i in range(max_requests):
            resp = requests.get(
                f"{BASE_URL}{ADMIN_STATS_ENDPOINT}", headers=admin_headers, timeout=TIMEOUT
            )
            if resp.status_code == 429:
                rate_limit_triggered = True
                break
            # Assert success or forbidden is unexpected here since admin is used
            assert resp.status_code == 200, f"Unexpected status {resp.status_code} during rate limit test"
            time.sleep(0.1)  # small delay between requests

        assert rate_limit_triggered, "Rate limiting not enforced for admin stats endpoint"

    except Exception as e:
        raise AssertionError(f"Test failed: {e}")


test_admin_statistics_api_functionality()
