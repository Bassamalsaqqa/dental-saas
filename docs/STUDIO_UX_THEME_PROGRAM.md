# Studio UX, workflow and theme development

Baseline: deployed/release29dce746c0fc3be436b85a2c1a67fbbb41be3b55 (tree188433ad669477aa0c6812acd09fcc3ecd2c67eb). Branch feature/studio-workflows-themes-20261028. Owner master brief supplied separately in original checkout; original assets stay read-only. DEVELOPMENT ONLY: no production access/SQL/migration/merge/deploy/featureactivation/Actions. Existing production contracts/signatures remainON; optional scheduling/WhatsApp/corrections/checkout/contacts/derivatives remainOFF. Pinned Tailwind293433bytes unchanged. Do not blindly build it.

## Delivery matrix

| Slice | State | Evidence / remaining work |
|---|---|---|
| Cleanup and source authority | IMPLEMENTED / independently checked | Originaldirtycheckout/20protectedobjects/Ownerbrief preserved; three documentation files reconciled; only4791byte recreatablecache deleted. Activecontract/evidence deletion proposals rejected. |
| Platform entry/impersonation | IMPLEMENTED / focused verification | b152ffc;17actual local session/browser checks,18presentation checks. Additional role-revocation/expiredclinic/legacyprint coverage remains explicit. |
| Auth fit/title/demo-label | IMPLEMENTED / focused verification | 170127d;22actualEN/ARviewportchecks,9interactions. Physicalhardware/nativezoom/AT untested. |
| Theme system | FOUNDATION IMPLEMENTED / focused SQL verification; UI NOT IMPLEMENTED | Fivepresets, 73contrastpairs, versioned theme-only current/history storage, defaultOFF config. 449purechecks/28SQLchecks/concurrency/auditrollback. Routed RBAC/editor/firstpaint/realpalettebrowser acceptance remain required. |
| Three flagship journeys | NOT IMPLEMENTED | Reception/patient/clinical handoff; patient/exam/treatment/checkout; finance/provider or inventory return-context. Preserve disabledfeature and canonicaldomain authority. |
| Product-wide component rollout | NOT COMPLETE |539registered method/patterns in routeinventory are aliases/methods, not539pages. Reachability,featurestate and role-specific task evidence remain to map. |
| Independent acceptance / draftPR | NOT REQUESTED / NOT READY | Final combined exactheadreview and portablegallery after implementation. No approval or deployment implied. |

Actual source route/filter inventory: [CSV](ui-evidence/product-ux/route-inventory.csv). Every entry is explicitly registered-source evidence; it does not assert runtime or mutation acceptance. [Focused evidence](ui-evidence/product-ux/README.md) distinguishes browser,SQL and presentation assertions.

## Evidence to design decisions

| Source | Applicable pattern | Adoption / rejection |
|---|---|---|
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
