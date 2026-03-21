# Local Development Setup - DentaCare Pro

Follow these steps to set up your local development environment.

## 1. Prerequisites

*   **PHP:** 8.1+
    *   Required extensions: `intl`, `mbstring`, `json`, `mysqli`, `xml`, `curl`, `gd`.
    *   *Note:* Ensure `extension=intl` is enabled in your `php.ini`.
*   **Database:** MySQL 8.0+ or MariaDB 10.3+
*   **Composer:** For managing PHP dependencies.

## 2. Environment Configuration

1.  Copy the example environment file:
    ```bash
    cp .env.example .env
    ```
2.  Open `.env` and configure your local settings:
    *   Set `CI_ENVIRONMENT = development` to enable debugging.
    *   Configure `database.default.*` with your local DB credentials.
3.  Generate an encryption key:
    ```bash
    php spark key:generate
    ```

## 3. Database Setup

### Option A: XAMPP MySQL (Current Local Standard)
For this repository on Windows/XAMPP, use the local MySQL service first:

1.  Start XAMPP Apache and MySQL.
2.  Create the database:
    ```bash
    mysql -u root -e "CREATE DATABASE IF NOT EXISTS democa_dental_saas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    ```
3.  Configure `.env`:
    ```ini
    database.default.hostname = 127.0.0.1
    database.default.database = democa_dental_saas
    database.default.username = root
    database.default.password =
    database.default.DBDriver = MySQLi
    database.default.port = 3306
    ```
4.  Load the maintained SQL dump when you need the current working schema + bootstrap data:
    ```bash
    mysql -u root democa_dental_saas < democa_dental_saas_schema_superadmin.sql
    ```
5.  Run migrations after importing:
    ```bash
    php spark migrate
    ```

### Option B: Clean Install
Use CodeIgniter's built-in migration system only when you explicitly want a fresh schema without the full working dataset:
```bash
php spark migrate
php spark db:seed RBACSeeder
```

### Option C: Docker MySQL (Optional)
Docker is still available for exporting or regenerating dumps, but it is not required for normal local runtime.
If you need to regenerate the maintained dump from the Docker container:
```bash
cmd /c "docker exec mysql8-dental mysqldump -u root -proot --default-character-set=utf8mb4 --routines --triggers --events --single-transaction --skip-lock-tables --set-charset --no-create-db democa_dental_saas --ignore-table=democa_dental_saas.control_plane_invoices --ignore-table=democa_dental_saas.control_plane_invoice_payments > democa_dental_saas_schema_superadmin.sql"
```

## 4. Web Server

### PHP Built-in Server (Easiest for Dev)
```bash
php spark serve
```
Access via: `http://localhost:8080`

### Apache/Nginx
Point your virtual host's **Document Root** to the `public/` directory.
*   **NEVER** use the repository root as the document root in production.
*   In development, if you must use the root, ensure `.htaccess` is active.
*   For this local XAMPP setup, the app is typically reached at `https://localhost/dental-saas/`.

## 5. Security Note

*   Never commit your `.env` file.
*   All sensitive scripts in `scripts/` are CLI-only.
*   `DBDebug` is only enabled when `CI_ENVIRONMENT` is `development`.
