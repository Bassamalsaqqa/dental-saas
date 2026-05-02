# Finance Current State - 2026-03-29

## Purpose
This document is the canonical description of the current finance feature state in the app after the treasury custody layer was introduced.

It replaces earlier finance studies that described the pre-treasury or dual-report state.

## Canonical User-Facing Finance Surfaces
- `/finance`
  - finance workspace and main entry point
- `/finance/reports`
  - canonical finance reporting page
- `/finance/transfers`
  - treasury transfer workspace
- `/finance/payouts`
  - provider payout workspace

## Removed Secondary Surface
- `/reports?report_type=finance` is no longer a first-class report path.
- Generic reports no longer list `finance` as a report type.
- Legacy generic finance-report URLs are redirected to `/finance/reports` with normalized `start_date` and `end_date`.

Codebase evidence:
- `app/Controllers/Reports.php`
- `app/Views/reports/content.php`

## Current Finance Model
The app now has four distinct finance layers.

### 1. Revenue / Receivables
Supported:
- direct patient payments
- invoices
- invoice settlements
- refunds
- accounts receivable aging

Current meaning:
- `Customers Owe Us` = unpaid invoice balances as of the report end date.

### 2. Expenses / Payables
Supported:
- paid expenses
- unpaid expenses
- partially paid expenses
- expense settlements through `finance_payments`
- accounts payable aging for expenses

Current meaning:
- `We Owe Others` includes open expense balances.

### 3. Provider Payouts
Supported:
- provider payout accruals
- provider payout cash payments
- provider payout outstanding balances

Current meaning:
- `We Owe Others` also includes outstanding provider payout balances.

### 4. Treasury / Custody
Supported custody buckets:
- `safe_cash`
- `bank`
- `undeposited_checks`

Supported treasury events:
- direct in/out transactions assigned to a custody account
- invoice settlement payments assigned to a custody account
- expense settlement payments assigned to a custody account
- provider payout payments assigned to a custody account
- internal liquidity transfers between custody buckets

Current transfer types:
- `cash_deposit_to_bank`
- `bank_withdrawal_to_safe`
- `check_deposit_to_bank`
- `internal_transfer`

Codebase evidence:
- `app/Database/Migrations/2026-03-29-210000_AddFinanceTreasuryCustody.php`
- `app/Models/FinanceLiquidityTransferModel.php`
- `app/Services/FinanceTreasuryService.php`
- `app/Controllers/Finance.php`

## Current Canonical Reporting Semantics
`/finance/reports` is now the authoritative finance report.

### Owner Summary
Current top-level owner metrics:
- `Money In`
- `Money Out`
- `Customers Owe Us`
- `We Owe Others`
- `Net Position`

Current formulas in `Finance::reports()`:
- `money_in` = collected inflows for the selected period
- `money_out` = refunds + paid expenses + paid provider payouts for the selected period
- `customers_owe_us` = A/R snapshot as of the end date
- `we_owe_others` = expense payables + provider payout balances as of the end date
- `net_position` = `money_in - money_out + customers_owe_us - we_owe_others`

Codebase evidence:
- `app/Controllers/Finance.php:1514`

### Treasury Balances
Current treasury cards show:
- closing safe cash
- closing bank cash
- checks on hand
- total liquid funds

Current treasury movement section shows:
- inflow
- outflow
- net
for each custody bucket and for total liquid movement.

Important meaning:
- closing balances are cumulative through the report end date
- period movement covers only the selected date window
- internal transfers change custody buckets but do not change total liquid funds

### Date Basis
Current date hierarchy is:
- `transaction_date`
  - business/document date for finance documents
- `payment_date`
  - settlement date for `finance_payments`
- `paid_date`
  - direct paid transaction date where applicable
- `created_at`
  - audit timestamp only

Important current rule:
- reporting should follow the business/settlement dates above, not the timestamp when the user happened to enter the row.

## What Is Safe To Claim Today
The app is now structurally safe enough to claim:
- period `Money In`
- period `Money Out`
- open receivables as of end date
- open payables as of end date
- `Net Position`
- closing `Safe Cash`
- closing `Bank`
- closing `Checks on Hand`
- `Total Liquid Funds`

This is safe only if the clinic records treasury transfers and check custody changes correctly.

## What Is Not Implemented Yet
Not implemented yet:
- opening balances for safe/bank/check buckets
- counted safe balance vs expected safe balance
- bank reconciliation workflows
- bounced check lifecycle
- bank fees / card settlement delays
- recurring bill engine / vendor master workflow

These remain future improvements and should not be faked in the UI.

## Operator Guidance Rules
Current intended operator rules:
- use `Cash Deposit to Bank` when physical cash leaves the safe and is deposited in the bank
- use `Bank Withdrawal to Safe` when money is withdrawn from the bank and placed in the safe
- use `Check Deposit to Bank` when checks on hand are deposited into the bank
- use `Internal Transfer` only for custody moves that do not create revenue or expense

Codebase evidence:
- `app/Views/finance/transfers.php`
- `app/Views/finance/reports.php`
- `app/Language/en/Finance.php`
- `app/Language/ar/Finance.php`

## Current Guardrails
- `/finance/reports` is canonical
- generic reports no longer expose finance as a report type
- `created_at` should not be interpreted as the primary accounting date
- custody balances depend on correctly recorded transfer/check events
- internal transfers must not change total liquid funds

## Future Improvement Track
Keep in documentation for later, but do not implement implicitly:
1. opening balances by custody bucket
2. counted safe closing and variance workflow
3. bank reconciliation
4. bounced check handling
5. recurring bills and vendor-centric A/P workflows
6. bank fees and card settlement timing
