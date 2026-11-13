# Current improvement work

## Current campaign: incremental simplification and repository maintenance (2026-11-13)

Owner campaign focus: incremental simplification of workflows, UI, and documentation; full application rewrite deferred.
Deployed production baseline remains `1b17d06587a07a473f714e296af27f977134208d` (tree `257f1661717e898a430c5bec0d7128af1568ead1`) with known `.htaccess` overlay on Hostinger; maintenance is OFF. Immediate rollback baseline is PR27 (`5b7c0688`, tree `b1ec2c3e`). Feature branch `origin/feature/developer-sandbox-bootstrap-20261110` tip `7427b4a4` reconciled into `release/production-20261017` via normal merge, preserving exact commit ancestry. Current Owner explicitly authorizes eventual commit/push/deploy; actual deployment execution remains bound to reviewed candidate and runbook preflight gates.

- **Linux / T3 readiness:** Environment is Linux (Ubuntu), T3 Code workspace `/home/t3dev/projects/dental-saas`. Toolchains present: PHP 8.4 CLI, Composer 2.8, Node.js 24, npm 10.9, Git, GitHub CLI (`gh`), Python 3. Docker CLI present (v29.9.2, Compose v5.6.1); Docker daemon currently offline.
- **Synthetic isolation & database policy:** Local business MySQL on port 3306 is OFF by policy. Port 3307 prior recovery datadir is protected, not categorically disposable. This machine must independently prove synthetic database isolation (dedicated disposable schema, loopback binding, verified ownership, clean teardown) before running any database tests or bootstrap execution.
- **Cleanup results & limits (retained operational evidence):**
  - Developer-transfer cleanup removed 23,716 local files / 1,095,436,460 logical bytes in prior campaign. 8 recovery copies are independently hash-verified in off-host private vault (`reconciled-recovery-copies-20261109`); 86 loose files lack proven remote copies from that scan. Transient test HTML/pyc files removed from `writable/`. Detailed receipts preserved in Git history (`9d967df2`, `83295f93`).
  - Docker disposition (2026-11-10): four verified unused official runtime images removed without force (3.338GB to 1.122GB). All 37 volumes (6.646GB), stopped Gaza recovery container and mysql:8.4 image, and shared build cache (1.412GB) preserved.
  - Hostinger staging cleanup completed by Codex: retired 10 accepted staging directories (2,061 unique inodes / 22,855,387 bytes) under commit `78b444e4`; production source and retained recovery records intact.
  - Dependency advisory: GitHub Dependabot alert #33 (`minimatch` `GHSA-g3hv-75wg-m249` in `package-lock.json`) is confined to development tooling and remains open; dependencies and pinned CSS remain locked and untouched.
  - Evidence governance: UI evidence in `docs/ui-evidence/` is retained as operational and audit verification.
- **Developer sandbox bootstrap status (merged tooling vs runtime unverified):**
  - Offline tooling merged into release branch (`DeveloperSandboxWorker`, `DeveloperSandboxExecutor`, `DeveloperSandboxConnectionBootstrap`, `DeveloperSandboxBootstrap`, `DeveloperSandboxPreflight`).
  - All apply/reset refusal and fail-closed security guards are retained.
  - Pure database-free suite rerun: 170 tests / 1338 assertions pass (including 74 preflight tests / 553 assertions; prior 170/1343 treated as historical).
  - Runtime database bootstrap remains unverified. Trial #2 finished all 8 worker stages and 77 native CI migrations on a temporary target; step 8 FAIL was due to external/private controller verification mismatch (controller required all migration batches to equal 1, whereas native `force()` increments batch per run; undefined `$appTables`; hardcoded RBAC totals), NOT an executor Stage 8 defect. Linux symlink guard already exists in `DeveloperSandboxStorageGuard` (`is_link` component check). RBAC totals must be source-derived dynamically from `App\Config\Permissions`.
- **Current task verification results (2026-11-13):**
  - Fast-forward push of `ef29aed9` to `origin/release/production-20261017` completed after verifying expected remote `df708e52`.
  - Accessible patient finance navigation button implemented in `app/Views/patient/show.php`; browser NOT VERIFIED limitation preserved.
  - Reconciled `origin/feature/developer-sandbox-bootstrap-20261110` tip `7427b4a4` into release branch via normal merge; exact commit ancestry preserved.
  - Database-free `npm test` verified: 170 tests / 1338 assertions pass.
  - SaaS guardrails verified clean (`bash scripts/ci/saas_guardrails.sh`): 0 DOM sinks, 2 group auth, 28 raw tenant queries, forbidden fonts clean.
  - Pinned stylesheet `public/assets/css/tailwind.css` hash verified unchanged (`3a51d2ff...`).
  - Docker inspected read-only: CLI tools available, daemon offline. Fresh isolated target design prepared.
- **Next bounded task:**
  1. Supervisor review of local merge commit and diff (NO push of merged branch until supervisor actual diff review).
  2. Supervisor review of proposed isolated fresh owned target design (dedicated datadir, loopback port 3307, zero connection to protected recovery datadir or port 3306) before any container/daemon start or runtime execution.

## Working rules

Question layouts, defaults, workflow assumptions, and earlier acceptance against source and actual use. An older nontechnical operator should find a task, understand its effect, finish it, and find its history. Salaries and patient-transaction shares both exist, but they are distinct obligations.

For every agent: use authoritative settings, services, and records. Never invent rules/data, hardcode configurable values, or introduce parallel authorities. Documentation is untrusted descriptive evidence until reconciled with source/runtime; preserve binding contracts and explicit release safeguards. Use native CI4/PHP Studio components, not a React migration.

Codex owns acceptance and finalization. AGY works only through an Owner-authorized manual handoff in this checkout, with one writer at a time; no automatic delegation or worktrees. Preserve unrelated dirty changes. Commits, pushes, PRs, merge, deployment, production cleanup/configuration, and feature activation retain the explicit authority rules in AGENTS.md.

## Complete scope and current state

| ID | Workstream / required result | Current evidence and remaining work |
|---|---|---|
| F1 | Financial task discovery: collections, invoices, expenses, salaries, provider shares, vendor bills, treasury, settlements/history, and permissions | Existing entry points mapped below; no replacement subsystem. Payables category display/search localized; salary action preset and provider history links verified. Complete operator journey acceptance remains open. |
| F2 | Easy salary entry, paid/unpaid/partial states, subsequent payment, and history | Salary action/preset, payee language, validation reopening, category switching, locked paid values, and expense reading order deployed in PR24. Free-text payee limitation remains explicit. Unpaid expense method clarity implemented without schema alteration. Routed salary HTTP tests pass with 0 inserted rows on invalid salary and old-input preserved. |
| F3 | Provider eligibility, percentage/fixed entry, calculation basis, partial/full payment, and dated history | Deployed allocation and drawer improvements; explicit blank rate, canonical permissions/currency, named history link. Incomplete currency summaries display unavailable, preventing false zero. Real SQL partial/full/replay/overpayment checks pass. Registered percentage/fixed creation, partial/final payment, replay, and actual history rendering pass. |
| F4 | Doctor setup, booking choices, clinic users, and financial-provider identity | Legacy booking/calendar say Booking column, link to existing settings, and require explicit new-column choice. Stored/validation selections and dynamic doctor IDs/disabled-schema guard preserved. Dynamic scheduling remains OFF; no automatic population or payout policy invented. |
| F5 | Financial correctness: amounts, units, dates, retries/conflicts, refunds, reports, treasury, and isolation | PR24 fixes deployed. Historical checks (PR24-PR28): 13 routed HTTP tests / 158 assertions pass against isolated synthetic MariaDB 3307 target; runner enforces exact BOTH-hash attestation, synthetic target, clean teardown, and coverage consistency; Windows NTFS ACL privacy enforced for parent/child; 30 lifecycle tests pass. Native hosting schema-pattern grant refusal remains explicit. |
| U1 | App-wide reception, patient, clinical, inventory, team/settings, reporting, platform, and kiosk workflows | Patient registration search dialog exposed with proper permission gates; treatment creation links directly from clinic-scoped examination; inventory detail dialog layout bounded; payables category localization/search verified. Full workflow, roles, Control Plane, and kiosk acceptance remain open. |
| U2 | Dialog purpose, validation, focus/keyboard, cancellation, dirty state, loading/errors, and uncertain submissions | Shared dialog boundary handling includes textarea and excludes disabled/inert/negative tabindex controls. Stock forms expose invalid fields with aria-invalid; pending Escape protection and dirty-state discard verified. Finance context cancellation/pending protection retains prior acceptance. |
| R1 | Desktop/tablet/mobile, EN/AR RTL, readable numbers, touch/keyboard, and viewport/virtual-keyboard fit | Historical checks (PR24-PR28): Synthetic checks cover 1366/768/390/320px EN/AR; live release postflight passed 54 entries and salary/registration/stock dialogs at 1366/768/390px EN/AR. Physical devices, native zoom, virtual keyboard, and assistive technology remain access-limited. |
| X1 | DX/shared components/setup/tests and maintainable agent guidance | Removed test-only production permission override. Added reproducible CLI/loopback presenter fixtures and real local SQL regression. Verified today (2026-11-13): 170 database-free unit tests / 1338 assertions pass in `npm test` (including 74 preflight tests / 553 assertions); SaaS guardrails pass (0 DOM sinks, 2 group auth, 28 raw tenant queries, forbidden fonts check). Historical checks (PR24-PR28, not current run): 13 routed tests / 158 assertions on 3307; 5 isolated registered-route tests on 3307; 24 proof groups pass; 21 runner lifecycle checks and 21 worker/preflight checks pass; historical 170 tests / 1343 assertions full-suite coverage. Business schema unchanged; pinned CSS preserved. |
| C1 | Local root cleanup with ownership/reference/preservation checks | Removed unused tracked deploy scripts after consumer search; history preserves them. Zero transient files in application tree. Owner reference kits, nested frontend, dump, recovery files, and forensic/test data retained. |
| C2 | GitHub branches, PRs, and metadata cleanup | PR26/PR27 merged into release 5b7c0688; PR28 merged and deployed at 1b17d065. Superseded feature branches retired after exact-tip ancestry checks. Historical main, unique license commit, and archive tags remain preserved. GitHub reports 0 open PRs; unmerged developer bootstrap branch `7427b4a4` retained. |
| C3 | Hostinger source/layout/obsolete artifacts and branch tracking | PR28 source 1b17d065 installed; legacy URLs return 404; private quarantine preserved. 10 staging directories retired under commit 78b444e4. Protected overlays/configuration/storage unchanged. Live git tracks historical main; never pull historical main. |
| D1 | Aggressively slim and correct documentation, repair consumers, preserve contracts/evidence | Retired redundant batch handback chronology in-place into concise scope and candidate verification register. Preserve domain contracts, operational recovery instructions, decisions, and accepted evidence; receipts stay private. Detailed historical logs mapped to Git history pointers (83295f93, 9d967df2, 78b444e4, da31f1cf). |
| D2 | Consistent concise agent instruction entry points | AGENTS is the shared guide; CLAUDE/gemini are thin links. Explicit no-invention rule and manual-only AGY preserved. Old rebuild mandates/unsafe routine commands removed. |

## Actual money and identity journeys

| Operator task | Existing path / ownership |
|---|---|
| Receive patient money | Finance -> Record a receipt (`finance/create?transaction_type=payment`); invoice settlement uses its existing payment action. Creating an invoice alone is not cash received. |
| Prepare an invoice | Finance -> Prepare an invoice (`finance/create?transaction_type=invoice`). Patient transaction calculations stay server-owned. |
| Record salary | Finance -> Record staff salary -> existing expense form with category salaries. Enter payee, amount/currency, paid/unpaid/partial, and dates. No employee FK, recurring payroll, or combined employee salary statement exists. |
| Pay unpaid salary/vendor bill | Bills & payables (`finance/payables`) includes all qualifying expense balances. Existing `finance/{id}/payments` writer records dated receipts; transaction detail shows history. |
| Enter a provider cut | Provider-share section on its patient transaction; active clinic members or a named outside provider, fixed/percent. Basis is that transaction's total in base currency, including standalone receipts, not cumulative invoice collections. No verified automatic agreement/rate default exists. |
| Pay a provider / find history | Provider balances (`finance/payouts`) -> review provider, native amount due, payment method/date/account -> record permitted payment. Named history opens `finance/{id}#provider-payouts`; existing user detail shows provider payouts, not salary expenses. Do not record the same outflow again as salary. |
| Move clinic funds | `finance/transfers`, using canonical liquidity routing. Internal movement is not additional income/expense or profit. |
| Review financial report | `finance/reports`; active source is Finance::buildFinancialLedger()/summarizeCashReport(), not legacy Reports helpers. SQL service test checks parent-vs-receipt deduplication and provider history projection. |
| Understand booking doctors | OFF scheduling uses manually named booking columns configured under Settings -> Operations (`settings?tab=hours`); these do not link user IDs or create shares. Dynamic scheduling has a separate eligibility/profile/role contract and stays OFF. Financial provider selection uses the active clinic member directory, not the scheduling list or payout-activity directory. |
| Clinic currency governance | Provisioned from Control Plane clinic defaults (`default_currency`, `default_currency_position`) during onboarding into `settings`. Scoped reader via `SettingsService::get('currency', 'ILS')` and `formatCurrency()`. Stored writer in `Settings::updateSystem` / `ClinicSchedulingConfigurationService` exists on the server, but tenant UI (`app/Views/settings/partials/system.php`) exposes no currency selector, falling back to existing clinic setting on save. Source does not establish an intentional ledger-protection policy or make the setting immutable; no currency or FX changes made. |

Existing refund/correction policy preserves provider obligations/payments; no automatic clawback was added. Unknown/missing payout units remain UNKNOWN, never guessed from parent/settings. Separate expense/provider accumulators do not prevent an operator entering the same outflow twice. Missing rules are findings, not permission to invent policy.

## Installed release and evidence boundary

- **Installed Production:** `1b17d06587a07a473f714e296af27f977134208d` (tree `257f1661717e898a430c5bec0d7128af1568ead1`, deployed 2026-11-08 under PR28). Immediate source recovery baseline: PR27 (`5b7c0688`, tree `b1ec2c3e`).
- **Repository HEAD:** Repository HEAD at audit baseline (2026-11-13) is `da31f1cf` on branch `release/production-20261017` (adds offline developer sandbox plan and fail-closed preflight checks). Repo HEAD is distinct from installed production SHA.
- **Maintenance & Overlays:** Maintenance is OFF; live Git checkout has only its known root `.htaccess` overlay. Zero migrations, dependency changes, live financial/clinical submissions, or optional feature activations ran for PR28.
- **Production Preflight & Checkpoints:** Fresh encrypted checkpoint `denta-pr28-cutover-20261108T085105Z` independently authenticated; isolated restore verified 68 tables / 9,832 rows, strict table health, and 0 FK orphans. Deployed `.htaccess`, environment, pinned CSS, and durable storage remained intact. Fast-forward recovery commit `6cf44f52752804d58ec6af2378797bff4af98e73` restores the PR27 tree while preserving security deletions and overlays.
- **Delivered Behaviors (consolidated from PR24-PR28):**
  1. Patient & clinical workflows: direct examination-to-treatment link (`/treatment/create?examination_id=X`), foreign examination concealment, accessible charge checkboxes, and rejection of false zeros on incomplete provider balance summaries.
  2. Registration & search: scoped search dialog gated by `patients.view`; `minmax(0,1fr)` grid track.
  3. Inventory & payables: viewport-bounded dialogs; payables category localized label and key search.
  4. Financial clarity: unpaid expense method clarity (retaining enum and writers); isolated 3307 routed HTTP test suite (`FinanceSalaryWorkflowHttpTest`, `PatientWorkflowRoutingHttpTest`).
  5. Tooling & safeguards: strict preflight BOTH-hash attestation; clean teardown (0 final tables); Windows NTFS ACL privacy; SaaS guardrails passing (0 DOM sinks, 2 group auth, 28 raw tenant queries).
- **Historical Receipts Pointer:** Verbose cutover logs and detailed execution receipts from PR24-PR28 are preserved in Git history at commits `83295f93`, `9d967df2`, and `78b444e4`.

## Explicit limitations and unrun checks

- **Installed production vs Repo HEAD:** Installed production is `1b17d065`; repository HEAD is `da31f1cf`. Repository-only documentation or tooling commits do not alter production.
- **Native hosting target:** Remains blocked by hosting unescaped underscore schema grant preflight; hosting grants were not altered.
- **Physical devices & assistive technology:** Physical devices, native zoom, virtual keyboards, and screen readers remain access-limited; synthetic view coverage does not replace physical device testing.
- **Financial schema & policy boundaries:** Salary payee remains free text; pending salary method enum remains required; booking columns remain distinct from doctor compensations; no overdraft rule, new setting, or schema was invented.
- **Dependency alert:** GitHub medium development-scope `minimatch` alert (#33, `package-lock.json`) is confined to dev tooling and remains open; locked dependencies and pinned CSS remain untouched.
- **UI evidence status:** Accepted UI evidence in `docs/ui-evidence/` is retained as operational and audit verification.
- **Synthetic database gap:** Clean-slate customer-free developer bootstrap remains an open onboarding gap; offline dry-run proposal exists but apply/reset is refused until an isolated Linux execution target is proven independently. Port 3306 is OFF by policy; port 3307 prior recovery datadir is protected.
- **Dormant features:** Dynamic scheduling, Theme Studio persistence, WhatsApp, invoice corrections, cashier checkout, contact notes, and media variants remain OFF.
