# App API Route Inventory

Last updated: 2026-10-22

Purpose: status and sequencing for the first-party React API. This file does not authorize implementation by itself.

## Boundaries

- `/api/v1/*` remains public/stateless Bearer + `X-Clinic-Id`.
- `/app-api/v1/*` uses same-origin session auth, CSRF on mutations, server-owned tenant context, JSON-only errors, plan gates, and RBAC.
- `/controlplane-app-api/v1/*` is reserved for a future airlocked control-plane API.
- Existing page-specific AJAX endpoints are not automatically App API contracts.

## Implemented

| Method | Route | Purpose |
|---|---|---|
| GET | `/app-api/v1/csrf` | Unauthenticated-safe CSRF refresh |
| POST | `/app-api/v1/auth/login` | JSON session login |
| POST | `/app-api/v1/auth/logout` | Session logout |
| GET | `/app-api/v1/bootstrap` | Auth/app/clinic/permission/feature state |
| POST | `/app-api/v1/clinic/switch` | Validated membership switch and session regeneration |
| GET | `/app-api/v1/patients` | Tenant-scoped directory |
| GET | `/app-api/v1/patients/{id}` | Tenant-scoped detail with cross-tenant concealment |
| POST | `/app-api/v1/patients` | Create; server forces clinic and protected fields |
| PATCH | `/app-api/v1/patients/{id}` | Scoped update |

Foundation tests: `tests/unit/AppApiFoundationTest.php`; Patients tests: `tests/unit/AppApiPatientsTest.php`.

## Development additions in consolidated PR #20

- `GET /app-api/v1/patients/{id}/history/appointments`
- `GET /app-api/v1/patients/{id}/history/examinations`
- `GET /app-api/v1/patients/{id}/history/treatments`

Bounded keyset histories with both patient and module view permissions; see `APP_API_CONTRACT.md`. This is not the complete workspace aggregate or React integration.

## Not Implemented

- Patient delete, workspace aggregate, timeline, attachments, finance, clinical, odontogram, or WhatsApp sections.
- Dashboard, appointments, clinical, finance, inventory, providers, users/roles, settings, files, notifications, or reports App API modules.
- Any control-plane App API.

## Recommended Sequence

1. Wire React bootstrap/auth/clinic state to the implemented foundation.
2. Define and implement `GET /app-api/v1/patients/{id}/workspace` as a reviewed read model.
3. Integrate Patients basics against existing endpoints.
4. Add one module contract at a time; keep clinical, finance, files, RBAC, and control plane late/high-risk.
5. Retain server-rendered print/export and complex specialist workflows until parity is proven.

## Module Risk

| Risk | Modules |
|---|---|
| Low/medium | dashboard summaries, patient basics, appointment reads, inventory tables |
| Medium/high | appointment writes, treatments, prescriptions, providers |
| High | odontogram, contracts/signatures, finance, files, settings, users/roles, WhatsApp |
| Critical | control plane |

## Refresh Checks

```powershell
php spark routes | findstr /I "app-api api/v1"
rg -n "setJSON|respond\(|isAJAX|calendar-events|chart-data" app\Controllers
rg -n "active_clinic_id|global_mode|PermissionService|PlanGuard" app
rg -n "StorageService|file/download|file/inline" app
```

Canonical response/auth/CSRF/error behavior is in `../APP_API_CONTRACT.md`. The pre-consolidation inventory remains available in Git history.
