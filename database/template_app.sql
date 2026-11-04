-- ===================================================
-- Database Template App Starter Kit (CodeIgniter 3)
-- Charset: utf8mb4 / InnoDB
-- ===================================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Table structure for `roles`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `roles` (`id`, `role_name`) VALUES
(1, 'Admin'),
(2, 'Manager'),
(3, 'Staff / User')
ON DUPLICATE KEY UPDATE `role_name` = VALUES(`role_name`);

-- --------------------------------------------------------
-- Table structure for `divisions`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `divisions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `division_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `divisions` (`id`, `division_name`) VALUES
(1, 'IT & Software Development'),
(2, 'Operasional & Bisnis'),
(3, 'Keuangan & Administrasi')
ON DUPLICATE KEY UPDATE `division_name` = VALUES(`division_name`);

-- --------------------------------------------------------
-- Table structure for `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL DEFAULT 3,
  `division_id` int(11) NOT NULL DEFAULT 1,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) DEFAULT NULL UNIQUE,
  `email` varchar(100) DEFAULT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_users_roles` (`role_id`),
  KEY `fk_users_divisions` (`division_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Default Seed Users
-- Password 'karisma' & 'admin123'
-- Hash bcrypt '$2y$10$7GVYCKlV4We/4O9638mbOe1kExXrEbK5Qo8DbNrtSBA7mnLQryOWO'
--
INSERT INTO `users` (`id`, `role_id`, `division_id`, `name`, `username`, `email`, `password`, `is_active`, `last_login`) VALUES
(1, 1, 1, 'Super Administrator', 'superadmin', 'superadmin@admin.com', '$2y$10$7GVYCKlV4We/4O9638mbOe1kExXrEbK5Qo8DbNrtSBA7mnLQryOWO', 1, NOW()),
(2, 2, 2, 'Manager Operasional', 'manager', 'manager@admin.com', '$2y$10$7GVYCKlV4We/4O9638mbOe1kExXrEbK5Qo8DbNrtSBA7mnLQryOWO', 1, NOW()),
(3, 3, 1, 'Firdan (Staff Dev)', 'firdan', 'firdan@firdan.com', '$2y$10$7GVYCKlV4We/4O9638mbOe1kExXrEbK5Qo8DbNrtSBA7mnLQryOWO', 1, NOW())
ON DUPLICATE KEY UPDATE 
  `name` = VALUES(`name`),
  `role_id` = VALUES(`role_id`),
  `is_active` = VALUES(`is_active`);

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

