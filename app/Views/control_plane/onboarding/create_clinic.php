<?php helper('asset');include APPPATH.'Views/layouts/partials/studio_icons.php'; ?>
<?= $this->extend('layouts/studio_control_plane') ?>
<?= $this->section('styles') ?><link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>"><link rel="stylesheet" href="<?= base_url('assets/css/studio-platform-onboarding.css') ?>?v=<?= esc(asset_version('assets/css/studio-platform-onboarding.css')) ?>"><?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php $plans=is_array($plans??null)?$plans:[];$errors=is_array(session('errors'))?session('errors'):[]; ?>
<header class="page-title-row">
<h1>
<?= esc(lang('Studio.platformCreateClinic')) ?>
</h1>
<a class="button secondary" href="<?= base_url('controlplane/clinics') ?>">
<?= esc(lang('Studio.back')) ?>
</a>
</header>
<?php if($errors||session('error')): ?>
<section class="form-notice form-notice--error" role="alert">
<h2>
<?= esc(lang('Studio.checkFields')) ?>
</h2>
<p>
<?= esc(session('error')??'') ?>
</p>
<ul>
<?php foreach($errors as $error): ?>
<li>
<?= esc($error) ?>
</li>
<?php endforeach; ?>
</ul>
</section>
<?php endif; ?>
<form class="studio-form platform-onboarding" id="platform-onboarding" action="<?= base_url('controlplane/onboarding/clinic/create') ?>" method="post" data-unsaved-form>
<?= csrf_field() ?>
<div class="platform-onboarding-main">
<section class="form-section">
<header>
<h2>
<?= esc(lang('Studio.platformClinicIdentity')) ?>
</h2>
</header>
<div class="form-field">
<label for="onboarding-clinic">
<?= esc(lang('Studio.platformClinicName')) ?> *</label>
<input id="onboarding-clinic" name="clinic_name" value="<?= esc(old('clinic_name'),'attr') ?>" required>
</div>
</section>
<section class="form-section">
<header>
<h2>
<?= esc(lang('Studio.platformInitialAdmin')) ?>
</h2>
</header>
<div class="platform-onboarding-admin">
<div class="form-field">
<label for="onboarding-admin">
<?= esc(lang('Studio.platformAdminName')) ?> *</label>
<input id="onboarding-admin" name="admin_name" required value="<?= esc(old('admin_name'),'attr') ?>">
</div>
<div class="form-field">
<label for="onboarding-email">
<?= esc(lang('Studio.platformEmail')) ?> *</label>
<input id="onboarding-email" name="admin_email" type="email" required value="<?= esc(old('admin_email'),'attr') ?>" data-user-email-check data-password-targets="#admin_password" data-existing-note-target="#onboarding-email-exists-note">
</div>
<div class="form-field platform-onboarding-password">
<label for="admin_password">
<?= esc(lang('UserRole.password')) ?> *</label>
<input id="admin_password" name="admin_password" type="password" minlength="8" required autocomplete="new-password">
<small>
<?= esc(lang('Studio.platformInitialPasswordHelp')) ?>
</small>
</div>
<p id="onboarding-email-exists-note" class="platform-onboarding-account-note hidden" role="status">
<span data-note-text>
</span>
</p>
</div>
</section>
<section class="form-section">
<header>
<h2>
<?= esc(lang('Studio.platformStartingPlan')) ?>
</h2>
</header>
<?php if(!$plans): ?>
<p class="form-notice form-notice--error">
<?= esc(lang('Studio.platformNoActivePlans')) ?>
</p>
<?php endif; ?>
<div class="platform-onboarding-plans">
<?php foreach($plans as $plan):$limits=json_decode($plan['limits_json']??'{}',true)??[];$duration=(string)($limits['duration']??''); ?>
<label class="platform-onboarding-plan">
<input type="radio" name="plan_id" value="<?= (int)$plan['id'] ?>" <?= (int)old('plan_id')===(int)$plan['id']?'checked':'' ?> required>
<span class="platform-onboarding-plan-body">
<span class="platform-onboarding-plan-heading">
<strong>
<?= esc($plan['name']) ?>
</strong>
<span>
<?= isset($limits['price'])?'$'.esc($limits['price']):esc(lang('Studio.platformCustomPrice')) ?>
<?= $duration?' · '.esc(in_array($duration,['monthly','yearly'],true)?lang('Studio.'.$duration):$duration):'' ?>
</span>
</span>
<span class="platform-onboarding-limits">
<?php foreach(['patients_active_max'=>'platformActivePatientLimit','users_max'=>'platformUserLimit','storage_gb'=>'platformStorageGb'] as $key=>$label): ?>
<span>
<?= esc(lang('Studio.'.$label)) ?>
<strong>
<?= esc($limits[$key]??'0') ?>
</strong>
</span>
<?php endforeach; ?>
</span>
</span>
</label>
<?php endforeach; ?>
</div>
</section>
</div>
<aside class="form-section platform-onboarding-finish">
<h2>
<?= esc(lang('Studio.platformCreateWorkspace')) ?>
</h2>
<p>
<?= esc(lang('Studio.platformProvisionHelp')) ?>
</p>
<dl>
<div>
<dt>
<?= esc(lang('Studio.platformClinic')) ?>
</dt>
<dd data-onboarding-review="clinic_name">—</dd>
</div>
<div>
<dt>
<?= esc(lang('Studio.platformInitialAdmin')) ?>
</dt>
<dd data-onboarding-review="admin_email">—</dd>
</div>
<div>
<dt>
<?= esc(lang('Studio.planName')) ?>
</dt>
<dd data-onboarding-plan>—</dd>
</div>
</dl>
<button class="button primary" type="submit">
<?= esc(lang('Studio.platformCreateClinic')) ?>
</button>
<a class="button secondary" href="<?= base_url('controlplane/clinics') ?>" data-form-cancel>
<?= esc(lang('Studio.cancel')) ?>
</a>
<p class="platform-onboarding-manual">
<?= esc(lang('Studio.platformWelcomeManual')) ?>
</p>
</aside>
</form>
<dialog id="form-discard-dialog" class="help-dialog" aria-labelledby="discard-title">
<header>
<h2 id="discard-title">
<?= esc(lang('Studio.discardTitle')) ?>
</h2>
<button class="icon-button" type="button" data-close aria-label="<?= esc(lang('Studio.close'),'attr') ?>">
<?= $studioIcon('close') ?>
</button>
</header>
<footer class="platform-onboarding-dialog-actions">
<button class="button secondary" type="button" data-discard-stay>
<?= esc(lang('Studio.keepEditing')) ?>
</button>
<button class="button primary" type="button" data-discard-confirm>
<?= esc(lang('Studio.discardChanges')) ?>
</button>
</footer>
</dialog>
<?= view('components/email_check_script',['emailCheckUrl'=>base_url('controlplane/onboarding/check-email'),'allowExistingLink'=>true]) ?>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
(()=>{const form=document.getElementById('platform-onboarding');const update=()=>{for(const label of form.querySelectorAll('[data-onboarding-review]'))label.textContent=form.elements[label.dataset.onboardingReview].value.trim()||'—';const plan=form.querySelector('[name=plan_id]:checked');form.querySelector('[data-onboarding-plan]').textContent=plan?.closest('label').querySelector('strong')?.textContent||'—';};form.addEventListener('input',update);form.addEventListener('change',update);update();})();
</script>
<script src="<?= base_url('assets/js/studio-forms.js') ?>?v=<?= esc(asset_version('assets/js/studio-forms.js')) ?>" defer>
</script>
<?= $this->endSection() ?>
