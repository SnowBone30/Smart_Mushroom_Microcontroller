<?php
require_once 'config.php';
$page_title   = 'Reports';
$current_page = 'reports.php';
require_once 'partials/header.php';

// ── FETCH SAVED REPORTS ───────────────────────────────────────────────────────
// FIX: original query used non-existent columns (period, size, status).
//      Corrected to match actual `reports` table columns:
//      report_code, title, date_from, date_to, type, format, file_size.
$reports = [];
try {
    $reports = db_all("
        SELECT
            report_code                                        AS id,
            title,
            CONCAT(
                DATE_FORMAT(date_from, '%b %e, %Y'),
                ' – ',
                DATE_FORMAT(date_to,   '%b %e, %Y')
            )                                                  AS date,
            type,
            UPPER(format)                                      AS format_label,
            CASE
                WHEN file_size IS NULL       THEN 'N/A'
                WHEN file_size < 1048576     THEN CONCAT(ROUND(file_size / 1024),       ' KB')
                ELSE                              CONCAT(ROUND(file_size / 1048576, 1), ' MB')
            END                                                AS size,
            'ready'                                            AS status,
            file_path,
            created_at
        FROM reports
        ORDER BY created_at DESC
        LIMIT 100
    ");
} catch (Throwable $e) {
    // Table not yet created or query failed — fall back to empty list
    $reports = [];
}

// If DB returned nothing, try scanning a local reports directory
if (empty($reports)) {
    $repdir = __DIR__ . '/reports';
    if (is_dir($repdir)) {
        foreach (scandir($repdir, SCANDIR_SORT_DESCENDING) as $f) {
            $path = $repdir . DIRECTORY_SEPARATOR . $f;
            if (is_file($path)) {
                $size_kb = round(filesize($path) / 1024);
                $reports[] = [
                    'id'           => strtoupper(pathinfo($f, PATHINFO_FILENAME)),
                    'title'        => $f,
                    'date'         => date('M j, Y', filemtime($path)),
                    'type'         => strtoupper(pathinfo($f, PATHINFO_EXTENSION)),
                    'format_label' => strtoupper(pathinfo($f, PATHINFO_EXTENSION)),
                    'size'         => $size_kb . ' KB',
                    'status'       => 'ready',
                    'file_path'    => 'reports/' . $f,
                ];
            }
        }
    }
}

// ── SUMMARY STATS ─────────────────────────────────────────────────────────────
$total_reports = count($reports);

$total_size_kb = 0;
foreach ($reports as $r) {
    if (isset($r['size']) && $r['size'] !== 'N/A') {
        // Strip ' KB' / ' MB' and convert everything to KB
        if (str_contains($r['size'], 'MB')) {
            $total_size_kb += (float) $r['size'] * 1024;
        } else {
            $total_size_kb += (float) $r['size'];
        }
    }
}
$total_size_display = $total_size_kb > 1024
    ? round($total_size_kb / 1024, 1) . ' MB'
    : round($total_size_kb) . ' KB';

// FIX: 'next_auto_report' key is now seeded in system_settings via settings_seed.sql
$next_auto = function_exists('get_setting') ? get_setting('next_auto_report', 'Sun 11:59 PM') : 'Sun 11:59 PM';

// ── WEEKLY CHART DATA ─────────────────────────────────────────────────────────
// Uses sensor_readings JOIN sensors — both tables exist in schema.
$chart_days = json_encode([]);
$chart_temp = json_encode([]);
$chart_co2  = json_encode([]);

try {
    $rows = db_all("
        SELECT
            DATE(sr.recorded_at)  AS day,
            s.type                AS sensor_type,
            AVG(sr.value)         AS avgv
        FROM sensor_readings sr
        JOIN sensors s ON s.id = sr.sensor_id
        WHERE sr.recorded_at >= CURDATE() - INTERVAL 7 DAY
        GROUP BY day, s.type
        ORDER BY day ASC
    ");

    if ($rows) {
        $days      = [];
        $temp_data = [];
        $co2_data  = [];

        foreach ($rows as $r) {
            $day   = date('M j', strtotime($r['day']));
            $stype = strtolower($r['sensor_type'] ?? '');

            if (!in_array($day, $days)) $days[] = $day;

            // FIX: schema uses enum 'temperature'/'humidity'/'co2' — match exactly
            if ($stype === 'temperature') $temp_data[$day] = round((float)$r['avgv'], 2);
            if ($stype === 'co2')         $co2_data[$day]  = round((float)$r['avgv'], 2);
        }

        $chart_days = json_encode($days);
        $chart_temp = json_encode(array_values($temp_data));
        $chart_co2  = json_encode(array_values($co2_data));
    }
} catch (Throwable $e) {
    // keep empty arrays
}

$week_start   = date('M j', strtotime('monday this week'));
$week_end     = date('M j', strtotime('sunday this week'));
$chart_period = "({$week_start}–{$week_end})";
?>

<div class="page-heading fade-up">
  <h1>REPORTS <small>DATA ARCHIVING & EXPORT</small></h1>
  <button class="btn btn-primary" onclick="document.getElementById('genModal').style.display='flex'">+ Generate Report</button>
</div>

<!-- Summary stats -->
<div class="grid-3 fade-up fade-up-1">
  <div class="stat-box" style="--sb-color:var(--blue)">
    <div class="stat-label">TOTAL REPORTS</div>
    <div class="stat-value"><?= $total_reports ?></div>
    <div class="stat-sub">All time</div>
  </div>
  <div class="stat-box" style="--sb-color:var(--green)">
    <div class="stat-label">DATA ARCHIVED</div>
    <div class="stat-value"><?= $total_size_display ?><span class="stat-unit"></span></div>
    <div class="stat-sub">Across all reports</div>
  </div>
  <div class="stat-box" style="--sb-color:var(--yellow)">
    <div class="stat-label">NEXT AUTO-REPORT</div>
    <div class="stat-value"><?= htmlspecialchars($next_auto) ?><span class="stat-unit"></span></div>
    <div class="stat-sub">Weekly summary</div>
  </div>
</div>

<!-- Charts -->
<div class="grid-2 fade-up fade-up-2">
  <div class="card">
    <div class="card-title">WEEKLY TEMPERATURE AVERAGE <span class="ct-accent">(<?= htmlspecialchars($chart_period) ?>)</span></div>
    <div class="chart-container" style="height:160px"><canvas id="rptTemp"></canvas></div>
  </div>
  <div class="card">
    <div class="card-title">WEEKLY CO₂ AVERAGE <span class="ct-accent">(<?= htmlspecialchars($chart_period) ?>)</span></div>
    <div class="chart-container" style="height:160px"><canvas id="rptCo2" data-warning-line="600"></canvas></div>
  </div>
</div>

<!-- Reports table -->
<div class="card fade-up fade-up-3">
  <div class="card-title">SAVED REPORTS</div>
  <table class="data-table">
    <thead>
      <tr>
        <th>REPORT ID</th>
        <th>TITLE</th>
        <th>PERIOD</th>
        <th>TYPE</th>
        <th>SIZE</th>
        <th>STATUS</th>
        <th>ACTION</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($reports)): ?>
      <tr>
        <td colspan="7" style="text-align:center;color:var(--dim)">No reports found.</td>
      </tr>
      <?php else: ?>
      <?php foreach ($reports as $r): ?>
      <tr>
        <td style="color:var(--dim)"><?= htmlspecialchars($r['id']) ?></td>
        <td style="color:var(--bright);font-family:var(--head);letter-spacing:1px"><?= htmlspecialchars($r['title']) ?></td>
        <td><?= htmlspecialchars($r['date']) ?></td>
        <td><span class="badge badge-blue"><?= htmlspecialchars(strtoupper($r['format_label'] ?? $r['type'])) ?></span></td>
        <td><?= htmlspecialchars($r['size']) ?></td>
        <td><span class="badge badge-green">✓ READY</span></td>
        <td>
          <?php if (!empty($r['file_path'])): ?>
            <a href="<?= htmlspecialchars($r['file_path']) ?>" class="btn btn-ghost" style="padding:4px 12px;font-size:11px" download>⬇ Download</a>
          <?php else: ?>
            <button class="btn btn-ghost" style="padding:4px 12px;font-size:11px" disabled>⬇ Download</button>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Generate Report Modal -->
<div id="genModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:300;align-items:center;justify-content:center">
  <div class="card" style="width:460px;max-width:95vw">
    <div class="card-title">📄 GENERATE NEW REPORT</div>
    <div class="form-group">
      <label class="form-label">REPORT TYPE</label>
      <select class="form-select">
        <option value="weekly">Weekly Summary</option>
        <option value="monthly">Monthly Summary</option>
        <option value="custom">Custom Range</option>
        <option value="event">Event Analysis</option>
        <option value="uptime">Sensor Uptime</option>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">DATE RANGE</label>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <input class="form-input" type="date" value="<?= date('Y-m-d', strtotime('-7 days')) ?>">
        <input class="form-input" type="date" value="<?= date('Y-m-d') ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">FORMAT</label>
      <select class="form-select">
        <option value="pdf">PDF</option>
        <option value="csv">CSV</option>
        <option value="xlsx">Excel (.xlsx)</option>
      </select>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:8px">
      <button class="btn btn-ghost" onclick="document.getElementById('genModal').style.display='none'">Cancel</button>
      <button class="btn btn-primary">Generate →</button>
    </div>
  </div>
</div>

<script>
const days = <?= $chart_days ?>;

const c1 = document.getElementById('rptTemp').getContext('2d');
new Chart(c1, {
  type: 'bar',
  data: {
    labels: days,
    datasets: [{
      data: <?= $chart_temp ?>,
      backgroundColor: 'rgba(59,158,255,0.5)',
      borderColor: '#3b9eff',
      borderWidth: 1,
      borderRadius: 4
    }]
  },
  options: {
    ...CHART_DEFAULTS,
    scales: { ...CHART_DEFAULTS.scales, y: { ...CHART_DEFAULTS.scales.y, min: 18, max: 28 } }
  }
});

const c2 = document.getElementById('rptCo2').getContext('2d');
new Chart(c2, {
  type: 'bar',
  data: {
    labels: days,
    datasets: [{
      data: <?= $chart_co2 ?>,
      backgroundColor: 'rgba(45,220,110,0.4)',
      borderColor: '#2ddc6e',
      borderWidth: 1,
      borderRadius: 4
    }]
  },
  options: {
    ...CHART_DEFAULTS,
    scales: { ...CHART_DEFAULTS.scales, y: { ...CHART_DEFAULTS.scales.y, min: 0, max: 800 } }
  }
});
</script>

<?php require_once 'partials/footer.php'; ?>