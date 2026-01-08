# Hardened Deployment Guide - DentaCare Pro

**Status:** Production-Ready
**Security Level:** Hardened

This is the **canonical guide** for deploying DentaCare Pro securely. It supersedes all previous installation instructions.

## 1. Security Architecture

DentaCare Pro enforces a **Secure-by-Design** deployment model:
*   **No Browser Installers:** All setup is performed via CLI.
*   **No Hardcoded Secrets:** Configuration is strictly environment-based.
*   **Fail-Closed Security:** Web server rules explicitly block sensitive files.

## 2. Server Requirements

*   **PHP:** 8.1 or higher (Extensions: intl, mbstring, json, mysql, xml, curl)
*   **Database:** MySQL 5.7+ or MariaDB 10.3+
*   **Web Server:** Apache 2.4+ or Nginx
*   **OS:** Linux (Ubuntu/Debian recommended)

## 3. Directory Structure & Web Root

### Recommended Setup (Standard)
Point your web server's **Document Root** to the `public/` directory.
*   Path: `/var/www/dental/public`
*   URL: `https://your-domain.com/`

### Legacy Setup (Repo Root - NOT Recommended)
If you must serve from the repository root:
*   Path: `/var/www/dental`
*   URL: `https://your-domain.com/public/`
*   **CRITICAL:** You MUST ensure the root `.htaccess` rules (or Nginx equivalents) are active to block access to `app/`, `system/`, and `.env`.

**See:**
*   [Apache Configuration](apache.md)
*   [Nginx Configuration](nginx.md)

## 4. Configuration (Secrets Management)

1.  **Copy Template:**
    ```bash
    cp .env.example .env
    ```
2.  **Generate Credentials:**
    *   **Encryption Key:** Run `php spark key:generate` or generate a random 32-char hex string.
    *   **Database:** Create a secure database user/password via MySQL CLI.
3.  **Configure Environment:**
    Edit `.env` and set the following (never commit this file):
    ```ini
    CI_ENVIRONMENT = production
    app.baseURL = 'https://your-domain.com/'
    app.forceGlobalSecureRequests = true
    
    database.default.hostname = localhost
    database.default.database = dentacare_pro
    database.default.username = <generated_db_user>
    database.default.password = <generated_db_pass>
    ```

## 5. Database Setup (CLI Only)

Browser-based database setup scripts (`init_database.php`, `repair_db.php`) have been **removed** for security.

1.  **Import Schema:**
    Use the CLI to import the initial schema (ensure your SQL dump is secure and not in the webroot).
    ```bash
    mysql -u <db_user> -p <db_name> < /path/to/secure/schema.sql
    ```

2.  **Verify:**
    Check that tables exist:
    ```bash
    mysql -u <db_user> -p -e "SHOW TABLES;" <db_name>
    ```

## 6. Admin Bootstrap & Rotation

1.  **Initial Access:**
    The system generates a temporary admin account during the initial database seed (or manual insertion).
    *   **Username/Email:** (Provided via secure channel/CLI output)
    *   **Initial Password:** (Provided via secure channel/CLI output)

2.  **Mandatory Rotation:**
    Upon first login, you MUST:
    *   Change the admin password immediately.
    *   Setup Two-Factor Authentication (if enabled).
    *   Verify SMTP settings for password recovery.

## 7. Operational Hygiene

*   **Log Rotation:** Ensure `writable/logs` are rotated and not web-accessible.
*   **Backups:** Store backups **outside** the webroot (e.g., S3, off-site). Never leave `.sql` files in `public/` or `writable/`.
*   **Updates:** Apply security updates via `composer update` and check `docs/SECURITY_HARDENING_PROGRAM.md`.

## 8. Verification

After deployment, run the verification checks:
*   [Verification Checklist](../verification/P0-02.md) (Server Rules)
*   [Verification Checklist](../verification/P0-03.md) (Secrets)
