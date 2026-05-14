<?php
$idPrefix = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($id_prefix ?? ''));
$fieldId = static function (string $id) use ($idPrefix): string {
    return $idPrefix !== '' ? $idPrefix . '_' . $id : $id;
};
?>
<div class="space-y-6">
<!-- Patient Selection -->
<div class="group/field relative">
    <label class="mb-3 flex items-center gap-2 text-sm font-bold text-gray-700">
        <i class="fas fa-user text-emerald-600"></i>
        <span><?= lang('Prescription.patient_required') ?></span>
    </label>
    <div class="relative">
        <select name="patient_id" id="<?= esc($fieldId('patientSelect')) ?>" class="w-full rounded-xl border-2 border-gray-200 bg-white/80 px-4 py-3 shadow-lg transition-all duration-300 hover:shadow-xl focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/20"
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
            <i class="fas fa-chevron-down text-gray-400"></i>
        </div>
    </div>
</div>

<!-- Medicines Section -->
<div class="group/section relative">
    <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-blue-500/5 to-cyan-500/5 opacity-0 blur transition-opacity duration-300 group-hover/section:opacity-100"></div>
    <div class="relative rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50/50 to-cyan-50/50 p-6">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600">
                    <i class="fas fa-pills text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900"><?= lang('Prescription.medicines') ?></h3>
            </div>
            <button type="button" data-prescription-add-medicine class="group/btn relative inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-blue-500 to-cyan-600 px-4 py-2 text-sm font-bold text-white transition-all duration-300 hover:scale-105 hover:from-blue-600 hover:to-cyan-700">
                <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-500/20 to-cyan-600/20 opacity-0 blur transition-opacity duration-300 group-hover/btn:opacity-100"></div>
                <i class="fas fa-plus me-2 relative z-10"></i>
                <span class="relative z-10"><?= lang('Prescription.add_medicine') ?></span>
            </button>
        </div>

        <div data-prescription-medicines-container class="space-y-4"></div>

        <div data-prescription-empty-medicines class="py-8 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-100 to-cyan-200">
                <i class="fas fa-pills text-2xl text-blue-500"></i>
            </div>
            <p class="font-medium text-gray-600"><?= lang('Prescription.no_medicines_added') ?></p>
            <p class="text-sm text-gray-500"><?= lang('Prescription.click_add_medicine') ?></p>
        </div>
    </div>
</div>

<!-- Prescribed and Expiry Dates -->
<div class="grid grid-cols-1 gap-8 md:grid-cols-2">
    <div class="group/field relative">
        <label class="mb-3 flex items-center gap-2 text-sm font-bold text-gray-700">
            <i class="fas fa-calendar text-indigo-600"></i>
            <span><?= lang('Prescription.prescribed_date_required') ?></span>
        </label>
        <div class="relative">
            <input type="text" name="prescribed_date" data-clinic-date="1" value="<?= esc(old('prescribed_date', date('Y-m-d'))) ?>" class="prescription-date-input w-full rounded-xl border-2 border-gray-200 bg-white/80 py-3 shadow-lg transition-all duration-300 hover:shadow-xl focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/20" required>
            <div class="pointer-events-none absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center">
                <i class="fas fa-calendar text-gray-400"></i>
            </div>
        </div>
    </div>

    <div class="group/field relative">
        <label class="mb-3 flex items-center gap-2 text-sm font-bold text-gray-700">
            <i class="fas fa-calendar-times text-red-600"></i>
            <span><?= lang('Prescription.expiry_date') ?></span>
        </label>
        <div class="relative">
            <input type="text" name="expiry_date" data-clinic-date="1" value="<?= esc(old('expiry_date', date('Y-m-d', strtotime('+30 days')))) ?>" class="prescription-date-input w-full rounded-xl border-2 border-gray-200 bg-white/80 py-3 shadow-lg transition-all duration-300 hover:shadow-xl focus:border-red-500 focus:outline-none focus:ring-4 focus:ring-red-500/20">
            <div class="pointer-events-none absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center">
                <i class="fas fa-calendar text-gray-400"></i>
            </div>
        </div>
    </div>
</div>

<!-- Instructions Section -->
<div class="group/field relative">
    <label class="mb-3 flex items-center gap-2 text-sm font-bold text-gray-700">
        <i class="fas fa-sticky-note text-teal-600"></i>
        <span><?= lang('Prescription.instructions') ?></span>
    </label>
    <div class="relative">
        <textarea name="instructions" rows="4" class="w-full resize-none rounded-xl border-2 border-gray-200 bg-white/80 px-4 py-3 shadow-lg transition-all duration-300 hover:shadow-xl focus:border-teal-500 focus:outline-none focus:ring-4 focus:ring-teal-500/20" placeholder="<?= esc(lang('Prescription.instructions_placeholder')) ?>"><?= esc(old('instructions')) ?></textarea>
        <div class="absolute bottom-3 <?= !empty($is_rtl) ? 'left-3' : 'right-3' ?> text-xs text-gray-400">
            <i class="fas fa-edit"></i>
        </div>
    </div>
</div>
</div>
