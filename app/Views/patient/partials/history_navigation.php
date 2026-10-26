<?php if (is_array($history_page ?? null) && is_array($history_navigation ?? null)): ?>
<nav  aria-label="<?= esc(lang('UI.patientHistory')) ?>">
    <p><?= esc(lang('PatientHistory.page')) ?></p>
    <?php if (empty($history_page['items'])): ?><p><?= esc(lang('PatientHistory.none')) ?></p><?php endif; ?>
    <p><?= esc(lang(!empty($history_navigation['next']) ? 'PatientHistory.unfinished' : 'PatientHistory.complete')) ?></p>
    <div >
        <a href="<?= esc($history_navigation['latest']) ?>"><?= esc(lang('PatientHistory.latest')) ?></a>
        <?php if (!empty($history_navigation['next'])): ?><a rel="next" href="<?= esc($history_navigation['next']) ?>"><?= esc(lang('PatientHistory.older')) ?></a><?php endif; ?>
    </div>
</nav>
<?php endif; ?>
