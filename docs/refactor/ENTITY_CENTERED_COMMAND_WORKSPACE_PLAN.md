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

## Entity Detail Page Visual Contract

Entity detail pages should use the same work style established on the treatment detail page:

- Keep the existing app hero/header card as the stable identity anchor.
- Place the high-level operational snapshot directly below the hero.
- Use strong primary color surfaces for major summary/status cards, not only pale secondary tints.
- Use color intentionally by domain: clinical/work context, patient context, billing/money trail, inventory/material cost, and timeline should be visually distinguishable.
- Prefer a readable detail-page structure over many peer cards:
  - primary work/content column for the entity record itself
  - contextual sidebar for patient/user/billing/timeline/linked-record context
  - sticky sidebar on desktop only when it helps orientation
- Do not make every card visually equal. Section headers, card size, placement, and color should tell the user what matters first.
- Avoid dark nested cards inside dark parent cards; preserve readable contrast for English and Arabic text.
- Keep canonical actions in context with drawers/popups where already available, but preserve full-page fallbacks.
- Do not change controller contracts, tenant checks, permissions, workflow redirects, or finance calculations for visual redesign work.
- Use the app fonts, existing icons, translation keys, `esc()`, permission checks, and workflow navigation helpers.

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
