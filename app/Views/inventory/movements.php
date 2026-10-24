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
<div class="v3-operational-view w-full space-y-5 px-3 py-4 sm:px-5 lg:px-6">
    <section class="overflow-hidden rounded-[1.65rem] border v3-border v3-bg-forest v3-text-on-primary">
        <div class="relative p-5 sm:p-6 lg:p-7">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_12%_14%,rgba(16,185,129,.56),transparent_30%),radial-gradient(circle_at_86%_8%,rgba(14,165,233,.36),transparent_28%),linear-gradient(135deg,#047857_0%,#0f766e_42%,#0f172a_100%)]"></div>
            <div class="relative grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(28rem,.76fr)] xl:items-center">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center v3-radius v3-surface v3-text-on-primary border v3-border-default">
                        <i class="fas fa-arrows-rotate text-2xl"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[0.2em] v3-text-primary"><?= esc($tr('inventoryManagement')) ?></p>
                        <h1 class="mt-1 text-2xl font-black tracking-tight v3-text-on-primary sm:text-3xl"><?= esc($tr('movementsTitle')) ?></h1>
                        <p class="mt-2 max-w-3xl text-sm font-semibold leading-6 v3-text-primary"><?= esc($tr('movementsSubtitle')) ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <div class="v3-radius border v3-border v3-surface p-4">
                        <p class="text-[11px] font-black uppercase tracking-wide v3-text-primary"><?= esc($tr('totalRecords')) ?></p>
                        <p class="mt-1 text-2xl font-black v3-text-on-primary"><?= count($movements) ?></p>
                    </div>
                    <div class="v3-radius border v3-border v3-bg-primary p-4">
                        <p class="text-[11px] font-black uppercase tracking-wide v3-text-primary"><?= esc($tr('change')) ?> +</p>
                        <p class="mt-1 text-2xl font-black v3-text-on-primary"><?= $inCount ?></p>
                    </div>
                    <div class="v3-radius border v3-border v3-bg-danger p-4">
                        <p class="text-[11px] font-black uppercase tracking-wide v3-text-danger"><?= esc($tr('change')) ?> -</p>
                        <p class="mt-1 text-2xl font-black v3-text-on-primary"><?= $outCount ?></p>
                    </div>
                    <div class="v3-radius border v3-border v3-soft-surface p-4">
                        <p class="text-[11px] font-black uppercase tracking-wide v3-accent-text"><?= esc($tr('allItems')) ?></p>
                        <p class="mt-1 text-2xl font-black v3-text-on-primary"><?= count($itemNames) ?></p>
                    </div>
                </div>
            </div>

            <div class="relative mt-5 flex flex-col gap-2 sm:flex-row sm:justify-end">
                <a href="<?= base_url('inventory/reorder-list') ?>" class="inline-flex items-center justify-center gap-2 v3-radius v3-surface px-4 py-2.5 text-sm font-black v3-text-on-primary border v3-border-default transition v3-hover-soft v3-btn v3-btn--secondary">
                    <i class="fas fa-cart-shopping"></i>
                    <span><?= esc($tr('reorderList')) ?></span>
                </a>
                <a href="<?= base_url('inventory') ?>" class="inline-flex items-center justify-center gap-2 v3-radius v3-surface px-4 py-2.5 text-sm font-black v3-text-primary transition v3-hover-primary v3-btn v3-btn--secondary">
                    <i class="fas fa-arrow-left rtl:rotate-180"></i>
                    <span><?= esc($tr('inventoryItems')) ?></span>
                </a>
            </div>
        </div>
    </section>

    <section class="rounded-[1.5rem] border v3-border v3-surface p-4 sm:p-5">
        <form method="get" action="<?= base_url('inventory/movements') ?>" class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_minmax(13rem,.8fr)_minmax(13rem,.8fr)_10rem_10rem_9rem_auto] xl:items-end">
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('search')) ?></label>
                <div class="relative">
                    <input type="text" name="q" value="<?= esc((string) ($filters['q'] ?? '')) ?>" class="w-full v3-radius border-2 v3-border v3-surface px-4 py-3 text-sm font-semibold v3-text-ink outline-none transition v3-border-default v3-focus-control focus:ring-4 v3-focus-control ltr:pl-10 rtl:pr-10 v3-input" placeholder="<?= esc($tr('itemSearchPlaceholder')) ?>">
                    <i class="fas fa-search pointer-events-none absolute top-1/2 /2 v3-text-muted ltr:left-3 rtl:right-3"></i>
                </div>
            </div>
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('movementType')) ?></label>
                <select name="movement_type" data-force-select2 data-allow-clear="false" class="w-full v3-radius border-2 v3-border v3-surface px-4 py-3 text-sm font-semibold v3-text-ink outline-none transition v3-border-default v3-focus-control focus:ring-4 v3-focus-control v3-select">
                    <?php foreach ($movementTypes as $value => $label): ?>
                        <option value="<?= esc((string) $value) ?>" <?= (string) ($filters['movement_type'] ?? '') === (string) $value ? 'selected' : '' ?>><?= esc((string) $label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('item')) ?></label>
                <select name="inventory_id" data-force-select2 data-allow-clear="false" class="w-full v3-radius border-2 v3-border v3-surface px-4 py-3 text-sm font-semibold v3-text-ink outline-none transition v3-border-default v3-focus-control focus:ring-4 v3-focus-control v3-select">
                    <option value="0"><?= esc($tr('allItems')) ?></option>
                    <?php foreach ($inventoryOptions as $inv): ?>
                        <option value="<?= (int) $inv['id'] ?>" <?= (int) ($filters['inventory_id'] ?? 0) === (int) $inv['id'] ? 'selected' : '' ?>><?= esc((string) $inv['item_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('from')) ?></label>
                <input type="text" data-clinic-date="1" name="date_from" value="<?= esc((string) ($filters['date_from'] ?? '')) ?>" class="w-full v3-radius border-2 v3-border v3-surface px-4 py-3 text-sm font-semibold v3-text-ink outline-none transition v3-border-default v3-focus-control focus:ring-4 v3-focus-control v3-input">
            </div>
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('to')) ?></label>
                <input type="text" data-clinic-date="1" name="date_to" value="<?= esc((string) ($filters['date_to'] ?? '')) ?>" class="w-full v3-radius border-2 v3-border v3-surface px-4 py-3 text-sm font-semibold v3-text-ink outline-none transition v3-border-default v3-focus-control focus:ring-4 v3-focus-control v3-input">
            </div>
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('totalRecords')) ?></label>
                <input type="number" min="1" max="200" name="limit" value="<?= (int) ($filters['limit'] ?? 100) ?>" class="w-full v3-radius border-2 v3-border v3-surface px-4 py-3 text-sm font-semibold v3-text-ink outline-none transition v3-border-default v3-focus-control focus:ring-4 v3-focus-control v3-input" title="<?= esc($tr('maxRows')) ?>">
            </div>
            <button type="submit" class="inline-flex items-center justify-center gap-2 v3-radius px-5 py-3 text-sm font-black v3-text-on-primary transition v3-btn v3-btn--primary">
                <i class="fas fa-filter"></i>
                <span><?= esc($tr('apply')) ?></span>
            </button>
        </form>
    </section>

    <section class="overflow-hidden rounded-[1.5rem] border v3-border v3-surface">
        <div class="flex flex-col gap-4 border-b v3-border p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-center gap-3">
                <span class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center v3-radius v3-text-on-primary">
                    <i class="fas fa-right-left"></i>
                </span>
                <div class="min-w-0">
                    <h2 class="text-xl font-black v3-text-ink"><?= esc($tr('movements')) ?></h2>
                    <p class="mt-1 text-sm font-semibold v3-text-muted"><?= esc($tr('rowsCount', ['count' => count($movements)])) ?></p>
                </div>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-full v3-bg-paid px-3 py-1.5 text-xs font-black v3-text-primary border v3-border-default">
                <span class="h-2 w-2 rounded-full v3-bg-primary"></span><?= $activeFilterCount ?> <?= esc($tr('filter')) ?>
            </span>
        </div>

        <?php if (!empty($movements)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="v3-surface">
                        <tr>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('date')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('item')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('status')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('change')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('beforeAfter')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('batch')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('cost')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('reference')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('actor')) ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y v3-border-default">
                        <?php foreach ($movements as $movement): ?>
                            <?php
                            $qtyChange = (int) ($movement['quantity_change'] ?? 0);
                            $isIncrease = $qtyChange >= 0;
                            $changeClass = $isIncrease ? 'v3-bg-paid v3-text-primary v3-border-default' : 'v3-bg-alert v3-text-danger v3-border-default';
                            $typeLabel = ucwords(str_replace('_', ' ', (string) ($movement['movement_type'] ?? 'unknown')));
                            $actorName = trim((string) (($movement['actor_first_name'] ?? '') . ' ' . ($movement['actor_last_name'] ?? '')));
                            $referenceType = (string) ($movement['reference_type'] ?? '');
                            $refText = $referenceType !== ''
                                ? ucwords(str_replace('_', ' ', $referenceType)) . (!empty($movement['reference_id']) ? (' #' . (int) $movement['reference_id']) : '')
                                : '-';
                            ?>
                            <tr class="align-top transition v3-hover-primary">
                                <td class="px-5 py-4 text-sm v3-text-ink">
                                    <div class="font-black v3-text-ink"><?= !empty($movement['created_at']) ? esc(formatDateTime($movement['created_at'])) : '-' ?></div>
                                    <?php if (!empty($movement['notes'])): ?>
                                        <div class="mt-1 max-w-xs text-xs font-semibold leading-5 v3-text-muted"><?= esc((string) $movement['notes']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-black v3-text-ink"><?= esc((string) ($movement['item_name'] ?? $tr('unknown'))) ?></div>
                                    <div class="mt-1 text-xs font-semibold v3-text-muted"><?= esc((string) ($movement['category'] ?? '')) ?></div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full v3-bg-soft px-3 py-1 text-xs font-black v3-text-ink border v3-border-default"><?= esc($typeLabel) ?></span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-black border <?= $changeClass ?>">
                                        <i class="fas <?= $isIncrease ? 'fa-arrow-up' : 'fa-arrow-down' ?>"></i>
                                        <?= $isIncrease ? '+' : '' ?><?= $qtyChange ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm font-bold v3-text-ink">
                                    <span><?= (int) ($movement['quantity_before'] ?? 0) ?></span>
                                    <span class="mx-1 v3-text-muted">-&gt;</span>
                                    <span class="font-black v3-text-ink"><?= (int) ($movement['quantity_after'] ?? 0) ?></span>
                                </td>
                                <td class="px-5 py-4 text-xs font-semibold leading-5 v3-text-muted">
                                    <?php if (!empty($movement['batch_lot_number']) || !empty($movement['batch_expiry_date'])): ?>
                                        <div><?= !empty($movement['batch_lot_number']) ? (esc($tr('lot')) . ': ' . esc((string) $movement['batch_lot_number'])) : esc($tr('batch')) ?></div>
                                        <?php if (!empty($movement['batch_expiry_date'])): ?>
                                            <div><?= esc($tr('expiry')) ?>: <?= esc(formatDate((string) $movement['batch_expiry_date'])) ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4 text-xs font-semibold leading-5 v3-text-ink">
                                    <?php if (($movement['unit_cost'] ?? null) !== null): ?>
                                        <div dir="ltr"><?= esc(formatCurrency((float) $movement['unit_cost'])) ?>/unit</div>
                                        <?php if (($movement['total_cost'] ?? null) !== null): ?>
                                            <div class="v3-text-muted" dir="ltr"><?= esc($tr('totalCost')) ?>: <?= esc(formatCurrency((float) $movement['total_cost'])) ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4 text-xs font-bold v3-text-ink"><?= esc($refText) ?></td>
                                <td class="px-5 py-4 text-xs font-bold v3-text-ink"><?= esc($actorName !== '' ? $actorName : '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="px-5 py-14 text-center">
                <div class="mx-auto flex max-w-md flex-col items-center">
                    <span class="flex h-14 w-14 items-center justify-center v3-radius v3-bg-paid v3-text-primary">
                        <i class="fas fa-arrows-rotate text-xl"></i>
                    </span>
                    <p class="mt-4 text-sm font-black v3-text-ink"><?= esc($tr('movementsEmpty')) ?></p>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>
<?= $this->endSection() ?>
