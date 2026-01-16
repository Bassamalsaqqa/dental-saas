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
