<?php
$wa = $patient_whatsapp ?? null;
$waOptout = $patient_whatsapp_optout ?? null;
$waStatus = $wa['resolution_status'] ?? null;
$waVerifiedAt = $wa['verified_at'] ?? null;
$waNumber = $wa['wa_number'] ?? '';

$statusClass = 'bg-gray-100 text-gray-800';
if ($waStatus === 'verified') {
    $statusClass = 'bg-green-100 text-green-800';
} elseif ($waStatus === 'ambiguous') {
    $statusClass = 'bg-amber-100 text-amber-800';
} elseif ($waStatus === 'failed') {
    $statusClass = 'bg-rose-100 text-rose-800';
} elseif ($waStatus === 'pending') {
    $statusClass = 'bg-blue-100 text-blue-800';
}
?>
<div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between gap-3">
        <h3 class="text-lg font-semibold text-gray-800 inline-flex items-center">
            <i class="fab fa-whatsapp text-green-600 mr-2"></i>WhatsApp Identity
        </h3>
        <?php if (!empty($waOptout)): ?>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">
                Opted Out
            </span>
        <?php endif; ?>
    </div>
    <div class="px-6 py-4">
        <?php if (!empty($wa)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">WhatsApp Number</label>
                    <p class="text-gray-900 font-medium"><?= esc($waNumber) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Resolution Status</label>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold <?= $statusClass ?>">
                        <?= esc(ucfirst((string) $waStatus)) ?>
                    </span>
                </div>
                <?php if (!empty($waVerifiedAt)): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Verified At</label>
                        <p class="text-gray-900"><?= esc(formatDateTime($waVerifiedAt)) ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($wa['bsuid'])): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">BSUID</label>
                        <p class="text-gray-900 font-mono text-xs"><?= esc($wa['bsuid']) ?></p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <?php if (has_permission('patients', 'edit')): ?>
                    <button type="button" class="inline-flex items-center px-3 py-2 rounded-lg border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 text-sm font-medium"
                            onclick="openPatientWhatsAppIdentityPrompt('<?= esc($waNumber, 'js') ?>')">
                        <i class="fas fa-pen mr-2"></i>Update Number
                    </button>
                <?php endif; ?>
                <?php if (!empty($waOptout)): ?>
                    <span class="inline-flex items-center px-3 py-2 rounded-lg border border-rose-200 bg-rose-50 text-rose-700 text-sm">
                        <i class="fas fa-ban mr-2"></i>Blocked by STOP opt-out
                    </span>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p class="text-sm text-gray-600">No WhatsApp identity is stored for this patient yet.</p>
            <?php if (has_permission('patients', 'edit')): ?>
                <div class="mt-4">
                    <button type="button" class="inline-flex items-center px-3 py-2 rounded-lg border border-green-200 bg-green-50 text-green-700 hover:bg-green-100 text-sm font-medium"
                            onclick="openPatientWhatsAppIdentityPrompt('')">
                        <i class="fas fa-plus mr-2"></i>Add WhatsApp Number
                    </button>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php if (has_permission('patients', 'edit')): ?>
<script>
function openPatientWhatsAppIdentityPrompt(currentValue) {
    const nextValue = window.prompt(
        'Enter WhatsApp number in international format (example: +970599999999):',
        currentValue || ''
    );
    if (nextValue === null) {
        return;
    }

    savePatientWhatsAppIdentity(nextValue);
}

function savePatientWhatsAppIdentity(numberValue) {
    const csrfHeader = '<?= csrf_header() ?>';
    const csrfToken = '<?= csrf_hash() ?>';
    const body = `wa_number=${encodeURIComponent(numberValue)}`;

    fetch('<?= base_url('patient/' . (int) ($patient['id'] ?? 0) . '/whatsapp-identity') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
            [csrfHeader]: csrfToken
        },
        body: body
    })
    .then((res) => res.json())
    .then((data) => {
        if (data && data.success) {
            window.location.reload();
            return;
        }
        window.alert((data && data.message) ? data.message : 'Failed to save WhatsApp identity.');
    })
    .catch(() => {
        window.alert('Failed to save WhatsApp identity.');
    });
}
</script>
<?php endif; ?>
