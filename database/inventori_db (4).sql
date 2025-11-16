-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 08, 2025 at 11:40 AM
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
-- Database: `inventori_db`
--

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
(1, 8, '2025-10-08 04:05:07', 'Dikembalikan', NULL, 'TES123', 'adadea', 'Diterima dalam kondisi baik'),
(2, 8, '2025-10-08 06:27:33', 'Dipakai', NULL, 'TES234', 'Adida', 'Dipakai untuk new joiner SPD'),
(3, 8, '2025-10-08 11:31:15', 'Dikembalikan', NULL, 'TES231', 'TES', 'TES'),
(4, 8, '2025-10-08 11:37:15', 'Diservis', 'Rabu', 'TES083', 'TES', 'TES');

-- --------------------------------------------------------

--
-- Table structure for table `inventori`
--

CREATE TABLE `inventori` (
  `id` int(11) NOT NULL,
  `rak` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `hostname` varchar(100) DEFAULT NULL,
  `ticket` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `ram` varchar(50) DEFAULT NULL,
  `storage` varchar(50) DEFAULT NULL,
  `win` varchar(50) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `kelengkapan` text DEFAULT NULL,
  `tanggal_keluar` date DEFAULT NULL,
  `nik` varchar(50) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `divisi` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventori`
--

INSERT INTO `inventori` (`id`, `rak`, `status`, `hostname`, `ticket`, `type`, `ram`, `storage`, `win`, `keterangan`, `kelengkapan`, `tanggal_keluar`, `nik`, `nama`, `divisi`) VALUES
(8, 'GD-R11', 'Spare', '8107184233NB', NULL, 'HP Probook 430 G3', '16', 'SSD 256', '11', 'OK', 'Tas dan Adaptor', '0000-00-00', '', '', ''),
(9, 'Assign', 'Assign', 'IDWIKLTI0352', NULL, 'Lenovo L13', '8', 'SSD 256', '11', 'OK', 'Adaptor', '2025-09-29', '1147570', 'OKTIVIAN MARPAUNG', 'CPD'),
(10, 'Assign', 'Assign', 'IDWIKLTI0126', NULL, 'Lenovo L390', '8', 'SSD 256', '11', 'OK', 'Tas, Adaptor, Converter LAN & VGA', '2025-10-01', '1153411', 'NATASHYA SUTANTO', 'CPD'),
(11, 'GD-R13', 'Grace Period', '8107184506NB', NULL, 'Dell 3480', '8', 'HDD 500', '10', 'EX AUDIT, LAYAR BERGARIS', 'Tas dan Adaptor', '2025-09-01', '', '', ''),
(12, 'GD-R13', 'Grace Period', '8107184507NB', NULL, 'Dell 3480', '16', 'SSD 256', '11', 'EX AUDIT', 'Tas dan Adaptor', '2025-09-01', '', '', ''),
(13, 'GD-R13', 'Grace Period', 'IDWIKLTI0495', NULL, 'Lenovo L13', '8', 'SSD 256', '11', 'OK', 'Tas, Adaptor, Converter LAN & VGA', '2025-09-01', '', '', ''),
(14, 'GD-R13', 'Grace Period', '910710180003NB', NULL, 'Lenovo L380', '8', 'SSD 256', '10', 'LEMOT, SLOW CHARGER', 'Tas dan Adaptor', '2025-06-01', '', '', ''),
(15, 'GD-R13', 'Grace Period', 'IDWIKLTI0408', NULL, 'Lenovo L13', '8', 'SSD 256', '', 'SSD RUSAK', 'Tas dan Adaptor', '2025-09-01', '', '', ''),
(16, 'GD-R13', 'Grace Period', 'IDWIKLTI0091', NULL, 'Lenovo L390', '8', 'SSD 256', '10', 'BACKUP DATA', 'Tas, Adaptor, Converter VGA', '2025-09-01', '', '', ''),
(17, 'GD-R13', 'Grace Period', '810708180081NB', NULL, 'Lenovo L380', '', '', '', '', 'Tas, Adaptor, Converter VGA', '2025-09-01', '', '', ''),
(18, 'GD-R13', 'Grace Period', 'IDWIKLTI0805', NULL, 'Dell 3430', '8', 'SSD 256', '11', 'KEYBOARD ERROR', 'Tas dan Adaptor', '2025-09-01', '', '', ''),
(19, 'GD-R13', 'Grace Period', '8107184466NB', NULL, 'Dell 3480', '8', 'SSD 256', '11', 'BATERAI BOCOR', 'Tas dan Adaptor', '2025-09-01', '', '', ''),
(20, 'GD-R13', 'Grace Period', '8107184078NB', NULL, 'HP Probook 430 G2', '16', 'SSD 256', '11', 'KIPAS TIDAK TERDETEKSI', 'Tas dan Adaptor', '2025-09-01', '', '', ''),
(21, 'Assign', 'Assign', 'IDWIKLTI0022', NULL, 'Lenovo L390', '8', 'SSD 256', '11', 'OK', 'Tas, Adaptor, Converter VGA', '2025-09-30', '1153449', 'AFRIYANTO DWI PUTRA', 'SPD'),
(22, 'GD-R12', 'Pending Service', 'IDWIKLTI0502', NULL, 'Lenovo L13', '8', 'SSD 256', '11', 'SPEAKER RUSAK', 'Tas, Adaptor, Converter LAN & VGA', '0000-00-00', '', '', ''),
(23, 'GD-R12', 'Pending Service', '8107184269NB', NULL, 'Dell 3470', '', '', '', 'BLANK TIDAK BISA HIDUP', 'Tas dan Adaptor', '0000-00-00', '', '', ''),
(38, 'GD-R12', 'Pending Service', '810710180133NB', NULL, 'Lenovo Thinkpad X1 Carbon', '8', 'SSD 256', '11', 'BATERAI BOCOR, CMOS ERROR', 'Tas, Adaptor, Converter LAN', '0000-00-00', '', '', ''),
(39, 'GD-R12', 'Pending Service', '810710180122NB', NULL, 'Lenovo L380', '8', 'SSD 256', '11', 'BATERAI BOCOR', 'Tas, Adaptor, Converter LAN', '0000-00-00', '', '', ''),
(40, 'Assign', 'Assign', '8107184801NB', NULL, 'HP Probook 430 G1', '12', 'HDD 500', '10', 'LEMOT', 'Tas dan Adaptor', '2025-09-30', '1055550', 'DEKI WITAMARA', 'CIT'),
(41, 'GD-R12', 'Pending Service', '8107184356NB', NULL, 'Dell 3480', '', '', '', 'LAYAR BLANK', 'Tas dan Adaptor', '0000-00-00', '', '', ''),
(42, 'GD-R12', 'Pending Service', '8107184441NB', NULL, 'Dell 3480', '', '', '', 'BATERAI BOCOR', 'Tas', '0000-00-00', '', '', ''),
(43, 'GD-R12', 'Pending Service', '8107184226NB', NULL, 'HP Probook 430 G3', '8', 'SSD 256', '11', 'BATERAI BOCOR', 'Tas dan Adaptor', '0000-00-00', '', '', ''),
(44, 'GD-R12', 'Pending Service', '8107184495NB', NULL, 'Dell 3480', '8', 'HDD 500', '10', 'LCD BERGARIS', 'Tas dan Adaptor', '0000-00-00', '', '', ''),
(45, 'GD-R12', 'Pending Service', '8107184530NB', NULL, 'Dell 3480', '', '', '', 'TIDAK NYALA', 'Tas, Adaptor, Converter VGA', '0000-00-00', '', '', ''),
(46, 'GD-R4', 'Scrap', '810705180042NB', NULL, 'Lenovo 13', '', '', '', '', 'Tas', '0000-00-00', '', '', ''),
(47, 'GD-R4', 'Scrap', 'IDWIKLTI0343', NULL, 'Lenovo L13', '', '', '11', '', 'Tas, Adaptor, Converter LAN & VGA', '0000-00-00', '', '', ''),
(48, 'GD-R4', 'Scrap', '8107184296NB', NULL, 'Lenovo 13', '', '', '', '', 'Tas', '0000-00-00', '', '', ''),
(49, 'GD-R12', 'Pending Service', '810707180052NB', NULL, 'Lenovo 13', '8', 'SSD 256', '11', 'ERROR RFID, LAYAR BERGARIS, BATERAI BOCOR', 'Tas dan Adaptor', '0000-00-00', '', '', ''),
(50, 'GD-R12', 'Pending Service', '8107184249NB', NULL, 'Dell 3470', '12', 'HDD 300', '10', 'LEMOT, BATERAI BOCOR', 'Tas dan Adaptor', '0000-00-00', '', '', ''),
(51, 'GD-R12', 'Pending Service', '8107184296NB', NULL, 'Dell 3470', '8', 'HDD 500', '10', 'BATERAI BOCOR', 'Tas dan Adaptor', '0000-00-00', '', '', ''),
(52, 'GD-R12', 'Pending Service', '8107164608NB', NULL, 'HP Probook 4340S', '8', 'HDD 300', '10', 'BATERAI BOCOR', 'Tas dan Adaptor', '0000-00-00', '', '', ''),
(53, 'GD-R12', 'Pending Service', '8107184308NB', NULL, 'Dell 3470', '8', 'HDD 500', '10', 'BATERAI BOCOR', 'Tas dan Adaptor', '0000-00-00', '', '', ''),
(54, 'GD-R9', 'Ready To Assign', '8107184324NB', NULL, 'Dell 3470', '8', 'HDD 500', '10', 'BATERAI BOCOR', 'Tas dan Adaptor', '0000-00-00', '', 'MASITA DINDA RIANI', ''),
(55, 'Assign', 'Assign', 'IDWIKLTI0756', NULL, 'Dell 3430', '', '', '11', 'OK', 'Tas', '2025-09-30', '1151554', 'VERHULST CLINHAERT', 'CPD'),
(56, 'GD-R9', 'Ready To Assign', 'IDWIKLTI0306', NULL, 'Lenovo L13', '8', 'SSD 256', '11', 'OK', 'Tas, Adaptor, Converter LAN & VGA', '0000-00-00', '', 'NATASYA SUSANTO', ''),
(57, 'GD-R9', 'Ready To Assign', '810711180146NB', NULL, 'Lenovo L380', '8', 'SSD 256', '11', 'OK', 'Tas dan Converter VGA', '0000-00-00', '', 'PIC FWI', ''),
(58, 'GD-R9', 'Ready To Assign', 'IDWIKLTI0491', NULL, 'Lenovo L13', '', '', '10', 'OK', 'Tas dan Adaptor', '0000-00-00', '', 'MUHAMMAD DAFFA PRATAMA', ''),
(59, 'GD-R9', 'Ready To Assign', '8107184321NB', NULL, 'Dell 3470', '', '', '', 'OK', 'Tas dan Adaptor', '0000-00-00', '', 'BAGOES HUDAYA KAMIL', ''),
(60, 'GD-R9', 'Ready To Assign', 'IDSMLLTI0657', NULL, 'Lenovo L13', '16', 'SSD 256', '10', 'OK', 'Tas dan Adaptor', '0000-00-00', '', 'EX VIVI TO TIARA CRD', ''),
(61, 'GD-R9', 'Ready To Assign', 'IDWIKLTI0372', NULL, 'Lenovo L13', '8', 'SSD 256', '10', 'OK', 'Tas, Adaptor, Converter LAN & VGA', '0000-00-00', '', 'EX PERIE TO ALEX CRD', ''),
(62, 'GD-R9', 'Ready To Assign', 'IDWIKLTI0521', NULL, 'Lenovo L13', '8', 'SSD 256', '11', 'OK', 'Tas, Adaptor, Converter LAN & VGA', '0000-00-00', '', '', ''),
(63, 'GD-R9', 'Ready To Assign', 'IDWIKLTI0407', NULL, 'Lenovo L13', '8', 'SSD 256', '11', 'OK', 'Tas, Adaptor, Converter VGA', '0000-00-00', '', '', ''),
(64, 'Loan', 'Loan', 'IDWIKLTI0199', NULL, 'Lenovo L13', '8', 'SSD 256', '11', 'OK', 'Tas, Adaptor, Converter LAN & VGA', '2025-10-01', '', 'ALFONS', 'IT'),
(65, 'GD-R13', 'Grace Period', '8107184173NB', NULL, 'HP Probook 430 G3', '12', 'SSD 256', '11', 'OK', 'Tas dan Adaptor', '2025-09-01', '', 'BOOKING BU DEWI UNTUK NEW JOINER', 'CPD'),
(66, 'Assign', 'Assign', 'IDWIKLTI0025', NULL, 'Lenovo L390', '8', 'SSD 512', '11', 'OK', 'Tas dan Adaptor', '2025-09-30', '1117043', 'CHRISTINA MARIA', 'CIT'),
(67, 'GD-R13', 'Grace Period', '8107184531NB', NULL, 'Dell 3480', '16', 'SSD 256', '10', 'EX AUDIT', 'Tas dan Adaptor', '2025-09-01', '', '', ''),
(68, 'Assign', 'Assign', '8107184514NB', NULL, 'Dell 3480', '16', 'SSD 256', '11', 'EX SPD', 'Tas dan Adaptor', '0000-00-00', '', 'QESHA ANGGRAINI GEMINTANG', 'SPD'),
(69, 'Assign', 'Assign', '8107184227NB', NULL, 'HP Probook 430 G3', '12', 'SSD 256', '11', 'OK', '', '0000-00-00', '', 'KELLY ANWAR', 'CPD'),
(70, 'GD-R8', 'MT', 'IDWIKLTI0264', NULL, 'HP Probook 430 G8', '8', 'SSD 256', '11', 'BATERAI BOCOR', 'Tas dan Adaptor', '0000-00-00', '', '', ''),
(71, 'GD-R13', 'Grace Period', 'IDWIKLTI0660', NULL, 'Lenovo L13', '16', 'SSD 256', '11', 'OK', 'Tas, Adaptor, Converter LAN & VGA', '2025-09-01', '', '', ''),
(72, 'Assign', 'Assign', 'IDSMLLTI0942', NULL, 'HP Elitebook 640 G10', '8', 'SSD 256', '11', 'AKAN DIAMBIL TEAM IPS (BOBY)', 'Tas dan Adaptor', '0000-00-00', '', '', ''),
(73, 'GD-R13', 'Grace Period', '8107184501NB', NULL, 'Dell 3480', '8', 'HDD 500', '10', 'EX AUDIT', 'Tas dan Adaptor', '2025-09-01', '', '', ''),
(74, 'GD-R9', 'Ready To Assign', 'IDWIKLTI0577', NULL, 'Lenovo L13', '8', 'SSD 256', '11', 'ARROW KANAN TIDAK ADA', 'Tas, Adaptor, Converter VGA', '0000-00-00', '', 'ERLANGGA NUR ARIESTRA', 'IT');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('normal','admin','superadmin') NOT NULL DEFAULT 'normal',
  `nama_lengkap` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `nama_lengkap`) VALUES
(1, 'superadmin', '$2y$10$AMNMoPfbW0sZSLgNR5aDkuk659h0hppWHXa97w9.GET8IgKkX5JMi', 'superadmin', 'Super Admin');

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
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

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
-- AUTO_INCREMENT for table `histori_aset`
--
ALTER TABLE `histori_aset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventori`
--
ALTER TABLE `inventori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  ADD CONSTRAINT `histori_aset_ibfk_1` FOREIGN KEY (`inventori_id`) REFERENCES `inventori` (`id`);

--
-- Constraints for table `validasi_aset`
--
ALTER TABLE `validasi_aset`
  ADD CONSTRAINT `validasi_aset_ibfk_1` FOREIGN KEY (`inventori_id`) REFERENCES `inventori` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
