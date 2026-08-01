# Clinical Workflows Current State

## 2026-08-01 Checkout Completion Contract

- Appointment completion is enforced at the existing server-owned stage transition endpoint for both `checkout -> completed` and direct consultation-style `in_chair -> completed` paths.
- A visit with no unresolved clinical or financial work completes normally.
- Open clinical work, uninvoiced charges, mixed currencies, outstanding balance, or date-context-only clinical links require explicit acknowledgement and a meaningful operator reason.
- The existing `appointments:edit` permission remains the authorization boundary; no frontend permission engine or new route was introduced.
- Acknowledged exceptions are retained in the appointment activity trail with stable issue codes and the supplied reason.
- Completion does not mutate clinical records, create invoices, collect money, reconcile balances, or infer authoritative links for legacy records.
- Authenticated English/Arabic and permission QA remains deferred, including verification of clean completion, blocked completion, acknowledged completion, activity evidence, and AJAX 422 responses.

## 2026-08-01 Explicit Appointment Linkage

- Examinations created from Appointment context now store a nullable, tenant- and patient-validated `appointment_id`.
- The shared Examination and Treatment create surfaces preserve this context through full-page and modal workflows.
- New appointment-context Treatments write the same appointment link to their canonical procedure and optional clinical charge line.
- Appointment checkout prefers explicit links and uses patient/date matching only as a labeled legacy fallback.
- Existing records remain unchanged; no historical appointment relationship is inferred or backfilled.
- This linkage does not complete clinical work, invoice charges, collect payments, or advance the appointment.

## 2026-07-30 Appointment Checkout Review

- Appointment detail now has a backend-owned checkout review that keeps three decisions visible: clinical work, captured charges, and payment state.
- The read model combines tenant-scoped Appointment context with same-visit examinations and Treatments, explicit procedure links where available, and the canonical Money Trail.
- Existing modules retain ownership: checkout starts or opens clinical/finance work but does not duplicate their writes, validation, calculations, or lifecycle rules.
- Open charges can use the existing invoice drawer only when they share one currency. Mixed currencies are flagged for separate finance review.
- Legacy examination/Treatment association is currently patient plus visit date and is visibly labeled as contextual, not authoritative.
- Phase 3B propagates explicit `appointment_id` into newly created examination, procedure, and charge relationships. Phase 3C now guards completion without automating checkout work.
- Manual authenticated QA remains deferred for English/Arabic, permissions, no-work, open-charge, invoiced, balance-due, settled, and mixed-currency scenarios.

## 2026-07-30 Treatment Procedure Integration

- New canonical Treatments now map one-to-one to `clinical_procedures`; existing Treatments remain intentionally unbackfilled.
- Treatment creation requires explicit workflow intent: `planned` creates a proposed procedure, while `start_now` records accepted and in-progress milestones.
- Treatment, optional inventory usage, optional clinical charge capture, procedure creation, and initial lifecycle transitions commit as one database unit.
- Clinical status and financial status remain separate: a charge can be linked without changing procedure status, and a procedure transition never creates an invoice or payment.
- Treatment edits synchronize the linked procedure name, tooth, examination, and optional charge link while preventing cross-patient reassignment.
- Completing a Treatment advances its linked procedure through the lifecycle service. Legacy Treatments with no procedure keep existing completion behavior.
- Lifecycle-linked Treatments cannot use the legacy hard-delete action because clinical history must not be orphaned.
- Treatment detail exposes lifecycle status read-only. Broad procedure editing, legacy backfill, patient-list lifecycle badges, correction/reopen, reasoned cancellation, and contract/appointment integration remain later slices.

## 2026-07-30 Procedure Lifecycle Foundation

- Added first-class `clinical_procedures` and append-only `clinical_procedure_status_history` tables.
- The lifecycle distinguishes proposed, presented, accepted/rejected, scheduled, in-progress, completed, referred, and cancelled work.
- Procedure creation validates patient and supplied examination, odontogram condition, treatment, appointment, contract/item, charge-line, and provider relationships against the active clinic.
- Rejection, referral, and cancellation require a reason. Terminal procedures cannot be reopened implicitly.
- Existing Treatments, Treatment Contracts, appointments, odontogram rows, and clinical charge lines remain canonical for their own facts and are not mutated by procedure transitions.
- No legacy backfill was attempted because existing Treatment `active` rows do not unambiguously distinguish proposed, accepted, scheduled, or started work.
- Phase 2B now connects newly created Treatments through explicit intent and tested mappings; it does not infer lifecycle state from tooth colors or financial status.

## 2026-07-26 Workflow Update

- The active encounter contract is `docs/refactor/PATIENT_CLINICAL_WORKFLOW_PLAN.md`.
- Patient baseline charting and examination charting are layered: visit-specific tooth rows override baseline rows for that examination without mutating the baseline.
- Examination create/edit save-and-chart behavior and examination chart links retain `examination_id`.
- Treatment workflows can create a clinical charge line when charge capture is explicitly enabled; conditions, appointments, and unsigned plans do not bill automatically.
- Patient odontogram snapshots provide historical read-only points; the first-class procedure-status foundation now exists but is not yet exposed in clinical UI.
- Remaining major gaps are procedure integration with plans/contracts and scheduling, reasoned cancellation/correction UI, signed/locked clinical notes and addenda, perio charting, mixed dentition, referral workflow UI, controlled baseline promotion, and a formal consent/refusal lifecycle.
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
