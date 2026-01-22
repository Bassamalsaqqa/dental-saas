# Control Plane Roadmap (UX + Functionality)

Date: 2026-01-22
Owner: Codex Planner

## Purpose
Bring the Control Plane to feature parity with the Clinic Plane in UX quality and deliver the core control plane functionality expected in a SaaS: clinic lifecycle, subscriptions, plans/configurator, plan requests, and template management. The ERP SaaS screenshots in `docs/Control Plane/` are used as UX inspiration, not as functional requirements.

## Deprecation Notice
- The previous Control Plane UI is deprecated and replaced in place at `/controlplane`.
- New screens are being built under the same route and will be wired before removing any legacy UI code.

## Scope Principles
- Control Plane = global administration (airlock respected).
- Clinic Plane = tenant workflows (unchanged).
- No API behavior changes unless explicitly planned and documented.
- Fail-closed always.

## Target Information Architecture (Control Plane)
Navigation groups:
- Platform
  - Dashboard
- Tenants
  - Clinics (Manage Clinics)
  - Onboarding (Add Clinic)
  - Termination / Suspend
- Plans & Billing
  - Plans (Manage Plans)
  - Plan Configurator (Create/Edit Plan)
  - Plan Requests
  - Subscriptions (start/end, renew, cancel)
- Templates
  - Email Templates
- Governance
  - Console
  - Admin Tools
- System
  - Settings
  - Danger Zone

## UX Reference Mapping (from screenshots)
- Dashboard: KPI cards + summary chart + recent activity.
- Manage Clinics: card grid with status badges + quick actions.
- Add Clinic: modal form (can be full page in our implementation).
- Manage Plans: card layout with plan features, duration, price.
- Add Plan: form with plan limits/features/duration.
- Plan Requests: table with approve/reject actions.
- Email Templates: table list with preview/edit actions.
- Settings: multi-section form with toggles.

## Functional Roadmap (Phased)

### Phase 1 — Data Model & Services (backend readiness)
Goal: ensure the DB + models can express plans, quotas, subscriptions, clinic lifecycle.

Tasks:
1) CP-PLAN-01: Plan model enrichment
   - Add plan duration (days/months/years), patient limits, feature toggles, storage quota, etc.
   - Add plan status (active/inactive), visibility.
   - DoD: Plan entity supports all control plane fields; migrations + model + validation.

2) CP-SUB-01: Subscription lifecycle
   - Subscription start/end, renewal, status transitions.
   - Store reason for termination/suspension.
   - DoD: Deterministic transitions and audit log entries.

3) CP-CLINIC-01: Clinic lifecycle
   - Activate/suspend/terminate clinic with safe cascading (access + API tokens).
   - DoD: Termination revokes access, keeps data intact.

### Phase 2 — Control Plane UI (manage + configure)
Goal: deliver core control plane pages with clinic-grade UX.

Tasks:
4) CP-UX-20: Clinics Management (cards)
   - Grid card layout, status badges, quick actions (open, suspend, terminate, upgrade plan).
   - DoD: List, filter, and action panel present; no cross-tenant data.

5) CP-UX-21: Add Clinic / Onboarding UX
   - Wizard or single form aligned with clinic style.
   - DoD: Create clinic + initial subscription + owner user.

6) CP-UX-22: Plans Management
   - Cards per plan with price, duration, limits, features.
   - DoD: CRUD (create/edit/archive). Feature list visible.

7) CP-UX-23: Plan Configurator
   - Form for plan limits/features and trial settings.
   - DoD: Works with CP-PLAN-01; validates limits and duration.

8) CP-UX-24: Plan Requests
   - Table with approve/reject actions; audit log.
   - DoD: State updates and notifications (if available).

9) CP-UX-25: Email Templates
   - Table list + edit/preview modal.
   - DoD: Templates stored, preview rendered safely.

10) CP-UX-26: Settings
   - Sectioned settings with toggles (billing defaults, emails, branding).
   - DoD: Changes persisted + audit log.

### Phase 3 — Metrics + Analytics
Goal: operational dashboards.

11) CP-DASH-01: Dashboard metrics
   - Revenue, plan adoption, subscription churn, signups.
   - DoD: KPI cards and chart with server data source.

12) CP-AUDIT-01: Audit views
   - Extend console and admin tools for audit trail and export.
   - DoD: Filterable audit log with secure access.

## Verification Expectations
- Each task has a verification doc under `docs/SaaS/verification/`.
- Evidence: route checks, curl outputs, and DB evidence where needed.
- Zero cross-tenant leakage (explicit tests for clinic isolation).

## Dependencies
- Existing models: Clinic, User, Plan, Subscription.
- Control plane filter (`controlplane`) must remain enforced.
- Tenant context contract stays authoritative.

## Open Decisions (to be logged when chosen)
- Plan duration model: fixed duration vs recurring cycles.
- Plan limits schema: JSON vs columns.
- Plan requests workflow: who can submit from clinic plane.

## Next Actions (Recommended)
1) CP-PLAN-01 + CP-SUB-01 (backend readiness)
2) CP-UX-20 Clinics Management
3) CP-UX-22 Plans Management + CP-UX-23 Configurator
