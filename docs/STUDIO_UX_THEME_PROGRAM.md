# Studio workflow and theme guidance

The 2026-10-28/29 development campaign shipped with PR #22. Current release and activation status are in [PROJECT_STATE](PROJECT_STATE.md); remaining maintenance work is in [WORK_PROGRAM](WORK_PROGRAM.md). This document is durable journey guidance, not a second task register or deployment authorization.

Use native CI4/PHP/JS Studio components, semantic tokens, readable operational text, keyboard focus and EN/AR RTL. Reuse canonical handlers, shared forms and explicit return context. Short contextual tasks may use dialogs; complex clinical, financial and management tasks keep an appropriate full-page path. Never invent metrics, policy, availability, automatic billing or fake actions to fill a design.

## Journey contracts

These are design/acceptance maps, not claims that features below are newly implemented. Each action must pass existing routepermissions/features and keep canonical objectIDs and return context. Disabled or missing capabilities remain explained and unavailable.

| Journey / entry | Required facts / next permitted action | Success / cancel / failure authority |
|---|---|---|
| Intake: search or patient/create | Explicit identity/duplicatecheck; retain unassessed warning; register then choose book/openprofile | Existing patientcontroller validation; real returntarget; no inferred clinicalnegative. |
| Reception: dayboard/calendar | Actual appointment/provider/stage; patientpeek then supportedarrival/handoff | Existing transitions/conflicts; preserve selectedday/filter; no inventedavailability. |
| Clinical: patient/visit/examination | Real patientalerts/encounter/chart; draft then explicitreview/sign | Existing examinationgovernance; signedimmutable; failure retains input and truthfuldurablestate. |
| Plan: clinicalfinding/contracts | Suggestions distinct from persistedpriceditems; explicit edits/removals | Existing draftrevision/locking; signed evidence preserved; noautocharges. |
| Checkout: appointmentreview | Canonicalclinicalwork/charge/invoice/currency facts | Existing gatedcheckout/settlement only; never automatemoney or acknowledgments. |
| Finance: ledger/detail/payout | Sourceinvoice/payment/moneytrail; allowedrecord/refund/payout | Existingservercalculations; retainlistfilters/recordcontext; nofalse0balance. |
| Inventory: item/movement/reorder | Authoritativequantity/batch/expiry; permittedexplicitmovement | Existingstockservices; reason/audit; nofake supplierpayableintegration. |
| Team: users/providers/settings | Canonical membership/provideridentity/hours | ExistingRBAC/doctorcontracts; do not activate dynamicscheduling. |
| Platform: gateway/clinicinspector | Genuineglobalrole; explicitenter/impersonate/exit | ExistingCSRFPOST/session/audit; unoverrideablebanner; clinic-switch differs from exitingimpersonation. |
| Exceptions | Denied/stale/empty/sessionexpired/missingrecord facts | Honest reason and existingretry/safeexit; noforgedsuccess or placeholder#actions. |

## Themes and acceptance

Use the current implementation and [UI_STUDIO_COMPONENTS](UI_STUDIO_COMPONENTS.md) for component architecture. Theme resolution must preserve platform/tenant separation, validated values, version/conflict protection and safe fallback when disabled or unavailable. Do not activate theme storage or publish presets based on this historical plan. Full dark/contrast, print, physical-device and assistive-technology acceptance are separate checks.

Retained [product UX evidence](ui-evidence/product-ux/README.md) records measured scopes and limitations. Full development chronology, provisional designs, earlier review states and detailed original theme proposals are preserved at `5c047911e49f04d4286f2ea8eaa7a0391dad9014:docs/STUDIO_UX_THEME_PROGRAM.md` in Git history. Earlier pending-release statements are superseded by the deployed release identity.
