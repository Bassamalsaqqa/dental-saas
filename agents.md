# Codex Operating Guide - SaaS Program (Planner/Reviewer)

## A) Role Definition (Codex = Planner/Reviewer)
- Primary mission: select Task IDs, sequence work, generate Gemini prompts, and review changes against the SaaS spec.
- Secondary mission: enforce evidence-based verification, prevent regression/drift, and maintain an audit-grade trail.

## B) Single Source of Truth
1) `docs/SaaS/SAAS_PROGRAM.md` governs SaaS scope, priority, and DoD.
2) `docs/SaaS/TENANT_CONTEXT_CONTRACT.md` governs tenant context mechanics.
3) `docs/API_CONTRACT.md` governs `/api/v1/*` semantics.
4) Guardrails: `docs/SaaS/guardrails/*` + repo guardrail scripts.

`docs/SECURITY_HARDENING_PROGRAM.md` is historical unless explicitly reopened.

## C) Non-Negotiable Rules
- Codex assigns Task IDs explicitly each iteration (1–3 max).
- Gemini must not expand scope; any adjacent issues are “Findings” only.
- Reviews must be evidence-based (paths, diffs, rg output, curl output).
- Fail-closed doctrine always.
- No redirects/HTML for `/api/*`.
- Airlock separation enforced: control plane vs tenant plane.

## D) Session Start Checklist
1) Read `docs/SaaS/SAAS_PROGRAM.md` and latest SaaS logs:
   - `docs/SaaS/logs/IMPLEMENTATION_LOG_SaaS.md`
   - `docs/SaaS/logs/DECISION_LOG_SaaS.md`
2) Run drift scans (examples; adjust to your scripts):
   - `rg -n "innerHTML|outerHTML|insertAdjacentHTML|\.html\(" app/Views`
   - `php spark routes | findstr /I "controlplane api/v1 api/search"`
   - `bash scripts/ci/saas_guardrails.sh` (if present)
3) Confirm clean tree:
   - `git status -sb`

## E) Prompt Template (Codex → Gemini)
Task IDs: [P5-XX, CP-XX, API-BX-XX]

Scope/DoD:
- [paste DoD from docs/SaaS/SAAS_PROGRAM.md or API_CONTRACT.md]

Files to touch:
- [explicit list]

Files to avoid:
- [explicit list]

Expected behavior:
- [bullet list]

Verification requirements:
- [exact commands + expected outputs]

Documentation updates required:
- docs/SaaS/verification/<TASK_ID>.md
- docs/SaaS/logs/IMPLEMENTATION_LOG_SaaS.md (append)
- docs/SaaS/logs/DECISION_LOG_SaaS.md (append, if decision made)

Constraints:
- No scope creep. Fail-closed only. No API redirects. Respect airlock doctrine.

## F) Environment & Infrastructure
**Local Path:** `C:\xampp\htdocs\dental-saas`

1. Docker Container Info
   - **Container Name:** `mysql8-dental`
   - **Image:** `mysql:8.0`
   - **Status:** Running (Port Mapping: `0.0.0.0:3307->3306/tcp`)
   - **Container ID:** `7eb3024aa7d5`

2. Active `.env` Configuration
   - **Environment:** `development`
   - **Base URL:** `http://localhost/dental-saas/`
   - **Database Host:** `127.0.0.1`
   - **Database Port:** `3307`
   - **Database Name:** `democa_dental_saas`
   - **DB Credentials:** `root` / `root`
   - **Encryption Key:** `9f3a2c1d8b7e6a5c4d3e2f1a0b9c8d7e`
   - **Session Cookie:** `ci_session_saas`