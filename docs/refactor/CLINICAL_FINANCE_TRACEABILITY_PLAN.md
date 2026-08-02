# Clinical-Finance Traceability Plan

Last updated: 2026-08-02

## Product Rule

A doctor should answer “what happened clinically, what was charged, what was paid, and who is owed?” without using accounting terminology. Canonical records remain separate but visibly linked.

## Canonical Chain

```text
Appointment / Examination
  -> Treatment / Procedure / Contract item
  -> Clinical charge line
  -> Invoice line
  -> Payment allocation
  -> Provider production/collection/payout
  -> Inventory usage/COGS
```

Each fact has one owner:

| Fact | Owner |
|---|---|
| Clinical finding | Examination/odontogram |
| Procedure state | Clinical procedure/Treatment |
| Presented consented plan | Treatment contract |
| Billable production | Clinical charge line |
| Patient obligation | Invoice/invoice line |
| Money received/refunded | Finance payment/transaction |
| Provider obligation | Provider payout |
| Material cost | Inventory usage/COGS expense |

Never infer billing from appointments, tooth colors, conditions, unsigned plans, or frontend state.

## Implemented

- Read-only Money Trail on patient, treatment, examination, finance, and related detail surfaces.
- Tenant-scoped clinical charge-line foundation.
- Explicit Treatment charge capture with source, patient, examination, procedure/treatment, appointment, provider, amount, currency, and service date context.
- Invoice creation from selected captured work.
- Links from clinical records to finance records and back.
- Provider payout and inventory COGS references in the trail where available.
- Checkout read model and completion exception contract.

## Simple User Experience

- Patient: Charges, Paid, Balance due, linked treatment/visit.
- Clinical record: Billing status and one action to create/open invoice.
- Finance record: Clinical source and patient/provider context.
- Provider: Work value, collected share, paid, outstanding.
- Specialist detail remains in Finance; daily clinical pages do not expose treasury/reconciliation jargon.

## Remaining Phases

### 1. Invoice Lines

- Link invoice items to one or more charge lines.
- Preserve quantity, price, discount, tax, provider, currency, and immutable source snapshots.
- Prevent duplicate invoicing and cross-clinic/cross-patient linkage.

### 2. Payment Allocation

- Allocate payments/refunds/adjustments to invoice lines.
- Support partial and multi-payment settlement without mutating clinical truth.
- Keep backend-owned balance calculations and append-only audit evidence.

### 3. Provider Reporting

- Distinguish production, collections, contractual share, paid payout, and outstanding payout.
- Support clinic users and outside providers without mixing supplier payables.

### 4. Specialist Extensions

- Insurance/write-offs/claims only after responsibility rules are explicit.
- Accounting export/journal integration only after operational ledgers are stable.

## Guardrails

- Active-clinic scope and cross-tenant concealment on every relationship.
- CSRF, permission, PlanGuard, server validation, and activity/audit logging on writes.
- No frontend totals, allocation, payout, tax, COGS, or reconciliation logic.
- No automatic historical backfill where intent is ambiguous.
- No public tenant file paths.

## Verification

- Trace both directions across patient, clinical, invoice, payment, provider, and inventory records.
- Test partial payment, refund/adjustment, multiple providers, guest provider, mixed currencies, no-charge care, and manual finance entries.
- Verify totals against canonical finance services and confirm zero cross-tenant visibility.
- Run authenticated English/Arabic UI tests plus focused finance/clinical/App API suites.

The pre-consolidation research and schema detail remains available in Git history.
