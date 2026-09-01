# F08 ProcurementRequests (API v1)

Give **this file only** to frontend Cursor, plus Blade screenshots for **this feature**. Do not implement other feature IDs in the same pass.

Status: **ready**

Auth: after F01, every call uses Authorization Bearer token and Accept: application/json. Base: APP_URL/api/v1.

How to read the JSON: apisfront = endpoints/payloads; list_filters = query keys (same as Blade, not extra routes); apisspec_plan = UI rules.

```json
{
  "ProcurementRequests": {
    "status": "ready",
    "id": "F08",
    "apisback": "Controllers: App\\Http\\Controllers\\Api\\V1\\Procurement\\ProcurementRequests\\ProcurementRequestController. Reuses StoreProcurementRequestRequest, UpdateProcurementRequestRequest, ProcurementRequestPersistenceService, RequestorResolver, PayloadResolver, FormDataResolver, SupportingDocumentStorage, RelatedRfqsForProcurementRequestQuery. Same view-own scope as Blade (created_by). Same destroy: forceDelete + purge files. Web ProcurementRequestController unchanged. No migrations. Enums unchanged: ProcurementRequestStatus draft|submitted|received|closed|cancelled, PrCompany, ProcurementType, GeographicScope, etc.",
    "apisfront": {
      "index": {
        "endpoint": "/api/v1/procurement-requests",
        "method": "GET",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {
          "q": "optional combined search (legacy)",
          "request_number": "optional",
          "requestor": "optional name or creator",
          "department": "optional",
          "requested_at": "optional Y-m-d",
          "delivery_date": "optional Y-m-d on any item",
          "status": "optional draft|submitted|received|closed|cancelled",
          "per_page": 15
        },
        "expected_response": {
          "data": [
            {
              "id": 1,
              "request_number": "PR-...",
              "status": "draft",
              "requestor_name": "",
              "creator": {
                "id": 1,
                "name": ""
              },
              "project": {
                "id": 1,
                "code": "",
                "name": ""
              }
            }
          ],
          "links": {},
          "meta": {},
          "statuses": [
            "draft",
            "submitted",
            "received",
            "closed",
            "cancelled"
          ]
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "No view or view-own permission"
        }
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
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {},
        "expected_response": {
          "procurement_request": {
            "id": 1,
            "request_number": "PR-...",
            "status": "draft",
            "items": [],
            "payment_terms": [],
            "retentions": [],
            "timeline_entries": [],
            "approvals": [],
            "header_documents": [],
            "form": {},
            "related_rfqs": []
          }
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Cannot view this PR (view-own mismatch)",
          "404": "Not found"
        }
      },
      "store": {
        "endpoint": "/api/v1/procurement-requests",
        "method": "POST",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}",
          "Content-Type": "application/json or multipart/form-data"
        },
        "request_payload": {
          "company_key": "asas_ventures",
          "project_id": 1,
          "geographic_scopes": [
            "local"
          ],
          "delivery_location": "required",
          "scope_of_work": "required",
          "procurement_types": [
            "purchase"
          ],
          "flexible_delivery_date": true,
          "delivery_lead_time_days": "required integer if flexible_delivery_date is false or omitted",
          "items": [
            {
              "category_id": 1,
              "description": "required",
              "quantity": 1,
              "zone_id": null,
              "subcategory_id": null,
              "unit": "pcs",
              "unit_price": 0
            }
          ],
          "payment_terms": [],
          "retentions": [],
          "timeline": [
            {
              "activity": "existing-enum-value",
              "duration_days": 1
            }
          ],
          "approvals": [
            {
              "role": "existing-enum-value",
              "name": "",
              "signature": "",
              "signed_at": null
            }
          ],
          "supporting_document_rows": "optional multipart files/urls"
        },
        "expected_response": {
          "message": "Procurement request created successfully.",
          "procurement_request": {}
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing procurement-requests.create",
          "422": "Validation (same rules as Blade form)"
        }
      },
      "update": {
        "endpoint": "/api/v1/procurement-requests/{id}",
        "method": "PUT",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": "Same fields as store plus items.*.id, payment_terms.*.id, retentions.*.id, remove_supporting_document_ids: [1]",
        "expected_response": {
          "message": "Procurement request updated successfully.",
          "procurement_request": {}
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing procurement-requests.update",
          "422": "Validation",
          "404": "Not found"
        }
      },
      "destroy": {
        "endpoint": "/api/v1/procurement-requests/{id}",
        "method": "DELETE",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {},
        "expected_response": {
          "message": "Procurement request deleted permanently."
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Missing procurement-requests.update",
          "404": "Not found"
        }
      }
    },
    "apisspec_plan": "F08. Same permissions as web. List filters match Blade columns: request_number, requestor, department, requested_at, delivery_date, status, plus per_page. q still works as combined search. If flexible_delivery_date is omitted it is treated as false (same Form Request as Blade) and delivery_lead_time_days becomes required. Send flexible_delivery_date: true to match the Blade create default, or send a lead time. view-own users only see their created_by PRs. Status values must stay draft|submitted|received|closed|cancelled. company_key: asas_ventures|qassioun_journey|activation. store/update payload matches Blade PR form (Form Requests reused). request_number optional on create — backend generates. Create needs existing project_id and category_id (from Blade or later F03/F04). show.form is the same shape Blade edit uses. Request Tracking page is F09 (not this feature). Print is F24."
  }
}
```
