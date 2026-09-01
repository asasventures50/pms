# F03 Projects (API v1)

Give **this file only** to frontend Cursor, plus Blade screenshots for **this feature**. Do not implement other feature IDs in the same pass.

Status: **ready**

Auth: after F01, every call uses Authorization Bearer token and Accept: application/json. Base: APP_URL/api/v1.

How to read the JSON: apisfront = endpoints/payloads; list_filters = query keys (same as Blade, not extra routes); apisspec_plan = UI rules.

```json
{
  "Projects": {
    "status": "ready",
    "id": "F03",
    "apisback": "Controllers: App\\Http\\Controllers\\Api\\V1\\Procurement\\Projects\\ProjectController, ProjectQuickStoreController, ZoneQuickStoreController. Reuses StoreProjectRequest, UpdateProjectRequest, ProjectCatalogService, ProjectCodeGenerator, ZoneCodeGenerator. Models Project/Zone unchanged (soft deletes, status active|inactive). Web project controllers and routes/web.php unchanged. No migrations. Code is generated on create (do not send code). Destroy soft-deletes project and its zones via ProjectCatalogService::softDeleteProjectCascade. Quick-store JSON shape matches Blade (flat id/code/name, not Resource wrap).",
    "apisfront": {
      "index": {
        "endpoint": "/api/v1/projects",
        "method": "GET",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {
          "q": "optional search code|name",
          "status": "optional active|inactive",
          "per_page": 15,
          "sort_by": "optional code|name|created_at",
          "sort_direction": "optional asc|desc"
        },
        "expected_response": {
          "data": [
            {
              "id": 1,
              "code": "",
              "name": "",
              "status": "active",
              "zones_count": 0
            }
          ],
          "links": {},
          "meta": {},
          "statuses": [
            "active",
            "inactive"
          ]
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing projects.view"
        }
      },
      "list_filters": {
        "projects": {
          "endpoint": "/api/v1/projects",
          "query": {
            "q": "optional LIKE code|name",
            "status": "optional active|inactive",
            "per_page": "optional 1-100 default 15",
            "sort_by": "optional code|name|created_at",
            "sort_direction": "optional asc|desc"
          }
        }
      },
      "show": {
        "endpoint": "/api/v1/projects/{id}",
        "method": "GET",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {},
        "expected_response": {
          "data": {
            "id": 1,
            "code": "",
            "name": "",
            "status": "active",
            "zones_count": 1,
            "zones": [
              {
                "id": 1,
                "project_id": 1,
                "code": "",
                "name": "",
                "status": "active"
              }
            ]
          }
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing projects.view",
          "404": "Not found"
        }
      },
      "store": {
        "endpoint": "/api/v1/projects",
        "method": "POST",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}",
          "Content-Type": "application/json"
        },
        "request_payload": {
          "name": "required",
          "status": "required active|inactive",
          "zones": [
            {
              "name": "required",
              "status": "required active|inactive"
            }
          ]
        },
        "expected_response": {
          "data": {},
          "message": "Project created successfully."
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing projects.create",
          "422": "Validation"
        }
      },
      "update": {
        "endpoint": "/api/v1/projects/{id}",
        "method": "PUT",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {
          "name": "required",
          "status": "required active|inactive",
          "zones": [
            {
              "id": "optional existing zone id for this project",
              "name": "required",
              "status": "required active|inactive"
            }
          ]
        },
        "expected_response": {
          "data": {},
          "message": "Project updated successfully."
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing projects.update",
          "404": "Not found",
          "422": "Validation or zone id not owned by this project"
        }
      },
      "destroy": {
        "endpoint": "/api/v1/projects/{id}",
        "method": "DELETE",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {},
        "expected_response": {
          "message": "Project deleted successfully."
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing projects.update",
          "404": "Not found"
        }
      },
      "quick_store_project": {
        "endpoint": "/api/v1/projects/quick-store",
        "method": "POST",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}",
          "Content-Type": "application/json"
        },
        "request_payload": {
          "name": "required"
        },
        "expected_response": {
          "id": 1,
          "code": "",
          "name": ""
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing projects.create",
          "422": "Validation"
        }
      },
      "quick_store_zone": {
        "endpoint": "/api/v1/zones/quick-store",
        "method": "POST",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}",
          "Content-Type": "application/json"
        },
        "request_payload": {
          "project_id": 1,
          "name": "required"
        },
        "expected_response": {
          "id": 1,
          "code": "",
          "name": "",
          "project_id": 1
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing projects.update",
          "422": "Validation"
        }
      }
    },
    "apisspec_plan": "F03 done. Permissions match Blade: projects.view for list/show, projects.create for store and project quick-store, projects.update for update/destroy and zone quick-store. Status stays active|inactive. Do not send project or zone code — backend generates. Omitting zones or sending [] syncs to no zones (same as Blade). On update, zones omitted from the payload that still have an id are kept only if included; rows not in the array are soft-deleted (same syncZones as Blade). Soft-deleted projects/zones are hidden. Quick-store always creates status active. Next when asked: F04 Categories."
  }
}
```
