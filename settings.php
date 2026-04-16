<?php
require_once 'config.php';
$page_title   = 'Settings';
$current_page = 'settings.php';

// Handle form save — persist to DB using `save_setting` when available
$saved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('save_setting')) {
    save_setting('temp_min', $_POST['temp_min'] ?? '18');
    save_setting('temp_max', $_POST['temp_max'] ?? '28');
    save_setting('humid_min', $_POST['humid_min'] ?? '75');
    save_setting('humid_max', $_POST['humid_max'] ?? '90');
    save_setting('co2_max', $_POST['co2_max'] ?? '600');
    save_setting('co2_danger', $_POST['co2_danger'] ?? '800');
  }
  $saved = true;
}

// Load thresholds from DB (fall back to sensible defaults)
$thresholds = [
  'temp_min'   => (int) (function_exists('get_setting') ? get_setting('temp_min', '18') : 18),
  'temp_max'   => (int) (function_exists('get_setting') ? get_setting('temp_max', '28') : 28),
  'humid_min'  => (int) (function_exists('get_setting') ? get_setting('humid_min', '75') : 75),
  'humid_max'  => (int) (function_exists('get_setting') ? get_setting('humid_max', '90') : 90),
  'co2_max'    => (int) (function_exists('get_setting') ? get_setting('co2_max', '600') : 600),
  'co2_danger' => (int) (function_exists('get_setting') ? get_setting('co2_danger', '800') : 800),
];

require_once 'partials/header.php';
?>

<div class="page-heading fade-up">
  <h1>SETTINGS <small>SYSTEM CONFIGURATION</small></h1>
  <?php if($saved): ?>
  <span class="badge badge-green">✓ SETTINGS SAVED</span>
  <?php endif; ?>
</div>

<form method="POST" action="settings.php">

<div class="grid-2 fade-up fade-up-1">

  <!-- Threshold Settings -->
  <div class="card">
    <div class="card-title">🌡️ THRESHOLD SETTINGS</div>

    <div class="form-group">
      <label class="form-label">TEMPERATURE RANGE <span>°C</span></label>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <div>
          <div style="font-family:var(--mono);font-size:10px;color:var(--dim);margin-bottom:4px">MIN</div>
          <input class="form-input" type="number" name="temp_min" value="<?= $thresholds['temp_min'] ?>" min="0" max="40">
        </div>
        <div>
          <div style="font-family:var(--mono);font-size:10px;color:var(--dim);margin-bottom:4px">MAX</div>
          <input class="form-input" type="number" name="temp_max" value="<?= $thresholds['temp_max'] ?>" min="0" max="40">
        </div>
      </div>
      <div class="form-hint">Fans activate if temperature exceeds MAX value.</div>
    </div>

    <div class="form-group">
      <label class="form-label">HUMIDITY RANGE <span>%</span></label>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <div>
          <div style="font-family:var(--mono);font-size:10px;color:var(--dim);margin-bottom:4px">MIN</div>
          <input class="form-input" type="number" name="humid_min" value="<?= $thresholds['humid_min'] ?>" min="0" max="100">
        </div>
        <div>
          <div style="font-family:var(--mono);font-size:10px;color:var(--dim);margin-bottom:4px">MAX</div>
          <input class="form-input" type="number" name="humid_max" value="<?= $thresholds['humid_max'] ?>" min="0" max="100">
        </div>
      </div>
      <div class="form-hint">Misters activate if humidity drops below MIN value.</div>
    </div>

    <div class="form-group">
      <label class="form-label">CO₂ THRESHOLDS <span>PPM</span></label>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <div>
          <div style="font-family:var(--mono);font-size:10px;color:var(--yellow);margin-bottom:4px">WARNING</div>
          <input class="form-input" type="number" name="co2_max" value="<?= $thresholds['co2_max'] ?>" min="0" max="2000">
        </div>
        <div>
          <div style="font-family:var(--mono);font-size:10px;color:var(--red);margin-bottom:4px">DANGER</div>
          <input class="form-input" type="number" name="co2_danger" value="<?= $thresholds['co2_danger'] ?>" min="0" max="2000">
        </div>
      </div>
      <div class="form-hint">Exhaust fans activate at WARNING level. DANGER triggers emergency alert.</div>
    </div>
  </div>

  <!-- System Settings -->
  <div class="card">
    <div class="card-title">⚙️ SYSTEM SETTINGS</div>

    <div class="form-group">
      <label class="form-label">CONTROL MODE</label>
      <select class="form-select" name="control_mode">
        <option value="auto" selected>Automatic (Recommended)</option>
        <option value="manual">Manual Override</option>
        <option value="scheduled">Scheduled</option>
      </select>
    </div>

    <div class="form-group">
      <label class="form-label">SENSOR POLL INTERVAL <span>seconds</span></label>
      <input class="form-input" type="number" name="poll_interval" value="10" min="1" max="300">
      <div class="form-hint">How often the ESP32 reads sensor data.</div>
    </div>

    <div class="form-group">
      <label class="form-label">DATA RETENTION <span>days</span></label>
      <input class="form-input" type="number" name="retention" value="90" min="7" max="365">
      <div class="form-hint">How long to keep logs in the MySQL database.</div>
    </div>

    <div class="form-group">
      <label class="form-label">TIMEZONE</label>
      <select class="form-select" name="timezone">
        <option value="Asia/Manila" selected>Asia/Manila (UTC+8)</option>
        <option value="UTC">UTC</option>
        <option value="America/New_York">America/New_York (UTC-5)</option>
      </select>
    </div>
  </div>

  <!-- Notification Settings -->
  <div class="card">
    <div class="card-title">🔔 NOTIFICATIONS</div>

    <?php
    $notif_items = [
      ['key'=>'notif_dashboard','label'=>'Dashboard Alerts',     'desc'=>'Show alert banner on web dashboard', 'default'=>true],
      ['key'=>'notif_email',    'label'=>'Email Notifications',  'desc'=>'Send email on danger-level events',  'default'=>false],
      ['key'=>'notif_sound',    'label'=>'Sound Alerts',         'desc'=>'Play audio on critical events',      'default'=>true],
      ['key'=>'notif_sensor',   'label'=>'Sensor Failure Alerts','desc'=>'Alert if a sensor stops responding', 'default'=>true],
    ];
    foreach($notif_items as $n):
      $current = function_exists('get_setting') ? get_setting($n['key'], $n['default'] ? '1' : '0') : ($n['default'] ? '1' : '0');
      $checked = $current === '1' ? 'checked' : '';
    ?>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--border)">
      <div>
        <div style="font-family:var(--head);font-weight:700;font-size:13px;letter-spacing:1px;color:var(--bright)"><?= $n['label'] ?></div>
        <div style="font-family:var(--mono);font-size:10px;color:var(--dim);margin-top:2px"><?= $n['desc'] ?></div>
      </div>
      <label class="toggle-switch">
        <input type="checkbox" name="<?= $n['key'] ?>" <?= $checked ?> >
        <div class="toggle-track"></div><div class="toggle-thumb"></div>
      </label>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Hardware Settings -->
  <div class="card">
    <div class="card-title">🔌 HARDWARE</div>

    <div class="form-group">
      <label class="form-label">ESP32 IP ADDRESS</label>
      <input class="form-input" type="text" name="esp32_ip" value="192.168.1.100" placeholder="e.g. 192.168.1.100">
      <div class="form-hint">Local IP address of the ESP32 module on your network.</div>
    </div>

    <div class="form-group">
      <label class="form-label">RELAY MODULE CONFIG</label>
      <select class="form-select" name="relay_config">
        <option value="4ch" selected>4-Channel Relay Board</option>
        <option value="2ch">2-Channel Relay Board</option>
        <option value="8ch">8-Channel Relay Board</option>
      </select>
    </div>

    <div class="form-group">
      <label class="form-label">FAN RELAY PIN</label>
      <input class="form-input" type="number" name="fan_pin" value="5" min="0" max="39">
    </div>

    <div class="form-group">
      <label class="form-label">MISTER RELAY PIN</label>
      <input class="form-input" type="number" name="mister_pin" value="18" min="0" max="39">
    </div>

    <div style="padding:12px;background:rgba(255,77,77,.06);border:1px solid rgba(255,77,77,.2);border-radius:8px;margin-top:4px">
      <div style="font-family:var(--head);font-weight:700;font-size:12px;letter-spacing:1px;color:var(--red);margin-bottom:4px">⚠ WARNING</div>
      <div style="font-family:var(--mono);font-size:10px;color:var(--dim);line-height:1.6">
        Changing relay pin assignments can cause unexpected hardware behavior. Only modify if you know your wiring configuration.
      </div>
    </div>
  </div>

</div>

<!-- Save Button -->
<div style="display:flex;justify-content:flex-end;gap:10px" class="fade-up fade-up-3">
  <button type="reset" class="btn btn-ghost">↺ Reset Defaults</button>
  <button type="submit" class="btn btn-success">✓ Save Settings</button>
</div>

</form>

<?php require_once 'partials/footer.php'; ?>
