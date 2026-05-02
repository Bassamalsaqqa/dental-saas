# Verification: API-B2-01 CSRF Exemption for /api/v1/*

## 1. Objective
Verify that `api/v1/*` routes are exempt from the global `csrf` filter, enabling stateless Bearer authentication without CSRF token management.

## 2. Configuration Change
Modified `app/Config/Filters.php`:
```php
'csrf' => ['except' => ['api/v1/*']],
```

## 3. Verification Evidence

### A) Pre-Change (CSRF Active)
**Command:**
`curl.exe -k -i -X POST https://localhost/dental-saas/api/v1/patients`

**Response:**
```http
HTTP/1.1 403 Forbidden
Date: Mon, 19 Jan 2026 12:17:13 GMT
...
{
    "title": "CodeIgniter\\Security\\Exceptions\\SecurityException",
    "type": "CodeIgniter\\Security\\Exceptions\\SecurityException",
    "code": 403,
    "message": "The action you requested is not allowed.",
    ...
}
```

### B) Post-Change (CSRF Exempt)
**Command:**
`curl.exe -k -i -X POST https://localhost/dental-saas/api/v1/patients`

**Response:**
```http
HTTP/1.1 401 Unauthorized
Date: Mon, 19 Jan 2026 12:17:27 GMT
...
{
    "error": {
        "code": "unauthenticated",
        "message": "Authentication required."
    }
}
```

## 4. Conclusion
The global CSRF filter is successfully bypassed for `/api/v1/*` routes. Authentication responsibility is now fully handled by `ApiV1AuthFilter`.
