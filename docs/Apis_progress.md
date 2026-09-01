# API v1 progress

Single glance. Agent: **only this file** for “what’s done”. Do not open spec files or `Apis_master_plan.md` just to answer status. When a feature becomes `ready`, update this table in the same turn.

`ready` = backend + **that ID’s** spec file in `docs/Apis_spec/` + requests in `docs/postman/pms.postman_collection.json`. `pending` = not started.

Frontend works the same way: one ID per pass. Give them the Spec path for that row only, plus Blade screenshots for that feature.

| ID | Feature | Status | Spec (give frontend this file) | Postman |
| --- | --- | --- | --- | --- |
| F01 | Authentication | ready | [`docs/Apis_spec/F01-authentication.md`](Apis_spec/F01-authentication.md) | `docs/postman/pms.postman_collection.json` |
| F02 | Locations | ready | [`docs/Apis_spec/F02-locations.md`](Apis_spec/F02-locations.md) | `docs/postman/pms.postman_collection.json` |
| F03 | Projects | ready | [`docs/Apis_spec/F03-projects.md`](Apis_spec/F03-projects.md) | `docs/postman/pms.postman_collection.json` |
| F04 | Categories | ready | [`docs/Apis_spec/F04-categories.md`](Apis_spec/F04-categories.md) | `docs/postman/pms.postman_collection.json` |
| F05 | Users | pending | [`docs/Apis_spec/F05-users.md`](Apis_spec/F05-users.md) | — |
| F06 | Roles | pending | [`docs/Apis_spec/F06-roles.md`](Apis_spec/F06-roles.md) | — |
| F07 | Vendors | pending | [`docs/Apis_spec/F07-vendors.md`](Apis_spec/F07-vendors.md) | — |
| F08 | Procurement requests | ready | [`docs/Apis_spec/F08-procurement-requests.md`](Apis_spec/F08-procurement-requests.md) | `docs/postman/pms.postman_collection.json` |
| F09 | PR flow | ready | [`docs/Apis_spec/F09-pr-flow.md`](Apis_spec/F09-pr-flow.md) | `docs/postman/pms.postman_collection.json` |
| F10 | Schedule of works | pending | [`docs/Apis_spec/F10-schedule-of-works.md`](Apis_spec/F10-schedule-of-works.md) | — |
| F11 | RFQ terms | pending | [`docs/Apis_spec/F11-rfq-terms.md`](Apis_spec/F11-rfq-terms.md) | — |
| F12 | RFQs | pending | [`docs/Apis_spec/F12-rfqs.md`](Apis_spec/F12-rfqs.md) | — |
| F13 | Quotation invites + public quote | pending | [`docs/Apis_spec/F13-quotation-invites-public-quote.md`](Apis_spec/F13-quotation-invites-public-quote.md) | — |
| F14 | Vendor quotations (staff) | pending | [`docs/Apis_spec/F14-vendor-quotations.md`](Apis_spec/F14-vendor-quotations.md) | — |
| F15 | Quotation comparison | pending | [`docs/Apis_spec/F15-quotation-comparison.md`](Apis_spec/F15-quotation-comparison.md) | — |
| F16 | Purchase orders | pending | [`docs/Apis_spec/F16-purchase-orders.md`](Apis_spec/F16-purchase-orders.md) | — |
| F17 | Invoices | pending | [`docs/Apis_spec/F17-invoices.md`](Apis_spec/F17-invoices.md) | — |
| F18 | Quick receipts | pending | [`docs/Apis_spec/F18-quick-receipts.md`](Apis_spec/F18-quick-receipts.md) | — |
| F19 | Activity logs | pending | [`docs/Apis_spec/F19-activity-logs.md`](Apis_spec/F19-activity-logs.md) | — |
| F20 | Dashboard | pending | [`docs/Apis_spec/F20-dashboard.md`](Apis_spec/F20-dashboard.md) | — |
| F21 | Public vendor registration | pending | [`docs/Apis_spec/F21-public-vendor-registration.md`](Apis_spec/F21-public-vendor-registration.md) | — |
| F22 | Category import/export | pending | [`docs/Apis_spec/F22-category-import-export.md`](Apis_spec/F22-category-import-export.md) | — |
| F23 | Vendor import/export | pending | [`docs/Apis_spec/F23-vendor-import-export.md`](Apis_spec/F23-vendor-import-export.md) | — |
| F24 | Downloads / prints / attachments | pending | [`docs/Apis_spec/F24-downloads-prints-attachments.md`](Apis_spec/F24-downloads-prints-attachments.md) | — |

**Count:** 6 / 24 ready (F01, F02, F03, F04, F08, F09).

**Next (recommended):** F05 Users.
