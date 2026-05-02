# Odontogram And Clinical Workflow Audit

Date: 2026-04-12
Scope: `odontogram`, `examination`, `treatment`, `treatment contracts`, patient clinical workflow

## Purpose

This document captures:

- how odontograms are actually wired in the current codebase
- how examination, treatment, and contract currently relate to each other
- where the current implementation diverges from common clinical software patterns
- a simple set of options for making the workflow more professional without overbuilding

## Executive Summary

The current codebase has three separate charting behaviors:

1. A live patient baseline odontogram
2. An examination-scoped odontogram
3. A contract snapshot odontogram

This is workable, but it is not yet a coherent clinical charting model.

The biggest gaps are:

- no true longitudinal patient odontogram history
- no first-class child / mixed dentition support
- contract creation is artificially forced from examination
- contracts are currently seeded from the patient baseline odontogram, not the examination odontogram
- examination and treatment are related in code, but the workflow boundary between them is not yet clear enough as a product concept

## Current Codebase Reality

### 1) Patient baseline odontogram

The patient profile loads one baseline chart for the patient:

- [Patient.php](C:\xampp\htdocs\dental-saas\app\Controllers\Patient.php#L681)
- [show.php](C:\xampp\htdocs\dental-saas\app\Views\patient\show.php#L601)
- [show.php](C:\xampp\htdocs\dental-saas\app\Views\patient\show.php#L4111)

Behavior:

- patient profile uses `getOdontogramByPatient($id)`
- that returns only rows with `examination_id IS NULL`
- patient profile can directly edit tooth conditions through `odontogram/update-tooth`

Implication:

- this is the patient's current baseline chart
- it is mutable
- editing it overwrites the current patient-level tooth state
- it is not a longitudinal chart history

### 2) Examination-scoped odontogram

The examination detail page loads a chart specific to that examination:

- [Examination.php](C:\xampp\htdocs\dental-saas\app\Controllers\Examination.php#L238)
- [Examination.php](C:\xampp\htdocs\dental-saas\app\Controllers\Examination.php#L406)
- [show.php](C:\xampp\htdocs\dental-saas\app\Views\examination\show.php#L116)

Behavior:

- exam detail uses `getOdontogramByExamination($id)`
- exam-side tooth updates write with `examination_id`
- opening `/odontogram/{patient}?examination_id={exam}` shows that exam-specific chart state

Implication:

- an examination can hold visit-specific tooth conditions
- this is the closest thing the app has to a per-visit snapshot
- but there is no explicit timeline UX or chart-history workflow on the patient record

### 3) Contract snapshot odontogram

Treatment contracts freeze the odontogram as SVG and JSON:

- [TreatmentContracts.php](C:\xampp\htdocs\dental-saas\app\Controllers\TreatmentContracts.php#L120)
- [TreatmentContracts.php](C:\xampp\htdocs\dental-saas\app\Controllers\TreatmentContracts.php#L172)
- [TreatmentContractOdontogramSnapshotModel.php](C:\xampp\htdocs\dental-saas\app\Models\TreatmentContractOdontogramSnapshotModel.php)
- [2026-02-05-130000_CreateTreatmentContractsTables.php](C:\xampp\htdocs\dental-saas\app\Database\Migrations\2026-02-05-130000_CreateTreatmentContractsTables.php#L155)

Behavior:

- contract create/update posts hidden `odontogram_json` and `odontogram_svg`
- those are persisted into `treatment_contract_odontogram_snapshots`
- contract show/print/sign surfaces use that frozen snapshot

Implication:

- this is the only true snapshot system currently in the app
- it is appropriate for treatment-plan documentation and signing
- it is not a general patient chart-history system

## Data Model Limits

### 1) Permanent dentition only

The odontogram model only supports teeth `1..32`:

- [OdontogramModel.php](C:\xampp\htdocs\dental-saas\app\Models\OdontogramModel.php#L38)
- [OdontogramModel.php](C:\xampp\htdocs\dental-saas\app\Models\OdontogramModel.php#L161)

Current assumptions:

- tooth numbers must be less than 33
- total teeth is hardcoded to 32
- quadrant mapping is permanent-dentition only

Implication:

- no deciduous dentition
- no mixed dentition
- no age-assisted dentition mode
- no dual chart model for pediatric patients

### 2) One row per tooth per scope

The odontogram upsert behavior is:

- patient baseline scope: one row per patient + tooth where `examination_id IS NULL`
- examination scope: one row per examination + tooth

Reference:

- [OdontogramModel.php](C:\xampp\htdocs\dental-saas\app\Models\OdontogramModel.php#L92)

Implication:

- the model stores current state per tooth per scope
- it does not store a change log per tooth
- there is no version sequence or valid-from / valid-to tracking

## Workflow Reality: Examination vs Treatment vs Contract

### Examination

The examination model is the clinical assessment encounter:

- [ExaminationModel.php](C:\xampp\htdocs\dental-saas\app\Models\ExaminationModel.php)

It stores:

- chief complaint
- history of present illness
- medical and dental history
- structured health update questions
- clinical findings
- diagnosis
- prognosis
- recommendations
- notes

This is the closest thing to a dentist visit assessment or clinical encounter.

### Treatment

The treatment model is the performed or planned clinical procedure record:

- [TreatmentModel.php](C:\xampp\htdocs\dental-saas\app\Models\TreatmentModel.php)
- [Treatment.php](C:\xampp\htdocs\dental-saas\app\Controllers\Treatment.php)

It stores:

- treatment type
- tooth number / tooth numbers
- treatment description and notes
- cost
- status
- dates
- optional `examination_id`

This is closer to a procedure record than an encounter note.

### Contract / treatment plan

The treatment contract is the patient-facing planned treatment agreement:

- [TreatmentContractModel.php](C:\xampp\htdocs\dental-saas\app\Models\TreatmentContractModel.php)
- [TreatmentContracts.php](C:\xampp\htdocs\dental-saas\app\Controllers\TreatmentContracts.php)

It stores:

- patient
- examination reference
- total cost
- terms / payment / duration
- line items
- signature state
- odontogram snapshot

This is a treatment plan / financial consent artifact, not the clinical chart itself.

### Current mismatch

The current product flow requires contracts to start from examination:

- [TreatmentContracts.php](C:\xampp\htdocs\dental-saas\app\Controllers\TreatmentContracts.php#L63)

But the create screen seeds the chart from the patient baseline:

- [TreatmentContracts.php](C:\xampp\htdocs\dental-saas\app\Controllers\TreatmentContracts.php#L81)

Implication:

- the workflow says "contract belongs to this examination"
- the chart data says "use current patient baseline"
- that is inconsistent and can produce wrong medico-legal context

## What Professional Systems Commonly Do

Public dental software and professional references consistently show these patterns:

### 1) Chart as an integrated clinical workspace

The chart is typically connected to:

- current dentition state
- treatment planning
- progress notes
- per-tooth history
- images / x-rays

References:

- Open Dental Chart Module: https://opendental.com/manual/chart.html
- CareStack Charting: https://carestack.com/dental-software/features/charting
- XLChart overview: https://www.xldent.com/products/xlchart

### 2) Separate baseline chart from treatment plan and progress history

Common pattern:

- baseline chart documents what the mouth currently looks like
- treatment plan documents proposed care
- progress notes / chart history document what changed over time

References:

- Dentally base chart and history workflow: https://help.dentally.com/en/articles/3563965-how-to-record-a-baseline-chart
- Dentally chart overview and history: https://help.dentally.com/en/articles/3565932-how-to-chart-in-dentally
- Open Dental treatment plan workflow: https://www.opendental.com/manual/charttp.html
- Open Dental progress notes and charting notes: https://www.opendental.com/manual/notes.html

### 3) Child / adult / mixed dentition support is normal

Professional systems commonly support:

- permanent dentition
- deciduous dentition
- mixed dentition

References:

- Open Dental missing / primary / mixed dentition: https://www.opendental.com/manual/missingteeth.html
- Open Dental graphical tooth chart and nomenclature: https://www.opendental.com/manual/graphicaltoothchart.html
- Dentally mixed dentition / predictive dentition guidance:
  - https://help.dentally.com/en/articles/3565932-how-to-chart-in-dentally
  - https://help.dentally.com/en/articles/11018193-how-to-set-up-your-chart-for-mixed-dentition
  - https://help.dentally.com/en/articles/10278653-how-to-use-age-based-predictive-dentition-base-charting

### 4) Historical playback / tooth history matters

Professional charting often supports:

- viewing tooth history directly from the chart
- showing complete history in a history tab or clinical timeline
- showing how chart graphics looked over time

References:

- Open Dental graphical chart history slider: https://www.opendental.com/manual/graphicaltoothchart.html
- Dentally tooth history and history tab: https://help.dentally.com/en/articles/3565932-how-to-chart-in-dentally

### 5) Recordkeeping requires historical assessment context

Professional recordkeeping guidance expects access to the history and details of assessment, treatment recommendations, alternatives, risks, and care provided.

Reference:

- AAPD recordkeeping best practice: https://www.aapd.org/research/oral-health-policies--recommendations/record-keeping/

## What We Could Not Reliably Prove From Public Sources

The user specifically asked about DentiMax / DentiMax360.

Public marketing material confirms that DentiMax presents charting, notes, imaging, and history review in an integrated workflow, but I did not find public vendor documentation detailed enough to responsibly claim:

- exact dentition-mode support
- exact chart-history model
- exact snapshot/versioning behavior

Useful public references found:

- DentiMax practice management overview: https://dentimax.com/dental-practice-management-software/
- DentiMax notes / charting messaging: https://dentimax.com/why-dental-clinical-notes-templates/
- DentiMax public product overview: https://dentimax.com/with-so-many-software-choices-why-choose-dentimax/

Conclusion:

- we can use DentiMax only as a soft directional reference for integrated charting
- we should not treat undocumented DentiMax behavior as fact

## The One-Year / 12-Visit Scenario

Scenario:

- patient comes in with one tooth state
- after one year and many visits, the tooth state is different
- clinician wants to understand progress and preserve historical context

Current app behavior:

- if clinicians use the patient profile odontogram, they overwrite the live baseline
- if clinicians use examination-scoped odontogram entries, each exam can preserve that visit's tooth state
- if a contract is created, the contract preserves a signed snapshot

What is missing:

- patient-level chart history timeline
- compare view between snapshots
- per-tooth visit history surfaced from one place

So the current app only partially solves this scenario.

## Recommended Product Distinction

The simplest professional distinction should be:

### Examination

Use examination as the clinical assessment encounter.

It should answer:

- why is the patient here
- what did we observe
- what is the diagnosis
- what is the recommendation
- what did the chart look like during this encounter

### Treatment

Use treatment as the procedure record.

It should answer:

- what was actually done
- on which tooth / teeth
- when
- by whom
- with what materials / notes / cost / outcome

### Contract / Treatment Plan

Use contract as the proposed treatment and consent artifact.

It should answer:

- what do we recommend doing
- what will it cost
- what is the agreed plan
- what chart snapshot supported that discussion

### Odontogram

Use odontogram as a charting system with these layers:

1. baseline dentition chart
2. examination snapshot chart
3. contract snapshot chart
4. tooth history / chart timeline

## Options To Improve This While Keeping It Simple

### Option A - Minimum viable correction

Goal:

- keep current architecture mostly intact
- remove the biggest product contradictions

Changes:

1. Keep patient baseline odontogram as the live chart
2. Keep examination odontogram rows as encounter-specific chart state
3. Keep contract snapshot table as-is
4. Change contract creation so it can start from:
   - patient profile
   - examination
5. If a contract is started from examination, seed it from the examination odontogram, not the patient baseline
6. Add a patient-level "Odontogram History" list that shows:
   - examinations with odontogram data
   - contracts with snapshots

Pros:

- lowest implementation risk
- preserves most current code paths
- solves the biggest workflow mismatch

Cons:

- still no true baseline version history
- still no child / mixed dentition

### Option B - Recommended balanced option

Goal:

- make the clinical workflow coherent without turning the project into a full EHR rewrite

Changes:

1. Keep one patient baseline odontogram
2. Automatically create an examination odontogram snapshot when:
   - clinician first edits the chart in that exam, or
   - examination is completed
3. Add an `odontogram_snapshots` timeline table for patient-level chart snapshots
4. Support contract creation from either:
   - patient
   - examination
5. If contract is created from examination, freeze from that examination snapshot
6. Add dentition mode to patient:
   - permanent
   - deciduous
   - mixed
7. Expand tooth numbering model beyond `1..32`
8. Add patient-level chart history UI:
   - baseline
   - each exam snapshot
   - each contract snapshot

Pros:

- still conceptually simple
- solves the one-year / 12-visit scenario properly
- supports pediatric workflow
- aligns much better with modern dental software practice

Cons:

- requires real schema and UI work
- requires migration plan for current odontogram rows

### Option C - Fully integrated chart workspace

Goal:

- move toward a modern chart module like the stronger commercial systems

Changes:

1. Build one chart workspace in patient profile
2. Make baseline, treatment plans, progress history, images, and tooth history available from one place
3. Make examination charting and treatment planning operate inside that chart workspace
4. Use examinations, treatments, and contracts as contextual views of the same underlying chart history

Pros:

- best long-term clinical UX
- closest to mature chart-module products

Cons:

- largest redesign and refactor
- not the right next step if simplicity is the priority

## Recommended Path

The recommended path is Option B.

Reason:

- Option A is still too weak on history and pediatrics
- Option C is too large for the current need
- Option B is the cleanest middle ground

## Suggested Simplified Clinical Journey

This is the workflow I would recommend for this app:

1. Patient arrives
2. Clinician creates an examination
3. Examination captures:
   - complaint
   - clinical findings
   - diagnosis
   - recommendations
   - exam-specific odontogram snapshot
4. From that examination, clinic may optionally create:
   - treatment records
   - a treatment plan / contract
   - prescriptions
   - follow-up appointment
5. Treatment records document completed or ongoing procedures
6. Contract documents the agreed proposed plan and costs
7. Patient profile shows:
   - current baseline chart
   - timeline of exam snapshots
   - contract snapshots
   - per-tooth history

This keeps the concepts understandable:

- examination = assessment
- treatment = procedure
- contract = plan / consent
- odontogram = chart + history

## Concrete Decisions To Consider Next

1. Should contracts be creatable directly from patient profile?
   - recommended answer: yes

2. Should contract snapshot source be:
   - patient baseline if launched from patient
   - examination chart if launched from examination
   - recommended answer: yes

3. Should we support child and mixed dentition?
   - recommended answer: yes

4. Should every completed examination create a frozen odontogram snapshot?
   - recommended answer: yes

5. Should patient profile surface chart history explicitly?
   - recommended answer: yes

## Immediate Refactor Targets

If this program is reopened for implementation, the highest-value changes are:

1. Allow contract creation from patient profile, not only examination
2. Fix contract odontogram source selection
3. Add dentition mode to patient / chart model
4. Introduce a formal odontogram snapshot history table
5. Add patient-level odontogram history UI

## Bottom Line

The current app has the beginnings of a useful charting model, but it is still halfway between:

- a simple mutable patient chart
- an encounter chart
- and a medico-legal treatment-plan snapshot

To make it professionally coherent while keeping it simple, the app should move to:

- one baseline chart
- one snapshot per meaningful encounter
- one frozen contract snapshot
- child / mixed dentition support
- one patient-visible chart timeline

## Addendum - Direction After Product Review

This section reflects the refined direction after discussion:

- keep odontogram primarily patient-centered
- do not add examination-tied odontogram by default unless there is a strong professional reason
- keep the workflow simple
- improve patient profile visibility of current chart state
- consider surfacing the latest x-ray on patient profile

### Should examinations keep their own odontogram?

Short answer:

- not as a required day-to-day editing surface
- yes as an optional history/snapshot mechanism if the clinic wants medically reliable visit history

Professional reasoning:

- modern dental systems commonly distinguish between:
  - the current baseline chart
  - historical chart state
  - treatment plan charting
- that does not mean clinicians need to edit an odontogram inside every examination screen
- it does mean the system should preserve historical chart state at meaningful points in time

Recommended simplification:

1. The user edits the current odontogram from the patient profile as the main charting workspace.
2. The system can optionally create a frozen snapshot when an examination is completed.
3. The examination page does not need to expose a full editable odontogram by default.
4. The examination can simply link to:
   - current odontogram
   - chart snapshot captured at that visit, if one exists

This preserves medical history without making examination charting a second competing workflow.

### Recommended decision

Recommended product decision:

- keep one main editable odontogram per patient
- remove the expectation that clinicians must chart inside examination
- preserve history by snapshotting at important moments instead of forcing exam-level chart editing

Important nuance:

- if snapshots are not kept anywhere, the clinic will lose the ability to see how the chart looked one year ago
- so the system still needs historical snapshots even if users only edit from patient profile

### Proposed simplified odontogram model

Use these layers only:

1. Current patient odontogram
- the live editable chart
- edited from patient profile

2. Historical odontogram snapshots
- read-only
- created automatically when needed
- each snapshot tied to an origin such as:
  - examination completion
  - contract creation
  - manual "save snapshot" action

3. Contract odontogram snapshot
- already exists
- remains the medico-legal plan/consent snapshot

This removes the need for a separate exam-editable odontogram while still preserving history.

## Popup / Modal Editing Idea

The idea of opening a popup dedicated to the odontogram is directionally good, but a small modal is usually the wrong container for detailed chart editing.

Recommended approach:

- use a focused overlay or dedicated full-page workspace
- avoid a cramped modal for SVG chart editing

Best-practice reasoning:

- odontogram editing is not a tiny confirm/cancel action
- it is a dense task with:
  - tooth selection
  - legend
  - status details
  - history context
  - possibly adult/child chart switching

Recommended UX:

1. Patient profile shows a compact odontogram preview card.
2. Clicking it opens a dedicated chart workspace:
   - full page is best
   - large slide-over is second best
   - small modal is worst

Simple rule:

- if the user will spend more than a few seconds in the chart, do not use a small modal

## Patient Profile Opportunities

These are good suggestions and align with common clinical UX.

### 1) Mini odontogram preview on patient profile

Recommended: yes

Use:

- a compact read-only miniature chart
- show current state only
- place it near patient summary / medical snapshot
- include a clear "Open chart" action

Benefits:

- immediate visual orientation
- faster recognition than scanning text
- helps the clinician spot major restorative or missing-tooth patterns quickly

### 2) Latest x-ray on patient profile

Recommended: yes, if it is implemented carefully

This app already has patient attachment categorization that includes x-ray and imaging categories:

- [Patient.php](C:\xampp\htdocs\dental-saas\app\Controllers\Patient.php#L29)

That means surfacing a latest x-ray preview is feasible without inventing a new concept.

Recommended UX:

- show only the latest clinically relevant image preview
- label it clearly by category and date
- make it open the full imaging/attachments area
- do not try to turn the profile page into a PACS viewer

Suggested categories to prioritize:

- Panoramic X-Ray (OPG)
- Periapical X-Ray
- Bitewing X-Ray
- CBCT
- Intraoral Photo

### 3) Odontogram + latest x-ray side by side (or one above the other)

Recommended: yes on desktop if space allows

Good patient-profile pattern:

- one compact card for current odontogram preview
- one compact card for latest imaging preview
- both should be lightweight previews, not full viewers

This gives the doctor immediate structural context:

- current charted state
- most recent imaging reference

## Revised Recommended Path

After the product refinement, the recommended path is:

### Phase 1 - Clarify workflow

1. Make patient profile the canonical editable odontogram workspace.
2. Stop treating examination as a required odontogram-editing context.
3. Allow contract creation from:
   - patient profile
   - examination
4. If contract is created from patient profile, use current patient odontogram.
5. If contract is created from examination, use the latest relevant chart snapshot for that visit if one exists.

### Phase 2 - Preserve history simply

1. Introduce patient odontogram snapshots table.
2. Snapshot triggers:
   - examination completion
   - contract creation
   - optional manual save snapshot
3. Make snapshots read-only historical records.

### Phase 3 - Improve patient profile visibility

1. Add mini odontogram preview card.
2. Add latest x-ray / imaging preview card.
3. Add quick access to:
   - open chart
   - open imaging
   - open treatment plan

### Phase 4 - Pediatric support

1. Add dentition mode:
   - permanent
   - deciduous
   - mixed
2. Add appropriate numbering / rendering support.

## Full Next Steps

These are the next steps in recommended order.

### Step 1 - Decide the canonical clinical workflow

Recommended decision:

- examination = assessment
- treatment = procedure
- contract = treatment plan / consent
- patient profile = canonical chart workspace

This should be treated as the product rule before coding.

### Step 2 - Fix contract workflow

Recommended changes:

1. Remove the rule that contract must start only from examination.
2. Allow contract creation directly from patient profile.
3. Keep optional launch from examination.
4. Fix chart source logic:
   - patient launch -> current patient chart
   - exam launch -> latest visit snapshot or exam-context snapshot if available

This is likely the first implementation change with the highest product value.

### Step 3 - Add odontogram snapshots

Recommended model:

- `patient_odontogram_snapshots`

Suggested fields:

- `id`
- `clinic_id`
- `patient_id`
- `source_type`:
  - `manual`
  - `examination`
  - `contract`
  - `system`
- `source_id`
- `title` or `label`
- `snapshot_json`
- `snapshot_svg`
- `created_by`
- `created_at`

Purpose:

- preserve history without duplicating editing workflows

### Step 4 - Rework the patient profile clinical summary area

Recommended additions:

1. current odontogram mini preview
2. latest imaging preview
3. quick links:
   - open full chart
   - open latest x-ray
   - open attachments / imaging
   - create treatment plan

### Step 5 - Defer examination odontogram editing

Recommended product rule:

- do not invest further in exam-side editable odontogram until the snapshot model is settled
- examination should display chart context, not necessarily own the main editing workflow

### Step 6 - Define pediatric scope explicitly

Before implementation, decide:

1. Do we support only permanent dentition in v1 of the redesign?
2. Or do we add mixed / deciduous support now?

Recommended answer:

- if odontogram is being reopened structurally, add dentition mode now
- otherwise the redesign will remain adult-only and require another schema change later

## Recommended Immediate Action

The highest-value next action is:

1. approve the canonical clinical workflow
2. then implement contract-from-patient plus proper chart-source logic

Reason:

- it fixes a current workflow contradiction
- it does not require finishing the full odontogram history system first
- it keeps the project moving without getting ahead of the product decision
