# Changelog

## 2026-10-20 - M0 current release reconciliation (unintegrated engineering candidate)

- Reconciled live/GitHub release `8499042` and tree `bcfa202`; corrected obsolete pre-alpha, main-deployment and blanket-migration guidance.
- Recorded Stage0/settings fixes as deployed; scheduling remains OFF with its pending migration excluded.
- Preserved read-only production metadata and prior recovery evidence outside source. Existing guardrail drift, missing recent logs and unavailable cron/MCP inspection remain explicit limitations.
- M1 work is isolated and has no production, migration or source-integration authorization.

## 2026-10-15 - Pre-pilot schedule impact review workflow

- Merge independently approved PR #2 at `89ccda3e65a4a091ca850db72ff7b0e4c1e56f04`, matching approved HEAD `3bf46f96a653d266870d2aa6a783dcc96f493415` exactly. No deployment or activation.
- AGY implemented a separate schedule-impact workflow; independent review corrected proposal parsing, coherent locked snapshots, strict acknowledgment, compact verified audit storage, retained-visit freshness and responsive EN/AR confirmation.
- Require reasoned dispositions for affected future visits while preserving appointments and clinical/finance links. Canonical appointment editing resolves reschedules; account/role security changes remain available and require fresh pilot review. Completed/past/clinical-locked visits cannot be excluded from slot advice.
- Focused isolated acceptance: **50 tests / 524 assertions**; actual Chromium EN/AR preview/recheck at 320/360/390px: **198 assertions**, including 505-visit JSON submission. No migration/DDL, customer data, Actions, production request or deployment. Existing guardrail findings unchanged; staging/operator rehearsal and release blockers remain. See [contract](SaaS/DOCTOR_SCHEDULING_CONTRACT.md).


## 2026-10-15 - PR #2 independent-review corrections

- Address the blocking duplicate-doctor-name feedback at `d623d203`: consistent visible clinician references in booking choices and dayboard columns/actions, retaining stable submitted IDs and historical name snapshots.
- Escape patient option names/phone text and bind advisory exclusions to an authorized, clinic-owned edit page with a short-lived session proof. Raw exclusions never hide another booking from advice.
- Require a future-appointment schedule-change impact report and explicit acknowledgment/resolution workflow as a production pilot gate; existing appointments remain unchanged. See [scheduling contract](SaaS/DOCTOR_SCHEDULING_CONTRACT.md). No migration, customer-data change, deployment, merge or Actions. Focused acceptance passed **36 tests / 582 assertions**; actual EN/AR mobile Chromium/Select2 checks passed **564 assertions**, with **96** retained slot/URL assertions. Independent read-only code review found no blockers; GitHub re-review remains required.


## 2026-10-15 ? Phase B doctor scheduling review

- P0 PR #1 merged at `8694607c4977b60be374a2b9edba0790247ab759`, including the Super Admin display fix (`e43af245e7ab61cb46515f7664b6289def903d23`; 21 focused tests / 196 assertions). No deployment.
- Prepare a separate, default-off doctor scheduling expansion: stable provider IDs, active clinic doctor columns, weekly/break/leave availability, serialized booking and settings writes, preserved historical appointment snapshots and conservative unresolved-visit blocking.
- Keep existing visits, clinical handoff and session/Bearer authority boundaries intact. See [scheduling contract](SaaS/DOCTOR_SCHEDULING_CONTRACT.md) for safe migration and release gates. Focused acceptance passed **56 tests / 500 assertions**; final EN/AR edit/fallback verification passed **13 tests / 89 assertions**. JavaScript passed **116 behavioral assertions**; 38 PHP and 10 rendered-inline-script syntax checks passed. Independent review found no remaining blocker for a separate review PR. No Phase B merge/deployment.



## 2026-10-15 ? P0 final display correction

- Control-plane user directory derives its Super Admin label from active, unexpired canonical platform RBAC rather than authentication-group membership. This changes presentation only.
- Focused label/authority/routed regression verification: **21 tests / 196 assertions passed**. Valid platform authority without admin-group membership is labeled correctly; historical group-only, expired/inactive assignment and disabled-account cases are not labeled Super Admin. No schema or production change.

## 2026-10-15 — PR #1 independent-review corrections (not deployed)

- Restore strict positive-ID parsing of routed control-plane membership POSTs, localize validation/success feedback, and revalidate role clinic scope under the membership transaction lock.
- Require canonical active account/clinic/membership/role eligibility in tenant-only web and App API gates. Web impersonation remains explicit and session-owned; App API resource gates reject it, including platform users who also have an ordinary membership. Bearer headers do not select web session authority.
- Independently reproduce and remove an AGY draft App API impersonation regression. Targeted routed corrections pass **42 tests / 514 assertions**. The exact committed final focused-suite result is recorded in the PR follow-up; no Actions or broad suite is run.
- Restore original LF convention in PR files with byte-equivalent normalization evidence, escape P0 flash text, and document each of the four production role differences using SELECT-only evidence. Two disabled accounts remain disabled; no automatic role/data repair, schema change or deployment. See the [active review](SaaS/P0_IDENTITY_RBAC_REVIEW_2026-10-15.md).

## 2026-10-15 — P0 tenant identity and RBAC corrections (not deployed)

- Make validated active `clinic_users.role_id` the tenant role authority; retain historical global tenant grants and unscoped overrides without allowing tenant escalation. Require active, unexpired global platform RBAC, removing authentication-group fallback and runtime auto-promotion.
- Add checked clinic-serialized membership creation/role/status/removal transactions with actor/context validation, rollback on audit/commit failure, last-manager/designated-admin protection and isolated connection support. Refuse tenant creation against an existing identity; platform-only linking preserves identity fields.
- Deny tenant global identity/password edits through user-management and legacy aliases. Retain Profile/recovery and verified self-password changes; display clinic access separately from global account status, in EN/AR.
- Separate strict public Bearer authority from validated first-party session search. Reuse the original nine search queries, select user IDs explicitly, enforce JSON failures and preserve hosted subdirectories and drawer initialization.
- Normalize permission declarations to framework-supported comma arguments; CI4.6 truncates a third colon segment. Deny malformed parser inputs and global-mode AJAX crossings.
- Final focused verification: **204 tests / 1,676 assertions**, **36 JS URL/transport assertions**, and **37 changed/new PHP lint checks** passed; diff whitespace check passed. Existing guardrails still report the same 22 baseline matches.
- Add behavioral/route/transaction-failure, bilingual render and URL mapping regressions. No migration/backfill or compiled asset change. See [review evidence and rollback](SaaS/P0_IDENTITY_RBAC_REVIEW_2026-10-15.md). Existing framework/dependency, guardrail and schema-parity debt remains deferred.


## 2026-09-25 - Isolated Test Baseline: Harness Stack Unwinding & Shared Orchestration (Run ID: 20260925-denta-plus-isolated-baseline-correction-03)

- **High Harness Safety: Guaranteed Stack Unwinding & Finally Cleanup:**
  - Resolved `exit(1)` inside `try` block in `scripts/verify_isolation_and_canary.php` that prevented PHP `finally` handlers from executing after dropping the ownership marker table.
  - Implemented `VerificationFailureException` propagation: `failVerification()` now throws `VerificationFailureException`, allowing the PHP call stack to unwind normally into the outer `try ... catch (\Throwable $primaryError) ... finally` block.
  - Guarded `finally` cleanup: safely restores ownership marker and baseline schema only when `$markerWasRemovedByHarness === true`, captures and logs cleanup diagnostics, and preserves original failure diagnostics if cleanup also fails. Outer boundary terminates with exit code 1.
  - Added CLI flag `--test-injected-failure-after-marker-drop` to simulate harness assertion failure precisely after the task ownership marker is dropped; verified that `finally` executes, restores the marker, and leaves `democa_dental_saas_test` confirmed usable.
  - Early preflight refusals (`--test-injected-failure`) remain strictly mutation-free before any schema changes or table drops.
- **Medium Test Fidelity: Shared Production Orchestration Replacing Test Replicas:**
  - Eliminated test-only replica closures and self-calling fakes in `tests/unit/SafeTestIsolationPreflightTest.php` that did not test actual production code.
  - Extracted shared, reusable orchestration methods in `scripts/TestDatabasePreflight.php`:
    - `checkDatabaseExistsOnServer(string $host, string $user, string $pass, int $port, string $targetDb): bool`
    - `orchestrateCheckOnly(callable $existenceChecker, callable $liveConnector, string $targetDb, ?callable $writerSpy = null): array`
    - `orchestrateRunnerPreTest(bool $provisionRequested, callable $provisionerExecutor, callable $targetConnector, callable $liveValidator): array`
  - Integrated shared orchestration directly into production scripts `scripts/provision_test_db.php` and `scripts/safe_test_runner.php`.
  - Re-implemented unit tests in `SafeTestIsolationPreflightTest.php` to exercise the exact production methods with mock/spy collaborators:
    - Demonstrated negative path: reversing runner execution order in `orchestrateRunnerPreTest` fails unit test with `Provisioning must execute BEFORE target connection!`.
    - Demonstrated negative path: calling writer/creator on absent target in `orchestrateCheckOnly` fails unit test with `Writer/creator must NOT be called on absent target in check-only mode.`.
  - Added automated unit test `testInjectedFailureAfterMarkerDropExecutesCleanupAndRestoresUsableTarget`.
- **Verification Evidence:**
  - Full Test Suite in a single invocation (`scripts/safe_test_runner.php --no-coverage --display-skipped tests/unit tests/database`): Tests: **313**, Assertions: **1898**, Errors: **0**, Failures: **0**, Skipped: **0** (20.5s).
  - Safe Test Isolation Preflight Suite (`SafeTestIsolationPreflightTest.php`): Tests: **26**, Assertions: **74**, Errors: **0**, Failures: **0**.
  - Containment Suite (`TenantBackupContainmentTest.php`): Tests: **13**, Assertions: **91**, Errors: **0**, Failures: **0**.
  - Canary Verification (`scripts/verify_isolation_and_canary.php`): All 6 verification steps passed cleanly.
  - Injected Failure After Marker Drop: verified exit code 1, STDERR diagnostic, `finally` cleanup executed, marker restored, target confirmed usable.
  - Injected Failure Early Refusal: verified exit code 1, early exit, zero database mutations.
  - Business Database Integrity: Observed counts recorded separately (`democa_dental_saas`: 67 tables, 7 patients, 9 appointments) without unproven absolute claims.
  - All changes left uncommitted for Codex audit.

## 2026-09-25 - Isolated Test Baseline: Tooling Safety Hardening (Run ID: 20260925-denta-plus-isolated-baseline-correction-02)

- **High Tooling Safety: Alternate Connection Overrides Rejected Fail-Closed:**
  - Hardened `scripts/TestDatabasePreflight.php::validateConfigBeforeConnection()` to reject non-empty `failover`, `DSN`, and `subdriver` configuration keys without connecting, preventing CodeIgniter's `BaseConnection` failover loop from substituting remote connection parameters.
  - Added live client loopback enforcement in `validateLiveConnection()` checking `USER()` host identity (`localhost`, `127.0.0.1`, `::1`).
  - Added marker target database validation in `verifyOwnershipForReset()` enforcing that `$marker['target_db'] === self::APPROVED_DISPOSABLE_TEST_DB` alongside task owner signature and disposable flag.
  - Hardened `scripts/verify_isolation_and_canary.php` to run preflight validation on its own connection handle before issuing any writes, drop, or create statements.
  - Added pure unit regression tests in `tests/unit/SafeTestIsolationPreflightTest.php` for `failover` rejection, `DSN` rejection, `subdriver` rejection, marker missing records, wrong task owner, non-disposable flag, and target database mismatch without making real network connections.
- **Medium Tooling Safety: Strictly Read-Only --check-only & Fresh Provisioning Ordering:**
  - Corrected `scripts/provision_test_db.php`: made `--check-only` strictly read-only by querying `INFORMATION_SCHEMA.SCHEMATA` via raw MySQLi without selecting a database; reports `ABSENT / UNPROVISIONED` without executing `CREATE DATABASE` or mutating schemas, exiting 0.
  - Explicit database creation is reserved strictly for the actual provisioning path with verified error handling.
  - Reordered `scripts/safe_test_runner.php`: when `--provision` is passed, provisioning runs *before* connecting to the target database, allowing sound first-run bootstrap of an absent database.
  - Added automated tests in `SafeTestIsolationPreflightTest.php` verifying that absent-target `--check-only` does not invoke database creation and that first-run provisioning creates the schema before connecting.
- **Medium Tooling Safety: Nonzero Exit on Verification Failure & Checked Operations:**
  - Replaced all `die("message")` exits in `scripts/verify_isolation_and_canary.php` with `failVerification()` writing to STDERR and terminating with `exit(1)`.
  - Added `--test-injected-failure` flag to `verify_isolation_and_canary.php` and verified via automated test and CLI that parent commands receive exit code 1 with stderr diagnostic output.
  - Canary preservation check now compares complete row values across all columns before and after normal repeat provisioning.
  - Added bounded cleanup in `try ... finally` in `verify_isolation_and_canary.php` restoring clean test DB without touching unowned targets.
  - Added return value checks for `MigrationRunner::latest()` (`$migrationResult !== false`) and fixture `insert()` operations (`safeFixtureInsert`).
- **Verification Evidence:**
  - Full Test Suite in a single invocation (`scripts/safe_test_runner.php --no-coverage --display-skipped tests/unit tests/database`): Tests: **309**, Assertions: **1869**, Errors: **0**, Failures: **0**, Skipped: **0**.
  - Safe Test Isolation Preflight Suite (`SafeTestIsolationPreflightTest.php`): Tests: **22**, Assertions: **45**, Errors: **0**, Failures: **0**.
  - Containment Suite (`TenantBackupContainmentTest.php`): Tests: **13**, Assertions: **91**, Errors: **0**, Failures: **0**.
  - Canary Preservation & Guarded Refusal (`scripts/verify_isolation_and_canary.php`): All 6 verification steps passed (canary all columns preserved on repeat, wiped on explicit reset, unowned non-empty database refused by both normal and reset paths with exit code 1).
  - Business Database Integrity: Observed counts recorded separately (`democa_dental_saas`: 67 tables, 7 patients, 9 appointments) without unproven absolute claims.
  - All changes left uncommitted for Codex audit.



- **Canonical Migration Replayability & Pre-Migration Foundation:**
  - Removed obsolete snapshot and manufactured migration tracking rows.
  - Created `tests/_support/Database/LegacyBaseSchema.php`: encapsulates the 18 foundation tables that predated CodeIgniter migrations with `CREATE TABLE IF NOT EXISTS`, zero data rows, zero credentials, and zero DROP statements.
  - Reconciled migrations `2025-01-19-000001_CreateRBACTables.php` and `2025-01-19-000002_CreateActivityLogsTable.php` by replacing quoted `'CURRENT_TIMESTAMP'` defaults with `RawSql` and including columns required by downstream migrations (`roles.slug`, `roles.is_system`, `roles.created_by`, `permissions.category`, `permissions.is_system`, `permissions.sort_order`, `user_roles.expires_at`, `user_roles.is_active`).
  - Verified CodeIgniter's own `MigrationRunner::latest()` executes all 75 canonical migrations sequentially from scratch against an empty schema, recording them organically in the `migrations` table.
- **Fail-Closed Preflight Safeguard & Guarded Reset:**
  - Created `scripts/TestDatabasePreflight.php`: unified preflight safeguard policy used across `provision_test_db.php` and `scripts/safe_test_runner.php`. Validates host (`127.0.0.1`, `localhost`, `::1`), port (`3306`, `3307`), driver (`MySQLi`), target DB name (`democa_dental_saas_test`), and business disjointness before attempting any database connection. Strictly refuses remote targets without connecting.
  - Added unit test suite `tests/unit/SafeTestIsolationPreflightTest.php` (10 tests, 21 assertions) verifying fail-closed refusal of remote hosts, non-MySQLi drivers, non-approved ports, and business DB collisions without connecting.
  - Implemented `_test_db_marker` task ownership tracking table. Normal provisioning (`provision_test_db.php`) is strictly non-destructive: migrates pending schema without deleting existing data, and was verified via `scripts/verify_isolation_and_canary.php` to preserve synthetic canary rows across repeat invocations.
  - Guarded `--reset` requires explicit opt-in, validates ownership marker prior to any destructive statement, and refuses non-empty unowned databases with exit code 1.
- **Deterministic Synthetic Fixtures & 0 Skips:**
  - Seeded owned synthetic reference fixtures (clinic 1, active plan 1, clinic subscription 1, super_admin user 1, patient 1, encrypted WhatsApp meta_cloud channel for clinic 1, platform secrets `wa_app_secret`, `wa_phone_number_id`, `wa_access_token`, and executed `PermissionSyncService::fullSync()`).
  - Resolved all 16 previously skipped tests in `DispatchServiceTest`, `WhatsAppPhase5EndpointsTest`, and `WhatsAppWebhookTest` with deterministic local mock configurations (zero real network transmission).
- **Verification Evidence:**
  - Full Test Suite in a single invocation (`scripts/safe_test_runner.php --no-coverage --display-skipped tests/unit tests/database`): Tests: **297**, Assertions: **1845**, Errors: **0**, Failures: **0**, Skipped: **0**.
  - Containment Suite (`TenantBackupContainmentTest.php`): Tests: **13**, Assertions: **91**, Errors: **0**, Failures: **0**.
  - Previously Skipped Messaging Suites (`DispatchServiceTest`, `WhatsAppPhase5EndpointsTest`, `WhatsAppWebhookTest`): Tests: **34**, Assertions: **178**, Errors: **0**, Failures: **0**, Skipped: **0**.
  - Database Examples (`ExampleDatabaseTest.php`): Tests: **2**, Assertions: **3**, Errors: **0**, Failures: **0**.
  - Canary Preservation & Guarded Refusal (`scripts/verify_isolation_and_canary.php`): All 6 verification steps passed (canary preserved on normal repeat, wiped on explicit reset, unowned non-empty database refused by both normal and reset paths with exit code 1).
  - Business Database Integrity: Verified completely untouched (`democa_dental_saas`: 67 tables, 7 patients, 9 appointments).
  - All changes left uncommitted for Codex audit.

## 2026-09-24 - Checkpoint 01: Baseline Evidence, Program Plan & Tenant Backup Containment

- Baseline capture recorded under `.ai/antigravity-runs/20260924-denta-plus-program-01/`, `.../20260924-denta-plus-program-01-correction-01/`, and `.../20260924-denta-plus-program-01-correction-02/`: Git status/HEAD (c4f31e5), runtimes (PHP 8.2.12, Composer 2.9.5, Node v24.12.0, npm 11.11.0, MariaDB 10.4.32), migration status (69 batches current), static asset byte sizes, and SaaS guardrails baseline gate (exit code 1: 6 DOM sinks, 12 raw queries, 4 font strings; blind spot documented for ~40 camelCase `->isAdmin()` / `->inGroup()` calls).
- Established `docs/refactor/DENTA_PLUS_HARDENING_PERFORMANCE_PROGRAM.md` as the single active modernization program plan linked in `docs/START_HERE.md`, covering verified/false/already-fixed findings, 13-stage dependency order, acceptance gates, and risks.
- Contained P0 platform database backup exposure in the tenant plane:
  - `Settings::createBackup()` hard-denies with 404 (`PageNotFoundException::forPageNotFound()`), preventing all-table database dump generation.
  - `Settings::downloadBackup(string $filename)` hard-denies with 404 (`PageNotFoundException::forPageNotFound()`), closing dump file downloads.
  - `Settings::restore()` hard-denies with 404 (`PageNotFoundException::forPageNotFound()`).
  - `Settings::getBackups()` returns `[]` without scanning or exposing `writable/backups/`.
  - `SettingsService::generateDatabaseBackup()` unconditionally throws `\RuntimeException`, preventing programmatic all-table dumping.
  - Removed manual backup generation form, table list, and client-side download/restore scripts from `app/Views/settings/index.php` and `app/Views/settings/partials/backup.php`.
  - Added bilingual explanatory status alert in English and Arabic (`TenantMisc.backupManagedPlatformTitle` / `TenantMisc.backupManagedPlatformDesc`), preserving Export Retention Policy and ordinary clinic settings functionality.
  - Left existing backups on disk untouched without deletion or mutation.
- Established repeatable test isolation:
  - Added `scripts/safe_test_runner.php` with preflight isolation check against `SELECT DATABASE()`.
  - Configured `phpunit.xml.dist` with `<env name="database.tests.database" value="democa_dental_saas_test"/>`.
  - Added fail-closed safeguard in `TenantBackupContainmentTest.php::setUp()` aborting immediately before assertions or fixture creation if connected to business database `democa_dental_saas`.
  - Verified containment suite: 13 tests, 91 assertions, 0 failures on verified disposable test target `democa_dental_saas_test`.
- Acceptance Boundary & Unresolved Validation Debt:
  - Accepted strictly as a coherent local checkpoint commit; not certified for full regression suite release or production deployment.
  - Six broader unit-suite issues on the disposable test database (285 tests, 1795 assertions, 2 errors, 4 failures) are documented as unresolved test-environment and schema reconciliation debt: onboarding commit failure, HealthTest baseURL, AppApiFoundation clinic_required/no_clinic, invoice-table down assertion, invoice empty-state assertion, and TenantDashboardSubscriptionTimezoneTest login/clinic-select routing. Incomplete test schema in the disposable database must be reconciled in a dedicated task before the framework upgrade.
  - Failing SaaS guardrails gate (exit code 1) and deferred browser verification remain documented limitations.

## 2026-09-15 - Subscription Timezone Contract & Tenant Dashboard Entry

- Established canonical UTC persistence (`ClinicSubscriptionModel::nowUtc`) for all subscription lifecycle timestamps across `OnboardingService`, `ControlPlane\Clinics`, and `ControlPlane\PlanRequests`.
- Centralized subscription timestamp parsing and standing evaluation in `ClinicSubscriptionModel::parseUtcTimestamp()` and `ClinicSubscriptionModel::evaluateStanding()`, eliminating the 3-hour mismatch between controller local timezone (`Asia/Jerusalem`, UTC+3) and bootstrap filter UTC reader.
- Subscriptions fail closed on malformed/corrupt dates, block future starts (`NOT_STARTED`), and block expired periods (`EXPIRED`) without grace periods or unconditional allowances.
- Delta Audit Corrections (Round 1 & Round 2 Final Delta):
  - Pre-validation and zero-mutation before destructive state change: in `ControlPlane\Clinics::startSubscription` and `ControlPlane\PlanRequests::approve`, plan existence, duration, and `resolveEndAt()` are validated before any database mutation or transaction begin, preventing invalid inputs from canceling active subscriptions.
  - Concurrency serialization & checked writes: `startSubscription`, `extendSubscription`, `cancelSubscription`, `updateSubscriptionDates`, and `approve` serialize operations with InnoDB `SELECT ... FOR UPDATE` row locks, enforce checked updates/inserts, and ensure fail-closed audit logging (`$failClosed = true`) atomically within the same transaction.
  - Full transaction rollback on late write failure: verified by behavioral test that audit or secondary write failure rolls back intermediate cancellations and inserts cleanly to baseline.
  - AuthFilter generalization: removed test-driven hardcoded `dental-saas/` path from `app/Filters/AuthFilter.php`; standardized on `$request->getUri()->getRoutePath()` for universal support across root and arbitrary subfolder deployments.
  - Local datetime normalization & DST policy: `Clinics::normalizeDateTime` fails closed on invalid timezone names (returns `null`), resets minute-only seconds deterministically to `:00`, rejects DST spring-forward gaps, and rejects ambiguous DST fall-back folds (`isAmbiguousDstFold`) unless an explicit ISO offset is supplied.
  - Bounded parser & display hardening: constrained `ClinicSubscriptionModel::parseUtcTimestamp` offset regex to valid real-world bounds (`+14:00` to `-12:00`), rejecting `+14:01`, `-12:01`, and `-14:00`; wrapped `ClinicSubscriptionModel::formatDisplay` with safe UTC fallback on invalid timezone names to prevent display exceptions.
  - Historical repair procedure in `docs/SaaS/DATETIME_CONTRACT.md` updated with accurate production snapshot evidence (`updated_at = '2026-09-15 19:40:16'`), application CLI command specification, MySQL DDL implicit-commit explanation, unique checked backup tables, full concurrency predicates, `ROW_COUNT() = 1` assertions, explicit operator ID, and prominent unexecuted designation.
  - Behavioral test suite `TenantDashboardSubscriptionTimezoneTest` expanded to 12 tests (245 assertions) covering strict grammar, valid/invalid offset extremes, DST gaps and folds, finite extension bounds, actual registered login/select/impersonation routes, zero-mutation on invalid start/approve, and late-write transaction rollback.
- Subscription Transaction & Concurrency Hardening Corrections:
  - Extracted unified `ControlPlaneSubscriptionService` governing `startSubscription`, `extendSubscription`, `cancelSubscription`, `updateSubscriptionDates`, and `approvePlanRequest`.
  - Owned transaction contract: rejects nested caller transactions (`$db->transDepth !== 0`), checks `transBegin()`, verifies `$db->transStatus() === false || !$db->transCommit()`, rolls back cleanly only when depth remains, and returns structured result codes instead of leaking raw exceptions.
  - Mandatory concurrency contract: `extendSubscription` requires `expected_end_at` and `expected_updated_at`; `cancelSubscription` requires `expected_updated_at`; `updateSubscriptionDates` requires `expected_start_at`, `expected_end_at`, and `expected_updated_at`; `approvePlanRequest` requires `expected_requested_plan_id` and `expected_requested_duration`. Missing, empty, or malformed tokens fail closed with localized machine code `missing_concurrency_token`, zero mutation, and zero audit rows before opening transactions.
  - Explicit NULL token sentinel: genuine SQL `NULL` timestamps (e.g. lifetime subscription with `NULL end_at`) are explicitly represented by `__NULL__`, distinguishing valid null state from missing tokens via `matchesNullableToken()`.
  - UI wiring: updated `app/Views/control_plane/clinic_detail.php` and `plan_requests.php` with mandatory hidden concurrency fields for extension, cancellation, date-update, and approval forms.
  - Regression suite `TenantDashboardSubscriptionTimezoneTest` expanded to 24 tests (504 assertions) covering registered controller routes for missing, empty, stale, and explicit `__NULL__` tokens across all operations.
- Validation: all 8 test suites passed in a single invocation (145 tests, 1101 assertions), touched PHP lint passed, `git diff --check` clean, DOM sink scan clean.

## 2026-09-15 - Control Plane Onboarding Transaction Fix

- Fixed onboarding in global mode without a tenant session by containing explicit tenant-scope bypasses in the control-plane workflow and supplying clinic IDs on writes.
- Added a control-plane email-check endpoint; onboarding no longer calls the tenant-plane users endpoint. Invalid email returns 422; query failure returns sanitized JSON with 503.
- Onboarding requires the authenticated active Super Admin actor, owns one checked transaction, rejects nested calls without touching the caller transaction, checks required writes, and persists both plan and control-plane success audits before commit.
- Removed implicit creation/permission expansion of shared manager roles. Missing required role/authentication bootstrap fails closed. Existing users keep their profile/password; inactive and Super Admin targets are rejected.
- Initial primary administrator designation and clinic defaults are persisted atomically. Added localized English/Arabic outcomes and behavioral tests for committed creation, a real create POST, reuse, access denial, rollback on required-write/SQL failures, and rendered JavaScript parsing.
- Production onboarding creation and interactive browser QA remain unexecuted to avoid creating business records during deployment verification.
- Validation: seven focused/adjacent suites passed (121 tests, 597 assertions), touched PHP lint and whitespace checks passed, and the modified onboarding view has no DOM sinks. Repository-wide guardrails still report pre-existing violations in unrelated finance, reports, inventory, and other views.

## 2026-09-15 - Control Plane Clinic Administrator Final Audit Corrections

- Delta Audit Corrections (5 Findings Resolved):
  - **Finding 1 (High - Explicit Restore Required in Designation Endpoint)**: In `ControlPlane\Users::designateClinicAdmin`, removed automatic restoration for already designated degraded users. Posting to `/admin/designate` for an already designated user with degraded authority now fails closed with `UI.clinicAdminAlreadyDesignatedDegraded` and directs the operator to the explicit restore action. No concurrency tokens are manufactured from server state; restoration requires an explicit restore request with `expected_designated_admin_id`.
  - **Finding 2 (High - Demotion Preserves Inactive Membership Status)**: In `ClinicAdminLifecycleService::replaceAdministrator`, demoting a replaced administrator (`demote_to_staff`) updates only `role_id` to staff and strictly preserves the previous membership's `status`. Locked/suspended memberships remain inactive and are never silently reactivated during demotion.
  - **Finding 3 (Medium - Localized Service Outcome Messages)**: Added stable machine result codes (`admin_assigned`, `admin_replaced`, `admin_restored`) to service success returns, and implemented `formatLocalizedMessage()` across both `Clinics` and `Users` controllers. Added complete English and Arabic translations for all service outcomes and errors in `app/Language/en/UI.php` and `app/Language/ar/UI.php` without altering internal machine codes.
  - **Finding 4 (Medium - Modal Focus Trapping & Accessibility)**: Implemented complete keyboard focus containment (`Tab` and `Shift+Tab` cycling) for active administrator dialogs in both `app/Views/control_plane/users/show.php` and `app/Views/control_plane/clinic_detail.php`. Guarded initial focus timeouts with `clearTimeout` and active modal visibility checks, protected against hidden modals consuming saved triggers, and ensured form reset and focus return on dialog closure. Authenticated browser QA remains explicitly deferred.
  - **Finding 5 (Medium - Bulk Resolver N+1 Query Elimination & Deterministic Tie-Breaking)**: In `ClinicAdminLifecycleService::getClinicAdministrators()`, preloaded all required role IDs across designated and fallback candidate memberships in a single batch query before evaluation, memoized roles in `$this->roleCache`, and passed the preloaded map to `isManagerRole()`. Added deterministic tie-breaking by `clinic_users.id ASC` after `clinic_users.created_at ASC` in both bulk and single resolvers. Verified bounded query count (4 clinics with 12 members execute <= 5 batch queries).

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
- Control Plane Clinic Detail Production Incident Resolution:
  - Root cause: `GET /controlplane/clinics/{id}` threw `Error: Call to a member function getResult() on bool in system/Model.php:287` because `control_plane_invoices` was omitted from the production database schema (due to historical `--ignore-table` in mysqldump commands while `migrations` recorded batch 24 as run).
  - Schema repair: added reconciliation migration `2026-09-14-200000_EnsureControlPlaneInvoicesTables` to repair entirely missing control-plane invoice tables when migrations run. It does not reconcile malformed or partially drifted existing tables.
  - Controller resilience: wrapped invoice query in `Clinics::show()` in a fail-safe try/catch block; invoice-query failures handled by this path no longer crash clinic detail and are surfaced as unavailable (`invoice_data_available = false`) with an operator warning and dashed statistics. Genuinely empty invoice sets preserve normal empty ledger rendering.
  - Logging infrastructure & diagnostics: initialized `writable/logs` on production Hostinger host to enable CI4 file logging. Diagnostic CLI script invocations previously logged errors due to unavailable CLI exec environments and are recorded as diagnostic noise.
  - Test coverage: added regression tests in `ControlPlaneClinicDetailTest` covering missing-table migration non-destructive rollback, query failure unavailable state, genuinely empty state, and registry/detail parity.
  - Documentation: updated `AGENTS.md`, `CLAUDE.md`, and `docs/OPERATIONS_RUNBOOK.md` to remove `--ignore-table` flags, prohibit generic production `php spark migrate:rollback`, and require reviewed migration-specific rollback plans.
  - Authenticated browser QA remains deferred.
- Control Plane Clinic Administrator Lifecycle & Registry Parity (Final Corrections):
  - Irreversible reconciliation migration: modified `2026-09-14-200000_EnsureControlPlaneInvoicesTables::down()` to be a non-destructive no-op because automatic rollback cannot safely distinguish adopted from newly created tables and must never drop financial tables or data.
  - Billing unavailable contract: `Clinics::show()` passes `'invoice_data_available' => false` on catch and logs exception class, clinic ID, and message. `clinic_detail.php` renders `--` for stats cards and a compact localized warning alert (`lang('UI.billingDataUnavailable')`), suppressing false zero ledgers.
  - Registry & detail parity: `/controlplane/clinics` and `/controlplane/clinics/{id}` now both resolve administrator identity and degraded state through `ClinicAdminLifecycleService::getClinicAdministrators()` and `getClinicAdministrator()`. Eliminates registry discrepancy where the oldest member was previously displayed.
  - Registry degraded-state indicators: DataTable renders explicit degraded-state badges (`Account Disabled`, `Inactive Membership`, `Missing Authority`, `Unassigned`) alongside designated administrator emails.
  - User profile in-context administrator actions: `/controlplane/users/{id}` annotates existing clinic memberships with primary administrator status and provides contextual "Make Primary Administrator" and "Restore Authority" actions with accessible modal dialogues (Cancel, X, Escape, backdrop dismissal), single-primary-admin explainer, previous-admin action choices (`demote_to_staff`, `keep_role`, `deactivate_membership`), and concurrency protection (`expected_admin_id`).
  - Strict product invariant: exactly one primary administrator per clinic. Management-role users can exist as managers without designated administrator status. Suspension/reactivation never silently alters `designated_admin_user_id`.

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
