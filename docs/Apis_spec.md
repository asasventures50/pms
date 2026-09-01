# Apis_spec (index)

**Progress:** [`Apis_progress.md`](Apis_progress.md). **One feature file per ID** under [`Apis_spec/`](Apis_spec/).

Backend and frontend both work **one ID at a time** (same table). Do not hand frontend the whole folder.

## Frontend (one pass)

1. You name the ID (e.g. F04).
2. They get **only** that file from the Spec column, plus Blade screenshots for that screen.
3. They implement that ID against `/api/v1`. `pending` files = do not build.

Auth for every later feature: F01 Bearer token.

Base: `{APP_URL}/api/v1`. `Accept: application/json`. Lists: query keys in `list_filters` (no `/helpers` API). Update methods match Blade forms (**PUT**, not PATCH).

Postman (QA only): [`postman/pms.postman_collection.json`](postman/pms.postman_collection.json).

## Files

| ID | Spec file |
| --- | --- |
| F01 | [`Apis_spec/F01-authentication.md`](Apis_spec/F01-authentication.md) |
| F02 | [`Apis_spec/F02-locations.md`](Apis_spec/F02-locations.md) |
| F03 | [`Apis_spec/F03-projects.md`](Apis_spec/F03-projects.md) |
| F04 | [`Apis_spec/F04-categories.md`](Apis_spec/F04-categories.md) |
| F05 | [`Apis_spec/F05-users.md`](Apis_spec/F05-users.md) |
| F06 | [`Apis_spec/F06-roles.md`](Apis_spec/F06-roles.md) |
| F07 | [`Apis_spec/F07-vendors.md`](Apis_spec/F07-vendors.md) |
| F08 | [`Apis_spec/F08-procurement-requests.md`](Apis_spec/F08-procurement-requests.md) |
| F09 | [`Apis_spec/F09-pr-flow.md`](Apis_spec/F09-pr-flow.md) |
| F10 | [`Apis_spec/F10-schedule-of-works.md`](Apis_spec/F10-schedule-of-works.md) |
| F11 | [`Apis_spec/F11-rfq-terms.md`](Apis_spec/F11-rfq-terms.md) |
| F12 | [`Apis_spec/F12-rfqs.md`](Apis_spec/F12-rfqs.md) |
| F13 | [`Apis_spec/F13-quotation-invites-public-quote.md`](Apis_spec/F13-quotation-invites-public-quote.md) |
| F14 | [`Apis_spec/F14-vendor-quotations.md`](Apis_spec/F14-vendor-quotations.md) |
| F15 | [`Apis_spec/F15-quotation-comparison.md`](Apis_spec/F15-quotation-comparison.md) |
| F16 | [`Apis_spec/F16-purchase-orders.md`](Apis_spec/F16-purchase-orders.md) |
| F17 | [`Apis_spec/F17-invoices.md`](Apis_spec/F17-invoices.md) |
| F18 | [`Apis_spec/F18-quick-receipts.md`](Apis_spec/F18-quick-receipts.md) |
| F19 | [`Apis_spec/F19-activity-logs.md`](Apis_spec/F19-activity-logs.md) |
| F20 | [`Apis_spec/F20-dashboard.md`](Apis_spec/F20-dashboard.md) |
| F21 | [`Apis_spec/F21-public-vendor-registration.md`](Apis_spec/F21-public-vendor-registration.md) |
| F22 | [`Apis_spec/F22-category-import-export.md`](Apis_spec/F22-category-import-export.md) |
| F23 | [`Apis_spec/F23-vendor-import-export.md`](Apis_spec/F23-vendor-import-export.md) |
| F24 | [`Apis_spec/F24-downloads-prints-attachments.md`](Apis_spec/F24-downloads-prints-attachments.md) |
