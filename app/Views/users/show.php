<?php helper('asset');include APPPATH.'Views/layouts/partials/studio_icons.php';$viewedUser=$viewed_user;$name=trim(($viewedUser->first_name??'').' '.($viewedUser->last_name??''))?:lang('UserRole.user');$membershipActive=($membership_status??$viewedUser->membership_status??'active')==='active';$globallyDisabled=(int)($viewedUser->active??0)!==1;$isSelf=!empty($is_self)||(int)$viewedUser->id===(int)($current_user->id??0);$canEditUsers=has_permission('users','edit');$canDeleteUsers=has_permission('users','delete');$canViewFinance=has_permission('finance','view');$canEditFinance=has_permission('finance','edit');$providerSummary=$provider_finance_summary??[];$providerRows=$provider_payout_rows??[];$paymentRows=$provider_payment_rows??[];$providerFormat=static fn($key)=>\App\Services\ProviderCurrencySummary::format($providerSummary,$key);$nativeMoney=static fn($amount,$currency)=>\App\Services\ProviderCurrencySummary::native($amount,$currency); ?>
<?= $this->extend('layouts/studio') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-management.css') ?>?v=<?= esc(asset_version('assets/css/studio-management.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<header class="page-title-row">
<div>
<h1>
<?= esc($name) ?>
</h1>
</div>
<div class="management-actions">
<a class="button secondary" href="<?= esc(workflow_back('users',$return_to??null)) ?>">
<?= esc(lang('UserRole.backToUsers')) ?>
</a>
<?php if($canEditUsers): ?>
<a class="button primary" href="<?= esc(workflow_context_url('users/'.(int)$viewedUser->id.'/edit')) ?>">
<?= esc(lang('UserRole.editUser')) ?>
</a>
<?php endif; ?>
</div>
</header>
<section class="staff-overview form-section">
<div class="staff-profile-identity">
<span class="staff-profile-avatar">
<?= esc($studioInitials($name)) ?>
</span>
<div>
<strong>
<?= esc($viewedUser->email) ?>
</strong>
<p dir="ltr">
<?= esc($viewedUser->phone??'') ?>
</p>
<div class="account-groups">
<?php foreach(($viewed_user_roles??[]) as $role): ?>
<span>
<?= esc(is_array($role)?($role['name']??''):($role->name??'')) ?>
</span>
<?php endforeach; ?>
</div>
</div>
</div>
<div class="staff-profile-status">
<span class="staff-badge <?= $membershipActive?'':'inactive' ?>">
<?= esc(lang('UserRole.'.($membershipActive?'activeMembership':'inactiveMembership'))) ?>
</span>
<?php if($globallyDisabled): ?>
<span class="staff-badge inactive">
<?= esc(lang('UserRole.globallyDisabled')) ?>
</span>
<?php endif; ?>
<span>#<?= (int)$viewedUser->id ?>
</span>
</div>
</section>
<div class="staff-profile-layout">
<section class="form-section">
<header>
<h2>
<?= esc(lang('UserRole.userInformation')) ?>
</h2>
</header>
<dl class="account-facts">
<?php $created=$viewedUser->created_on??$viewedUser->created_at??null;$lastLogin=$viewedUser->last_login??null;foreach([['department',($departments??[])[$viewedUser->department??'']??($viewedUser->department??lang('UserRole.na'))],['hireDateRequired',!empty($viewedUser->hire_date)?formatDate((string)$viewedUser->hire_date):lang('UserRole.na')],['createdColon',$created?formatDateTime($created):lang('UserRole.na')],['lastLoginColon',$lastLogin?formatDateTime($lastLogin):lang('UserRole.never')],['lastIpColon',$viewedUser->ip_address??lang('UserRole.na')]] as [$label,$value]): ?>
<div>
<dt>
<?= esc(lang('UserRole.'.$label)) ?>
</dt>
<dd>
<?= esc($value) ?>
</dd>
</div>
<?php endforeach; ?>
</dl>
</section>
<section class="form-section">
<header>
<h2>
<?= esc(lang('UserRole.roleAndPermissions')) ?>
</h2>
</header>
<div class="staff-profile-tools">
<?php if($canEditUsers): ?>
<button class="button secondary" type="button" data-dialog="staff-role-dialog">
<?= esc(lang('Studio.editRole')) ?>
</button>
<button class="button secondary" type="button" data-management-action data-url="<?= base_url('users/'.(int)$viewedUser->id.'/toggle-status') ?>" data-method="POST" data-confirm="<?= esc(lang('UserRole.confirmToggleUserStatus'),'attr') ?>" <?= $globallyDisabled&&!$membershipActive?'disabled':'' ?>>
<?= esc(lang('UserRole.'.($membershipActive?'deactivate':'activate'))) ?>
</button>
<?php if($isSelf): ?>
<a class="button secondary" href="<?= esc(workflow_context_url('users/'.(int)$viewedUser->id.'/change-password')) ?>">
<?= esc(lang('UserRole.changePassword')) ?>
</a>
<?php else: ?>
<p>
<?= esc(lang('UserRole.cannotResetOtherUserPassword')) ?>
</p>
<?php endif; ?>
<?php endif; ?>
<?php if($canDeleteUsers): ?>
<button class="button destructive" type="button" data-management-action data-url="<?= base_url('users/'.(int)$viewedUser->id) ?>" data-method="DELETE" data-confirm="<?= esc(lang('UserRole.confirmRemoveClinicAccess'),'attr') ?>">
<?= esc(lang('UserRole.removeClinicAccess')) ?>
</button>
<?php endif; ?>
</div>
</section>
</div>
<?php if($canViewFinance): ?>
<section class="staff-provider-section">
<header class="form-section staff-provider-heading">
<div>
<h2>
<?= esc(lang('Finance.clinicalProviderCompensation')) ?>
</h2>
<p>
<?= esc(lang('Finance.clinicalProviderCompensationHelp')) ?>
</p>
</div>
<a class="button secondary" href="<?= base_url('finance/payouts') ?>">
<?= esc(lang('Finance.openPayoutLedger')) ?>
</a>
</header>
<div class="staff-provider-totals">
<?php foreach([['provider_share_total','providerShare'],['provider_paid_total','paidToProvider'],['provider_balance_total','providerPayable']] as [$key,$label]): ?>
<div>
<span>
<?= esc(lang('Finance.'.$label)) ?>
</span>
<strong dir="ltr">
<?= esc($providerFormat($key)) ?>
</strong>
</div>
<?php endforeach; ?>
<div>
<span>
<?= esc(lang('Finance.providerPayoutCount')) ?>
</span>
<strong>
<?= (int)($providerSummary['payout_count']??0) ?>
</strong>
</div>
</div>
<p class="form-notice">
<?= esc(lang('Finance.providerCurrencyWarning')) ?>
</p>
<section class="staff-directory">
<h3 class="staff-allocation-title">
<?= esc(lang('Studio.recentProviderAllocations')) ?>
</h3>
<div class="platform-table-scroll">
<table class="platform-table">
<thead>
<tr>
<?php foreach(['transactionId','patient','date','paidBalance','lastPaymentDate','status'] as $key): ?>
<th scope="col">
<?= esc(lang('Finance.'.$key)) ?>
</th>
<?php endforeach; ?>
</tr>
</thead>
<tbody>
<?php foreach($providerRows as $row):$rowStatus=strtolower((string)($row['status']??'unknown'));$canSettle=$canEditFinance&&!in_array($rowStatus,['paid','void'],true);$financeUrl=workflow_context_url('finance/'.(int)($row['finance_id']??0),'users/'.(int)$viewedUser->id); ?>
<tr data-platform-row>
<td data-label="<?= esc(lang('Finance.transactionId'),'attr') ?>">
<a class="platform-primary" href="<?= esc($financeUrl,'attr') ?>">
<?= esc($row['transaction_id']??'-') ?>
</a>
<?php if((int)($row['clinical_line_count']??0)>0): ?>
<small>
<?= esc(lang('Finance.clinicalTraceLines')) ?>: <?= (int)$row['clinical_line_count'] ?>
</small>
<small>
<?= esc(lang('Finance.clinicalTraceTreatments')) ?>: <?= (int)($row['treatment_count']??0) ?> / <?= esc(lang('Finance.clinicalTraceExaminations')) ?>: <?= (int)($row['examination_count']??0) ?>
</small>
<small>
<?= esc($row['clinical_procedures']??'') ?>
</small>
<?php endif; ?>
</td>
<td data-label="<?= esc(lang('Finance.patient'),'attr') ?>">
<?= esc($row['patient_name']??lang('Finance.notAvailable')) ?>
</td>
<td data-label="<?= esc(lang('Finance.date'),'attr') ?>">
<?= !empty($row['business_date'])?esc(formatDate((string)$row['business_date'])):'—' ?>
</td>
<td data-label="<?= esc(lang('Finance.paidBalance'),'attr') ?>">
<strong dir="ltr">
<?= esc($nativeMoney($row['provider_share_amount']??0,$row['currency']??null)) ?>
</strong>
<small>
<?= esc(lang('Finance.paid')) ?> <b dir="ltr">
<?= esc($nativeMoney($row['provider_paid_amount']??0,$row['currency']??null)) ?>
</b>
</small>
<small>
<?= esc(lang('Finance.balance')) ?> <b dir="ltr">
<?= esc($nativeMoney($row['provider_balance_due']??0,$row['currency']??null)) ?>
</b>
</small>
</td>
<td data-label="<?= esc(lang('Finance.lastPaymentDate'),'attr') ?>">
<?= !empty($row['last_paid_date'])?esc(formatDate((string)$row['last_paid_date'])):esc(lang('UserRole.never')) ?>
</td>
<td data-label="<?= esc(lang('Finance.status'),'attr') ?>">
<span class="staff-badge <?= $rowStatus==='void'?'inactive':'' ?>">
<?= esc(lang('Finance.'.$rowStatus)) ?>
</span>
<div class="platform-table-actions">
<?php if($canSettle): ?>
<button class="button secondary" type="button" data-staff-payout data-id="<?= (int)$row['id'] ?>" data-max="<?= esc(number_format((float)($row['provider_balance_due']??0),2,'.',''),'attr') ?>" data-currency="<?= esc(\App\Services\ProviderCurrencySummary::currency($row['currency']??null),'attr') ?>">
<?= esc(lang('Finance.recordPaymentFromLedger')) ?>
</button>
<button class="button secondary" type="button" data-management-action data-method="POST" data-encoding="form" data-payment-method="cash" data-url="<?= base_url('finance/payouts/'.(int)$row['id'].'/mark-paid') ?>" data-confirm="<?= esc(lang('Finance.confirmMarkProviderPayoutFullyPaid'),'attr') ?>">
<?= esc(lang('Finance.markFullyPaid')) ?>
</button>
<?php endif; ?>
</div>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php if(!$providerRows): ?>
<p class="staff-empty">
<?= esc(lang('Finance.noProviderPayoutsFound')) ?>
</p>
<?php endif; ?>
</section>
<section class="form-section">
<header>
<h2>
<?= esc(lang('Studio.recentProviderPayments')) ?>
</h2>
</header>
<div class="staff-payment-history">
<?php foreach($paymentRows as $payment): ?>
<a href="<?= esc(workflow_context_url('finance/'.(int)($payment['finance_id']??0),'users/'.(int)$viewedUser->id),'attr') ?>">
<span>
<strong>
<?= esc($payment['transaction_id']??'-') ?>
</strong>
<small>
<?= esc($payment['patient_name']??'') ?>
</small>
<small>
<?= !empty($payment['paid_date'])?esc(formatDate((string)$payment['paid_date'])):esc(lang('Finance.notAvailable')) ?>
</small>
</span>
<strong dir="ltr">
<?= esc($nativeMoney($payment['amount']??0,$payment['currency']??null)) ?>
</strong>
</a>
<?php endforeach; ?>
<?php if(!$paymentRows): ?>
<p>
<?= esc(lang('Finance.noPaymentsYet')) ?>
</p>
<?php endif; ?>
</div>
</section>
</section>
<?php endif; ?>
<?php if($canEditUsers): ?>
<dialog id="staff-role-dialog" class="help-dialog" aria-labelledby="staff-role-title">
<header>
<h2 id="staff-role-title">
<?= esc(lang('Studio.editRole')) ?>
</h2>
<button class="icon-button" type="button" data-close aria-label="<?= esc(lang('Studio.close')) ?>">
<?= $studioIcon('close') ?>
</button>
</header>
<form class="staff-dialog-form" data-staff-role action="<?= base_url('users/'.(int)$viewedUser->id.'/inline-update-role') ?>" method="post">
<?= csrf_field() ?>
<div class="form-field">
<label for="inlineRoleSelect">
<?= esc(lang('UserRole.roleRequired')) ?>
</label>
<select id="inlineRoleSelect" name="role_id" required>
<option value="">
<?= esc(lang('UserRole.selectRole')) ?>
</option>
<?php foreach(($assignable_roles??[]) as $role): ?>
<option value="<?= (int)$role['id'] ?>" <?= (int)($current_role_id??0)===(int)$role['id']?'selected':'' ?>>
<?= esc($role['name']) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<p class="field-error" role="alert" data-staff-error hidden>
</p>
<button class="button primary" type="submit">
<?= esc(lang('UI.settingsSave')) ?>
</button>
</form>
</dialog>
<?php endif; ?>
<?php if($canEditFinance): ?>
<dialog id="staff-payout-dialog" class="help-dialog" aria-labelledby="staff-payout-title" data-base="<?= base_url('finance/payouts/') ?>" data-max-label="<?= esc(lang('Finance.maximumPayable'),'attr') ?>">
<header>
<h2 id="staff-payout-title">
<?= esc(lang('Finance.recordPaymentFromLedger')) ?>
</h2>
<button class="icon-button" type="button" data-close aria-label="<?= esc(lang('Studio.close')) ?>">
<?= $studioIcon('close') ?>
</button>
</header>
<form class="staff-dialog-form" data-staff-payout-form>
<div class="form-field">
<label for="payoutAmountInput">
<?= esc(lang('Finance.amount')) ?>
</label>
<input id="payoutAmountInput" name="amount" type="number" step="0.01" min="0.01" required dir="ltr">
<small id="payoutAmountHelp">
</small>
</div>
<div class="form-field">
<label for="payoutMethodInput">
<?= esc(lang('Finance.paymentMethod')) ?>
</label>
<select id="payoutMethodInput" name="payment_method">
<?php foreach(['cash'=>'cash','card'=>'card','bank_transfer'=>'bankTransfer','check'=>'check','other'=>'other'] as $value=>$label): ?>
<option value="<?= $value ?>">
<?= esc(lang('Finance.'.$label)) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="form-field" id="payoutLiquidityAccountField" hidden>
<label for="payoutLiquidityAccountInput">
<?= esc(lang('Finance.paidFromAccount')) ?>
</label>
<select id="payoutLiquidityAccountInput" name="liquidity_account">
<?php foreach(['bank','undeposited_checks'] as $account): ?>
<option value="<?= $account ?>">
<?= esc(lang('Finance.liquidityAccount.'.$account)) ?>
</option>
<?php endforeach; ?>
</select>
<small>
<?= esc(lang('Finance.checkOutgoingAccountHelp')) ?>
</small>
</div>
<div class="form-field">
<label for="payoutDateInput">
<?= esc(lang('Finance.paidDate')) ?>
</label>
<input id="payoutDateInput" name="paid_date" type="date" required>
</div>
<div class="form-field">
<label for="payoutNotesInput">
<?= esc(lang('Finance.notes')) ?>
</label>
<textarea id="payoutNotesInput" name="notes" rows="2">
</textarea>
</div>
<p class="field-error" data-staff-error role="alert" hidden>
</p>
<button type="submit" class="button primary">
<?= esc(lang('Finance.recordPaymentFromLedger')) ?>
</button>
</form>
</dialog>
<?php endif; ?>
<?= view('users/partials/studio_action_dialog') ?>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/studio-management.js') ?>?v=<?= esc(asset_version('assets/js/studio-management.js')) ?>" defer>
</script>
<script src="<?= base_url('assets/js/studio-staff.js') ?>?v=<?= esc(asset_version('assets/js/studio-staff.js')) ?>" defer>
</script>
<?= $this->endSection() ?>
