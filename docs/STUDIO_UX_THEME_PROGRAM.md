# Studio UX, workflow and theme development

Baseline: deployed/release29dce746c0fc3be436b85a2c1a67fbbb41be3b55 (tree188433ad669477aa0c6812acd09fcc3ecd2c67eb). Branch feature/studio-workflows-themes-20261028. Owner master brief supplied separately in original checkout; original assets stay read-only. DEVELOPMENT ONLY: no production access/SQL/migration/merge/deploy/featureactivation/Actions. Existing production contracts/signatures remainON; optional scheduling/WhatsApp/corrections/checkout/contacts/derivatives remainOFF. Pinned Tailwind293433bytes unchanged. Do not blindly build it.

## Delivery matrix

| Slice | State | Evidence / remaining work |
|---|---|---|
| Cleanup and source authority | IMPLEMENTED / independently checked | Originaldirtycheckout/20protectedobjects/Ownerbrief preserved; three documentation files reconciled; only4791byte recreatablecache deleted. Activecontract/evidence deletion proposals rejected. |
| Platform entry/impersonation | IMPLEMENTED / focused verification | b152ffc;17actual local session/browser checks,18presentation checks. Additional role-revocation/expiredclinic/legacyprint coverage remains explicit. |
| Auth fit/title/demo-label | IMPLEMENTED / focused verification | 170127d;22actualEN/ARviewportchecks,9interactions. Physicalhardware/nativezoom/AT untested. |
| Theme system | IMPLEMENTED SLICE / focused verification; NOT ACCEPTED | d21e2e2: native tenant/platform editor, validated sample and first paint, actual RBAC/SQL/CSRF/scope tests. Catalog administration, bounded override registry and browser accessibility precedence implemented with affected checks. Full consumer contrast/dark/print audit remains pending. |
| Three flagship journeys | CONTINUITY SLICE IMPLEMENTED / focused evidence | Native synthetic register-to-book and visit-to-pending-draft; examination-plan cancel, patient billing return-tab and inventory edit/history return. All445 original fixture rows preserved; explicitly recorded owned additions. Full journey composition, timing and product-wide rollout still incomplete. |
| Contextual creation | IMPLEMENTED / focused evidence | Patient registry and dayboard/calendar native dialogs reuse existing forms/endpoints; real registration and booking, server validation/conflict rejection, unsaved-input protection, focus return and full-page fallback. Complex initial-stock creation remains full-page because it has a native redirect contract and stock-movement consequences. |
| Product-wide component rollout | NOT COMPLETE |539registered method/patterns in routeinventory are aliases/methods, not539pages. Reachability,featurestate and role-specific task evidence remain to map. |
| Independent acceptance / draftPR | NOT REQUESTED / NOT READY | Final combined exactheadreview and portablegallery after implementation. No approval or deployment implied. |

Actual source route/filter inventory: [CSV](ui-evidence/product-ux/route-inventory.csv). Every entry is explicitly registered-source evidence; it does not assert runtime or mutation acceptance. [Focused evidence](ui-evidence/product-ux/README.md) distinguishes browser,SQL and presentation assertions.

## Owner refinement (2026-10-28)

The current direction is generally accepted. Draw product-wide inspiration from shadcn dashboard/component composition and Linear's focused navigation, compact toolbars, readable tables, coherent tabs, useful source-backed metrics and responsive views. Apply patterns to forms, details, settings, dialogs, navigation, loading/error states and workspaces; retain the olive/sand identity, existing CI4 components, actual Arabic RTL and Western digits. No React rewrite, borrowed demo figures or permission changes.

Prefer contextual create dialogs/drawers when they reduce context loss. This is conditional component-fit guidance, not an instruction to cram every form into a modal. Short registration/booking/context edits may stay in the current workspace, with native full-page fallback, dirty-input protection, real validation and deliberate next actions. Signing, complex clinical records, financial corrections and multi-step management retain appropriate full pages and explicit returns. Do not create fake popups or fetch arbitrary HTML/scripts to manufacture overlays.

## Evidence to design decisions

| Source | Applicable pattern | Adoption / rejection |
|---|---|---|
| [shadcn blocks](https://ui.shadcn.com/blocks) / [Dialog](https://ui.shadcn.com/docs/components/base/dialog) | Composable dashboard hierarchy and focused overlay semantics | Adapt spacing, compact controls and task hierarchy to existing native CI4 components; do not install React or copy sample metrics. |
| [Linear dashboards](https://linear.app/docs/dashboards) / [Display options](https://linear.app/docs/display-options) | Source-backed insight drilldown, deliberate list grouping and visible controls | Keep meaningful clinic facts and direct permitted actions; preserve source scope and existing list filters, without generic KPI decoration. |
| [GOV.UK password input](https://design-system.service.gov.uk/components/password-input/) | Nativepasswordfield, revealcontrol, autocomplete/paste | Preserve nativefield and integrated suffix; do not create customcredentialstate or blockpaste. |
| [WAI disclosure](https://www.w3.org/WAI/ARIA/apg/patterns/disclosure/) | Button plus managed visibility | Optional content uses disclosure; do not treat a panel as a persistedon/offpreference. |
| [USWDS modal](https://designsystem.digital.gov/components/modal/) | Focused interruption/confirmation | Reuse focus-safe dialogs for shortconfirmations; complexclinical/finance tasks retain dedicatedpages. |
| [Dentrix routing](https://hsps.pro/DentrixAscend/Help/Routing_panel_overview.htm) and [visit handoffs](https://hsps.pro/DentrixAscend/Help/handling_patient_visits.htm) | Patientcontext during visitrouting | Preserve real appointment/patient/providerlinks and supportednextactions; do not copy their statuses,billing or eligibilitypolicy. |
| [WCAG2.2](https://www.w3.org/TR/WCAG22/) | Contrast,reflow,focus,targets | Validate actual foreground/background pairs; test usable controls,not merely screenshots. Do not claim formalcompliance. |
| [DTCG2025.10](https://www.designtokens.org/tr/2025.10/) | Typed tokens and resolution | Primitive/semantic/component roles with explicit context. Inspiredbycommunityreport; not claiming W3Cstandardconformance or adopting an unnecessaryruntimeframework. |

## Journey contracts

These are design/acceptance maps, not claims that features below are newly implemented. Each action must pass existing routepermissions/features and keep canonical objectIDs and return context. Disabled or missing capabilities remain explained and unavailable.

| Journey / entry | Required facts / next permitted action | Success / cancel / failure authority |
|---|---|---|
| Intake: search or patient/create | Explicit identity/duplicatecheck; retain unassessed warning; register then choose book/openprofile | Existing patientcontroller validation; real returntarget; no inferred clinicalnegative. |
| Reception: dayboard/calendar | Actual appointment/provider/stage; patientpeek then supportedarrival/handoff | Existing transitions/conflicts; preserve selectedday/filter; no inventedavailability. |
| Clinical: patient/visit/examination | Real patientalerts/encounter/chart; draft then explicitreview/sign | Existing examinationgovernance; signedimmutable; failure retains input and truthfuldurablestate. |
| Plan: clinicalfinding/contracts | Suggestions distinct from persistedpriceditems; explicit edits/removals | Existing draftrevision/locking; signed evidence preserved; noautocharges. |
| Checkout: appointmentreview | Canonicalclinicalwork/charge/invoice/currency facts | Existing gatedcheckout/settlement only; never automatemoney or acknowledgments. |
| Finance: ledger/detail/payout | Sourceinvoice/payment/moneytrail; allowedrecord/refund/payout | Existingservercalculations; retainlistfilters/recordcontext; nofalse0balance. |
| Inventory: item/movement/reorder | Authoritativequantity/batch/expiry; permittedexplicitmovement | Existingstockservices; reason/audit; nofake supplierpayableintegration. |
| Team: users/providers/settings | Canonical membership/provideridentity/hours | ExistingRBAC/doctorcontracts; do not activate dynamicscheduling. |
| Platform: gateway/clinicinspector | Genuineglobalrole; explicitenter/impersonate/exit | ExistingCSRFPOST/session/audit; unoverrideablebanner; clinic-switch differs from exitingimpersonation. |
| Exceptions | Denied/stale/empty/sessionexpired/missingrecord facts | Honest reason and existingretry/safeexit; noforgedsuccess or placeholder#actions. |

## Theme architecture and remaining integration

Effectivepresentation: shippedsafeStudio -> publishedplatformdefault -> explicitclinicselection/overrides -> supportedaccessibilitypreference. Inheritingclinics followglobalchanges; explicitcustomclinics retain theircustompresentation until theychooseinherit/reset. GlobalControlPlane keeps its own platformidentity and cannot inherit tenantcolors; impersonationuses targettheme with fixedsecuritybanner. Publiclogin derives onlyplatformbrand before verifiedtenantcontext.

Reuseexistingsemanticroles and UIbehavior. No arbitraryCSS/selectors/HTML/URLs/fonts. WhitelistedHEXcolors/limitedlayoutoptions; colorstatusmustretain labels/symbols. Contrastchecks must cover textonallsurfaces,action/nav/selection,focus and clinicalwarningcontexts. Darktheme requires a real consumer-roleaudit: some existingframe/action/info variables are reused as bothtext and backgrounds. Do not publish attractivepalettes that make these consumers unreadable.

SettingsModel inspection: setSetting/setGlobalSetting perform read-then-write withoutthemeversion/CAS, globalNULLscope in UNIQUE(clinic_id,setting_key) does notguarantee oneglobalkey, and globalwriter does not use an explicitly injectedDBconnection. Do not weaken or refactor those establishedsettingswriters to buildthemes. Evaluate dedicated minimal additive current-theme+append-onlyrevision storage, unique nonNULLscopekeys and checked global-before-cliniclock/versioncontract. One additive theme migration authored and exercised only in a newly owned synthetic database; production execution/activation is unauthorized. Missing/disabledschema must fall back to currentshippedtheme withoutbreaking legacyproduction; zero per-componentthemequeries. Independent schema/securityreview required before any futureproductionactivation.

Theme code stays small: immutablecatalog/validation plus connection-bound resolution/persistence and one scopednativeeditor; classnames/number are implementationchoices,not a requiredframework. Include preview/cancel/tokenreset/fullinherit/publish/revert and staleeditprotection. Fivepresets: StudioForest, SandstoneCopper, SlateSage, DeepCharcoal, HighContrast. Catalogmanagement/default/newclinicinherit and tenantoverridevisibility must be implemented honestly, not represented by decorativebuttons.

## Preservation and acceptance

Original checkout/referenceassets/acceptedSVG/snapshotbytes/signedclinicalevidence/financialmath/tenantauthority/licenses/recoverykeys/backups/forensicdatadirs remainprotected. Testmutations only in ownedguarded syntheticdatabases. New additive theme schema tests require a fresh dedicated synthetic target; existingfixture schemas and originalbusinessDB stay intact. No customerdata fixtures or productiontests. One writer; manualAGY handoff only, Codex pauses while it works. No automatic agents/newworktrees/historyrewrite.

Measuredremaininglimits: patientworkspace querycount/TTFB remains backendfollowup; currentpass doesnot claim tofix441queries. Responsive and authenticationfit checks are specific viewports,not physicaldevice/AT/nativezoomcertification. Finalcandidate needs one scopedcombinedworkflow/security/theme acceptance and independentexactheadreview, not repeated broadcampaigns.

## Owner scale trial (2026-10-28)

Owner requested a modestly smaller overall interface and a single-page comparison before application-wide approval. Dashboard-only `?studio_scale=compact` opts into a screen-only trial; `?studio_scale=current` compares the unchanged existing scale. Neither URL saves a theme or changes another route. Default dashboard has no review strip. At 1586px: body18->17px, navigation17->16px, title44->40px, toolbar80->72px, fixed rows96->84px; identity lines remain readable through tighter internal gaps. Existing responsive composition is retained. Mobile touch targets remain44px; browser zoom is not overridden. Eight actual read-only browser checks cover EN desktop1586/1366, AR390, creation-menu keyboard behavior and trial isolation; original445 synthetic business rows remain unchanged.

App-wide scale rollout is PENDING OWNER VISUAL APPROVAL. Existing native Control Plane appearance architecture already persists safe enumerated text size and density with platform-to-clinic precedence. After approval, extend that existing contract with a bounded scale choice and shared typography/spacing tokens, retaining standard/large accessibility choices. Do not add arbitrary CSS or a parallel settings store. Current theme feature stays OFF in shipped configuration. This trial is not physical-device or formal zoom/accessibility certification.
