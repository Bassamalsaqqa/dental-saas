<?php
$validation = $validation ?? null;
$contextual = !empty($contextual);
$selectedPatient = $selected_patient ?? (!empty($patients[0]) ? $patients[0] : null);
$selectedToothNumber = old('tooth_number', $selected_tooth_number ?? '');
$inputClass = 'context-field-control';
$usageEnabledByOld = old('usage_enabled') || old('usage_notes') || !empty(old('usage_items'));
$procedureIntent = (string) old('procedure_intent', 'planned');
?>
<div class="contextual-fields context-treatment-fields" data-contextual-treatment-form>
    <input type="hidden" name="examination_id" value="<?= esc($selected_examination_id ?? '') ?>">
    <?php if ($contextual && !empty($selectedPatient['id'])): ?>
        <?= view('patient/partials/context_patient_badge', ['context_patient' => $selectedPatient]) ?>
        <input type="hidden" name="patient_id" value="<?= (int) $selectedPatient['id'] ?>">
    <?php else: ?>
        <div>
            <label ><?= esc(lang('Treatment.select_patient_required')) ?></label>
            <select name="patient_id" id="patientSelect" class="<?= esc($inputClass) ?>" data-searchable-select data-minimum-input-length="0" data-limit="5" data-search-url="<?= base_url('api/v1/search/patients') ?>" data-placeholder="<?= esc(lang('Treatment.search_patients_placeholder')) ?>" required>
                <option value=""><?= esc(lang('Treatment.select_patient')) ?></option>
                <?php foreach (($patients ?? []) as $patientRow): ?><option value="<?= (int) $patientRow['id'] ?>" selected><?= esc($patientRow['first_name'] . ' ' . $patientRow['last_name']) ?> (<?= esc($patientRow['phone'] ?? '') ?>)</option><?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>
    <fieldset class="context-intent">
        <legend><?= esc(lang('Treatment.procedure_intent')) ?></legend>
        <p ><?= esc(lang('Treatment.procedure_intent_help')) ?></p>
        <div class="context-intent-options">
            <label>
                <input type="radio" name="procedure_intent" value="planned"  <?= $procedureIntent !== 'start_now' ? 'checked' : '' ?>>
                <span>
                    <span ><?= esc(lang('Treatment.procedure_intent_planned')) ?></span>
                    <span ><?= esc(lang('Treatment.procedure_intent_planned_hint')) ?></span>
                </span>
            </label>
            <label >
                <input type="radio" name="procedure_intent" value="start_now"  <?= $procedureIntent === 'start_now' ? 'checked' : '' ?>>
                <span>
                    <span ><?= esc(lang('Treatment.procedure_intent_start_now')) ?></span>
                    <span ><?= esc(lang('Treatment.procedure_intent_start_now_hint')) ?></span>
                </span>
            </label>
        </div>
    </fieldset>
    <div class="context-field-grid">
        <div>
            <label ><?= esc(lang('Treatment.treatment_type_required')) ?></label>
            <select name="treatment_type" id="treatment_type" class="<?= esc($inputClass) ?>" data-searchable-select data-minimum-input-length="0" data-limit="5" data-search-url="<?= base_url('api/v1/search/treatment-types') ?>" data-placeholder="<?= esc(lang('Treatment.search_treatment_types_placeholder')) ?>" required>
                <option value=""><?= esc(lang('Treatment.select_treatment_type')) ?></option>
                <?php foreach (($treatment_types ?? []) as $key => $value): ?><option value="<?= esc($key) ?>" <?= old('treatment_type') == $key ? 'selected' : '' ?>><?= esc($value) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div>
            <label ><?= esc(lang('Treatment.tooth_number')) ?></label>
            <input type="text" name="tooth_number" value="<?= esc($selectedToothNumber) ?>" maxlength="10" class="<?= esc($inputClass) ?>" placeholder="<?= esc(lang('Treatment.tooth_number_placeholder')) ?>">
        </div>
        <div>
            <label ><?= esc(lang('Treatment.start_date_required')) ?></label>
            <input type="text" name="start_date" data-clinic-date="1" value="<?= esc(old('start_date', date('Y-m-d'))) ?>" class="<?= esc($inputClass) ?> treatment-date-input" required>
        </div>
        <div>
            <label ><?= esc(lang('Treatment.estimated_duration_days_required')) ?></label>
            <input type="number" name="estimated_duration" value="<?= esc(old('estimated_duration', 1)) ?>" min="1" class="<?= esc($inputClass) ?>" required>
        </div>
    </div>
    <div >
        <label ><?= esc(lang('Treatment.treatment_description_required')) ?></label>
        <textarea name="description" rows="4" class="<?= esc($inputClass) ?>" placeholder="<?= esc(lang('Treatment.treatment_description_placeholder')) ?>" required><?= old('description') ?></textarea>
    </div>
    <?= view('treatment/partials/charge_line_fields', [
        'validation' => $validation,
        'clinic_providers' => $clinic_providers ?? [],
        'charge_line' => $charge_line ?? null,
        'id_prefix' => $id_prefix ?? 'treatment_quick_charge',
        'input_class' => $inputClass,
    ]) ?>
    <div >
        <div class="context-field-stack context-stock-usage">
            <label class="context-check">
                <span><?= esc(lang('Treatment.record_inventory_usage')) ?></span>
                <span>
                    <input type="checkbox" name="usage_enabled" value="1" data-usage-toggle <?= $usageEnabledByOld ? 'checked' : '' ?>>

                </span>
            </label>
            <div data-usage-section class="<?= $usageEnabledByOld ? '' : 'hidden' ?>">
<label class="context-field"><span><?= esc(lang('Treatment.date')) ?></span><input type="text" name="usage_date" data-clinic-date="1" value="<?= esc(date('Y-m-d')) ?>" class="<?= esc($inputClass) ?> treatment-date-input"></label>
                <div data-usage-items-container class="context-field-stack">
                    <div class="usage-item context-usage-item">
                        <div class="context-field-grid context-usage-columns">
                            <select name="usage_items[0][item_id]" class="<?= esc($inputClass) ?> item-select" data-force-select2>
                                <option value=""><?= esc(lang('Treatment.select_item')) ?></option>
                                <?php foreach (($inventory_items ?? []) as $item): ?>
                                    <option value="<?= (int) $item['id'] ?>" data-quantity="<?= esc($item['quantity']) ?>" data-unit="<?= esc($item['unit']) ?>" data-price="<?= esc($item['unit_price']) ?>"><?= esc($item['item_name']) ?> (<?= esc($item['quantity']) ?> <?= esc($item['unit'] ?: lang('Treatment.pieces')) ?>)</option>
                                <?php endforeach; ?>
                            </select>
<label class="context-field"><span><?= esc(lang('Treatment.quantity_required')) ?></span><input type="number" name="usage_items[0][quantity]" min="1" class="<?= esc($inputClass) ?> quantity-input" placeholder="<?= esc(lang('Treatment.quantity_required')) ?>"></label>
<label class="context-field"><span><?= esc(lang('Treatment.unit_cost')) ?></span><input type="text" class="<?= esc($inputClass) ?> unit-cost" readonly placeholder="<?= esc(lang('Treatment.unit_cost')) ?>"></label>
<label class="context-field"><span><?= esc(lang('Treatment.total_cost')) ?></span><input type="text" class="<?= esc($inputClass) ?> total-cost" readonly placeholder="<?= esc(lang('Treatment.total_cost')) ?>"></label>
                        </div>
                        <div >
                            <button type="button" data-remove-usage-item >
                                <span><?= esc(lang('Treatment.remove')) ?></span>
                            </button>
                        </div>
                    </div>
                </div>
                <div >
                    <button type="button" data-add-usage-item ><?= esc(lang('Treatment.add_item')) ?></button>
                    <div ><?= esc(lang('Treatment.total')) ?>: <span data-usage-grand-total><?= formatCurrency(0) ?></span></div>
                </div>
<label class="context-field"><span><?= esc(lang('Treatment.usage_notes')) ?></span><textarea name="usage_notes" rows="3" class="<?= esc($inputClass) ?>" placeholder="<?= esc(lang('Treatment.optional_usage_notes_placeholder')) ?>"><?= old('usage_notes') ?></textarea></label>
            </div>
        </div>
    </div>
</div>
