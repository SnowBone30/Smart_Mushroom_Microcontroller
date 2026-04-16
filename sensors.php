<?php
require_once 'config.php';
$page_title   = 'Sensors';
$current_page = 'sensors.php';
require_once 'partials/header.php';

// Fetch latest sensor readings from DB view `v_latest_readings` (safe fallback to empty)
$sensor_rows = function_exists('get_latest_readings') ? get_latest_readings() : [];

$status_map = ['optimal'=>['badge-green','OPTIMAL'],'normal'=>['badge-yellow','NORMAL'],'warning'=>['badge-red','WARNING']];

// Build display sensor array from DB rows or fallback to sample data
$display_sensors = [];
if (!empty($sensor_rows)) {
  foreach ($sensor_rows as $r) {
    $stype = strtolower($r['sensor_type'] ?? $r['type'] ?? '');
    $val = $r['value'] ?? null;
    $formatted = '';
    if ($val !== null) {
      if (strpos($stype, 'temp') !== false) $formatted = round((float)$val,1) . '°C';
      elseif (strpos($stype, 'humid') !== false) $formatted = round((float)$val,1) . '%';
      elseif (strpos($stype, 'co2') !== false) $formatted = round((float)$val) . ' PPM';
      else $formatted = (string)$val;
    }
    $display_sensors[] = [
      'id' => $r['sensor_id'] ?? $r['id'] ?? 'SN-?',
      'name' => $r['sensor_name'] ?? $r['name'] ?? ($r['sensor_type'] ?? 'Sensor'),
      'location' => $r['zone'] ?? $r['location'] ?? 'Zone A',
      'value' => $formatted,
      'status' => ($r['status'] ?? 'normal'),
      'color' => (strpos($stype,'temp')!==false)?'#3b9eff':((strpos($stype,'humid')!==false)?'#2ddc6e':'#f5c842'),
      'icon' => (strpos($stype,'temp')!==false)?'🌡️':((strpos($stype,'humid')!==false)?'💧':'💨'),
      'last' => isset($r['recorded_at']) ? date('g:i A', strtotime($r['recorded_at'])) : '',
    ];
  }
} else {
  // fallback sample sensors
  $display_sensors = [
    ['id'=>'SN-001','name'=>'DHT22 — Temperature','location'=>'Zone A','value'=>'24°C',   'status'=>'optimal', 'color'=>'#3b9eff','icon'=>'🌡️','last'=>'2 min ago'],
    ['id'=>'SN-002','name'=>'DHT22 — Humidity',   'location'=>'Zone A','value'=>'85%',    'status'=>'optimal', 'color'=>'#2ddc6e','icon'=>'💧','last'=>'2 min ago'],
    ['id'=>'SN-003','name'=>'MH-Z19B — CO₂',      'location'=>'Zone A','value'=>'600 PPM','status'=>'normal',  'color'=>'#f5c842','icon'=>'💨','last'=>'2 min ago'],
    ['id'=>'SN-004','name'=>'DHT22 — Temperature','location'=>'Zone B','value'=>'25°C',   'status'=>'optimal', 'color'=>'#3b9eff','icon'=>'🌡️','last'=>'3 min ago'],
    ['id'=>'SN-005','name'=>'DHT22 — Humidity',   'location'=>'Zone B','value'=>'82%',    'status'=>'optimal', 'color'=>'#2ddc6e','icon'=>'💧','last'=>'3 min ago'],
    ['id'=>'SN-006','name'=>'MH-Z19B — CO₂',      'location'=>'Zone B','value'=>'580 PPM','status'=>'normal',  'color'=>'#f5c842','icon'=>'💨','last'=>'3 min ago'],
  ];
}

// Summary stats from DB where possible
$active_sensors = function_exists('db_scalar') ? (int) db_scalar('SELECT COUNT(*) FROM sensors WHERE is_active = 1') : count($display_sensors);
$data_points_today = function_exists('db_scalar') ? (int) db_scalar("SELECT COUNT(*) FROM sensor_readings WHERE DATE(recorded_at) = CURDATE()") : 8640;
$last_cal_days = 3; // placeholder; could be read from settings
?>

<div class="page-heading fade-up">
  <h1>SENSORS <small>LIVE SENSOR MONITORING</small></h1>
  <div style="font-family:var(--mono);font-size:10px;color:var(--dim);display:flex;align-items:center;gap:8px">
    <span class="dot dot-green"></span> All sensors online
  </div>
</div>

<!-- Stat summary -->
<div class="grid-3 fade-up fade-up-1">
  <div class="stat-box" style="--sb-color:var(--green)">
    <div class="stat-label">ACTIVE SENSORS</div>
    <div class="stat-value"><?= htmlspecialchars((string)$active_sensors) ?><span class="stat-unit">/ <?= htmlspecialchars((string)max(6,$active_sensors)) ?></span></div>
    <div class="stat-sub">All zones online</div>
  </div>
  <div class="stat-box" style="--sb-color:var(--blue)">
    <div class="stat-label">DATA POINTS TODAY</div>
    <div class="stat-value"><?= number_format($data_points_today) ?><span class="stat-unit">pts</span></div>
    <div class="stat-sub">~1 reading / 10 sec</div>
  </div>
  <div class="stat-box" style="--sb-color:var(--yellow)">
    <div class="stat-label">LAST CALIBRATION</div>
    <div class="stat-value"><?= htmlspecialchars((string)$last_cal_days) ?><span class="stat-unit">days ago</span></div>
    <div class="stat-sub">Next due in <?= max(0, 30 - (int)$last_cal_days) ?> days</div>
  </div>
</div>

<!-- Sensor cards -->
<div class="grid-auto fade-up fade-up-2">
  <?php foreach($display_sensors as $s):
    [$bdg,$lbl] = $status_map[$s['status']] ?? $status_map['normal'];
  ?>
  <div class="card" style="border-left:3px solid <?= htmlspecialchars($s['color']) ?>;position:relative">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
      <span style="font-size:24px"><?= htmlspecialchars($s['icon']) ?></span>
      <div style="flex:1">
        <div style="font-family:var(--head);font-weight:700;font-size:13px;letter-spacing:1px;color:var(--bright)"><?= htmlspecialchars($s['name']) ?></div>
        <div style="font-family:var(--mono);font-size:10px;color:var(--dim)"><?= htmlspecialchars($s['id']) ?> &nbsp;·&nbsp; <?= htmlspecialchars($s['location']) ?></div>
      </div>
      <span class="badge <?= htmlspecialchars($bdg) ?>"><?= htmlspecialchars($lbl) ?></span>
    </div>
    <div style="font-family:var(--mono);font-size:28px;font-weight:700;color:<?= htmlspecialchars($s['color']) ?>;margin-bottom:8px"><?= htmlspecialchars($s['value']) ?></div>
    <div style="font-family:var(--mono);font-size:10px;color:var(--dim)">Last reading: <?= htmlspecialchars($s['last']) ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Live mini chart -->
<div class="card fade-up fade-up-3">
  <div class="card-title">ZONE A — 24-HOUR OVERVIEW</div>
  <div class="chart-container" style="height:180px">
    <canvas id="sensorChart"></canvas>
  </div>
</div>

<?php
// Build 24-hour hourly averages for zone A (temp & humidity) for the mini chart
$hours_arr = [];
for ($i = 0; $i < 24; $i++) {
  $hours_arr[] = sprintf('%02d:00', $i);
}
$chart_hours = json_encode($hours_arr);
$chart_temp24 = json_encode([21,21,20,20,19,19,20,21,22,23,24,24,24,24,23,23,23,24,24,24,24,23,23,22]);
$chart_hum24  = json_encode([87,87,88,88,89,89,88,87,86,85,85,85,84,85,85,86,86,85,85,85,85,86,86,87]);

if (function_exists('db_all')) {
    try {
        $rows = db_all(
            "SELECT DATE_FORMAT(sr.recorded_at, '%H:00') AS hour_label,
                    s.sensor_type, AVG(sr.value) AS avgv
             FROM sensor_readings sr
             JOIN sensors s ON s.id = sr.sensor_id
             WHERE sr.recorded_at >= NOW() - INTERVAL 24 HOUR
               AND s.zone = 'Zone A'
             GROUP BY hour_label, s.sensor_type
             ORDER BY hour_label ASC"
        );
        if ($rows) {
            $types = ['temp'=>[], 'humid'=>[]];
            $labels = [];
            foreach ($rows as $r) {
                $h = $r['hour_label'];
                $stype = strtolower($r['sensor_type'] ?? '');
                if (strpos($stype, 'temp') !== false) $types['temp'][$h] = (float)$r['avgv'];
                if (strpos($stype, 'humid') !== false) $types['humid'][$h] = (float)$r['avgv'];
                $labels[$h] = true;
            }
            if (!empty($labels)) {
                ksort($labels);
                $lbls = array_keys($labels);
                $ljson = json_encode($lbls);
                $tarr = []; $harr = [];
                foreach ($lbls as $lh) {
                    $tarr[] = $types['temp'][$lh] ?? null;
                    $harr[] = $types['humid'][$lh] ?? null;
                }
                $chart_hours = $ljson;
                $chart_temp24 = json_encode($tarr);
                $chart_hum24 = json_encode($harr);
            }
        }
    } catch (Throwable $e) {
        // keep defaults
    }
}
?>
<script>
const hours = <?= $chart_hours ?>;
const tempD  = <?= $chart_temp24 ?>;
const humidD = <?= $chart_hum24 ?>;

const ctx = document.getElementById('sensorChart').getContext('2d');
new Chart(ctx,{
  type:'line',
  data:{
    labels:hours,
    datasets:[
      {data:tempD,  label:'Temp °C',  borderColor:'#3b9eff',borderWidth:2,backgroundColor:'rgba(59,158,255,0.1)', fill:true,tension:0.4,pointRadius:0},
      {data:humidD, label:'Humidity%',borderColor:'#2ddc6e',borderWidth:2,backgroundColor:'rgba(45,220,110,0.1)', fill:true,tension:0.4,pointRadius:0},
    ]
  },
  options:{
    ...CHART_DEFAULTS,
    plugins:{...CHART_DEFAULTS.plugins,legend:{display:true,labels:{color:'#6a82a0',font:{family:'Share Tech Mono',size:10}}}},
    scales:{...CHART_DEFAULTS.scales,y:{...CHART_DEFAULTS.scales.y,min:15,max:95}}
  }
});
</script>

<?php require_once 'partials/footer.php'; ?>
