<?php
// ── PITAHAYA FARM — SHARED CONFIG ─────────────────────────────────────────────

define('SITE_NAME',  'MUSHROOM FARM');
define('SITE_TITLE', 'Smart Mushroom Farming Microclimate Controller');
define('VERSION',    'v2.1.0');

require_once __DIR__ . '/db.php';

// ── SENSOR SUMMARY ────────────────────────────────────────────────────────────
$sensor_data = [
    'temperature'      => null,
    'humidity'         => null,
    'co2'              => null,
    'temp_status'      => '',
    'humidity_status'  => '',
    'co2_status'       => '',
    'system_status'    => 'UNKNOWN',
    'last_updated'     => null,
    'fans_active'      => false,
    'misters_active'   => false,
    'alert_count'      => function_exists('get_unread_alert_count') ? get_unread_alert_count() : 0,
];

if (function_exists('get_latest_readings')) {
    $rows      = get_latest_readings();
    $latest_ts = 0;

    foreach ($rows as $r) {
        $stype = strtolower($r['sensor_type'] ?? '');
        $val   = isset($r['value']) ? (float)$r['value'] : null;
        $rec   = isset($r['recorded_at']) ? strtotime($r['recorded_at']) : 0;
        if ($rec > $latest_ts) $latest_ts = $rec;

        if ($stype === 'temperature') {
            $sensor_data['temperature'] = $val;
        } elseif ($stype === 'humidity') {
            $sensor_data['humidity'] = $val;
        } elseif ($stype === 'co2') {
            $sensor_data['co2'] = $val;
        }
    }

    if ($latest_ts) {
        $sensor_data['last_updated'] = date('Y-m-d H:i:s', $latest_ts);
    }

    // ── THRESHOLDS ────────────────────────────────────────────────────────────
    // Pull from the `thresholds` table (global rows where zone_id IS NULL).
    // Falls back to sensible hard-coded defaults if the table is empty.
    // FIX: original code used get_setting() keys (temp_min, co2_max, etc.)
    //      that do not exist in system_settings — those thresholds live in
    //      the `thresholds` table instead.
    $tmin     = 18;   $tmax     = 28;
    $hmin     = 75;   $hmax     = 90;
    $cmax     = 600;  $cdanger  = 800;

    if (function_exists('db_all')) {
        try {
            $thresh_rows = db_all(
                "SELECT sensor_type, min_value, max_value, danger_value
                 FROM thresholds
                 WHERE zone_id IS NULL"
            );
            foreach ($thresh_rows as $tr) {
                switch ($tr['sensor_type']) {
                    case 'temperature':
                        $tmin = (float)$tr['min_value'];
                        $tmax = (float)$tr['max_value'];
                        break;
                    case 'humidity':
                        $hmin = (float)$tr['min_value'];
                        $hmax = (float)$tr['max_value'];
                        break;
                    case 'co2':
                        $cmax    = (float)$tr['max_value'];
                        $cdanger = (float)$tr['danger_value'];
                        break;
                }
            }
        } catch (Throwable $e) {
            // keep hard-coded defaults
        }
    }

    if ($sensor_data['temperature'] !== null) {
        $sensor_data['temp_status'] = ($sensor_data['temperature'] > $tmax) ? 'High' : 'Optimal';
    }
    if ($sensor_data['humidity'] !== null) {
        $sensor_data['humidity_status'] = ($sensor_data['humidity'] < $hmin) ? 'Low' : 'Optimal';
    }
    if ($sensor_data['co2'] !== null) {
        $sensor_data['co2_status'] = ($sensor_data['co2'] >= $cdanger)
            ? 'Danger'
            : (($sensor_data['co2'] >= $cmax) ? 'High' : 'Normal');
    }

    // ── RELAY STATES ──────────────────────────────────────────────────────────
    // relay_devices.name uses "Ventilation Fans" / "Water Misters" —
    // LIKE '%fan%' and '%mister%' match correctly.
    if (function_exists('db_scalar')) {
        try {
            $fans_on    = (int) db_scalar("SELECT COUNT(*) FROM relay_devices WHERE LOWER(name) LIKE '%fan%'    AND is_on = 1");
            $misters_on = (int) db_scalar("SELECT COUNT(*) FROM relay_devices WHERE LOWER(name) LIKE '%mister%' AND is_on = 1");
            $sensor_data['fans_active']    = $fans_on    > 0;
            $sensor_data['misters_active'] = $misters_on > 0;
        } catch (Throwable $e) {
            // ignore
        }
    }

    $sensor_data['system_status'] = 'ONLINE';
}

// ── RECENT ALERTS ─────────────────────────────────────────────────────────────
$alerts = function_exists('get_unread_alerts') ? get_unread_alerts(5) : [];

// ── NAV ITEMS ─────────────────────────────────────────────────────────────────
$nav_items = [
    ['href' => 'index.php',       'icon' => '📊', 'label' => 'DASHBOARD'],
    ['href' => 'sensors.php',     'icon' => '📡', 'label' => 'SENSORS'],
    ['href' => 'logs.php',        'icon' => '📋', 'label' => 'LOGS'],
    ['href' => 'settings.php',    'icon' => '⚙️',  'label' => 'SETTINGS'],
    ['href' => 'reports.php',     'icon' => '📄', 'label' => 'REPORTS'],
    ['href' => 'system-flow.php', 'icon' => '🔄', 'label' => 'SYSTEM FLOW'],
    ['href' => 'logout.php', 'icon' => '➡️', 'label' => 'LOGOUT'],
];