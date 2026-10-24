<?php $hide_cp_nav = true; ?>
<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="v3-platform-workspace flex min-h-[70vh] items-center justify-center">
    <div class="w-full max-w-md glass-card cp-radius cp-panel-padding text-center">
        <div class="mb-6">
            <div class="w-16 h-16 cp-soft-surface rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-lock cp-accent text-2xl"></i>
            </div>
            <p class="text-xs uppercase tracking-widest text-gray-400 font-semibold mb-2"><?= esc(lang('V3Platform.controlPlane')) ?></p>
            <h1 class="text-xl font-bold text-gray-900 mb-2"><?= esc(lang('V3Platform.restrictedAccess')) ?></h1>
            <p class="text-sm text-gray-500"><?= esc(lang('V3Platform.enterGlobalModeToUnlockControlPlaneTools')) ?></p>
            <p class="text-xs text-gray-400 mt-3"><?= esc(lang('V3Platform.allActionsAreLoggedAndRestrictedToSuperAdmins')) ?></p>
        </div>

        <form action="<?= base_url('controlplane/enter') ?>" method="post">
            <?= csrf_field() ?>
            <button type="submit" class="w-full flex justify-center items-center px-4 py-3 text-sm font-semibold rounded-lg text-white bg-slate-900 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all">
                <?= esc(lang('V3Platform.enterGlobalMode')) ?>
            </button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
