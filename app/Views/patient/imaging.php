<?= $this->extend('layouts/main_auth') ?>

<?php
$latestImagingAttachment = $latest_imaging_attachment ?? null;
$latestImagingPreviewSrc = $latest_imaging_preview_src ?? null;
$imagingRecords = $imaging_records ?? [];
$maxFileMb = $attachments_max_file_mb ?? 5;
$usagePercent = (int) ($attachments_usage_percent ?? 0);
$usageBytes = (int) ($attachments_storage_usage_bytes ?? 0);
$limitGb = (int) ($attachments_storage_limit_gb ?? 0);
$limitBytes = $limitGb > 0 ? $limitGb * 1024 * 1024 * 1024 : 0;
$isRtl = !empty($is_rtl);
$fullName = trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''));
$patientHeroThemeClass = 'patient-hero-card-neutral';
$patientAvatarIconClass = 'fas fa-user';
$patientGenderValue = strtolower((string) ($patient['gender'] ?? ''));
if ($patientGenderValue === 'female') {
    $patientHeroThemeClass = 'patient-hero-card-female';
    $patientAvatarIconClass = 'fas fa-person-dress';
} elseif ($patientGenderValue === 'male') {
    $patientHeroThemeClass = 'patient-hero-card-male';
    $patientAvatarIconClass = 'fas fa-person';
}
?>

<?= $this->section('content') ?>
<div class="mx-auto w-full max-w-none px-2 py-6 sm:px-3 sm:py-8 xl:px-4 2xl:px-5">
    <div class="space-y-6">
        <section class="patient-hero-card <?= esc($patientHeroThemeClass) ?> overflow-hidden rounded-[28px] border border-slate-200">
            <div class="flex flex-col gap-5 p-6 sm:p-8 xl:flex-row xl:items-center xl:justify-between">
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-blue-100/85">
                        <span><?= esc(lang('UI.backToPatient')) ?></span>
                        <span class="h-1 w-1 rounded-full bg-blue-200/70"></span>
                        <span><?= esc(lang('UI.patientImagingWorkspaceTitle')) ?></span>
                    </div>
                    <div class="space-y-2">
                        <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl"><?= esc($fullName) ?></h1>
                        <p class="max-w-3xl text-sm leading-7 text-blue-100/90"><?= esc(lang('UI.patientImagingWorkspaceHelp')) ?></p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('patient/' . $patient['id']) ?>" class="inline-flex items-center gap-2 rounded-xl bg-white/12 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/18">
                        <i class="<?= esc($patientAvatarIconClass) ?>"></i>
                        <span><?= esc(lang('UI.patientDetailsTitle')) ?></span>
                    </a>
                </div>
            </div>
        </section>

        <?php if ($limitBytes > 0 && $usagePercent >= 80): ?>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                <strong><?= esc(lang('UI.patientStorageWarning')) ?></strong> <?= esc(lang('UI.patientStorageWarningText', ['percent' => $usagePercent])) ?>
            </div>
        <?php endif; ?>

        <?php if (session('success')): ?>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
                <?= esc(session('success')) ?>
            </div>
        <?php endif; ?>

        <?php if (session('error')): ?>
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                <?= esc(session('error')) ?>
            </div>
        <?php endif; ?>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(340px,0.95fr)]">
            <section class="rounded-3xl border border-slate-200 bg-white shadow-[0_22px_55px_-42px_rgba(15,23,42,0.30)]">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-blue-50/70 px-5 py-4">
                    <h2 class="text-lg font-bold text-slate-900"><?= esc(lang('UI.patientImagingWorkspaceLatestTitle')) ?></h2>
                    <p class="mt-1 text-sm text-slate-500"><?= esc(lang('UI.patientLatestImagingHelp')) ?></p>
                </div>
                <div class="space-y-4 p-5">
                    <?php if (!empty($latestImagingAttachment)): ?>
                        <div class="overflow-hidden rounded-[24px] border border-slate-200 bg-slate-950">
                            <div class="aspect-[16/9] flex items-center justify-center">
                                <?php if (!empty($latestImagingPreviewSrc)): ?>
                                    <img src="<?= esc($latestImagingPreviewSrc) ?>" alt="<?= esc($latestImagingAttachment['original_name'] ?? lang('UI.patientLatestImagingTitle')) ?>" class="h-full w-full object-contain">
                                <?php elseif (!empty($latestImagingAttachment['is_previewable'])): ?>
                                    <img src="<?= esc($latestImagingAttachment['inline_url']) ?>" alt="<?= esc($latestImagingAttachment['original_name'] ?? lang('UI.patientLatestImagingTitle')) ?>" class="h-full w-full object-contain">
                                <?php else: ?>
                                    <div class="px-6 text-center text-sm text-slate-300"><?= esc(lang('UI.patientImagingPreviewUnavailable')) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <h3 class="text-base font-bold text-slate-900"><?= esc($latestImagingAttachment['category'] ?? lang('UI.patientLatestImagingTitle')) ?></h3>
                                <p class="mt-1 text-sm text-slate-500"><?= esc($latestImagingAttachment['original_name'] ?? lang('UI.patientAttachmentFallback')) ?></p>
                                <p class="mt-1 text-xs text-slate-400"><?= esc(!empty($latestImagingAttachment['created_at']) ? formatDateTime($latestImagingAttachment['created_at']) : '--') ?></p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <?php if (!empty($latestImagingAttachment['inline_url']) && !empty($latestImagingAttachment['is_previewable'])): ?>
                                    <a href="<?= esc($latestImagingAttachment['inline_url']) ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200">
                                        <i class="fas fa-eye"></i>
                                        <span><?= esc(lang('UI.patientOpenImaging')) ?></span>
                                    </a>
                                <?php endif; ?>
                                <a href="<?= esc($latestImagingAttachment['download_url']) ?>" class="inline-flex items-center gap-2 rounded-xl bg-cyan-50 px-4 py-2.5 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-100">
                                    <i class="fas fa-download"></i>
                                    <span><?= esc(lang('UI.patientImagingWorkspaceOpenFile')) ?></span>
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="rounded-[24px] border border-dashed border-slate-300 bg-slate-50 px-5 py-16 text-center">
                            <i class="fas fa-x-ray text-3xl text-slate-300"></i>
                            <p class="mt-4 text-sm text-slate-500"><?= esc(lang('UI.patientNoImagingPreview')) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <aside class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white shadow-[0_22px_55px_-42px_rgba(15,23,42,0.30)]">
                    <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-cyan-50/70 px-5 py-4">
                        <h2 class="text-lg font-bold text-slate-900"><?= esc(lang('UI.patientImagingWorkspaceUploadTitle')) ?></h2>
                        <p class="mt-1 text-sm text-slate-500"><?= esc(lang('UI.patientImagingWorkspaceUploadHelp')) ?></p>
                    </div>
                    <div class="p-5">
                        <form action="<?= base_url('patient/' . $patient['id'] . '/attachments/upload') ?>" method="post" enctype="multipart/form-data" class="space-y-4">
                            <?= csrf_field() ?>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700"><?= esc(lang('UI.patientUploadFiles')) ?></label>
                                <input type="file" name="attachment[]" id="patientImagingInput" required multiple accept=".jpg,.jpeg,.png,.pdf" class="block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 file:me-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700"><?= esc(lang('UI.patientAttachmentCategory')) ?></label>
                                <select name="category" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200">
                                    <?php foreach (($imaging_categories ?? []) as $category): ?>
                                        <option value="<?= esc($category) ?>" <?= $category === 'Panoramic X-Ray (OPG)' ? 'selected' : '' ?>><?= esc($category) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700"><?= esc(lang('UI.patientNotes')) ?></label>
                                <textarea name="notes" rows="4" placeholder="<?= esc(lang('UI.patientAttachmentNotesPlaceholder')) ?>" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-200"></textarea>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-xs text-slate-400"><?= esc(lang('UI.patientAttachmentsSubtitle', ['max' => ($maxFileMb === null ? lang('UI.patientUnlimited') : ((int) $maxFileMb . 'MB'))])) ?></p>
                                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700">
                                    <i class="fas fa-upload"></i>
                                    <span><?= esc(lang('UI.patientUpload')) ?></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white shadow-[0_22px_55px_-42px_rgba(15,23,42,0.30)]">
                    <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-slate-100 px-5 py-4">
                        <h2 class="text-lg font-bold text-slate-900"><?= esc(lang('UI.patientImagingWorkspaceLibraryTitle')) ?></h2>
                        <p class="mt-1 text-sm text-slate-500"><?= esc(lang('UI.patientImagingWorkspaceLibraryHelp')) ?></p>
                    </div>
                    <div class="max-h-[calc(100vh-22rem)] space-y-3 overflow-y-auto p-4">
                        <?php if (!empty($imagingRecords)): ?>
                            <?php foreach ($imagingRecords as $image): ?>
                                <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-slate-900"><?= esc($image['category'] ?? lang('UI.patientLatestImagingTitle')) ?></p>
                                            <p class="mt-1 truncate text-sm text-slate-600"><?= esc($image['original_name'] ?? lang('UI.patientAttachmentFallback')) ?></p>
                                        </div>
                                        <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500"><?= esc(number_format(((int) ($image['file_size'] ?? 0)) / 1024, 1)) ?> KB</span>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-2 text-xs text-slate-400">
                                        <span><?= esc(!empty($image['created_at']) ? formatDateTime($image['created_at']) : '--') ?></span>
                                        <?php if (!empty($image['creator_name'])): ?>
                                            <span><?= esc(lang('UI.patientImagingWorkspaceCapturedBy', ['name' => $image['creator_name']])) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($image['notes'])): ?>
                                        <p class="mt-3 text-sm leading-6 text-slate-600"><?= esc($image['notes']) ?></p>
                                    <?php endif; ?>
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <?php if (!empty($image['is_previewable'])): ?>
                                            <a href="<?= esc($image['inline_url']) ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                                                <i class="fas fa-eye"></i>
                                                <span><?= esc(lang('UI.patientOpenImaging')) ?></span>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= esc($image['download_url']) ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                                            <i class="fas fa-download"></i>
                                            <span><?= esc(lang('UI.patientImagingWorkspaceOpenFile')) ?></span>
                                        </a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-10 text-center">
                                <i class="fas fa-images text-3xl text-slate-300"></i>
                                <p class="mt-4 text-sm text-slate-500"><?= esc(lang('UI.patientImagingWorkspaceEmpty')) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>
<style>
    .patient-hero-card {
        position: relative;
        --hero-angle: 135deg;
        --hero-start: #0f3f90;
        --hero-mid: #1656c1;
        --hero-end: #0f7cc1;
        background: linear-gradient(var(--hero-angle), var(--hero-start) 0%, var(--hero-mid) 58%, var(--hero-end) 100%);
        box-shadow: 0 28px 70px -44px rgba(15, 23, 42, 0.75);
    }

    .patient-hero-card-male {
        --hero-start: #0f3f90;
        --hero-mid: #1656c1;
        --hero-end: #0f7cc1;
    }

    .patient-hero-card-female {
        --hero-start: #8a245a;
        --hero-mid: #c03579;
        --hero-end: #7c3aed;
    }

    .patient-hero-card-neutral {
        --hero-start: #334155;
        --hero-mid: #2563eb;
        --hero-end: #0f766e;
    }
</style>
<?= $this->endSection() ?>
