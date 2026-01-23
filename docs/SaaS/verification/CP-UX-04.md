# CP-UX-04 Verification

## Scope
Operational workflow grouping and action emphasis in Operations.

## Evidence

### Workflow quick actions
Command:
`rg -n "Operator workflows|Process Plan Requests|Review Clinics" app\\Views\\control_plane\\operations.php`

Output:
```
55:                    <h3 class="text-sm font-semibold text-slate-800">Operator workflows</h3>
71:                        <i class="fas fa-hospital-user"></i> Review Clinics
77:                        <i class="fas fa-inbox"></i> Process Plan Requests
```
