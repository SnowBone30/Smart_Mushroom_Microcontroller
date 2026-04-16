<?php
require_once 'config.php';
$page_title   = 'Dashboard';
$current_page = 'index.php';
require_once 'partials/header.php';
?>

<div class="page-heading fade-up">
  <h1>DASHBOARD <small>REAL-TIME OVERVIEW</small></h1>
  <div style="display:flex;gap:8px">
    <button class="btn btn-ghost" onclick="location.reload()">🔄 Refresh</button>
    <a href="system-flow.php" class="btn btn-ghost">🔄 System Flow</a>
  </div>
</div>

<!-- GAUGE ROW -->
<div class="grid-3 fade-up fade-up-1">

  <!-- Temperature -->
  <div class="gauge-card">
    <div class="gauge-header"><div class="gauge-num">1</div> TEMPERATURE</div>
    <div class="gauge-wrap">
      <svg class="gauge-svg" viewBox="0 0 190 115">
        <path d="M 15 108 A 78 78 0 0 1 175 108" fill="none" stroke="#1a2a3a" stroke-width="9" stroke-linecap="round"/>
        <path d="M 15 108 A 78 78 0 0 1 175 108" fill="none" stroke="#2ddc6e" stroke-width="9" stroke-linecap="round"
              stroke-dasharray="245" stroke-dashoffset="78" style="filter:drop-shadow(0 0 5px #2ddc6e)"/>
      </svg>
      <div class="gauge-value"><?= $sensor_data['temperature'] ?><sup>°C</sup></div>
    </div>
    <div class="status-badge"><?= $sensor_data['temp_status'] ?></div>
  </div>

  <!-- Humidity -->
  <div class="gauge-card">
    <div class="gauge-header"><div class="gauge-num">2</div> HUMIDITY</div>
    <div class="gauge-wrap">
      <svg class="gauge-svg" viewBox="0 0 190 115">
        <path d="M 15 108 A 78 78 0 0 1 175 108" fill="none" stroke="#1a2a3a" stroke-width="9" stroke-linecap="round"/>
        <path d="M 15 108 A 78 78 0 0 1 175 108" fill="none" stroke="#2ddc6e" stroke-width="9" stroke-linecap="round"
              stroke-dasharray="245" stroke-dashoffset="37" style="filter:drop-shadow(0 0 5px #2ddc6e)"/>
      </svg>
      <div class="gauge-value"><?= $sensor_data['humidity'] ?><sup>%</sup></div>
    </div>
    <div class="status-badge"><?= $sensor_data['humidity_status'] ?></div>
  </div>

  <!-- CO2 -->
  <div class="gauge-card">
    <div class="gauge-header"><div class="gauge-num">3</div> CO₂ LEVEL</div>
    <div class="gauge-wrap">
      <svg class="gauge-svg" viewBox="0 0 190 115">
        <path d="M 15 108 A 78 78 0 0 1 175 108" fill="none" stroke="#1a2a3a" stroke-width="9" stroke-linecap="round"/>
        <path d="M 15 108 A 78 78 0 0 1 175 108" fill="none" stroke="#2ddc6e" stroke-width="9" stroke-linecap="round"
              stroke-dasharray="245" stroke-dashoffset="98" style="filter:drop-shadow(0 0 5px #2ddc6e)"/>
      </svg>
      <div class="gauge-value"><?= $sensor_data['co2'] ?><sup style="font-size:14px"> PPM</sup></div>
    </div>
    <div class="status-badge normal"><?= $sensor_data['co2_status'] ?></div>
  </div>

</div>

<!-- CHARTS + SIDE PANEL -->
<div style="display:grid;grid-template-columns:1fr 1fr 260px;gap:14px;" class="fade-up fade-up-2">

  <!-- Temp & Humidity Chart -->
  <div class="card">
    <div class="card-title">TEMPERATURE &amp; HUMIDITY TRENDS <span class="ct-accent">(LAST 12 HOURS)</span></div>
    <div class="chart-container" style="height:200px">
      <canvas id="tempHumidChart"></canvas>
    </div>
  </div>

  <!-- CO2 Chart -->
  <div class="card">
    <div class="card-title">CO₂ LEVEL HISTORY</div>
    <div class="chart-container" style="height:200px">
      <canvas id="co2Chart" data-warning-line="600"></canvas>
    </div>
  </div>

  <!-- Control Panel -->
  <div class="card" style="display:flex;flex-direction:column;gap:18px">
    <div>
      <div class="card-title">SYSTEM CONTROL MODE</div>
      <div class="mode-toggle">
        <button class="mode-btn active" onclick="setMode(this)">Automatic</button>
        <button class="mode-btn" onclick="setMode(this)">Manual</button>
      </div>
    </div>
    <div>
      <div class="card-title">MANUAL OVERRIDE</div>
      <div style="display:flex;flex-direction:column;gap:12px">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <div>
            <div style="font-family:var(--head);font-weight:700;font-size:13px;letter-spacing:1px;color:var(--bright)">VENTILATION FANS</div>
            <div id="fans-state" class="text-green" style="font-family:var(--mono);font-size:10px;color:var(--green)">[ON]</div>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" id="fans-toggle" checked onchange="toggleDevice('fans')">
            <div class="toggle-track"></div><div class="toggle-thumb"></div>
          </label>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center">
          <div>
            <div style="font-family:var(--head);font-weight:700;font-size:13px;letter-spacing:1px;color:var(--bright)">WATER MISTERS</div>
            <div id="misters-state" style="font-family:var(--mono);font-size:10px;color:var(--dim)">[OFF]</div>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" id="misters-toggle" onchange="toggleDevice('misters')">
            <div class="toggle-track"></div><div class="toggle-thumb"></div>
          </label>
        </div>
      </div>
      <div style="font-family:var(--mono);font-size:10px;color:var(--green);margin-top:10px;display:flex;align-items:center;gap:5px">
        <span class="dot dot-green"></span> Fans Active (3:15 PM)
      </div>
    </div>
    <div>
      <div class="card-title">RECENT ALERTS</div>
      <div style="display:flex;flex-direction:column;gap:8px">
        <?php foreach(array_slice($alerts,0,2) as $a):
          $atype = $a['type'] ?? $a['severity'] ?? 'info';
          $timeVal = $a['created_at'] ?? $a['time'] ?? null;
          $timeDisplay = $timeVal ? date('g:i A', strtotime($timeVal)) : '';
          $title = $a['title'] ?? $a['event_name'] ?? '';
          $desc = $a['desc'] ?? $a['message'] ?? $a['trigger_desc'] ?? '';
        ?>
        <div class="alert-item <?= htmlspecialchars($atype) ?>">
          <div class="alert-body">
            <div class="alert-time"><?= htmlspecialchars($timeDisplay) ?></div>
            <div class="alert-title"><?= htmlspecialchars($title) ?></div>
            <div class="alert-desc"><?= htmlspecialchars($desc) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

</div>

<script>
<?php
// Build 12-hour hourly averages for Temp, Humidity, CO2 for charts (fallback to static arrays)
$chart_labels = json_encode(['10 AM','11 AM','12 PM','1 PM','2 PM','3 PM','4 PM','5 PM','6 PM']);
$chart_temp = json_encode([22.8,22.2,21.6,21.0,20.4,20.1,19.8,19.5,19.2]);
$chart_hum  = json_encode([19.5,20.0,21.5,22.8,23.2,23.6,24.0,24.2,24.5]);
$chart_co2 = json_encode([90,110,140,190,280,370,430,500,560]);

if (function_exists('db_all')) {
    try {
        $rows = db_all(
            "SELECT DATE_FORMAT(sr.recorded_at, '%Y-%m-%d %H:00:00') AS hour_label,
                    s.sensor_type, AVG(sr.value) AS avgv
             FROM sensor_readings sr
             JOIN sensors s ON s.id = sr.sensor_id
             WHERE sr.recorded_at >= NOW() - INTERVAL 12 HOUR
             GROUP BY hour_label, s.sensor_type
             ORDER BY hour_label ASC"
        );

        if ($rows) {
            $map = [];
            $types = ['temp'=>[], 'humid'=>[], 'co2'=>[]];
            foreach ($rows as $r) {
                $hour = date('g A', strtotime($r['hour_label']));
                $stype = strtolower($r['sensor_type'] ?? '');
                if (strpos($stype, 'temp') !== false) $types['temp'][$hour] = (float)$r['avgv'];
                elseif (strpos($stype, 'humid') !== false) $types['humid'][$hour] = (float)$r['avgv'];
                elseif (strpos($stype, 'co2') !== false) $types['co2'][$hour] = (float)$r['avgv'];
            }
            // build sorted labels set
            $labels = [];
            foreach ($types as $t) {
                foreach ($t as $h => $_) $labels[$h] = true;
            }
            if (!empty($labels)) {
                ksort($labels);
                $lbls = array_keys($labels);
                $ljson = json_encode($lbls);
                $tarr = []; $harr = []; $carr = [];
                foreach ($lbls as $h) {
                    $tarr[] = $types['temp'][$h] ?? null;
                    $harr[] = $types['humid'][$h] ?? null;
                    $carr[] = $types['co2'][$h] ?? null;
                }
                $chart_labels = $ljson;
                $chart_temp = json_encode($tarr);
                $chart_hum  = json_encode($harr);
                $chart_co2 = json_encode($carr);
            }
        }
    } catch (Throwable $e) {
        // leave defaults
    }
}
?>

const labels = <?= $chart_labels ?>;

// Temp/Humidity
const c1 = document.getElementById('tempHumidChart').getContext('2d');
new Chart(c1, {
  type: 'line',
  data: {
    labels,
    datasets: [
      { data: <?= $chart_temp ?>, borderColor:'#3b9eff', borderWidth:2,
        backgroundColor:'rgba(59,158,255,0.1)', fill:true, tension:0.45, pointRadius:0 },
      { data: <?= $chart_hum ?>, borderColor:'#2ddc6e', borderWidth:2,
        backgroundColor:'rgba(45,220,110,0.1)', fill:true, tension:0.45, pointRadius:0 },
    ]
  },
  options: { ...CHART_DEFAULTS, scales: { ...CHART_DEFAULTS.scales, y: { ...CHART_DEFAULTS.scales.y, min:18, max:26 } } }
});

// CO2
const c2 = document.getElementById('co2Chart').getContext('2d');
new Chart(c2, {
  type: 'line',
  data: {
    labels,
    datasets: [{
      data: <?= $chart_co2 ?>, borderColor:'#2ddc6e', borderWidth:2,
      backgroundColor:'rgba(45,220,110,0.15)', fill:true, tension:0.4, pointRadius:0
    }]
  },
  options: { ...CHART_DEFAULTS, scales: { ...CHART_DEFAULTS.scales, y: { ...CHART_DEFAULTS.scales.y, min:0, max:800 } } }
});
</script>

<?php require_once 'partials/footer.php'; ?>
