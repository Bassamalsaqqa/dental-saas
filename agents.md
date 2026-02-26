# Codex Operating Guide - Dental SaaS

## A) Role
- Primary mission: execute requested changes cleanly, keep SaaS spec alignment, and avoid regressions.
- Secondary mission: leave an audit-grade trail (commands, diffs, tests) and enforce tenant isolation.

## B) Source of Truth
1) `docs/SaaS/SAAS_PROGRAM.md` governs SaaS scope, priority, and DoD.
2) `docs/SaaS/TENANT_CONTEXT_CONTRACT.md` governs tenant context mechanics.
3) `docs/API_CONTRACT.md` governs `/api/v1/*` semantics.
4) Guardrails: `docs/SaaS/guardrails/*` + repo guardrail scripts.
5) WhatsApp implementation package (current canonical set):
   - `docs/WHATSAPP_IMPLEMENTATION_PLAN_V5.md`
   - `docs/SaaS/whatsapp_provider_facts.md`
   - `docs/SaaS/PHASE_0_EXECUTION_CHECKLIST.md`

`docs/SECURITY_HARDENING_PROGRAM.md` is historical unless explicitly reopened.

## C) Non-Negotiables
- Fail-closed doctrine always.
- No redirects/HTML for `/api/*`.
- Airlock separation enforced: control plane vs tenant plane.
- Control plane requires `global_mode=true` (TenantFilter).
- Evidence-based verification (paths, diffs, rg output, curl output when relevant).

## D) Session Start Checklist
1) Read `docs/SaaS/SAAS_PROGRAM.md` + latest logs:
   - `docs/SaaS/logs/IMPLEMENTATION_LOG_SaaS.md`
   - `docs/SaaS/logs/DECISION_LOG_SaaS.md`
2) Read current WhatsApp package and confirm active phase:
   - `docs/WHATSAPP_IMPLEMENTATION_PLAN_V5.md`
   - `docs/SaaS/whatsapp_provider_facts.md`
   - `docs/SaaS/PHASE_0_EXECUTION_CHECKLIST.md`
   - Active phase must be treated as: **Phase 0 - Notifications hardening (pre-WhatsApp implementation)** unless explicitly changed in docs.
3) Drift scans:
   - `rg -n "innerHTML|outerHTML|insertAdjacentHTML|\.html\(" app/Views`
   - `php spark routes | findstr /I "controlplane api/v1 api/search"`
   - `bash scripts/ci/saas_guardrails.sh`
4) Confirm clean tree: `git status -sb`

## E) Environment & Infrastructure
**Local Path:** `C:\xampp\htdocs\dental-saas`

1) MySQL Docker
- Container: `mysql8-dental`
- Image: `mysql:8.0`
- Port: `0.0.0.0:3307 -> 3306`

2) Local DB (source for dumps)
- DB: `democa_dental_saas`
- Host: `127.0.0.1`
- Port: `3307`
- User/Pass: `root` / `root`

3) Session
- Cookie: `ci_session_saas`

## F) Deployment Notes (Hostinger)
- Hostinger needs the SQL dump **without CREATE DATABASE**. Use UTF-8.
- Latest dump: `democa_dental_saas_schema_superadmin.sql`.
- If import errors mention "Unexpected character" at position 1, the file was UTF-16. Re-generate using `cmd` redirection.
- `.htaccess`: on Hostinger, `RewriteBase /` is required (not `/dental-saas/`).
- Only manual items after deploy: `.env` + latest SQL dump.

## G) How to Regenerate SQL Dump (UTF-8, no CREATE DATABASE)
Run from repo root (uses docker container):
```
cmd /c "docker exec mysql8-dental mysqldump -u root -proot --default-character-set=utf8mb4 --routines --triggers --events --single-transaction --skip-lock-tables --set-charset --no-create-db democa_dental_saas --ignore-table=democa_dental_saas.control_plane_invoices --ignore-table=democa_dental_saas.control_plane_invoice_payments > democa_dental_saas_schema_superadmin.sql"
```

## H) Recent Changes Snapshot (Feb 2026)
- **Control Plane Operations** rebuilt as an ops console (health checks + tooling). Plan enforcement table restyled to match clinic-plane tables.
- **Control Plane Invoices**: added delete action (only draft/void/cancelled), modal confirm, route `POST /controlplane/invoices/{id}/delete`.
- **Control Plane Defaults**: fixed time format persistence by saving `default_time_format` as string.
- **Kiosk Settings**: toggle now saves immediately via AJAX; track color toggles; server responds JSON on AJAX errors.
- **RBAC**: dashboard permission is `dashboard:view`; exam treatment plan button checks `treatments:create`.
- **Finance Provider Split / Payouts**: implemented `provider_payouts` + `provider_payout_payments`, finance create/edit split controls, payout ledger page (`/finance/payouts`), and payout payment actions.
- **Finance Stats Accounting Basis**: `/finance/stats` now exposes both accrual and cash-aware payout metrics:
  - `provider_share_total` (accrual)
  - `provider_paid_cash_total` (cash paid out)
  - `provider_payable_outstanding`
  - `net_clinic_revenue` (cash view)
  - `net_clinic_revenue_accrual`
- **Finance Reports Consistency**: `/reports?report_type=finance` now uses consistent start/end date filtering across transactions, payment methods, outstanding invoices, and monthly revenue; report cards use payout-aware values.
- **Finance Create UX**: check-scan upload section is now always visible and optional; backend still only processes file if uploaded/valid.
- **Tenant Finance Expansion**: tenant finance now supports `Add Expense` (alongside `Add Income`) with expense metadata (`expense_category`, `counterparty_name`), expense-aware index/show/reports, and finance API tenant ownership validation for patient/examination references.
- **Inventory ↔ Finance (COGS)**: inventory usage can auto-post tenant finance `inventory_cogs` expenses (idempotent source refs), controlled by tenant setting `finance_auto_post_inventory_cogs`; usage history/details link to generated finance entries.
- **Inventory Operations Refinement**: added tenant supplier directory + quick-add, stock movement ledger, receive-stock workflow, starter inventory templates/import, supplier-item mappings (SKU/pack/preferred reorder qty/last price), reorder list with supplier contact shortcuts, global movement ledger page (`/inventory/movements`), and batch/lot tracking foundation (`inventory_batches` + optional lot/expiry capture on receive).

## J) Current Improvement Phase (Authoritative)
- Current phase: **Phase 0 - Notifications Hardening (pre-WhatsApp implementation)**.
- Execution runbook: `docs/SaaS/PHASE_0_EXECUTION_CHECKLIST.md`.
- Provider behavior registry: `docs/SaaS/whatsapp_provider_facts.md` (confidence-tiered, update date required).
- Implementation architecture plan: `docs/WHATSAPP_IMPLEMENTATION_PLAN_V5.md`.
- Do not start Phase 1+ until Phase 0 exit criteria and verification evidence are complete.

## I) Known Requirements / Guardrails
- Keep `democa_dental_saas_schema_superadmin.sql` updated whenever schema or bootstrap data changes.
- Ensure IonAuth default group `members` exists in dumps to avoid "Invalid default group name" errors.
- No DOM sinks in `app/Views` (guardrails enforce this).
- After pulling latest finance changes, run migrations to ensure payout tables exist:
  - `php spark migrate`
