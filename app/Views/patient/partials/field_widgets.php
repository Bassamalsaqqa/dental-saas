<?php $fieldConfig=['value0'=>html_entity_decode((string)(esc(settings()->get('date_format', 'd/m/Y'))),ENT_QUOTES,'UTF-8'),'value1'=>html_entity_decode((string)(esc(settings()->get('time_format', '12'))),ENT_QUOTES,'UTF-8')]; ?>
<script type="application/json" id="studio-patient-fields-config"><?= json_encode($fieldConfig,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?></script>
<script src="<?= base_url('assets/js/studio-patient-fields.js') ?>?v=<?= esc(asset_version('assets/js/studio-patient-fields.js')) ?>" defer></script>
