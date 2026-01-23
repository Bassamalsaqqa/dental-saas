# CP-UX-22 Verification

## Scope
Plans management cards with filters and quick plan stats.

## Evidence

### Filters + stats UI wiring
Command:
`rg -n "Plan catalog|Apply Filters|duration" app\\Views\\control_plane\\plans.php app\\Controllers\\ControlPlane\\Plans.php`

Output:
```
app\\Views\\control_plane\\plans.php:9:                <h1 class="text-3xl font-semibold text-slate-900 mt-2">Plan catalog</h1>
app\\Views\\control_plane\\plans.php:11:                    Define pricing, duration, and entitlement caps for clinics.
app\\Views\\control_plane\\plans.php:55:            <?php $currentDuration = $filters['duration'] ?? ''; ?>
app\\Views\\control_plane\\plans.php:56:            <select name="duration" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
app\\Views\\control_plane\\plans.php:57:                <option value="" <?= $currentDuration === '' ? 'selected' : '' ?>>All durations</option>
app\\Views\\control_plane\\plans.php:65:                Apply Filters
app\\Views\\control_plane\\plans.php:81:                $duration = ucfirst($limits['duration'] ?? 'monthly');
app\\Views\\control_plane\\plans.php:108:                        <p class="text-xs text-slate-500"><?= esc($duration) ?></p>
app\\Views\\control_plane\\plans.php:194:                <select name="requested_duration" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
app\\Views\\control_plane\\plans.php:233:                                    <td class="px-6 py-4 text-slate-600"><?= esc(ucfirst($request['requested_duration'] ?? 'monthly')) ?></td>
app\\Controllers\\ControlPlane\\Plans.php:31:        $durationFilter = strtolower(trim((string) ($request ? $request->getGet('duration') : '')));
app\\Controllers\\ControlPlane\\Plans.php:55:            $duration = strtolower((string) ($limits['duration'] ?? ''));
app\\Controllers\\ControlPlane\\Plans.php:57:            if ($durationFilter !== '' && $duration !== $durationFilter) {
app\\Controllers\\ControlPlane\\Plans.php:91:                'duration' => $durationFilter,
app\\Controllers\\ControlPlane\\Plans.php:208:            'duration' => 'monthly',
app\\Controllers\\ControlPlane\\Plans.php:264:            'duration' => $post['duration'] ?? 'monthly',
```
