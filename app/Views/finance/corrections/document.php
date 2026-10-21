<?= $this->extend('layouts/main_auth') ?>
<?= $this->section('content') ?>
<?php $tr = static fn(string $key) => lang('InvoiceCorrections.' . $key); $correction = $document['correction']; $invoiceId = (int) $correction['finance_id']; ?>
<?= $this->include('finance/corrections/styles') ?>
<main class="ic-wrap" dir="<?= service('request')->getLocale() === 'ar' ? 'rtl' : 'ltr' ?>">
<h1><?= esc($tr('document')) ?></h1><div class="ic-note ic-warning"><?= esc($tr('operationalNotice')) ?></div>
<?php if (session()->getFlashdata('correction_success')): ?><p class="ic-note ic-noprint" role="status"><?= esc(session()->getFlashdata('correction_success')) ?></p><?php endif ?>
<section class="ic-card"><dl class="ic-grid">
<?php foreach (['number'=>'document_number','invoice'=>'finance_id','amountLabel'=>'amount_original','currency'=>'currency','date'=>'issued_at','actor'=>'created_by'] as $label=>$field): ?><div><dt><?= esc($tr($label)) ?></dt><dd><bdi><?= esc($correction[$field] ?? '') ?></bdi></dd></div><?php endforeach ?>
</dl><h2><?= esc($tr('reason')) ?></h2><p class="ic-reason"><?= esc($correction['reason']) ?></p><p class="ic-help"><?= esc($tr('audit')) ?>: <bdi><?= esc($correction['id']) ?></bdi></p></section>
<p class="ic-note"><?= esc($tr('preservation')) ?></p><p class="ic-help"><?= esc($tr('noEdit')) ?></p>
<section class="ic-card"><h2><?= esc($tr('applications')) ?></h2>
<?php if (!empty($document['applications'])): ?><div class="ic-scroll"><table class="ic-table"><thead><tr><th><?= esc($tr('target')) ?></th><th><?= esc($tr('amountLabel')) ?></th><th><?= esc($tr('date')) ?></th></tr></thead><tbody><?php foreach ($document['applications'] as $application): ?><tr><td><bdi><?= esc($application['target_finance_id']) ?></bdi></td><td><bdi><?= esc($application['amount_original']) ?> <?= esc($application['currency']) ?></bdi></td><td><bdi><?= esc($application['created_at']) ?></bdi><?php foreach (($application['return_history'] ?? []) as $returned): ?><p><?= esc($tr('returned')) ?> <bdi><?= esc($returned['created_at']) ?></bdi> ? <?= esc($returned['reason']) ?></p><?php endforeach ?></td></tr><?php endforeach ?></tbody></table></div><?php endif ?>
</section>
<?php if ($canWrite): foreach ($document['applications'] as $application): if (!empty($application['return_history'])) { continue; } ?>
<form class="ic-card ic-noprint" method="post" action="<?= esc(site_url('finance/corrections/' . (int) $correction['id'] . '/applications/' . (int) $application['id'] . '/return'), 'attr') ?>">
<?= csrf_field() ?><input type="hidden" name="idempotency_key" value="<?= esc(bin2hex(random_bytes(24)), 'attr') ?>">
<h3><?= esc($tr('returnApplication')) ?> <bdi>#<?= esc($application['id']) ?></bdi></h3>
<label><?= esc($tr('returnReason')) ?><textarea name="reason" maxlength="2000" required></textarea></label>
<label class="ic-check"><input type="checkbox" name="confirm" value="1" required><span><?= esc($tr('returnConfirm')) ?></span></label>
<button class="ic-button" type="submit"><?= esc($tr('returnApplication')) ?></button></form>
<?php endforeach; endif ?>
<?php if ($canWrite && !empty($document['source_hash'])): ?>
<form class="ic-card ic-noprint" method="post" action="<?= esc(site_url('finance/corrections/' . (int) $correction['id'] . '/allocate'), 'attr') ?>">
<?= csrf_field() ?><input type="hidden" name="source_hash" value="<?= esc($document['source_hash'], 'attr') ?>"><input type="hidden" name="idempotency_key" value="<?= esc($idempotencyKey, 'attr') ?>">
<h2><?= esc($tr('allocation')) ?></h2><p class="ic-help"><?= esc($tr('allocationHelp')) ?></p>
<label for="ic-target"><?= esc($tr('replacementId')) ?></label><input id="ic-target" type="text" name="target_finance_id" inputmode="numeric" pattern="[0-9]+" maxlength="18" required>
<label for="ic-apply-amount"><?= esc($tr('amount')) ?></label><input id="ic-apply-amount" name="amount_original" type="text" dir="ltr" inputmode="decimal" pattern="[0-9]+([.][0-9]{1,2})?" maxlength="20" required>
<label class="ic-check"><input type="checkbox" name="confirm" value="1" required><span><?= esc($tr('allocateConfirm')) ?></span></label>
<button class="ic-button" type="submit"><?= esc($tr('allocateAction')) ?></button>
</form><?php endif ?>
<div class="ic-noprint"><p class="ic-help"><?= esc($tr('printHelp')) ?></p><p class="ic-help"><?= esc($tr('separateRefund')) ?></p><div class="ic-actions"><a href="<?= esc(site_url('finance/' . $invoiceId . '/corrections'), 'attr') ?>"><?= esc($tr('returnCorrections')) ?></a><a href="<?= esc(site_url('finance/' . $invoiceId), 'attr') ?>"><?= esc($tr('back')) ?></a></div></div>
</main>
<?= $this->endSection() ?>
