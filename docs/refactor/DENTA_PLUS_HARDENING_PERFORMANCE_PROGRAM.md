# Denta+ Modernization, Hardening & Performance Program

**Authoritative Implementation Plan**
**Track:** Modernization & Performance Refactor
**Status:** Active
**Initial Checkpoint:** Checkpoint 01 (Baseline & Tenant Database-Backup Containment)
**Last Updated:** 2026-09-25
**Target Head:** `c4f31e59cc656bbb622047a33217fd2fd0be5c5a`

---

## 2026-10-22 consolidated execution matrix

This section supersedes historical checkpoint statuses below. Development stays in Draft PR #20 on the deployed M2 ancestry; it does not authorize production integration, migrations or activation. The Owner-supplied `DENTA_PLUS_UNIFIED_MASTER_ENGINEERING_PLAN_2026-10-19.md` is reconciled against current source, not reapplied as patches.

| Scope | State | Current disposition |
|---|---|---|
| Stage 0, M1 inventory/COGS/tenant joins and clinic messaging | DONE | Deployed M2 ancestry; reuse accepted evidence. |
| M2 signing/publication recovery and payout preservation | DONE | Preserve immutable evidence; PR19 final external review was Owner-waived, not approved. |
| M2-C operational corrections, explicit applications/returns | PARTIAL | Implemented and focused acceptance recorded. Cash-backing finding retracted; overall external review remains COMMENTED/CHANGES_REQUESTED, not approval. Final consolidated review required. |
| Patient counts and report queries | DONE (development) | Explicit clinic/page aggregate replaces incorrect retained-builder N+1. Four monthly charts use one grouped query; DOB frequencies retain existing PHP calendar semantics. Unused full-clinic treatment/inventory registry loads removed; treatment stats 8 queries -> 1, inventory counters 4 -> 1, unchanged criteria. |
| Timeline / chart data serialization | DONE narrow correction | Twelve timeline fields and six specifically Owner-authorized chart JSON slots are HTML-safe, with exact decoded data, inert-payload browser proof and golden protected-code comparison. No stored evidence, SVG, rendering or snapshot changes. |
| Static delivery and patient preview | DONE (development) | Stable metadata asset versions, consuming-view chart/calendar vendors, hidden-tab polling pause, protected lazy raster URLs instead of Base64, responsive login WebP with preserved original. |
| Storage integrity | PARTIAL | Retained metadata quota totals fixed; newest export remains visible; publication precedes retention. User deletion retains bytes. Physical purge disabled until approved retention/legal-hold policy. Orphan/derivative physical accounting remains separate. |
| Patient email tenancy | DONE (development) | Shared clinic validation plus targeted scoped unique index migration. Current Hostinger global unique index is confirmed; source alone does not remove it. Production migration separately authorized only. |
| Legacy group authorization | PARTIAL | Unused controller wrappers removed; camelCase guard added. Two exact non-authorizing observations remain. Font guard now passes and view DOM sinks reduced from six to one in protected odontogram internals. Provider rows use one inert template with safe seeded JSON. Raw-controller-query and remaining DOM categories still fail, not waived. |
| Dynamic doctor scheduling | DONE code / BLOCKED pilot | Stable doctor identities, eligibility, availability, serialization, preserved legacy visits and impact acknowledgment already exist. Old PR4 ancestors must not be merged again. Five indexed DATE predicates optimized with SQL equivalence. Native rehearsal, roster/legacy review and separate migration/activation remain gates. |
| Missed/cancelled visit attention | PARTIAL (contact default OFF) | Read-only last-30-calendar-day panel with all-date canonical links plus an explicit manual factual contact ledger on appointment details. No automatic sending, consent inference, queue hiding, status change or rescheduling. Separate schema/activation and representative GET acceptance remain gates. |
| PHP patient workspace decomposition and paginated secondary histories | PARTIAL (bounded clinical/timeline complete) | Canonical ten-source keysets, 100-candidate work budgets, 25 visible matches, complete summaries/same-day context and explicit sparse-search continuation are wired to the actual PHP shell. 87SQL/17presenter/105HTTP checks and EN/AR desktop/mobile browser acceptance pass. Existing financial/MoneyTrail and other module datasets remain complete; no fully bounded-page or Hostinger speedup claim. |
| Atomic invoice + initial receipt | PARTIAL (default OFF) | One owned transaction composes existing invoice, receipt, treasury attribution, clinical/provider linkage and audits. Persistent actor/tenant request identities prevent duplicate checkout. 30 request/concurrency SQL checks, 32 actual shared-receipt checks and native permission/replay/rollback acceptance pass; 18 two-connection clinical-claim checks and fresh routed clinical checkout/retry pass after stale-source double-billing containment. Actual full-shell EN/AR desktop/mobile interactions and 15 actual receipt/provider/audit composition checks pass. Separate schema/activation and final review remain gates. |
| Media derivatives | PARTIAL (default OFF) | Bounded private raster variants, checksum metadata, explicit cursor CLI and original-authorized preview routes implemented; 55 synthetic GD/SQL/file checks pass, including two-process rendering/publication, injected metadata failure, explicit recovery retry and all eight EXIF orientations. Bounded read-only integrity report works while OFF. Six native loopback HTTP checks certify original-authorized delivery/fallback, permission revocation, tenant denial and no-store; 14 migration preflight checks pass. Hostinger staging/capacity and operator rehearsal still required. No automatic backfill/purge. |
| React splitting / App API expansion | BLOCKED separate source | `Bassamalsaqqa/Dentacare-Shell` resolves; old `dentacare-shell-5051486e` does not resolve with current access. This repo tracks generated `public/app`, not React source. Never hand-edit compiled bundles or claim source modernization here. |
| Clinical corrections, perio/referral depth | PARTIAL / BLOCKED semantics | Existing signed notes/addenda and procedure transitions remain. Completed procedure amendments require explicit provenance and separate financial consequences; specialized chart definitions and clinical signoff are not invented. |
| Purchasing / receiving / supplier accounting | PARTIAL | Existing reorder, receiving and supplier links remain. New order/payable lifecycle requires explicit receiving/valuation and cancellation rules; no automatic supplier charges. |
| Payroll, insurance, deposits and reconciliation | BLOCKED policy | Salaries remain separate from invoice commissions. Require accrual/period/approval rules; insurance responsibility/adjustment/claim rules and custody reconciliation policies before operational writes. Existing patient-credit policy is implemented, not a universal deposit policy. |
| Patient portal / intake / self-booking | BLOCKED security/product contract | Define patient identity verification, guardian access, consent/versioning, document scope and appointment approval before exposing clinical records or granting access. Safe architecture reuses tenant services; no parallel auth/backend or public PHI endpoints. |
| Retention/purge, fiscal documents, external payments | BLOCKED explicit validation | Require retention classes/legal holds, accountant-approved fiscal modes/numbering/tax and provider settlement rules. No silent operational defaults. |
| Framework upgrade, old branches, blanket cleanup | OBSOLETE as written | CI4 4.7.5 and earlier fixes are already deployed. No duplicate upgrade, old PR merges, bulk locale removal or odontogram optimization. |

### Account and manual-contact preservation

Physical global-account deletion is fail-closed in the central IonAuth model, before hooks, group removal or database operations. Control-plane screens retain explicit activation/deactivation and explain the restriction in EN/AR; denied direct requests return a plain localized reason. This also restricts unused-account hard deletion until a reviewed purge/eligibility contract can prove preservation. No account is silently deactivated and existing histories are not rewritten.

Fresh read-only Hostinger FK metadata found only `clinic_users.user_id -> users.id` with cascading deletion; the migration-declared activity-log FK is absent there. FK-only preflight cannot certify non-FK clinical, financial and audit attribution under concurrent writers. Physical clinic deletion is also denied centrally by `ClinicModel::delete`, the existing deletion guard and the routed control-plane endpoint before subscription/membership/history mutations. Existing suspend/reactivate actions remain explicit. This includes apparently unused clinics until approved offboarding defines preservation and purge. Actual Hostinger metadata confirms 17 cascading clinic FK mappings including clinical/patient data; finance-only eligibility was insufficient. Manual appointment contact history now uses a separate append-only ledger with restrictive parent FKs and actor/tenant/request identities; factual note text never enters general activity metadata. It remains default OFF. Existing read-only no-show/cancellation attention lists remain available without automatically treating recorded contact as successful recovery.

### Language and operational identity preservation

Language-switch redirects are limited to the explicit configured application origin/path, including encoded traversal and forged Host-header checks. GET updates only personal preference; persisted clinic-default POST requires existing settings-edit authority and checked writes. The account/clinic purge restrictions above are intentional release limitations, not silent data cleanup.

### Measured development evidence

- Actual synthetic MariaDB report parity: 19 checks. Twelve-month baseline **12 queries / 9.852 ms -> 1 query / 1.259 ms**, same results on 1,200 synthetic patients. This is local timing, not Hostinger capacity certification. DOB hydration falls to 91 distinct dates in that fixture, not constant-space buckets.
- Registry actual SQL parity: six checks on 1,200 synthetic records per module. Treatment eight-counter baseline **8 queries / 7.783 ms -> 1 query / 1.657 ms**; inventory counters one query, preserving null/quantity/status semantics. Paginated table endpoints and quick-create options remain intact.
- Patient count query-contract: 18 checks, 100 rows **100 -> 1 query**; actual SQL aggregate/tenant parity also included in report fixture.
- Appointment DATE predicate equivalence: 12 actual SQL checks. No native EXPLAIN speedup claim.
- Patient secondary-history transfer on a deliberately wide 90-row synthetic fixture: examinations **2,548,255 -> 22,675 bytes**, appointments **2,551,561 -> 25,981**, treatments **2,591,240 -> 65,660**. Same rows/values/order and tenant filters; this is projection evidence, not production timing or bounded-row PHP completion.
- Login original **6,231,977 bytes** retained; 1920-wide WebP **31,176 bytes**, mobile **3,844 bytes**. EN/AR desktop/mobile rendered acceptance passed; fallback font limitations recorded privately.
- Fresh Hostinger read-only identity: commit `f86376a4a6c8f6bf0fe4471f08093af71e76e042`, tree `4f12c340f8869b4b8f8aff57881c4b29ee98a72c`, PHP **8.3.35**, MariaDB **11.8.9**, 77 migration records. `appointments.appointment_date` is DATE, `finances.total_amount` exists, correction tables absent, patient email globally unique. Runtime/schema observation is not native write/concurrency certification.
- Composer locked advisory check reports no advisories/abandoned packages; production npm audit reports zero. Compatible build-tool lockfile patches reduce full npm audit from 13 to 7 findings (5 high/2 moderate); build succeeds. Remaining Tailwind3-related dev-tool issues need reviewed remediation/containment, not `audit fix --force`. Bundled framework `system/` and separately generated React assets are outside those narrower clean audits; no blanket security certification.

Generated CSS rebuilt with the compatible patched lockfile: **291,800 -> 293,388 bytes**. Retained selectors keep identical property values; five unreferenced utility selectors disappear and 29 are added for current templates. This is not a claim that every source/artifact got smaller.

The campaign is not complete while MISSING entries remain. Continue safe implementation without intermediate external review requests. No production data, original checkout, historical staging or protected clinical evidence was changed.

---

## 1. Executive Charter & Governance

This document serves as the **sole active temporary implementation plan** for the end-to-end modernization, hardening, and performance refactor of the Denta+ (DentaCare) SaaS platform. It governs task order, acceptance gates, risk controls, and verified findings across all modernization stages.

> [!IMPORTANT]
> **Governance Precedence:** This document is an active implementation roadmap and does **not** supersede permanent architectural contracts wholesale. Core invariants continue to be governed by:
> - [`docs/SaaS/TENANT_CONTEXT_CONTRACT.md`](file:///C:/xampp/htdocs/dental-saas/docs/SaaS/TENANT_CONTEXT_CONTRACT.md) (Tenant Isolation & Context Mechanics)
> - [`docs/API_CONTRACT.md`](file:///C:/xampp/htdocs/dental-saas/docs/API_CONTRACT.md) (Public `/api/v1/*` Stateless Semantics)
> - [`docs/APP_API_CONTRACT.md`](file:///C:/xampp/htdocs/dental-saas/docs/APP_API_CONTRACT.md) (First-party Session `/app-api/v1/*` Semantics)
> - [`docs/SaaS/SAAS_PROGRAM.md`](file:///C:/xampp/htdocs/dental-saas/docs/SaaS/SAAS_PROGRAM.md) (SaaS Invariants & Airlock Doctrine)
> - [`docs/START_HERE.md`](file:///C:/xampp/htdocs/dental-saas/docs/START_HERE.md) (Documentation Map)

### 1.1 Architectural Invariants & Non-Negotiables
- **Hostinger Shared Hosting Compatibility:** The platform operates under standard shared hosting constraints without resident Node.js processes, Redis daemons, or long-running background worker services. Background tasks must execute via CLI Spark entrypoints or scheduled cron triggers.
- **Backend-Owned Authority:** The CodeIgniter 4 backend and MySQL database remain the authoritative source of truth. All business rules, invariants, and authorization decisions are enforced server-side; client interfaces (PHP views and React SPA) are untrusted presentation surfaces.
- **Authentication vs. Authorization Separation:** IonAuth handles user identity authentication only. Authorization is strictly permission-based, governed via `PermissionService` (RBAC) and tenant context scoping. Role/group-based checks are deprecated and forbidden.
- **Distinct API Trust Models:**
  - `/api/v1/*`: Public integration plane, stateless Bearer token authentication, zero web session reliance, JSON only, fail-closed without HTML redirects.
  - `/app-api/v1/*`: First-party application plane, authenticated via CI4 web session (`ci_session_saas`), CSRF-protected, tenant-scoped.
  - `/controlplane/*`: Platform administration plane, strictly requiring `global_mode=true` under the TenantFilter airlock doctrine.
- **Strict Clinical & Financial Separation:** Clinical findings, examinations, and procedures record health delivery; financial invoices, payments, adjustments, and provider payouts constitute an immutable, idempotent financial ledger. Clinical documentation must never directly mutate financial balances.
- **Signed Examination & Record Immutability:** Once clinical examinations or treatment records are finalized or signed, they are immutable. Revisions require versioned addenda or superseding records.
- **Bilingual & RTL Parity:** Full functional and visual parity between English (LTR) and Arabic (RTL) across all views and UI components. All user-facing strings must use canonical language keys (`lang()`).

---

## 2. Reconciled Findings & Current-State Diagnosis

### A. Verified Defect & Modernization Findings
1. **Tenant Database Backup Route Exposure (P0 - Contained in Checkpoint 01):**
   - *Code References:* `app/Config/Routes.php:647-652`, `app/Controllers/Settings.php:46-49, 78, 626-724`, `app/Services/SettingsService.php:267-336`.
   - *Mechanism:* The legacy backup mechanism performed PHP-level table iteration and SELECT query streaming across all database tables without tenant scoping or filtering, dumping the entire multi-tenant database to `writable/backups/`. It was NOT an external shell call to `mysqldump`, but direct PHP table reads. The legacy `/settings/restore` endpoint was already a placeholder that did not actually restore data.
   - *Exposure:* Authorized tenant managers (`settings:view` / `settings:edit`) could trigger full platform database dumps, enumerate dump files, download raw SQL, or attempt restores, completely bypassing tenant isolation and exposing passwords, sessions, and PHI of all clinics.
   - *Status:* **Contained in Checkpoint 01.** Routes and controller methods hard-deny with 404, `SettingsService::generateDatabaseBackup()` unconditionally throws `RuntimeException`, and the UI is sanitized with bilingual platform status advisories.
2. **Framework Obsolescence & Tracking Path:**
   - *Code References:* `system/CodeIgniter.php:58` (CI 4.6.3), `composer.json:52` (PSR-4 maps `CodeIgniter\` directly to `system/`).
   - *Fact:* CodeIgniter core is tracked directly within `system/` rather than managed as a Composer package under `vendor/codeigniter4/framework`.
   - *Status:* Slated for Checkpoint 02 (Upgrade to current 4.7.x release).
3. **Financial Integrity & Settlement Concurrency:**
   - *Code References:* `app/Controllers/Finance.php`, `app/Services/MoneyTrailService.php`, `app/Models/FinancePaymentModel.php`.
   - *Defect:* Dispersed transaction logic lacks a unified settlement mutation boundary with row locking, atomic charge line claims, and database-unique settlement idempotency/replay protection.
   - *Status:* Slated for Stage 2.
4. **Untyped Money Representation & Currency Precision:**
   - *Database:* Schema currently stores monetary fields using `DECIMAL(10,2)` or `DECIMAL(12,2)`.
   - *Scope:* Moving to integer minor units (cents) or exact scale decimals must be evaluated per currency (e.g. ILS, USD, EUR, GBP scale 2 vs JOD scale 3). No mandate has been given to unconditionally rewrite all columns to `BIGINT` without careful analysis of historical schema, foreign keys, and migration rollback feasibility.
   - *Status:* Slated for Stage 2/3 architectural review.
5. **Monolithic View Sizes & Unoptimized Bundles:**
   - *Code References:* `app/Views/patient/show.php` (376 KB, 6,700+ lines), `app/Views/layouts/main_auth.php` (125 KB, 2,800+ lines), `public/assets/images/login-background.jpg` (6.23 MB), `frontend/` single monolithic bundle (1.3 MB JS).
   - *Status:* Slated for Stage 5 and Stage 8.
6. **Frontend Repository Discrepancy:**
   - *Fact:* Per [`docs/refactor/LOVABLE_STRATEGY_CODEX.md`](file:///C:/xampp/htdocs/dental-saas/docs/refactor/LOVABLE_STRATEGY_CODEX.md), the React/Lovable source repository is documented as `Bassamalsaqqa/dentacare-shell-5051486e`, while owner references also identify `Bassamalsaqqa/Dentacare-Shell`. This repository identity must be formally aligned before Stage 9 React deployment.

### B. Unresolved / In-Progress Findings (Authorization Blind Spot - P0 / Stage 1)
1. **Group-Based Auth Checks in Controllers (P0 - Stage 1):**
   - *Audit Finding:* `scripts/ci/saas_guardrails.sh`'s `run_group_guard` passes with 0 matches because it **only scans for snake_case** `in_group(` and `is_admin(`.
   - *Blind Spot:* CamelCase IonAuth calls (`->isAdmin()`, `->inGroup()`) remain present in active controllers (approx. 40 occurrences, e.g. `RoleController.php:51,58`, `ControlPlane/Entry.php:39`, `Users.php:574,879,1019`, `UserManagementController.php:66,71`, `SyncController.php:15,43,69`, `DebugController.php:34,52`, `BaseController.php:110,285,300`, `Auth.php:89,525,555`).
   - *Disposition:* This authorization drift is an **UNRESOLVED P0 DEFECT** belonging in Stage 1 remaining security work. All controller methods relying on `isAdmin()` or `inGroup()` must be systematically migrated to `has_permission()` via `PermissionService`, and `saas_guardrails.sh` updated to scan for both camelCase and snake_case patterns.

### C. Gated / Unexecuted Work
1. **Historical Subscription 35 Data Repair:**
   - The row-locked repair procedure in `docs/SaaS/DATETIME_CONTRACT.md` remains intentionally unexecuted locally to protect database integrity without production coordination.

---

## 3. Checkpoint 01 Empirical Baseline Summary

- **Repository:** `C:/xampp/htdocs/dental-saas` (`Bassamalsaqqa/dental-saas`)
- **HEAD Commit:** `c4f31e59cc656bbb622047a33217fd2fd0be5c5a`
- **Runtimes:** PHP 8.2.12 (ZTS x64), Node.js v24.12.0, npm 11.11.0, Composer 2.9.5, MariaDB 10.4.32 on `127.0.0.1:3306`.
- **Migration State:** 69 batches applied and verified; latest `2026-09-14-200000` (`EnsureControlPlaneInvoicesTables`).
- **PHPUnit Unit Suite Baseline on Disposable Target (`democa_dental_saas_test`):**
  - Full Unit Suite: Tests: **285**, Assertions: **1695**, Errors: **0**, Failures: **0**, Skipped: **16** (authoritative raw log: `.ai/antigravity-runs/20260924-denta-plus-isolated-baseline-reconciliation/unit-regression-results.txt`).
  - Containment Suite (`TenantBackupContainmentTest`): **13 passed**, **91 assertions**, 0 failures.
  - Database Examples Suite (`ExampleDatabaseTest`): **2 passed**, **3 assertions**, 0 failures.
  - All 6 previously failing issues classified and resolved:
    1. `HealthTest::testBaseUrlHasBeenSet`: Initialized default typed `$baseURL` in `app/Config/App.php`.
    2. `AppApiFoundationTest::testBootstrapClinicRequiredState`: Removed hardcoded `databaseGroupName = 'default'` in `app/Config/IonAuth.php` so IonAuth respects the active testing connection.
    3. `TenantDashboardSubscriptionTimezoneTest::testActualRegisteredLoginAndClinicSelectRoutesToDashboard`: Resolved via IonAuth dynamic database group inheritance.
    4. `ControlPlaneClinicDetailTest::testEnsureControlPlaneInvoicesTablesDownIsNonDestructive`: Provisioned physical tables `control_plane_invoices` and `control_plane_invoice_payments` in canonical baseline.
    5. `ControlPlaneClinicDetailTest::testShowWithEmptyInvoicesRendersNormalEmptyState`: Resolved by canonical invoice schema provisioning.
    6. `ControlPlaneOnboardingBehaviorTest::testCreationCommitsCompleteOnboardingWithoutTenantContext`: Added `$this->db?->resetTransStatus();` in `ControlPlaneClinicDetailTest::tearDown()` to eliminate connection state leakage across tests.
- **SaaS Guardrails Baseline Gate (Exit Code: 1 — FAILING GATE):**
  - `Group-based auth helpers`: 0 matches in snake_case scan (blind spot identified above).
  - Pre-existing failing matches (3 categories):
    - `DOM sinks in app/Views`: 6 matches (`contracts/create.php:1416`, `finance/partials/provider_split_section.php:475`, `patient/show.php:6597`, `providers/index.php:578,616`, `settings/partials/kiosk.php:147`).
    - `Raw tenant queries in controllers`: 12 matches (`Finance.php` [7], `Inventory.php` [1], `Reports.php` [4]).
    - `Forbidden UI font-family strings`: 4 matches (`clinic/select.php:17`, `auth/login.php:18`, `auth/forgot_password.php:18`, `treatment/show.php:74`).
  - *Gate Policy:* These pre-existing violations are documented baseline gates; they must not increase, and will be burned down in their respective stages (Stage 5, Stage 2, Stage 8).
- **Test Database Isolation & Tooling Safety (Corrections 01, 02 & 03):**
  - Automated provisioning and reset CLI established in `scripts/provision_test_db.php` using minimal pre-migration foundation `tests/_support/Database/LegacyBaseSchema.php` (18 tables, zero PHI, zero credentials, `CREATE TABLE IF NOT EXISTS`) and canonical migration replay via `MigrationRunner::latest()` (75 migrations recorded organically).
  - Preflight checks enforce fail-closed isolation via `scripts/TestDatabasePreflight.php`: requires exact dotted `database.tests.database`, local loopback host (`127.0.0.1`, `localhost`, `::1`), approved port (`3306`, `3307`), driver (`MySQLi`), and business disjointness before connecting. Remote targets, non-empty `failover`, `DSN`, `subdriver`, and business DB collisions are strictly refused without connecting.
  - Guarded reset and canary preservation: `_test_db_marker` tracks task ownership and enforces `$marker['target_db'] === 'democa_dental_saas_test'`; normal provisioning is non-destructive and verified to preserve synthetic canaries across repeat runs; explicit `--reset` validates ownership marker and refuses unowned non-empty databases.
  - Strictly read-only `--check-only` via `INFORMATION_SCHEMA.SCHEMATA` without creating absent targets; fresh provisioning ordered to provision before target connection in `safe_test_runner.php`. Shared production orchestration methods extracted in `TestDatabasePreflight.php` (`checkDatabaseExistsOnServer`, `orchestrateCheckOnly`, `orchestrateRunnerPreTest`) integrated into production CLI scripts and verified via unit tests with mock/spy collaborators (including demonstrated negative failure paths on reversal/mutation).
  - Harness stack unwinding guaranteed: resolved PHP `exit()` in `try` bypassing `finally` by implementing `VerificationFailureException` in `scripts/verify_isolation_and_canary.php`; verified via `--test-injected-failure-after-marker-drop` that `finally` executes, restores marker, and leaves target usable; early refusals (`--test-injected-failure`) remain mutation-free.
  - Zero skips: seeded owned synthetic fixtures for clinic, plan, user, patient, encrypted WhatsApp notification channel, and secrets; full suite passes with 313 tests, 1898 assertions, 0 errors, 0 failures, 0 skips. Business database `democa_dental_saas` observed counts recorded separately (67 tables, 7 patients, 9 appointments).
- **Build Baseline (Post-Change):**
  - Root Tailwind CSS: `npm run build` -> `public/assets/css/tailwind.css` 291,911 bytes (+111 bytes delta).
  - Frontend SPA: `npm --prefix frontend run build` -> monolithic `index-BLGBVVz9.js` (1,305.38 kB / 320.79 kB gzip), confirming bundle-split necessity.
- **Performance Surfaces & Missing Measurements:**
  - Local runtime lacks live production telemetry, automated browser performance harnesses, and real odontogram SVG render profiling. These metrics will be established during Stage 5 and Stage 6.
  - Composer audit limitation: tracked framework core under `system/` is not a Composer-installed package and is omitted from `composer audit`.

---

## 4. Approved 13-Stage Program Order & Requirements

### Stage 1: Remaining P0 Containment & CI Security Upgrade (Checkpoint 02)
- Update CodeIgniter core from 4.6.3 to current stable 4.7.x release preserving custom filters and bootstrap.
- Remove or hard-disable obsolete debug artifacts (`index_public.php`, `_debug_diff.txt`, `redirect.php`).
- Enforce private `/public` document root hosting where supported and verify root `.htaccess` deny rules.
- Audit public API (`/api/v1/*`) to guarantee zero reliance on web session state.
- Consolidate legacy IonAuth `isAdmin()` and `inGroup()` calls in controllers into `PermissionService` (RBAC), eliminating role/group-based authorization drift.
- Update `scripts/ci/saas_guardrails.sh`'s `run_group_guard` to scan for both camelCase (`isAdmin`, `inGroup`) and snake_case (`is_admin`, `in_group`) calls, closing the CI authorization blind spot.

### Stage 2: Financial Integrity, Idempotency & Settlement Boundary (Checkpoint 03)
- Implement a single authoritative settlement mutation boundary with checked transactions (`transDepth === 0`), deterministic parent row locks (`SELECT ... FOR UPDATE`), locked-state reconstruction, and validation.
- Enforce database-unique settlement idempotency/replay protection and payload mismatch rejection semantics.
- Enforce append-only ledger for payments, refunds, voids, write-offs, and adjustments with atomic charge line claims preventing duplicate billing.
- Adopt exact decimal or minor-unit arithmetic with explicit scale for ILS, USD, EUR, GBP (scale 2) and JOD (scale 3).
- Audit all float/round/format calculations across reports, contracts, treasury, and provider payouts.
- Define production, invoiced collections, refunds, expenses, accounts receivable/payable, and net cash once. Unapplied credits must be explicitly tracked without loss.

### Stage 3: Storage & Retention Correctness (Checkpoint 04)
- Distinguish active logical, retained/trash, derivative, and physical bytes; include retained physical bytes in clinic storage quotas.
- Ensure superseded clinical and financial files remain versioned as required.
- Enforce audited purge rules and retention classes; export pruning must only purge explicitly eligible data without soft-delete cleanup accidents.
- Ensure database migrations remain canonical; schema dumps are derived artifacts only.

### Stage 4: Media Processing Pipeline (Checkpoint 05)
- Preserve exact original diagnostic images, photos, scans, and PDFs with SHA-256 fingerprint, source MIME, size, and private storage path.
- Generate bounded derivatives (thumbnails ~480px, previews 1600-2048px) with aspect ratio preservation, no upscaling, EXIF reorientation, and WebP capability detection (falling back to JPEG/PNG).
- Validate dimensions, pixels, and memory bounds before decoding; derivative generation failure must never lose original sources.
- Introduce a tenant-owned attachment variants table with parent ownership and indexes.
- Provide authorized derivative routes with private caching, nosniff headers, and streaming.
- Implement an idempotent, resumable CLI backfill without permanent worker requirements.

### Stage 5: Zero-Visual-Change PHP Performance & Base64 Elimination (Checkpoint 06)
- Remove Patient profile Base64 imaging; serve images via authorized derivative routes.
- Implement stable asset versioning (`filemtime`) to replace `?v=<?= time() ?>`, restoring HTTP caching.
- Conditionally load or lazy-load heavy vendors (`Chart.js`, `FullCalendar`, `DataTables`, `Select2`) only on pages that use them.
- Replace document-wide `MutationObserver` with scoped form initialization.
- Implement visibility-aware, lightweight notification polling (`document.hidden` check, BroadcastChannel tab coordination).
- Implement canonical tenant-scoped paginated histories for patient examinations, appointments, treatments, finance, files, and contracts.
- Fix Patient list examination-count N+1 query using batch aggregation.
- Optimize login image (`login-background.jpg`, 6.2 MB) and odontogram SVG.
- Implement request-local RBAC/permission caching to eliminate redundant database permission lookups within the same HTTP lifecycle.
- Implement scoped reference-data file caching/invalidation for static system lists (treatments, insurance, ICD codes).
- Implement quick-create lazy mounting in command workspaces to eliminate hidden DOM overhead.
- Extract common head/footer asset bundles from `main_auth.php`.

### Stage 6: Testing & CI Consolidation (Checkpoint 07)
- Implement real DB concurrency tests for settlements, charge claims, and appointment booking against isolated test DB.
- Add isolation, rollback, file variant, retention, and currency precision tests.
- Consolidate CI pipeline: PHP syntax, PHPUnit, SaaS guardrails, migration sanity, and dependency audits.
- Create automated browser tests for core flows in both English and Arabic.

### Stage 7: Treatment Contract Signing Governance (Checkpoint 08)
- Implement a checked lock and signable-state transaction boundary (`transDepth === 0`, `SELECT ... FOR UPDATE` on contract row) preventing race conditions during concurrent signing attempts.
- Canonical all-agreement-content snapshot: capture all contract terms, selected teeth, planned procedure items, patient consent acknowledgments, and pricing into an immutable canonical JSON snapshot.
- Cryptographic SHA-256 fingerprinting of the canonical agreement snapshot to guarantee tamper-evident integrity.
- Signature uniqueness enforcement: prevent duplicate or replayed signatures across tenant contracts.
- Comprehensive audit trail recording signer identity, IP address, timestamp, user agent, and signature metadata.
- Immutable, versioned contract corrections: modifications to signed contracts require explicit amendment records with parent linkage, strictly prohibiting in-place mutation of finalized agreements.
- Staged artifact publication & recovery: robust synchronization across the MySQL and filesystem boundary (e.g. signed PDF generation, temporary spooling, atomic file commit, and crash recovery reconciliation).

### Stage 8: PHP Frontend Structural Decomposition & Workspace Command (Checkpoint 09)
- Decompose monolithic views (`patient/show.php`, `main_auth.php`, `finance/index.php`) into domain services and reusable UI primitives.
- Standardize semantic CSS tokens and reduce repeated inline styles without gratuitous redesign.
- Maintain full English/Arabic visual and RTL parity.

### Stage 9: React Progression & App API Alignment (Checkpoint 10)
- Resolve and verify the frontend repository identity (`Bassamalsaqqa/dentacare-shell-5051486e` vs `Bassamalsaqqa/Dentacare-Shell`).
- Implement route-level code splitting and lazy loading via dynamic imports (`React.lazy`), using React Query for server-state caching over canonical `/app-api/v1/*` contracts.
- Compile static assets to `public/app` with Apache fallback; hand-editing generated bundles is prohibited.
- Maintain strict functional, RBAC, RTL, and performance parity before deprecating legacy PHP surfaces.

### Stage 10: Practice Operations Workflows (Checkpoint 11)
- Implement operational recall/recare queues, no-show/cancellation recovery, accepted-unscheduled care tracking, and documentation queues.
- Deliver an actionable, role-sensitive clinic operations dashboard.

### Stage 11: Clinical Depth & Specialized Workflows (Checkpoint 12)
- Add periodontal charting, mixed dentition, referral tracking, longitudinal charts, and tooth/procedure-linked imaging.
- Support broader clinical document signing while maintaining separation between findings, procedures, consent, visits, and billing.

### Stage 12: Financial Maturity & Insurance Models (Checkpoint 13)
- Implement canonical payment-to-invoice line and provider share allocation.
- Support unapplied credits, prepayments, cash custody, bank deposits, and reconciliation.
- Introduce a dedicated dental insurance claim and adjustment model separate from generic invoice adjustments.

### Stage 13: Patient-Facing Portals & Digital Intake (Checkpoint 14)
- Introduce patient intake forms, self-service portal, online booking/check-in, digital statements, and governed messaging using canonical backend services.

---

## 5. Checkpoint 01 Acceptance Criteria & Verification

1. **Baseline Recorded:** Empirical evidence covering Git, toolchains, PHP modules, migrations, test suites, guardrails, and asset sizes written to `.ai/antigravity-runs/20260924-denta-plus-program-01/`, `.ai/antigravity-runs/20260924-denta-plus-program-01-correction-01/`, and `.ai/antigravity-runs/20260924-denta-plus-program-01-correction-02/`.
2. **Program Document Established:** `docs/refactor/DENTA_PLUS_HARDENING_PERFORMANCE_PROGRAM.md` maintained as the active plan, preserving all approved requirements, and linked in `docs/START_HERE.md`.
3. **P0 Tenant Backup Exposure Contained:**
   - Direct requests to `settings/create-backup`, `settings/download-backup/(:segment)`, `settings/restore`, and `settings/backup` hard-deny with 404 (`PageNotFoundException`).
   - `Settings::getBackups()` returns `[]` without scanning the filesystem.
   - `SettingsService::generateDatabaseBackup()` unconditionally throws `RuntimeException`.
   - Tenant settings UI contains zero backup creation forms, download buttons, or restore buttons.
   - Explanatory managed platform advisory displayed in settings with English and Arabic parity.
   - Existing backups on disk are left untouched; no files deleted or mutated.
   - Ordinary settings viewing and updates continue to operate normally.
4. **Behavioral Route Tests Passing:**
   - 13 comprehensive tests with 91 assertions verifying registered routes fail closed for unauthenticated requests, missing tenant context, global mode, cross-clinic isolation, and privileged tenant impersonation on the verified disposable test target.
   - Tests prove non-mutation of file contents (synthetic canary SHA-256 hash and byte length) and database rows.

### Resumable checkpoint after bounded history delivery

Draft PR #20 checkpoint `7865eabafdefadb6d8ce6013ee9ede8f7ea39c6a` was pushed; subsequent development adds bounded App API histories, registry aggregates, localized patient errors and compatible build-dependency patches. No final external review requested during the campaign. AGY hello succeeds, substantive requests timed out; a broader source review export was rejected by automatic approval review because the recorded disclosure authorization is M2-C only. Continue locally rather than bypassing that decision or stalling.

Remaining eligible code includes derivative concurrency/recovery acceptance, coherent legacy patient history loading and atomic cashier composition. Static non-odontogram patient workspace CSS is now a cacheable 73,189-byte asset; protected chart styles and scripts are unchanged by hash. Do not mark the campaign ready while those remain missing. Native Hostinger staging/capacity/recovery and product-policy gates remain separately identified above.
