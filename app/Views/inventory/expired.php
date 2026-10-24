<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php $tr = static fn(string $key, array $params = []) => lang('Inventory.' . $key, $params); ?>
<div class="v3-operational-view min-h-screen">
    <div class="container mx-auto px-4 py-6">
        <div class="mb-6 v3-surface border v3-border v3-radius p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 v3-radius v3-text-on-primary flex items-center justify-center">
                        <i class="fas fa-calendar-times text-xl"></i>
                    </div>
                    <div>
                        <h1 class="v3-title-scale font-bold v3-text-ink"><?= esc($tr('expiredItems')) ?></h1>
                        <p class="v3-text-muted"><?= esc($tr('expiredItemsSubtitle')) ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="<?= workflow_back('inventory', $return_to ?? null) ?>" class="inline-flex items-center px-4 py-2 rounded-lg v3-bg-soft v3-text-ink text-sm font-semibold v3-hover-soft v3-btn v3-btn--secondary">
                        <i class="fas fa-arrow-left mr-2"></i><?= esc($tr('backToInventory')) ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="v3-radius v3-surface border v3-border overflow-hidden">
            <div class="px-6 py-4 border-b v3-border v3-surface flex items-center justify-between">
                <h3 class="text-lg font-semibold v3-text-ink"><?= esc($tr('expiredItems')) ?></h3>
                <span class="text-sm v3-text-muted"><?= esc($tr('rowsCount', ['count' => count($items ?? [])])) ?></span>
            </div>

            <?php if (!empty($items)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="v3-surface text-left text-xs font-semibold uppercase tracking-wide v3-text-muted">
                            <tr>
                                <th class="px-4 py-3"><?= esc($tr('item')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('category')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('quantity')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('supplier')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('expiryDate')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('actions')) ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y v3-border-default">
                            <?php foreach (($items ?? []) as $item): ?>
                                <tr class="v3-hover-soft">
                                    <td class="px-4 py-3">
                                        <div class="font-semibold v3-text-ink"><?= esc((string) ($item['item_name'] ?? '')) ?></div>
                                        <?php if (!empty($item['description'])): ?>
                                            <div class="text-xs v3-text-muted mt-1"><?= esc((string) $item['description']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm v3-text-ink"><?= esc((string) ($item['category'] ?? '')) ?></td>
                                    <td class="px-4 py-3 text-sm v3-text-ink"><?= (int) ($item['quantity'] ?? 0) ?></td>
                                    <td class="px-4 py-3 text-sm v3-text-ink"><?= esc((string) ($item['supplier'] ?? '-')) ?></td>
                                    <td class="px-4 py-3 text-sm v3-text-danger font-semibold"><?= !empty($item['expiry_date']) ? esc(formatDate((string) $item['expiry_date'])) : '-' ?></td>
                                    <td class="px-4 py-3">
                                        <a href="<?= base_url('inventory/' . (int) $item['id']) ?>" class="inline-flex items-center px-3 py-2 rounded-lg v3-bg-alert v3-text-danger text-xs font-semibold v3-hover-danger v3-btn v3-btn--danger">
                                            <i class="fas fa-eye mr-2"></i><?= esc($tr('viewItem')) ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="px-6 py-10 text-center v3-text-muted">
                    <?= esc($tr('noExpiredItems')) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
