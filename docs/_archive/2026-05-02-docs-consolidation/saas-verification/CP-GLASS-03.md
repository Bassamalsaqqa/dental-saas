# CP-GLASS-03 Verification

## Scope
Add a Command Palette (Cmd/Ctrl+K) to the Control Plane header with a global search endpoint.

## Evidence

### Command palette UI + search wiring
Command:
`rg -n "cp-command-palette|search/global|Super Search" app\\Views\\layouts\\main_control_plane.php app\\Config\\Routes.php app\\Controllers\\ControlPlane\\Search.php`

Output:
```
app\\Views\\layouts\\main_control_plane.php:175:                        Super Search
app\\Views\\layouts\\main_control_plane.php:217:    <div id="cp-command-palette" class="fixed inset-0 z-50 hidden items-start justify-center bg-slate-900/40 px-4 py-10">
app\\Views\\layouts\\main_control_plane.php:260:        const cpPalette = document.getElementById('cp-command-palette');
app\\Views\\layouts\\main_control_plane.php:423:            fetch(`<?= base_url('controlplane/search/global') ?>?q=${encodeURIComponent(query)}`)
app\\Config\\Routes.php:42:        $routes->get('search/global', 'ControlPlane\\Search::global'); // CP-GLASS-03
app\\Controllers\\ControlPlane\\Search.php:12:     * GET /controlplane/search/global
```

### Route appears in spark routing table
Command:
`php spark routes | findstr /I "controlplane/search/global"`

Output:
```
| GET    | controlplane/search/global                              | ?    | \App\Controllers\ControlPlane\Search::global                      | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
```
