<?php if (!empty($studioImpersonating)): ?>
<section class="studio-security-banner" aria-label="<?= esc(lang('Studio.platformViewingClinic'), 'attr') ?>" data-studio-impersonation>
    <div class="studio-security-identity">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 3 4 6v5c0 5 8 10 8 10s8-5 8-10V6z"/><path d="M12 8v5m0 3v.1"/></svg>
        <div><strong><?= esc(lang('Studio.platformViewingClinic')) ?></strong><span><?= esc(($clinic['name'] ?? '') ?: lang('Studio.platformClinicReference', [(int) session('active_clinic_id')])) ?></span></div>
    </div>
    <form action="<?= base_url('controlplane/impersonation/exit') ?>" method="post">
        <?= csrf_field() ?>
        <button type="submit" class="studio-security-return"><?= esc(lang('Studio.returnControlPlane')) ?></button>
    </form>
</section>
<?php endif; ?>
