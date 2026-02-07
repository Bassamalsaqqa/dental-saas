# P5-KIOSK-02 Verification

## Summary
Added plan-gated kiosk feature toggle, ensured settings switch behavior is meaningful, and improved kiosk layout to render Arabic text correctly.

## Verification Steps

### 1) DOM sink guard (views)
```
rg -n "innerHTML|outerHTML|insertAdjacentHTML|\.html\(" app/Views
```
Result: no matches (rg exit code 1).

### 2) Kiosk routes still present
```
php spark routes | findstr /I "kiosk/"
```
Output:
```
| GET    | kiosk/([^/]+)/check-in                                  | ?    | \App\Controllers\Kiosk::checkIn/$1                                | csrf forcehttps                                                   | secureheaders csrfjson toolbar                                                   |
| GET    | kiosk/([^/]+)/check-in/reset                            | ?    | \App\Controllers\Kiosk::reset/$1                                  | csrf forcehttps                                                   | secureheaders csrfjson toolbar                                                   |
| POST   | kiosk/([^/]+)/unlock                                    | ?    | \App\Controllers\Kiosk::unlock/$1                                 | csrf forcehttps                                                   | secureheaders csrfjson toolbar                                                   |
| POST   | kiosk/([^/]+)/lookup                                    | ?    | \App\Controllers\Kiosk::lookup/$1                                 | csrf forcehttps                                                   | secureheaders csrfjson toolbar                                                   |
| POST   | kiosk/([^/]+)/confirm                                   | ?    | \App\Controllers\Kiosk::confirm/$1                                | csrf forcehttps                                                   | secureheaders csrfjson toolbar                                                   |
```

### 3) PHP syntax checks
```
php -l app/Controllers/Kiosk.php
```
Output:
```
No syntax errors detected in app\Controllers\Kiosk.php
```

```
php -l app/Controllers/Settings.php
```
Output:
```
No syntax errors detected in app\Controllers\Settings.php
```

```
php -l app/Controllers/ControlPlane/Plans.php
```
Output:
```
No syntax errors detected in app\Controllers\ControlPlane\Plans.php
```

```
php -l app/Views/control_plane/plan_form.php
```
Output:
```
No syntax errors detected in app\Views\control_plane\plan_form.php
```

```
php -l app/Views/kiosk/check_in.php
```
Output:
```
No syntax errors detected in app\Views\kiosk\check_in.php
```

```
php -l app/Views/layouts/kiosk.php
```
Output:
```
No syntax errors detected in app\Views\layouts\kiosk.php
```

### 4) Guardrail script (if available)
```
bash scripts/ci/saas_guardrails.sh
```
Output:
```
Access is denied.
Error code: Bash/Service/CreateInstance/E_ACCESSDENIED
```

## Notes
- Guardrail bash script could not execute due to host Bash access denial.
