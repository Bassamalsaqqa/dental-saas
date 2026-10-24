<?php
$modalReturnTo = (string) ($return_to ?? 'users');
$isRtl = !empty($is_rtl);
$shouldOpenUserCreateModal = old('source_modal') === 'user_quick_create';
?>
<?php if (has_permission('users', 'create')): ?>


    <div id="newUserModal" class="v3-context-drawer user-create-modal fixed inset-0 z-[2147483600] hidden overflow-hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="newUserModalTitle" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
        <div class="user-create-modal__backdrop v3-context-drawer__backdrop" data-user-create-modal-close></div>
        <div class="user-create-modal__shell v3-context-drawer__shell" data-user-create-modal-close>
            <form id="newUserQuickCreateForm" action="<?= base_url('users/store') ?>" method="POST" class="user-create-modal__panel v3-context-drawer__panel" data-user-create-form data-select2-dropdown-parent onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="<?= esc($modalReturnTo) ?>">
                <input type="hidden" name="source_modal" value="user_quick_create">

                <div class="v3-context-drawer__header">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center">
                            <i class="fas fa-user-plus"></i>
                        </span>
                        <div>
                            <h2 id="newUserModalTitle" class="text-lg font-bold"><?= esc(lang('UserRole.createNewUser')) ?></h2>
                            <a href="<?= base_url('users/create?' . http_build_query(['return_to' => $modalReturnTo])) ?>" class="mt-1 inline-flex text-xs font-semibold">
                                <?= esc(lang('UI.openFullPage')) ?>
                            </a>
                        </div>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center transition" data-user-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="v3-context-drawer__body">
                    <?php if (session('error') && $shouldOpenUserCreateModal): ?>
                        <div class="border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>

                    <?= view('users/partials/create_form_fields', [
                        'roles' => $roles ?? [],
                        'departments' => $departments ?? [],
                        'is_rtl' => $isRtl,
                        'sectionClass' => 'v3-flow-section',
                        'sectionGapClass' => 'v3-flow-fields',
                    ]) ?>
                </div>

                <div class="v3-context-drawer__footer">
                    <button type="button" data-user-create-modal-close class="inline-flex items-center justify-center border px-5 py-2.5 text-sm font-semibold transition">
                        <?= esc(lang('UserRole.cancel')) ?>
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-bold transition">
                        <i class="fas fa-user-plus me-2"></i>
                        <?= esc(lang('UserRole.createUser')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function () {
        if (window.__userQuickCreateModalBound) {
            return;
        }
        window.__userQuickCreateModalBound = true;

        let userModalTrigger = null;
        let userModalCloseTimer = null;
        const shouldReopen = <?= $shouldOpenUserCreateModal ? 'true' : 'false' ?>;

        function modal() {
            const el = document.getElementById('newUserModal');
            if (el && el.parentElement !== document.body) {
                document.body.appendChild(el);
            }
            return el;
        }

        function resetUserForm(el) {
            if (!el || shouldReopen) {
                return;
            }
            const form = el.querySelector('[data-user-create-form]');
            if (!form) {
                return;
            }
            form.reset();
            form.querySelectorAll('.select2-hidden-accessible').forEach(function (select) {
                if (window.jQuery) {
                    window.jQuery(select).trigger('change');
                }
            });
            const hireDate = form.querySelector('#hire_date');
            if (hireDate && !hireDate.value) {
                hireDate.value = new Date().toISOString().slice(0, 10);
            }
            const note = form.querySelector('#users-email-exists-note');
            if (note) {
                note.classList.add('hidden');
                const text = note.querySelector('[data-note-text]');
                if (text) {
                    text.textContent = '';
                }
            }
            form.querySelectorAll('#password,#password_confirm').forEach(function (field) {
                field.disabled = false;
                field.required = true;
                field.classList.remove('opacity-50', 'pointer-events-none');
            });
        }

        function initializeContent(el) {
            if (!el) {
                return Promise.resolve();
            }
            const tasks = [];
            if (typeof window.initAppSelects === 'function') {
                tasks.push(Promise.resolve(window.initAppSelects(el)));
            }
            if (typeof window.initializeClinicDateInputs === 'function') {
                window.initializeClinicDateInputs(el);
            }
            return Promise.allSettled(tasks);
        }

        function openUserModal(trigger) {
            const el = modal();
            if (!el) {
                return;
            }
            userModalTrigger = trigger || document.activeElement;
            if (userModalCloseTimer) {
                clearTimeout(userModalCloseTimer);
                userModalCloseTimer = null;
            }
            el.classList.remove('hidden');
            el.setAttribute('aria-hidden', 'false');
            document.documentElement.classList.add('app-drawer-lock');
            document.body.classList.add('app-drawer-lock');
            requestAnimationFrame(function () {
                el.classList.add('is-open');
            });
            window.setTimeout(function () {
                initializeContent(el);
                const firstField = el.querySelector('#first_name');
                if (firstField && typeof firstField.focus === 'function') {
                    firstField.focus({ preventScroll: true });
                }
            }, 80);
        }

        function closeUserModal() {
            const el = document.getElementById('newUserModal');
            if (!el || el.classList.contains('hidden')) {
                return;
            }
            el.classList.remove('is-open');
            el.setAttribute('aria-hidden', 'true');
            document.documentElement.classList.remove('app-drawer-lock');
            document.body.classList.remove('app-drawer-lock');
            userModalCloseTimer = setTimeout(function () {
                resetUserForm(el);
                el.classList.add('hidden');
                if (userModalTrigger && typeof userModalTrigger.focus === 'function') {
                    userModalTrigger.focus();
                }
                userModalTrigger = null;
            }, 240);
        }

        document.addEventListener('click', function (event) {
            const openTrigger = event.target.closest('[data-user-create-modal-open]');
            if (openTrigger) {
                event.preventDefault();
                openUserModal(openTrigger);
                return;
            }
            const closeTrigger = event.target.closest('[data-user-create-modal-close]');
            if (closeTrigger) {
                event.preventDefault();
                closeUserModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }
            const el = document.getElementById('newUserModal');
            if (el && !el.classList.contains('hidden')) {
                event.preventDefault();
                closeUserModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            if (shouldReopen) {
                openUserModal(document.querySelector('[data-user-create-modal-open]'));
            }
        });
    })();
    </script>
<?php endif; ?>
