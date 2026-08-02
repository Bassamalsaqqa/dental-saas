# Changelog

## 2026-08-02 - Active documentation consolidation

- Reduced the active documentation surface to current contracts, current state, recent continuation history, and operational instructions.
- Archived completed migration studies, duplicate Gemini strategy material, completed calendar/finance plans, old changelog entries, and the SaaS task-by-task closure ledger without deleting evidence.
- Replaced repeated research and prompt libraries with concise React, App API, clinical-finance, and product workflow contracts; full originals remain in the dated archive.
- Simplified README, project/module state, operations, and documentation navigation so each fact has one active owner.
- Binding API, tenant, entitlement, and decision contracts retain their necessary detail.

## 2026-08-01 - Appointment checkout completion contract

- Completed Product Workflow Roadmap Phase 3C at the existing appointment transition boundary; no competing checkout route or write workflow was added.
- Clean consultation-only visits can complete normally when no clinical or financial follow-up is present.
- Completion now reports unresolved clinical work, uninvoiced charges, mixed-currency charges, outstanding balances, and contextual legacy links before the visit can close.
- Users with the existing `appointments:edit` permission may intentionally complete with open items only after an explicit acknowledgement and a 10-500 character reason.
- Completion exceptions are written to the appointment activity trail with the issue codes and operator reason.
- Direct `in_chair -> completed` and normal `checkout -> completed` transitions use the same server-owned assessment, preventing the compatibility path from bypassing checkout review.
- Existing Examination, Treatment, Finance, payment, and appointment-stage services remain authoritative. Nothing is auto-completed, auto-invoiced, or auto-paid.
- Added `AppointmentCheckoutCompletionServiceTest`: 6 tests and 11 assertions covering clean completion, issue classification, mixed currencies, acknowledgement, and reason validation.
- Deferred manual QA: verify clean and exception completion in English/Arabic, AJAX and normal POST behavior, view-only permissions, activity evidence, and all five checkout states on local/deployed authenticated sessions.

## 2026-08-01 - Explicit appointment-to-clinical linkage

- Completed Product Workflow Roadmap Phase 3B for newly created appointment-context clinical work.
- Added a nullable, indexed `examinations.appointment_id` so an Examination started from Appointment detail retains durable visit identity after redirects and later handoffs.
- Shared Examination and Treatment full-page/modal forms now carry the same appointment context; no duplicate create surface was added.
- Added a fail-closed appointment context service that accepts only an appointment in the active clinic belonging to the selected patient.
- New Treatments started in appointment context propagate the validated appointment to both the canonical clinical procedure and optional clinical charge line.
- Appointment checkout now prefers explicit examination/procedure links while retaining clearly labeled patient/date matching for legacy records.
- Existing Treatments and examinations were not backfilled because their historical appointment intent is ambiguous.
- Added `ClinicalAppointmentContextServiceTest` and expanded checkout/procedure integration coverage. Focused verification passes with 17 tests and 124 assertions.
- Deployment requires `php spark migrate`; the canonical Hostinger SQL dump was regenerated.
- Deferred manual QA: create an Examination from Appointment detail, continue into Treatment with and without charge capture, return to the Appointment, and confirm checkout shows explicit linkage in English and Arabic.
- No automatic billing, clinical completion, appointment transition, or legacy inference was introduced.

## 2026-07-30 - Appointment checkout review workspace

- Implemented Product Workflow Roadmap Phase 3A as a backend-owned, read-only checkout review on Appointment detail.
- Checkout now presents three simple decisions before the patient leaves: clinical work review, captured-charge review, and payment state.
- Added `AppointmentCheckoutService` to aggregate the active-clinic appointment, same-visit examinations and Treatments, explicitly linked procedures, charge lines, invoices, payments, provider obligations, and inventory usage without calculating finance truth in the view.
- Added charge-line-scoped Money Trail aggregation and an explicit open-charge amount so invoice actions reuse canonical finance contracts.
- Existing canonical actions remain authoritative: Examination/Treatment own clinical records, the existing invoice drawer owns invoice creation, Patient Finance owns payment handling, and the appointment transition service owns visit completion.
- Mixed-currency open charges are detected and cannot be submitted as one invoice from checkout.
- Legacy clinical records matched only by patient and visit date are clearly identified for user review; no ambiguous legacy relationship was silently promoted to an explicit appointment link.
- Added `AppointmentCheckoutServiceTest`: 3 tests and 17 assertions covering tenant concealment, same-visit aggregation, open-charge totals, checkout states, and mixed-currency detection.
- No route, schema, automatic billing, automatic clinical completion, payment form, or frontend-owned finance calculation was added.
- Deferred manual QA: exercise Appointment detail checkout in English/Arabic with no clinical work, open charges, mixed currencies, invoiced balances, settled invoices, and view-only permissions.
- Followed by Phase 3B explicit appointment linkage and Phase 3C completion safeguards.

## 2026-07-30 - Treatment procedure lifecycle integration

- Completed Product Workflow Roadmap Phase 2B for newly created Treatments.
- Added an explicit create-time choice between planning work for later and starting it now; no status is inferred from financial data, odontogram color, or legacy Treatment `active` rows.
- Every new Treatment now creates exactly one tenant- and patient-validated clinical procedure in the same database transaction as optional inventory usage and optional clinical charge capture.
- `Plan for later` creates a proposed procedure. `Start now` records proposed, accepted, and in-progress milestones in append-only history.
- Optional clinical charge lines link to the same procedure while billing status remains independent from clinical status.
- Treatment edits synchronize safe descriptive fields and reject moving a linked procedure to another patient or clinic.
- Marking a linked Treatment complete advances its procedure through valid lifecycle milestones and commits both updates atomically. Legacy Treatments without a procedure remain compatible.
- Linked Treatments can no longer use the legacy hard-delete action because that would orphan clinical history; a future reasoned correction/cancellation contract remains required.
- Added a read-only procedure status to Treatment detail and English/Arabic create/status copy.
- Added `TreatmentProcedureIntegrationServiceTest`: 4 tests and 17 assertions covering planned, start-now, delayed charge linkage, patient integrity, completion history, and legacy compatibility.
- Focused lifecycle, finance bridge, odontogram, visit-stage, and App API suites pass. The full suite still has three unrelated pre-existing harness errors: two example database tests report a migration-sequence gap and `HealthTest` reads an uninitialized test `baseURL`.
- Tailwind build and route inspection pass; the Bash SaaS guardrail script cannot run on this workstation because WSL has no installed distribution.
- Deployment still requires the Phase 2A migration: `php spark migrate`.
- Deferred manual QA: create planned and start-now Treatments from full-page and quick-create surfaces, test optional charge/inventory capture, complete a linked Treatment, and verify English/Arabic status presentation.

## 2026-07-30 - Clinical procedure lifecycle foundation

- Recorded production Phase 1 clinic-day QA as deferred manual testing on `denta.palsync.net`.
- Implemented Product Workflow Roadmap Phase 2A with tenant-scoped `clinical_procedures` and append-only `clinical_procedure_status_history`.
- Added a canonical procedure lifecycle: proposed, presented, accepted/rejected, scheduled, in progress, completed, referred, and cancelled.
- Added transactional, concurrency-checked transitions with actor/time attribution and mandatory reasons for rejection, referral, or cancellation.
- Added a fail-closed creation service that validates patient, examination, odontogram condition, treatment, appointment, contract/item, charge-line, and provider relationships against the active clinic.
- Added unique source-link safeguards for Treatment, Treatment Contract item, and clinical charge line mappings.
- Deliberately did not backfill ambiguous legacy records or add UI/routes, billing, appointment, contract, Treatment, or odontogram side effects.
- Added `ClinicalProcedureLifecycleServiceTest`: 7 tests covering the normal lifecycle, invalid skips, terminal behavior, initial history, transition history, tenant isolation, patient-link integrity, and exception reasons.
- Deployment requires `php spark migrate`.
- Followed by Phase 2B integration for newly created Treatments without interpreting legacy `active` records.

## 2026-07-30 - Today patient-routing workspace

- Completed Product Workflow Roadmap Phase 1B on the existing tenant dashboard rather than creating a competing page.
- Added a tenant-scoped Today read model grouping appointments into Expected, Waiting, Chairside, Checkout, and Needs attention.
- Added one primary server-validated handoff per active stage; appointment-view permission controls visibility and appointment-edit permission controls transitions.
- Added late-arrival signals only for scheduled/confirmed visits, completed/open counts, compact exception review, patient/appointment context links, and English/Arabic copy.
- Kept terminal exceptions, clinical work, finance work, and scheduling details in their canonical modules.
- Changed the existing Upcoming panel and count to future appointments after today, removing duplicate Today/upcoming presentation.
- Added `TodayRoutingServiceTest`: 3 tests covering grouping, handoffs, sorting, late behavior, completed counts, exceptions, and unknown legacy states.
- No route, database table, clinical inference, financial calculation, auto-billing behavior, or public/App API contract was added.
- Full clinic-day English/Arabic and view-only/edit permission QA remains deferred.

## 2026-07-30 - Appointment visit-stage workflow foundation

- Added the active cross-module roadmap at `docs/refactor/PRODUCT_WORKFLOW_OPTIMIZATION_ROADMAP.md`, covering Visit, Care Plan, Checkout, Recall, Supply, Communication, and Governance loops.
- Implemented Phase 1A as a server-owned appointment visit-stage contract:
  `scheduled -> confirmed -> arrived -> ready -> in_chair -> checkout -> completed`.
- Added controlled cancellation/no-show branches and terminal-state protection; invalid stage skips return a structured 422 response.
- Kept appointment edit focused on schedule details so it cannot bypass the transition contract.
- Preserved existing confirm, complete, and cancel routes as wrappers around the unified transition service.
- Added tenant- and permission-filtered singular/plural transition routes, activity logging, and governed no-show follow-up dispatch.
- Updated appointment detail, list, calendar, print, filters, and English/Arabic labels for the expanded stages.
- Applied migration `2026-07-30-120000_ExpandAppointmentVisitStages` locally and regenerated the canonical Hostinger SQL dump.
- Added `AppointmentVisitStageServiceTest`: 5 tests and 21 assertions covering normal, consultation-only, walk-in-compatible, cancellation/no-show, invalid-skip, and terminal-stage behavior.
- Deferred the role-aware Today routing workspace to Phase 1B; this slice does not duplicate clinical records, perform finance calculations, auto-bill, or auto-complete visits.

## 2026-07-26 - Odontogram workflow relationships

- Kept the odontogram as the fast visual clinical index while preserving canonical ownership in examinations, treatments, contracts, appointments, attachments, and finance.
- Examination print and examination-linked treatment contracts now use the effective visit chart (baseline plus visit overrides).
- Added context-preserving odontogram actions for treatment creation and treatment-plan creation.
- Treatment creation can prefill a validated tooth number from chart context.
- Clarified legacy odontogram treatment note/status fields as tooth follow-up metadata, not procedures or billable events.
- Expanded `docs/refactor/PATIENT_CLINICAL_WORKFLOW_PLAN.md` with the durable relationship contract and fast doctor workflow.

## 2026-07-26 - Patient clinical workflow and visit-chart integrity

- Added the active patient clinical workflow contract covering preparation, examination, visit charting, treatment, completion, checkout, exceptions, and deferred capabilities.
- Fixed examination create/edit save-and-chart redirects and examination list/detail chart actions so they retain `examination_id`.
- Visit odontograms now show patient baseline conditions with examination tooth overrides layered on top, without copying or mutating baseline rows.
- Added clear patient-chart versus visit-chart context labels in English and Arabic.
- Added Open visit chart as a primary examination action.
- Added a database-backed regression test proving visit overrides win while untouched baseline rows remain intact.

## 2026-07-26 - Finance workflow simplification

- Simplified the daily finance workspace around three clinic jobs: Record payment, Create invoice, and New expense.
- Kept payables, provider payouts, treasury transfers, and reports available as secondary specialist finance areas.
- Added progressive disclosure to the shared income and expense quick-create forms without changing field names, routes, validation, calculations, permissions, or POST behavior.
- Exchange-rate and converted-amount fields now appear only for non-base currency; paid and due dates follow transaction state; linked records and narrative fields live under one optional-details control.
- Simplified patient finance actions to Record payment and Create invoice while retaining one ledger and a collapsed money-trail drill-down.
- Reduced repeated guidance on payables, payouts, transfers, clinical charge selection, provider share, and treatment billing handoffs.
- Preserved backend ownership of balances, taxes, discounts, allocations, provider compensation, treasury custody, inventory COGS, tenant scope, and audit history.
- Deferred consolidation of legacy full-page finance forms into shared partials to a dedicated compatibility-tested cleanup slice.

## 2026-07-26 - Finance Workflow Simplification Phase 1

- Added the finance and clinical workflow simplification plan, now archived after implementation at `docs/_archive/2026-08-02-docs-slimdown/FINANCE_WORKFLOW_SIMPLIFICATION_PLAN.md`.
- Replaced the generic Finance `Add Income` entry point with explicit `Record Payment` and `Create Invoice` actions that reuse the existing canonical modal and preselect the correct transaction type.
- Kept `New Expense` as the clinic spending action and moved supplier bills, provider payments, and money moves into a secondary finance-area navigation strip.
- Reduced repeated summary-card explanations and decorative status clutter while preserving all values, routes, permissions, forms, backend calculations, and audit behavior.
- Finance quick-create modals now start fresh on normal action clicks while preserving submitted values when reopened after server validation errors.

## Older History

Entries before 2026-07-26 are archived in docs/_archive/2026-08-02-docs-slimdown/CHANGELOG_PRE_2026-07-26.md.
