<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="px-6 py-6">
        <!-- Compact Page Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-200"></div>
                        <div class="relative w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                            <i class="fas fa-box text-white text-lg"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-gray-900">Item Details</h1>
                        <p class="text-gray-600 font-medium">Inventory item information</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="<?= base_url('inventory') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition-all duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back
                    </a>
                    <a href="<?= base_url('inventory/' . $item['id'] . '/edit') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white text-sm font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                </div>
            </div>
        </div> 

        <?php $creatorName = trim(($item['creator_first_name'] ?? '') . ' ' . ($item['creator_last_name'] ?? '')); ?>
        <!-- Compact Item Overview -->
        <div class="mb-6">
            <div class="backdrop-blur-xl bg-white/80 rounded-3xl shadow-2xl shadow-blue-500/10 border border-white/30 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl blur-lg opacity-75"></div>
                            <div class="relative w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-xl">
                                <?= esc(ui_badge_text($item['item_name'] ?? '', 2, 'IT')) ?>
                            </div>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-gray-900 mb-2"><?= $item['item_name'] ?></h2>
                            <div class="flex items-center space-x-3">
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?= ucfirst($item['category']) ?>
                                </span>
                                <?php
                                $status = 'in_stock';
                                $statusClass = 'bg-green-100 text-green-800';
                                $statusText = 'In Stock';
                                
                                if ($item['quantity'] == 0) {
                                    $status = 'out_of_stock';
                                    $statusClass = 'bg-red-100 text-red-800';
                                    $statusText = 'Out of Stock';
                                } elseif (isset($item['min_quantity']) && $item['quantity'] <= $item['min_quantity']) {
                                    $status = 'low_stock';
                                    $statusClass = 'bg-yellow-100 text-yellow-800';
                                    $statusText = 'Low Stock';
                                }
                                ?>
                                <span class="px-3 py-1 text-sm font-semibold rounded-full <?= $statusClass ?>">
                                    <?= $statusText ?>
                                </span>
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <?= ucfirst($item['status']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-black text-gray-900"><?= $item['quantity'] ?></div>
                        <div class="text-sm text-gray-600 font-medium">units in stock</div>
                        <div class="text-lg font-bold text-gray-900"><?= formatCurrency($item['quantity'] * $item['unit_price']) ?></div>
                        <div class="text-xs text-gray-500">total value</div>
                    </div>
                </div>
                
                <?php if (!empty($item['description'])): ?>
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-gray-600 text-sm leading-relaxed"><?= $item['description'] ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Compact Details Grid -->
        <div class="mb-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Stock Details -->
                <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-green-500/10 border border-white/30 p-4">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-boxes text-sm"></i>
                        </div>
                        <h3 class="text-lg font-black text-gray-900">Stock Details</h3>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Current</span>
                            <span class="text-lg font-bold text-gray-900"><?= $item['quantity'] ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Minimum</span>
                            <span class="text-lg font-bold text-gray-900"><?= $item['min_quantity'] ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Status</span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $statusClass ?>">
                                <?= $statusText ?>
                            </span>
                        </div>
                        
                        <?php if ($item['quantity'] <= $item['min_quantity']): ?>
                            <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-2 text-sm"></i>
                                    <span class="text-xs text-yellow-800 font-semibold">Low Stock Alert</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Financial Details -->
                <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-purple-500/10 border border-white/30 p-4">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-dollar-sign text-sm"></i>
                        </div>
                        <h3 class="text-lg font-black text-gray-900">Financial</h3>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Unit Price</span>
                            <span class="text-lg font-bold text-gray-900"><?= formatCurrency($item['unit_price']) ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Total Value</span>
                            <span class="text-lg font-bold text-gray-900"><?= formatCurrency($item['quantity'] * $item['unit_price']) ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Supplier</span>
                            <span class="text-sm text-gray-900"><?= $item['supplier'] ?: 'N/A' ?></span>
                        </div>
                    </div>
                </div>

                <!-- Location & Expiry -->
                <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-indigo-500/10 border border-white/30 p-4">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-map-marker-alt text-sm"></i>
                        </div>
                        <h3 class="text-lg font-black text-gray-900">Location</h3>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Storage</span>
                            <span class="text-sm text-gray-900"><?= $item['location'] ?: 'N/A' ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Expiry</span>
                            <span class="text-sm text-gray-900">
                                <?php if ($item['expiry_date']): ?>
                                    <?php
                                    $expiryDate = strtotime($item['expiry_date']);
                                    $today = time();
                                    $daysUntilExpiry = ceil(($expiryDate - $today) / (60 * 60 * 24));
                                    
                                    if ($daysUntilExpiry < 0) {
                                        echo '<span class="text-red-600 font-semibold">Expired</span>';
                                    } elseif ($daysUntilExpiry <= 30) {
                                        echo '<span class="text-yellow-600 font-semibold">' . $daysUntilExpiry . ' days</span>';
                                    } else {
                                        echo date('M j, Y', $expiryDate);
                                    }
                                    ?>
                                <?php else: ?>
                                    No expiry
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Item ID</span>
                            <span class="text-sm text-gray-900 font-mono">#<?= $item['id'] ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Created By</span>
                            <span class="text-sm text-gray-900"><?= $creatorName !== '' ? 'Dr. ' . esc($creatorName) : 'Unknown' ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="mb-6">
            <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-gray-500/10 border border-white/30 p-4">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-gray-500 to-gray-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                        <i class="fas fa-info-circle text-sm"></i>
                    </div>
                    <h3 class="text-lg font-black text-gray-900">System Information</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-sm text-gray-600 font-medium">Created</div>
                        <div class="text-sm text-gray-900 font-semibold">
                            <?= $item['created_at'] ? date('M j, Y', strtotime($item['created_at'])) : 'Unknown' ?>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-sm text-gray-600 font-medium">Last Updated</div>
                        <div class="text-sm text-gray-900 font-semibold">
                            <?= $item['updated_at'] ? date('M j, Y', strtotime($item['updated_at'])) : 'Unknown' ?>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-sm text-gray-600 font-medium">Item ID</div>
                        <div class="text-sm text-gray-900 font-mono font-semibold">#<?= $item['id'] ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Movement Ledger -->
        <div class="mb-6">
            <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-emerald-500/10 border border-white/30 p-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-arrows-rotate text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-gray-900">Stock Movement Ledger</h3>
                            <p class="text-xs text-gray-500">Recent receive, usage, and adjustment events for this item</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-100 text-slate-700"><?= count($movements ?? []) ?> movement(s)</span>
                </div>

                <?php if (!empty($movements)): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-slate-600 uppercase border-b border-slate-200">
                                    <th class="px-3 py-2">Date</th>
                                    <th class="px-3 py-2">Type</th>
                                    <th class="px-3 py-2">Change</th>
                                    <th class="px-3 py-2">Before → After</th>
                                    <th class="px-3 py-2">Cost</th>
                                    <th class="px-3 py-2">Reference</th>
                                    <th class="px-3 py-2">Actor</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach (($movements ?? []) as $movement): ?>
                                    <?php
                                        $qtyChange = (int) ($movement['quantity_change'] ?? 0);
                                        $changeClass = $qtyChange >= 0 ? 'text-emerald-700 bg-emerald-50' : 'text-rose-700 bg-rose-50';
                                        $type = (string) ($movement['movement_type'] ?? 'unknown');
                                        $typeLabel = ucwords(str_replace('_', ' ', $type));
                                        $actorName = trim((string) (($movement['actor_first_name'] ?? '') . ' ' . ($movement['actor_last_name'] ?? '')));
                                        $refType = (string) ($movement['reference_type'] ?? '');
                                        $refId = (int) ($movement['reference_id'] ?? 0);
                                        $refText = $refType !== '' ? ucwords(str_replace('_', ' ', $refType)) . ($refId ? (' #' . $refId) : '') : '-';
                                    ?>
                                    <tr class="hover:bg-slate-50/80">
                                        <td class="px-3 py-3 text-sm text-gray-700">
                                            <div><?= !empty($movement['created_at']) ? formatDateTime($movement['created_at']) : '-' ?></div>
                                            <?php if (!empty($movement['notes'])): ?><div class="text-xs text-gray-500 mt-1"><?= esc($movement['notes']) ?></div><?php endif; ?>
                                        </td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700"><?= esc($typeLabel) ?></span>
                                        </td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold <?= $changeClass ?>">
                                                <i class="fas <?= $qtyChange >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' ?> mr-1"></i>
                                                <?= $qtyChange >= 0 ? '+' : '' ?><?= $qtyChange ?>
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-sm text-gray-700">
                                            <?= (int) ($movement['quantity_before'] ?? 0) ?> → <span class="font-semibold text-gray-900"><?= (int) ($movement['quantity_after'] ?? 0) ?></span>
                                        </td>
                                        <td class="px-3 py-3 text-sm text-gray-700">
                                            <?php if ($movement['unit_cost'] !== null): ?>
                                                <div><?= formatCurrency((float) $movement['unit_cost']) ?>/unit</div>
                                                <?php if ($movement['total_cost'] !== null): ?><div class="text-xs text-gray-500">Total: <?= formatCurrency((float) $movement['total_cost']) ?></div><?php endif; ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-3 py-3 text-sm text-gray-700">
                                            <?= esc($refText) ?>
                                        </td>
                                        <td class="px-3 py-3 text-sm text-gray-700">
                                            <?= esc($actorName !== '' ? $actorName : '-') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-600">
                        No stock movements recorded yet for this item.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Compact Action Buttons -->
        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
            <button onclick="receiveStock(<?= $item['id'] ?>)" class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                <i class="fas fa-box-open mr-2"></i>Receive Stock
            </button>
            <button onclick="adjustStock(<?= $item['id'] ?>)" class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                <i class="fas fa-plus-minus mr-2"></i>Adjust Stock
            </button>
            
            <a href="<?= base_url('inventory/' . $item['id'] . '/edit') ?>" class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                <i class="fas fa-edit mr-2"></i>Edit Item
            </a>
            
            <button onclick="deleteItem(<?= $item['id'] ?>)" class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-red-600 to-pink-600 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                <i class="fas fa-trash mr-2"></i>Delete Item
            </button>
        </div>
    </div>
</div>

<script>
function receiveStock(id) {
    const quantity = prompt('Enter received quantity:');
    if (quantity === null) return;
    if (isNaN(quantity) || parseInt(quantity, 10) <= 0) {
        showToast('error', 'Error!', 'Please enter a valid received quantity.');
        return;
    }

    const unitCost = prompt('Enter unit cost for this receipt (leave blank to keep current unit price):', '<?= esc((string) $item['unit_price']) ?>');
    if (unitCost !== null && unitCost !== '' && (isNaN(unitCost) || parseFloat(unitCost) < 0)) {
        showToast('error', 'Error!', 'Please enter a valid unit cost.');
        return;
    }

    const reason = prompt('Reference / note for this stock receipt (optional):') || 'Stock received';

    fetch(`<?= base_url('inventory') ?>/${id}/receive`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        body: JSON.stringify({
            quantity: parseInt(quantity, 10),
            unit_cost: unitCost ?? '',
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Success!', data.message || 'Stock received successfully!');
            setTimeout(() => location.reload(), 1200);
        } else {
            showToast('error', 'Error!', data.message || 'Failed to receive stock.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Error!', 'An error occurred while receiving stock.');
    });
}

function adjustStock(id) {
    const adjustment = prompt('Enter adjustment amount (positive to add, negative to subtract):');
    if (adjustment !== null && !isNaN(adjustment)) {
        const adjustmentValue = parseInt(adjustment);
        const type = adjustmentValue >= 0 ? 'add' : 'subtract';
        const reason = prompt('Enter reason for adjustment (optional):') || 'Manual adjustment';
        
        fetch(`<?= base_url('inventory') ?>/${id}/adjust`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            body: JSON.stringify({ 
                adjustment: Math.abs(adjustmentValue),
                type: type,
                reason: reason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Success!', 'Stock adjusted successfully!');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('error', 'Error!', data.message || 'Failed to adjust stock.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Error!', 'An error occurred while adjusting the stock.');
        });
    }
}

function deleteItem(id) {
    confirmDelete('<?= base_url('inventory') ?>/' + id, 'Are you sure you want to delete this inventory item? This action cannot be undone.');
}
</script>
<?= $this->endSection() ?>
