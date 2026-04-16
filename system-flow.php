<?php
require_once 'config.php';
$page_title   = 'System Flow';
$current_page = 'system-flow.php';
require_once 'partials/header.php';

// Fetch latest sensor summaries and settings to populate descriptive text
$latest_rows = function_exists('get_latest_readings') ? get_latest_readings() : [];
$temp_val = null; $hum_val = null; $co2_val = null;
foreach ($latest_rows as $r) {
  $stype = strtolower($r['sensor_type'] ?? '');
  if (strpos($stype, 'temp') !== false && $temp_val === null) $temp_val = $r['value'];
  if (strpos($stype, 'humid') !== false && $hum_val === null) $hum_val = $r['value'];
  if ((strpos($stype, 'co2') !== false || strpos($stype, 'carbon') !== false) && $co2_val === null) $co2_val = $r['value'];
}

// settings fallback
$tmax = function_exists('get_setting') ? (int) get_setting('temp_max', '28') : 28;
$hmin = function_exists('get_setting') ? (int) get_setting('humid_min', '75') : 75;
$cmax = function_exists('get_setting') ? (int) get_setting('co2_max', '600') : 600;

$temp_disp = $temp_val !== null ? round((float)$temp_val,1) . '°C' : '24°C';
$hum_disp  = $hum_val !== null ? round((float)$hum_val,1) . '%' : '85%';
$co2_disp  = $co2_val !== null ? round((float)$co2_val) . ' PPM' : '600 PPM';

$phases = [
  ['number'=>'01','label'=>'INPUT PHASE',  'subtitle'=>'Data Gathering','color'=>'#3b9eff','glow'=>'rgba(59,158,255,.15)','icon'=>'📡',
   'sections'=>[
    ['title'=>'Environmental Sensing','icon'=>'🌡️','body'=>'The <strong>DHT22</strong> (Temp/Humidity) and <strong>MH-Z19B</strong> (CO₂) sensors constantly "sniff" the air inside the grow room.','chips'=>['DHT22 Sensor','MH-Z19B Sensor','Grow Room Air']],
    ['title'=>'Signal Processing',    'icon'=>'⚡','body'=>'The <strong>Arduino/ESP32</strong> receives electronic signals and converts them into digital values (' . $temp_disp . ', ' . $hum_disp . ', ' . $co2_disp . ').','chips'=>[ $temp_disp . ' Temp', $hum_disp . ' Humidity', $co2_disp . ' CO₂' ]],
  ]],
  ['number'=>'02','label'=>'PROCESS PHASE','subtitle'=>'The Logic',    'color'=>'#f5c842','glow'=>'rgba(245,200,66,.15)','icon'=>'⚙️',
   'sections'=>[
    ['title'=>'Threshold Comparison','icon'=>'🔍','body'=>'The system compares live data against your <strong>Target Settings</strong> — e.g., Is temperature higher than ' . $tmax . '°C?','chips'=>['IF Temp > ' . $tmax . '°C','IF Humidity < ' . $hmin . '%','IF CO₂ > ' . $cmax . ' PPM']],
    ['title'=>'Decision Making',     'icon'=>'🧠','body'=>'<strong>IF</strong> too hot → Trigger Fans &nbsp;|&nbsp; <strong>IF</strong> too dry → Trigger Misters &nbsp;|&nbsp; <strong>IF</strong> CO₂ high → Trigger Exhaust.','chips'=>['Fan Logic','Mister Logic','Exhaust Logic']],
    ['title'=>'Cloud Sync',          'icon'=>'☁️','body'=>'The <strong>ESP32</strong> sends a copy of the data to your <strong>MySQL Database</strong> via Wi-Fi so the dashboard can update in real-time.','chips'=>['ESP32 Wi-Fi','MySQL DB','REST API']],
  ]],
  ['number'=>'03','label'=>'CONTROL PHASE','subtitle'=>'The Action',   'color'=>'#ff6b35','glow'=>'rgba(255,107,53,.15)','icon'=>'🔌',
   'sections'=>[
    ['title'=>'Relay Activation',  'icon'=>'⚡','body'=>'The microcontroller sends a signal to a <strong>Relay Module</strong>, which acts as a high-power switch for 220V appliances.','chips'=>['Relay Module','Signal Output','220V Switch']],
    ['title'=>'Hardware Execution','icon'=>'🌀','body'=>'Physical <strong>220V fans</strong> or <strong>water pumps</strong> turn ON/OFF automatically to correct the environment — no human required.','chips'=>['220V Fans','Water Pumps','Auto Control']],
  ]],
  ['number'=>'04','label'=>'OUTPUT PHASE', 'subtitle'=>'User Interface','color'=>'#2ddc6e','glow'=>'rgba(45,220,110,.15)','icon'=>'🖥️',
   'sections'=>[
    ['title'=>'Real-Time Monitoring','icon'=>'📊','body'=>'Live gauges and "heartbeat" graphs on your <strong>Web Dashboard</strong> show temperature, humidity, and CO₂ at all times.','chips'=>['Live Gauges','Trend Graphs','Web Dashboard']],
    ['title'=>'Notifications',       'icon'=>'🔔','body'=>'If a sensor fails or levels stay dangerous, the system sends an <strong>alert</strong> directly to the dashboard.','chips'=>['Sensor Alerts','Danger Warnings','Dashboard Push']],
    ['title'=>'Data Archiving',      'icon'=>'📁','body'=>'Every action is recorded in the <strong>Logs</strong>, enabling report generation for your capstone documentation.','chips'=>['Action Logs','Report Gen','Capstone Docs']],
  ]],
];
?>

<div class="page-heading fade-up">
  <h1>SYSTEM FLOW <small>AUTOMATED CONTROL PIPELINE</small></h1>
  <a href="index.php" class="btn btn-ghost">📊 Back to Dashboard</a>
</div>

<!-- Pipeline overview bar -->
<div class="card fade-up fade-up-1" style="display:flex;align-items:center;justify-content:center;gap:0;padding:20px;flex-wrap:wrap;gap:0">
  <?php
  $pip = [['INPUT','#3b9eff'],['PROCESS','#f5c842'],['CONTROL','#ff6b35'],['OUTPUT','#2ddc6e']];
  foreach($pip as $i=>[$lbl,$c]):
    if($i>0) echo '<div style="flex:1;max-width:60px;height:2px;background:linear-gradient(90deg,'.$pip[$i-1][1].','.$c.')"></div>';
  ?>
  <div style="padding:8px 20px;border-radius:30px;border:1px solid <?= $c ?>;color:<?= $c ?>;
    background:<?= str_replace(')',',0.08)',$c) ?>;font-family:var(--head);font-weight:700;font-size:12px;letter-spacing:2px;white-space:nowrap">
    <?= sprintf('%02d', $i+1) ?> <?= $lbl ?>
  </div>
  <?php endforeach; ?>
</div>

<!-- Phase cards -->
<?php foreach($phases as $i => $ph): ?>
<?php if($i > 0): $pc = $phases[$i-1]['color']; $cc = $ph['color']; ?>
<div style="display:flex;align-items:center;justify-content:center;gap:12px;margin:2px 0">
  <div style="flex:1;height:2px;background:linear-gradient(90deg,transparent,<?= $pc ?>,<?= $cc ?>)"></div>
  <div style="font-family:var(--mono);font-size:10px;color:<?= $cc ?>;letter-spacing:2px;
    border:1px solid <?= $cc ?>;padding:3px 12px;border-radius:20px;opacity:.7">↓ DATA FLOWS DOWN ↓</div>
  <div style="flex:1;height:2px;background:linear-gradient(90deg,<?= $cc ?>,transparent)"></div>
</div>
<?php endif; ?>

<div class="card fade-up" style="--phase-c:<?= $ph['color'] ?>;border-top:3px solid <?= $ph['color'] ?>;padding:0">
  <!-- Phase header -->
  <div style="display:flex;align-items:center;gap:14px;padding:18px 22px;border-bottom:1px solid var(--border);
    background:linear-gradient(135deg,<?= $ph['glow'] ?> 0%,transparent 50%)">
    <div style="font-family:var(--mono);font-size:38px;font-weight:700;color:<?= $ph['color'] ?>;
      opacity:.2;min-width:68px;text-align:right;letter-spacing:-2px"><?= $ph['number'] ?></div>
    <div style="width:48px;height:48px;border-radius:10px;border:1px solid <?= $ph['color'] ?>;
      background:<?= $ph['glow'] ?>;display:flex;align-items:center;justify-content:center;
      font-size:22px;flex-shrink:0;box-shadow:0 0 16px <?= $ph['glow'] ?>"><?= $ph['icon'] ?></div>
    <div style="flex:1">
      <div style="font-family:var(--head);font-weight:700;font-size:19px;letter-spacing:3px;color:var(--bright)"><?= $ph['label'] ?></div>
      <div style="font-family:var(--mono);font-size:11px;color:<?= $ph['color'] ?>;letter-spacing:2px;margin-top:2px"><?= $ph['subtitle'] ?></div>
    </div>
    <div style="font-family:var(--mono);font-size:10px;color:<?= $ph['color'] ?>;
      border:1px solid <?= $ph['color'] ?>;padding:3px 12px;border-radius:20px;
      background:<?= $ph['glow'] ?>;display:flex;align-items:center;gap:6px">
      <span class="dot dot-green"></span> ACTIVE
    </div>
  </div>

  <!-- Sections grid -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));background:var(--border);gap:1px">
    <?php foreach($ph['sections'] as $sec): ?>
    <div style="background:var(--bg-card);padding:18px 20px">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
        <div style="width:28px;height:28px;border-radius:6px;border:1px solid <?= $ph['color'] ?>;
          background:<?= $ph['glow'] ?>;display:flex;align-items:center;justify-content:center;font-size:13px"><?= $sec['icon'] ?></div>
        <div style="font-family:var(--head);font-weight:700;font-size:13px;letter-spacing:1.5px;color:var(--bright)"><?= $sec['title'] ?></div>
      </div>
      <div style="font-family:var(--mono);font-size:11.5px;color:var(--dim);line-height:1.75;margin-bottom:12px"><?= $sec['body'] ?></div>
      <div style="display:flex;flex-wrap:wrap;gap:5px">
        <?php foreach($sec['chips'] as $chip): ?>
        <span style="font-family:var(--mono);font-size:10px;padding:3px 9px;border-radius:4px;
          border:1px solid rgba(255,255,255,.1);color:var(--dim);background:rgba(255,255,255,.03);
          cursor:default;transition:all .2s" onmouseover="this.style.borderColor='<?= $ph['color'] ?>';this.style.color='<?= $ph['color'] ?>'"
          onmouseout="this.style.borderColor='rgba(255,255,255,.1)';this.style.color=''">
          <?= $chip ?>
        </span>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endforeach; ?>

<?php require_once 'partials/footer.php'; ?>
