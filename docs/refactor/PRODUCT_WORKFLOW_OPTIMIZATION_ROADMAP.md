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

The app currently has odontogram findings, treatments, contracts, appointments, and charge lines, but no first-class procedure lifecycle connecting all stages.

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
- Add one role-aware Today/routing workspace in Phase 1B.
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

### Phase 2 - Procedure Lifecycle Foundation

- Define first-class procedure record and status transitions.
- Link condition, tooth, examination, plan/contract item, appointment, treatment, charge line, and provider.
- Backfill only where mapping is unambiguous; preserve legacy records otherwise.

### Phase 3 - Fast Checkout

- Build a contextual checkout orchestrator.
- Require explicit review of completed work and charges.
- Reuse finance and appointment forms/contracts.

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

Implement **Phase 1B: Today routing workspace** using the Phase 1A contract.

The next slice should:

1. Show today’s appointments grouped by operational stage.
2. Expose only the next valid stage action for front desk, assistant, or provider permissions.
3. Preserve links to patient, appointment, examination, treatment, and finance records.
4. Surface exceptions such as late arrivals, no-shows, and visits waiting in checkout.
5. Avoid duplicating the patient chart, calendar, clinical forms, or finance calculations.

Do not begin the procedure-lifecycle schema until Phase 1B has been manually exercised in a real clinic-day workflow.
