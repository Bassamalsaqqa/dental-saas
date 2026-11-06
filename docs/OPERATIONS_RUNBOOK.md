# Operations Runbook

## Database

The migration commands below are ONLY for explicitly authorized isolated disposable environments. At the 2026-11-05 checkpoint, local business MySQL was stopped and earlier synthetic environments retired; recheck readiness. Do not migrate production or the local business database as routine verification. The dump command requires separate authorization for its named business source; it is not a synthetic test command. This runbook does not authorize any of these actions.

```powershell
php spark migrate
php spark migrate:rollback
```

Canonical UTF-8 dump without `CREATE DATABASE`:

```powershell
cmd /c "\"C:\xampp\mysql\bin\mysqldump.exe\" -h 127.0.0.1 -P 3306 -u root --default-character-set=utf8mb4 --routines --triggers --events --single-transaction --skip-lock-tables --set-charset --no-create-db democa_dental_saas > democa_dental_saas_schema_superadmin.sql"
```

The dump must stay untracked. Its filename is not proof of release compatibility; the retained local dump predates this release. Regenerate only after separately authorized changes to its named business source. Synthetic test-fixture changes do not require exporting business data.

## Build and Verification

```powershell
php spark routes
# Run only an inspected database-free file until an isolated SQL target is verified:
php vendor/phpunit/phpunit/phpunit --no-coverage --no-logging --do-not-cache-result <file>
# Do not rebuild the pinned Tailwind release artifact during setup; see README.md.
bash scripts/ci/saas_guardrails.sh
```

Targeted safety scans:

```powershell
rg -n "innerHTML|outerHTML|insertAdjacentHTML|\.html\(" app/Views
php spark routes | findstr /I "controlplane app-api api/v1 api/search"
```

## WhatsApp

Queue/dispatch commands can create records or send real messages. Use them only for an explicitly authorized operational task against the verified target, never as routine readiness checks.

```powershell
php spark whatsapp:ops-check
php spark whatsapp:queue-product-flows
php spark notifications:dispatch --channel whatsapp
```

Queue product workflows before dispatch.

## Continuing on another machine

Follow the current [README setup](../README.md#local-setup-and-continuing-on-another-machine). Clone the default release branch, install exact locked dependencies and configure a new private local environment. Databases, uploads, encryption keys, recovery archives and owner-only reference assets are transferred separately when needed. Do not infer that Git clone provisions a compatible database or licenses automatic migrations.

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
- **Observed checkout (2026-11-05)**: installed SHA/tree matches the approved release, but its local branch is `main` tracking `origin/main`. Do not pull historical main or change production tracking during inspection; use the exact approved source.
- **Framework**: CodeIgniter 4 (use `php spark`, **never** `php artisan`)
- **Credentials Policy**: Never store passwords, private keys, or API secrets in repository files, logs, or commits.

### Controlled Production Release

Owner authorizes source integration and production deployment separately. Do not execute a release from this runbook alone.

1. Reconcile exact live SHA/tree with the approved release ref. Inspect hooks and deployment triggers read-only; do not assume a push is harmless.
2. Confirm explicit Hostinger PHP runtime `/opt/alt/php83/usr/bin/php`, applied schema/history and exact pending migration list. Current dynamic scheduling migration stays unapplied and the feature OFF. No blanket `spark migrate`, dependency installation or GitHub Actions.
3. Verify fresh recoverable off-host backup and prior isolated restore evidence. DB/files backups are not atomic; report that limitation. Save private source preimages with mode0700 directories/mode0600 files.
4. Execute only the reviewed exact-SHA deployment helper with its release-specific authorization flag. Source checkout needs umask0022; restore private umask0077 afterward. No archive overwrite, Git reset, protected-file replacement or local SQL import.
5. Preserve `.env`, root/public `.htaccess`, writable storage, uploads and customer records. Check source hashes and exact HEAD/tree/ref. The historical PR24 transition from 9e1f8558 allowed exactly the known root `.htaccess` overlay plus the three intentional tracked quarantine deletions (`_debug_diff.txt`, `index_public.php`, `redirect.php`), pinned to verified hashes and private recovery receipts; reject every other change. After PR24 integration at 6505bac3, those deletions are committed and only the known `.htaccess` overlay remains dirty. Future preflight must use the actual installed baseline, not reuse the old deletion allowance.
6. Essential postflight: authenticated tenant workflow, EN/AR critical pages, HTTP200 for JS/CSS (including `select2-init.js` and `contextual-workspace.js`), mode0644 for changed web assets, protected hashes and schema/data-preservation evidence. Do not broadly chmod unrelated files.

While controlled maintenance is active, missing legacy URLs may correctly return 503. Verify their physical absence and probe them through the reviewed private verification path; require 403/404 again after reopening. Never interpret a closed maintenance response as completed public containment.

### Rollback Guidance

Prepare a source-only rollback commit whose parent is the candidate and whose tree is the previous release, retaining any subsequently required security containment. For PR24, keep index_public.php, redirect.php and _debug_diff.txt absent: restoring the exact old tree would re-expose quarantined helpers. Verify the complete recovery-tree diff and protected overlay preservation before publication. Publish/install it only within the separately authorized recovery procedure; verify installed source and release ref agree. Never automatically restore or alter production DB/configuration/storage, run generic migration rollback, reset Git or force-push. A schema-changing release requires its own reviewed recovery/fix-forward design.

## Troubleshooting

- Migration namespace/parse failures: verify UTF-8 without BOM.
- SQL “Unexpected character at position 1”: dump was UTF-16; regenerate with `cmd` redirection.
- Tenant 404: confirm session has valid `active_clinic_id`, `global_mode` is false, plan is active, and route permissions match.
- Control-plane 404: confirm `global_mode=true`, no active tenant, and superadmin authorization.
- Hostinger `/auth/login` 404: verify Apache rewrite support and `RewriteBase /`.
- Missing styles: check asset URLs, deployed file identity and the preserved prebuilt artifact. Do not rebuild Tailwind as a repair shortcut; follow README asset preservation.
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

## Treatment-type migration recovery constraints (already deployed in PR22)

The exact treatment migration shipped on 2026-10-30; see PROJECT_STATE.md. The following describes its historical controlled procedure and recovery constraints, not an instruction to rerun it. For a separately authorized new target, review/apply only `2026-10-29-120000_WidenTreatmentTypeStorage` using a pinned single-migration runner after explicit production authorization; never blanket `spark migrate` with other pending migrations. Preflight must verify exact source/schema/history, no unrecognized or empty historical keys, unchanged attributes/constraints/indexes, required DDL/LOCK TABLES privileges, disk headroom and certified fresh encrypted off-host recovery. Inspect long transactions/metadata-lock waiters and choose a low-activity window. The explicit WRITE lock blocks treatment-table reads/writes while COPY DDL and before/after checks run; lock wait is bounded at5seconds, original SQL mode/wait restored. Measured native44-row copy took2.35seconds, not a production SLA or proof of zero interruption.

After authorized DDL verify VARCHAR100, same charset/collation/null/default/index/FK definitions, exact preserved historical records and single migration-history entry. Run only essential authorized synthetic rehearsal writes/read-only live checks. Source rollback leaves the widened column and every new key intact; `down()` intentionally refuses destructive narrowing. Never restore an older DB over subsequent activity or rewrite stored keys. Theme/scheduling/WhatsApp/corrections/checkout/contacts/derivatives stayOFF. See [scoped treatment evidence](ui-evidence/product-ux/treatment-storage.json).
