-- Migration: 009_create_branch_child_categories.sql
-- Enables branch-level visibility (hide/show by branch) for sub-categories (child categories).

CREATE TABLE IF NOT EXISTS `branch_child_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `branch_id` INT NOT NULL,
    `child_category_id` INT NOT NULL,
    `is_available` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_branch_child` (`branch_id`, `child_category_id`),
    INDEX `idx_branch` (`branch_id`),
    INDEX `idx_child` (`child_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
