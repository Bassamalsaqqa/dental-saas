# API-BRIDGE-01 Verification

**Task:** API-BRIDGE-01 (Remove v1 session bridge)
**Date:** 2026-01-20
**Status:** PASS

## 1) Filter Chain
Command: `php spark filter:check GET api/v1/search/patients`
```
CodeIgniter v4.6.3 Command Line Tool - Server Time: 2026-01-20 21:14:41 UTC+00:00

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

## 2) Bridge Code Removal
Command: `rg -n "SESSION_BRIDGE|API_V1_SESSION_BRIDGE_SEARCH|decodeSessionData|resolveSessionBridgeContext" app/Filters/ApiV1AuthFilter.php`
```
no matches
```

## 3) Runtime Evidence (Bearer-only)

### 3.1 No Bearer (401)
Command: `curl.exe -i http://saas.dental.local/api/v1/search/patients?q=a`
```http
HTTP/1.1 401 Unauthorized
Date: Tue, 20 Jan 2026 21:14:43 GMT
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

### 3.2 Cookie-only (401)
Command: `curl.exe -i -H "Cookie: ci_session_saas=<REDACTED>" http://saas.dental.local/api/v1/search/patients?q=a`
```http
HTTP/1.1 401 Unauthorized
Date: Tue, 20 Jan 2026 21:14:44 GMT
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

### 3.3 Bearer without clinic (422)
Command: `curl.exe -i -H "Authorization: Bearer <REDACTED>" http://saas.dental.local/api/v1/search/patients?q=a`
```http
HTTP/1.1 422 Unprocessable Content
Date: Tue, 20 Jan 2026 21:14:52 GMT
Server: Apache/2.4.58 (Win64) OpenSSL/3.1.3 PHP/8.2.12
X-Powered-By: PHP/8.2.12
Cache-Control: no-store, max-age=0, no-cache
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
Referrer-Policy: same-origin
X-Permitted-Cross-Domain-Policies: none
Content-Length: 119
Content-Type: application/json; charset=UTF-8

{
    "error": {
        "code": "tenant_context_missing",
        "message": "X-Clinic-Id header is required."
    }
}
```

### 3.4 Bearer + clinic (200)
Command: `curl.exe -i -H "Authorization: Bearer <REDACTED>" -H "X-Clinic-Id: 1" http://saas.dental.local/api/v1/search/patients?q=a`
```http
HTTP/1.1 200 OK
Date: Tue, 20 Jan 2026 21:14:54 GMT
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

### 3.5 Bearer sanity (patients)
Command: `curl.exe -i -H "Authorization: Bearer <REDACTED>" -H "X-Clinic-Id: 1" http://saas.dental.local/api/v1/patients`
```http
HTTP/1.1 200 OK
Date: Tue, 20 Jan 2026 21:14:56 GMT
Server: Apache/2.4.58 (Win64) OpenSSL/3.1.3 PHP/8.2.12
X-Powered-By: PHP/8.2.12
Cache-Control: no-store, max-age=0, no-cache
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-Download-Options: noopen
X-Permitted-Cross-Domain-Policies: none
Referrer-Policy: same-origin
Content-Length: 1148
Content-Type: application/json; charset=UTF-8

[
    {
        "id": "26",
        "patient_id": "PAT20266558",
        "first_name": "clinicA quotaaab",
        "last_name": "exceeded",
        "status": "active"
    },
    {
        "id": "25",
        "patient_id": "PAT20265728",
        "first_name": "clinicA quotaaa",
        "last_name": "exceeded",
        "status": "active"
    },
    {
        "id": "24",
        "patient_id": "PAT20264920",
        "first_name": "clinicA quota",
        "last_name": "exceed",
        "status": "active"
    },
    {
        "id": "20",
        "patient_id": "PAT20265297",
        "first_name": "Alice",
        "last_name": "Baker",
        "status": "active"
    },
    {
        "id": "21",
        "patient_id": "PAT20263155",
        "first_name": "Charlie",
        "last_name": "Delta",
        "status": "inactive"
    },
    {
        "id": "22",
        "patient_id": "PAT20262630",
        "first_name": "Echo",
        "last_name": "Foxtrot",
        "status": "active"
    },
    {
        "id": "23",
        "patient_id": "PAT20264727",
        "first_name": "Golf",
        "last_name": "Hotel",
        "status": "active"
    }
]
```

## 4) Transitional Note
Tenant UI cookie-only calls to `/api/v1/search/*` will now return 401 until UI is updated to send Bearer tokens.
