-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2025 at 10:19 PM
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
-- Database: `batcavecafez`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(80) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `rate_per_hour` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`id`, `code`, `name`, `rate_per_hour`, `created_at`) VALUES
(1, 'PROJECTOR', 'Projector', 150.00, '2025-11-17 16:16:34'),
(2, 'SPEAKER_MIKE', 'Speaker + Microphone', 150.00, '2025-11-17 16:16:34');

-- --------------------------------------------------------

--
-- Table structure for table `menu_categories`
--

CREATE TABLE `menu_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menu_categories`
--

INSERT INTO `menu_categories` (`id`, `name`, `description`) VALUES
(1, 'Specialty Coffee', 'Signature coffee & specialty drinks'),
(2, 'Pastries', 'Baked items'),
(3, 'Snacks', 'Light meals and snacks');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `created_at`) VALUES
(1, 1, 'The Bat Brew (Signature)', NULL, 120.00, '2025-11-17 16:16:34'),
(2, 1, 'Midnight Mocha', NULL, 135.00, '2025-11-17 16:16:34'),
(3, 2, 'Red Velvet Muffin', NULL, 65.00, '2025-11-17 16:16:34'),
(4, 3, 'Nachos Grande', NULL, 180.00, '2025-11-17 16:16:34');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_type` enum('STUDY','GATHERING','EVENT') NOT NULL,
  `reservation_date` date NOT NULL,
  `start_time` time NOT NULL,
  `hours` decimal(4,2) NOT NULL,
  `end_time` time GENERATED ALWAYS AS (addtime(`start_time`,sec_to_time(round(`hours` * 3600,0)))) STORED,
  `persons` int(11) NOT NULL DEFAULT 1,
  `student_id` varchar(50) DEFAULT NULL,
  `contact_email` varchar(255) NOT NULL,
  `contact_phone` varchar(30) NOT NULL,
  `base_hourly_rate` decimal(10,2) NOT NULL DEFAULT 50.00,
  `minimum_fee` decimal(10,2) NOT NULL DEFAULT 75.00,
  `estimated_equipment_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estimated_total_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('PENDING','CONFIRMED','REJECTED','REVERTED','CANCELLED') NOT NULL DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `room_type`, `reservation_date`, `start_time`, `hours`, `persons`, `student_id`, `contact_email`, `contact_phone`, `base_hourly_rate`, `minimum_fee`, `estimated_equipment_fee`, `estimated_total_fee`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'STUDY', '2025-12-05', '19:00:00', 3.00, 6, '2019-01234', 'juan@example.com', '09170001111', 50.00, 75.00, 0.00, 0.00, 'PENDING', '2025-11-17 16:15:32', '2025-11-17 16:15:32'),
(2, 1, 'STUDY', '2025-12-05', '19:00:00', 3.00, 6, '2019-01234', 'juan@example.com', '09170001111', 50.00, 75.00, 450.00, 600.00, 'CONFIRMED', '2025-11-17 16:17:13', '2025-11-17 16:18:41'),
(3, 3, 'STUDY', '2025-11-01', '13:00:00', 1.50, 2, '6', 'medinalander66@gmail.com', '09452180407', 50.00, 75.00, 450.00, 525.00, 'PENDING', '2025-11-18 09:12:26', '2025-11-18 09:12:26'),
(4, 3, 'STUDY', '2025-11-17', '13:00:00', 1.00, 2, '6', 'medinalander66@gmail.com', '09452180407', 50.00, 75.00, 300.00, 350.00, 'PENDING', '2025-11-18 09:39:34', '2025-11-18 09:39:34'),
(5, 4, 'STUDY', '2025-11-01', '13:00:00', 1.50, 1, '2', 'medinalander33@gmail.com', '09452180407', 50.00, 75.00, 450.00, 525.00, 'PENDING', '2025-11-18 09:40:00', '2025-11-18 09:40:00'),
(6, 3, 'STUDY', '2025-11-01', '13:00:00', 1.50, 2, '6', 'medinalander66@gmail.com', '09452180407', 50.00, 75.00, 450.00, 525.00, 'PENDING', '2025-11-18 09:46:06', '2025-11-18 09:46:06'),
(7, 3, 'STUDY', '2025-11-01', '14:22:00', 1.50, 2, '6', 'medinalander66@gmail.com', '09452180407', 50.00, 75.00, 450.00, 525.00, 'PENDING', '2025-11-18 09:57:00', '2025-11-18 09:57:00'),
(8, 5, 'STUDY', '2025-11-01', '13:00:00', 1.50, 1, '6', 'medinalander3@gmail.com', '09452180407', 50.00, 75.00, 450.00, 525.00, 'PENDING', '2025-11-18 09:57:27', '2025-11-18 09:57:27'),
(9, 3, 'STUDY', '2025-11-18', '13:00:00', 1.00, 1, '6', 'medinalander66@gmail.com', '09452180407', 50.00, 75.00, 300.00, 350.00, 'PENDING', '2025-11-18 10:20:26', '2025-11-18 10:20:26'),
(10, 3, 'STUDY', '2025-11-18', '16:00:00', 1.00, 3, '6', 'medinalander66@gmail.com', '09452180407', 50.00, 75.00, 300.00, 350.00, 'PENDING', '2025-11-18 11:34:12', '2025-11-18 11:34:12'),
(11, 3, 'STUDY', '2025-11-18', '12:00:00', 1.00, 1, '6', 'medinalander66@gmail.com', '09452180407', 50.00, 75.00, 300.00, 350.00, 'PENDING', '2025-11-18 11:36:25', '2025-11-18 11:36:25'),
(12, 3, 'STUDY', '2025-11-18', '01:00:00', 1.50, 1, '6', 'medinalander66@gmail.com', '09452180407', 50.00, 75.00, 450.00, 525.00, 'PENDING', '2025-11-18 12:45:33', '2025-11-18 12:45:33'),
(13, 3, 'GATHERING', '2025-11-19', '01:00:00', 1.00, 1, '6', 'medinalander66@gmail.com', '09452180407', 50.00, 75.00, 300.00, 350.00, 'PENDING', '2025-11-18 16:28:24', '2025-11-18 16:28:24'),
(14, 3, 'STUDY', '2025-11-19', '01:00:00', 1.00, 2, '6', 'medinalander66@gmail.com', '09452180407', 50.00, 75.00, 300.00, 350.00, 'PENDING', '2025-11-18 16:40:12', '2025-11-18 16:40:12'),
(15, 3, 'EVENT', '2025-11-19', '01:00:00', 2.00, 1, '6', 'medinalander66@gmail.com', '09452180407', 50.00, 75.00, 600.00, 700.00, 'PENDING', '2025-11-18 17:56:23', '2025-11-18 17:56:23'),
(16, 3, 'EVENT', '2025-11-19', '14:00:00', 5.00, 1, '6', 'medinalander66@gmail.com', '09452180407', 50.00, 75.00, 1500.00, 1750.00, 'PENDING', '2025-11-18 18:55:27', '2025-11-18 18:55:27');

-- --------------------------------------------------------

--
-- Table structure for table `reservation_actions`
--

CREATE TABLE `reservation_actions` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action_type` enum('SUBMITTED','CONFIRMED','REJECTED','REVERTED','CANCELLED','UPDATED') NOT NULL,
  `action_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservation_actions`
--

INSERT INTO `reservation_actions` (`id`, `reservation_id`, `admin_id`, `action_type`, `action_reason`, `created_at`) VALUES
(1, 2, NULL, 'SUBMITTED', 'User submitted booking via site.', '2025-11-17 16:17:13'),
(2, 3, NULL, 'SUBMITTED', 'User submitted reservation via website.', '2025-11-18 09:12:26'),
(3, 4, NULL, 'SUBMITTED', 'User submitted reservation via website.', '2025-11-18 09:39:34'),
(4, 5, NULL, 'SUBMITTED', 'User submitted reservation via website.', '2025-11-18 09:40:00'),
(5, 6, NULL, 'SUBMITTED', 'User submitted reservation via website.', '2025-11-18 09:46:06'),
(6, 7, NULL, 'SUBMITTED', 'User submitted reservation via website.', '2025-11-18 09:57:00'),
(7, 8, NULL, 'SUBMITTED', 'User submitted reservation via website.', '2025-11-18 09:57:28'),
(8, 9, NULL, 'SUBMITTED', 'User submitted reservation via website.', '2025-11-18 10:20:26'),
(9, 10, NULL, 'SUBMITTED', 'User submitted reservation via website.', '2025-11-18 11:34:12'),
(10, 11, NULL, 'SUBMITTED', 'User submitted reservation via website.', '2025-11-18 11:36:25'),
(11, 12, NULL, 'SUBMITTED', 'User submitted reservation via website.', '2025-11-18 12:45:33'),
(12, 13, NULL, 'SUBMITTED', 'Submitted via website', '2025-11-18 16:28:25'),
(13, 14, NULL, 'SUBMITTED', 'Submitted via website', '2025-11-18 16:40:12'),
(14, 15, NULL, 'SUBMITTED', 'Submitted via website', '2025-11-18 17:56:23'),
(15, 16, NULL, 'SUBMITTED', 'Submitted via website', '2025-11-18 18:55:27');

-- --------------------------------------------------------

--
-- Stand-in structure for view `reservation_costs`
-- (See below for the actual view)
--
CREATE TABLE `reservation_costs` (
`reservation_id` int(11)
,`hours` decimal(4,2)
,`base_hourly_rate` decimal(10,2)
,`minimum_fee` decimal(10,2)
,`base_charge` decimal(14,4)
,`equipment_charge` decimal(46,4)
,`total_raw` decimal(47,4)
,`total_after_minimum` decimal(47,4)
);

-- --------------------------------------------------------

--
-- Table structure for table `reservation_equipment`
--

CREATE TABLE `reservation_equipment` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `equipment_rate_per_hour` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservation_equipment`
--

INSERT INTO `reservation_equipment` (`id`, `reservation_id`, `equipment_id`, `quantity`, `equipment_rate_per_hour`) VALUES
(1, 2, 1, 1, 150.00),
(2, 3, 1, 1, 150.00),
(3, 3, 2, 1, 150.00),
(4, 4, 1, 1, 150.00),
(5, 4, 2, 1, 150.00),
(6, 5, 1, 1, 150.00),
(7, 5, 2, 1, 150.00),
(8, 6, 2, 1, 150.00),
(9, 6, 1, 1, 150.00),
(10, 7, 1, 1, 150.00),
(11, 7, 2, 1, 150.00),
(12, 8, 1, 1, 150.00),
(13, 8, 2, 1, 150.00),
(14, 9, 1, 1, 150.00),
(15, 9, 2, 1, 150.00),
(16, 10, 1, 1, 150.00),
(17, 10, 2, 1, 150.00),
(18, 11, 2, 1, 150.00),
(19, 11, 1, 1, 150.00),
(20, 12, 2, 1, 150.00),
(21, 12, 1, 1, 150.00),
(22, 13, 1, 1, 150.00),
(23, 13, 2, 1, 150.00),
(24, 14, 1, 1, 150.00),
(25, 14, 2, 1, 150.00),
(26, 15, 1, 1, 150.00),
(27, 15, 2, 1, 150.00),
(28, 16, 1, 1, 150.00),
(29, 16, 2, 1, 150.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `student_id`, `email`, `phone`, `created_at`) VALUES
(1, 'Juan Dela Cruz', '2019-01234', 'juan@example.com', '09170001111', '2025-11-17 16:15:32'),
(3, 'Lander', '6', 'medinalander66@gmail.com', '09452180407', '2025-11-18 09:12:26'),
(4, 'Medina', '2', 'medinalander33@gmail.com', '09452180407', '2025-11-18 09:40:00'),
(5, 'Lander', '6', 'medinalander3@gmail.com', '09452180407', '2025-11-18 09:57:27');

-- --------------------------------------------------------

--
-- Structure for view `reservation_costs`
--
DROP TABLE IF EXISTS `reservation_costs`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `reservation_costs`  AS SELECT `r`.`id` AS `reservation_id`, `r`.`hours` AS `hours`, `r`.`base_hourly_rate` AS `base_hourly_rate`, `r`.`minimum_fee` AS `minimum_fee`, `r`.`hours`* `r`.`base_hourly_rate` AS `base_charge`, coalesce(sum(`re`.`quantity` * `e`.`rate_per_hour` * `r`.`hours`),0.00) AS `equipment_charge`, `r`.`hours`* `r`.`base_hourly_rate` + coalesce(sum(`re`.`quantity` * `e`.`rate_per_hour` * `r`.`hours`),0.00) AS `total_raw`, CASE WHEN `r`.`hours` < 2 THEN greatest(`r`.`minimum_fee`,`r`.`hours` * `r`.`base_hourly_rate` + coalesce(sum(`re`.`quantity` * `e`.`rate_per_hour` * `r`.`hours`),0.00)) ELSE `r`.`hours`* `r`.`base_hourly_rate` + coalesce(sum(`re`.`quantity` * `e`.`rate_per_hour` * `r`.`hours`),0.00) END AS `total_after_minimum` FROM ((`reservations` `r` left join `reservation_equipment` `re` on(`re`.`reservation_id` = `r`.`id`)) left join `equipment` `e` on(`e`.`id` = `re`.`equipment_id`)) GROUP BY `r`.`id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `menu_categories`
--
ALTER TABLE `menu_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reservation_actions`
--
ALTER TABLE `reservation_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `reservation_equipment`
--
ALTER TABLE `reservation_equipment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `equipment_id` (`equipment_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `menu_categories`
--
ALTER TABLE `menu_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `reservation_actions`
--
ALTER TABLE `reservation_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `reservation_equipment`
--
ALTER TABLE `reservation_equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservation_actions`
--
ALTER TABLE `reservation_actions`
  ADD CONSTRAINT `reservation_actions_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservation_actions_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reservation_equipment`
--
ALTER TABLE `reservation_equipment`
  ADD CONSTRAINT `reservation_equipment_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservation_equipment_ibfk_2` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
