# CP-SUB-01 Verification

## Scope
- Subscription lifecycle transitions (pause/cancel/expire) and reason storage.
- Standing checks include start_at and end_at with deterministic expiration normalization.

## Evidence

### Migration applied
Command:
`php spark migrate:status`

Output:
```
CodeIgniter v4.6.3 Command Line Tool - Server Time: 2026-01-23 18:43:35 UTC+00:00

+-----------+-------------------+----------------------------------------+---------+---------------------+-------+
| Namespace | Version           | Filename                               | Group   | Migrated On         | Batch |
+-----------+-------------------+----------------------------------------+---------+---------------------+-------+
| App       | 2025-01-19-000001 | CreateRBACTables                       | default | 2026-01-13 20:50:15 | 1     |
| App       | 2025-01-19-000002 | CreateActivityLogsTable                | default | 2026-01-13 20:51:09 | 2     |
| App       | 2025-01-19-000003 | AddIsDoctorToRoles                     | default | 2026-01-13 20:51:09 | 2     |
| App       | 2026-01-04-000004 | UpdateFinancesCurrencyEnum             | default | 2026-01-13 20:51:09 | 2     |
| App       | 2026-01-04-000005 | AddTotalAmountToFinances               | default | 2026-01-13 20:51:09 | 2     |
| App       | 2026-01-04-000006 | AddHireDateToUsersTable                | default | 2026-01-13 20:51:09 | 2     |
| App       | 2026-01-13-204835 | CreateClinicsAndMemberships            | default | 2026-01-13 20:51:09 | 2     |
| App       | 2026-01-14-101059 | AddClinicIdToTenantTables              | default | 2026-01-14 10:12:19 | 3     |
| App       | 2026-01-14-101229 | BackfillClinicId                       | default | 2026-01-14 10:13:29 | 4     |
| App       | 2026-01-14-102341 | EnforceClinicIdConstraints             | default | 2026-01-14 10:24:23 | 5     |
| App       | 2026-01-14-174315 | CreateFileAttachmentsTable             | default | 2026-01-14 17:43:31 | 6     |
| App       | 2026-01-15-000000 | AdjustSettingsUniqueIndex              | default | 2026-01-14 18:07:04 | 7     |
| App       | 2026-01-15-085119 | FixMedicalRolesAndSyncPermissions      | default | 2026-01-15 08:51:36 | 8     |
| App       | 2026-01-15-091550 | PurgeTenantRoles                       | default | 2026-01-15 09:16:01 | 9     |
| App       | 2026-01-15-092851 | AddPurposeAndHashToFileAttachments     | default | 2026-01-15 09:29:17 | 10    |
| App       | 2026-01-15-110941 | CreateJobAuditsTable                   | default | 2026-01-15 11:11:06 | 11    |
| App       | 2026-01-15-203050 | CreateClinicNotificationChannelsTable  | default | 2026-01-15 20:31:22 | 12    |
| App       | 2026-01-15-203054 | CreateNotificationsTable               | default | 2026-01-15 20:31:22 | 12    |
| App       | 2026-01-15-212052 | AddParentNotificationIdToNotifications | default | 2026-01-15 21:21:14 | 13    |
| App       | 2026-01-15-213930 | CreatePlansAndSubscriptionsTables      | default | 2026-01-15 21:39:58 | 14    |
| App       | 2026-01-16-220000 | CreateControlPlaneAudits               | default | 2026-01-16 18:35:08 | 15    |
| App       | 2026-01-16-222000 | AddPreferencesToClinicUsers            | default | 2026-01-16 22:18:54 | 16    |
| App       | 2026-01-18-000001 | CreateApiTokensTable                   | default | 2026-01-18 18:52:00 | 18    |
| App       | 2026-01-21-000001 | AddNotesToPrescriptions                | default | 2026-01-22 08:55:05 | 18    |
| App       | 2026-01-22-000001 | AddClinicIdToSummaryTables             | default | 2026-01-22 08:55:28 | 19    |
| App       | 2026-01-22-200000 | CreatePlanRequestsTable                | default | 2026-01-22 18:01:04 | 20    |
| App       | 2026-01-23-070700 | AddSubscriptionLifecycleReasons        | default | 2026-01-23 18:43:32 | 22    |
| App       | 2026-01-23-091500 | CreateEmailTemplates                   | default | 2026-01-23 06:42:11 | 21    |
+-----------+-------------------+----------------------------------------+---------+---------------------+-------+
```

### Lifecycle enforcement + reason fields (rg)
Command:
`rg -n "markExpiredIfNeeded|paused_reason|canceled_reason|NOT_STARTED" app\\Models\\ClinicSubscriptionModel.php app\\Controllers\\ControlPlane\\Clinics.php app\\Controllers\\ControlPlane\\PlanRequests.php app\\Filters\\SubscriptionFilter.php app\\Services\\PlanGuard.php app\\Controllers\\Home.php`

Output:
```
app\\Models\\ClinicSubscriptionModel.php:20:        'canceled_reason',
app\\Models\\ClinicSubscriptionModel.php:22:        'paused_reason',
app\\Models\\ClinicSubscriptionModel.php:48:    public function markExpiredIfNeeded(array $subscription): array
app\\Controllers\\Home.php:31:            $sub = $subModel->markExpiredIfNeeded($sub);
app\\Services\\PlanGuard.php:78:            $sub = $subModel->markExpiredIfNeeded($sub);
app\\Services\\PlanGuard.php:90:                    $reason = 'NOT_STARTED';
app\\Controllers\\ControlPlane\\PlanRequests.php:112:            $current = $subscriptionModel->markExpiredIfNeeded($current);
app\\Controllers\\ControlPlane\\PlanRequests.php:120:                'canceled_reason' => 'plan_change',
app\\Controllers\\ControlPlane\\Clinics.php:127:            $current = $subscriptionModel->markExpiredIfNeeded($current);
app\\Controllers\\ControlPlane\\Clinics.php:135:                'canceled_reason' => 'superseded_by_new_subscription',
app\\Controllers\\ControlPlane\\Clinics.php:167:            $subscription = $subscriptionModel->markExpiredIfNeeded($subscription);
app\\Controllers\\ControlPlane\\Clinics.php:198:            $subscription = $subscriptionModel->markExpiredIfNeeded($subscription);
app\\Controllers\\ControlPlane\\Clinics.php:209:            'canceled_reason' => 'manual_cancel',
app\\Controllers\\ControlPlane\\Clinics.php:229:            $subscription = $subscriptionModel->markExpiredIfNeeded($subscription);
app\\Controllers\\ControlPlane\\Clinics.php:281:            $subscription = $subscriptionModel->markExpiredIfNeeded($subscription);
app\\Controllers\\ControlPlane\\Clinics.php:289:                'canceled_reason' => 'clinic_terminated',
app\\Controllers\\ControlPlane\\Clinics.php:318:            $subscription = $subscriptionModel->markExpiredIfNeeded($subscription);
app\\Controllers\\ControlPlane\\Clinics.php:325:                'paused_reason' => 'clinic_suspended',
app\\Controllers\\ControlPlane\\Clinics.php:354:            $subscription = $subscriptionModel->markExpiredIfNeeded($subscription);
app\\Controllers\\ControlPlane\\Clinics.php:360:                'paused_reason' => null,
app\\Filters\\SubscriptionFilter.php:36:            $sub = $subModel->markExpiredIfNeeded($sub);
app\\Filters\\SubscriptionFilter.php:48:                    $reason = 'NOT_STARTED';
```

### Schema proof (clinic_subscriptions columns)
Command:
`docker exec mysql8-dental mysql -uroot -proot -e "SHOW COLUMNS FROM clinic_subscriptions;" democa_dental_saas`

Output:
```
Field	Type	Null	Key	Default	Extra
id	int unsigned	NO	PRI	NULL	auto_increment
clinic_id	int unsigned	NO	MUL	NULL	
plan_id	int unsigned	NO	MUL	NULL	
status	enum('active','paused','canceled','expired')	NO		active	
start_at	datetime	YES		NULL	
end_at	datetime	YES		NULL	
canceled_at	datetime	YES		NULL	
created_at	datetime	YES		NULL	
updated_at	datetime	YES		NULL	
paused_at	datetime	YES		NULL	
paused_reason	varchar(120)	YES		NULL	
canceled_reason	varchar(120)	YES		NULL	
mysql: [Warning] Using a password on the command line interface can be insecure.
```
