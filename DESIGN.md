---
name: Denta+ clinic overview proof
description: Owner-accepted dashboard direction; application-wide rebuild in progress
colors:
  primary: "#153f36"
  primary-deep: "#10332c"
  action: "#23684e"
  active-navigation: "#c4dc87"
  canvas: "#eaf0eb"
  surface: "#f3f7ef"
  ink: "#102e27"
  muted: "#445f54"
  line: "#c9d8cf"
typography:
  headline:
    fontFamily: "Manrope, Noto Sans Arabic, sans-serif"
    fontSize: "44px"
    fontWeight: 750
    lineHeight: 1.3
    letterSpacing: "-1.25px"
  body:
    fontFamily: "Manrope, Noto Sans Arabic, sans-serif"
    fontSize: "18px"
    lineHeight: 1.55
rounded:
  control: "8px"
  surface: "16px"
spacing:
  compact: "8px"
  regular: "16px"
  section: "24px"
  workspace: "36px"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.surface}"
    rounded: "{rounded.control}"
    padding: "10px 15px"
---
# Denta+ design proof

## Overview
The Owner rejected the previous V4 implementation. It remains preserved at checkpoint 9bf0622 and is not visual authority. The Owner generally approved the one-page direction, requesting stronger overall color and larger text. The latest Owner refinement requires larger text, denser appointment information, one-click language switching, filled text containers and borderless colored cards. The final dashboard direction was accepted at 99c9ecb; the Owner authorized the clean-base application rebuild. The Owner additionally requires distinct inner attention-task cards, full-row table navigation and no directional arrows anywhere, including dialogs and menus.

The accepted dashboard defines the visual anchor. The system now extends through the rebuilt routes recorded in docs/UI_REBUILD_COVERAGE.csv, using Studio shells, semantic theme roles and shared components. Unrebuilt routes remain explicitly pending. No claim of whole-application completion, blanket Owner visual acceptance, external approval or production readiness.

## Colors
A deep forest frame establishes persistent navigation; lime indicates the active location. A crisp reading surface, cool neutral canvas and sage attention region separate operational tasks. Status colors require actual persisted states and visible text.

## Typography
Manrope is the English UI workhorse; Arabic uses Noto Sans Arabic first. Primary operational text is 17–18px; supporting metadata is at least 14px. Headline is 44px desktop with responsive reduction. Form labels are 17px and inputs 18px. Do not shrink primary patient names to metadata size. Fonts currently use Google's existing remote font delivery pattern, not a self-hosting claim.

## Layout
Desktop: 260px navigation, 80px toolbar, 36px workspace insets. Below 1390px the rail is 238px. Appointments lead; the attention queue is complementary. Recently added patients and real task links follow. The appointment table uses existing patient phone, booking resource and duration data without new queries. Below 1450px the attention queue follows the full-width table for readable columns. Below 1150px attention becomes a row; below 800px navigation is an inert-background overlay, the appointment list becomes a vertical record layout, and attention follows it.

## Elevation & Depth
Appointment surface: 0 4px 18px #233f3010. Popovers: 0 16px 55px #112b3029. Dialogs use a dimmed, 5px blurred backdrop to preserve spatial context while isolating the task. No decorative animation.

## Shapes
8px controls, 16px main surfaces, 18px dialogs. Avatar initials use a consistent rounded-square patient treatment and circular account treatment.

## Components
First-party 1.65px outline SVG geometry. No icon fonts, legacy CSS, Tailwind, jQuery or UI framework on the proof route. Language switching is a direct link to the other locale, without a menu. Native dialog/popover semantics; explicit dialog names, Escape, focus return, keyboard tabs and mobile navigation wrap. Search uses the existing session patient search route with bounded results and text-only rendering. Notification content comes from the existing API; marking read remains in the canonical notification centre. Create choices navigate to existing authorized full workflows.

## Do's and Don'ts
Attention items are individually filled task cards with a clear task title, factual queue quantity and named action. Appointment rows use native stretched links across all cells; patient-name links retain their own destination. Preserve keyboard focus and modifier-click behavior. No arrow, chevron or dropdown-arrow decoration in the proof, including popup dialogs. Use filled containers for headings, dates, task links and content; cards have no decorative borders. Preserve visible keyboard focus outlines. Use actual controller data and canonical permission checks. Synthetic fixtures are clearly identified and belong only to the local disposable schema. Preserve backend, tenant, finance, clinical and feature-gate contracts. Extend the accepted system through the coverage ledger; new surfaces still require their own visual and interaction evidence. Do not call a screenshot or passing checks visual approval.


## Form component contract
Use `studio-forms.css` only on form routes. Filled inputs with readable 17px values and approximately 44px height, visible keyboard focus and explicit labels. Size fields by expected content: names have more room; phone, date and gender are compact. Use purposeful groups rather than a uniform two-column form. Supporting copy appears only when it helps the task. Optional clinical details retain canonical enable/disable behavior and original field names. Save and save-and-book remain distinct actual server actions. Unsaved cancel uses a named native dialog, Escape and focus return. No arrow decorations. Validation summaries must be escaped and linked to fields; never claim backend errors work where actual acceptance fails.


## Owner notes: binding across future pages
These are cumulative requirements, not suggestions to rediscover each session. Read this section before each frontend batch.
- Preserve the accepted forest/green strength and readable text. Do not shrink typography just to make the interface compact.
- Fill the available workspace with the page's cards/panels. **Size the fields, not their containing card.** Do not cap the entire form width in response to feedback about oversized inputs. An intentional narrower surface needs a task-specific reason; it is not the default application layout.
- Inputs use content-appropriate widths and heights. Names and narrative text need room; phone, date, gender and other short values do not need to stretch. Not all controls should be equally large or equally small.
- Avoid redundant help text, repeated notices and explanations on ordinary fields. Show actual validation and necessary task-specific guidance.
- Compose each workflow afresh from its task and data, rather than copying the incumbent field grid or merely changing colors. Reuse the accepted component language, not old page hierarchy.
- Filled, borderless cards; contain headings/context in meaningful surfaces. Distinct inner cards for attention tasks. Entire entity rows are actionable without arrow decorations; preserve inline controls, text selection and native links.
- No directional arrows/chevrons, including dialogs and menus. One-click EN/AR switching, proper RTL and accessible keyboard/touch controls.
- Current registration composition is generally Owner-accepted (2026-10-25), with the explicit full-width-card correction recorded above. Do not restart its design or treat unrelated historical designs as authority.
- Current work is presentation-only: no new backend/database changes, migrations or feature activation. The previously authorized one-line validation fix remains the sole pinned backend exception.

- Full-width cards also need a coherent full-width field composition: use proportionate grid columns and complete responsive rows. Do not leave a large unused side area by capping inner field groups. Core registration fields share one weighted desktop row; intermediate widths wrap into a name row and a three-field facts row; mobile uses paired names, full-row phone, paired date/gender.

- Owner accepted patient editing and inventory reorder presentation at source checkpoint c3ff655. Preserve these accepted compositions and improve only substantiated issues; do not redesign them again while expanding coverage.


## Binding Owner acceptance: inventory direction and future themes (2026-10-26)
The stock workspace pilot at e003cb2 is accepted in direction. Its sparse Total / Active card is NOT accepted; replace it with a compact labelled count group in the catalogue heading. Propagate task-focused composition and restrained amber/rust attention cues, not its old layout or isolated hardcoded palette.
- ALL application tables use deliberate fixed column tracks and uniform row height within each table. Expected content determines column widths, never the longest record. Long names/notes do not stretch rows or columns. Preserve full content through record access or a keyboard-accessible cell reader. Do not discard required information.
- Responsive tables retain their full data in a contained, keyboard-accessible horizontal scroll region when columns cannot fit. Never cause whole-page horizontal overflow. Use fixed-height rows at narrow widths too; do not fall back to inconsistent wrapping cards.
- Every system color in rebuilt presentation derives from public/assets/css/studio-theme.css semantic roles. Components may reference roles and transparent mixes only. Pair foreground/background roles; a future theme changes this single source. No theme settings, persistence, permissions, feature switch or database change is authorized now.
- Do not restart accepted forms/dashboard. Extend the component rules consistently while replacing rejected workflow structures. All optional features remain OFF; backend and database are preserved.


## Owner approval and research-first continuation (2026-10-26)
Owner approved the accepted-system continuation at 9d744ab and authorized the remaining application presentation pass. Keep the strong forest/sage identity, content-sized controls, compact labelled summaries, stable table geometry, native whole-row navigation and arrow-free actions. Before designing each workflow family, consult authoritative current UX/accessibility guidance and inspect the actual route/data contract; record the source and concrete application in UI_REBUILD_EXECUTION.md. Old views are behavior evidence only, never visual/layout authority. No blanket cosmetic wrapper, legacy color utility inheritance, invented business facts, fake controls or silent workflow removals. All rebuilt colors resolve to studio-theme.css. Finish safe related pages autonomously; backend/database/production constraints remain binding.


## Owner direction — task architecture takes priority (2026-10-26)
All presentation work through 5a32c38 is SOFT_ACCEPTED only. Earlier acceptance records preserve history, not a requirement to retain their workflow, hierarchy, page inventory, form grouping or information architecture. Reassess every surface from the user's actual task. Consolidate fragmented navigation and split overloaded workspaces where supported by canonical data and permissions. Old routes/templates are behavior evidence, not a specification for composition. Preserve legitimate capabilities, financial/clinical semantics, authorization and durable evidence; do not silently remove workflows or invent policy. Existing strong semantic palette, readable type, proportionate fields, fixed responsive grids, arrow-free controls and one-click language remain current component constraints. Presentation-only and production/database protection remain binding. Research current usability guidance per workflow family and implement useful changes rather than repeatedly requesting approval.
