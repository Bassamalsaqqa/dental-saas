<?php $queue = is_array($queue ?? null) ? $queue : []; ?>
<section class="mb-6 rounded-2xl border border-amber-200 bg-white p-5" aria-labelledby="appointment-recovery-heading">
    <h2 id="appointment-recovery-heading" class="text-lg font-bold text-slate-900"><?= esc(lang('AppointmentRecovery.title')) ?> <span class="text-amber-700">(<?= (int)($queue['count'] ?? 0) ?>)</span></h2>
    <p class="mt-2 text-sm text-slate-600"><?= esc(lang('AppointmentRecovery.scope')) ?> <bdi dir="ltr"><?= esc($queue['from'] ?? '') ?> &ndash; <?= esc($queue['through'] ?? '') ?></bdi></p>
    <p class="mt-1 text-sm text-slate-600"><?= esc(lang('AppointmentRecovery.notice')) ?></p>
    <?php foreach (($queue['items'] ?? []) as $item): ?>
        <a class="mt-3 block rounded-lg border border-slate-200 p-3 text-sm hover:bg-slate-50" href="<?= esc(site_url('appointment/'.$item['id']), 'attr') ?>">
            <strong><?= esc(trim($item['first_name'].' '.$item['last_name'])) ?></strong>
            <span class="block"><bdi dir="ltr"><?= esc($item['appointment_date'].' '.substr($item['appointment_time'],0,5)) ?></bdi> &middot; <?= esc(lang('AppointmentRecovery.'.$item['status'])) ?></span>
        </a>
    <?php endforeach; ?>
    <?php if (empty($queue['items'])): ?><p class="mt-3 text-sm text-slate-500"><?= esc(lang('AppointmentRecovery.empty')) ?></p><?php endif; ?>
    <div class="mt-4 flex flex-wrap gap-4 text-sm font-semibold text-blue-700">
        <a href="<?= esc(site_url('appointments?view=all&status=no_show'),'attr') ?>"><?= esc(lang('AppointmentRecovery.allNoShows')) ?></a>
        <a href="<?= esc(site_url('appointments?view=all&status=cancelled'),'attr') ?>"><?= esc(lang('AppointmentRecovery.allCancellations')) ?></a>
    </div>
</section>
