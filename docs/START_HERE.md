# Documentation Start

This is the authoritative active document map for Denta+. Read only the documents relevant to your task; obsolete historical chronology belongs in Git history rather than current instructions.

## Start Here

| Need | Source |
|---|---|
| Current implementation, release authority & active campaign | `PROJECT_STATE.md` |
| Authoritative product-wide UX, workflow & theme brief | Owner-supplied `DentaPlus_StateOfTheArt_UX_Workflow_Theme_System_Codex_Master.md` (read-only original checkout asset; not a tracked file here) |
| Recent implementation history | `CHANGELOG.md` |
| Durable architecture/product decisions | `DECISIONS.md` |
| Local/deployment operations & runbook | `OPERATIONS_RUNBOOK.md`, repository `README.md` |
| SaaS governance & tenant isolation | `SaaS/SAAS_PROGRAM.md`, `SaaS/TENANT_CONTEXT_CONTRACT.md` |
| Public API contract | `API_CONTRACT.md` |
| First-party React App API contract | `APP_API_CONTRACT.md`, `refactor/APP_API_ROUTE_INVENTORY.md` |
| Modernization program & performance roadmap | `refactor/DENTA_PLUS_HARDENING_PERFORMANCE_PROGRAM.md` |

## Active Campaign Overview

The active campaign is **Product-Wide UX, Workflow, Responsive Design & Multi-Tenant Theming** on development branch `feature/studio-workflows-themes-20261028`, based on deployed release `29dce746c0fc3be436b85a2c1a67fbbb41be3b55` (`release/production-20261017`).

- **Boundary:** Development and review only. No live deployment, production migration or customer-data mutation is authorized. Additive theme migration authoring/testing is allowed only in verified disposable development environments.
- **P0 Focus:** Genuine Super Admin Control Plane entry (`/controlplane/enter`) and persistent responsive impersonation banner/exit (`/controlplane/impersonation/exit`); auth screen fit in `studio_access.php`; production presentation cleanup (remove demo preview badge and fix layout title encoding); safe theme presets (modern/warm optional themes preserving Studio forest/sage default); continuous clinic journey refinements.

## Module State

| Domain | Source |
|---|---|
| Clinical & odontogram workflows | `CLINICAL_CURRENT_STATE.md`, `refactor/PATIENT_CLINICAL_WORKFLOW_PLAN.md` |
| Clinical/finance traceability & ledger | `FINANCE_CURRENT_STATE.md`, `refactor/CLINICAL_FINANCE_TRACEABILITY_PLAN.md` |
| Cross-module workflow optimization | `refactor/PRODUCT_WORKFLOW_OPTIMIZATION_ROADMAP.md` |
| WhatsApp messaging & notifications | `WHATSAPP_CURRENT_STATE.md` |
| Entity workspaces & command drawers | `refactor/ENTITY_CENTERED_COMMAND_WORKSPACE_PLAN.md` |
| React/Lovable interface strategy | `refactor/LOVABLE_STRATEGY_CODEX.md` |
| Doctor scheduling contract | `SaaS/DOCTOR_SCHEDULING_CONTRACT.md` |
| Control Plane entitlements contract | `SaaS/CP-01_CONTROL_PLANE_ENTITLEMENTS_CONTRACT.md` |

## Assistant Operating Guides

- `../AGENTS.md`: Codex operating rules.
- `../GEMINI.md`: Gemini operating rules.

## Documentation Rules

1. **Contracts preserve detail:** Summaries link to authoritative contracts rather than replicating partial rules.
2. **Current state truth:** `PROJECT_STATE.md` reflects current deployed reality and active engineering boundaries; it is not a chronological log.
3. **Recent history only:** `CHANGELOG.md` retains recent continuation context. Historical development chronology is preserved in Git history.
4. **Append-only decisions:** `DECISIONS.md` records durable architectural choices and is append-only.
5. **Preservation of assets:** Retained accepted evidence (`docs/ui-evidence/`), guardrail configurations (`docs/SaaS/guardrails/`), and operational recovery material are active operational and audit assets, not disposable clutter.
6. **No fabricated evidence:** Do not claim documentation, test suites, or verification evidence not actually present in the repository or recorded receipts.
