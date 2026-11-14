# Project State

Last reconciled: 2026-11-14. Historical deployment and development entries remain available in Git history.

## Production authority

- **Installed application:** `6ddc1fa717af12a99af70b9e22ae9c85c5d51421`, tree `01131d8b805533ea5ce298252e5d11ba57476d02`. Deployed and active under Owner authorization; verified via independent SSH; maintenance is OFF. The immediate source-recovery baseline is `bc5dbaa6f67dd60f8a074496180a4421facd2ffb` (tree `8d9a18636a18f9634168f5ae100b3b8d8105a481`); older `1b17d06587a07a473f714e296af27f977134208d` remains deeper history.
- **Repository HEAD vs Installed Production:** Installed production runtime is `6ddc1fa717af12a99af70b9e22ae9c85c5d51421` (tree `01131d8b805533ea5ce298252e5d11ba57476d02`). Branch `release/production-20261017` includes subsequent clinical changes (`52c3dd4f` and corrections) which remain strictly local and undeployed until parent review and isolated clinical acceptance.
- **Deployment branch & GitHub refs:** Deployment branch and GitHub default is `release/production-20261017`. Historical `main` remains preserved (`485a19824d08e35fcd1a2a68923c63ec265867ca`); do not deploy historical main. Feature branch `origin/feature/developer-sandbox-bootstrap-20261110` tip `7427b4a4` reconciled into release branch; exact feature commit ancestry preserved.
- **Production Hostinger checkout:** Live checkout is named `main` tracking historical `origin/main`, with deployed application matching tree `01131d8b805533ea5ce298252e5d11ba57476d02` plus the known root `.htaccess` overlay. Front controller `public/index.php` (hash `7c980a040ddb863d402babf18e2e45eb27ca715fb42d41be8239c3d0837a8f`) restored at mode 644. Do not pull, rename, or reconfigure production Git refs during inspection.
- **Schema & migration history:** PHP 8.3.36 / MariaDB 11.8.9. Exactly 79 migrations applied, 68 tables. 0 migrations introduced in candidate cutover; 0 feature activations.
- **Release 6ddc1fa7 cutover & acceptance:** Today reception release deployed exactly 28 paths (14 modified, 6 added, 8 deleted from Git; no fictional filters/models/currency changes). Verified via independent SSH. Acceptance receipt recorded at private `/home/t3dev/.reception-acceptance-receipts/release_acceptance_6ddc1fa7.json` (mode 600, no secrets); zero production business mutations or migrations. Maintenance is OFF; ops closed tabs/ports inactive.
- **Dormant features:** Optional scheduling, WhatsApp, corrections, checkout, contact history, derivatives, and tenant themes remain OFF. Existing contract/signature/attachment entitlements remain unchanged.
- **Receipts:** Detailed recovery/cutover receipts are private operator evidence, not repository fixtures. Never commit backups, keys, credentials, or customer records.

## Local and repository housekeeping

- **Verified checkout:** Linux T3 development environment at `/home/t3dev/projects/dental-saas`. Clean working tree, verified toolchains: PHP 8.4 CLI, Composer 2.8, Node.js 24, npm 10.9, Git, `gh`, Python 3. Docker CLI present; Docker daemon currently offline.
- **Database isolation & protection:** Local business MySQL on port 3306 is OFF by policy. Port 3307 prior recovery datadir is strictly protected, not categorically disposable. This machine must independently prove synthetic database isolation (dedicated disposable schema, loopback binding, verified ownership, clean teardown) before running database-backed tests.
- **Local cleanup completed:** Developer-transfer cleanup removed 23,716 local files / 1,095,436,460 logical bytes. 8 recovery copies independently hash-verified off-host in private vault (`reconciled-recovery-copies-20261109`); 86 loose files lack proven remote copies from that scan. Transient test HTML/pyc files removed from `writable/`. Detailed receipts preserved in Git history (`9d967df2`, `83295f93`).
- **Hostinger staging cleanup completed:** Codex completed the bounded ten-directory Hostinger staging retirement after authenticated off-host preservation: 2,061 unique inodes / 22,855,387 logical bytes removed under commit `78b444e4`, with production source and retained recovery records unchanged.
- **Branch housekeeping:** Superseded PRs #4-#8, #12, #26, #27, and #28 were closed/merged after proving heads are release ancestors. Unique historical tips are retained under `archive/cleanup-20261102/*` and `production-20261017-97cd12c` tags.

## Remaining real limitations

- **Developer-transfer cleanup & Docker disposition:**
  - Developer-transfer cleanup removed 23,716 local files / 1,095,436,460 logical bytes by reconciled receipt. 8 deleted release artifacts have independently hash-verified off-host copies with hardened ACLs; 86 loose artifacts lack proven remote copies from that scan.
  - Docker was inspected (2026-11-10): four verified unused official runtime images were removed without force; image storage reported 3.338GB before / 1.122GB after. All 37 volumes (6.646GB), stopped Gaza recovery container and mysql:8.4 image, and shared build cache (1.412GB) were preserved. Docker daemon currently offline/unstarted on Linux host.
- **Retained operational evidence & advisories:**
  - Dev dependency advisory: GitHub Dependabot alert #33 (`minimatch` `GHSA-g3hv-75wg-m249` in `package-lock.json`) is confined to development tooling and remains open; locked dependencies and pinned CSS (`3a51d2ff...`) remain untouched.
  - Evidence governance: UI evidence in `docs/ui-evidence/` is retained as operational and audit verification.
  - Datadir protection: Port 3307 prior recovery datadir is protected, not categorically disposable. Test harness MariaDB on loopback port 3307 and synthetic PHP on port 38747 were gracefully stopped after verifying PIDs; ports 3307 and 38747 are free. Port 3306 OFF by policy.
- **Synthetic bootstrap trial & verification status:**
  - Clean-slate developer bootstrap trial on 127.0.0.1:3307 verified 69 tables (18 foundation + 50 migrated + 1 marker), 77 applied migrations (out of 85 repository migration classes), and dynamic RBAC checks with 0 mutations to repo `writable/`. Relational metadata check: 26 physical FK rows inventoried; 11 specific orphan relational queries checked (0 orphans across those 11 checked pairs; non-exhaustive constraint comparison). Stopped dev harness (360MB) retained on disk.
  - Pure test suite passes 210 tests / 1531 assertions in `npm test` (including 74 preflight tests / 553 assertions, 40 trial controller verifier tests / 166 assertions); targeted JS tests (`StudioNativeCreatePresetTest`, `FinancePaymentEntryTest`) pass; 13 routed HTTP tests / 158 assertions pass in `scripts/safe_test_runner.php`. Release `bc5dbaa6` cutover completed and parent verified; maintenance released; ports closed; Issue #29 remains OPEN for broader Today/encounter workflow milestones. See [`docs/WORK_PROGRAM.md`](WORK_PROGRAM.md).
- **Patient workspace query performance:** Patient workspace query count / latency remains a performance backlog item; no unbounded query refactor is authorized by cleanup.
- **Clinical policy provenance:** Unassessed / No / Yes medical-history provenance remains a separate clinical policy decision. Historical zero values must not be treated as assessed negative.
- **Format and accessibility boundaries:** Existing signed/legal print format is English-only. Full 400%/native zoom, physical assistive-technology / virtual-keyboard testing, and full third-party API mutation recertification remain access-limited.
- **Port 3306 OFF:** Local business MySQL on port 3306 was stopped during cleanup. No business database was started, migrated, restored, or deleted.

## Future changes & release authority

The Owner explicitly authorized resolving unfinished features and reconciling `origin/feature/developer-sandbox-bootstrap-20261110` tip `7427b4a4` into the release branch. Exact commit ancestry is preserved so subsequent branch retirement can prove preservation. The Owner's improvement campaign continues incremental workflow simplification, responsive dialogs, financial clarity, and safe tooling. Full application rewrite or frontend rebuild is deferred.

- **Release authority:** Current Owner explicitly authorizes eventual commit/push/deploy; actual deployment execution remains bound to reviewed candidate and runbook preflight gates.
- **Production baselines:** Deployed production source is `6ddc1fa717af12a99af70b9e22ae9c85c5d51421` (tree `01131d8b805533ea5ce298252e5d11ba57476d02`); immediate source recovery baseline is `bc5dbaa6f67dd60f8a074496180a4421facd2ffb` (tree `8d9a18636a18f9634168f5ae100b3b8d8105a481`). Clinical handoff changes (`52c3dd4f` + corrections) remain strictly local and undeployed. Preserve tenant/RBAC authority, signed evidence, original records, immutable financial history, and durable files. Production deployments, migrations, and feature activations require separate explicit Owner authorization. No GitHub Actions.
- **Contracts:** Module contracts remain linked from [START_HERE.md](START_HERE.md). Historic acceptance reports describe their recorded source/time, not automatic certification of future code.
