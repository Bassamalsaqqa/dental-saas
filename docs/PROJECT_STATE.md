# Project State

Last updated: 2026-05-05

## Current Status

- **Current Phase:** App API + React/Lovable migration foundation alongside WhatsApp Phase 6 pilot rollout and operations hardening
- **Stability:** Pre-Alpha (Do Not Deploy to production)
- **Active Tracks:** App API contract, imported React/Lovable shell stabilization, finance treasury/custody, provider payouts, supplier payables, inventory COGS, WhatsApp workflows

## Major Modules

- **Patient Management:** profiles, appointments, examinations, odontograms
- **Clinical:** treatments, prescriptions, treatment contracts, signatures
- **Finance:** invoices, payments, expenses, provider payouts, supplier payables, treasury custody, reports
- **Inventory:** stock management, supplier directory, COGS auto-posting, movements ledger
- **WhatsApp:** template-first notification dispatch, webhook ingestion, Phase 6 rollout
- **SaaS Control Plane:** clinics, plans, plan requests, operations console, impersonation
- **RBAC:** permission-based authorization, IonAuth authentication only, no group-based checks
- **App API:** first-party `/app-api/v1/*` session API foundation plus Patients read/write slices

## App API / React Migration State

- `/api/v1/*` remains public/stateless and requires Bearer auth plus `X-Clinic-Id`.
- `/app-api/v1/*` is the first-party React App API using CI4/IonAuth session auth, CSRF, JSON-only responses, and server-controlled `active_clinic_id`.
- `/controlplane-app-api/v1/*` is reserved for future React control-plane APIs.
- Implemented foundation routes: CSRF, login, logout, bootstrap, clinic switch.
- Implemented Patients Slice 1/2 routes: list, detail, create, update.
- Not implemented yet: patient delete, patient workspace, patient summary, timeline, attachments, finance/clinical/odontogram/WhatsApp patient sections.
- Lovable Session 1 React shell is imported under `frontend/` and builds to `public/app/` with Vite base `/app/`.
- The imported shell still uses mock data; real App API wiring and Patients UI integration are later work.
- Current next frontend track: stabilize `/app` static serving and then wire real App API calls in small module slices.
- Lovable must not create Supabase, auth systems, backend business logic, finance calculations, tenant isolation, or production API truth.

## Local Environment

- **Path:** `C:\xampp\htdocs\dental-saas`
- **DB:** `democa_dental_saas` on XAMPP MySQL (127.0.0.1:3306, root, no password)
- **Docker:** `mysql8-dental` container available for dump export (port 3307)
- **Session cookie:** `ci_session_saas`

## Invariants (Do Not Regress)

- CSRF + secure headers globally enforced
- API requests fail-closed with JSON 401/403/422/429; no redirects or HTML errors
- `/api/v1/*` stays public/stateless; do not use it as the logged-in React app API
- `/app-api/v1/*` stays JSON-only and same-origin/session based
- No DOM-string rendering for dynamic content (no innerHTML/outerHTML/insertAdjacentHTML/.html())
- No browser-accessible installers/repair/debug/test scripts
- No secrets or database dumps tracked in git
- Tenant isolation via `clinic_id` on all tenant tables; `TenantAwareModel` fail-closed

## Session Start Checklist

1. `git status -sb` (expect clean)
2. `rg -n "innerHTML|outerHTML|insertAdjacentHTML|\.html\(" app/Views` (expect empty)
3. `bash scripts/ci/saas_guardrails.sh`
4. `php spark routes | findstr /I "controlplane api/v1 api/search"`
5. `php spark routes | findstr /I "app-api api/v1 patients"`

## Where to Look Next

- For SaaS governance: `docs/SaaS/SAAS_PROGRAM.md`
- For API behavior: `docs/API_CONTRACT.md`
- For App API behavior: `docs/APP_API_CONTRACT.md`
- For App API route status: `docs/refactor/APP_API_ROUTE_INVENTORY.md`
- For React/Lovable scope: `docs/refactor/LOVABLE_STRATEGY_CODEX.md`
- For finance details: `docs/FINANCE_CURRENT_STATE.md`
- For WhatsApp status: `docs/WHATSAPP_CURRENT_STATE.md`
- For operations: `docs/OPERATIONS_RUNBOOK.md`
- For decisions: `docs/DECISIONS.md`
- For recent changes: `docs/CHANGELOG.md`
