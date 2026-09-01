# F01 Authentication (API v1)

Give **this file only** to frontend Cursor, plus Blade screenshots for **this feature**. Do not implement other feature IDs in the same pass.

Status: **ready**

Auth: after F01, every call uses Authorization Bearer token and Accept: application/json. Base: APP_URL/api/v1.

How to read the JSON: apisfront = endpoints/payloads; list_filters = query keys (same as Blade, not extra routes); apisspec_plan = UI rules.

```json
{
  "Authentication": {
    "status": "ready",
    "id": "F01",
    "apisback": "Laravel Sanctum personal access tokens (new table personal_access_tokens only). Controllers: App\\Http\\Controllers\\Api\\V1\\Auth\\LoginController, LogoutController, MeController. Request: App\\Http\\Requests\\Api\\V1\\Auth\\LoginRequest (does not call web Auth::attempt / session). Resource: App\\Http\\Resources\\Api\\V1\\Auth\\UserResource. User model only gained HasApiTokens. Web login/logout and routes/web.php + routes/auth.php unchanged. ActivityLogger logLogin/logLogout reused. JSON 401/403 via bootstrap/app.php shouldRenderJsonWhen for api/*. Same users table and existing roles/permissions. No enum changes.",
    "apisfront": {
      "login": {
        "endpoint": "/api/v1/auth/login",
        "method": "POST",
        "headers": {
          "Accept": "application/json",
          "Content-Type": "application/json"
        },
        "request_payload": {
          "email": "user@example.com",
          "password": "secret"
        },
        "expected_response": {
          "token": "1|plainTextToken",
          "token_type": "Bearer",
          "user": {
            "id": 1,
            "name": "Name",
            "email": "user@example.com",
            "department": "string-or-null",
            "currency_code": "USD-or-null",
            "is_super_admin": false,
            "roles": [
              "pr-requester"
            ],
            "permissions": [
              "procurement-requests.view-own"
            ]
          }
        },
        "error_codes": {
          "422": "Validation or invalid credentials (errors.email). Also throttle after 5 attempts.",
          "429": "Too many requests (route throttle 5/min)."
        }
      },
      "me": {
        "endpoint": "/api/v1/auth/me",
        "method": "GET",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {},
        "expected_response": {
          "user": {
            "id": 1,
            "name": "Name",
            "email": "user@example.com",
            "department": "string-or-null",
            "currency_code": "USD-or-null",
            "is_super_admin": false,
            "roles": [
              "pr-requester"
            ],
            "permissions": [
              "procurement-requests.view-own"
            ]
          }
        },
        "error_codes": {
          "401": "Missing or invalid token. Body: { \"message\": \"Unauthenticated.\" }"
        }
      },
      "logout": {
        "endpoint": "/api/v1/auth/logout",
        "method": "POST",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {},
        "expected_response": {
          "message": "Logged out."
        },
        "error_codes": {
          "401": "Missing or invalid token."
        }
      }
    },
    "apisspec_plan": "F01 done. Store token in memory or secure storage; send Authorization: Bearer on every later /api/v1 call. Blade /login session is a different system — do not mix cookies with this token. Super-admin gets the full PermissionCatalog list. Other users get role permission names (canonical). Frontend should hide/show screens from user.permissions. Next feature when asked: F02 Locations."
  }
}
```
