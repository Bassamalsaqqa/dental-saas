# Project State

Last reconciled: 2026-11-09. Historical deployment and development entries remain available in Git history.

## Production authority

- Installed application: `1b17d06587a07a473f714e296af27f977134208d`, tree `257f1661717e898a430c5bec0d7128af1568ead1`. PR28 merged and deployed on 2026-11-08 under existing Owner authorization; maintenance is OFF. PR27 source 5b7c0688 is the immediate source-recovery baseline.
- Deployment branch and GitHub default: `release/production-20261017`. Historical `main` remains `485a19824d08e35fcd1a2a68923c63ec265867ca`; do not deploy historical main.
- PR22 schema/runtime history: PHP 8.3.36 / MariaDB 11.8.9. Exactly two approved migrations ran: `2026-10-29-120000_WidenTreatmentTypeStorage` and `2026-10-29-180000_CreateFinanceSettlementRequests`. History advanced 77 to 79; tables 67 to 68. No unrelated migration or dependency installation ran. PR24 introduces no migrations or dependency changes.
- PR22 historical recovery preserved original table fingerprints, protected configuration and durable files. Its encrypted off-host recovery restored 67 tables / 9,664 rows, 137 protected files and 1,882 previous-source files; an encrypted post-migration supplement preserves the new treatment provenance journals.
- Prior PR27 postflight: 24 no-follow HTTP GET/HEAD checks, 54 authenticated browser entries in EN/AR at 1366/768/390px, and six each salary, registration/search and stock-dialog open/cancel journeys passed. No page errors or viewport overflow observed; maintenance is OFF. No live financial/clinical submissions, migrations, dependency changes or optional activations occurred.
- Fresh encrypted checkpoint was authenticated/decrypted independently; isolated two-sided restore verified 68 tables/9,832 rows and 0 FK orphans. Under maintenance, SQL content (apart from dump timestamp) and 56 durable files matched that verified checkpoint before source installation. Recovery retains security deletions and protected overlays.
- Optional scheduling, WhatsApp, corrections, checkout, contact history, derivatives and tenant themes remain OFF. Existing contract/signature/attachment entitlements remain unchanged.
- Detailed recovery/cutover receipts are private operator evidence, not repository fixtures. Never commit backups, keys, credentials or customer records.

## Local and repository housekeeping

- The main checkout at `C:\xampp\htdocs\dental-saas` has been reconciled to the deployed release. September unfinished changes are preserved in a named stash and verified private source archives; they are not accepted release changes.
- Superseded PRs #4, #5, #6, #7, #8 and #12 were closed after proving their heads are release ancestors. Obsolete remote branches were removed; three unique historical tips are retained under `archive/cleanup-20261102/*` tags.
- All 13 obsolete registered worktrees and 14 stale local branches were retired after verified preservation. One authoritative checkout remains. Owner design kits/prompts, runtime dependencies, durable files and the isolated MariaDB data directory remain preserved. Cleanup archives, historical screenshots and Git bundles are outside the source tree under Owner-restricted private storage.
- The unused Hostinger `.denta-rehearsal` was retired on 2026-11-01 after encrypted off-host preservation. Additional obsolete synthetic staging clones were retired on 2026-11-02. Production checkpoints and current release tools remain available. Do not treat retired test domains as running rehearsals.
- PR23 preserved the prior documentation checkpoint. PR28 source 1b17d065 is merged and installed. Repository-only documentation checkpoints may follow without changing the installed application SHA; always distinguish those two identities.

## Remaining real limitations

- Developer-transfer cleanup removed 23,716 local files / 1,095,436,460 logical bytes by the reconciled receipt. Eight deleted release artifacts now have independently hash-verified off-host copies with hardened ACLs; the remaining 86 top-level artifacts lack proven exact remote preservation from that scan. Retain all remaining recovery material. Docker remains unavailable/uninspected. Codex completed the bounded ten-directory Hostinger staging retirement after authenticated off-host preservation: 2,061 unique inodes / 22,855,387 logical bytes removed, with production source and retained recovery records unchanged. General Hostinger cleanup is not authorized by that finite result. A complete reviewed customer-free application database bootstrap is still a new-machine onboarding gap; source setup and pure checks do not close it. See [developer onboarding](DEVELOPER_ONBOARDING.md) and WORK_PROGRAM for current boundaries.
- Patient workspace query count / latency remains a performance backlog item; no unbounded query refactor is authorized by cleanup.
- Unassessed / No / Yes medical-history provenance remains a separate clinical policy decision. Historical zero values must not be treated as assessed negative.
- Existing signed/legal print format is English-only. Full 400%/native zoom, physical assistive-technology / virtual-keyboard testing and full third-party API mutation recertification remain incomplete and were explicitly disclosed for release.
- On 2026-11-02, locked Composer audit reported zero advisories; production-only npm audit reported zero. Full npm audit reported three moderate build-tool dependency findings (`glob`, `minimatch`, transitive `sucrase`). They are not resolved by deleting branches, and dependencies must not be blindly upgraded or pinned CSS regenerated during housekeeping.
- GitHub security reconciliation on 2026-11-06 reports one medium development-scope `minimatch` alert (#33, package-lock.json); the earlier checkpoint referenced superseded alert #32. No alerts were manually dismissed and no dependency upgrade/build was run. Full npm findings include transitive build-tool effects and must not be confused with runtime vulnerabilities.
- One obsolete HTTPS negative-control folder in Windows Temp remains inaccessible under its existing ACL. It was retained because its contents/ownership could not be verified; no permissions were weakened to delete it.
- Local business MySQL on port 3306 was stopped during cleanup. No business database was started, migrated, restored or deleted. Source/dependency/static checks are distinct from a database-backed local workflow test.

## Future changes

The Owner's improvement campaign is tracked in [WORK_PROGRAM.md](WORK_PROGRAM.md): salary/provider/booking improvements are live through PR24; patient/clinical actions, registration search, responsive dialogs, financial clarity and safe tooling follow in [merged PR27](https://github.com/Bassamalsaqqa/dental-saas/pull/27). App-wide workflows, dialogs, responsive/device coverage and justified cleanup continue. The Owner provided an authenticated live review session and authorized sign-in when needed, preferring Chrome through Playwright.

Use the deployed source as the baseline. Preserve tenant/RBAC authority, signed evidence, original records, immutable financial history and durable files. Production deployments, migrations and feature activation require separate explicit Owner authorization. No GitHub Actions.

Module contracts remain linked from [START_HERE.md](START_HERE.md). Historic acceptance reports describe their recorded source/time, not automatic certification of future code.

## Current operational checkpoint

The three verified obsolete legacy files remain absent from public source and preserved in private quarantine. No-follow GET/HEAD 404 and login/assets 200 passed after reopening. Root/public `.htaccess`, environment, pinned CSS and durable storage remained intact. Live Git has only the pre-existing root rewrite overlay. Its local branch metadata still names historical main; never pull/reconfigure it as routine maintenance.

Fresh encrypted recovery integrity, isolated two-sided restore proof and source-only recovery retaining security deletions are recorded privately. Failed maintenance-entry checks restored the original front controller before installation; source installed only after gate validation. No production schema/data rollback or optional activation occurred. See WORK_PROGRAM for current evidence scope and remaining work; do not resume superseded readiness handbacks.

The two PR26/PR27 feature branches were retired after exact-tip ancestry checks proved preservation in the release branch. Post-release documentation commits may advance GitHub without changing installed application source 1b17d065.

PR28 postflight: 24 authenticated no-follow GET/HEAD checks under maintenance, 18 Chrome authenticated EN/AR entries at 1366/768/390px, six local-event pending/restoration checks and six dirty-cancel/keep journeys passed with no attempted POSTs, page errors or horizontal overflow. Public login/CSS/JS returned 200 and three quarantined legacy URLs returned 404 after reopening. Temporary browser verification access was removed; Arabic desktop restored. No current-day application log was created (latest log remains October 30); this is an absence of emitted entries, not proof of external logging coverage. Source installation saved 18 preimages. Fresh encrypted checkpoint denta-pr28-cutover-20261108T085105Z was independently authenticated and matched production SQL (apart from dump timestamp) and 56 durable files under maintenance before installation. No migrations, dependency changes, financial/clinical writes or feature activations occurred.

PR28 feature branch retirement: exact local and remote tip `1b17d065` is preserved in the merged release ancestry. Historical main and archive tags remain retained.
