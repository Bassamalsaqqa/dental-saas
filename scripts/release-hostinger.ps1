param(
    [switch]$SkipGitStatus
)

$ErrorActionPreference = 'Stop'

$repoRoot = Resolve-Path (Join-Path $PSScriptRoot '..')
Set-Location $repoRoot

$cssPath = Join-Path $repoRoot 'public/assets/css/tailwind.css'
$beforeHash = ''

if (Test-Path $cssPath) {
    $beforeHash = (Get-FileHash $cssPath -Algorithm SHA256).Hash
}

Write-Host 'Building Tailwind for Hostinger deployment...' -ForegroundColor Cyan
& npm.cmd run build

if ($LASTEXITCODE -ne 0) {
    throw "Tailwind build failed with exit code $LASTEXITCODE."
}

$afterHash = ''
if (Test-Path $cssPath) {
    $afterHash = (Get-FileHash $cssPath -Algorithm SHA256).Hash
}

Write-Host ''
Write-Host 'Release Prep Summary' -ForegroundColor Green
Write-Host "CSS file: public/assets/css/tailwind.css"

if ($beforeHash -and $afterHash -and $beforeHash -ne $afterHash) {
    Write-Host 'Compiled CSS changed in this run.' -ForegroundColor Yellow
} elseif ($afterHash) {
    Write-Host 'Compiled CSS is up to date. No content change detected.' -ForegroundColor DarkYellow
} else {
    Write-Host 'Compiled CSS file was not found after build.' -ForegroundColor Red
}

Write-Host ''
Write-Host 'Deploy these together to Hostinger:' -ForegroundColor Cyan
Write-Host '  1. PHP/view/controller changes'
Write-Host '  2. public/assets/css/tailwind.css'

if (-not $SkipGitStatus) {
    Write-Host ''
    Write-Host 'Git status:' -ForegroundColor Cyan
    & git status -sb
}
