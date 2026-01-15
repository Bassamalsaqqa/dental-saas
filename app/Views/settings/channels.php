<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Notification Channels</h1>
            <p class="text-gray-600 mt-1"><?= $is_global ? 'Global Governance (Superadmin)' : 'Clinic Configuration' ?></p>
        </div>
        <a href="<?= base_url('settings') ?>" class="text-blue-600 hover:text-blue-800">Back to Settings</a>
    </div>

    <?php if ($is_global): ?>
        <!-- Superadmin View -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clinic</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">SMS</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">WhatsApp</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($clinics as $clinic): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?= esc($clinic['name']) ?></div>
                                <div class="text-xs text-gray-500">ID: <?= $clinic['id'] ?></div>
                            </td>
                            <?php foreach (['email', 'sms', 'whatsapp'] as $type): 
                                $chan = null;
                                foreach($clinic['channels'] as $c) {
                                    if ($c['channel_type'] === $type) { $chan = $c; break; }
                                }
                                $enabled = $chan && !empty($chan['enabled_by_superadmin']);
                            ?>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" 
                                           onchange="toggleChannel(<?= $clinic['id'] ?>, '<?= $type ?>', this.checked)"
                                           <?= $enabled ? 'checked' : '' ?>>
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <!-- Clinic Admin View -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($channels as $chan): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900 capitalize"><?= $chan['channel_type'] ?></h3>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?= !empty($chan['enabled_by_superadmin']) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                            <?= !empty($chan['enabled_by_superadmin']) ? 'Enabled' : 'Disabled by Admin' ?>
                        </span>
                    </div>
                    
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Configured:</span>
                            <span class="<?= !empty($chan['configured_by_clinic']) ? 'text-green-600' : 'text-gray-400' ?>">
                                <i class="fas fa-<?= !empty($chan['configured_by_clinic']) ? 'check-circle' : 'times-circle' ?>"></i>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Validated:</span>
                            <span class="<?= !empty($chan['validated']) ? 'text-green-600' : 'text-gray-400' ?>">
                                <i class="fas fa-<?= !empty($chan['validated']) ? 'check-circle' : 'times-circle' ?>"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 space-y-2">
                        <button onclick="openConfigModal('<?= $chan['channel_type'] ?>')" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-cog mr-2"></i> Configure
                        </button>
                        <?php if (!empty($chan['configured_by_clinic'])): ?>
                            <button onclick="validateChannel('<?= $chan['channel_type'] ?>')" class="w-full px-4 py-2 bg-blue-50 border border-blue-200 rounded-lg text-blue-700 hover:bg-blue-100 transition-colors">
                                <i class="fas fa-check-double mr-2"></i> Mark Validated
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Config Modal (Clinic Admin) -->
<?php if (!$is_global): ?>
<div id="configModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full mx-4 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-xl font-bold text-gray-900">Configure <span id="modalChannelType" class="capitalize"></span></h3>
        </div>
        <div class="p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Configuration JSON</label>
            <textarea id="configJson" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-lg font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder='{"api_key": "...", "sender": "..."}'></textarea>
            <p class="text-xs text-gray-500 mt-2">Enter provider credentials as JSON. Stored encrypted.</p>
        </div>
        <div class="p-6 bg-gray-50 flex justify-end space-x-3">
            <button onclick="closeConfigModal()" class="px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-lg">Cancel</button>
            <button onclick="saveConfig()" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg">Save Configuration</button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
const csrfHeader = '<?= config('Security')->headerName ?>';
const csrfToken = '<?= csrf_hash() ?>';

function toggleChannel(clinicId, type, enabled) {
    fetch('<?= base_url('settings/updateChannelStatus') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
            [csrfHeader]: csrfToken
        },
        body: `clinic_id=${clinicId}&channel_type=${type}&enabled=${enabled ? 1 : 0}`
    }).then(res => res.json())
      .then(data => {
          if(!data.success) alert('Failed to update status');
      });
}

<?php if (!$is_global): ?>
let currentChannel = null;

function openConfigModal(type) {
    currentChannel = type;
    document.getElementById('modalChannelType').textContent = type;
    document.getElementById('configJson').value = ''; // Reset or fetch existing? For security, maybe don't show existing? "Enter new config"
    document.getElementById('configModal').classList.remove('hidden');
    document.getElementById('configModal').classList.add('flex');
}

function closeConfigModal() {
    document.getElementById('configModal').classList.add('hidden');
    document.getElementById('configModal').classList.remove('flex');
}

function saveConfig() {
    const json = document.getElementById('configJson').value;
    try {
        JSON.parse(json);
    } catch(e) {
        alert('Invalid JSON format');
        return;
    }

    fetch('<?= base_url('settings/updateChannelConfig') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
            [csrfHeader]: csrfToken
        },
        body: `channel_type=${currentChannel}&config_json=${encodeURIComponent(json)}&action=save_config`
    }).then(res => res.json())
      .then(data => {
          if(data.success) location.reload();
          else alert(data.message);
      });
}

function validateChannel(type) {
    if(!confirm('Mark this channel as validated? (Manual Action)')) return;
    
    fetch('<?= base_url('settings/updateChannelConfig') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
            [csrfHeader]: csrfToken
        },
        body: `channel_type=${type}&action=validate`
    }).then(res => res.json())
      .then(data => {
          if(data.success) location.reload();
          else alert(data.message);
      });
}
<?php endif; ?>
</script>
<?= $this->endSection() ?>
