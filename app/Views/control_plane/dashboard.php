<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?php
    $storageBytes = (int) ($stats['storage_total_bytes'] ?? 0);
    $storageGb = $storageBytes > 0 ? ($storageBytes / 1024 / 1024 / 1024) : 0;
    $lastEventAt = $stats['last_event_at'] ?? null;
?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-purple-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-amber-400/10 to-orange-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-tachometer-alt text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Control Plane</h1>
                            <p class="text-gray-600">Global system overview and health status</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold border border-emerald-200">
                            <i class="fas fa-globe mr-1"></i> Global Mode
                        </span>
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold border border-slate-200">
                            v2.0.0
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
                <!-- Total Clinics -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-purple-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-hospital-user text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-blue-100 to-indigo-100 px-3 py-1.5 rounded-full border border-blue-200">
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                <span class="text-blue-700 font-bold text-xs">Active: <?= number_format($stats['active_clinics'] ?? 0) ?></span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider flex items-center gap-1">
                                Total Clinics
                                <span class="text-gray-400" title="All clinics in the system (including inactive).">
                                    <i class="fas fa-info-circle"></i>
                                </span>
                            </p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-blue-900"><?= number_format($stats['total_clinics'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-gradient-to-r from-blue-200 to-indigo-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Global Tenants</p>
                        </div>
                    </div>
                </div>

                <!-- Active Subscriptions -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-cyan-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-receipt text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-emerald-100 to-teal-100 px-3 py-1.5 rounded-full border border-emerald-200">
                                <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                <span class="text-emerald-700 font-bold text-xs">Recurring</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider flex items-center gap-1">
                                Active Subscriptions
                                <span class="text-gray-400" title="Active subscriptions where current date is within start/end window.">
                                    <i class="fas fa-info-circle"></i>
                                </span>
                            </p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-emerald-900"><?= number_format($stats['active_subscriptions'] ?? 0) ?></p>
                                <div class="w-16 h-1 bg-gradient-to-r from-emerald-200 to-teal-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Revenue Accounts</p>
                        </div>
                    </div>
                </div>

                <!-- Active Plans -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/20 to-orange-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-amber-500/10 group-hover:shadow-amber-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-layer-group text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-amber-100 to-orange-100 px-3 py-1.5 rounded-full border border-amber-200">
                                <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
                                <span class="text-amber-700 font-bold text-xs">Published</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider flex items-center gap-1">
                                Catalog Plans
                                <span class="text-gray-400" title="Plans marked active in the catalog.">
                                    <i class="fas fa-info-circle"></i>
                                </span>
                            </p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-amber-900"><?= number_format($stats['active_plans'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-gradient-to-r from-amber-200 to-orange-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-amber-500 to-orange-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Service Levels</p>
                        </div>
                    </div>
                </div>

                <!-- MRR -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500/20 to-pink-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/10 group-hover:shadow-purple-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-chart-line text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-purple-100 to-pink-100 px-3 py-1.5 rounded-full border border-purple-200">
                                <div class="w-2 h-2 bg-purple-500 rounded-full animate-pulse"></div>
                                <span class="text-purple-700 font-bold text-xs">Monthly</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider flex items-center gap-1">
                                Estimated MRR
                                <span class="text-gray-400" title="Sum of active plan prices (yearly plans converted to monthly).">
                                    <i class="fas fa-info-circle"></i>
                                </span>
                            </p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-purple-900">$<?= number_format((float) ($stats['mrr'] ?? 0), 2) ?></p>
                                <div class="w-16 h-1 bg-gradient-to-r from-purple-200 to-pink-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Recurring Revenue</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch mt-6">
                <!-- Active Users -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-slate-500/10 to-indigo-500/10 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-slate-500/10 group-hover:shadow-slate-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-slate-500 to-indigo-500 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-slate-500 to-indigo-500 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-user-shield text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-slate-100 to-indigo-100 px-3 py-1.5 rounded-full border border-slate-200">
                                <div class="w-2 h-2 bg-slate-500 rounded-full animate-pulse"></div>
                                <span class="text-slate-700 font-bold text-xs">Operators</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider flex items-center gap-1">
                                Active Users
                                <span class="text-gray-400" title="All active users across clinics and control plane.">
                                    <i class="fas fa-info-circle"></i>
                                </span>
                            </p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-slate-900"><?= number_format($stats['active_users'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-gradient-to-r from-slate-200 to-indigo-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-slate-500 to-indigo-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Enabled Accounts</p>
                        </div>
                    </div>
                </div>

                <!-- Expiring Subscriptions -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-rose-500/10 to-orange-500/10 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-rose-500/10 group-hover:shadow-rose-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-rose-500 to-orange-500 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-rose-500 to-orange-500 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-hourglass-half text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-rose-100 to-orange-100 px-3 py-1.5 rounded-full border border-rose-200">
                                <div class="w-2 h-2 bg-rose-500 rounded-full animate-pulse"></div>
                                <span class="text-rose-700 font-bold text-xs">30 Days</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider flex items-center gap-1">
                                Expiring Subscriptions
                                <span class="text-gray-400" title="Active subscriptions ending in the next 30 days.">
                                    <i class="fas fa-info-circle"></i>
                                </span>
                            </p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-rose-700"><?= number_format($stats['expiring_subscriptions'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-gradient-to-r from-rose-200 to-orange-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-rose-500 to-orange-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Renewal Watch</p>
                        </div>
                    </div>
                </div>

                <!-- Storage Usage -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-teal-500/10 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-hdd text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-emerald-100 to-teal-100 px-3 py-1.5 rounded-full border border-emerald-200">
                                <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                <span class="text-emerald-700 font-bold text-xs">All Files</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider flex items-center gap-1">
                                Storage Used
                                <span class="text-gray-400" title="Total attachments and generated documents across all clinics.">
                                    <i class="fas fa-info-circle"></i>
                                </span>
                            </p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-emerald-700"><?= number_format($storageGb, 2) ?> GB</p>
                                <div class="w-12 h-1 bg-gradient-to-r from-emerald-200 to-teal-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Storage Footprint</p>
                        </div>
                    </div>
                </div>

                <!-- Pending Requests -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-orange-500/10 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-amber-500/10 group-hover:shadow-amber-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-inbox text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-amber-100 to-orange-100 px-3 py-1.5 rounded-full border border-amber-200">
                                <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
                                <span class="text-amber-700 font-bold text-xs">Queue</span>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider flex items-center gap-1">
                                Pending Requests
                                <span class="text-gray-400" title="Plan change requests awaiting review.">
                                    <i class="fas fa-info-circle"></i>
                                </span>
                            </p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-amber-700"><?= number_format($stats['pending_requests'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-gradient-to-r from-amber-200 to-orange-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-amber-500 to-orange-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">Awaiting Action</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Charts Section -->
        <div class="mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
                <!-- MRR Growth Chart -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-purple-600/10 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-indigo-500/10 group-hover:shadow-indigo-500/20 transition-all duration-200 hover:scale-105 h-full flex flex-col">
                        <div class="flex items-center justify-between p-6 pb-4">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full animate-pulse"></div>
                                    <h3 class="text-xl font-black text-gray-900 flex items-center gap-2">
                                        MRR Growth
                                        <span class="text-gray-400 text-sm" title="MRR trend derived from active subscriptions (yearly normalized).">
                                            <i class="fas fa-info-circle"></i>
                                        </span>
                                    </h3>
                                </div>
                                <p class="text-gray-600 font-medium text-sm">Monthly Recurring Revenue Trend</p>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-indigo-50 to-purple-50 px-3 py-2 rounded-xl border border-indigo-200">
                                <div class="w-3 h-3 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full"></div>
                                <span class="text-indigo-700 font-bold text-xs">Revenue</span>
                            </div>
                        </div>
                        <div class="relative h-64 p-6 pt-0 flex-1">
                            <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/50 to-purple-50/50 rounded-2xl"></div>
                            <canvas id="mrrGrowthChart" class="relative z-10"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Activity Volume Chart -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-cyan-600/10 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-200 hover:scale-105 h-full flex flex-col">
                        <div class="flex items-center justify-between p-6 pb-4">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-gradient-to-r from-blue-500 to-cyan-600 rounded-full animate-pulse"></div>
                                    <h3 class="text-xl font-black text-gray-900 flex items-center gap-2">
                                        Activity Volume
                                        <span class="text-gray-400 text-sm" title="Control plane audit events per day (last 14 days).">
                                            <i class="fas fa-info-circle"></i>
                                        </span>
                                    </h3>
                                </div>
                                <p class="text-gray-600 font-medium text-sm">Control Plane Events</p>
                            </div>
                            <div class="text-right bg-gradient-to-r from-blue-50 to-cyan-50 px-6 py-2 rounded-xl border border-blue-200">
                                <p class="text-xs text-blue-600 font-semibold uppercase tracking-wider">System Load</p>
                            </div>
                        </div>
                        <div class="relative h-64 p-6 pt-0 flex-1">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-50/50 to-cyan-50/50 rounded-2xl"></div>
                            <canvas id="activityVolumeChart" class="relative z-10"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
                <!-- Plan Distribution -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-teal-600/10 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-200 hover:scale-105 h-full flex flex-col">
                        <div class="flex items-center justify-between p-6 pb-4">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full animate-pulse"></div>
                                    <h3 class="text-xl font-black text-gray-900 flex items-center gap-2">
                                        Plan Distribution
                                        <span class="text-gray-400 text-sm" title="Active subscriptions by plan.">
                                            <i class="fas fa-info-circle"></i>
                                        </span>
                                    </h3>
                                </div>
                                <p class="text-gray-600 font-medium text-sm">Active plan mix</p>
                            </div>
                        </div>
                        <div class="relative h-64 p-6 pt-0 flex-1">
                            <div class="absolute inset-0 bg-gradient-to-br from-emerald-50/50 to-teal-50/50 rounded-2xl"></div>
                            <canvas id="planDistributionChart" class="relative z-10"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Clinic Growth -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-indigo-600/10 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-purple-500/10 group-hover:shadow-purple-500/20 transition-all duration-200 hover:scale-105 h-full flex flex-col">
                        <div class="flex items-center justify-between p-6 pb-4">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-full animate-pulse"></div>
                                    <h3 class="text-xl font-black text-gray-900 flex items-center gap-2">
                                        Clinic Growth
                                        <span class="text-gray-400 text-sm" title="New clinics created in the last 6 months.">
                                            <i class="fas fa-info-circle"></i>
                                        </span>
                                    </h3>
                                </div>
                                <p class="text-gray-600 font-medium text-sm">New clinic onboarding</p>
                            </div>
                        </div>
                        <div class="relative h-64 p-6 pt-0 flex-1">
                            <div class="absolute inset-0 bg-gradient-to-br from-purple-50/50 to-indigo-50/50 rounded-2xl"></div>
                            <canvas id="clinicGrowthChart" class="relative z-10"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Feeds & Quick Actions -->
        <div class="mb-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-stretch">
                <!-- Recent Clinics -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-teal-600/10 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-200 hover:scale-105 h-full flex flex-col">
                        <div class="flex justify-between items-center p-4 pb-3">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full animate-pulse"></div>
                                    <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                                        Recent Clinics
                                        <span class="text-gray-400 text-xs" title="Newest clinics by creation date.">
                                            <i class="fas fa-info-circle"></i>
                                        </span>
                                    </h3>
                                </div>
                                <p class="text-gray-600 font-medium text-xs">Newest onboarded tenants</p>
                            </div>
                            <a href="<?= base_url('controlplane/clinics') ?>" class="group/link relative inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-50 to-teal-50 hover:from-emerald-100 hover:to-teal-100 text-emerald-700 hover:text-emerald-800 text-sm font-semibold rounded-2xl border border-emerald-200 hover:border-emerald-300 transition-all duration-200 hover:scale-105">
                                <span class="relative z-10">View All</span>
                                <i class="fas fa-arrow-right ml-2 text-xs relative z-10 group-hover/link:translate-x-1 transition-transform duration-300"></i>
                            </a>
                        </div>
                        <div class="space-y-1 p-4 pt-0 flex-1">
                            <?php if (!empty($recent_clinics)): ?>
                                <?php foreach ($recent_clinics as $clinic): ?>
                                    <?php $status = $clinic['status'] ?? 'inactive'; ?>
                                    <div class="group/item relative flex items-center space-x-3 p-3 rounded-xl hover:bg-gradient-to-r hover:from-emerald-50/50 hover:to-teal-50/50 transition-all duration-200 border border-transparent hover:border-emerald-200/50 hover:shadow-lg hover:shadow-emerald-500/10 cursor-pointer">
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl blur-lg opacity-75 group-hover/item:opacity-100 transition-opacity duration-300"></div>
                                            <div class="relative w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg group-hover/item:scale-110 group-hover/item:rotate-3 transition-all duration-200">
                                                <span class="text-white font-bold text-xs"><?= strtoupper(substr($clinic['name'], 0, 2)) ?></span>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0 space-y-1">
                                            <p class="text-sm font-bold text-gray-900 truncate group-hover/item:text-emerald-900 transition-colors duration-300">
                                                <?= esc($clinic['name']) ?>
                                            </p>
                                            <p class="text-xs text-gray-500 font-medium">ID: <?= esc($clinic['id']) ?></p>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider <?= $status === 'active' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' ?>">
                                                <?= esc($status) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-12 text-slate-400 text-sm">No recent clinics found.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Pending Requests -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-orange-600/10 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-amber-500/10 group-hover:shadow-amber-500/20 transition-all duration-200 hover:scale-105 h-full flex flex-col">
                        <div class="flex justify-between items-center p-4 pb-3">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-gradient-to-r from-amber-500 to-orange-600 rounded-full animate-pulse"></div>
                                    <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                                        Plan Requests
                                        <span class="text-gray-400 text-xs" title="Most recent plan change requests (pending queue).">
                                            <i class="fas fa-info-circle"></i>
                                        </span>
                                    </h3>
                                </div>
                                <p class="text-gray-600 font-medium text-xs">Pending approval queue</p>
                            </div>
                            <a href="<?= base_url('controlplane/plan-requests') ?>" class="group/link relative inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-50 to-orange-50 hover:from-amber-100 hover:to-orange-100 text-amber-700 hover:text-amber-800 text-sm font-semibold rounded-2xl border border-amber-200 hover:border-amber-300 transition-all duration-200 hover:scale-105">
                                <span class="relative z-10">Review</span>
                                <i class="fas fa-arrow-right ml-2 text-xs relative z-10 group-hover/link:translate-x-1 transition-transform duration-300"></i>
                            </a>
                        </div>
                        <div class="space-y-1 p-4 pt-0 flex-1">
                            <?php if (!empty($recent_plan_requests)): ?>
                                <?php foreach ($recent_plan_requests as $request): ?>
                                    <div class="group/item relative flex items-center space-x-3 p-3 rounded-xl hover:bg-gradient-to-r hover:from-amber-50/50 hover:to-orange-50/50 transition-all duration-200 border border-transparent hover:border-amber-200/50 hover:shadow-lg hover:shadow-amber-500/10 cursor-pointer">
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl blur-lg opacity-75 group-hover/item:opacity-100 transition-opacity duration-300"></div>
                                            <div class="relative w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg group-hover/item:scale-110 group-hover/item:rotate-3 transition-all duration-200">
                                                <i class="fas fa-inbox text-white text-sm"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0 space-y-1">
                                            <p class="text-sm font-bold text-gray-900 truncate group-hover/item:text-amber-900 transition-colors duration-300">
                                                <?= esc($request['clinic_name'] ?? 'Clinic') ?>
                                            </p>
                                            <p class="text-xs text-gray-500 font-medium truncate"><?= esc($request['summary'] ?? 'Plan Change') ?></p>
                                        </div>
                                        <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded-full border border-amber-100">
                                            PENDING
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-12 text-slate-400 text-sm">No pending requests.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-violet-600/10 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-indigo-500/10 group-hover:shadow-indigo-500/20 transition-all duration-200">
                        <div class="p-4 pb-3">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-gradient-to-r from-indigo-500 to-violet-600 rounded-full animate-pulse"></div>
                                    <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                                        Quick Actions
                                        <span class="text-gray-400 text-xs" title="Shortcuts for common control-plane tasks.">
                                            <i class="fas fa-info-circle"></i>
                                        </span>
                                    </h3>
                                </div>
                                <p class="text-gray-600 font-medium text-xs">Operator shortcuts</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 p-4 pt-0">
                            <!-- Onboard -->
                            <a href="<?= base_url('controlplane/onboarding/clinic/create') ?>" class="group/action relative flex flex-col items-center p-4 rounded-xl border-2 border-dashed border-indigo-200 hover:border-indigo-400 hover:bg-gradient-to-br hover:from-indigo-50/80 hover:to-violet-50/80 transition-all duration-200 hover:shadow-xl hover:scale-105 hover:-translate-y-1">
                                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 mb-2 group-hover/action:bg-indigo-600 group-hover/action:text-white transition-colors">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700 group-hover/action:text-indigo-900">Onboard Clinic</span>
                            </a>

                            <!-- Plans -->
                            <a href="<?= base_url('controlplane/plans/create') ?>" class="group/action relative flex flex-col items-center p-4 rounded-xl border-2 border-dashed border-purple-200 hover:border-purple-400 hover:bg-gradient-to-br hover:from-purple-50/80 hover:to-fuchsia-50/80 transition-all duration-200 hover:shadow-xl hover:scale-105 hover:-translate-y-1">
                                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600 mb-2 group-hover/action:bg-purple-600 group-hover/action:text-white transition-colors">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700 group-hover/action:text-purple-900">New Plan</span>
                            </a>

                            <!-- Operations -->
                            <a href="<?= base_url('controlplane/operations') ?>" class="group/action relative flex flex-col items-center p-4 rounded-xl border-2 border-dashed border-slate-200 hover:border-slate-400 hover:bg-gradient-to-br hover:from-slate-50/80 hover:to-gray-50/80 transition-all duration-200 hover:shadow-xl hover:scale-105 hover:-translate-y-1">
                                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-600 mb-2 group-hover/action:bg-slate-600 group-hover/action:text-white transition-colors">
                                    <i class="fas fa-cogs"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700 group-hover/action:text-slate-900">Operations</span>
                            </a>

                            <!-- Clinics -->
                            <a href="<?= base_url('controlplane/clinics') ?>" class="group/action relative flex flex-col items-center p-4 rounded-xl border-2 border-dashed border-blue-200 hover:border-blue-400 hover:bg-gradient-to-br hover:from-blue-50/80 hover:to-cyan-50/80 transition-all duration-200 hover:shadow-xl hover:scale-105 hover:-translate-y-1">
                                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600 mb-2 group-hover/action:bg-blue-600 group-hover/action:text-white transition-colors">
                                    <i class="fas fa-list"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-700 group-hover/action:text-blue-900">List Clinics</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-stretch">
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-teal-600/10 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-200 hover:scale-105 h-full flex flex-col">
                        <div class="flex justify-between items-center p-4 pb-3">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full animate-pulse"></div>
                                    <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                                        Storage Hotspots
                                        <span class="text-gray-400 text-xs" title="Top clinics by attachment storage usage.">
                                            <i class="fas fa-info-circle"></i>
                                        </span>
                                    </h3>
                                </div>
                                <p class="text-gray-600 font-medium text-xs">Largest storage consumers</p>
                            </div>
                        </div>
                        <div class="space-y-1 p-4 pt-0 flex-1">
                            <?php if (!empty($storage_top ?? [])): ?>
                                <?php foreach ($storage_top as $row): ?>
                                    <?php
                                        $clinicName = $row['clinic_name'] ?? 'Clinic';
                                        $sizeBytes = (int) ($row['total_size'] ?? 0);
                                        $sizeGb = $sizeBytes > 0 ? ($sizeBytes / 1024 / 1024 / 1024) : 0;
                                    ?>
                                    <div class="flex items-center justify-between p-3 rounded-xl border border-emerald-100 bg-emerald-50/60">
                                        <span class="text-sm font-semibold text-emerald-900"><?= esc($clinicName) ?></span>
                                        <span class="text-xs font-semibold text-emerald-700"><?= number_format($sizeGb, 2) ?> GB</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-10 text-slate-400 text-sm">No storage usage yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-indigo-600/10 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-200 hover:scale-105 h-full flex flex-col">
                        <div class="flex justify-between items-center p-4 pb-3">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full animate-pulse"></div>
                                    <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                                        System Health
                                        <span class="text-gray-400 text-xs" title="Recent control plane activity and last event timestamp.">
                                            <i class="fas fa-info-circle"></i>
                                        </span>
                                    </h3>
                                </div>
                                <p class="text-gray-600 font-medium text-xs">Audit pulse</p>
                            </div>
                        </div>
                        <div class="space-y-2 p-4 pt-0 flex-1 text-sm text-slate-600">
                            <div class="flex items-center justify-between">
                                <span>Events (7d)</span>
                                <span class="font-semibold text-slate-900"><?= number_format($stats['events_7d'] ?? 0) ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Events (30d)</span>
                                <span class="font-semibold text-slate-900"><?= number_format($stats['events_30d'] ?? 0) ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Last Event</span>
                                <span class="font-semibold text-slate-900"><?= $lastEventAt ? esc(date('M j, Y g:i A', strtotime($lastEventAt))) : '—' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const dashboardChartData = <?= json_encode($chart_data ?? []) ?>;

        const mrrCtx = document.getElementById('mrrGrowthChart');
        if (mrrCtx && Array.isArray(dashboardChartData.mrr_series)) {
            const mrrLabels = dashboardChartData.mrr_series.map(point => point.label);
            const mrrTotals = dashboardChartData.mrr_series.map(point => point.value);
            const mrrGradient = mrrCtx.getContext('2d').createLinearGradient(0, 0, 0, 220);
            mrrGradient.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
            mrrGradient.addColorStop(1, 'rgba(16, 185, 129, 0.05)');

            new Chart(mrrCtx, {
                type: 'line',
                data: {
                    labels: mrrLabels,
                    datasets: [{
                        label: 'MRR ($)',
                        data: mrrTotals,
                        borderColor: '#10b981',
                        backgroundColor: mrrGradient,
                        pointBackgroundColor: '#059669',
                        pointBorderColor: '#ffffff',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.35,
                        fill: true,
                    }],
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#475569' },
                        },
                        y: {
                            grid: {
                                borderDash: [4, 4],
                                color: 'rgba(100, 116, 139, 0.15)',
                            },
                            ticks: {
                                callback: value => typeof value === 'number' ? '$' + value.toLocaleString() : value,
                                color: '#475569',
                            },
                        },
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: context => '$' + Number(context.parsed.y ?? 0).toLocaleString(undefined, { minimumFractionDigits: 2 }),
                            },
                        },
                    },
                },
            });
        }

        const statusCtx = document.getElementById('clinicStatusChart');
        if (statusCtx) {
            const statusLabels = ['Active', 'Suspended', 'Trial'];
            const statusValues = statusLabels.map(label => dashboardChartData.status_breakdown?.[label.toLowerCase()] || 0);

            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusValues,
                        backgroundColor: ['#10b981', '#f97316', '#6366f1'],
                        borderColor: 'rgba(255,255,255,0.8)',
                        borderWidth: 2,
                    }],
                },
                options: {
                    responsive: true,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: '#475569', boxWidth: 10, boxHeight: 10 },
                        },
                    },
                },
            });
        }

        const activityCtx = document.getElementById('activityVolumeChart');
        if (activityCtx && Array.isArray(dashboardChartData.activity_series)) {
            const activityLabels = dashboardChartData.activity_series.map(point => point.day);
            const activityTotals = dashboardChartData.activity_series.map(point => point.total);

            new Chart(activityCtx, {
                type: 'bar',
                data: {
                    labels: activityLabels,
                    datasets: [{
                        label: 'Events',
                        data: activityTotals,
                        backgroundColor: '#2563eb',
                        borderRadius: 6,
                        maxBarThickness: 24,
                    }],
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#475569' },
                        },
                        y: {
                            grid: {
                                borderDash: [4, 4],
                                color: 'rgba(148, 163, 184, 0.35)',
                            },
                            ticks: { color: '#475569' },
                        },
                    },
                    plugins: {
                        legend: { display: false },
                    },
                },
            });
        }

        const distributionCtx = document.getElementById('planDistributionChart');
        if (distributionCtx && Array.isArray(dashboardChartData.plan_distribution)) {
            const distLabels = dashboardChartData.plan_distribution.map(point => point.label);
            const distValues = dashboardChartData.plan_distribution.map(point => point.value);
            new Chart(distributionCtx, {
                type: 'doughnut',
                data: {
                    labels: distLabels,
                    datasets: [{
                        data: distValues,
                        backgroundColor: ['#10b981', '#14b8a6', '#6366f1', '#f59e0b', '#ef4444', '#0ea5e9'],
                        borderColor: 'rgba(255,255,255,0.8)',
                        borderWidth: 2,
                    }],
                },
                options: {
                    responsive: true,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: '#475569', boxWidth: 10, boxHeight: 10 },
                        },
                    },
                },
            });
        }

        const growthCtx = document.getElementById('clinicGrowthChart');
        if (growthCtx && Array.isArray(dashboardChartData.clinic_growth)) {
            const growthLabels = dashboardChartData.clinic_growth.map(point => point.label);
            const growthValues = dashboardChartData.clinic_growth.map(point => point.value);
            const growthGradient = growthCtx.getContext('2d').createLinearGradient(0, 0, 0, 220);
            growthGradient.addColorStop(0, 'rgba(99, 102, 241, 0.35)');
            growthGradient.addColorStop(1, 'rgba(99, 102, 241, 0.05)');

            new Chart(growthCtx, {
                type: 'line',
                data: {
                    labels: growthLabels,
                    datasets: [{
                        label: 'New Clinics',
                        data: growthValues,
                        borderColor: '#6366f1',
                        backgroundColor: growthGradient,
                        pointBackgroundColor: '#4f46e5',
                        pointBorderColor: '#ffffff',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.35,
                        fill: true,
                    }],
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#475569' },
                        },
                        y: {
                            grid: {
                                borderDash: [4, 4],
                                color: 'rgba(100, 116, 139, 0.15)',
                            },
                            ticks: { color: '#475569' },
                        },
                    },
                    plugins: {
                        legend: { display: false },
                    },
                },
            });
        }
    </script>
<?= $this->endSection() ?>
