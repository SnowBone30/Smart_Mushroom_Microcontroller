<?php
// ── SHARED HEADER — included on every page ─────────────────────────────────
// Expects: $page_title (string), $current_page (string matching nav href)

if (!isset($page_title))   $page_title   = SITE_TITLE;
if (!isset($current_page)) $current_page = 'index.php';

// ── SESSION & AUTHENTICATION ──────────────────────────────────────────────
session_start();
require_once __DIR__ . '/../db.php';

// Require login for all pages except login.php
if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
    require_login();
}

// Handle logout
if (isset($_GET['logout'])) {
    logout();
}

// ── TIMEZONE (from DB setting) ─────────────────────────────────────────────
if (function_exists('get_setting')) {
    date_default_timezone_set(get_setting('timezone', 'Asia/Manila'));
} else {
    date_default_timezone_set('Asia/Manila');
}

// ── ALERT COUNT ────────────────────────────────────────────────────────────
$alert_count = $sensor_data['alert_count'] 
    ?? (function_exists('get_unread_alert_count') ? get_unread_alert_count() : 0);

// ── CURRENT USER ───────────────────────────────────────────────────────────
$current_user = get_session_user();

// ── LAST UPDATED FORMAT ────────────────────────────────────────────────────
$last_raw = $sensor_data['last_updated'] ?? null;

$last_display = 'No data';
if ($last_raw) {
    $t = strtotime($last_raw);
    if ($t) {
        $last_display = date('M j, Y g:i:s A', $t);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title) ?> — Mushroom Farm</title>

<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Share+Tech+Mono&family=Exo+2:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>

<body>

<header>
  <div class="header-left">
    <a href="index.php" class="logo-box">
      <div class="logo-icon">🍄</div>
      <div class="logo-text">
        <span>MUSHROOM</span>
        <span>FARM</span>
      </div>
    </a>

    <div class="divider"></div>

    <div class="title-block">
      SMART MUSHROOM FARMING
      <small>MICROCLIMATE CONTROLLER</small>
    </div>
  </div>

  <div class="header-right">
    <div class="alert-btn">
      <span>🔔</span> ALERTS
      <span class="alert-badge"><?= htmlspecialchars((string)$alert_count) ?></span>
    </div>

    <div class="hbtn" onclick="showUserMenu()" style="cursor:pointer;">
      <span>👤</span> <?= htmlspecialchars(strtoupper($current_user['username'] ?? 'USER')) ?>
    </div>
  </div>
</header>

<!-- USER MENU (hidden by default) -->
<div id="user-menu" style="display:none; position:absolute; top:64px; right:10px; background:var(--bg-card); border:1px solid var(--border); border-radius:8px; padding:10px; z-index:300; min-width:150px;">
  <div style="font-family:var(--head); font-weight:700; font-size:12px; color:var(--bright); margin-bottom:8px; text-align:center;">
    <?= htmlspecialchars($current_user['username'] ?? 'USER') ?>
  </div>
  <div style="font-family:var(--mono); font-size:10px; color:var(--dim); margin-bottom:12px; text-align:center;">
    Role: <?= htmlspecialchars($current_user['role'] ?? 'unknown') ?>
  </div>
  <a href="?logout=1" style="display:block; padding:8px 12px; color:var(--text); text-decoration:none; font-family:var(--mono); font-size:12px; border-radius:4px; transition:background 0.2s;" onmouseover="this.style.background='var(--bg-card2)'" onmouseout="this.style.background='transparent'">
    🚪 Logout
  </a>
</div>

<!-- STATUS BAR -->
<div class="status-bar">
  <span>
    SYSTEM STATUS:
    <span class="online">● <?= htmlspecialchars($sensor_data['system_status'] ?? 'UNKNOWN') ?></span>
  </span>

  <span id="live-clock"></span>

  <span><?= VERSION ?></span>
</div>

<div class="layout-shell">

  <!-- SIDEBAR -->
  <nav class="sidebar">
    <?php foreach ($nav_items as $item): ?>
      <a href="<?= $item['href'] ?>"
         class="nav-item <?= ($current_page === $item['href']) ? 'active' : '' ?>">
        <span class="nav-icon"><?= $item['icon'] ?></span>
        <span class="nav-label"><?= $item['label'] ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <!-- MAIN CONTENT -->
  <main class="page-content">

<!-- ── LIVE CLOCK SCRIPT ───────────────────────────────────────────── -->
<script>
function updateClock() {
    const now = new Date();

    const options = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        second: '2-digit'
    };

    document.getElementById('live-clock').innerText =
        'CURRENT TIME: ' + now.toLocaleString('en-US', options);
}

// update every second
setInterval(updateClock, 1000);
updateClock();

// ── USER MENU ───────────────────────────────────────────────────────
function showUserMenu() {
    const menu = document.getElementById('user-menu');
    const isVisible = menu.style.display === 'block';
    menu.style.display = isVisible ? 'none' : 'block';
}

// Hide user menu when clicking elsewhere
document.addEventListener('click', function(e) {
    const menu = document.getElementById('user-menu');
    const userBtn = e.target.closest('.hbtn');
    if (!userBtn && menu.style.display === 'block') {
        menu.style.display = 'none';
    }
});
</script>