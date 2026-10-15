# P0 identity and tenant RBAC review — 2026-10-15

Status update: PR #1 merged at `8694607c4977b60be374a2b9edba0790247ab759` after independent code approval and the Super Admin display correction (`e43af245e7ab61cb46515f7664b6289def903d23`; 21 focused tests / 196 assertions). No deployment or production data/schema change occurred. The evidence below records the original review and corrections; doctor scheduling is a separate default-off review phase.

Status: draft PR #1; independent review requested changes at `8496425419651c6add53d5027b3278f8c1aeb3cd`. Acceptance remains blocked pending independent review of corrections; no merge or deployment. Scope is SEC-01 through SEC-05 from the repository audit. Doctor registry/scheduling remains a separate follow-up after P0 acceptance.

## Confirmed baseline and compatibility

The branch starts at main `0dfdc06efe67db3b80f3b8cb44e498be47d0cbeb`. Repository study mapped 708 first-party source/documentation files and traced identity, tenancy, clinical, finance, messaging, API and deployment boundaries. Installed third-party code is not certified by that study. The owner's original dirty checkout was preserved; all 708 inventoried file hashes remained unchanged.

Hostinger was inspected read-only. Deployed SHA is `063317498e366a009357e0c0fe500041b2417050`; only the existing intentional `.htaccess` modification is present, SHA-256 `af27aced255b9699855d28a108bac66f7a985f09256295b00080a592b6764086`. PHP CLI 8.2.34, MariaDB 11.8.9, 67 tables, 77 migrations (latest 2026-09-14-200000). Identity/membership/audit tables use InnoDB. Final HTTP checks: login 200 HTML, unauthenticated public API 401 JSON, blocked backup endpoint 403. No production files, accounts, data, configuration or migrations were changed.

Read-only aggregate impact: 7 active memberships, 0 invalid active role bindings, 0 unexpired direct global overrides, 4 memberships whose historical global tenant-role assignments differ, and 0 expired platform assignments. The follow-up below adjudicates those four differences using a new read-only inspection; no assignment changes are needed automatically. No automatic reconciliation or personal-data export was performed.

Local verification uses PHP 8.2.12 and MariaDB 10.4.32. Composer platform/validation and locked audit passed; production npm audit returned zero advisories. Existing installed dependencies were reused. Temporary Tailwind compilation passed without modifying compiled CSS.

## Resulting authority and ownership

- Active `clinic_users.role_id` is tenant role authority; validate active user, clinic, membership and eligible role scope. Global tenant assignments and unscoped direct overrides remain stored but cannot grant tenant authority.
- Platform authority requires an active user and active, unexpired global `super_admin` assignment. Authentication groups cannot confer it or be promoted automatically by runtime sync.
- Tenant creation refuses existing global identities. New identity, authentication group, membership and audit share one checked transaction. Controlled global-mode platform linking preserves existing identity/password fields.
- Membership mutations authenticate the actor, validate mode/context and exact permission, lock the clinic before membership changes, revalidate authority/targets, and check begin/write/audit/status/commit. Nested ownership is rejected. Last eligible manager/designated administrator and platform accounts are protected.
- Tenant account aliases cannot edit shared identity/password. Owner edits use Profile and verified self-password/recovery flows. Clinic removal deactivates access while preserving global accounts, other memberships and historical records.
- Public Bearer authority is independent of cookies/web impersonation and rejects malformed clinic headers. First-party session search retains the original nine query/payload contracts through its own JSON authentication/tenant/subscription/permission boundary.
- Route declarations use `permission:module,action`: CI4.6 truncates the third colon segment before the custom filter receives it. Parser-only fixes cannot correct that framework behavior.

## Verification and limits

All tests use the canonical `scripts/safe_test_runner.php`, the provisioned synthetic `democa_dental_saas_test` on loopback port 3307, and a dedicated test-only database identity. No business/production database tests or real notifications were used. Evidence is retained in the ignored audit and worker attempt artifacts.

Independent review suites cover cross-clinic roles, inactive/expired platform assignments, malformed filters through the real framework pipeline, checked creation and injected transaction/audit failures, ownership aliases, controlled linking, last-manager protection, all nine session searches, strict Bearer isolation, URL mapping, quotas and airlock enforcement. Existing focused app API, onboarding, lifecycle, timezone and backup-containment checks are retained. Final result: **204 tests / 1,676 assertions passed**, including bilingual edit/show/index rendering and syntax checks of rendered inline JavaScript; **36 URL/transport assertions passed**; all **37 changed/new PHP files linted successfully**; `git diff --check` passed. The focused command is retained in `tests/README.md`.

Deferred release blockers remain: copied CI4.6.3 official security advisories (outside Composer dependency auditing), 13 affected npm development/build packages, 22 existing guardrail matches (6 DOM sinks, 12 raw controller queries, 4 font strings), one preexisting unreferenced patient backup-view parse error, and documented schema-parity debt. No framework/dependency upgrade or allowlist expansion is included. Render/route regressions are not a full bilingual desktop/mobile browser or concurrency load certification.

## Integration and staging follow-up

The original checkout contains separate uncommitted test-isolation and dependency-injection work, including `PermissionService::setDatabase()`. This branch starts from main and deliberately does not absorb it; reconcile that overlapping API before merging those changes together. No owner files were overwritten.

Before rollout, use a staging copy to check English/Arabic desktop/mobile user management, two clinics sharing one identity, genuine platform entry/impersonation, last-manager denials, and the nine session searches. This draft does not claim a full visual or concurrent-load certification. Doctor registry/scheduling is the next separately scoped PR only after P0 acceptance.

## Migration and rollback

No migration, schema change, bootstrap-data change, backfill or SQL-dump regeneration is required. Historical grants are retained and ignored by tenant authorization. Review the four production role differences and stage the code before any separately authorized rollout.

Rollback is a code revert to the prior accepted revision; it does not undo legitimate memberships created/changed while the patch ran. Reverting reintroduces the audited authorization defects, so deployment and rollback require owner review. No production deployment was authorized or performed by this work.
## Independent-review corrections and production authority disposition

The [independent GitHub review](https://github.com/Bassamalsaqqa/dental-saas/pull/1#pullrequestreview-5531116937) found undefined form variables in `ControlPlane\Users::addMembership()` and membership-only entry checks in `TenantFilter` / `AppApiTenant`. Corrections restore strict positive-ID form parsing and require canonical eligible-role validation before ordinary tenant-only access, retaining the existing explicit web impersonation contract and strict Bearer boundary. Bootstrap remains an authenticated context-discovery endpoint, not a tenant resource gate.

### Four historical role differences: read-only adjudication

Observed at **2026-10-15T10:02:07+00:00** using streamed PHP over SSH (no remote file creation), `SET SESSION TRANSACTION READ ONLY`, SELECTs, and rollback. Identity fields, raw user/clinic IDs, passwords and business/medical data were not exported. A local ignored evidence file holds consistent keyed pseudonyms; the table uses review-only labels A/B and X/Y. These labels confer no authority and are not migration mappings.

| Case | Account | Clinic | Active membership role | Differing historical global assignment | Expected authorization |
| --- | --- | --- | --- | --- | --- |
| D1 | A (disabled) | X (active) | `practice_manager` | `ora_doctor`, scoped to X | Denied: global account disabled. |
| D2 | B (disabled) | X (active) | `practice_manager` | `ora_doctor`, scoped to X | Denied: global account disabled. |
| D3 | B (disabled) | Y (active) | `practice_manager` | `ora_doctor`, scoped to X (foreign here) | Denied: global account disabled; foreign global assignment cannot grant Y authority. |
| D4 | A (disabled) | Y (active) | `practice_manager` | `ora_doctor`, scoped to X (foreign here) | Denied: global account disabled; foreign global assignment cannot grant Y authority. |

All four membership roles are active and correctly scoped; their differing global assignments are active and unexpired. Neither account has a genuine platform assignment or unexpired direct permission overrides. They are **four membership rows on two disabled accounts**, not four currently eligible users.

There are 11 active membership rows in total; 7 also have active accounts/clinics, and all 7 pass canonical role eligibility. The four differences are the remaining rows. The prior aggregate mixed these predicates, so the four should not be described as active-account role conflicts.

Each `practice_manager` membership grants 42 distinct module/action capabilities after existing `read/view` and `edit/update` aliases are normalized. For each case, the legacy union (membership role plus all active historical global role grants) is the **same set of 42**: ignoring `ora_doctor` removes **zero** capabilities from that membership-role set and adds none. This is a permission-set comparison, not proof that a disabled account can execute those actions. Effective authorization remains **zero while the account is disabled**. If an owner separately and deliberately reactivates either account, its existing eligible `practice_manager` membership is expected to grant those 42 capabilities in each clinic; it will not silently become a doctor-only membership. Intent is not inferred from historical role labels.

**Disposition:** retain disabled status and all assignment rows; no backfill, remap, reactivation or automatic repair. Before any later account activation, the owner must separately decide each clinic's intended membership role. This evidence resolves the four-difference analysis for this code review; it does not approve production rollout, account activation or other release blockers.

### Reviewability and diff controls

The initial commit converted most originally LF files to CRLF. The correction restores LF in the affected PR files, including the 1,835-line dashboard test and 2,533-line user details view; byte comparison before/after LF normalization proves identical non-newline content in all 51 restored files (20,983 CR bytes removed). No whole repository normalization or `.gitattributes` change is included. Intentional removal of unsafe identity/legacy group mutation bodies remains: restoring those paths to reduce line count would reintroduce the defects.

Deterministic route comparison, after the required `permission:module:action` to `permission:module,action` translation, leaves only scoped email checks, delete authority for role removal, and the nine session-search routes. No existing route/controller target is dropped. The correction tests real registered requests rather than replacement test routes. Remaining controller, view, model, service and contract changes were examined by domain; isolated connection binding, membership read projections and owner-only UI are required to preserve the new authority contract.

Targeted correction regressions passed **42 tests / 514 assertions** through the safe runner. They exercise real registered POST linking/reactivation, malformed arrays/scalars, unchanged identity, canonical role rejection across tenant-only web/file/notification and App API routes, valid zero-permission tenant entry, EN/AR outcomes, and explicit App API impersonation rejection. A separate read-only independent agent reviewed source, contracts, routes and regression assertions and found no remaining blocking runtime issue; it did not run DB tests or access production. This does not replace independent GitHub acceptance or authorize release. Final acceptance-suite verification on the exact corrected commit is recorded in the PR follow-up. Existing focused-test results above are the original draft's results and do not substitute for the corrected suite. No GitHub Actions, broad suite, schema/data migration, dependency upgrade, merge or deployment is part of this correction.

The strengthened real Arabic POST assertion exposed a subdirectory locale-routing defect: `BaseController::applyLocaleContext()` now uses application-relative `SiteURI::getRoutePath()` consistently with AuthFilter. English/Arabic routed linking verifies exact localized success messages (2 tests / 40 assertions). The separate reviewer inspected this last runtime change and found no blocker. Local synthetic browser QA exercised login, an English role-only form submission, Arabic edit/detail rendering and localized existing-account rejection; it used an ignored local router and the disposable database, not production. Browser coverage is focused desktop QA, not a full mobile or concurrency certification.
