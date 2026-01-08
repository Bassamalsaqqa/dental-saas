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
            <p class="text-muted mb-0">Create a new user with roles and permissions</p>
        </div>
        <a href="<?= base_url('user-management') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('validation')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach (session()->getFlashdata('validation')->getErrors() as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
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

    <form action="<?= base_url('user-management/store') ?>" method="post" id="userForm">
        <div class="row">
            <!-- User Information -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">User Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?= old('first_name') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?= old('last_name') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= old('email') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= old('phone') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Minimum 8 characters</div>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role Assignment -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Role Assignment</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Select one or more roles for this user:</p>
                        
                        <?php foreach ($roles as $role): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input role-checkbox" 
                                       type="checkbox" 
                                       name="roles[]" 
                                       value="<?= $role['id'] ?>"
                                       id="role_<?= $role['id'] ?>"
                                       <?= old('roles') && in_array($role['id'], old('roles')) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="role_<?= $role['id'] ?>">
                                    <strong><?= esc($role['name']) ?></strong>
                                    <?php if ($role['description']): ?>
                                        <br><small class="text-muted"><?= esc($role['description']) ?></small>
                                    <?php endif; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>

                        <?php if (empty($roles)): ?>
                            <div class="text-center py-3">
                                <p class="text-muted">No roles available</p>
                                <a href="<?= base_url('roles/create') ?>" class="btn btn-sm btn-outline-primary">
                                    Create First Role
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Individual Permissions -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Individual Permissions</h6>
                        <small class="text-muted">Optional overrides</small>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Grant additional permissions beyond role permissions:</p>
                        
                        <div class="permission-list" style="max-height: 400px; overflow-y: auto;">
                            <?php foreach ($permissions as $module => $modulePermissions): ?>
                                <div class="permission-module mb-3">
                                    <h6 class="text-primary mb-2">
                                        <i class="fas fa-<?= getModuleIcon($module) ?>"></i>
                                        <?= esc($categories[$module] ?? ucfirst($module)) ?>
                                    </h6>
                                    
                                    <?php foreach ($modulePermissions as $permission): ?>
                                        <div class="form-check mb-1">
                                            <input class="form-check-input permission-checkbox" 
                                                   type="checkbox" 
                                                   name="permissions[]" 
                                                   value="<?= $permission['id'] ?>"
                                                   id="perm_<?= $permission['id'] ?>"
                                                   <?= old('permissions') && in_array($permission['id'], old('permissions')) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="perm_<?= $permission['id'] ?>">
                                                <small><?= esc($permission['name']) ?></small>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="<?= base_url('user-management') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create User
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password confirmation validation
    const password = document.getElementById('password');
    const passwordConfirm = document.getElementById('password_confirm');
    
    function validatePassword() {
        if (password.value !== passwordConfirm.value) {
            passwordConfirm.setCustomValidity('Passwords do not match');
        } else {
            passwordConfirm.setCustomValidity('');
        }
    }
    
    password.addEventListener('change', validatePassword);
    passwordConfirm.addEventListener('keyup', validatePassword);

    // Role selection counter
    updateRoleCounter();
    
    document.querySelectorAll('.role-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateRoleCounter);
    });
});

function updateRoleCounter() {
    const totalRoles = document.querySelectorAll('.role-checkbox').length;
    const selectedRoles = document.querySelectorAll('.role-checkbox:checked').length;
    
    // Update any counters if they exist
    const counters = document.querySelectorAll('.role-counter');
    counters.forEach(counter => {
        counter.textContent = `${selectedRoles}/${totalRoles}`;
    });
}

function getModuleIcon(module) {
    const icons = {
        'dashboard': 'tachometer-alt',
        'patients': 'user-injured',
        'appointments': 'calendar-alt',
        'examinations': 'stethoscope',
        'treatments': 'procedures',
        'prescriptions': 'prescription-bottle-alt',
        'finance': 'dollar-sign',
        'reports': 'chart-bar',
        'inventory': 'boxes',
        'users': 'users',
        'settings': 'cog'
    };
    return icons[module] || 'folder';
}
</script>
<?= $this->endSection() ?>
