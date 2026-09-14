# Operations Runbook

## Database

```powershell
php spark migrate
php spark migrate:rollback
```

Canonical UTF-8 dump without `CREATE DATABASE`:

```powershell
cmd /c "\"C:\xampp\mysql\bin\mysqldump.exe\" -h 127.0.0.1 -P 3306 -u root --default-character-set=utf8mb4 --routines --triggers --events --single-transaction --skip-lock-tables --set-charset --no-create-db democa_dental_saas > democa_dental_saas_schema_superadmin.sql"
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

### Connection & Environment
- **SSH Endpoint**: `ssh -p 65002 u556956644@89.116.192.222` (or via configured SSH alias `hostinger-dalel`)
- **Remote Repository Path**: `~/domains/palsync.net/public_html/denta`
- **Branch**: `main`
- **Framework**: CodeIgniter 4 (use `php spark`, **never** `php artisan`)
- **Credentials Policy**: Never store passwords, private keys, or API secrets in repository files, logs, or commits.

### Standard Deployment Workflow
1. Verify clean working tree on production:
   ```bash
   ssh hostinger-dalel "cd ~/domains/palsync.net/public_html/denta && git status -sb"
   ```
2. Fast-forward pull reviewed commits from origin:
   ```bash
   ssh hostinger-dalel "cd ~/domains/palsync.net/public_html/denta && git pull --ff-only"
   ```
3. Run pending migrations:
   ```bash
   ssh hostinger-dalel "cd ~/domains/palsync.net/public_html/denta && php spark migrate"
   ```
4. Verify migration status:
   ```bash
   ssh hostinger-dalel "cd ~/domains/palsync.net/public_html/denta && php spark migrate:status"
   ```
5. Deploy reviewed assets if UI changed: deploy `public/assets/css/tailwind.css` (or run `npm run release:hostinger` locally).
6. Verify Apache rewrite: `RewriteBase /` is required on Hostinger (not `/dental-saas/`).
7. Post-deploy verification & log inspection:
   - Check `writable/logs/log-YYYY-MM-DD.log` for any errors.
   - Smoke test key routes (`/controlplane/clinics/19`, `/controlplane/clinics/21`, `/auth/login`).

### Rollback Guidance
- Identify the previous stable commit: `git log --oneline -5`.
- If rollback is needed, create a revert commit locally, test it, push, and `git pull --ff-only` on production.
- If migrations need rollback: `php spark migrate:rollback` (targets last batch).
- Do not use destructive git operations (`git reset --hard`, force-push) on production.

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
