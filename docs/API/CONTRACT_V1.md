# API Contract v1 (Stateless)

Status: Active
Scope: `/api/v1/*`
Authority: This contract is the source of truth for v1 behavior.

Hard rule: `/api/v1/*` MUST NOT return redirects or HTML and MUST NOT use 404 concealment for auth/tenant/entitlement failures.

---

## 1) Authentication (Bearer)
Required header:
- `Authorization: Bearer <token>`

Rules:
- Cookies are ignored for auth decisions on `/api/v1/*`.
- Missing or invalid Bearer -> 401 JSON (pre-controller).

---

## 2) Tenant Context (Header)
Required header:
- `X-Clinic-Id: <integer clinic_id>`

Validation (pre-controller, ApiV1AuthFilter):
- Missing -> 422 `tenant_context_missing`
- Non-integer / malformed -> 422 `tenant_context_invalid`
- Clinic not found -> 422 `tenant_context_invalid`
- Authenticated user not a member -> 403 `tenant_context_forbidden`
- Clinic disabled/suspended (if implemented) -> 403 `tenant_context_forbidden`

---

## 3) Subscription Standing (Trial + Grace Locked)
Enforcement point: ApiV1AuthFilter pre-controller via PlanGuard standing checks.

Locked model:
- status values: active | paused | canceled | expired
- Trial is date-based via start_at/end_at (no trialing status).
- end_at is exclusive.
- grace_days default 0 (implementation detail; contract defines behavior).

Standing decision:
- Valid only if status==active AND within (start_at/end_at) window OR within grace window (if configured).
- Invalid standing -> 403 `subscription_inactive`

---

## 4) Permissions (RBAC)
Enforced by PermissionFilter on the route:
- Missing permission -> 403 `forbidden` (pre-controller)

---

## 5) ActorContext (Audit Attribution Contract)
Purpose: audit integrity across stateless and session contexts.

Precedence (locked):
- API v1: bearer context (ApiRuntimeContext user_id + token_id)
- Web: session user_id
- Jobs/CLI: system actor

Required fields (minimum):
- actor_user_id
- actor_type: session | bearer | system
- actor_token_id (nullable)
- clinic_id (nullable)
- route, method
- ip, ua
- request_id (preferred)

Rule:
- actor_user_id=0 is forbidden unless actor_type=system with explicit reason_code.

---

## 6) Error Semantics (Canonical)
API MUST NOT use 404 concealment for:
- authentication
- tenant context
- subscription standing
- RBAC
- quota limits
- feature flags

Status mapping:
- 401: `unauthenticated`
- 422: `tenant_context_missing` / `tenant_context_invalid`
- 403: `tenant_context_forbidden` / `subscription_inactive` / `forbidden` / `feature_disabled`
- 429: `plan_quota_exceeded` (and `rate_limited` reserved)

Error JSON shape:
```json
{
  "error": {
    "code": "unauthenticated",
    "message": "Authentication required.",
    "details": {}
  }
}
Client rule:

Clients MUST use error.code and MUST NOT parse error.message.
## 7) Non-Goals

No CSRF requirements for /api/v1/*.

No session initiation, no cookie auth.

No redirects (including to login or clinic select).

No API v2 in this phase.