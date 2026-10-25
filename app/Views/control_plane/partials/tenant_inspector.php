    <aside class="v3-tenant-inspector" aria-labelledby="v3-inspector-name">
        <header><h2 id="v3-inspector-name"><?= esc(lang('V3Fidelity.selectClinic')) ?></h2><p id="v3-inspector-slug">&mdash;</p></header>
        <nav class="v3-inspector-tabs" aria-label="<?= esc(lang('V3Fidelity.clinicInspector'), 'attr') ?>"><button type="button" data-inspector-tab="overview" aria-pressed="true"><?= esc(lang('V3Fidelity.overview')) ?></button><button type="button" data-inspector-tab="subscription" aria-pressed="false"><?= esc(lang('V3Fidelity.subscription')) ?></button><button type="button" data-inspector-tab="usage" aria-pressed="false"><?= esc(lang('V3Fidelity.usage')) ?></button></nav>
        <dl data-inspector-pane="overview"><div><dt><?= esc(lang('V3Platform.status')) ?></dt><dd id="v3-inspector-status">&mdash;</dd></div><div><dt><?= esc(lang('V3Platform.adminEmail')) ?></dt><dd id="v3-inspector-admin" dir="ltr">&mdash;</dd></div></dl>
        <dl data-inspector-pane="subscription" hidden><div><dt><?= esc(lang('V3Platform.plan')) ?></dt><dd id="v3-inspector-plan">&mdash;</dd></div></dl>
        <div data-inspector-pane="usage" hidden><p><?= esc(lang('V3Fidelity.inspectUsageHint')) ?></p></div>
        <p class="v3-inspector-note"><?= esc(lang('V3Fidelity.noImpersonation')) ?></p>
        <a id="v3-inspector-detail" class="v3-btn v3-btn--primary" hidden><?= esc(lang('V3Fidelity.openClinic')) ?></a>
    </aside>
