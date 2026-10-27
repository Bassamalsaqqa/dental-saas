<?php
$tr = static fn(string $key, array $params = []) => lang('Examination.' . $key, $params);
$validation = $validation ?? session()->getFlashdata('validation');
$contextual = !empty($contextual);
$selectedPatient = $selected_patient ?? null;
$selectedPatientGender = strtolower((string) ($selectedPatient['gender'] ?? ''));
$idPrefix = isset($id_prefix) && $id_prefix !== '' ? preg_replace('/[^A-Za-z0-9_-]/', '', (string) $id_prefix) : '';
$fieldId = static function (string $id) use ($idPrefix): string {
    return $idPrefix !== '' ? $idPrefix . '_' . $id : $id;
};
$optionalFieldKeys = [
    'dental_history', 'clinical_findings', 'diagnosis', 'examination_notes',
    'health_update_medical_changes', 'health_update_medication_changes',
    'health_update_allergies_reactions', 'health_update_pregnancy',
    'health_update_current_symptoms', 'health_update_details',
];
$optionalEnabledByOld = false;
foreach ($optionalFieldKeys as $fieldKey) {
    if (old($fieldKey) !== null && old($fieldKey) !== '') {
        $optionalEnabledByOld = true;
        break;
    }
}
$healthToggleChecked = static function (string $key): bool {
    return in_array(old($key), ['1', 1, true, 'true', 'on', 'yes'], true);
};
$inputClass = 'context-field-control';
$anyHealthToggleChecked = $healthToggleChecked('health_update_medical_changes')
    || $healthToggleChecked('health_update_medication_changes')
    || $healthToggleChecked('health_update_allergies_reactions')
    || $healthToggleChecked('health_update_pregnancy')
    || $healthToggleChecked('health_update_current_symptoms');
?>
<div class="contextual-fields context-exam-fields">
<?php if ($contextual && !empty($selectedPatient['id'])): ?>
<?= view('patient/partials/context_patient_badge', ['context_patient'=>$selectedPatient]) ?>
<input type="hidden" name="patient_id" value="<?= (int)$selectedPatient['id'] ?>">
<?php else: ?><div class="context-field"><label for="<?= esc($fieldId('patientSelect')) ?>"><?= esc($tr('patient_required')) ?></label><select name="patient_id" id="<?= esc($fieldId('patientSelect')) ?>" class="<?= esc($inputClass) ?>" data-searchable-select data-minimum-input-length="0" data-limit="5" data-search-url="<?= base_url('api/v1/search/patients') ?>" data-placeholder="<?= esc($tr('search_patients_placeholder')) ?>" required><option value=""><?= esc($tr('select_patient')) ?></option><?php if(!empty($selectedPatient)): ?><option value="<?= (int)$selectedPatient['id'] ?>" data-gender="<?= esc($selectedPatient['gender']??'') ?>" selected><?= esc($selectedPatient['first_name'].' '.$selectedPatient['last_name']) ?> (<?= esc($selectedPatient['phone']??$selectedPatient['patient_id']??$tr('na')) ?>)</option><?php endif; ?></select><?php if($validation&&$validation->hasError('patient_id')): ?><p class="context-field-error"><?= $validation->getError('patient_id') ?></p><?php endif; ?></div><?php endif; ?>
<div class="context-core-grid">
<div class="context-field"><label for="<?= esc($fieldId('examination_type')) ?>"><?= esc($tr('examination_type_required')) ?></label><select name="examination_type" id="<?= esc($fieldId('examination_type')) ?>" class="<?= esc($inputClass) ?>" data-force-select2 data-allow-clear="false" required><option value=""><?= esc($tr('select_type')) ?></option><?php foreach(['initial','periodic','emergency','follow_up'] as $type): ?><option value="<?= $type ?>" <?= old('examination_type')===$type?'selected':'' ?>><?= esc($tr('type_'.$type)) ?></option><?php endforeach; ?></select><?php if($validation&&$validation->hasError('examination_type')): ?><p class="context-field-error"><?= $validation->getError('examination_type') ?></p><?php endif; ?></div>
<div class="context-field"><label for="<?= esc($fieldId('examination_date')) ?>"><?= esc($tr('examination_date_required')) ?></label><input type="text" name="examination_date" id="<?= esc($fieldId('examination_date')) ?>" data-clinic-date="1" value="<?= esc(old('examination_date',date('Y-m-d'))) ?>" class="<?= esc($inputClass) ?>" required><?php if($validation&&$validation->hasError('examination_date')): ?><p class="context-field-error"><?= $validation->getError('examination_date') ?></p><?php endif; ?></div></div>
<div class="context-field"><label for="<?= esc($fieldId('chief_complaint')) ?>"><?= esc($tr('chief_complaint_required')) ?></label><textarea name="chief_complaint" id="<?= esc($fieldId('chief_complaint')) ?>" rows="3" class="<?= esc($inputClass) ?>" placeholder="<?= esc($tr('chief_complaint_placeholder')) ?>" required><?= old('chief_complaint') ?></textarea><?php if($validation&&$validation->hasError('chief_complaint')): ?><p class="context-field-error"><?= $validation->getError('chief_complaint') ?></p><?php endif; ?></div>
<section class="context-clinical-details" data-contextual-exam data-patient-gender="<?= esc($selectedPatientGender) ?>"><header><label class="context-check" for="<?= esc($fieldId('optionalEnabled')) ?>"><input type="checkbox" id="<?= esc($fieldId('optionalEnabled')) ?>" data-exam-optional-toggle data-section-id="<?= esc($fieldId('optionalSection')) ?>" <?= $optionalEnabledByOld?'checked':'' ?>><span><?= esc($tr('additional_clinical_details')) ?></span></label><span><?= esc($tr('optional')) ?></span></header>
<div id="<?= esc($fieldId('optionalSection')) ?>" class="context-optional-fields <?= $optionalEnabledByOld?'':'hidden' ?>">
<section class="context-health"><h3><?= esc($tr('health_update_since_last_visit')) ?></h3><div class="context-health-questions">
<?php $healthUpdateItems=[['key'=>'health_update_medical_changes','label'=>$tr('health_q_medical_changes')],['key'=>'health_update_medication_changes','label'=>$tr('health_q_medication_changes')],['key'=>'health_update_allergies_reactions','label'=>$tr('health_q_allergies_reactions')],['key'=>'health_update_pregnancy','label'=>$tr('health_q_pregnancy'),'pregnancy_only'=>true],['key'=>'health_update_current_symptoms','label'=>$tr('health_q_current_symptoms')]]; ?>
<?php foreach($healthUpdateItems as $item): $checked=$healthToggleChecked($item['key']); ?>
<div class="context-health-question" data-health-update-item <?= !empty($item['pregnancy_only'])?'data-pregnancy-only="1"':'' ?>><label class="context-check" for="<?= esc($fieldId($item['key'])) ?>"><span><?= esc($item['label']) ?></span><input type="hidden" name="<?= esc($item['key']) ?>" value="0"><input type="checkbox" id="<?= esc($fieldId($item['key'])) ?>" name="<?= esc($item['key']) ?>" value="1" class="health-update-toggle" data-details-id="<?= esc($fieldId('healthUpdateDetailsWrap')) ?>" <?= $checked?'checked':'' ?>></label></div>
<?php endforeach; ?></div>
<div id="<?= esc($fieldId('healthUpdateDetailsWrap')) ?>" class="context-field"><label for="<?= esc($fieldId('health_update_details')) ?>"><?= esc($tr('health_update_details')) ?></label><textarea name="health_update_details" id="<?= esc($fieldId('health_update_details')) ?>" rows="3" class="<?= esc($inputClass) ?>" placeholder="<?= esc($tr('health_update_details_placeholder')) ?>" <?= $anyHealthToggleChecked?'':'disabled' ?>><?= old('health_update_details') ?></textarea></div></section>
<div class="context-narrative-grid"><?php foreach(['dental_history','clinical_findings','diagnosis','examination_notes'] as $key): ?><div class="context-field"><label for="<?= esc($fieldId($key)) ?>"><?= esc($tr($key)) ?></label><textarea name="<?= $key ?>" id="<?= esc($fieldId($key)) ?>" rows="4" class="<?= esc($inputClass) ?>" placeholder="<?= esc($tr($key.'_placeholder')) ?>"><?= old($key) ?></textarea></div><?php endforeach; ?></div>
</div></section></div>
