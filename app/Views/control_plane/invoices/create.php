<?php helper('asset');include APPPATH.'Views/layouts/partials/studio_icons.php'; ?>
<?= $this->extend('layouts/studio_control_plane') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-platform-billing.css') ?>?v=<?= esc(asset_version('assets/css/studio-platform-billing.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php helper('form');
    $lineItemsOld = [];
    $oldLineJson = old('line_items_json') ?? '';
    if ($oldLineJson) {
        $decoded = json_decode($oldLineJson, true);
        if (is_array($decoded) && !empty($decoded)) {
            $lineItemsOld = $decoded;
        }
    }
    if (empty($lineItemsOld)) {
        $lineItemsOld = [
            ['description' => '', 'quantity' => 1, 'unit_price' => 0],
        ];
    }
    $lineItemsJson = json_encode($lineItemsOld, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    $taxDefault = old('tax_percentage') ?? 0;
$billingErrors=array_values(is_array(session('errors'))?session('errors'):[]);foreach(['clinic_id','issue_date','due_date','tax_percentage','line_items_json'] as $key){$message=isset($validation)?$validation->getError($key):'';if($message)$billingErrors[]=$message;} ?>
<header class="page-title-row">
<h1>
<?= esc(lang('Studio.platformInvoiceCreate')) ?>
</h1>
<a class="button secondary" href="<?= base_url('controlplane/invoices') ?>">
<?= esc(lang('Studio.back')) ?>
</a>
</header>
<?php if($billingErrors||session('error')): ?>
<section class="form-notice form-notice--error" role="alert">
<h2>
<?= esc(lang('Studio.checkFields')) ?>
</h2>
<?php if(session('error')): ?>
<p>
<?= esc(session('error')) ?>
</p>
<?php endif; ?>
<ul>
<?php foreach($billingErrors as $message): ?>
<li>
<?= esc($message) ?>
</li>
<?php endforeach; ?>
</ul>
</section>
<?php endif; ?>
<form id="invoiceForm" class="studio-form" action="<?= base_url('controlplane/invoices') ?>" method="post" data-unsaved-form>
<?= csrf_field() ?><?= view('control_plane/invoices/partials/studio_compose_fields',$this->getData()) ?>
</form>
<dialog id="form-discard-dialog" class="help-dialog" aria-labelledby="discard-title">
<header>
<h2 id="discard-title">
<?= esc(lang('Studio.discardTitle')) ?>
</h2>
<button class="icon-button" type="button" data-close aria-label="<?= esc(lang('Studio.close'),'attr') ?>">
<?= $studioIcon('close') ?>
</button>
</header>
<footer class="platform-billing-dialog-actions">
<button class="button secondary" type="button" data-discard-stay>
<?= esc(lang('Studio.keepEditing')) ?>
</button>
<button class="button primary" type="button" data-discard-confirm>
<?= esc(lang('Studio.discardChanges')) ?>
</button>
</footer>
</dialog>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>

<script src="<?= base_url('assets/js/studio-forms.js') ?>?v=<?= esc(asset_version('assets/js/studio-forms.js')) ?>" defer>
</script>
<?= $this->endSection() ?>
