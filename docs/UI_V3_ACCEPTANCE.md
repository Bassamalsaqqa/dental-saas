# V3 architectural redesign acceptance

2026-10-24. Protected release: `89352fd20e0d5166e81f631cb743bead1f596a68`. Runtime: `8a720c3593c961c0926ad258d0a3d3c9838359aa`; subsequent evidence commits do not change runtime. Draft PR #21 awaits Owner visual acceptance and finished-candidate independent review. No deployment requested.

## Actual visual evidence

Reliable local Chromium replaced the timed-out screenshot path. 210 actual CI4 captures cover 35 route/states ? EN/AR ? 1586?992, 1366?992 and 390?844: 204 HTTP-page captures return 200; six kiosk result captures are post-lookup UI states. No document-width overflow or recorded page errors in accepted captures. Late corrected captures supersede earlier attempts. One earlier navigation timeout and transient inventory diagnostic remain privately retained, not counted as passes. This is rendered coverage, not exhaustive interaction certification or pixel equivalence.

- [English desktop](ui-v3/v3-workspaces-en-1586.jpg), [Arabic desktop](ui-v3/v3-workspaces-ar-1586.jpg), [English mobile](ui-v3/v3-workspaces-en-390.jpg), [Arabic mobile](ui-v3/v3-workspaces-ar-390.jpg)
- Registry before/after: [initial desktop](ui-v3/initial-patients-en-1586.png) ? [final desktop](ui-v3/v3-patients-en-1586.png); [initial AR mobile](ui-v3/initial-patients-ar-390.png) ? [final AR mobile](ui-v3/v3-patients-ar-390.png)
- [Clinical](ui-v3/v3-clinical-en-1586.png), [Control Plane AR](ui-v3/v3-cp-clinics-ar-1586.png), [kiosk AR mobile](ui-v3/v3-kiosk-lookup-ar-390.png), [viewport matrix](ui-v3/viewport-acceptance.json)

Reference, initial and corrected renders were visually compared. Legacy oversized headers, pastel cards, centered dialogs and scattered actions were replaced with the 88px rail, 62px command bar, matte panels, compact filter/table hierarchy and contextual edge workspaces. Markup was replaced where necessary, preserving functional hooks.

## Screen fidelity matrix

EVIDENCED means rendered and inspected; ADAPTED identifies real-workflow differences, not Owner approval.

| Reference | Replacement composition | Status / divergence |
|---|---|---|
| Identity board | Shared palette, spacing, typography, focus and component tokens | REFERENCE, no application route |
| Dashboard | Actual KPIs, routing/activity and recovery/documentation queues | EVIDENCED / ADAPTED: no invented capacity statistics |
| Reception | Five real-status lanes, compact cards, existing actions retained in disclosure | EVIDENCED / ADAPTED: no new transitions or checkout |
| Calendar | FullCalendar filters and 520px edge drawer; footer clipping fixed | EVIDENCED / ADAPTED: dynamic resources OFF |
| Registry | KPI/filter/zebra table, creation drawer, sectioned create/edit forms | EVIDENCED; original validation, search and consent hooks |
| Clinical | Chart-first main column, context/actions/imaging sidebar | EVIDENCED / ADAPTED: native 32-tooth SVG/snapshots unchanged; empty fixture honest |
| Contracts | Split plan/chart, agreement and actual signing panel | EVIDENCED / ADAPTED: no fabricated signatures; frozen evidence unchanged |
| Examinations / Rx | Compact context, safety/history and protected forms | EVIDENCED / ADAPTED: existing separate clinical routes |
| Finance / treasury | Real KPIs, operational tabs/filter/ledger, payment/expense drawers | EVIDENCED / ADAPTED: canonical figures; treasury remains existing route |
| Inventory | Stock KPIs/table and consistent item, supplier, batch/receiving forms | EVIDENCED / ADAPTED: no invented supplier finance |
| Settings | Organized tabs, real hours/impact controls, compact mobile navigation | EVIDENCED / ADAPTED: scheduling OFF |
| Kiosk | Touch-first identity/keypad/privacy and real lookup stages | EVIDENCED / ADAPTED: actual PIN/four-stage privacy; no manufactured consent |
| Control Plane | Rebuilt dashboard/registry and related management forms | EVIDENCED / ADAPTED: independent authority; unknown requested plan em dash, real summary retained |

Related appointment, patient, expense, provider, report, inventory, user/role forms share V3 sections and actions. Static platform text is localized; existing dynamic JavaScript labels and technical identifiers can remain English. Native chart internal labels and legal/clinical print/signed snapshots retain protected presentation. No blanket claim for every historical state.

## Focused checks and preservation

- 124 changed PHP files lint; CSS parses and Tailwind build passes. SaaS guardrails pass using Git Bash. No Actions.
- 42 HTTP/security checks pass: EN/AR routes, forged tenant IDs, session/Bearer separation, CSRF, role denial, Control Plane airlock and kiosk privacy/PIN.
- 10 browser interaction checks pass: actual session patient search, calendar navigation/drawers, focus/Escape and canonical finance values. Six targeted finance/exam drawer checks pass. Final checks verify calendar footer geometry, kiosk touch/contrast and unique registry IDs.
- Independent bounded source review found no substantiated blocking regression after checking form ownership, hooks and scripts. Platform Chart.js styling is the intentional page-script presentation change. This is not external approval.
- Preservation manifests: 79 original-checkout files and 1,021 protected source files unchanged. Controllers/models/services, database/configuration, native odontogram and signed evidence remain protected. No migrations, dependencies, permissions or feature-switch changes.
- Accepted backend evidence reused; no fresh full financial SQL or real patient consent certification claimed for this visual pass.

## Performance

Idle-browser loopback PHP 8.2/MariaDB 10.4; three warm sequential trials per route. Server response, not paint or Hostinger measurement. Query deltas zero; HTML shrinks. Latency mixed: no global performance win. High existing query counts remain a separate backend limitation.

| Route | Before / after ms | Queries before / after | HTML byte change |
|---|---:|---:|---:|
| `/dashboard?lang=en` | 313 / 297 | 223 / 223 | -49,755 |
| `/patients?lang=en` | 297 / 313 | 199 / 199 | -43,179 |
| `/patient/1?lang=en` | 547 / 579 | 453 / 453 | -53,632 |
| `/appointments?lang=en` | 297 / 313 | 165 / 165 | -38,727 |
| `/finance?lang=en` | 312 / 375 | 203 / 203 | -52,309 |
| `/inventory?lang=en` | 266 / 266 | 158 / 158 | -46,766 |
| `/settings?tab=hours&lang=ar` | 250 / 203 | 188 / 188 | -48,273 |
| `/appointment/calendar?lang=en` | 172 / 219 | 160 / 160 | -45,396 |

Non-export pages avoid 2,253,608 decoded JS bytes from the prior unconditional export bundle; exporters retain dependencies. Duplicate FullCalendar major-version loading removed. Asset-size observations are not compressed-transfer or elapsed-time claims.

Route-scoped theme assets (not all loaded everywhere):

| Asset | Bytes |
|---|---:|
| `v3-chart-chrome.css` | 1,624 |
| `v3-clinical-kiosk.css` | 4,088 |
| `v3-control-plane.css` | 26,503 |
| `v3-flow-forms.css` | 18,116 |
| `v3-inventory-forms.css` | 2,579 |
| `v3-support-views.css` | 39,895 |
| `warm-clinical.css` | 20,402 |
| `warm-secondary-layout.css` | 44,989 |
| `warm-secondary.css` | 17,421 |
| `warm-workspaces.css` | 14,040 |

Support CSS consolidates repeated inline styles; secondary CSS pruned. New components add weight: no overall CSS reduction claimed. No new framework, dependency or query. Theme links are screen-only to protect printed evidence.

## Preserved / unsupported

No invented prototype statistics, medical inference, checkout, supplier finance or dynamic resources. Corrections, scheduling, WhatsApp, checkout, contact history and derivatives retain OFF state. Legal documents, signatures and native odontogram remain stable. Localization exceptions above are explicit. Production and reference kit untouched. Full captures/private credentials remain outside source; curated screenshots are synthetic only. Ready for visual acceptance and independent review, not deployment.
