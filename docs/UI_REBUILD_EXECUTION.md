# Denta+ approved frontend rebuild: execution contract

Status: active clean-base implementation; Stage A foundation and Stage B patient registration are in progress. The Owner accepted the dashboard direction at 99c9ecbc9d87311d887fcc81c138f42fc4a03af4. This is not approval of the rejected V3/V4 implementation or permission to deploy.

## 1. Authority and restart gate
- Read this file, DESIGN.md, UI_REBUILD_LOCK.json and the next incomplete rows of UI_REBUILD_COVERAGE.csv before each implementation session.
- The accepted dashboard, actual captures and Owner decisions govern visual behavior. Historical prototypes remain inspiration where compatible; rejected layouts and prior completion claims do not govern.
- Before editing: verify branch/HEAD, working changes, release ref and protected scope. Inspect incomplete work rather than restarting it. End a session with exact HEAD, changed paths, verification, open findings and the next bounded task in the ledger/lock.
- Do not rely on chat memory, screenshot counts or a successful render as proof of completion. Never mark a module complete while its forms, details, dialogs or generated controls remain unreviewed.

## 2. Clean source strategy
Verified GitHub release: 89352fd20e0d5166e81f631cb743bead1f596a68.
Clean transition completed in the existing isolated worktree. Current branch: `feature/denta-ui-approved-system`, clean-base transfer commit `503f052f919548edbc9b6f1209edc72505bd67c0`, direct parent exactly the release above. Rejected V3/V4 ancestry was not included.

AGY cleanup independently accepted and preserved locally on the historical branch at `f555c76fe5f44a4938020d94f4fbc6b137a1d04b`: 27 rejected additions absent, 56 baseline restorations byte-identical, protected source unchanged. One correction to AGY evidence: restored baseline files contain inherited whitespace flagged by diff --check; Codex did not rewrite them. Clean transfer contains only nine accepted proof paths, four governance files, PRODUCT.md and two reviewed nonvisual dependencies. Skip-link translations now belong to Studio. Dashboard transfer acceptance: 41 checks passed. No remote push was made during this cleanup/transfer.

Historical PR #21 remains an untouched open draft on the old branch. Preserve historical branches/galleries; do not reset, prune or delete them. No new worktrees. Original checkout and reference kit remain protected.

## 3. Frozen visual and interaction rules
- Preserve the approved forest/sage palette and colored, borderless surfaces. Preserve clear keyboard focus outlines.
- Main operational text 17-18px; ordinary supporting text at least 14px. Do not shrink text to fit columns. Use responsive composition instead.
- Put headings, contextual copy and actions into purposeful filled surfaces. Do not turn ordinary text into fake buttons or create a card around every individual text node.
- Each attention task has its own filled inner card: task title, truthful quantity/context and named action.
- No directional arrow/chevron decorations anywhere, including popup dialogs, menus, table rows and cards. Use named Previous/Next controls where calendar navigation needs them. Preserve keyboard arrow-key behavior and native accessibility.
- Language switches to the other locale in one click.
- Entity rows/cards are clickable across their surface using real authorized destinations. Preserve patient links, text-selection considerations, modifier-click, keyboard behavior, bulk-selection checkboxes, inline actions and form controls. Do not invent a detail destination for aggregate/total rows.
- One consistent first-party outline icon system; no decorative mixed icon fonts.
- Layout is task-specific. Do not clone the dashboard composition into a finance form or clinical workspace.
- No imports of rejected v4-/warm- styles or old shell overrides. Third-party functional widget CSS may be retained with deliberate, scoped styling and verified behavior.
- Unsupported operations are visibly unavailable, never fake success. Disabled features stay OFF.

## 4. Coverage contract
The initial ledger covers 214 current view files, including 201 baseline views. These are files, NOT 214 independent screens.
Before broad implementation, reconcile every file with actual routes, controller rendering, parent/child includes and runtime-generated DOM. Add route/state rows where one template serves multiple workflows. Record unreachable/demo candidates; do not delete them based on filenames or grep alone.

Every applicable surface needs: list/search/filter/pagination; create/edit/detail; dialogs/drawers/confirmations; validation/errors/loading/empty/success; keyboard/focus/unsaved-data behavior; EN/AR; 1586/1366 desktop, 768 tablet and 390 mobile; increased zoom; permitted/denied roles.
Record actual URLs with synthetic IDs, captures, focused checks and remaining exceptions. A file's presence in the inventory is not route acceptance.

## 5. Internal delivery stages
| Stage | Deliverable and stop condition |
|---|---|
| A: clean foundation | Clean-base proof, vetted dependencies, shared shell/component catalogue, protected hashes and baseline measurements. No rejected visual assets loaded. |
| B: reception/patients | Dashboard, patient registry/registration/edit/detail, appointment calendar/dayboard/booking/status dialogs and their full workflows. |
| C: clinical | Patient workspace surroundings, examinations, treatments, prescriptions, contracts and signing interfaces. Preserve native chart/rendering and frozen evidence. |
| D: money/stock | Finance/treasury, invoice/payment/refund/settlement presentation, providers, suppliers, inventory/batches/reorder/receiving and reports. Preserve canonical calculations and writers. |
| E: clinic management | Every settings tab, users, roles, profile, activity/notifications, clinic selection, authentication/recovery and all related forms/details. |
| F: independent planes | Every Control Plane dashboard/list/create/edit/detail/settings/operations/billing/roles/onboarding flow; every kiosk verification/result/signing state. Keep separate authority/privacy boundaries. |
| G: closure | Resolve ledger gaps, check all reachable navigation, one scoped final integration acceptance, final gallery and exact-head review; one coherent draft PR and truthful handback. |

Within a stage: inspect baseline contracts, rebuild markup using the shared system, inspect actual rendered screenshots, run only change-relevant synthetic interaction checks, fix findings, commit and update the ledger. Continue internally; no routine Owner approval after every page. Escalate actual policy/backend exceptions or material unresolved risk.

## 6. Regression and drift gates
- Freeze backend/config/schema/dependency/permission/feature-gate source hashes against the chosen release. Fail on unexpected changes; no controller/model/service rewrites to make a visual task easier.
- Preserve routes, field names, IDs/selectors used by scripts, endpoints, method/CSRF, validation and permission checks. Map these before replacing markup, including inline/dynamic scripts.
- Explicit preservation exceptions: native odontogram SVG/rendering/snapshots, signed clinical evidence and legal/clinical printable output. Redesign surrounding controls; do not silently restyle stored or signed evidence. Any unchanged visible exception must be named.
- Verify session versus Bearer boundaries and denied/cross-clinic UI paths where changed entrypoints warrant it. Hidden controls never replace server authorization.
- Use existing disposable synthetic environments; no business database or customer fixtures. No production writes, migrations, feature activation, deployment or source integration.
- Browser check the real actions, not just selectors. Review screenshots at their actual viewport and readable scale. Include associated forms and popup states; compare against the accepted component vocabulary.
- Establish before/after route and asset measurements on the clean base. Avoid added queries, repeated requests, global widget loading and oversized bundles. Report existing server bottlenecks separately.
- Keep screenshots/browser profiles/scratch and complete archives outside Git. Commit only a small final evidence set if useful. Remove rejected assets from the NEW candidate only after a reference/runtime audit proves they are unused.
- Final review covers the frozen actual delivered tree. Do not claim external approval where it was not obtained.

## 7. Cleanup and release boundaries
A clean rebuild does not require deleting the old work. Preserve original checkout, reference kit, old branches/worktrees, backups, corrupted staging evidence and existing fixtures.
Do not perform repository-wide cleanup, pruning, reset, branch deletion, dependency changes or mass formatting. Normalize only the new candidate's presentation assets as needed.
No automatic AGY delegation: only explicit $agy-manual under the Owner's current instructions. No GitHub Actions.
No promise of mathematically zero regression: require contract preservation, protected-source verification, actual workflow acceptance and a reversible release candidate.
Stop at the consolidated draft/Owner visual acceptance gate. Merge, production deployment, migrations and activation require separate authorization.

## 8. Current implementation checkpoint
- Shared form system: readable filled controls, responsive sections, medical-alert switches, validation containers and unsaved-discard dialog. Page assets load only on the form; native server submission retained.
- Patient registration rebuilt from baseline contracts: all original field names, canonical toggle script, CSRF, cancel/return context and save/save-and-book actions preserved. EN/AR 1586 desktop and 390 mobile: 36 checks passed. Canonical successful POSTs created only synthetic patients 16/17; booking preselected patient 17.
- Historical finding (now corrected under explicit Owner authorization): invalid POST lost old input and validation errors on BOTH untouched baseline and candidate. External diagnostic found `Patient::store()` persists the Validation object whose view/loader/autoloader contains registered Closures; session serialization throws. Owner authorized the narrow fix on 2026-10-25: remove only the full Validation-object flash in Patient::store; retain canonical scalar errors, old input and all validation/mutation logic. Exact baseline and approved file blobs are pinned as the sole backend exception in UI_REBUILD_LOCK.json. No other protected backend differences are accepted.
- Routed checks: 13 passed, one recorded baseline failure. CSRF-forged complete POST rejected without adding a patient. Existing fixture fingerprint comparison did not execute after a test-selector ambiguity and is not claimed. Two new synthetic fixtures were appended; no cleanup/deletion performed.
- Local measured registration HTML: release 231272 characters versus candidate 35679; script tags 20 versus 3; stylesheets 13 versus 3; observed query header 143 versus 117. Single local samples, not production latency or query-optimization claims. No backend query code changed.
- Evidence remains outside source in `../clean-foundation/`; accepted dashboard gallery remains `../studio-round4/index.html`.
- Manual source boundary/inventory gate: `php scripts/ci/frontend_rebuild_guard.php`. `--final` must fail while ledger entries remain unfinished. No GitHub Actions.
- Next bounded task: map and rebuild patient registry using existing bounded endpoint, filters, pagination and export/row actions, then edit/detail presentation. Do not claim other restored pages have been redesigned.

### Authorized validation correction
- Actual EN desktop / AR mobile failed browser POSTs now retain required/optional/medical-alert values and show linked field errors: 15/15 focused checks, no JS errors. All existing synthetic patients matched the before/after query snapshot; no new records in this verification.
- Evidence: `../clean-foundation/registration-validation-fixed.json` and `patient-validation-fixed-en.png` / `patient-validation-fixed-ar.png`. Original failed evidence remains preserved; it is not rewritten as a pass.
- The registration view remains pending the full route/role/viewport acceptance batch; this fix does not certify the whole frontend. Current local preview 127.0.0.1:57131 reads this worktree directly; refresh to see changes. Optional gates remain OFF.

### Registration proportion refinement / manual design experiment
Owner requested varied control widths and less generic form framing. Registration now uses a compact identity panel with a name row and content-sized phone/date/gender row. Optional contact/insurance and medical alerts occupy separate desktop columns and stack responsively; redundant explanatory paragraphs removed. Inputs remain readable at 17px with approximately 44px height. Original field names, canonical toggles, endpoints and scripts remain intact. No backend/database changes in this pass. Focused EN/AR desktop, laptop, tablet and phone acceptance: 46/46; screenshots in `../registration-refined/`.

A patient-registry design-only experiment is prepared for manual AGY in `../manual-patient-registry/brief.md`. It supplies written approved language and workflow contracts, forbids reading existing UI code/current pages/history, and limits output to an external standalone synthetic concept. It does not authorize application source edits or automatically start AGY. After the Owner returns, inspect actual output before integrating anything. This is not app-route completion.

### Owner acceptance / card-width correction
2026-10-25: Owner generally accepted registration, except that reducing field sizes had incorrectly narrowed the whole form. Removed the 1120px form cap; page cards/actions now use the full available workspace while content-sized field groups remain. DESIGN.md now records all cumulative Owner notes, including "size fields, not their containing card", proportionate controls and restrained guidance. No backend/database changes. AGY is preparing an external design-only concept; the Owner explicitly permitted Codex to continue this page concurrently. AGY has no permission to edit application source, so outputs are disjoint. Keep its original handoff baseline and attribute subsequent Codex commits separately.

Owner follow-up: removing the outer cap alone still left wasted space in capped inner groups. Replaced the two inner groups with one weighted five-column desktop grid; intermediate screens use complete 2+3 rows, phones use 2+1+2. All rows fill available content width while controls retain 17px text and approximately 44px height. Six focused EN/AR viewport checks passed with no overflow; actual desktop/mobile captures inspected in registration-refined/arranged-*.png. No field names, scripts, backend or database changed.
