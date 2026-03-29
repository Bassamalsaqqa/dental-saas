# Finance Reporting Model Study - 2026-03-29

## Purpose

This document re-studies the tenant finance reporting model from current code and current accounting practice.

It is intentionally independent from earlier implementation assumptions.

The goal is to answer:

1. What the app can reliably report today
2. What the app cannot safely claim today
3. What a professional reporting model should distinguish
4. What needs to be implemented before the app can truthfully show:
   - cash in safe
   - cash in bank
   - checks collected from patients
   - checks still on hand
   - checks deposited to bank
   - checks endorsed or transferred to vendors
   - over-period money in, money out, and balance
5. How accounting software and professional workflows normally handle this
6. What the minimum safe implementation path should be
7. What future improvements would be appropriate after the minimum safe model is in place

This document is a study only. It is not approval to implement.

## Executive Summary

The current finance model is materially better than it was earlier in March 2026.

It now has:

- receivables via invoices and settlements
- general expense payables with unpaid / partial / paid behavior
- provider payout liabilities
- `transaction_date` groundwork
- owner-facing reporting that separates cash movement from open balances more clearly than before

However, the app is still **not safe enough** to report all of the following as if they were exact real-world balances:

- current safe cash
- current bank cash
- current checks on hand
- exact closing liquid funds by end date

The reason is structural:

The app records **payment method**, but it still does not record **cash custody location / clearing state / internal transfer state**.

That means the app can say:

- this transaction used `cash`
- this transaction used `card`
- this transaction used `bank_transfer`
- this transaction used `check`

But it cannot yet say safely:

- this cash is still physically in the clinic safe
- this cash was deposited from safe to bank
- this check is still held by the clinic and not yet deposited
- this check was deposited to bank on a later date
- this patient check was transferred directly to a vendor
- this card payment is book-recorded but not yet settled to bank

Professional conclusion:

The app already supports a useful **period movement report**.

It does **not yet** support a fully trustworthy **cash custody report**.

To support safe / bank / checks properly, the next reporting model must add a **custody layer** or **treasury layer**, not just more cards.

## Scope of This Study

This study covers tenant-plane finance and reporting only.

It covers:

- `/finance`
- `/finance/reports`
- `/reports?report_type=finance`
- finance create / expense create / finance detail behavior where it affects reporting
- AR and AP open balances
- cash movement and payment-method handling
- implications for over-period reports

It does not treat control-plane SaaS billing as clinic accounting.

## Verification Sources

### Codebase reviewed

Core tenant finance and reports:

- [app/Controllers/Finance.php](C:/xampp/htdocs/dental-saas/app/Controllers/Finance.php)
- [app/Controllers/Reports.php](C:/xampp/htdocs/dental-saas/app/Controllers/Reports.php)
- [app/Models/FinanceModel.php](C:/xampp/htdocs/dental-saas/app/Models/FinanceModel.php)
- [app/Models/FinancePaymentModel.php](C:/xampp/htdocs/dental-saas/app/Models/FinancePaymentModel.php)
- [app/Models/ProviderPayoutModel.php](C:/xampp/htdocs/dental-saas/app/Models/ProviderPayoutModel.php)
- [app/Models/ProviderPayoutPaymentModel.php](C:/xampp/htdocs/dental-saas/app/Models/ProviderPayoutPaymentModel.php)

Reporting views:

- [app/Views/finance/reports.php](C:/xampp/htdocs/dental-saas/app/Views/finance/reports.php)
- [app/Views/reports/content.php](C:/xampp/htdocs/dental-saas/app/Views/reports/content.php)

Prior verified finance docs still relevant:

- [docs/FINANCE_FEATURE_RESTUDY_2026-03-28.md](C:/xampp/htdocs/dental-saas/docs/FINANCE_FEATURE_RESTUDY_2026-03-28.md)
- [docs/FINANCE_MODEL_REFINEMENT_SPEC_2026-03-28.md](C:/xampp/htdocs/dental-saas/docs/FINANCE_MODEL_REFINEMENT_SPEC_2026-03-28.md)
- [docs/FINANCE_MODEL_REFINEMENT_EXECUTION_CHECKLIST_2026-03-28.md](C:/xampp/htdocs/dental-saas/docs/FINANCE_MODEL_REFINEMENT_EXECUTION_CHECKLIST_2026-03-28.md)
- [docs/FINANCE_PHASE2_VERIFICATION_2026-03-29.md](C:/xampp/htdocs/dental-saas/docs/FINANCE_PHASE2_VERIFICATION_2026-03-29.md)

### External sources consulted

Official / primary or vendor-official sources:

- QuickBooks: Deposit payments into Undeposited Funds
  - https://quickbooks.intuit.com/learn-support/en-us/help-articles/deposit-payments-into-the-undeposited-funds-account/00/261690
- QuickBooks: Record invoice payments / choose Deposit To account
  - https://quickbooks.intuit.com/learn-support/en-us/manage-customers-and-income/record-a-customer-payment/00/239785
- QuickBooks: Pay bills / choose payment account / partial payment
  - https://quickbooks.intuit.com/learn-support/en-us/help-article/pay-bills/pay-bills-quickbooks-online/L3eEpgbLD_US_en_US
- Xero: cash vs accrual accounting
  - https://www.xero.com/us/guides/cash-vs-accrual-accounting/
- Xero: bank reconciliation
  - https://www.xero.com/us/guides/what-is-bank-reconciliation/
  - https://www.xero.com/us/glossary/bank-reconciliation/
- Zoho Books / Zoho BillPay: bills, due dates, expected payment date, partial vendor payments, paid through account
  - https://www.zoho.com/us/books/help/bills/
  - https://www.zoho.com/books/help/bills/basic-functions.html
  - https://www.zoho.com/us/books/help/bills/functions.html
  - https://www.zoho.com/us/billpay/help/bills/record-payments-for-bills/
- Microsoft Business Central: purchase invoice fields and check ledger model
  - https://learn.microsoft.com/en-us/dynamics365/business-central/dev-itpro/api-reference/v2.0/resources/dynamics_purchaseinvoice
  - https://learn.microsoft.com/en-us/dynamics365/business-central/application/base-application/table/microsoft.bank.check.check-ledger-entry

## Current App State - Verified

### 1. What the app now does well

The current finance system now supports a meaningful operational finance model:

- invoices with partial and full collections
- expenses with unpaid / partial / paid states
- provider payout liabilities and cash payout events
- `transaction_date` support on finance rows
- finance reports that distinguish cash activity from open balances more clearly than earlier

This is materially stronger than a simple payment log.

### 2. What the app currently records

Current relevant fields and concepts in code:

- finance document date:
  - `transaction_date`
- expense settlement date:
  - `paid_date`
- invoice / expense settlement events:
  - `finance_payments.payment_date`
- provider payout settlement events:
  - `provider_payout_payments.paid_date`
- audit timestamp:
  - `created_at`
- payment methods:
  - `cash`
  - `card`
  - `bank_transfer`
  - `check`
  - `insurance`
  - `other`

### 3. What the main finance report does now

`/finance/reports` and the finance branch inside `/reports` now operate primarily as a hybrid operational report:

- cash movement in period
- open receivable balances as of period end
- open payable balances as of period end
- simplified owner summary

The reporting engine already builds:

- direct finance events
- invoice settlement events
- provider payout payment events

Current cash-style summary logic in [Finance.php](C:/xampp/htdocs/dental-saas/app/Controllers/Finance.php) still includes:

- `gross_collections`
- `refunds_total`
- `expenses_paid`
- `provider_paid_cash_total`
- `safe_cash_in`
- `safe_cash_out`
- `safe_cash_net`

This is useful, but it is still only a partial treasury picture.

### 4. What the current app still does not model

The current app does **not** have explicit custody accounts or clearing states for liquid funds.

There is no verified first-class model today for:

- `safe_cash`
- `bank_cash`
- `undeposited_checks`
- `checks_endorsed_to_vendors`
- `cash_deposits_to_bank`
- `bank_withdrawals_to_safe`
- `card_settlement_clearing`

Without these, any card on the report called `Current Safe Cash` or `Current Bank Cash` would be partly inferential and therefore unsafe.

## Code-Level Findings That Matter

### A. Reporting date usage improved, but not everywhere

The finance reporting paths now deliberately prefer business dates over `created_at` in important places.

Examples:

- [app/Controllers/Reports.php](C:/xampp/htdocs/dental-saas/app/Controllers/Reports.php)
  - `directReportDateExpr` uses `transaction_date`, `paid_date`, then `DATE(created_at)` as fallback.
- [app/Controllers/Finance.php](C:/xampp/htdocs/dental-saas/app/Controllers/Finance.php)
  - the ledger builder and raw export now use `transaction_date` more intentionally.

But some core model/report queries still rely on `created_at` for filtering.

Verified example:

- [app/Models/FinanceModel.php](C:/xampp/htdocs/dental-saas/app/Models/FinanceModel.php)
  - the service-type summary query still filters by `created_at`.

Professional implication:

The app is much better than before, but date normalization is not yet perfectly complete across every secondary report path.

### B. Safe cash is currently a payment-method summary, not a custody ledger

Verified in [app/Controllers/Finance.php](C:/xampp/htdocs/dental-saas/app/Controllers/Finance.php):

- `safe_cash_in` increases when a report row is a `payment` and `payment_method = cash`
- `safe_cash_out` increases when a row is `refund`, `void`, `expense`, or provider payout paid by `cash`

That means current `safe_cash_*` metrics are actually:

- **cash movement by payment method during the selected period**

They are **not**:

- closing safe balance
- reconciled cash drawer balance
- cash physically still held at clinic after internal transfers

This distinction is correct and must remain explicit.

### C. The app can now support AP reporting, but not full treasury reporting

Open payables are now materially better because expenses can be unpaid or partially paid.

This supports:

- `We Owe Others`
- A/P aging
- overdue unpaid expenses

But this is still obligation reporting, not liquid-funds custody reporting.

That is a different layer.

## Professional Accounting Model - What Must Be Separated

Professional systems normally separate these reporting layers.

### 1. Performance layer

This answers:

- how much was earned or billed
- how much was paid out as expense
- what was the realized operating result

Examples:

- revenue
- collections
- expenses
- provider share / payouts
- operating result

### 2. Obligation layer

This answers:

- who still owes us
- who we still owe

Examples:

- accounts receivable
- accounts payable
- aging reports
- overdue invoices and bills

### 3. Custody / treasury layer

This answers:

- where the liquid funds currently sit
- how they moved between custody locations

Examples:

- cash in safe
- cash in bank
- undeposited checks
- internal transfer from safe to bank
- deposit of checks to bank
- vendor payment by endorsed patient check

This third layer is what the app still lacks.

## What Professional Systems Do

### QuickBooks

QuickBooks explicitly separates payment receipt from where money is held.

Verified from Intuit help:

- customer payments can be recorded to a `Deposit To` account
- those payments can go to `Undeposited Funds`
- later they are deposited to the real bank account as a separate bank deposit step

This is the exact professional idea the current app is missing for checks and grouped deposits.

The same pattern appears on the payables side:

- bills exist as what you owe
- payments are recorded separately
- payment account matters
- partial bill payment is supported

That is a clean distinction between:

- bill / obligation
- payment event
- account used for payment

### Xero

Xero’s materials reinforce two separate ideas:

1. cash vs accrual are different report bases
2. bank balances are not just accounting summaries; they require reconciliation

This matters because a system can show:

- period cash movement
- AR / AP balances

while still not safely claiming a final bank balance unless reconciliation concepts exist.

This is why we should avoid pretending the current app has a reconciled bank number when it does not.

### Zoho Books / Zoho BillPay

Zoho’s bill workflow shows the same professional pattern:

- bill date
- due date
- expected payment date
- record payment
- partial payment
- paid through account
- bill status transitions such as open, overdue, partially paid, paid, void

This aligns closely with the direction the app now needs for general payable workflows and for source/destination of liquidity.

### Microsoft Business Central

Business Central exposes a more formal model:

- invoice date
- posting date
- due date
- vendor invoice number
- invoice status

and separately:

- check ledger entries
- bank account ledger integration
- bank reconciliation support

That is the mature version of the distinction this app now needs in simpler form.

## The Core Reporting Question - Period Movement vs End-of-Period Balance

This is the most important conceptual distinction.

### A. Period movement report

A period movement report answers:

- money in during selected period
- money out during selected period
- net movement during selected period

Example:

- from `2026-03-01` to `2026-03-31`
- cash received: `10,000`
- cash paid out: `7,000`
- net cash movement: `3,000`

This does **not** require an opening balance.

This is valid with just period events.

### B. Closing balance at end date

A closing balance report answers:

- how much is in safe on `2026-03-31`
- how much is in bank on `2026-03-31`
- how much is in undeposited checks on `2026-03-31`

This **does require** either:

- a known opening balance, plus all movements since then
- or cumulative full history from clinic inception through end date

So the earlier assumption that opening balance is not relevant is only true for **period movement**.

It is not true for **closing custody balances**.

Professional conclusion:

The app should support **both** report types, but they must not be mislabeled as the same thing.

## Recommended Reporting Model

The app should explicitly separate the reporting workspace into three blocks.

### 1. Period Money Movement

For selected `from_date` -> `to_date`:

- Safe Cash In
- Safe Cash Out
- Safe Cash Net Movement
- Bank In
- Bank Out
- Bank Net Movement
- Checks Received
- Checks Deposited to Bank
- Checks Used to Pay Vendors
- Checks Net Movement
- Total Liquid Movement

This is a movement report. No opening balance is required.

### 2. Open Obligations as of End Date

As of `to_date`:

- Customers Owe Us
- Open Invoices Count
- Receivables Aging
- We Owe Others
- Open Bills / Expenses Count
- Payables Aging

This is not cash custody. It is obligations reporting.

### 3. Closing Liquid Funds as of End Date

As of `to_date`:

- Closing Safe Cash
- Closing Bank Cash
- Closing Checks on Hand
- Total Liquid Funds

This requires custody-account logic or cumulative opening-to-end reconstruction.

## Recommended Minimum Safe Data Model

Before the app can safely show exact safe / bank / checks balances, it should add a minimal custody model.

### Custody accounts / locations

At minimum:

- `safe_cash`
- `bank`
- `undeposited_checks`

Optional future expansion:

- multiple bank accounts
- multiple safes / drawers
- card clearing account
- insurance receivables
- petty cash account

### Settlement-source / destination rules

Professional minimum rules:

1. Patient cash receipt
- increases `safe_cash`

2. Patient card payment
- increases `bank` or `card_clearing`

3. Patient bank transfer
- increases `bank`

4. Patient check receipt
- increases `undeposited_checks`

5. Expense paid by cash
- decreases `safe_cash`

6. Expense paid by bank transfer or card
- decreases `bank`

7. Expense paid by endorsed patient check
- decreases `undeposited_checks`

8. Deposit check to bank
- transfer from `undeposited_checks` to `bank`

9. Deposit clinic cash to bank
- transfer from `safe_cash` to `bank`

10. Withdraw from bank to safe
- transfer from `bank` to `safe_cash`

These are **internal transfers**, not revenue or expense.

That distinction is mandatory.

## Why Payment Method Alone Is Not Enough

A transaction marked `check` is ambiguous without lifecycle state.

It could mean:

- patient gave us a check today and we still hold it
- patient gave us a check today and we deposited it tomorrow
- patient gave us a check and we used it to pay a vendor directly
- we issued our own check from bank to a vendor

Those are not the same accounting events.

Professional systems solve this with:

- source / destination account
- document status
- deposit workflow
- check lifecycle entries
- bank reconciliation

The current app only has the payment method code.

That is why the study does **not** recommend adding a simple `Current Bank Cash` card immediately.

## Over-Period Reporting - Recommended Definitions

For a user-selected period `from_date` -> `to_date`, the report should define these terms exactly.

### Money In

Definition:

- funds received during the selected period from customer settlements and other incoming finance events

Should be splittable by custody destination:

- to safe
- to bank
- to checks on hand

### Money Out

Definition:

- funds paid during the selected period for refunds, expenses, provider payouts, and other outgoing settlements

Should be splittable by custody source:

- from safe
- from bank
- from checks on hand

### Net Movement

Definition:

- period inflows minus period outflows

This is valid without opening balance.

### Customers Owe Us

Definition:

- open receivable balances as of the selected end date

### We Owe Others

Definition:

- open payable balances as of the selected end date

### Net Position

Definition:

- a hybrid owner-facing metric
- period movement plus open balances

Formula:

- `Money In - Money Out + Customers Owe Us - We Owe Others`

This is useful but should never be mislabeled as:

- safe balance
- bank balance
- total cash on hand

## What the App Can Safely Show Now vs Later

### Safe to show now

The app can already safely show or is close to safely showing:

- period money in
- period money out
- period net movement
- customers owe us as of end date
- we owe others as of end date
- receivables aging
- payables aging

### Not yet safe to show as exact balances

The app should **not yet** claim exact:

- current safe cash
n- current bank cash
- current checks on hand
- total liquid funds at end date

unless it first implements custody and transfer logic.

## Proposed Minimum Implementation Sequence

### Phase A - Reporting model cleanup

Do this before adding more cards.

1. Keep `/finance/reports` as canonical
2. Keep owner summary separate from custody balances
3. Rename current `safe_cash_*` internally and in UI if needed to make clear they are movement metrics, not closing balances
4. Audit every finance reporting path to ensure:
   - period movement uses movement dates
   - open balances use end-date snapshots
   - no card implies a reconciled bank balance when it is not reconciled

### Phase B - Minimal custody layer

Add:

- liquidity account / custody destination on settlement events
- internal transfer transaction type
- check lifecycle state for received checks

At minimum, support:

- receive check
- deposit check to bank
- use check to pay vendor
- deposit cash to bank

This is the minimum for trustworthy safe/bank/check reporting.

### Phase C - Reporting outputs

Add two separate report sections.

#### Section 1: Period Movement

- Safe In / Out / Net
- Bank In / Out / Net
- Checks Received / Deposited / Used / Net
- Total Liquid Movement

#### Section 2: Closing Balances as of End Date

- Closing Safe Cash
- Closing Bank Cash
- Closing Checks on Hand
- Total Liquid Funds

### Phase D - Future treasury hardening

Later, if needed:

- multiple bank accounts
- multiple drawers / safes
- card clearing and settlement delays
- bounced checks
- returned transfers
- bank reconciliation screens
- deposit batches and deposit slips
- check issue / print / void lifecycle for outgoing checks

## Recommended UX / Reporting Language

The report should avoid ambiguous terms.

### Good labels

- Money In (Period)
- Money Out (Period)
- Net Movement (Period)
- Customers Owe Us (End Date)
- We Owe Others (End Date)
- Closing Safe Cash (End Date)
- Closing Bank Cash (End Date)
- Checks on Hand (End Date)
- Total Liquid Funds (End Date)

### Bad labels

- Balance
- Current Cash
- Current Money
- Net Cash

unless the basis is explicitly defined right next to the card.

## Practical Recommendation for This App

The correct next reporting target is:

1. keep the current owner summary approach for obligations and period movement
2. do **not** add exact safe / bank / check balance cards yet
3. first add a minimal custody-account model
4. then add over-period movement plus end-date custody balances
5. keep `created_at` strictly as audit metadata and continue removing it from financial basis logic where it still leaks into secondary summaries

## Final Conclusion

The app is now strong enough to support a serious clinic finance workflow.

It is not yet strong enough to claim exact treasury balances.

That does not mean the report is weak.
It means the report should remain honest about what it currently measures:

- period movement
- open obligations
- net position

To move from there to professional safe / bank / checks reporting, the app needs one more structural layer:

- custody destinations
- internal transfers
- check lifecycle tracking

That is the minimum professional step.

Anything less would produce attractive cards with unreliable meaning.

## Recommended Decision Before Any Further Implementation

Approve one of these two tracks explicitly.

### Track 1 - Conservative and safe

- keep current summary model
- improve wording only
- do not add exact safe / bank / checks balances yet
- implement custody layer first

### Track 2 - Treasury-ready reporting

- implement custody layer
- implement check lifecycle
- implement internal transfers
- then add exact safe / bank / checks balances and total liquid funds

Professional recommendation:

Choose **Track 2**, but do it in phases and do not ship exact treasury balances before the custody layer exists.
