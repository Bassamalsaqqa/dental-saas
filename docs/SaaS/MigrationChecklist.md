# Multi-Tenant Migration Checklist

## 1. Schema changes (phased)
| Step | Action | Verification |
| --- | --- | --- |
| 1.1 | Add `clinic_id` columns (nullable) + indexes to tenant tables: `patients`, `appointments`, `examinations`, `finances`, `inventory`, `inventory_usage`, `odontograms`, `prescriptions`, `treatments`, `activity_logs`, `settings`, `appointment_summary`, `financial_summary`, `patient_examination_summary`. | `SHOW COLUMNS FROM <table>` shows `clinic_id` + index. |
| 1.2 | Backfill `clinic_id` for existing rows (default legacy clinic). | `SELECT COUNT(*) FROM <table> WHERE clinic_id IS NULL` returns 0. |
| 1.3 | Alter columns to `NOT NULL`, add composite unique indexes (see plan). | `SHOW CREATE TABLE` shows `UNIQUE KEY (clinic_id, ...)` and `clinic_id` NOT NULL. |
| 1.4 | Add `patients.user_id` column (unique) referencing `users.id`; populate via receptionist + existing mappings, then enforce `NOT NULL`. | Query ensures `patients.user_id` exists for all rows; foreign key optional if needed. |
| 1.5 | Replace unique index on `patients.email` with composite `(clinic_id, email)` (allow empty email). | `SHOW INDEX FROM patients` and `SELECT COUNT(DISTINCT clinic_id, email) = COUNT(*)` ignoring empty emails. |
| 1.6 | Add `clinic_id` to `user_roles` and `user_permissions`; enforce `NOT NULL` (exception super_admin). | `SELECT DISTINCT clinic_id FROM user_roles WHERE role_slug != 'super_admin' AND clinic_id IS NULL` returns 0. |

## 2. Patient onboarding & authentication
- Receptionist flow: when creating patient without email, assign synthetic email `PAT{patient_id}@{clinic}.local`, create IonAuth user with `active=0`, store `whatsapp_number` when available.  
- Self-registration: require email + whatsapp (validate format, e.g., `preg_match('/^\+\d{10,15}$/')`), ensure patient record links to user via `patients.user_id`, set `users.active=1`.
- Update `app\Controllers\Patient.php` validation rules to conditionally require email for self-registration and enforce per-clinic uniqueness.

## 3. Tenant context & filters
- Extend IonAuth session data with `active_clinic_id`, `global_mode`, `is_impersonating`.
- Implement `TenantFilter` (require `active_clinic_id`, membership via `user_clinics`) and `ControlPlaneFilter` (super_admin + `global_mode`).
- Update routes to group as Tenant vs control plane (e.g., `settings/*` under control plane filter). Evidence: `app\Config\Routes.php`.

## 4. Query scoping
- Introduce `TenantBaseModel` that auto-adds `WHERE clinic_id = session('active_clinic_id')` for tenant tables.
- Refactor controller queries (Finance datatable, Examination datatable, Inventory, Prescription, Treatment, Doctor list, ActivityLog notifications) to use tenant models or explicit clinic guard.

## 5. Settings & Export safeguards
- Split settings into global vs clinic (add `clinic_id` to settings rows where appropriate). Update `SettingsService` to respect clinic context.
- Audit log: ensure `activity_logs` stores `clinic_id`; add `audit_logs` table for security events (clinic switch, impersonation, exports). Include `actor_id`, `clinic_id`, `action`, `target_type`, `metadata`, `ip`, `user_agent`.
- Backups and repairs remain control plane only; add `controlplane` filter to settings routes and disable `RepairController` in production (route removal). Evidence: `app\Controllers\Settings.php:655`, `app\Controllers\RepairController.php`.

## 6. Testing & verification
- Build tenant isolation smoke tests covering DataTables, search APIs, calendar feeds, finance export.  
- Add grep-based guard (CI check) for tenant tables without `clinic_id` filters (e.g., `rg -n "->table\\('patients'\\)" -g'*Controller.php' | grep -v clinic_id`).  
- Document steps in docs/SaaS/ImplementationPlan.md for developer reference.
