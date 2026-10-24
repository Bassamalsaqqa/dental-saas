<?php $hide_cp_nav = true; ?>
<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="v3-platform-workspace flex min-h-[70vh] items-center justify-center">
    <div class="w-full max-w-md glass-card cp-radius cp-panel-padding text-center">
        <div class="mb-6">
            <div class="w-16 h-16 cp-soft-surface rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-lock cp-accent text-2xl"></i>
            </div>
            <p class="text-xs uppercase tracking-widest v3-text-muted font-semibold mb-2"><?= esc(lang('V3Platform.controlPlane')) ?></p>
            <h1 class="text-xl font-bold v3-text-ink mb-2"><?= esc(lang('V3Platform.restrictedAccess')) ?></h1>
            <p class="text-sm v3-text-muted"><?= esc(lang('V3Platform.enterGlobalModeToUnlockControlPlaneTools')) ?></p>
            <p class="text-xs v3-text-muted mt-3"><?= esc(lang('V3Platform.allActionsAreLoggedAndRestrictedToSuperAdmins')) ?></p>
        </div>

        <form action="<?= base_url('controlplane/enter') ?>" method="post">
            <?= csrf_field() ?>
            <button type="submit" class="w-full flex justify-center items-center px-4 py-3 text-sm font-semibold rounded-lg v3-text-on-primary v3-bg-forest v3-bg-forest focus:outline-none focus:ring-2 focus:ring-offset-2 v3-focus-control transition-all v3-btn v3-btn--primary">
                <?= esc(lang('V3Platform.enterGlobalMode')) ?>
            </button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
