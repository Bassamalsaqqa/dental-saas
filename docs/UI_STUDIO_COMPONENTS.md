# Studio frontend ownership and component contracts

This catalog governs existing CI4 presentation. It is not a second UI framework, a new application API or a claim that every legacy inline script is gone. Production-derived release, tenant/RBAC authority and signed/native evidence remain protected. All optional features stay OFF. Components render escaped scalar data or use the established trusted server partial contract; do not introduce arbitrary raw-HTML slots, downloaded HTML/script execution or dynamic evaluation.

## Foundations and four header jobs

`public/assets/css/studio-theme.css` owns brand/neutral/semantic colors and now names type, Arabic font, line-height/weight, spacing, radius, elevation, focus, motion, layer and grid-density roles. New components consume these roles. Existing route skins adopt them incrementally; scattered historical spacing values are not falsely described as fully consolidated. Warm forest/sage remains the accepted direction. The later development-only Theme Studio adds versioned safe theme configuration behind `StudioThemes.enabled=false`; see `STUDIO_UX_THEME_PROGRAM.md`. Existing domain permissions are reused, with scope-specific administration checks; no new production grants.

| Header job | Existing owner / input | Deliberate treatment and non-use |
|---|---|---|
| Brand/entry | `layouts/studio_access.php`; existing locale and auth form | Forest introduction, original subdued contour, compact language control; never safety/security claims |
| Operational command | Dashboard `page-title-row`, appointment heading; selected date/actions from server | Date/context and one dominant permitted action; contextual secondary navigation; motif only the existing dashboard heading |
| Patient identity | `patient/show.php`; canonical patient, alerts and header DTO | Clear identity/critical context without decorative pattern; no invented healthy/assessed state |
| Reading/form/money record | `record-heading`, finance record and existing form headings | Quiet reading surface and real record identity; no visual texture behind money, notes or signed evidence |

## Governed primitives and compositions

Straightforward buttons/labels render semantic HTML directly. Reuse behavior contracts rather than wrapping every `div` in a PHP function. All labels are real EN/AR language keys; logical layout supports RTL while phone/password/money text keeps its appropriate direction.

| Family / owner | Input API and example | Semantics, keyboard and locale | Responsive / async / deliberate non-use |
|---|---|---|---|
| Button / IconButton | Existing `.button`, `.icon-button`; real href or type=button/submit, localized accessible name | Native link vs button, visible focus, >=44px where feasible; disabled operations explain why | Preserve submitter name/value; no fake quick action or permission inferred from CSS |
| Password | `components/studio_password_controls.php`, `studio-access.js`; existing native password input and field label | Hidden initially/on submit, preserved name/id/autocomplete/paste/selection; per-field changing accessible name, no mixed aria-pressed | One-line Show/Hide caption; wrapper owns rounding independent of LTR password text; no native MFA/consent-field substitution |
| Field / input / notes | Existing `.form-field`, `.studio-form`; native label, name, required, errors, autocomplete/inputmode | Server validation/CSRF authoritative; long notes stay textarea; radio/checkbox meaning preserved | Proportionate field grid; complex workflows remain full pages; no new medical default |
| Select / combobox | Native select unless existing large patient lookup uses its authorized search control | Native arrow/keyboard restored in seven affected skins; Select2 keeps its own enhanced affordance | No blanket custom dropdown, date/file replacement or extra lookup request |
| Disclosure | Registration `#optionalEnabled` button controls `#optionalSection` | type=button, stable control ID, aria-expanded, Enter/Space; no submitted name | Data/field values remain intact while collapsed; **not** a persisted preference or medical Yes/No |
| Checkbox / switch | Existing actual selection/preferences | Retain native successful fields, labels and existing clinical semantics | Clinical alert historical 0/unassessed remains a separate policy decision; no migration here |
| Tabs / section groups | `studio-patient-sections.js`, existing `.tab-button`/tabpanel IDs | Care and History/billing are stable labelled group buttons; actual tabs retain aria-controls/selected and manual keyboard activation; arrows/Home/End stay within visible tabs | Deep links select correct group, no history removed; ordinary route links use navigation, not role=tab |
| ActionMenu | `studio-controls.js`, actual button/popover `.workspace-action-menu` | Native button/popover, labelled actions; once-only binding; focus returns to visible trigger after drawer close | Secondary patient actions only; no auto checkout or new privileged action |
| Surface / summary / alert | Semantic role tokens plus canonical DTO values | Status/warning colors only where facts justify them; unknown/denied differ from zero | No four-KPI template, invented income/readiness or pattern behind clinical/finance data |
| DataTable / MobileRecord | `studio-grid.js`, patient/chart/stock directories; existing bounded page response | Fixed deliberate desktop tracks, focusable labelled 2D scroll, full-value reader where needed; native record links/actions | Patient identity records on narrow screens; true spatial grids remain grids. No universal tablet card conversion |
| Grid lifecycle | `StudioGrid.refresh(root=document)`, `.metrics`, `.dispose()` | Idempotent visible-table preparation, observers queue only affected tables; dispose disconnects/stops pending work; no data request | Eight-table fixture proves scoped updates; metrics are development evidence, not analytics |
| Filter / pagination | Existing clinic/finance/clinical producers | Native form/search controls, stable input while request runs, version/abort protection, page identity from same server response | Preserve stale context with a label, clear records on denied access; no full-page simulated SPA |
| Drawer | Existing `app-drawer.js`, explicitly rendered drawer body/fields | Focus trap, Escape, visible opener return, queued-open cancellation; authorized existing forms only | Contextual short operations; long forms retain deep-link full page. Never fetch arbitrary HTML and re-execute scripts |
| Decision / reason / error | `StudioDialogs.confirm(message, {title,subject,confirmLabel,destructive})`, `.reason(...)`, `.notice(...)` | Native dialog, safe textContent, Cancel/Escape no request, specific subject/outcome; financial bodies/CSRF unchanged | Short consequential decision; no invented refund/approval policy or synthetic success |
| Loading / empty / denied / retry | `StudioLoading.begin(region,{initial,columns,rows}) -> finish()`; existing producers own request | Inert aria-hidden static shapes after 300ms, aria-busy/status, no delay of resolved data; bounded read timeout | Only actual asynchronous content; no SSR/navigation skeleton or fake percentage; previous results explicitly stale |
| Pending / validation / toast | Shared native submit feedback, existing server/async success handlers | Only valid non-cancelled native submit enters pending; successful fields remain enabled; duplicate prevention; confirmed server result owns success | Do not show success before persistence or replace financial mutation feedback with skeletons |
| PatientLookup | Existing session `/api/search/patients` and governed appointment lookup | Safe DOM and numeric IDs, same tenant/session/permission boundary, query cancellation | No Bearer credential reuse or parallel patient truth |
| AppointmentRecord / Inspector | `components/workflows/appointment_record.php`: `$visit`, `$stageLabels`, `$returnContext`; existing calendar inspector | Escaped actual identity/time/status/resource, canonical link/context; no inferred transition | Same server appointments feed lanes and phone agenda; FullCalendar grid retained when workspace is wide enough |
| Calendar | `appointment/partials/calendar_runtime.php`; existing working-hours/events/permissions | Existing event mapping/details/date booking; list event renders a real anchor required by FullCalendar v5; modifier/keyboard behavior preserved | Available calendar width <680px defaults listDay; same provider/availability feature gates; no second scheduler |
| Clinical chart frame | Existing native chart and patient clinical presentation partial | Original native SVG, paint, algorithms, snapshot fields and clinical runtime remain unchanged | Responsive surrounding frame only; known history-preview mismatch is not silently fixed |
| FinanceDecision / finance modal | `studio-finance-controls.js`, `finance/partials/modal_*`; exact existing handlers and fields | Validated numeric payment ID, enumerated open/close bindings, explicit reason/record; no inline eval/HTML API | Existing permission guards stay outside partials; no recalculation or new settlement authority |
| Contract builder | `studio-contract-builder.js`; four inert safely encoded existing JSON values | Original builder algorithm byte-preserved after data extraction; explicit allowlisted button bindings | Existing draft workflow only; signed/print/storage evidence untouched; item-sync defect still blocks certification of draft item preservation |

## Source ownership and extraction limits

- Patient: nine `workspace_*.php` panels plus named field/history/clinical-presentation runtimes. Scope-preserving includes intentionally retain the original local DTO/permission variables. `patient/show.php`: 3891 to1257 lines. Two clinical/history scripts remain server-parameterized and unchanged; this is an incremental decomposition, **not fully externalized clinical logic**.
- Finance: payment/refund/provider-modal partials, field widgets and record runtime; explicit action bindings in a scoped asset. `finance/show.php`:2059 to990 lines. Money mutation functions/field payloads are retained; the record runtime is still PHP-parameterized.
- Agreements: original chart paint CSS separated from the existing builder layout asset, unchanged algorithm moved to an external file and four values carried as inert JSON. `contracts/create.php`:1560 to386 lines. No signed document, clinical charge or accounting behavior change.
- Calendar: existing server configuration stays in `calendar.php` (586 to133 lines), calendar behavior is isolated in `calendar_runtime.php`. It remains a PHP-parameterized runtime; no alternate framework or bundler. Optional scheduling stays OFF.
- Shared: duplicate byte-identical patient/finance CSRF presenter consolidated into one `studio_csrf_runtime.php`. Native intake initializer is a guarded external module. Main-file line reduction means separation of ownership, not deletion of functionality or a smaller total repository.
- Source parity checks expand the includes and compare all original successful name attributes: patient24, finance30, contract17; calendar0. Original native clinical/history scripts and contract builder algorithm remain exact. Enumerated inline handlers removed: finance19, contract10, calendar1, patient navigation13. Seven patient inline handlers remain around preserved clinical/evidence-related behavior; do not claim zero legacy source or expand into clinical fixes to improve a count.

## State ownership

`not requested -> initial loading -> populated | empty | denied | error`; `populated -> refreshing (labelled stale) -> refreshed | transient error with previous records | denied-cleared`. Forms keep existing validating/submitting/server-result contracts. Abort/version guards apply only to reads; do not cancel a financial mutation and infer that the server rolled it back. Shared helper makes shapes; producer owns records, tokens, errors and retry. A permissions change clears links/records before presenting denied feedback.

## Separate clinical-intake decision, not implemented

Recommend explicit Unassessed / No / Yes for each clinical question only after the clinical Owner approves wording, clinician workflow and historical interpretation. Existing unchecked/hidden0 data cannot safely be relabelled retrospectively as assessed No or unknown without evidence. A future proposal must define provenance, which fields require answers, audit/versioning and how old entries display; preserve original records and never mass-backfill meaning. This PR changes only optional-section disclosure semantics, not any medical value, validation or stored default.

## Third-party ownership

Patient registry retains its existing jQuery3.7.1/DataTables1.13.7 CDN dependencies; calendar retains FullCalendar5.11.3 and locales. No dependency version/install changed. CDN availability, absent local fallback and lack of integrity attributes remain explicit operational/security follow-ups; graceful frontend error handling is not proof of offline operation or complete supply-chain certification. Do not conflate current upstream versions with an authorized upgrade. No Mobbin/Figma service was connected or represented as used; browser screenshots, installed frontend skills and publicly available primary design guidance were used.

## Acceptance evidence and remaining gates

See [current portable gallery, reconciliation and test matrix](ui-evidence/modular/README.md). Physical screen-reader, real touch hardware/password-manager extension, native browser zoom and successful new clinical/finance persistence were not certified. Local role/tenant/CSRF source authority and previously completed backend evidence were preserved, not relabelled as new tests. PR remains Draft; Owner whole-frontend visual acceptance and independent exact-current-head review are separate gates. No production access, migrations, source integration or deployment.

## Current workflow continuity slice

Patient Care/History tabs own presentation state, while the native patient runtime still owns clinical/filter facts. `studio-patient-sections.js` synchronizes only explicit same-patient return targets, designated quick forms and existing row navigation. Cross-origin targets and unrelated return destinations are retained unchanged. It does not rewrite clinical or money values.

Examination optional narrative fields now use a native disclosure button, not a persisted on/off switch. Hidden content remains in the form and entered values survive collapse. Medical assessment storage and positive-alert policy remain unchanged. Visit links distinguish opening an existing examination (`view`) from creating one (`create`), without granting either permission.

Inventory usage-history remains clinic-wide: the existing producer does not consume an item-ID filter. Its item-detail link is labeled honestly and preserves a local return to the item. Item-filtered history is a separately scoped backend follow-up, not simulated by frontend filtering. [Focused evidence](ui-evidence/product-ux/workflow-continuity.json).

## Shared compact scale and contextual creation

`studio-scale.css` owns the safe screen-only default. Route styles consume `--studio-font-*` roles with original pixel fallbacks; `StudioThemeCatalog` produces the same declaration map and enumerated Compact/Comfortable variants. Larger text expands row height, and mobile native inputs retain16px minimum to avoid focus zoom. No body transform or browser zoom is used. Print documents and diagnostic chart rendering remain excluded. Platform reads only platform layout declarations and never tenant colors. Theme feature remains defaultOFF until separately reviewed schema/activation.

`studio-create-dialog.js` owns patient-registry and booking task lifecycle: explicit trigger-kind matching, native validation, CSRF-aware existing JSON endpoints, dirty-discard/keep, actual record-ID success, unconfirmed-transport handling without automatic retries, focus restoration and full-page fallback. Availability starts on opening. DataTable refresh keeps patient list context; dayboard offers an explicit schedule refresh and calendar refetches its established source. Complex initial-stock creation and clinical/signing workflows retain full pages. [Focused current evidence](ui-evidence/product-ux/compact-rollout.json).

## Native component rollout ownership

`studio-controls.css` owns native switch/checkbox/radio/focus/disabled appearance. Switches retain successful checkbox values and persisted-preference semantics; permission sets and working-day multi-selection stay checkboxes.

`studio_native_create_dialog.php` and `studio-native-create.js` own fixed, application-selected form presentation, keyboard focus, dirty state, explicit discard, native validation/redirect recovery and full-page fallback. The finance dialog mounts one inert canonical form template at a time. Existing calculator, patient picker and provider presenter initializers are idempotent and scoped; no arbitrary HTML fetching or script execution is used. Hidden business payloads participate in dirty detection. Clinically complex and multi-step provisioning workflows remain full pages.

`studio-provider-allocations.js` extracts the original presenter body without changing monetary preview logic. Source equivalence is independently checked after PHP JSON/date configuration substitution. Durable accounting authority remains in existing services/controllers. Create rejection responses use plain session error arrays instead of nonserializable validator objects. These bounded response changes are explicit source exceptions, not a claim that all controllers are unchanged.

[Current actual gallery and coverage limits](ui-evidence/product-ux/component-gallery.html).

## Compact native control ownership (2026-10-29)

`studio-controls.js` owns dynamically mounted native date groups, button/popover row menus and explicitly marked icon actions. It performs no data requests or business mutations; initialization is idempotent and batched. Menus preserve native links/buttons, Escape, keyboard movement, focus return and RTL positioning. `studio-create-dialog.css` owns sizing and nested section spacing; native runtime owns pending/dirty/confirmed-success lifecycles. Finance's outer submit remains associated with the existing canonical form. Selected-patient identity and clear controls update that same canonical select; no parallel patient state or additional query is introduced.

[Actual changed component evidence and limitations](ui-evidence/product-ux/component-craft.json). Source-only preservation is relative tobc7; the broader branch still includes its explicitly documented theme and create-response exceptions. Kiosk touch/PIN controls and legal/native chart evidence are intentional separate surfaces, not compact CRUD dialogs.

## Settings and ledger-first finance refinement

[Current actual gallery](ui-evidence/product-ux/controls-gallery.html) and [scoped receipt](ui-evidence/product-ux/settings-finance-controls.json) cover integrated icon password controls, native time input groups, native file-selector styling, equal-height patient toolbar controls, balanced booking fields and full-width finance ledger. Existing canonical totals were moved above the ledger, without changing their producers/calculations. Action routes, fields, CSRF and permission checks remain unchanged.24 initial read-only cases,8 settled-state confirmations and14 tenant-dialog interaction cases pass; no writes. Native OS pickers remain intentional. Existing independent acceptance/recovery/release gates remain open.
