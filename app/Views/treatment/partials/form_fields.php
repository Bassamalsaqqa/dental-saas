<?php
$validation = $validation ?? null;
$contextual = !empty($contextual);
$selectedPatient = $selected_patient ?? (!empty($patients[0]) ? $patients[0] : null);
$selectedToothNumber = old('tooth_number', $selected_tooth_number ?? '');
$inputClass = 'v4-field w-full';
$usageEnabledByOld = old('usage_enabled') || old('usage_notes') || !empty(old('usage_items'));
$procedureIntent = (string) old('procedure_intent', 'planned');
?>
<div data-contextual-treatment-form>
    <input type="hidden" name="examination_id" value="<?= esc($selected_examination_id ?? '') ?>">
    <?php if ($contextual && !empty($selectedPatient['id'])): ?>
        <?= view('patient/partials/context_patient_badge', ['context_patient' => $selectedPatient]) ?>
        <input type="hidden" name="patient_id" value="<?= (int) $selectedPatient['id'] ?>">
    <?php else: ?>
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('Treatment.select_patient_required')) ?></label>
            <select name="patient_id" id="patientSelect" class="<?= esc($inputClass) ?>" data-searchable-select data-minimum-input-length="0" data-limit="5" data-search-url="<?= base_url('api/v1/search/patients') ?>" data-placeholder="<?= esc(lang('Treatment.search_patients_placeholder')) ?>" required>
                <option value=""><?= esc(lang('Treatment.select_patient')) ?></option>
                <?php foreach (($patients ?? []) as $patientRow): ?><option value="<?= (int) $patientRow['id'] ?>" selected><?= esc($patientRow['first_name'] . ' ' . $patientRow['last_name']) ?> (<?= esc($patientRow['phone'] ?? '') ?>)</option><?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>
    <fieldset class="v4-choice-fieldset">
        <legend class="px-1 text-sm font-extrabold text-slate-900"><?= esc(lang('Treatment.procedure_intent')) ?></legend>
        <p class="mb-3 text-xs font-semibold text-slate-600"><?= esc(lang('Treatment.procedure_intent_help')) ?></p>
        <div class="grid gap-3 sm:grid-cols-2">
            <label class="v4-choice-option">
                <input type="radio" name="procedure_intent" value="planned" class="mt-1 h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-500" <?= $procedureIntent !== 'start_now' ? 'checked' : '' ?>>
                <span>
                    <span class="block text-sm font-extrabold text-slate-900"><?= esc(lang('Treatment.procedure_intent_planned')) ?></span>
                    <span class="mt-1 block text-xs font-medium leading-5 text-slate-600"><?= esc(lang('Treatment.procedure_intent_planned_hint')) ?></span>
                </span>
            </label>
            <label class="v4-choice-option">
                <input type="radio" name="procedure_intent" value="start_now" class="mt-1 h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500" <?= $procedureIntent === 'start_now' ? 'checked' : '' ?>>
                <span>
                    <span class="block text-sm font-extrabold text-slate-900"><?= esc(lang('Treatment.procedure_intent_start_now')) ?></span>
                    <span class="mt-1 block text-xs font-medium leading-5 text-slate-600"><?= esc(lang('Treatment.procedure_intent_start_now_hint')) ?></span>
                </span>
            </label>
        </div>
    </fieldset>
    <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('Treatment.treatment_type_required')) ?></label>
            <select name="treatment_type" id="treatment_type" class="<?= esc($inputClass) ?>" data-searchable-select data-minimum-input-length="0" data-limit="5" data-search-url="<?= base_url('api/v1/search/treatment-types') ?>" data-placeholder="<?= esc(lang('Treatment.search_treatment_types_placeholder')) ?>" required>
                <option value=""><?= esc(lang('Treatment.select_treatment_type')) ?></option>
                <?php foreach (($treatment_types ?? []) as $key => $value): ?><option value="<?= esc($key) ?>" <?= old('treatment_type') == $key ? 'selected' : '' ?>><?= esc($value) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('Treatment.tooth_number')) ?></label>
            <input type="text" name="tooth_number" value="<?= esc($selectedToothNumber) ?>" maxlength="10" class="<?= esc($inputClass) ?>" placeholder="<?= esc(lang('Treatment.tooth_number_placeholder')) ?>">
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('Treatment.start_date_required')) ?></label>
            <input type="text" name="start_date" data-clinic-date="1" value="<?= esc(old('start_date', date('Y-m-d'))) ?>" class="<?= esc($inputClass) ?> treatment-date-input" required>
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('Treatment.estimated_duration_days_required')) ?></label>
            <input type="number" name="estimated_duration" value="<?= esc(old('estimated_duration', 1)) ?>" min="1" class="<?= esc($inputClass) ?>" required>
        </div>
    </div>
    <div class="mt-5">
        <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('Treatment.treatment_description_required')) ?></label>
        <textarea name="description" rows="4" class="<?= esc($inputClass) ?> resize-y" placeholder="<?= esc(lang('Treatment.treatment_description_placeholder')) ?>" required><?= old('description') ?></textarea>
    </div>
    <?= view('treatment/partials/charge_line_fields', [
        'validation' => $validation,
        'clinic_providers' => $clinic_providers ?? [],
        'charge_line' => $charge_line ?? null,
        'id_prefix' => $id_prefix ?? 'treatment_quick_charge',
        'input_class' => $inputClass,
    ]) ?>
    <div class="v4-form-section mt-5">
        <div class="space-y-4">
            <label class="v4-switch-row">
                <span class="v4-switch-row__text"><?= esc(lang('Treatment.record_inventory_usage')) ?></span>
                <span class="v4-switch">
                    <input type="checkbox" name="usage_enabled" value="1" data-usage-toggle <?= $usageEnabledByOld ? 'checked' : '' ?>>
                    <span class="v4-switch__track" aria-hidden="true">
                        <span class="v4-switch__dot"></span>
                    </span>
                </span>
            </label>
            <div data-usage-section class="<?= $usageEnabledByOld ? '' : 'hidden' ?> space-y-4">
                <input type="text" name="usage_date" data-clinic-date="1" value="<?= esc(date('Y-m-d')) ?>" class="<?= esc($inputClass) ?> treatment-date-input">
                <div data-usage-items-container class="space-y-4">
                    <div class="usage-item rounded-xl border border-slate-200 bg-white p-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <select name="usage_items[0][item_id]" class="<?= esc($inputClass) ?> item-select" data-force-select2>
                                <option value=""><?= esc(lang('Treatment.select_item')) ?></option>
                                <?php foreach (($inventory_items ?? []) as $item): ?>
                                    <option value="<?= (int) $item['id'] ?>" data-quantity="<?= esc($item['quantity']) ?>" data-unit="<?= esc($item['unit']) ?>" data-price="<?= esc($item['unit_price']) ?>"><?= esc($item['item_name']) ?> (<?= esc($item['quantity']) ?> <?= esc($item['unit'] ?: lang('Treatment.pieces')) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" name="usage_items[0][quantity]" min="1" class="<?= esc($inputClass) ?> quantity-input" placeholder="<?= esc(lang('Treatment.quantity_required')) ?>">
                            <input type="text" class="<?= esc($inputClass) ?> unit-cost bg-slate-100" readonly placeholder="<?= esc(lang('Treatment.unit_cost')) ?>">
                            <input type="text" class="<?= esc($inputClass) ?> total-cost bg-slate-100" readonly placeholder="<?= esc(lang('Treatment.total_cost')) ?>">
                        </div>
                        <div class="mt-3 flex justify-end">
                            <button type="button" data-remove-usage-item class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-bold text-rose-700 shadow-sm transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-800">
                                <i class="fas fa-trash"></i><span><?= esc(lang('Treatment.remove')) ?></span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <button type="button" data-add-usage-item class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700"><i class="fas fa-plus me-2"></i><?= esc(lang('Treatment.add_item')) ?></button>
                    <div class="text-sm font-bold text-slate-700"><?= esc(lang('Treatment.total')) ?>: <span data-usage-grand-total><?= formatCurrency(0) ?></span></div>
                </div>
                <textarea name="usage_notes" rows="3" class="<?= esc($inputClass) ?> resize-y" placeholder="<?= esc(lang('Treatment.optional_usage_notes_placeholder')) ?>"><?= old('usage_notes') ?></textarea>
            </div>
        </div>
    </div>
</div>
