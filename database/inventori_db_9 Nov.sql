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
-- Database: `inventori_db`
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
  `hari` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ticket` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `oleh` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `catatan` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `histori_aset`
--

INSERT INTO `histori_aset` (`id`, `inventori_id`, `tanggal`, `aksi`, `hari`, `ticket`, `oleh`, `catatan`) VALUES
(1, 8, '2025-10-08 04:05:07', 'Dikembalikan', NULL, 'TES123', 'adadea', 'Diterima dalam kondisi baik'),
(2, 8, '2025-10-08 06:27:33', 'Dipakai', NULL, 'TES234', 'Adida', 'Dipakai untuk new joiner SPD'),
(3, 8, '2025-10-08 11:31:15', 'Dikembalikan', NULL, 'TES231', 'TES', 'TES'),
(4, 8, '2025-10-08 11:37:15', 'Diservis', 'Rabu', 'TES083', 'TES', 'TES'),
(5, 68, '2025-10-10 13:57:32', 'Dipakai', 'Jumat', 'RITM1160571', 'Pak Endang', 'Di Assign ke QESHA ANGGRAINI GEMINTANG'),
(7, 75, '2025-10-10 14:02:51', 'Dikembalikan', 'Jumat', '', 'Pak Endang', 'Asset dikembalikan EX FAESAL CAHYA AKBAR SPD MRO'),
(8, 9, '2025-10-21 08:09:40', 'Diservis', NULL, 'TESAJA', 'admin', 'Upgrade RAM'),
(9, 9, '2025-10-23 08:28:21', 'Diservis', NULL, 'TES567', 'superadmin', 'Upgrade ram'),
(10, 9, '2025-10-23 13:40:02', 'Diservis', NULL, 'TES987', 'superadmin', 'Tes aja'),
(11, 8, '2025-10-30 17:03:35', '', NULL, 'TES123', 'Admin', 'Tes'),
(12, 21, '2025-10-30 17:08:12', 'Diservis', NULL, 'wq', 'Admin', 'printer'),
(13, 21, '2025-10-31 08:52:25', '', NULL, 'tes', 'Aris Agustian', 'ok'),
(14, 21, '2025-10-31 08:54:57', '', NULL, 'tes', 'Aris Agustian', 'ok');

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
(8, 'GD-R11', 'Spare', 'APP', '8107184233NB', 'HP Probook 430 G3', 'Laptop', '16', 'SSD 256', '11', 'OK', 'Tas dan Adaptor', NULL, '0000-00-00', '', '', ''),
(9, 'Assign', 'Assign', 'APP', 'IDWIKLTI0352', 'Lenovo L13', 'Laptop', '8', 'SSD 256', '11', 'OK', 'Adaptor', NULL, '2025-09-29', '1147570', 'OKTIVIAN MARPAUNG', 'CPD'),
(10, 'Assign', 'Assign', 'APP', 'IDWIKLTI0126', 'Lenovo L390', 'Laptop', '8', 'SSD 256', '11', 'OK', 'Tas, Adaptor, Converter LAN & VGA', NULL, '2025-10-01', '1153411', 'NATASHYA SUTANTO', 'CPD'),
(11, 'GD-R13', 'Grace Period', 'APP', '8107184506NB', 'Dell 3480', 'Laptop', '8', 'HDD 500', '10', 'EX AUDIT, LAYAR BERGARIS', 'Tas dan Adaptor', NULL, '2025-09-01', '', '', ''),
(12, 'GD-R13', 'Grace Period', 'APP', '8107184507NB', 'Dell 3480', 'Laptop', '16', 'SSD 256', '11', 'EX AUDIT', 'Tas dan Adaptor', NULL, '2025-09-01', '', '', ''),
(13, 'GD-R13', 'Grace Period', 'APP', 'IDWIKLTI0495', 'Lenovo L13', 'Laptop', '8', 'SSD 256', '11', 'OK', 'Tas, Adaptor, Converter LAN & VGA', NULL, '2025-09-01', '', '', ''),
(14, 'GD-R13', 'Grace Period', 'APP', '910710180003NB', 'Lenovo L380', 'Laptop', '8', 'SSD 256', '10', 'LEMOT, SLOW CHARGER', 'Tas dan Adaptor', NULL, '2025-06-01', '', '', ''),
(15, 'GD-R13', 'Grace Period', 'APP', 'IDWIKLTI0408', 'Lenovo L13', 'Laptop', '8', 'SSD 256', '', 'SSD RUSAK', 'Tas dan Adaptor', NULL, '2025-09-01', '', '', ''),
(16, 'GD-R13', 'Grace Period', 'APP', 'IDWIKLTI0091', 'Lenovo L390', 'Laptop', '8', 'SSD 256', '10', 'BACKUP DATA', 'Tas, Adaptor, Converter VGA', NULL, '2025-09-01', '', '', ''),
(17, 'GD-R13', 'Grace Period', 'APP', '810708180081NB', 'Lenovo L380', 'Laptop', '', '', '', '', 'Tas, Adaptor, Converter VGA', NULL, '2025-09-01', '', '', ''),
(18, 'GD-R13', 'Grace Period', 'APP', 'IDWIKLTI0805', 'Dell 3430', 'Laptop', '8', 'SSD 256', '11', 'KEYBOARD ERROR', 'Tas dan Adaptor', NULL, '2025-09-01', '', '', ''),
(19, 'GD-R13', 'Grace Period', 'APP', '8107184466NB', 'Dell 3480', 'Laptop', '8', 'SSD 256', '11', 'BATERAI BOCOR', 'Tas dan Adaptor', NULL, '2025-09-01', '', '', ''),
(20, 'GD-R13', 'Grace Period', 'APP', '8107184078NB', 'HP Probook 430 G2', 'Laptop', '16', 'SSD 256', '11', 'KIPAS TIDAK TERDETEKSI', 'Tas dan Adaptor', NULL, '2025-09-01', '', '', ''),
(21, 'Assign', 'Assign', 'APP', 'IDWIKLTI0022', 'Lenovo L390', 'Laptop', '8', 'SSD 256', '11', 'OK', 'Tas, Adaptor, Converter VGA', NULL, '2025-09-30', '1153449', 'AFRIYANTO DWI PUTRA', 'SPD'),
(22, 'GD-R12', 'Pending Service', 'APP', 'IDWIKLTI0502', 'Lenovo L13', 'Laptop', '8', 'SSD 256', '11', 'SPEAKER RUSAK', 'Tas, Adaptor, Converter LAN & VGA', NULL, '0000-00-00', '', '', ''),
(23, 'GD-R12', 'Pending Service', 'APP', '8107184269NB', 'Dell 3470', 'Laptop', '', '', '', 'BLANK TIDAK BISA HIDUP', 'Tas dan Adaptor', NULL, '0000-00-00', '', '', ''),
(38, 'GD-R12', 'Pending Service', 'APP', '810710180133NB', 'Lenovo Thinkpad X1 Carbon', 'Laptop', '8', 'SSD 256', '11', 'BATERAI BOCOR, CMOS ERROR', 'Tas, Adaptor, Converter LAN', NULL, '0000-00-00', '', '', ''),
(39, 'GD-R12', 'Pending Service', 'APP', '810710180122NB', 'Lenovo L380', 'Laptop', '8', 'SSD 256', '11', 'BATERAI BOCOR', 'Tas, Adaptor, Converter LAN', NULL, '0000-00-00', '', '', ''),
(40, 'Assign', 'Assign', 'APP', '8107184801NB', 'HP Probook 430 G1', 'Laptop', '12', 'HDD 500', '10', 'LEMOT', 'Tas dan Adaptor', NULL, '2025-09-30', '1055550', 'DEKI WITAMARA', 'CIT'),
(41, 'GD-R12', 'Pending Service', 'APP', '8107184356NB', 'Dell 3480', 'Laptop', '', '', '', 'LAYAR BLANK', 'Tas dan Adaptor', NULL, '0000-00-00', '', '', ''),
(42, 'GD-R12', 'Pending Service', 'APP', '8107184441NB', 'Dell 3480', 'Laptop', '', '', '', 'BATERAI BOCOR', 'Tas', NULL, '0000-00-00', '', '', ''),
(43, 'GD-R12', 'Pending Service', 'APP', '8107184226NB', 'HP Probook 430 G3', 'Laptop', '8', 'SSD 256', '11', 'BATERAI BOCOR', 'Tas dan Adaptor', NULL, '0000-00-00', '', '', ''),
(44, 'GD-R12', 'Pending Service', 'APP', '8107184495NB', 'Dell 3480', 'Laptop', '8', 'HDD 500', '10', 'LCD BERGARIS', 'Tas dan Adaptor', NULL, '0000-00-00', '', '', ''),
(45, 'GD-R12', 'Pending Service', 'APP', '8107184530NB', 'Dell 3480', 'Laptop', '', '', '', 'TIDAK NYALA', 'Tas, Adaptor, Converter VGA', NULL, '0000-00-00', '', '', ''),
(46, 'GD-R4', 'Scrap', 'APP', '810705180042NB', 'Lenovo 13', 'Laptop', '', '', '', '', 'Tas', NULL, '0000-00-00', '', '', ''),
(47, 'GD-R4', 'Scrap', 'APP', 'IDWIKLTI0343', 'Lenovo L13', 'Laptop', '', '', '11', '', 'Tas, Adaptor, Converter LAN & VGA', NULL, '0000-00-00', '', '', ''),
(48, 'GD-R4', 'Scrap', 'APP', '8107184296NB', 'Lenovo 13', 'Laptop', '', '', '', '', 'Tas', NULL, '0000-00-00', '', '', ''),
(49, 'GD-R12', 'Pending Service', 'APP', '810707180052NB', 'Lenovo 13', 'Laptop', '8', 'SSD 256', '11', 'ERROR RFID, LAYAR BERGARIS, BATERAI BOCOR', 'Tas dan Adaptor', NULL, '0000-00-00', '', '', ''),
(50, 'GD-R12', 'Pending Service', 'APP', '8107184249NB', 'Dell 3470', 'Laptop', '12', 'HDD 300', '10', 'LEMOT, BATERAI BOCOR', 'Tas dan Adaptor', NULL, '0000-00-00', '', '', ''),
(51, 'GD-R12', 'Pending Service', 'APP', '8107184296NB', 'Dell 3470', 'Laptop', '8', 'HDD 500', '10', 'BATERAI BOCOR', 'Tas dan Adaptor', NULL, '0000-00-00', '', '', ''),
(52, 'GD-R12', 'Pending Service', 'APP', '8107164608NB', 'HP Probook 4340S', 'Laptop', '8', 'HDD 300', '10', 'BATERAI BOCOR', 'Tas dan Adaptor', NULL, '0000-00-00', '', '', ''),
(53, 'GD-R12', 'Pending Service', 'APP', '8107184308NB', 'Dell 3470', 'Laptop', '8', 'HDD 500', '10', 'BATERAI BOCOR', 'Tas dan Adaptor', NULL, '0000-00-00', '', '', ''),
(54, 'GD-R9', 'Ready To Assign', 'APP', '8107184324NB', 'Dell 3470', 'Laptop', '8', 'HDD 500', '10', 'BATERAI BOCOR', 'Tas dan Adaptor', NULL, '0000-00-00', '', 'MASITA DINDA RIANI', ''),
(55, 'Assign', 'Assign', 'APP', 'IDWIKLTI0756', 'Dell 3430', 'Laptop', '', '', '11', 'OK', 'Tas', NULL, '2025-09-30', '1151554', 'VERHULST CLINHAERT', 'CPD'),
(56, 'GD-R9', 'Ready To Assign', 'APP', 'IDWIKLTI0306', 'Lenovo L13', 'Laptop', '8', 'SSD 256', '11', 'OK', 'Tas, Adaptor, Converter LAN & VGA', NULL, '0000-00-00', '', 'NATASYA SUSANTO', ''),
(57, 'GD-R9', 'Ready To Assign', 'APP', '810711180146NB', 'Lenovo L380', 'Laptop', '8', 'SSD 256', '11', 'OK', 'Tas dan Converter VGA', NULL, '0000-00-00', '', 'PIC FWI', ''),
(58, 'GD-R9', 'Ready To Assign', 'APP', 'IDWIKLTI0491', 'Lenovo L13', 'Laptop', '', '', '10', 'OK', 'Tas dan Adaptor', NULL, '0000-00-00', '', 'MUHAMMAD DAFFA PRATAMA', ''),
(59, 'GD-R9', 'Ready To Assign', 'APP', '8107184321NB', 'Dell 3470', 'Laptop', '', '', '', 'OK', 'Tas dan Adaptor', NULL, '0000-00-00', '', 'BAGOES HUDAYA KAMIL', ''),
(60, 'GD-R9', 'Ready To Assign', 'APP', 'IDSMLLTI0657', 'Lenovo L13', 'Laptop', '16', 'SSD 256', '10', 'OK', 'Tas dan Adaptor', NULL, '0000-00-00', '', 'EX VIVI TO TIARA CRD', ''),
(61, 'GD-R9', 'Ready To Assign', 'APP', 'IDWIKLTI0372', 'Lenovo L13', 'Laptop', '8', 'SSD 256', '10', 'OK', 'Tas, Adaptor, Converter LAN & VGA', NULL, '0000-00-00', '', 'EX PERIE TO ALEX CRD', ''),
(62, 'GD-R9', 'Ready To Assign', 'APP', 'IDWIKLTI0521', 'Lenovo L13', 'Laptop', '8', 'SSD 256', '11', 'OK', 'Tas, Adaptor, Converter LAN & VGA', NULL, '0000-00-00', '', '', ''),
(63, 'GD-R9', 'Ready To Assign', 'APP', 'IDWIKLTI0407', 'Lenovo L13', 'Laptop', '8', 'SSD 256', '11', 'OK', 'Tas, Adaptor, Converter VGA', NULL, '0000-00-00', '', '', ''),
(64, 'Loan', 'Loan', 'APP', 'IDWIKLTI0199', 'Lenovo L13', 'Laptop', '8', 'SSD 256', '11', 'OK', 'Tas, Adaptor, Converter LAN & VGA', NULL, '2025-10-01', '', 'ALFONS', 'IT'),
(65, 'GD-R13', 'Grace Period', 'APP', '8107184173NB', 'HP Probook 430 G3', 'Laptop', '12', 'SSD 256', '11', 'OK', 'Tas dan Adaptor', NULL, '2025-09-01', '', 'BOOKING BU DEWI UNTUK NEW JOINER', 'CPD'),
(66, 'Assign', 'Assign', 'APP', 'IDWIKLTI0025', 'Lenovo L390', 'Laptop', '8', 'SSD 512', '11', 'OK', 'Tas dan Adaptor', NULL, '2025-09-30', '1117043', 'CHRISTINA MARIA', 'CIT'),
(67, 'GD-R13', 'Grace Period', 'APP', '8107184531NB', 'Dell 3480', 'Laptop', '16', 'SSD 256', '10', 'EX AUDIT', 'Tas dan Adaptor', NULL, '2025-09-01', '', '', ''),
(68, 'Assign', 'Assign', 'APP', '8107184514NB', 'Dell 3480', 'Laptop', '16', 'SSD 256', '11', 'EX SPD', 'Tas dan Adaptor', NULL, '2025-10-08', '1153229', 'QESHA ANGGRAINI GEMINTANG', 'SPD'),
(69, 'Assign', 'Assign', 'APP', '8107184227NB', 'HP Probook 430 G3', 'Laptop', '12', 'SSD 256', '11', 'OK', '', NULL, '0000-00-00', '', 'KELLY ANWAR', 'CPD'),
(70, 'GD-R8', 'MT', 'APP', 'IDWIKLTI0264', 'HP Probook 430 G8', 'Laptop', '8', 'SSD 256', '11', 'BATERAI BOCOR', 'Tas dan Adaptor', NULL, '0000-00-00', '', '', ''),
(71, 'GD-R13', 'Grace Period', 'APP', 'IDWIKLTI0660', 'Lenovo L13', 'Laptop', '16', 'SSD 256', '11', 'OK', 'Tas, Adaptor, Converter LAN & VGA', NULL, '2025-09-01', '', '', ''),
(72, 'Assign', 'Assign', 'APP', 'IDSMLLTI0942', 'HP Elitebook 640 G10', 'Laptop', '8', 'SSD 256', '11', 'AKAN DIAMBIL TEAM IPS (BOBY)', 'Tas dan Adaptor', NULL, '0000-00-00', '', '', ''),
(73, 'GD-R13', 'Grace Period', 'APP', '8107184501NB', 'Dell 3480', 'Laptop', '8', 'HDD 500', '10', 'EX AUDIT', 'Tas dan Adaptor', NULL, '2025-09-01', '', '', ''),
(74, 'GD-R9', 'Ready To Assign', 'APP', 'IDWIKLTI0577', 'Lenovo L13', 'Laptop', '8', 'SSD 256', '11', 'ARROW KANAN TIDAK ADA', 'Tas, Adaptor, Converter VGA', NULL, '0000-00-00', '', 'ERLANGGA NUR ARIESTRA', 'IT'),
(75, 'GD-R13', 'Grace Period', 'APP', '8107184337NB', 'Dell 3480', 'Laptop', '8', 'SSD 256', '10', 'EX SPD MRO', 'Tas dan Adaptor', NULL, '2025-10-08', '1152560', 'FAESAL CAHYA AKBAR', 'SPD'),
(76, 'GD-R13', 'Grace Period', 'APP', '810705180012NB', 'Lenovo 13', 'Laptop', '8', 'SSD 256', '10', 'EX IAD', 'Tas dan Adaptor', NULL, '2025-10-02', '1134204', 'ANDRY A SUHERMAN', 'IAD');

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
  `finish_status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `admin_finish_id` int DEFAULT NULL,
  `admin_finish_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `finish_timestamp` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_list`
--

INSERT INTO `service_list` (`id_service`, `id_inventori`, `hostname`, `nama_user`, `divisi`, `tanggal_masuk`, `catatan`, `claim_status`, `admin_claim_id`, `admin_claim_name`, `claim_timestamp`, `current_admin_id`, `current_admin_name`, `finish_status`, `admin_finish_id`, `admin_finish_name`, `finish_timestamp`) VALUES
(9, 9, 'IDWIKLTI0352', 'OKTIVIAN MARPAUNG', 'CPD', '2025-10-22 09:09:35', 'Aset masuk service via scan mandiri.', 'On Service', 1, 'superadmin', NULL, 2, 'admin', 'Selesai', 2, 'admin', '2025-10-27 11:05:42'),
(10, 8, '8107184233NB', '', '', '2025-10-30 16:56:39', 'Aset masuk service via scan mandiri.', 'On Service', 2, 'admin', NULL, 2, 'admin', NULL, NULL, NULL, NULL),
(11, 21, 'IDWIKLTI0022', 'AFRIYANTO DWI PUTRA', 'SPD', '2025-10-30 17:05:31', 'Aset masuk service via scan mandiri.', 'On Service', 2, 'admin', NULL, 4, 'arisagustian', NULL, NULL, NULL, NULL);

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
-- Dumping data for table `validasi_aset`
--

INSERT INTO `validasi_aset` (`id`, `inventori_id`, `waktu_validasi`, `validator_nama`, `keterangan`) VALUES
(6, 8, '2025-10-07 10:58:08', 'admin', 'Valid'),
(7, 8, '2025-10-07 10:58:10', 'admin', 'Valid'),
(8, 8, '2025-10-07 10:58:13', 'admin', 'Valid'),
(9, 8, '2025-10-07 10:58:15', 'admin', 'Valid'),
(10, 8, '2025-10-07 10:58:25', 'admin', 'Valid'),
(11, 8, '2025-10-07 11:42:32', 'admin', 'Valid'),
(12, 8, '2025-10-07 11:42:38', 'admin', 'Valid'),
(13, 8, '2025-10-07 11:47:38', 'admin', 'Valid');

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `inventori`
--
ALTER TABLE `inventori`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `service_list`
--
ALTER TABLE `service_list`
  MODIFY `id_service` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
  ADD CONSTRAINT `histori_aset_ibfk_1` FOREIGN KEY (`inventori_id`) REFERENCES `inventori` (`id`);

--
-- Constraints for table `service_list`
--
ALTER TABLE `service_list`
  ADD CONSTRAINT `fk_inventori_service` FOREIGN KEY (`id_inventori`) REFERENCES `inventori` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `validasi_aset`
--
ALTER TABLE `validasi_aset`
  ADD CONSTRAINT `validasi_aset_ibfk_1` FOREIGN KEY (`inventori_id`) REFERENCES `inventori` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
