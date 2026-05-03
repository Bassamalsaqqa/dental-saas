<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$isRtl = !empty($is_rtl);
$tr = static fn(string $key, array $params = []) => lang('Inventory.' . $key, $params);
$creatorName = trim(($item['creator_first_name'] ?? '') . ' ' . ($item['creator_last_name'] ?? ''));

$stockStatus = 'in_stock';
$statusClass = 'bg-green-100 text-green-800';
$statusText = $tr('inStockStatus');
if ($item['quantity'] == 0) {
    $stockStatus = 'out_of_stock'; $statusClass = 'bg-red-100 text-red-800'; $statusText = $tr('outOfStockStatus');
} elseif (isset($item['min_quantity']) && $item['quantity'] <= $item['min_quantity']) {
    $stockStatus = 'low_stock'; $statusClass = 'bg-yellow-100 text-yellow-800'; $statusText = $tr('lowStockStatus');
}
?>

<div class="min-h-screen bg-slate-50">
    <div class="px-4 sm:px-6 lg:px-8 py-6">

        <!-- Header + Action Buttons -->
        <div class="mb-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-box text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-gray-900"><?= esc($item['item_name']) ?></h1>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-700"><?= ucfirst($item['category']) ?></span>
                            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $statusClass ?>"><?= $statusText ?></span>
                            <span class="text-xs text-gray-400">#<?= $item['id'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="<?= base_url('inventory') ?>" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-arrow-left text-xs"></i><?= esc($tr('back')) ?>
                    </a>
                    <button onclick="receiveStock(<?= $item['id'] ?>)" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                        <i class="fas fa-box-open text-xs"></i><?= esc($tr('receiveStock')) ?>
                    </button>
                    <button onclick="adjustStock(<?= $item['id'] ?>)" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors shadow-sm">
                        <i class="fas fa-plus-minus text-xs"></i><?= esc($tr('adjustStock')) ?>
                    </button>
                    <a href="<?= base_url('inventory/' . $item['id'] . '/edit') ?>" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                        <i class="fas fa-edit text-xs"></i><?= esc($tr('edit')) ?>
                    </a>
                    <button onclick="deleteItem(<?= $item['id'] ?>)" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-white bg-rose-600 rounded-lg hover:bg-rose-700 transition-colors shadow-sm">
                        <i class="fas fa-trash text-xs"></i><?= esc($tr('delete')) ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Overview + Key Metrics (2-col layout) -->
        <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <!-- Left: Item identity & description -->
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <?php if (!empty($item['description'])): ?>
                    <p class="text-sm text-gray-600 leading-relaxed mb-4"><?= esc($item['description']) ?></p>
                <?php else: ?>
                    <p class="text-sm text-gray-400 italic mb-4"><?= esc($tr('noDescription')) ?></p>
                <?php endif; ?>
                <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500">
                    <span><i class="far fa-calendar mr-1"></i><?= esc($tr('created')) ?>: <?= $item['created_at'] ? date('M j, Y', strtotime($item['created_at'])) : '—' ?></span>
                    <span><i class="far fa-clock mr-1"></i><?= esc($tr('lastUpdated')) ?>: <?= $item['updated_at'] ? date('M j, Y', strtotime($item['updated_at'])) : '—' ?></span>
                    <?php if ($creatorName !== ''): ?>
                        <span><i class="far fa-user mr-1"></i><?= esc($creatorName) ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($item['quantity'] <= $item['min_quantity']): ?>
                    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg flex items-center gap-2 text-sm text-yellow-800 font-semibold">
                        <i class="fas fa-exclamation-triangle text-yellow-600"></i><?= esc($tr('lowStockAlert')) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right: Key metrics -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1"><?= esc($tr('current')) ?></p>
                        <p class="text-2xl font-black text-gray-900"><?= $item['quantity'] ?></p>
                        <p class="text-xs text-gray-400"><?= esc($tr('unitInStock')) ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1"><?= esc($tr('minimum')) ?></p>
                        <p class="text-2xl font-black text-gray-900"><?= $item['min_quantity'] ?></p>
                        <p class="text-xs text-gray-400"><?= esc($tr('minQtyShort')) ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1"><?= esc($tr('unitPrice')) ?></p>
                        <p class="text-xl font-bold text-gray-900"><?= formatCurrency($item['unit_price']) ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1"><?= esc($tr('totalValue')) ?></p>
                        <p class="text-xl font-bold text-gray-900"><?= formatCurrency($item['quantity'] * $item['unit_price']) ?></p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500"><?= esc($tr('location')) ?></span><span class="text-gray-900 font-medium"><?= $item['location'] ?: '—' ?></span></div>
                    <div class="flex justify-between"><span class="text-gray-500"><?= esc($tr('expiry')) ?></span><span class="text-gray-900 font-medium">
                        <?php if ($item['expiry_date']):
                            $daysUntilExpiry = ceil((strtotime($item['expiry_date']) - time()) / 86400);
                            echo $daysUntilExpiry < 0 ? '<span class="text-red-600">' . esc($tr('expiredLabel')) . '</span>' : ($daysUntilExpiry <= 30 ? '<span class="text-yellow-600">' . $daysUntilExpiry . ' days</span>' : date('M j, Y', strtotime($item['expiry_date'])));
                        else: echo '—'; endif; ?>
                    </span></div>
                    <div class="flex justify-between"><span class="text-gray-500"><?= esc($tr('supplier')) ?></span><span class="text-gray-900 font-medium"><?= $item['supplier'] ?: '—' ?></span></div>
                </div>
            </div>
        </div>

        <!-- Supplier Procurement Mapping -->
        <div class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-5">
            <div class="xl:col-span-3 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900"><?= esc($tr('supplierMappings')) ?></h3>
                        <p class="text-xs text-gray-500"><?= esc($tr('supplierMappingsHelp')) ?></p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-100 text-amber-700"><?= count($supplier_links ?? []) ?> <?= esc($tr('supplier')) ?></span>
                </div>
                <?php if (!empty($supplier_links)): ?>
                    <div class="space-y-2">
                        <?php foreach (($supplier_links ?? []) as $link): ?>
                            <div class="rounded-lg border border-gray-200 bg-gray-50/50 p-3">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div>
                                        <span class="font-semibold text-sm text-gray-900"><?= esc($link['supplier_name'] ?? ('Supplier #' . (int) $link['supplier_id'])) ?></span>
                                        <?php if ((int) ($link['is_preferred'] ?? 0) === 1): ?>
                                            <span class="ml-2 px-1.5 py-px rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700"><?= esc($tr('preferred')) ?></span>
                                        <?php endif; ?>
                                        <div class="mt-0.5 text-xs text-gray-500">
                                            <?php if (!empty($link['supplier_sku'])): ?>SKU: <?= esc($link['supplier_sku']) ?><?php endif; ?>
                                            <?php if (!empty($link['pack_size_label'])): ?> • <?= esc($link['pack_size_label']) ?><?php endif; ?>
                                            <?php if (!empty($link['preferred_order_qty'])): ?> • Qty: <?= (int) $link['preferred_order_qty'] ?><?php endif; ?>
                                            <?php if (!empty($link['last_unit_price'])): ?> • <?= esc($link['currency'] ?? 'ILS') ?> <?= number_format((float) $link['last_unit_price'], 2) ?><?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="addItemToReorder(<?= (int) $item['id'] ?>, <?= (int) ($link['preferred_order_qty'] ?? 0) ?>)" class="px-2.5 py-1.5 rounded-lg bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600"><i class="fas fa-cart-plus mr-1"></i><?= esc($tr('addToReorderList')) ?></button>
                                        <form method="post" action="<?= base_url('inventory/' . (int) $item['id'] . '/supplier-link/' . (int) $link['id'] . '/delete') ?>" onsubmit="return confirm('<?= esc(lang('Inventory.removeSupplierMappingConfirm'), 'js') ?>');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-700 text-xs font-semibold hover:bg-rose-100"><i class="fas fa-trash mr-1"></i><?= esc($tr('removeItem')) ?></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-gray-400 text-center py-6"><?= esc($tr('noSupplierMappings')) ?></p>
                <?php endif; ?>
            </div>

            <div class="xl:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-base font-bold text-gray-900 mb-3"><?= esc($tr('addUpdateMapping')) ?></h3>
                <form method="post" action="<?= base_url('inventory/' . (int) $item['id'] . '/supplier-link') ?>" class="space-y-3">
                    <?= csrf_field() ?>
                    <select name="link_supplier_id" data-force-select2 data-allow-clear="false" class="w-full h-10 px-3 border border-gray-300 rounded-lg bg-white text-sm">
                        <option value=""><?= esc($tr('supplier')) ?></option>
                        <?php foreach (($supplier_directory ?? []) as $supplier): ?>
                            <option value="<?= (int) $supplier['id'] ?>"><?= esc($supplier['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="supplier_sku" class="h-10 px-3 border border-gray-300 rounded-lg bg-white text-sm" placeholder="<?= esc($tr('supplierSku')) ?>">
                        <input type="text" name="pack_size_label" class="h-10 px-3 border border-gray-300 rounded-lg bg-white text-sm" placeholder="<?= esc($tr('packSize')) ?>">
                        <input type="number" min="1" step="1" name="preferred_order_qty" class="h-10 px-3 border border-gray-300 rounded-lg bg-white text-sm" placeholder="<?= esc($tr('preferredQty')) ?>">
                        <div class="grid grid-cols-3 gap-2">
                            <input type="number" min="0" step="0.01" name="last_unit_price" class="col-span-2 h-10 px-3 border border-gray-300 rounded-lg bg-white text-sm" placeholder="<?= esc($tr('lastPrice')) ?>">
                            <select name="link_currency" data-force-select2 data-allow-clear="false" class="h-10 px-2 border border-gray-300 rounded-lg bg-white text-sm">
                                <?php foreach (($currencies ?? ['ILS']) as $c): ?>
                                    <option value="<?= esc($c) ?>"><?= esc($c) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <textarea name="link_notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm" placeholder="<?= esc($tr('additionalNotesPlaceholder')) ?>"></textarea>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_preferred" value="1" checked class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <?= esc($tr('setAsPreferredSupplier')) ?>
                    </label>
                    <button type="submit" class="w-full py-2.5 rounded-lg bg-amber-500 text-white text-sm font-semibold hover:bg-amber-600 transition-colors">
                        <i class="fas fa-save mr-2"></i><?= esc($tr('saveSupplierMapping')) ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Stock Movements -->
        <div class="mb-6 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-gray-900"><?= esc($tr('movementsTitle')) ?></h3>
                    <p class="text-xs text-gray-500"><?= esc($tr('movementsSubtitle')) ?></p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-600"><?= esc($tr('rowsCount', ['count' => count($movements ?? [])])) ?></span>
            </div>
            <?php if (!empty($movements)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-100">
                                <th class="px-3 py-2.5"><?= esc($tr('date')) ?></th>
                                <th class="px-3 py-2.5"><?= esc($tr('status')) ?></th>
                                <th class="px-3 py-2.5"><?= esc($tr('change')) ?></th>
                                <th class="px-3 py-2.5"><?= esc($tr('beforeAfter')) ?></th>
                                <th class="px-3 py-2.5"><?= esc($tr('cost')) ?></th>
                                <th class="px-3 py-2.5"><?= esc($tr('reference')) ?></th>
                                <th class="px-3 py-2.5"><?= esc($tr('actor')) ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach (($movements ?? []) as $m):
                                $qtyChange = (int) ($m['quantity_change'] ?? 0);
                                $changeClass = $qtyChange >= 0 ? 'text-emerald-700 bg-emerald-50' : 'text-rose-700 bg-rose-50';
                                $actorName = trim((string) (($m['actor_first_name'] ?? '') . ' ' . ($m['actor_last_name'] ?? '')));
                                $refType = (string) ($m['reference_type'] ?? '');
                                $refId = (int) ($m['reference_id'] ?? 0);
                                $refText = $refType !== '' ? ucwords(str_replace('_', ' ', $refType)) . ($refId ? ' #' . $refId : '') : '-';
                            ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2.5">
                                        <div class="text-gray-700"><?= !empty($m['created_at']) ? formatDateTime($m['created_at']) : '-' ?></div>
                                        <?php if (!empty($m['notes'])): ?><div class="text-xs text-gray-400 mt-0.5"><?= esc($m['notes']) ?></div><?php endif; ?>
                                    </td>
                                    <td class="px-3 py-2.5"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600"><?= esc(ucwords(str_replace('_', ' ', (string) ($m['movement_type'] ?? 'unknown')))) ?></span></td>
                                    <td class="px-3 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold <?= $changeClass ?>"><i class="fas <?= $qtyChange >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' ?> mr-1"></i><?= $qtyChange >= 0 ? '+' : '' ?><?= $qtyChange ?></span></td>
                                    <td class="px-3 py-2.5 text-gray-700"><?= (int) ($m['quantity_before'] ?? 0) ?> → <span class="font-semibold"><?= (int) ($m['quantity_after'] ?? 0) ?></span></td>
                                    <td class="px-3 py-2.5 text-gray-700"><?= $m['unit_cost'] !== null ? formatCurrency((float) $m['unit_cost']) . '/u' : '-' ?></td>
                                    <td class="px-3 py-2.5 text-gray-600"><?= esc($refText) ?></td>
                                    <td class="px-3 py-2.5 text-gray-600"><?= esc($actorName ?: '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-sm text-gray-400 text-center py-6">No stock movements recorded yet.</p>
            <?php endif; ?>
        </div>

        <!-- Batches -->
        <?php if (!empty($batches)): ?>
        <div class="mb-6 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-gray-900"><?= esc($tr('batchesLotsFoundation')) ?></h3>
                    <p class="text-xs text-gray-500"><?= esc($tr('batchesLotsFoundationHelp')) ?></p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-cyan-100 text-cyan-700"><?= count($batches) ?> batches</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-100">
                            <th class="px-3 py-2.5"><?= esc($tr('lot')) ?></th>
                            <th class="px-3 py-2.5"><?= esc($tr('expiry')) ?></th>
                            <th class="px-3 py-2.5"><?= esc($tr('receivedQty')) ?></th>
                            <th class="px-3 py-2.5"><?= esc($tr('remainingQty')) ?></th>
                            <th class="px-3 py-2.5"><?= esc($tr('cost')) ?></th>
                            <th class="px-3 py-2.5"><?= esc($tr('created')) ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach ($batches as $batch): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2.5 text-gray-800"><?= esc((string) ($batch['lot_number'] ?? '—')) ?></td>
                                <td class="px-3 py-2.5 text-gray-600"><?= !empty($batch['expiry_date']) ? esc(formatDate((string) $batch['expiry_date'])) : '—' ?></td>
                                <td class="px-3 py-2.5 text-gray-700"><?= (int) ($batch['received_qty'] ?? 0) ?></td>
                                <td class="px-3 py-2.5 font-semibold text-gray-900"><?= (int) ($batch['remaining_qty'] ?? 0) ?></td>
                                <td class="px-3 py-2.5 text-gray-700"><?= $batch['unit_cost'] !== null ? esc((string) ($batch['currency'] ?? 'ILS')) . ' ' . number_format((float) $batch['unit_cost'], 2) : '—' ?></td>
                                <td class="px-3 py-2.5 text-xs text-gray-500"><?= !empty($batch['created_at']) ? esc(formatDateTime((string) $batch['created_at'])) : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Bottom Actions -->
        <div class="flex flex-wrap gap-2">
            <button onclick="addItemToReorder(<?= (int) $item['id'] ?>)" class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold text-white bg-amber-500 rounded-lg hover:bg-amber-600 transition-colors shadow-sm">
                <i class="fas fa-cart-plus text-xs"></i><?= esc($tr('addToReorderList')) ?>
            </button>
            <button onclick="deleteItem(<?= $item['id'] ?>)" class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold text-rose-600 bg-rose-50 rounded-lg hover:bg-rose-100 transition-colors">
                <i class="fas fa-trash text-xs"></i><?= esc($tr('delete')) ?>
            </button>
        </div>
    </div>
</div>

<script>
function addItemToReorder(id, preferredQty) {
    var qty = prompt('Requested reorder quantity:', (preferredQty && preferredQty > 0) ? String(preferredQty) : '1');
    if (!qty) return;
    var n = parseInt(qty, 10);
    if (!Number.isFinite(n) || n <= 0) { showToast('error','Error!','Enter a valid quantity.'); return; }
    var fd = new FormData();
    fd.append('inventory_id', String(id));
    fd.append('requested_qty', String(n));
    fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    fetch('<?= base_url('inventory/reorder-list/add') ?>', { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd })
        .then(function(r){return r.json();})
        .then(function(d){ showToast(d.success?'success':'error', d.success?'Reorder List':'Error!', d.message||'Added.'); })
        .catch(function(){ showToast('error','Error!','Failed.'); });
}

function receiveStock(id) {
    var qty = prompt('Enter received quantity:');
    if (!qty || isNaN(qty) || parseInt(qty,10)<=0) { showToast('error','Error!','Enter a valid quantity.'); return; }
    var cost = prompt('Unit cost (leave blank to keep current):','<?= esc((string) $item['unit_price']) ?>');
    if (cost !== null && cost !== '' && (isNaN(cost) || parseFloat(cost)<0)) { showToast('error','Error!','Enter a valid cost.'); return; }
    var reason = prompt('Reference / note (optional):') || 'Stock received';
    var lot = prompt('Lot/Batch number (optional):') || '';
    var exp = prompt('Batch expiry date (optional, YYYY-MM-DD):') || '';
    fetch('<?= base_url('inventory') ?>/'+id+'/receive', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','<?= csrf_token() ?>':'<?= csrf_hash() ?>'},
        body:JSON.stringify({quantity:parseInt(qty,10),unit_cost:cost??'',reason:reason,lot_number:lot,batch_expiry_date:exp,batch_currency:'ILS'})
    }).then(function(r){return r.json();}).then(function(d){
        showToast(d.success?'success':'error', d.success?'Success!':'Error!', d.message||'Done.');
        if(d.success) setTimeout(function(){location.reload();},1200);
    }).catch(function(){ showToast('error','Error!','An error occurred.'); });
}

function adjustStock(id) {
    var adj = prompt('Adjustment (+ to add, - to subtract):');
    if (!adj || isNaN(adj)) return;
    var val = parseInt(adj,10), type = val>=0?'add':'subtract', reason = prompt('Reason (optional):')||'Manual adjustment';
    fetch('<?= base_url('inventory') ?>/'+id+'/adjust', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','<?= csrf_token() ?>':'<?= csrf_hash() ?>'},
        body:JSON.stringify({adjustment:Math.abs(val),type:type,reason:reason})
    }).then(function(r){return r.json();}).then(function(d){
        showToast(d.success?'success':'error', d.success?'Success!':'Error!', d.message||'Done.');
        if(d.success) setTimeout(function(){location.reload();},1500);
    }).catch(function(){ showToast('error','Error!','An error occurred.'); });
}

function deleteItem(id) {
    confirmDelete('<?= base_url('inventory') ?>/'+id, 'Are you sure you want to delete this item? This cannot be undone.');
}
</script>
<?= $this->endSection() ?>
