# Denta+ production-readiness report - 2026-10-16

## Decision

**Production release and clinic pilot remain BLOCKED.** PR #3 has independent exact-HEAD code approval; integration is ready for the Owner's merge decision. This report does not authorize merge, deployment, migration, scheduling activation or customer-data changes. Dynamic scheduling remains default-off. The work below is isolated synthetic certification, not certification of an installed Hostinger release or a real clinic's schedules.

Latest read-only readiness follow-up and the deferred exact-SHA integration gates are in [Owner integration and readiness](OWNER_INTEGRATION_AND_READINESS.md). They supersede the initial absence of fresh Hostinger evidence below, without granting deployment or pilot approval.

## Exact source and Owner integration control

- Approved and tested application candidate: `ad481ebbf454ebf3a53bc09417d13a2b2f57c0d5` (PR #3).
- Independent GitHub approval: `bassamalsaqqa-google`, 2026-10-15 22:26:21 UTC, specifically that commit. Earlier requested changes on `cc56dec9` were superseded by this exact-commit approval.
- Remote main remains `89ccda3e65a4a091ca850db72ff7b0e4c1e56f04`; PR #3 is open, mergeable and auto-merge is unset.
- A non-ref-changing `git merge-tree --write-tree` integration simulation returned the approved candidate tree `9a7256c0591a80c8288eb41d93b60cedbdc82858` without conflicts. The Owner must recheck both refs and approval immediately before using GitHub merge controls; any changed HEAD requires review of that changed candidate.
- Certification uses a separate linked checkout based on the approved candidate. No runtime application files are changed. Certification harness/runbook/report changes are submitted independently; they do not move PR #3's approved HEAD or main.
- Original checkout HEAD/local main remain `0dfdc06efe67db3b80f3b8cb44e498be47d0cbeb`; all 24 previously recorded owner-file hashes are preserved. Isolated artifacts are under `.ai/pr3-staging-20261016/`.

## Environment and evidence scope

The owned disposable instance is loopback-only `127.0.0.1:3307`, database `democa_dental_saas_test`, with a dedicated test-only identity and live ownership preflight. Business identity in the isolated configuration is deliberately unavailable. PHP is 8.2.12, MariaDB 10.4.32, 64-MiB InnoDB buffer pool, 40 connection limit and REPEATABLE-READ isolation. The initial local suite used no customer snapshots/dumps, Hostinger requests, outgoing messaging, dependency upgrades, Actions, migrations, application-schema or index changes. The later authorized Hostinger follow-up reads metadata only and is recorded separately in the integration/readiness packet. Temporary enabled feature flags belong solely to generated test clinics and are cleaned; unconfigured-clinic default-off and absence of persistent flags are independently checked.

During setup, the disposable instance's Aria `mysql.db` permissions table failed its checksum check. It was repaired only after exact datadir/port verification, and the known test-only grants were restored. Every pre-existing application record covered by the prior PR #3 snapshot matched afterward. A wider read-only inventory found 68 readable tables and an unreadable `whatsapp_delivery_events` entry (`doesn't exist in engine`). No attempt was made to drop, recreate, migrate or conceal that application-table failure. All 275 pre-existing rows across the 68 readable tables matched exactly after the final suite, including all settings. Baseline feature flags were absent and the identical settings map establishes no persistent activation. Full-environment/schema certification therefore remains blocked; the scheduling tests certify only the readable dependencies exercised. This is a local staging baseline finding, not evidence about production or a demonstrated PR #3 application regression.

## Verification results

The initial PR #4 focused suite at `98fe30b4` passed **41 PHP tests / 643 assertions** in 26.683 seconds (64 MiB suite peak); real Chromium passed **198 EN/AR assertions** at 320/360/390px. See the committed [machine-readable evidence](evidence/PR3_STAGING_2026-10-16.json) and [runbook](STAGING_CERTIFICATION_RUNBOOK.md). Prior approved-candidate evidence remains 88 PHP tests / 896 assertions and 198 EN/AR Chromium assertions; it is not presented as newly executed full-system testing.

| Gate | Evidence collected in this certification | Release implication |
|---|---|---|
| Cross-clinic concurrency | Two actual eight-worker mutation scenarios: shared fixture-owned global authority and disjoint clinic/users/roles. 2 tests / 159 assertions passed; tenant/patient/provider IDs and appointment audits checked. Clinic B completed while clinic A mutex stayed held; queued A writes revalidated committed hours. | Local scheduling correctness only. Shared authority can intentionally serialize; unrelated clinical/membership writers and hosting contention need staging sign-off. |
| Configuration restoration/default-off | 2 tests / 22 assertions passed. Prior hours restored through fresh proof and a new checked audit; original immutable bundle and appointment tuple preserved. Unconfigured-clinic reads created no flag and exposed no dynamic providers. | Configuration restoration demonstrated. This does not prove an old-code rollback or safe feature-off transition after provider-linked visits. |
| Larger load and charge plans | 50,000 historical + 5,000 future appointments, 50,001 charges, 2,002 patients, four doctors. Active report: 4,999 items. Lock: 0.389s / 5,014 queries; report: 0.566s / 43 queries; combined: 0.955s; 60 MiB process peak. Six fixture table hashes identical. | No production capacity claim. |
| Multipart audit storage recovery | Complete exported pointer and all parts deleted, fail-closed reads/retention checked, restored from SHA-verified JSON under checked transaction. Every restored DB column, decoded bundle, retained exceptions and appointment hash match. | Storage rehearsal cannot replace an operational backup/restore and retention policy. |
| EN/AR workflows/mobile | Final PHP suite includes routed EN/AR preview/apply/recheck, CSRF/permission checks, clinic clocks and DST. Real rendered-view Chromium: 198 assertions at three mobile widths. | Named operators, real browsers/phones and communication decisions remain unverified. |
| Legacy appointment review | Synthetic immutable NULL provider, duplicate names, completed/invalid intervals and chair/provider snapshots checked by focused tests. | Real legacy identities must be reviewed read-only; never infer doctors by name or automatically backfill. |

Per-worker durations in the concurrency artifact include process spawn, queueing and parent observation; they are not database execution times or a measured production p95. The load fixture is an explicit synthetic assumption; no actual clinic distribution was inspected.

The P2 follow-up groups the populated benchmark and EXPLAIN into one test, eliminating order/same-process dependencies. It passed filtered alone (1 test / 28 assertions), randomized class order (4 / 105, seed 416), then one final focused suite (**40 / 642**) plus **198 mobile assertions**. The capture tool passed **17 isolated self-tests**. Unlike the initial ephemeral logs, [sanitized executed stdout, metrics and complete manifests](evidence/pr4-p2-20261016/manifest.json) are committed with complete selected-source, command, exit, timestamp and raw-input digest bindings. Standalone verification and current-source verification passed. This unsigned executor-declared evidence is not an independent execution attestation. Initial timing figures above belong to the original run; inspect the new captured metrics for the follow-up sample.

## Charge-query indexing assessment

The current query filters `clinic_id` and appointment IDs, while the existing charge schema lacks an index beginning `(clinic_id, appointment_id)`. Real captured EXPLAIN plans and optimizer estimates are retained with the load evidence. A composite index is a candidate for a **separate reviewed migration proposal**, not an executed remedy or a guarantee. Assess optimizer plans, selectivity, read/write overhead, storage, deployment-host version/online-build support, lock duration, backup/recovery and large IN predicates using a healthy production-like staging baseline before requesting schema authorization. In this fixture, the charge plan selected `idx_ccl_patient_status`, estimated 25,001 charge rows and materialized the 4,999-ID predicate. These are optimizer estimates, not measured rows examined. Point locks scale with relevant appointments; batched report queries do not make lock count constant. No index was created or dropped.

## Remaining blockers and closure evidence

1. **Healthy staging and installed-platform parity:** preserve the failed owned instance and approve a separate recovery/replacement procedure; current engine evidence confirms table-open error 1932 and a dictionary/file tablespace-ID mismatch. Fresh authorized Hostinger metadata confirms deployed `063317498e366a009357e0c0fe500041b2417050`, CLI PHP 8.2.34 and MariaDB 11.8.9, absent scheduling provider column/profiles table/migration and no scheduling feature flags. The deployment is behind the candidate. Web-runtime/resource/security parity and an independently reviewed additive rollout plan remain open; no production change or migration was performed. See the [sanitized metadata](evidence/PR4_READINESS_METADATA_2026-10-16.json).
2. **Representative operating capacity:** clinic-size and future-horizon distributions, hosting-like CPU/storage/memory, repeated latency/slow-query/lock-wait evidence, charge-index assessment and relevant clinical/administrative writer contention. Local synthetic samples are not an SLA or universal deadlock proof.
3. **Real operator and legacy sign-off:** named EN/AR operators rehearse preview, explicit acknowledgment/reasons, stale refresh and post-change recheck on real target browsers/phones. Review real timezone/provider qualifications/leave/duplicate identities and unresolved/unsupported preceding-date intervals read-only. Every real future/ongoing conflict needs a documented retain decision or an authorized lifecycle resolution followed by fresh preview. Nothing is automatically moved, cancelled, assigned or notified.
4. **Communications and durable recovery:** approve staff/patient/doctor communication, reminder/queue verification without unsolicited live sends, audit retention/storage limits, consistent backup of compact pointers plus all parts, restore permissions and measured RTO/RPO. A local synthetic export/reinsert does not certify live backup completeness.
5. **Owner-approved rollback for post-pilot data:** restoring prior hours/profile is a new audited configuration change. Feature-off is refused if any provider-linked appointment exists, including historical/cancelled rows. Preserve expanded schema, doctor IDs, clinical/financial relationships and immutable evidence. Do not clear IDs, delete appointments/profiles or down-migrate to force rollback. Old-code compatibility and fix-forward/recovery must be separately reviewed against the actual post-pilot state.
6. **Independent product release debt:** clinical-file permission scope, finance/settlement integrity, framework/dependency/security and schema-parity findings remain governed by their separate program gates. Required guardrails' existing failures are not waived by scheduling approval or this certificate.

## Reproduction and evidence limits

Run serially against an owned, healthy, already-provisioned isolated schema; the runner performs ownership/identity preflight and does not authorize provisioning:

```powershell
C:/xampp/php/php.exe scripts/safe_test_runner.php -c phpunit.staging-certification.xml --no-coverage --no-logging --do-not-cache-result
node tests/js/ScheduleImpactMobileTest.cjs
```

AGY's initial harness had fixture/count/report-format and evidence defects, including a fresh-empty EXPLAIN assumption. Those were corrected and independently reviewed before the one final suite above. No new application-source failure was demonstrated. Existing guardrail failures remain unchanged (DOM sinks, raw-query and font checks); this focused run does not waive them. Raw logs/metrics/synthetic audit export remain in the isolated workspace `tmp/`, with digest references in the committed evidence. The export contains synthetic fixtures only; do not treat it as a real backup. The owned database process was no longer running after tool-session expiration; the unrelated accounting database process was preserved. No additional database restart or test rerun was needed after completed evidence capture.

## Change and failure handling

AGY implemented the certification harness/runbook; Codex and independent read-only reviewers checked actual source, corrected fixture/evidence defects and independently ran the focused tests. Harness defects are fixed within the separate certification PR. Runtime application source remains the approved candidate. If a runtime failure is demonstrated, isolate its correction in a new focused PR, rerun the relevant certification cases and obtain exact-SHA review; do not amend or silently invalidate the approved PR #3 candidate.

The Owner retains PR #3 merge control. After any Owner merge, record the resulting integration SHA and compare its application tree to the approved candidate. A merge is separate from deployment and does not remove the blockers above.
