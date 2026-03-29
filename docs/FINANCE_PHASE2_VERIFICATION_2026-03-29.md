# Finance Phase 2 Verification - 2026-03-29

## Scope

This verification pass covers the Phase 2 finance refinement that introduced:

- unpaid expenses
- partially paid expenses
- expense settlement tracking via `finance_payments`
- open expense payables in finance reporting
- combined `We Owe Others` semantics

This pass was done **before** any further finance-model expansion.

## Verification method

The verification was run against the local application codebase and local MySQL database using a **rollback-safe sample**.

Method:

1. Use existing clinic `21` because it exists and had `0` finance rows at the start of the verification.
2. Open a DB transaction.
3. Insert 4 sample expense records:
   - unpaid current
   - unpaid overdue
   - partial
   - fully paid
4. Insert `finance_payments` rows for:
   - the partial expense
   - the fully paid expense
5. Run the app’s **actual A/P aging model**:
   - [FinanceModel.php](C:\xampp\htdocs\dental-saas\app\Models\FinanceModel.php)
6. Calculate the owner summary using the same finance-report formulas currently used by the app:
   - expense cash out from `finance_payments`
   - open payables from `finances.balance_due`
7. Roll the transaction back so no verification data persists.

Important:

- No clinic data was left behind.
- This was not a UI-only check.
- This used live DB inserts plus the real model logic for A/P aging.

## Sample cases used

Reporting window:

- `2026-03-01` to `2026-03-31`

Sample expenses:

1. `VEREXP-CURRENT`
- status: `pending`
- total: `100.00`
- paid so far: `0.00`
- balance due: `100.00`
- transaction date: `2026-03-10`
- due date: `2026-03-31`

2. `VEREXP-OVERDUE`
- status: `overdue`
- total: `200.00`
- paid so far: `0.00`
- balance due: `200.00`
- transaction date: `2026-03-05`
- due date: `2026-03-15`

3. `VEREXP-PARTIAL`
- status: `partial`
- total: `300.00`
- paid so far: `120.00`
- balance due: `180.00`
- transaction date: `2026-03-12`
- due date: `2026-03-30`
- settlement row:
  - payment date: `2026-03-20`
  - amount: `120.00`

4. `VEREXP-PAID`
- status: `paid`
- total: `400.00`
- paid so far: `400.00`
- balance due: `0.00`
- transaction date: `2026-03-08`
- paid date: `2026-03-18`
- settlement row:
  - payment date: `2026-03-18`
  - amount: `400.00`

## Verified output

### Sample expense records after insertion

```json
[
  {
    "transaction_id": "VEREXP-CURRENT",
    "payment_status": "pending",
    "amount_paid": "0.00",
    "balance_due": "100.00",
    "transaction_date": "2026-03-10",
    "due_date": "2026-03-31",
    "paid_date": null
  },
  {
    "transaction_id": "VEREXP-OVERDUE",
    "payment_status": "overdue",
    "amount_paid": "0.00",
    "balance_due": "200.00",
    "transaction_date": "2026-03-05",
    "due_date": "2026-03-15",
    "paid_date": null
  },
  {
    "transaction_id": "VEREXP-PARTIAL",
    "payment_status": "partial",
    "amount_paid": "120.00",
    "balance_due": "180.00",
    "transaction_date": "2026-03-12",
    "due_date": "2026-03-30",
    "paid_date": "2026-03-20"
  },
  {
    "transaction_id": "VEREXP-PAID",
    "payment_status": "paid",
    "amount_paid": "400.00",
    "balance_due": "0.00",
    "transaction_date": "2026-03-08",
    "due_date": null,
    "paid_date": "2026-03-18"
  }
]
```

### Verified owner-summary output

```json
{
  "money_in": 0,
  "money_out": 520,
  "customers_owe_us": 0,
  "we_owe_others": 480,
  "net_position": -1000
}
```

Interpretation:

- `Money In = 0`
  - correct because there were no payment/invoice inflows
- `Money Out = 520`
  - `120` partial expense settlement
  - `400` fully paid expense settlement
- `We Owe Others = 480`
  - `100` current unpaid
  - `200` overdue unpaid
  - `180` remaining on partial expense
- `Net Position = -1000`
  - formula:
  - `0 - 520 + 0 - 480 = -1000`

### Verified A/P aging output

```json
{
  "current": 100,
  "days_1_30": 380,
  "days_31_60": 0,
  "days_61_90": 0,
  "days_91_plus": 0,
  "total_balance_due": 480,
  "expense_count": 3,
  "as_of_date": "2026-03-31"
}
```

Interpretation:

- `VEREXP-CURRENT`
  - due on report end date
  - stays in `current`
  - `100`
- `VEREXP-OVERDUE`
  - 15 days past due
  - bucket `1-30`
  - `200`
- `VEREXP-PARTIAL`
  - 1 day past due
  - bucket `1-30`
  - remaining balance `180`
- `VEREXP-PAID`
  - excluded from A/P aging because `balance_due = 0`

### Verified A/P aging detail rows

```json
[
  {
    "transaction_id": "VEREXP-OVERDUE",
    "balance_due": "200.00",
    "days_past_due": 15,
    "aging_bucket": "days_1_30"
  },
  {
    "transaction_id": "VEREXP-PARTIAL",
    "balance_due": "180.00",
    "days_past_due": 1,
    "aging_bucket": "days_1_30"
  },
  {
    "transaction_id": "VEREXP-CURRENT",
    "balance_due": "100.00",
    "days_past_due": 0,
    "aging_bucket": "current"
  }
]
```

## Checks that passed

```json
{
  "ap_total_expected_480": true,
  "money_out_expected_520": true,
  "net_position_expected_negative_1000": true,
  "open_payables_count_expected_3": true
}
```

## What this proves

### 1. Expense payable logic is materially working

The current model correctly supports these states:

- unpaid current expense
- unpaid overdue expense
- partially paid expense
- fully paid expense

### 2. Open expense balances are correctly included in payables

The A/P model correctly includes:

- fully unpaid expenses
- remaining balance on partial expenses

and excludes:

- fully paid expenses

### 3. Cash out for expenses now behaves correctly for report-period settlement activity

Only actual settlements within the selected period contributed to `Money Out`:

- partial settlement: `120`
- full settlement: `400`

The unpaid and overdue amounts did **not** incorrectly appear as period cash out.

### 4. The page-level owner summary math is coherent

For this isolated sample, the owner summary is internally consistent:

- `Money In = 0`
- `Money Out = 520`
- `We Owe Others = 480`
- `Net Position = -1000`

## Cross-check against the codebase

### `/finance/reports`

Primary finance workspace:

- [Finance.php](C:\xampp\htdocs\dental-saas\app\Controllers\Finance.php)

Relevant logic:

- owner summary built in `reports()`
- cash ledger built in `buildFinancialLedger()`
- cash summary built in `summarizeCashReport()`

### `/reports?report_type=finance`

Secondary analytics surface:

- [Reports.php](C:\xampp\htdocs\dental-saas\app\Controllers\Reports.php)

Relevant logic:

- owner summary built in `getFinanceReports()`
- cash ledger built in `buildFinanceCashLedger()`
- cash summary built in `summarizeFinanceCashLedger()`

### Code-path parity note

The generic reports surface was **not** executed in the rollback harness because controller execution there depends on tenant-web context in a way that is not practical in the CLI verification harness.

However, the implementation was cross-checked directly in code:

- both report surfaces use the same conceptual model:
  - cash activity from settlements in-period
  - open balances as of end date
  - expense payables included in `We Owe Others`
- both surfaces now use:
  - expense settlements from `finance_payments`
  - A/P aging from [FinanceModel.php](C:\xampp\htdocs\dental-saas\app\Models\FinanceModel.php)

So for the same clinic/date window, they should produce the same owner-summary numbers unless one code path is changed independently later.

## Remaining gap

This verification confirms the current Phase 2 model is working for:

- open expense balances
- settlement-based expense cash reporting
- payables aging

It does **not** yet make the finance subsystem a full AP system.

Still missing if needed later:

- recurring bills
- vendor statements
- due-date workflow beyond manual entry
- non-expense payable documents
- full audit verification through UI automation under authenticated tenant session

## Conclusion

Phase 2 is good enough to proceed.

The current finance model now has a real, working symmetry between:

- customer receivables
- expense payables

with reporting that distinguishes correctly between:

- cash movement in the period
- open balances at period end

That is the correct foundation before any further AP or reporting expansion.
