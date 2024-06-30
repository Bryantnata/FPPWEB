-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 17, 2024 at 07:43 PM
-- Server version: 8.0.30
-- PHP Version: 8.3.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `laporservice`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `generate_rincian_id` ()   BEGIN
    DECLARE nextval INT;
    SET nextval = (SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rincian_keluhan');
    SET @new_id = CONCAT('rin', LPAD(nextval, 4, '0'));
    SELECT @new_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id_barang` int NOT NULL,
  `tanggal_input` datetime DEFAULT CURRENT_TIMESTAMP,
  `tanggal_selesai` datetime DEFAULT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `jenis_barang` varchar(50) DEFAULT NULL,
  `merk_barang` varchar(50) DEFAULT NULL,
  `keluhan_barang` text,
  `id_pelanggan` int DEFAULT NULL,
  `status` enum('Belum Diperbaiki','Sedang Diperbaiki','Selesai Diperbaiki','') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `status_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `tanggal_input`, `tanggal_selesai`, `nama_barang`, `jenis_barang`, `merk_barang`, `keluhan_barang`, `id_pelanggan`, `status`, `status_updated_at`) VALUES
(1, '2024-06-13 22:38:18', NULL, 'Laptop', 'Elektronik', 'Dell', 'Baterai cepat habis', 1, 'Selesai Diperbaiki', '2024-06-17 19:14:00'),
(14, '2024-06-17 21:27:26', NULL, 'djfhhvad', 'asnbas', 'zkdjbasd', 'asnmdad', 15, 'Sedang Diperbaiki', '2024-06-17 19:32:34'),
(15, '2024-06-18 01:39:30', NULL, 'hufasd', 'asdkjgad', 'asdhghsad', 'asdgiugsfa', 16, 'Sedang Diperbaiki', '2024-06-17 19:14:36'),
(16, '2024-06-18 01:46:13', NULL, 'sbsj,hc', 'ascnkbs', 'sdcmnm sac', 'sacmn asc', 17, 'Selesai Diperbaiki', '2024-06-17 19:27:34');

-- --------------------------------------------------------

--
-- Table structure for table `barang_keluar`
--

CREATE TABLE `barang_keluar` (
  `id_barang_keluar` int NOT NULL,
  `id_barang` int DEFAULT NULL,
  `id_pelanggan` int DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `tanggal_masuk` datetime DEFAULT NULL,
  `tanggal_keluar` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detail_keluhan`
--

CREATE TABLE `detail_keluhan` (
  `id_keluhan` int NOT NULL,
  `id_barang` int DEFAULT NULL,
  `deskripsi` text,
  `id_user` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_keluhan`
--

INSERT INTO `detail_keluhan` (`id_keluhan`, `id_barang`, `deskripsi`, `id_user`) VALUES
(1, 1, 'ganti baterai', 2);

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `alamat` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `nama`, `no_hp`, `alamat`) VALUES
(1, 'Doni', '08123456789', 'Jl. Khindi'),
(15, 'asdjsa', '891274', 'asdmn sd'),
(16, 'asddasdf', '123678142', 'safaf'),
(17, 'sadbjhwq', '8712836', 'asdkba');

-- --------------------------------------------------------

--
-- Table structure for table `rincian_keluhan`
--

CREATE TABLE `rincian_keluhan` (
  `id_rincian` int NOT NULL,
  `id_keluhan` int DEFAULT NULL,
  `jumlah` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tipe` varchar(50) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `total` decimal(10,2) GENERATED ALWAYS AS ((`jumlah` * `harga`)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `rincian_keluhan`
--

INSERT INTO `rincian_keluhan` (`id_rincian`, `id_keluhan`, `jumlah`, `nama`, `tipe`, `harga`) VALUES
(1, 1, 1, 'Baterai Laptop', 'Sparepart', 150000.00);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `role` enum('admin','kasir','teknisi') NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `nama`, `username`, `no_hp`, `role`, `password`) VALUES
(2, 'teknisi', 'teknisi', '08123456789', 'teknisi', 'teknisi'),
(6, 'kasir', 'kasir', '23523523', 'kasir', 'admin'),
(7, 'admin', 'admin', '23523523', 'admin', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`),
  ADD KEY `id_pelanggan` (`id_pelanggan`);

--
-- Indexes for table `barang_keluar`
--
ALTER TABLE `barang_keluar`
  ADD PRIMARY KEY (`id_barang_keluar`),
  ADD KEY `id_barang` (`id_barang`),
  ADD KEY `id_pelanggan` (`id_pelanggan`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `detail_keluhan`
--
ALTER TABLE `detail_keluhan`
  ADD PRIMARY KEY (`id_keluhan`),
  ADD KEY `id_barang` (`id_barang`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`);

--
-- Indexes for table `rincian_keluhan`
--
ALTER TABLE `rincian_keluhan`
  ADD PRIMARY KEY (`id_rincian`),
  ADD KEY `id_keluhan` (`id_keluhan`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id_barang` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `barang_keluar`
--
ALTER TABLE `barang_keluar`
  MODIFY `id_barang_keluar` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `detail_keluhan`
--
ALTER TABLE `detail_keluhan`
  MODIFY `id_keluhan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `rincian_keluhan`
--
ALTER TABLE `rincian_keluhan`
  MODIFY `id_rincian` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `barang_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`);

--
-- Constraints for table `barang_keluar`
--
ALTER TABLE `barang_keluar`
  ADD CONSTRAINT `barang_keluar_ibfk_1` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`),
  ADD CONSTRAINT `barang_keluar_ibfk_2` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`),
  ADD CONSTRAINT `barang_keluar_ibfk_3` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

--
-- Constraints for table `detail_keluhan`
--
ALTER TABLE `detail_keluhan`
  ADD CONSTRAINT `detail_keluhan_ibfk_1` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`),
  ADD CONSTRAINT `detail_keluhan_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

--
-- Constraints for table `rincian_keluhan`
--
ALTER TABLE `rincian_keluhan`
  ADD CONSTRAINT `rincian_keluhan_ibfk_1` FOREIGN KEY (`id_keluhan`) REFERENCES `detail_keluhan` (`id_keluhan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
