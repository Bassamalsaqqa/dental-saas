# CP-CLINIC-01 Verification

## Scope
- Add clinic suspend/reactivate lifecycle controls.
- Wire control plane UI actions and routes.

## Evidence

### Routes for lifecycle actions
```bat
php spark routes | findstr /I "suspend reactivate"
```
```
| POST   | controlplane/clinics/([0-9]+)/suspend             | ?    | \App\Controllers\ControlPlane\Clinics::suspend/$1             | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| POST   | controlplane/clinics/([0-9]+)/reactivate          | ?    | \App\Controllers\ControlPlane\Clinics::reactivate/$1          | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
```

### UI wiring (clinics list + clinic detail)
```bat
rg -n "suspend|reactivate" app/Views/control_plane/clinics.php app/Views/control_plane/clinic_detail.php
```
```
app/Views/control_plane/clinic_detail.php:105:                    <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/suspend') ?>" method="post">
app/Views/control_plane/clinic_detail.php:110:                    <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/reactivate') ?>" method="post">
app/Views/control_plane/clinics.php:46:                : ($status === 'suspended' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600');
app/Views/control_plane/clinics.php:81:                        <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/suspend') ?>" method="post">
app/Views/control_plane/clinics.php:88:                        <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/reactivate') ?>" method="post">
```
