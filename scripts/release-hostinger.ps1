param(
    [switch]$SkipGitStatus
)

$ErrorActionPreference = 'Stop'

$repoRoot = Resolve-Path (Join-Path $PSScriptRoot '..')
$cssPath = Join-Path $repoRoot 'public/assets/css/tailwind.css'
$pinnedHash = '3A51D2FFFB54AE89FBB4188E3B1DDFC2BCE09C7E81148ADBA78CE750B7FD801A'

if (Test-Path $cssPath) {
    $currentHash = (Get-FileHash $cssPath -Algorithm SHA256).Hash
    if ($currentHash -ne $pinnedHash) {
        Write-Error "CRITICAL: Pinned Tailwind CSS hash mismatch! Expected $pinnedHash, found $currentHash"
    }
}

Write-Error "Unsafe release shortcut disabled. Automated Tailwind builds and direct release shortcuts are prohibited per AGENTS.md, README.md, and docs/OPERATIONS_RUNBOOK.md. The production Tailwind stylesheet is pinned (SHA256 $pinnedHash) and must not be rebuilt or overwritten. Deployment, migrations, and feature activation require explicit, separate Owner authorization. Follow the controlled release procedure in docs/OPERATIONS_RUNBOOK.md."
exit 1
