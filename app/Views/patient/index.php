<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$validation = session('validation');
if (!$validation instanceof \CodeIgniter\Validation\Validation) {
    $validation = null;
}
$shouldOpenPatientCreateModal = old('source_modal') === 'patient_quick_create';
$isRtl = !empty($is_rtl) || (service('request')->getLocale() === 'ar');
$patientIndexReturnTo = workflow_current_target() ?? 'patients';
?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

<!-- Custom DataTables Styling -->


<div class="patients-page min-h-screen v3-surface">
    <div class="w-full px-3 py-4 sm:px-5 sm:py-5">
        <header class="v3-command-header">
  <div><h1><?= esc(lang('UI.patientManagementTitle')) ?></h1><p><?= esc(lang('UI.patientManagementSubtitle')) ?></p></div>
  <div class="v3-command-actions"><?php if (has_permission('patients', 'create')): ?><a href="<?= base_url('patient/create') ?>" id="newPatientModalTrigger" data-patient-create-modal-open class="v3-action v3-action-primary">
                        <i class="fas fa-user-plus"></i>
                        <?= esc(lang('UI.patientNewPatient')) ?>
                    </a><?php endif; ?></div>
</header>

        <section class="v3-metric-strip" aria-label="<?= esc(lang('UI.patientPatientRegistry')) ?>">
<article class="v3-metric v3-metric-tone-0"><span><?= esc(lang('UI.patientTotalPatients')) ?></span><strong id="totalPatients"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i></strong><i class="fas fa-users" aria-hidden="true"></i></article>
<article class="v3-metric v3-metric-tone-1"><span><?= esc(lang('UI.patientActivePatients')) ?></span><strong id="activePatients"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i></strong><i class="fas fa-user-check" aria-hidden="true"></i></article>
<article class="v3-metric v3-metric-tone-2"><span><?= esc(lang('UI.patientNewPatients')) ?></span><strong id="newPatients"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i></strong><i class="fas fa-user-plus" aria-hidden="true"></i></article>
<article class="v3-metric v3-metric-tone-3"><span><?= esc(lang('UI.patientThisMonth')) ?></span><strong id="monthlyPatients"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i></strong><i class="fas fa-calendar-day" aria-hidden="true"></i></article>
</section>

        <section id="tableView" class="v3-ledger-panel">
            <div class="border-b v3-border v3-warm-surface px-4 py-4 sm:px-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center v3-radius v3-bg-paid v3-text-primary border v3-border-default">
                            <i class="fas fa-list"></i>
                        </span>
                        <div>
                            <h3 class="text-lg v3-text-ink"><?= esc(lang('UI.patientPatientRegistry')) ?></h3>
                            <p class="text-sm font-semibold v3-text-muted"><?= esc(lang('UI.patientManagementSubtitle')) ?></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button onclick="exportToPDF()" class="inline-flex items-center gap-2 v3-radius border v3-border-default v3-bg-alert px-3 py-2 text-xs v3-text-danger transition v3-hover-danger v3-btn v3-btn--danger">
                            <i class="fas fa-file-pdf"></i>
                            PDF
                        </button>
                        <button onclick="printPatients()" class="inline-flex items-center gap-2 v3-radius border v3-border v3-surface px-3 py-2 text-xs v3-text-ink transition v3-hover-soft v3-btn v3-btn--secondary">
                            <i class="fas fa-print"></i>
                            <?= esc(lang('UI.patientPrint')) ?>
                        </button>
                        <button onclick="exportToCSV()" class="inline-flex items-center gap-2 v3-radius border v3-border-default v3-bg-paid px-3 py-2 text-xs v3-text-primary transition v3-hover-primary v3-btn v3-btn--secondary">
                            <i class="fas fa-file-csv"></i>
                            CSV
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                        <table id="patientsTable" class="w-full min-w-full">
                            <thead>
                                <tr>
                                    <th class="px-6 py-4 text-start text-xs font-semibold v3-text-muted uppercase tracking-wider">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-id-card v3-text-primary"></i>
                                            <span><?= esc(lang('UI.patientPatientId')) ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-start text-xs font-semibold v3-text-muted uppercase tracking-wider">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-user v3-text-primary"></i>
                                            <span><?= esc(lang('UI.patientName')) ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-start text-xs font-semibold v3-text-muted uppercase tracking-wider">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-phone v3-text-primary"></i>
                                            <span><?= esc(lang('UI.patientPhone')) ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-start text-xs font-semibold v3-text-muted uppercase tracking-wider">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-envelope v3-text-primary"></i>
                                            <span><?= esc(lang('UI.patientEmail')) ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-start text-xs font-semibold v3-text-muted uppercase tracking-wider">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-info-circle v3-text-primary"></i>
                                            <span><?= esc(lang('UI.patientStatus')) ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-start text-xs font-semibold v3-text-muted uppercase tracking-wider">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-calendar v3-text-primary"></i>
                                            <span><?= esc(lang('UI.patientCreated')) ?></span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-start text-xs font-semibold v3-text-muted uppercase tracking-wider">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-cogs v3-text-primary"></i>
                                            <span><?= esc(lang('UI.patientActions')) ?></span>
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
    </div>
</div>

<?php if (has_permission('patients', 'create')): ?>
    <div id="newPatientModal" class="v3-context-drawer patient-create-modal fixed inset-0 z-[2147483600] hidden overflow-hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="newPatientModalTitle" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
        <div class="patient-create-modal__backdrop v3-context-drawer__backdrop" data-patient-create-modal-close></div>
        <div class="patient-create-modal__shell v3-context-drawer__shell" data-patient-create-modal-close>
            <form id="newPatientQuickCreateForm" action="<?= base_url('patient/store') ?>" method="POST" class="patient-create-modal__panel v3-context-drawer__panel" data-patient-create-form data-select2-dropdown-parent onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="patients">
                <input type="hidden" name="source_modal" value="patient_quick_create">

                <div class="v3-context-drawer__header">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center v3-radius v3-warm-surface">
                            <i class="fas fa-user-plus"></i>
                        </span>
                        <div>
                            <h2 id="newPatientModalTitle" class="text-lg font-bold v3-text-ink"><?= esc(lang('UI.patientNewPatient')) ?></h2>
                            <a href="<?= workflow_context_url('patient/create', $patientIndexReturnTo) ?>" class="mt-1 inline-flex text-xs font-semibold v3-text-primary v3-hover-text-primary">
                                <?= esc(lang('UI.openFullPage')) ?>
                            </a>
                        </div>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full v3-text-muted transition v3-hover-soft v3-hover-text-ink v3-btn v3-btn--icon" data-patient-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="v3-context-drawer__body">
                    <?php if (session('error') && $shouldOpenPatientCreateModal): ?>
                        <div class="v3-radius border v3-border-default v3-bg-alert p-4 text-sm v3-text-danger">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>

                    <?= view('patient/partials/create_form_fields', [
                        'validation' => $validation,
                        'is_rtl' => $isRtl,
                        'sectionClass' => 'v3-flow-section',
                    ]) ?>
                </div>

                <div class="v3-context-drawer__footer">
                    <button type="button" data-patient-create-modal-close class="inline-flex items-center justify-center v3-radius border v3-border px-5 py-2.5 text-sm font-semibold v3-text-ink transition v3-hover-soft v3-btn v3-btn--secondary">
                        <?= esc(lang('UI.cancel')) ?>
                    </button>
                    <button type="submit" name="post_submit_action" value="save_only" class="inline-flex items-center justify-center v3-radius border v3-border-default v3-surface px-5 py-2.5 text-sm font-bold v3-text-primary transition v3-hover-primary v3-btn v3-btn--primary">
                        <i class="fas fa-user-plus me-2"></i>
                        <?= esc(lang('UI.settingsSave')) ?>
                    </button>
                    <button type="submit" name="post_submit_action" value="save_and_book" class="inline-flex items-center justify-center v3-radius v3-warm-surface px-5 py-2.5 text-sm font-bold transition v3-btn v3-btn--primary">
                        <i class="fas fa-calendar-plus me-2"></i>
                        <?= esc(lang('UI.patientSaveAndBookAppointment')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?= view('examination/partials/quick_create_modal', [
    'is_rtl' => $isRtl,
    'return_to' => $patientIndexReturnTo,
    'contextual' => false,
    'selected_patient' => null,
]) ?>

<?= view('treatment/partials/quick_create_modal', [
    'isRtl' => $isRtl,
    'return_to' => $patientIndexReturnTo,
    'contextual' => false,
    'selected_patient' => null,
    'patients' => [],
    'treatment_types' => $patient_index_treatment_types ?? [],
    'inventory_items' => $patient_index_inventory_items ?? [],
    'clinic_providers' => $patient_index_clinic_providers ?? [],
    'validation' => session()->getFlashdata('validation'),
]) ?>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
let newPatientModalTrigger = null;
let newPatientModalCloseTimer = null;

function mountNewPatientModal() {
    const modal = document.getElementById('newPatientModal');
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
    if (modal && modal.dataset.closeControlsBound !== '1') {
        modal.dataset.closeControlsBound = '1';
        modal.querySelectorAll('button[data-patient-create-modal-close], a[data-patient-create-modal-close]').forEach(function (control) {
            control.addEventListener('click', function (event) {
                event.preventDefault();
                closeNewPatientModal();
            });
        });
    }
    return modal;
}

function initializeNewPatientModalContent(modal) {
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
    if (typeof window.initPatientCreateForms === 'function') {
        window.initPatientCreateForms(modal);
    }
    return Promise.allSettled(tasks);
}

function resetNewPatientModalForm(modal) {
    const form = modal ? modal.querySelector('[data-patient-create-form]') : null;
    if (!form) {
        return;
    }

    form.reset();
    form.querySelectorAll('select').forEach(function(field) {
        if (window.jQuery && window.jQuery(field).data('select2')) {
            window.jQuery(field).val('').trigger('change');
        } else {
            field.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
    form.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(function(field) {
        field.dispatchEvent(new Event('change', { bubbles: true }));
    });
    if (typeof form.__syncPatientCreateForm === 'function') {
        form.__syncPatientCreateForm();
    } else if (typeof window.initPatientCreateForms === 'function') {
        window.initPatientCreateForms(form);
    }
}

function openNewPatientModal(trigger) {
    const modal = mountNewPatientModal();
    if (!modal) {
        return;
    }
    newPatientModalTrigger = trigger || document.activeElement;
    if (newPatientModalCloseTimer) {
        clearTimeout(newPatientModalCloseTimer);
        newPatientModalCloseTimer = null;
    }
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.add('app-drawer-lock');
    document.body.classList.add('app-drawer-lock');
    requestAnimationFrame(function() {
        modal.classList.add('is-open');
    });
    const firstField = modal.querySelector('#first_name');
    if (firstField && typeof firstField.focus === 'function') {
        firstField.focus({ preventScroll: true });
    }
    window.setTimeout(function() {
        initializeNewPatientModalContent(modal);
    }, 80);
    window.setTimeout(function() {
        if (firstField && typeof firstField.focus === 'function') {
            firstField.focus();
        }
    }, 100);
}

function closeNewPatientModal() {
    const modal = document.getElementById('newPatientModal');
    if (!modal || modal.classList.contains('hidden')) {
        return;
    }
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.documentElement.classList.remove('app-drawer-lock');
    document.body.classList.remove('app-drawer-lock');
    newPatientModalCloseTimer = setTimeout(function() {
        modal.classList.add('hidden');
        resetNewPatientModalForm(modal);
        if (newPatientModalTrigger && typeof newPatientModalTrigger.focus === 'function') {
            newPatientModalTrigger.focus();
        }
        newPatientModalTrigger = null;
    }, 240);
}

document.addEventListener('click', function(event) {
    const openTrigger = event.target.closest('[data-patient-create-modal-open]');
    if (openTrigger) {
        event.preventDefault();
        openNewPatientModal(openTrigger);
        return;
    }

    const closeTrigger = event.target.closest('[data-patient-create-modal-close]');
    if (closeTrigger) {
        event.preventDefault();
        closeNewPatientModal();
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key !== 'Escape') {
        return;
    }
    const modal = document.getElementById('newPatientModal');
    if (modal && !modal.classList.contains('hidden')) {
        event.preventDefault();
        closeNewPatientModal();
    }
});

document.addEventListener('click', function(event) {
    const examinationTrigger = event.target.closest('[data-examination-create-modal-open]');
    if (examinationTrigger && document.getElementById('newExaminationModal') && typeof window.openNewExaminationModal === 'function') {
        event.preventDefault();
        event.stopPropagation();
        window.openNewExaminationModal(examinationTrigger);
        return;
    }

    const treatmentTrigger = event.target.closest('[data-treatment-create-modal-open]');
    if (treatmentTrigger && document.getElementById('newTreatmentModal') && typeof window.openTreatmentModal === 'function') {
        event.preventDefault();
        event.stopPropagation();
        window.openTreatmentModal(treatmentTrigger);
    }
}, true);
document.addEventListener('DOMContentLoaded', function() {
    if (<?= $shouldOpenPatientCreateModal ? 'true' : 'false' ?>) {
        openNewPatientModal(document.getElementById('newPatientModalTrigger'));
    }
});

// Global variables
let patientsTable;
const csrfMetaName = document.querySelector('meta[name="csrf-name"]');
const csrfMetaToken = document.querySelector('meta[name="csrf-token"]');
const csrfCookieName = '<?= esc(config('Security')->cookieName) ?>';
const patientI18n = {
    sessionExpired: '<?= esc(lang('UI.patientSessionExpired'), 'js') ?>',
    networkError: '<?= esc(lang('UI.patientNetworkError'), 'js') ?>',
    loadingError: '<?= esc(lang('UI.patientLoadingError'), 'js') ?>',
    noPhone: '<?= esc(lang('UI.patientNoPhone'), 'js') ?>',
    noEmail: '<?= esc(lang('UI.patientNoEmail'), 'js') ?>',
    na: '<?= esc(lang('UI.patientNa'), 'js') ?>',
    unknown: '<?= esc(lang('UI.patientUnknown'), 'js') ?>',
    statusActive: '<?= esc(lang('UI.patientStatusActive'), 'js') ?>',
    statusInactive: '<?= esc(lang('UI.patientStatusInactive'), 'js') ?>',
    statusPending: '<?= esc(lang('UI.patientStatusPending'), 'js') ?>',
    patientId: '<?= esc(lang('UI.patientPatientId'), 'js') ?>',
    patientName: '<?= esc(lang('UI.patientName'), 'js') ?>',
    viewPatient: '<?= esc(lang('UI.patientViewPatient'), 'js') ?>',
    editPatient: '<?= esc(lang('UI.patientEditPatient'), 'js') ?>',
    odontogram: '<?= esc(lang('UI.patientOdontogram'), 'js') ?>',
    newExamination: '<?= esc(lang('UI.newExamination'), 'js') ?>',
    newTreatment: '<?= esc(lang('UI.patientNewTreatment'), 'js') ?>',
    deletePatient: '<?= esc(lang('UI.patientDeletePatient'), 'js') ?>',
    reportTitle: '<?= esc(lang('UI.patientReportTitle'), 'js') ?>',
    loading: '<?= esc(lang('UI.patientLoading'), 'js') ?>',
    noRecords: '<?= esc(lang('UI.patientNoRecords'), 'js') ?>',
    showing: '<?= esc(lang('UI.patientShowing'), 'js') ?>',
    infoEmpty: '<?= esc(lang('UI.patientInfoEmpty'), 'js') ?>',
    infoFiltered: '<?= esc(lang('UI.patientInfoFiltered'), 'js') ?>',
    searchPlaceholder: '<?= esc(lang('UI.patientSearchPlaceholder'), 'js') ?>',
    paginateFirst: '<?= esc(lang('UI.patientPaginateFirst'), 'js') ?>',
    paginateLast: '<?= esc(lang('UI.patientPaginateLast'), 'js') ?>',
    paginateNext: '<?= esc(lang('UI.patientPaginateNext'), 'js') ?>',
    paginatePrevious: '<?= esc(lang('UI.patientPaginatePrevious'), 'js') ?>',
    deleteConfirm: '<?= esc(lang('UI.patientDeleteConfirm'), 'js') ?>'
};

function escapeHtmlAttribute(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function getCookieValue(name) {
    if (!name) {
        return '';
    }

    const cookies = document.cookie.split(';');
    for (const cookie of cookies) {
        const [key, ...valueParts] = cookie.trim().split('=');
        if (key === name) {
            return decodeURIComponent(valueParts.join('='));
        }
    }

    return '';
}

function getCsrfName() {
    return csrfMetaName ? csrfMetaName.getAttribute('content') : '<?= csrf_token() ?>';
}

function getCsrfToken() {
    return csrfMetaToken ? csrfMetaToken.getAttribute('content') : '';
}

function refreshCsrfToken() {
    const cookieToken = getCookieValue(csrfCookieName);
    if (cookieToken && csrfMetaToken) {
        csrfMetaToken.setAttribute('content', cookieToken);
    }
}

$(document).ready(function() {
    // Check if table exists before initializing DataTable
    if ($('#patientsTable').length === 0) {
        console.error('Table #patientsTable not found');
        return;
    }

    // Initialize DataTable with server-side processing
    patientsTable = $('#patientsTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '<?= base_url('patient/get-data') ?>',
            type: 'POST',
            beforeSend: function(xhr) {
                const csrfToken = getCsrfToken();
                if (csrfToken) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                }
            },
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.search_term = $('#searchInput').val();
                const csrfName = getCsrfName();
                const csrfToken = getCsrfToken();
                if (csrfName && csrfToken) {
                    d[csrfName] = csrfToken;
                }
                return d;
            },
            complete: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.csrf_token && csrfMetaToken) {
                    csrfMetaToken.setAttribute('content', xhr.responseJSON.csrf_token);
                }
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables AJAX error:', error, thrown);
                console.error('Response:', xhr.responseText);

                if (xhr.responseJSON && xhr.responseJSON.csrf_token && csrfMetaToken) {
                    csrfMetaToken.setAttribute('content', xhr.responseJSON.csrf_token);
                }

                // Check if it's an authentication error
                if (xhr.status === 401 || xhr.status === 403) {
                    alert(patientI18n.sessionExpired);
                    window.location.reload();
                } else if (xhr.status === 0) {
                    alert(patientI18n.networkError);
                } else {
                    alert(patientI18n.loadingError);
                }
            }
        },
        columns: [
            {
                data: 'patient_id',
                name: 'patient_id',
                render: function(data, type, row) {
                    return `
                        <div>
                            <div class="text-sm font-medium v3-text-ink">${data || patientI18n.na}</div>
                            <div class="text-xs v3-text-muted">${patientI18n.patientId}</div>
                        </div>
                    `;
                }
            },
            {
                data: 'name',
                name: 'name',
                render: function(data, type, row) {
                    const gender = String(row.gender || 'other').toLowerCase();
                    const avatarMap = {
                        male: {
                            icon: 'fas fa-person',
                            shell: 'v3-bg-scheduled border v3-border-default',
                            iconColor: 'v3-text-primary'
                        },
                        female: {
                            icon: 'fas fa-person-dress',
                            shell: 'v3-bg-alert border v3-border-default',
                            iconColor: 'v3-text-danger'
                        },
                        other: {
                            icon: 'fas fa-user',
                            shell: 'v3-bg-soft border v3-border-default',
                            iconColor: 'v3-text-muted'
                        }
                    };
                    const avatar = avatarMap[gender] || avatarMap.other;
                    return `
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-7 h-7 flex-shrink-0">
                                <i class="${avatar.icon} ${avatar.iconColor} text-2xl"></i>
                            </span>
                            <div>
                                <div class="text-sm font-medium v3-text-ink">${data || patientI18n.na}</div>
                                <div class="text-xs v3-text-muted">${patientI18n.patientName}</div>
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: 'phone',
                name: 'phone',
                render: function(data, type, row) {
                    return data ? `
                        <div class="flex items-center gap-2">
                            <i class="fas fa-phone v3-text-primary text-xs"></i>
                            <span class="text-sm v3-text-ink">${data}</span>
                        </div>
                    ` : `<span class="v3-text-muted text-sm">${patientI18n.noPhone}</span>`;
                }
            },
            {
                data: 'email',
                name: 'email',
                render: function(data, type, row) {
                    return data ? `
                        <div class="flex items-center gap-2">
                            <i class="fas fa-envelope v3-text-primary text-xs"></i>
                            <span class="text-sm v3-text-ink">${data}</span>
                        </div>
                    ` : `<span class="v3-text-muted text-sm">${patientI18n.noEmail}</span>`;
                }
            },
            {
                data: 'status',
                name: 'status',
                render: function(data, type, row) {
                    const statusColors = {
                        'active': 'v3-bg-paid v3-text-primary',
                        'inactive': 'v3-bg-soft v3-text-ink',
                        'pending': 'v3-bg-waiting v3-text-warning'
                    };
                    const statusIcons = {
                        'active': 'fas fa-check-circle',
                        'inactive': 'fas fa-times-circle',
                        'pending': 'fas fa-clock'
                    };
                    const statusLabels = {
                        'active': patientI18n.statusActive,
                        'inactive': patientI18n.statusInactive,
                        'pending': patientI18n.statusPending
                    };
                    const colorClass = statusColors[data] || 'v3-bg-soft v3-text-ink';
                    const iconClass = statusIcons[data] || 'fas fa-question-circle';
                    const label = statusLabels[data] || patientI18n.unknown;

                    return `
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold ${colorClass}">
                            <i class="${iconClass}"></i>
                            ${label}
                        </span>
                    `;
                }
            },
            {
                data: 'created_at',
                name: 'created_at',
                render: function(data, type, row) {
                    return `
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar v3-text-primary text-xs"></i>
                            <span class="text-sm v3-text-ink">${data || patientI18n.na}</span>
                        </div>
                    `;
                }
            },
            {
                data: 'id',
                name: 'id',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let actions = '<div class="flex items-center gap-1.5">';

                    // View button
                    if (permissions.patients.view) {
                        actions += `
                            <a href="<?= base_url('patient') ?>/${data}"
                               data-workflow-context="1" class="inline-flex h-9 w-9 items-center justify-center v3-radius v3-bg-paid v3-text-primary border v3-border-default transition v3-hover-primary" title="${patientI18n.viewPatient}">
                                <i class="fas fa-eye"></i>
                            </a>`;
                    }

                    // Edit button
                    if (permissions.patients.edit) {
                        actions += `
                            <a href="<?= base_url('patient') ?>/${data}/edit"
                               class="inline-flex h-9 w-9 items-center justify-center v3-radius v3-bg-scheduled v3-text-primary border v3-border-default transition v3-hover-primary" title="${patientI18n.editPatient}">
                                <i class="fas fa-edit"></i>
                            </a>`;
                    }

                    // Odontogram button
                    actions += `
                        <a href="<?= base_url('odontogram') ?>/${data}"
                           class="inline-flex h-9 w-9 items-center justify-center v3-radius v3-bg-chair v3-text-chair border v3-border-default transition v3-hover-primary" title="${patientI18n.odontogram}">
                            <i class="fas fa-tooth"></i>
                        </a>`;

                    const patientContextLabel = row.name || row.patient_id || '';

                    // New examination button
                    if (permissions.examinations.create) {
                        actions += `
                            <a href="<?= base_url('examination/create?patient_id=') ?>${data}"
                               data-examination-create-modal-open
                               data-workflow-context="1"
                               data-context-patient-id="${data}"
                               data-context-patient-name="${escapeHtmlAttribute(patientContextLabel)}"
                               data-context-patient-code="${escapeHtmlAttribute(row.patient_id || '')}"
                               data-context-patient-gender="${escapeHtmlAttribute(row.gender || '')}"
                               class="inline-flex h-9 w-9 items-center justify-center v3-radius v3-bg-chair v3-text-chair border v3-border-default transition v3-hover-primary" title="${patientI18n.newExamination}">
                                <i class="fas fa-stethoscope"></i>
                            </a>`;
                    }

                    // New treatment button
                    if (permissions.treatments.create) {
                        actions += `
                            <a href="<?= base_url('treatment/create?patient_id=') ?>${data}"
                               data-treatment-create-modal-open
                               data-workflow-context="1"
                               data-context-patient-id="${data}"
                               data-context-patient-name="${escapeHtmlAttribute(patientContextLabel)}"
                               data-context-patient-code="${escapeHtmlAttribute(row.patient_id || '')}"
                               class="inline-flex h-9 w-9 items-center justify-center v3-radius v3-bg-scheduled v3-text-primary border v3-border-default transition v3-hover-primary" title="${patientI18n.newTreatment}">
                                <i class="fas fa-briefcase-medical"></i>
                            </a>`;
                    }

                    // Delete button
                    if (permissions.patients.delete) {
                        actions += `
                            <button onclick="deletePatient(${data})"
                                    class="inline-flex h-9 w-9 items-center justify-center v3-radius v3-bg-alert v3-text-danger border v3-border-default transition v3-hover-danger v3-btn v3-btn--danger" title="${patientI18n.deletePatient}">
                                <i class="fas fa-trash"></i>
                            </button>`;
                    }

                    actions += '</div>';
                    return actions;
                }
            }
        ],
        createdRow: function(row, data) {
            if (!permissions.patients.view) {
                return;
            }

            const profileUrl = `<?= base_url('patient') ?>/${data.id}`;
            const contextualProfileUrl = window.workflowContextUrl ? window.workflowContextUrl(profileUrl) : profileUrl;

            $(row)
                .addClass('cursor-pointer v3-hover-primary transition-colors duration-150')
                .attr('data-href', contextualProfileUrl)
                .attr('tabindex', '0')
                .attr('role', 'link')
                .attr('aria-label', `${patientI18n.viewPatient}: ${data.name || data.patient_id || ''}`)
                .on('click', function(event) {
                    if ($(event.target).closest('a, button').length) {
                        return;
                    }
                    window.location.href = contextualProfileUrl;
                })
                .on('keydown', function(event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        if ($(event.target).closest('a, button').length) {
                            return;
                        }
                        event.preventDefault();
                        window.location.href = contextualProfileUrl;
                    }
                });

            $('a, button', row).on('click', function(event) {
                event.stopPropagation();
            });
        },
        dom: 'B<"px-6 py-4 border-b v3-border-default v3-bg-panel"<"flex items-center justify-between"<"flex items-center space-x-4"l><"flex items-center space-x-4"f>>rt<"px-6 py-4 border-t v3-border-default v3-bg-panel"<"flex items-center justify-between"<"flex items-center"i><"flex items-center"p>>>',
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'desc']],
        buttons: [
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf mr-2"></i>PDF',
                className: 'btn-pdf',
                title: patientI18n.reportTitle,
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Patient ID, Name, Phone, Email, Status, Created
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print mr-2"></i>Print',
                className: 'btn-print',
                title: patientI18n.reportTitle,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Patient ID, Name, Phone, Email, Status, Created
                }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv mr-2"></i>CSV',
                className: 'btn-csv',
                title: patientI18n.reportTitle,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Patient ID, Name, Phone, Email, Status, Created
                }
            }
        ],
        language: {
            processing: patientI18n.loading,
            lengthMenu: "_MENU_",
            zeroRecords: patientI18n.noRecords,
            info: patientI18n.showing,
            infoEmpty: patientI18n.infoEmpty,
            infoFiltered: patientI18n.infoFiltered,
            search: "",
            searchPlaceholder: patientI18n.searchPlaceholder,
            paginate: {
                first: patientI18n.paginateFirst,
                last: patientI18n.paginateLast,
                next: patientI18n.paginateNext,
                previous: patientI18n.paginatePrevious
            }
        },
        drawCallback: function(settings) {
            console.log('Patients DataTable draw callback triggered');
            // Re-initialize tooltips if the function exists
            if (typeof $.fn.tooltip === 'function') {
                $('[data-toggle="tooltip"]').tooltip();
            }

            // Apply custom styling after each draw
            applyDataTablesStyling();
        }
    });

    // Apply custom DataTables styling
    function applyDataTablesStyling() {
        // Style length selector
        $('.dataTables_length select').addClass('v3-focus-control v3-focus-control');

        // Style search input
        $('.dataTables_filter input').addClass('v3-focus-control v3-focus-control');

        // Style info text
        $('.dataTables_info').addClass('v3-text-muted');

        // Style pagination buttons
        $('.dataTables_paginate .paginate_button').each(function() {
            if ($(this).hasClass('current')) {
                $(this).addClass('v3-bg-primary v3-border-default v3-text-on-primary');
            } else {
                $(this).addClass('v3-hover-primary v3-border-default v3-hover-text-primary');
            }
        });

        // Style processing indicator
        $('.dataTables_processing').addClass('v3-border-default v3-text-primary');

        // Style labels
        $('.dataTables_length label, .dataTables_filter label').addClass('v3-text-ink font-semibold');

        // Style icons
        $('.dataTables_length i, .dataTables_filter i').addClass('v3-text-primary');
    }

    // Load statistics
    loadPatientStatistics();

    // Search functionality
    $('#searchInput').on('keyup', function() {
        patientsTable.search(this.value).draw();
    });

    // Status filter
    $('#statusFilter').on('change', function() {
        patientsTable.draw();
    });

    // Add a fallback if the table fails to load
    setTimeout(function() {
        if (patientsTable && patientsTable.data().count() === 0) {
            console.log('Table appears empty, checking for errors...');
            // You could add fallback logic here if needed
        }
    }, 5000);
});

// Load patient statistics
function loadPatientStatistics() {
    $.ajax({
        url: '<?= base_url('patient/get-statistics') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#totalPatients').text(data.total_patients || 0);
            $('#activePatients').text(data.active_patients || 0);
            $('#newPatients').text(data.new_patients || 0);
            $('#monthlyPatients').text(data.monthly_patients || 0);
        },
        error: function() {
            $('#totalPatients').text('0');
            $('#activePatients').text('0');
            $('#newPatients').text('0');
            $('#monthlyPatients').text('0');
        }
    });
}

// Export functions
function exportToPDF() {
    if (patientsTable) {
        patientsTable.button('.btn-pdf').trigger();
    }
}

function printPatients() {
    if (patientsTable) {
        patientsTable.button('.btn-print').trigger();
    }
}

function exportToCSV() {
    if (patientsTable) {
        patientsTable.button('.btn-csv').trigger();
    }
}

// Delete patient function
function deletePatient(patientId) {
    confirmDelete('<?= base_url('patient') ?>/' + patientId, patientI18n.deleteConfirm);
}

// Permissions mirrored from backend so visible row actions have matching modal support
var permissions = {
    patients: {
        view: <?= has_permission('patients', 'view') ? 'true' : 'false' ?>,
        edit: <?= has_permission('patients', 'edit') ? 'true' : 'false' ?>,
        delete: <?= has_permission('patients', 'delete') ? 'true' : 'false' ?>,
        create: <?= has_permission('patients', 'create') ? 'true' : 'false' ?>
    },
    examinations: {
        create: <?= has_permission('examinations', 'create') ? 'true' : 'false' ?>
    },
    treatments: {
        create: <?= has_permission('treatments', 'create') ? 'true' : 'false' ?>
    }
};
</script>

<?= view('patient/partials/create_form_script') ?>

<?= $this->endSection() ?>
