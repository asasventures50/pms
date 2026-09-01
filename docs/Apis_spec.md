# Apis_spec

**Progress table:** [`docs/Apis_progress.md`](Apis_progress.md). This file is the contract; that file is the scoreboard.

Single file for backend + frontend. After each feature ships, **replace** that feature’s `"status": "pending"` block with a filled object.

Frontend: drop this whole file into Cursor. Use only features where `status` is `ready`.

---

## How to read a feature

Each feature is one JSON object:

- `apisback` — where it lives (v1 controllers, services reused, permissions, no schema changes).
- `apisfront` — endpoints, headers, payloads, responses, errors (copy-paste for the client).
- `list_filters` — **required on every ready feature that has a GET list**. Query-string keys only (not extra routes). Frontend uses this to render filters. Same keys as Blade. Postman folder `Helpers` in `pms.postman_collection.json` mirrors this 1:1.
- `apisspec_plan` — business rules the UI must know (own vs all, enums as-is, etc.).

Base URL: `{APP_URL}/api/v1`  
Auth header (after F01): `Authorization: Bearer {token}`  
`Accept: application/json`

List filters: send as `GET …?key=value`. Combine freely. `per_page` default 15, max 100. Empty/omitted key = no filter. There is no `/helpers` API.

### Postman

One collection only: [`docs/postman/pms.postman_collection.json`](postman/pms.postman_collection.json).

When a feature becomes `ready`, add its requests and Helpers into that file (keep existing folders). Do not create other JSON files under `docs/postman/`. Re-import `pms` in Postman after updates.

---

## Features

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
            "roles": ["pr-requester"],
            "permissions": ["procurement-requests.view-own"]
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
            "roles": ["pr-requester"],
            "permissions": ["procurement-requests.view-own"]
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
  },
  "Locations": {
    "status": "ready",
    "id": "F02",
    "apisback": "Controllers: App\\Http\\Controllers\\Api\\V1\\Geo\\CountryController, CityController. Reuses StoreCountryRequest, UpdateCountryRequest, StoreCityRequest, UpdateCityRequest. Models Country/City unchanged (soft deletes, status active|inactive). Web Geo controllers and routes/web.php unchanged. No migrations. Same delete guards as Blade (country: has cities or vendor locations; city: vendor locations). name is always copied from name_en on store/update like web.",
    "apisfront": {
      "locations_index": {
        "endpoint": "/api/v1/locations",
        "method": "GET",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": { "q": "optional search name_ar|name_en|iso_code", "status": "optional active|inactive", "per_page": 15, "sort_by": "optional name_ar|name_en|created_at", "sort_direction": "optional asc|desc" },
        "expected_response": { "data": [{ "id": 1, "name_ar": "", "name_en": "", "name": "", "iso_code": "SY", "flag_emoji": "", "status": "active", "cities_count": 1, "used_in_vendor_locations": false, "cities": [{ "id": 1, "country_id": 1, "name_ar": "", "name_en": "", "name": "", "status": "active", "used_in_vendor_locations": false }] }], "links": {}, "meta": {}, "statuses": ["active", "inactive"] },
        "error_codes": { "401": "Unauthenticated", "403": "Missing locations.view" }
      },
      "list_filters": {
        "locations": {
          "endpoint": "/api/v1/locations",
          "query": {
            "q": "optional LIKE name_ar|name_en|iso_code",
            "status": "optional active|inactive",
            "per_page": "optional 1-100 default 15",
            "sort_by": "optional name_ar|name_en|created_at",
            "sort_direction": "optional asc|desc"
          }
        },
        "cities": {
          "endpoint": "/api/v1/cities",
          "query": {
            "q": "optional LIKE name_ar|name_en",
            "country_id": "optional integer",
            "status": "optional active|inactive",
            "per_page": "optional 1-100 default 15",
            "sort_by": "optional name_ar|name_en|created_at",
            "sort_direction": "optional asc|desc"
          }
        }
      },
      "countries_show": {
        "endpoint": "/api/v1/countries/{id}",
        "method": "GET",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": {},
        "expected_response": { "data": { "id": 1, "name_ar": "", "name_en": "", "iso_code": "SY", "status": "active", "cities": [] } },
        "error_codes": { "401": "Unauthenticated", "403": "Missing locations.view", "404": "Not found" }
      },
      "countries_store": {
        "endpoint": "/api/v1/countries",
        "method": "POST",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}", "Content-Type": "application/json" },
        "request_payload": { "name_ar": "required", "name_en": "required", "iso_code": "optional unique max 8", "flag_emoji": "optional", "status": "active|inactive (defaults to active if omitted)" },
        "expected_response": { "data": {}, "message": "Country created successfully." },
        "error_codes": { "401": "Unauthenticated", "403": "Missing locations.manage", "422": "Validation" }
      },
      "countries_update": {
        "endpoint": "/api/v1/countries/{id}",
        "method": "PUT",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": { "name_ar": "required", "name_en": "required", "iso_code": "optional", "flag_emoji": "optional", "status": "required active|inactive" },
        "expected_response": { "data": {}, "message": "Country updated successfully." },
        "error_codes": { "401": "Unauthenticated", "403": "Missing locations.manage", "404": "Not found", "422": "Validation" }
      },
      "countries_destroy": {
        "endpoint": "/api/v1/countries/{id}",
        "method": "DELETE",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": {},
        "expected_response": { "message": "Country deleted successfully." },
        "error_codes": { "401": "Unauthenticated", "403": "Missing locations.manage", "404": "Not found", "422": "Has cities or used in vendor locations" }
      },
      "cities_index": {
        "endpoint": "/api/v1/cities",
        "method": "GET",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": { "q": "optional", "country_id": "optional", "status": "optional active|inactive", "per_page": 15, "sort_by": "optional name_ar|name_en|created_at", "sort_direction": "optional asc|desc" },
        "expected_response": { "data": [{ "id": 1, "country_id": 1, "name_ar": "", "name_en": "", "status": "active", "used_in_vendor_locations": false, "country": { "id": 1, "name_ar": "", "name_en": "", "iso_code": "" } }], "links": {}, "meta": {}, "statuses": ["active", "inactive"] },
        "error_codes": { "401": "Unauthenticated", "403": "Missing locations.manage (same as Blade cities index)" }
      },
      "cities_show": {
        "endpoint": "/api/v1/cities/{id}",
        "method": "GET",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": {},
        "expected_response": { "data": { "id": 1, "country_id": 1, "name_ar": "", "name_en": "", "status": "active", "country": {} } },
        "error_codes": { "401": "Unauthenticated", "403": "Missing locations.manage", "404": "Not found" }
      },
      "cities_store": {
        "endpoint": "/api/v1/cities",
        "method": "POST",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}", "Content-Type": "application/json" },
        "request_payload": { "country_id": 1, "name_ar": "required unique per country", "name_en": "required unique per country", "status": "required active|inactive" },
        "expected_response": { "data": {}, "message": "City created successfully." },
        "error_codes": { "401": "Unauthenticated", "403": "Missing locations.manage", "422": "Validation" }
      },
      "cities_update": {
        "endpoint": "/api/v1/cities/{id}",
        "method": "PUT",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": { "country_id": 1, "name_ar": "required", "name_en": "required", "status": "required active|inactive" },
        "expected_response": { "data": {}, "message": "City updated successfully." },
        "error_codes": { "401": "Unauthenticated", "403": "Missing locations.manage", "404": "Not found", "422": "Validation" }
      },
      "cities_destroy": {
        "endpoint": "/api/v1/cities/{id}",
        "method": "DELETE",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": {},
        "expected_response": { "message": "City deleted successfully." },
        "error_codes": { "401": "Unauthenticated", "403": "Missing locations.manage", "404": "Not found", "422": "Used in vendor locations" }
      }
    },
    "apisspec_plan": "F02 done. Permissions match Blade: locations.view for GET /locations and GET /countries/{id}; locations.manage for country write and all city routes (Blade cities index is also manage). Status enum stays active|inactive. Do not send name; backend sets name = name_en. iso_code is uppercased. Soft-deleted rows are hidden. Hide delete in UI when used_in_vendor_locations is true, or country cities_count > 0. Nested cities on GET /locations is the Blade locations page. Next: F03 Projects or F04 Categories."
  },
  "Projects": {
    "status": "ready",
    "id": "F03",
    "apisback": "Controllers: App\\Http\\Controllers\\Api\\V1\\Procurement\\Projects\\ProjectController, ProjectQuickStoreController, ZoneQuickStoreController. Reuses StoreProjectRequest, UpdateProjectRequest, ProjectCatalogService, ProjectCodeGenerator, ZoneCodeGenerator. Models Project/Zone unchanged (soft deletes, status active|inactive). Web project controllers and routes/web.php unchanged. No migrations. Code is generated on create (do not send code). Destroy soft-deletes project and its zones via ProjectCatalogService::softDeleteProjectCascade. Quick-store JSON shape matches Blade (flat id/code/name, not Resource wrap).",
    "apisfront": {
      "index": {
        "endpoint": "/api/v1/projects",
        "method": "GET",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": { "q": "optional search code|name", "status": "optional active|inactive", "per_page": 15, "sort_by": "optional code|name|created_at", "sort_direction": "optional asc|desc" },
        "expected_response": { "data": [{ "id": 1, "code": "", "name": "", "status": "active", "zones_count": 0 }], "links": {}, "meta": {}, "statuses": ["active", "inactive"] },
        "error_codes": { "401": "Unauthenticated", "403": "Missing projects.view" }
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
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": {},
        "expected_response": { "data": { "id": 1, "code": "", "name": "", "status": "active", "zones_count": 1, "zones": [{ "id": 1, "project_id": 1, "code": "", "name": "", "status": "active" }] } },
        "error_codes": { "401": "Unauthenticated", "403": "Missing projects.view", "404": "Not found" }
      },
      "store": {
        "endpoint": "/api/v1/projects",
        "method": "POST",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}", "Content-Type": "application/json" },
        "request_payload": { "name": "required", "status": "required active|inactive", "zones": [{ "name": "required", "status": "required active|inactive" }] },
        "expected_response": { "data": {}, "message": "Project created successfully." },
        "error_codes": { "401": "Unauthenticated", "403": "Missing projects.create", "422": "Validation" }
      },
      "update": {
        "endpoint": "/api/v1/projects/{id}",
        "method": "PUT",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": { "name": "required", "status": "required active|inactive", "zones": [{ "id": "optional existing zone id for this project", "name": "required", "status": "required active|inactive" }] },
        "expected_response": { "data": {}, "message": "Project updated successfully." },
        "error_codes": { "401": "Unauthenticated", "403": "Missing projects.update", "404": "Not found", "422": "Validation or zone id not owned by this project" }
      },
      "destroy": {
        "endpoint": "/api/v1/projects/{id}",
        "method": "DELETE",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": {},
        "expected_response": { "message": "Project deleted successfully." },
        "error_codes": { "401": "Unauthenticated", "403": "Missing projects.update", "404": "Not found" }
      },
      "quick_store_project": {
        "endpoint": "/api/v1/projects/quick-store",
        "method": "POST",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}", "Content-Type": "application/json" },
        "request_payload": { "name": "required" },
        "expected_response": { "id": 1, "code": "", "name": "" },
        "error_codes": { "401": "Unauthenticated", "403": "Missing projects.create", "422": "Validation" }
      },
      "quick_store_zone": {
        "endpoint": "/api/v1/zones/quick-store",
        "method": "POST",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}", "Content-Type": "application/json" },
        "request_payload": { "project_id": 1, "name": "required" },
        "expected_response": { "id": 1, "code": "", "name": "", "project_id": 1 },
        "error_codes": { "401": "Unauthenticated", "403": "Missing projects.update", "422": "Validation" }
      }
    },
    "apisspec_plan": "F03 done. Permissions match Blade: projects.view for list/show, projects.create for store and project quick-store, projects.update for update/destroy and zone quick-store. Status stays active|inactive. Do not send project or zone code — backend generates. Omitting zones or sending [] syncs to no zones (same as Blade). On update, zones omitted from the payload that still have an id are kept only if included; rows not in the array are soft-deleted (same syncZones as Blade). Soft-deleted projects/zones are hidden. Quick-store always creates status active. Next when asked: F04 Categories."
  },
  "Categories": {
    "status": "ready",
    "id": "F04",
    "apisback": "Controllers: App\\Http\\Controllers\\Api\\V1\\Procurement\\Categories\\CategoryController, CategoryQuickStoreController, SubcategoryQuickStoreController. Reuses StoreCategoryRequest, UpdateCategoryRequest, ReassignCategoryVendorLinkRequest, CategoryCatalogService, SubcategoryMoveService, CategoryVendorLinkService. Models Category/Subcategory unchanged (soft deletes, status active|inactive). Web CategoryController and routes/web.php unchanged. No migrations. Slug is generated from name_en (do not send slug). Destroy soft-deletes via CategoryCatalogService::softDeleteCategoryCascade (vendor links detached, vendors kept). Import/export/rebuild is F22.",
    "apisfront": {
      "index": {
        "endpoint": "/api/v1/categories",
        "method": "GET",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": { "q": "optional search name_ar|name_en|slug", "status": "optional active|inactive", "per_page": 15, "sort_by": "optional name_ar|name_en|created_at", "sort_direction": "optional asc|desc" },
        "expected_response": { "data": [{ "id": 1, "name_en": "", "name_ar": "", "slug": "", "status": "active", "subcategories_count": 0, "vendors_count": 0 }], "links": {}, "meta": {}, "statuses": ["active", "inactive"] },
        "error_codes": { "401": "Unauthenticated", "403": "Missing categories.view" }
      },
      "list_filters": {
        "categories": {
          "endpoint": "/api/v1/categories",
          "query": {
            "q": "optional LIKE name_ar|name_en|slug",
            "status": "optional active|inactive",
            "per_page": "optional 1-100 default 15",
            "sort_by": "optional name_ar|name_en|created_at",
            "sort_direction": "optional asc|desc"
          }
        }
      },
      "show": {
        "endpoint": "/api/v1/categories/{id}",
        "method": "GET",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": {},
        "expected_response": { "data": { "id": 1, "name_en": "", "name_ar": "", "slug": "", "status": "active", "subcategories_count": 1, "category_only_vendor_count": 0, "subcategories": [{ "id": 1, "category_id": 1, "name_en": "", "name_ar": "", "slug": "", "status": "active", "vendors_count": 0 }] }, "all_categories": [{ "id": 1, "name_en": "", "name_ar": "" }] },
        "error_codes": { "401": "Unauthenticated", "403": "Missing categories.view", "404": "Not found" }
      },
      "store": {
        "endpoint": "/api/v1/categories",
        "method": "POST",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}", "Content-Type": "application/json" },
        "request_payload": { "name_en": "required", "name_ar": "required", "status": "required active|inactive", "subcategories": [{ "name_en": "required", "name_ar": "required", "status": "required active|inactive" }] },
        "expected_response": { "data": {}, "message": "Category created successfully." },
        "error_codes": { "401": "Unauthenticated", "403": "Missing categories.create", "422": "Validation (unique name_en/slug, duplicate subcategory names in form)" }
      },
      "update": {
        "endpoint": "/api/v1/categories/{id}",
        "method": "PUT",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}", "Content-Type": "application/json" },
        "request_payload": { "name_en": "required", "name_ar": "required", "status": "required active|inactive", "subcategories": [{ "id": "optional existing subcategory id for this category", "target_category_id": "optional other category id to move this row", "name_en": "required", "name_ar": "required", "status": "required active|inactive" }] },
        "expected_response": { "data": {}, "message": "Category updated successfully.", "all_categories": [] },
        "error_codes": { "401": "Unauthenticated", "403": "Missing categories.update", "404": "Not found", "422": "Validation or subcategory id not owned by this category" }
      },
      "destroy": {
        "endpoint": "/api/v1/categories/{id}",
        "method": "DELETE",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": {},
        "expected_response": { "message": "Category deleted successfully." },
        "error_codes": { "401": "Unauthenticated", "403": "Missing categories.update", "404": "Not found" }
      },
      "move_preview": {
        "endpoint": "/api/v1/categories/subcategories/{subcategory_id}/move-preview",
        "method": "GET",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": { "target_category_id": "required query integer" },
        "expected_response": { "vendor_links": 0, "brochures": 0, "procurement_requests": 0, "has_name_conflict": false, "has_slug_conflict": false },
        "error_codes": { "401": "Unauthenticated", "403": "Missing categories.update", "404": "Not found", "422": "Missing target_category_id" }
      },
      "category_vendor_links": {
        "endpoint": "/api/v1/categories/{id}/vendor-links",
        "method": "GET",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": {},
        "expected_response": { "category": { "id": 1, "name_en": "", "name_ar": "" }, "subcategory": null, "vendor_links": [{ "id": 1, "vendor_id": 1, "category_id": 1, "subcategory_id": null, "is_primary": false, "matching_brochures_count": 0, "vendor": { "id": 1, "name": "", "vendor_code": "" }, "other_links_in_category": [] }], "catalog_categories": [], "subcategories_by_category": {} },
        "error_codes": { "401": "Unauthenticated", "403": "Missing categories.view", "404": "Not found" }
      },
      "subcategory_vendor_links": {
        "endpoint": "/api/v1/categories/{id}/subcategories/{subcategory_id}/vendor-links",
        "method": "GET",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": {},
        "expected_response": { "category": {}, "subcategory": { "id": 1, "name_en": "", "name_ar": "" }, "vendor_links": [], "catalog_categories": [], "subcategories_by_category": {} },
        "error_codes": { "401": "Unauthenticated", "403": "Missing categories.view", "404": "Subcategory not under this category" }
      },
      "reassign_vendor_link": {
        "endpoint": "/api/v1/vendor-categories/{id}/reassign",
        "method": "PUT",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}", "Content-Type": "application/json" },
        "request_payload": { "target_category_id": 1, "target_subcategory_id": "optional null or id under target category", "update_brochures": false },
        "expected_response": { "message": "Vendor reassigned successfully. Procurement requests were not changed." },
        "error_codes": { "401": "Unauthenticated", "403": "Missing categories.update", "404": "Not found", "422": "Validation" }
      },
      "remove_vendor_link": {
        "endpoint": "/api/v1/vendor-categories/{id}",
        "method": "DELETE",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}", "Content-Type": "application/json" },
        "request_payload": { "update_brochures": false },
        "expected_response": { "message": "Vendor link removed from this classification. Procurement requests were not changed." },
        "error_codes": { "401": "Unauthenticated", "403": "Missing categories.update", "404": "Not found" }
      },
      "quick_store_category": {
        "endpoint": "/api/v1/categories/quick-store",
        "method": "POST",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}", "Content-Type": "application/json" },
        "request_payload": { "name_en": "required", "name_ar": "required" },
        "expected_response": { "id": 1, "name_ar": "", "name_en": "", "slug": "" },
        "error_codes": { "401": "Unauthenticated", "403": "Missing categories.create", "422": "Validation or generated slug exists" }
      },
      "quick_store_subcategory": {
        "endpoint": "/api/v1/subcategories/quick-store",
        "method": "POST",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}", "Content-Type": "application/json" },
        "request_payload": { "category_id": 1, "name_en": "required", "name_ar": "required" },
        "expected_response": { "id": 1, "name_ar": "", "name_en": "" },
        "error_codes": { "401": "Unauthenticated", "403": "Missing both categories.create and procurement-requests.create", "422": "Validation or duplicate name/slug in category" }
      }
    },
    "apisspec_plan": "F04 done. Category update is PUT only (Blade edit form @method PUT; no PATCH). Permissions match Blade: categories.view for list/show/vendor-links; categories.create for store and category quick-store; categories.update for update/destroy/move-preview/reassign/remove-link; subcategory quick-store allows categories.create OR procurement-requests.create. Status stays active|inactive. Do not send slug — backend slugs name_en. Omitting subcategories or sending [] on update syncs to no remaining rows (same as Blade: omitted ids are soft-deleted). To move a subcategory, send its id plus target_category_id of another category; call move-preview first if the UI needs impact counts. Empty subcategory name rows are dropped like Blade. Soft-deleted categories/subcategories are hidden. Quick-store always creates status active. show.all_categories is the parent-category picker for moves. Category-only vendor links (no subcategory) are GET .../vendor-links; subcategory links are the nested route. Reassign/remove never change procurement requests. Import/export/rebuild is F22. Next when asked: F05 Users or F07 Vendors."
  },
  "Users": {
    "status": "pending",
    "id": "F05",
    "apisback": "Pending.",
    "apisfront": {},
    "apisspec_plan": "Access users. Same users.* permissions."
  },
  "Roles": {
    "status": "pending",
    "id": "F06",
    "apisback": "Pending.",
    "apisfront": {},
    "apisspec_plan": "Roles + PermissionCatalog names only. Do not invent permissions."
  },
  "Vendors": {
    "status": "pending",
    "id": "F07",
    "apisback": "Pending.",
    "apisfront": {},
    "apisspec_plan": "Vendor CRUD + select search. Keep existing vendor enums (status, company type, etc.). Import is F23."
  },
  "ProcurementRequests": {
    "status": "ready",
    "id": "F08",
    "apisback": "Controllers: App\\Http\\Controllers\\Api\\V1\\Procurement\\ProcurementRequests\\ProcurementRequestController. Reuses StoreProcurementRequestRequest, UpdateProcurementRequestRequest, ProcurementRequestPersistenceService, RequestorResolver, PayloadResolver, FormDataResolver, SupportingDocumentStorage, RelatedRfqsForProcurementRequestQuery. Same view-own scope as Blade (created_by). Same destroy: forceDelete + purge files. Web ProcurementRequestController unchanged. No migrations. Enums unchanged: ProcurementRequestStatus draft|submitted|received|closed|cancelled, PrCompany, ProcurementType, GeographicScope, etc.",
    "apisfront": {
      "index": {
        "endpoint": "/api/v1/procurement-requests",
        "method": "GET",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": { "q": "optional combined search (legacy)", "request_number": "optional", "requestor": "optional name or creator", "department": "optional", "requested_at": "optional Y-m-d", "delivery_date": "optional Y-m-d on any item", "status": "optional draft|submitted|received|closed|cancelled", "per_page": 15 },
        "expected_response": { "data": [{ "id": 1, "request_number": "PR-...", "status": "draft", "requestor_name": "", "creator": { "id": 1, "name": "" }, "project": { "id": 1, "code": "", "name": "" } }], "links": {}, "meta": {}, "statuses": ["draft", "submitted", "received", "closed", "cancelled"] },
        "error_codes": { "401": "Unauthenticated", "403": "No view or view-own permission" }
      },
      "list_filters": {
        "procurement_requests": {
          "endpoint": "/api/v1/procurement-requests",
          "query": {
            "q": "optional combined LIKE request_number|requestor_name|requestor_department|creator.name",
            "request_number": "optional",
            "requestor": "optional requestor_name or creator.name",
            "department": "optional requestor_department",
            "requested_at": "optional Y-m-d (Date column)",
            "delivery_date": "optional Y-m-d on any item",
            "status": "optional draft|submitted|received|closed|cancelled",
            "per_page": "optional 1-100 default 15"
          }
        }
      },
      "show": {
        "endpoint": "/api/v1/procurement-requests/{id}",
        "method": "GET",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": {},
        "expected_response": { "procurement_request": { "id": 1, "request_number": "PR-...", "status": "draft", "items": [], "payment_terms": [], "retentions": [], "timeline_entries": [], "approvals": [], "header_documents": [], "form": {}, "related_rfqs": [] } },
        "error_codes": { "401": "Unauthenticated", "403": "Cannot view this PR (view-own mismatch)", "404": "Not found" }
      },
      "store": {
        "endpoint": "/api/v1/procurement-requests",
        "method": "POST",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}", "Content-Type": "application/json or multipart/form-data" },
        "request_payload": {
          "company_key": "asas_ventures",
          "project_id": 1,
          "geographic_scopes": ["local"],
          "delivery_location": "required",
          "scope_of_work": "required",
          "procurement_types": ["purchase"],
          "flexible_delivery_date": true,
          "delivery_lead_time_days": "required integer if flexible_delivery_date is false or omitted",
          "items": [{ "category_id": 1, "description": "required", "quantity": 1, "zone_id": null, "subcategory_id": null, "unit": "pcs", "unit_price": 0 }],
          "payment_terms": [],
          "retentions": [],
          "timeline": [{ "activity": "existing-enum-value", "duration_days": 1 }],
          "approvals": [{ "role": "existing-enum-value", "name": "", "signature": "", "signed_at": null }],
          "supporting_document_rows": "optional multipart files/urls"
        },
        "expected_response": { "message": "Procurement request created successfully.", "procurement_request": {} },
        "error_codes": { "401": "Unauthenticated", "403": "Missing procurement-requests.create", "422": "Validation (same rules as Blade form)" }
      },
      "update": {
        "endpoint": "/api/v1/procurement-requests/{id}",
        "method": "PUT",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": "Same fields as store plus items.*.id, payment_terms.*.id, retentions.*.id, remove_supporting_document_ids: [1]",
        "expected_response": { "message": "Procurement request updated successfully.", "procurement_request": {} },
        "error_codes": { "401": "Unauthenticated", "403": "Missing procurement-requests.update", "422": "Validation", "404": "Not found" }
      },
      "destroy": {
        "endpoint": "/api/v1/procurement-requests/{id}",
        "method": "DELETE",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": {},
        "expected_response": { "message": "Procurement request deleted permanently." },
        "error_codes": { "401": "Unauthenticated", "403": "Missing procurement-requests.update", "404": "Not found" }
      }
    },
    "apisspec_plan": "F08. Same permissions as web. List filters match Blade columns: request_number, requestor, department, requested_at, delivery_date, status, plus per_page. q still works as combined search. If flexible_delivery_date is omitted it is treated as false (same Form Request as Blade) and delivery_lead_time_days becomes required. Send flexible_delivery_date: true to match the Blade create default, or send a lead time. view-own users only see their created_by PRs. Status values must stay draft|submitted|received|closed|cancelled. company_key: asas_ventures|qassioun_journey|activation. store/update payload matches Blade PR form (Form Requests reused). request_number optional on create — backend generates. Create needs existing project_id and category_id (from Blade or later F03/F04). show.form is the same shape Blade edit uses. Request Tracking page is F09 (not this feature). Print is F24."
  },
  "ProcurementRequestFlow": {
    "status": "ready",
    "id": "F09",
    "apisback": "Controller: App\\Http\\Controllers\\Api\\V1\\Procurement\\ProcurementRequests\\ProcurementRequestFlowController. Reuses ProcurementRequestFlowBuilder, FlowStageKey, FlowStageState, User canAccessProcurementRequestFlow / canViewAllProcurementRequestFlows / scopesProcurementRequestFlowToOwn. Web ProcurementRequestFlowController and routes/web.php unchanged. No migrations. Same pipeline PR → RFQ → Quotations → Selection → PO → Invoice.",
    "apisfront": {
      "index": {
        "endpoint": "/api/v1/my-procurement-requests/flow",
        "method": "GET",
        "headers": { "Accept": "application/json", "Authorization": "Bearer {token}" },
        "request_payload": { "per_page": "optional 1-50 default 15 (Blade max is 50, not 100)" },
        "expected_response": {
          "data": [{
            "id": 1,
            "request_number": "PR-...",
            "status": "draft",
            "requested_at": "2026-08-31",
            "project": { "id": 1, "code": "", "name": "" },
            "creator": { "id": 1, "name": "only when view_all is true" },
            "flow": {
              "active_stage": "pr",
              "status_summary": "Purchase request · Draft",
              "rfq_count": 0,
              "quotation_count": 0,
              "po_count": 0,
              "invoice_count": 0,
              "has_selection": false,
              "stages": [{ "key": "pr", "state": "active", "label": "PR", "badge": null, "badge_label": null, "detail": "Draft" }],
              "rfqs": [{ "id": 1, "rfq_number": "", "selected_vendor_quotation_id": null }],
              "purchase_orders": [{ "id": 1, "po_number": "", "status": "" }],
              "invoices": [{ "id": 1, "invoice_number": "", "source": "" }]
            }
          }],
          "links": {},
          "meta": {},
          "view_all": true,
          "stage_keys": ["pr", "rfq", "quotations", "selection", "po", "invoice"]
        },
        "error_codes": { "401": "Unauthenticated", "403": "Cannot access request tracking" }
      },
      "list_filters": {
        "flow": {
          "endpoint": "/api/v1/my-procurement-requests/flow",
          "query": {
            "per_page": "optional 1-50 default 15"
          }
        }
      }
    },
    "apisspec_plan": "F09 is the Blade Request Tracking page (/my-procurement-requests/flow), not PR CRUD (F08). Same access as Blade: middleware any of procurement-requests.view|view-own|create|rfqs.view, then User::canAccessProcurementRequestFlow(). view_all true means rfqs.view or super-admin or can view all PRs — those users see every PR and creator on each row. Otherwise only created_by = current user (view-own or create). Stage keys stay pr|rfq|quotations|selection|po|invoice. Stage state stays completed|active|pending|cancelled. Draw the pipeline from data[].flow.stages in that order (or stage_keys). status_summary is the Blade amber line under the request number. RFQ/PO/Invoice nested lists are summaries for this screen; full CRUD is later features. Blade has no search/status filters on this page — only per_page."
  },
  "ScheduleOfWorks": {
    "status": "pending",
    "id": "F10",
    "apisback": "Pending.",
    "apisfront": {},
    "apisspec_plan": "Schedule of works tied to PRs."
  },
  "RfqTerms": {
    "status": "pending",
    "id": "F11",
    "apisback": "Pending.",
    "apisfront": {},
    "apisspec_plan": "RFQ general terms."
  },
  "Rfqs": {
    "status": "pending",
    "id": "F12",
    "apisback": "Pending.",
    "apisfront": {},
    "apisspec_plan": "RFQ CRUD. Existing RfqStatus only."
  },
  "QuotationInvitesAndPublicQuote": {
    "status": "pending",
    "id": "F13",
    "apisback": "Pending.",
    "apisfront": {},
    "apisspec_plan": "Invite links + public quotation by token. Throttle like web. No Bearer on public routes."
  },
  "VendorQuotations": {
    "status": "pending",
    "id": "F14",
    "apisback": "Pending.",
    "apisfront": {},
    "apisspec_plan": "Staff-side quotations under RFQs."
  },
  "QuotationComparison": {
    "status": "pending",
    "id": "F15",
    "apisback": "Pending.",
    "apisfront": {},
    "apisspec_plan": "Compare + select / clear selection. quotation-comparison.* permissions."
  },
  "PurchaseOrders": {
    "status": "pending",
    "id": "F16",
    "apisback": "Pending.",
    "apisfront": {},
    "apisspec_plan": "PO CRUD. view vs view-own. Existing PO / payment status enums."
  },
  "Invoices": {
    "status": "pending",
    "id": "F17",
    "apisback": "Pending.",
    "apisfront": {},
    "apisspec_plan": "Invoices. Signed document upload is files (F24) if not included here."
  },
  "QuickReceipts": {
    "status": "pending",
    "id": "F18",
    "apisback": "Pending.",
    "apisfront": {},
    "apisspec_plan": "Quick receipts + approve/reject/sign. Existing QuickReceiptStatus only."
  },
  "ActivityLogs": {
    "status": "pending",
    "id": "F19",
    "apisback": "Pending.",
    "apisfront": {},
    "apisspec_plan": "Activity log list/show/report."
  },
  "Dashboard": {
    "status": "pending",
    "id": "F20",
    "apisback": "Pending.",
    "apisfront": {},
    "apisspec_plan": "Same numbers as Blade dashboard. Do not invent new KPIs."
  },
  "PublicVendorRegistration": {
    "status": "pending",
    "id": "F21",
    "apisback": "Pending.",
    "apisfront": {},
    "apisspec_plan": "Public form API. Throttle. Existing vendor enums."
  },
  "CategoryImportExport": {
    "status": "pending",
    "id": "F22",
    "apisback": "Pending.",
    "apisfront": {},
    "apisspec_plan": "Import/export/rebuild. No data rebuild except what web already does."
  },
  "VendorImportExport": {
    "status": "pending",
    "id": "F23",
    "apisback": "Pending.",
    "apisfront": {},
    "apisspec_plan": "Vendor import/export/duplicates."
  },
  "DownloadsPrintsAttachments": {
    "status": "pending",
    "id": "F24",
    "apisback": "Pending.",
    "apisfront": {},
    "apisspec_plan": "Print/export/signed files. Last."
  }
}
```
