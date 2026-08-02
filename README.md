# DentaCare SaaS

Multi-tenant dental clinic management built with CodeIgniter 4, PHP views, Tailwind CSS, jQuery/Select2, and MySQL.

## Product Surface

- Tenant app: patients, appointments, examinations, odontogram, treatments, prescriptions, finance, inventory, providers, users/roles, reports, settings, and activity.
- Control plane: clinics, plans/entitlements, onboarding, operations, WhatsApp governance, settings, and audit activity.
- APIs:
  - `/api/v1/*`: public/stateless Bearer API with `X-Clinic-Id`.
  - `/app-api/v1/*`: first-party session/CSRF JSON API for the React app.
- React/Lovable frontend is maintained in a separate repository. This repository owns the CI4 backend and generated `public/app` deployment assets.

## Architecture Rules

- Tenant and control planes are isolated and fail closed.
- Tenant records are scoped by server-owned `active_clinic_id`.
- IonAuth authenticates; RBAC permissions authorize.
- Cross-tenant records are concealed with 404 where appropriate.
- Tenant files are private and served through authorized streaming routes.
- Finance, permissions, tenant authority, and clinical truth remain backend-owned.

Binding contracts are indexed in [docs/START_HERE.md](docs/START_HERE.md).

## Local Setup

Requirements: PHP 8.2+ (`intl`, `mbstring`, `json`, `mysqli`, `xml`, `curl`, `gd`), Composer, Node/npm, MySQL/MariaDB, and Apache/XAMPP on Windows.

```powershell
composer install
npm.cmd install
Copy-Item env .env
php spark key:generate
php spark migrate
npm.cmd run build
```

Local defaults:

- Repository: `C:\xampp\htdocs\dental-saas`
- Database: `democa_dental_saas`
- Host: `127.0.0.1:3306`
- User: `root`, blank password
- Session cookie: `ci_session_saas`

Use `.env` as the authoritative connection configuration. Tailwind builds `src/input.css` to `public/assets/css/tailwind.css`; reviewed React assets build under `/app` to `public/app`.

Prefer an Apache/Nginx document root at `public/`. If the repository root is served, web-server rules must block dotfiles, `app`, `system`, `tests`, `writable`, `scripts`, `docs`, `vendor`, `node_modules`, source/config files, dumps, logs, and browser-accessible maintenance scripts.

## Common Verification

```powershell
php spark routes
vendor\bin\phpunit --no-coverage --no-logging --do-not-cache-result
npm.cmd run build
bash scripts/ci/saas_guardrails.sh
```

Known local baseline test issues are tracked in `docs/PROJECT_STATE.md`; do not hide new failures behind them.

## Deployment

1. Pull the reviewed commit.
2. Configure production `.env`.
3. Run `php spark migrate` when migrations changed.
4. Run/build assets when source CSS or frontend assets changed.
5. On Hostinger, use `RewriteBase /` and import the UTF-8 SQL dump without `CREATE DATABASE` when a dump is required.

Operational commands and the canonical dump command are in [docs/OPERATIONS_RUNBOOK.md](docs/OPERATIONS_RUNBOOK.md).

## Documentation

- Start with [docs/START_HERE.md](docs/START_HERE.md).
- Current implementation: `docs/PROJECT_STATE.md` plus module current-state files.
- Durable decisions: `docs/DECISIONS.md`.
- Recent changes: `docs/CHANGELOG.md`.
- Removed historical material: Git history.

Do not treat removed historical plans from Git history as current requirements.
