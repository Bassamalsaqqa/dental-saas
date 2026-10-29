# Operations Runbook

## Database

The commands below are for explicitly approved isolated development databases. Do not run them against production or the original local business database as part of routine verification.

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
- **Branch**: `release/production-20261017` (exact approved SHA/tree; `main` is not deployment authority)
- **Framework**: CodeIgniter 4 (use `php spark`, **never** `php artisan`)
- **Credentials Policy**: Never store passwords, private keys, or API secrets in repository files, logs, or commits.

### Controlled Production Release

Owner authorizes source integration and production deployment separately. Do not execute a release from this runbook alone.

1. Reconcile exact live SHA/tree with the approved release ref. Inspect hooks and deployment triggers read-only; do not assume a push is harmless.
2. Confirm explicit Hostinger PHP runtime `/opt/alt/php83/usr/bin/php`, applied schema/history and exact pending migration list. Current dynamic scheduling migration stays unapplied and the feature OFF. No blanket `spark migrate`, dependency installation or GitHub Actions.
3. Verify fresh recoverable off-host backup and prior isolated restore evidence. DB/files backups are not atomic; report that limitation. Save private source preimages with mode0700 directories/mode0600 files.
4. Execute only the reviewed exact-SHA deployment helper with its release-specific authorization flag. Source checkout needs umask0022; restore private umask0077 afterward. No archive overwrite, Git reset, protected-file replacement or local SQL import.
5. Preserve `.env`, root/public `.htaccess`, writable storage, uploads and customer records. Check source hashes, exact HEAD/tree/ref and only expected `.htaccess` dirty state.
6. Essential postflight: authenticated tenant workflow, EN/AR critical pages, HTTP200 for JS/CSS (including `select2-init.js` and `contextual-workspace.js`), mode0644 for changed web assets, protected hashes and schema/data-preservation evidence. Do not broadly chmod unrelated files.

### Rollback Guidance

Prepare a source-only rollback commit whose parent is the candidate and whose tree is the exact previous release. Publish/install it only within the separately authorized recovery procedure; verify installed source and release ref agree. Never automatically restore or alter production DB/configuration/storage, run generic migration rollback, reset Git or force-push. A schema-changing release requires its own reviewed recovery/fix-forward design.

## Troubleshooting

- Migration namespace/parse failures: verify UTF-8 without BOM.
- SQL “Unexpected character at position 1”: dump was UTF-16; regenerate with `cmd` redirection.
- Tenant 404: confirm session has valid `active_clinic_id`, `global_mode` is false, plan is active, and route permissions match.
- Control-plane 404: confirm `global_mode=true`, no active tenant, and superadmin authorization.
- Hostinger `/auth/login` 404: verify Apache rewrite support and `RewriteBase /`.
- Missing styles: rebuild Tailwind and deploy `public/assets/css/tailwind.css`.
- Bash `E_ACCESSDENIED` on Windows: run guardrails in a working local Git Bash/WSL environment (GitHub Actions are prohibited); do not report it as a passing check.

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

## Proposed treatment-type schema release (not production-authorized)

This is separate from the theme migration and cannot be called source-only. Review/apply only `2026-10-29-120000_WidenTreatmentTypeStorage` using a pinned single-migration runner after explicit production authorization; never blanket `spark migrate` with other pending migrations. Preflight must verify exact source/schema/history, no unrecognized or empty historical keys, unchanged attributes/constraints/indexes, required DDL/LOCK TABLES privileges, disk headroom and certified fresh encrypted off-host recovery. Inspect long transactions/metadata-lock waiters and choose a low-activity window. The explicit WRITE lock blocks treatment-table reads/writes while COPY DDL and before/after checks run; lock wait is bounded at5seconds, original SQL mode/wait restored. Measured native44-row copy took2.35seconds, not a production SLA or proof of zero interruption.

After authorized DDL verify VARCHAR100, same charset/collation/null/default/index/FK definitions, exact preserved historical records and single migration-history entry. Run only essential authorized synthetic rehearsal writes/read-only live checks. Source rollback leaves the widened column and every new key intact; `down()` intentionally refuses destructive narrowing. Never restore an older DB over subsequent activity or rewrite stored keys. Theme/scheduling/WhatsApp/corrections/checkout/contacts/derivatives stayOFF. See [scoped treatment evidence](ui-evidence/product-ux/treatment-storage.json).
