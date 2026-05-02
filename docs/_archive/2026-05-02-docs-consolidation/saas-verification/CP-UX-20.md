# CP-UX-20 Verification

## Scope
Clinics management cards with action panel filters (search/status/plan) and quick actions.

## Evidence

### Controller filters + view action panel
Command:
`rg -n "controlplane/clinics\b|status" app\\Controllers\\ControlPlane\\Clinics.php app\\Views\\control_plane\\clinics.php`

Output:
```
app\\Views\\control_plane\\clinics.php:11:                    Provision new clinics, inspect status, and prepare lifecycle actions without leaving the control plane.
app\\Views\\control_plane\\clinics.php:44:            <label class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Status</label>
app\\Views\\control_plane\\clinics.php:45:            <select name="status" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
app\\Views\\control_plane\\clinics.php:46:                <?php $currentStatus = $filters['status'] ?? ''; ?>
app\\Views\\control_plane\\clinics.php:47:                <option value="" <?= $currentStatus === '' ? 'selected' : '' ?>>All statuses</option>
app\\Views\\control_plane\\clinics.php:48:                <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>Active</option>
app\\Views\\control_plane\\clinics.php:49:                <option value="suspended" <?= $currentStatus === 'suspended' ? 'selected' : '' ?>>Suspended</option>
app\\Views\\control_plane\\clinics.php:50:                <option value="inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>Inactive</option>
app\\Views\\control_plane\\clinics.php:69:            <a href="<?= base_url('controlplane/clinics') ?>"
app\\Views\\control_plane\\clinics.php:81:            $status = $clinic['status'] ?? 'inactive';
app\\Views\\control_plane\\clinics.php:82:            $statusLabel = ucfirst($status);
app\\Views\\control_plane\\clinics.php:83:            $statusClasses = $status === 'active'
app\\Views\\control_plane\\clinics.php:85:                : ($status === 'suspended' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600');
app\\Views\\control_plane\\clinics.php:93:                    <span class="text-xs font-semibold px-2 py-1 rounded-full <?= $statusClasses ?>"><?= esc($statusLabel) ?></span>
app\\Views\\control_plane\\clinics.php:112:                    <a href="<?= base_url('controlplane/clinics/' . $clinic['id']) ?>"
app\\Views\\control_plane\\clinics.php:119:                    <?php if ($status === 'active'): ?>
app\\Views\\control_plane\\clinics.php:120:                        <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/suspend') ?>" method="post">
app\\Views\\control_plane\\clinics.php:127:                        <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/reactivate') ?>" method="post">
app\\Views\\control_plane\\clinics.php:134:                    <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/terminate') ?>" method="post">
app\\Controllers\\ControlPlane\\Clinics.php:21:     * GET /controlplane/clinics
app\\Controllers\\ControlPlane\\Clinics.php:26:            'route' => '/controlplane/clinics',
app\\Controllers\\ControlPlane\\Clinics.php:30:        $statusFilter = strtolower(trim((string) ($request ? $request->getGet('status') : '')));
app\\Controllers\\ControlPlane\\Clinics.php:39:        $activeClinics = (new ClinicModel())->where('status', 'active')->countAllResults();
app\\Controllers\\ControlPlane\\Clinics.php:42:        if (in_array($statusFilter, ['active', 'inactive', 'suspended'], true)) {
app\\Controllers\\ControlPlane\\Clinics.php:43:            $clinicQuery = $clinicQuery->where('status', $statusFilter);
app\\Controllers\\ControlPlane\\Clinics.php:64:            $status = $clinic['status'] ?? 'inactive';
app\\Controllers\\ControlPlane\\Clinics.php:96:                'status' => $statusFilter,
app\\Controllers\\ControlPlane\\Clinics.php:105:     * GET /controlplane/clinics/{id}
app\\Controllers\\ControlPlane\\Clinics.php:110:            'route' => '/controlplane/clinics/' . $id,
app\\Controllers\\ControlPlane\\Clinics.php:136:            'plans' => $planModel->where('status', 'active')->orderBy('name', 'ASC')->findAll(),
```
