-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 13, 2026 at 02:15 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `user`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `seat_number` varchar(10) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `qr_code_data` varchar(255) DEFAULT NULL,
  `payment_method` varchar(20) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `coupon_used` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `event_id`, `seat_number`, `price`, `status`, `qr_code_data`, `payment_method`, `transaction_id`, `created_at`, `booking_date`, `coupon_used`) VALUES
(49, 4, 15, 'A12', 7500.00, 'paid', 'KSUJT8WU27', 'card', NULL, '2026-04-06 09:11:57', '2026-04-06 09:11:57', NULL),
(50, 4, 22, 'E12', 3300.00, 'paid', 'AUQJEIS829', 'card', NULL, '2026-04-06 09:17:14', '2026-04-06 09:17:14', NULL),
(51, 4, 22, 'G8', 2200.00, 'paid', 'H16WY75U18', 'card', NULL, '2026-04-06 09:17:14', '2026-04-06 09:17:14', NULL),
(52, 4, 18, 'B4', 5400.00, 'paid', 'MNVH12J58A', 'card', NULL, '2026-04-06 09:54:09', '2026-04-06 09:54:09', NULL),
(53, 4, 22, 'C10', 4400.00, 'paid', 'LAKSI19582', 'card', NULL, '2026-04-06 10:19:52', '2026-04-06 10:19:52', NULL),
(54, 4, 15, 'C22', 5000.00, 'paid', 'A5S9A8W2A3', 'card', NULL, '2026-04-06 10:21:41', '2026-04-06 10:21:41', NULL),
(55, 4, 15, 'B16', 7500.00, 'paid', 'F9AZVBH27C', 'card', NULL, '2026-04-06 10:44:18', '2026-04-06 10:44:18', NULL),
(56, 4, 16, 'B9', 5400.00, 'paid', 'N8F70UCKPV', 'card', NULL, '2026-04-07 23:57:48', '2026-04-07 23:57:48', NULL),
(63, 4, 15, 'A11', 6250.00, 'paid', 'N8F70UCKPV', 'card', NULL, '2026-04-08 00:26:06', '2026-04-08 00:26:06', NULL),
(64, 4, 15, 'D23', 5000.00, 'paid', 'N8F70UCKPV', 'card', NULL, '2026-04-08 00:26:06', '2026-04-08 00:26:06', NULL),
(65, 4, 15, 'A1', 6750.00, 'paid', 'YH9PUE1F43', 'card', NULL, '2026-04-08 00:27:24', '2026-04-08 00:27:24', NULL),
(66, 4, 15, 'I14', 2500.00, 'pending', NULL, 'card', NULL, '2026-04-13 12:15:35', '2026-04-13 12:15:35', NULL),
(67, 4, 15, 'I15', 2500.00, 'pending', NULL, 'card', NULL, '2026-04-13 12:15:35', '2026-04-13 12:15:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `coupon_offers`
--

CREATE TABLE `coupon_offers` (
  `id` int(11) NOT NULL,
  `coupon_name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `point_cost` int(11) DEFAULT NULL,
  `max_uses` int(11) DEFAULT NULL,
  `tier_label` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupon_offers`
--

INSERT INTO `coupon_offers` (`id`, `coupon_name`, `description`, `point_cost`, `max_uses`, `tier_label`) VALUES
(1, 'CRIMSON-NEWBIE', 'Exclusive ₱500 discount for your first event booking.', 1000, 1, 'NEW USER ONLY'),
(2, 'FAN-FAVE-10', '10% Discount on any Regular event ticket.', 500, 5, 'REDEEMABLE 5x');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `artist` varchar(120) NOT NULL,
  `venue` varchar(150) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `poster` varchar(255) DEFAULT NULL,
  `status` enum('active','soldout','cancelled') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_rows` int(11) DEFAULT 10,
  `cols_per_row` int(11) NOT NULL,
  `seats_per_row` int(11) DEFAULT 10,
  `vip_rows_count` int(11) DEFAULT 3,
  `price_vip1` int(11) DEFAULT 0,
  `price_vip2` int(11) DEFAULT 0,
  `price_vip3` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `artist`, `venue`, `event_date`, `event_time`, `price`, `poster`, `status`, `created_at`, `total_rows`, `cols_per_row`, `seats_per_row`, `vip_rows_count`, `price_vip1`, `price_vip2`, `price_vip3`) VALUES
(14, 'Sariling Mundo Tour', 'TJ Monterde', 'Cebu City Sports Center', '2026-12-22', '20:00:00', 1500.00, 'images/1769754553_tj.png', 'active', '2026-01-30 06:29:13', 20, 0, 10, 3, 0, 0, 0),
(15, 'P-Pop Legend', 'SB19', 'Philippine Arena, Bulacan', '2026-04-18', '19:00:00', 2500.00, 'images/1769754635_sb19.jpg', 'active', '2026-01-30 06:30:35', 10, 0, 10, 3, 0, 0, 0),
(16, 'Folk-Pop Sessions', 'Ben&Ben', 'Waterfront Hotel Cebu', '2026-08-12', '20:00:00', 1800.00, 'images/1769754683_BenBen.jpg', 'active', '2026-01-30 06:31:23', 10, 0, 10, 3, 0, 0, 0),
(17, 'Pop Royalty', 'Sarah G.', 'Araneta Coliseum, QC', '2026-10-15', '20:00:00', 3000.00, 'images/1769754732_sarahg.jpg', 'active', '2026-01-30 06:32:12', 10, 0, 10, 3, 0, 0, 0),
(18, 'Higa Night Live', 'Arthur Nery', 'SM Seaside Arena, Cebu', '2026-07-16', '20:00:00', 1800.00, 'images/1769757109_ArthurNery.jpg', 'active', '2026-01-30 07:11:49', 10, 0, 10, 3, 0, 0, 0),
(19, 'OPM Hearts Tour', 'Moira Dela Torre', 'Araneta Coliseum, Quezon City', '2026-09-07', '19:30:00', 2000.00, 'images/1769757264_moira.jpg', 'active', '2026-01-30 07:14:24', 10, 0, 10, 3, 0, 0, 0),
(20, 'Saturn Nights', 'Zack Tabudlo', 'Waterfront Hotel Ballroom, Cebu', '2026-07-20', '20:00:00', 1500.00, 'images/1769757382_Zack_Tabudlo_Coke_Studio_16-9.jpg', 'active', '2026-01-30 07:16:22', 10, 0, 10, 3, 0, 0, 0),
(21, 'Where Have You Been Live', 'IV of Spades', 'Philippine Arena, Bulacan', '2026-10-11', '18:30:00', 2800.00, 'images/1769757442_ivos_2025_08_10_17_17_27.jpg', 'active', '2026-01-30 07:17:22', 10, 0, 10, 3, 0, 0, 0),
(22, 'OPM Legends Night', 'Rico Blanco', 'Cebu City Sports Center', '2026-11-20', '20:00:00', 2200.00, 'images/1769757515_RicoBlancoPR.jpg', 'active', '2026-01-30 07:18:35', 10, 0, 10, 3, 0, 0, 0),
(23, 'Asia’s Songbird Live in Concert', 'Regine Velasquez', 'Smart Araneta Coliseum, Quezon City', '2026-12-26', '20:00:00', 3500.00, 'images/1769758250_maxresdefault.jpg', 'active', '2026-01-30 07:30:50', 10, 0, 10, 3, 0, 0, 0),
(26, 'Bruno Mars - End of The World', 'Bruno Mars', 'Manila ', '2026-12-25', '20:00:00', 1500.00, 'images/1776082383_bruno_mars_MI0004141313_MN0001032082.jpg', 'active', '2026-04-13 12:13:03', 15, 50, 10, 3, 5000, 9000, 12000);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `lname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `points` int(11) DEFAULT 0,
  `status` varchar(20) DEFAULT 'pending',
  `profile_pic` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `name`, `lname`, `email`, `password`, `role`, `points`, `status`, `profile_pic`) VALUES
(1, 'jeremy', 'rabanes', 'rabanesjeremy@gmail.com', '$2y$10$r.DOQmeMY36viOLWMSquM.HHJXCOeqmIN21tHU4sxjACe0BnjllL6', 'admin', 0, 'active', 'default.png'),
(4, 'Kimberly', 'Herbias', 'kimberly@gmail.com', '$2y$10$r.DOQmeMY36viOLWMSquM.HHJXCOeqmIN21tHU4sxjACe0BnjllL6', 'user', 2390, 'active', '4_1772352025.jpg'),
(6, 'Jhon Darell', 'Pateno', 'darell@gmail.com', '$2y$10$PMLyY8EdRXx1WQovdzeBIOq9hZ4neKyCcui2ABIJ7XKNuUcNij.1u', 'user', 0, 'active', 'default.png');

-- --------------------------------------------------------

--
-- Table structure for table `user_coupons`
--

CREATE TABLE `user_coupons` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `coupon_name` varchar(50) DEFAULT NULL,
  `redeemed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_coupons`
--

INSERT INTO `user_coupons` (`id`, `user_id`, `coupon_name`, `redeemed_at`) VALUES
(1, 1, 'CRIMSON-NEWBIE', '2026-04-05 06:59:05'),
(2, 1, 'FAN-FAVE-10', '2026-04-05 06:59:05'),
(4, 4, 'CRIMSON-NEWBIE', '2026-04-06 10:10:46'),
(7, 4, 'FAN-FAVE-10', '2026-04-08 00:06:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `coupon_offers`
--
ALTER TABLE `coupon_offers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupon_name` (`coupon_name`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_coupons`
--
ALTER TABLE `user_coupons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_redemption` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `coupon_offers`
--
ALTER TABLE `coupon_offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_coupons`
--
ALTER TABLE `user_coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_coupons`
--
ALTER TABLE `user_coupons`
  ADD CONSTRAINT `fk_user_redemption` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
