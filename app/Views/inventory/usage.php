<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php $tr = static fn(string $key, array $params = []) => lang('Inventory.' . $key, $params); ?>
<div class="v3-operational-view min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-4">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
                <div class="flex items-center space-x-4">
                    <div class="relative group">

                        <div class="relative w-12 h-12 v3-radius flex items-center justify-center group-hover:rotate-2 transition-all duration-200">
                            <i class="fas fa-clipboard-list v3-text-on-primary text-lg"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black v3-text-ink"><?= lang('Inventory.inventoryUsage') ?></h1>
                        <p class="v3-text-muted font-medium"><?= lang('Inventory.recordItemConsumptionForTreatments') ?></p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="<?= base_url() ?>inventory" class="inline-flex items-center px-4 py-2 v3-bg-soft v3-text-ink text-sm font-semibold rounded-lg v3-hover-soft transition-all duration-200 v3-btn v3-btn--secondary">
                        <i class="fas fa-arrow-left mr-2"></i><?= lang('Inventory.backToInventory') ?>
                    </a>
                    <a href="<?= base_url() ?>inventory/usage-history" class="inline-flex items-center px-4 py-2 v3-text-on-primary text-sm font-semibold rounded-lg transition-all duration-200 v3-btn v3-btn--secondary">
                        <i class="fas fa-history mr-2"></i><?= lang('Inventory.usageHistory') ?>
                    </a>
                </div>
            </div>
        </div>

        <?php $financeAutoPostInventoryCogs = (int) settings()->get('finance_auto_post_inventory_cogs', 1) === 1; ?>
        <div class="mb-4">
            <div class="v3-surface v3-radius border v3-border p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start space-x-3">
                        <div class="w-10 h-10 v3-radius flex items-center justify-center <?= $financeAutoPostInventoryCogs ? 'v3-bg-paid v3-text-primary' : 'v3-bg-waiting v3-text-warning' ?>">
                            <i class="fas <?= $financeAutoPostInventoryCogs ? 'fa-file-invoice-dollar' : 'fa-toggle-off' ?>"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold v3-text-ink"><?= lang('Inventory.financeAutoPostInventoryCogs') ?></h2>
                            <p class="text-sm v3-text-muted">
                                <?= $financeAutoPostInventoryCogs
                                    ? lang('Inventory.financeAutoPostEnabledDescription')
                                    : lang('Inventory.financeAutoPostDisabledDescription') ?>
                            </p>
                        </div>
                    </div>
                    <div class="shrink-0">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?= $financeAutoPostInventoryCogs ? 'v3-bg-paid v3-text-primary' : 'v3-bg-waiting v3-text-warning' ?>">
                            <span class="w-2 h-2 rounded-full mr-2 <?= $financeAutoPostInventoryCogs ? 'v3-bg-primary' : 'v3-bg-warning' ?>"></span>
                            <?= $financeAutoPostInventoryCogs ? lang('Inventory.enabled') : lang('Inventory.disabled') ?>
                        </span>
                    </div>
                </div>
                <div class="mt-3 text-xs v3-text-muted">
                    <?= lang('Inventory.changeThisIn') ?> <a href="<?= base_url('settings') ?>" class="font-semibold v3-accent-text v3-hover-text-primary"><?= lang('Inventory.settingsSystemPreference') ?></a>.
                </div>
            </div>
        </div>

        <!-- Usage Form -->
        <div class="mb-4">
            <div class="v3-surface v3-radius border v3-border p-4">
                <form action="<?= base_url() ?>inventory/record-usage" method="POST" id="usageForm">
                    <?= csrf_field() ?>

                    <!-- Treatment Selection -->
                    <div class="mb-4">
                        <h3 class="text-lg font-bold v3-text-ink mb-4 flex items-center">
                            <i class="fas fa-user-md v3-accent-text mr-2"></i>
                            <?= lang('Inventory.treatmentInformationOptional') ?>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-start">
                            <div class="flex flex-col h-full">
                                <label for="treatment_id" class="block text-sm font-medium v3-text-ink mb-2"><?= lang('Inventory.selectTreatmentOptional') ?></label>
                                <select name="treatment_id" id="treatment_id" data-force-select2 data-allow-clear="false" class="w-full px-4 py-3 border v3-border rounded-lg focus:ring-2 v3-focus-control v3-focus-control transition-all duration-200 v3-select">
                                    <option value=""><?= lang('Inventory.chooseTreatment') ?></option>
                                    <?php if (!empty($treatments)): ?>
                                        <?php foreach ($treatments as $treatment): ?>
                                            <option value="<?= $treatment['id'] ?>"
                                                    data-patient="<?= $treatment['first_name'] . ' ' . $treatment['last_name'] ?>"
                                                    data-treatment="<?= $treatment['treatment_name'] ?>">
                                                <?= $treatment['treatment_id'] ?> - <?= $treatment['first_name'] . ' ' . $treatment['last_name'] ?> (<?= $treatment['treatment_name'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled><?= lang('Inventory.noTreatmentsFound') ?></option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="flex flex-col h-full">
                                <label for="usage_date" class="block text-sm font-medium v3-text-ink mb-2"><?= lang('Inventory.usageDateRequired') ?></label>
                                <input type="text" name="usage_date" id="usage_date" data-clinic-date="1" value="<?= date('Y-m-d') ?>" class="w-full px-4 py-3 border v3-border rounded-lg focus:ring-2 v3-focus-control v3-focus-control transition-all duration-200 v3-input" required>
                            </div>
                        </div>
                        <p class="text-sm v3-text-muted mt-3"><?= lang('Inventory.linkUsageToTreatmentHelp') ?></p>

                        <!-- Treatment Details Display -->
                        <div id="treatmentDetails" class="mt-4 p-4 v3-soft-surface rounded-lg hidden">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 v3-soft-surface rounded-lg flex items-center justify-center v3-text-on-primary">
                                    <i class="fas fa-user-md"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium v3-text-muted"><?= esc($tr('patient')) ?></p>
                                    <p class="text-lg font-bold v3-text-ink" id="patientName">-</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium v3-text-muted"><?= esc($tr('treatment')) ?></p>
                                    <p class="text-lg font-bold v3-text-ink" id="treatmentName">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Selection -->
                    <div class="mb-4">
                        <h3 class="text-lg font-bold v3-text-ink mb-4 flex items-center">
                            <i class="fas fa-boxes v3-text-primary mr-2"></i>
                            <?= lang('Inventory.itemsUsed') ?>
                        </h3>

                        <div class="space-y-4" id="itemsContainer">
                            <div class="item-row v3-surface p-4 rounded-lg border v3-border">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium v3-text-ink mb-2"><?= lang('Inventory.itemRequired') ?></label>
                                        <select name="items[0][item_id]" data-force-select2 data-allow-clear="false" class="w-full px-3 py-2 border v3-border rounded-lg focus:ring-2 v3-focus-control v3-focus-control item-select v3-select" required>
                                            <option value=""><?= lang('Inventory.selectItem') ?></option>
                                            <?php foreach ($inventory_items as $item): ?>
                                                <option value="<?= $item['id'] ?>"
                                                        data-quantity="<?= $item['quantity'] ?>"
                                                        data-unit="<?= $item['unit'] ?>"
                                                        data-price="<?= $item['unit_price'] ?>">
                                                    <?= $item['item_name'] ?> (<?= lang('Inventory.available') ?>: <?= $item['quantity'] ?> <?= $item['unit'] ?: lang('Inventory.unitPiecesLower') ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium v3-text-ink mb-2"><?= esc($tr('quantity')) ?> *</label>
                                        <input type="number" name="items[0][quantity]" min="1" class="w-full px-3 py-2 border v3-border rounded-lg focus:ring-2 v3-focus-control v3-focus-control quantity-input v3-input" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium v3-text-ink mb-2"><?= lang('Inventory.unitCost') ?></label>
                                        <input type="text" class="w-full px-3 py-2 border v3-border rounded-lg v3-bg-soft unit-cost v3-input" readonly>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium v3-text-ink mb-2"><?= esc($tr('totalCost')) ?></label>
                                        <input type="text" class="w-full px-3 py-2 border v3-border rounded-lg v3-bg-soft total-cost v3-input" readonly>
                                    </div>
                                </div>
                                <div class="mt-3 flex justify-end">
                                    <button type="button" class="v3-text-danger v3-hover-text-danger remove-item hidden v3-btn v3-btn--danger">
                                        <i class="fas fa-trash mr-1"></i><?= esc($tr('removeItem')) ?>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="button" id="addItem" class="inline-flex items-center px-4 py-2 v3-bg-primary v3-text-on-primary text-sm font-medium rounded-lg v3-hover-primary transition-all duration-200 v3-btn v3-btn--primary">
                                <i class="fas fa-plus mr-2"></i><?= esc($tr('addAnotherItem')) ?>
                            </button>
                        </div>

                        <!-- Total Cost Display -->
                        <div class="mt-6 p-4 v3-bg-paid rounded-lg border v3-border-default">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-medium v3-text-ink"><?= lang('Inventory.totalMaterialCost') ?>:</span>
                                <span class="text-2xl font-bold v3-text-primary" id="grandTotal"><?= formatCurrency(0) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-4">
                        <h3 class="text-lg font-bold v3-text-ink mb-4 flex items-center">
                            <i class="fas fa-sticky-note v3-text-warning mr-2"></i>
                            <?= lang('Inventory.additionalNotes') ?>
                        </h3>
                        <textarea name="notes" rows="4" class="w-full px-4 py-3 border v3-border rounded-lg focus:ring-2 v3-focus-control v3-focus-control transition-all duration-200 v3-textarea" placeholder="<?= esc($tr('additionalNotesPlaceholder')) ?>"></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <a href="<?= base_url() ?>inventory" class="inline-flex items-center px-6 py-3 v3-bg-soft v3-text-ink text-sm font-semibold rounded-lg v3-hover-soft transition-all duration-200 v3-btn v3-btn--secondary">
                            <?= esc($tr('cancel')) ?>
                        </a>
                        <button type="submit" class="inline-flex items-center px-8 py-3 v3-text-on-primary text-sm font-bold rounded-lg transition-all duration-200 v3-btn v3-btn--primary">
                            <i class="fas fa-save mr-2"></i><?= lang('Inventory.recordUsage') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let itemCount = 1;

// Currency formatting function
function formatCurrency(amount) {
    if (isNaN(amount)) return '0.00';
    return '<?= settings()->getCurrencySymbol() ?>' + parseFloat(amount).toFixed(2);
}

// Treatment selection handler
document.getElementById('treatment_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const patientName = selectedOption.getAttribute('data-patient');
    const treatmentName = selectedOption.getAttribute('data-treatment');
    const detailsDiv = document.getElementById('treatmentDetails');

    if (this.value) {
        document.getElementById('patientName').textContent = patientName;
        document.getElementById('treatmentName').textContent = treatmentName;
        detailsDiv.classList.remove('hidden');
    } else {
        detailsDiv.classList.add('hidden');
    }
});

// Add item button handler
document.getElementById('addItem').addEventListener('click', function() {
    const container = document.getElementById('itemsContainer');
    const newItemRow = document.querySelector('.item-row').cloneNode(true);

    // Update form field names
    newItemRow.querySelectorAll('select, input').forEach(field => {
        if (field.name) {
            field.name = field.name.replace('[0]', '[' + itemCount + ']');
        }
        field.value = '';
    });

    // Show remove button
    newItemRow.querySelector('.remove-item').classList.remove('hidden');

    container.appendChild(newItemRow);
    itemCount++;

    // Add event listeners to new row
    addItemEventListeners(newItemRow);
});

// Remove item handler
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-item')) {
        e.target.closest('.item-row').remove();
        calculateTotal();
    }
});

// Add event listeners to existing items
document.querySelectorAll('.item-row').forEach(row => {
    addItemEventListeners(row);
});

function addItemEventListeners(row) {
    const itemSelect = row.querySelector('.item-select');
    const quantityInput = row.querySelector('.quantity-input');
    const unitCostInput = row.querySelector('.unit-cost');
    const totalCostInput = row.querySelector('.total-cost');

    // Item selection handler
    itemSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const price = selectedOption.getAttribute('data-price');
        const maxQuantity = selectedOption.getAttribute('data-quantity');

        console.log('Item selected:', this.value, 'Price:', price, 'Max Quantity:', maxQuantity);

        if (this.value) {
            const formattedPrice = formatCurrency(parseFloat(price));
            console.log('Setting unit cost to:', formattedPrice);
            unitCostInput.value = formattedPrice;
            quantityInput.max = maxQuantity;
            quantityInput.placeholder = `<?= lang('Inventory.max') ?>: ${maxQuantity}`;
            calculateItemTotal(row);
        } else {
            unitCostInput.value = '';
            totalCostInput.value = '';
        }
    });

    // Quantity change handler
    quantityInput.addEventListener('input', function() {
        calculateItemTotal(row);
    });
}

function calculateItemTotal(row) {
    const quantityInput = row.querySelector('.quantity-input');
    const unitCostInput = row.querySelector('.unit-cost');
    const totalCostInput = row.querySelector('.total-cost');
    const itemSelect = row.querySelector('.item-select');

    const quantity = parseFloat(quantityInput.value) || 0;
    const unitCost = parseFloat(unitCostInput.value.replace(/[^0-9.-]/g, '')) || 0;

    console.log('Calculating total - Quantity:', quantity, 'Unit Cost:', unitCost, 'Unit Cost Raw:', unitCostInput.value);

    if (quantity > 0 && unitCost > 0) {
        const total = quantity * unitCost;
        const formattedTotal = formatCurrency(total);
        console.log('Setting total cost to:', formattedTotal);
        totalCostInput.value = formattedTotal;

        // Validate quantity against available stock
        const selectedOption = itemSelect.options[itemSelect.selectedIndex];
        const maxQuantity = parseInt(selectedOption.getAttribute('data-quantity'));

        if (quantity > maxQuantity) {
            quantityInput.setCustomValidity(`<?= lang('Inventory.insufficientStockAvailable') ?>: ${maxQuantity}`);
            quantityInput.reportValidity();
        } else {
            quantityInput.setCustomValidity('');
        }
    } else {
        totalCostInput.value = '';
    }

    calculateTotal();
}

function calculateTotal() {
    const totalCosts = document.querySelectorAll('.total-cost');
    let grandTotal = 0;

    totalCosts.forEach(cost => {
        const value = parseFloat(cost.value.replace(/[^0-9.-]/g, '')) || 0;
        grandTotal += value;
    });

    document.getElementById('grandTotal').textContent = formatCurrency(grandTotal);
}

// Form submission handler
document.getElementById('usageForm').addEventListener('submit', function(e) {
    const treatmentId = document.getElementById('treatment_id').value;
    const items = document.querySelectorAll('.item-select');
    let hasValidItems = false;

    items.forEach(item => {
        if (item.value) {
            hasValidItems = true;
        }
    });

    // Treatment is now optional, so we don't need to validate it

    if (!hasValidItems) {
        e.preventDefault();
        alert('<?= lang('Inventory.pleaseSelectAtLeastOneItem') ?>');
        return;
    }

    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const loadingIcon = document.createElement('i');
    loadingIcon.className = 'fas fa-spinner fa-spin mr-2';
    submitBtn.replaceChildren(loadingIcon, document.createTextNode('<?= lang('Inventory.recording') ?>...'));
    submitBtn.disabled = true;
});
</script>

<?= $this->endSection() ?>
