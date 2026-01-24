# CP-OBS-01A Verification

## Scope
Shorten the dashboard `mrr_series` labels to month-only so Chart.js ticks read `Jan`, `Feb`, etc.

## Evidence

### Chart payload label change
Command:
`rg -n "label' => date\\('M'" app/Controllers/ControlPlane/Dashboard.php`

Output:
```
119:            'label' => date('M', strtotime('-' . $i . ' months')),
```
