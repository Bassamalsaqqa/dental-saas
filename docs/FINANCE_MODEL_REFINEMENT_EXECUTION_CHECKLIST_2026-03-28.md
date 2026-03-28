# Finance Model Refinement Execution Checklist - 2026-03-28

## Purpose
This checklist converts the finance study and refinement spec into an execution sequence.

Source documents:
- [FINANCE_FEATURE_RESTUDY_2026-03-28.md](C:/xampp/htdocs/dental-saas/docs/FINANCE_FEATURE_RESTUDY_2026-03-28.md)
- [FINANCE_MODEL_REFINEMENT_SPEC_2026-03-28.md](C:/xampp/htdocs/dental-saas/docs/FINANCE_MODEL_REFINEMENT_SPEC_2026-03-28.md)

This file is intentionally operational. It is for implementation tracking, not for product explanation.

## Execution Principles
1. Do not widen scope into full accounting/ERP.
2. Preserve current paid-expense and invoice behavior while adding payable capability.
3. Normalize dates before expanding metrics.
4. Prefer additive migrations and fail-closed validation.
5. Keep `/finance/reports` as the canonical owner-facing finance report.
6. Treat `created_at` as audit metadata, not financial basis, except temporary controlled fallback.

## Phase 1 - Date Model Groundwork
### Goal
Introduce a real business date for finance documents and make reports/models prefer it.

### Schema tasks
- [ ] Add `transaction_date` to `finances`
- [ ] Backfill `transaction_date` for existing rows:
  - direct paid rows: prefer `paid_date`
  - invoices: use current best issue-date approximation from legacy data
  - expenses: prefer `paid_date`, else `DATE(created_at)`
- [ ] Add index support if needed for common date-range reporting on `clinic_id + transaction_date`

### Model tasks
Files:
- [app/Models/FinanceModel.php](C:/xampp/htdocs/dental-saas/app/Models/FinanceModel.php)

- [ ] Add `transaction_date` to allowed fields and validation rules
- [ ] Update `getFinanceStatsByClinic()` to stop using `created_at` as primary financial date
- [ ] Update `getMonthlyRevenueByClinic()` to use normalized date semantics
- [ ] Update `getMonthlyRevenueByClinicRange()` to use normalized date semantics
- [ ] Update `getPaymentMethodsStats()` to use normalized date semantics
- [ ] Review `getAccountsReceivableAgingDetailByClinic()` fallback behavior and reduce dependence on `created_at`

### Controller tasks
Files:
- [app/Controllers/Finance.php](C:/xampp/htdocs/dental-saas/app/Controllers/Finance.php)
- [app/Controllers/Reports.php](C:/xampp/htdocs/dental-saas/app/Controllers/Reports.php)

- [ ] Finance create/update flows must accept and persist `transaction_date`
- [ ] Expense create/update flows must accept and persist `transaction_date`
- [ ] `/finance/reports` ledger must prefer `transaction_date` where document-date logic is needed
- [ ] `/reports?report_type=finance` must prefer `transaction_date` where document-date logic is needed

### View tasks
Files:
- [app/Views/finance/create.php](C:/xampp/htdocs/dental-saas/app/Views/finance/create.php)
- [app/Views/finance/edit.php](C:/xampp/htdocs/dental-saas/app/Views/finance/edit.php)
- [app/Views/finance/expense_create.php](C:/xampp/htdocs/dental-saas/app/Views/finance/expense_create.php)
- [app/Views/finance/expense_edit.php](C:/xampp/htdocs/dental-saas/app/Views/finance/expense_edit.php)
- [app/Views/finance/show.php](C:/xampp/htdocs/dental-saas/app/Views/finance/show.php)

- [ ] Add `transaction_date` field to direct finance create/edit forms
- [ ] Add `transaction_date` field to expense create/edit forms
- [ ] Show `transaction_date` clearly on finance detail pages
- [ ] Keep `created_at` visible only as audit metadata where helpful

### Verification
- [ ] migration runs cleanly
- [ ] historical rows get a non-null transaction date
- [ ] finance reports still load
- [ ] date filters behave consistently on direct rows and invoice settlements
- [ ] no desktop/mobile regressions on finance forms

## Phase 2 - Expense Payable Capability
### Goal
Allow expenses to be unpaid, partially paid, overdue, and settled later.

### Schema tasks
Files likely affected:
- existing `finances` table
- possibly `finance_payments` for broader settlement support

- [ ] Confirm current `finances` columns are sufficient for payable expenses:
  - `amount_paid`
  - `balance_due`
  - `payment_status`
  - `due_date`
- [ ] If needed, widen `payment_status` validation/usage to canonical values:
  - `unpaid`
  - `partial`
  - `paid`
  - `overdue`
  - `void`
- [ ] If needed, extend `finance_payments` semantics so expense documents can receive settlement events

### Controller tasks
Files:
- [app/Controllers/Finance.php](C:/xampp/htdocs/dental-saas/app/Controllers/Finance.php)
- [app/Models/FinancePaymentModel.php](C:/xampp/htdocs/dental-saas/app/Models/FinancePaymentModel.php)

- [ ] Refactor `storeExpense()` so it supports:
  - immediate paid expense
  - unpaid bill
  - partial payment at creation time
- [ ] Refactor `updateExpense()` so it preserves payable semantics instead of forcing `paid`
- [ ] Add expense settlement action for open expense rows
- [ ] Add validation rules for partial payment amounts and due dates
- [ ] Keep fail-closed handling for invalid transitions

### View tasks
Files:
- [app/Views/finance/expense_create.php](C:/xampp/htdocs/dental-saas/app/Views/finance/expense_create.php)
- [app/Views/finance/expense_edit.php](C:/xampp/htdocs/dental-saas/app/Views/finance/expense_edit.php)
- [app/Views/finance/show.php](C:/xampp/htdocs/dental-saas/app/Views/finance/show.php)

- [ ] Add business-first question to expense form:
  - `Was this expense already paid?`
- [ ] If paid:
  - show payment method
  - show paid date
- [ ] If unpaid/partial:
  - show due date
  - show optional initial payment amount
  - do not require payment method unless payment is entered
- [ ] On finance detail page, separate:
  - expense document fields
  - payment/settlement history
- [ ] Add pay action for unpaid/partial expense rows

### Reporting tasks
Files:
- [app/Controllers/Finance.php](C:/xampp/htdocs/dental-saas/app/Controllers/Finance.php)
- [app/Controllers/Reports.php](C:/xampp/htdocs/dental-saas/app/Controllers/Reports.php)
- [app/Views/finance/reports.php](C:/xampp/htdocs/dental-saas/app/Views/finance/reports.php)
- [app/Views/reports/content.php](C:/xampp/htdocs/dental-saas/app/Views/reports/content.php)

- [ ] Include open expense/bill balances in `we_owe_others`
- [ ] Add AP aging detail/summary for unpaid expenses
- [ ] Ensure `Money Out` only includes paid cash outflows, not open unpaid bills
- [ ] Ensure `Net Position` uses complete AR and AP

### Verification
- [ ] create unpaid expense
- [ ] create partially paid expense
- [ ] record later payment against expense
- [ ] verify expense becomes overdue based on due date
- [ ] verify AP appears in finance reports
- [ ] verify `Money Out` does not increase until cash is actually paid

## Phase 3 - Report Basis Cleanup
### Goal
Make finance reports explicit about what they measure and reduce semantic overlap.

### `/finance/reports`
- [ ] Keep as canonical owner-facing finance report
- [ ] Add explicit basis labels where needed:
  - cash activity
  - open balances as of end date
- [ ] Make AP aging visible alongside AR aging
- [ ] Keep top summary definitions stable and documented

### `/reports?report_type=finance`
- [ ] Decide whether it remains a secondary analytics surface or defers to `/finance/reports`
- [ ] If kept, ensure wording and basis do not compete with `/finance/reports`
- [ ] Remove or reduce duplicated owner-summary semantics if they cause confusion

### Metric definitions to enforce
- [ ] `Money In` = settled inflows in period
- [ ] `Money Out` = settled outflows in period
- [ ] `Customers Owe Us` = open receivables as of end date
- [ ] `We Owe Others` = open provider balances + open expense/bill balances as of end date
- [ ] `Net Position` = `Money In - Money Out + Customers Owe Us - We Owe Others`

### Verification
- [ ] reports must explain themselves in user-facing wording
- [ ] date basis must be consistent with labels
- [ ] report totals must not silently change meaning between pages

## Phase 4 - Historical Data and Migration Safety
### Goal
Make transition safe for existing production data.

### Tasks
- [ ] Backfill `transaction_date` safely for all historical finance rows
- [ ] Preserve existing invoice balances and payment histories
- [ ] Preserve existing provider payout balances and payment histories
- [ ] Preserve existing paid-expense behavior for legacy rows
- [ ] Add migration notes to operational documentation if user-visible semantics change

### Verification
- [ ] legacy finance rows remain viewable
- [ ] legacy reports still render after migration
- [ ] no null-date breakage in list/report pages

## Phase 5 - UX and Terminology Cleanup
### Goal
Make the finance model understandable without exposing accounting-system complexity unnecessarily.

### Tasks
- [ ] Use simple owner-facing wording in reports
- [ ] Use professional but plain wording in expense and invoice forms
- [ ] Avoid ambiguous `balance` wording where `Net Position` is intended
- [ ] Distinguish:
  - document date
  - due date
  - paid date
  - recorded at
- [ ] Keep Arabic and English terminology aligned

### Verification
- [ ] Arabic and English both explain the same meaning
- [ ] finance detail pages show date roles clearly
- [ ] open payable rows are understandable to normal clinic users

## Phase 6 - Optional Follow-Up After Core Refinement
These are optional and should only happen after Phases 1-5 are complete.

- [ ] payable aging export
- [ ] accountant package update to include AP aging
- [ ] vendor summary analytics
- [ ] bill/document activity report by `transaction_date`
- [ ] more formal distinction between management view and accountant view

## File Impact Map
### Highest-impact files
- [app/Controllers/Finance.php](C:/xampp/htdocs/dental-saas/app/Controllers/Finance.php)
- [app/Controllers/Reports.php](C:/xampp/htdocs/dental-saas/app/Controllers/Reports.php)
- [app/Models/FinanceModel.php](C:/xampp/htdocs/dental-saas/app/Models/FinanceModel.php)
- [app/Models/FinancePaymentModel.php](C:/xampp/htdocs/dental-saas/app/Models/FinancePaymentModel.php)
- [app/Views/finance/create.php](C:/xampp/htdocs/dental-saas/app/Views/finance/create.php)
- [app/Views/finance/edit.php](C:/xampp/htdocs/dental-saas/app/Views/finance/edit.php)
- [app/Views/finance/expense_create.php](C:/xampp/htdocs/dental-saas/app/Views/finance/expense_create.php)
- [app/Views/finance/expense_edit.php](C:/xampp/htdocs/dental-saas/app/Views/finance/expense_edit.php)
- [app/Views/finance/show.php](C:/xampp/htdocs/dental-saas/app/Views/finance/show.php)
- [app/Views/finance/reports.php](C:/xampp/htdocs/dental-saas/app/Views/finance/reports.php)
- [app/Views/reports/content.php](C:/xampp/htdocs/dental-saas/app/Views/reports/content.php)

### Related but lower-impact files
- [app/Models/ProviderPayoutModel.php](C:/xampp/htdocs/dental-saas/app/Models/ProviderPayoutModel.php)
- [app/Models/ProviderPayoutPaymentModel.php](C:/xampp/htdocs/dental-saas/app/Models/ProviderPayoutPaymentModel.php)
- [app/Services/FinanceLedgerService.php](C:/xampp/htdocs/dental-saas/app/Services/FinanceLedgerService.php)
- finance language files
- finance migrations and schema dump

## Required Verification Discipline During Implementation
For every phase:
- [ ] `php -l` on all changed PHP files
- [ ] migration status check if schema changed
- [ ] browser verification on:
  - `/finance`
  - `/finance/reports`
  - `/reports?report_type=finance`
  - finance create/edit
  - expense create/edit
- [ ] verify in both Arabic/RTL and English/LTR where UI changes occur
- [ ] keep desktop layout stable while checking mobile/tablet responsiveness

## Stop Conditions
Implementation should pause and be re-evaluated if any of the following happens:
- reports begin disagreeing in meaning without clear basis labels
- expense payable design requires full vendor ledger complexity beyond intended scope
- provider payout logic starts collapsing into generic AP in a way that creates regressions
- migration/backfill produces uncertain historical financial dates without a controlled fallback strategy

## Recommended Immediate Next Step
Start with Phase 1 only.

Reason:
- date normalization is prerequisite to trustworthy finance reporting
- it improves correctness without yet forcing a full payable UI rollout
- it reduces the chance of compounding inconsistent report semantics while building AP
