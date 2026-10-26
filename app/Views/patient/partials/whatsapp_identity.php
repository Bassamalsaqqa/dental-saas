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

$embedOnly = !empty($embed_only);
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
<?php if (!$embedOnly): ?>
<div >
    <div >
        <h3 >
            <?= esc($copy['title']) ?>
        </h3>
        <?php if (!empty($waOptout)): ?>
            <span >
                <?= esc($copy['optedOut']) ?>
            </span>
        <?php endif; ?>
    </div>
    <div >
        <?php if (!empty($wa)): ?>
            <div class="patient-fields">
                <div>
                    <label ><?= esc($copy['number']) ?></label>
                    <p  dir="ltr"><?= esc($waNumber) ?></p>
                </div>
                <div>
                    <label ><?= esc($copy['status']) ?></label>
                    <span >
                        <?= esc(ucfirst((string) $waStatus)) ?>
                    </span>
                </div>
                <?php if (!empty($waVerifiedAt)): ?>
                    <div>
                        <label ><?= esc($copy['verifiedAt']) ?></label>
                        <p ><?= esc(formatDateTime($waVerifiedAt)) ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($wa['bsuid'])): ?>
                    <div>
                        <label >BSUID</label>
                        <p  dir="ltr"><?= esc($wa['bsuid']) ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($wa['parent_bsuid'])): ?>
                    <div>
                        <label >Parent BSUID</label>
                        <p  dir="ltr"><?= esc($wa['parent_bsuid']) ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($wa['username'])): ?>
                    <div>
                        <label >WhatsApp Username</label>
                        <p  dir="ltr">@<?= esc(ltrim((string) $wa['username'], '@')) ?></p>
                    </div>
                <?php endif; ?>
            </div>
            <div >
                <?php if (has_permission('patients', 'edit')): ?>
                    <button type="button"
                            onclick="openPatientWhatsAppIdentityModal('<?= esc($waNumber, 'js') ?>')">
                        <?= esc($copy['update']) ?>
                    </button>
                <?php endif; ?>
                <?php if (!empty($waOptout)): ?>
                    <span >
                        <?= esc($copy['blocked']) ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p ><?= esc($copy['empty']) ?></p>
            <?php if (has_permission('patients', 'edit')): ?>
                <div >
                    <button type="button"
                            onclick="openPatientWhatsAppIdentityModal('')">
                        <?= esc($copy['add']) ?>
                    </button>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if (has_permission('patients', 'edit')): ?>
<div id="patientWhatsAppIdentityModal" class="hidden">
    <div  dir="<?= $isArabic ? 'rtl' : 'ltr' ?>">
        <div >
            <div>
                <h4 id="patientWhatsAppIdentityModalTitle" ><?= esc($copy['modalTitleAdd']) ?></h4>
                <p ><?= esc($copy['modalHelp']) ?></p>
            </div>
            <button type="button"  onclick="closePatientWhatsAppIdentityModal()">
                <?= esc(lang('Studio.close')) ?>
            </button>
        </div>
        <div >
            <div>
                <label for="patientWhatsAppIdentityInput" ><?= esc($copy['number']) ?></label>
                <input id="patientWhatsAppIdentityInput" type="text" dir="ltr"  placeholder="+970599999999">
                <p id="patientWhatsAppIdentityError" class="hidden"></p>
            </div>
        </div>
        <div >
            <button type="button"  onclick="closePatientWhatsAppIdentityModal()"><?= esc($copy['cancel']) ?></button>
            <button type="button" id="patientWhatsAppIdentitySaveButton"  onclick="submitPatientWhatsAppIdentity()">
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
