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
        <span class="ms-auto rounded-full px-3 py-1 text-xs font-black <?= $signed ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>"><?= esc($signed ? $tr('note_signed_locked') : $tr('note_unsigned')) ?></span>
    </div>
    <div class="exam-card-body space-y-5">
        <?php if ($signed && $signature): ?>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-xl bg-white/90 p-4 shadow-sm ring-1 ring-emerald-100">
                    <p class="text-xs font-bold text-slate-500"><?= esc($tr('signed_by')) ?></p>
                    <p class="mt-1 font-black text-slate-900"><?= esc($signature['signed_by_name'] ?? '') ?></p>
                </div>
                <div class="rounded-xl bg-white/90 p-4 shadow-sm ring-1 ring-emerald-100">
                    <p class="text-xs font-bold text-slate-500"><?= esc($tr('signed_at')) ?></p>
                    <p class="mt-1 font-black text-slate-900" dir="ltr"><?= esc(formatDateTime($signature['signed_at'] ?? '')) ?></p>
                </div>
            </div>
            <p class="text-sm font-semibold leading-6 text-emerald-900"><?= esc($tr('signed_note_help')) ?></p>

            <?php if ($addenda): ?>
            <div class="space-y-3">
                <h3 class="text-sm font-black text-slate-900"><?= esc($tr('note_addenda')) ?></h3>
                <?php foreach ($addenda as $addendum): ?>
                <article class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-black text-slate-900"><?= esc($addendum['reason'] ?? '') ?></p>
                        <p class="text-xs font-bold text-slate-500"><span><?= esc($addendum['added_by_name'] ?? '') ?></span> &middot; <span dir="ltr"><?= esc(formatDateTime($addendum['created_at'] ?? '')) ?></span></p>
                    </div>
                    <div class="mt-3 whitespace-pre-line text-sm font-semibold leading-6 text-slate-700"><?= esc($addendum['body'] ?? '') ?></div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (has_permission('examinations', 'edit')): ?>
            <form method="post" action="<?= base_url('examination/' . (int) $examination['id'] . '/clinical-note/addendum') ?>" class="rounded-xl bg-white/85 p-4 shadow-sm ring-1 ring-emerald-100">
                <?= csrf_field() ?>
                <h3 class="text-sm font-black text-slate-900"><?= esc($tr('add_note_addendum')) ?></h3>
                <div class="mt-4 grid grid-cols-1 gap-4">
                    <div>
                        <label for="note_addendum_reason" class="mb-2 block text-sm font-bold text-slate-700"><?= esc($tr('addendum_reason')) ?></label>
                        <input id="note_addendum_reason" name="reason" maxlength="255" value="<?= esc(old('reason')) ?>" required class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                    </div>
                    <div>
                        <label for="note_addendum_body" class="mb-2 block text-sm font-bold text-slate-700"><?= esc($tr('addendum_text')) ?></label>
                        <textarea id="note_addendum_body" name="body" rows="4" maxlength="10000" required class="w-full resize-y rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"><?= esc(old('body')) ?></textarea>
                    </div>
                </div>
                <button type="submit" class="mt-4 inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-700 px-5 py-3 text-sm font-black text-white shadow-md transition hover:bg-emerald-800"><i class="fas fa-plus"></i><?= esc($tr('append_addendum')) ?></button>
            </form>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-sm font-semibold leading-6 text-amber-900"><?= esc($tr('unsigned_note_help')) ?></p>
            <?php if (($status_key ?? '') === 'completed' && has_permission('examinations', 'edit')): ?>
            <form method="post" action="<?= base_url('examination/' . (int) $examination['id'] . '/clinical-note/sign') ?>" class="rounded-xl bg-white/90 p-4 shadow-sm ring-1 ring-amber-100">
                <?= csrf_field() ?>
                <label class="flex cursor-pointer items-start gap-3">
                    <input type="checkbox" name="attest" value="1" required class="mt-1 h-5 w-5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                    <span class="text-sm font-bold leading-6 text-slate-700"><?= esc($tr('sign_attestation')) ?></span>
                </label>
                <button type="submit" class="mt-4 inline-flex items-center justify-center gap-2 rounded-xl bg-amber-600 px-5 py-3 text-sm font-black text-white shadow-md transition hover:bg-amber-700"><i class="fas fa-file-signature"></i><?= esc($tr('sign_and_lock_note')) ?></button>
            </form>
            <?php elseif (($status_key ?? '') !== 'completed'): ?>
                <p class="rounded-xl bg-white/90 p-4 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-amber-100"><?= esc($tr('complete_before_signing')) ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
