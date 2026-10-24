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
$inputClass = 'w-full v3-radius border-2 v3-border-default v3-bg-panel px-4 py-3 text-sm font-medium v3-text-ink v3-elevation-none transition v3-focus-control focus:outline-none focus:ring-4 v3-focus-control';
$anyHealthToggleChecked = $healthToggleChecked('health_update_medical_changes')
    || $healthToggleChecked('health_update_medication_changes')
    || $healthToggleChecked('health_update_allergies_reactions')
    || $healthToggleChecked('health_update_pregnancy')
    || $healthToggleChecked('health_update_current_symptoms');
?>
<?php if ($contextual && !empty($selectedPatient['id'])): ?>
    <?= view('patient/partials/context_patient_badge', ['context_patient' => $selectedPatient]) ?>
    <input type="hidden" name="patient_id" value="<?= (int) $selectedPatient['id'] ?>">
<?php else: ?>
    <div>
        <label for="<?= esc($fieldId('patientSelect')) ?>" class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc($tr('patient_required')) ?></label>
        <select name="patient_id" id="<?= esc($fieldId('patientSelect')) ?>" class="<?= esc($inputClass) ?> v3-select" data-searchable-select data-minimum-input-length="0" data-limit="5" data-search-url="<?= base_url('api/v1/search/patients') ?>" data-placeholder="<?= esc($tr('search_patients_placeholder')) ?>" required>
            <option value=""><?= esc($tr('select_patient')) ?></option>
            <?php if (!empty($selectedPatient)): ?>
                <option value="<?= (int) $selectedPatient['id'] ?>" data-gender="<?= esc($selectedPatient['gender'] ?? '') ?>" selected>
                    <?= esc($selectedPatient['first_name'] . ' ' . $selectedPatient['last_name']) ?> (<?= esc($selectedPatient['phone'] ?? $selectedPatient['patient_id'] ?? $tr('na')) ?>)
                </option>
            <?php endif; ?>
        </select>
        <?php if ($validation && $validation->hasError('patient_id')): ?><p class="mt-2 text-sm v3-text-danger"><?= $validation->getError('patient_id') ?></p><?php endif; ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label for="<?= esc($fieldId('examination_type')) ?>" class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc($tr('examination_type_required')) ?></label>
        <select name="examination_type" id="<?= esc($fieldId('examination_type')) ?>" class="<?= esc($inputClass) ?> v3-select" data-force-select2 data-allow-clear="false" required>
            <option value=""><?= esc($tr('select_type')) ?></option>
            <option value="initial" <?= old('examination_type') === 'initial' ? 'selected' : '' ?>><?= esc($tr('type_initial')) ?></option>
            <option value="periodic" <?= old('examination_type') === 'periodic' ? 'selected' : '' ?>><?= esc($tr('type_periodic')) ?></option>
            <option value="emergency" <?= old('examination_type') === 'emergency' ? 'selected' : '' ?>><?= esc($tr('type_emergency')) ?></option>
            <option value="follow_up" <?= old('examination_type') === 'follow_up' ? 'selected' : '' ?>><?= esc($tr('type_follow_up')) ?></option>
        </select>
        <?php if ($validation && $validation->hasError('examination_type')): ?><p class="mt-2 text-sm v3-text-danger"><?= $validation->getError('examination_type') ?></p><?php endif; ?>
    </div>
    <div>
        <label for="<?= esc($fieldId('examination_date')) ?>" class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc($tr('examination_date_required')) ?></label>
        <input type="text" name="examination_date" id="<?= esc($fieldId('examination_date')) ?>" data-clinic-date="1" value="<?= esc(old('examination_date', date('Y-m-d'))) ?>" class="<?= esc($inputClass) ?> v3-input" required>
        <?php if ($validation && $validation->hasError('examination_date')): ?><p class="mt-2 text-sm v3-text-danger"><?= $validation->getError('examination_date') ?></p><?php endif; ?>
    </div>
</div>

<div>
    <label for="<?= esc($fieldId('chief_complaint')) ?>" class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc($tr('chief_complaint_required')) ?></label>
    <textarea name="chief_complaint" id="<?= esc($fieldId('chief_complaint')) ?>" rows="3" class="<?= esc($inputClass) ?> resize-y v3-textarea" placeholder="<?= esc($tr('chief_complaint_placeholder')) ?>" required><?= old('chief_complaint') ?></textarea>
    <?php if ($validation && $validation->hasError('chief_complaint')): ?><p class="mt-2 text-sm v3-text-danger"><?= $validation->getError('chief_complaint') ?></p><?php endif; ?>
</div>

<div class="v3-radius border v3-border v3-surface p-5 md:p-6" data-contextual-exam data-patient-gender="<?= esc($selectedPatientGender) ?>">
    <label class="mb-4 flex cursor-pointer flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <span class="flex min-w-0 items-center gap-3">
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center v3-radius v3-text-on-primary v3-form-section-header">
                <i class="fas fa-notes-medical text-sm"></i>
            </span>
            <span class="min-w-0">
                <span class="block text-sm font-bold v3-text-ink"><?= esc($tr('additional_clinical_details')) ?></span>
                <span class="block text-xs font-medium v3-text-muted"><?= esc($tr('optional')) ?></span>
            </span>
        </span>
        <span class="inline-flex shrink-0 items-center gap-3">
            <span class="text-sm font-semibold v3-text-ink"><?= esc($tr('enable')) ?></span>
            <span class="relative inline-flex">
                <input type="checkbox"
                       id="<?= esc($fieldId('optionalEnabled')) ?>"
                       class="sr-only v3-checkbox"
                       data-exam-optional-toggle
                       data-section-id="<?= esc($fieldId('optionalSection')) ?>"
                       data-track-id="<?= esc($fieldId('optionalTrack')) ?>"
                       data-dot-id="<?= esc($fieldId('optionalDot')) ?>"
                       <?= $optionalEnabledByOld ? 'checked' : '' ?>>
                <span id="<?= esc($fieldId('optionalTrack')) ?>" class="toggle-track relative h-6 w-11 rounded-full <?= $optionalEnabledByOld ? 'v3-bg-primary' : 'v3-bg-sand' ?>">
                    <span id="<?= esc($fieldId('optionalDot')) ?>" class="toggle-dot absolute left-1 top-1 h-4 w-4 rounded-full v3-surface" style="transform: <?= $optionalEnabledByOld ? (!empty($is_rtl) ? 'translateX(-20px)' : 'translateX(20px)') : 'translateX(0)' ?>;"></span>
                </span>
            </span>
        </span>
    </label>

    <div id="<?= esc($fieldId('optionalSection')) ?>" class="<?= $optionalEnabledByOld ? '' : 'hidden' ?> space-y-6">
        <div>
            <div class="mb-3 flex items-center justify-between gap-3">
                <h5 class="text-sm font-bold v3-text-ink"><?= esc($tr('health_update_since_last_visit')) ?></h5>
                <span class="text-xs font-semibold v3-text-muted"><?= esc($tr('quick_check')) ?></span>
            </div>
            <div class="v3-radius border v3-border v3-surface p-4 md:p-5">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <?php
                $healthUpdateItems = [
                    ['key' => 'health_update_medical_changes', 'label' => $tr('health_q_medical_changes'), 'icon' => 'fas fa-notes-medical', 'icon_class' => 'v3-text-primary'],
                    ['key' => 'health_update_medication_changes', 'label' => $tr('health_q_medication_changes'), 'icon' => 'fas fa-pills', 'icon_class' => 'v3-text-primary'],
                    ['key' => 'health_update_allergies_reactions', 'label' => $tr('health_q_allergies_reactions'), 'icon' => 'fas fa-allergies', 'icon_class' => 'v3-text-danger'],
                    ['key' => 'health_update_pregnancy', 'label' => $tr('health_q_pregnancy'), 'icon' => 'fas fa-baby', 'icon_class' => 'text-fuchsia-500', 'pregnancy_only' => true],
                    ['key' => 'health_update_current_symptoms', 'label' => $tr('health_q_current_symptoms'), 'icon' => 'fas fa-triangle-exclamation', 'icon_class' => 'v3-text-warning'],
                ];
                ?>
                <?php foreach ($healthUpdateItems as $item): ?>
                    <?php $checked = $healthToggleChecked($item['key']); ?>
                    <div class="rounded-lg border v3-border v3-surface p-3" data-health-update-item <?= !empty($item['pregnancy_only']) ? 'data-pregnancy-only="1"' : '' ?>>
                        <div class="flex items-center justify-between gap-3">
                            <label for="<?= esc($fieldId($item['key'])) ?>" class="flex min-w-0 items-start gap-2 text-sm font-medium leading-snug v3-text-ink">
                                <i class="<?= esc($item['icon']) ?> <?= esc($item['icon_class']) ?> mt-0.5 w-4 shrink-0 text-center"></i>
                                <span><?= esc($item['label']) ?></span>
                            </label>
                            <span class="inline-flex shrink-0 items-center gap-2">
                                <span class="text-xs font-semibold v3-text-muted"><?= esc($tr('no')) ?></span>
                                <label class="inline-flex cursor-pointer items-center">
                                    <input type="hidden" name="<?= esc($item['key']) ?>" value="0">
                                    <input type="checkbox"
                                           id="<?= esc($fieldId($item['key'])) ?>"
                                           name="<?= esc($item['key']) ?>"
                                           value="1"
                                           class="sr-only health-update-toggle v3-checkbox"
                                           data-track-id="<?= esc($fieldId($item['key'] . '_track')) ?>"
                                           data-dot-id="<?= esc($fieldId($item['key'] . '_dot')) ?>"
                                           data-details-id="<?= esc($fieldId('healthUpdateDetailsWrap')) ?>"
                                           <?= $checked ? 'checked' : '' ?>>
                                    <span id="<?= esc($fieldId($item['key'] . '_track')) ?>" class="toggle-track relative h-6 w-11 rounded-full <?= $checked ? 'v3-bg-primary' : 'v3-bg-sand' ?>">
                                        <span id="<?= esc($fieldId($item['key'] . '_dot')) ?>" class="toggle-dot absolute left-1 top-1 h-4 w-4 rounded-full v3-surface" style="transform: <?= $checked ? (!empty($is_rtl) ? 'translateX(-20px)' : 'translateX(20px)') : 'translateX(0)' ?>;"></span>
                                    </span>
                                </label>
                                <span class="text-xs font-semibold v3-accent-text"><?= esc($tr('yes')) ?></span>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>

            </div>

            <div id="<?= esc($fieldId('healthUpdateDetailsWrap')) ?>" class="mt-3">
                <label for="<?= esc($fieldId('health_update_details')) ?>" class="mb-2 block text-xs font-semibold v3-text-muted"><?= esc($tr('health_update_details')) ?></label>
                <textarea name="health_update_details" id="<?= esc($fieldId('health_update_details')) ?>" rows="3" class="<?= esc($inputClass) ?> resize-y v3-textarea" placeholder="<?= esc($tr('health_update_details_placeholder')) ?>" <?= $anyHealthToggleChecked ? '' : 'disabled' ?>><?= old('health_update_details') ?></textarea>
            </div>
        </div>

        <div class="space-y-5">
            <h5 class="mb-3 text-sm font-semibold v3-text-ink"><?= esc($tr('additional_clinical_details')) ?></h5>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label for="<?= esc($fieldId('dental_history')) ?>" class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc($tr('dental_history')) ?></label>
                    <textarea name="dental_history" id="<?= esc($fieldId('dental_history')) ?>" rows="4" class="<?= esc($inputClass) ?> resize-y v3-textarea" placeholder="<?= esc($tr('dental_history_placeholder')) ?>"><?= old('dental_history') ?></textarea>
                </div>
                <div>
                    <label for="<?= esc($fieldId('clinical_findings')) ?>" class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc($tr('clinical_findings')) ?></label>
                    <textarea name="clinical_findings" id="<?= esc($fieldId('clinical_findings')) ?>" rows="4" class="<?= esc($inputClass) ?> resize-y v3-textarea" placeholder="<?= esc($tr('clinical_findings_placeholder')) ?>"><?= old('clinical_findings') ?></textarea>
                </div>
            </div>
            <div>
                <label for="<?= esc($fieldId('diagnosis')) ?>" class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc($tr('diagnosis')) ?></label>
                <textarea name="diagnosis" id="<?= esc($fieldId('diagnosis')) ?>" rows="4" class="<?= esc($inputClass) ?> resize-y v3-textarea" placeholder="<?= esc($tr('diagnosis_placeholder')) ?>"><?= old('diagnosis') ?></textarea>
            </div>
            <div>
                <label for="<?= esc($fieldId('examination_notes')) ?>" class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc($tr('examination_notes')) ?></label>
                <textarea name="examination_notes" id="<?= esc($fieldId('examination_notes')) ?>" rows="4" class="<?= esc($inputClass) ?> resize-y v3-textarea" placeholder="<?= esc($tr('examination_notes_placeholder')) ?>"><?= old('examination_notes') ?></textarea>
            </div>
        </div>
    </div>
</div>
