<?php helper('asset');include APPPATH.'Views/layouts/partials/studio_icons.php'; ?>
<?= $this->extend('layouts/studio_control_plane') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-platform-billing.css') ?>?v=<?= esc(asset_version('assets/css/studio-platform-billing.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-create-dialog.css') ?>?v=<?= esc(asset_version('assets/css/studio-create-dialog.css')) ?>"><?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php $filters=is_array($filters??null)?$filters:[];$invoices=is_array($invoices??null)?$invoices:[];$billingStatus=static function($value){$key='Studio.platformInvoiceStatus_'.(string)$value;$label=lang($key);return $label===$key?(string)$value:$label;}; ?>
<header class="page-title-row">
<h1>
<?= esc(lang('UI.cpInvoices')) ?>
</h1>
<a class="button primary" data-native-create="platform-invoice-create-dialog" href="<?= base_url('controlplane/invoices/create') ?>">
<?= $studioIcon('plus') ?>
<?= esc(lang('Studio.platformInvoiceCreate')) ?>
</a>
</header>
<?php foreach(['error'=>'alert','success'=>'status'] as $key=>$role):if(session($key)): ?>
<p class="form-notice <?= $key==='error'?'form-notice--error':'' ?>" role="<?= $role ?>">
<?= esc(session($key)) ?>
</p>
<?php endif;endforeach; ?>
<section class="form-section">
<form method="get" action="<?= base_url('controlplane/invoices') ?>" class="platform-invoice-filters">
<div class="form-field">
<label for="billing-clinic">
<?= esc(lang('Studio.platformClinic')) ?>
</label>
<select name="clinic_id" id="billing-clinic">
<option value="">
<?= esc(lang('Studio.platformAllClinics')) ?>
</option>
<?php foreach(($clinics??[]) as $clinic): ?>
<option value="<?= (int)$clinic['id'] ?>" <?= (int)($filters['clinic_id']??0)===(int)$clinic['id']?'selected':'' ?>>
<?= esc($clinic['name']) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="form-field">
<label for="billing-status">
<?= esc(lang('Studio.status')) ?>
</label>
<select id="billing-status" name="status">
<option value="">
<?= esc(lang('Studio.all')) ?>
</option>
<?php foreach(['draft','sent','paid','overdue','void','cancelled'] as $status): ?>
<option value="<?= $status ?>" <?= ($filters['status']??'')===$status?'selected':'' ?>>
<?= esc($billingStatus($status)) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<?php foreach(['from'=>'platformIssuedFrom','to'=>'platformIssuedTo'] as $key=>$label): ?>
<div class="form-field">
<label for="billing-<?= $key ?>">
<?= esc(lang('Studio.'.$label)) ?>
</label>
<input type="date" name="<?= $key ?>" id="billing-<?= $key ?>" value="<?= esc($filters[$key]??'','attr') ?>">
</div>
<?php endforeach; ?>
<button class="button primary" type="submit">
<?= esc(lang('Inventory.filter')) ?>
</button>
<a class="button secondary" href="<?= base_url('controlplane/invoices') ?>">
<?= esc(lang('Studio.reset')) ?>
</a>
</form>
<div class="platform-billing-tools">
<span>
<?= count($invoices) ?> <?= esc(lang('Studio.platformLoadedInvoices')) ?>
</span>
<nav>
<button class="button secondary" type="button" data-billing-export>
<?= esc(lang('Studio.platformExportLoaded')) ?>
</button>
<button class="button secondary" type="button" onclick="window.print()">
<?= esc(lang('Studio.platformPrintLoaded')) ?>
</button>
</nav>
</div>
<table class="studio-grid" id="platform-invoices" data-grid-widths="19,24,15,15,16,11" aria-label="<?= esc(lang('UI.cpInvoices'),'attr') ?>">
<thead>
<tr>
<?php foreach(['platformInvoiceNumber','platformClinic','platformIssueDate','platformDueDate','platformInvoiceTotal','status'] as $key): ?>
<th scope="col">
<?= esc(lang('Studio.'.$key)) ?>
</th>
<?php endforeach; ?>
</tr>
</thead>
<tbody>
<?php foreach($invoices as $invoice): ?>
<tr data-billing-record>
<td>
<a href="<?= base_url('controlplane/invoices/'.(int)$invoice['id']) ?>">
<?= esc($invoice['invoice_number']??('#'.(int)$invoice['id'])) ?>
</a>
</td>
<td>
<?= esc($invoice['clinic_name']??'') ?>
<small>
<?= esc($invoice['clinic_domain']??'') ?>
</small>
</td>
<td>
<?= esc($invoice['issue_date']??'') ?>
</td>
<td>
<?= esc($invoice['due_date']??'') ?>
</td>
<td dir="ltr">
<strong>
<?= esc($invoice['currency']??'USD') ?> <?= number_format((float)($invoice['total_amount']??0),2) ?>
</strong>
</td>
<td>
<span class="platform-billing-status" data-status="<?= esc($invoice['status']??'','attr') ?>">
<?= esc($billingStatus($invoice['status']??'')) ?>
</span>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php if(!$invoices): ?>
<p class="platform-billing-empty">
<?= esc(lang('Studio.emptyList')) ?>
</p>
<?php endif; ?>
<details class="platform-billing-legacy">
<summary>
<?= esc(lang('Studio.platformLegacySummary')) ?>
</summary>
<p>
<?= esc(lang('Studio.platformLegacySummaryHelp')) ?>
</p>
<dl>
<?php foreach(['outstanding'=>'platformLegacyOutstanding','revenue_month'=>'platformLegacyMonth','overdue'=>'platformLegacyOverdue'] as $key=>$label): ?>
<div>
<dt>
<?= esc(lang('Studio.'.$label)) ?>
</dt>
<dd>
<?= isset($stats[$key])?number_format((float)$stats[$key],$key==='overdue'?0:2):'—' ?>
</dd>
</div>
<?php endforeach; ?>
</dl>
</details>
</section>
<?php include APPPATH.'Views/layouts/partials/studio_icons.php'; ?><?= view('components/studio_native_create_dialog',['studioIcon'=>$studioIcon,'createId'=>'platform-invoice-create-dialog','createFormId'=>'invoiceForm','createTitle'=>lang('Studio.platformInvoiceCreate'),'createAction'=>base_url('controlplane/invoices'),'createSource'=>'studio_platform_invoice_create','createReturn'=>'controlplane/invoices','createFields'=>'control_plane/invoices/partials/studio_compose_fields','createData'=>$this->getData(),'createFullPage'=>base_url('controlplane/invoices/create'),'createSubmit'=>lang('Studio.platformInvoiceCreate')]) ?>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/studio-platform-billing.js') ?>?v=<?= esc(asset_version('assets/js/studio-platform-billing.js')) ?>" defer>
</script>
<?= $this->endSection() ?>
