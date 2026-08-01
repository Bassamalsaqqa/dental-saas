# Product Workflow Optimization Roadmap

## Goal

Make DentaCare fast for a normal clinic team by closing the daily operating loops between records that already exist. The product should not gain another broad module until the current patient, schedule, clinical, finance, inventory, communication, and governance records behave as one coherent operating system.

The design test for every phase is:

> Can a doctor or front-desk user see what must happen next, perform it in context, and know that the authoritative record was updated?

## Evidence Base

This roadmap was checked against the current controllers, models, routes, active documentation, and established dental workflows documented by:

- Dentrix Ascend: routing panel, procedure completion, patient checkout, signed clinical notes, and note task queues.
- Open Dental: treatment-planned procedures, planned appointments, recall and unscheduled lists, supply ordering, and graphical chart statuses.
- ADA: clinical records as legal records, provider attribution, relevant documentation, consent/refusal, and separate financial records.

Primary references:

- https://hsps.pro/DentrixAscend/Help/Routing_panel_overview.htm
- https://hsps.pro/DentrixAscend/Help/Viewing_or_editing_patient_information_from_the_routing_panel.htm
- https://hsps.pro/DentrixAscend/Help/Completing_treatment.htm
- https://hsps.pro/DentrixAscend/Help/Checking_out_patients.htm
- https://hsps.pro/DentrixAscend/Help/Signing_clinical_notes.htm
- https://www.opendental.com/manual/apptplanned.html
- https://www.opendental.com/manual/recalllist.html
- https://www.opendental.com/manual/unscheduled.html
- https://www.opendental.com/manual/supplyorders.html
- https://www.ada.org/resources/practice/practice-management/documentation-patient-records

## Current Product Assessment

### Strong Foundations

- Tenant isolation, plan gates, RBAC, activity logging, private attachments, and separate public/App API contracts.
- Patient-centered workspace with contextual creation while canonical modules own writes.
- Calendar, appointment status, examinations, layered odontograms, treatments, prescriptions, signed treatment contracts, and follow-up scheduling.
- Clinical charge capture, invoice/payment traceability, provider payouts, supplier payables, treasury custody, and inventory COGS.
- Supplier mappings, low-stock detection, reorder list, stock movements, and treatment-linked inventory usage.
- Template-first notifications with WhatsApp governance, attempts, delivery events, opt-outs, quotas, and retry controls.

### Main Product Gap

The app has most component records, but several workflows are connected by links rather than by a durable operating state. Users can create records, yet the system does not always answer:

- What stage is this patient visit in?
- Which accepted work is ready to schedule?
- Which scheduled work was actually completed?
- Is the clinical note complete and signed?
- Is checkout finished?
- Who needs recall or rescheduling?
- Did a failed message create a staff follow-up task?
- Did a reorder become an order, receipt, stock movement, and payable?

## Seven Closed Loops

### 1. Visit Loop

Target:

`Scheduled -> Confirmed -> Arrived -> Ready -> In chair -> Checkout -> Completed`

Current appointments use a smaller status set and are not a complete patient-routing workflow.

The visit workspace should expose only stage-relevant actions:

- Arriving: confirm identity, alerts, contact details, forms.
- Ready: provider/chair, appointment reason, planned work.
- In chair: examination, odontogram, clinical note, procedure completion.
- Checkout: completed work, invoice, payment, next appointment, instructions.
- Complete: immutable visit summary and unresolved follow-up.

Do not create a second patient chart in the routing workspace. It should orchestrate canonical records.

### 2. Care Plan Loop

Target:

`Condition -> Proposed procedure -> Presented plan -> Accepted/rejected -> Ready to schedule -> Scheduled -> Completed/referred`

The app now has a Phase 2A first-class procedure lifecycle foundation. Phase 2B still needs to connect new Treatment, plan, schedule, completion, and charge workflows explicitly.

Recommended durable entity:

- clinic, patient, examination, tooth/area/surfaces
- procedure/catalog code and description
- provider
- status: proposed, presented, accepted, rejected, scheduled, completed, referred, cancelled
- contract item, appointment, treatment, and clinical charge references
- status timestamps and actor attribution

This must be a dedicated schema and migration slice. Do not overload odontogram rows, contracts, or invoices to simulate it.

### 3. Checkout Loop

Target:

`Completed clinical work -> Review charges -> Invoice -> Payment/arrangement -> Follow-up appointment -> Visit closed`

Create one simple checkout drawer/workspace from appointment, examination, or patient context:

- Work completed today
- Work intentionally left unbilled
- Amount due and payment recorded today
- Next appointment or explicit "not scheduled"
- Patient instructions/documents
- Unresolved follow-up

Finance remains authoritative. Checkout orchestrates existing charge, invoice, payment, and appointment contracts without calculating money independently.

### 4. Recall and Unscheduled Care Loop

Target:

`Due -> Contacted -> Scheduled` or `Cancelled/no-show -> Follow-up -> Rescheduled/closed`

Add two operational queues:

- Recall/recare: due date, recall type, assigned provider, contact attempts, next action.
- Unscheduled care: cancelled/no-show appointments and accepted treatment not yet scheduled.

Do not mix recall, no-show recovery, and marketing campaigns. Each needs explicit reason, status, owner, and audit history.

### 5. Supply Loop

Target:

`Low stock -> Reorder request -> Supplier order -> Partial/full receipt -> Stock movement -> Supplier payable`

Current inventory reaches the reorder list but does not have a complete purchase-order and receiving lifecycle.

Add only after the daily clinical loops:

- Supplier order header and lines
- Draft, placed, partially received, received, cancelled
- Per-line ordered/received quantities and unit cost
- Receipt-created stock movements
- Explicit supplier payable creation

Inventory owns quantities and receiving. Finance owns the payable and settlement.

### 6. Communication Loop

Target:

`Workflow event -> Approved template -> Dispatch -> Delivery/read/failure -> Staff action when unresolved`

WhatsApp transport and governance are strong. The missing product layer is a tenant-facing work queue:

- Failed/rejected messages needing action
- Opt-out or invalid-contact cases
- Appointment reminders not delivered
- Recall/no-show follow-up attempts
- Owner, due date, resolution, and link to patient/appointment

Do not make transport logs the normal clinic user interface.

### 7. Governance Loop

Target:

`Work performed -> Documentation complete -> Signed/locked -> Addendum only -> Audit/review`

Highest clinical safety gap:

- Signed clinical notes
- Provider attribution
- Optional additional-provider signature
- Lock after signature
- Append-only addenda
- Unsigned/missing-note task queue

Also resolve existing guardrail debt before expanding high-risk workflows:

- Legacy DOM-string sinks
- Raw tenant queries in controllers
- Standalone font declarations
- Development/test controllers or routes that are not required in deployed environments

## Priority Order

### Phase 0 - Reliability Baseline

- Close guardrail failures or formally review and minimize allowlists.
- Audit production exposure of debug/test controllers and routes.
- Refresh active current-state dates and remove stale statements already superseded by implementation.
- Add focused workflow tests before schema expansion.

### Phase 1 - Visit Status and Chairside Workspace

- **Phase 1A completed 2026-07-30:** expanded appointment visit stages safely and made transitions server-owned.
- **Phase 1B completed 2026-07-30:** added one permission-aware Today routing workspace to the existing dashboard.
- Preserve patient, appointment, examination, and return context.
- No new clinical or finance calculations.

#### Phase 1A Visit-Stage Contract

Operational path:

`Scheduled -> Confirmed -> Arrived -> Ready -> In chair -> Checkout -> Completed`

Supported exceptions:

- Scheduled can become confirmed, arrived, cancelled, or no-show.
- Confirmed can become arrived, cancelled, or no-show.
- Arrived can become ready, in-chair, or cancelled.
- Ready can become in-chair or cancelled.
- In-chair can become checkout or completed. Direct completion supports a consultation-only visit where no checkout work remains.
- Checkout can become completed.
- Completed, cancelled, and no-show are terminal. Reopening requires a future explicit correction workflow; it is not an implicit edit.

Implementation ownership:

- `AppointmentVisitStageService` owns the transition matrix.
- `Appointment::transition()` owns tenant-scoped writes, permission-filtered routing, no-show workflow dispatch, and activity logging.
- Appointment edit owns scheduling details only and cannot bypass stage transitions.
- Appointment detail presents only valid next actions.
- Existing confirm, complete, and cancel endpoints remain compatibility wrappers around the same transition contract.
- Calendar, list, print, filters, and localization recognize all stages.

Role/action boundary for this phase:

| Role capability | Allowed behavior |
| --- | --- |
| `appointments:view` | See the current visit stage and clinical/financial context |
| `appointments:edit` | Execute only a transition allowed by the server-owned matrix |
| Clinical permissions | Create/open examinations and treatments through their canonical modules |
| Finance permissions | Review/create billing records through canonical Finance contracts |

Phase 1A does not infer clinical completion, calculate checkout balances, sign notes, or auto-bill work.

Acceptance scenarios covered by the transition contract:

- Normal visit: scheduled through checkout to completed.
- Consultation-only visit: in-chair directly to completed.
- Emergency/walk-in: scheduled directly to arrived, then through chairside stages.
- Cancellation/no-show: allowed only before chairside work has started.
- Incomplete checkout: remains explicitly in checkout rather than being silently completed.

#### Phase 1B Today Routing Contract

The existing tenant dashboard is the Today workspace. No second dashboard or routing route was created.

| Group | Appointment stages | Normal job |
| --- | --- | --- |
| Expected | scheduled, confirmed | Bring the patient into the clinic flow |
| Waiting | arrived, ready | Prepare and hand off to the provider |
| Chairside | in_chair | Continue canonical clinical work |
| Checkout | checkout | Finish through canonical finance and scheduling records |
| Needs attention | cancelled, no_show | Review the exception from appointment context |

Primary handoff mapping:

- scheduled/confirmed -> arrived
- arrived -> ready
- ready -> in-chair
- in-chair -> checkout
- checkout -> completed

Safety and simplicity rules:

- The board reads only appointments from the active clinic and current clinic date.
- Appointment detail remains the canonical visit context; patient, clinical, and finance modules keep ownership of their records.
- Users with `appointments:view` can see the board and open its records.
- Only users with `appointments:edit` see the primary transition action.
- Terminal exceptions do not expose a fake recovery action; they open the appointment for review.
- Late flags apply only to expected patients whose scheduled time has passed.
- Completed visits are counted, not repeated as a long card list.
- Upcoming appointments now mean future appointments after today, preventing duplicate Today rows.
- The board does not calculate balances, infer clinical completion, create examinations, auto-bill, or replace the calendar.

Manual clinic-day scenarios:

1. Scheduled or confirmed patient moves through waiting, chairside, checkout, and completion.
2. A late scheduled/confirmed patient shows elapsed minutes; an arrived patient is no longer flagged late.
3. Cancellation/no-show appears under Needs attention with no invalid next-stage button.
4. A view-only user can inspect records but cannot transition visits.
5. Arabic uses the same logical column/card behavior and transition meaning.

### Phase 2 - Procedure Lifecycle Foundation

- **Phase 2A completed 2026-07-30:** defined the first-class procedure record, append-only status history, and server-owned transition contract.
- The schema can link condition, tooth/surfaces, examination, plan/contract item, appointment, treatment, charge line, and provider without taking ownership from those modules.
- Procedure creation validates supplied links against the active clinic and selected patient.
- No legacy rows were backfilled because current Treatment `active` status is not enough to distinguish proposed, accepted, scheduled, or in-progress work.

#### Phase 2A Procedure Contract

Normal lifecycle:

`proposed -> presented -> accepted -> scheduled -> in_progress -> completed`

Supported shortcuts and exceptions:

- A proposal may be accepted directly when presentation is not separately recorded.
- Accepted work may start immediately without a separate appointment.
- Proposed or presented work may be rejected or referred.
- Accepted, scheduled, or in-progress work may be referred or cancelled with a required reason.
- Completed, rejected, referred, and cancelled records are terminal; reopening requires a future explicit correction contract.

Ownership and safety:

- Odontogram rows remain findings/current tooth state.
- Treatment remains the canonical delivery record until a later integration slice deliberately maps it.
- Treatment Contracts remain presentation/consent artifacts.
- Appointments remain schedule/visit operations.
- Clinical charge lines remain billable production records.
- Procedure transitions do not mutate any of those records and never create an invoice or payment.
- Current state stores milestone timestamps and actor attribution; history stores every initial/transition event.
- Source links are tenant and patient validated before creation.

Deferred production evidence:

- Phase 1 clinic-day QA on `denta.palsync.net` is still pending manual testing.
- Phase 2A is backend foundation only and has no production UI surface to test yet.

#### Phase 2B Treatment Integration - Completed 2026-07-30

Connect newly created Treatment work to the procedure foundation through one explicit mapping path:

1. New Treatment creation exposes `Plan for later` and `Start now`; legacy `active` is never interpreted.
2. Treatment, optional inventory usage, optional charge, procedure, and lifecycle history commit atomically.
3. Each new Treatment owns one procedure link; optional charge capture links financially without changing clinical status.
4. Treatment completion advances the linked procedure only through the lifecycle service.
5. Treatment detail shows read-only lifecycle status before any broad procedure edit surface is introduced.
6. Existing Treatments remain unbackfilled and continue to complete under the legacy-compatible path.
7. Linked Treatment hard-delete is blocked to prevent orphaning append-only clinical history.

Deferred Phase 2B evidence:

- Production/local authenticated UI QA is still pending for full-page and modal Treatment creation in English and Arabic.
- Verify planned/start-now intent, optional charge and inventory capture, completion, status display, return context, and delete protection.
- Patient-profile lifecycle badges were deliberately deferred because the legacy patient view requires a separate encoding-safe cleanup.

### Phase 3 - Fast Checkout

#### Phase 3A Checkout Review - Completed 2026-07-30

- Appointment detail is the contextual checkout workspace; no competing checkout route or module was created.
- A tenant-scoped service reviews three facts: clinical work state, captured-charge state, and payment state.
- Canonical Examination/Treatment, Finance, Patient Finance, and appointment-stage contracts remain the only owners of writes.
- Open charge lines can hand off to the existing invoice drawer when they use one currency.
- Mixed currencies are flagged and must be handled separately; the view does not sum or submit them together.
- Legacy examinations and Treatments can only be associated by patient and visit date and are visibly marked as contextual matches requiring review.
- Checkout never auto-completes clinical work, creates an invoice, records a payment, or advances the appointment.

Deferred Phase 3A evidence:

- Authenticated English/Arabic and permission QA on the deployed app.
- Scenarios: no clinical work, unfinished work, ready-to-invoice charge, mixed currencies, invoiced balance, settled invoice, and view-only access.

#### Phase 3B Explicit Visit Linkage - Completed 2026-08-01

- New Examinations started from Appointment detail persist a nullable, indexed `appointment_id` after active-clinic and patient validation.
- Shared full-page/modal Treatment creation propagates that appointment to the canonical procedure and optional charge line.
- Canonical module endpoints and context-aware return behavior remain unchanged.
- Appointment checkout prefers explicit examination/procedure links and keeps patient/date matching only as a visibly contextual legacy fallback.
- No ambiguous legacy records were backfilled.

Deferred Phase 3B evidence:

- Authenticated English/Arabic workflow QA from Appointment -> Examination -> Treatment -> optional charge -> Appointment checkout.
- Confirm modal and full-page paths preserve context and cross-patient/cross-clinic IDs fail closed.

#### Phase 3C Checkout Completion Contract - Completed 2026-08-01

- The existing appointment transition endpoint owns completion assessment for both `checkout -> completed` and direct consultation-style `in_chair -> completed` transitions.
- Clean visits complete normally. Unresolved clinical work, uninvoiced charges, mixed currencies, balance due, or contextual legacy links require explicit acknowledgement.
- The override uses the existing `appointments:edit` permission and requires a 10-500 character operator reason.
- Appointment activity records stable issue codes and the reason whenever completion intentionally leaves items open.
- No route, schema, permission system, clinical mutation, invoice creation, payment enforcement, or frontend financial calculation was added.

Deferred Phase 3C evidence:

- Authenticated English/Arabic and view-only/edit-permission QA.
- Verify clean completion, blocked completion, valid acknowledged completion, activity evidence, normal POST redirects, and AJAX 422 responses.

### Phase 4 - Signed Clinical Notes

- Note templates, provider signatures, locks, addenda, and missing-note queue.
- Treat signed notes as legal records with audit-grade mutation rules.

### Phase 5 - Recall and Unscheduled Queues

- Recare rules, due dates, no-show/cancellation recovery, contact attempts, and scheduling handoff.
- Integrate with WhatsApp only through existing governed dispatch contracts.

### Phase 6 - Supply Ordering and Receiving

- Convert reorder lines to supplier orders.
- Record partial receipt and stock movement.
- Create payables explicitly and traceably.

### Phase 7 - Management and Reporting

- Provider production versus collections from procedure/charge/payment allocation truth.
- Unscheduled accepted treatment, recall effectiveness, chair utilization, checkout completion, unsigned notes, and stock-order performance.
- Keep doctor-facing summaries short; specialist details remain in canonical modules.

## Defer Until Explicitly Required

- Insurance claim/EOB/ERA engine
- Automated diagnosis or treatment recommendations
- Voice charting
- Perio charting
- Mixed dentition and orthodontic treatment details
- Patient portal and online booking
- Full accounting general ledger
- Cross-clinic shared patient identity

These may be valuable, but they should not interrupt the seven operating loops.

## Product Simplicity Rules

- One owner for each fact.
- One primary action per stage.
- Show exceptions and next actions before analytics.
- Use drawers for contextual medium forms; use full pages for complex review and reconciliation.
- Never auto-complete clinical work, auto-sign notes, or auto-bill from visual chart state.
- Never calculate finance, permissions, quotas, or tenant authority in views.
- Every handoff carries entity context and returns the user to the originating workflow.
- Every irreversible clinical or financial transition records actor, time, and reason.

## Next Recommended Slice

Phase 1B production QA, Phase 2B authenticated UI QA, and Phase 3A-3C checkout UI QA remain deferred. Phase 2A, the Treatment-focused Phase 2B integration, and the Phase 3 checkout contracts are independently tested.

The next implementation slice is **Phase 4: signed clinical-note contract planning**. Define provider attribution, signatures, immutable locks, corrections, addenda, and missing-note queues before changing runtime behavior.
