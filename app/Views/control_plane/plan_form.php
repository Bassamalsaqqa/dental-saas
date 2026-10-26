<?php helper('asset');$plan=$plan??[];$limits=$limits??[];$features=$features??[];$mode=$mode??'create';$actionUrl=$mode==='edit'&&!empty($plan['id'])?base_url('controlplane/plans/'.(int)$plan['id']):base_url('controlplane/plans'); ?>
<?= $this->extend('layouts/studio_control_plane') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-management.css') ?>?v=<?= esc(asset_version('assets/css/studio-management.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<header class="page-title-row">
<div>
<h1>
<?= esc(lang('Studio.'.($mode==='edit'?'planEdit':'planCreate'))) ?>
</h1>
</div>
<a class="button secondary" href="<?= base_url('controlplane/plans') ?>">
<?= esc(lang('UI.cpPlans')) ?>
</a>
</header>
<?php if(session('error')||session('errors')): ?>
<section class="form-notice form-notice--error" role="alert">
<h2>
<?= esc(lang('Studio.checkFields')) ?>
</h2>
<p>
<?= esc(strip_tags((string)session('error'))) ?>
</p>
<?php foreach((is_array(session('errors'))?session('errors'):[]) as $error): ?>
<p>
<?= esc($error) ?>
</p>
<?php endforeach; ?>
</section>
<?php endif; ?>
<form action="<?= esc($actionUrl,'attr') ?>" method="post" class="studio-form" id="platform-plan-form">
<?= csrf_field() ?>
<section class="form-section">
<header>
<h2>
<?= esc(lang('Studio.planIdentity')) ?>
</h2>
</header>
<div class="platform-plan-identity">
<div class="form-field">
<label for="plan-name">
<?= esc(lang('Studio.planName')) ?>
</label>
<input id="plan-name" name="name" type="text" value="<?= esc(old('name',$plan['name']??''),'attr') ?>" required>
</div>
<div class="form-field">
<label for="plan-price">
<?= esc(lang('Studio.planPrice')) ?>
</label>
<input id="plan-price" name="price" type="number" min="0" step="0.01" value="<?= esc(old('price',$limits['price']??0),'attr') ?>" dir="ltr">
</div>
<div class="form-field">
<label for="plan-duration">
<?= esc(lang('Studio.planDuration')) ?>
</label>
<select id="plan-duration" name="duration">
<?php foreach(['monthly','yearly','lifetime'] as $duration): ?>
<option value="<?= $duration ?>" <?= old('duration',$limits['duration']??'monthly')===$duration?'selected':'' ?>>
<?= esc(lang('Studio.'.$duration)) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="form-field">
<label for="plan-status">
<?= esc(lang('UserRole.status')) ?>
</label>
<select id="plan-status" name="status">
<?php foreach(['active','inactive'] as $status): ?>
<option value="<?= $status ?>" <?= old('status',$plan['status']??'active')===$status?'selected':'' ?>>
<?= esc(lang('UserRole.'.$status)) ?>
</option>
<?php endforeach; ?>
</select>
</div>
</div>
<div class="form-field role-description">
<label for="plan-description">
<?= esc(lang('Studio.planDescription')) ?>
</label>
<textarea id="plan-description" name="description" rows="2">
<?= esc(old('description',$limits['description']??'')) ?>
</textarea>
</div>
</section>
<section class="form-section">
<header>
<h2>
<?= esc(lang('Studio.planLimits')) ?>
</h2>
<p>
<?= esc(lang('Studio.planUnlimited')) ?>
</p>
</header>
<div class="platform-plan-limits">
<?php foreach([['patient_limit','patients_active_max','planPatients',-1],['user_seats','users_max','planSeats',-1],['storage_gb','storage_gb','planStorage',-1],['attachments_max_file_mb','attachments_max_file_mb','planAttachmentSize',5],['trial_days','trial_days','planTrial',0],['export_quota','exports','planExports',-1],['email_quota','notifications_email','planEmail',-1]] as [$name,$key,$label,$default]): ?>
<div class="form-field">
<label for="plan-<?= $name ?>">
<?= esc(lang('Studio.'.$label)) ?>
</label>
<input id="plan-<?= $name ?>" name="<?= $name ?>" type="number" value="<?= esc(old($name,$limits[$key]??$default),'attr') ?>" <?= $name==='trial_days'?'min="0"':($name==='attachments_max_file_mb'?'min="-1" step="1"':'') ?> dir="ltr">
</div>
<?php endforeach; ?>
</div>
</section>
<section class="form-section">
<header>
<h2>
<?= esc(lang('Studio.planFeatures')) ?>
</h2>
</header>
<div class="permission-grid">
<?php $nestedFeatures=[['features[finance_reports][enabled]','finance_reports',!empty($features['finance_reports']['enabled'])||(empty($features['finance_reports'])&&!empty($features['reports']))],['features[exports][enabled]','exports',!empty($features['exports']['enabled'])],['features[notifications][email][enabled]','email',!empty($features['notifications']['email']['enabled'])],['features[notifications][whatsapp][enabled]','whatsapp',!empty($features['notifications']['whatsapp']['enabled'])],['features[attachments][enabled]','attachments',!empty($features['attachments']['enabled'])],['features[contracts][enabled]','contracts',!empty($features['contracts']['enabled'])],['features[contracts][signature][enabled]','signature',!empty($features['contracts']['signature']['enabled'])],['features[kiosk][enabled]','kiosk',!empty($features['kiosk']['enabled'])]];foreach($nestedFeatures as [$name,$key,$checked]): ?>
<label class="permission-choice">
<input type="checkbox" name="<?= $name ?>" <?= $checked?'checked':'' ?>>
<span>
<strong>
<?= esc(lang('Studio.planFeature_'.$key)) ?>
</strong>
</span>
</label>
<?php endforeach; ?>
<?php foreach(['appointments','finance','inventory','reports','email_templates','audit_exports','chairside_notes','claims_hub'] as $key): ?>
<label class="permission-choice">
<input type="checkbox" name="features[<?= $key ?>]" <?= !empty($features[$key])?'checked':'' ?>>
<span>
<strong>
<?= esc(lang('Studio.planFeature_'.$key)) ?>
</strong>
</span>
</label>
<?php endforeach; ?>
</div>
</section>
<footer class="form-actions">
<a class="button secondary" href="<?= base_url('controlplane/plans') ?>">
<?= esc(lang('UI.cancel')) ?>
</a>
<button type="submit" class="button primary">
<?= esc(lang('Studio.'.($mode==='edit'?'planEdit':'planCreate'))) ?>
</button>
</footer>
</form>
<?= $this->endSection() ?>
