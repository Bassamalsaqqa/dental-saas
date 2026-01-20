## APACHE-SSL-02 Verification

Goal: Ensure HTTPS vhost for `saas.dental.local` serves `C:\xampp\htdocs\dental-saas\public` so CI routes resolve under SSL.

### Pre-fix evidence (verbatim)
`httpd.exe -S` shows only the default SSL vhost:
```
*:443 www.example.com (C:/xampp/apache/conf/extra/httpd-ssl.conf:121)
```

HTTPS currently serves XAMPP (CI route 404):
```bat
curl.exe -k -i https://saas.dental.local/auth/login | findstr /I "HTTP/ Location:"
```
```
HTTP/1.1 404 Not Found
```

XAMPP redirect observed at root:
```bat
curl.exe -k -i https://saas.dental.local/ | findstr /I "HTTP/ Location:"
```
```
HTTP/1.1 302 Found
Location: https://saas.dental.local/dashboard/
```

### Owner apply steps (machine config, not in repo)
1) Edit:
   `C:\xampp\apache\conf\extra\httpd-ssl.conf`

2) Add the vhost block from:
   `docs/SaaS/apache/vhost-saas.dental.local-ssl.conf`
   Place it above the default `<VirtualHost _default_:443>` block, or replace the default block.

3) Validate and restart (XAMPP is not a service):
```bat
C:\xampp\apache\bin\httpd.exe -t
```
Expected: `Syntax OK`

Restart Apache using XAMPP Control Panel (Stop/Start).
Note: `httpd.exe -k restart` fails because XAMPP is not installed as a service.

### Post-fix verification (paste verbatim)
```bat
C:\xampp\apache\bin\httpd.exe -S
```
```
<paste output showing *:443 saas.dental.local ...>
```

```bat
curl.exe -k -i https://saas.dental.local/auth/login | findstr /I "HTTP/ Location:"
```
```
<paste output>
```

Optional sanity:
```bat
curl.exe -k -i https://saas.dental.local/ | findstr /I "HTTP/ Location:"
```
```
<paste output>
```
