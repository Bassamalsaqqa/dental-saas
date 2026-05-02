# DentaCare SaaS — Documentation Start

This is the active documentation surface for the project. Everything else is archived historical evidence.

## Active Docs Map

| File | Purpose |
|------|---------|
| `README.md` | Project overview, tech stack, quick-setup |
| `AGENTS.md` | Operating guide for AI assistants (Codex) |
| `gemini.md` | Operating guide for AI assistants (Gemini) |
| `docs/START_HERE.md` | This file — navigation and doc policy |
| `docs/PROJECT_STATE.md` | Current-state dashboard: active phase, modules, known workflows |
| `docs/DECISIONS.md` | Append-only architectural and business decisions |
| `docs/CHANGELOG.md` | Recent implementation history for work continuation |
| `docs/API_CONTRACT.md` | Canonical `/api/v1/*` contract |
| `docs/SaaS/SAAS_PROGRAM.md` | SaaS governance source of truth |
| `docs/SaaS/TENANT_CONTEXT_CONTRACT.md` | Tenant context mechanics |
| `docs/SaaS/CP-01_CONTROL_PLANE_ENTITLEMENTS_CONTRACT.md` | Control plane entitlements contract |
| `docs/FINANCE_CURRENT_STATE.md` | Finance feature current state |
| `docs/WHATSAPP_CURRENT_STATE.md` | WhatsApp feature current state |
| `docs/CLINICAL_CURRENT_STATE.md` | Clinical workflows current state |
| `docs/OPERATIONS_RUNBOOK.md` | Operational commands, guardrails, deployment |
| `docs/DEPLOYMENT_LOCAL_SETUP.md` | Local development and deployment setup |

### Config (not "docs count")

- `docs/SaaS/guardrails/dom-sinks.allowlist`
- `docs/SaaS/guardrails/group-auth.allowlist`
- `docs/SaaS/guardrails/raw-tenant-queries.allowlist`

## Archive Policy

`docs/_archive/` contains historical evidence only: old audits, verification artifacts, dated plans, generated HTML, and copied code bundles. Archived docs are NOT source of truth. If you need historical context, browse the archive — but always confirm against the active docs above before making decisions.

## Documentation Maintenance Rules

1. **Active docs map is authoritative.** New docs must be added to the map above or they do not count as active.
2. **Detailed verification evidence goes under `docs/_archive/`.** Do not create new `docs/verification/` or `docs/SaaS/verification/` directories.
3. **Current implementation summaries go in `docs/CHANGELOG.md`.** Write a brief entry after each meaningful change.
4. **Durable architectural/product decisions go in `docs/DECISIONS.md`.** Append-only; add a dated entry.
5. **Do not create new dated root-level plan/audit files** (e.g., `docs/WHATSAPP_PLAN_V6_2026-05-15.md`) unless they are immediately archived after summarizing into the active docs.
6. **Guardrail allowlists** (`docs/SaaS/guardrails/*`) must stay in place — the CI guardrail script hard-codes those paths.
7. **`docs/START_HERE.md` and `docs/OPERATIONS_RUNBOOK.md`** may mention old path patterns (`docs/SaaS/verification`, `docs/SaaS/logs`, etc.) only as policy rules, anti-pattern examples, or QA test strings. These are not stale references.

## Archive Roots

Current: `docs/_archive/2026-05-02-docs-consolidation/`

Legacy archive: `docs/_archive/security-hardening-preSaaS/`
