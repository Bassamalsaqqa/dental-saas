# Operations Runbook

## Common Commands

### Database

```bash
php spark migrate                    # Run migrations
php spark migrate:rollback           # Rollback last migration

# Regenerate SQL dump (XAMPP MySQL)
cmd /c "\"C:\xampp\mysql\bin\mysqldump.exe\" -h 127.0.0.1 -P 3306 -u root --default-character-set=utf8mb4 --routines --triggers --events --single-transaction --skip-lock-tables --set-charset --no-create-db democa_dental_saas --ignore-table=democa_dental_saas.control_plane_invoices --ignore-table=democa_dental_saas.control_plane_invoice_payments > democa_dental_saas_schema_superadmin.sql"

# Import SQL dump
mysql -u root democa_dental_saas < democa_dental_saas_schema_superadmin.sql
```

### Cache & Assets

```bash
php spark cache:clear                # Clear application cache
npm run build                        # Rebuild Tailwind CSS
npm run release:hostinger            # Rebuild Tailwind + deployment reminder
```

### Guardrails

```bash
bash scripts/ci/saas_guardrails.sh   # Run SaaS guardrail checks
rg -n "innerHTML|outerHTML|insertAdjacentHTML|\.html\(" app/Views  # DOM sink scan
php spark routes | findstr /I "controlplane api/v1 api/search"       # Route sanity
```

### WhatsApp

```bash
php spark whatsapp:ops-check                     # Phase 6 readiness monitor
php spark whatsapp:queue-product-flows           # Queue product-flow reminders
php spark notifications:dispatch --channel whatsapp  # Dispatch WhatsApp notifications
php spark whatsapp:seed-local-test               # Seed local test data
```

### Tenant Jobs

```bash
php spark tenant:run-job SendEmailNotificationsJob --clinic-id <ID>
```

## Troubleshooting

### Migration fails with namespace error
Ensure migration files are saved as UTF-8 without BOM.

### 404 on tenant routes
- Confirm `.htaccess` is active and `RewriteBase` matches host root.
- Check `app.baseURL` in `.env`.

### Plan gate blocks features
Verify clinic plan and feature toggles in the control plane.

### SQL import errors ("Unexpected character at position 1")
The dump file was UTF-16. Regenerate using `cmd` redirection (not PowerShell).

### /auth/login 404 on Hostinger
Check `RewriteBase` — on Hostinger it should be `/` (not `/dental-saas/`).

## Hostinger Deployment

1. Upload codebase
2. Upload `.env`
3. Import SQL dump (`democa_dental_saas_schema_superadmin.sql`) — use UTF-8, no CREATE DATABASE
4. Ensure `.htaccess` `RewriteBase /` (not `/dental-saas/`)
5. Run `npm run release:hostinger` locally before deploying UI/Tailwind changes
6. Only manual items after deploy: `.env` + latest SQL dump

## Local Environment

- Path: `C:\xampp\htdocs\dental-saas`
- DB: `democa_dental_saas` on XAMPP MySQL (127.0.0.1:3306, root, no password)
- Docker MySQL container: `mysql8-dental` (port 3307, password: root) — optional, for dump export
- Session cookie: `ci_session_saas`
- Tenant login: `https://localhost/dental-saas/auth/login`
- Control plane: `https://localhost/dental-saas/controlplane`

## Docs QA

### Active docs count (expect 19)

Bash:
```bash
git ls-files docs | grep -v "^docs/_archive/" | grep -v "^docs/SaaS/guardrails/" | wc -l
```

PowerShell:
```powershell
$activeDocs = git ls-files docs | Where-Object { $_ -notmatch '^docs/_archive/' -and $_ -notmatch '^docs/SaaS/guardrails/' }
$activeDocs.Count
```

### Stale reference scan (expect no output)

Scans only normal active docs (excludes START_HERE.md and this file, which may mention old paths as policy examples).

Bash:
```bash
rg -n "docs/SaaS/verification|docs/verification|docs/SaaS/logs|docs/DECISION_LOG|docs/IMPLEMENTATION_LOG" \
  README.md AGENTS.md gemini.md \
  docs/PROJECT_STATE.md docs/CHANGELOG.md docs/DECISIONS.md \
  docs/API_CONTRACT.md docs/APP_API_CONTRACT.md \
  docs/refactor/APP_API_ROUTE_INVENTORY.md \
  docs/refactor/LOVABLE_STRATEGY_CODEX.md \
  docs/refactor/LOVABLE_STRATEGY_GEMINI.md \
  docs/refactor/LARAVEL_MIGRATION_STUDY_CODEX.md \
  docs/refactor/LARAVEL_MIGRATION_STUDY_GEMINI.md \
  docs/FINANCE_CURRENT_STATE.md \
  docs/WHATSAPP_CURRENT_STATE.md docs/CLINICAL_CURRENT_STATE.md \
  docs/SaaS/TENANT_CONTEXT_CONTRACT.md \
  docs/SaaS/CP-01_CONTROL_PLANE_ENTITLEMENTS_CONTRACT.md
```

PowerShell:
```powershell
$docsToScan = @(
  'README.md', 'AGENTS.md', 'gemini.md',
  'docs/PROJECT_STATE.md', 'docs/CHANGELOG.md', 'docs/DECISIONS.md',
  'docs/API_CONTRACT.md', 'docs/APP_API_CONTRACT.md',
  'docs/refactor/APP_API_ROUTE_INVENTORY.md',
  'docs/refactor/LOVABLE_STRATEGY_CODEX.md',
  'docs/refactor/LOVABLE_STRATEGY_GEMINI.md',
  'docs/refactor/LARAVEL_MIGRATION_STUDY_CODEX.md',
  'docs/refactor/LARAVEL_MIGRATION_STUDY_GEMINI.md',
  'docs/FINANCE_CURRENT_STATE.md',
  'docs/WHATSAPP_CURRENT_STATE.md', 'docs/CLINICAL_CURRENT_STATE.md',
  'docs/SaaS/TENANT_CONTEXT_CONTRACT.md',
  'docs/SaaS/CP-01_CONTROL_PLANE_ENTITLEMENTS_CONTRACT.md'
)
rg -n "docs/SaaS/verification|docs/verification|docs/SaaS/logs|docs/DECISION_LOG|docs/IMPLEMENTATION_LOG" $docsToScan
```

### SAAS_PROGRAM.md historical check

Matches after `## Append-Only Corrections & Decisions` are acceptable. Matches before that heading are bugs.

```bash
rg -n "docs/SaaS/verification|docs/verification|docs/SaaS/logs" docs/SaaS/SAAS_PROGRAM.md
```

### DOM sink scan (expect empty)

```bash
rg -n "innerHTML|outerHTML|insertAdjacentHTML|\.html\(" app/Views
```

### Replacement-character scan (expect no output)

PowerShell:
```powershell
$active = git ls-files README.md AGENTS.md gemini.md docs | Where-Object { $_ -notmatch '^docs/_archive/' }
$active | ForEach-Object {
  $text = [System.IO.File]::ReadAllText((Resolve-Path $_), [System.Text.Encoding]::UTF8)
  if ($text.Contains([char]0xFFFD)) { $_ }
}
```

### Guardrails

```bash
bash scripts/ci/saas_guardrails.sh
```
