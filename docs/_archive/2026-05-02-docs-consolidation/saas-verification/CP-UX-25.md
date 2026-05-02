# CP-UX-25 Verification

## Scope
Email templates management UX with stats and filters.

## Evidence

### Filters + stats wiring
Command:
`rg -n "Email Templates|templateStats|templateFilters|Filter" app\\Views\\control_plane\\settings.php app\\Controllers\\ControlPlane\\Settings.php`

Output:
```
app\\Views\\control_plane\\settings.php:120:                <p class="text-xs uppercase tracking-[0.3em] text-blue-700 font-semibold">Email Templates</p>
app\\Views\\control_plane\\settings.php:137:                <p class="text-2xl font-semibold text-slate-900 mt-1"><?= number_format($templateStats['total'] ?? 0) ?></p>
app\\Views\\control_plane\\settings.php:141:                <p class="text-2xl font-semibold text-emerald-600 mt-1"><?= number_format($templateStats['active'] ?? 0) ?></p>
app\\Views\\control_plane\\settings.php:145:                <p class="text-2xl font-semibold text-amber-600 mt-1"><?= number_format($templateStats['draft'] ?? 0) ?></p>
app\\Views\\control_plane\\settings.php:149:                <p class="text-2xl font-semibold text-slate-900 mt-1"><?= number_format($templateStats['filtered'] ?? 0) ?></p>
app\\Views\\control_plane\\settings.php:156:                <input type="text" name="q" value="<?= esc($templateFilters['q'] ?? '') ?>" placeholder="Template name or slug"
app\\Views\\control_plane\\settings.php:161:                <?php $currentTemplateStatus = $templateFilters['status'] ?? ''; ?>
app\\Views\\control_plane\\settings.php:169:                <button class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm font-semibold">Filter</button>
app\\Controllers\\ControlPlane\\Settings.php:27:        $statusFilter = strtolower(trim((string) ($request ? $request->getGet('status') : '')));
app\\Controllers\\ControlPlane\\Settings.php:33:        if (in_array($statusFilter, ['active', 'draft'], true)) {
app\\Controllers\\ControlPlane\\Settings.php:34:            $templateQuery = $templateQuery->where('status', $statusFilter);
app\\Controllers\\ControlPlane\\Settings.php:56:            'templateStats' => [
app\\Controllers\\ControlPlane\\Settings.php:62:            'templateFilters' => [
app\\Controllers\\ControlPlane\\Settings.php:63:                'status' => $statusFilter,
```
