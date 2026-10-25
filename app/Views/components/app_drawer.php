<?php
$drawerId = (string) ($id ?? '');
$drawerTitle = (string) ($title ?? '');
$drawerDescription = (string) ($description ?? '');
$drawerIcon = (string) ($icon ?? 'fas fa-pen');
$drawerSize = in_array(($size ?? 'form'), ['compact', 'form', 'wide', 'full'], true) ? (string) $size : 'form';
$drawerBody = (string) ($body ?? '');
$drawerFooter = (string) ($footer ?? '');
$closeLabel = (string) ($close_label ?? lang('UI.closeDrawer'));
?>
<div id="<?= esc($drawerId) ?>" class="app-drawer hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="<?= esc($drawerId) ?>Title">
    <div class="app-drawer__backdrop" data-app-drawer-close="<?= esc($drawerId) ?>"></div>
    <div class="app-drawer__shell">
        <section class="app-drawer__panel app-drawer__panel--<?= esc($drawerSize) ?>" tabindex="-1">
            <header class="app-drawer__header v4-drawer-heading">
                <div><span class="v4-drawer-symbol"><i class="<?= esc($drawerIcon) ?>" aria-hidden="true"></i></span><div><h2 id="<?= esc($drawerId) ?>Title"><?= esc($drawerTitle) ?></h2><?php if($drawerDescription!==''): ?><p><?= esc($drawerDescription) ?></p><?php endif; ?></div></div>
                <button type="button" class="v4-icon-button" data-app-drawer-close="<?= esc($drawerId) ?>" aria-label="<?= esc($closeLabel) ?>"><i class="fas fa-times" aria-hidden="true"></i></button>
            </header>
            <div class="app-drawer__body custom-scrollbar v4-form">
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
