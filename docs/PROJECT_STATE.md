# Project State

Last updated: 2026-09-24

## Status

- Stage: pre-alpha; production release readiness is not approved.
- Backend: CodeIgniter 4 + MySQL tenant/control-plane application remains live.
- React: separate Lovable repository remains mock-first; `/app-api/v1/*` wiring is not started beyond foundation and Patients contracts.
- Active product track: Denta+ Modernization Program (Security, Financial Integrity, Storage, Performance & Product Architecture) governed by `docs/refactor/DENTA_PLUS_HARDENING_PERFORMANCE_PROGRAM.md`.

## Implemented Workflow Milestones

- Checkpoint 01 — Baseline Evidence, Program Plan & Tenant Database Backup Containment (P0): Accepted for local checkpoint commit. Captured reproducible baseline across toolchains, migrations, tests, guardrails, and asset sizes in `.ai/antigravity-runs/20260924-denta-plus-program-01*/`. Established single active program plan `docs/refactor/DENTA_PLUS_HARDENING_PERFORMANCE_PROGRAM.md` linked in `START_HERE`. Completely contained tenant-plane database backup exposure: `Settings::createBackup()`, `Settings::downloadBackup($filename)`, and `Settings::restore()` hard-deny with 404 (`PageNotFoundException`); `Settings::getBackups()` returns empty array without filesystem scanning; `SettingsService::generateDatabaseBackup()` unconditionally throws `RuntimeException`. Removed manual backup forms, download buttons, and restore triggers from settings UI; added bilingual explanatory status (English/Arabic) in `settings/partials/backup.php`; verified file/DB non-mutation, absence of shared SQL disclosure, and legitimate file streaming / ordinary settings updates intact via tests (`TenantBackupContainmentTest`: 13 tests, 91 assertions passing). Established repeatable test isolation via `scripts/safe_test_runner.php`, `phpunit.xml.dist` tests DB config, and suite-level fail-closed safeguard. Unresolved validation debt documented: 6 broader-suite issues on isolated disposable database (schema/seed reconciliation required before framework upgrade), failing SaaS guardrails gate (exit code 1), and deferred browser QA.
- Control Plane onboarding works without tenant session context, uses its own authorized email-check route, atomically persists clinic/admin/subscription/defaults/designation and required audits, and rejects nested transactions and ineligible administrator accounts. Local behavioral creation/rollback, route authorization, English/Arabic errors, and rendered JavaScript parsing are verified; no production clinic is created for smoke testing.
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
- Control Plane clinic administrator lifecycle: state detection (healthy, disabled user, inactive membership, missing authority, unassigned), explicit designation in settings, replacement with optional demotion (preserving inactive status without silent reactivation), restoration decoupled from global account activation with explicit operator intent required on designation, membership-bounded selection, concurrency protection, fail-closed audit logging, localized service outcomes in English/Arabic with stable machine codes, bounded bulk query resolution with deterministic ID tie-breaking, canonical registry/detail administrator parity, accessible modal focus containment with guarded timers and trigger restoration, in-context user profile admin management actions, non-destructive reconciliation migrations, and resilient invoice panel handling against schema drift.
- Subscription timezone contract, tenant dashboard entry & transaction hardening: canonical UTC persistence (`ClinicSubscriptionModel::nowUtc`) across all writers (`OnboardingService`, `ControlPlane\Clinics`, `ControlPlane\PlanRequests`) and readers (`SubscriptionFilter`, `PlanGuard`, `AppApiSubscription`, `Home`, `Foundation`). Centralized standing evaluation in `ClinicSubscriptionModel::evaluateStanding()` strictly fails closed on malformed/relative dates, future starts, or expired periods independent of mutable process timezone (`date_default_timezone_get()`). Core lifecycle mutations extracted into `ControlPlaneSubscriptionService` with an owned checked-transaction contract (`transDepth !== 0` rejection, `transBegin()`, `transStatus() === false || !$db->transCommit()`, controlled rollback, connection-bound audit logging). Pre-validation ensures zero-mutation before destructive state change in `startSubscription` and `PlanRequests::approve`; operations serialize via InnoDB row locking (`SELECT ... FOR UPDATE`), enforce checked writes, compute extensions strictly from locked state, require mandatory concurrency tokens (`expected_end_at`, `expected_updated_at`, `expected_start_at`, `expected_requested_plan_id`, `expected_requested_duration`) with explicit `__NULL__` sentinel for SQL null values, and re-validate plan active status under lock. Localized, sanitized operator messages in English and Arabic with zero raw exception leaking (`UI.subscriptionCode_*`). Bounded grammar parsing (+14:00 to -12:00 bounds), safe display fallback on invalid timezone names, DST gap and ambiguous fall-back fold rejection (`isAmbiguousDstFold`), generalized `AuthFilter` via framework `getRoutePath()`, UTC-anchored monthly calendar arithmetic, and real route login/select/impersonation verification (24 tests, 504 assertions; all 8 suites passing with 145 tests, 1101 assertions). Governed by `docs/SaaS/DATETIME_CONTRACT.md`; unexecuted, row-locked, audited historical repair CLI design/procedure documented for Clinic 26 / subscription 35 without mutating database rows.

## Deferred Manual QA

- Control Plane clinic administrator lifecycle: browser modal verification (focus trapping on Tab/Shift+Tab, assign, replace, restore, reset password, make administrator) in English and Arabic RTL, and Escape/backdrop dismissal on clinics and users detail surfaces.
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

Checkpoint 01 (baseline, plan & backup containment) is accepted for local checkpoint commit. The immediate next validation task before the framework upgrade is to reconcile disposable test schema and reference seeds, establish a verified isolated baseline, and classify the remaining 6 test issues. Following that, proceed to Checkpoint 02: Remaining P0 security & CI upgrade (compatible CI 4.7.x release verification, removal of web/debug artifacts, private document root protection, RBAC guardrails, public API session trust removal).

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
