<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Enhanced Financial Reports with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-green-50 to-emerald-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-green-400/20 to-emerald-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-amber-400/10 to-orange-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Enhanced Page Header with Glassmorphism -->
        <div class="mb-8">
            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-green-500/10 to-emerald-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-8 shadow-2xl shadow-green-500/10 group-hover:shadow-green-500/20 transition-all duration-500">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
                        <div class="space-y-4">
                            <div class="flex items-center space-x-4">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative p-4 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                        <i class="fas fa-chart-line text-3xl"></i>
                                    </div>
                                </div>
                                <div>
                                    <h1 class="text-5xl font-black text-gray-900 mb-2">Financial Reports</h1>
                                    <p class="text-gray-600 text-xl font-medium">Comprehensive financial analysis and insights</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                            <a href="<?= base_url('finance') ?>" class="group/btn relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 text-sm font-bold rounded-2xl hover:from-gray-200 hover:to-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-500/50 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-gray-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-600/10 rounded-2xl blur-xl group-hover/btn:blur-2xl transition-all duration-500 opacity-0 group-hover/btn:opacity-100"></div>
                                <i class="fas fa-arrow-left mr-3 text-lg relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                                <span class="relative z-10">Back to Finance</span>
                            </a>
                            <button onclick="exportReport()" class="group/btn relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white text-sm font-bold rounded-2xl hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-4 focus:ring-green-500/50 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-green-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-green-500/20 to-emerald-600/20 rounded-2xl blur-xl group-hover/btn:blur-2xl transition-all duration-500 opacity-0 group-hover/btn:opacity-100"></div>
                                <i class="fas fa-download mr-3 text-lg relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                                <span class="relative z-10">Export Report</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Range Filter -->
        <div class="mb-8">
            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-indigo-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-500 p-6">
                    <form method="GET" action="<?= base_url('finance/reports') ?>" class="flex flex-col lg:flex-row lg:items-end space-y-4 lg:space-y-0 lg:space-x-6">
                        <div class="flex-1">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-calendar-alt w-4 h-4 mr-2 text-blue-600"></i>
                                Start Date
                            </label>
                            <input type="date" name="start_date" value="<?= $start_date ?>" 
                                   class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-calendar-alt w-4 h-4 mr-2 text-green-600"></i>
                                End Date
                            </label>
                            <input type="date" name="end_date" value="<?= $end_date ?>" 
                                   class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 hover:shadow-xl">
                        </div>
                        <div class="flex space-x-3">
                            <button type="submit" class="group/btn relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-xl hover:shadow-blue-500/30 focus:outline-none focus:ring-4 focus:ring-blue-500/20 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-search mr-2 relative z-10"></i>
                                <span class="relative z-10">Filter</span>
                            </button>
                            <a href="<?= base_url('finance/reports') ?>" class="group/btn relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-200 to-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:from-gray-300 hover:to-gray-400 focus:outline-none focus:ring-4 focus:ring-gray-500/20 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-300 to-gray-400 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-refresh mr-2 relative z-10"></i>
                                <span class="relative z-10">Reset</span>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Financial Statistics Cards -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Revenue Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-500/20 to-emerald-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-green-500/10 group-hover:shadow-green-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-dollar-sign text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-green-100 to-emerald-100 px-3 py-1.5 rounded-full border border-green-200">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-arrow-up text-green-600 text-xs"></i>
                                <span class="text-green-700 font-bold text-xs">+12%</span>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Revenue</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-green-900"><?= formatCurrencyAbbreviated($stats['total_revenue'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-gradient-to-r from-green-200 to-emerald-200 rounded-full overflow-hidden">
                                    <div class="w-4/5 h-full bg-gradient-to-r from-green-500 to-emerald-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">vs last period</p>
                        </div>
                    </div>
                </div>

                <!-- Total Transactions Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-indigo-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-receipt text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-blue-100 to-indigo-100 px-3 py-1.5 rounded-full border border-blue-200">
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-chart-line text-blue-600 text-xs"></i>
                                <span class="text-blue-700 font-bold text-xs">Active</span>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Transactions</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-blue-900"><?= number_format($stats['total_transactions'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-gradient-to-r from-blue-200 to-indigo-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">this period</p>
                        </div>
                    </div>
                </div>

                <!-- Pending Payments Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/20 to-orange-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-amber-500/10 group-hover:shadow-amber-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-clock text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-amber-100 to-orange-100 px-3 py-1.5 rounded-full border border-amber-200">
                                <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-exclamation-triangle text-amber-600 text-xs"></i>
                                <span class="text-amber-700 font-bold text-xs">Pending</span>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Pending Payments</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-amber-900"><?= formatCurrencyAbbreviated($stats['pending_amount'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-gradient-to-r from-amber-200 to-orange-200 rounded-full overflow-hidden">
                                    <div class="w-2/3 h-full bg-gradient-to-r from-amber-500 to-orange-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">awaiting payment</p>
                        </div>
                    </div>
                </div>

                <!-- Average Transaction Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500/20 to-pink-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/10 group-hover:shadow-purple-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-chart-bar text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-purple-100 to-pink-100 px-3 py-1.5 rounded-full border border-purple-200">
                                <div class="w-2 h-2 bg-purple-500 rounded-full animate-pulse"></div>
                                <i class="fas fa-trending-up text-purple-600 text-xs"></i>
                                <span class="text-purple-700 font-bold text-xs">Average</span>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Avg Transaction</p>
                            <div class="flex items-baseline space-x-2">
                                <p class="text-3xl font-black text-purple-900"><?= formatCurrencyAbbreviated($stats['average_transaction'] ?? 0) ?></p>
                                <div class="w-12 h-1 bg-gradient-to-r from-purple-200 to-pink-200 rounded-full overflow-hidden">
                                    <div class="w-5/6 h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">per transaction</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Chart -->
        <div class="mb-8">
            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-purple-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-indigo-500/10 group-hover:shadow-indigo-500/20 transition-all duration-500 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl blur opacity-75"></div>
                                <div class="relative w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                                    <i class="fas fa-chart-line text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">Revenue Trend</h3>
                                <p class="text-gray-600">Monthly revenue over time</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chart Placeholder -->
                    <div class="h-64 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl flex items-center justify-center border-2 border-dashed border-gray-300">
                        <div class="text-center">
                            <i class="fas fa-chart-line text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-500 font-medium">Revenue chart will be displayed here</p>
                            <p class="text-gray-400 text-sm">Chart integration coming soon</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Summary Table -->
        <div class="mb-8">
            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-teal-500/10 to-cyan-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-teal-500/10 group-hover:shadow-teal-500/20 transition-all duration-500 overflow-hidden">
                    <div class="px-8 py-6 border-b border-white/20 bg-gradient-to-r from-teal-50/80 to-cyan-50/80 backdrop-blur-sm rounded-t-2xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-3 h-3 bg-gradient-to-r from-teal-500 to-cyan-600 rounded-full animate-pulse"></div>
                                <h3 class="text-xl font-black text-gray-900">Transaction Summary</h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-8">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-full">
                                <thead class="bg-gradient-to-r from-teal-50/80 to-cyan-50/80 backdrop-blur-sm">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-teal-700 uppercase tracking-wider">Transaction Type</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-teal-700 uppercase tracking-wider">Count</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-teal-700 uppercase tracking-wider">Total Amount</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-teal-700 uppercase tracking-wider">Average</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white/50 backdrop-blur-sm divide-y divide-white/20">
                                    <?php if (!empty($stats['transaction_summary'])): ?>
                                        <?php foreach ($stats['transaction_summary'] as $summary): ?>
                                            <tr class="hover:bg-gradient-to-r hover:from-teal-50/50 hover:to-cyan-50/50 transition-all duration-300">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-gradient-to-r from-teal-100 to-cyan-100 text-teal-800 border border-teal-200">
                                                        <i class="fas fa-receipt mr-2"></i>
                                                        <?= ucfirst($summary['transaction_type']) ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                                    <?= number_format($summary['count']) ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                                    <?= formatCurrency($summary['total_amount']) ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                                    <?= formatCurrency($summary['average_amount']) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-16 text-center">
                                                <div class="flex flex-col items-center space-y-4">
                                                    <div class="relative w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-3xl flex items-center justify-center">
                                                        <i class="fas fa-chart-bar text-gray-400 text-2xl"></i>
                                                    </div>
                                                    <div class="text-center">
                                                        <h3 class="text-lg font-bold text-gray-900 mb-2">No Data Available</h3>
                                                        <p class="text-gray-600 mb-4">No transactions found for the selected period</p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportReport() {
    // Create a more modern confirmation dialog
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50';
    
    const card = document.createElement('div');
    card.className = 'bg-white rounded-2xl shadow-2xl p-8 max-w-md mx-4';
    
    const textCenter = document.createElement('div');
    textCenter.className = 'text-center';
    
    const iconContainer = document.createElement('div');
    iconContainer.className = 'w-16 h-16 bg-gradient-to-r from-green-500 to-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4';
    const icon = document.createElement('i');
    icon.className = 'fas fa-download text-white text-2xl';
    iconContainer.appendChild(icon);
    
    const title = document.createElement('h3');
    title.className = 'text-xl font-bold text-gray-800 mb-2';
    title.textContent = 'Export Report';
    
    const description = document.createElement('p');
    description.className = 'text-gray-600 mb-6';
    description.textContent = 'Choose the format for your financial report';
    
    const buttonContainer = document.createElement('div');
    buttonContainer.className = 'flex space-x-3';
    
    const pdfBtn = document.createElement('button');
    pdfBtn.className = 'px-6 py-3 bg-gradient-to-r from-red-500 to-pink-600 text-white rounded-xl hover:from-red-600 hover:to-pink-700 transition-all';
    pdfBtn.onclick = function() {
        downloadReport('pdf');
        modal.remove();
    };
    const pdfIcon = document.createElement('i');
    pdfIcon.className = 'fas fa-file-pdf mr-2';
    pdfBtn.appendChild(pdfIcon);
    pdfBtn.appendChild(document.createTextNode('PDF'));
    
    const excelBtn = document.createElement('button');
    excelBtn.className = 'px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all';
    excelBtn.onclick = function() {
        downloadReport('excel');
        modal.remove();
    };
    const excelIcon = document.createElement('i');
    excelIcon.className = 'fas fa-file-excel mr-2';
    excelBtn.appendChild(excelIcon);
    excelBtn.appendChild(document.createTextNode('Excel'));
    
    buttonContainer.appendChild(pdfBtn);
    buttonContainer.appendChild(excelBtn);
    
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'mt-4 px-6 py-2 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors';
    cancelBtn.textContent = 'Cancel';
    cancelBtn.onclick = function() {
        modal.remove();
    };
    
    textCenter.appendChild(iconContainer);
    textCenter.appendChild(title);
    textCenter.appendChild(description);
    textCenter.appendChild(buttonContainer);
    textCenter.appendChild(cancelBtn);
    
    card.appendChild(textCenter);
    modal.appendChild(card);
    document.body.appendChild(modal);
}

function downloadReport(format) {
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = document.querySelector('input[name="end_date"]').value;
    
    // Implementation for downloading report
    window.location.href = `<?= base_url('finance/export-report') ?>?format=${format}&start_date=${startDate}&end_date=${endDate}`;
}

// Add smooth scrolling and enhanced interactions
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth transitions to all interactive elements
    const interactiveElements = document.querySelectorAll('button, input, select, a');
    interactiveElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-1px)';
        });
        
        element.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>
<?= $this->endSection() ?>
