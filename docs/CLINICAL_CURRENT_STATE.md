# Clinical Current State

Last updated: 2026-08-02

## Working Model

- Patient baseline odontogram is the current known chart.
- Examination odontogram stores visit-specific overrides layered over that baseline.
- Treatment owns procedure intent, provider, tooth, dates, lifecycle, inventory use, and optional charge capture.
- Treatment contracts own presented/accepted plans, consent/signature, items, and frozen chart snapshots.
- Finance owns charges, invoices, payments, allocations, payouts, and all money math.
- Attachments use private tenant storage.

The odontogram is a clinical index. Tooth color/status must not infer diagnosis, treatment, completion, provider, fee, or billing.

## Implemented Workflow

1. Appointment advances through server-owned visit stages.
2. Examination records encounter details and can open an examination-scoped chart.
3. Visit chart overlays baseline findings without mutating the baseline.
4. New Treatment records explicit plan/start intent and creates an audited procedure lifecycle.
5. Optional charge and inventory capture commit with the Treatment transaction.
6. Appointment checkout reviews clinical state, open charges, invoice/payment state, and legacy contextual links.
7. Clean checkout completes normally; unresolved items require an acknowledged reason retained in appointment activity.
8. A completed Examination can be signed once. Its clinical narrative is snapshotted and locked; later corrections are attributed append-only addenda and appear in printed records.
9. Dashboard and Examination registry identify completed unsigned notes and older unfinished drafts for follow-up without bulk signing.

New appointment-context Examinations, procedures, and charge lines preserve explicit appointment identity. Ambiguous historical records are not backfilled.

## Safety Boundaries

- No automatic diagnosis, treatment recommendation, clinical completion, billing, or payment.
- Conditions and accepted plans are not revenue.
- Visit overrides never silently replace patient baseline state.
- Signed Examination notes are immutable; correction happens only through an attributed addendum.
- Tenant scope, CSRF, permissions, PlanGuard, audit logging, and private storage remain authoritative.

## Remaining Clinical Work

1. Reasoned procedure correction/reopen/cancellation UI.
2. Recall and accepted-unscheduled treatment queues.
3. Perio charting, mixed dentition, referral tracking, and controlled baseline promotion.
4. Imaging linked to teeth/procedures and richer longitudinal clinical reporting.
5. Broader clinical-document signature coverage beyond Examinations.

Contracts and scenarios are in `refactor/PATIENT_CLINICAL_WORKFLOW_PLAN.md` and `refactor/PRODUCT_WORKFLOW_OPTIMIZATION_ROADMAP.md`.
