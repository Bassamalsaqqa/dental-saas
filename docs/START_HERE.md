# Documentation Start

This is the authoritative map. Read only the documents relevant to the task; removed historical material in Git is not current direction.

## Start Here

| Need | Source |
|---|---|
| Current implementation and next work | `PROJECT_STATE.md` |
| Recent implementation history | `CHANGELOG.md` |
| Durable architecture/product decisions | `DECISIONS.md` |
| Local/deployment operations | `OPERATIONS_RUNBOOK.md`, repository `README.md` |
| SaaS governance and tenant isolation | `SaaS/SAAS_PROGRAM.md`, `SaaS/TENANT_CONTEXT_CONTRACT.md` |
| Public API | `API_CONTRACT.md` |
| First-party React App API | `APP_API_CONTRACT.md`, `refactor/APP_API_ROUTE_INVENTORY.md` |
| Modernization Program & Roadmap | `refactor/DENTA_PLUS_HARDENING_PERFORMANCE_PROGRAM.md` |

## Module State

| Domain | Source |
|---|---|
| Clinical and odontogram | `CLINICAL_CURRENT_STATE.md`, `refactor/PATIENT_CLINICAL_WORKFLOW_PLAN.md` |
| Clinical/finance traceability | `FINANCE_CURRENT_STATE.md`, `refactor/CLINICAL_FINANCE_TRACEABILITY_PLAN.md` |
| Cross-module workflow roadmap | `refactor/PRODUCT_WORKFLOW_OPTIMIZATION_ROADMAP.md` |
| WhatsApp | `WHATSAPP_CURRENT_STATE.md` |
| Entity workspaces/drawers | `refactor/ENTITY_CENTERED_COMMAND_WORKSPACE_PLAN.md` |
| React/Lovable strategy | `refactor/LOVABLE_STRATEGY_CODEX.md` |
| Dynamic doctor scheduling | `SaaS/DOCTOR_SCHEDULING_CONTRACT.md` |
| Scheduling readiness and Owner integration | `SaaS/PRODUCTION_READINESS_2026-10-16.md`, `SaaS/STAGING_CERTIFICATION_RUNBOOK.md`, `SaaS/OWNER_INTEGRATION_AND_READINESS.md` |
| Provider payout lifecycle and native currency (deployed M2; PR18 approved) | `M2_PROVIDER_PAYOUT_EVIDENCE_20261021.md` |
| Contract consent publication/recovery (deployed M2; final external review Owner-waived) | `M2_CONTRACT_SIGNING_EVIDENCE_20261021.md` |
| M2-C invoice corrections: adopted policy and draft evidence | `M2C_INVOICE_CORRECTION_DISCOVERY.md`, `M2C_INVOICE_CORRECTION_POLICY.md`, `M2C_IMPLEMENTATION_EVIDENCE_20261021.md`, `M2C_RELEASE_PROCEDURE.md` |
| Control-plane entitlements | `SaaS/CP-01_CONTROL_PLANE_ENTITLEMENTS_CONTRACT.md` |


## Assistant Guides

- `../AGENTS.md`: Codex operating rules.
- `../gemini.md`: Gemini operating rules.

## Documentation Rules

1. Contracts keep necessary detail; summaries link to them instead of repeating them.
2. `PROJECT_STATE.md` states current truth. Do not turn it into a chronological log.
3. `CHANGELOG.md` retains recent continuation context only. Git history preserves older entries.
4. `DECISIONS.md` is append-only for durable decisions.
5. Delete completed studies, audits, prompts, and obsolete verification files after current conclusions are recorded; Git preserves history.
6. New active documents must be added to this map.
7. Guardrail allowlists under `SaaS/guardrails/` are configuration and must remain in place.

## Historical Material

The working tree intentionally contains no documentation archive. Use Git history only when historical evidence is necessary, then confirm every conclusion against this active map and current code.

- [P0 identity/RBAC review, deployment boundary and rollback (2026-10-15)](SaaS/P0_IDENTITY_RBAC_REVIEW_2026-10-15.md) — review branch evidence; not a deployment authorization.
