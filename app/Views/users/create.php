<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="v3-operational-view">
<div class="v3-operational-view">
    <div class="v3-operational-body">
        <div class="relative">
            <div class="relative overflow-hidden">
                <div class="border-b">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
                        <div class="flex items-center gap-4">
                            <div class="relative">

                                <div class="relative w-12 h-12 flex items-center justify-center">
                                    <i class="fas fa-user-plus text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="v3-section-title"><?= lang('UserRole.createNewUser') ?></h3>
                                <p class="font-medium"><?= lang('UserRole.addNewUserDescription') ?></p>
                            </div>
                        </div>

                        <a href="<?= workflow_back('users', $return_to ?? null) ?>" class="inline-flex items-center justify-center px-6 py-3 border-2 text-sm font-bold v3-btn v3-btn--secondary">
                            <i class="fas fa-arrow-left rtl-back-icon me-2"></i>
                            <span><?= lang('UserRole.backToUsers') ?></span>
                        </a>
                    </div>
                </div>

                <div class="">
                    <form action="<?= base_url('users/store') ?>" method="POST" class="v3-flow-form " data-user-create-form data-select2-dropdown-parent>
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?>
                            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                        <?php endif; ?>
                        <?= view('users/partials/create_form_fields', [
                            'roles' => $roles ?? [],
                            'departments' => $departments ?? [],
                            'is_rtl' => !empty($is_rtl),
                        ]) ?>

                        <div class="flex flex-col sm:flex-row justify-end gap-4 pt-6 border-t">
                            <a href="<?= workflow_back('users', $return_to ?? null) ?>" class="inline-flex items-center justify-center px-8 py-3 border-2 text-sm font-bold v3-btn v3-btn--secondary">
                                <i class="fas fa-times me-2"></i>
                                <span><?= lang('UserRole.cancel') ?></span>
                            </a>
                            <button type="submit" class="inline-flex items-center justify-center px-8 py-3 text-sm font-bold v3-btn v3-btn--primary">
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
</div>
<?= $this->endSection() ?>
