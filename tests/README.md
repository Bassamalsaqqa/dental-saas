# Running application tests

Inspect the selected tests before execution. A `tests/unit` location does not guarantee database safety. Business/customer databases and dumps are never test fixtures. Do not edit `.env` or start business MySQL just to run checks.

The application tool metadata points to this repository. `npm test` / `npm run test:unit` runs the named database-free finance, Arabic integrity, booking and runner checks; it does not build assets. `npm run test:finance:http` uses the guarded SQL runner and requires an explicitly configured, owned isolated target. No dependency or pinned CSS changes are part of these commands.

For an inspected database-free suite:

```powershell
php vendor/phpunit/phpunit/phpunit --no-coverage --no-logging --do-not-cache-result tests/unit/FinanceSalaryProviderAccountingTest.php tests/unit/FinanceSettlementProviderCurrencyTest.php tests/unit/FinanceSettlementServiceTest.php tests/unit/FinanceSettlementInputGuardTest.php
```

For SQL/routed tests, use an explicitly owned disposable local target and `scripts/TestDatabasePreflight.php`. Exact dotted `database.tests.*` configuration must resolve to `democa_dental_saas_test`, remain disjoint from business configuration, and pass both configuration and live identity/ownership checks. A database name alone proves neither ownership nor safety. Run mutation suites serially.

`php scripts/provision_test_db.php --check-only` is read-only. Provisioning applies canonical migrations and synthetic reference fixtures; reset destroys owned test tables. Neither is a routine command to run without target checks and authorization. The test-only foundation supplies the current non-null appointment clinic reference and the verified nullable finance author column required by existing writers; it does not alter populated application tables.

Use `scripts/safe_test_runner.php` for inspected database-backed PHPUnit suites. It launches the same PHP directly and forwards the verified test connection through a CLI-only prepend bridge, avoiding lost dotted environment settings in Windows/CI DotEnv. Credentials are passed privately to the child, never written to command arguments or new files. Do not run the entire suite blindly: some historical regressions have target-specific guards or DDL assumptions. No notification dispatch or production requests are part of ordinary test acceptance.

`tests/regression/FinanceLocalSettlementSql.php` exercises real settlement services against the owned local test database. It requires `DENTA_REPLAY_TEST_BOOTSTRAP` pointing to an explicitly supplied CLI bootstrap that loads the framework's testing environment with the isolated connection configuration. It validates configuration before connecting and live identity plus the ownership marker before fixture writes. It adds synthetic fixtures, retaining them for inspection; it never imports a customer seed or resets tables. Its SQL/replay assertions do not certify HTTP CSRF/session/RBAC or production.

Current dependency versions are pinned in `composer.lock`; do not install or upgrade dependencies to follow an old testing tutorial. `phpunit.xml.dist` is the default configuration. Coverage requires a configured coverage engine and should be requested only when useful. Historical generic setup/tutorial text is available in Git history.

`tests/unit/FinanceSalaryWorkflowHttpTest.php` uses registered routes and the existing shared synthetic tenant fixture, with an additional configuration/live ownership check before fixture writes. Run it serially through the safe runner after the target is proven disposable. It covers salary states/receipts, subsequent settlement/replay, permission/CSRF/tenant rejection, clinic-user/outside-provider cuts, payout/history, invoice receipt/refund preservation and neutral internal transfer. It cleans up only its newly created clinic scopes. No production connection, notification or feature rollout is involved.

## Finance browser regression

Start `node tests/js/finance-presenter-server.cjs` in the existing checkout. It binds only `127.0.0.1:57526`, renders real PHP presenters/assets using synthetic permission/data helpers, and refuses financial writes. It neither bootstraps the application nor connects to a database. Stop that process when finished.

`tests/js/FinanceContextCancellation.browser.js` is a Playwright Page function (run as the browser MCP `browser_run_code_unsafe` filename, or invoke it with an existing Playwright Page). It covers context cancellation, late responses, pending/uncertain submission protection, and unpaid/paid/unknown/void drawers across EN/AR and 1366/768/390/320px. No dependency installation is needed for the connected browser. Mock transport evidence is not SQL persistence or routed authentication acceptance. `FinanceAuditPresentation.browser.js` checks actual mixed-currency spacing/delimiters and localized booking settings in EN/AR at 320/390px against the same presenter server.

## P0 identity / tenant RBAC regression suite (2026-10-15)

Use a provisioned disposable loopback database with a dedicated test-only account. Set the dotted test connection values through the existing approved local test environment; the runner must pass its preflight. The suite uses synthetic fixtures and cleans up only its own rows; do not run it against a business database.

```powershell
php scripts/safe_test_runner.php --no-coverage --no-logging --do-not-cache-result tests/unit/P0AuthorityReviewTest.php tests/unit/P0PermissionFilterReviewTest.php tests/unit/P0MembershipMutationReviewTest.php tests/unit/P0ReviewBoundaryAcceptanceTest.php tests/unit/PR1ReviewCorrectionRegressionTest.php tests/unit/AppApiFoundationTest.php tests/unit/AppApiPatientsTest.php tests/unit/ControlPlaneOnboardingTenantContextTest.php tests/unit/ControlPlaneOnboardingBehaviorTest.php tests/unit/ClinicAdminLifecycleServiceTest.php tests/unit/ClinicAdminLifecycleComprehensiveTest.php tests/unit/TenantDashboardSubscriptionTimezoneTest.php tests/unit/TenantBackupContainmentTest.php
node tests/js/P0SearchUrlTest.cjs
```

Initial draft isolated run: 204 tests / 1,676 assertions and 36 JS assertions passed. Coverage includes real registered filter execution and transaction/audit failures, not only parser or mocked happy paths. Bilingual render checks also parse inline JavaScript using Node. See [review, known release blockers and rollback](../docs/SaaS/P0_IDENTITY_RBAC_REVIEW_2026-10-15.md).

PR #1 correction target run: **42 tests / 514 assertions passed** in `P0ReviewBoundaryAcceptanceTest.php` and `PR1ReviewCorrectionRegressionTest.php`. The expanded command above is the single final focused acceptance suite; its exact corrected SHA and outcome are retained in the PR. These tests use actual registered routes and preserve canonical foreign-key constraints (deleting a fixture role cascades its memberships), without replacement routes or weakened schema.


## Phase B focused acceptance

Use the explicitly owned disposable database on loopback port 3307, dedicated test credentials and a valid ownership marker. Apply canonical migrations to that isolated target only. Never use a customer dump or a production connection. Run serially: this suite includes a two-process booking race and an expansion rollback/reapply test, which refuses unrelated populated provider data.

```console
php scripts/safe_test_runner.php --no-coverage --no-logging --do-not-cache-result --configuration phpunit.phase-b.xml
node tests/js/PhaseBSlotTransportTest.cjs
node tests/js/PhaseBEditSlotPreservationTest.cjs
node tests/js/P0SearchUrlTest.cjs
```

The PHP routed regressions generate synthetic EN/AR edit HTML in ignored `tmp/phase-b-rendered-edit-*.html`; the edit-slot JavaScript check consumes those artifacts. No network, notifications, production migration, GitHub Actions or broad suite is required.


## PR #2 focused review corrections

No migration/provisioning is needed for this correction. Use the existing task-owned, already expanded disposable database on port 3307 and run serially:

```console
php scripts/safe_test_runner.php --no-coverage --no-logging --do-not-cache-result --configuration phpunit.pr2-review.xml
node tests/js/PR2DoctorIdentityMobileTest.cjs
node tests/js/PhaseBSlotTransportTest.cjs
node tests/js/P0SearchUrlTest.cjs
```

The PHP correction tests render synthetic full-create/edit/shared/quick controls and EN/AR dayboards into ignored `tmp/pr2-mobile-*.html`. The browser check uses local Chrome headless DevTools (override `PR2_BROWSER_BINARY` if necessary), actual 320/360/390px viewports, the real compiled CSS and app Select2 initializer. Cache **the same versions already loaded by `main_auth.php`**, without installing/upgrading dependencies: `https://code.jquery.com/jquery-3.7.1.min.js` as `tmp/pr2-jquery-3.7.1.min.js`, and Select2 `4.1.0-rc.0` JS/CSS from jsDelivr as `tmp/pr2-select2-4.1.0-rc.0.min.js` / `.min.css`. These ignored browser fixtures are never committed or deployed. The check blocks external host resolution during browser execution, verifies visible reference prefixes/menu selection/submitted IDs and unclipped column references, and saves 360px screenshots. No production browser requests or customer fixtures are used.
