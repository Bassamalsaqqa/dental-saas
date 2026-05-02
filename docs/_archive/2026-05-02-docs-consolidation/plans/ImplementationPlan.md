# Multi-Tenant SaaS Implementation Plan

## Objective
Deliver row-level tenancy with RBAC-only authorization, clinic-aware settings, and tenant-safe patient onboarding so the single codebase can securely serve multiple clinics.

## Current Status
- RBAC filters, TenantFilter, and control-plane enforcement are in place (P5-02/P5-03/S0-03). Guardrails remain green (DOM=0, Group=0, Raw=8).
- Clinic context flows via session('active_clinic_id'), global_mode, and ctive_clinic_role_id. Models set clinic_id via TenantTrait during inserts.
- Patient flows rely on AJAX/Select2 pickers that query scoped search endpoints. Export endpoints now persist artifacts via StorageService into writable/uploads/clinic_{id} and gate downloads through /file/download/{attachmentId} (P5-05).
- Settings, roles, and permissions are system-managed; tenants cannot create roles or run syncs outside the control plane (S0-01/S0-03).

## Achievements
1. **Tenant filters + RBAC:** All tenant-plane controllers now run through uth, 	enant, and permission filters; impairment of IonAuth group helpers replaced by PermissionService. ensureControlPlane() guards role mutations.
2. **Clinic-scoped schema/data layer:** Major migrations added clinic_id to tenant tables, composite uniques, clinic_user pivot handling, and patient-user linkage. Settings now support per-clinic metadata via the composite index.
3. **Export persistence:** Every export/print endpoint enforces (id, clinic_id) ownership, persists the artifact via StorageService/file_attachments, and deletes previous attachments per purpose. Downloads go through the gated route.
4. **Guardrail completion:** Raw $db->table() builders removed from controllers; guardrail script passes with Raw=8.

## Outstanding Tasks
- **P5-06 (Tenant-aware job runner):** Design a background job framework where every queued task requires an explicit clinic_id (no implicit session). Provide a TenantJob base class and ensure cron exports/report generators accept clinic context.
- **P5-07 (TenantAwareModel base class):** Refactor existing models to inherit a base class or trait that automatically applies clinic_id to queries and exposes helpers like indByClinic, listByClinic. Update controllers to consume these helpers.
- **Retention job for exports:** Implement a scheduled cleanup that soft-deletes attachments older than e.g., 90 days or keeps only the latest N per (clinic_id, entity_type, purpose).
- **Audit/document invariants:** Codify in documentation the invariant rules (clinic_id required, roles system-only, exports gated) and ensure migration scripts always run in all environments.
- **S0-04 cleanup:** Remove test/debug endpoints from production controllers and move raw SQL operations (e.g., Settings maintenance) into control-plane services.

## Timeline Recommendation
1. Phase 5.06 (tenant job context, retention job) to avoid future cross-tenant background operations.
2. Phase 5.07 (TenantAwareModel) to guard against human error when writing new controllers.
3. S0-04 (cleanup remaining debug/test actions).
4. Continuous guardrail maintenance with updated logs as new endpoints are added.

## Deliverables to Track
- Tenant-aware job runner + retention scheduler (P5-06).
- TenantAwareModel base and scoped helper methods (P5-07).
- Documentation updates that articulate the invariants and export policy.
- Guardrail scripts consistently passing after each micro-task; include outputs in verification docs/logs. 
