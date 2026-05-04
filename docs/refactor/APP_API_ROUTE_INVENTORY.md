# App API Route Inventory

Status: planning inventory for future React migration

This document maps the current PHP/session route surface to the proposed
first-party React app API. It does not authorize route/controller changes by
itself.

## Confirmed Route Facts

- Current product surface is CodeIgniter 4 PHP views plus mixed JSON/AJAX
  endpoints.
- Existing public API lives under `/api/v1/*`.
- `/api/v1/*` is stateless and uses the `apiv1` filter.
- `/api/v1/*` must remain Bearer + `X-Clinic-Id`.
- `app/Config/Filters.php` excludes `api/*` and `api/v1/*` from CSRF.
- First-party React API should use `/app-api/v1/*`.
- Future React control-plane API should use `/controlplane-app-api/v1/*`.

## Inspection Commands

Use these commands before implementation and when refreshing this inventory:

```powershell
php spark routes
php spark routes | findstr /I "api app-api get-data get-statistics stats chart-data calendar-events data export"
rg -n "setJSON|respond\(|isAJAX|get[A-Za-z0-9_]*Data|get[A-Za-z0-9_]*Stats|calendar-events" app\Controllers
rg -n "return view|redirect\(" app\Controllers
rg -n "active_clinic_id|global_mode|is_impersonating|impersonated_clinic_id" app
rg -n "permission:|has_permission|PermissionService|PlanGuard" app\Controllers app\Views app\Services
rg -n "extends TenantAwareModel|where\('clinic_id'|db->table" app\Models app\Controllers
rg -n "StorageService|file/download|file/inline|writable/uploads|public/uploads" app
```

## Initial App API Foundation Routes

| Route | Method | Purpose | Filters |
| --- | --- | --- | --- |
| `/app-api/v1/bootstrap` | GET | Authenticated app state, clinics, permissions, features, locale, CSRF | `auth` |
| `/app-api/v1/csrf` | GET | Unauthenticated-safe CSRF refresh | none |
| `/app-api/v1/auth/login` | POST | JSON login | `csrf` |
| `/app-api/v1/auth/logout` | POST | JSON logout | `auth`, `csrf` |
| `/app-api/v1/clinic/switch` | POST | Select/switch active tenant context | `auth`, `csrf` |

Bootstrap and clinic switch must not require the tenant filter. Tenant resource
endpoints require tenant context.

## Module Inventory

| Module | Existing PHP Routes | Existing AJAX/session JSON endpoints | Existing `/api/v1` endpoints | Proposed `/app-api/v1` endpoints | Permissions | Tenant Scope | Risk | Notes |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Bootstrap/auth/clinic | `/auth/*`, `/clinic/select`, `/clinic/switch` | `/api/session-token` | None | `/bootstrap`, `/csrf`, `/auth/login`, `/auth/logout`, `/clinic/switch` | auth-level | session/global split | High | Foundation. Bootstrap supports `no_clinic`, `clinic_required`, `tenant_ready`, `controlplane_ready`, `suspended`, `subscription_inactive`. |
| Dashboard | `/dashboard` | `/dashboard/stats`, `/dashboard/chart-data` | None | `/dashboard/summary`, `/dashboard/charts` | `dashboard:view` | tenant | Low | Not the first serious frontend slice. Useful after patient workspace proves app shell. |
| Patients | `/patients`, `/patient`, create/show/edit/delete | `get-data`, `get-statistics`, `{id}/data`, attachment upload/delete | `/api/v1/patients` CRUD | `/patients`, `/patients/{id}`, `/patients/{id}/summary`, `/patients/{id}/workspace`, `/patients/{id}/attachments` | `patients:*` | tenant | Medium | First serious React slice should be Patients directory + Patient profile workspace. |
| Appointments | `/appointments`, `/appointment`, calendar/create/show/edit/status | calendar events, time slots, status actions, AJAX delete | `/api/v1/appointments` CRUD | `/appointments`, `/appointments/calendar`, `/appointments/slots`, status endpoints | `appointments:*` | tenant | Medium | Calendar UX needs clean non-DataTables contract. |
| Examinations | `/examinations`, `/examination`, create/show/edit/complete/print | calendar events, stats, data, tooth update | `/api/v1/examinations` CRUD | `/examinations`, `/examinations/{id}`, `/examinations/{id}/complete` | `examinations:*` | tenant | High | Clinical data and odontogram coupling. |
| Odontogram | patient/exam/contract-related views and `Odontogram` controller | tooth-condition update, patient version endpoints | None | `/odontogram/patients/{id}`, `/odontogram/examinations/{id}`, `/odontogram/snapshots/{id}` | patients/examinations | tenant | High | Backend must define canonical state, validation, and snapshots. |
| Treatments | `/treatments`, `/treatment` | stats, complete/delete AJAX | search only under `/api/v1/search/treatments` | `/treatments`, `/treatments/{id}`, `/treatments/stats` | `treatments:*` | tenant | Medium | Keep clinical treatment separate from finance transaction assumptions. |
| Contracts | `/contracts/*` | limited JSON; mostly PHP views | None | `/contracts`, `/contracts/{id}`, sign metadata/actions | `patients:*` currently | tenant | High | Signatures and odontogram snapshots need careful parity. |
| Prescriptions | `/prescriptions`, `/prescription` | stats, `get-prescriptions` | medication search only | `/prescriptions`, `/prescriptions/{id}`, `/prescriptions/stats` | `prescriptions:*` | tenant | Medium | Server-rendered print can remain during migration. |
| Finance transactions | `/finance`, `/finance/create`, `/finance/{id}` | stats and page-specific transaction actions | `/api/v1/finances` CRUD | `/finance/transactions`, `/finance/transactions/{id}`, payment/refund/provider-payment actions | `finance:*` | tenant | High | Server owns all money math and reconciliation fields. |
| Finance reports | `/finance/reports`, `/reports` | chart/detail data, exports | None | `/finance/reports/*`, `/reports/*` | `reports:view`, `finance:view` | tenant | High | Not early low-risk. Move later and require old PHP vs React parity checks. |
| Supplier payables | `/finance/payables` | page-specific actions | None | `/finance/payables`, `/finance/payables/{id}`, payment actions | `finance:*` | tenant | High | Keep separate from provider payouts. |
| Provider payouts | `/finance/payouts` | provider payout actions/payments | None | `/finance/payouts`, `/finance/payouts/{id}`, `/payments` | `finance:*` | tenant | High | Supports guest providers; shares are patient-transaction based. |
| Treasury transfers | `/finance/transfers` | transfer actions | None | `/finance/transfers`, `/finance/accounts` | `finance:*` | tenant | High | Liquidity account validation stays backend-owned. |
| Inventory items | `/inventory`, create/show/edit | `getInventoryData`, `getInventoryStats`, adjust/receive JSON | inventory search only | `/inventory/items`, `/inventory/items/{id}`, adjust/receive | `inventory:*` | tenant | Medium/High | Inventory COGS touches finance. |
| Inventory movements | `/inventory/movements`, usage, low-stock, reorder | usage history, low-stock, reorder JSON | None | `/inventory/movements`, `/inventory/usage`, `/inventory/low-stock`, `/inventory/reorder-list` | `inventory:*` | tenant | Medium | Good table slice after patient workspace. |
| Providers | `/providers`, `/inventory/suppliers`, `/users/{id}`, `/finance/payouts` | `/providers/data`, `/providers/stats` | user search only | `/providers`, `/providers/{type}/{id}`, stats | inventory/finance/users mix | tenant | Medium | Must avoid duplicate provider rows and preserve supplier/provider distinction. |
| Users | `/users` | stats, inline update, role/permission AJAX | user search only | `/users`, `/users/{id}`, role/permission endpoints | `users:*` | tenant | High | RBAC sensitive. Do not make UI permissions security. |
| Roles | `/roles`, `/controlplane/roles` | stats, permissions | role search only | `/roles`, `/roles/{id}/permissions`; control-plane later under `/controlplane-app-api/v1/roles` | `users:*` | tenant/control | High | Tenant/control-plane split must be explicit. |
| Settings | `/settings` tabs/actions | channel config, WhatsApp test, notifications | None | `/settings/*`, `/settings/channels/*` | `settings:*` | tenant | High | Plan gates and WhatsApp config. |
| Notifications/WhatsApp | `/notifications/ledger`, settings communications, `/webhooks/whatsapp` | `/api/notifications`, mark-read, retry | None | `/notifications`, `/notifications/{id}/read`, `/notifications/ledger` | settings for ledger | tenant | High | Webhooks stay outside app API. Dispatch remains backend-owned. |
| Files | `/file/download/{id}`, `/file/inline/{id}` | JSON errors on failure | None | `/files/{id}`, `/files/{id}/download`, `/files/{id}/inline` | resource-specific | tenant | High | Cross-tenant file access returns 404; same-tenant missing permission returns 403. |
| Kiosk | `/kiosk/{token}/*` | AJAX responses in public flow | None | Defer; probably no logged-in app API | public token | public/tenant-derived | High | Public patient-facing flow. Do not fold into tenant app API early. |
| Control Plane | `/controlplane/*` | activity-log API, global search, settings AJAX | None | `/controlplane-app-api/v1/*` later | superadmin/control | control only | Critical | Future React control plane must stay airlocked. |

## Existing Public API Surface

Confirmed `/api/v1/*` modules:

```text
/api/v1/patients
/api/v1/examinations
/api/v1/appointments
/api/v1/finances
/api/v1/search/patients
/api/v1/search/users
/api/v1/search/examinations
/api/v1/search/treatments
/api/v1/search/inventory
/api/v1/search/medications
/api/v1/search/treatment-types
/api/v1/search/departments
/api/v1/search/roles
```

These remain public/stateless and are not the React app API.

## Existing Mixed Session JSON Examples

Examples found in current routes/controllers:

```text
/dashboard/stats
/dashboard/chart-data
/patients/get-data
/patients/get-statistics
/patients/{id}/data
/providers/data
/providers/stats
/examinations/calendar-events
/examinations/getExaminationsData
/examinations/getExaminationStats
/appointments/calendar-events
/inventory/getInventoryData
/inventory/getInventoryStats
/inventory/getUsageHistoryData
/inventory/getLowStockData
/reports/chart-data
/reports/detail-data
/api/notifications
/activity-log/api
/file/download/{id}
/file/inline/{id}
```

These endpoints may inform implementation but should not be treated as final
React contracts because payload shapes are inconsistent and often page-specific.

## Recommended First Backend Steps

1. Add this contract and inventory to active docs.
2. Implement only `/app-api/v1/bootstrap`, `/app-api/v1/csrf`,
   `/app-api/v1/auth/login`, `/app-api/v1/auth/logout`, and
   `/app-api/v1/clinic/switch`.
3. Add JSON failure behavior for app API auth, CSRF, tenant, subscription, and
   permission failures.
4. Add foundation tests before importing Lovable output.
5. Start the first serious React slice with Patients directory + Patient profile
   workspace.

Do not start with finance reports. They require old PHP vs React parity checks
and should be migrated later.

## Minimum Foundation Tests

- unauthenticated app API returns JSON
- CSRF failure returns JSON
- `/app-api/v1/csrf` is unauthenticated-safe and leaks no private data
- bootstrap returns `no_clinic`
- bootstrap returns `clinic_required`
- bootstrap returns `tenant_ready`
- bootstrap returns `controlplane_ready`
- bootstrap returns `suspended`
- bootstrap returns `subscription_inactive`
- tenant resource endpoint without active tenant returns JSON
- tenant/control-plane mismatch returns JSON
- permission denial returns JSON
- cross-tenant record concealment returns 404
- same-tenant missing file permission returns 403
- clinic switch regenerates session
- validation errors use `422 validation_failed`
- invalid credentials use `401 unauthenticated`

