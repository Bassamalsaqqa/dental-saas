# P5-AUTH-03 Forgot Password Tenant Scope Fix

## Scope
- Prevent tenant scope exceptions during forgot-password email dispatch when logged out

## Evidence

### 1) PHP Syntax
```
php -l app/Controllers/Auth.php
```

### 2) Tenant Scope Bypass
```
rg -n "withoutTenantScope\(\)" app/Controllers/Auth.php
```
