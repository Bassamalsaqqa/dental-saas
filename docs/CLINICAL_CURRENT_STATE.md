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
10. Proposed/presented Treatment work can be explicitly accepted; accepted work without a scheduled visit appears in Dashboard and Treatments recovery queues.
11. Scheduling from that queue reuses the canonical Appointment workflow, preserves the original encounter link, records the future scheduled appointment separately, and advances the procedure to `scheduled` only after the appointment succeeds.

New appointment-context Examinations, procedures, and charge lines preserve explicit appointment identity. Ambiguous historical records are not backfilled.

## Safety Boundaries

- No automatic diagnosis, treatment recommendation, clinical completion, billing, or payment.
- Conditions and accepted plans are not revenue.
- Visit overrides never silently replace patient baseline state.
- Signed Examination notes are immutable; correction happens only through an attributed addendum.
- Tenant scope, CSRF, permissions, PlanGuard, audit logging, and private storage remain authoritative.

## Remaining Clinical Work

1. Reasoned procedure correction/reopen/cancellation UI.
2. Recall and no-show/cancellation recovery queues.
3. Perio charting, mixed dentition, referral tracking, and controlled baseline promotion.
4. Imaging linked to teeth/procedures and richer longitudinal clinical reporting.
5. Broader clinical-document signature coverage beyond Examinations.

Contracts and scenarios are in `refactor/PATIENT_CLINICAL_WORKFLOW_PLAN.md` and `refactor/PRODUCT_WORKFLOW_OPTIMIZATION_ROADMAP.md`.

## Treatment catalog storage correction (PR22, isolated development)

The deployed eight-key ENUM cannot store41 of43 current offered catalog keys. A dedicated forward-only migration `2026-10-29-120000_WidenTreatmentTypeStorage.php` widens only that column to VARCHAR100 while retaining strict catalog validation and historical string bytes. No remapping, autocharge, classification or retrospective write is introduced. The production-schema synthetic clone includes all67 tables/26 foreign keys and actual indexes; zero triggers exist in the inspected production schema. [Exact fail-before/pass-after and43 routed create/edit/read/filter results](ui-evidence/product-ux/treatment-storage.json).

The migration requires standalone DDL, reviewed exact existing metadata, known historical keys and supported indexes. It takes an explicit treatment-table WRITE lock across preservation snapshots and COPY ALTER; reads/writes pause. Local/Hostinger timings are rehearsal measurements, not a downtime promise. Automatic down refuses ENUM narrowing. Prior compatible source may run against the wider string column; preserve it on source rollback. Production schema change and deployment still require separate authorization after review, fresh recoverable backup and rehearsal. A source-only deployment does not repair the old ENUM.
