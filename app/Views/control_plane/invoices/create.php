<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?php helper('form'); ?>
<?php
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
    $lineItemsJson = json_encode($lineItemsOld, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
    $taxDefault = old('tax_percentage') ?? 0;
?>
<div class="space-y-6">
    <div class="glass-card rounded-3xl p-6 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-indigo-500 font-semibold">New invoice</p>
                <h1 class="text-3xl font-semibold text-slate-900">Invoice builder</h1>
                <p class="text-sm text-slate-500">Assign clinics, line items, and taxes before sending.</p>
            </div>
            <a href="<?= base_url('controlplane/invoices') ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:border-slate-300">
                <i class="fas fa-arrow-left"></i>
                Back to registry
            </a>
        </div>

        <form id="invoiceForm" action="<?= base_url('controlplane/invoices') ?>" method="post" class="space-y-6">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="text-xs font-semibold text-slate-500" for="clinic_id">Clinic</label>
                    <select id="clinic_id" name="clinic_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                        <option value="">Select clinic</option>
                        <?php foreach ($clinics as $clinic): ?>
                            <option value="<?= esc($clinic['id']) ?>" <?= old('clinic_id') == $clinic['id'] ? 'selected' : '' ?>><?= esc($clinic['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($validation->getError('clinic_id')): ?>
                        <p class="text-rose-500 text-xs mt-1"><?= esc($validation->getError('clinic_id')) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500" for="issue_date">Issue date</label>
                    <input type="date" id="issue_date" name="issue_date" value="<?= esc(old('issue_date') ?: date('Y-m-d')) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500" for="due_date">Due date</label>
                    <input type="date" id="due_date" name="due_date" value="<?= esc(old('due_date') ?: date('Y-m-d')) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500" for="currency">Currency</label>
                    <input id="currency" name="currency" type="text" value="<?= esc(old('currency') ?: 'USD') ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="USD">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-slate-500" for="tax_percentage">Tax percentage</label>
                    <div class="flex items-center gap-3">
                        <input id="taxPercentage" name="tax_percentage" type="number" step="0.01" min="0" value="<?= esc($taxDefault) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <span class="text-xs text-slate-400">% applied to subtotal</span>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500" for="notes">Public notes</label>
                    <textarea id="notes" name="notes" rows="2" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="This will appear on the invoice"><?= esc(old('notes')) ?></textarea>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-800">Line items</h3>
                    <button type="button" class="rounded-xl border border-slate-200 bg-white/80 px-4 py-2 text-xs font-semibold text-slate-600 hover:border-slate-300" onclick="addLineItem()">
                        <i class="fas fa-plus"></i>
                        Add item
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-slate-600">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-[0.3em] text-slate-400">
                                <th class="pb-2">Description</th>
                                <th class="pb-2">Qty</th>
                                <th class="pb-2">Unit price</th>
                                <th class="pb-2">Line total</th>
                                <th class="pb-2">Action</th>
                            </tr>
                        </thead>
                        <tbody id="lineItemsBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white/80 p-5 shadow-sm">
                <div class="flex items-center justify-between text-sm text-slate-600">
                    <span>Subtotal</span>
                    <span id="subtotalValue">0.00</span>
                </div>
                <div class="flex items-center justify-between text-sm text-slate-600 mt-2">
                    <span>Tax (<span id="taxPercentLabel"><?= esc($taxDefault) ?></span>%)</span>
                    <span id="taxValue">0.00</span>
                </div>
                <div class="flex items-center justify-between text-lg font-semibold text-slate-900 mt-3">
                    <span>Total</span>
                    <span id="totalValue">0.00</span>
                </div>
            </div>

            <input type="hidden" id="lineItemsPayload" name="line_items_json" value="<?= $lineItemsJson ?>">
            <input type="hidden" id="lineItemsRaw" value='<?= $lineItemsJson ?>'>

            <div>
                <label class="text-xs font-semibold text-slate-500" for="private_notes">Internal notes</label>
                <textarea id="private_notes" name="private_notes" rows="2" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Private admin notes that stay in the control plane"><?= esc(old('private_notes')) ?></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 text-xs font-semibold text-white shadow-lg shadow-emerald-500/25">
                    <i class="fas fa-paper-plane"></i>
                    Create invoice
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const lineItemsPayload = document.getElementById('lineItemsPayload');
const taxInput = document.getElementById('taxPercentage');
const taxLabel = document.getElementById('taxPercentLabel');
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
        const descCell = document.createElement('td');
        const descInput = document.createElement('input');
        descInput.type = 'text';
        descInput.value = item.description || '';
        descInput.placeholder = 'Service or product description';
        descInput.className = 'w-full rounded-xl border border-slate-200 px-3 py-2 text-sm';
        descInput.addEventListener('input', () => updateLineItem(index, 'description', descInput.value));
        descCell.appendChild(descInput);

        const qtyCell = document.createElement('td');
        const qtyInput = document.createElement('input');
        qtyInput.type = 'number';
        qtyInput.min = 0;
        qtyInput.step = 1;
        qtyInput.value = item.quantity ?? 1;
        qtyInput.className = 'w-full rounded-xl border border-slate-200 px-3 py-2 text-sm';
        qtyInput.addEventListener('input', () => updateLineItem(index, 'quantity', parseFloat(qtyInput.value) || 0));
        qtyCell.appendChild(qtyInput);

        const priceCell = document.createElement('td');
        const priceInput = document.createElement('input');
        priceInput.type = 'number';
        priceInput.min = 0;
        priceInput.step = 0.01;
        priceInput.value = item.unit_price ?? 0;
        priceInput.className = 'w-full rounded-xl border border-slate-200 px-3 py-2 text-sm';
        priceInput.addEventListener('input', () => updateLineItem(index, 'unit_price', parseFloat(priceInput.value) || 0));
        priceCell.appendChild(priceInput);

        const totalCell = document.createElement('td');
        totalCell.className = 'py-3 font-semibold text-slate-900 text-right';
        totalCell.textContent = formatCurrency((item.quantity ?? 0) * (item.unit_price ?? 0));

        const actionCell = document.createElement('td');
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-600';
        removeBtn.textContent = 'Remove';
        removeBtn.addEventListener('click', () => removeLineItem(index));
        if (lineItems.length === 1) {
            removeBtn.disabled = true;
            removeBtn.classList.add('opacity-40', 'cursor-not-allowed');
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
    if (lineItems.length === 1) {
        return;
    }
    lineItems.splice(index, 1);
    renderLineItems();
}

function updateLineItem(index, key, value) {
    lineItems[index][key] = value;
    renderLineItems();
}

function updateSummary() {
    const subtotal = lineItems.reduce((sum, item) => sum + (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0), 0);
    const taxPct = parseFloat(taxInput.value) || 0;
    const taxAmount = subtotal * taxPct / 100;
    const total = subtotal + taxAmount;

    subtotalEl.textContent = formatCurrency(subtotal);
    taxEl.textContent = formatCurrency(taxAmount);
    totalEl.textContent = formatCurrency(total);
    taxLabel.textContent = taxPct.toFixed(2);
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
<?= $this->endSection() ?>
