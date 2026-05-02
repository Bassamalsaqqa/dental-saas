# P5-AUTH-04 Forgot Password Contact Surfacing

## Scope
- Show clinic admin email on forgot-password page
- Include platform support email in SMTP-misconfigured error

## Evidence

### 1) PHP Syntax
```
php -l app/Controllers/Auth.php
```

### 2) View Wiring
```
rg -n "clinic_admin_email|Clinic Admin" app/Views/auth/forgot_password.php
```

### 3) Support Email
```
rg -n "platform_support_email" app/Controllers/Auth.php
```
