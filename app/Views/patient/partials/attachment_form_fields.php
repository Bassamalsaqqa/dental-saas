<?php
$inputClass = 'w-full v3-radius border v3-border-default px-3 py-2.5 text-sm v3-text-ink v3-focus-control focus:outline-none focus:ring-2 v3-focus-control';
$selectedAttachmentCategory = old('category');
?>
<?= view('patient/partials/context_patient_badge', ['context_patient' => $patient ?? null]) ?>
<div class="space-y-4">
    <div>
        <label class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc(lang('UI.patientAttachmentFile')) ?></label>
        <input type="file" name="attachment[]" required multiple accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx,.zip" class="block w-full v3-radius border v3-border-default px-3 py-2.5 text-sm v3-text-ink file:me-3 file:rounded-lg file:border-0 file:v3-bg-soft file:px-3 file:py-2 file:text-sm file:font-semibold file:v3-text-ink hover:file:v3-bg-sand v3-input">
        <?php if (!empty($max_file_mb)): ?>
            <p class="mt-2 text-xs v3-text-muted"><?= esc(lang('UI.patientMaxFileSize')) ?>: <?= esc((string) $max_file_mb) ?> MB</p>
        <?php endif; ?>
    </div>
    <div>
        <label class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc(lang('UI.patientAttachmentCategory')) ?></label>
        <select name="category" class="<?= esc($inputClass) ?> v3-select" data-force-select2>
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
        <label class="mb-2 block text-sm font-semibold v3-text-ink"><?= esc(lang('UI.patientAttachmentNotes')) ?></label>
        <textarea name="notes" rows="3" class="<?= esc($inputClass) ?> resize-y v3-textarea" placeholder="<?= esc(lang('UI.patientAttachmentNotesPlaceholder')) ?>"><?= old('notes') ?></textarea>
    </div>
</div>
