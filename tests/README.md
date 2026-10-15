# Running Application Tests

## Safe Test Execution & Test Database Provisioning (Mandatory)

> [!IMPORTANT]
> **Strict Fail-Closed Isolation:** Tests must NEVER run against or mutate the business database (`democa_dental_saas`).
> Always use `scripts/safe_test_runner.php` or ensure exact dotted `database.tests.database=democa_dental_saas_test` is set in the execution environment.

### 1. Provisioning / Resetting the Isolated Test Database

The disposable test database schema is provisioned from the minimal pre-migration foundation (`tests/_support/Database/LegacyBaseSchema.php`), followed by canonical migrations executed via CodeIgniter's `MigrationRunner::latest()`, and seeded with deterministic synthetic reference fixtures (zero PHI, zero credentials, zero business transaction records).

To verify isolation or provision the disposable test database:
```console
# Verify connection, isolation, and disposable target eligibility (no mutations):
php scripts/provision_test_db.php --check-only

# Normal non-destructive provisioning (applies pending schema/seeds without wiping existing data):
php scripts/provision_test_db.php

# Explicit guarded reset (requires ownership marker verification, wipes disposable schema, re-provisions):
php scripts/provision_test_db.php --reset
```

### 2. Running Tests Safely

Use `scripts/safe_test_runner.php` which validates preflight isolation (`TestDatabasePreflight`) before invoking PHPUnit:
```console
# Run focused containment suite:
php scripts/safe_test_runner.php --no-coverage tests/unit/TenantBackupContainmentTest.php

# Run full test suite in a single invocation:
php scripts/safe_test_runner.php --no-coverage --display-skipped tests/unit tests/database

# Automatically provision clean database before running tests:
php scripts/safe_test_runner.php --provision --no-coverage tests/unit
```

---

## Resources

* [CodeIgniter 4 User Guide on Testing](https://codeigniter.com/user_guide/testing/index.html)
* [PHPUnit docs](https://phpunit.de/documentation.html)
* [Any tutorials on Unit testing in CI4?](https://forum.codeigniter.com/showthread.php?tid=81830)

## Requirements

It is recommended to use the latest version of PHPUnit. At the time of this
writing, we are running version 9.x. Support for this has been built into the
**composer.json** file that ships with CodeIgniter and can easily be installed
via [Composer](https://getcomposer.org/) if you don't already have it installed globally.

```console
> composer install
```

If running under macOS or Linux, you can create a symbolic link to make running tests a touch nicer.

```console
> ln -s ./vendor/bin/phpunit ./phpunit
```

You also need to install [XDebug](https://xdebug.org/docs/install) in order
for code coverage to be calculated successfully. After installing `XDebug`, you must add `xdebug.mode=coverage` in the **php.ini** file to enable code coverage.

## Setting Up

A number of the tests use a running database.
In order to set up the database edit the details for the `tests` group in
**app/Config/Database.php** or **.env**.
Make sure that you provide a database engine that is currently running on your machine.
More details on a test database setup are in the
[Testing Your Database](https://codeigniter.com/user_guide/testing/database.html) section of the documentation.

## Running the tests

The entire test suite can be run by simply typing one command-line command from the main directory.

```console
> ./phpunit
```

If you are using Windows, use the following command.

```console
> vendor\bin\phpunit
```

You can limit tests to those within a single test directory by specifying the
directory name after phpunit.

```console
> ./phpunit app/Models
```

## Generating Code Coverage

To generate coverage information, including HTML reports you can view in your browser,
you can use the following command:

```console
> ./phpunit --colors --coverage-text=tests/coverage.txt --coverage-html=tests/coverage/ -d memory_limit=1024m
```

This runs all of the tests again collecting information about how many lines,
functions, and files are tested. It also reports the percentage of the code that is covered by tests.
It is collected in two formats: a simple text file that provides an overview as well
as a comprehensive collection of HTML files that show the status of every line of code in the project.

The text file can be found at **tests/coverage.txt**.
The HTML files can be viewed by opening **tests/coverage/index.html** in your favorite browser.

## PHPUnit XML Configuration

The repository has a ``phpunit.xml.dist`` file in the project root that's used for
PHPUnit configuration. This is used to provide a default configuration if you
do not have your own configuration file in the project root.

The normal practice would be to copy ``phpunit.xml.dist`` to ``phpunit.xml``
(which is git ignored), and to tailor it as you see fit.
For instance, you might wish to exclude database tests, or automatically generate
HTML code coverage reports.

## Test Cases

Every test needs a *test case*, or class that your tests extend. CodeIgniter 4
provides one class that you may use directly:
* `CodeIgniter\Test\CIUnitTestCase`

Most of the time you will want to write your own test cases that extend `CIUnitTestCase`
to hold functions and services common to your test suites.

## Creating Tests

All tests go in the **tests/** directory. Each test file is a class that extends a
**Test Case** (see above) and contains methods for the individual tests. These method
names must start with the word "test" and should have descriptive names for precisely what
they are testing:
`testUserCanModifyFile()` `testOutputColorMatchesInput()` `testIsLoggedInFailsWithInvalidUser()`

Writing tests is an art, and there are many resources available to help learn how.
Review the links above and always pay attention to your code coverage.

### Database Tests

Tests can include migrating, seeding, and testing against a mock or live database.
Be sure to modify the test case (or create your own) to point to your seed and migrations
and include any additional steps to be run before tests in the `setUp()` method.
See [Testing Your Database](https://codeigniter.com/user_guide/testing/database.html)
for details.

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
