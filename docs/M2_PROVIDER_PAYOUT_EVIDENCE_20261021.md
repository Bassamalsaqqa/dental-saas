# M2 provider-payout lifecycle: focused evidence

Base: deployed M1 `6908df7c116ccd9d01941724835f071574ef17e1` (tree `c1ed1da1d8fe4f002a605b04f83eb53c9f03fe58`). This branch is for review only, not source integration or deployment.

## Behavior

- Historical void gross, share, and paid amounts are retained; outstanding void balance is zero.
- Writes, removals, revivals, and inline payments on void allocations are rejected.
- Identical semantic repost performs no write; original decimal token prevents float truncation waivers.
- Native provider currency buckets are explicitly shown.
- Mixed or unknown combined monetary totals and incompatible base-currency cash/net positions are marked unavailable instead of summing across currencies or inventing FX rates.
- Finance ledger selects payment currency; original invoice FX accounting remains unchanged.
- Unitless old data is labelled unknown.
- No finance rows or agreements are rewritten.
- Actual routed tests cover: anonymous access, denied permissions, foreign-clinic tenant isolation, direct POST, void lifecycle, exact retry, active positive amounts, and payment idempotency.

## Independent verification (2026-10-21)

Codex independently verified the candidate branch across standalone, database, and routed web-kernel suites:
- **47 standalone assertions:** exact-decimal arithmetic, identity mapping, and text/void roundtrips.
- **51 real MariaDB lifecycle assertions:** status predicates, aggregate totals, void isolation, and rollback verification.
- **25 native-currency SQL assertions:** multi-currency bucket isolation, unknown currency handling, and aggregation consistency.
- **Routed CodeIgniter web-kernel checks (592 total):** passed 50 workflow assertions plus 542 original-row integrity fingerprints. Exercises real routes, authentication sessions, tenant filters, permissions, and CSRF protection (does not constitute wire HTTP or interactive browser certification).

### Testbed & fixture preservation
- **Clone:** `/home/u556956644/.denta-stage-20261017/m2-20261020-base6908df7` (PHP 8.3 / MariaDB 11.8.9).
- **Database:** Synthetic DB `u556956644_dentstage1017` (GUID `b60809432ec1d915b0b975aef8484e43`).
- **Fixture mechanics:** SQL lifecycle assertions roll back all temporary fixtures. Routed kernel fixtures are newly owned synthetic rows retained for repeatable sessions (not claiming zero extra rows).
- **Data integrity:** All original 542 synthetic rows and 68 schemas were preserved.
- **Environment integrity:** Private environment (`.env`) and egress-blocking configuration were hash-preserved. No production writes, schema alterations, migrations, dependency updates, or GitHub Actions changes.

## Limits and release gates

- Production read-only ILS provider allocations do not prove absence of future mixed currency risk; ordinary source-finance foreign currency is already converted to base before allocations, and no automated conversion policy has been introduced.
- Actual visual mobile and interactive browser certification remains pending.
- Separate contract PR is excluded from this scope.
- External exact-head financial approval remains required.
- Source integration and deployment require explicit Owner authorization.
- Dynamic doctor scheduling remains OFF.
