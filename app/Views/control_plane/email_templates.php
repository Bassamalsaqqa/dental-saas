<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="v3-platform-workspace min-h-screen relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">



    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Page Header -->
        <div class="mb-4">
            <div class="cp-surface border cp-border cp-radius p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 cp-radius flex items-center justify-center text-white">
                            <i class="fas fa-envelope-open-text text-xl"></i>
                        </div>
                        <div>
                            <h1 class="cp-title font-bold text-gray-900"><?= esc(lang('V3Platform.emailTemplates')) ?></h1>
                            <p class="text-gray-600"><?= esc(lang('V3Platform.globalMessagingLibraryAndDefaults')) ?></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <span class="px-3 py-1 rounded-full cp-soft-surface text-emerald-700 text-xs font-semibold"><?= esc(lang('V3Platform.globalMode')) ?></span>
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold"><?= esc(lang('V3Platform.systemDefaults')) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <?php
            $activeTemplate = $activeTemplate ?? null;
            $templates = $templates ?? [];
            $templateAction = base_url('controlplane/settings/email-templates' . ($activeTemplate ? '/' . $activeTemplate['id'] : ''));
        ?>

        <!-- Stats Grid -->
        <div class="mb-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-stretch">
                <!-- Total -->
                <div class="group relative">

                    <div class="relative cp-surface border cp-border cp-radius p-6 transition-all duration-200 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">

                                <div class="relative p-3 cp-radius text-white transition-all duration-200">
                                    <i class="fas fa-folder text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 px-3 py-1.5 rounded-full border cp-border">
                                <div class="w-2 h-2 bg-slate-500 rounded-full"></div>
                                <span class="text-slate-700 font-bold text-xs"><?= esc(lang('V3Platform.allTime')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= esc(lang('V3Platform.totalTemplates')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="cp-title font-black text-slate-900"><?= number_format($templateStats['total'] ?? 0) ?></p>
                                <div class="w-12 h-1 rounded-full overflow-hidden">

                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= esc(lang('V3Platform.systemLibrary')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Published -->
                <div class="group relative">

                    <div class="relative cp-surface border cp-border cp-radius p-6 transition-all duration-200 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">

                                <div class="relative p-3 cp-radius text-white transition-all duration-200">
                                    <i class="fas fa-check-circle text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 px-3 py-1.5 rounded-full border border-emerald-200">
                                <div class="w-2 h-2 cp-primary-surface rounded-full"></div>
                                <span class="text-emerald-700 font-bold text-xs"><?= esc(lang('V3Platform.published')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= esc(lang('V3Platform.active')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="cp-title font-black text-emerald-600" style="color: #059669 !important;"><?= number_format($templateStats['active'] ?? 0) ?></p>
                                <div class="w-12 h-1 rounded-full overflow-hidden">

                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= esc(lang('V3Platform.liveTemplates')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Drafts -->
                <div class="group relative">

                    <div class="relative cp-surface border cp-border cp-radius p-6 transition-all duration-200 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">

                                <div class="relative p-3 cp-radius text-white transition-all duration-200">
                                    <i class="fas fa-pencil-ruler text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 px-3 py-1.5 rounded-full border border-amber-200">
                                <div class="w-2 h-2 bg-amber-500 rounded-full"></div>
                                <span class="text-amber-700 font-bold text-xs"><?= esc(lang('V3Platform.inProgress')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= esc(lang('V3Platform.drafts')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="cp-title font-black text-amber-600" style="color: #d97706 !important;"><?= number_format($templateStats['draft'] ?? 0) ?></p>
                                <div class="w-12 h-1 rounded-full overflow-hidden">

                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= esc(lang('V3Platform.editing')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- In View -->
                <div class="group relative">

                    <div class="relative cp-surface border cp-border cp-radius p-6 transition-all duration-200 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">

                                <div class="relative p-3 cp-radius text-white transition-all duration-200">
                                    <i class="fas fa-eye text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 px-3 py-1.5 rounded-full border cp-border">
                                <div class="w-2 h-2 cp-primary-surface rounded-full"></div>
                                <span class="cp-accent font-bold text-xs"><?= esc(lang('V3Platform.filtered2')) ?></span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider"><?= esc(lang('V3Platform.inView')) ?></p>
                            <div class="flex items-baseline space-x-2">
                                <p class="cp-title font-black cp-accent" style="color: #2563eb !important;"><?= number_format($templateStats['filtered'] ?? 0) ?></p>
                                <div class="w-12 h-1 rounded-full overflow-hidden">

                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium"><?= esc(lang('V3Platform.filteredResults')) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unified Table Section -->
        <div class="cp-surface cp-radius border cp-border overflow-hidden mb-12">
            <!-- Header & Filters -->
            <div class="px-6 py-4 border-b cp-border cp-surface">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 cp-primary-surface rounded-full"></div>
                        <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('V3Platform.templateLibrary')) ?></h3>
                    </div>

                    <form method="get" class="flex flex-wrap items-center gap-3">
                        <!-- Search -->
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="q" value="<?= esc($templateFilters['q'] ?? '') ?>" placeholder="<?= esc(lang('V3Platform.searchTemplates'), 'attr') ?>"
                                   class="pl-10 pr-4 py-2 rounded-lg border cp-border text-sm focus:ring-violet-500 focus:border-violet-500 w-64">
                        </div>

                        <!-- Status Filter -->
                        <select name="status" class="py-2 pl-3 pr-8 rounded-lg border cp-border text-sm focus:ring-violet-500 focus:border-violet-500 cp-surface" onchange="this.form.submit()">
                            <?php $currentTemplateStatus = $templateFilters['status'] ?? ''; ?>
                            <option value="" <?= $currentTemplateStatus === '' ? 'selected' : '' ?>><?= esc(lang('V3Platform.allStatus')) ?></option>
                            <option value="active" <?= $currentTemplateStatus === 'active' ? 'selected' : '' ?>><?= esc(lang('V3Platform.active')) ?></option>
                            <option value="draft" <?= $currentTemplateStatus === 'draft' ? 'selected' : '' ?>><?= esc(lang('V3Platform.draft')) ?></option>
                        </select>

                        <!-- Actions -->
                        <div class="flex items-center space-x-2 border-l cp-border pl-3">
                            <button type="submit" class="p-2 cp-accent hover:bg-violet-50 rounded-lg transition-colors" title="<?= esc(lang('V3Platform.filter'), 'attr') ?>">
                                <i class="fas fa-filter"></i>
                            </button>
                            <a href="<?= base_url('controlplane/email-templates') ?>" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors" title="<?= esc(lang('V3Platform.reset'), 'attr') ?>">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>

                        <!-- Create Button -->
                        <button type="button" onclick="openTemplateModal()" class="ml-2 inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-slate-800 transition-colors">
                            <i class="fas fa-plus"></i>
                            <?= esc(lang('V3Platform.createTemplate')) ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="cp-surface">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-file-alt cp-accent"></i>
                                    <span><?= esc(lang('V3Platform.templateName')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-code cp-accent"></i>
                                    <span><?= esc(lang('V3Platform.slug')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-info-circle cp-accent"></i>
                                    <span><?= esc(lang('V3Platform.status')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-clock cp-accent"></i>
                                    <span><?= esc(lang('V3Platform.lastUpdated')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-end space-x-2">
                                    <i class="fas fa-cogs cp-accent"></i>
                                    <span><?= esc(lang('V3Platform.actions')) ?></span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="cp-surface divide-y divide-gray-200">
                        <?php if (empty($templates)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                            <i class="fas fa-folder-open text-gray-400 text-xl"></i>
                                        </div>
                                        <p class="font-medium"><?= esc(lang('V3Platform.noTemplatesFound')) ?></p>
                                        <p class="text-xs mt-1"><?= esc(lang('V3Platform.createANewTemplateToGetStarted')) ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($templates as $template): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-slate-900"><?= esc($template['name']) ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <code class="text-xs font-mono bg-slate-100 px-2 py-1 rounded text-slate-600"><?= esc($template['slug']) ?></code>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if (($template['status'] ?? 'draft') === 'active'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cp-soft-surface text-emerald-800">
                                                <i class="fas fa-check-circle mr-1.5"></i> <?= esc(lang('V3Platform.active')) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                <i class="fas fa-pencil-ruler mr-1.5"></i> <?= esc(lang('V3Platform.draft')) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= esc($template['updated_at'] ?? $template['created_at'] ?? '-') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="<?= base_url('controlplane/email-templates?template_id=' . $template['id']) ?>"
                                           class="p-2 cp-accent hover:text-violet-700 rounded-md hover:bg-violet-50 transition-colors duration-200 inline-flex"
                                           title="<?= esc(lang('V3Platform.editTemplate'), 'attr') ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Template Editor Modal -->
<div id="templateModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 px-4">
    <div class="w-full max-w-4xl cp-radius cp-surface border cp-border overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-6 py-5 flex items-center justify-between shrink-0">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 cp-radius cp-surface flex items-center justify-center">
                    <i class="fas fa-pen-nib text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white" id="modalTitle">
                        <?= $activeTemplate ? 'Edit Template' : 'Create Template' ?>
                    </h3>
                    <p class="text-xs cp-accent"><?= esc(lang('V3Platform.designConsistentEmailMessagingForEveryClinic')) ?></p>
                </div>
            </div>
            <button type="button" class="text-white/80 hover:text-white" onclick="closeTemplateModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="overflow-y-auto p-6">
            <form action="<?= $templateAction ?>" method="post" class="space-y-6" id="templateForm">
                <?= csrf_field() ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.templateName')) ?></label>
                        <input type="text" name="name" value="<?= esc($activeTemplate['name'] ?? '') ?>"
                               class="w-full px-4 py-3 border-2 cp-border cp-radius text-slate-700 font-medium focus:border-violet-500 focus:ring-4 focus:ring-violet-200/60 transition-all" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.slugUnique')) ?></label>
                        <input type="text" name="slug" value="<?= esc($activeTemplate['slug'] ?? '') ?>"
                               class="w-full px-4 py-3 border-2 cp-border cp-radius text-slate-700 font-medium focus:border-violet-500 focus:ring-4 focus:ring-violet-200/60 transition-all"
                               placeholder="auto-generated-if-empty">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.emailSubject')) ?></label>
                    <input type="text" name="subject" value="<?= esc($activeTemplate['subject'] ?? '') ?>"
                           class="w-full px-4 py-3 border-2 cp-border cp-radius text-slate-700 font-medium focus:border-violet-500 focus:ring-4 focus:ring-violet-200/60 transition-all" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.status')) ?></label>
                    <?php $templateStatus = $activeTemplate['status'] ?? 'draft'; ?>
                    <select name="status" class="w-full px-4 py-3 border-2 cp-border cp-radius text-slate-700 font-medium focus:border-violet-500 focus:ring-4 focus:ring-violet-200/60 transition-all">
                        <option value="draft" <?= $templateStatus === 'draft' ? 'selected' : '' ?>><?= esc(lang('V3Platform.draft')) ?></option>
                        <option value="active" <?= $templateStatus === 'active' ? 'selected' : '' ?>><?= esc(lang('V3Platform.active')) ?></option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.htmlBody')) ?></label>
                    <div class="cp-radius border-2 cp-border overflow-hidden">
                        <textarea name="body_html" rows="12"
                                  class="w-full border-0 p-4 text-sm font-mono text-slate-700 focus:ring-0"
                                  placeholder="<html>...</html>"><?= esc($activeTemplate['body_html'] ?? '') ?></textarea>
                    </div>
                    <p class="mt-2 text-xs text-slate-500"><?= esc(lang('V3Platform.supportsStandardHtmlTagsUseVarForDynamicContent')) ?></p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.plainTextBody')) ?></label>
                    <textarea name="body_text" rows="6"
                              class="w-full px-4 py-3 border-2 cp-border cp-radius text-sm font-mono text-slate-700 focus:border-violet-500 focus:ring-4 focus:ring-violet-200/60 transition-all"
                              placeholder="<?= esc(lang('V3Platform.plainTextFallback'), 'attr') ?>"><?= esc($activeTemplate['body_text'] ?? '') ?></textarea>
                </div>
            </form>
        </div>

        <div class="px-6 py-4 border-t cp-border cp-surface flex justify-end gap-3 shrink-0">
            <button type="button" class="px-4 py-2 cp-radius cp-surface border cp-border text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors" onclick="closeTemplateModal()">
                <?= esc(lang('V3Platform.cancel')) ?>
            </button>
            <button type="submit" form="templateForm" class="px-5 py-2 cp-radius text-white text-sm font-semibold transition-all">
                <?= $activeTemplate ? 'Update Template' : 'Create Template' ?>
            </button>
        </div>
    </div>
</div>

<script>
function openTemplateModal() {
    const modal = document.getElementById('templateModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeTemplateModal() {
    const modal = document.getElementById('templateModal');
    if (modal) {
        // If we are in "Edit Mode" (URL has param), clearing the modal essentially means going back to list
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('template_id')) {
            window.location.href = '<?= base_url('controlplane/email-templates') ?>';
            return;
        }
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

// Auto-open modal if active template exists
<?php if ($activeTemplate): ?>
    document.addEventListener('DOMContentLoaded', function() {
        openTemplateModal();
    });
<?php endif; ?>
</script>
    </div>
</div>
<?= $this->endSection() ?>
