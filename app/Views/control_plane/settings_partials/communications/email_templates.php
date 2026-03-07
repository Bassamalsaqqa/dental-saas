                            <div id="comms-panel-email-templates" data-section-title="Email Templates" class="comms-panel space-y-6">
                                <div id="section-email-template-metrics" data-section-title="Template Metrics" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50"><p class="text-xs text-slate-500">Total</p><p class="text-2xl font-black text-slate-900"><?= number_format($templateStats['total'] ?? 0) ?></p></div>
                                    <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50"><p class="text-xs text-emerald-600">Active</p><p class="text-2xl font-black text-emerald-700"><?= number_format($templateStats['active'] ?? 0) ?></p></div>
                                    <div class="p-4 rounded-xl border border-amber-200 bg-amber-50"><p class="text-xs text-amber-600">Draft</p><p class="text-2xl font-black text-amber-700"><?= number_format($templateStats['draft'] ?? 0) ?></p></div>
                                    <div class="p-4 rounded-xl border border-blue-200 bg-blue-50"><p class="text-xs text-blue-600">Filtered</p><p class="text-2xl font-black text-blue-700"><?= number_format($templateStats['filtered'] ?? 0) ?></p></div>
                                </div>

                                <form id="section-email-template-filters" data-section-title="Template Filters" method="get" action="<?= base_url('controlplane/settings') ?>" class="flex flex-wrap gap-3 items-end">
                                    <input type="hidden" name="tab" value="communications">
                                    <input type="hidden" name="subtab" value="email-templates">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Search</label>
                                        <input type="text" name="q" value="<?= esc($templateFilters['q'] ?? '') ?>" class="control-settings-field w-64" placeholder="Search templates...">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Status</label>
                                        <select name="status" class="control-settings-field">
                                            <?php $currentTemplateStatus = $templateFilters['status'] ?? ''; ?>
                                            <option value="" <?= $currentTemplateStatus === '' ? 'selected' : '' ?>>All</option>
                                            <option value="active" <?= $currentTemplateStatus === 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="draft" <?= $currentTemplateStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="control-settings-submit">Apply</button>
                                </form>

                                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                                    <div id="section-email-template-library" data-section-title="Template Library" class="border border-slate-200 rounded-xl overflow-hidden">
                                        <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 text-sm font-bold text-slate-800">Template Library</div>
                                        <div class="max-h-[28rem] overflow-y-auto divide-y divide-slate-100">
                                            <?php if (empty($templates)): ?>
                                                <div class="p-6 text-sm text-slate-500">No templates found.</div>
                                            <?php else: ?>
                                                <?php foreach ($templates as $template): ?>
                                                    <a href="<?= base_url('controlplane/settings?tab=communications&subtab=email-templates&template_id=' . (int) $template['id']) ?>" class="block p-4 hover:bg-slate-50 transition-colors">
                                                        <p class="text-sm font-semibold text-slate-900"><?= esc($template['name']) ?></p>
                                                        <p class="text-xs text-slate-500"><?= esc($template['slug']) ?></p>
                                                    </a>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php $editorTemplate = $activeTemplate ?? null; ?>
                                    <div id="section-email-template-editor" data-section-title="Template Editor" class="border border-slate-200 rounded-2xl p-4">
                                        <h4 class="text-sm font-bold text-slate-800 mb-4"><?= $editorTemplate ? 'Edit Template' : 'Create Template' ?></h4>
                                        <form action="<?= base_url('controlplane/settings/email-templates' . ($editorTemplate ? '/' . (int) $editorTemplate['id'] : '')) ?>" method="post" class="space-y-3" data-settings-form="true" data-saving-label="Saving template...">
                                            <?= csrf_field() ?>
                                            <div><label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Name</label><input type="text" name="name" value="<?= esc($editorTemplate['name'] ?? '') ?>" class="control-settings-field" required></div>
                                            <div><label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Slug</label><input type="text" name="slug" value="<?= esc($editorTemplate['slug'] ?? '') ?>" class="control-settings-field"></div>
                                            <div><label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Subject</label><input type="text" name="subject" value="<?= esc($editorTemplate['subject'] ?? '') ?>" class="control-settings-field" required></div>
                                            <div><label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">HTML Body</label><textarea name="body_html" rows="6" class="control-settings-field"><?= esc($editorTemplate['body_html'] ?? '') ?></textarea></div>
                                            <div><label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Text Body</label><textarea name="body_text" rows="4" class="control-settings-field"><?= esc($editorTemplate['body_text'] ?? '') ?></textarea></div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Status</label>
                                                <select name="status" class="control-settings-field">
                                                    <option value="draft" <?= (($editorTemplate['status'] ?? 'draft') === 'draft') ? 'selected' : '' ?>>Draft</option>
                                                    <option value="active" <?= (($editorTemplate['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
                                                </select>
                                            </div>
                                            <button type="submit" class="control-settings-submit">Save Template</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
