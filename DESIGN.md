---
name: Denta+ clinic overview proof
description: Provisional one-page replacement, awaiting Owner visual acceptance
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
The Owner rejected the previous V4 implementation. It remains preserved at checkpoint 9bf0622 and is not visual authority. The Owner generally approved the one-page direction, requesting stronger overall color and larger text. The latest Owner refinement requires larger text, denser appointment information, one-click language switching, filled text containers and borderless colored cards. The refined proof awaits visual review before application-wide work. The Owner additionally requires distinct inner attention-task cards, full-row table navigation and no directional arrows anywhere, including dialogs and menus.

This document describes only dashboard/index.php using layouts/studio.php and studio.css/studio.js. Other routes still use the rejected incumbent presentation. No claim of whole-application redesign, final Owner visual acceptance, external review or production readiness.

## Colors
A deep forest frame establishes persistent navigation; lime indicates the active location. A crisp reading surface, cool neutral canvas and sage attention region separate operational tasks. Status colors require actual persisted states and visible text.

## Typography
Manrope is the English UI workhorse; Arabic uses Noto Sans Arabic first. Patient identity is 13px, descriptive body 12–14px, headline 38px desktop / 26px mobile. Secondary table metadata is 10px. Do not shrink primary patient names to metadata size. Fonts currently use Google's existing remote font delivery pattern, not a self-hosting claim.

## Layout
Desktop: 260px navigation, 80px toolbar, 36px workspace insets. Below 1390px the rail is 238px. Appointments lead; the attention queue is complementary. Recently added patients and real task links follow. The appointment table uses existing patient phone, booking resource and duration data without new queries. Below 1450px the attention queue follows the full-width table for readable columns. Below 1150px attention becomes a row; below 800px navigation is an inert-background overlay, the appointment list becomes a vertical record layout, and attention follows it.

## Elevation & Depth
Appointment surface: 0 4px 18px #233f3010. Popovers: 0 16px 55px #112b3029. Dialogs use a dimmed, 5px blurred backdrop to preserve spatial context while isolating the task. No decorative animation.

## Shapes
8px controls, 16px main surfaces, 18px dialogs. Avatar initials use a consistent rounded-square patient treatment and circular account treatment.

## Components
First-party 1.65px outline SVG geometry. No icon fonts, legacy CSS, Tailwind, jQuery or UI framework on the proof route. Language switching is a direct link to the other locale, without a menu. Native dialog/popover semantics; explicit dialog names, Escape, focus return, keyboard tabs and mobile navigation wrap. Search uses the existing session patient search route with bounded results and text-only rendering. Notification content comes from the existing API; marking read remains in the canonical notification centre. Create choices navigate to existing authorized full workflows.

## Do's and Don'ts
Attention items are individually filled task cards with a clear task title, factual queue quantity and named action. Appointment rows use native stretched links across all cells; patient-name links retain their own destination. Preserve keyboard focus and modifier-click behavior. No arrow, chevron or dropdown-arrow decoration in the proof, including popup dialogs. Use filled containers for headings, dates, task links and content; cards have no decorative borders. Preserve visible keyboard focus outlines. Use actual controller data and canonical permission checks. Synthetic fixtures are clearly identified and belong only to the local disposable schema. Preserve backend, tenant, finance, clinical and feature-gate contracts. Do not apply this proposed system across more pages until the Owner judges this proof. Do not call a screenshot or passing checks visual approval.
