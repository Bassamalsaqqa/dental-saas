## WEB-CSRF-01 Verification

Goal: Centralize CSRF injection + refresh for all non-GET jQuery AJAX requests (including DataTables) under HTTPS without disabling CSRF.

### Pre-fix failure (token present but 403)
```bat
curl "https://saas.dental.local/patient/get-data" ^
  -H "Accept: application/json, text/javascript, */*; q=0.01" ^
  -H "Content-Type: application/x-www-form-urlencoded; charset=UTF-8" ^
  -b "ci_session_saas=<REDACTED>; csrf_cookie_name=<REDACTED>" ^
  -H "Origin: https://saas.dental.local" ^
  -H "Referer: https://saas.dental.local/patients" ^
  -H "X-CSRF-TOKEN: <REDACTED>" ^
  -H "X-Requested-With: XMLHttpRequest" ^
  --data-raw "draw=2&...&csrf_test_name=<REDACTED>" ^
  --insecure
```
```
{
    "title": "CodeIgniter\\Security\\Exceptions\\SecurityException",
    "type": "CodeIgniter\\Security\\Exceptions\\SecurityException",
    "code": 403,
    "message": "The action you requested is not allowed.",
    "file": "C:\\xampp\\htdocs\\dental-saas\\system\\Security\\Security.php",
    "line": 262,
    "trace": [
        {
            "file": "C:\\xampp\\htdocs\\dental-saas\\system\\Security\\Security.php",
            "line": 262,
            "function": "forDisallowedAction",
            "class": "CodeIgniter\\Security\\Exceptions\\SecurityException",
            "type": "::",
            "args": []
        },
        {
            "file": "C:\\xampp\\htdocs\\dental-saas\\system\\Filters\\CSRF.php",
            "line": 52,
            "function": "verify",
            "class": "CodeIgniter\\Security\\Security",
            "type": "->",
            "args": [
                {}
            ]
        },
        {
            "file": "C:\\xampp\\htdocs\\dental-saas\\system\\Filters\\Filters.php",
            "line": 241,
            "function": "before",
            "class": "CodeIgniter\\Filters\\CSRF",
            "type": "->",
            "args": [
                {},
                null
            ]
        }
    ]
}
```

### SSL sanity (verbatim)
```bat
C:\xampp\apache\bin\httpd.exe -S | findstr /I ":443 saas.dental.local"
```
```
*:443                  is a NameVirtualHost
         port 443 namevhost saas.dental.local (C:/xampp/apache/conf/extra/httpd-ssl.conf:291)
```

```bat
curl.exe -k -i https://saas.dental.local/auth/login | findstr /I "HTTP/"
```
```
HTTP/1.1 200 OK
```

### Positive test: Patients DataTables (DevTools)
1) Open Patients page.
2) In DevTools Network, capture:
   - `POST /patient/get-data` returns 200.
   - Request Headers include CSRF header.
   - Request payload includes CSRF field when applicable.
   - Response includes refreshed CSRF token (JSON field or header).

Paste evidence:
```
<paste network details or headers/payload excerpt>
```

### Positive test: Patients DataTables (copy-as-cURL)
```bat
curl "https://saas.dental.local/patient/get-data" ^
  -H "Accept: application/json, text/javascript, */*; q=0.01" ^
  -H "Content-Type: application/x-www-form-urlencoded; charset=UTF-8" ^
  -b "csrf_cookie_name=<REDACTED>; ci_session_saas=<REDACTED>" ^
  -H "Origin: https://saas.dental.local" ^
  -H "Referer: https://saas.dental.local/patients" ^
  -H "X-CSRF-TOKEN: <REDACTED>" ^
  -H "X-Requested-With: XMLHttpRequest" ^
  --data-raw "draw=1&...&csrf_test_name=<REDACTED>" ^
  --insecure
```
```
{
    "draw": 1,
    "recordsTotal": 7,
    "recordsFiltered": 7,
    "data": [
        {
            "id": 26,
            "patient_id": "PAT20266558",
            "name": "clinicA quotaaab exceeded",
            "email": "ClinicApatientquota112@mail.com",
            "phone": "+123456789",
            "status": "active",
            "examinations": 0,
            "date_of_birth": "01/01/1988",
            "created_at": "17/01/2026 2:23 PM"
        }
    ],
    "csrf_token": "<REDACTED>"
}
```

### Additional JSON refresh evidence (non-DataTables)
```bat
curl "https://saas.dental.local/patient/get-statistics" ^
  -H "Accept: application/json, text/javascript, */*; q=0.01" ^
  -b "ci_session_saas=<REDACTED>; csrf_cookie_name=<REDACTED>" ^
  -H "Referer: https://saas.dental.local/patients" ^
  -H "X-Requested-With: XMLHttpRequest" ^
  --insecure
```
```
{
    "total_patients": 7,
    "active_patients": 6,
    "new_patients": 6,
    "monthly_patients": 6,
    "csrf_token": "37f2a700046ecbddde0c37772f929933f2057d667b9c7a0f7486d2c35d11d91a"
}
```

### Positive test: second DataTables endpoint
Pick another DataTables screen (Appointments/Finance/Inventory/etc):
1) Capture the POST request returning 200 with CSRF header and payload token.

Paste evidence:
```
curl "https://saas.dental.local/finance/getFinancesData" ^
  -H "Accept: application/json, text/javascript, */*; q=0.01" ^
  -H "Content-Type: application/x-www-form-urlencoded; charset=UTF-8" ^
  -b "csrf_cookie_name=<REDACTED>; ci_session_saas=<REDACTED>" ^
  -H "Origin: https://saas.dental.local" ^
  -H "Referer: https://saas.dental.local/finance" ^
  -H "X-CSRF-TOKEN: <REDACTED>" ^
  -H "X-Requested-With: XMLHttpRequest" ^
  --data-raw "draw=1&...&csrf_test_name=<REDACTED>" ^
  --insecure

{
    "draw": 1,
    "recordsTotal": 1,
    "recordsFiltered": 1,
    "data": [
        {
            "id": 3,
            "transaction_id": "TXN-000003",
            "patient_name": "",
            "phone": "",
            "transaction_type": "payment",
            "amount": 100,
            "amount_formatted": "100.00 ₪",
            "payment_status": "paid",
            "description": "",
            "created_at": "2026-01-14 17:36:12",
            "created_at_formatted": "14/01/2026 5:36 PM",
            "patient_id": 4
        }
    ],
    "csrf_token": "<REDACTED>"
}
```

### Negative test (hard evidence)
From DevTools Network:
1) Right-click a successful `/patient/get-data` request -> Copy -> Copy as cURL (cmd).
2) Run the cURL as-is (expect 200).
3) Remove the CSRF token only (leave cookies), run again (expect 403).

Paste evidence:
```bat
<paste curl with token>
```
```
<paste output showing 200>
```

```bat
<paste curl without token>
```
```
curl "https://saas.dental.local/finance/getFinancesData" ^
  -H "Accept: application/json, text/javascript, */*; q=0.01" ^
  -H "Content-Type: application/x-www-form-urlencoded; charset=UTF-8" ^
  -b "csrf_cookie_name=<REDACTED>; ci_session_saas=<REDACTED>" ^
  -H "Origin: https://saas.dental.local" ^
  -H "Referer: https://saas.dental.local/finance" ^
  -H "X-Requested-With: XMLHttpRequest" ^
  --data-raw "draw=1&columns%5B0%5D%5Bdata%5D=transaction_id&columns%5B0%5D%5Bname%5D=transaction_id&columns%5B0%5D%5Bsearchable%5D=true&columns%5B0%5D%5Borderable%5D=true&columns%5B0%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B0%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B1%5D%5Bdata%5D=patient_name&columns%5B1%5D%5Bname%5D=patient_name&columns%5B1%5D%5Bsearchable%5D=true&columns%5B1%5D%5Borderable%5D=true&columns%5B1%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B1%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B2%5D%5Bdata%5D=transaction_type&columns%5B2%5D%5Bname%5D=transaction_type&columns%5B2%5D%5Bsearchable%5D=true&columns%5B2%5D%5Borderable%5D=true&columns%5B2%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B2%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B3%5D%5Bdata%5D=amount&columns%5B3%5D%5Bname%5D=amount&columns%5B3%5D%5Bsearchable%5D=true&columns%5B3%5D%5Borderable%5D=true&columns%5B3%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B3%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B4%5D%5Bdata%5D=payment_status&columns%5B4%5D%5Bname%5D=payment_status&columns%5B4%5D%5Bsearchable%5D=true&columns%5B4%5D%5Borderable%5D=true&columns%5B4%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B4%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B5%5D%5Bdata%5D=created_at&columns%5B5%5D%5Bname%5D=created_at&columns%5B5%5D%5Bsearchable%5D=true&columns%5B5%5D%5Borderable%5D=true&columns%5B5%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B5%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B6%5D%5Bdata%5D=id&columns%5B6%5D%5Bname%5D=actions&columns%5B6%5D%5Bsearchable%5D=false&columns%5B6%5D%5Borderable%5D=false&columns%5B6%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B6%5D%5Bsearch%5D%5Bregex%5D=false&order%5B0%5D%5Bcolumn%5D=0&order%5B0%5D%5Bdir%5D=desc&start=0&length=10&search%5Bvalue%5D=&search%5Bregex%5D=false" ^
  --insecure
```
```
{
    "title": "CodeIgniter\\Security\\Exceptions\\SecurityException",
    "type": "CodeIgniter\\Security\\Exceptions\\SecurityException",
    "code": 403,
    "message": "The action you requested is not allowed.",
    "file": "C:\\xampp\\htdocs\\dental-saas\\system\\Security\\Security.php",
    "line": 262
}
```
