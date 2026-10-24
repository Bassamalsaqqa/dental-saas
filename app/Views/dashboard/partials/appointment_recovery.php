<?php $queue = is_array($queue ?? null) ? $queue : []; ?>
<section class="mb-6 rounded-[9px] border-[1.5px] border-[#C4B9A8] bg-[#F3EFE6] p-5" aria-labelledby="appointment-recovery-heading">
    <h2 id="appointment-recovery-heading" class="text-lg font-bold v3-text-ink"><?= esc(lang('AppointmentRecovery.title')) ?> <span class="v3-text-warning">(<?= (int)($queue['count'] ?? 0) ?>)</span></h2>
    <p class="mt-2 text-sm v3-text-muted"><?= esc(lang('AppointmentRecovery.scope')) ?> <bdi dir="ltr"><?= esc($queue['from'] ?? '') ?> &ndash; <?= esc($queue['through'] ?? '') ?></bdi></p>
    <p class="mt-1 text-sm v3-text-muted"><?= esc(lang('AppointmentRecovery.notice')) ?></p>
    <?php foreach (($queue['items'] ?? []) as $item): ?>
        <a class="mt-3 block rounded-[9px] border border-[#C4B9A8] bg-[#FCFBF7] p-3 text-sm hover:bg-[#E2DACB]" href="<?= esc(site_url('appointment/'.$item['id']), 'attr') ?>">
            <strong><?= esc(trim($item['first_name'].' '.$item['last_name'])) ?></strong>
            <span class="block"><bdi dir="ltr"><?= esc($item['appointment_date'].' '.substr($item['appointment_time'],0,5)) ?></bdi> &middot; <?= esc(lang('AppointmentRecovery.'.$item['status'])) ?></span>
        </a>
    <?php endforeach; ?>
    <?php if (empty($queue['items'])): ?><p class="mt-3 text-sm v3-text-muted"><?= esc(lang('AppointmentRecovery.empty')) ?></p><?php endif; ?>
    <div class="mt-4 flex flex-wrap gap-4 text-sm font-semibold text-[#0F6B63]">
        <a href="<?= esc(site_url('appointments?view=all&status=no_show'),'attr') ?>"><?= esc(lang('AppointmentRecovery.allNoShows')) ?></a>
        <a href="<?= esc(site_url('appointments?view=all&status=cancelled'),'attr') ?>"><?= esc(lang('AppointmentRecovery.allCancellations')) ?></a>
    </div>
</section>
