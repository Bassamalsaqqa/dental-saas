# API-MIG-01B Verification

**Task:** API-MIG-01B (Session bridge lockdown for `/api/v1/search/*`)
**Date:** 2026-01-20
**Status:** PASS

## 1) Filter Chain
Command: `php spark filter:check GET api/v1/search/patients`
```
CodeIgniter v4.6.3 Command Line Tool - Server Time: 2026-01-20 20:26:48 UTC+00:00

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

## 2) Allowlist Proofs
Command: `rg -n "SESSION_BRIDGE" app/Filters/ApiV1AuthFilter.php`
```
15:    private const SESSION_BRIDGE_MARKER = 'SESSION_BRIDGE';
35:        // SESSION_BRIDGE: search-only, file-backed session decode.
37:        $bridgeEnabled = env('API_V1_SESSION_BRIDGE_SEARCH', $feature->apiV1SessionBridgeSearch);
```

Command: `rg -n "api/v1/search" app/Filters/ApiV1AuthFilter.php`
```
20:        return str_contains($path, 'api/v1/search');
30:        return str_starts_with($path, 'api/v1/search/');
```

## 3) Runtime Evidence

### 3.1 Bearer works (stateless client)
Command: `curl.exe -i -H "Authorization: Bearer 529abfa68a044e5baad935bf59ed3cc91146fd299ad1648d98f9f14305dcc5af" -H "X-Clinic-Id: 1" http://localhost/dental-saas/api/v1/search/patients?q=a`
```http
HTTP/1.1 200 OK
Date: Tue, 20 Jan 2026 20:27:14 GMT
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

### 3.2 Bridge works when enabled (200)
Note: Temporarily set `Config\Feature::$apiV1SessionBridgeSearch = true` to capture this output.

Command: `curl.exe -i -H "Cookie: ci_session_saas=37ed93f7585ffd80e0973fb712c82074" http://localhost/dental-saas/api/v1/search/patients?q=a`
```http
HTTP/1.1 200 OK
Date: Tue, 20 Jan 2026 20:27:03 GMT
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

### 3.3 Bridge blocked when disabled (401)
Command: `curl.exe -i -H "Cookie: ci_session_saas=37ed93f7585ffd80e0973fb712c82074" http://localhost/dental-saas/api/v1/search/patients?q=a`
```http
HTTP/1.1 401 Unauthorized
Date: Tue, 20 Jan 2026 20:27:27 GMT
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

### 3.4 No Set-Cookie on bridge responses
Command: `curl.exe -i -H "Cookie: ci_session_saas=37ed93f7585ffd80e0973fb712c82074" http://localhost/dental-saas/api/v1/search/patients?q=a | findstr /I "Set-Cookie"`
```
no matches
```
