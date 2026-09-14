# Changelog

## 2026-09-14 - Control Plane Clinic Administrator Lifecycle & Hardening

- Resolved 500 error on `GET /controlplane/clinics/{id}`:
  - Fixed `IonAuthModel::user()` null query builder state when called with empty argument or uninitialized session.
  - Hardened `PlanGuard::isFeatureEnabled()` to strictly fail closed (`return false;`) on exceptions and corrupt plan JSON without leaking internals.
- Implemented authoritative `ClinicAdminLifecycleService`:
  - Detects 5 canonical administrator states: `healthy`, `disabled_user`, `inactive_membership`, `missing_authority`, and `unassigned`.
  - Stored primary clinic administrator designation in `settings` table (`setting_key = 'designated_admin_user_id'`) without requiring schema modifications.
  - Safe fallback logic: resolves oldest active user holding `practice_manager` or `clinic_admin` role when explicit designation is unset; non-manager memberships are never inferred as administrators (clinic state remains unassigned).
  - Global account activation decoupled from clinic restore: restoring clinic authority requires the account to already be active, returning `account_disabled` otherwise. Global activation must be done explicitly in User Management.
  - Clinic membership boundary enforced: `getEligibleUsersForClinic`, `assignAdministrator`, and `replaceAdministrator` strictly operate on active members of the target clinic, rejecting non-members with `cross_clinic_forbidden`.
  - Concurrency protection: `replaceAdministrator` supports `expectedCurrentAdminUserId` to detect concurrent modifications and reject stale requests with `stale_state`.
  - Role resolution: resolves roles strictly by slug (`practice_manager`, `clinic_admin`, `read_only`, `receptionist`) without magic numeric IDs.
  - Enforced strict airlock: Super Admins (IonAuth group 1) cannot be assigned as clinic administrators.
  - Enforced active user requirement: deactivated users cannot be assigned as clinic administrators.
  - Session revocation: deactivated user sessions are immediately invalidated by `AuthFilter` upon account disablement.
  - Fail-closed audit logging: all mutations pass explicit `$actorUserId`, record target, previous admin, and clinic IDs, and roll back transaction if audit cannot be persisted.
  - Internal error masking: catch blocks log detailed exceptions internally and return sanitized generic error strings to the operator.
- Control Plane Clinic Administrator Lifecycle & Hardening (Second Audit Corrections):
  - Explicit designation authoritative (Finding 1): `getClinicAdministrator()` treats `designated_admin_user_id` as authoritative. Missing or inactive clinic membership reports degraded state (`inactive_membership`) on the designated user without falling back to another manager. Legacy oldest-manager fallback runs only when no explicit designation exists at all.
  - Revoked/inactive membership protection (Finding 2): `getEligibleUsersForClinic()` filters strictly on `clinic_users.status = 'active'`. `assignAdministrator()` and `replaceAdministrator()` reject inactive candidates with `inactive_membership_forbidden` and do not reactivate inactive memberships.
  - Assign separated from replace (Finding 3): `assignAdministrator()` is strictly for unassigned clinics; returns `already_assigned` if an administrator is already active or designated. Concurrency is serialized via InnoDB row-locking (`SELECT id FROM clinics WHERE id = ? FOR UPDATE`) on the clinic record.
  - Restore restricted to designated administrator (Finding 4): `restoreAdministrator()` requires the target user to match the explicit designated administrator (rejects mismatches with `target_mismatch`), requires designated admin setting to exist (rejects with `no_designated_admin`), and verifies `expectedDesignatedAdminUserId` against stale modifications (`stale_state`).
  - Replacement choice semantics (Finding 5): Replaced lone checkbox with explicit radio choices for `previous_admin_action` (`demote_to_staff` vs `keep_role`). Controller enforces a strict allowlist (`demote_to_staff`, `keep_role`, `deactivate_membership`).
  - Test & report accuracy (Finding 6): Eliminated hardcoded actor user ID assumptions by introducing a deterministic `getOrCreateSuperAdmin()` fixture helper across test suites. Added extensive zero-mutation assertions across all rejected execution paths.
- Control Plane Clinic Administrator Lifecycle & Hardening (Final Audit Corrections):
  - Fail-closed write verification: every `ClinicUserModel` and `SettingsModel` `insert()`/`update()` return value in `assignAdministrator`, `replaceAdministrator`, `restoreAdministrator`, and `setDesignatedAdminUserId` is checked; `false` throws `\RuntimeException` to roll back the surrounding transaction before any audit or success response is emitted.
  - Per-membership row locking: target and previous membership rows are locked and re-read inside the transaction (`SELECT * FROM clinic_users WHERE clinic_id = ? AND user_id = ? FOR UPDATE`) and revalidated for active status before mutation, preventing silent races from concurrent Control Plane Users workflows.
  - Fixture ID false-positive repair: `getOrCreateSuperAdmin()` in both `ClinicAdminLifecycleComprehensiveTest` and `ControlPlaneClinicDetailTest` now uses checked insert + `insertID()` instead of `(int) $db->table('roles')->insert(...)`. `testPlanGuardFailClosedBehavior()` plan ID fixture corrected the same way.
  - Documentation: consolidated two overlapping 2026-09-14 entries in `docs/DECISIONS.md` into a single comprehensive durable decision; service error responses accurately described as sanitized generic strings (not localized).
  - Verification: 44 tests, 191 assertions pass cleanly across all three lifecycle suites. Manual browser QA and production verification remain deferred.

## 2026-08-02 - Accepted treatment scheduling recovery

- Added one tenant-scoped queue for accepted Treatment procedures that do not yet have a scheduled appointment, shown compactly on Dashboard and Treatments.
- Treatment detail now provides an explicit `Accept treatment` action for proposed/presented work and reuses the canonical appointment popup for scheduling.
- Successful appointment creation atomically links `scheduled_appointment_id` and advances the procedure from `accepted` to `scheduled`; failed linkage rolls back the appointment.
- Original `appointment_id` encounter context is preserved and is not overwritten by the future scheduled visit.
- Patient, provider/chair, date/time, billing, and clinical completion remain explicit; the queue performs no inference or automatic scheduling.
- Verification passed: PHP syntax, routes, Tailwind build, queue test `1/4`, procedure integration `6/24`, lifecycle `7/29`, App API foundation `19/66`, and Patients `25/111`.
- Full PHPUnit reached `151` tests / `702` assertions with only the three documented unrelated harness errors. Local WSL guardrails remain unavailable with `E_ACCESSDENIED`; direct DOM-sink and secret scans returned no hits.
- Deployment requires `php spark migrate`; the canonical local SQL dump was regenerated.
- Deferred manual QA: accept a planned Treatment, verify both queues, schedule it from the shared popup in English/Arabic, confirm return context, and confirm the queue item disappears.

## 2026-08-02 - Clinical documentation attention queue

- Added one tenant-scoped clinical documentation read model shared by Dashboard and Examination registry views.
- Completed unsigned Examinations appear as `Ready to sign`; unfinished Examinations dated before today appear as `Overdue drafts`.
- The queue does not infer deficient care from optional diagnosis, treatment, or narrative fields and does not permit bulk signing.
- Examination registry filters use the same server-owned classification and expose signed notes separately.
- Added 3 tests and 9 assertions for classification, signed-record exclusion, registry consistency, and tenant isolation.
- Deferred manual QA: verify Dashboard counts, all three registry filters, Arabic labels/RTL, and record links against real clinic data.

## 2026-08-02 - Signed Examination clinical-note foundation

- Added one-time provider attestation for completed Examination notes with an immutable canonical JSON snapshot and SHA-256 fingerprint.
- Signed Examinations cannot be edited, completed again, or deleted through normal controller/model paths; corrections are append-only addenda with actor, reason, timestamp, and a tamper-evident hash chain.
- Examination detail and print views now expose signature state and addendum history in English and Arabic.
- Tenant scope, existing `examinations:edit` permission, CSRF, canonical Examination routes, and activity logging remain authoritative; no new permission system or parallel clinical-note module was introduced.
- Added focused governance coverage: 5 tests and 18 assertions, plus passing adjacent clinical, appointment, finance-bridge, and App API suites.
- Deployment requires `php spark migrate`; the canonical Hostinger SQL dump was regenerated.
- Deferred manual QA: sign a completed Examination in English and Arabic, confirm edit/delete/complete are blocked, append a correction, and verify the printed record shows signer and addenda.

## 2026-08-02 - Active documentation consolidation

- Reduced the active documentation surface to current contracts, current state, recent continuation history, and operational instructions.
- Removed completed migration studies, duplicate Gemini strategy material, completed calendar/finance plans, old changelog entries, and the SaaS task-by-task closure ledger from the working tree; Git history preserves them.
- Replaced repeated research and prompt libraries with concise React, App API, clinical-finance, and product workflow contracts.
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

Older implementation history is available through Git. Current state is summarized in PROJECT_STATE.md and module state documents.
