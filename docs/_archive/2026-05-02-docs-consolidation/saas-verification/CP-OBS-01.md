# CP-OBS-01 Verification

## Scope
Deliver Chart.js observability panels for the control plane dashboard (MRR line, clinic status doughnut, activity bar) powered by freshly assembled chart_data.

## Evidence

### Chart payload wiring
Command:
`rg -n "chart_data" app\\Controllers\\ControlPlane\\Dashboard.php`

Output:
```
219:            'chart_data' => [
```

### Chart canvases + scripts
Command:
`rg -n "mrrGrowthChart|clinicStatusChart|activityVolumeChart" app\\Views\\control_plane\\dashboard.php`

Output:
```
82:            <canvas id="mrrGrowthChart" height="180"></canvas>
92:                <canvas id="clinicStatusChart" height="200"></canvas>
102:                <canvas id="activityVolumeChart" height="200"></canvas>
200:        const mrrCtx = document.getElementById('mrrGrowthChart');
255:        const statusCtx = document.getElementById('clinicStatusChart');
284:        const activityCtx = document.getElementById('activityVolumeChart');
```
