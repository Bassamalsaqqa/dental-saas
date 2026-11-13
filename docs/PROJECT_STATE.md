# Project State

Last reconciled: 2026-11-13. Historical deployment and development entries remain available in Git history.

## Production authority

- **Installed application:** `1b17d06587a07a473f714e296af27f977134208d`, tree `257f1661717e898a430c5bec0d7128af1568ead1`. PR28 merged and deployed on 2026-11-08 under existing Owner authorization; maintenance is OFF. PR27 source `5b7c0688` (tree `b1ec2c3e`) is the immediate source-recovery baseline.
- **Repository HEAD vs Installed Production:** Repository HEAD at audit baseline (2026-11-13) is `da31f1cf` on branch `release/production-20261017` (adds offline developer sandbox plan and fail-closed preflight checks). Repository-only documentation and tooling commits do not alter the installed application SHA; always distinguish these two identities.
- **Deployment branch & GitHub refs:** Deployment branch and GitHub default is `release/production-20261017`. Historical `main` remains preserved (`485a19824d08e35fcd1a2a68923c63ec265867ca`); do not deploy historical main. GitHub reports 0 open pull requests; `origin/feature/developer-sandbox-bootstrap-20261110` at `7427b4a4` has 4 unique commits (unmerged).
- **Production Hostinger checkout:** Live checkout is named `main` tracking historical `origin/main`, with deployed application matching tree `257f1661` plus the known root `.htaccess` overlay. Do not pull, rename, or reconfigure production Git refs during inspection.
- **PR22 schema/runtime history:** PHP 8.3.36 / MariaDB 11.8.9. Exactly two approved migrations ran: `2026-10-29-120000_WidenTreatmentTypeStorage` and `2026-10-29-180000_CreateFinanceSettlementRequests`. History advanced 77 to 79; tables 67 to 68. No unrelated migration or dependency installation ran. PR24 and PR28 introduced zero migrations or dependency changes.
- **PR28 postflight & recovery:** 24 authenticated no-follow GET/HEAD checks under maintenance, 18 Chrome authenticated EN/AR entries at 1366/768/390px, six local-event pending/restoration checks, and six dirty-cancel/keep journeys passed with no attempted POSTs, page errors, or horizontal overflow. Public login/CSS/JS returned 200; three quarantined legacy URLs returned 404. Fresh encrypted checkpoint `denta-pr28-cutover-20261108T085105Z` independently authenticated and matched production SQL (apart from dump timestamp) and 56 durable files. Fast-forward recovery commit `6cf44f52752804d58ec6af2378797bff4af98e73` restores the PR27 tree while preserving security deletions and overlays.
- **Dormant features:** Optional scheduling, WhatsApp, corrections, checkout, contact history, derivatives, and tenant themes remain OFF. Existing contract/signature/attachment entitlements remain unchanged.
- **Receipts:** Detailed recovery/cutover receipts are private operator evidence, not repository fixtures. Never commit backups, keys, credentials, or customer records.

## Local and repository housekeeping

- **Verified checkout:** Linux T3 development environment at `/home/t3dev/projects/dental-saas`. Checkout at audit baseline (2026-11-13) is clean ~76 MB total (~40 MB working tree, ~36 MB `.git`), with zero `vendor/` or `node_modules/` installed. Toolchains verified: PHP 8.4 CLI, Composer 2.8, Node.js 24, npm 10.9, Git, `gh`, Python 3. Docker remains offline/uninspected.
- **Database isolation & protection:** Local business MySQL on port 3306 is OFF by policy. Port 3307 prior recovery datadir is strictly protected, not categorically disposable. This machine must independently prove synthetic database isolation (dedicated disposable schema, loopback binding, verified ownership, clean teardown) before running database-backed tests.
- **Local cleanup completed:** Developer-transfer cleanup removed 23,716 local files / 1,095,436,460 logical bytes. 8 recovery copies independently hash-verified off-host in private vault (`reconciled-recovery-copies-20261109`); 86 loose files lack proven remote copies from that scan. Transient test HTML/pyc files removed from `writable/`. Detailed receipts preserved in Git history (`9d967df2`, `83295f93`).
- **Hostinger staging cleanup completed:** Codex completed the bounded ten-directory Hostinger staging retirement after authenticated off-host preservation: 2,061 unique inodes / 22,855,387 logical bytes removed under commit `78b444e4`, with production source and retained recovery records unchanged.
- **Branch housekeeping:** Superseded PRs #4-#8, #12, #26, #27, and #28 were closed/merged after proving heads are release ancestors. Unique historical tips are retained under `archive/cleanup-20261102/*` and `production-20261017-97cd12c` tags.

## Remaining real limitations

- **Retained operational evidence & advisories:**
  - Dev dependency advisory: GitHub Dependabot alert #33 (`minimatch` `GHSA-g3hv-75wg-m249` in `package-lock.json`) is confined to development tooling and remains open; locked dependencies and pinned CSS remain untouched.
  - Evidence governance: UI evidence in `docs/ui-evidence/` is retained as operational and audit verification.
  - Datadir protection: Port 3307 prior recovery datadir is protected, not categorically disposable; this machine must independently prove synthetic isolation before any database-backed execution.
- **Developer onboarding gap:** A complete, reviewed customer-free application database bootstrap remains an open onboarding gap. `php scripts/DeveloperSandboxBootstrap.php --dry-run` prints a source-derived proposal but refuses apply/reset/check-only; preflight helpers pass 125 pure tests / 860 assertions DB-free. Source setup and pure checks do not close the database bootstrap gap. See [developer onboarding](DEVELOPER_ONBOARDING.md) and WORK_PROGRAM for current boundaries.
- **Patient workspace query performance:** Patient workspace query count / latency remains a performance backlog item; no unbounded query refactor is authorized by cleanup.
- **Clinical policy provenance:** Unassessed / No / Yes medical-history provenance remains a separate clinical policy decision. Historical zero values must not be treated as assessed negative.
- **Format and accessibility boundaries:** Existing signed/legal print format is English-only. Full 400%/native zoom, physical assistive-technology / virtual-keyboard testing, and full third-party API mutation recertification remain access-limited.
- **Port 3306 OFF:** Local business MySQL on port 3306 was stopped during cleanup. No business database was started, migrated, restored, or deleted.

## Future changes & release authority

The Owner's improvement campaign is tracked in [WORK_PROGRAM.md](WORK_PROGRAM.md): incremental workflow simplification, responsive dialogs, financial clarity, and safe tooling. Full application rewrite or frontend rebuild is deferred.

- **Release authority:** Current Owner explicitly authorizes eventual commit/push/deploy; actual deployment execution remains bound to reviewed candidate and runbook preflight gates.
- **Production baselines:** Use deployed source `1b17d065` as baseline. Preserve tenant/RBAC authority, signed evidence, original records, immutable financial history, and durable files. Production deployments, migrations, and feature activations require separate explicit Owner authorization. No GitHub Actions.
- **Contracts:** Module contracts remain linked from [START_HERE.md](START_HERE.md). Historic acceptance reports describe their recorded source/time, not automatic certification of future code.
