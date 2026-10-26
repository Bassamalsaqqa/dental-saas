<?php
$emailCheckUrl = $emailCheckUrl ?? base_url('users/check-email');
$allowExistingLink = $allowExistingLink ?? false;
?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const endpoint = '<?= esc($emailCheckUrl) ?>';
    const allowExistingLink = <?= json_encode((bool) $allowExistingLink) ?>;
    const existingAccountMessage = <?= json_encode(lang('UserRole.existingAccountRestricted'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const emailWords = <?= json_encode(['existing'=>lang('Studio.emailExistingUser'),'memberships'=>lang('Studio.emailMemberships'),'already'=>lang('Studio.emailAlreadyMember'),'link'=>lang('Studio.emailWillLink'),'unknownClinic'=>lang('Studio.emailUnknownClinic')],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>;
    const inputs = document.querySelectorAll('[data-user-email-check]');
    if (!inputs.length) {
        return;
    }

    const debounce = (fn, delay = 450) => {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn(...args), delay);
        };
    };

    const togglePasswordFields = (fields, disabled) => {
        fields.forEach(field => {
            if (!field) return;
            field.disabled = disabled;
            field.required = !disabled;
            if (disabled) {
                field.classList.add('opacity-50', 'pointer-events-none');
            } else {
                field.classList.remove('opacity-50', 'pointer-events-none');
            }
        });
    };

    const setNoteText = (noteEl, message) => {
        if (!noteEl) return;
        const textEl = noteEl.querySelector('[data-note-text]') ?? noteEl;
        textEl.textContent = message;
        noteEl.classList.remove('hidden');
    };

    const hideNote = (noteEl) => {
        if (!noteEl) return;
        noteEl.classList.add('hidden');
        const textEl = noteEl.querySelector('[data-note-text]') ?? noteEl;
        textEl.textContent = '';
    };

    const resetState = (noteEl, fields) => {
        togglePasswordFields(fields, false);
        hideNote(noteEl);
    };

    inputs.forEach(input => {
        const noteSelector = input.dataset.existingNoteTarget;
        const noteEl = noteSelector ? document.querySelector(noteSelector) : null;
        const passwordSelectors = (input.dataset.passwordTargets ?? '')
            .split(',')
            .map(sel => sel.trim())
            .filter(Boolean);
        const passwordFields = passwordSelectors
            .map(sel => document.querySelector(sel))
            .filter(Boolean);

        const describeMemberships = (memberships) => {
            if (!Array.isArray(memberships) || memberships.length === 0) {
                return '';
            }
            const names = memberships.map(m => m.clinic_name || emailWords.unknownClinic);
            return ' ' + emailWords.memberships + ' ' + names.join(', ') + '.';
        };

        const handleExisting = (payload) => {
            togglePasswordFields(passwordFields, true);
            if (!allowExistingLink) {
                input.setCustomValidity(existingAccountMessage);
                setNoteText(noteEl, existingAccountMessage);
                return;
            }
            if (!noteEl) return;
            const membershipDesc = describeMemberships(payload.memberships);
            let message = emailWords.existing + (payload.name ? ' (' + payload.name.trim() + ')' : '') + '.';
            if (membershipDesc) {
                message += membershipDesc;
            }
            if (payload.in_current_clinic) {
                message += ' ' + emailWords.already;
            } else {
                message += ' ' + emailWords.link;
            }
            setNoteText(noteEl, message);
        };

        const checkEmail = debounce(async () => {
            const email = input.value.trim();
            input.setCustomValidity('');
            if (!email) {
                resetState(noteEl, passwordFields);
                return;
            }

            try {
                const response = await fetch(`${endpoint}?email=${encodeURIComponent(email)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) {
                    resetState(noteEl, passwordFields);
                    return;
                }
                const payload = await response.json();
                if (input.value.trim() !== email) return;
                if (payload.exists) {
                    handleExisting(payload);
                } else {
                    resetState(noteEl, passwordFields);
                }
            } catch (error) {
                console.warn('Email existence check failed', error);
                resetState(noteEl, passwordFields);
            }
        });

        input.addEventListener('input', () => {
            input.setCustomValidity('');
            resetState(noteEl, passwordFields);
            checkEmail();
        });
        input.addEventListener('blur', checkEmail);
        if (input.value.trim()) {
            checkEmail();
        }
    });
});
</script>
