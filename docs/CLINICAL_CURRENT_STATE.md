# Clinical Workflows Current State

## 2026-07-26 Workflow Update

- The active encounter contract is `docs/refactor/PATIENT_CLINICAL_WORKFLOW_PLAN.md`.
- Patient baseline charting and examination charting are layered: visit-specific tooth rows override baseline rows for that examination without mutating the baseline.
- Examination create/edit save-and-chart behavior and examination chart links retain `examination_id`.
- Treatment workflows can create a clinical charge line when charge capture is explicitly enabled; conditions, appointments, and unsigned plans do not bill automatically.
- Patient odontogram snapshots provide historical read-only points, but the app still lacks a first-class procedure-status timeline.
- Remaining major gaps are signed/locked clinical notes and addenda, procedure statuses, perio charting, mixed dentition, referrals, controlled baseline promotion, and a formal consent/refusal lifecycle.

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
- Clinical charge-line foundation exists, but clinical workflows do not yet create charge lines automatically.
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
