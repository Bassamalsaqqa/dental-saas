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

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

<!-- Custom DataTables Styling -->


<div class="denta-examination-workspace examinations-page min-h-screen v3-surface" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="w-full px-3 py-4 sm:px-5 sm:py-5">
        <section class="v3-command-section mb-5 overflow-hidden v3-corner">
            <div class="grid gap-5 p-5 sm:p-6 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center v3-corner v3-surface v3-text-on-primary border v3-border-default">
                        <i class="fas fa-stethoscope text-xl"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[0.22em] v3-text-warning"><?= esc($tr('examinations_registry')) ?></p>
                        <h1 class="mt-1 text-2xl font-black leading-tight v3-text-on-primary sm:text-3xl"><?= esc($tr('examination_management')) ?></h1>
                        <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 v3-text-warning"><?= esc($tr('examination_management_subtitle')) ?></p>
                    </div>
                </div>
                <?php if (has_permission('examinations', 'create')): ?>
                    <a href="<?= base_url('examination/create') ?>" id="newExaminationModalTrigger" data-examination-create-modal-open class="inline-flex items-center justify-center gap-2 v3-radius v3-surface px-5 py-3 text-sm font-black v3-text-warning transition v3-hover-primary focus:outline-none focus:ring-4 v3-focus-control v3-btn v3-btn--secondary">
                        <i class="fas fa-plus"></i>
                        <?= esc($tr('new_examination')) ?>
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <section class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <article class="v3-metric v3-corner p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide v3-text-warning"><?= esc($tr('card_total_examinations')) ?></p>
                        <p class="mt-2 v3-title-scale font-black" id="totalExaminations"><i class="fas fa-spinner fa-spin text-lg"></i></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center v3-corner v3-surface border v3-border-default"><i class="fas fa-stethoscope"></i></span>
                </div>
            </article>
            <article class="v3-metric v3-corner p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide v3-text-primary"><?= esc($tr('card_completed_today')) ?></p>
                        <p class="mt-2 v3-title-scale font-black" id="completedToday"><i class="fas fa-spinner fa-spin text-lg"></i></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center v3-corner v3-surface border v3-border-default"><i class="fas fa-check-circle"></i></span>
                </div>
            </article>
            <article class="v3-metric v3-corner p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide v3-text-warning"><?= esc($tr('card_pending_reviews')) ?></p>
                        <p class="mt-2 v3-title-scale font-black" id="pendingReviews"><i class="fas fa-spinner fa-spin text-lg"></i></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center v3-corner v3-surface border v3-border-default"><i class="fas fa-clock"></i></span>
                </div>
            </article>
            <article class="v3-metric v3-corner p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide v3-text-danger"><?= esc($tr('card_emergency_cases')) ?></p>
                        <p class="mt-2 v3-title-scale font-black" id="emergencyCases"><i class="fas fa-spinner fa-spin text-lg"></i></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center v3-corner v3-surface border v3-border-default"><i class="fas fa-triangle-exclamation"></i></span>
                </div>
            </article>
        </section>

        <section class="v3-command-section mb-5 v3-corner p-4 sm:p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-11 w-11 items-center justify-center v3-corner v3-surface border v3-border-default"><i class="fas fa-file-signature"></i></span>
                    <div>
                        <h2 class="text-lg font-black"><?= esc($tr('documentation_queue')) ?></h2>
                        <p class="mt-1 text-sm font-semibold v3-accent-text"><?= esc($tr('documentation_queue_help')) ?></p>
                    </div>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="flex gap-2">
                        <a href="<?= base_url('examination?documentation=ready_to_sign') ?>" class="v3-radius v3-surface px-3 py-2 text-sm font-black border v3-border-default v3-hover-soft"><span dir="ltr"><?= $readyToSignCount ?></span> <?= esc($tr('documentation_ready_to_sign')) ?></a>
                        <a href="<?= base_url('examination?documentation=overdue_draft') ?>" class="v3-radius v3-bg-warning px-3 py-2 text-sm font-black v3-text-warning v3-hover-primary"><span dir="ltr"><?= $overdueDraftCount ?></span> <?= esc($tr('documentation_overdue_draft')) ?></a>
                    </div>
                    <select id="documentationFilter" class="min-w-[12rem] v3-radius border v3-border v3-surface px-3 py-2.5 text-sm font-black v3-text-ink outline-none focus:ring-4 v3-focus-control v3-select">
                        <option value="" <?= $documentationFilter === '' ? 'selected' : '' ?>><?= esc($tr('documentation_all')) ?></option>
                        <option value="ready_to_sign" <?= $documentationFilter === 'ready_to_sign' ? 'selected' : '' ?>><?= esc($tr('documentation_ready_to_sign')) ?></option>
                        <option value="overdue_draft" <?= $documentationFilter === 'overdue_draft' ? 'selected' : '' ?>><?= esc($tr('documentation_overdue_draft')) ?></option>
                        <option value="signed" <?= $documentationFilter === 'signed' ? 'selected' : '' ?>><?= esc($tr('documentation_signed')) ?></option>
                    </select>
                </div>
            </div>
        </section>

        <section id="tableView" class="overflow-hidden v3-corner border v3-border v3-surface">
            <div class="v3-surface-heading border-b v3-border px-4 py-4 sm:px-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center v3-corner v3-bg-waiting v3-text-warning border v3-border-default">
                            <i class="fas fa-list"></i>
                        </span>
                        <div>
                            <h3 class="text-lg font-black v3-text-ink"><?= esc($tr('examinations_registry')) ?></h3>
                            <p class="text-sm font-semibold v3-text-muted"><?= esc($tr('examination_management_subtitle')) ?></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button onclick="exportToPDF()" class="inline-flex items-center gap-2 v3-radius border v3-border-default v3-bg-alert px-3 py-2 text-xs font-black v3-text-danger transition v3-hover-danger v3-btn v3-btn--danger">
                            <i class="fas fa-file-pdf"></i>
                            <?= esc($tr('pdf')) ?>
                        </button>
                        <button onclick="printExaminations()" class="inline-flex items-center gap-2 v3-radius border v3-border v3-surface px-3 py-2 text-xs font-black v3-text-ink transition v3-hover-soft v3-btn v3-btn--secondary">
                            <i class="fas fa-print"></i>
                            <?= esc($tr('print')) ?>
                        </button>
                        <button onclick="exportToCSV()" class="inline-flex items-center gap-2 v3-radius border v3-border-default v3-bg-paid px-3 py-2 text-xs font-black v3-text-primary transition v3-hover-primary v3-btn v3-btn--secondary">
                            <i class="fas fa-file-csv"></i>
                            <?= esc($tr('csv')) ?>
                        </button>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table id="examinationsTable" class="w-full min-w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-stethoscope v3-text-warning"></i>
                                    <span><?= esc($tr('column_examination_id')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user v3-text-warning"></i>
                                    <span><?= esc($tr('column_patient')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar-alt v3-text-warning"></i>
                                    <span><?= esc($tr('column_date_time')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-tag v3-text-warning"></i>
                                    <span><?= esc($tr('column_type')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-info-circle v3-text-warning"></i>
                                    <span><?= esc($tr('column_status')) ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-cogs v3-text-warning"></i>
                                    <span><?= esc($tr('column_actions')) ?></span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="v3-surface divide-y v3-border-default">
                        <!-- Data will be loaded via DataTables -->
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Grid View -->
        <div id="gridView" class="hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php if (!empty($examinations)): ?>
                    <?php foreach ($examinations as $examination): ?>
                        <div class="v3-surface v3-radius border v3-border transition-all duration-300 examination-card" data-examination-id="<?= $examination['id'] ?>">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="v3-icon w-10 h-10 rounded-full flex items-center justify-center">
                                            <i class="fas fa-stethoscope v3-text-on-primary text-sm"></i>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?= $examination['status'] == 'completed' ? 'v3-bg-paid v3-text-primary' :
                                            ($examination['status'] == 'in_progress' ? 'v3-bg-scheduled v3-text-primary' :
                                            ($examination['status'] == 'pending' ? 'v3-bg-waiting v3-text-warning' : 'v3-bg-alert v3-text-danger')) ?>">
                                        <?= esc($tr('status_' . $examination['status'])) ?>
                                    </span>
                                </div>

                                <div class="mb-4">
                                    <h3 class="text-lg font-semibold v3-text-ink mb-1"><?= $examination['examination_id'] ?></h3>
                                    <p class="text-sm v3-text-muted"><?= $examination['first_name'] . ' ' . $examination['last_name'] ?></p>
                                    <p class="text-xs v3-text-muted"><?= $examination['patient_number'] ?></p>
                                </div>

                                <div class="mb-4">
                                    <div class="flex items-center text-sm v3-text-muted mb-2">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        <?= formatDate($examination['examination_date']) ?>
                                    </div>
                                    <div class="flex items-center text-sm v3-text-muted mb-2">
                                        <i class="fas fa-clock me-2"></i>
                                        <?= formatTime($examination['examination_date']) ?>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?= $examination['examination_type'] == 'emergency' ? 'v3-bg-alert v3-text-danger' :
                                            ($examination['examination_type'] == 'initial' ? 'v3-bg-waiting v3-text-warning' :
                                            ($examination['examination_type'] == 'periodic' ? 'v3-bg-paid v3-text-primary' : 'v3-bg-soft v3-text-ink')) ?>">
                                        <?= esc($tr('type_' . $examination['examination_type'])) ?>
                                    </span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <div class="flex gap-2">
                                        <?php if (has_permission('examinations', 'view')): ?>
                                            <a href="<?= base_url('examination/' . $examination['id']) ?>"
                                               class="p-2 v3-text-warning v3-hover-text-warning rounded-lg v3-hover-primary transition-colors" title="<?= esc($tr('action_view_details')) ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (has_permission('examinations', 'edit')): ?>
                                            <a href="<?= base_url('examination/' . $examination['id'] . '/edit') ?>"
                                               class="v3-text-warning v3-hover-text-warning p-2 rounded-lg v3-hover-primary transition-colors" title="<?= esc($tr('edit_examination')) ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (has_permission('examinations', 'view')): ?>
                                            <a href="<?= workflow_context_url('odontogram/' . (int) $examination['patient_id'] . '?examination_id=' . (int) $examination['id'], 'examination/' . (int) $examination['id']) ?>"
                                               class="v3-accent-text v3-hover-text-chair p-2 rounded-lg v3-hover-primary transition-colors" title="<?= esc($tr('action_view_odontogram')) ?>">
                                                <i class="fas fa-tooth"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (($examination['status'] == 'pending' || $examination['status'] == 'in_progress') && has_permission('examinations', 'edit')): ?>
                                        <button onclick="completeExamination(<?= $examination['id'] ?>)"
                                                class="v3-text-primary v3-hover-text-primary p-2 rounded-lg v3-hover-primary transition-colors v3-btn v3-btn--secondary" title="<?= esc($tr('action_complete_examination')) ?>">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full flex flex-col items-center justify-center py-12">
                        <div class="w-16 h-16 v3-bg-soft rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-stethoscope v3-text-muted text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold v3-text-ink mb-2"><?= esc($tr('no_examinations_found')) ?></h3>
                        <p class="v3-text-muted mb-4"><?= esc($tr('start_creating_new_examination')) ?></p>
                        <?php if (has_permission('examinations', 'create')): ?>
                            <a href="<?= base_url('examination/create') ?>" data-examination-create-modal-open class="inline-flex items-center v3-radius px-4 py-2 text-sm font-bold v3-text-on-primary transition focus:outline-none focus:ring-4 v3-focus-control v3-btn v3-btn--secondary">
                                <i class="fas fa-plus me-2"></i><?= esc($tr('new_examination')) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Calendar View -->
        <div id="calendarView" class="hidden">
            <div class="v3-surface v3-radius border v3-border">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold v3-text-ink"><?= esc($tr('calendar_title')) ?></h3>
                        <div class="flex items-center gap-4">
                            <button onclick="previousMonth()" class="p-2 v3-text-muted v3-hover-text-ink v3-hover-soft rounded-lg v3-btn v3-btn--secondary">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span id="currentMonth" class="text-lg font-semibold v3-text-ink"><?= formatDate(date('Y-m-d')) ?></span>
                            <button onclick="nextMonth()" class="p-2 v3-text-muted v3-hover-text-ink v3-hover-soft rounded-lg v3-btn v3-btn--secondary">
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
    <div id="newExaminationModal" class="examination-create-modal v3-context-drawer fixed inset-0 z-[2147483600] hidden overflow-hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="newExaminationModalTitle" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
        <div class="examination-create-modal__backdrop v3-context-drawer__backdrop fixed inset-0 v3-backdrop" data-examination-create-modal-close></div>
        <div class="examination-create-modal__shell v3-context-drawer__shell relative z-10 flex min-h-screen items-center justify-center p-4" data-examination-create-modal-close>
            <form id="newExaminationQuickCreateForm" action="<?= base_url('examination/store') ?>" method="POST" class="examination-create-modal__panel v3-context-drawer__panel flex w-full max-w-xl flex-col overflow-hidden v3-radius border v3-border v3-surface" data-examination-create-form data-select2-dropdown-parent onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="<?= esc($returnToContext) ?>">
                <input type="hidden" name="source_modal" value="examination_quick_create">

                <div class="flex flex-shrink-0 items-start justify-between gap-4 border-b v3-border v3-surface px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="v3-icon inline-flex h-11 w-11 items-center justify-center v3-corner">
                            <i class="fas fa-stethoscope"></i>
                        </span>
                        <div>
                            <h2 id="newExaminationModalTitle" class="text-lg font-bold v3-text-ink"><?= esc($tr('new_examination')) ?></h2>
                            <a href="<?= workflow_context_url('examination/create', $returnToContext) ?>" class="mt-1 inline-flex text-xs font-semibold v3-text-warning v3-hover-text-warning">
                                <?= esc(lang('UI.openFullPage')) ?>
                            </a>
                        </div>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full v3-text-muted transition v3-hover-soft v3-hover-text-ink v3-btn v3-btn--icon" data-examination-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="min-h-0 flex-1 space-y-5 overflow-y-auto v3-surface p-5">
                    <?php if (session('error') && $shouldOpenExaminationCreateModal): ?>
                        <div class="v3-radius border v3-border-default v3-bg-alert p-4 text-sm v3-text-danger">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>

                    <div class="v3-corner border v3-border v3-surface p-5 md:p-6">
                        <?= view('examination/partials/form_fields', [
                            'selected_patient' => null,
                            'validation' => session()->getFlashdata('validation'),
                            'is_rtl' => $isRtl,
                            'id_prefix' => 'examinationQuick',
                        ]) ?>
                    </div>
                </div>

                <div class="flex flex-shrink-0 flex-col-reverse gap-3 border-t v3-border v3-surface px-5 py-4 sm:flex-row sm:justify-end">
                    <button type="button" data-examination-create-modal-close class="inline-flex items-center justify-center v3-radius border v3-border px-5 py-2.5 text-sm font-semibold v3-text-ink transition v3-hover-soft v3-btn v3-btn--secondary">
                        <?= esc($tr('cancel')) ?>
                    </button>
                    <button type="submit" name="after_save" value="odontogram" class="inline-flex items-center justify-center v3-radius border v3-border v3-soft-surface px-5 py-2.5 text-sm font-bold v3-accent-text transition v3-hover-primary v3-btn v3-btn--primary">
                        <i class="fas fa-tooth me-2"></i>
                        <?= esc($tr('create_and_open_chart')) ?>
                    </button>
                    <button type="submit" class="v3-action v3-action-primary inline-flex items-center justify-center v3-radius px-5 py-2.5 text-sm font-bold transition v3-btn v3-btn--primary">
                        <i class="fas fa-stethoscope me-2"></i>
                        <?= esc($tr('create_examination')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- Complete Examination Modal -->
<div id="completeModal" class="fixed inset-0 v3-bg-canvas bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 rounded-md v3-surface max-w-lg">
        <div class="p-8">
            <div class="flex items-center justify-center mb-6">
                <div class="w-16 h-16 v3-bg-paid rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle v3-text-primary text-2xl"></i>
                </div>
            </div>

            <div class="text-center mb-6">
                <h3 class="text-xl font-bold v3-text-ink mb-2"><?= esc($tr('complete_examination')) ?></h3>
                <p class="v3-text-muted"><?= esc($tr('complete_examination_subtitle')) ?></p>
            </div>

            <form id="completeForm">
                <?= csrf_field() ?>
                <input type="hidden" id="examinationId" name="examination_id">

                <div class="mb-6">
                    <label for="examination_notes" class="block text-sm font-medium v3-text-ink mb-2"><?= esc($tr('final_notes')) ?></label>
                    <textarea id="examination_notes" name="examination_notes"
                              class="w-full v3-radius border v3-border px-3 py-2 v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-textarea" rows="4"
                              placeholder="<?= esc($tr('final_notes_placeholder')) ?>"></textarea>
                </div>

                <div class="flex gap-4">
                    <button type="button" onclick="closeCompleteModal()" class="inline-flex flex-1 items-center justify-center v3-radius v3-bg-soft px-4 py-2 text-sm font-bold v3-text-ink transition v3-hover-soft focus:outline-none focus:ring-4 v3-focus-control v3-btn v3-btn--secondary">
                        <i class="fas fa-times me-2"></i><?= esc($tr('cancel')) ?>
                    </button>
                    <button type="submit" class="v3-action v3-action-primary inline-flex flex-1 items-center justify-center v3-radius px-4 py-2 text-sm font-bold transition focus:outline-none focus:ring-4 v3-focus-control v3-btn v3-btn--primary">
                        <i class="fas fa-check me-2"></i><?= esc($tr('complete_examination')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

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
        male: { icon: 'fas fa-person', color: 'v3-text-primary' },
        female: { icon: 'fas fa-person-dress', color: 'v3-text-danger' },
        other: { icon: 'fas fa-user', color: 'v3-text-muted' }
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
    if (event.key !== 'Escape') {
        return;
    }
    const modal = document.getElementById('newExaminationModal');
    if (modal && !modal.classList.contains('hidden')) {
        event.preventDefault();
        closeNewExaminationModal();
    }
});

function applyDataTablesStyling() {
    $('.dataTables_length select').removeClass().addClass('v3-radius border v3-border-default v3-bg-panel px-3 py-2 text-sm font-bold v3-text-ink v3-elevation-none outline-none transition v3-focus-control focus:ring-4 v3-focus-control');
    $('.dataTables_filter input').removeClass().addClass('v3-radius border v3-border-default v3-bg-panel px-3 py-2 text-sm font-bold v3-text-ink v3-elevation-none outline-none transition placeholder:v3-text-muted v3-focus-control focus:ring-4 v3-focus-control');
    $('.dataTables_info').removeClass().addClass('flex items-center text-sm font-bold v3-text-muted');
    $('.dataTables_paginate .paginate_button').removeClass().addClass('mx-1 v3-radius border v3-border-default v3-bg-panel px-3 py-2 text-sm font-extrabold v3-text-ink v3-elevation-none transition v3-border-default v3-hover-primary v3-hover-text-warning focus:outline-none focus:ring-4 v3-focus-control');
    $('.dataTables_paginate .paginate_button.current').addClass('!v3-border-default !v3-bg-warning !v3-text-on-primary');
    $('.dataTables_paginate .paginate_button.disabled').addClass('cursor-not-allowed opacity-45 hover:!v3-border-default hover:!v3-bg-panel hover:!v3-text-muted');
    $('.dataTables_processing').removeClass().addClass('v3-radius border-0 v3-bg-warning px-4 py-3 text-sm font-extrabold v3-text-on-primary v3-elevation-none');

    $('.dataTables_wrapper').addClass('relative');
    $('.dataTables_length').addClass('order-1 flex items-center gap-3');
    $('.dataTables_filter').addClass('order-2 ms-auto flex items-center gap-3');
    $('.dataTables_info').addClass('flex items-center order-1');
    $('.dataTables_paginate').addClass('order-2 ms-auto flex items-center gap-2');

    $('.dataTables_wrapper > .row:first-child').addClass('flex items-center justify-between');
    $('.dataTables_wrapper > .row:last-child').addClass('flex items-center justify-between');

    if ($('.dataTables_length label').length === 0) {
        $('.dataTables_length').prepend(`<label class="me-3 flex items-center text-sm font-extrabold v3-text-ink"><i class="fas fa-list-ul me-2 v3-text-warning"></i>${i18n.datatableShow}</label>`);
    }
    if ($('.dataTables_filter label').length === 0) {
        $('.dataTables_filter').prepend(`<label class="me-3 flex items-center text-sm font-extrabold v3-text-ink"><i class="fas fa-search me-2 v3-text-warning"></i>${i18n.datatableSearch}</label>`);
    }

    // Add icons to pagination buttons using safe DOM construction
    const createIcon = (cls) => {
        const icon = document.createElement('i');
        icon.className = cls;
        return icon;
    };

    $('.dataTables_paginate .paginate_button.first').each(function() { this.replaceChildren(createIcon('fas fa-angle-double-left')); });
    $('.dataTables_paginate .paginate_button.previous').each(function() { this.replaceChildren(createIcon('fas fa-angle-left')); });
    $('.dataTables_paginate .paginate_button.next').each(function() { this.replaceChildren(createIcon('fas fa-angle-right')); });
    $('.dataTables_paginate .paginate_button.last').each(function() { this.replaceChildren(createIcon('fas fa-angle-double-right')); });
}

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
                            <div class="flex h-10 w-10 items-center justify-center v3-radius v3-bg-waiting v3-text-warning">
                                <i class="fas fa-stethoscope text-sm"></i>
                            </div>
                            <div>
                                <div class="text-sm font-extrabold v3-text-ink">${data}</div>
                                <div class="text-xs font-bold v3-text-muted">${i18n.examinationIdLabel}</div>
                            </div>
                        </div>
                    `;
                    if (permissions.examinations.view) {
                        return `
                            <a href="<?= base_url('examination') ?>/${row.id}" class="block cursor-pointer v3-radius transition-colors duration-200 v3-hover-primary">
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
                                <div class="text-sm font-extrabold v3-text-ink">${data}</div>
                                <div class="text-xs font-bold v3-text-muted">${row.patient_number}</div>
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
                            <div class="text-sm font-extrabold v3-text-ink">${row.examination_date_formatted || data}</div>
                            <div class="text-xs font-bold v3-text-muted">${row.examination_time_formatted || ''}</div>
                        </div>
                    `;
                }
            },
            {
                data: 'examination_type',
                name: 'examination_type',
                render: function(data, type, row) {
                    const typeConfig = {
                        'emergency': { bg: 'v3-bg-panel v3-bg-panel to-pink-100', text: 'v3-text-danger', border: 'v3-border-default', icon: 'exclamation-triangle' },
                        'initial': { bg: 'v3-bg-panel v3-bg-panel v3-bg-panel', text: 'v3-text-warning', border: 'v3-border-default', icon: 'play-circle' },
                        'periodic': { bg: 'v3-bg-panel v3-bg-panel v3-bg-panel', text: 'v3-text-primary', border: 'v3-border-default', icon: 'calendar-check' },
                        'follow_up': { bg: 'v3-bg-panel v3-bg-panel v3-bg-panel', text: 'v3-text-ink', border: 'v3-border-default', icon: 'arrow-right' }
                    };
                    const typeLabelMap = {
                        emergency: i18n.typeEmergency,
                        initial: i18n.typeInitial,
                        periodic: i18n.typePeriodic,
                        follow_up: i18n.typeFollowUp,
                    };
                    const config = typeConfig[data] || typeConfig['follow_up'];
                    return `
                        <span class="inline-flex items-center v3-radius border px-3 py-1.5 text-xs font-extrabold ${config.bg} ${config.text} ${config.border}">
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
                        'completed': { bg: 'v3-bg-panel v3-bg-panel v3-bg-panel', text: 'v3-text-primary', border: 'v3-border-default', icon: 'check-circle' },
                        'in_progress': { bg: 'v3-bg-panel v3-bg-panel v3-bg-panel', text: 'v3-text-primary', border: 'v3-border-default', icon: 'clock' },
                        'pending': { bg: 'v3-bg-panel v3-bg-panel v3-bg-panel', text: 'v3-text-warning', border: 'v3-border-default', icon: 'hourglass-half' },
                        'cancelled': { bg: 'v3-bg-panel v3-bg-panel to-pink-100', text: 'v3-text-danger', border: 'v3-border-default', icon: 'times-circle' }
                    };
                    const statusLabelMap = {
                        pending: i18n.statusPending,
                        in_progress: i18n.statusInProgress,
                        completed: i18n.statusCompleted,
                        cancelled: i18n.statusCancelled,
                    };
                    const config = statusConfig[data] || statusConfig['pending'];
                    const documentationBadge = row.documentation_state === 'signed'
                        ? `<span class="mt-1 inline-flex rounded-lg v3-bg-paid px-2 py-1 text-xs font-black v3-text-primary">${i18n.documentationSigned}</span>`
                        : (row.documentation_state === 'ready_to_sign'
                            ? `<span class="mt-1 inline-flex rounded-lg v3-bg-chair px-2 py-1 text-xs font-black v3-text-chair">${i18n.documentationReadyToSign}</span>`
                            : (row.documentation_state === 'overdue_draft'
                                ? `<span class="mt-1 inline-flex rounded-lg v3-bg-waiting px-2 py-1 text-xs font-black v3-text-warning">${i18n.documentationOverdueDraft}</span>`
                                : ''));
                    return `
                        <span class="inline-flex items-center v3-radius border px-3 py-1.5 text-xs font-extrabold ${config.bg} ${config.text} ${config.border}">
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
                               class="v3-radius p-2 v3-text-warning transition-all duration-200  v3-hover-primary v3-hover-text-warning" title="${i18n.actionViewDetails}">
                                <i class="fas fa-eye"></i>
                            </a>`;
                    }

                    // Edit button
                    if (permissions.examinations.edit) {
                        actions += `
                            <a href="<?= base_url('examination') ?>/${data}/edit"
                               class="group/action relative v3-radius p-2 v3-text-primary transition-all duration-300 hover:scale-110 v3-hover-primary v3-hover-text-primary" title="${i18n.actionEditExamination}">
                                <div class="absolute inset-0 v3-radius v3-bg-panel v3-bg-panel v3-bg-panel opacity-0 blur transition-opacity duration-300 group-hover/action:opacity-100"></div>
                                <i class="fas fa-edit relative z-10"></i>
                            </a>`;
                    }

                    // Odontogram button
                    if (permissions.examinations.view) {
                        actions += `
                            <a href="<?= base_url('odontogram') ?>/${row.patient_id}"
                               class="group/action relative v3-radius p-2 v3-text-chair transition-all duration-300 hover:scale-110 v3-hover-primary v3-hover-text-chair" title="${i18n.actionViewOdontogram}">
                                <div class="absolute inset-0 v3-radius v3-bg-panel v3-bg-panel to-pink-500/10 opacity-0 blur transition-opacity duration-300 group-hover/action:opacity-100"></div>
                                <i class="fas fa-tooth relative z-10"></i>
                            </a>`;
                    }

                    // Complete button
                    if ((row.status === 'pending' || row.status === 'in_progress') && permissions.examinations.edit) {
                        actions += `
                            <button onclick="completeExamination(${data})"
                                    class="group/action relative v3-radius p-2 v3-text-primary transition-all duration-300 hover:scale-110 v3-hover-primary v3-hover-text-primary v3-btn v3-btn--secondary" title="${i18n.actionCompleteExamination}">
                                <div class="absolute inset-0 v3-radius v3-bg-panel v3-bg-panel v3-bg-panel opacity-0 blur transition-opacity duration-300 group-hover/action:opacity-100"></div>
                                <i class="fas fa-check relative z-10"></i>
                            </button>`;
                    }

                    // More actions dropdown
                    actions += `
                        <div class="relative">
                            <button onclick="toggleDropdown(${data})" class="v3-radius p-2 v3-text-muted transition-all duration-300 hover:scale-110 v3-hover-soft v3-hover-text-ink v3-btn v3-btn--secondary" title="${i18n.actionMoreActions}">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div id="dropdown-${data}" class="absolute end-0 z-10 mt-2 hidden w-48 v3-radius border v3-border-default v3-bg-panel v3-elevation-none">
                                <div class="py-2">`;

                    // Print button (always available if view permission)
                    if (permissions.examinations.view) {
                        actions += `
                            <a href="<?= base_url('examination') ?>/${data}/print" class="flex items-center px-4 py-2 text-sm font-bold v3-text-ink transition-colors duration-200 v3-hover-soft v3-hover-text-ink">
                                        <i class="fas fa-print me-3 v3-text-muted"></i>${i18n.actionPrintReport}
                                    </a>`;
                    }

                    // Duplicate button (requires create permission)
                    if (permissions.examinations.create) {
                        actions += `
                                    <a href="<?= base_url('examination') ?>/${data}/duplicate" class="flex items-center px-4 py-2 text-sm font-bold v3-text-ink transition-colors duration-200 v3-hover-primary v3-hover-text-warning">
                                        <i class="fas fa-copy me-3 v3-text-warning"></i>${i18n.actionDuplicate}
                                    </a>`;
                    }

                    // Delete button
                    if (permissions.examinations.delete) {
                        actions += `
                                    <button onclick="deleteExamination(${data})" class="flex w-full items-center px-4 py-2 text-sm font-bold v3-text-danger transition-colors duration-200 v3-hover-danger v3-btn v3-btn--danger">
                                        <i class="fas fa-trash me-3 v3-text-danger"></i>${i18n.actionDelete}
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
        dom: '<"border-b v3-border-default v3-bg-panel px-4 py-4 sm:px-6"<"flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"<"flex items-center gap-4"l><"flex items-center gap-4"f>>rt<"border-t v3-border-default v3-bg-panel px-4 py-4 sm:px-6"<"flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"<"flex items-center"i><"flex items-center"p>>>',
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
                $('#examinationsTable tbody tr').addClass('cursor-pointer v3-hover-primary transition-colors duration-150');
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
    // Hide all views
    document.getElementById('tableView').classList.add('hidden');
    document.getElementById('gridView').classList.add('hidden');
    document.getElementById('calendarView').classList.add('hidden');

    // Reset button styles
    document.getElementById('tableViewBtn').className = 'inline-flex items-center px-4 py-2 v3-bg-soft v3-text-ink text-sm font-medium rounded-lg v3-hover-soft focus:outline-none focus:ring-2 v3-focus-control focus:ring-offset-2 transition-all duration-200';
    document.getElementById('gridViewBtn').className = 'inline-flex items-center px-4 py-2 v3-bg-soft v3-text-ink text-sm font-medium rounded-lg v3-hover-soft focus:outline-none focus:ring-2 v3-focus-control focus:ring-offset-2 transition-all duration-200';
    document.getElementById('calendarViewBtn').className = 'inline-flex items-center px-4 py-2 v3-bg-soft v3-text-ink text-sm font-medium rounded-lg v3-hover-soft focus:outline-none focus:ring-2 v3-focus-control focus:ring-offset-2 transition-all duration-200';

    // Show selected view and update button
    if (view === 'table') {
        document.getElementById('tableView').classList.remove('hidden');
        document.getElementById('tableViewBtn').className = 'inline-flex items-center rounded-lg v3-bg-warning px-4 py-2 text-sm font-bold v3-text-on-primary v3-elevation-none transition-all duration-200 v3-hover-primary focus:outline-none focus:ring-4 v3-focus-control';
    } else if (view === 'grid') {
        document.getElementById('gridView').classList.remove('hidden');
        document.getElementById('gridViewBtn').className = 'inline-flex items-center rounded-lg v3-bg-warning px-4 py-2 text-sm font-bold v3-text-on-primary v3-elevation-none transition-all duration-200 v3-hover-primary focus:outline-none focus:ring-4 v3-focus-control';
    } else if (view === 'calendar') {
        document.getElementById('calendarView').classList.remove('hidden');
        document.getElementById('calendarViewBtn').className = 'inline-flex items-center rounded-lg v3-bg-warning px-4 py-2 text-sm font-bold v3-text-on-primary v3-elevation-none transition-all duration-200 v3-hover-primary focus:outline-none focus:ring-4 v3-focus-control';
        generateCalendar();
    }

    currentView = view;
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
        dayHeader.className = 'p-2 text-center text-sm font-semibold v3-text-muted v3-bg-panel';
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
        emptyCell.className = 'h-20 p-2 border v3-border-default v3-bg-panel';
        calendarGrid.appendChild(emptyCell);
    }

    // Add days of month
    for (let day = 1; day <= daysInMonth; day++) {
        const dayCell = document.createElement('div');
        dayCell.className = 'h-20 p-2 border v3-border-default v3-hover-soft cursor-pointer';

        const dayNum = document.createElement('div');
        dayNum.className = 'text-sm font-medium v3-text-ink';
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
