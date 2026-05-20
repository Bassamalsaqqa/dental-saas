# Clinical-Finance Traceability Plan

Last updated: 2026-05-20

## Purpose

This document defines how DentaCare should connect clinical work to finance so a clinic owner, doctor, receptionist, accountant, or outside provider can trace the financial impact of every meaningful medical event.

The goal is not just "create invoice from treatment." The goal is an audit-grade chain:

Appointment / examination / treatment / contract / inventory usage -> billable charge lines -> invoice/payment/refund/adjustment -> provider production/collection/payout -> patient ledger -> finance reports.

## Product Simplicity Rule

The implementation must feel simple for a doctor-manager, even if the backend model is audit-grade.

The user should not need to understand accounting language, double-entry accounting, allocation theory, or revenue-cycle terminology. The UI should answer practical clinic questions:

- What did we do for this patient?
- What did we charge?
- What did the patient pay?
- What is still unpaid?
- Which doctor/provider is connected to the work?
- How much is owed to that provider?
- Where did the money go: safe, bank, check, or payout?

Use simple clinic language in the product:

- Charge
- Invoice
- Payment
- Refund
- Discount
- Adjustment
- Balance due
- Provider share
- Provider paid
- Provider remaining
- Linked treatment
- Linked examination

Avoid exposing accounting-heavy words to normal clinic users unless they are in a finance/reporting context:

- debit
- credit
- journal entry
- revenue recognition
- receivable allocation
- contra revenue
- accrued liability

The backend may store precise ledger/allocation records, but the UI should present them as a readable timeline and summary cards.

## Implemented Slice 1: Read-Only Money Trail

Status: implemented in the current PHP app without schema or write-behavior changes.

Implemented:

- `MoneyTrailService` reads existing `finances`, `finance_payments`, `provider_payouts`, and `inventory_usage` rows.
- Patient Profile financials show a Money Trail panel.
- Examination detail shows the same read-only trail for finance records and material usage linked to the examination.
- Treatment detail shows material usage and any examination-level finance context.
- UI language stays simple: charged, paid, balance due, provider remaining, material cost, linked records.

Not implemented yet:

- canonical clinical charge lines
- invoice line items
- payment allocation by line/treatment
- provider production by exact treatment line
- insurance/claim lifecycle
- accounting export/journal layer

## Simple User Experience Target

Every important record should have a clear "Money trail" section.

### Patient Profile

Show:

- Total charged
- Total paid
- Balance due
- Open invoices
- Recent payments
- Treatment charges
- Provider shares related to this patient

Primary actions:

- Add charge
- Record payment
- View full money trail

### Treatment / Examination

Show:

- Linked patient
- Linked charge/invoice, if any
- Paid/unpaid status
- Provider connected to the work
- Related inventory usage/COGS if recorded

Primary actions:

- Create charge
- Open patient money trail
- Open linked invoice

### Finance Transaction

Show:

- Patient
- Linked examination/treatment/contract item
- Provider share, if any
- Payments/refunds/adjustments
- Remaining balance
- Treasury bucket: safe, bank, or checks

Primary actions:

- Record payment
- Add adjustment
- View linked clinical record

### Provider Profile

Show:

- Work connected to provider
- Produced amount
- Collected amount
- Provider share
- Paid to provider
- Remaining owed

Primary actions:

- View provider money trail
- Record provider payout

The professional accounting model should be available in finance reports, exports, and admin views, not forced into daily clinical workflows.

## External Research Summary

Professional dental and healthcare systems separate clinical documentation from financial posting, but keep them linked through charge capture, patient ledgers, provider attribution, and production/collection reports.

- Healthcare revenue-cycle guidance describes the encounter as flowing from scheduling through charge capture, checkout, payment posting, AR follow-up, and auditing. It explicitly says clinical care documentation should match coding and charge capture, and checkout should post charges, collect payments, discuss balances, and reconcile records.
- Open Dental separates production from income. Production is the amount the practice expects to collect from procedures/adjustments; income is actual money collected. Provider reporting can be by production or income, and income needs allocation to providers/procedures.
- Open Dental also treats the patient account module as the place where patient financial information and claim history are viewed, including payments, adjustments, claims, and related procedure breakdowns.
- Dentrix Ascend describes adjustments as distinct charge/credit categories and warns that discounts should usually be represented as adjustments rather than silently changing the procedure history.

Useful references:

- Revenue Cycle Management Process: https://health.maryland.gov/pophealth/Documents/Local%20Health%20Department%20Billing%20Manual/PDF%20Manual/Section%20III/CARDEA%20Revenue-Cycle-Management-Process.pdf
- Open Dental, Production and Income: https://opendental.com/manual/productionincome.html
- Open Dental, Production and Income Reports: https://www.opendental.com/manual/reportprodinc.html
- Open Dental, Track Income by Provider: https://www.opendental.com/manual/trackingincome.html
- Open Dental, Account Module: https://www.opendental.com/manual/account.html
- Dentrix Ascend, Adjustment Types: https://blog.dentrixascend.com/2020/09/22/understanding-adjustment-types/

## Current Verified DentaCare State

Verified by inspecting active docs, controllers, models, and the local schema on 2026-05-19.

### Existing Strengths

- `finances` is tenant-scoped and supports patient/examination context:
  - `patient_id`
  - `examination_id`
  - `transaction_type`
  - `service_type`
  - `amount`, `discount_amount`, `tax_percentage`, `tax_amount`, `total_amount`
  - `amount_paid`, `balance_due`
  - `source_entity_type`, `source_entity_id`
- `finance_payments` supports settlement rows against finance documents:
  - `finance_id`
  - `type`: payment/refund/void/adjustment
  - amount/currency/exchange fields
  - payment method and liquidity bucket
- `provider_payouts` are connected to finance documents:
  - `finance_id`
  - `patient_id`
  - clinic user or guest provider
  - split mode/value
  - gross/provider/clinic share amounts
  - paid/balance/status
- `provider_payout_payments` track actual payout cash movements.
- `Finance::validateOwnedFinanceReferences()` checks patient/examination ownership in the active clinic before finance writes.
- `Finance::upsertProviderPayouts()` creates/updates provider payout rows from a finance transaction.
- `Finance::buildFinancialLedger()` powers cash reporting across direct finance rows, invoice settlements, expense settlements, refunds, and provider payouts.
- `treatments` link to `patient_id` and optional `examination_id`.
- `treatment_contracts` link to patient and optional examination; `treatment_contract_items` have procedure code, tooth, description, quantity, and price.
- `inventory_usage` links to optional `treatment_id`.
- Inventory usage joins back to finance through `finances.source_entity_type = 'inventory_usage'` and `source_entity_id = inventory_usage.id`; current docs already state inventory COGS auto-posting exists.

### Current Gaps

The current system has useful finance and clinical primitives, but it does not yet have a canonical clinical charge-capture layer.

1. **No billable clinical charge line model.**
   - Treatments and contract items can describe clinical work.
   - Finance rows can invoice a patient/examination.
   - But there is no durable row that says: "this procedure/treatment/tooth/provider produced this billable amount."

2. **Invoices are document-level, not line-level.**
   - A finance invoice has a total and optional patient/examination.
   - It does not own invoice line items linked to specific treatments, contract items, procedures, providers, or teeth.

3. **Provider payout is attached to finance rows, not clinical charge lines.**
   - This works for one simple invoice/payment.
   - It becomes ambiguous when one invoice contains multiple procedures by different doctors, guest doctors, or hygienists.

4. **Payments settle invoices, but allocation is not procedure/provider-level.**
   - `finance_payments` tracks payments against a finance row.
   - It does not allocate a payment amount to individual charge lines/providers.

5. **Treatment contracts are not financial source-of-truth invoices.**
   - They have item prices and totals, but they are planning/consent artifacts.
   - They do not automatically become charge lines or invoices in a governed way.

6. **Appointments mostly have schedule impact, not financial impact.**
   - They can lead to a visit/examination/treatment.
   - They are not currently a first-class billable source except through manual finance entries.

7. **No formal production vs collection reports by provider/procedure.**
   - Finance can report money in/out and provider payout totals.
   - It cannot yet answer cleanly: production by doctor, collections by doctor, adjusted production, unpaid production, or payment allocation by procedure.

8. **No insurance/claim/write-off model yet.**
   - Current finance supports insurance as a payment method, but not payer plans, claims, EOB/ERA, write-offs, denials, patient responsibility, or claim-level reconciliation.

## Target Product Model

Doctors and clinic owners should be able to answer these questions from any relevant page:

- From a patient profile: What has this patient been charged, what did they pay, what is outstanding, and which clinical items caused it?
- From an examination: Which procedures/treatments were recommended, completed, charged, paid, refunded, or adjusted?
- From a treatment: Was it billed? Which invoice line? Which payment(s) applied? Which provider(s) get credit?
- From a provider profile: What did this provider produce, what has been collected, what is owed to the provider, and what has already been paid out?
- From finance: What clinical source created this invoice/payment/payout/COGS expense?
- From reports: What is production vs collections by provider, patient, clinic, service type, treatment type, and time period?

## Proposed Canonical Data Model

This is a future implementation plan. Do not add all tables at once without a migration/test slice.

### 1. Clinical Charge Lines

Create a canonical billable line table, for example `clinical_charge_lines`.

Suggested fields:

- `id`
- `clinic_id`
- `patient_id`
- `examination_id` nullable
- `treatment_id` nullable
- `appointment_id` nullable
- `contract_id` nullable
- `contract_item_id` nullable
- `source_entity_type` enum/string: examination, treatment, contract_item, appointment, manual, inventory_usage
- `source_entity_id`
- `provider_type`: user, guest
- `provider_user_id` nullable
- `provider_name_snapshot`
- `procedure_code` nullable
- `procedure_name`
- `tooth_number` / `tooth_numbers` nullable
- `quantity`
- `unit_price`
- `gross_amount`
- `discount_amount`
- `adjustment_amount`
- `tax_amount`
- `net_production_amount`
- `currency`
- `service_date`
- `charge_date`
- `status`: planned, accepted, completed, invoiced, voided
- `created_by`, `created_at`, `updated_at`, `voided_at`

Rules:

- Clinical modules may propose or create charge lines.
- Finance owns invoicing, payment, refund, adjustment, and reporting.
- Provider production is computed from charge lines, not from raw finance document totals.
- Completed clinical charge lines are immutable for audit except through adjustment/void rows.

### 2. Invoice Line Items

Use existing `finances` as the invoice header for now, but add line rows, for example `finance_invoice_lines`.

Suggested fields:

- `finance_id`
- `clinical_charge_line_id` nullable
- `source_entity_type`, `source_entity_id`
- `description`
- `provider_type`, `provider_user_id`, `provider_name_snapshot`
- `quantity`
- `unit_price`
- `gross_amount`
- `discount_amount`
- `tax_amount`
- `net_amount`
- `currency`

Rules:

- An invoice can contain multiple clinical charge lines.
- An invoice line can originate from a treatment, contract item, manual patient charge, no-show fee, or adjustment.
- Invoice totals must be derived from invoice lines.
- Existing document-level invoices can remain for backwards compatibility until migrated.

### 3. Payment Allocation

Add allocation rows for payments/refunds/voids/adjustments, for example `finance_payment_allocations`.

Suggested fields:

- `finance_payment_id`
- `finance_id`
- `finance_invoice_line_id`
- `clinical_charge_line_id` nullable
- `provider_type`, `provider_user_id`, `provider_name_snapshot`
- `amount`
- `allocation_date`
- `allocation_method`: manual, fifo, proportional, system

Rules:

- Money collected must be allocatable to invoice lines/providers.
- If payment is entered before final allocation, keep it unapplied/unearned until allocated.
- Provider collections are computed from allocations, not guessed from the invoice header.

### 4. Provider Production and Payout

Keep `provider_payouts`, but generate them from clinical charge lines/payment allocations depending on clinic compensation policy.

Provider compensation modes:

- Production-based: provider share accrues from charge line net production.
- Collection-based: provider share accrues only when payments are allocated to the provider's lines.
- Hybrid/manual: provider share is manually approved from invoice/payment context.

Rules:

- Current provider payout rows can remain finance-linked for compatibility.
- Future payout rows should also reference charge line or allocation source when available.
- Guest doctors must use immutable name snapshots.
- Clinic users must resolve to active/inactive user records but keep historical snapshots.

### 5. Patient Ledger

Create a unified patient ledger view/model, not necessarily a table at first.

Ledger event sources:

- clinical charge lines
- finance invoice lines
- finance payments
- refunds
- adjustments
- voids
- contract signatures
- inventory COGS references where relevant

The patient ledger should show:

- source clinical record
- invoice/document reference
- provider
- charge
- discount/adjustment
- payment
- refund
- balance
- status

### 6. Inventory COGS

Keep inventory COGS separate from provider payouts.

Rules:

- Inventory usage can remain linked to treatment.
- Auto-posted COGS expenses should reference inventory usage through `source_entity_type/source_entity_id`.
- COGS reduces clinic margin, not patient balance and not provider production unless explicitly part of a compensation formula.

## Recommended Workflow

### Appointment

- Appointment scheduling captures reason, provider/chair, timing, and maybe expected service.
- It should not create production by itself unless the clinic defines no-show/late fees.
- Follow-up from appointment should point to examination/visit context.

### Examination / Visit

- Examination is the clinical encounter.
- It can create proposed charge lines when procedures are recommended.
- It can create completed charge lines when work is performed.
- Charge capture should be visible at checkout.

### Treatment

- Treatment should describe clinical work and status.
- If it is billable, it should create or reference a clinical charge line.
- Removing the old `treatments.cost` from user-facing forms was correct: cost in treatment was ambiguous. Financial price belongs in charge/contract/invoice line context.

### Treatment Contract

- Contract items are proposal/consent lines.
- Accepted/signed contract items can become planned or accepted charge lines.
- A contract should not silently become revenue or collections.

### Finance

- Finance creates invoice headers and settlement rows.
- Finance may create manual patient charge lines when there is no clinical source.
- Finance owns money math, payment allocation, refunds, adjustments, discounts, tax, balance, treasury bucket, and provider payout settlement.

### Provider / Guest Doctor

- Provider attribution should happen at charge-line level.
- Provider payout calculation should be policy-based:
  - by production
  - by collections
  - by manual split
- Provider payout payment remains separate from patient payment.

## Implementation Phases

### Phase 0 - Documentation and Audit

Status: current document.

Deliverables:

- Document current state and target contract.
- Confirm no runtime code changes.
- Identify existing tables and code paths.

### Phase 1 - Read-Only Traceability Dashboard

Goal: prove the traceability UX without changing writes.

Deliverables:

- Patient financial trace panel:
  - related examinations
  - treatments
  - contracts
  - invoices/payments
  - provider payout references
  - inventory COGS references
- Finance detail trace panel:
  - patient
  - examination
  - source entity
  - provider payout rows
  - payments/refunds/adjustments
- Provider payout detail trace panel:
  - finance row
  - patient
  - provider
  - settlement history

No migrations unless needed for query performance.

### Phase 2 - Billable Charge Line Schema

Goal: add canonical production rows without disrupting existing finance.

Deliverables:

- `clinical_charge_lines`
- indexes on clinic, patient, examination, treatment, provider, status, service date
- model + service for creating charge lines
- no automatic invoice generation yet
- tests for tenant isolation and source ownership

### Phase 3 - Generate Charge Lines from Clinical Sources

Goal: connect clinical modules to production.

Deliverables:

- treatment -> charge line
- contract item -> planned/accepted charge line
- examination completed procedure -> charge line if applicable
- manual patient charge -> charge line with source `manual`
- audit/activity entries

### Phase 4 - Invoice Line Items

Goal: invoices become line-based.

Deliverables:

- `finance_invoice_lines`
- create invoice from selected charge lines
- invoice total derived from lines
- source links from line to clinical charge
- compatibility with old one-row invoices

### Phase 5 - Payment Allocation

Goal: collections can be traced to procedures/providers.

Deliverables:

- `finance_payment_allocations`
- payment allocation UI
- default FIFO/proportional allocation option
- manual override with audit trail
- unapplied/unearned payment handling

### Phase 6 - Provider Production / Collection / Payout Reports

Goal: answer owner/provider compensation questions cleanly.

Deliverables:

- provider production report
- provider collections report
- adjusted production report
- provider payout reconciliation
- provider AR/outstanding report
- guest provider support

### Phase 7 - Insurance / Claims Later

Goal: support payer responsibility without faking it.

Deliverables:

- payer/insurance plan records
- claims
- claim line status
- EOB/ERA posting
- write-offs
- denial/rebill handling
- patient responsibility after payer response

Do not start here. It is a larger domain.

### Phase 8 - Accounting Export / Journal Integration Later

Goal: make accounting software integration reliable.

Deliverables:

- accounting export map
- revenue, AR, cash, refunds, discounts, COGS, provider payable, supplier payable accounts
- export batches
- reconciliation status

## Non-Goals

- Do not calculate provider pay from ambiguous document totals once line-level data exists.
- Do not mix supplier payables with provider payouts.
- Do not make treatment contracts count as revenue.
- Do not treat appointments as production except explicit fees.
- Do not add insurance/claim workflows until patient-charge and payment-allocation basics are stable.
- Do not put finance math in frontend or views.
- Do not use `/api/v1/*` for first-party React finance integration.

## Recommendation

The current system is directionally good but not complete enough for "complete control and monitor to all money everywhere."

The correct next backend architecture step is not another popup or a broader finance form. It is a charge-capture layer between clinical work and finance. That layer lets DentaCare preserve clinical context, provider attribution, patient ledgers, payment allocation, collections, payouts, inventory COGS, and future accounting exports without guessing from invoice totals.

The correct product step is simpler: give users a clear money trail from patient, treatment, examination, finance transaction, and provider pages. Users should see "charged, paid, unpaid, provider share, linked records" before they ever see accounting terminology.
