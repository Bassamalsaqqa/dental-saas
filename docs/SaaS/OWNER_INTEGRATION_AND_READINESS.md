# Owner integration sequence and readiness decisions

No merge is authorized. These are separate future approval gates, not executable instructions to merge now. Auto-merge remains unset on both PRs.

## Pinned candidates

- Remote main: `89ccda3e65a4a091ca850db72ff7b0e4c1e56f04`.
- PR #3: `ad481ebbf454ebf3a53bc09417d13a2b2f57c0d5`, exact-head approval by `bassamalsaqqa-google` at 2026-10-15 22:26:21 UTC. Expected application tree: `9a7256c0591a80c8288eb41d93b60cedbdc82858`.
- PR #4 previously approved HEAD: `98fe30b48f795cb6066bb4176ba6265158d2e1de`, independent approval at 2026-10-16 05:45:03 UTC. Tree: `677fe88cd3954c2c54f7cb8f79fc444e005bfe31`. It is stacked on PR #3.
- This PR #4 P2 correction changes test/evidence source. The old approval is historical and does not approve the new HEAD. The exact new SHA and tree must be recorded from GitHub after push and receive re-review before integration.

## Ordered integration gates

1. Obtain explicit Owner authorization for PR #3 on the exact pinned HEAD. Immediately re-fetch read-only PR metadata and remote main/head refs, latest reviewer states, mergeability, diff and Actions count. Stop on drift, a newer request for changes, unexpected runtime delta or missing exact-head approval. Do not use auto-merge. The current conflict-free merge-tree simulation produces tree `9a7256c0591a80c8288eb41d93b60cedbdc82858` and moves no refs.
2. Only after that authorization and merge, record the actual resulting main SHA as **M3**. Do not invent M3: merge/squash/rebase choices produce different commit IDs. Its entire tree must match the approved PR #3 tree above. Verify actual main remote, PR #3 merged state, candidate provenance and parent/history as appropriate to the Owner's chosen strategy. Stop if integration adds any unexpected change; do not compensate by editing main.
3. Only then retarget PR #4 from `feature/pre-pilot-schedule-impact` to `main`. Do not merge PR #4 into PR #3's branch: that would move its approved HEAD. Retargeting is deferred now because PR #3 is still open.
4. Pin PR #4's new corrected HEAD as **H4** and tree as **T4**; require exact-head independent approval. Compare M3..H4, not an assumed merge-base-only diff. Delta must contain only the intended reviewed test/evidence/docs/capture-tool changes; application/runtime/migration/dependency trees must equal PR #3. Simulate merging M3 and H4 with `git merge-tree --write-tree` (no ref updates). Expected result is T4 when M3 exactly matches PR #3's tree. Squash/rebase of PR #3 may change ancestry and GitHub's displayed three-dot diff, so inspect actual result/delta rather than infer a conflict or approval from commit ancestry alone. If the displayed diff contains inherited PR #3 changes, stop for an Owner-reviewed stack adjustment; do not silently rebase or rewrite the approved PR #4 branch.
5. Recheck review status and the retargeted base/result. Ask the independent reviewer to reaffirm the changed base if its semantics/diff differ from the originally reviewed stack. Any H4 source change needs a fresh exact-head review. Explicit Owner authorization for the final H4/M3 integration is still required before PR #4 merge; authorization to merge PR #3 does not authorize PR #4.
6. After an authorized PR #4 merge, record actual main **M4**, verify its tree equals approved T4 and preserve integration evidence. Stop there. Neither merge authorizes deployment, migrations, feature activation, schema repair or customer-data changes.

## Material readiness findings

Read-only Hostinger evidence at 2026-10-16 05:58:16 UTC confirms deployed `063317498e366a009357e0c0fe500041b2417050`. Eight sampled files/absences match that deployed commit; `.htaccess` is a tracked deployment override. CLI PHP 8.2.34 and MariaDB 11.8.9 differ from local PHP 8.2.12/MariaDB 10.4.32. Web PHP/extensions/timezone/resource limits are unverified. A missing RewriteBase directive alone is not proof of a route defect; the installed root-controller rewrites must be validated during a separately authorized release check. No rewrite was modified.

Hostinger lacks `appointments.provider_user_id` and `clinic_provider_profiles`, and the scheduling migration is absent from its recorded migration inventory. Feature flags are absent. Existing charge indexes have no `(clinic_id, appointment_id)` prefix. This is an intentionally older installed release, not evidence of an attempted deployment. Code integration cannot make its schema ready. Schema expansion, index changes and activation require separate reviewed plans and Owner authorization; none occurred here.

The owned staging database has SQL error 1932/42S02 opening `whatsapp_delivery_events`: SQL metadata cannot obtain its engine, while InnoDB dictionary/tablespace catalogs identify space 297 and current engine stderr reports physical file ID 4294967294. Both `.frm` and `.ibd` exist. This confirms a table-open/tablespace mismatch; it does not establish when or why corruption occurred. No REPAIR, DROP/CREATE, DISCARD/IMPORT, forced recovery, migration, reset or file transplant was attempted. No production-table failure was demonstrated by this local finding. Preserve the failed instance as evidence; approve a separate healthy isolated replacement/recovery procedure before whole-schema certification.

## Decisions still requiring Owner/Ops approval

- Exact-SHA integration: PR #3 first, corrected/re-reviewed PR #4 only after retargeted-base verification.
- Healthy staging recovery/replacement scope and hosting-like runtime; no repair of current data under this task.
- Representative clinic/horizon distributions, capacity budgets and realistic concurrent clinical/administrative writers. Local synthetic timings do not establish hosting SLA; shared global authority can serialize clinics.
- Charge index proposal: review `(clinic_id, appointment_id)` selectivity, plans/rows examined, write overhead, storage, online-build/version support and lock/recovery impact on healthy staging before requesting any DDL. No index was created.
- Audit recovery: named Ops owner, retention covering every compact settings pointer and all immutable part/event rows, consistent off-host backup, restore permissions, integrity/clinic/actor checks and measured RTO/RPO. Current tests prove synthetic export/reinsert and configuration restoration; neither full-database restore nor storage-engine crash durability is certified. Tenant UI backup remains prohibited; use separately approved operational recovery tooling.
- Named EN/AR operators and clinical owner: review unresolved legacy doctor identities, duplicate-name ID labels, qualifications/timezones/leave and future conflicts, then rehearse explicit retain reasons or existing lifecycle resolution followed by fresh preview. Sign off target phones/browsers and patient/doctor/reminder communications without live unsolicited sends. No existing appointment may be moved or remapped automatically.
- Keep clinical-file permission isolation, finance/settlement integrity, dependency/framework security and baseline guardrail failures as separate unresolved release blockers. Scheduling approvals waive none of them. Post-pilot rollback cannot clear provider IDs or force disablement once linked appointments exist.

Read-only sanitized [metadata evidence](evidence/PR4_READINESS_METADATA_2026-10-16.json) is included. See [readiness report](PRODUCTION_READINESS_2026-10-16.md) for measured synthetic scope. Dynamic scheduling remains default-off; no release/pilot readiness decision is granted.
