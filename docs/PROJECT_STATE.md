# Project State

Last updated: 2026-07-30

## Current Status

- **Current Phase:** App API + React/Lovable migration foundation alongside WhatsApp Phase 6 pilot rollout and operations hardening
- **Stability:** Pre-Alpha (Do Not Deploy to production)
- **Active Tracks:** App API contract, imported React/Lovable shell stabilization, finance treasury/custody, provider payouts, supplier payables, inventory COGS, WhatsApp workflows

## Major Modules

- **Patient Management:** profiles, appointments, examinations, odontograms, patient-centered command workspace quick actions
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
- Ongoing Lovable UI/UX work continues in the separate `Bassamalsaqqa/dentacare-shell` repo; `frontend/` is a reviewed snapshot only.
- The imported shell still uses mock data; real App API wiring and Patients UI integration are later work.
- Current next frontend track: stabilize `/app` static serving and then wire real App API calls in small module slices.
- Lovable must not create Supabase, auth systems, backend business logic, finance calculations, tenant isolation, or production API truth.

## App-Wide UX Pattern

- Entity-centered command workspaces are now an app feature pattern, not a one-off patient tweak.
- Finance and clinical-money UX simplification follows `docs/refactor/FINANCE_WORKFLOW_SIMPLIFICATION_PLAN.md`: daily clinic actions stay simple, while allocations, treasury, payables, payouts, and reporting remain available in specialist finance layers.
- Patient encounter and odontogram behavior follows `docs/refactor/PATIENT_CLINICAL_WORKFLOW_PLAN.md`: baseline charting, visit overrides, treatment, checkout, and deferred clinical capabilities remain explicitly separated.
- Cross-module optimization follows `docs/refactor/PRODUCT_WORKFLOW_OPTIMIZATION_ROADMAP.md`: close the Visit, Care Plan, Checkout, Recall, Supply, Communication, and Governance loops before adding broad new modules.
- Visit workflow Phase 1A is implemented: appointment stages now follow a server-owned transition contract from scheduled through checkout/completion, with tenant-scoped writes, activity logging, no-show dispatch, localized UI, and compatibility wrappers for existing appointment actions.
- Visit workflow Phase 1B is implemented on the tenant dashboard: today’s appointments are grouped into Expected, Waiting, Chairside, Checkout, and Needs attention, with permission-aware primary handoffs, late-arrival signals, completed counts, and future-only upcoming appointments.
- Phase 1 production clinic-day QA on `denta.palsync.net` is intentionally deferred for later manual testing; it remains a release-readiness check rather than an architecture blocker.
- Procedure lifecycle Phase 2A is implemented as a backend foundation: tenant-scoped procedure state, append-only status history, validated patient/clinical relationships, explicit forward transitions, actor/timestamp attribution, and no automatic legacy backfill or downstream side effects.
- Procedure lifecycle Phase 2B is implemented for new Treatments: explicit plan/start intent, one-to-one procedure mapping, atomic inventory/charge/procedure creation, safe edit synchronization, completion mirroring, read-only Treatment status, and legacy compatibility without ambiguous backfill.
- Deferred Phase 2B manual QA: test full-page and quick-create Treatment flows in English/Arabic with planned/start-now intent, optional charge/inventory capture, completion, and linked-treatment delete protection.
- Checkout Phase 3A is implemented on Appointment detail as a read-only, tenant-scoped review of clinical work, captured charges, and payment state. It reuses canonical clinical/finance actions, blocks mixed-currency combined invoicing, and labels legacy patient/date matching as contextual.
- Deferred Phase 3A manual QA: test Appointment checkout in English/Arabic and under view-only/edit permissions across no-work, open-charge, mixed-currency, invoiced, balance-due, and settled scenarios.
- Next workflow slice: Phase 3B explicit appointment linkage for newly created procedures and charge lines; do not automate billing or appointment completion.
- The current PHP Patient Profile is the reference first slice: contextual drawers can start related work while canonical modules still own validation, writes, redirects, permissions, tenant checks, audit logging, and finance math.
- When applying the pattern to another entity page, reuse the rules in `docs/refactor/ENTITY_CENTERED_COMMAND_WORKSPACE_PLAN.md`.
- Do not copy patient actions blindly to provider, user, supplier, finance, inventory, or appointment pages; each entity gets actions that match its domain.
- Deferred manual QA reminder: when testing time is available, run the full clinical-finance chain end to end: treatment -> invoice -> patient payment -> patient profile trace -> treatment trace -> finance trace -> provider payout payment -> provider/user payout ledger.

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
