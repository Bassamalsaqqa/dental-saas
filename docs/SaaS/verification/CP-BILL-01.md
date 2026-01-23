# CP-BILL-01 Verification

## Scope
- Add manual subscription date override controls for clinics.

## Evidence

### Route for subscription date updates
```bat
php spark routes | findstr /I "update-dates"
```
```
| POST   | controlplane/clinics/([0-9]+)/subscription/update-dates | ?    | \App\Controllers\ControlPlane\Clinics::updateSubscriptionDates/$1 | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
```

### UI wiring (clinic detail)
```bat
rg -n "update-dates|datetime-local" app/Views/control_plane/clinic_detail.php
```
```
105:            <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/subscription/update-dates') ?>" method="post" class="rounded-2xl border border-slate-100 bg-slate-50/40 p-4 space-y-3">
110:                    <input type="datetime-local" name="start_at" value="<?= esc($startValue) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
114:                    <input type="datetime-local" name="end_at" value="<?= esc($endValue) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
```
