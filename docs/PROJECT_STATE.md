# Project State

Last updated: 2026-05-02

## Current Status

- **Current Phase:** WhatsApp Phase 6 — Pilot rollout & operations hardening (Phases 0-5 completed)
- **Stability:** Pre-Alpha (Do Not Deploy to production)
- **Active Tracks:** Finance treasury/custody, provider payouts, supplier payables, inventory COGS, WhatsApp workflows

## Major Modules

- **Patient Management:** profiles, appointments, examinations, odontograms
- **Clinical:** treatments, prescriptions, treatment contracts, signatures
- **Finance:** invoices, payments, expenses, provider payouts, supplier payables, treasury custody, reports
- **Inventory:** stock management, supplier directory, COGS auto-posting, movements ledger
- **WhatsApp:** template-first notification dispatch, webhook ingestion, Phase 6 rollout
- **SaaS Control Plane:** clinics, plans, plan requests, operations console, impersonation
- **RBAC:** permission-based authorization, IonAuth authentication only, no group-based checks

## Local Environment

- **Path:** `C:\xampp\htdocs\dental-saas`
- **DB:** `democa_dental_saas` on XAMPP MySQL (127.0.0.1:3306, root, no password)
- **Docker:** `mysql8-dental` container available for dump export (port 3307)
- **Session cookie:** `ci_session_saas`

## Invariants (Do Not Regress)

- CSRF + secure headers globally enforced
- API requests fail-closed with JSON 401/403/422/429; no redirects or HTML errors
- No DOM-string rendering for dynamic content (no innerHTML/outerHTML/insertAdjacentHTML/.html())
- No browser-accessible installers/repair/debug/test scripts
- No secrets or database dumps tracked in git
- Tenant isolation via `clinic_id` on all tenant tables; `TenantAwareModel` fail-closed

## Session Start Checklist

1. `git status -sb` (expect clean)
2. `rg -n "innerHTML|outerHTML|insertAdjacentHTML|\.html\(" app/Views` (expect empty)
3. `bash scripts/ci/saas_guardrails.sh`
4. `php spark routes | findstr /I "controlplane api/v1 api/search"`

## Where to Look Next

- For SaaS governance: `docs/SaaS/SAAS_PROGRAM.md`
- For API behavior: `docs/API_CONTRACT.md`
- For finance details: `docs/FINANCE_CURRENT_STATE.md`
- For WhatsApp status: `docs/WHATSAPP_CURRENT_STATE.md`
- For operations: `docs/OPERATIONS_RUNBOOK.md`
- For decisions: `docs/DECISIONS.md`
- For recent changes: `docs/CHANGELOG.md`
