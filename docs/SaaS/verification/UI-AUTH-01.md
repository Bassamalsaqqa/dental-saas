## UI-AUTH-01 Verification

Goal: Issue short-lived API tokens for session users and attach Bearer + X-Clinic-Id to `/api/v1/search/*` from the tenant UI.

Notes:
- Local HTTPS is not available via `curl.exe` in this environment. Because cookies are `Secure`, the session-token request is executed over HTTP with `X-Forwarded-Proto: https` to simulate a secure request for verification.
- Token/cookie values are redacted.

### Evidence: UI search endpoints remain on v1
```bat
rg -n "api/v1/search" app/Views
```
```
app/Views\prescription\create.php:59:                                        data-search-url="<?= base_url('api/v1/search/patients') ?>"
app/Views\prescription\edit.php:378:            url: '<?= base_url('api/v1/search/patients') ?>',
app/Views\test_searchable_select.php:20:                            data-search-url="<?= base_url('api/v1/search/patients') ?>"
app/Views\test_searchable_select.php:40:                            data-search-url="<?= base_url('api/v1/search/medications') ?>"
app/Views\test_searchable_select.php:60:                            data-search-url="<?= base_url('api/v1/search/treatment-types') ?>"
app/Views\test_searchable_select.php:80:                            data-search-url="<?= base_url('api/v1/search/departments') ?>"
app/Views\test_searchable_select.php:100:                            data-search-url="<?= base_url('api/v1/search/roles') ?>"
app/Views\test_select2.php:25:                            data-search-url="<?= base_url('api/v1/search/patients') ?>" 
app/Views\test_select2.php:80:                            data-search-url="<?= base_url('api/v1/search/treatment-types') ?>"
app/Views\test_select2.php:96:                            data-search-url="<?= base_url('api/v1/search/medications') ?>"
app/Views\treatment\edit.php:58:                                                                        data-search-url="<?= base_url('api/v1/search/patients') ?>"
app/Views\treatment\edit.php:92:                                            data-search-url="<?= base_url('api/v1/search/treatment-types') ?>"
app/Views\treatment\create.php:58:                                                                        data-search-url="<?= base_url('api/v1/search/patients') ?>"
app/Views\treatment\create.php:85:                                            data-search-url="<?= base_url('api/v1/search/treatment-types') ?>"
app/Views\finance\edit.php:58:                                            data-search-url="<?= base_url('api/v1/search/patients') ?>"
app/Views\finance\edit.php:117:                                        data-search-url="<?= base_url('api/v1/search/examinations') ?>"
app/Views\users\create.php:278:                                                        data-search-url="<?= base_url('api/v1/search/roles') ?>"
app/Views\users\create.php:309:                                                        data-search-url="<?= base_url('api/v1/search/departments') ?>"
app/Views\appointment\create.php:59:                                            data-search-url="<?= base_url('api/v1/search/patients') ?>"
app/Views\finance\create.php:59:                                            data-search-url="<?= base_url('api/v1/search/patients') ?>"
app/Views\finance\create.php:111:                                        data-search-url="<?= base_url('api/v1/search/examinations') ?>"
app/Views\appointment\edit.php:58:                                            data-search-url="<?= base_url('api/v1/search/patients') ?>"
app/Views\users\edit.php:286:                                                        data-search-url="<?= base_url('api/v1/search/roles') ?>"
app/Views\users\edit.php:336:                                                        data-search-url="<?= base_url('api/v1/search/departments') ?>"
app/Views\examination\create.php:59:                                            data-search-url="<?= base_url('api/v1/search/patients') ?>"
app/Views\examination\edit.php:34:                                            data-search-url="<?= base_url('api/v1/search/patients') ?>"
```

### Evidence: filter chain for v1 search
```bat
php spark filter:check GET api/v1/search/patients
```
```
CodeIgniter v4.6.3 Command Line Tool - Server Time: 2026-01-20 21:28:06 UTC+00:00

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

### Evidence: v1 search requires Bearer
```bat
curl.exe -i http://saas.dental.local/api/v1/search/patients?q=a
```
```
HTTP/1.1 401 Unauthorized
Date: Tue, 20 Jan 2026 21:27:48 GMT
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

```bat
curl.exe -i -H "Cookie: ci_session_saas=<REDACTED>" http://saas.dental.local/api/v1/search/patients?q=a
```
```
HTTP/1.1 401 Unauthorized
Date: Tue, 20 Jan 2026 21:27:49 GMT
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

### Evidence: session token issuance (30 minutes)
```bat
curl.exe -i -H "X-Forwarded-Proto: https" -H "Cookie: ci_session_saas=<REDACTED>" http://saas.dental.local/api/session-token
```
```
HTTP/1.1 200 OK
Date: Tue, 20 Jan 2026 21:27:26 GMT
Server: Apache/2.4.58 (Win64) OpenSSL/3.1.3 PHP/8.2.12
X-Powered-By: PHP/8.2.12
Expires: Thu, 19 Nov 1981 08:52:00 GMT
Cache-Control: no-store, no-cache, must-revalidate
Pragma: no-cache
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-Download-Options: noopen
X-Permitted-Cross-Domain-Policies: none
Referrer-Policy: same-origin
Debugbar-Time: 1768944447.067904
Debugbar-Link: https://saas.dental.local/?debugbar_time=1768944447.067904
Set-Cookie: csrf_cookie_name=<REDACTED>; expires=Tue, 20 Jan 2026 23:27:27 GMT; Max-Age=7200; path=/; secure; HttpOnly; SameSite=Lax
Content-Length: 216
Content-Type: application/json; charset=UTF-8

{
    "token": "<REDACTED>",
    "expires_at": "2026-01-20T21:57:27+00:00",
    "csrf_token": "<REDACTED>"
}
```

### Evidence: Bearer + X-Clinic-Id required
```bat
curl.exe -i -H "Authorization: Bearer <REDACTED>" http://saas.dental.local/api/v1/search/patients?q=a
```
```
HTTP/1.1 422 Unprocessable Content
Date: Tue, 20 Jan 2026 21:27:49 GMT
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

```bat
curl.exe -i -H "Authorization: Bearer <REDACTED>" -H "X-Clinic-Id: 1" http://saas.dental.local/api/v1/search/patients?q=a
```
```
HTTP/1.1 200 OK
Date: Tue, 20 Jan 2026 21:27:50 GMT
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

```bat
curl.exe -i -H "Authorization: Bearer <REDACTED>" -H "X-Clinic-Id: 1" http://saas.dental.local/api/v1/patients
```
```
HTTP/1.1 200 OK
Date: Tue, 20 Jan 2026 21:27:51 GMT
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

### Evidence: v1 remains stateless (no Set-Cookie)
```bat
curl.exe -i -H "Authorization: Bearer <REDACTED>" -H "X-Clinic-Id: 1" http://saas.dental.local/api/v1/search/patients?q=a | findstr /I "Set-Cookie"
```
```
```

```bat
curl.exe -i -H "Authorization: Bearer <REDACTED>" -H "X-Clinic-Id: 1" http://saas.dental.local/api/v1/patients | findstr /I "Set-Cookie"
```
```
```
