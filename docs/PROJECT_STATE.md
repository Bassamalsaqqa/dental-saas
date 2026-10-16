# Project State

## 2026-10-16 — P0/Phase B/PR #3 merged; PR #4 retargeted to main, corrected HEAD awaiting fresh review & Owner merge authorization

P0 PR #1 merged at `8694607c4977b60be374a2b9edba0790247ab759`, including the Super Admin display correction at `e43af245e7ab61cb46515f7664b6289def903d23`. Independent code review found no blockers; the display correction and adjacent authority regressions passed 21 tests / 196 assertions. Nothing was deployed. Hostinger was last observed at `063317498e366a009357e0c0fe500041b2417050` through read-only inspection.

Active clinic membership is tenant role authority. Existing identities cannot be overwritten by tenant creation. Public Bearer API ignores web impersonation; first-party searches use session-authenticated `/api/search/*`. The four historical production role differences concern two disabled accounts across two clinics; read-only comparison found zero capability-set loss. No account or assignment changed. See [P0 review boundary](SaaS/P0_IDENTITY_RBAC_REVIEW_2026-10-15.md).

Phase B PR #2 was independently approved at `3bf46f96a653d266870d2aa6a783dcc96f493415` and merged at `89ccda3e65a4a091ca850db72ff7b0e4c1e56f04` with an identical tree. A separate pre-pilot PR adds full-horizon before/after reporting, attributed dispositions, verified audit bundles and current-state rechecks; final focused acceptance passed 50 tests / 524 assertions plus 198 EN/AR mobile Chromium assertions. Dynamic scheduling remains default off. Its [contract](SaaS/DOCTOR_SCHEDULING_CONTRACT.md) preserves existing appointments and requires an additive schema before explicit feature enablement. No production migration, backfill, dependency upgrade or deployment is authorized. Overall release readiness remains unapproved.


Last updated: 2026-10-16

PR #3 (exact approved HEAD `ad481ebbf454ebf3a53bc09417d13a2b2f57c0d5`, tree `9a7256c0591a80c8288eb41d93b60cedbdc82858`) was merged into main at `8be7ad67a4a2810daea55f23c307b206e09cfdf4` at 2026-10-16T08:47:38Z with an identical tree. PR #4 now directly targets main. Pre-correction HEAD was `0986fefea865e5e0acb858ee57eabd2e69c7a7d2` (old tree `4a178e2189dee77ee42a23400acfc13a41dd84e6`). Latest GitHub review on PR #4 was CHANGES_REQUESTED (review5550429984), superseding prior approvals. Documentation correction changes HEAD/tree; fresh independent exact-HEAD approval is REQUIRED on the resulting corrected HEAD. The corrected exact HEAD (H4) and tree (T4) are recorded in the PR discussion, keeping self-referential commit IDs out of source. PR #4 remains unmerged, auto-merge is unset, and separate Owner merge authorization is required. Owner-authorized healthy staging replacement is completed (GUID `40582f85-97ec-400f-8a65-c6632adb7e8c`, datadir `C:/xampp/htdocs/dental-saas/.ai/staging-replacement-20261016/attempt-02/mysql-data`, 70 tables, 76 migrations; latest run passed 40 PHP tests / 642 assertions, 198 mobile assertions, 2 Bearer tests / 12 assertions; all 70 schema/data hashes identical before/after; prior corrupted instance preserved historically without repair). Production readiness gates remain OPEN (blocked); local healthy synthetic evidence does not certify Hostinger. Hostinger runtime/schema drift (Web PHP 8.3.35, CLI 8.2.34, MariaDB 11.8.9, 67 tables / 77 migrations vs 70 / 76, missing migration `2026-09-26-000001` with intact OFF fallback guards, finance enum, finance tracking/lifecycle columns) and backup/security/finance/clinical/pilot blockers remain. Dynamic scheduling remains default-off; no deployment, migration, scheduling activation, pilot, or customer writes authorized.

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

- Isolated Test Baseline Reconciliation & Tooling Safety (Corrections 01, 02 & 03): Established reproducible, fail-closed, non-destructive test provisioning via `scripts/provision_test_db.php`, minimal pre-migration foundation `tests/_support/Database/LegacyBaseSchema.php` (18 tables, zero data, `CREATE TABLE IF NOT EXISTS`), and organic canonical migration execution via CodeIgniter's `MigrationRunner::latest()` (75 migrations applied and recorded naturally). Eliminated full snapshot imports and manufactured migration history entries. Hardened `scripts/TestDatabasePreflight.php` to reject non-empty `failover`, `DSN`, and `subdriver` configuration keys without connecting (preventing BaseConnection failover bypass), enforce loopback host locality via `USER()`, and validate `$marker['target_db'] === self::APPROVED_DISPOSABLE_TEST_DB`. Corrected `provision_test_db.php` `--check-only` to be strictly read-only via `INFORMATION_SCHEMA.SCHEMATA` without creating absent databases. Reordered `safe_test_runner.php --provision` to provision before connecting, ensuring sound first-run bootstrap. Extracted shared production orchestration methods in `TestDatabasePreflight.php` (`checkDatabaseExistsOnServer`, `orchestrateCheckOnly`, `orchestrateRunnerPreTest`) integrated into production CLI scripts and verified via unit tests with mock/spy collaborators (including demonstrated negative failure paths on reversal/mutation). Resolved PHP `exit()` in `try` bypassing `finally` by implementing `VerificationFailureException` in `scripts/verify_isolation_and_canary.php`, ensuring stack unwinding, safe guarded cleanup restoration, and exit code 1 on failure. Verified via `--test-injected-failure-after-marker-drop` that marker is restored and target database left usable; early preflight refusals (`--test-injected-failure`) remain mutation-free. Verified complete canary row preservation across all columns on normal repeat provisioning. Seeded deterministic synthetic test fixtures (clinic, user, patient, plan, subscription, encrypted WhatsApp notification channel, platform secrets, and synced permissions). Full test suite executed in a single invocation (`safe_test_runner.php tests/unit tests/database`): 313 tests, 1898 assertions, 0 errors, 0 failures, 0 skips. Containment suite (`TenantBackupContainmentTest`: 13 tests, 91 assertions) and preflight suite (`SafeTestIsolationPreflightTest`: 26 tests, 74 assertions) verified passing cleanly. Business database `democa_dental_saas` observed counts recorded separately (67 tables, 7 patients, 9 appointments).

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

PR #3 is merged into main at `8be7ad67a4a2810daea55f23c307b206e09cfdf4`. PR #4 now targets main; pre-correction HEAD `0986fefea865e5e0acb858ee57eabd2e69c7a7d2` had prior approval superseded by GitHub CHANGES_REQUESTED (review5550429984). Address review with docs-only corrections; fresh independent exact-HEAD approval is REQUIRED on the resulting corrected HEAD (H4). PR #4 remains unmerged, auto-merge is unset, and separate Owner merge authorization is required. Owner-authorized healthy staging replacement is completed (70 tables, 76 migrations, GUID `40582f85-97ec-400f-8a65-c6632adb7e8c`, prior corrupted instance preserved historically with NO repair requirement). Production readiness gates remain OPEN (blocked); Hostinger parity not complete (Web PHP 8.3.35, CLI 8.2.34, MariaDB 11.8.9, 67 tables / 77 migrations vs 70 / 76; missing migration `2026-09-26-000001` while OFF fallback guards exist; schema differences in finance enum and finance tracking/lifecycle columns; uncertified backup restore). Production remains unchanged and dynamic scheduling remains default-off. No deployment, migration, activation, or customer writes.

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

- Isolated test database `democa_dental_saas_test` is provisioned with 67 tables and deterministic synthetic fixtures via `scripts/provision_test_db.php`.
- Full PHPUnit unit suite passes cleanly with 0 errors and 0 failures (285 tests, 1695 assertions; 16 tests skipped only when external WhatsApp/channel credentials are unconfigured).
- Database example suite (`ExampleDatabaseTest`) passes cleanly (2 tests, 3 assertions).
- Pre-existing SaaS guardrail failures (6 DOM sinks, 12 raw queries, 4 font strings) remain unchanged baseline gates for later stages.

## References

- Documentation map: `START_HERE.md`
- Product workflow: `refactor/PRODUCT_WORKFLOW_OPTIMIZATION_ROADMAP.md`
- Clinical: `CLINICAL_CURRENT_STATE.md`
- Finance: `FINANCE_CURRENT_STATE.md`
- WhatsApp: `WHATSAPP_CURRENT_STATE.md`
- Recent changes: `CHANGELOG.md`
