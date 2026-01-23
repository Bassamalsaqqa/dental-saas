# CP-UX-02b Verification

## Scope
Restore legacy /controlplane/clinic/create routes to avoid onboarding 404s.

## Evidence

### Legacy alias present in Routes.php
Command:
`rg -n "controlplane/clinic/create|clinic/create" app\\Config\\Routes.php`

Output:
```
47:        // Legacy onboarding alias to avoid 404 on /controlplane/clinic/create
48:        $routes->get('clinic/create', 'ControlPlane\\Onboarding::createClinic'); // CP-UX-02b
49:        $routes->post('clinic/create', 'ControlPlane\\Onboarding::processCreateClinic'); // CP-UX-02b
53:        $routes->get('clinic/create', 'ControlPlane\\Onboarding::createClinic');
54:        $routes->post('clinic/create', 'ControlPlane\\Onboarding::processCreateClinic');
```

### Spark routes include /controlplane/clinic/create
Command:
`php spark routes | findstr /I "controlplane/clinic/create"`

Output:
```
| GET    | controlplane/clinic/create                              | ?    | \App\Controllers\ControlPlane\Onboarding::createClinic            | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| POST   | controlplane/clinic/create                              | ?    | \App\Controllers\ControlPlane\Onboarding::processCreateClinic     | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
```
