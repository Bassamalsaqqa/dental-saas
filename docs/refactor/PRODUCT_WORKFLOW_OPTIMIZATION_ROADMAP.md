# Product Workflow Optimization Roadmap

Last updated: 2026-08-02

## Goal

Make DentaCare a fast clinic operating system: preserve patient/visit context, show one clear next action, keep exceptions visible, and leave business truth in canonical backend modules.

## Operating Loops

1. **Visit:** schedule -> arrive -> chair -> checkout -> complete.
2. **Care plan:** finding -> procedure proposal -> acceptance -> performance -> completion.
3. **Checkout:** review work -> invoice selected charges -> payment/defer -> follow-up.
4. **Recall:** due/no-show/cancelled -> contact -> reschedule.
5. **Supply:** low stock -> reorder -> receive -> movement/payable.
6. **Communication:** governed template -> queue -> dispatch -> delivery/opt-out evidence.
7. **Governance:** permissions, tenant boundaries, audit, signatures, and exceptions.

## Completed Foundations

### Phase 1 - Visit Flow

- Server-owned appointment stages and transitions.
- Dashboard operational queues for expected, waiting, chairside, checkout, and attention.
- Context-preserving canonical quick actions.

### Phase 2 - Procedure Lifecycle

- First-class tenant-scoped procedure records and append-only status history.
- New Treatment plan/start intent mapped to procedure lifecycle.
- Atomic optional inventory and charge capture.
- No ambiguous legacy backfill.

### Phase 3 - Checkout

- Appointment detail reviews clinical, charge, invoice/payment, and link state.
- New appointment-context clinical work retains explicit appointment linkage.
- Clean visits complete normally.
- Unresolved work/billing/balance/context links require an acknowledged reason logged in appointment activity.
- No automatic clinical completion, invoice, payment, or legacy inference.

## Next Phases

### Phase 4 - Signed Clinical Notes

Implementation contract:

- A completed Examination owns one immutable signed clinical-note snapshot.
- Signing is an explicit provider attestation with actor, timestamp, snapshot, and SHA-256 evidence.
- Signed Examinations cannot be edited or deleted; corrections are append-only, reasoned, actor-attributed addenda.
- Addenda are hash-chained to the signature and preceding addendum.
- Existing Examination permissions govern signing and addenda; tenant scope, CSRF, and activity logging remain authoritative.
- Signing does not sign treatments, odontogram findings, prescriptions, contracts, attachments, or financial records.
- Dashboard and Examination registry now expose completed unsigned notes and older unfinished drafts through one tenant-scoped read model. Bulk signing and inferred content deficiencies remain forbidden.

### Phase 5 - Recall and Unscheduled Work

- Recare rules and due dates.
- Accepted-unscheduled treatment queue.
- No-show/cancellation recovery and contact attempts.
- Scheduling handoff; WhatsApp only through governed dispatch contracts.

### Phase 6 - Ordering and Receiving

- Convert reorder lines to supplier orders.
- Partial receiving and inventory movement.
- Explicit supplier payable linkage.

### Phase 7 - Management

- Provider production versus collections from allocations.
- Chair utilization, checkout completion, recall recovery, unsigned notes, and stock-order performance.
- Doctor-facing summaries remain short; specialist evidence remains in modules.

### Phase 8 - Patient Access and Front Office

- Digital intake and medical-history updates.
- Patient portal for appointments, plans, documents, statements, and payments.
- Online booking, self-check-in, waitlist, and governed two-way communication.

### Phase 9 - Dental Depth and Interoperability

- Perio charting, mixed dentition, referral closure, and tooth/procedure-linked imaging.
- Evaluate licensed CDT/SNODENT terminology and country-specific insurance/claims requirements.
- Define FHIR/CDA dental exchange and safe data export before adding integrations.

### Phase 10 - Release Assurance

- MFA, login/API throttling, backup restoration drills, incident response, monitoring, and external security assessment.
- Repair the full test harness and add browser coverage for English/Arabic, permissions, clinic-day workflows, and accessibility.
- Run observed role-based usability pilots and measure task completion, errors, and training time before claiming ease of use.

## Product Rules

- One owner for each fact and one primary action per stage.
- Prefer drawers for contextual medium forms; full pages for complex review/reconciliation.
- Every handoff retains source context and a safe return path.
- Never auto-sign, auto-complete clinical work, auto-bill, force payment, or calculate finance/permissions in views.
- Irreversible clinical/financial transitions record actor, time, and reason.

## Deferred QA

- Production clinic-day workflow in English/Arabic and view-only/edit roles.
- Treatment intent with optional inventory/charge capture and completion.
- Appointment -> Examination -> Treatment -> invoice/payment -> checkout trace.
- Clean, blocked, and acknowledged checkout completion, including AJAX and activity evidence.

The full research and phase evidence remains available in Git history.
