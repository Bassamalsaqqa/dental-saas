<?php helper('asset');include APPPATH.'Views/layouts/partials/studio_icons.php'; ?>
<?= $this->extend('layouts/studio_control_plane') ?>
<?= $this->section('styles') ?><link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>"><link rel="stylesheet" href="<?= base_url('assets/css/studio-platform-account.css') ?>?v=<?= esc(asset_version('assets/css/studio-platform-account.css')) ?>"><link rel="stylesheet" href="<?= base_url('assets/css/studio-platform-directory.css') ?>?v=<?= esc(asset_version('assets/css/studio-platform-directory.css')) ?>"><?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php $accountId=(int)$userProfile['id'];$accountName=trim(($userProfile['first_name']??'').' '.($userProfile['last_name']??''))?:($userProfile['email']??('#'.$accountId));$accountActive=(int)($userProfile['active']??0)===1;$memberships=is_array($memberships??null)?$memberships:[];$roleOptions=is_array($rolesByClinic??null)?$rolesByClinic:[];$adminStateLabel=static function(array $info){if(($info['state_label']??'')==='User Missing')return lang('UI.clinicAdminStateUserMissing');$keys=['healthy'=>'clinicAdminStateHealthy','disabled_user'=>'clinicAdminStateDisabled','inactive_membership'=>'clinicAdminStateInactiveMembership','missing_authority'=>'clinicAdminStateMissingAuthority','unassigned'=>'clinicAdminStateUnassigned'];return isset($keys[$info['state']??''])?lang('UI.'.$keys[$info['state']]):($info['state_label']??lang('ActivityLog.notAvailable'));}; ?>
<header class="page-title-row">
<h1>
<?= esc($accountName) ?>
</h1>
<a class="button secondary" href="<?= base_url('controlplane/users') ?>">
<?= esc(lang('Studio.back')) ?>
</a>
</header>
<?php foreach(['error'=>'alert','success'=>'status'] as $key=>$role):if(session($key)): ?>
<p class="form-notice <?= $key==='error'?'form-notice--error':'' ?>" role="<?= $role ?>">
<?= esc(session($key)) ?>
</p>
<?php endif;endforeach; ?>
<div class="platform-account-layout">
<section class="form-section platform-account-identity">
<header>
<h2>
<?= esc(lang('Studio.platformAccountIdentity')) ?>
</h2>
<span class="platform-directory-status platform-directory-status--<?= $accountActive?'active':'inactive' ?>">
<?= esc(lang('Studio.platformStatus_'.($accountActive?'active':'inactive'))) ?>
</span>
</header>
<dl>
<?php foreach(['platformAccountId'=>'#'.$accountId,'platformEmail'=>$userProfile['email']??'','platformPhone'=>$userProfile['phone']??'','platformCreatedAt'=>!empty($userProfile['created_on'])?date('Y-m-d',(int)$userProfile['created_on']):''] as $label=>$value): ?>
<div>
<dt>
<?= esc(lang('Studio.'.$label)) ?>
</dt>
<dd>
<?= esc($value?:lang('ActivityLog.notAvailable')) ?>
</dd>
</div>
<?php endforeach; ?>
</dl>
<h3>
<?= esc(lang('Studio.platformGlobalRoles')) ?>
</h3>
<div class="platform-account-global-roles">
<?php foreach(($globalRoles??[]) as $role): ?>
<span>
<?= esc($role['name']) ?>
</span>
<?php endforeach; ?>
<?php if(empty($globalRoles)): ?>
<p>
<?= esc(lang('Studio.platformNoGlobalRoles')) ?>
</p>
<?php endif; ?>
</div>
<p class="platform-account-scope">
<?= esc(lang('Studio.platformRoleScope')) ?>
</p>
</section>
<section class="form-section platform-account-memberships">
<header class="platform-account-heading">
<div>
<h2>
<?= esc(lang('Studio.platformMemberships')) ?>
</h2>
<p>
<?= count($memberships) ?> <?= esc(lang('Studio.platformLinkedClinics')) ?>
</p>
</div>
<button class="button primary" type="button" data-dialog="platform-membership-add">
<?= $studioIcon('plus') ?>
<?= esc(lang('Studio.platformAddMembership')) ?>
</button>
</header>
<table class="studio-grid" data-grid-widths="28,19,20,21,12" aria-label="<?= esc(lang('Studio.platformMemberships'),'attr') ?>">
<thead>
<tr>
<?php foreach(['platformClinic','platformJoinedAt','platformClinicRole','platformAdministrator','actions'] as $label): ?>
<th scope="col">
<?= esc(lang('Studio.'.$label)) ?>
</th>
<?php endforeach; ?>
</tr>
</thead>
<tbody>
<?php foreach($memberships as $membership): ?>
<tr data-membership-dialog="membership-<?= (int)$membership['id'] ?>">
<td>
<a href="<?= base_url('controlplane/clinics/'.(int)$membership['clinic_id']) ?>">
<?= esc($membership['clinic_name']) ?>
</a>
<small>#<?= (int)$membership['clinic_id'] ?>
</small>
</td>
<td>
<?= esc($membership['created_at']??'') ?>
</td>
<td>
<?= esc($membership['role_name']??'') ?>
</td>
<td>
<?= esc(!empty($membership['is_designated_admin'])&& !empty($membership['admin_is_healthy'])?lang('UI.primaryAdmin'):(!empty($membership['is_fallback_candidate'])?lang('UI.legacyAdminCandidate'):$adminStateLabel($membership['admin_info']??[]))) ?>
</td>
<td>
<button class="button secondary" type="button" data-dialog="membership-<?= (int)$membership['id'] ?>">
<?= esc(lang('Studio.actions')) ?>
</button>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php if(!$memberships): ?>
<p class="platform-account-empty">
<?= esc(lang('Studio.platformNoMemberships')) ?>
</p>
<?php endif; ?>
</section>
</div>
<section class="form-section platform-account-access">
<header>
<h2>
<?= esc(lang('Studio.platformAccountAccess')) ?>
</h2>
</header>
<div class="platform-account-access-actions">
<form action="<?= base_url('controlplane/users/'.$accountId.'/toggle-status') ?>" method="post" data-account-confirm="<?= esc(lang('Studio.platformToggleUser'),'attr') ?>">
<?= csrf_field() ?>
<button class="button secondary" type="submit">
<?= esc(lang('Studio.platformToggleUser')) ?>
</button>
</form>
<button class="button secondary" type="button" data-dialog="passwordResetModal">
<?= esc(lang('Studio.platformResetPassword')) ?>
</button>
<span>
<?= esc(lang('IonAuth.delete_disabled_label')) ?>
</span>
</div>
<p>
<?= esc(lang('IonAuth.delete_history_protected')) ?>
</p>
</section>
<?php foreach($memberships as $membership):$membershipId=(int)$membership['id'];$clinicId=(int)$membership['clinic_id'];$adminInfo=$membership['admin_info']??[];$designated=!empty($membership['is_designated_admin']);$healthy=!empty($membership['admin_is_healthy']);$fallback=!empty($membership['is_fallback_candidate']);$currentAdmin=(int)($adminInfo['admin_user']['id']??0);$replacement=!$fallback&&empty($adminInfo['is_fallback'])&&!empty($adminInfo['has_admin'])&&$currentAdmin>0;$available=$roleOptions[$clinicId]??[];$currentRoleAvailable=in_array((int)$membership['role_id'],array_map(static fn($role)=>(int)$role['id'],$available),true); ?>
<dialog class="platform-directory-review platform-membership-review" id="membership-<?= $membershipId ?>" aria-labelledby="membership-title-<?= $membershipId ?>">
<header>
<div>
<h2 id="membership-title-<?= $membershipId ?>">
<?= esc($membership['clinic_name']) ?>
</h2>
<p>#<?= $clinicId ?> · <?= esc($membership['role_name']??'') ?>
</p>
</div>
<button class="icon-button" type="button" data-close aria-label="<?= esc(lang('Studio.close'),'attr') ?>">
<?= $studioIcon('close') ?>
</button>
</header>
<div class="platform-directory-review-body">
<a class="button secondary" href="<?= base_url('controlplane/clinics/'.$clinicId) ?>">
<?= esc(lang('Studio.platformOpenClinic')) ?>
</a>
<section class="platform-membership-operation">
<h3>
<?= esc(lang('Studio.platformClinicRole')) ?>
</h3>
<form class="studio-form" method="post" action="<?= base_url('controlplane/users/'.$accountId.'/memberships/'.$membershipId.'/update-role') ?>" data-account-confirm="<?= esc(lang('Studio.platformUpdateMembershipRole'),'attr') ?>">
<?= csrf_field() ?>
<div class="form-field">
<label for="membership-role-<?= $membershipId ?>">
<?= esc(lang('Studio.platformClinicRole')) ?>
</label>
<select id="membership-role-<?= $membershipId ?>" name="role_id" required>
<?php if(!$currentRoleAvailable): ?>
<option value="" selected disabled>
<?= esc($membership['role_name']??lang('ActivityLog.notAvailable')) ?> · <?= esc(lang('Studio.platformRoleUnavailable')) ?>
</option>
<?php endif; ?>
<?php foreach($available as $role): ?>
<option value="<?= (int)$role['id'] ?>" <?= (int)$membership['role_id']===(int)$role['id']?'selected':'' ?>>
<?= esc($role['name']) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<button class="button primary" type="submit" <?= !$available?'disabled':'' ?>>
<?= esc(lang('Studio.platformUpdateMembershipRole')) ?>
</button>
</form>
</section>
<section class="platform-membership-operation">
<h3>
<?= esc(lang('UI.clinicAdminTitle')) ?>
</h3>
<p>
<?= esc($adminStateLabel($adminInfo)) ?>
</p>
<?php if($designated&&$healthy): ?>
<p class="platform-account-notice">
<?= esc(lang('UI.primaryAdmin')) ?>
</p>
<?php elseif(!$accountActive): ?>
<p class="platform-account-notice">
<?= esc(lang('UI.accountInactive')) ?>
</p>
<?php elseif($designated): ?>
<p>
<?= esc(lang('UI.clinicAdminRestoreHelp')) ?>
</p>
<form class="studio-form" action="<?= base_url('controlplane/users/'.$accountId.'/admin/restore') ?>" method="post" data-account-confirm="<?= esc(lang('UI.clinicAdminConfirmRestore'),'attr') ?>">
<?= csrf_field() ?>
<input type="hidden" name="clinic_id" value="<?= $clinicId ?>">
<input type="hidden" name="expected_designated_admin_id" value="<?= $currentAdmin?:$accountId ?>">
<div class="form-field">
<label for="restore-reason-<?= $membershipId ?>">
<?= esc(lang('UI.clinicAdminReason')) ?>
</label>
<input id="restore-reason-<?= $membershipId ?>" name="reason">
</div>
<button class="button primary" type="submit">
<?= esc(lang('UI.restoreAuthority')) ?>
</button>
</form>
<?php else: ?>
<form class="studio-form" action="<?= base_url('controlplane/users/'.$accountId.'/admin/designate') ?>" method="post" data-account-confirm="<?= esc(lang($fallback?'UI.confirmDesignation':($replacement?'UI.clinicAdminConfirmReplace':'UI.clinicAdminConfirmAssign')),'attr') ?>">
<?= csrf_field() ?>
<input type="hidden" name="clinic_id" value="<?= $clinicId ?>">
<input type="hidden" name="expected_admin_id" value="<?= $replacement?$currentAdmin:'' ?>">
<?php if($replacement): ?>
<div class="platform-account-notice">
<strong>
<?= esc(lang('UI.currentAdministratorLabel')) ?>
</strong>
<p>
<?= esc($adminInfo['admin_name']??'') ?> · <?= esc($adminInfo['admin_email']??'') ?>
</p>
</div>
<fieldset class="platform-previous-admin">
<legend>
<?= esc(lang('Studio.platformPreviousAdminAction')) ?>
</legend>
<?php foreach(['demote_to_staff'=>'clinicAdminDemotePrevious','keep_role'=>'clinicAdminKeepRolePrevious','deactivate_membership'=>'clinicAdminDeactivateMembershipPrevious'] as $value=>$key): ?>
<label>
<input type="radio" name="previous_admin_action" value="<?= $value ?>" <?= $value==='demote_to_staff'?'checked':'' ?>>
<span>
<?= esc(lang('UI.'.$key)) ?>
</span>
</label>
<?php endforeach; ?>
</fieldset>
<?php else: ?>
<input type="hidden" name="previous_admin_action" value="demote_to_staff">
<?php endif; ?>
<div class="form-field">
<label for="designate-reason-<?= $membershipId ?>">
<?= esc(lang('UI.clinicAdminReason')) ?>
</label>
<input id="designate-reason-<?= $membershipId ?>" name="reason">
</div>
<button class="button primary" type="submit">
<?= esc(lang($fallback?'UI.confirmDesignation':'UI.makeAdministrator')) ?>
</button>
</form>
<?php endif; ?>
</section>
<section class="platform-membership-operation">
<h3>
<?= esc(lang('Studio.platformRemoveMembership')) ?>
</h3>
<p>
<?= esc(lang('Studio.platformRemoveMembershipHelp')) ?>
</p>
<form method="post" action="<?= base_url('controlplane/users/'.$accountId.'/memberships/'.$membershipId.'/remove') ?>" data-account-confirm="<?= esc(lang('Studio.platformRemoveMembership'),'attr') ?>">
<?= csrf_field() ?>
<button class="button destructive" type="submit">
<?= esc(lang('Studio.platformRemoveMembership')) ?>
</button>
</form>
</section>
</div>
</dialog>
<?php endforeach; ?>
<dialog class="help-dialog" id="platform-membership-add" aria-labelledby="membership-add-title">
<header>
<h2 id="membership-add-title">
<?= esc(lang('Studio.platformAddMembership')) ?>
</h2>
<button class="icon-button" type="button" data-close aria-label="<?= esc(lang('Studio.close'),'attr') ?>">
<?= $studioIcon('close') ?>
</button>
</header>
<form class="studio-form platform-account-dialog-form" method="post" action="<?= base_url('controlplane/users/'.$accountId.'/memberships/add') ?>" data-account-confirm="<?= esc(lang('Studio.platformAddMembership'),'attr') ?>">
<?= csrf_field() ?>
<div class="form-field">
<label for="addMembershipClinic">
<?= esc(lang('Studio.platformClinic')) ?>
</label>
<select id="addMembershipClinic" name="clinic_id" required>
<option value="">
<?= esc(lang('Studio.chooseOption')) ?>
</option>
<?php foreach(($assignableClinics??[]) as $clinic): ?>
<option value="<?= (int)$clinic['id'] ?>">#<?= (int)$clinic['id'] ?> · <?= esc($clinic['name']) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="form-field">
<label for="addMembershipRole">
<?= esc(lang('Studio.platformClinicRole')) ?>
</label>
<select id="addMembershipRole" name="role_id" disabled required>
<option value="">
<?= esc(lang('Studio.chooseOption')) ?>
</option>
</select>
</div>
<button class="button primary" type="submit">
<?= esc(lang('Studio.platformAddMembership')) ?>
</button>
</form>
</dialog>
<dialog class="help-dialog" id="passwordResetModal" aria-labelledby="password-reset-title">
<header>
<h2 id="password-reset-title">
<?= esc(lang('Studio.platformResetPassword')) ?>
</h2>
<button class="icon-button" type="button" data-close aria-label="<?= esc(lang('Studio.close'),'attr') ?>">
<?= $studioIcon('close') ?>
</button>
</header>
<form class="studio-form platform-account-dialog-form" action="<?= base_url('controlplane/users/'.$accountId.'/reset-password') ?>" method="post" data-account-confirm="<?= esc(lang('Studio.platformResetPassword'),'attr') ?>">
<?= csrf_field() ?>
<p>
<?= esc($userProfile['email']??'') ?>
</p>
<div class="form-field">
<label for="account-new-password">
<?= esc(lang('Studio.platformNewPassword')) ?>
</label>
<input id="account-new-password" type="password" name="password" required minlength="8" autocomplete="new-password">
</div>
<button class="button primary" type="submit">
<?= esc(lang('Studio.platformResetPassword')) ?>
</button>
</form>
</dialog>
<dialog class="help-dialog" id="platform-account-confirm" aria-labelledby="account-confirm-title">
<header>
<h2 id="account-confirm-title">
</h2>
<button class="icon-button" type="button" data-close aria-label="<?= esc(lang('Studio.close'),'attr') ?>">
<?= $studioIcon('close') ?>
</button>
</header>
<div class="platform-account-confirm-body">
<p>
<?= esc(lang('Studio.platformAccountConfirmHelp')) ?>
</p>
<dl>
</dl>
</div>
<footer class="platform-account-dialog-actions">
<button class="button secondary" type="button" data-close>
<?= esc(lang('Studio.cancel')) ?>
</button>
<button class="button primary" type="button" data-account-proceed>
<?= esc(lang('Studio.confirm')) ?>
</button>
</footer>
</dialog>
<script id="platform-account-config" type="application/json">
<?= json_encode(['roles'=>array_map(static fn($roles)=>array_map(static fn($role)=>['id'=>(int)$role['id'],'name'=>$role['name']],$roles),$roleOptions),'choose'=>lang('Studio.chooseOption'),'account'=>$accountName,'labels'=>['account'=>lang('Studio.platformAccountIdentity'),'clinic_id'=>lang('Studio.platformClinic'),'role_id'=>lang('Studio.platformClinicRole'),'previous_admin_action'=>lang('Studio.platformPreviousAdminAction'),'reason'=>lang('UI.clinicAdminReason')]],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>
</script>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/studio-platform-account.js') ?>?v=<?= esc(asset_version('assets/js/studio-platform-account.js')) ?>" defer>
</script>
<?= $this->endSection() ?>
