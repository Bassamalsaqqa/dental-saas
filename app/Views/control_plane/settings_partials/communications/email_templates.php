<section id="tab-email-templates" class="settings-content" hidden aria-labelledby="nav-email-templates">
<div class="settings-template-facts">
<?php foreach(['total'=>'Total','active'=>'Active','draft'=>'Draft','filtered'=>'Filtered'] as $k=>$label): ?>
<span>
<?= $st($label) ?>
<strong>
<?= number_format($templateStats[$k]??0) ?>
</strong>
</span>
<?php endforeach; ?>
</div>
<form method="get" action="<?= base_url('controlplane/settings') ?>" class="studio-form settings-filter">
<input type="hidden" name="tab" value="email-templates">
<div class="form-field">
<label for="email-search">
<?= $st('Search') ?>
</label>
<input id="email-search" name="q" value="<?= esc($templateFilters['q']??'','attr') ?>">
</div>
<div class="form-field">
<label for="email-status">
<?= $st('Status') ?>
</label>
<select id="email-status" name="status">
<?php foreach([''=>'All','active'=>'Active','draft'=>'Draft'] as $value=>$key): ?>
<option value="<?= esc($value) ?>" <?= ($templateFilters['status']??'')===$value?'selected':'' ?>>
<?= $st($key) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<button class="button secondary" type="submit">
<?= $st('Apply') ?>
</button>
</form>
<div class="settings-editor-layout">
<section class="form-section settings-library">
<header>
<h2>
<?= $st('Library') ?>
</h2>
<a class="button secondary" href="<?= base_url('controlplane/settings?tab=email-templates') ?>">
<?= $st('NewTemplate') ?>
</a>
</header>
<?php if(empty($templates)): ?>
<p>
<?= $st('NoTemplates') ?>
</p>
<?php endif; ?>
<?php foreach($templates??[] as $template): ?>
<a class="settings-template-link" href="<?= base_url('controlplane/settings?tab=email-templates&template_id='.(int)$template['id']) ?>" <?= (int)($activeTemplate['id']??0)===(int)$template['id']?'aria-current="true"':'' ?>>
<strong>
<?= esc($template['name']) ?>
</strong>
<span>
<?= esc($template['slug']) ?>
</span>
</a>
<?php endforeach; ?>
</section>
<?php $editorTemplate=$activeTemplate??null;$settingOpen('controlplane/settings/email-templates'.($editorTemplate?'/'.(int)$editorTemplate['id']:''),$editorTemplate?'EditTemplate':'NewTemplate'); ?>
<div class="settings-fields">
<div class="form-field">
<label for="email-name">
<?= $st('Name') ?>
</label>
<input id="email-name" name="name" value="<?= esc($editorTemplate['name']??'','attr') ?>" required>
</div>
<div class="form-field">
<label for="email-slug">
<?= $st('Slug') ?>
</label>
<input id="email-slug" name="slug" value="<?= esc($editorTemplate['slug']??'','attr') ?>" >
</div>
<div class="form-field">
<label for="email-subject">
<?= $st('Subject') ?>
</label>
<input id="email-subject" name="subject" value="<?= esc($editorTemplate['subject']??'','attr') ?>" required>
</div>
<div class="form-field settings-wide">
<label for="email-body_html">
<?= $st('HtmlBody') ?>
</label>
<textarea id="email-body_html" name="body_html" rows="6">
<?= esc($editorTemplate['body_html']??'') ?>
</textarea>
</div>
<div class="form-field settings-wide">
<label for="email-body_text">
<?= $st('TextBody') ?>
</label>
<textarea id="email-body_text" name="body_text" rows="4">
<?= esc($editorTemplate['body_text']??'') ?>
</textarea>
</div>
<div class="form-field">
<label for="email-editor-status">
<?= $st('Status') ?>
</label>
<select id="email-editor-status" name="status">
<?php foreach(['draft'=>'Draft','active'=>'Active'] as $value=>$key): ?>
<option value="<?= esc($value) ?>" <?= ($editorTemplate['status']??'draft')===$value?'selected':'' ?>>
<?= $st($key) ?>
</option>
<?php endforeach; ?>
</select>
</div>
</div>
<?php $settingClose(); ?>
</div>
</section>