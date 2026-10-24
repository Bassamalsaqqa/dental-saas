<?= $this->extend('layouts/main_auth') ?>
<?= $this->section('content') ?>
<?php $tr = static fn(string $key) => lang('InvoiceCorrections.' . $key); $invoiceId = (int) $invoice['id']; ?>
<?= $this->include('finance/corrections/styles') ?>
<main class="ic-wrap" dir="<?= service('request')->getLocale() === 'ar' ? 'rtl' : 'ltr' ?>">
<h1><?= esc($tr('title')) ?></h1>
<p class="ic-help"><?= esc($tr('intro')) ?></p>
<div class="ic-note ic-warning"><?= esc($tr('operationalNotice')) ?></div>
<?php if (session()->getFlashdata('correction_error')): ?><div class="ic-note ic-error" role="alert"><?= esc(session()->getFlashdata('correction_error')) ?></div><?php endif ?>
<?php if (session()->getFlashdata('correction_success')): ?><div class="ic-note" role="status"><?= esc(session()->getFlashdata('correction_success')) ?></div><?php endif ?>
<section class="ic-card" aria-labelledby="ic-original">
<h2 id="ic-original"><?= esc($tr('invoice')) ?> <bdi><?= esc($invoice['transaction_id'] ?? $invoiceId) ?></bdi></h2>
<p class="ic-help"><?= esc($tr('accountingBasis')) ?></p>
<dl class="ic-grid">
<?php foreach (['gross'=>'total_amount', 'credited'=>'credited_base', 'remaining'=>'remaining_base', 'patientCredit'=>'patient_credit_base', 'cash'=>'net_cash_base'] as $label=>$key): ?>
<div><dt><?= esc($tr($label)) ?></dt><dd><bdi><?= esc($position[$key] ?? '0.00') ?></bdi></dd></div>
<?php endforeach ?>
<div><dt><?= esc($tr('currency')) ?></dt><dd><bdi><?= esc($invoice['currency'] ?? '') ?> / <?= esc($invoice['exchange_rate'] ?? '') ?></bdi></dd></div>
</dl>
</section>
<?php if ($canWrite): ?>
<form class="ic-card" method="post" action="<?= esc(site_url('finance/' . $invoiceId . '/corrections/preview'), 'attr') ?>">
<?= csrf_field() ?><input type="hidden" name="idempotency_key" value="<?= esc($idempotencyKey, 'attr') ?>">
<h2><?= esc($tr('preview')) ?></h2>
<label for="ic-amount"><?= esc($tr('amount')) ?></label>
<input id="ic-amount" name="amount_original" type="text" inputmode="decimal" pattern="[0-9]+([.][0-9]{1,2})?" maxlength="20" value="<?= esc(old('amount_original', ''), 'attr') ?>" dir="ltr" required class="v3-input">
<label for="ic-reason"><?= esc($tr('reason')) ?></label>
<textarea id="ic-reason" name="reason" minlength="3" maxlength="2000" aria-describedby="ic-reason-help" required class="v3-textarea"><?= esc(old('reason', '')) ?></textarea>
<p id="ic-reason-help" class="ic-help"><?= esc($tr('reasonHelp')) ?></p>
<div class="ic-actions"><button class="ic-button v3-btn v3-btn--primary" type="submit"><?= esc($tr('previewAction')) ?></button></div>
</form>
<?php endif ?>
<section class="ic-card"><h2><?= esc($tr('history')) ?></h2>
<?php if (!$corrections): ?><p><?= esc($tr('empty')) ?></p><?php else: ?>
<div class="ic-scroll"><table class="ic-table"><thead><tr><th><?= esc($tr('number')) ?></th><th><?= esc($tr('amountLabel')) ?></th><th><?= esc($tr('date')) ?></th><th><?= esc($tr('document')) ?></th></tr></thead><tbody>
<?php foreach ($corrections as $correction): ?><tr><td><bdi><?= esc($correction['document_number']) ?></bdi></td><td><bdi><?= esc($correction['amount_original']) ?> <?= esc($correction['currency']) ?></bdi></td><td><bdi><?= esc($correction['issued_at']) ?></bdi></td><td><a href="<?= esc(site_url('finance/corrections/' . (int) $correction['id']), 'attr') ?>"><?= esc($tr('view')) ?></a></td></tr><?php endforeach ?>
</tbody></table></div><?php endif ?>
</section>
<?php if ($canWrite): ?>
<form class="ic-card" method="post" action="<?= esc(site_url('finance/' . $invoiceId . '/corrections/link-replacement'), 'attr') ?>">
<?= csrf_field() ?><h2><?= esc($tr('replacement')) ?></h2><p class="ic-help"><?= esc($tr('replacementHelp')) ?></p>
<p><a href="<?= esc(site_url('finance/create?patient_id=' . (int) ($invoice['patient_id'] ?? 0)), 'attr') ?>"><?= esc($tr('createReplacement')) ?></a></p>
<label for="ic-replacement"><?= esc($tr('replacementId')) ?></label><input id="ic-replacement" type="text" inputmode="numeric" name="target_finance_id" pattern="[0-9]+" maxlength="18" required class="v3-input">
<label class="ic-check"><input type="checkbox" name="confirm" value="1" required class="v3-checkbox"><span><?= esc($tr('linkConfirm')) ?></span></label>
<button class="ic-button v3-btn v3-btn--primary" type="submit"><?= esc($tr('linkAction')) ?></button>
</form><?php endif ?>
<p class="ic-help"><?= esc($tr('separateRefund')) ?></p><div class="ic-actions"><a href="<?= esc(site_url('finance/' . $invoiceId), 'attr') ?>"><?= esc($tr('back')) ?></a></div>
</main>
<?= $this->endSection() ?>
