<?php include APPPATH . 'Views/layouts/partials/view_permissions.php'; helper('asset'); ?>
<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$tr = static fn(string $key, array $params = []) => lang('Examination.' . $key, $params);
$currentUri = service('request')->getUri();
$returnToContext = ltrim($currentUri->getPath(), '/');
$currentQuery = $currentUri->getQuery();
if ($currentQuery !== '') {
    $returnToContext .= '?' . $currentQuery;
}
$returnToContextEncoded = urlencode($returnToContext);
$shouldOpenExaminationCreateModal = old('source_modal') === 'examination_quick_create';
$isRtl = !empty($is_rtl) || (service('request')->getLocale() === 'ar');
$documentationFilter = (string) ($documentation_filter ?? '');
$documentationQueue = is_array($documentation_queue ?? null) ? $documentation_queue : [];
$readyToSignCount = (int) ($documentationQueue['ready_to_sign_count'] ?? 0);
$overdueDraftCount = (int) ($documentationQueue['overdue_draft_count'] ?? 0);
?>

<link rel="stylesheet" href="<?= base_url('assets/css/v4-operations.css') ?>?v=<?= esc(asset_version('assets/css/v4-operations.css')) ?>">
<script defer src="<?= base_url('assets/js/v4-record-cards.js') ?>?v=<?= esc(asset_version('assets/js/v4-record-cards.js')) ?>"></script>
<div class="v4-clinical-registry"><div class="v4-clinical-main">
<header class="v4-page-heading"><div><h1><?= esc(lang('Examination.examination_management')) ?></h1><p><?= esc(lang('V4.examinationHelp')) ?></p></div><div class="v4-page-actions"><?php if($v4Can('examinations','create')): ?><a id="newExaminationModalTrigger" href="<?= base_url('examination/create') ?>" data-examination-create-modal-open class="v4-button v4-button--primary"><i class="fas fa-plus" aria-hidden="true"></i><?= esc(lang('Examination.new_examination')) ?></a><?php endif; ?></div></header>
<div class="v4-clinical-queue"><div><h2><?= esc($tr('documentation_queue')) ?></h2><p><?= esc($tr('documentation_queue_help')) ?></p></div><a class="v4-button" href="<?= base_url('examination?documentation=ready_to_sign') ?>"><strong><?= $readyToSignCount ?></strong><?= esc($tr('documentation_ready_to_sign')) ?></a><a class="v4-button" href="<?= base_url('examination?documentation=overdue_draft') ?>"><strong><?= $overdueDraftCount ?></strong><?= esc($tr('documentation_overdue_draft')) ?></a><label class="v4-form"><span class="sr-only"><?= esc($tr('documentation_all')) ?></span><select id="documentationFilter" data-no-select2><?php foreach([''=>'documentation_all','ready_to_sign'=>'documentation_ready_to_sign','overdue_draft'=>'documentation_overdue_draft','signed'=>'documentation_signed'] as $value=>$label): ?><option value="<?= $value ?>" <?= $documentationFilter===$value?'selected':'' ?>><?= esc($tr($label)) ?></option><?php endforeach; ?></select></label></div><details class="v4-clinical-summary"><summary><?= esc(lang('V4.recordSummary')) ?></summary><dl><div><dt><?= esc($tr('card_total_examinations')) ?></dt><dd id="totalExaminations">&mdash;</dd></div><div><dt><?= esc($tr('card_completed_today')) ?></dt><dd id="completedToday">&mdash;</dd></div><div><dt><?= esc($tr('card_pending_reviews')) ?></dt><dd id="pendingReviews">&mdash;</dd></div><div><dt><?= esc($tr('card_emergency_cases')) ?></dt><dd id="emergencyCases">&mdash;</dd></div></dl></details><section class="v4-surface v4-ledger"><header class="v4-ledger-header"><h2><?= esc(lang('Examination.examination_management')) ?></h2><div class="v4-record-views"><div class="v4-segments"><?php foreach(['table'=>'UI.appointmentListView','grid'=>'V4.cardsView','calendar'=>'UI.appointmentCalendarView'] as $view=>$label): ?><button type="button" id="<?= $view ?>ViewBtn" onclick="toggleView('<?= $view ?>')" aria-pressed="<?= $view==='table'?'true':'false' ?>"><?= esc(lang($label)) ?></button><?php endforeach; ?></div><details class="v4-action-menu"><summary class="v4-button v4-button--small"><?= esc(lang('V4.exportRecords')) ?></summary><div><button type="button" onclick="exportToPDF()">PDF</button><button type="button" onclick="printExaminations()"><?= esc(lang('Finance.print')) ?></button><button type="button" onclick="exportToCSV()">CSV</button></div></details></div></header><div id="tableView"><div class="v4-ledger-data" data-v4-records="examination" data-v4-table="examinationsTable"><table id="examinationsTable"><thead><tr><th><?= esc($tr('column_examination_id')) ?></th><th><?= esc($tr('column_patient')) ?></th><th><?= esc($tr('column_date_time')) ?></th><th><?= esc($tr('column_type')) ?></th><th><?= esc($tr('column_status')) ?></th><th><?= esc($tr('column_actions')) ?></th></tr></thead><tbody></tbody></table><div class="v4-mobile-records" data-v4-record-cards aria-live="polite"></div>
            </div></div>
        </section>

        <!-- Grid View -->
        <div id="gridView" class="hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php if (!empty($examinations)): ?>
                    <?php foreach ($examinations as $examination): ?>
                        <div class="bg-white rounded-xl border border-gray-200 shadow-lg hover:shadow-xl transition-all duration-300 examination-card" data-examination-id="<?= $examination['id'] ?>">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-amber-600 rounded-full flex items-center justify-center">
                                            <i class="fas fa-stethoscope text-white text-sm"></i>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?= $examination['status'] == 'completed' ? 'bg-green-100 text-green-800' :
                                            ($examination['status'] == 'in_progress' ? 'bg-sky-100 text-sky-800' :
                                            ($examination['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) ?>">
                                        <?= esc($tr('status_' . $examination['status'])) ?>
                                    </span>
                                </div>

                                <div class="mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1"><?= $examination['examination_id'] ?></h3>
                                    <p class="text-sm text-gray-600"><?= $examination['first_name'] . ' ' . $examination['last_name'] ?></p>
                                    <p class="text-xs text-gray-500"><?= $examination['patient_number'] ?></p>
                                </div>

                                <div class="mb-4">
                                    <div class="flex items-center text-sm text-gray-600 mb-2">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        <?= formatDate($examination['examination_date']) ?>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600 mb-2">
                                        <i class="fas fa-clock me-2"></i>
                                        <?= formatTime($examination['examination_date']) ?>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?= $examination['examination_type'] == 'emergency' ? 'bg-red-100 text-red-800' :
                                            ($examination['examination_type'] == 'initial' ? 'bg-yellow-100 text-yellow-800' :
                                            ($examination['examination_type'] == 'periodic' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) ?>">
                                        <?= esc($tr('type_' . $examination['examination_type'])) ?>
                                    </span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <div class="flex gap-2">
                                        <?php if (has_permission('examinations', 'view')): ?>
                                            <a href="<?= base_url('examination/' . $examination['id']) ?>"
                                               class="p-2 text-orange-700 hover:text-orange-800 rounded-lg hover:bg-orange-50 transition-colors" title="<?= esc($tr('action_view_details')) ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (has_permission('examinations', 'edit')): ?>
                                            <a href="<?= base_url('examination/' . $examination['id'] . '/edit') ?>"
                                               class="text-yellow-600 hover:text-yellow-700 p-2 rounded-lg hover:bg-yellow-50 transition-colors" title="<?= esc($tr('edit_examination')) ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (has_permission('examinations', 'view')): ?>
                                            <a href="<?= workflow_context_url('odontogram/' . (int) $examination['patient_id'] . '?examination_id=' . (int) $examination['id'], 'examination/' . (int) $examination['id']) ?>"
                                               class="text-purple-600 hover:text-purple-700 p-2 rounded-lg hover:bg-purple-50 transition-colors" title="<?= esc($tr('action_view_odontogram')) ?>">
                                                <i class="fas fa-tooth"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (($examination['status'] == 'pending' || $examination['status'] == 'in_progress') && has_permission('examinations', 'edit')): ?>
                                        <button onclick="completeExamination(<?= $examination['id'] ?>)"
                                                class="text-green-600 hover:text-green-700 p-2 rounded-lg hover:bg-green-50 transition-colors" title="<?= esc($tr('action_complete_examination')) ?>">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full flex flex-col items-center justify-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-stethoscope text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= esc($tr('no_examinations_found')) ?></h3>
                        <p class="text-gray-500 mb-4"><?= esc($tr('start_creating_new_examination')) ?></p>
                        <?php if (has_permission('examinations', 'create')): ?>
                            <a href="<?= base_url('examination/create') ?>" data-examination-create-modal-open class="inline-flex items-center rounded-xl bg-gradient-to-r from-orange-600 to-amber-600 px-4 py-2 text-sm font-bold text-white shadow-lg transition hover:from-orange-700 hover:to-amber-700 focus:outline-none focus:ring-4 focus:ring-orange-200">
                                <i class="fas fa-plus me-2"></i><?= esc($tr('new_examination')) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Calendar View -->
        <div id="calendarView" class="hidden">
            <div class="bg-white rounded-xl border border-gray-200 shadow-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900"><?= esc($tr('calendar_title')) ?></h3>
                        <div class="flex items-center gap-4">
                            <button onclick="previousMonth()" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span id="currentMonth" class="text-lg font-semibold text-gray-900"><?= formatDate(date('Y-m-d')) ?></span>
                            <button onclick="nextMonth()" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    <div id="calendarGrid" class="grid grid-cols-7 gap-1">
                        <!-- Calendar will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (has_permission('examinations', 'create')): ?>
    <div id="newExaminationModal" data-confirm-discard="<?= esc(lang('V4.discardEntry'),'attr') ?>" class="examination-create-modal v4-entry-modal hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="newExaminationModalTitle" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
        <div class="examination-create-modal__backdrop v4-entry-modal__backdrop" data-examination-create-modal-close></div>
        <div class="examination-create-modal__shell v4-entry-modal__shell" data-examination-create-modal-close>
            <form id="newExaminationQuickCreateForm" action="<?= base_url('examination/store') ?>" method="POST" class="examination-create-modal__panel v4-entry-modal__panel v4-form" data-examination-create-form data-select2-dropdown-parent onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="<?= esc($returnToContext) ?>">
                <input type="hidden" name="source_modal" value="examination_quick_create">

<header class="v4-modal-header"><div><h2 id="newExaminationModalTitle"><?= esc($tr('new_examination')) ?></h2><a href="<?= workflow_context_url('examination/create', $returnToContext) ?>" class="v4-text-link">
                                <?= esc(lang('UI.openFullPage')) ?>
                            </a></div><button type="button" class="v4-icon-button" data-examination-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>"><i class="fas fa-times" aria-hidden="true"></i></button></header>
                <div class="v4-modal-body">
                    <?php if (session('error') && $shouldOpenExaminationCreateModal): ?>
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>

                    <div class="v4-form-section">
                        <?= view('examination/partials/form_fields', [
                            'selected_patient' => null,
                            'validation' => session()->getFlashdata('validation'),
                            'is_rtl' => $isRtl,
                            'id_prefix' => 'examinationQuick',
                        ]) ?>
                    </div>
                </div>

                <div class="v4-modal-footer">
                    <button type="button" data-examination-create-modal-close class="v4-button">
                        <?= esc($tr('cancel')) ?>
                    </button>
                    <button type="submit" name="after_save" value="odontogram" class="v4-button v4-button--primary">
                        <i class="fas fa-tooth me-2"></i>
                        <?= esc($tr('create_and_open_chart')) ?>
                    </button>
                    <button type="submit" class="v4-button v4-button--primary">
                        <i class="fas fa-stethoscope me-2"></i>
                        <?= esc($tr('create_examination')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- Complete Examination Modal -->
<div id="completeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white max-w-lg">
        <div class="p-8">
            <div class="flex items-center justify-center mb-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>

            <div class="text-center mb-6">
                <h3 class="text-xl font-bold text-gray-900 mb-2"><?= esc($tr('complete_examination')) ?></h3>
                <p class="text-gray-600"><?= esc($tr('complete_examination_subtitle')) ?></p>
            </div>

            <form id="completeForm">
                <?= csrf_field() ?>
                <input type="hidden" id="examinationId" name="examination_id">

                <div class="mb-6">
                    <label for="examination_notes" class="block text-sm font-medium text-gray-700 mb-2"><?= esc($tr('final_notes')) ?></label>
                    <textarea id="examination_notes" name="examination_notes"
                              class="w-full rounded-xl border border-slate-300 px-3 py-2 shadow-sm focus:border-orange-400 focus:outline-none focus:ring-4 focus:ring-orange-100" rows="4"
                              placeholder="<?= esc($tr('final_notes_placeholder')) ?>"></textarea>
                </div>

                <div class="flex gap-4">
                    <button type="button" onclick="closeCompleteModal()" class="inline-flex flex-1 items-center justify-center rounded-xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-200 focus:outline-none focus:ring-4 focus:ring-slate-200">
                        <i class="fas fa-times me-2"></i><?= esc($tr('cancel')) ?>
                    </button>
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-xl bg-gradient-to-r from-emerald-600 to-green-600 px-4 py-2 text-sm font-bold text-white shadow-lg transition hover:from-emerald-700 hover:to-green-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">
                        <i class="fas fa-check me-2"></i><?= esc($tr('complete_examination')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DataTables JS -->
<script>
// Global variables
let currentView = 'table';
let currentMonth = new Date();
let examinationsTable;

// Permission data from PHP
const permissions = {
    examinations: {
        view: <?= has_permission('examinations', 'view') ? 'true' : 'false' ?>,
        create: <?= has_permission('examinations', 'create') ? 'true' : 'false' ?>,
        edit: <?= has_permission('examinations', 'edit') ? 'true' : 'false' ?>,
        delete: <?= has_permission('examinations', 'delete') ? 'true' : 'false' ?>
    }
};
const i18n = {
    datatableShow: '<?= esc($tr('datatable_show'), 'js') ?>',
    datatableSearch: '<?= esc($tr('datatable_search'), 'js') ?>',
    errorLoadingStatistics: '<?= esc($tr('error_loading_statistics'), 'js') ?>',
    errorLoadingData: '<?= esc($tr('error_loading_data'), 'js') ?>',
    datatableErrorPrefix: '<?= esc($tr('datatable_error_prefix'), 'js') ?>',
    actionViewDetails: '<?= esc($tr('action_view_details'), 'js') ?>',
    actionEditExamination: '<?= esc($tr('action_edit_examination'), 'js') ?>',
    actionViewOdontogram: '<?= esc($tr('action_view_odontogram'), 'js') ?>',
    actionCompleteExamination: '<?= esc($tr('action_complete_examination'), 'js') ?>',
    actionMoreActions: '<?= esc($tr('action_more_actions'), 'js') ?>',
    actionPrintReport: '<?= esc($tr('action_print_report'), 'js') ?>',
    actionDuplicate: '<?= esc($tr('action_duplicate'), 'js') ?>',
    actionDelete: '<?= esc($tr('action_delete'), 'js') ?>',
    deleteExaminationConfirm: '<?= esc($tr('delete_examination_confirm'), 'js') ?>',
    errorPrefix: '<?= esc($tr('error_prefix'), 'js') ?>',
    datatableLoadingExaminations: '<?= esc($tr('datatable_loading_examinations'), 'js') ?>',
    datatableNoExaminationsFound: '<?= esc($tr('datatable_no_examinations_found'), 'js') ?>',
    datatableInfo: '<?= esc($tr('datatable_info'), 'js') ?>',
    datatableInfoEmpty: '<?= esc($tr('datatable_info_empty'), 'js') ?>',
    datatableInfoFiltered: '<?= esc($tr('datatable_info_filtered'), 'js') ?>',
    datatableSearchPlaceholder: '<?= esc($tr('datatable_search_placeholder'), 'js') ?>',
    first: '<?= esc($tr('first'), 'js') ?>',
    last: '<?= esc($tr('last'), 'js') ?>',
    next: '<?= esc($tr('next'), 'js') ?>',
    previous: '<?= esc($tr('previous'), 'js') ?>',
    examinationsReport: '<?= esc($tr('examinations_report'), 'js') ?>',
    examinationIdLabel: '<?= esc($tr('examination_id_label'), 'js') ?>',
    typeInitial: '<?= esc($tr('type_initial'), 'js') ?>',
    typePeriodic: '<?= esc($tr('type_periodic'), 'js') ?>',
    typeEmergency: '<?= esc($tr('type_emergency'), 'js') ?>',
    typeFollowUp: '<?= esc($tr('type_follow_up'), 'js') ?>',
    statusPending: '<?= esc($tr('status_pending'), 'js') ?>',
    statusInProgress: '<?= esc($tr('status_in_progress'), 'js') ?>',
    statusCompleted: '<?= esc($tr('status_completed'), 'js') ?>',
    statusCancelled: '<?= esc($tr('status_cancelled'), 'js') ?>',
    documentationReadyToSign: '<?= esc($tr('documentation_ready_to_sign'), 'js') ?>',
    documentationOverdueDraft: '<?= esc($tr('documentation_overdue_draft'), 'js') ?>',
    documentationSigned: '<?= esc($tr('documentation_signed'), 'js') ?>',
    calendarDays: [
        '<?= esc($tr('calendar_day_sun'), 'js') ?>',
        '<?= esc($tr('calendar_day_mon'), 'js') ?>',
        '<?= esc($tr('calendar_day_tue'), 'js') ?>',
        '<?= esc($tr('calendar_day_wed'), 'js') ?>',
        '<?= esc($tr('calendar_day_thu'), 'js') ?>',
        '<?= esc($tr('calendar_day_fri'), 'js') ?>',
        '<?= esc($tr('calendar_day_sat'), 'js') ?>'
    ],
    locale: '<?= esc(strtolower(service('request')->getLocale()), 'js') ?>',
};

function getPatientAvatar(gender) {
    const avatarMap = {
        male: { icon: 'fas fa-person', color: 'text-blue-700' },
        female: { icon: 'fas fa-person-dress', color: 'text-rose-700' },
        other: { icon: 'fas fa-user', color: 'text-slate-600' }
    };

    return avatarMap[String(gender || 'other').toLowerCase()] || avatarMap.other;
}

let newExaminationModalTrigger = null;
let newExaminationModalCloseTimer = null;

function mountNewExaminationModal() {
    const modal = document.getElementById('newExaminationModal');
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
    if (modal && modal.dataset.closeControlsBound !== '1') {
        modal.dataset.closeControlsBound = '1';
        modal.querySelectorAll('button[data-examination-create-modal-close], a[data-examination-create-modal-close]').forEach(function(control) {
            control.addEventListener('click', function(event) {
                event.preventDefault();
                closeNewExaminationModal();
            });
        });
    }
    return modal;
}

function initializeNewExaminationModalContent(modal) {
    if (!modal) {
        return Promise.resolve();
    }
    const tasks = [];
    if (typeof window.initAppSelects === 'function') {
        tasks.push(Promise.resolve(window.initAppSelects(modal)));
    }
    if (typeof window.initializeClinicDateInputs === 'function') {
        window.initializeClinicDateInputs(modal);
    }
    if (typeof window.initContextualExaminationForms === 'function') {
        window.initContextualExaminationForms(modal);
    }
    return Promise.allSettled(tasks);
}

function openNewExaminationModal(trigger) {
    const modal = mountNewExaminationModal();
    if (!modal) {
        return;
    }
    newExaminationModalTrigger = trigger || document.activeElement;
    if (typeof applyNewExaminationTriggerContext === 'function') {
        applyNewExaminationTriggerContext(modal, trigger);
    }
    if (window.AppModal) {
        window.AppModal.open(modal, {
            trigger: trigger,
            onOpen: initializeNewExaminationModalContent,
            focusSelector: '[name="patient_id"], input, select, textarea, button'
});
        return;
    }
    if (newExaminationModalCloseTimer) {
        clearTimeout(newExaminationModalCloseTimer);
        newExaminationModalCloseTimer = null;
    }
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.add('app-drawer-lock');
    document.body.classList.add('app-drawer-lock');
    requestAnimationFrame(function () {
        modal.classList.add('is-open');
    });
    window.setTimeout(function () {
        initializeNewExaminationModalContent(modal);
        const firstField = modal.querySelector('[name="patient_id"], input, select, textarea, button');
        if (firstField && typeof firstField.focus === 'function') {
            firstField.focus({ preventScroll: true });
}
    }, 80);
}

        function resetNewExaminationModal(modal) {
    const form = modal ? modal.querySelector('[data-examination-create-form]') : null;
    if (form) {
        form.reset();
        form.querySelectorAll('select, input[type="checkbox"]').forEach(function (field) {
            field.dispatchEvent(new Event('change', { bubbles: true }));
            if (window.jQuery && field.tagName === 'SELECT') {
        window.jQuery(field).trigger('change');
    }
});
        if (typeof clearDynamicExaminationPatient === 'function') {
            clearDynamicExaminationPatient(modal);
}
        if (typeof window.initContextualExaminationForms === 'function') {
            window.initContextualExaminationForms(form);
}
    }
    newExaminationModalTrigger = null;
}

function closeNewExaminationModal() {
    const modal = document.getElementById('newExaminationModal');
    if (!modal || modal.classList.contains('hidden')) {
        return;
    }
    if (window.AppModal) {
        window.AppModal.close(modal, { onReset: resetNewExaminationModal });
        return;
    }
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.documentElement.classList.remove('app-drawer-lock');
    document.body.classList.remove('app-drawer-lock');
    newExaminationModalCloseTimer = setTimeout(function () {
        resetNewExaminationModal(modal);
        modal.classList.add('hidden');
        if (newExaminationModalTrigger && typeof newExaminationModalTrigger.focus === 'function') {
            newExaminationModalTrigger.focus();
}
    }, 240);
}

        // capture-phase examination modal close: form panels stop bubbling clicks.
document.addEventListener('click', function(event) {
    const closeTrigger = event.target.closest('[data-examination-create-modal-close]');
    const isExplicitCloseControl = closeTrigger && closeTrigger.matches('button, a');
    const isBackdropOrShellClick = closeTrigger && event.target === closeTrigger;
    if (isExplicitCloseControl || isBackdropOrShellClick) {
        event.preventDefault();
        event.stopPropagation();
        closeNewExaminationModal();
    }
}, true);
document.addEventListener('click', function(event) {
    const openTrigger = event.target.closest('[data-examination-create-modal-open]');
    if (openTrigger) {
        event.preventDefault();
        openNewExaminationModal(openTrigger);
        return;
    }

    const closeTrigger = event.target.closest('[data-examination-create-modal-close]');
    if (closeTrigger) {
        event.preventDefault();
        closeNewExaminationModal();
    }
});

document.addEventListener('keydown', function(event) {
    if (event.target.closest?.('.select2-search')) return;
        if (event.key !== 'Escape') {
        return;
    }
    const modal = document.getElementById('newExaminationModal');
    if (modal && !modal.classList.contains('hidden')) {
        event.preventDefault();
        closeNewExaminationModal();
    }
});

function applyDataTablesStyling() { /* V4 components own layout and state styling. */ }

// Load examination statistics
function loadExaminationStats() {
    fetch('<?= base_url('examination/getExaminationStats') ?>')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalExaminations').textContent = data.total_examinations || 0;
            document.getElementById('completedToday').textContent = data.today_examinations || 0;
            document.getElementById('pendingReviews').textContent = data.pending_examinations || 0;
            document.getElementById('emergencyCases').textContent = data.emergency_examinations || 0;
        })
        .catch(error => {
            console.error(i18n.errorLoadingStatistics, error);
            document.getElementById('totalExaminations').textContent = '0';
            document.getElementById('completedToday').textContent = '0';
            document.getElementById('pendingReviews').textContent = '0';
            document.getElementById('emergencyCases').textContent = '0';
        });
}

// Initialize DataTables
$(document).ready(function() {
    if (<?= $shouldOpenExaminationCreateModal ? 'true' : 'false' ?>) {
        openNewExaminationModal(document.getElementById('newExaminationModalTrigger'));
    }

    // Load statistics
    loadExaminationStats();

    // Initialize DataTable with server-side processing
    examinationsTable = $('#examinationsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('examination/getExaminationsData') ?>',
            type: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader(window.csrfConfig.header, window.getCsrfToken());
            },
            data: function(d) {
                d[window.csrfConfig.name] = window.getCsrfToken();
                d.documentation_filter = document.getElementById('documentationFilter')?.value || '';
                return d;
            },
            complete: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.csrf_token) {
                    window.refreshCsrfToken(xhr.responseJSON.csrf_token);
                }
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables Ajax error:', {
                    xhr: xhr,
                    error: error,
                    thrown: thrown,
                    responseText: xhr.responseText,
                    status: xhr.status,
                    statusText: xhr.statusText
                });

                if (xhr.responseJSON && xhr.responseJSON.csrf_token) {
                    window.refreshCsrfToken(xhr.responseJSON.csrf_token);
                }

                let errorMessage = i18n.errorLoadingData;
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        errorMessage = response.error;
                    }
                } catch (e) {
                    errorMessage = xhr.responseText || error || thrown;
                }

                alert(i18n.datatableErrorPrefix + ' ' + errorMessage);
            }
        },
        columns: [
            {
                data: 'examination_id',
                name: 'examination_id',
                render: function(data, type, row) {
                    const returnTo = encodeURIComponent((window.location.pathname + window.location.search).replace(/^\/+/, ''));
                    const identityHtml = `
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-100 text-orange-700">
                                <i class="fas fa-stethoscope text-sm"></i>
                            </div>
                            <div>
                                <div class="text-sm font-extrabold text-slate-950">${data}</div>
                                <div class="text-xs font-bold text-slate-500">${i18n.examinationIdLabel}</div>
                            </div>
                        </div>
                    `;
                    if (permissions.examinations.view) {
                        return `
                            <a href="<?= base_url('examination') ?>/${row.id}" class="block cursor-pointer rounded-xl transition-colors duration-200 hover:bg-orange-50/70">
                                ${identityHtml}
                            </a>
                        `;
                    }
                    return identityHtml;
                }
            },
            {
                data: 'patient_name',
                name: 'patient_name',
                render: function(data, type, row) {
                    const avatar = getPatientAvatar(row.gender);
                    return `
                        <div class="flex items-center gap-4">
                            <span class="inline-flex h-7 w-7 flex-shrink-0 items-center justify-center">
                                <i class="${avatar.icon} ${avatar.color} text-2xl"></i>
                            </span>
                            <div>
                                <div class="text-sm font-extrabold text-slate-950">${data}</div>
                                <div class="text-xs font-bold text-slate-500">${row.patient_number}</div>
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: 'examination_date',
                name: 'examination_date',
                render: function(data, type, row) {
                    return `
                        <div class="space-y-1">
                            <div class="text-sm font-extrabold text-slate-950">${row.examination_date_formatted || data}</div>
                            <div class="text-xs font-bold text-slate-500">${row.examination_time_formatted || ''}</div>
                        </div>
                    `;
                }
            },
            {
                data: 'examination_type',
                name: 'examination_type',
                render: function(data, type, row) {
                    const typeConfig = {
                        'emergency': { bg: 'bg-gradient-to-r from-red-100 to-pink-100', text: 'text-red-800', border: 'border-red-200', icon: 'exclamation-triangle' },
                        'initial': { bg: 'bg-gradient-to-r from-yellow-100 to-amber-100', text: 'text-yellow-800', border: 'border-yellow-200', icon: 'play-circle' },
                        'periodic': { bg: 'bg-gradient-to-r from-green-100 to-emerald-100', text: 'text-green-800', border: 'border-green-200', icon: 'calendar-check' },
                        'follow_up': { bg: 'bg-gradient-to-r from-slate-100 to-gray-100', text: 'text-slate-800', border: 'border-slate-200', icon: 'arrow-right' }
                    };
                    const typeLabelMap = {
                        emergency: i18n.typeEmergency,
                        initial: i18n.typeInitial,
                        periodic: i18n.typePeriodic,
                        follow_up: i18n.typeFollowUp,
                    };
                    const config = typeConfig[data] || typeConfig['follow_up'];
                    return `
                        <span class="inline-flex items-center rounded-xl border px-3 py-1.5 text-xs font-extrabold ${config.bg} ${config.text} ${config.border}">
                            <i class="fas fa-${config.icon} me-2"></i>
                            ${typeLabelMap[data] || (typeLabelMap.follow_up)}
                        </span>
                    `;
                }
            },
            {
                data: 'status',
                name: 'status',
                render: function(data, type, row) {
                    const statusConfig = {
                        'completed': { bg: 'bg-gradient-to-r from-green-100 to-emerald-100', text: 'text-green-800', border: 'border-green-200', icon: 'check-circle' },
                        'in_progress': { bg: 'bg-gradient-to-r from-sky-100 to-cyan-100', text: 'text-sky-800', border: 'border-sky-200', icon: 'clock' },
                        'pending': { bg: 'bg-gradient-to-r from-yellow-100 to-amber-100', text: 'text-yellow-800', border: 'border-yellow-200', icon: 'hourglass-half' },
                        'cancelled': { bg: 'bg-gradient-to-r from-red-100 to-pink-100', text: 'text-red-800', border: 'border-red-200', icon: 'times-circle' }
                    };
                    const statusLabelMap = {
                        pending: i18n.statusPending,
                        in_progress: i18n.statusInProgress,
                        completed: i18n.statusCompleted,
                        cancelled: i18n.statusCancelled,
                    };
                    const config = statusConfig[data] || statusConfig['pending'];
                    const documentationBadge = row.documentation_state === 'signed'
                        ? `<span class="mt-1 inline-flex rounded-lg bg-emerald-100 px-2 py-1 text-[11px] font-black text-emerald-800">${i18n.documentationSigned}</span>`
                        : (row.documentation_state === 'ready_to_sign'
                            ? `<span class="mt-1 inline-flex rounded-lg bg-indigo-100 px-2 py-1 text-[11px] font-black text-indigo-800">${i18n.documentationReadyToSign}</span>`
                            : (row.documentation_state === 'overdue_draft'
                                ? `<span class="mt-1 inline-flex rounded-lg bg-amber-100 px-2 py-1 text-[11px] font-black text-amber-900">${i18n.documentationOverdueDraft}</span>`
                                : ''));
                    return `
                        <span class="inline-flex items-center rounded-xl border px-3 py-1.5 text-xs font-extrabold ${config.bg} ${config.text} ${config.border}">
                            <i class="fas fa-${config.icon} me-2"></i>
                            ${statusLabelMap[data] || statusLabelMap.pending}
                        </span>
                        ${documentationBadge}
                    `;
                }
            },
            {
                data: 'id',
                name: 'id',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    const returnTo = encodeURIComponent((window.location.pathname + window.location.search).replace(/^\/+/, ''));
                    let actions = '<div class="flex items-center gap-2">';

                    // View button
                    if (permissions.examinations.view) {
                        actions += `
                            <a href="<?= base_url('examination') ?>/${data}"
                               class="rounded-xl p-2 text-orange-700 transition-all duration-200 hover:scale-105 hover:bg-orange-50 hover:text-orange-800" title="${i18n.actionViewDetails}">
                                <i class="fas fa-eye"></i>
                            </a>`;
                    }

                    // Edit button
                    if (permissions.examinations.edit) {
                        actions += `
                            <a href="<?= base_url('examination') ?>/${data}/edit"
                               class="group/action relative rounded-xl p-2 text-sky-700 transition-all duration-300 hover:scale-110 hover:bg-sky-50 hover:text-sky-800" title="${i18n.actionEditExamination}">
                                <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-sky-500/10 to-cyan-500/10 opacity-0 blur transition-opacity duration-300 group-hover/action:opacity-100"></div>
                                <i class="fas fa-edit relative z-10"></i>
                            </a>`;
                    }

                    // Odontogram button
                    if (permissions.examinations.view) {
                        actions += `
                            <a href="<?= base_url('odontogram') ?>/${row.patient_id}"
                               class="group/action relative rounded-xl p-2 text-purple-700 transition-all duration-300 hover:scale-110 hover:bg-purple-50 hover:text-purple-800" title="${i18n.actionViewOdontogram}">
                                <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-purple-500/10 to-pink-500/10 opacity-0 blur transition-opacity duration-300 group-hover/action:opacity-100"></div>
                                <i class="fas fa-tooth relative z-10"></i>
                            </a>`;
                    }

                    // Complete button
                    if ((row.status === 'pending' || row.status === 'in_progress') && permissions.examinations.edit) {
                        actions += `
                            <button onclick="completeExamination(${data})"
                                    class="group/action relative rounded-xl p-2 text-emerald-700 transition-all duration-300 hover:scale-110 hover:bg-emerald-50 hover:text-emerald-800" title="${i18n.actionCompleteExamination}">
                                <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-emerald-500/10 to-green-500/10 opacity-0 blur transition-opacity duration-300 group-hover/action:opacity-100"></div>
                                <i class="fas fa-check relative z-10"></i>
                            </button>`;
                    }

                    // More actions dropdown
                    actions += `
                        <div class="relative">
                            <button onclick="toggleDropdown(${data})" class="rounded-xl p-2 text-slate-600 transition-all duration-300 hover:scale-110 hover:bg-slate-100 hover:text-slate-900" title="${i18n.actionMoreActions}">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div id="dropdown-${data}" class="absolute end-0 z-10 mt-2 hidden w-48 rounded-xl border border-slate-200 bg-white shadow-2xl">
                                <div class="py-2">`;

                    // Print button (always available if view permission)
                    if (permissions.examinations.view) {
                        actions += `
                            <a href="<?= base_url('examination') ?>/${data}/print" class="flex items-center px-4 py-2 text-sm font-bold text-slate-700 transition-colors duration-200 hover:bg-slate-50 hover:text-slate-950">
                                        <i class="fas fa-print me-3 text-slate-500"></i>${i18n.actionPrintReport}
                                    </a>`;
                    }

                    // Duplicate button (requires create permission)
                    if (permissions.examinations.create) {
                        actions += `
                                    <a href="<?= base_url('examination') ?>/${data}/duplicate" class="flex items-center px-4 py-2 text-sm font-bold text-slate-700 transition-colors duration-200 hover:bg-orange-50 hover:text-orange-800">
                                        <i class="fas fa-copy me-3 text-orange-600"></i>${i18n.actionDuplicate}
                                    </a>`;
                    }

                    // Delete button
                    if (permissions.examinations.delete) {
                        actions += `
                                    <button onclick="deleteExamination(${data})" class="flex w-full items-center px-4 py-2 text-sm font-bold text-red-700 transition-colors duration-200 hover:bg-red-50">
                                        <i class="fas fa-trash me-3 text-red-600"></i>${i18n.actionDelete}
                                    </button>`;
                    }

                    actions += `
                                </div>
                            </div>
                        </div>
                    </div>`;

                    return actions;
                }
            }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            processing: i18n.datatableLoadingExaminations,
            lengthMenu: "_MENU_",
            zeroRecords: i18n.datatableNoExaminationsFound,
            info: i18n.datatableInfo,
            infoEmpty: i18n.datatableInfoEmpty,
            infoFiltered: i18n.datatableInfoFiltered,
            search: "",
            searchPlaceholder: i18n.datatableSearchPlaceholder,
            paginate: {
                first: i18n.first,
                last: i18n.last,
                next: i18n.next,
                previous: i18n.previous
            }
        },
        dom: '<"v4-ledger-tools"lf>rt<"v4-ledger-footer"ip>B',
        buttons: [
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf mr-2"></i><?= esc($tr('pdf'), 'js') ?>',
                className: 'btn-pdf',
                title: i18n.examinationsReport,
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Adjust column indices as needed
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print mr-2"></i><?= esc($tr('print'), 'js') ?>',
                className: 'btn-print',
                title: i18n.examinationsReport,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Adjust column indices as needed
                }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv mr-2"></i><?= esc($tr('csv'), 'js') ?>',
                className: 'btn-csv',
                title: i18n.examinationsReport,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Adjust column indices as needed
                }
            }
        ],
        initComplete: function() {
            setTimeout(applyDataTablesStyling, 100);
        },
        drawCallback: function() {
            setTimeout(applyDataTablesStyling, 50);
            if (permissions.examinations.view) {
                $('#examinationsTable tbody tr').addClass('cursor-pointer hover:bg-orange-50/40 transition-colors duration-150');
            }
        }
    });

    document.getElementById('documentationFilter')?.addEventListener('change', function() {
        const url = new URL(window.location.href);
        if (this.value) url.searchParams.set('documentation', this.value);
        else url.searchParams.delete('documentation');
        window.history.replaceState({}, '', url);
        examinationsTable.ajax.reload();
    });

    if (permissions.examinations.view) {
        $('#examinationsTable tbody').on('click', 'tr', function(e) {
            if ($(e.target).closest('a, button, input, select, textarea, [onclick]').length) {
                return;
            }

            const rowData = examinationsTable.row(this).data();
            if (!rowData || !rowData.id) {
                return;
            }

            window.location.href = window.workflowContextUrl(`<?= base_url('examination') ?>/${rowData.id}`);
        });
    }

    // Set up mutation observer to watch for DataTables DOM changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                // Check if DataTables elements were added
                if (mutation.target.querySelector &&
                    (mutation.target.querySelector('.dataTables_length') ||
                     mutation.target.querySelector('.dataTables_filter') ||
                     mutation.target.querySelector('.dataTables_paginate'))) {
                    setTimeout(applyDataTablesStyling, 50);
                }
            }
        });
    });

    // Start observing
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});

// View Toggle Functions
function toggleView(view) {
    if(!['table','grid','calendar'].includes(view))return;
    for(const key of ['table','grid','calendar']){document.getElementById(key+'View')?.classList.toggle('hidden',key!==view);document.getElementById(key+'ViewBtn')?.setAttribute('aria-pressed',String(key===view));}
    if(view==='calendar')generateCalendar();currentView=view;
}

// Calendar Functions
function generateCalendar() {
    const calendarGrid = document.getElementById('calendarGrid');
    const year = currentMonth.getFullYear();
    const month = currentMonth.getMonth();

    // Update month display
    document.getElementById('currentMonth').textContent = currentMonth.toLocaleDateString(i18n.locale, { month: 'long', year: 'numeric' });

    // Clear calendar
    calendarGrid.replaceChildren();

    // Add day headers
    i18n.calendarDays.forEach(day => {
        const dayHeader = document.createElement('div');
        dayHeader.className = 'p-2 text-center text-sm font-semibold text-gray-600 bg-gray-50';
        dayHeader.textContent = day;
        calendarGrid.appendChild(dayHeader);
    });

    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();

    // Add empty cells for days before month starts
    for (let i = 0; i < startingDayOfWeek; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'h-20 p-2 border border-gray-200 bg-gray-50';
        calendarGrid.appendChild(emptyCell);
    }

    // Add days of month
    for (let day = 1; day <= daysInMonth; day++) {
        const dayCell = document.createElement('div');
        dayCell.className = 'h-20 p-2 border border-gray-200 hover:bg-gray-50 cursor-pointer';

        const dayNum = document.createElement('div');
        dayNum.className = 'text-sm font-medium text-gray-900';
        dayNum.textContent = day;

        const dayContent = document.createElement('div');
        dayContent.className = 'mt-1 space-y-1';

        dayCell.appendChild(dayNum);
        dayCell.appendChild(dayContent);
        calendarGrid.appendChild(dayCell);
    }
}

function previousMonth() {
    currentMonth.setMonth(currentMonth.getMonth() - 1);
    generateCalendar();
}

function nextMonth() {
    currentMonth.setMonth(currentMonth.getMonth() + 1);
    generateCalendar();
}

// Export Functions
function exportToPDF() {
    if (examinationsTable) {
        examinationsTable.button('.btn-pdf').trigger();
    }
}

function printExaminations() {
    if (examinationsTable) {
        examinationsTable.button('.btn-print').trigger();
    }
}

function exportToCSV() {
    if (examinationsTable) {
        examinationsTable.button('.btn-csv').trigger();
    }
}

// Examination Actions
function completeExamination(examinationId) {
    document.getElementById('examinationId').value = examinationId;
    const modal = document.getElementById('completeModal');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
}

function deleteExamination(examinationId) {
    if (confirm(i18n.deleteExaminationConfirm)) {
        fetch(`<?= base_url('examination') ?>/${examinationId}/delete`, {
            method: 'DELETE',
            headers: {
                [window.csrfConfig.header]: window.getCsrfToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }

            if (data.success) {
                examinationsTable.ajax.reload();
            } else {
                alert(i18n.errorPrefix + ' ' + data.message);
            }
        });
    }
}

function closeCompleteModal() {
    const modal = document.getElementById('completeModal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
    document.getElementById('completeForm').reset();
}

// Handle form submission
document.getElementById('completeForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const examinationId = formData.get('examination_id');

    fetch(`<?= base_url('examination') ?>/${examinationId}/complete`, {
        method: 'POST',
        headers: {
            [window.csrfConfig.header]: window.getCsrfToken()
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.csrf_token) {
            window.refreshCsrfToken(data.csrf_token);
        }

        if (data.success) {
            closeCompleteModal();
            examinationsTable.ajax.reload();
        } else {
            alert(i18n.errorPrefix + ' ' + data.message);
        }
    });
});

// Close modal when clicking outside
document.getElementById('completeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCompleteModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCompleteModal();
    }
});

// Dropdown toggle function
function toggleDropdown(examinationId) {
    const dropdown = document.getElementById(`dropdown-${examinationId}`);
    if (!dropdown) return;

    // Close all other dropdowns first
    document.querySelectorAll('[id^="dropdown-"]').forEach(dropdown => {
        if (dropdown.id !== `dropdown-${examinationId}`) {
            dropdown.classList.add('hidden');
        }
    });

    // Toggle current dropdown
    dropdown.classList.toggle('hidden');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    // Check if click is outside any dropdown
    if (!e.target.closest('[id^="dropdown-"]') && !e.target.closest('button[onclick*="toggleDropdown"]')) {
        document.querySelectorAll('[id^="dropdown-"]').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    }
});
</script>
<?= $this->endSection() ?>
