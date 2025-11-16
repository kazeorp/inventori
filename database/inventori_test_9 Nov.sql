-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 09, 2025 at 04:21 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventori_test`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('normal','admin','superadmin') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'normal',
  `nama_lengkap` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `role`, `nama_lengkap`) VALUES
(1, 'superadmin', '$2y$10$AMNMoPfbW0sZSLgNR5aDkuk659h0hppWHXa97w9.GET8IgKkX5JMi', 'superadmin', 'Super Admin'),
(2, 'admin', '$2y$10$432MKKxnNIpBjUJnmc/lVuuijAtPrj18vGCdg70h/UrmaYEKM4C5i', 'admin', 'Admin'),
(3, 'normal', '$2y$10$9bYNC9wSg8q6G0Ptcf3NJexsFqi.LzzI8ACTS/WbawDlaUdtFgHcq', 'normal', NULL),
(4, 'arisagustian', '$2y$10$y7g/WOgUabpsu1aDrtk/G.FN3J/IPZmIKdKmcjNJMeJP9nAOoRcrC', 'superadmin', 'Aris Agustian');

-- --------------------------------------------------------

--
-- Table structure for table `device_types`
--

CREATE TABLE `device_types` (
  `id` int UNSIGNED NOT NULL,
  `type_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `device_types`
--

INSERT INTO `device_types` (`id`, `type_name`) VALUES
(6, 'DELL LATITUDE 3430'),
(7, 'DELL LATITUDE 3470'),
(8, 'DELL LATITUDE 3480'),
(14, 'HP ELITEBOOK 640 G10'),
(10, 'HP PROBOOK 430 G1'),
(11, 'HP PROBOOK 430 G2'),
(12, 'HP PROBOOK 430 G3'),
(13, 'HP PROBOOK 430 G8'),
(9, 'HP PROBOOK 4340S'),
(1, 'LENOVO THINKPAD 13'),
(2, 'LENOVO THINKPAD L13'),
(15, 'LENOVO THINKPAD L14 GEN 6'),
(3, 'LENOVO THINKPAD L380'),
(4, 'LENOVO THINKPAD L390'),
(5, 'LENOVO THINKPAD X1 CARBON');

-- --------------------------------------------------------

--
-- Table structure for table `histori_aset`
--

CREATE TABLE `histori_aset` (
  `id` int NOT NULL,
  `inventori_id` int NOT NULL,
  `tanggal` datetime NOT NULL,
  `aksi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ticket` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `oleh` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `catatan` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `histori_aset`
--

INSERT INTO `histori_aset` (`id`, `inventori_id`, `tanggal`, `aksi`, `ticket`, `oleh`, `catatan`) VALUES
(11, 81, '2025-10-31 10:36:09', 'Diservis', 'tes', 'Aris Agustian', 'tes'),
(13, 78, '2025-10-31 14:11:19', '', NULL, 'Aris Agustian', 'Servis ID #10 di-reassign ke PIC baru: Aris Agustian. (Dilakukan oleh: Aris Agustian)'),
(15, 78, '2025-11-03 09:11:14', 'Diservis', 'Tes tiket 123', 'Aris Agustian', 'Tes aktivitas'),
(16, 78, '2025-11-03 09:11:58', '', NULL, 'Super Admin', 'Servis ID #10 di-reassign ke PIC baru: Super Admin. (Dilakukan oleh: Super Admin)'),
(17, 78, '2025-11-03 09:12:37', '', NULL, 'Aris Agustian', 'Servis ID #10 di-reassign ke PIC baru: Admin. (Dilakukan oleh: Aris Agustian)'),
(18, 78, '2025-11-03 09:12:45', '', NULL, 'Aris Agustian', 'Servis ID #10 di-reassign ke PIC baru: Aris Agustian. (Dilakukan oleh: Aris Agustian)'),
(19, 82, '2025-11-03 10:36:28', 'Diservis', 'Tes ok', 'Admin', 'tes ok'),
(20, 82, '2025-11-09 20:56:52', 'Diservis', 'Tes Loan', 'Admin', 'TTTes'),
(21, 82, '2025-11-09 23:00:18', 'Diservis', 'Tes Loan', 'Admin', '123'),
(22, 82, '2025-11-09 23:00:31', 'Loan Dibuat & Status Aset A diubah menjadi: Pending Service', 'Tes Loan', 'Admin', '123'),
(23, 83, '2025-11-09 23:00:31', 'Loan', 'Tes Loan', 'Admin', 'LOAN kepada user \'Tes\' (82318, Divisi: SDP). Tiket: Tes Loan. Catatan Tambahan: 123');

-- --------------------------------------------------------

--
-- Table structure for table `inventori`
--

CREATE TABLE `inventori` (
  `id` int NOT NULL,
  `rak` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `domain` enum('APP','SMF','CKP','TGR','KRW') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'APP',
  `hostname` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `device_category` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Laptop',
  `ram` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `storage` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `win` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci,
  `kelengkapan` text COLLATE utf8mb4_general_ci,
  `tanggal_masuk` date DEFAULT NULL,
  `tanggal_keluar` date DEFAULT NULL,
  `nik` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `divisi` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventori`
--

INSERT INTO `inventori` (`id`, `rak`, `status`, `domain`, `hostname`, `type`, `device_category`, `ram`, `storage`, `win`, `keterangan`, `kelengkapan`, `tanggal_masuk`, `tanggal_keluar`, `nik`, `nama`, `divisi`) VALUES
(78, 'GD-R13', 'Grace Period', 'APP', 'IDWIKLTI1234', 'LENOVO THINKPAD L14 GEN 6', 'Laptop', '16', '256 SSD', '11', 'OK', 'TAS DAN ADAPTOR', '2025-10-16', '2025-10-30', '', 'ANA', 'SPD'),
(80, 'Assign', 'Assign', 'APP', 'IDWIKLTI4321', 'HP PROBOOK 430 G8', 'Laptop', '8', '256 SSD', '11', 'OK', 'TAS, ADAPTOR,CONVERTER LAN', NULL, NULL, '', 'UHIBUKI', 'CPD'),
(81, 'Loan', 'Loan', 'APP', 'IDWIKLTI0007', 'LENOVO THINKPAD L13', 'Laptop', '16', '256 SSD', '11', 'OK', 'TAS, ADAPTOR', NULL, NULL, '', 'ADJAD', 'SAP'),
(82, 'Assign', 'Pending Service', 'APP', 'IDWIKLTI1133', 'LENOVO THINKPAD L14 GEN 6', 'Laptop', '16', '256 SSD', '11', 'OK', 'TAS DAN ADAPTOR', '2025-10-16', '2025-10-30', '82318', 'Tes', 'SDP'),
(83, 'GD-R11', 'Loan', 'APP', 'IDWIKLTI0001', 'DELL LATITUDE 3430', 'Laptop', '16', '256 SSD', '11', 'OK', 'TAS DAN ADAPTOR', '2025-10-16', '2025-10-30', '82318', 'Tes', 'SDP');

-- --------------------------------------------------------

--
-- Table structure for table `service_list`
--

CREATE TABLE `service_list` (
  `id_service` int NOT NULL,
  `id_inventori` int NOT NULL COMMENT 'FK ke tabel inventori',
  `hostname` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Hostname aset saat discan',
  `nama_user` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Nama user saat aset masuk service',
  `divisi` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Divisi user saat aset masuk service',
  `tanggal_masuk` datetime NOT NULL COMMENT 'Waktu aset dicatat masuk service',
  `catatan` text COLLATE utf8mb4_general_ci,
  `claim_status` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `admin_claim_id` int DEFAULT NULL,
  `admin_claim_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `claim_timestamp` datetime DEFAULT NULL,
  `current_admin_id` int DEFAULT NULL,
  `current_admin_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `loan_hostname` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `finish_status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `admin_finish_id` int DEFAULT NULL,
  `admin_finish_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `finish_timestamp` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_list`
--

INSERT INTO `service_list` (`id_service`, `id_inventori`, `hostname`, `nama_user`, `divisi`, `tanggal_masuk`, `catatan`, `claim_status`, `admin_claim_id`, `admin_claim_name`, `claim_timestamp`, `current_admin_id`, `current_admin_name`, `loan_hostname`, `finish_status`, `admin_finish_id`, `admin_finish_name`, `finish_timestamp`) VALUES
(10, 78, 'IDWIKLTI1234', 'ANA', 'SPD', '2025-10-30 16:50:08', 'Aset masuk service via scan mandiri.', 'On Service', 2, 'admin', NULL, 4, 'Aris Agustian', NULL, 'Selesai', 4, 'arisagustian', '2025-11-03 09:13:37'),
(11, 80, 'IDWIKLTI4321', 'UHIBUKI', 'CPD', '2025-10-30 16:52:02', 'Aset masuk service via scan mandiri.', 'On Service', 4, 'arisagustian', NULL, 4, 'arisagustian', NULL, 'Selesai', 4, 'arisagustian', '2025-10-30 16:54:21'),
(12, 82, 'IDWIKLTI1133', 'Tes', 'SDP', '2025-11-03 09:15:34', 'Aset masuk service via scan mandiri.', 'On Service', 2, 'admin', NULL, 2, 'admin', NULL, 'Selesai', 2, 'admin', '2025-11-03 10:36:41'),
(13, 82, 'IDWIKLTI1133', 'Tes', 'SDP', '2025-11-03 13:32:19', 'Aset masuk service via scan mandiri.', 'On Service', 2, '0', '2025-11-09 18:13:07', 2, 'admin', '', NULL, NULL, NULL, NULL),
(14, 83, 'IDWIKLTI0001', 'Tes', 'SDP', '2025-11-09 18:18:57', 'Aset masuk service via scan mandiri.', 'On Service', 2, '0', '2025-11-09 18:19:12', 2, 'admin', '', 'Selesai', 2, 'Admin', '2025-11-09 20:45:00'),
(15, 80, 'IDWIKLTI4321', 'UHIBUKI', 'CPD', '2025-11-09 18:25:21', 'Aset masuk service via scan mandiri.', 'On Service', 2, '0', '2025-11-09 18:25:39', 2, 'admin', '', 'Selesai', 2, NULL, '2025-11-09 20:38:46');

-- --------------------------------------------------------

--
-- Table structure for table `validasi_aset`
--

CREATE TABLE `validasi_aset` (
  `id` int NOT NULL,
  `inventori_id` int NOT NULL,
  `waktu_validasi` datetime NOT NULL,
  `validator_nama` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `device_types`
--
ALTER TABLE `device_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `histori_aset`
--
ALTER TABLE `histori_aset`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventori_id` (`inventori_id`);

--
-- Indexes for table `inventori`
--
ALTER TABLE `inventori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_list`
--
ALTER TABLE `service_list`
  ADD PRIMARY KEY (`id_service`),
  ADD KEY `fk_inventori_service` (`id_inventori`);

--
-- Indexes for table `validasi_aset`
--
ALTER TABLE `validasi_aset`
  ADD PRIMARY KEY (`id`),
  ADD KEY `validasi_aset_ibfk_1` (`inventori_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `device_types`
--
ALTER TABLE `device_types`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `histori_aset`
--
ALTER TABLE `histori_aset`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `inventori`
--
ALTER TABLE `inventori`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `service_list`
--
ALTER TABLE `service_list`
  MODIFY `id_service` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `validasi_aset`
--
ALTER TABLE `validasi_aset`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `histori_aset`
--
ALTER TABLE `histori_aset`
  ADD CONSTRAINT `histori_aset_ibfk_1` FOREIGN KEY (`inventori_id`) REFERENCES `inventori` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_list`
--
ALTER TABLE `service_list`
  ADD CONSTRAINT `fk_inventori_service` FOREIGN KEY (`id_inventori`) REFERENCES `inventori` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `validasi_aset`
--
ALTER TABLE `validasi_aset`
  ADD CONSTRAINT `validasi_aset_ibfk_1` FOREIGN KEY (`inventori_id`) REFERENCES `inventori` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
