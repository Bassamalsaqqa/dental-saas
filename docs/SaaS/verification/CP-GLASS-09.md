# CP-GLASS-09 Verification

## Scope
Fix Control Plane clinics DataTables parser errors by using the configured CSRF header and explicit JSON response handling.

## Evidence

### DataTables uses CSRF header meta and JSON response
Command:
`rg -n "csrf-header|dataType: 'json'" app\\Views\\control_plane\\clinics.php`

Output:
```
286:const csrfMetaHeader = document.querySelector('meta[name="csrf-header"]');
407:            dataType: 'json',
```
