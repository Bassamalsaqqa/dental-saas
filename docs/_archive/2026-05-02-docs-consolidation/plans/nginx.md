# Nginx Security Configuration

This document outlines the required Nginx configuration for securing the DentaCare Pro application.

## 1. Standard Configuration (Docroot = public/)

This is the recommended configuration where the web server's document root is set to the `public/` directory.

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/dental/public;
    index index.php index.html;

    # ----------------------------------------------------------------------
    # Security Hardening (P0-02)
    # ----------------------------------------------------------------------

    # Block access to dotfiles
    location ~ /\.(?!well-known) {
        deny all;
    }

    # Block access to specific sensitive files if they accidentally exist in public
    location ~ ^/(ENVIRONMENT|environment\.env|composer\.json|composer\.lock|package\.json|package-lock\.json)$ {
        deny all;
    }

    # Block access to sensitive extensions
    location ~ \.(sql|log|bak|dist|ini|sh|md|env|dump)$ {
        deny all;
    }

    # Block access to potentially dangerous scripts
    location ~ ^/(install|debug_env|repair_db.*|init_database|simple_login_test)\.php$ {
        deny all;
    }

    # ----------------------------------------------------------------------
    # Application Routing
    # ----------------------------------------------------------------------

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock; # Adjust PHP version as needed
    }
}
```

## 2. Legacy Configuration (Docroot = Repo Root)

If you **must** serve the application from the repository root (not recommended), you must explicitly block access to sensitive directories.

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/dental; # Pointing to repo root
    index index.php index.html;

    # ----------------------------------------------------------------------
    # Security Hardening (P0-02)
    # ----------------------------------------------------------------------

    # Block access to sensitive directories (CRITICAL)
    location ~ ^/(app|system|tests|writable|scripts|docs|lib|src|vendor|node_modules)/ {
        deny all;
    }

    # Block access to dotfiles
    location ~ /\.(?!well-known) {
        deny all;
    }

    # Block access to specific sensitive files
    location ~ ^/(ENVIRONMENT|environment\.env|composer\.json|composer\.lock|package\.json|package-lock\.json)$ {
        deny all;
    }

    # Block access to sensitive extensions
    location ~ \.(sql|log|bak|dist|ini|sh|md|dump)$ {
        deny all;
    }

    # Block access to potentially dangerous scripts
    location ~ ^/(install|debug_env|repair_db.*|init_database|simple_login_test)\.php$ {
        deny all;
    }

    # ----------------------------------------------------------------------
    # Application Routing
    # ----------------------------------------------------------------------

    # Route requests to public/index.php if not a static file
    location / {
        try_files $uri $uri/ /public/index.php$is_args$args;
    }

    # Handle assets in public/
    location /assets/ {
        try_files $uri /public/assets/$uri;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    }
}
```

## 3. Verification

After reloading Nginx (`sudo nginx -t && sudo systemctl reload nginx`), verify protection:

Example checks:
- `curl -I https://your-domain.com/.env` -> 403 Forbidden
- `curl -I https://your-domain.com/scripts/repair_db_final.php` -> 403 Forbidden
- `curl -I https://your-domain.com/database.dump` -> 403 Forbidden