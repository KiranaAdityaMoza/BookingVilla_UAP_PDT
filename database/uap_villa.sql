-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 03, 2026 at 03:40 AM
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
-- Database: `uap_villa`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_manage_vila` (IN `action_type` VARCHAR(10), IN `p_id` VARCHAR(5), IN `p_nama` VARCHAR(100), IN `p_alamat` TEXT, IN `p_klaster` VARCHAR(10), IN `p_harga` DECIMAL(10,2), IN `p_status` VARCHAR(15))   BEGIN
    -- Operasi READ/SELECT
    IF action_type = 'SELECT' THEN
        SELECT * FROM vila;
        
    -- Operasi CREATE/INSERT
    ELSEIF action_type = 'INSERT' THEN
        INSERT INTO vila (id_vila, nama_vila, alamat, klaster, harga_per_malam, status)
        VALUES (p_id, p_nama, p_alamat, p_klaster, p_harga, p_status);
        
    -- Operasi UPDATE (Mengubah harga sewa dan status berdasarkan ID)
    ELSEIF action_type = 'UPDATE' THEN
        UPDATE vila 
        SET harga_per_malam = p_harga,
            status = p_status
        WHERE id_vila = p_id;
        
    -- Operasi DELETE
    ELSEIF action_type = 'DELETE' THEN
        DELETE FROM vila WHERE id_vila = p_id;
    END IF;
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `GetDiskonVila` (`total_biaya` DECIMAL(10,2)) RETURNS DECIMAL(10,2) DETERMINISTIC BEGIN
    DECLARE nilai_potongan DECIMAL(10,2) DEFAULT 0.00;
    -- Jika transaksi pemesanan di atas Rp 3.000.000, potong langsung 10%
    IF total_biaya > 3000000.00 THEN
        SET nilai_potongan = total_biaya * 0.10;
    END IF;
    RETURN nilai_potongan;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `id_booking` int NOT NULL,
  `id_customer` int NOT NULL,
  `id_vila` varchar(5) NOT NULL,
  `tgl_checkin` date NOT NULL,
  `durasi_malam` int NOT NULL,
  `total_harga_asli` decimal(10,2) NOT NULL,
  `potongan_diskon` decimal(10,2) NOT NULL,
  `total_bayar_bersih` decimal(10,2) NOT NULL,
  `status_booking` enum('Pending','Paid','Cancelled') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`id_booking`, `id_customer`, `id_vila`, `tgl_checkin`, `durasi_malam`, `total_harga_asli`, `potongan_diskon`, `total_bayar_bersih`, `status_booking`) VALUES
(6, 1, 'V02', '2026-06-06', 1, '750000.00', '0.00', '750000.00', 'Pending'),
(7, 1, 'V02', '2026-06-03', 1, '750000.00', '0.00', '750000.00', 'Pending'),
(8, 1, 'V02', '2026-06-20', 5, '3750000.00', '375000.00', '3375000.00', 'Pending');

--
-- Triggers `booking`
--
DELIMITER $$
CREATE TRIGGER `trigger_pembatalan_lunas` AFTER UPDATE ON `booking` FOR EACH ROW BEGIN
    -- Kondisi kritis: Jika uang sudah lunas masuk kas, tapi admin mendadak membatalkan sepihak
    IF OLD.status_booking = 'Paid' AND NEW.status_booking = 'Cancelled' THEN
        INSERT INTO log_pembatalan (id_booking, nama_customer, nominal_hangus, tgl_pencatatan)
        VALUES (
            OLD.id_booking, 
            (SELECT nama FROM customer WHERE id_customer = OLD.id_customer LIMIT 1), 
            OLD.total_bayar_bersih, 
            NOW()
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `id_customer` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`id_customer`, `nama`, `no_hp`) VALUES
(1, 'Kirana Aditya Moza', '08123456789'),
(2, 'Khaila Noverisya Nurdi', '08987654321'),
(3, 'Naura Azura grahyta', '088673561074');

-- --------------------------------------------------------

--
-- Table structure for table `log_pembatalan`
--

CREATE TABLE `log_pembatalan` (
  `id_log` int NOT NULL,
  `id_booking` int NOT NULL,
  `nama_customer` varchar(100) NOT NULL,
  `nominal_hangus` decimal(10,2) NOT NULL,
  `tgl_pencatatan` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') NOT NULL,
  `id_customer_fk` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `role`, `id_customer_fk`) VALUES
(1, 'admin', 'admin123', 'admin', NULL),
(2, 'kirana', 'kirana123', 'customer', 1),
(3, 'khaila', 'khaila123', 'customer', 2),
(4, 'naura', 'naura123', 'customer', 3);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_rekap_wilayah`
-- (See below for the actual view)
--
CREATE TABLE `view_rekap_wilayah` (
`id_booking` int
,`total_bayar_bersih` decimal(10,2)
,`wilayah_operasional` varchar(22)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_riwayat_reservasi`
-- (See below for the actual view)
--
CREATE TABLE `view_riwayat_reservasi` (
`id_booking` int
,`nama_customer_kapital` varchar(100)
,`nama_vila` varchar(100)
,`klaster` enum('Puncak','Pantai')
,`tgl_checkin` date
,`durasi_malam` int
,`total_bayar_bersih` decimal(10,2)
,`status_booking` enum('Pending','Paid','Cancelled')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_vila_pantai_aktif`
-- (See below for the actual view)
--
CREATE TABLE `view_vila_pantai_aktif` (
`id_vila` varchar(5)
,`nama_vila` varchar(100)
,`alamat` text
,`harga_per_malam` decimal(10,2)
,`status` enum('Tersedia','Disewa')
);

-- --------------------------------------------------------

--
-- Table structure for table `vila`
--

CREATE TABLE `vila` (
  `id_vila` varchar(5) NOT NULL,
  `nama_vila` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `klaster` enum('Puncak','Pantai') NOT NULL,
  `harga_per_malam` decimal(10,2) NOT NULL,
  `status` enum('Tersedia','Disewa') DEFAULT 'Tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `vila`
--

INSERT INTO `vila` (`id_vila`, `nama_vila`, `alamat`, `klaster`, `harga_per_malam`, `status`) VALUES
('V01', 'Villa Kabut Pinus', 'Jl. Raya Cisarua No. 88, Puncak, Bogor, Jawa Barat', 'Puncak', '750000.00', 'Tersedia'),
('V02', 'Villa Arunika Hills', 'Jl. Bukit Pelangi No. 15, Megamendung, Puncak, Jawa Barat', 'Puncak', '750000.00', 'Tersedia'),
('V03', 'Villa Panorama Puncak', 'Jl. Gunung Mas No. 27, Cisarua, Puncak, Jawa Barat', 'Puncak', '650000.00', 'Tersedia'),
('V04', 'Villa Skyview Retreat', 'Jl. Taman Safari No. 42, Cibeureum, Puncak, Jawa Barat', 'Puncak', '800000.00', 'Tersedia'),
('V05', 'Villa Pine Valley', 'Jl. Hutan Pinus No. 10, Megamendung, Puncak, Jawa Barat', 'Puncak', '700000.00', 'Tersedia'),
('V06', 'Villa Ombak Biru', 'Jl. Pantai Selatan No. 12, Anyer, Banten', 'Pantai', '1200000.00', 'Tersedia'),
('V07', 'Villa Sunset Cove', 'Jl. Karang Bolong No. 25, Anyer, Banten', 'Pantai', '1100000.00', 'Tersedia'),
('V08', 'Villa Pasir Mutiara', 'Jl. Pantai Indah No. 7, Lampung Selatan, Lampung', 'Pantai', '950000.00', 'Tersedia'),
('V09', 'Villa Sea Breeze', 'Jl. Teluk Harapan No. 18, Kalianda, Lampung', 'Pantai', '1000000.00', 'Tersedia'),
('V10', 'Villa Samudra Indah', 'Jl. Pesisir Bahari No. 33, Pesawaran, Lampung', 'Pantai', '900000.00', 'Tersedia');

-- --------------------------------------------------------

--
-- Structure for view `view_rekap_wilayah`
--
DROP TABLE IF EXISTS `view_rekap_wilayah`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_rekap_wilayah`  AS SELECT `booking`.`id_booking` AS `id_booking`, `booking`.`total_bayar_bersih` AS `total_bayar_bersih`, 'Klaster Premium Pantai' AS `wilayah_operasional` FROM `booking` WHERE `booking`.`id_vila` in (select `vila`.`id_vila` from `vila` where (`vila`.`klaster` = 'Pantai')) union all select `booking`.`id_booking` AS `id_booking`,`booking`.`total_bayar_bersih` AS `total_bayar_bersih`,'Klaster Reguler Puncak' AS `wilayah_operasional` from `booking` where `booking`.`id_vila` in (select `vila`.`id_vila` from `vila` where (`vila`.`klaster` = 'Puncak'))  ;

-- --------------------------------------------------------

--
-- Structure for view `view_riwayat_reservasi`
--
DROP TABLE IF EXISTS `view_riwayat_reservasi`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_riwayat_reservasi`  AS SELECT `b`.`id_booking` AS `id_booking`, upper(`c`.`nama`) AS `nama_customer_kapital`, `v`.`nama_vila` AS `nama_vila`, `v`.`klaster` AS `klaster`, `b`.`tgl_checkin` AS `tgl_checkin`, `b`.`durasi_malam` AS `durasi_malam`, `b`.`total_bayar_bersih` AS `total_bayar_bersih`, `b`.`status_booking` AS `status_booking` FROM ((`booking` `b` join `customer` `c` on((`b`.`id_customer` = `c`.`id_customer`))) join `vila` `v` on((`b`.`id_vila` = `v`.`id_vila`)))  ;

-- --------------------------------------------------------

--
-- Structure for view `view_vila_pantai_aktif`
--
DROP TABLE IF EXISTS `view_vila_pantai_aktif`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_vila_pantai_aktif`  AS SELECT `vila`.`id_vila` AS `id_vila`, `vila`.`nama_vila` AS `nama_vila`, `vila`.`alamat` AS `alamat`, `vila`.`harga_per_malam` AS `harga_per_malam`, `vila`.`status` AS `status` FROM `vila` WHERE ((`vila`.`klaster` = 'Pantai') AND (`vila`.`status` = 'Tersedia'))  ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id_booking`),
  ADD KEY `id_customer` (`id_customer`),
  ADD KEY `id_vila` (`id_vila`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id_customer`);

--
-- Indexes for table `log_pembatalan`
--
ALTER TABLE `log_pembatalan`
  ADD PRIMARY KEY (`id_log`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `id_customer_fk` (`id_customer_fk`);

--
-- Indexes for table `vila`
--
ALTER TABLE `vila`
  ADD PRIMARY KEY (`id_vila`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `id_booking` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `id_customer` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `log_pembatalan`
--
ALTER TABLE `log_pembatalan`
  MODIFY `id_log` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`id_customer`) REFERENCES `customer` (`id_customer`),
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`id_vila`) REFERENCES `vila` (`id_vila`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`id_customer_fk`) REFERENCES `customer` (`id_customer`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
