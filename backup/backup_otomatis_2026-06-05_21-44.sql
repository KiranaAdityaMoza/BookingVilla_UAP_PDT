-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: uap_villa
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `booking`
--

DROP TABLE IF EXISTS `booking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `booking` (
  `id_booking` int NOT NULL AUTO_INCREMENT,
  `id_customer` int NOT NULL,
  `id_vila` varchar(5) NOT NULL,
  `tgl_checkin` date NOT NULL,
  `durasi_malam` int NOT NULL,
  `total_harga_asli` decimal(10,2) NOT NULL,
  `potongan_diskon` decimal(10,2) NOT NULL,
  `total_bayar_bersih` decimal(10,2) NOT NULL,
  `status_booking` enum('Pending','Paid','Cancelled') DEFAULT 'Pending',
  PRIMARY KEY (`id_booking`),
  KEY `id_customer` (`id_customer`),
  KEY `id_vila` (`id_vila`),
  CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`id_customer`) REFERENCES `customer` (`id_customer`),
  CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`id_vila`) REFERENCES `vila` (`id_vila`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking`
--

LOCK TABLES `booking` WRITE;
/*!40000 ALTER TABLE `booking` DISABLE KEYS */;
INSERT INTO `booking` VALUES (6,1,'V02','2026-06-06',1,750000.00,0.00,750000.00,'Pending'),(7,1,'V02','2026-06-03',1,750000.00,0.00,750000.00,'Pending'),(8,1,'V02','2026-06-20',5,3750000.00,375000.00,3375000.00,'Pending'),(9,2,'V01','2026-06-13',1,750000.00,0.00,750000.00,'Paid');
/*!40000 ALTER TABLE `booking` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trigger_pembatalan_lunas` AFTER UPDATE ON `booking` FOR EACH ROW BEGIN
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
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `customer`
--

DROP TABLE IF EXISTS `customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer` (
  `id_customer` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(15) NOT NULL,
  PRIMARY KEY (`id_customer`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer`
--

LOCK TABLES `customer` WRITE;
/*!40000 ALTER TABLE `customer` DISABLE KEYS */;
INSERT INTO `customer` VALUES (1,'Kirana Aditya Moza','08123456789'),(2,'Khaila Noverisya Nurdi','08987654321'),(3,'Naura Azura grahyta','088673561074');
/*!40000 ALTER TABLE `customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_pembatalan`
--

DROP TABLE IF EXISTS `log_pembatalan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_pembatalan` (
  `id_log` int NOT NULL AUTO_INCREMENT,
  `id_booking` int NOT NULL,
  `nama_customer` varchar(100) NOT NULL,
  `nominal_hangus` decimal(10,2) NOT NULL,
  `tgl_pencatatan` datetime NOT NULL,
  PRIMARY KEY (`id_log`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_pembatalan`
--

LOCK TABLES `log_pembatalan` WRITE;
/*!40000 ALTER TABLE `log_pembatalan` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_pembatalan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') NOT NULL,
  `id_customer_fk` int DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`),
  KEY `id_customer_fk` (`id_customer_fk`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`id_customer_fk`) REFERENCES `customer` (`id_customer`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin123','admin',NULL),(2,'kirana','kirana123','customer',1),(3,'khaila','khaila123','customer',2),(4,'naura','naura123','customer',3);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `view_rekap_wilayah`
--

DROP TABLE IF EXISTS `view_rekap_wilayah`;
/*!50001 DROP VIEW IF EXISTS `view_rekap_wilayah`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_rekap_wilayah` AS SELECT 
 1 AS `id_booking`,
 1 AS `total_bayar_bersih`,
 1 AS `wilayah_operasional`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_riwayat_reservasi`
--

DROP TABLE IF EXISTS `view_riwayat_reservasi`;
/*!50001 DROP VIEW IF EXISTS `view_riwayat_reservasi`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_riwayat_reservasi` AS SELECT 
 1 AS `id_booking`,
 1 AS `nama_customer_kapital`,
 1 AS `nama_vila`,
 1 AS `klaster`,
 1 AS `tgl_checkin`,
 1 AS `durasi_malam`,
 1 AS `total_bayar_bersih`,
 1 AS `status_booking`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_vila_pantai_aktif`
--

DROP TABLE IF EXISTS `view_vila_pantai_aktif`;
/*!50001 DROP VIEW IF EXISTS `view_vila_pantai_aktif`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_vila_pantai_aktif` AS SELECT 
 1 AS `id_vila`,
 1 AS `nama_vila`,
 1 AS `alamat`,
 1 AS `harga_per_malam`,
 1 AS `status`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_vila_puncak_aktif`
--

DROP TABLE IF EXISTS `view_vila_puncak_aktif`;
/*!50001 DROP VIEW IF EXISTS `view_vila_puncak_aktif`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_vila_puncak_aktif` AS SELECT 
 1 AS `id_vila`,
 1 AS `nama_vila`,
 1 AS `alamat`,
 1 AS `harga_per_malam`,
 1 AS `status`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `vila`
--

DROP TABLE IF EXISTS `vila`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vila` (
  `id_vila` varchar(5) NOT NULL,
  `nama_vila` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `klaster` enum('Puncak','Pantai') NOT NULL,
  `harga_per_malam` decimal(10,2) NOT NULL,
  `status` enum('Tersedia','Tidak Tersedia') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'Tersedia',
  PRIMARY KEY (`id_vila`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vila`
--

LOCK TABLES `vila` WRITE;
/*!40000 ALTER TABLE `vila` DISABLE KEYS */;
INSERT INTO `vila` VALUES ('V01','Villa Kabut Pinus','Jl. Raya Cisarua No. 88, Puncak, Bogor, Jawa Barat','Puncak',750000.00,'Tidak Tersedia'),('V02','Villa Arunika Hills','Jl. Bukit Pelangi No. 15, Megamendung, Puncak, Jawa Barat','Puncak',750000.00,'Tersedia'),('V03','Villa Panorama Puncak','Jl. Gunung Mas No. 27, Cisarua, Puncak, Jawa Barat','Puncak',650000.00,'Tersedia'),('V04','Villa Skyview Retreat','Jl. Taman Safari No. 42, Cibeureum, Puncak, Jawa Barat','Puncak',800000.00,'Tersedia'),('V05','Villa Pine Valley','Jl. Hutan Pinus No. 10, Megamendung, Puncak, Jawa Barat','Puncak',700000.00,'Tersedia'),('V06','Villa Ombak Biru','Jl. Pantai Selatan No. 12, Anyer, Banten','Pantai',1200000.00,'Tersedia'),('V07','Villa Sunset Cove','Jl. Karang Bolong No. 25, Anyer, Banten','Pantai',1100000.00,'Tersedia'),('V08','Villa Pasir Mutiara','Jl. Pantai Indah No. 7, Lampung Selatan, Lampung','Pantai',950000.00,'Tersedia'),('V09','Villa Sea Breeze','Jl. Teluk Harapan No. 18, Kalianda, Lampung','Pantai',1000000.00,'Tersedia'),('V10','Villa Samudra Indah','Jl. Pesisir Bahari No. 33, Pesawaran, Lampung','Pantai',900000.00,'Tersedia'),('V11','Villa Puncak Indah','Jl Puncak Indah No 12, Bogot, Jawa Barat','Puncak',100000.00,'Tersedia'),('V12','Villa Idaman','Jl Puncak Idaman No 15, Denpasar, Bali','Pantai',500000.00,'Tersedia');
/*!40000 ALTER TABLE `vila` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `view_rekap_wilayah`
--

/*!50001 DROP VIEW IF EXISTS `view_rekap_wilayah`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_rekap_wilayah` AS select `booking`.`id_booking` AS `id_booking`,`booking`.`total_bayar_bersih` AS `total_bayar_bersih`,'Klaster Premium Pantai' AS `wilayah_operasional` from `booking` where `booking`.`id_vila` in (select `vila`.`id_vila` from `vila` where (`vila`.`klaster` = 'Pantai')) union all select `booking`.`id_booking` AS `id_booking`,`booking`.`total_bayar_bersih` AS `total_bayar_bersih`,'Klaster Reguler Puncak' AS `wilayah_operasional` from `booking` where `booking`.`id_vila` in (select `vila`.`id_vila` from `vila` where (`vila`.`klaster` = 'Puncak')) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_riwayat_reservasi`
--

/*!50001 DROP VIEW IF EXISTS `view_riwayat_reservasi`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_riwayat_reservasi` AS select `b`.`id_booking` AS `id_booking`,upper(`c`.`nama`) AS `nama_customer_kapital`,`v`.`nama_vila` AS `nama_vila`,`v`.`klaster` AS `klaster`,`b`.`tgl_checkin` AS `tgl_checkin`,`b`.`durasi_malam` AS `durasi_malam`,`b`.`total_bayar_bersih` AS `total_bayar_bersih`,`b`.`status_booking` AS `status_booking` from ((`booking` `b` join `customer` `c` on((`b`.`id_customer` = `c`.`id_customer`))) join `vila` `v` on((`b`.`id_vila` = `v`.`id_vila`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_vila_pantai_aktif`
--

/*!50001 DROP VIEW IF EXISTS `view_vila_pantai_aktif`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_vila_pantai_aktif` AS select `vila`.`id_vila` AS `id_vila`,`vila`.`nama_vila` AS `nama_vila`,`vila`.`alamat` AS `alamat`,`vila`.`harga_per_malam` AS `harga_per_malam`,`vila`.`status` AS `status` from `vila` where ((`vila`.`klaster` = 'Pantai') and (`vila`.`status` = 'Tersedia')) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_vila_puncak_aktif`
--

/*!50001 DROP VIEW IF EXISTS `view_vila_puncak_aktif`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_vila_puncak_aktif` AS select `vila`.`id_vila` AS `id_vila`,`vila`.`nama_vila` AS `nama_vila`,`vila`.`alamat` AS `alamat`,`vila`.`harga_per_malam` AS `harga_per_malam`,`vila`.`status` AS `status` from `vila` where ((`vila`.`klaster` = 'Puncak') and (`vila`.`status` = 'Tersedia')) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-05 21:44:39
