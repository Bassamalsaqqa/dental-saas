<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>

<style>
.inventory-detail-shell {
    font-family: inherit;
}
.inventory-hero-card {
    position: relative;
    overflow: hidden;
    border-radius: 1.75rem;
    border: 1px solid rgba(255,255,255,0.55);
    background:
        radial-gradient(circle at 8% 0%, rgba(255,255,255,0.22), transparent 28%),
        radial-gradient(circle at 92% 16%, rgba(20,184,166,0.36), transparent 28%),
        linear-gradient(135deg, #0f766e 0%, #155e75 54%, #1d4ed8 100%);
    box-shadow: 0 30px 72px -46px rgba(15, 23, 42, 0.78);
}
[dir="rtl"] .inventory-hero-card {
    background:
        radial-gradient(circle at 92% 0%, rgba(255,255,255,0.22), transparent 28%),
        radial-gradient(circle at 8% 16%, rgba(20,184,166,0.36), transparent 28%),
        linear-gradient(225deg, #0f766e 0%, #155e75 54%, #1d4ed8 100%);
}
.inventory-hero-card::after {
    content: "";
    position: absolute;
    inset: auto -8rem -10rem auto;
    width: 24rem;
    aspect-ratio: 1;
    border-radius: 999px;
    background: rgba(255,255,255,0.13);
}
.inventory-hero-content {
    position: relative;
    z-index: 1;
}
.inventory-hero-avatar {
    display: inline-flex;
    width: 5rem;
    height: 5rem;
    align-items: center;
    justify-content: center;
    border-radius: 1.5rem;
    border: 1px solid rgba(255,255,255,0.22);
    background: linear-gradient(145deg, rgba(255,255,255,0.24), rgba(153,246,228,0.16));
    color: #fff;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 22px 42px -28px rgba(15,23,42,0.8);
}
.inventory-hero-chip-grid {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 0.75rem;
}
@media (min-width: 640px) {
    .inventory-hero-chip-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (min-width: 1180px) {
    .inventory-hero-chip-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
}
.inventory-hero-chip {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    min-height: 3rem;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,0.16);
    background: rgba(255,255,255,0.14);
    padding: 0.6rem 0.85rem;
    color: #fff;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.14);
}
.inventory-hero-chip i {
    display: inline-flex;
    width: 1.9rem;
    height: 1.9rem;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background: rgba(255,255,255,0.18);
}
.inventory-hero-chip span {
    display: block;
    min-width: 0;
}
.inventory-hero-chip .label {
    font-size: 0.68rem;
    font-weight: 800;
    color: rgba(224,242,254,0.9);
    text-transform: uppercase;
    letter-spacing: 0.08em;
}
.inventory-hero-chip .value {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 0.88rem;
    font-weight: 900;
}
.inventory-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    min-height: 2.65rem;
    border-radius: 0.95rem;
    padding: 0.72rem 0.95rem;
    font-size: 0.875rem;
    font-weight: 850;
    transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
}
.inventory-action:hover {
    transform: translateY(-1px);
}
.inventory-action-soft {
    border: 1px solid rgba(255,255,255,0.18);
    background: rgba(255,255,255,0.14);
    color: #fff;
}
.inventory-action-soft:hover {
    background: rgba(255,255,255,0.2);
}
.inventory-action-emerald {
    background: linear-gradient(135deg, #10b981, #047857);
    color: #fff;
    box-shadow: 0 18px 34px -24px rgba(16,185,129,0.82);
}
.inventory-action-blue {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    box-shadow: 0 18px 34px -24px rgba(37,99,235,0.82);
}
.inventory-action-rose {
    background: linear-gradient(135deg, #e11d48, #be123c);
    color: #fff;
    box-shadow: 0 18px 34px -24px rgba(225,29,72,0.82);
}
.inventory-snapshot-grid {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 1rem;
}
@media (min-width: 640px) {
    .inventory-snapshot-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (min-width: 1180px) {
    .inventory-snapshot-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
}
.inventory-snapshot-card {
    position: relative;
    overflow: hidden;
    border-radius: 1.25rem;
    border: 1px solid rgba(255,255,255,0.75);
    padding: 1rem;
    box-shadow: 0 18px 36px -30px rgba(15,23,42,0.42), inset 0 1px 0 rgba(255,255,255,0.78);
}
.inventory-snapshot-card::after {
    content: "";
    position: absolute;
    inset: -3rem -2rem auto auto;
    width: 8rem;
    aspect-ratio: 1;
    border-radius: 999px;
    background: rgba(255,255,255,0.42);
}
.inventory-snapshot-card > * {
    position: relative;
    z-index: 1;
}
.inventory-snapshot-stock { background: linear-gradient(135deg, #bbf7d0, #5eead4); }
.inventory-snapshot-risk { background: linear-gradient(135deg, #fed7aa, #facc15); }
.inventory-snapshot-value { background: linear-gradient(135deg, #bfdbfe, #93c5fd); }
.inventory-snapshot-location { background: linear-gradient(135deg, #ddd6fe, #c4b5fd); }
.inventory-snapshot-label {
    margin: 0;
    font-size: 0.7rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(15,23,42,0.62);
}
.inventory-snapshot-value-text {
    margin-top: 0.3rem;
    font-size: 1.45rem;
    line-height: 1.12;
    font-weight: 950;
    color: #0f172a;
}
.inventory-snapshot-meta {
    margin-top: 0.25rem;
    font-size: 0.78rem;
    font-weight: 750;
    color: rgba(15,23,42,0.66);
}
.show-card {
    overflow: hidden;
    background: linear-gradient(135deg, rgba(255,255,255,0.98), rgba(248,250,252,0.96));
    border-radius: 1.35rem;
    border: 1px solid rgba(226,232,240,0.9);
    box-shadow: 0 22px 48px -36px rgba(15,23,42,0.32), inset 0 1px 0 rgba(255,255,255,0.8);
    transition: box-shadow 0.2s, transform 0.2s;
}
.show-card:hover { box-shadow: 0 28px 62px -42px rgba(15,23,42,0.38), inset 0 1px 0 rgba(255,255,255,0.84); }
.show-card-header {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f5f9;
    font-weight: 700;
    font-size: 0.9375rem;
    color: #1e293b;
}
.show-card-header i {
    display: inline-flex;
    height: 2rem;
    width: 2rem;
    align-items: center;
    justify-content: center;
    border-radius: 0.625rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}
.show-metric-value { font-size: 1.75rem; font-weight: 900; color: #0f172a; line-height: 1.2; }
.show-metric-label { font-size: 0.6875rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; }
.show-metric-sub { font-size: 0.6875rem; color: #94a3b8; }
.inventory-detail-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 1.25rem;
}
@media (min-width: 1180px) {
    .inventory-detail-grid {
        grid-template-columns: minmax(0, 1.05fr) minmax(22rem, 0.95fr);
    }
}
.inventory-table-wrap {
    border-radius: 1rem;
    border: 1px solid rgba(226,232,240,0.84);
    background: rgba(255,255,255,0.76);
}
.inventory-table-wrap table thead tr {
    background: rgba(248,250,252,0.92);
}
.inventory-empty {
    border-radius: 1rem;
    border: 1px dashed rgba(148,163,184,0.5);
    background: rgba(248,250,252,0.72);
    padding: 1.5rem;
    text-align: center;
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 650;
}
</style>

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
$quantity = (int) ($item['quantity'] ?? 0);
$minQuantity = (int) ($item['min_quantity'] ?? 0);
$unitPrice = (float) ($item['unit_price'] ?? 0);
$totalValue = $quantity * $unitPrice;
$expiryDisplay = '—';
$expiryTone = 'text-slate-700';
if (!empty($item['expiry_date'])) {
    $daysUntilExpiry = (int) ceil((strtotime((string) $item['expiry_date']) - time()) / 86400);
    if ($daysUntilExpiry < 0) {
        $expiryDisplay = $tr('expiredLabel');
        $expiryTone = 'text-rose-800';
    } elseif ($daysUntilExpiry <= 30) {
        $expiryDisplay = $daysUntilExpiry . ' days';
        $expiryTone = 'text-amber-800';
    } else {
        $expiryDisplay = date('M j, Y', strtotime((string) $item['expiry_date']));
    }
}
?>

<div class="inventory-detail-shell min-h-screen bg-slate-50">
    <div class="px-0 sm:px-4 lg:px-5 xl:px-6 py-6 sm:py-8">

        <section class="inventory-hero-card mb-6">
            <div class="inventory-hero-content p-6 sm:p-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <div class="inventory-hero-avatar">
                        <i class="fas fa-box-open text-3xl"></i>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.18em] text-teal-100/90">
                            <span><?= esc($tr('inventoryItems')) ?></span>
                            <span class="h-1 w-1 rounded-full bg-teal-100"></span>
                            <span>#<?= (int) $item['id'] ?></span>
                        </div>
                        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-white sm:text-4xl"><?= esc($item['item_name']) ?></h1>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full border border-white/20 bg-white/15 px-3 py-1 text-xs font-black text-white"><?= esc(ucfirst((string) $item['category'])) ?></span>
                            <span class="inline-flex items-center rounded-full border border-white/20 bg-white/15 px-3 py-1 text-xs font-black text-white"><?= esc($statusText) ?></span>
                        </div>
                        <?php if (!empty($item['description'])): ?>
                            <p class="mt-4 max-w-3xl text-sm font-semibold leading-6 text-teal-50/90"><?= esc($item['description']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="<?= workflow_back('inventory', $return_to ?? null) ?>" class="inventory-action inventory-action-soft">
                        <i class="fas <?= $isRtl ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i><?= esc($tr('back')) ?>
                    </a>
                    <button onclick="receiveStock(<?= $item['id'] ?>)" class="inventory-action inventory-action-emerald">
                        <i class="fas fa-box-open"></i><?= esc($tr('receiveStock')) ?>
                    </button>
                    <button onclick="adjustStock(<?= $item['id'] ?>)" class="inventory-action inventory-action-emerald">
                        <i class="fas fa-plus-minus"></i><?= esc($tr('adjustStock')) ?>
                    </button>
                    <a href="<?= workflow_context_url('inventory/' . $item['id'] . '/edit', $return_to ?? null) ?>" class="inventory-action inventory-action-blue">
                        <i class="fas fa-edit"></i><?= esc($tr('edit')) ?>
                    </a>
                    <button onclick="deleteItem(<?= $item['id'] ?>)" class="inventory-action inventory-action-rose">
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

        <section class="mb-6 rounded-[1.5rem] border border-white/80 bg-gradient-to-br from-white via-teal-50/70 to-blue-50/60 p-4 shadow-xl shadow-slate-200/60 sm:p-5">
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
                <div class="mt-4 flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-900">
                    <i class="fas fa-exclamation-triangle text-amber-600"></i>
                    <span><?= esc($tr('lowStockAlert')) ?></span>
                </div>
            <?php endif; ?>
        </section>

        <!-- Legacy overview kept hidden while the detail page uses the stronger snapshot band above. -->
        <div class="hidden">
            <!-- Left: Item identity & description -->
            <div class="lg:col-span-2 show-card p-5">
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
                        <p class="text-lg font-bold text-gray-900"><?= formatCurrency($item['unit_price']) ?></p>
                    </div>
                    <div>
                        <p class="show-metric-label"><?= esc($tr('totalValue')) ?></p>
                        <p class="text-lg font-bold text-gray-900"><?= formatCurrency($item['quantity'] * $item['unit_price']) ?></p>
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
        <div class="mb-6 grid grid-cols-1 gap-5 xl:grid-cols-5">
            <div class="xl:col-span-3 show-card p-5 bg-gradient-to-br from-amber-50 via-white to-orange-50">
                <div class="flex items-center justify-between mb-4">
                    <div class="show-card-header mb-0 pb-0 border-b-0">
                        <i class="fas fa-truck-ramp-box text-sm" style="background:#fef3c7;color:#d97706;"></i>
                        <span><?= esc($tr('supplierMappings')) ?></span>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-100 text-amber-700"><?= count($supplier_links ?? []) ?> <?= esc($tr('supplier')) ?></span>
                </div>
                <?php if (!empty($supplier_links)): ?>
                    <div class="space-y-2">
                        <?php foreach (($supplier_links ?? []) as $link): ?>
                            <div class="rounded-2xl border border-amber-100 bg-white/78 p-3 shadow-sm">
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
                    <div class="inventory-empty"><?= esc($tr('noSupplierMappings')) ?></div>
                <?php endif; ?>
            </div>

            <div class="xl:col-span-2 show-card p-5 bg-gradient-to-br from-white via-emerald-50/70 to-teal-50">
                <div class="show-card-header mb-3 pb-0 border-b-0">
                    <i class="fas fa-link text-sm" style="background:#fef3c7;color:#d97706;"></i>
                    <span><?= esc($tr('addUpdateMapping')) ?></span>
                </div>
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
        <div class="mb-6 show-card p-5 bg-gradient-to-br from-white via-emerald-50/70 to-blue-50/70">
            <div class="flex items-center justify-between mb-4">
                <div class="show-card-header mb-0 pb-0 border-b-0">
                    <i class="fas fa-arrows-rotate text-sm" style="background:#d1fae5;color:#059669;"></i>
                    <span><?= esc($tr('movementsTitle')) ?></span>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-600"><?= esc($tr('rowsCount', ['count' => count($movements ?? [])])) ?></span>
            </div>
            <?php if (!empty($movements)): ?>
                <div class="inventory-table-wrap overflow-x-auto">
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
                <div class="inventory-empty">No stock movements recorded yet.</div>
            <?php endif; ?>
        </div>

        <!-- Batches -->
        <?php if (!empty($batches)): ?>
        <div class="mb-6 show-card p-5 bg-gradient-to-br from-white via-cyan-50/80 to-blue-50/70">
            <div class="flex items-center justify-between mb-4">
                <div class="show-card-header mb-0 pb-0 border-b-0">
                    <i class="fas fa-tags text-sm" style="background:#e0f2fe;color:#0284c7;"></i>
                    <span><?= esc($tr('batchesLotsFoundation')) ?></span>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-cyan-100 text-cyan-700"><?= count($batches) ?> batches</span>
            </div>
            <div class="inventory-table-wrap overflow-x-auto">
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
            <button onclick="addItemToReorder(<?= (int) $item['id'] ?>)" class="inventory-action bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-lg shadow-amber-500/20">
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
