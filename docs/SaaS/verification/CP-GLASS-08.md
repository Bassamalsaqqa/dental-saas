# CP-GLASS-08 Verification

## Scope
Add an Exit Impersonation control for tenant views and route it back to the Control Plane.

## Evidence

### Exit impersonation route + controller + tenant layout control
Command:
`rg -n "impersonation/exit|is_impersonating" app\\Controllers\\ControlPlane.php app\\Config\\Routes.php app\\Views\\layouts\\main_auth.php`

Output:
```
app\\Controllers\\ControlPlane.php:49:        session()->remove('is_impersonating');
app\\Controllers\\ControlPlane.php:74:        session()->remove('is_impersonating');
app\\Controllers\\ControlPlane.php:115:        session()->set('is_impersonating', true);
app\\Controllers\\ControlPlane.php:131:     * POST /controlplane/impersonation/exit
app\\Controllers\\ControlPlane.php:152:        session()->remove('is_impersonating');
app\\Controllers\\ControlPlane.php:156:            'route' => '/controlplane/impersonation/exit'
app\\Config\\Routes.php:17:    $routes->post('impersonation/exit', 'ControlPlane::exitImpersonation'); // CP-GLASS-08
app\\Views\\layouts\\main_auth.php:314:                    <?php if (session()->get('is_impersonating')): ?>
app\\Views\\layouts\\main_auth.php:315:                    <form action="<?= base_url('controlplane/impersonation/exit') ?>" method="post" class="flex items-center space-x-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-1.5">
```

### Route appears in spark routing table
Command:
`php spark routes | findstr /I "controlplane/impersonation/exit"`

Output:
```
| POST   | controlplane/impersonation/exit                         | ?    | \App\Controllers\ControlPlane::exitImpersonation                  | csrf forcehttps                                                   | secureheaders csrfjson toolbar                                                   |
```
