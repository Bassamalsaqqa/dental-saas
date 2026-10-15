# Dynamic Doctor Scheduling ? Phase B

Status: Phase B PR #2 independently approved at `3bf46f96a653d266870d2aa6a783dcc96f493415` and merged at `89ccda3e65a4a091ca850db72ff7b0e4c1e56f04`; pre-pilot workflow is a separate review PR. No production migration, backfill, assignment change or deployment is authorized by this document.

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

## Required pilot gate: future-appointment schedule-change impact

**Production pilot remains blocked until an operator-visible impact report and explicit acknowledgment/resolution workflow are available and exercised in staging.**

Changing clinic working days/hours, provider weekly windows, leave exceptions, qualification or bookability changes eligibility for new bookings. It deliberately does **not** rewrite, move, cancel, reassign or shorten existing appointments. A future visit can therefore remain booked outside the new hours, during new leave, or against a provider who is no longer bookable/eligible. Its stable provider ID, patient/clinical links and historical label remain intact. Default duration changes likewise leave stored durations unchanged. Timezone changes are already refused for clinics containing appointments.

### Implemented schedule-impact workflow (Pre-pilot PR)

1. **Full future booking horizon report**: Read-only, clinic-scoped before/after impact evaluation over all future appointments starting from clinic-local `today` (`appointments.appointment_date >= today`), including confirmed visits, completed visits, and unresolved historical assignments (`provider_user_id IS NULL`). Cancelled and no-show visits release capacity. Checks whole visit intervals against proposed clinic hours, working days, provider weekly windows, lunch breaks, dated leave, and canonical clinic-scoped role eligibility.
2. **Preview/apply cryptographic handshake**: `POST /settings/preview-schedule-impact` validates normalized proposed DTOs, computes a full-content SHA-256 state digest across all future visits and settings, and issues a 15-minute session-bound proof. `POST /settings/apply-schedule-impact` and direct service mutations (`configureHours`, `configureProfile`) recompute the report under the clinic-first transaction lock (`FOR UPDATE`). Direct mutation bypass without valid proof is rejected whenever conflicts exist. Stale, tampered, expired, cross-clinic, or Bearer-blended requests fail closed.
3. **Explicit retention and resolution communication**: For every affected after-change visit, an attributed acknowledgment checkbox and an explicit reasoned retain disposition (`action: retain`, non-empty reason) are required. Alternatively, operators may reschedule affected visits via the existing authorized appointment-edit page and refresh the report. Retained exceptions block overlapping slot bookings canonically but do not grant new booking availability. Existing appointment records, patient links, and clinical/finance links remain byte-for-byte unchanged.
4. **Durable audit evidence location**: Implemented strictly using existing tables (`settings`, `control_plane_audits`) without migrations or schema additions. Stores a compact clinic-scoped review reference in `settings`; complete configuration/report/disposition evidence belongs in bounded, verified audit parts in `control_plane_audits`. Evidence must include all affected IDs, before/after reasons, original and proposed configuration, actor and timestamp; no silent truncation is permitted. Audit failure triggers complete transaction rollback.
5. **Recheck pilot gate**: `GET /settings/schedule-impact-recheck` provides an on-demand, read-only post-change recheck report showing active clinic conflict status and referencing the latest applied audit record.

### Remaining staging and pilot gates

Code implementation alone does **not** claim deployment readiness or authorize production data changes. The following gates remain open:
- Independent code review and approval by Codex.
- Staging walkthrough and workflow rehearsal with clinic operators managing out-of-hours visits.
- Operator review of legacy unresolved bookings and provider qualifications.
- Existing P0 security/dependency and schema-parity release debt remains separate and active.

## Advisory edit exclusions and visible doctor identity

Doctor selection must visibly pair the name with a stable clinician reference; numeric provider IDs remain the submitted identity, and the historical `chair_name` snapshot remains a name snapshot. Equal names must be distinguishable in EN/AR full forms, quick/contextual forms and narrow dayboard headers/actions.

An advisory `exclude_id` is ignored unless a short-lived, session-owned edit proof was issued by that clinic appointment's authorized edit page. The proof is bound to the actor and appointment for 15 minutes; current clinic ownership and edit authority are rechecked. Foreign, stale, malformed, mismatched or Bearer-borrowed proofs do not remove any booking from slot advice. Reloading an expired edit page issues a fresh proof. Canonical booking mutations never trust this advisory proof or a client-supplied excluded ID.

## PR #2 correction verification (2026-10-15)

Independent GitHub review requested changes at `d623d203671ae45ffd23de6fc369cb603e81861d` because same-named doctors were not visibly distinguishable. AGY implemented the reference formatter and booking/dayboard label correction. Codex independently reviewed and completed inactive/disabled labels, RTL Select2 direction, patient escaping, and session-bound advisory exclusions. A separate read-only reviewer found no correction blockers.

Focused final acceptance passed **36 tests / 582 assertions** (`phpunit.pr2-review.xml`). Actual headless Chromium checks passed **564 assertions** for native controls, real app Select2, selected numeric IDs, long equal names, visible column/action references and internal scrolling in EN/AR at **320/360/390px**. Retained slot transport and P0 URL checks passed **60 + 36 assertions**. Browser fixtures use synthetic data and locally cached copies of the already shipped third-party versions; no dependency change is included. No migration, schema/backfill, production request/write, customer-data change, merge, deployment or GitHub Actions was performed for this correction.

The pre-pilot workflow in this separate PR must pass independent approval and a staging operator rehearsal before a production pilot. No approval here authorizes customer-data changes.

## Pre-pilot operating boundary

The preview/apply gate covers clinic hours and provider-profile availability, bookability and explicit clinician qualification. Security lifecycle actions (account deactivation, membership removal or role changes) remain available through their existing authorized workflows. They can invalidate existing doctor eligibility and must invalidate pending previews. Operators must run the current schedule recheck and complete a fresh acknowledgment after any such action before relying on pilot readiness. The code does not prevent emergency account disabling or claim that every administrative action is a schedule change.

A retained exception is an explicit, reasoned decision about an exact existing appointment snapshot. It does not create available slots, waive overlapping-booking checks, grant clinician authority or automatically contact anyone. A stale decision after rescheduling or configuration/eligibility changes requires a new review. A proposed or planned reschedule is unresolved until the canonical appointment workflow succeeds and a refreshed report verifies the result. Historical display labels and all patient, clinical and finance links remain intact.

Platform audit retention can prune older evidence. Missing, incomplete or corrupt review evidence must be reported as unavailable and require a fresh operator review; absence is never proof that a conflict was accepted. Operators must retain required evidence under the clinic's approved policy before a pilot. Recheck reports are operational evidence, not a deployment switch.

Before a pilot, rehearse an hours reduction, a lunch-window change, leave, loss of bookability, qualification change, explicit retention, canonical rescheduling, stale-preview rejection, evidence loss, and restoration of the previous configuration on staging synthetic data. Confirm dayboard/unavailable-provider visibility, preserved overlap protection and unchanged booked reminder times. Patient/doctor communication needs an attributed clinic-approved process; a schedule edit neither reschedules visits nor sends those communications.

PR #2 merge authorizes code integration only. No deployment, migration, backfill, feature activation, production request or customer-data modification is part of this workflow PR. Existing P0 dependency/security, guardrail and schema-parity release blockers remain. Production readiness requires independent code approval, successful staging rehearsal, resolved or explicitly managed impacts and separately authorized release work.

Compatibility: the legacy direct hours/profile entry points remain available for changes whose recomputed full-horizon report has zero affected visits and which supply no preview fields. They still require current tenant settings authority, serialization and required audit. They cannot silently accept existing conflicts: any affected visit requires the same proof, explicit acknowledgment and exact per-visit dispositions. The current settings UI always previews and requests explicit confirmation, including zero-conflict changes.

## Pre-pilot PR verification (2026-10-15)

AGY implemented and corrected the initial slice; Codex independently reviewed the source and corrected audit storage, snapshot consistency, transport parsing, stale retention and UI controls. A separate read-only source reviewer found no remaining isolation, transaction, audit-integrity, appointment-preservation or schema blocker after the identified corrections. External PR approval remains pending.

Focused final command: `php scripts/safe_test_runner.php --no-coverage --no-logging --do-not-cache-result --configuration phpunit.schedule-impact.xml` passed **50 tests / 524 assertions** on the owned loopback MariaDB test database at port 3307. No migrations, DDL, schema resets or production requests were run. Coverage includes before/after hours, weekly breaks, leave, bookability/qualification, legacy unresolved and confirmed visits, stale role/booking state, exact dispositions, CSRF-protected EN/AR preview/apply/recheck, Bearer/session isolation, second-audit-part rollback, over-64-KiB Arabic evidence and missing-part rejection, exact appointment preservation and completed/clinical-lock proof invalidation.

`node tests/js/ScheduleImpactMobileTest.cjs` passed **198 assertions** using actual headless Chromium at 320/360/390px in EN/AR. Rendered report fixtures use synthetic data and local compiled CSS, with network requests blocked; tests verify the report content and real submit handler, not a deployed installation or full production browser shell. The 505-visit fixture preserves all IDs/reasons via one JSON field beyond PHP's default form-variable limit. No dependency changes.

All 22 changed/new PHP files passed syntax checks; whitespace and LF preservation passed. Required SaaS guardrails still fail on the same 24 baseline source matches across three categories, with zero added matches. Required route scan completed through an ignored CLI bootstrap shim for the repository's existing SUPPORTPATH issue. Original checkout and local main remain unchanged; detailed relay/review/test/preservation artifacts live in the ignored `.ai/schedule-impact-20261015/` directory. Future-horizon load/lock duration, operator communications, restoration rehearsal and audit retention require representative staging verification before a pilot.
