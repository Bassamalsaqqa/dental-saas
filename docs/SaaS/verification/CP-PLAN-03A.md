# CP-PLAN-03A Verification

## Scope
Ensure the plan request rejection modal shows actionable buttons and stays visible on small viewports.

## Evidence

### Modal sizing and confirm button
Command:
`rg -n "reject-modal|Confirm Reject|max-h" app\\Views\\control_plane\\plan_requests.php`

Output:
```
162:<div id="reject-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm">
163:    <div class="glass-card rounded-2xl p-6 w-full max-w-md max-h-[85vh] overflow-y-auto">
184:                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-sm font-semibold">Confirm Reject</button>
191:    const rejectModal = document.getElementById('reject-modal');
```
