# API v1 progress

Single glance. Agent: **only this file** for “what’s done”. Do not open `Apis_spec.md` or `Apis_master_plan.md` just to answer status. When a feature becomes `ready`, update this table in the same turn.

`ready` = backend + spec block + requests added to `docs/postman/pms.postman_collection.json`. `pending` = not started.

| ID | Feature | Status | Postman |
| --- | --- | --- | --- |
| F01 | Authentication | ready | `docs/postman/pms.postman_collection.json` |
| F02 | Locations | ready | `docs/postman/pms.postman_collection.json` |
| F03 | Projects | ready | `docs/postman/pms.postman_collection.json` |
| F04 | Categories | pending | — |
| F05 | Users | pending | — |
| F06 | Roles | pending | — |
| F07 | Vendors | pending | — |
| F08 | Procurement requests | ready | `docs/postman/pms.postman_collection.json` |
| F09 | PR flow | ready | `docs/postman/pms.postman_collection.json` |
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

**Count:** 5 / 24 ready (F01, F02, F03, F08, F09).

**Note:** F08 shipped before F04. Creating a PR via API still needs an existing `category_id` (Blade or F04).

**Next (recommended):** F04 Categories.
