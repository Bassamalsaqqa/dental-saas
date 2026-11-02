# Project State

Last reconciled: 2026-11-02. Historical deployment and development entries remain available in Git history.

## Production authority

- Installed application: `9e1f8558a85b4a8956cd26c1e36b4e6767fb00d7`, tree `f927a6e8f1fea30333388c4ed924e3f446a31561`. PR #22 was independently APPROVED at this exact HEAD (review #5712796846), integrated and deployed on 2026-10-30.
- Deployment branch and GitHub default: `release/production-20261017`. Historical `main` remains `485a19824d08e35fcd1a2a68923c63ec265867ca`; do not deploy historical main.
- Verified hosting runtime at release: PHP 8.3.36 / MariaDB 11.8.9. Exactly two approved migrations ran: `2026-10-29-120000_WidenTreatmentTypeStorage` and `2026-10-29-180000_CreateFinanceSettlementRequests`. History advanced 77 to 79; tables 67 to 68. No unrelated migration or dependency installation ran.
- Original table fingerprints, protected configuration and original durable files were preserved. Fresh encrypted off-host recovery restored 67 tables / 9,664 rows, 137 protected files and 1,882 previous-source files; an encrypted post-migration supplement preserves the new treatment provenance journals.
- Production postflight: 25 read-only checks each behind maintenance and live, EN desktop / AR mobile, no observed JavaScript exceptions. This does not certify live customer mutations or every view individually.
- Optional scheduling, WhatsApp, corrections, checkout, contact history, derivatives and tenant themes remain OFF. Existing contract/signature/attachment entitlements remain unchanged.
- Detailed recovery/cutover receipts are private operator evidence, not repository fixtures. Never commit backups, keys, credentials or customer records.

## Local and repository housekeeping

- The main checkout at `C:\xampp\htdocs\dental-saas` has been reconciled to the deployed release. September unfinished changes are preserved in a named stash and verified private source archives; they are not accepted release changes.
- Superseded PRs #4, #5, #6, #7, #8 and #12 were closed after proving their heads are release ancestors. Obsolete remote branches were removed; three unique historical tips are retained under `archive/cleanup-20261102/*` tags.
- All 13 obsolete registered worktrees and 14 stale local branches were retired after verified preservation. One authoritative checkout remains. Owner design kits/prompts, runtime dependencies, durable files and the isolated MariaDB data directory remain preserved. Cleanup archives, historical screenshots and Git bundles are outside the source tree under Owner-restricted private storage.
- The unused Hostinger `.denta-rehearsal` was retired on 2026-11-01 after encrypted off-host preservation. Additional obsolete synthetic staging clones were retired on 2026-11-02. Production checkpoints and current release tools remain available. Do not treat retired test domains as running rehearsals.
- PR #23 is Owner-approved housekeeping: release documentation, exact-line guardrail bookkeeping and removal of an obsolete generated debug diff. Integrating it advances repository documentation only; the installed production application remains at `9e1f8558`. The authoritative local checkout uses `release/production-20261017`; runtime application code matches production.

## Remaining real limitations

- Patient workspace query count / latency remains a performance backlog item; no unbounded query refactor is authorized by cleanup.
- Unassessed / No / Yes medical-history provenance remains a separate clinical policy decision. Historical zero values must not be treated as assessed negative.
- Existing signed/legal print format is English-only. Full 400%/native zoom, physical assistive-technology / virtual-keyboard testing and full third-party API mutation recertification remain incomplete and were explicitly disclosed for release.
- On 2026-11-02, locked Composer audit reported zero advisories; production-only npm audit reported zero. Full npm audit reported three moderate build-tool dependency findings (`glob`, `minimatch`, transitive `sucrase`). They are not resolved by deleting branches, and dependencies must not be blindly upgraded or pinned CSS regenerated during housekeeping.
- GitHub initially reported ten alerts against historical main. After re-evaluating the current default release branch, it reports one moderate `minimatch` build-tool alert (#32). No alerts were manually dismissed. The three npm findings include transitive package effects and must not be confused with three separate runtime vulnerabilities.
- One obsolete HTTPS negative-control folder in Windows Temp remains inaccessible under its existing ACL. It was retained because its contents/ownership could not be verified; no permissions were weakened to delete it.
- Local business MySQL on port 3306 was stopped during cleanup. No business database was started, migrated, restored or deleted. Source/dependency/static checks are distinct from a database-backed local workflow test.

## Future changes

Use the deployed source as the baseline. Preserve tenant/RBAC authority, signed evidence, original records, immutable financial history and durable files. Production deployments, migrations and feature activation require separate explicit Owner authorization. No GitHub Actions.

Module contracts remain linked from [START_HERE.md](START_HERE.md). Historic acceptance reports describe their recorded source/time, not automatic certification of future code.