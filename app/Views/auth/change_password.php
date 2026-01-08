<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .change-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        .change-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .change-body {
            padding: 2rem;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-change {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-change:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .dental-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="change-card">
                    <div class="change-header">
                        <i class="fas fa-lock dental-icon"></i>
                        <h3 class="mb-0">Change Password</h3>
                        <p class="mb-0">Update your account password</p>
                    </div>
                    <div class="change-body">
                        <?php if (session()->getFlashdata('message')): ?>
                            <div class="alert alert-info" role="alert">
                                <?= session()->getFlashdata('message') ?>
                            </div>
                        <?php endif; ?>

                        <?= form_open('auth/change-password', ['class' => 'needs-validation', 'novalidate' => true]) ?>
                            <div class="mb-3">
                                <label for="old" class="form-label">
                                    <i class="fas fa-key me-2"></i>Current Password
                                </label>
                                <?= form_input($old_password) ?>
                                <div class="invalid-feedback">
                                    Please enter your current password.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="new" class="form-label">
                                    <i class="fas fa-lock me-2"></i>New Password
                                </label>
                                <?= form_input($new_password) ?>
                                <div class="invalid-feedback">
                                    Please enter a new password (minimum <?= $minPasswordLength ?> characters).
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="new_confirm" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Confirm New Password
                                </label>
                                <?= form_input($new_password_confirm) ?>
                                <div class="invalid-feedback">
                                    Please confirm your new password.
                                </div>
                            </div>

                            <?= form_hidden($user_id) ?>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-change">
                                    <i class="fas fa-save me-2"></i>Change Password
                                </button>
                            </div>
                        <?= form_close() ?>

                        <div class="text-center">
                            <a href="<?= base_url('dashboard') ?>" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Bootstrap form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        // Password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const newPassword = document.getElementById('new');
            const confirmPassword = document.getElementById('new_confirm');

            function validatePassword() {
                if (newPassword.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity("Passwords don't match");
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }

            newPassword.addEventListener('change', validatePassword);
            confirmPassword.addEventListener('keyup', validatePassword);
        });
    </script>
</body>
</html>