# Finance Current State

Last updated: 2026-10-22

## User Surfaces

- `/finance`: daily clinic finance workspace.
- `/finance/reports`: owner/accounting summaries.
- `/finance/payables`: supplier/vendor obligations.
- `/finance/payouts`: provider obligations and payments.
- `/finance/transfers`: movement between liquidity accounts.

## Canonical Lanes

| Lane | Owns |
|---|---|
| Patient finance | Charges, invoices, payments, refunds, adjustments, balance due |
| Payables | Supplier/vendor bills and settlements |
| Provider payouts | Shares owed and paid to clinic/outside providers |
| Treasury | Safe cash, bank, checks, and internal transfers |
| Inventory COGS | Material cost posted as an expense with source linkage |

Provider payouts are not supplier payables. Internal transfers do not change profit or total liquid funds.

## Reporting Semantics

- `money_in`: collected inflows in the period.
- `money_out`: refunds plus paid expenses and provider payouts.
- `customers_owe_us`: open patient invoice balance at period end.
- `we_owe_others`: open supplier/payable and provider payout balances.
- `net_position`: money in - money out + receivables - obligations.
- Business and settlement dates drive reports; `created_at` is audit time only.
- Revenue uses cash-basis receipt, not unsigned plans, appointments, or invoice issuance alone.

## Clinical Traceability

- Treatments may explicitly capture a clinical charge line.
- Charge lines retain patient, examination, treatment/procedure, appointment, provider, amount, currency, and source context where available.
- Money Trail views connect clinical source -> charge -> invoice -> payment -> provider obligation -> inventory cost.
- Procedure transitions never bill automatically.
- Invoice/payment/provider calculations remain backend-owned.

## Remaining Gaps

- Invoice line allocation to individual clinical charges.
- Payment allocation by line/provider and production-versus-collection reporting.
- Opening/counting/reconciliation workflows for liquidity accounts.
- Bounced checks, bank fees, delayed card settlement, and recurring bills.
- Insurance claims, write-offs, denials, and responsibility split.

Implementation sequencing is in `refactor/CLINICAL_FINANCE_TRACEABILITY_PLAN.md`.

## Consolidated development boundary

M1/M2 are deployed at `f86376a4a6c8f6bf0fe4471f08093af71e76e042`. Invoice corrections in Draft PR #20 remain default OFF and operational-only. See `M2C_INVOICE_CORRECTION_POLICY.md`, `M2C_IMPLEMENTATION_EVIDENCE_20261021.md` and `M2C_RELEASE_PROCEDURE.md`.

Incoming replacement applications transfer existing backing; local refundable capacity excludes incoming amounts. The external cash-creation finding was retracted, not fixed by changing formulas, and is not final approval. Preserve original invoices, receipts and provider history. Full returns, equal-FX applications and no automatic clawbacks remain documented limits. Source-only rollback after activation cannot ignore the correction ledger.
