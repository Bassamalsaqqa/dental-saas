<?php
$contextPatient = $context_patient ?? $selected_patient ?? null;
$contextName = trim((string) (($contextPatient['first_name'] ?? '') . ' ' . ($contextPatient['last_name'] ?? '')));
$contextNumber = (string) ($contextPatient['patient_id'] ?? ('#' . (int) ($contextPatient['id'] ?? 0)));
$contextPhone = (string) ($contextPatient['phone'] ?? '');
$contextGender = strtolower((string) ($contextPatient['gender'] ?? ''));
$contextToneClass = in_array($contextGender, ['female', 'f'], true)
    ? 'context-patient-badge--female'
    : (in_array($contextGender, ['male', 'm'], true) ? 'context-patient-badge--male' : 'context-patient-badge--neutral');
?>
<?php if(!empty($contextPatient['id'])): ?>
<dl class="context-patient-badge <?= esc($contextToneClass) ?>"><div><dt><?= esc(lang('UI.currentPatient')) ?></dt><dd class="context-patient-badge__identity"><span class="context-patient-badge__name"><?= esc($contextName!==''?$contextName:lang('UI.patientUnknown')) ?></span><bdi class="context-patient-badge__number"><?= esc($contextNumber) ?></bdi></dd></div><?php if($contextPhone!==''): ?><div><dt><?= esc(lang('Studio.phone')) ?></dt><dd class="context-patient-badge__phone"><bdi><?= esc($contextPhone) ?></bdi></dd></div><?php endif; ?></dl>
<?php endif; ?>
