<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-slate-50">
    <div class="container mx-auto px-4 py-6 space-y-6">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-gray-900">Provider Payouts</h1>
                    <p class="text-sm text-gray-600">Track doctor/guest split payouts and outstanding balances.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="<?= base_url('finance') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Finance
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-gray-100 shadow p-5">
                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Provider Share Total</p>
                <p class="mt-2 text-2xl font-black text-indigo-700"><?= formatCurrency($stats['total_provider_share'] ?? 0) ?></p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow p-5">
                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Outstanding Payable</p>
                <p class="mt-2 text-2xl font-black text-rose-700"><?= formatCurrency($stats['total_provider_balance'] ?? 0) ?></p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow p-5">
                <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Clinic Share Total</p>
                <p class="mt-2 text-2xl font-black text-emerald-700"><?= formatCurrency($stats['total_clinic_share'] ?? 0) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <h2 class="text-lg font-bold text-gray-900">Payout Ledger</h2>
                    <select id="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">All statuses</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="partial">Partial</option>
                        <option value="paid">Paid</option>
                        <option value="void">Void</option>
                    </select>
                </div>
                <div class="relative w-full md:w-72">
                    <input id="payoutSearch" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Search by provider, patient, txn...">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="providerPayoutsTable" class="min-w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-600">
                            <th class="px-4 py-3">Transaction</th>
                            <th class="px-4 py-3">Patient</th>
                            <th class="px-4 py-3">Provider</th>
                            <th class="px-4 py-3">Provider Share</th>
                            <th class="px-4 py-3">Paid</th>
                            <th class="px-4 py-3">Balance</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
let payoutsTable = null;
const payoutCurrencySymbol = '<?= esc($currency_symbol ?? '₪') ?>';
const payoutCurrencyPosition = '<?= esc($currency_position ?? 'before') ?>';

function formatCurrency(value) {
    const amount = Number(value || 0);
    const normalized = Number.isFinite(amount) ? amount : 0;
    const formatted = normalized.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    return payoutCurrencyPosition === 'after'
        ? `${formatted} ${payoutCurrencySymbol}`
        : `${payoutCurrencySymbol}${formatted}`;
}

function statusBadge(status) {
    const cls = {
        unpaid: 'bg-rose-100 text-rose-700',
        partial: 'bg-amber-100 text-amber-700',
        paid: 'bg-emerald-100 text-emerald-700',
        void: 'bg-gray-100 text-gray-700'
    }[status] || 'bg-gray-100 text-gray-700';
    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${cls}">${status}</span>`;
}

$(document).ready(function () {
    payoutsTable = $('#providerPayoutsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('finance/payouts/data') ?>',
            type: 'POST',
            data: function (d) {
                d[window.csrfConfig.name] = window.getCsrfToken();
                d.status = $('#statusFilter').val();
                return d;
            },
            beforeSend: function (xhr) {
                xhr.setRequestHeader(window.csrfConfig.header, window.getCsrfToken());
            },
            complete: function (xhr) {
                if (xhr.responseJSON && xhr.responseJSON.csrf_token) {
                    window.refreshCsrfToken(xhr.responseJSON.csrf_token);
                }
            }
        },
        columns: [
            { data: 'transaction_id', render: function (data, type, row) {
                return `<a class="text-indigo-700 font-semibold hover:underline" href="<?= base_url('finance') ?>/${row.finance_id}">${data}</a>`;
            }},
            { data: 'patient_name' },
            { data: 'provider_name' },
            { data: 'provider_share_amount', render: function(v){ return formatCurrency(v); } },
            { data: 'provider_paid_amount', render: function(v){ return formatCurrency(v); } },
            { data: 'provider_balance_due', render: function(v){ return formatCurrency(v); } },
            { data: 'status', render: function(v){ return statusBadge(v); } },
            { data: 'id', orderable: false, searchable: false, className: 'text-right', render: function(id, type, row){
                let buttons = '';
                if (row.status !== 'paid' && row.status !== 'void') {
                    buttons += `<button type="button" onclick="recordPayout(${id}, ${Number(row.provider_balance_due || 0)})" class="px-3 py-1.5 text-xs font-semibold rounded-md bg-indigo-100 text-indigo-700 hover:bg-indigo-200 mr-2">Record</button>`;
                    buttons += `<button type="button" onclick="markPayoutPaid(${id})" class="px-3 py-1.5 text-xs font-semibold rounded-md bg-emerald-100 text-emerald-700 hover:bg-emerald-200">Mark Paid</button>`;
                }
                return buttons || '<span class="text-xs text-gray-400">No actions</span>';
            }}
        ],
        pageLength: 10,
        order: [[0, 'desc']],
        language: {
            search: '',
            searchPlaceholder: 'Search...'
        }
    });

    $('#payoutSearch').on('keyup', function () {
        payoutsTable.search(this.value).draw();
    });
    $('#statusFilter').on('change', function () {
        payoutsTable.draw();
    });
});

function recordPayout(id, maxAmount) {
    const max = Number(maxAmount || 0);
    const amountRaw = prompt(`Amount to pay (max ${max.toFixed(2)}):`);
    if (!amountRaw) return;
    const amount = parseFloat(amountRaw);
    if (!Number.isFinite(amount) || amount <= 0 || amount > max) {
        alert('Invalid amount.');
        return;
    }
    const formData = new FormData();
    formData.append('amount', amount.toFixed(2));
    formData.append('payment_method', 'cash');
    formData.append('paid_date', new Date().toISOString().split('T')[0]);

    fetch(`<?= base_url('finance/payouts') ?>/${id}/payments`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            [window.csrfConfig.header]: window.getCsrfToken()
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.csrf_token) window.refreshCsrfToken(data.csrf_token);
        if (!data.success) throw new Error(data.message || 'Failed');
        payoutsTable.ajax.reload(null, false);
    })
    .catch(err => alert(err.message));
}

function markPayoutPaid(id) {
    if (!confirm('Mark this provider payout as fully paid now?')) return;
    const formData = new FormData();
    formData.append('payment_method', 'cash');

    fetch(`<?= base_url('finance/payouts') ?>/${id}/mark-paid`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            [window.csrfConfig.header]: window.getCsrfToken()
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.csrf_token) window.refreshCsrfToken(data.csrf_token);
        if (!data.success) throw new Error(data.message || 'Failed');
        payoutsTable.ajax.reload(null, false);
    })
    .catch(err => alert(err.message));
}
</script>
<?= $this->endSection() ?>
