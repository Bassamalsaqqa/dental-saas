# API-B Contract: Stateless Integration API (v1)

Status: B4 (Implemented + Verified)
Scope: /api/v1/*
Owner: Security hardening program

This document reflects CURRENT ENFORCED behavior. It does not define new behavior.

---

## 1) Scope
- In scope: all endpoints under `/api/v1/*`.
- Out of scope: `/api/search/*` (session-based) and any other `/api/*` paths unless explicitly documented.

---

## 2) Authentication (API-B-I1)
Required header:
- `Authorization: Bearer <token>`

Cookie auth rule:
- `/api/v1/*` MUST NOT authenticate via cookies.
- If cookies are present, they are ignored for auth decisions.

Operational handling:
1) Cookie present, no Bearer -> 401 `unauthenticated` (JSON).
2) Cookie + Bearer present -> evaluate Bearer only; ignore cookie.
3) CSRF tokens are not required for `/api/v1/*` requests (CSRF remains for web routes and session-based API routes).

Notes:
- The server may still set a CSRF cookie globally; that cookie must not gate `/api/v1/*`.
- `/api/search/*` is intentionally session-based and may emit `ci_session_saas`.

---

## 3) Tenant Context
Required header:
- `X-Clinic-Id: <integer clinic_id>`

Validation rules:
- Missing header -> 422 `tenant_context_missing`
- Non-integer / malformed -> 422 `tenant_context_invalid`
- Clinic id numeric but not found -> 422 `tenant_context_invalid`
- Authenticated user not a member -> 403 `tenant_context_forbidden`
- Clinic disabled/suspended (if applicable) -> 403 `tenant_context_forbidden`

Evaluation order (locked):
1) Bearer auth
2) Tenant header presence/format
3) Tenant membership authorization
4) Subscription standing checks
5) Quota checks
6) RBAC permission checks

---

## 4) Error Model (API-C0)
API must not return 404 for auth/tenant/plan/quota failures.

Status mapping (locked):
- 401 unauthenticated (missing/invalid Bearer)
- 403 forbidden (RBAC OR subscription_inactive OR tenant_context_forbidden)
- 422 tenant context missing/invalid
- 429 plan quota exceeded and rate limiting (rate limiting may be implemented later)

Machine codes (locked):
- unauthenticated -> 401
- tenant_context_missing -> 422
- tenant_context_invalid -> 422
- tenant_context_forbidden -> 403
- subscription_inactive -> 403
- forbidden (RBAC) -> 403
- plan_quota_exceeded -> 429
- rate_limited -> 429 (reserved for B5)

Error response shape:
- Content-Type: application/json
- Required keys: `error.code`, `error.message`
- Optional: `error.details`
Client rule:
- Clients MUST use `error.code` and MUST NOT parse `error.message`.

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

---

## 5) Non-Goals
  No /api/v2.
  No payload reshaping of successful responses in this contract.
  No RBAC redesign.
  No rate limit implementation required (reserved only).
  No importing web concealment behaviors into API.

## 6) Verification Checklist (Operational)
  Bearer required:
  cookie-only -> 401 JSON
  Bearer valid + tenant header valid -> 200 (subject to RBAC/plan/quota)
  Tenant header enforcement:
  missing -> 422 tenant_context_missing
  invalid -> 422 tenant_context_invalid
  not a member -> 403 tenant_context_forbidden
  Subscription inactive -> 403 subscription_inactive (JSON)
  Quota exceeded -> 429 plan_quota_exceeded (JSON)
  /api/search/* remains session-based and may emit ci_session_saas

## 7) Acceptance Statement
  This contract is the source of truth for API-B semantics and must stay aligned with the runtime behavior of /api/v1/*.
  
---

## docs/DECISION_LOG.md

```md
# Architectural Decision Log - DentaCare Pro Security Hardening

This log records security and architecture decisions that materially affect production posture.

---

## 2026-01-09: Secrets & Dump Management (P0-06)
- Decision: Untrack `.env` and all `.sql` dumps from the repository. Provide `.env.example` and a local setup guide.
- Rationale: Prevent accidental exposure of credentials and sensitive data in VCS history.
- Constraint: Developers create `.env` locally and obtain data dumps out-of-band.

## 2026-01-09: Database Debug Policy (P1-04)
- Decision: `DBDebug` in `app/Config/Database.php` is environment-controlled and defaults to FALSE outside development.
- Rationale: Prevent schema/query leakage in production-like environments.

## 2026-01-09: Script Access Control (P1-04)
- Decision: `/scripts` is CLI-only and blocked at webserver level and application level.
- Rationale: Browser-accessible repair/install scripts are a critical exploit path.

## 2026-01-09: XSS Prevention Policy (P2-01)
- Decision: DOM-string rendering sinks are prohibited for dynamic content:
  - `innerHTML`, `outerHTML`, `insertAdjacentHTML`, and jQuery `.html()`.
- Enforcement: Use `createElement`, `textContent`, `setAttribute`, and safe DOM construction.
- Rationale: Prevent DOM-XSS and persistent XSS.

## 2026-01-10: Global DOM-Sink Remediation Standard (P3-11)
- Decision: All allowlisted high/medium priority views must avoid DOM-string sinks for dynamic content.
- Rationale: Standardization reduces regression risk and makes review mechanical.

## 2026-01-10: Branding Centralization (P4-01)
- Decision: Clinic branding is settings-driven and injected consistently so views do not contain hard-coded tenant branding.
- Rationale: White-labeling must not require code edits.

## 2026-01-10: Secure Logo Upload Policy (P4-02b)
- Decision: Logo upload uses deterministic naming and excludes SVG.
- Decision: Stored logo path is system-managed; never accepted directly from POST.
- Rationale: Prevent path injection and SVG-based script execution.

## 2026-01-11: Print Logo Output Escaping (P4-04)
- Decision: Escape logo URL at output in print views (defense-in-depth).
- Decision: Avoid double-escaping by deriving `logoSrc` from raw stored path and escaping only at output.
- Rationale: Keep valid URLs functional while preserving output safety.

## 2026-01-17: Web Concealment and Airlock (Closure Track)
- Decision (Web concealment): Unauthorized/out-of-plane web access returns 404 (PageNotFoundException), not 403 and not explicit denial messages.
- Decision (Airlock): When `global_mode=true`, tenant web routes fail-closed with 404 and do not redirect into tenant UX.
- Rationale: Prevent cross-plane leakage and privilege boundary probing.

## 2026-01-18 to 2026-01-19: API-B Stateless Redesign (API-C0)
- Decision: `/api/v1/*` is stateless Bearer-token auth; cookie auth is forbidden (API-B-I1).
- Decision: `/api/v1/*` is CSRF-exempt ONLY via path-scoped exemption (`api/v1/*`), leaving web unchanged.
- Decision: API uses deterministic JSON error semantics (API-C0):
  - 401 unauthenticated
  - 403 forbidden (RBAC, tenant_context_forbidden, subscription_inactive)
  - 422 tenant_context_missing/invalid
  - 429 quota/rate-limit
  - No API concealment via 404 for auth/plan/quota/tenant failures
- Decision: Plan/Subscription enforcement returns JSON errors in API context and preserves airlock concealment in web context.
- Rationale: APIs must be machine-stable and fail-closed without redirects or HTML.

## 2026-01-18: Token Policy Addendum (API-B1)
- Decision: Token lookup by stored hash is acceptable for the initial cutover; constant-time comparisons apply where PHP compares secrets.
- Rationale: Minimal stateless auth is required for cutover; deeper cryptographic hardening can follow in B1.1.
