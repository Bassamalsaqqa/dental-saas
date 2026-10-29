<?php // Existing authorized actions are owned by the caller; this is only their compact disclosure trigger.
include APPPATH.'Views/layouts/partials/studio_icons.php';$rowMenuLabel=$rowMenuLabel??lang('Studio.actions'); ?>
<button type="button" class="icon-button studio-row-menu-trigger" popovertarget="<?= esc($rowMenuId,'attr') ?>" aria-controls="<?= esc($rowMenuId,'attr') ?>" aria-label="<?= esc($rowMenuLabel,'attr') ?>" title="<?= esc($rowMenuLabel,'attr') ?>"><?= $studioIcon('more') ?></button>
