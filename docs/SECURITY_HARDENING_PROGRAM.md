# SECURITY HARDENING & DEPLOYMENT PROGRAM — DentaCare Pro

**Owner:** Bassam Alsaqqa  
**Phase:** 0 — Containment  
**Start Date:** 2026-01-05  
**Last Updated:** 2026-01-09  
**Status:** ACTIVE — NO-SHIP UNTIL COMPLETED

---

## 0. Purpose (Read First)

This program governs **all security, deployment, and documentation changes** required to bring DentaCare Pro to a **defensible production state**.

This is not a feature roadmap.
This is not optional hardening.
This is a **stop-ship recovery program**.

**If this document and the code diverge, the code is wrong.**

---

## 1. Absolute Constraints (Non-Negotiable)

1. No browser-accessible installer, repair, debug, or test scripts exist in production.
2. No secrets (DB creds, encryption keys, admin passwords) are committed or requested interactively.
3. All credentials are **generated**, documented, and rotated on first use.
4. CSRF protection is globally enforced.
5. Secure headers and secure cookies are enforced in production.
6. APIs exposing patient/user data are authenticated and authorized.
7. RBAC is consistent, fail-closed, and non-bypassable.
8. Documentation **must reflect the hardened reality**, not legacy behavior.

Violation of any item = **NO-SHIP**.

---

## 2. Single Source of Truth Rule

This file (`docs/SECURITY_HARDENING_PROGRAM.md`) is the **only authoritative reference** for:

- What is allowed
- What is forbidden
- How deployment works
- How admin access is established
- How testing is performed

All implementation work, reviews, and documentation updates **must reference task IDs defined here**.

---

## 3. Documentation Is Part of the Product

### 3.1 Controlled Documentation Set

The following files are considered **production-critical** and must be updated alongside code changes:

- `docs/index.html`
- `docs/installation.html`
- `docs/user-guide.html`
- `docs/customization.html`
- `docs/troubleshooting.html`

Any instruction in these files that contradicts security posture is a **bug**.

---

## 4. Deployment Model (Authoritative)

### 4.1 Installation Method
- **Browser-based installation is forbidden in production**
- Installation is performed via:
  - CLI command **OR**
  - one-time local bootstrap script removed after use

### 4.2 Credentials Policy
- No user is prompted to choose credentials during installation.
- The system **generates**:
  - Admin email
  - Admin password
  - Application encryption key
- Generated credentials are:
  - Stored securely
  - Output once during installation
  - Documented in deployment docs
  - Forced to rotate on first login

### 4.3 Admin Bootstrap (Canonical)
- One admin account is created automatically.
- Default role: `Super Admin`
- Password reset is enforced at first login.
- Documentation must describe:
  - Where credentials are found
  - How to rotate them
  - How to create additional admins

---

## 5. Phase 0 — Containment Tasks (ACTIVE)

### P0-01 Remove Web-Accessible Dangerous Scripts
**Status:** DONE  
**Scope:**
- Remove or relocate any installer, repair, debug, or test scripts
- Scripts must be CLI-only if retained

**Definition of Done:**
- Direct HTTP access returns 404/403
- Verified via browser and curl
- Documentation no longer references browser installer

---


### P0-02 Block Sensitive Files at Web Server Level
**Status:** DONE  

**Definition of Done:**
- `.env`, `*.sql`, `*.bak`, `install.php`, debug scripts blocked
- Apache/Nginx rules documented in `docs/deployment/`

---


### P0-03 Secrets Removal & Rotation
**Status:** DONE  

**Definition of Done:**
- No secrets in tracked files
- `.env.example` used for documentation
- Rotation steps documented

---


### P0-04 Documentation Rewrite (Phase 0 Scope)
**Status:** DONE  

**Required Changes:**
- `installation.html`:
  - Remove browser installer flow
  - Add CLI/bootstrap flow
  - Document generated credentials
- `index.html`:
  - Remove demo credentials from production path
- `troubleshooting.html`:
  - Add security verification checks
- `user-guide.html`:
  - Add first-login password rotation steps

Documentation must **not** instruct insecure actions.

---


### P0-05 Non-Operational Audit/Review Documentation Sanitization
**Status:** DONE

**Scope (Files):**
- `docs/CODEX_FORENSIC_CODE_AUDIT.md`
- `docs/GEMINI_DEEP_SECURITY_ARCH_REVIEW.md`
- `docs/CODEBASE_REVIEW_FULL_CODEX.md`
- `docs/CODEBASE_REVIEW_FULL.md`
- `docs/codebase-review.md`

**Definition of Done:**
- Narrative-only, risk-focused content; no playbook-like remediation steps.
- No imperative phrasing (e.g., "Run", "Enable", "Remove", "Fix", "Ensure").
- No code fences or code-like snippets.
- No operational markers (URLs, commands, routes, `/api/`, "Reproduction steps", "Test:").
- Placeholders like `[REDACTED_ENDPOINT]`, `[REDACTED_PATH]`, `[REDACTED_COMPONENT]` allowed.

**Verification:**
- `rg -n "^(\s*[-*]\s*)?(Run|Enable|Disable|Remove|Fix|Ensure|Verify|Set|Apply|Configure|Execute)\b" docs/CODEX_FORENSIC_CODE_AUDIT.md docs/GEMINI_DEEP_SECURITY_ARCH_REVIEW.md docs/CODEBASE_REVIEW_FULL_CODEX.md docs/CODEBASE_REVIEW_FULL.md docs/codebase-review.md`
- `rg -n "http://|https://|curl|mysql\b|\brm\b|GET /|POST /|/api/|Reproduction steps|Test:" docs/CODEX_FORENSIC_CODE_AUDIT.md docs/GEMINI_DEEP_SECURITY_ARCH_REVIEW.md docs/CODEBASE_REVIEW_FULL_CODEX.md docs/CODEBASE_REVIEW_FULL.md docs/codebase-review.md`
- `rg -n "```" docs/CODEX_FORENSIC_CODE_AUDIT.md docs/GEMINI_DEEP_SECURITY_ARCH_REVIEW.md docs/CODEBASE_REVIEW_FULL_CODEX.md docs/CODEBASE_REVIEW_FULL.md docs/codebase-review.md`
- Manual review for playbook-like remediation steps.

---


### P0-06 Repo Secret + Dump Hygiene
**Status:** DONE

**Definition of Done:**
- `.env` and `democa_dental.sql` are untracked and in `.gitignore`.
- `.env.example` updated with comprehensive keys and no real secrets.
- `docs/development/LOCAL_SETUP.md` created with secure setup instructions.

**Verification Artifacts:**
- `docs/verification/P0-06.md`

---

## 6. Phase 1 - Hardening Tasks (PLANNED)

**Phase Gate:** NO-SHIP until all Phase 1 tasks are DONE. Fail-closed behavior required.

### P1-01 Global CSRF, Secure Headers, Secure Cookies Enforcement
**Status:** DONE  

**Definition of Done:**
- CSRF protection enforced globally for all state-changing requests.
- Secure headers enabled globally.
- Session cookies configured as secure, httpOnly, and strict/appropriate same-site in production.
- Exceptions are explicit and minimized; no silent bypasses.

**Verification Artifacts:**
- `docs/verification/P1-01.md` with header/cookie inspection results and CSRF failure tests (expected 403/redirect).

---


### P1-02 API Authentication & Data Minimization
**Status:** DONE

**Definition of Done:**
- All API routes that expose patient/user data require authentication and authorization.
- API search endpoints return minimal required fields only.
- Unauthenticated requests fail closed (401/403) without leakage.

**Verification Artifacts:**
- `docs/verification/P1-02.md` with authenticated/unauthenticated API checks and field-minimization evidence.

---


### P1-02a API Fail-Closed AuthN/AuthZ Filter Behavior
**Status:** DONE  

**Definition of Done:**
- API requests return JSON 401 (unauthenticated) or 403 (unauthorized).
- No redirects or HTML responses for API requests.
- Permission exceptions fail closed (403) with no stack trace leakage.
- Non-API request behavior remains unchanged.
- Runtime HTTP evidence is deferred to P1-02a-V; P1-02a may pass on code-level evidence.

**Verification Artifacts:**
- `docs/verification/P1-02a.md` with line-level code excerpts and reasoning that map request type to JSON 401/403 and fail-closed exception behavior.

---


### P1-02a-V Runtime Verification (API AuthN/AuthZ Response Evidence)
**Status:** DONE  

**Definition of Done:**
- Runtime HTTP evidence captured for:
  - Unauthenticated API request returns JSON 401 (no redirect).
  - Authenticated but unauthorized API request returns JSON 403 (no redirect).
  - Non-API request preserves redirect behavior.

**Verification Artifacts:**
- `docs/verification/P1-02a-V.md` containing raw HTTP status lines, Content-Type headers, and JSON body excerpts.

---


### P1-03 RBAC Fail-Closed & Route Coverage
**Status:** DONE  

**Definition of Done:**
- RBAC enforcement is consistent across singular/plural routes.
- Permission checks fail closed on exceptions.
- Test/debug routes are removed or restricted to admin-only/CLI.

**Verification Artifacts:**
- `docs/verification/P1-03.md` with route/filter coverage evidence and unauthorized access checks.

---


### P1-04 Debug Exposure + /scripts HTTP Block
**Status:** DONE

**Definition of Done:**
- `DBDebug` is environment-controlled and defaults to FALSE.
- `/scripts` directory blocked over HTTP via `.htaccess`.
- `scripts/init_database.php` and `scripts/simple_login_test.php` have 403 CLI-only guards.

**Verification Artifacts:**
- `docs/verification/P1-04.md`

---

## 7. Phase 2 — Operational Hardening (PLANNED)

### P2-01 XSS Audit Pass 1 (innerHTML Remediation)
**Status:** DONE

...

---

### P2-02 Toast Component Remediation
**Status:** DONE

...

---

### P2-03 Odontogram UI Remediation
**Status:** DONE

...

---

### P2-04 Inventory UI Remediation
**Status:** DONE

**Objective:**
Remove all dynamic `innerHTML` sinks from the Inventory list view.

**Definition of Done:**
- `app/Views/inventory/index.php` refactored to use safe DOM methods.
- No `innerHTML` or `.html()` remains for dynamic data.
- Button icons preserved via child node caching.
- Table rendering, notifications, and print functionality preserved.

**Verification Artifacts:**
- `docs/verification/P2-04.md`

---

## 8. Verification & Testing (Mandatory)

For each completed task:
- A verification checklist is added under `docs/verification/`
- Includes:
  - curl examples
  - expected HTTP status codes
  - screenshots/log paths if applicable

---


## 8. Implementation Protocol (LLM Governance)

### Codex (Planner & Reviewer)
- Reads this file first
- Selects next task(s)
- Generates Gemini implementation prompt
- Reviews changes against **Definition of Done**
- Updates task status

### Gemini (Implementer)
- Implements only referenced task IDs
- Updates code **and documentation**
- Appends to `docs/IMPLEMENTATION_LOG.md`
- Does not introduce temporary bypasses

---


## 9. Logs (Append Only)

### Decision Log
`docs/DECISION_LOG.md`

### Implementation Log
`docs/IMPLEMENTATION_LOG.md`

Each session must leave a trace.

---


## 10. Current Priority Order
1. P0-01 Remove dangerous scripts
2. P0-02 Block sensitive files
3. P0-03 Secrets rotation
4. P0-04 Documentation rewrite
5. P0-05 Non-operational audit/review docs
6. P0-06 Repo secret + dump hygiene (DONE)
7. P1-01 Global CSRF, secure headers, secure cookies
8. P1-02a API fail-closed authn/authz filters
9. P1-02a-V runtime API authn/authz verification
10. P1-02 API authentication & data minimization
11. P1-03 RBAC fail-closed & route coverage
12. P1-04 Debug exposure + /scripts HTTP block (DONE)
13. P2-01 XSS audit pass 1 (remove unsafe innerHTML sinks) (DONE)
14. P2-02 Toast component remediation (remove innerHTML) (DONE)
15. P2-03 Odontogram UI remediation (remove innerHTML) (DONE)
16. P2-04 Inventory UI remediation (remove innerHTML) (DONE)