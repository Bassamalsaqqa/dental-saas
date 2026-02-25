<div id="smtpFormContainer" class="hidden">
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">SMTP Host</label>
            <input type="text" id="smtp_host" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="smtp.example.com">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Port</label>
                <input type="number" id="smtp_port" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="587">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Encryption</label>
                <select id="smtp_crypto" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <option value="tls">TLS</option>
                    <option value="ssl">SSL</option>
                    <option value="none">None</option>
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Username</label>
            <input type="text" id="smtp_user" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="user@example.com">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" id="smtp_pass" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="********">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">From Name</label>
            <input type="text" id="smtp_from_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="My Clinic">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">From Email</label>
            <input type="email" id="smtp_from_email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="noreply@example.com">
        </div>
    </div>
</div>

<div id="whatsappFormContainer" class="hidden">
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Phone Number ID</label>
            <input type="text" id="wa_phone_number_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="123456789012345">
            <p class="text-xs text-gray-500 mt-1">Meta WhatsApp Cloud API phone number ID for this clinic channel.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">API Version (optional)</label>
            <input type="text" id="wa_api_version" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="v22.0">
            <p class="text-xs text-gray-500 mt-1">Leave empty to use platform default.</p>
        </div>
    </div>
</div>

<div id="jsonFormContainer">
    <label class="block text-sm font-medium text-gray-700 mb-2">Configuration JSON</label>
    <textarea id="configJson" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-lg font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder='{"api_key": "...", "sender": "..."}'></textarea>
    <p class="text-xs text-gray-500 mt-2">Enter provider credentials as JSON. Stored encrypted.</p>
</div>

<script>
    function toggleFormMode(type) {
        if (type === 'email') {
            document.getElementById('smtpFormContainer').classList.remove('hidden');
            document.getElementById('whatsappFormContainer').classList.add('hidden');
            document.getElementById('jsonFormContainer').classList.add('hidden');
        } else if (type === 'whatsapp') {
            document.getElementById('smtpFormContainer').classList.add('hidden');
            document.getElementById('whatsappFormContainer').classList.remove('hidden');
            document.getElementById('jsonFormContainer').classList.add('hidden');
        } else {
            document.getElementById('smtpFormContainer').classList.add('hidden');
            document.getElementById('whatsappFormContainer').classList.add('hidden');
            document.getElementById('jsonFormContainer').classList.remove('hidden');
        }
    }

    function serializeSmtpForm() {
        return JSON.stringify({
            smtp_host: document.getElementById('smtp_host').value,
            smtp_port: document.getElementById('smtp_port').value,
            smtp_crypto: document.getElementById('smtp_crypto').value,
            smtp_user: document.getElementById('smtp_user').value,
            smtp_pass: document.getElementById('smtp_pass').value,
            smtp_from_name: document.getElementById('smtp_from_name').value,
            smtp_from_email: document.getElementById('smtp_from_email').value
        });
    }

    function serializeWhatsAppForm() {
        return JSON.stringify({
            phone_number_id: document.getElementById('wa_phone_number_id').value,
            api_version: document.getElementById('wa_api_version').value
        });
    }

    function clearChannelConfigForm(type) {
        if (type === 'email') {
            document.getElementById('smtp_host').value = '';
            document.getElementById('smtp_user').value = '';
            document.getElementById('smtp_pass').value = '';
            return;
        }

        if (type === 'whatsapp') {
            document.getElementById('wa_phone_number_id').value = '';
            document.getElementById('wa_api_version').value = '';
            return;
        }

        document.getElementById('configJson').value = '';
    }
    
    // Helper to populate form from JSON (if we were decrypting/returning it, which we aren't for security, but good to have logic ready)
    // Currently we don't return the config back to UI for security reasons (write-only or masked).
</script>
