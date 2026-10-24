<?php
$tr = static fn(string $key, array $params = []) => lang('Examination.' . $key, $params);
$signed = !empty($clinical_note_signed);
$signature = is_array($clinical_note_signature ?? null) ? $clinical_note_signature : null;
$addenda = is_array($clinical_note_addenda ?? null) ? $clinical_note_addenda : [];
?>
<div class="exam-card" style="border-color:<?= $signed ? '#a7f3d0' : '#fde68a' ?>;background:<?= $signed ? 'linear-gradient(145deg,#ecfdf5,#ffffff)' : 'linear-gradient(145deg,#fffbeb,#ffffff)' ?>;">
    <div class="exam-card-header">
        <span class="exam-icon-circle" style="background:<?= $signed ? '#d1fae5' : '#fef3c7' ?>;color:<?= $signed ? '#047857' : '#b45309' ?>;"><i class="fas <?= $signed ? 'fa-file-circle-check' : 'fa-file-signature' ?> text-sm"></i></span>
        <span><?= esc($tr('clinical_note_governance')) ?></span>
        <span class="ms-auto rounded-full px-3 py-1 text-xs font-black <?= $signed ? 'v3-bg-paid v3-text-primary' : 'v3-bg-waiting v3-text-warning' ?>"><?= esc($signed ? $tr('note_signed_locked') : $tr('note_unsigned')) ?></span>
    </div>
    <div class="exam-card-body space-y-5">
        <?php if ($signed && $signature): ?>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="v3-radius v3-bg-panel p-4 v3-elevation-none border v3-border-default">
                    <p class="text-xs font-bold v3-text-muted"><?= esc($tr('signed_by')) ?></p>
                    <p class="mt-1 font-black v3-text-ink"><?= esc($signature['signed_by_name'] ?? '') ?></p>
                </div>
                <div class="v3-radius v3-bg-panel p-4 v3-elevation-none border v3-border-default">
                    <p class="text-xs font-bold v3-text-muted"><?= esc($tr('signed_at')) ?></p>
                    <p class="mt-1 font-black v3-text-ink" dir="ltr"><?= esc(formatDateTime($signature['signed_at'] ?? '')) ?></p>
                </div>
            </div>
            <p class="text-sm font-semibold leading-6 v3-text-primary"><?= esc($tr('signed_note_help')) ?></p>

            <?php if ($addenda): ?>
            <div class="space-y-3">
                <h3 class="text-sm font-black v3-text-ink"><?= esc($tr('note_addenda')) ?></h3>
                <?php foreach ($addenda as $addendum): ?>
                <article class="v3-radius v3-bg-panel p-4 v3-elevation-none border v3-border-default">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-black v3-text-ink"><?= esc($addendum['reason'] ?? '') ?></p>
                        <p class="text-xs font-bold v3-text-muted"><span><?= esc($addendum['added_by_name'] ?? '') ?></span> &middot; <span dir="ltr"><?= esc(formatDateTime($addendum['created_at'] ?? '')) ?></span></p>
                    </div>
                    <div class="mt-3 whitespace-pre-line text-sm font-semibold leading-6 v3-text-ink"><?= esc($addendum['body'] ?? '') ?></div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (has_permission('examinations', 'edit')): ?>
            <form method="post" action="<?= base_url('examination/' . (int) $examination['id'] . '/clinical-note/addendum') ?>" class="v3-radius v3-bg-panel p-4 v3-elevation-none border v3-border-default">
                <?= csrf_field() ?>
                <h3 class="text-sm font-black v3-text-ink"><?= esc($tr('add_note_addendum')) ?></h3>
                <div class="mt-4 grid grid-cols-1 gap-4">
                    <div>
                        <label for="note_addendum_reason" class="mb-2 block text-sm font-bold v3-text-ink"><?= esc($tr('addendum_reason')) ?></label>
                        <input id="note_addendum_reason" name="reason" maxlength="255" value="<?= esc(old('reason')) ?>" required class="w-full v3-radius border-2 v3-border-default v3-bg-panel px-4 py-3 text-sm font-semibold v3-text-ink v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-input">
                    </div>
                    <div>
                        <label for="note_addendum_body" class="mb-2 block text-sm font-bold v3-text-ink"><?= esc($tr('addendum_text')) ?></label>
                        <textarea id="note_addendum_body" name="body" rows="4" maxlength="10000" required class="w-full resize-y v3-radius border-2 v3-border-default v3-bg-panel px-4 py-3 text-sm font-semibold v3-text-ink v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-textarea"><?= esc(old('body')) ?></textarea>
                    </div>
                </div>
                <button type="submit" class="mt-4 inline-flex items-center justify-center gap-2 v3-radius v3-bg-primary px-5 py-3 text-sm font-black v3-text-on-primary v3-elevation-none transition v3-hover-primary v3-btn v3-btn--primary"><i class="fas fa-plus"></i><?= esc($tr('append_addendum')) ?></button>
            </form>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-sm font-semibold leading-6 v3-text-warning"><?= esc($tr('unsigned_note_help')) ?></p>
            <?php if (($status_key ?? '') === 'completed' && has_permission('examinations', 'edit')): ?>
            <form method="post" action="<?= base_url('examination/' . (int) $examination['id'] . '/clinical-note/sign') ?>" class="v3-radius v3-bg-panel p-4 v3-elevation-none border v3-border-default">
                <?= csrf_field() ?>
                <label class="flex cursor-pointer items-start gap-3">
                    <input type="checkbox" name="attest" value="1" required class="mt-1 h-5 w-5 rounded v3-border-default v3-text-warning v3-focus-control v3-checkbox">
                    <span class="text-sm font-bold leading-6 v3-text-ink"><?= esc($tr('sign_attestation')) ?></span>
                </label>
                <button type="submit" class="mt-4 inline-flex items-center justify-center gap-2 v3-radius v3-bg-warning px-5 py-3 text-sm font-black v3-text-on-primary v3-elevation-none transition v3-hover-primary v3-btn v3-btn--primary"><i class="fas fa-file-signature"></i><?= esc($tr('sign_and_lock_note')) ?></button>
            </form>
            <?php elseif (($status_key ?? '') !== 'completed'): ?>
                <p class="v3-radius v3-bg-panel p-4 text-sm font-bold v3-text-ink v3-elevation-none border v3-border-default"><?= esc($tr('complete_before_signing')) ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
