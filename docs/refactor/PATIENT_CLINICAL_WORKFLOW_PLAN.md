# Patient Clinical Workflow Plan

Last updated: 2026-07-26

## Product Goal

DentaCare should let a doctor move through a patient visit without repeatedly finding the patient, losing context, or mixing clinical facts with financial records.

The daily sequence is:

1. **Prepare** - confirm identity, medical alerts, medications, reason for visit, and relevant images.
2. **Assess** - record complaint, history, findings, diagnosis, prognosis, and notes in an examination.
3. **Chart** - review the patient baseline odontogram and record visit-specific tooth changes.
4. **Plan or perform** - add treatment linked to the examination, provider, tooth, and optional charge capture.
5. **Complete** - finalize the examination and performed treatment accurately.
6. **Checkout** - invoice captured work, record payment when appropriate, and schedule follow-up.

The interface should present the next likely action without forcing one path. Emergency care, consultation-only visits, no-treatment visits, multi-visit treatment, outside referrals, and follow-up reviews remain valid.

## External Product Findings

The reviewed professional systems share several rules:

- Open Dental keeps conditions, existing work, planned procedures, referred work, and completed procedures as distinct chart states. Planned procedures remain in treatment planning; completed procedures post to the account.
- Dentrix Ascend recommends attaching procedures to appointments and completing them from the clinical chart/progress notes. Completed procedures appear in both clinical and financial records.
- Chart, treatment plan, appointment, and account stay linked, while each module retains canonical ownership.
- Dentrix uses clinical-note completion/signing controls and task lists so completed procedures are not left without documentation.
- The ADA treats the dental record as a legal clinical record and recommends organized documentation of history, findings, diagnoses, performed treatment, recommendations, consent/refusal, images, prescriptions, referrals, and follow-up. Financial records remain separately maintained even when linked.

References:

- https://www.opendental.com/manual/chart.html
- https://www.opendental.com/manual/entertreatment.html
- https://www.opendental.com/manual/treatmentplan.html
- https://hsps.pro/DentrixAscend/Help/Clinical.htm
- https://hsps.pro/DentrixAscend/Help/Posting_procedures_-_ledger.htm
- https://hsps.pro/DentrixAscend/Help/Entering_clinical_notes_not_entered.htm
- https://www.ada.org/resources/practice/practice-management/documentation-patient-records
- https://www.ada.org/resources/practice/practice-management/writing-in-the-dental-record

## Current Strengths

- Patient-centered command workspace and contextual create drawers.
- Examinations capture complaint, health updates, findings, diagnosis, prognosis, recommendations, and notes.
- Working patient baseline odontogram, examination-scoped rows, and historical snapshots.
- Treatments link to patient, optional examination, provider, tooth, inventory usage, and optional charge capture.
- Contracts support itemized plans, consent/signature, and frozen odontogram snapshots.
- Prescriptions, appointments, imaging, attachments, activity, and finance remain linked to the patient.
- Clinical charge lines connect treatment work to invoices and the money trail.

## Clinical Record Boundaries

| Concept | Meaning | Financial effect |
|---|---|---|
| Patient baseline chart | Current known dental state | None |
| Visit chart override | Finding or state recorded during one examination | None |
| Condition / diagnosis | Clinical fact requiring observation or care | None |
| Planned treatment | Recommended work not yet performed | Not revenue |
| Accepted plan / contract | Consent to proposed work | Not revenue |
| Completed treatment | Work actually performed | May produce a billable charge |
| Invoice | Financial document for selected charges | Creates patient balance |
| Payment/refund/adjustment | Money settlement event | Changes balance and treasury |

## Odontogram Contract

- Patient context edits the patient baseline.
- Examination context always includes `examination_id`.
- The visit chart displays baseline conditions for context.
- An examination row overrides the matching baseline tooth for that visit.
- Saving in visit context changes only the examination row.
- Resetting a visit override must never delete the baseline.
- Examination completion may capture a read-only snapshot but must not silently merge visit findings into baseline.
- Contract snapshots remain frozen consent artifacts.

Implemented in this slice:

- Examination create/edit "save and chart" redirects retain `examination_id`.
- Examination list/detail chart links open the visit chart.
- Visit charts layer examination overrides over baseline without copying data.
- Visit-chart statistics use the effective layered chart.
- The odontogram identifies patient-baseline versus visit context.
- Examination detail exposes Open visit chart as a primary action.
- Examination reports use the same effective visit chart shown during the examination.
- Treatment contracts launched from an examination are seeded from that effective visit chart, not from a stale patient baseline.
- A selected tooth can hand patient, examination, tooth, and return context to canonical Treatment creation.

### Odontogram Relationship Contract

The odontogram is a clinical visual index, not the owner of every downstream workflow.

| Relationship | Contract |
|---|---|
| Patient | Owns the current baseline dental state and historical chart snapshots. |
| Examination | Owns visit findings. Its effective chart layers visit overrides over the baseline. |
| Treatment | Owns the canonical procedure, provider, dates, status, inventory usage, and optional charge capture. |
| Treatment contract | Owns the presented/accepted plan and freezes the relevant chart at creation time. |
| Appointment | Owns scheduling; it may point to examination/treatment context but does not define clinical truth. |
| Finance | Owns invoices, payments, allocations, payouts, and all money calculations. |
| Attachments/imaging | Owns private clinical files; future work may link them to teeth and procedures. |

Fast doctor workflow:

1. Open the patient or examination chart.
2. Select a tooth and record the finding.
3. Use **Add Treatment** when a real procedure is proposed or performed; patient, visit, and tooth are prefilled.
4. Use **Create treatment plan** when the work must be presented or accepted; the correct visit chart is frozen with it.
5. Complete treatment in the Treatment module. Only explicit charge capture creates a financial obligation.

The chart must never infer a diagnosis, procedure, provider, fee, completion, or invoice from a tooth color or follow-up status. The existing `treatment_notes` and `treatment_status` columns are tooth follow-up metadata, not canonical procedure records.

## Simple Doctor Experience

### Patient Profile

The patient profile is the clinical home: identity and alerts first, then appointments and focused clinical tabs. Contextual actions open canonical forms. It must not duplicate the full examination, odontogram, treatment, or finance modules.

### Examination

The examination is the encounter record: review medical changes, document complaint/findings/diagnosis, open the visit chart, add linked treatment or plan, complete the assessment, invoice captured work if present, and schedule follow-up when needed.

No-treatment and consultation-only examinations are valid.

### Treatment

Treatment represents proposed or performed work. Patient, examination, tooth, provider, status, inventory usage, and optional charge capture remain linked. Planned treatment is not automatically completed or billed.

### Checkout

Checkout exposes simple actions: create invoice from selected captured work, record payment, show balance due, and schedule follow-up. Allocation, provider settlement, treasury, and reconciliation remain specialist finance workflows.

## Supported Scenarios

- Routine exam with no treatment.
- Emergency visit with immediate treatment.
- Treatment planned for a later visit.
- Multi-visit treatment.
- Existing work/history without a charge.
- Outside referral.
- Patient declines recommended care.
- Treatment with charge capture disabled.
- Explicit manual finance charge without a clinical source.
- Partial payment after completed clinical work.

## Deferred Clinical Capabilities

1. Signed/locked clinical notes with addenda and a missing-note queue.
2. Procedure catalog with condition, planned, existing, referred, in-progress, and completed states.
3. Perio charting and longitudinal comparison.
4. Mixed/primary dentition.
5. Referral cases and completion tracking.
6. Formal consent/refusal lifecycle beyond treatment contracts.
7. Imaging series linked to teeth/procedures.
8. Appointment-to-procedure grouping for multi-visit treatment.
9. Controlled promotion of visit findings into baseline.
10. Date-range clinical reports combining chart, notes, treatment, prescriptions, and images.

Each requires a separate contract, permissions, audit rules, migrations where necessary, and real workflow testing.

## Guardrails

- No automatic diagnosis or treatment recommendation.
- No automatic billing from conditions, appointments, unsigned plans, or contracts.
- No frontend-owned clinical or finance truth.
- No baseline mutation from visit charting without an explicit reviewed workflow.
- No cross-tenant clinical access.
- Preserve CSRF, permissions, PlanGuard, activity logging, private storage, and backend validation.

## Verification

- Baseline remains unchanged when a visit override is saved.
- Visit chart shows baseline teeth and visit overrides together.
- Save-and-chart and examination links retain examination context.
- Resetting a visit override does not remove baseline.
- Existing baseline odontogram remains functional.
- Existing treatment, appointment, invoice, and follow-up drawers still use canonical endpoints.
- English and Arabic context labels are equivalent.
- Clinical finance and App API regression tests pass.
