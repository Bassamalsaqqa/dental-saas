# Implementation Log - DentaCare Pro Security Hardening

This log summarizes implemented work at the task level and points to verification artifacts.
(History is rewritten/normalized to remove prior corruption and duplication.)

---

## 2026-01-09: P0-06 Repo Secret + Dump Hygiene
- Status: Completed
- Summary: `.env` and `.sql` dumps are untracked and ignored; `.env.example` and local setup guidance exist.
- Verification: `docs/verification/P0-06.md`

## 2026-01-09 to 2026-01-10: Phase 2/3 UI Hardening (DOM-XSS Remediation)
- Status: Completed
- Summary: Removed DOM-string sinks from allowlisted views (no innerHTML/outerHTML/insertAdjacentHTML/.html()).
- Tasks:
  - P2-02 Toast component remediation
  - P2-03 Odontogram UI remediation
  - P2-04 Inventory UI remediation
  - P3-01 Notifications UI remediation
  - P3-02 RBAC setup remediation
  - P3-03 Inventory usage history remediation
  - P3-04 User management permissions remediation
  - P3-05 Appointment create remediation
  - P3-06 Appointment edit remediation
  - P3-07 Odontogram interaction remediation
  - P3-08 Patient index modal remediation
  - P3-09 Settings modal remediation
  - P3-10 Appointment index remediation
  - P3-11 Global remaining sinks remediation
- Verification artifacts:
  - `docs/verification/P2-02.md`
  - `docs/verification/P2-03.md`
  - `docs/verification/P2-04.md`
  - `docs/verification/P3-01.md` through `docs/verification/P3-11.md`

## 2026-01-10 to 2026-01-11: Phase 4 Branding Centralization
- Status: Completed
- Summary: Branding is settings-driven (tagline + logo) with safe escaping; secure logo upload pipeline implemented; print views standardized.
- Tasks:
  - P4-01 Branding centralization via settings injection
  - P4-02a Settings-driven branding (tagline + logo path)
  - P4-02b Secure logo upload pipeline (deterministic name, SVG excluded)
  - P4-03 Print branding consistency
  - P4-04 Logo removal + print branding + output escaping
- Verification artifacts:
  - `docs/verification/P4-01.md`
  - `docs/verification/P4-02a.md`
  - `docs/verification/P4-02b.md`
  - `docs/verification/P4-03.md`
  - `docs/verification/P4-04.md`

## 2026-01-17: Closure Track (Control Plane + Airlock)
- Status: Completed
- Summary:
  - CP entry denial uses 404 concealment.
  - Tenant RBAC session values purged on CP entry and CP filter.
  - global_mode airlock enforced: tenant web routes fail-closed with 404.
- Tasks:
  - CP-01 ControlPlane entry concealment
  - CP-02 Tenant RBAC purge
  - CP-03 Airlock routing enforcement
- Verification artifacts:
  - `docs/verification/CP-01.md` (or equivalent captured evidence)
  - `docs/verification/CP-02.md`
  - `docs/verification/CP-03.md`

## 2026-01-19: P5-21 Status Handling / Airlock Verification
- Status: Completed
- Summary: Verified airlock invariants and status handling evidence.
- Verification: `docs/SaaS/verification/P5-21.md`

## 2026-01-18 to 2026-01-19: API-B Stateless Integration API
- Status: Completed (B0-B4)
- Summary:
  - `/api/v1/*` uses Bearer auth and forbids cookie auth (stateless).
  - CSRF is exempt ONLY for `/api/v1/*` via path-scoped Filters.php exception.
  - Tenant context required via `X-Clinic-Id`, membership validated.
  - Plan/Subscription enforcement returns deterministic JSON in API context while web retains airlock concealment.
- Tasks:
  - API-B0-01 Contract definition
  - API-B1-01 Bearer auth + enforcement
  - API-B2-01 CSRF exemption for `/api/v1/*` only
  - API-B3-01 Tenant header enforcement
  - API-B4-01 PlanGuard API semantics (API-C0)
- Verification artifacts:
  - `docs/verification/API-B0-01.md` (contract snapshot / grep evidence)
  - `docs/verification/API-B2-01.md`
  - `docs/verification/API-B4-01.md`
  - Any recorded verbatim curl evidence confirming:
    - `/api/v1/*` no redirect; returns JSON 401/422/200
    - `/api/search/*` remains session-based and emits `ci_session_saas`

## 2026-01-19: API-B4-Search-01 Versioned Stateless Search Endpoints
- Status: Pending verification (runtime outputs required)
- Summary:
  - Added stateless `/api/v1/search/*` endpoints under the API-B auth model.
  - Kept legacy `/api/search/*` session-based as documented.
- Files:
  - `app/Config/Routes.php`
  - `app/Controllers/Api/V1/Search.php`
  - `docs/API_CONTRACT.md`
- Verification (paste verbatim outputs):
  - `curl.exe -i http://localhost/dental-saas/api/v1/search/patients`
  - `curl.exe -i -H "Authorization: Bearer <TOKEN>" http://localhost/dental-saas/api/v1/search/patients`
  - `curl.exe -i -H "Authorization: Bearer <TOKEN>" -H "X-Clinic-Id: 1" "http://localhost/dental-saas/api/v1/search/patients?q=a"`
  - `curl.exe -i http://localhost/dental-saas/api/search/patients`
  - `php spark filter:check GET api/v1/search/patients`
  - `php spark filter:check POST api/v1/search/patients`

