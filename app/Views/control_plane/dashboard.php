<?= $this->extend('layouts/main_control_plane') ?>
<?= $this->section('content') ?>
<?php $storageBytes=(int)($stats['storage_total_bytes']??0);$storageGb=$storageBytes/1073741824;$lastEventAt=$stats['last_event_at']??null; ?>
<header class="v4-page-heading"><div><h1><?= esc(lang('V4.platformDesk')) ?></h1><p><?= esc(lang('V4.platformDeskHelp')) ?></p></div><a class="v4-button v4-button--primary" href="<?= base_url('controlplane/onboarding/clinic/create') ?>"><i class="fas fa-plus" aria-hidden="true"></i><?= esc(lang('UI.cpOnboarding')) ?></a></header>
<div class="v4-platform-overview">
<section class="v4-surface"><h2><?= esc(lang('V4.recentClinics')) ?></h2><div class="v4-platform-list"><?php foreach(($recent_clinics??[]) as $row): ?><?php $status=(string)($row['status']??'inactive'); ?><a href="<?= base_url('controlplane/clinics/'.(int)$row['id']) ?>"><div><strong><?= esc($row['name']??'') ?></strong><p dir="ltr">#<?= (int)$row['id'] ?><?php if(!empty($row['created_at'])): ?> / <?= esc(formatDate($row['created_at'])) ?><?php endif; ?></p></div><span class="v4-badge <?= $status==='active'?'v4-badge--success':'' ?>"><?= esc($status==='active'?lang('V4.active'):($status==='inactive'?lang('V4.inactive'):$status)) ?></span></a><?php endforeach; ?><?php if(empty($recent_clinics)): ?><p class="v4-records-empty"><?= esc(lang('V4.noClinics')) ?></p><?php endif; ?></div><a class="v4-platform-footlink" href="<?= base_url('controlplane/clinics') ?>"><?= esc(lang('UI.cpClinics')) ?></a></section>
<aside class="v4-platform-queues"><h2><?= esc(lang('V4.operatorQueue')) ?></h2><a href="<?= base_url('controlplane/plan-requests') ?>"><span><?= esc(lang('UI.cpRequests')) ?></span><strong><?= (int)($stats['pending_requests']??0) ?></strong></a><a href="<?= base_url('controlplane/operations') ?>"><span><?= esc(lang('V4.renewalWatch')) ?></span><strong><?= (int)($stats['expiring_subscriptions']??0) ?></strong></a><a href="<?= base_url('controlplane/activity-log') ?>"><span><?= esc(lang('V4.auditWeek')) ?></span><strong><?= (int)($stats['events_7d']??0) ?></strong></a><p><?= esc(lang('V4.lastAuditEvent')) ?>: <?= esc($lastEventAt?formatDateTime($lastEventAt):lang('V4.noEvents')) ?></p></aside>
<section class="v4-surface"><h2><?= esc(lang('V4.planRequests')) ?></h2><div class="v4-platform-list"><?php foreach(($recent_plan_requests??[]) as $request): ?><a href="<?= base_url('controlplane/plan-requests') ?>"><div><strong><?= esc($request['clinic_name']??'') ?></strong><p><?= esc($request['summary']??'') ?></p></div><span class="v4-badge v4-badge--attention"><?= esc(lang('V4.pending')) ?></span></a><?php endforeach; ?><?php if(empty($recent_plan_requests)): ?><p class="v4-records-empty"><?= esc(lang('V4.noRequests')) ?></p><?php endif; ?></div></section>
<section class="v4-surface"><h2><?= esc(lang('V4.storageFootprint')) ?></h2><p class="v4-platform-storage-total" dir="ltr"><?= number_format($storageGb,2) ?> GB</p><div class="v4-platform-list"><?php foreach(array_slice($storage_top??[],0,3) as $row): ?><div class="v4-platform-storage-row"><span><?= esc($row['clinic_name']??'') ?></span><b dir="ltr"><?= number_format(((int)($row['total_size']??0))/1073741824,2) ?> GB</b></div><?php endforeach; ?></div></section>
</div>
<dl class="v4-platform-metrics"><div><dt><?= esc(lang('UI.cpClinics')) ?></dt><dd><?= (int)($stats['total_clinics']??0) ?></dd><p><?= (int)($stats['active_clinics']??0) ?> <?= esc(lang('V4.active')) ?></p></div><div><dt><?= esc(lang('V4.activeSubscriptions')) ?></dt><dd><?= (int)($stats['active_subscriptions']??0) ?></dd><p><?= (int)($stats['active_plans']??0) ?> <?= esc(lang('V4.activePlans')) ?></p></div><div><dt><?= esc(lang('V4.estimatedMrr')) ?></dt><dd dir="ltr">$<?= number_format((float)($stats['mrr']??0),2) ?></dd><p><?= esc(lang('V4.estimatedMrrHelp')) ?></p></div><div><dt><?= esc(lang('V4.activeUsers')) ?></dt><dd><?= (int)($stats['active_users']??0) ?></dd><p><?= esc(lang('V4.enabledAccounts')) ?></p></div></dl>
<section class="v4-platform-trends"><article><h2><?= esc(lang('V4.subscriptionTrend')) ?></h2><div class="v4-platform-chart"><canvas id="mrrGrowthChart"></canvas></div></article><article><h2><?= esc(lang('V4.activityTrend')) ?></h2><div class="v4-platform-chart"><canvas id="activityVolumeChart"></canvas></div></article><details><summary><?= esc(lang('V4.moreTrends')) ?></summary><div><article><h2><?= esc(lang('V4.planDistribution')) ?></h2><div class="v4-platform-chart"><canvas id="planDistributionChart"></canvas></div></article><article><h2><?= esc(lang('V4.clinicGrowth')) ?></h2><div class="v4-platform-chart"><canvas id="clinicGrowthChart"></canvas></div></article></div></details></section>
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
                ticks: { color: '#52645A', font: { weight: 600 } },
            },
            y: {
                grid: { color: 'rgba(148, 163, 184, 0.28)' },
                ticks: { color: '#52645A', font: { weight: 600 } },
            },
        },
    };

    const mrrCtx = document.getElementById('mrrGrowthChart');
    if (mrrCtx && Array.isArray(dashboardChartData.mrr_series)) {
        const mrrLabels = dashboardChartData.mrr_series.map(point => point.label);
        const mrrTotals = dashboardChartData.mrr_series.map(point => point.value);
        const mrrGradient = mrrCtx.getContext('2d').createLinearGradient(0, 0, 0, 240);
        mrrGradient.addColorStop(0, 'rgba(15,107,99,.20)');
        mrrGradient.addColorStop(1, 'rgba(15,107,99,.02)');

        new Chart(mrrCtx, {
            type: 'line',
            data: {
                labels: mrrLabels,
                datasets: [{
                    label: 'MRR ($)',
                    data: mrrTotals,
                    borderColor: '#0F6B63',
                    backgroundColor: mrrGradient,
                    pointBackgroundColor: '#0A514B',
                    pointBorderColor: '#ffffff',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.35,
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
                            color: '#52645A',
                            font: { weight: 600 },
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
                    backgroundColor: '#2563eb',
                    borderRadius: 8,
                    maxBarThickness: 26,
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
                    backgroundColor: ['#0F6B63', '#14b8a6', '#2563eb', '#f59e0b', '#dc2626', '#7c3aed'],
                    borderColor: 'rgba(255,255,255,0.88)',
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#475569', boxWidth: 10, boxHeight: 10, font: { weight: 600 } },
                    },
                },
            },
        });
    }

    const growthCtx = document.getElementById('clinicGrowthChart');
    if (growthCtx && Array.isArray(dashboardChartData.clinic_growth)) {
        const growthGradient = growthCtx.getContext('2d').createLinearGradient(0, 0, 0, 220);
        growthGradient.addColorStop(0, 'rgba(37, 99, 235, 0.32)');
        growthGradient.addColorStop(1, 'rgba(124, 58, 237, 0.04)');

        new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: dashboardChartData.clinic_growth.map(point => point.label),
                datasets: [{
                    label: 'New Clinics',
                    data: dashboardChartData.clinic_growth.map(point => point.value),
                    borderColor: '#2563eb',
                    backgroundColor: growthGradient,
                    pointBackgroundColor: '#1d4ed8',
                    pointBorderColor: '#ffffff',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.35,
                    fill: true,
                }],
            },
            options: baseChartOptions,
        });
    }
</script>
<?= $this->endSection() ?>
