<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50 to-teal-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-teal-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-teal-400/10 to-emerald-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-purple-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-envelope-open-text text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Email Templates</h1>
                            <p class="text-gray-600">Global messaging library and defaults</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Global Mode</span>
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">System Defaults</span>
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
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-stretch">
                <!-- Total -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-slate-500/20 to-gray-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-slate-500/10 group-hover:shadow-slate-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-slate-500 to-gray-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-slate-500 to-gray-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-folder text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-slate-100 to-gray-100 px-3 py-1.5 rounded-full border border-slate-200">
                                <div class="w-2 h-2 bg-slate-500 rounded-full animate-pulse"></div>
                                <span class="text-slate-700 font-bold text-xs">All Time</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Templates</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-slate-900"><?= number_format($templateStats['total'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-gradient-to-r from-slate-200 to-gray-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-slate-500 to-gray-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">System Library</p>
                        </div>
                    </div>
                </div>

                <!-- Published -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-green-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-check-circle text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-emerald-100 to-green-100 px-3 py-1.5 rounded-full border border-emerald-200">
                                <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                <span class="text-emerald-700 font-bold text-xs">Published</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Active</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-emerald-600" style="color: #059669 !important;"><?= number_format($templateStats['active'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-gradient-to-r from-emerald-200 to-green-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-emerald-500 to-green-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Live Templates</p>
                        </div>
                    </div>
                </div>

                <!-- Drafts -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/20 to-orange-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-amber-500/10 group-hover:shadow-amber-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-pencil-ruler text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-amber-100 to-orange-100 px-3 py-1.5 rounded-full border border-amber-200">
                                <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
                                <span class="text-amber-700 font-bold text-xs">In Progress</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Drafts</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-amber-600" style="color: #d97706 !important;"><?= number_format($templateStats['draft'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-gradient-to-r from-amber-200 to-orange-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-amber-500 to-orange-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Editing</p>
                        </div>
                    </div>
                </div>

                <!-- In View -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-indigo-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-eye text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-blue-100 to-indigo-100 px-3 py-1.5 rounded-full border border-blue-200">
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                <span class="text-blue-700 font-bold text-xs">Filtered</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">In View</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-blue-600" style="color: #2563eb !important;"><?= number_format($templateStats['filtered'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-gradient-to-r from-blue-200 to-indigo-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Filtered Results</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unified Table Section -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden mb-12">
            <!-- Header & Filters -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-violet-500 rounded-full"></div>
                        <h3 class="text-lg font-semibold text-gray-900">Template Library</h3>
                    </div>
                    
                    <form method="get" class="flex flex-wrap items-center gap-3">
                        <!-- Search -->
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="q" value="<?= esc($templateFilters['q'] ?? '') ?>" placeholder="Search templates..."
                                   class="pl-10 pr-4 py-2 rounded-lg border border-gray-300 text-sm focus:ring-violet-500 focus:border-violet-500 w-64">
                        </div>

                        <!-- Status Filter -->
                        <select name="status" class="py-2 pl-3 pr-8 rounded-lg border border-gray-300 text-sm focus:ring-violet-500 focus:border-violet-500 bg-white" onchange="this.form.submit()">
                            <?php $currentTemplateStatus = $templateFilters['status'] ?? ''; ?>
                            <option value="" <?= $currentTemplateStatus === '' ? 'selected' : '' ?>>All Status</option>
                            <option value="active" <?= $currentTemplateStatus === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="draft" <?= $currentTemplateStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
                        </select>

                        <!-- Actions -->
                        <div class="flex items-center space-x-2 border-l border-gray-300 pl-3">
                            <button type="submit" class="p-2 text-violet-600 hover:bg-violet-50 rounded-lg transition-colors" title="Filter">
                                <i class="fas fa-filter"></i>
                            </button>
                            <a href="<?= base_url('controlplane/email-templates') ?>" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors" title="Reset">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>

                        <!-- Create Button -->
                        <button type="button" onclick="openTemplateModal()" class="ml-2 inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-slate-800 transition-colors shadow-sm">
                            <i class="fas fa-plus"></i>
                            Create Template
                        </button>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-file-alt text-violet-600"></i>
                                    <span>Template Name</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-code text-violet-600"></i>
                                    <span>Slug</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-info-circle text-violet-600"></i>
                                    <span>Status</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-clock text-violet-600"></i>
                                    <span>Last Updated</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-end space-x-2">
                                    <i class="fas fa-cogs text-violet-600"></i>
                                    <span>Actions</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($templates)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                            <i class="fas fa-folder-open text-gray-400 text-xl"></i>
                                        </div>
                                        <p class="font-medium">No templates found</p>
                                        <p class="text-xs mt-1">Create a new template to get started.</p>
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
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                <i class="fas fa-check-circle mr-1.5"></i> Active
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                <i class="fas fa-pencil-ruler mr-1.5"></i> Draft
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= esc($template['updated_at'] ?? $template['created_at'] ?? '-') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="<?= base_url('controlplane/email-templates?template_id=' . $template['id']) ?>" 
                                           class="p-2 text-violet-600 hover:text-violet-700 rounded-md hover:bg-violet-50 transition-colors duration-200 inline-flex"
                                           title="Edit Template">
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
<div id="templateModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 px-4 backdrop-blur-sm">
    <div class="w-full max-w-4xl rounded-2xl bg-white shadow-2xl border border-white/30 overflow-hidden flex flex-col max-h-[90vh]">
        <div class="bg-gradient-to-r from-violet-600 to-purple-700 px-6 py-5 flex items-center justify-between shrink-0">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center">
                    <i class="fas fa-pen-nib text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white" id="modalTitle">
                        <?= $activeTemplate ? 'Edit Template' : 'Create Template' ?>
                    </h3>
                    <p class="text-xs text-violet-100">Design consistent email messaging for every clinic</p>
                </div>
            </div>
            <button type="button" class="text-white/80 hover:text-white" onclick="closeTemplateModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="overflow-y-auto p-6">
            <form action="<?= $templateAction ?>" method="post" class="space-y-6" id="templateForm">
                <?= csrf_field() ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Template Name</label>
                        <input type="text" name="name" value="<?= esc($activeTemplate['name'] ?? '') ?>"
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-slate-700 font-medium focus:border-violet-500 focus:ring-4 focus:ring-violet-200/60 transition-all" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Slug (Unique)</label>
                        <input type="text" name="slug" value="<?= esc($activeTemplate['slug'] ?? '') ?>"
                               class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-slate-700 font-medium focus:border-violet-500 focus:ring-4 focus:ring-violet-200/60 transition-all"
                               placeholder="auto-generated-if-empty">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Email Subject</label>
                    <input type="text" name="subject" value="<?= esc($activeTemplate['subject'] ?? '') ?>"
                           class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-slate-700 font-medium focus:border-violet-500 focus:ring-4 focus:ring-violet-200/60 transition-all" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Status</label>
                    <?php $templateStatus = $activeTemplate['status'] ?? 'draft'; ?>
                    <select name="status" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-slate-700 font-medium focus:border-violet-500 focus:ring-4 focus:ring-violet-200/60 transition-all">
                        <option value="draft" <?= $templateStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="active" <?= $templateStatus === 'active' ? 'selected' : '' ?>>Active</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">HTML Body</label>
                    <div class="rounded-xl border-2 border-slate-200 overflow-hidden">
                        <textarea name="body_html" rows="12"
                                  class="w-full border-0 p-4 text-sm font-mono text-slate-700 focus:ring-0"
                                  placeholder="<html>...</html>"><?= esc($activeTemplate['body_html'] ?? '') ?></textarea>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Supports standard HTML tags. Use {{var}} for dynamic content.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Plain Text Body</label>
                    <textarea name="body_text" rows="6"
                              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-sm font-mono text-slate-700 focus:border-violet-500 focus:ring-4 focus:ring-violet-200/60 transition-all"
                              placeholder="Plain text fallback..."><?= esc($activeTemplate['body_text'] ?? '') ?></textarea>
                </div>
            </form>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-end gap-3 shrink-0">
            <button type="button" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors" onclick="closeTemplateModal()">
                Cancel
            </button>
            <button type="submit" form="templateForm" class="px-5 py-2 rounded-xl bg-gradient-to-r from-violet-600 to-purple-700 text-white text-sm font-semibold shadow-lg hover:from-violet-700 hover:to-purple-800 transition-all">
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
