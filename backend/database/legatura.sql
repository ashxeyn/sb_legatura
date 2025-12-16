-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 16, 2025 at 09:41 AM
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
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`admin_id`, `username`, `email`, `password_hash`, `last_name`, `middle_name`, `first_name`, `is_active`, `created_at`) VALUES
(1, 'admin123', 'admin@gmail.com', '$2y$12$wN6x17yJegWeKIUj3Lq8O.sAG6QfX.5OPQ13k0xSAY./xrDhej5Uq', 'admin', 'admin', 'admin', 1, '2025-10-25 15:19:23');

-- --------------------------------------------------------

--
-- Table structure for table `bids`
--

CREATE TABLE `bids` (
  `bid_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `contractor_id` int(11) NOT NULL,
  `proposed_cost` decimal(15,2) NOT NULL,
  `estimated_timeline` int(11) NOT NULL,
  `contractor_notes` text DEFAULT NULL,
  `bid_status` enum('submitted','under_review','accepted','rejected','cancelled') DEFAULT 'submitted',
  `reason` text DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT current_timestamp(),
  `decision_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bid_files`
--

CREATE TABLE `bid_files` (
  `file_id` int(11) NOT NULL,
  `bid_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contractors`
--

CREATE TABLE `contractors` (
  `contractor_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `company_start_date` date NOT NULL DEFAULT current_timestamp(),
  `years_of_experience` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `contractor_type_other` varchar(200) DEFAULT NULL,
  `services_offered` text NOT NULL,
  `business_address` text NOT NULL,
  `company_email` varchar(100) NOT NULL,
  `company_phone` varchar(20) NOT NULL,
  `company_website` varchar(255) DEFAULT NULL,
  `company_social_media` varchar(255) DEFAULT NULL,
  `company_description` text DEFAULT NULL,
  `picab_number` varchar(100) NOT NULL,
  `picab_category` enum('AAAA','AAA','AA','A','B','C','D','Trade/E') NOT NULL,
  `picab_expiration_date` date NOT NULL,
  `business_permit_number` varchar(100) NOT NULL,
  `business_permit_city` varchar(100) NOT NULL,
  `business_permit_expiration` date NOT NULL,
  `tin_business_reg_number` varchar(100) NOT NULL,
  `dti_sec_registration_photo` varchar(255) NOT NULL,
  `verification_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `verification_date` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `completed_projects` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contractors`
--

INSERT INTO `contractors` (`contractor_id`, `user_id`, `company_name`, `company_start_date`, `years_of_experience`, `type_id`, `contractor_type_other`, `services_offered`, `business_address`, `company_email`, `company_phone`, `company_website`, `company_social_media`, `company_description`, `picab_number`, `picab_category`, `picab_expiration_date`, `business_permit_number`, `business_permit_city`, `business_permit_expiration`, `tin_business_reg_number`, `dti_sec_registration_photo`, `verification_status`, `verification_date`, `rejection_reason`, `completed_projects`, `created_at`, `updated_at`) VALUES
(1687, 1, 'Main Construction Co 1', '2011-12-16', 14, 8, NULL, 'General Construction', 'Zamboanga City', 'company1@example.com', '09170000001', NULL, NULL, NULL, 'PCAB-23362', 'A', '2026-12-31', 'BP-1', 'Zamboanga', '2026-01-01', 'TIN-1', 'dti_cert.jpg', 'approved', '2025-08-01 07:49:09', NULL, 5, '2025-07-30 07:49:09', '2025-12-16 08:41:07'),
(1688, 2, 'Main Construction Co 2', '2000-12-16', 25, 5, NULL, 'General Construction', 'Zamboanga City', 'company2@example.com', '09170000002', NULL, NULL, NULL, 'PCAB-22358', 'A', '2026-12-31', 'BP-2', 'Zamboanga', '2026-01-01', 'TIN-2', 'dti_cert.jpg', 'approved', '2025-10-23 07:49:09', NULL, 43, '2025-10-21 07:49:09', '2025-12-16 08:41:07'),
(1689, 3, 'Main Construction Co 3', '2005-12-16', 20, 6, NULL, 'General Construction', 'Zamboanga City', 'company3@example.com', '09170000003', NULL, NULL, NULL, 'PCAB-11115', 'A', '2026-12-31', 'BP-3', 'Zamboanga', '2026-01-01', 'TIN-3', 'dti_cert.jpg', 'approved', '2024-12-19 07:49:09', NULL, 28, '2024-12-17 07:49:09', '2025-12-16 08:41:07'),
(1690, 4, 'Main Construction Co 4', '1998-12-16', 27, 4, NULL, 'General Construction', 'Zamboanga City', 'company4@example.com', '09170000004', NULL, NULL, NULL, 'PCAB-96931', 'A', '2026-12-31', 'BP-4', 'Zamboanga', '2026-01-01', 'TIN-4', 'dti_cert.jpg', 'approved', '2025-01-26 07:49:09', NULL, 23, '2025-01-24 07:49:09', '2025-12-16 08:41:07'),
(1691, 5, 'Main Construction Co 5', '2013-12-16', 12, 1, NULL, 'General Construction', 'Zamboanga City', 'company5@example.com', '09170000005', NULL, NULL, NULL, 'PCAB-75904', 'A', '2026-12-31', 'BP-5', 'Zamboanga', '2026-01-01', 'TIN-5', 'dti_cert.jpg', 'approved', NULL, '', 4, '2025-02-19 07:49:09', '2025-12-16 08:41:07'),
(1692, 6, 'Main Construction Co 6', '2014-12-16', 11, 9, NULL, 'General Construction', 'Zamboanga City', 'company6@example.com', '09170000006', NULL, NULL, NULL, 'PCAB-79857', 'A', '2026-12-31', 'BP-6', 'Zamboanga', '2026-01-01', 'TIN-6', 'dti_cert.jpg', 'approved', '2025-04-23 07:49:09', NULL, 24, '2025-04-21 07:49:09', '2025-12-16 08:41:07'),
(1693, 7, 'Main Construction Co 7', '2008-12-16', 17, 6, NULL, 'General Construction', 'Zamboanga City', 'company7@example.com', '09170000007', NULL, NULL, NULL, 'PCAB-39566', 'A', '2026-12-31', 'BP-7', 'Zamboanga', '2026-01-01', 'TIN-7', 'dti_cert.jpg', 'approved', '2025-11-27 07:49:09', NULL, 38, '2025-11-25 07:49:09', '2025-12-16 08:41:07'),
(1694, 8, 'Main Construction Co 8', '2003-12-16', 22, 7, NULL, 'General Construction', 'Zamboanga City', 'company8@example.com', '09170000008', NULL, NULL, NULL, 'PCAB-54580', 'A', '2026-12-31', 'BP-8', 'Zamboanga', '2026-01-01', 'TIN-8', 'dti_cert.jpg', 'approved', '2025-10-10 07:49:09', NULL, 19, '2025-10-08 07:49:09', '2025-12-16 08:41:07'),
(1695, 9, 'Main Construction Co 9', '1999-12-16', 26, 9, NULL, 'General Construction', 'Zamboanga City', 'company9@example.com', '09170000009', NULL, NULL, NULL, 'PCAB-34347', 'A', '2026-12-31', 'BP-9', 'Zamboanga', '2026-01-01', 'TIN-9', 'dti_cert.jpg', 'approved', '2025-03-28 07:49:09', NULL, 47, '2025-03-26 07:49:09', '2025-12-16 08:41:07'),
(1696, 10, 'Main Construction Co 10', '2018-12-16', 7, 6, NULL, 'General Construction', 'Zamboanga City', 'company10@example.com', '09170000010', NULL, NULL, NULL, 'PCAB-69751', 'A', '2026-12-31', 'BP-10', 'Zamboanga', '2026-01-01', 'TIN-10', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 37, '2025-09-23 07:49:09', '2025-12-16 08:41:07'),
(1697, 11, 'Main Construction Co 11', '2010-12-16', 15, 9, NULL, 'General Construction', 'Zamboanga City', 'company11@example.com', '09170000011', NULL, NULL, NULL, 'PCAB-70939', 'A', '2026-12-31', 'BP-11', 'Zamboanga', '2026-01-01', 'TIN-11', 'dti_cert.jpg', 'approved', '2025-05-12 07:49:09', NULL, 20, '2025-05-10 07:49:09', '2025-12-16 08:41:07'),
(1698, 12, 'Main Construction Co 12', '2003-12-16', 22, 1, NULL, 'General Construction', 'Zamboanga City', 'company12@example.com', '09170000012', NULL, NULL, NULL, 'PCAB-10680', 'A', '2026-12-31', 'BP-12', 'Zamboanga', '2026-01-01', 'TIN-12', 'dti_cert.jpg', 'approved', '2025-09-11 07:49:09', NULL, 38, '2025-09-09 07:49:09', '2025-12-16 08:41:07'),
(1699, 13, 'Main Construction Co 13', '2021-12-16', 4, 1, NULL, 'General Construction', 'Zamboanga City', 'company13@example.com', '09170000013', NULL, NULL, NULL, 'PCAB-10739', 'A', '2026-12-31', 'BP-13', 'Zamboanga', '2026-01-01', 'TIN-13', 'dti_cert.jpg', 'approved', '2025-07-08 07:49:09', NULL, 18, '2025-07-06 07:49:09', '2025-12-16 08:41:07'),
(1700, 14, 'Main Construction Co 14', '2012-12-16', 13, 4, NULL, 'General Construction', 'Zamboanga City', 'company14@example.com', '09170000014', NULL, NULL, NULL, 'PCAB-81118', 'A', '2026-12-31', 'BP-14', 'Zamboanga', '2026-01-01', 'TIN-14', 'dti_cert.jpg', 'approved', '2025-02-22 07:49:09', NULL, 11, '2025-02-20 07:49:09', '2025-12-16 08:41:07'),
(1701, 15, 'Main Construction Co 15', '2003-12-16', 22, 5, NULL, 'General Construction', 'Zamboanga City', 'company15@example.com', '09170000015', NULL, NULL, NULL, 'PCAB-66597', 'A', '2026-12-31', 'BP-15', 'Zamboanga', '2026-01-01', 'TIN-15', 'dti_cert.jpg', 'pending', NULL, '', 12, '2025-01-08 07:49:09', '2025-12-16 08:41:07'),
(1702, 16, 'Main Construction Co 16', '2012-12-16', 13, 2, NULL, 'General Construction', 'Zamboanga City', 'company16@example.com', '09170000016', NULL, NULL, NULL, 'PCAB-19082', 'A', '2026-12-31', 'BP-16', 'Zamboanga', '2026-01-01', 'TIN-16', 'dti_cert.jpg', 'approved', '2025-05-17 07:49:09', NULL, 25, '2025-05-15 07:49:09', '2025-12-16 08:41:07'),
(1703, 17, 'Main Construction Co 17', '2000-12-16', 25, 5, NULL, 'General Construction', 'Zamboanga City', 'company17@example.com', '09170000017', NULL, NULL, NULL, 'PCAB-77703', 'A', '2026-12-31', 'BP-17', 'Zamboanga', '2026-01-01', 'TIN-17', 'dti_cert.jpg', 'approved', '2025-05-01 07:49:09', NULL, 12, '2025-04-29 07:49:09', '2025-12-16 08:41:07'),
(1704, 18, 'Main Construction Co 18', '1996-12-16', 29, 2, NULL, 'General Construction', 'Zamboanga City', 'company18@example.com', '09170000018', NULL, NULL, NULL, 'PCAB-36965', 'A', '2026-12-31', 'BP-18', 'Zamboanga', '2026-01-01', 'TIN-18', 'dti_cert.jpg', 'approved', '2025-01-06 07:49:09', NULL, 9, '2025-01-04 07:49:09', '2025-12-16 08:41:07'),
(1705, 19, 'Main Construction Co 19', '2016-12-16', 9, 2, NULL, 'General Construction', 'Zamboanga City', 'company19@example.com', '09170000019', NULL, NULL, NULL, 'PCAB-34306', 'A', '2026-12-31', 'BP-19', 'Zamboanga', '2026-01-01', 'TIN-19', 'dti_cert.jpg', 'approved', '2025-05-31 07:49:09', NULL, 28, '2025-05-29 07:49:09', '2025-12-16 08:41:07'),
(1706, 20, 'Main Construction Co 20', '2006-12-16', 19, 3, NULL, 'General Construction', 'Zamboanga City', 'company20@example.com', '09170000020', NULL, NULL, NULL, 'PCAB-67093', 'A', '2026-12-31', 'BP-20', 'Zamboanga', '2026-01-01', 'TIN-20', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 6, '2025-10-18 07:49:09', '2025-12-16 08:41:07'),
(1707, 21, 'Main Construction Co 21', '2019-12-16', 6, 2, NULL, 'General Construction', 'Zamboanga City', 'company21@example.com', '09170000021', NULL, NULL, NULL, 'PCAB-39542', 'A', '2026-12-31', 'BP-21', 'Zamboanga', '2026-01-01', 'TIN-21', 'dti_cert.jpg', 'approved', '2025-01-24 07:49:09', NULL, 10, '2025-01-22 07:49:09', '2025-12-16 08:41:07'),
(1708, 22, 'Main Construction Co 22', '2024-12-16', 1, 3, NULL, 'General Construction', 'Zamboanga City', 'company22@example.com', '09170000022', NULL, NULL, NULL, 'PCAB-34168', 'A', '2026-12-31', 'BP-22', 'Zamboanga', '2026-01-01', 'TIN-22', 'dti_cert.jpg', 'approved', '2025-10-18 07:49:09', NULL, 11, '2025-10-16 07:49:09', '2025-12-16 08:41:07'),
(1709, 23, 'Main Construction Co 23', '2008-12-16', 17, 6, NULL, 'General Construction', 'Zamboanga City', 'company23@example.com', '09170000023', NULL, NULL, NULL, 'PCAB-42469', 'A', '2026-12-31', 'BP-23', 'Zamboanga', '2026-01-01', 'TIN-23', 'dti_cert.jpg', 'approved', '2025-03-18 07:49:09', NULL, 15, '2025-03-16 07:49:09', '2025-12-16 08:41:07'),
(1710, 24, 'Main Construction Co 24', '2002-12-16', 23, 5, NULL, 'General Construction', 'Zamboanga City', 'company24@example.com', '09170000024', NULL, NULL, NULL, 'PCAB-46764', 'A', '2026-12-31', 'BP-24', 'Zamboanga', '2026-01-01', 'TIN-24', 'dti_cert.jpg', 'approved', '2025-08-27 07:49:09', NULL, 32, '2025-08-25 07:49:09', '2025-12-16 08:41:07'),
(1711, 25, 'Main Construction Co 25', '2021-12-16', 4, 1, NULL, 'General Construction', 'Zamboanga City', 'company25@example.com', '09170000025', NULL, NULL, NULL, 'PCAB-57726', 'A', '2026-12-31', 'BP-25', 'Zamboanga', '2026-01-01', 'TIN-25', 'dti_cert.jpg', 'pending', NULL, NULL, 48, '2025-05-01 07:49:09', '2025-12-16 08:41:07'),
(1712, 26, 'Main Construction Co 26', '2017-12-16', 8, 2, NULL, 'General Construction', 'Zamboanga City', 'company26@example.com', '09170000026', NULL, NULL, NULL, 'PCAB-45837', 'A', '2026-12-31', 'BP-26', 'Zamboanga', '2026-01-01', 'TIN-26', 'dti_cert.jpg', 'approved', '2025-08-16 07:49:09', NULL, 6, '2025-08-14 07:49:09', '2025-12-16 08:41:07'),
(1713, 27, 'Main Construction Co 27', '1998-12-16', 27, 5, NULL, 'General Construction', 'Zamboanga City', 'company27@example.com', '09170000027', NULL, NULL, NULL, 'PCAB-36243', 'A', '2026-12-31', 'BP-27', 'Zamboanga', '2026-01-01', 'TIN-27', 'dti_cert.jpg', 'approved', '2025-11-08 07:49:09', NULL, 21, '2025-11-06 07:49:09', '2025-12-16 08:41:07'),
(1714, 28, 'Main Construction Co 28', '2003-12-16', 22, 8, NULL, 'General Construction', 'Zamboanga City', 'company28@example.com', '09170000028', NULL, NULL, NULL, 'PCAB-96216', 'A', '2026-12-31', 'BP-28', 'Zamboanga', '2026-01-01', 'TIN-28', 'dti_cert.jpg', 'approved', '2025-05-03 07:49:09', NULL, 21, '2025-05-01 07:49:09', '2025-12-16 08:41:07'),
(1715, 29, 'Main Construction Co 29', '1997-12-16', 28, 8, NULL, 'General Construction', 'Zamboanga City', 'company29@example.com', '09170000029', NULL, NULL, NULL, 'PCAB-84101', 'A', '2026-12-31', 'BP-29', 'Zamboanga', '2026-01-01', 'TIN-29', 'dti_cert.jpg', 'approved', '2025-07-21 07:49:09', NULL, 19, '2025-07-19 07:49:09', '2025-12-16 08:41:07'),
(1716, 30, 'Main Construction Co 30', '2010-12-16', 15, 6, NULL, 'General Construction', 'Zamboanga City', 'company30@example.com', '09170000030', NULL, NULL, NULL, 'PCAB-57206', 'A', '2026-12-31', 'BP-30', 'Zamboanga', '2026-01-01', 'TIN-30', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 20, '2025-05-24 07:49:09', '2025-12-16 08:41:07'),
(1717, 31, 'Main Construction Co 31', '2006-12-16', 19, 2, NULL, 'General Construction', 'Zamboanga City', 'company31@example.com', '09170000031', NULL, NULL, NULL, 'PCAB-68035', 'A', '2026-12-31', 'BP-31', 'Zamboanga', '2026-01-01', 'TIN-31', 'dti_cert.jpg', 'approved', '2025-11-10 07:49:09', NULL, 42, '2025-11-08 07:49:09', '2025-12-16 08:41:07'),
(1718, 32, 'Main Construction Co 32', '2006-12-16', 19, 4, NULL, 'General Construction', 'Zamboanga City', 'company32@example.com', '09170000032', NULL, NULL, NULL, 'PCAB-72477', 'A', '2026-12-31', 'BP-32', 'Zamboanga', '2026-01-01', 'TIN-32', 'dti_cert.jpg', 'approved', '2025-10-01 07:49:09', NULL, 21, '2025-09-29 07:49:09', '2025-12-16 08:41:07'),
(1719, 33, 'Main Construction Co 33', '2017-12-16', 8, 6, NULL, 'General Construction', 'Zamboanga City', 'company33@example.com', '09170000033', NULL, NULL, NULL, 'PCAB-44442', 'A', '2026-12-31', 'BP-33', 'Zamboanga', '2026-01-01', 'TIN-33', 'dti_cert.jpg', 'approved', '2025-02-06 07:49:09', NULL, 1, '2025-02-04 07:49:09', '2025-12-16 08:41:07'),
(1720, 34, 'Main Construction Co 34', '2012-12-16', 13, 6, NULL, 'General Construction', 'Zamboanga City', 'company34@example.com', '09170000034', NULL, NULL, NULL, 'PCAB-88849', 'A', '2026-12-31', 'BP-34', 'Zamboanga', '2026-01-01', 'TIN-34', 'dti_cert.jpg', 'approved', '2025-08-20 07:49:09', NULL, 8, '2025-08-18 07:49:09', '2025-12-16 08:41:07'),
(1721, 35, 'Main Construction Co 35', '2015-12-16', 10, 1, NULL, 'General Construction', 'Zamboanga City', 'company35@example.com', '09170000035', NULL, NULL, NULL, 'PCAB-23798', 'A', '2026-12-31', 'BP-35', 'Zamboanga', '2026-01-01', 'TIN-35', 'dti_cert.jpg', 'pending', NULL, NULL, 42, '2025-04-22 07:49:09', '2025-12-16 08:41:07'),
(1722, 36, 'Main Construction Co 36', '2012-12-16', 13, 8, NULL, 'General Construction', 'Zamboanga City', 'company36@example.com', '09170000036', NULL, NULL, NULL, 'PCAB-33648', 'A', '2026-12-31', 'BP-36', 'Zamboanga', '2026-01-01', 'TIN-36', 'dti_cert.jpg', 'approved', '2025-09-24 07:49:09', NULL, 44, '2025-09-22 07:49:09', '2025-12-16 08:41:07'),
(1723, 37, 'Main Construction Co 37', '2024-12-16', 1, 2, NULL, 'General Construction', 'Zamboanga City', 'company37@example.com', '09170000037', NULL, NULL, NULL, 'PCAB-32151', 'A', '2026-12-31', 'BP-37', 'Zamboanga', '2026-01-01', 'TIN-37', 'dti_cert.jpg', 'approved', '2025-05-12 07:49:09', NULL, 22, '2025-05-10 07:49:09', '2025-12-16 08:41:07'),
(1724, 38, 'Main Construction Co 38', '1997-12-16', 28, 7, NULL, 'General Construction', 'Zamboanga City', 'company38@example.com', '09170000038', NULL, NULL, NULL, 'PCAB-53613', 'A', '2026-12-31', 'BP-38', 'Zamboanga', '2026-01-01', 'TIN-38', 'dti_cert.jpg', 'approved', '2025-02-10 07:49:09', NULL, 27, '2025-02-08 07:49:09', '2025-12-16 08:41:07'),
(1725, 39, 'Main Construction Co 39', '2009-12-16', 16, 8, NULL, 'General Construction', 'Zamboanga City', 'company39@example.com', '09170000039', NULL, NULL, NULL, 'PCAB-85450', 'A', '2026-12-31', 'BP-39', 'Zamboanga', '2026-01-01', 'TIN-39', 'dti_cert.jpg', 'approved', '2025-07-23 07:49:09', NULL, 42, '2025-07-21 07:49:09', '2025-12-16 08:41:07'),
(1726, 40, 'Main Construction Co 40', '2001-12-16', 24, 6, NULL, 'General Construction', 'Zamboanga City', 'company40@example.com', '09170000040', NULL, NULL, NULL, 'PCAB-48462', 'A', '2026-12-31', 'BP-40', 'Zamboanga', '2026-01-01', 'TIN-40', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 11, '2025-02-04 07:49:09', '2025-12-16 08:41:07'),
(1727, 41, 'Main Construction Co 41', '2011-12-16', 14, 9, NULL, 'General Construction', 'Zamboanga City', 'company41@example.com', '09170000041', NULL, NULL, NULL, 'PCAB-67854', 'A', '2026-12-31', 'BP-41', 'Zamboanga', '2026-01-01', 'TIN-41', 'dti_cert.jpg', 'approved', '2025-02-10 07:49:09', NULL, 25, '2025-02-08 07:49:09', '2025-12-16 08:41:07'),
(1728, 42, 'Main Construction Co 42', '1999-12-16', 26, 9, NULL, 'General Construction', 'Zamboanga City', 'company42@example.com', '09170000042', NULL, NULL, NULL, 'PCAB-91907', 'A', '2026-12-31', 'BP-42', 'Zamboanga', '2026-01-01', 'TIN-42', 'dti_cert.jpg', 'approved', '2025-06-04 07:49:09', NULL, 19, '2025-06-02 07:49:09', '2025-12-16 08:41:07'),
(1729, 43, 'Main Construction Co 43', '1999-12-16', 26, 3, NULL, 'General Construction', 'Zamboanga City', 'company43@example.com', '09170000043', NULL, NULL, NULL, 'PCAB-33148', 'A', '2026-12-31', 'BP-43', 'Zamboanga', '2026-01-01', 'TIN-43', 'dti_cert.jpg', 'approved', '2025-05-23 07:49:09', NULL, 31, '2025-05-21 07:49:09', '2025-12-16 08:41:07'),
(1730, 44, 'Main Construction Co 44', '2002-12-16', 23, 6, NULL, 'General Construction', 'Zamboanga City', 'company44@example.com', '09170000044', NULL, NULL, NULL, 'PCAB-27263', 'A', '2026-12-31', 'BP-44', 'Zamboanga', '2026-01-01', 'TIN-44', 'dti_cert.jpg', 'approved', '2025-06-30 07:49:09', NULL, 7, '2025-06-28 07:49:09', '2025-12-16 08:41:07'),
(1731, 45, 'Main Construction Co 45', '2018-12-16', 7, 9, NULL, 'General Construction', 'Zamboanga City', 'company45@example.com', '09170000045', NULL, NULL, NULL, 'PCAB-35372', 'A', '2026-12-31', 'BP-45', 'Zamboanga', '2026-01-01', 'TIN-45', 'dti_cert.jpg', 'pending', NULL, NULL, 12, '2025-04-02 07:49:09', '2025-12-16 08:41:07'),
(1732, 46, 'Main Construction Co 46', '1999-12-16', 26, 6, NULL, 'General Construction', 'Zamboanga City', 'company46@example.com', '09170000046', NULL, NULL, NULL, 'PCAB-88247', 'A', '2026-12-31', 'BP-46', 'Zamboanga', '2026-01-01', 'TIN-46', 'dti_cert.jpg', 'approved', '2025-08-08 07:49:09', NULL, 1, '2025-08-06 07:49:09', '2025-12-16 08:41:07'),
(1733, 47, 'Main Construction Co 47', '2006-12-16', 19, 3, NULL, 'General Construction', 'Zamboanga City', 'company47@example.com', '09170000047', NULL, NULL, NULL, 'PCAB-60406', 'A', '2026-12-31', 'BP-47', 'Zamboanga', '2026-01-01', 'TIN-47', 'dti_cert.jpg', 'approved', '2025-07-14 07:49:09', NULL, 37, '2025-07-12 07:49:09', '2025-12-16 08:41:07'),
(1734, 48, 'Main Construction Co 48', '2007-12-16', 18, 2, NULL, 'General Construction', 'Zamboanga City', 'company48@example.com', '09170000048', NULL, NULL, NULL, 'PCAB-21775', 'A', '2026-12-31', 'BP-48', 'Zamboanga', '2026-01-01', 'TIN-48', 'dti_cert.jpg', 'approved', '2025-06-17 07:49:09', NULL, 10, '2025-06-15 07:49:09', '2025-12-16 08:41:07'),
(1735, 49, 'Main Construction Co 49', '1996-12-16', 29, 2, NULL, 'General Construction', 'Zamboanga City', 'company49@example.com', '09170000049', NULL, NULL, NULL, 'PCAB-80787', 'A', '2026-12-31', 'BP-49', 'Zamboanga', '2026-01-01', 'TIN-49', 'dti_cert.jpg', 'approved', '2025-12-14 07:49:09', NULL, 4, '2025-12-12 07:49:09', '2025-12-16 08:41:07'),
(1736, 50, 'Main Construction Co 50', '2023-12-16', 2, 3, NULL, 'General Construction', 'Zamboanga City', 'company50@example.com', '09170000050', NULL, NULL, NULL, 'PCAB-46129', 'A', '2026-12-31', 'BP-50', 'Zamboanga', '2026-01-01', 'TIN-50', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 47, '2025-08-24 07:49:09', '2025-12-16 08:41:07'),
(1737, 51, 'Main Construction Co 51', '2014-12-16', 11, 3, NULL, 'General Construction', 'Zamboanga City', 'company51@example.com', '09170000051', NULL, NULL, NULL, 'PCAB-85340', 'A', '2026-12-31', 'BP-51', 'Zamboanga', '2026-01-01', 'TIN-51', 'dti_cert.jpg', 'approved', '2025-09-19 07:49:09', NULL, 3, '2025-09-17 07:49:09', '2025-12-16 08:41:07'),
(1738, 52, 'Main Construction Co 52', '2006-12-16', 19, 1, NULL, 'General Construction', 'Zamboanga City', 'company52@example.com', '09170000052', NULL, NULL, NULL, 'PCAB-78141', 'A', '2026-12-31', 'BP-52', 'Zamboanga', '2026-01-01', 'TIN-52', 'dti_cert.jpg', 'approved', '2025-08-29 07:49:09', NULL, 46, '2025-08-27 07:49:09', '2025-12-16 08:41:07'),
(1739, 53, 'Main Construction Co 53', '2022-12-16', 3, 6, NULL, 'General Construction', 'Zamboanga City', 'company53@example.com', '09170000053', NULL, NULL, NULL, 'PCAB-34145', 'A', '2026-12-31', 'BP-53', 'Zamboanga', '2026-01-01', 'TIN-53', 'dti_cert.jpg', 'approved', '2025-11-12 07:49:09', NULL, 30, '2025-11-10 07:49:09', '2025-12-16 08:41:07'),
(1740, 54, 'Main Construction Co 54', '2006-12-16', 19, 2, NULL, 'General Construction', 'Zamboanga City', 'company54@example.com', '09170000054', NULL, NULL, NULL, 'PCAB-32105', 'A', '2026-12-31', 'BP-54', 'Zamboanga', '2026-01-01', 'TIN-54', 'dti_cert.jpg', 'approved', '2025-01-15 07:49:09', NULL, 38, '2025-01-13 07:49:09', '2025-12-16 08:41:07'),
(1741, 55, 'Main Construction Co 55', '2002-12-16', 23, 6, NULL, 'General Construction', 'Zamboanga City', 'company55@example.com', '09170000055', NULL, NULL, NULL, 'PCAB-36431', 'A', '2026-12-31', 'BP-55', 'Zamboanga', '2026-01-01', 'TIN-55', 'dti_cert.jpg', 'pending', NULL, NULL, 0, '2025-06-01 07:49:09', '2025-12-16 08:41:07'),
(1742, 56, 'Main Construction Co 56', '1997-12-16', 28, 3, NULL, 'General Construction', 'Zamboanga City', 'company56@example.com', '09170000056', NULL, NULL, NULL, 'PCAB-82813', 'A', '2026-12-31', 'BP-56', 'Zamboanga', '2026-01-01', 'TIN-56', 'dti_cert.jpg', 'approved', '2025-03-16 07:49:09', NULL, 38, '2025-03-14 07:49:09', '2025-12-16 08:41:07'),
(1743, 57, 'Main Construction Co 57', '2013-12-16', 12, 1, NULL, 'General Construction', 'Zamboanga City', 'company57@example.com', '09170000057', NULL, NULL, NULL, 'PCAB-72710', 'A', '2026-12-31', 'BP-57', 'Zamboanga', '2026-01-01', 'TIN-57', 'dti_cert.jpg', 'approved', '2025-03-27 07:49:09', NULL, 12, '2025-03-25 07:49:09', '2025-12-16 08:41:07'),
(1744, 58, 'Main Construction Co 58', '2019-12-16', 6, 8, NULL, 'General Construction', 'Zamboanga City', 'company58@example.com', '09170000058', NULL, NULL, NULL, 'PCAB-34834', 'A', '2026-12-31', 'BP-58', 'Zamboanga', '2026-01-01', 'TIN-58', 'dti_cert.jpg', 'approved', '2025-01-25 07:49:09', NULL, 9, '2025-01-23 07:49:09', '2025-12-16 08:41:07'),
(1745, 59, 'Main Construction Co 59', '2004-12-16', 21, 6, NULL, 'General Construction', 'Zamboanga City', 'company59@example.com', '09170000059', NULL, NULL, NULL, 'PCAB-19251', 'A', '2026-12-31', 'BP-59', 'Zamboanga', '2026-01-01', 'TIN-59', 'dti_cert.jpg', 'approved', '2025-06-12 07:49:09', NULL, 44, '2025-06-10 07:49:09', '2025-12-16 08:41:07'),
(1746, 60, 'Main Construction Co 60', '1996-12-16', 29, 4, NULL, 'General Construction', 'Zamboanga City', 'company60@example.com', '09170000060', NULL, NULL, NULL, 'PCAB-71914', 'A', '2026-12-31', 'BP-60', 'Zamboanga', '2026-01-01', 'TIN-60', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 46, '2025-10-15 07:49:09', '2025-12-16 08:41:07'),
(1747, 61, 'Main Construction Co 61', '2005-12-16', 20, 3, NULL, 'General Construction', 'Zamboanga City', 'company61@example.com', '09170000061', NULL, NULL, NULL, 'PCAB-15554', 'A', '2026-12-31', 'BP-61', 'Zamboanga', '2026-01-01', 'TIN-61', 'dti_cert.jpg', 'approved', '2024-12-25 07:49:09', NULL, 15, '2024-12-23 07:49:09', '2025-12-16 08:41:07'),
(1748, 62, 'Main Construction Co 62', '2013-12-16', 12, 7, NULL, 'General Construction', 'Zamboanga City', 'company62@example.com', '09170000062', NULL, NULL, NULL, 'PCAB-84279', 'A', '2026-12-31', 'BP-62', 'Zamboanga', '2026-01-01', 'TIN-62', 'dti_cert.jpg', 'approved', '2025-01-18 07:49:09', NULL, 1, '2025-01-16 07:49:09', '2025-12-16 08:41:07'),
(1749, 63, 'Main Construction Co 63', '1997-12-16', 28, 4, NULL, 'General Construction', 'Zamboanga City', 'company63@example.com', '09170000063', NULL, NULL, NULL, 'PCAB-24967', 'A', '2026-12-31', 'BP-63', 'Zamboanga', '2026-01-01', 'TIN-63', 'dti_cert.jpg', 'approved', '2025-03-29 07:49:09', NULL, 2, '2025-03-27 07:49:09', '2025-12-16 08:41:07'),
(1750, 64, 'Main Construction Co 64', '2010-12-16', 15, 5, NULL, 'General Construction', 'Zamboanga City', 'company64@example.com', '09170000064', NULL, NULL, NULL, 'PCAB-42118', 'A', '2026-12-31', 'BP-64', 'Zamboanga', '2026-01-01', 'TIN-64', 'dti_cert.jpg', 'approved', '2025-08-21 07:49:09', NULL, 7, '2025-08-19 07:49:09', '2025-12-16 08:41:07'),
(1751, 65, 'Main Construction Co 65', '2005-12-16', 20, 1, NULL, 'General Construction', 'Zamboanga City', 'company65@example.com', '09170000065', NULL, NULL, NULL, 'PCAB-11349', 'A', '2026-12-31', 'BP-65', 'Zamboanga', '2026-01-01', 'TIN-65', 'dti_cert.jpg', 'pending', NULL, NULL, 10, '2025-01-05 07:49:09', '2025-12-16 08:41:07'),
(1752, 66, 'Main Construction Co 66', '2002-12-16', 23, 9, NULL, 'General Construction', 'Zamboanga City', 'company66@example.com', '09170000066', NULL, NULL, NULL, 'PCAB-91407', 'A', '2026-12-31', 'BP-66', 'Zamboanga', '2026-01-01', 'TIN-66', 'dti_cert.jpg', 'approved', '2025-10-21 07:49:09', NULL, 18, '2025-10-19 07:49:09', '2025-12-16 08:41:07'),
(1753, 67, 'Main Construction Co 67', '1999-12-16', 26, 3, NULL, 'General Construction', 'Zamboanga City', 'company67@example.com', '09170000067', NULL, NULL, NULL, 'PCAB-58294', 'A', '2026-12-31', 'BP-67', 'Zamboanga', '2026-01-01', 'TIN-67', 'dti_cert.jpg', 'approved', '2025-01-01 07:49:09', NULL, 13, '2024-12-30 07:49:09', '2025-12-16 08:41:07'),
(1754, 68, 'Main Construction Co 68', '1997-12-16', 28, 8, NULL, 'General Construction', 'Zamboanga City', 'company68@example.com', '09170000068', NULL, NULL, NULL, 'PCAB-21255', 'A', '2026-12-31', 'BP-68', 'Zamboanga', '2026-01-01', 'TIN-68', 'dti_cert.jpg', 'approved', '2025-06-26 07:49:09', NULL, 8, '2025-06-24 07:49:09', '2025-12-16 08:41:07'),
(1755, 69, 'Main Construction Co 69', '2021-12-16', 4, 1, NULL, 'General Construction', 'Zamboanga City', 'company69@example.com', '09170000069', NULL, NULL, NULL, 'PCAB-67993', 'A', '2026-12-31', 'BP-69', 'Zamboanga', '2026-01-01', 'TIN-69', 'dti_cert.jpg', 'approved', '2025-03-18 07:49:09', NULL, 48, '2025-03-16 07:49:09', '2025-12-16 08:41:07'),
(1756, 70, 'Main Construction Co 70', '2000-12-16', 25, 9, NULL, 'General Construction', 'Zamboanga City', 'company70@example.com', '09170000070', NULL, NULL, NULL, 'PCAB-28285', 'A', '2026-12-31', 'BP-70', 'Zamboanga', '2026-01-01', 'TIN-70', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 2, '2025-07-27 07:49:09', '2025-12-16 08:41:07'),
(1757, 71, 'Main Construction Co 71', '2003-12-16', 22, 6, NULL, 'General Construction', 'Zamboanga City', 'company71@example.com', '09170000071', NULL, NULL, NULL, 'PCAB-42832', 'A', '2026-12-31', 'BP-71', 'Zamboanga', '2026-01-01', 'TIN-71', 'dti_cert.jpg', 'approved', '2025-07-26 07:49:09', NULL, 12, '2025-07-24 07:49:09', '2025-12-16 08:41:07'),
(1758, 72, 'Main Construction Co 72', '2019-12-16', 6, 7, NULL, 'General Construction', 'Zamboanga City', 'company72@example.com', '09170000072', NULL, NULL, NULL, 'PCAB-52638', 'A', '2026-12-31', 'BP-72', 'Zamboanga', '2026-01-01', 'TIN-72', 'dti_cert.jpg', 'approved', '2025-04-28 07:49:09', NULL, 31, '2025-04-26 07:49:09', '2025-12-16 08:41:07'),
(1759, 73, 'Main Construction Co 73', '2004-12-16', 21, 1, NULL, 'General Construction', 'Zamboanga City', 'company73@example.com', '09170000073', NULL, NULL, NULL, 'PCAB-28803', 'A', '2026-12-31', 'BP-73', 'Zamboanga', '2026-01-01', 'TIN-73', 'dti_cert.jpg', 'approved', '2025-02-23 07:49:09', NULL, 9, '2025-02-21 07:49:09', '2025-12-16 08:41:07'),
(1760, 74, 'Main Construction Co 74', '1997-12-16', 28, 3, NULL, 'General Construction', 'Zamboanga City', 'company74@example.com', '09170000074', NULL, NULL, NULL, 'PCAB-70844', 'A', '2026-12-31', 'BP-74', 'Zamboanga', '2026-01-01', 'TIN-74', 'dti_cert.jpg', 'approved', '2025-01-31 07:49:09', NULL, 18, '2025-01-29 07:49:09', '2025-12-16 08:41:07'),
(1761, 75, 'Main Construction Co 75', '2007-12-16', 18, 6, NULL, 'General Construction', 'Zamboanga City', 'company75@example.com', '09170000075', NULL, NULL, NULL, 'PCAB-56597', 'A', '2026-12-31', 'BP-75', 'Zamboanga', '2026-01-01', 'TIN-75', 'dti_cert.jpg', 'pending', NULL, NULL, 35, '2025-11-17 07:49:09', '2025-12-16 08:41:07'),
(1762, 76, 'Main Construction Co 76', '2020-12-16', 5, 3, NULL, 'General Construction', 'Zamboanga City', 'company76@example.com', '09170000076', NULL, NULL, NULL, 'PCAB-73540', 'A', '2026-12-31', 'BP-76', 'Zamboanga', '2026-01-01', 'TIN-76', 'dti_cert.jpg', 'approved', '2025-10-07 07:49:09', NULL, 26, '2025-10-05 07:49:09', '2025-12-16 08:41:07'),
(1763, 77, 'Main Construction Co 77', '1995-12-16', 30, 8, NULL, 'General Construction', 'Zamboanga City', 'company77@example.com', '09170000077', NULL, NULL, NULL, 'PCAB-30352', 'A', '2026-12-31', 'BP-77', 'Zamboanga', '2026-01-01', 'TIN-77', 'dti_cert.jpg', 'approved', '2025-04-13 07:49:09', NULL, 33, '2025-04-11 07:49:09', '2025-12-16 08:41:07'),
(1764, 78, 'Main Construction Co 78', '2012-12-16', 13, 4, NULL, 'General Construction', 'Zamboanga City', 'company78@example.com', '09170000078', NULL, NULL, NULL, 'PCAB-97162', 'A', '2026-12-31', 'BP-78', 'Zamboanga', '2026-01-01', 'TIN-78', 'dti_cert.jpg', 'approved', '2025-12-07 07:49:09', NULL, 27, '2025-12-05 07:49:09', '2025-12-16 08:41:07'),
(1765, 79, 'Main Construction Co 79', '2021-12-16', 4, 8, NULL, 'General Construction', 'Zamboanga City', 'company79@example.com', '09170000079', NULL, NULL, NULL, 'PCAB-11742', 'A', '2026-12-31', 'BP-79', 'Zamboanga', '2026-01-01', 'TIN-79', 'dti_cert.jpg', 'approved', '2025-04-27 07:49:09', NULL, 27, '2025-04-25 07:49:09', '2025-12-16 08:41:07'),
(1766, 80, 'Main Construction Co 80', '2015-12-16', 10, 5, NULL, 'General Construction', 'Zamboanga City', 'company80@example.com', '09170000080', NULL, NULL, NULL, 'PCAB-94072', 'A', '2026-12-31', 'BP-80', 'Zamboanga', '2026-01-01', 'TIN-80', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 43, '2025-02-08 07:49:09', '2025-12-16 08:41:07'),
(1767, 81, 'Main Construction Co 81', '2018-12-16', 7, 4, NULL, 'General Construction', 'Zamboanga City', 'company81@example.com', '09170000081', NULL, NULL, NULL, 'PCAB-22152', 'A', '2026-12-31', 'BP-81', 'Zamboanga', '2026-01-01', 'TIN-81', 'dti_cert.jpg', 'approved', '2025-03-27 07:49:09', NULL, 6, '2025-03-25 07:49:09', '2025-12-16 08:41:07'),
(1768, 82, 'Main Construction Co 82', '2021-12-16', 4, 4, NULL, 'General Construction', 'Zamboanga City', 'company82@example.com', '09170000082', NULL, NULL, NULL, 'PCAB-97364', 'A', '2026-12-31', 'BP-82', 'Zamboanga', '2026-01-01', 'TIN-82', 'dti_cert.jpg', 'approved', '2025-01-07 07:49:09', NULL, 4, '2025-01-05 07:49:09', '2025-12-16 08:41:07'),
(1769, 83, 'Main Construction Co 83', '1997-12-16', 28, 7, NULL, 'General Construction', 'Zamboanga City', 'company83@example.com', '09170000083', NULL, NULL, NULL, 'PCAB-14246', 'A', '2026-12-31', 'BP-83', 'Zamboanga', '2026-01-01', 'TIN-83', 'dti_cert.jpg', 'approved', '2024-12-27 07:49:09', NULL, 47, '2024-12-25 07:49:09', '2025-12-16 08:41:07'),
(1770, 84, 'Main Construction Co 84', '2018-12-16', 7, 1, NULL, 'General Construction', 'Zamboanga City', 'company84@example.com', '09170000084', NULL, NULL, NULL, 'PCAB-81872', 'A', '2026-12-31', 'BP-84', 'Zamboanga', '2026-01-01', 'TIN-84', 'dti_cert.jpg', 'approved', '2025-03-15 07:49:09', NULL, 25, '2025-03-13 07:49:09', '2025-12-16 08:41:07'),
(1771, 85, 'Main Construction Co 85', '2014-12-16', 11, 6, NULL, 'General Construction', 'Zamboanga City', 'company85@example.com', '09170000085', NULL, NULL, NULL, 'PCAB-39928', 'A', '2026-12-31', 'BP-85', 'Zamboanga', '2026-01-01', 'TIN-85', 'dti_cert.jpg', 'pending', NULL, NULL, 26, '2024-12-27 07:49:09', '2025-12-16 08:41:07'),
(1772, 86, 'Main Construction Co 86', '2022-12-16', 3, 7, NULL, 'General Construction', 'Zamboanga City', 'company86@example.com', '09170000086', NULL, NULL, NULL, 'PCAB-74824', 'A', '2026-12-31', 'BP-86', 'Zamboanga', '2026-01-01', 'TIN-86', 'dti_cert.jpg', 'approved', '2025-08-28 07:49:09', NULL, 49, '2025-08-26 07:49:09', '2025-12-16 08:41:07'),
(1773, 87, 'Main Construction Co 87', '2014-12-16', 11, 8, NULL, 'General Construction', 'Zamboanga City', 'company87@example.com', '09170000087', NULL, NULL, NULL, 'PCAB-17198', 'A', '2026-12-31', 'BP-87', 'Zamboanga', '2026-01-01', 'TIN-87', 'dti_cert.jpg', 'approved', '2025-08-23 07:49:09', NULL, 49, '2025-08-21 07:49:09', '2025-12-16 08:41:07'),
(1774, 88, 'Main Construction Co 88', '2009-12-16', 16, 9, NULL, 'General Construction', 'Zamboanga City', 'company88@example.com', '09170000088', NULL, NULL, NULL, 'PCAB-27581', 'A', '2026-12-31', 'BP-88', 'Zamboanga', '2026-01-01', 'TIN-88', 'dti_cert.jpg', 'approved', '2025-09-24 07:49:09', NULL, 24, '2025-09-22 07:49:09', '2025-12-16 08:41:07'),
(1775, 89, 'Main Construction Co 89', '2009-12-16', 16, 4, NULL, 'General Construction', 'Zamboanga City', 'company89@example.com', '09170000089', NULL, NULL, NULL, 'PCAB-97920', 'A', '2026-12-31', 'BP-89', 'Zamboanga', '2026-01-01', 'TIN-89', 'dti_cert.jpg', 'approved', '2025-07-13 07:49:09', NULL, 29, '2025-07-11 07:49:09', '2025-12-16 08:41:07'),
(1776, 90, 'Main Construction Co 90', '2024-12-16', 1, 8, NULL, 'General Construction', 'Zamboanga City', 'company90@example.com', '09170000090', NULL, NULL, NULL, 'PCAB-36143', 'A', '2026-12-31', 'BP-90', 'Zamboanga', '2026-01-01', 'TIN-90', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 18, '2025-04-07 07:49:09', '2025-12-16 08:41:07'),
(1777, 91, 'Main Construction Co 91', '2008-12-16', 17, 4, NULL, 'General Construction', 'Zamboanga City', 'company91@example.com', '09170000091', NULL, NULL, NULL, 'PCAB-34191', 'A', '2026-12-31', 'BP-91', 'Zamboanga', '2026-01-01', 'TIN-91', 'dti_cert.jpg', 'approved', '2025-09-18 07:49:09', NULL, 2, '2025-09-16 07:49:09', '2025-12-16 08:41:07'),
(1778, 92, 'Main Construction Co 92', '2002-12-16', 23, 7, NULL, 'General Construction', 'Zamboanga City', 'company92@example.com', '09170000092', NULL, NULL, NULL, 'PCAB-89813', 'A', '2026-12-31', 'BP-92', 'Zamboanga', '2026-01-01', 'TIN-92', 'dti_cert.jpg', 'approved', '2025-10-11 07:49:09', NULL, 10, '2025-10-09 07:49:09', '2025-12-16 08:41:07'),
(1779, 93, 'Main Construction Co 93', '2021-12-16', 4, 7, NULL, 'General Construction', 'Zamboanga City', 'company93@example.com', '09170000093', NULL, NULL, NULL, 'PCAB-59197', 'A', '2026-12-31', 'BP-93', 'Zamboanga', '2026-01-01', 'TIN-93', 'dti_cert.jpg', 'approved', '2025-10-08 07:49:09', NULL, 12, '2025-10-06 07:49:09', '2025-12-16 08:41:07'),
(1780, 94, 'Main Construction Co 94', '2017-12-16', 8, 6, NULL, 'General Construction', 'Zamboanga City', 'company94@example.com', '09170000094', NULL, NULL, NULL, 'PCAB-74178', 'A', '2026-12-31', 'BP-94', 'Zamboanga', '2026-01-01', 'TIN-94', 'dti_cert.jpg', 'approved', '2025-05-26 07:49:09', NULL, 4, '2025-05-24 07:49:09', '2025-12-16 08:41:07'),
(1781, 95, 'Main Construction Co 95', '1997-12-16', 28, 2, NULL, 'General Construction', 'Zamboanga City', 'company95@example.com', '09170000095', NULL, NULL, NULL, 'PCAB-32796', 'A', '2026-12-31', 'BP-95', 'Zamboanga', '2026-01-01', 'TIN-95', 'dti_cert.jpg', 'pending', NULL, NULL, 21, '2025-04-03 07:49:09', '2025-12-16 08:41:07'),
(1782, 96, 'Main Construction Co 96', '2001-12-16', 24, 1, NULL, 'General Construction', 'Zamboanga City', 'company96@example.com', '09170000096', NULL, NULL, NULL, 'PCAB-78766', 'A', '2026-12-31', 'BP-96', 'Zamboanga', '2026-01-01', 'TIN-96', 'dti_cert.jpg', 'approved', '2025-12-02 07:49:09', NULL, 11, '2025-11-30 07:49:09', '2025-12-16 08:41:07'),
(1783, 97, 'Main Construction Co 97', '2017-12-16', 8, 1, NULL, 'General Construction', 'Zamboanga City', 'company97@example.com', '09170000097', NULL, NULL, NULL, 'PCAB-29749', 'A', '2026-12-31', 'BP-97', 'Zamboanga', '2026-01-01', 'TIN-97', 'dti_cert.jpg', 'approved', '2025-01-04 07:49:09', NULL, 13, '2025-01-02 07:49:09', '2025-12-16 08:41:07'),
(1784, 98, 'Main Construction Co 98', '1997-12-16', 28, 4, NULL, 'General Construction', 'Zamboanga City', 'company98@example.com', '09170000098', NULL, NULL, NULL, 'PCAB-73126', 'A', '2026-12-31', 'BP-98', 'Zamboanga', '2026-01-01', 'TIN-98', 'dti_cert.jpg', 'approved', '2025-10-25 07:49:09', NULL, 30, '2025-10-23 07:49:09', '2025-12-16 08:41:07'),
(1785, 99, 'Main Construction Co 99', '2003-12-16', 22, 7, NULL, 'General Construction', 'Zamboanga City', 'company99@example.com', '09170000099', NULL, NULL, NULL, 'PCAB-92160', 'A', '2026-12-31', 'BP-99', 'Zamboanga', '2026-01-01', 'TIN-99', 'dti_cert.jpg', 'approved', '2025-07-15 07:49:09', NULL, 28, '2025-07-13 07:49:09', '2025-12-16 08:41:07'),
(1786, 100, 'Main Construction Co 100', '1996-12-16', 29, 9, NULL, 'General Construction', 'Zamboanga City', 'company100@example.com', '09170000100', NULL, NULL, NULL, 'PCAB-87531', 'A', '2026-12-31', 'BP-100', 'Zamboanga', '2026-01-01', 'TIN-100', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 27, '2025-07-02 07:49:09', '2025-12-16 08:41:07'),
(1787, 201, 'Main Construction Co 201', '2006-12-16', 19, 6, NULL, 'General Construction', 'Zamboanga City', 'company201@example.com', '09170000201', NULL, NULL, NULL, 'PCAB-77737', 'A', '2026-12-31', 'BP-201', 'Zamboanga', '2026-01-01', 'TIN-201', 'dti_cert.jpg', 'approved', '2025-08-16 07:49:09', NULL, 42, '2025-08-14 07:49:09', '2025-12-16 08:41:07'),
(1788, 202, 'Main Construction Co 202', '2021-12-16', 4, 2, NULL, 'General Construction', 'Zamboanga City', 'company202@example.com', '09170000202', NULL, NULL, NULL, 'PCAB-39620', 'A', '2026-12-31', 'BP-202', 'Zamboanga', '2026-01-01', 'TIN-202', 'dti_cert.jpg', 'approved', '2025-04-18 07:49:09', NULL, 35, '2025-04-16 07:49:09', '2025-12-16 08:41:07'),
(1789, 203, 'Main Construction Co 203', '2000-12-16', 25, 8, NULL, 'General Construction', 'Zamboanga City', 'company203@example.com', '09170000203', NULL, NULL, NULL, 'PCAB-69863', 'A', '2026-12-31', 'BP-203', 'Zamboanga', '2026-01-01', 'TIN-203', 'dti_cert.jpg', 'approved', '2025-09-06 07:49:09', NULL, 15, '2025-09-04 07:49:09', '2025-12-16 08:41:07'),
(1790, 204, 'Main Construction Co 204', '2002-12-16', 23, 3, NULL, 'General Construction', 'Zamboanga City', 'company204@example.com', '09170000204', NULL, NULL, NULL, 'PCAB-61739', 'A', '2026-12-31', 'BP-204', 'Zamboanga', '2026-01-01', 'TIN-204', 'dti_cert.jpg', 'approved', '2025-09-23 07:49:09', NULL, 41, '2025-09-21 07:49:09', '2025-12-16 08:41:07'),
(1791, 205, 'Main Construction Co 205', '2018-12-16', 7, 1, NULL, 'General Construction', 'Zamboanga City', 'company205@example.com', '09170000205', NULL, NULL, NULL, 'PCAB-32187', 'A', '2026-12-31', 'BP-205', 'Zamboanga', '2026-01-01', 'TIN-205', 'dti_cert.jpg', 'pending', NULL, NULL, 21, '2025-05-29 07:49:09', '2025-12-16 08:41:07'),
(1792, 206, 'Main Construction Co 206', '1999-12-16', 26, 1, NULL, 'General Construction', 'Zamboanga City', 'company206@example.com', '09170000206', NULL, NULL, NULL, 'PCAB-75939', 'A', '2026-12-31', 'BP-206', 'Zamboanga', '2026-01-01', 'TIN-206', 'dti_cert.jpg', 'approved', '2025-11-17 07:49:09', NULL, 45, '2025-11-15 07:49:09', '2025-12-16 08:41:07'),
(1793, 207, 'Main Construction Co 207', '2006-12-16', 19, 3, NULL, 'General Construction', 'Zamboanga City', 'company207@example.com', '09170000207', NULL, NULL, NULL, 'PCAB-51453', 'A', '2026-12-31', 'BP-207', 'Zamboanga', '2026-01-01', 'TIN-207', 'dti_cert.jpg', 'approved', '2025-04-18 07:49:09', NULL, 3, '2025-04-16 07:49:09', '2025-12-16 08:41:07'),
(1794, 208, 'Main Construction Co 208', '2009-12-16', 16, 2, NULL, 'General Construction', 'Zamboanga City', 'company208@example.com', '09170000208', NULL, NULL, NULL, 'PCAB-13265', 'A', '2026-12-31', 'BP-208', 'Zamboanga', '2026-01-01', 'TIN-208', 'dti_cert.jpg', 'approved', '2025-12-10 07:49:09', NULL, 8, '2025-12-08 07:49:09', '2025-12-16 08:41:07'),
(1795, 209, 'Main Construction Co 209', '2003-12-16', 22, 2, NULL, 'General Construction', 'Zamboanga City', 'company209@example.com', '09170000209', NULL, NULL, NULL, 'PCAB-91806', 'A', '2026-12-31', 'BP-209', 'Zamboanga', '2026-01-01', 'TIN-209', 'dti_cert.jpg', 'approved', '2025-07-17 07:49:09', NULL, 24, '2025-07-15 07:49:09', '2025-12-16 08:41:07'),
(1796, 210, 'Main Construction Co 210', '2021-12-16', 4, 7, NULL, 'General Construction', 'Zamboanga City', 'company210@example.com', '09170000210', NULL, NULL, NULL, 'PCAB-69228', 'A', '2026-12-31', 'BP-210', 'Zamboanga', '2026-01-01', 'TIN-210', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 45, '2025-06-18 07:49:09', '2025-12-16 08:41:07'),
(1797, 211, 'Main Construction Co 211', '2014-12-16', 11, 6, NULL, 'General Construction', 'Zamboanga City', 'company211@example.com', '09170000211', NULL, NULL, NULL, 'PCAB-52834', 'A', '2026-12-31', 'BP-211', 'Zamboanga', '2026-01-01', 'TIN-211', 'dti_cert.jpg', 'approved', '2025-10-14 07:49:09', NULL, 49, '2025-10-12 07:49:09', '2025-12-16 08:41:07'),
(1798, 212, 'Main Construction Co 212', '2011-12-16', 14, 7, NULL, 'General Construction', 'Zamboanga City', 'company212@example.com', '09170000212', NULL, NULL, NULL, 'PCAB-70043', 'A', '2026-12-31', 'BP-212', 'Zamboanga', '2026-01-01', 'TIN-212', 'dti_cert.jpg', 'approved', '2025-02-18 07:49:09', NULL, 16, '2025-02-16 07:49:09', '2025-12-16 08:41:07'),
(1799, 213, 'Main Construction Co 213', '2019-12-16', 6, 7, NULL, 'General Construction', 'Zamboanga City', 'company213@example.com', '09170000213', NULL, NULL, NULL, 'PCAB-34511', 'A', '2026-12-31', 'BP-213', 'Zamboanga', '2026-01-01', 'TIN-213', 'dti_cert.jpg', 'approved', '2025-05-19 07:49:09', NULL, 5, '2025-05-17 07:49:09', '2025-12-16 08:41:07'),
(1800, 214, 'Main Construction Co 214', '2006-12-16', 19, 6, NULL, 'General Construction', 'Zamboanga City', 'company214@example.com', '09170000214', NULL, NULL, NULL, 'PCAB-56516', 'A', '2026-12-31', 'BP-214', 'Zamboanga', '2026-01-01', 'TIN-214', 'dti_cert.jpg', 'approved', '2025-06-20 07:49:09', NULL, 2, '2025-06-18 07:49:09', '2025-12-16 08:41:07'),
(1801, 215, 'Main Construction Co 215', '2011-12-16', 14, 7, NULL, 'General Construction', 'Zamboanga City', 'company215@example.com', '09170000215', NULL, NULL, NULL, 'PCAB-75873', 'A', '2026-12-31', 'BP-215', 'Zamboanga', '2026-01-01', 'TIN-215', 'dti_cert.jpg', 'pending', NULL, NULL, 5, '2025-08-12 07:49:09', '2025-12-16 08:41:07'),
(1802, 216, 'Main Construction Co 216', '2011-12-16', 14, 1, NULL, 'General Construction', 'Zamboanga City', 'company216@example.com', '09170000216', NULL, NULL, NULL, 'PCAB-62029', 'A', '2026-12-31', 'BP-216', 'Zamboanga', '2026-01-01', 'TIN-216', 'dti_cert.jpg', 'approved', '2025-04-13 07:49:09', NULL, 27, '2025-04-11 07:49:09', '2025-12-16 08:41:07'),
(1803, 217, 'Main Construction Co 217', '1998-12-16', 27, 9, NULL, 'General Construction', 'Zamboanga City', 'company217@example.com', '09170000217', NULL, NULL, NULL, 'PCAB-27801', 'A', '2026-12-31', 'BP-217', 'Zamboanga', '2026-01-01', 'TIN-217', 'dti_cert.jpg', 'approved', '2025-01-16 07:49:09', NULL, 49, '2025-01-14 07:49:09', '2025-12-16 08:41:07'),
(1804, 218, 'Main Construction Co 218', '2022-12-16', 3, 8, NULL, 'General Construction', 'Zamboanga City', 'company218@example.com', '09170000218', NULL, NULL, NULL, 'PCAB-45679', 'A', '2026-12-31', 'BP-218', 'Zamboanga', '2026-01-01', 'TIN-218', 'dti_cert.jpg', 'approved', '2025-06-07 07:49:09', NULL, 23, '2025-06-05 07:49:09', '2025-12-16 08:41:07'),
(1805, 219, 'Main Construction Co 219', '2000-12-16', 25, 4, NULL, 'General Construction', 'Zamboanga City', 'company219@example.com', '09170000219', NULL, NULL, NULL, 'PCAB-73715', 'A', '2026-12-31', 'BP-219', 'Zamboanga', '2026-01-01', 'TIN-219', 'dti_cert.jpg', 'approved', '2025-03-11 07:49:09', NULL, 13, '2025-03-09 07:49:09', '2025-12-16 08:41:07'),
(1806, 220, 'Main Construction Co 220', '2001-12-16', 24, 5, NULL, 'General Construction', 'Zamboanga City', 'company220@example.com', '09170000220', NULL, NULL, NULL, 'PCAB-16396', 'A', '2026-12-31', 'BP-220', 'Zamboanga', '2026-01-01', 'TIN-220', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 49, '2025-11-08 07:49:09', '2025-12-16 08:41:07');

-- --------------------------------------------------------

--
-- Table structure for table `contractor_types`
--

CREATE TABLE `contractor_types` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contractor_types`
--

INSERT INTO `contractor_types` (`type_id`, `type_name`) VALUES
(6, 'Architectural Contractor'),
(5, 'Civil Works Contractor'),
(2, 'Electrical Contractor'),
(1, 'General Contractor'),
(7, 'Interior Fit-out Contractor'),
(8, 'Landscaping Contractor'),
(4, 'Mechanical Contractor'),
(9, 'Others'),
(3, 'Pool Contractor');

-- --------------------------------------------------------

--
-- Table structure for table `contractor_users`
--

CREATE TABLE `contractor_users` (
  `contractor_user_id` int(11) NOT NULL,
  `contractor_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `authorized_rep_lname` varchar(100) NOT NULL,
  `authorized_rep_mname` varchar(100) DEFAULT NULL,
  `authorized_rep_fname` varchar(100) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `role` enum('owner','manager','engineer','others','architect') DEFAULT 'owner',
  `role_other` varchar(255) DEFAULT NULL,
  `if_others` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `suspension_until` date DEFAULT NULL,
  `suspension_reason` text DEFAULT NULL,
  `deletion_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contractor_users`
--

INSERT INTO `contractor_users` (`contractor_user_id`, `contractor_id`, `user_id`, `authorized_rep_lname`, `authorized_rep_mname`, `authorized_rep_fname`, `phone_number`, `role`, `role_other`, `if_others`, `is_deleted`, `is_active`, `suspension_until`, `suspension_reason`, `deletion_reason`, `created_at`) VALUES
(1876, 1687, 1, 'OwnerLast1', NULL, 'OwnerFirst1', '09170000001', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-07-30 07:49:09'),
(1877, 1688, 2, 'OwnerLast2', NULL, 'OwnerFirst2', '09170000002', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-10-21 07:49:09'),
(1878, 1689, 3, 'OwnerLast3', NULL, 'OwnerFirst3', '09170000003', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2024-12-17 07:49:09'),
(1879, 1690, 4, 'OwnerLast4', NULL, 'OwnerFirst4', '09170000004', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-01-24 07:49:09'),
(1880, 1691, 5, 'OwnerLast5', NULL, 'OwnerFirst5', '09170000005', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-02-19 07:49:09'),
(1881, 1692, 6, 'OwnerLast6', NULL, 'OwnerFirst6', '09170000006', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-04-21 07:49:09'),
(1882, 1693, 7, 'OwnerLast7', NULL, 'OwnerFirst7', '09170000007', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-11-25 07:49:09'),
(1883, 1694, 8, 'OwnerLast8', NULL, 'OwnerFirst8', '09170000008', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-10-08 07:49:09'),
(1884, 1695, 9, 'OwnerLast9', NULL, 'OwnerFirst9', '09170000009', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-03-26 07:49:09'),
(1885, 1696, 10, 'OwnerLast10', NULL, 'OwnerFirst10', '09170000010', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-09-23 07:49:09'),
(1886, 1697, 11, 'OwnerLast11', NULL, 'OwnerFirst11', '09170000011', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-05-10 07:49:09'),
(1887, 1698, 12, 'OwnerLast12', NULL, 'OwnerFirst12', '09170000012', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-09-09 07:49:09'),
(1888, 1699, 13, 'OwnerLast13', NULL, 'OwnerFirst13', '09170000013', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-07-06 07:49:09'),
(1889, 1700, 14, 'OwnerLast14', NULL, 'OwnerFirst14', '09170000014', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-02-20 07:49:09'),
(1890, 1701, 15, 'OwnerLast15', NULL, 'OwnerFirst15', '09170000015', 'owner', NULL, NULL, 0, 0, NULL, NULL, NULL, '2025-01-08 07:49:09'),
(1891, 1702, 16, 'OwnerLast16', NULL, 'OwnerFirst16', '09170000016', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-05-15 07:49:09'),
(1892, 1703, 17, 'OwnerLast17', NULL, 'OwnerFirst17', '09170000017', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-04-29 07:49:09'),
(1893, 1704, 18, 'OwnerLast18', NULL, 'OwnerFirst18', '09170000018', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-01-04 07:49:09'),
(1894, 1705, 19, 'OwnerLast19', NULL, 'OwnerFirst19', '09170000019', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-05-29 07:49:09'),
(1895, 1706, 20, 'OwnerLast20', NULL, 'OwnerFirst20', '09170000020', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-10-18 07:49:09'),
(1896, 1707, 21, 'OwnerLast21', NULL, 'OwnerFirst21', '09170000021', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-01-22 07:49:09'),
(1897, 1708, 22, 'OwnerLast22', NULL, 'OwnerFirst22', '09170000022', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-10-16 07:49:09'),
(1898, 1709, 23, 'OwnerLast23', NULL, 'OwnerFirst23', '09170000023', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-03-16 07:49:09'),
(1899, 1710, 24, 'OwnerLast24', NULL, 'OwnerFirst24', '09170000024', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-08-25 07:49:09'),
(1900, 1711, 25, 'OwnerLast25', NULL, 'OwnerFirst25', '09170000025', 'owner', NULL, NULL, 0, 0, NULL, NULL, NULL, '2025-05-01 07:49:09'),
(1901, 1712, 26, 'OwnerLast26', NULL, 'OwnerFirst26', '09170000026', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-08-14 07:49:09'),
(1902, 1713, 27, 'OwnerLast27', NULL, 'OwnerFirst27', '09170000027', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-11-06 07:49:09'),
(1903, 1714, 28, 'OwnerLast28', NULL, 'OwnerFirst28', '09170000028', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-05-01 07:49:09'),
(1904, 1715, 29, 'OwnerLast29', NULL, 'OwnerFirst29', '09170000029', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-07-19 07:49:09'),
(1905, 1716, 30, 'OwnerLast30', NULL, 'OwnerFirst30', '09170000030', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-05-24 07:49:09'),
(1906, 1717, 31, 'OwnerLast31', NULL, 'OwnerFirst31', '09170000031', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-11-08 07:49:09'),
(1907, 1718, 32, 'OwnerLast32', NULL, 'OwnerFirst32', '09170000032', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-09-29 07:49:09'),
(1908, 1719, 33, 'OwnerLast33', NULL, 'OwnerFirst33', '09170000033', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-02-04 07:49:09'),
(1909, 1720, 34, 'OwnerLast34', NULL, 'OwnerFirst34', '09170000034', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-08-18 07:49:09'),
(1910, 1721, 35, 'OwnerLast35', NULL, 'OwnerFirst35', '09170000035', 'owner', NULL, NULL, 0, 0, NULL, NULL, NULL, '2025-04-22 07:49:09'),
(1911, 1722, 36, 'OwnerLast36', NULL, 'OwnerFirst36', '09170000036', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-09-22 07:49:09'),
(1912, 1723, 37, 'OwnerLast37', NULL, 'OwnerFirst37', '09170000037', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-05-10 07:49:09'),
(1913, 1724, 38, 'OwnerLast38', NULL, 'OwnerFirst38', '09170000038', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-02-08 07:49:09'),
(1914, 1725, 39, 'OwnerLast39', NULL, 'OwnerFirst39', '09170000039', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-07-21 07:49:09'),
(1915, 1726, 40, 'OwnerLast40', NULL, 'OwnerFirst40', '09170000040', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-02-04 07:49:09'),
(1916, 1727, 41, 'OwnerLast41', NULL, 'OwnerFirst41', '09170000041', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-02-08 07:49:09'),
(1917, 1728, 42, 'OwnerLast42', NULL, 'OwnerFirst42', '09170000042', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-06-02 07:49:09'),
(1918, 1729, 43, 'OwnerLast43', NULL, 'OwnerFirst43', '09170000043', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-05-21 07:49:09'),
(1919, 1730, 44, 'OwnerLast44', NULL, 'OwnerFirst44', '09170000044', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-06-28 07:49:09'),
(1920, 1731, 45, 'OwnerLast45', NULL, 'OwnerFirst45', '09170000045', 'owner', NULL, NULL, 0, 0, NULL, NULL, NULL, '2025-04-02 07:49:09'),
(1921, 1732, 46, 'OwnerLast46', NULL, 'OwnerFirst46', '09170000046', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-08-06 07:49:09'),
(1922, 1733, 47, 'OwnerLast47', NULL, 'OwnerFirst47', '09170000047', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-07-12 07:49:09'),
(1923, 1734, 48, 'OwnerLast48', NULL, 'OwnerFirst48', '09170000048', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-06-15 07:49:09'),
(1924, 1735, 49, 'OwnerLast49', NULL, 'OwnerFirst49', '09170000049', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-12 07:49:09'),
(1925, 1736, 50, 'OwnerLast50', NULL, 'OwnerFirst50', '09170000050', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-08-24 07:49:09'),
(1926, 1737, 51, 'OwnerLast51', NULL, 'OwnerFirst51', '09170000051', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-09-17 07:49:09'),
(1927, 1738, 52, 'OwnerLast52', NULL, 'OwnerFirst52', '09170000052', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-08-27 07:49:09'),
(1928, 1739, 53, 'OwnerLast53', NULL, 'OwnerFirst53', '09170000053', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-11-10 07:49:09'),
(1929, 1740, 54, 'OwnerLast54', NULL, 'OwnerFirst54', '09170000054', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-01-13 07:49:09'),
(1930, 1741, 55, 'OwnerLast55', NULL, 'OwnerFirst55', '09170000055', 'owner', NULL, NULL, 0, 0, NULL, NULL, NULL, '2025-06-01 07:49:09'),
(1931, 1742, 56, 'OwnerLast56', NULL, 'OwnerFirst56', '09170000056', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-03-14 07:49:09'),
(1932, 1743, 57, 'OwnerLast57', NULL, 'OwnerFirst57', '09170000057', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-03-25 07:49:09'),
(1933, 1744, 58, 'OwnerLast58', NULL, 'OwnerFirst58', '09170000058', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-01-23 07:49:09'),
(1934, 1745, 59, 'OwnerLast59', NULL, 'OwnerFirst59', '09170000059', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-06-10 07:49:09'),
(1935, 1746, 60, 'OwnerLast60', NULL, 'OwnerFirst60', '09170000060', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-10-15 07:49:09'),
(1936, 1747, 61, 'OwnerLast61', NULL, 'OwnerFirst61', '09170000061', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2024-12-23 07:49:09'),
(1937, 1748, 62, 'OwnerLast62', NULL, 'OwnerFirst62', '09170000062', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-01-16 07:49:09'),
(1938, 1749, 63, 'OwnerLast63', NULL, 'OwnerFirst63', '09170000063', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-03-27 07:49:09'),
(1939, 1750, 64, 'OwnerLast64', NULL, 'OwnerFirst64', '09170000064', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-08-19 07:49:09'),
(1940, 1751, 65, 'OwnerLast65', NULL, 'OwnerFirst65', '09170000065', 'owner', NULL, NULL, 0, 0, NULL, NULL, NULL, '2025-01-05 07:49:09'),
(1941, 1752, 66, 'OwnerLast66', NULL, 'OwnerFirst66', '09170000066', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-10-19 07:49:09'),
(1942, 1753, 67, 'OwnerLast67', NULL, 'OwnerFirst67', '09170000067', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2024-12-30 07:49:09'),
(1943, 1754, 68, 'OwnerLast68', NULL, 'OwnerFirst68', '09170000068', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-06-24 07:49:09'),
(1944, 1755, 69, 'OwnerLast69', NULL, 'OwnerFirst69', '09170000069', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-03-16 07:49:09'),
(1945, 1756, 70, 'OwnerLast70', NULL, 'OwnerFirst70', '09170000070', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-07-27 07:49:09'),
(1946, 1757, 71, 'OwnerLast71', NULL, 'OwnerFirst71', '09170000071', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-07-24 07:49:09'),
(1947, 1758, 72, 'OwnerLast72', NULL, 'OwnerFirst72', '09170000072', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-04-26 07:49:09'),
(1948, 1759, 73, 'OwnerLast73', NULL, 'OwnerFirst73', '09170000073', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-02-21 07:49:09'),
(1949, 1760, 74, 'OwnerLast74', NULL, 'OwnerFirst74', '09170000074', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-01-29 07:49:09'),
(1950, 1761, 75, 'OwnerLast75', NULL, 'OwnerFirst75', '09170000075', 'owner', NULL, NULL, 0, 0, NULL, NULL, NULL, '2025-11-17 07:49:09'),
(1951, 1762, 76, 'OwnerLast76', NULL, 'OwnerFirst76', '09170000076', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-10-05 07:49:09'),
(1952, 1763, 77, 'OwnerLast77', NULL, 'OwnerFirst77', '09170000077', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-04-11 07:49:09'),
(1953, 1764, 78, 'OwnerLast78', NULL, 'OwnerFirst78', '09170000078', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-05 07:49:09'),
(1954, 1765, 79, 'OwnerLast79', NULL, 'OwnerFirst79', '09170000079', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-04-25 07:49:09'),
(1955, 1766, 80, 'OwnerLast80', NULL, 'OwnerFirst80', '09170000080', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-02-08 07:49:09'),
(1956, 1767, 81, 'OwnerLast81', NULL, 'OwnerFirst81', '09170000081', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-03-25 07:49:09'),
(1957, 1768, 82, 'OwnerLast82', NULL, 'OwnerFirst82', '09170000082', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-01-05 07:49:09'),
(1958, 1769, 83, 'OwnerLast83', NULL, 'OwnerFirst83', '09170000083', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2024-12-25 07:49:09'),
(1959, 1770, 84, 'OwnerLast84', NULL, 'OwnerFirst84', '09170000084', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-03-13 07:49:09'),
(1960, 1771, 85, 'OwnerLast85', NULL, 'OwnerFirst85', '09170000085', 'owner', NULL, NULL, 0, 0, NULL, NULL, NULL, '2024-12-27 07:49:09'),
(1961, 1772, 86, 'OwnerLast86', NULL, 'OwnerFirst86', '09170000086', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-08-26 07:49:09'),
(1962, 1773, 87, 'OwnerLast87', NULL, 'OwnerFirst87', '09170000087', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-08-21 07:49:09'),
(1963, 1774, 88, 'OwnerLast88', NULL, 'OwnerFirst88', '09170000088', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-09-22 07:49:09'),
(1964, 1775, 89, 'OwnerLast89', NULL, 'OwnerFirst89', '09170000089', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-07-11 07:49:09'),
(1965, 1776, 90, 'OwnerLast90', NULL, 'OwnerFirst90', '09170000090', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-04-07 07:49:09'),
(1966, 1777, 91, 'OwnerLast91', NULL, 'OwnerFirst91', '09170000091', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-09-16 07:49:09'),
(1967, 1778, 92, 'OwnerLast92', NULL, 'OwnerFirst92', '09170000092', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-10-09 07:49:09'),
(1968, 1779, 93, 'OwnerLast93', NULL, 'OwnerFirst93', '09170000093', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-10-06 07:49:09'),
(1969, 1780, 94, 'OwnerLast94', NULL, 'OwnerFirst94', '09170000094', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-05-24 07:49:09'),
(1970, 1781, 95, 'OwnerLast95', NULL, 'OwnerFirst95', '09170000095', 'owner', NULL, NULL, 0, 0, NULL, NULL, NULL, '2025-04-03 07:49:09'),
(1971, 1782, 96, 'OwnerLast96', NULL, 'OwnerFirst96', '09170000096', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-11-30 07:49:09'),
(1972, 1783, 97, 'OwnerLast97', NULL, 'OwnerFirst97', '09170000097', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-01-02 07:49:09'),
(1973, 1784, 98, 'OwnerLast98', NULL, 'OwnerFirst98', '09170000098', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-10-23 07:49:09'),
(1974, 1785, 99, 'OwnerLast99', NULL, 'OwnerFirst99', '09170000099', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-07-13 07:49:09'),
(1975, 1786, 100, 'OwnerLast100', NULL, 'OwnerFirst100', '09170000100', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-07-02 07:49:09'),
(1976, 1787, 201, 'OwnerLast201', NULL, 'OwnerFirst201', '09170000201', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-08-14 07:49:09'),
(1977, 1788, 202, 'OwnerLast202', NULL, 'OwnerFirst202', '09170000202', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-04-16 07:49:09'),
(1978, 1789, 203, 'OwnerLast203', NULL, 'OwnerFirst203', '09170000203', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-09-04 07:49:09'),
(1979, 1790, 204, 'OwnerLast204', NULL, 'OwnerFirst204', '09170000204', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-09-21 07:49:09'),
(1980, 1791, 205, 'OwnerLast205', NULL, 'OwnerFirst205', '09170000205', 'owner', NULL, NULL, 0, 0, NULL, NULL, NULL, '2025-05-29 07:49:09'),
(1981, 1792, 206, 'OwnerLast206', NULL, 'OwnerFirst206', '09170000206', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-11-15 07:49:09'),
(1982, 1793, 207, 'OwnerLast207', NULL, 'OwnerFirst207', '09170000207', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-04-16 07:49:09'),
(1983, 1794, 208, 'OwnerLast208', NULL, 'OwnerFirst208', '09170000208', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-08 07:49:09'),
(1984, 1795, 209, 'OwnerLast209', NULL, 'OwnerFirst209', '09170000209', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-07-15 07:49:09'),
(1985, 1796, 210, 'OwnerLast210', NULL, 'OwnerFirst210', '09170000210', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-06-18 07:49:09'),
(1986, 1797, 211, 'OwnerLast211', NULL, 'OwnerFirst211', '09170000211', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-10-12 07:49:09'),
(1987, 1798, 212, 'OwnerLast212', NULL, 'OwnerFirst212', '09170000212', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-02-16 07:49:09'),
(1988, 1799, 213, 'OwnerLast213', NULL, 'OwnerFirst213', '09170000213', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-05-17 07:49:09'),
(1989, 1800, 214, 'OwnerLast214', NULL, 'OwnerFirst214', '09170000214', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-06-18 07:49:09'),
(1990, 1801, 215, 'OwnerLast215', NULL, 'OwnerFirst215', '09170000215', 'owner', NULL, NULL, 0, 0, NULL, NULL, NULL, '2025-08-12 07:49:09'),
(1991, 1802, 216, 'OwnerLast216', NULL, 'OwnerFirst216', '09170000216', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-04-11 07:49:09'),
(1992, 1803, 217, 'OwnerLast217', NULL, 'OwnerFirst217', '09170000217', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-01-14 07:49:09'),
(1993, 1804, 218, 'OwnerLast218', NULL, 'OwnerFirst218', '09170000218', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-06-05 07:49:09'),
(1994, 1805, 219, 'OwnerLast219', NULL, 'OwnerFirst219', '09170000219', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-03-09 07:49:09'),
(1995, 1806, 220, 'OwnerLast220', NULL, 'OwnerFirst220', '09170000220', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-11-08 07:49:09'),
(2003, 1772, 221, 'StaffLast221', NULL, 'StaffFirst221', '09200000221', 'engineer', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2004, 1747, 222, 'StaffLast222', NULL, 'StaffFirst222', '09200000222', 'manager', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2005, 1774, 223, 'StaffLast223', NULL, 'StaffFirst223', '09200000223', 'manager', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2006, 1795, 224, 'StaffLast224', NULL, 'StaffFirst224', '09200000224', 'manager', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2007, 1758, 225, 'StaffLast225', NULL, 'StaffFirst225', '09200000225', 'others', 'Safety Officer', NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2008, 1785, 226, 'StaffLast226', NULL, 'StaffFirst226', '09200000226', 'engineer', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2009, 1719, 227, 'StaffLast227', NULL, 'StaffFirst227', '09200000227', 'manager', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2010, 1800, 228, 'StaffLast228', NULL, 'StaffFirst228', '09200000228', 'others', 'Safety Officer', NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2011, 1735, 229, 'StaffLast229', NULL, 'StaffFirst229', '09200000229', 'manager', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2012, 1694, 230, 'StaffLast230', NULL, 'StaffFirst230', '09200000230', 'others', 'Safety Officer', NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2013, 1780, 231, 'StaffLast231', NULL, 'StaffFirst231', '09200000231', 'others', 'Safety Officer', NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2014, 1767, 232, 'StaffLast232', NULL, 'StaffFirst232', '09200000232', 'others', 'Safety Officer', NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2015, 1799, 233, 'StaffLast233', NULL, 'StaffFirst233', '09200000233', 'others', 'Safety Officer', NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2016, 1717, 234, 'StaffLast234', NULL, 'StaffFirst234', '09200000234', 'manager', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2017, 1688, 235, 'StaffLast235', NULL, 'StaffFirst235', '09200000235', 'manager', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2018, 1724, 236, 'StaffLast236', NULL, 'StaffFirst236', '09200000236', 'engineer', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2019, 1717, 237, 'StaffLast237', NULL, 'StaffFirst237', '09200000237', 'manager', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2020, 1763, 238, 'StaffLast238', NULL, 'StaffFirst238', '09200000238', 'others', 'Safety Officer', NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2021, 1692, 239, 'StaffLast239', NULL, 'StaffFirst239', '09200000239', 'engineer', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2022, 1773, 240, 'StaffLast240', NULL, 'StaffFirst240', '09200000240', 'manager', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2023, 1698, 241, 'StaffLast241', NULL, 'StaffFirst241', '09200000241', 'others', 'Safety Officer', NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2024, 1702, 242, 'StaffLast242', NULL, 'StaffFirst242', '09200000242', 'manager', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2025, 1712, 243, 'StaffLast243', NULL, 'StaffFirst243', '09200000243', 'engineer', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2026, 1697, 244, 'StaffLast244', NULL, 'StaffFirst244', '09200000244', 'manager', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2027, 1712, 245, 'StaffLast245', NULL, 'StaffFirst245', '09200000245', 'manager', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2028, 1799, 246, 'StaffLast246', NULL, 'StaffFirst246', '09200000246', 'manager', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2029, 1750, 247, 'StaffLast247', NULL, 'StaffFirst247', '09200000247', 'others', 'Safety Officer', NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2030, 1742, 248, 'StaffLast248', NULL, 'StaffFirst248', '09200000248', 'others', 'Safety Officer', NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2031, 1784, 249, 'StaffLast249', NULL, 'StaffFirst249', '09200000249', 'others', 'Safety Officer', NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2032, 1789, 250, 'StaffLast250', NULL, 'StaffFirst250', '09200000250', 'engineer', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2033, 1780, 251, 'StaffLast251', NULL, 'StaffFirst251', '09200000251', 'others', 'Safety Officer', NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2034, 1783, 252, 'StaffLast252', NULL, 'StaffFirst252', '09200000252', 'manager', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2035, 1772, 253, 'StaffLast253', NULL, 'StaffFirst253', '09200000253', 'engineer', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2036, 1729, 254, 'StaffLast254', NULL, 'StaffFirst254', '09200000254', 'manager', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2037, 1718, 255, 'StaffLast255', NULL, 'StaffFirst255', '09200000255', 'engineer', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2038, 1773, 256, 'StaffLast256', NULL, 'StaffFirst256', '09200000256', 'manager', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2039, 1773, 257, 'StaffLast257', NULL, 'StaffFirst257', '09200000257', 'engineer', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2040, 1713, 258, 'StaffLast258', NULL, 'StaffFirst258', '09200000258', 'manager', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2041, 1762, 259, 'StaffLast259', NULL, 'StaffFirst259', '09200000259', 'engineer', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2042, 1763, 260, 'StaffLast260', NULL, 'StaffFirst260', '09200000260', 'manager', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2043, 1782, 261, 'StaffLast261', NULL, 'StaffFirst261', '09200000261', 'engineer', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2044, 1700, 262, 'StaffLast262', NULL, 'StaffFirst262', '09200000262', 'engineer', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2045, 1779, 263, 'StaffLast263', NULL, 'StaffFirst263', '09200000263', 'manager', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2046, 1740, 264, 'StaffLast264', NULL, 'StaffFirst264', '09200000264', 'others', 'Safety Officer', NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2047, 1735, 265, 'StaffLast265', NULL, 'StaffFirst265', '09200000265', 'engineer', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2048, 1694, 266, 'StaffLast266', NULL, 'StaffFirst266', '09200000266', 'others', 'Safety Officer', NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2049, 1689, 267, 'StaffLast267', NULL, 'StaffFirst267', '09200000267', 'manager', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2050, 1690, 268, 'StaffLast268', NULL, 'StaffFirst268', '09200000268', 'engineer', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2051, 1698, 269, 'StaffLast269', NULL, 'StaffFirst269', '09200000269', 'engineer', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2052, 1784, 270, 'StaffLast270', NULL, 'StaffFirst270', '09200000270', 'others', 'Safety Officer', NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09');

-- --------------------------------------------------------

--
-- Table structure for table `disputes`
--

CREATE TABLE `disputes` (
  `dispute_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `raised_by_user_id` int(11) NOT NULL,
  `against_user_id` int(11) NOT NULL,
  `milestone_id` int(11) DEFAULT NULL,
  `milestone_item_id` int(11) DEFAULT NULL,
  `dispute_type` enum('Payment','Delay','Quality','Others') NOT NULL,
  `if_others_distype` varchar(255) DEFAULT NULL,
  `dispute_desc` text NOT NULL,
  `dispute_status` enum('open','under_review','resolved','closed','cancelled') DEFAULT 'open',
  `reason` text DEFAULT NULL,
  `admin_response` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `disputes`
--

INSERT INTO `disputes` (`dispute_id`, `project_id`, `raised_by_user_id`, `against_user_id`, `milestone_id`, `milestone_item_id`, `dispute_type`, `if_others_distype`, `dispute_desc`, `dispute_status`, `reason`, `admin_response`, `created_at`, `resolved_at`) VALUES
(79, 1015, 141, 4, NULL, NULL, 'Delay', NULL, 'Delayed', 'open', NULL, NULL, '2025-12-15 07:49:09', NULL),
(80, 1016, 157, 8, NULL, NULL, 'Delay', NULL, 'Delayed', 'open', NULL, NULL, '2025-12-15 07:49:09', NULL),
(81, 1017, 151, 14, NULL, NULL, 'Delay', NULL, 'Delayed', 'open', NULL, NULL, '2025-12-15 07:49:09', NULL),
(82, 1018, 137, 16, NULL, NULL, 'Delay', NULL, 'Delayed', 'open', NULL, NULL, '2025-12-15 07:49:09', NULL),
(83, 1019, 169, 16, NULL, NULL, 'Delay', NULL, 'Delayed', 'open', NULL, NULL, '2025-12-15 07:49:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `dispute_files`
--

CREATE TABLE `dispute_files` (
  `file_id` int(11) NOT NULL,
  `dispute_id` int(11) NOT NULL,
  `storage_path` varchar(500) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `size` int(11) UNSIGNED DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `milestones`
--

CREATE TABLE `milestones` (
  `milestone_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `contractor_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `milestone_name` varchar(200) NOT NULL,
  `milestone_description` text NOT NULL,
  `milestone_status` enum('not_started','in_progress','rejected','delayed','cancelled') DEFAULT 'not_started',
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `is_deleted` tinyint(1) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `setup_status` enum('submitted','rejected','approved','') NOT NULL DEFAULT 'submitted',
  `setup_rej_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `milestones`
--

INSERT INTO `milestones` (`milestone_id`, `project_id`, `contractor_id`, `plan_id`, `milestone_name`, `milestone_description`, `milestone_status`, `start_date`, `end_date`, `is_deleted`, `reason`, `setup_status`, `setup_rej_reason`, `created_at`, `updated_at`) VALUES
(1466, 1015, 1690, 890, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1467, 1015, 1690, 890, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1468, 1015, 1690, 890, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1469, 1016, 1694, 891, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1470, 1016, 1694, 891, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1471, 1016, 1694, 891, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1472, 1017, 1700, 892, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1473, 1017, 1700, 892, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1474, 1017, 1700, 892, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1475, 1018, 1702, 893, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1476, 1018, 1702, 893, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1477, 1018, 1702, 893, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1478, 1019, 1702, 894, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1479, 1019, 1702, 894, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1480, 1019, 1702, 894, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1481, 1020, 1707, 895, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1482, 1020, 1707, 895, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1483, 1020, 1707, 895, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1484, 1021, 1715, 896, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1485, 1021, 1715, 896, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1486, 1021, 1715, 896, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1487, 1022, 1717, 897, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1488, 1022, 1717, 897, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1489, 1022, 1717, 897, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1490, 1023, 1717, 898, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1491, 1023, 1717, 898, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1492, 1023, 1717, 898, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1493, 1024, 1718, 899, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1494, 1024, 1718, 899, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1495, 1024, 1718, 899, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1496, 1025, 1718, 900, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1497, 1025, 1718, 900, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1498, 1025, 1718, 900, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1499, 1026, 1725, 901, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1500, 1026, 1725, 901, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1501, 1026, 1725, 901, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1502, 1027, 1727, 902, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1503, 1027, 1727, 902, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1504, 1027, 1727, 902, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1505, 1028, 1730, 903, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1506, 1028, 1730, 903, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1507, 1028, 1730, 903, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1508, 1029, 1733, 904, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1509, 1029, 1733, 904, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1510, 1029, 1733, 904, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1511, 1030, 1735, 905, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1512, 1030, 1735, 905, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1513, 1030, 1735, 905, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1514, 1031, 1739, 906, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1515, 1031, 1739, 906, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1516, 1031, 1739, 906, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1517, 1032, 1740, 907, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1518, 1032, 1740, 907, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1519, 1032, 1740, 907, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1520, 1033, 1745, 908, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1521, 1033, 1745, 908, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1522, 1033, 1745, 908, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1523, 1034, 1755, 909, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1524, 1034, 1755, 909, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1525, 1034, 1755, 909, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1526, 1035, 1763, 910, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1527, 1035, 1763, 910, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1528, 1035, 1763, 910, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1529, 1036, 1764, 911, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1530, 1036, 1764, 911, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1531, 1036, 1764, 911, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1532, 1037, 1768, 912, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1533, 1037, 1768, 912, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1534, 1037, 1768, 912, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1535, 1038, 1773, 913, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1536, 1038, 1773, 913, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1537, 1038, 1773, 913, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1538, 1039, 1777, 914, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1539, 1039, 1777, 914, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1540, 1039, 1777, 914, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1541, 1040, 1787, 915, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1542, 1040, 1787, 915, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1543, 1040, 1787, 915, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1544, 1041, 1789, 916, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1545, 1041, 1789, 916, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1546, 1041, 1789, 916, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1547, 1042, 1792, 917, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1548, 1042, 1792, 917, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1549, 1042, 1792, 917, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1550, 1043, 1794, 918, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1551, 1043, 1794, 918, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1552, 1043, 1794, 918, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1553, 1044, 1798, 919, 'Milestone 1', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1554, 1044, 1798, 919, 'Milestone 2', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1555, 1044, 1798, 919, 'Milestone 3', 'Desc', 'in_progress', '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09');

-- --------------------------------------------------------

--
-- Table structure for table `milestone_items`
--

CREATE TABLE `milestone_items` (
  `item_id` int(11) NOT NULL,
  `milestone_id` int(11) NOT NULL,
  `sequence_order` int(11) NOT NULL,
  `percentage_progress` decimal(5,2) NOT NULL,
  `milestone_item_title` varchar(255) NOT NULL,
  `milestone_item_description` text DEFAULT NULL,
  `milestone_item_cost` decimal(12,2) NOT NULL,
  `date_to_finish` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `milestone_items`
--

INSERT INTO `milestone_items` (`item_id`, `milestone_id`, `sequence_order`, `percentage_progress`, `milestone_item_title`, `milestone_item_description`, `milestone_item_cost`, `date_to_finish`) VALUES
(2547, 1466, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2548, 1467, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2549, 1468, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2550, 1469, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2551, 1470, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2552, 1471, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2553, 1472, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2554, 1473, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2555, 1474, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2556, 1475, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2557, 1476, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2558, 1477, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2559, 1478, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2560, 1479, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2561, 1480, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2562, 1481, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2563, 1482, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2564, 1483, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2565, 1484, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2566, 1485, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2567, 1486, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2568, 1487, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2569, 1488, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2570, 1489, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2571, 1490, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2572, 1491, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2573, 1492, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2574, 1493, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2575, 1494, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2576, 1495, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2577, 1496, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2578, 1497, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2579, 1498, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2580, 1499, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2581, 1500, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2582, 1501, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2583, 1502, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2584, 1503, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2585, 1504, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2586, 1505, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2587, 1506, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2588, 1507, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2589, 1508, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2590, 1509, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2591, 1510, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2592, 1511, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2593, 1512, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2594, 1513, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2595, 1514, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2596, 1515, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2597, 1516, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2598, 1517, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2599, 1518, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2600, 1519, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2601, 1520, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2602, 1521, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2603, 1522, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2604, 1523, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2605, 1524, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2606, 1525, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2607, 1526, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2608, 1527, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2609, 1528, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2610, 1529, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2611, 1530, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2612, 1531, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2613, 1532, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2614, 1533, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2615, 1534, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2616, 1535, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2617, 1536, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2618, 1537, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2619, 1538, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2620, 1539, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2621, 1540, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2622, 1541, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2623, 1542, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2624, 1543, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2625, 1544, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2626, 1545, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2627, 1546, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2628, 1547, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2629, 1548, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2630, 1549, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2631, 1550, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2632, 1551, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2633, 1552, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2634, 1553, 1, 40.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2635, 1554, 1, 100.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2636, 1555, 1, 50.00, 'Primary Task', 'Desc', 25000.00, '2025-12-20 15:49:09'),
(2674, 1466, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2675, 1468, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2676, 1469, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2677, 1471, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2678, 1472, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2679, 1474, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2680, 1475, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2681, 1477, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2682, 1478, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2683, 1480, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2684, 1481, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2685, 1483, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2686, 1484, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2687, 1486, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2688, 1487, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2689, 1489, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2690, 1490, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2691, 1492, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2692, 1493, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2693, 1495, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2694, 1496, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2695, 1498, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2696, 1499, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2697, 1501, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2698, 1502, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2699, 1504, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2700, 1505, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2701, 1507, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2702, 1508, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2703, 1510, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2704, 1511, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2705, 1513, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2706, 1514, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2707, 1516, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2708, 1517, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2709, 1519, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2710, 1520, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2711, 1522, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2712, 1523, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2713, 1525, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2714, 1526, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2715, 1528, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2716, 1529, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2717, 1531, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2718, 1532, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2719, 1534, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2720, 1535, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2721, 1537, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2722, 1538, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2723, 1540, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2724, 1541, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2725, 1543, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2726, 1544, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2727, 1546, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2728, 1547, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2729, 1549, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2730, 1550, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2731, 1552, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2732, 1553, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2733, 1555, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, '2025-12-25 15:49:09'),
(2737, 1466, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2738, 1469, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2739, 1472, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2740, 1475, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2741, 1478, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2742, 1481, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2743, 1484, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2744, 1487, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2745, 1490, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2746, 1493, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2747, 1496, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2748, 1499, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2749, 1502, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2750, 1505, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2751, 1508, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2752, 1511, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2753, 1514, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2754, 1517, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2755, 1520, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2756, 1523, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2757, 1526, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2758, 1529, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2759, 1532, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2760, 1535, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2761, 1538, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2762, 1541, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2763, 1544, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2764, 1547, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2765, 1550, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09'),
(2766, 1553, 3, 30.00, 'Final Task', 'Desc', 10000.00, '2025-12-30 15:49:09');

-- --------------------------------------------------------

--
-- Table structure for table `milestone_payments`
--

CREATE TABLE `milestone_payments` (
  `payment_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `contractor_user_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_type` enum('cash','check','bank_transfer','online_payment') NOT NULL,
  `transaction_number` varchar(100) DEFAULT NULL,
  `receipt_photo` varchar(255) NOT NULL,
  `transaction_date` date DEFAULT current_timestamp(),
  `payment_status` enum('submitted','approved','rejected','deleted') DEFAULT 'submitted',
  `reason` text DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `milestone_payments`
--

INSERT INTO `milestone_payments` (`payment_id`, `item_id`, `project_id`, `owner_id`, `contractor_user_id`, `amount`, `payment_type`, `transaction_number`, `receipt_photo`, `transaction_date`, `payment_status`, `reason`, `updated_at`) VALUES
(764, 2547, 1015, 1727, 2050, 25000.00, 'bank_transfer', 'TXN-2547', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(765, 2674, 1015, 1727, 2050, 25000.00, 'bank_transfer', 'TXN-2674', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(766, 2737, 1015, 1727, 1879, 25000.00, 'bank_transfer', 'TXN-2737', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(767, 2548, 1015, 1727, 1879, 25000.00, 'bank_transfer', 'TXN-2548', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(768, 2549, 1015, 1727, 2050, 25000.00, 'bank_transfer', 'TXN-2549', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(769, 2675, 1015, 1727, 2050, 25000.00, 'bank_transfer', 'TXN-2675', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(770, 2550, 1016, 1743, 1883, 25000.00, 'bank_transfer', 'TXN-2550', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(771, 2676, 1016, 1743, 1883, 25000.00, 'bank_transfer', 'TXN-2676', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(772, 2738, 1016, 1743, 2012, 25000.00, 'bank_transfer', 'TXN-2738', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(773, 2551, 1016, 1743, 1883, 25000.00, 'bank_transfer', 'TXN-2551', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(774, 2552, 1016, 1743, 2048, 25000.00, 'bank_transfer', 'TXN-2552', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(775, 2677, 1016, 1743, 2048, 25000.00, 'bank_transfer', 'TXN-2677', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(776, 2553, 1017, 1737, 2044, 25000.00, 'bank_transfer', 'TXN-2553', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(777, 2678, 1017, 1737, 1889, 25000.00, 'bank_transfer', 'TXN-2678', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(778, 2739, 1017, 1737, 2044, 25000.00, 'bank_transfer', 'TXN-2739', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(779, 2554, 1017, 1737, 1889, 25000.00, 'bank_transfer', 'TXN-2554', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(780, 2555, 1017, 1737, 2044, 25000.00, 'bank_transfer', 'TXN-2555', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(781, 2679, 1017, 1737, 1889, 25000.00, 'bank_transfer', 'TXN-2679', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(782, 2556, 1018, 1723, 2024, 25000.00, 'bank_transfer', 'TXN-2556', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(783, 2680, 1018, 1723, 1891, 25000.00, 'bank_transfer', 'TXN-2680', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(784, 2740, 1018, 1723, 2024, 25000.00, 'bank_transfer', 'TXN-2740', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(785, 2557, 1018, 1723, 2024, 25000.00, 'bank_transfer', 'TXN-2557', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(786, 2558, 1018, 1723, 2024, 25000.00, 'bank_transfer', 'TXN-2558', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(787, 2681, 1018, 1723, 1891, 25000.00, 'bank_transfer', 'TXN-2681', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(788, 2559, 1019, 1755, 1891, 25000.00, 'bank_transfer', 'TXN-2559', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(789, 2682, 1019, 1755, 1891, 25000.00, 'bank_transfer', 'TXN-2682', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(790, 2741, 1019, 1755, 1891, 25000.00, 'bank_transfer', 'TXN-2741', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(791, 2560, 1019, 1755, 1891, 25000.00, 'bank_transfer', 'TXN-2560', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(792, 2561, 1019, 1755, 1891, 25000.00, 'bank_transfer', 'TXN-2561', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(793, 2683, 1019, 1755, 2024, 25000.00, 'bank_transfer', 'TXN-2683', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(794, 2562, 1020, 1693, 1896, 25000.00, 'bank_transfer', 'TXN-2562', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(795, 2684, 1020, 1693, 1896, 25000.00, 'bank_transfer', 'TXN-2684', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(796, 2742, 1020, 1693, 1896, 25000.00, 'bank_transfer', 'TXN-2742', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(797, 2563, 1020, 1693, 1896, 25000.00, 'bank_transfer', 'TXN-2563', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(798, 2564, 1020, 1693, 1896, 25000.00, 'bank_transfer', 'TXN-2564', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(799, 2685, 1020, 1693, 1896, 25000.00, 'bank_transfer', 'TXN-2685', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(800, 2565, 1021, 1710, 1904, 25000.00, 'bank_transfer', 'TXN-2565', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(801, 2686, 1021, 1710, 1904, 25000.00, 'bank_transfer', 'TXN-2686', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(802, 2743, 1021, 1710, 1904, 25000.00, 'bank_transfer', 'TXN-2743', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(803, 2566, 1021, 1710, 1904, 25000.00, 'bank_transfer', 'TXN-2566', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(804, 2567, 1021, 1710, 1904, 25000.00, 'bank_transfer', 'TXN-2567', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(805, 2687, 1021, 1710, 1904, 25000.00, 'bank_transfer', 'TXN-2687', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(806, 2568, 1022, 1719, 2016, 25000.00, 'bank_transfer', 'TXN-2568', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(807, 2688, 1022, 1719, 1906, 25000.00, 'bank_transfer', 'TXN-2688', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(808, 2744, 1022, 1719, 2016, 25000.00, 'bank_transfer', 'TXN-2744', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(809, 2569, 1022, 1719, 1906, 25000.00, 'bank_transfer', 'TXN-2569', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(810, 2570, 1022, 1719, 2019, 25000.00, 'bank_transfer', 'TXN-2570', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(811, 2689, 1022, 1719, 2016, 25000.00, 'bank_transfer', 'TXN-2689', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(812, 2571, 1023, 1738, 2016, 25000.00, 'bank_transfer', 'TXN-2571', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(813, 2690, 1023, 1738, 2019, 25000.00, 'bank_transfer', 'TXN-2690', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` enum('Milestone Update','Bid Status','Payment Reminder','Project Alert') NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `delivery_method` enum('App','Email','Both') DEFAULT 'App',
  `action_link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `message`, `type`, `is_read`, `delivery_method`, `action_link`, `created_at`) VALUES
(3418, 1, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3419, 10, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3420, 100, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3421, 101, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3422, 102, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3423, 103, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3424, 104, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3425, 105, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3426, 106, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3427, 107, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3428, 108, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3429, 109, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3430, 11, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3431, 110, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3432, 111, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3433, 112, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3434, 113, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3435, 114, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3436, 115, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3437, 116, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3438, 117, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3439, 118, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3440, 119, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3441, 12, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3442, 120, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3443, 121, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3444, 122, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3445, 123, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3446, 124, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3447, 125, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3448, 126, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3449, 127, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3450, 128, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3451, 129, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3452, 13, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3453, 130, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3454, 131, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3455, 132, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3456, 133, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3457, 134, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3458, 135, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3459, 136, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3460, 137, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3461, 138, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3462, 139, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3463, 14, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3464, 140, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3465, 141, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3466, 142, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3467, 143, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3468, 144, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3469, 145, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3470, 146, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3471, 147, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3472, 148, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3473, 149, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3474, 15, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3475, 150, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3476, 151, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3477, 152, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3478, 153, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3479, 154, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3480, 155, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3481, 156, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3482, 157, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3483, 158, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3484, 159, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3485, 16, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3486, 160, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3487, 161, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3488, 162, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3489, 163, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3490, 164, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3491, 165, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3492, 166, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3493, 167, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3494, 168, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3495, 169, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3496, 17, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3497, 170, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3498, 171, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3499, 172, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3500, 173, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3501, 174, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3502, 175, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3503, 176, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3504, 177, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3505, 178, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3506, 179, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3507, 18, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3508, 180, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3509, 181, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3510, 182, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3511, 183, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3512, 184, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3513, 185, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3514, 186, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3515, 187, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3516, 188, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3517, 189, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3518, 19, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3519, 190, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3520, 191, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3521, 192, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3522, 193, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3523, 194, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3524, 195, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3525, 196, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3526, 197, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3527, 198, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3528, 199, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3529, 2, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3530, 20, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3531, 200, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3532, 201, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3533, 202, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3534, 203, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3535, 204, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3536, 205, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3537, 206, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3538, 207, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3539, 208, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3540, 209, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3541, 21, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3542, 210, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3543, 211, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3544, 212, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3545, 213, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3546, 214, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3547, 215, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3548, 216, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3549, 217, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3550, 218, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3551, 219, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3552, 22, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3553, 220, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3554, 23, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3555, 24, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3556, 25, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3557, 26, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3558, 27, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3559, 28, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3560, 29, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3561, 3, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3562, 30, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3563, 31, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3564, 32, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3565, 33, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3566, 34, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3567, 35, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3568, 36, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3569, 37, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3570, 38, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3571, 39, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3572, 4, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3573, 40, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3574, 41, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3575, 42, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3576, 43, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3577, 44, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3578, 45, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3579, 46, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3580, 47, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3581, 48, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3582, 49, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3583, 5, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3584, 50, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3585, 51, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3586, 52, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3587, 53, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3588, 54, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3589, 55, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3590, 56, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3591, 57, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3592, 58, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3593, 59, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3594, 6, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3595, 60, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3596, 61, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3597, 62, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3598, 63, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3599, 64, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3600, 65, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3601, 66, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3602, 67, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3603, 68, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3604, 69, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3605, 7, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3606, 70, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3607, 71, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3608, 72, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3609, 73, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3610, 74, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3611, 75, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3612, 76, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3613, 77, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3614, 78, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3615, 79, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3616, 8, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3617, 80, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3618, 81, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3619, 82, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3620, 83, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3621, 84, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3622, 85, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3623, 86, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3624, 87, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3625, 88, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3626, 89, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3627, 9, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3628, 90, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3629, 91, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3630, 92, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3631, 93, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3632, 94, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3633, 95, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3634, 96, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3635, 97, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3636, 98, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3637, 99, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3638, 221, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3639, 222, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3640, 223, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3641, 224, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3642, 225, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3643, 226, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3644, 227, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3645, 228, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3646, 229, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3647, 230, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3648, 231, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3649, 232, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3650, 233, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3651, 234, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3652, 235, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3653, 236, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3654, 237, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3655, 238, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3656, 239, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3657, 240, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3658, 241, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3659, 242, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3660, 243, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3661, 244, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3662, 245, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3663, 246, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3664, 247, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3665, 248, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3666, 249, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3667, 250, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3668, 251, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3669, 252, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3670, 253, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3671, 254, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3672, 255, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3673, 256, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3674, 257, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3675, 258, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3676, 259, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3677, 260, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3678, 261, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3679, 262, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3680, 263, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3681, 264, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3682, 265, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3683, 266, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3684, 267, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3685, 268, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3686, 269, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09'),
(3687, 270, 'Welcome!', 'Project Alert', 0, 'App', NULL, '2025-12-15 07:49:09');

-- --------------------------------------------------------

--
-- Table structure for table `occupations`
--

CREATE TABLE `occupations` (
  `id` int(11) NOT NULL,
  `occupation_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `occupations`
--

INSERT INTO `occupations` (`id`, `occupation_name`) VALUES
(1, 'Teacher'),
(2, 'Engineer'),
(3, 'Doctor'),
(4, 'Nurse'),
(5, 'Police Officer'),
(6, 'Firefighter'),
(7, 'Lawyer'),
(8, 'Architect'),
(9, 'Driver'),
(10, 'Construction Worker'),
(11, 'Electrician'),
(12, 'Plumber'),
(13, 'Farmer'),
(14, 'Fisherman'),
(15, 'Office Clerk'),
(16, 'Salesperson'),
(17, 'Cashier'),
(18, 'Security Guard'),
(19, 'IT Specialist'),
(20, 'Call Center Agent'),
(21, 'Chef'),
(22, 'Accountant'),
(23, 'Businessman'),
(24, 'Student'),
(25, 'Unemployed'),
(26, 'Others');

-- --------------------------------------------------------

--
-- Table structure for table `payment_plans`
--

CREATE TABLE `payment_plans` (
  `plan_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `contractor_id` int(11) NOT NULL,
  `payment_mode` enum('full_payment','downpayment') NOT NULL,
  `total_project_cost` decimal(12,2) NOT NULL,
  `downpayment_amount` decimal(12,2) DEFAULT 0.00,
  `is_confirmed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_plans`
--

INSERT INTO `payment_plans` (`plan_id`, `project_id`, `contractor_id`, `payment_mode`, `total_project_cost`, `downpayment_amount`, `is_confirmed`, `created_at`, `updated_at`) VALUES
(890, 1015, 1690, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(891, 1016, 1694, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(892, 1017, 1700, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(893, 1018, 1702, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(894, 1019, 1702, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(895, 1020, 1707, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(896, 1021, 1715, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(897, 1022, 1717, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(898, 1023, 1717, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(899, 1024, 1718, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(900, 1025, 1718, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(901, 1026, 1725, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(902, 1027, 1727, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(903, 1028, 1730, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(904, 1029, 1733, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(905, 1030, 1735, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(906, 1031, 1739, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(907, 1032, 1740, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(908, 1033, 1745, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(909, 1034, 1755, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(910, 1035, 1763, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(911, 1036, 1764, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(912, 1037, 1768, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(913, 1038, 1773, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(914, 1039, 1777, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(915, 1040, 1787, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(916, 1041, 1789, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(917, 1042, 1792, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(918, 1043, 1794, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(919, 1044, 1798, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09');

-- --------------------------------------------------------

--
-- Table structure for table `platform_payments`
--

CREATE TABLE `platform_payments` (
  `platform_payment_id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `contractor_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `payment_for` enum('commission','boosted_post') NOT NULL,
  `percentage` decimal(5,2) DEFAULT 0.02,
  `amount` decimal(15,2) NOT NULL,
  `transaction_number` varchar(100) DEFAULT NULL,
  `receipt_photo` varchar(255) NOT NULL,
  `transaction_date` timestamp NULL DEFAULT current_timestamp(),
  `is_approved` tinyint(1) DEFAULT 0,
  `approved_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `progress`
--

CREATE TABLE `progress` (
  `progress_id` int(11) NOT NULL,
  `milestone_item_id` int(11) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `progress_status` enum('submitted','approved','rejected','deleted') DEFAULT 'submitted',
  `delete_reason` text DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `progress`
--

INSERT INTO `progress` (`progress_id`, `milestone_item_id`, `purpose`, `progress_status`, `delete_reason`, `submitted_at`, `updated_at`) VALUES
(764, 2547, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(765, 2674, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(766, 2737, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(767, 2548, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(768, 2549, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(769, 2675, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(770, 2550, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(771, 2676, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(772, 2738, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(773, 2551, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(774, 2552, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(775, 2677, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(776, 2553, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(777, 2678, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(778, 2739, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(779, 2554, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(780, 2555, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(781, 2679, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(782, 2556, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(783, 2680, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(784, 2740, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(785, 2557, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(786, 2558, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(787, 2681, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(788, 2559, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(789, 2682, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(790, 2741, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(791, 2560, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(792, 2561, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(793, 2683, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(794, 2562, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(795, 2684, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(796, 2742, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(797, 2563, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(798, 2564, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(799, 2685, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(800, 2565, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(801, 2686, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(802, 2743, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(803, 2566, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(804, 2567, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(805, 2687, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(806, 2568, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(807, 2688, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(808, 2744, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(809, 2569, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(810, 2570, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(811, 2689, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(812, 2571, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(813, 2690, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `progress_files`
--

CREATE TABLE `progress_files` (
  `file_id` int(11) NOT NULL,
  `progress_id` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL,
  `relationship_id` int(11) DEFAULT NULL,
  `project_title` varchar(200) NOT NULL,
  `project_description` text NOT NULL,
  `project_location` text NOT NULL,
  `budget_range_min` decimal(15,2) DEFAULT NULL,
  `budget_range_max` decimal(15,2) DEFAULT NULL,
  `lot_size` int(11) NOT NULL,
  `floor_area` int(11) NOT NULL,
  `property_type` enum('Residential','Commercial','Industrial','Agricultural') NOT NULL,
  `type_id` int(11) NOT NULL,
  `if_others_ctype` varchar(255) DEFAULT NULL,
  `to_finish` int(11) DEFAULT NULL,
  `project_status` enum('open','bidding_closed','in_progress','completed','terminated','deleted_post','halt') DEFAULT 'open',
  `selected_contractor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`project_id`, `relationship_id`, `project_title`, `project_description`, `project_location`, `budget_range_min`, `budget_range_max`, `lot_size`, `floor_area`, `property_type`, `type_id`, `if_others_ctype`, `to_finish`, `project_status`, `selected_contractor_id`) VALUES
(985, 987, 'Project 987', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'terminated', NULL),
(986, 988, 'Project 988', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(987, 989, 'Project 989', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(988, 990, 'Project 990', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(989, 994, 'Project 994', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(990, 995, 'Project 995', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(991, 997, 'Project 997', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(992, 998, 'Project 998', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(993, 999, 'Project 999', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(994, 1000, 'Project 1000', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(995, 1003, 'Project 1003', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(996, 1005, 'Project 1005', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(997, 1010, 'Project 1010', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(998, 1012, 'Project 1012', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(999, 1013, 'Project 1013', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(1000, 1018, 'Project 1018', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(1001, 1019, 'Project 1019', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(1002, 1020, 'Project 1020', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(1003, 1021, 'Project 1021', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(1004, 1022, 'Project 1022', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(1005, 1023, 'Project 1023', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(1006, 1024, 'Project 1024', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(1007, 1027, 'Project 1027', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(1008, 1032, 'Project 1032', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(1009, 1034, 'Project 1034', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(1010, 1035, 'Project 1035', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(1011, 1036, 'Project 1036', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(1012, 1037, 'Project 1037', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(1013, 1038, 'Project 1038', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(1014, 1042, 'Project 1042', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(1015, 1017, 'Project 1017', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1690),
(1016, 1030, 'Project 1030', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1694),
(1017, 1025, 'Project 1025', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1700),
(1018, 1014, 'Project 1014', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1702),
(1019, 1040, 'Project 1040', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1702),
(1020, 991, 'Project 991', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1707),
(1021, 1004, 'Project 1004', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1715),
(1022, 1011, 'Project 1011', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1717),
(1023, 1026, 'Project 1026', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1717),
(1024, 1001, 'Project 1001', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1718),
(1025, 1043, 'Project 1043', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1718),
(1026, 1002, 'Project 1002', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1725),
(1027, 986, 'Project 986', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1727),
(1028, 1041, 'Project 1041', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1730),
(1029, 1008, 'Project 1008', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1733),
(1030, 1009, 'Project 1009', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1735),
(1031, 1039, 'Project 1039', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1739),
(1032, 1044, 'Project 1044', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1740),
(1033, 985, 'Project 985', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'terminated', 1745),
(1034, 1029, 'Project 1029', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1755),
(1035, 1028, 'Project 1028', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1763),
(1036, 1007, 'Project 1007', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1764),
(1037, 1031, 'Project 1031', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1768),
(1038, 1033, 'Project 1033', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1773),
(1039, 996, 'Project 996', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1777),
(1040, 1015, 'Project 1015', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1787),
(1041, 993, 'Project 993', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1789),
(1042, 992, 'Project 992', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1792),
(1043, 1006, 'Project 1006', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1794),
(1044, 1016, 'Project 1016', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1798);

-- --------------------------------------------------------

--
-- Table structure for table `project_files`
--

CREATE TABLE `project_files` (
  `file_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `file_type` enum('building permit','blueprint','desired design','title','others') NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_relationships`
--

CREATE TABLE `project_relationships` (
  `rel_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `selected_contractor_id` int(11) DEFAULT NULL,
  `project_post_status` enum('under_review','deleted','approved','rejected','due') NOT NULL DEFAULT 'under_review',
  `admin_reason` text DEFAULT NULL COMMENT 'Reason for post rejection by admin',
  `reason` text DEFAULT NULL,
  `bidding_due` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_relationships`
--

INSERT INTO `project_relationships` (`rel_id`, `owner_id`, `selected_contractor_id`, `project_post_status`, `admin_reason`, `reason`, `bidding_due`, `created_at`, `updated_at`) VALUES
(985, 1687, 1745, 'approved', NULL, NULL, NULL, '2025-04-11 07:49:09', '2025-12-15 07:49:09'),
(986, 1688, 1727, 'rejected', NULL, NULL, NULL, '2025-12-11 07:49:09', '2025-12-15 07:49:09'),
(987, 1689, NULL, 'approved', NULL, NULL, NULL, '2025-04-26 07:49:09', '2025-12-15 07:49:09'),
(988, 1690, NULL, 'rejected', NULL, NULL, NULL, '2025-03-10 07:49:09', '2025-12-15 07:49:09'),
(989, 1691, NULL, 'under_review', NULL, NULL, NULL, '2025-05-15 07:49:09', '2025-12-15 07:49:09'),
(990, 1692, NULL, 'under_review', 'Violation.', NULL, NULL, '2025-05-12 07:49:09', '2025-12-15 07:49:09'),
(991, 1693, 1707, 'approved', 'Violation.', NULL, NULL, '2025-05-28 07:49:09', '2025-12-15 07:49:09'),
(992, 1695, 1792, 'approved', 'Violation.', NULL, NULL, '2025-06-14 07:49:09', '2025-12-15 07:49:09'),
(993, 1697, 1789, 'approved', NULL, NULL, NULL, '2024-12-22 07:49:09', '2025-12-15 07:49:09'),
(994, 1698, NULL, 'rejected', NULL, NULL, NULL, '2025-08-24 07:49:09', '2025-12-15 07:49:09'),
(995, 1699, NULL, 'approved', 'Violation.', NULL, NULL, '2025-08-02 07:49:09', '2025-12-15 07:49:09'),
(996, 1700, 1777, 'rejected', NULL, NULL, NULL, '2025-07-31 07:49:09', '2025-12-15 07:49:09'),
(997, 1701, NULL, 'under_review', NULL, NULL, NULL, '2025-12-10 07:49:09', '2025-12-15 07:49:09'),
(998, 1702, NULL, 'rejected', NULL, NULL, NULL, '2025-04-13 07:49:09', '2025-12-15 07:49:09'),
(999, 1704, NULL, 'approved', NULL, NULL, NULL, '2025-04-19 07:49:09', '2025-12-15 07:49:09'),
(1000, 1705, NULL, 'under_review', NULL, NULL, NULL, '2025-06-27 07:49:09', '2025-12-15 07:49:09'),
(1001, 1707, 1718, 'rejected', NULL, NULL, NULL, '2025-01-04 07:49:09', '2025-12-15 07:49:09'),
(1002, 1708, 1725, 'under_review', 'Violation.', NULL, NULL, '2025-08-18 07:49:09', '2025-12-15 07:49:09'),
(1003, 1709, NULL, 'rejected', NULL, NULL, NULL, '2025-12-12 07:49:09', '2025-12-15 07:49:09'),
(1004, 1710, 1715, 'rejected', 'Violation.', NULL, NULL, '2025-12-02 07:49:09', '2025-12-15 07:49:09'),
(1005, 1711, NULL, 'rejected', NULL, NULL, NULL, '2025-11-12 07:49:09', '2025-12-15 07:49:09'),
(1006, 1713, 1794, 'under_review', NULL, NULL, NULL, '2025-03-09 07:49:09', '2025-12-15 07:49:09'),
(1007, 1714, 1764, 'approved', NULL, NULL, NULL, '2025-06-04 07:49:09', '2025-12-15 07:49:09'),
(1008, 1715, 1733, 'approved', 'Violation.', NULL, NULL, '2025-03-31 07:49:09', '2025-12-15 07:49:09'),
(1009, 1717, 1735, 'under_review', 'Violation.', NULL, NULL, '2025-05-04 07:49:09', '2025-12-15 07:49:09'),
(1010, 1718, NULL, 'rejected', NULL, NULL, NULL, '2025-12-08 07:49:09', '2025-12-15 07:49:09'),
(1011, 1719, 1717, 'under_review', NULL, NULL, NULL, '2025-09-27 07:49:09', '2025-12-15 07:49:09'),
(1012, 1720, NULL, 'approved', NULL, NULL, NULL, '2025-06-25 07:49:09', '2025-12-15 07:49:09'),
(1013, 1722, NULL, 'approved', 'Violation.', NULL, NULL, '2025-10-04 07:49:09', '2025-12-15 07:49:09'),
(1014, 1723, 1702, 'rejected', NULL, NULL, NULL, '2025-04-29 07:49:09', '2025-12-15 07:49:09'),
(1015, 1724, 1787, 'rejected', NULL, NULL, NULL, '2025-05-12 07:49:09', '2025-12-15 07:49:09'),
(1016, 1725, 1798, 'approved', NULL, NULL, NULL, '2025-02-15 07:49:09', '2025-12-15 07:49:09'),
(1017, 1727, 1690, 'under_review', NULL, NULL, NULL, '2025-08-11 07:49:09', '2025-12-15 07:49:09'),
(1018, 1728, NULL, 'approved', NULL, NULL, NULL, '2025-05-22 07:49:09', '2025-12-15 07:49:09'),
(1019, 1729, NULL, 'approved', 'Violation.', NULL, NULL, '2025-10-22 07:49:09', '2025-12-15 07:49:09'),
(1020, 1731, NULL, 'rejected', NULL, NULL, NULL, '2025-03-29 07:49:09', '2025-12-15 07:49:09'),
(1021, 1732, NULL, 'approved', 'Violation.', NULL, NULL, '2025-10-20 07:49:09', '2025-12-15 07:49:09'),
(1022, 1733, NULL, 'approved', NULL, NULL, NULL, '2025-10-08 07:49:09', '2025-12-15 07:49:09'),
(1023, 1734, NULL, 'under_review', NULL, NULL, NULL, '2025-04-16 07:49:09', '2025-12-15 07:49:09'),
(1024, 1735, NULL, 'approved', NULL, NULL, NULL, '2025-06-18 07:49:09', '2025-12-15 07:49:09'),
(1025, 1737, 1700, 'approved', NULL, NULL, NULL, '2025-10-30 07:49:09', '2025-12-15 07:49:09'),
(1026, 1738, 1717, 'under_review', NULL, NULL, NULL, '2025-03-17 07:49:09', '2025-12-15 07:49:09'),
(1027, 1740, NULL, 'under_review', NULL, NULL, NULL, '2025-12-14 07:49:09', '2025-12-15 07:49:09'),
(1028, 1741, 1763, 'under_review', 'Violation.', NULL, NULL, '2025-06-16 07:49:09', '2025-12-15 07:49:09'),
(1029, 1742, 1755, 'approved', NULL, NULL, NULL, '2025-09-07 07:49:09', '2025-12-15 07:49:09'),
(1030, 1743, 1694, 'approved', NULL, NULL, NULL, '2025-12-10 07:49:09', '2025-12-15 07:49:09'),
(1031, 1744, 1768, 'rejected', NULL, NULL, NULL, '2025-07-16 07:49:09', '2025-12-15 07:49:09'),
(1032, 1745, NULL, 'rejected', 'Violation.', NULL, NULL, '2025-11-28 07:49:09', '2025-12-15 07:49:09'),
(1033, 1747, 1773, 'under_review', 'Violation.', NULL, NULL, '2025-07-03 07:49:09', '2025-12-15 07:49:09'),
(1034, 1749, NULL, 'under_review', NULL, NULL, NULL, '2025-05-17 07:49:09', '2025-12-15 07:49:09'),
(1035, 1750, NULL, 'rejected', NULL, NULL, NULL, '2025-05-26 07:49:09', '2025-12-15 07:49:09'),
(1036, 1751, NULL, 'rejected', NULL, NULL, NULL, '2025-10-15 07:49:09', '2025-12-15 07:49:09'),
(1037, 1752, NULL, 'rejected', NULL, NULL, NULL, '2025-01-13 07:49:09', '2025-12-15 07:49:09'),
(1038, 1753, NULL, 'approved', 'Violation.', NULL, NULL, '2025-04-05 07:49:09', '2025-12-15 07:49:09'),
(1039, 1754, 1739, 'under_review', NULL, NULL, NULL, '2025-11-17 07:49:09', '2025-12-15 07:49:09'),
(1040, 1755, 1702, 'approved', NULL, NULL, NULL, '2025-05-23 07:49:09', '2025-12-15 07:49:09'),
(1041, 1758, 1730, 'under_review', NULL, NULL, NULL, '2025-08-06 07:49:09', '2025-12-15 07:49:09'),
(1042, 1759, NULL, 'rejected', NULL, NULL, NULL, '2025-09-08 07:49:09', '2025-12-15 07:49:09'),
(1043, 1760, 1718, 'under_review', NULL, NULL, NULL, '2025-04-17 07:49:09', '2025-12-15 07:49:09'),
(1044, 1761, 1740, 'under_review', 'Violation.', NULL, NULL, '2025-02-17 07:49:09', '2025-12-15 07:49:09');

-- --------------------------------------------------------

--
-- Table structure for table `property_owners`
--

CREATE TABLE `property_owners` (
  `owner_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `address` varchar(500) NOT NULL,
  `valid_id_id` int(11) DEFAULT NULL,
  `valid_id_photo` varchar(255) DEFAULT NULL,
  `valid_id_back_photo` varchar(255) NOT NULL,
  `police_clearance` varchar(255) NOT NULL,
  `date_of_birth` date NOT NULL,
  `age` int(11) NOT NULL,
  `occupation_id` int(11) DEFAULT NULL,
  `occupation_other` varchar(200) DEFAULT NULL,
  `verification_status` enum('pending','rejected','approved','deleted') NOT NULL DEFAULT 'pending',
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `suspension_until` date DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `deletion_reason` text DEFAULT NULL,
  `suspension_reason` text DEFAULT NULL,
  `verification_date` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_owners`
--

INSERT INTO `property_owners` (`owner_id`, `user_id`, `last_name`, `middle_name`, `first_name`, `phone_number`, `address`, `valid_id_id`, `valid_id_photo`, `valid_id_back_photo`, `police_clearance`, `date_of_birth`, `age`, `occupation_id`, `occupation_other`, `verification_status`, `is_active`, `suspension_until`, `rejection_reason`, `deletion_reason`, `suspension_reason`, `verification_date`, `created_at`) VALUES
(1687, 101, 'Tampus', NULL, 'Jeffslazir Augheight', '09990000101', 'Tetuan, Poblacion, Mankayan, Benguet 7000', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 3, NULL, 'approved', 0, '9999-12-31', NULL, '', 'ainnoway', '2025-12-16 05:32:21', '2025-04-06 07:49:09'),
(1688, 102, 'OwnerLast102', NULL, 'OwnerFirst102', '09990000102', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 21, NULL, 'pending', 0, NULL, NULL, '', NULL, '2025-12-16 04:29:45', '2025-12-06 07:49:09'),
(1689, 103, 'OwnerLast103', NULL, 'OwnerFirst103', '09990000103', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 23, NULL, 'approved', 0, '2025-12-18', NULL, NULL, 'ainnoway', '2025-04-22 07:49:09', '2025-04-21 07:49:09'),
(1690, 104, 'OwnerLast104', NULL, 'OwnerFirst104', '09990000104', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 20, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-03-06 07:49:09', '2025-03-05 07:49:09'),
(1691, 105, 'OwnerLast105', NULL, 'OwnerFirst105', '09990000105', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 21, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-11 07:49:09', '2025-05-10 07:49:09'),
(1692, 106, 'OwnerLast106', NULL, 'OwnerFirst106', '09990000106', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 3, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-08 07:49:09', '2025-05-07 07:49:09'),
(1693, 107, 'OwnerLast107', NULL, 'OwnerFirst107', '09990000107', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 4, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-24 07:49:09', '2025-05-23 07:49:09'),
(1694, 108, 'OwnerLast108', NULL, 'OwnerFirst108', '09990000108', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 21, NULL, 'pending', 0, NULL, '', NULL, NULL, '2025-12-15 08:06:48', '2025-03-29 07:49:09'),
(1695, 109, 'OwnerLast109', NULL, 'OwnerFirst109', '09990000109', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 18, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-06-10 07:49:09', '2025-06-09 07:49:09'),
(1696, 110, 'OwnerLast110', NULL, 'OwnerFirst110', '09990000110', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 3, NULL, 'rejected', 1, NULL, 'Invalid ID.', NULL, NULL, '2025-07-10 07:49:09', '2025-07-09 07:49:09'),
(1697, 111, 'OwnerLast111', NULL, 'OwnerFirst111', '09990000111', 'Tetuan, Tadiangan, Tuba, Benguet 2342', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 15, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2024-12-18 07:49:09', '2024-12-17 07:49:09'),
(1698, 112, 'OwnerLast112', NULL, 'OwnerFirst112', '09990000112', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 4, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-08-20 07:49:09', '2025-08-19 07:49:09'),
(1699, 113, 'OwnerLast113', NULL, 'OwnerFirst113', '09990000113', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 6, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-07-29 07:49:09', '2025-07-28 07:49:09'),
(1700, 114, 'OwnerLast114', NULL, 'OwnerFirst114', '09990000114', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 23, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-07-27 07:49:09', '2025-07-26 07:49:09'),
(1701, 115, 'OwnerLast115', NULL, 'OwnerFirst115', '09990000115', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 13, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-06 07:49:09', '2025-12-05 07:49:09'),
(1702, 116, 'OwnerLast116', NULL, 'OwnerFirst116', '09990000116', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 18, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-04-09 07:49:09', '2025-04-08 07:49:09'),
(1703, 117, 'OwnerLast117', NULL, 'OwnerFirst117', '09990000117', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 13, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-10-17 07:49:09'),
(1704, 118, 'OwnerLast118', NULL, 'OwnerFirst118', '09990000118', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 23, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-04-15 07:49:09', '2025-04-14 07:49:09'),
(1705, 119, 'OwnerLast119', NULL, 'OwnerFirst119', '09990000119', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 1, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-06-23 07:49:09', '2025-06-22 07:49:09'),
(1706, 120, 'OwnerLast120', NULL, 'OwnerFirst120', '09990000120', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 20, NULL, 'rejected', 0, NULL, 'Invalid ID.', NULL, NULL, '2025-08-23 07:49:09', '2025-08-22 07:49:09'),
(1707, 121, 'OwnerLast121', NULL, 'OwnerFirst121', '09990000121', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 1, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2024-12-31 07:49:09', '2024-12-30 07:49:09'),
(1708, 122, 'OwnerLast122', NULL, 'OwnerFirst122', '09990000122', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 1, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-08-14 07:49:09', '2025-08-13 07:49:09'),
(1709, 123, 'OwnerLast123', NULL, 'OwnerFirst123', '09990000123', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 25, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-08 07:49:09', '2025-12-07 07:49:09'),
(1710, 124, 'OwnerLast124', NULL, 'OwnerFirst124', '09990000124', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 13, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-11-28 07:49:09', '2025-11-27 07:49:09'),
(1711, 125, 'OwnerLast125', NULL, 'OwnerFirst125', '09990000125', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 14, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-11-08 07:49:09', '2025-11-07 07:49:09'),
(1712, 126, 'OwnerLast126', NULL, 'OwnerFirst126', '09990000126', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 13, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-04-22 07:49:09'),
(1713, 127, 'OwnerLast127', NULL, 'OwnerFirst127', '09990000127', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 19, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-03-05 07:49:09', '2025-03-04 07:49:09'),
(1714, 128, 'OwnerLast128', NULL, 'OwnerFirst128', '09990000128', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 12, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-31 07:49:09', '2025-05-30 07:49:09'),
(1715, 129, 'OwnerLast129', NULL, 'OwnerFirst129', '09990000129', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 25, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-03-27 07:49:09', '2025-03-26 07:49:09'),
(1716, 130, 'OwnerLast130', NULL, 'OwnerFirst130', '09990000130', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 6, NULL, 'rejected', 1, NULL, 'Invalid ID.', NULL, NULL, '2025-06-13 07:49:09', '2025-06-12 07:49:09'),
(1717, 131, 'OwnerLast131', NULL, 'OwnerFirst131', '09990000131', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 23, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-04-30 07:49:09', '2025-04-29 07:49:09'),
(1718, 132, 'OwnerLast132', NULL, 'OwnerFirst132', '09990000132', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 1, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-04 07:49:09', '2025-12-03 07:49:09'),
(1719, 133, 'OwnerLast133', NULL, 'OwnerFirst133', '09990000133', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 10, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-09-23 07:49:09', '2025-09-22 07:49:09'),
(1720, 134, 'OwnerLast134', NULL, 'OwnerFirst134', '09990000134', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 4, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-06-21 07:49:09', '2025-06-20 07:49:09'),
(1721, 135, 'OwnerLast135', NULL, 'OwnerFirst135', '09990000135', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 20, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-01-10 07:49:09'),
(1722, 136, 'OwnerLast136', NULL, 'OwnerFirst136', '09990000136', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 25, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-09-30 07:49:09', '2025-09-29 07:49:09'),
(1723, 137, 'OwnerLast137', NULL, 'OwnerFirst137', '09990000137', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 1, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-04-25 07:49:09', '2025-04-24 07:49:09'),
(1724, 138, 'OwnerLast138', NULL, 'OwnerFirst138', '09990000138', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 13, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-08 07:49:09', '2025-05-07 07:49:09'),
(1725, 139, 'OwnerLast139', NULL, 'OwnerFirst139', '09990000139', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 20, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-02-11 07:49:09', '2025-02-10 07:49:09'),
(1726, 140, 'OwnerLast140', NULL, 'OwnerFirst140', '09990000140', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 12, NULL, 'rejected', 0, NULL, 'Invalid ID.', NULL, NULL, '2025-03-07 07:49:09', '2025-03-06 07:49:09'),
(1727, 141, 'OwnerLast141', NULL, 'OwnerFirst141', '09990000141', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 11, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-08-07 07:49:09', '2025-08-06 07:49:09'),
(1728, 142, 'OwnerLast142', NULL, 'OwnerFirst142', '09990000142', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 17, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-18 07:49:09', '2025-05-17 07:49:09'),
(1729, 143, 'OwnerLast143', NULL, 'OwnerFirst143', '09990000143', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 16, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-10-18 07:49:09', '2025-10-17 07:49:09'),
(1730, 144, 'OwnerLast144', NULL, 'OwnerFirst144', '09990000144', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 4, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-02-03 07:49:09'),
(1731, 145, 'OwnerLast145', NULL, 'OwnerFirst145', '09990000145', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 3, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-03-25 07:49:09', '2025-03-24 07:49:09'),
(1732, 146, 'OwnerLast146', NULL, 'OwnerFirst146', '09990000146', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 19, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-10-16 07:49:09', '2025-10-15 07:49:09'),
(1733, 147, 'OwnerLast147', NULL, 'OwnerFirst147', '09990000147', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 1, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-10-04 07:49:09', '2025-10-03 07:49:09'),
(1734, 148, 'OwnerLast148', NULL, 'OwnerFirst148', '09990000148', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 23, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-04-12 07:49:09', '2025-04-11 07:49:09'),
(1735, 149, 'OwnerLast149', NULL, 'OwnerFirst149', '09990000149', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 4, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-06-14 07:49:09', '2025-06-13 07:49:09'),
(1736, 150, 'OwnerLast150', NULL, 'OwnerFirst150', '09990000150', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 19, NULL, 'rejected', 1, NULL, 'Invalid ID.', NULL, NULL, '2025-09-27 07:49:09', '2025-09-26 07:49:09'),
(1737, 151, 'OwnerLast151', NULL, 'OwnerFirst151', '09990000151', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 20, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-10-26 07:49:09', '2025-10-25 07:49:09'),
(1738, 152, 'OwnerLast152', NULL, 'OwnerFirst152', '09990000152', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 8, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-03-13 07:49:09', '2025-03-12 07:49:09'),
(1739, 153, 'OwnerLast153', NULL, 'OwnerFirst153', '09990000153', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 11, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-12-14 07:49:09'),
(1740, 154, 'OwnerLast154', NULL, 'OwnerFirst154', '09990000154', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 22, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-10 07:49:09', '2025-12-09 07:49:09'),
(1741, 155, 'OwnerLast155', NULL, 'OwnerFirst155', '09990000155', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 15, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-06-12 07:49:09', '2025-06-11 07:49:09'),
(1742, 156, 'OwnerLast156', NULL, 'OwnerFirst156', '09990000156', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 14, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-09-03 07:49:09', '2025-09-02 07:49:09'),
(1743, 157, 'OwnerLast157', NULL, 'OwnerFirst157', '09990000157', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 4, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-06 07:49:09', '2025-12-05 07:49:09'),
(1744, 158, 'OwnerLast158', NULL, 'OwnerFirst158', '09990000158', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 6, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-07-12 07:49:09', '2025-07-11 07:49:09'),
(1745, 159, 'OwnerLast159', NULL, 'OwnerFirst159', '09990000159', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 13, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-11-24 07:49:09', '2025-11-23 07:49:09'),
(1746, 160, 'OwnerLast160', NULL, 'OwnerFirst160', '09990000160', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 8, NULL, 'rejected', 0, NULL, 'Invalid ID.', NULL, NULL, '2025-10-12 07:49:09', '2025-10-11 07:49:09'),
(1747, 161, 'OwnerLast161', NULL, 'OwnerFirst161', '09990000161', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 26, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-06-29 07:49:09', '2025-06-28 07:49:09'),
(1748, 162, 'OwnerLast162', NULL, 'OwnerFirst162', '09990000162', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 12, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-09-06 07:49:09'),
(1749, 163, 'OwnerLast163', NULL, 'OwnerFirst163', '09990000163', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 2, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-13 07:49:09', '2025-05-12 07:49:09'),
(1750, 164, 'OwnerLast164', NULL, 'OwnerFirst164', '09990000164', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 11, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-22 07:49:09', '2025-05-21 07:49:09'),
(1751, 165, 'OwnerLast165', NULL, 'OwnerFirst165', '09990000165', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 15, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-10-11 07:49:09', '2025-10-10 07:49:09'),
(1752, 166, 'OwnerLast166', NULL, 'OwnerFirst166', '09990000166', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 4, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-01-09 07:49:09', '2025-01-08 07:49:09'),
(1753, 167, 'OwnerLast167', NULL, 'OwnerFirst167', '09990000167', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 16, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-04-01 07:49:09', '2025-03-31 07:49:09'),
(1754, 168, 'OwnerLast168', NULL, 'OwnerFirst168', '09990000168', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 8, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-11-13 07:49:09', '2025-11-12 07:49:09'),
(1755, 169, 'OwnerLast169', NULL, 'OwnerFirst169', '09990000169', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 12, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-19 07:49:09', '2025-05-18 07:49:09'),
(1756, 170, 'OwnerLast170', NULL, 'OwnerFirst170', '09990000170', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 23, NULL, 'rejected', 1, NULL, 'Invalid ID.', NULL, NULL, '2025-07-22 07:49:09', '2025-07-21 07:49:09'),
(1757, 171, 'OwnerLast171', NULL, 'OwnerFirst171', '09990000171', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 5, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-05-17 07:49:09'),
(1758, 172, 'OwnerLast172', NULL, 'OwnerFirst172', '09990000172', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 25, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-08-02 07:49:09', '2025-08-01 07:49:09'),
(1759, 173, 'OwnerLast173', NULL, 'OwnerFirst173', '09990000173', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 13, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-09-04 07:49:09', '2025-09-03 07:49:09'),
(1760, 174, 'OwnerLast174', NULL, 'OwnerFirst174', '09990000174', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 22, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-04-13 07:49:09', '2025-04-12 07:49:09'),
(1761, 175, 'OwnerLast175', NULL, 'OwnerFirst175', '09990000175', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 23, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-02-13 07:49:09', '2025-02-12 07:49:09'),
(1762, 176, 'OwnerLast176', NULL, 'OwnerFirst176', '09990000176', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 5, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-10-12 07:49:09', '2025-10-11 07:49:09'),
(1763, 177, 'OwnerLast177', NULL, 'OwnerFirst177', '09990000177', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 7, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-09-08 07:49:09', '2025-09-07 07:49:09'),
(1764, 178, 'OwnerLast178', NULL, 'OwnerFirst178', '09990000178', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 19, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-02-04 07:49:09', '2025-02-03 07:49:09'),
(1765, 179, 'OwnerLast179', NULL, 'OwnerFirst179', '09990000179', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 20, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-06-27 07:49:09', '2025-06-26 07:49:09'),
(1766, 180, 'OwnerLast180', NULL, 'OwnerFirst180', '09990000180', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 22, NULL, 'rejected', 0, NULL, 'Invalid ID.', NULL, NULL, '0000-00-00 00:00:00', '2025-11-28 07:49:09'),
(1767, 181, 'OwnerLast181', NULL, 'OwnerFirst181', '09990000181', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 2, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-01-27 07:49:09', '2025-01-26 07:49:09'),
(1768, 182, 'OwnerLast182', NULL, 'OwnerFirst182', '09990000182', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 6, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-10 07:49:09', '2025-05-09 07:49:09'),
(1769, 183, 'OwnerLast183', NULL, 'OwnerFirst183', '09990000183', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 2, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-08 07:49:09', '2025-12-07 07:49:09'),
(1770, 184, 'OwnerLast184', NULL, 'OwnerFirst184', '09990000184', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 8, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-03-11 07:49:09', '2025-03-10 07:49:09'),
(1771, 185, 'OwnerLast185', NULL, 'OwnerFirst185', '09990000185', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 5, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-08-21 07:49:09', '2025-08-20 07:49:09'),
(1772, 186, 'OwnerLast186', NULL, 'OwnerFirst186', '09990000186', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 1, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2024-12-19 07:49:09', '2024-12-18 07:49:09'),
(1773, 187, 'OwnerLast187', NULL, 'OwnerFirst187', '09990000187', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 5, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-03-06 07:49:09', '2025-03-05 07:49:09'),
(1774, 188, 'OwnerLast188', NULL, 'OwnerFirst188', '09990000188', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 11, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-23 07:49:09', '2025-05-22 07:49:09'),
(1775, 189, 'OwnerLast189', NULL, 'OwnerFirst189', '09990000189', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 21, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-03-19 07:49:09'),
(1776, 190, 'OwnerLast190', NULL, 'OwnerFirst190', '09990000190', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 19, NULL, 'rejected', 1, NULL, 'Invalid ID.', NULL, NULL, '2025-08-27 07:49:09', '2025-08-26 07:49:09'),
(1777, 191, 'OwnerLast191', NULL, 'OwnerFirst191', '09990000191', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 23, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-19 07:49:09', '2025-05-18 07:49:09'),
(1778, 192, 'OwnerLast192', NULL, 'OwnerFirst192', '09990000192', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 15, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-11-06 07:49:09', '2025-11-05 07:49:09'),
(1779, 193, 'OwnerLast193', NULL, 'OwnerFirst193', '09990000193', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 5, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-01-24 07:49:09', '2025-01-23 07:49:09'),
(1780, 194, 'OwnerLast194', NULL, 'OwnerFirst194', '09990000194', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 3, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-06-17 07:49:09', '2025-06-16 07:49:09'),
(1781, 195, 'OwnerLast195', NULL, 'OwnerFirst195', '09990000195', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 2, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-29 07:49:09', '2025-05-28 07:49:09'),
(1782, 196, 'OwnerLast196', NULL, 'OwnerFirst196', '09990000196', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 3, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-09-21 07:49:09', '2025-09-20 07:49:09'),
(1783, 197, 'OwnerLast197', NULL, 'OwnerFirst197', '09990000197', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 26, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-07-20 07:49:09', '2025-07-19 07:49:09'),
(1784, 198, 'OwnerLast198', NULL, 'OwnerFirst198', '09990000198', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 7, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-12-03 07:49:09'),
(1785, 199, 'OwnerLast199', NULL, 'OwnerFirst199', '09990000199', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 12, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-01-21 07:49:09', '2025-01-20 07:49:09'),
(1786, 200, 'OwnerLast200', NULL, 'OwnerFirst200', '09990000200', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 18, NULL, 'rejected', 0, NULL, 'Invalid ID.', NULL, NULL, '2025-08-05 07:49:09', '2025-08-04 07:49:09'),
(1787, 201, 'OwnerLast201', NULL, 'OwnerFirst201', '09990000201', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 20, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-08-15 07:49:09', '2025-08-14 07:49:09'),
(1788, 202, 'OwnerLast202', NULL, 'OwnerFirst202', '09990000202', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 3, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-04-17 07:49:09', '2025-04-16 07:49:09'),
(1789, 203, 'OwnerLast203', NULL, 'OwnerFirst203', '09990000203', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 24, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-09-05 07:49:09', '2025-09-04 07:49:09'),
(1790, 204, 'OwnerLast204', NULL, 'OwnerFirst204', '09990000204', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 6, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-09-22 07:49:09', '2025-09-21 07:49:09'),
(1791, 205, 'OwnerLast205', NULL, 'OwnerFirst205', '09990000205', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 10, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-30 07:49:09', '2025-05-29 07:49:09'),
(1792, 206, 'OwnerLast206', NULL, 'OwnerFirst206', '09990000206', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 24, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-11-16 07:49:09', '2025-11-15 07:49:09'),
(1793, 207, 'OwnerLast207', NULL, 'OwnerFirst207', '09990000207', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 24, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-04-16 07:49:09'),
(1794, 208, 'OwnerLast208', NULL, 'OwnerFirst208', '09990000208', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 2, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-09 07:49:09', '2025-12-08 07:49:09'),
(1795, 209, 'OwnerLast209', NULL, 'OwnerFirst209', '09990000209', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 12, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-07-16 07:49:09', '2025-07-15 07:49:09'),
(1796, 210, 'OwnerLast210', NULL, 'OwnerFirst210', '09990000210', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 6, NULL, 'rejected', 1, NULL, 'Invalid ID.', NULL, NULL, '2025-06-19 07:49:09', '2025-06-18 07:49:09'),
(1797, 211, 'OwnerLast211', NULL, 'OwnerFirst211', '09990000211', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 15, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-10-13 07:49:09', '2025-10-12 07:49:09'),
(1798, 212, 'OwnerLast212', NULL, 'OwnerFirst212', '09990000212', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 15, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-02-17 07:49:09', '2025-02-16 07:49:09'),
(1799, 213, 'OwnerLast213', NULL, 'OwnerFirst213', '09990000213', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 26, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-18 07:49:09', '2025-05-17 07:49:09'),
(1800, 214, 'OwnerLast214', NULL, 'OwnerFirst214', '09990000214', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 4, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-06-19 07:49:09', '2025-06-18 07:49:09'),
(1801, 215, 'OwnerLast215', NULL, 'OwnerFirst215', '09990000215', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 17, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-08-13 07:49:09', '2025-08-12 07:49:09'),
(1802, 216, 'OwnerLast216', NULL, 'OwnerFirst216', '09990000216', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 25, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-04-11 07:49:09'),
(1803, 217, 'OwnerLast217', NULL, 'OwnerFirst217', '09990000217', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 3, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-01-15 07:49:09', '2025-01-14 07:49:09'),
(1804, 218, 'OwnerLast218', NULL, 'OwnerFirst218', '09990000218', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 12, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-06-06 07:49:09', '2025-06-05 07:49:09'),
(1805, 219, 'OwnerLast219', NULL, 'OwnerFirst219', '09990000219', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 3, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-03-10 07:49:09', '2025-03-09 07:49:09'),
(1806, 220, 'OwnerLast220', NULL, 'OwnerFirst220', '09990000220', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 17, NULL, 'rejected', 0, NULL, 'Invalid ID.', NULL, NULL, '2025-11-09 07:49:09', '2025-11-08 07:49:09'),
(1807, 351, 'Bongo', 'Duran', 'Chrysjann Theo', '09926314071', 'Sapphire Street, Baliwasan, City of Zamboanga, Zamboanga Del Sur 7000', 2, 'validID/front/tQ9mcgV1EIbDSijOfEFkbS00YfuRxHBIkPtORkDn.jpg', 'validID/back/TUqXZpXuuzBHWXneHWA9cjG7zCxzHFoAbqC9iFw2.jpg', 'policeClearance/laoVX4DumPxRgQ9hKsyFcjXzeq90pYpV5eGR8MW2.png', '2004-10-01', 21, NULL, 'Comshop', 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-16 03:04:28', '2025-12-15 18:13:01'),
(1808, 352, 'Padios', 'Ahat', 'Olive Faith', '09926354567', 'Unit No.3, Baliwasan, City of Zamboanga, Zamboanga Del Sur 7000', 4, 'validID/front/MvGX5e8pYjtVsFkylyaxHaueJ3HVwnIWvXRWkVm5.png', 'validID/back/5NkmiiyJDLqow0XzLRGQLYqFefcYAsQyaBs8gGJh.png', 'policeClearance/WW7HTg8kE6w1DvCN2adronrHrCmDHzKIYr470veX.png', '2000-02-09', 25, 4, NULL, 'deleted', 0, NULL, NULL, 'ainnoway', NULL, '2025-12-15 20:32:28', '2025-12-15 20:32:28'),
(1809, 353, 'Kulong', 'Gellecania', 'Rone Paullan', '09926354567', 'Bungiau, Baliwasan, City of Zamboanga, Zamboanga Del Sur 7000', 2, 'validID/front/WQFw3sDBljTwWxtdrWcwFsRISqJnM5sjGBKbCtAU.jpg', 'validID/back/mP8bq8qSm6avB2SubBvNv0jdW8DhgVECUFFlUv8l.jpg', 'policeClearance/zKsaydvtQFqb40O5FvYeNGbqdqkyXHrOOWAa4BE4.png', '2025-12-17', 0, NULL, 'Private Nurse', 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-16 08:15:37', '2025-12-16 00:10:43');

-- --------------------------------------------------------

--
-- Table structure for table `qr_codes`
--

CREATE TABLE `qr_codes` (
  `qr_id` int(11) NOT NULL,
  `qr_path` varchar(255) NOT NULL,
  `qr_name` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `reviewer_user_id` int(11) NOT NULL,
  `reviewee_user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `cover_photo` varchar(255) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `OTP_hash` varchar(255) NOT NULL,
  `user_type` enum('contractor','property_owner','both','staff') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `profile_pic`, `cover_photo`, `username`, `email`, `password_hash`, `OTP_hash`, `user_type`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, 'owner1', 'owner1@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-07-30 07:49:09', '2025-12-15 21:13:42'),
(2, NULL, NULL, 'owner2', 'owner2@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-10-21 07:49:09', '2025-12-15 21:13:42'),
(3, NULL, NULL, 'owner3', 'owner3@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2024-12-17 07:49:09', '2025-12-15 21:13:42'),
(4, NULL, NULL, 'owner4', 'owner4@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-01-24 07:49:09', '2025-12-15 21:13:42'),
(5, NULL, NULL, 'owner5', 'owner5@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-02-19 07:49:09', '2025-12-15 21:13:42'),
(6, NULL, NULL, 'owner6', 'owner6@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-04-21 07:49:09', '2025-12-15 21:13:42'),
(7, NULL, NULL, 'owner7', 'owner7@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-11-25 07:49:09', '2025-12-15 21:13:42'),
(8, NULL, NULL, 'owner8', 'owner8@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-10-08 07:49:09', '2025-12-15 21:13:42'),
(9, NULL, NULL, 'owner9', 'owner9@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-03-26 07:49:09', '2025-12-15 21:13:42'),
(10, NULL, NULL, 'owner10', 'owner10@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-09-23 07:49:09', '2025-12-15 21:13:42'),
(11, NULL, NULL, 'owner11', 'owner11@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-05-10 07:49:09', '2025-12-15 21:13:42'),
(12, NULL, NULL, 'owner12', 'owner12@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-09-09 07:49:09', '2025-12-15 21:13:42'),
(13, NULL, NULL, 'owner13', 'owner13@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-07-06 07:49:09', '2025-12-15 21:13:42'),
(14, NULL, NULL, 'owner14', 'owner14@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-02-20 07:49:09', '2025-12-15 21:13:42'),
(15, NULL, NULL, 'owner15', 'owner15@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-01-08 07:49:09', '2025-12-15 21:13:42'),
(16, NULL, NULL, 'owner16', 'owner16@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-05-15 07:49:09', '2025-12-15 21:13:42'),
(17, NULL, NULL, 'owner17', 'owner17@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-04-29 07:49:09', '2025-12-15 21:13:42'),
(18, NULL, NULL, 'owner18', 'owner18@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-01-04 07:49:09', '2025-12-15 21:13:42'),
(19, NULL, NULL, 'owner19', 'owner19@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-05-29 07:49:09', '2025-12-15 21:13:42'),
(20, NULL, NULL, 'owner20', 'owner20@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-10-18 07:49:09', '2025-12-15 21:13:42'),
(21, NULL, NULL, 'owner21', 'owner21@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-01-22 07:49:09', '2025-12-15 21:13:42'),
(22, NULL, NULL, 'owner22', 'owner22@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-10-16 07:49:09', '2025-12-15 21:13:42'),
(23, NULL, NULL, 'owner23', 'owner23@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-03-16 07:49:09', '2025-12-15 21:13:42'),
(24, NULL, NULL, 'owner24', 'owner24@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-08-25 07:49:09', '2025-12-15 21:13:42'),
(25, NULL, NULL, 'owner25', 'owner25@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-05-01 07:49:09', '2025-12-15 21:13:42'),
(26, NULL, NULL, 'owner26', 'owner26@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-08-14 07:49:09', '2025-12-15 21:13:42'),
(27, NULL, NULL, 'owner27', 'owner27@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-11-06 07:49:09', '2025-12-15 21:13:42'),
(28, NULL, NULL, 'owner28', 'owner28@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-05-01 07:49:09', '2025-12-15 21:13:42'),
(29, NULL, NULL, 'owner29', 'owner29@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-07-19 07:49:09', '2025-12-15 21:13:42'),
(30, NULL, NULL, 'owner30', 'owner30@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-05-24 07:49:09', '2025-12-15 21:13:42'),
(31, NULL, NULL, 'owner31', 'owner31@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-11-08 07:49:09', '2025-12-15 21:13:42'),
(32, NULL, NULL, 'owner32', 'owner32@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-09-29 07:49:09', '2025-12-15 21:13:42'),
(33, NULL, NULL, 'owner33', 'owner33@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-02-04 07:49:09', '2025-12-15 21:13:42'),
(34, NULL, NULL, 'owner34', 'owner34@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-08-18 07:49:09', '2025-12-15 21:13:42'),
(35, NULL, NULL, 'owner35', 'owner35@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-04-22 07:49:09', '2025-12-15 21:13:42'),
(36, NULL, NULL, 'owner36', 'owner36@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-09-22 07:49:09', '2025-12-15 21:13:42'),
(37, NULL, NULL, 'owner37', 'owner37@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-05-10 07:49:09', '2025-12-15 21:13:42'),
(38, NULL, NULL, 'owner38', 'owner38@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-02-08 07:49:09', '2025-12-15 21:13:42'),
(39, NULL, NULL, 'owner39', 'owner39@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-07-21 07:49:09', '2025-12-15 21:13:42'),
(40, NULL, NULL, 'owner40', 'owner40@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-02-04 07:49:09', '2025-12-15 21:13:42'),
(41, NULL, NULL, 'owner41', 'owner41@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-02-08 07:49:09', '2025-12-15 21:13:42'),
(42, NULL, NULL, 'owner42', 'owner42@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-06-02 07:49:09', '2025-12-15 21:13:42'),
(43, NULL, NULL, 'owner43', 'owner43@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-05-21 07:49:09', '2025-12-15 21:13:42'),
(44, NULL, NULL, 'owner44', 'owner44@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-06-28 07:49:09', '2025-12-15 21:13:42'),
(45, NULL, NULL, 'owner45', 'owner45@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-04-02 07:49:09', '2025-12-15 21:13:42'),
(46, NULL, NULL, 'owner46', 'owner46@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-08-06 07:49:09', '2025-12-15 21:13:42'),
(47, NULL, NULL, 'owner47', 'owner47@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-07-12 07:49:09', '2025-12-15 21:13:42'),
(48, NULL, NULL, 'owner48', 'owner48@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-06-15 07:49:09', '2025-12-15 21:13:42'),
(49, NULL, NULL, 'owner49', 'owner49@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-12-12 07:49:09', '2025-12-15 21:13:42'),
(50, NULL, NULL, 'owner50', 'owner50@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-08-24 07:49:09', '2025-12-15 21:13:42'),
(51, NULL, NULL, 'owner51', 'owner51@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-09-17 07:49:09', '2025-12-15 21:13:42'),
(52, NULL, NULL, 'owner52', 'owner52@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-08-27 07:49:09', '2025-12-15 21:13:42'),
(53, NULL, NULL, 'owner53', 'owner53@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-11-10 07:49:09', '2025-12-15 21:13:42'),
(54, NULL, NULL, 'owner54', 'owner54@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-01-13 07:49:09', '2025-12-15 21:13:42'),
(55, NULL, NULL, 'owner55', 'owner55@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-06-01 07:49:09', '2025-12-15 21:13:42'),
(56, NULL, NULL, 'owner56', 'owner56@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-03-14 07:49:09', '2025-12-15 21:13:42'),
(57, NULL, NULL, 'owner57', 'owner57@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-03-25 07:49:09', '2025-12-15 21:13:42'),
(58, NULL, NULL, 'owner58', 'owner58@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-01-23 07:49:09', '2025-12-15 21:13:42'),
(59, NULL, NULL, 'owner59', 'owner59@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-06-10 07:49:09', '2025-12-15 21:13:42'),
(60, NULL, NULL, 'owner60', 'owner60@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-10-15 07:49:09', '2025-12-15 21:13:42'),
(61, NULL, NULL, 'owner61', 'owner61@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2024-12-23 07:49:09', '2025-12-15 21:13:42'),
(62, NULL, NULL, 'owner62', 'owner62@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-01-16 07:49:09', '2025-12-15 21:13:42'),
(63, NULL, NULL, 'owner63', 'owner63@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-03-27 07:49:09', '2025-12-15 21:13:42'),
(64, NULL, NULL, 'owner64', 'owner64@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-08-19 07:49:09', '2025-12-15 21:13:42'),
(65, NULL, NULL, 'owner65', 'owner65@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-01-05 07:49:09', '2025-12-15 21:13:42'),
(66, NULL, NULL, 'owner66', 'owner66@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-10-19 07:49:09', '2025-12-15 21:13:42'),
(67, NULL, NULL, 'owner67', 'owner67@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2024-12-30 07:49:09', '2025-12-15 21:13:42'),
(68, NULL, NULL, 'owner68', 'owner68@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-06-24 07:49:09', '2025-12-15 21:13:42'),
(69, NULL, NULL, 'owner69', 'owner69@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-03-16 07:49:09', '2025-12-15 21:13:42'),
(70, NULL, NULL, 'owner70', 'owner70@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-07-27 07:49:09', '2025-12-15 21:13:42'),
(71, NULL, NULL, 'owner71', 'owner71@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-07-24 07:49:09', '2025-12-15 21:13:42'),
(72, NULL, NULL, 'owner72', 'owner72@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-04-26 07:49:09', '2025-12-15 21:13:42'),
(73, NULL, NULL, 'owner73', 'owner73@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-02-21 07:49:09', '2025-12-15 21:13:42'),
(74, NULL, NULL, 'owner74', 'owner74@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-01-29 07:49:09', '2025-12-15 21:13:42'),
(75, NULL, NULL, 'owner75', 'owner75@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-11-17 07:49:09', '2025-12-15 21:13:42'),
(76, NULL, NULL, 'owner76', 'owner76@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-10-05 07:49:09', '2025-12-15 21:13:42'),
(77, NULL, NULL, 'owner77', 'owner77@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-04-11 07:49:09', '2025-12-15 21:13:42'),
(78, NULL, NULL, 'owner78', 'owner78@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-12-05 07:49:09', '2025-12-15 21:13:42'),
(79, NULL, NULL, 'owner79', 'owner79@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-04-25 07:49:09', '2025-12-15 21:13:42'),
(80, NULL, NULL, 'owner80', 'owner80@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-02-08 07:49:09', '2025-12-15 21:13:42'),
(81, NULL, NULL, 'owner81', 'owner81@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-03-25 07:49:09', '2025-12-15 21:13:42'),
(82, NULL, NULL, 'owner82', 'owner82@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-01-05 07:49:09', '2025-12-15 21:13:42'),
(83, NULL, NULL, 'owner83', 'owner83@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2024-12-25 07:49:09', '2025-12-15 21:13:42'),
(84, NULL, NULL, 'owner84', 'owner84@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-03-13 07:49:09', '2025-12-15 21:13:42'),
(85, NULL, NULL, 'owner85', 'owner85@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2024-12-27 07:49:09', '2025-12-15 21:13:42'),
(86, NULL, NULL, 'owner86', 'owner86@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-08-26 07:49:09', '2025-12-15 21:13:42'),
(87, NULL, NULL, 'owner87', 'owner87@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-08-21 07:49:09', '2025-12-15 21:13:42'),
(88, NULL, NULL, 'owner88', 'owner88@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-09-22 07:49:09', '2025-12-15 21:13:42'),
(89, NULL, NULL, 'owner89', 'owner89@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-07-11 07:49:09', '2025-12-15 21:13:42'),
(90, NULL, NULL, 'owner90', 'owner90@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-04-07 07:49:09', '2025-12-15 21:13:42'),
(91, NULL, NULL, 'owner91', 'owner91@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-09-16 07:49:09', '2025-12-15 21:13:42'),
(92, NULL, NULL, 'owner92', 'owner92@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-10-09 07:49:09', '2025-12-15 21:13:42'),
(93, NULL, NULL, 'owner93', 'owner93@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-10-06 07:49:09', '2025-12-15 21:13:42'),
(94, NULL, NULL, 'owner94', 'owner94@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-05-24 07:49:09', '2025-12-15 21:13:42'),
(95, NULL, NULL, 'owner95', 'owner95@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-04-03 07:49:09', '2025-12-15 21:13:42'),
(96, NULL, NULL, 'owner96', 'owner96@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-11-30 07:49:09', '2025-12-15 21:13:42'),
(97, NULL, NULL, 'owner97', 'owner97@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-01-02 07:49:09', '2025-12-15 21:13:42'),
(98, NULL, NULL, 'owner98', 'owner98@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-10-23 07:49:09', '2025-12-15 21:13:42'),
(99, NULL, NULL, 'owner99', 'owner99@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-07-13 07:49:09', '2025-12-15 21:13:42'),
(100, NULL, NULL, 'owner100', 'owner100@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', '2025-07-02 07:49:09', '2025-12-15 21:13:42'),
(101, NULL, NULL, 'owner101', 'owner101@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-04-06 07:49:09', '2025-12-15 23:05:38'),
(102, NULL, NULL, 'owner102', 'owner102@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-12-06 07:49:09', '2025-12-15 21:13:42'),
(103, NULL, NULL, 'owner103', 'owner103@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-04-21 07:49:09', '2025-12-15 21:13:42'),
(104, NULL, NULL, 'owner104', 'owner104@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-03-05 07:49:09', '2025-12-15 21:13:42'),
(105, NULL, NULL, 'owner105', 'owner105@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-05-10 07:49:09', '2025-12-15 21:13:42'),
(106, NULL, NULL, 'owner106', 'owner106@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-05-07 07:49:09', '2025-12-15 21:13:42'),
(107, NULL, NULL, 'owner107', 'owner107@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-05-23 07:49:09', '2025-12-15 21:13:42'),
(108, NULL, NULL, 'owner108', 'owner108@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-03-29 07:49:09', '2025-12-15 21:13:42'),
(109, NULL, NULL, 'owner109', 'owner109@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-06-09 07:49:09', '2025-12-15 21:13:42'),
(110, NULL, NULL, 'owner110', 'owner110@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-07-09 07:49:09', '2025-12-15 21:13:42'),
(111, NULL, NULL, 'owner111', 'owner111@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2024-12-17 07:49:09', '2025-12-16 00:08:52'),
(112, NULL, NULL, 'owner112', 'owner112@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-08-19 07:49:09', '2025-12-15 21:13:42'),
(113, NULL, NULL, 'owner113', 'owner113@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-07-28 07:49:09', '2025-12-15 21:13:42'),
(114, NULL, NULL, 'owner114', 'owner114@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-07-26 07:49:09', '2025-12-15 21:13:42'),
(115, NULL, NULL, 'owner115', 'owner115@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-12-05 07:49:09', '2025-12-15 21:13:42'),
(116, NULL, NULL, 'owner116', 'owner116@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-04-08 07:49:09', '2025-12-15 21:13:42'),
(117, NULL, NULL, 'owner117', 'owner117@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-10-17 07:49:09', '2025-12-15 21:13:42'),
(118, NULL, NULL, 'owner118', 'owner118@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-04-14 07:49:09', '2025-12-15 21:13:42'),
(119, NULL, NULL, 'owner119', 'owner119@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-06-22 07:49:09', '2025-12-15 21:13:42'),
(120, NULL, NULL, 'owner120', 'owner120@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-08-22 07:49:09', '2025-12-15 21:13:42'),
(121, NULL, NULL, 'owner121', 'owner121@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2024-12-30 07:49:09', '2025-12-15 21:13:42'),
(122, NULL, NULL, 'owner122', 'owner122@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-08-13 07:49:09', '2025-12-15 21:13:42'),
(123, NULL, NULL, 'owner123', 'owner123@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-12-07 07:49:09', '2025-12-15 21:13:42'),
(124, NULL, NULL, 'owner124', 'owner124@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-11-27 07:49:09', '2025-12-15 21:13:42'),
(125, NULL, NULL, 'owner125', 'owner125@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-11-07 07:49:09', '2025-12-15 21:13:42'),
(126, NULL, NULL, 'owner126', 'owner126@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-04-22 07:49:09', '2025-12-15 21:13:42'),
(127, NULL, NULL, 'owner127', 'owner127@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-03-04 07:49:09', '2025-12-15 21:13:42'),
(128, NULL, NULL, 'owner128', 'owner128@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-05-30 07:49:09', '2025-12-15 21:13:42'),
(129, NULL, NULL, 'owner129', 'owner129@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-03-26 07:49:09', '2025-12-15 21:13:42'),
(130, NULL, NULL, 'owner130', 'owner130@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-06-12 07:49:09', '2025-12-15 21:13:42'),
(131, NULL, NULL, 'owner131', 'owner131@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-04-29 07:49:09', '2025-12-15 21:13:42'),
(132, NULL, NULL, 'owner132', 'owner132@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-12-03 07:49:09', '2025-12-15 21:13:42'),
(133, NULL, NULL, 'owner133', 'owner133@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-09-22 07:49:09', '2025-12-15 21:13:42'),
(134, NULL, NULL, 'owner134', 'owner134@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-06-20 07:49:09', '2025-12-15 21:13:42'),
(135, NULL, NULL, 'owner135', 'owner135@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-01-10 07:49:09', '2025-12-15 21:13:42'),
(136, NULL, NULL, 'owner136', 'owner136@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-09-29 07:49:09', '2025-12-15 21:13:42'),
(137, NULL, NULL, 'owner137', 'owner137@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-04-24 07:49:09', '2025-12-15 21:13:42'),
(138, NULL, NULL, 'owner138', 'owner138@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-05-07 07:49:09', '2025-12-15 21:13:42'),
(139, NULL, NULL, 'owner139', 'owner139@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-02-10 07:49:09', '2025-12-15 21:13:42'),
(140, NULL, NULL, 'owner140', 'owner140@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-03-06 07:49:09', '2025-12-15 21:13:42'),
(141, NULL, NULL, 'owner141', 'owner141@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-08-06 07:49:09', '2025-12-15 21:13:42'),
(142, NULL, NULL, 'owner142', 'owner142@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-05-17 07:49:09', '2025-12-15 21:13:42'),
(143, NULL, NULL, 'owner143', 'owner143@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-10-17 07:49:09', '2025-12-15 21:13:42'),
(144, NULL, NULL, 'owner144', 'owner144@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-02-03 07:49:09', '2025-12-15 21:13:42'),
(145, NULL, NULL, 'owner145', 'owner145@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-03-24 07:49:09', '2025-12-15 21:13:42'),
(146, NULL, NULL, 'owner146', 'owner146@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-10-15 07:49:09', '2025-12-15 21:13:42'),
(147, NULL, NULL, 'owner147', 'owner147@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-10-03 07:49:09', '2025-12-15 21:13:42'),
(148, NULL, NULL, 'owner148', 'owner148@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-04-11 07:49:09', '2025-12-15 21:13:42'),
(149, NULL, NULL, 'owner149', 'owner149@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-06-13 07:49:09', '2025-12-15 21:13:42'),
(150, NULL, NULL, 'owner150', 'owner150@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-09-26 07:49:09', '2025-12-15 21:13:42'),
(151, NULL, NULL, 'owner151', 'owner151@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-10-25 07:49:09', '2025-12-15 21:13:42'),
(152, NULL, NULL, 'owner152', 'owner152@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-03-12 07:49:09', '2025-12-15 21:13:42'),
(153, NULL, NULL, 'owner153', 'owner153@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-12-14 07:49:09', '2025-12-15 21:13:42'),
(154, NULL, NULL, 'owner154', 'owner154@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-12-09 07:49:09', '2025-12-15 21:13:42'),
(155, NULL, NULL, 'owner155', 'owner155@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-06-11 07:49:09', '2025-12-15 21:13:42'),
(156, NULL, NULL, 'owner156', 'owner156@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-09-02 07:49:09', '2025-12-15 21:13:42'),
(157, NULL, NULL, 'owner157', 'owner157@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-12-05 07:49:09', '2025-12-15 21:13:42'),
(158, NULL, NULL, 'owner158', 'owner158@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-07-11 07:49:09', '2025-12-15 21:13:42'),
(159, NULL, NULL, 'owner159', 'owner159@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-11-23 07:49:09', '2025-12-15 21:13:42'),
(160, NULL, NULL, 'owner160', 'owner160@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-10-11 07:49:09', '2025-12-15 21:13:42'),
(161, NULL, NULL, 'owner161', 'owner161@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-06-28 07:49:09', '2025-12-15 21:13:42'),
(162, NULL, NULL, 'owner162', 'owner162@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-09-06 07:49:09', '2025-12-15 21:13:42'),
(163, NULL, NULL, 'owner163', 'owner163@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-05-12 07:49:09', '2025-12-15 21:13:42'),
(164, NULL, NULL, 'owner164', 'owner164@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-05-21 07:49:09', '2025-12-15 21:13:42'),
(165, NULL, NULL, 'owner165', 'owner165@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-10-10 07:49:09', '2025-12-15 21:13:42'),
(166, NULL, NULL, 'owner166', 'owner166@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-01-08 07:49:09', '2025-12-15 21:13:42'),
(167, NULL, NULL, 'owner167', 'owner167@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-03-31 07:49:09', '2025-12-15 21:13:42'),
(168, NULL, NULL, 'owner168', 'owner168@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-11-12 07:49:09', '2025-12-15 21:13:42'),
(169, NULL, NULL, 'owner169', 'owner169@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-05-18 07:49:09', '2025-12-15 21:13:42'),
(170, NULL, NULL, 'owner170', 'owner170@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-07-21 07:49:09', '2025-12-15 21:13:42'),
(171, NULL, NULL, 'owner171', 'owner171@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-05-17 07:49:09', '2025-12-15 21:13:42'),
(172, NULL, NULL, 'owner172', 'owner172@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-08-01 07:49:09', '2025-12-15 21:13:42'),
(173, NULL, NULL, 'owner173', 'owner173@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-09-03 07:49:09', '2025-12-15 21:13:42'),
(174, NULL, NULL, 'owner174', 'owner174@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-04-12 07:49:09', '2025-12-15 21:13:42'),
(175, NULL, NULL, 'owner175', 'owner175@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-02-12 07:49:09', '2025-12-15 21:13:42'),
(176, NULL, NULL, 'owner176', 'owner176@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-10-11 07:49:09', '2025-12-15 21:13:42'),
(177, NULL, NULL, 'owner177', 'owner177@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-09-07 07:49:09', '2025-12-15 21:13:42'),
(178, NULL, NULL, 'owner178', 'owner178@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-02-03 07:49:09', '2025-12-15 21:13:42'),
(179, NULL, NULL, 'owner179', 'owner179@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-06-26 07:49:09', '2025-12-15 21:13:42'),
(180, NULL, NULL, 'owner180', 'owner180@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-11-28 07:49:09', '2025-12-15 21:13:42'),
(181, NULL, NULL, 'owner181', 'owner181@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-01-26 07:49:09', '2025-12-15 21:13:42'),
(182, NULL, NULL, 'owner182', 'owner182@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-05-09 07:49:09', '2025-12-15 21:13:42'),
(183, NULL, NULL, 'owner183', 'owner183@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-12-07 07:49:09', '2025-12-15 21:13:42'),
(184, NULL, NULL, 'owner184', 'owner184@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-03-10 07:49:09', '2025-12-15 21:13:42'),
(185, NULL, NULL, 'owner185', 'owner185@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-08-20 07:49:09', '2025-12-15 21:13:42'),
(186, NULL, NULL, 'owner186', 'owner186@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2024-12-18 07:49:09', '2025-12-15 21:13:42'),
(187, NULL, NULL, 'owner187', 'owner187@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-03-05 07:49:09', '2025-12-15 21:13:42'),
(188, NULL, NULL, 'owner188', 'owner188@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-05-22 07:49:09', '2025-12-15 21:13:42'),
(189, NULL, NULL, 'owner189', 'owner189@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-03-19 07:49:09', '2025-12-15 21:13:42'),
(190, NULL, NULL, 'owner190', 'owner190@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-08-26 07:49:09', '2025-12-15 21:13:42'),
(191, NULL, NULL, 'owner191', 'owner191@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-05-18 07:49:09', '2025-12-15 21:13:42'),
(192, NULL, NULL, 'owner192', 'owner192@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-11-05 07:49:09', '2025-12-15 21:13:42'),
(193, NULL, NULL, 'owner193', 'owner193@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-01-23 07:49:09', '2025-12-15 21:13:42'),
(194, NULL, NULL, 'owner194', 'owner194@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-06-16 07:49:09', '2025-12-15 21:13:42'),
(195, NULL, NULL, 'owner195', 'owner195@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-05-28 07:49:09', '2025-12-15 21:13:42'),
(196, NULL, NULL, 'owner196', 'owner196@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-09-20 07:49:09', '2025-12-15 21:13:42'),
(197, NULL, NULL, 'owner197', 'owner197@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-07-19 07:49:09', '2025-12-15 21:13:42'),
(198, NULL, NULL, 'owner198', 'owner198@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-12-03 07:49:09', '2025-12-15 21:13:42'),
(199, NULL, NULL, 'owner199', 'owner199@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-01-20 07:49:09', '2025-12-15 21:13:42'),
(200, NULL, NULL, 'owner200', 'owner200@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', '2025-08-04 07:49:09', '2025-12-15 21:13:42'),
(201, NULL, NULL, 'owner201', 'owner201@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', '2025-08-14 07:49:09', '2025-12-15 21:13:42'),
(202, NULL, NULL, 'owner202', 'owner202@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', '2025-04-16 07:49:09', '2025-12-15 21:13:42'),
(203, NULL, NULL, 'owner203', 'owner203@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', '2025-09-04 07:49:09', '2025-12-15 21:13:42'),
(204, NULL, NULL, 'owner204', 'owner204@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', '2025-09-21 07:49:09', '2025-12-15 21:13:42'),
(205, NULL, NULL, 'owner205', 'owner205@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', '2025-05-29 07:49:09', '2025-12-15 21:13:42'),
(206, NULL, NULL, 'owner206', 'owner206@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', '2025-11-15 07:49:09', '2025-12-15 21:13:42'),
(207, NULL, NULL, 'owner207', 'owner207@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', '2025-04-16 07:49:09', '2025-12-15 21:13:42'),
(208, NULL, NULL, 'owner208', 'owner208@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', '2025-12-08 07:49:09', '2025-12-15 21:13:42'),
(209, NULL, NULL, 'owner209', 'owner209@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', '2025-07-15 07:49:09', '2025-12-15 21:13:42'),
(210, NULL, NULL, 'owner210', 'owner210@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', '2025-06-18 07:49:09', '2025-12-15 21:13:42'),
(211, NULL, NULL, 'owner211', 'owner211@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', '2025-10-12 07:49:09', '2025-12-15 21:13:42'),
(212, NULL, NULL, 'owner212', 'owner212@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', '2025-02-16 07:49:09', '2025-12-15 21:13:42'),
(213, NULL, NULL, 'owner213', 'owner213@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', '2025-05-17 07:49:09', '2025-12-15 21:13:42'),
(214, NULL, NULL, 'owner214', 'owner214@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', '2025-06-18 07:49:09', '2025-12-15 21:13:42'),
(215, NULL, NULL, 'owner215', 'owner215@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', '2025-08-12 07:49:09', '2025-12-15 21:13:42'),
(216, NULL, NULL, 'owner216', 'owner216@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', '2025-04-11 07:49:09', '2025-12-15 21:13:42'),
(217, NULL, NULL, 'owner217', 'owner217@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', '2025-01-14 07:49:09', '2025-12-15 21:13:42'),
(218, NULL, NULL, 'owner218', 'owner218@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', '2025-06-05 07:49:09', '2025-12-15 21:13:42'),
(219, NULL, NULL, 'owner219', 'owner219@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', '2025-03-09 07:49:09', '2025-12-15 21:13:42'),
(220, NULL, NULL, 'owner220', 'owner220@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', '2025-11-08 07:49:09', '2025-12-15 21:13:42'),
(221, NULL, NULL, 'staff221', 'staff221@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(222, NULL, NULL, 'staff222', 'staff222@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(223, NULL, NULL, 'staff223', 'staff223@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(224, NULL, NULL, 'staff224', 'staff224@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(225, NULL, NULL, 'staff225', 'staff225@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(226, NULL, NULL, 'staff226', 'staff226@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(227, NULL, NULL, 'staff227', 'staff227@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(228, NULL, NULL, 'staff228', 'staff228@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(229, NULL, NULL, 'staff229', 'staff229@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(230, NULL, NULL, 'staff230', 'staff230@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(231, NULL, NULL, 'staff231', 'staff231@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(232, NULL, NULL, 'staff232', 'staff232@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(233, NULL, NULL, 'staff233', 'staff233@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(234, NULL, NULL, 'staff234', 'staff234@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(235, NULL, NULL, 'staff235', 'staff235@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(236, NULL, NULL, 'staff236', 'staff236@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(237, NULL, NULL, 'staff237', 'staff237@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(238, NULL, NULL, 'staff238', 'staff238@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(239, NULL, NULL, 'staff239', 'staff239@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(240, NULL, NULL, 'staff240', 'staff240@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(241, NULL, NULL, 'staff241', 'staff241@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(242, NULL, NULL, 'staff242', 'staff242@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(243, NULL, NULL, 'staff243', 'staff243@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(244, NULL, NULL, 'staff244', 'staff244@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(245, NULL, NULL, 'staff245', 'staff245@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(246, NULL, NULL, 'staff246', 'staff246@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(247, NULL, NULL, 'staff247', 'staff247@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(248, NULL, NULL, 'staff248', 'staff248@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(249, NULL, NULL, 'staff249', 'staff249@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(250, NULL, NULL, 'staff250', 'staff250@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(251, NULL, NULL, 'staff251', 'staff251@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(252, NULL, NULL, 'staff252', 'staff252@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(253, NULL, NULL, 'staff253', 'staff253@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(254, NULL, NULL, 'staff254', 'staff254@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(255, NULL, NULL, 'staff255', 'staff255@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(256, NULL, NULL, 'staff256', 'staff256@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(257, NULL, NULL, 'staff257', 'staff257@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(258, NULL, NULL, 'staff258', 'staff258@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(259, NULL, NULL, 'staff259', 'staff259@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(260, NULL, NULL, 'staff260', 'staff260@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(261, NULL, NULL, 'staff261', 'staff261@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(262, NULL, NULL, 'staff262', 'staff262@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(263, NULL, NULL, 'staff263', 'staff263@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(264, NULL, NULL, 'staff264', 'staff264@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(265, NULL, NULL, 'staff265', 'staff265@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42');
INSERT INTO `users` (`user_id`, `profile_pic`, `cover_photo`, `username`, `email`, `password_hash`, `OTP_hash`, `user_type`, `created_at`, `updated_at`) VALUES
(266, NULL, NULL, 'staff266', 'staff266@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(267, NULL, NULL, 'staff267', 'staff267@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(268, NULL, NULL, 'staff268', 'staff268@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(269, NULL, NULL, 'staff269', 'staff269@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(270, NULL, NULL, 'staff270', 'staff270@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(351, 'profiles/dkoe1C0vh4hIVqBZyaCStCPlkWz5xD5JgpXlMOGq.jpg', NULL, 'owner_9202', 'ashxeyn@gmail.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'admin_created', 'property_owner', '2025-12-15 18:13:01', '2025-12-15 22:58:57'),
(352, 'profiles/uzYGxzALdcUs45kPeM9T2vjyRhiOIhj1tDbfFDyo.jpg', NULL, 'owner_9302', 'HZ202300486@wmsu.edu.ph', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'admin_created', 'property_owner', '2025-12-15 20:32:28', '2025-12-15 21:13:42'),
(353, NULL, NULL, 'owner_4153', 'shanehart1001@gmail.com', '$2y$12$TcERXqXzMZfclAVNvDSL4eaom7Hj2TN00zoODHkbvfBh9VoGbHk4q', 'admin_created', 'property_owner', '2025-12-16 00:10:43', '2025-12-16 00:10:43');

-- --------------------------------------------------------

--
-- Table structure for table `valid_ids`
--

CREATE TABLE `valid_ids` (
  `id` int(11) NOT NULL,
  `valid_id_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `valid_ids`
--

INSERT INTO `valid_ids` (`id`, `valid_id_name`) VALUES
(1, 'Passport'),
(2, 'Drivers License'),
(3, 'UMID'),
(4, 'National ID'),
(5, 'PRC ID'),
(6, 'Postal ID');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `bids`
--
ALTER TABLE `bids`
  ADD PRIMARY KEY (`bid_id`),
  ADD UNIQUE KEY `unique_project_contractor` (`project_id`,`contractor_id`),
  ADD KEY `contractor_id` (`contractor_id`);

--
-- Indexes for table `bid_files`
--
ALTER TABLE `bid_files`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `bid_id` (`bid_id`);

--
-- Indexes for table `contractors`
--
ALTER TABLE `contractors`
  ADD PRIMARY KEY (`contractor_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `contractor_types`
--
ALTER TABLE `contractor_types`
  ADD PRIMARY KEY (`type_id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `contractor_users`
--
ALTER TABLE `contractor_users`
  ADD PRIMARY KEY (`contractor_user_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `contractor_id` (`contractor_id`);

--
-- Indexes for table `disputes`
--
ALTER TABLE `disputes`
  ADD PRIMARY KEY (`dispute_id`),
  ADD KEY `disputes_ibfk_1` (`project_id`),
  ADD KEY `disputes_ibfk_2` (`raised_by_user_id`),
  ADD KEY `disputes_ibfk_3` (`against_user_id`),
  ADD KEY `disputes_ibfk_4` (`milestone_id`),
  ADD KEY `milestone_item_item_milestone_item_id_foreign` (`milestone_item_id`);

--
-- Indexes for table `dispute_files`
--
ALTER TABLE `dispute_files`
  ADD PRIMARY KEY (`file_id`),
  ADD UNIQUE KEY `storage_path` (`storage_path`),
  ADD KEY `dispute_id` (`dispute_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `milestones`
--
ALTER TABLE `milestones`
  ADD PRIMARY KEY (`milestone_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `contractor_id` (`contractor_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `milestone_items`
--
ALTER TABLE `milestone_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `milestone_id` (`milestone_id`);

--
-- Indexes for table `milestone_payments`
--
ALTER TABLE `milestone_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `contractor_user_id` (`contractor_user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `occupations`
--
ALTER TABLE `occupations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_plans`
--
ALTER TABLE `payment_plans`
  ADD PRIMARY KEY (`plan_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `contractor_id` (`contractor_id`);

--
-- Indexes for table `platform_payments`
--
ALTER TABLE `platform_payments`
  ADD PRIMARY KEY (`platform_payment_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `contractor_id` (`contractor_id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `progress`
--
ALTER TABLE `progress`
  ADD PRIMARY KEY (`progress_id`),
  ADD KEY `fk_progress_milestone_item` (`milestone_item_id`);

--
-- Indexes for table `progress_files`
--
ALTER TABLE `progress_files`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `fk_progress_files_progress_id` (`progress_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `fk_projects_type_id` (`type_id`),
  ADD KEY `fk_projects_relationship_id` (`relationship_id`);

--
-- Indexes for table `project_files`
--
ALTER TABLE `project_files`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `project_relationships`
--
ALTER TABLE `project_relationships`
  ADD PRIMARY KEY (`rel_id`),
  ADD KEY `fk_projectrel_owner` (`owner_id`),
  ADD KEY `fk_projectrel_contractor` (`selected_contractor_id`);

--
-- Indexes for table `property_owners`
--
ALTER TABLE `property_owners`
  ADD PRIMARY KEY (`owner_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `valid_id_id` (`valid_id_id`),
  ADD KEY `occupation_id` (`occupation_id`);

--
-- Indexes for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD PRIMARY KEY (`qr_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `reviewer_user_id` (`reviewer_user_id`),
  ADD KEY `reviewee_user_id` (`reviewee_user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `valid_ids`
--
ALTER TABLE `valid_ids`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bids`
--
ALTER TABLE `bids`
  MODIFY `bid_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=253;

--
-- AUTO_INCREMENT for table `bid_files`
--
ALTER TABLE `bid_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contractors`
--
ALTER TABLE `contractors`
  MODIFY `contractor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1807;

--
-- AUTO_INCREMENT for table `contractor_types`
--
ALTER TABLE `contractor_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `contractor_users`
--
ALTER TABLE `contractor_users`
  MODIFY `contractor_user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2053;

--
-- AUTO_INCREMENT for table `disputes`
--
ALTER TABLE `disputes`
  MODIFY `dispute_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `dispute_files`
--
ALTER TABLE `dispute_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;

--
-- AUTO_INCREMENT for table `milestones`
--
ALTER TABLE `milestones`
  MODIFY `milestone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1556;

--
-- AUTO_INCREMENT for table `milestone_items`
--
ALTER TABLE `milestone_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2767;

--
-- AUTO_INCREMENT for table `milestone_payments`
--
ALTER TABLE `milestone_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=814;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3688;

--
-- AUTO_INCREMENT for table `occupations`
--
ALTER TABLE `occupations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `payment_plans`
--
ALTER TABLE `payment_plans`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=920;

--
-- AUTO_INCREMENT for table `platform_payments`
--
ALTER TABLE `platform_payments`
  MODIFY `platform_payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `progress`
--
ALTER TABLE `progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=814;

--
-- AUTO_INCREMENT for table `progress_files`
--
ALTER TABLE `progress_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1045;

--
-- AUTO_INCREMENT for table `project_files`
--
ALTER TABLE `project_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_relationships`
--
ALTER TABLE `project_relationships`
  MODIFY `rel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1045;

--
-- AUTO_INCREMENT for table `property_owners`
--
ALTER TABLE `property_owners`
  MODIFY `owner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1810;

--
-- AUTO_INCREMENT for table `qr_codes`
--
ALTER TABLE `qr_codes`
  MODIFY `qr_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=354;

--
-- AUTO_INCREMENT for table `valid_ids`
--
ALTER TABLE `valid_ids`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bids`
--
ALTER TABLE `bids`
  ADD CONSTRAINT `bids_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bids_ibfk_2` FOREIGN KEY (`contractor_id`) REFERENCES `contractors` (`contractor_id`) ON DELETE CASCADE;

--
-- Constraints for table `bid_files`
--
ALTER TABLE `bid_files`
  ADD CONSTRAINT `bid_files_ibfk_1` FOREIGN KEY (`bid_id`) REFERENCES `bids` (`bid_id`) ON DELETE CASCADE;

--
-- Constraints for table `contractors`
--
ALTER TABLE `contractors`
  ADD CONSTRAINT `contractors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contractors_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `contractor_types` (`type_id`) ON DELETE CASCADE;

--
-- Constraints for table `contractor_users`
--
ALTER TABLE `contractor_users`
  ADD CONSTRAINT `contractor_users_ibfk_1` FOREIGN KEY (`contractor_id`) REFERENCES `contractors` (`contractor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contractor_users_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `disputes`
--
ALTER TABLE `disputes`
  ADD CONSTRAINT `disputes_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `disputes_ibfk_2` FOREIGN KEY (`raised_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `disputes_ibfk_3` FOREIGN KEY (`against_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `disputes_ibfk_4` FOREIGN KEY (`milestone_id`) REFERENCES `milestones` (`milestone_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `milestone_item_item_milestone_item_id_foreign` FOREIGN KEY (`milestone_item_id`) REFERENCES `milestone_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `dispute_files`
--
ALTER TABLE `dispute_files`
  ADD CONSTRAINT `dispute_files_ibfk_1` FOREIGN KEY (`dispute_id`) REFERENCES `disputes` (`dispute_id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `milestones`
--
ALTER TABLE `milestones`
  ADD CONSTRAINT `milestones_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `milestones_ibfk_2` FOREIGN KEY (`contractor_id`) REFERENCES `contractors` (`contractor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `milestones_ibfk_3` FOREIGN KEY (`plan_id`) REFERENCES `payment_plans` (`plan_id`) ON DELETE CASCADE;

--
-- Constraints for table `milestone_items`
--
ALTER TABLE `milestone_items`
  ADD CONSTRAINT `milestone_items_ibfk_1` FOREIGN KEY (`milestone_id`) REFERENCES `milestones` (`milestone_id`) ON DELETE CASCADE;

--
-- Constraints for table `milestone_payments`
--
ALTER TABLE `milestone_payments`
  ADD CONSTRAINT `milestone_payments_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `milestone_items` (`item_id`),
  ADD CONSTRAINT `milestone_payments_ibfk_3` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`),
  ADD CONSTRAINT `milestone_payments_ibfk_4` FOREIGN KEY (`owner_id`) REFERENCES `property_owners` (`owner_id`),
  ADD CONSTRAINT `milestone_payments_ibfk_5` FOREIGN KEY (`contractor_user_id`) REFERENCES `contractor_users` (`contractor_user_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `payment_plans`
--
ALTER TABLE `payment_plans`
  ADD CONSTRAINT `payment_plans_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`),
  ADD CONSTRAINT `payment_plans_ibfk_2` FOREIGN KEY (`contractor_id`) REFERENCES `contractors` (`contractor_id`);

--
-- Constraints for table `platform_payments`
--
ALTER TABLE `platform_payments`
  ADD CONSTRAINT `platform_payments_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`),
  ADD CONSTRAINT `platform_payments_ibfk_2` FOREIGN KEY (`contractor_id`) REFERENCES `contractors` (`contractor_id`),
  ADD CONSTRAINT `platform_payments_ibfk_3` FOREIGN KEY (`owner_id`) REFERENCES `property_owners` (`owner_id`),
  ADD CONSTRAINT `platform_payments_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `admin_users` (`admin_id`);

--
-- Constraints for table `progress`
--
ALTER TABLE `progress`
  ADD CONSTRAINT `fk_progress_milestone_item` FOREIGN KEY (`milestone_item_id`) REFERENCES `milestone_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `progress_files`
--
ALTER TABLE `progress_files`
  ADD CONSTRAINT `fk_progress_files_progress_id` FOREIGN KEY (`progress_id`) REFERENCES `progress` (`progress_id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_projects_relationship_id` FOREIGN KEY (`relationship_id`) REFERENCES `project_relationships` (`rel_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_projects_type_id` FOREIGN KEY (`type_id`) REFERENCES `contractor_types` (`type_id`);

--
-- Constraints for table `project_files`
--
ALTER TABLE `project_files`
  ADD CONSTRAINT `project_files_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`);

--
-- Constraints for table `project_relationships`
--
ALTER TABLE `project_relationships`
  ADD CONSTRAINT `fk_projectrel_contractor` FOREIGN KEY (`selected_contractor_id`) REFERENCES `contractors` (`contractor_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_projectrel_owner` FOREIGN KEY (`owner_id`) REFERENCES `property_owners` (`owner_id`);

--
-- Constraints for table `property_owners`
--
ALTER TABLE `property_owners`
  ADD CONSTRAINT `property_owners_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `property_owners_ibfk_2` FOREIGN KEY (`valid_id_id`) REFERENCES `valid_ids` (`id`),
  ADD CONSTRAINT `property_owners_ibfk_3` FOREIGN KEY (`occupation_id`) REFERENCES `occupations` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`reviewer_user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`reviewee_user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
