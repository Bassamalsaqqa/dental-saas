# Changelog

Recent implementation history for work continuation. Full historical logs are archived in `docs/_archive/2026-05-02-docs-consolidation/legacy-root-logs/`.

## 2026-04 — Finance: Provider Compensation & Supplier Payables

- Implemented provider payouts + provider payout payments
- Finance create/edit split controls, payout ledger page (`/finance/payouts`)
- Payout payment actions
- Finance stats: accrual and cash-aware payout metrics
- Supplier payables expansion: unpaid/partially paid expenses, settlement entries, A/P aging

## 2026-03-29 — Finance: Treasury Custody Layer

- Custody buckets: `safe_cash`, `bank`, `undeposited_checks`
- Transfer workspace: `/finance/transfers`
- Transfer types: `cash_deposit_to_bank`, `bank_withdrawal_to_safe`, `check_deposit_to_bank`, `internal_transfer`
- `/finance/reports` canonicalized as authoritative finance reporting surface
- Owner-facing 5 metrics: Money In, Money Out, Customers Owe Us, We Owe Others, Net Position

## 2026-03 — Finance: Payables & COGS

- Tenant finance supports `Add Expense` alongside `Add Income`
- Expense metadata: `expense_category`, `counterparty_name`
- Inventory COGS auto-posting: inventory usage can auto-post tenant finance `inventory_cogs` expenses
- Tenant setting `finance_auto_post_inventory_cogs` controls behavior

## 2026-03 — WhatsApp Phases 1-6 (Partial)

- Phases 1-5 completed: schema, unified dispatcher, outbound client, webhooks, UI/ops
- Phase 6 partial: template-first workflow enqueueing wired for appointment confirmations, queued reminders, follow-up, no-show recovery, invoice reminders
- Commands: `php spark whatsapp:queue-product-flows`, `php spark notifications:dispatch --channel whatsapp`
- Remaining Phase 6: alerting/runbook hardening, broader workflow coverage

## 2026-03 — Inventory Operations Refinement

- Tenant supplier directory + quick-add
- Stock movement ledger
- Receive-stock workflow
- Starter inventory templates/import
- Supplier-item mappings (SKU/pack/preferred reorder qty/last price)
- Reorder list with supplier contact shortcuts
- Global movement ledger page (`/inventory/movements`)
- Batch/lot tracking foundation (`inventory_batches`)

## 2026-03 — Control Plane Operations

- Ops console rebuilt (health checks + tooling)
- Plan enforcement table restyled
- Control Plane Invoices: delete action (draft/void/cancelled), modal confirm
- Control Plane Defaults: time format persistence fixed

## 2026-01 — SaaS Multi-Tenant Transition (COMPLETE)

- Phases 0-6 completed and verified
- RBAC consolidation, clinic context wall, schema tenancy
- TenantAwareModel fail-closed, private file storage
- Background jobs with tenant context
- API-B stateless integration API (`/api/v1/*`)

## 2026-01 — UI Hardening & Branding

- DOM-XSS remediation across all allowlisted views
- Branding centralization via settings injection
- Secure logo upload pipeline
- Print branding consistency
