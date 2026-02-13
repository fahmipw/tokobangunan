-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 02, 2026 at 03:12 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `toko_bangunan`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kecamatan`
--

CREATE TABLE `kecamatan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kode_wilayah` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kecamatan`
--

INSERT INTO `kecamatan` (`id`, `nama`, `kode_wilayah`) VALUES
(1, 'Pamulang', NULL),
(2, 'Ciputat', NULL),
(3, 'Serpong', 10),
(4, 'Cilandak', NULL),
(5, 'Pondok Cabe', NULL),
(6, 'Karawaci', 12),
(7, 'jad', NULL),
(8, 'Parung', NULL),
(9, 'Cisauk', NULL),
(10, 'Parung Panjang', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pengiriman`
--

CREATE TABLE `pengiriman` (
  `id` int(11) NOT NULL,
  `surat_jalan_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `toko_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengiriman`
--

INSERT INTO `pengiriman` (`id`, `surat_jalan_id`, `produk_id`, `toko_id`, `jumlah`, `harga`) VALUES
(166, 166, 12, 41, 5, '200.00'),
(167, 167, 12, 37, 20, '500000.00'),
(168, 168, 11, 31, 20, '55000.00'),
(169, 169, 12, 43, 50, '55000.00'),
(170, 170, 14, 32, 20, '2000.00'),
(171, 171, 16, 27, 20, '60000.00'),
(172, 172, 11, 27, 20, '55000.00'),
(173, 173, 14, 27, 12, '60000.00'),
(174, 174, 11, 34, 10, '3000000.00'),
(175, 174, 15, 34, 15, '500000.00'),
(176, 175, 11, 42, 2, '500000.00'),
(177, 175, 15, 42, 5, '300000.00');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `stok` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `nama`, `stok`) VALUES
(11, 'SKIMCOAT LM 202', 287),
(12, 'THINBED LM 306', 159),
(13, 'TILE ON TILE ADHESIVE LM 606', 51),
(14, 'TILE ADHESIVE LM 601', 84),
(15, 'THINBED LM 301', 236),
(16, 'SKIMCOAT LM 200', 225),
(17, 'BASE PLASTER LM 101', 130);

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_stok`
--

CREATE TABLE `riwayat_stok` (
  `id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `waktu` datetime NOT NULL,
  `stok_lama` int(11) NOT NULL,
  `stok_baru` int(11) NOT NULL,
  `jenis` varchar(50) NOT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `riwayat_stok`
--

INSERT INTO `riwayat_stok` (`id`, `produk_id`, `waktu`, `stok_lama`, `stok_baru`, `jenis`, `keterangan`) VALUES
(1, 12, '2025-10-14 00:26:27', 4, 3, 'stok_opname', 'Stok opname, stok diubah dari 4 menjadi 3'),
(2, 12, '2025-10-14 00:28:46', 3, 5, 'stok_opname', 'Stok opname, stok diubah dari 3 menjadi 5'),
(3, 12, '2025-10-14 00:36:43', 5, 8, 'barang masuk', 'Barang masuk produk ID 12, qty 3'),
(4, 12, '2025-10-14 00:36:50', 8, 6, 'barang keluar', 'Barang keluar produk ID 12, qty 2'),
(5, 12, '2025-10-14 00:37:09', 6, 5, 'stok opname', 'Stok opname produk ID 12'),
(6, 13, '2025-10-14 00:37:09', 5, 7, 'stok opname', 'Stok opname produk ID 13'),
(7, 11, '2025-10-14 00:41:09', 1, 2, 'stok opname', 'Stok opname produk ID 11'),
(8, 11, '2025-10-14 00:41:17', 2, 3, 'barang masuk', 'Barang masuk produk ID 11, qty 1'),
(14, 11, '2025-10-14 01:33:04', 3, 1, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 2'),
(15, 11, '2025-10-14 01:36:55', 1, 10, 'stok opname', 'Stok opname produk ID 11'),
(16, 11, '2025-10-14 01:39:33', 10, 5, 'pengiriman', 'Pengiriman ke toko ID 34, jumlah 5'),
(17, 11, '2025-10-14 02:10:27', 5, 9, 'barang masuk', 'Barang masuk produk ID 11, qty 4'),
(18, 13, '2025-10-14 02:17:23', 7, 2, 'barang keluar', 'Barang keluar produk ID 13, qty 5'),
(19, 11, '2025-10-14 02:21:48', 9, 7, 'pengiriman', 'Pengiriman ke toko ID 34, jumlah 2'),
(20, 11, '2025-10-14 02:22:27', 7, 4, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 3'),
(21, 11, '2025-10-14 02:57:37', 4, 2, 'pengiriman', 'Pengiriman ke toko ID 28, jumlah 2'),
(22, 12, '2025-10-14 02:58:26', 5, 4, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 1'),
(23, 17, '2025-10-14 02:58:44', 5, 4, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 1'),
(24, 17, '2025-10-14 02:59:05', 4, 3, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 1'),
(25, 12, '2025-10-14 03:04:50', 4, 2, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 2'),
(26, 11, '2025-10-14 03:17:14', 2, 102, 'barang masuk', 'Barang masuk produk ID 11, qty 100'),
(27, 12, '2025-10-14 03:17:14', 2, 102, 'barang masuk', 'Barang masuk produk ID 12, qty 100'),
(28, 13, '2025-10-14 03:17:14', 2, 102, 'barang masuk', 'Barang masuk produk ID 13, qty 100'),
(29, 14, '2025-10-14 03:17:14', 0, 100, 'barang masuk', 'Barang masuk produk ID 14, qty 100'),
(30, 15, '2025-10-14 03:17:14', 0, 100, 'barang masuk', 'Barang masuk produk ID 15, qty 100'),
(31, 16, '2025-10-14 03:17:14', 0, 100, 'barang masuk', 'Barang masuk produk ID 16, qty 100'),
(32, 17, '2025-10-14 03:17:14', 3, 103, 'barang masuk', 'Barang masuk produk ID 17, qty 100'),
(33, 15, '2025-10-14 03:18:53', 100, 80, 'pengiriman', 'Pengiriman ke toko ID 34, jumlah 20'),
(34, 16, '2025-10-14 03:19:17', 100, 98, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 2'),
(35, 15, '2025-10-14 03:25:55', 80, 78, 'pengiriman', 'Pengiriman ke toko ID 28, jumlah 2'),
(36, 12, '2025-10-14 03:26:15', 102, 82, 'pengiriman', 'Pengiriman ke toko ID 28, jumlah 20'),
(37, 12, '2025-10-14 04:11:44', 82, 60, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 22'),
(38, 13, '2025-10-16 12:50:06', 102, 82, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 20'),
(39, 16, '2025-10-16 12:50:22', 98, 78, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 20'),
(40, 11, '2025-10-20 19:29:18', 102, 302, 'barang masuk', 'Barang masuk produk ID 11, qty 200'),
(41, 11, '2025-10-20 19:29:29', 302, 2, 'barang keluar', 'Barang keluar produk ID 11, qty 300'),
(42, 12, '2025-10-20 19:31:35', 60, 50, 'pengiriman', 'Pengiriman ke toko ID 28, jumlah 10'),
(43, 13, '2025-10-20 19:32:34', 82, 72, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 10'),
(44, 14, '2025-10-20 19:33:55', 100, 78, 'pengiriman', 'Pengiriman ke toko ID 29, jumlah 22'),
(45, 15, '2025-10-20 19:34:48', 78, 45, 'pengiriman', 'Pengiriman ke toko ID 28, jumlah 33'),
(47, 12, '2025-10-20 20:17:51', 50, 10, 'pengiriman', 'Pengiriman ke toko ID 28, jumlah 40'),
(48, 13, '2025-10-20 20:19:23', 72, 62, 'pengiriman', 'Pengiriman ke toko ID 28, jumlah 10'),
(49, 16, '2025-10-20 20:19:49', 78, 68, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 10'),
(50, 16, '2025-10-20 20:20:43', 68, 48, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 20'),
(51, 15, '2025-10-20 20:21:15', 45, 15, 'pengiriman', 'Pengiriman ke toko ID 34, jumlah 30'),
(52, 13, '2025-10-20 20:29:46', 62, 57, 'pengiriman', 'Pengiriman ke toko ID 36, jumlah 5'),
(53, 14, '2025-10-20 20:30:19', 78, 73, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 5'),
(54, 14, '2025-10-20 20:31:11', 73, 71, 'pengiriman', 'Pengiriman ke toko ID 36, jumlah 2'),
(55, 14, '2025-10-20 20:33:48', 71, 69, 'pengiriman', 'Pengiriman ke toko ID 28, jumlah 2'),
(56, 13, '2025-10-20 20:34:14', 57, 55, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 2'),
(64, 14, '2025-10-20 20:51:45', 69, 67, 'pengiriman', 'Pengiriman ke toko ID 36, jumlah 2'),
(65, 12, '2025-10-20 21:23:09', 10, 9, 'pengiriman', 'Pengiriman ke toko ID 36, jumlah 1'),
(66, 17, '2025-10-20 21:23:35', 103, 102, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 1'),
(67, 13, '2025-10-20 21:24:13', 55, 54, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 1'),
(68, 14, '2025-10-20 21:25:11', 67, 66, 'pengiriman', 'Pengiriman ke toko ID 35, jumlah 1'),
(69, 14, '2025-10-20 21:32:41', 66, 65, 'pengiriman', 'Pengiriman ke toko ID 36, jumlah 1'),
(70, 14, '2025-10-20 21:33:08', 65, 60, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 5'),
(71, 15, '2025-10-20 21:33:31', 15, 5, 'pengiriman', 'Pengiriman ke toko ID 35, jumlah 10'),
(72, 17, '2025-10-20 21:39:14', 102, 97, 'pengiriman', 'Pengiriman ke toko ID 35, jumlah 5'),
(73, 17, '2025-10-20 21:39:42', 97, 87, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 10'),
(74, 14, '2025-10-20 21:51:19', 60, 55, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 5'),
(75, 16, '2025-10-20 21:51:47', 48, 47, 'pengiriman', 'Pengiriman ke toko ID 36, jumlah 1'),
(76, 13, '2025-10-20 21:59:04', 54, 52, 'pengiriman', 'Pengiriman ke toko ID 36, jumlah 2'),
(77, 13, '2025-10-20 21:59:22', 52, 50, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 2'),
(78, 13, '2025-10-20 21:59:49', 50, 48, 'pengiriman', 'Pengiriman ke toko ID 36, jumlah 2'),
(79, 13, '2025-10-20 22:00:14', 48, 44, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 4'),
(80, 14, '2025-10-20 22:00:39', 55, 54, 'pengiriman', 'Pengiriman ke toko ID 28, jumlah 1'),
(81, 15, '2025-10-20 22:01:06', 5, 4, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 1'),
(82, 15, '2025-10-20 22:01:35', 4, 3, 'pengiriman', 'Pengiriman ke toko ID 35, jumlah 1'),
(83, 14, '2025-10-20 22:02:48', 54, 52, 'pengiriman', 'Pengiriman ke toko ID 34, jumlah 2'),
(84, 15, '2025-10-21 19:35:00', 3, 1, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 2'),
(85, 13, '2025-10-21 21:01:21', 44, 22, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 22'),
(86, 13, '2025-10-21 21:11:27', 22, 10, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 12'),
(87, 13, '2025-10-21 21:12:10', 10, 22, 'barang masuk', 'Retur karena pengiriman ditolak (Surat Jalan ID: 83)'),
(88, 11, '2025-11-17 13:49:49', 2, 202, 'barang masuk', 'Barang masuk produk ID 11, qty 200'),
(89, 12, '2025-11-17 13:49:49', 9, 109, 'barang masuk', 'Barang masuk produk ID 12, qty 100'),
(90, 11, '2025-11-17 13:51:22', 202, 102, 'pengiriman', 'Pengiriman ke toko ID 36, jumlah 100'),
(91, 14, '2025-11-17 13:52:00', 52, 42, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 10'),
(92, 11, '2025-11-26 19:55:35', 102, 100, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 2'),
(93, 11, '2025-11-26 19:56:00', 100, 98, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 2'),
(94, 11, '2025-11-26 19:59:09', 98, 97, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 1'),
(95, 11, '2025-11-26 19:59:23', 97, 96, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 1'),
(96, 14, '2025-11-26 19:59:54', 42, 40, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 2'),
(97, 14, '2025-11-26 20:00:19', 40, 37, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 3'),
(98, 13, '2025-11-26 20:00:41', 22, 21, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 1'),
(99, 14, '2025-11-26 20:01:13', 37, 36, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 1'),
(100, 13, '2025-11-26 20:01:37', 21, 19, 'pengiriman', 'Pengiriman ke toko ID 34, jumlah 2'),
(101, 12, '2025-11-26 20:04:52', 109, 108, 'pengiriman', 'Pengiriman ke toko ID 35, jumlah 1'),
(102, 13, '2025-11-26 20:05:09', 19, 18, 'pengiriman', 'Pengiriman ke toko ID 28, jumlah 1'),
(103, 14, '2025-11-26 20:05:31', 36, 34, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 2'),
(104, 12, '2025-11-26 20:06:43', 108, 106, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 2'),
(105, 11, '2025-11-26 20:06:55', 96, 95, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 1'),
(106, 12, '2025-11-26 20:07:52', 106, 104, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 2'),
(107, 12, '2025-11-26 20:08:08', 104, 84, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 20'),
(108, 11, '2025-11-26 20:10:08', 95, 94, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 1'),
(109, 13, '2025-11-26 20:10:21', 18, 16, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 2'),
(110, 12, '2025-11-26 20:20:19', 84, 83, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 1'),
(111, 13, '2025-11-26 20:20:37', 16, 15, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 1'),
(112, 17, '2025-11-26 20:25:01', 87, 85, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 2'),
(113, 12, '2025-11-26 20:25:17', 83, 81, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 2'),
(114, 13, '2025-11-26 20:25:35', 15, 12, 'pengiriman', 'Pengiriman ke toko ID 36, jumlah 3'),
(115, 11, '2025-11-26 20:25:57', 94, 93, 'pengiriman', 'Pengiriman ke toko ID 36, jumlah 1'),
(116, 12, '2025-11-26 20:26:30', 81, 80, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 1'),
(117, 12, '2025-11-26 20:26:48', 80, 77, 'pengiriman', 'Pengiriman ke toko ID 35, jumlah 3'),
(118, 12, '2025-11-26 20:31:08', 77, 67, 'pengiriman', 'Pengiriman ke toko ID 38, jumlah 10'),
(119, 13, '2025-11-26 20:32:26', 12, 7, 'pengiriman', 'Pengiriman ke toko ID 37, jumlah 5'),
(120, 11, '2025-11-26 20:39:50', 93, 92, 'pengiriman', 'Pengiriman ke toko ID 40, jumlah 1'),
(121, 14, '2025-11-26 20:40:05', 34, 32, 'pengiriman', 'Pengiriman ke toko ID 39, jumlah 2'),
(122, 13, '2025-11-26 20:42:45', 7, 5, 'pengiriman', 'Pengiriman ke toko ID 36, jumlah 2'),
(123, 11, '2025-11-26 20:44:14', 92, 91, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 1'),
(124, 14, '2025-11-26 20:44:33', 32, 30, 'pengiriman', 'Pengiriman ke toko ID 36, jumlah 2'),
(125, 13, '2025-11-26 20:46:31', 5, 3, 'pengiriman', 'Pengiriman ke toko ID 36, jumlah 2'),
(126, 14, '2025-11-26 20:46:51', 30, 28, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 2'),
(127, 14, '2025-11-26 20:48:37', 28, 26, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 2'),
(128, 14, '2025-11-26 20:53:18', 26, 24, 'pengiriman', 'Pengiriman ke toko ID 41, jumlah 2'),
(129, 12, '2025-11-26 20:53:41', 67, 65, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 2'),
(130, 12, '2025-11-26 20:54:56', 65, 56, 'pengiriman', 'Pengiriman ke toko ID 40, jumlah 9'),
(131, 11, '2025-11-26 20:57:55', 91, 89, 'pengiriman', 'Pengiriman ke toko ID 42, jumlah 2'),
(132, 14, '2025-11-26 20:59:00', 24, 22, 'pengiriman', 'Pengiriman ke toko ID 28, jumlah 2'),
(133, 12, '2025-11-26 20:59:28', 56, 54, 'pengiriman', 'Pengiriman ke toko ID 42, jumlah 2'),
(134, 12, '2025-11-26 21:00:05', 54, 52, 'pengiriman', 'Pengiriman ke toko ID 28, jumlah 2'),
(135, 14, '2025-11-26 21:00:37', 22, 20, 'pengiriman', 'Pengiriman ke toko ID 36, jumlah 2'),
(136, 13, '2025-11-26 21:00:56', 3, 1, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 2'),
(137, 11, '2025-11-26 21:06:38', 89, 86, 'pengiriman', 'Pengiriman ke toko ID 36, jumlah 3'),
(138, 11, '2025-11-26 21:06:52', 86, 84, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 2'),
(139, 11, '2025-11-26 21:08:01', 84, 81, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 3'),
(140, 12, '2025-11-26 21:08:16', 52, 50, 'pengiriman', 'Pengiriman ke toko ID 36, jumlah 2'),
(141, 11, '2025-11-26 21:08:35', 81, 79, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 2'),
(142, 11, '2025-11-26 21:11:32', 79, 77, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 2'),
(143, 11, '2025-11-26 21:11:57', 77, 75, 'pengiriman', 'Pengiriman ke toko ID 41, jumlah 2'),
(144, 11, '2025-11-26 21:12:45', 75, 73, 'pengiriman', 'Pengiriman ke toko ID 41, jumlah 2'),
(145, 11, '2025-11-26 21:13:12', 73, 71, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 2'),
(146, 11, '2025-11-26 21:13:52', 71, 68, 'pengiriman', 'Pengiriman ke toko ID 40, jumlah 3'),
(147, 11, '2025-11-26 21:14:11', 68, 67, 'pengiriman', 'Pengiriman ke toko ID 39, jumlah 1'),
(148, 11, '2025-11-26 21:18:32', 67, 65, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 2'),
(149, 16, '2025-11-26 21:18:49', 47, 45, 'pengiriman', 'Pengiriman ke toko ID 41, jumlah 2'),
(150, 14, '2025-11-26 21:20:40', 20, 17, 'pengiriman', 'Pengiriman ke toko ID 28, jumlah 3'),
(151, 11, '2025-11-26 21:20:58', 65, 63, 'pengiriman', 'Pengiriman ke toko ID 42, jumlah 2'),
(152, 12, '2025-11-26 21:21:28', 50, 48, 'pengiriman', 'Pengiriman ke toko ID 38, jumlah 2'),
(153, 11, '2025-11-26 21:21:43', 63, 61, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 2'),
(154, 11, '2025-11-26 21:23:26', 61, 59, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 2'),
(155, 14, '2025-11-26 21:28:10', 17, 15, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 2'),
(156, 14, '2025-11-26 21:29:27', 15, 12, 'pengiriman', 'Pengiriman ke toko ID 35, jumlah 3'),
(157, 12, '2025-11-26 21:30:23', 48, 40, 'pengiriman', 'Pengiriman ke toko ID 38, jumlah 8'),
(158, 17, '2025-11-26 21:30:57', 85, 80, 'pengiriman', 'Pengiriman ke toko ID 40, jumlah 5'),
(159, 11, '2025-11-26 21:32:26', 59, 52, 'pengiriman', 'Pengiriman ke toko ID 39, jumlah 7'),
(160, 14, '2025-11-26 21:33:17', 12, 7, 'pengiriman', 'Pengiriman ke toko ID 40, jumlah 5'),
(161, 11, '2025-11-26 21:34:22', 52, 49, 'pengiriman', 'Pengiriman ke toko ID 39, jumlah 3'),
(162, 11, '2025-11-26 21:34:42', 49, 47, 'pengiriman', 'Pengiriman ke toko ID 39, jumlah 2'),
(163, 12, '2025-11-26 21:35:10', 40, 38, 'pengiriman', 'Pengiriman ke toko ID 40, jumlah 2'),
(164, 11, '2025-11-26 21:35:25', 47, 27, 'pengiriman', 'Pengiriman ke toko ID 38, jumlah 20'),
(165, 14, '2025-11-26 21:35:41', 7, 5, 'pengiriman', 'Pengiriman ke toko ID 35, jumlah 2'),
(166, 12, '2025-11-26 21:36:04', 38, 36, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 2'),
(167, 14, '2025-11-26 21:36:21', 5, 4, 'pengiriman', 'Pengiriman ke toko ID 36, jumlah 1'),
(168, 11, '2025-11-26 21:37:52', 27, 25, 'pengiriman', 'Pengiriman ke toko ID 43, jumlah 2'),
(169, 11, '2025-11-26 23:39:59', 25, 15, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 10'),
(170, 11, '2025-11-28 11:10:40', 15, 5, 'pengiriman', 'Pengiriman ke toko ID 41, jumlah 10'),
(171, 12, '2025-12-01 20:02:13', 36, 34, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 2'),
(172, 12, '2025-12-01 20:08:28', 34, 29, 'pengiriman', 'Pengiriman ke toko ID 41, jumlah 5'),
(173, 12, '2025-12-01 21:24:40', 29, 9, 'pengiriman', 'Pengiriman ke toko ID 37, jumlah 20'),
(174, 11, '2025-12-01 21:31:29', 5, 205, 'barang masuk', 'Barang masuk produk ID 11, qty 200'),
(175, 12, '2025-12-01 21:31:29', 9, 109, 'barang masuk', 'Barang masuk produk ID 12, qty 100'),
(176, 13, '2025-12-01 21:31:29', 1, 51, 'barang masuk', 'Barang masuk produk ID 13, qty 50'),
(177, 14, '2025-12-01 21:31:29', 4, 104, 'barang masuk', 'Barang masuk produk ID 14, qty 100'),
(178, 15, '2025-12-01 21:31:29', 1, 201, 'barang masuk', 'Barang masuk produk ID 15, qty 200'),
(179, 16, '2025-12-01 21:31:29', 45, 245, 'barang masuk', 'Barang masuk produk ID 16, qty 200'),
(180, 17, '2025-12-01 21:31:29', 80, 130, 'barang masuk', 'Barang masuk produk ID 17, qty 50'),
(181, 11, '2025-12-01 21:33:24', 205, 185, 'pengiriman', 'Pengiriman ke toko ID 31, jumlah 20'),
(182, 12, '2025-12-01 21:33:50', 109, 59, 'pengiriman', 'Pengiriman ke toko ID 43, jumlah 50'),
(183, 14, '2025-12-01 22:16:21', 104, 84, 'pengiriman', 'Pengiriman ke toko ID 32, jumlah 20'),
(184, 11, '2025-12-01 22:25:00', 185, 285, 'barang masuk', 'Barang masuk produk ID 11, qty 100'),
(185, 16, '2025-12-01 22:25:39', 245, 225, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 20'),
(186, 12, '2025-12-01 22:57:06', 59, 159, 'barang masuk', 'Barang masuk produk ID 12, qty 100'),
(187, 11, '2025-12-03 13:10:42', 285, 265, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 20'),
(188, 14, '2025-12-03 13:11:04', 84, 72, 'pengiriman', 'Pengiriman ke toko ID 27, jumlah 12'),
(189, 11, '2025-12-04 20:21:06', 265, 255, 'pengiriman', 'Pengiriman ke toko ID 34, jumlah 10'),
(190, 15, '2025-12-04 20:21:06', 201, 186, 'pengiriman', 'Pengiriman ke toko ID 34, jumlah 15'),
(191, 11, '2025-12-04 20:39:10', 255, 265, 'barang masuk', 'Pembatalan oleh admin - Surat Jalan ID 174'),
(192, 15, '2025-12-04 20:39:10', 186, 201, 'barang masuk', 'Pembatalan oleh admin - Surat Jalan ID 174'),
(193, 11, '2025-12-04 20:57:32', 265, 275, 'barang masuk', 'Pembatalan oleh admin - Surat Jalan ID 174'),
(194, 15, '2025-12-04 20:57:32', 201, 216, 'barang masuk', 'Pembatalan oleh admin - Surat Jalan ID 174'),
(195, 11, '2025-12-04 21:17:01', 275, 285, 'barang masuk', 'Pembatalan oleh admin - Surat Jalan ID 174'),
(196, 15, '2025-12-04 21:17:01', 216, 231, 'barang masuk', 'Pembatalan oleh admin - Surat Jalan ID 174'),
(197, 14, '2025-12-05 19:04:41', 72, 84, 'barang masuk', 'Pembatalan oleh admin - Surat Jalan ID 173'),
(198, 11, '2025-12-05 20:27:27', 285, 283, 'pengiriman', 'Pengiriman ke toko ID 42, jumlah 2'),
(199, 15, '2025-12-05 20:27:27', 231, 226, 'pengiriman', 'Pengiriman ke toko ID 42, jumlah 5'),
(200, 11, '2025-12-05 21:14:38', 283, 285, 'barang masuk', 'Pembatalan oleh admin - Surat Jalan ID 175'),
(201, 15, '2025-12-05 21:14:38', 226, 231, 'barang masuk', 'Pembatalan oleh admin - Surat Jalan ID 175'),
(202, 11, '2025-12-05 21:19:28', 285, 287, 'barang masuk', 'Pembatalan oleh admin - Surat Jalan ID 175'),
(203, 15, '2025-12-05 21:19:28', 231, 236, 'barang masuk', 'Pembatalan oleh admin - Surat Jalan ID 175');

-- --------------------------------------------------------

--
-- Table structure for table `sopir`
--

CREATE TABLE `sopir` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `plat_nomor` varchar(20) NOT NULL,
  `lokasi_terakhir_toko_id` int(11) DEFAULT NULL,
  `last_location_kecamatan_id` int(11) DEFAULT NULL,
  `lokasi_terakhir_lat` decimal(10,8) DEFAULT NULL,
  `lokasi_terakhir_lon` decimal(11,8) DEFAULT NULL,
  `lokasi_terakhir_waktu` datetime DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sopir`
--

INSERT INTO `sopir` (`id`, `nama`, `plat_nomor`, `lokasi_terakhir_toko_id`, `last_location_kecamatan_id`, `lokasi_terakhir_lat`, `lokasi_terakhir_lon`, `lokasi_terakhir_waktu`, `username`, `password`) VALUES
(13, 'Feri Yudistira', 'B 8328 KSD', NULL, NULL, NULL, NULL, NULL, 'feri123', '123'),
(14, 'fadly', 'B 9909 JQP', NULL, NULL, NULL, NULL, NULL, 'fadly', '123'),
(15, 'MIRTO', 'B 9877 ICE', NULL, NULL, NULL, NULL, NULL, 'mirto', '123'),
(16, 'ENDING ', 'B 9788 AKP', NULL, NULL, NULL, NULL, NULL, 'ending', '123'),
(17, 'Wage', 'B 9455 WSC', NULL, NULL, NULL, NULL, NULL, 'wage', '123');

-- --------------------------------------------------------

--
-- Table structure for table `surat_jalan`
--

CREATE TABLE `surat_jalan` (
  `id` int(11) NOT NULL,
  `nomor` varchar(50) NOT NULL,
  `tanggal` date NOT NULL,
  `sopir_id` int(11) NOT NULL,
  `urutan_rute` int(11) DEFAULT 0,
  `status` enum('Pending','Dalam Perjalanan','Terkirim','Ditolak') NOT NULL DEFAULT 'Pending',
  `alasan` text DEFAULT NULL,
  `tanggal_terkirim` datetime DEFAULT NULL,
  `bukti_foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `surat_jalan`
--

INSERT INTO `surat_jalan` (`id`, `nomor`, `tanggal`, `sopir_id`, `urutan_rute`, `status`, `alasan`, `tanggal_terkirim`, `bukti_foto`) VALUES
(166, 'SJ202512016097', '2025-12-01', 13, 0, 'Terkirim', '', '2025-12-01 21:11:46', 'uploads/bukti_166_1764598306.jpg'),
(167, 'SJ202512018883', '2025-12-01', 13, 0, 'Terkirim', '', '2025-12-01 21:30:34', 'uploads/bukti_167_1764599434.jpg'),
(168, 'SJ202512011005', '2025-12-01', 14, 0, 'Terkirim', '', '2025-12-05 21:04:04', 'uploads/bukti_168_1764943444.jpg'),
(169, 'SJ202512012763', '2025-12-01', 14, 0, 'Terkirim', '', '2025-12-05 21:03:52', NULL),
(170, 'SJ202512011332', '2025-12-01', 14, 0, 'Pending', NULL, NULL, NULL),
(171, 'SJ202512015990', '2025-12-01', 13, 0, 'Terkirim', '', '2025-12-03 13:09:03', 'uploads/bukti_171_1764742143.jpg'),
(172, 'SJ202512037718', '2025-12-03', 17, 0, 'Pending', NULL, NULL, NULL),
(173, 'SJ202512032555', '2025-12-03', 17, 0, 'Dalam Perjalanan', 'Dibatalkan oleh admin', NULL, NULL),
(174, 'SJ202512041140', '2025-12-04', 14, 0, 'Pending', 'Dibatalkan oleh admin', NULL, NULL),
(175, 'SJ202512053663', '2025-12-05', 17, 0, '', 'Dibatalkan oleh admin', '2025-12-05 20:51:00', 'uploads/bukti_175_1764942660.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `toko`
--

CREATE TABLE `toko` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `kecamatan_id` int(11) NOT NULL,
  `latitude` decimal(12,9) NOT NULL,
  `longitude` decimal(12,9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `toko`
--

INSERT INTO `toko` (`id`, `nama`, `alamat`, `no_telp`, `kecamatan_id`, `latitude`, `longitude`) VALUES
(27, 'TB Harapan Jaya', 'no 35 01/02 ,tangerang selatan, serpong Jalan Fani Afandi tangerang, Pd. Jagung Tim., Kec. Serpong Utara, Kota Tangerang Selatan, Banten 15310', '081238387373', 3, '-6.246041682', '106.674804939'),
(28, 'TB Asia Baru', 'Perumnas Jl. Beringin Raya No.165, RT.003/RW.011, Nusa Jaya, Kec. Karawaci, Kota Tangerang, Banten 15116', '021838373232', 6, '-6.167779922', '106.605003357'),
(31, 'TB Bintang Timur', 'Jl. Parakan No.51, Pd. Benda, Kec. Pamulang, Kota Tangerang Selatan, Banten 15415', '082183737382', 1, '-6.335516453', '106.737586975'),
(32, 'TB Cahaya Abadi', 'Jl. Dr. Setiabudi No.1B, RT.3/RW.5, Pamulang Bar., Kec. Pamulang, Kota Tangerang Selatan, Banten 15417', '0218837373', 1, '-6.340549469', '106.732604980'),
(34, 'TB Makmur', 'Jl. Serpong Parung, Padurenan, Kec. Gn. Sindur, Kabupaten Bogor, Jawa Barat 16340', '08127377883', 8, '-6.379169941', '106.711997986'),
(35, 'TB Bangunan Jaya', 'Jalan Raya Lapan. 1 - 2 Suradita, Cibogo, Kec. Cisauk, Kabupaten Tangerang, Banten 15341', '0219383838', 9, '-6.337629795', '106.638000488'),
(36, 'TB Giat Makmur', 'Jl. Aria Putra No.31, Serua Indah, Kec. Ciputat, Kota Tangerang Selatan, Banten 15414', '0212938389', 2, '-6.309588705', '106.719985769'),
(37, 'TB Alam Jaya', 'Jl. Kp. Cibogo No.8, RT.13/RW.3, Cibogo, Kec. Cisauk, Kabupaten Tangerang, Banten 15344', '0218373773', 9, '-6.325620926', '106.648664471'),
(38, 'TB Suradita Jaya', 'Perum, Suradita, Jl. Serpong Garden Jalan Baru No.11, Suradita, Kec. Cisauk, Kabupaten Tangerang, Banten 15343', '0892828282', 9, '-6.338416987', '106.643686291'),
(39, 'TB Sinar Baru Mandiri', 'JL. Kabasiran, Kec. Parung Panjang, Kabupaten Bogor, Jawa Barat 16360', '02191828222', 10, '-6.357422043', '106.582174045'),
(40, 'TB Hasill Karya Abadi', 'Jl. Raya Dago kp No.30, Kabasiran, Kec. Parung Panjang, Kabupaten Bogor, Jawa Barat 16360', '082192828272', 10, '-6.353846482', '106.579903363'),
(41, 'TB Dunia Bangunan BSD', 'BSD City, Blok CBD II, Sunburst, Jl. Raya Serpong Kavling No.12, Lengkong Gudang, Kec. Serpong Utara, Kota Tangerang Selatan, Banten 15322', '0218283773', 3, '-6.285869689', '106.661667048'),
(42, 'TB Bangunan Jaya', 'Jalan Beringin Raya Blok 27 No 23, Perumnas No.1, Kota Tangerang, Banten 15116', '021993383', 6, '-6.205168851', '106.615430900'),
(43, 'TB Jombang Jaya', 'Jl. Jombang Raya No.6, RW.4, Jombang, Kec. Ciputat, Kota Tangerang Selatan, Banten 15414', '0218383838', 2, '-6.298769494', '106.715633867');

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
-- Indexes for table `kecamatan`
--
ALTER TABLE `kecamatan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama` (`nama`);

--
-- Indexes for table `pengiriman`
--
ALTER TABLE `pengiriman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `surat_jalan_id` (`surat_jalan_id`),
  ADD KEY `produk_id` (`produk_id`),
  ADD KEY `toko_id` (`toko_id`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `riwayat_stok`
--
ALTER TABLE `riwayat_stok`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sopir`
--
ALTER TABLE `sopir`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plat_nomor` (`plat_nomor`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `username_2` (`username`);

--
-- Indexes for table `surat_jalan`
--
ALTER TABLE `surat_jalan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor` (`nomor`),
  ADD KEY `sopir_id` (`sopir_id`);

--
-- Indexes for table `toko`
--
ALTER TABLE `toko`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kecamatan_id` (`kecamatan_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kecamatan`
--
ALTER TABLE `kecamatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pengiriman`
--
ALTER TABLE `pengiriman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=178;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `riwayat_stok`
--
ALTER TABLE `riwayat_stok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=204;

--
-- AUTO_INCREMENT for table `sopir`
--
ALTER TABLE `sopir`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `surat_jalan`
--
ALTER TABLE `surat_jalan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=176;

--
-- AUTO_INCREMENT for table `toko`
--
ALTER TABLE `toko`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pengiriman`
--
ALTER TABLE `pengiriman`
  ADD CONSTRAINT `pengiriman_ibfk_1` FOREIGN KEY (`surat_jalan_id`) REFERENCES `surat_jalan` (`id`),
  ADD CONSTRAINT `pengiriman_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`),
  ADD CONSTRAINT `pengiriman_ibfk_3` FOREIGN KEY (`toko_id`) REFERENCES `toko` (`id`);

--
-- Constraints for table `surat_jalan`
--
ALTER TABLE `surat_jalan`
  ADD CONSTRAINT `surat_jalan_ibfk_1` FOREIGN KEY (`sopir_id`) REFERENCES `sopir` (`id`);

--
-- Constraints for table `toko`
--
ALTER TABLE `toko`
  ADD CONSTRAINT `toko_ibfk_1` FOREIGN KEY (`kecamatan_id`) REFERENCES `kecamatan` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
