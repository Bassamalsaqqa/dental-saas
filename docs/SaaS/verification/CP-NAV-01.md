# CP-NAV-01 Verification

## Scope
- Remove deprecated control plane routes/views (console, admin-tools, email-templates, danger page).
- Keep only the consolidated pages and required POST handlers.

## Evidence

### Routes cleanup
```bat
rg -n "console|admin-tools|email-templates|plan-requests|danger" app/Config/Routes.php
```
```
37:        $routes->post('plan-requests', 'ControlPlane\PlanRequests::store'); // CP-UX-24
38:        $routes->post('plan-requests/(:num)/approve', 'ControlPlane\PlanRequests::approve/$1');
39:        $routes->post('plan-requests/(:num)/reject', 'ControlPlane\PlanRequests::reject/$1');
```

### Control plane exit route
```bat
rg -n "ControlPlane::exit" app/Config/Routes.php
```
```
42:        $routes->post('exit', 'ControlPlane::exit');
```

### Exit wiring in views
```bat
rg -n "controlplane/exit" app/Views/control_plane/operations.php app/Views/layouts/main_control_plane.php
```
```
app/Views/layouts/main_control_plane.php:145:                    <form action="<?= base_url('controlplane/exit') ?>" method="post">
app/Views/control_plane/operations.php:242:        <form action="<?= base_url('controlplane/exit') ?>" method="post" class="space-y-6">
```
