# Finance Feature Re-Study - 2026-03-28

## Scope
This document replaces the earlier finance/reporting assessment as the current codebase study for the tenant finance feature and its reporting surfaces.

It covers:
- tenant finance transactions
- invoice and payment logic
- expense logic
- provider payout logic
- finance and reports pages
- inventory-to-finance cost posting where it affects finance statements
- comparison against professional accounting systems and practice-management systems

It does not treat control-plane SaaS billing as part of the tenant clinic accounting model, except where separation of concerns matters.

## Executive Summary
The current finance subsystem is no longer just a simple payment log. It now has four meaningful layers:
1. patient-facing receivables via invoices and invoice settlements
2. direct cash transactions and expenses
3. provider-share accrual and payout tracking
4. reporting surfaces that try to summarize clinic money movement and open balances

That is materially better than a basic clinic cashbook. However, it is still not a complete accounting model.

The most important current facts are:
- Accounts receivable exists in a usable form through `invoice` rows plus `finance_payments` settlements.
- General accounts payable does not exist yet. Provider payables exist, but unpaid rent, unpaid laboratory bills, unpaid services, unpaid utilities, and similar obligations cannot be represented as open liabilities in the same way invoices represent open receivables.
- Date semantics remain inconsistent across the codebase. Some report paths now use business/payment dates, but many core model methods still rely on `created_at` for financial filtering.
- `/finance/reports` and `/reports?report_type=finance` have improved owner-facing summaries, but their notion of `we owe others` is still incomplete because it is currently driven only by provider payout balances.

Professional conclusion:
- The app is currently best described as an operational clinic finance system with partial accounting concepts.
- It is not yet accountant-grade bookkeeping software.
- The correct next financial-model step is not more dashboard cosmetics. It is to normalize business dates and introduce bill/payable logic for unpaid expenses.

## Verification Sources
### Codebase reviewed
Core tenant finance code:
- [app/Controllers/Finance.php](C:/xampp/htdocs/dental-saas/app/Controllers/Finance.php)
- [app/Controllers/Reports.php](C:/xampp/htdocs/dental-saas/app/Controllers/Reports.php)
- [app/Models/FinanceModel.php](C:/xampp/htdocs/dental-saas/app/Models/FinanceModel.php)
- [app/Models/FinancePaymentModel.php](C:/xampp/htdocs/dental-saas/app/Models/FinancePaymentModel.php)
- [app/Models/ProviderPayoutModel.php](C:/xampp/htdocs/dental-saas/app/Models/ProviderPayoutModel.php)
- [app/Models/ProviderPayoutPaymentModel.php](C:/xampp/htdocs/dental-saas/app/Models/ProviderPayoutPaymentModel.php)
- [app/Services/FinanceLedgerService.php](C:/xampp/htdocs/dental-saas/app/Services/FinanceLedgerService.php)

Tenant finance UI surfaces:
- [app/Views/finance/index.php](C:/xampp/htdocs/dental-saas/app/Views/finance/index.php)
- [app/Views/finance/show.php](C:/xampp/htdocs/dental-saas/app/Views/finance/show.php)
- [app/Views/finance/create.php](C:/xampp/htdocs/dental-saas/app/Views/finance/create.php)
- [app/Views/finance/edit.php](C:/xampp/htdocs/dental-saas/app/Views/finance/edit.php)
- [app/Views/finance/expense_create.php](C:/xampp/htdocs/dental-saas/app/Views/finance/expense_create.php)
- [app/Views/finance/expense_edit.php](C:/xampp/htdocs/dental-saas/app/Views/finance/expense_edit.php)
- [app/Views/finance/reports.php](C:/xampp/htdocs/dental-saas/app/Views/finance/reports.php)
- [app/Views/reports/content.php](C:/xampp/htdocs/dental-saas/app/Views/reports/content.php)

Schema and finance-related migrations:
- [app/Database/Migrations/2026-02-07-223000_AddFinancePaymentsAndBalances.php](C:/xampp/htdocs/dental-saas/app/Database/Migrations/2026-02-07-223000_AddFinancePaymentsAndBalances.php)
- [app/Database/Migrations/2026-02-19-120000_AddProviderPayouts.php](C:/xampp/htdocs/dental-saas/app/Database/Migrations/2026-02-19-120000_AddProviderPayouts.php)
- [app/Database/Migrations/2026-02-26-120000_AddExpenseSupportToFinances.php](C:/xampp/htdocs/dental-saas/app/Database/Migrations/2026-02-26-120000_AddExpenseSupportToFinances.php)
- [app/Database/Migrations/2026-02-26-140000_AddFinanceSourceReferences.php](C:/xampp/htdocs/dental-saas/app/Database/Migrations/2026-02-26-140000_AddFinanceSourceReferences.php)
- [app/Database/Migrations/2026-03-19-120000_AddExchangeFieldsToFinancePayments.php](C:/xampp/htdocs/dental-saas/app/Database/Migrations/2026-03-19-120000_AddExchangeFieldsToFinancePayments.php)

Related cross-feature finance behavior:
- [app/Controllers/Inventory.php](C:/xampp/htdocs/dental-saas/app/Controllers/Inventory.php)
- [app/Controllers/Treatment.php](C:/xampp/htdocs/dental-saas/app/Controllers/Treatment.php)
- [app/Views/inventory/usage.php](C:/xampp/htdocs/dental-saas/app/Views/inventory/usage.php)

### External comparison sources
Official product and vendor guidance used for comparison:
- QuickBooks: [Differentiate Cash and Accrual basis](https://quickbooks.intuit.com/learn-support/en-us/help-article/report-management/differentiate-cash-accrual-basis/L3RvRm1vW_US_en_US)
- Xero: [Cash vs accrual accounting: Key differences explained](https://www.xero.com/us/guides/cash-vs-accrual-accounting/)
- Xero: [Managing Accounts Payable](https://www.xero.com/us/guides/how-to-do-bookkeeping/manage-accounts-payable/)
- Xero: [Managing Accounts Receivable](https://www.xero.com/us/guides/how-to-do-bookkeeping/manage-accounts-receivable/)
- Zoho Books: [Bills - Overview](https://www.zoho.com/us/books/help/bills/)
- Zoho Books: [Manage Bills](https://www.zoho.com/us/books/help/bills/manage-bills.html)
- Microsoft Business Central: [Update document dates with posting dates](https://learn.microsoft.com/en-us/dynamics365/business-central/across-link-doc-dates-to-posting-dates)
- Cliniko: [Understanding report figures](https://help.cliniko.com/en/articles/11203202-understanding-report-discrepancies)
- Cliniko: [Pay an invoice](https://help.cliniko.com/en/articles/1177719-pay-an-invoice)

## Current Finance Model in the App
### 1. Transaction families
The tenant finance subsystem currently works with these practical families:
- direct payment transactions
- invoice transactions
- direct refund transactions
- direct expense transactions
- invoice settlement events in `finance_payments`
- provider payout accrual rows in `provider_payouts`
- provider payout cash settlement rows in `provider_payout_payments`
- auto-posted inventory COGS expenses through [FinanceLedgerService.php](C:/xampp/htdocs/dental-saas/app/Services/FinanceLedgerService.php)

This is more sophisticated than a single `finances` table, but it is still not fully normalized accounting.

### 2. Receivables model is real
The app can already represent open customer debt.

Verified in [Finance.php](C:/xampp/htdocs/dental-saas/app/Controllers/Finance.php):
- `store()` supports `transaction_type = invoice`
- invoices can have:
  - `amount_paid = 0`
  - `balance_due > 0`
  - `payment_status = pending|overdue`
  - `due_date`
  - no `paid_date` initially
- later settlements are recorded in `finance_payments`

This means the app already has a workable Accounts Receivable pattern:
- invoice document
- due date
- open balance
- payment events against the invoice
- aging by due date

That is directionally similar to accounting systems and clinic systems.

### 3. Expense model is still pay-now only
This is the most important structural gap.

Verified in [Finance.php](C:/xampp/htdocs/dental-saas/app/Controllers/Finance.php):
- `storeExpense()` always persists:
  - `transaction_type = expense`
  - `payment_status = paid`
  - `amount_paid = totalAmount`
  - `balance_due = 0`
  - `due_date = null`
  - `paid_date = provided date or today`
- `updateExpense()` does the same

So today the app cannot model:
- rent received but not yet paid
- lab invoice received but only partially paid
- equipment/service bill due next month
- utilities bill open and overdue
- vendor payable balance for stock purchases

This is the single biggest accounting gap in the current design.

### 4. Provider payables are partially modeled
The app has a special liability model for practitioner/provider splits.

Verified in [ProviderPayoutModel.php](C:/xampp/htdocs/dental-saas/app/Models/ProviderPayoutModel.php):
- `provider_share_amount`
- `provider_paid_amount`
- `provider_balance_due`
- `status = unpaid|partial|paid|void`

And cash settlements are recorded in `provider_payout_payments`.

This means the app already has one working payable pattern, but only for provider-share obligations tied to revenue.

Professional implication:
- the app has already proven the business need and technical pattern for liabilities
- but it has not generalized that pattern for all unpaid expenses and vendor bills

### 5. Inventory COGS uses finance, but only as paid expense posting
Verified in [FinanceLedgerService.php](C:/xampp/htdocs/dental-saas/app/Services/FinanceLedgerService.php):
- inventory usage auto-posts an `expense`
- category is `inventory_cogs`
- `payment_status` is forced to `paid`
- `amount_paid = totalCost`
- `balance_due = 0`
- `paid_date = usageDate`

That is acceptable if the intent is internal cost recognition for clinic operations. It is not acceptable as a full vendor payables model.

## Date Semantics: Current State
This is the second major structural issue after AP.

Professional finance systems separate at least these concepts:
- document date / transaction date
- due date
- settlement or payment date
- created timestamp / audit timestamp

The current app mixes them.

### 1. Where the app uses the right date concepts
Improved report paths already use payment/business dates in some places.

Verified in [Finance.php](C:/xampp/htdocs/dental-saas/app/Controllers/Finance.php):
- `/finance/reports` ledger includes:
  - direct `payment/refund/expense` rows filtered by `COALESCE(f.paid_date, DATE(f.created_at))`
  - invoice settlement rows filtered by `finance_payments.payment_date`
  - provider payout payments filtered by `provider_payout_payments.paid_date`
- historical AR aging is as-of the selected report end date

Verified in [Reports.php](C:/xampp/htdocs/dental-saas/app/Controllers/Reports.php):
- `/reports?report_type=finance` uses a similar cash-style ledger basis
- standalone rows use `COALESCE(paid_date, DATE(created_at))`
- invoice settlements use `payment_date`
- provider payouts use `paid_date`

This is materially better than earlier report behavior.

### 2. Where the app still uses the wrong date concept
Many core model methods still filter financial activity by `created_at`.

Verified in [FinanceModel.php](C:/xampp/htdocs/dental-saas/app/Models/FinanceModel.php):
- `getFinanceStatsByClinic()` uses `created_at` for:
  - direct finance rows
  - standalone payment totals
  - expense totals
  - refund totals
- `getMonthlyRevenueByClinic()` uses `created_at` for standalone rows, while invoice settlements use `payment_date`
- `getPaymentMethodsStats()` uses `created_at` for direct rows and `payment_date` for invoice settlements
- `getAccountsReceivableAgingDetailByClinic()` falls back to `created_at` if `due_date` is absent

Verified in [ProviderPayoutModel.php](C:/xampp/htdocs/dental-saas/app/Models/ProviderPayoutModel.php):
- provider payout accrual stats and provider payout report rows are filtered by the underlying finance row `created_at`

This means parts of the system are still answering different questions with different date bases.

### 3. Professional comparison on dates
Microsoft Business Central explicitly distinguishes `Document Date` from `Posting Date`, noting they serve different purposes and that many accounting standards require reporting based on the date transactions occurred. QuickBooks and Xero both distinguish cash reporting from accrual reporting, and Cliniko explicitly documents that different report types use different date semantics.

The relevant professional lesson is simple:
- the app must stop treating `created_at` as a financial date except as an audit fallback
- `created_at` is for who/when the user recorded the row
- `transaction/document date` is for when the business event happened
- `due_date` is for receivables/payables maturity
- `payment_date` is for cash movement

## Reporting Surfaces: Current Reliability
### 1. `/finance/reports`
Current purpose:
- dedicated owner-facing finance workspace
- simplified top summary
- open invoice detail
- unified financial ledger

Current strengths:
- filters by explicit start/end dates
- owner summary is simpler than before
- ledger combines direct rows, invoice settlements, and provider payout cash
- AR aging is shown as-of end date

Current limitations:
- `we_owe_others` only reflects provider payout balances, not general unpaid expenses
- cash and open-balance concepts are presented together, but the underlying liability model is incomplete
- direct rows still depend on `paid_date` fallbacking to `created_at`, because the app has no dedicated transaction date field

### 2. `/reports?report_type=finance`
Current purpose:
- generic reporting hub finance view
- secondary finance reporting surface

Current strengths:
- now broadly cash-style for period activity
- better than the old mixed basis
- supports drilldown tables and summary figures

Current limitations:
- duplicates part of the job of `/finance/reports`
- still depends on underlying model methods that are not fully date-normalized
- owner-facing figures remain incomplete because AP is incomplete

### 3. Can the app currently show a reliable “current net”?
Only if the term is defined carefully.

The app can reasonably show:
- period money in
- period money out
- open receivables as of end date
- provider payables as of end date
- a derived owner-facing `net position`

But that is not the same as:
- actual safe cash balance
- actual bank balance
- full balance-sheet net worth
- full profit for the period under accrual accounting

So the current `net position` is useful, but only as a simplified management metric.

Professional conclusion:
- it is acceptable as an owner-facing operational summary
- it is not a substitute for formal accounting balance or profit reporting

## Comparison Against Professional Systems
### 1. QuickBooks / Xero / Zoho Books
Professional accounting systems consistently support both sides of the open-balance model:
- invoices / receivables
- bills / payables

QuickBooks explicitly distinguishes cash vs accrual reporting. Xero and Zoho Books both treat unpaid vendor bills as first-class records with statuses, due dates, payment recording, and open balances.

What those systems do that this app does not yet do:
- record a vendor bill before payment
- track partial payment against the bill
- age unpaid bills by due date
- show AP alongside AR in reporting
- choose a clear report basis per report

### 2. Business Central and similar ERP/accounting systems
Professional systems distinguish:
- document date
- posting date
- due date
- created/audit timestamp

What they do that this app does not yet do cleanly:
- treat `created_at` as non-financial metadata
- let document date and posting date differ when needed
- allow rules around linking or separating those dates depending on business process

### 3. Practice-management systems like Cliniko
Cliniko explicitly documents that different reports can produce different numbers because they answer different questions:
- payments entered on a day
- invoices raised in a period
- invoices closed in a period
- outstanding invoices report

That is a useful product lesson for this app.

Current app risk:
- some screens implicitly mix these meanings without naming them strongly enough

Professional implication:
- the app should have fewer finance report surfaces, with each report explicitly named by basis and purpose
- a report should say whether it is cash activity, open balances as-of date, or invoice activity by issue date

## What the Current Model Supports Well
The following are already defensible and useful:
1. clinic cash receipts and refunds
2. patient invoices and open receivables
3. invoice settlements and partial collections
4. provider split accruals and provider payout balances
5. inventory COGS operational posting
6. owner-facing simplified finance summary for period review

This means the app has a solid operational finance foundation.

## What the Current Model Does Not Support Properly
### 1. General accounts payable
Missing today:
- unpaid bills
- partially paid expenses
- due dates on unpaid expenses
- vendor/service payable aging
- payables detail by vendor/category/status

### 2. Clean date basis across the whole finance stack
Missing today:
- dedicated transaction/document date on direct finance rows
- strict avoidance of `created_at` in financial calculations
- explicit report basis labels across all summaries and charts

### 3. Truly reliable “current balance” reporting
Missing today:
- opening/closing cash controls
- bank account ledgering
- general liabilities beyond provider payout balances
- full accrual profit model

## Recommended Financial Model Direction
This section is recommendation, not description of what already exists.

### 1. Add a general payable/bill model
The cleanest simple direction is to mirror the receivable pattern already used for invoices.

Recommended concept:
- introduce a payable-style expense document that can be:
  - unpaid
  - partially paid
  - paid
  - overdue
  - void
- keep actual settlement events separate from the bill itself

Minimum fields needed on payable expenses:
- transaction/document date
- due date
- vendor/counterparty
- category
- total amount
- amount paid
- balance due
- status
- notes
- optional attachment

Then settlements should be recorded separately, the same way invoice settlements are recorded separately.

This could be implemented as either:
- a generalized settlement model for both receivables and payables
- or a bills/payments structure parallel to `finance_payments`

The existing provider payout architecture proves the product already benefits from open-liability tracking.

### 2. Normalize business dates
Recommended date vocabulary for direct finance rows:
- `transaction_date` or `document_date`
  - when the business event happened
- `due_date`
  - when payment is contractually due
- `paid_date`
  - when cash actually moved
- `created_at`
  - when the user entered the row

Recommended rules:
- cash reports use `paid_date`
- accrual/invoice/bill reports use `transaction_date` or issue date
- aging uses `due_date`
- audit pages may show `created_at`, but should not compute totals from it

### 3. Make report basis explicit
Recommended reporting split:
- Cash Activity Report
  - money in/out based on payment date
- Open Balances Report
  - AR and AP as of end date
- Invoice/Bill Activity Report
  - documents issued during period based on transaction date
- optional owner summary combining these carefully as management KPIs

This is how professionals avoid conflicting totals.

### 4. Redefine `we owe others`
Today it appears to mean provider balances only. That is too narrow.

Recommended future meaning:
- unpaid provider payouts
- unpaid vendor bills
- unpaid rent/utilities/services if recorded as open expenses
- possibly tax liabilities if the product ever tracks them explicitly

Until general AP exists, any UI using `we owe others` should be treated as partial, not complete.

### 5. Redefine “current net” carefully
The app should not use one ambiguous word like `balance` without definition.

Recommended management definitions:
- `Money In`: cash collected in the selected period
- `Money Out`: cash paid out in the selected period
- `Customers Owe Us`: open receivables as of end date
- `We Owe Others`: open payables as of end date
- `Net Position`: `Money In - Money Out + Customers Owe Us - We Owe Others`

This can be shown, but it should be described as a management position, not literal safe/bank balance.

## Specific Findings on the User’s Requested Future Capability
The requested capability is: record expenses that are not fully paid yet, the same way the app can record income that is not fully paid yet.

Professional assessment:
- this is the correct next accounting feature
- it should not be hacked into the current `expense = always paid` controller flow
- it should become a first-class payable/bill workflow

Why:
- unpaid expenses are liabilities, not just delayed cash events
- the report page needs those liabilities to make `we owe others` and `net position` meaningful
- if this is implemented as a simple checkbox on the current expense form without a settlement model, the app will continue to mix cash and accrual semantics badly

## Recommended Refinement Order
1. Define the financial date model
   - add business/document date for direct finance rows
2. Add general AP / bill capability
   - unpaid, partial, paid, overdue, void
3. Update finance reports so `we owe others` includes all open liabilities
4. Reduce duplicated finance-report surfaces or clearly label their purpose and basis
5. Only after that, refine higher-level metrics and exports further

## Bottom Line
Current state, verified:
- receivables are supported
- provider payables are partially supported
- general payables are not supported
- reporting has improved materially but still rests on mixed date semantics and incomplete liabilities

Professional next step:
- build unpaid expense / bills capability using the same open-balance discipline already used for invoices
- separate business date from audit timestamp across the finance stack
- make each report explicit about whether it is cash, accrual, or open-balance based

That is the correct path if the goal is to refine the financial model without pretending the current system is already a full accounting engine.
