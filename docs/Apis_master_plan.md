# APIs Master Plan (Blade stays live)

**What’s done / left:** [`docs/Apis_progress.md`](Apis_progress.md) (one table, keep in sync).

The existing Laravel + Blade app stays as-is. We only **add** versioned JSON APIs beside it. Work is **one feature at a time**. Do not implement the next feature until the current one is done and its block is appended to [`Apis_spec.md`](Apis_spec.md).

## Non-negotiable (live production)

1. No migrations that drop tables, drop/rename columns, rewrite enums, or mutate existing rows.
2. Sanctum token tables (new tables only) are allowed when we start Authentication.
3. Do not refactor duplicated code or duplicated data. Leave it.
4. Do not change existing enums. Do not invent a parallel enum for the same field.
5. Stay inside current layers: routes → controllers → Form Requests → Services → Models. API controllers are **new files** under versioned folders. Web controllers are **not rewritten**.
6. Routes: `/api/v1/...`. Controllers: `app/Http/Controllers/Api/V1/...`.
7. Same permission names as [`PermissionCatalog`](../app/Support/Access/PermissionCatalog.php). Same `permission:...` middleware.
8. Before coding a feature: read this plan **and** the feature spec in `Apis_spec.md`. If the feature block is still `pending`, write/agree the spec first, then code.

## Versioning

| Layer | Web (unchanged) | API v1 |
| --- | --- | --- |
| Routes | `routes/web.php` | `routes/api.php` prefix `v1` |
| Controllers | `app/Http/Controllers/...` | `app/Http/Controllers/Api/V1/...` |
| Auth | session `auth` | `auth:sanctum` Bearer |
| Response | Blade / redirect | JSON resources |

Later `v2` = new folder + new prefix. Do not break `v1` or Blade.

## Workflow each feature

1. You say which feature (e.g. “start Authentication”).
2. Agent reads this plan + that feature’s spec in `Apis_spec.md`.
3. Implement backend only for that feature.
4. Append/update the feature object in `Apis_spec.md` (`apisback`, `apisfront`, `apisspec_plan`) so frontend can paste the file into Cursor and implement without asking you.
5. Add a **new** Postman Collection v2.1 JSON for **this feature only** under `docs/postman/`, same shape as [`docs/postman/procurement-requests.postman_collection.json`](postman/procurement-requests.postman_collection.json). Do not edit or delete other collection files. The user imports it themselves.

### Postman file per feature

- Path: `docs/postman/{feature-slug}.postman_collection.json` (example: `procurement-requests.postman_collection.json`).
- Collection auth: Bearer `{{token}}`.
- Variables: `baseUrl` (`http://127.0.0.1:8000`), `token` (empty), plus ids this feature needs (`prId`, etc.).
- One request per route: `Accept: application/json`; body sample when POST/PUT/PATCH.
- One file = one feature. Never merge into / overwrite an older collection.

## Feature order (recommended)

Order follows **dependencies**, not calendar. You can say “do PR next” after the deps exist (auth + projects + categories at least).

| ID | Feature | Depends on | Notes |
| --- | --- | --- | --- |
| F01 | Authentication | — | Sanctum, login, logout, me, JSON 401/403. First feature. |
| F02 | Locations | F01 | countries, cities |
| F03 | Projects | F01 | projects + zones + quick-store (same services as web) |
| F04 | Categories | F01 | CRUD + vendor-links read/reassign. Import/export later (F23). |
| F05 | Users | F01 | access users |
| F06 | Roles | F01 | roles + permission sync (existing catalog names only) |
| F07 | Vendors | F02, F04 | CRUD + search-for-select. Import/export later (F24). |
| F08 | Procurement requests | F03, F04 | list/show/store/update/destroy + print as later file if needed |
| F09 | PR flow | F08 | `my-procurement-requests/flow` JSON |
| F10 | Schedule of works | F08 | |
| F11 | RFQ terms | F01 | |
| F12 | RFQs | F08, F07 | |
| F13 | Quotation invites + public quote | F12 | public token routes, no sanctum, same throttle |
| F14 | Vendor quotations (staff) | F12 | |
| F15 | Quotation comparison | F14 | |
| F16 | Purchase orders | F07, F08 | view / view-own |
| F17 | Invoices | F16 | |
| F18 | Quick receipts | F01 | view / view-own, approve, reject, sign |
| F19 | Activity logs | F01 | |
| F20 | Dashboard | F01 | numbers the Blade dashboard already shows |
| F21 | Public vendor registration | F07 fields | no sanctum, throttle |
| F22 | Category import/export/rebuild | F04 | files last |
| F23 | Vendor import/export/duplicates | F07 | files last |
| F24 | Downloads / prints / attachments | as needed | signed files, print endpoints — last |

F08 is “PR” (your day-2 example). Do it after F01 and the catalogs it needs (F03, F04), not as the second feature on a fresh API.

## Out of scope for every feature

- Changing Blade views or `routes/web.php` behavior.
- Shared “one controller for web + api”.
- Collapsing duplicate vendor/PR/RFQ payload logic.
- New permission names unless you explicitly ask (then add catalog + migration that **only inserts** new permission rows, like existing migrations).
