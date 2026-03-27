<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Enhanced Treatment Edit with Advanced Design -->
<div class="min-h-screen bg-slate-50">
    <!-- Animated Background Elements -->
    <div class="container mx-auto relative z-10 px-4 py-6">

        <!-- Enhanced Treatment Form with Glassmorphism -->
        <div class="group relative">
            <div class="relative bg-white rounded-2xl shadow-2xl shadow-amber-500/10 group-hover:shadow-amber-500/20 transition-all duration-500 overflow-hidden">
                <!-- Form Header -->
                <div class="p-8 border-b border-white/20">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-tooth text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-amber-900 transition-colors duration-300"><?= lang('Treatment.edit_treatment') ?></h3>
                                <p class="text-gray-600 font-medium"><?= lang('Treatment.edit_treatment_subtitle') ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('treatment') ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-arrow-left mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Treatment.back') ?></span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-8"> 
                    <form action="<?= base_url('treatment/' . $treatment['id'] . '/update') ?>" method="POST" class="space-y-8">
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?>
                            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                        <?php endif; ?>
                        <!-- Patient Selection -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-user text-blue-600"></i>
                                <span><?= lang('Treatment.select_patient_required') ?></span>
                            </label>
                                                            <div class="relative">
                                                                <select name="patient_id" id="patientSelect" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl" 
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
                                                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                                                </div>
                                                            </div>                            <?php if ($validation && $validation->hasError('patient_id')): ?>
                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span><?= $validation->getError('patient_id') ?></span>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Treatment Type and Tooth Number -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-stethoscope text-purple-600"></i>
                                    <span><?= lang('Treatment.treatment_type_required') ?></span>
                                </label>
                                <div class="relative">
                                    <select id="treatment_type" name="treatment_type" 
                                            class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl" 
                                            data-searchable-select 
                                            data-minimum-input-length="0"
                                            data-limit="5"
                                            data-search-url="<?= base_url('api/v1/search/treatment-types') ?>"
                                            data-placeholder="<?= lang('Treatment.search_treatment_types_placeholder') ?>"
                                            required>
                                        <option value=""><?= lang('Treatment.select_treatment_type') ?></option>
                                        <?php if (!empty($treatment_types)): ?>
                                            <?php foreach ($treatment_types as $key => $value): ?>
                                                <option value="<?= $key ?>" <?= (old('treatment_type', $treatment['treatment_type']) == $key) ? 'selected' : '' ?>>
                                                    <?= $value ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if ($validation && $validation->hasError('treatment_type')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= $validation->getError('treatment_type') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-tooth text-cyan-600"></i>
                                    <span><?= lang('Treatment.tooth_number') ?></span>
                                </label>
                                <div class="relative">
                                    <input type="text" id="tooth_number" name="tooth_number" 
                                           class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all duration-300 hover:shadow-xl"
                                           value="<?= old('tooth_number', $treatment['tooth_number']) ?>" maxlength="10" placeholder="<?= lang('Treatment.tooth_number_placeholder') ?>">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-tooth text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if ($validation && $validation->hasError('tooth_number')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= $validation->getError('tooth_number') ?></span>
                                    </p>
                                <?php endif; ?>
                                <p class="text-gray-500 text-sm mt-2 flex items-center space-x-1">
                                    <i class="fas fa-info-circle"></i>
                                    <span><?= lang('Treatment.leave_empty_if_not_applicable') ?></span>
                                </p>
                            </div>
                        </div>

                        <!-- Treatment Description -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-align-left text-indigo-600"></i>
                                <span><?= lang('Treatment.treatment_description_required') ?></span>
                            </label>
                            <div class="relative">
                                <textarea id="description" name="description" rows="4" 
                                          class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl resize-none"
                                          placeholder="<?= lang('Treatment.treatment_description_placeholder') ?>" required><?= old('description', $treatment['treatment_description']) ?></textarea>
                                <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
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

                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-sticky-note text-amber-600"></i>
                                <span><?= lang('Treatment.treatment_notes') ?></span>
                            </label>
                            <div class="relative">
                                <textarea id="treatment_notes" name="treatment_notes" rows="3"
                                          class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl resize-none"
                                          placeholder="<?= lang('Treatment.optional_treatment_notes_placeholder') ?>"><?= old('treatment_notes', $treatment['treatment_notes'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Cost and Duration -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-dollar-sign text-green-600"></i>
                                    <span><?= lang('Treatment.cost') ?></span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-gray-500 font-bold"><?= getCurrencySymbol() ?></span>
                                    </div>
                                    <input type="number" id="cost" name="cost" step="0.01" min="0"
                                           class="w-full px-4 py-3 pl-8 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 hover:shadow-xl"
                                           value="<?= old('cost', $treatment['cost']) ?>" placeholder="<?= lang('Treatment.zero_amount_placeholder') ?>">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-dollar-sign text-gray-400"></i>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-2"><?= service('request')->getLocale() === 'ar' ? 'اختياري. اتركه فارغًا إذا لم يتم تسعير هذا العلاج بعد.' : 'Optional. Leave blank if this treatment is not billed yet.' ?></p>
                                <?php if ($validation && $validation->hasError('cost')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= $validation->getError('cost') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-clock text-amber-600"></i>
                                    <span><?= lang('Treatment.estimated_duration_days_required') ?></span>
                                </label>
                                <div class="relative">
                                    <input type="number" id="estimated_duration" name="estimated_duration" 
                                           class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl"
                                           value="<?= old('estimated_duration', $treatment['estimated_duration']) ?>" min="1" placeholder="<?= lang('Treatment.estimated_duration_placeholder') ?>" required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-clock text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if ($validation && $validation->hasError('estimated_duration')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= $validation->getError('estimated_duration') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Start Date -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-calendar text-rose-600"></i>
                                <span><?= lang('Treatment.start_date_required') ?></span>
                            </label>
                            <div class="relative">
                                <input type="text" id="start_date" name="start_date" data-clinic-date="1"
                                       class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 transition-all duration-300 hover:shadow-xl"
                                       value="<?= old('start_date', $treatment['start_date']) ?>" required>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-calendar text-gray-400"></i>
                                </div>
                            </div>
                            <?php if ($validation && $validation->hasError('start_date')): ?>
                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span><?= $validation->getError('start_date') ?></span>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Inventory Usage (Optional) -->
                        <div class="group/field relative">
                            <div class="bg-white/70 border border-white/30 rounded-2xl shadow-xl p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center text-white">
                                            <i class="fas fa-boxes"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-lg font-bold text-gray-900"><?= lang('Treatment.record_inventory_usage') ?></h4>
                                            <p class="text-sm text-gray-500"><?= lang('Treatment.record_inventory_usage_subtitle') ?></p>
                                        </div>
                                    </div>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="usage_enabled" id="usageEnabled" class="sr-only">
                                        <div class="toggle-track w-11 h-6 bg-gray-200 rounded-full relative transition-all duration-200">
                                            <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-all duration-200"></div>
                                        </div>
                                        <span class="ml-3 text-sm font-semibold text-gray-700"><?= lang('Treatment.enable') ?></span>
                                    </label>
                                </div>

                                <div id="usageSection" class="hidden space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Treatment.usage_date_required') ?></label>
                                            <input type="text" name="usage_date" id="usage_date" data-clinic-date="1" value="<?= date('Y-m-d') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200">
                                        </div>
                                        <div class="flex items-end">
                                            <p class="text-sm text-gray-500"><?= lang('Treatment.items_must_be_in_stock') ?></p>
                                        </div>
                                    </div>

                                    <div id="usageItemsContainer" class="space-y-4">
                                        <div class="usage-item bg-gray-50 p-4 rounded-xl border border-gray-200">
                                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Treatment.item_required') ?></label>
                                                    <select name="usage_items[0][item_id]" class="w-full px-3 py-2 border border-gray-300 rounded-lg item-select">
                                                        <option value=""><?= lang('Treatment.select_item') ?></option>
                                                        <?php foreach ($inventory_items ?? [] as $item): ?>
                                                            <option value="<?= $item['id'] ?>" data-quantity="<?= $item['quantity'] ?>" data-unit="<?= $item['unit'] ?>" data-price="<?= $item['unit_price'] ?>">
                                                                <?= $item['item_name'] ?> (<?= $item['quantity'] ?> <?= $item['unit'] ?: lang('Treatment.pieces') ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Treatment.quantity_required') ?></label>
                                                    <input type="number" name="usage_items[0][quantity]" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg quantity-input">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Treatment.unit_cost') ?></label>
                                                    <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 unit-cost" readonly>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Treatment.total_cost') ?></label>
                                                    <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 total-cost" readonly>
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
                                        <button type="button" id="addUsageItem" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700">
                                            <i class="fas fa-plus mr-2"></i><?= lang('Treatment.add_item') ?>
                                        </button>
                                        <div class="text-sm font-semibold text-gray-700">
                                            <?= lang('Treatment.total') ?>: <span id="usageGrandTotal"><?= formatCurrency(0) ?></span>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Treatment.usage_notes') ?></label>
                                        <textarea name="usage_notes" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl" placeholder="<?= lang('Treatment.optional_usage_notes_placeholder') ?>"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-12 pt-6 border-t border-white/20">
                            <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('treatment') ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-times mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Treatment.cancel') ?></span>
                            </a>
                            <button type="submit" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white text-sm font-bold rounded-xl hover:from-amber-600 hover:to-orange-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-amber-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-amber-500/20 to-orange-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-tooth mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Treatment.update_treatment') ?></span>
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
    const toggleDot = usageToggle?.parentElement?.querySelector('.dot');
    if (usageToggle) {
        usageToggle.addEventListener('change', function() {
            if (this.checked) {
                usageSection.classList.remove('hidden');
                toggleTrack?.classList.add('bg-emerald-500');
                toggleTrack?.classList.remove('bg-gray-200');
                window.applyToggleDotState?.(toggleDot, true);
            } else {
                usageSection.classList.add('hidden');
                toggleTrack?.classList.remove('bg-emerald-500');
                toggleTrack?.classList.add('bg-gray-200');
                window.applyToggleDotState?.(toggleDot, false);
            }
        });
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

});
</script>
<?= $this->endSection() ?>


