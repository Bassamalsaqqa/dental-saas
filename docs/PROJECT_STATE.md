# Project State

Last reconciled: 2026-11-06. Historical deployment and development entries remain available in Git history.

## Production authority

- Installed application: `6505bac31dbc9a1869139ff14827456353b0c6a2`, tree `77e1d0b7502a3b03edd4ef9bd71fb81defa351a6`. PR #24 merged and deployed on 2026-11-06 with Owner authorization, protected source recovery and authenticated postflight. The prior PR22 release 9e1f8558 remains historical recovery provenance.
- Deployment branch and GitHub default: `release/production-20261017`. Historical `main` remains `485a19824d08e35fcd1a2a68923c63ec265867ca`; do not deploy historical main.
- PR22 schema/runtime history: PHP 8.3.36 / MariaDB 11.8.9. Exactly two approved migrations ran: `2026-10-29-120000_WidenTreatmentTypeStorage` and `2026-10-29-180000_CreateFinanceSettlementRequests`. History advanced 77 to 79; tables 67 to 68. No unrelated migration or dependency installation ran. PR24 introduces no migrations or dependency changes.
- PR22 historical recovery preserved original table fingerprints, protected configuration and durable files. Its encrypted off-host recovery restored 67 tables / 9,664 rows, 137 protected files and 1,882 previous-source files; an encrypted post-migration supplement preserves the new treatment provenance journals.
- PR24 postflight:24 mandatory HTTP GET/HEAD checks,24 Chrome/Playwright entry checks across EN/AR desktop/tablet/mobile, and six salary-dialog opening/cancellation checks. No observed document overflow/page errors; maintenance is OFF. This does not certify live customer mutations, every role or every workflow.
- Optional scheduling, WhatsApp, corrections, checkout, contact history, derivatives and tenant themes remain OFF. Existing contract/signature/attachment entitlements remain unchanged.
- Detailed recovery/cutover receipts are private operator evidence, not repository fixtures. Never commit backups, keys, credentials or customer records.

## Local and repository housekeeping

- The main checkout at `C:\xampp\htdocs\dental-saas` has been reconciled to the deployed release. September unfinished changes are preserved in a named stash and verified private source archives; they are not accepted release changes.
- Superseded PRs #4, #5, #6, #7, #8 and #12 were closed after proving their heads are release ancestors. Obsolete remote branches were removed; three unique historical tips are retained under `archive/cleanup-20261102/*` tags.
- All 13 obsolete registered worktrees and 14 stale local branches were retired after verified preservation. One authoritative checkout remains. Owner design kits/prompts, runtime dependencies, durable files and the isolated MariaDB data directory remain preserved. Cleanup archives, historical screenshots and Git bundles are outside the source tree under Owner-restricted private storage.
- The unused Hostinger `.denta-rehearsal` was retired on 2026-11-01 after encrypted off-host preservation. Additional obsolete synthetic staging clones were retired on 2026-11-02. Production checkpoints and current release tools remain available. Do not treat retired test domains as running rehearsals.
- PR23 preserved the prior documentation checkpoint. PR24 source 6505bac3 is now merged and installed. Repository-only documentation checkpoints may follow without changing the installed application SHA; always distinguish those two identities.

## Remaining real limitations

- Patient workspace query count / latency remains a performance backlog item; no unbounded query refactor is authorized by cleanup.
- Unassessed / No / Yes medical-history provenance remains a separate clinical policy decision. Historical zero values must not be treated as assessed negative.
- Existing signed/legal print format is English-only. Full 400%/native zoom, physical assistive-technology / virtual-keyboard testing and full third-party API mutation recertification remain incomplete and were explicitly disclosed for release.
- On 2026-11-02, locked Composer audit reported zero advisories; production-only npm audit reported zero. Full npm audit reported three moderate build-tool dependency findings (`glob`, `minimatch`, transitive `sucrase`). They are not resolved by deleting branches, and dependencies must not be blindly upgraded or pinned CSS regenerated during housekeeping.
- GitHub initially reported ten alerts against historical main. After re-evaluating the current default release branch, it reports one moderate `minimatch` build-tool alert (#32). No alerts were manually dismissed. The three npm findings include transitive package effects and must not be confused with three separate runtime vulnerabilities.
- One obsolete HTTPS negative-control folder in Windows Temp remains inaccessible under its existing ACL. It was retained because its contents/ownership could not be verified; no permissions were weakened to delete it.
- Local business MySQL on port 3306 was stopped during cleanup. No business database was started, migrated, restored or deleted. Source/dependency/static checks are distinct from a database-backed local workflow test.

## Future changes

The Owner's improvement campaign is tracked in [WORK_PROGRAM.md](WORK_PROGRAM.md): salary/provider/booking improvements and three defect fixes are live through [merged PR24](https://github.com/Bassamalsaqqa/dental-saas/pull/24). App-wide workflows, dialogs, responsive/device coverage and justified cleanup continue. The Owner provided an authenticated live review session and authorized sign-in when needed, preferring Chrome through Playwright.

Use the deployed source as the baseline. Preserve tenant/RBAC authority, signed evidence, original records, immutable financial history and durable files. Production deployments, migrations and feature activation require separate explicit Owner authorization. No GitHub Actions.

Module contracts remain linked from [START_HERE.md](START_HERE.md). Historic acceptance reports describe their recorded source/time, not automatic certification of future code.

## PR24 operational checkpoint

The three verified obsolete legacy files remain absent from public source and preserved in private quarantine. No-follow GET/HEAD 404 and login/assets 200 passed after reopening. Root/public `.htaccess`, environment, pinned CSS and durable storage remained intact. Live Git has only the pre-existing root rewrite overlay. Its local branch metadata still names historical main; never pull/reconfigure it as routine maintenance.

Fresh encrypted recovery integrity, isolated two-sided restore proof and source-only recovery retaining security deletions are recorded privately. Failed maintenance-entry checks restored the original front controller before installation; source installed only after gate validation. No production schema/data rollback or optional activation occurred. See WORK_PROGRAM for current evidence scope and remaining work; do not resume superseded readiness handbacks.
