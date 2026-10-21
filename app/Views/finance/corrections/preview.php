<?= $this->extend('layouts/main_auth') ?>
<?= $this->section('content') ?>
<?php
$tr = static fn(string $key) => lang('InvoiceCorrections.' . $key);
$invoice = $preview['invoice']; $invoiceId = (int) $invoice['id'];
$requiresApproval = !empty($preview['requires_manager_approval']);
$hasApproval = !empty($payload['approval_id']);
?>
<?= $this->include('finance/corrections/styles') ?>
<main class="ic-wrap" dir="<?= service('request')->getLocale() === 'ar' ? 'rtl' : 'ltr' ?>">
<h1><?= esc($tr('preview')) ?></h1><div class="ic-note ic-warning"><?= esc($tr('operationalNotice')) ?></div>
<section class="ic-card"><dl class="ic-grid">
<div><dt><?= esc($tr('invoice')) ?></dt><dd><bdi><?= esc($invoice['transaction_id'] ?? $invoiceId) ?></bdi></dd></div>
<div><dt><?= esc($tr('amountLabel')) ?></dt><dd><bdi><?= esc($payload['amount_original']) ?> <?= esc($invoice['currency'] ?? '') ?></bdi></dd></div>
<div><dt><?= esc($tr('remaining')) ?></dt><dd><bdi><?= esc($preview['position']['remaining_base'] ?? '') ?></bdi></dd></div>
<div><dt><?= esc($tr('patientCredit')) ?></dt><dd><bdi><?= esc($preview['position']['patient_credit_base'] ?? '') ?></bdi></dd></div>
</dl><h2><?= esc($tr('reason')) ?></h2><p class="ic-reason"><?= esc($payload['reason']) ?></p></section>
<p class="ic-note"><?= esc($tr('preservation')) ?></p>
<section class="ic-card"><h2><?= esc($tr('remaining')) ?> / <?= esc($tr('patientCredit')) ?></h2><p class="ic-help"><?= esc($tr('accountingBasis')) ?></p><dl class="ic-grid">
<?php foreach (['remaining'=>'outstanding','patientCredit'=>'patient_credit','cash'=>'cash_net'] as $label=>$key): ?><div><dt><?= esc($tr($label)) ?></dt><dd><bdi><?= esc($preview['projected_position'][$key] ?? '') ?></bdi></dd></div><?php endforeach ?>
</dl></section>

<?php if ($requiresApproval): ?>
<section class="ic-card"><h2><?= esc($tr('providerTitle')) ?></h2><p class="ic-help"><?= esc($tr('providerNotice')) ?></p>
<?php if (!empty($preview['provider_impact'])): ?><div class="ic-scroll"><table class="ic-table"><thead><tr><th><?= esc($tr('providerGross')) ?></th><th><?= esc($tr('providerShare')) ?></th><th><?= esc($tr('providerPaid')) ?></th><th><?= esc($tr('currency')) ?></th></tr></thead><tbody>
<?php foreach (($preview['provider_impact']['allocations'] ?? []) as $impact): if (!is_array($impact)) { continue; } ?>
<tr><td><bdi><?= esc($impact['gross_amount'] ?? $impact['gross'] ?? '') ?></bdi></td><td><bdi><?= esc($impact['provider_share_amount'] ?? $impact['provider_share'] ?? '') ?></bdi></td><td><bdi><?= esc($impact['provider_paid_amount'] ?? $impact['paid_amount'] ?? '') ?></bdi></td><td><bdi><?= esc($impact['currency'] ?? '') ?></bdi></td></tr>
<?php endforeach ?></tbody></table></div><?php endif ?>
<p class="ic-note ic-warning"><?= esc($tr($hasApproval ? 'approvalGranted' : 'approvalRequired')) ?></p>
</section><?php endif ?>
<?php $isApproval = $requiresApproval && !$hasApproval; if (!$isApproval || $canApprove): ?>
<form class="ic-card" method="post" action="<?= esc(site_url('finance/' . $invoiceId . '/corrections/' . ($isApproval ? 'approve' : 'issue')), 'attr') ?>">
<?= csrf_field() ?>
<?php foreach (['amount_original','reason','idempotency_key'] as $field): ?><input type="hidden" name="<?= esc($field, 'attr') ?>" value="<?= esc($payload[$field], 'attr') ?>"><?php endforeach ?>
<input type="hidden" name="source_hash" value="<?= esc($preview['source_hash'], 'attr') ?>">
<?php if ($hasApproval): ?><input type="hidden" name="approval_id" value="<?= esc($payload['approval_id'], 'attr') ?>"><?php endif ?>
<label class="ic-check"><input type="checkbox" name="confirm" value="1" required><span><?= esc($tr($isApproval ? 'approveConfirm' : 'issueConfirm')) ?></span></label>
<button class="ic-button" type="submit"><?= esc($tr($isApproval ? 'approveAction' : 'issueAction')) ?></button>
</form><?php endif ?>
<p class="ic-help"><?= esc($tr('snapshotNotice')) ?></p><div class="ic-actions"><a href="<?= esc(site_url('finance/' . $invoiceId . '/corrections'), 'attr') ?>"><?= esc($tr('returnCorrections')) ?></a></div>
</main>
<?= $this->endSection() ?>
