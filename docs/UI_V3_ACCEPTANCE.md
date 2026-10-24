# V3 UI acceptance record

Date: 2026-10-24. Baseline: `89352fd20e0d5166e81f631cb743bead1f596a68`. Candidate: isolated `feature/warm-clinical-v3-20261023` worktree. Reference: owner-approved `new-ui-ux/denta-warm-ui-v3`, including its `docs/REVIEW_MATRIX_V3.md`.

## Outcome and scope

Frontend composition and shared warm clinical theme implemented across the authenticated shell and listed modules. Backend/controllers/services/models/config/database and native odontogram remain protected. Independent changed-source review found no remaining substantiated blocking runtime regression; contract review column placement and editor mobile breakpoint findings were corrected. This record is engineering evidence, not formal owner visual approval.

Status meanings: **COMPLETE** = the stated acceptance item is fully evidenced; **PARTIAL** = implemented and checked to the extent stated, with remaining visual acceptance; **REFERENCE** = design guidance rather than a production screen. No screen is claimed to have complete matched-viewport screenshot acceptance.

## Thirteen-reference matrix

| Reference screen | Status | Implemented / evidenced | Remaining limit |
|---|---|---|---|
| Design system | REFERENCE | Shared tokens, typography, shell, forms, tables and focus styles applied | Reference gallery remains design guidance |
| Clinical chart | PARTIAL | Patient workspace recomposed; native 32-tooth chart actual-browser pass; EN/AR mobile check | Final matched-viewport comparison pending; native chart deliberately preserved |
| Reception/dayboard | PARTIAL | Existing dynamic dayboard retained; read-only legacy lanes project existing appointment rows; original actions retained | Projection follows current filters; no new transition workflow; visual comparison pending |
| Finance | PARTIAL | Ledger/actions and original metric IDs/labels retained; HTTP and EN/AR mobile checks | Final screenshot acceptance pending; no new accounting behavior |
| Command dashboard | PARTIAL | Commands, metrics and operational composition replaced; latest native desktop capture | Late contrast patches postdate some captures |
| Appointment calendar | PARTIAL | Existing FullCalendar behavior and detail hooks retained; duplicate version load removed | Prototype grid states are not new backend features; visual comparison pending |
| Patient registry | PARTIAL | Registry composition and creation hooks retained; actual browser at 1366px and EN/AR mobile checks | Final matched-viewport screenshot acceptance pending |
| Treatment contracts | PARTIAL | Plan/agreement columns corrected; stored snapshots and signed evidence outputs preserved | Reference is a collage quadrant; final visual/signing-state comparison pending |
| Examinations & Rx | PARTIAL | Examination and prescription surfaces/forms restyled; native data/actions retained; mobile checks cover both | Final screenshot acceptance pending; no clinical workflow expansion |
| Inventory | PARTIAL | KPIs/grid/actions restyled; seven DataTables strings use existing EN/AR keys; mobile checks | Final visual acceptance pending; no new FEFO/accounting workflow |
| Settings | PARTIAL | Settings composition and schedule impact surfaces restyled; existing preview/apply controls retained | Late heading contrast patch; final screenshot acceptance pending |
| Kiosk | PARTIAL | Separate large-touch shell themed; actual PIN-stage native capture and HTTP privacy checks | Full identity/health journey visual acceptance pending |
| Super-admin | PARTIAL | Separate global shell themed; global entry and tenant-plane rejection HTTP checks | No latest complete desktop/mobile visual matrix |

## Verification

| Acceptance item | Status | Evidence |
|---|---|---|
| Protected source and records | COMPLETE | Source preservation artifacts report 1,021 protected files unchanged; original synthetic financial/clinical records retained; no backend/config/DB diff |
| Focused HTTP checks | COMPLETE | `focused-http-final-output.json`: 42 PASS, zero failed; includes EN/AR routes, forged/cross-tenant IDs, session/Bearer boundary, JSON denial, CSRF, viewer POST denial, airlock, kiosk privacy/PIN and record preservation |
| Responsive document width | COMPLETE | `evidence/v3-responsive-final.json`: 22 checks across 11 screens in EN/AR at 390 x 844; zero document overflow |
| Actual browser spot checks | COMPLETE | 1366px patient registry and native 32-tooth clinical chart passed; does not certify every page or state |
| Build and repository guardrails | COMPLETE | Build and SaaS guardrails passed; all 34 changed/new PHP files lint; five theme CSS files parse; exact-lock dependencies retained |
| Final screenshot acceptance | PARTIAL | Latest `v3-*` native screenshots show actual recomposed pages; exact-viewport CDP screenshot capture repeatedly timed out |

Screenshot chronology matters: older `final-*`/`after-*` images predate recomposition and must not be presented as final candidate evidence. Native `v3-*` captures are current composition evidence, but some precede late heading/contrast fixes and are not a complete final matched-viewport set. No pixel-equivalence or complete screenshot approval is claimed.

Final native screenshots also exposed clipped long rail labels and pagination reparented outside DataTables wrappers. Scoped CSS now gives collapsed rail labels sufficient room/wrapping and styles the existing pager independently of its wrapper. These are presentation-only corrections.

Source review checked existing IDs, forms, names/actions/methods, drawer hooks and page scripts. Existing IDs remain; page scripts remain unchanged except shell navigation accessibility and the seven inventory language bindings. Signed SVG/data snapshots, signer/time/image and attachment outputs remain intact; finance gross collections/provider paid/net cash/open invoice labels retain their original IDs. New appointment lanes add no mutation or query.

## Measured performance

Source: `paired-performance-output.json`. Loopback PHP 8.2/MariaDB 10.4, same synthetic schema, production mode without toolbar, three warm sequential HTTP responses per route; medians below measure server response, not browser paint or a network benchmark. All eight query deltas are zero. HTML shrank on every measured route, but latency is mixed: inventory, settings and calendar medians increased. There is no measured global latency win.

| Route | Median ms before / after | Queries before / after | HTML bytes before / after |
|---|---:|---:|---:|
| `/dashboard?lang=en` | 219 / 218 | 223 / 223 | 296,336 / 256,383 |
| `/patients?lang=en` | 188 / 188 | 199 / 199 | 320,570 / 285,541 |
| `/patient/1?lang=en` | 360 / 344 | 453 / 453 | 963,810 / 928,310 |
| `/appointments?lang=en` | 188 / 188 | 165 / 165 | 289,797 / 262,456 |
| `/finance?lang=en` | 203 / 203 | 203 / 203 | 342,710 / 300,830 |
| `/inventory?lang=en` | 203 / 281 | 158 / 158 | 329,140 / 296,961 |
| `/settings?tab=hours&lang=ar` | 329 / 344 | 188 / 188 | 448,755 / 412,366 |
| `/appointment/calendar?lang=en` | 328 / 360 | 160 / 160 | 292,370 / 257,828 |

Final compiled Tailwind: 293,433 to 280,862 bytes (12,571 fewer). Non-export pages avoid 2,253,608 decoded JavaScript bytes from the removed unconditional export-library bundle; this is a decoded asset-size saving, not measured compressed transfer or elapsed-time saving. Existing exporters retain their dependencies. Earlier build artifacts can describe intermediate CSS sizes; 280,862 is the final candidate file size. Theme stylesheets add their own assets, so the Tailwind reduction is not a claim that total CSS fell by the same amount.

## Evidence location and remaining acceptance

Session JSON/log/native-image artifacts are held outside the repository under `C:/Users/bassa/AppData/Local/Temp/denta-ui-v3-20261023/`; curated evidence is managed separately. This document records supplied completed checks and independent source review; no checks were rerun to write it. Before claiming complete visual acceptance, capture the current patched candidate at agreed desktop/mobile viewports and compare each implemented screen/state against its reference, including kiosk and control plane. Prototype-only interactions remain references unless already supported by the production workflow.

## Curated visual evidence

All images show synthetic fixtures. PNG dimensions are actual capture dimensions; filenames do not assert matched viewport certification. Native-window captures differ from requested viewport sizes. The old desktop/mobile images are baseline only; there is no final 390px screenshot claim.

| Before | Recomposition |
|---|---|
| [English registry baseline](ui-v3/before-patients-en.png) | [English registry](ui-v3/after-patients-en.png) |
| [Arabic mobile baseline](ui-v3/before-patients-ar-mobile.png) | [Arabic registry, native desktop](ui-v3/after-patients-ar.png) |

[Clinical workspace](ui-v3/after-clinical-en.png), [Arabic agreement review](ui-v3/after-contracts-ar.png), [machine-readable focused acceptance](ui-v3/acceptance.json). The registry English capture was refreshed after rail/pager fixes; Arabic predates those spacing-only fixes; other native captures may precede the documented final contrast-only patches.

## Backlog and release boundary

Finish matched-viewport visual comparisons (1586 x 992, 1366px, 390px), complete kiosk/control-plane browser states, zoom and full keyboard journey before claiming full visual acceptance. The connected browser can inspect responsive DOM but repeatedly times out capturing overridden viewports. Dashboard baseline query count and patient-workspace payload remain significant; this presentation scope does not refactor backend queries. No new transition, scheduling, accounting, clinical or kiosk privilege is supplied by the prototype.

There are no database, configuration, dependency or permission changes to roll out. No migration or dependency installation is needed for this UI branch. A future authorized source release must include compiled CSS and preserve `.env`, `.htaccess`, all writable/uploads/signing evidence and optional feature settings. Source-only rollback must retain current databases and durable files. Production compatibility and deployment are not newly certified by loopback PHP 8.2/MariaDB 10.4 checks. Recommendation: ready for draft source/design review; not yet a production GO.
