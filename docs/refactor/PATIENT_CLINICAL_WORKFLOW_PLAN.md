# Patient Clinical Workflow Contract

Last updated: 2026-08-02

## Goal

Let a doctor complete a patient visit quickly without losing context or mixing clinical truth with financial records.

## Daily Flow

1. **Prepare:** identity, medical alerts, medications, visit reason, relevant images.
2. **Assess:** complaint, history, findings, diagnosis, prognosis, recommendations, notes.
3. **Chart:** review baseline odontogram and record visit-specific overrides.
4. **Plan or perform:** create Treatment linked to patient, examination, tooth, provider, and optional explicit charge.
5. **Complete:** finalize accurate clinical records; never infer completion from chart color or billing.
6. **Checkout:** review work, invoice selected captured charges, record/defer payment, and schedule follow-up.

Consultation-only, emergency, no-treatment, multi-visit, referral, declined-care, and follow-up visits are valid paths.

## Record Ownership

| Record | Owns | Financial effect |
|---|---|---|
| Patient baseline chart | Current known dental state | None |
| Examination/visit chart | Encounter findings and overrides | None |
| Treatment/procedure | Proposed or performed clinical work and lifecycle | Optional explicit charge only |
| Treatment contract | Presented/accepted plan, consent, frozen snapshot | Not revenue |
| Appointment | Schedule and visit stage | None by itself |
| Finance | Charges, invoices, payments, refunds, allocations, payouts | Canonical money truth |
| Attachments | Private clinical files | None |

## Odontogram Rules

- Patient context edits baseline state.
- Examination context requires `examination_id` and writes only visit overrides.
- Effective visit chart layers overrides over baseline without copying or mutating untouched baseline rows.
- Resetting a visit override reveals, not deletes, baseline state.
- Contract snapshots remain frozen.
- Tooth findings may hand patient/examination/tooth context to Treatment, but never infer procedure, provider, fee, completion, or invoice.

## Procedure and Checkout Rules

- New Treatment requires explicit plan/start intent and maps to an audited procedure lifecycle.
- Conditions and accepted plans are not completed work or revenue.
- Optional inventory and charge capture are explicit and backend-owned.
- Checkout displays clinical, billing, and settlement state from canonical services.
- Clean visits may complete directly; unresolved items require an audited acknowledgement/reason.
- No workflow auto-completes clinical work, creates invoices, forces payment, or rewrites legacy relationships.

## Deferred Capabilities

- Signed/locked notes, addenda, and missing-note queue.
- Reasoned correction/reopen/cancellation UI.
- Recall and accepted-unscheduled treatment queues.
- Perio charting, mixed dentition, referral tracking, controlled baseline promotion.
- Imaging linked to teeth/procedures and richer longitudinal reports.

## Verification

- Baseline remains unchanged after visit chart writes.
- Context survives Appointment -> Examination -> Treatment -> checkout handoffs.
- Clinical and financial statuses remain independent and traceable.
- Full-page and drawer forms use canonical endpoints and preserve tenant/permission/CSRF rules.
- English/Arabic and view/edit permission paths are tested.
- Clinical, finance, appointment, and App API regression suites pass.

The external research and full prior specification remain available in Git history.
