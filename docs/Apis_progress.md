# API v1 progress

Single glance. Agent: **only this file** for “what’s done”. Do not open `Apis_spec.md` or `Apis_master_plan.md` just to answer status. When a feature becomes `ready`, update this table in the same turn.

`ready` = backend + spec block + Postman file. `pending` = not started.

| ID | Feature | Status | Postman |
| --- | --- | --- | --- |
| F01 | Authentication | ready | missing |
| F02 | Locations | ready | `docs/postman/locations.postman_collection.json` |
| F03 | Projects | pending | — |
| F04 | Categories | pending | — |
| F05 | Users | pending | — |
| F06 | Roles | pending | — |
| F07 | Vendors | pending | — |
| F08 | Procurement requests | ready | `docs/postman/procurement-requests.postman_collection.json` |
| F09 | PR flow | pending | — |
| F10 | Schedule of works | pending | — |
| F11 | RFQ terms | pending | — |
| F12 | RFQs | pending | — |
| F13 | Quotation invites + public quote | pending | — |
| F14 | Vendor quotations (staff) | pending | — |
| F15 | Quotation comparison | pending | — |
| F16 | Purchase orders | pending | — |
| F17 | Invoices | pending | — |
| F18 | Quick receipts | pending | — |
| F19 | Activity logs | pending | — |
| F20 | Dashboard | pending | — |
| F21 | Public vendor registration | pending | — |
| F22 | Category import/export | pending | — |
| F23 | Vendor import/export | pending | — |
| F24 | Downloads / prints / attachments | pending | — |

**Count:** 3 / 24 ready (F01, F02, F08).

**Note:** F08 shipped before F03/F04. Creating a PR via API still needs existing `project_id` and `category_id` (Blade or later F03/F04).

**Next (recommended):** F03 Projects or F04 Categories.
