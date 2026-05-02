# Finance Treasury Implementation Plan - 2026-03-29

## Purpose

This plan operationalizes `Track 2` from [FINANCE_REPORTING_MODEL_STUDY_2026-03-29.md](C:/xampp/htdocs/dental-saas/docs/FINANCE_REPORTING_MODEL_STUDY_2026-03-29.md).

Goal:

Add a treasury / custody layer to tenant finance so the app can eventually report all of these safely:

- closing safe cash
- closing bank cash
- closing checks on hand
- total liquid funds
- period movement by custody bucket
- internal transfers between custody buckets

This plan is implementation-oriented. It defines phase order, file targets, rules, and verification gates.

## Non-Negotiable Rules

1. Do not fake balances.
- No card may claim exact safe / bank / check balances until custody logic exists.

2. Separate three reporting layers.
- period movement
- open obligations
- closing custody balances

3. Keep date semantics explicit.
- `transaction_date`: document/business date
- `due_date`: obligation due date
- settlement dates: `payment_date` / `paid_date`
- `created_at`: audit only

4. Internal transfers are not revenue or expense.
- safe -> bank
- checks -> bank
- bank -> safe
must not affect clinic income or expense totals.

5. Custody account must be explicit on settlement events.
- payment method alone is not enough.

## Target Treasury Model

### Custody buckets

Minimum supported buckets:

- `safe_cash`
- `bank`
- `undeposited_checks`

Future optional buckets:

- `bank_account_2`
- `card_clearing`
- `petty_cash`
- `insurance_clearing`

### Event model

The treasury layer will be driven by two sources:

1. settlement events
- invoice settlement
- expense settlement
- provider payout payment

2. internal liquidity transfers
- safe to bank
- bank to safe
- checks to bank
- optional later: bucket-to-bucket adjustments

### Core rules

Incoming settlement defaults:

- `cash` -> `safe_cash`
- `card` -> `bank`
- `bank_transfer` -> `bank`
- `check` -> `undeposited_checks`

Outgoing settlement defaults:

- `cash` -> `safe_cash`
- `card` -> `bank`
- `bank_transfer` -> `bank`
- `check` -> user must choose:
  - `bank` if clinic-issued bank check
  - `undeposited_checks` if endorsed patient check

## Phase Order

## Phase 0 - Guardrails and terminology

### Objective
Freeze vocabulary and prevent ambiguous reporting labels while implementation is in progress.

### Actions

- keep `/finance/reports` as canonical finance workspace
- keep existing `safe_cash_*` metrics labeled as movement, not balance
- avoid adding any `Current Bank Cash` or `Current Safe Cash` card before custody accounting exists

### Files likely affected

- [app/Views/finance/reports.php](C:/xampp/htdocs/dental-saas/app/Views/finance/reports.php)
- [app/Views/reports/content.php](C:/xampp/htdocs/dental-saas/app/Views/reports/content.php)
- [app/Language/en/Finance.php](C:/xampp/htdocs/dental-saas/app/Language/en/Finance.php)
- [app/Language/ar/Finance.php](C:/xampp/htdocs/dental-saas/app/Language/ar/Finance.php)

### Verification gate

- no UI card uses ambiguous balance wording for treasury values not yet implemented

## Phase 1 - Schema foundation

### Objective
Add custody metadata and transfer storage.

### Required schema changes

1. `finance_payments`
- add `liquidity_account`
- add `source_liquidity_account` only if needed for advanced adjustments later
- optional `instrument_reference` for check/deposit tracing later

2. `provider_payout_payments`
- add `liquidity_account`

3. new table: `finance_liquidity_transfers`
- `id`
- `clinic_id`
- `from_account`
- `to_account`
- `amount`
- `transfer_date`
- `transfer_type`
- `notes`
- `related_finance_id` nullable
- `created_by`
- `created_at`
- `updated_at`

4. indexes
- `(clinic_id, payment_date, liquidity_account)` or equivalent
- `(clinic_id, transfer_date)`

### Migration behavior

Backfill rules:

- `finance_payments.liquidity_account`
  - `cash` -> `safe_cash`
  - `card` -> `bank`
  - `bank_transfer` -> `bank`
  - `check` -> `undeposited_checks`
  - `other` / `insurance` -> conservative default `bank` only if explicitly defensible, otherwise `NULL` + follow-up cleanup

- `provider_payout_payments.liquidity_account`
  - `cash` -> `safe_cash`
  - `card` -> `bank`
  - `bank_transfer` -> `bank`
  - `check` -> `bank` by conservative assumption for historical rows unless evidence suggests endorsed-check use
  - `other` -> `bank` or `NULL` by policy decision

### Files likely affected

- new migration file(s)
- [app/Models/FinancePaymentModel.php](C:/xampp/htdocs/dental-saas/app/Models/FinancePaymentModel.php)
- [app/Models/ProviderPayoutPaymentModel.php](C:/xampp/htdocs/dental-saas/app/Models/ProviderPayoutPaymentModel.php)

### Verification gate

- migration runs cleanly
- all existing rows get a valid custody bucket
- no null custody bucket remains unless intentionally allowed and handled

## Phase 2 - Backend settlement logic

### Objective
Make every new settlement record carry custody information explicitly.

### Required backend updates

1. Invoice settlements
- `addInvoicePayment()`
- `markAsPaid()`
- `refundInvoicePayment()`
- `voidInvoicePayment()` where relevant

2. Expense settlements
- expense create/update initial settlement
- expense detail payment posts
- mark fully paid action

3. Provider payouts
- `addProviderPayoutPayment()`
- `markProviderPayoutPaid()`

### Rules

- `payment_method` remains user-facing method
- `liquidity_account` becomes actual custody bucket
- enforce valid pairings

Examples:

- `cash` cannot go to `bank` unless it is recorded as a transfer, not a settlement
- patient `check` receipt defaults to `undeposited_checks`
- provider payment by `check` may be:
  - `bank` for clinic-issued check
  - `undeposited_checks` for endorsed patient check

### Files likely affected

- [app/Controllers/Finance.php](C:/xampp/htdocs/dental-saas/app/Controllers/Finance.php)
- [app/Views/finance/show.php](C:/xampp/htdocs/dental-saas/app/Views/finance/show.php)
- [app/Views/finance/expense_create.php](C:/xampp/htdocs/dental-saas/app/Views/finance/expense_create.php)
- [app/Views/finance/expense_edit.php](C:/xampp/htdocs/dental-saas/app/Views/finance/expense_edit.php)

### Verification gate

- every new settlement row stores a valid liquidity account
- provider payout by endorsed check reduces `undeposited_checks`, not `bank`
- no settlement route silently invents account location

## Phase 3 - Internal transfer workflow

### Objective
Allow operators to record custody movements that are not income or expense.

### Minimum supported transfer types

- `cash_deposit_to_bank`
- `bank_withdrawal_to_safe`
- `check_deposit_to_bank`
- `internal_transfer`

### UX surface

Recommended minimal UI:

- dedicated page or modal under `/finance`
- simple form:
  - from account
  - to account
  - amount
  - transfer date
  - transfer type
  - notes

### Required guardrails

- `from_account != to_account`
- amount > 0
- same-clinic only
- no effect on revenue / expense / AR / AP

### Files likely affected

- [app/Config/Routes.php](C:/xampp/htdocs/dental-saas/app/Config/Routes.php)
- [app/Controllers/Finance.php](C:/xampp/htdocs/dental-saas/app/Controllers/Finance.php)
- new model for transfer table
- new view(s) under `app/Views/finance/`

### Verification gate

- transfer creates one logical treasury movement
- reports show movement between buckets without changing total liquid funds

## Phase 4 - Treasury report engine

### Objective
Compute period movement and closing balances from custody events.

### Required outputs

#### A. Period movement (`from_date` -> `to_date`)

For each bucket:

- inflow
- outflow
- net movement

Buckets:

- safe cash
- bank
- undeposited checks

#### B. Closing balances as of `to_date`

For each bucket:

- closing balance

Derived:

- total liquid funds = safe + bank + checks

### Computation model

#### Settlement-driven movements

- incoming invoice/payment rows add to bucket
- outgoing refunds/expenses/provider payouts subtract from bucket

#### Transfer-driven movements

- subtract from `from_account`
- add to `to_account`

### Important distinction

- period movement for selected range does not need opening balance
- closing balance as of end date requires cumulative movements up to end date
- this can be computed from inception history if all movements are stored

### Files likely affected

- [app/Controllers/Finance.php](C:/xampp/htdocs/dental-saas/app/Controllers/Finance.php)
- [app/Controllers/Reports.php](C:/xampp/htdocs/dental-saas/app/Controllers/Reports.php)
- new treasury summarizer helpers or service

### Verification gate

- period movement changes when date range changes
- closing balances reflect cumulative historical activity up to end date
- internal transfers do not change total liquid funds

## Phase 5 - Finance reports UI

### Objective
Expose custody reporting clearly without mixing it with obligations.

### Recommended structure for `/finance/reports`

1. Period movement section
- Safe In / Out / Net
- Bank In / Out / Net
- Checks In / Out / Net
- Total Liquid Movement

2. Open obligations section
- Customers Owe Us
- We Owe Others
- receivables aging
- payables aging

3. Closing balances as of end date
- Closing Safe Cash
- Closing Bank Cash
- Closing Checks on Hand
- Total Liquid Funds

### UX rules

- label every block with basis
- keep owner summary simple
- allow drill-down tables to clickable source rows
- do not bury custody balances under generic payment-method charts

### Files likely affected

- [app/Views/finance/reports.php](C:/xampp/htdocs/dental-saas/app/Views/finance/reports.php)
- [app/Language/en/Finance.php](C:/xampp/htdocs/dental-saas/app/Language/en/Finance.php)
- [app/Language/ar/Finance.php](C:/xampp/htdocs/dental-saas/app/Language/ar/Finance.php)

### Verification gate

- user can distinguish movement, obligations, and closing balances at a glance
- no card uses ambiguous wording like `Balance` without explicit basis

## Phase 6 - Generic reports alignment

### Objective
Prevent `/reports?report_type=finance` from drifting into a conflicting finance model.

### Actions

- either fully align generic finance report with treasury model
- or keep it explicitly secondary with limited summary and link to `/finance/reports`

### Files likely affected

- [app/Controllers/Reports.php](C:/xampp/htdocs/dental-saas/app/Controllers/Reports.php)
- [app/Views/reports/content.php](C:/xampp/htdocs/dental-saas/app/Views/reports/content.php)

### Verification gate

- no second contradictory finance report model exists

## Phase 7 - Export and audit hardening

### Objective
Ensure treasury data exports are defensible and usable.

### Export targets

- period movement by account
- closing balances by account as of end date
- transfer ledger
- settlement ledger with liquidity account

### Required columns

Settlement export:

- document id
- transaction type
- settlement type
- payment method
- liquidity account
- settlement date
- amount
- clinic / patient / counterparty context
- recorded at

Transfer export:

- transfer date
- from account
- to account
- amount
- transfer type
- notes
- created by
- recorded at

### Verification gate

- CSV exports are basis-labeled and consistent with on-screen numbers

## Phase 8 - Advanced future improvements

These are intentionally out of scope for the minimum safe treasury layer.

Possible future work:

- multiple bank accounts
- card settlement clearing delays
- deposit batching
- bounced checks
- outgoing check numbering / issue lifecycle
- bank reconciliation against statements
- treasury close workflow with counted safe balance
- variance reporting between expected and counted cash

## Test and Verification Strategy

## Automated checks

Minimum:

- `php -l` for all changed PHP files
- migration runs cleanly
- migration status confirms treasury migration(s)

Recommended tests to add:

1. settlement to bucket mapping
- cash invoice payment -> `safe_cash`
- card invoice payment -> `bank`
- patient check receipt -> `undeposited_checks`

2. outgoing payment routing
- expense by bank transfer -> reduces `bank`
- provider payout by endorsed check -> reduces `undeposited_checks`

3. transfer neutrality
- safe -> bank transfer changes bucket balances, not total liquid funds
- check deposit changes `undeposited_checks` and `bank`, not total liquid funds

4. period movement vs closing balances
- selected range movement only counts in-range events
- closing balance includes pre-range history up to end date

## Manual verification scenarios

1. Patient pays by cash
2. Patient pays by card
3. Patient gives check, clinic holds it
4. Clinic deposits check to bank later
5. Clinic pays vendor by endorsed patient check
6. Clinic deposits safe cash to bank
7. Unpaid expense remains in A/P but does not change liquid funds
8. Partial expense payment reduces bucket and A/P correctly

## Definition of Done

Track 2 should only be considered complete when all of these are true:

1. Every settlement row carries a valid liquidity account
2. Internal transfers exist as first-class treasury events
3. `/finance/reports` can show:
- period movement by bucket
- closing balances by bucket
- total liquid funds
4. AR/AP remain separate from custody balances
5. Generic reports do not contradict canonical finance reports
6. Exports match on-screen numbers
7. The app can distinguish at minimum:
- check on hand
- check deposited to bank
- endorsed check used to pay vendor

## Recommended Immediate Next Step

Start with **Phase 1 only**:

- schema foundation
- model updates
- conservative backfill policy

Do not begin UI claims about exact safe / bank / checks balances until Phase 4 is ready.
