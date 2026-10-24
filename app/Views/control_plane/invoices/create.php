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
<div class="v3-platform-workspace min-h-screen cp-surface">
    <!-- Animated Background Elements -->
    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Page Header -->
        <div class="mb-4">
            <div class="cp-surface border cp-border cp-radius p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 cp-radius flex items-center justify-center text-white">
                            <i class="fas fa-file-invoice text-xl"></i>
                        </div>
                        <div>
                            <h1 class="cp-title font-bold text-gray-900"><?= esc(lang('V3Platform.newInvoice')) ?></h1>
                            <p class="text-gray-600"><?= esc(lang('V3Platform.createAndSendANewInvoiceToAClinic')) ?></p>
                        </div>
                    </div>
                    <a href="<?= base_url('controlplane/invoices') ?>" class="inline-flex items-center gap-2 px-4 py-2 cp-surface border cp-border cp-radius text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all">
                        <i class="fas fa-arrow-left rtl-back-icon"></i> <?= esc(lang('V3Platform.backToRegistry')) ?>
                    </a>
                </div>
            </div>
        </div>

        <form id="invoiceForm" action="<?= base_url('controlplane/invoices') ?>" method="post">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
                <!-- Main Form Area -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Invoice Details -->
                    <div class="cp-surface border cp-border cp-radius p-6">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-8 h-8 cp-soft-surface rounded-lg flex items-center justify-center cp-accent">
                                <i class="fas fa-info-circle text-sm"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900"><?= esc(lang('V3Platform.invoiceDetails')) ?></h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.clinic')) ?></label>
                                <select id="clinic_id" name="clinic_id" class="w-full px-4 py-3 cp-surface border cp-border cp-radius focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                                    <option value=""><?= esc(lang('V3Platform.selectClinic')) ?></option>
                                    <?php foreach ($clinics as $clinic): ?>
                                        <option value="<?= esc($clinic['id']) ?>" <?= old('clinic_id') == $clinic['id'] ? 'selected' : '' ?>><?= esc($clinic['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($validation->getError('clinic_id')): ?>
                                    <p class="text-rose-500 text-xs mt-1 font-medium"><?= esc($validation->getError('clinic_id')) ?></p>
                                <?php endif; ?>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.issueDate')) ?></label>
                                <input type="text" data-clinic-date="1" id="issue_date" name="issue_date" value="<?= esc(old('issue_date') ?: date('Y-m-d')) ?>" class="w-full px-4 py-3 cp-surface border cp-border cp-radius focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.dueDate')) ?></label>
                                <input type="text" data-clinic-date="1" id="due_date" name="due_date" value="<?= esc(old('due_date') ?: date('Y-m-d')) ?>" class="w-full px-4 py-3 cp-surface border cp-border cp-radius focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                            </div>
                        </div>
                    </div>

                    <!-- Line Items -->
                    <div class="cp-surface border cp-border cp-radius p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 cp-soft-surface rounded-lg flex items-center justify-center text-emerald-600">
                                    <i class="fas fa-list-ol text-sm"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900"><?= esc(lang('V3Platform.lineItems')) ?></h3>
                            </div>
                            <button type="button" class="inline-flex items-center gap-2 px-3 py-1.5 cp-soft-surface text-emerald-700 text-xs font-bold rounded-lg border border-emerald-100 hover:bg-emerald-100 transition-colors" onclick="addLineItem()">
                                <i class="fas fa-plus"></i> <?= esc(lang('V3Platform.addItem')) ?>
                            </button>
                        </div>

                        <div class="overflow-hidden cp-radius border cp-border">
                            <table class="w-full text-sm text-left">
                                <thead class="cp-surface text-xs uppercase tracking-wider text-gray-500 font-bold">
                                    <tr>
                                        <th class="px-4 py-3"><?= esc(lang('V3Platform.description')) ?></th>
                                        <th class="px-4 py-3 w-24"><?= esc(lang('V3Platform.qty')) ?></th>
                                        <th class="px-4 py-3 w-32"><?= esc(lang('V3Platform.price')) ?></th>
                                        <th class="px-4 py-3 w-32 text-right"><?= esc(lang('V3Platform.total')) ?></th>
                                        <th class="px-4 py-3 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody id="lineItemsBody" class="divide-y divide-gray-100 cp-surface">
                                    <!-- JS Rendered -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Settings -->
                <div class="space-y-8">
                    <!-- Financials -->
                    <div class="cp-surface border cp-border cp-radius p-6">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600">
                                <i class="fas fa-coins text-sm"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900"><?= esc(lang('V3Platform.financials')) ?></h3>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.currency')) ?></label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </span>
                                    <input id="currency" name="currency" type="text" value="<?= esc(old('currency') ?: 'USD') ?>" class="w-full pl-10 pr-4 py-2 cp-surface border cp-border cp-radius focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm" placeholder="USD">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.taxRate')) ?></label>
                                <div class="relative">
                                    <input id="taxPercentage" name="tax_percentage" type="number" step="0.01" min="0" value="<?= esc($taxDefault) ?>" class="w-full px-4 py-2 cp-surface border cp-border cp-radius focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm">
                                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 font-bold">%</span>
                                </div>
                            </div>

                            <div class="pt-4 border-t cp-border space-y-2">
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span><?= esc(lang('V3Platform.subtotal')) ?></span>
                                    <span id="subtotalValue" class="font-mono">$0.00</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span><?= esc(lang('V3Platform.tax')) ?></span>
                                    <span id="taxValue" class="font-mono">$0.00</span>
                                </div>
                                <div class="flex justify-between text-lg font-bold cp-accent pt-2 border-t border-dashed cp-border">
                                    <span><?= esc(lang('V3Platform.total')) ?></span>
                                    <span id="totalValue" class="font-mono">$0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="cp-surface border cp-border cp-radius p-6">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-8 h-8 cp-soft-surface rounded-lg flex items-center justify-center cp-accent">
                                <i class="fas fa-sticky-note text-sm"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900"><?= esc(lang('V3Platform.notes')) ?></h3>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.publicNotes')) ?></label>
                                <textarea id="notes" name="notes" rows="3" class="w-full px-4 py-2 cp-surface border cp-border cp-radius focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="<?= esc(lang('V3Platform.visibleToClient'), 'attr') ?>"><?= esc(old('notes')) ?></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.internalNotes')) ?></label>
                                <textarea id="private_notes" name="private_notes" rows="2" class="w-full px-4 py-2 bg-yellow-50 border border-yellow-200 cp-radius focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm" placeholder="<?= esc(lang('V3Platform.adminOnly'), 'attr') ?>"><?= esc(old('private_notes')) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="lineItemsPayload" name="line_items_json" value="<?= $lineItemsJson ?>">
                    <input type="hidden" id="lineItemsRaw" value='<?= $lineItemsJson ?>'>

                    <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-4 text-white font-bold cp-radius transition-all duration-300">
                        <i class="fas fa-paper-plane mr-2"></i> <?= esc(lang('V3Platform.createInvoice')) ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
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
        row.className = 'group hover:bg-gray-50/50 transition-colors';

        // Description
        const descCell = document.createElement('td');
        descCell.className = 'px-4 py-2';
        const descInput = document.createElement('input');
        descInput.type = 'text';
        descInput.value = item.description || '';
        descInput.placeholder = 'Item description...';
        descInput.className = 'w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm';
        descInput.addEventListener('input', () => updateLineItem(index, 'description', descInput.value));
        descCell.appendChild(descInput);

        // Qty
        const qtyCell = document.createElement('td');
        qtyCell.className = 'px-4 py-2';
        const qtyInput = document.createElement('input');
        qtyInput.type = 'number';
        qtyInput.min = 0;
        qtyInput.step = 1;
        qtyInput.value = item.quantity ?? 1;
        qtyInput.className = 'w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-center';
        qtyInput.addEventListener('input', () => updateLineItem(index, 'quantity', parseFloat(qtyInput.value) || 0));
        qtyCell.appendChild(qtyInput);

        // Price
        const priceCell = document.createElement('td');
        priceCell.className = 'px-4 py-2';
        const priceInput = document.createElement('input');
        priceInput.type = 'number';
        priceInput.min = 0;
        priceInput.step = 0.01;
        priceInput.value = item.unit_price ?? 0;
        priceInput.className = 'w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-right font-mono';
        priceInput.addEventListener('input', () => updateLineItem(index, 'unit_price', parseFloat(priceInput.value) || 0));
        priceCell.appendChild(priceInput);

        // Total
        const totalCell = document.createElement('td');
        totalCell.className = 'px-4 py-2 text-right font-mono font-medium text-gray-900';
        totalCell.textContent = formatCurrency((item.quantity ?? 0) * (item.unit_price ?? 0));

        // Action
        const actionCell = document.createElement('td');
        actionCell.className = 'px-4 py-2 text-center';
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'text-gray-400 hover:text-rose-500 transition-colors p-1';
        const icon = document.createElement('i');
        icon.className = 'fas fa-trash-alt';
        removeBtn.appendChild(icon);
        removeBtn.addEventListener('click', () => removeLineItem(index));
        if (lineItems.length === 1) {
            removeBtn.disabled = true;
            removeBtn.classList.add('opacity-30', 'cursor-not-allowed');
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

