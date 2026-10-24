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
<div class="v3-operational-view w-full space-y-5 px-3 py-4 sm:px-5 lg:px-6">
    <section class="overflow-hidden v3-radius border v3-border v3-bg-forest v3-text-on-primary">
        <div class="relative p-5 sm:p-6 lg:p-7">
            <div class="absolute inset-0 v3-bg-panel"></div>
            <div class="relative grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(28rem,.76fr)] xl:items-center">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center v3-radius v3-surface v3-text-on-primary border v3-border-default">
                        <i class="fas fa-boxes-stacked text-2xl"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[0.2em] v3-text-primary"><?= esc($tr('starterTemplates')) ?></p>
                        <h1 class="mt-1 text-2xl font-black tracking-tight v3-text-on-primary sm:text-3xl"><?= esc($tr('starterTemplatesTitle')) ?></h1>
                        <p class="mt-2 max-w-3xl text-sm font-semibold leading-6 v3-text-primary"><?= esc($tr('starterTemplatesSubtitle')) ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="v3-radius border v3-border v3-surface p-4">
                        <p class="text-[11px] font-black uppercase tracking-wide v3-text-primary"><?= esc($tr('templateCatalog')) ?></p>
                        <p class="mt-1 text-2xl font-black v3-text-on-primary"><?= count($templates) ?></p>
                    </div>
                    <div class="v3-radius border v3-border v3-soft-surface p-4">
                        <p class="text-[11px] font-black uppercase tracking-wide v3-accent-text"><?= esc($tr('category')) ?></p>
                        <p class="mt-1 text-2xl font-black v3-text-on-primary"><?= count($categories) ?></p>
                    </div>
                    <div class="v3-radius border v3-border v3-bg-primary p-4">
                        <p class="text-[11px] font-black uppercase tracking-wide v3-text-primary"><?= esc($tr('filter')) ?></p>
                        <p class="mt-1 text-2xl font-black v3-text-on-primary"><?= $activeFilterCount ?></p>
                    </div>
                </div>
            </div>

            <div class="relative mt-5 flex flex-col gap-2 sm:flex-row sm:justify-end">
                <a href="<?= base_url('providers') ?>" class="inline-flex items-center justify-center gap-2 v3-radius v3-surface px-4 py-2.5 text-sm font-black v3-text-on-primary border v3-border-default transition v3-hover-soft v3-btn v3-btn--secondary">
                    <i class="fas fa-handshake"></i>
                    <span><?= esc($tr('suppliers')) ?></span>
                </a>
                <a href="<?= base_url('inventory') ?>" class="inline-flex items-center justify-center gap-2 v3-radius v3-surface px-4 py-2.5 text-sm font-black v3-text-primary transition v3-hover-primary v3-btn v3-btn--secondary">
                    <i class="fas fa-arrow-left rtl:rotate-180"></i>
                    <span><?= esc($tr('backToInventory')) ?></span>
                </a>
            </div>
        </div>
    </section>

    <section class="v3-radius border v3-border v3-surface p-4 sm:p-5">
        <form method="GET" action="<?= base_url('inventory/templates') ?>" class="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1.1fr)_minmax(16rem,.7fr)_auto] lg:items-end">
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('search')) ?></label>
                <div class="relative">
                    <input type="text" name="q" value="<?= esc($searchQuery) ?>" placeholder="<?= esc($tr('templateSearchPlaceholder')) ?>" class="w-full v3-radius border-2 v3-border v3-surface px-4 py-3 text-sm font-semibold v3-text-ink outline-none transition v3-border-default v3-focus-control focus:ring-4 v3-focus-control ltr:pl-10 rtl:pr-10 v3-input">
                    <i class="fas fa-search pointer-events-none absolute top-1/2 /2 v3-text-muted ltr:left-3 rtl:right-3"></i>
                </div>
            </div>
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('category')) ?></label>
                <select name="category" data-force-select2 data-allow-clear="false" class="w-full v3-radius border-2 v3-border v3-surface px-4 py-3 text-sm font-semibold v3-text-ink outline-none transition v3-border-default v3-focus-control focus:ring-4 v3-focus-control v3-select">
                    <option value=""><?= esc($tr('allCategories')) ?></option>
                    <?php foreach ($categories as $key => $label): ?>
                        <option value="<?= esc((string) $key) ?>" <?= $selectedCategory === (string) $key ? 'selected' : '' ?>><?= esc((string) $label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row lg:justify-end">
                <button type="submit" class="inline-flex items-center justify-center gap-2 v3-radius px-5 py-3 text-sm font-black v3-text-on-primary transition v3-btn v3-btn--primary">
                    <i class="fas fa-filter"></i>
                    <span><?= esc($tr('filter')) ?></span>
                </button>
                <a href="<?= base_url('inventory/templates') ?>" class="inline-flex items-center justify-center gap-2 v3-radius border v3-border v3-bg-soft px-5 py-3 text-sm font-black v3-text-ink transition v3-border-default v3-hover-primary v3-hover-text-primary v3-btn v3-btn--secondary">
                    <i class="fas fa-rotate-left"></i>
                    <span><?= esc($tr('reset')) ?></span>
                </a>
            </div>
        </form>
    </section>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="v3-radius border v3-border-default v3-bg-alert p-4 text-sm font-bold v3-text-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="v3-radius border v3-border-default v3-bg-paid p-4 text-sm font-bold v3-text-primary"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= base_url('inventory/templates/import') ?>">
        <?= csrf_field() ?>
        <section class="overflow-hidden v3-radius border v3-border v3-surface">
            <div class="flex flex-col gap-4 border-b v3-border p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center v3-radius v3-text-on-primary">
                        <i class="fas fa-clipboard-list"></i>
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-xl font-black v3-text-ink"><?= esc($tr('templateCatalog')) ?></h2>
                        <p class="mt-1 text-sm font-semibold v3-text-muted"><?= esc($tr('itemsAvailableCount', ['count' => count($templates)])) ?></p>
                    </div>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <button type="button" id="selectAllTemplates" class="inline-flex items-center justify-center v3-radius border v3-border v3-surface px-4 py-2.5 text-sm font-black v3-text-ink transition v3-border-default v3-hover-primary v3-hover-text-primary v3-btn v3-btn--secondary"><?= esc($tr('selectAll')) ?></button>
                    <button type="button" id="clearAllTemplates" class="inline-flex items-center justify-center v3-radius border v3-border v3-surface px-4 py-2.5 text-sm font-black v3-text-ink transition v3-border-default v3-hover-danger v3-hover-text-danger v3-btn v3-btn--secondary"><?= esc($tr('clear')) ?></button>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 v3-radius px-5 py-2.5 text-sm font-black v3-text-on-primary transition v3-btn v3-btn--primary">
                        <i class="fas fa-download"></i>
                        <span><?= esc($tr('importSelected')) ?></span>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="v3-surface">
                        <tr>
                            <th class="w-14 px-5 py-4 text-start"><input type="checkbox" id="masterTemplateCheckbox" class="h-4 w-4 rounded v3-border v3-text-primary v3-focus-control v3-checkbox"></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('item')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('category')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('unit')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('minimumQuantityRequired')) ?></th>
                            <th class="px-5 py-4 text-start text-xs font-black uppercase tracking-wide v3-text-muted"><?= esc($tr('defaultPrice')) ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y v3-border-default">
                        <?php if (!empty($templates)): ?>
                            <?php foreach ($templates as $tpl): ?>
                                <?php
                                $categoryKey = (string) ($tpl['category'] ?? '');
                                $categoryLabel = (string) ($categories[$categoryKey] ?? ucfirst($categoryKey));
                                ?>
                                <tr class="transition v3-hover-primary">
                                    <td class="px-5 py-4 align-top">
                                        <input type="checkbox" class="template-checkbox h-4 w-4 rounded v3-border v3-text-primary v3-focus-control v3-checkbox" name="template_ids[]" value="<?= (int) $tpl['id'] ?>">
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-black v3-text-ink"><?= esc((string) $tpl['name']) ?></div>
                                        <?php if (!empty($tpl['description'])): ?>
                                            <div class="mt-1 max-w-2xl text-xs font-semibold leading-5 v3-text-muted"><?= esc((string) $tpl['description']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center rounded-full v3-soft-surface px-3 py-1 text-xs font-black v3-accent-text border v3-border-default"><?= esc($categoryLabel) ?></span>
                                    </td>
                                    <td class="px-5 py-4 text-sm font-bold v3-text-ink"><?= esc((string) ($tpl['unit'] ?? '')) ?></td>
                                    <td class="px-5 py-4 text-sm font-black v3-text-ink"><?= (int) ($tpl['default_min_quantity'] ?? 0) ?></td>
                                    <td class="px-5 py-4 text-sm font-black v3-text-primary" dir="ltr"><?= formatCurrency((float) ($tpl['default_unit_price'] ?? 0)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center">
                                    <div class="mx-auto flex max-w-md flex-col items-center">
                                        <span class="flex h-14 w-14 items-center justify-center v3-radius v3-bg-soft v3-text-muted">
                                            <i class="fas fa-box-open text-xl"></i>
                                        </span>
                                        <p class="mt-4 text-sm font-black v3-text-ink"><?= esc($tr('noTemplatesMatched')) ?></p>
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
