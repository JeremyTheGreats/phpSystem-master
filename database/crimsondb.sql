-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2026 at 08:53 AM
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
-- Database: `crimsondb`
--

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` enum('Pending','Paid','Cancelled') DEFAULT 'Pending',
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `validation_message` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `event_id` int(11) NOT NULL,
  `organizer_id` int(11) NOT NULL,
  `venue_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `artist` varchar(100) DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `poster` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `total_rows` int(11) DEFAULT NULL,
  `cols_per_row` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`event_id`, `organizer_id`, `venue_id`, `title`, `artist`, `event_date`, `event_time`, `poster`, `price`, `total_rows`, `cols_per_row`, `status`, `created_at`) VALUES
(1, 2, 1, 'bruno mars', 'Bruno Mars', '1212-12-12', '12:12:00', 'images/1777443321_bruno-mars_MI0004141313-MN0001032082.jpg', 1500.00, NULL, NULL, 'active', '2026-04-29 06:15:21'),
(4, 2, 1, 'test', 'test', '0000-00-00', '12:12:00', 'images/1777444316_download.jpg', 1500.00, 1212, 1212, 'active', '2026-04-29 06:31:56');

-- --------------------------------------------------------

--
-- Table structure for table `rewards_coupon`
--

CREATE TABLE `rewards_coupon` (
  `coupon_id` int(11) NOT NULL,
  `coupon_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `point_cost` int(11) DEFAULT 0,
  `max_uses` int(11) DEFAULT NULL,
  `tier_label` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rewards_coupon`
--

INSERT INTO `rewards_coupon` (`coupon_id`, `coupon_name`, `description`, `point_cost`, `max_uses`, `tier_label`) VALUES
(2, 'test', 'testt', 1500, 10, 'test');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `ticket_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `seat_number` varchar(10) NOT NULL,
  `is_vip` tinyint(1) DEFAULT 0,
  `price` decimal(10,2) NOT NULL,
  `status` enum('Available','Booked','Reserved') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Organizer','Customer') DEFAULT 'Customer',
  `points` int(11) DEFAULT 0,
  `status` varchar(20) DEFAULT 'Active',
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `first_name`, `last_name`, `email`, `password`, `role`, `points`, `status`, `profile_pic`) VALUES
(1, 'jeremy', 'rabanes', 'rabanesjeremy@gmail.com', '$2y$10$4mRsUzf4OqqGisXxvuM8KuWuCJ./PPcone.32mIrT64OcFOSS0asy', 'Admin', 0, 'Active', NULL),
(2, 'Kimberly', 'Herbias', 'kimberly@gmail.com', '$2y$10$iv1mnKA48BbLITJXSkvNY.XYgjfHLegMGq1ugJ7G/Qxd7KgLSwDPK', 'Organizer', 0, 'Active', NULL),
(3, 'darell', 'Pateno', 'darell@gmail.com', '$2y$10$G9CJMI04T/bCVs.n.A.YtefpCysP5jNsE7B2l//OnT01lHjGLl/P2', 'Customer', 0, 'active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_rewards`
--

CREATE TABLE `user_rewards` (
  `user_reward_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `redeemed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `venue`
--

CREATE TABLE `venue` (
  `venue_id` int(11) NOT NULL,
  `venue_name` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `capacity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venue`
--

INSERT INTO `venue` (`venue_id`, `venue_name`, `location`, `capacity`) VALUES
(1, 'test', 'test', 500);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `organizer_id` (`organizer_id`),
  ADD KEY `venue_id` (`venue_id`);

--
-- Indexes for table `rewards_coupon`
--
ALTER TABLE `rewards_coupon`
  ADD PRIMARY KEY (`coupon_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_rewards`
--
ALTER TABLE `user_rewards`
  ADD PRIMARY KEY (`user_reward_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `coupon_id` (`coupon_id`);

--
-- Indexes for table `venue`
--
ALTER TABLE `venue`
  ADD PRIMARY KEY (`venue_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rewards_coupon`
--
ALTER TABLE `rewards_coupon`
  MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_rewards`
--
ALTER TABLE `user_rewards`
  MODIFY `user_reward_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `venue`
--
ALTER TABLE `venue`
  MODIFY `venue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `event` (`event_id`),
  ADD CONSTRAINT `booking_ibfk_3` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`ticket_id`);

--
-- Constraints for table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `event_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_ibfk_2` FOREIGN KEY (`venue_id`) REFERENCES `venue` (`venue_id`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `event` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_rewards`
--
ALTER TABLE `user_rewards`
  ADD CONSTRAINT `user_rewards_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `user_rewards_ibfk_2` FOREIGN KEY (`coupon_id`) REFERENCES `rewards_coupon` (`coupon_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
