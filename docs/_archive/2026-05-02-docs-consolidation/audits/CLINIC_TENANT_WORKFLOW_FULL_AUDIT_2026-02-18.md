# Clinic Tenant Workflow Audit (Deep Dive)

Date: 2026-02-18  
Codebase: `C:\xampp\htdocs\dental-saas`  
Framework: CodeIgniter 4 + Tailwind CSS + server-rendered PHP views + AJAX endpoints

---

## 1) Purpose and audit approach

This report is a full tenant-plane workflow audit intended for product simplification, UX acceleration, and enterprise-style wiring review.

The goal was to document:

- Every major clinic feature surface.
- How each feature is wired (routes -> controller -> model -> view -> side effects).
- Every create/edit page and field inventory.
- Cross-feature workflow continuity (especially return/back behavior).
- Plan flags, quotas, and tenant isolation controls.
- Comparison against common best-practice dental SaaS patterns for future enhancements.

Method used:

- Full route extraction from `app/Config/Routes.php`.
- Controller-level method mapping and validation extraction.
- Create/edit view field extraction (programmatic pass + manual verification).
- Core schema inspection from MySQL (`democa_dental_saas`) for tenant tables and financial/contract tables.
- Tenant/control-plane filter and RBAC deep read.
- Kiosk workflow trace including session state, locale, and signature flow.
- External benchmark references (vendor docs/help pages) for modern dental SaaS workflow patterns.

---

## 2) Executive summary (what this app is today)

This app is a broad all-in-one dental clinic tenant platform with:

- Clinical core: patients, examinations, odontogram, treatments, prescriptions, treatment contracts/signature.
- Front-desk core: appointments, kiosk check-in, patient lookup/update.
- Financial core: invoices/payments/refunds/voids/close-balance with multi-currency entry.
- Operations core: inventory + usage records.
- Governance core: RBAC permissions/roles, activity logs, feature/plan gates, control-plane separation.

### Current architecture quality

Strong points:

- Tenant isolation is enforced in multiple layers (`TenantFilter` + `TenantAwareModel` + membership checks).
- Control plane separation is explicit (`global_mode` + `ControlPlaneFilter`).
- Return navigation has been generalized in key create/edit flows (`return_to` sanitization).
- Finance ledger supports real-world corrections (partial payments, refunds, voids, write-off close).
- Kiosk now supports broader patient operations, not only same-day confirmation.

Weak points / complexity drivers:

- Very high feature density for a small clinic workflow; too many optional paths create cognitive load.
- Some modules still rely on long free-text forms where template/toggle choices would reduce friction.
- There are duplicated singular/plural route groups (`patient` + `patients`, etc.), increasing maintenance overhead.
- Some pages are not yet uniformly driven by a single reusable form component strategy.

Bottom line:

- The platform is powerful and increasingly enterprise-ready.
- For a small clinic, simplification should focus on faster defaults, guided steps, and reducing optional branches shown by default.

---

## 3) Frontend/stack and rendering model

- Backend framework: CodeIgniter 4 (`composer.json`).
- Frontend style system: Tailwind CSS (`package.json`, `tailwind.config.js`, built output in `public/assets/css/tailwind.css`).
- UI approach: server-rendered PHP views + selective AJAX (DataTables/JSON endpoints).
- Not a React SPA and not shadcn/ui.

Primary directories:

- Controllers: `app/Controllers`
- Views: `app/Views`
- Models: `app/Models`
- Filters: `app/Filters`
- Services: `app/Services`
- Routes: `app/Config/Routes.php`

---

## 4) Security, tenancy, and plane separation (critical wiring)

### 4.1 Tenant isolation (tenant plane)

Key files:

- `app/Filters/TenantFilter.php`
- `app/Models/TenantAwareModel.php`

Behavior:

- Requires `active_clinic_id` in session for tenant routes.
- Verifies user membership in `clinic_users` for the active clinic.
- Enforces role consistency by syncing `active_clinic_role_id` from DB.
- Returns JSON errors for AJAX/API contexts; redirects HTML requests appropriately.
- Denies tenant-plane web requests when `global_mode=true` (fail-closed behavior).

`TenantAwareModel` enforcement:

- Auto-applies `clinic_id` scope on find/update/delete through callbacks.
- Auto-injects `clinic_id` on insert (`beforeInsert`).
- Throws fail-closed errors if clinic context is missing.

### 4.2 Control plane airlock

Key file:

- `app/Filters/ControlPlaneFilter.php`

Behavior:

- Requires authenticated super admin.
- Requires `global_mode=true` session state.
- Clears tenant context keys while in control plane.
- Throws 404/deny if preconditions are not met.

### 4.3 API v1 behavior

- API routes under `/api/v1/*` use dedicated auth/tenant flow and permission checks.
- API behavior is JSON-oriented (no HTML redirects expected on API paths by design).

---

## 5) Full feature map (tenant plane)

### 5.1 Patient domain

Routes:

- `patient/*` and `patients/*` aliases in `app/Config/Routes.php`.

Core actions:

- List/search/statistics.
- Create/edit/show/delete.
- Attachments upload/delete.
- Related tabs in profile: examinations, treatments, appointments, prescriptions, finance, contracts, odontogram, attachments.

Controller:

- `app/Controllers/Patient.php`

Models involved:

- `PatientModel`, `FileAttachmentModel`, related summary models.

Key validations:

- Name, phone, DOB, gender required.
- Email optional but validated and checked for uniqueness per clinic.

Key side-effects:

- Activity log entries for lifecycle actions.
- Plan quota checks (patients count/storage).

### 5.2 Examination domain

Routes:

- `examination/*` and `examinations/*` aliases.

Core actions:

- Create/edit/show/complete/duplicate/print/delete.
- Calendar and calendar-events.
- Tooth condition updates.

Controller:

- `app/Controllers/Examination.php`

Models involved:

- `ExaminationModel`, `PatientModel`, `OdontogramModel`, `TreatmentModel`, `TreatmentContractModel`.

Key validations:

- Patient/date/type/chief complaint required.

Key side-effects:

- Activity logs.
- Links forward to treatments/contracts and returns to origin via `return_to`.

### 5.3 Treatment domain

Routes:

- `treatment/*` and `treatments/*` aliases.

Core actions:

- Create/edit/show/complete/print/delete.
- Data/statistics endpoints.
- Inventory usage linkage (manual/optional usage capture).

Controller:

- `app/Controllers/Treatment.php`

Models involved:

- `TreatmentModel`, `PatientModel`, `InventoryModel`, `InventoryUsageModel`.

Treatment type source:

- `app/Libraries/TreatmentCatalog.php`

Key validations:

- Patient/type/description/cost/start date/estimated duration.

Key side-effects:

- Activity log entries.
- Optional inventory usage records and stock deductions.

### 5.4 Appointment domain

Routes:

- `appointment/*` and `appointments/*` aliases.

Core actions:

- Create/edit/show/delete.
- Confirm/complete/cancel.
- Calendar view/events.
- Available timeslot JSON endpoint.

Controller:

- `app/Controllers/Appointment.php`

Models involved:

- `AppointmentModel`, `PatientModel`.

Key validations and guards:

- Date/time/duration/type required.
- Must be within working days/hours from clinic settings.
- Timeslot conflict check.

Key side-effects:

- Activity logs for status transitions.

### 5.5 Finance domain

Routes:

- `finance/*`.

Core actions:

- Create/edit/show/delete transactions.
- Invoice payment lifecycle actions:
  - mark paid
  - add payment
  - refund payment
  - void payment
  - close remaining balance
- Reporting and exports.

Controller:

- `app/Controllers/Finance.php`

Models involved:

- `FinanceModel`, `FinancePaymentModel`, `FileAttachmentModel`, `PatientModel`, `ExaminationModel`.

Key validations:

- Type, amount, payment method, service type, tax rules.
- Currency restricted to `ILS, USD, JOD, EUR, GBP`.
- Exchange rate required when currency != clinic base currency.
- Existing transaction type is immutable on edit.

Ledger behavior:

- `finances` stores invoice/transaction header.
- `finance_payments` stores payment/refund/void/adjustment events.
- Sync logic recalculates `amount_paid`, `balance_due`, and status.

### 5.6 Prescription domain

Routes:

- `prescription/*` and `prescriptions/*` aliases.

Core actions:

- Create/edit/show/print/delete.
- Statistics and DataTables endpoints.

Controller:

- `app/Controllers/Prescription.php`

Model:

- `PrescriptionModel`.

Data shape note:

- `medication_name` currently stores JSON array of medicines in create/edit flow.

### 5.7 Inventory domain

Routes:

- `inventory/*` (including usage and low-stock surfaces).

Core actions:

- Item CRUD.
- Quantity adjustments.
- Usage recording and usage history.
- Low stock / expired views.

Controller:

- `app/Controllers/Inventory.php`

Models:

- `InventoryModel`, `InventoryUsageModel`.

Key side-effects:

- Inventory usage inserts records and decrements stock quantities.
- Activity logs around item and usage actions.

### 5.8 Contracts and signatures

Routes:

- `contracts/*`.

Core actions:

- Create from patient + examination context.
- Edit/update while draft.
- Show/print.
- Patient signature capture.

Controller:

- `app/Controllers/TreatmentContracts.php`

Models:

- `TreatmentContractModel`
- `TreatmentContractItemModel`
- `TreatmentContractSignatureModel`
- `TreatmentContractOdontogramSnapshotModel`

Key behavior:

- Supports manual total override (not only auto sum).
- Signature requires signer name + signature data.
- Signed contracts lock edit/delete and generate stored document artifact.

### 5.9 Odontogram

Routes:

- `odontogram/*`.

Core actions:

- Patient odontogram view/list.
- Tooth update/reset.
- Export/print/PDF endpoints.

Controller:

- `app/Controllers/Odontogram.php`

Model:

- `OdontogramModel`.

Current UX direction:

- Supports single and multi-select flows, with modernized popup action cards.

### 5.10 Kiosk

Public routes:

- `kiosk/{clinic_uuid}/check-in`
- `reset`, `unlock`, `lookup`, `confirm`, `update-details`, `sign-contract`

Controller:

- `app/Controllers/Kiosk.php`

Key behavior:

- Plan feature gate: `kiosk.enabled`.
- PIN unlock session now lasts 7 days for clinic device usage.
- Patient lookup by phone includes patients without same-day appointments.
- Supports:
  - appointment confirmation,
  - patient contact updates,
  - contract signature flow.
- Dual language support in kiosk flow (Arabic/English), clinic-configurable default.

---

## 6) Full create/edit page inventory and fields

Source: extracted from `app/Views/*create.php` and `*edit.php` plus manual controller verification.

### 6.1 Patient

`app/Views/patient/create.php` fields:

- `return_to`
- `first_name`, `last_name`, `phone`, `date_of_birth`, `gender`, `email`
- `country`, `address`, `city`, `state`, `zip_code`
- `emergency_contact_name`, `emergency_contact_phone`
- `medical_history`, `allergies`
- `insurance_provider`, `insurance_number`
- `notes`

`app/Views/patient/edit.php` fields:

- `return_to`
- `first_name`, `last_name`, `email`, `phone`, `date_of_birth`, `gender`
- `address`, `city`, `state`, `zip_code`, `country`
- `emergency_contact_name`, `emergency_contact_phone`
- `medical_history`, `allergies`, `notes`

### 6.2 Examination

`app/Views/examination/create.php` core fields:

- `return_to`
- `patient_id`
- `examination_date`
- `examination_type`
- `chief_complaint`
- optional clinical narrative blocks (history, findings, diagnosis, notes)

`app/Views/examination/edit.php` fields:

- `return_to`, `patient_id`, `examination_date`, `examination_type`, `status`
- `chief_complaint`
- `history_of_present_illness`, `medical_history`, `dental_history`
- `clinical_findings`, `diagnosis`, `prognosis`, `recommendations`, `examination_notes`

### 6.3 Treatment

`app/Views/treatment/create.php` fields:

- `examination_id`, `return_to`, `patient_id`
- `treatment_type`
- `tooth_number`
- `start_date`
- `description`, `treatment_notes`
- `cost`, `estimated_duration`
- optional usage controls: `usage_enabled`, `usage_date`, usage items, `usage_notes`

`app/Views/treatment/edit.php` fields:

- same core fields as create, with status/update behavior and usage options.

### 6.4 Appointment

`app/Views/appointment/create.php`:

- `return_to`, `examination_id`, `patient_id`
- `appointment_type`, `appointment_date`, `appointment_time`, `duration`
- `notes`

`app/Views/appointment/edit.php`:

- `return_to`, `patient_id`, `appointment_type`
- `appointment_date`, `appointment_time`, `duration`, `status`, `notes`

### 6.5 Finance

`app/Views/finance/create.php` and `app/Views/finance/edit.php`:

- `return_to`, `patient_id`, `transaction_type`, `examination_id`
- `amount_original`, `currency`, `exchange_rate`
- `payment_method`, `check_scan`
- `paid_date`, `due_date`
- `service_type`, `service_details`
- `discount_amount`, `tax_percentage`
- `description`, `notes`

### 6.6 Prescription

`app/Views/prescription/create.php`:

- `return_to`, `patient_id`, `prescribed_date`, `expiry_date`, `instructions`
- dynamic medicines array UI

`app/Views/prescription/edit.php`:

- same + `status`

### 6.7 Inventory

`app/Views/inventory/create.php`:

- `item_name`, `category`, `description`
- `quantity`, `min_quantity`, `unit`, `unit_price`
- `supplier`, `purchase_date`, `expiry_date`, `location`, `notes`

`app/Views/inventory/edit.php`:

- same + `status`

### 6.8 Contracts

`app/Views/contracts/create.php`:

- hidden/snapshot fields: `odontogram_json`, `odontogram_svg`, `examination_id`
- financial/terms fields: `currency`, `total_cost`, `terms_conditions`, `payment_terms`, `duration_plan`, `notes`
- treatment items array fields (code, tooth, description, qty, price)
- signature path is on contract show (`signer_name`, `signature_data`)

### 6.9 Users

`app/Views/users/create.php`:

- `first_name`, `last_name`, `email`, `phone`
- `hire_date`
- `password`, `password_confirm`
- `role`, `department`, `status`

`app/Views/users/edit.php`:

- same fields; username/address removed from required UX flow (database keeps columns for compatibility).

---

## 7) Full data model map (core tenant entities)

Schema sampled from MySQL `democa_dental_saas`.

Core tables:

- `patients` (26 cols)
- `examinations` (20)
- `treatments` (22)
- `appointments` (14)
- `finances` (28)
- `finance_payments` (12)
- `prescriptions` (18)
- `inventory` (18)
- `inventory_usage` (10)
- `odontograms` (15)
- `treatment_contracts` (20)
- `treatment_contract_items` (11)
- `treatment_contract_signatures` (11)
- `file_attachments` (17)
- `notifications` (13)
- `settings` (8)

Membership / RBAC / SaaS:

- `clinic_users` (8)
- `roles` (12)
- `permissions` (10)
- `role_permissions`, `user_roles`, `user_permissions`
- `plans`, `clinic_subscriptions`, `plan_usage`, `plan_requests`, `plan_audits`

Material financial design:

- Header-transaction table (`finances`) + event-ledger table (`finance_payments`) is a strong implementation direction for invoice lifecycle corrections.

---

## 8) Workflow continuity and back-navigation behavior

Your requested UX principle was: user should return to where they came from after create/edit/cancel.

Current status:

Implemented broadly in these controllers:

- `Patient`
- `Examination`
- `Treatment`
- `Appointment`
- `Finance`
- `Prescription`

Pattern:

- `return_to` accepted from query/body.
- Sanitized via `sanitizeInternalReturnTo(...)` against allow-lists.
- Redirect goes to sanitized path if valid, else to module default.

Security note:

- This avoids open redirects while preserving workflow continuity.

Remaining consistency opportunities:

- Ensure every create/edit surface in less-used modules follows identical cancel/back helper conventions.
- Standardize back button label to `Back` + context-aware URL across all create/edit pages.

---

## 9) Settings and feature flags that shape tenant UX

Primary settings surfaces in `app/Controllers/Settings.php` and `app/Views/settings/index.php`:

System preferences:

- `timezone`
- `date_format`
- `time_format`
- `currency`
- `currency_position`
- `appointment_duration`
- `working_hours_start`
- `working_hours_end`
- working days list

Kiosk preferences:

- `kiosk_enabled`
- `kiosk_pin_hash`
- `kiosk_header`
- `kiosk_subheader`
- `kiosk_footer_note`
- `kiosk_default_language`

Plan-gated features (from plan JSON + guard usage):

- `kiosk.enabled`
- `contracts.enabled`
- `contracts.signature.enabled`
- `attachments.enabled`
- reporting and channel features

Limits examples:

- `users_max`
- `patients_active_max`
- storage-related limits

---

## 10) How modules talk to each other (interaction graph)

### 10.1 Patient as center node

Patient record is the central index for:

- appointments
- examinations
- treatments
- prescriptions
- finances
- contracts/signatures
- attachments
- odontogram data

### 10.2 Examination as clinical gateway

Examination can lead directly to:

- treatment creation
- follow-up appointment
- treatment contract creation
- odontogram condition updates

### 10.3 Treatment and inventory coupling

Treatment can optionally consume inventory via usage recording logic, which updates stock and usage history.

### 10.4 Finance and care context

Finance transactions can link to patient and optionally examination, enabling clinical-to-financial traceability.

### 10.5 Contract and signature chain

Contract includes structured items + odontogram snapshot + signature artifact, then locks status to preserve medico-legal trace.

---

## 11) UX complexity analysis (current)

### 11.1 Complexity hotspots

1. High branching in create pages.

- Multiple optional fields + optional contextual IDs + optional return logic + optional attachments can overwhelm reception workflows.

2. Mixed text-heavy and structured input patterns.

- Some modules use large narrative text where templates/quick picks could reduce time.

3. Similar concepts split across modules.

- Treatment appears as standalone module and also as part of exam/contract planning flow.

4. Advanced finance options exposed early.

- Exchange rate, tax, discount, due dates, check attachments are powerful but can be overkill for simple cash clinics unless progressively disclosed.

### 11.2 Simplification opportunities (without reducing capability)

- Progressive disclosure defaults:
  - show basic fields first,
  - hide advanced fields in expandable sections.
- Template-first data entry for common procedures/exam notes.
- Single dominant CTA per page and context-aware secondary actions.
- Standardized section headers and same visual rhythm across all create/edit pages.

---

## 12) Best-practice comparison (global dental SaaS patterns)

This section compares your current implementation to patterns seen in mature products.

### 12.1 Patterns observed in mature platforms

From vendor docs/help pages (Open Dental, Dentrix, Dentally, CareStack):

- Faster charting via reusable templates/panels, quick filters, and favorites.
- Tight charting -> treatment plan -> signature loop.
- In-app patient signature for plans and financial documents.
- Patient-facing workflows (kiosk/portal/forms/check-in) integrated with front desk.
- Scheduling deeply linked with working hours and availability conflict prevention.
- Financial workflows that support phased plans, payment plans, and lifecycle adjustments.

Sources used:

- Open Dental ODTouch and treatment plan signature docs.
- Dentrix feature pages on scheduling, treatment planner, and practice workflows.
- Dentally help docs on charting and treatment plan templates.
- CareStack feature pages on charting, treatment planning, kiosk, clinical notes, and patient workflows.

### 12.2 Current app vs market pattern

Strong alignment:

- Odontogram + treatment + contract signature exists.
- Kiosk now supports multiple patient actions.
- Finance supports correction events (refund/void/close).
- Appointment slot checks align with working-hour logic.

Gaps / next evolution:

- Faster structured charting templates for examinations/treatments can be expanded.
- More guided role-specific dashboards (reception vs doctor vs coordinator) to reduce perceived complexity.
- Optional wizard mode for small clinics to hide advanced features by default.

Inference note:

- The recommendation above is inferred from the patterns described in source documentation and your actual current code paths.

---

## 13) Persona-based test-drive walkthroughs

### 13.1 Receptionist (new patient -> booking -> payment)

1. Open `patients/create`.
2. Create patient with required demographics.
3. Redirect returns to prior list/context via `return_to`.
4. Open `appointment/create?patient_id={id}&return_to=patients`.
5. Select slot validated against working hours + collisions.
6. Confirm appointment from list or via kiosk confirmation.
7. Open `finance/create?patient_id={id}&return_to=patient/{id}`.
8. Record invoice or payment with chosen currency and optional exchange rate.

Expected system behavior:

- Audit logs created.
- Back navigation consistent.
- Patient profile tabs show linked records.

### 13.2 Dentist (exam -> charting -> treatment -> contract)

1. Open `examination/create` from patient context.
2. Enter structured diagnosis/findings.
3. Use odontogram for tooth-level conditions.
4. Create treatment from exam context with standardized treatment type catalog.
5. Build contract from exam, adjust total as needed, collect signature.

Expected system behavior:

- Contract locks after signature.
- Signed artifact stored and linked.
- Clinical records remain tenant-scoped.

### 13.3 Billing coordinator (invoice lifecycle edge cases)

Scenario example:

- Invoice 1000 -> payment 500 -> payment 500 -> refund 300 -> close remaining as adjustment/write-off.

Execution path:

- `Finance::addInvoicePayment`
- `Finance::refundInvoicePayment`
- `Finance::closeInvoiceBalance`

Expected:

- Header recalculates paid/balance/status.
- No destructive edits to original invoice intent required.

### 13.4 Kiosk patient (tablet)

1. Open public clinic kiosk URL.
2. Enter PIN (device stays unlocked for week policy).
3. Lookup by phone.
4. Confirm appointment if eligible.
5. Update phone/email if needed.
6. Sign pending contract if feature enabled.

Expected:

- Rate limits active.
- Plan features enforced.
- Session-allowed patient/appointment/contract IDs restrict misuse.

---

## 14) Current implementation quality by domain

### Patients

- Well implemented with broad data model and attachments.
- Complexity risk: too many non-essential fields visible by default for quick front desk intake.

### Examinations

- Clinically rich; good for detailed records.
- Complexity risk: long narrative blocks can be slower than template-driven capture for small clinics.

### Treatments

- Improved with standardized procedure catalog.
- Needs optional quick presets (common bundles by clinic).

### Appointments

- Solid schedule integrity checks.
- UX can be improved further with one-click suggested next slots.

### Finance

- Strong transactional correction model.
- Should keep advanced controls visually secondary in small-clinic mode.

### Contracts

- Good medico-legal chain (snapshot + signature hash + artifact).
- Minor UI polish is still possible for better contrast and form readability.

### Kiosk

- Major improvement: now multipurpose and bilingual-ready foundation.
- Future gain: stronger role-based kiosk workflow labels and clearer call-to-action hierarchy.

---

## 15) Potential enhancement backlog (prioritized for simplification)

### Priority A (high UX impact, low risk)

1. Introduce "quick mode" defaults on create forms.

- Show only core required fields initially.
- Expand advanced section on demand.

2. Add exam/treatment templates.

- Toggle chips and quick phrases for common procedures/notes.

3. Unify create/edit page shell component.

- Same spacing, card elevation, CTA placement, and back behavior everywhere.

### Priority B (medium impact)

1. Role-focused home views.

- Reception profile sees patient/appointment/payment-first quick actions.
- Dentist profile sees exam/odontogram/treatment-first actions.

2. Guided workflow action bar on patient profile.

- "New Exam", "New Appointment", "New Treatment", "New Invoice" in one place.

### Priority C (strategic)

1. Structured clinical form engine.

- Convert long text areas into configurable templates/checklists where practical.

2. Reporting simplification layer.

- Small clinic dashboard with only top 5 KPIs and one-click drilldowns.

---

## 16) Known technical risks / audit notes

1. Duplicate singular/plural route groups increase maintenance burden.
2. Some validation/storage logic still repeated across controllers (candidate for shared form services).
3. Inventory usage flow references assumptions about table readiness in legacy fallback messages.
4. `users` table still has legacy profile columns retained for compatibility (acceptable, but document as deprecated UI fields).

---

## 17) File-level index used for this report

Primary route and architecture files:

- `app/Config/Routes.php`
- `app/Filters/TenantFilter.php`
- `app/Filters/ControlPlaneFilter.php`
- `app/Models/TenantAwareModel.php`
- `app/Services/PlanGuard.php`
- `app/Services/PermissionService.php`

Tenant controllers reviewed:

- `app/Controllers/Patient.php`
- `app/Controllers/Examination.php`
- `app/Controllers/Treatment.php`
- `app/Controllers/Appointment.php`
- `app/Controllers/Finance.php`
- `app/Controllers/Prescription.php`
- `app/Controllers/Inventory.php`
- `app/Controllers/TreatmentContracts.php`
- `app/Controllers/Odontogram.php`
- `app/Controllers/Kiosk.php`
- `app/Controllers/Users.php`
- `app/Controllers/Settings.php`

Important supporting libraries/helpers:

- `app/Libraries/TreatmentCatalog.php`
- `app/Helpers/settings_helper.php`

Field extraction artifacts generated during audit:

- `tmp_form_fields_by_page.md`
- `tmp_form_fields.tsv`
- `tmp_routes.txt`
- `tmp_routes_create_edit_hits.txt`

---

## 18) External references used for best-practice comparison

Open Dental:

- https://opendental.com/site/odtouch.html
- https://www.opendental.com/site/0_odtouch.html
- https://www.opendental.com/manual/treatmentplansign.html

Dentrix:

- https://www.dentrix.com/
- https://www.dentrix.com/products/dentrix
- https://canada.dentrix.com/products/dentrix/features/practice-advisor.aspx

Dentally:

- https://help.dentally.com/en/articles/3565932-charting-overview
- https://help.dentally.com/en/articles/3565921-how-to-set-up-treatment-plan-templates
- https://help.dentally.com/en/articles/3563930-how-to-use-treatment-plan-templates
- https://help.dentally.com/en/articles/9271709-where-can-i-find-my-charting-settings

CareStack:

- https://carestack.com/dental-software/features
- https://carestack.com/dental-software/features/charting
- https://carestack.com/dental-software/features/treatment-planning
- https://carestack.com/dental-software
- https://carestack.com/dental-software/features/periodontal-charting

Note:

- These references were used to compare feature patterns and UX direction. Recommendations in this report are implementation-focused inferences relative to your current codebase.

---

## 19) Final audit conclusion

The app is functionally rich and technically maturing toward enterprise SaaS standards (tenant isolation, RBAC, plan gating, kiosk, finance ledger lifecycle). For a small dental clinic, perceived complexity comes mainly from the breadth of options shown early, not from core capability gaps.

Best next product direction for your stated goal (simpler app, fewer steps):

- Keep current capability.
- Reduce first-screen choices.
- Add template-driven fast entry in clinical forms.
- Continue enforcing context-aware return/back behavior.
- Keep advanced options hidden until explicitly needed.

That approach gives small-clinic simplicity without losing the architecture you already invested in.
