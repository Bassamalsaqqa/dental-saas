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
- Drawer opens by button and closes by close button, Escape, and backdrop.
- Focus enters the drawer and returns to the trigger.
- Examination, appointment, treatment, finance, and attachment submissions route through existing controllers.
- Existing full-page create flows still work.
- Existing tenant and permission filters remain unchanged.
