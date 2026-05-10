<?php
$contextPatient = $context_patient ?? $selected_patient ?? null;
$contextName = trim((string) (($contextPatient['first_name'] ?? '') . ' ' . ($contextPatient['last_name'] ?? '')));
$contextNumber = (string) ($contextPatient['patient_id'] ?? ('#' . (int) ($contextPatient['id'] ?? 0)));
$contextPhone = (string) ($contextPatient['phone'] ?? '');
$contextGender = strtolower((string) ($contextPatient['gender'] ?? ''));
$contextToneClass = in_array($contextGender, ['female', 'f'], true)
    ? 'context-patient-badge--female'
    : (in_array($contextGender, ['male', 'm'], true) ? 'context-patient-badge--male' : 'context-patient-badge--neutral');
?>
<?php if (!empty($contextPatient['id'])): ?>
    <div class="context-patient-badge <?= esc($contextToneClass) ?> mb-5 rounded-2xl border p-4">
        <div class="context-patient-badge__row flex items-start gap-3">
            <span class="context-patient-badge__icon inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-700">
                <i class="fas fa-user"></i>
            </span>
            <div class="context-patient-badge__body min-w-0 flex-1">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-sky-700"><?= esc(lang('UI.currentPatient')) ?></p>
                <p class="context-patient-badge__identity mt-1 flex min-w-0 flex-wrap items-baseline gap-x-1.5 gap-y-0.5 text-sm font-bold text-slate-950">
                    <span class="context-patient-badge__name min-w-0 truncate"><?= esc($contextName !== '' ? $contextName : lang('UI.patientUnknown')) ?></span>
                    <span class="context-patient-badge__number shrink-0 font-mono text-slate-500" dir="ltr">&middot; <?= esc($contextNumber) ?></span>
                </p>
                <?php if ($contextPhone !== ''): ?>
                    <p class="mt-0.5 text-xs text-slate-500"><span class="context-patient-badge__phone" dir="ltr"><?= esc($contextPhone) ?></span></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
