<?php
$drawerId = (string) ($id ?? '');
$drawerTitle = (string) ($title ?? '');
$drawerDescription = (string) ($description ?? '');
$drawerIcon = (string) ($icon ?? 'fas fa-pen');
$drawerSize = in_array(($size ?? 'form'), ['compact', 'form', 'wide', 'full'], true) ? (string) $size : 'form';
$drawerTone = (string) ($tone ?? 'bg-sky-600');
$drawerBody = (string) ($body ?? '');
$drawerFooter = (string) ($footer ?? '');
$closeLabel = (string) ($close_label ?? lang('UI.closeDrawer'));
?>
<div id="<?= esc($drawerId) ?>" class="app-drawer hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="<?= esc($drawerId) ?>Title">
    <div class="app-drawer__backdrop" data-app-drawer-close="<?= esc($drawerId) ?>"></div>
    <div class="app-drawer__shell">
        <section class="app-drawer__panel app-drawer__panel--<?= esc($drawerSize) ?>" tabindex="-1">
            <header class="app-drawer__header">
                <div>
                    <div>
                        <span >

                        </span>
                        <div>
                            <h2 id="<?= esc($drawerId) ?>Title" ><?= esc($drawerTitle) ?></h2>
                            <?php if ($drawerDescription !== ''): ?>
                                <p ><?= esc($drawerDescription) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button type="button"  data-app-drawer-close="<?= esc($drawerId) ?>" aria-label="<?= esc($closeLabel) ?>">
                        <?= esc(lang('Studio.close')) ?>
                    </button>
                </div>
            </header>
            <div class="app-drawer__body">
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
