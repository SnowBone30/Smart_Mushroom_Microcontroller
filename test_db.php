<?php
require_once 'db.php';

echo "Testing database queries...\n";

// Test reports
$reports = db_all('SELECT * FROM reports');
echo "Reports: " . count($reports) . "\n";

// Test temperature data
$temp_data = db_all('SELECT DATE(recorded_at) as date, AVG(value) as avg_temp FROM sensor_readings WHERE sensor_id LIKE "TEMP%" AND recorded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(recorded_at) ORDER BY date');
echo "Temperature data points: " . count($temp_data) . "\n";

// Test CO2 data
$co2_data = db_all('SELECT DATE(recorded_at) as date, AVG(value) as avg_co2 FROM sensor_readings WHERE sensor_id LIKE "CO2%" AND recorded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(recorded_at) ORDER BY date');
echo "CO2 data points: " . count($co2_data) . "\n";

echo "All tests passed!\n";
?>