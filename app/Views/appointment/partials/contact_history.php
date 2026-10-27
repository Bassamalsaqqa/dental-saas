<?php if (!empty($contact_history_enabled)): ?>
<?php
$retryData=session()->getFlashdata('appointment_contact_retry');
$retry=is_array($retryData) && ($retryData['clinic_id']??0)===(int)session()->get('active_clinic_id') && ($retryData['actor_id']??0)===(int)session()->get('user_id') && ($retryData['appointment_id']??0)===(int)$appointment['id'];
$formKey=$retry?$retryData['request_key']:$contact_request_key;
?>
<section id="contact-history" class="appointment-contact-workspace" aria-labelledby="contact-history-title">
    <h3 id="contact-history-title" class="appointment-contact-title"><?= esc(lang('AppointmentContact.title')) ?></h3>
    <p class="appointment-contact-help"><?= esc(lang('AppointmentContact.help')) ?></p>
    <?php if (has_permission('appointments','edit')): ?>
    <form class="appointment-contact-form" method="post" action="<?= base_url('appointment/'.(int)$appointment['id'].'/contact-attempt') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="request_key" value="<?= esc($formKey) ?>">
        <label class="appointment-contact-label" for="contact-note"><?= esc(lang('AppointmentContact.note')) ?></label>
        <textarea id="contact-note" name="note" required maxlength="2000" rows="3" class="appointment-contact-input" aria-describedby="contact-note-limit"><?= esc($retry ? $retryData['note'] : '') ?></textarea>
        <p id="contact-note-limit" class="appointment-contact-help"><?= esc(lang('AppointmentContact.limit')) ?></p>
        <button type="submit" class="button primary"><?= esc(lang('AppointmentContact.save')) ?></button>
    </form>
    <?php endif; ?>
    <ol class="appointment-contact-list">
    <?php foreach (($contact_history['items']??[]) as $record): ?>
        <li class="appointment-contact-entry">
            <p class="appointment-contact-meta"><time datetime="<?= esc(str_replace(' ','T',$record['created_at']).'Z') ?>"><?= esc($record['created_at']) ?> <?= esc(lang('AppointmentContact.utc')) ?></time> &middot; <?= esc(lang('AppointmentContact.actor')) ?> #<?= (int)$record['actor_id'] ?> &middot; #<?= (int)$record['id'] ?></p>
            <p class="appointment-contact-note"><?= esc($record['note']) ?></p>
        </li>
    <?php endforeach; ?>
    </ol>
    <?php if (empty($contact_history['items'])): ?><p class="appointment-contact-empty"><?= esc(lang('AppointmentContact.empty')) ?></p><?php endif; ?>
    <nav class="appointment-contact-navigation">
        <a href="<?= base_url('appointment/'.(int)$appointment['id']) ?>#contact-history"><?= esc(lang('AppointmentContact.latest')) ?></a>
        <?php if (!empty($contact_history['next'])): ?><a href="<?= base_url('appointment/'.(int)$appointment['id']).'?contact_before_id='.(int)$contact_history['next'] ?>#contact-history"><?= esc(lang('AppointmentContact.older')) ?></a><?php endif; ?>
    </nav>
</section>
<?php endif; ?>
