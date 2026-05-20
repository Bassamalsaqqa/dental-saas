# Finance Current State

Last updated: 2026-05-02

## Canonical User-Facing Finance Surfaces

- `/finance` — finance workspace and main entry point
- `/finance/reports` — canonical finance reporting page (authoritative)
- `/finance/transfers` — treasury transfer workspace
- `/finance/payables` — supplier/vendor bills
- `/finance/payouts` — provider payout workspace

Legacy `/reports?report_type=finance` redirects to `/finance/reports`.

## Four Finance Layers

### 1. Revenue / Receivables
- Direct patient payments, invoices, invoice settlements, refunds
- Accounts receivable aging
- `Customers Owe Us` = unpaid invoice balances as of report end date

### 2. Expenses / Payables
- Paid, unpaid, and partially paid expenses
- Expense settlements through `finance_payments`
- Accounts payable aging for expenses
- `We Owe Others` includes open expense balances

### 3. Provider Payouts
- Provider payout accruals, cash payments, outstanding balances
- Providers are clinic users or outside providers who receive shares from patient transactions
- `We Owe Others` also includes outstanding provider payout balances
- **Provider shares are for patient transaction splits, NOT supplier bills**

### 4. Treasury / Custody
- Custody buckets: `safe_cash`, `bank`, `undeposited_checks`
- Transfer types: `cash_deposit_to_bank`, `bank_withdrawal_to_safe`, `check_deposit_to_bank`, `internal_transfer`
- Closing balances are cumulative through report end date
- Period movement covers only the selected date window
- Internal transfers change custody buckets but do NOT change total liquid funds

## Current Reporting Semantics

Owner-summary metrics in `/finance/reports`:
- `money_in` = collected inflows for the selected period
- `money_out` = refunds + paid expenses + paid provider payouts
- `customers_owe_us` = A/R snapshot as of end date
- `we_owe_others` = expense payables + provider payout balances
- `net_position` = `money_in - money_out + customers_owe_us - we_owe_others`

Treasury cards show: closing safe cash, closing bank cash, checks on hand, total liquid funds.

## Date Basis

- `transaction_date` — business/document date
- `payment_date` / `paid_date` — settlement date
- `created_at` — audit timestamp only

Reporting follows business/settlement dates, not entry timestamps.

## Key Business Rules

- **Provider shares ≠ supplier payables.** Provider payouts are for clinical providers receiving patient-transaction splits. Supplier/vendor bills (lab, materials, inventory, rent) are expenses/payables.
- **Inventory COGS is an expense lane**, not a provider payout.
- Revenue is recognized on payment receipt (cash basis), not invoice issuance.

## What Is Implemented

- Period Money In/Out, open receivables/payables, Net Position
- Closing safe cash, bank, checks on hand, total liquid funds
- Provider payout accruals and payments
- Supplier/vendor expense payables with A/P aging
- Inventory COGS auto-posting to finance expenses
- Patient/examination-linked finance rows
- Provider payout rows linked to finance rows
- Generic finance source linkage through `source_entity_type` / `source_entity_id`
- Clinical charge-line foundation table/model for future treatment/examination/contract-item production rows

## Clinical-Finance Traceability Status

The app has useful clinical and finance links and now has a canonical clinical charge-line foundation. Existing clinical workflows do not yet create charge lines automatically.

- Current finance invoices/payments can link to patients and examinations.
- Inventory usage can auto-post COGS expenses and link back through source entity fields.
- Treatment contracts have itemized planned procedure rows and totals.
- Provider payouts are currently derived from finance rows.
- Missing: automatic charge-line creation from clinical sources, invoice line items linked to treatments/contract items/procedures, payment allocation by line/provider, provider production vs collections reports, and insurance/write-off workflows.

See `docs/refactor/CLINICAL_FINANCE_TRACEABILITY_PLAN.md` for the active plan.

## What Is Not Yet Implemented

- Opening balances for safe/bank/check buckets
- Counted safe balance vs expected safe balance
- Bank reconciliation workflows
- Bounced check lifecycle
- Bank fees / card settlement delays
- Recurring bill engine / vendor master workflow
- Automatic clinical charge-line creation from treatments/examinations/contracts
- Invoice line items linked to clinical charge lines
- Payment allocation to charge lines/providers
- Provider production vs collection reports from line-level data
- Insurance claims, EOB/ERA posting, write-offs, denials, and patient responsibility split
