<?php $categories=\App\Config\Permissions::getCategories();$actionDescriptions=\App\Config\Permissions::getActionDescriptions();$actionLabels=\App\Config\Permissions::getActionLabels(); ?>
<form action="<?= base_url($rolesRoute.($roleEditing?'/'.(int)$role['id'].'/update':'/store')) ?>" method="post" id="roleForm" class="studio-form" data-role-form data-role-mode="<?= $roleEditing?'edit':'create' ?>" data-permission-error="<?= esc(lang('UserRole.selectAtLeastOnePermissionAlert'),'attr') ?>">
<?= csrf_field() ?>
<?php if(!empty($return_to)): ?>
<input type="hidden" name="return_to" value="<?= esc($return_to,'attr') ?>">
<?php endif; ?>
<?= view('roles/partials/studio_form_fields', $this->getData()) ?>
<footer class="form-actions">
<a class="button secondary" href="<?= esc(workflow_back($rolesRoute,$return_to)) ?>">
<?= esc(lang('UserRole.cancel')) ?>
</a>
<button type="submit" class="button primary">
<?= esc(($roleEditing?lang('Studio.saveRole'):lang('UserRole.createRole'))) ?>
</button>
</footer>
</form>
