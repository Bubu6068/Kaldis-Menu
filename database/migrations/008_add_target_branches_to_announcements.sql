-- ============================================================
-- Migration 008: Add target_branches (JSON array) to announcements
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE `announcements`
  ADD COLUMN `target_branches` TEXT NULL AFTER `branch_id`;
