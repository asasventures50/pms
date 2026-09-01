# F09 ProcurementRequestFlow (API v1)

Give **this file only** to frontend Cursor, plus Blade screenshots for **this feature**. Do not implement other feature IDs in the same pass.

Status: **ready**

Auth: after F01, every call uses Authorization Bearer token and Accept: application/json. Base: APP_URL/api/v1.

How to read the JSON: apisfront = endpoints/payloads; list_filters = query keys (same as Blade, not extra routes); apisspec_plan = UI rules.

```json
{
  "ProcurementRequestFlow": {
    "status": "ready",
    "id": "F09",
    "apisback": "Controller: App\\Http\\Controllers\\Api\\V1\\Procurement\\ProcurementRequests\\ProcurementRequestFlowController. Reuses ProcurementRequestFlowBuilder, FlowStageKey, FlowStageState, User canAccessProcurementRequestFlow / canViewAllProcurementRequestFlows / scopesProcurementRequestFlowToOwn. Web ProcurementRequestFlowController and routes/web.php unchanged. No migrations. Same pipeline PR → RFQ → Quotations → Selection → PO → Invoice.",
    "apisfront": {
      "index": {
        "endpoint": "/api/v1/my-procurement-requests/flow",
        "method": "GET",
        "headers": {
          "Accept": "application/json",
          "Authorization": "Bearer {token}"
        },
        "request_payload": {
          "per_page": "optional 1-50 default 15 (Blade max is 50, not 100)"
        },
        "expected_response": {
          "data": [
            {
              "id": 1,
              "request_number": "PR-...",
              "status": "draft",
              "requested_at": "2026-08-31",
              "project": {
                "id": 1,
                "code": "",
                "name": ""
              },
              "creator": {
                "id": 1,
                "name": "only when view_all is true"
              },
              "flow": {
                "active_stage": "pr",
                "status_summary": "Purchase request · Draft",
                "rfq_count": 0,
                "quotation_count": 0,
                "po_count": 0,
                "invoice_count": 0,
                "has_selection": false,
                "stages": [
                  {
                    "key": "pr",
                    "state": "active",
                    "label": "PR",
                    "badge": null,
                    "badge_label": null,
                    "detail": "Draft"
                  }
                ],
                "rfqs": [
                  {
                    "id": 1,
                    "rfq_number": "",
                    "selected_vendor_quotation_id": null
                  }
                ],
                "purchase_orders": [
                  {
                    "id": 1,
                    "po_number": "",
                    "status": ""
                  }
                ],
                "invoices": [
                  {
                    "id": 1,
                    "invoice_number": "",
                    "source": ""
                  }
                ]
              }
            }
          ],
          "links": {},
          "meta": {},
          "view_all": true,
          "stage_keys": [
            "pr",
            "rfq",
            "quotations",
            "selection",
            "po",
            "invoice"
          ]
        },
        "error_codes": {
          "401": "Unauthenticated",
          "403": "Cannot access request tracking"
        }
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
  }
}
```
