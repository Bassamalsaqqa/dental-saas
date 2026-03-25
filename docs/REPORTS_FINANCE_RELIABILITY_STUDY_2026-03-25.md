# Reports Finance Reliability Study - 2026-03-25

## Scope

This study reviews the finance reporting behavior of `/reports?report_type=finance` in the current codebase, with emphasis on date semantics, summary/detail consistency, and whether the resulting report can be treated as reliable for accounting decisions.

It also answers the practical question:

- For an expense transaction, what date should drive reporting?
  - `created_at`?
  - settlement/payment date?
  - some other business date?

## Executive Summary

Short answer:

- The current finance report is **not fully reliable as an accounting report**.
- It is currently a **mixed-basis operational report**.
- It mixes:
  - `created_at` for standalone finance rows and detail tables
  - `payment_date` for invoice settlement activity recorded in `finance_payments`
  - `due_date` for receivables aging
- The UI itself already partially admits this in the finance basis banner.

For **expense transactions**, the correct report date depends on the basis of the report:

- **Cash-basis expense reporting**: use the actual payment/settlement date.
- **Accrual-basis expense reporting**: use the expense/incurred/document date.
- **Audit/activity reporting**: use `created_at`.

In the current app, expense entry captures `paid_date` and labels it as the expense date, but `/reports` finance logic largely ignores that and filters expense rows by `created_at`. That means back-entered expenses can land in the wrong reporting period.

That is acceptable for a lightweight operational dashboard, but not for accountant-grade reporting.

## Direct Answer To The Date Question

### What should `/reports` show for expense transactions?

For a finance report, **not `created_at` by default**.

The date should be:

- `paid_date` if the report is intended to be **cash-basis**
- `expense_date` (or document/incurred date) if the report is intended to be **accrual-basis**
- `created_at` only if the page is acting as an **audit log** or "when the record was entered" view

### What does the app do today?

Today the app behaves inconsistently:

- Expense entry UI calls the field `expenseDate`, but stores it in `paid_date`
  - `app/Views/finance/expense_create.php`
  - `app/Controllers/Finance.php`
- `/reports?report_type=finance` filters the finance transaction set by `created_at`
  - `app/Controllers/Reports.php`
- Summary helpers inside `FinanceModel` also use `created_at` for standalone expenses and refunds
  - `app/Models/FinanceModel.php`
- Invoice settlement events are counted by `payment_date` from `finance_payments`
  - `app/Models/FinanceModel.php`

So the report is currently doing this:

- standalone expense -> report by creation timestamp
- invoice payment -> report by settlement date
- A/R aging -> report by due date

That is a three-date model on one report surface, without a formal report basis selector.

## Codebase Evidence

### 1. `/reports` finance detail rows are built from `created_at`

In `Reports::buildDetailTables()` the finance detail table uses:

- `transaction_id`
- `subject_label`
- `transaction_type`
- `payment_method`
- `payment_status`
- amounts
- `created_at` labeled as `Created`

Evidence:

- `app/Controllers/Reports.php`
  - `buildDetailTables()`
  - finance detail table column definition includes:
    - `['key' => 'created_at', 'label' => 'Created']`

This is direct proof that the visible transaction row date on `/reports` is currently the row creation timestamp, not business date or settlement date.

### 2. `/reports` finance transaction dataset is filtered by `created_at`

In `Reports::getFinanceReports()` the main transaction query is:

- `where('created_at >=', $startDateTime)`
- `where('created_at <=', $endDateTime)`

Evidence:

- `app/Controllers/Reports.php`
  - `private function getFinanceReports(...)`

This means the detail dataset on the finance report is currently creation-timestamp based.

### 3. Summary cards are not on the same date basis as the detail table

`Reports::getFinanceReports()` does not compute summary cards directly from the `transactions` query. It calls:

- `FinanceModel->getFinanceStatsByClinic($clinicId, $startDate, $endDate)`
- `ProviderPayoutModel->getStatsByClinic(...)`

Inside `FinanceModel->getFinanceStatsByClinic()`:

- standalone rows are filtered by `created_at`
- invoice settlement rows from `finance_payments` are filtered by `payment_date`
- overdue and A/R logic use `due_date` and `balance_due`

Evidence:

- `app/Models/FinanceModel.php`
  - `getFinanceStatsByClinic()`

This creates a mixed basis:

- `gross_collections` includes invoice payments by `payment_date`
- `total_expenses` includes expense rows by `created_at`
- `pending_invoice_amount` is balance-based and driven by invoice state
- detail rows still show `created_at`

### 4. The UI already discloses that the finance report is mixed-basis

In the finance section of the reports view, there is a banner with:

- `financeReportBasisTitle`
- `financeReportBasisDesc`

The English text says:

- "This report mixes cash collections, provider payout metrics, and operating expenses. Summary cards use the clinic base currency, while transaction detail rows include original currency and exchange rate when applicable."

Evidence:

- `app/Views/reports/content.php`
- `app/Language/en/TenantMisc.php`
- `app/Language/ar/TenantMisc.php`

That description is directionally honest, but it does not explicitly disclose the date-basis inconsistency.

### 5. Expense entry captures a business date, but reports ignore it

Expense creation validates and stores `paid_date`, defaulting to today if omitted.

Evidence:

- `app/Controllers/Finance.php`
  - expense store path sets:
    - `paid_date = normalizePostedDateOnly('paid_date') ?: date('Y-m-d')`
- `app/Views/finance/expense_create.php`
  - label shown to the user is `Finance.expenseDate`
  - field name stored is `paid_date`

This means the app already has a business-facing expense date field, but `/reports` finance currently does not use it for period inclusion or detail display.

### 6. A/R aging is due-date based, which is appropriate

`FinanceModel::getAccountsReceivableAgingDetailByClinic()`:

- loads open invoice rows
- uses `due_date`
- falls back to `created_at` if `due_date` is empty
- buckets by days past due

Evidence:

- `app/Models/FinanceModel.php`
  - `getAccountsReceivableAgingDetailByClinic()`

This is closer to expected accounting behavior for receivables aging.

## Reliability Assessment

### Reliable enough today for:

- management visibility into general finance activity
- cash collections monitoring at a rough level
- outstanding invoice monitoring
- payment method mix
- basic provider payout monitoring
- operational dashboards where users understand the page is blended

### Not reliable enough today for:

- period-accurate expense reporting
- accountant-grade monthly close
- precise cash-vs-accrual interpretation
- backdated entry handling
- audit-friendly export where the date column must represent one clearly defined accounting meaning

### Why the reliability problem exists

The page is trying to answer too many finance questions at once:

- what was entered this period?
- what cash was collected this period?
- what expenses belong to this period?
- what invoices are overdue as of this date?
- what provider amounts accrued vs were paid?

Those are valid questions, but they require different date drivers.

The current page answers them all on a single surface without formally separating report basis.

## Comparison To How Accounting Software Does It

### Best-practice pattern in accounting software

Modern accounting systems typically separate these concepts:

- **document date / transaction date**
- **due date**
- **payment/settlement date**
- **created/entered timestamp**
- **closed date** in some workflows

They do not normally use one timestamp to mean all of them.

### 1. Xero: accrual accounting distinguishes unpaid obligations from cash movement

Xero's glossary explanation of accrual accounting states that accrual accounting tracks bills and sales invoices that are yet to be paid, and is meant to provide a more accurate long-term financial view than cash accounting.

Source:

- Xero, "What is Accrual Accounting?"
  - https://www.xero.com/id/glossary/accrual-accounting/

Implication for this app:

- if the app wants accrual-style finance reporting, invoice/bill/expense business dates must matter
- `created_at` is not enough
- a clean accrual report should not be driven by when a user typed the row into the system

### 2. QuickBooks: cash-basis reporting should behave differently from A/R and A/P state

QuickBooks support notes that a cash-basis balance sheet should not show A/R or A/P balances the same way accrual accounting does, and warns that reporting anomalies arise when users expect cash-basis reporting to behave like accrual records.

Source:

- Intuit QuickBooks Support, "Resolve accounts receivable and accounts payable balances on a cash basis balance sheet"
  - https://quickbooks.intuit.com/learn-support/en-us/reports-and-accounting/resolve-ar-and-ap-balances-on-the-cash-basis-balance-sheet/00/262233

Implication for this app:

- cash collections and outstanding receivables should be treated as different report concepts
- combining them on one page is fine for operations, but they should be labeled and filtered on their own basis
- current `/reports` mixes those concepts without a basis selector

### 3. Cliniko: similar healthcare software explicitly separates issue date, paid date, and closed date

Cliniko's report-discrepancy guidance is especially relevant because it is a clinic/business operations product rather than a pure accounting system.

It explicitly distinguishes:

- **payment summary** -> based on payment data and payment date/time
- **practice revenue (accrual)** -> based on invoice issue date
- **practice revenue (cash-basis)** -> based on payment paid-at date
- **closed invoices/practitioner revenue** -> based on invoice close date

Source:

- Cliniko Help, "Understanding report discrepancies"
  - https://help.cliniko.com/en/articles/11203202-understanding-report-discrepancies

This is the closest direct comparison to this SaaS.

Implication for this app:

- the app should not pretend one finance report can answer all date questions cleanly
- it should either:
  - add a basis selector, or
  - split the reports into clearly named operational views

### 4. Zoho: cash flow is treated as its own report, not blended with all finance semantics

Zoho Books documents cash flow as a separate report surface.

Source:

- Zoho Books FAQ, "How do I view the cash flow of my organization?"
  - https://www.zoho.com/books/kb/year-end-accounting/cash-flow-report.html

Implication for this app:

- cash movement reporting should be isolated when possible
- mixing cash, accrual, overdue, and operational metrics on a single date basis is harder to reason about

## Similar Businesses / Clinic Software Comparison

For clinic software, the common professional pattern is:

- daily takings / payment summary -> payment date
- invoice revenue -> invoice issue date or service date
- overdue receivables -> due date
- fully collected revenue -> closed/fully-paid date
- audit trail -> created/updated timestamps

The app currently compresses these into one finance report page. That is understandable for a lightweight dental SaaS, but it creates ambiguity.

## What The App Is Really Reporting Today

### Today `/reports?report_type=finance` behaves like this:

#### Detail rows
- Included by `created_at`
- Display date = `created_at`
- Meaning = "when the finance row was recorded"

#### Summary gross collections
- standalone payments/refunds by `created_at`
- invoice settlement activity by `payment_date`
- Meaning = partially cash-basis, partially creation-date basis

#### Expenses period total
- standalone expense rows by `created_at`
- Meaning = not true cash-basis if expense was back-entered and `paid_date` differs

#### A/R aging
- open invoices by `balance_due`
- bucketed by `due_date`
- Meaning = aging as-of-date view

#### Payment methods mix
- standalone payment/refund rows by `created_at`
- invoice settlement rows by `payment_date`
- Meaning = mixed cash/creation basis

This is why the page is operationally informative but not accounting-clean.

## Specific Answer: Expense Transaction Date On `/reports`

If the question is:

- "What date should the expense use in reports?"

then the professional answer is:

### For a cash-style financial report
Use:

- `paid_date`

Because that is the actual outflow/settlement date.

### For an accrual-style financial report
Use:

- a dedicated `expense_date` / `incurred_date` / document date

Because accrual reporting should reflect when the expense belongs economically, not when it was paid and not when it was typed into the app.

### For audit/history
Use:

- `created_at`

Because that answers: when was the record entered?

### In the current app
The closest correct date for expense reporting is:

- `paid_date`

because that is the only business-facing expense date currently captured.

Using `created_at` for expense reporting is the weaker choice and should be treated as an implementation shortcut, not accounting doctrine.

## Professional Judgment On Current Design

### Good parts
- The app already distinguishes some finance date meanings:
  - `paid_date`
  - `due_date`
  - `payment_date` in settlement rows
  - `created_at`
- A/R aging using due date is directionally correct.
- The UI at least warns that the finance report is mixed.
- Expense entry already asks the user for an expense date.

### Weak parts
- `/reports` finance detail rows use `created_at` as if it were the finance date.
- Expense totals are perioded by `created_at`, not `paid_date`.
- Summary cards and detail rows are not on the same date basis.
- The page exposes a finance report without a formal basis switch.

### Bottom-line reliability verdict
- **Operational reliability**: medium
- **Accounting reliability**: low to medium
- **Audit clarity**: medium
- **Period accuracy for expenses**: weak if entries are backdated or entered late

## Recommended Direction

Keep it simple and professional.

### Recommended minimum correction
Do not turn `/reports` into a full accounting engine. Instead:

1. Define the current `/reports?report_type=finance` page explicitly as one of:
   - `Operational Finance Report`, or
   - `Cash Collections and Receivables Report`

2. Add a visible date-basis rule:
   - payments/settlements -> settlement date
   - expenses -> `paid_date`
   - invoices outstanding -> due date / balance state
   - audit rows -> created timestamp in a separate column only

3. For finance detail rows, show both:
   - `transaction_date` (business date)
   - `recorded_at` (created_at)

4. For expenses, use:
   - `paid_date` as the period filter and display date for the current report
   - not `created_at`

### Recommended cleaner long-term model
If you later want stronger finance reporting, split finance reports into separate report families:

- **Cash Activity**
  - driven by payment/settlement date
- **Revenue / Expense (Accrual-ish)**
  - driven by invoice/expense business date
- **Receivables Aging**
  - driven by due date and open balance
- **Audit / Entry Log**
  - driven by created_at

That is closer to how accounting software and mature clinic systems behave.

## Final Answer

If you are asking what date an **expense transaction** should use on `/reports`, the professional answer is:

- **Not `created_at`** for finance meaning
- **Use `paid_date`** for the current simple/cash-oriented finance report
- Keep `created_at` only as an audit timestamp

And if you are asking whether `/reports` finance is truly reliable today:

- It is **useful**, but **not fully reliable as a true accounting report** because it mixes creation date, settlement date, and due date in one report surface without a formal basis model.

## Evidence Index

Primary code references used in this study:

- `app/Controllers/Reports.php`
  - `buildDetailTables()`
  - `getFinanceReports()`
  - export helpers around finance transactions and CSV/HTML generation
- `app/Models/FinanceModel.php`
  - `getFinanceStatsByClinic()`
  - `getMonthlyRevenueByClinicRange()`
  - `getPaymentMethodsStats()`
  - `getAccountsReceivableAgingSummaryByClinic()`
  - `getAccountsReceivableAgingDetailByClinic()`
- `app/Controllers/Finance.php`
  - expense store/update handling for `paid_date`
- `app/Views/finance/expense_create.php`
  - `expenseDate` input
- `app/Views/reports/content.php`
  - finance report basis banner
- `app/Language/en/TenantMisc.php`
  - finance basis explanatory text
- `app/Language/ar/TenantMisc.php`
  - finance basis explanatory text

External comparison sources:

- Xero, Accrual Accounting
  - https://www.xero.com/id/glossary/accrual-accounting/
- Intuit QuickBooks Support, cash-basis A/R and A/P behavior
  - https://quickbooks.intuit.com/learn-support/en-us/reports-and-accounting/resolve-ar-and-ap-balances-on-the-cash-basis-balance-sheet/00/262233
- Cliniko Help, report discrepancy/date-basis behavior
  - https://help.cliniko.com/en/articles/11203202-understanding-report-discrepancies
- Zoho Books FAQ, cash flow as separate report
  - https://www.zoho.com/books/kb/year-end-accounting/cash-flow-report.html
