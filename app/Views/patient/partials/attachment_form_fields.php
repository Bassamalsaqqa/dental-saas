<?php
$inputClass = 'w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200';
$selectedAttachmentCategory = old('category');
?>
<?= view('patient/partials/context_patient_badge', ['context_patient' => $patient ?? null]) ?>
<div class="space-y-4">
    <div>
        <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('UI.patientAttachmentFile')) ?></label>
        <input type="file" name="attachment[]" required multiple accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx,.zip" class="block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 file:me-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
        <?php if (!empty($max_file_mb)): ?>
            <p class="mt-2 text-xs text-slate-500"><?= esc(lang('UI.patientMaxFileSize')) ?>: <?= esc((string) $max_file_mb) ?> MB</p>
        <?php endif; ?>
    </div>
    <div>
        <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('UI.patientAttachmentCategory')) ?></label>
        <select name="category" class="<?= esc($inputClass) ?>" data-force-select2>
            <option value=""><?= esc(lang('UI.patientAttachmentChooseCategory')) ?></option>
            <?php foreach (($attachment_category_options ?? []) as $groupLabel => $groupOptions): ?>
                <optgroup label="<?= esc($groupLabel) ?>">
                    <?php foreach ($groupOptions as $optionLabel): ?>
                        <option value="<?= esc($optionLabel) ?>" <?= $selectedAttachmentCategory === $optionLabel ? 'selected' : '' ?>><?= esc($optionLabel) ?></option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="mb-2 block text-sm font-semibold text-slate-700"><?= esc(lang('UI.patientAttachmentNotes')) ?></label>
        <textarea name="notes" rows="3" class="<?= esc($inputClass) ?> resize-y" placeholder="<?= esc(lang('UI.patientAttachmentNotesPlaceholder')) ?>"><?= old('notes') ?></textarea>
    </div>
</div>
