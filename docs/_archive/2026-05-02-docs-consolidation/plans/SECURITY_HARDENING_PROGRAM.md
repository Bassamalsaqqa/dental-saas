# SECURITY HARDENING & DEPLOYMENT PROGRAM — DentaCare Pro

Owner: Bassam Alsaqqa
Start Date: 2026-01-05
Last Updated: 2026-01-19

This program governs security, deployment, and documentation changes required to reach a defensible production posture.
This is not a feature roadmap. This is a stop-ship recovery program.

If this document and the code diverge, the code is wrong.

---

## 1) Absolute Constraints (Non-Negotiable)
1) No browser-accessible installer, repair, debug, or test scripts exist in production.
2) No secrets (DB creds, encryption keys, admin passwords) are committed or requested interactively.
3) Credentials are generated, documented, and rotated on first use.
4) CSRF protection is globally enforced for web state-changing routes.
5) Secure headers and secure cookies are enforced in production posture.
6) APIs exposing patient/user data are authenticated and authorized.
7) RBAC is consistent, fail-closed, and non-bypassable.
8) Documentation reflects hardened reality, not legacy behavior.

Violation of any item = NO-SHIP.

---

## 2) Single Source of Truth
This file is authoritative for:
- what is allowed/forbidden
- deployment posture
- admin bootstrap
- testing expectations
- task IDs and phase gates

All implementation work must reference task IDs defined here.

---

## 3) Documentation Is Part of the Product
Production-critical docs that must match the hardened posture:
- `docs/index.html`
- `docs/installation.html`
- `docs/user-guide.html`
- `docs/customization.html`
- `docs/troubleshooting.html`

---

## 4) Deployment Model (Authoritative)
### 4.1 Installation Method
- Browser-based installation is forbidden in production.
- Installation is performed via CLI or one-time local bootstrap (removed after use).

### 4.2 Credentials Policy
- No interactive choice of credentials during installation.
- System generates:
  - admin email
  - admin password
  - application encryption key
- Credentials are output once, stored securely, and rotated on first login.

### 4.3 Admin Bootstrap (Canonical)
- One admin account exists by default.
- Role: Super Admin
- Forced password rotation on first login.

---

## 5) Phase 0 — Containment (DONE)
### P0-01 Remove Web-Accessible Dangerous Scripts — DONE
- DoD: HTTP access blocked; any retained scripts are CLI-only.
- Verification: `docs/verification/P0-01.md` (or equivalent evidence)

### P0-02 Block Sensitive Files at Web Server Level — DONE
- DoD: `.env`, `*.sql`, backups, install artifacts blocked via server config.
- Verification: `docs/verification/P0-02.md`

### P0-03 Secrets Removal & Rotation — DONE
- DoD: no secrets in tracked files; rotation documented.
- Verification: `docs/verification/P0-03.md`

### P0-04 Documentation Rewrite (Phase 0 scope) — DONE
- DoD: docs remove browser installer flow; reflect CLI/bootstrap; remove demo creds; add security verification checks.
- Verification: `docs/verification/P0-04.md`

### P0-05 Non-Operational Audit/Review Docs Sanitization — DONE
- DoD: review docs are narrative-only; no operational playbooks or code fences.
- Verification: `docs/verification/P0-05.md`

### P0-06 Repo Secret + Dump Hygiene — DONE
- DoD: `.env` and `democa_dental.sql` untracked and ignored; `.env.example` + secure setup docs exist.
- Verification: `docs/verification/P0-06.md`

---

## 6) Phase 1 — Hardening (DONE)
### P1-01 Global CSRF, Secure Headers, Secure Cookies — DONE
- Verification: `docs/verification/P1-01.md`

### P1-02 API Authentication & Data Minimization — DONE
- Verification: `docs/verification/P1-02.md`

### P1-02a API Fail-Closed AuthN/AuthZ Filter Behavior — DONE
- Verification: `docs/verification/P1-02a.md`

### P1-02a-V Runtime Verification — DONE
- Verification: `docs/verification/P1-02a-V.md`

### P1-03 RBAC Fail-Closed & Route Coverage — DONE
- Verification: `docs/verification/P1-03.md`

### P1-04 Debug Exposure + /scripts HTTP Block — DONE
- Verification: `docs/verification/P1-04.md`

---

## 7) Phase 2 — Operational UI Hardening (DONE)
- P2-01 XSS Audit Pass 1 — DONE
- P2-02 Toast Component Remediation — DONE
- P2-03 Odontogram UI Remediation — DONE
- P2-04 Inventory UI Remediation — DONE
Verification: `docs/verification/P2-01.md` through `docs/verification/P2-04.md`

---

## 8) Phase 3 — Global UI Hardening (DONE)
- P3-01 through P3-11 — DONE
Verification: `docs/verification/P3-01.md` through `docs/verification/P3-11.md`

---

## 9) Phase 4 — Branding Centralization (DONE)
- P4-01 Branding centralization — DONE
- P4-02a Settings-driven branding — DONE
- P4-02b Secure logo upload pipeline — DONE
- P4-03 Print branding consistency — DONE
- P4-04 Logo removal + print logo escaping — DONE
Verification: `docs/verification/P4-01.md` through `docs/verification/P4-04.md`

---

## 10) Closure Track — Airlock Invariants (DONE)
Purpose: ensure cross-plane boundaries are not probeable and fail closed.

### CP-01 ControlPlane Entry Concealment — DONE
- DoD: denial is 404 PageNotFoundException; no explicit denial messages.

### CP-02 Session Purge Completeness — DONE
- DoD: purge tenant session keys when entering control plane.

### CP-03 Tenant Airlock Enforcement — DONE
- DoD: when `global_mode=true`, tenant web routes fail-closed with 404 and do not redirect.

Evidence: `docs/verification/CP-01.md`, `docs/verification/CP-02.md`, `docs/verification/CP-03.md` (or equivalent captured proof)

### P5-21 Status Handling (Verification) — DONE
Evidence: `docs/SaaS/verification/P5-21.md`

Stop-Ship Conditions (must remain false):
- Any 403 or explicit denial message on CP entry.
- Any redirect into tenant UX while `global_mode=true`.
- Any tenant session key surviving CP entry.

---

## 11) Phase API-B — Stateless Integration API (DONE: B0-B4)
Purpose: convert `/api/v1/*` from legacy cookie-session patterns to stateless Bearer auth with explicit tenant context and deterministic JSON errors.

Invariant (API-B-I1):
- `/api/v1/*` must not authenticate via cookies.

### API-B0-01 Contract Definition — DONE
- Verification: `docs/verification/API-B0-01.md` and `docs/API_CONTRACT.md`

### API-B1-01 Bearer Auth Filter — DONE
- DoD: Bearer required; cookie-only auth rejected.

### API-B1-02 Token Issuance + Revocation Docs — DONE (or explicitly Not Implemented where applicable)
- DoD: docs reflect code reality only.

### API-B2-01 CSRF Exemption for `/api/v1/*` — DONE
- DoD: CSRF exemption must be path-scoped to `api/v1/*` only; does not affect web or `/api/search/*`.
- Verification: `docs/verification/API-B2-01.md`

### API-B3-01 Tenant Header Required — DONE
- DoD: `X-Clinic-Id` enforced; membership validated; JSON 422/403 as required.

### API-B4-01 PlanGuard API Semantics (API-C0) — DONE
- DoD: subscription/quota failures return JSON semantics for API; web retains airlock concealment behavior.
- Verification: `docs/verification/API-B4-01.md`

### API-B5-01 Rate Limiting — OPTIONAL (NOT STARTED)
- DoD: explicit policy + enforcement + verification.

---

## 12) Governance (Roles)
### Codex (Planner & Reviewer)
- reads this file first
- selects next tasks
- generates implementation prompts
- reviews changes strictly against DoD and invariants
- blocks merges on scope drift or invented behavior

### Gemini (Implementer)
- implements only referenced task IDs
- updates code + docs
- produces verification artifacts
- does not introduce temporary bypasses

### Owner (You)
- runs final smoke tests and sanity checks
- decides merge/revert
- ensures docs remain aligned with runtime behavior
