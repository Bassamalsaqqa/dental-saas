# Font Usage Audit - 2026-04-01

## Scope
- Source-level audit only.
- Included: `app/`, `public/`, `src/`, `lib/` source files (`.php`, `.css`, `.js`, `.html`).
- Excluded: compiled `public/assets/css/tailwind.css` and build log artifacts, because they would distort percentages.

## Executive Summary
- The app's intended primary UI font is `Inter`.
- The intended primary mono font is `JetBrains Mono`.
- Arabic UI text is now standardized to the original sidebar fallback stack: `ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Noto Sans", sans-serif`.
- The strongest drift comes from legacy or special-purpose views that still hardcode `Arial`, `Segoe UI`, `Tahoma`, `Helvetica Neue`, `Times New Roman`, and related stacks.
- The patient snapshot cards on [show.php](C:\xampp\htdocs\dental-saas\app\Views\patient\show.php) do not introduce a new font family. Their mismatch was coming from local sizing/weight/card styling, not a different font file.

## Global Theme Fonts
Defined in [tailwind.config.js](C:\xampp\htdocs\dental-saas\tailwind.config.js):
- `sans`: `Inter`, then system fallbacks
- `display`: `Inter`
- `mono`: `JetBrains Mono`, then monospace fallbacks

Meaning:
- Most app UI should converge on `Inter` through Tailwind's default sans stack.
- Code / IDs / technical fields should converge on `JetBrains Mono`.
- Arabic UI should converge on the original sidebar stack through shared layout routing, not one-off page overrides.

## Canonical Typography Policy
Approved product policy:

1. Primary app UI font: `Inter`
2. Technical / code-like values only: `JetBrains Mono`
3. Arabic UI text: the original sidebar stack
4. Print/export templates may deviate only when there is an explicit document-use reason
5. Forbidden for normal app UI: `Arial`, `Segoe UI`, `Tahoma`, `Verdana`, `Helvetica Neue`, `Helvetica`, `Times New Roman`, `Georgia`, and other one-off overrides

Implementation rules:
- Remove hardcoded `font-family` declarations from normal app UI surfaces
- Prefer theme tokens and shared layout routing over page-local font stacks
- Keep `JetBrains Mono` restricted to IDs, codes, technical values, and machine-readable content
- Normalize font-weight, line-height, and spacing at the same time as family changes

## First Cleanup Slice Completed
Completed in this pass:
- shared tenant layout
- shared control-plane layout
- kiosk layout
- welcome / error pages
- odontogram interactive page
- WhatsApp template preview partial
- contract create surface

Guardrail added:
- `scripts/ci/saas_guardrails.sh` now fails on forbidden UI font strings outside excluded print/export surfaces

## Exact Arabic Stack Adopted
The sidebar did not previously use a dedicated Arabic webfont. It inherited the app sans stack, and Arabic glyphs rendered through the fallback portion of that stack.

Canonical Arabic UI stack now:
- `ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Noto Sans", sans-serif`

This is now used as the Arabic UI stack across tenant layout, control-plane layout, kiosk, and the standalone welcome/error surfaces.

## Coverage Summary
- Total source files scanned: `548`
- Files containing font-related declarations or utilities: `35`
- Total explicit font-declaration occurrences counted: `47`

## Explicit Font Declarations By Usage Share
Percentages below are based on explicit source-level font declarations only, not on rendered page traffic.

1. `Arial`
- Occurrences: `10`
- Share of explicit declarations: `21.28%`
- Files: `10`
- Notable locations:
  - [Reports.php](C:\xampp\htdocs\dental-saas\app\Controllers\Reports.php)
  - [inventory/index.php](C:\xampp\htdocs\dental-saas\app\Views\inventory\index.php)
  - [inventory/low_stock.php](C:\xampp\htdocs\dental-saas\app\Views\inventory\low_stock.php)
  - [inventory/usage_history.php](C:\xampp\htdocs\dental-saas\app\Views\inventory\usage_history.php)
  - [layouts/kiosk.php](C:\xampp\htdocs\dental-saas\app\Views\layouts\kiosk.php)
  - [welcome_message.php](C:\xampp\htdocs\dental-saas\app\Views\welcome_message.php)

2. `Segoe UI`
- Occurrences: `7`
- Share: `14.89%`
- Files: `7`
- Mostly print / legacy / kiosk / control-plane partials

3. `Tahoma`
- Occurrences: `6`
- Share: `12.77%`
- Files: `6`
- Mostly paired with `Segoe UI` fallback stacks

4. `Verdana`
- Occurrences: `4`
- Share: `8.51%`
- Files: `4`
- Mostly print/export documents

5. `Helvetica Neue`
- Occurrences: `4`
- Share: `8.51%`
- Files: `4`
- Legacy pages / error pages / odontogram

6. `Helvetica`
- Occurrences: `3`
- Share: `6.38%`
- Files: `3`

7. `Inter`
- Occurrences: `2`
- Share: `4.26%`
- Files: `2`
- Explicit declarations are low because `Inter` is primarily applied through Tailwind theme defaults, not repeated inline everywhere.

8. `Times New Roman`
- Occurrences: `2`
- Share: `4.26%`
- Files: `2`
- Locations:
  - [finance/invoice.php](C:\xampp\htdocs\dental-saas\app\Views\finance\invoice.php)
  - [inventory/usage_print.php](C:\xampp\htdocs\dental-saas\app\Views\inventory\usage_print.php)

9. Minor one-off fonts / stacks
- `Cairo`: `2`
- `Tajawal`: `1`
- `Georgia`: `1`
- `ui-sans-serif`: `1`
- `system-ui`: `1`
- `ui-monospace`: `1`
- `SFMono-Regular`: `1`
- `Menlo`: `1`
- `Monaco`: `1`
- `Consolas`: `1`
- `BlinkMacSystemFont`: `1`

## Where Font Drift Is Coming From
### Expected / Acceptable
- Tailwind theme defaults in [tailwind.config.js](C:\xampp\htdocs\dental-saas\tailwind.config.js)
- Monospace technical fields and IDs using `font-mono`
- Some print/export views that intentionally use document-style fonts

### Likely Candidates For Cleanup
1. Legacy error / welcome pages
- [error_400.php](C:\xampp\htdocs\dental-saas\app\Views\errors\html\error_400.php)
- [error_404.php](C:\xampp\htdocs\dental-saas\app\Views\errors\html\error_404.php)
- [welcome_message.php](C:\xampp\htdocs\dental-saas\app\Views\welcome_message.php)

2. Kiosk and special-purpose shells
- [layouts/kiosk.php](C:\xampp\htdocs\dental-saas\app\Views\layouts\kiosk.php)
- This was cleaned in the first normalization slice and now follows the shared app typography contract.

3. Odontogram and some print/export pages
- [odontogram/index.php](C:\xampp\htdocs\dental-saas\app\Views\odontogram\index.php)
- [odontogram/pdf.php](C:\xampp\htdocs\dental-saas\app\Views\odontogram\pdf.php)
- [odontogram/print.php](C:\xampp\htdocs\dental-saas\app\Views\odontogram\print.php)
- [odontogram/export.php](C:\xampp\htdocs\dental-saas\app\Views\odontogram\export.php)

4. Finance/inventory print-style outputs
- [finance/invoice.php](C:\xampp\htdocs\dental-saas\app\Views\finance\invoice.php)
- [inventory/usage_print.php](C:\xampp\htdocs\dental-saas\app\Views\inventory\usage_print.php)

## Practical Interpretation
- If your goal is product-wide UI consistency, the app should converge on:
  - `Inter` for all normal UI surfaces
  - `JetBrains Mono` only for code-like or machine-readable values
- The biggest inconsistency is not in the main app shell. It is in:
  - print/export templates
  - kiosk shell
  - legacy/standalone pages
  - a few specialized modules like odontogram

## Recommendation Order
1. Keep
- `Inter` as the main UI font
- `JetBrains Mono` for code/IDs/technical fields

2. Review deliberately
- historical `Tajawal` / `Cairo` occurrences should not remain as default UI choices
- Arabic should ride on the approved sidebar fallback stack consistently

3. Replace or normalize over time
- `Arial`
- `Segoe UI`
- `Tahoma`
- `Helvetica Neue`
- `Helvetica`
- `Verdana`

4. Keep isolated only where truly intentional
- `Times New Roman` / `Georgia` for formal print documents, if you explicitly want document-style output

## Immediate Conclusion For Patient Page Work
- The snapshot cards should align with the app's main sans rhythm, not introduce a special local title style.
- The visual mismatch you noticed there was valid, but it was mainly a local styling issue, not a hidden new font family.
