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

### Option A: Clean Install (Recommended)
Use CodeIgniter's built-in migration system:
```bash
php spark migrate
php spark db:seed RBACSeeder
```

### Option B: Local SQL Dump
If a pre-populated dump is provided to you out-of-band:
1.  Place the dump (e.g., `democa_dental.sql`) in the project root.
2.  Import via CLI:
    ```bash
    mysql -u your_user -p your_database < democa_dental.sql
    ```
    *Note: `.sql` files in the root are ignored by git to prevent accidental secret exposure.*

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

## 5. Security Note

*   Never commit your `.env` file.
*   All sensitive scripts in `scripts/` are CLI-only.
*   `DBDebug` is only enabled when `CI_ENVIRONMENT` is `development`.
