-- ============================================================
-- Migration 007: Add branch_id to announcements table
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE `announcements`
  ADD COLUMN `branch_id` INT UNSIGNED NULL AFTER `product_id`,
  ADD KEY `idx_ann_branch` (`branch_id`),
  ADD CONSTRAINT `fk_ann_branch` FOREIGN KEY (`branch_id`)
      REFERENCES `branches`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
