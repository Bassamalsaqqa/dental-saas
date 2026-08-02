# Documentation Start

This is the authoritative map. Read only the documents relevant to the task; archived material is evidence, not current direction.

## Start Here

| Need | Source |
|---|---|
| Current implementation and next work | `PROJECT_STATE.md` |
| Recent implementation history | `CHANGELOG.md` |
| Durable architecture/product decisions | `DECISIONS.md` |
| Local/deployment operations | `OPERATIONS_RUNBOOK.md`, `DEPLOYMENT_LOCAL_SETUP.md` |
| SaaS governance and tenant isolation | `SaaS/SAAS_PROGRAM.md`, `SaaS/TENANT_CONTEXT_CONTRACT.md` |
| Public API | `API_CONTRACT.md` |
| First-party React App API | `APP_API_CONTRACT.md`, `refactor/APP_API_ROUTE_INVENTORY.md` |

## Module State

| Domain | Source |
|---|---|
| Clinical and odontogram | `CLINICAL_CURRENT_STATE.md`, `refactor/PATIENT_CLINICAL_WORKFLOW_PLAN.md` |
| Clinical/finance traceability | `FINANCE_CURRENT_STATE.md`, `refactor/CLINICAL_FINANCE_TRACEABILITY_PLAN.md` |
| Cross-module workflow roadmap | `refactor/PRODUCT_WORKFLOW_OPTIMIZATION_ROADMAP.md` |
| WhatsApp | `WHATSAPP_CURRENT_STATE.md` |
| Entity workspaces/drawers | `refactor/ENTITY_CENTERED_COMMAND_WORKSPACE_PLAN.md` |
| React/Lovable strategy | `refactor/LOVABLE_STRATEGY_CODEX.md` |
| Control-plane entitlements | `SaaS/CP-01_CONTROL_PLANE_ENTITLEMENTS_CONTRACT.md` |

## Assistant Guides

- `../AGENTS.md`: Codex operating rules.
- `../gemini.md`: Gemini operating rules.

## Documentation Rules

1. Contracts keep necessary detail; summaries link to them instead of repeating them.
2. `PROJECT_STATE.md` states current truth. Do not turn it into a chronological log.
3. `CHANGELOG.md` retains recent continuation context only. Move older entries to a dated archive.
4. `DECISIONS.md` is append-only for durable decisions.
5. Completed studies, audits, prompts, and verification evidence belong under `_archive/`.
6. New active documents must be added to this map.
7. Guardrail allowlists under `SaaS/guardrails/` are configuration and must remain in place.

## Archive

- `_archive/2026-08-02-docs-slimdown/`: completed plans and history removed from the active reading path.
- `_archive/2026-05-02-docs-consolidation/`: earlier project evidence.
- `_archive/security-hardening-preSaaS/`: pre-SaaS security history.
