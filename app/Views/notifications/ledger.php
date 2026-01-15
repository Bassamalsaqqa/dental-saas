<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Notification Ledger</h1>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
        <form method="get" class="flex space-x-4">
            <select name="status" class="form-select rounded-md border-gray-300">
                <option value="">All Statuses</option>
                <option value="sent" <?= ($filters['status'] == 'sent') ? 'selected' : '' ?>>Sent</option>
                <option value="failed" <?= ($filters['status'] == 'failed') ? 'selected' : '' ?>>Failed</option>
                <option value="blocked" <?= ($filters['status'] == 'blocked') ? 'selected' : '' ?>>Blocked</option>
                <option value="pending" <?= ($filters['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
            </select>
            <select name="channel" class="form-select rounded-md border-gray-300">
                <option value="">All Channels</option>
                <option value="email" <?= ($filters['channel'] == 'email') ? 'selected' : '' ?>>Email</option>
                <option value="sms" <?= ($filters['channel'] == 'sms') ? 'selected' : '' ?>>SMS</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Filter</button>
        </form>
    </div>

    <!-- Ledger Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Channel</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recipient</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($notifications as $note): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= $note['created_at'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 capitalize">
                            <?= esc($note['channel_type']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= esc($note['recipient_type']) ?>
                            <?php 
                                // Mask address
                                $addr = $note['recipient_address'] ?? 'N/A';
                                if (strpos($addr, '@') !== false) {
                                    $parts = explode('@', $addr);
                                    echo ' (' . substr($parts[0], 0, 2) . '***@' . $parts[1] . ')';
                                } elseif (strlen($addr) > 4) {
                                    echo ' (***' . substr($addr, -4) . ')';
                                }
                            ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?= $note['status'] === 'sent' ? 'bg-green-100 text-green-800' : 
                                   ($note['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                <?= esc($note['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="<?= esc($note['failure_reason']) ?>">
                            <?= esc($note['failure_reason']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <?php if (in_array($note['status'], ['failed', 'blocked'])): ?>
                                <button onclick="retryNotification(<?= $note['id'] ?>)" class="text-indigo-600 hover:text-indigo-900">Retry</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="p-4">
        <?= $pager->links() ?>
    </div>
</div>

<script>
function retryNotification(id) {
    if(!confirm('Retry this notification? A new pending record will be created.')) return;
    
    fetch('<?= base_url('notifications/ledger/retry/') ?>' + id, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            '<?= config('Security')->headerName ?>': '<?= csrf_hash() ?>'
        }
    }).then(res => res.json())
      .then(data => {
          if(data.success) {
              alert(data.message);
              location.reload();
          } else {
              alert(data.message);
          }
      });
}
</script>
<?= $this->endSection() ?>
