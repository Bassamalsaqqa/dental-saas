# Provider Compensation and Supplier Payables Implementation Plan

Date: 2026-04-19

Related audit:

- [docs/PROVIDER_PAYOUT_AND_SUPPLIER_AUDIT_2026-04-19.md](C:/xampp/htdocs/dental-saas/docs/PROVIDER_PAYOUT_AND_SUPPLIER_AUDIT_2026-04-19.md)

## Goal

Keep the clinic workflow simple while covering the real-world scenarios correctly:

- doctor/provider compensation
- external visiting doctor compensation
- lab/material supplier bills
- pay now vs pay later
- clear balances and ledgers
- easy payment entry points
- user pages and supplier pages that actually show financial context

The main business-logic principle is:

- clinical provider compensation is not the same thing as supplier/vendor payable
- do not force labs/material vendors into the same semantic bucket as doctor revenue sharing

This is the cleanest path for both accounting logic and clinic usability.

## External Best-Practice Direction

The plan below is aligned with common accounting software and dental workflow patterns:

- QuickBooks distinguishes between paying later with bills versus paying now with checks/expenses; the core difference is timing and the need to track what is owed.
  - Source: https://quickbooks.intuit.com/learn-support/en-us/help-article/accounts-payable/learn-difference-bills-checks-expenses-quickbooks/L0ZtL2TYI_US_en_US
- Odoo’s vendor-bill flow explicitly separates recording the liability from registering payment later, and supports aged payable reporting.
  - Source: https://www.odoo.com/documentation/15.0/applications/finance/accounting/vendor_bills.html
- Dentrix/Easy Dental treat labs as their own operational domain and track them as labs/cases, not as generic doctors/providers.
  - Source: https://hsps.pro/Dentrix/Help/mergedProjects/Lab%20Case%20Manager/Lab_Case_Manager_overview.htm
  - Source: https://hsps.pro/EasyDental/Help/mergedProjects/Lab%20Case%20manager/desktop/Setting_Up_the_Lab_Case_Manager/Adding_labs_using_DDX.htm

Practical interpretation for this app:

- doctor share belongs to provider compensation
- lab/material obligations belong to supplier bills / payables
- both may be unpaid and paid later
- both need clear balance views and easy payment entry points

## Business-Logic Decision

### 1. Keep provider payouts for clinical providers only

Use provider payout logic for:

- clinic doctor users
- visiting doctors
- external clinical providers

Meaning:

- someone who receives a share of collected revenue
- not someone who sold us materials or fabricated lab work as a vendor

### 2. Move labs/material vendors into supplier payable logic

Use supplier/vendor payable logic for:

- labs
- material suppliers
- third-party vendors

Meaning:

- someone the clinic owes because of a bill, service invoice, lab invoice, or materials purchase
- not someone being paid a percentage/fixed share of patient collections

### 3. Do not merge the two workflows

Do not try to make one generic payout tool cover:

- doctor revenue share
- supplier bills
- lab case invoicing

That creates semantic confusion and worse UX.

The software should feel simple because the underlying meanings are kept clean.

## Target Operating Model

### A. Clinical Provider Compensation

Entry origin:

- finance transaction
- optionally treatment/examination-linked production in future

Workflow:

1. user records patient finance transaction
2. user enables provider compensation split
3. user chooses provider
4. system calculates provider share
5. user either:
   - pays now
   - leaves unpaid/partial
6. outstanding compensation appears in:
   - provider payouts ledger
   - provider/user page
7. later payments can be recorded from:
   - payout ledger
   - finance transaction show page
   - user/provider detail page

### B. Supplier / Lab Payables

Entry origin:

- expense
- inventory receiving
- future lab case billing

Workflow:

1. user records bill/expense from supplier
2. supplier is selected from canonical supplier directory
3. system creates payable if not paid immediately
4. outstanding payable appears in:
   - supplier page
   - payables ledger
   - finance reports / aging
5. later payments can be recorded from:
   - supplier page
   - finance transaction show page
   - payables ledger

## Current Gaps To Solve

### Provider compensation side

- one provider payout per finance transaction only
- no provider statement page
- payment entry point is not obvious enough from payout ledger UX
- tenant user detail page exists, but financial context is not its first-class purpose yet
- provider typing is too weak for future external clinical provider support

### Supplier/lab side

- suppliers exist, but finance payable logic is not strongly connected to them as canonical counterparties
- no dedicated supplier financial statement page
- no clean separation in UI between:
  - provider compensation
  - supplier payable
- labs are not modeled as a first-class payable path from clinical workflow yet

## Implementation Strategy

## Phase 1. Lock Domain Rules

### Deliverable

A finalized business rule layer before more UI work.

### Decisions to implement

- provider compensation is for clinical providers only
- supplier payable is for vendors/labs/material suppliers
- a finance transaction may eventually have multiple provider allocations, but supplier bills remain a separate payable flow

### Code tasks

- add an internal domain note in code comments / docs where provider payouts are normalized
- relabel current finance UI copy from broad “provider” wording toward “clinical provider” where needed
- prevent misuse by clarifying helper text in finance create/edit

### UX tasks

- in finance create/edit, explain:
  - “Use this only when the clinic shares collected revenue with a clinical provider.”
  - “For lab/material bills, use expense/supplier payable.”

### Verification

- finance create/edit copy clearly distinguishes the two workflows
- no screen implies that lab supplier should be entered as guest provider unless explicitly unsupported fallback

## Phase 2. Make Provider Compensation Operationally Complete

### Deliverable

Users can manage doctor/provider balances without hunting for actions.

### Business logic

Keep current paid-now / paid-later model.

Current strengths to preserve:

- unpaid / partial / paid statuses
- separate payment rows
- balance due
- payout ledger

### Required improvements

1. Explicit payment entry point in payout ledger
- every unpaid/partial row must clearly show:
  - `Record Payment`
  - `Mark Paid`
  - `View Details`
- this must be visible and obvious, not hidden behind ambiguous actions

2. Provider statement page
- add a financial section to tenant user page `/users/{id}`
- when the user is a provider, show:
  - total provider share
  - total paid
  - outstanding balance
  - related payout rows
  - related payment rows

3. Finance show page clarity
- if a finance row has provider compensation:
  - show the provider payout card clearly
  - show status
  - show balance
  - show `Record Payment` prominently

4. Better provider labeling
- keep current `user` and `guest` for now
- display whether the payout belongs to:
  - clinic user
  - external provider

### Code tasks

- enhance [app/Views/finance/payouts.php](C:/xampp/htdocs/dental-saas/app/Views/finance/payouts.php)
- enhance [app/Views/finance/show.php](C:/xampp/htdocs/dental-saas/app/Views/finance/show.php)
- extend [app/Controllers/Users.php](C:/xampp/htdocs/dental-saas/app/Controllers/Users.php) show data
- update [app/Views/users/show.php](C:/xampp/htdocs/dental-saas/app/Views/users/show.php) with financial summary panel and ledger table

### Verification

- from `/finance/payouts`, user can record a payment in one obvious click
- from `/finance/{id}`, user can record provider payment if balance exists
- from `/users/{id}`, user can see provider financial status and linked transactions

## Phase 3. Connect Supplier Payables Properly

### Deliverable

Suppliers become the canonical payable entity for labs/material vendors.

### Business logic

Use supplier records already present in inventory as the canonical vendor identity.

Do not duplicate the same lab/vendor as:

- supplier in inventory
- guest provider in finance

unless temporarily needed during migration.

### Required improvements

1. Canonical supplier linkage in finance expenses
- expense/payable rows for vendors should point to `supplier_id` where applicable
- keep `counterparty_name` only as snapshot/fallback

2. Supplier financial page
- supplier detail page should show:
  - contact data
  - linked inventory items
  - open payables
  - paid amounts
  - outstanding balances
  - related finance transactions

3. Payment entry points
- unpaid supplier bills must be payable from:
  - supplier page
  - finance show page
  - payables ledger

4. Inventory and finance connection
- receiving stock should optionally create or update supplier payable if the clinic records vendor bill later
- expense rows tied to material/lab vendors should surface back to supplier record

### Code tasks

- introduce explicit finance-to-supplier linkage where missing
- enhance supplier model/controller/view set
- add payables ledger if current generic finance list is insufficient
- ensure reports include supplier aging cleanly

### Verification

- one supplier can be opened and its open balances are visible
- supplier-related finance rows are discoverable from the supplier page
- user can pay a supplier bill later without editing raw transactions

## Phase 4. Multi-Provider Allocation

### Deliverable

One finance transaction can support more than one provider allocation.

### When this phase is needed

This phase is for cases like:

- doctor gets a share
- another doctor gets a share
- not for supplier/lab bill logic

### Business logic

Replace one-row-per-finance payout with allocation rows:

- `finance_id`
- `provider entity`
- allocation rule
- share amount
- paid amount
- balance due
- status

Payments remain child rows of the allocation row.

### Data model direction

Either:

- refactor `provider_payouts` to allow multiple rows per `finance_id`

or:

- introduce `provider_compensation_allocations`

Preferred approach:

- keep old payout data migratable
- use a more explicit future-facing name if refactor scope is acceptable

### UX tasks

- finance create/edit should allow:
  - add allocation row
  - choose provider
  - choose percent/fixed
  - see running totals
- enforce total provider allocations cannot exceed gross collected amount

### Verification

- one finance row can save multiple provider allocations
- each provider has independent balance and payment history
- reports remain accurate

## Phase 5. Provider and Supplier Statement Surfaces

### Deliverable

Every entity the clinic owes money to has a place to review balances and transactions.

### Target surfaces

1. User/provider page `/users/{id}`
- profile
- clinic role context
- provider compensation summary
- transactions
- payments
- outstanding

2. Supplier page `/inventory/suppliers/{id}` or equivalent future route
- supplier profile
- linked inventory
- finance/payables summary
- bills/payments/outstanding

### UX principle

The clinic user should not have to remember whether to search in:

- finance
- inventory
- payout ledger

The entity page should summarize what matters first.

## Phase 6. Reporting and Accounting Integrity

### Deliverable

Reports stay correct after workflow expansion.

### Required reporting outcomes

- provider compensation outstanding
- provider compensation cash paid
- supplier payable outstanding
- supplier payable cash paid
- aging by supplier
- aging by provider if useful

### Technical checks

- `/finance/reports`
- existing treasury money-out calculations
- existing “we owe others” formula
- payout cash classification
- expense/payable classification

### Verification

- provider payments affect provider reports and treasury correctly
- supplier payments affect payables and treasury correctly
- no double counting between provider payouts and supplier bills

## Phase 7. Migration and Safety

### Deliverable

Move forward without breaking existing clinics.

### Migration rules

- preserve current provider payout rows
- preserve current guest providers as-is
- do not force historical rows into supplier linkage immediately
- allow future records to be linked more cleanly

### Data migration opportunities

- optional admin tool to map repeated guest provider names to suppliers or external providers later

## Explicit Workflow Design

### Workflow 1: Doctor gets paid later

1. create finance transaction
2. enable clinical provider split
3. choose doctor user
4. save unpaid
5. doctor balance appears in:
   - finance show page
   - payout ledger
   - `/users/{id}`
6. later record payment from any of those locations

### Workflow 2: Visiting doctor gets paid later

1. create finance transaction
2. choose external clinical provider
3. save unpaid
4. later pay from payout ledger or provider page

### Workflow 3: Lab bill paid later

1. create expense/vendor bill linked to supplier
2. leave unpaid
3. supplier balance appears in supplier page and payables ledger
4. later record payment

### Workflow 4: Material supplier tied to inventory

1. receive stock or create supplier expense
2. supplier is canonical vendor
3. payable appears under supplier
4. later payment closes payable

## Why This Is Simpler For The Clinic User

Because the user thinks in these buckets naturally:

- pay doctor
- pay lab
- pay supplier

Not:

- should I store this lab as guest provider, supplier, expense counterparty, or finance split?

The system should choose the right accounting lane based on the business meaning.

## Recommended Build Order

1. Phase 1
2. Phase 2
3. Phase 3
4. Phase 6
5. Phase 5
6. Phase 4
7. Phase 7

Reason:

- fix clarity and payment entry points first
- connect suppliers next
- only then tackle multi-provider allocations, because that is structurally larger

## Concrete Next Task Recommendation

The next implementation task should be:

### Task 1

Complete provider compensation workflow usability.

Scope:

- make `Record Payment` obvious in payout ledger
- add provider compensation summary and ledger to `/users/{id}`
- make finance show page compensation card operational and prominent
- clarify provider split helper text to say this is for clinical provider compensation only

This is the highest-value next step because:

- it fixes a real usability gap already noticed
- it does not require major schema upheaval
- it makes the current system operable before expanding it

