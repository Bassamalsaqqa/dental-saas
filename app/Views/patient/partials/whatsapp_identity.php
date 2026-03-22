<?php
$wa = $patient_whatsapp ?? null;
$waOptout = $patient_whatsapp_optout ?? null;
$waStatus = $wa['resolution_status'] ?? null;
$waVerifiedAt = $wa['verified_at'] ?? null;
$waNumber = $wa['wa_number'] ?? '';
$isArabic = service('request')->getLocale() === 'ar';
$decode = static fn(string $value): string => json_decode('"' . $value . '"') ?? '';
$copy = $isArabic
    ? [
        'title' => $decode('\u0647\u0648\u064a\u0629 \u0648\u0627\u062a\u0633\u0627\u0628'),
        'optedOut' => $decode('\u062a\u0645 \u0625\u064a\u0642\u0627\u0641 \u0627\u0644\u0627\u0633\u062a\u0644\u0627\u0645'),
        'number' => $decode('\u0631\u0642\u0645 \u0648\u0627\u062a\u0633\u0627\u0628'),
        'status' => $decode('\u062d\u0627\u0644\u0629 \u0627\u0644\u062a\u062d\u0642\u0642'),
        'verifiedAt' => $decode('\u062a\u0645 \u0627\u0644\u062a\u062d\u0642\u0642 \u0641\u064a'),
        'update' => $decode('\u062a\u062d\u062f\u064a\u062b \u0627\u0644\u0631\u0642\u0645'),
        'blocked' => $decode('\u0645\u062d\u0638\u0648\u0631 \u0628\u0633\u0628\u0628 \u0625\u064a\u0642\u0627\u0641 STOP'),
        'empty' => $decode('\u0644\u0627 \u064a\u0648\u062c\u062f \u0631\u0642\u0645 \u0648\u0627\u062a\u0633\u0627\u0628 \u0645\u062d\u0641\u0648\u0638 \u0644\u0647\u0630\u0627 \u0627\u0644\u0645\u0631\u064a\u0636 \u0628\u0639\u062f.'),
        'add' => $decode('\u0625\u0636\u0627\u0641\u0629 \u0631\u0642\u0645 \u0648\u0627\u062a\u0633\u0627\u0628'),
        'modalTitleAdd' => $decode('\u0625\u0636\u0627\u0641\u0629 \u0631\u0642\u0645 \u0648\u0627\u062a\u0633\u0627\u0628'),
        'modalTitleEdit' => $decode('\u062a\u062d\u062f\u064a\u062b \u0631\u0642\u0645 \u0648\u0627\u062a\u0633\u0627\u0628'),
        'modalHelp' => $decode('\u0623\u062f\u062e\u0644 \u0627\u0644\u0631\u0642\u0645 \u0628\u0627\u0644\u0635\u064a\u063a\u0629 \u0627\u0644\u062f\u0648\u0644\u064a\u0629 \u0645\u062b\u0644 +970599999999.'),
        'cancel' => $decode('\u0625\u0644\u063a\u0627\u0621'),
        'save' => $decode('\u062d\u0641\u0638'),
        'saving' => $decode('\u062c\u0627\u0631\u064d \u0627\u0644\u062d\u0641\u0638...'),
        'saveFailed' => $decode('\u062a\u0639\u0630\u0631 \u062d\u0641\u0638 \u0631\u0642\u0645 \u0648\u0627\u062a\u0633\u0627\u0628.'),
        'required' => $decode('\u0631\u0642\u0645 \u0648\u0627\u062a\u0633\u0627\u0628 \u0645\u0637\u0644\u0648\u0628.'),
    ]
    : [
        'title' => 'WhatsApp Identity',
        'optedOut' => 'Opted Out',
        'number' => 'WhatsApp Number',
        'status' => 'Resolution Status',
        'verifiedAt' => 'Verified At',
        'update' => 'Update Number',
        'blocked' => 'Blocked by STOP opt-out',
        'empty' => 'No WhatsApp identity is stored for this patient yet.',
        'add' => 'Add WhatsApp Number',
        'modalTitleAdd' => 'Add WhatsApp Number',
        'modalTitleEdit' => 'Update WhatsApp Number',
        'modalHelp' => 'Enter the number in international format, for example +970599999999.',
        'cancel' => 'Cancel',
        'save' => 'Save',
        'saving' => 'Saving...',
        'saveFailed' => 'Failed to save WhatsApp identity.',
        'required' => 'WhatsApp number is required.',
    ];

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
            <i class="fab fa-whatsapp text-green-600 mr-2"></i><?= esc($copy['title']) ?>
        </h3>
        <?php if (!empty($waOptout)): ?>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">
                <?= esc($copy['optedOut']) ?>
            </span>
        <?php endif; ?>
    </div>
    <div class="px-6 py-4">
        <?php if (!empty($wa)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1"><?= esc($copy['number']) ?></label>
                    <p class="text-gray-900 font-medium" dir="ltr"><?= esc($waNumber) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1"><?= esc($copy['status']) ?></label>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold <?= $statusClass ?>">
                        <?= esc(ucfirst((string) $waStatus)) ?>
                    </span>
                </div>
                <?php if (!empty($waVerifiedAt)): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1"><?= esc($copy['verifiedAt']) ?></label>
                        <p class="text-gray-900"><?= esc(formatDateTime($waVerifiedAt)) ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($wa['bsuid'])): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">BSUID</label>
                        <p class="text-gray-900 font-mono text-xs" dir="ltr"><?= esc($wa['bsuid']) ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($wa['parent_bsuid'])): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Parent BSUID</label>
                        <p class="text-gray-900 font-mono text-xs" dir="ltr"><?= esc($wa['parent_bsuid']) ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($wa['username'])): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">WhatsApp Username</label>
                        <p class="text-gray-900 font-mono text-xs" dir="ltr">@<?= esc(ltrim((string) $wa['username'], '@')) ?></p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <?php if (has_permission('patients', 'edit')): ?>
                    <button type="button" class="inline-flex items-center px-3 py-2 rounded-lg border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 text-sm font-medium"
                            onclick="openPatientWhatsAppIdentityModal('<?= esc($waNumber, 'js') ?>')">
                        <i class="fas fa-pen mr-2"></i><?= esc($copy['update']) ?>
                    </button>
                <?php endif; ?>
                <?php if (!empty($waOptout)): ?>
                    <span class="inline-flex items-center px-3 py-2 rounded-lg border border-rose-200 bg-rose-50 text-rose-700 text-sm">
                        <i class="fas fa-ban mr-2"></i><?= esc($copy['blocked']) ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p class="text-sm text-gray-600"><?= esc($copy['empty']) ?></p>
            <?php if (has_permission('patients', 'edit')): ?>
                <div class="mt-4">
                    <button type="button" class="inline-flex items-center px-3 py-2 rounded-lg border border-green-200 bg-green-50 text-green-700 hover:bg-green-100 text-sm font-medium"
                            onclick="openPatientWhatsAppIdentityModal('')">
                        <i class="fas fa-plus mr-2"></i><?= esc($copy['add']) ?>
                    </button>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php if (has_permission('patients', 'edit')): ?>
<div id="patientWhatsAppIdentityModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 px-4">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl border border-slate-200 overflow-hidden" dir="<?= $isArabic ? 'rtl' : 'ltr' ?>">
        <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between gap-4">
            <div>
                <h4 id="patientWhatsAppIdentityModalTitle" class="text-lg font-semibold text-slate-900"><?= esc($copy['modalTitleAdd']) ?></h4>
                <p class="text-sm text-slate-500 mt-1"><?= esc($copy['modalHelp']) ?></p>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-600" onclick="closePatientWhatsAppIdentityModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <div>
                <label for="patientWhatsAppIdentityInput" class="block text-sm font-medium text-slate-700 mb-2"><?= esc($copy['number']) ?></label>
                <input id="patientWhatsAppIdentityInput" type="text" dir="ltr" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" placeholder="+970599999999">
                <p id="patientWhatsAppIdentityError" class="mt-2 hidden text-sm text-rose-600"></p>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex items-center justify-end gap-3">
            <button type="button" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 transition-colors" onclick="closePatientWhatsAppIdentityModal()"><?= esc($copy['cancel']) ?></button>
            <button type="button" id="patientWhatsAppIdentitySaveButton" class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition-colors" onclick="submitPatientWhatsAppIdentity()">
                <?= esc($copy['save']) ?>
            </button>
        </div>
    </div>
</div>
<script>
const patientWhatsAppIdentityModal = document.getElementById('patientWhatsAppIdentityModal');
const patientWhatsAppIdentityInput = document.getElementById('patientWhatsAppIdentityInput');
const patientWhatsAppIdentityError = document.getElementById('patientWhatsAppIdentityError');
const patientWhatsAppIdentityModalTitle = document.getElementById('patientWhatsAppIdentityModalTitle');
const patientWhatsAppIdentitySaveButton = document.getElementById('patientWhatsAppIdentitySaveButton');
const patientWhatsAppIdentityCopy = <?= json_encode($copy, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

function openPatientWhatsAppIdentityModal(currentValue) {
    patientWhatsAppIdentityInput.value = currentValue || '';
    patientWhatsAppIdentityModalTitle.textContent = currentValue ? patientWhatsAppIdentityCopy.modalTitleEdit : patientWhatsAppIdentityCopy.modalTitleAdd;
    patientWhatsAppIdentityError.textContent = '';
    patientWhatsAppIdentityError.classList.add('hidden');
    patientWhatsAppIdentitySaveButton.disabled = false;
    patientWhatsAppIdentitySaveButton.textContent = patientWhatsAppIdentityCopy.save;
    patientWhatsAppIdentityModal.classList.remove('hidden');
    patientWhatsAppIdentityModal.classList.add('flex');
    setTimeout(() => patientWhatsAppIdentityInput.focus(), 0);
}

function closePatientWhatsAppIdentityModal() {
    patientWhatsAppIdentityModal.classList.add('hidden');
    patientWhatsAppIdentityModal.classList.remove('flex');
}

function showPatientWhatsAppIdentityError(message) {
    patientWhatsAppIdentityError.textContent = message;
    patientWhatsAppIdentityError.classList.remove('hidden');
}

function submitPatientWhatsAppIdentity() {
    const numberValue = patientWhatsAppIdentityInput.value.trim();
    if (!numberValue) {
        showPatientWhatsAppIdentityError(patientWhatsAppIdentityCopy.required);
        patientWhatsAppIdentityInput.focus();
        return;
    }

    patientWhatsAppIdentitySaveButton.disabled = true;
    patientWhatsAppIdentitySaveButton.textContent = patientWhatsAppIdentityCopy.saving;
    patientWhatsAppIdentityError.textContent = '';
    patientWhatsAppIdentityError.classList.add('hidden');

    fetch('<?= base_url('patient/' . (int) ($patient['id'] ?? 0) . '/whatsapp-identity') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
            '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
        },
        body: `wa_number=${encodeURIComponent(numberValue)}`
    })
    .then((res) => res.json())
    .then((data) => {
        if (data && data.success) {
            window.location.reload();
            return;
        }

        patientWhatsAppIdentitySaveButton.disabled = false;
        patientWhatsAppIdentitySaveButton.textContent = patientWhatsAppIdentityCopy.save;
        showPatientWhatsAppIdentityError((data && data.message) ? data.message : patientWhatsAppIdentityCopy.saveFailed);
    })
    .catch(() => {
        patientWhatsAppIdentitySaveButton.disabled = false;
        patientWhatsAppIdentitySaveButton.textContent = patientWhatsAppIdentityCopy.save;
        showPatientWhatsAppIdentityError(patientWhatsAppIdentityCopy.saveFailed);
    });
}

patientWhatsAppIdentityModal?.addEventListener('click', (event) => {
    if (event.target === patientWhatsAppIdentityModal) {
        closePatientWhatsAppIdentityModal();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && patientWhatsAppIdentityModal && !patientWhatsAppIdentityModal.classList.contains('hidden')) {
        closePatientWhatsAppIdentityModal();
    }
});
</script>
<?php endif; ?>
