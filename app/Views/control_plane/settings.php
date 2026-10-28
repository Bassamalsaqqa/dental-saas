<?php helper('asset');include APPPATH.'Views/layouts/partials/studio_icons.php';
$settings=array_replace(['default_working_hours_start'=>'09:00','default_working_hours_end'=>'17:00','default_appointment_duration'=>30,'default_booking_chair_max'=>3,'retention_last_n'=>5,'retention_days'=>30,'control_plane_audit_retention_days'=>365],$settings??[]);
$settings=array_replace(['default_working_hours_start'=>'09:00','default_working_hours_end'=>'17:00','default_appointment_duration'=>30,'default_booking_chair_max'=>3,'retention_last_n'=>5,'retention_days'=>30,'control_plane_audit_retention_days'=>365],$settings??[]);
$st=static fn($key)=>esc(lang('Studio.ps'.$key));
$settingField=static function($name,$key,$type='text',$extra='')use($settings,$st){$value=old($name)??($settings[$name]??'');echo '<div class="form-field">
<label for="setting-'.esc($name,'attr').'">'.$st($key).'</label>
<input id="setting-'.esc($name,'attr').'" name="'.esc($name,'attr').'" type="'.esc($type,'attr').'" value="'.esc($value,'attr').'" '.$extra.'>
</div>';};
$settingOpen=static function($route,$key,$extra='')use($st){echo '<form class="studio-form settings-form" action="'.base_url($route).'" method="post" data-settings-form="true" '.$extra.'>'.csrf_field().'<section class="form-section">
<header>
<h2>'.$st($key).'</h2>
</header>';};
$settingClose=static function($key='Save')use($st){echo '<footer class="settings-save">
<button type="submit" class="button primary">'.$st($key).'</button>
</footer>
</section>
</form>';};
?>
<?= $this->extend('layouts/studio_control_plane') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-platform-settings.css') ?>?v=<?= esc(asset_version('assets/css/studio-platform-settings.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<header class="page-title-row">
<h1>
<?= $st('Title') ?>
</h1>
<a class="button secondary" href="<?= base_url('controlplane/operations') ?>">
<?= $st('Operations') ?>
</a>
</header>
<?php $errors=session('errors');if(session('error')||is_array($errors)&&$errors): ?>
<section class="form-notice form-notice--error" role="alert">
<h2>
<?= esc(lang('Studio.checkFields')) ?>
</h2>
<p>
<?= esc(session('error')??'') ?>
</p>
<ul>
<?php foreach(is_array($errors)?$errors:[] as $error): ?>
<li>
<?= esc($error) ?>
</li>
<?php endforeach; ?>
</ul>
</section>
<?php endif; ?>
<div class="platform-settings-layout">
<nav class="settings-navigation" aria-label="<?= $st('Areas') ?>">
<?php if(config('StudioThemes')->enabled): ?><a class="button secondary" href="<?= base_url('controlplane/settings/appearance') ?>"><?= esc(lang('Themes.title')) ?></a><?php endif ?>
<?php foreach(['platform'=>'Profile','system-preferences'=>'Defaults','working-hours'=>'Hours','governance'=>'Governance','email-templates'=>'Email','whatsapp-templates'=>'WhatsApp'] as $tab=>$key): ?>
<button type="button" id="nav-<?= esc($tab) ?>" data-settings-tab="<?= esc($tab) ?>" aria-controls="tab-<?= esc($tab) ?>">
<?= $st($key) ?>
</button>
<?php endforeach; ?>
<p>
<?= $st('SectionSave') ?>
</p>
</nav>
<div class="platform-settings-content">
<?php foreach(['platform','clinic_defaults','governance','communications'] as $partial)include APPPATH.'Views/control_plane/settings_partials/'.$partial.'.php'; ?>
</div>
</div>
<dialog id="settings-confirm" class="settings-dialog" aria-labelledby="settings-confirm-title">
<h2 id="settings-confirm-title">
<?= $st('Review') ?>
</h2>
<p data-settings-message>
</p>
<dl data-settings-review>
</dl>
<div class="settings-save">
<button type="button" class="button secondary" data-settings-cancel>
<?= $st('Cancel') ?>
</button>
<button type="button" class="button primary" data-settings-proceed>
<?= $st('Confirm') ?>
</button>
</div>
</dialog>
<p id="settings-feedback" class="form-notice" role="status" hidden>
</p>
<script id="settings-ui" type="application/json">
<?= json_encode(['review'=>lang('Studio.psReview'),'confirm'=>lang('Studio.psConfirm'),'saving'=>lang('Studio.psSaving'),'secret'=>lang('Studio.psSecretHidden'),'pruneExports'=>lang('Studio.psPruneExportWarning'),'pruneAudits'=>lang('Studio.psPruneAuditWarning'),'failed'=>lang('Studio.psFailed'),'exportsUrl'=>base_url('controlplane/settings/retention/prune'),'auditsUrl'=>base_url('controlplane/settings/audit-retention/prune')],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>
</script>
<script src="<?= base_url('assets/js/studio-platform-settings.js') ?>?v=<?= esc(asset_version('assets/js/studio-platform-settings.js')) ?>" defer>
</script>
<?= $this->endSection() ?>