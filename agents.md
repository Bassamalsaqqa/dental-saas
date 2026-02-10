# Codex Operating Guide - SaaS Program (Planner/Reviewer)

## A) Role Definition (Codex = Planner/Reviewer)
- Primary mission: select Task IDs, sequence work, generate Gemini prompts, and review changes against the SaaS spec.
- Secondary mission: enforce evidence-based verification, prevent regression/drift, and maintain an audit-grade trail.

## B) Single Source of Truth
1) `docs/SaaS/SAAS_PROGRAM.md` governs SaaS scope, priority, and DoD.
2) `docs/SaaS/TENANT_CONTEXT_CONTRACT.md` governs tenant context mechanics.
3) `docs/API_CONTRACT.md` governs `/api/v1/*` semantics.
4) Guardrails: `docs/SaaS/guardrails/*` + repo guardrail scripts.

`docs/SECURITY_HARDENING_PROGRAM.md` is historical unless explicitly reopened.

## C) Non-Negotiable Rules
- Codex assigns Task IDs explicitly each iteration (1–3 max).
- Gemini must not expand scope; any adjacent issues are “Findings” only.
- Reviews must be evidence-based (paths, diffs, rg output, curl output).
- Fail-closed doctrine always.
- No redirects/HTML for `/api/*`.
- Airlock separation enforced: control plane vs tenant plane.

## D) Session Start Checklist
1) Read `docs/SaaS/SAAS_PROGRAM.md` and latest SaaS logs:
   - `docs/SaaS/logs/IMPLEMENTATION_LOG_SaaS.md`
   - `docs/SaaS/logs/DECISION_LOG_SaaS.md`
2) Run drift scans (examples; adjust to your scripts):
   - `rg -n "innerHTML|outerHTML|insertAdjacentHTML|\.html\(" app/Views`
   - `php spark routes | findstr /I "controlplane api/v1 api/search"`
   - `bash scripts/ci/saas_guardrails.sh` (if present)
3) Confirm clean tree:
   - `git status -sb`

## E) Prompt Template (Codex → Gemini)
Task IDs: [P5-XX, CP-XX, API-BX-XX]

Scope/DoD:
- [paste DoD from docs/SaaS/SAAS_PROGRAM.md or API_CONTRACT.md]

Files to touch:
- [explicit list]

Files to avoid:
- [explicit list]

Expected behavior:
- [bullet list]

Verification requirements:
- [exact commands + expected outputs]

Documentation updates required:
- docs/SaaS/verification/<TASK_ID>.md
- docs/SaaS/logs/IMPLEMENTATION_LOG_SaaS.md (append)
- docs/SaaS/logs/DECISION_LOG_SaaS.md (append, if decision made)

Constraints:
- No scope creep. Fail-closed only. No API redirects. Respect airlock doctrine.

## F) Environment & Infrastructure
**Local Path:** `C:\xampp\htdocs\dental-saas`

1. Docker Container Info
   - **Container Name:** `mysql8-dental`
   - **Image:** `mysql:8.0`
   - **Status:** Running (Port Mapping: `0.0.0.0:3307->3306/tcp`)
   - **Container ID:** `7eb3024aa7d5`

2. Active `.env` Configuration
   - **Environment:** `development`
   - **Base URL:** `http://localhost/dental-saas/`
   - **Database Host:** `127.0.0.1`
   - **Database Port:** `3307`
   - **Database Name:** `democa_dental_saas`
   - **DB Credentials:** `root` / `root`
   - **Encryption Key:** `9f3a2c1d8b7e6a5c4d3e2f1a0b9c8d7e`
- **Session Cookie:** `ci_session_saas`

## G) Recent State Snapshot (for fast catch-up)
- **Control Plane surface is fully modernized with AJAX-driven workflows.** Registry pages (Users, Clinics, Plans) now support zero-reload actions for Delete, Terminate, Suspend, and Status Toggles. These pages use `fetch` API and `DataTable.ajax.reload(null, false)` to maintain pagination and scroll position after updates.
- **UI/UX Standardization:** All Control Plane statistics cards follow a uniform high-fidelity design pattern (Source of Truth: Clinic Dashboard). This includes glassmorphism, animated progress bars, vibrant solid-color themes for neutral/negative statuses (All Time, Rejected), and top-right status badges. Header search/filter bars are unified into a single responsive line.
- **RBAC & Scoping:** Access is centralized in `RoleController` and `PermissionService`. System roles (`clinic_id IS NULL`) and tenant roles are unified under `/roles`. Control Plane routes are airlocked via `TenantFilter` and require `global_mode=true`.
- **Clinic Settings Fix:** Resolved a structural regression in `app/Views/settings/index.php` where broken HTML nesting hid the "Subscription Plan" menu. The settings interface is now correctly partitioned and functional.
- **Logs:** `docs/SaaS/logs/IMPLEMENTATION_LOG_SaaS.md` and `DECISION_LOG_SaaS.md` capture specific task history. Sections G and H of this file, combined with `gemini.md`, provide the current architectural "source of truth."

## H) Catch-up Checklist (for future sessions)
1. Read sections A–H of this file and `gemini.md` first. These provide the authoritative map of the frontend, backend, and database schema as of Jan 28, 2026.
2. For UI work, always match the new standardized pattern: `backdrop-blur-xl bg-white/80` for cards, animated progress bars for metrics, and `indigo-700` solid themes for neutral "All Time" metrics.
3. For Control Plane actions, always prefer AJAX (fetch) over form redirects. Ensure `refreshCsrfToken()` is called after each background operation to keep security headers in sync.
4. Verify all ID-based operations are scoped by `active_clinic_id` (Tenant Plane) or protected by `global_mode` (Control Plane).
5. Guardrails (`bash scripts/ci/saas_guardrails.sh`) remain active; if environment issues (Windows/WSL) persist, prioritize manual code inspection for DOM sinks and raw queries.

## I) Deployment Note
- Keep `democa_dental_saas_schema_superadmin.sql` updated whenever schema or superadmin bootstrap data changes.
- Ensure the dump includes the IonAuth default group `members` (in addition to `admin`) to avoid "Invalid default group name set" errors.

## J) Recent Updates (Feb 10, 2026)
- **Activity Logs (Tenant + Control Plane):**
  - Tenant Activity Log now supports name/email user filtering, correct filtered/total counts, and CSV export at `activity-log/export`.
  - Control Plane Activity Log has CSV export at `controlplane/activity-log/export` and UI aligned to the tenant version (max width, shaded event badges, 250 rows/page).
  - Task IDs: `P5-AL-01`, `CP-AL-01`, `CP-AL-02`.
- **Kiosk Guardrail Fix:** Removed all `innerHTML` DOM sinks in `app/Views/kiosk/check_in.php` to satisfy guardrails. Task ID `P5-KIOSK-03`.
- **Forgot Password (Tenant SMTP):**
  - Forgot-password now sends using **clinic SMTP** (per-clinic channel config), not global config.
  - If SMTP not configured, requester sees a message including **platform support email** (from control plane settings) and an activity log entry is written (`entity_type=auth`, `action=password_reset_failed`).
  - Forgot-password page displays the **clinic admin email** (tenant) when available.
  - Reset email link now uses `/auth/reset-password/{code}`.
  - Redirects in Auth controller standardized to hyphenated routes.
  - Task IDs: `P5-AUTH-01` to `P5-AUTH-04`.
