# CP-UX-24c Verification

## Scope
Restore the GET route for /controlplane/plan-requests to resolve 404s.

## Evidence

### Route registration in Routes.php
Command:
`rg -n "plan-requests" app\\Config\\Routes.php`

Output:
```
37:        $routes->get('plan-requests', 'ControlPlane\\PlanRequests::index'); // CP-UX-24c
38:        $routes->post('plan-requests', 'ControlPlane\\PlanRequests::store'); // CP-UX-24
39:        $routes->post('plan-requests/(:num)/approve', 'ControlPlane\\PlanRequests::approve/$1');
40:        $routes->post('plan-requests/(:num)/reject', 'ControlPlane\\PlanRequests::reject/$1');
```

### Route appears in spark routing table
Command:
`php spark routes | findstr /I "controlplane/plan-requests"`

Output:
```
| GET    | controlplane/plan-requests                              | ?    | \App\Controllers\ControlPlane\PlanRequests::index                 | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| POST   | controlplane/plan-requests                              | ?    | \App\Controllers\ControlPlane\PlanRequests::store                 | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| POST   | controlplane/plan-requests/([0-9]+)/approve             | ?    | \App\Controllers\ControlPlane\PlanRequests::approve/$1            | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| POST   | controlplane/plan-requests/([0-9]+)/reject              | ?    | \App\Controllers\ControlPlane\PlanRequests::reject/$1             | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
```
