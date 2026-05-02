# CP-UX-01 Verification

## Scope
Layout unification across control plane views (single layout, no legacy layout usage).

## Evidence

### Control plane views use main_control_plane
Command:
`rg -n "layouts/control_plane|control_plane" app\\Views`

Output:
```
app\\Views\\control_plane\\settings.php:1:<?= $this->extend('layouts/main_control_plane') ?>
app\\Views\\control_plane\\clinic_detail.php:1:<?= $this->extend('layouts/main_control_plane') ?>
app\\Views\\control_plane\\plan_form.php:1:<?= $this->extend('layouts/main_control_plane') ?>
app\\Views\\control_plane\\plans.php:1:<?= $this->extend('layouts/main_control_plane') ?>
app\\Views\\control_plane\\clinics.php:1:<?= $this->extend('layouts/main_control_plane') ?>
app\\Views\\control_plane\\operations.php:1:<?= $this->extend('layouts/main_control_plane') ?>
app\\Views\\control_plane\\dashboard.php:1:<?= $this->extend('layouts/main_control_plane') ?>
app\\Views\\control_plane\\entry.php:2:<?= $this->extend('layouts/main_control_plane') ?>
app\\Views\\control_plane\\onboarding\\create_clinic.php:2:<?= $this->extend('layouts/main_control_plane') ?>
```
