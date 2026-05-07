<?php
$contextPatient = $context_patient ?? $selected_patient ?? null;
$contextName = trim((string) (($contextPatient['first_name'] ?? '') . ' ' . ($contextPatient['last_name'] ?? '')));
$contextNumber = (string) ($contextPatient['patient_id'] ?? ('#' . (int) ($contextPatient['id'] ?? 0)));
$contextPhone = (string) ($contextPatient['phone'] ?? '');
?>
<?php if (!empty($contextPatient['id'])): ?>
    <div class="mb-5 rounded-2xl border border-sky-100 bg-sky-50/80 p-4">
        <div class="flex items-start gap-3">
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-700">
                <i class="fas fa-user"></i>
            </span>
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-sky-700"><?= esc(lang('UI.currentPatient')) ?></p>
                <p class="mt-1 truncate text-sm font-bold text-slate-950">
                    <?= esc($contextName !== '' ? $contextName : lang('UI.patientUnknown')) ?>
                    <span class="font-mono text-slate-500" dir="ltr">· <?= esc($contextNumber) ?></span>
                </p>
                <?php if ($contextPhone !== ''): ?>
                    <p class="mt-0.5 text-xs text-slate-500" dir="ltr"><?= esc($contextPhone) ?></p>
                <?php endif; ?>
                <p class="mt-2 text-xs font-medium text-slate-500"><?= esc(lang('UI.actionLinkedToCurrentPatient')) ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>
