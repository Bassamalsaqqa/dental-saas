<?php include APPPATH.'Views/layouts/partials/studio_icons.php'; ?>
<dialog id="management-confirm" class="help-dialog" aria-labelledby="management-title" data-csrf-header="<?= esc(csrf_header(),'attr') ?>" data-csrf-token="<?= esc(csrf_hash(),'attr') ?>" data-error="<?= esc(lang('Studio.requestFailed'),'attr') ?>">
<header>
<div>
<h2 id="management-title">
<?= esc(lang('Studio.confirm')) ?>
</h2>
<p data-management-prompt>
</p>
</div>
<button type="button" class="icon-button" data-close aria-label="<?= esc(lang('Studio.close')) ?>">
<?= $studioIcon('close') ?>
</button>
</header>
<div class="management-typed form-field" hidden>
<label for="management-typed">DELETE</label>
<input type="text" id="management-typed" autocomplete="off" spellcheck="false" dir="ltr">
</div>
<p class="management-error" data-management-error role="alert" hidden>
</p>
<footer class="management-dialog-actions">
<button class="button secondary" type="button" data-close>
<?= esc(lang('UI.cancel')) ?>
</button>
<button class="button primary" type="button" data-management-confirm>
<?= esc(lang('Studio.confirm')) ?>
</button>
</footer>
</dialog>
