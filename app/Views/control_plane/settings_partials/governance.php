<section id="tab-governance" class="settings-content" hidden aria-labelledby="nav-governance">
<?php $settingOpen('controlplane/settings/retention','Exports');$retentionMode=$settings['retention_mode']??'latest'; ?>
<p class="settings-context">
<?= $st('RetentionNote') ?>
</p>
<fieldset class="settings-retention">
<legend>
<?= $st('Policy') ?>
</legend>
<?php foreach(['latest'=>'Latest','keep_last_n'=>'LastN','keep_x_days'=>'LastDays'] as $value=>$key): ?>
<label>
<input type="radio" name="retention_mode" value="<?= esc($value) ?>" <?= $retentionMode===$value?'checked':'' ?>>
<span>
<?= $st($key) ?>
</span>
</label>
<?php endforeach; ?>
</fieldset>
<div class="settings-fields">
<div id="retention-last-n" <?= $retentionMode!=='keep_last_n'?'hidden':'' ?>>
<?php $settingField('retention_last_n','Count','number','min="1" max="100"'); ?>
</div>
<div id="retention-days" <?= $retentionMode!=='keep_x_days'?'hidden':'' ?>>
<?php $settingField('retention_days','DayCount','number','min="1" max="3650"'); ?>
</div>
</div>
<?php $settingClose(); ?>
<?php $settingOpen('controlplane/settings/audit-retention','Audits');$settingField('control_plane_audit_retention_days','AuditDays','number','min="30" max="3650"');$settingClose(); ?>
<section class="form-section settings-danger">
<header>
<h2>
<?= $st('Maintenance') ?>
</h2>
</header>
<p>
<?= $st('PruneNote') ?>
</p>
<div class="settings-save">
<button type="button" class="button secondary" data-settings-prune="exports">
<?= $st('PruneExports') ?>
</button>
<button type="button" class="button secondary" data-settings-prune="audits">
<?= $st('PruneAudits') ?>
</button>
</div>
</section>
<section class="form-section">
<header>
<h2>
<?= $st('Features') ?>
</h2>
</header>
<p>
<?= $st('FeatureNote') ?>
</p>
<a class="button secondary" href="<?= base_url('controlplane/plans') ?>">
<?= $st('ManagePlans') ?>
</a>
</section>
</section>