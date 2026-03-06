                            <div id="comms-panel-email-templates" class="comms-panel space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50"><p class="text-xs text-slate-500">Total</p><p class="text-2xl font-black text-slate-900"><?= number_format($templateStats['total'] ?? 0) ?></p></div>
                                    <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50"><p class="text-xs text-emerald-600">Active</p><p class="text-2xl font-black text-emerald-700"><?= number_format($templateStats['active'] ?? 0) ?></p></div>
                                    <div class="p-4 rounded-xl border border-amber-200 bg-amber-50"><p class="text-xs text-amber-600">Draft</p><p class="text-2xl font-black text-amber-700"><?= number_format($templateStats['draft'] ?? 0) ?></p></div>
                                    <div class="p-4 rounded-xl border border-blue-200 bg-blue-50"><p class="text-xs text-blue-600">Filtered</p><p class="text-2xl font-black text-blue-700"><?= number_format($templateStats['filtered'] ?? 0) ?></p></div>
                                </div>

                                <form method="get" action="<?= base_url('controlplane/settings') ?>" class="flex flex-wrap gap-3 items-end">
                                    <input type="hidden" name="tab" value="communications">
                                    <input type="hidden" name="subtab" value="email-templates">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Search</label>
                                        <input type="text" name="q" value="<?= esc($templateFilters['q'] ?? '') ?>" class="px-3 py-2 border border-slate-300 rounded-lg text-sm w-64" placeholder="Search templates...">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Status</label>
                                        <select name="status" class="px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white">
                                            <?php $currentTemplateStatus = $templateFilters['status'] ?? ''; ?>
                                            <option value="" <?= $currentTemplateStatus === '' ? 'selected' : '' ?>>All</option>
                                            <option value="active" <?= $currentTemplateStatus === 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="draft" <?= $currentTemplateStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="px-4 py-2 bg-slate-700 text-white rounded-lg text-sm font-semibold">Apply</button>
                                </form>

                                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                                    <div class="border border-slate-200 rounded-xl overflow-hidden">
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
                                    <div class="border border-slate-200 rounded-xl p-4" id="template-editor">
                                        <h4 class="text-sm font-bold text-slate-800 mb-4"><?= $editorTemplate ? 'Edit Template' : 'Create Template' ?></h4>
                                        <form action="<?= base_url('controlplane/settings/email-templates' . ($editorTemplate ? '/' . (int) $editorTemplate['id'] : '')) ?>" method="post" class="space-y-3" data-settings-form="true" data-saving-label="Saving template...">
                                            <?= csrf_field() ?>
                                            <div><label class="block text-xs font-bold text-slate-500 mb-1">Name</label><input type="text" name="name" value="<?= esc($editorTemplate['name'] ?? '') ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" required></div>
                                            <div><label class="block text-xs font-bold text-slate-500 mb-1">Slug</label><input type="text" name="slug" value="<?= esc($editorTemplate['slug'] ?? '') ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm"></div>
                                            <div><label class="block text-xs font-bold text-slate-500 mb-1">Subject</label><input type="text" name="subject" value="<?= esc($editorTemplate['subject'] ?? '') ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" required></div>
                                            <div><label class="block text-xs font-bold text-slate-500 mb-1">HTML Body</label><textarea name="body_html" rows="6" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm"><?= esc($editorTemplate['body_html'] ?? '') ?></textarea></div>
                                            <div><label class="block text-xs font-bold text-slate-500 mb-1">Text Body</label><textarea name="body_text" rows="4" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm"><?= esc($editorTemplate['body_text'] ?? '') ?></textarea></div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-500 mb-1">Status</label>
                                                <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                                                    <option value="draft" <?= (($editorTemplate['status'] ?? 'draft') === 'draft') ? 'selected' : '' ?>>Draft</option>
                                                    <option value="active" <?= (($editorTemplate['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
                                                </select>
                                            </div>
                                            <button type="submit" class="px-4 py-2 bg-slate-700 text-white text-sm font-semibold rounded-lg">Save Template</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
