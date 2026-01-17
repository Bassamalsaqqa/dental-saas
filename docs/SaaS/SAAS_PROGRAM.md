# DentaCare SaaS Program — Source of Truth

## 0. Purpose
- Canonical rules and phases for the SaaS/multi-tenant track.
- If code or other docs diverge from this file, the code/docs are wrong.

## 1. Non-Negotiables
- RBAC-only authorization. IonAuth handles authentication only; no group-based runtime checks.
- Tenant vs control plane separation with explicit mismatch rejection.
- Clinic selection wall: no dashboard/menu/API without `active_clinic_id`.
- Private storage with streaming access only; no public tenant files or URLs.
- Tenant-context jobs: every job carries `clinic_id`; workers never rely on session.
- Fail-closed behavior: cross-tenant access returns 404 (not 403) and may log audit.
- Session regeneration on clinic switch or impersonation changes.
- Verification and CI guardrails are mandatory before ship.

## 2. Session & Tenant Contracts
- Keys (server-set only):
  - `active_clinic_id` (int/uuid) — required for tenant plane
  - `global_mode` (bool) — required for control plane
  - `is_impersonating` (bool)
  - `impersonated_clinic_id` (nullable)
- Mismatch rules:
  - Tenant routes reject if `active_clinic_id` is missing/invalid or if `global_mode = true`.
  - Control-plane routes reject if `active_clinic_id` exists.
  - Cross-tenant record access returns 404; consider audit event `tenant_violation_attempt` (rate-limited).
- Session regeneration on clinic switch and impersonation start/end.

## 3. Planes & Routing
- Tenant plane: `auth` → `tenant` → `permission` filters; requires `active_clinic_id`; denies `global_mode`.
- Control plane: `auth` → `controlplane` → `permission`; requires `global_mode`; denies `active_clinic_id`; `super_admin` only.
- No API/listing/search endpoint is reachable without the appropriate filter chain.

## 4. Data Isolation Rules
- Every tenant-owned table has `clinic_id` NOT NULL and composite uniques as needed (e.g., `clinic_id + email`).
- Reads use scoped find `(id + clinic_id)`; no plain `find(id)` on tenant models.
- Writes set `clinic_id` server-side; do not trust client payload.
- Default scoping via `TenantBaseModel`; `withoutTenantScope` allowed only for control plane and logged.

## 5. File Isolation Rules
- Storage path: `writable/tenants/{clinic_uuid}/...`
- No public URLs; access via streaming controller only.
- Streaming checks: auth, membership, `file.clinic_id == active_clinic_id`, and permission as applicable.

## 6. Background Jobs Rules
- Payload includes `clinic_id` (and actor if applicable); worker sets tenant context from payload.
- No session reliance. Outbound messages/events are logged with `clinic_id`.
- Rate limiting per clinic for messaging.

## 7. Authorization Model
- Single source: `PermissionService` / RBAC tables.
- IonAuth groups are not used for authorization paths.
- “Admin” is a permission/role, not a group; `super_admin` is control-plane only (or via impersonation flows).

## 8. Phases, Task IDs, and Definition of Done
_Phase numbering aligns with `SaaS_Tenant_Isolation_Execution_Plan.md`; instantiate Task IDs per phase._
- **Phase 0 — SaaS Scaffold & Guardrails**
  - DoD: CI guards defined (ban DOM sinks; ban raw tenant queries in controllers; ban group-based auth); SaaS docs imported; repo split/freeze rules documented.
  - Verification: CI rules present; `docs/SaaS/verification/S0-*.md` per task.
- **Phase 1 — RBAC Consolidation**
  - DoD: `AdminFilter` removed; `ControlPlaneFilter` added; routes regrouped; no `in_group`/`is_admin` checks remain.
  - Verification: grep proof for removed group checks; permission denial behavior validated.
- **Phase 2 — Clinic Context & Selection Wall**
  - DoD: `clinics` and `user_clinics` tables; post-login clinic resolution; wall for >1 clinics; block if 0; clinic switch endpoint + audit; header dropdown.
  - Verification: access blocked without `active_clinic_id`; session regenerates on switch; audit event recorded.
- **Phase 3 — Schema Tenancy**
  - DoD: `clinic_id` added/backfilled/NOT NULL with composite uniques; `patients.user_id` added/enforced; `clinic_id` on RBAC assignments (except `super_admin` global).
  - Verification: `COUNT(*) WHERE clinic_id IS NULL == 0`; indexes present.
- **Phase 4 — TenantBaseModel & Query Refactor**
  - DoD: tenant models extend `TenantBaseModel`; raw queries replaced/scoped; scoped find helpers in use; CI guard catching unscoped queries.
  - Verification: isolation tests for datatables/search/calendar/export; CI grep passes.
- **Phase 5 — Private File Storage**
  - DoD: tenant storage service; uploads moved to private; file registry; streaming controller with tenant checks; no public links.
  - Verification: direct path access fails; cross-tenant fetch returns 404.
- **Phase 6 — Background Jobs & Messaging**
  - DoD: jobs table; worker with tenant context; reminders/WhatsApp per clinic; audit/delivery logs.
  - Verification: jobs without `clinic_id` are rejected; cross-tenant delivery impossible in tests.

## 9. CI/QA Guardrails (must exist before ship)
- Pattern bans:
  - DOM sinks: `innerHTML`, `outerHTML`, `insertAdjacentHTML`, `.html(` in app code.
  - Raw tenant table queries in controllers without `clinic_id` scoping.
  - Group-based auth checks (`in_group`, `is_admin`, `groups`).
- Required tests:
  - Tenant isolation for datatables/search/calendar feeds.
  - Export scoping and permission enforcement.
  - File access denial across clinics.
  - Job execution tied to `clinic_id`.
- Lint/check: composite unique/index presence for `clinic_id` where specified.

## 10. Documentation & Verification Requirements
- Each Task ID produces a verification doc under `docs/SaaS/verification/<TASK_ID>.md` with commands/evidence.
- Logs are append-only in `docs/SaaS/logs/`.
- Supporting references: `ImplementationPlan.md`, `MigrationChecklist.md`, `MULTI_TENANCY_RBAC_MIGRATION.md`, `TENANT_CONTEXT_CONTRACT.md`, `SaaS_Tenant_Isolation_Execution_Plan.md`, `ScopeOverview.md`.

## 11. Append-Only Rules
- No in-place edits to historical log entries; corrections are appended blocks.
- This file is the single source of truth; changes are append-noted or amended with explicit dated sections if needed.

## 12. References
- docs/SaaS/ImplementationPlan.md
- docs/SaaS/MigrationChecklist.md
- docs/SaaS/MULTI_TENANCY_RBAC_MIGRATION.md
- docs/SaaS/TENANT_CONTEXT_CONTRACT.md
- docs/SaaS/SaaS_Tenant_Isolation_Execution_Plan.md
- docs/SaaS/ScopeOverview.md

## Append-Only Corrections & Decisions — 2026-01-16
### Storage Path Correction (Supersedes Section 5)
- **Authoritative storage path:** `writable/uploads/clinic_{clinic_id}/...`
- **Note:** Section 5 wording (`writable/tenants/{clinic_uuid}`) is legacy and does not match the implemented storage service.

### P5-11 Tenant Onboarding (Completed)
- **Status:** PASS (see `docs/SaaS/verification/P5-11.md`)
- **Summary:** Control-plane onboarding creates clinic + admin user + membership + subscription + plan audit in one transaction.
- **Decisions:**
  - Initial clinic admin is a **normal tenant user** (not superadmin).
  - **Plan selection is mandatory**; no default plan is auto-assigned.

### Tenant Model Base Clarification
- **Current base model:** `TenantAwareModel` (codebase implementation).
- **Note:** Any `TenantBaseModel` references in earlier sections are legacy naming. The accepted base is `TenantAwareModel` unless explicitly renamed in a future task.

### [2026-01-16] Pivot to Manual SaaS Model
*   **Decision:** We are deferring automated billing (Stripe) and subdomain routing.
*   **New Direction:** The system will operate as a "Single App" (Path-based context).
*   **Billing:** Handled via external invoicing; Plans managed manually by Super Admin.
*   **Impact:** "Phase 4" requirements for automated subscription handling are superseded by the "Manual Onboarding SOP".

### S0-05, S0-06, S0-07 Notification & Preferences (Completed)
- **Status:** PASS (see `docs/SaaS/verification/S0-*.md`)
- **Summary:** Notification crash fixed, per-clinic user preferences enabled (Schema/Persistence), and SMTP configuration UX hardened.
- **Decisions:**
  - `clinic_users.preferences` JSON column is the source of truth for per-clinic user settings.
  - SMTP configuration uses encrypted JSON storage but is presented via a structured form.

### S0-08, S0-09 Persistence & Compliance Fixes (Completed)
- **Status:** PASS
- **Summary:** Fixed checkbox normalization (0/1), removed fatal IonAuth service call, and enforced ASCII limits in UI.
- **Decisions:** Unchecked preferences are explicitly stored as `0`.

### S0-10 Verification Alignment (Completed)
- **Status:** PASS
- **Summary:** Aligned verification documents with Docker environment commands and confirmed routing paths.
- **Decisions:** No routing changes were required; existing paths were correct.

### S0-11 Route Correction (Completed)
- **Status:** PASS
- **Summary:** Adjusted form action and redirect paths to remove `settings/` prefix as per strict instruction.
- **Decisions:** Route paths updated to `notifications/update` and `/notification-settings`.

### S0-12 Documentation Route Corrections (Completed)
- **Status:** PASS
- **Summary:** Corrected verification doc path and clarified log entries for S0-10/S0-11 route statements. No code changes introduced.

### S0-13 Log Wording Correction (Completed)
- **Status:** PASS
- **Summary:** Corrected log wording to avoid unverified Routes.php assertions; no code changes.

### S0-14 Control Plane UI Refactor (Completed)
- **Status:** PASS
- **Summary:** Standardized Control Plane UI with `main_control_plane` layout (Dark Sidebar) and added live active clinic metrics.
- **Decisions:** Control Plane uses a Slate-900 sidebar to visually distinguish it from the Tenant Plane (White/Blue).

### S0-15 Control Plane Asset Safety (Completed)
- **Status:** PASS
- **Summary:** Confirmed Control Plane layout is free of tenant-heavy assets and corrected log ordering.
- **Decisions:** `main_control_plane` is the standard layout for all `/controlplane/*` routes.

### S0-16 Log Ordering + rg Standardization (Completed)
- **Status:** PASS
- **Summary:** Restored S0-12 status line and updated S0-15 verification commands to use rg.

### S0-17 Log Integrity Corrections (Completed)
- **Status:** PASS
- **Summary:** Restored missing S0-12 status line and re-added the S0-06/S0-07 runtime evidence block via append-only correction; updated S0-15 verification header to match rg usage.

### P5-21 Subscription Enforcement & Plan Gatekeeping (Completed)
- **Status:** PASS
- **Summary:** Implemented strict subscription standing and quota enforcement with 404 concealment. Centralized logic in `SubscriptionFilter` and `PlanGuard`.
- **Decisions:** All subscription/plan violations present as 404 to the user but log forensic details for audit. Order by latest expiry for subscription standing.

### P5-21b Subscription Correctness Fixes (Completed)
- **Status:** PASS
- **Summary:** Reinforced 404 concealment in `Patient::store`, unified subscription lookup in `PlanGuard`, and enhanced forensic log metadata.
- **Decisions:** Quota violations MUST surface as 404 to maintain endpoint concealment. `PlanGuard` implements standing invariant: re-checks only for non-web calls.

### P5-21b Subscription Correctness Fixes (Completed)
- **Status:** PASS
- **Summary:** Reinforced 404 concealment in `Patient::store`, unified subscription lookup in `PlanGuard`, and enhanced forensic log metadata.
- **Decisions:** Quota violations MUST surface as 404 to maintain endpoint concealment. Standings checks in `PlanGuard` now use deterministic expiry ordering.

### P5-06 Tenant-Aware Job Runner (Completed)
- **Status:** PASS
- **Summary:** Background jobs enforce `clinic_id` context via `RunTenantJob` command and `TenantJob` library.
- **Decisions:** Jobs must be invoked with `--clinic-id`.

### P5-07 Enforce TenantAwareModel & Scoped Queries (Completed)
- **Status:** PASS
- **Summary:** `TenantAwareModel` implements auto-scoping via model events. `UserManagementController` refactored to use scoped user lookups.
- **Decisions:** Tenant controllers rely on `TenantAwareModel` auto-scoping where applicable, or explicit checks for global models.

### P5-07a Duplicate Method Fix (Completed)
- **Status:** PASS
- **Summary:** Removed duplicate methods in `UserManagementController` and consolidated scoped logic.
- **Decisions:** Codebase compiled state restored.

### P5-08 Data Integrity & Evidence (Completed)
- **Status:** PASS
- **Summary:** Enforced valid `role_id` in `UserManagementController::store` to prevent invalid clinic memberships. Annotated P5-06 verification with deferred evidence note.
- **Decisions:** Users created in tenant context MUST have at least one role assigned.

### P5-06/07 Fail-Closed Update (Completed)
- **Status:** PASS
- **Summary:** Reinforced `TenantAwareModel` with strict exception-throwing if context is missing. Control Plane console explicitly exempted.
- **Decisions:** Unscoped queries in tenant context are now runtime errors.

### P5-07a Tenant Context 404 Mapping (Completed)
- **Status:** PASS
- **Summary:** Updated `TenantAwareModel` to throw `PageNotFoundException` instead of `RuntimeException` when tenant context is missing, ensuring correct HTTP 404 response. Added logging for audit trail.
- **Decisions:** Fail-closed events must appear as 404 to the user but logged as errors.

### P5-07b Evidence Remediation (Completed)
- **Status:** PASS
- **Summary:** Updated verification documents to include concrete CLI outputs and code evidence matching the P5-07a changes.

### P5-07c Evidence Repair (Completed)
- **Status:** PASS
- **Summary:** Replaced simulated verification evidence with verbatim captured artifacts for CLI, Log, and HTTP 404 behavior.
- **Decisions:** Audit trails must contain real output, not asserted results.

### P5-07c Evidence Completeness Correction (Completed)
- **Status:** PASS
- **Summary:** Updated P5-07 wording to explicitly reference `PageNotFoundException::forPageNotFound()` and removed simulated negative-path output from P5-06 (deferred).
- **Decisions:** Verification docs must contain only verbatim evidence blocks or explicit deferrals.

### P5-07c Evidence Correction (Completed)
- **Status:** PASS
- **Summary:** Removed simulated negative-path evidence from P5-06 and added missing 302 redirect evidence to P5-07. Clarified exception wording.

### P5-08b Fail-Closed Role Enforcement (Completed)
- **Status:** PASS
- **Summary:** Added a hard fail-closed guard to block user creation when roles are missing/invalid, preventing orphaned users without clinic membership.
- **Decisions:** User creation is aborted before registration if role selection is invalid.

### S0-19 Reader Advisory (Completed)
- **Status:** PASS
- **Summary:** Appended a reader advisory to the implementation log clarifying that later correction blocks supersede earlier route statements.

### P5-21 Finalization Clarification (Completed)
- **Status:** PASS
- **Summary:** Corrected duplication in logs and clarified that `app/Controllers/Patient.php` is part of the enforcement chain to maintain 404 concealment. Commit(s): [Pending].

### P5-21d Forensics + Audit Clarification (2026-01-17)
- **Clarification:** Earlier P5-21b blocks are duplicated and may be confusing. The effective P5-21 invariant behavior (web assumes SubscriptionFilter; PlanGuard re-checks standing only for CLI/jobs; missing clinicId in web logs PLAN_GUARD_CONTEXT_MISSING and 404s) is implemented in commit 4e34799.
- **Status:** P5-21 verification remains PENDING until runtime evidence is captured.
- **Note:** This clarification is append-only; no prior entries were modified.

### P5-21e Audit Placeholder Correction (2026-01-17)
- **Clarification:** The earlier 'Commit(s): [Pending]' placeholder is now outdated.
- **Authoritative Commits for P5-21:**
  - 4e34799 � P5-21 base implementation (subscription gate + quota enforcement)
  - 6bc9272 � P5-21 docs/audit finalization
  - f6a8ab8 � P5-21d PlanGuard log enrichment + audit clarification
- **Status:** Verification remains PENDING until runtime evidence is captured.
- **Note:** This clarification is append-only; no prior entries were modified.

### P5-21 Verification Status Update (2026-01-17)
- **Status:** PENDING (Partial evidence captured)
- **Note:** Missing context gate curl evidence captured verbatim. Log evidence (SUBSCRIPTION_CONTEXT_MISSING) remains pending; TENANT_CONTEXT_MISSING appeared instead during initial capture.

### P5-21 SQL Syntax Fix (2026-01-17)
- **Status:** PASS (Code fix verified via static analysis)
- **Decision:** Disabled SQL escaping for complex CASE statements in ClinicSubscriptionModel to prevent query corruption.

### P5-21 Schema Alignment (2026-01-17)
- **Status:** PASS
- **Decision:** Removed trial_ends_at and trial status logic as they are not present in the current database schema. Unified end_at as the primary expiry field.

### P5-21f Schema Alignment & Deterministic Selection (2026-01-17)
- **Status:** PENDING (Evidence needed)
- **Decision:** Removed trial_ends_at logic as it is not in the schema. Subscription selection is now deterministic: status='active', ordered by end_at DESC, id DESC. Logged 
o_valid_subscription reason for missing subscriptions.

### P5-21g Standing Selection Fix (2026-01-17)
- **Status:** PENDING
- **Decision:** Restricted getCurrentSubscription to status='active' only. Removed legacy 	rial_ends_at references from code and documentation to align with the actual database schema.
