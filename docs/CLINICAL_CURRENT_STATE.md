# Clinical Workflows Current State

## 2026-07-30 Procedure Lifecycle Foundation

- Added first-class `clinical_procedures` and append-only `clinical_procedure_status_history` tables.
- The lifecycle distinguishes proposed, presented, accepted/rejected, scheduled, in-progress, completed, referred, and cancelled work.
- Procedure creation validates patient and supplied examination, odontogram condition, treatment, appointment, contract/item, charge-line, and provider relationships against the active clinic.
- Rejection, referral, and cancellation require a reason. Terminal procedures cannot be reopened implicitly.
- Existing Treatments, Treatment Contracts, appointments, odontogram rows, and clinical charge lines remain canonical for their own facts and are not mutated by procedure transitions.
- No legacy backfill was attempted because existing Treatment `active` rows do not unambiguously distinguish proposed, accepted, scheduled, or started work.
- Phase 2B must connect new clinical workflows through explicit mappings and real workflow tests; it must not infer lifecycle state from tooth colors or financial status.

## 2026-07-26 Workflow Update

- The active encounter contract is `docs/refactor/PATIENT_CLINICAL_WORKFLOW_PLAN.md`.
- Patient baseline charting and examination charting are layered: visit-specific tooth rows override baseline rows for that examination without mutating the baseline.
- Examination create/edit save-and-chart behavior and examination chart links retain `examination_id`.
- Treatment workflows can create a clinical charge line when charge capture is explicitly enabled; conditions, appointments, and unsigned plans do not bill automatically.
- Patient odontogram snapshots provide historical read-only points; the first-class procedure-status foundation now exists but is not yet exposed in clinical UI.
- Remaining major gaps are clinical integration of the procedure lifecycle, signed/locked clinical notes and addenda, perio charting, mixed dentition, referral workflow UI, controlled baseline promotion, and a formal consent/refusal lifecycle.
- Examination reports and examination-linked treatment contracts consume the effective visit chart. A selected tooth can hand off patient, examination, tooth, and return context to canonical Treatment creation.
- Odontogram `treatment_notes` and `treatment_status` remain legacy tooth follow-up metadata. They are not canonical procedures, accepted plans, completed work, or billable events.

Last updated: 2026-05-02

## Core Modules

- **Patient Profile:** baseline odontogram, demographics, attachments, appointments history
- **Odontogram:** interactive tooth chart with conditions, procedures, and status tracking
- **Examinations:** per-visit clinical exams with exam-scoped odontogram
- **Treatments:** treatment plans linked to examinations
- **Treatment Contracts:** frozen odontogram snapshots (SVG+JSON), itemized treatment lists, e-signature capture
- **Prescriptions:** medication management linked to patients

## Current Charting Model

Three separate charting behaviors exist:

1. **Patient baseline odontogram** — loaded from patient profile, mutable, no longitudinal history
2. **Examination-scoped odontogram** — per-visit chart state with `examination_id`, closest to visit snapshot
3. **Contract snapshot odontogram** — frozen as SVG and JSON at contract creation time

## Known Gaps

- No true longitudinal patient odontogram history (timeline of changes across visits)
- No first-class child / mixed dentition support
- Contract creation artificially forced from examination flow
- Contracts currently seeded from patient baseline odontogram, not exam odontogram
- Examination/treatment workflow boundary needs clearer product concept
- Clinical charge-line capture is available explicitly from Treatment workflows; procedures do not auto-bill.
- No invoice-line/payment-allocation layer yet linking treatments/procedures/contract items to payments, provider production, provider collections, and payout reconciliation.

## Finance Traceability

Clinical modules currently connect to finance through patient/examination context, treatment contracts, and inventory usage COGS. This is useful but incomplete.

The active product/architecture plan is `docs/refactor/CLINICAL_FINANCE_TRACEABILITY_PLAN.md`.

## Key Files

- Patient controller: `app/Controllers/Patient.php`
- Examination controller: `app/Controllers/Examination.php`
- Odontogram controller: `app/Controllers/Odontogram.php`
- Treatment controller: `app/Controllers/Treatment.php`
- Treatment contracts: `app/Controllers/TreatmentContracts.php`

## Archived Audit

The full clinical workflow audit (2026-04-12) is archived in `docs/_archive/2026-05-02-docs-consolidation/audits/`.
