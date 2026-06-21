<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$tr = static fn(string $key, array $params = []) => lang('Inventory.' . $key, $params);
$movements = is_array($movements ?? null) ? $movements : [];
$filters = is_array($filters ?? null) ? $filters : [];
$inventoryOptions = is_array($inventory_options ?? null) ? $inventory_options : [];
$movementTypes = is_array($movement_types ?? null) ? $movement_types : [];
$inCount = 0;
$outCount = 0;
$itemNames = [];

foreach ($movements as $movement) {
    $quantityChange = (int) ($movement['quantity_change'] ?? 0);
    if ($quantityChange >= 0) {
        $inCount++;
    } else {
        $outCount++;
    }

    $itemName = trim((string) ($movement['item_name'] ?? ''));
    if ($itemName !== '') {
        $itemNames[$itemName] = true;
    }
}

$activeFilterCount = 0;
foreach (['movement_type', 'date_from', 'date_to', 'q'] as $filterKey) {
    if (trim((string) ($filters[$filterKey] ?? '')) !== '') {
        $activeFilterCount++;
    }
}
if ((int) ($filters['inventory_id'] ?? 0) > 0) {
    $activeFilterCount++;
}
?>
<div class="w-full space-y-5 px-3 py-4 sm:px-5 lg:px-6">
    <section class="overflow-hidden rounded-[1.65rem] border border-white/20 bg-slate-950 text-white shadow-2xl shadow-emerald-900/20">
        <div class="relative p-5 sm:p-6 lg:p-7">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_12%_14%,rgba(16,185,129,.56),transparent_30%),radial-gradient(circle_at_86%_8%,rgba(14,165,233,.36),transparent_28%),linear-gradient(135deg,#047857_0%,#0f766e_42%,#0f172a_100%)]"></div>
            <div class="relative grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(28rem,.76fr)] xl:items-center">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white shadow-lg ring-1 ring-white/20 backdrop-blur">
                        <i class="fas fa-arrows-rotate text-2xl"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-emerald-100"><?= esc($tr('inventoryManagement')) ?></p>
                        <h1 class="mt-1 text-2xl font-black tracking-tight text-white sm:text-3xl"><?= esc($tr('movementsTitle')) ?></h1>
                        <p class="mt-2 max-w-3xl text-sm font-semibold leading-6 text-emerald-50/90"><?= esc($tr('movementsSubtitle')) ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <div class="rounded-2xl border border-white/15 bg-white/14 p-4 shadow-inner backdrop-blur">
                        <p class="text-[11px] font-black uppercase tracking-wide text-emerald-100"><?= esc($tr('totalRecords')) ?></p>
                        <p class="mt-1 text-2xl font-black text-white"><?= count($movements) ?></p>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-emerald-400/18 p-4 shadow-inner backdrop-blur">
                        <p class="text-[11px] font-black uppercase tracking-wide text-emerald-100"><?= esc($tr('change')) ?> +</p>
                        <p class="mt-1 text-2xl font-black text-white"><?= $inCount ?></p>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-rose-400/18 p-4 shadow-inner backdrop-blur">
                        <p class="text-[11px] font-black uppercase tracking-wide text-rose-100"><?= esc($tr('change')) ?> -</p>
                        <p class="mt-1 text-2xl font-black text-white"><?= $outCount ?></p>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-cyan-400/18 p-4 shadow-inner backdrop-blur">
                        <p class="text-[11px] font-black uppercase tracking-wide text-cyan-100"><?= esc($tr('allItems')) ?></p>
                        <p class="mt-1 text-2xl font-black text-white"><?= count($itemNames) ?></p>
                    </div>
                </div>
            </div>

            <div class="relative mt-5 flex flex-col gap-2 sm:flex-row sm:justify-end">
                <a href="<?= base_url('inventory/reorder-list') ?>" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-black text-white ring-1 ring-white/20 transition hover:-translate-y-0.5 hover:bg-white/20">
                    <i class="fas fa-cart-shopping"></i>
                    <span><?= esc($tr('reorderList')) ?></span>
                </a>
                <a href="<?= base_url('inventory') ?>" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-black text-emerald-800 shadow-lg transition hover:-translate-y-0.5 hover:bg-emerald-50">
                    <i class="fas fa-arrow-left rtl:rotate-180"></i>
                    <span><?= esc($tr('inventoryItems')) ?></span>
                </a>
            </div>
        </div>
    </section>

    <section class="rounded-[1.5rem] border border-slate-200/80 bg-white p-4 shadow-xl shadow-slate-200/60 sm:p-5">
        <form method="get" action="<?= base_url('inventory/movements') ?>" class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_minmax(13rem,.8fr)_minmax(13rem,.8fr)_10rem_10rem_9rem_auto] xl:items-end">
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-600"><?= esc($tr('search')) ?></label>
                <div class="relative">
                    <input type="text" name="q" value="<?= esc((string) ($filters['q'] ?? '')) ?>" class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition hover:border-slate-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15 ltr:pl-10 rtl:pr-10" placeholder="<?= esc($tr('itemSearchPlaceholder')) ?>">
                    <i class="fas fa-search pointer-events-none absolute top-1/2 -translate-y-1/2 text-slate-400 ltr:left-3 rtl:right-3"></i>
                </div>
            </div>
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-600"><?= esc($tr('movementType')) ?></label>
                <select name="movement_type" data-force-select2 data-allow-clear="false" class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition hover:border-slate-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15">
                    <?php foreach ($movementTypes as $value => $label): ?>
                        <option value="<?= esc((string) $value) ?>" <?= (string) ($filters['movement_type'] ?? '') === (string) $value ? 'selected' : '' ?>><?= esc((string) $label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-600"><?= esc($tr('item')) ?></label>
                <select name="inventory_id" data-force-select2 data-allow-clear="false" class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition hover:border-slate-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15">
                    <option value="0"><?= esc($tr('allItems')) ?></option>
                    <?php foreach ($inventoryOptions as $inv): ?>
                        <option value="<?= (int) $inv['id'] ?>" <?= (int) ($filters['inventory_id'] ?? 0) === (int) $inv['id'] ? 'selected' : '' ?>><?= esc((string) $inv['item_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-600"><?= esc($tr('from')) ?></label>
                <input type="text" data-clinic-date="1" name="date_from" value="<?= esc((string) ($filters['date_from'] ?? '')) ?>" class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition hover:border-slate-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15">
            </div>
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-600"><?= esc($tr('to')) ?></label>
                <input type="text" data-clinic-date="1" name="date_to" value="<?= esc((string) ($filters['date_to'] ?? '')) ?>" class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition hover:border-slate-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15">
            </div>
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-600"><?= esc($tr('totalRecords')) ?></label>
                <input type="number" min="1" max="200" name="limit" value="<?= (int) ($filters['limit'] ?? 100) ?>" class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition hover:border-slate-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15" title="<?= esc($tr('maxRows')) ?>">
            </div>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:from-emerald-700 hover:to-teal-800">
                <i class="fas fa-filter"></i>
                <span><?= esc($tr('apply')) ?></span>
            </button>
        </form>
    </section>

    <section class="overflow-hidden rounded-[1.5rem] border border-slate-200/80 bg-white shadow-xl shadow-slate-200/60">
        <div class="flex flex-col gap-4 border-b border-slate-200/80 bg-gradient-to-r from-white via-emerald-50/90 to-teal-50/80 p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-center gap-3">
                <span class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 text-white shadow-lg shadow-emerald-500/20">
                    <i class="fas fa-right-left"></i>
                </span>
                <div class="min-w-0">
                    <h2 class="text-xl font-black text-slate-950"><?= esc($tr('movements')) ?></h2>
                    <p class="mt-1 text-sm font-semibold text-slate-600"><?= esc($tr('rowsCount', ['count' => count($movements)])) ?></p>
                </div>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-black text-emerald-800 ring-1 ring-emerald-200/70">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span><?= $activeFilterCount ?> <?= esc($tr('filter')) ?>
            </span>
        </div>

        <?php if (!empty($movements)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide text-slate-500"><?= esc($tr('date')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide text-slate-500"><?= esc($tr('item')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide text-slate-500"><?= esc($tr('status')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide text-slate-500"><?= esc($tr('change')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide text-slate-500"><?= esc($tr('beforeAfter')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide text-slate-500"><?= esc($tr('batch')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide text-slate-500"><?= esc($tr('cost')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide text-slate-500"><?= esc($tr('reference')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide text-slate-500"><?= esc($tr('actor')) ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($movements as $movement): ?>
                            <?php
                            $qtyChange = (int) ($movement['quantity_change'] ?? 0);
                            $isIncrease = $qtyChange >= 0;
                            $changeClass = $isIncrease ? 'bg-emerald-100 text-emerald-800 ring-emerald-200/70' : 'bg-rose-100 text-rose-800 ring-rose-200/70';
                            $typeLabel = ucwords(str_replace('_', ' ', (string) ($movement['movement_type'] ?? 'unknown')));
                            $actorName = trim((string) (($movement['actor_first_name'] ?? '') . ' ' . ($movement['actor_last_name'] ?? '')));
                            $referenceType = (string) ($movement['reference_type'] ?? '');
                            $refText = $referenceType !== ''
                                ? ucwords(str_replace('_', ' ', $referenceType)) . (!empty($movement['reference_id']) ? (' #' . (int) $movement['reference_id']) : '')
                                : '-';
                            ?>
                            <tr class="align-top transition hover:bg-emerald-50/45">
                                <td class="px-5 py-4 text-sm text-slate-700">
                                    <div class="font-black text-slate-900"><?= !empty($movement['created_at']) ? esc(formatDateTime($movement['created_at'])) : '-' ?></div>
                                    <?php if (!empty($movement['notes'])): ?>
                                        <div class="mt-1 max-w-xs text-xs font-semibold leading-5 text-slate-500"><?= esc((string) $movement['notes']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-black text-slate-950"><?= esc((string) ($movement['item_name'] ?? $tr('unknown'))) ?></div>
                                    <div class="mt-1 text-xs font-semibold text-slate-500"><?= esc((string) ($movement['category'] ?? '')) ?></div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700 ring-1 ring-slate-200/70"><?= esc($typeLabel) ?></span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-black ring-1 <?= $changeClass ?>">
                                        <i class="fas <?= $isIncrease ? 'fa-arrow-up' : 'fa-arrow-down' ?>"></i>
                                        <?= $isIncrease ? '+' : '' ?><?= $qtyChange ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm font-bold text-slate-700">
                                    <span><?= (int) ($movement['quantity_before'] ?? 0) ?></span>
                                    <span class="mx-1 text-slate-400">-&gt;</span>
                                    <span class="font-black text-slate-950"><?= (int) ($movement['quantity_after'] ?? 0) ?></span>
                                </td>
                                <td class="px-5 py-4 text-xs font-semibold leading-5 text-slate-600">
                                    <?php if (!empty($movement['batch_lot_number']) || !empty($movement['batch_expiry_date'])): ?>
                                        <div><?= !empty($movement['batch_lot_number']) ? (esc($tr('lot')) . ': ' . esc((string) $movement['batch_lot_number'])) : esc($tr('batch')) ?></div>
                                        <?php if (!empty($movement['batch_expiry_date'])): ?>
                                            <div><?= esc($tr('expiry')) ?>: <?= esc(formatDate((string) $movement['batch_expiry_date'])) ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4 text-xs font-semibold leading-5 text-slate-700">
                                    <?php if (($movement['unit_cost'] ?? null) !== null): ?>
                                        <div dir="ltr"><?= esc(formatCurrency((float) $movement['unit_cost'])) ?>/unit</div>
                                        <?php if (($movement['total_cost'] ?? null) !== null): ?>
                                            <div class="text-slate-500" dir="ltr"><?= esc($tr('totalCost')) ?>: <?= esc(formatCurrency((float) $movement['total_cost'])) ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4 text-xs font-bold text-slate-700"><?= esc($refText) ?></td>
                                <td class="px-5 py-4 text-xs font-bold text-slate-700"><?= esc($actorName !== '' ? $actorName : '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="px-5 py-14 text-center">
                <div class="mx-auto flex max-w-md flex-col items-center">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                        <i class="fas fa-arrows-rotate text-xl"></i>
                    </span>
                    <p class="mt-4 text-sm font-black text-slate-700"><?= esc($tr('movementsEmpty')) ?></p>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>
<?= $this->endSection() ?>
