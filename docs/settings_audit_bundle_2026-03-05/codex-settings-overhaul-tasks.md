# Codex Settings Overhaul Tasks

This task file translates the current settings audit, overhaul plan, and assessment into an executable sequence for this repository.

The goal is not a blind redesign. The goal is to tighten information architecture, reduce drift, preserve working behavior, and then improve UI/UX on top of that cleaner base.

## Execution Rules

- Preserve tenant/control-plane isolation.
- Preserve existing `tab` and `subtab` URL behavior unless a task explicitly improves it.
- Prefer compatibility aliases and redirects before route removal.
- Do not move operational dashboards into settings unless the task explicitly says so.
- Treat tenant settings as the shared token and interaction reference, not as a literal clone target.

## Phase 0: Route and Legacy Cleanup

### Task 0.1: Canonicalize tenant notification settings paths
Status: `completed`

Scope:
- make the communications alerts subtab the canonical destination
- keep compatibility support for legacy GET and POST paths
- remove the mismatch between current forms and current route registration

Files:
- `app/Config/Routes.php`
- `app/Controllers/Settings.php`
- `app/Views/settings/index.php`
- `app/Views/settings/notifications.php`

Acceptance:
- `GET /settings/notification-settings` redirects to `/settings?tab=communications&subtab=alerts`
- notification preference forms submit to one canonical POST endpoint
- legacy POST alias still works during transition

### Task 0.2: Produce full settings route decision table
Status: `completed`

Scope:
- inventory every tenant/control-plane settings-related route
- classify as canonical, compatibility alias, redirect, or dead
- attach owner plane and target surface

Files:
- `app/Config/Routes.php`
- `docs/settings_audit_bundle_2026-03-05/settings-route-decision-table.md`

Acceptance:
- every settings-related route appears exactly once in the table

### Task 0.3: Decide legacy tenant settings surfaces
Status: `completed`

Scope:
- decide fate of legacy tenant settings views that are no longer canonical
- document whether each remains, redirects, or is removed later

Files:
- `app/Views/settings/notifications.php`
- `docs/settings_audit_bundle_2026-03-05/settings-route-decision-table.md`

Acceptance:
- no legacy settings surface remains undocumented

### Task 0.4: Repoint dead tenant GET settings routes
Status: `completed`

Scope:
- convert dead legacy GET routes into explicit canonical redirects
- avoid missing-method failures from old bookmarks or links

Files:
- `app/Config/Routes.php`

Acceptance:
- `GET /settings/backup` redirects to `/settings?tab=backup`
- `GET /settings/security` redirects to `/settings?tab=system`

### Task 0.5: Reactivate tenant backup actions and retention save
Status: `completed`

Scope:
- wire the backup tab to real controller actions instead of dead routes
- keep restore explicit rather than silently broken
- make tenant backup retention save work in global mode

Files:
- `app/Controllers/Settings.php`
- `app/Config/Routes.php`
- `app/Views/settings/index.php`

Acceptance:
- backup creation works from the tenant settings backup tab
- backup download uses a validated controller action
- restore returns explicit feedback instead of a missing-method failure
- retention save has a live route

### Task 1.1: Split tenant settings shell into tab partials
Status: `completed`

## Phase 1: Modularization

Scope:
- keep one shell view
- move each major tab and communications subtab into partials
- preserve current JS tab state and form behavior

Files:
- `app/Views/settings/index.php`
- `app/Views/settings/partials/*`

Acceptance:
- tenant settings behavior unchanged
- shell file materially smaller

### Task 1.2: Split control-plane settings shell into tab partials
Status: `completed`

Scope:
- same approach as tenant shell
- keep current `tab` and `subtab` behavior

Files:
- `app/Views/control_plane/settings.php`
- `app/Views/control_plane/settings_partials/*`

Acceptance:
- control-plane settings behavior unchanged
- shell file materially smaller

## Phase 2: Shared Interaction Contract

### Task 2.1: Standardize save-state behavior
Status: `pending`

Scope:
- define and apply consistent save/loading/error behavior
- distinguish POST-redirect forms from AJAX forms and immediate toggles

Files:
- tenant and control-plane settings views
- shared JS assets as needed

Acceptance:
- each settings interaction type follows one consistent pattern

### Task 2.2: Standardize inline validation and summary feedback
Status: `pending`

Scope:
- ensure invalid fields and save failures communicate clearly
- avoid persistent stale warnings after correction

Acceptance:
- validation behavior is consistent within each plane

## Phase 3: Tenant Settings Refinement

### Task 3.1: Tighten tenant settings visual consistency
Status: `pending`

Scope:
- unify cards, tab buttons, section headers, and CTA hierarchy
- keep RTL/LTR spacing parity

### Task 3.2: Retire or isolate legacy tenant settings screens
Status: `pending`

Scope:
- once compatibility window is satisfied, either remove legacy views or leave only explicit redirect shims

## Phase 4: Control-Plane Settings Parity

### Task 4.1: Align control-plane settings tokens with tenant settings
Status: `pending`

Scope:
- typography, spacing, inputs, button hierarchy, tabs, empty states
- preserve plane-specific IA

### Task 4.2: Clarify settings-adjacent links
Status: `pending`

Scope:
- settings should link to operations surfaces cleanly
- operations surfaces should link back to configuration origin where appropriate

## Phase 5: Settings-Adjacent Surface Cleanup

### Task 5.1: Control-plane operations discoverability cleanup
Status: `pending`

### Task 5.2: Control-plane WhatsApp config/ops bridge cleanup
Status: `pending`

## Phase 6: Verification and Retirement

### Task 6.1: Browser verification matrix
Status: `pending`

Scope:
- deep links
- refresh persistence
- browser back/forward
- RTL/LTR
- save flows
- compatibility redirects

### Task 6.2: Final legacy path retirement
Status: `pending`

Scope:
- remove compatibility aliases only after all canonical paths are verified and documented
