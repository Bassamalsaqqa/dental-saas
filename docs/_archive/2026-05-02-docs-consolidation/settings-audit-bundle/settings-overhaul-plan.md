# Settings Overhaul Execution Plan

> Scope: UX/UI overhaul first, with minimal structural cleanup where needed to remove dead paths, legacy duplication, and settings-surface ambiguity.
> Non-goal: no business-rule changes, no permission model changes, no tenant isolation changes.
> Constraint: keep existing working routes and POST handlers unless a route is clearly legacy or dead.
> Design reference: tenant settings is the primary token reference, not a literal copy target.

---

## Executive Summary

The app does not only have a styling inconsistency problem. It has three overlapping settings problems:

1. Visual inconsistency between tenant and control plane settings.
2. Information-architecture drift, where some configuration lives outside the main settings shell.
3. Legacy route/view drift, especially in tenant settings, where historical endpoints still exist alongside the consolidated shell.

Because of that, a safe settings overhaul for this app must do two things:

1. Standardize the design system and interaction rules.
2. Clean up the structure enough that the result is coherent and maintainable.

If we do only cosmetic work, the app will look better but still behave like multiple generations of settings UI stitched together.

---

## Current State

### Tenant plane

Canonical settings shell:
- `app/Views/settings/index.php`
- `app/Controllers/Settings.php`

Current tenant tabs:
- Clinic Profile
- Subscription
- System
- Operations
- Kiosk
- Backup and Restore
- Communications

Current communications subtabs:
- Channels
- Email
- SMS
- WhatsApp
- Alerts

Current strengths:
- URL-driven tab and subtab state already exists.
- RTL breadcrumb handling already exists.
- `.ui-toggle` already exists.
- Button loading helper already exists.
- Communications is already consolidated into the main shell.

Current weaknesses:
- The file is very large and mixes markup, styling, and behavior in one place.
- Different sections still use different CTA colors and emphasis levels.
- Legacy routes and legacy settings pages still exist.
- Notifications legacy page still exists as a secondary path.

### Control plane

Canonical settings shell:
- `app/Views/control_plane/settings.php`
- `app/Controllers/ControlPlane/Settings.php`

Current CP tabs:
- Platform Profile
- Clinic Defaults
- Governance
- Communications

Current CP communications subtabs:
- Email Templates
- WhatsApp

Current strengths:
- URL-driven tab and subtab state already exists.
- Communications configuration is already moved into settings.
- WhatsApp live operations remains separate, which is correct.

Current weaknesses:
- Visual tokens still differ materially from tenant settings.
- Forms, labels, focus states, buttons, and sidebars are inconsistent.
- CP settings still relies on large inline JS blocks.
- CP settings, CP operations, and CP WhatsApp are related but not yet expressed as one coherent product model.

### Adjacent settings-related surfaces

These are not settings pages, but they must align with the settings overhaul:
- `app/Views/control_plane/operations.php`
- `app/Views/control_plane/whatsapp.php`
- `app/Views/settings/notifications.php`
- `app/Views/components/smtp_form.php`

---

## Product Rules

These rules should govern every implementation step.

### Rule 1: One canonical home per configuration concern

Every configuration feature should have exactly one canonical home inside the relevant settings shell.

Examples:
- Tenant kiosk config belongs in tenant settings.
- Tenant communication-channel config belongs in tenant settings.
- CP platform defaults belong in CP settings.
- CP WhatsApp credentials and policies belong in CP settings.

### Rule 2: Operations are not configuration

Operational dashboards may stay outside settings, but must be clearly linked from settings as operational surfaces.

Examples:
- `controlplane/whatsapp` stays an operations surface.
- `controlplane/operations` stays an operations surface.

### Rule 3: URL state is required

All tabs and subtabs must be deep-linkable, refresh-safe, and back-button-safe.

### Rule 4: Shared interaction contract

Every settings form should follow the same interaction model:
- clear section ownership
- explicit save action
- loading state on submit
- consistent success/error feedback
- consistent inline validation treatment

### Rule 5: Minimal structural cleanup is allowed

We are allowed to:
- retire or redirect legacy views
- remove clearly dead settings routes
- split giant view files into partials
- move repeated JS helpers into shared assets

We are not allowed to:
- change underlying business rules
- change permission semantics
- redesign data models

---

## Final Target Information Architecture

### Tenant settings

Settings
- Clinic Profile
- Subscription
- System
- Operations
- Kiosk
- Backup and Restore
- Communications

Communications
- Channels
- Email
- SMS
- WhatsApp
- Alerts

Legacy target:
- `settings/notification-settings` should end as a compatibility redirect or a compatibility shell, not a separate long-term experience.

### Control plane settings

Global Configuration
- Platform Profile
- Clinic Defaults
- Governance
- Communications

Communications
- Email Templates
- WhatsApp

Outside but linked
- WhatsApp Command Center
- Operations Console

---

## Design System Rules

These are the shared tokens we should standardize across settings surfaces.

### Shared visual language

- Primary page background:
  - `bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100`
- Primary action:
  - `bg-gradient-to-r from-blue-500 to-indigo-600`
- Primary hover:
  - `hover:from-blue-600 hover:to-indigo-700`
- Standard dark section banner:
  - `bg-gradient-to-r from-slate-700 to-slate-900`
- Standard card wrapper:
  - `bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden`

### Form primitives

- Input/select/textarea:
  - `w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200`
- Label:
  - `block text-sm font-semibold text-gray-700 mb-2`
- Help text:
  - `text-xs text-gray-500 mt-1`

### Navigation

- Sidebar container:
  - `bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 p-6`
- Active nav item:
  - `bg-gradient-to-r from-blue-500 to-indigo-600 text-white`
- Inactive nav item:
  - `text-gray-600 hover:bg-gray-50 hover:text-gray-900`

### Toggles

- Use `.ui-toggle` as the shared visual pattern for settings toggles.
- If there are checkbox patterns outside settings shells, only migrate them if they are part of settings or settings-adjacent UX.

### Feedback

- Use one toast pattern across settings pages.
- Use one button loading helper across settings pages.
- Prefer graceful degradation to existing flash messages rather than replacing every existing success flow immediately.

---

## Implementation Strategy

This should be executed in phases, in order.

### Phase 0: Structural cleanup before visual pass

Goal: remove stale assumptions and define the actual settings surface map.

Tasks:
- Audit tenant settings routes in `app/Config/Routes.php` against actual methods in `app/Controllers/Settings.php`.
- Identify legacy or dead routes and mark them for redirect, retention, or removal.
- Confirm which settings-adjacent pages remain canonical versus legacy.
- Preserve working endpoints that current views still use.

Files:
- `app/Config/Routes.php`
- `app/Controllers/Settings.php`
- `app/Controllers/ControlPlane/Settings.php`

Acceptance:
- No settings route points to a method that does not exist.
- Every retained settings route has a clear reason to exist.

### Phase 1: Modularization foundation

Goal: make the overhaul maintainable.

Tasks:
- Split tenant settings main view into tab partials.
- Split CP settings main view into tab and subtab partials.
- Extract repeated settings JS helpers into shared assets where practical.
- Keep behavior unchanged while refactoring layout structure.

Preferred target shape:
- tenant:
  - `app/Views/settings/index.php` as shell
  - partials under `app/Views/settings/partials/`
- control plane:
  - `app/Views/control_plane/settings.php` as shell
  - partials under `app/Views/control_plane/settings_partials/`

Acceptance:
- No visual change required yet.
- Rendered output remains functionally equivalent.
- Inline JS footprint is reduced where safe.

### Phase 2: Shared interaction contract

Goal: standardize feedback and save behavior.

Tasks:
- Unify `setButtonLoading()` behavior across tenant and CP settings.
- Unify toast usage across settings shells.
- Keep existing flash-message redirects as fallback.
- Ensure primary submit buttons visually indicate in-flight save state.

Files:
- `app/Views/settings/index.php`
- `app/Views/control_plane/settings.php`
- `app/Views/control_plane/operations.php`
- `app/Views/settings/notifications.php`

Acceptance:
- All primary settings forms have loading feedback.
- All AJAX settings actions use the same toast convention.

### Phase 3: Tenant settings refinement

Goal: tighten the tenant shell without breaking the consolidated model already in place.

Tasks:
- Normalize CTA hierarchy.
- Normalize card/banner treatment.
- Normalize communications subtab treatment.
- Keep URL tab/subtab logic intact.
- Keep tenant settings as the main source of token truth.

Important note:
- Do not blindly recolor every tenant button blue. Preserve semantic variants where they add clarity, but ensure primary save actions are visually consistent.

Acceptance:
- Tenant settings feels internally consistent.
- Communications remains the single canonical home for tenant notification/channel config.

### Phase 4: Control-plane settings parity pass

Goal: bring CP settings to the same design system and interaction quality as tenant settings.

Tasks:
- Align background, sidebar, form tokens, banners, labels, inputs, help text, and CTAs.
- Add RTL-aware breadcrumb and sidebar alignment parity.
- Standardize communications subtab treatment.
- Maintain CP-specific identity where appropriate.

Important note:
- This is parity in system quality, not literal clone mode.
- CP can keep its own product voice while sharing design primitives.

Acceptance:
- CP settings looks like the same product family as tenant settings.
- No mixed green/orange/purple/slate focus-ring chaos remains in CP settings forms.

### Phase 5: Settings-adjacent surface alignment

Goal: ensure related pages reinforce the new settings structure.

Tasks:
- Update legacy tenant notifications page into a compatibility shell or redirect target.
- Standardize the settings-link banner in CP WhatsApp.
- Align CP operations tab interaction tokens with the new settings system.
- Normalize `smtp_form.php` tokens to match settings forms.

Files:
- `app/Views/settings/notifications.php`
- `app/Views/control_plane/whatsapp.php`
- `app/Views/control_plane/operations.php`
- `app/Views/components/smtp_form.php`

Acceptance:
- No settings-adjacent page feels visually or behaviorally orphaned.

### Phase 6: Retirement and redirect cleanup

Goal: remove or de-emphasize legacy settings paths after the new shell is stable.

Tasks:
- Redirect legacy tenant notifications path if appropriate.
- Remove dead settings-specific route aliases if no longer needed.
- Confirm that all navigation points to canonical settings destinations.

Acceptance:
- Users cannot reasonably end up in two different long-term settings experiences for the same concern.

---

## Execution Rules For Codex

These are the operational instructions to follow while implementing.

1. Do not make business-logic changes unless required to preserve existing settings behavior during refactor.
2. Preserve all working form actions and controller handlers until route cleanup phase explicitly removes them.
3. Prefer extracting partials over expanding monolithic files further.
4. Prefer shared helpers over duplicating inline JS across settings-related pages.
5. Preserve tenant/control plane separation at all times.
6. For any change that risks breaking deep links, keep `tab` and `subtab` URL behavior unchanged.
7. If a prompt conflicts with the current repo state, trust the repo, not the prompt.

---

## Risks

### Risk 1: Styling-only execution hides structural debt

Mitigation:
- Complete Phase 0 before visual changes.

### Risk 2: Partial parity without modularization increases future complexity

Mitigation:
- Do not skip Phase 1.

### Risk 3: CP over-cloning tenant settings damages product clarity

Mitigation:
- Use tenant as token reference, not literal layout template.

### Risk 4: Legacy routes or legacy views continue to confuse users

Mitigation:
- Explicitly retire or redirect them in Phase 6.

---

## Acceptance Checklist

### Architecture
- Every configuration concern has one canonical home.
- Tenant settings remains the canonical tenant configuration shell.
- CP settings remains the canonical CP configuration shell.
- Operations surfaces remain outside settings but are clearly linked.

### UX
- All settings tabs and subtabs are deep-linkable.
- All primary forms show loading state on submit.
- All AJAX settings actions use consistent toast behavior.
- Breadcrumbs and sidebar interactions are consistent in LTR and RTL.

### Visual system
- Tenant and CP settings share one design system.
- CP settings no longer mixes unrelated focus-ring colors and CTA systems.
- Settings-adjacent pages align with the new design language.

### Maintainability
- Giant settings files are broken into partials where practical.
- Repeated helpers are extracted or standardized.
- Legacy settings surfaces are either retired or explicitly maintained as compatibility surfaces.

---

## Recommended Execution Order

1. Phase 0
2. Phase 1
3. Phase 2
4. Phase 3
5. Phase 4
6. Phase 5
7. Phase 6

This order matters. If we skip straight to visual polish, we will improve screenshots without actually fixing the settings experience.
