-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 13, 2025 at 01:48 PM
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
-- Database: `batcave`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `reservation_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time DEFAULT NULL,
  `hours` decimal(4,2) NOT NULL,
  `persons` int(11) NOT NULL,
  `projector` tinyint(1) DEFAULT 0,
  `speaker_mike` tinyint(1) DEFAULT 0,
  `booking_fee` decimal(10,2) NOT NULL,
  `equipment_fee` decimal(10,2) NOT NULL,
  `total_fee` decimal(10,2) NOT NULL,
  `is_finished` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` enum('Study','Gathering') NOT NULL DEFAULT 'Study'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `student_id`, `name`, `email`, `phone`, `reservation_date`, `start_time`, `end_time`, `hours`, `persons`, `projector`, `speaker_mike`, `booking_fee`, `equipment_fee`, `total_fee`, `is_finished`, `created_at`, `type`) VALUES
(1, '5', 'Lander', 'medinalander66@gmail.com', '09452180407', '2025-11-01', '14:01:00', '10:01:00', 20.00, 20, 1, 1, 1000.00, 6000.00, 7000.00, 0, '2025-11-09 12:52:02', 'Study'),
(2, '6', 'Lander', 'medinalander66@gmail.com', '09452180407', '2025-01-01', '14:02:00', '10:02:00', 20.00, 10, 1, 1, 1000.00, 6000.00, 7000.00, 0, '2025-11-09 12:52:41', 'Gathering'),
(3, '5', 'Lander', 'medinalander66@gmail.com', '09452180407', '2025-01-01', '14:01:00', '10:01:00', 20.00, 10, 1, 1, 1000.00, 6000.00, 7000.00, 0, '2025-11-09 12:53:20', 'Study'),
(4, '5', 'Lander', 'medinalander66@gmail.com', '09452180407', '2025-01-01', '14:01:00', '10:01:00', 20.00, 10, 1, 1, 1000.00, 6000.00, 7000.00, 0, '2025-11-09 12:55:37', 'Study'),
(5, '5', 'Lander', 'medinalander66@gmail.com', '09452180407', '2025-01-01', '14:01:00', '00:01:00', 10.00, 10, 1, 1, 500.00, 3000.00, 3500.00, 0, '2025-11-09 12:57:54', 'Gathering'),
(6, '6', 'Lander', 'medinalander33@gmail.com', '09452180407', '2025-02-02', '14:01:00', '10:01:00', 20.00, 10, 1, 1, 1000.00, 6000.00, 7000.00, 0, '2025-11-09 13:06:39', 'Study'),
(7, '6', 'Lander', 'medinalander33@gmail.com', '09452180407', '2025-02-02', '14:01:00', '10:01:00', 20.00, 10, 1, 1, 1000.00, 6000.00, 7000.00, 0, '2025-11-09 13:06:43', 'Study'),
(8, '2', 'Lander', 'medinalander66@gmail.com', '09452180407', '2025-02-02', '14:00:00', '00:00:00', 10.00, 1, 1, 1, 500.00, 3000.00, 3500.00, 0, '2025-11-09 13:07:44', 'Gathering'),
(9, '7', 'Lander', 'medinalander66@gmail.com', '09452180407', '2025-02-02', '14:02:00', '00:02:00', 10.00, 10, 1, 1, 500.00, 3000.00, 3500.00, 0, '2025-11-09 13:11:26', 'Study'),
(10, '7', 'Lander', 'medinalander66@gmail.com', '09452180407', '2025-02-02', '14:02:00', '00:02:00', 10.00, 10, 1, 1, 500.00, 3000.00, 3500.00, 0, '2025-11-09 13:15:55', 'Study'),
(11, '6', 'Lander', 'medinalander66@gmail.com', '09452180407', '2025-03-03', '13:00:00', '23:00:00', 10.00, 10, 1, 1, 500.00, 3000.00, 3500.00, 0, '2025-11-09 13:17:15', 'Gathering'),
(12, '5', 'Lander', 'medinalander66@gmail.com', '09452180407', '2025-03-03', '13:00:00', '23:00:00', 10.00, 10, 1, 1, 500.00, 3000.00, 3500.00, 0, '2025-11-09 13:17:57', 'Study'),
(13, '3', 'Medina', 'francienaumento66@gmail.com', '09452180407', '2025-04-04', '14:00:00', '19:00:00', 5.00, 5, 1, 1, 250.00, 1500.00, 1750.00, 0, '2025-11-09 13:24:11', 'Study'),
(14, '1', 'Lander', 'medinalander66@gmail.com', '09452180407', '2025-04-04', '14:00:00', '19:00:00', 5.00, 5, 1, 1, 250.00, 1500.00, 1750.00, 0, '2025-11-09 13:24:39', 'Gathering'),
(15, '1', 'Medina', 'medinalander33@gmail.com', '09452180407', '2025-05-05', '13:00:00', '23:00:00', 10.00, 5, 1, 1, 500.00, 3000.00, 3500.00, 0, '2025-11-09 13:31:09', 'Study'),
(16, '1', 'Medina', 'medinalander33@gmail.com', '09452180407', '2025-05-05', '13:00:00', '23:00:00', 10.00, 5, 1, 1, 500.00, 3000.00, 3500.00, 0, '2025-11-09 13:31:12', 'Study'),
(17, '1', 'Medina', 'medinalander33@gmail.com', '09452180407', '2025-05-05', '13:00:00', '23:00:00', 10.00, 5, 1, 1, 500.00, 3000.00, 3500.00, 0, '2025-11-09 13:31:20', 'Study'),
(18, '1', 'Medina', 'medinalander33@gmail.com', '09452180407', '2025-05-05', '13:00:00', '23:00:00', 10.00, 5, 1, 1, 500.00, 3000.00, 3500.00, 0, '2025-11-09 13:31:41', 'Study'),
(19, '3', 'Lander', 'medinalander66@gmail.com', '09452180407', '2025-06-06', '13:00:00', '18:00:00', 5.00, 5, 1, 1, 250.00, 1500.00, 1750.00, 0, '2025-11-09 13:33:22', 'Study'),
(20, '7', 'Lander', 'medinalander66@gmail.com', '09452180407', '2025-06-06', '13:00:00', '18:00:00', 5.00, 5, 1, 1, 250.00, 1500.00, 1750.00, 0, '2025-11-09 13:33:48', 'Gathering'),
(21, '5', 'Medina', 'medinalander66@gmail.com', '09452180407', '2025-08-08', '13:00:00', '18:00:00', 5.00, 5, 1, 1, 250.00, 1500.00, 1750.00, 0, '2025-11-09 13:44:26', 'Study'),
(22, '1', 'Medina', 'medinalander66@gmail.com', '09452180407', '2025-09-09', '13:00:00', '18:00:00', 5.00, 5, 1, 1, 250.00, 1500.00, 1750.00, 0, '2025-11-09 13:47:07', 'Gathering'),
(23, '2', 'Lander', 'medinalander33@gmail.com', '09452180407', '2025-10-10', '13:00:00', '18:00:00', 5.00, 10, 1, 1, 250.00, 1500.00, 1750.00, 0, '2025-11-09 14:06:22', 'Study'),
(24, '2', 'Lander', 'medinalander33@gmail.com', '09452180407', '2025-10-10', '13:00:00', '18:00:00', 5.00, 10, 1, 1, 250.00, 1500.00, 1750.00, 0, '2025-11-09 14:06:25', 'Study'),
(25, '6', 'Lander', 'medinalander66@gmail.com', '09452180407', '2025-11-11', '13:00:00', '17:00:00', 4.00, 5, 1, 1, 200.00, 1200.00, 1400.00, 0, '2025-11-09 14:23:34', 'Study'),
(26, '6', 'Lander', 'medinalander66@gmail.com', '09452180407', '2025-11-11', '13:00:00', '17:00:00', 4.00, 5, 1, 1, 200.00, 1200.00, 1400.00, 0, '2025-11-09 14:24:31', 'Study'),
(27, '6', 'Lander', 'medinalander66@gmail.com', '09452180407', '2025-11-11', '13:00:00', '17:00:00', 4.00, 5, 1, 1, 200.00, 1200.00, 1400.00, 0, '2025-11-09 14:24:36', 'Study'),
(28, '6', 'Lander', 'medinalander66@gmail.com', '09452180407', '2025-11-11', '13:00:00', '17:00:00', 4.00, 5, 1, 1, 200.00, 1200.00, 1400.00, 0, '2025-11-09 14:34:03', 'Study'),
(29, '6', 'Lander', 'medinalander66@gmail.com', '09452180407', '2025-12-12', '13:00:00', '18:00:00', 5.00, 10, 1, 1, 250.00, 1500.00, 1750.00, 0, '2025-11-09 14:44:12', 'Study'),
(30, '6', 'Lander', 'medinalander66@gmail.com', '09452180407', '2025-12-12', '13:00:00', '18:00:00', 5.00, 10, 1, 1, 250.00, 1500.00, 1750.00, 0, '2025-11-09 14:44:19', 'Study'),
(31, '6', 'Medina', 'medinalander3@gmail.com', '09452180407', '2025-11-10', '13:00:00', '14:00:00', 1.00, 5, 0, 0, 50.00, 0.00, 50.00, 0, '2025-11-09 14:52:26', 'Study'),
(32, '6', 'Medina', 'medinalander3@gmail.com', '09452180407', '2025-11-10', '13:00:00', '14:00:00', 1.00, 5, 0, 0, 50.00, 0.00, 50.00, 0, '2025-11-09 15:00:51', 'Study');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
