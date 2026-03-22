<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?php
$stats = $stats ?? [];
$failureRate = (float) ($stats['failure_rate_24h'] ?? 0);
$activeTab = $active_tab ?? 'dashboard';
$statusBreakdown = $status_breakdown ?? [];
$recentStream = $recent_stream ?? [];
$needsAttention = $needs_attention ?? [];
$recentAttempts = $recent_attempts ?? [];
$recentEvents = $recent_events ?? [];
$recentOptouts = $recent_optouts ?? [];
$operatorGuidance = $operator_guidance ?? [];
?>

<style>
    .wa-tab.hidden { display: none !important; }
    .journal-pane.hidden { display: none !important; }
    .nav-btn-active {
        background: linear-gradient(135deg, #0f766e, #0f172a);
        color: #fff !important;
        box-shadow: 0 16px 30px -18px rgba(15, 23, 42, 0.45);
    }
    .journal-btn-active {
        background: #ffffff;
        color: #0f172a !important;
        box-shadow: 0 12px 24px -18px rgba(15, 23, 42, 0.35);
    }
    .wa-status-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        border-width: 1px;
        border-radius: 9999px;
        padding: 0.35rem 0.75rem;
        font-size: 0.72rem;
        font-weight: 800;
        line-height: 1;
    }
    .wa-dot {
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 9999px;
        display: inline-block;
    }
    .wa-table-wrap { overflow-x: auto; }
    .wa-table {
        width: 100%;
        min-width: 980px;
        border-collapse: separate;
        border-spacing: 0;
    }
    .wa-table th {
        padding: 1rem 1.25rem;
        font-size: 0.68rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .wa-table td {
        padding: 1rem 1.25rem;
        vertical-align: top;
        border-bottom: 1px solid #f1f5f9;
    }
    .wa-table tbody tr:hover { background: rgba(248, 250, 252, 0.85); }
    .wa-kpi-card {
        background: linear-gradient(180deg, rgba(255,255,255,0.88), rgba(255,255,255,0.76));
        border: 1px solid rgba(226,232,240,0.9);
        box-shadow: 0 24px 50px -40px rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(18px);
    }
    .wa-guidance-card {
        background: linear-gradient(135deg, #ecfeff, #f8fafc);
        border: 1px solid #bae6fd;
    }
    .wa-log-card {
        background: linear-gradient(180deg, rgba(255,255,255,0.9), rgba(248,250,252,0.84));
        border: 1px solid rgba(226,232,240,0.92);
        box-shadow: 0 24px 50px -42px rgba(15, 23, 42, 0.35);
    }
    .copy-toast { animation: slide-up 0.25s ease-out; }
    @keyframes slide-up {
        from { transform: translateY(100%); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>

<div class="space-y-8">
    <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
        <div class="flex items-start gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-gradient-to-br from-emerald-500 via-teal-600 to-slate-900 text-white shadow-2xl shadow-emerald-500/20">
                <i class="fab fa-whatsapp text-3xl"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-3xl font-black text-slate-900">WhatsApp Command Center</h1>
                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-black text-emerald-700">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Live transport enabled
                    </span>
                </div>
                <p class="mt-2 max-w-3xl text-sm text-slate-600">
                    Operator view for message delivery, webhook health, out-of-session failures, and live test sends. This page is built for answering three questions fast: what happened, why it happened, and what the operator should do next.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 rounded-2xl border border-slate-200 bg-white/80 p-1.5 shadow-sm backdrop-blur">
            <button onclick="showWATab('dashboard')" id="btn-dashboard" class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-600 transition-all hover:bg-white hover:text-slate-900">Overview</button>
            <button onclick="showWATab('journal')" id="btn-journal" class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-600 transition-all hover:bg-white hover:text-slate-900">Audit Log</button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <?php foreach ($operatorGuidance as $guidance): ?>
            <div class="wa-guidance-card rounded-2xl p-5">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-9 w-9 items-center justify-center rounded-2xl bg-sky-100 text-sky-700">
                        <i class="fas fa-circle-info"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900"><?= esc($guidance['title']) ?></h3>
                        <p class="mt-1 text-sm leading-6 text-slate-600"><?= esc($guidance['body']) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div>
                <h4 class="text-sm font-black text-slate-900">Platform secrets and tenant policies</h4>
                <p class="mt-1 text-xs text-slate-500">Credentials, fallback templates, and clinic policy limits remain under Global Settings.</p>
            </div>
            <a href="<?= base_url('controlplane/settings?tab=communications&subtab=whatsapp') ?>" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800">
                Open Settings
            </a>
        </div>
        <div class="flex items-center justify-between gap-4 rounded-2xl border border-amber-200 bg-amber-50 p-4">
            <div>
                <h4 class="text-sm font-black text-amber-900">Operational rule of thumb</h4>
                <p class="mt-1 text-xs text-amber-800">If you see error <strong>131047</strong>, switch the operator to template mode. Free-form retry is the wrong move.</p>
            </div>
            <a href="<?= base_url('controlplane/operations') ?>" class="rounded-xl bg-amber-600 px-4 py-2 text-sm font-bold text-white hover:bg-amber-700">
                Open Ops Console
            </a>
        </div>
    </div>

    <div class="wa-log-card rounded-3xl p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-3xl">
                <div class="flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.18em] text-emerald-700">
                    <i class="fas fa-paper-plane"></i>
                    Live test send
                </div>
                <h2 class="mt-2 text-2xl font-black text-slate-900">Run a real send through the production code path</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    This form writes to the real notification ledger, dispatches through the same driver used by clinic workflows, and immediately exposes the resulting delivery state here. Use <strong>Text</strong> only inside an open 24-hour session. Use <strong>Template</strong> for business-initiated or out-of-session delivery.
                </p>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[11px] font-black uppercase tracking-[0.16em] text-emerald-700">
                Audit + dispatcher + webhook verified
            </div>
        </div>

        <form method="post" action="<?= base_url('controlplane/whatsapp/send-test') ?>" class="mt-6 space-y-5">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">Clinic</label>
                    <select name="clinic_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900" required>
                        <option value="">Select ready clinic</option>
                        <?php foreach (($test_send_clinics ?? []) as $clinic): ?>
                            <option value="<?= (int) $clinic['clinic_id'] ?>" <?= old('clinic_id') == $clinic['clinic_id'] ? 'selected' : '' ?>>
                                <?= esc($clinic['clinic_name']) ?> (ID <?= (int) $clinic['clinic_id'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">Target</label>
                    <input type="text" name="target_number" value="<?= esc(old('target_number')) ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900" placeholder="+970599999999 or BSUID / parent BSUID" required>
                    <p class="mt-1 text-xs text-slate-500">BSUID fields usually populate automatically after Meta webhook traffic reaches this contact.</p>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">Mode</label>
                    <select id="wa-test-send-mode" name="send_mode" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900">
                        <option value="text" <?= old('send_mode', 'text') === 'text' ? 'selected' : '' ?>>Text inside active session</option>
                        <option value="template" <?= old('send_mode') === 'template' ? 'selected' : '' ?>>Template / out-of-session</option>
                    </select>
                </div>
            </div>

            <div id="wa-test-text-fields" class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-[0.16em] text-slate-500">Message body</label>
                <textarea name="message" rows="4" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900" placeholder="Manual control-plane test message"><?= esc(old('message')) ?></textarea>
            </div>
            <div id="wa-test-template-fields" class="hidden space-y-5">
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">Template source</label>
                        <select id="wa-template-source" name="template_source" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900">
                            <option value="explicit" <?= old('template_source', 'explicit') === 'explicit' ? 'selected' : '' ?>>Use explicit template name</option>
                            <option value="fallback" <?= old('template_source') === 'fallback' ? 'selected' : '' ?>>Use clinic fallback template</option>
                        </select>
                    </div>
                    <div id="wa-template-name-wrap">
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">Template name</label>
                        <input type="text" name="template_name" value="<?= esc(old('template_name')) ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900" placeholder="appointment_reminder_v1">
                    </div>
                    <div id="wa-template-language-wrap">
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">Template language</label>
                        <input type="text" name="template_language" value="<?= esc(old('template_language', 'en_US')) ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900" placeholder="en_US">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">Header text parameter</label>
                        <input type="text" name="template_header_text" value="<?= esc(old('template_header_text')) ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900" placeholder="Optional header text">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">Body parameters</label>
                        <textarea name="template_body_params" rows="4" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900" placeholder="One parameter per line&#10;2026-03-22&#10;10:00"><?= esc(old('template_body_params')) ?></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">Button type</label>
                        <select name="template_button_type" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900">
                            <option value="">No button parameter</option>
                            <option value="quick_reply" <?= old('template_button_type') === 'quick_reply' ? 'selected' : '' ?>>Quick reply</option>
                            <option value="url" <?= old('template_button_type') === 'url' ? 'selected' : '' ?>>URL suffix</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">Button index</label>
                        <input type="text" name="template_button_index" value="<?= esc(old('template_button_index', '0')) ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900" placeholder="0">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">Button value</label>
                        <input type="text" name="template_button_value" value="<?= esc(old('template_button_value')) ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900" placeholder="Optional payload or URL suffix">
                    </div>
                </div>

                <details class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <summary class="cursor-pointer text-sm font-black text-slate-900">Advanced override: raw template components JSON</summary>
                    <p class="mt-2 text-xs text-slate-500">Leave this empty for guided operator mode. If you paste JSON here, it overrides the guided fields above.</p>
                    <textarea name="template_components_json" rows="5" class="mt-3 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-xs font-mono text-slate-900" placeholder='[{"type":"body","parameters":[{"type":"text","text":"2026-03-22"},{"type":"text","text":"10:00"}]}]'><?= esc(old('template_components_json')) ?></textarea>
                </details>
            </div>

            <div class="flex flex-col gap-4 border-t border-slate-100 pt-5 lg:flex-row lg:items-center lg:justify-between">
                <p class="max-w-3xl text-xs leading-6 text-slate-500">
                    Professional operator workflow: use text only when the customer has reopened the conversation window. For cold-start or re-engagement sends, use a reviewed template and capture the delivery state from the activity log below.
                </p>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700">
                    <i class="fab fa-whatsapp mr-2"></i>
                    Send test message
                </button>
            </div>
        </form>
    </div>

    <div id="dashboard-tab" class="wa-tab space-y-8">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="wa-kpi-card rounded-3xl p-5">
                <div class="text-[11px] font-black uppercase tracking-[0.14em] text-slate-500">Successful sends (24h)</div>
                <div class="mt-3 text-3xl font-black text-slate-900"><?= number_format($stats['attempts_24h_sentish'] ?? 0) ?></div>
                <p class="mt-2 text-xs text-slate-500">Sent, delivered, or read attempts.</p>
            </div>
            <div class="wa-kpi-card rounded-3xl p-5">
                <div class="text-[11px] font-black uppercase tracking-[0.14em] text-slate-500">Read confirmations (24h)</div>
                <div class="mt-3 text-3xl font-black text-slate-900"><?= number_format($stats['attempts_24h_read'] ?? 0) ?></div>
                <p class="mt-2 text-xs text-slate-500">Highest-confidence engagement signal.</p>
            </div>
            <div class="wa-kpi-card rounded-3xl p-5">
                <div class="text-[11px] font-black uppercase tracking-[0.14em] text-slate-500">Failures (24h)</div>
                <div class="mt-3 text-3xl font-black text-slate-900"><?= number_format($stats['attempts_24h_failed'] ?? 0) ?></div>
                <p class="mt-2 text-xs text-slate-500">Current failure rate: <?= number_format($failureRate, 2) ?>%.</p>
            </div>
            <div class="wa-kpi-card rounded-3xl p-5">
                <div class="text-[11px] font-black uppercase tracking-[0.14em] text-slate-500">131047 window failures</div>
                <div class="mt-3 text-3xl font-black text-slate-900"><?= number_format($stats['attempts_24h_session_restricted'] ?? 0) ?></div>
                <p class="mt-2 text-xs text-slate-500">Operators should switch to template mode.</p>
            </div>
            <div class="wa-kpi-card rounded-3xl p-5">
                <div class="text-[11px] font-black uppercase tracking-[0.14em] text-slate-500">Webhook events (24h)</div>
                <div class="mt-3 text-3xl font-black text-slate-900"><?= number_format($stats['events_24h'] ?? 0) ?></div>
                <p class="mt-2 text-xs text-slate-500">Incoming delivery and identity updates.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <div class="xl:col-span-2 wa-log-card rounded-3xl p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-black text-slate-900">Needs attention</h2>
                        <p class="mt-1 text-sm text-slate-500">The most recent items that require an operator decision or follow-up.</p>
                    </div>
                    <button onclick="showWATab('journal')" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 hover:border-slate-300">
                        Open full audit log
                    </button>
                </div>

                <div class="mt-5 space-y-3">
                    <?php if ($needsAttention === []): ?>
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">
                            No actionable failures in the recent sample. The latest activity does not currently require operator intervention.
                        </div>
                    <?php else: ?>
                        <?php foreach ($needsAttention as $item): ?>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="space-y-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="wa-status-chip <?= esc($item['status_meta']['class']) ?>"><span class="wa-dot bg-current"></span><?= esc($item['status_meta']['label']) ?></span>
                                            <span class="text-xs font-black text-slate-500">Clinic: <?= esc($item['clinic_name']) ?></span>
                                            <span class="text-xs text-slate-400">Notification #<?= (int) $item['notification_id'] ?> · Attempt #<?= (int) $item['attempt_id'] ?></span>
                                        </div>
                                        <div class="text-sm font-black text-slate-900"><?= esc($item['recipient']['label']) ?></div>
                                        <div class="text-xs text-slate-500"><?= esc($item['content']['title']) ?> · <?= esc($item['content']['subtitle']) ?></div>
                                        <div class="pt-1 text-sm font-semibold text-rose-700"><?= esc($item['failure']['title']) ?></div>
                                        <div class="text-xs leading-6 text-slate-500"><?= esc($item['failure']['detail']) ?></div>
                                    </div>
                                    <div class="min-w-[14rem] rounded-2xl bg-slate-50 p-4 text-xs text-slate-600">
                                        <div class="font-black text-slate-700">Operator action</div>
                                        <div class="mt-2 leading-6">
                                            <?= esc($item['failure']['kind'] === 'session_window' ? 'Retry with an approved template from this page or ask the customer to reply first.' : ($item['failure']['kind'] === 'optout' ? 'Do not retry. Respect the opt-out state until a valid opt-in is captured.' : 'Review provider details and webhook trail before retrying.')) ?>
                                        </div>
                                        <div class="mt-3 border-t border-slate-200 pt-3 text-[11px] text-slate-500">
                                            <?= esc($item['created_label']) ?> · <?= esc($item['created_relative']) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="wa-log-card rounded-3xl p-6">
                <h2 class="text-xl font-black text-slate-900">Recent status mix</h2>
                <p class="mt-1 text-sm text-slate-500">Fast read on the current activity sample.</p>
                <div class="mt-5 space-y-3">
                    <?php foreach ($statusBreakdown as $statusKey => $count): ?>
                        <?php
                        $meta = match ($statusKey) {
                            'read' => ['label' => 'Read', 'class' => 'bg-emerald-100 text-emerald-800 border-emerald-200'],
                            'delivered' => ['label' => 'Delivered', 'class' => 'bg-teal-100 text-teal-800 border-teal-200'],
                            'sent' => ['label' => 'Sent', 'class' => 'bg-blue-100 text-blue-800 border-blue-200'],
                            'failed' => ['label' => 'Failed', 'class' => 'bg-rose-100 text-rose-800 border-rose-200'],
                            'blocked' => ['label' => 'Blocked', 'class' => 'bg-amber-100 text-amber-800 border-amber-200'],
                            default => ['label' => 'Pending', 'class' => 'bg-slate-100 text-slate-700 border-slate-200'],
                        };
                        ?>
                        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3">
                            <span class="wa-status-chip <?= esc($meta['class']) ?>"><span class="wa-dot bg-current"></span><?= esc($meta['label']) ?></span>
                            <span class="text-lg font-black text-slate-900"><?= number_format((int) $count) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="wa-log-card rounded-3xl p-6">
            <div class="flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-xl font-black text-slate-900">Recent message activity</h2>
                    <p class="mt-1 text-sm text-slate-500">Every row shows the recipient, message type, current delivery state, and provider details.</p>
                </div>
                <div class="text-xs text-slate-500">Showing the most recent <?= count($recentStream) ?> activity items.</div>
            </div>
            <div class="wa-table-wrap mt-5">
                <table class="wa-table">
                    <thead>
                        <tr>
                            <th>Recipient</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Provider trail</th>
                            <th>Failure / guidance</th>
                            <th>Timeline</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentStream as $item): ?>
                            <tr>
                                <td>
                                    <div class="font-black text-slate-900"><?= esc($item['recipient']['label']) ?></div>
                                    <div class="mt-1 text-xs text-slate-500"><?= esc($item['recipient']['secondary']) ?></div>
                                    <div class="mt-2 text-[11px] font-semibold text-slate-400"><?= esc($item['clinic_name']) ?></div>
                                </td>
                                <td>
                                    <div class="font-black text-slate-900"><?= esc($item['content']['title']) ?></div>
                                    <div class="mt-1 text-xs text-slate-500"><?= esc($item['content']['subtitle']) ?></div>
                                    <div class="mt-2 text-xs leading-6 text-slate-600"><?= esc($item['content']['preview']) ?></div>
                                </td>
                                <td>
                                    <div class="wa-status-chip <?= esc($item['status_meta']['class']) ?>"><span class="wa-dot bg-current"></span><?= esc($item['status_meta']['label']) ?></div>
                                    <div class="mt-2 text-xs text-slate-500">Notification: <?= esc(strtoupper($item['notification_status'])) ?></div>
                                    <div class="mt-1 text-xs text-slate-500">Attempt: <?= esc(strtoupper($item['attempt_status'])) ?></div>
                                </td>
                                <td>
                                    <div class="text-xs font-black uppercase tracking-[0.12em] text-slate-400"><?= esc($item['delivery_meta']['label']) ?></div>
                                    <div class="mt-2 break-all font-mono text-xs text-slate-700"><?= esc($item['delivery_meta']['value']) ?></div>
                                </td>
                                <td>
                                    <?php if ($item['failure']['kind'] === 'none'): ?>
                                        <div class="text-sm font-semibold text-emerald-700">No operator issue detected</div>
                                        <div class="mt-1 text-xs text-slate-500">The message has either advanced successfully or is still awaiting webhook progression.</div>
                                    <?php else: ?>
                                        <div class="text-sm font-semibold text-rose-700"><?= esc($item['failure']['title']) ?></div>
                                        <div class="mt-1 text-xs leading-6 text-slate-500"><?= esc($item['failure']['detail']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="text-sm font-black text-slate-900"><?= esc($item['created_label']) ?></div>
                                    <div class="mt-1 text-xs text-slate-500"><?= esc($item['created_relative']) ?></div>
                                    <div class="mt-2 text-[11px] text-slate-400">Notification #<?= (int) $item['notification_id'] ?> · Attempt #<?= (int) $item['attempt_id'] ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="journal-tab" class="wa-tab hidden space-y-8">
        <div class="wa-log-card overflow-hidden rounded-3xl">
            <div class="flex flex-wrap justify-center gap-2 border-b border-slate-200 bg-slate-50 p-2">
                <button onclick="showJournalSubTab('attempts')" id="btn-j-attempts" class="rounded-xl px-5 py-2 text-xs font-black text-slate-500 transition-all">Message ledger</button>
                <button onclick="showJournalSubTab('webhooks')" id="btn-j-webhooks" class="rounded-xl px-5 py-2 text-xs font-black text-slate-500 transition-all">Webhook trail</button>
                <button onclick="showJournalSubTab('optouts')" id="btn-j-optouts" class="rounded-xl px-5 py-2 text-xs font-black text-slate-500 transition-all">Opt-outs</button>
            </div>

            <div id="journal-attempts" class="journal-pane p-0">
                <div class="wa-table-wrap">
                    <table class="wa-table">
                        <thead>
                            <tr>
                                <th>IDs</th>
                                <th>Recipient</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Provider</th>
                                <th>Failure detail</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentAttempts as $item): ?>
                                <tr>
                                    <td>
                                        <div class="font-black text-slate-900">Notification #<?= (int) $item['notification_id'] ?></div>
                                        <div class="mt-1 text-xs text-slate-500">Attempt #<?= (int) $item['attempt_id'] ?></div>
                                        <div class="mt-2 text-[11px] text-slate-400"><?= esc($item['clinic_name']) ?></div>
                                    </td>
                                    <td>
                                        <div class="font-black text-slate-900"><?= esc($item['recipient']['label']) ?></div>
                                        <div class="mt-1 text-xs text-slate-500"><?= esc($item['recipient']['secondary']) ?></div>
                                    </td>
                                    <td>
                                        <div class="font-black text-slate-900"><?= esc($item['content']['title']) ?></div>
                                        <div class="mt-1 text-xs text-slate-500"><?= esc($item['content']['subtitle']) ?></div>
                                        <div class="mt-2 text-xs leading-6 text-slate-600"><?= esc($item['content']['preview']) ?></div>
                                    </td>
                                    <td>
                                        <div class="wa-status-chip <?= esc($item['status_meta']['class']) ?>"><span class="wa-dot bg-current"></span><?= esc($item['status_meta']['label']) ?></div>
                                        <div class="mt-2 text-xs text-slate-500">Notification: <?= esc($item['notification_status']) ?></div>
                                        <div class="mt-1 text-xs text-slate-500">Attempt: <?= esc($item['attempt_status']) ?></div>
                                    </td>
                                    <td>
                                        <div class="text-xs font-black uppercase tracking-[0.12em] text-slate-400"><?= esc($item['delivery_meta']['label']) ?></div>
                                        <div class="mt-2 break-all font-mono text-xs text-slate-700"><?= esc($item['delivery_meta']['value']) ?></div>
                                        <?php if ($item['error_code'] !== ''): ?>
                                            <div class="mt-2 text-xs font-semibold text-rose-700">Error code <?= esc($item['error_code']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($item['failure']['kind'] === 'none'): ?>
                                            <div class="text-sm font-semibold text-emerald-700">No failure recorded</div>
                                        <?php else: ?>
                                            <div class="text-sm font-semibold text-rose-700"><?= esc($item['failure']['title']) ?></div>
                                            <div class="mt-1 text-xs leading-6 text-slate-500"><?= esc($item['failure']['detail']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="text-sm font-black text-slate-900"><?= esc($item['created_label']) ?></div>
                                        <div class="mt-1 text-xs text-slate-500"><?= esc($item['created_relative']) ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="journal-webhooks" class="journal-pane hidden p-6 space-y-4">
                <?php foreach ($recentEvents as $event): ?>
                    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-black text-slate-700"><?= esc($event['event_type']) ?></span>
                                    <?php if ($event['summary']['code'] !== ''): ?>
                                        <span class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-[11px] font-black text-rose-700">Code <?= esc($event['summary']['code']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-sm font-black text-slate-900"><?= esc($event['summary']['title']) ?></div>
                                <div class="text-xs leading-6 text-slate-500"><?= esc($event['summary']['detail']) ?></div>
                                <?php if ($event['summary']['subject'] !== ''): ?>
                                    <div class="break-all font-mono text-xs text-slate-600"><?= esc($event['summary']['subject']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="min-w-[13rem] rounded-2xl bg-slate-50 p-4 text-xs text-slate-500">
                                <div class="font-black text-slate-700">Processed</div>
                                <div class="mt-1"><?= esc($event['processed_label']) ?></div>
                                <div class="mt-1"><?= esc($event['processed_relative']) ?></div>
                                <button onclick="copyToClipboard('<?= htmlspecialchars($event['payload_json'], ENT_QUOTES) ?>')" class="mt-3 rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-black text-slate-700 hover:border-slate-300">Copy raw JSON</button>
                            </div>
                        </div>
                        <details class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <summary class="cursor-pointer text-xs font-black uppercase tracking-[0.14em] text-slate-600">View raw payload</summary>
                            <pre class="mt-3 overflow-x-auto rounded-2xl bg-slate-900 p-4 text-[11px] text-emerald-300"><?= esc($event['payload_pretty']) ?></pre>
                        </details>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="journal-optouts" class="journal-pane hidden p-0">
                <div class="wa-table-wrap">
                    <table class="wa-table" style="min-width:820px;">
                        <thead>
                            <tr>
                                <th>Clinic</th>
                                <th>wa_number</th>
                                <th>BSUID</th>
                                <th>Parent BSUID</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOptouts as $optout): ?>
                                <tr>
                                    <td>
                                        <div class="font-black text-slate-900"><?= esc($optout['clinic_name']) ?></div>
                                    </td>
                                    <td><code class="text-xs font-mono text-slate-700"><?= esc($optout['wa_number'] !== '' ? '+' . preg_replace('/\D+/', '', $optout['wa_number']) : '—') ?></code></td>
                                    <td><code class="break-all text-xs font-mono text-slate-700"><?= esc($optout['bsuid'] !== '' ? $optout['bsuid'] : '—') ?></code></td>
                                    <td><code class="break-all text-xs font-mono text-slate-700"><?= esc($optout['parent_bsuid'] !== '' ? $optout['parent_bsuid'] : '—') ?></code></td>
                                    <td>
                                        <div class="text-sm font-black text-slate-900"><?= esc($optout['created_label']) ?></div>
                                        <div class="mt-1 text-xs text-slate-500"><?= esc($optout['created_relative']) ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function showWATab(tabName) {
    document.querySelectorAll('.wa-tab').forEach(el => el.classList.add('hidden'));
    const target = document.getElementById(tabName + '-tab');
    if (target) target.classList.remove('hidden');

    document.querySelectorAll('#btn-dashboard, #btn-journal').forEach(btn => {
        btn.classList.remove('nav-btn-active');
    });
    const activeBtn = document.getElementById('btn-' + tabName);
    if (activeBtn) activeBtn.classList.add('nav-btn-active');
}

function showJournalSubTab(subName) {
    document.querySelectorAll('.journal-pane').forEach(el => el.classList.add('hidden'));
    const target = document.getElementById('journal-' + subName);
    if (target) target.classList.remove('hidden');

    document.querySelectorAll('[id^="btn-j-"]').forEach(btn => {
        btn.classList.remove('journal-btn-active');
    });
    const activeBtn = document.getElementById('btn-j-' + subName);
    if (activeBtn) activeBtn.classList.add('journal-btn-active');
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => showToast('Copied raw payload'));
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'copy-toast fixed bottom-8 left-1/2 z-[100] -translate-x-1/2 rounded-2xl border border-white/10 bg-slate-900 px-6 py-3 text-[11px] font-black uppercase tracking-[0.16em] text-white shadow-2xl';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 1800);
}

document.addEventListener('DOMContentLoaded', () => {
    showWATab('<?= $activeTab ?>');
    showJournalSubTab('attempts');

    const sendMode = document.getElementById('wa-test-send-mode');
    const textFields = document.getElementById('wa-test-text-fields');
    const templateFields = document.getElementById('wa-test-template-fields');
    const templateSource = document.getElementById('wa-template-source');
    const templateNameWrap = document.getElementById('wa-template-name-wrap');
    const templateLanguageWrap = document.getElementById('wa-template-language-wrap');

    const syncSendModeFields = () => {
        if (!sendMode || !textFields || !templateFields) return;
        const templateMode = sendMode.value === 'template';
        textFields.classList.toggle('hidden', templateMode);
        templateFields.classList.toggle('hidden', !templateMode);
    };

    const syncTemplateSourceFields = () => {
        if (!templateSource || !templateNameWrap || !templateLanguageWrap) return;
        const fallbackMode = templateSource.value === 'fallback';
        templateNameWrap.classList.toggle('hidden', fallbackMode);
        templateLanguageWrap.classList.toggle('hidden', fallbackMode);
    };

    if (sendMode) {
        sendMode.addEventListener('change', syncSendModeFields);
        syncSendModeFields();
    }

    if (templateSource) {
        templateSource.addEventListener('change', syncTemplateSourceFields);
        syncTemplateSourceFields();
    }
});
</script>

<?= $this->endSection() ?>
