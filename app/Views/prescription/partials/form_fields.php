<?php
$idPrefix = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($id_prefix ?? ''));
$fieldId = static function (string $id) use ($idPrefix): string {
    return $idPrefix !== '' ? $idPrefix . '_' . $id : $id;
};
?>
<div class="space-y-6">
<!-- Patient Selection -->
<div class="group/field relative">
    <label class="mb-3 flex items-center gap-2 text-sm font-bold v3-text-ink">
        <i class="fas fa-user v3-text-primary"></i>
        <span><?= lang('Prescription.patient_required') ?></span>
    </label>
    <div class="relative">
        <select name="patient_id" id="<?= esc($fieldId('patientSelect')) ?>" class="w-full v3-radius border-2 v3-border v3-surface px-4 py-3 transition-all duration-300 v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-select"
                data-searchable-select
                data-minimum-input-length="0"
                data-limit="5"
                data-search-url="<?= base_url('api/v1/search/patients') ?>"
                data-search-param="q"
                data-value-field="id"
                data-text-field="text"
                data-placeholder="<?= esc(lang('Prescription.search_patients_placeholder')) ?>"
                data-allow-clear="true"
                data-minimum-input-length="1"
                data-delay="300"
                required>
            <option value=""><?= lang('Prescription.select_patient') ?></option>
            <?php if (!empty($selected_patient)): ?>
                <option value="<?= (int) $selected_patient['id'] ?>" selected>
                    <?= esc($selected_patient['first_name'] . ' ' . $selected_patient['last_name']) ?> (<?= esc($selected_patient['phone'] ?? $selected_patient['patient_id'] ?? lang('Prescription.na')) ?>)
                </option>
            <?php endif; ?>
        </select>
        <div class="pointer-events-none absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center">
            <i class="fas fa-chevron-down v3-text-muted"></i>
        </div>
    </div>
</div>

<!-- Medicines Section -->
<div class="group/section relative">

    <div class="relative v3-radius border v3-border p-6 v3-form-section-header">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center v3-radius v3-form-section-header">
                    <i class="fas fa-pills v3-text-on-primary"></i>
                </div>
                <h3 class="text-xl font-bold v3-text-ink"><?= lang('Prescription.medicines') ?></h3>
            </div>
            <button type="button" data-prescription-add-medicine class="group/btn relative inline-flex items-center justify-center v3-radius px-4 py-2 text-sm font-bold v3-text-on-primary transition-all duration-300 v3-form-section-header v3-btn v3-btn--secondary">

                <i class="fas fa-plus me-2 relative z-10"></i>
                <span class="relative z-10"><?= lang('Prescription.add_medicine') ?></span>
            </button>
        </div>

        <div data-prescription-medicines-container class="space-y-4"></div>

        <div data-prescription-empty-medicines class="py-8 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center v3-radius v3-form-section-header">
                <i class="fas fa-pills text-2xl v3-accent-text"></i>
            </div>
            <p class="font-medium v3-text-muted"><?= lang('Prescription.no_medicines_added') ?></p>
            <p class="text-sm v3-text-muted"><?= lang('Prescription.click_add_medicine') ?></p>
        </div>
    </div>
</div>

<!-- Prescribed and Expiry Dates -->
<div class="grid grid-cols-1 gap-8 md:grid-cols-2">
    <div class="group/field relative">
        <label class="mb-3 flex items-center gap-2 text-sm font-bold v3-text-ink">
            <i class="fas fa-calendar v3-accent-text"></i>
            <span><?= lang('Prescription.prescribed_date_required') ?></span>
        </label>
        <div class="relative">
            <input type="text" name="prescribed_date" data-clinic-date="1" value="<?= esc(old('prescribed_date', date('Y-m-d'))) ?>" class="prescription-date-input w-full v3-radius border-2 v3-border v3-surface py-3 transition-all duration-300 v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-input" required>
            <div class="pointer-events-none absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center">
                <i class="fas fa-calendar v3-text-muted"></i>
            </div>
        </div>
    </div>

    <div class="group/field relative">
        <label class="mb-3 flex items-center gap-2 text-sm font-bold v3-text-ink">
            <i class="fas fa-calendar-times v3-text-danger"></i>
            <span><?= lang('Prescription.expiry_date') ?></span>
        </label>
        <div class="relative">
            <input type="text" name="expiry_date" data-clinic-date="1" value="<?= esc(old('expiry_date', date('Y-m-d', strtotime('+30 days')))) ?>" class="prescription-date-input w-full v3-radius border-2 v3-border v3-surface py-3 transition-all duration-300 v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-input">
            <div class="pointer-events-none absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center">
                <i class="fas fa-calendar v3-text-muted"></i>
            </div>
        </div>
    </div>
</div>

<!-- Instructions Section -->
<div class="group/field relative">
    <label class="mb-3 flex items-center gap-2 text-sm font-bold v3-text-ink">
        <i class="fas fa-sticky-note v3-text-primary"></i>
        <span><?= lang('Prescription.instructions') ?></span>
    </label>
    <div class="relative">
        <textarea name="instructions" rows="4" class="w-full resize-none v3-radius border-2 v3-border v3-surface px-4 py-3 transition-all duration-300 v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-textarea" placeholder="<?= esc(lang('Prescription.instructions_placeholder')) ?>"><?= esc(old('instructions')) ?></textarea>
        <div class="absolute bottom-3 <?= !empty($is_rtl) ? 'left-3' : 'right-3' ?> text-xs v3-text-muted">
            <i class="fas fa-edit"></i>
        </div>
    </div>
</div>
</div>
