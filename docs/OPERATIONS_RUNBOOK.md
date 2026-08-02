# Operations Runbook

## Database

```powershell
php spark migrate
php spark migrate:rollback
```

Canonical UTF-8 dump without `CREATE DATABASE`:

```powershell
cmd /c "\"C:\xampp\mysql\bin\mysqldump.exe\" -h 127.0.0.1 -P 3306 -u root --default-character-set=utf8mb4 --routines --triggers --events --single-transaction --skip-lock-tables --set-charset --no-create-db democa_dental_saas --ignore-table=democa_dental_saas.control_plane_invoices --ignore-table=democa_dental_saas.control_plane_invoice_payments > democa_dental_saas_schema_superadmin.sql"
```

The dump must stay untracked. Regenerate after schema/bootstrap-data changes.

## Build and Verification

```powershell
php spark routes
vendor\bin\phpunit --no-coverage --no-logging --do-not-cache-result
npm.cmd run build
bash scripts/ci/saas_guardrails.sh
```

Targeted safety scans:

```powershell
rg -n "innerHTML|outerHTML|insertAdjacentHTML|\.html\(" app/Views
php spark routes | findstr /I "controlplane app-api api/v1 api/search"
```

## WhatsApp

```powershell
php spark whatsapp:ops-check
php spark whatsapp:queue-product-flows
php spark notifications:dispatch --channel whatsapp
```

Queue product workflows before dispatch.

## Local Runtime

- Path: `C:\xampp\htdocs\dental-saas`
- Database: `democa_dental_saas` at `127.0.0.1:3306`, user `root`, blank password
- Session cookie: `ci_session_saas`
- Tenant login: `https://localhost/dental-saas/auth/login`
- Control plane: `https://localhost/dental-saas/controlplane`

Use `.env` as runtime truth.

## Hostinger Deployment

1. Pull reviewed commits.
2. Configure production `.env`.
3. Run migrations when required.
4. Deploy reviewed generated assets; run `npm run release:hostinger` locally for Tailwind changes.
5. Use `RewriteBase /`, not `/dental-saas/`.
6. Import only a UTF-8 dump without `CREATE DATABASE` when database import is required.

Only `.env` and database import are manual host-specific items.

## Troubleshooting

- Migration namespace/parse failures: verify UTF-8 without BOM.
- SQL “Unexpected character at position 1”: dump was UTF-16; regenerate with `cmd` redirection.
- Tenant 404: confirm session has valid `active_clinic_id`, `global_mode` is false, plan is active, and route permissions match.
- Control-plane 404: confirm `global_mode=true`, no active tenant, and superadmin authorization.
- Hostinger `/auth/login` 404: verify Apache rewrite support and `RewriteBase /`.
- Missing styles: rebuild Tailwind and deploy `public/assets/css/tailwind.css`.
- Bash `E_ACCESSDENIED` on Windows: run guardrails in CI or a working WSL/Bash environment; do not report it as a passing check.

## Documentation QA

```powershell
$active = git ls-files README.md AGENTS.md gemini.md docs
$active.Count
rg -n "docs/SaaS/verification|docs/verification|docs/SaaS/logs|docs/DECISION_LOG|docs/IMPLEMENTATION_LOG" $active
$active | ForEach-Object {
  $text = [System.IO.File]::ReadAllText((Resolve-Path $_), [System.Text.Encoding]::UTF8)
  if ($text.Contains([char]0xFFFD)) { $_ }
}
```

Expected: no stale-path or replacement-character output. Active count is informational; `START_HERE.md` is the authoritative map.
