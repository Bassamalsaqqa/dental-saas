# Denta+ approved frontend rebuild: execution contract

Status: planning checkpoint. The Owner accepted the dashboard direction at 99c9ecbc9d87311d887fcc81c138f42fc4a03af4. This is not approval of the rejected V3/V4 implementation or permission to deploy.

## 1. Authority and restart gate
- Read this file, DESIGN.md, UI_REBUILD_LOCK.json and the next incomplete rows of UI_REBUILD_COVERAGE.csv before each implementation session.
- The accepted dashboard, actual captures and Owner decisions govern visual behavior. Historical prototypes remain inspiration where compatible; rejected layouts and prior completion claims do not govern.
- Before editing: verify branch/HEAD, working changes, release ref and protected scope. Inspect incomplete work rather than restarting it. End a session with exact HEAD, changed paths, verification, open findings and the next bounded task in the ledger/lock.
- Do not rely on chat memory, screenshot counts or a successful render as proof of completion. Never mark a module complete while its forms, details, dialogs or generated controls remain unreviewed.

## 2. Clean source strategy
Verified GitHub release: 89352fd20e0d5166e81f631cb743bead1f596a68.
Current proof branch: feature/denta-v4-rebuild at 99c9ecb, clean at inspection and four commits ahead of its GitHub branch.
GitHub PR #21 is still the older open draft on feature/warm-clinical-v3-20261023 at a09a933. It does not contain the approved proof.

Do not continue the application-wide rebuild on top of the current branch's 95-file historical delta.
1. Preserve the current branch, accepted proof and galleries. Verify source backup/synchronization and that feature-ref updates cannot trigger Actions/deployment before pushing anything.
2. Recheck the release ref. If advanced, reconcile the actual changes before selecting a baseline; do not overwrite them.
3. In the EXISTING isolated worktree, create one fresh feature branch from the verified production-derived release. No new worktree, detached session, reset, force-push or deletion.
4. Transfer only the nine approved proof paths pinned in the lock. Audit their nonvisual dependency closure first.
5. Known dependencies: layouts/partials/page_context.php, layouts/partials/view_permissions.php and the V4.skip translation reference. These are absent from the release. Review/recreate necessary presentation glue, move the skip label into Studio translations, and remove the accidental V4 dependency. Do not blindly copy the rejected shell or assets to make the page run.
6. Reproduce the accepted page on that clean base and compare screenshots/functionality. Only then normalize CSS into a maintainable component system and expand it.
7. Keep the old PR/branches as historical evidence until explicit disposition. Prepare one consolidated draft against the release when appropriate; do not silently repurpose PR #21 or create per-module PRs.

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
