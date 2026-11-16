-- ============================================================
-- Migration: 004_add_category_image_path.sql
-- Add image_path to child_categories for Sub-category visual cards
-- ============================================================

ALTER TABLE `child_categories`
  ADD COLUMN `image_path` VARCHAR(255) NULL AFTER `is_visible`;
