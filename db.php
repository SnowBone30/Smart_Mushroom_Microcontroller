<?php
// ══════════════════════════════════════════════════════════════════════════════
// MUSHROOM FARM — DATABASE CONNECTION
// File: db.php
// ══════════════════════════════════════════════════════════════════════════════

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'mushroom_farm_db');
define('DB_USER', 'root');          // change to a dedicated DB user
define('DB_PASS', '');              // set your password here

// ── PDO CONNECTION ────────────────────────────────────────────────────────────
try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        DB_HOST, DB_PORT, DB_NAME
    );
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    error_log('[DB ERROR] ' . $e->getMessage());
    http_response_code(503);
    die(json_encode(['error' => 'Database unavailable. Please try again later.']));
}

// ══════════════════════════════════════════════════════════════════════════════
// QUERY HELPERS
// ══════════════════════════════════════════════════════════════════════════════

function db_row(string $sql, array $params = []): ?array {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row ?: null;
}

function db_all(string $sql, array $params = []): array {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function db_scalar(string $sql, array $params = []): mixed {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_NUM);
    return $row ? $row[0] : null;
}

function db_exec(string $sql, array $params = []): int {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

function db_insert(string $sql, array $params = []): string {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $pdo->lastInsertId();
}

// ══════════════════════════════════════════════════════════════════════════════
// DOMAIN QUERIES
// ══════════════════════════════════════════════════════════════════════════════

/**
 * Returns live readings for all sensors (from v_latest_readings view).
 */
function get_latest_readings(): array {
    return db_all('SELECT * FROM v_latest_readings ORDER BY zone, sensor_type');
}

/**
 * Returns sensor readings for one sensor over the last N hours.
 */
function get_readings_history(string $sensor_id, int $hours = 12): array {
    return db_all(
        'SELECT value, recorded_at
         FROM sensor_readings
         WHERE sensor_id = ? AND recorded_at >= NOW() - INTERVAL ? HOUR
         ORDER BY recorded_at ASC',
        [$sensor_id, $hours]
    );
}

/**
 * Returns the latest reading value for a given sensor.
 */
function get_latest_value(string $sensor_id): ?float {
    $val = db_scalar(
        'SELECT value FROM sensor_readings
         WHERE sensor_id = ?
         ORDER BY recorded_at DESC LIMIT 1',
        [$sensor_id]
    );
    return $val !== null ? (float)$val : null;
}

/**
 * Returns thresholds for a zone (falls back to global defaults if zone has none).
 */
function get_thresholds(int $zone_id): array {
    $rows = db_all(
        'SELECT sensor_type, min_value, max_value, danger_value
         FROM thresholds
         WHERE zone_id = ? OR zone_id IS NULL
         ORDER BY zone_id DESC',
        [$zone_id]
    );
    $result = [];
    foreach ($rows as $row) {
        if (!isset($result[$row['sensor_type']])) {
            $result[$row['sensor_type']] = $row;
        }
    }
    return $result;
}

/**
 * Returns all event logs with optional severity filter.
 */
function get_event_logs(?string $severity = null, int $limit = 100): array {
    if ($severity) {
        return db_all(
            'SELECT l.*, z.name AS zone_name
             FROM event_logs l
             LEFT JOIN zones z ON z.id = l.zone_id
             WHERE l.severity = ?
             ORDER BY l.created_at DESC LIMIT ?',
            [$severity, $limit]
        );
    }
    return db_all(
        'SELECT l.*, z.name AS zone_name
         FROM event_logs l
         LEFT JOIN zones z ON z.id = l.zone_id
         ORDER BY l.created_at DESC LIMIT ?',
        [$limit]
    );
}

/**
 * Returns unread alerts, newest first.
 */
function get_unread_alerts(int $limit = 10): array {
    return db_all(
        'SELECT a.*, z.name AS zone_name
         FROM alerts a
         LEFT JOIN zones z ON z.id = a.zone_id
         WHERE a.is_read = 0
         ORDER BY a.created_at DESC LIMIT ?',
        [$limit]
    );
}

/**
 * Returns count of unread alerts.
 */
function get_unread_alert_count(): int {
    return (int) db_scalar('SELECT unread_count FROM v_unread_alerts');
}

/**
 * Marks all alerts as read.
 */
function mark_alerts_read(): void {
    db_exec('UPDATE alerts SET is_read = 1 WHERE is_read = 0');
}

/**
 * Returns a system setting value by key.
 */
function get_setting(string $key, string $default = ''): string {
    $val = db_scalar(
        'SELECT setting_value FROM system_settings WHERE setting_key = ?', [$key]
    );
    return $val ?? $default;
}

/**
 * Saves a system setting.
 */
function save_setting(string $key, string $value): void {
    db_exec(
        'INSERT INTO system_settings (setting_key, setting_value)
         VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
        [$key, $value]
    );
}

/**
 * Toggles a relay device ON or OFF and writes an event log entry.
 */
function set_relay(int $relay_id, bool $on, string $trigger_desc = 'Manual'): void {
    $state = $on ? 1 : 0;
    $label = $on ? 'ON' : 'OFF';

    db_exec(
        'UPDATE relay_devices SET is_on = ? WHERE id = ?',
        [$state, $relay_id]
    );

    $relay = db_row('SELECT * FROM relay_devices WHERE id = ?', [$relay_id]);
    if ($relay) {
        $code = 'LOG-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        db_insert(
            'INSERT INTO event_logs
               (log_code, zone_id, event_name, trigger_desc, action_taken, severity)
             VALUES (?, ?, ?, ?, ?, ?)',
            [
                $code,
                $relay['zone_id'],
                strtoupper($relay['name']) . ' ' . $label,
                $trigger_desc,
                'Relay ' . $relay_id . ' ' . $label,
                'info',
            ]
        );
    }
}

/**
 * Inserts a new sensor reading (called by ESP32 API endpoint).
 */
function log_reading(string $sensor_id, float $value): void {
    db_insert(
        'INSERT INTO sensor_readings (sensor_id, value) VALUES (?, ?)',
        [$sensor_id, $value]
    );
    db_exec(
        'UPDATE sensors SET last_seen = NOW() WHERE id = ?',
        [$sensor_id]
    );
}

/**
 * Determines status label from a value + threshold row.
 * Returns: 'optimal' | 'normal' | 'warning' | 'danger'
 */
function reading_status(float $value, array $threshold): string {
    if ($threshold['danger_value'] !== null && $value >= (float)$threshold['danger_value']) {
        return 'danger';
    }
    if ($threshold['max_value'] !== null && $value >= (float)$threshold['max_value']) {
        return 'warning';
    }
    if ($threshold['min_value'] !== null && $value <= (float)$threshold['min_value']) {
        return 'warning';
    }
    return 'optimal';
}

/**
 * Authenticates a user with username and password.
 * Returns user array on success, null on failure.
 */
function authenticate_user(string $username, string $password): ?array {
    $user = db_row(
        'SELECT id, username, email, role, is_active, created_at, last_login
         FROM users
         WHERE username = ? AND is_active = 1',
        [$username]
    );

    if (!$user) {
        return null;
    }

    // Verify password hash
    if (!password_verify($password, db_scalar('SELECT password_hash FROM users WHERE id = ?', [$user['id']]))) {
        return null;
    }

    // Update last login
    db_exec('UPDATE users SET last_login = NOW() WHERE id = ?', [$user['id']]);

    return $user;
}

/**
 * Gets current logged-in user from session.
 * Returns user array or null if not logged in.
 */
function get_session_user(): ?array {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    return db_row(
        'SELECT id, username, email, role, is_active, created_at, last_login
         FROM users
         WHERE id = ? AND is_active = 1',
        [$_SESSION['user_id']]
    );
}

/**
 * Requires user to be logged in, redirects to login page if not.
 */
function require_login(): void {
    if (!get_session_user()) {
        $requested = $_SERVER['REQUEST_URI'] ?? 'index.php';
        $redirect  = urlencode($requested);
        header('Location: login.php?redirect=' . $redirect);
        exit;
    }
}

/**
 * Logs out the current user.
 */
function logout(): void {
    session_destroy();
    header('Location: login.php');
    exit;
}