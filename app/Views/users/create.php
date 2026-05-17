<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-slate-50">
    <div class="container mx-auto relative z-10 px-4 py-6">
        <div class="group relative">
            <div class="relative bg-white rounded-2xl shadow-2xl shadow-blue-500/10 transition-all duration-500 overflow-hidden">
                <div class="p-8 border-b border-white/20">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl blur-lg opacity-75"></div>
                                <div class="relative w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-xl">
                                    <i class="fas fa-user-plus text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-900"><?= lang('UserRole.createNewUser') ?></h3>
                                <p class="text-gray-600 font-medium"><?= lang('UserRole.addNewUserDescription') ?></p>
                            </div>
                        </div>

                        <a href="<?= nav_back('users') ?>" class="inline-flex items-center justify-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 hover:border-gray-400 transition-all duration-300">
                            <i class="fas fa-arrow-left rtl-back-icon me-2"></i>
                            <span><?= lang('UserRole.backToUsers') ?></span>
                        </a>
                    </div>
                </div>

                <div class="p-8">
                    <form action="<?= base_url('users/store') ?>" method="POST" class="space-y-8" data-user-create-form data-select2-dropdown-parent>
                        <?= csrf_field() ?>
                        <?= view('users/partials/create_form_fields', [
                            'roles' => $roles ?? [],
                            'departments' => $departments ?? [],
                            'is_rtl' => !empty($is_rtl),
                        ]) ?>

                        <div class="flex flex-col sm:flex-row justify-end gap-4 pt-6 border-t border-slate-200">
                            <a href="<?= nav_back('users') ?>" class="inline-flex items-center justify-center px-8 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 hover:border-gray-400 transition-all duration-300">
                                <i class="fas fa-times me-2"></i>
                                <span><?= lang('UserRole.cancel') ?></span>
                            </a>
                            <button type="submit" class="inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white text-sm font-bold rounded-xl hover:from-blue-600 hover:to-purple-700 transition-all duration-300 hover:shadow-2xl hover:shadow-blue-500/25">
                                <i class="fas fa-user-plus me-2"></i>
                                <span><?= lang('UserRole.createUser') ?></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= view('components/email_check_script', ['emailCheckUrl' => base_url('users/check-email')]) ?>
<?= $this->endSection() ?>
