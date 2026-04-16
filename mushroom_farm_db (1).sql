-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 15, 2026 at 05:09 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mushroom_farm_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int(10) UNSIGNED NOT NULL,
  `zone_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `type` enum('info','warning','danger') NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `alerts`
--

INSERT INTO `alerts` (`id`, `zone_id`, `title`, `description`, `type`, `is_read`, `created_at`) VALUES
(1, 1, 'FANS ACTIVATED', 'High CO2 detected 600 PPM', 'warning', 0, '2026-04-13 09:53:54'),
(2, 1, 'MISTERS ACTIVATED', 'Humidity dropped to 81%', 'info', 1, '2026-04-13 09:08:54'),
(3, 2, 'CO2 SPIKE', 'CO2 reached 650 PPM', 'danger', 1, '2026-04-13 08:23:54');

-- --------------------------------------------------------

--
-- Table structure for table `event_logs`
--

CREATE TABLE `event_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `log_code` varchar(12) NOT NULL,
  `zone_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `event_name` varchar(100) NOT NULL,
  `trigger_desc` varchar(255) DEFAULT NULL,
  `action_taken` varchar(255) DEFAULT NULL,
  `severity` enum('info','warning','danger') NOT NULL DEFAULT 'info',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_logs`
--

INSERT INTO `event_logs` (`id`, `log_code`, `zone_id`, `event_name`, `trigger_desc`, `action_taken`, `severity`, `created_at`) VALUES
(1, 'LOG-0412', 1, 'FANS ACTIVATED', 'CO2 > 600 PPM', 'Relay 1 ON', 'warning', '2026-04-13 09:53:54'),
(2, 'LOG-0411', 1, 'MISTERS ACTIVATED', 'Humidity < 81%', 'Relay 2 ON', 'info', '2026-04-13 09:08:54'),
(3, 'LOG-0410', 2, 'CO2 SPIKE DETECTED', 'CO2 reached 650 PPM', 'Alert sent', 'danger', '2026-04-13 08:23:54'),
(4, 'LOG-0409', 1, 'FANS DEACTIVATED', 'CO2 normalized', 'Relay 1 OFF', 'info', '2026-04-13 06:48:54'),
(5, 'LOG-0408', 1, 'MISTERS DEACTIVATED', 'Humidity OK 85%', 'Relay 2 OFF', 'info', '2026-04-13 05:38:54'),
(6, 'LOG-0407', NULL, 'SYSTEM BOOT', 'Manual restart', 'All relays init', 'info', '2026-04-13 04:48:54'),
(7, 'LOG-0406', 2, 'FANS ACTIVATED', 'CO2 > 600 PPM', 'Relay 1 ON', 'warning', '2026-04-12 13:38:54'),
(8, 'LOG-0405', 2, 'SENSOR TIMEOUT', 'SN-004 no response', 'Alert sent', 'danger', '2026-04-12 12:23:54');

-- --------------------------------------------------------

--
-- Table structure for table `relay_devices`
--

CREATE TABLE `relay_devices` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `zone_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('fan','mister','exhaust','heater','light','other') NOT NULL,
  `relay_pin` tinyint(3) UNSIGNED NOT NULL,
  `is_on` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `relay_devices`
--

INSERT INTO `relay_devices` (`id`, `zone_id`, `name`, `type`, `relay_pin`, `is_on`, `is_active`, `updated_at`, `created_at`) VALUES
(1, 1, 'Ventilation Fans', 'fan', 5, 1, 1, '2026-04-13 10:38:54', '2026-04-13 10:38:54'),
(2, 1, 'Water Misters', 'mister', 18, 0, 1, '2026-04-13 10:38:54', '2026-04-13 10:38:54'),
(3, 2, 'Ventilation Fans', 'fan', 19, 0, 1, '2026-04-13 10:38:54', '2026-04-13 10:38:54'),
(4, 2, 'Water Misters', 'mister', 21, 0, 1, '2026-04-13 10:38:54', '2026-04-13 10:38:54');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `report_code` varchar(12) NOT NULL,
  `title` varchar(150) NOT NULL,
  `type` enum('weekly','monthly','event','custom','uptime') NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `format` enum('pdf','csv','xlsx') NOT NULL DEFAULT 'pdf',
  `file_path` varchar(255) DEFAULT NULL,
  `file_size` int(10) UNSIGNED DEFAULT NULL,
  `generated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `report_code`, `title`, `type`, `date_from`, `date_to`, `format`, `file_path`, `file_size`, `generated_by`, `created_at`) VALUES
(2, 'RPT-002', 'CO2 Spike Analysis — Zone B', 'event', '2025-03-07', '2025-03-07', 'pdf', NULL, 49152, 1, '2026-04-13 10:38:54'),
(3, 'RPT-003', 'Monthly Averages — February', 'monthly', '2025-02-01', '2025-02-28', 'pdf', NULL, 262144, 1, '2026-04-13 10:38:54'),
(4, 'RPT-004', 'Sensor Uptime Report', 'uptime', '2025-03-01', '2025-03-09', 'csv', NULL, 90112, 1, '2026-04-13 10:38:54'),
(5, 'RPT-005', 'Weekly Environmental Summary', 'weekly', '2025-02-24', '2025-03-02', 'pdf', NULL, 120832, 1, '2026-04-13 10:38:54');

-- --------------------------------------------------------

--
-- Table structure for table `sensors`
--

CREATE TABLE `sensors` (
  `id` varchar(10) NOT NULL,
  `zone_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `model` varchar(50) NOT NULL,
  `type` enum('temperature','humidity','co2') NOT NULL,
  `unit` varchar(10) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_seen` datetime DEFAULT NULL,
  `calibrated_at` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sensors`
--

INSERT INTO `sensors` (`id`, `zone_id`, `name`, `model`, `type`, `unit`, `is_active`, `last_seen`, `calibrated_at`, `created_at`) VALUES
('SN-001', 1, 'DHT22 — Temperature', 'DHT22', 'temperature', '°C', 1, NULL, '2026-04-13', '2026-04-13 10:38:54'),
('SN-002', 1, 'DHT22 — Humidity', 'DHT22', 'humidity', '%', 1, NULL, '2026-04-13', '2026-04-13 10:38:54'),
('SN-003', 1, 'MH-Z19B — CO2', 'MH-Z19B', 'co2', 'PPM', 1, NULL, '2026-04-13', '2026-04-13 10:38:54'),
('SN-004', 2, 'DHT22 — Temperature', 'DHT22', 'temperature', '°C', 1, NULL, '2026-04-13', '2026-04-13 10:38:54'),
('SN-005', 2, 'DHT22 — Humidity', 'DHT22', 'humidity', '%', 1, NULL, '2026-04-13', '2026-04-13 10:38:54'),
('SN-006', 2, 'MH-Z19B — CO2', 'MH-Z19B', 'co2', 'PPM', 1, NULL, '2026-04-13', '2026-04-13 10:38:54');

-- --------------------------------------------------------

--
-- Table structure for table `sensor_readings`
--

CREATE TABLE `sensor_readings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sensor_id` varchar(10) NOT NULL,
  `value` decimal(8,2) NOT NULL,
  `recorded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sensor_readings`
--

INSERT INTO `sensor_readings` (`id`, `sensor_id`, `value`, `recorded_at`) VALUES
(1, 'SN-001', 24.00, '2026-04-13 09:38:54'),
(2, 'SN-001', 23.80, '2026-04-13 09:48:54'),
(3, 'SN-001', 24.10, '2026-04-13 09:58:54'),
(4, 'SN-001', 24.30, '2026-04-13 10:08:54'),
(5, 'SN-001', 24.00, '2026-04-13 10:18:54'),
(6, 'SN-001', 24.20, '2026-04-13 10:28:54'),
(7, 'SN-001', 24.00, '2026-04-13 10:38:54'),
(8, 'SN-002', 85.00, '2026-04-13 09:38:54'),
(9, 'SN-002', 84.50, '2026-04-13 09:48:54'),
(10, 'SN-002', 83.20, '2026-04-13 09:58:54'),
(11, 'SN-002', 82.00, '2026-04-13 10:08:54'),
(12, 'SN-002', 83.50, '2026-04-13 10:18:54'),
(13, 'SN-002', 84.80, '2026-04-13 10:28:54'),
(14, 'SN-002', 85.00, '2026-04-13 10:38:54'),
(15, 'SN-003', 480.00, '2026-04-13 09:38:54'),
(16, 'SN-003', 510.00, '2026-04-13 09:48:54'),
(17, 'SN-003', 540.00, '2026-04-13 09:58:54'),
(18, 'SN-003', 580.00, '2026-04-13 10:08:54'),
(19, 'SN-003', 600.00, '2026-04-13 10:18:54'),
(20, 'SN-003', 610.00, '2026-04-13 10:28:54'),
(21, 'SN-003', 600.00, '2026-04-13 10:38:54');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_key` varchar(60) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`, `updated_at`) VALUES
('control_mode', 'automatic', 'System control mode: automatic | manual | scheduled', '2026-04-13 10:38:54'),
('data_retention_days', '90', 'How many days to keep sensor_readings rows', '2026-04-13 10:38:54'),
('esp32_ip', '192.168.1.100', 'Local IP of the ESP32 module', '2026-04-13 10:38:54'),
('notif_dashboard', '1', 'Show alert banner on dashboard', '2026-04-13 10:38:54'),
('notif_email', '0', 'Send email on danger events', '2026-04-13 10:38:54'),
('notif_sensor_fail', '1', 'Alert if sensor stops responding', '2026-04-13 10:38:54'),
('notif_sound', '1', 'Play audio on critical events', '2026-04-13 10:38:54'),
('poll_interval_sec', '10', 'Sensor polling interval in seconds', '2026-04-13 10:38:54'),
('relay_config', '4ch', 'Relay board type: 2ch | 4ch | 8ch', '2026-04-13 10:38:54'),
('site_version', 'v2.1.0', 'Application version string', '2026-04-13 10:38:54'),
('timezone', 'Asia/Manila', 'Dashboard display timezone', '2026-04-13 10:38:54');

-- --------------------------------------------------------

--
-- Table structure for table `thresholds`
--

CREATE TABLE `thresholds` (
  `id` int(10) UNSIGNED NOT NULL,
  `zone_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `sensor_type` enum('temperature','humidity','co2') NOT NULL,
  `min_value` decimal(8,2) DEFAULT NULL,
  `max_value` decimal(8,2) DEFAULT NULL,
  `danger_value` decimal(8,2) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `thresholds`
--

INSERT INTO `thresholds` (`id`, `zone_id`, `sensor_type`, `min_value`, `max_value`, `danger_value`, `updated_at`) VALUES
(1, NULL, 'temperature', 18.00, 28.00, 35.00, '2026-04-13 10:38:54'),
(2, NULL, 'humidity', 75.00, 90.00, 95.00, '2026-04-13 10:38:54'),
(3, NULL, 'co2', NULL, 600.00, 800.00, '2026-04-13 10:38:54'),
(4, 1, 'temperature', 18.00, 28.00, 34.00, '2026-04-13 10:38:54'),
(5, 1, 'humidity', 78.00, 90.00, 95.00, '2026-04-13 10:38:54'),
(6, 1, 'co2', NULL, 600.00, 800.00, '2026-04-13 10:38:54'),
(7, 2, 'temperature', 18.00, 28.00, 34.00, '2026-04-13 10:38:54'),
(8, 2, 'humidity', 75.00, 90.00, 95.00, '2026-04-13 10:38:54'),
(9, 2, 'co2', NULL, 600.00, 800.00, '2026-04-13 10:38:54');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','operator','viewer') NOT NULL DEFAULT 'viewer',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `is_active`, `created_at`, `last_login`) VALUES
(1, 'admin', 'admin@mushroomfarm.local', '$2y$12$PLACEHOLDER_HASH_REPLACE_ME', 'admin', 1, '2026-04-13 10:38:54', NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_hourly_averages`
-- (See below for the actual view)
--
CREATE TABLE `v_hourly_averages` (
`sensor_id` varchar(10)
,`hour_bucket` varchar(24)
,`avg_value` decimal(9,2)
,`min_value` decimal(8,2)
,`max_value` decimal(8,2)
,`reading_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_latest_readings`
-- (See below for the actual view)
--
CREATE TABLE `v_latest_readings` (
`sensor_id` varchar(10)
,`sensor_name` varchar(100)
,`sensor_type` enum('temperature','humidity','co2')
,`unit` varchar(10)
,`zone` varchar(50)
,`value` decimal(8,2)
,`recorded_at` datetime
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_unread_alerts`
-- (See below for the actual view)
--
CREATE TABLE `v_unread_alerts` (
`unread_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `zones`
--

CREATE TABLE `zones` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `zones`
--

INSERT INTO `zones` (`id`, `name`, `description`, `is_active`, `created_at`) VALUES
(1, 'Zone A', 'Primary grow room — main crop beds', 1, '2026-04-13 10:38:54'),
(2, 'Zone B', 'Secondary grow room — nursery beds', 1, '2026-04-13 10:38:54');

-- --------------------------------------------------------

--
-- Structure for view `v_hourly_averages`
--
DROP TABLE IF EXISTS `v_hourly_averages`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_hourly_averages`  AS SELECT `sensor_readings`.`sensor_id` AS `sensor_id`, date_format(`sensor_readings`.`recorded_at`,'%Y-%m-%d %H:00:00') AS `hour_bucket`, round(avg(`sensor_readings`.`value`),2) AS `avg_value`, round(min(`sensor_readings`.`value`),2) AS `min_value`, round(max(`sensor_readings`.`value`),2) AS `max_value`, count(0) AS `reading_count` FROM `sensor_readings` WHERE `sensor_readings`.`recorded_at` >= current_timestamp() - interval 24 hour GROUP BY `sensor_readings`.`sensor_id`, date_format(`sensor_readings`.`recorded_at`,'%Y-%m-%d %H:00:00') ORDER BY `sensor_readings`.`sensor_id` ASC, date_format(`sensor_readings`.`recorded_at`,'%Y-%m-%d %H:00:00') ASC ;

-- --------------------------------------------------------

--
-- Structure for view `v_latest_readings`
--
DROP TABLE IF EXISTS `v_latest_readings`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_latest_readings`  AS SELECT `s`.`id` AS `sensor_id`, `s`.`name` AS `sensor_name`, `s`.`type` AS `sensor_type`, `s`.`unit` AS `unit`, `z`.`name` AS `zone`, `r`.`value` AS `value`, `r`.`recorded_at` AS `recorded_at` FROM ((`sensors` `s` join `zones` `z` on(`z`.`id` = `s`.`zone_id`)) join `sensor_readings` `r` on(`r`.`id` = (select `sensor_readings`.`id` from `sensor_readings` where `sensor_readings`.`sensor_id` = `s`.`id` order by `sensor_readings`.`recorded_at` desc limit 1))) WHERE `s`.`is_active` = 1 ;

-- --------------------------------------------------------

--
-- Structure for view `v_unread_alerts`
--
DROP TABLE IF EXISTS `v_unread_alerts`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_unread_alerts`  AS SELECT count(0) AS `unread_count` FROM `alerts` WHERE `alerts`.`is_read` = 0 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `fk_alert_zone` (`zone_id`);

--
-- Indexes for table `event_logs`
--
ALTER TABLE `event_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `log_code` (`log_code`),
  ADD KEY `idx_severity` (`severity`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `fk_log_zone` (`zone_id`);

--
-- Indexes for table `relay_devices`
--
ALTER TABLE `relay_devices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_relay_zone` (`zone_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `report_code` (`report_code`),
  ADD KEY `fk_report_user` (`generated_by`);

--
-- Indexes for table `sensors`
--
ALTER TABLE `sensors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sensor_zone` (`zone_id`);

--
-- Indexes for table `sensor_readings`
--
ALTER TABLE `sensor_readings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sensor_time` (`sensor_id`,`recorded_at`),
  ADD KEY `idx_recorded_at` (`recorded_at`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `thresholds`
--
ALTER TABLE `thresholds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_zone_type` (`zone_id`,`sensor_type`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `zones`
--
ALTER TABLE `zones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `event_logs`
--
ALTER TABLE `event_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `relay_devices`
--
ALTER TABLE `relay_devices`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sensor_readings`
--
ALTER TABLE `sensor_readings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `thresholds`
--
ALTER TABLE `thresholds`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `zones`
--
ALTER TABLE `zones`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alerts`
--
ALTER TABLE `alerts`
  ADD CONSTRAINT `fk_alert_zone` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `event_logs`
--
ALTER TABLE `event_logs`
  ADD CONSTRAINT `fk_log_zone` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `relay_devices`
--
ALTER TABLE `relay_devices`
  ADD CONSTRAINT `fk_relay_zone` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `fk_report_user` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `sensors`
--
ALTER TABLE `sensors`
  ADD CONSTRAINT `fk_sensor_zone` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `sensor_readings`
--
ALTER TABLE `sensor_readings`
  ADD CONSTRAINT `fk_reading_sensor` FOREIGN KEY (`sensor_id`) REFERENCES `sensors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `thresholds`
--
ALTER TABLE `thresholds`
  ADD CONSTRAINT `fk_threshold_zone` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
