# API-B Contract: Stateless Integration API (v1)

**Status:** B0 (Authoritative Contract)
**Scope:** /api/v1/* only
**Owner:** Security hardening program
**Purpose:** Define authoritative API-B behavior prior to implementation.

---

## 1) Scope

- **In scope:** All endpoints under `/api/v1/*`.
- **Out of scope:** Any other `/api/*` paths (if present) are not governed by this contract unless explicitly listed in future updates.

---

## 2) Authentication (API-B-I1) ? Operational Rules

- **Required header:** `Authorization: Bearer <token>`
- **Cookies forbidden for auth (API-B-I1):** `/api/v1/*` must not authenticate via cookies after cutover.

Operational handling (post-cutover):
1. **Cookie present, no Bearer:** return **401 unauthenticated**. Session auth is not used.
2. **Cookie + Bearer present:** evaluate **Bearer** only; **ignore cookie** (log event optional).
3. **CSRF tokens:** irrelevant for `/api/v1/*` after B2. CSRF remains for web routes.

Legacy note (pre-B1):
- Current API behavior is cookie-session + CSRF and uses session tenant context. This is legacy only.

---

## 3) Tenant Context ? Header Contract + Evaluation Order

- **Required header:** `X-Clinic-Id: <integer clinic_id>`
- **Enforcement timing:** Mandatory starting B1.

Validation rules:
- Missing header -> **422** `tenant_context_missing`
- Non-integer or malformed -> **422** `tenant_context_invalid`
- Clinic not found (numeric, no record) -> **422** `tenant_context_invalid`
- Authenticated user not a member of clinic -> **403** `tenant_context_forbidden`
- Clinic disabled/suspended (if applicable) -> **403** `tenant_context_forbidden`

**Evaluation order (locked):**
1) Bearer auth
2) Tenant header presence/format
3) Tenant membership authorization
4) Subscription standing checks
5) Quota checks
6) RBAC permission checks

---

## 4) Error Model (API-C0) ? Status Codes + Machine Codes

**API never returns 404 for auth, subscription, quota, or tenant failures.**

Status code mapping (locked):
- **401** unauthenticated (missing/invalid Bearer)
- **403** forbidden (RBAC OR subscription_inactive OR tenant_context_forbidden)
- **422** tenant context missing/invalid
- **429** quota exceeded (plan limits) and rate limiting (reserved)

Machine codes (locked):
- `unauthenticated` -> 401
- `tenant_context_missing` -> 422
- `tenant_context_invalid` -> 422
- `tenant_context_forbidden` -> 403
- `subscription_inactive` -> 403
- `forbidden` (RBAC) -> 403
- `plan_quota_exceeded` -> 429
- `rate_limited` -> 429 (reserved for B5)

Error response shape (contract):
- **Content-Type:** application/json
- **Required keys:** `error.code`, `error.message`
- **Optional:** `error.details`
- **Client rule:** Clients MUST NOT parse `error.message`; they MUST use `error.code`.

Example:
```json
{
  "error": {
    "code": "plan_quota_exceeded",
    "message": "Plan limit reached.",
    "details": {
      "metric": "patients_active_max",
      "limit": 0,
      "usage": 3
    }
  }
}
```

---

## 5) Non-Goals (Explicit)

- No version bump (/api/v2 is not created).
- No payload reshaping of successful responses in B0/B1.
- No RBAC redesign.
- No rate-limit implementation yet (rate_limited is reserved only).
- No new resource endpoints required by this contract.
- No web concealment behaviors imported into API.

---

## 6) Transition Notes (Pre-B1 -> B1 -> B2 -> B3)

**Pre-B1 (current legacy):**
- Cookie-session auth + CSRF.
- Session tenant context (`active_clinic_id`) is used.

**B1:**
- Bearer auth introduced for `/api/v1/*`.
- Cookie-only requests return 401; cookies are ignored when Bearer is present.
- `X-Clinic-Id` is mandatory starting B1.

**B2:**
- CSRF removed for `/api/v1/*` only; web remains unchanged.

**B3:**
- Tenant header enforcement audited and validated against the contract.

---

## 7) Verification Checklist (B0 Baseline)

Future reviewers must confirm:
- **No 404 for API auth/plan/quota:** `/api/v1/*` returns 401/403/422/429 only.
- **Bearer required:**
  - Cookie-only -> 401
  - Bearer -> 200 (with valid permissions)
- **Tenant header enforcement:**
  - Missing header -> 422 tenant_context_missing
  - Invalid header -> 422 tenant_context_invalid
  - Not a member -> 403 tenant_context_forbidden
- **Subscription inactive:** 403 subscription_inactive
- **Quota exceeded:** 429 plan_quota_exceeded

Suggested checks (examples):
- `curl -k -i https://localhost/dental-saas/api/v1/patients` -> 401
- `curl -k -i -H "Authorization: Bearer TOKEN" https://localhost/dental-saas/api/v1/patients` -> 200
- `curl -k -i -H "Authorization: Bearer TOKEN" https://localhost/dental-saas/api/v1/patients` -> 422 (missing X-Clinic-Id once B1 is active)
- `curl -k -i -H "Authorization: Bearer TOKEN" -H "X-Clinic-Id: 999" https://localhost/dental-saas/api/v1/patients` -> 422/403 per contract
- `curl -k -i -H "Authorization: Bearer TOKEN" -H "X-Clinic-Id: 1" -X POST https://localhost/dental-saas/api/v1/patients` -> 429 when quota exceeded

---

## 8) Acceptance Statement

This contract is the source of truth for API-B. Implementation must conform to these rules and will be verified against the checklist in B1-B4.
