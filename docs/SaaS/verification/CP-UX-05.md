# CP-UX-05 Verification

## Scope
Polish and UX parity pass (copy and badge consistency).

## Evidence

### Copy/badge consistency checks
Command:
`rg -n "Read-only|Control Plane v2" app\\Views\\control_plane\\dashboard.php app\\Views\\layouts\\main_control_plane.php`

Output:
```
app\\Views\\control_plane\\dashboard.php:16:                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">Read-only</span>
app\\Views\\layouts\\main_control_plane.php:148:                    Control Plane v2
```
