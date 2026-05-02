# CP-PLAN-02B Verification

## Scope
Polish the Control Plane plans view toggle so it behaves like a modern switch with cursor, ARIA state, and instant submit.

## Evidence

### Toggle markup & aria
Command:
`rg -n "aria-checked" app/Views/control_plane/plans.php`

Output:
```
41:                                <input type="checkbox" data-plan-toggle <?= $isActive ? 'checked aria-checked="true"' : 'aria-checked="false"' ?> aria-label="Toggle plan status">
```

### Cursor-aware toggle control
Command:
`rg -n "toggle-switch relative inline-flex" app/Views/control_plane/plans.php`

Output:
```
37:        <label class="toggle-switch relative inline-flex items-center cursor-pointer" title="Toggle plan status">
```
