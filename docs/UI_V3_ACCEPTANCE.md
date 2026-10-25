# V3 reference-fidelity acceptance

2026-10-25. Draft PR #21 only. Protected release: `89352fd20e0d5166e81f631cb743bead1f596a68`. The runtime identity and exact evidence inventory are recorded in [component acceptance](ui-v3/component-acceptance.json); the final PR HEAD may add evidence/documentation only. **Owner visual acceptance is outstanding. No merge or deployment is requested.**

## Owner gallery and provenance

Open `C:\Users\bassa\AppData\Local\Temp\denta-ui-v3-20261023\v3-owner-review\index.html` by double-clicking it or pasting that path into Edge/Chrome. It works offline. Thirteen reference thumbnails, pinned registry/dashboard/finance/platform views, equal-height reference/actual panes, synchronized zoom/pan, 1586x992 / 1366x992 / 390x844 selection, EN/AR, real state selection and a component strip replace the former long screenshot dump. All actual screens are CI4 with disposable synthetic data. Reference files remain unchanged outside the worktree.

Current inventory: **277 unique actual captures across 51 named route/states**, with coverage detailed per locale/viewport in [viewport acceptance](ui-v3/viewport-acceptance.json). Not every supplementary state has every viewport; the manifest is authoritative. No recorded page errors, blocked business mutations or document-width overflow in the current selected set. Eighteen offline-gallery checks pass, including all thirteen reference selectors, image loading, equal-height panes, synchronized pan and AR mobile selection. These counts establish coverage, not pixel equivalence, exhaustive workflow certification or Owner acceptance.

The previous counts were different historical sets, not additive results:

| Set | Runtime | Meaning |
|---|---|---|
| 210 | `8a720c3593c961c0926ad258d0a3d3c9838359aa` | Earlier 35 states x two locales x three widths; 204 HTTP captures and six kiosk result states |
| 176 | `7bb13d30517cd3e941646f7402c5442ed2707917` | Later 44 states x two locales x two widths; two mobile notification-dropdown states unavailable then |
| 277 | Current runtime in component manifest | Deduplicated final presentation-pass captures, with targeted confirmations replacing affected earlier states |

The historical mobile notification gaps remain recorded. The current mobile notification trigger is visible and tested in EN/AR. Original attempts/diagnostics remain outside Git. Forced `--disable-gpu` produced blank native SVG in some desktop screenshots; normal Chromium composition rendered it correctly. Six clinical captures were replaced using the normal compositor. Native chart source hashes never changed. Capture sets and source changes are explicitly distinguished; no claim that every image was produced by checking out the final documentation commit.

## Reviewable visual evidence

- [English desktop contact sheet](ui-v3/v3-workspaces-en-1586.jpg), [Arabic desktop](ui-v3/v3-workspaces-ar-1586.jpg), [English mobile](ui-v3/v3-workspaces-en-390.jpg), [Arabic mobile](ui-v3/v3-workspaces-ar-390.jpg).
- Registry: [initial](ui-v3/initial-patients-en-1586.png), [current directory](ui-v3/v3-patients-en-1586.png), [current intake](ui-v3/v3-registry-intake-en-1586.png), [AR mobile intake](ui-v3/v3-registry-intake-ar-390.png).
- [Dashboard](ui-v3/v3-dashboard-en-1586.png), [finance](ui-v3/v3-finance-en-1586.png), [clinical](ui-v3/v3-clinical-en-1586.png), [platform overview](ui-v3/v3-cp-clinics-en-1586.png), [platform AR mobile](ui-v3/v3-cp-clinics-ar-390.png), [income drawer](ui-v3/v3-drawer-income-en-1586.png), [AR Select2](ui-v3/v3-select2-open-ar-390.png), [kiosk contract preview](ui-v3/v3-kiosk-sign-review-ar-390.png).

[Offline gallery preview](ui-v3/v3-owner-gallery-preview.png). Native empty-form validation, actual login and an unsaved provider-form switch state are included in the gallery component strip. The full screenshot archive and private fixture credentials stay outside the repository. The identity board is reference-only. The supplied 1586 reference is scaled for 1366 comparison; no original 1366 reference was supplied. Prototype Arabic is incomplete on some extended screens; actual application translations remain authoritative.

## Visual fidelity and workflow matrix

**REBUILT / ADAPTED** means the composition is implemented and rendered, with the stated real-workflow differences. It does not mean Owner approval or completion of every historical form state.

| Reference | Actual replacement | Status and intentional divergence |
|---|---|---|
| Design system | Forest rail, Travertine canvas, Sandstone toolbar, matte data surfaces, outline icon family, shared controls | REFERENCE / REBUILT. No new identity-board route; print/signed evidence retains protected design |
| Patient registry | Two primary facts, compact search/status task strip, labelled row action + disclosure, 600px contextual intake below toolbar | REBUILT / ADAPTED. Active/new counts replace unavailable medical-alert aggregate. Existing filters only; no invented active-plan or overdue-balance filter. Explicit save/book actions retain existing validation; no fake walk-in transition |
| Dashboard | Four actual facts, vertical visit-stage rows, clinical/recovery action queue and capacity footer; graphs/history lower | REBUILT / ADAPTED. No chair occupancy or utilization percentage is fabricated. Empty stages remain honest |
| Finance/treasury | Four canonical metrics, custody band, operational links, dense ledger and canonical outstanding-balance footer | REBUILT / ADAPTED. Actual account balances remain on Money Moves. No unlike-currency sum or new cash calculation |
| Clinical workspace | Identity/context, chart-first main column, action/imaging dock and compact clinical navigation | REBUILT / ADAPTED. Native 32-tooth SVG, clinical colors, snapshots and signed evidence unchanged; a new isolated fixture demonstrates a populated chart |
| Reception | Five actual visit lanes, compact cards/filter strip and existing authorized actions | REBUILT / ADAPTED. No new transitions, checkout authority or silent appointment change |
| Calendar | Existing FullCalendar grid, compact tools, real patient autocomplete and booking edge workspace | REBUILT / ADAPTED. Dynamic doctor resources remain OFF; original scheduling safeguards preserved |
| Contracts | Split plan/chart context, actual terms and signing panel | REBUILT / ADAPTED. No manufactured signature or signed-snapshot restyling; original concept reference is a collage |
| Examinations/Rx | Clinical context, history/safety sections, protected record forms and prescription workflow | REBUILT / ADAPTED. Existing routes and immutable-record rules; no simulated medical decisions |
| Inventory | Stock/risk surfaces, zebra table and consistent item/batch/supplier forms | REBUILT / ADAPTED. No invented supplier finance or stock movement |
| Settings | Organized clinic administration, hours form and contextual impact assessment | REBUILT / ADAPTED. Preview remains the real protected workflow; doctor scheduling stays OFF |
| Kiosk | Touch-first PIN, lookup, verification/details and contract review states | REBUILT / ADAPTED. Real shared-phone/no-match/session-reset states captured. No final check-in, patient update, consent or signature submitted |
| Control Plane | Distinct 220px rail/terracotta identity, four overview facts, tenant directory and persistent read-only inspector | REBUILT / ADAPTED. Inspector uses already authorized row data; missing usage/admin/plan facts are not invented. Detail navigation and audited privileged actions remain separate |

Associated patient/appointment/inventory forms, income/expense/examination drawers, provider screens, reports, users/roles and platform plan/settings/confirmation/detail screens have current captures. FullCalendar, DataTables and Select2 retain behavior while using V3 appearance. Precise inaccessible/unsupported prototype capabilities are not rendered as fake working controls.

## Focused validation and preservation

- Tailwind build and theme CSS parsing pass; 46 changed PHP templates linted at the final runtime. SaaS guardrails pass. No Actions.
- **42 actual routed HTTP/security checks pass**: EN/AR shell/routes, forged and cross-tenant IDs, CSRF rejection, role denial, session/Bearer separation, Control Plane airlock and kiosk PIN/privacy. Existing synthetic financial/clinical rows have zero changes/removals.
- **19 changed-interaction checks pass**: two primary registry cards, 88px rail, locale/filter roundtrip, dirty intake cancel/retain/discard, Escape/focus return, row-menu navigation safety, visible mobile notifications, populated native preview, scheduling OFF, 220px platform rail and real selected-row inspector/tab behavior.
- **20 geometry/frost/zoom/control checks pass** in EN/AR: 62px clinic toolbar, visible medical alerts, native toggle state, desktop 5px scrim blur, opaque reduced-transparency fallback, no mobile blur and visible footer under 125%/150% CSS zoom equivalents. These are not native browser-zoom or full assistive-technology certification.
- **86 computed primary-text contrast samples pass** after correcting clinical small-copy contrast. Scope: visible headings/metrics/filter/custody/ledger text, not universal WCAG certification. Inter detector warning is a documented intentional exception: the Owner-approved reference explicitly requires Inter.
- 79 original-checkout/reference hashes and 1,021 protected-source hashes match. Controllers, models, services, database/config/security files, native odontogram and evidentiary print/signed files remain unchanged. Proprietary and third-party licensing preserved.
- Synthetic additions are two new patients (IDs 9/10), with a 32-row nonbillable chart for patient 9. No original fixture is rewritten; no finance records created. No production or business-database access.

Repeated expensive permission checks introduced by toolbar visibility were measured, then removed by reusing exact results within the current view renderer. Existing `has_permission()` remains authoritative, partials fall back to it, and controller/service authorization is unchanged. No session/cache permission snapshot, cross-request or cross-user cache is introduced.

## Performance and cost

Same synthetic database, loopback PHP 8.2/MariaDB 10.4, three warm sequential HTTP trials per route, browsers idle. Server response only; no Hostinger, cold-network, LCP or low-end-device performance claim. Query counts fall on all sampled routes, but latency is mixed and existing high backend counts remain a separate limitation.

| Route | Before / after ms | Queries before / after | HTML byte change |
|---|---:|---:|---:|
| `/dashboard?lang=en` | 203 / 187 | 223 / 205 | -52395 |
| `/patients?lang=en` | 187 / 187 | 199 / 193 | -42914 |
| `/patient/1?lang=en` | 328 / 328 | 453 / 447 | -53485 |
| `/appointments?lang=en` | 172 / 171 | 165 / 159 | -41489 |
| `/finance?lang=en` | 188 / 203 | 203 / 191 | -53293 |
| `/inventory?lang=en` | 172 / 172 | 158 / 146 | -48235 |
| `/settings?tab=hours&lang=ar` | 188 / 141 | 188 / 176 | -47452 |
| `/appointment/calendar?lang=en` | 172 / 172 | 160 / 154 | -47344 |

Compared with the prior `95a3af2` candidate, scoped theme/source CSS grows from 431,056 to 453,787 uncompressed bytes (exact per-asset final figures in component manifest). The new architecture stylesheet is roughly 20KB uncompressed; it is not a new framework or dependency. No overall CSS-size or universal latency win is claimed. Forty purely visual `!important` declarations were removed from platform CSS; retained compatibility mechanics are inventoried. Previous secondary-layout reduction (252 to 58) remains historical evidence, not a new reduction in this pass.

Non-export routes retain the earlier avoidance of 2,253,608 decoded JS bytes from unconditional export dependencies. Export workflows keep their existing dependencies. Native chart assets and licensing notices are preserved. Frost is limited to temporary desktop scrims and the scrolled command bar, with opaque mobile/reduced-transparency/forced-color fallbacks; no glass on financial/clinical reading panels.

## Remaining acceptance boundaries

Owner visual acceptance and fresh independent review of the final exact HEAD remain outstanding. Previous `95a3af2` review was **COMMENTED**, not APPROVED; its 210/176 discrepancy is reconciled above. No blanket complete status is inferred from captures or local tests. Source reviewer cannot certify inaccessible local gallery contents without opening the curated evidence or supplied gallery.

Native clinical geometry/colors, legal/print documents, real validation field requirements, existing independent access boundaries and unavailable backend facts are intentional exceptions to literal prototype matching. Dynamic labels/technical identifiers may remain English where inherited canonical data requires it. Full kiosk consent/clinical/finance mutations were deliberately not performed for presentation acceptance; accepted backend evidence was reused rather than reopening financial suites.

All optional gates retain their disabled state: corrections, dynamic scheduling, WhatsApp, checkout, contact history and derivatives. No migration, dependency upgrade, new permission, main/release integration, production change or deployment. **Ready for Owner visual review; merge/deployment remain blocked.**
