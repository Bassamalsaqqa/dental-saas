<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <div class="glass-card rounded-3xl p-6 md:p-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-indigo-700 font-semibold">User Profile</p>
                <h1 class="text-3xl font-semibold text-slate-900 mt-2"><?= esc(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?: esc($user['email']) ?></h1>
                <p class="mt-3 text-sm text-slate-600"><?= esc($user['email']) ?> · ID: <?= esc($user['id']) ?></p>
            </div>
            <?php
                $statusClass = ($user['active'] == 1) ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700';
                $statusLabel = ($user['active'] == 1) ? 'Active' : 'Inactive';
            ?>
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-xs font-semibold px-3 py-1 rounded-full <?= $statusClass ?>">
                    Status: <?= $statusLabel ?>
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[1.2fr,0.8fr] gap-6">
        <!-- Clinic Memberships -->
        <div class="glass-card rounded-3xl p-6">
            <h2 class="text-sm font-semibold text-slate-800 mb-4">Clinic Memberships</h2>
            <?php if (empty($memberships)): ?>
                <p class="text-sm text-slate-500">No clinic memberships found.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($memberships as $membership): ?>
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <div>
                                <p class="text-sm font-bold text-slate-900"><?= esc($membership['clinic_name']) ?></p>
                                <p class="text-xs text-slate-500">Since <?= date('M j, Y', strtotime($membership['created_at'])) ?></p>
                            </div>
                            <span class="text-xs font-semibold px-2 py-1 rounded-lg bg-indigo-100 text-indigo-700">
                                <!-- Ideally verify role name from role_id if possible, simplified here -->
                                Role ID: <?= esc($membership['role_id']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Global Roles -->
        <div class="glass-card rounded-3xl p-6">
            <h2 class="text-sm font-semibold text-slate-800 mb-4">Global Roles</h2>
            <?php if (empty($globalRoles)): ?>
                <p class="text-sm text-slate-500">No global roles assigned.</p>
            <?php else: ?>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($globalRoles as $role): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700 border border-purple-200">
                            <?= esc($role['name']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="glass-card rounded-3xl p-6 space-y-6">
        <h2 class="text-sm font-semibold text-slate-800">Actions</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-600">
            <!-- Reset Password -->
            <div class="rounded-2xl border border-indigo-100 bg-indigo-50/40 p-4 space-y-3">
                <h3 class="text-xs uppercase tracking-widest text-indigo-700 font-semibold">Security</h3>
                <p class="text-xs text-slate-500">Reset user password manually.</p>
                <form action="<?= base_url('controlplane/users/' . $user['id'] . '/reset-password') ?>" method="post" class="space-y-2">
                    <?= csrf_field() ?>
                    <input type="password" name="password" placeholder="New Password (min 8 chars)" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs" required minlength="8">
                    <button class="w-full px-3 py-2 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition">Reset Password</button>
                </form>
            </div>

            <!-- Toggle Status -->
            <div class="rounded-2xl border border-rose-100 bg-rose-50/40 p-4 space-y-3">
                <h3 class="text-xs uppercase tracking-widest text-rose-700 font-semibold">Account Status</h3>
                <p class="text-xs text-slate-500">Enable or disable user access globally.</p>
                <form action="<?= base_url('controlplane/users/' . $user['id'] . '/toggle-status') ?>" method="post">
                    <?= csrf_field() ?>
                    <?php if ($user['active'] == 1): ?>
                        <button class="w-full px-3 py-2 rounded-lg bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 transition">Deactivate User</button>
                    <?php else: ?>
                        <button class="w-full px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition">Activate User</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <p class="text-xs text-slate-500">Actions are audited and immediate.</p>
    </div>
</div>
<?= $this->endSection() ?>
