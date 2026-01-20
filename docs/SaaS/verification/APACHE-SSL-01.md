## APACHE-SSL-01 Verification

Goal: Fix HTTPS routing for `saas.dental.local` so CI routes resolve under SSL (no 404 for `/auth/login`).

### Pre-fix diagnostics (verbatim)
Route exists in CI:
```bat
php spark routes | findstr /I "auth/login"
```
```
| GET    | auth/login                            | ?    | \App\Controllers\Auth::login                                  | csrf forcehttps                                                   | secureheaders csrfjson toolbar                                                   |
| POST   | auth/login                            | ?    | \App\Controllers\Auth::login                                  | csrf forcehttps                                                   | secureheaders csrfjson toolbar                                                   |
```

HTTP redirects to HTTPS:
```bat
curl.exe -i http://saas.dental.local/auth/login | findstr /I "HTTP/ Location:"
```
```
HTTP/1.1 307 Temporary Redirect
Location: https://saas.dental.local/auth/login
```

HTTPS request currently fails to establish (SSL handshake error in this environment):
```bat
curl.exe -k -i https://saas.dental.local/auth/login | findstr /I "HTTP/ Location:"
```
```
curl: (35) schannel: AcquireCredentialsHandle failed: SEC_E_NO_CREDENTIALS (0x8009030E) - No credentials are available in the security package
```

```bat
curl.exe -k -i https://saas.dental.local/index.php/auth/login | findstr /I "HTTP/ Location:"
```
```
curl: (35) schannel: AcquireCredentialsHandle failed: SEC_E_NO_CREDENTIALS (0x8009030E) - No credentials are available in the security package
```

### Owner apply steps (machine config, not in repo)
1) Replace the SSL vhost in:
   `C:\xampp\apache\conf\extra\httpd-ssl.conf`

   Replace the default `<VirtualHost _default_:443>...</VirtualHost>` block
   with the snippet in:
   `docs/SaaS/apache/vhost-saas.dental.local-ssl.conf`

2) Validate config and restart Apache:
```bat
httpd -t
```
Expected: `Syntax OK`

3) Restart Apache via XAMPP control panel or:
```bat
httpd -k restart
```

### Post-fix verification (run after restart, paste verbatim)
```bat
curl.exe -k -i https://saas.dental.local/auth/login | findstr /I "HTTP/ Location:"
```
```
<paste output>
```

```bat
curl.exe -k -i https://saas.dental.local/index.php/auth/login | findstr /I "HTTP/ Location:"
```
```
<paste output>
```

```bat
curl.exe -i http://saas.dental.local/auth/login | findstr /I "HTTP/ Location:"
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
