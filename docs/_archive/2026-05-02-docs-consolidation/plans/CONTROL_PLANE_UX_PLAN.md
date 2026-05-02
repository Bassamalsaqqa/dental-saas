# Control Plane UI/UX Rebuild Plan

Status: Draft (proposed)
Owner: Codex (Planner/Reviewer)
Date: 2026-01-22

## Goal
Make the Control Plane UI/UX parity-grade with the Clinic UI/UX: consistent layout, navigation, and component system, with a clear admin information architecture and safe operator workflows.

## Decision
We will rebuild the Control Plane frontend on a single, consistent layout (recommended: `app/Views/layouts/main_control_plane.php`) and remove the fragmented legacy layout (`app/Views/layouts/control_plane.php`). This is a deliberate reset to eliminate drift and reduce long-term maintenance risk.

## Guiding Principles
- Single layout and asset pipeline for all Control Plane screens.
- Consistent navigation and page structure across all Control Plane routes.
- No backend behavior change unless explicitly required by a task.
- Evidence-based verification for each task (routes + screenshots or curl + rg output).
- Preserve existing security guardrails (controlplane filter, global_mode, fail-closed).

## Scope (This Plan)
- Control Plane UI/UX only.
- No changes to tenant clinic UI/UX (already mature).
- No changes to authentication or controlplane gate logic.
- No API contract changes unless separately approved.

## Phase 0 ? Inventory & Freeze (CP-UX-00)
Objective: establish a baseline before changes.

Tasks:
1) Catalog current Control Plane screens, routes, and layouts.
2) Confirm global navigation targets and identify broken links.
3) Record current visual inconsistencies.

DoD:
- Verified list of Control Plane views/routes/layouts.
- Broken links documented (e.g., `/controlplane/plans`).
- Baseline screenshots of current CP pages.

Verification:
- `rg -n "controlplane" app/Views app/Controllers app/Config/Routes.php`
- `php spark routes | findstr /I "controlplane"`


## Phase 1 ? Layout Unification (CP-UX-01)
Objective: move all Control Plane pages to one layout and remove layout drift.

Tasks:
1) Choose the single layout: `app/Views/layouts/main_control_plane.php`.
2) Migrate all Control Plane views to use the chosen layout.
3) Remove or retire `app/Views/layouts/control_plane.php`.
4) Fix encoding artifacts (e.g., `???` in titles).

DoD:
- All Control Plane pages extend the same layout.
- No Tailwind CDN usage in Control Plane templates.
- Encoding issues resolved in CP templates.

Verification:
- `rg -n "layouts/control_plane|cdn.tailwindcss" app/Views/control_plane app/Views/layouts`
- Manual browse: /controlplane/dashboard, /controlplane/console, /controlplane/operations, /controlplane/settings, /controlplane/danger, /controlplane/onboarding/clinic/create


## Phase 2 ? Navigation & IA (CP-UX-02)
Objective: build a consistent Control Plane navigation with proper IA.

Target Navigation (proposed):
- Dashboard
- Clinics (Registry)
- Subscriptions
- Plans
- Usage & Quotas
- Audit & Events
- Operations
- Settings
- Danger Zone

Tasks:
1) Build CP nav structure in the unified layout.
2) Implement placeholder pages or routing stubs for missing modules (no functionality yet).
3) Remove broken links and ensure all nav items resolve.

DoD:
- All nav items route correctly without 404.
- No dead links in CP UI.

Verification:
- `php spark routes | findstr /I "controlplane"`
- Manual click-through of all nav items.


## Phase 3 ? Core CP Screens (CP-UX-03)
Objective: deliver baseline, admin-grade screens for core governance.

Screens:
- Clinics Registry
- Subscriptions Overview
- Plans Management (read-only for now)
- Audit/Event Viewer
- System Health (status + queues + storage)

DoD:
- Each screen renders and matches layout/IA.
- Data blocks are present and correctly scoped.
- No sensitive data leakage across tenants.

Verification:
- Screens render and show expected data.
- No unauthorized access (controlplane filter enforced).


## Phase 4 ? Operational Workflows (CP-UX-04)
Objective: bring operator workflows to parity with clinic UI.

Workflows:
- Onboarding clinic (existing)
- Suspend/restore clinic
- Set plan / subscription state
- View tenant usage

DoD:
- Workflows have consistent UI patterns.
- All workflows are auditable (log entries).

Verification:
- Evidence of workflow completion and logs.


## Phase 5 ? Polish & UX Parity (CP-UX-05)
Objective: make CP UI match clinic UX polish.

Tasks:
- Consistent typography, spacing, and component styling.
- Shared toast/alert system.
- Consistent empty states.

DoD:
- CP feels consistent with clinic UI and navigation.


## Risks & Mitigations
- Risk: breaking controlplane access flow.
  Mitigation: keep routing + filters unchanged; validate after each phase.
- Risk: UI drift from clinic.
  Mitigation: align to existing clinic layout patterns and component choices.

## Open Decisions
- Confirm willingness to retire `layouts/control_plane.php` entirely.
- Confirm desired CP navigation order and module names.

## Next Step (Upon Approval)
Start CP-UX-00 (Inventory & Freeze) and CP-UX-01 (Layout Unification).

