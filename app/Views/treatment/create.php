<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="v3-operational-view">
<?php $usageEnabledByOld = old('usage_enabled') || old('usage_notes') || !empty(old('usage_items')); ?>

<div class="v3-operational-view">
        <div class="v3-operational-body">

                <div class="relative">
            <div class="relative overflow-hidden">
                <!-- Form Header -->
                <div class="v3-flow-command">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4">
                            <div class="relative">

                                <div class="relative w-12 h-12 flex items-center justify-center">
                                    <i class="fas fa-tooth text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="v3-section-title transition-colors"><?= lang('Treatment.create_new_treatment') ?></h3>
                                <p class="font-medium"><?= lang('Treatment.create_new_treatment_subtitle') ?></p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <a href="<?= esc(workflow_back('treatment', $return_to ?? null)) ?>" class="relative inline-flex items-center px-6 py-3 border-2 text-sm font-bold">

                                <i class="fas fa-arrow-left rtl-back-icon mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Treatment.back') ?></span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="">
                    <form action="<?= base_url('treatment/store') ?>" method="POST" class="v3-flow-form " data-contextual-treatment-form>
                        <?= csrf_field() ?>
                        <input type="hidden" name="examination_id" value="<?= esc($selected_examination_id ?? '') ?>">
                        <input type="hidden" name="appointment_id" value="<?= esc($selected_appointment_id ?? '') ?>">
                        <?php if (!empty($return_to)): ?>
                            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                        <?php endif; ?>
                        <!-- Patient and Treatment Type -->
                        <div class="grid grid-cols-1 md:grid-cols-2">
                            <div class="relative">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-user"></i>
                                    <span><?= lang('Treatment.select_patient_required') ?></span>
                                </label>
                                <div class="relative">
                                    <select name="patient_id" id="patientSelect" class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4"
                                            data-searchable-select
                                            data-minimum-input-length="0"
                                            data-limit="5"
                                            data-search-url="<?= base_url('api/v1/search/patients') ?>"
                                            data-placeholder="<?= lang('Treatment.search_patients_placeholder') ?>"
                                            required>
                                        <option value=""><?= lang('Treatment.select_patient') ?></option>
                                        <?php if (!empty($patients)): ?>
                                            <?php foreach ($patients as $patient): ?>
                                                <option value="<?= $patient['id'] ?>" selected>
                                                    <?= $patient['first_name'] . ' ' . $patient['last_name'] ?> (<?= $patient['phone'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <?php if ($validation && $validation->hasError('patient_id')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= $validation->getError('patient_id') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="relative">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-stethoscope"></i>
                                    <span><?= lang('Treatment.treatment_type_required') ?></span>
                                </label>
                                <div class="relative">
                                    <select id="treatment_type" name="treatment_type"
                                            class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4"
                                            data-searchable-select
                                            data-minimum-input-length="0"
                                            data-limit="5"
                                            data-search-url="<?= base_url('api/v1/search/treatment-types') ?>"
                                            data-placeholder="<?= lang('Treatment.search_treatment_types_placeholder') ?>"
                                            required>
                                        <option value=""><?= lang('Treatment.select_treatment_type') ?></option>
                                        <?php if (!empty($treatment_types)): ?>
                                            <?php foreach ($treatment_types as $key => $value): ?>
                                                <option value="<?= $key ?>" <?= old('treatment_type') == $key ? 'selected' : '' ?>>
                                                    <?= $value ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <?php if ($validation && $validation->hasError('treatment_type')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= $validation->getError('treatment_type') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Tooth Number and Start Date -->
                        <div class="grid grid-cols-1 md:grid-cols-2">
                            <div class="relative">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-tooth"></i>
                                    <span><?= lang('Treatment.tooth_number') ?></span>
                                </label>
                                <div class="relative">
                                    <input type="text" id="tooth_number" name="tooth_number"
                                           class="treatment-leading-input w-full py-3 border-2 focus:outline-none focus:ring-4 focus:ring-cyan-500/20 focus:border-cyan-500"
                                           value="<?= esc(old('tooth_number', $selected_tooth_number ?? '')) ?>" maxlength="10" placeholder="<?= lang('Treatment.tooth_number_placeholder') ?>">
                                    <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                        <i class="fas fa-tooth"></i>
                                    </div>
                                </div>
                                <?php if ($validation && $validation->hasError('tooth_number')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= $validation->getError('tooth_number') ?></span>
                                    </p>
                                <?php endif; ?>
                                <p class="text-sm mt-2 flex items-center space-x-1">
                                    <i class="fas fa-info-circle"></i>
                                    <span><?= lang('Treatment.leave_empty_if_not_applicable') ?></span>
                                </p>
                            </div>

                            <div class="relative">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar text-rose-600"></i>
                                    <span><?= lang('Treatment.start_date_required') ?></span>
                                </label>
                                <div class="relative">
                                    <input type="text" id="start_date" name="start_date" data-clinic-date="1"
                                           class="treatment-date-input w-full py-3 border-2 focus:outline-none focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500"
                                           value="<?= old('start_date') ?: date('Y-m-d') ?>" required>
                                </div>
                                <?php if ($validation && $validation->hasError('start_date')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= $validation->getError('start_date') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Treatment Description -->
                        <div class="relative">
                            <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                <i class="fas fa-align-left"></i>
                                <span><?= lang('Treatment.treatment_description_required') ?></span>
                            </label>
                            <div class="relative">
                                <textarea id="description" name="description" rows="4"
                                          class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4 resize-none"
                                          placeholder="<?= lang('Treatment.treatment_description_placeholder') ?>" required><?= old('description') ?></textarea>
                                <div class="absolute bottom-3 <?= !empty($is_rtl) ? 'left-3' : 'right-3' ?> text-xs">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                            <?php if ($validation && $validation->hasError('description')): ?>
                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span><?= $validation->getError('description') ?></span>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Duration -->
                        <div class="grid grid-cols-1 md:grid-cols-2">
                            <div class="relative">
                                <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                    <i class="fas fa-clock text-amber-600"></i>
                                    <span><?= lang('Treatment.estimated_duration_days_required') ?></span>
                                </label>
                                <div class="relative">
                                    <input type="number" id="estimated_duration" name="estimated_duration"
                                           class="w-full px-4 py-3 border-2 focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500"
                                           value="<?= old('estimated_duration', 1) ?>" min="1" placeholder="<?= lang('Treatment.estimated_duration_placeholder') ?>" required>
                                </div>
                                <?php if ($validation && $validation->hasError('estimated_duration')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= $validation->getError('estimated_duration') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?= view('treatment/partials/charge_line_fields', [
                            'validation' => $validation,
                            'clinic_providers' => $clinic_providers ?? [],
                            'charge_line' => null,
                            'id_prefix' => 'create_treatment_charge',
                        ]) ?>

                        <!-- Inventory Usage (Optional) -->
                        <div class="relative">
                            <div class="border">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 flex items-center justify-center">
                                            <i class="fas fa-boxes"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-lg font-bold"><?= lang('Treatment.record_inventory_usage') ?></h4>
                                            <p class="text-sm"><?= lang('Treatment.record_inventory_usage_subtitle') ?></p>
                                        </div>
                                    </div>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="usage_enabled" id="usageEnabled" class="sr-only" <?= $usageEnabledByOld ? 'checked' : '' ?>>
                                        <div class="toggle-track w-11 h-6 relative duration-200">
                                            <div class="toggle-dot absolute left-1 top-1 w-4 h-4 duration-200"></div>
                                        </div>
                                        <span class="ml-3 text-sm font-semibold"><?= lang('Treatment.enable') ?></span>
                                    </label>
                                </div>

                                <div id="usageSection" class="<?= $usageEnabledByOld ? '' : 'hidden' ?> space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium mb-2"><?= lang('Treatment.usage_date_required') ?></label>
                                            <input type="text" name="usage_date" id="usage_date" data-clinic-date="1" value="<?= date('Y-m-d') ?>" class="treatment-date-input w-full py-3 border focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 duration-200">
                                        </div>
                                        <div class="flex items-end">
                                            <p class="text-sm"><?= lang('Treatment.items_must_be_in_stock') ?></p>
                                        </div>
                                    </div>

                                    <div id="usageItemsContainer" class="space-y-4">
                                        <div class="usage-item p-4 border">
                                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium mb-2"><?= lang('Treatment.item_required') ?></label>
                                                    <select name="usage_items[0][item_id]" class="w-full px-3 py-2 border rounded-lg item-select">
                                                        <option value=""><?= lang('Treatment.select_item') ?></option>
                                                        <?php foreach ($inventory_items ?? [] as $item): ?>
                                                            <option value="<?= $item['id'] ?>" data-quantity="<?= $item['quantity'] ?>" data-unit="<?= $item['unit'] ?>" data-price="<?= $item['unit_price'] ?>">
                                                                <?= $item['item_name'] ?> (<?= $item['quantity'] ?> <?= $item['unit'] ?: lang('Treatment.pieces') ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium mb-2"><?= lang('Treatment.quantity_required') ?></label>
                                                    <input type="number" name="usage_items[0][quantity]" min="1" class="w-full px-3 py-2 border rounded-lg quantity-input">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium mb-2"><?= lang('Treatment.unit_cost') ?></label>
                                                    <input type="text" class="w-full px-3 py-2 border rounded-lg unit-cost" readonly>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium mb-2"><?= lang('Treatment.total_cost') ?></label>
                                                    <input type="text" class="w-full px-3 py-2 border rounded-lg total-cost" readonly>
                                                </div>
                                            </div>
                                            <div class="mt-3 flex justify-end">
                                                <button type="button" class="text-red-600 hover:text-red-800 remove-usage-item hidden">
                                                    <i class="fas fa-trash mr-1"></i><?= lang('Treatment.remove') ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <button type="button" id="addUsageItem" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-sm font-semibold rounded-lg hover:bg-emerald-700">
                                            <i class="fas fa-plus mr-2"></i><?= lang('Treatment.add_item') ?>
                                        </button>
                                        <div class="text-sm font-semibold">
                                            <?= lang('Treatment.total') ?>: <span id="usageGrandTotal"><?= formatCurrency(0) ?></span>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-2"><?= lang('Treatment.usage_notes') ?></label>
                                        <textarea name="usage_notes" rows="3" class="w-full px-4 py-3 border" placeholder="<?= lang('Treatment.optional_usage_notes_placeholder') ?>"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-12 pt-6 border-t">
                            <a href="<?= esc(workflow_cancel('treatment', $return_to ?? null)) ?>" class="relative inline-flex items-center justify-center px-8 py-3 border-2 text-sm font-bold">

                                <i class="fas fa-times mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Treatment.cancel') ?></span>
                            </a>
                            <button type="submit" class="relative inline-flex items-center justify-center px-8 py-3 text-sm font-bold">

                                <i class="fas fa-tooth mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Treatment.create_treatment') ?></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-fill today's date if start_date is empty
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    if (!startDateInput.value) {
        const todayIso = new Date().toISOString().split('T')[0];
        startDateInput.value = window.formatClinicDateFromIso ? window.formatClinicDateFromIso(todayIso) : todayIso;
    }

    // Inventory usage toggle
    const usageToggle = document.getElementById('usageEnabled');
    const usageSection = document.getElementById('usageSection');
    const toggleTrack = usageToggle?.parentElement?.querySelector('.toggle-track');
    const toggleDot = usageToggle?.parentElement?.querySelector('.toggle-dot');
    function syncUsageToggleState() {
        if (!usageToggle) {
            return;
        }

        if (usageToggle.checked) {
            usageSection?.classList.remove('hidden');
            toggleTrack?.classList.add('bg-emerald-500');
            toggleTrack?.classList.remove('bg-gray-200');
            window.applyToggleDotState?.(toggleDot, true);
        } else {
            usageSection?.classList.add('hidden');
            toggleTrack?.classList.remove('bg-emerald-500');
            toggleTrack?.classList.add('bg-gray-200');
            window.applyToggleDotState?.(toggleDot, false);
        }
    }

    if (usageToggle) {
        usageToggle.addEventListener('change', syncUsageToggleState);
        syncUsageToggleState();
    }

    let usageItemCount = 1;
    const usageContainer = document.getElementById('usageItemsContainer');
    const addUsageItemBtn = document.getElementById('addUsageItem');
    const usageTotalEl = document.getElementById('usageGrandTotal');

    const formatCurrency = (amount) => {
        if (isNaN(amount)) return '<?= settings()->getCurrencySymbol() ?>0.00';
        return '<?= settings()->getCurrencySymbol() ?>' + parseFloat(amount).toFixed(2);
    };

    const calculateUsageTotal = () => {
        let total = 0;
        usageContainer.querySelectorAll('.usage-item').forEach(row => {
            const cost = parseFloat(row.querySelector('.total-cost')?.dataset?.value || 0);
            total += cost;
        });
        usageTotalEl.textContent = formatCurrency(total);
    };

    const bindUsageRow = (row) => {
        const select = row.querySelector('.item-select');
        const quantity = row.querySelector('.quantity-input');
        const unitCost = row.querySelector('.unit-cost');
        const totalCost = row.querySelector('.total-cost');

        const updateRow = () => {
            const option = select.options[select.selectedIndex];
            const price = parseFloat(option?.getAttribute('data-price') || 0);
            const qty = parseFloat(quantity.value || 0);
            unitCost.value = price ? formatCurrency(price) : '';
            const rowTotal = price * qty;
            totalCost.value = rowTotal ? formatCurrency(rowTotal) : '';
            totalCost.dataset.value = rowTotal || 0;
            calculateUsageTotal();
        };

        select.addEventListener('change', updateRow);
        quantity.addEventListener('input', updateRow);
    };

    usageContainer.querySelectorAll('.usage-item').forEach(bindUsageRow);

    addUsageItemBtn?.addEventListener('click', () => {
        const baseRow = usageContainer.querySelector('.usage-item');
        if (!baseRow) return;
        const newRow = baseRow.cloneNode(true);
        newRow.querySelectorAll('select, input').forEach(field => {
            if (field.name) {
                field.name = field.name.replace('[0]', '[' + usageItemCount + ']');
            }
            field.value = '';
            if (field.classList.contains('total-cost')) {
                field.dataset.value = '0';
            }
        });
        newRow.querySelector('.remove-usage-item').classList.remove('hidden');
        usageContainer.appendChild(newRow);
        usageItemCount++;
        bindUsageRow(newRow);
    });

    document.addEventListener('click', (e) => {
        if (e.target.closest('.remove-usage-item')) {
            e.preventDefault();
            e.target.closest('.usage-item').remove();
            calculateUsageTotal();
        }
    });

    // Add some basic form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const duration = parseInt(document.getElementById('estimated_duration').value);

        if (duration <= 0) {
            alert('<?= esc(lang('Treatment.alert_valid_duration_gt_zero_days'), 'js') ?>');
            e.preventDefault();
            return false;
        }
    });
});
</script>
</div>
<?= $this->endSection() ?>



