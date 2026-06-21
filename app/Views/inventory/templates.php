<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$tr = static fn(string $key, array $params = []) => lang('Inventory.' . $key, $params);
$templates = is_array($templates ?? null) ? $templates : [];
$categories = is_array($categories ?? null) ? $categories : [];
$selectedCategory = (string) ($selected_category ?? '');
$searchQuery = (string) ($search_query ?? '');
$activeFilterCount = ($selectedCategory !== '' ? 1 : 0) + ($searchQuery !== '' ? 1 : 0);
?>
<div class="w-full space-y-5 px-3 py-4 sm:px-5 lg:px-6">
    <section class="overflow-hidden rounded-[1.65rem] border border-white/20 bg-slate-950 text-white shadow-2xl shadow-teal-900/20">
        <div class="relative p-5 sm:p-6 lg:p-7">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_12%_12%,rgba(20,184,166,.55),transparent_30%),radial-gradient(circle_at_84%_10%,rgba(37,99,235,.42),transparent_27%),linear-gradient(135deg,#0f766e_0%,#155e75_45%,#0f172a_100%)]"></div>
            <div class="relative grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(28rem,.76fr)] xl:items-center">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white shadow-lg ring-1 ring-white/20 backdrop-blur">
                        <i class="fas fa-boxes-stacked text-2xl"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-teal-100"><?= esc($tr('starterTemplates')) ?></p>
                        <h1 class="mt-1 text-2xl font-black tracking-tight text-white sm:text-3xl"><?= esc($tr('starterTemplatesTitle')) ?></h1>
                        <p class="mt-2 max-w-3xl text-sm font-semibold leading-6 text-teal-50/90"><?= esc($tr('starterTemplatesSubtitle')) ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/15 bg-white/14 p-4 shadow-inner backdrop-blur">
                        <p class="text-[11px] font-black uppercase tracking-wide text-teal-100"><?= esc($tr('templateCatalog')) ?></p>
                        <p class="mt-1 text-2xl font-black text-white"><?= count($templates) ?></p>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-blue-400/18 p-4 shadow-inner backdrop-blur">
                        <p class="text-[11px] font-black uppercase tracking-wide text-blue-100"><?= esc($tr('category')) ?></p>
                        <p class="mt-1 text-2xl font-black text-white"><?= count($categories) ?></p>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-emerald-400/18 p-4 shadow-inner backdrop-blur">
                        <p class="text-[11px] font-black uppercase tracking-wide text-emerald-100"><?= esc($tr('filter')) ?></p>
                        <p class="mt-1 text-2xl font-black text-white"><?= $activeFilterCount ?></p>
                    </div>
                </div>
            </div>

            <div class="relative mt-5 flex flex-col gap-2 sm:flex-row sm:justify-end">
                <a href="<?= base_url('providers') ?>" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-black text-white ring-1 ring-white/20 transition hover:-translate-y-0.5 hover:bg-white/20">
                    <i class="fas fa-handshake"></i>
                    <span><?= esc($tr('suppliers')) ?></span>
                </a>
                <a href="<?= base_url('inventory') ?>" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-black text-teal-800 shadow-lg transition hover:-translate-y-0.5 hover:bg-teal-50">
                    <i class="fas fa-arrow-left rtl:rotate-180"></i>
                    <span><?= esc($tr('backToInventory')) ?></span>
                </a>
            </div>
        </div>
    </section>

    <section class="rounded-[1.5rem] border border-slate-200/80 bg-white p-4 shadow-xl shadow-slate-200/60 sm:p-5">
        <form method="GET" action="<?= base_url('inventory/templates') ?>" class="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1.1fr)_minmax(16rem,.7fr)_auto] lg:items-end">
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-600"><?= esc($tr('search')) ?></label>
                <div class="relative">
                    <input type="text" name="q" value="<?= esc($searchQuery) ?>" placeholder="<?= esc($tr('templateSearchPlaceholder')) ?>" class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition hover:border-slate-300 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/15 ltr:pl-10 rtl:pr-10">
                    <i class="fas fa-search pointer-events-none absolute top-1/2 -translate-y-1/2 text-slate-400 ltr:left-3 rtl:right-3"></i>
                </div>
            </div>
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-600"><?= esc($tr('category')) ?></label>
                <select name="category" data-force-select2 data-allow-clear="false" class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition hover:border-slate-300 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/15">
                    <option value=""><?= esc($tr('allCategories')) ?></option>
                    <?php foreach ($categories as $key => $label): ?>
                        <option value="<?= esc((string) $key) ?>" <?= $selectedCategory === (string) $key ? 'selected' : '' ?>><?= esc((string) $label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row lg:justify-end">
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-teal-600 to-cyan-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-teal-500/20 transition hover:-translate-y-0.5 hover:from-teal-700 hover:to-cyan-800">
                    <i class="fas fa-filter"></i>
                    <span><?= esc($tr('filter')) ?></span>
                </button>
                <a href="<?= base_url('inventory/templates') ?>" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-100 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-teal-200 hover:bg-teal-50 hover:text-teal-800">
                    <i class="fas fa-rotate-left"></i>
                    <span><?= esc($tr('reset')) ?></span>
                </a>
            </div>
        </form>
    </section>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-700"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-700"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= base_url('inventory/templates/import') ?>">
        <?= csrf_field() ?>
        <section class="overflow-hidden rounded-[1.5rem] border border-slate-200/80 bg-white shadow-xl shadow-slate-200/60">
            <div class="flex flex-col gap-4 border-b border-slate-200/80 bg-gradient-to-r from-white via-teal-50/90 to-cyan-50/80 p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-600 to-cyan-700 text-white shadow-lg shadow-teal-500/20">
                        <i class="fas fa-clipboard-list"></i>
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-xl font-black text-slate-950"><?= esc($tr('templateCatalog')) ?></h2>
                        <p class="mt-1 text-sm font-semibold text-slate-600"><?= esc($tr('itemsAvailableCount', ['count' => count($templates)])) ?></p>
                    </div>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <button type="button" id="selectAllTemplates" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:border-teal-200 hover:bg-teal-50 hover:text-teal-800"><?= esc($tr('selectAll')) ?></button>
                    <button type="button" id="clearAllTemplates" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700"><?= esc($tr('clear')) ?></button>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 px-5 py-2.5 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:from-emerald-700 hover:to-teal-800">
                        <i class="fas fa-download"></i>
                        <span><?= esc($tr('importSelected')) ?></span>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="w-14 px-5 py-4 text-start"><input type="checkbox" id="masterTemplateCheckbox" class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500"></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide text-slate-500"><?= esc($tr('item')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide text-slate-500"><?= esc($tr('category')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide text-slate-500"><?= esc($tr('unit')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide text-slate-500"><?= esc($tr('minimumQuantityRequired')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide text-slate-500"><?= esc($tr('defaultPrice')) ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (!empty($templates)): ?>
                            <?php foreach ($templates as $tpl): ?>
                                <?php
                                $categoryKey = (string) ($tpl['category'] ?? '');
                                $categoryLabel = (string) ($categories[$categoryKey] ?? ucfirst($categoryKey));
                                ?>
                                <tr class="transition hover:bg-teal-50/45">
                                    <td class="px-5 py-4 align-top">
                                        <input type="checkbox" class="template-checkbox h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500" name="template_ids[]" value="<?= (int) $tpl['id'] ?>">
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-black text-slate-950"><?= esc((string) $tpl['name']) ?></div>
                                        <?php if (!empty($tpl['description'])): ?>
                                            <div class="mt-1 max-w-2xl text-xs font-semibold leading-5 text-slate-500"><?= esc((string) $tpl['description']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-black text-blue-800 ring-1 ring-blue-200/70"><?= esc($categoryLabel) ?></span>
                                    </td>
                                    <td class="px-5 py-4 text-sm font-bold text-slate-700"><?= esc((string) ($tpl['unit'] ?? '')) ?></td>
                                    <td class="px-5 py-4 text-sm font-black text-slate-900"><?= (int) ($tpl['default_min_quantity'] ?? 0) ?></td>
                                    <td class="px-5 py-4 text-sm font-black text-emerald-700" dir="ltr"><?= formatCurrency((float) ($tpl['default_unit_price'] ?? 0)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center">
                                    <div class="mx-auto flex max-w-md flex-col items-center">
                                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                            <i class="fas fa-box-open text-xl"></i>
                                        </span>
                                        <p class="mt-4 text-sm font-black text-slate-700"><?= esc($tr('noTemplatesMatched')) ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </form>
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
