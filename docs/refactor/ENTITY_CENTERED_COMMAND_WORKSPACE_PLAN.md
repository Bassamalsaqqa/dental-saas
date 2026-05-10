# Entity-Centered Command Workspace Plan

Status: current-PHP implementation plan and first patient slice.

## Decision

The current CodeIgniter frontend should support contextual patient actions from the Patient Profile, but the canonical modules remain the owners of the real workflows.

The first slice is patient-only and uses existing web/session routes:

- `examination/store`
- `appointment/store`
- `treatment/store`
- `finance/store`
- `patient/{id}/attachments/upload`

This avoids a parallel workflow engine and keeps CSRF, tenant checks, permissions, activity logging, and validation in existing controllers.

## Implementation Shape

- Patient Profile gets a compact `Patient actions` workspace.
- Quick-create surfaces are drawers, not random modals.
- Drawers lock the current patient with a visible context badge.
- Drawers submit normal forms to existing canonical routes.
- Full module links remain available for advanced workflows.

## First Slice

Implemented drawers:

- Add examination
- New appointment
- Add treatment
- Record payment
- Add patient charge
- Add document, when attachments are enabled

Non-goals:

- No new App API route.
- No `/api/v1/*` use.
- No React/Lovable change.
- No frontend finance calculations.
- No provider payout, supplier bill, COGS, reconciliation, or fake accounting logic in Patient Profile.
- No generic entity framework until the patient pattern proves useful.

## Verification

Required checks after changes:

- Patient page loads in English and Arabic.
- Drawer context badges must be RTL-aware as a full unit: icon, title, patient name, patient number, phone, and helper copy should mirror cleanly in Arabic. Patient numbers and phone values stay `dir="ltr"` with isolated bidi handling.
- Do not duplicate patient-context helper copy in drawer headers or patient badges. The focused patient card is the context indicator; avoid text such as "This action is linked to the current patient" when the card is present.
- Patient context badges should reuse patient visual tone when available, including male/female color continuity, while preserving readable neutral defaults.
- Select2 fields must reserve enough inline space for the dropdown arrow and clear button in both LTR and RTL. Do not mix physical `left`/`right` with the wrong logical `inset-inline-*`; short values such as `ILS` and Arabic placeholders must never sit under the arrow.
- Drawer Select2 dropdowns must be parented/stacked above the drawer body so menus never render under later helper text or fields.
- Drawer opens by button and closes by close button, Escape, and backdrop.
- Focus enters the drawer and returns to the trigger.
- Examination, appointment, treatment, finance, and attachment submissions route through existing controllers.
- Existing full-page create flows still work.
- Existing tenant and permission filters remain unchanged.
