# Developer Onboarding & Architecture Companion

<!-- proprietary-license-notice: Bassamalsaqqa -->
> **License:** Proprietary. Copyright (c) 2026 Bassamalsaqqa. All rights reserved. Third-party components retain their own licenses. See [LICENSE](../LICENSE) and [THIRD_PARTY_NOTICES.md](../THIRD_PARTY_NOTICES.md).

This guide is a practical companion to [`README.md`](../README.md), [`docs/START_HERE.md`](START_HERE.md), and [`docs/OPERATIONS_RUNBOOK.md`](OPERATIONS_RUNBOOK.md). It outlines stack boundaries, release truth, environment setup, test tiers, domain invariants, and operational procedures for engineers taking over development of DentaCare SaaS.

**Transfer checkpoint (2026-11-10):** The Owner ended development/design work in this environment and plans to continue in another developer environment/T3 later. Production remains PR28 at `1b17d065` (tree `257f1661`). The published `feature/developer-sandbox-bootstrap-20261110` branch contains unreleased developer tooling and this handoff; it has not been merged into the deployment branch or deployed. Inspect that branch explicitly when taking over. Transfer source through Git; provide environment secrets, recovery keys and any approved data separately through private channels. Do not copy browser profiles, customer dumps or ignored `writable/` wholesale into T3.
---

## 1. Stack & Architecture Boundaries

- **Backend:** CodeIgniter 4 (PHP 8.2+; production runs PHP 8.3.36).
- **Presentation:** Native server-rendered PHP views (`app/Views`) with shadcn-inspired Studio components ([`docs/UI_STUDIO_COMPONENTS.md`](UI_STUDIO_COMPONENTS.md)) and Tailwind CSS 3.
- **Enhanced UI Controls:** jQuery 3.7.1, Select2, and Font Awesome Free 6.4.0 vendored under `public/assets/vendor/fontawesome` (CDN loading prohibited by [DECISIONS.md](DECISIONS.md)).
- **Database:** Production was verified on MariaDB 11.8.9; isolated local regression used MariaDB 10.4.32. These checkpoints do not certify every database version.
- **Authentication & RBAC:** IonAuth handles session identity; `PermissionService` enforces tenant and platform permissions.
- **Session Management:** CodeIgniter FileHandler defaults to `writable/session/`, with cookie name `ci_session_saas`; verify runtime cookie settings and deployment overrides.
- **Pinned Stylesheet:** `public/assets/css/tailwind.css` is a reviewed, prebuilt artifact with exact SHA256:
  `3a51d2fffb54ae89fbb4188e3b1ddfc2bce09c7e81148adba78ce750b7fd801a`.
  Do not run routine asset builds; asset changes require temporary-directory verification.
- **Separate React/Lovable Frontend:**
  - Source repository: `Bassamalsaqqa/dentacare-shell-5051486e`.
  - When cloned locally into `frontend/`, it is git-ignored and is **not** a Git submodule or subtree. The parent repository tracks zero files beneath `frontend/`.
  - Build invariants: Vite `base: '/app/'`, `outDir: '../public/app'`, `emptyOutDir: true`, `BrowserRouter basename="/app"`, locked via `package-lock.json`.
  - API boundary: Communicates exclusively with `/app-api/v1/*` using session cookies and CSRF tokens. React code must never use Supabase, client auth tokens (localStorage/sessionStorage), or Bearer auth. See [`docs/refactor/LOVABLE_STRATEGY_CODEX.md`](refactor/LOVABLE_STRATEGY_CODEX.md).

---

## 2. Release Truth & Git Reference

- **Deployed Production Commit:** `1b17d06587a07a473f714e296af27f977134208d` (Tree SHA: `257f1661717e898a430c5bec0d7128af1568ead1`, PR #28 deployed 2026-11-08, maintenance OFF).
- **Immediate Source Rollback Baseline:** PR #27 at `5b7c0688` (Tree SHA: `b1ec2c3e3b3472c5b62d11734d94fa8e1dc6941a`).
- **Captured Repository Documentation Checkpoint (before this cleanup):** `83295f93d94dbd1902ba000fc037a9e9fb712446` on branch `release/production-20261017`.
- **Authoritative Branch:** `release/production-20261017` is the GitHub default and deployment ref.
- **Production Git Working Copy:** On Hostinger, the live checkout is named `main` tracking historical `origin/main`. Do not pull, rename, or reconfigure production Git refs during maintenance.
- Consult [`docs/PROJECT_STATE.md`](PROJECT_STATE.md) for authoritative release history and [`docs/WORK_PROGRAM.md`](WORK_PROGRAM.md) for active assignments.

---

## 3. Setup & Environment Configuration

### Prerequisites
- PHP 8.2+ with extensions: `intl`, `mbstring`, `mysqli`, `curl`, `json`, `gd`, `openssl`, `xml`, `zip`.
- Composer 2.2+.
- Node/npm compatible with the committed tooling lockfile; do not upgrade dependencies during setup.
- Git (Windows requires Git Bash at `C:/Program Files/Git/bin/bash.exe`).

### Setup Commands
```powershell
# 1. Clone repository on authoritative branch
git clone --branch release/production-20261017 https://github.com/Bassamalsaqqa/dental-saas.git
Set-Location dental-saas

# 2. Install locked PHP dependencies
composer install --no-interaction --no-scripts --no-plugins

# 3. Install locked root JavaScript tooling
npm.cmd ci --ignore-scripts --no-fund

# 4. Optional: Install locked frontend dependencies (if frontend sub-repo is cloned)
if (Test-Path "frontend/package.json") {
    Push-Location frontend
    npm.cmd ci --ignore-scripts --no-fund
    Pop-Location
}

# 5. Initialize environment configuration
if (-not (Test-Path -LiteralPath .env)) { Copy-Item -LiteralPath .env.example -Destination .env }
```

### Configuration Invariants
- Copy `.env.example` to `.env` **only** if `.env` does not already exist.
- `.env` holds private configuration: base URL, database connection, and encryption key (`app.encryption.key = hex2bin:...`).
- **No Blank-Password Defaults:** Authoritative connection settings must be explicitly configured for your local isolated database.
- Keep dormant features and outbound delivery disabled. Use the actual Config/clinic-setting contracts for each feature, not invented environment flag names; do not run message dispatchers during setup.

---

## 4. Test Tiers & Execution Rules

Tests are strictly divided into database-free pure checks and isolated synthetic suites.

### Tier 1: Pure Unit Tests & Source Guardrails (Database-Free)
Safe for routine execution without a running database:

```powershell
# Run pure database-free test suite (96 tests, 785 assertions)
npm.cmd test

# Run a single inspected database-free PHPUnit file
php vendor/phpunit/phpunit/phpunit --no-coverage --no-logging --do-not-cache-result tests/unit/BookingResourcePresenterTest.php

# Run SaaS guardrails (validates DOM sinks, group auth helpers, reviewed raw queries, fonts)
& "C:/Program Files/Git/bin/bash.exe" scripts/ci/saas_guardrails.sh

# Check for unapproved DOM sinks in views
rg -n "innerHTML|outerHTML|insertAdjacentHTML|\.html\(" app/Views

# Verify git whitespace formatting
git diff --check
```
*Note on Windows:* Use `npm.cmd test` directly in PowerShell because execution policies may block `npm.ps1`.
*Database Warning:* Do not run tests like `tests/unit/AppApiFoundationTest.php` in routine pure checks; their `setUp` initializes database connections. Only run files enumerated in `package.json` under `test:unit`.

### Tier 2: Isolated Synthetic SQL & HTTP Tests
- **Approved Target:** `scripts/TestDatabasePreflight.php` requires the exact schema `democa_dental_saas_test`, an approved loopback connection, disjoint business configuration and live ownership checks. Its implementation allows ports 3306 and 3307; this task authorizes only the isolated 3307 listener. Arbitrary schema prefixes fail preflight.
- **Port Rules:** Business MySQL on port 3306 must remain **OFF**. Synthetic test listeners run on loopback port 3307.
- **Lifecycle & Teardown:** Test runners must verify ownership, clean up synthetic fixtures, and shut down isolated test instances upon completion.

---

## 5. Local Synthetic Bootstrap & Known Gaps

`php scripts/DeveloperSandboxBootstrap.php --dry-run` remains an offline planning command. The feature branch also includes a confined worker/executor/connection bridge; execution requires a separately reviewed, explicit isolated-target authorization. Do not treat these tools as routine install steps.

- **Observed progress:** Fresh synthetic Trial #2 completed all eight worker stages and 77 native CI migrations. Its worker report processed 42 configured permissions and 8 configured roles; these are processing counts, not independently certified physical totals. AGY reports 69 tables and successful CHECK TABLE results. The final controller receipt is **FAIL**, because its verifier incorrectly requires every migration batch to equal 1; native per-file `force()` calls increment batches. No further database trial was run during transfer wrap-up.
- **Remaining verifier work:** Correct the batch assertion against actual CI history semantics; replace the undefined `$appTables` receipt variable; replace stale hardcoded 5-role/77-permission expectations with source-derived verification. Finish physical RBAC/settings/FK and actual service/route acceptance before claiming a fully validated fresh developer environment. No verified end-to-end bootstrap PASS or portable initialized database is supplied.
- **Target separation:** Developer schema `democa_dental_saas_dev` is distinct from automated test schema `democa_dental_saas_test`. Preserve both stopped trial datadirs and the customer recovery instance. Private operator controllers, credentials and receipts are ignored local evidence, not clone dependencies; obtain only specifically needed material through a private handoff.
- **Safety:** Keep business port3306 stopped. Use a genuinely new owned local3307 instance for any separately authorized future trial. Never run blanket `php spark migrate`, import customer recovery dumps as development fixtures, reset partial trials or activate dormant features to obtain a pass. Pure checks remain usable without a database.

---

## 6. Domain Authority & Security Invariants

- **Tenant Isolation:** Tenant operations must be scoped by server-owned `active_clinic_id`. Cross-tenant record lookups fail closed (HTTP 404/403). Consult [`docs/SaaS/TENANT_CONTEXT_CONTRACT.md`](SaaS/TENANT_CONTEXT_CONTRACT.md).
- **Control Plane Separation:** Control plane requires superadmin role and `global_mode = true`. Impersonating a clinic displays an unoverrideable security banner; exiting impersonation regenerates session state.
- **Public API (`/api/v1/*`):** Stateless Bearer token + `X-Clinic-Id` header. Excluded from CSRF. Never return HTML or redirects. See [`docs/API_CONTRACT.md`](API_CONTRACT.md).
- **First-Party App API (`/app-api/v1/*`):** Session cookie authentication + CSRF validation on all mutations. See [`docs/APP_API_CONTRACT.md`](APP_API_CONTRACT.md) and [`docs/refactor/APP_API_ROUTE_INVENTORY.md`](refactor/APP_API_ROUTE_INVENTORY.md).
- **Clinical & Financial Invariants:**
  - Canonical fact-owner chain: Examination -> Procedure -> Charge Line -> Invoice -> Payment Allocation -> Provider Payout -> COGS. See [`docs/refactor/CLINICAL_FINANCE_TRACEABILITY_PLAN.md`](refactor/CLINICAL_FINANCE_TRACEABILITY_PLAN.md).
  - Preserve signed clinical evidence, original receipts, journal history and authoritative calculations. Editing/correction rules differ by record type; use the clinical and finance contracts rather than treating all ordinary records as immutable.
  - Provider compensation allocations and supplier payables belong to distinct ledgers and must never be merged.
  - Guardrails allow 28 reviewed raw tenant queries in controllers; new raw queries require explicit review.
- **Dormant Features:** The deployed feature states are recorded in PROJECT_STATE.md. Dynamic scheduling, Theme Studio persistence and WhatsApp remain OFF; do not infer that delivery is mocked or safe to invoke.
- **Open Security Advisory:** Dependabot Alert #33 is `minimatch` (`GHSA-g3hv-75wg-m249`), confined to development tooling.

---

## 7. Release, Maintenance & Recovery Procedures

- **Release Deployment:** Applied via pinned `git merge --ff-only` under controlled maintenance mode. Protected overlays (`.env`, root `.htaccess`, `public/.htaccess`, `public/uploads/`, `writable/`) are preserved. Deployment does not swap application archives.
- **Schema Migrations:** Releases with schema migrations require their own reviewed migration procedure; do not assume all releases are source-only.
- **Recovery & Rollback:** A pinned fast-forward recovery commit restores the reviewed prior source tree while preserving overlays and customer data. Encrypted backups and isolated restore proofs provide separate operational recovery evidence; restoring a database is not part of routine source rollback. See [`docs/OPERATIONS_RUNBOOK.md`](OPERATIONS_RUNBOOK.md).

---

## 8. Ownership & Asset Handover

- **Access Delegation:** The Owner provides repository collaborator permissions and Hostinger hPanel/SSH access separately.
- **Zero Secrets in Git:** Keys, database passwords, session secrets, and customer data are never committed.
- **Private Recovery Materials:** Encrypted customer backups and recovery vaults (`DentaPrivateRecovery`) reside off-repository and are transferred exclusively through private, authenticated channels.

---

## 9. Key Documentation Map

| Need | Document |
|---|---|
| Master Document Index | [`docs/START_HERE.md`](START_HERE.md) |
| Active Release & Baseline Truth | [`docs/PROJECT_STATE.md`](PROJECT_STATE.md) |
| Current Task Register | [`docs/WORK_PROGRAM.md`](WORK_PROGRAM.md) |
| Durable Architectural Decisions | [`docs/DECISIONS.md`](DECISIONS.md) |
| Refactor & Modernization Architecture Index | [`docs/refactor/README.md`](refactor/README.md) |
| Clinical-Finance Traceability Plan | [`docs/refactor/CLINICAL_FINANCE_TRACEABILITY_PLAN.md`](refactor/CLINICAL_FINANCE_TRACEABILITY_PLAN.md) |
| Entity Workspace & Command Drawers Plan | [`docs/refactor/ENTITY_CENTERED_COMMAND_WORKSPACE_PLAN.md`](refactor/ENTITY_CENTERED_COMMAND_WORKSPACE_PLAN.md) |
| Studio Component System & Journey Contracts | [`docs/UI_STUDIO_COMPONENTS.md`](UI_STUDIO_COMPONENTS.md) |
| Operations & Runbook | [`docs/OPERATIONS_RUNBOOK.md`](OPERATIONS_RUNBOOK.md) |
| Public API Specification | [`docs/API_CONTRACT.md`](API_CONTRACT.md) |
| First-Party App API Specification | [`docs/APP_API_CONTRACT.md`](APP_API_CONTRACT.md) |
| Tenant Isolation & SaaS Contracts | [`docs/SaaS/TENANT_CONTEXT_CONTRACT.md`](SaaS/TENANT_CONTEXT_CONTRACT.md) |
| Historical Acceptance Evidence Index | [`docs/ui-evidence/product-ux/README.md`](ui-evidence/product-ux/README.md) |
