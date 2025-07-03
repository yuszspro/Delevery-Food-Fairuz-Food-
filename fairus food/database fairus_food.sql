/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.13-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: znazvumr_fairus_food
-- ------------------------------------------------------
-- Server version	10.11.13-MariaDB-cll-lve

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `znazvumr_fairus_food`
--


--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(10) NOT NULL DEFAULT 'admin',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` (`id`, `username`, `email`, `phone`, `password`, `role`) VALUES (2,'admin','yuszkhi@gmail.com','0899999998','$2y$10$Gzp1s7fyVWyZruTpGaDbVeyJjCIixpWUV5LiwmWM3a7M5aKz0MQBC','admin');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat`
--

DROP TABLE IF EXISTS `chat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `pengirim_role` enum('user','admin') NOT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp(),
  `pesan` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `waktu_kirim` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `is_read_index` (`is_read`),
  CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat`
--

LOCK TABLES `chat` WRITE;
/*!40000 ALTER TABLE `chat` DISABLE KEYS */;
INSERT INTO `chat` (`id`, `id_user`, `pengirim_role`, `waktu`, `pesan`, `is_read`, `waktu_kirim`) VALUES (46,3,'user','2025-06-26 14:35:06','halo bang',1,'2025-06-26 14:35:06'),
(47,3,'user','2025-06-26 14:35:17','ada diskon pengguna pertama ga',1,'2025-06-26 14:35:17'),
(48,3,'admin','2025-06-26 14:35:41','ada',1,'2025-06-26 14:35:41'),
(49,3,'admin','2025-06-26 14:35:51','silahkan ke profil',1,'2025-06-26 14:35:51'),
(50,3,'user','2025-06-26 14:37:59','mabar epep',1,'2025-06-26 14:37:59'),
(51,3,'admin','2025-06-26 14:38:17','gasinn',0,'2025-06-26 14:38:17'),
(52,4,'user','2025-06-26 15:04:02','hi min',1,'2025-06-26 15:04:02'),
(53,6,'user','2025-06-27 08:42:27','bang',1,'2025-06-27 08:42:27'),
(54,6,'user','2025-06-27 08:42:36','bang',1,'2025-06-27 08:42:36'),
(55,6,'user','2025-06-27 08:45:43','bales dong',1,'2025-06-27 08:45:43'),
(56,4,'user','2025-06-27 13:43:56','oi min',1,'2025-06-27 13:43:56'),
(57,5,'user','2025-06-28 09:05:00','minn',1,'2025-06-28 09:05:00'),
(58,5,'user','2025-06-28 09:05:07','pesanan saya gimana',1,'2025-06-28 09:05:07'),
(59,6,'admin','2025-06-28 09:05:55','apa sayang',0,'2025-06-28 09:05:55'),
(60,5,'admin','2025-06-28 09:06:11','lagi di proses ya kak,mohon di tunggu',0,'2025-06-28 09:06:11'),
(63,4,'admin','2025-06-29 08:01:25','apa jawa',1,'2025-06-29 08:01:25'),
(64,4,'admin','2025-06-29 08:12:54','hmmm',1,'2025-06-29 08:12:54'),
(65,4,'admin','2025-06-29 08:14:34','hmmm',1,'2025-06-29 08:14:34'),
(66,4,'admin','2025-06-29 08:16:33','hmm',1,'2025-06-29 08:16:33'),
(67,4,'admin','2025-06-29 08:21:52','apaan bang',1,'2025-06-29 08:21:52'),
(68,4,'admin','2025-06-29 08:21:54','jawab bang',1,'2025-06-29 08:21:54'),
(69,4,'admin','2025-06-29 08:23:00','ppp',1,'2025-06-29 08:23:00'),
(70,4,'admin','2025-06-29 08:32:15','jawa',1,'2025-06-29 08:32:15'),
(71,4,'admin','2025-06-29 08:32:16','jawa',1,'2025-06-29 08:32:16'),
(72,4,'admin','2025-06-29 08:32:17','jawa',1,'2025-06-29 08:32:17');
/*!40000 ALTER TABLE `chat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detail_transaksi`
--

DROP TABLE IF EXISTS `detail_transaksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `detail_transaksi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_transaksi` int(11) NOT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `nama_produk_saat_transaksi` varchar(255) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_saat_transaksi` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_transaksi` (`id_transaksi`),
  KEY `id_produk` (`id_produk`),
  CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_detail_transaksi_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_transaksi`
--

LOCK TABLES `detail_transaksi` WRITE;
/*!40000 ALTER TABLE `detail_transaksi` DISABLE KEYS */;
INSERT INTO `detail_transaksi` (`id`, `id_transaksi`, `id_produk`, `nama_produk_saat_transaksi`, `jumlah`, `harga_saat_transaksi`) VALUES (45,38,16,'\"Bucket FF\"',1,110000),
(46,39,16,'\"Bucket FF\"',3,110000),
(47,40,26,'\"Teh Hijau (Teh Tangsel) x Fairuz Food\"',1,8000),
(48,41,16,'\"Bucket FF\"',1,110000),
(49,41,25,'\"Es Jeruk\"',1,4000),
(50,41,15,'\"Paket Combo FF\"',1,20000),
(51,42,15,'\"Paket Combo FF\"',1,20000),
(52,43,15,'\"Paket Combo FF\"',2,20000),
(53,43,26,'\"Teh Hijau (Teh Tangsel) x Fairuz Food\"',1,8000),
(54,43,25,'\"Es Jeruk\"',1,4000),
(55,44,16,'\"Bucket FF\"',1,110000),
(56,44,17,'\"Nasi\"',3,2000),
(57,45,25,'\"Es Jeruk\"',1,4000),
(58,45,23,'\"Es Teh\"',1,2000),
(59,45,24,'\"Es Coca-cola\"',1,10000),
(60,45,21,'\"Dada\"',1,8000),
(61,45,20,'\"Sayap\"',1,8000),
(62,45,18,'\"Paha Atas\"',3,8000),
(63,45,19,'\"Paha Bawah\"',1,9000),
(64,45,17,'\"Nasi\"',2,2000),
(65,45,22,'\"Kentang Goreng\"',3,10000),
(66,45,26,'\"Teh Hijau (Teh Tangsel) x Fairuz Food\"',1,8000);
/*!40000 ALTER TABLE `detail_transaksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kategori`
--

DROP TABLE IF EXISTS `kategori`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kategori` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kategori`
--

LOCK TABLES `kategori` WRITE;
/*!40000 ALTER TABLE `kategori` DISABLE KEYS */;
INSERT INTO `kategori` (`id`, `nama_kategori`) VALUES (1,'Makanan'),
(2,'Minuman'),
(3,'Cemilan'),
(4,'Paket Hemat');
/*!40000 ALTER TABLE `kategori` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `keranjang`
--

DROP TABLE IF EXISTS `keranjang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `keranjang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_produk` (`id_produk`),
  CONSTRAINT `keranjang_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `keranjang_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `keranjang`
--

LOCK TABLES `keranjang` WRITE;
/*!40000 ALTER TABLE `keranjang` DISABLE KEYS */;
/*!40000 ALTER TABLE `keranjang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produk`
--

DROP TABLE IF EXISTS `produk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `produk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `harga` int(11) NOT NULL,
  `harga_diskon` int(11) DEFAULT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_kategori` (`id_kategori`),
  CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produk`
--

LOCK TABLES `produk` WRITE;
/*!40000 ALTER TABLE `produk` DISABLE KEYS */;
INSERT INTO `produk` (`id`, `nama`, `harga`, `harga_diskon`, `id_kategori`, `deskripsi`, `gambar`) VALUES (15,'Paket Combo FF',24000,20000,4,'Nasi, Chiken (Sayap,Dada), Es Teh','1750948344_Paket-Combo.png'),
(16,'Bucket FF',110000,NULL,1,'Family Friendly','1750948389_baketfff.jpg'),
(17,'Nasi',2000,NULL,1,'FF Juara','1750948623_nasi.jpg'),
(18,'Paha Atas',8000,NULL,1,'FF Juara','1750948733_paha atas.jpg'),
(19,'Paha Bawah',9000,NULL,1,'FF Juara','1750948800_paha bawah.jpg'),
(20,'Sayap',8000,NULL,1,'FF Juara','1750948826_sayap.jpg'),
(21,'Dada',8000,NULL,1,'FF Juara','1750983230_dada.jpg'),
(22,'Kentang Goreng',10000,NULL,3,'FF Juara','1750986763_kentang goreng.jpg'),
(23,'Es Teh',2000,NULL,2,'FF Juara','1750986647_ess teh.jpg'),
(24,'Es Coca-cola',10000,NULL,2,'FF Juara','1750984320_es coca cola.jpg'),
(25,'Es Jeruk',4000,NULL,2,'FF Juara','1750986569_es jeruk.jpg'),
(26,'Teh Hijau (Teh Tangsel) x Fairuz Food',12000,8000,2,'COLLAB PRODUCT','1750986879_teh tangsel.jpg');
/*!40000 ALTER TABLE `produk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `riwayat`
--

DROP TABLE IF EXISTS `riwayat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `riwayat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `aktifitas` varchar(255) NOT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `riwayat_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `riwayat`
--

LOCK TABLES `riwayat` WRITE;
/*!40000 ALTER TABLE `riwayat` DISABLE KEYS */;
INSERT INTO `riwayat` (`id`, `id_user`, `aktifitas`, `waktu`) VALUES (40,3,'Status pesanan #FF-FC712D8D diubah menjadi \'Selesai\'','2025-06-26 14:37:40'),
(41,3,'Status pesanan #FF-09832FFA diubah menjadi \'Dikirim\'','2025-06-26 14:41:39'),
(42,3,'Status pesanan #FF-09832FFA diubah menjadi \'Selesai\'','2025-06-26 14:41:40'),
(43,4,'Status pesanan #FF-174136FF diubah menjadi \'Selesai\'','2025-06-27 01:58:39'),
(44,5,'Status pesanan #FF-05DC2CC8 diubah menjadi \'Dikirim\'','2025-06-28 09:06:31'),
(45,5,'Status pesanan #FF-05DC2CC8 diubah menjadi \'Dibatalkan\'','2025-06-28 09:06:37'),
(46,5,'Status pesanan #FF-05DC2CC8 diubah menjadi \'Selesai\'','2025-06-28 09:06:42');
/*!40000 ALTER TABLE `riwayat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaksi`
--

DROP TABLE IF EXISTS `transaksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `total_harga` int(11) NOT NULL,
  `potongan_harga` int(11) DEFAULT 0,
  `id_voucher_terpakai` int(11) DEFAULT NULL,
  `kode_voucher_terpakai` varchar(50) DEFAULT NULL,
  `metode_pembayaran` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Menunggu Pembayaran',
  `tanggal_transaksi` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi`
--

LOCK TABLES `transaksi` WRITE;
/*!40000 ALTER TABLE `transaksi` DISABLE KEYS */;
INSERT INTO `transaksi` (`id`, `id_user`, `total_harga`, `potongan_harga`, `id_voucher_terpakai`, `kode_voucher_terpakai`, `metode_pembayaran`, `status`, `tanggal_transaksi`) VALUES (38,3,110000,0,NULL,NULL,'DANA','Selesai','2025-06-26 14:37:03'),
(39,3,322000,8000,1,'DISKON8RB','DANA','Selesai','2025-06-26 14:40:00'),
(40,4,8000,0,NULL,NULL,'COD','Selesai','2025-06-27 01:58:01'),
(41,4,125000,9000,2,'DIKSKONHAYYUK','Debit - CIMB Niaga','Diproses','2025-06-27 05:04:38'),
(42,6,20000,0,NULL,NULL,'COD','Diproses','2025-06-27 08:42:15'),
(43,5,52000,0,NULL,NULL,'Debit - BRI','Selesai','2025-06-28 09:04:16'),
(44,4,108000,8000,1,'DISKON8RB','Debit - BCA','Diproses','2025-06-29 09:33:41'),
(45,4,107000,0,NULL,NULL,'COD','Diproses','2025-06-29 09:37:05');
/*!40000 ALTER TABLE `transaksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_vouchers`
--

DROP TABLE IF EXISTS `user_vouchers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_vouchers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_voucher` int(11) NOT NULL,
  `status` enum('tersedia','terpakai') NOT NULL DEFAULT 'tersedia',
  `tanggal_klaim` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_voucher` (`id_voucher`),
  CONSTRAINT `user_vouchers_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_vouchers_ibfk_2` FOREIGN KEY (`id_voucher`) REFERENCES `vouchers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_vouchers`
--

LOCK TABLES `user_vouchers` WRITE;
/*!40000 ALTER TABLE `user_vouchers` DISABLE KEYS */;
INSERT INTO `user_vouchers` (`id`, `id_user`, `id_voucher`, `status`, `tanggal_klaim`) VALUES (14,3,1,'terpakai','2025-06-26 14:38:18'),
(15,3,2,'tersedia','2025-06-26 14:38:20'),
(16,4,2,'terpakai','2025-06-26 15:02:56'),
(17,4,1,'terpakai','2025-06-26 15:58:52'),
(18,6,1,'tersedia','2025-06-27 08:41:38'),
(19,6,2,'tersedia','2025-06-27 08:41:38');
/*!40000 ALTER TABLE `user_vouchers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(10) NOT NULL DEFAULT 'user',
  `foto_profil` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `alamat_lat` double DEFAULT NULL,
  `alamat_lng` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `username`, `email`, `phone`, `password`, `role`, `foto_profil`, `alamat`, `alamat_lat`, `alamat_lng`) VALUES (2,'wadehel','wadehel@oyoy.com','081123287313','$2y$10$fZuebYs65yK4xn4yJtbdUex2Uo7oLvfgpPKM8TJWgooAjdMsiwaW2','user',NULL,NULL,NULL,NULL),
(3,'aduhai','aduhai@aduhabang.com','08123456789','$2y$10$tov1vvKhc/O5uk4rEYbJleaVA2Gnc2pN6WV37bpUkbTDbgCLsO7je','user',NULL,'Kebon Pisang, Sumur Bandung, Kota Bandung, Jawa Barat, Jawa, 40112, Indonesia',-6.917536229095981,107.61928081512453),
(4,'pincent','fairussuhair@gmail.com','088909987987','$2y$10$SdB4KkxgOqzyoOPbdiDEc.jyF2eLDF/NvQbUzZ/GLSsgyeh1K4So2','user',NULL,'Sikampuh, Cilacap, Jawa Tengah, Jawa, Indonesia',-7.621704349445665,109.1988593734166),
(5,'apaaja','yahhahayuk@gmail.com','0999999999','$2y$10$Sh.25e8M5wRV3Eq4eoXoEex5wgXi2DRvScsdu4eFr/UOYvrEyliXO','user',NULL,'Sikampuh, Cilacap, Jawa Tengah, Jawa, Indonesia',-7.624212066917151,109.19791422357343),
(6,'bebek15','bebek@gmail.com','085728950496','$2y$10$HLNVrA5b5FT2yifMBLjvV.uCIQ1zo0Z4kMJbCJs.S6MeaSYhBAj5y','user','1751013834_IMG-20250410-WA0015.jpg','Kebon Pisang, Sumur Bandung, Kota Bandung, Jawa Barat, Jawa, 40112, Indonesia',-6.9175,107.6191);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vouchers`
--

DROP TABLE IF EXISTS `vouchers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vouchers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_voucher` varchar(50) NOT NULL,
  `deskripsi` text NOT NULL,
  `potongan_harga` int(11) NOT NULL,
  `min_pembelian` int(11) NOT NULL,
  `berlaku_hingga` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_voucher` (`kode_voucher`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vouchers`
--

LOCK TABLES `vouchers` WRITE;
/*!40000 ALTER TABLE `vouchers` DISABLE KEYS */;
INSERT INTO `vouchers` (`id`, `kode_voucher`, `deskripsi`, `potongan_harga`, `min_pembelian`, `berlaku_hingga`) VALUES (1,'DISKON8RB','Diskon Spesial Rp 8.000',8000,40000,'2025-12-31'),
(2,'DIKSKONHAYYUK','diskon spesial ff rillis',9000,33000,'2025-12-31');
/*!40000 ALTER TABLE `vouchers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'znazvumr_fairus_food'
--

--
-- Dumping routines for database 'znazvumr_fairus_food'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-29 15:21:39
