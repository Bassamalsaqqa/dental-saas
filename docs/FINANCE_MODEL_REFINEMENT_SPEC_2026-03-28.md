# Finance Model Refinement Spec - 2026-03-28

## Purpose
This document translates the verified finance re-study into an implementation-grade specification.

It defines how the tenant finance model should evolve so the app can support:
- unpaid and partially paid expenses
- consistent financial date semantics
- clearer owner-facing reports
- safer future reporting and export logic

This is a design specification. It does not claim that the described model already exists in the codebase.

Related study:
- [FINANCE_FEATURE_RESTUDY_2026-03-28.md](C:/xampp/htdocs/dental-saas/docs/FINANCE_FEATURE_RESTUDY_2026-03-28.md)

## Product Direction
The recommended product direction remains:
- operational clinic finance with disciplined accounting concepts
- not a full general-ledger accounting platform

That means the model should become stronger on:
- receivables
- payables
- business dates
- reporting basis
- auditability

It should not yet expand into:
- chart of accounts
- double-entry journal engine
- bank reconciliation
- tax engine
- accounting period close

## Goals
1. Add first-class unpaid expense support.
2. Make expense and invoice logic structurally symmetrical where appropriate.
3. Stop using `created_at` as a financial date except for audit purposes.
4. Make finance reports explicit about basis and meaning.
5. Preserve current operational simplicity for clinic users.
6. Avoid breaking current tenant finance usage patterns during rollout.

## Non-Goals
1. Do not turn tenant finance into a full ERP.
2. Do not replace the provider payout subsystem.
3. Do not remove existing invoice settlement behavior.
4. Do not force accountants-only terminology into primary clinic workflows.
5. Do not widen control-plane billing scope into tenant operational finance.

## Canonical Finance Concepts
The model should use these business concepts explicitly.

### 1. Receivable document
Represents money owed to the clinic.

Current app equivalent:
- `finances.transaction_type = invoice`
- settlement events in `finance_payments`

### 2. Payable document
Represents money the clinic owes to someone else.

Target app equivalent:
- a payable-style finance document for expenses/bills
- settlement events recorded separately

### 3. Cash transaction
Represents money movement that is fully settled at entry time.

Examples:
- direct payment received immediately
- direct refund paid immediately
- small petty-cash expense paid immediately

### 4. Settlement event
Represents cash movement against an open document.

Current equivalents:
- `finance_payments` for invoices
- `provider_payout_payments` for provider obligations

Target principle:
- open balances should not be mutated by one-off ad hoc field edits alone
- settlements should be event-driven where possible

## Canonical Date Semantics
This is the most important technical design decision.

### Required dates
Every finance document should distinguish these dates:

1. `transaction_date`
- when the business event occurred
- examples:
  - invoice issue date
  - expense/bill date
  - direct payment date if entered as immediate cash

2. `due_date`
- when the balance is contractually due
- used for aging and overdue logic

3. `paid_date`
- only for fully settled direct cash rows
- not the document date of unpaid items

4. `payment_date`
- settlement event date in payment tables
- already exists in `finance_payments`
- already exists in `provider_payout_payments` as `paid_date`

5. `created_at`
- audit timestamp only
- never the primary reporting basis except controlled fallback for historical rows

### Reporting basis rules
1. Cash activity reports
- use settlement/payment dates only
- `paid_date` for direct cash rows
- `payment_date` for invoice settlements
- `paid_date` for provider payout cash payments

2. Open balance reports
- use balances as of an end date
- use `due_date` for aging
- use document existence/status and settlement totals through the end date

3. Document activity reports
- use `transaction_date`
- answer: what invoices or bills were issued during the period

### Backward compatibility rule
Until migration/backfill is complete:
- direct cash rows may fall back to `DATE(created_at)` only when no transaction/business date exists
- this fallback must be explicit and temporary in code and documentation

## Target Data Model
### Option selected
Use the existing `finances` table as the finance document table, but make it capable of representing both receivables and payables cleanly.

Reason:
- lower migration cost
- preserves current routes and page structure
- avoids introducing a second parallel document table for bills
- fits current tenant mental model

### `finances` table evolution
Add or standardize these fields for document semantics:
- `transaction_date` date not null after backfill
- `due_date` nullable date
- `transaction_family` or equivalent derived logic
- keep `transaction_type`, but standardize semantics

Recommended transaction families by behavior, not only label:
- `invoice`
- `expense`
- `payment`
- `refund`
- `adjustment`

Recommended behavior definitions:
- `invoice`
  - receivable document
  - may be unpaid/partial/paid/overdue/void
- `expense`
  - payable or direct cash expense depending on payment status
  - may be unpaid/partial/paid/overdue/void
- `payment`
  - fully settled direct inflow
- `refund`
  - fully settled direct outflow
- `adjustment`
  - non-cash corrective document only if kept; otherwise reduce future use

### `finance_payments` table evolution
Current state:
- handles invoice settlements

Recommended target:
- general finance settlements table for both receivables and payables

Keep structure conceptually similar, but support:
- settlement against invoice receivable
- settlement against expense/bill payable
- payment types:
  - `payment`
  - `refund`
  - `void`
  - `adjustment` only if still required

Meaning:
- for invoices, `payment` reduces customer balance due
- for expenses, `payment` reduces payable balance due
- for invoices, `refund` increases outflow and can reverse prior settlement
- for expenses, `refund` is usually not needed as a normal path; vendor-credit handling can be postponed

### Provider payout subsystem
Keep `provider_payouts` and `provider_payout_payments` separate for now.

Reason:
- provider share is a specialized sub-ledger tied to treatment revenue splits
- not every payable is a provider split
- forcing provider payouts into generic bill logic now would widen scope and risk regressions

But reporting should aggregate them with general payables where appropriate.

## Target Workflow Design
### 1. Income workflows
#### A. Immediate payment
Use for:
- patient pays immediately

Behavior:
- `transaction_type = payment`
- `transaction_date = paid_date = actual business date`
- `payment_status = paid`
- `balance_due = 0`

#### B. Invoice / receivable
Use for:
- patient owes clinic now and may pay later

Behavior:
- `transaction_type = invoice`
- `transaction_date = invoice issue date`
- `due_date` optional or required based on clinic policy
- `amount_paid` initially `0` or partial amount if deposit captured immediately
- `balance_due = total - paid`
- settlements recorded through `finance_payments`

### 2. Expense workflows
#### A. Immediate cash expense
Use for:
- petty cash
- instant purchase paid at entry time

Behavior:
- `transaction_type = expense`
- `transaction_date = actual expense date`
- `payment_status = paid`
- `amount_paid = total`
- `balance_due = 0`
- no separate settlement event required unless you want full event purity later

#### B. Bill / payable expense
Use for:
- rent
- utilities
- laboratory bills
- service contracts
- vendor invoices
- equipment/service invoices

Behavior:
- `transaction_type = expense`
- `transaction_date = bill date`
- `due_date` required or strongly encouraged
- `payment_status = unpaid|partial|paid|overdue`
- `amount_paid` starts at `0` or partial
- `balance_due = total - paid`
- later settlements recorded through finance settlement events

This is the key new capability.

### 3. Provider payout workflows
Keep existing provider payout generation and payment flow.

Required reporting behavior:
- provider payout open balances must roll into `we owe others`
- provider payout cash payments must roll into `money out`

## Status Model
### Invoice and expense document statuses
Recommended canonical statuses:
- `draft`
- `unpaid`
- `partial`
- `paid`
- `overdue`
- `void`

Notes:
- `draft` is optional for expenses if the UI stays simple; it is more important for invoices
- `overdue` can be derived, but storing it is acceptable if current code already depends on status fields
- `void` should fail closed and stop aging/payments

### Status derivation rules
For open documents:
- if voided: `void`
- else if `balance_due <= 0`: `paid`
- else if `amount_paid > 0`: `partial`
- else if `due_date < today`: `overdue`
- else: `unpaid`

## Reporting Model Specification
### Canonical owner-facing report
Primary owner-facing finance report should be `/finance/reports`.

Top metrics should remain:
- `Money In`
- `Money Out`
- `Customers Owe Us`
- `We Owe Others`
- `Net Position`

Definitions:
- `Money In`
  - cash collected in the selected period
- `Money Out`
  - cash paid out in the selected period
  - includes refunds, expenses paid, provider payouts paid
- `Customers Owe Us`
  - open receivables as of end date
- `We Owe Others`
  - open provider payout balances plus open expense/bill balances as of end date
- `Net Position`
  - `Money In - Money Out + Customers Owe Us - We Owe Others`

Important UI note:
- this must be labeled as management position, not literal safe/bank balance

### Report types the system should explicitly support
#### A. Cash Activity
Question answered:
- what cash came in and out during the selected period?

Date basis:
- payment/settlement dates only

#### B. Open Balances As Of
Question answered:
- what remains unpaid at the end of the selected date?

Includes:
- receivables
- payables
- provider balances

Date basis:
- document exists on or before end date
- subtract settlements on or before end date
- age by `due_date`

#### C. Document Activity
Question answered:
- what invoices and bills were issued in the period?

Date basis:
- `transaction_date`

### `/reports?report_type=finance`
Recommended role:
- either become a lighter analytics/drilldown surface
- or simply defer to `/finance/reports` for owner-facing finance

It should not compete semantically with `/finance/reports`.

## UI/UX Specification for Expense Forms
### Current problem
Current expense form assumes every expense is already paid.

### Target interaction
The expense form should ask one simple business question first:
- `Was this expense already paid?`

If yes:
- show payment method
- show paid date
- hide due-date-driven payable workflow

If no:
- show transaction date
- show due date
- optional initial payment amount
- optional notes/attachment
- payment method should not be required yet unless there is an initial payment

This keeps the product simple while adding correct accounting behavior.

### Editing rules
For unpaid/partial expenses:
- allow additional settlements through a dedicated pay action, not only raw edit fields
- document fields and payment history should be visually separate

## Migration Strategy
### Phase 1. Date normalization groundwork
1. add `transaction_date` to `finances`
2. backfill existing rows using best available rule:
   - if `paid_date` exists for direct settled rows, use `paid_date`
   - if invoice, use current issue/business best approximation, likely current `created_at` date for legacy rows
   - if expense and paid, use `paid_date` if present else `DATE(created_at)`
3. update model methods and reports to prefer `transaction_date`

### Phase 2. Expense/payable capability
1. extend expense create/edit validation rules
2. allow `payment_status`, `amount_paid`, `balance_due`, `due_date` for expenses
3. add settlement action for expense documents
4. update report aggregations so AP includes open expenses plus provider payouts

### Phase 3. Reporting cleanup
1. make report basis labels explicit
2. ensure all owner metrics use normalized date semantics
3. reduce duplicated finance-report logic between `/finance/reports` and `/reports?report_type=finance`

## Backward Compatibility Rules
1. Existing paid expenses must continue to work without user re-entry.
2. Existing invoices and settlements must remain readable and correct.
3. Legacy rows missing `transaction_date` may use controlled fallback during transition.
4. Existing provider payout functionality must not regress.
5. Exports and dashboards should not silently change meaning; any changed basis must be labeled.

## Risks
### 1. Silent semantic drift
If we change report math without labeling basis, users will lose trust.

Mitigation:
- add explicit basis labels
- document migration impacts

### 2. Mixed open-balance logic
If expenses become partially payable but settlements are not event-driven, the app will become harder to audit.

Mitigation:
- separate document editing from payment recording wherever possible

### 3. Over-complication
If AP is implemented as a heavy accountant workflow, clinics may stop using it.

Mitigation:
- keep the UI simple
- ask business-first questions, not accounting-jargon-first questions

## Acceptance Criteria
The refinement can be considered complete when all of the following are true:
1. The app can record an expense as unpaid, partial, or paid.
2. Expense balances can be reduced later through settlement actions.
3. `we owe others` includes both provider balances and unpaid expenses.
4. Finance reporting no longer depends primarily on `created_at` for financial totals.
5. `/finance/reports` clearly distinguishes cash movement from open balances.
6. `Net Position` is computed from complete AR and AP, not partial liabilities only.
7. Existing paid-expense and invoice workflows still work after migration.

## Recommended Immediate Next Implementation Slice
The safest first implementation slice is:
1. add `transaction_date`
2. update reporting/model methods to prefer it
3. extend expense data model for open balances
4. add unpaid/partial expense create flow
5. update `/finance/reports` top metrics and AP aging to include expense payables

This order improves correctness before adding more UI complexity.

## Bottom Line
The finance feature should evolve by making the current model more disciplined, not more grand.

The correct refinement path is:
- normalize dates
- add payable/bill logic for expenses
- fold open payables into reporting
- keep provider payout logic specialized but aggregated
- keep the owner-facing report simple and explicit

That is the professional path that matches the study findings and fits the current codebase without over-architecting it.
