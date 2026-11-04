-- ========================================================
-- Database Dedikasi: db_ttbp (Tanda Terima Barang Promosi)
-- ========================================================

CREATE DATABASE IF NOT EXISTS `db_ttbp` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_ttbp`;

-- --------------------------------------------------------
-- 1. Table structure for `roles`
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
-- 2. Table structure for `divisions`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `divisions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `division_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `divisions` (`id`, `division_name`) VALUES
(1, 'Operasional & Bisnis'),
(2, 'IT & Software Development'),
(3, 'Keuangan & Administrasi')
ON DUPLICATE KEY UPDATE `division_name` = VALUES(`division_name`);

-- --------------------------------------------------------
-- 3. Table structure for `users`
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

-- Password bawaan: 'karisma' & 'admin123'
INSERT INTO `users` (`id`, `role_id`, `division_id`, `name`, `username`, `email`, `password`, `is_active`) VALUES
(1, 1, 1, 'Super Administrator', 'superadmin', 'superadmin@admin.com', '$2y$10$7GVYCKlV4We/4O9638mbOe1kExXrEbK5Qo8DbNrtSBA7mnLQryOWO', 1),
(2, 2, 1, 'Manager Operasional', 'manager', 'manager@admin.com', '$2y$10$7GVYCKlV4We/4O9638mbOe1kExXrEbK5Qo8DbNrtSBA7mnLQryOWO', 1),
(3, 3, 1, 'Firdan', 'firdan', 'firdan@admin.com', '$2y$10$7GVYCKlV4We/4O9638mbOe1kExXrEbK5Qo8DbNrtSBA7mnLQryOWO', 1),
(4, 1, 1, 'Administrator', 'admin', 'admin@admin.com', '$2y$10$7GVYCKlV4We/4O9638mbOe1kExXrEbK5Qo8DbNrtSBA7mnLQryOWO', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- --------------------------------------------------------
-- 4. Table structure for `ttbp`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ttbp` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nomor_ttbp` VARCHAR(50) NOT NULL,
    `tanggal` DATE NOT NULL,
    `kode_kios` VARCHAR(50) NULL,
    `nama_kios` VARCHAR(255) NOT NULL,
    `alamat` TEXT NULL,
    `salesman` VARCHAR(100) NULL,
    `realisasi_program` VARCHAR(255) NULL,
    `id_program` INT NULL,
    `periode` VARCHAR(100) NULL,
    `perhatian` TEXT NULL,
    `disetujui_oleh` VARCHAR(100) NULL,
    `dibuat_oleh` VARCHAR(100) NULL,
    `penerima` VARCHAR(100) NULL,
    `is_manual` TINYINT(1) DEFAULT 0,
    `created_by` VARCHAR(100) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_nomor` (`nomor_ttbp`),
    INDEX `idx_tanggal` (`tanggal`),
    INDEX `idx_salesman` (`salesman`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 5. Table structure for `ttbp_items`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ttbp_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ttbp_id` INT NOT NULL,
    `no_urut` INT NOT NULL DEFAULT 1,
    `nama_barang` VARCHAR(255) NOT NULL,
    `jumlah` DECIMAL(15,2) NOT NULL DEFAULT 1.00,
    `satuan` VARCHAR(50) NOT NULL DEFAULT 'Unit',
    `keterangan` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ttbp` (`ttbp_id`),
    CONSTRAINT `fk_ttbp_items_ttbp` FOREIGN KEY (`ttbp_id`) REFERENCES `ttbp`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 6. Table structure for `laporan_bonus_promosi`
-- Rekap Laporan Bonus Promosi dari Data Analyst
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `laporan_bonus_promosi` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `no_urut_lap` VARCHAR(50) DEFAULT NULL,
  `tanggal_ttbp` DATE DEFAULT NULL,
  `nomor_ttbp` VARCHAR(50) DEFAULT NULL,
  `tgl_sdh_dipotong` DATE DEFAULT NULL,
  `faktur_promo` VARCHAR(50) DEFAULT NULL,
  `kode_kios` VARCHAR(50) DEFAULT NULL,
  `nama_pembeli` VARCHAR(255) DEFAULT NULL,
  `value_produk_program` DECIMAL(15,2) DEFAULT 0.00,
  `program` VARCHAR(255) DEFAULT NULL,
  `nama_barang` VARCHAR(255) DEFAULT NULL,
  `potong_nota` DECIMAL(15,2) DEFAULT 0.00,
  `pajak` DECIMAL(15,2) DEFAULT 0.00,
  `total_bonus` DECIMAL(15,2) DEFAULT 0.00,
  `qty` DECIMAL(15,2) DEFAULT 0.00,
  `satuan` VARCHAR(50) DEFAULT NULL,
  `volume_prog` DECIMAL(15,2) DEFAULT 0.00,
  `satuan_vol` VARCHAR(50) DEFAULT NULL,
  `periode_prog_awal` DATE DEFAULT NULL,
  `periode_prog_akhir` DATE DEFAULT NULL,
  `periode_prog_raw` VARCHAR(100) DEFAULT NULL,
  `faktur` VARCHAR(255) DEFAULT NULL,
  `kode_wilayah` VARCHAR(50) DEFAULT NULL,
  `tgl_kembali_ttbp` DATE DEFAULT NULL,
  `keterangan` TEXT DEFAULT NULL,
  `ttbp_id` INT DEFAULT NULL,
  `status_ttbp` ENUM('pending','generated','returned') NOT NULL DEFAULT 'pending',
  `periode_laporan` VARCHAR(100) DEFAULT 'Januari - Desember 2026',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_lap_nomor_ttbp` (`nomor_ttbp`),
  INDEX `idx_lap_kode_kios` (`kode_kios`),
  INDEX `idx_lap_status_ttbp` (`status_ttbp`),
  INDEX `idx_lap_ttbp_id` (`ttbp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
