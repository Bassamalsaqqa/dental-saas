<?php helper('asset');$cl=static fn($key)=>esc(lang('Studio.pc'.$key));$clinicState=static function($value){$key='Studio.pcState_'.$value;$label=lang($key);return esc($label===$key?$value:$label);};
$admin_info=$admin_info??[];$subscription=$subscription??null;$clinicRoute='controlplane/clinics/'.(int)$clinic['id'];$waPolicy=is_array($wa_policy??null)?$wa_policy:[];$waPolicyToggles=is_array($wa_policy_toggles??null)?$wa_policy_toggles:[];$waTriggerCatalog=is_array($wa_trigger_catalog??null)?$wa_trigger_catalog:[];$waGlobalTemplates=is_array($wa_global_templates??null)?$wa_global_templates:[];$waGlobalTriggerTemplates=is_array($waGlobalTemplates['trigger_templates']??null)?$waGlobalTemplates['trigger_templates']:[];$waGlobalTriggerToggles=is_array($waGlobalTemplates['trigger_toggles']??null)?$waGlobalTemplates['trigger_toggles']:[];$clinicSettingsSnapshot=is_array($clinic_settings_snapshot??null)?$clinic_settings_snapshot:[];
$adminStateLabel=!empty($admin_info['is_fallback'])?lang('UI.legacyAdminCandidate'):lang('UI.'.(['healthy'=>'clinicAdminHealthy','disabled_user'=>'clinicAdminDisabledUser','inactive_membership'=>'clinicAdminInactiveMembership','missing_authority'=>'clinicAdminMissingAuthority'][$admin_info['status']??'']??'clinicAdminUnassigned'));
$clinicForm=static function($action,$title)use($cl){echo '<form class="studio-form clinic-operation-form" action="'.base_url($action).'" method="post" data-clinic-review>
<h3>'.$title.'</h3>'.csrf_field();};
$clinicEnd=static function($label,$disabled=false){echo '<footer>
<button class="button primary" type="submit" '.($disabled?'disabled':'').'>'.$label.'</button>
</footer>
</form>';};
?>
<?= $this->extend('layouts/studio_control_plane') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-platform-clinic.css') ?>?v=<?= esc(asset_version('assets/css/studio-platform-clinic.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<header class="page-title-row">
<h1>
<?= esc($clinic['name']) ?>
</h1>
<a class="button secondary" href="<?= base_url('controlplane/clinics') ?>">
<?= $cl('Clinics') ?>
</a>
</header>
<?php if(session('error')): ?>
<div class="form-notice form-notice--error" role="alert">
<?= esc(session('error')) ?>
</div>
<?php endif; ?>
<nav class="clinic-sections" aria-label="<?= $cl('Areas') ?>">
<?php foreach(['access'=>'Access','subscription'=>'Subscription','billing'=>'Billing','whatsapp'=>'WhatsApp','lifecycle'=>'Lifecycle'] as $id=>$label): ?>
<a class="button secondary" href="#clinic-<?= $id ?>">
<?= $cl($label) ?>
</a>
<?php endforeach; ?>
</nav>
<div class="platform-clinic-layout">
<aside class="clinic-identity form-section">
<h2>
<?= $cl('Identity') ?>
</h2>
<dl>
<dt>
<?= $cl('Status') ?>
</dt>
<dd>
<?= $clinicState($clinic['status']??'inactive') ?>
</dd>
<dt>
<?= $cl('Slug') ?>
</dt>
<dd>
<?= esc($clinic['slug']??'—') ?>
</dd>
<dt>
<?= $cl('Created') ?>
</dt>
<dd>
<?= esc($clinic['created_at']??'—') ?>
</dd>
</dl>
<?php $clinicForm('controlplane/impersonate',$cl('EnterClinic')); ?>
<input type="hidden" name="clinic_id" value="<?= esc($clinic['id']) ?>">
<p>
<?= $cl('EnterNote') ?>
</p>
<?php $clinicEnd($cl('EnterClinic')); ?>
</aside>
<div class="clinic-main">
<section id="clinic-access" class="form-section">
<header>
<h2>
<?= esc(lang('UI.clinicAdminTitle')) ?>
</h2>
<span class="clinic-state <?= !empty($admin_info['is_healthy'])?'is-healthy':'needs-attention' ?>">
<?= esc($adminStateLabel) ?>
</span>
</header>
<?php if(!empty($admin_info['issues'])): ?>
<div class="form-notice">
<ul>
<?php if(!empty($admin_info['issue_codes'])):foreach($admin_info['issue_codes'] as $code): ?>
<li>
<?= esc(lang('UI.'.$code)) ?>
</li>
<?php endforeach;else:foreach($admin_info['issues'] as $issue): ?>
<li>
<?= esc($issue) ?>
</li>
<?php endforeach;endif; ?>
</ul>
</div>
<?php endif; ?>
<?php if(!empty($admin_info['admin_user'])):$u=$admin_info['admin_user']; ?>
<div class="clinic-admin-person">
<a href="<?= base_url('controlplane/users/'.(int)$u['id']) ?>">
<strong>
<?= esc(trim(($u['first_name']??'').' '.($u['last_name']??''))?:($u['username']??'User #'.$u['id'])) ?>
</strong>
<span>
<?= esc($u['email']??'—') ?>
</span>
</a>
<?php if(!empty($u['phone'])): ?>
<span>
<?= esc($u['phone']) ?>
</span>
<?php endif; ?>
</div>
<dl class="clinic-authority-facts">
<?php foreach(['account_active'=>'clinicAdminAccountActive','membership_active'=>'clinicAdminMembershipActive','has_admin_authority'=>'clinicAdminAuthorityActive'] as $flag=>$label): ?>
<div>
<dt>
<?= esc(lang('UI.'.$label)) ?>
</dt>
<dd>
<?= esc(lang('UI.'.($flag==='account_active'?(!empty($admin_info[$flag])?'statusActive':'statusDisabled'):($flag==='membership_active'?(!empty($admin_info[$flag])?'statusActive':'statusInactive'):(!empty($admin_info[$flag])?'statusManager':'statusMissing'))))) ?>
</dd>
</div>
<?php endforeach; ?>
<div>
<dt>
<?= esc(lang('UI.clinicAdminAssignedSince')) ?>
</dt>
<dd>
<?= !empty($admin_info['assigned_since'])?esc(date('M j, Y',strtotime($admin_info['assigned_since']))):'—' ?>
</dd>
</div>
<div>
<dt>
<?= $cl('Designation') ?>
</dt>
<dd>
<?= esc(lang('UI.'.(($admin_info['designation_source']??'')==='designated'?'clinicAdminSourceDesignated':'legacyAdminCandidate'))) ?>
</dd>
</div>
</dl>
<div class="clinic-actions">
<?php if(!empty($admin_info['is_fallback'])): ?>
<button class="button primary" type="button" data-dialog="clinic-admin-assign">
<?= esc(lang('UI.confirmDesignation')) ?>
</button>
<?php elseif(!($admin_info['is_healthy']??false)): ?>
<button class="button primary" type="button" data-dialog="clinic-admin-restore">
<?= esc(lang('UI.clinicAdminRestoreBtn')) ?>
</button>
<?php endif; ?>
<button class="button secondary" type="button" data-dialog="clinic-admin-replace">
<?= esc(lang('UI.clinicAdminReplaceBtn')) ?>
</button>
<button class="button secondary" type="button" data-dialog="clinic-admin-password">
<?= esc(lang('UI.clinicAdminResetPassword')) ?>
</button>
</div>
<?php else: ?>
<p class="form-notice">
<?= esc(lang('UI.clinicAdminWarningUnassigned')) ?>
</p>
<button class="button primary" type="button" data-dialog="clinic-admin-assign">
<?= esc(lang('UI.clinicAdminAssignBtn')) ?>
</button>
<?php endif; ?>
</section>
<section id="clinic-subscription" class="form-section">
<header>
<h2>
<?= $cl('Subscription') ?>
</h2>
<span class="clinic-state">
<?= $clinicState($subscription['status']??'—') ?>
</span>
</header>
<dl class="clinic-subscription-facts">
<div>
<dt>
<?= $cl('Plan') ?>
</dt>
<dd>
<?= esc($plan_name??'—') ?>
</dd>
</div>
<?php foreach(['start_at'=>'Start','end_at'=>'End'] as $field=>$label): ?>
<div>
<dt>
<?= $cl($label) ?>
</dt>
<dd>
<?= esc(\App\Models\ClinicSubscriptionModel::formatDisplay($subscription[$field]??null)) ?>
</dd>
</div>
<?php endforeach; ?>
</dl>
<div class="clinic-subscription-actions">
<?php $clinicForm($clinicRoute.'/subscription/start',$cl('ChangePlan')); ?>
<div class="form-field">
<label for="clinic-plan">
<?= $cl('Plan') ?>
</label>
<select id="clinic-plan" name="plan_id" required>
<option value="">
<?= $cl('SelectPlan') ?>
</option>
<?php foreach($plans??[] as $plan): ?>
<option value="<?= esc($plan['id']) ?>">
<?= esc($plan['name']) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<?php $clinicEnd($cl('ChangePlan')); ?>
<?php $clinicForm($clinicRoute.'/subscription/extend',$cl('Extend')); ?>
<input type="hidden" name="expected_end_at" value="<?= ($subscription&&$subscription['end_at']!==null)?esc($subscription['end_at']):'__NULL__' ?>">
<input type="hidden" name="expected_updated_at" value="<?= ($subscription&&$subscription['updated_at']!==null)?esc($subscription['updated_at']):'__NULL__' ?>">
<div class="form-field">
<label for="clinic-duration">
<?= $cl('Duration') ?>
</label>
<select id="clinic-duration" name="duration">
<option value="monthly">
<?= $cl('Month') ?>
</option>
<option value="yearly">
<?= $cl('Year') ?>
</option>
</select>
</div>
<?php $clinicEnd($cl('Extend')); ?>
</div>
<details class="clinic-advanced">
<summary>
<?= $cl('AdvancedSubscription') ?>
</summary>
<?php $clinicForm($clinicRoute.'/subscription/update-dates',$cl('ChangeDates'));foreach(['start_at','end_at','updated_at'] as $field): ?>
<input type="hidden" name="expected_<?= $field ?>" value="<?= ($subscription&&$subscription[$field]!==null)?esc($subscription[$field]):'__NULL__' ?>">
<?php endforeach; ?>
<div class="clinic-field-pair">
<?php foreach(['start_at'=>'Start','end_at'=>'End'] as $field=>$label): ?>
<div class="form-field">
<label for="clinic-<?= $field ?>">
<?= $cl($label) ?>
</label>
<input id="clinic-<?= $field ?>" type="text" name="<?= $field ?>" value="<?= esc($subscription[$field]??'','attr') ?>" placeholder="YYYY-MM-DD HH:MM:SS">
</div>
<?php endforeach; ?>
</div>
<?php $clinicEnd($cl('ChangeDates')); ?>
<?php $clinicForm($clinicRoute.'/subscription/cancel',$cl('CancelSubscription')); ?>
<input type="hidden" name="expected_updated_at" value="<?= ($subscription&&$subscription['updated_at']!==null)?esc($subscription['updated_at']):'__NULL__' ?>">
<input type="hidden" name="canceled_reason" value="manual_cancel">
<?php $clinicEnd($cl('CancelSubscription')); ?>
</details>
</section>
<section id="clinic-billing" class="form-section">
<header>
<h2>
<?= $cl('Billing') ?>
</h2>
<a class="button secondary" href="<?= base_url('controlplane/invoices?clinic_id='.(int)$clinic['id']) ?>">
<?= $cl('Invoices') ?>
</a>
</header>
<?php if(!($invoice_data_available??true)): ?>
<p class="form-notice">
<?= esc(lang('UI.billingDataUnavailable')) ?>
</p>
<?php else: ?>
<details class="clinic-advanced">
<summary>
<?= $cl('BillingProjection') ?>
</summary>
<p>
<?= $cl('BillingProjectionNote') ?>
</p>
<dl class="clinic-subscription-facts">
<div>
<dt>
<?= $cl('Outstanding') ?>
</dt>
<dd>
<?= !empty($invoice_data_available)?'$'.number_format($invoiceStats['outstanding']??0,2):'—' ?>
</dd>
</div>
<div>
<dt>
<?= $cl('MonthPaid') ?>
</dt>
<dd>
<?= !empty($invoice_data_available)?'$'.number_format($invoiceStats['revenue_month']??0,2):'—' ?>
</dd>
</div>
<div>
<dt>
<?= $cl('Overdue') ?>
</dt>
<dd>
<?= !empty($invoice_data_available)?(int)($invoiceStats['overdue']??0):'—' ?>
</dd>
</div>
</dl>
</details>
<?php if(!empty($invoices)): ?>
<table class="studio-grid" data-grid-widths="30,24,24,22" aria-label="<?= $cl('LatestInvoices') ?>">
<thead>
<tr>
<?php foreach(['Invoice','IssueDate','Total','Status'] as $key): ?>
<th scope="col">
<?= $cl($key) ?>
</th>
<?php endforeach; ?>
</tr>
</thead>
<tbody>
<?php foreach(array_slice($invoices,0,5) as $inv): ?>
<tr data-clinic-invoice>
<td>
<a href="<?= base_url('controlplane/invoices/'.(int)$inv['id']) ?>">
<?= esc($inv['invoice_number']) ?>
</a>
</td>
<td>
<?= esc($inv['issue_date']) ?>
</td>
<td>$<?= number_format($inv['total_amount'],2) ?>
</td>
<td>
<?= $clinicState($inv['status']??'') ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p>
<?= $cl('NoInvoices') ?>
</p>
<?php endif;endif; ?>
</section>
<section id="clinic-whatsapp" class="form-section">
<header>
<h2>
<?= $cl('WhatsApp') ?>
</h2>
<span class="clinic-state">
<?= $cl(!empty($wa_feature_enabled)?'PlanEnabled':'PlanDisabled') ?>
</span>
</header>
<div class="clinic-contact-facts">
<?php foreach(['clinic_name'=>'SenderName','clinic_phone'=>'Phone','clinic_address'=>'Address'] as $field=>$label): ?>
<div>
<strong>
<?= $cl($label) ?>
</strong>
<span>
<?= esc(($clinicSettingsSnapshot[$field]??'')!==''?$clinicSettingsSnapshot[$field]:($field==='clinic_name'?($clinic['name']??'—'):'—')) ?>
</span>
</div>
<?php endforeach; ?>
</div>
<a class="button secondary" href="<?= base_url('controlplane/settings?tab=whatsapp-templates') ?>">
<?= $cl('SharedTemplates') ?>
</a>
<?php $clinicForm('controlplane/whatsapp/policy',$cl('ClinicPolicy')); ?>
<input type="hidden" name="clinic_id" value="<?= (int)$clinic['id'] ?>">
<div class="clinic-policy-limits">
<?php foreach(['daily_limit'=>['Daily',50],'monthly_limit'=>['Monthly',1000],'per_hour_limit'=>['Hourly',20],'reminder_lead_time_minutes'=>['LeadTime',1440]] as $field=>[$label,$fallback]): ?>
<div class="form-field">
<label for="policy-<?= $field ?>">
<?= $cl($label) ?>
</label>
<input id="policy-<?= $field ?>" type="number" name="<?= $field ?>" value="<?= (int)($waPolicy[$field]??$fallback) ?>">
</div>
<?php endforeach; ?>
</div>
<p class="clinic-note">
<?= $cl('LockedSwitches') ?>
</p>
<div class="clinic-trigger-list">
<?php foreach($waTriggerCatalog as $triggerKey=>$triggerMeta):$platformEnabled=(int)($waGlobalTriggerToggles[$triggerKey]??($triggerMeta['default_enabled']??1))===1;$clinicEnabled=(int)($waPolicyToggles[$triggerKey]??($triggerMeta['default_enabled']??1))===1;$triggerConfig=is_array($waGlobalTriggerTemplates[$triggerKey]??null)?$waGlobalTriggerTemplates[$triggerKey]:[];$displayTemplateName=trim((string)($triggerConfig['name']??''))!==''?(string)$triggerConfig['name']:(string)($triggerMeta['suggested_template_name']??($triggerKey.'_ar_v1')); ?>
<label>
<input type="checkbox" name="trigger_toggles[<?= esc($triggerKey) ?>]" value="1" <?= $clinicEnabled?'checked':'' ?> <?= !$platformEnabled||empty($wa_feature_enabled)?'disabled':'' ?>>
<span>
<strong>
<?= esc(lang('Studio.psTrigger_'.$triggerKey)==='Studio.psTrigger_'.$triggerKey?($triggerMeta['label']??$triggerKey):lang('Studio.psTrigger_'.$triggerKey)) ?>
</strong>
<code>
<?= esc($displayTemplateName) ?>
</code>
<small>
<?= $cl($platformEnabled?'PlatformEnabled':'PlatformDisabled') ?>
</small>
</span>
</label>
<?php if((!$platformEnabled||empty($wa_feature_enabled))&&$clinicEnabled): ?>
<input type="hidden" name="trigger_toggles[<?= esc($triggerKey) ?>]" value="1">
<?php endif;endforeach; ?>
</div>
<div class="clinic-field-pair">
<label class="clinic-pause">
<input type="checkbox" name="suspended" value="1" <?= !empty($waPolicy['suspended'])?'checked':'' ?>>
<span>
<?= $cl('EmergencyPause') ?>
</span>
</label>
<div class="form-field">
<label for="clinic-pause-reason">
<?= $cl('PauseReason') ?>
</label>
<input id="clinic-pause-reason" name="suspended_reason" value="<?= esc($waPolicy['suspended_reason']??'','attr') ?>">
</div>
</div>
<?php $clinicEnd($cl('SavePolicy')); ?>
</section>
<section id="clinic-lifecycle" class="form-section clinic-danger">
<header>
<h2>
<?= $cl('Lifecycle') ?>
</h2>
</header>
<p>
<?= esc(lang('IonAuth.clinic_delete_history_protected')) ?>
</p>
<div class="clinic-actions">
<?php $active=($clinic['status']??'inactive')==='active';$clinicForm($clinicRoute.($active?'/suspend':'/reactivate'),$cl($active?'Suspend':'Reactivate'));$clinicEnd($cl($active?'Suspend':'Reactivate')); ?>
<button class="button secondary" type="button" data-dialog="clinic-terminate">
<?= $cl('Terminate') ?>
</button>
</div>
</section>
</div>
</div>
<dialog id="clinic-admin-password" class="clinic-dialog" aria-labelledby="clinic-admin-password-title">
<header>
<h2 id="clinic-admin-password-title">
<?= esc(lang('UI.clinicAdminResetPassword')) ?>
</h2>
<button type="button" class="button secondary" data-close>
<?= $cl('Close') ?>
</button>
</header>
<?php $clinicForm($clinicRoute.'/reset-admin-password',$cl('NewPassword')); ?>
<div class="form-field">
<label for="clinic-new-password">
<?= $cl('NewPassword') ?>
</label>
<input id="clinic-new-password" type="password" name="password" required minlength="8" autocomplete="new-password">
</div>
<?php $clinicEnd($cl('ResetPassword')); ?>
</dialog>
<dialog id="clinic-admin-assign" class="clinic-dialog" aria-labelledby="clinic-admin-assign-title">
<header>
<h2 id="clinic-admin-assign-title">
<?= esc(lang('UI.'.(!empty($admin_info['is_fallback'])?'confirmDesignation':'clinicAdminModalAssignTitle'))) ?>
</h2>
<button type="button" class="button secondary" data-close>
<?= $cl('Close') ?>
</button>
</header>
<?php $clinicForm($clinicRoute.'/admin/assign',esc($clinic['name'])); ?>
<?php $candidates=$eligible_admin_users??[]; ?>
<?php if(!empty($candidates)): ?>
<div class="form-field">
<label for="clinic-admin-assign-user">
<?= esc(lang('UI.clinicAdminSelectUser')) ?>
</label>
<select id="clinic-admin-assign-user" name="user_id" required>
<option value="">
<?= esc(lang('UI.clinicAdminSelectUserPlaceholder')) ?>
</option>
<?php foreach($candidates as $eu): ?>
<option value="<?= (int)$eu['id'] ?>" <?= (!empty($admin_info['is_fallback'])&&(int)($admin_info['admin_user']['id']??0)===(int)$eu['id'])?'selected':'' ?>>
<?= esc(!empty($eu['name'])?$eu['name']:(!empty($eu['email'])?$eu['email']:'User #'.$eu['id'])) ?> (<?= esc($eu['email']??'') ?>) [<?= esc($eu['clinic_role_title']??'Staff') ?>]</option>
<?php endforeach; ?>
</select>
</div>
<?php else: ?>
<p class="form-notice">
<?= esc(lang('UI.clinicAdminNoEligibleUsers')) ?>
</p>
<?php endif; ?>
<?php $clinicEnd(esc(lang('UI.'.(!empty($admin_info['is_fallback'])?'confirmDesignation':'clinicAdminConfirmAssign'))),empty($candidates)); ?>
</dialog>
<dialog id="clinic-admin-replace" class="clinic-dialog" aria-labelledby="clinic-admin-replace-title">
<header>
<h2 id="clinic-admin-replace-title">
<?= esc(lang('UI.clinicAdminReplaceBtn')) ?>
</h2>
<button type="button" class="button secondary" data-close>
<?= $cl('Close') ?>
</button>
</header>
<?php $clinicForm($clinicRoute.'/admin/replace',esc($clinic['name'])); ?>
<input type="hidden" name="expected_admin_id" value="<?= (int)($admin_info['admin_user']['id']??0) ?>">
<?php $currentAdminId=(int)($admin_info['admin_user']['id']??0);$candidates=array_filter($eligible_admin_users??[],static fn($eu)=>(int)$eu['id']!==$currentAdminId); ?>
<?php if(!empty($candidates)): ?>
<div class="form-field">
<label for="clinic-admin-replace-user">
<?= esc(lang('UI.clinicAdminSelectUser')) ?>
</label>
<select id="clinic-admin-replace-user" name="user_id" required>
<option value="">
<?= esc(lang('UI.clinicAdminSelectUserPlaceholder')) ?>
</option>
<?php foreach($candidates as $eu): ?>
<option value="<?= (int)$eu['id'] ?>" >
<?= esc(!empty($eu['name'])?$eu['name']:(!empty($eu['email'])?$eu['email']:'User #'.$eu['id'])) ?> (<?= esc($eu['email']??'') ?>) [<?= esc($eu['clinic_role_title']??'Staff') ?>]</option>
<?php endforeach; ?>
</select>
</div>
<?php else: ?>
<p class="form-notice">
<?= esc(lang('UI.clinicAdminNoEligibleUsers')) ?>
</p>
<?php endif; ?>
<fieldset class="clinic-radio-list">
<legend>
<?= esc(lang('UI.clinicAdminPreviousActionLabel')) ?>
</legend>
<?php foreach(['demote_to_staff'=>'clinicAdminDemotePrevious','keep_role'=>'clinicAdminKeepRolePrevious','deactivate_membership'=>'clinicAdminDeactivateMembershipPrevious'] as $value=>$key): ?>
<label>
<input type="radio" name="previous_admin_action" value="<?= esc($value) ?>" <?= $value==='demote_to_staff'?'checked':'' ?>>
<span>
<?= esc(lang('UI.'.$key)) ?>
</span>
</label>
<?php endforeach; ?>
</fieldset>
<?php $clinicEnd(esc(lang('UI.clinicAdminConfirmReplace')),empty($candidates)); ?>
</dialog>
<dialog id="clinic-admin-restore" class="clinic-dialog" aria-labelledby="clinic-admin-restore-title">
<header>
<h2 id="clinic-admin-restore-title">
<?= esc(lang('UI.clinicAdminRestoreBtn')) ?>
</h2>
<button type="button" class="button secondary" data-close>
<?= $cl('Close') ?>
</button>
</header>
<?php $clinicForm($clinicRoute.'/admin/restore',esc($clinic['name'])); ?>
<input type="hidden" name="user_id" value="<?= (int)($admin_info['admin_user']['id']??0) ?>">
<input type="hidden" name="expected_designated_admin_id" value="<?= (int)($admin_info['admin_user']['id']??0) ?>">
<p>
<?= esc(lang('UI.clinicAdminRestoreHelp')) ?>
</p>
<?php if(!($admin_info['account_active']??true)): ?>
<p class="form-notice">
<?= esc(lang('UI.clinicAdminAccountDeactivatedHelp')) ?>
</p>
<a class="button secondary" href="<?= base_url('controlplane/users') ?>">
<?= esc(lang('UI.clinicAdminGoToUsers')) ?>
</a>
<?php endif; ?>
<p>
<?= esc($admin_info['admin_name']??($admin_info['admin_user']['email']??'')) ?>
</p>
<?php $clinicEnd(esc(lang('UI.clinicAdminConfirmRestore')),!($admin_info['account_active']??true)); ?>
</dialog>
<dialog id="clinic-terminate" class="clinic-dialog" aria-labelledby="clinic-terminate-title">
<header>
<h2 id="clinic-terminate-title">
<?= $cl('Terminate') ?>
</h2>
<button type="button" class="button secondary" data-close>
<?= $cl('Close') ?>
</button>
</header>
<p>
<?= $cl('TerminateNote') ?>
</p>
<div class="form-field">
<label for="clinic-terminate-name">
<?= esc($clinic['name']) ?>
</label>
<input id="clinic-terminate-name" autocomplete="off" data-clinic-name="<?= esc($clinic['name'],'attr') ?>">
</div>
<?php $clinicForm($clinicRoute.'/terminate',esc($clinic['name']));$clinicEnd($cl('Terminate'),true); ?>
</dialog>
<dialog id="clinic-final-review" class="clinic-dialog" aria-labelledby="clinic-final-title">
<h2 id="clinic-final-title">
<?= $cl('Review') ?>
</h2>
<p>
<?= esc($clinic['name']) ?>
</p>
<dl data-clinic-review-values>
</dl>
<footer>
<button class="button secondary" type="button" data-clinic-cancel>
<?= $cl('KeepEditing') ?>
</button>
<button class="button primary" type="button" data-clinic-proceed>
<?= $cl('Confirm') ?>
</button>
</footer>
</dialog>
<script id="clinic-ui" type="application/json">
<?= json_encode(['hidden'=>lang('Studio.pcHiddenPassword'),'yes'=>lang('Studio.pcYes'),'no'=>lang('Studio.pcNo')],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>
</script>
<script src="<?= base_url('assets/js/studio-platform-clinic.js') ?>?v=<?= esc(asset_version('assets/js/studio-platform-clinic.js')) ?>" defer>
</script>
<?= $this->endSection() ?>