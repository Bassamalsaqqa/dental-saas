<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php $tr = static fn(string $key, array $params = []) => lang('Inventory.' . $key, $params); ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50 to-teal-100">
    <div class="container mx-auto px-4 py-6">
        <div class="mb-6 bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-arrows-rotate text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900"><?= esc($tr('movementsTitle')) ?></h1>
                        <p class="text-gray-600"><?= esc($tr('movementsSubtitle')) ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="<?= base_url('inventory/reorder-list') ?>" class="inline-flex items-center px-4 py-2 rounded-lg bg-amber-50 text-amber-800 text-sm font-semibold border border-amber-200 hover:bg-amber-100">
                        <i class="fas fa-cart-shopping mr-2"></i><?= esc($tr('reorderList')) ?>
                    </a>
                    <a href="<?= base_url('inventory') ?>" class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200">
                        <i class="fas fa-arrow-left mr-2"></i><?= esc($tr('inventoryItems')) ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="mb-6 rounded-2xl bg-white/80 border border-white/30 shadow-xl p-4">
            <form method="get" action="<?= base_url('inventory/movements') ?>" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1"><?= esc($tr('search')) ?></label>
                    <input type="text" name="q" value="<?= esc((string) ($filters['q'] ?? '')) ?>" class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white" placeholder="<?= esc($tr('itemSearchPlaceholder')) ?>">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1"><?= esc($tr('movementType')) ?></label>
                    <select name="movement_type" data-force-select2 data-allow-clear="false" class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white">
                        <?php foreach (($movement_types ?? []) as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= (string) ($filters['movement_type'] ?? '') === (string) $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1"><?= esc($tr('item')) ?></label>
                    <select name="inventory_id" data-force-select2 data-allow-clear="false" class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white">
                        <option value="0"><?= esc($tr('allItems')) ?></option>
                        <?php foreach (($inventory_options ?? []) as $inv): ?>
                            <option value="<?= (int) $inv['id'] ?>" <?= (int) ($filters['inventory_id'] ?? 0) === (int) $inv['id'] ? 'selected' : '' ?>><?= esc((string) $inv['item_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1"><?= esc($tr('from')) ?></label>
                    <input type="text" data-clinic-date="1" name="date_from" value="<?= esc((string) ($filters['date_from'] ?? '')) ?>" class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1"><?= esc($tr('to')) ?></label>
                    <input type="text" data-clinic-date="1" name="date_to" value="<?= esc((string) ($filters['date_to'] ?? '')) ?>" class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="number" min="1" max="200" name="limit" value="<?= (int) ($filters['limit'] ?? 100) ?>" class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white" title="<?= esc($tr('maxRows')) ?>">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                        <i class="fas fa-filter mr-1"></i><?= esc($tr('apply')) ?>
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl bg-white/80 border border-white/30 shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900"><?= esc($tr('movements')) ?></h3>
                <span class="text-sm text-slate-600"><?= esc($tr('rowsCount', ['count' => count($movements ?? [])])) ?></span>
            </div>
            <?php if (!empty($movements)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                            <tr>
                                <th class="px-4 py-3"><?= esc($tr('date')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('item')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('status')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('change')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('beforeAfter')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('batch')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('cost')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('reference')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('actor')) ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach (($movements ?? []) as $m): ?>
                                <?php
                                    $qtyChange = (int) ($m['quantity_change'] ?? 0);
                                    $changeClass = $qtyChange >= 0 ? 'text-emerald-700 bg-emerald-50' : 'text-rose-700 bg-rose-50';
                                    $typeLabel = ucwords(str_replace('_', ' ', (string) ($m['movement_type'] ?? 'unknown')));
                                    $actorName = trim((string) (($m['actor_first_name'] ?? '') . ' ' . ($m['actor_last_name'] ?? '')));
                                    $refText = ((string) ($m['reference_type'] ?? '') !== '')
                                        ? ucwords(str_replace('_', ' ', (string) $m['reference_type'])) . (!empty($m['reference_id']) ? (' #' . (int) $m['reference_id']) : '')
                                        : '-';
                                ?>
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-4 py-3 text-sm text-slate-700">
                                        <div class="font-medium"><?= !empty($m['created_at']) ? esc(formatDateTime($m['created_at'])) : '-' ?></div>
                                        <?php if (!empty($m['notes'])): ?><div class="text-xs text-slate-500"><?= esc((string) $m['notes']) ?></div><?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-semibold text-slate-900"><?= esc((string) ($m['item_name'] ?? $tr('unknown'))) ?></div>
                                        <div class="text-xs text-slate-500"><?= esc((string) ($m['category'] ?? '')) ?></div>
                                    </td>
                                    <td class="px-4 py-3"><span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700"><?= esc($typeLabel) ?></span></td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold <?= $changeClass ?>">
                                            <i class="fas <?= $qtyChange >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' ?> mr-1"></i><?= $qtyChange >= 0 ? '+' : '' ?><?= $qtyChange ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-700"><?= (int) ($m['quantity_before'] ?? 0) ?> -> <span class="font-semibold text-slate-900"><?= (int) ($m['quantity_after'] ?? 0) ?></span></td>
                                    <td class="px-4 py-3 text-xs text-slate-600">
                                        <?php if (!empty($m['batch_lot_number']) || !empty($m['batch_expiry_date'])): ?>
                                            <div><?= !empty($m['batch_lot_number']) ? (esc($tr('lot')) . ': ' . esc((string) $m['batch_lot_number'])) : esc($tr('batch')) ?></div>
                                            <?php if (!empty($m['batch_expiry_date'])): ?><div><?= esc($tr('expiry')) ?>: <?= esc(formatDate((string) $m['batch_expiry_date'])) ?></div><?php endif; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-700">
                                        <?php if ($m['unit_cost'] !== null): ?>
                                            <div><?= esc(formatCurrency((float) $m['unit_cost'])) ?>/unit</div>
                                            <?php if ($m['total_cost'] !== null): ?><div class="text-slate-500"><?= esc($tr('totalCost')) ?>: <?= esc(formatCurrency((float) $m['total_cost'])) ?></div><?php endif; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-700"><?= esc($refText) ?></td>
                                    <td class="px-4 py-3 text-xs text-slate-700"><?= esc($actorName !== '' ? $actorName : '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="px-6 py-10 text-center text-slate-600">
                    <?= esc($tr('movementsEmpty')) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
