<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?php
    $storageBytes = (int) ($stats['storage_total_bytes'] ?? 0);
    $storageGb = $storageBytes > 0 ? ($storageBytes / 1024 / 1024 / 1024) : 0;
    $lastEventAt = $stats['last_event_at'] ?? null;
    $lastEventLabel = $lastEventAt ? date('M j, Y g:i A', strtotime($lastEventAt)) : lang('V3Platform.noEventsYet');
    $activeClinicRatio = ((int) ($stats['total_clinics'] ?? 0)) > 0
        ? round(((int) ($stats['active_clinics'] ?? 0) / (int) ($stats['total_clinics'] ?? 1)) * 100)
        : 0;

    $kpis = [
        [
            'label' => lang('V3Platform.totalClinics'),
            'value' => number_format($stats['total_clinics'] ?? 0),
            'detail' => lang('V3Platform.active3') . ' ' . number_format($stats['active_clinics'] ?? 0) . ' (' . $activeClinicRatio . '%)',
            'icon' => 'fas fa-hospital-user',
            'tone' => 'mint',
        ],
        [
            'label' => lang('V3Platform.activeSubscriptions'),
            'value' => number_format($stats['active_subscriptions'] ?? 0),
            'detail' => lang('V3Platform.renewalWatch') . ' ' . number_format($stats['expiring_subscriptions'] ?? 0),
            'icon' => 'fas fa-receipt',
            'tone' => 'softteal',
        ],
        [
            'label' => lang('V3Platform.estimatedMrr'),
            'value' => '$' . number_format((float) ($stats['mrr'] ?? 0), 2),
            'detail' => lang('V3Platform.normalizedActivePlanRevenue'),
            'icon' => 'fas fa-chart-line',
            'tone' => 'emerald',
        ],
        [
            'label' => lang('V3Platform.pendingRequests'),
            'value' => number_format($stats['pending_requests'] ?? 0),
            'detail' => lang('V3Platform.planChangesAwaitingReview'),
            'icon' => 'fas fa-inbox',
            'tone' => 'amber',
        ],
        [
            'label' => lang('V3Platform.activePlans'),
            'value' => number_format($stats['active_plans'] ?? 0),
            'detail' => lang('V3Platform.publishedServiceCatalog'),
            'icon' => 'fas fa-layer-group',
            'tone' => 'purple',
        ],
        [
            'label' => lang('V3Platform.activeUsers'),
            'value' => number_format($stats['active_users'] ?? 0),
            'detail' => lang('V3Platform.enabledOperatorAccounts'),
            'icon' => 'fas fa-user-shield',
            'tone' => 'parchment',
        ],
        [
            'label' => lang('V3Platform.storageUsed'),
            'value' => number_format($storageGb, 2) . ' GB',
            'detail' => lang('V3Platform.attachmentsAndGeneratedFiles'),
            'icon' => 'fas fa-hard-drive',
            'tone' => 'mint',
        ],
        [
            'label' => lang('V3Platform.auditEvents'),
            'value' => number_format($stats['events_7d'] ?? 0),
            'detail' => lang('V3Platform.auditWindow', ['count' => number_format($stats['events_30d'] ?? 0)]),
            'icon' => 'fas fa-wave-square',
            'tone' => 'coral',
        ],
    ];

    $quickActions = [
        ['label' => lang('V3Platform.onboardClinic'), 'href' => base_url('controlplane/onboarding/clinic/create'), 'icon' => 'fas fa-plus', 'btnClass' => 'cp-btn-primary'],
        ['label' => lang('V3Platform.createPlan'), 'href' => base_url('controlplane/plans/create'), 'icon' => 'fas fa-layer-group', 'btnClass' => 'cp-btn-light'],
        ['label' => lang('V3Platform.reviewRequests'), 'href' => base_url('controlplane/plan-requests'), 'icon' => 'fas fa-inbox', 'btnClass' => 'cp-btn-terracotta'],
        ['label' => lang('V3Platform.operations'), 'href' => base_url('controlplane/operations'), 'icon' => 'fas fa-cogs', 'btnClass' => 'cp-btn-light'],
    ];
?>

<div class="cp-dashboard">
    <!-- Command Header / Platform Stamp -->
    <div class="cp-command-bar">
        <div class="cp-command-bar-info">
            <div class="cp-command-bar-icon">
                <i class="fas fa-tachometer-alt"></i>
            </div>
            <div>
                <h1 class="cp-command-bar-title">
                    <span><?= esc(lang('V3Platform.saasPlatformOperations')) ?></span>
                    <span class="cp-badge cp-badge-teal">
                        <i class="fas fa-globe text-[10px]"></i> <?= esc(lang('V3Platform.globalMode')) ?>
                    </span>
                    <span class="cp-badge cp-badge-neutral text-xs">
                        <?= esc(lang('V3Platform.lastEvent')) ?> <?= esc($lastEventLabel) ?>
                    </span>
                </h1>
                <p class="cp-command-bar-sub"><?= esc(lang('V3Platform.monitorTenantClinicsSubscriptionsRevenuePlatformPulseAndOperatorQueuesAcrossAllClinics')) ?></p>
            </div>
        </div>
        <div class="cp-command-bar-actions">
            <?php foreach ($quickActions as $qa): ?>
                <a href="<?= esc($qa['href'], 'attr') ?>" class="cp-btn <?= esc($qa['btnClass']) ?>">
                    <i class="<?= esc($qa['icon']) ?>"></i>
                    <span><?= esc($qa['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Real KPI Strip -->
    <div class="cp-kpi-grid">
        <?php foreach (array_slice($kpis, 0, 4) as $kpi): ?>
            <div class="cp-kpi <?= esc($kpi['tone']) ?>">
                <div class="cp-kpi-label">
                    <i class="<?= esc($kpi['icon']) ?>"></i>
                    <span><?= esc($kpi['label']) ?></span>
                </div>
                <div class="cp-kpi-value"><?= esc($kpi['value']) ?></div>
                <div class="cp-kpi-foot"><?= esc($kpi['detail']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <details class="v3-platform-summary"><summary><?= esc(lang('V3Platform.operations')) ?></summary><dl class="v3-platform-secondary-facts"><?php foreach (array_slice($kpis, 4) as $kpi): ?><div><dt><?= esc($kpi['label']) ?></dt><dd><?= esc($kpi['value']) ?></dd><p><?= esc($kpi['detail']) ?></p></div><?php endforeach; ?></dl></details>
    <!-- Recent Clinics & Recent Plan Requests Columns -->
    <div class="cp-columns v3-platform-overview-split">
        <!-- Recent Clinics -->
        <div class="cp-panel mb-0">
            <div class="cp-panel-head">
                <div>
                    <h2><i class="fas fa-hospital text-[var(--denta-emerald-pine)]"></i> <?= esc(lang('V3Platform.recentClinicTenants')) ?></h2>
                    <p><?= esc(lang('V3Platform.newestRegistrationsInThePlatformRegistry')) ?></p>
                </div>
                <a href="<?= base_url('controlplane/clinics') ?>" class="cp-btn cp-btn-light cp-btn-sm">
                    <?= esc(lang('V3Platform.viewRegistry')) ?> <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            <div class="p-0">
                <table class="cp-table">
                    <thead>
                        <tr>
                            <th><?= esc(lang('V3Platform.clinicName')) ?></th>
                            <th><?= esc(lang('V3Platform.status')) ?></th>
                            <th class="text-end"><?= esc(lang('V3Platform.actions')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_clinics)): ?>
                            <?php foreach ($recent_clinics as $clinic): ?>
                                <?php $status = strtolower($clinic['status'] ?? 'inactive'); ?>
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded bg-[var(--denta-sandstone)] border border-[var(--denta-taupe-border)] flex items-center justify-center text-xs font-extrabold text-[var(--denta-ink)] flex-shrink-0">
                                                <?= esc(ui_badge_text($clinic['name'] ?? '', 2, 'CL')) ?>
                                            </div>
                                            <div class="min-w-0">
                                                <a href="<?= base_url('controlplane/clinics/' . $clinic['id']) ?>" class="font-bold text-[var(--denta-ink)] hover:text-[var(--denta-emerald-pine)] truncate block text-xs">
                                                    <?= esc($clinic['name'] ?? 'Clinic') ?>
                                                </a>
                                                <span class="text-xs v3-text-muted font-mono"><?= esc(lang('V3Platform.id2')) ?> <?= esc($clinic['id'] ?? '') ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($status === 'active'): ?>
                                            <span class="cp-badge cp-badge-active"><?= esc(lang('V3Platform.active')) ?></span>
                                        <?php elseif ($status === 'suspended'): ?>
                                            <span class="cp-badge cp-badge-suspended"><?= esc(lang('V3Platform.suspended')) ?></span>
                                        <?php else: ?>
                                            <span class="cp-badge cp-badge-neutral"><?= esc(ucfirst($status)) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="cp-btn cp-btn-primary cp-btn-sm" data-overview-inspect="<?= (int) $clinic['id'] ?>"><?= esc(lang('V3Platform.inspect')) ?></button>
                                        <a href="<?= base_url('controlplane/clinics/' . $clinic['id']) ?>" class="cp-btn cp-btn-light cp-btn-sm">
                                            <?= esc(lang('V3Platform.viewDetails')) ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center py-6 text-xs v3-text-muted"><?= esc(lang('V3Platform.noRecentClinicsFound')) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pending Plan Requests -->
        <?= view('control_plane/partials/tenant_inspector') ?>
    </div><div class="cp-panel mb-0">
            <div class="cp-panel-head">
                <div>
                    <h2><i class="fas fa-inbox text-[var(--denta-terracotta)]"></i> <?= esc(lang('V3Platform.pendingPlanRequests')) ?></h2>
                    <p><?= esc(lang('V3Platform.subscriptionAndUpgradeApprovalsRequiringReview')) ?></p>
                </div>
                <a href="<?= base_url('controlplane/plan-requests') ?>" class="cp-btn cp-btn-light cp-btn-sm">
                    <?= esc(lang('V3Platform.reviewQueue')) ?> <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            <div class="p-0">
                <table class="cp-table">
                    <thead>
                        <tr>
                            <th><?= esc(lang('V3Platform.clinic')) ?></th>
                            <th><?= esc(lang('V3Platform.requestedPlan')) ?></th>
                            <th><?= esc(lang('V3Platform.status')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_plan_requests)): ?>
                            <?php foreach ($recent_plan_requests as $request): ?>
                                <tr>
                                    <td>
                                        <b class="text-xs text-[var(--denta-ink)] block"><?= esc($request['clinic_name'] ?? 'Clinic') ?></b>
                                        <small class="text-xs v3-text-muted"><?= esc($request['summary'] ?? 'Plan change') ?></small>
                                    </td>
                                    <td>
                                        <span class="cp-badge cp-badge-teal text-xs"><?= esc($request['requested_plan_name'] ?? '—') ?></span>
                                    </td>
                                    <td>
                                        <span class="cp-badge cp-badge-amber"><?= esc(lang('V3Platform.pending')) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center py-6 text-xs v3-text-muted"><?= esc(lang('V3Platform.noPendingPlanChangeRequests')) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const dashboardChartData = <?= json_encode($chart_data ?? []) ?>;

    const baseChartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: '#526157', font: { weight: 600, size: 10 } },
            },
            y: {
                grid: { color: 'rgba(196, 185, 168, 0.35)' },
                ticks: { color: '#526157', font: { weight: 600, size: 10 } },
            },
        },
    };

    const mrrCtx = document.getElementById('mrrGrowthChart');
    if (mrrCtx && Array.isArray(dashboardChartData.mrr_series)) {
        const mrrLabels = dashboardChartData.mrr_series.map(point => point.label);
        const mrrTotals = dashboardChartData.mrr_series.map(point => point.value);
        const mrrGradient = mrrCtx.getContext('2d').createLinearGradient(0, 0, 0, 220);
        mrrGradient.addColorStop(0, 'rgba(15, 107, 99, 0.30)');
        mrrGradient.addColorStop(1, 'rgba(15, 107, 99, 0.02)');

        new Chart(mrrCtx, {
            type: 'line',
            data: {
                labels: mrrLabels,
                datasets: [{
                    label: 'MRR ($)',
                    data: mrrTotals,
                    borderColor: '#0F6B63',
                    backgroundColor: mrrGradient,
                    pointBackgroundColor: '#0F6B63',
                    pointBorderColor: '#FCFBF7',
                    pointRadius: 3.5,
                    pointHoverRadius: 5.5,
                    tension: 0.3,
                    fill: true,
                }],
            },
            options: {
                ...baseChartOptions,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: context => '$' + Number(context.parsed.y ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2 }),
                        },
                    },
                },
                scales: {
                    ...baseChartOptions.scales,
                    y: {
                        ...baseChartOptions.scales.y,
                        ticks: {
                            color: '#526157',
                            font: { weight: 600, size: 10 },
                            callback: value => typeof value === 'number' ? '$' + value.toLocaleString() : value,
                        },
                    },
                },
            },
        });
    }

    const activityCtx = document.getElementById('activityVolumeChart');
    if (activityCtx && Array.isArray(dashboardChartData.activity_series)) {
        new Chart(activityCtx, {
            type: 'bar',
            data: {
                labels: dashboardChartData.activity_series.map(point => point.day),
                datasets: [{
                    label: 'Events',
                    data: dashboardChartData.activity_series.map(point => point.total),
                    backgroundColor: '#C85A32',
                    borderRadius: 4,
                    maxBarThickness: 22,
                }],
            },
            options: baseChartOptions,
        });
    }

    const distributionCtx = document.getElementById('planDistributionChart');
    if (distributionCtx && Array.isArray(dashboardChartData.plan_distribution)) {
        new Chart(distributionCtx, {
            type: 'doughnut',
            data: {
                labels: dashboardChartData.plan_distribution.map(point => point.label),
                datasets: [{
                    data: dashboardChartData.plan_distribution.map(point => point.value),
                    backgroundColor: ['#0F6B63', '#162421', '#C85A32', '#C4B9A8', '#7E9388', '#AB4928'],
                    borderColor: '#FCFBF7',
                    borderWidth: 1.5,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#1B302B', boxWidth: 10, boxHeight: 10, font: { weight: 600, size: 10 } },
                    },
                },
            },
        });
    }

    const growthCtx = document.getElementById('clinicGrowthChart');
    if (growthCtx && Array.isArray(dashboardChartData.clinic_growth)) {
        const growthGradient = growthCtx.getContext('2d').createLinearGradient(0, 0, 0, 200);
        growthGradient.addColorStop(0, 'rgba(15, 107, 99, 0.25)');
        growthGradient.addColorStop(1, 'rgba(22, 36, 33, 0.02)');

        new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: dashboardChartData.clinic_growth.map(point => point.label),
                datasets: [{
                    label: 'New Clinics',
                    data: dashboardChartData.clinic_growth.map(point => point.value),
                    borderColor: '#0F6B63',
                    backgroundColor: growthGradient,
                    pointBackgroundColor: '#0F6B63',
                    pointBorderColor: '#FCFBF7',
                    pointRadius: 3.5,
                    pointHoverRadius: 5.5,
                    tension: 0.3,
                    fill: true,
                }],
            },
            options: baseChartOptions,
        });
    }
</script>
    <!-- Plan Mix, Clinic Growth, and Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3.5 mb-3.5">
        <!-- Plan Mix -->
        <div class="cp-panel mb-0">
            <div class="cp-panel-head">
                <h2><i class="fas fa-chart-pie text-[var(--denta-emerald-pine)]"></i> <?= esc(lang('V3Platform.planMix')) ?></h2>
                <span class="cp-badge cp-badge-neutral"><?= esc(lang('V3Platform.distribution')) ?></span>
            </div>
            <div class="cp-panel-body">
                <div class="cp-chart-container">
                    <canvas id="planDistributionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Clinic Growth -->
        <div class="cp-panel mb-0">
            <div class="cp-panel-head">
                <h2><i class="fas fa-arrow-trend-up text-[var(--denta-emerald-pine)]"></i> <?= esc(lang('V3Platform.clinicGrowth6Mo')) ?></h2>
                <span class="cp-badge cp-badge-mint"><?= esc(lang('V3Platform.newTenants')) ?></span>
            </div>
            <div class="cp-panel-body">
                <div class="cp-chart-container">
                    <canvas id="clinicGrowthChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Operator Shortcuts & Activity Pulse -->
        <div class="cp-panel mb-0">
            <div class="cp-panel-head">
                <h2><i class="fas fa-bolt text-[var(--denta-terracotta)]"></i> <?= esc(lang('V3Platform.operatorPulse')) ?></h2>
                <span class="cp-badge cp-badge-teal"><?= esc(lang('V3Platform.adminQueues')) ?></span>
            </div>
            <div class="cp-panel-body space-y-3">
                <div class="cp-stats-grid">
                    <div class="cp-metric-card">
                        <small><?= esc(lang('V3Platform.storageFootprint')) ?></small>
                        <strong><?= number_format($storageGb, 2) ?> GB</strong>
                    </div>
                    <div class="cp-metric-card">
                        <small><?= esc(lang('V3Platform.label7dAuditEvents')) ?></small>
                        <strong><?= number_format($stats['events_7d'] ?? 0) ?></strong>
                    </div>
                </div>

                <div>
                    <div class="text-xs font-bold uppercase tracking-wider v3-text-muted mb-1.5"><?= esc(lang('V3Platform.storageHotspots')) ?></div>
                    <div class="space-y-1.5">
                        <?php if (!empty($storage_top ?? [])): ?>
                            <?php foreach (array_slice($storage_top, 0, 3) as $row): ?>
                                <?php
                                    $clinicName = $row['clinic_name'] ?? 'Clinic';
                                    $sizeBytes = (int) ($row['total_size'] ?? 0);
                                    $sizeGb = $sizeBytes > 0 ? ($sizeBytes / 1024 / 1024 / 1024) : 0;
                                ?>
                                <div class="flex items-center justify-between p-2 rounded bg-[var(--denta-parchment)] border border-[var(--denta-taupe-border)] text-xs">
                                    <span class="font-bold text-[var(--denta-ink)] truncate max-w-[170px]"><?= esc($clinicName) ?></span>
                                    <span class="font-semibold text-[var(--denta-emerald-pine)]"><?= number_format($sizeGb, 2) ?> GB</span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-xs v3-text-muted py-2 text-center"><?= esc(lang('V3Platform.noStorageUsageRecordedYet')) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Charts Split Layout -->
    <div class="cp-columns mb-3.5">
        <!-- Revenue Trend -->
        <div class="cp-panel mb-0">
            <div class="cp-panel-head">
                <div>
                    <h2><i class="fas fa-chart-line text-[var(--denta-emerald-pine)]"></i> <?= esc(lang('V3Platform.revenueAndSubscriptionTrend')) ?></h2>
                    <p>Estimated monthly recurring revenue (MRR) normalized from active subscriptions.</p>
                </div>
                <span class="cp-badge cp-badge-mint"><?= esc(lang('V3Platform.mrrVolume')) ?></span>
            </div>
            <div class="cp-panel-body">
                <div class="cp-chart-container">
                    <canvas id="mrrGrowthChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Audit Volume -->
        <div class="cp-panel mb-0">
            <div class="cp-panel-head">
                <div>
                    <h2><i class="fas fa-wave-square text-[var(--denta-terracotta)]"></i> <?= esc(lang('V3Platform.platformAuditVolume')) ?></h2>
                    <p><?= esc(lang('V3Platform.controlPlaneAndTenantEventsRecordedOverTheLast14Days')) ?></p>
                </div>
                <span class="cp-badge cp-badge-neutral"><?= number_format($stats['events_7d'] ?? 0) ?> / 7d</span>
            </div>
            <div class="cp-panel-body">
                <div class="cp-chart-container">
                    <canvas id="activityVolumeChart"></canvas>
                </div>
            </div>
        </div>
    </div>


<script id="v3-overview-tenant-data" type="application/json"><?= json_encode(array_values($recent_clinics ?? []), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
<script>
document.addEventListener('DOMContentLoaded',()=>{
    const data=JSON.parse(document.getElementById('v3-overview-tenant-data')?.textContent||'[]');
    function inspect(id){const row=data.find(item=>String(item.id)===String(id));if(!row)return;const values={name:row.name,slug:row.slug,status:({active:<?= json_encode(lang('V3Platform.active'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,suspended:<?= json_encode(lang('V3Platform.suspended'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,inactive:<?= json_encode(lang('V3Platform.inactive'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>})[row.status]||row.status,admin:row.admin_email,plan:row.plan_name};Object.entries(values).forEach(([key,value])=>{const el=document.getElementById('v3-inspector-'+key);if(el)el.textContent=value||'\u2014'});const link=document.getElementById('v3-inspector-detail');link.href=<?= json_encode(base_url('controlplane/clinics/'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>+encodeURIComponent(String(row.id));link.hidden=false;document.querySelectorAll('[data-overview-inspect]').forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.overviewInspect===String(id))));}
    document.querySelectorAll('[data-overview-inspect]').forEach(button=>button.addEventListener('click',()=>inspect(button.dataset.overviewInspect)));
    document.querySelectorAll('[data-inspector-tab]').forEach(button=>button.addEventListener('click',()=>{document.querySelectorAll('[data-inspector-tab]').forEach(b=>b.setAttribute('aria-pressed',String(b===button)));document.querySelectorAll('[data-inspector-pane]').forEach(p=>p.hidden=p.dataset.inspectorPane!==button.dataset.inspectorTab)}));
    if(data[0])inspect(data[0].id);
});
</script>
<?= $this->endSection() ?>