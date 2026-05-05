# Deployment & Local Setup

## Local Development Setup (Windows / XAMPP)

### Prerequisites
- PHP 8.1+ (extensions: `intl`, `mbstring`, `json`, `mysqli`, `xml`, `curl`, `gd`)
- MySQL 8.0+ or MariaDB 10.3+
- Composer
- Node.js (for Tailwind CSS build)

### Setup Steps

1. Clone repo into XAMPP htdocs: `C:\xampp\htdocs\dental-saas`
2. Start XAMPP Apache + MySQL
3. Copy `.env.example` to `.env` and configure:
   ```ini
   CI_ENVIRONMENT = development
   database.default.hostname = 127.0.0.1
   database.default.database = democa_dental_saas
   database.default.username = root
   database.default.password =
   database.default.DBDriver = MySQLi
   database.default.port = 3306
   app.baseURL = http://localhost/dental-saas/
   session.cookieName = ci_session_saas
   ```
4. Generate encryption key: `php spark key:generate`
5. Create database: `mysql -u root -e "CREATE DATABASE IF NOT EXISTS democa_dental_saas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"`
6. Import SQL dump: `mysql -u root democa_dental_saas < democa_dental_saas_schema_superadmin.sql`
7. Run migrations: `php spark migrate`
8. Install frontend deps: `npm install`
9. Build Tailwind: `npm run build`

### Docker MySQL (Optional)

Container `mysql8-dental` (image `mysql:8.0`, port 3307) is available for dump export. Not required for normal local runtime.

Regenerate dump from Docker:
```bash
cmd /c "docker exec mysql8-dental mysqldump -u root -proot --default-character-set=utf8mb4 --routines --triggers --events --single-transaction --skip-lock-tables --set-charset --no-create-db democa_dental_saas --ignore-table=democa_dental_saas.control_plane_invoices --ignore-table=democa_dental_saas.control_plane_invoice_payments > democa_dental_saas_schema_superadmin.sql"
```

### Frontend Asset Pipeline

- Source: `src/input.css` → Output: `public/assets/css/tailwind.css`
- Browser requests `/assets/...`, root `.htaccess` aliases to `public/assets/...`
- Hostinger does not run the Node/Tailwind build — deploy `public/assets/css/tailwind.css` with your PHP changes
- For Hostinger workflow: `npm run release:hostinger`

### React Shell Build

The Lovable React shell is separate from the legacy Tailwind/PHP asset pipeline.

- Source: `frontend/`
- Vite base: `/app/`
- Build output: `public/app/`

Build commands:

```powershell
cd frontend
npm install
npm run build
```

The compiled output under `public/app/` is intended to be deployed with the PHP app. Do not commit `frontend/node_modules/` or `frontend/dist/`.

## Apache Configuration

DocumentRoot MUST point to `public/` directory. If serving from repo root (legacy), root `.htaccess` must block `app/`, `system/`, `scripts/`, `.env`, etc.

Root `.htaccess` includes:
- Block dotfiles (except `.well-known`)
- Block sensitive files: `ENVIRONMENT`, `composer.json`, `composer.lock`, `package.json`
- Block sensitive extensions: `.sql`, `.log`, `.bak`, `.ini`, `.sh`, `.md`, `.dump`
- Block dangerous scripts: `install.php`, `debug_env.php`, `repair_db*.php`, `init_database.php`, `simple_login_test.php`
- Block sensitive directories: `app/`, `system/`, `tests/`, `writable/`, `scripts/`, `docs/`, `lib/`, `src/`, `vendor/`, `node_modules/`

Public `.htaccess` includes defense-in-depth duplicate blocking.

## Nginx Configuration

Recommended: DocumentRoot = `public/`. Block dotfiles, sensitive files/extensions/scripts at server level.

Legacy (repo root): explicitly block `app/`, `system/`, `tests/`, `writable/`, `scripts/`, `docs/`, `lib/`, `src/`, `vendor/`, `node_modules/` directories.

## Security Notes

- Never commit `.env` file
- All scripts in `scripts/` are CLI-only
- `DBDebug` only enabled in development (`CI_ENVIRONMENT`)
- `$forceGlobalSecureRequests = true` in production
- Archived deployment docs: `docs/_archive/2026-05-02-docs-consolidation/`
