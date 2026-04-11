<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$isRtl = !empty($is_rtl);
$doctorName = trim(($prescription['creator_first_name'] ?? '') . ' ' . ($prescription['creator_last_name'] ?? ''));
$patientName = trim(($prescription['first_name'] ?? '') . ' ' . ($prescription['last_name'] ?? ''));
$patientPhone = trim((string) ($prescription['phone'] ?? '')) !== '' ? $prescription['phone'] : lang('Prescription.na');
$patientEmail = trim((string) ($prescription['email'] ?? '')) !== '' ? $prescription['email'] : lang('Prescription.na');
$prescriptionReference = $prescription['prescription_id'] ?? ('RX-' . str_pad((string) ($prescription['id'] ?? '0'), 6, '0', STR_PAD_LEFT));

$medicines = json_decode((string) ($prescription['medication_name'] ?? ''), true);
if (!is_array($medicines)) {
    $medicines = [];
}
if (isset($medicines['name']) || isset($medicines['dosage']) || isset($medicines['frequency']) || isset($medicines['duration'])) {
    $medicines = [$medicines];
}

$medicineCount = count($medicines);
$statusKey = strtolower((string) ($prescription['status'] ?? 'pending'));
$statusConfig = [
    'active' => ['tone' => 'bg-teal-100 text-teal-800', 'label' => lang('Prescription.status_active')],
    'expired' => ['tone' => 'bg-amber-100 text-amber-800', 'label' => lang('Prescription.status_expired')],
    'cancelled' => ['tone' => 'bg-slate-200 text-slate-700', 'label' => lang('Prescription.status_cancelled')],
    'pending' => ['tone' => 'bg-blue-100 text-blue-800', 'label' => lang('Prescription.status_pending')],
];
$statusMeta = $statusConfig[$statusKey] ?? $statusConfig['pending'];

$summaryMeta = [
    ['label' => lang('Prescription.issued_on'), 'value' => !empty($prescription['prescribed_date']) ? formatDate($prescription['prescribed_date']) : lang('Prescription.na')],
    ['label' => lang('Prescription.expires_on'), 'value' => !empty($prescription['expiry_date']) ? formatDate($prescription['expiry_date']) : lang('Prescription.na')],
    ['label' => lang('Prescription.created_by'), 'value' => $doctorName !== '' ? $doctorName : lang('Prescription.unknown')],
    ['label' => lang('Prescription.medicines'), 'value' => lang('Prescription.medicine_count_label', ['count' => $medicineCount])],
];
?>

<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="space-y-8">
            <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
                <div>
                    <div class="mb-2 flex flex-wrap items-center gap-2 text-xs uppercase tracking-[0.18em] text-slate-400">
                        <span><?= lang('Prescription.all_prescriptions') ?></span>
                        <span>/</span>
                        <span class="text-teal-700"><?= esc($prescriptionReference) ?></span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="font-headline text-3xl font-black tracking-tight text-cyan-950"><?= lang('Prescription.details_title') ?></h1>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] <?= esc($statusMeta['tone']) ?>">
                            <?= esc($statusMeta['label']) ?>
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-slate-500"><?= lang('Prescription.prescription_summary_help') ?></p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <?php if (has_permission('prescriptions', 'view')): ?>
                        <a href="<?= base_url('prescription/' . $prescription['id'] . '/print') ?>" target="_blank" class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-300">
                            <i class="fas fa-print text-sm"></i>
                            <span><?= lang('Prescription.print_prescription') ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if (has_permission('prescriptions', 'edit')): ?>
                        <a href="<?= base_url('prescription/' . $prescription['id'] . '/edit' . (!empty($return_to) ? '?return_to=' . urlencode($return_to) : '')) ?>" class="inline-flex items-center gap-2 rounded-lg bg-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-300">
                            <i class="fas fa-edit text-sm"></i>
                            <span><?= lang('Prescription.edit_prescription') ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if (has_permission('prescriptions', 'delete')): ?>
                        <button type="button" onclick="deletePrescription(<?= $prescription['id'] ?>)" class="inline-flex items-center gap-2 rounded-lg bg-red-100 px-5 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-200">
                            <i class="fas fa-trash text-sm"></i>
                            <span><?= lang('Prescription.delete_prescription') ?></span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                <div class="space-y-6 xl:col-span-4">
                    <section class="rounded-xl bg-slate-100 p-6">
                        <h2 class="mb-6 text-xs font-bold uppercase tracking-[0.2em] text-slate-400"><?= lang('Prescription.patient_profile') ?></h2>
                        <div class="mb-6 flex items-center gap-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-white text-xl font-black text-cyan-900 shadow-sm">
                                <?= esc(mb_substr($patientName !== '' ? $patientName : 'P', 0, 1)) ?>
                            </div>
                            <div>
                                <p class="text-lg font-bold text-cyan-950"><?= esc($patientName !== '' ? $patientName : lang('Prescription.na')) ?></p>
                                <p class="text-sm text-slate-500"><?= lang('Prescription.patient_id') ?>: <?= esc($prescription['patient_id'] ?? lang('Prescription.na')) ?></p>
                            </div>
                        </div>

                        <div class="space-y-4 border-t border-slate-200 pt-6">
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="text-slate-400"><?= lang('Prescription.phone') ?></span>
                                <span class="font-medium text-cyan-900"><?= esc($patientPhone) ?></span>
                            </div>
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="text-slate-400"><?= lang('Prescription.email') ?></span>
                                <span class="max-w-[65%] break-words text-right font-medium text-cyan-900"><?= esc($patientEmail) ?></span>
                            </div>
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="text-slate-400"><?= lang('Prescription.status') ?></span>
                                <span class="font-medium text-cyan-900"><?= esc($statusMeta['label']) ?></span>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-xl bg-slate-100 p-6">
                        <h2 class="mb-6 text-xs font-bold uppercase tracking-[0.2em] text-slate-400"><?= lang('Prescription.rx_metadata') ?></h2>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                            <?php foreach ($summaryMeta as $item): ?>
                                <div>
                                    <p class="mb-1 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400"><?= esc($item['label']) ?></p>
                                    <p class="text-sm font-semibold text-cyan-950"><?= esc($item['value']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>

                <div class="space-y-6 xl:col-span-8">
                    <section class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                            <h2 class="text-xl font-bold text-cyan-950"><?= lang('Prescription.medication_plan') ?></h2>
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-cyan-50 text-cyan-600">
                                <i class="fas fa-pills"></i>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left rtl:text-right">
                                <thead class="bg-slate-100 text-xs uppercase tracking-[0.16em] text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4 font-normal"><?= lang('Prescription.medicine_name') ?></th>
                                        <th class="px-6 py-4 font-normal"><?= lang('Prescription.dosage') ?></th>
                                        <th class="px-6 py-4 font-normal"><?= lang('Prescription.frequency') ?></th>
                                        <th class="px-6 py-4 font-normal"><?= lang('Prescription.duration') ?></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if (!empty($medicines)): ?>
                                        <?php foreach ($medicines as $medicine): ?>
                                            <tr class="align-top">
                                                <td class="px-6 py-5">
                                                    <div class="flex flex-col">
                                                        <span class="font-bold text-cyan-950"><?= esc($medicine['name'] ?? lang('Prescription.na')) ?></span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-5 text-sm text-cyan-900"><?= esc($medicine['dosage'] ?? lang('Prescription.na')) ?></td>
                                                <td class="px-6 py-5 text-sm text-cyan-900"><?= esc($medicine['frequency'] ?? lang('Prescription.na')) ?></td>
                                                <td class="px-6 py-5 text-sm font-semibold text-cyan-950"><?= esc($medicine['duration'] ?? lang('Prescription.na')) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500"><?= lang('Prescription.no_medication_information_available') ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="rounded-xl bg-slate-100 p-8">
                        <div class="mb-6 flex items-center gap-3">
                            <i class="fas fa-circle-info text-teal-700"></i>
                            <h2 class="text-xl font-bold text-cyan-950"><?= lang('Prescription.important_instructions') ?></h2>
                        </div>
                        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                            <div class="rounded-xl bg-white/70 p-5">
                                <p class="whitespace-pre-line text-sm leading-8 text-slate-700"><?= nl2br(esc($prescription['instructions'] ?? lang('Prescription.no_instructions_provided'))) ?></p>
                            </div>
                            <div class="overflow-hidden rounded-xl border border-red-200 bg-red-50 p-6 text-red-950">
                                <h3 class="mb-2 flex items-center gap-2 text-sm font-bold text-red-800">
                                    <i class="fas fa-triangle-exclamation text-sm text-red-600"></i>
                                    <span><?= lang('Prescription.emergency_notice') ?></span>
                                </h3>
                                <p class="text-sm leading-7 text-red-900"><?= lang('Prescription.emergency_notice_body') ?></p>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <footer class="flex flex-col gap-3 border-t border-slate-200 pt-6 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-4">
                    <span><?= lang('Prescription.prescription_id') ?>: <span class="font-mono"><?= esc($prescriptionReference) ?></span></span>
                    <span><?= lang('Prescription.created_by') ?>: <span class="font-semibold text-cyan-700"><?= esc($doctorName !== '' ? $doctorName : lang('Prescription.unknown')) ?></span></span>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <?php if (!empty($prescription['patient_id'])): ?>
                        <a href="<?= base_url('patient/' . $prescription['patient_id']) ?>" class="text-sm font-medium text-cyan-700 hover:text-cyan-800"><?= lang('Prescription.back_to_patient') ?></a>
                    <?php endif; ?>
                    <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('prescription') ?>" class="text-sm font-medium text-cyan-700 hover:text-cyan-800"><?= lang('Prescription.back_to_prescriptions') ?></a>
                </div>
            </footer>
        </div>
    </div>
</div>

<script>
function deletePrescription(id) {
    if (!confirm(<?= json_encode(lang('Prescription.delete_confirm_hard')) ?>)) {
        return;
    }

    const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
    const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';

    fetch(`<?= base_url() ?>prescription/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            [csrfHeader]: csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.csrf_token && window.refreshCsrfToken) {
            window.refreshCsrfToken(data.csrf_token);
        }

        if (data.success) {
            window.location.href = '<?= base_url('prescription') ?>';
            return;
        }

        const message = <?= json_encode(lang('Prescription.error_prefix')) ?> + (data.message || '');
        if (window.showToast) {
            window.showToast('error', <?= json_encode(lang('UI.error')) ?>, message);
        } else {
            alert(message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.showToast) {
            window.showToast('error', <?= json_encode(lang('UI.error')) ?>, <?= json_encode(lang('Prescription.delete_error_occurred')) ?>);
        } else {
            alert(<?= json_encode(lang('Prescription.delete_error_occurred')) ?>);
        }
    });
}
</script>
<?= $this->endSection() ?>
