# CP-PLAN-02 Verification

## Scope
Update control plane plan catalog cards with glass styling and toggle switch activation control.

## Evidence

### Toggle switch and auto-submit wiring
Command:
`rg -n "toggle-switch|data-plan-toggle" app\\Views\\control_plane\\plans.php`

Output:
```
5:    .toggle-switch {
12:    .toggle-switch input {
36:    .toggle-switch input:checked + .toggle-track {
39:    .toggle-switch input:checked + .toggle-track .toggle-thumb {
156:                            <label class="toggle-switch" title="Toggle plan status">
157:                                <input type="checkbox" data-plan-toggle <?= $isActive ? 'checked' : '' ?> aria-label="Toggle plan status">
218:    document.querySelectorAll('[data-plan-toggle]').forEach((toggle) => {
```
