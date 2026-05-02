# P5-AUTH-02 Forgot Password Route Hygiene

## Scope
- Align forgot/reset password redirects with hyphenated auth routes

## Evidence

### 1) PHP Syntax
```
php -l app/Controllers/Auth.php
```

### 2) Redirect Targets
```
rg -n "auth/forgot-password|auth/reset-password" app/Controllers/Auth.php
```
