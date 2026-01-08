<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
                <div>
                    <h1 class="text-4xl font-bold text-gray-800 mb-2">👥 User Management</h1>
                    <p class="text-gray-600">Manage system users and their access levels</p>
                </div>
                <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                    <?php if (has_permission('settings', 'edit')): ?>
                        <a href="<?= base_url('rbac/setup') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-sm font-medium rounded-lg hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                            <i class="fas fa-cog mr-2"></i>RBAC Setup
                        </a>
                    <?php endif; ?>
                    <?php if (has_permission('users', 'create')): ?>
                        <a href="<?= base_url('users/create') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                            <i class="fas fa-plus mr-2"></i>Create User
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RBAC Status -->
        <?php if (isset($diagnostics) && !$diagnostics['rbac_ready']): ?>
            <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4 shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">RBAC System Not Ready</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>The role-based access control system hasn't been initialized yet. User roles and permissions won't be displayed until you sync the system.</p>
                            <div class="mt-3">
                                <a href="<?= base_url('rbac/setup') ?>" class="inline-flex items-center px-3 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 transition-colors">
                                    <i class="fas fa-cog mr-2"></i>Initialize RBAC System
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">Success</h3>
                        <div class="mt-2 text-sm text-green-700">
                            <p><?= session()->getFlashdata('success') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Error</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p><?= session()->getFlashdata('error') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if (isset($error)): ?>
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Error Loading Users</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p><?= $error ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/30 shadow-lg hover:shadow-xl transition-all duration-300 group">
                    <div class="flex items-center justify-between p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-4 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600 mb-1">Total Users</p>
                                <p class="text-3xl font-bold text-gray-900"><?= $total_users ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/30 shadow-lg hover:shadow-xl transition-all duration-300 group">
                    <div class="flex items-center justify-between p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-4 rounded-2xl bg-gradient-to-br from-green-500 to-green-600 text-white shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-check-circle text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600 mb-1">Active Users</p>
                                <p class="text-3xl font-bold text-gray-900"><?= $active_users ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/30 shadow-lg hover:shadow-xl transition-all duration-300 group">
                    <div class="flex items-center justify-between p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-4 rounded-2xl bg-gradient-to-br from-yellow-500 to-yellow-600 text-white shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-user-slash text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600 mb-1">Inactive Users</p>
                                <p class="text-3xl font-bold text-gray-900"><?= $inactive_users ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/30 shadow-lg hover:shadow-xl transition-all duration-300 group">
                    <div class="flex items-center justify-between p-6">
                        <div class="flex items-center space-x-4">
                            <div class="p-4 rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 text-white shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-user-shield text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600 mb-1">Admin Users</p>
                                <p class="text-3xl font-bold text-gray-900"><?= $diagnostics['total_user_roles'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Grid -->
        <div class="space-y-6">
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/30 shadow-lg hover:shadow-xl transition-all duration-300 group">
                        <div class="p-6">
                            <!-- User Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <?= strtoupper(substr($user->first_name ?? '', 0, 1) . substr($user->last_name ?? '', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-800"><?= esc(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?></h3>
                                        <p class="text-sm text-gray-500"><?= esc($user->email) ?></p>
                                        <p class="text-xs text-gray-400">@<?= esc($user->username ?? $user->email) ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <!-- Status Badge -->
                                    <?php
                                    $statusColors = [
                                        1 => 'bg-green-100 text-green-800 border-green-200',
                                        0 => 'bg-gray-100 text-gray-800 border-gray-200'
                                    ];
                                    $statusColor = $statusColors[$user->active] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                    $statusText = $user->active == 1 ? 'Active' : 'Inactive';
                                    ?>
                                    <span class="px-3 py-1 text-sm font-medium rounded-full border <?= $statusColor ?>">
                                        <?= $statusText ?>
                                    </span>
                                    
                                    <!-- Actions -->
                                    <div class="flex space-x-2">
                                        <?php if (has_permission('users', 'view')): ?>
                                            <a href="<?= base_url('users/' . $user->id) ?>" class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors" title="View User">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (has_permission('users', 'edit')): ?>
                                            <a href="<?= base_url('users/' . $user->id . '/edit') ?>" class="p-2 text-yellow-600 hover:text-yellow-800 hover:bg-yellow-50 rounded-lg transition-colors" title="Edit User">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= base_url('users/' . $user->id . '/change-password') ?>" class="p-2 text-green-600 hover:text-green-800 hover:bg-green-50 rounded-lg transition-colors" title="Change Password">
                                                <i class="fas fa-key"></i>
                                            </a>
                                            <button onclick="toggleStatus(<?= $user->id ?>)" class="p-2 text-purple-600 hover:text-purple-800 hover:bg-purple-50 rounded-lg transition-colors" title="Toggle Status">
                                                <i class="fas fa-toggle-on"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (has_permission('users', 'delete') && $user->id != 1): ?>
                                            <button onclick="deleteUser(<?= $user->id ?>)" class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors" title="Delete User">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- User Details -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Groups/Roles -->
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-700 mb-3">Roles & Permissions</h4>
                                    <?php if (!empty($user->display_roles)): ?>
                                        <div class="space-y-2">
                                            <?php foreach ($user->display_roles as $role): ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-user-tag mr-2"></i>
                                                    <?php 
                                                    // Handle both array and object formats
                                                    $roleName = '';
                                                    if (is_array($role)) {
                                                        $roleName = $role['name'] ?? 'Unknown Role';
                                                    } else {
                                                        $roleName = $role->name ?? 'Unknown Role';
                                                    }
                                                    echo esc(ucfirst($roleName));
                                                    ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4 text-gray-500">
                                            <i class="fas fa-user-tag text-2xl mb-2"></i>
                                            <p>No roles assigned</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Contact Info -->
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-700 mb-3">Contact Information</h4>
                                    <div class="space-y-2">
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-phone w-4 mr-2"></i>
                                            <span><?= esc($user->phone ?? 'Not provided') ?></span>
                                        </div>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-envelope w-4 mr-2"></i>
                                            <span><?= esc($user->email) ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Activity Info -->
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-700 mb-3">Activity</h4>
                                    <div class="space-y-2">
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-calendar w-4 mr-2"></i>
                                            <span>Created: <?= $user->created_on ? date('M j, Y', strtotime($user->created_on)) : 'N/A' ?></span>
                                        </div>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-clock w-4 mr-2"></i>
                                            <span>Last Login: <?= $user->last_login ? date('M j, Y g:i A', strtotime($user->last_login)) : 'Never' ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/30 shadow-lg p-12 text-center">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-users text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">No Users Found</h3>
                    <p class="text-gray-500 mb-8 max-w-md mx-auto">Get started by creating your first user account. You can assign roles and permissions to manage access levels.</p>
                    <a href="<?= base_url('users/create') ?>" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-lg font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                        <i class="fas fa-plus mr-3"></i>Create First User
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Search and Filter Controls -->
        <?php if (!empty($users)): ?>
            <div class="mt-8 bg-white/70 backdrop-blur-sm rounded-2xl border border-white/30 shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">🔍 Search & Filter Users</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative">
                        <input type="text" id="userSearchInput" placeholder="Search users..." class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 pl-10">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <select id="groupFilter" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Roles</option>
                        <?php if (!empty($roles)): ?>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= strtolower($role['name']) ?>"><?= esc(ucfirst($role['name'])) ?></option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="super admin">Super Admin</option>
                            <option value="doctor">Doctor</option>
                            <option value="receptionist">Receptionist</option>
                        <?php endif; ?>
                    </select>
                    <select id="statusFilter" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="mt-4 text-sm text-gray-500">
                    Showing <span id="filteredCount"><?= count($users) ?></span> of <span id="totalCount"><?= count($users) ?></span> users
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// User Filter Management Class
class UserFilterManager {
    constructor() {
        this.searchValue = '';
        this.groupFilter = '';
        this.statusFilter = '';
        this.allUsers = [];
        this.filteredUsers = [];
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadAllUsers();
    }
    
    bindEvents() {
        // Search functionality
        const searchInput = document.getElementById('userSearchInput');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.searchValue = e.target.value.toLowerCase();
                    this.applyFilters();
                }, 300);
            });
        }
        
        // Group filter
        const groupFilter = document.getElementById('groupFilter');
        if (groupFilter) {
            groupFilter.addEventListener('change', (e) => {
                this.groupFilter = e.target.value;
                this.applyFilters();
            });
        }
        
        // Status filter
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                this.statusFilter = e.target.value;
                this.applyFilters();
            });
        }
    }
    
    loadAllUsers() {
        // Get all user cards from the grid
        const userCards = document.querySelectorAll('.space-y-6 > div');
        this.allUsers = Array.from(userCards).map(card => {
            const nameElement = card.querySelector('h3');
            const emailElement = card.querySelector('p');
            const statusElement = card.querySelector('.bg-green-100, .bg-gray-100');
            const rolesElement = card.querySelector('.bg-green-100');
            
            const name = nameElement?.textContent?.toLowerCase() || '';
            const email = emailElement?.textContent?.toLowerCase() || '';
            const status = statusElement?.classList.contains('bg-green-100') ? '1' : '0';
            const roles = Array.from(card.querySelectorAll('.bg-green-100')).map(span => 
                span.textContent.toLowerCase()
            );
            
            return {
                element: card,
                name: name,
                email: email,
                roles: roles,
                status: status,
                searchText: `${name} ${email}`.toLowerCase()
            };
        }).filter(user => user !== null);
        
        this.filteredUsers = [...this.allUsers];
    }
    
    applyFilters() {
        this.filteredUsers = this.allUsers.filter(user => {
            // Search filter
            if (this.searchValue && !user.searchText.includes(this.searchValue)) {
                return false;
            }
            
            // Role filter
            if (this.groupFilter && !user.roles.includes(this.groupFilter)) {
                return false;
            }
            
            // Status filter
            if (this.statusFilter && user.status !== this.statusFilter) {
                return false;
            }
            
            return true;
        });
        
        this.updateDisplay();
    }
    
    updateDisplay() {
        // Hide all cards first
        this.allUsers.forEach(user => {
            user.element.style.display = 'none';
        });
        
        // Show filtered cards
        this.filteredUsers.forEach(user => {
            user.element.style.display = '';
        });
        
        // Update counter
        const filteredCountElement = document.getElementById('filteredCount');
        const totalCountElement = document.getElementById('totalCount');
        if (filteredCountElement) {
            filteredCountElement.textContent = this.filteredUsers.length;
        }
        if (totalCountElement) {
            totalCountElement.textContent = this.allUsers.length;
        }
    }
}

// Initialize the filter manager when the page loads
document.addEventListener('DOMContentLoaded', function() {
    new UserFilterManager();
});

function toggleStatus(id) {
    if (confirm('Are you sure you want to toggle this user\'s status?')) {
        fetch(`<?= base_url('users') ?>/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the user status.');
        });
    }
}

function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        fetch(`<?= base_url('users') ?>/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the user.');
        });
    }
}
</script>
<?= $this->endSection() ?>