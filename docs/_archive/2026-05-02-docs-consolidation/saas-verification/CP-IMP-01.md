# CP-IMP-01 Verification

## Scope
Implement control plane tenant impersonation wiring for the Clinics view.

## Evidence

### Route registration
Command:
`rg -n "ControlPlane\\Clinics::impersonate" app/Config/Routes.php`

Output:
```
44:        $routes->post('impersonate', 'ControlPlane\Clinics::impersonate'); // CP-IMP-01
```

### Controller method & audit logging
Command:
`rg -n "clinic_impersonate" app/Controllers/ControlPlane/Clinics.php`

Output:
```
205:        $this->auditService->logEvent('clinic_impersonate', [
```
