<?php $currentRoleId='';if($userEditing){if(!empty($editedUser->roles)&&is_array($editedUser->roles))$currentRoleId=$editedUser->roles[0]['role_id']??'';elseif(!empty($editedUser->current_role_id))$currentRoleId=$editedUser->current_role_id;}$selectedRole=old('role',$currentRoleId); ?>
<section class="form-section">
<header>
<h2>
<?= esc(lang('UserRole.personalInformation')) ?>
</h2>
<?php if($userEditing): ?>
<p>
<?= esc(lang('Studio.sharedAccountReadOnly')) ?>
</p>
<?php endif; ?>
</header>
<div class="staff-identity-grid">
<?php foreach([['first_name','firstNameRequired','text'],['last_name','lastNameRequired','text'],['email','emailRequired','email'],['phone','phone','tel'],['hire_date','hireDateRequired','date']] as [$field,$label,$type]): ?>
<div class="form-field">
<label for="<?= $userEditing?'display_':'' ?>
<?= $field ?>">
<?= esc(lang('UserRole.'.$label)) ?>
</label>
<?php if($userEditing): ?>
<input id="display_<?= $field ?>" type="text" value="<?= esc($field==='hire_date'?(!empty($editedUser->hire_date)?formatDate((string)$editedUser->hire_date):lang('UserRole.na')):($editedUser->$field??''),'attr') ?>" readonly disabled>
<?php else: ?>
<input type="<?= $type ?>" id="<?= $field ?>" name="<?= $field ?>" value="<?= esc(old($field,$field==='hire_date'?date('Y-m-d'):''),'attr') ?>" <?= $field==='phone'?'':'required' ?> <?= in_array($type,['email','tel','date'],true)?'dir="ltr"':'' ?> <?= $field==='email'?'data-user-email-check data-password-targets="#password,#password_confirm" data-existing-note-target="#users-email-exists-note"':'' ?>>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</section>
<?php if(!$userEditing): ?>
<section class="form-section">
<header>
<h2>
<?= esc(lang('UserRole.accountSecurity')) ?>
</h2>
</header>
<div class="role-identity">
<?php foreach([['password','passwordRequired'],['password_confirm','confirmPasswordRequired']] as [$field,$label]): ?>
<div class="form-field">
<label for="<?= $field ?>">
<?= esc(lang('UserRole.'.$label)) ?>
</label>
<input id="<?= $field ?>" name="<?= $field ?>" type="password" autocomplete="new-password" dir="ltr" required>
<?php if($field==='password'): ?>
<p id="users-email-exists-note" class="field-error hidden" role="status">
<span data-note-text>
</span>
</p>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</section>
<?php endif; ?>
<section class="form-section">
<header>
<h2>
<?= esc(lang('UserRole.roleAndPermissions')) ?>
</h2>
</header>
<div class="staff-access-grid">
<div class="form-field">
<label for="role">
<?= esc(lang('UserRole.roleRequired')) ?>
</label>
<select id="role" name="role" required>
<option value="">
<?= esc(lang('UserRole.selectRole')) ?>
</option>
<?php foreach($roles as $key=>$role): ?>
<?php $roleId=is_array($role)?($role['id']??$key):$key;$roleName=is_array($role)?($role['name']??$roleId):$role; ?>
<option value="<?= esc($roleId,'attr') ?>" <?= (string)$selectedRole===(string)$roleId?'selected':'' ?>>
<?= esc($roleName) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="form-field">
<label for="<?= $userEditing?'display_department':'department' ?>">
<?= esc(lang('UserRole.department')) ?>
</label>
<?php if($userEditing): ?>
<input id="display_department" value="<?= esc($departments[$editedUser->department??'']??($editedUser->department??lang('UserRole.na')),'attr') ?>" disabled readonly>
<?php else: ?>
<select id="department" name="department">
<option value="">
<?= esc(lang('UserRole.selectDepartment')) ?>
</option>
<?php foreach($departments as $key=>$department): ?>
<option value="<?= esc($key,'attr') ?>" <?= (string)old('department')===(string)$key?'selected':'' ?>>
<?= esc($department) ?>
</option>
<?php endforeach; ?>
</select>
<?php endif; ?>
</div>
<?php if(!$userEditing): ?>
<div class="form-field">
<label for="status">
<?= esc(lang('UserRole.statusRequired')) ?>
</label>
<select id="status" name="status" required>
<?php foreach(['active','inactive','suspended'] as $status): ?>
<option value="<?= $status ?>" <?= old('status','active')===$status?'selected':'' ?>>
<?= esc(lang('UserRole.'.$status)) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<?php endif; ?>
</div>
</section>
