# DentaCare SaaS

<!-- proprietary-license-notice: Bassamalsaqqa -->
> **License:** Proprietary. Copyright (c) 2026 Bassamalsaqqa. All rights reserved for the original application code. Third-party components retain their own licenses. See [LICENSE](LICENSE) and [THIRD_PARTY_NOTICES.md](THIRD_PARTY_NOTICES.md).

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

## Local setup and continuing on another machine

For the complete incoming engineer onboarding guide, architecture boundaries, and synthetic test tiers, read [docs/DEVELOPER_ONBOARDING.md](docs/DEVELOPER_ONBOARDING.md).

The authoritative branch is `release/production-20261017`, also the GitHub default. Historical `main` is not the current application. Repository housekeeping commits may advance documentation without changing the application installed in production; see [current state](docs/PROJECT_STATE.md).

Requirements: PHP 8.2+ with the extensions required by `composer.lock` (including `intl`, `mysqli`, XML and the documented polyfills), Composer, Node/npm, MySQL/MariaDB and a web server. Production was verified on PHP 8.3.36 / MariaDB 11.8.9. Docker is optional; this release has no authoritative Docker/Compose stack.

```powershell
git clone --branch release/production-20261017 https://github.com/Bassamalsaqqa/dental-saas.git
Set-Location dental-saas
composer install --no-interaction --no-scripts --no-plugins
npm.cmd ci --ignore-scripts --no-fund
Copy-Item .env.example .env
```

Copy `.env.example` only in a new checkout where `.env` does not already exist. Configure that file privately for a new, isolated local database, development URLs and a fresh development-only encryption key. The example defaults are not ready-to-run development settings. Keep email/WhatsApp dispatch and optional features OFF. Never connect development to production or reuse production integration credentials.

GitHub contains source and locked dependencies, not a ready-made business database, customer uploads, encryption secrets, sessions or recovery keys. Transfer any needed synthetic fixture/approved schema separately and securely. Do not restore a customer backup merely to obtain development fixtures. Do not run blanket migrations or seeders against an existing database; migration inventory and bootstrap compatibility require explicit review for the chosen disposable target. The canonical local dump filename is not proof that its schema matches the current release.

Existing local business data remains at `democa_dental_saas`, `127.0.0.1:3306`; `.env` is authoritative. Its MySQL service was stopped during cleanup, so database-backed local workflow readiness has not been certified. Do not overwrite it to make a new setup pass. After a compatible isolated database is prepared, use a web document root at `public/` or a local-only development server; never expose a disposable fixture server publicly.

### Asset preservation

The committed `public/assets/css/tailwind.css` is a verified prebuilt artifact, SHA256 `3a51d2fffb54ae89fbb4188e3b1ddfc2bce09c7e81148adba78ce750b7fd801a`. Keep it unchanged during setup. A routine `npm run build` produces different scanner-derived output and must not overwrite this reviewed asset. For a future authorized CSS change, build to a temporary output, verify all runtime/print consumers, and review the resulting artifact separately. No dependency update is implied by installation from the existing locks.

Prefer an Apache/Nginx document root at `public/`. If the repository root is served, web-server rules must block dotfiles, `app`, `system`, `tests`, `writable`, `scripts`, `docs`, `vendor`, `node_modules`, source/config files, dumps, logs and browser-accessible maintenance scripts.

## Common verification

```powershell
composer validate --no-check-publish
composer check-platform-reqs
php spark routes
bash scripts/ci/saas_guardrails.sh
git status -sb
```

These commands check source/platform/routes; they do not certify successful clinical or financial persistence. Run SQL/browser mutation suites only after proving the target is isolated and synthetic. Keep GitHub Actions OFF. See [current state](docs/PROJECT_STATE.md) for measured evidence and genuine remaining limitations.

## Deployment

Production is pinned to `release/production-20261017`, not `main`. See [current state](docs/PROJECT_STATE.md) and obtain separate Owner release authorization.

1. Verify the exact installed SHA/tree and approved candidate, independent review and recoverable checkpoint.
2. Enumerate migration differences explicitly. Do not run blanket migrations: dynamic scheduling is OFF and its pending migration is deliberately excluded.
3. Apply the reviewed pinned source-only procedure while preserving production `.env`, both `.htaccess` files, writable storage and uploads. Never import a local dump over customer data.
4. Verify source/ref parity, protected-file hashes, essential HTTP assets and data integrity. Use the exact source-only rollback checkpoint on failure; never restore the customer database automatically.

Operational commands and the canonical dump command are in [docs/OPERATIONS_RUNBOOK.md](docs/OPERATIONS_RUNBOOK.md).

## Documentation

- Start with [docs/START_HERE.md](docs/START_HERE.md) and [docs/DEVELOPER_ONBOARDING.md](docs/DEVELOPER_ONBOARDING.md).
- Current implementation: docs/PROJECT_STATE.md plus module current-state files.
- Durable decisions: docs/DECISIONS.md.
- Component & journey contracts: [docs/UI_STUDIO_COMPONENTS.md](docs/UI_STUDIO_COMPONENTS.md).
- Operations & runbook: [docs/OPERATIONS_RUNBOOK.md](docs/OPERATIONS_RUNBOOK.md).
- Recent changes: docs/CHANGELOG.md.
- Historical modernization & acceptance pointers: [docs/refactor/README.md](docs/refactor/README.md) and Git history.

Do not treat removed historical plans from Git history as current requirements.
