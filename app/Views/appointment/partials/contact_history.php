<?php if (!empty($contact_history_enabled)): ?>
<?php
$retryData=session()->getFlashdata('appointment_contact_retry');
$retry=is_array($retryData) && ($retryData['clinic_id']??0)===(int)session()->get('active_clinic_id') && ($retryData['actor_id']??0)===(int)session()->get('user_id') && ($retryData['appointment_id']??0)===(int)$appointment['id'];
$formKey=$retry?$retryData['request_key']:$contact_request_key;
?>
<section id="contact-history" class="my-6 v3-radius border v3-border-default v3-bg-panel p-4 sm:p-6" aria-labelledby="contact-history-title">
    <h3 id="contact-history-title" class="text-lg font-bold"><?= esc(lang('AppointmentContact.title')) ?></h3>
    <p class="mt-2 text-sm v3-text-muted"><?= esc(lang('AppointmentContact.help')) ?></p>
    <?php if (has_permission('appointments','edit')): ?>
    <form class="mt-4 space-y-3" method="post" action="<?= base_url('appointment/'.(int)$appointment['id'].'/contact-attempt') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="request_key" value="<?= esc($formKey) ?>">
        <label class="block text-sm font-semibold" for="contact-note"><?= esc(lang('AppointmentContact.note')) ?></label>
        <textarea id="contact-note" name="note" required maxlength="2000" rows="3" class="w-full rounded-lg border v3-border-default p-3 v3-textarea" aria-describedby="contact-note-limit"><?= esc($retry ? $retryData['note'] : '') ?></textarea>
        <p id="contact-note-limit" class="text-xs v3-text-muted"><?= esc(lang('AppointmentContact.limit')) ?></p>
        <button type="submit" class="rounded-lg v3-bg-primary px-4 py-2 font-semibold v3-text-on-primary v3-btn v3-btn--primary"><?= esc(lang('AppointmentContact.save')) ?></button>
    </form>
    <?php endif; ?>
    <ol class="mt-5 space-y-4">
    <?php foreach (($contact_history['items']??[]) as $record): ?>
        <li class="v3-radius border v3-border-default p-3">
            <p class="text-xs v3-text-muted"><time datetime="<?= esc(str_replace(' ','T',$record['created_at']).'Z') ?>"><?= esc($record['created_at']) ?> <?= esc(lang('AppointmentContact.utc')) ?></time> &middot; <?= esc(lang('AppointmentContact.actor')) ?> #<?= (int)$record['actor_id'] ?> &middot; #<?= (int)$record['id'] ?></p>
            <p class="mt-2 whitespace-pre-wrap break-words text-sm"><?= esc($record['note']) ?></p>
        </li>
    <?php endforeach; ?>
    </ol>
    <?php if (empty($contact_history['items'])): ?><p class="mt-4 text-sm v3-text-muted"><?= esc(lang('AppointmentContact.empty')) ?></p><?php endif; ?>
    <nav class="mt-4 flex flex-wrap gap-4 text-sm font-semibold">
        <a href="<?= base_url('appointment/'.(int)$appointment['id']) ?>#contact-history"><?= esc(lang('AppointmentContact.latest')) ?></a>
        <?php if (!empty($contact_history['next'])): ?><a href="<?= base_url('appointment/'.(int)$appointment['id']).'?contact_before_id='.(int)$contact_history['next'] ?>#contact-history"><?= esc(lang('AppointmentContact.older')) ?></a><?php endif; ?>
    </nav>
</section>
<?php endif; ?>
