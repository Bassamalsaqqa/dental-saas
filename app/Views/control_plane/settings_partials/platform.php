<section id="tab-platform" class="settings-content" aria-labelledby="nav-platform">
<?php $settingOpen('controlplane/settings/platform','Profile'); ?>
<div class="settings-fields settings-fields--profile">
<?php $settingField('platform_name','Name','text','required maxlength="100"'); ?>
<?php $settingField('platform_support_email','SupportEmail','email','maxlength="120"'); ?>
<?php $settingField('platform_support_phone','Phone','text','maxlength="40"'); ?>
<?php $settingField('platform_support_website','Website','url','maxlength="200"'); ?>
<?php $settingField('platform_support_whatsapp_primary','PrimaryWA','text','maxlength="40"'); ?>
<?php $settingField('platform_support_whatsapp_secondary','SecondaryWA','text','maxlength="40"'); ?>
</div>
<?php $settingClose(); ?>
</section>