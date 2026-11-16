-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 16, 2026 at 06:12 AM
-- Server version: 10.11.19-MariaDB-cll-lve
-- PHP Version: 8.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kaldisbp_cnet_synced_data`
--

CREATE DATABASE IF NOT EXISTS `kaldisbp_cnet_synced_data` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `kaldisbp_cnet_synced_data`;

-- --------------------------------------------------------

--
-- Table structure for table `menu_article_import`
--

DROP TABLE IF EXISTS `menu_article_import`;
CREATE TABLE `menu_article_import` (
  `id` int(11) DEFAULT NULL,
  `article_code` varchar(50) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `gsl_type_id` int(11) DEFAULT NULL,
  `gsl_type` varchar(250) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `child_preference_id` int(11) DEFAULT NULL,
  `child_preference` varchar(100) DEFAULT NULL,
  `parent_preference_id` int(11) DEFAULT NULL,
  `parent_preference` varchar(100) DEFAULT NULL,
  `branches_price` decimal(10,2) DEFAULT NULL,
  `currency` varchar(50) DEFAULT NULL,
  `airport_price` decimal(10,2) DEFAULT NULL,
  `eca_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for table `menu_article_import`
--
ALTER TABLE `menu_article_import`
  ADD KEY `idx_id` (`id`),
  ADD KEY `idx_article_code` (`article_code`),
  ADD KEY `idx_child_preference` (`child_preference`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
