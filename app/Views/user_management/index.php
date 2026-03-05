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
            <p class="text-muted mb-0"><?= lang('UserRole.manageUsersRolesPermissions') ?></p>
        </div>
        <a href="<?= base_url('user-management/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?= lang('UserRole.createUser') ?>
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
                    <h6 class="mb-0"><?= lang('UserRole.users') ?></h6>
                </div>
                <div class="col-auto">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" placeholder="<?= lang('UserRole.searchUsers') ?>" id="searchInput">
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
                            <th><?= lang('UserRole.user') ?></th>
                            <th><?= lang('UserRole.email') ?></th>
                            <th><?= lang('UserRole.roles') ?></th>
                            <th><?= lang('UserRole.status') ?></th>
                            <th><?= lang('UserRole.lastLogin') ?></th>
                            <th width="120"><?= lang('UserRole.actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <a href="<?= base_url('user-management/' . $user['id']) ?>" class="text-decoration-none text-reset d-block rounded py-1 px-1">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <?= esc(ui_initials($user['first_name'] ?? '', $user['last_name'] ?? '', 'U')) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?= esc($user['first_name'] . ' ' . $user['last_name']) ?></div>
                                                <small class="text-muted"><?= lang('UserRole.idColon') ?> <?= $user['id'] ?></small>
                                            </div>
                                        </div>
                                    </a>
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
                                        <span class="text-muted"><?= lang('UserRole.noRolesAssigned') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $user['active'] ? 'success' : 'danger' ?>">
                                        <i class="fas fa-<?= $user['active'] ? 'check' : 'times' ?>"></i>
                                        <?= $user['active'] ? lang('UserRole.active') : lang('UserRole.inactive') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['last_login']): ?>
                                        <div><?= formatDate($user['last_login']) ?></div>
                                        <small class="text-muted"><?= formatTime($user['last_login']) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted"><?= lang('UserRole.never') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="<?= base_url('user-management/' . $user['id']) ?>">
                                                <i class="fas fa-eye"></i> <?= lang('UserRole.viewDetails') ?>
                                            </a></li>
                                            <li><a class="dropdown-item" href="<?= base_url('user-management/' . $user['id'] . '/edit') ?>">
                                                <i class="fas fa-edit"></i> <?= lang('UserRole.editUser') ?>
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item" onclick="assignRole(<?= $user['id'] ?>)">
                                                    <i class="fas fa-user-tag"></i> <?= lang('UserRole.assignRole') ?>
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item" onclick="managePermissions(<?= $user['id'] ?>)">
                                                    <i class="fas fa-key"></i> <?= lang('UserRole.managePermissions') ?>
                                                </button>
                                            </li>
                                            <?php if ($user['id'] != 1): // Don't allow deactivating super admin ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="<?= base_url('user-management/' . $user['id'] . '/toggle-status') ?>" method="post" class="d-inline">
                                                        <button type="submit" class="dropdown-item text-<?= $user['active'] ? 'warning' : 'success' ?>">
                                                            <i class="fas fa-<?= $user['active'] ? 'pause' : 'play' ?>"></i>
                                                            <?= $user['active'] ? lang('UserRole.deactivate') : lang('UserRole.activate') ?>
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="<?= base_url('user-management/' . $user['id']) ?>" method="post" class="d-inline" 
                                                          onsubmit="return confirm(<?= json_encode(lang('UserRole.confirmDeleteUserShort')) ?>)">
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="fas fa-trash"></i> <?= lang('UserRole.deleteUser') ?>
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
                            <p class="mb-0"><?= lang('UserRole.totalUsers') ?></p>
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
                            <p class="mb-0"><?= lang('UserRole.activeUsers') ?></p>
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
                            <p class="mb-0"><?= lang('UserRole.availableRoles') ?></p>
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
                            <p class="mb-0"><?= lang('UserRole.inactiveUsers') ?></p>
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
                <h5 class="modal-title"><?= lang('UserRole.assignRole') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="roleForm">
                    <input type="hidden" id="userId" name="user_id">
                    <div class="mb-3">
                        <label for="roleSelect" class="form-label"><?= lang('UserRole.selectRole') ?></label>
                        <select class="form-select" id="roleSelect" name="role_id" required>
                            <option value=""><?= lang('UserRole.chooseRole') ?></option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>"><?= esc($role['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="expiresAt" class="form-label"><?= lang('UserRole.expiresAtOptional') ?></label>
                        <input type="datetime-local" class="form-control" id="expiresAt" name="expires_at">
                        <div class="form-text"><?= lang('UserRole.leaveEmptyForPermanentAssignment') ?></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= lang('UserRole.cancel') ?></button>
                <button type="button" class="btn btn-primary" onclick="submitRoleAssignment()"><?= lang('UserRole.assignRole') ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Permission Management Modal -->
<div class="modal fade" id="permissionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('UserRole.managePermissions') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="permissionContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden"><?= lang('UserRole.loading') ?></span>
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
            alert(<?= json_encode(lang('UserRole.errorPrefix')) ?> + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(<?= json_encode(lang('UserRole.errorAssigningRole')) ?>);
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
    srOnly.textContent = <?= json_encode(lang('UserRole.loading')) ?>;
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
            p.textContent = <?= json_encode(lang('UserRole.permissionManagementPlaceholder')) ?>;
            container.appendChild(p);
        } else {
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger';
            alert.textContent = <?= json_encode(lang('UserRole.errorLoadingPermissionsPrefix')) ?> + data.error;
            container.appendChild(alert);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        container.replaceChildren();
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger';
        alert.textContent = <?= json_encode(lang('UserRole.errorLoadingPermissions')) ?>;
        container.appendChild(alert);
    });
}
</script>
<?= $this->endSection() ?>
