# Finance and Clinical Workflow Simplification Plan

Last updated: 2026-07-26

## Purpose

DentaCare already has strong financial traceability: clinical charge lines, invoices, payments, expenses, supplier payables, provider payouts, treasury custody, inventory COGS, and linked patient/clinical money trails. The problem is no longer missing capability. The problem is that too much of the accounting model is presented at the same visual and workflow level.

This plan simplifies the daily experience for doctors, receptionists, and clinic managers without weakening backend accounting, tenant isolation, permissions, audit history, or clinical-finance traceability.

## Product Rule

The default interface must answer five clinic questions:

1. What work was done?
2. What was charged?
3. What was paid?
4. What is still due?
5. Who provided the work?

Accounting details remain available when needed, but they must not dominate routine clinical work.

## External Product Findings

The reviewed dental and financial products converge on the same pattern:

- Open Dental centers routine patient finance in the patient Account module. Its primary actions are payment, adjustment, statement, and invoice. Detailed payment allocation exists underneath and can automatically follow oldest outstanding production.
- CareStack separates the patient menu into Profile, Treatments, Billing, and Quick Links. Add Payment is a contextual patient action; ledger, claims, and statements remain specialist billing surfaces.
- Dentrix allows users to open treatment planning from the chart and ledger, preserving patient context while keeping treatment and ledger responsibilities distinct.
- Stripe presents invoice creation as a short lifecycle with itemized services and status. Advanced invoice options are progressively disclosed instead of competing with the core customer, items, amount, and collection method.

Sources:

- https://www.opendental.com/manual/account.html
- https://www.opendental.com/manual/payment.html
- https://www.opendental.com/manual/allocationssetup.html
- https://carestack.zendesk.com/hc/en-us/articles/27105893982100-Explore-the-Patient-Menu
- https://carestack.zendesk.com/hc/en-us/articles/24747924378388-Ledger-Transactions-and-Extended-Features
- https://docs.stripe.com/invoicing/overview
- https://docs.stripe.com/invoicing/dashboard

## Current DentaCare Assessment

### Keep

- Clinical charge lines as the canonical bridge between clinical work and invoices.
- Backend-owned totals, discounts, tax, balances, allocations, provider shares, payouts, and treasury custody.
- Patient, examination, treatment, finance, provider, and inventory trace links.
- Contextual drawers and modals that preserve the user's current entity.
- Separate supplier payable and provider payout lanes.
- Existing permissions, PlanGuard behavior, CSRF, tenant scoping, and audit logging.

### Simplify

- Do not present every finance module as an equal primary action.
- Replace generic "Add income" entry with the two jobs users actually perform: Record payment and Create invoice.
- Keep New expense as the clinic spending action.
- Move treasury transfers, bills to pay, and provider payments into a secondary finance-area navigation strip.
- Remove repeated explanatory paragraphs when a label, amount, status, and icon are sufficient.
- Show patient-facing language by default: charge, invoice, payment, refund, balance due, provider share.
- Keep accounting language inside reports and specialist finance pages.
- Use progressive disclosure for uncommon currency, tax, discount, provider-share, attachment, and treasury details where existing workflow constraints permit.

## Information Architecture

### Daily Clinic Layer

- Patient profile
- Treatment/examination context
- Record payment
- Create invoice from captured work
- New expense
- Balance due
- Recent activity

### Finance Operations Layer

- Transaction ledger
- Bills to pay
- Provider payments
- Money moves

### Owner/Accounting Layer

- Finance reports
- Receivable/payable aging
- Treasury closing balances
- Provider production and collections
- Audit and exports

Users may move downward into detail, but specialist concepts should not be forced upward into the daily clinic layer.

## Implementation Phases

### Phase 1 - Finance Home Simplification

Status: completed in `2a56475`.

- Replace generic Add Income with Record Payment and Create Invoice entry points.
- Keep New Expense as a distinct action.
- Reduce hero action competition.
- Move treasury, payables, and provider payments to a secondary navigation strip.
- Remove summary-card explanation text and decorative noise.
- Preserve existing modal forms, routes, calculations, permissions, and POST behavior.

### Phase 2 - Transaction Form Progressive Disclosure

Status: completed for shared quick-create forms.

- Keep patient, transaction type, amount, payment method, date, and service as the visible core.
- Show exchange rate only for non-base currency.
- Show check upload only for check payments.
- Show due date only for invoices or unpaid expenses.
- Show paid date only for payments/settled records.
- Keep discounts, tax, total, and provider share visible because they change stored financial meaning and were intentionally retained in the established clinic workflow.
- Move linked records, attachments, descriptions, references, and notes into one consistent optional section.
- Preserve all submitted field names and validation rules.

### Phase 3 - Patient Finance Simplification

Status: completed for the current patient finance tab.

- Lead with balance due, total billed, total paid, and refunds.
- Use one readable ledger plus a collapsed money-trail drill-down.
- Keep direct actions contextual: Create invoice, Record payment, and Open transaction.
- Move provider payout and treasury detail out of the patient-facing layer.

### Phase 4 - Clinical Billing Handoff

Status: completed for the existing treatment, examination, and appointment billing drawers.

- On treatment and examination pages, show one billing state: Not captured, Ready to invoice, Invoiced, Partially paid, or Paid.
- Keep one primary next action based on state.
- Continue using contextual drawers so the user remains on the clinical record.
- Never create duplicate finance records or infer money from display-only values.

### Phase 5 - Specialist Finance Pages

Status: completed for reports previously simplified, plus payables, payouts, and treasury transfers.

- Simplify reports into owner questions before accounting breakdowns.
- Keep payables focused on vendor, due date, amount due, and payment action.
- Keep payouts focused on provider, earned, paid, remaining, and payment action.
- Keep treasury focused on where money currently is and moving it safely.

### Phase 6 - Terminology and Explanation Audit

Status: completed for the touched daily and specialist finance surfaces; continue opportunistically when related pages change.

- Audit English and Arabic together.
- Remove repeated subtitles and instructional prose.
- Keep help text only for consequences, uncommon terms, validation constraints, and irreversible actions.
- Ensure Arabic uses familiar clinic language and natural RTL layout.

## Deferred Cleanup

The legacy full-page `finance/create.php`, `finance/expense_create.php`, and edit variants still contain older form markup alongside the newer shared quick-create partials. They are intentionally not consolidated in this program because they include established validation rendering and page-specific behavior. Converting them should be a separate compatibility slice with side-by-side POST testing for create, edit, validation failure, currency, payment method, liquidity account, and provider-share behavior.

## Guardrails

- No schema changes in visual simplification slices.
- No finance calculations in views or browser code beyond existing display calculators.
- No automatic invoice/payment/payout creation without explicit backend contracts.
- No weakening tenant scope, permissions, PlanGuard, CSRF, or audit logging.
- No merging provider payouts with supplier payables.
- No treating treatment plans/contracts as collected revenue.
- No hiding a field that changes stored financial meaning unless it remains discoverable through progressive disclosure.
- No frontend-only balance or allocation truth.

## Acceptance Criteria

- A doctor can identify the correct daily finance action without understanding accounting terminology.
- The finance home has a clear primary/secondary action hierarchy.
- Patient and clinical pages show financial state in plain language.
- Specialist accounting controls remain available but do not dominate routine care workflows.
- Existing routes, request fields, calculations, permissions, and audit links continue to work.
- English and Arabic remain equivalent in meaning and interaction hierarchy.
