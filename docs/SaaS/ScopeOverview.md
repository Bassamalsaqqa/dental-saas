# Multi-Tenant SaaS Scope Overview

## Vision
We are splitting the existing single-clinic dental app into two parallel products:
- **Legacy Single-Clinic** – frozen in the current codebase; no new feature work after the split.  
- **SaaS Multi-Tenant** – delivered in a new repo clone with explicit tenancy controls, RBAC-only authorization, and tenant-safe UX.

## Short‑Run Scope (this repo)
- Document the SaaS migration requirements (`ImplementationPlan.md`, `MigrationChecklist.md`).
- Harden the existing single-tenant code to make it safe for “freeze” (audit logging, backups control, no new feature work).  
- Capture the SaaS conversion requirements so the new repo doesn’t need re-explaining (tenant context, clinic_id schema, RBAC, patient onboarding rules, exports/audit expectations).

## Long‑Run Scope (new SaaS workstream)
- Row-level tenancy with `clinic_id` on every tenant table and tenant-aware query layer (`TenantBaseModel` or equivalent).
- RBAC-only authorization with `super_admin` control-plane mode, tenant-specific permissions, and filtered filters (`TenantFilter`, `ControlPlaneFilter`, `PermissionFilter`).
- Tenant-safe patient workflow (per-clinic email uniqueness, receptionist synthetic emails, enforced WhatsApp, patient-user 1:1 mapping).
- Audit/export controls: audit logs include `clinic_id`, exports/backups gated by RBAC, and synthetic emails used only when admin-created.
- Documentation + verification guardrails (per-tenancy checklists and CI grep rules).

## Hand‑off notes
- Use these docs as the single source of truth for the SaaS clone; no need to re-state the decisions when you start the new repo.  
- The new repo should import `docs/SaaS/*.md` as part of its onboarding so every developer understands the rules before coding.  
- After the clone, this repo becomes a maintenance snapshot; treat it as historical/reference only.
