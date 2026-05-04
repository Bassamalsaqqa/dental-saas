# App API Contract

Status: proposed foundation contract

This document defines the first-party JSON API for the future React application.
It does not change the existing public API contract.

## Purpose

`/app-api/v1/*` is the first-party API for the DentaCare React frontend.
It exposes app-oriented JSON contracts over the existing CodeIgniter 4 backend
while preserving backend ownership of authentication, tenant isolation, RBAC,
plan gates, finance logic, provider payouts, supplier payables, inventory COGS,
WhatsApp dispatch, file access, and odontogram persistence.

This API is not a public integration API. It is for the same-origin React app.

## App API vs Public API

| Area | `/app-api/v1/*` | `/api/v1/*` |
| --- | --- | --- |
| Consumer | First-party React app | External/public integrations |
| Auth | CI4 session cookie | Bearer token |
| Tenant source | Server session `active_clinic_id` | Required `X-Clinic-Id` header |
| CSRF | Required for unsafe methods | Not required |
| Cookies | Required/used | Ignored for auth decisions |
| Response type | JSON only | JSON only |
| Redirects | Forbidden | Forbidden |
| Business logic | Backend-owned | Backend-owned |
| Status | New internal app contract | Existing public/stateless contract |

Confirmed repo constraint: `app/Config/Filters.php` excludes `api/*` and
`api/v1/*` from CSRF. Therefore the React app API must not be placed under
`/api/*`. The correct namespace is `/app-api/v1/*`.

## Auth and Cookies

`/app-api/v1/*` uses the existing CI4/IonAuth session model.

Rules:

- The session cookie must be `HttpOnly`.
- The session cookie must be `Secure` in production.
- The session cookie must explicitly set `SameSite`, likely `Lax` for the
  same-origin `/app` React deployment.
- React must not store auth tokens in `localStorage`.
- React must not persist long-lived Bearer tokens.
- App API requests are same-origin credentialed requests.
- Auth failures return JSON, never HTML and never redirects.

Recommended auth endpoints:

- `POST /app-api/v1/auth/login`
- `POST /app-api/v1/auth/logout`
- `GET /app-api/v1/bootstrap`

Existing `/auth/*` routes remain during migration.

## Bootstrap and App State

`GET /app-api/v1/bootstrap` requires authentication, but it must not require
the tenant filter. Bootstrap tells React what state the authenticated session is
in.

Bootstrap must support:

- authenticated user with no clinics
- authenticated user with multiple clinics and no `active_clinic_id`
- authenticated tenant-ready state
- control-plane/global-mode state
- suspended clinic state
- inactive subscription state

Valid `app_state` values:

| State | Meaning |
| --- | --- |
| `no_clinic` | User is authenticated but has no clinic memberships. |
| `clinic_required` | User must select a clinic before tenant resources can load. |
| `tenant_ready` | User has an active tenant context and can load tenant resources. |
| `controlplane_ready` | User is in global/control-plane mode. |
| `suspended` | Active clinic or account state blocks tenant use. |
| `subscription_inactive` | Active clinic exists but subscription blocks tenant use. |

Tenant resource endpoints require tenant context. Bootstrap and clinic switch
must not require the tenant filter before they can render/select the clinic wall.

## CSRF Behavior

Unsafe methods require CSRF:

- `POST`
- `PUT`
- `PATCH`
- `DELETE`

Safe methods normally do not require CSRF:

- `GET`
- `HEAD`
- `OPTIONS`

React obtains CSRF from:

1. `GET /app-api/v1/bootstrap` after auth, or
2. `GET /app-api/v1/csrf` before login or when a token needs refresh.

`GET /app-api/v1/csrf` is unauthenticated-safe and JSON-only. It must reveal no
private user, clinic, permission, plan, or tenant data.

CSRF response shape:

```json
{
  "data": {
    "csrf": {
      "header": "X-CSRF-TOKEN",
      "name": "csrf_test_name",
      "hash": "..."
    }
  },
  "meta": {}
}
```

React stores CSRF in memory, not localStorage.

Unsafe requests send:

```http
X-CSRF-TOKEN: <hash>
```

CSRF failure:

```http
HTTP 419
```

```json
{
  "error": {
    "code": "csrf_failed",
    "message": "Security token expired. Refresh and try again.",
    "details": {}
  },
  "meta": {
    "request_id": "..."
  }
}
```

If CI4 regenerates tokens after successful unsafe requests, mutation responses
should include the next token in `meta.csrf`.

## Tenant Behavior

`active_clinic_id` is server-controlled.

Rules:

- React never sends `clinic_id` as tenant authority.
- React may display active clinic information from bootstrap.
- React may request clinic switching through an explicit endpoint.
- Backend validates the user is a member of the requested clinic.
- Backend regenerates the session after clinic switch.
- Missing tenant context returns JSON, not redirect.
- Tenant resource endpoints require tenant context.
- Bootstrap and clinic switch do not require the tenant filter before clinic
  selection is complete.

Missing tenant context:

```http
HTTP 422
```

```json
{
  "error": {
    "code": "tenant_context_missing",
    "message": "Select a clinic before continuing.",
    "details": {}
  },
  "meta": {
    "request_id": "..."
  }
}
```

Cross-tenant record access should be concealed:

```http
HTTP 404
```

```json
{
  "error": {
    "code": "not_found",
    "message": "Resource not found.",
    "details": {}
  },
  "meta": {
    "request_id": "..."
  }
}
```

## Control Plane Behavior

Future React control-plane APIs should use:

```text
/controlplane-app-api/v1/*
```

Rules:

- `global_mode=true` is required.
- `active_clinic_id` is rejected.
- Tenant routes reject `global_mode=true`.
- Control-plane routes reject active tenant context.
- Control plane should use a separate React shell if rebuilt.
- Tenant app state and control-plane app state must not be shared.

`/app-api/v1/*` is tenant-app territory. `/controlplane-app-api/v1/*` is the
reserved and recommended future React control-plane namespace.

## Error JSON Shape

All errors use:

```json
{
  "error": {
    "code": "validation_failed",
    "message": "Validation failed.",
    "details": {}
  },
  "meta": {
    "request_id": "req_..."
  }
}
```

Standard errors:

| Code | HTTP | Meaning |
| --- | ---: | --- |
| `unauthenticated` | 401 | No valid session or invalid credentials. |
| `tenant_context_missing` | 422 | No active clinic for tenant resource endpoint. |
| `control_plane_required` | 403 | Global mode required. |
| `tenant_plane_required` | 403 | Tenant mode required. |
| `forbidden` | 403 | Missing permission. |
| `subscription_inactive` | 403 | Clinic subscription blocks action. |
| `feature_disabled` | 403 | Plan feature unavailable. |
| `validation_failed` | 422 | Input validation failed. |
| `csrf_failed` | 419 | CSRF missing, expired, or invalid. |
| `not_found` | 404 | Resource absent or concealed. |
| `conflict` | 409 | State conflict or stale update. |
| `rate_limited` | 429 | Too many requests. |
| `server_error` | 500 | Unexpected server error. |

Login validation errors use `422 validation_failed`. Invalid credentials use
`401 unauthenticated`.

## Validation Error Shape

```json
{
  "error": {
    "code": "validation_failed",
    "message": "Please correct the highlighted fields.",
    "details": {
      "fields": {
        "email": ["Email is required."],
        "amount": ["Amount must be greater than zero."]
      }
    }
  },
  "meta": {
    "request_id": "req_..."
  }
}
```

Rules:

- Field keys match request body keys.
- Multiple messages per field are allowed.
- Global validation errors go under `details.form`.

## Response Envelope

All success responses use `{ data, meta }`.

Detail response:

```json
{
  "data": {
    "id": 27,
    "name": "Patient Name"
  },
  "meta": {}
}
```

List response:

```json
{
  "data": [],
  "meta": {
    "pagination": {
      "page": 1,
      "per_page": 25,
      "total": 100,
      "total_pages": 4
    },
    "sort": {
      "field": "created_at",
      "direction": "desc"
    },
    "filters": {}
  }
}
```

Mutation responses must return updated resource data or enough summary fields
for React state reconciliation. They must not return only a success message.

Mutation response example:

```json
{
  "data": {
    "id": 27,
    "status": "paid",
    "balance_due": "0.00",
    "updated_at": "2026-05-04T18:00:00+00:00"
  },
  "meta": {
    "message": "Saved.",
    "csrf": {
      "header": "X-CSRF-TOKEN",
      "name": "csrf_test_name",
      "hash": "..."
    }
  }
}
```

Do not use legacy `success: true` in new app API.

## Pagination, Filter, Sort, Search

List query convention:

```text
?page=1&per_page=25&sort=created_at&direction=desc&search=rami
```

Filters use bracket notation:

```text
?filter[status]=active&filter[type]=supplier
```

Rules:

- `page` minimum: `1`
- `per_page` default: `25`
- `per_page` max: `100`
- `direction`: `asc` or `desc`
- unsupported sort fields return `validation_failed`
- search is a plain string and server-sanitized
- backend decides searchable columns

## File Upload and Download Rules

Uploads:

- Use `multipart/form-data`.
- CSRF is required.
- Tenant context is required unless an endpoint is explicitly public.
- File type, size, and purpose are validated server-side.
- Files are stored only through backend storage services.
- Files must not be written to public web paths for direct access.

Downloads:

- React never receives raw filesystem paths.
- React uses authorized app API metadata and backend streaming/download actions.
- Private file access remains behind permission and tenant checks.
- Cross-tenant file access returns `404 not_found`.
- Same-tenant missing permission returns `403 forbidden`.

Recommended future endpoints:

```text
POST /app-api/v1/files
GET /app-api/v1/files/{id}
GET /app-api/v1/files/{id}/download
GET /app-api/v1/files/{id}/inline
DELETE /app-api/v1/files/{id}
```

## Audit and Actor Attribution

Every unsafe app API request must preserve:

- authenticated user id
- active clinic id when tenant-scoped
- global/control-plane actor context when control-plane-scoped
- action name
- target resource type
- target resource id when available
- IP/user agent where already logged
- before/after values for sensitive changes where feasible

Backend services/controllers should reuse existing audit facilities, especially
`ActivityLogger` and control-plane audit services.

React must not send actor identity except as display input.

## Localization and RTL

Bootstrap returns:

```json
{
  "data": {
    "locale": {
      "code": "ar",
      "direction": "rtl",
      "available": ["en", "ar"]
    }
  },
  "meta": {}
}
```

Rules:

- Backend errors return localized user-facing `message` where practical.
- Error `code` remains a stable English machine code.
- React handles RTL layout from bootstrap.
- React may maintain UI translation JSON.
- Server validation messages remain backend-owned.
- Locale switching goes through backend and updates the session.

## Versioning Policy

- Current version: `/app-api/v1/*`
- Breaking changes require `/app-api/v2/*`
- Non-breaking additions are allowed in v1.
- Response fields may be added, not removed.
- Error codes are stable.
- Deprecated fields remain for at least one migration phase.

## Initial Required Endpoints

### `GET /app-api/v1/bootstrap`

Purpose: initialize React app state.

Filters:

```text
auth
```

Bootstrap must not require `tenant` or `subscription` filters. It reports the
current app state instead.

Request body: none.

Response:

```json
{
  "data": {
    "app_state": "tenant_ready",
    "user": {
      "id": 12,
      "name": "Dr. Example",
      "email": "doctor@example.com"
    },
    "clinics": [
      {
        "id": 3,
        "name": "Palsync.net Ramallah",
        "status": "active"
      }
    ],
    "active_clinic": {
      "id": 3,
      "name": "Palsync.net Ramallah",
      "status": "active"
    },
    "session": {
      "authenticated": true,
      "global_mode": false,
      "impersonating": false
    },
    "locale": {
      "code": "ar",
      "direction": "rtl",
      "available": ["en", "ar"]
    },
    "permissions": {
      "patients:view": true,
      "finance:view": true
    },
    "features": {
      "whatsapp": true,
      "inventory": true
    },
    "navigation": [],
    "csrf": {
      "header": "X-CSRF-TOKEN",
      "name": "csrf_test_name",
      "hash": "..."
    }
  },
  "meta": {
    "request_id": "req_..."
  }
}
```

Error cases:

- 401 `unauthenticated`
- 500 `server_error`

Likely reused backend pieces:

- `BaseController::getUserDataForView()`
- `ClinicService`
- `ClinicUserModel`
- `PermissionService`
- `PlanGuard`
- `NavigationService`
- `LocaleResolverService`
- session service

Required tests:

- unauthenticated returns JSON 401
- authenticated user with no clinics returns `app_state=no_clinic`
- multiple clinics and no active clinic returns `app_state=clinic_required`
- active clinic returns `app_state=tenant_ready`
- global mode returns `app_state=controlplane_ready`
- suspended clinic returns `app_state=suspended`
- inactive subscription returns `app_state=subscription_inactive`
- CSRF token present
- RTL locale present for Arabic session
- no private file paths or secrets are returned

### `POST /app-api/v1/auth/login`

Purpose: JSON login for React.

Filters:

```text
csrf
```

Request:

```json
{
  "identity": "doctor@example.com",
  "password": "secret",
  "remember": false
}
```

Response:

```json
{
  "data": {
    "authenticated": true,
    "app_state": "clinic_required",
    "clinics": [
      {
        "id": 3,
        "name": "Clinic Name",
        "role": "doctor"
      }
    ]
  },
  "meta": {
    "message": "Signed in.",
    "csrf": {
      "header": "X-CSRF-TOKEN",
      "name": "csrf_test_name",
      "hash": "..."
    }
  }
}
```

Error cases:

- 422 `validation_failed`
- 401 `unauthenticated`
- 403 `forbidden` for disabled user
- 419 `csrf_failed`
- 429 `rate_limited`

Likely reused backend pieces:

- existing `Auth`/IonAuth login logic
- `ClinicUserModel`
- `ClinicService`
- session regeneration logic

Required tests:

- successful login regenerates session
- invalid credentials return JSON 401
- missing identity/password returns JSON 422
- disabled user rejected
- CSRF required
- no redirect/HTML

### `POST /app-api/v1/auth/logout`

Purpose: JSON logout.

Filters:

```text
auth, csrf
```

Request body: empty.

Response:

```json
{
  "data": {
    "authenticated": false
  },
  "meta": {
    "message": "Signed out."
  }
}
```

Error cases:

- 419 `csrf_failed`
- 500 `server_error`

Likely reused backend pieces:

- existing `Auth::logout` logic
- session destroy/regeneration

Required tests:

- logs out current user
- clears active clinic context
- returns JSON
- no redirect

### `GET /app-api/v1/csrf`

Purpose: refresh CSRF token.

Filters:

```text
none
```

This endpoint is unauthenticated-safe and must reveal no private data.

Response:

```json
{
  "data": {
    "csrf": {
      "header": "X-CSRF-TOKEN",
      "name": "csrf_test_name",
      "hash": "..."
    }
  },
  "meta": {}
}
```

Error cases:

- 500 `server_error`

Required tests:

- returns valid token before login
- returns valid token after login
- returns no private user/clinic data
- token works on next unsafe request

### `POST /app-api/v1/clinic/switch`

Purpose: select or switch tenant context.

Filters:

```text
auth, csrf
```

Do not run the `tenant` filter before this endpoint. The endpoint must work
from the clinic wall.

Request:

```json
{
  "clinic_id": 3
}
```

Response:

```json
{
  "data": {
    "app_state": "tenant_ready",
    "active_clinic": {
      "id": 3,
      "name": "Clinic Name",
      "status": "active"
    },
    "session": {
      "active_clinic_id": 3,
      "global_mode": false
    }
  },
  "meta": {
    "message": "Clinic selected.",
    "csrf": {
      "header": "X-CSRF-TOKEN",
      "name": "csrf_test_name",
      "hash": "..."
    }
  }
}
```

Error cases:

- 401 `unauthenticated`
- 403 `forbidden` if user is not a clinic member
- 403 `tenant_plane_required` if incompatible control-plane state cannot exit
- 404 `not_found` for concealed clinic
- 419 `csrf_failed`
- 422 `validation_failed`

Likely reused backend pieces:

- `ClinicSelector::processSelect`
- `ClinicSelector::switchClinic`
- `ClinicService`
- `ClinicUserModel`
- session regeneration logic

Required tests:

- valid switch sets active clinic
- session id regenerates
- old clinic context removed
- forbidden clinic rejected
- global mode handled safely
- response JSON only

## Forbidden Frontend Assumptions

React and Lovable-generated code must not assume:

- clinic id in UI state is authoritative
- permissions in UI are security
- plan gates in UI are security
- finance totals can be calculated client-side
- provider shares can be calculated client-side
- supplier payable balances can be calculated client-side
- inventory COGS can be calculated client-side
- odontogram saved state can bypass backend validation
- file URLs can be public
- WhatsApp can be dispatched directly
- control plane and tenant plane can share state
- public `/api/v1/*` can be used as logged-in app API

## Minimum Foundation Tests

- unauthenticated app API returns JSON
- CSRF failure returns JSON
- missing tenant session on tenant resource endpoint returns JSON
- bootstrap handles `no_clinic`
- bootstrap handles `clinic_required`
- bootstrap handles `tenant_ready`
- bootstrap handles `controlplane_ready`
- bootstrap handles `suspended`
- bootstrap handles `subscription_inactive`
- tenant/control-plane mismatch returns JSON
- permission denial returns JSON
- cross-tenant record concealment returns 404
- bootstrap correctness
- clinic switch session regeneration
- validation error shape

