-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 11, 2025 at 10:33 AM
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
-- Database: `disaster_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `disasters`
--

CREATE TABLE `disasters` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `type` enum('flood','earthquake','typhoon','landslide','accident') NOT NULL,
  `occurred_at` datetime NOT NULL,
  `intensity_signal` varchar(100) NOT NULL,
  `latitude` decimal(10,6) NOT NULL,
  `longitude` decimal(10,6) NOT NULL,
  `radius_m` int(10) UNSIGNED NOT NULL DEFAULT 250,
  `address` varchar(255) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `disasters`
--

INSERT INTO `disasters` (`id`, `name`, `type`, `occurred_at`, `intensity_signal`, `latitude`, `longitude`, `radius_m`, `address`, `description`, `created_at`) VALUES
(15, 'Ilod Car Accident', 'accident', '2025-12-11 17:15:00', '2 Vehicles', 16.370534, 120.685190, 50, 'Ilod Sontown Colleges', '2 Vehicular Collision', '2025-12-11 09:15:02');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admindisaster@baguio.gov', '$2b$12$xMb4whOsDgqoWgriW5/ZwOXwXVGPN6vh6QnrEZ77tCD6AYtyko82m', 'admin', '2025-11-01 06:40:13'),
(2, 'test@gmail.com', '$2y$10$wX81zL78nSoGuMur9NDToOLNFon43m1kSCNl52KcmuNQ7X13GMh6W', 'user', '2025-11-01 06:44:23'),
(4, 'test1@gmail.com', '$2y$10$0RBfPHeXMqgZq5OoIrwH0OeNu2oHaLDLVbPnTYKcM6wf2XPX.Bywu', 'user', '2025-11-01 06:47:38'),
(5, 'sabrina@gmail.com', '$2y$10$K9zgOlPr.jmFwMRuDMM0vuZ7X/tDK7jk0SiqX67hWNd5iLqu6HQd2', 'user', '2025-12-04 15:19:53'),
(6, 'johndoe@gmail.com', '$2y$10$4.GUsuYyVr7btqhJ814JDOKo.JFtI6Wq1g7pSE6aJJA6QDUevDWNm', 'user', '2025-12-08 07:57:25'),
(7, 'decibel@gmail.com', '$2y$10$JD7//39Z/FTqu/BiKsqW9.ChvuhS6qoFk11WVROfmv7Rsis4Qj...', 'user', '2025-12-11 09:26:47');

-- --------------------------------------------------------

--
-- Table structure for table `user_reports`
--

CREATE TABLE `user_reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `occurred_at` datetime NOT NULL,
  `intensity_signal` varchar(100) NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `radius_m` int(11) NOT NULL DEFAULT 250,
  `address` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `submitted_at` datetime NOT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `admin_notes` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_reports`
--

INSERT INTO `user_reports` (`id`, `user_id`, `name`, `type`, `occurred_at`, `intensity_signal`, `latitude`, `longitude`, `radius_m`, `address`, `description`, `status`, `submitted_at`, `reviewed_at`, `admin_notes`) VALUES
(1, 2, 'Virac Flooding', 'flood', '2025-12-08 16:10:00', '15 cm Flooding', 16.3659330, 120.6537440, 100, 'Virac', 'Ongoing Flooding at Virac', 'approved', '2025-12-08 16:11:00', '2025-12-08 21:44:08', ''),
(5, 7, 'Landslide at Ucab Road', 'landslide', '2025-12-11 17:28:00', 'Rockslides', 16.3962060, 120.6576060, 100, '0', '2 Households affected', 'rejected', '2025-12-11 17:28:25', '2025-12-11 17:29:58', 'False Alarm');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `disasters`
--
ALTER TABLE `disasters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_occurred_at` (`occurred_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_reports`
--
ALTER TABLE `user_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_submitted_at` (`submitted_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `disasters`
--
ALTER TABLE `disasters`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_reports`
--
ALTER TABLE `user_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_reports`
--
ALTER TABLE `user_reports`
  ADD CONSTRAINT `user_reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
