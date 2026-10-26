<?php helper('asset');include APPPATH.'Views/layouts/partials/studio_icons.php'; ?>
<?= $this->extend('layouts/studio_control_plane') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-platform-billing.css') ?>?v=<?= esc(asset_version('assets/css/studio-platform-billing.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php $statusKey=(string)($invoice['status']??'draft');$statusLabel=lang('Studio.platformInvoiceStatus_'.$statusKey);if($statusLabel==='Studio.platformInvoiceStatus_'.$statusKey)$statusLabel=$statusKey;$lineItems=json_decode($invoice['items_json']??'[]',true)?:[];$currency=(string)($invoice['currency']??'USD');$canDelete=in_array($statusKey,['draft','void','cancelled'],true); ?>
<header class="page-title-row">
<h1>
<?= esc($invoice['invoice_number']??lang('UI.cpInvoices')) ?>
</h1>
<nav class="platform-billing-actions">
<a class="button secondary" href="<?= base_url('controlplane/invoices') ?>">
<?= esc(lang('Studio.back')) ?>
</a>
<button class="button secondary" type="button" onclick="window.print()">
<?= esc(lang('Studio.platformPrintDocument')) ?>
</button>
</nav>
</header>
<?php foreach(['error'=>'alert','success'=>'status'] as $key=>$role):if(session($key)): ?>
<p class="form-notice <?= $key==='error'?'form-notice--error':'' ?>" role="<?= $role ?>">
<?= esc(session($key)) ?>
</p>
<?php endif;endforeach; ?>
<div class="platform-invoice-detail">
<div class="platform-invoice-document">
<section class="form-section">
<header class="platform-billing-section-heading">
<div>
<h2>
<?= esc($clinic['name']??lang('Studio.platformClinic')) ?>
</h2>
<p>#<?= (int)($clinic['id']??$invoice['clinic_id']) ?> · <?= esc(lang('Studio.platformSubscriptionBilling')) ?>
</p>
</div>
<span class="platform-billing-status" data-status="<?= esc($statusKey,'attr') ?>">
<?= esc($statusLabel) ?>
</span>
</header>
<dl class="platform-invoice-facts">
<?php foreach(['issue_date'=>'platformIssueDate','due_date'=>'platformDueDate','paid_at'=>'platformPaidAt'] as $key=>$label): ?>
<div>
<dt>
<?= esc(lang('Studio.'.$label)) ?>
</dt>
<dd>
<?= esc($invoice[$key]??lang('ActivityLog.notAvailable')) ?>
</dd>
</div>
<?php endforeach; ?>
</dl>
<table class="studio-grid platform-document-table" data-grid-widths="46,14,20,20" aria-label="<?= esc(lang('Studio.platformInvoiceItems'),'attr') ?>">
<thead>
<tr>
<?php foreach(['platformItemDescription','platformQuantity','platformUnitPrice','platformInvoiceTotal'] as $key): ?>
<th scope="col">
<?= esc(lang('Studio.'.$key)) ?>
</th>
<?php endforeach; ?>
</tr>
</thead>
<tbody>
<?php foreach($lineItems as $item): ?>
<tr>
<td>
<?= esc($item['description']??'') ?>
</td>
<td>
<?= esc($item['quantity']??0) ?>
</td>
<td dir="ltr">
<?= number_format((float)($item['unit_price']??0),2) ?>
</td>
<td dir="ltr">
<?= number_format(($item['quantity']??0)*($item['unit_price']??0),2) ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<dl class="platform-invoice-totals">
<div>
<dt>
<?= esc(lang('Studio.platformSubtotal')) ?>
</dt>
<dd dir="ltr">
<?= number_format((float)($invoice['subtotal']??0),2) ?>
</dd>
</div>
<div>
<dt>
<?= esc(lang('Studio.platformTaxAmount')) ?> (<?= number_format((float)($invoice['tax_percentage']??0),2) ?>%)</dt>
<dd dir="ltr">
<?= number_format((float)($invoice['tax_amount']??0),2) ?>
</dd>
</div>
<div>
<dt>
<?= esc(lang('Studio.platformInvoiceTotal')) ?>
</dt>
<dd dir="ltr">
<?= esc($currency) ?> <?= number_format((float)($invoice['total_amount']??0),2) ?>
</dd>
</div>
</dl>
<?php if(!empty($invoice['notes'])): ?>
<div class="platform-document-notes">
<h3>
<?= esc(lang('Studio.platformPublicNotes')) ?>
</h3>
<p>
<?= esc($invoice['notes']) ?>
</p>
</div>
<?php endif; ?>
</section>
<section class="form-section">
<header>
<h2>
<?= esc(lang('Studio.platformPaymentHistory')) ?>
</h2>
</header>
<?php if(empty($payments)): ?>
<p class="platform-billing-empty">
<?= esc(lang('Studio.platformNoPayments')) ?>
</p>
<?php else: ?>
<table class="studio-grid" data-grid-widths="22,22,28,28" aria-label="<?= esc(lang('Studio.platformPaymentHistory'),'attr') ?>">
<thead>
<tr>
<?php foreach(['platformPaymentAmount','platformPaymentMethod','platformPaidAt','platformPaymentReference'] as $key): ?>
<th scope="col">
<?= esc(lang('Studio.'.$key)) ?>
</th>
<?php endforeach; ?>
</tr>
</thead>
<tbody>
<?php foreach($payments as $payment): ?>
<tr>
<td dir="ltr">
<?= esc($currency) ?> <?= number_format((float)($payment['amount']??0),2) ?>
</td>
<td>
<?= esc($payment['payment_method']??'manual') ?>
</td>
<td>
<?= esc($payment['paid_at']??'') ?>
</td>
<td>
<?= esc($payment['transaction_reference']??'') ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</section>
</div>
<aside class="platform-invoice-sidebar">
<section class="form-section">
<h2>
<?= esc(lang('Studio.platformInvoiceActions')) ?>
</h2>
<form method="post" action="<?= base_url('controlplane/invoices/'.(int)$invoice['id'].'/email') ?>">
<?= csrf_field() ?>
<button class="button secondary" type="submit" data-billing-review="email">
<?= esc(lang('Studio.platformLogEmailAction')) ?>
</button>
</form>
<p class="platform-billing-explanation">
<?= esc(lang('Studio.platformEmailNotSending')) ?>
</p>
<?php if($canDelete): ?>
<form method="post" action="<?= base_url('controlplane/invoices/'.(int)$invoice['id'].'/delete') ?>">
<?= csrf_field() ?>
<button class="button destructive" type="submit" data-billing-review="delete">
<?= esc(lang('Studio.platformDeleteInvoice')) ?>
</button>
</form>
<?php else: ?>
<p class="platform-billing-explanation">
<?= esc(lang('Studio.platformInvoiceDeleteRestricted')) ?>
</p>
<?php endif; ?>
</section>
<?php if($statusKey!=='paid'): ?>
<section class="form-section">
<h2>
<?= esc(lang('Studio.platformRecordAndMarkPaid')) ?>
</h2>
<p class="platform-billing-explanation">
<?= esc(lang('Studio.platformPaymentBehavior')) ?>
</p>
<form class="studio-form" action="<?= base_url('controlplane/invoices/'.(int)$invoice['id'].'/mark-paid') ?>" method="post" data-billing-payment>
<?= csrf_field() ?>
<div class="form-field">
<label for="payment-amount">
<?= esc(lang('Studio.platformPaymentAmount')) ?> (<?= esc($currency) ?>)</label>
<input id="payment-amount" type="number" name="payment_amount" step="0.01" value="<?= esc($invoice['total_amount']??0,'attr') ?>">
</div>
<div class="form-field">
<label for="payment-method">
<?= esc(lang('Studio.platformPaymentMethod')) ?>
</label>
<select id="payment-method" name="payment_method">
<?php foreach(['bank_transfer'=>'bankTransfer','card'=>'card','cash'=>'cash','check'=>'check','other'=>'other'] as $value=>$key): ?>
<option value="<?= $value ?>">
<?= esc(lang('Finance.'.$key)) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="form-field">
<label for="payment-reference">
<?= esc(lang('Studio.platformPaymentReference')) ?>
</label>
<input id="payment-reference" name="transaction_reference">
</div>
<div class="form-field">
<label for="payment-notes">
<?= esc(lang('Finance.notes')) ?>
</label>
<textarea rows="3" id="payment-notes" name="payment_notes">
</textarea>
</div>
<button class="button primary" type="submit" data-billing-review="payment">
<?= esc(lang('Studio.platformRecordAndMarkPaid')) ?>
</button>
</form>
</section>
<?php endif; ?>
<?php if(!empty($invoice['private_notes'])): ?>
<section class="form-section platform-billing-private">
<h2>
<?= esc(lang('Studio.platformPrivateNotes')) ?>
</h2>
<p>
<?= esc($invoice['private_notes']) ?>
</p>
</section>
<?php endif; ?>
</aside>
</div>
<dialog id="platform-billing-confirm" class="help-dialog" aria-labelledby="billing-confirm-title">
<header>
<h2 id="billing-confirm-title">
</h2>
<button class="icon-button" type="button" data-close aria-label="<?= esc(lang('Studio.close'),'attr') ?>">
<?= $studioIcon('close') ?>
</button>
</header>
<div class="platform-billing-confirm-body">
<p data-billing-confirm-help>
</p>
<dl data-billing-confirm-facts>
</dl>
<div class="form-field" data-billing-delete-field hidden>
<label for="invoiceDeleteConfirm">
<?= esc(lang('Studio.platformTypeDelete')) ?>
</label>
<input id="invoiceDeleteConfirm" autocomplete="off">
</div>
</div>
<footer class="platform-billing-dialog-actions">
<button class="button secondary" type="button" data-close>
<?= esc(lang('Studio.cancel')) ?>
</button>
<button class="button primary" type="button" data-billing-proceed>
<?= esc(lang('Studio.confirm')) ?>
</button>
</footer>
</dialog>
<script id="platform-billing-config" type="application/json">
<?= json_encode(['identity'=>$invoice['invoice_number']??('#'.(int)$invoice['id']),'currency'=>$currency,'delete'=>lang('Studio.platformDeleteInvoice'),'deleteHelp'=>lang('Studio.platformDeleteInvoiceHelp'),'email'=>lang('Studio.platformLogEmailAction'),'emailHelp'=>lang('Studio.platformEmailNotSending'),'payment'=>lang('Studio.platformRecordAndMarkPaid'),'paymentHelp'=>lang('Studio.platformPaymentBehavior'),'amount'=>lang('Studio.platformPaymentAmount'),'method'=>lang('Studio.platformPaymentMethod'),'reference'=>lang('Studio.platformPaymentReference')],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>
</script>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/studio-platform-billing.js') ?>?v=<?= esc(asset_version('assets/js/studio-platform-billing.js')) ?>" defer>
</script>
<?= $this->endSection() ?>
