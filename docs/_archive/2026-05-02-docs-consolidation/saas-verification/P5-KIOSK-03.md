# P5-KIOSK-03 Kiosk DOM Sink Removal

## Scope
- Remove innerHTML/DOM sink usage in kiosk check-in UI
- Preserve kiosk appointment/contract rendering behavior

## Evidence

### 1) Guardrail Query
```
rg -n "innerHTML|outerHTML|insertAdjacentHTML|\.html\(" app/Views/kiosk/check_in.php
```

### 2) Syntax Check
```
php -l app/Views/kiosk/check_in.php
```

