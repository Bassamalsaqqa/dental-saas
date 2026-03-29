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
   - Active phase is: **Phase 6 - Pilot rollout & operations hardening** (Phases 0-5 completed; see WhatsApp checkpoint/verification docs).
3) Drift scans:
   - `rg -n "innerHTML|outerHTML|insertAdjacentHTML|\.html\(" app/Views`
   - `php spark routes | findstr /I "controlplane api/v1 api/search"`
   - `bash scripts/ci/saas_guardrails.sh`
4) Confirm clean tree: `git status -sb`

## E) Environment & Infrastructure
**Local Path:** `C:\xampp\htdocs\dental-saas`

1) Local Database (authoritative local source)
- DB: `democa_dental_saas`
- Host: `127.0.0.1`
- Port: `3306`
- User: `root`
- Password: blank
- Source of truth for active connection settings: `.env`

2) Session
- Cookie: `ci_session_saas`

## F) Deployment Notes (Hostinger)
- Hostinger needs the SQL dump **without CREATE DATABASE**. Use UTF-8.
- Latest dump: `democa_dental_saas_schema_superadmin.sql`.
- If import errors mention "Unexpected character" at position 1, the file was UTF-16. Re-generate using `cmd` redirection.
- `.htaccess`: on Hostinger, `RewriteBase /` is required (not `/dental-saas/`).
- Only manual items after deploy: `.env` + latest SQL dump.

## G) How to Regenerate SQL Dump (UTF-8, no CREATE DATABASE)
Run from repo root using the local XAMPP MySQL client:
```
cmd /c "\"C:\xampp\mysql\bin\mysqldump.exe\" -h 127.0.0.1 -P 3306 -u root --default-character-set=utf8mb4 --routines --triggers --events --single-transaction --skip-lock-tables --set-charset --no-create-db democa_dental_saas --ignore-table=democa_dental_saas.control_plane_invoices --ignore-table=democa_dental_saas.control_plane_invoice_payments > democa_dental_saas_schema_superadmin.sql"
```

## H) Recent Changes Snapshot (Mar 2026)
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
- **Finance Reporting Canonicalization**: `/finance/reports` is now the authoritative finance reporting surface. Generic `/reports` no longer exposes finance as a first-class report type; legacy finance report URLs redirect into `/finance/reports`.
- **Finance Reporting Model**: `/finance/reports` now leads with the owner-facing 5 metrics:
  - `Money In`
  - `Money Out`
  - `Customers Owe Us`
  - `We Owe Others`
  - `Net Position`
- **Finance Treasury / Custody Layer**: implemented custody buckets and transfer tracking:
  - `safe_cash`
  - `bank`
  - `undeposited_checks`
  - transfer workspace: `/finance/transfers`
  - supported transfer types:
    - `cash_deposit_to_bank`
    - `bank_withdrawal_to_safe`
    - `check_deposit_to_bank`
    - `internal_transfer`
- **Finance Payables Expansion**: tenant finance now supports unpaid and partially paid expenses using settlement entries, plus A/P aging and treasury-aware reporting.
- **Finance Create UX**: check-scan upload section is now always visible and optional; backend still only processes file if uploaded/valid.
- **Tenant Finance Expansion**: tenant finance now supports `Add Expense` (alongside `Add Income`) with expense metadata (`expense_category`, `counterparty_name`), expense-aware index/show/reports, and finance API tenant ownership validation for patient/examination references.
- **Inventory <-> Finance (COGS)**: inventory usage can auto-post tenant finance `inventory_cogs` expenses (idempotent source refs), controlled by tenant setting `finance_auto_post_inventory_cogs`; usage history/details link to generated finance entries.
- **Inventory Operations Refinement**: added tenant supplier directory + quick-add, stock movement ledger, receive-stock workflow, starter inventory templates/import, supplier-item mappings (SKU/pack/preferred reorder qty/last price), reorder list with supplier contact shortcuts, global movement ledger page (`/inventory/movements`), and batch/lot tracking foundation (`inventory_batches` + optional lot/expiry capture on receive).

## J) Current Improvement Phase (Authoritative)
- Current phase: **Phase 6 - Pilot rollout & operations hardening**.
- Phase 6 readiness monitor: `php spark whatsapp:ops-check`.
- Execution baseline docs:
  - `docs/SaaS/verification/WHATSAPP-CHECKPOINT-2026-02-26.md`
  - `docs/SaaS/verification/WHATSAPP-PHASE5-UI-OPS.md`
- Provider behavior registry: `docs/SaaS/whatsapp_provider_facts.md` (confidence-tiered, update date required).
- Implementation architecture plan: `docs/WHATSAPP_IMPLEMENTATION_PLAN_V5.md`.
- Remaining Phase 6 work: pilot rollout execution, template-first enqueue flows, and alerting/runbook hardening.

## K) Current Finance Reporting Context
- Canonical finance reporting surface:
  - `/finance/reports`
- Treasury operations surface:
  - `/finance/transfers`
- Current owner-summary formulas in `/finance/reports`:
  - `money_in` = collected inflows for the selected period
  - `money_out` = refunds + paid expenses + paid provider payouts for the selected period
  - `customers_owe_us` = A/R snapshot as of the selected end date
  - `we_owe_others` = expense payables + provider payout balances as of the selected end date
  - `net_position` = `money_in - money_out + customers_owe_us - we_owe_others`
- Current treasury balances shown in `/finance/reports`:
  - closing safe cash
  - closing bank cash
  - checks on hand
  - total liquid funds
- Current supported custody buckets:
  - `safe_cash`
  - `bank`
  - `undeposited_checks`
- Current transfer types:
  - `cash_deposit_to_bank`
  - `bank_withdrawal_to_safe`
  - `check_deposit_to_bank`
  - `internal_transfer`
- Important accounting date rules:
  - `transaction_date` = business/document date
  - `payment_date` / `paid_date` = settlement/cash date
  - `created_at` = audit timestamp only
- Important: treasury closing balances are only as accurate as the recorded custody events and internal transfers.
- Future treasury improvements stay documented only for now:
  - opening balances
  - counted cash variance
  - bank reconciliation
  - bounced checks
  - recurring bill/vendor workflows

## I) Known Requirements / Guardrails
- Keep `democa_dental_saas_schema_superadmin.sql` updated whenever schema or bootstrap data changes.
- Ensure IonAuth default group `members` exists in dumps to avoid "Invalid default group name" errors.
- No DOM sinks in `app/Views` (guardrails enforce this).
- After pulling finance or messaging schema changes, run migrations:
  - `php spark migrate`
