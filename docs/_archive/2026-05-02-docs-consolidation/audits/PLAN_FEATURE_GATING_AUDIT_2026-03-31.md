# Plan Feature Gating Audit - 2026-03-31

## Purpose

Re-audit the app's plan feature system to answer four questions:

1. What feature flags exist today in plan configuration?
2. Which ones are actually enforced at runtime?
3. Is control-plane plan management wired correctly?
4. What should be added next, especially for finance reporting?

This document is intentionally split into:

- verified local codebase facts
- recommended design
- external SaaS / dental software patterns

It is not an implementation spec yet.

---

## Executive Summary

The current plan feature system is **partially real**:

- **Control-plane CRUD is real**: plans store `features_json` and `limits_json`, and the plan form exposes a feature matrix.
- **Runtime enforcement is partial**: some nested features are truly enforced, but multiple top-level toggles are currently only commercial metadata and do not block access.
- **Current strong feature gates**:
  - `attachments.enabled`
  - `contracts.enabled`
  - `contracts.signature.enabled`
  - `kiosk.enabled`
  - `notifications.whatsapp.enabled`
  - `exports.enabled` (artifact/export storage path only)
- **Current weak or not-enforced feature flags**:
  - `appointments`
  - `finance`
  - `inventory`
  - `reports`
  - `email_templates`
  - `audit_exports`
  - `chairside_notes`
  - `claims_hub`

### Main conclusion

If we want `financial reports` behind a plan feature, we should not keep extending the current mixed model casually.

We should:

1. keep existing working feature gates
2. classify all plan flags as either:
   - enforced entitlement
   - not yet enforced / roadmap only
3. add finance reporting as a **real enforced entitlement**
4. then decide which other modules deserve the same treatment

---

## Verified Local Architecture

### Control-plane plan storage

Verified in:

- `app/Controllers/ControlPlane/Plans.php`
- `app/Views/control_plane/plan_form.php`
- `app/Models/PlanModel.php`
- `app/Database/Migrations/2026-01-15-213930_CreatePlansAndSubscriptionsTables.php`

The control plane currently:

- stores plan features in `plans.features_json`
- stores plan limits in `plans.limits_json`
- exposes plan toggles in the plan form
- persists them correctly on create/update

### Runtime enforcement service

Verified in:

- `app/Services/PlanGuard.php`

`PlanGuard` is the runtime authority for:

- subscription standing
- feature entitlement checks
- quota checks
- plan audits

Core methods:

- `assertFeature($clinicId, $featureKey, $meta = [])`
- `assertQuota($clinicId, $metricKey, $delta = 1, $meta = [])`
- `isFeatureEnabled($clinicId, $featureKey, $default = false)`
- `getLimitValue($clinicId, $metricKey, $default = null)`

Important behavior:

- fail-closed on web routes via `PageNotFoundException`
- API scope can return structured `feature_disabled` / quota responses
- nested feature keys are supported via dot notation

### Current default feature map

Verified in:

- `app/Controllers/ControlPlane/Plans.php`

Current default features:

```php
[
  'appointments' => true,
  'finance' => true,
  'inventory' => true,
  'reports' => true,
  'email_templates' => true,
  'audit_exports' => false,
  'chairside_notes' => false,
  'claims_hub' => false,
  'attachments' => ['enabled' => false],
  'contracts' => [
    'enabled' => false,
    'signature' => ['enabled' => false],
  ],
  'kiosk' => ['enabled' => false],
  'exports' => ['enabled' => false],
  'notifications' => [
    'email' => ['enabled' => true],
    'sms' => ['enabled' => false],
    'whatsapp' => ['enabled' => false],
  ],
]
```

---

## Verified Current Gate Matrix

### A) Properly wired and enforced

These features are both exposed in plans and actually checked at runtime.

| Feature key | Control-plane plan form | Runtime enforcement | Notes |
|---|---|---|---|
| `attachments.enabled` | Yes | Yes | Patient attachments gated in `Patient.php` and `StorageService.php`. |
| `contracts.enabled` | Yes | Yes | Treatment contract flows and kiosk contract flows are gated. |
| `contracts.signature.enabled` | Yes | Yes | Signature-specific contract actions are gated. |
| `kiosk.enabled` | Yes | Yes | Kiosk routes and kiosk settings save path are gated. |
| `notifications.whatsapp.enabled` | Yes | Yes | WhatsApp dispatch, readiness checks, and workflow usage are gated. |
| `notifications.email.enabled` | Yes | Partially yes | Dispatch path checks it; surface-level hiding is not comprehensive. |
| `notifications.sms.enabled` | Yes | Partially yes | Dispatch path checks it; actual product depth is thinner than WhatsApp. |
| `exports.enabled` | Yes | Partial but real | Export artifact storage is gated in `StorageService::storeExport()`, but not every reporting surface is hidden proactively. |

### B) Exposed in plans, but not truly enforced

These toggles exist in control-plane plan management but do not currently act as strong runtime entitlements.

| Feature key | Exposed in plan form | Runtime entitlement today | Assessment |
|---|---|---|---|
| `appointments` | Yes | No verified plan guard on appointment module entry/routes | Weak / metadata only |
| `finance` | Yes | No verified plan guard on finance module routes | Weak / metadata only |
| `inventory` | Yes | No verified plan guard on inventory module routes | Weak / metadata only |
| `reports` | Yes | No verified plan guard on reports surfaces | Weak / metadata only |
| `email_templates` | Yes | No verified plan guard | Weak / metadata only |
| `audit_exports` | Yes | No verified plan guard | Weak / metadata only |
| `chairside_notes` | Yes | No verified plan guard | Weak / roadmap only |
| `claims_hub` | Yes | No verified plan guard | Weak / roadmap only |

### C) Limits that are actually used

Verified in `PlanGuard.php`, `StorageService.php`, and current SaaS logs.

| Limit key | Runtime use |
|---|---|
| `patients_active_max` | Enforced |
| `users_max` | Enforced |
| `storage_gb` | Enforced |
| `attachments_max_file_mb` | Enforced |
| `exports` | Enforced by export artifact path |
| `notifications_email` | Enforced as monthly usage quota path |

---

## Is Control Plane Properly Wired?

### Yes, for persistence

The control plane is properly wired for:

- creating/editing plans
- saving `features_json`
- saving `limits_json`
- showing feature toggles in the plan form
- reading plan JSON back into edit mode

### No, not fully, for product truth

The control plane currently gives the impression that all listed feature toggles are commercial entitlements. That is not yet true.

This is the important mismatch:

- the plan UI suggests feature-gated modules
- the tenant runtime only enforces some of them

That means the control-plane plan UI is **operationally wired** but **commercially overstated**.

### Recommended interpretation

The control plane is working mechanically.

The product contract is only partially complete.

---

## Finance Reporting: Should It Be Behind a Feature Flag?

### Yes

This is a reasonable feature-gate candidate.

Why:

- reporting is a common tier differentiator in SaaS
- finance reporting is higher-value than base transaction entry
- it is operationally safe to separate:
  - finance ledger usage
  - advanced finance reporting / analytics

### Recommended model

Use a **nested entitlement**, not another loose top-level boolean.

Recommended key:

- `reports.finance.enabled`

Rationale:

- finance reporting is a reporting entitlement, not the whole finance module
- it fits future growth cleanly:
  - `reports.finance.enabled`
  - `reports.operational.enabled`
  - `reports.executive.enabled`

Alternative acceptable key:

- `finance.reports.enabled`

But `reports.finance.enabled` is cleaner if reporting is treated as a product family.

### Recommended scope of the gate

When disabled:

- block `/finance/reports`
- block any direct UI links to finance reports
- block finance-report-specific exports if they depend on that surface
- optionally redirect `/reports?report_type=finance` to a controlled message or deny entirely

When enabled:

- allow the full treasury-aware finance reporting workspace

### Important product rule

Do **not** gate the ability to:

- create invoices
- record payments
- create expenses
- record settlements
- access finance detail pages

unless the commercial model explicitly makes finance itself premium.

Best practice here is:

- base finance operations stay available
- advanced finance reporting is premium

---

## What Else Could Be Behind a Feature Flag?

This section combines:

- current app realities
- common SaaS packaging logic
- what is reasonable for a clinic/dental product

### Strong candidates for plan gating

#### 1) Finance reporting and analytics

Candidate keys:

- `reports.finance.enabled`
- `reports.executive.enabled`
- `reports.treasury.enabled`

Why:

- advanced reporting is commonly tiered
- does not block day-to-day operational use

#### 2) Patient attachments / document storage

Already partially done:

- `attachments.enabled`
- plus size/storage limits

This is a good feature to keep gated because document-heavy clinics drive real storage cost.

#### 3) Treatment contracts and e-signatures

Already correctly modeled:

- `contracts.enabled`
- `contracts.signature.enabled`

This is commercially sound and matches real dental workflow segmentation.

#### 4) Kiosk / self check-in / digital forms

Already partly modeled via:

- `kiosk.enabled`

This is also a strong entitlement because it affects patient-facing workflow and hardware/tablet usage.

#### 5) WhatsApp / premium patient communications

Already correctly modeled:

- `notifications.whatsapp.enabled`

This matches real SaaS packaging very well.

#### 6) Export tools

Already partly modeled:

- `exports.enabled`

This is a sensible premium entitlement, especially for:

- branded exports
- accountant-grade exports
- high-volume artifact generation

#### 7) Inventory management

Candidate key:

- `inventory.enabled`

Unlike finance reports, this is a full module decision.
If inventory is not intended to be core for all clinics, this is a valid premium gate.

#### 8) Insurance / claims workflows

Candidate key:

- `claims_hub.enabled`

This is one of the strongest premium-module candidates in dental SaaS because claims features are operationally complex and commercially valuable.

#### 9) Advanced communications

Candidates:

- `notifications.sms.enabled`
- `notifications.email.enabled`
- `communications.two_way.enabled`
- `communications.automation.enabled`

This should remain layered, because not all clinics need premium automation depth.

#### 10) Digital forms / patient portal / online intake

Candidate keys:

- `forms.digital.enabled`
- `portal.patient.enabled`
- `booking.online.enabled`

These are common tier separators in clinic SaaS.

---

## What Should Probably Stay Core

These should usually not be feature-gated if the product is marketed as a clinic management system:

- patient records
- appointments
- examinations
- treatments
- prescriptions
- basic finance transaction entry
- basic patient search and history

These are system fundamentals, not premium luxuries.

If you gate them, you are no longer selling tiers of one product; you are selling fundamentally different products.

That can be valid, but it should be a deliberate commercial strategy.

---

## External Product Pattern Scan

The current market pattern is consistent:

- patient communications, reminders, and online booking are often sold as enhanced/premium workflows
- digital forms and kiosk/check-in are commonly premium or add-on
- advanced analytics/reporting is often positioned as a premium capability
- e-signatures, eRx, insurance claims, and revenue-cycle tooling are often premium or service-linked

### Relevant examples

#### CareStack

CareStack publicly positions these as differentiated capability areas:

- Patient Kiosk
- Online forms
- Appointment reminders
- Patient portal
- Revenue Cycle Management
- Analytics and Reporting
- Treatment planning with e-signatures

Source:

- https://carestack.com/
- https://carestack.com/dental-software/features/patient-kiosk

#### Dentrix

Dentrix markets:

- Patient Engage / online booking / reminders / forms / communications
- eClaims
- ePrescribe / clinical technology add-ons

These are not presented as bare-core charting only; they are packaged capability layers.

Source:

- https://www.dentrix.com/products/eservices/patient-engage?online-booking=true
- https://www.dentrix.com/products/eservices/eclaims/
- https://www.dentrix.com/products/eservices/eprescribe

#### Open Dental

Open Dental documents separate feature/service layers around:

- eForms / digital forms
- eClipboard / web forms
- eRx services with extra operational requirements and pricing

This strongly supports gating:

- digital intake
- patient-facing forms
- electronic prescribing

Source:

- https://www.opendental.com/site/featuredigitalforms.html
- https://www.opendental.com/site/featureeforms.html
- https://www.opendental.com/site/dosespot.html
- https://www.opendental.com/site/electronicrx.html

### Professional takeaway

The cleanest commercial split in clinic SaaS is usually:

- core clinical operations
- communications add-ons
- forms / kiosk / portal add-ons
- claims / eRx / signature add-ons
- advanced reporting / analytics add-ons

That pattern fits this app well.

---

## Recommended Feature Flag Taxonomy

### Keep existing working flags

Keep:

- `attachments.enabled`
- `contracts.enabled`
- `contracts.signature.enabled`
- `kiosk.enabled`
- `exports.enabled`
- `notifications.email.enabled`
- `notifications.sms.enabled`
- `notifications.whatsapp.enabled`

### Normalize module flags

Convert the vague top-level booleans into explicit nested entitlements over time:

```json
{
  "modules": {
    "appointments": { "enabled": true },
    "finance": { "enabled": true },
    "inventory": { "enabled": true },
    "reports": { "enabled": true }
  },
  "reports": {
    "finance": { "enabled": true },
    "executive": { "enabled": false }
  },
  "forms": {
    "digital": { "enabled": false }
  },
  "portal": {
    "patient": { "enabled": false }
  },
  "communications": {
    "automation": { "enabled": false }
  }
}
```

This is better than a flat list because:

- it scales
- it is easier to reason about
- it separates modules from subfeatures

---

## Immediate Recommendation for This App

### Do now

1. Treat the current matrix as two classes:
   - enforced features
   - roadmap/non-enforced plan metadata

2. Add a real gate for finance reporting:
   - `reports.finance.enabled`

3. Update control-plane plan UI wording so it stops overstating non-enforced flags.

4. Decide which of these become real next:
   - `inventory.enabled`
   - `reports.enabled`
   - `claims_hub.enabled`
   - `forms.digital.enabled`

### Do not do yet

Do not immediately gate all of:

- appointments
- patient records
- treatments
- prescriptions

That would be commercially disruptive and operationally risky.

---

## Recommended Verification Matrix for Future Implementation

When any new feature gate is added, verify all of these:

1. Control plane

- plan form shows the toggle
- create plan saves it
- edit plan persists it
- plan list reflects it correctly

2. Tenant UI

- nav link hidden or disabled
- index page inaccessible when disabled
- deep links blocked
- AJAX endpoints blocked

3. Service / backend

- controller action checks `PlanGuard`
- background jobs or dispatch paths check `PlanGuard` if they can operate without UI

4. API

- if exposed via API, returns proper `feature_disabled`

5. Audit

- `PlanGuard` writes audit entry on blocked access

---

## Concrete Recommendation on the User's Specific Request

### Answer: yes, finance reports should be behind a feature flag

Recommended entitlement:

- `reports.finance.enabled`

Recommended behavior:

- hide or disable finance report entry points when off
- hard-block `/finance/reports`
- hard-block finance-report-specific exports
- keep basic finance operations available

### Answer: are current plan features properly wired?

Partially.

- control plane persistence: yes
- runtime entitlement truth: only for some features

### Answer: what else could be feature-gated?

Best candidates:

- finance reporting
- inventory module
- digital forms / kiosk
- patient portal / online booking
- claims / insurance workflows
- e-signature workflows
- advanced communications automation
- advanced exports / analytics

---

## Local Evidence References

Primary local files reviewed:

- `app/Controllers/ControlPlane/Plans.php`
- `app/Views/control_plane/plan_form.php`
- `app/Services/PlanGuard.php`
- `app/Controllers/Settings.php`
- `app/Controllers/Patient.php`
- `app/Services/StorageService.php`
- `app/Controllers/Kiosk.php`
- `app/Controllers/TreatmentContracts.php`
- `app/Services/DispatchService.php`
- `app/Services/NotificationService.php`
- `app/Services/WhatsAppWorkflowService.php`
- `app/Services/ControlPlaneWhatsAppTestSendService.php`

External references used:

- CareStack:
  - https://carestack.com/
  - https://carestack.com/dental-software/features/patient-kiosk
- Dentrix:
  - https://www.dentrix.com/products/eservices/patient-engage?online-booking=true
  - https://www.dentrix.com/products/eservices/eclaims/
  - https://www.dentrix.com/products/eservices/eprescribe
- Open Dental:
  - https://www.opendental.com/site/featuredigitalforms.html
  - https://www.opendental.com/site/featureeforms.html
  - https://www.opendental.com/site/dosespot.html
  - https://www.opendental.com/site/electronicrx.html

---

## Final Recommendation

The app should stop pretending every checkbox in plans is already a real entitlement.

The clean next move is:

1. audit and classify current flags
2. add `reports.finance.enabled` as a real gate
3. then convert the next few premium modules into real gated products one by one

That is the safest and most professional way to mature the SaaS commercial model.
