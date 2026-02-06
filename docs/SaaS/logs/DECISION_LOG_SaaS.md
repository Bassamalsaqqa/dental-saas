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
