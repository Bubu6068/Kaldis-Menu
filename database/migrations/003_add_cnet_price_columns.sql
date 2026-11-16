-- ============================================================
-- Migration: 003_add_cnet_price_columns.sql
-- Add branches_price, currency, airport_price, eca_price to products
-- ============================================================

ALTER TABLE `products`
  ADD COLUMN `branches_price` DECIMAL(10,2) NULL AFTER `default_value`,
  ADD COLUMN `currency` VARCHAR(50) NULL DEFAULT 'ETB' AFTER `branches_price`,
  ADD COLUMN `airport_price` DECIMAL(10,2) NULL AFTER `currency`,
  ADD COLUMN `eca_price` DECIMAL(10,2) NULL AFTER `airport_price`;

UPDATE `products`
  SET `branches_price` = CAST(`default_value` AS DECIMAL(10,2))
  WHERE `branches_price` IS NULL 
    AND `default_value` IS NOT NULL 
    AND `default_value` REGEXP '^[0-9]+(\\.[0-9]+)?$';

UPDATE `products`
  SET `currency` = COALESCE(`price_currency`, 'ETB')
  WHERE `currency` IS NULL;
