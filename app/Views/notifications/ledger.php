<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold v3-text-ink">Notification Ledger</h1>
    </div>

    <!-- Filters -->
    <div class="v3-bg-panel p-4 rounded-lg v3-elevation-none border v3-border-default mb-6">
        <form method="get" class="flex space-x-4">
            <select name="status" class="form-select rounded-md v3-border-default v3-select">
                <option value="">All Statuses</option>
                <option value="sent" <?= ($filters['status'] == 'sent') ? 'selected' : '' ?>>Sent</option>
                <option value="failed" <?= ($filters['status'] == 'failed') ? 'selected' : '' ?>>Failed</option>
                <option value="blocked" <?= ($filters['status'] == 'blocked') ? 'selected' : '' ?>>Blocked</option>
                <option value="pending" <?= ($filters['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
            </select>
            <select name="channel" class="form-select rounded-md v3-border-default v3-select">
                <option value="">All Channels</option>
                <option value="email" <?= ($filters['channel'] == 'email') ? 'selected' : '' ?>>Email</option>
                <option value="sms" <?= ($filters['channel'] == 'sms') ? 'selected' : '' ?>>SMS</option>
            </select>
            <button type="submit" class="px-4 py-2 v3-bg-primary v3-text-on-primary rounded-md v3-hover-primary v3-btn v3-btn--primary">Filter</button>
        </form>
    </div>

    <!-- Ledger Table -->
    <div class="v3-bg-panel rounded-lg v3-elevation-none border v3-border-default overflow-hidden">
        <table class="min-w-full divide-y v3-border-default">
            <thead class="v3-bg-panel">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium v3-text-muted uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium v3-text-muted uppercase">Channel</th>
                    <th class="px-6 py-3 text-left text-xs font-medium v3-text-muted uppercase">Recipient</th>
                    <th class="px-6 py-3 text-left text-xs font-medium v3-text-muted uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium v3-text-muted uppercase">Reason</th>
                    <th class="px-6 py-3 text-right text-xs font-medium v3-text-muted uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="v3-bg-panel divide-y v3-border-default">
                <?php foreach ($notifications as $note): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm v3-text-muted">
                            <?= $note['created_at'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm v3-text-ink capitalize">
                            <?= esc($note['channel_type']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm v3-text-muted">
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
                                <?= $note['status'] === 'sent' ? 'v3-bg-paid v3-text-primary' :
                                   ($note['status'] === 'pending' ? 'v3-bg-waiting v3-text-warning' : 'v3-bg-alert v3-text-danger') ?>">
                                <?= esc($note['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm v3-text-muted max-w-xs truncate" title="<?= esc($note['failure_reason']) ?>">
                            <?= esc($note['failure_reason']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <?php if (in_array($note['status'], ['failed', 'blocked'])): ?>
                                <button onclick="retryNotification(<?= $note['id'] ?>)" class="v3-text-chair v3-hover-text-chair v3-btn v3-btn--secondary">Retry</button>
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
