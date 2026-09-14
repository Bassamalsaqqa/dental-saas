# Project State

Last updated: 2026-09-14

## Status

- Stage: pre-alpha; production release readiness is not approved.
- Backend: CodeIgniter 4 + MySQL tenant/control-plane application remains live.
- React: separate Lovable repository remains mock-first; `/app-api/v1/*` wiring is not started beyond foundation and Patients contracts.
- Active product track: cross-module clinic workflow optimization.

## Implemented Workflow Milestones

- Visit stages are server-owned from scheduled through checkout/completed.
- Dashboard groups today’s appointments into operational queues.
- Clinical procedures have a first-class audited lifecycle for newly created Treatments; ambiguous legacy rows are not backfilled.
- Appointment-context Examinations and new Treatments preserve explicit appointment linkage.
- Appointment checkout reviews clinical work, captured charges, invoice/payment state, and mixed currencies using canonical services.
- Checkout completion permits clean visits and requires an audited reason for unresolved clinical, billing, balance, or legacy-link concerns.
- Patient/entity contextual drawers reuse canonical forms and endpoints; modules still own validation and writes.
- Clinical-to-finance traceability uses charge lines and Money Trail views without frontend finance calculations.
- Completed Examination notes can be signed once, become immutable, and receive attributed append-only addenda; print output carries the signature evidence.
- Dashboard and Examination registry expose tenant-scoped ready-to-sign and overdue-draft documentation queues without bulk signing or clinical-content inference.
- Accepted Treatment procedures without a scheduled visit appear in tenant-scoped Dashboard/Treatments queues; explicit appointment creation links the future visit separately and advances the lifecycle atomically.
- Control Plane clinic administrator lifecycle: state detection (healthy, disabled user, inactive membership, missing authority, unassigned), explicit designation in settings, replacement with optional demotion, restoration decoupled from global account activation, membership-bounded selection, concurrency protection, fail-closed audit logging, and resilient invoice panel handling against schema drift.

## Deferred Manual QA

- Control Plane clinic administrator lifecycle: browser modal verification (assign, replace, restore, reset password) in English and Arabic RTL, and Escape/backdrop dismissal.
- Full clinic-day workflow on local and `denta.palsync.net` in English and Arabic.
- View-only versus edit permissions across appointment checkout.
- Treatment full-page/modal creation with lifecycle intent, optional inventory, and optional charge capture.
- Appointment -> Examination -> Treatment -> charge -> checkout explicit-link workflow.
- Clean, blocked, and acknowledged checkout completion, including AJAX 422 and activity evidence.
- Full clinical-finance chain: treatment -> invoice -> payment -> patient/treatment/finance trace -> provider payout.
- Signed-note workflow in English and Arabic: sign, blocked edit/delete/re-complete, addendum, and print evidence.
- Documentation queue counts and registry filters in English/Arabic against real clinic data.
- Accepted-treatment recovery in English/Arabic: accept planned work, schedule from Dashboard/Treatments, verify return context and queue removal.

## Current Next Slice

Signed clinical-note governance, documentation attention, and accepted-but-unscheduled treatment recovery are implemented and code-verified. The next bounded product slice is recall/no-show recovery; broader clinical-document signing remains deferred.

## App API / React State

- `/api/v1/*`: public/stateless Bearer + `X-Clinic-Id`; unchanged.
- `/app-api/v1/*`: same-origin session/CSRF JSON API.
- Implemented App API: CSRF, login/logout, bootstrap, clinic switch, Patients list/detail/create/update.
- Not implemented: patient workspace/timeline/attachments and clinical, finance, odontogram, or WhatsApp React slices.
- Lovable must not own auth, tenant isolation, permissions, finance math, clinical truth, file access, or messaging dispatch.

## Non-Negotiables

- Fail closed; server controls clinic scope.
- Tenant/control-plane airlock remains enforced.
- APIs return JSON errors, never HTML redirects.
- No public tenant files.
- No DOM string sinks outside explicit guardrail allowances.
- No secrets or database dumps tracked in Git.
- Canonical modules own business rules; views display backend-owned results.

## Known Verification Baseline

- Full PHPUnit currently has three unrelated baseline errors: two sample migration-sequence errors near `2026-03-25-160000` and `HealthTest` accessing an uninitialized `Config\App::$baseURL`.
- On this Windows environment, the Bash guardrail may fail to start through WSL with `E_ACCESSDENIED`; run it in CI or a working Bash environment.

## References

- Documentation map: `START_HERE.md`
- Product workflow: `refactor/PRODUCT_WORKFLOW_OPTIMIZATION_ROADMAP.md`
- Clinical: `CLINICAL_CURRENT_STATE.md`
- Finance: `FINANCE_CURRENT_STATE.md`
- WhatsApp: `WHATSAPP_CURRENT_STATE.md`
- Recent changes: `CHANGELOG.md`
