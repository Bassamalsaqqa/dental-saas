<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>



<?php
$isRtl = !empty($is_rtl);
$tr = static fn(string $key, array $params = []) => lang('Inventory.' . $key, $params);
$creatorName = trim(($item['creator_first_name'] ?? '') . ' ' . ($item['creator_last_name'] ?? ''));

$stockStatus = 'in_stock';
$statusClass = 'v3-bg-paid v3-text-primary';
$statusText = $tr('inStockStatus');
if ($item['quantity'] == 0) {
    $stockStatus = 'out_of_stock'; $statusClass = 'v3-bg-alert v3-text-danger'; $statusText = $tr('outOfStockStatus');
} elseif (isset($item['min_quantity']) && $item['quantity'] <= $item['min_quantity']) {
    $stockStatus = 'low_stock'; $statusClass = 'v3-bg-waiting v3-text-warning'; $statusText = $tr('lowStockStatus');
}
$quantity = (int) ($item['quantity'] ?? 0);
$minQuantity = (int) ($item['min_quantity'] ?? 0);
$unitPrice = (float) ($item['unit_price'] ?? 0);
$totalValue = $quantity * $unitPrice;
$expiryDisplay = '—';
$expiryTone = 'v3-text-ink';
if (!empty($item['expiry_date'])) {
    $daysUntilExpiry = (int) ceil((strtotime((string) $item['expiry_date']) - time()) / 86400);
    if ($daysUntilExpiry < 0) {
        $expiryDisplay = $tr('expiredLabel');
        $expiryTone = 'v3-text-danger';
    } elseif ($daysUntilExpiry <= 30) {
        $expiryDisplay = $daysUntilExpiry . ' days';
        $expiryTone = 'v3-text-warning';
    } else {
        $expiryDisplay = date('M j, Y', strtotime((string) $item['expiry_date']));
    }
}
?>

<div class="denta-inventory-workspace inventory-detail-shell min-h-screen v3-surface">
    <div class="px-0 sm:px-4 lg:px-5 xl:px-6 py-6 sm:py-8">

        <section class="inventory-hero-card mb-6">
            <div class="inventory-hero-content p-6 sm:p-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <div class="inventory-hero-avatar">
                        <i class="fas fa-box-open v3-title-scale"></i>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.18em] v3-text-primary">
                            <span><?= esc($tr('inventoryItems')) ?></span>
                            <span class="h-1 w-1 rounded-full v3-bg-scheduled"></span>
                            <span>#<?= (int) $item['id'] ?></span>
                        </div>
                        <h1 class="mt-2 v3-title-scale font-extrabold tracking-tight v3-text-on-primary sm:text-4xl"><?= esc($item['item_name']) ?></h1>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full border v3-border v3-surface px-3 py-1 text-xs font-black v3-text-on-primary"><?= esc(ucfirst((string) $item['category'])) ?></span>
                            <span class="inline-flex items-center rounded-full border v3-border v3-surface px-3 py-1 text-xs font-black v3-text-on-primary"><?= esc($statusText) ?></span>
                        </div>
                        <?php if (!empty($item['description'])): ?>
                            <p class="mt-4 max-w-3xl text-sm font-semibold leading-6 v3-text-primary"><?= esc($item['description']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="<?= workflow_back('inventory', $return_to ?? null) ?>" class="inventory-action inventory-action-soft">
                        <i class="fas <?= $isRtl ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i><?= esc($tr('back')) ?>
                    </a>
                    <button onclick="receiveStock(<?= $item['id'] ?>)" class="inventory-action inventory-action-emerald v3-btn v3-btn--secondary">
                        <i class="fas fa-box-open"></i><?= esc($tr('receiveStock')) ?>
                    </button>
                    <button onclick="adjustStock(<?= $item['id'] ?>)" class="inventory-action inventory-action-emerald v3-btn v3-btn--secondary">
                        <i class="fas fa-plus-minus"></i><?= esc($tr('adjustStock')) ?>
                    </button>
                    <a href="<?= workflow_context_url('inventory/' . $item['id'] . '/edit', $return_to ?? null) ?>" class="inventory-action inventory-action-blue">
                        <i class="fas fa-edit"></i><?= esc($tr('edit')) ?>
                    </a>
                    <button onclick="deleteItem(<?= $item['id'] ?>)" class="inventory-action inventory-action-rose v3-btn v3-btn--secondary">
                        <i class="fas fa-trash"></i><?= esc($tr('delete')) ?>
                    </button>
                </div>
            </div>
            <div class="mt-6 inventory-hero-chip-grid">
                <div class="inventory-hero-chip">
                    <i class="fas fa-location-dot"></i>
                    <span><span class="label"><?= esc($tr('location')) ?></span><span class="value"><?= esc($item['location'] ?: '—') ?></span></span>
                </div>
                <div class="inventory-hero-chip">
                    <i class="fas fa-truck"></i>
                    <span><span class="label"><?= esc($tr('supplier')) ?></span><span class="value"><?= esc($item['supplier'] ?: '—') ?></span></span>
                </div>
                <div class="inventory-hero-chip">
                    <i class="fas fa-calendar-day"></i>
                    <span><span class="label"><?= esc($tr('expiry')) ?></span><span class="value"><?= esc($expiryDisplay) ?></span></span>
                </div>
                <div class="inventory-hero-chip">
                    <i class="fas fa-user-clock"></i>
                    <span><span class="label"><?= esc($tr('created')) ?></span><span class="value"><?= $item['created_at'] ? esc(date('M j, Y', strtotime($item['created_at']))) : '—' ?></span></span>
                </div>
            </div>
            </div>
        </section>

        <section class="v3-command-section mb-6 v3-corner border v3-border p-4 sm:p-5">
            <div class="inventory-snapshot-grid">
                <div class="inventory-snapshot-card inventory-snapshot-stock">
                    <p class="inventory-snapshot-label"><?= esc($tr('current')) ?></p>
                    <p class="inventory-snapshot-value-text" dir="ltr"><?= esc((string) $quantity) ?></p>
                    <p class="inventory-snapshot-meta"><?= esc($tr('unitInStock')) ?> / <?= esc($tr('minimum')) ?> <span dir="ltr"><?= esc((string) $minQuantity) ?></span></p>
                </div>
                <div class="inventory-snapshot-card inventory-snapshot-risk">
                    <p class="inventory-snapshot-label"><?= esc($tr('status')) ?></p>
                    <p class="inventory-snapshot-value-text"><?= esc($statusText) ?></p>
                    <p class="inventory-snapshot-meta <?= esc($expiryTone) ?>"><?= esc($tr('expiry')) ?>: <?= esc($expiryDisplay) ?></p>
                </div>
                <div class="inventory-snapshot-card inventory-snapshot-value">
                    <p class="inventory-snapshot-label"><?= esc($tr('totalValue')) ?></p>
                    <p class="inventory-snapshot-value-text" dir="ltr"><?= formatCurrency($totalValue) ?></p>
                    <p class="inventory-snapshot-meta"><?= esc($tr('unitPrice')) ?>: <span dir="ltr"><?= formatCurrency($unitPrice) ?></span></p>
                </div>
                <div class="inventory-snapshot-card inventory-snapshot-location">
                    <p class="inventory-snapshot-label"><?= esc($tr('lastUpdated')) ?></p>
                    <p class="inventory-snapshot-value-text text-xl"><?= $item['updated_at'] ? esc(date('M j, Y', strtotime($item['updated_at']))) : '—' ?></p>
                    <p class="inventory-snapshot-meta"><?= $creatorName !== '' ? esc($creatorName) : '—' ?></p>
                </div>
            </div>
            <?php if ($quantity <= $minQuantity): ?>
                <div class="mt-4 flex items-center gap-3 v3-corner border v3-border-default v3-bg-waiting px-4 py-3 text-sm font-bold v3-text-warning">
                    <i class="fas fa-exclamation-triangle v3-text-warning"></i>
                    <span><?= esc($tr('lowStockAlert')) ?></span>
                </div>
            <?php endif; ?>
        </section>

        <!-- Legacy overview kept hidden while the detail page uses the stronger snapshot band above. -->
        <div class="hidden">
            <!-- Left: Item identity & description -->
            <div class="lg:col-span-2 show-card p-5">
                <?php if (!empty($item['description'])): ?>
                    <p class="text-sm v3-text-muted leading-relaxed mb-4"><?= esc($item['description']) ?></p>
                <?php else: ?>
                    <p class="text-sm v3-text-muted italic mb-4"><?= esc($tr('noDescription')) ?></p>
                <?php endif; ?>
                <div class="flex flex-wrap items-center gap-4 text-xs v3-text-muted">
                    <span><i class="far fa-calendar mr-1"></i><?= esc($tr('created')) ?>: <?= $item['created_at'] ? date('M j, Y', strtotime($item['created_at'])) : '—' ?></span>
                    <span><i class="far fa-clock mr-1"></i><?= esc($tr('lastUpdated')) ?>: <?= $item['updated_at'] ? date('M j, Y', strtotime($item['updated_at'])) : '—' ?></span>
                    <?php if ($creatorName !== ''): ?>
                        <span><i class="far fa-user mr-1"></i><?= esc($creatorName) ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($item['quantity'] <= $item['min_quantity']): ?>
                    <div class="mt-4 p-3 v3-bg-waiting border v3-border-default rounded-lg flex items-center gap-2 text-sm v3-text-warning font-semibold">
                        <i class="fas fa-exclamation-triangle v3-text-warning"></i><?= esc($tr('lowStockAlert')) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right: Key metrics -->
            <div class="show-card p-5">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="show-metric-label"><?= esc($tr('current')) ?></p>
                        <p class="show-metric-value"><?= $item['quantity'] ?></p>
                        <p class="show-metric-sub"><?= esc($tr('unitInStock')) ?></p>
                    </div>
                    <div>
                        <p class="show-metric-label"><?= esc($tr('minimum')) ?></p>
                        <p class="show-metric-value"><?= $item['min_quantity'] ?></p>
                        <p class="show-metric-sub"><?= esc($tr('minQtyShort')) ?></p>
                    </div>
                    <div>
                        <p class="show-metric-label"><?= esc($tr('unitPrice')) ?></p>
                        <p class="text-lg font-bold v3-text-ink"><?= formatCurrency($item['unit_price']) ?></p>
                    </div>
                    <div>
                        <p class="show-metric-label"><?= esc($tr('totalValue')) ?></p>
                        <p class="text-lg font-bold v3-text-ink"><?= formatCurrency($item['quantity'] * $item['unit_price']) ?></p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t v3-border space-y-2 text-sm">
                    <div class="flex justify-between"><span class="v3-text-muted"><?= esc($tr('location')) ?></span><span class="v3-text-ink font-medium"><?= $item['location'] ?: '—' ?></span></div>
                    <div class="flex justify-between"><span class="v3-text-muted"><?= esc($tr('expiry')) ?></span><span class="v3-text-ink font-medium">
                        <?php if ($item['expiry_date']):
                            $daysUntilExpiry = ceil((strtotime($item['expiry_date']) - time()) / 86400);
                            echo $daysUntilExpiry < 0 ? '<span class="v3-text-danger">' . esc($tr('expiredLabel')) . '</span>' : ($daysUntilExpiry <= 30 ? '<span class="v3-text-warning">' . $daysUntilExpiry . ' days</span>' : date('M j, Y', strtotime($item['expiry_date'])));
                        else: echo '—'; endif; ?>
                    </span></div>
                    <div class="flex justify-between"><span class="v3-text-muted"><?= esc($tr('supplier')) ?></span><span class="v3-text-ink font-medium"><?= $item['supplier'] ?: '—' ?></span></div>
                </div>
            </div>
        </div>

        <!-- Supplier Procurement Mapping -->
        <div class="mb-6 grid grid-cols-1 gap-5 xl:grid-cols-5">
            <div class="v3-surface-heading xl:col-span-3 show-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="show-card-header mb-0 pb-0 border-b-0">
                        <i class="fas fa-truck-ramp-box text-sm" style="background:#fef3c7;color:#d97706;"></i>
                        <span><?= esc($tr('supplierMappings')) ?></span>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full v3-bg-waiting v3-text-warning"><?= count($supplier_links ?? []) ?> <?= esc($tr('supplier')) ?></span>
                </div>
                <?php if (!empty($supplier_links)): ?>
                    <div class="space-y-2">
                        <?php foreach (($supplier_links ?? []) as $link): ?>
                            <div class="v3-corner border v3-border-default v3-surface p-3">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div>
                                        <span class="font-semibold text-sm v3-text-ink"><?= esc($link['supplier_name'] ?? ('Supplier #' . (int) $link['supplier_id'])) ?></span>
                                        <?php if ((int) ($link['is_preferred'] ?? 0) === 1): ?>
                                            <span class="ml-2 px-1.5 py-px rounded-full text-xs font-semibold v3-bg-paid v3-text-primary"><?= esc($tr('preferred')) ?></span>
                                        <?php endif; ?>
                                        <div class="mt-0.5 text-xs v3-text-muted">
                                            <?php if (!empty($link['supplier_sku'])): ?>SKU: <?= esc($link['supplier_sku']) ?><?php endif; ?>
                                            <?php if (!empty($link['pack_size_label'])): ?> • <?= esc($link['pack_size_label']) ?><?php endif; ?>
                                            <?php if (!empty($link['preferred_order_qty'])): ?> • Qty: <?= (int) $link['preferred_order_qty'] ?><?php endif; ?>
                                            <?php if (!empty($link['last_unit_price'])): ?> • <?= esc($link['currency'] ?? 'ILS') ?> <?= number_format((float) $link['last_unit_price'], 2) ?><?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="addItemToReorder(<?= (int) $item['id'] ?>, <?= (int) ($link['preferred_order_qty'] ?? 0) ?>)" class="px-2.5 py-1.5 rounded-lg v3-bg-warning v3-text-on-primary text-xs font-semibold v3-hover-primary v3-btn v3-btn--secondary"><i class="fas fa-cart-plus mr-1"></i><?= esc($tr('addToReorderList')) ?></button>
                                        <form method="post" action="<?= base_url('inventory/' . (int) $item['id'] . '/supplier-link/' . (int) $link['id'] . '/delete') ?>" onsubmit="return confirm('<?= esc(lang('Inventory.removeSupplierMappingConfirm'), 'js') ?>');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg v3-bg-alert v3-text-danger text-xs font-semibold v3-hover-danger v3-btn v3-btn--danger"><i class="fas fa-trash mr-1"></i><?= esc($tr('removeItem')) ?></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="inventory-empty"><?= esc($tr('noSupplierMappings')) ?></div>
                <?php endif; ?>
            </div>

            <div class="v3-surface-heading xl:col-span-2 show-card p-5">
                <div class="show-card-header mb-3 pb-0 border-b-0">
                    <i class="fas fa-link text-sm" style="background:#fef3c7;color:#d97706;"></i>
                    <span><?= esc($tr('addUpdateMapping')) ?></span>
                </div>
                <form method="post" action="<?= base_url('inventory/' . (int) $item['id'] . '/supplier-link') ?>" class="space-y-3">
                    <?= csrf_field() ?>
                    <select name="link_supplier_id" data-force-select2 data-allow-clear="false" class="w-full h-10 px-3 border v3-border rounded-lg v3-surface text-sm v3-select">
                        <option value=""><?= esc($tr('supplier')) ?></option>
                        <?php foreach (($supplier_directory ?? []) as $supplier): ?>
                            <option value="<?= (int) $supplier['id'] ?>"><?= esc($supplier['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="supplier_sku" class="h-10 px-3 border v3-border rounded-lg v3-surface text-sm v3-input" placeholder="<?= esc($tr('supplierSku')) ?>">
                        <input type="text" name="pack_size_label" class="h-10 px-3 border v3-border rounded-lg v3-surface text-sm v3-input" placeholder="<?= esc($tr('packSize')) ?>">
                        <input type="number" min="1" step="1" name="preferred_order_qty" class="h-10 px-3 border v3-border rounded-lg v3-surface text-sm v3-input" placeholder="<?= esc($tr('preferredQty')) ?>">
                        <div class="grid grid-cols-3 gap-2">
                            <input type="number" min="0" step="0.01" name="last_unit_price" class="col-span-2 h-10 px-3 border v3-border rounded-lg v3-surface text-sm v3-input" placeholder="<?= esc($tr('lastPrice')) ?>">
                            <select name="link_currency" data-force-select2 data-allow-clear="false" class="h-10 px-2 border v3-border rounded-lg v3-surface text-sm v3-select">
                                <?php foreach (($currencies ?? ['ILS']) as $c): ?>
                                    <option value="<?= esc($c) ?>"><?= esc($c) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <textarea name="link_notes" rows="2" class="w-full px-3 py-2 border v3-border rounded-lg v3-surface text-sm v3-textarea" placeholder="<?= esc($tr('additionalNotesPlaceholder')) ?>"></textarea>
                    <label class="flex items-center gap-2 text-sm v3-text-ink">
                        <input type="checkbox" name="is_preferred" value="1" checked class="rounded v3-border v3-text-primary v3-focus-control v3-checkbox">
                        <?= esc($tr('setAsPreferredSupplier')) ?>
                    </label>
                    <button type="submit" class="w-full py-2.5 rounded-lg v3-bg-warning v3-text-on-primary text-sm font-semibold v3-hover-primary transition-colors v3-btn v3-btn--primary">
                        <i class="fas fa-save mr-2"></i><?= esc($tr('saveSupplierMapping')) ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Stock Movements -->
        <div class="v3-surface-heading mb-6 show-card p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="show-card-header mb-0 pb-0 border-b-0">
                    <i class="fas fa-arrows-rotate text-sm" style="background:#d1fae5;color:#059669;"></i>
                    <span><?= esc($tr('movementsTitle')) ?></span>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full v3-bg-soft v3-text-muted"><?= esc($tr('rowsCount', ['count' => count($movements ?? [])])) ?></span>
            </div>
            <?php if (!empty($movements)): ?>
                <div class="inventory-table-wrap overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold v3-text-muted uppercase border-b v3-border">
                                <th class="px-3 py-2.5"><?= esc($tr('date')) ?></th>
                                <th class="px-3 py-2.5"><?= esc($tr('status')) ?></th>
                                <th class="px-3 py-2.5"><?= esc($tr('change')) ?></th>
                                <th class="px-3 py-2.5"><?= esc($tr('beforeAfter')) ?></th>
                                <th class="px-3 py-2.5"><?= esc($tr('cost')) ?></th>
                                <th class="px-3 py-2.5"><?= esc($tr('reference')) ?></th>
                                <th class="px-3 py-2.5"><?= esc($tr('actor')) ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y v3-border-default">
                            <?php foreach (($movements ?? []) as $m):
                                $qtyChange = (int) ($m['quantity_change'] ?? 0);
                                $changeClass = $qtyChange >= 0 ? 'v3-text-primary v3-bg-paid' : 'v3-text-danger v3-bg-alert';
                                $actorName = trim((string) (($m['actor_first_name'] ?? '') . ' ' . ($m['actor_last_name'] ?? '')));
                                $refType = (string) ($m['reference_type'] ?? '');
                                $refId = (int) ($m['reference_id'] ?? 0);
                                $refText = $refType !== '' ? ucwords(str_replace('_', ' ', $refType)) . ($refId ? ' #' . $refId : '') : '-';
                            ?>
                                <tr class="v3-hover-soft">
                                    <td class="px-3 py-2.5">
                                        <div class="v3-text-ink"><?= !empty($m['created_at']) ? formatDateTime($m['created_at']) : '-' ?></div>
                                        <?php if (!empty($m['notes'])): ?><div class="text-xs v3-text-muted mt-0.5"><?= esc($m['notes']) ?></div><?php endif; ?>
                                    </td>
                                    <td class="px-3 py-2.5"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold v3-bg-soft v3-text-muted"><?= esc(ucwords(str_replace('_', ' ', (string) ($m['movement_type'] ?? 'unknown')))) ?></span></td>
                                    <td class="px-3 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold <?= $changeClass ?>"><i class="fas <?= $qtyChange >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' ?> mr-1"></i><?= $qtyChange >= 0 ? '+' : '' ?><?= $qtyChange ?></span></td>
                                    <td class="px-3 py-2.5 v3-text-ink"><?= (int) ($m['quantity_before'] ?? 0) ?> → <span class="font-semibold"><?= (int) ($m['quantity_after'] ?? 0) ?></span></td>
                                    <td class="px-3 py-2.5 v3-text-ink"><?= $m['unit_cost'] !== null ? formatCurrency((float) $m['unit_cost']) . '/u' : '-' ?></td>
                                    <td class="px-3 py-2.5 v3-text-muted"><?= esc($refText) ?></td>
                                    <td class="px-3 py-2.5 v3-text-muted"><?= esc($actorName ?: '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="inventory-empty">No stock movements recorded yet.</div>
            <?php endif; ?>
        </div>

        <!-- Batches -->
        <?php if (!empty($batches)): ?>
        <div class="v3-surface-heading mb-6 show-card p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="show-card-header mb-0 pb-0 border-b-0">
                    <i class="fas fa-tags text-sm" style="background:#e0f2fe;color:#0284c7;"></i>
                    <span><?= esc($tr('batchesLotsFoundation')) ?></span>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full v3-soft-surface v3-accent-text"><?= count($batches) ?> batches</span>
            </div>
            <div class="inventory-table-wrap overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold v3-text-muted uppercase border-b v3-border">
                            <th class="px-3 py-2.5"><?= esc($tr('lot')) ?></th>
                            <th class="px-3 py-2.5"><?= esc($tr('expiry')) ?></th>
                            <th class="px-3 py-2.5"><?= esc($tr('receivedQty')) ?></th>
                            <th class="px-3 py-2.5"><?= esc($tr('remainingQty')) ?></th>
                            <th class="px-3 py-2.5"><?= esc($tr('cost')) ?></th>
                            <th class="px-3 py-2.5"><?= esc($tr('created')) ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y v3-border-default">
                        <?php foreach ($batches as $batch): ?>
                            <tr class="v3-hover-soft">
                                <td class="px-3 py-2.5 v3-text-ink"><?= esc((string) ($batch['lot_number'] ?? '—')) ?></td>
                                <td class="px-3 py-2.5 v3-text-muted"><?= !empty($batch['expiry_date']) ? esc(formatDate((string) $batch['expiry_date'])) : '—' ?></td>
                                <td class="px-3 py-2.5 v3-text-ink"><?= (int) ($batch['received_qty'] ?? 0) ?></td>
                                <td class="px-3 py-2.5 font-semibold v3-text-ink"><?= (int) ($batch['remaining_qty'] ?? 0) ?></td>
                                <td class="px-3 py-2.5 v3-text-ink"><?= $batch['unit_cost'] !== null ? esc((string) ($batch['currency'] ?? 'ILS')) . ' ' . number_format((float) $batch['unit_cost'], 2) : '—' ?></td>
                                <td class="px-3 py-2.5 text-xs v3-text-muted"><?= !empty($batch['created_at']) ? esc(formatDateTime((string) $batch['created_at'])) : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Bottom Actions -->
        <div class="flex flex-wrap gap-2">
            <button onclick="addItemToReorder(<?= (int) $item['id'] ?>)" class="inventory-action v3-text-on-primary v3-btn v3-btn--secondary">
                <i class="fas fa-cart-plus text-xs"></i><?= esc($tr('addToReorderList')) ?>
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
