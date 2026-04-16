<?php
require_once 'config.php';
$page_title   = 'Logs';
$current_page = 'logs.php';
require_once 'partials/header.php';

// Fetch logs from DB (uses helpers in db.php)
$logs = function_exists('get_event_logs') ? get_event_logs(null, 200) : [];
$type_map = ['warning'=>['badge-yellow','⚠'],'info'=>['badge-blue','ℹ'],'danger'=>['badge-red','⛔']];

// Summary stats (counts for today)
$total_events_today = function_exists('db_scalar') ? (int) db_scalar("SELECT COUNT(*) FROM event_logs WHERE DATE(created_at) = CURDATE()") : 0;
$warnings_today = function_exists('db_scalar') ? (int) db_scalar("SELECT COUNT(*) FROM event_logs WHERE DATE(created_at) = CURDATE() AND severity = 'warning'") : 0;
$critical_alerts = function_exists('db_scalar') ? (int) db_scalar("SELECT COUNT(*) FROM event_logs WHERE DATE(created_at) = CURDATE() AND severity = 'danger'") : 0;
?>

<div class="page-heading fade-up">
  <h1>LOGS <small>EVENT & ACTION HISTORY</small></h1>
  <div style="display:flex;gap:8px">
    <button class="btn btn-ghost" onclick="filterLogs('all')">All</button>
    <button class="btn btn-ghost" onclick="filterLogs('warning')">⚠ Warning</button>
    <button class="btn btn-ghost" onclick="filterLogs('danger')">⛔ Danger</button>
    <a href="reports.php" class="btn btn-primary">📄 Export Report</a>
  </div>
</div>

<!-- Summary stats -->
<div class="grid-3 fade-up fade-up-1">
  <div class="stat-box" style="--sb-color:var(--blue)">
    <div class="stat-label">TOTAL EVENTS TODAY</div>
    <div class="stat-value"><?= $total_events_today ?></div>
    <div class="stat-sub">Since midnight</div>
  </div>
  <div class="stat-box" style="--sb-color:var(--yellow)">
    <div class="stat-label">WARNINGS TODAY</div>
    <div class="stat-value"><?= $warnings_today ?></div>
    <div class="stat-sub">CO₂ & humidity triggers</div>
  </div>
  <div class="stat-box" style="--sb-color:var(--red)">
    <div class="stat-label">CRITICAL ALERTS</div>
    <div class="stat-value"><?= $critical_alerts ?></div>
    <div class="stat-sub">Since midnight</div>
  </div>
</div>

<!-- Log table -->
<div class="card fade-up fade-up-2">
  <div class="card-title">EVENT LOG <span class="ct-accent">— <?= count($logs) ?> ENTRIES</span></div>
  <table class="data-table" id="logTable">
    <thead>
      <tr>
        <th>LOG ID</th>
        <th>DATE / TIME</th>
        <th>EVENT</th>
        <th>TRIGGER</th>
        <th>ACTION TAKEN</th>
        <th>ZONE</th>
        <th>TYPE</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($logs as $log):
        $severity = $log['severity'] ?? $log['type'] ?? 'info';
        [$bdg,$ico] = $type_map[$severity] ?? $type_map['info'];
        $log_code = $log['log_code'] ?? $log['id'] ?? '';
        $created = $log['created_at'] ?? ($log['date'] . ' ' . ($log['time'] ?? ''));
        $event_name = $log['event_name'] ?? $log['event'] ?? '';
        $trigger = $log['trigger_desc'] ?? $log['trigger'] ?? '';
        $action = $log['action_taken'] ?? $log['action'] ?? '';
        $zone = $log['zone_name'] ?? $log['zone'] ?? '';
      ?>
      <tr data-type="<?= htmlspecialchars($severity) ?>">
        <td style="font-family:var(--mono);color:var(--dim)"><?= htmlspecialchars($log_code) ?></td>
        <td><?= htmlspecialchars($created) ?></td>
        <td style="color:var(--bright);font-family:var(--head);letter-spacing:1px"><?= htmlspecialchars($event_name) ?></td>
        <td><?= htmlspecialchars($trigger) ?></td>
        <td><?= htmlspecialchars($action) ?></td>
        <td><span class="badge badge-dim"><?= htmlspecialchars($zone) ?></span></td>
        <td><span class="badge <?= $bdg ?>"><?= $ico ?> <?= strtoupper(htmlspecialchars($severity)) ?></span></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
function filterLogs(type) {
  document.querySelectorAll('#logTable tbody tr').forEach(row => {
    row.style.display = (type === 'all' || row.dataset.type === type) ? '' : 'none';
  });
}
</script>

<?php require_once 'partials/footer.php'; ?>
