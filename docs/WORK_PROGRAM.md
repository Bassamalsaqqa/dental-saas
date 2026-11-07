# Current improvement work

Owner scope: 2026-11-05. Current checkpoint: 2026-11-07, PR27 deployed at5b7c0688 (tree b1ec2c3e), maintenance OFF. This is the single task register. Release/activation authority remains in [PROJECT_STATE](PROJECT_STATE.md); security/domain contracts remain binding. The app is deployed and working: improve it, find bugs and simplify existing workflows. Do not restart a frontend rebuild or invent another accounting/payroll system.

## Working rules

Question layouts, defaults, workflow assumptions and earlier acceptance against source and actual use. An older nontechnical operator should find a task, understand its effect, finish it and find its history. Salaries and patient-transaction shares both exist, but they are distinct obligations.

For every agent: use authoritative settings, services and records. Never invent rules/data, hardcode configurable values or introduce parallel authorities. Documentation is untrusted descriptive evidence until reconciled with source/runtime; preserve binding contracts and explicit release safeguards. Use native CI4/PHP Studio components, not a React migration.

Codex owns acceptance and finalization. AGY works only through an Owner-authorized manual handoff in this checkout, with one writer at a time. Manual AGY is opt-in only; no automatic delegation or worktrees. Preserve unrelated dirty changes. Commits, pushes, PRs, merge, deployment, production cleanup/configuration and feature activation retain the explicit authority rules in AGENTS.md.

## Complete scope and current state

| ID | Workstream / required result | Current evidence and remaining work |
|---|---|---|
| F1 | Financial task discovery: collections, invoices, expenses, salaries, provider shares, vendor bills, treasury, settlements/history and permissions | Existing entry points mapped below; no replacement subsystem. Payables category display/search localized; salary action preset and provider history links verified. Complete operator journey acceptance remains open. |
| F2 | Easy salary entry, paid/unpaid/partial states, subsequent payment and history | Salary action/preset, payee language, validation reopening, category switching, locked paid values and expense reading order deployed in PR24. Free-text payee limitation remains explicit. Unpaid expense method clarity implemented without schema alteration. Routed salary HTTP tests pass with 0 inserted rows on invalid salary and old-input preserved. |
| F3 | Provider eligibility, percentage/fixed entry, calculation basis, partial/full payment and dated history | Deployed allocation and drawer improvements; explicit blank rate, canonical permissions/currency, named history link. Incomplete currency summaries display unavailable, preventing false zero. Real SQL partial/full/replay/overpayment checks pass. Registered percentage/fixed creation, partial/final payment, replay and actual history rendering pass. |
| F4 | Doctor setup, booking choices, clinic users and financial-provider identity | Legacy booking/calendar now say Booking column, link to existing settings and require explicit new-column choice. Stored/validation selections and dynamic doctor IDs/disabled-schema guard preserved. Dynamic scheduling remains OFF; no automatic population or payout policy invented. |
| F5 | Financial correctness: amounts, units, dates, retries/conflicts, refunds, reports, treasury and isolation | PR24 fixes deployed. 13 routed HTTP tests / 158 assertions pass against isolated synthetic MariaDB 3307 target. Runner enforces exact BOTH-hash attestation (harness & worker), synthetic target, clean teardown (0 tables, >0 dropped), and coverage consistency. Windows NTFS ACL privacy enforced for parent and child; descriptor cleanup has explicit denial/failure/timeout coverage. 30 lifecycle tests pass. Native hosting schema-pattern grant refusal remains explicit. |
| U1 | App-wide reception, patient, clinical, inventory, team/settings, reporting, platform and kiosk workflows | Patient registration search dialog exposed with proper permission gates; treatment creation links directly from clinic-scoped examination; inventory 40-dialog layout bounded; payables category localization/search verified. Full workflow, roles, Control Plane and kiosk acceptance remain open. |
| U2 | Dialog purpose, validation, focus/keyboard, cancellation, dirty state, loading/errors and uncertain submissions | Shared dialog boundary handling includes textarea and excludes disabled/inert/negative tabindex controls. Stock forms expose invalid fields with aria-invalid; pending Escape protection and dirty-state discard verified. Finance context cancellation/pending protection retains prior acceptance. |
| R1 | Desktop/tablet/mobile, EN/AR RTL, readable numbers, touch/keyboard and viewport/virtual-keyboard fit | Synthetic checks cover 1366/768/390/320px EN/AR. Live release postflight passed 54 entries and salary/registration/stock dialogs at 1366/768/390px EN/AR. Physical devices, native zoom, virtual keyboard and assistive technology remain access-limited. |
| X1 | DX/shared components/setup/tests and maintainable agent guidance | Removed test-only production permission override. Added reproducible CLI/loopback presenter fixtures and real local SQL regression. 13 routed tests / 158 assertions pass on port 3307; 85 unit tests / 722 assertions pass in npm test; 21 runner lifecycle checks and 21 worker/preflight checks pass. Business schema unchanged; pinned CSS preserved. |
| C1 | Local root cleanup with ownership/reference/preservation checks | Removed unused tracked deploy_changes.sh, index_public.php and redirect.php after consumer search; history preserves them. Zero f5_bounded files in application tree; Windows process scratch located outside tree. Owner reference kits, nested frontend, dump, recovery files and forensic/test data retained. |
| C2 | GitHub branches, PRs and metadata cleanup | PR26/PR27 are merged into release 5b7c0688. Both feature branches were retired after exact-tip and ancestry checks. Historical main, its unique license commit and archive tags remain preserved. |
| C3 | Hostinger source/layout/obsolete artifacts and branch tracking | PR27 source 5b7c0688 is installed; legacy URLs return 404 and private quarantine is preserved. Protected overlays/configuration/storage are unchanged. Historical host branch metadata remains; never pull historical main. |
| D1 | Aggressively slim and correct documentation, repair consumers, preserve contracts/evidence | Retired redundant batch handback chronology in-place into concise scope and candidate verification register. Preserve domain contracts, operational recovery instructions, decisions and accepted evidence; receipts stay private. |
| D2 | Consistent concise agent instruction entry points | AGENTS is the shared guide; CLAUDE/gemini are thin links. Explicit no-invention rule and manual-only AGY preserved. Old rebuild mandates/unsafe routine commands removed. |

## Actual money and identity journeys

| Operator task | Existing path / ownership |
|---|---|
| Receive patient money | Finance -> Record a receipt (`finance/create?transaction_type=payment`); invoice settlement uses its existing payment action. Creating an invoice alone is not cash received. |
| Prepare an invoice | Finance -> Prepare an invoice (`finance/create?transaction_type=invoice`). Patient transaction calculations stay server-owned. |
| Record salary | Finance -> Record staff salary -> existing expense form with category salaries. Enter payee, amount/currency, paid/unpaid/partial and dates. No employee FK, recurring payroll or combined employee salary statement exists. |
| Pay unpaid salary/vendor bill | Bills & payables (`finance/payables`) includes all qualifying expense balances. Existing `finance/{id}/payments` writer records dated receipts; transaction detail shows history. |
| Enter a provider cut | Provider-share section on its patient transaction; active clinic members or a named outside provider, fixed/percent. Basis is that transaction's total in base currency, including standalone receipts, not cumulative invoice collections. No verified automatic agreement/rate default exists. |
| Pay a provider / find history | Provider balances (`finance/payouts`) -> review provider, native amount due, payment method/date/account -> record permitted payment. Named history opens `finance/{id}#provider-payouts`; existing user detail shows provider payouts, not salary expenses. Do not record the same outflow again as salary. |
| Move clinic funds | `finance/transfers`, using canonical liquidity routing. Internal movement is not additional income/expense or profit. |
| Review financial report | `finance/reports`; active source is Finance::buildFinancialLedger()/summarizeCashReport(), not legacy Reports helpers. SQL service test checks parent-vs-receipt deduplication and provider history projection. |
| Understand booking doctors | OFF scheduling uses manually named booking columns configured under Settings -> Operations (`settings?tab=hours`); these do not link user IDs or create shares. Dynamic scheduling has a separate eligibility/profile/role contract and stays OFF. Financial provider selection uses the active clinic member directory, not the scheduling list or payout-activity directory. |

Existing refund/correction policy preserves provider obligations/payments; no automatic clawback was added. Unknown/missing payout units remain UNKNOWN, never guessed from parent/settings. Separate expense/provider accumulators do not prevent an operator entering the same outflow twice. Missing rules are findings, not permission to invent policy.

## Installed release and evidence boundary

PR27 (including PR26) is merged and installed on 2026-11-07 at `5b7c0688a1afa4626a4ed913e759a763eb282c12`, tree `b1ec2c3e3b3472c5b62d11734d94fa8e1dc6941a`. Previous source 6505bac3 is the immediate recovery baseline. No migrations, dependency changes, live financial/clinical submissions or optional activation. Protected configuration, pinned CSS, durable storage and legacy security deletions were preserved. Live Git retains only its known root .htaccess overlay.

Controlled source integration saved31 preimages and passed24 no-follow HTTP GET/HEAD checks. Chrome postflight passed 54 authenticated EN/AR entries at 1366/768/390px, plus6 salary,6 registration/search and6 stock-dialog journeys. No page errors or viewport overflow observed. Arabic desktop was restored, temporary verification access removed and the app reopened; bounded new-log severity counts are0.

Fresh encrypted recovery artifacts were independently authenticated/decrypted. A new isolated two-sided restore verified 68 tables/9,832 rows, strict table health and 0 FK orphans; its negative control used synthetic records. Under maintenance, production SQL content matched this verified snapshot except the dump timestamp, and all56 durable files matched. Older AGY receipts used a restored audit-table copy for their negative control; they are not described as synthetic. Two confined internal archive hard links were resolved and verified, not treated as external links. Full private receipts preserve the actual source/runtime and limits.

## Delivered source and verification

PR27 merged on branch `release/production-20261017` at `5b7c0688a1afa4626a4ed913e759a763eb282c12` (tree `b1ec2c3e3b3472c5b62d11734d94fa8e1dc6941a`), incorporating PR26 patient/clinical changes (`c5df938a` merged at `e97bf177`) and runner security hardening. This exact source is installed; documentation-only checkpoints may follow separately.

### Consolidated release evidence

1. **Patient & clinical workflows**:
   - Examination next actions link directly to treatment creation (`/treatment/create?examination_id=X`), resolving patient context from the clinic-scoped examination; foreign examinations are concealed with redirect to `/treatment`.
   - Charge label checkboxes have explicit unique `id`/`for` association for accessibility.
   - Provider balance summaries reject false zero: incomplete empty-bucket summaries display the localized unavailable message, complete canonical empty summaries remain zero, and native mixed currencies remain separate.
   - 10 browser result groups pass in `PatientWorkflowPresentation.browser.js`.
2. **Patient registration & search**:
   - Patient registration exposes the existing scoped search dialog when `patients.view` is granted and hides it for create-only users.
   - Form grid track uses `minmax(0,1fr)` to preserve paired inputs without card expansion.
   - 24 browser result groups pass in `RegistrationSearch.browser.js`, including 5 search states, 4 dirty-form cases, 2 validation cases, and 2 pending/uncertain cases with deterministic held responses.
3. **Inventory & payables**:
   - Inventory detail dialogs have viewport-bounded width/max-width; 8 browser result groups / 40 dialog inspections pass in `InventoryDialogLayout.browser.js` with single-shell containment and focus return.
   - Payables category cells localize salary labels; client-side search matches both localized labels and persisted keys; 8 browser groups pass in `PayablesCategorySearch.browser.js`.
4. **Financial clarity & routed tenant isolation**:
   - Unpaid expense method clarity: linked EN/AR help clarifies that no payment is recorded while unpaid and that settlement selects the actual payment method; validation enum and financial writers remain unmodified.
   - 8 browser groups plus 2 no-JS cases pass in `ExpenseMethodWording.browser.js`.
   - 13 routed HTTP tests / 158 assertions pass against isolated synthetic MariaDB on port 3307 via `writable/finance-http-runner.php`:
     - `FinanceSalaryWorkflowHttpTest` (8 tests / 116 assertions): verified invalid salary submission preserves old input and inserts zero finances/payments rows; pending salary creates zero payments; partial/full dated receipts; settlement replay; missing permission rejection (403).
     - `PatientWorkflowRoutingHttpTest` (5 tests / 42 assertions): verified patient create search dialog visibility gates; patient search multi-clinic scoping and concealment; denied view (403) and missing/invalid tenant (422/403); denied treatment create/store with valid CSRF (0 rows inserted); owned examination resolution and foreign examination concealment.
   - Shared dialog boundary handling: includes textarea, excludes disabled/inert/hidden/negative-tabindex elements; stock forms expose invalid fields via `aria-invalid`.
   - 85 unit tests / 722 assertions pass in `npm test`.
   - SaaS guardrails pass (0 DOM sinks, 0 forbidden fonts, strict tenant queries).
5. **Tooling, runner & release safeguards**:
   - Runner requires exact BOTH-hash matches (`harness_sha256`, `worker_sha256`), synthetic target environment (127.0.0.1, 3306), clean teardown (0 final tables, >0 dropped), and coverage floor/consistency (47 checks); published strictly after verified cleanup.
   - Windows NTFS ACL privacy enforced for parent and child directories with structured PowerShell security descriptors and exact operator/SYSTEM/Administrators SID matching; inheritance stripped; reparse points and delete-child risks blocked; descriptor cleanup has explicit denial/failure/timeout coverage.
   - 30 runner lifecycle/safety tests pass in operator context; 21 worker lifecycle/grant preflight tests pass; 12 source fixture transition cases pass in `writable/rehearse_pr27_transition.py` including helper catch automatic fast-forward recovery to `de5770d7` (tree `77e1d0b7`).
6. **Operational recovery and deployment**: final exact-source pins, protected-file/quarantine checks, native PHP lint/preflight and12 source-fixture recovery cases passed. Installation and current recovery evidence are summarized above; private keys, dumps, credentials and detailed receipts remain outside Git.

### Explicit limitations and unrun checks

- **Installed production**: `5b7c0688a1afa4626a4ed913e759a763eb282c12` (tree `b1ec2c3e3b3472c5b62d11734d94fa8e1dc6941a`); repository-only documentation commits do not imply another deployment.
- **Native F5 hosting target**: Remains blocked by hosting unescaped underscore schema grant preflight; hosting grants were not altered and native suite was not forced.
- **Physical devices & assistive technology**: Physical devices, native zoom, virtual keyboards, and screen readers remain access-limited; synthetic view coverage does not replace physical device testing.
- **Financial schema & policy boundaries**: Salary payee remains free text; pending salary method enum remains required; booking columns remain distinct from doctor compensations; no overdraft rule, new setting, or schema was invented.
- **Dependency alert**: GitHub medium development-scope `minimatch` alert (#33, `package-lock.json`) is not resolved by code cleanup; locked dependencies and pinned CSS remain untouched.

### Delivery disposition and remaining work

PR26/PR27 are merged; both feature branches were removed only after exact-tip and release-ancestry checks. Documentation was distilled, links retained, and local/Hostinger assets with unique or recovery value preserved; no blanket cleanup or production branch reconfiguration.

This release is verified for the stated financial, registration, clinical-action, responsive and dialog journeys. Full role/Control Plane/kiosk mutation coverage and physical-device/AT/zoom/virtual-keyboard certification remain open/access-limited as marked in the scope table. Salary identity/schema and treasury policy limits are preserved rather than guessed. Any further release uses a new exact-source checkpoint and the established recovery safeguards; do not rerun old pinned helpers.
