# F02 Locations (API v1)

Give **this file only** to frontend Cursor, plus Blade screenshots for **this feature**. Do not implement other feature IDs in the same pass.

Status: **ready**

Auth: after F01, every call uses Authorization Bearer token and Accept: application/json. Base: APP_URL/api/v1.

How to read the JSON: apisfront = endpoints/payloads; list_filters = query keys (same as Blade, not extra routes); apisspec_plan = UI rules.

```json
{
  "Locations": {
    "status": "ready",
    "id": "F02",
    "apisback": "Controllers: App\\Http\\Controllers\\Api\\V1\\Geo\\CountryController, CityController. Reuses StoreCountryRequest, UpdateCountryRequest, StoreCityRequest, UpdateCityRequest. Models Country/City unchanged (soft deletes, status active|inactive). Web Geo controllers and routes/web.php unchanged. No migrations. Same delete guards as Blade (country: has cities or vendor locations; city: vendor locations). name is always copied from name_en on store/update like web.",
    "apisfront": {
      "locations_index": {
        "endpoint": "/api/v1/locations",
        "method": "GET",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {
          "q": "optional search name_ar|name_en|iso_code",
          "status": "optional active|inactive",
          "per_page": 15,
          "sort_by": "optional name_ar|name_en|created_at",
          "sort_direction": "optional asc|desc"
        },
        "expected_response": {
          "data": [
            {
              "id": 1,
              "name_ar": "",
              "name_en": "",
              "name": "",
              "iso_code": "SY",
              "flag_emoji": "",
              "status": "active",
              "cities_count": 1,
              "used_in_vendor_locations": false,
              "cities": [
                {
                  "id": 1,
                  "country_id": 1,
                  "name_ar": "",
                  "name_en": "",
                  "name": "",
                  "status": "active",
                  "used_in_vendor_locations": false
                }
              ]
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
          "403": "Missing locations.view"
        }
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
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {},
        "expected_response": {
          "data": {
            "id": 1,
            "name_ar": "",
            "name_en": "",
            "iso_code": "SY",
            "status": "active",
            "cities": []
          }
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing locations.view",
          "404": "Not found"
        }
      },
      "countries_store": {
        "endpoint": "/api/v1/countries",
        "method": "POST",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}",
          "Content-Type": "application/json"
        },
        "request_payload": {
          "name_ar": "required",
          "name_en": "required",
          "iso_code": "optional unique max 8",
          "flag_emoji": "optional",
          "status": "active|inactive (defaults to active if omitted)"
        },
        "expected_response": {
          "data": {},
          "message": "Country created successfully."
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing locations.manage",
          "422": "Validation"
        }
      },
      "countries_update": {
        "endpoint": "/api/v1/countries/{id}",
        "method": "PUT",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {
          "name_ar": "required",
          "name_en": "required",
          "iso_code": "optional",
          "flag_emoji": "optional",
          "status": "required active|inactive"
        },
        "expected_response": {
          "data": {},
          "message": "Country updated successfully."
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing locations.manage",
          "404": "Not found",
          "422": "Validation"
        }
      },
      "countries_destroy": {
        "endpoint": "/api/v1/countries/{id}",
        "method": "DELETE",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {},
        "expected_response": {
          "message": "Country deleted successfully."
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing locations.manage",
          "404": "Not found",
          "422": "Has cities or used in vendor locations"
        }
      },
      "cities_index": {
        "endpoint": "/api/v1/cities",
        "method": "GET",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {
          "q": "optional",
          "country_id": "optional",
          "status": "optional active|inactive",
          "per_page": 15,
          "sort_by": "optional name_ar|name_en|created_at",
          "sort_direction": "optional asc|desc"
        },
        "expected_response": {
          "data": [
            {
              "id": 1,
              "country_id": 1,
              "name_ar": "",
              "name_en": "",
              "status": "active",
              "used_in_vendor_locations": false,
              "country": {
                "id": 1,
                "name_ar": "",
                "name_en": "",
                "iso_code": ""
              }
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
          "403": "Missing locations.manage (same as Blade cities index)"
        }
      },
      "cities_show": {
        "endpoint": "/api/v1/cities/{id}",
        "method": "GET",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {},
        "expected_response": {
          "data": {
            "id": 1,
            "country_id": 1,
            "name_ar": "",
            "name_en": "",
            "status": "active",
            "country": {}
          }
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing locations.manage",
          "404": "Not found"
        }
      },
      "cities_store": {
        "endpoint": "/api/v1/cities",
        "method": "POST",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}",
          "Content-Type": "application/json"
        },
        "request_payload": {
          "country_id": 1,
          "name_ar": "required unique per country",
          "name_en": "required unique per country",
          "status": "required active|inactive"
        },
        "expected_response": {
          "data": {},
          "message": "City created successfully."
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing locations.manage",
          "422": "Validation"
        }
      },
      "cities_update": {
        "endpoint": "/api/v1/cities/{id}",
        "method": "PUT",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {
          "country_id": 1,
          "name_ar": "required",
          "name_en": "required",
          "status": "required active|inactive"
        },
        "expected_response": {
          "data": {},
          "message": "City updated successfully."
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing locations.manage",
          "404": "Not found",
          "422": "Validation"
        }
      },
      "cities_destroy": {
        "endpoint": "/api/v1/cities/{id}",
        "method": "DELETE",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {},
        "expected_response": {
          "message": "City deleted successfully."
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing locations.manage",
          "404": "Not found",
          "422": "Used in vendor locations"
        }
      }
    },
    "apisspec_plan": "F02 done. Permissions match Blade: locations.view for GET /locations and GET /countries/{id}; locations.manage for country write and all city routes (Blade cities index is also manage). Status enum stays active|inactive. Do not send name; backend sets name = name_en. iso_code is uppercased. Soft-deleted rows are hidden. Hide delete in UI when used_in_vendor_locations is true, or country cities_count > 0. Nested cities on GET /locations is the Blade locations page. Next: F03 Projects or F04 Categories."
  }
}
```
