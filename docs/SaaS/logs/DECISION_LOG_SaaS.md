# SaaS Decision Log (Append-Only)

## 2026-01-13 — Establish SaaS Source of Truth
- Decision: Created `SAAS_PROGRAM.md` as the canonical SaaS governance doc under `docs/SaaS/`. All SaaS tasks must reference it.
- Rationale: Centralize non-negotiables, contracts, phases, and guardrails for the multi-tenant track; avoid divergence from supporting plans.
- Impact: Future SaaS work must add Task IDs and verification under `docs/SaaS/`, and log decisions/implementation in this folder only.

## 2026-02-05 — Attachment Limits & Feature Gating
- Decision: Enforce attachment access and per-file size strictly via plan settings (`attachments.enabled`, `attachments_max_file_mb`) with no clinic-level override.
- Rationale: Keep SaaS enforcement centralized and deterministic; align storage usage with plan-level commercial constraints.
- Impact: All patient attachment operations are gated by plan features/limits. Storage caps use `storage_gb` at plan level.

## 2026-02-05 — Contract Document Storage
- Decision: Store signed contract HTML via `StorageService::storeGenerated` (no export feature gate) while still enforcing `storage_gb` quotas.
- Rationale: Contracts are core clinical records and must remain available regardless of export feature toggles.
- Impact: Signed contract artifacts persist under tenant storage and are downloadable via `file/download/{id}`.

## 2026-02-07 ? Kiosk Check-In Access Model
- Decision: Kiosk check-in uses a clinic-specific URL (`/kiosk/{clinic_uuid}/check-in`) protected by a clinic PIN configured in settings. Branding strings (header/subheader/footer note) are configurable per clinic.
- Rationale: Static kiosk link is simple for in-clinic devices, while the PIN prevents unauthorized use if the URL leaks.
- Impact: Kiosk access is fail-closed unless the clinic is active, kiosk is enabled, and a PIN is set.

## 2026-02-07 ? Kiosk Feature Gating
- Decision: Kiosk check-in is controlled by a plan feature flag (`features.kiosk.enabled`) and remains fail-closed even if the clinic settings toggle is enabled.
- Rationale: Feature access must be commercial-plan driven and enforced in public kiosk endpoints.
- Impact: Clinics without the feature cannot enable or access kiosk check-in.
### [2026-02-07] Revenue Recognition Basis for Tenant Finance
- **Decision:** Revenue metrics are computed on **payments received** (standalone payments + invoice payments) minus refunds. Invoices do not count as revenue until paid.
- **Rationale:** Matches cash-basis expectations in small clinics and supports partial payments without double-counting.
- **Impacted Areas:** Finance stats, monthly revenue, reports, dashboard.

## 2026-02-09 — Control Plane Settings Scope
- Decision: Control plane settings manage platform identity and governance (retention policies) only; tenant feature availability remains exclusively managed through plan configuration to avoid duplicate toggles.
- Rationale: Prevent inconsistent feature states and keep commercial entitlements centralized in plan management.
- Impact: Control plane settings page links to plan management for feature access changes while retaining global retention policy controls.
