<?php
$drawerId = (string) ($id ?? '');
$drawerTitle = (string) ($title ?? '');
$drawerDescription = (string) ($description ?? '');
$drawerIcon = (string) ($icon ?? 'fas fa-pen');
$drawerSize = in_array(($size ?? 'form'), ['compact', 'form', 'wide', 'full'], true) ? (string) $size : 'form';
$drawerTone = (string) ($tone ?? 'v3-bg-primary');
$drawerBody = (string) ($body ?? '');
$drawerFooter = (string) ($footer ?? '');
$closeLabel = (string) ($close_label ?? lang('UI.closeDrawer'));
?>
<div id="<?= esc($drawerId) ?>" class="app-drawer hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="<?= esc($drawerId) ?>Title">
    <div class="app-drawer__backdrop" data-app-drawer-close="<?= esc($drawerId) ?>"></div>
    <div class="app-drawer__shell">
        <section class="app-drawer__panel app-drawer__panel--<?= esc($drawerSize) ?>" tabindex="-1">
            <header class="app-drawer__header">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center v3-radius v3-text-on-primary v3-elevation-none <?= esc($drawerTone) ?>">
                            <i class="<?= esc($drawerIcon) ?>"></i>
                        </span>
                        <div class="min-w-0">
                            <h2 id="<?= esc($drawerId) ?>Title" class="text-lg font-bold leading-6 v3-text-ink"><?= esc($drawerTitle) ?></h2>
                            <?php if ($drawerDescription !== ''): ?>
                                <p class="mt-1 text-sm leading-5 v3-text-muted"><?= esc($drawerDescription) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full v3-text-muted transition v3-hover-soft v3-hover-text-ink v3-btn v3-btn--icon" data-app-drawer-close="<?= esc($drawerId) ?>" aria-label="<?= esc($closeLabel) ?>">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </header>
            <div class="app-drawer__body custom-scrollbar">
                <?= $drawerBody ?>
            </div>
            <?php if ($drawerFooter !== ''): ?>
                <footer class="app-drawer__footer">
                    <?= $drawerFooter ?>
                </footer>
            <?php endif; ?>
        </section>
    </div>
</div>
