<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Enhanced Patient Management with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-purple-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-amber-400/10 to-orange-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="relative z-10">
        <!-- Enhanced Header with Glassmorphism -->
        <div class="backdrop-blur-xl bg-white/80 border-b border-white/20 shadow-2xl shadow-emerald-500/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center space-x-3">
                            <div class="relative group">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-cyan-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative w-12 h-12 bg-gradient-to-br from-emerald-500 to-cyan-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-users text-white text-xl"></i>
                                </div>
                            </div>
                            <div>
                                <h1 class="text-3xl font-black text-gray-900">Patient Management</h1>
                                <div class="flex items-center space-x-4">
                                    <p class="text-sm text-gray-600 font-medium"><?= count($patients ?? []) ?> patients registered</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <a href="<?= base_url('patient/create') ?>" class="group relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-cyan-600 text-white text-sm font-bold rounded-xl hover:from-emerald-600 hover:to-cyan-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-emerald-500/25">
                            <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-cyan-600/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <i class="fas fa-plus mr-2 relative z-10"></i>
                            <span class="relative z-10">Add Patient</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>


    <!-- Enhanced Patient Table with Server-Side Processing -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        <!-- Enhanced Patient Directory Header -->
        <div class="group relative mb-8 mt-8">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-cyan-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
            <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-500 p-6">
                <div class="flex items-center justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center space-x-3">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-cyan-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative w-12 h-12 bg-gradient-to-br from-emerald-500 to-cyan-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-list text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h2 class="text-2xl font-black text-gray-900 group-hover:text-emerald-900 transition-colors duration-300">Patient Directory</h2>
                                <div class="flex items-center space-x-4">
                                    <p class="text-sm text-gray-600 font-medium">Advanced patient management with real-time search and sorting</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center space-x-2 bg-gradient-to-r from-blue-100 to-cyan-100 px-4 py-2 rounded-full border border-blue-200">
                            <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                            <i class="fas fa-server text-blue-600 text-sm"></i>
                            <span class="text-blue-700 font-bold text-sm">Server-Side Processing</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

            <!-- Enhanced Grid View (Hidden) -->
            <div id="gridView" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($patients as $patient): ?>
                    <?php
                    $dob = new DateTime($patient['date_of_birth']);
                    $now = new DateTime();
                    $age = $now->diff($dob)->y;
                    ?>
                    
                    <div class="group relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-cyan-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                        <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                            <!-- Patient Header -->
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-cyan-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative w-14 h-14 bg-gradient-to-br from-emerald-500 to-cyan-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                        <span class="text-white font-bold text-lg"><?= strtoupper(substr($patient['first_name'], 0, 1) . substr($patient['last_name'], 0, 1)) ?></span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-black text-gray-900 truncate group-hover:text-emerald-900 transition-colors duration-300"><?= $patient['first_name'] . ' ' . $patient['last_name'] ?></h3>
                                    <p class="text-sm text-gray-500 font-medium">ID: <?= $patient['patient_id'] ?></p>
                                </div>
                                <span class="px-3 py-1.5 text-xs font-bold rounded-full <?= $patient['status'] === 'active' ? 'bg-gradient-to-r from-emerald-100 to-cyan-100 text-emerald-800 border border-emerald-200' : ($patient['status'] === 'inactive' ? 'bg-gradient-to-r from-gray-100 to-slate-100 text-gray-800 border border-gray-200' : 'bg-gradient-to-r from-amber-100 to-orange-100 text-amber-800 border border-amber-200') ?>">
                                    <?= ucfirst($patient['status']) ?>
                                </span>
                            </div>
                            
                            <!-- Patient Details -->
                            <div class="space-y-3 mb-6 flex-1">
                                <div class="flex items-center text-sm text-gray-600 group-hover:text-gray-800 transition-colors duration-300">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-100 to-blue-200 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform duration-300">
                                        <i class="fas fa-phone text-blue-600 text-xs"></i>
                                    </div>
                                    <span class="truncate font-medium"><?= $patient['phone'] ?></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600 group-hover:text-gray-800 transition-colors duration-300">
                                    <div class="w-8 h-8 bg-gradient-to-br from-purple-100 to-purple-200 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform duration-300">
                                        <i class="fas fa-envelope text-purple-600 text-xs"></i>
                                    </div>
                                    <span class="truncate font-medium"><?= $patient['email'] ?: 'No email' ?></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600 group-hover:text-gray-800 transition-colors duration-300">
                                    <div class="w-8 h-8 bg-gradient-to-br from-amber-100 to-amber-200 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform duration-300">
                                        <i class="fas fa-birthday-cake text-amber-600 text-xs"></i>
                                    </div>
                                    <span class="font-medium"><?= $age ?> years old</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600 group-hover:text-gray-800 transition-colors duration-300">
                                    <div class="w-8 h-8 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform duration-300">
                                        <i class="fas fa-stethoscope text-emerald-600 text-xs"></i>
                                    </div>
                                    <span class="font-medium"><?= $patient['total_examinations'] ?? 0 ?> examinations</span>
                                </div>
                            </div>
                            
                            <!-- Action Buttons with Enhanced Styling -->
                            <div class="space-y-3">
                                <div class="flex space-x-2">
                                    <a href="<?= base_url('patient/' . $patient['id']) ?>" 
                                       class="flex-1 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-cyan-600 text-white text-sm font-bold rounded-xl hover:from-emerald-600 hover:to-cyan-700 transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-emerald-500/25 text-center">
                                        <i class="fas fa-eye mr-1"></i>View
                                    </a>
                                    <a href="<?= base_url('patient/' . $patient['id'] . '/edit') ?>" 
                                       class="px-4 py-2.5 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= base_url('odontogram/' . $patient['id']) ?>" 
                                       class="px-4 py-2.5 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                        <i class="fas fa-tooth"></i>
                                    </a>
                                </div>
                                <button onclick="deletePatient(<?= $patient['id'] ?>)" 
                                        class="w-full px-4 py-2 text-red-600 hover:text-red-700 hover:bg-gradient-to-r hover:from-red-50 hover:to-pink-50 rounded-xl transition-all duration-300 hover:scale-105 font-semibold text-sm">
                                    <i class="fas fa-trash mr-1"></i>Delete Patient
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Enhanced List View (Default) -->
            <div id="listView">
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-cyan-600/5 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-500 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gradient-to-r from-emerald-50 to-cyan-50 border-b border-emerald-200">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-user text-emerald-600"></i>
                                                <span>Patient</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-phone text-emerald-600"></i>
                                                <span>Contact</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-birthday-cake text-emerald-600"></i>
                                                <span>Age</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-info-circle text-emerald-600"></i>
                                                <span>Status</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-stethoscope text-emerald-600"></i>
                                                <span>Examinations</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-cogs text-emerald-600"></i>
                                                <span>Actions</span>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-emerald-100">
                                    <?php foreach ($patients as $patient): ?>
                                        <?php
                                        $dob = new DateTime($patient['date_of_birth']);
                                        $now = new DateTime();
                                        $age = $now->diff($dob)->y;
                                        ?>
                                        <tr class="group/row hover:bg-gradient-to-r hover:from-emerald-50/50 hover:to-cyan-50/50 transition-all duration-300">
                                            <td class="px-6 py-5 whitespace-nowrap">
                                                <div class="flex items-center space-x-4">
                                                    <div class="relative">
                                                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-cyan-600 rounded-2xl blur-lg opacity-75 group-hover/row:opacity-100 transition-opacity duration-300"></div>
                                                        <div class="relative w-12 h-12 bg-gradient-to-br from-emerald-500 to-cyan-600 rounded-2xl flex items-center justify-center shadow-lg group-hover/row:scale-110 group-hover/row:rotate-3 transition-all duration-300">
                                                            <span class="text-white font-bold text-sm"><?= strtoupper(substr($patient['first_name'], 0, 1) . substr($patient['last_name'], 0, 1)) ?></span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="text-base font-black text-gray-900 group-hover/row:text-emerald-900 transition-colors duration-300"><?= $patient['first_name'] . ' ' . $patient['last_name'] ?></div>
                                                        <div class="text-sm text-gray-500 font-medium">ID: <?= $patient['patient_id'] ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-5 whitespace-nowrap">
                                                <div class="space-y-1">
                                                    <div class="flex items-center text-sm text-gray-900 font-medium">
                                                        <i class="fas fa-phone text-emerald-600 mr-2 text-xs"></i>
                                                        <?= $patient['phone'] ?>
                                                    </div>
                                                    <div class="flex items-center text-sm text-gray-500">
                                                        <i class="fas fa-envelope text-gray-400 mr-2 text-xs"></i>
                                                        <?= $patient['email'] ?: 'No email' ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-5 whitespace-nowrap">
                                                <div class="flex items-center text-base font-bold text-gray-900">
                                                    <i class="fas fa-birthday-cake text-amber-500 mr-2 text-sm"></i>
                                                    <?= $age ?> years
                                                </div>
                                            </td>
                                            <td class="px-6 py-5 whitespace-nowrap">
                                                <span class="px-3 py-1.5 text-xs font-bold rounded-full <?= $patient['status'] === 'active' ? 'bg-gradient-to-r from-emerald-100 to-cyan-100 text-emerald-800 border border-emerald-200' : ($patient['status'] === 'inactive' ? 'bg-gradient-to-r from-gray-100 to-slate-100 text-gray-800 border border-gray-200' : 'bg-gradient-to-r from-amber-100 to-orange-100 text-amber-800 border border-amber-200') ?>">
                                                    <i class="fas fa-circle text-xs mr-1"></i>
                                                    <?= ucfirst($patient['status']) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-5 whitespace-nowrap">
                                                <div class="flex items-center text-base font-bold text-gray-900">
                                                    <i class="fas fa-stethoscope text-emerald-500 mr-2 text-sm"></i>
                                                    <?= $patient['total_examinations'] ?? 0 ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-5 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="<?= base_url('patient/' . $patient['id']) ?>" 
                                                       class="group/action relative p-2.5 text-emerald-600 hover:text-emerald-800 hover:bg-gradient-to-r hover:from-emerald-50 hover:to-cyan-50 rounded-xl transition-all duration-300 hover:scale-110" title="View Details">
                                                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/10 to-cyan-500/10 rounded-xl blur opacity-0 group-hover/action:opacity-100 transition-opacity duration-300"></div>
                                                        <i class="fas fa-eye relative z-10"></i>
                                                    </a>
                                                    <a href="<?= base_url('patient/' . $patient['id'] . '/edit') ?>" 
                                                       class="group/action relative p-2.5 text-blue-600 hover:text-blue-800 hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 rounded-xl transition-all duration-300 hover:scale-110" title="Edit Patient">
                                                        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-cyan-500/10 rounded-xl blur opacity-0 group-hover/action:opacity-100 transition-opacity duration-300"></div>
                                                        <i class="fas fa-edit relative z-10"></i>
                                                    </a>
                                                    <a href="<?= base_url('odontogram/' . $patient['id']) ?>" 
                                                       class="group/action relative p-2.5 text-purple-600 hover:text-purple-800 hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 rounded-xl transition-all duration-300 hover:scale-110" title="View Odontogram">
                                                        <div class="absolute inset-0 bg-gradient-to-r from-purple-500/10 to-pink-500/10 rounded-xl blur opacity-0 group-hover/action:opacity-100 transition-opacity duration-300"></div>
                                                        <i class="fas fa-tooth relative z-10"></i>
                                                    </a>
                                                    <button onclick="deletePatient(<?= $patient['id'] ?>)" 
                                                            class="group/action relative p-2.5 text-red-600 hover:text-red-800 hover:bg-gradient-to-r hover:from-red-50 hover:to-pink-50 rounded-xl transition-all duration-300 hover:scale-110" title="Delete Patient">
                                                        <div class="absolute inset-0 bg-gradient-to-r from-red-500/10 to-pink-500/10 rounded-xl blur opacity-0 group-hover/action:opacity-100 transition-opacity duration-300"></div>
                                                        <i class="fas fa-trash relative z-10"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Pagination -->
        <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
            <div class="mt-8 flex justify-center">
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <?= $pager->links() ?>
                </div>
            </div>
        <?php endif; ?>
            
        <?php else: ?>
            <!-- Enhanced Empty State -->
            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-cyan-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-500">
                    <div class="text-center py-20 px-8">
                        <div class="relative w-24 h-24 bg-gradient-to-br from-emerald-100 to-cyan-100 rounded-3xl flex items-center justify-center mx-auto mb-8 group-hover:scale-110 transition-transform duration-300">
                            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-cyan-600 rounded-3xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <i class="fas fa-users text-emerald-600 text-4xl relative z-10"></i>
                        </div>
                        <h3 class="text-2xl font-black text-gray-900 mb-4">No patients found</h3>
                        <p class="text-gray-600 mb-8 max-w-md mx-auto text-lg font-medium">Get started by adding your first patient to begin managing their dental care journey with our comprehensive management system.</p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="<?= base_url('patient/create') ?>" class="group/btn relative inline-flex items-center px-8 py-4 bg-gradient-to-r from-emerald-500 to-cyan-600 text-white text-lg font-bold rounded-xl hover:from-emerald-600 hover:to-cyan-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-emerald-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-cyan-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-plus mr-3 relative z-10"></i>
                                <span class="relative z-10">Add Your First Patient</span>
                            </a>
                            <button onclick="importPatients()" class="group/btn relative inline-flex items-center px-8 py-4 border-2 border-emerald-300 text-emerald-700 text-lg font-bold rounded-xl hover:bg-gradient-to-r hover:from-emerald-50 hover:to-cyan-50 hover:border-emerald-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/10 to-cyan-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-upload mr-3 relative z-10"></i>
                                <span class="relative z-10">Import Patients</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Quick Actions Section -->
        <div class="mt-8">
            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-cyan-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-500">
                    <div class="p-6">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-3 h-3 bg-gradient-to-r from-emerald-500 to-cyan-600 rounded-full animate-pulse"></div>
                            <h3 class="text-xl font-black text-gray-900">Quick Actions</h3>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <!-- Add Patient -->
                            <a href="<?= base_url('patient/create') ?>" class="group/action relative flex flex-col items-center p-6 rounded-2xl border-2 border-dashed border-emerald-200 hover:border-emerald-400 hover:bg-gradient-to-br hover:from-emerald-50/80 hover:to-cyan-50/80 transition-all duration-500 hover:shadow-2xl hover:shadow-emerald-500/20 hover:scale-105 hover:-translate-y-2">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-cyan-600/10 rounded-2xl blur-xl group-hover/action:blur-2xl transition-all duration-500 opacity-0 group-hover/action:opacity-100"></div>
                                <div class="relative z-10 w-16 h-16 bg-gradient-to-br from-emerald-500 to-cyan-600 rounded-2xl flex items-center justify-center mb-4 shadow-2xl shadow-emerald-500/25 group-hover/action:scale-110 group-hover/action:rotate-6 transition-all duration-500">
                                    <i class="fas fa-user-plus text-2xl text-white"></i>
                                </div>
                                <div class="relative z-10 text-center space-y-1">
                                    <span class="text-base font-bold text-gray-900 group-hover/action:text-emerald-900 transition-colors duration-300">Add Patient</span>
                                    <span class="text-xs text-gray-500 font-medium">Register new patient</span>
                                </div>
                            </a>

                            <!-- Import Patients -->
                            <button onclick="importPatients()" class="group/action relative flex flex-col items-center p-6 rounded-2xl border-2 border-dashed border-blue-200 hover:border-blue-400 hover:bg-gradient-to-br hover:from-blue-50/80 hover:to-cyan-50/80 transition-all duration-500 hover:shadow-2xl hover:shadow-blue-500/20 hover:scale-105 hover:-translate-y-2">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-cyan-600/10 rounded-2xl blur-xl group-hover/action:blur-2xl transition-all duration-500 opacity-0 group-hover/action:opacity-100"></div>
                                <div class="relative z-10 w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl flex items-center justify-center mb-4 shadow-2xl shadow-blue-500/25 group-hover/action:scale-110 group-hover/action:rotate-6 transition-all duration-500">
                                    <i class="fas fa-upload text-2xl text-white"></i>
                                </div>
                                <div class="relative z-10 text-center space-y-1">
                                    <span class="text-base font-bold text-gray-900 group-hover/action:text-blue-900 transition-colors duration-300">Import Patients</span>
                                    <span class="text-xs text-gray-500 font-medium">Bulk import data</span>
                                </div>
                            </button>

                            <!-- Export Patients -->
                            <button onclick="exportPatients()" class="group/action relative flex flex-col items-center p-6 rounded-2xl border-2 border-dashed border-purple-200 hover:border-purple-400 hover:bg-gradient-to-br hover:from-purple-50/80 hover:to-pink-50/80 transition-all duration-500 hover:shadow-2xl hover:shadow-purple-500/20 hover:scale-105 hover:-translate-y-2">
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-pink-600/10 rounded-2xl blur-xl group-hover/action:blur-2xl transition-all duration-500 opacity-0 group-hover/action:opacity-100"></div>
                                <div class="relative z-10 w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center mb-4 shadow-2xl shadow-purple-500/25 group-hover/action:scale-110 group-hover/action:rotate-6 transition-all duration-500">
                                    <i class="fas fa-download text-2xl text-white"></i>
                                </div>
                                <div class="relative z-10 text-center space-y-1">
                                    <span class="text-base font-bold text-gray-900 group-hover/action:text-purple-900 transition-colors duration-300">Export Data</span>
                                    <span class="text-xs text-gray-500 font-medium">Download patient data</span>
                                </div>
                            </button>

                            <!-- Bulk Actions -->
                            <button onclick="bulkActions()" class="group/action relative flex flex-col items-center p-6 rounded-2xl border-2 border-dashed border-amber-200 hover:border-amber-400 hover:bg-gradient-to-br hover:from-amber-50/80 hover:to-orange-50/80 transition-all duration-500 hover:shadow-2xl hover:shadow-amber-500/20 hover:scale-105 hover:-translate-y-2">
                                <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-orange-600/10 rounded-2xl blur-xl group-hover/action:blur-2xl transition-all duration-500 opacity-0 group-hover/action:opacity-100"></div>
                                <div class="relative z-10 w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center mb-4 shadow-2xl shadow-amber-500/25 group-hover/action:scale-110 group-hover/action:rotate-6 transition-all duration-500">
                                    <i class="fas fa-tasks text-2xl text-white"></i>
                                </div>
                                <div class="relative z-10 text-center space-y-1">
                                    <span class="text-base font-bold text-gray-900 group-hover/action:text-amber-900 transition-colors duration-300">Bulk Actions</span>
                                    <span class="text-xs text-gray-500 font-medium">Manage multiple patients</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Simplified Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeDeleteModal()"></div>
        
        <div class="relative bg-white rounded-lg shadow-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Delete Patient</h3>
                </div>
                
                <p class="text-gray-600 mb-6">
                    Are you sure you want to delete this patient? This action cannot be undone and will permanently remove all patient data.
                </p>
                
                <div class="flex space-x-3">
                    <button onclick="closeDeleteModal()" 
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button onclick="confirmDelete()" 
                            class="flex-1 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition-colors">
                        Delete Patient
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let patientToDelete = null;
let currentView = 'list'; // Default to list view

// View toggle functionality removed - only list view is shown


// Export Patients Function
function exportPatients() {
    // Create a simple CSV export
    const patients = <?= json_encode($patients ?? []) ?>;
    const csvContent = "data:text/csv;charset=utf-8," + 
        "ID,First Name,Last Name,Phone,Email,Age,Status,Examinations\n" +
        patients.map(p => {
            const dob = new Date(p.date_of_birth);
            const now = new Date();
            const age = now.getFullYear() - dob.getFullYear();
            return `${p.patient_id},${p.first_name},${p.last_name},${p.phone},${p.email || ''},${age},${p.status},${p.total_examinations || 0}`;
        }).join('\n');
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "patients_export.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Import Patients Function
function importPatients() {
    alert('Import functionality would be implemented here. This would typically open a file upload dialog or redirect to an import page.');
}

// Bulk Actions Function
function bulkActions() {
    alert('Bulk actions functionality would be implemented here. This would typically open a modal with options like bulk delete, bulk status update, etc.');
}

// Delete Patient Functionality
function deletePatient(patientId) {
    patientToDelete = patientId;
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('hidden');
}

function closeDeleteModal() {
    patientToDelete = null;
    const modal = document.getElementById('deleteModal');
    modal.classList.add('hidden');
}

function confirmDelete() {
    if (patientToDelete) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= base_url('patient') ?>/' + patientToDelete;
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '<?= csrf_token() ?>';
        csrfInput.value = '<?= csrf_hash() ?>';
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Basic Modal Interactions
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('deleteModal');
    
    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeDeleteModal();
        }
    });
});
</script>
<?= $this->endSection() ?>
