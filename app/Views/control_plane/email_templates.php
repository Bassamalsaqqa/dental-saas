<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto space-y-8">
    <div class="glass-card rounded-3xl p-6 md:p-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-blue-700 font-semibold">Email Templates</p>
                <h2 class="text-2xl font-semibold text-slate-900 mt-2">Global messaging library</h2>
                <p class="mt-2 text-sm text-slate-600">Manage templates that clinics inherit by default.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Global Mode</span>
                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">Read-only</span>
            </div>
        </div>

        <?php
            $activeTemplate = $activeTemplate ?? null;
            $templates = $templates ?? [];
            $templateAction = $activeTemplate
                ? base_url('controlplane/settings/email-templates/' . $activeTemplate['id'])
                : base_url('controlplane/settings/email-templates');
        ?>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="rounded-2xl border border-white/60 bg-white/70 p-4">
                <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Total</p>
                <p class="text-2xl font-semibold text-slate-900 mt-1"><?= number_format($templateStats['total'] ?? 0) ?></p>
            </div>
            <div class="rounded-2xl border border-white/60 bg-white/70 p-4">
                <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Active</p>
                <p class="text-2xl font-semibold text-emerald-600 mt-1"><?= number_format($templateStats['active'] ?? 0) ?></p>
            </div>
            <div class="rounded-2xl border border-white/60 bg-white/70 p-4">
                <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Drafts</p>
                <p class="text-2xl font-semibold text-amber-600 mt-1"><?= number_format($templateStats['draft'] ?? 0) ?></p>
            </div>
            <div class="rounded-2xl border border-white/60 bg-white/70 p-4">
                <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">In View</p>
                <p class="text-2xl font-semibold text-slate-900 mt-1"><?= number_format($templateStats['filtered'] ?? 0) ?></p>
            </div>
        </div>

        <form method="get" class="grid grid-cols-1 lg:grid-cols-[2fr,1fr,auto] gap-4 items-end mb-6">
            <div>
                <label class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Search</label>
                <input type="text" name="q" value="<?= esc($templateFilters['q'] ?? '') ?>" placeholder="Template name or slug"
                       class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Status</label>
                <?php $currentTemplateStatus = $templateFilters['status'] ?? ''; ?>
                <select name="status" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    <option value="" <?= $currentTemplateStatus === '' ? 'selected' : '' ?>>All statuses</option>
                    <option value="active" <?= $currentTemplateStatus === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="draft" <?= $currentTemplateStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm font-semibold">Filter</button>
                <a href="<?= base_url('controlplane/email-templates') ?>" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold">Reset</a>
            </div>
        </form>

        <div id="template-editor" class="glass-muted rounded-2xl p-6 mb-6">
            <form action="<?= $templateAction ?>" method="post" class="space-y-4">
                <?= csrf_field() ?>
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-800">
                        <?= $activeTemplate ? 'Edit Template' : 'Create Template' ?>
                    </h3>
                    <?php if ($activeTemplate): ?>
                        <a href="<?= base_url('controlplane/email-templates') ?>" class="text-xs text-slate-500">Clear</a>
                    <?php endif; ?>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs uppercase tracking-widest text-slate-400 mb-2">Template name</label>
                        <input type="text" name="name" value="<?= esc($activeTemplate['name'] ?? '') ?>" class="w-full rounded-xl border-slate-200 text-sm shadow-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-widest text-slate-400 mb-2">Slug</label>
                        <input type="text" name="slug" value="<?= esc($activeTemplate['slug'] ?? '') ?>" class="w-full rounded-xl border-slate-200 text-sm shadow-sm" placeholder="auto-generated if empty">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs uppercase tracking-widest text-slate-400 mb-2">Subject</label>
                        <input type="text" name="subject" value="<?= esc($activeTemplate['subject'] ?? '') ?>" class="w-full rounded-xl border-slate-200 text-sm shadow-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-widest text-slate-400 mb-2">Status</label>
                        <?php $templateStatus = $activeTemplate['status'] ?? 'draft'; ?>
                        <select name="status" class="w-full rounded-xl border-slate-200 text-sm shadow-sm">
                            <option value="draft" <?= $templateStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="active" <?= $templateStatus === 'active' ? 'selected' : '' ?>>Active</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-widest text-slate-400 mb-2">HTML body</label>
                    <textarea name="body_html" rows="5" class="w-full rounded-xl border-slate-200 text-sm shadow-sm" placeholder="HTML content"><?= esc($activeTemplate['body_html'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-widest text-slate-400 mb-2">Text body</label>
                    <textarea name="body_text" rows="4" class="w-full rounded-xl border-slate-200 text-sm shadow-sm" placeholder="Plain text content"><?= esc($activeTemplate['body_text'] ?? '') ?></textarea>
                </div>
                <div class="flex justify-end">
                    <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold">
                        <?= $activeTemplate ? 'Update Template' : 'Create Template' ?>
                    </button>
                </div>
            </form>
        </div>

        <div class="glass-muted rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-white/40">
                <span class="text-xs text-slate-500">Templates</span>
                <span class="text-[10px] uppercase tracking-widest text-slate-500 bg-white/70 border border-slate-200 px-2 py-1 rounded-full">
                    <?= count($templates) ?> total
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-white/70 text-xs uppercase tracking-widest text-slate-500">
                        <tr>
                            <th class="px-6 py-3 text-left">Template</th>
                            <th class="px-6 py-3 text-left">Slug</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Updated</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/50">
                        <?php if (empty($templates)): ?>
                            <tr>
                                <td class="px-6 py-6 text-center text-slate-400" colspan="5">No templates yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($templates as $template): ?>
                                <tr>
                                    <td class="px-6 py-4 font-semibold text-slate-700"><?= esc($template['name']) ?></td>
                                    <td class="px-6 py-4 text-slate-500 font-mono"><?= esc($template['slug']) ?></td>
                                    <td class="px-6 py-4">
                                        <?php if (($template['status'] ?? 'draft') === 'active'): ?>
                                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">Active</span>
                                        <?php else: ?>
                                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-amber-100 text-amber-700">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500"><?= esc($template['updated_at'] ?? $template['created_at'] ?? '-') ?></td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="<?= base_url('controlplane/email-templates?template_id=' . $template['id'] . '#template-editor') ?>" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold">Edit</a>
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
<?= $this->endSection() ?>