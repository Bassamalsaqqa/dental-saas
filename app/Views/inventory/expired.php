<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php $tr = static fn(string $key, array $params = []) => lang('Inventory.' . $key, $params); ?>
<div class="v3-operational-view min-h-screen">
    <div class="container mx-auto px-4 py-6">
        <div class="mb-6 v3-surface border v3-border v3-radius p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 v3-radius text-white flex items-center justify-center">
                        <i class="fas fa-calendar-times text-xl"></i>
                    </div>
                    <div>
                        <h1 class="v3-title-scale font-bold text-gray-900"><?= esc($tr('expiredItems')) ?></h1>
                        <p class="text-gray-600"><?= esc($tr('expiredItemsSubtitle')) ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="<?= workflow_back('inventory', $return_to ?? null) ?>" class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200">
                        <i class="fas fa-arrow-left mr-2"></i><?= esc($tr('backToInventory')) ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="v3-radius v3-surface border v3-border overflow-hidden">
            <div class="px-6 py-4 border-b v3-border v3-surface flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900"><?= esc($tr('expiredItems')) ?></h3>
                <span class="text-sm text-slate-600"><?= esc($tr('rowsCount', ['count' => count($items ?? [])])) ?></span>
            </div>

            <?php if (!empty($items)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="v3-surface text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                            <tr>
                                <th class="px-4 py-3"><?= esc($tr('item')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('category')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('quantity')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('supplier')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('expiryDate')) ?></th>
                                <th class="px-4 py-3"><?= esc($tr('actions')) ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach (($items ?? []) as $item): ?>
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-900"><?= esc((string) ($item['item_name'] ?? '')) ?></div>
                                        <?php if (!empty($item['description'])): ?>
                                            <div class="text-xs text-slate-500 mt-1"><?= esc((string) $item['description']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-700"><?= esc((string) ($item['category'] ?? '')) ?></td>
                                    <td class="px-4 py-3 text-sm text-slate-700"><?= (int) ($item['quantity'] ?? 0) ?></td>
                                    <td class="px-4 py-3 text-sm text-slate-700"><?= esc((string) ($item['supplier'] ?? '-')) ?></td>
                                    <td class="px-4 py-3 text-sm text-rose-700 font-semibold"><?= !empty($item['expiry_date']) ? esc(formatDate((string) $item['expiry_date'])) : '-' ?></td>
                                    <td class="px-4 py-3">
                                        <a href="<?= base_url('inventory/' . (int) $item['id']) ?>" class="inline-flex items-center px-3 py-2 rounded-lg bg-rose-50 text-rose-700 text-xs font-semibold hover:bg-rose-100">
                                            <i class="fas fa-eye mr-2"></i><?= esc($tr('viewItem')) ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="px-6 py-10 text-center text-slate-600">
                    <?= esc($tr('noExpiredItems')) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
