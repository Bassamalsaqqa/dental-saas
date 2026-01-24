# CP-SEARCH-01 Verification

## Scope
Add the Control Plane `/controlplane/search/global` endpoint that returns lightweight clinic/user/link payloads for the Cmd/Ctrl+K palette.

## Evidence

### Route registration
Command:
`rg -n "search/global" app/Config/Routes.php`

Output:
```
43:        $routes->get('search/global', 'ControlPlane\Search::global'); // CP-GLASS-03, CP-SEARCH-01
```

### Controller output structure
Command:
`rg -n "results' =>" app/Controllers/ControlPlane/Search.php`

Output:
```
24:            return $this->response->setJSON(['results' => $results]);
93:        return $this->response->setJSON(['results' => $results]);
```

### Search limits enforced
Command:
`rg -n "limit\\(5\\)" app/Controllers/ControlPlane/Search.php`

Output:
```
36:            ->limit(5)
55:            ->limit(5)
```
