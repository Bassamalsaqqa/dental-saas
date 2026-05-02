# WHATSAPP-PHASE1-SCHEMA-FOUNDATION

Date: 2026-02-24
Status: PASS
Scope: Phase 1 schema/models foundation only (new tables, models, encrypted platform secrets model pattern)

## Summary

Implemented the Phase 1 foundational schema and model layer for WhatsApp architecture:
- Added six new tables
- Added six model classes
- Applied migrations successfully
- Verified audit-specific indexes/unique keys
- Refreshed `democa_dental_saas_schema_superadmin.sql`

## Files Added / Updated

### Migrations
- `app/Database/Migrations/2026-02-24-140000_CreateWhatsAppPhase1Tables.php`
- `app/Database/Migrations/2026-02-24-140100_FixWhatsAppOptoutsPatientForeignKey.php`

### Models
- `app/Models/NotificationAttemptModel.php`
- `app/Models/WhatsAppDeliveryEventModel.php`
- `app/Models/PatientWhatsAppModel.php`
- `app/Models/WhatsAppOptoutModel.php`
- `app/Models/WhatsAppTenantPolicyModel.php`
- `app/Models/PlatformSecretModel.php`

## Migration Execution

Commands:

```powershell
php spark migrate
php spark migrate
```

Notes:
- First run applied `CreateWhatsAppPhase1Tables`
- Second run applied FK correction migration `FixWhatsAppOptoutsPatientForeignKey`

## Created Tables (DB Verification)

Command:

```powershell
docker exec -i mysql8-dental mysql -uroot -proot democa_dental_saas -e "SHOW TABLES LIKE 'notification_attempts'; SHOW TABLES LIKE 'whatsapp_delivery_events'; SHOW TABLES LIKE 'patient_whatsapp'; SHOW TABLES LIKE 'whatsapp_optouts'; SHOW TABLES LIKE 'whatsapp_tenant_policies'; SHOW TABLES LIKE 'platform_secrets';"
```

Verified created:
1. `notification_attempts`
2. `whatsapp_delivery_events`
3. `patient_whatsapp`
4. `whatsapp_optouts`
5. `whatsapp_tenant_policies`
6. `platform_secrets`

## Audit-Specific Schema Notes (Required)

### 1) `whatsapp_delivery_events` idempotency unique key

Verified via `SHOW CREATE TABLE whatsapp_delivery_events`:
- Unique key on `(provider_event_id, event_type)`

### 2) `whatsapp_optouts` BSUID index

Verified via `SHOW CREATE TABLE whatsapp_optouts`:
- Named index `idx_bsuid` on `(clinic_id, bsuid)`

### 3) `platform_secrets` encryption pattern alignment

Implemented `PlatformSecretModel` using the same pattern as `ClinicNotificationChannelModel`:
- Encrypt with `\Config\Services::encrypter()->encrypt(...)`
- Store as `base64_encode(...)` in DB (`secret_encrypted`)
- Read with `base64_decode(...)` + `decrypt(...)`

Static evidence command:

```powershell
rg -n "setSecret\(|getSecret\(|encrypt\(|decrypt\(" app/Models/PlatformSecretModel.php app/Models/ClinicNotificationChannelModel.php
```

## FK Correction Note

The initial Phase 1 migration created the `whatsapp_optouts.patient_id` FK with reversed update/delete actions due CI4 `addForeignKey()` parameter ordering.

Fix applied immediately in:
- `app/Database/Migrations/2026-02-24-140100_FixWhatsAppOptoutsPatientForeignKey.php`

Final verified behavior (`SHOW CREATE TABLE whatsapp_optouts`):
- `ON DELETE SET NULL`
- `ON UPDATE CASCADE`

## SQL Dump Refresh

Command:

```powershell
cmd /c "docker exec mysql8-dental mysqldump -u root -proot --default-character-set=utf8mb4 --routines --triggers --events --single-transaction --skip-lock-tables --set-charset --no-create-db democa_dental_saas --ignore-table=democa_dental_saas.control_plane_invoices --ignore-table=democa_dental_saas.control_plane_invoice_payments > democa_dental_saas_schema_superadmin.sql"
```

Verification commands:

```powershell
rg -n 'CREATE TABLE `notification_attempts`|CREATE TABLE `whatsapp_delivery_events`|CREATE TABLE `patient_whatsapp`|CREATE TABLE `whatsapp_optouts`|CREATE TABLE `whatsapp_tenant_policies`|CREATE TABLE `platform_secrets`' democa_dental_saas_schema_superadmin.sql
rg -n 'UNIQUE KEY `provider_event_id_event_type`|KEY `idx_bsuid`|CREATE TABLE `platform_secrets`|`secret_encrypted`' democa_dental_saas_schema_superadmin.sql
```

Observed evidence highlights:
- Six table definitions present in dump
- `UNIQUE KEY provider_event_id_event_type` present
- `KEY idx_bsuid` present
- `platform_secrets.secret_encrypted` present

## Phase 1 Foundation Boundaries

This verification covers schema/models only.
Out of scope for this change set:
- Unified dispatcher (`DispatchService`)
- `EmailDriver` / `WhatsAppDriver`
- WhatsApp outbound send path
- Webhook routes/controller/signature verification
- UI surfaces and observability dashboards
