<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-green-50 via-blue-50 to-purple-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-4">
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-blue-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-200"></div>
                    <div class="relative w-12 h-12 bg-gradient-to-br from-green-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                        <i class="fas fa-plus text-white text-lg"></i>
                    </div>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900">Add Inventory Usage</h1>
                    <p class="text-gray-600 font-medium">Record inventory consumption for treatments</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="max-w-4xl mx-auto">
            <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-gray-500/10 border border-white/30 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <h3 class="text-lg font-semibold text-gray-900">Usage Information</h3>
                    </div>
                </div>

                <form action="<?= base_url('inventory-usage/store') ?>" method="POST" class="p-6">
                    <?= csrf_field() ?>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Inventory Item Selection -->
                        <div class="lg:col-span-2">
                            <label for="inventory_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-box text-blue-600 mr-2"></i>Inventory Item *
                            </label>
                            <select name="inventory_id" id="inventory_id" class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" required onchange="loadInventoryItem()">
                                <option value="">Select an inventory item...</option>
                                <?php foreach ($inventory_items as $item): ?>
                                    <option value="<?= $item['id'] ?>" data-quantity="<?= $item['quantity'] ?>" data-unit="<?= $item['unit'] ?? 'pieces' ?>" data-price="<?= $item['unit_price'] ?>">
                                        <?= $item['item_name'] ?> (Available: <?= $item['quantity'] ?> <?= $item['unit'] ?? 'pieces' ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="item-info" class="mt-2 p-3 bg-blue-50 rounded-lg hidden">
                                <div class="text-sm text-blue-800">
                                    <div class="flex justify-between">
                                        <span>Available Quantity:</span>
                                        <span id="available-quantity" class="font-semibold"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Unit Price:</span>
                                        <span id="unit-price" class="font-semibold"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Patient Selection -->
                        <div>
                            <label for="patient_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-user text-green-600 mr-2"></i>Patient *
                            </label>
                            <select name="patient_id" id="patient_id" class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200" required>
                                <option value="">Select a patient...</option>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?= $patient['id'] ?>">
                                        <?= $patient['first_name'] . ' ' . $patient['last_name'] ?> (ID: <?= $patient['patient_id'] ?? $patient['id'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Treatment Selection -->
                        <div>
                            <label for="treatment_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-stethoscope text-purple-600 mr-2"></i>Treatment (Optional)
                            </label>
                            <select name="treatment_id" id="treatment_id" class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                                <option value="">Select a treatment...</option>
                                <?php foreach ($treatments as $treatment): ?>
                                    <option value="<?= $treatment['id'] ?>">
                                        <?= $treatment['treatment_name'] ?> (<?= ucfirst($treatment['treatment_type']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Quantity Used -->
                        <div>
                            <label for="quantity_used" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calculator text-orange-600 mr-2"></i>Quantity Used *
                            </label>
                            <div class="relative">
                                <input type="number" name="quantity_used" id="quantity_used" step="0.01" min="0.01" class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-200" required onchange="calculateCost()">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span id="quantity-unit" class="text-gray-500 text-sm">pieces</span>
                                </div>
                            </div>
                        </div>

                        <!-- Usage Date -->
                        <div>
                            <label for="usage_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar text-indigo-600 mr-2"></i>Usage Date *
                            </label>
                            <input type="date" name="usage_date" id="usage_date" value="<?= date('Y-m-d') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200" required>
                        </div>

                        <!-- Cost Display -->
                        <div class="lg:col-span-2">
                            <div class="bg-gray-50 rounded-xl p-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-3">Cost Calculation</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="text-center">
                                        <div class="text-sm text-gray-600">Unit Price</div>
                                        <div id="display-unit-price" class="text-lg font-bold text-gray-900">$0.00</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-sm text-gray-600">Quantity</div>
                                        <div id="display-quantity" class="text-lg font-bold text-gray-900">0</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-sm text-gray-600">Total Cost</div>
                                        <div id="display-total-cost" class="text-xl font-bold text-green-600">$0.00</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="lg:col-span-2">
                            <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-sticky-note text-yellow-600 mr-2"></i>Notes
                            </label>
                            <textarea name="notes" id="notes" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200" placeholder="Additional notes about this usage..."></textarea>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-8 flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                        <a href="<?= base_url('inventory-usage') ?>" class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gray-100 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-200 transition-all duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>Cancel
                        </a>
                        
                        <button type="submit" class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                            <i class="fas fa-save mr-2"></i>Record Usage
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function loadInventoryItem() {
    const select = document.getElementById('inventory_id');
    const selectedOption = select.options[select.selectedIndex];
    const itemInfo = document.getElementById('item-info');
    const availableQuantity = document.getElementById('available-quantity');
    const unitPrice = document.getElementById('unit-price');
    const quantityUnit = document.getElementById('quantity-unit');
    
    if (selectedOption.value) {
        const quantity = selectedOption.getAttribute('data-quantity');
        const unit = selectedOption.getAttribute('data-unit');
        const price = selectedOption.getAttribute('data-price');
        
        availableQuantity.textContent = quantity + ' ' + unit;
        unitPrice.textContent = '$' + parseFloat(price).toFixed(2);
        quantityUnit.textContent = unit;
        
        itemInfo.classList.remove('hidden');
        
        // Update cost calculation
        calculateCost();
    } else {
        itemInfo.classList.add('hidden');
    }
}

function calculateCost() {
    const select = document.getElementById('inventory_id');
    const selectedOption = select.options[select.selectedIndex];
    const quantityInput = document.getElementById('quantity_used');
    
    const displayUnitPrice = document.getElementById('display-unit-price');
    const displayQuantity = document.getElementById('display-quantity');
    const displayTotalCost = document.getElementById('display-total-cost');
    
    if (selectedOption.value && quantityInput.value) {
        const unitPrice = parseFloat(selectedOption.getAttribute('data-price'));
        const quantity = parseFloat(quantityInput.value);
        const totalCost = unitPrice * quantity;
        
        displayUnitPrice.textContent = '$' + unitPrice.toFixed(2);
        displayQuantity.textContent = quantity;
        displayTotalCost.textContent = '$' + totalCost.toFixed(2);
    } else {
        displayUnitPrice.textContent = '$0.00';
        displayQuantity.textContent = '0';
        displayTotalCost.textContent = '$0.00';
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const quantityInput = document.getElementById('quantity_used');
    const inventorySelect = document.getElementById('inventory_id');
    
    form.addEventListener('submit', function(e) {
        if (inventorySelect.value && quantityInput.value) {
            const selectedOption = inventorySelect.options[inventorySelect.selectedIndex];
            const availableQuantity = parseFloat(selectedOption.getAttribute('data-quantity'));
            const requestedQuantity = parseFloat(quantityInput.value);
            
            if (requestedQuantity > availableQuantity) {
                e.preventDefault();
                alert('Insufficient stock! Available: ' + availableQuantity + ' ' + selectedOption.getAttribute('data-unit'));
                return false;
            }
        }
    });
});
</script>

<?= $this->endSection() ?>
