<?php
$validation = $validation ?? null;
$contextual = !empty($contextual);
$selectedPatient = $selected_patient ?? (!empty($patients[0]) ? $patients[0] : null);
$selectedToothNumber = old('tooth_number', $selected_tooth_number ?? '');
$inputClass = 'w-full v3-radius border-2 v3-border-default v3-bg-panel px-4 py-3 text-sm font-medium v3-text-ink v3-elevation-none transition v3-focus-control focus:outline-none focus:ring-4 v3-focus-control';
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
            <label class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc(lang('Treatment.select_patient_required')) ?></label>
            <select name="patient_id" id="patientSelect" class="<?= esc($inputClass) ?> v3-select" data-searchable-select data-minimum-input-length="0" data-limit="5" data-search-url="<?= base_url('api/v1/search/patients') ?>" data-placeholder="<?= esc(lang('Treatment.search_patients_placeholder')) ?>" required>
                <option value=""><?= esc(lang('Treatment.select_patient')) ?></option>
                <?php foreach (($patients ?? []) as $patientRow): ?><option value="<?= (int) $patientRow['id'] ?>" selected><?= esc($patientRow['first_name'] . ' ' . $patientRow['last_name']) ?> (<?= esc($patientRow['phone'] ?? '') ?>)</option><?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>
    <fieldset class="mt-5 v3-radius border v3-border-default bg-gradient-to-br from-blue-50 via-white to-cyan-50 p-4 v3-elevation-none">
        <legend class="px-1 text-sm font-extrabold v3-text-ink"><?= esc(lang('Treatment.procedure_intent')) ?></legend>
        <p class="mb-3 text-xs font-semibold v3-text-muted"><?= esc(lang('Treatment.procedure_intent_help')) ?></p>
        <div class="grid gap-3 sm:grid-cols-2">
            <label class="flex cursor-pointer items-start gap-3 v3-radius border v3-border-default v3-bg-panel p-3 v3-elevation-none transition v3-border-default">
                <input type="radio" name="procedure_intent" value="planned" class="mt-1 h-4 w-4 v3-border-default v3-text-primary v3-focus-control v3-radio" <?= $procedureIntent !== 'start_now' ? 'checked' : '' ?>>
                <span>
                    <span class="block text-sm font-extrabold v3-text-ink"><?= esc(lang('Treatment.procedure_intent_planned')) ?></span>
                    <span class="mt-1 block text-xs font-medium leading-5 v3-text-muted"><?= esc(lang('Treatment.procedure_intent_planned_hint')) ?></span>
                </span>
            </label>
            <label class="flex cursor-pointer items-start gap-3 v3-radius border v3-border-default v3-bg-panel p-3 v3-elevation-none transition v3-border-default">
                <input type="radio" name="procedure_intent" value="start_now" class="mt-1 h-4 w-4 v3-border-default v3-text-primary v3-focus-control v3-radio" <?= $procedureIntent === 'start_now' ? 'checked' : '' ?>>
                <span>
                    <span class="block text-sm font-extrabold v3-text-ink"><?= esc(lang('Treatment.procedure_intent_start_now')) ?></span>
                    <span class="mt-1 block text-xs font-medium leading-5 v3-text-muted"><?= esc(lang('Treatment.procedure_intent_start_now_hint')) ?></span>
                </span>
            </label>
        </div>
    </fieldset>
    <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc(lang('Treatment.treatment_type_required')) ?></label>
            <select name="treatment_type" id="treatment_type" class="<?= esc($inputClass) ?> v3-select" data-searchable-select data-minimum-input-length="0" data-limit="5" data-search-url="<?= base_url('api/v1/search/treatment-types') ?>" data-placeholder="<?= esc(lang('Treatment.search_treatment_types_placeholder')) ?>" required>
                <option value=""><?= esc(lang('Treatment.select_treatment_type')) ?></option>
                <?php foreach (($treatment_types ?? []) as $key => $value): ?><option value="<?= esc($key) ?>" <?= old('treatment_type') == $key ? 'selected' : '' ?>><?= esc($value) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc(lang('Treatment.tooth_number')) ?></label>
            <input type="text" name="tooth_number" value="<?= esc($selectedToothNumber) ?>" maxlength="10" class="<?= esc($inputClass) ?> v3-input" placeholder="<?= esc(lang('Treatment.tooth_number_placeholder')) ?>">
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc(lang('Treatment.start_date_required')) ?></label>
            <input type="text" name="start_date" data-clinic-date="1" value="<?= esc(old('start_date', date('Y-m-d'))) ?>" class="<?= esc($inputClass) ?> treatment-date-input v3-input" required>
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc(lang('Treatment.estimated_duration_days_required')) ?></label>
            <input type="number" name="estimated_duration" value="<?= esc(old('estimated_duration', 1)) ?>" min="1" class="<?= esc($inputClass) ?> v3-input" required>
        </div>
    </div>
    <div class="mt-5">
        <label class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc(lang('Treatment.treatment_description_required')) ?></label>
        <textarea name="description" rows="4" class="<?= esc($inputClass) ?> resize-y v3-textarea" placeholder="<?= esc(lang('Treatment.treatment_description_placeholder')) ?>" required><?= old('description') ?></textarea>
    </div>
    <?= view('treatment/partials/charge_line_fields', [
        'validation' => $validation,
        'clinic_providers' => $clinic_providers ?? [],
        'charge_line' => $charge_line ?? null,
        'id_prefix' => $id_prefix ?? 'treatment_quick_charge',
        'input_class' => $inputClass,
    ]) ?>
    <div class="mt-5 v3-radius border v3-border-default v3-bg-panel p-4">
        <div class="space-y-4">
            <label class="app-toggle-row">
                <span class="app-toggle-row__text"><?= esc(lang('Treatment.record_inventory_usage')) ?></span>
                <span class="app-toggle">
                    <input type="checkbox" name="usage_enabled" value="1" data-usage-toggle <?= $usageEnabledByOld ? 'checked' : '' ?> class="v3-checkbox">
                    <span class="app-toggle__track" aria-hidden="true">
                        <span class="app-toggle__dot"></span>
                    </span>
                </span>
            </label>
            <div data-usage-section class="<?= $usageEnabledByOld ? '' : 'hidden' ?> space-y-4">
                <input type="text" name="usage_date" data-clinic-date="1" value="<?= esc(date('Y-m-d')) ?>" class="<?= esc($inputClass) ?> treatment-date-input v3-input">
                <div data-usage-items-container class="space-y-4">
                    <div class="usage-item v3-radius border v3-border-default v3-bg-panel p-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <select name="usage_items[0][item_id]" class="<?= esc($inputClass) ?> item-select v3-select" data-force-select2>
                                <option value=""><?= esc(lang('Treatment.select_item')) ?></option>
                                <?php foreach (($inventory_items ?? []) as $item): ?>
                                    <option value="<?= (int) $item['id'] ?>" data-quantity="<?= esc($item['quantity']) ?>" data-unit="<?= esc($item['unit']) ?>" data-price="<?= esc($item['unit_price']) ?>"><?= esc($item['item_name']) ?> (<?= esc($item['quantity']) ?> <?= esc($item['unit'] ?: lang('Treatment.pieces')) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" name="usage_items[0][quantity]" min="1" class="<?= esc($inputClass) ?> quantity-input v3-input" placeholder="<?= esc(lang('Treatment.quantity_required')) ?>">
                            <input type="text" class="<?= esc($inputClass) ?> unit-cost v3-bg-soft v3-input" readonly placeholder="<?= esc(lang('Treatment.unit_cost')) ?>">
                            <input type="text" class="<?= esc($inputClass) ?> total-cost v3-bg-soft v3-input" readonly placeholder="<?= esc(lang('Treatment.total_cost')) ?>">
                        </div>
                        <div class="mt-3 flex justify-end">
                            <button type="button" data-remove-usage-item class="inline-flex items-center justify-center gap-2 v3-radius border v3-border-default v3-bg-panel px-3 py-2 text-xs font-bold v3-text-danger v3-elevation-none transition v3-border-default v3-hover-danger v3-hover-text-danger v3-btn v3-btn--danger">
                                <i class="fas fa-trash"></i><span><?= esc(lang('Treatment.remove')) ?></span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <button type="button" data-add-usage-item class="v3-radius v3-bg-primary px-4 py-2 text-sm font-bold v3-text-on-primary v3-hover-primary v3-btn v3-btn--primary"><i class="fas fa-plus me-2"></i><?= esc(lang('Treatment.add_item')) ?></button>
                    <div class="text-sm font-bold v3-text-ink"><?= esc(lang('Treatment.total')) ?>: <span data-usage-grand-total><?= formatCurrency(0) ?></span></div>
                </div>
                <textarea name="usage_notes" rows="3" class="<?= esc($inputClass) ?> resize-y v3-textarea" placeholder="<?= esc(lang('Treatment.optional_usage_notes_placeholder')) ?>"><?= old('usage_notes') ?></textarea>
            </div>
        </div>
    </div>
</div>
