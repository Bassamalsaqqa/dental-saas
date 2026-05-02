# API-AUTH-01 Verification

**Task:** API-AUTH-01 (Token validation root-cause + fix)
**Date:** 2026-01-20
**Status:** PASS

## 1) DB Target Confirmation
DB settings source:
Command: `rg -n "^database\\.default\\.(hostname|port|database)" .env`
```
19:database.default.hostname = 127.0.0.1
20:database.default.database = democa_dental_saas
24:database.default.port = 3307
```

Command: `SELECT DATABASE() AS db, @@hostname AS host, @@port AS port;`
```
mysql: [Warning] Using a password on the command line interface can be insecure.
db	host	port
democa_dental_saas	7eb3024aa7d5	3306
```

## 2) Schema Evidence (api_tokens)
Command: `SHOW COLUMNS FROM api_tokens;`
```
mysql: [Warning] Using a password on the command line interface can be insecure.
Field	Type	Null	Key	Default	Extra
id	int unsigned	NO	PRI	NULL	auto_increment
user_id	int unsigned	NO	MUL	NULL	
name	varchar(100)	YES		NULL	
token_hash	varchar(64)	NO	UNI	NULL	
last_used_at	datetime	YES		NULL	
expires_at	datetime	YES		NULL	
created_at	datetime	YES		NULL	
updated_at	datetime	YES		NULL	
```
Note: `revoked_at` column does not exist in this schema.

## 3) Recent Token Rows (sanity)
Command: `SELECT id, user_id, name, LENGTH(token_hash) AS len, expires_at, created_at, updated_at FROM api_tokens ORDER BY id DESC LIMIT 5;`
```
mysql: [Warning] Using a password on the command line interface can be insecure.
id	user_id	name	len	expires_at	created_at	updated_at
8	1	codex-report	64	2026-01-21 19:47:17	2026-01-20 19:47:17	2026-01-20 20:27:14
6	1	Verification Token	64	2026-01-20 20:02:44	2026-01-20 19:02:44	2026-01-20 19:21:01
3	1	Manual Generation	64	NULL	2026-01-18 19:43:38	2026-01-18 20:15:43
2	1	dev	64	NULL	2026-01-18 19:32:15	2026-01-19 12:24:47
1	1	Manual Generation	64	NULL	2026-01-18 18:53:50	2026-01-18 19:43:07
```

## 4) Hash Format Proof (length only)
Command: PHP one-liner showing length only
```
hex_len=64
bin_len=32
```

## 5) Filter Chain
Command: `php spark filter:check GET api/v1/patients`
```
CodeIgniter v4.6.3 Command Line Tool - Server Time: 2026-01-20 21:07:42 UTC+00:00

+--------+-----------------+---------------------------+-----------------------------------------------------+
| Method | Route           | Before Filters            | After Filters                                       |
+--------+-----------------+---------------------------+-----------------------------------------------------+
| GET    | api/v1/patients | apiv1 permission:patients | permission:patients apiv1 secureheaders performance |
+--------+-----------------+---------------------------+-----------------------------------------------------+

Before Filter Classes:
App\Filters\ApiV1AuthFilter → App\Filters\PermissionFilter:patients
After Filter Classes:
App\Filters\PermissionFilter:patients → App\Filters\ApiV1AuthFilter → CodeIgniter\Filters\SecureHeaders → CodeIgniter\Filters\PerformanceMetrics
```

## 6) Runtime Evidence (curl)

### 6.1 Unauthenticated (no Bearer)
Command: `curl.exe -i http://localhost/dental-saas/api/v1/patients`
```http
HTTP/1.1 401 Unauthorized
Date: Tue, 20 Jan 2026 21:07:43 GMT
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

### 6.2 Valid Bearer (not 401)
Command: `curl.exe -i -H "Authorization: Bearer <REDACTED>" -H "X-Clinic-Id: 1" http://localhost/dental-saas/api/v1/patients`
```http
HTTP/1.1 200 OK
Date: Tue, 20 Jan 2026 21:07:43 GMT
Server: Apache/2.4.58 (Win64) OpenSSL/3.1.3 PHP/8.2.12
Vary: Authorization
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

### 6.3 Invalid Bearer (401)
Command: `curl.exe -i -H "Authorization: Bearer invalid" -H "X-Clinic-Id: 1" http://localhost/dental-saas/api/v1/patients`
```http
HTTP/1.1 401 Unauthorized
Date: Tue, 20 Jan 2026 21:07:44 GMT
Server: Apache/2.4.58 (Win64) OpenSSL/3.1.3 PHP/8.2.12
Vary: Authorization
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

