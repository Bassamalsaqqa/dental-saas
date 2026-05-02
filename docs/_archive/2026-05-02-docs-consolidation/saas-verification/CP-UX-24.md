# CP-UX-24 Verification

## Scope
Plan requests queue with stats, filters, and quick actions.

## Evidence

### Filters + stats wiring
Command:
`rg -n "Plan Requests|requestStats|requestFilters|Filter" app\\Views\\control_plane\\plans.php app\\Controllers\\ControlPlane\\PlanRequests.php`

Output:
```
app\\Controllers\\ControlPlane\\PlanRequests.php:31:        $statusFilter = strtolower(trim((string) ($request ? $request->getGet('status') : '')));
app\\Controllers\\ControlPlane\\PlanRequests.php:45:        if (in_array($statusFilter, ['pending', 'approved', 'rejected'], true)) {
app\\Controllers\\ControlPlane\\PlanRequests.php:46:            $requestQuery = $requestQuery->where('plan_requests.status', $statusFilter);
app\\Controllers\\ControlPlane\\PlanRequests.php:81:            'requestStats' => [
app\\Controllers\\ControlPlane\\PlanRequests.php:88:            'requestFilters' => [
app\\Controllers\\ControlPlane\\PlanRequests.php:89:                'status' => $statusFilter,
app\\Views\\control_plane\\plans.php:65:                Apply Filters
app\\Views\\control_plane\\plans.php:166:                <p class="text-xs uppercase tracking-[0.3em] text-blue-700 font-semibold">Plan Requests</p>
app\\Views\\control_plane\\plans.php:175:                <p class="text-2xl font-semibold text-slate-900 mt-1"><?= number_format($requestStats['total'] ?? 0) ?></p>
app\\Views\\control_plane\\plans.php:179:                <p class="text-2xl font-semibold text-amber-600 mt-1"><?= number_format($requestStats['pending'] ?? 0) ?></p>
app\\Views\\control_plane\\plans.php:183:                <p class="text-2xl font-semibold text-emerald-600 mt-1"><?= number_format($requestStats['approved'] ?? 0) ?></p>
app\\Views\\control_plane\\plans.php:187:                <p class="text-2xl font-semibold text-rose-600 mt-1"><?= number_format($requestStats['rejected'] ?? 0) ?></p>
app\\Views\\control_plane\\plans.php:194:                <input type="text" name="q" value="<?= esc($requestFilters['q'] ?? '') ?>" placeholder="Clinic or plan name"
app\\Views\\control_plane\\plans.php:199:                <?php $currentReqStatus = $requestFilters['status'] ?? ''; ?>
app\\Views\\control_plane\\plans.php:208:                <button class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm font-semibold">Filter</button>
```
