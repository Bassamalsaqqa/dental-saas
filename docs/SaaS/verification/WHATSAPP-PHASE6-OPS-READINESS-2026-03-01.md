# WhatsApp Phase 6 Ops Readiness - 2026-03-01

- **Date:** 2026-03-01
- **Scope:** Add a repeatable operations readiness command for WhatsApp pilot monitoring and capture baseline execution evidence.

## Implemented

Added a new CLI command:
- `php spark whatsapp:ops-check`

File:
- `app/Commands/WhatsAppOpsCheck.php`

Purpose:
- Provide threshold-based health checks over a rolling window for:
  - WhatsApp attempt failure rate
  - rate-limited attempt count
  - stale processing lock count
  - suspended clinic count (informational)

Key options:
- `--hours` rolling window (default `24`)
- `--clinic-id` optional tenant-scoped checks
- `--min-attempts` gate for evaluating failure-rate threshold
- `--max-failure-rate`, `--max-rate-limited`, `--max-stuck`
- `--json` machine-readable output for cron/ops automation

Exit behavior:
- Returns `EXIT_SUCCESS` when all gates pass.
- Returns `EXIT_ERROR` when any gate fails.

## Verification

### 1) Command registration

```bat
php spark list | findstr /I "whatsapp:ops-check"
```

Observed:
- `whatsapp:ops-check        Phase 6 readiness check for WhatsApp delivery health with threshold gates.`

### 2) Default execution (human-readable)

```bat
php spark whatsapp:ops-check --hours 24
```

Observed (baseline environment):
- command executed successfully
- status output: `Overall status: PASS`
- metrics: zero attempts/rate limits/stale locks in the last 24h

### 3) JSON execution (automation-friendly)

```bat
php spark whatsapp:ops-check --hours 24 --min-attempts 1 --max-failure-rate 100 --max-rate-limited 99999 --max-stuck 99999 --json
```

Observed:
- JSON payload emitted with `status: "PASS"`
- includes `metrics`, `thresholds`, and per-check pass/fail state

## Notes

- Current environment had no WhatsApp attempts in the 24h window, so failure-rate gate is marked as "not evaluated" when attempts are below `--min-attempts`.
- This command is intended to be scheduled in pilot rollout environments and/or invoked from external monitoring.
