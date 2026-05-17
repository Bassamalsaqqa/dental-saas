# Appointment Calendar Workspace Plan

Status: active phased plan for the current PHP appointment calendar.

## Current State

- `/appointment` and `/appointments` route to the appointment list/daily workspace.
- `/appointment/calendar` and `/appointments/calendar` route to the FullCalendar page.
- Calendar access currently requires `appointments:calendar`; list access requires `appointments:view`.
- The calendar uses the existing session-authenticated tenant web routes and the existing `/appointment/calendar-events` feed.
- The default calendar view was a month grid, which is useful for overview but weak for clinic scheduling because appointment times and duration are compressed.

## Decision

Do not immediately replace `/appointment` with `/appointment/calendar`.

The safer path is to improve the calendar into a professional schedule view first, then decide whether `/appointment` should default to daily list, calendar, or a saved user preference.

Reasons:

- `/appointment` already contains daily list workflow, filters, operational cards, and quick-create behavior.
- `/appointment/calendar` has a stricter `appointments:calendar` permission than `/appointment`.
- A hard redirect from `/appointment` to `/appointment/calendar` could remove access for users who can view appointments but do not have calendar permission.
- Calendar and list views should eventually feel like one appointment workspace, not two unrelated pages.

## External UX Basis

The plan follows standard scheduling calendar behavior:

- Time-grid day/week views are better for operational scheduling than month grids.
- Business hours should be visually emphasized.
- Current-time indicators are useful in agenda/day/week views.
- Event rendering should expose time, patient, duration, status, and provider/chair context.
- RTL and locale configuration must be explicit for Arabic.

Reference docs:

- FullCalendar time-grid/date display/event rendering/business hours/direction/locale options.
- Dental/clinic scheduling systems commonly expose provider/chair/operatories, schedule columns, and configurable schedule views.

## Phase 1 - Calendar Readability And Schedule View

Goal: make `/appointment/calendar` useful as a real schedule page without changing routes, controller contracts, permissions, or appointment writes.

Scope:

- Default desktop calendar to week schedule view.
- Default mobile calendar to day schedule view.
- Keep month view available as an overview.
- Improve appointment event rendering so time, patient, type, chair/provider, status, and duration are easier to scan.
- Use viewport-aware height instead of a cramped fixed calendar.
- Configure slot duration, label interval, now indicator, scroll time, and business hours.
- Add explicit RTL direction and locale settings.
- Remove debug console logging.
- Preserve current `/appointment/calendar-events` payload shape.
- Preserve event click to appointment show page.
- Preserve date click behavior for now.

Non-goals:

- No drag/drop or resize.
- No backend endpoint changes.
- No new database fields.
- No default route switch.
- No permission changes.

## Phase 2 - Create Flow Integration

Goal: keep scheduling context when starting an appointment from the calendar.

Scope:

- Date/time click opens the existing appointment create popup instead of navigating to the full create page.
- Prefill clicked date/time.
- Preserve fallback full-page create link.
- Keep existing appointment store endpoint, CSRF, tenant checks, subscription checks, permission checks, validation, and return behavior.

Non-goals:

- No AJAX persistence rewrite.
- No new appointment store endpoint.

## Phase 3 - Unified Appointment Workspace

Goal: reduce the split between `/appointment` and `/appointment/calendar`.

Scope:

- Add a calendar view mode to the appointment workspace, for example `/appointment?view=calendar`.
- Keep daily list and all-list modes available.
- Share filters where practical: status, patient search, chair/provider, appointment type.
- Keep `/appointment/calendar` as a full calendar route or compatibility alias until a route decision is made.

Decision gate:

- Decide whether `/appointment` should default to daily list, week calendar, or last-used preference.
- Do not make the switch until permission impact is resolved.

## Phase 4 - Professional Scheduling Features

Goal: add richer scheduling behavior only after the basic calendar is stable.

Candidate features:

- Appointment preview drawer with patient, time, duration, chair/provider, status, and quick actions.
- Chair/provider filter chips.
- Chair/provider lane view if implemented safely.
- Conflict indicators.
- Waitlist/no-show/follow-up workflow.
- Daily schedule print/export.

Non-goals until explicitly planned:

- No frontend-only conflict truth.
- No drag/drop writes without server validation.
- No finance or clinical writes from the calendar.

## Verification Checklist

For every phase:

- `/appointment` still loads.
- `/appointment/calendar` still loads.
- `/appointments/calendar` alias still loads.
- Calendar events remain tenant-scoped.
- Existing appointment create/edit/show pages still work.
- Existing appointment popup still works where already used.
- No `/api/v1/*` behavior changes.
- No App API behavior changes.
- PHP syntax checks pass for touched files.
- `npm.cmd run build` passes if view classes or CSS are changed.
- App API focused tests still pass when backend behavior is touched.

