<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php $tr = static fn(string $key, array $params = []) => lang('Inventory.' . $key, $params); ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-900"><?= esc($tr('suppliers')) ?></h1>
                <p class="text-gray-600"><?= esc($tr('suppliersSubtitle')) ?></p>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?= base_url('inventory/create') ?>" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg font-semibold hover:bg-emerald-700">
                    <i class="fas fa-plus mr-2"></i><?= esc($tr('addNewInventoryItem')) ?>
                </a>
                <a href="<?= base_url('inventory') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200">
                    <i class="fas fa-arrow-left mr-2"></i><?= esc($tr('backToInventory')) ?>
                </a>
            </div>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-700"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-1">
                <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl border border-white/40 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4"><?= esc($tr('addSupplier')) ?></h2>
                    <form method="POST" action="<?= base_url('inventory/suppliers/store') ?>" class="space-y-4">
                        <?= csrf_field() ?>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc($tr('name')) ?> *</label>
                            <input type="text" name="name" value="<?= old('name') ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc($tr('contactPerson')) ?></label>
                            <input type="text" name="contact_person" value="<?= old('contact_person') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc($tr('phone')) ?></label>
                                <input type="text" name="phone" value="<?= old('phone') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc($tr('whatsApp')) ?></label>
                                <input type="text" name="whatsapp" value="<?= old('whatsapp') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc($tr('email')) ?></label>
                            <input type="email" name="email" value="<?= old('email') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc($tr('address')) ?></label>
                            <input type="text" name="address" value="<?= old('address') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc($tr('notes')) ?></label>
                            <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg"><?= esc((string) old('notes')) ?></textarea>
                        </div>
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" <?= old('is_active', '1') ? 'checked' : '' ?>>
                            <span class="text-sm font-medium text-gray-700"><?= esc($tr('active')) ?></span>
                        </label>
                        <div class="pt-2">
                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700">
                                <i class="fas fa-save mr-2"></i><?= esc($tr('saveSupplier')) ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="xl:col-span-2">
                <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl border border-white/40 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900"><?= esc($tr('supplierDirectoryTitle')) ?></h2>
                        <span class="text-sm text-gray-500"><?= esc($tr('supplierCount', ['count' => count($suppliers_list ?? [])])) ?></span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase"><?= esc($tr('supplier')) ?></th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase"><?= esc($tr('contactPerson')) ?></th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase"><?= esc($tr('reach')) ?></th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase"><?= esc($tr('status')) ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (!empty($suppliers_list)): ?>
                                    <?php foreach ($suppliers_list as $supplier): ?>
                                        <tr class="hover:bg-slate-50/80">
                                            <td class="px-4 py-3">
                                                <div class="font-semibold text-gray-900"><?= esc($supplier['name']) ?></div>
                                                <?php if (!empty($supplier['address'])): ?>
                                                    <div class="text-xs text-gray-500"><?= esc($supplier['address']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                <?= esc($supplier['contact_person'] ?: '-') ?>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                <div><?= esc($supplier['phone'] ?: '-') ?></div>
                                                <?php if (!empty($supplier['whatsapp'])): ?><div class="text-xs text-emerald-700"><?= esc($tr('whatsApp')) ?>: <?= esc($supplier['whatsapp']) ?></div><?php endif; ?>
                                                <?php if (!empty($supplier['email'])): ?><div class="text-xs text-blue-700"><?= esc($supplier['email']) ?></div><?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold <?= !empty($supplier['is_active']) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' ?>">
                                                    <?= !empty($supplier['is_active']) ? esc($tr('active')) : esc($tr('inactive')) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-500"><?= esc($tr('manageSuppliers')) ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
