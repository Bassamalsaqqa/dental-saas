<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$tr = static fn(string $key, array $params = []) => lang('Inventory.' . $key, $params);
$reorderItems = is_array($reorder_items ?? null) ? $reorder_items : [];
$draftCount = 0;
$orderedCount = 0;
$supplierNames = [];

foreach ($reorderItems as $item) {
    $status = (string) ($item['status'] ?? '');
    if ($status === 'draft') {
        $draftCount++;
    }
    if ($status === 'ordered') {
        $orderedCount++;
    }

    $supplierName = trim((string) ($item['supplier_name'] ?? ''));
    if ($supplierName !== '') {
        $supplierNames[$supplierName] = true;
    }
}

$supplierCount = count($supplierNames);
$statusClasses = [
    'draft' => 'v3-bg-waiting v3-text-warning v3-border-default',
    'ordered' => 'v3-bg-scheduled v3-text-primary v3-border-default',
    'received' => 'v3-bg-paid v3-text-primary v3-border-default',
    'cancelled' => 'v3-bg-soft v3-text-ink v3-border-default',
];
?>
<div class="v3-operational-view w-full space-y-5 px-3 py-4 sm:px-5 lg:px-6">
    <section class="overflow-hidden v3-radius border v3-border v3-bg-forest v3-text-on-primary">
        <div class="relative p-5 sm:p-6 lg:p-7">
            <div class="absolute inset-0 v3-bg-panel"></div>
            <div class="relative grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(28rem,.76fr)] xl:items-center">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center v3-radius v3-surface v3-text-on-primary border v3-border-default">
                        <i class="fas fa-cart-shopping text-2xl"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[0.2em] v3-text-warning"><?= esc($tr('inventoryManagement')) ?></p>
                        <h1 class="mt-1 text-2xl font-black tracking-tight v3-text-on-primary sm:text-3xl"><?= esc($tr('reorderList')) ?></h1>
                        <p class="mt-2 max-w-3xl text-sm font-semibold leading-6 v3-text-warning"><?= esc($tr('reorderListSubtitle')) ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="v3-radius border v3-border v3-surface p-4">
                        <p class="text-xs font-black uppercase tracking-wide v3-text-warning"><?= esc($tr('draftLines')) ?></p>
                        <p class="mt-1 text-2xl font-black v3-text-on-primary"><?= $draftCount ?></p>
                    </div>
                    <div class="v3-radius border v3-border v3-soft-surface p-4">
                        <p class="text-xs font-black uppercase tracking-wide v3-accent-text"><?= esc($tr('orderedLines')) ?></p>
                        <p class="mt-1 text-2xl font-black v3-text-on-primary"><?= $orderedCount ?></p>
                    </div>
                    <div class="v3-radius border v3-border v3-bg-primary p-4">
                        <p class="text-xs font-black uppercase tracking-wide v3-text-primary"><?= esc($tr('suppliersInvolved')) ?></p>
                        <p class="mt-1 text-2xl font-black v3-text-on-primary"><?= $supplierCount ?></p>
                    </div>
                </div>
            </div>

            <div class="relative mt-5 flex flex-col gap-2 sm:flex-row sm:justify-end">
                <a href="<?= base_url('inventory/movements') ?>" class="inline-flex items-center justify-center gap-2 v3-radius v3-surface px-4 py-2.5 text-sm font-black v3-text-on-primary border v3-border-default transition v3-hover-soft v3-btn v3-btn--secondary">
                    <i class="fas fa-arrows-rotate"></i>
                    <span><?= esc($tr('movements')) ?></span>
                </a>
                <a href="<?= base_url('inventory/low-stock') ?>" class="inline-flex items-center justify-center gap-2 v3-radius v3-bg-danger px-4 py-2.5 text-sm font-black v3-text-on-primary transition v3-hover-danger v3-btn v3-btn--danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?= esc($tr('lowStockItems')) ?></span>
                </a>
                <a href="<?= base_url('inventory') ?>" class="inline-flex items-center justify-center gap-2 v3-radius v3-surface px-4 py-2.5 text-sm font-black v3-text-warning transition v3-hover-primary v3-btn v3-btn--secondary">
                    <i class="fas fa-arrow-left rtl:rotate-180"></i>
                    <span><?= esc($tr('backToInventory')) ?></span>
                </a>
            </div>
        </div>
    </section>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="v3-radius border v3-border-default v3-bg-paid p-4 text-sm font-bold v3-text-primary">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="v3-radius border v3-border-default v3-bg-alert p-4 text-sm font-bold v3-text-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <section class="overflow-hidden v3-radius border v3-border v3-surface">
        <div class="flex flex-col gap-4 border-b v3-border p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-center gap-3">
                <span class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center v3-radius v3-text-on-primary">
                    <i class="fas fa-list-check"></i>
                </span>
                <div class="min-w-0">
                    <h2 class="text-xl font-black v3-text-ink"><?= esc($tr('reorderLines')) ?></h2>
                    <p class="mt-1 text-sm font-semibold v3-text-muted"><?= esc($tr('rowsCount', ['count' => count($reorderItems)])) ?></p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 text-xs font-black">
                <span class="inline-flex items-center gap-2 rounded-full v3-bg-waiting px-3 py-1.5 v3-text-warning border v3-border-default">
                    <span class="h-2 w-2 rounded-full v3-bg-warning"></span><?= esc($tr('draftLines')) ?>
                </span>
                <span class="inline-flex items-center gap-2 rounded-full v3-soft-surface px-3 py-1.5 v3-accent-text border v3-border-default">
                    <span class="h-2 w-2 rounded-full v3-soft-surface"></span><?= esc($tr('orderedLines')) ?>
                </span>
            </div>
        </div>

        <?php if (!empty($reorderItems)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="v3-surface">
                        <tr>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('item')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('stockDetails')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('supplier')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('requested')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('status')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('notes')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('actions')) ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y v3-border-default">
                        <?php foreach ($reorderItems as $row): ?>
                            <?php
                            $status = (string) ($row['status'] ?? 'draft');
                            $inventoryId = (int) ($row['inventory_id'] ?? 0);
                            $supplierName = trim((string) ($row['supplier_name'] ?? ''));
                            $supplierName = $supplierName !== '' ? $supplierName : 'Unassigned';
                            ?>
                            <tr class="align-top transition v3-hover-primary">
                                <td class="px-5 py-4">
                                    <div class="font-black v3-text-ink"><?= esc($row['item_name'] ?? ('Item #' . $inventoryId)) ?></div>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs font-semibold v3-text-muted">
                                        <?php if (!empty($row['category'])): ?>
                                            <span><?= esc(ucfirst((string) $row['category'])) ?></span>
                                            <span aria-hidden="true">&middot;</span>
                                        <?php endif; ?>
                                        <a class="font-black v3-accent-text v3-hover-text-primary" href="<?= base_url('inventory/' . $inventoryId) ?>"><?= esc($tr('viewItem')) ?></a>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-sm">
                                    <div class="font-black v3-text-ink">
                                        <?= (int) ($row['current_qty'] ?? 0) ?> <span class="font-bold v3-text-muted"><?= esc((string) ($row['unit'] ?? 'units')) ?></span>
                                    </div>
                                    <div class="mt-1 text-xs font-bold v3-text-muted"><?= esc($tr('minimum')) ?> <?= (int) ($row['min_quantity'] ?? 0) ?></div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-black v3-text-ink"><?= esc($supplierName) ?></div>
                                    <div class="mt-1 text-xs font-semibold v3-text-muted">
                                        <?php if (!empty($row['preferred_supplier_sku'])): ?>SKU: <?= esc((string) $row['preferred_supplier_sku']) ?><?php endif; ?>
                                        <?php if (!empty($row['preferred_pack_size_label'])): ?><?= !empty($row['preferred_supplier_sku']) ? ' &middot; ' : '' ?><?= esc($tr('packSize')) ?>: <?= esc((string) $row['preferred_pack_size_label']) ?><?php endif; ?>
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <?php if (!empty($row['supplier_phone'])): ?>
                                            <a href="tel:<?= esc((string) $row['supplier_phone']) ?>" class="inline-flex items-center gap-1 rounded-lg v3-bg-soft px-2.5 py-1 text-xs font-black v3-text-ink transition v3-hover-soft v3-btn v3-btn--secondary">
                                                <i class="fas fa-phone"></i><?= esc($tr('call')) ?>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($row['supplier_email'])): ?>
                                            <a href="mailto:<?= esc((string) $row['supplier_email']) ?>" class="inline-flex items-center gap-1 rounded-lg v3-soft-surface px-2.5 py-1 text-xs font-black v3-accent-text transition v3-hover-primary v3-btn v3-btn--secondary">
                                                <i class="fas fa-envelope"></i><?= esc($tr('emailShort')) ?>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($row['supplier_whatsapp'])): ?>
                                            <?php $wa = preg_replace('/\D+/', '', (string) $row['supplier_whatsapp']); ?>
                                            <a href="https://wa.me/<?= esc($wa) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 rounded-lg v3-bg-paid px-2.5 py-1 text-xs font-black v3-text-primary transition v3-hover-primary v3-btn v3-btn--secondary">
                                                <i class="fab fa-whatsapp"></i><?= esc($tr('whatsApp')) ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-sm font-black v3-text-ink"><?= (int) ($row['requested_qty'] ?? 0) ?></td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-black border <?= $statusClasses[$status] ?? 'v3-bg-soft v3-text-ink v3-border-default' ?>">
                                        <?= esc(ucfirst($status)) ?>
                                    </span>
                                </td>
                                <td class="max-w-xs px-5 py-4 text-sm font-semibold leading-5 v3-text-muted"><?= esc((string) ($row['notes'] ?? '')) ?></td>
                                <td class="px-5 py-4">
                                    <div class="min-w-[20rem] space-y-3">
                                        <form method="post" action="<?= base_url('inventory/reorder-list/' . (int) $row['id'] . '/status') ?>" class="grid grid-cols-[5.5rem_minmax(0,1fr)_auto] gap-2">
                                            <?= csrf_field() ?>
                                            <input type="number" name="requested_qty" min="1" value="<?= (int) ($row['requested_qty'] ?? 1) ?>" class="w-full v3-radius border v3-border px-3 py-2 text-sm font-bold v3-text-ink outline-none transition v3-focus-control focus:ring-4 v3-focus-control v3-input">
                                            <select name="status" data-force-select2 data-allow-clear="false" class="w-full v3-radius border v3-border px-3 py-2 text-sm font-bold v3-text-ink outline-none transition v3-focus-control focus:ring-4 v3-focus-control v3-select">
                                                <?php foreach (['draft', 'ordered', 'received', 'cancelled'] as $statusOption): ?>
                                                    <option value="<?= esc($statusOption) ?>" <?= $status === $statusOption ? 'selected' : '' ?>><?= esc(ucfirst($statusOption)) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="v3-radius v3-soft-surface px-4 py-2 text-xs font-black v3-text-on-primary transition v3-hover-primary v3-btn v3-btn--primary"><?= esc($tr('apply')) ?></button>
                                        </form>

                                        <div class="v3-radius border v3-border v3-surface p-3">
                                            <div class="mb-2 flex items-center justify-between gap-3">
                                                <div class="text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('supplierContact')) ?></div>
                                                <form method="post" action="<?= base_url('inventory/reorder-list/' . (int) $row['id'] . '/delete') ?>" onsubmit="return confirm('<?= esc(lang('Inventory.removeReorderLineConfirm'), 'js') ?>');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg v3-bg-alert px-2.5 py-1 text-[11px] font-black v3-text-danger transition v3-hover-danger v3-btn v3-btn--danger">
                                                        <i class="fas fa-trash"></i><?= esc($tr('removeLine')) ?>
                                                    </button>
                                                </form>
                                            </div>

                                            <?php if (!empty($row['supplier_contacted_at'])): ?>
                                                <div class="text-xs font-black v3-text-primary">
                                                    <?= esc($tr('loggedVia', ['method' => ucfirst((string) ($row['supplier_contact_method'] ?? 'other'))])) ?>
                                                </div>
                                                <div class="mt-0.5 text-xs font-semibold v3-text-muted"><?= esc(formatDateTime($row['supplier_contacted_at'])) ?></div>
                                            <?php else: ?>
                                                <div class="mb-2 text-xs font-semibold v3-text-muted"><?= esc($tr('noContactLoggedYet')) ?></div>
                                            <?php endif; ?>

                                            <div class="mt-2 grid grid-cols-4 gap-1.5">
                                                <?php foreach (['phone' => 'Phone', 'email' => 'Email', 'whatsapp' => 'WA', 'other' => 'Other'] as $methodKey => $label): ?>
                                                    <form method="post" action="<?= base_url('inventory/reorder-list/' . (int) $row['id'] . '/contacted') ?>">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="method" value="<?= esc($methodKey) ?>">
                                                        <button type="submit" class="w-full rounded-lg border v3-border v3-surface px-2 py-1.5 text-[11px] font-black v3-text-ink transition v3-hover-soft v3-btn v3-btn--primary"><?= esc($label) ?></button>
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
            <div class="px-5 py-14 text-center">
                <div class="mx-auto flex max-w-md flex-col items-center">
                    <span class="flex h-14 w-14 items-center justify-center v3-radius v3-bg-waiting v3-text-warning">
                        <i class="fas fa-cart-shopping text-xl"></i>
                    </span>
                    <p class="mt-4 text-sm font-black v3-text-ink"><?= esc($tr('noReorderLinesYet')) ?></p>
                    <p class="mt-1 text-sm font-semibold v3-text-muted"><?= esc($tr('addItemsFromLowStock')) ?></p>
                    <div class="mt-5 flex flex-col justify-center gap-2 sm:flex-row">
                        <a href="<?= base_url('inventory/low-stock') ?>" class="inline-flex items-center justify-center gap-2 v3-radius v3-bg-danger px-4 py-2.5 text-sm font-black v3-text-on-primary transition v3-hover-danger v3-btn v3-btn--danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span><?= esc($tr('openLowStock')) ?></span>
                        </a>
                        <a href="<?= base_url('inventory') ?>" class="inline-flex items-center justify-center gap-2 v3-radius border v3-border v3-bg-soft px-4 py-2.5 text-sm font-black v3-text-ink transition v3-hover-soft v3-btn v3-btn--secondary">
                            <i class="fas fa-boxes"></i>
                            <span><?= esc($tr('inventoryItems')) ?></span>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>
<?= $this->endSection() ?>
