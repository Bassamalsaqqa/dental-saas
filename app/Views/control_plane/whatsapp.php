<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?php
$stats = $stats ?? [];
$failureRate = (float) ($stats['failure_rate_24h'] ?? 0);
?>
<div class="space-y-6">
    <div class="glass-card rounded-2xl p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-900">WhatsApp Operations</h2>
                <p class="text-sm text-slate-600 mt-1">Outbound attempts, webhook events, opt-outs, platform secrets, and tenant emergency controls.</p>
            </div>
            <div class="flex flex-wrap gap-2 text-xs font-semibold">
                <a href="#attempts" class="px-3 py-2 rounded-xl bg-white/80 border border-white/70 text-slate-700 hover:bg-white">Attempts</a>
                <a href="#events" class="px-3 py-2 rounded-xl bg-white/80 border border-white/70 text-slate-700 hover:bg-white">Events</a>
                <a href="#optouts" class="px-3 py-2 rounded-xl bg-white/80 border border-white/70 text-slate-700 hover:bg-white">Opt-Outs</a>
                <a href="#secrets" class="px-3 py-2 rounded-xl bg-white/80 border border-white/70 text-slate-700 hover:bg-white">Secrets</a>
                <a href="#policies" class="px-3 py-2 rounded-xl bg-white/80 border border-white/70 text-slate-700 hover:bg-white">Policies</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
        <div class="glass-card rounded-2xl p-5">
            <div class="text-xs uppercase tracking-wider text-slate-500 font-bold">Attempts (24h)</div>
            <div class="mt-2 text-2xl font-black text-slate-900"><?= (int) ($stats['attempts_24h_total'] ?? 0) ?></div>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <div class="text-xs uppercase tracking-wider text-slate-500 font-bold">Delivered/Read/Sent (24h)</div>
            <div class="mt-2 text-2xl font-black text-emerald-700"><?= (int) ($stats['attempts_24h_sentish'] ?? 0) ?></div>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <div class="text-xs uppercase tracking-wider text-slate-500 font-bold">Failures (24h)</div>
            <div class="mt-2 text-2xl font-black text-rose-700"><?= (int) ($stats['attempts_24h_failed'] ?? 0) ?></div>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <div class="text-xs uppercase tracking-wider text-slate-500 font-bold">Failure Rate (24h)</div>
            <div class="mt-2 text-2xl font-black <?= $failureRate >= 20 ? 'text-rose-700' : 'text-slate-900' ?>"><?= number_format($failureRate, 2) ?>%</div>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <div class="text-xs uppercase tracking-wider text-slate-500 font-bold">Webhook Events / Opt-Outs (24h)</div>
            <div class="mt-2 text-lg font-black text-slate-900">
                <?= (int) ($stats['events_24h'] ?? 0) ?> <span class="text-slate-400">/</span> <?= (int) ($stats['optouts_24h'] ?? 0) ?>
            </div>
        </div>
    </div>

    <div id="secrets" class="glass-card rounded-2xl p-6">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Platform Secrets (Rotation)</h3>
                <p class="text-sm text-slate-600">Values are write-only. This surface only shows metadata and rotates allowlisted WhatsApp keys.</p>
            </div>
        </div>
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            <?php foreach (($secrets ?? []) as $secret): ?>
                <div class="rounded-2xl border border-white/50 bg-white/70 p-4">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="font-mono text-xs font-bold text-slate-800"><?= esc($secret['secret_key']) ?></div>
                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold <?= !empty($secret['missing']) ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-700' ?>">
                            <?= !empty($secret['missing']) ? 'Missing' : 'Present' ?>
                        </span>
                    </div>
                    <div class="text-xs text-slate-600 space-y-1 mb-3">
                        <div>Rotated At: <?= !empty($secret['rotated_at']) ? esc((string) $secret['rotated_at']) : '-' ?></div>
                        <div>Rotated By: <?= !empty($secret['rotated_by']) ? (int) $secret['rotated_by'] : '-' ?></div>
                        <div>Note: <?= !empty($secret['rotation_note']) ? esc((string) $secret['rotation_note']) : '-' ?></div>
                    </div>
                    <form action="<?= base_url('controlplane/whatsapp/rotate-secret') ?>" method="post" class="space-y-2">
                        <?= csrf_field() ?>
                        <input type="hidden" name="secret_key" value="<?= esc($secret['secret_key']) ?>">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">New Value</label>
                            <input type="password" name="new_value" required class="w-full rounded-xl border-slate-300 text-sm" autocomplete="off">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Rotation Note (optional)</label>
                            <input type="text" name="rotation_note" maxlength="255" class="w-full rounded-xl border-slate-300 text-sm" placeholder="Reason / ticket / incident">
                        </div>
                        <button type="submit" class="px-3 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800">
                            Rotate Secret
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="policies" class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="glass-card rounded-2xl p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-2">Emergency Pause</h3>
            <p class="text-sm text-slate-600 mb-4">Suspend or resume WhatsApp sends for a clinic immediately. This writes `whatsapp_tenant_policies.suspended`.</p>
            <form action="<?= base_url('controlplane/whatsapp/emergency-pause') ?>" method="post" class="space-y-3">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Clinic</label>
                    <select name="clinic_id" required class="w-full rounded-xl border-slate-300 text-sm">
                        <option value="">Select clinic</option>
                        <?php foreach (($clinics ?? []) as $clinic): ?>
                            <option value="<?= (int) $clinic['id'] ?>"><?= esc($clinic['name']) ?> (ID <?= (int) $clinic['id'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="cp-wa-suspend" name="suspend" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                    <label for="cp-wa-suspend" class="text-sm text-slate-700 font-medium">Suspend WhatsApp for selected clinic</label>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Reason</label>
                    <input type="text" name="suspended_reason" maxlength="255" class="w-full rounded-xl border-slate-300 text-sm" placeholder="Fraud spike / provider incident / pilot pause">
                </div>
                <button type="submit" class="px-3 py-2 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700">
                    Apply Emergency Pause
                </button>
            </form>
        </div>

        <div class="glass-card rounded-2xl p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-2">Policy Limits</h3>
            <p class="text-sm text-slate-600 mb-4">Create or update clinic WhatsApp policy limits and suspension state.</p>
            <form action="<?= base_url('controlplane/whatsapp/policy') ?>" method="post" class="space-y-3">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Clinic</label>
                    <select name="clinic_id" required class="w-full rounded-xl border-slate-300 text-sm">
                        <option value="">Select clinic</option>
                        <?php foreach (($clinics ?? []) as $clinic): ?>
                            <option value="<?= (int) $clinic['id'] ?>"><?= esc($clinic['name']) ?> (ID <?= (int) $clinic['id'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Daily Limit</label>
                        <input type="number" min="1" name="daily_limit" class="w-full rounded-xl border-slate-300 text-sm" placeholder="50">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Monthly Limit</label>
                        <input type="number" min="1" name="monthly_limit" class="w-full rounded-xl border-slate-300 text-sm" placeholder="1000">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Per Patient / Day</label>
                        <input type="number" min="1" name="per_patient_daily_limit" class="w-full rounded-xl border-slate-300 text-sm" placeholder="3">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Per Hour</label>
                        <input type="number" min="1" name="per_hour_limit" class="w-full rounded-xl border-slate-300 text-sm" placeholder="20">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Reminder Lead Time (minutes)</label>
                    <input type="number" min="1" name="reminder_lead_time_minutes" class="w-full rounded-xl border-slate-300 text-sm" placeholder="1440">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="cp-wa-policy-suspend" name="suspended" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                    <label for="cp-wa-policy-suspend" class="text-sm text-slate-700 font-medium">Mark suspended</label>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Suspended Reason (optional)</label>
                    <input type="text" name="suspended_reason" maxlength="255" class="w-full rounded-xl border-slate-300 text-sm">
                </div>
                <button type="submit" class="px-3 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">
                    Save Policy
                </button>
            </form>
        </div>
    </div>

    <div class="glass-card rounded-2xl p-6" id="attempts">
        <h3 class="text-lg font-bold text-slate-900 mb-4">Recent WhatsApp Attempts</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <th class="py-2 pr-4">ID</th>
                        <th class="py-2 pr-4">Clinic</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Provider Message</th>
                        <th class="py-2 pr-4">Error</th>
                        <th class="py-2 pr-4">Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($recent_attempts ?? []) as $attempt): ?>
                        <?php $status = (string) ($attempt['status'] ?? 'unknown'); ?>
                        <tr class="border-b border-slate-100">
                            <td class="py-2 pr-4 font-semibold text-slate-800"><?= (int) ($attempt['id'] ?? 0) ?></td>
                            <td class="py-2 pr-4"><?= esc((string) ($attempt['clinic_name'] ?? 'Unknown')) ?></td>
                            <td class="py-2 pr-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold <?= in_array($status, ['sent', 'delivered', 'read'], true) ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>">
                                    <?= esc($status) ?>
                                </span>
                            </td>
                            <td class="py-2 pr-4 font-mono text-xs text-slate-700"><?= !empty($attempt['provider_message_id']) ? esc(substr((string) $attempt['provider_message_id'], 0, 36)) : '-' ?></td>
                            <td class="py-2 pr-4 text-rose-700"><?= !empty($attempt['error_code']) ? esc((string) $attempt['error_code']) : '-' ?></td>
                            <td class="py-2 pr-4 text-slate-600"><?= esc((string) ($attempt['created_at'] ?? '-')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recent_attempts)): ?>
                        <tr><td colspan="6" class="py-4 text-slate-500">No WhatsApp attempts yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="glass-card rounded-2xl p-6" id="events">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Recent Webhook Events</h3>
            <div class="space-y-3 max-h-[34rem] overflow-y-auto pr-1">
                <?php foreach (($recent_events ?? []) as $event): ?>
                    <?php
                    $payloadRaw = $event['payload_json'] ?? '';
                    if (is_array($payloadRaw)) {
                        $payloadRaw = json_encode($payloadRaw);
                    }
                    $payloadRaw = is_string($payloadRaw) ? $payloadRaw : '';
                    $payloadPreview = mb_substr($payloadRaw, 0, 220);
                    ?>
                    <div class="rounded-xl border border-white/60 bg-white/70 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-xs font-mono text-slate-500">#<?= (int) ($event['id'] ?? 0) ?></div>
                                <div class="text-sm font-semibold text-slate-900"><?= esc((string) ($event['event_type'] ?? 'unknown')) ?></div>
                            </div>
                            <div class="text-xs text-slate-600"><?= !empty($event['processed_at']) ? esc((string) $event['processed_at']) : 'unprocessed' ?></div>
                        </div>
                        <div class="mt-2 text-xs text-slate-600 font-mono break-all"><?= esc((string) ($event['provider_event_id'] ?? '-')) ?></div>
                        <pre class="mt-2 text-xs text-slate-700 whitespace-pre-wrap break-words"><?= esc($payloadPreview) ?><?= strlen($payloadRaw) > strlen($payloadPreview) ? '...' : '' ?></pre>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($recent_events)): ?>
                    <div class="text-sm text-slate-500">No webhook events journaled yet.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="space-y-6">
            <div class="glass-card rounded-2xl p-6" id="optouts">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Recent Opt-Outs</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
                                <th class="py-2 pr-4">Clinic</th>
                                <th class="py-2 pr-4">Patient</th>
                                <th class="py-2 pr-4">Number</th>
                                <th class="py-2 pr-4">Source</th>
                                <th class="py-2 pr-4">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (($recent_optouts ?? []) as $row): ?>
                                <tr class="border-b border-slate-100">
                                    <td class="py-2 pr-4"><?= esc((string) ($row['clinic_name'] ?? 'Unknown')) ?></td>
                                    <td class="py-2 pr-4"><?= !empty($row['patient_id']) ? (int) $row['patient_id'] : '-' ?></td>
                                    <td class="py-2 pr-4 font-mono text-xs"><?= esc((string) ($row['wa_number'] ?? $row['bsuid'] ?? '-')) ?></td>
                                    <td class="py-2 pr-4"><?= esc((string) ($row['source'] ?? '-')) ?></td>
                                    <td class="py-2 pr-4 text-slate-600"><?= esc((string) ($row['created_at'] ?? '-')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recent_optouts)): ?>
                                <tr><td colspan="5" class="py-4 text-slate-500">No opt-outs recorded yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Tenant Policies Snapshot</h3>
                <div class="max-h-[20rem] overflow-y-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
                                <th class="py-2 pr-4">Clinic</th>
                                <th class="py-2 pr-4">Suspended</th>
                                <th class="py-2 pr-4">Daily/Monthly</th>
                                <th class="py-2 pr-4">Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (($policies ?? []) as $policy): ?>
                                <tr class="border-b border-slate-100">
                                    <td class="py-2 pr-4">
                                        <div class="font-medium text-slate-800"><?= esc((string) ($policy['clinic_name'] ?? 'Unknown')) ?></div>
                                        <div class="text-xs text-slate-500">Clinic ID <?= (int) ($policy['clinic_id'] ?? 0) ?></div>
                                        <?php if (!empty($policy['suspended_reason'])): ?>
                                            <div class="text-xs text-rose-700 mt-1"><?= esc((string) $policy['suspended_reason']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 pr-4">
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold <?= !empty($policy['suspended']) ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' ?>">
                                            <?= !empty($policy['suspended']) ? 'Yes' : 'No' ?>
                                        </span>
                                    </td>
                                    <td class="py-2 pr-4"><?= (int) ($policy['daily_limit'] ?? 0) ?> / <?= (int) ($policy['monthly_limit'] ?? 0) ?></td>
                                    <td class="py-2 pr-4 text-slate-600"><?= esc((string) ($policy['updated_at'] ?? $policy['created_at'] ?? '-')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($policies)): ?>
                                <tr><td colspan="4" class="py-4 text-slate-500">No tenant policies created yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
