# PR22 final acceptance closure - production NO-GO

Reviewed source: `0619d0277202924e5a34d4979e084e972c760652`, tree `5e34a1d85171e55a7c10aefb961735c959aa196c`. Review5695038870 is COMMENTED with an explicit CHANGES REQUIRED disposition; it is neither APPROVED nor an empty review history. Owner accepted the visual direction and conditionally authorized deployment when the work and safety gates are complete. The new mandatory gate failed; no live release was executed.

[Sanitized machine receipt](final-closure.json) | [Arabic desktop](closure-impersonation-ar-1366.jpg) | [Arabic mobile](closure-impersonation-ar-390.jpg) | [Current component gallery](controls-gallery.html).

## Delivered correction

Two literal confirmation messages now use EN/AR language keys: `ControlPlane/Clinics::impersonate()` and `ControlPlane::exitImpersonation()`. Clinic identity is passed as text to `lang()` and escaped by the unchanged shell. Session regeneration, actor checks, POST/CSRF, scope switching, audit calls and redirects are unchanged. No other visual/backend rules were changed. There was no additional UI design in this pass; the existing shadcn-researched component system remains intact.

Actual Arabic desktop/mobile POST entry -> impersonation -> patient/dashboard/finance -> another-tab return passed. Ordinary manager forged entry and Bearer/session separation passed. A loaded theme form with revoked settings edit permission returned the existing localized rejection redirect and changed no current/revision row; an inactive impersonated clinic and expired browser session were denied. These are eight scoped cases, not eight complete products or formal security certification. Temporary grant/clinic changes occurred only in a newly owned clone and were restored.

## Confirmed material blocker

`TreatmentSchemaCompatibilitySql.php` is a genuine read-only acceptance gate: **exit1, passed=false**, not a successful defect-characterization test. It finds **41 of43 offered treatment keys unsupported** by the actual column. Production, native rehearsal and the isolated clone have:

`enum('cleaning','extraction','filling','crown','root_canal','orthodontic','implant','other')`

Actual form submission of `comprehensive_oral_evaluation` passed application validation but failed SQL with a type-truncation error. The treatment transaction rolled back and created no treatment or invoice. The first selection was the real catalog option, not a forged value. The application catalog is byte-equivalent to deployed29dce746: this is a pre-existing clinical-workflow/storage mismatch, not a new UI regression. Patient26 and pending examination1150 were successfully created through their real dialogs in the owned clone; they remain preserved. No successful treatment save is claimed.

Production metadata was read under `START TRANSACTION READ ONLY`, with exact installed29dce746/tree188433ad verified, and no patient/customer rows extracted. The source-only compatibility gate cannot be certified by choosing only Crown/Other, weakening the schema test, silently mapping clinical keys, dropping valid catalog choices, enabling features or running an unauthorized migration. A separate reviewed compatibility correction must define supported entry behavior and preserve exact persisted meanings. Extending storage to support the full existing catalog requires a separately authorized migration; source-only containment must visibly explain unavailable choices and keep historical values unchanged. Neither policy was silently chosen here.

## Theme acceptance/control matrix

| Contract | Evidence | Disposition |
|---|---|---|
| Immutable built-in fallback, OFF makes zero theme queries | Existing store test plus fresh owned migration test | Verified scoped SQL; production OFF retained |
| Platform default, explicit clinic override, inherit, recovery revision |28 checks against fresh schema created by actual migration; earlier routed publication receipts reused | Verified scoped; no new production activation |
| Unique non-null scope/current PK and composite history PK, InnoDB | Actual migration DDL and prior SQL checks | Verified theme schema; does not imply business FK parity |
| Canonical tenant/platform access, expiry, inactive actor, impersonation read-only |12 current SQL access checks plus actual routed revocation/inactive/manager denials | Verified scoped |
| Revocation races publication | Real parallel MariaDB connections: publication waits for committed grant revocation, then rejects; current/history unchanged | Verified; not exhaustive deadlock certification |
| Failed audit persistence | Real rejecting insert trigger; transaction depth0 and all state/history unchanged | Verified |
| Partial DDL | First real table created; explicit Forge failure injected before second CREATE; retained table, fallback and retry ownership rejection verified | Verified injected failure; **not** denied-user privilege test |
| Migration down | Throws rather than deleting configuration/history | Verified; preserve partial DDL and recover manually after ownership review |
| Catalog availability and frozen clinic overrides | Prior44 SQL/catalog route checks reused unchanged | Existing bounded evidence, not rerun |
| Five real preset consumers, accessibility preference, public/platform separation | Prior first-paint/preset/preference receipts;96 numeric pairs per preset are not all-consumer certification | PARTIAL; theme activation NOT ACCEPTED |
| Deep Charcoal | Existing code explicitly uses dark chrome with light reading surfaces | Not full dark mode; no new claim |
| Complete live contrast, retained legacy consumers, CSP first paint, legal print/PDF, physical AT/native zoom | Not completed | NOT CERTIFIED; do not activate themes |

The new clone used exact SHOW CREATE statements rather than CREATE LIKE, but its source fixture has **zero foreign keys**. Production metadata has **26 foreign keys**. Matching copied columns and successful writes do not prove production-compatible business constraints. A production-schema synthetic fixture and targeted native successful mutations remain necessary; do not overwrite the older fixture or claim it is certified. Local disposable server grant-system tables are unavailable, so a proposed real denied-DDL-user test stopped before creating any user/schema; the replacement test explicitly records injected Forge failure instead. No grant security was weakened.

## Risk-ranked reachable task/persona/state matrix

| Task/persona | Actual evidence | Remaining state/gate |
|---|---|---|
| Reception manager | Earlier registration21/booking1143 persistence/conflict; current real patient26 creation in separate clone | Native success against authentic production constraints not newly certified |
| Examination manager | Actual quick-form creation1150, pending, patient26, clinic1, redirect/examinations | Existing PR21 save/sign/concurrency evidence reused; new route-wide sign/edit not rerun |
| Treatment manager | Actual native dialog opens and sends catalog value; SQL rollback verified | BLOCKED schema compatibility; no successful treatment save |
| Contract clinician | PR21 priced-item preservation, stale/signed evidence retained; earlier current plan open/cancel | Current full create/edit/sign continuity not newly certified |
| Finance manager | Existing UI/read/rejection evidence; no current successful invoice/payment/refund/payout write | NOT CERTIFIED for current connected mutation acceptance; no optional checkout activation |
| Inventory manager | Prior read/edit/cancel and scoped native forms | Current successful stock movement/reorder not newly certified |
| Platform super-admin | Real localized impersonation/return and another-tab exit; previous platform dialogs | Full onboarding/plan/invoice successful persistence not newly certified |
| Restricted manager/viewer/foreign clinic/API | Current manager forged POST and Bearer separation; earlier viewer/cross-tenant denials | Authenticated Bearer success and full cross-tenant mutation matrix incomplete |
| Actual optional Select2 | Real existing pinned jQuery3.7.1 + Select2 4.1.0-rc.0, AJAX patient26 selection inside native finance dialog, top-layer positioning, Escape and keyboard focus | Passed4 E2E cases; browser-only test load, no new shipped dependency or offline claim |
| General surfaces | Earlier36 representative route renders,24 EN/AR settings/control cases,14 dialog interactions and focused captures reused |543 registered route patterns are not543 screens; no application-wide COMPLETE status |
| Responsive/accessibility | Current Arabic390/desktop1366 impersonation; existing scoped320..1586 evidence | No new blanket320..1920/landscape/200-400% native zoom/physical AT certification |

Short native dialogs, record-context drawers and complex full-page clinical/signing/provisioning fallbacks remain deliberate. The latest treatment/examination UNAVAILABLE_NOT_CERTIFIED test selectors had missed working links: their forms actually opened here. Actual examination persistence is now distinct from the treatment SQL failure; neither is inferred from HTTP200.

## Preservation and performance

- Original checkout untouched;445 original local synthetic business rows still match the exact preservation verifier. New tests use a separate clone/database/session/writable on loopback57323; existing Owner preview57322 is preserved.
- Production remains29dce746/tree188433ad. No production checkpoint, merge, deployment, data write, migration, dependency install or feature activation. No GitHub Actions.
- Native chart/SVG/snapshot, signed/legal evidence, financial calculations, dependency/license files and Tailwind hash remain unchanged. No original clinic/customer data used as fixtures.
- No CSS/JS/payload change in this correction; localization does not add queries. Earlier measured query counts/latency remain the existing evidence, not a newly claimed speedup. Patient400+ query hotspot remains separate.
- Actual financial/clinical mutation success and business-table fingerprints cannot be inferred from the fresh theme-table tests. Failed private harness receipts are retained; corrected selectors/locale expectations are explicitly not source defects.

## Release choice and recommendation

**CHANGES REQUIRED. PRODUCTION NO-GO.**

A. **Source-only with themes OFF** is the only eligible release direction within existing migration boundaries, but it is not ready: resolve the treatment schema/catalog acceptance blocker through reviewed compatible behavior; complete production-constraint/native mutation evidence; obtain actual exact-head independent acceptance; then create/certify a fresh encrypted off-host recovery checkpoint and perform final pinned preflight/source-only rollback preparation. Do not use an older restore to certify newer writes.

B. **Usable theme activation** remains separately unauthorized and uncertified. Requires theme-only migration/activation permission after complete security, consumer contrast/print and production-compatible rehearsal acceptance. Keep source feature OFF; do not combine B with A. No schema/source rollback may delete theme history or restore an old customer DB.

This handoff stops at a demonstrated clinical/storage and acceptance blocker, not a routine design checkpoint. Owner visual acceptance is retained; it is not represented as independent financial/schema/security approval or permission for production migrations.
