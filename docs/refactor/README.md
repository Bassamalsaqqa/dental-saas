# Refactor & Modernization Architecture Index

This directory contains durable domain contracts, workflow specifications, and modernization plans governing the evolution of DentaCare SaaS.

## Per-Document Architecture Disposition

| Document | Canonical Authority & Scope | Active Status & Consumers |
|---|---|---|
| [`CLINICAL_FINANCE_TRACEABILITY_PLAN.md`](CLINICAL_FINANCE_TRACEABILITY_PLAN.md) | **Canonical Financial Fact-Owner Chain:** Defines the immutable chain from examination/procedure to clinical charge line, invoice, payment allocation, provider payout, and inventory COGS. Prevents inferring billing from unsaved UI state. | **Active Contract.** Cited by [`docs/DECISIONS.md`](../DECISIONS.md#2026-05-19---clinical-finance-traceability-requires-charge-capture-layer) and [`docs/FINANCE_CURRENT_STATE.md`](../FINANCE_CURRENT_STATE.md). |
| [`ENTITY_CENTERED_COMMAND_WORKSPACE_PLAN.md`](ENTITY_CENTERED_COMMAND_WORKSPACE_PLAN.md) | **Contextual Command Workspaces & Drawers:** Defines the quick-create drawer pattern (Patient Profile reference implementation). Mandates that drawers submit to canonical web routes preserving CSRF, tenant context, RBAC, and server-owned calculation. | **Active Contract.** Cited by [`docs/DECISIONS.md`](../DECISIONS.md#2026-05-06---entity-workspaces-use-contextual-quick-create-drawers) and [`docs/UI_STUDIO_COMPONENTS.md`](../UI_STUDIO_COMPONENTS.md). |
| [`PATIENT_CLINICAL_WORKFLOW_PLAN.md`](PATIENT_CLINICAL_WORKFLOW_PLAN.md) | **Clinical Encounter & Procedure Contracts:** Details examination governance, signed clinical note immutability, treatment catalog integration, and remaining clinical correction/reopen/perio requirements. | **Active Contract.** Cited by [`docs/CLINICAL_CURRENT_STATE.md`](../CLINICAL_CURRENT_STATE.md). |
| [`PRODUCT_WORKFLOW_OPTIMIZATION_ROADMAP.md`](PRODUCT_WORKFLOW_OPTIMIZATION_ROADMAP.md) | **Cross-Module Journey Optimization:** Sequences workflow improvements across intake, reception, clinical encounters, and checkout. | **Active Planning Contract.** Cited by [`docs/CLINICAL_CURRENT_STATE.md`](../CLINICAL_CURRENT_STATE.md). |
| [`APP_API_ROUTE_INVENTORY.md`](APP_API_ROUTE_INVENTORY.md) | **First-Party React API Route Ledger:** Inventories implemented `/app-api/v1/*` routes (CSRF, auth, bootstrap, clinic switch, patients) vs planned routes. Complements the core contract in `APP_API_CONTRACT.md`. | **Active Reference.** Cited by [`docs/START_HERE.md`](../START_HERE.md) and [`docs/APP_API_CONTRACT.md`](../APP_API_CONTRACT.md). |
| [`LOVABLE_STRATEGY_CODEX.md`](LOVABLE_STRATEGY_CODEX.md) | **React / Lovable Frontend Boundaries:** Establishes repository ownership, Vite build invariants (`base: '/app/'`, `outDir: '../public/app'`), and strict guardrails (no Supabase, no client auth tokens, no Bearer auth in React). | **Active Architectural Boundary.** Cited by [`docs/DEVELOPER_ONBOARDING.md`](../DEVELOPER_ONBOARDING.md). |
| [`DENTA_PLUS_HARDENING_PERFORMANCE_PROGRAM.md`](DENTA_PLUS_HARDENING_PERFORMANCE_PROGRAM.md) | **Modernization Program Context:** Clarifies the historical modernization roadmap stages vs deployed releases (PR20, PR22, PR27, PR28). Documents performance, security, and scheduling requirements. | **Active Program Context.** Cited by [`docs/SaaS/DOCTOR_SCHEDULING_CONTRACT.md`](../SaaS/DOCTOR_SCHEDULING_CONTRACT.md) and [`docs/CHANGELOG.md`](../CHANGELOG.md). |

## Invariant Rules
- Do not treat future planning items or roadmap phases as permission to bypass server-owned validation or invent accounting/clinical policy.
- Future policy gaps (e.g. procedure reopen workflows, provider overpayment adjustments, dynamic scheduling activation) remain explicitly unresolved until reviewed and authorized.
