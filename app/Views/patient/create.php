<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php $validation = $validation ?? null; ?>

<style>
.toggle-track {
    transition: background-color 0.2s ease;
}

.toggle-dot {
    transition: transform 0.2s ease;
}
</style>

<div class="v3-record-flow">
    <header class="v3-flow-command">
        <div><h1><?= esc(lang('UI.patientCreateTitle')) ?></h1><p><?= esc(lang('UI.patientCreateSubtitle')) ?></p></div>
        <div class="v3-flow-navigation"><a href="<?= esc(workflow_back('patient')) ?>" class="relative inline-flex items-center px-6 py-3 border-2 text-sm font-bold">
                            <i class="fas fa-arrow-left rtl-back-icon me-2"></i>
                            <span><?= esc(lang('UI.patientBack')) ?></span>
                        </a></div>
    </header>
    <div class="v3-flow-body">
        <?php if (session('error')): ?>
            <div class="v3-flow-error" role="alert"><?= esc(session('error')) ?></div>
        <?php endif; ?>
<form action="<?= base_url('patient/store') ?>" method="POST" class="v3-flow-form v3-flow-form--patient" data-patient-create-form>
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?>
                            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                        <?php endif; ?>

                        <?= view('patient/partials/create_form_fields', [
                            'validation' => $validation,
                            'is_rtl' => $is_rtl ?? false,
                        ]) ?>

                        <div class="v3-flow-actions">
                            <a href="<?= esc(workflow_cancel('patient', $return_to ?? null)) ?>" class="inline-flex items-center justify-center px-6 py-3 border text-sm font-semibold">
                                <?= esc(lang('UI.cancel')) ?>
                            </a>
                            <button type="submit" name="post_submit_action" value="save_only" class="v3-flow-submit inline-flex items-center justify-center px-6 py-3 border text-sm font-bold duration-200">
                                <i class="fas fa-user-plus me-2"></i>
                                <?= esc(lang('UI.settingsSave')) ?>
                            </button>
                            <button type="submit" name="post_submit_action" value="save_and_book" class="v3-flow-submit inline-flex items-center justify-center px-6 py-3 text-sm font-bold duration-200">
                                <i class="fas fa-calendar-plus me-2"></i>
                                <?= esc(lang('UI.patientSaveAndBookAppointment')) ?>
                            </button>
                        </div>
                    </form>
    </div>
</div>

<?= view('patient/partials/create_form_script') ?>
<?= $this->endSection() ?>
