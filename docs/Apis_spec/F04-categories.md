# F04 Categories (API v1)

Give **this file only** to frontend Cursor, plus Blade screenshots for **this feature**. Do not implement other feature IDs in the same pass.

Status: **ready**

Auth: after F01, every call uses Authorization Bearer token and Accept: application/json. Base: APP_URL/api/v1.

How to read the JSON: apisfront = endpoints/payloads; list_filters = query keys (same as Blade, not extra routes); apisspec_plan = UI rules.

```json
{
  "Categories": {
    "status": "ready",
    "id": "F04",
    "apisback": "Controllers: App\\Http\\Controllers\\Api\\V1\\Procurement\\Categories\\CategoryController, CategoryQuickStoreController, SubcategoryQuickStoreController. Reuses StoreCategoryRequest, UpdateCategoryRequest, ReassignCategoryVendorLinkRequest, CategoryCatalogService, SubcategoryMoveService, CategoryVendorLinkService. Models Category/Subcategory unchanged (soft deletes, status active|inactive). Web CategoryController and routes/web.php unchanged. No migrations. Slug is generated from name_en (do not send slug). Destroy soft-deletes via CategoryCatalogService::softDeleteCategoryCascade (vendor links detached, vendors kept). Import/export/rebuild is F22.",
    "apisfront": {
      "index": {
        "endpoint": "/api/v1/categories",
        "method": "GET",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {
          "q": "optional search name_ar|name_en|slug",
          "status": "optional active|inactive",
          "per_page": 15,
          "sort_by": "optional name_ar|name_en|created_at",
          "sort_direction": "optional asc|desc"
        },
        "expected_response": {
          "data": [
            {
              "id": 1,
              "name_en": "",
              "name_ar": "",
              "slug": "",
              "status": "active",
              "subcategories_count": 0,
              "vendors_count": 0
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
          "403": "Missing categories.view"
        }
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
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {},
        "expected_response": {
          "data": {
            "id": 1,
            "name_en": "",
            "name_ar": "",
            "slug": "",
            "status": "active",
            "subcategories_count": 1,
            "category_only_vendor_count": 0,
            "subcategories": [
              {
                "id": 1,
                "category_id": 1,
                "name_en": "",
                "name_ar": "",
                "slug": "",
                "status": "active",
                "vendors_count": 0
              }
            ]
          },
          "all_categories": [
            {
              "id": 1,
              "name_en": "",
              "name_ar": ""
            }
          ]
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing categories.view",
          "404": "Not found"
        }
      },
      "store": {
        "endpoint": "/api/v1/categories",
        "method": "POST",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}",
          "Content-Type": "application/json"
        },
        "request_payload": {
          "name_en": "required",
          "name_ar": "required",
          "status": "required active|inactive",
          "subcategories": [
            {
              "name_en": "required",
              "name_ar": "required",
              "status": "required active|inactive"
            }
          ]
        },
        "expected_response": {
          "data": {},
          "message": "Category created successfully."
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing categories.create",
          "422": "Validation (unique name_en/slug, duplicate subcategory names in form)"
        }
      },
      "update": {
        "endpoint": "/api/v1/categories/{id}",
        "method": "PUT",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}",
          "Content-Type": "application/json"
        },
        "request_payload": {
          "name_en": "required",
          "name_ar": "required",
          "status": "required active|inactive",
          "subcategories": [
            {
              "id": "optional existing subcategory id for this category",
              "target_category_id": "optional other category id to move this row",
              "name_en": "required",
              "name_ar": "required",
              "status": "required active|inactive"
            }
          ]
        },
        "expected_response": {
          "data": {},
          "message": "Category updated successfully.",
          "all_categories": []
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing categories.update",
          "404": "Not found",
          "422": "Validation or subcategory id not owned by this category"
        }
      },
      "destroy": {
        "endpoint": "/api/v1/categories/{id}",
        "method": "DELETE",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {},
        "expected_response": {
          "message": "Category deleted successfully."
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing categories.update",
          "404": "Not found"
        }
      },
      "move_preview": {
        "endpoint": "/api/v1/categories/subcategories/{subcategory_id}/move-preview",
        "method": "GET",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {
          "target_category_id": "required query integer"
        },
        "expected_response": {
          "vendor_links": 0,
          "brochures": 0,
          "procurement_requests": 0,
          "has_name_conflict": false,
          "has_slug_conflict": false
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing categories.update",
          "404": "Not found",
          "422": "Missing target_category_id"
        }
      },
      "category_vendor_links": {
        "endpoint": "/api/v1/categories/{id}/vendor-links",
        "method": "GET",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {},
        "expected_response": {
          "category": {
            "id": 1,
            "name_en": "",
            "name_ar": ""
          },
          "subcategory": null,
          "vendor_links": [
            {
              "id": 1,
              "vendor_id": 1,
              "category_id": 1,
              "subcategory_id": null,
              "is_primary": false,
              "matching_brochures_count": 0,
              "vendor": {
                "id": 1,
                "name": "",
                "vendor_code": ""
              },
              "other_links_in_category": []
            }
          ],
          "catalog_categories": [],
          "subcategories_by_category": {}
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing categories.view",
          "404": "Not found"
        }
      },
      "subcategory_vendor_links": {
        "endpoint": "/api/v1/categories/{id}/subcategories/{subcategory_id}/vendor-links",
        "method": "GET",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {},
        "expected_response": {
          "category": {},
          "subcategory": {
            "id": 1,
            "name_en": "",
            "name_ar": ""
          },
          "vendor_links": [],
          "catalog_categories": [],
          "subcategories_by_category": {}
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing categories.view",
          "404": "Subcategory not under this category"
        }
      },
      "reassign_vendor_link": {
        "endpoint": "/api/v1/vendor-categories/{id}/reassign",
        "method": "PUT",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}",
          "Content-Type": "application/json"
        },
        "request_payload": {
          "target_category_id": 1,
          "target_subcategory_id": "optional null or id under target category",
          "update_brochures": false
        },
        "expected_response": {
          "message": "Vendor reassigned successfully. Procurement requests were not changed."
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing categories.update",
          "404": "Not found",
          "422": "Validation"
        }
      },
      "remove_vendor_link": {
        "endpoint": "/api/v1/vendor-categories/{id}",
        "method": "DELETE",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}",
          "Content-Type": "application/json"
        },
        "request_payload": {
          "update_brochures": false
        },
        "expected_response": {
          "message": "Vendor link removed from this classification. Procurement requests were not changed."
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing categories.update",
          "404": "Not found"
        }
      },
      "quick_store_category": {
        "endpoint": "/api/v1/categories/quick-store",
        "method": "POST",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}",
          "Content-Type": "application/json"
        },
        "request_payload": {
          "name_en": "required",
          "name_ar": "required"
        },
        "expected_response": {
          "id": 1,
          "name_ar": "",
          "name_en": "",
          "slug": ""
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing categories.create",
          "422": "Validation or generated slug exists"
        }
      },
      "quick_store_subcategory": {
        "endpoint": "/api/v1/subcategories/quick-store",
        "method": "POST",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}",
          "Content-Type": "application/json"
        },
        "request_payload": {
          "category_id": 1,
          "name_en": "required",
          "name_ar": "required"
        },
        "expected_response": {
          "id": 1,
          "name_ar": "",
          "name_en": ""
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing both categories.create and procurement-requests.create",
          "422": "Validation or duplicate name/slug in category"
        }
      }
    },
    "apisspec_plan": "F04 done. Category update is PUT only (Blade edit form @method PUT; no PATCH). Permissions match Blade: categories.view for list/show/vendor-links; categories.create for store and category quick-store; categories.update for update/destroy/move-preview/reassign/remove-link; subcategory quick-store allows categories.create OR procurement-requests.create. Status stays active|inactive. Do not send slug — backend slugs name_en. Omitting subcategories or sending [] on update syncs to no remaining rows (same as Blade: omitted ids are soft-deleted). To move a subcategory, send its id plus target_category_id of another category; call move-preview first if the UI needs impact counts. Empty subcategory name rows are dropped like Blade. Soft-deleted categories/subcategories are hidden. Quick-store always creates status active. show.all_categories is the parent-category picker for moves. Category-only vendor links (no subcategory) are GET .../vendor-links; subcategory links are the nested route. Reassign/remove never change procurement requests. Import/export/rebuild is F22. Next when asked: F05 Users or F07 Vendors."
  }
}
```
