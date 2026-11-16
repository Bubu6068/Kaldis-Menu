-- ============================================================
-- Migration: 005_add_is_popup_to_products.sql
-- Add is_popup flag for promotional QR scan welcome popup
-- ============================================================

ALTER TABLE `products`
  ADD COLUMN `is_popup` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_visible`;
