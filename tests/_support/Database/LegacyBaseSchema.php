<?php
declare(strict_types=1);

namespace Tests\Support\Database;

/**
 * Minimal Legacy Base Schema (Pre-Migration Foundation)
 *
 * Defines ONLY the 18 foundation tables that predated CodeIgniter 4 migrations.
 * This class contains NO business data, NO credentials, and NO DROP TABLE statements.
 * All migrated tables and columns are created exclusively by canonical migrations
 * in app/Database/Migrations/ executed via the CodeIgniter MigrationRunner.
 */
final class LegacyBaseSchema
{
    /**
     * Array of CREATE TABLE IF NOT EXISTS statements for the 18 pre-migration foundation tables.
     */
    public const DDL_STATEMENTS = [
        "CREATE TABLE IF NOT EXISTS `users` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `ip_address` varchar(45) DEFAULT NULL,
          `username` varchar(100) DEFAULT NULL,
          `password` varchar(255) NOT NULL,
          `email` varchar(254) NOT NULL,
          `activation_selector` varchar(255) DEFAULT NULL,
          `activation_code` varchar(255) DEFAULT NULL,
          `forgotten_password_selector` varchar(255) DEFAULT NULL,
          `forgotten_password_code` varchar(255) DEFAULT NULL,
          `forgotten_password_time` int(10) unsigned DEFAULT NULL,
          `remember_selector` varchar(255) DEFAULT NULL,
          `remember_code` varchar(255) DEFAULT NULL,
          `created_at` int(10) unsigned DEFAULT NULL,
          `last_login` int(10) unsigned DEFAULT NULL,
          `active` tinyint(3) unsigned DEFAULT NULL,
          `address` text DEFAULT NULL,
          `first_name` varchar(50) DEFAULT NULL,
          `last_name` varchar(50) DEFAULT NULL,
          `company` varchar(100) DEFAULT NULL,
          `phone` varchar(20) DEFAULT NULL,
          `license_number` varchar(50) DEFAULT NULL COMMENT 'Medical license number',
          `specialization` varchar(100) DEFAULT NULL COMMENT 'Medical specialization',
          `years_experience` int(11) DEFAULT NULL COMMENT 'Years of medical experience',
          `consultation_fee` decimal(10,2) DEFAULT NULL COMMENT 'Consultation fee per visit',
          `medical_qualifications` text DEFAULT NULL COMMENT 'Medical degrees and certifications',
          `availability_schedule` longtext DEFAULT NULL COMMENT 'Doctor availability schedule',
          `department` varchar(50) DEFAULT NULL COMMENT 'Department assignment',
          `created_on` int(11) NOT NULL,
          `updated_at` int(11) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uc_email` (`email`),
          UNIQUE KEY `uc_activation_selector` (`activation_selector`),
          UNIQUE KEY `uc_forgotten_password_selector` (`forgotten_password_selector`),
          UNIQUE KEY `uc_remember_selector` (`remember_selector`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

        "CREATE TABLE IF NOT EXISTS `groups` (
          `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
          `name` varchar(20) NOT NULL,
          `description` varchar(100) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

        "CREATE TABLE IF NOT EXISTS `users_groups` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `user_id` int(10) unsigned NOT NULL,
          `group_id` mediumint(8) unsigned NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uc_users_groups` (`user_id`,`group_id`),
          KEY `fk_users_groups_users1_idx` (`user_id`),
          KEY `fk_users_groups_groups1_idx` (`group_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

        "CREATE TABLE IF NOT EXISTS `login_attempts` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `ip_address` varchar(45) NOT NULL,
          `login` varchar(100) NOT NULL,
          `time` int(10) unsigned DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

        "CREATE TABLE IF NOT EXISTS `patients` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `patient_id` varchar(20) NOT NULL,
          `first_name` varchar(50) NOT NULL,
          `last_name` varchar(50) NOT NULL,
          `email` varchar(100) DEFAULT NULL,
          `phone` varchar(15) NOT NULL,
          `date_of_birth` date NOT NULL,
          `gender` enum('male','female','other') NOT NULL,
          `city` varchar(50) DEFAULT NULL,
          `medical_history` text DEFAULT NULL,
          `allergies` text DEFAULT NULL,
          `insurance_provider` varchar(100) DEFAULT NULL,
          `insurance_number` varchar(50) DEFAULT NULL,
          `notes` text DEFAULT NULL,
          `status` enum('active','inactive','archived') DEFAULT 'active',
          `created_by` int(11) DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          `deleted_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `patient_id` (`patient_id`),
          UNIQUE KEY `email` (`email`),
          KEY `idx_email` (`email`),
          KEY `idx_phone` (`phone`),
          KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

        "CREATE TABLE IF NOT EXISTS `appointments` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `patient_id` int(11) NOT NULL,
          `appointment_id` varchar(20) NOT NULL,
          `appointment_date` date NOT NULL,
          `appointment_time` time NOT NULL,
          `duration` int(11) NOT NULL DEFAULT 30 COMMENT 'Duration in minutes',
          `appointment_type` enum('consultation','treatment','follow_up','emergency','cleaning','checkup') NOT NULL,
          `status` enum('scheduled','confirmed','arrived','ready','in_chair','checkout','completed','cancelled','no_show') DEFAULT 'scheduled',
          `notes` text DEFAULT NULL,
          `created_by` int(11) DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          `deleted_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `appointment_id` (`appointment_id`),
          KEY `idx_patient_id` (`patient_id`),
          KEY `idx_appointment_date` (`appointment_date`),
          KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

        "CREATE TABLE IF NOT EXISTS `finances` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `patient_id` int(11) DEFAULT NULL,
          `examination_id` int(11) DEFAULT NULL,
          `transaction_id` varchar(20) NOT NULL,
          `transaction_type` enum('payment','invoice','refund','adjustment') NOT NULL,
          `amount` decimal(10,2) NOT NULL,
          `currency` enum('USD','EUR','GBP','BDT') DEFAULT 'USD',
          `payment_method` enum('cash','card','bank_transfer','check','insurance','other') NOT NULL,
          `liquidity_account` varchar(40) DEFAULT NULL,
          `payment_status` enum('pending','paid','partial','overdue','cancelled') DEFAULT 'pending',
          `invoice_status` varchar(20) DEFAULT NULL,
          `description` text DEFAULT NULL,
          `service_type` enum('consultation','treatment','medication','procedure','other') NOT NULL,
          `service_details` text DEFAULT NULL,
          `discount_amount` decimal(10,2) DEFAULT 0.00,
          `tax_amount` decimal(10,2) DEFAULT 0.00,
          `due_date` date DEFAULT NULL,
          `paid_date` date DEFAULT NULL,
          `issued_at` datetime DEFAULT NULL,
          `cancelled_at` datetime DEFAULT NULL,
          `notes` text DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          `deleted_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `transaction_id` (`transaction_id`),
          KEY `idx_patient_id` (`patient_id`),
          KEY `idx_transaction_type` (`transaction_type`),
          KEY `idx_payment_status` (`payment_status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

        "CREATE TABLE IF NOT EXISTS `examinations` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `patient_id` int(11) NOT NULL,
          `examination_id` varchar(20) NOT NULL,
          `examination_date` date NOT NULL,
          `examination_type` enum('initial','periodic','emergency','follow_up') NOT NULL,
          `chief_complaint` text NOT NULL,
          `history_of_present_illness` text DEFAULT NULL,
          `medical_history` text DEFAULT NULL,
          `dental_history` text DEFAULT NULL,
          `clinical_findings` text DEFAULT NULL,
          `diagnosis` text DEFAULT NULL,
          `prognosis` text DEFAULT NULL,
          `recommendations` text DEFAULT NULL,
          `examination_notes` text DEFAULT NULL,
          `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
          `next_appointment` date DEFAULT NULL,
          `treatment_plan` text DEFAULT NULL,
          `created_by` int(11) DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          `deleted_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `examination_id` (`examination_id`),
          KEY `idx_patient_id` (`patient_id`),
          KEY `idx_examination_date` (`examination_date`),
          KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

        "CREATE TABLE IF NOT EXISTS `prescriptions` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `patient_id` int(11) NOT NULL,
          `examination_id` int(11) DEFAULT NULL,
          `prescription_id` varchar(20) NOT NULL,
          `medication_name` text NOT NULL,
          `dosage` varchar(50) NOT NULL,
          `frequency` varchar(50) NOT NULL,
          `duration` varchar(50) NOT NULL,
          `instructions` text NOT NULL,
          `prescribed_date` date NOT NULL,
          `expiry_date` date NOT NULL,
          `status` enum('active','expired','cancelled','pending') DEFAULT 'active',
          `created_by` int(11) DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          `deleted_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `prescription_id` (`prescription_id`),
          KEY `idx_patient_id` (`patient_id`),
          KEY `idx_status` (`status`),
          KEY `idx_prescribed_date` (`prescribed_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

        "CREATE TABLE IF NOT EXISTS `settings` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `setting_key` varchar(100) NOT NULL,
          `setting_value` text DEFAULT NULL,
          `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
          `description` text DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `setting_key` (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

        "CREATE TABLE IF NOT EXISTS `treatments` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `patient_id` int(11) NOT NULL,
          `examination_id` int(11) DEFAULT NULL,
          `treatment_id` varchar(20) NOT NULL,
          `treatment_name` varchar(100) NOT NULL,
          `treatment_type` enum('cleaning','extraction','filling','crown','root_canal','orthodontic','implant','other') NOT NULL,
          `tooth_number` varchar(10) DEFAULT NULL,
          `tooth_numbers` varchar(50) DEFAULT NULL COMMENT 'Comma-separated tooth numbers',
          `treatment_description` text DEFAULT NULL,
          `treatment_notes` text DEFAULT NULL,
          `materials_used` text DEFAULT NULL,
          `cost` decimal(10,2) DEFAULT 0.00,
          `status` enum('active','completed','cancelled','on_hold') DEFAULT 'active',
          `start_date` date DEFAULT NULL,
          `estimated_duration` int(11) DEFAULT NULL,
          `completion_date` datetime DEFAULT NULL,
          `end_date` date DEFAULT NULL,
          `created_by` int(11) DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          `deleted_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `treatment_id` (`treatment_id`),
          KEY `idx_patient_id` (`patient_id`),
          KEY `idx_treatment_type` (`treatment_type`),
          KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

        "CREATE TABLE IF NOT EXISTS `odontograms` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `patient_id` int(11) NOT NULL,
          `examination_id` int(11) DEFAULT NULL,
          `tooth_number` tinyint(4) NOT NULL,
          `tooth_position` enum('upper_right','upper_left','lower_left','lower_right') NOT NULL,
          `condition_type` enum('healthy','cavity','filling','crown','root_canal','extracted','implant','bridge','partial_denture','full_denture','other') NOT NULL,
          `condition_description` text DEFAULT NULL,
          `treatment_notes` text DEFAULT NULL,
          `treatment_date` date DEFAULT NULL,
          `treatment_status` enum('pending','in_progress','completed','needs_attention') DEFAULT 'pending',
          `created_by` int(11) DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          `deleted_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_patient_id` (`patient_id`),
          KEY `idx_tooth_number` (`tooth_number`),
          KEY `idx_condition_type` (`condition_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

        "CREATE TABLE IF NOT EXISTS `inventory` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `item_name` varchar(100) NOT NULL,
          `category` enum('medication','equipment','supplies','consumables','other') NOT NULL,
          `description` text DEFAULT NULL,
          `quantity` int(11) NOT NULL DEFAULT 0,
          `min_quantity` int(11) NOT NULL DEFAULT 0,
          `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
          `unit` enum('pieces','boxes','bottles','tubes','packs','sets') DEFAULT 'pieces',
          `supplier` varchar(100) DEFAULT NULL,
          `purchase_date` date DEFAULT NULL,
          `expiry_date` date DEFAULT NULL,
          `location` varchar(100) DEFAULT NULL,
          `notes` text DEFAULT NULL,
          `status` enum('active','inactive','discontinued') DEFAULT 'active',
          `created_by` int(11) DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `idx_category` (`category`),
          KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

        "CREATE TABLE IF NOT EXISTS `inventory_usage` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `treatment_id` int(11) DEFAULT NULL COMMENT 'Reference to treatments table (optional for general usage)',
          `usage_date` date NOT NULL COMMENT 'Date when items were used',
          `items_used` text NOT NULL COMMENT 'JSON array of items used with quantities and costs',
          `total_cost` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total cost of all items used',
          `notes` text DEFAULT NULL COMMENT 'Additional notes about the usage',
          `recorded_by` int(11) NOT NULL COMMENT 'User ID who recorded this usage',
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `idx_treatment_id` (`treatment_id`),
          KEY `idx_usage_date` (`usage_date`),
          KEY `idx_recorded_by` (`recorded_by`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

        "CREATE TABLE IF NOT EXISTS `appointment_summary` (
          `appointment_date` date DEFAULT NULL,
          `appointment_type` enum('consultation','treatment','follow_up','emergency','cleaning','checkup') DEFAULT NULL,
          `status` enum('scheduled','confirmed','completed','cancelled','no_show') DEFAULT NULL,
          `appointment_count` bigint(20) DEFAULT NULL,
          `average_duration` decimal(14,4) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

        "CREATE TABLE IF NOT EXISTS `financial_summary` (
          `transaction_date` date DEFAULT NULL,
          `transaction_type` enum('payment','invoice','refund','adjustment') DEFAULT NULL,
          `payment_method` enum('cash','card','bank_transfer','check','insurance','other') DEFAULT NULL,
          `transaction_count` bigint(20) DEFAULT NULL,
          `total_amount` decimal(32,2) DEFAULT NULL,
          `average_amount` decimal(14,6) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

        "CREATE TABLE IF NOT EXISTS `patient_examination_summary` (
          `patient_id` int(11) DEFAULT NULL,
          `patient_number` varchar(20) DEFAULT NULL,
          `patient_name` varchar(101) DEFAULT NULL,
          `phone` varchar(15) DEFAULT NULL,
          `email` varchar(100) DEFAULT NULL,
          `total_examinations` bigint(20) DEFAULT NULL,
          `last_examination_date` date DEFAULT NULL,
          `completed_examinations` bigint(20) DEFAULT NULL,
          `pending_examinations` bigint(20) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

        "CREATE TABLE IF NOT EXISTS `permission_audit_log` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `user_id` int(10) unsigned NOT NULL COMMENT 'User whose permissions changed',
          `action` enum('role_assigned','role_removed','permission_granted','permission_denied','permission_expired') NOT NULL,
          `role_id` int(10) unsigned DEFAULT NULL,
          `permission_id` int(10) unsigned DEFAULT NULL,
          `performed_by` int(10) unsigned NOT NULL COMMENT 'Admin who made the change',
          `ip_address` varchar(45) DEFAULT NULL,
          `user_agent` text DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `idx_user_id` (`user_id`),
          KEY `idx_performed_by` (`performed_by`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
    ];

    /**
     * Apply the 18 foundation tables non-destructively to the given database connection.
     *
     * @param \CodeIgniter\Database\BaseConnection $db
     */
    public static function apply($db): void
    {
        $db->query("SET FOREIGN_KEY_CHECKS = 0");
        foreach (self::DDL_STATEMENTS as $ddl) {
            $db->query($ddl);
        }
        $db->query("SET FOREIGN_KEY_CHECKS = 1");
        $db->resetDataCache();
    }
}
