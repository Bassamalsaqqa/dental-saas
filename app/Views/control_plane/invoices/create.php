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
$billingErrors=[];foreach(['clinic_id','issue_date','due_date','tax_percentage','line_items_json'] as $key){$message=isset($validation)?$validation->getError($key):'';if($message)$billingErrors[]=$message;} ?>
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
<form id="invoiceForm" class="studio-form platform-invoice-compose" action="<?= base_url('controlplane/invoices') ?>" method="post" data-unsaved-form>
<div class="platform-invoice-compose-main">
<section class="form-section">
<header>
<h2>
<?= esc(lang('Studio.platformBillingRecipient')) ?>
</h2>
</header>
<?= csrf_field() ?>
<div class="platform-invoice-identity">
<div class="form-field">
<label for="clinic_id">
<?= esc(lang('Studio.platformClinic')) ?> *</label>
<select id="clinic_id" name="clinic_id" required>
<option value="">
<?= esc(lang('Studio.chooseOption')) ?>
</option>
<?php foreach(($clinics??[]) as $clinic): ?>
<option value="<?= (int)$clinic['id'] ?>" <?= (int)old('clinic_id')===(int)$clinic['id']?'selected':'' ?>>#<?= (int)$clinic['id'] ?> · <?= esc($clinic['name']) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<?php foreach(['issue_date'=>'platformIssueDate','due_date'=>'platformDueDate'] as $field=>$label): ?>
<div class="form-field">
<label for="<?= $field ?>">
<?= esc(lang('Studio.'.$label)) ?> *</label>
<input type="date" id="<?= $field ?>" name="<?= $field ?>" required value="<?= esc(old($field)?:date('Y-m-d'),'attr') ?>">
</div>
<?php endforeach; ?>
</div>
</section>
<section class="form-section">
<header class="platform-billing-section-heading">
<h2>
<?= esc(lang('Studio.platformInvoiceItems')) ?>
</h2>
<button class="button secondary" type="button" onclick="addLineItem()">
<?= $studioIcon('plus') ?>
<?= esc(lang('Studio.platformAddItem')) ?>
</button>
</header>
<div class="platform-item-scroll" tabindex="0" role="region" aria-label="<?= esc(lang('Studio.platformInvoiceItems'),'attr') ?>">
<table class="platform-invoice-items">
<colgroup>
<col style="width:44%">
<col style="width:12%">
<col style="width:17%">
<col style="width:17%">
<col style="width:10%">
</colgroup>
<thead>
<tr>
<?php foreach(['platformItemDescription','platformQuantity','platformUnitPrice','platformInvoiceTotal','actions'] as $key): ?>
<th scope="col">
<?= esc(lang('Studio.'.$key)) ?>
</th>
<?php endforeach; ?>
</tr>
</thead>
<tbody id="lineItemsBody">
</tbody>
</table>
</div>
</section>
<section class="form-section">
<header>
<h2>
<?= esc(lang('Finance.notes')) ?>
</h2>
</header>
<div class="form-grid">
<div class="form-field">
<label for="notes">
<?= esc(lang('Studio.platformPublicNotes')) ?>
</label>
<textarea id="notes" name="notes" rows="4">
<?= esc(old('notes')) ?>
</textarea>
</div>
<div class="form-field">
<label for="private_notes">
<?= esc(lang('Studio.platformPrivateNotes')) ?>
</label>
<textarea id="private_notes" name="private_notes" rows="4">
<?= esc(old('private_notes')) ?>
</textarea>
</div>
</div>
</section>
</div>
<aside class="form-section platform-invoice-composition-summary">
<h2>
<?= esc(lang('Studio.platformInvoiceTerms')) ?>
</h2>
<div class="platform-invoice-currency-tax">
<div class="form-field">
<label for="currency">
<?= esc(lang('Finance.currency')) ?>
</label>
<input id="currency" name="currency" value="<?= esc(old('currency')?:'USD','attr') ?>">
</div>
<div class="form-field">
<label for="taxPercentage">
<?= esc(lang('Studio.platformTaxRate')) ?>
</label>
<input id="taxPercentage" name="tax_percentage" type="number" step="0.01" min="0" value="<?= esc($taxDefault,'attr') ?>">
</div>
</div>
<dl class="platform-invoice-totals">
<div>
<dt>
<?= esc(lang('Studio.platformSubtotal')) ?>
</dt>
<dd id="subtotalValue">0.00</dd>
</div>
<div>
<dt>
<?= esc(lang('Studio.platformTaxAmount')) ?>
</dt>
<dd id="taxValue">0.00</dd>
</div>
<div>
<dt>
<?= esc(lang('Studio.platformInvoiceTotal')) ?>
</dt>
<dd id="totalValue">0.00</dd>
</div>
</dl>
<p class="platform-billing-explanation">
<?= esc(lang('Studio.platformInvoiceCreateHelp')) ?>
</p>
<input type="hidden" id="lineItemsPayload" name="line_items_json" value="<?= esc($lineItemsJson,'attr') ?>">
<input type="hidden" id="lineItemsRaw" value="<?= esc($lineItemsJson,'attr') ?>">
<button class="button primary" type="submit">
<?= esc(lang('Studio.platformInvoiceCreate')) ?>
</button>
<a class="button secondary" href="<?= base_url('controlplane/invoices') ?>" data-form-cancel>
<?= esc(lang('Studio.cancel')) ?>
</a>
</aside>
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
<script>
const billingWords=<?= json_encode(['description'=>lang('Studio.platformItemDescription'),'quantity'=>lang('Studio.platformQuantity'),'price'=>lang('Studio.platformUnitPrice'),'remove'=>lang('Studio.platformRemoveItem')],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>;
const lineItemsPayload = document.getElementById('lineItemsPayload');
const taxInput = document.getElementById('taxPercentage');
const subtotalEl = document.getElementById('subtotalValue');
const taxEl = document.getElementById('taxValue');
const totalEl = document.getElementById('totalValue');
const lineItemsBody = document.getElementById('lineItemsBody');
const initialLineItems = JSON.parse(document.getElementById('lineItemsRaw').value || '[]');
let lineItems = initialLineItems.length ? initialLineItems : [{ description: '', quantity: 1, unit_price: 0 }];

function renderLineItems() {
    while (lineItemsBody.firstChild) {
        lineItemsBody.removeChild(lineItemsBody.firstChild);
    }
    lineItems.forEach((item, index) => {
        const row = document.createElement('tr');
        row.className = 'platform-invoice-item';

        // Description
        const descCell = document.createElement('td');
        descCell.className = 'billing-item-cell';
        const descInput = document.createElement('input');
        descInput.type = 'text';
        descInput.value = item.description || '';
        descInput.placeholder = billingWords.description;descInput.setAttribute('aria-label',billingWords.description);
        descInput.className = 'billing-item-input';
        descInput.addEventListener('input', () => updateLineItem(index, 'description', descInput.value));
        descCell.appendChild(descInput);

        // Qty
        const qtyCell = document.createElement('td');
        qtyCell.className = 'billing-item-cell';
        const qtyInput = document.createElement('input');
        qtyInput.type = 'number';qtyInput.setAttribute('aria-label',billingWords.quantity);
        qtyInput.min = 0;
        qtyInput.step = 1;
        qtyInput.value = item.quantity ?? 1;
        qtyInput.className = 'billing-item-input';
        qtyInput.addEventListener('input', () => updateLineItem(index, 'quantity', parseFloat(qtyInput.value) || 0));
        qtyCell.appendChild(qtyInput);

        // Price
        const priceCell = document.createElement('td');
        priceCell.className = 'billing-item-cell';
        const priceInput = document.createElement('input');
        priceInput.type = 'number';priceInput.setAttribute('aria-label',billingWords.price);
        priceInput.min = 0;
        priceInput.step = 0.01;
        priceInput.value = item.unit_price ?? 0;
        priceInput.className = 'billing-item-input';
        priceInput.addEventListener('input', () => updateLineItem(index, 'unit_price', parseFloat(priceInput.value) || 0));
        priceCell.appendChild(priceInput);

        // Total
        const totalCell = document.createElement('td');
        totalCell.className = 'billing-item-cell';
        totalCell.textContent = formatCurrency((item.quantity ?? 0) * (item.unit_price ?? 0));

        // Action
        const actionCell = document.createElement('td');
        actionCell.className = 'billing-item-cell';
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'button destructive';
        removeBtn.textContent=billingWords.remove;
        removeBtn.addEventListener('click', () => removeLineItem(index));
        if (lineItems.length === 1) {
            removeBtn.disabled = true;
            removeBtn.setAttribute('aria-disabled','true');
        }
        actionCell.appendChild(removeBtn);

        row.appendChild(descCell);
        row.appendChild(qtyCell);
        row.appendChild(priceCell);
        row.appendChild(totalCell);
        row.appendChild(actionCell);
        lineItemsBody.appendChild(row);
    });
    updateSummary();
}

function addLineItem() {
    lineItems.push({ description: '', quantity: 1, unit_price: 0 });
    renderLineItems();
}

function removeLineItem(index) {
    if (lineItems.length === 1) return;
    lineItems.splice(index, 1);
    renderLineItems();
}

function updateLineItem(index, key, value) {
    lineItems[index][key] = value;
    const totalCell=lineItemsBody.rows[index]?.cells[3];if(totalCell)totalCell.textContent=formatCurrency((lineItems[index].quantity??0)*(lineItems[index].unit_price??0));
    updateSummary();
}

function updateSummary() {
    const subtotal = lineItems.reduce((sum, item) => sum + (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0), 0);
    const taxPct = parseFloat(taxInput.value) || 0;
    const taxAmount = subtotal * taxPct / 100;
    const total = subtotal + taxAmount;

    subtotalEl.textContent = formatCurrency(subtotal);
    taxEl.textContent = formatCurrency(taxAmount);
    totalEl.textContent = formatCurrency(total);
    syncPayload();
}

function syncPayload() {
    if (!lineItemsPayload) return;
    lineItemsPayload.value = JSON.stringify(lineItems);
}

function formatCurrency(value) {
    return (value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

taxInput.addEventListener('input', () => updateSummary());

document.addEventListener('DOMContentLoaded', () => {
    renderLineItems();
    document.getElementById('invoiceForm').addEventListener('submit', () => syncPayload());
});
</script>
<script src="<?= base_url('assets/js/studio-forms.js') ?>?v=<?= esc(asset_version('assets/js/studio-forms.js')) ?>" defer>
</script>
<?= $this->endSection() ?>
