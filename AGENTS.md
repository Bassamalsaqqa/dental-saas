# Codex Operating Guide - Dental SaaS

## A) Role
- Primary mission: execute requested changes cleanly, keep SaaS spec alignment, and avoid regressions.
- Secondary mission: leave an audit-grade trail (commands, diffs, tests) and enforce tenant isolation.

## B) Source of Truth
1) `docs/START_HERE.md` — navigation hub and archive policy
2) `docs/SaaS/SAAS_PROGRAM.md` governs SaaS scope, priority, and DoD.
3) `docs/SaaS/TENANT_CONTEXT_CONTRACT.md` governs tenant context mechanics.
4) `docs/API_CONTRACT.md` governs `/api/v1/*` semantics.
5) Guardrails: `docs/SaaS/guardrails/*` + repo guardrail scripts.
6) `docs/WHATSAPP_CURRENT_STATE.md` — WhatsApp current state and Phase 6 resume point.
7) `docs/FINANCE_CURRENT_STATE.md` — finance current state.
8) `docs/PROJECT_STATE.md` — current-state dashboard.

Historical docs live in `docs/_archive/`. Archived docs are evidence, not source of truth.

## C) Non-Negotiables
- Fail-closed doctrine always.
- No redirects/HTML for `/api/*`.
- Airlock separation enforced: control plane vs tenant plane.
- Control plane requires `global_mode=true` (TenantFilter).
- Evidence-based verification (paths, diffs, rg output, curl output when relevant).

## D) Session Start Checklist
1) Read active docs for context:
   - `docs/PROJECT_STATE.md`
   - `docs/CHANGELOG.md`
   - `docs/DECISIONS.md`
   - `docs/WHATSAPP_CURRENT_STATE.md`
2) Drift scans:
   - `rg -n "innerHTML|outerHTML|insertAdjacentHTML|\.html\(" app/Views`
   - `php spark routes | findstr /I "controlplane api/v1 api/search"`
   - `bash scripts/ci/saas_guardrails.sh`
3) Confirm clean tree: `git status -sb`

## E) Environment & Infrastructure
**Local Path:** `C:\xampp\htdocs\dental-saas`

1) Local Database (authoritative local source)
- DB: `democa_dental_saas`
- Host: `127.0.0.1`
- Port: `3306`
- User: `root`
- Password: blank
- Source of truth for active connection settings: `.env`

2) Session
- Cookie: `ci_session_saas`

## F) Deployment Notes (Hostinger)
- Hostinger needs the SQL dump **without CREATE DATABASE**. Use UTF-8.
- Latest dump: `democa_dental_saas_schema_superadmin.sql`.
- If import errors mention "Unexpected character" at position 1, the file was UTF-16. Re-generate using `cmd` redirection.
- `.htaccess`: on Hostinger, `RewriteBase /` is required (not `/dental-saas/`).
- Only manual items after deploy: `.env` + latest SQL dump.

## G) How to Regenerate SQL Dump (UTF-8, no CREATE DATABASE)
Run from repo root using the local XAMPP MySQL client:
```
cmd /c "\"C:\xampp\mysql\bin\mysqldump.exe\" -h 127.0.0.1 -P 3306 -u root --default-character-set=utf8mb4 --routines --triggers --events --single-transaction --skip-lock-tables --set-charset --no-create-db democa_dental_saas --ignore-table=democa_dental_saas.control_plane_invoices --ignore-table=democa_dental_saas.control_plane_invoice_payments > democa_dental_saas_schema_superadmin.sql"
```

## H) Recent Changes
See `docs/CHANGELOG.md` for recent implementation history. See `docs/PROJECT_STATE.md` for the current active phase.

## I) Known Requirements / Guardrails
- Keep `democa_dental_saas_schema_superadmin.sql` updated whenever schema or bootstrap data changes.
- Ensure IonAuth default group `members` exists in dumps to avoid "Invalid default group name" errors.
- No DOM sinks in `app/Views` (guardrails enforce this).
- After pulling finance or messaging schema changes, run migrations:
  - `php spark migrate`
