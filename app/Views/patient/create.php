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

<div class="min-h-screen bg-slate-50">
    <div class="container mx-auto px-4 py-6 max-w-7xl">
        <div class="group relative">
            <div class="relative bg-white rounded-2xl shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-500 overflow-hidden">
                <div class="p-8 border-b border-slate-100">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-user-plus text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-blue-900 transition-colors duration-300"><?= esc(lang('UI.patientCreateTitle')) ?></h3>
                                <p class="text-gray-600 font-medium"><?= esc(lang('UI.patientCreateSubtitle')) ?></p>
                            </div>
                        </div>
                        <a href="<?= esc(workflow_back('patient')) ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                            <i class="fas fa-arrow-left rtl-back-icon me-2"></i>
                            <span><?= esc(lang('UI.patientBack')) ?></span>
                        </a>
                    </div>
                </div>

                <div class="p-8">
                    <?php if (session('error')): ?>
                        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('patient/store') ?>" method="POST" class="space-y-8" data-patient-create-form>
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?>
                            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                        <?php endif; ?>

                        <?= view('patient/partials/create_form_fields', [
                            'validation' => $validation,
                            'is_rtl' => $is_rtl ?? false,
                        ]) ?>

                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-6 border-t border-slate-200">
                            <a href="<?= esc(workflow_cancel('patient', $return_to ?? null)) ?>" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50">
                                <?= esc(lang('UI.cancel')) ?>
                            </a>
                            <button type="submit" name="post_submit_action" value="save_only" class="inline-flex items-center justify-center px-6 py-3 border border-blue-200 bg-white text-blue-700 text-sm font-bold rounded-xl hover:bg-blue-50 transition-all duration-200 shadow-sm hover:shadow-md">
                                <i class="fas fa-user-plus me-2"></i>
                                <?= esc(lang('UI.settingsSave')) ?>
                            </button>
                            <button type="submit" name="post_submit_action" value="save_and_book" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white text-sm font-bold rounded-xl hover:from-blue-700 hover:to-cyan-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                <i class="fas fa-calendar-plus me-2"></i>
                                <?= esc(lang('UI.patientSaveAndBookAppointment')) ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('patient/partials/create_form_script') ?>
<?= $this->endSection() ?>
