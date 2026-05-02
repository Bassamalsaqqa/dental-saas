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

Current archive root: `docs/_archive/2026-05-02-docs-consolidation/`

Legacy archive: `docs/_archive/security-hardening-preSaaS/`
