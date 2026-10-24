                            <div id="tab-email-templates" data-section-title="<?= esc(lang('V3Platform.emailTemplates'), 'attr') ?>" class="settings-content hidden space-y-6">
                                <div id="section-email-template-metrics" data-section-title="<?= esc(lang('V3Platform.templateMetrics'), 'attr') ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="control-settings-metric p-4"><p class="text-xs font-bold uppercase tracking-[0.14em] v3-text-muted"><?= esc(lang('V3Platform.total')) ?></p><p class="mt-2 text-2xl font-black v3-text-ink"><?= number_format($templateStats['total'] ?? 0) ?></p></div>
                                    <div class="control-settings-metric p-4 cp-soft-surface"><p class="text-xs font-bold uppercase tracking-[0.14em] v3-text-primary"><?= esc(lang('V3Platform.active')) ?></p><p class="mt-2 text-2xl font-black v3-text-primary"><?= number_format($templateStats['active'] ?? 0) ?></p></div>
                                    <div class="control-settings-metric p-4 v3-bg-waiting"><p class="text-xs font-bold uppercase tracking-[0.14em] v3-text-warning"><?= esc(lang('V3Platform.draft')) ?></p><p class="mt-2 text-2xl font-black v3-text-warning"><?= number_format($templateStats['draft'] ?? 0) ?></p></div>
                                    <div class="control-settings-metric p-4 cp-soft-surface"><p class="text-xs font-bold uppercase tracking-[0.14em] cp-accent"><?= esc(lang('V3Platform.filtered2')) ?></p><p class="mt-2 text-2xl font-black cp-accent"><?= number_format($templateStats['filtered'] ?? 0) ?></p></div>
                                </div>

                                <form id="section-email-template-filters" data-section-title="<?= esc(lang('V3Platform.templateFilters'), 'attr') ?>" method="get" action="<?= base_url('controlplane/settings') ?>" class="control-settings-surface flex flex-wrap gap-3 items-end p-5">
                                    <input type="hidden" name="tab" value="email-templates">
                                    <div>
                                        <label class="block text-xs font-bold v3-text-muted uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.search')) ?></label>
                                        <input type="text" name="q" value="<?= esc($templateFilters['q'] ?? '') ?>" class="control-settings-field w-64 v3-input" placeholder="<?= esc(lang('V3Platform.searchTemplates'), 'attr') ?>">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold v3-text-muted uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.status')) ?></label>
                                        <select name="status" class="control-settings-field v3-select">
                                            <?php $currentTemplateStatus = $templateFilters['status'] ?? ''; ?>
                                            <option value="" <?= $currentTemplateStatus === '' ? 'selected' : '' ?>><?= esc(lang('V3Platform.all')) ?></option>
                                            <option value="active" <?= $currentTemplateStatus === 'active' ? 'selected' : '' ?>><?= esc(lang('V3Platform.active')) ?></option>
                                            <option value="draft" <?= $currentTemplateStatus === 'draft' ? 'selected' : '' ?>><?= esc(lang('V3Platform.draft')) ?></option>
                                        </select>
                                    </div>
                                    <button type="submit" class="control-settings-submit v3-btn v3-btn--primary"><?= esc(lang('V3Platform.apply')) ?></button>
                                </form>

                                <div class="grid grid-cols-1 xl:grid-cols-2 gap-3">
                                    <div id="section-email-template-library" data-section-title="<?= esc(lang('V3Platform.templateLibrary'), 'attr') ?>" class="control-settings-card">
                                        <div class="control-settings-header">
                                            <div class="flex items-center space-x-4">
                                                <div class="w-12 h-12 cp-radius flex items-center justify-center">
                                                    <i class="fas fa-layer-group text-xl"></i>
                                                </div>
                                                <div>
                                                    <h4 class="text-lg font-bold"><?= esc(lang('V3Platform.templateLibrary')) ?></h4>
                                                    <p class="text-sm"><?= esc(lang('V3Platform.browseAndSelectSharedEmailTemplates')) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="max-h-[28rem] overflow-y-auto divide-y v3-border-default">
                                            <?php if (empty($templates)): ?>
                                                <div class="p-6 text-sm v3-text-muted"><?= esc(lang('V3Platform.noTemplatesFound2')) ?></div>
                                            <?php else: ?>
                                                <?php foreach ($templates as $template): ?>
                                                    <a href="<?= base_url('controlplane/settings?tab=email-templates&template_id=' . (int) $template['id']) ?>" class="block p-4 v3-hover-soft transition-colors">
                                                        <p class="text-sm font-semibold v3-text-ink"><?= esc($template['name']) ?></p>
                                                        <p class="text-xs v3-text-muted"><?= esc($template['slug']) ?></p>
                                                    </a>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php $editorTemplate = $activeTemplate ?? null; ?>
                                    <div id="section-email-template-editor" data-section-title="<?= esc(lang('V3Platform.templateEditor'), 'attr') ?>" class="control-settings-card">
                                        <div class="control-settings-header">
                                            <div class="flex items-center space-x-4">
                                                <div class="w-12 h-12 cp-radius flex items-center justify-center">
                                                    <i class="fas fa-pen-ruler text-xl"></i>
                                                </div>
                                                <div>
                                                    <h4 class="text-lg font-bold"><?= $editorTemplate ? 'Edit Template' : 'Create Template' ?></h4>
                                                    <p class="text-sm"><?= $editorTemplate ? 'Update the selected shared email template.' : 'Create a new shared email template.' ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <form action="<?= base_url('controlplane/settings/email-templates' . ($editorTemplate ? '/' . (int) $editorTemplate['id'] : '')) ?>" method="post" class="space-y-4 p-6" data-settings-form="true" data-saving-label="Saving template...">
                                            <?= csrf_field() ?>
                                            <div><label class="block text-xs font-bold v3-text-muted uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.name')) ?></label><input type="text" name="name" value="<?= esc($editorTemplate['name'] ?? '') ?>" class="control-settings-field v3-input" required></div>
                                            <div><label class="block text-xs font-bold v3-text-muted uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.slug')) ?></label><input type="text" name="slug" value="<?= esc($editorTemplate['slug'] ?? '') ?>" class="control-settings-field v3-input"></div>
                                            <div><label class="block text-xs font-bold v3-text-muted uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.subject')) ?></label><input type="text" name="subject" value="<?= esc($editorTemplate['subject'] ?? '') ?>" class="control-settings-field v3-input" required></div>
                                            <div><label class="block text-xs font-bold v3-text-muted uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.htmlBody')) ?></label><textarea name="body_html" rows="6" class="control-settings-field v3-textarea"><?= esc($editorTemplate['body_html'] ?? '') ?></textarea></div>
                                            <div><label class="block text-xs font-bold v3-text-muted uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.textBody')) ?></label><textarea name="body_text" rows="4" class="control-settings-field v3-textarea"><?= esc($editorTemplate['body_text'] ?? '') ?></textarea></div>
                                            <div>
                                                <label class="block text-xs font-bold v3-text-muted uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.status')) ?></label>
                                                <select name="status" class="control-settings-field v3-select">
                                                    <option value="draft" <?= (($editorTemplate['status'] ?? 'draft') === 'draft') ? 'selected' : '' ?>><?= esc(lang('V3Platform.draft')) ?></option>
                                                    <option value="active" <?= (($editorTemplate['status'] ?? '') === 'active') ? 'selected' : '' ?>><?= esc(lang('V3Platform.active')) ?></option>
                                                </select>
                                            </div>
                                            <button type="submit" class="control-settings-submit v3-btn v3-btn--primary"><?= esc(lang('V3Platform.saveTemplate')) ?></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
