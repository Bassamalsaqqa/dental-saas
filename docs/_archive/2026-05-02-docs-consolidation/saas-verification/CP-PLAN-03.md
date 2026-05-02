# CP-PLAN-03 Verification

## Scope
Create a plan request triage inbox with approve/reject interactions and rejection modal.

## Evidence

### Inbox actions and modal wiring
Command:
`rg -n "data-reject-action|data-approve-form|reject-modal" app\\Views\\control_plane\\plan_requests.php`

Output:
```
132:                                    <form action="<?= base_url('controlplane/plan-requests/' . $request['id'] . '/approve') ?>" method="post" data-approve-form>
141:                                            data-reject-action="<?= base_url('controlplane/plan-requests/' . $request['id'] . '/reject') ?>"
162:<div id="reject-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm">
191:    const rejectModal = document.getElementById('reject-modal');
197:    document.querySelectorAll('[data-approve-form]').forEach((form) => {
212:    document.querySelectorAll('[data-reject-action]').forEach((button) => {
215:            const action = button.getAttribute('data-reject-action');
```
