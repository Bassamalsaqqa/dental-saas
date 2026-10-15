# P0 identity and tenant RBAC review — 2026-10-15

Status: isolated review branch; no merge or deployment. Scope is SEC-01 through SEC-05 from the repository audit. Doctor registry/scheduling remains a separate follow-up after P0 acceptance.

## Confirmed baseline and compatibility

The branch starts at main `0dfdc06efe67db3b80f3b8cb44e498be47d0cbeb`. Repository study mapped 708 first-party source/documentation files and traced identity, tenancy, clinical, finance, messaging, API and deployment boundaries. Installed third-party code is not certified by that study. The owner's original dirty checkout was preserved; all 708 inventoried file hashes remained unchanged.

Hostinger was inspected read-only. Deployed SHA is `063317498e366a009357e0c0fe500041b2417050`; only the existing intentional `.htaccess` modification is present, SHA-256 `af27aced255b9699855d28a108bac66f7a985f09256295b00080a592b6764086`. PHP CLI 8.2.34, MariaDB 11.8.9, 67 tables, 77 migrations (latest 2026-09-14-200000). Identity/membership/audit tables use InnoDB. Final HTTP checks: login 200 HTML, unauthenticated public API 401 JSON, blocked backup endpoint 403. No production files, accounts, data, configuration or migrations were changed.

Read-only aggregate impact: 7 active memberships, 0 invalid active role bindings, 0 unexpired direct global overrides, 4 memberships whose historical global tenant-role assignments differ, and 0 expired platform assignments. Review those four differences before deployment. No automatic reconciliation or personal-data export was performed.

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