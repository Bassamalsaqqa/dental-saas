# CP-GLASS-07 Verification

## Scope
Wire clinic lifecycle actions (suspend/reactivate/terminate modal) and add impersonation support in the Control Plane.

## Evidence

### Impersonate route + controller + tenant filter bypass
Command:
`rg -n "impersonate" app\\Controllers\\ControlPlane.php app\\Filters\\TenantFilter.php app\\Config\\Routes.php`

Output:
```
app\\Controllers\\ControlPlane.php:88:     * POST /controlplane/impersonate
app\\Controllers\\ControlPlane.php:90:    public function impersonate()
app\\Controllers\\ControlPlane.php:114:        session()->set('impersonated_clinic_id', $clinicId);
app\\Filters\\TenantFilter.php:92:        $impersonatedClinicId = $session->get('impersonated_clinic_id');
app\\Filters\\TenantFilter.php:93:        if (!$membership && $isImpersonating && $impersonatedClinicId && (string) $impersonatedClinicId === (string) $activeClinicId) {
app\\Config\\Routes.php:43:        $routes->post('impersonate', 'ControlPlane::impersonate'); // CP-GLASS-07
```

### Terminate confirmation modal + Login As buttons
Command:
`rg -n "terminateModal|terminateConfirmInput|Login As|impersonate" app\\Views\\control_plane\\clinics.php app\\Views\\control_plane\\clinic_detail.php`

Output:
```
app\\Views\\control_plane\\clinics.php:249:<div id="terminateModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 px-4">
app\\Views\\control_plane\\clinics.php:266:            <input type="text" id="terminateConfirmInput" class="w-full rounded-xl border border-rose-200 px-3 py-2 text-sm"
app\\Views\\control_plane\\clinics.php:511:                        <form action="${baseUrl}controlplane/impersonate" method="post">
app\\Views\\control_plane\\clinic_detail.php:23:                <form action="<?= base_url('controlplane/impersonate') ?>" method="post">
app\\Views\\control_plane\\clinic_detail.php:164:<div id="terminateModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 px-4">
app\\Views\\control_plane\\clinic_detail.php:181:            <input type="text" id="terminateConfirmInput" class="w-full rounded-xl border border-rose-200 px-3 py-2 text-sm"
```

### Route appears in spark routing table
Command:
`php spark routes | findstr /I "controlplane/impersonate"`

Output:
```
| POST   | controlplane/impersonate                                | ?    | \App\Controllers\ControlPlane::impersonate                        | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
```
