# Stage 0 / remediation checkpoint 02 — corrected integration evidence

This supersedes the old PR #12 composition at `221b61af`. No integration or deployment is authorized. Production is preserved at `97cd12c67b3f426997d5f80f769031fa35a9f0c0`, tree `0f2b978af72a5ec31788b7294043d527b309a266`; scheduling OFF. No migrations, dependency changes, customer-data writes or GitHub Actions.

## Pinned source and review boundaries

- PR #9 `c1d2218370e45dbca498a9230467846e4539d2a6`: bounded COMMENTED result; formal exact-head decision requested using existing provenance/runtime evidence.
- PR #10 `f5a15c775c6a986062eb2002b02751fede17844b`: scoped independent APPROVED, unchanged.
- PR #11 `27b333ac40c1361d88bdac6415733dbed54a3737`: scoped independent APPROVED, unchanged; obsolete FX thread resolved.
- Core review branch `ceb58f88625aa64814501597b0a6c23e0d42b507`, tree `7192c4b575c266e5271f1aee5207b4fadb9c6e74`. Object-only sequential merge-tree of those exact component heads cleanly reproduces this tree. No ref/source merge performed.
- This PR remains stacked on that core; its final exact SHA/tree are recorded in the GitHub review handoff. Component approval does not approve the composed release.

## Three reviewer findings corrected

1. `InvoiceReminderFormatter` is the authoritative explicit-clinic currency/date/timezone formatter used by the real payment reminder producer and claim validator. Calendar DATE values do not shift across timezones; background due dates and default workflow suffix use the clinic's local day. Notification enqueue reads retain explicit clinic predicates without ambient session scoping.
2. Payment status never establishes issuance. Production has no positive unissued-draft evidence, so all persisted invoice financial basis, patient/clinical identity, document fields and printed notes are protected. API and browser edits use the same locked service; model callbacks reject direct bypasses, foreign unscoped edits and expense-to-invoice conversion, and preserve identical legacy totals. New invoice creation serializes clinic/patient/examination identity. Legitimate receipt, refund, receipt-void, write-off and expense paths remain distinct.
3. No persisted invoice may be physically deleted without proven unissued status; that proof does not currently exist. Queue/history is preserved, not erased to manufacture eligibility. Direct finance-model deletion fails closed. Clinic/patient/examination deletion guards protect invoice links and financial history, including the production clinic FK cascade. Parent guards lock clinic then identity, then take their first nonlocking history snapshot to avoid reversing settlement/audit lock order.

Safe metadata is an append-only `internal_note` in existing attributed activity audit, never an overwrite of printed invoice notes or financial values. Existing edit authorization applies; EN/AR edit forms display protected fields disabled and expose the separate note. Audit persistence is mandatory and atomic.

## Focused independent execution

Private non-webserved CLI application, PHP8.3.35 / MariaDB11.8.9, restricted identity-marked synthetic database. All17 changed runtime file hashes match the candidate. Public staging and production applications were not changed.

- 45 real SQL lifecycle/API assertions: pending/overdue/communicated/legacy protection, direct-model/foreign-context bypasses, printed notes, audit-only addenda, rollback, expense/FX compatibility and exact legacy totals.
- 40 real SQL reminder assertions, including actual `WhatsAppWorkflowService::queueInvoicePaymentReminder` and actual `NotificationService` enqueue for new clinic B while clinic A is active with conflicting settings; pending enqueue then successful claim. Only plan/registry policy gates are doubled; no external sends. Stale/forged/foreign demands reject.
- 26 real SQL parent-deletion assertions: clinic/patient/examination financial history, indirect billed charge, orphan finance audit, tenant/nested transaction rejection, callback/audit rollback and empty-parent compatibility.
- 34 actual API/browser-controller EN/AR assertions: audited API creation/server actor, computed totals, forged-paid/foreign-patient rejection, internal-note append and protected-value rejection. UI evidence is source/translation inspection; this is not routed authentication or browser transport certification.
- Two deterministic SQL races: blocked invoice deletion versus valid reminder claim; actual model invoice creation holding parent identity locks versus patient deletion waiting and then rejecting committed history.
- 34 standalone lifecycle policy assertions; changed PHP parses and whitespace checks pass. AGY cascade package also passed17 focused unit tests/45assertions. Completed framework/reporting/settlement evidence is reused; PR #10 reporting source and PR #11 settlement source remain unchanged.

All320 pre-existing synthetic rows and68 schemas remain unchanged;97 newly owned synthetic rows retained. Original tracked dirty files/head/main are untouched. Historical preservation baseline matches23 existing files; `_audit_bundle.txt` was already absent when this operation checked it and was neither restored nor removed. All238 corrupted staging metadata entries and affected table hashes remain unchanged.

## Compatibility, limitations and authorization gates

Hostinger MCP confirms PHP8.3.35 with required extensions; Git auto-deployment settings are all null. Production protected `.env`, root/public `.htaccess` hashes remain unchanged. No schema or migration impact. Source-only rollback would restore the prior runtime while retaining all data/configuration/storage; actual rollout still requires a fresh consistent off-host backup and verified recovery checkpoint.

A true draft/issue/cancel/credit-note/rebill lifecycle and historical invoice patient snapshot are absent. This release contains unsafe edits/deletes; it does not invent accounting semantics or claim `closeInvoiceBalance` is universal cancellation. Internal notes do not correct financial basis. Owner product rules are needed for that later workflow. Historical print regeneration still uses the current patient profile; ordinary patient profile edits are not blocked. Existing noninvoice financial edit behavior remains outside this bounded invoice-issuance correction. A payment after a valid committed reminder claim cannot recall an external message already being sent.

Release remains NO-GO until fresh exact-head independent PR #12 approval, formal PR #9 decision, final composed-tree review and separate Owner source/release authorization. No later roadmap stage starts before that decision. Scheduling pilot authorization remains separate.