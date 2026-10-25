# V4 delivery ledger

Status: IN DEVELOPMENT. Branch feature/denta-v4-rebuild from protected 89352fd20e0d5166e81f631cb743bead1f596a68 in the existing isolated worktree. V3 a09a933 and gallery preserved; PR21 remains draft. No production access or deployment.

## Direction exploration
1. Reception desk: grouped readable navigation; broad day agenda; compact supporting work queue; recent patients as human-readable records. Strongest general clinic task entry; Arabic mobile becomes a single agenda with task access. Selected.
2. Chairside canvas: persistent patient identity and chart centered with contextual record/history dock. Strongest patient detail composition; adopted inside patient workspace, not as a dashboard.
3. Calendar cockpit: time grid dominates with an appointment inspector. Appropriate for calendar/reception routes, but rejected as the universal landing page because an empty schedule becomes a dead screen.

One coherent warm Clinic Desk system joins these task-specific compositions. Depth comes from differentiated surfaces, a layered forest navigation frame, a generous readable type scale and controlled directional elevation, not repetitive metric tiles or decorative gradients. No V3 CSS layers are loaded.

## Research and adopted patterns
- https://hsps.pro/DentrixAscend/Help/handling_new-patient_visits.htm : appointment context and patient selection before booking; retain current Denta+ patient/visit contracts, not competitor procedure automation.
- https://carestack-uk.zendesk.com/hc/en-us/articles/15260383434524-Options-on-the-Patient-Overview-Page : patient identity, medical alerts, clinical navigation and actionable next steps stay together; unsupported competitor capabilities omitted. Public documentation inspected; some hosted image links inaccessible.
- https://linear.app/changelog/2024-03-20-new-linear-ui : purposeful sidebar groups and consistent current-view/actions hierarchy; reject its unrelated palette and software-project semantics.
- https://www.opendental.com/manual/appointments.html : distinct schedule/agenda context and explicit visit actions; do not conflate operating doctors with rooms or activate scheduling.
- https://www.w3.org/TR/WCAG22/ : contrast, reflow, focus and target size. 44px is our core-touch goal, not a false claim of AA's minimum.
- Mobbin public homepage inspected; no connected Mobbin tool/private authenticated screen access. No inaccessible UI inspection claimed.
- Skills: frontend-design and Impeccable. codeigniter-page-upgrade safety constraints inspected; its refinement-only visual restrictions are superseded by the explicit V4 rebuild. The Owner brief supplies product/visual choices, so redundant interviews and automatic subagent handoffs are not used.

## Preservation map
Canonical backend and schema remain the production-derived baseline. V3 presentation is rejected, not cherry-picked as a design skeleton. Reusable operational fixes (early CSRF bootstrapping, route-scoped assets and safe focus/dirty-state handling) are re-established with their original contracts. Existing clinical/financial print and native odontogram source remain unchanged. Existing medical alert booleans cannot prove assessment: do not introduce Unknown/No/Yes persistence without domain authorization.

## Route coverage
The full local route/contract inventory is retained in the private v4/route-ledger.json. Nothing is labelled delivered based on a render alone. Shared shell, dashboard, patient registry/intake, chairside and reception are the initial implementation gate. All remaining modules are NOT_STARTED until their individual composition and interactions are accepted.

## Resumable implementation checkpoint ? 2026-10-25
The shared clinic shell, clinic desk, patient directory/intake, patient chart context, reception, calendar, finance ledger/quick entry and inventory registry have new compositions. They remain PARTIAL: secondary clinical/history tables, full edit/detail flows, notifications, shared drawers, inventory creation, all remaining modules, kiosk, Control Plane and auth are not yet visually accepted. No final review requested; no release candidate or deployment readiness claimed.

Focused actual-browser acceptance: 25 checks pass with no page errors: canonical directory search, desktop/mobile record parity, intake focus/background isolation/dirty cancellation/focus restoration, exactly one calendar clear refetch, keyboard visit inspector and return focus, nested Select2 Escape, session patient lookup without Bearer leakage, native 32-tooth chart, history tabs, mobile navigation/actions and AJAX CSRF rejection. Finance and inventory EN/AR desktop/mobile render without page errors. Sixteen additional focused checks pass for canonical formatted totals/counts, same-response mobile records, payment preset and CSRF, modal isolation, Bearer-only denial (401), documented Control Plane denial (404) and unauthenticated registry denial. Existing financial/stock SQL suites were not rerun or relabeled as current transactional acceptance. No records were created by this pass.

The initial asset-helper, modal-close, calendar duplicate-request and finance DataTables asset-ownership defects were corrected from observed browser failures. Permission-result reuse is presentation-only within the CI view render and keyed by actor/clinic/plane context; route filters and financial/clinical services are unchanged. Canonical finance formatted totals replace abbreviations, failed loads display unavailable rather than fabricated zero, and invoice face amounts no longer use a cash-direction arrow.

Preservation: 79 original checkout/reference hashes and 1,021 protected backend/config/native files match their manifests. Native patient chart JavaScript from `patientShowI18n` onward remains byte-identical. No schema, dependency, backend, permission, durable-file or feature-switch changes. Remote release remains 89352fd20e0d5166e81f631cb743bead1f596a68.

Measured second local-loopback samples (synthetic clinic; timings are not a production speed claim):
| Route | HTML bytes before / after | Queries before / after | Scripts before / after |
|---|---:|---:|---:|
| Dashboard | 298503 / 189108 | 223 / 217 | 23 / 15 |
| Patients | 320570 / 151057 | 199 / 163 | 29 / 23 |
| Patient chart | 4265799 / 4183365 | 295 / 295 | 26 / 24 |
| Calendar | 292370 / 213531 | 160 / 160 | 25 / 22 |
| Finance | 342710 / 254350 | 203 / 179 | 29 / 27 |
| Inventory | 329140 / 253982 | 158 / 152 | 26 / 24 |

TTFB varied from 155-275ms after warm-up, including some slower routes. High existing query counts and native SVG payload are explicit remaining limitations; chart internals were not optimized or replaced. Existing inventory price/quantity display formatting and the observed user-display fallback need separate bounded reconciliation before completion.

Local evidence is intentionally outside Git under `C:/Users/bassa/AppData/Local/Temp/denta-ui-v3-20261023/v4/`; review gallery is `../v4-owner-review/index.html` relative to the existing worktree. This includes all 13 original references, retained V3 comparisons, actual V4 EN/AR desktop/mobile where implemented, and explicit pending markers elsewhere. Captures taken before the last minor permission/presentation refinements are labeled development evidence, not final-head certification.

Next: continue the shared form/notification component presentation (the shared drawer header/profile form is rebuilt and renders in four EN/AR desktop/mobile captures); verify finance/inventory interactions and role/tenant denial on the synthetic fixture; extend full associated create/edit/detail flows; then agreements/exams/prescriptions/settings/users/reports, kiosk/Control Plane/auth. Reconcile every route in the existing ledger. Do not recreate worktrees, rerun completed unrelated suites, or automatically delegate to AGY.
