# CP-UX-24b Verification

## Scope
Separate Plans and Plan Requests pages and navigation.

## Evidence

### Plan requests live on their own page
Command:
`rg -n "Plan Requests" app\\Views\\control_plane\\plans.php app\\Views\\control_plane\\plan_requests.php`

Output:
```
app\\Views\\control_plane\\plan_requests.php:8:                <p class="text-xs uppercase tracking-[0.3em] text-blue-700 font-semibold">Plan Requests</p>
```

### Nav points to /controlplane/plan-requests
Command:
`rg -n "Plan Requests" app\\Views\\layouts\\main_control_plane.php`

Output:
```
app\\Views\\layouts\\main_control_plane.php:88:                                    ['title' => 'Plan Requests', 'url' => 'controlplane/plan-requests', 'icon' => 'fas fa-inbox', 'color' => 'indigo'],
```
