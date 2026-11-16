-- ============================================================
-- Migration 006: New Product Announcement / Moving Banner System
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `announcements` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title`           VARCHAR(255) NOT NULL,
    `badge_text`      VARCHAR(50)  NOT NULL DEFAULT 'NEW PRODUCT',
    `message`         TEXT         NOT NULL,
    `product_id`      INT UNSIGNED NULL,
    `start_date`      DATE         NULL,
    `end_date`        DATE         NULL,
    `scroll_duration` INT UNSIGNED NOT NULL DEFAULT 25 COMMENT 'Seconds per scroll cycle',
    `bg_color`        VARCHAR(50)  NOT NULL DEFAULT '#2C1810',
    `text_color`      VARCHAR(50)  NOT NULL DEFAULT '#FDF8EE',
    `accent_color`    VARCHAR(50)  NOT NULL DEFAULT '#C5A059',
    `is_active`       TINYINT(1)   NOT NULL DEFAULT 1,
    `sort_order`      SMALLINT     NOT NULL DEFAULT 0,
    `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_ann_active` (`is_active`),
    KEY `idx_ann_dates` (`start_date`, `end_date`),
    KEY `idx_ann_product` (`product_id`),
    CONSTRAINT `fk_ann_product` FOREIGN KEY (`product_id`)
        REFERENCES `products`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default settings for announcements
INSERT IGNORE INTO `settings` (`key`, `value`, `description`) VALUES
('announcements_enabled', '1', 'Enable or disable the moving product announcement banner'),
('announcements_auto_products', '1', 'Automatically feature newest products when no custom announcement is active');
