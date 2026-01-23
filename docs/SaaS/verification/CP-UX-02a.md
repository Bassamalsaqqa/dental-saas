# CP-UX-02a Verification

## Scope
Fix onboarding route usage and keep onboarding separate from clinics.

## Evidence

### Route availability
Command:
`php spark routes | findstr /I "controlplane/onboarding/clinic/create"`

Output:
```
| GET    | controlplane/onboarding/clinic/create                   | ?    | \App\Controllers\ControlPlane\Onboarding::createClinic            | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| POST   | controlplane/onboarding/clinic/create                   | ?    | \App\Controllers\ControlPlane\Onboarding::processCreateClinic     | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
```

### No legacy onboarding path usage
Command:
`rg -n "controlplane/clinic/create" app\\Views app\\Views\\layouts\\main_control_plane.php`

Output:
```
```
