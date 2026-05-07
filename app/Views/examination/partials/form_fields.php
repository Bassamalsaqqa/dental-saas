<?php
$tr = static fn(string $key, array $params = []) => lang('Examination.' . $key, $params);
$validation = $validation ?? session()->getFlashdata('validation');
$contextual = !empty($contextual);
$selectedPatient = $selected_patient ?? null;
$selectedPatientGender = strtolower((string) ($selectedPatient['gender'] ?? ''));
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
$inputClass = 'w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/15';
?>
<?php if ($contextual && !empty($selectedPatient['id'])): ?>
    <?= view('patient/partials/context_patient_badge', ['context_patient' => $selectedPatient]) ?>
    <input type="hidden" name="patient_id" value="<?= (int) $selectedPatient['id'] ?>">
<?php else: ?>
    <div>
        <label for="patientSelect" class="mb-2 block text-sm font-semibold text-slate-700"><?= esc($tr('patient_required')) ?></label>
        <select name="patient_id" id="patientSelect" class="<?= esc($inputClass) ?>" data-searchable-select data-minimum-input-length="0" data-limit="5" data-search-url="<?= base_url('api/v1/search/patients') ?>" data-placeholder="<?= esc($tr('search_patients_placeholder')) ?>" required>
            <option value=""><?= esc($tr('select_patient')) ?></option>
            <?php if (!empty($selectedPatient)): ?>
                <option value="<?= (int) $selectedPatient['id'] ?>" data-gender="<?= esc($selectedPatient['gender'] ?? '') ?>" selected>
                    <?= esc($selectedPatient['first_name'] . ' ' . $selectedPatient['last_name']) ?> (<?= esc($selectedPatient['phone'] ?? $selectedPatient['patient_id'] ?? $tr('na')) ?>)
                </option>
            <?php endif; ?>
        </select>
        <?php if ($validation && $validation->hasError('patient_id')): ?><p class="mt-2 text-sm text-red-600"><?= $validation->getError('patient_id') ?></p><?php endif; ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label for="examination_type" class="mb-2 block text-sm font-semibold text-slate-700"><?= esc($tr('examination_type_required')) ?></label>
        <select name="examination_type" id="examination_type" class="<?= esc($inputClass) ?>" data-force-select2 data-allow-clear="false" required>
            <option value=""><?= esc($tr('select_type')) ?></option>
            <option value="initial" <?= old('examination_type') === 'initial' ? 'selected' : '' ?>><?= esc($tr('type_initial')) ?></option>
            <option value="periodic" <?= old('examination_type') === 'periodic' ? 'selected' : '' ?>><?= esc($tr('type_periodic')) ?></option>
            <option value="emergency" <?= old('examination_type') === 'emergency' ? 'selected' : '' ?>><?= esc($tr('type_emergency')) ?></option>
            <option value="follow_up" <?= old('examination_type') === 'follow_up' ? 'selected' : '' ?>><?= esc($tr('type_follow_up')) ?></option>
        </select>
        <?php if ($validation && $validation->hasError('examination_type')): ?><p class="mt-2 text-sm text-red-600"><?= $validation->getError('examination_type') ?></p><?php endif; ?>
    </div>
    <div>
        <label for="examination_date" class="mb-2 block text-sm font-semibold text-slate-700"><?= esc($tr('examination_date_required')) ?></label>
        <input type="text" name="examination_date" id="examination_date" data-clinic-date="1" value="<?= esc(old('examination_date', date('Y-m-d'))) ?>" class="<?= esc($inputClass) ?>" required>
        <?php if ($validation && $validation->hasError('examination_date')): ?><p class="mt-2 text-sm text-red-600"><?= $validation->getError('examination_date') ?></p><?php endif; ?>
    </div>
</div>

<div>
    <label for="chief_complaint" class="mb-2 block text-sm font-semibold text-slate-700"><?= esc($tr('chief_complaint_required')) ?></label>
    <textarea name="chief_complaint" id="chief_complaint" rows="3" class="<?= esc($inputClass) ?> resize-y" placeholder="<?= esc($tr('chief_complaint_placeholder')) ?>" required><?= old('chief_complaint') ?></textarea>
    <?php if ($validation && $validation->hasError('chief_complaint')): ?><p class="mt-2 text-sm text-red-600"><?= $validation->getError('chief_complaint') ?></p><?php endif; ?>
</div>

<details class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4" <?= $optionalEnabledByOld ? 'open' : '' ?> data-contextual-exam>
    <summary class="cursor-pointer text-sm font-bold text-slate-800"><?= esc(lang('UI.advancedDetails')) ?></summary>
    <div class="mt-4 space-y-5">
        <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
            <?php
            $healthUpdateItems = [
                ['key' => 'health_update_medical_changes', 'label' => $tr('health_q_medical_changes')],
                ['key' => 'health_update_medication_changes', 'label' => $tr('health_q_medication_changes')],
                ['key' => 'health_update_allergies_reactions', 'label' => $tr('health_q_allergies_reactions')],
                ['key' => 'health_update_pregnancy', 'label' => $tr('health_q_pregnancy'), 'pregnancy_only' => true],
                ['key' => 'health_update_current_symptoms', 'label' => $tr('health_q_current_symptoms')],
            ];
            ?>
            <?php foreach ($healthUpdateItems as $item): ?>
                <?php $checked = $healthToggleChecked($item['key']); ?>
                <label class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-3 text-sm font-medium text-slate-700" <?= !empty($item['pregnancy_only']) ? 'data-pregnancy-only="1"' : '' ?>>
                    <span><?= esc($item['label']) ?></span>
                    <span class="inline-flex items-center gap-2">
                        <input type="hidden" name="<?= esc($item['key']) ?>" value="0">
                        <input type="checkbox" name="<?= esc($item['key']) ?>" value="1" class="health-update-toggle" <?= $checked ? 'checked' : '' ?>>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
        <div>
            <label for="health_update_details" class="mb-2 block text-sm font-semibold text-slate-700"><?= esc($tr('health_update_details')) ?></label>
            <textarea name="health_update_details" id="health_update_details" rows="3" class="<?= esc($inputClass) ?> resize-y" placeholder="<?= esc($tr('health_update_details_placeholder')) ?>"><?= old('health_update_details') ?></textarea>
        </div>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label for="dental_history" class="mb-2 block text-sm font-semibold text-slate-700"><?= esc($tr('dental_history')) ?></label>
                <textarea name="dental_history" id="dental_history" rows="4" class="<?= esc($inputClass) ?> resize-y" placeholder="<?= esc($tr('dental_history_placeholder')) ?>"><?= old('dental_history') ?></textarea>
            </div>
            <div>
                <label for="clinical_findings" class="mb-2 block text-sm font-semibold text-slate-700"><?= esc($tr('clinical_findings')) ?></label>
                <textarea name="clinical_findings" id="clinical_findings" rows="4" class="<?= esc($inputClass) ?> resize-y" placeholder="<?= esc($tr('clinical_findings_placeholder')) ?>"><?= old('clinical_findings') ?></textarea>
            </div>
        </div>
        <div>
            <label for="diagnosis" class="mb-2 block text-sm font-semibold text-slate-700"><?= esc($tr('diagnosis')) ?></label>
            <textarea name="diagnosis" id="diagnosis" rows="4" class="<?= esc($inputClass) ?> resize-y" placeholder="<?= esc($tr('diagnosis_placeholder')) ?>"><?= old('diagnosis') ?></textarea>
        </div>
        <div>
            <label for="examination_notes" class="mb-2 block text-sm font-semibold text-slate-700"><?= esc($tr('examination_notes')) ?></label>
            <textarea name="examination_notes" id="examination_notes" rows="4" class="<?= esc($inputClass) ?> resize-y" placeholder="<?= esc($tr('examination_notes_placeholder')) ?>"><?= old('examination_notes') ?></textarea>
        </div>
    </div>
</details>
