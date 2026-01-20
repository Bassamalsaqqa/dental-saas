# API-MIG-01 Verification

**Task:** API-MIG-01 (Migrate UI search to `/api/v1/search/*` with session bridge)
**Date:** 2026-01-20
**Status:** PASS

## 1) Evidence: View Endpoint Replacement
Representative view update:
Command: `rg -n "api/v1/search/patients" app/Views/examination/create.php`
```
59:                                            data-search-url="<?= base_url('api/v1/search/patients') ?>"
```

## 2) Evidence: No Legacy `/api/search/*` Usage in app
Command: `rg -n "api/search" app`
```
no matches
```

## 3) Filter Chain Checks
Command: `php spark filter:check GET api/search/patients`
```
CodeIgniter v4.6.3 Command Line Tool - Server Time: 2026-01-20 20:04:06 UTC+00:00

+--------+---------------------+----------------------------------------------+-----------------------------------------------------------------------------------------+
| Method | Route               | Before Filters                               | After Filters                                                                           |
+--------+---------------------+----------------------------------------------+-----------------------------------------------------------------------------------------+
| GET    | api/search/patients | auth tenant subscription permission:patients | permission:patients subscription tenant auth secureheaders csrfjson toolbar performance |
+--------+---------------------+----------------------------------------------+-----------------------------------------------------------------------------------------+

Before Filter Classes:
App\Filters\AuthFilter → App\Filters\TenantFilter → App\Filters\SubscriptionFilter → App\Filters\PermissionFilter:patients
After Filter Classes:
App\Filters\PermissionFilter:patients → App\Filters\SubscriptionFilter → App\Filters\TenantFilter → App\Filters\AuthFilter → CodeIgniter\Filters\SecureHeaders → App\Filters\CsrfJson → CodeIgniter\Filters\DebugToolbar → CodeIgniter\Filters\PerformanceMetrics
```

Command: `php spark filter:check GET api/v1/search/patients`
```
CodeIgniter v4.6.3 Command Line Tool - Server Time: 2026-01-20 20:04:00 UTC+00:00

+--------+------------------------+---------------------------+-----------------------------------------------------+
| Method | Route                  | Before Filters            | After Filters                                       |
+--------+------------------------+---------------------------+-----------------------------------------------------+
| GET    | api/v1/search/patients | apiv1 permission:patients | permission:patients apiv1 secureheaders performance |
+--------+------------------------+---------------------------+-----------------------------------------------------+

Before Filter Classes:
App\Filters\ApiV1AuthFilter → App\Filters\PermissionFilter:patients
After Filter Classes:
App\Filters\PermissionFilter:patients → App\Filters\ApiV1AuthFilter → CodeIgniter\Filters\SecureHeaders → CodeIgniter\Filters\PerformanceMetrics
```

## 4) Runtime Evidence

### 4.1 Unauthenticated (no Bearer, no session)
Command: `curl.exe -i http://localhost/dental-saas/api/v1/search/patients?q=a`
```http
HTTP/1.1 401 Unauthorized
Date: Tue, 20 Jan 2026 20:08:21 GMT
Server: Apache/2.4.58 (Win64) OpenSSL/3.1.3 PHP/8.2.12
X-Powered-By: PHP/8.2.12
Cache-Control: no-store, max-age=0, no-cache
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
Referrer-Policy: same-origin
X-Permitted-Cross-Domain-Policies: none
Content-Length: 105
Content-Type: application/json; charset=UTF-8

{
    "error": {
        "code": "unauthenticated",
        "message": "Authentication required."
    }
}
```

### 4.2 Bearer + X-Clinic-Id (stateless client)
Command: `curl.exe -i -H "Authorization: Bearer 529abfa68a044e5baad935bf59ed3cc91146fd299ad1648d98f9f14305dcc5af" -H "X-Clinic-Id: 1" http://localhost/dental-saas/api/v1/search/patients?q=a`
```http
HTTP/1.1 200 OK
Date: Tue, 20 Jan 2026 20:08:12 GMT
Server: Apache/2.4.58 (Win64) OpenSSL/3.1.3 PHP/8.2.12
X-Powered-By: PHP/8.2.12
Cache-Control: no-store, max-age=0, no-cache
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-Download-Options: noopen
X-Permitted-Cross-Domain-Policies: none
Referrer-Policy: same-origin
Content-Length: 840
Content-Type: application/json; charset=UTF-8

{
    "results": [
        {
            "id": "20",
            "text": "Alice Baker (1234567890)",
            "value": "20"
        },
        {
            "id": "24",
            "text": "clinicA quota exceed (+123456789)",
            "value": "24"
        },
        {
            "id": "25",
            "text": "clinicA quotaaa exceeded (+123456789)",
            "value": "25"
        },
        {
            "id": "26",
            "text": "clinicA quotaaab exceeded (+123456789)",
            "value": "26"
        },
        {
            "id": "22",
            "text": "Echo Foxtrot (1234567890)",
            "value": "22"
        },
        {
            "id": "23",
            "text": "Golf Hotel (1234567890)",
            "value": "23"
        }
    ],
    "total": 6,
    "pagination": {
        "more": false
    }
}
```

### 4.3 Session Bridge (tenant UI)
Session cookie used: `ci_session_saas=37ed93f7585ffd80e0973fb712c82074` (file-backed session with `user_id=1` and `active_clinic_id=1`)

Command: `curl.exe -i -H "Cookie: ci_session_saas=37ed93f7585ffd80e0973fb712c82074" http://localhost/dental-saas/api/v1/search/patients?q=a`
```http
HTTP/1.1 200 OK
Date: Tue, 20 Jan 2026 20:07:57 GMT
Server: Apache/2.4.58 (Win64) OpenSSL/3.1.3 PHP/8.2.12
X-Powered-By: PHP/8.2.12
Cache-Control: no-store, max-age=0, no-cache
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-Download-Options: noopen
X-Permitted-Cross-Domain-Policies: none
Referrer-Policy: same-origin
Content-Length: 840
Content-Type: application/json; charset=UTF-8

{
    "results": [
        {
            "id": "20",
            "text": "Alice Baker (1234567890)",
            "value": "20"
        },
        {
            "id": "24",
            "text": "clinicA quota exceed (+123456789)",
            "value": "24"
        },
        {
            "id": "25",
            "text": "clinicA quotaaa exceeded (+123456789)",
            "value": "25"
        },
        {
            "id": "26",
            "text": "clinicA quotaaab exceeded (+123456789)",
            "value": "26"
        },
        {
            "id": "22",
            "text": "Echo Foxtrot (1234567890)",
            "value": "22"
        },
        {
            "id": "23",
            "text": "Golf Hotel (1234567890)",
            "value": "23"
        }
    ],
    "total": 6,
    "pagination": {
        "more": false
    }
}
```

### 4.4 Session Bridge Cookie Suppression
Command: `curl.exe -i -H "Cookie: ci_session_saas=37ed93f7585ffd80e0973fb712c82074" http://localhost/dental-saas/api/v1/search/patients?q=a | findstr /I "Set-Cookie"`
```
no matches
```
