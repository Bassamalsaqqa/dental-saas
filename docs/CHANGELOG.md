# Changelog

## 2026-10-28 — Product-wide UX, workflow & theme system campaign (development only)

- Initialized development branch `feature/studio-workflows-themes-20261028` directly from verified deployed release `29dce746c0fc3be436b85a2c1a67fbbb41be3b55` (`release/production-20261017`).
- Bounded groundwork task: aligned active documentation (`PROJECT_STATE.md`, `START_HERE.md`, `CHANGELOG.md`) and compiled candidate cleanup inventories (`local-cleanup-candidates.csv`, `documentation-cleanup-candidates.csv`); zero deletions executed.
- Established P0 delivery boundaries under Master Brief `DentaPlus_StateOfTheArt_UX_Workflow_Theme_System_Codex_Master.md`: genuine platform Super Admin Control Plane entry (`/controlplane/enter`) and persistent responsive impersonation exit (`/controlplane/impersonation/exit`); auth screen fit in `studio_access.php`; production presentation cleanup (remove demo preview label and fix title-separator encoding); safe theme presets (modern/warm optional themes preserving Studio forest/sage default); continuous clinic journey refinements.
- Strict development boundary: development and review only. Zero live deployments, zero production schema migrations, and zero customer data mutations authorized.

## 2026-10-28 — Studio foundation & clinical corrections deployed release

- Deployed and verified commit `29dce746c0fc3be436b85a2c1a67fbbb41be3b55` (tree `188433ad669477aa0c6812acd09fcc3ecd2c67eb`) to production (`https://denta.palsync.net`) on branch `release/production-20261017`. PR #21 merged through authorized exact-SHA fast-forward; `main` remains historical `485a19824d08e35fcd1a2a68923c63ec265867ca`.
- Delivered unified forest/sage Studio shell (`studio-theme.css`, `app_drawer.php`/`app-drawer.js`, contextual action dialogs, money drawers, five-lane appointment dayboard, booking/calendar, settings, and Control Plane layout).
- Delivered four clinical corrections: contract-item preservation, historical-preview mapping/visibility, examination update durability, and explicit empty historical-snapshot/no-recorded-data handling.
- Source-only cutover on PHP 8.3.36 and MariaDB 11.8.9; 67 tables and 77 migration records preserved with zero schema migrations (7 pending migration files stay unapplied), zero Composer/npm dependency changes, zero GitHub Actions, and zero customer data rewrites.
- Independent review `5670330900` recorded with explicit COMMENTED / ACCEPTED disposition.
- Retained committed 293,433-byte Tailwind CSS artifact pinned at SHA256 `3a51d2fffb54ae89fbb4188e3b1ddfc2bce09c7e81148adba78ce750b7fd801a`.
- Active production entitlements: Core contracts and contract signatures remain ON (confirmed live by Owner); dynamic scheduling, WhatsApp, invoice corrections, checkout, contact history, and derivatives remain OFF.

## 2026-10-20 — M0 current release reconciliation (unintegrated candidate)

- Reconciled live and GitHub release `8499042` and tree `bcfa202`; corrected obsolete pre-alpha, main-deployment, and blanket-migration guidance.
- Recorded Stage 0 and settings fixes as deployed; scheduling remained OFF with pending migration excluded.
- Preserved read-only production metadata and prior recovery evidence outside source.

## 2026-10-15 — Pre-pilot schedule impact review workflow

- Merged independently approved PR #2 at `89ccda3e65a4a091ca850db72ff7b0e4c1e56f04`, matching approved HEAD `3bf46f96a653d266870d2aa6a783dcc96f493415` exactly. No deployment or activation.
- Implemented separate schedule-impact workflow; corrected proposal parsing, coherent locked snapshots, strict acknowledgment, compact verified audit storage, retained-visit freshness, and responsive EN/AR confirmation.
- Required reasoned dispositions for affected future visits while preserving appointments and clinical/finance links. Completed/past/clinical-locked visits cannot be excluded from slot advice.
- Addressed duplicate doctor name feedback in booking choices and dayboard columns, escaping patient option names/phone text and binding advisory exclusions to an authorized clinic-owned edit page with short-lived session proof.
- Prepared separate default-OFF doctor scheduling expansion: stable provider IDs, active clinic doctor columns, weekly/break/leave availability, serialized booking and settings writes, preserved historical appointment snapshots, and conservative unresolved-visit blocking. See [scheduling contract](SaaS/DOCTOR_SCHEDULING_CONTRACT.md).

## 2026-10-15 — P0 tenant identity and RBAC corrections

- Derived Control Plane user directory Super Admin label from active, unexpired canonical platform RBAC rather than authentication-group membership (`e43af245e7ab61cb46515f7664b6289def903d23`).
- Made validated active `clinic_users.role_id` the tenant role authority; retained historical global tenant grants and unscoped overrides without allowing tenant escalation.
- Required active, unexpired global platform RBAC, removing authentication-group fallback and runtime auto-promotion.
- Added checked clinic-serialized membership creation, role, status, and removal transactions with actor/context validation, rollback on audit/commit failure, last-manager/designated-admin protection, and isolated connection support.
- Denied tenant global identity/password edits through user-management and legacy aliases; retained Profile/recovery and verified self-password changes in English and Arabic.
- Separated strict public Bearer authority from validated first-party session search, enforcing JSON failures and preserving hosted subdirectories and drawer initialization.
- Normalized permission declarations to framework-supported comma arguments to prevent CI4.6 truncation. See [P0 review](SaaS/P0_IDENTITY_RBAC_REVIEW_2026-10-15.md).

## 2026-09-25 — Isolated test baseline & tooling safety hardening

- Guaranteed harness stack unwinding and `finally` cleanup in `scripts/verify_isolation_and_canary.php` via `VerificationFailureException` propagation, ensuring ownership marker and baseline schema restoration on assertion failures.
- Unified preflight safeguard policy in `scripts/TestDatabasePreflight.php`, enforcing loopback host checks (`127.0.0.1`, `localhost`), approved ports, driver checks, and disjoint business DB verification before connection.
- Standardized canonical migration replayability in `tests/_support/Database/LegacyBaseSchema.php`, encapsulating pre-migration foundation tables with `CREATE TABLE IF NOT EXISTS`.
- Verified fail-closed refusal of non-approved ports, non-MySQLi drivers, remote hosts, and business DB collisions.

## 2026-09-24 — Baseline evidence, program plan & tenant backup containment

- Contained P0 platform database backup exposure in the tenant plane: hard-denied `Settings::createBackup()`, `downloadBackup()`, and `restore()` with 404, preventing full-table database dumping from tenant web routes.
- Removed manual backup generation form, table list, and client-side download/restore scripts from `app/Views/settings/index.php`.
- Established `docs/refactor/DENTA_PLUS_HARDENING_PERFORMANCE_PROGRAM.md` as the single active modernization program plan linked in `docs/START_HERE.md`.

## 2026-09-15 — Subscription timezone contract & Control Plane onboarding

- Established canonical UTC persistence (`ClinicSubscriptionModel::nowUtc`) for all subscription lifecycle timestamps, resolving 3-hour local timezone mismatch.
- Unified `ControlPlaneSubscriptionService` governing subscription start, extend, cancel, date-update, and plan-request approval with mandatory concurrency tokens and explicit NULL sentinels.
- Hardened Control Plane onboarding to operate in global mode without tenant session, enforcing primary administrator designation and atomic defaults persistence.

## 2026-09-14 — Control Plane clinic administrator lifecycle & registry parity

- Implemented authoritative `ClinicAdminLifecycleService` with 5 canonical states (`healthy`, `disabled_user`, `inactive_membership`, `missing_authority`, `unassigned`).
- Enforced single primary clinic administrator per clinic via `designated_admin_user_id` setting with strict airlock preventing Super Admins from being assigned as clinic administrators.
- Added non-destructive reconciliation migration `2026-09-14-200000_EnsureControlPlaneInvoicesTables` and resilient error handling for missing invoice tables in clinic detail views.

## 2026-08-02 — Clinical documentation queues & signed examination notes

- Added tenant-scoped clinical documentation attention queues for completed unsigned examinations ("Ready to sign") and overdue drafts.
- Added one-time provider attestation for completed examination notes with immutable canonical JSON snapshot, SHA-256 fingerprint, and append-only addendum history.
- Added tenant-scoped queue for accepted Treatment procedures awaiting scheduled appointments, linking appointments atomically to procedure lifecycle.

## 2026-08-01 — Appointment checkout completion & explicit clinical linkage

- Completed appointment checkout completion contract: clean visits complete normally; visits with open clinical or financial items require explicit acknowledgement and reasoned audit logging.
- Added indexed `examinations.appointment_id` and fail-closed clinical appointment context service, propagating validated appointment context across Examination and Treatment workflows without duplicate forms.

Older implementation history is preserved in Git history. Current state is summarized in `PROJECT_STATE.md` and active module contracts.
