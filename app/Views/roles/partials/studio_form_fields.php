<?php $categories=\App\Config\Permissions::getCategories();$actionDescriptions=\App\Config\Permissions::getActionDescriptions();$actionLabels=\App\Config\Permissions::getActionLabels(); ?>
<section class="form-section">
<header>
<h2>
<?= esc(lang('UserRole.roleInformation')) ?>
</h2>
</header>
<div class="role-identity">
<div class="form-field">
<label for="name">
<?= esc(lang('UserRole.roleName')) ?>
</label>
<input type="text" id="name" name="name" value="<?= esc(old('name',$role['name']??''),'attr') ?>" required>
</div>
<div class="form-field">
<label for="slug">
<?= esc(lang('UserRole.roleSlug')) ?>
</label>
<input type="text" id="slug" name="slug" dir="ltr" value="<?= esc(old('slug',$role['slug']??''),'attr') ?>" required>
<small>
<?= esc(lang('Studio.roleSlugHelp')) ?>
</small>
</div>
</div>
<div class="form-field role-description">
<label for="description">
<?= esc(lang('UserRole.description')) ?>
</label>
<textarea id="description" name="description" rows="2">
<?= esc(old('description',$role['description']??'')) ?>
</textarea>
</div>
<div class="role-options">
<fieldset>
<legend>
<?= esc(lang('UserRole.status')) ?>
</legend>
<?php foreach([1=>'active',0=>'inactive'] as $value=>$label): ?>
<label>
<input type="radio" name="is_active" value="<?= $value ?>" <?= (string)old('is_active',$role['is_active']??1)===(string)$value?'checked':'' ?>>
<?= esc(lang('UserRole.'.$label)) ?>
</label>
<?php endforeach; ?>
</fieldset>
<fieldset>
<legend>
<?= esc(lang('UserRole.roleType')) ?>
</legend>
<?php foreach([1=>'medicalRoleDoctor',0=>'nonMedicalRole'] as $value=>$label): ?>
<label>
<input type="radio" name="is_medical" value="<?= $value ?>" <?= (string)old('is_medical',$role['is_medical']??0)===(string)$value?'checked':'' ?>>
<?= esc(lang('UserRole.'.$label)) ?>
</label>
<?php endforeach; ?>
</fieldset>
</div>
<?php if($roleEditing&&!empty($role['is_system'])): ?>
<p class="role-system-note">
<?= esc(lang('UserRole.systemRoles')) ?>
</p>
<?php endif; ?>
</section>
<section class="form-section">
<header>
<h2>
<?= esc(lang('UserRole.permissions')) ?>
</h2>
</header>
<p class="field-error" data-role-error role="alert" hidden>
</p>
<div class="permission-modules">
<?php foreach($permissions as $module=>$modulePermissions): ?>
<section class="permission-module" data-permission-module>
<header>
<h3>
<?= esc($categories[$module]??ucwords(str_replace('_',' ',$module))) ?>
</h3>
<div>
<button class="button secondary" type="button" data-permission-select="1">
<?= esc(lang('UserRole.selectAll')) ?>
</button>
<button class="button secondary" type="button" data-permission-select="0">
<?= esc(lang('UserRole.deselectAll')) ?>
</button>
</div>
</header>
<div class="permission-grid">
<?php foreach($modulePermissions as $permission): ?>
<?php $selected=$roleEditing?(old('permissions')?in_array($permission['id'],old('permissions')):in_array($permission['id'],$role_permissions)):in_array($permission['id'],old('permissions',[]));$action=$permission['action']??null;$system=$roleEditing&&!empty($role['is_system'])&&!empty($permission['is_system']); ?>
<label class="permission-choice">
<input type="checkbox" name="permissions[]" value="<?= (int)$permission['id'] ?>" class="permission-checkbox" data-module="<?= esc($module,'attr') ?>" <?= $selected?'checked':'' ?>>
<span>
<strong>
<?= esc($actionLabels[$action]??$permission['name']) ?>
</strong>
<?php if($action&&isset($actionDescriptions[$action])): ?>
<small>
<?= esc($actionDescriptions[$action]) ?>
</small>
<?php endif; ?>
<?php if($system): ?>
<small class="permission-system">
<?= esc(lang('UserRole.system')) ?>
</small>
<?php endif; ?>
</span>
</label>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
<?php if(empty($permissions)): ?>
<div class="role-empty">
<h3>
<?= esc(lang('UserRole.noPermissionsAvailable')) ?>
</h3>
<p>
<?= esc(lang('UserRole.syncPermissionsFirstCreate')) ?>
</p>
</div>
<?php endif; ?>
</div>
</section>
