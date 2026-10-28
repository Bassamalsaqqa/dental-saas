# Product UX and theme campaign evidence

Development baseline29dce746; branch feature/studio-workflows-themes-20261028. No production operations. Clinical/financial/RBAC authority unchanged.

## Platform P0

17actual local guarded session/browser observations pass: ordinary manager controls absent/directPOSTdenied, genuine platformentry and explicitimpersonation,9EN/AR320/390/1366route observations, missingCSRFdenial/noGETexit/Bearerseparation, visibleexit with sessionregeneration, second-tab404and hiddenbanner, globaltenantsearch403. Original339syntheticbusinessrows and schemas preserved. See platform-p0.json.

Server-injected platform identity is derived by unchanged BaseController from PermissionService::isSuperAdmin each request. The view uses that canonical boolean and exactmatching active/impersonatedclinic IDs; no email/group/tenantrole inference. Existing POST/CSRF/sessionregeneration/audit endpoints remain authority. Shared security appearance is deliberately excluded from future clinic theme overrides and hidden on print. Legacy retained tenant layout reuses it; its old mobile-hidden control/directPOSTentry was replaced with the same gateway/exit semantics.

Private screenshot/runners remain outside source in sibling studio-ux-theme-20261028. Actual ARmobile security banner inspected. These are specific tests, not complete product/theme/security certification. A temporary assertion initially expected redirect after another-tabexit; actual unchanged TenantFilter intentionally returns404 at the same dashboardURL. The corrected expectation verifies actual404and absence of tenantbanner; original failed receipt preserved. No backend behavior was changed to satisfy tests.

Runtime tests: php tests/regression/StudioPlatformChromeStandalone.php; private p0-platform.cjs requires existing guarded loopback57321/3307 synthetic fixture and refuses otherDBidentity. No GitHubActions. Additional persona/role-revocation/accessibility scenarios remain campaign work.

## Authentication fit and presentation hygiene

22actual EN/ARviewport observations pass at320x568/360x640/390x667/390x844/430x932/768x1024/1024x768/1280x720/1366x768/1440x900/844x390: CTAvisible and nohorizontaloverflow. The11validEnglishbaseline observations had4CTAoffscreen; initialArabiclabelled baseline rows were not actually localeverified and are excluded. Finaltests use real authlanguageendpoint and assert renderedlocale. PrivateharnessbaseURL mismatch fixed outsideappsource.9actual EN/ARpassword/error/forgot/invalidreset and shortheightscroll checks pass. Physicalautofill/passwordmanager/nativezoom/ATremainuntested. See auth-fit.json.

Access DOM is form-first; decorative introduction moves aside or disappears before controls are compressed. No fixedheight/overflowclipping. Existing fields/endpoints/validation/CSRF/paste/passwordmanagement preserved. Two title separators corrected; syntheticpreviewlabel removed from genuine dashboardpresentation. Originalchart/legal/print/financialauthority unchanged.

## Theme foundation (not yet an enabled feature)

[Receipt](theme-foundation.json): five palettes, 73 explicit consumer contrast pairs each, 449 pure validation assertions; 28 actual MariaDB fixture checks; same-scope concurrent writers produce one accepted revision and one stale rejection; cross-clinic writes remain scoped; forced audit insertion failure rolls back current state and all history. These tests use a callback authorizer fixture, not actual routed RBAC. No editor, first-paint integration or browser palette acceptance is claimed.

Theme testing used a newly created `denta_theme_synthetic_20261028` on verified loopback port3307, with separate connections and synthetic clinic parents. All ancestor paths were checked for reparse points; original339 synthetic rows and existing schemas remain unchanged. No production access. Private bootstrap credentials and detailed logs remain outside source. The first SQL runner attempt stopped before DDL because the timestamped migration needed explicit class loading; the corrected fresh-target run passed.

`StudioThemeCatalogStandalone.php` needs no application/DB. `StudioThemeStoreSql.php` requires an explicit outside-source guarded bootstrap, fresh `denta_theme_synthetic_*` target, verified3307 connection and ownership constant. It refuses previously used theme tables instead of clearing evidence. The additive migration creates only two theme tables and one platform lock anchor, without clinic backfill or changes to existing settings. Its down method refuses destructive deletion; source rollback leaves history intact. Clinic/user references in retained revisions intentionally do not cascade-delete historical attribution. Runtime authorization must validate active clinic and genuine actor through the controller's current canonical checks before this feature can ship.

The default Studio palette matches all37 existing CSS color roles byte-for-byte. New on-action, on-selected, link and control-border roles separate foreground from fill semantics; actual consumer migration and browser validation remain pending. Deep Charcoal intentionally uses dark chrome with light reading surfaces. Diagnostic SVG, immutable snapshots, signing evidence and legal print remain excluded from theming.

## Native theme editor and first paint

[Portable gallery](theme-gallery.html) and [bounded acceptance receipt](theme-editor.json). The code slice is `d21e2e2`; subsequent evidence-only commits do not imply independent approval. The default configuration is OFF. Existing production and original checkout remain untouched.

Implemented: fixed curated presets, all42 semantic color fields, per-role reset, contained server-validated preview, explicit cancel, bounded reading size/density, native reason/confirmation/publish/inherit/revert, current canonical tenant/platform authorization, request-scoped first-paint palette selection and immutable revision creation. Global Control Plane chrome is fixed; anonymous login ignores query-supplied clinic/theme IDs. The theme editor does not modify evidence or accounting.

Focused evidence:16 routed editor observations,8 platform/CSRF/scope/history observations,9 first-paint observations,11 actual-preset route observations;12 canonical RBAC SQL checks;454 pure catalog checks. These are distinct observations, not a claim of equivalent numbers of full workflows. Store's earlier28 SQL checks and concurrency/audit-failure receipts are reused. Shared form timing correction `8a6a3b0` addresses a demonstrated canceled-submit pending-state failure;9 relevant auth interactions passed. SaaS guardrails pass without new exemptions; their pre-existing stale DOM-sink allowlist note is retained.

Failures retained privately: initial harness did not open the advanced disclosure; platform gateway token harness initially assumed meta tags rather than its native CSRF input. Actual shared form pending timing and one source-encoding issue were corrected and rechecked. First-paint initial integration assumed branding included a clinic ID; it does not. Resolution now uses the TenantFilter-validated role and authenticated actor, with genuine impersonation checks. No failed run is represented as a pass.

Still pending: platform preset catalog administration and override registry; accessibility-preference precedence; exhaustive real consumer-role contrast/print/CSP/forced-color checks; routed stale-edit/revocation concurrency; flagship task improvements and final combined independent review. Stored revision history is retained on source rollback; migration down intentionally refuses automatic deletion. No production schema or activation approval is implied.

## Catalog, preferences and preservation follow-up

[Governance receipt](theme-governance.json): native platform preset availability publication and per-clinic inheritance registry, tenant scope protections,44 affected SQL checks,7 native catalog browser observations and7 browser contrast/print/unsaved/grouping observations. Catalog uses the same two additive tables, a separate versioned scope and existing platform lock anchor. No additional migration. Published preferences never rewrite existing clinic selections; the active global default cannot be retired and global reset cannot bypass availability validation.

Screen-only theme variables leave print on the shipped palette. Supported browser `prefers-contrast: more` is the final presentation layer; reduced-motion/forced-colors retain native existing behavior. These are actual browser checks, not physical assistive-technology certification or legal PDF parity. All445 complete copied business rows across15 synthetic tables compare equal after these changes. Platform/clinic theme audit, sessions and synthetic security logging are intentionally separate.

A new read-only platform projection uses50-row pages and a51st-row has-more check; raw theme JSON is not exposed by that projection. The palette editor groups all42 roles exactly once. Native leave protection preserves unsaved input; it does not add a draft-storage policy. Existing feature switches remain OFF in shipped configuration.

## Three journey continuity checks

[Receipt](workflow-continuity.json):7 actual read/click/cancel/panel observations;3 reception native-form persistence observations;4 clinical draft/plan-cancel/role observations. Actual mutations are one owned patient, one appointment and one pending examination in the disposable theme fixture. All445 pre-existing business rows remain byte-for-byte equal, with explicitly enumerated new IDs. No original acceptance schema or business data changed.

The initial harness selected patient1 without invoice history, then corrected to the actual invoice-linked patient3. Native navigation assertions were changed to await the new document rather than treating a loading transition as completed. Booking harness initially treated an availability select as free text; resumed the already-created patient and selected an actual available slot without duplicate patient creation. View-only HTML denial uses the existing302 safe redirect, while JSON denial is403; application RBAC was not changed to satisfy an incorrect harness assumption. Failed receipts remain private and no failed run is claimed successful.

Task continuity improved without changing canonical business controllers/services. Clinical persistence is a pending synthetic draft, not evidence of completed care, signed consent, new billing or provider obligations. Existing clinical/financial transaction suites are not repeated or generalized from this UI check. The entire authorized campaign remains unfinished and no final draftPR/review request has been made.
