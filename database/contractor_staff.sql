-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 11, 2026 at 08:36 AM
-- Server version: 11.4.5-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `legatura`
--

-- --------------------------------------------------------

--
-- Table structure for table `contractor_staff`
--

CREATE TABLE `contractor_staff` (
  `staff_id` int(11) NOT NULL,
  `contractor_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `company_role` enum('manager','engineer','others','architect','representative') DEFAULT NULL,
  `role_if_others` varchar(255) DEFAULT NULL,
  `company_role_before` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_suspended` tinyint(4) NOT NULL DEFAULT 0,
  `suspension_until` date DEFAULT NULL,
  `suspension_reason` text DEFAULT NULL,
  `deletion_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contractor_staff`
--

INSERT INTO `contractor_staff` (`staff_id`, `contractor_id`, `owner_id`, `company_role`, `role_if_others`, `company_role_before`, `is_active`, `is_suspended`, `suspension_until`, `suspension_reason`, `deletion_reason`, `created_at`) VALUES
(1, 1, 6, 'engineer', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-10 16:08:59'),
(2, 1, 8, 'manager', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-10 16:08:59'),
(3, 2, 10, 'architect', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-10 16:08:59'),
(4, 2, 12, 'representative', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-10 16:08:59'),
(5, 3, 14, 'engineer', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-10 16:08:59'),
(6, 4, 16, 'representative', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-10 16:08:59'),
(7, 1, 18, 'others', 'Mcdo Crewmate', NULL, 1, 0, NULL, NULL, NULL, '2026-03-10 16:08:59'),
(8, 1, 20, 'engineer', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-10 16:08:59'),
(9, 1, 4, 'others', 'Jollibee Crew', NULL, 0, 0, NULL, NULL, NULL, '2026-03-10 23:05:58'),
(10, 1, 35, 'engineer', NULL, NULL, 0, 1, '2026-03-31', 'sdsadasdasdasda', NULL, '2026-03-10 23:05:58'),
(11, 1, 37, 'representative', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-10 23:11:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contractor_staff`
--
ALTER TABLE `contractor_staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD KEY `fk_staff_company` (`contractor_id`),
  ADD KEY `fk_staff_owner` (`owner_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contractor_staff`
--
ALTER TABLE `contractor_staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contractor_staff`
--
ALTER TABLE `contractor_staff`
  ADD CONSTRAINT `fk_staff_company` FOREIGN KEY (`contractor_id`) REFERENCES `contractors` (`contractor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_staff_owner` FOREIGN KEY (`owner_id`) REFERENCES `property_owners` (`owner_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
