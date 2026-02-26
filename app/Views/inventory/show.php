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

        <!-- Supplier Procurement Mapping -->
        <div class="mb-6">
            <div class="grid grid-cols-1 xl:grid-cols-5 gap-4">
                <div class="xl:col-span-3 backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-amber-500/10 border border-white/30 p-4">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                                <i class="fas fa-truck-ramp-box text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-gray-900">Supplier Mappings</h3>
                                <p class="text-xs text-gray-500">Preferred vendors, pack sizes, and reorder defaults for this item</p>
                            </div>
                        </div>
                        <span class="text-xs font-semibold px-3 py-1 rounded-full bg-amber-100 text-amber-800"><?= count($supplier_links ?? []) ?> supplier(s)</span>
                    </div>

                    <?php if (!empty($supplier_links)): ?>
                        <div class="space-y-3">
                            <?php foreach (($supplier_links ?? []) as $link): ?>
                                <div class="rounded-xl border border-slate-200 bg-white/70 p-3">
                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-slate-900"><?= esc($link['supplier_name'] ?? ('Supplier #' . (int) $link['supplier_id'])) ?></span>
                                                <?php if ((int) ($link['is_preferred'] ?? 0) === 1): ?>
                                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">Preferred</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mt-1 text-xs text-slate-600">
                                                <?php if (!empty($link['supplier_sku'])): ?>SKU: <?= esc($link['supplier_sku']) ?><?php endif; ?>
                                                <?php if (!empty($link['pack_size_label'])): ?><?= !empty($link['supplier_sku']) ? ' • ' : '' ?>Pack: <?= esc($link['pack_size_label']) ?><?php endif; ?>
                                                <?php if (!empty($link['preferred_order_qty'])): ?> • Preferred Qty: <?= (int) $link['preferred_order_qty'] ?><?php endif; ?>
                                            </div>
                                            <div class="mt-1 text-xs text-slate-500">
                                                <?php if (!empty($link['last_unit_price'])): ?>Last Price: <?= esc($link['currency'] ?? 'ILS') ?> <?= number_format((float) $link['last_unit_price'], 2) ?><?php endif; ?>
                                                <?php if (!empty($link['phone'])): ?> • Phone: <?= esc($link['phone']) ?><?php endif; ?>
                                                <?php if (!empty($link['whatsapp'])): ?> • WhatsApp: <?= esc($link['whatsapp']) ?><?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" onclick="addItemToReorder(<?= (int) $item['id'] ?>, <?= (int) ($link['preferred_order_qty'] ?? 0) ?>)" class="inline-flex items-center px-3 py-2 rounded-lg bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600">
                                                <i class="fas fa-cart-plus mr-2"></i>Add to Reorder
                                            </button>
                                            <form method="post" action="<?= base_url('inventory/' . (int) $item['id'] . '/supplier-link/' . (int) $link['id'] . '/delete') ?>" onsubmit="return confirm('Remove this supplier mapping?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="inline-flex items-center px-3 py-2 rounded-lg bg-rose-50 text-rose-700 text-xs font-semibold hover:bg-rose-100">
                                                    <i class="fas fa-trash mr-2"></i>Remove
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-600">
                            No supplier mappings yet. Add one to speed up reordering and supplier contact workflows.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="xl:col-span-2 backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-amber-500/10 border border-white/30 p-4">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-yellow-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-link text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-gray-900">Add / Update Mapping</h3>
                            <p class="text-xs text-gray-500">Map this item to a supplier and set default reorder values</p>
                        </div>
                    </div>
                    <form method="post" action="<?= base_url('inventory/' . (int) $item['id'] . '/supplier-link') ?>" class="space-y-3">
                        <?= csrf_field() ?>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Supplier</label>
                            <select name="link_supplier_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-white">
                                <option value="">Select supplier</option>
                                <?php foreach (($supplier_directory ?? []) as $supplier): ?>
                                    <option value="<?= (int) $supplier['id'] ?>"><?= esc($supplier['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="supplier_sku" class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-white" placeholder="Supplier SKU">
                            <input type="text" name="pack_size_label" class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-white" placeholder="Pack size">
                            <input type="number" min="1" step="1" name="preferred_order_qty" class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-white" placeholder="Preferred qty">
                            <div class="grid grid-cols-3 gap-2">
                                <input type="number" min="0" step="0.01" name="last_unit_price" class="col-span-2 w-full px-3 py-2 border border-slate-300 rounded-lg bg-white" placeholder="Last price">
                                <select name="link_currency" class="w-full px-2 py-2 border border-slate-300 rounded-lg bg-white text-sm">
                                    <?php foreach (($currencies ?? ['ILS']) as $currency): ?>
                                        <option value="<?= esc($currency) ?>"><?= esc($currency) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <textarea name="link_notes" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-white" placeholder="Notes (optional)"></textarea>
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="is_preferred" value="1" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            Set as preferred supplier
                        </label>
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-lg bg-gradient-to-r from-amber-500 to-orange-600 text-white text-sm font-semibold shadow hover:shadow-lg">
                            <i class="fas fa-save mr-2"></i>Save Supplier Mapping
                        </button>
                    </form>
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

        <!-- Batch / Lot Foundation -->
        <div class="mb-6">
            <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-cyan-500/10 border border-white/30 p-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-tags text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-gray-900">Batches / Lots (Foundation)</h3>
                            <p class="text-xs text-gray-500">Optional receive-time lot and expiry capture for FEFO-ready inventory controls</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-cyan-100 text-cyan-800"><?= count($batches ?? []) ?> batch(es)</span>
                </div>
                <?php if (!empty($batches)): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-slate-600 uppercase border-b border-slate-200">
                                    <th class="px-3 py-2">Lot</th>
                                    <th class="px-3 py-2">Expiry</th>
                                    <th class="px-3 py-2">Received</th>
                                    <th class="px-3 py-2">Remaining</th>
                                    <th class="px-3 py-2">Cost</th>
                                    <th class="px-3 py-2">Created</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach (($batches ?? []) as $batch): ?>
                                    <tr class="hover:bg-slate-50/80">
                                        <td class="px-3 py-2 text-sm text-slate-800"><?= esc((string) ($batch['lot_number'] ?? '—')) ?></td>
                                        <td class="px-3 py-2 text-sm text-slate-700"><?= !empty($batch['expiry_date']) ? esc(formatDate((string) $batch['expiry_date'])) : 'No expiry' ?></td>
                                        <td class="px-3 py-2 text-sm text-slate-700"><?= (int) ($batch['received_qty'] ?? 0) ?></td>
                                        <td class="px-3 py-2 text-sm font-semibold text-slate-900"><?= (int) ($batch['remaining_qty'] ?? 0) ?></td>
                                        <td class="px-3 py-2 text-sm text-slate-700">
                                            <?php if ($batch['unit_cost'] !== null): ?>
                                                <?= esc((string) ($batch['currency'] ?? 'ILS')) ?> <?= number_format((float) $batch['unit_cost'], 2) ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-3 py-2 text-xs text-slate-500"><?= !empty($batch['created_at']) ? esc(formatDateTime((string) $batch['created_at'])) : '-' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-600">
                        No batches recorded yet. Use “Receive Stock” and optionally enter lot number / expiry to start tracking batches.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Compact Action Buttons -->
        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
            <button onclick="addItemToReorder(<?= (int) $item['id'] ?>)" class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                <i class="fas fa-cart-plus mr-2"></i>Add to Reorder List
            </button>
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
function addItemToReorder(id, preferredQty = 0) {
    const qtyPrompt = prompt('Requested reorder quantity:', preferredQty && preferredQty > 0 ? String(preferredQty) : '1');
    if (qtyPrompt === null) return;
    const requestedQty = parseInt(qtyPrompt, 10);
    if (!Number.isFinite(requestedQty) || requestedQty <= 0) {
        showToast('error', 'Error!', 'Please enter a valid reorder quantity.');
        return;
    }

    const formData = new FormData();
    formData.append('inventory_id', String(id));
    formData.append('requested_qty', String(requestedQty));
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch(`<?= base_url('inventory/reorder-list/add') ?>`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data && data.success) {
            showToast('success', 'Reorder List', data.message || 'Added to reorder list.');
        } else {
            showToast('error', 'Error!', (data && data.message) ? data.message : 'Failed to add to reorder list.');
        }
    })
    .catch(() => showToast('error', 'Error!', 'Failed to add to reorder list.'));
}

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
    const lotNumber = prompt('Lot/Batch number (optional):') || '';
    const batchExpiryDate = prompt('Batch expiry date (optional, YYYY-MM-DD or clinic format):') || '';

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
            reason: reason,
            lot_number: lotNumber,
            batch_expiry_date: batchExpiryDate,
            batch_currency: 'ILS'
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
