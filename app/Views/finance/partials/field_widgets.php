<?php $fieldConfig=['value0'=>html_entity_decode((string)(esc(settings()->get('date_format', 'd/m/Y'))),ENT_QUOTES,'UTF-8')]; ?>
<script type="application/json" id="studio-finance-fields-config"><?= json_encode($fieldConfig,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?></script>
<script src="<?= base_url('assets/js/studio-finance-fields.js') ?>?v=<?= esc(asset_version('assets/js/studio-finance-fields.js')) ?>" defer></script>
