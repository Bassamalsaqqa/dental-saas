# CP-GLASS-06 Verification

## Scope
Upgrade the Control Plane clinics list to DataTables with server-side processing via Clinics::index, MRR column, filters, and glass table styling.

## Evidence

### Clinics::index serves DataTables JSON + MRR field
Command:
`rg -n "mrr|Clinics::index" app\\Controllers\\ControlPlane\\Clinics.php`

Output:
```
app\\Controllers\\ControlPlane\\Clinics.php:62:                    if ($orderColumn === 'mrr') {
app\\Controllers\\ControlPlane\\Clinics.php:147:            $mrr = 0.0;
app\\Controllers\\ControlPlane\\Clinics.php:157:                    $mrr = $price / 12;
app\\Controllers\\ControlPlane\\Clinics.php:159:                    $mrr = $price;
app\\Controllers\\ControlPlane\\Clinics.php:173:                'mrr' => $mrr,
```

### DataTables wiring targets /controlplane/clinics and glass table styling
Command:
`rg -n "controlplane/clinics\)|clinicsTable|mrr" app\\Config\\Routes.php app\\Views\\control_plane\\clinics.php`

Output:
```
app\\Views\\control_plane\\clinics.php:138:#clinicsTable {
app\\Views\\control_plane\\clinics.php:142:#clinicsTable tbody tr {
app\\Views\\control_plane\\clinics.php:147:#clinicsTable tbody tr td {
app\\Views\\control_plane\\clinics.php:233:            <table id="clinicsTable" class="min-w-full divide-y divide-slate-200">
app\\Views\\control_plane\\clinics.php:250:let clinicsTable;
app\\Views\\control_plane\\clinics.php:317:    clinicsTable = $('#clinicsTable').DataTable({
app\\Views\\control_plane\\clinics.php:398:                data: 'mrr',
app\\Views\\control_plane\\clinics.php:399:                name: 'mrr',
```

### Routes include POST /controlplane/clinics
Command:
`php spark routes | findstr /I "controlplane/clinics"`

Output:
```
| GET    | controlplane/clinics                                    | ?    | \App\Controllers\ControlPlane\Clinics::index                      | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| GET    | controlplane/clinics/([0-9]+)                           | ?    | \App\Controllers\ControlPlane\Clinics::show/$1                    | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| POST   | controlplane/clinics                                    | ?    | \App\Controllers\ControlPlane\Clinics::index                      | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| POST   | controlplane/clinics/([0-9]+)/subscription/start        | ?    | \App\Controllers\ControlPlane\Clinics::startSubscription/$1       | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| POST   | controlplane/clinics/([0-9]+)/subscription/extend       | ?    | \App\Controllers\ControlPlane\Clinics::extendSubscription/$1      | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| POST   | controlplane/clinics/([0-9]+)/subscription/cancel       | ?    | \App\Controllers\ControlPlane\Clinics::cancelSubscription/$1      | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| POST   | controlplane/clinics/([0-9]+)/subscription/update-dates | ?    | \App\Controllers\ControlPlane\Clinics::updateSubscriptionDates/$1 | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| POST   | controlplane/clinics/([0-9]+)/terminate                 | ?    | \App\Controllers\ControlPlane\Clinics::terminate/$1               | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| POST   | controlplane/clinics/([0-9]+)/suspend                   | ?    | \App\Controllers\ControlPlane\Clinics::suspend/$1                 | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| POST   | controlplane/clinics/([0-9]+)/reactivate                | ?    | \App\Controllers\ControlPlane\Clinics::reactivate/$1              | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
```
