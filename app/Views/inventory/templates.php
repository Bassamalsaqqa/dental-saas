<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php $tr = static fn(string $key, array $params = []) => lang('Inventory.' . $key, $params); ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-teal-50 to-blue-100">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-900"><?= esc($tr('starterTemplatesTitle')) ?></h1>
                <p class="text-gray-600"><?= esc($tr('starterTemplatesSubtitle')) ?></p>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?= base_url('inventory/suppliers') ?>" class="inline-flex items-center px-4 py-2 bg-white text-slate-700 rounded-lg border border-slate-200 shadow-sm hover:bg-slate-50">
                    <i class="fas fa-truck mr-2 text-indigo-600"></i><?= esc($tr('suppliers')) ?>
                </a>
                <a href="<?= base_url('inventory') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-semibold">
                    <i class="fas fa-arrow-left mr-2"></i><?= esc($tr('backToInventory')) ?>
                </a>
            </div>
        </div>

        <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl border border-white/40 p-6 mb-6">
            <form method="GET" action="<?= base_url('inventory/templates') ?>" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1"><?= esc($tr('search')) ?></label>
                    <input type="text" name="q" value="<?= esc((string) ($search_query ?? '')) ?>" placeholder="e.g. gloves, composite" class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1"><?= esc($tr('category')) ?></label>
                    <select name="category" class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                        <option value=""><?= esc($tr('allCategories')) ?></option>
                        <?php foreach (($categories ?? []) as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= ($selected_category ?? '') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg font-semibold hover:bg-teal-700">
                        <i class="fas fa-filter mr-2"></i><?= esc($tr('filter')) ?>
                    </button>
                    <a href="<?= base_url('inventory/templates') ?>" class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-700 rounded-lg font-semibold hover:bg-slate-200">
                        <?= esc($tr('reset')) ?>
                    </a>
                </div>
            </form>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-700"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= base_url('inventory/templates/import') ?>">
            <?= csrf_field() ?>
            <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl border border-white/40 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900"><?= esc($tr('templateCatalog')) ?></h2>
                        <p class="text-sm text-gray-500"><?= esc($tr('itemsAvailableCount', ['count' => count($templates ?? [])])) ?></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" id="selectAllTemplates" class="px-3 py-2 text-sm bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200"><?= esc($tr('selectAll')) ?></button>
                        <button type="button" id="clearAllTemplates" class="px-3 py-2 text-sm bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200"><?= esc($tr('clear')) ?></button>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg font-semibold hover:bg-emerald-700">
                            <i class="fas fa-download mr-2"></i><?= esc($tr('importSelected')) ?>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                            <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left"><input type="checkbox" id="masterTemplateCheckbox"></th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase"><?= esc($tr('item')) ?></th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase"><?= esc($tr('category')) ?></th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase"><?= esc($tr('unit')) ?></th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase"><?= esc($tr('minimumQuantityRequired')) ?></th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase"><?= esc($tr('defaultPrice')) ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (!empty($templates)): ?>
                                <?php foreach ($templates as $tpl): ?>
                                    <tr class="hover:bg-slate-50/80">
                                        <td class="px-4 py-3 align-top">
                                            <input type="checkbox" class="template-checkbox" name="template_ids[]" value="<?= (int) $tpl['id'] ?>">
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-gray-900"><?= esc($tpl['name']) ?></div>
                                            <?php if (!empty($tpl['description'])): ?>
                                                <div class="text-xs text-gray-500 mt-1"><?= esc($tpl['description']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800"><?= esc(ucfirst($tpl['category'])) ?></span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700"><?= esc($tpl['unit']) ?></td>
                                        <td class="px-4 py-3 text-sm text-gray-700"><?= (int) $tpl['default_min_quantity'] ?></td>
                                        <td class="px-4 py-3 text-sm text-gray-700"><?= formatCurrency((float) ($tpl['default_unit_price'] ?? 0)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500"><?= esc($tr('noTemplatesMatched')) ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const master = document.getElementById('masterTemplateCheckbox');
    const selectAllBtn = document.getElementById('selectAllTemplates');
    const clearAllBtn = document.getElementById('clearAllTemplates');

    function setAllTemplates(checked) {
        document.querySelectorAll('.template-checkbox').forEach((cb) => { cb.checked = checked; });
        if (master) master.checked = checked;
    }

    if (master) {
        master.addEventListener('change', function () { setAllTemplates(master.checked); });
    }
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function () { setAllTemplates(true); });
    }
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function () { setAllTemplates(false); });
    }
});
</script>
<?= $this->endSection() ?>
