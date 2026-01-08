<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
                <div class="flex items-center space-x-4">
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-200"></div>
                        <div class="relative w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                            <i class="fas fa-clipboard-list text-white text-lg"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-gray-900">Inventory Usage</h1>
                        <p class="text-gray-600 font-medium">Record item consumption for treatments</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="<?= base_url() ?>inventory" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition-all duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Inventory
                    </a>
                    <a href="<?= base_url() ?>inventory/usage-history" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white text-sm font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                        <i class="fas fa-history mr-2"></i>Usage History
                    </a>
                </div>
            </div>
        </div>

        <!-- Usage Form -->
        <div class="mb-8">
            <div class="backdrop-blur-xl bg-white/80 rounded-3xl shadow-2xl shadow-blue-500/10 border border-white/30 p-8">
                <form action="<?= base_url() ?>inventory/record-usage" method="POST" id="usageForm">
                    <?= csrf_field() ?>
                    
                    <!-- Treatment Selection -->
                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-user-md text-blue-600 mr-2"></i>
                            Treatment Information (Optional)
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="treatment_id" class="block text-sm font-medium text-gray-700 mb-2">Select Treatment (Optional)</label>
                                <p class="text-sm text-gray-500 mb-3">Link this usage to a specific treatment, or leave blank for general usage</p>
                                <select name="treatment_id" id="treatment_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    <option value="">Choose a treatment...</option>
                                    <?php if (!empty($treatments)): ?>
                                        <?php foreach ($treatments as $treatment): ?>
                                            <option value="<?= $treatment['id'] ?>" 
                                                    data-patient="<?= $treatment['first_name'] . ' ' . $treatment['last_name'] ?>"
                                                    data-treatment="<?= $treatment['treatment_name'] ?>">
                                                <?= $treatment['treatment_id'] ?> - <?= $treatment['first_name'] . ' ' . $treatment['last_name'] ?> (<?= $treatment['treatment_name'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>No treatments found</option>
                                    <?php endif; ?>
                                </select>
                                
                                <!-- Debug info -->
                                <div class="mt-2 text-sm text-gray-500">
                                    Debug: <?= count($treatments) ?> treatments found
                                </div>
                            </div>
                            <div>
                                <label for="usage_date" class="block text-sm font-medium text-gray-700 mb-2">Usage Date *</label>
                                <input type="date" name="usage_date" id="usage_date" value="<?= date('Y-m-d') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" required>
                            </div>
                        </div>
                        
                        <!-- Treatment Details Display -->
                        <div id="treatmentDetails" class="mt-4 p-4 bg-blue-50 rounded-lg hidden">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center text-white">
                                    <i class="fas fa-user-md"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Patient</p>
                                    <p class="text-lg font-bold text-gray-900" id="patientName">-</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Treatment</p>
                                    <p class="text-lg font-bold text-gray-900" id="treatmentName">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Selection -->
                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-boxes text-green-600 mr-2"></i>
                            Items Used
                        </h3>
                        
                        <div class="space-y-4" id="itemsContainer">
                            <div class="item-row bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Item *</label>
                                        <select name="items[0][item_id]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 item-select" required>
                                            <option value="">Select item...</option>
                                            <?php foreach ($inventory_items as $item): ?>
                                                <option value="<?= $item['id'] ?>" 
                                                        data-quantity="<?= $item['quantity'] ?>"
                                                        data-unit="<?= $item['unit'] ?>"
                                                        data-price="<?= $item['unit_price'] ?>">
                                                    <?= $item['item_name'] ?> (Available: <?= $item['quantity'] ?> <?= $item['unit'] ?: 'pieces' ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
                                        <input type="number" name="items[0][quantity]" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 quantity-input" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Unit Cost</label>
                                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 unit-cost" readonly>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Total Cost</label>
                                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 total-cost" readonly>
                                    </div>
                                </div>
                                <div class="mt-3 flex justify-end">
                                    <button type="button" class="text-red-600 hover:text-red-800 remove-item hidden">
                                        <i class="fas fa-trash mr-1"></i>Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="button" id="addItem" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-all duration-200">
                                <i class="fas fa-plus mr-2"></i>Add Another Item
                            </button>
                        </div>
                        
                        <!-- Total Cost Display -->
                        <div class="mt-6 p-4 bg-green-50 rounded-lg border border-green-200">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-medium text-gray-700">Total Material Cost:</span>
                                <span class="text-2xl font-bold text-green-600" id="grandTotal"><?= formatCurrency(0) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-sticky-note text-amber-600 mr-2"></i>
                            Additional Notes
                        </h3>
                        <textarea name="notes" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" placeholder="Add any additional notes about this usage..."></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <a href="<?= base_url() ?>inventory" class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition-all duration-200">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white text-sm font-bold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                            <i class="fas fa-save mr-2"></i>Record Usage
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
            quantityInput.placeholder = `Max: ${maxQuantity}`;
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
            quantityInput.setCustomValidity(`Insufficient stock. Available: ${maxQuantity}`);
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
        alert('Please select at least one item.');
        return;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Recording...';
    submitBtn.disabled = true;
});
</script>

<?= $this->endSection() ?>
