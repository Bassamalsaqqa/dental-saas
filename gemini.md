# Gemini / AGY entry point - Dental SaaS

Read and follow [AGENTS.md](AGENTS.md), the shared operating guide for all agents.

Implement the bounded assignment in [WORK_PROGRAM](docs/WORK_PROGRAM.md). Use [PROJECT_STATE](docs/PROJECT_STATE.md) for release/activation status and [START_HERE](docs/START_HERE.md) for relevant contracts; verify implementation claims against code and runtime.

AGY handoffs require the Owner's explicit `$agy-manual`. Use only the existing checkout with one writer. No headless dispatch, additional worktrees, branch/checkout/reset/clean/stash/rebase/merge, commits, pushes, PRs, deployments, migrations or production changes are authorized by a handoff.

Inspect the full diff and untracked files, run focused safe checks, update the existing checkpoint, and return changed paths, results, open concerns and `git status --short`. Leave no unattended background tasks. Codex owns independent acceptance.
