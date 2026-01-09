# Apache Security Configuration

This document outlines the required Apache configuration for securing the DentaCare Pro application.

## 0. Document Root (CRITICAL)

For security reasons, the **DocumentRoot** of your virtual host MUST point to the `public/` directory:
```apache
DocumentRoot "/var/www/dental/public"
```
Pointing the DocumentRoot to the repository root is considered insecure and requires active `.htaccess` protection.

## 1. Root .htaccess (Hardening)

The `.htaccess` file in the repository root (`/`) must include the following blocking rules **before** any rewrite logic. This is critical if the web server's document root is set to the repository root (legacy hosting).

It includes compatibility checks for Apache 2.4 (`mod_authz_core`) and fallback rules for Apache 2.2.

```apache
# ----------------------------------------------------------------------
# Security Hardening (P0-02)
# ----------------------------------------------------------------------

# Apache 2.4+
<IfModule mod_authz_core.c>
    # Block access to dotfiles (except .htaccess)
    <FilesMatch "^\.(?!well-known)">
        Require all denied
    </FilesMatch>

    # Block access to specific sensitive files
    <FilesMatch "^(ENVIRONMENT|environment\.env|composer\.json|composer\.lock|package\.json|package-lock\.json)$">
        Require all denied
    </FilesMatch>

    # Block access to sensitive extensions
    <FilesMatch "\.(sql|log|bak|dist|ini|sh|md|dump)$">
        Require all denied
    </FilesMatch>

    # Block access to potentially dangerous scripts
    <FilesMatch "^(install|debug_env|repair_db.*|init_database|simple_login_test)\.php$">
        Require all denied
    </FilesMatch>
</IfModule>

# Apache 2.2 Fallback
<IfModule !mod_authz_core.c>
    <FilesMatch "^\.(?!well-known)">
        Order allow,deny
        Deny from all
    </FilesMatch>
    <FilesMatch "^(ENVIRONMENT|environment\.env|composer\.json|composer\.lock|package\.json|package-lock\.json)$">
        Order allow,deny
        Deny from all
    </FilesMatch>
    <FilesMatch "\.(sql|log|bak|dist|ini|sh|md|dump)$">
        Order allow,deny
        Deny from all
    </FilesMatch>
    <FilesMatch "^(install|debug_env|repair_db.*|init_database|simple_login_test)\.php$">
        Order allow,deny
        Deny from all
    </FilesMatch>
</IfModule>

# Disable directory browsing
Options -Indexes

# ----------------------------------------------------------------------
# Rewrite Rules (Routing & Directory Blocking)
# ----------------------------------------------------------------------
RewriteEngine On

# Block access to sensitive directories (Repo-Root Docroot Scenario)
# This MUST be before other rewrite rules.
RewriteRule ^(app|system|tests|writable|scripts|docs|lib|src|vendor|node_modules)/ - [F,L]
```

## 2. Public .htaccess (Defense in Depth)

The `.htaccess` file in the `public/` directory includes similar blocking rules to prevent accidental exposure of sensitive files.

```apache
# ----------------------------------------------------------------------
# Security Hardening (P0-02)
# ----------------------------------------------------------------------

# Apache 2.4+
<IfModule mod_authz_core.c>
    # Block access to dotfiles
    <FilesMatch "^\.(?!well-known)">
        Require all denied
    </FilesMatch>

    # Block sensitive extensions if they ever appear in public
    <FilesMatch "\.(sql|log|bak|env|ini|sh|dump)$">
        Require all denied
    </FilesMatch>

    # Block specific dangerous scripts if mistakenly placed here
    <FilesMatch "^(install|debug_env|repair_db.*|init_database)\.php$">
        Require all denied
    </FilesMatch>
</IfModule>

# Apache 2.2 Fallback
<IfModule !mod_authz_core.c>
    <FilesMatch "^\.(?!well-known)">
        Order allow,deny
        Deny from all
    </FilesMatch>
    <FilesMatch "\.(sql|log|bak|env|ini|sh|dump)$">
        Order allow,deny
        Deny from all
    </FilesMatch>
    <FilesMatch "^(install|debug_env|repair_db.*|init_database)\.php$">
        Order allow,deny
        Deny from all
    </FilesMatch>
</IfModule>

# Disable directory browsing
Options -Indexes
```

## 3. Verification

After deploying these rules, verify that accessing sensitive files returns a 403 Forbidden or 404 Not Found error.

Example checks:
- `curl -I https://your-domain.com/.env` -> 403 Forbidden
- `curl -I https://your-domain.com/scripts/repair_db_final.php` -> 403 Forbidden
- `curl -I https://your-domain.com/database.dump` -> 403 Forbidden
