<!-- Quick Add Supplier Modal -->
<div id="quickSupplierModal" class="fixed inset-0 z-[2147483640] hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="fixed inset-0 v3-backdrop"></div>
    <div class="relative z-10 flex min-h-screen items-center justify-center p-4" onclick="closeQuickSupplierModal()">
        <div class="w-full max-w-lg v3-radius border v3-border v3-surface" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between border-b v3-border px-5 py-4">
                <div>
                    <h4 class="text-base font-semibold v3-text-ink"><?= lang('Inventory.quickAddSupplierTitle') ?></h4>
                    <p class="mt-1 text-sm v3-text-muted"><?= lang('Inventory.quickAddSupplierHelp') ?></p>
                </div>
                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full v3-text-muted transition v3-hover-soft v3-hover-text-ink v3-btn v3-btn--secondary" onclick="closeQuickSupplierModal()">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                <input type="text" id="quickSupplierName" class="inventory-create-form-field v3-input" placeholder="<?= lang('Inventory.supplierNameRequired') ?>" required>
                <input type="text" id="quickSupplierContact" class="inventory-create-form-field v3-input" placeholder="<?= lang('Inventory.contactPerson') ?>">
                <input type="text" id="quickSupplierPhone" class="inventory-create-form-field v3-input" placeholder="<?= lang('Inventory.phone') ?>">
                <input type="email" id="quickSupplierEmail" class="inventory-create-form-field v3-input" placeholder="<?= lang('Inventory.email') ?>">
                <p id="quickSupplierStatus" class="text-xs v3-text-muted sm:col-span-2"></p>
            </div>
            <div class="flex flex-wrap justify-end gap-2 border-t v3-border px-5 py-4">
                <button type="button" onclick="closeQuickSupplierModal()" class="inline-flex items-center gap-2 v3-radius border v3-border px-4 py-2.5 text-sm font-semibold v3-text-ink transition v3-hover-soft v3-btn v3-btn--secondary">
                    <i class="fas fa-times text-xs"></i><span><?= lang('Inventory.cancel') ?></span>
                </button>
                <button type="button" id="createQuickSupplierBtn" class="inline-flex items-center gap-2 v3-radius v3-primary-surface px-4 py-2.5 text-sm font-semibold v3-text-on-primary transition v3-hover-primary v3-btn v3-btn--secondary">
                    <i class="fas fa-plus text-xs"></i><span><?= lang('Inventory.createSupplier') ?></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('quickSupplierModal');
    var openBtn = document.getElementById('openQuickSupplierModal');
    var createBtn = document.getElementById('createQuickSupplierBtn');
    var statusEl = document.getElementById('quickSupplierStatus');
    var supplierSelect = document.getElementById('supplier_id');
    var supplierText = document.getElementById('supplier_text');
    var fields = ['quickSupplierName','quickSupplierContact','quickSupplierPhone','quickSupplierEmail']
        .map(function(id){ return document.getElementById(id); });

    function openQuickSupplierModal() {
        if (!modal) return;
        if (modal.parentElement !== document.body) document.body.appendChild(modal);
        modal.classList.remove('hidden');
        setTimeout(function(){ fields[0] && fields[0].focus(); }, 50);
    }

    window.closeQuickSupplierModal = function() {
        if (!modal) return;
        modal.classList.add('hidden');
        fields.forEach(function(f){ if(f) f.value = ''; });
        if (statusEl) { statusEl.textContent = ''; statusEl.className = 'text-xs v3-text-muted sm:col-span-2'; }
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            window.closeQuickSupplierModal();
        }
    });

    if (openBtn) openBtn.addEventListener('click', openQuickSupplierModal);

    if (createBtn) {
        createBtn.addEventListener('click', async function() {
            var name = (fields[0]?.value || '').trim();
            if (!name) {
                statusEl.textContent = '<?= lang('Inventory.supplierNameRequiredMessage') ?>';
                statusEl.className = 'text-xs v3-text-danger sm:col-span-2';
                return;
            }
            var formData = new FormData();
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            formData.append('name', name);
            formData.append('contact_person', fields[1]?.value || '');
            formData.append('phone', fields[2]?.value || '');
            formData.append('email', fields[3]?.value || '');
            createBtn.disabled = true;
            statusEl.textContent = '<?= lang('Inventory.creatingSupplier') ?>';
            statusEl.className = 'text-xs v3-text-chair sm:col-span-2';
            try {
                var r = await fetch('<?= base_url('inventory/suppliers/quick-create') ?>', { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:formData });
                var d = await r.json();
                if (!r.ok || !d.success || !d.supplier) throw new Error(d.message || '<?= lang('Inventory.failedToCreateSupplier') ?>');
                var opt = document.createElement('option');
                opt.value = String(d.supplier.id);
                opt.textContent = d.supplier.name;
                supplierSelect.appendChild(opt);
                supplierSelect.value = String(d.supplier.id);
                if (window.jQuery?.fn?.select2) window.jQuery(supplierSelect).val(String(d.supplier.id)).trigger('change');
                if (supplierText) supplierText.value = d.supplier.name;
                statusEl.textContent = d.duplicate ? '<?= lang('Inventory.supplierAlreadyExistedSelected') ?>' : '<?= lang('Inventory.supplierCreatedSelected') ?>';
                statusEl.className = 'text-xs v3-text-primary sm:col-span-2';
                window.setTimeout(window.closeQuickSupplierModal, 350);
            } catch(e) {
                statusEl.textContent = e.message || '<?= lang('Inventory.failedToCreateSupplierWithPeriod') ?>';
                statusEl.className = 'text-xs v3-text-danger sm:col-span-2';
            } finally { createBtn.disabled = false; }
        });
    }

    if (supplierSelect && supplierText) {
        supplierSelect.addEventListener('change', function() {
            var s = supplierSelect.options[supplierSelect.selectedIndex];
            if (s && supplierSelect.value) supplierText.value = s.textContent.trim();
        });
    }
});
</script>
