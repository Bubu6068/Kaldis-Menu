-- Migration 010: Add Google Analytics (GA4) configuration settings
INSERT IGNORE INTO `settings` (`key`, `value`, `description`) VALUES
('google_analytics_enabled', '0', '1 = enable Google Analytics tracking on the digital menu'),
('google_analytics_id', '', 'Google Analytics Measurement ID (e.g. G-XXXXXXXXXX)');
