# M2 provider-payout lifecycle: focused evidence

Base: deployed M1 `6908df7c116ccd9d01941724835f071574ef17e1` (tree `c1ed1da1d8fe4f002a605b04f83eb53c9f03fe58`). This branch is for review only, not source integration or deployment.

## Behavior

Outstanding provider balances exclude `void` allocations consistently in clinic totals, status/provider/user/directory summaries and patient money trails. The finance-detail summary and row amounts also exclude void debt, and do not offer a settlement form for void rows. Historical shares, cached paid amounts, actual payout-payment rows, gross totals and provider identities remain visible and unchanged.

The existing browser allocation writer preserves exact equivalent void roundtrips without resynchronizing or rewriting the void row. Attempts to remove, change or add inline payments to a void allocation fail closed inside the existing caller-owned transaction. This does not introduce a cancellation, refund, reversal or commission-adjustment workflow. EN/AR notes explain outstanding versus historical amounts.

## Independent verification (2026-10-21)

AGY supplied an incomplete draft and an unaccepted test harness before exhausting its account quota. Codex inspected the actual eight runtime-file diff and replaced the weak harness with actual-model/controller-method SQL assertions. No AGY self-report is treated as approval. A second independent source reviewer identified a pre-existing finance-detail display inconsistency; the adjacent summary, row and settlement-control predicates were corrected in the same bounded package. This view/controller adjustment was inspected and PHP-linted, then checked through the actual show controller and rendered EN/AR views inside rollback-only SQL fixtures; no HTTP/filter or browser-interaction certification is claimed.

`tests/regression/M2ProviderPayoutLifecycleSql.php` passed 41 focused checks on Hostinger PHP 8.3 / real MariaDB through the canonical private M2 bootstrap. It verifies all six changed model aggregates, positive legacy void balances, actual cash versus document dates, single-count split gross, cached paid history above the payment ledger, actual patient money trails, exact void reposts, active updates, rejected mutations and caller rollback, other-clinic isolation, EN/AR message keys, actual detail summary/rendered settlement controls and fixture rollback. Replacing only the private test model with the unchanged M1 model caused the expected failure at `Actual aggregate delta total_provider_balance`; the exact candidate model was restored and hash-verified.

Private clone: `/home/u556956644/.denta-stage-20261017/m2-20261020-base6908df7`. Synthetic-only database: `u556956644_dentstage1017`, identity `b60809432ec1d915b0b975aef8484e43`. Its bootstrap checks database identity, restricted grants, paths and synthetic-only marker before execution. All new fixtures rolled back. Preservation verification passed all 542 pre-existing synthetic rows and 68 schemas, with zero additional rows. AUTO_INCREMENT advancement is expected. Private `.env` and egress-blocking Services configuration were hash-preserved. No production writes, schema changes, migrations, dependency changes or GitHub Actions.

Raw source-freeze manifests, SQL output, negative-control and preservation output are retained privately in the operator's `denta-m2-20261020` evidence directory, without credentials or customer data in this PR.

## Limits and release gates

- These are actual SQL and controller-method tests, not HTTP permission/filter or rendered-browser certification.
- Single-currency ILS fixtures do not certify FX conversion or mixed-currency reports. Existing refund/reversal and commission-policy limitations remain open; no historical agreements or payments are rewritten.
- Current user labels can reflect directory changes; no identity remapping is introduced.
- Separate contract-signing work remains unaccepted and is not included here.
- Independent exact-head financial review remains required. Owner authorization, a fresh recovery checkpoint and pinned combined-tree verification remain required before integration/deployment. Scheduling stays OFF.
