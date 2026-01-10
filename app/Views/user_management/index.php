<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
            <p class="text-muted mb-0">Manage users, roles, and permissions</p>
        </div>
        <a href="<?= base_url('user-management/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create User
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col">
                    <h6 class="mb-0">Users</h6>
                </div>
                <div class="col-auto">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" placeholder="Search users..." id="searchInput">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="usersTable">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                            <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?= esc($user['first_name'] . ' ' . $user['last_name']) ?></div>
                                            <small class="text-muted">ID: <?= $user['id'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><?= esc($user['email']) ?></div>
                                    <?php if ($user['phone']): ?>
                                        <small class="text-muted"><?= esc($user['phone']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($user['roles'])): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach ($user['roles'] as $role): ?>
                                                <span class="badge bg-<?= $role['is_active'] ? 'primary' : 'secondary' ?>">
                                                    <?= esc($role['name']) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No roles assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $user['active'] ? 'success' : 'danger' ?>">
                                        <i class="fas fa-<?= $user['active'] ? 'check' : 'times' ?>"></i>
                                        <?= $user['active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['last_login']): ?>
                                        <div><?= date('M j, Y', strtotime($user['last_login'])) ?></div>
                                        <small class="text-muted"><?= date('g:i A', strtotime($user['last_login'])) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">Never</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="<?= base_url('user-management/' . $user['id']) ?>">
                                                <i class="fas fa-eye"></i> View Details
                                            </a></li>
                                            <li><a class="dropdown-item" href="<?= base_url('user-management/' . $user['id'] . '/edit') ?>">
                                                <i class="fas fa-edit"></i> Edit User
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item" onclick="assignRole(<?= $user['id'] ?>)">
                                                    <i class="fas fa-user-tag"></i> Assign Role
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item" onclick="managePermissions(<?= $user['id'] ?>)">
                                                    <i class="fas fa-key"></i> Manage Permissions
                                                </button>
                                            </li>
                                            <?php if ($user['id'] != 1): // Don't allow deactivating super admin ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="<?= base_url('user-management/' . $user['id'] . '/toggle-status') ?>" method="post" class="d-inline">
                                                        <button type="submit" class="dropdown-item text-<?= $user['active'] ? 'warning' : 'success' ?>">
                                                            <i class="fas fa-<?= $user['active'] ? 'pause' : 'play' ?>"></i>
                                                            <?= $user['active'] ? 'Deactivate' : 'Activate' ?>
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="<?= base_url('user-management/' . $user['id']) ?>" method="post" class="d-inline" 
                                                          onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="fas fa-trash"></i> Delete User
                                                        </button>
                                                    </form>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?= count($users) ?></h4>
                            <p class="mb-0">Total Users</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?= count(array_filter($users, fn($u) => $u['active'])) ?></h4>
                            <p class="mb-0">Active Users</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?= count($roles) ?></h4>
                            <p class="mb-0">Available Roles</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-tag fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?= count(array_filter($users, fn($u) => !$u['active'])) ?></h4>
                            <p class="mb-0">Inactive Users</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-times fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Role Assignment Modal -->
<div class="modal fade" id="roleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="roleForm">
                    <input type="hidden" id="userId" name="user_id">
                    <div class="mb-3">
                        <label for="roleSelect" class="form-label">Select Role</label>
                        <select class="form-select" id="roleSelect" name="role_id" required>
                            <option value="">Choose a role...</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>"><?= esc($role['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="expiresAt" class="form-label">Expires At (Optional)</label>
                        <input type="datetime-local" class="form-control" id="expiresAt" name="expires_at">
                        <div class="form-text">Leave empty for permanent assignment</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitRoleAssignment()">Assign Role</button>
            </div>
        </div>
    </div>
</div>

<!-- Permission Management Modal -->
<div class="modal fade" id="permissionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Permissions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="permissionContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const usersTable = document.getElementById('usersTable');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = usersTable.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
});

function assignRole(userId) {
    document.getElementById('userId').value = userId;
    document.getElementById('roleSelect').value = '';
    document.getElementById('expiresAt').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('roleModal'));
    modal.show();
}

function submitRoleAssignment() {
    const form = document.getElementById('roleForm');
    const formData = new FormData(form);
    
    fetch('<?= base_url('user-management/assign-role') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while assigning the role.');
    });
}

function managePermissions(userId) {
    const container = document.getElementById('permissionContent');
    const modal = new bootstrap.Modal(document.getElementById('permissionModal'));
    
    // Clear and show spinner
    container.replaceChildren();
    const spinnerDiv = document.createElement('div');
    spinnerDiv.className = 'text-center';
    const spinner = document.createElement('div');
    spinner.className = 'spinner-border';
    spinner.setAttribute('role', 'status');
    const srOnly = document.createElement('span');
    srOnly.className = 'visually-hidden';
    srOnly.textContent = 'Loading...';
    spinner.appendChild(srOnly);
    spinnerDiv.appendChild(spinner);
    container.appendChild(spinnerDiv);

    modal.show();
    
    // Load user permissions
    fetch(`<?= base_url('user-management/') ?>${userId}/permissions`)
    .then(response => response.json())
    .then(data => {
        container.replaceChildren();
        if (data.success) {
            const p = document.createElement('p');
            p.textContent = 'Permission management interface will be implemented here.';
            container.appendChild(p);
        } else {
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger';
            alert.textContent = 'Error loading permissions: ' + data.error;
            container.appendChild(alert);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        container.replaceChildren();
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger';
        alert.textContent = 'An error occurred while loading permissions.';
        container.appendChild(alert);
    });
}
</script>
<?= $this->endSection() ?>
