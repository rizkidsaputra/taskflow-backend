-- phpMyAdmin SQL Dump (Revisi)
-- versi 5.2.1
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET NAMES utf8mb4 */;

-- Buat database dan gunakan
CREATE DATABASE IF NOT EXISTS `taskflow` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `taskflow`;

-- --------------------------------------------------------
-- Table structure for table `auth_tokens`
-- --------------------------------------------------------
CREATE TABLE `auth_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `auth_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(24, 4, '3d78ff83c63ff27d6502b8c2c2d73b3422dbefc6699a999b', '2025-08-25 18:47:22', '2025-08-18 11:47:22'),
(25, 7, 'f4e2b33ef6a9fffc81a29bffff133c765f2cab5de3e3f54a', '2025-08-25 18:51:57', '2025-08-18 11:51:57');

-- --------------------------------------------------------
-- Table structure for table `projects`
-- --------------------------------------------------------
CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `owner_id` int(11) NOT NULL,
  `is_private` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `projects` (`id`, `title`, `description`, `owner_id`, `is_private`, `created_at`) VALUES
(1, 'Website Redesign', 'Redesign untuk company profile website', 4, 0, '2025-08-18 11:49:40'),
(2, 'Mobile App', 'Pengembangan aplikasi mobile Android/iOS', 5, 1, '2025-08-18 11:49:40'),
(3, 'Data Migration', 'Migrasi database lama ke sistem baru', 6, 0, '2025-08-18 11:49:40'),
(4, 'Marketing Campaign', 'Kampanye digital marketing Q4', 7, 0, '2025-08-18 11:49:40');

-- --------------------------------------------------------
-- Table structure for table `project_members`
-- --------------------------------------------------------
CREATE TABLE `project_members` (
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`project_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `tasks`
-- --------------------------------------------------------
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('todo','in_progress','done') DEFAULT 'todo',
  `assignee` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deadline` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `assignee` (`assignee`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tasks` (`id`, `project_id`, `title`, `description`, `status`, `assignee`, `created_at`, `deadline`) VALUES
(1, 1, 'Buat wireframe', 'Desain awal halaman utama', 'done', 4, '2025-08-18 11:49:50', '2025-08-25 12:00:00'),
(2, 1, 'Implementasi UI', 'Coding React frontend', 'in_progress', 5, '2025-08-18 11:49:50', '2025-08-28 12:00:00'),
(3, 1, 'Setup hosting', 'Deploy ke VPS baru', 'todo', 6, '2025-08-18 11:49:50', NULL),
(4, 2, 'Buat API Auth', 'Login & register API', 'done', 5, '2025-08-18 11:49:50', '2025-08-20 10:00:00'),
(5, 2, 'Integrasi Firebase', 'Push notif & analytics', 'in_progress', 4, '2025-08-18 11:49:50', NULL),
(6, 3, 'Export data lama', 'Dump database lama', 'done', 6, '2025-08-18 11:49:50', NULL),
(7, 3, 'Import ke DB baru', 'Setup MariaDB baru', 'todo', 7, '2025-08-18 11:49:50', '2025-08-30 12:00:00'),
(8, 4, 'Desain banner iklan', 'Untuk sosial media', 'in_progress', 5, '2025-08-18 11:49:50', NULL),
(9, 4, 'Setup ads campaign', 'Google & FB Ads', 'todo', 7, '2025-08-18 11:49:50', '2025-09-05 09:00:00');

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `username`, `full_name`, `email`, `password`) VALUES
(4, 'Fauzan', 'Ahmad Fauzan Ramadhan', 'Fauzan@networkCCI.com', '$2y$10$OCFtpC9i5ofqjYWqE.CmeujgF1buSp6sx4uN6NlHkWL4hP84t3jp6'),
(5, 'Rizki', 'Mohammad Rizki Dwi Saputra', 'Rizki@networkCCI.com', '$2y$10$VmvrmZ.XbOs8wQf3RuZXP.fGnYocYR5rmAFQC7VtNcKL1cQig/8MC'),
(6, 'Lovind', 'Lovind Luthfan Hakeem Firdaus', 'Lovind@networkCCI.com', '$2y$10$zl4legqxOLYTqSr3ShqZauSoQ.AWEyzyGP./.dkYcuncsAtQOHUsq'),
(7, 'Danish', 'Muhammad Khaizuran Danish', 'Danish@networkCCI.com', '$2y$10$.YPqPA.mTBbugLUCGhM8yeYlUpkery8v/X5iefEfR3X/S7kROn4Y.');

-- --------------------------------------------------------
-- Foreign key constraints
-- --------------------------------------------------------
ALTER TABLE `auth_tokens`
  ADD CONSTRAINT `auth_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `project_members`
  ADD CONSTRAINT `project_members_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`assignee`) REFERENCES `users` (`id`) ON DELETE SET NULL;

COMMIT;
