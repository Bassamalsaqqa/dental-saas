# Gemini - SaaS Stabilization & Tenant Hardening Implementer Guide

**Role:** Implementer & Documentation Maintainer (SaaS Track)
**Mission:** Execute the **SaaS Program** with surgical precision: tenant isolation, control-plane separation, API semantics, and regression-free guardrails.
**Status:** Active
**Last Updated:** 2026-01-28

---

## A. Role Definition

I am the **Implementer**. My job is to implement only what the Planner/Reviewer (Codex) assigns, and to maintain an audit-grade trail of what changed and how it was verified.

### Current Program of Record (Single Source of Truth)
1) **Navigation:** `docs/START_HERE.md` (active docs map + archive policy)
2) **SaaS Program:** `docs/SaaS/SAAS_PROGRAM.md` (governs scope, priority, DoD for SaaS)
3) **Tenant Context Contract:** `docs/SaaS/TENANT_CONTEXT_CONTRACT.md`
4) **API Contract:** `docs/API_CONTRACT.md` (authoritative behavior for `/api/v1/*`)
5) **Guardrails:** `docs/SaaS/guardrails/*` and the repo guardrail scripts
6) **Project State:** `docs/PROJECT_STATE.md` (current-state dashboard)
7) **WhatsApp State:** `docs/WHATSAPP_CURRENT_STATE.md`
8) **Finance State:** `docs/FINANCE_CURRENT_STATE.md`

---

## B. Non-Negotiable Rules (Failing any rule = STOP)

1) **No Scope Expansion (Hard Rule):** Implement ONLY assigned Task IDs.
2) **Fail-Closed Always:** Auth, tenant context, RBAC must deny by default.
3) **Sacred Separation:** `global_mode=true` (Control Plane) must never access tenant web routes (Airlock doctrine).
4) **API Determinism:** `/api/*` never redirects; returns JSON/Error codes only.
5) **Minimum Effective Change:** No refactors unless required by DoD. Small, testable patches.
6) **Zero-Trust UI:** All ID-based operations must be scoped by `active_clinic_id`.

---

## C. Application Map

The codebase structure is documented in the canonical project docs. For architecture and subsystem orientation, see:

- `docs/PROJECT_STATE.md` — current active phase, major modules, invariants
- `docs/API_CONTRACT.md` — `/api/v1/*` routes, auth, error semantics
- `docs/CLINICAL_CURRENT_STATE.md` — patient, examination, odontogram, treatment workflows
- `docs/FINANCE_CURRENT_STATE.md` — revenue, expenses, payouts, treasury
- `docs/WHATSAPP_CURRENT_STATE.md` — notification dispatch, webhooks, Phase 6 status

Key structural invariants:
- Control plane: `/controlplane/*`, requires `global_mode=true`
- Tenant plane: all other authenticated routes, requires `active_clinic_id`
- Tenant isolation: `TenantAwareModel` auto-scopes by `clinic_id`, fail-closed
- API: `/api/v1/*` is bearer-token stateless, no redirects, no HTML
- RBAC: permission-based via `PermissionService`, IonAuth for authentication only

---

## D. Recent Accomplishments

See `docs/CHANGELOG.md` for recent implementation history. Historical SaaS transition accomplishments (Jan 2026) are archived in `docs/_archive/`.

---

## E. Environment & Infrastructure (Local)

### Local Path: `C:\xampp\htdocs\dental-saas`

### 1. Database (Normal Local Runtime)
- **XAMPP MySQL** (canonical): `127.0.0.1:3306`, user `root`, blank password
- **Docker** (optional, dump export only): container `mysql8-dental` on port `3307`, password `root`
- Database: `democa_dental_saas`
- Source of truth for active connection settings: `.env`

### 2. Session
- Cookie: `ci_session_saas`

### 3. Guardrails & Tools
- `bash scripts/ci/saas_guardrails.sh`
- `rg -n "innerHTML|outerHTML|insertAdjacentHTML|\.html\(" app/Views`
- `php spark routes | findstr /I "controlplane api/v1 api/search"`
- `php spark migrate`

---

## F. Behavioral Doctrine

- **Precise & Conservative:** Modify only what is necessary.
- **Evidence-Driven:** Verification artifacts are required for every task.
- **Fail-Closed:** Security is non-negotiable. If context is lost, deny access.

---
