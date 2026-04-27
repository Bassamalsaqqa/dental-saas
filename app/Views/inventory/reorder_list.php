<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php $tr = static fn(string $key, array $params = []) => lang('Inventory.' . $key, $params); ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-amber-50 to-orange-100">
    <div class="container mx-auto px-4 py-6">
        <div class="mb-6 bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-cart-shopping text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900"><?= esc($tr('reorderList')) ?></h1>
                        <p class="text-gray-600"><?= esc($tr('reorderListSubtitle')) ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="<?= base_url('inventory/movements') ?>" class="inline-flex items-center px-4 py-2 rounded-lg bg-emerald-50 text-emerald-800 text-sm font-semibold border border-emerald-200 hover:bg-emerald-100">
                        <i class="fas fa-arrows-rotate mr-2"></i><?= esc($tr('movements')) ?>
                    </a>
                    <a href="<?= base_url('inventory/low-stock') ?>" class="inline-flex items-center px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700">
                        <i class="fas fa-exclamation-triangle mr-2"></i><?= esc($tr('lowStock')) ?>
                    </a>
                    <a href="<?= base_url('inventory') ?>" class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200">
                        <i class="fas fa-arrow-left mr-2"></i><?= esc($tr('backToInventory')) ?>
                    </a>
                </div>
            </div>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 text-sm font-medium">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800 text-sm font-medium">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <?php
                $draftCount = 0;
                $orderedCount = 0;
                $supplierNames = [];
                foreach (($reorder_items ?? []) as $r) {
                    if (($r['status'] ?? '') === 'draft') {
                        $draftCount++;
                    }
                    if (($r['status'] ?? '') === 'ordered') {
                        $orderedCount++;
                    }
                    $name = trim((string) ($r['supplier_name'] ?? ''));
                    if ($name !== '') {
                        $supplierNames[$name] = true;
                    }
                }
                $supplierCount = count($supplierNames);
            ?>
            <div class="rounded-2xl bg-white/80 border border-white/30 p-5 shadow-lg">
                <div class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= esc($tr('draftLines')) ?></div>
                <div class="mt-2 text-3xl font-black text-gray-900"><?= $draftCount ?></div>
            </div>
            <div class="rounded-2xl bg-white/80 border border-white/30 p-5 shadow-lg">
                <div class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= esc($tr('orderedLines')) ?></div>
                <div class="mt-2 text-3xl font-black text-gray-900"><?= $orderedCount ?></div>
            </div>
            <div class="rounded-2xl bg-white/80 border border-white/30 p-5 shadow-lg">
                <div class="text-xs uppercase tracking-wide text-gray-500 font-semibold"><?= esc($tr('suppliersInvolved')) ?></div>
                <div class="mt-2 text-3xl font-black text-gray-900"><?= $supplierCount ?></div>
            </div>
        </div>

        <div class="rounded-2xl bg-white/80 border border-white/30 shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900"><?= esc($tr('reorderLines')) ?></h3>
                <span class="text-sm text-slate-600"><?= esc($tr('rowsCount', ['count' => count($reorder_items ?? [])])) ?></span>
            </div>

            <?php if (!empty($reorder_items)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                            <tr>
                                <th class="px-4 py-3"><?= esc($tr('item')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('stockDetails')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('supplier')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('requested')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('status')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('notes')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('actions')) ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach (($reorder_items ?? []) as $row): ?>
                                <?php
                                    $status = (string) ($row['status'] ?? 'draft');
                                    $statusClasses = [
                                        'draft' => 'bg-amber-100 text-amber-800',
                                        'ordered' => 'bg-blue-100 text-blue-800',
                                        'received' => 'bg-emerald-100 text-emerald-800',
                                        'cancelled' => 'bg-slate-100 text-slate-700',
                                    ];
                                ?>
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-900"><?= esc($row['item_name'] ?? ('Item #' . (int) $row['inventory_id'])) ?></div>
                                        <div class="text-xs text-slate-500"><?= esc(ucfirst((string) ($row['category'] ?? ''))) ?> • <a class="text-blue-600 hover:text-blue-800" href="<?= base_url('inventory/' . (int) $row['inventory_id']) ?>"><?= esc($tr('viewItem')) ?></a></div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div><span class="font-semibold text-slate-900"><?= (int) ($row['current_qty'] ?? 0) ?></span> <?= esc((string) ($row['unit'] ?? 'units')) ?></div>
                                        <div class="text-xs text-slate-500"><?= esc($tr('minimum')) ?> <?= (int) ($row['min_quantity'] ?? 0) ?></div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-medium text-slate-900"><?= esc((string) ($row['supplier_name'] ?? 'Unassigned')) ?></div>
                                        <div class="text-xs text-slate-500 mt-0.5">
                                            <?php if (!empty($row['preferred_supplier_sku'])): ?>SKU: <?= esc((string) $row['preferred_supplier_sku']) ?><?php endif; ?>
                                            <?php if (!empty($row['preferred_pack_size_label'])): ?><?= !empty($row['preferred_supplier_sku']) ? ' • ' : '' ?><?= esc($tr('packSize')) ?>: <?= esc((string) $row['preferred_pack_size_label']) ?><?php endif; ?>
                                        </div>
                                        <div class="flex flex-wrap gap-2 mt-1">
                                            <?php if (!empty($row['supplier_phone'])): ?>
                                                <a href="tel:<?= esc((string) $row['supplier_phone']) ?>" class="inline-flex items-center px-2 py-1 rounded-md bg-slate-100 text-slate-700 text-xs hover:bg-slate-200"><i class="fas fa-phone mr-1"></i><?= esc($tr('call')) ?></a>
                                            <?php endif; ?>
                                            <?php if (!empty($row['supplier_email'])): ?>
                                                <a href="mailto:<?= esc((string) $row['supplier_email']) ?>" class="inline-flex items-center px-2 py-1 rounded-md bg-slate-100 text-slate-700 text-xs hover:bg-slate-200"><i class="fas fa-envelope mr-1"></i><?= esc($tr('emailShort')) ?></a>
                                            <?php endif; ?>
                                            <?php if (!empty($row['supplier_whatsapp'])): ?>
                                                <?php $wa = preg_replace('/\D+/', '', (string) $row['supplier_whatsapp']); ?>
                                                <a href="https://wa.me/<?= esc($wa) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-2 py-1 rounded-md bg-emerald-100 text-emerald-700 text-xs hover:bg-emerald-200"><i class="fab fa-whatsapp mr-1"></i><?= esc($tr('whatsApp')) ?></a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-semibold text-slate-900"><?= (int) ($row['requested_qty'] ?? 0) ?></td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold <?= $statusClasses[$status] ?? 'bg-slate-100 text-slate-700' ?>">
                                            <?= esc(ucfirst($status)) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-600"><?= esc((string) ($row['notes'] ?? '')) ?></td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col gap-2 min-w-[180px]">
                                            <form method="post" action="<?= base_url('inventory/reorder-list/' . (int) $row['id'] . '/status') ?>" class="flex gap-2">
                                                <?= csrf_field() ?>
                                                <input type="number" name="requested_qty" min="1" value="<?= (int) ($row['requested_qty'] ?? 1) ?>" class="w-20 px-2 py-1 border border-slate-300 rounded-md text-sm">
                                                <select name="status" data-force-select2 data-allow-clear="false" class="flex-1 px-2 py-1 border border-slate-300 rounded-md text-sm">
                                                    <?php foreach (['draft', 'ordered', 'received', 'cancelled'] as $statusOption): ?>
                                                        <option value="<?= $statusOption ?>" <?= $status === $statusOption ? 'selected' : '' ?>><?= ucfirst($statusOption) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" class="px-3 py-1 rounded-md bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700"><?= esc($tr('apply')) ?></button>
                                            </form>
                                            <form method="post" action="<?= base_url('inventory/reorder-list/' . (int) $row['id'] . '/delete') ?>" onsubmit="return confirm('<?= esc(lang('Inventory.removeReorderLineConfirm'), 'js') ?>');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="w-full px-3 py-1 rounded-md bg-rose-50 text-rose-700 text-xs font-semibold hover:bg-rose-100">
                                                    <i class="fas fa-trash mr-1"></i><?= esc($tr('removeLine')) ?>
                                                </button>
                                            </form>
                                            <div class="rounded-md border border-slate-200 bg-slate-50 p-2">
                                                <div class="text-[11px] font-semibold text-slate-600 uppercase tracking-wide mb-1"><?= esc($tr('supplierContact')) ?></div>
                                                <?php if (!empty($row['supplier_contacted_at'])): ?>
                                                    <div class="text-xs text-emerald-700 font-semibold">
                                                        <?= esc($tr('loggedVia', ['method' => ucfirst((string) ($row['supplier_contact_method'] ?? 'other'))])) ?>
                                                    </div>
                                                    <div class="text-[11px] text-slate-500"><?= esc(formatDateTime($row['supplier_contacted_at'])) ?></div>
                                                <?php else: ?>
                                                    <div class="text-xs text-slate-500 mb-2"><?= esc($tr('noContactLoggedYet')) ?></div>
                                                <?php endif; ?>
                                                <div class="grid grid-cols-2 gap-1">
                                                    <?php foreach (['phone' => 'Phone', 'email' => 'Email', 'whatsapp' => 'WA', 'other' => 'Other'] as $methodKey => $label): ?>
                                                        <form method="post" action="<?= base_url('inventory/reorder-list/' . (int) $row['id'] . '/contacted') ?>">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="method" value="<?= esc($methodKey) ?>">
                                                            <button type="submit" class="w-full px-2 py-1 rounded bg-white border border-slate-200 text-[11px] font-semibold text-slate-700 hover:bg-slate-100"><?= esc($label) ?></button>
                                                        </form>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="px-6 py-10 text-center">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mb-3">
                        <i class="fas fa-cart-shopping text-xl"></i>
                    </div>
                    <p class="text-slate-700 font-semibold"><?= esc($tr('noReorderLinesYet')) ?></p>
                    <p class="text-sm text-slate-500 mt-1"><?= esc($tr('addItemsFromLowStock')) ?></p>
                    <div class="mt-4 flex justify-center gap-3">
                        <a href="<?= base_url('inventory/low-stock') ?>" class="inline-flex items-center px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700">
                            <i class="fas fa-exclamation-triangle mr-2"></i><?= esc($tr('openLowStock')) ?>
                        </a>
                        <a href="<?= base_url('inventory') ?>" class="inline-flex items-center px-4 py-2 rounded-lg bg-slate-100 text-slate-700 text-sm font-semibold hover:bg-slate-200">
                            <i class="fas fa-boxes mr-2"></i><?= esc($tr('inventoryItems')) ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
