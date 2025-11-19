-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 19, 2025 at 09:53 AM
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
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_type_id` int(11) NOT NULL,
  `reservation_date` date NOT NULL,
  `start_time` time NOT NULL,
  `hours` decimal(4,2) NOT NULL,
  `end_time` time GENERATED ALWAYS AS (addtime(`start_time`,sec_to_time(round(`hours` * 3600,0)))) STORED,
  `persons` int(11) NOT NULL DEFAULT 1,
  `student_id` varchar(50) DEFAULT NULL,
  `contact_email` varchar(255) NOT NULL,
  `contact_phone` varchar(30) NOT NULL,
  `estimated_equipment_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estimated_total_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('PENDING','CONFIRMED','REJECTED','REVERTED','CANCELLED') NOT NULL DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `room_type_id`, `reservation_date`, `start_time`, `hours`, `persons`, `student_id`, `contact_email`, `contact_phone`, `estimated_equipment_fee`, `estimated_total_fee`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 0, '2025-12-05', '19:00:00', 3.00, 6, '2019-01234', 'juan@example.com', '09170001111', 0.00, 0.00, 'PENDING', '2025-11-17 16:15:32', '2025-11-17 16:15:32'),
(2, 1, 0, '2025-12-05', '19:00:00', 3.00, 6, '2019-01234', 'juan@example.com', '09170001111', 450.00, 600.00, 'CONFIRMED', '2025-11-17 16:17:13', '2025-11-17 16:18:41'),
(3, 3, 0, '2025-11-01', '13:00:00', 1.50, 2, '6', 'medinalander66@gmail.com', '09452180407', 450.00, 525.00, 'PENDING', '2025-11-18 09:12:26', '2025-11-18 09:12:26'),
(4, 3, 0, '2025-11-17', '13:00:00', 1.00, 2, '6', 'medinalander66@gmail.com', '09452180407', 300.00, 350.00, 'PENDING', '2025-11-18 09:39:34', '2025-11-18 09:39:34'),
(5, 4, 0, '2025-11-01', '13:00:00', 1.50, 1, '2', 'medinalander33@gmail.com', '09452180407', 450.00, 525.00, 'PENDING', '2025-11-18 09:40:00', '2025-11-18 09:40:00'),
(6, 3, 0, '2025-11-01', '13:00:00', 1.50, 2, '6', 'medinalander66@gmail.com', '09452180407', 450.00, 525.00, 'PENDING', '2025-11-18 09:46:06', '2025-11-18 09:46:06'),
(7, 3, 0, '2025-11-01', '14:22:00', 1.50, 2, '6', 'medinalander66@gmail.com', '09452180407', 450.00, 525.00, 'PENDING', '2025-11-18 09:57:00', '2025-11-18 09:57:00'),
(8, 5, 0, '2025-11-01', '13:00:00', 1.50, 1, '6', 'medinalander3@gmail.com', '09452180407', 450.00, 525.00, 'PENDING', '2025-11-18 09:57:27', '2025-11-18 09:57:27'),
(9, 3, 0, '2025-11-18', '13:00:00', 1.00, 1, '6', 'medinalander66@gmail.com', '09452180407', 300.00, 350.00, 'PENDING', '2025-11-18 10:20:26', '2025-11-18 10:20:26'),
(10, 3, 0, '2025-11-18', '16:00:00', 1.00, 3, '6', 'medinalander66@gmail.com', '09452180407', 300.00, 350.00, 'PENDING', '2025-11-18 11:34:12', '2025-11-18 11:34:12'),
(11, 3, 0, '2025-11-18', '12:00:00', 1.00, 1, '6', 'medinalander66@gmail.com', '09452180407', 300.00, 350.00, 'PENDING', '2025-11-18 11:36:25', '2025-11-18 11:36:25'),
(12, 3, 0, '2025-11-18', '01:00:00', 1.50, 1, '6', 'medinalander66@gmail.com', '09452180407', 450.00, 525.00, 'PENDING', '2025-11-18 12:45:33', '2025-11-18 12:45:33'),
(13, 3, 0, '2025-11-19', '01:00:00', 1.00, 1, '6', 'medinalander66@gmail.com', '09452180407', 300.00, 350.00, 'PENDING', '2025-11-18 16:28:24', '2025-11-18 16:28:24'),
(14, 3, 0, '2025-11-19', '01:00:00', 1.00, 2, '6', 'medinalander66@gmail.com', '09452180407', 300.00, 350.00, 'PENDING', '2025-11-18 16:40:12', '2025-11-18 16:40:12'),
(15, 3, 0, '2025-11-19', '01:00:00', 2.00, 1, '6', 'medinalander66@gmail.com', '09452180407', 600.00, 700.00, 'PENDING', '2025-11-18 17:56:23', '2025-11-18 17:56:23'),
(16, 3, 0, '2025-11-19', '14:00:00', 5.00, 1, '6', 'medinalander66@gmail.com', '09452180407', 1500.00, 1750.00, 'PENDING', '2025-11-18 18:55:27', '2025-11-18 18:55:27'),
(17, 3, 0, '2025-11-19', '01:00:00', 3.00, 2, '6', 'medinalander66@gmail.com', '09452180407', 900.00, 1050.00, 'PENDING', '2025-11-18 21:24:56', '2025-11-18 21:24:56'),
(18, 3, 0, '2025-11-19', '15:30:00', 1.00, 2, '6', 'medinalander66@gmail.com', '09452180407', 300.00, 350.00, 'PENDING', '2025-11-18 21:45:52', '2025-11-18 21:45:52'),
(19, 3, 0, '2025-11-19', '22:15:00', 3.00, 1, '6', 'medinalander66@gmail.com', '09452180407', 900.00, 1050.00, 'PENDING', '2025-11-18 21:53:17', '2025-11-18 21:53:17'),
(20, 3, 0, '2025-11-19', '14:00:00', 1.00, 2, '6', 'medinalander66@gmail.com', '09452180407', 300.00, 350.00, 'PENDING', '2025-11-18 21:55:24', '2025-11-18 21:55:24'),
(21, 3, 0, '2025-11-19', '18:00:00', 1.00, 1, '6', 'medinalander66@gmail.com', '09452180407', 300.00, 350.00, 'PENDING', '2025-11-18 21:55:58', '2025-11-18 21:55:58'),
(22, 3, 8, '2025-11-19', '14:30:00', 2.50, 2, '6', 'medinalander66@gmail.com', '09452180407', 750.00, 1650.00, 'PENDING', '2025-11-19 08:09:11', '2025-11-19 08:09:11');

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
(15, 16, NULL, 'SUBMITTED', 'Submitted via website', '2025-11-18 18:55:27'),
(16, 17, NULL, 'SUBMITTED', 'Submitted via website', '2025-11-18 21:24:56'),
(17, 18, NULL, 'SUBMITTED', 'Submitted via website', '2025-11-18 21:45:52'),
(18, 19, NULL, 'SUBMITTED', 'Submitted via website', '2025-11-18 21:53:17'),
(19, 20, NULL, 'SUBMITTED', 'Submitted via website', '2025-11-18 21:55:24'),
(20, 21, NULL, 'SUBMITTED', 'Submitted via website', '2025-11-18 21:55:58'),
(21, 22, NULL, 'SUBMITTED', 'Submitted via website', '2025-11-19 08:09:11');

-- --------------------------------------------------------

--
-- Stand-in structure for view `reservation_costs`
-- (See below for the actual view)
--
CREATE TABLE `reservation_costs` (
`reservation_id` int(11)
,`rate_per_hour` decimal(10,2)
,`minimum_fee` decimal(10,2)
,`rate_per_person` decimal(10,2)
,`hours` decimal(4,2)
,`persons` int(11)
,`room_charge` decimal(14,4)
,`person_charge` decimal(20,2)
,`equipment_charge` decimal(46,4)
,`total_cost` decimal(47,4)
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
(29, 16, 2, 1, 150.00),
(30, 17, 1, 1, 150.00),
(31, 17, 2, 1, 150.00),
(32, 18, 2, 1, 150.00),
(33, 18, 1, 1, 150.00),
(34, 19, 1, 1, 150.00),
(35, 19, 2, 1, 150.00),
(36, 20, 1, 1, 150.00),
(37, 20, 2, 1, 150.00),
(38, 21, 1, 1, 150.00),
(39, 21, 2, 1, 150.00),
(40, 22, 1, 1, 150.00),
(41, 22, 2, 1, 150.00);

-- --------------------------------------------------------

--
-- Table structure for table `room_types`
--

CREATE TABLE `room_types` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `rate_per_hour` decimal(10,2) DEFAULT NULL,
  `minimum_fee` decimal(10,2) DEFAULT 0.00,
  `rate_per_person` decimal(10,2) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `room_types`
--

INSERT INTO `room_types` (`id`, `code`, `name`, `rate_per_hour`, `minimum_fee`, `rate_per_person`, `created_at`) VALUES
(7, 'STUDY', 'Study Room', 50.00, 75.00, 50.00, '2025-11-19 06:30:27'),
(8, 'GATHERING', 'Gathering Room', 300.00, 150.00, 75.00, '2025-11-19 06:30:27'),
(9, 'EVENT', 'Event Room', 500.00, 250.00, 100.00, '2025-11-19 06:30:27');

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

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `reservation_costs`  AS SELECT `r`.`id` AS `reservation_id`, `rt`.`rate_per_hour` AS `rate_per_hour`, `rt`.`minimum_fee` AS `minimum_fee`, `rt`.`rate_per_person` AS `rate_per_person`, `r`.`hours` AS `hours`, `r`.`persons` AS `persons`, greatest(`rt`.`minimum_fee`,`rt`.`rate_per_hour` * `r`.`hours`) AS `room_charge`, `rt`.`rate_per_person`* `r`.`persons` AS `person_charge`, coalesce(sum(`re`.`quantity` * `e`.`rate_per_hour` * `r`.`hours`),0) AS `equipment_charge`, greatest(`rt`.`minimum_fee`,`rt`.`rate_per_hour` * `r`.`hours`) + `rt`.`rate_per_person` * `r`.`persons` + coalesce(sum(`re`.`quantity` * `e`.`rate_per_hour` * `r`.`hours`),0) AS `total_cost` FROM (((`reservations` `r` join `room_types` `rt` on(`rt`.`id` = `r`.`room_type_id`)) left join `reservation_equipment` `re` on(`re`.`reservation_id` = `r`.`id`)) left join `equipment` `e` on(`e`.`id` = `re`.`equipment_id`)) GROUP BY `r`.`id` ;

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
-- Indexes for table `room_types`
--
ALTER TABLE `room_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

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
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `reservation_actions`
--
ALTER TABLE `reservation_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `reservation_equipment`
--
ALTER TABLE `reservation_equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `room_types`
--
ALTER TABLE `room_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
