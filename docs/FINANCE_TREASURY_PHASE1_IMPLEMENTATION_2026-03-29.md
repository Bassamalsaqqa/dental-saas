# Finance Treasury Phase 1 Implementation - 2026-03-29

## Scope

This phase implements the treasury/custody foundation approved in `Track 2`:

- custody buckets:
  - `safe_cash`
  - `bank`
  - `undeposited_checks`
- internal transfer ledger
- treasury-aware finance settlements
- canonical finance report support for:
  - treasury movement by bucket
  - closing balances by bucket
  - total liquid funds

This phase does **not** introduce:

- bank reconciliation
- cheque clearance / bounce workflow
- opening balance journals
- recurring vendor bills
- full treasury statement exports beyond the existing finance exports

## Implemented Files

### Schema

- [2026-03-29-210000_AddFinanceTreasuryCustody.php](C:\xampp\htdocs\dental-saas\app\Database\Migrations\2026-03-29-210000_AddFinanceTreasuryCustody.php)

Adds:

- `finances.liquidity_account`
- `finance_payments.liquidity_account`
- `provider_payout_payments.liquidity_account`
- `finance_liquidity_transfers`

### Models / Services

- [FinanceLiquidityTransferModel.php](C:\xampp\htdocs\dental-saas\app\Models\FinanceLiquidityTransferModel.php)
- [FinanceTreasuryService.php](C:\xampp\htdocs\dental-saas\app\Services\FinanceTreasuryService.php)
- [FinanceModel.php](C:\xampp\htdocs\dental-saas\app\Models\FinanceModel.php)
- [FinancePaymentModel.php](C:\xampp\htdocs\dental-saas\app\Models\FinancePaymentModel.php)
- [ProviderPayoutPaymentModel.php](C:\xampp\htdocs\dental-saas\app\Models\ProviderPayoutPaymentModel.php)

### Controller / Routes

- [Finance.php](C:\xampp\htdocs\dental-saas\app\Controllers\Finance.php)
- [Routes.php](C:\xampp\htdocs\dental-saas\app\Config\Routes.php)

Adds:

- treasury account normalization and compatibility checks
- treasury-aware settlement recording
- `GET /finance/transfers`
- `POST /finance/transfers/store`

### Views

- [expense_create.php](C:\xampp\htdocs\dental-saas\app\Views\finance\expense_create.php)
- [expense_edit.php](C:\xampp\htdocs\dental-saas\app\Views\finance\expense_edit.php)
- [payouts.php](C:\xampp\htdocs\dental-saas\app\Views\finance\payouts.php)
- [show.php](C:\xampp\htdocs\dental-saas\app\Views\finance\show.php)
- [reports.php](C:\xampp\htdocs\dental-saas\app\Views\finance\reports.php)
- [transfers.php](C:\xampp\htdocs\dental-saas\app\Views\finance\transfers.php)

### Language

- [Finance.php](C:\xampp\htdocs\dental-saas\app\Language\en\Finance.php)
- [Finance.php](C:\xampp\htdocs\dental-saas\app\Language\ar\Finance.php)

### Tests

- [FinanceTreasuryServiceTest.php](C:\xampp\htdocs\dental-saas\tests\unit\FinanceTreasuryServiceTest.php)

## Business Rules Implemented

### 1. Default custody mapping

- `cash` -> `safe_cash`
- `card` -> `bank`
- `bank_transfer` -> `bank`
- incoming `check` -> `undeposited_checks`
- outgoing `check`:
  - operator must choose:
    - `bank`
    - `undeposited_checks`

### 2. Transfer treatment

Transfers create two treasury rows in the canonical report ledger:

- outflow from source bucket
- inflow into destination bucket

This means:

- per-bucket movement changes correctly
- closing balances change correctly
- total liquid funds remain neutral
- total external in/out excludes internal transfers

### 3. Report basis

`/finance/reports` now separates:

- owner summary:
  - money in
  - money out
  - customers owe us
  - we owe others
  - net position
- treasury overview:
  - closing safe cash
  - closing bank cash
  - checks on hand
  - total liquid funds
- period treasury movement:
  - in / out / net by custody bucket

## Verification Performed

### Syntax

Passed:

- `php -l app/Controllers/Finance.php`
- `php -l app/Views/finance/reports.php`
- `php -l app/Views/finance/transfers.php`
- `php -l app/Language/en/Finance.php`
- `php -l app/Language/ar/Finance.php`
- `php -l app/Database/Migrations/2026-03-29-210000_AddFinanceTreasuryCustody.php`
- `php -l tests/unit/FinanceTreasuryServiceTest.php`

### Migration

Passed:

- `php spark migrate`
- `php spark migrate:status`

Confirmed migration entry:

- `2026-03-29-210000 AddFinanceTreasuryCustody`

### Unit test

Passed:

- `vendor\bin\phpunit --filter FinanceTreasuryServiceTest`

Observed warning only:

- no code coverage driver available

### Browser verification

Attempted:

- `/finance/reports`

Blocked by current expired login session redirect to `/auth/login`.

## Known Boundaries After This Phase

The app can now report treasury custody **structurally** better, but it still does not claim:

- reconciled bank balance
- reconciled safe count
- cheque clearance state
- opening balance journals

Those remain future treasury phases.

## Recommended Next Phase

1. Treasury UX hardening
- simplify treasury cards further for owners/operators
- add explicit transfer drilldown links where useful

2. Treasury export hardening
- add treasury-specific CSV export if needed
- add custody bucket to all relevant finance exports

3. Cheque lifecycle refinement
- on hand
- deposited
- paid out to vendor
- optionally later: bounced / cancelled
