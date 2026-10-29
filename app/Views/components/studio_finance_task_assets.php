<?php helper('asset');include APPPATH.'Views/components/studio_csrf_runtime.php'; ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-finance-task.css') ?>?v=<?= esc(asset_version('assets/css/studio-finance-task.css')) ?>">
<?php $financeTaskLabels=[];foreach(['financeDiscardTitle','financeDiscardHelp','financePending','financeUnconfirmed','financeCheckHistory','financeDenied','financeRequestFailed','keepEditing','discardChanges'] as $key)$financeTaskLabels[$key]=lang('Studio.'.$key); ?>
<script type="application/json" id="finance-task-config"><?= json_encode($financeTaskLabels,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?></script>
<script src="<?= base_url('assets/js/studio-finance-task.js') ?>?v=<?= esc(asset_version('assets/js/studio-finance-task.js')) ?>" defer></script>
