# Documentation Start

This is the authoritative active document map for Denta+. Read only the documents relevant to your task; obsolete historical chronology belongs in Git history rather than current instructions.

## Start Here

| Need | Source |
|---|---|
| Developer Onboarding & Architecture Boundaries | `DEVELOPER_ONBOARDING.md` |
| Current implementation, release authority & active campaign | `PROJECT_STATE.md` |
| Current Owner task scope, findings and next checkpoint | `WORK_PROGRAM.md` |
| Durable journey guidance and component architecture | `UI_STUDIO_COMPONENTS.md` |
| Historical Owner design reference (read-only; not a restart gate) | Owner-supplied `DentaPlus_StateOfTheArt_UX_Workflow_Theme_System_Codex_Master.md` (read-only original checkout asset; not a tracked file here) |
| Recent implementation history | `CHANGELOG.md` |
| Durable architecture/product decisions | `DECISIONS.md` |
| Local/deployment operations & runbook | `OPERATIONS_RUNBOOK.md`, repository `README.md` |
| SaaS governance & tenant isolation | `SaaS/SAAS_PROGRAM.md`, `SaaS/TENANT_CONTEXT_CONTRACT.md` |
| Public API contract | `API_CONTRACT.md` |
| First-party React App API contract | `APP_API_CONTRACT.md`, `refactor/APP_API_ROUTE_INVENTORY.md` |
| Modernization program & architecture index | `refactor/README.md`, `refactor/DENTA_PLUS_HARDENING_PERFORMANCE_PROGRAM.md` |

## Current release and maintenance

Installed production is `3cb232b2ad2b5bdb9d4f28f4b4dff592818f49b8` (tree `49b45c80923d8bff9c897caaf133ab09aef69a6c`), verified via SSH and authenticated read-only browser checks; maintenance is OFF. Immediate source recovery is `6ddc1fa717af12a99af70b9e22ae9c85c5d51421` (tree `01131d8b805533ea5ce298252e5d11ba57476d02`), with forward recovery commit `5076835731c52018eb2032a0a502902c6eed93dc` parented by the installed candidate. Older recovery baselines remain preserved. The approved release ref is `release/production-20261017`; production checkout remains on historical `main` tracking `origin/main` and must not be pulled or reconfigured. Dashboard redesign and clinical handoff are deployed. Subsequent documentation-only commits do not change installed runtime authority.

See `PROJECT_STATE.md` for the exact release, two-migration inventory, preservation results, retired rehearsal environments, parent verified live state, and remaining limitations. Optional features remain OFF. Current Owner explicitly authorizes eventual commit/push/deploy; actual deployment execution remains bound to reviewed candidate and runbook preflight gates.

## Module State

| Domain | Source |
|---|---|
| Clinical & odontogram workflows | `CLINICAL_CURRENT_STATE.md`, `refactor/PATIENT_CLINICAL_WORKFLOW_PLAN.md` |
| Clinical/finance traceability & ledger | `FINANCE_CURRENT_STATE.md`, `refactor/CLINICAL_FINANCE_TRACEABILITY_PLAN.md` |
| Cross-module workflow optimization | `refactor/PRODUCT_WORKFLOW_OPTIMIZATION_ROADMAP.md` |
| Entity workspaces & command drawers | `refactor/ENTITY_CENTERED_COMMAND_WORKSPACE_PLAN.md` |
| WhatsApp messaging & notifications | `WHATSAPP_CURRENT_STATE.md` |
| Studio components & journey contracts | `UI_STUDIO_COMPONENTS.md` |
| React frontend boundaries & Lovable guidelines | `refactor/LOVABLE_STRATEGY_CODEX.md`, `DEVELOPER_ONBOARDING.md` |
| Doctor scheduling contract | `SaaS/DOCTOR_SCHEDULING_CONTRACT.md` |
| Control Plane entitlements contract | `SaaS/CP-01_CONTROL_PLANE_ENTITLEMENTS_CONTRACT.md` |

## Assistant Operating Guides

- `../AGENTS.md`: Codex operating rules & shared safeguards.
- `../gemini.md`: Gemini/AGY entry point; follows the shared guide.
- `../CLAUDE.md`: Claude operating guide & shared safeguards.

## Documentation Rules

1. **Contracts preserve detail:** Summaries link to authoritative contracts rather than replicating partial rules.
2. **Current state truth:** `PROJECT_STATE.md` reflects current deployed reality and active engineering boundaries; it is not a chronological log.
3. **Recent history only:** `CHANGELOG.md` retains recent continuation context. Historical development chronology is preserved in Git history.
4. **Append-only decisions:** `DECISIONS.md` records durable architectural choices and is append-only.
5. **Preservation of assets:** Retained accepted evidence (`docs/ui-evidence/`), guardrail configurations (`docs/SaaS/guardrails/`), and operational recovery material are active operational and audit assets, not disposable clutter.
6. **No fabricated evidence:** Do not claim documentation, test suites, or verification evidence not actually present in the repository or recorded receipts.
