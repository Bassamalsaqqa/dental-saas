# P5-AUTH-01 Tenant SMTP Forgot Password

## Scope
- Send forgot-password emails using clinic SMTP config
- Show requester notice when SMTP not configured
- Log activity for blocked password reset email
- Fix reset link route in email template

## Evidence

### 1) Routes
```
php spark routes | findstr /I "auth/forgot-password auth/reset-password"
```

### 2) PHP Syntax
```
php -l app/Controllers/Auth.php
```

### 3) Email Template Link
```
rg -n "reset-password" app/Views/auth/email/forgot_password.tpl.php
```

### 4) Activity Log Action
```
rg -n "password_reset_failed|auth" app/Controllers/Auth.php app/Controllers/ActivityLog.php app/Models/ActivityLogModel.php
```
