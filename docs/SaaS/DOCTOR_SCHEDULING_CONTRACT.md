# Dynamic Doctor Scheduling ? Phase B

Status: separate review phase after P0 PR #1. No production migration, backfill, assignment change or deployment is authorized by this document.

## Identity and eligibility

Booking resources are clinic doctor identities (`users.id`), independent of physical rooms and historical chair labels. Active user, clinic, membership and canonical clinic-scoped role are required. Doctor/senior-doctor roles or an explicit doctor qualification identify clinicians; the broader medical-staff flag does not qualify assistants. An authorized clinic operator may explicitly qualify a clinician with a custom role through a provider profile. Global grants never confer tenant eligibility. Duplicate names remain distinct resources; identity renames never rewrite appointment display snapshots.

The clinic feature flag defaults off. Enablement requires the additive schema first. Dynamic mode has one column per eligible active bookable doctor, without the legacy manual three-label ceiling. No eligible doctors means booking is unavailable. Legacy mode retains configured resource labels.

## Availability and consistency

Clinic-local appointment dates/times remain local, unlike UTC subscription timestamps. A booking must fit entirely within both clinic working hours/days and provider availability. Provider weekly windows support breaks; dated exceptions support leave or replacement hours. Invalid dates, cross-midnight intervals and ambiguous/nonexistent DST times are rejected.

Intervals are half-open: an appointment ending at 10:00 permits a new one at 10:00. Cancelled and no-show visits release capacity; all other statuses block. Updates exclude only the same clinic-owned appointment. Clinic-row serialization protects booking writes and changes to hours, profiles and feature enablement. Slot responses are advisory; the mutation rechecks eligibility and conflicts under the lock.

All mutations use an owned checked transaction. Failed writes, clinical handoff, required audit or commit must not report success. Accepted-treatment scheduling preserves the encounter appointment separately from the future scheduled appointment, with lifecycle history atomic with booking. Notifications and activity side effects occur only after commit. Session and Bearer actors retain their separate canonical identity/context contracts.

## Preservation and future migration

The expansion adds nullable `appointments.provider_user_id` and a clinic/provider profile table. Existing appointment IDs, dates, labels, patient links, statuses and clinical links remain unchanged. `chair_name` remains the historical display snapshot. No matching by name, label or list position is allowed.

Unassigned historical appointments conservatively block overlapping doctor bookings. Dayboards retain an explicit unresolved/unavailable-provider column so existing visits cannot disappear when users, roles or profiles change. Reconciliation is read-only: report counts, labels and eligible candidate IDs with ambiguity left unresolved. Any future assignment migration needs a separate reviewed mapping, scoped authority, backup, dry run and rollback plan; this phase never runs it automatically. Removing populated provider columns is unsafe and must be refused.

## Review and future release gates

Before merge, verify real web and public Bearer writes, session isolation, forged/foreign/inactive providers, duplicate names and more than three doctors, lunch/leave/boundaries, overlap/adjacency, concurrent writes, clinical rollback and EN/AR booking/dayboard rendering. Use synthetic fixtures on the explicitly owned disposable test database. No GitHub Actions or broad unrelated reruns.

Before a later production release: independent approval, staging schema expansion, old-code/new-schema compatibility, legacy reconciliation review, provider qualification and schedules checked by clinic operators, explicit feature pilot and rollback rehearsal. Code approval does not authorize deployment or production data changes. Existing security/dependency and schema-parity release blockers remain separate.

## 2026-10-15 verification evidence

- Dedicated disposable loopback MariaDB on port 3307; exact approved database and ownership marker verified before execution. No production schema or data writes.
- Focused acceptance: `php scripts/safe_test_runner.php --no-coverage --no-logging --do-not-cache-result --configuration phpunit.phase-b.xml` passed **56 tests / 500 assertions**. Final empty-directory edit/fallback regression rerun passed **13 tests / 89 assertions** after the UI correction.
- JavaScript behavioral checks passed **116 assertions** (60 slot transport, 20 rendered EN/AR edit preservation, 36 retained P0 URL/transport checks); **38 changed/new PHP files** and **10 rendered inline scripts** passed syntax checks. Diff whitespace check passed; changed files retain LF, without whole-file line-ending churn.
- Coverage includes real routed session/Bearer writes, exact audit actor, EN/AR notes and fallback selection, more than three same-named doctors, assistant qualification/bookability, foreign/inactive roles, lunch/leave, DST gap/fold/crossing, overlap/adjacency, concurrent two-process booking, failed begin/commit/audit/history rollback, clinical future-link preservation, old-schema legacy creation and enabled-schema fail-closed slots/writes, and expansion rollback/reapply preserving a synthetic historical row.
- Read-only production observation: deployed `063317498e366a009357e0c0fe500041b2417050`; intentional `.htaccess` SHA-256 `af27aced255b9699855d28a108bac66f7a985f09256295b00080a592b6764086` unchanged. Original checkout `HEAD` and local `main` remain `0dfdc06efe67db3b80f3b8cb44e498be47d0cbeb`; 708 inventoried source hashes and 23 owner-dirty hashes unchanged.
- Independent code review found no remaining blocker for a **separate review PR**. No Phase B merge or deployment. Staging/browser walkthrough, operator schedule/qualification review, unresolved historical-visit reconciliation and explicit feature pilot remain release gates. Existing P0 framework/dependency/guardrail/schema-parity release debt is unchanged.

The schema test uses synthetic rows only. It does not establish production data parity or authorize running a rollback/migration against production. No full customer SQL dump is imported, copied or regenerated.
