-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 30, 2024 at 03:03 PM
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
  `tanggal_input` datetime DEFAULT CURRENT_TIMESTAMP,
  `tanggal_selesai` datetime DEFAULT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `jenis_barang` varchar(50) DEFAULT NULL,
  `merk_barang` varchar(50) DEFAULT NULL,
  `keluhan_barang` text,
  `id_pelanggan` int DEFAULT NULL,
  `status` enum('Belum Diperbaiki','Sedang Diperbaiki','Selesai Diperbaiki','') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `status_updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ID_Service` int NOT NULL,
  `hubungi_kondisi` enum('Sudah','Belum') DEFAULT 'Belum',
  `hubungi_ambil` enum('Sudah','Belum') DEFAULT 'Belum',
  `diambil` enum('Sudah','Belum') DEFAULT 'Belum',
  `dibayar` enum('Sudah','Belum') DEFAULT 'Belum'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`tanggal_input`, `tanggal_selesai`, `nama_barang`, `jenis_barang`, `merk_barang`, `keluhan_barang`, `id_pelanggan`, `status`, `status_updated_at`, `ID_Service`, `hubungi_kondisi`, `hubungi_ambil`, `diambil`, `dibayar`) VALUES
('2024-06-23 06:30:05', NULL, 'Laptop', 'Pro Max', 'Asus', 'Layar burn in', 30, 'Selesai Diperbaiki', '2024-06-30 08:56:36', 43, 'Sudah', 'Sudah', 'Belum', 'Belum'),
('2024-06-25 18:43:30', NULL, 'Laptop', 'Zenbook', 'asus', 'TIba-tiba restart', 31, 'Belum Diperbaiki', '2024-06-30 08:47:43', 44, 'Belum', 'Belum', 'Belum', 'Belum'),
('2024-06-27 20:41:26', NULL, 'laptop', 'asfqef', 'asff', 'jgasdv', 32, 'Belum Diperbaiki', '2024-06-30 08:45:18', 45, 'Belum', 'Belum', 'Belum', 'Belum'),
('2024-06-29 01:00:35', NULL, 'asdfasf', 'asdaD', 'zxcadf', 'ASVAS', 31, 'Belum Diperbaiki', '2024-06-28 18:00:35', 46, 'Belum', 'Belum', 'Belum', 'Belum');

-- --------------------------------------------------------

--
-- Table structure for table `barang_keluar`
--

CREATE TABLE `barang_keluar` (
  `id_barang_keluar` int NOT NULL,
  `id_service` int DEFAULT NULL,
  `id_pelanggan` int DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `tanggal_masuk` datetime DEFAULT NULL,
  `tanggal_keluar` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barang_menumpuk`
--

CREATE TABLE `barang_menumpuk` (
  `id` int NOT NULL,
  `id_service` int NOT NULL,
  `tanggal_input` datetime NOT NULL,
  `id_pelanggan` int NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `merk_barang` varchar(50) NOT NULL,
  `kondisi` enum('bisa diperbaiki','tidak bisa diperbaiki') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detail_keluhan`
--

CREATE TABLE `detail_keluhan` (
  `id_keluhan` int NOT NULL,
  `id_barang` int DEFAULT NULL,
  `deskripsi` text,
  `keterangan_awal` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `konfirmasi_keterangan` enum('Eksekusi','Jangan Dieksekusi') NOT NULL,
  `keterangan_akhir` text NOT NULL,
  `id_user` int DEFAULT NULL,
  `kondisi` enum('bisa diperbaiki','tidak bisa diperbaiki') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_keluhan`
--

INSERT INTO `detail_keluhan` (`id_keluhan`, `id_barang`, `deskripsi`, `keterangan_awal`, `konfirmasi_keterangan`, `keterangan_akhir`, `id_user`, `kondisi`) VALUES
(2, 43, 'AMSMDMVJHVSD', 'POKOKNYA BEGINI NANTI', 'Jangan Dieksekusi', 'dfsdfdsv', 11, 'bisa diperbaiki'),
(3, 44, 'jkbsadhvavsdhvlshjkdvj', 'Sudah dianu', 'Jangan Dieksekusi', '', 11, 'tidak bisa diperbaiki');

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
(30, 'Doni', '08123456789', 'Jl. Al Khindi'),
(31, 'Doni', '082673245128', 'Jl. Al Khindi'),
(32, 'Doni', '08123456789', 'Jl. Al Khindi');

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
(11, 'kasir', 'kasir', '23523523', 'kasir', '$argon2id$v=19$m=65536,t=4,p=1$SUpJWGZXczNicWtGUVMudg$29I8heedPMOSMUyncdQke3dnf7WsrHQ7VKXXy56j8vw'),
(14, 'teknisi', 'teknisi', '23523523', 'teknisi', '$argon2id$v=19$m=65536,t=4,p=1$dU9lcnVUMzh5UnQzREpYdw$aVARW8VvqZ0JOdnMWjbii1BWV+JGdWtEShVneTc9XM8'),
(15, 'admin', 'admin', '23523523', 'teknisi', '$argon2id$v=19$m=65536,t=4,p=1$MDFmdTQ2c0QwNGVXUE1oVg$mugZ+S7TO14TSuJqSBQIfu0oevnr16oTWK0gqxc/CUw');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`ID_Service`),
  ADD KEY `id_pelanggan` (`id_pelanggan`);

--
-- Indexes for table `barang_keluar`
--
ALTER TABLE `barang_keluar`
  ADD PRIMARY KEY (`id_barang_keluar`),
  ADD KEY `id_pelanggan` (`id_pelanggan`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `barang_keluar_ibfk_1` (`id_service`);

--
-- Indexes for table `barang_menumpuk`
--
ALTER TABLE `barang_menumpuk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_id_service` (`id_service`),
  ADD KEY `fk_id_pelanggan` (`id_pelanggan`);

--
-- Indexes for table `detail_keluhan`
--
ALTER TABLE `detail_keluhan`
  ADD PRIMARY KEY (`id_keluhan`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `detail_keluhan_ibfk_1` (`id_barang`);

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
  MODIFY `ID_Service` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `barang_keluar`
--
ALTER TABLE `barang_keluar`
  MODIFY `id_barang_keluar` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `barang_menumpuk`
--
ALTER TABLE `barang_menumpuk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `detail_keluhan`
--
ALTER TABLE `detail_keluhan`
  MODIFY `id_keluhan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `rincian_keluhan`
--
ALTER TABLE `rincian_keluhan`
  MODIFY `id_rincian` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
  ADD CONSTRAINT `barang_keluar_ibfk_1` FOREIGN KEY (`id_service`) REFERENCES `barang` (`ID_Service`),
  ADD CONSTRAINT `barang_keluar_ibfk_2` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`),
  ADD CONSTRAINT `barang_keluar_ibfk_3` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

--
-- Constraints for table `barang_menumpuk`
--
ALTER TABLE `barang_menumpuk`
  ADD CONSTRAINT `fk_id_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`),
  ADD CONSTRAINT `fk_id_service` FOREIGN KEY (`id_service`) REFERENCES `barang` (`ID_Service`);

--
-- Constraints for table `detail_keluhan`
--
ALTER TABLE `detail_keluhan`
  ADD CONSTRAINT `detail_keluhan_ibfk_1` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`ID_Service`),
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
