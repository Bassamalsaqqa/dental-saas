# CP-GLASS-02 Verification

## Scope
Enable DataTables-backed clinic listing in the Control Plane.

## Evidence

### DataTables table and AJAX endpoint wiring
Command:
`rg -n "clinics/get-data|clinicsTable" app\\Config\\Routes.php app\\Views\\control_plane\\clinics.php app\\Controllers\\ControlPlane\\Clinics.php`

Output:
```
app\\Config\\Routes.php:24:        $routes->post('clinics/get-data', 'ControlPlane\\Clinics::getData'); // CP-GLASS-02
app\\Views\\control_plane\\clinics.php:221:            <table id="clinicsTable" class="min-w-full divide-y divide-slate-200">
app\\Views\\control_plane\\clinics.php:239:let clinicsTable;
app\\Views\\control_plane\\clinics.php:298:    clinicsTable = $('#clinicsTable').DataTable({
app\\Views\\control_plane\\clinics.php:304:            url: '<?= base_url('controlplane/clinics/get-data') ?>',
app\\Views\\control_plane\\clinics.php:479:        clinicsTable.draw();
app\\Views\\control_plane\\clinics.php:486:        clinicsTable.draw();
app\\Controllers\\ControlPlane\\Clinics.php:57:     * POST /controlplane/clinics/get-data
app\\Controllers\\ControlPlane\\Clinics.php:62:            'route' => '/controlplane/clinics/get-data',
```

### Route appears in spark routing table
Command:
`php spark routes | findstr /I "controlplane/clinics/get-data"`

Output:
```
| POST   | controlplane/clinics/get-data                           | ?    | \App\Controllers\ControlPlane\Clinics::getData                    | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
```
