# F05 Users (API v1)

Give **this file only** to frontend Cursor, plus Blade screenshots for **this feature**. Do not implement other feature IDs in the same pass.

Status: **ready**

Auth: after F01, every call uses Authorization Bearer token and Accept: application/json. Base: APP_URL/api/v1.

How to read the JSON: apisfront = endpoints/payloads; list_filters = query keys (same as Blade, not extra routes); apisspec_plan = UI rules.

```json
{
  "Users": {
    "status": "ready",
    "id": "F05",
    "apisback": "Controller: App\\Http\\Controllers\\Api\\V1\\Access\\Users\\UserController. Reuses StoreUserRequest, UpdateUserRequest, User::syncRoles, UserDepartment, PermissionCatalog SUPER_ADMIN_ROLE. User model and web UserController + routes/web.php unchanged. No migrations. Destroy detaches roles then deletes (same as Blade). Cannot delete last super-admin or own account (JSON 422).",
    "apisfront": {
      "index": {
        "endpoint": "/api/v1/users",
        "method": "GET",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {
          "q": "optional search name|email|department",
          "role": "optional role name",
          "department": "optional department value",
          "per_page": 15,
          "sort_by": "optional name|email|department|created_at",
          "sort_direction": "optional asc|desc"
        },
        "expected_response": {
          "data": [
            {
              "id": 1,
              "name": "",
              "email": "",
              "department": "general",
              "department_label": "General",
              "currency_code": "USD",
              "daily_receipt_limit": 200,
              "is_super_admin": false,
              "roles": [
                {
                  "name": "pr-requester",
                  "label": "PR Requester"
                }
              ],
              "created_at": ""
            }
          ],
          "links": {},
          "meta": {},
          "roles": [
            {
              "name": "pr-requester",
              "label": "PR Requester"
            }
          ],
          "departments": [
            {
              "value": "general",
              "label": "General"
            }
          ]
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing users.view"
        }
      },
      "list_filters": {
        "users": {
          "endpoint": "/api/v1/users",
          "query": {
            "q": "optional LIKE name|email|department",
            "role": "optional roles.name",
            "department": "optional exact department key",
            "per_page": "optional 1-100 default 15",
            "sort_by": "optional name|email|department|created_at",
            "sort_direction": "optional asc|desc"
          }
        }
      },
      "show": {
        "endpoint": "/api/v1/users/{id}",
        "method": "GET",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {},
        "expected_response": {
          "data": {
            "id": 1,
            "name": "",
            "email": "",
            "department": "general",
            "department_label": "General",
            "currency_code": "USD",
            "daily_receipt_limit": 200,
            "is_super_admin": false,
            "roles": [],
            "created_at": ""
          },
          "roles": [],
          "departments": []
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing users.view",
          "404": "Not found"
        }
      },
      "store": {
        "endpoint": "/api/v1/users",
        "method": "POST",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}",
          "Content-Type": "application/json"
        },
        "request_payload": {
          "name": "required",
          "email": "required unique",
          "department": "required one of departments[].value",
          "currency_code": "optional ISO 4217 3 letters",
          "daily_receipt_limit": "optional number default 200",
          "password": "required min 8",
          "password_confirmation": "required match password",
          "roles": ["optional array of role names"]
        },
        "expected_response": {
          "data": {},
          "message": "User created successfully.",
          "roles": [],
          "departments": []
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing users.create",
          "422": "Validation"
        }
      },
      "update": {
        "endpoint": "/api/v1/users/{id}",
        "method": "PUT",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}",
          "Content-Type": "application/json"
        },
        "request_payload": {
          "name": "required",
          "email": "required unique except this user",
          "department": "required",
          "currency_code": "optional",
          "daily_receipt_limit": "optional",
          "password": "optional min 8; omit or empty to keep",
          "password_confirmation": "required if password sent",
          "roles": ["optional; omit or [] clears all roles like Blade"]
        },
        "expected_response": {
          "data": {},
          "message": "User updated successfully.",
          "roles": [],
          "departments": []
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing users.update",
          "404": "Not found",
          "422": "Validation"
        }
      },
      "destroy": {
        "endpoint": "/api/v1/users/{id}",
        "method": "DELETE",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {},
        "expected_response": {
          "message": "User deleted successfully."
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing users.delete",
          "404": "Not found",
          "422": "Last super-admin, or deleting own account"
        }
      }
    },
    "apisspec_plan": "F05 done. User update is PUT only (Blade edit form @method PUT; no PATCH). Permissions match Blade: users.view list/show; users.create store; users.update update; users.delete destroy. Create form dropdowns: use index.roles and index.departments (same keys as Blade). Department values are UserDepartment keys including 'Office Manager' with a space. Role checkboxes send roles[] as role name strings. Password required on create with confirmation; on update leave blank to keep. Default daily_receipt_limit is 200 if omitted. Do not send slug/id on create. Hide/show Add User from users.create. Next when asked: F06 Roles."
  }
}
```
