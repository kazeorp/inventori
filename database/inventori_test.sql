-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 07, 2025 at 11:28 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

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
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('normal','admin','superadmin') NOT NULL DEFAULT 'normal',
  `nama_lengkap` varchar(100) DEFAULT NULL
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
  `id` int(11) UNSIGNED NOT NULL,
  `type_name` varchar(100) NOT NULL
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
  `id` int(11) NOT NULL,
  `inventori_id` int(11) NOT NULL,
  `tanggal` datetime NOT NULL,
  `aksi` enum('Dipakai','Dikembalikan','Dipinjamkan','Diservis') NOT NULL,
  `hari` varchar(20) DEFAULT NULL,
  `ticket` varchar(100) DEFAULT NULL,
  `oleh` varchar(100) DEFAULT NULL,
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `histori_aset`
--

INSERT INTO `histori_aset` (`id`, `inventori_id`, `tanggal`, `aksi`, `hari`, `ticket`, `oleh`, `catatan`) VALUES
(11, 81, '2025-10-31 10:36:09', 'Diservis', NULL, 'tes', 'Aris Agustian', 'tes'),
(13, 78, '2025-10-31 14:11:19', '', NULL, NULL, 'Aris Agustian', 'Servis ID #10 di-reassign ke PIC baru: Aris Agustian. (Dilakukan oleh: Aris Agustian)'),
(15, 78, '2025-11-03 09:11:14', 'Diservis', NULL, 'Tes tiket 123', 'Aris Agustian', 'Tes aktivitas'),
(16, 78, '2025-11-03 09:11:58', '', NULL, NULL, 'Super Admin', 'Servis ID #10 di-reassign ke PIC baru: Super Admin. (Dilakukan oleh: Super Admin)'),
(17, 78, '2025-11-03 09:12:37', '', NULL, NULL, 'Aris Agustian', 'Servis ID #10 di-reassign ke PIC baru: Admin. (Dilakukan oleh: Aris Agustian)'),
(18, 78, '2025-11-03 09:12:45', '', NULL, NULL, 'Aris Agustian', 'Servis ID #10 di-reassign ke PIC baru: Aris Agustian. (Dilakukan oleh: Aris Agustian)'),
(19, 82, '2025-11-03 10:36:28', 'Diservis', NULL, 'Tes ok', 'Admin', 'tes ok');

-- --------------------------------------------------------

--
-- Table structure for table `inventori`
--

CREATE TABLE `inventori` (
  `id` int(11) NOT NULL,
  `rak` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `domain` enum('APP','SMF','CKP','TGR','KRW') NOT NULL DEFAULT 'APP',
  `hostname` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `device_category` varchar(50) NOT NULL DEFAULT 'Laptop',
  `ram` varchar(50) DEFAULT NULL,
  `storage` varchar(50) DEFAULT NULL,
  `win` varchar(50) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `kelengkapan` text DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `tanggal_keluar` date DEFAULT NULL,
  `nik` varchar(50) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `divisi` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventori`
--

INSERT INTO `inventori` (`id`, `rak`, `status`, `domain`, `hostname`, `type`, `device_category`, `ram`, `storage`, `win`, `keterangan`, `kelengkapan`, `tanggal_masuk`, `tanggal_keluar`, `nik`, `nama`, `divisi`) VALUES
(78, 'GD-R13', 'Grace Period', 'APP', 'IDWIKLTI1234', 'LENOVO THINKPAD L14 GEN 6', 'Laptop', '16', '256 SSD', '11', 'OK', 'TAS DAN ADAPTOR', '2025-10-16', '2025-10-30', '', 'ANA', 'SPD'),
(80, 'Assign', 'Assign', 'APP', 'IDWIKLTI4321', 'HP PROBOOK 430 G8', 'Laptop', '8', '256 SSD', '11', 'OK', 'TAS, ADAPTOR,CONVERTER LAN', NULL, NULL, '', 'UHIBUKI', 'CPD'),
(81, 'Loan', 'Loan', 'APP', 'IDWIKLTI0007', 'LENOVO THINKPAD L13', 'Laptop', '16', '256 SSD', '11', 'OK', 'TAS, ADAPTOR', NULL, NULL, '', 'ADJAD', 'SAP'),
(82, 'Assign', 'Assign', 'APP', 'IDWIKLTI1133', 'LENOVO THINKPAD L14 GEN 6', 'Laptop', '16', '256 SSD', '11', 'OK', 'TAS DAN ADAPTOR', '2025-10-16', '2025-10-30', '82318', 'Tes', 'SDP'),
(83, 'GD-R11', 'Spare', 'APP', 'IDWIKLTI0001', 'DELL LATITUDE 3430', 'Laptop', '16', '256 SSD', '11', 'OK', 'TAS DAN ADAPTOR', '2025-10-16', '2025-10-30', '81318', 'Tes', 'SDP');

-- --------------------------------------------------------

--
-- Table structure for table `service_list`
--

CREATE TABLE `service_list` (
  `id_service` int(11) NOT NULL,
  `id_inventori` int(11) NOT NULL COMMENT 'FK ke tabel inventori',
  `hostname` varchar(100) NOT NULL COMMENT 'Hostname aset saat discan',
  `nama_user` varchar(255) DEFAULT NULL COMMENT 'Nama user saat aset masuk service',
  `divisi` varchar(100) DEFAULT NULL COMMENT 'Divisi user saat aset masuk service',
  `tanggal_masuk` datetime NOT NULL COMMENT 'Waktu aset dicatat masuk service',
  `catatan` text DEFAULT NULL,
  `claim_status` varchar(100) DEFAULT NULL,
  `admin_claim_id` int(11) DEFAULT NULL,
  `admin_claim_name` varchar(100) DEFAULT NULL,
  `claim_timestamp` datetime DEFAULT NULL,
  `current_admin_id` int(11) DEFAULT NULL,
  `current_admin_name` varchar(100) DEFAULT NULL,
  `loan_hostname` varchar(255) DEFAULT NULL,
  `finish_status` varchar(50) DEFAULT NULL,
  `admin_finish_id` int(11) DEFAULT NULL,
  `admin_finish_name` varchar(100) DEFAULT NULL,
  `finish_timestamp` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_list`
--

INSERT INTO `service_list` (`id_service`, `id_inventori`, `hostname`, `nama_user`, `divisi`, `tanggal_masuk`, `catatan`, `claim_status`, `admin_claim_id`, `admin_claim_name`, `claim_timestamp`, `current_admin_id`, `current_admin_name`, `loan_hostname`, `finish_status`, `admin_finish_id`, `admin_finish_name`, `finish_timestamp`) VALUES
(10, 78, 'IDWIKLTI1234', 'ANA', 'SPD', '2025-10-30 16:50:08', 'Aset masuk service via scan mandiri.', 'On Service', 2, 'admin', NULL, 4, 'Aris Agustian', NULL, 'Selesai', 4, 'arisagustian', '2025-11-03 09:13:37'),
(11, 80, 'IDWIKLTI4321', 'UHIBUKI', 'CPD', '2025-10-30 16:52:02', 'Aset masuk service via scan mandiri.', 'On Service', 4, 'arisagustian', NULL, 4, 'arisagustian', NULL, 'Selesai', 4, 'arisagustian', '2025-10-30 16:54:21'),
(12, 82, 'IDWIKLTI1133', 'Tes', 'SDP', '2025-11-03 09:15:34', 'Aset masuk service via scan mandiri.', 'On Service', 2, 'admin', NULL, 2, 'admin', NULL, 'Selesai', 2, 'admin', '2025-11-03 10:36:41'),
(13, 82, 'IDWIKLTI1133', 'Tes', 'SDP', '2025-11-03 13:32:19', 'Aset masuk service via scan mandiri.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `validasi_aset`
--

CREATE TABLE `validasi_aset` (
  `id` int(11) NOT NULL,
  `inventori_id` int(11) NOT NULL,
  `waktu_validasi` datetime NOT NULL,
  `validator_nama` varchar(100) DEFAULT NULL,
  `keterangan` text DEFAULT NULL
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `device_types`
--
ALTER TABLE `device_types`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `histori_aset`
--
ALTER TABLE `histori_aset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `inventori`
--
ALTER TABLE `inventori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `service_list`
--
ALTER TABLE `service_list`
  MODIFY `id_service` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `validasi_aset`
--
ALTER TABLE `validasi_aset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
