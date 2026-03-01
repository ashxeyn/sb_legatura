-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 01, 2026 at 01:04 PM
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
-- Table structure for table `ai_prediction_logs`
--

CREATE TABLE `ai_prediction_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` int(11) NOT NULL,
  `prediction` varchar(20) NOT NULL,
  `delay_probability` decimal(5,4) NOT NULL,
  `weather_severity` int(11) NOT NULL DEFAULT 0,
  `ai_response_snapshot` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Dumping data for table `bids`
--

INSERT INTO `bids` (`bid_id`, `project_id`, `contractor_id`, `proposed_cost`, `estimated_timeline`, `contractor_notes`, `bid_status`, `reason`, `submitted_at`, `decision_date`) VALUES
(253, 1045, 1809, 50000000.00, 12, 'nice notes', 'accepted', NULL, '2025-12-17 14:32:40', '2026-01-27 07:30:13'),
(254, 1046, 1809, 78000000.00, 14, 'We are the best to do the job', 'rejected', NULL, '2025-12-18 10:26:49', '2026-01-28 08:06:20'),
(255, 1046, 1687, 77500000.00, 180, 'We specialize in large-scale residential projects with over 15 years of experience. Our team uses high-quality materials and modern construction techniques to ensure timely delivery.', 'rejected', 'ssssssssssssssssssssssssssssssssssssssssssssssssssss', '2025-12-18 18:32:27', '2026-01-28 08:11:26'),
(256, 1046, 1688, 79000000.00, 165, 'Premium construction services with emphasis on quality and durability. We offer comprehensive warranty and post-construction support. Our timeline is aggressive but achievable.', 'rejected', NULL, '2025-12-18 18:32:27', '2026-01-28 08:06:20'),
(257, 1046, 1689, 76800000.00, 195, 'Cost-effective solution without compromising quality. We have successfully completed similar projects in the area. Extended timeline allows for meticulous attention to detail.', 'rejected', NULL, '2025-12-18 18:32:27', '2026-01-28 08:06:20'),
(258, 1046, 1690, 78200000.00, 175, 'Balanced approach combining competitive pricing with reliable execution. Our company has excellent track record and customer satisfaction ratings. We prioritize communication and transparency.', 'rejected', NULL, '2025-12-18 18:32:27', '2026-01-28 08:06:20'),
(259, 1046, 1691, 79800000.00, 160, 'Fast-track construction with premium materials and experienced workforce. We guarantee on-time completion with penalty clauses. Highest standards of safety and quality control.', 'rejected', NULL, '2025-12-18 18:32:27', '2026-01-28 08:06:20'),
(260, 1047, 1809, 50000000.00, 12, 'Jaosjzoaoaa', 'rejected', NULL, '2025-12-18 19:20:16', '2026-02-22 07:19:27'),
(261, 1048, 1809, 19000000.00, 12, 'Hahah', 'accepted', NULL, '2025-12-18 21:08:52', '2025-12-18 21:09:18'),
(262, 1049, 1809, 55000000.00, 12, 'We are the best at the industry', 'accepted', NULL, '2025-12-18 23:53:51', '2025-12-18 23:56:14'),
(263, 1054, 1809, 30000000.00, 24, 'Just some notes for you', 'accepted', NULL, '2026-01-25 00:16:01', '2026-01-25 00:17:04'),
(293, 1053, 1809, 34444444.00, 23, 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'accepted', NULL, '2026-02-20 06:11:56', NULL),
(294, 1056, 1810, 50000000.00, 24, 'oudydiydidyyky', 'accepted', NULL, '2026-02-21 01:57:19', '2026-02-23 00:09:12'),
(295, 1047, 1810, 6898.00, 24, 'tuItskss', 'accepted', NULL, '2026-02-22 03:17:31', '2026-02-22 07:19:27'),
(298, 1057, 1810, 90000000.00, 36, 'Test 4 reporting for Duty', 'submitted', NULL, '2026-02-28 17:43:50', NULL),
(299, 1057, 1809, 100000000.00, 26, 'Test 2 Reporting for duty lami mas magaling', 'submitted', NULL, '2026-02-28 17:45:20', NULL);

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

--
-- Dumping data for table `bid_files`
--

INSERT INTO `bid_files` (`file_id`, `bid_id`, `file_name`, `file_path`, `description`, `uploaded_at`) VALUES
(9, 294, 'Screenshot_20260219-230933.jpg', 'bid_attachments/1771667839_6999817fd07f3_Screenshot_20260219-230933.jpg', NULL, '2026-02-21 01:57:19'),
(10, 295, 'IMG_20260221_165037_972.jpg', 'bid_attachments/1771756154_699ada7a4ac36_IMG_20260221_165037_972.jpg', NULL, '2026-02-22 02:29:15'),
(11, 295, 'EMBEDDED-SYSTEMS-DESIGN-Lect.pdf', 'bid_files/1771759051_699ae5cb322cb_EMBEDDED-SYSTEMS-DESIGN-Lect.pdf', NULL, '2026-02-22 03:17:31'),
(14, 298, 'IMG_20260223_010847_813.jpg', 'bid_attachments/1772358230_69a40a56baa0b_IMG_20260223_010847_813.jpg', NULL, '2026-02-28 17:43:50'),
(15, 299, 'EMBEDDED-SYSTEMS-DESIGN-Lect.pdf', 'bid_attachments/1772358320_69a40ab07a287_EMBEDDED-SYSTEMS-DESIGN-Lect.pdf', NULL, '2026-02-28 17:45:20');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contractors`
--

CREATE TABLE `contractors` (
  `contractor_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `company_banner` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
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
  `verification_status` enum('pending','approved','rejected','deleted') DEFAULT 'pending',
  `verification_date` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `suspension_until` date DEFAULT NULL,
  `suspension_reason` text DEFAULT NULL,
  `deletion_reason` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `completed_projects` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contractors`
--

INSERT INTO `contractors` (`contractor_id`, `user_id`, `company_logo`, `company_banner`, `bio`, `company_name`, `company_start_date`, `years_of_experience`, `type_id`, `contractor_type_other`, `services_offered`, `business_address`, `company_email`, `company_phone`, `company_website`, `company_social_media`, `company_description`, `picab_number`, `picab_category`, `picab_expiration_date`, `business_permit_number`, `business_permit_city`, `business_permit_expiration`, `tin_business_reg_number`, `dti_sec_registration_photo`, `verification_status`, `verification_date`, `is_active`, `suspension_until`, `suspension_reason`, `deletion_reason`, `rejection_reason`, `completed_projects`, `created_at`, `updated_at`) VALUES
(1687, 1, NULL, NULL, NULL, 'Main Construction Co 1', '2011-12-16', 14, 8, NULL, 'General Construction', 'Zamboanga City', 'company1@example.com', '09170000001', NULL, NULL, NULL, 'PCAB-23362', 'A', '2026-12-31', 'BP-1', 'Zamboanga', '2026-01-01', 'TIN-1', 'dti_cert.jpg', 'approved', '2025-08-01 07:49:09', 1, NULL, NULL, NULL, NULL, 5, '2025-07-30 07:49:09', '2025-12-16 08:41:07'),
(1688, 2, NULL, NULL, NULL, 'Main Construction Co 2', '2000-12-16', 25, 5, NULL, 'General Construction', 'Zamboanga City', 'company2@example.com', '09170000002', NULL, NULL, NULL, 'PCAB-22358', 'A', '2026-12-31', 'BP-2', 'Zamboanga', '2026-01-01', 'TIN-2', 'dti_cert.jpg', 'approved', '2025-10-23 07:49:09', 1, NULL, NULL, NULL, NULL, 43, '2025-10-21 07:49:09', '2025-12-16 08:41:07'),
(1689, 3, NULL, NULL, NULL, 'Main Construction Co 3', '2005-12-16', 20, 6, NULL, 'General Construction', 'Zamboanga City', 'company3@example.com', '09170000003', NULL, NULL, NULL, 'PCAB-11115', 'A', '2026-12-31', 'BP-3', 'Zamboanga', '2026-01-01', 'TIN-3', 'dti_cert.jpg', 'approved', '2024-12-19 07:49:09', 1, NULL, NULL, NULL, NULL, 28, '2024-12-17 07:49:09', '2025-12-16 08:41:07'),
(1690, 4, NULL, NULL, NULL, 'Main Construction Co 4', '1998-12-16', 27, 4, NULL, 'General Construction', 'Zamboanga City', 'company4@example.com', '09170000004', NULL, NULL, NULL, 'PCAB-96931', 'A', '2026-12-31', 'BP-4', 'Zamboanga', '2026-01-01', 'TIN-4', 'dti_cert.jpg', 'approved', '2025-01-26 07:49:09', 1, NULL, NULL, NULL, NULL, 23, '2025-01-24 07:49:09', '2025-12-16 08:41:07'),
(1691, 5, NULL, NULL, NULL, 'Main Construction Co 5', '2013-12-16', 12, 1, NULL, 'General Construction', 'Zamboanga City', 'company5@example.com', '09170000005', NULL, NULL, NULL, 'PCAB-75904', 'A', '2026-12-31', 'BP-5', 'Zamboanga', '2026-01-01', 'TIN-5', 'dti_cert.jpg', 'approved', NULL, 1, NULL, NULL, NULL, '', 4, '2025-02-19 07:49:09', '2025-12-16 08:41:07'),
(1692, 6, NULL, NULL, NULL, 'Main Construction Co 6', '2014-12-16', 11, 9, NULL, 'General Construction', 'Zamboanga City', 'company6@example.com', '09170000006', NULL, NULL, NULL, 'PCAB-79857', 'A', '2026-12-31', 'BP-6', 'Zamboanga', '2026-01-01', 'TIN-6', 'dti_cert.jpg', 'approved', '2025-04-23 07:49:09', 1, NULL, NULL, NULL, NULL, 24, '2025-04-21 07:49:09', '2025-12-16 08:41:07'),
(1693, 7, NULL, NULL, NULL, 'Main Construction Co 7', '2008-12-16', 17, 6, NULL, 'General Construction', 'Zamboanga City', 'company7@example.com', '09170000007', NULL, NULL, NULL, 'PCAB-39566', 'A', '2026-12-31', 'BP-7', 'Zamboanga', '2026-01-01', 'TIN-7', 'dti_cert.jpg', 'approved', '2025-11-27 07:49:09', 1, NULL, NULL, NULL, NULL, 38, '2025-11-25 07:49:09', '2025-12-16 08:41:07'),
(1694, 8, NULL, NULL, NULL, 'Main Construction Co 8', '2003-12-16', 22, 7, NULL, 'General Construction', 'Zamboanga City', 'company8@example.com', '09170000008', NULL, NULL, NULL, 'PCAB-54580', 'A', '2026-12-31', 'BP-8', 'Zamboanga', '2026-01-01', 'TIN-8', 'dti_cert.jpg', 'approved', '2025-10-10 07:49:09', 1, NULL, NULL, NULL, NULL, 19, '2025-10-08 07:49:09', '2025-12-16 08:41:07'),
(1695, 9, NULL, NULL, NULL, 'Main Construction Co 9', '1999-12-16', 26, 9, NULL, 'General Construction', 'Zamboanga City', 'company9@example.com', '09170000009', NULL, NULL, NULL, 'PCAB-34347', 'A', '2026-12-31', 'BP-9', 'Zamboanga', '2026-01-01', 'TIN-9', 'dti_cert.jpg', 'approved', '2025-03-28 07:49:09', 1, NULL, NULL, NULL, NULL, 47, '2025-03-26 07:49:09', '2025-12-16 08:41:07'),
(1696, 10, NULL, NULL, NULL, 'Main Construction Co 10', '2018-12-16', 7, 6, NULL, 'General Construction', 'Zamboanga City', 'company10@example.com', '09170000010', NULL, NULL, NULL, 'PCAB-69751', 'A', '2026-12-31', 'BP-10', 'Zamboanga', '2026-01-01', 'TIN-10', 'dti_cert.jpg', 'rejected', NULL, 1, NULL, NULL, NULL, 'Permit expired.', 37, '2025-09-23 07:49:09', '2025-12-16 08:41:07'),
(1697, 11, NULL, NULL, NULL, 'Main Construction Co 11', '2010-12-16', 15, 9, NULL, 'General Construction', 'Zamboanga City', 'company11@example.com', '09170000011', NULL, NULL, NULL, 'PCAB-70939', 'A', '2026-12-31', 'BP-11', 'Zamboanga', '2026-01-01', 'TIN-11', 'dti_cert.jpg', 'approved', '2025-05-12 07:49:09', 1, NULL, NULL, NULL, NULL, 20, '2025-05-10 07:49:09', '2025-12-16 08:41:07'),
(1698, 12, NULL, NULL, NULL, 'Main Construction Co 12', '2003-12-16', 22, 1, NULL, 'General Construction', 'Zamboanga City', 'company12@example.com', '09170000012', NULL, NULL, NULL, 'PCAB-10680', 'A', '2026-12-31', 'BP-12', 'Zamboanga', '2026-01-01', 'TIN-12', 'dti_cert.jpg', 'approved', '2025-09-11 07:49:09', 1, NULL, NULL, NULL, NULL, 38, '2025-09-09 07:49:09', '2025-12-16 08:41:07'),
(1699, 13, NULL, NULL, NULL, 'Main Construction Co 13', '2021-12-16', 4, 1, NULL, 'General Construction', 'Zamboanga City', 'company13@example.com', '09170000013', NULL, NULL, NULL, 'PCAB-10739', 'A', '2026-12-31', 'BP-13', 'Zamboanga', '2026-01-01', 'TIN-13', 'dti_cert.jpg', 'approved', '2025-07-08 07:49:09', 1, NULL, NULL, NULL, NULL, 18, '2025-07-06 07:49:09', '2025-12-16 08:41:07'),
(1700, 14, NULL, NULL, NULL, 'Main Construction Co 14', '2012-12-16', 13, 4, NULL, 'General Construction', 'Zamboanga City', 'company14@example.com', '09170000014', NULL, NULL, NULL, 'PCAB-81118', 'A', '2026-12-31', 'BP-14', 'Zamboanga', '2026-01-01', 'TIN-14', 'dti_cert.jpg', 'approved', '2025-02-22 07:49:09', 1, NULL, NULL, NULL, NULL, 11, '2025-02-20 07:49:09', '2025-12-16 08:41:07'),
(1701, 15, NULL, NULL, NULL, 'Main Construction Co 15', '2003-12-16', 22, 5, NULL, 'General Construction', 'Zamboanga City', 'company15@example.com', '09170000015', NULL, NULL, NULL, 'PCAB-66597', 'A', '2026-12-31', 'BP-15', 'Zamboanga', '2026-01-01', 'TIN-15', 'dti_cert.jpg', 'approved', NULL, 1, NULL, NULL, NULL, '', 12, '2025-01-08 07:49:09', '2025-12-17 14:57:33'),
(1702, 16, NULL, NULL, NULL, 'Main Construction Co 16', '2012-12-16', 13, 2, NULL, 'General Construction', 'Zamboanga City', 'company16@example.com', '09170000016', NULL, NULL, NULL, 'PCAB-19082', 'A', '2026-12-31', 'BP-16', 'Zamboanga', '2026-01-01', 'TIN-16', 'dti_cert.jpg', 'approved', '2025-05-17 07:49:09', 1, NULL, NULL, NULL, NULL, 25, '2025-05-15 07:49:09', '2025-12-16 08:41:07'),
(1703, 17, NULL, NULL, NULL, 'Main Construction Co 17', '2000-12-16', 25, 5, NULL, 'General Construction', 'Zamboanga City', 'company17@example.com', '09170000017', NULL, NULL, NULL, 'PCAB-77703', 'A', '2026-12-31', 'BP-17', 'Zamboanga', '2026-01-01', 'TIN-17', 'dti_cert.jpg', 'approved', '2025-05-01 07:49:09', 1, NULL, NULL, NULL, NULL, 12, '2025-04-29 07:49:09', '2025-12-16 08:41:07'),
(1704, 18, NULL, NULL, NULL, 'Main Construction Co 18', '1996-12-16', 29, 2, NULL, 'General Construction', 'Zamboanga City', 'company18@example.com', '09170000018', NULL, NULL, NULL, 'PCAB-36965', 'A', '2026-12-31', 'BP-18', 'Zamboanga', '2026-01-01', 'TIN-18', 'dti_cert.jpg', 'approved', '2025-01-06 07:49:09', 1, NULL, NULL, NULL, NULL, 9, '2025-01-04 07:49:09', '2025-12-16 08:41:07'),
(1705, 19, NULL, NULL, NULL, 'Main Construction Co 19', '2016-12-16', 9, 2, NULL, 'General Construction', 'Zamboanga City', 'company19@example.com', '09170000019', NULL, NULL, NULL, 'PCAB-34306', 'A', '2026-12-31', 'BP-19', 'Zamboanga', '2026-01-01', 'TIN-19', 'dti_cert.jpg', 'approved', '2025-05-31 07:49:09', 1, NULL, NULL, NULL, NULL, 28, '2025-05-29 07:49:09', '2025-12-16 08:41:07'),
(1706, 20, NULL, NULL, NULL, 'Main Construction Co 20', '2006-12-16', 19, 3, NULL, 'General Construction', 'Zamboanga City', 'company20@example.com', '09170000020', NULL, NULL, NULL, 'PCAB-67093', 'A', '2026-12-31', 'BP-20', 'Zamboanga', '2026-01-01', 'TIN-20', 'dti_cert.jpg', 'rejected', NULL, 1, NULL, NULL, NULL, 'Permit expired.', 6, '2025-10-18 07:49:09', '2025-12-16 08:41:07'),
(1707, 21, NULL, NULL, NULL, 'Main Construction Co 21', '2019-12-16', 6, 2, NULL, 'General Construction', 'Zamboanga City', 'company21@example.com', '09170000021', NULL, NULL, NULL, 'PCAB-39542', 'A', '2026-12-31', 'BP-21', 'Zamboanga', '2026-01-01', 'TIN-21', 'dti_cert.jpg', 'approved', '2025-01-24 07:49:09', 1, NULL, NULL, NULL, NULL, 10, '2025-01-22 07:49:09', '2025-12-16 08:41:07'),
(1708, 22, NULL, NULL, NULL, 'Main Construction Co 22', '2024-12-16', 1, 3, NULL, 'General Construction', 'Zamboanga City', 'company22@example.com', '09170000022', NULL, NULL, NULL, 'PCAB-34168', 'A', '2026-12-31', 'BP-22', 'Zamboanga', '2026-01-01', 'TIN-22', 'dti_cert.jpg', 'approved', '2025-10-18 07:49:09', 1, NULL, NULL, NULL, NULL, 11, '2025-10-16 07:49:09', '2025-12-16 08:41:07'),
(1709, 23, NULL, NULL, NULL, 'Main Construction Co 23', '2008-12-16', 17, 6, NULL, 'General Construction', 'Zamboanga City', 'company23@example.com', '09170000023', NULL, NULL, NULL, 'PCAB-42469', 'A', '2026-12-31', 'BP-23', 'Zamboanga', '2026-01-01', 'TIN-23', 'dti_cert.jpg', 'approved', '2025-03-18 07:49:09', 1, NULL, NULL, NULL, NULL, 15, '2025-03-16 07:49:09', '2025-12-16 08:41:07'),
(1710, 24, NULL, NULL, NULL, 'Main Construction Co 24', '2002-12-16', 23, 5, NULL, 'General Construction', 'Zamboanga City', 'company24@example.com', '09170000024', NULL, NULL, NULL, 'PCAB-46764', 'A', '2026-12-31', 'BP-24', 'Zamboanga', '2026-01-01', 'TIN-24', 'dti_cert.jpg', 'approved', '2025-08-27 07:49:09', 1, NULL, NULL, NULL, NULL, 32, '2025-08-25 07:49:09', '2025-12-16 08:41:07'),
(1711, 25, NULL, NULL, NULL, 'Main Construction Co 25', '2021-12-16', 4, 1, NULL, 'General Construction', 'Zamboanga City', 'company25@example.com', '09170000025', NULL, NULL, NULL, 'PCAB-57726', 'A', '2026-12-31', 'BP-25', 'Zamboanga', '2026-01-01', 'TIN-25', 'dti_cert.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 48, '2025-05-01 07:49:09', '2025-12-16 08:41:07'),
(1712, 26, NULL, NULL, NULL, 'Main Construction Co 26', '2017-12-16', 8, 2, NULL, 'General Construction', 'Zamboanga City', 'company26@example.com', '09170000026', NULL, NULL, NULL, 'PCAB-45837', 'A', '2026-12-31', 'BP-26', 'Zamboanga', '2026-01-01', 'TIN-26', 'dti_cert.jpg', 'approved', '2025-08-16 07:49:09', 1, NULL, NULL, NULL, NULL, 6, '2025-08-14 07:49:09', '2025-12-16 08:41:07'),
(1713, 27, NULL, NULL, NULL, 'Main Construction Co 27', '1998-12-16', 27, 5, NULL, 'General Construction', 'Zamboanga City', 'company27@example.com', '09170000027', NULL, NULL, NULL, 'PCAB-36243', 'A', '2026-12-31', 'BP-27', 'Zamboanga', '2026-01-01', 'TIN-27', 'dti_cert.jpg', 'approved', '2025-11-08 07:49:09', 1, NULL, NULL, NULL, NULL, 21, '2025-11-06 07:49:09', '2025-12-16 08:41:07'),
(1714, 28, NULL, NULL, NULL, 'Main Construction Co 28', '2003-12-16', 22, 8, NULL, 'General Construction', 'Zamboanga City', 'company28@example.com', '09170000028', NULL, NULL, NULL, 'PCAB-96216', 'A', '2026-12-31', 'BP-28', 'Zamboanga', '2026-01-01', 'TIN-28', 'dti_cert.jpg', 'approved', '2025-05-03 07:49:09', 1, NULL, NULL, NULL, NULL, 21, '2025-05-01 07:49:09', '2025-12-16 08:41:07'),
(1715, 29, NULL, NULL, NULL, 'Main Construction Co 29', '1997-12-16', 28, 8, NULL, 'General Construction', 'Zamboanga City', 'company29@example.com', '09170000029', NULL, NULL, NULL, 'PCAB-84101', 'A', '2026-12-31', 'BP-29', 'Zamboanga', '2026-01-01', 'TIN-29', 'dti_cert.jpg', 'approved', '2025-07-21 07:49:09', 1, NULL, NULL, NULL, NULL, 19, '2025-07-19 07:49:09', '2025-12-16 08:41:07'),
(1716, 30, NULL, NULL, NULL, 'Main Construction Co 30', '2010-12-16', 15, 6, NULL, 'General Construction', 'Zamboanga City', 'company30@example.com', '09170000030', NULL, NULL, NULL, 'PCAB-57206', 'A', '2026-12-31', 'BP-30', 'Zamboanga', '2026-01-01', 'TIN-30', 'dti_cert.jpg', 'rejected', NULL, 1, NULL, NULL, NULL, 'Permit expired.', 20, '2025-05-24 07:49:09', '2025-12-16 08:41:07'),
(1717, 31, NULL, NULL, NULL, 'Main Construction Co 31', '2006-12-16', 19, 2, NULL, 'General Construction', 'Zamboanga City', 'company31@example.com', '09170000031', NULL, NULL, NULL, 'PCAB-68035', 'A', '2026-12-31', 'BP-31', 'Zamboanga', '2026-01-01', 'TIN-31', 'dti_cert.jpg', 'approved', '2025-11-10 07:49:09', 1, NULL, NULL, NULL, NULL, 42, '2025-11-08 07:49:09', '2025-12-16 08:41:07'),
(1718, 32, NULL, NULL, NULL, 'Main Construction Co 32', '2006-12-16', 19, 4, NULL, 'General Construction', 'Zamboanga City', 'company32@example.com', '09170000032', NULL, NULL, NULL, 'PCAB-72477', 'A', '2026-12-31', 'BP-32', 'Zamboanga', '2026-01-01', 'TIN-32', 'dti_cert.jpg', 'approved', '2025-10-01 07:49:09', 1, NULL, NULL, NULL, NULL, 21, '2025-09-29 07:49:09', '2025-12-16 08:41:07'),
(1719, 33, NULL, NULL, NULL, 'Main Construction Co 33', '2017-12-16', 8, 6, NULL, 'General Construction', 'Zamboanga City', 'company33@example.com', '09170000033', NULL, NULL, NULL, 'PCAB-44442', 'A', '2026-12-31', 'BP-33', 'Zamboanga', '2026-01-01', 'TIN-33', 'dti_cert.jpg', 'approved', '2025-02-06 07:49:09', 1, NULL, NULL, NULL, NULL, 1, '2025-02-04 07:49:09', '2025-12-16 08:41:07'),
(1720, 34, NULL, NULL, NULL, 'Main Construction Co 34', '2012-12-16', 13, 6, NULL, 'General Construction', 'Zamboanga City', 'company34@example.com', '09170000034', NULL, NULL, NULL, 'PCAB-88849', 'A', '2026-12-31', 'BP-34', 'Zamboanga', '2026-01-01', 'TIN-34', 'dti_cert.jpg', 'approved', '2025-08-20 07:49:09', 1, NULL, NULL, NULL, NULL, 8, '2025-08-18 07:49:09', '2025-12-16 08:41:07'),
(1721, 35, NULL, NULL, NULL, 'Main Construction Co 35', '2015-12-16', 10, 1, NULL, 'General Construction', 'Zamboanga City', 'company35@example.com', '09170000035', NULL, NULL, NULL, 'PCAB-23798', 'A', '2026-12-31', 'BP-35', 'Zamboanga', '2026-01-01', 'TIN-35', 'dti_cert.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 42, '2025-04-22 07:49:09', '2025-12-16 08:41:07'),
(1722, 36, NULL, NULL, NULL, 'Main Construction Co 36', '2012-12-16', 13, 8, NULL, 'General Construction', 'Zamboanga City', 'company36@example.com', '09170000036', NULL, NULL, NULL, 'PCAB-33648', 'A', '2026-12-31', 'BP-36', 'Zamboanga', '2026-01-01', 'TIN-36', 'dti_cert.jpg', 'approved', '2025-09-24 07:49:09', 1, NULL, NULL, NULL, NULL, 44, '2025-09-22 07:49:09', '2025-12-16 08:41:07'),
(1723, 37, NULL, NULL, NULL, 'Main Construction Co 37', '2024-12-16', 1, 2, NULL, 'General Construction', 'Zamboanga City', 'company37@example.com', '09170000037', NULL, NULL, NULL, 'PCAB-32151', 'A', '2026-12-31', 'BP-37', 'Zamboanga', '2026-01-01', 'TIN-37', 'dti_cert.jpg', 'approved', '2025-05-12 07:49:09', 1, NULL, NULL, NULL, NULL, 22, '2025-05-10 07:49:09', '2025-12-16 08:41:07'),
(1724, 38, NULL, NULL, NULL, 'Main Construction Co 38', '1997-12-16', 28, 7, NULL, 'General Construction', 'Zamboanga City', 'company38@example.com', '09170000038', NULL, NULL, NULL, 'PCAB-53613', 'A', '2026-12-31', 'BP-38', 'Zamboanga', '2026-01-01', 'TIN-38', 'dti_cert.jpg', 'approved', '2025-02-10 07:49:09', 1, NULL, NULL, NULL, NULL, 27, '2025-02-08 07:49:09', '2025-12-16 08:41:07'),
(1725, 39, NULL, NULL, NULL, 'Main Construction Co 39', '2009-12-16', 16, 8, NULL, 'General Construction', 'Zamboanga City', 'company39@example.com', '09170000039', NULL, NULL, NULL, 'PCAB-85450', 'A', '2026-12-31', 'BP-39', 'Zamboanga', '2026-01-01', 'TIN-39', 'dti_cert.jpg', 'approved', '2025-07-23 07:49:09', 1, NULL, NULL, NULL, NULL, 42, '2025-07-21 07:49:09', '2025-12-16 08:41:07'),
(1726, 40, NULL, NULL, NULL, 'Main Construction Co 40', '2001-12-16', 24, 6, NULL, 'General Construction', 'Zamboanga City', 'company40@example.com', '09170000040', NULL, NULL, NULL, 'PCAB-48462', 'A', '2026-12-31', 'BP-40', 'Zamboanga', '2026-01-01', 'TIN-40', 'dti_cert.jpg', 'rejected', NULL, 1, NULL, NULL, NULL, 'Permit expired.', 11, '2025-02-04 07:49:09', '2025-12-16 08:41:07'),
(1727, 41, NULL, NULL, NULL, 'Main Construction Co 41', '2011-12-16', 14, 9, NULL, 'General Construction', 'Zamboanga City', 'company41@example.com', '09170000041', NULL, NULL, NULL, 'PCAB-67854', 'A', '2026-12-31', 'BP-41', 'Zamboanga', '2026-01-01', 'TIN-41', 'dti_cert.jpg', 'approved', '2025-02-10 07:49:09', 1, NULL, NULL, NULL, NULL, 25, '2025-02-08 07:49:09', '2025-12-16 08:41:07'),
(1728, 42, NULL, NULL, NULL, 'Main Construction Co 42', '1999-12-16', 26, 9, NULL, 'General Construction', 'Zamboanga City', 'company42@example.com', '09170000042', NULL, NULL, NULL, 'PCAB-91907', 'A', '2026-12-31', 'BP-42', 'Zamboanga', '2026-01-01', 'TIN-42', 'dti_cert.jpg', 'approved', '2025-06-04 07:49:09', 1, NULL, NULL, NULL, NULL, 19, '2025-06-02 07:49:09', '2025-12-16 08:41:07'),
(1729, 43, NULL, NULL, NULL, 'Main Construction Co 43', '1999-12-16', 26, 3, NULL, 'General Construction', 'Zamboanga City', 'company43@example.com', '09170000043', NULL, NULL, NULL, 'PCAB-33148', 'A', '2026-12-31', 'BP-43', 'Zamboanga', '2026-01-01', 'TIN-43', 'dti_cert.jpg', 'approved', '2025-05-23 07:49:09', 1, NULL, NULL, NULL, NULL, 31, '2025-05-21 07:49:09', '2025-12-16 08:41:07'),
(1730, 44, NULL, NULL, NULL, 'Main Construction Co 44', '2002-12-16', 23, 6, NULL, 'General Construction', 'Zamboanga City', 'company44@example.com', '09170000044', NULL, NULL, NULL, 'PCAB-27263', 'A', '2026-12-31', 'BP-44', 'Zamboanga', '2026-01-01', 'TIN-44', 'dti_cert.jpg', 'approved', '2025-06-30 07:49:09', 1, NULL, NULL, NULL, NULL, 7, '2025-06-28 07:49:09', '2025-12-16 08:41:07'),
(1731, 45, NULL, NULL, NULL, 'Main Construction Co 45', '2018-12-16', 7, 9, NULL, 'General Construction', 'Zamboanga City', 'company45@example.com', '09170000045', NULL, NULL, NULL, 'PCAB-35372', 'A', '2026-12-31', 'BP-45', 'Zamboanga', '2026-01-01', 'TIN-45', 'dti_cert.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 12, '2025-04-02 07:49:09', '2025-12-16 08:41:07'),
(1732, 46, NULL, NULL, NULL, 'Main Construction Co 46', '1999-12-16', 26, 6, NULL, 'General Construction', 'Zamboanga City', 'company46@example.com', '09170000046', NULL, NULL, NULL, 'PCAB-88247', 'A', '2026-12-31', 'BP-46', 'Zamboanga', '2026-01-01', 'TIN-46', 'dti_cert.jpg', 'approved', '2025-08-08 07:49:09', 1, NULL, NULL, NULL, NULL, 1, '2025-08-06 07:49:09', '2025-12-16 08:41:07'),
(1733, 47, NULL, NULL, NULL, 'Main Construction Co 47', '2006-12-16', 19, 3, NULL, 'General Construction', 'Zamboanga City', 'company47@example.com', '09170000047', NULL, NULL, NULL, 'PCAB-60406', 'A', '2026-12-31', 'BP-47', 'Zamboanga', '2026-01-01', 'TIN-47', 'dti_cert.jpg', 'approved', '2025-07-14 07:49:09', 1, NULL, NULL, NULL, NULL, 37, '2025-07-12 07:49:09', '2025-12-16 08:41:07'),
(1734, 48, NULL, NULL, NULL, 'Main Construction Co 48', '2007-12-16', 18, 2, NULL, 'General Construction', 'Zamboanga City', 'company48@example.com', '09170000048', NULL, NULL, NULL, 'PCAB-21775', 'A', '2026-12-31', 'BP-48', 'Zamboanga', '2026-01-01', 'TIN-48', 'dti_cert.jpg', 'approved', '2025-06-17 07:49:09', 1, NULL, NULL, NULL, NULL, 10, '2025-06-15 07:49:09', '2025-12-16 08:41:07'),
(1735, 49, NULL, NULL, NULL, 'Main Construction Co 49', '1996-12-16', 29, 2, NULL, 'General Construction', 'Sample, Cahayagan, Carmen, Agusan Del Norte 2311', 'company49@example.com', '09170000049', NULL, NULL, NULL, 'PCAB-80787', 'A', '2026-12-31', 'BP-49', 'Agutaya', '2026-01-01', 'TIN-49', 'dti_cert.jpg', 'approved', '2025-12-14 07:49:09', 1, NULL, NULL, NULL, NULL, 4, '2025-12-12 07:49:09', '2025-12-16 09:56:43'),
(1736, 50, NULL, NULL, NULL, 'Main Construction Co 50', '2023-12-16', 2, 3, NULL, 'General Construction', 'Zamboanga City', 'company50@example.com', '09170000050', NULL, NULL, NULL, 'PCAB-46129', 'A', '2026-12-31', 'BP-50', 'Zamboanga', '2026-01-01', 'TIN-50', 'dti_cert.jpg', 'rejected', NULL, 1, NULL, NULL, NULL, 'Permit expired.', 47, '2025-08-24 07:49:09', '2025-12-16 08:41:07'),
(1737, 51, NULL, NULL, NULL, 'Main Construction Co 51', '2014-12-16', 11, 3, NULL, 'General Construction', 'Zamboanga City', 'company51@example.com', '09170000051', NULL, NULL, NULL, 'PCAB-85340', 'A', '2026-12-31', 'BP-51', 'Zamboanga', '2026-01-01', 'TIN-51', 'dti_cert.jpg', 'approved', '2025-09-19 07:49:09', 1, NULL, NULL, NULL, NULL, 3, '2025-09-17 07:49:09', '2025-12-16 08:41:07'),
(1738, 52, NULL, NULL, NULL, 'Main Construction Co 52', '2006-12-16', 19, 1, NULL, 'General Construction', 'Zamboanga City', 'company52@example.com', '09170000052', NULL, NULL, NULL, 'PCAB-78141', 'A', '2026-12-31', 'BP-52', 'Zamboanga', '2026-01-01', 'TIN-52', 'dti_cert.jpg', 'approved', '2025-08-29 07:49:09', 1, NULL, NULL, NULL, NULL, 46, '2025-08-27 07:49:09', '2025-12-16 08:41:07'),
(1739, 53, NULL, NULL, NULL, 'Main Construction Co 53', '2022-12-16', 3, 6, NULL, 'General Construction', 'Zamboanga City', 'company53@example.com', '09170000053', NULL, NULL, NULL, 'PCAB-34145', 'A', '2026-12-31', 'BP-53', 'Zamboanga', '2026-01-01', 'TIN-53', 'dti_cert.jpg', 'approved', '2025-11-12 07:49:09', 1, NULL, NULL, NULL, NULL, 30, '2025-11-10 07:49:09', '2025-12-16 08:41:07'),
(1740, 54, NULL, NULL, NULL, 'Main Construction Co 54', '2006-12-16', 19, 2, NULL, 'General Construction', 'Zamboanga City', 'company54@example.com', '09170000054', NULL, NULL, NULL, 'PCAB-32105', 'A', '2026-12-31', 'BP-54', 'Zamboanga', '2026-01-01', 'TIN-54', 'dti_cert.jpg', 'approved', '2025-01-15 07:49:09', 1, NULL, NULL, NULL, NULL, 38, '2025-01-13 07:49:09', '2025-12-16 08:41:07'),
(1741, 55, NULL, NULL, NULL, 'Main Construction Co 55', '2002-12-16', 23, 6, NULL, 'General Construction', 'Zamboanga City', 'company55@example.com', '09170000055', NULL, NULL, NULL, 'PCAB-36431', 'A', '2026-12-31', 'BP-55', 'Zamboanga', '2026-01-01', 'TIN-55', 'dti_cert.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 0, '2025-06-01 07:49:09', '2025-12-16 08:41:07'),
(1742, 56, NULL, NULL, NULL, 'Main Construction Co 56', '1997-12-16', 28, 3, NULL, 'General Construction', 'Zamboanga City', 'company56@example.com', '09170000056', NULL, NULL, NULL, 'PCAB-82813', 'A', '2026-12-31', 'BP-56', 'Zamboanga', '2026-01-01', 'TIN-56', 'dti_cert.jpg', 'approved', '2025-03-16 07:49:09', 1, NULL, NULL, NULL, NULL, 38, '2025-03-14 07:49:09', '2025-12-16 08:41:07'),
(1743, 57, NULL, NULL, NULL, 'Main Construction Co 57', '2013-12-16', 12, 1, NULL, 'General Construction', 'Zamboanga City', 'company57@example.com', '09170000057', NULL, NULL, NULL, 'PCAB-72710', 'A', '2026-12-31', 'BP-57', 'Zamboanga', '2026-01-01', 'TIN-57', 'dti_cert.jpg', 'approved', '2025-03-27 07:49:09', 1, NULL, NULL, NULL, NULL, 12, '2025-03-25 07:49:09', '2025-12-16 08:41:07'),
(1744, 58, NULL, NULL, NULL, 'Main Construction Co 58', '2019-12-16', 6, 8, NULL, 'General Construction', 'Zamboanga City', 'company58@example.com', '09170000058', NULL, NULL, NULL, 'PCAB-34834', 'A', '2026-12-31', 'BP-58', 'Zamboanga', '2026-01-01', 'TIN-58', 'dti_cert.jpg', 'approved', '2025-01-25 07:49:09', 1, NULL, NULL, NULL, NULL, 9, '2025-01-23 07:49:09', '2025-12-16 08:41:07'),
(1745, 59, NULL, NULL, NULL, 'Main Construction Co 59', '2004-12-16', 21, 6, NULL, 'General Construction', 'Zamboanga City', 'company59@example.com', '09170000059', NULL, NULL, NULL, 'PCAB-19251', 'A', '2026-12-31', 'BP-59', 'Zamboanga', '2026-01-01', 'TIN-59', 'dti_cert.jpg', 'approved', '2025-06-12 07:49:09', 1, NULL, NULL, NULL, NULL, 44, '2025-06-10 07:49:09', '2025-12-16 08:41:07'),
(1746, 60, NULL, NULL, NULL, 'Main Construction Co 60', '1996-12-16', 29, 4, NULL, 'General Construction', 'Zamboanga City', 'company60@example.com', '09170000060', NULL, NULL, NULL, 'PCAB-71914', 'A', '2026-12-31', 'BP-60', 'Zamboanga', '2026-01-01', 'TIN-60', 'dti_cert.jpg', 'rejected', NULL, 1, NULL, NULL, NULL, 'Permit expired.', 46, '2025-10-15 07:49:09', '2025-12-16 08:41:07'),
(1747, 61, NULL, NULL, NULL, 'Main Construction Co 61', '2005-12-16', 20, 3, NULL, 'General Construction', 'Zamboanga City', 'company61@example.com', '09170000061', NULL, NULL, NULL, 'PCAB-15554', 'A', '2026-12-31', 'BP-61', 'Zamboanga', '2026-01-01', 'TIN-61', 'dti_cert.jpg', 'approved', '2024-12-25 07:49:09', 1, NULL, NULL, NULL, NULL, 15, '2024-12-23 07:49:09', '2025-12-16 08:41:07'),
(1748, 62, NULL, NULL, NULL, 'Main Construction Co 62', '2013-12-16', 12, 7, NULL, 'General Construction', 'Zamboanga City', 'company62@example.com', '09170000062', NULL, NULL, NULL, 'PCAB-84279', 'A', '2026-12-31', 'BP-62', 'Zamboanga', '2026-01-01', 'TIN-62', 'dti_cert.jpg', 'approved', '2025-01-18 07:49:09', 1, NULL, NULL, NULL, NULL, 1, '2025-01-16 07:49:09', '2025-12-16 08:41:07'),
(1749, 63, NULL, NULL, NULL, 'Main Construction Co 63', '1997-12-16', 28, 4, NULL, 'General Construction', 'Zamboanga City', 'company63@example.com', '09170000063', NULL, NULL, NULL, 'PCAB-24967', 'A', '2026-12-31', 'BP-63', 'Zamboanga', '2026-01-01', 'TIN-63', 'dti_cert.jpg', 'approved', '2025-03-29 07:49:09', 1, NULL, NULL, NULL, NULL, 2, '2025-03-27 07:49:09', '2025-12-16 08:41:07'),
(1750, 64, NULL, NULL, NULL, 'Main Construction Co 64', '2010-12-16', 15, 5, NULL, 'General Construction', 'Zamboanga City', 'company64@example.com', '09170000064', NULL, NULL, NULL, 'PCAB-42118', 'A', '2026-12-31', 'BP-64', 'Zamboanga', '2026-01-01', 'TIN-64', 'dti_cert.jpg', 'approved', '2025-08-21 07:49:09', 1, NULL, NULL, NULL, NULL, 7, '2025-08-19 07:49:09', '2025-12-16 08:41:07'),
(1751, 65, NULL, NULL, NULL, 'Main Construction Co 65', '2005-12-16', 20, 1, NULL, 'General Construction', 'Zamboanga City', 'company65@example.com', '09170000065', NULL, NULL, NULL, 'PCAB-11349', 'A', '2026-12-31', 'BP-65', 'Zamboanga', '2026-01-01', 'TIN-65', 'dti_cert.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 10, '2025-01-05 07:49:09', '2025-12-16 08:41:07'),
(1752, 66, NULL, NULL, NULL, 'Main Construction Co 66', '2002-12-16', 23, 9, NULL, 'General Construction', 'Zamboanga City', 'company66@example.com', '09170000066', NULL, NULL, NULL, 'PCAB-91407', 'A', '2026-12-31', 'BP-66', 'Zamboanga', '2026-01-01', 'TIN-66', 'dti_cert.jpg', 'approved', '2025-10-21 07:49:09', 1, NULL, NULL, NULL, NULL, 18, '2025-10-19 07:49:09', '2025-12-16 08:41:07'),
(1753, 67, NULL, NULL, NULL, 'Main Construction Co 67', '1999-12-16', 26, 3, NULL, 'General Construction', 'Zamboanga City', 'company67@example.com', '09170000067', NULL, NULL, NULL, 'PCAB-58294', 'A', '2026-12-31', 'BP-67', 'Zamboanga', '2026-01-01', 'TIN-67', 'dti_cert.jpg', 'approved', '2025-01-01 07:49:09', 1, NULL, NULL, NULL, NULL, 13, '2024-12-30 07:49:09', '2025-12-16 08:41:07'),
(1754, 68, NULL, NULL, NULL, 'Main Construction Co 68', '1997-12-16', 28, 8, NULL, 'General Construction', 'Zamboanga City', 'company68@example.com', '09170000068', NULL, NULL, NULL, 'PCAB-21255', 'A', '2026-12-31', 'BP-68', 'Zamboanga', '2026-01-01', 'TIN-68', 'dti_cert.jpg', 'approved', '2025-06-26 07:49:09', 1, NULL, NULL, NULL, NULL, 8, '2025-06-24 07:49:09', '2025-12-16 08:41:07'),
(1755, 69, NULL, NULL, NULL, 'Main Construction Co 69', '2021-12-16', 4, 1, NULL, 'General Construction', 'Zamboanga City', 'company69@example.com', '09170000069', NULL, NULL, NULL, 'PCAB-67993', 'A', '2026-12-31', 'BP-69', 'Zamboanga', '2026-01-01', 'TIN-69', 'dti_cert.jpg', 'approved', '2025-03-18 07:49:09', 1, NULL, NULL, NULL, NULL, 48, '2025-03-16 07:49:09', '2025-12-16 08:41:07'),
(1756, 70, NULL, NULL, NULL, 'Main Construction Co 70', '2000-12-16', 25, 9, NULL, 'General Construction', 'Zamboanga City', 'company70@example.com', '09170000070', NULL, NULL, NULL, 'PCAB-28285', 'A', '2026-12-31', 'BP-70', 'Zamboanga', '2026-01-01', 'TIN-70', 'dti_cert.jpg', 'rejected', NULL, 1, NULL, NULL, NULL, 'Permit expired.', 2, '2025-07-27 07:49:09', '2025-12-16 08:41:07'),
(1757, 71, NULL, NULL, NULL, 'Main Construction Co 71', '2003-12-16', 22, 6, NULL, 'General Construction', 'Zamboanga City', 'company71@example.com', '09170000071', NULL, NULL, NULL, 'PCAB-42832', 'A', '2026-12-31', 'BP-71', 'Zamboanga', '2026-01-01', 'TIN-71', 'dti_cert.jpg', 'approved', '2025-07-26 07:49:09', 1, NULL, NULL, NULL, NULL, 12, '2025-07-24 07:49:09', '2025-12-16 08:41:07'),
(1758, 72, NULL, NULL, NULL, 'Main Construction Co 72', '2019-12-16', 6, 7, NULL, 'General Construction', 'Zamboanga City', 'company72@example.com', '09170000072', NULL, NULL, NULL, 'PCAB-52638', 'A', '2026-12-31', 'BP-72', 'Zamboanga', '2026-01-01', 'TIN-72', 'dti_cert.jpg', 'approved', '2025-04-28 07:49:09', 1, NULL, NULL, NULL, NULL, 31, '2025-04-26 07:49:09', '2025-12-16 08:41:07'),
(1759, 73, NULL, NULL, NULL, 'Main Construction Co 73', '2004-12-16', 21, 1, NULL, 'General Construction', 'Zamboanga City', 'company73@example.com', '09170000073', NULL, NULL, NULL, 'PCAB-28803', 'A', '2026-12-31', 'BP-73', 'Zamboanga', '2026-01-01', 'TIN-73', 'dti_cert.jpg', 'approved', '2025-02-23 07:49:09', 1, NULL, NULL, NULL, NULL, 9, '2025-02-21 07:49:09', '2025-12-16 08:41:07'),
(1760, 74, NULL, NULL, NULL, 'Main Construction Co 74', '1997-12-16', 28, 3, NULL, 'General Construction', 'Zamboanga City', 'company74@example.com', '09170000074', NULL, NULL, NULL, 'PCAB-70844', 'A', '2026-12-31', 'BP-74', 'Zamboanga', '2026-01-01', 'TIN-74', 'dti_cert.jpg', 'approved', '2025-01-31 07:49:09', 1, NULL, NULL, NULL, NULL, 18, '2025-01-29 07:49:09', '2025-12-16 08:41:07'),
(1761, 75, NULL, NULL, NULL, 'Main Construction Co 75', '2007-12-16', 18, 6, NULL, 'General Construction', 'Zamboanga City', 'company75@example.com', '09170000075', NULL, NULL, NULL, 'PCAB-56597', 'A', '2026-12-31', 'BP-75', 'Zamboanga', '2026-01-01', 'TIN-75', 'dti_cert.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 35, '2025-11-17 07:49:09', '2025-12-16 08:41:07'),
(1762, 76, NULL, NULL, NULL, 'Main Construction Co 76', '2020-12-16', 5, 3, NULL, 'General Construction', 'Zamboanga City', 'company76@example.com', '09170000076', NULL, NULL, NULL, 'PCAB-73540', 'A', '2026-12-31', 'BP-76', 'Zamboanga', '2026-01-01', 'TIN-76', 'dti_cert.jpg', 'approved', '2025-10-07 07:49:09', 1, NULL, NULL, NULL, NULL, 26, '2025-10-05 07:49:09', '2025-12-16 08:41:07'),
(1763, 77, NULL, NULL, NULL, 'Main Construction Co 77', '1995-12-16', 30, 8, NULL, 'General Construction', 'Zamboanga City', 'company77@example.com', '09170000077', NULL, NULL, NULL, 'PCAB-30352', 'A', '2026-12-31', 'BP-77', 'Zamboanga', '2026-01-01', 'TIN-77', 'dti_cert.jpg', 'approved', '2025-04-13 07:49:09', 1, NULL, NULL, NULL, NULL, 33, '2025-04-11 07:49:09', '2025-12-16 08:41:07'),
(1764, 78, NULL, NULL, NULL, 'Main Construction Co 78', '2012-12-16', 13, 4, NULL, 'General Construction', 'Zamboanga City', 'company78@example.com', '09170000078', NULL, NULL, NULL, 'PCAB-97162', 'A', '2026-12-31', 'BP-78', 'Zamboanga', '2026-01-01', 'TIN-78', 'dti_cert.jpg', 'approved', '2025-12-07 07:49:09', 1, NULL, NULL, NULL, NULL, 27, '2025-12-05 07:49:09', '2025-12-16 08:41:07'),
(1765, 79, NULL, NULL, NULL, 'Main Construction Co 79', '2021-12-16', 4, 8, NULL, 'General Construction', 'Zamboanga City', 'company79@example.com', '09170000079', NULL, NULL, NULL, 'PCAB-11742', 'A', '2026-12-31', 'BP-79', 'Zamboanga', '2026-01-01', 'TIN-79', 'dti_cert.jpg', 'approved', '2025-04-27 07:49:09', 1, NULL, NULL, NULL, NULL, 27, '2025-04-25 07:49:09', '2025-12-16 08:41:07'),
(1766, 80, NULL, NULL, NULL, 'Main Construction Co 80', '2015-12-16', 10, 5, NULL, 'General Construction', 'Zamboanga City', 'company80@example.com', '09170000080', NULL, NULL, NULL, 'PCAB-94072', 'A', '2026-12-31', 'BP-80', 'Zamboanga', '2026-01-01', 'TIN-80', 'dti_cert.jpg', 'rejected', NULL, 1, NULL, NULL, NULL, 'Permit expired.', 43, '2025-02-08 07:49:09', '2025-12-16 08:41:07'),
(1767, 81, NULL, NULL, NULL, 'Main Construction Co 81', '2018-12-16', 7, 4, NULL, 'General Construction', 'Zamboanga City', 'company81@example.com', '09170000081', NULL, NULL, NULL, 'PCAB-22152', 'A', '2026-12-31', 'BP-81', 'Zamboanga', '2026-01-01', 'TIN-81', 'dti_cert.jpg', 'approved', '2025-03-27 07:49:09', 1, NULL, NULL, NULL, NULL, 6, '2025-03-25 07:49:09', '2025-12-16 08:41:07'),
(1768, 82, NULL, NULL, NULL, 'Main Construction Co 82', '2021-12-16', 4, 4, NULL, 'General Construction', 'Zamboanga City', 'company82@example.com', '09170000082', NULL, NULL, NULL, 'PCAB-97364', 'A', '2026-12-31', 'BP-82', 'Zamboanga', '2026-01-01', 'TIN-82', 'dti_cert.jpg', 'approved', '2025-01-07 07:49:09', 1, NULL, NULL, NULL, NULL, 4, '2025-01-05 07:49:09', '2025-12-16 08:41:07'),
(1769, 83, NULL, NULL, NULL, 'Main Construction Co 83', '1997-12-16', 28, 7, NULL, 'General Construction', 'Zamboanga City', 'company83@example.com', '09170000083', NULL, NULL, NULL, 'PCAB-14246', 'A', '2026-12-31', 'BP-83', 'Zamboanga', '2026-01-01', 'TIN-83', 'dti_cert.jpg', 'approved', '2024-12-27 07:49:09', 1, NULL, NULL, NULL, NULL, 47, '2024-12-25 07:49:09', '2025-12-16 08:41:07'),
(1770, 84, NULL, NULL, NULL, 'Main Construction Co 84', '2018-12-16', 7, 1, NULL, 'General Construction', 'Zamboanga City', 'company84@example.com', '09170000084', NULL, NULL, NULL, 'PCAB-81872', 'A', '2026-12-31', 'BP-84', 'Zamboanga', '2026-01-01', 'TIN-84', 'dti_cert.jpg', 'approved', '2025-03-15 07:49:09', 1, NULL, NULL, NULL, NULL, 25, '2025-03-13 07:49:09', '2025-12-16 08:41:07'),
(1771, 85, NULL, NULL, NULL, 'Main Construction Co 85', '2014-12-16', 11, 6, NULL, 'General Construction', 'Zamboanga City', 'company85@example.com', '09170000085', NULL, NULL, NULL, 'PCAB-39928', 'A', '2026-12-31', 'BP-85', 'Zamboanga', '2026-01-01', 'TIN-85', 'dti_cert.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 26, '2024-12-27 07:49:09', '2025-12-16 08:41:07'),
(1772, 86, NULL, NULL, NULL, 'Main Construction Co 86', '2022-12-16', 3, 7, NULL, 'General Construction', 'Zamboanga City', 'company86@example.com', '09170000086', NULL, NULL, NULL, 'PCAB-74824', 'A', '2026-12-31', 'BP-86', 'Zamboanga', '2026-01-01', 'TIN-86', 'dti_cert.jpg', 'approved', '2025-08-28 07:49:09', 1, NULL, NULL, NULL, NULL, 49, '2025-08-26 07:49:09', '2025-12-16 08:41:07'),
(1773, 87, NULL, NULL, NULL, 'Main Construction Co 87', '2014-12-16', 11, 8, NULL, 'General Construction', 'Zamboanga City', 'company87@example.com', '09170000087', NULL, NULL, NULL, 'PCAB-17198', 'A', '2026-12-31', 'BP-87', 'Zamboanga', '2026-01-01', 'TIN-87', 'dti_cert.jpg', 'approved', '2025-08-23 07:49:09', 1, NULL, NULL, NULL, NULL, 49, '2025-08-21 07:49:09', '2025-12-16 08:41:07'),
(1774, 88, NULL, NULL, NULL, 'Main Construction Co 88', '2009-12-16', 16, 9, NULL, 'General Construction', 'Zamboanga City', 'company88@example.com', '09170000088', NULL, NULL, NULL, 'PCAB-27581', 'A', '2026-12-31', 'BP-88', 'Zamboanga', '2026-01-01', 'TIN-88', 'dti_cert.jpg', 'approved', '2025-09-24 07:49:09', 1, NULL, NULL, NULL, NULL, 24, '2025-09-22 07:49:09', '2025-12-16 08:41:07'),
(1775, 89, NULL, NULL, NULL, 'Main Construction Co 89', '2009-12-16', 16, 4, NULL, 'General Construction', 'Zamboanga City', 'company89@example.com', '09170000089', NULL, NULL, NULL, 'PCAB-97920', 'A', '2026-12-31', 'BP-89', 'Zamboanga', '2026-01-01', 'TIN-89', 'dti_cert.jpg', 'approved', '2025-07-13 07:49:09', 1, NULL, NULL, NULL, NULL, 29, '2025-07-11 07:49:09', '2025-12-16 08:41:07'),
(1776, 90, NULL, NULL, NULL, 'Main Construction Co 90', '2024-12-16', 1, 8, NULL, 'General Construction', 'Zamboanga City', 'company90@example.com', '09170000090', NULL, NULL, NULL, 'PCAB-36143', 'A', '2026-12-31', 'BP-90', 'Zamboanga', '2026-01-01', 'TIN-90', 'dti_cert.jpg', 'rejected', NULL, 1, NULL, NULL, NULL, 'Permit expired.', 18, '2025-04-07 07:49:09', '2025-12-16 08:41:07'),
(1777, 91, NULL, NULL, NULL, 'Main Construction Co 91', '2008-12-16', 17, 4, NULL, 'General Construction', 'Zamboanga City', 'company91@example.com', '09170000091', NULL, NULL, NULL, 'PCAB-34191', 'A', '2026-12-31', 'BP-91', 'Zamboanga', '2026-01-01', 'TIN-91', 'dti_cert.jpg', 'approved', '2025-09-18 07:49:09', 1, NULL, NULL, NULL, NULL, 2, '2025-09-16 07:49:09', '2025-12-16 08:41:07'),
(1778, 92, NULL, NULL, NULL, 'Main Construction Co 92', '2002-12-16', 23, 7, NULL, 'General Construction', 'Zamboanga City', 'company92@example.com', '09170000092', NULL, NULL, NULL, 'PCAB-89813', 'A', '2026-12-31', 'BP-92', 'Zamboanga', '2026-01-01', 'TIN-92', 'dti_cert.jpg', 'approved', '2025-10-11 07:49:09', 1, NULL, NULL, NULL, NULL, 10, '2025-10-09 07:49:09', '2025-12-16 08:41:07'),
(1779, 93, NULL, NULL, NULL, 'Main Construction Co 93', '2021-12-16', 4, 7, NULL, 'General Construction', 'Zamboanga City', 'company93@example.com', '09170000093', NULL, NULL, NULL, 'PCAB-59197', 'A', '2026-12-31', 'BP-93', 'Zamboanga', '2026-01-01', 'TIN-93', 'dti_cert.jpg', 'approved', '2025-10-08 07:49:09', 1, NULL, NULL, NULL, NULL, 12, '2025-10-06 07:49:09', '2025-12-16 08:41:07'),
(1780, 94, NULL, NULL, NULL, 'Main Construction Co 94', '2017-12-16', 8, 6, NULL, 'General Construction', 'Zamboanga City', 'company94@example.com', '09170000094', NULL, NULL, NULL, 'PCAB-74178', 'A', '2026-12-31', 'BP-94', 'Zamboanga', '2026-01-01', 'TIN-94', 'dti_cert.jpg', 'approved', '2025-05-26 07:49:09', 1, NULL, NULL, NULL, NULL, 4, '2025-05-24 07:49:09', '2025-12-16 08:41:07'),
(1781, 95, NULL, NULL, NULL, 'Main Construction Co 95', '1997-12-16', 28, 2, NULL, 'General Construction', 'Zamboanga City', 'company95@example.com', '09170000095', NULL, NULL, NULL, 'PCAB-32796', 'A', '2026-12-31', 'BP-95', 'Zamboanga', '2026-01-01', 'TIN-95', 'dti_cert.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 21, '2025-04-03 07:49:09', '2025-12-16 08:41:07'),
(1782, 96, NULL, NULL, NULL, 'Main Construction Co 96', '2001-12-16', 24, 1, NULL, 'General Construction', 'Zamboanga City', 'company96@example.com', '09170000096', NULL, NULL, NULL, 'PCAB-78766', 'A', '2026-12-31', 'BP-96', 'Zamboanga', '2026-01-01', 'TIN-96', 'dti_cert.jpg', 'approved', '2025-12-02 07:49:09', 1, NULL, NULL, NULL, NULL, 11, '2025-11-30 07:49:09', '2025-12-16 08:41:07'),
(1783, 97, NULL, NULL, NULL, 'Main Construction Co 97', '2017-12-16', 8, 1, NULL, 'General Construction', 'Zamboanga City', 'company97@example.com', '09170000097', NULL, NULL, NULL, 'PCAB-29749', 'A', '2026-12-31', 'BP-97', 'Zamboanga', '2026-01-01', 'TIN-97', 'dti_cert.jpg', 'approved', '2025-01-04 07:49:09', 1, NULL, NULL, NULL, NULL, 13, '2025-01-02 07:49:09', '2025-12-16 08:41:07'),
(1784, 98, NULL, NULL, NULL, 'Main Construction Co 98', '1997-12-16', 28, 4, NULL, 'General Construction', 'Zamboanga City', 'company98@example.com', '09170000098', NULL, NULL, NULL, 'PCAB-73126', 'A', '2026-12-31', 'BP-98', 'Zamboanga', '2026-01-01', 'TIN-98', 'dti_cert.jpg', 'approved', '2025-10-25 07:49:09', 1, NULL, NULL, NULL, NULL, 30, '2025-10-23 07:49:09', '2025-12-16 08:41:07'),
(1785, 99, NULL, NULL, NULL, 'Main Construction Co 99', '2003-12-16', 22, 7, NULL, 'General Construction', 'Zamboanga City', 'company99@example.com', '09170000099', NULL, NULL, NULL, 'PCAB-92160', 'A', '2026-12-31', 'BP-99', 'Zamboanga', '2026-01-01', 'TIN-99', 'dti_cert.jpg', 'approved', '2025-07-15 07:49:09', 1, NULL, NULL, NULL, NULL, 28, '2025-07-13 07:49:09', '2025-12-16 08:41:07'),
(1786, 100, NULL, NULL, NULL, 'Main Construction Co 100', '1996-12-16', 29, 9, NULL, 'General Construction', 'Zamboanga City', 'company100@example.com', '09170000100', NULL, NULL, NULL, 'PCAB-87531', 'A', '2026-12-31', 'BP-100', 'Zamboanga', '2026-01-01', 'TIN-100', 'dti_cert.jpg', 'rejected', NULL, 1, NULL, NULL, NULL, 'Permit expired.', 27, '2025-07-02 07:49:09', '2025-12-16 08:41:07'),
(1787, 201, NULL, NULL, NULL, 'Main Construction Co 201', '2006-12-16', 19, 6, NULL, 'General Construction', 'Zamboanga City', 'company201@example.com', '09170000201', NULL, NULL, NULL, 'PCAB-77737', 'A', '2026-12-31', 'BP-201', 'Zamboanga', '2026-01-01', 'TIN-201', 'dti_cert.jpg', 'approved', '2025-08-16 07:49:09', 1, NULL, NULL, NULL, NULL, 42, '2025-08-14 07:49:09', '2025-12-16 08:41:07'),
(1788, 202, NULL, NULL, NULL, 'Main Construction Co 202', '2021-12-16', 4, 2, NULL, 'General Construction', 'Zamboanga City', 'company202@example.com', '09170000202', NULL, NULL, NULL, 'PCAB-39620', 'A', '2026-12-31', 'BP-202', 'Zamboanga', '2026-01-01', 'TIN-202', 'dti_cert.jpg', 'approved', '2025-04-18 07:49:09', 1, NULL, NULL, NULL, NULL, 35, '2025-04-16 07:49:09', '2025-12-16 08:41:07'),
(1789, 203, NULL, NULL, NULL, 'Main Construction Co 203', '2000-12-16', 25, 8, NULL, 'General Construction', 'Zamboanga City', 'company203@example.com', '09170000203', NULL, NULL, NULL, 'PCAB-69863', 'A', '2026-12-31', 'BP-203', 'Zamboanga', '2026-01-01', 'TIN-203', 'dti_cert.jpg', 'approved', '2025-09-06 07:49:09', 1, NULL, NULL, NULL, NULL, 15, '2025-09-04 07:49:09', '2025-12-16 08:41:07'),
(1790, 204, NULL, NULL, NULL, 'Main Construction Co 204', '2002-12-16', 23, 3, NULL, 'General Construction', 'Zamboanga City', 'company204@example.com', '09170000204', NULL, NULL, NULL, 'PCAB-61739', 'A', '2026-12-31', 'BP-204', 'Zamboanga', '2026-01-01', 'TIN-204', 'dti_cert.jpg', 'approved', '2025-09-23 07:49:09', 1, NULL, NULL, NULL, NULL, 41, '2025-09-21 07:49:09', '2025-12-16 08:41:07'),
(1791, 205, NULL, NULL, NULL, 'Main Construction Co 205', '2018-12-16', 7, 1, NULL, 'General Construction', 'Zamboanga City', 'company205@example.com', '09170000205', NULL, NULL, NULL, 'PCAB-32187', 'A', '2026-12-31', 'BP-205', 'Zamboanga', '2026-01-01', 'TIN-205', 'dti_cert.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 21, '2025-05-29 07:49:09', '2025-12-16 08:41:07'),
(1792, 206, NULL, NULL, NULL, 'Main Construction Co 206', '1999-12-16', 26, 1, NULL, 'General Construction', 'Zamboanga City', 'company206@example.com', '09170000206', NULL, NULL, NULL, 'PCAB-75939', 'A', '2026-12-31', 'BP-206', 'Zamboanga', '2026-01-01', 'TIN-206', 'dti_cert.jpg', 'approved', '2025-11-17 07:49:09', 1, NULL, NULL, NULL, NULL, 45, '2025-11-15 07:49:09', '2025-12-16 08:41:07'),
(1793, 207, NULL, NULL, NULL, 'Main Construction Co 207', '2006-12-16', 19, 3, NULL, 'General Construction', 'Zamboanga City', 'company207@example.com', '09170000207', NULL, NULL, NULL, 'PCAB-51453', 'A', '2026-12-31', 'BP-207', 'Zamboanga', '2026-01-01', 'TIN-207', 'dti_cert.jpg', 'approved', '2025-04-18 07:49:09', 1, NULL, NULL, NULL, NULL, 3, '2025-04-16 07:49:09', '2025-12-16 08:41:07'),
(1794, 208, NULL, NULL, NULL, 'Main Construction Co 208', '2009-12-16', 16, 2, NULL, 'General Construction', 'Zamboanga City', 'company208@example.com', '09170000208', NULL, NULL, NULL, 'PCAB-13265', 'A', '2026-12-31', 'BP-208', 'Zamboanga', '2026-01-01', 'TIN-208', 'dti_cert.jpg', 'approved', '2025-12-10 07:49:09', 1, NULL, NULL, NULL, NULL, 8, '2025-12-08 07:49:09', '2025-12-16 08:41:07'),
(1795, 209, NULL, NULL, NULL, 'Main Construction Co 209', '2003-12-16', 22, 2, NULL, 'General Construction', 'Zamboanga City', 'company209@example.com', '09170000209', NULL, NULL, NULL, 'PCAB-91806', 'A', '2026-12-31', 'BP-209', 'Zamboanga', '2026-01-01', 'TIN-209', 'dti_cert.jpg', 'approved', '2025-07-17 07:49:09', 1, NULL, NULL, NULL, NULL, 24, '2025-07-15 07:49:09', '2025-12-16 08:41:07'),
(1796, 210, NULL, NULL, NULL, 'Main Construction Co 210', '2021-12-16', 4, 7, NULL, 'General Construction', 'Zamboanga City', 'company210@example.com', '09170000210', NULL, NULL, NULL, 'PCAB-69228', 'A', '2026-12-31', 'BP-210', 'Zamboanga', '2026-01-01', 'TIN-210', 'dti_cert.jpg', 'rejected', NULL, 1, NULL, NULL, NULL, 'Permit expired.', 45, '2025-06-18 07:49:09', '2025-12-16 08:41:07'),
(1797, 211, NULL, NULL, NULL, 'Main Construction Co 211', '2014-12-16', 11, 6, NULL, 'General Construction', 'Zamboanga City', 'company211@example.com', '09170000211', NULL, NULL, NULL, 'PCAB-52834', 'A', '2026-12-31', 'BP-211', 'Zamboanga', '2026-01-01', 'TIN-211', 'dti_cert.jpg', 'approved', '2025-10-14 07:49:09', 1, NULL, NULL, NULL, NULL, 49, '2025-10-12 07:49:09', '2025-12-16 08:41:07'),
(1798, 212, NULL, NULL, NULL, 'Main Construction Co 212', '2011-12-16', 14, 7, NULL, 'General Construction', 'Zamboanga City', 'company212@example.com', '09170000212', NULL, NULL, NULL, 'PCAB-70043', 'A', '2026-12-31', 'BP-212', 'Zamboanga', '2026-01-01', 'TIN-212', 'dti_cert.jpg', 'approved', '2025-02-18 07:49:09', 1, NULL, NULL, NULL, NULL, 16, '2025-02-16 07:49:09', '2025-12-16 08:41:07'),
(1799, 213, NULL, NULL, NULL, 'Main Construction Co 213', '2019-12-16', 6, 7, NULL, 'General Construction', 'Zamboanga City', 'company213@example.com', '09170000213', NULL, NULL, NULL, 'PCAB-34511', 'A', '2026-12-31', 'BP-213', 'Zamboanga', '2026-01-01', 'TIN-213', 'dti_cert.jpg', 'approved', '2025-05-19 07:49:09', 1, NULL, NULL, NULL, NULL, 5, '2025-05-17 07:49:09', '2025-12-16 08:41:07'),
(1800, 214, NULL, NULL, NULL, 'Main Construction Co 214', '2006-12-16', 19, 6, NULL, 'General Construction', 'Zamboanga City', 'company214@example.com', '09170000214', NULL, NULL, NULL, 'PCAB-56516', 'A', '2026-12-31', 'BP-214', 'Zamboanga', '2026-01-01', 'TIN-214', 'dti_cert.jpg', 'approved', '2025-06-20 07:49:09', 1, NULL, NULL, NULL, NULL, 2, '2025-06-18 07:49:09', '2025-12-16 08:41:07'),
(1801, 215, NULL, NULL, NULL, 'Main Construction Co 215', '2011-12-16', 14, 7, NULL, 'General Construction', 'Zamboanga City', 'company215@example.com', '09170000215', NULL, NULL, NULL, 'PCAB-75873', 'A', '2026-12-31', 'BP-215', 'Zamboanga', '2026-01-01', 'TIN-215', 'dti_cert.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 5, '2025-08-12 07:49:09', '2025-12-16 08:41:07'),
(1802, 216, NULL, NULL, NULL, 'Main Construction Co 216', '2011-12-16', 14, 1, NULL, 'General Construction', 'Zamboanga City', 'company216@example.com', '09170000216', NULL, NULL, NULL, 'PCAB-62029', 'A', '2026-12-31', 'BP-216', 'Zamboanga', '2026-01-01', 'TIN-216', 'dti_cert.jpg', 'approved', '2025-04-13 07:49:09', 1, NULL, NULL, NULL, NULL, 27, '2025-04-11 07:49:09', '2025-12-16 08:41:07'),
(1803, 217, NULL, NULL, NULL, 'Main Construction Co 217', '1998-12-16', 27, 9, NULL, 'General Construction', 'Zamboanga City', 'company217@example.com', '09170000217', NULL, NULL, NULL, 'PCAB-27801', 'A', '2026-12-31', 'BP-217', 'Zamboanga', '2026-01-01', 'TIN-217', 'dti_cert.jpg', 'approved', '2025-01-16 07:49:09', 1, NULL, NULL, NULL, NULL, 49, '2025-01-14 07:49:09', '2025-12-16 08:41:07'),
(1804, 218, NULL, NULL, NULL, 'Main Construction Co 218', '2022-12-16', 3, 8, NULL, 'General Construction', 'Zamboanga City', 'company218@example.com', '09170000218', NULL, NULL, NULL, 'PCAB-45679', 'A', '2026-12-31', 'BP-218', 'Zamboanga', '2026-01-01', 'TIN-218', 'dti_cert.jpg', 'approved', '2025-06-07 07:49:09', 1, NULL, NULL, NULL, NULL, 23, '2025-06-05 07:49:09', '2025-12-16 08:41:07'),
(1805, 219, NULL, NULL, NULL, 'Main Construction Co 219', '2000-12-16', 25, 4, NULL, 'General Construction', 'Zamboanga City', 'company219@example.com', '09170000219', NULL, NULL, NULL, 'PCAB-73715', 'A', '2026-12-31', 'BP-219', 'Zamboanga', '2026-01-01', 'TIN-219', 'dti_cert.jpg', 'approved', '2025-03-11 07:49:09', 1, NULL, NULL, NULL, NULL, 13, '2025-03-09 07:49:09', '2025-12-16 08:41:07'),
(1806, 220, NULL, NULL, NULL, 'Main Construction Co 220', '2001-12-16', 24, 5, NULL, 'General Construction', 'Zamboanga City', 'company220@example.com', '09170000220', NULL, NULL, NULL, 'PCAB-16396', 'A', '2026-12-31', 'BP-220', 'Zamboanga', '2026-01-01', 'TIN-220', 'dti_cert.jpg', 'rejected', NULL, 1, NULL, NULL, NULL, 'Permit expired.', 49, '2025-11-08 07:49:09', '2025-12-16 08:41:07'),
(1807, 360, NULL, NULL, NULL, 'Rone Works', '2023-01-01', 2, 9, 'Gaming', 'Nothing', 'Nothing, Amparo, City of Butuan, 160200000 7000', 'shanehart100q1@gmail.com', '09926314071', NULL, NULL, NULL, '1231232', 'AA', '2025-12-31', '12124', 'Ajuy', '2025-12-31', '13123123', 'DTI_SEC/dAbCKi0N1cEfYwEtU0cQKlsCp1EoVCeh22H3ldvV.jpg', 'approved', '2025-12-16 08:22:15', 1, NULL, NULL, NULL, NULL, 0, '2025-12-16 08:22:15', '2025-12-16 09:21:56'),
(1808, 361, NULL, NULL, NULL, 'Krystal Services', '2023-01-31', 2, 5, NULL, 'Nothing', 'Sample, Humilog, Remedios T. Romualdez, Agusan Del Norte 2311', 'shanehart1001d@gmail.com', '09926314033', 'https://krystal.com', 'https://krystalservices.com', 'desc to', '123341212', 'AA', '2025-12-31', '1234423123', 'Aguinaldo', '2025-12-31', '1231231ss', 'DTI_SEC/ECDYAx9WeQj2kcVgICbYIdyf7lCE903hLfOZdvBN.jpg', 'approved', '2025-12-16 08:40:00', 1, NULL, NULL, NULL, NULL, 0, '2025-12-16 08:40:00', '2025-12-16 20:49:54'),
(1809, 372, NULL, NULL, NULL, 'test2', '2025-12-18', 5, 7, NULL, 'yes services here and there', 'Anywhere, Saluping, Tabuan-Lasa, Basilan 7000', 'slayvibe.info@gmail.com', '09360211157', NULL, NULL, NULL, '82919910181', 'AAAA', '2026-12-18', '01917291082', 'Zamboanga City', '2025-12-18', '0198237292772', 'DTI_SEC/41dcx0yqd7nbVQK36H34tylFRgZYKrCyF5vPasPU.jpg', 'approved', '0000-00-00 00:00:00', 1, NULL, NULL, NULL, NULL, 0, '2025-12-17 13:58:50', '2025-12-18 15:00:59'),
(1810, 380, NULL, NULL, NULL, 'Apex Company', '2026-02-21', 40, 8, NULL, 'iydiyditd', '456 oak ridge, Inyawan, Libertad, Antique, 7000', 'joxego4264@advarm.com', '09360521478', 'mbkyc.com', 'khfykdyo', NULL, '936336939663', 'AAA', '2027-02-02', '0292746391', 'Alcala', '2027-02-02', '13589652', 'DTI_SEC/jNfmB6LE9tIJvCij8DG61oi7HTCji94XlRwQFjcu.jpg', 'approved', NULL, 1, NULL, NULL, NULL, NULL, 0, '2026-02-21 01:34:20', '2026-02-21 09:38:34');

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
  `role` enum('owner','manager','engineer','others','architect','representative') DEFAULT 'owner',
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
(1924, 1735, 49, 'OwnerLast49', 'Ownerlang', 'OwnerFirst49', '09170000049', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-12 07:49:09'),
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
(2033, 1808, 251, 'StaffLast251', NULL, 'StaffFirst251', '09200000251', 'representative', '', NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
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
(2052, 1784, 270, 'StaffLast270', NULL, 'StaffFirst270', '09200000270', 'others', 'Safety Officer', NULL, 0, 1, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(2053, 1807, 360, 'Kulong', NULL, 'Rone', '09926314071', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-16 08:22:15'),
(2054, 1808, 361, 'Bongo', NULL, 'Krystal', '09926314033', 'owner', NULL, NULL, 0, 1, NULL, NULL, '', '2025-12-16 08:40:00'),
(2055, 1809, 372, 'Test2', NULL, 'Test2', '09360211157', 'owner', NULL, NULL, 0, 1, NULL, NULL, NULL, '2025-12-17 13:58:50'),
(2056, 1809, 376, 'wwwwwww', 'wwwwwwww', 'wwwwwwww', '09987674839', 'representative', 'others', 'ddddddd', 0, 1, NULL, NULL, NULL, '2026-01-27 06:39:40'),
(2057, 1809, 377, 'wwwwwwwwwwwww', 'wwwww', 'wwwwwwww', '09987647589', 'architect', NULL, NULL, 0, 1, NULL, NULL, NULL, '2026-01-27 06:43:03'),
(2058, 1809, 378, 'Sah', NULL, 'Emman', '09987678987', 'others', NULL, 'Nigas', 0, 1, NULL, NULL, NULL, '2026-01-29 04:26:35'),
(2059, 1810, 380, 'test4', 'test4', 'test4', '09360521479', 'owner', NULL, NULL, 0, 0, NULL, NULL, NULL, '2026-02-21 01:34:20');

-- --------------------------------------------------------

--
-- Table structure for table `contract_terminations`
--

CREATE TABLE `contract_terminations` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `contractor_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `remarks` text NOT NULL,
  `reason` text DEFAULT NULL,
  `terminated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `conversation_id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `receiver_id` bigint(20) UNSIGNED NOT NULL,
  `is_suspended` tinyint(11) DEFAULT 0,
  `no_suspends` int(11) NOT NULL DEFAULT 0,
  `reason` varchar(255) DEFAULT NULL,
  `suspended_until` datetime DEFAULT NULL,
  `status` enum('active','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`conversation_id`, `sender_id`, `receiver_id`, `is_suspended`, `no_suspends`, `reason`, `suspended_until`, `status`, `created_at`, `updated_at`) VALUES
(1000002, 1, 2, 0, 0, NULL, NULL, 'active', '2026-02-06 23:31:07', '2026-02-06 23:31:07'),
(1000010, 1, 10, 0, 0, NULL, NULL, 'active', '2026-02-07 03:08:43', '2026-02-07 03:08:43'),
(1000102, 1, 102, 0, 0, 'spam', NULL, 'active', '2026-02-07 03:19:39', '2026-02-07 03:19:39'),
(1000103, 1, 103, 0, 0, NULL, NULL, 'active', '2026-02-07 03:26:49', '2026-02-07 03:26:49'),
(1000371, 1, 371, 0, 0, NULL, NULL, 'active', '2026-02-06 22:56:13', '2026-02-06 22:56:13'),
(1000372, 1, 372, 0, 0, NULL, NULL, 'active', '2026-02-06 22:56:53', '2026-02-07 01:15:12'),
(2000371, 371, 2, 0, 4, NULL, NULL, 'active', '2026-02-07 19:29:05', '2026-02-07 19:29:05'),
(3000372, 372, 3, 0, 0, NULL, NULL, 'active', '2026-02-08 07:47:21', '2026-02-08 07:47:21'),
(103000371, 371, 103, 0, 2, NULL, NULL, 'active', '2026-02-08 06:44:37', '2026-02-08 06:44:37'),
(352000372, 372, 352, 0, 1, NULL, NULL, 'active', '2026-02-08 04:39:09', '2026-02-08 04:39:09'),
(371000372, 371, 372, 0, 4, NULL, NULL, 'active', '2026-02-07 19:29:05', '2026-02-07 19:29:05');

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
  `dispute_type` enum('Payment','Delay','Quality','Others','Halt') NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `if_others_distype` varchar(255) DEFAULT NULL,
  `dispute_desc` text NOT NULL,
  `dispute_status` enum('open','under_review','resolved','closed','cancelled') DEFAULT 'open',
  `reason` text DEFAULT NULL,
  `requested_action` text NOT NULL DEFAULT '',
  `admin_response` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `disputes`
--

INSERT INTO `disputes` (`dispute_id`, `project_id`, `raised_by_user_id`, `against_user_id`, `milestone_id`, `milestone_item_id`, `dispute_type`, `title`, `if_others_distype`, `dispute_desc`, `dispute_status`, `reason`, `requested_action`, `admin_response`, `created_at`, `resolved_at`) VALUES
(79, 1015, 141, 4, NULL, NULL, 'Delay', NULL, NULL, 'Delayed', 'open', NULL, '', NULL, '2025-12-15 07:49:09', NULL),
(80, 1016, 157, 8, NULL, NULL, 'Delay', NULL, NULL, 'Delayed', 'open', NULL, '', NULL, '2025-12-15 07:49:09', NULL),
(81, 1017, 151, 14, NULL, NULL, 'Delay', NULL, NULL, 'Delayed', 'open', NULL, '', NULL, '2025-12-15 07:49:09', NULL),
(82, 1018, 137, 16, NULL, NULL, 'Delay', NULL, NULL, 'Delayed', 'open', NULL, '', NULL, '2025-12-15 07:49:09', NULL),
(83, 1019, 169, 16, NULL, NULL, 'Delay', NULL, NULL, 'Delayed', 'open', NULL, '', NULL, '2025-12-15 07:49:09', NULL),
(84, 1045, 371, 372, 1556, 2767, 'Payment', NULL, NULL, 'Di nagbabayad', 'under_review', NULL, '', NULL, '2025-12-18 08:22:56', NULL),
(85, 1047, 371, 372, 1557, 2773, 'Halt', NULL, NULL, 'jakahaojana', 'closed', NULL, '', 'Project halted based on this dispute. Halt reason: wwwwwwwwwwwwwwwwwww', '2025-12-18 20:59:09', NULL),
(86, 1048, 371, 372, 1558, 2774, 'Halt', NULL, NULL, 'oskznzlaka', 'open', NULL, '', 'jejemon', '2025-12-18 21:12:39', NULL),
(87, 1049, 371, 372, 1559, 2776, 'Payment', NULL, NULL, 'Di nagbabayad', 'resolved', NULL, '', 'ya pone flooring', '2025-12-19 00:03:35', NULL),
(88, 1056, 380, 379, 1564, 2790, 'Delay', NULL, NULL, 'qqqqqqqqqqqqqqqqqqqqqqqqqqq', 'open', '', '', NULL, '2026-02-28 06:17:02', NULL);

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

--
-- Dumping data for table `dispute_files`
--

INSERT INTO `dispute_files` (`file_id`, `dispute_id`, `storage_path`, `original_name`, `mime_type`, `size`, `uploaded_at`) VALUES
(1, 84, 'disputes/evidence/1766074976_69442a60304b0.jpg', 'Screenshot_20251215_171943.jpg', 'image/jpeg', 353892, '2025-12-18 08:22:57'),
(2, 85, 'disputes/evidence/1766120349_6944db9d7271b.jpg', 'Screenshot_20251219-085607.jpg', 'image/jpeg', 310716, '2025-12-18 20:59:09'),
(3, 86, 'disputes/evidence/1766121159_6944dec7479d9.jpg', 'Screenshot_20251219-085607.jpg', 'image/jpeg', 310716, '2025-12-18 21:12:39'),
(4, 87, 'disputes/evidence/1766131415_694506d7f2230.docx', 'sandbox-of-SE-Revisions-List-Template.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 1985858, '2025-12-19 00:03:36'),
(5, 88, 'disputes/evidence/1772288222_69a2f8de919b7.pdf', 'id.pdf', 'application/pdf', 220855, '2026-02-28 06:17:02');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `item_files`
--

CREATE TABLE `item_files` (
  `file_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `conversation_id` bigint(20) UNSIGNED NOT NULL,
  `from_sender` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'True if sent by conversations.sender_id, false if by receiver_id',
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_flagged` tinyint(1) DEFAULT 0,
  `flag_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `conversation_id`, `from_sender`, `content`, `is_read`, `is_flagged`, `flag_reason`, `created_at`, `updated_at`) VALUES
(177, 1000371, 0, 'ssssssssssssssssssssssssssssssssssssssss', 1, 0, NULL, '2026-02-06 22:56:13', '2026-02-07 03:07:49'),
(178, 1000372, 0, 'hi b', 1, 0, NULL, '2026-02-06 22:56:53', '2026-02-07 03:07:46'),
(179, 1000371, 0, 'ww', 1, 0, NULL, '2026-02-06 22:59:01', '2026-02-06 22:59:01'),
(180, 1000371, 0, 'ww', 1, 0, NULL, '2026-02-06 22:59:05', '2026-02-06 22:59:05'),
(181, 1000371, 0, 'hi baby check mo progress report ko, love you kain ka na', 1, 0, NULL, '2026-02-06 22:59:23', '2026-02-06 22:59:23'),
(182, 1000371, 0, 'hi', 1, 0, NULL, '2026-02-06 23:05:58', '2026-02-06 23:05:58'),
(183, 1000371, 0, 'hello guys', 1, 0, NULL, '2026-02-06 23:31:06', '2026-02-06 23:31:06'),
(184, 1000002, 0, 'hello guys', 1, 0, NULL, '2026-02-06 23:31:07', '2026-02-07 03:21:57'),
(185, 1000371, 0, 'gg', 1, 0, NULL, '2026-02-06 23:53:56', '2026-02-06 23:53:56'),
(186, 1000371, 0, 'what', 1, 0, NULL, '2026-02-06 23:39:17', '2026-02-07 03:07:49'),
(187, 1000371, 0, 'hi', 1, 0, NULL, '2026-02-06 23:52:51', '2026-02-07 03:07:49'),
(188, 1000372, 0, 'bb', 1, 0, NULL, '2026-02-06 23:55:39', '2026-02-07 03:07:46'),
(190, 1000371, 0, 'see this file below girl ugh', 1, 0, NULL, '2026-02-06 23:57:11', '2026-02-07 03:07:49'),
(193, 1000371, 0, 'se', 1, 0, NULL, '2026-02-07 00:10:03', '2026-02-07 03:07:49'),
(196, 1000002, 0, 'Test real-time message from user 2!', 1, 0, NULL, '2026-02-07 00:32:27', '2026-02-07 03:21:57'),
(197, 1000002, 0, 'Test real-time message from user 2!', 1, 0, NULL, '2026-02-07 00:32:48', '2026-02-07 03:21:57'),
(198, 1000371, 0, 'd', 1, 0, NULL, '2026-02-07 00:37:19', '2026-02-07 03:07:49'),
(199, 1000372, 1, 'hi', 1, 0, NULL, '2026-02-07 03:08:19', '2026-02-07 03:08:47'),
(200, 1000372, 1, 'see mo ito', 1, 0, NULL, '2026-02-07 03:08:28', '2026-02-07 03:08:47'),
(201, 1000010, 1, 'baby hi', 1, 0, NULL, '2026-02-07 03:08:43', '2026-02-07 03:08:51'),
(202, 1000010, 1, 'ddd', 1, 0, NULL, '2026-02-07 03:09:09', '2026-02-07 03:09:14'),
(203, 1000372, 1, 'whats that', 1, 0, NULL, '2026-02-07 03:09:24', '2026-02-07 08:26:49'),
(204, 1000002, 1, 'Test from User 1', 1, 0, NULL, '2026-02-07 03:13:07', '2026-02-07 03:13:07'),
(205, 1000002, 0, 'Test from User 2', 1, 0, NULL, '2026-02-07 03:13:07', '2026-02-07 03:21:57'),
(206, 1000002, 1, 'Another test from User 1', 1, 0, NULL, '2026-02-07 03:13:07', '2026-02-07 03:13:07'),
(207, 1000002, 0, 'Another test from User 2', 1, 0, NULL, '2026-02-07 03:13:07', '2026-02-07 03:21:57'),
(208, 1000002, 1, 'hi', 1, 0, NULL, '2026-02-07 03:13:49', '2026-02-07 03:13:49'),
(209, 1000102, 1, 'hiiiiiiiiiiiiiiii', 0, 0, NULL, '2026-02-07 03:19:39', '2026-02-07 03:19:39'),
(210, 1000002, 1, 'Test 1 from User 1', 1, 0, NULL, '2026-02-07 03:21:57', '2026-02-07 03:21:57'),
(211, 1000002, 1, 'Test 2 from User 1', 1, 0, NULL, '2026-02-07 03:21:57', '2026-02-07 03:21:57'),
(212, 1000002, 0, 'Test 1 from User 2', 1, 0, NULL, '2026-02-07 03:21:57', '2026-02-07 03:21:57'),
(213, 1000002, 1, 'Test 1 from User 1', 0, 0, NULL, '2026-02-07 03:22:50', '2026-02-07 03:22:50'),
(214, 1000002, 1, 'Test 2 from User 1', 0, 0, NULL, '2026-02-07 03:22:50', '2026-02-07 03:22:50'),
(215, 1000002, 0, 'Test 1 from User 2', 1, 0, NULL, '2026-02-07 03:22:50', '2026-02-07 03:22:50'),
(216, 1000103, 1, 'test', 0, 0, NULL, '2026-02-07 03:26:49', '2026-02-07 03:26:49'),
(217, 1000103, 1, 'see ,', 0, 0, NULL, '2026-02-07 03:27:07', '2026-02-07 03:27:07'),
(218, 1000372, 1, 'hi', 1, 0, NULL, '2026-02-07 05:47:27', '2026-02-07 08:26:49'),
(219, 1000372, 1, 'hi', 1, 0, NULL, '2026-02-07 08:02:00', '2026-02-07 08:26:49'),
(220, 1000371, 0, 'hmm', 1, 0, NULL, '2026-02-07 08:28:08', '2026-02-07 08:38:39'),
(221, 1000010, 0, 'hmm', 1, 0, NULL, '2026-02-07 08:28:29', '2026-02-07 08:39:02'),
(222, 1000371, 1, 'hi', 1, 0, NULL, '2026-02-07 08:38:43', '2026-02-07 17:32:37'),
(223, 1000372, 1, 'hi gutl', 1, 0, NULL, '2026-02-07 08:38:51', '2026-02-07 19:30:24'),
(224, 1000371, 1, 'hello', 1, 0, NULL, '2026-02-07 17:32:53', '2026-02-07 17:33:07'),
(225, 1000371, 1, 'hello', 1, 0, NULL, '2026-02-07 17:32:56', '2026-02-07 17:33:07'),
(226, 1000371, 1, 'hi', 1, 0, NULL, '2026-02-07 17:33:11', '2026-02-07 17:38:44'),
(227, 1000371, 1, 'hello', 1, 0, NULL, '2026-02-07 17:38:58', '2026-02-07 17:39:21'),
(228, 1000371, 1, 'hi', 1, 0, NULL, '2026-02-07 17:40:09', '2026-02-07 17:40:17'),
(229, 1000371, 1, 'hi', 1, 0, NULL, '2026-02-07 17:52:23', '2026-02-07 17:52:30'),
(230, 1000371, 1, 'hi', 1, 0, NULL, '2026-02-07 17:52:35', '2026-02-07 17:52:41'),
(231, 1000371, 1, 'hello\\', 1, 0, NULL, '2026-02-07 17:58:51', '2026-02-07 17:59:03'),
(232, 1000371, 1, 'nigger', 1, 0, NULL, '2026-02-07 18:57:26', '2026-02-07 18:57:32'),
(233, 1000371, 1, 'hi muah', 1, 0, NULL, '2026-02-07 19:02:22', '2026-02-07 19:02:24'),
(234, 1000371, 1, 'gi', 1, 0, NULL, '2026-02-07 19:02:28', '2026-02-07 19:02:30'),
(235, 1000371, 1, 'i have chika', 1, 0, NULL, '2026-02-07 19:03:54', '2026-02-07 19:03:58'),
(236, 1000371, 1, 'i have chila', 1, 0, NULL, '2026-02-07 19:15:11', '2026-02-07 19:15:54'),
(237, 1000371, 1, 'i have chila', 1, 0, NULL, '2026-02-07 19:15:14', '2026-02-07 19:15:54'),
(238, 1000371, 0, 'huy', 1, 0, NULL, '2026-02-07 19:20:32', '2026-02-07 19:20:38'),
(239, 1000371, 1, 'how are you', 1, 0, NULL, '2026-02-07 19:20:45', '2026-02-07 19:20:48'),
(240, 1000371, 0, 'im good', 1, 0, NULL, '2026-02-07 19:20:51', '2026-02-07 19:20:54'),
(241, 1000371, 0, 'see mo to', 1, 0, NULL, '2026-02-07 19:21:10', '2026-02-07 19:21:13'),
(242, 1000371, 1, 'wow okay', 1, 0, NULL, '2026-02-07 19:21:22', '2026-02-07 19:21:25'),
(243, 2000371, 1, 'hi po free po ba kayo ngayon', 0, 0, NULL, '2026-02-07 19:29:05', '2026-02-07 19:29:05'),
(244, 371000372, 1, 'hi po free po ba kayo ngayon', 1, 0, NULL, '2026-02-07 19:29:05', '2026-02-07 19:30:25'),
(245, 371000372, 0, 'nuyan', 1, 0, NULL, '2026-02-07 19:30:32', '2026-02-07 19:30:37'),
(246, 371000372, 0, 'bb', 1, 0, NULL, '2026-02-07 19:33:43', '2026-02-07 19:33:49'),
(247, 371000372, 1, 'ano yan baby', 1, 0, NULL, '2026-02-07 19:33:58', '2026-02-07 19:34:09'),
(248, 371000372, 1, '', 1, 0, NULL, '2026-02-07 19:34:05', '2026-02-07 19:34:09'),
(249, 371000372, 0, 'hi beh', 1, 0, NULL, '2026-02-07 23:29:34', '2026-02-07 23:29:51'),
(250, 371000372, 0, 'hi', 1, 0, NULL, '2026-02-07 23:30:17', '2026-02-07 23:34:30'),
(251, 1000371, 0, 'gg', 1, 0, NULL, '2026-02-07 23:30:53', '2026-02-07 23:30:59'),
(252, 371000372, 0, 'hi', 1, 0, NULL, '2026-02-07 23:34:25', '2026-02-07 23:34:30'),
(253, 371000372, 0, 'kumain ka na?', 1, 0, NULL, '2026-02-07 23:35:05', '2026-02-07 23:35:15'),
(254, 371000372, 0, 'kamusta', 1, 0, NULL, '2026-02-07 23:35:23', '2026-02-07 23:35:30'),
(255, 1000371, 1, 'te', 1, 0, NULL, '2026-02-07 23:35:36', '2026-02-07 23:35:46'),
(256, 1000371, 0, 'dd', 1, 0, NULL, '2026-02-07 23:36:44', '2026-02-07 23:36:50'),
(257, 1000371, 1, 'hi', 1, 0, NULL, '2026-02-07 23:44:00', '2026-02-07 23:44:09'),
(258, 1000371, 0, 'teh', 1, 0, NULL, '2026-02-07 23:44:11', '2026-02-07 23:45:56'),
(259, 371000372, 0, 'hmm', 1, 0, NULL, '2026-02-07 23:44:28', '2026-02-07 23:44:32'),
(260, 371000372, 1, 'hmmm', 1, 0, NULL, '2026-02-07 23:44:34', '2026-02-07 23:44:46'),
(261, 371000372, 0, 'hhhhh', 1, 0, NULL, '2026-02-07 23:44:50', '2026-02-07 23:44:55'),
(262, 1000372, 1, 'te', 1, 0, NULL, '2026-02-07 23:45:07', '2026-02-07 23:45:51'),
(263, 1000372, 0, 'sss', 1, 0, NULL, '2026-02-07 23:45:53', '2026-02-07 23:45:58'),
(264, 1000372, 1, 'hi', 1, 0, NULL, '2026-02-07 23:52:20', '2026-02-07 23:52:44'),
(265, 1000372, 0, 's', 1, 0, NULL, '2026-02-07 23:52:47', '2026-02-07 23:53:01'),
(266, 371000372, 0, 'ddd', 1, 0, NULL, '2026-02-07 23:53:10', '2026-02-07 23:53:15'),
(267, 1000372, 1, 'hi', 1, 0, NULL, '2026-02-07 23:59:07', '2026-02-07 23:59:32'),
(268, 1000371, 1, 'jjjjj', 1, 0, NULL, '2026-02-07 23:59:14', '2026-02-08 00:07:16'),
(269, 371000372, 1, 'sss', 1, 0, NULL, '2026-02-07 23:59:24', '2026-02-08 00:06:32'),
(270, 1000372, 0, 'qq', 1, 0, NULL, '2026-02-07 23:59:35', '2026-02-08 00:00:25'),
(271, 371000372, 1, 'hello', 1, 0, NULL, '2026-02-07 23:59:44', '2026-02-08 00:06:32'),
(272, 1000372, 0, 'sss', 1, 0, NULL, '2026-02-08 00:00:17', '2026-02-08 00:00:25'),
(273, 371000372, 1, 'yow', 1, 0, NULL, '2026-02-08 00:06:28', '2026-02-08 00:06:32'),
(274, 371000372, 0, 'hi', 1, 0, NULL, '2026-02-08 00:06:34', '2026-02-08 00:08:20'),
(275, 1000372, 1, 'sss', 1, 0, NULL, '2026-02-08 00:06:50', '2026-02-08 00:07:11'),
(276, 1000371, 1, 'sss', 1, 0, NULL, '2026-02-08 00:06:55', '2026-02-08 00:07:16'),
(277, 1000371, 1, 'good morning baby', 1, 0, NULL, '2026-02-08 00:08:34', '2026-02-08 00:09:49'),
(278, 1000372, 1, 'good morning baby', 1, 0, NULL, '2026-02-08 00:08:37', '2026-02-08 00:09:46'),
(279, 1000372, 1, 'hi po block kita', 1, 0, NULL, '2026-02-08 00:16:13', '2026-02-08 00:16:21'),
(280, 1000371, 1, 'hi po block kita nigga', 1, 0, NULL, '2026-02-08 00:16:16', '2026-02-08 00:16:23'),
(281, 1000371, 0, 'okay po', 1, 0, NULL, '2026-02-08 00:16:27', '2026-02-08 00:16:29'),
(282, 371000372, 0, 'free kaba bukas?', 1, 0, NULL, '2026-02-08 00:16:42', '2026-02-08 00:16:46'),
(283, 371000372, 0, 'hi ngga]\\', 1, 0, NULL, '2026-02-08 00:20:32', '2026-02-08 00:20:34'),
(284, 371000372, 1, 'okay ka lang?4', 1, 0, NULL, '2026-02-08 00:23:40', '2026-02-08 00:23:44'),
(285, 371000372, 0, 'yea okay lang naman po', 1, 0, NULL, '2026-02-08 00:23:49', '2026-02-08 00:23:50'),
(286, 1000372, 0, 'block', 1, 0, NULL, '2026-02-08 00:29:43', '2026-02-08 00:30:40'),
(287, 1000372, 1, 'hi', 1, 0, NULL, '2026-02-08 03:49:01', '2026-02-08 03:49:07'),
(288, 1000372, 0, 'hmm', 1, 0, NULL, '2026-02-08 03:49:09', '2026-02-08 03:49:11'),
(289, 1000372, 0, 'I can pay via gcash', 1, 1, 'System: Suspicious Keyword Detected', '2026-02-08 03:49:29', '2026-02-08 03:49:32'),
(290, 1000372, 0, 'facebook', 1, 0, NULL, '2026-02-08 03:49:48', '2026-02-08 03:49:50'),
(291, 371000372, 1, 'nigga', 1, 0, NULL, '2026-02-08 03:54:56', '2026-02-08 03:55:14'),
(292, 1000372, 1, 'gcash', 1, 1, 'System: Suspicious Keyword Detected', '2026-02-08 03:57:37', '2026-02-08 03:59:33'),
(293, 1000372, 0, 'facebook', 1, 0, NULL, '2026-02-08 03:59:59', '2026-02-08 04:00:04'),
(294, 371000372, 1, 'negro', 1, 0, NULL, '2026-02-08 04:02:31', '2026-02-08 04:02:41'),
(295, 371000372, 0, 'musta', 1, 0, NULL, '2026-02-08 04:07:10', '2026-02-08 04:07:14'),
(296, 371000372, 0, 'negro', 1, 0, NULL, '2026-02-08 04:10:01', '2026-02-08 04:10:05'),
(297, 352000372, 1, 'girl nigga', 0, 0, NULL, '2026-02-08 04:39:09', '2026-02-08 04:39:09'),
(298, 2000371, 1, 'nigggggggggggga', 0, 0, NULL, '2026-02-08 05:05:58', '2026-02-08 05:05:58'),
(299, 2000371, 1, 'nigga', 0, 0, NULL, '2026-02-08 05:06:04', '2026-02-08 05:06:04'),
(300, 371000372, 0, 'sorry beh', 1, 0, NULL, '2026-02-08 05:31:06', '2026-02-08 05:31:13'),
(301, 1000371, 0, 'ss', 1, 0, NULL, '2026-02-08 06:24:23', '2026-02-08 07:32:28'),
(302, 1000371, 1, 's', 1, 0, NULL, '2026-02-08 06:32:34', '2026-02-08 06:51:36'),
(303, 371000372, 1, 'hi', 1, 0, NULL, '2026-02-08 06:38:54', '2026-02-08 06:39:01'),
(304, 371000372, 0, 'sss', 1, 0, NULL, '2026-02-08 06:39:04', '2026-02-08 06:39:08'),
(305, 371000372, 1, 'nigga', 1, 0, NULL, '2026-02-08 06:39:11', '2026-02-08 06:39:15'),
(306, 103000371, 1, 'sss', 0, 0, NULL, '2026-02-08 06:44:37', '2026-02-08 06:44:37'),
(307, 103000371, 1, 'nigga', 0, 0, NULL, '2026-02-08 06:44:53', '2026-02-08 06:44:53'),
(308, 103000371, 1, 'nigga', 0, 0, NULL, '2026-02-08 06:45:29', '2026-02-08 06:45:29'),
(309, 1000371, 1, '', 1, 0, NULL, '2026-02-08 06:51:25', '2026-02-08 06:51:36'),
(310, 371000372, 0, 'nigga', 1, 0, NULL, '2026-02-08 07:22:01', '2026-02-08 07:23:50'),
(311, 371000372, 1, 'test', 1, 0, NULL, '2026-02-08 07:23:55', '2026-02-08 07:23:59'),
(312, 1000372, 1, 'www', 1, 0, NULL, '2026-02-08 07:47:02', '2026-02-08 07:47:10'),
(313, 3000372, 1, 'sss', 0, 0, NULL, '2026-02-08 07:47:21', '2026-02-08 07:47:21'),
(314, 371000372, 1, '', 1, 0, NULL, '2026-02-08 07:57:42', '2026-02-08 07:57:47'),
(315, 371000372, 0, 'hello', 1, 0, NULL, '2026-02-08 23:21:08', '2026-02-08 23:21:31'),
(316, 371000372, 0, 'sss', 1, 0, NULL, '2026-02-08 23:21:40', '2026-02-08 23:26:59'),
(317, 371000372, 0, 'hi', 1, 0, NULL, '2026-02-08 23:26:51', '2026-02-08 23:26:59'),
(318, 371000372, 1, 'dd', 1, 0, NULL, '2026-02-08 23:27:02', '2026-02-08 23:27:27'),
(319, 371000372, 1, 'hi', 1, 0, NULL, '2026-02-08 23:28:55', '2026-02-08 23:36:40'),
(320, 371000372, 1, 'hi', 1, 0, NULL, '2026-02-08 23:36:36', '2026-02-08 23:36:40'),
(321, 371000372, 0, 'teh', 1, 0, NULL, '2026-02-08 23:36:42', '2026-02-08 23:36:44'),
(322, 371000372, 1, 'hi', 1, 0, NULL, '2026-02-08 23:43:39', '2026-02-08 23:43:42'),
(323, 371000372, 0, 's', 1, 0, NULL, '2026-02-09 00:27:51', '2026-02-09 00:27:54'),
(324, 1000371, 0, 'd', 1, 0, NULL, '2026-02-09 00:52:14', '2026-02-09 00:57:27'),
(325, 1000372, 0, 's', 1, 0, NULL, '2026-02-09 00:52:24', '2026-02-09 00:57:30'),
(326, 1000372, 0, 'w', 1, 0, NULL, '2026-02-09 00:52:45', '2026-02-09 00:57:30'),
(327, 1000372, 0, 'wwwwww', 1, 0, NULL, '2026-02-09 00:52:58', '2026-02-09 00:57:30'),
(328, 1000372, 0, 'ww', 1, 0, NULL, '2026-02-09 00:53:05', '2026-02-09 00:57:30'),
(329, 371000372, 1, 'hi', 1, 0, NULL, '2026-02-12 20:49:55', '2026-02-12 20:50:11'),
(330, 371000372, 1, 'hi', 1, 0, NULL, '2026-02-12 20:49:58', '2026-02-12 20:50:11'),
(331, 371000372, 1, 'hi', 1, 0, NULL, '2026-02-12 20:50:00', '2026-02-12 20:50:11'),
(332, 371000372, 0, 'nigger', 1, 1, 'System: Suspicious Keyword Detected', '2026-02-12 20:51:02', '2026-02-12 20:51:04'),
(333, 371000372, 0, 'n i g g a', 1, 0, NULL, '2026-02-12 20:51:18', '2026-02-12 20:51:20'),
(334, 371000372, 0, 'fuck you', 1, 1, 'System: Suspicious Keyword Detected', '2026-02-12 20:51:24', '2026-02-12 20:51:26'),
(335, 371000372, 0, 'buysit', 1, 0, NULL, '2026-02-12 20:51:45', '2026-02-12 20:51:47');

-- --------------------------------------------------------

--
-- Table structure for table `message_attachments`
--

CREATE TABLE `message_attachments` (
  `attachment_id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message_attachments`
--

INSERT INTO `message_attachments` (`attachment_id`, `message_id`, `file_path`, `file_name`, `file_type`, `created_at`) VALUES
(1, 179, 'messages/CVBM9jTmvTPQqrJLo0P9f1RIdhJf6Ymf3tboDJbC.pdf', 'seminar_proposal-Google-Docs.pdf', 'application/pdf', '2026-02-07 06:59:03'),
(2, 180, 'messages/tIbZRP5BhBfBYuW6UZYNjbqny8ffXWKBHh1Sw1av.pdf', 'seminar_proposal-Google-Docs.pdf', 'application/pdf', '2026-02-07 06:59:05'),
(3, 193, 'messages/5D12SL9bZstw2wBGiguLmvr4OGvKYz05nVKa9ShT.docx', 'JIMENEZ, SHANE HART D..docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '2026-02-07 08:10:03'),
(4, 200, 'messages/5BmJ2FAxNwVjzUSzlnkkEMN7ms415hoxBoV3LTxS.docx', 'JIMENEZ, SHANE HART D..docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '2026-02-07 11:08:28'),
(5, 217, 'messages/f3kvPLX6CcXtDpAmQ77kYn4hRyJNWscm3oO37P57.docx', 'JIMENEZ, SHANE HART D..docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '2026-02-07 11:27:07'),
(6, 241, 'messages/u5v23MLTVzuFOaVjrpmStAZ1IvrAutEbtfesaCF0.docx', 'JIMENEZ, SHANE HART D..docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '2026-02-08 03:21:11'),
(7, 246, 'messages/ypyI3pkZkvu6VQtu9idy6v3X6P6eSGlibrCQsD3R.pdf', 'epic.pdf', 'application/pdf', '2026-02-08 03:33:43'),
(8, 248, 'messages/mXFY5qs6wlYturTaNNxDobHlp9vzETNwiK5LdwTO.docx', 'JIMENEZ, SHANE HART D..docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '2026-02-08 03:34:05'),
(9, 309, 'messages/L47HAt8H1e8xPNEEU8MG5f0z0X5mF90FQZokB97v.jpg', '9066ae1f-a8e6-4c83-b936-c46ba7e5fd64.jpeg', 'image/jpeg', '2026-02-08 14:51:26'),
(10, 314, 'messages/1f1XraBKAeJtpNtExDhUzerIRXSosrWJSEFjngbk.docx', 'JIMENEZ, SHANE HART D..docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '2026-02-08 15:57:43');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_02_27_051146_add_start_date_to_milestone_items_table', 1),
(2, '2026_02_27_120000_create_milestone_item_updates_and_make_proposed_end_date_nullable', 2),
(3, '2026_02_28_000000_create_subscription_plans_table', 2),
(4, '2026_02_28_144747_normalize_platform_payments_table', 3),
(5, '2026_03_01_052140_add_duration_days_to_subscription_plans_table', 4),
(6, '2026_02_28_080946_create_jobs_table', 5),
(7, '2026_02_28_081825_create_failed_jobs_table', 5),
(8, '2026_03_01_115415_add_feed_ranking_indexes', 5);

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
  `milestone_status` enum('not_started','in_progress','rejected','delayed','cancelled','deleted','completed') DEFAULT 'not_started',
  `previous_status` varchar(50) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `is_deleted` tinyint(1) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `setup_status` enum('submitted','rejected','approved') NOT NULL DEFAULT 'submitted',
  `setup_rej_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `milestones`
--

INSERT INTO `milestones` (`milestone_id`, `project_id`, `contractor_id`, `plan_id`, `milestone_name`, `milestone_description`, `milestone_status`, `previous_status`, `start_date`, `end_date`, `is_deleted`, `reason`, `setup_status`, `setup_rej_reason`, `created_at`, `updated_at`) VALUES
(1466, 1015, 1690, 890, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1467, 1015, 1690, 890, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1468, 1015, 1690, 890, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1469, 1016, 1694, 891, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1470, 1016, 1694, 891, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1471, 1016, 1694, 891, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1472, 1017, 1700, 892, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1473, 1017, 1700, 892, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1474, 1017, 1700, 892, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1475, 1018, 1702, 893, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1476, 1018, 1702, 893, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1477, 1018, 1702, 893, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1478, 1019, 1702, 894, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1479, 1019, 1702, 894, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1480, 1019, 1702, 894, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1481, 1020, 1707, 895, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1482, 1020, 1707, 895, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1483, 1020, 1707, 895, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1484, 1021, 1715, 896, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1485, 1021, 1715, 896, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1486, 1021, 1715, 896, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1487, 1022, 1717, 897, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1488, 1022, 1717, 897, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1489, 1022, 1717, 897, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1490, 1023, 1717, 898, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1491, 1023, 1717, 898, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1492, 1023, 1717, 898, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1493, 1024, 1718, 899, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1494, 1024, 1718, 899, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1495, 1024, 1718, 899, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1496, 1025, 1718, 900, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1497, 1025, 1718, 900, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1498, 1025, 1718, 900, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1499, 1026, 1725, 901, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1500, 1026, 1725, 901, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1501, 1026, 1725, 901, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1502, 1027, 1727, 902, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2026-02-06 12:18:00'),
(1503, 1027, 1727, 902, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2026-02-06 12:18:00'),
(1504, 1027, 1727, 902, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2026-02-06 12:18:00'),
(1505, 1028, 1730, 903, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1506, 1028, 1730, 903, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1507, 1028, 1730, 903, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1508, 1029, 1733, 904, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1509, 1029, 1733, 904, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1510, 1029, 1733, 904, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1511, 1030, 1735, 905, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1512, 1030, 1735, 905, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1513, 1030, 1735, 905, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1514, 1031, 1739, 906, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1515, 1031, 1739, 906, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1516, 1031, 1739, 906, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1517, 1032, 1740, 907, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1518, 1032, 1740, 907, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1519, 1032, 1740, 907, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1520, 1033, 1745, 908, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1521, 1033, 1745, 908, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1522, 1033, 1745, 908, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1523, 1034, 1755, 909, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1524, 1034, 1755, 909, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1525, 1034, 1755, 909, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1526, 1035, 1763, 910, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1527, 1035, 1763, 910, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1528, 1035, 1763, 910, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1529, 1036, 1764, 911, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1530, 1036, 1764, 911, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1531, 1036, 1764, 911, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1532, 1037, 1768, 912, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1533, 1037, 1768, 912, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1534, 1037, 1768, 912, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1535, 1038, 1773, 913, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1536, 1038, 1773, 913, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1537, 1038, 1773, 913, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1538, 1039, 1777, 914, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1539, 1039, 1777, 914, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1540, 1039, 1777, 914, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1541, 1040, 1787, 915, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1542, 1040, 1787, 915, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1543, 1040, 1787, 915, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1544, 1041, 1789, 916, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1545, 1041, 1789, 916, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1546, 1041, 1789, 916, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1547, 1042, 1792, 917, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1548, 1042, 1792, 917, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1549, 1042, 1792, 917, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1550, 1043, 1794, 918, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1551, 1043, 1794, 918, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1552, 1043, 1794, 918, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(1553, 1044, 1798, 919, 'Milestone 1', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2026-02-06 12:20:18'),
(1554, 1044, 1798, 919, 'Milestone 2', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2026-02-06 12:20:18'),
(1555, 1044, 1798, 919, 'Milestone 3', 'Desc', 'in_progress', NULL, '2025-12-15 15:49:09', '2026-01-14 15:49:09', NULL, NULL, 'approved', NULL, '2025-12-15 07:49:09', '2026-02-06 12:20:18'),
(1556, 1045, 1809, 920, 'Test Project Final', 'Test Project Final', 'not_started', NULL, '2025-12-18 00:00:00', '2027-12-18 23:59:59', NULL, NULL, 'approved', NULL, '2025-12-17 14:57:31', '2026-02-05 17:50:09'),
(1557, 1047, 1808, 921, 'Testz', 'Testz', 'not_started', NULL, '2025-12-19 00:00:00', '2027-12-21 23:59:59', NULL, NULL, 'approved', NULL, '2025-12-18 19:51:47', '2026-02-05 16:53:59'),
(1558, 1048, 1809, 922, 'Noche buena', 'Noche buena', 'in_progress', NULL, '2025-12-19 00:00:00', '2026-12-19 23:59:59', NULL, NULL, 'approved', NULL, '2025-12-18 21:10:50', '2026-02-20 14:59:51'),
(1559, 1049, 1809, 923, 'Construction Project', 'Construction Project', 'not_started', NULL, '2025-12-19 00:00:00', '2026-12-19 23:59:59', NULL, NULL, 'approved', NULL, '2025-12-18 23:59:41', '2025-12-19 00:00:35'),
(1560, 1054, 1809, 924, 'Project Construction for Residential Area', 'Project Construction for Residential Area', 'in_progress', NULL, '2026-01-25 00:00:00', '2028-01-25 23:59:59', NULL, NULL, 'approved', NULL, '2026-01-25 00:20:09', '2026-02-20 14:59:33'),
(1563, 1047, 1810, 927, 'Proyekto ng bayan', 'Proyekto ng bayan', 'not_started', NULL, '2026-02-22 00:00:00', '2026-02-28 23:59:59', NULL, NULL, 'approved', NULL, '2026-02-22 07:51:59', '2026-02-22 08:08:23'),
(1564, 1056, 1810, 928, 'Project Batumbakal', 'Project Batumbakal', 'not_started', NULL, '2026-02-23 00:00:00', '2026-03-07 23:59:59', NULL, NULL, 'approved', NULL, '2026-02-23 00:12:07', '2026-02-25 07:15:26');

-- --------------------------------------------------------

--
-- Table structure for table `milestone_date_histories`
--

CREATE TABLE `milestone_date_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL COMMENT 'FK to milestone_items.item_id',
  `previous_date` datetime NOT NULL COMMENT 'The date_to_finish before extension',
  `new_date` datetime NOT NULL COMMENT 'The date_to_finish after extension',
  `extension_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK to project_updates.extension_id',
  `changed_by` int(10) UNSIGNED NOT NULL COMMENT 'user_id who triggered the change',
  `changed_at` datetime NOT NULL COMMENT 'When the change was applied',
  `change_reason` varchar(500) DEFAULT NULL COMMENT 'e.g. "Project update #2 approved"',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `milestone_date_histories`
--

INSERT INTO `milestone_date_histories` (`id`, `item_id`, `previous_date`, `new_date`, `extension_id`, `changed_by`, `changed_at`, `change_reason`, `created_at`, `updated_at`) VALUES
(1, 2790, '2026-02-18 23:59:59', '2026-02-25 23:59:59', 2, 379, '2026-02-25 15:15:26', 'Project update #2 approved (retroactive)', '2026-02-26 03:26:12', '2026-02-26 03:26:12'),
(2, 2791, '2026-02-28 23:59:59', '2026-03-07 23:59:59', 2, 379, '2026-02-25 15:15:26', 'Project update #2 approved (retroactive)', '2026-02-26 03:26:12', '2026-02-26 03:26:12'),
(3, 2792, '2026-02-28 23:59:59', '2026-03-07 23:59:59', 2, 379, '2026-02-25 15:15:26', 'Project update #2 approved (retroactive)', '2026-02-26 03:26:12', '2026-02-26 03:26:12'),
(4, 2790, '2026-02-25 00:00:00', '2026-02-25 00:00:00', 4, 379, '2026-02-27 12:12:04', 'project_update_approved', '2026-02-27 04:12:04', '2026-02-27 04:12:04'),
(5, 2791, '2026-03-02 00:00:00', '2026-03-02 00:00:00', 4, 379, '2026-02-27 12:12:04', 'project_update_approved', '2026-02-27 04:12:04', '2026-02-27 04:12:04');

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
  `adjusted_cost` decimal(12,2) DEFAULT NULL COMMENT 'Required amount after underpayment carry-forward. NULL = no adjustment (use milestone_item_cost).',
  `carry_forward_amount` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Shortfall amount carried forward FROM the previous item.',
  `item_status` enum('not_started','in_progress','delayed','completed','cancelled','halt','deleted') NOT NULL DEFAULT 'not_started',
  `start_date` datetime DEFAULT NULL,
  `previous_status` varchar(50) DEFAULT NULL,
  `date_to_finish` datetime NOT NULL,
  `original_date_to_finish` datetime DEFAULT NULL COMMENT 'Preserved first deadline before any extension',
  `was_extended` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Quick flag: true if date_to_finish was ever shifted by an extension',
  `extension_count` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Number of times this item was extended',
  `settlement_due_date` date DEFAULT NULL COMMENT 'Payment settlement deadline set by contractor',
  `extension_date` date DEFAULT NULL COMMENT 'Optional extended deadline granted by contractor',
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `milestone_items`
--

INSERT INTO `milestone_items` (`item_id`, `milestone_id`, `sequence_order`, `percentage_progress`, `milestone_item_title`, `milestone_item_description`, `milestone_item_cost`, `adjusted_cost`, `carry_forward_amount`, `item_status`, `start_date`, `previous_status`, `date_to_finish`, `original_date_to_finish`, `was_extended`, `extension_count`, `settlement_due_date`, `extension_date`, `updated_at`) VALUES
(2547, 1466, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2548, 1467, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2549, 1468, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2550, 1469, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2551, 1470, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2552, 1471, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2553, 1472, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2554, 1473, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2555, 1474, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2556, 1475, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2557, 1476, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2558, 1477, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2559, 1478, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2560, 1479, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2561, 1480, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2562, 1481, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2563, 1482, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2564, 1483, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2565, 1484, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2566, 1485, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2567, 1486, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2568, 1487, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2569, 1488, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2570, 1489, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2571, 1490, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2572, 1491, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2573, 1492, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2574, 1493, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2575, 1494, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2576, 1495, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2577, 1496, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2578, 1497, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2579, 1498, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2580, 1499, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2581, 1500, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2582, 1501, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2583, 1502, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2584, 1503, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2585, 1504, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2586, 1505, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2587, 1506, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2588, 1507, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2589, 1508, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2590, 1509, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2591, 1510, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2592, 1511, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2593, 1512, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2594, 1513, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2595, 1514, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2596, 1515, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2597, 1516, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2598, 1517, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2599, 1518, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2600, 1519, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2601, 1520, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2602, 1521, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2603, 1522, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2604, 1523, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2605, 1524, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2606, 1525, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2607, 1526, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2608, 1527, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2609, 1528, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2610, 1529, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2611, 1530, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2612, 1531, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2613, 1532, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2614, 1533, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2615, 1534, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2616, 1535, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2617, 1536, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2618, 1537, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2619, 1538, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2620, 1539, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2621, 1540, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2622, 1541, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2623, 1542, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2624, 1543, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2625, 1544, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2626, 1545, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2627, 1546, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2628, 1547, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2629, 1548, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2630, 1549, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2631, 1550, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2632, 1551, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2633, 1552, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2634, 1553, 1, 40.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2635, 1554, 1, 100.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2636, 1555, 1, 50.00, 'Primary Task', 'Desc', 25000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-20 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2674, 1466, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2675, 1468, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2676, 1469, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2677, 1471, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2678, 1472, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2679, 1474, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2680, 1475, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2681, 1477, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2682, 1478, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2683, 1480, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2684, 1481, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2685, 1483, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2686, 1484, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2687, 1486, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2688, 1487, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2689, 1489, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2690, 1490, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2691, 1492, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2692, 1493, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2693, 1495, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2694, 1496, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2695, 1498, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2696, 1499, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2697, 1501, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2698, 1502, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2699, 1504, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2700, 1505, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2701, 1507, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2702, 1508, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2703, 1510, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2704, 1511, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2705, 1513, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2706, 1514, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2707, 1516, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2708, 1517, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2709, 1519, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2710, 1520, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2711, 1522, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2712, 1523, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2713, 1525, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2714, 1526, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2715, 1528, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2716, 1529, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2717, 1531, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2718, 1532, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2719, 1534, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2720, 1535, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2721, 1537, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2722, 1538, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2723, 1540, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2724, 1541, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2725, 1543, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2726, 1544, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2727, 1546, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2728, 1547, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2729, 1549, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2730, 1550, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2731, 1552, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2732, 1553, 2, 30.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2733, 1555, 2, 50.00, 'Secondary Task', 'Desc', 15000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-25 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2737, 1466, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2738, 1469, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2739, 1472, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2740, 1475, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2741, 1478, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2742, 1481, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2743, 1484, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2744, 1487, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2745, 1490, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2746, 1493, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2747, 1496, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2748, 1499, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2749, 1502, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2750, 1505, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2751, 1508, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2752, 1511, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2753, 1514, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2754, 1517, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2755, 1520, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2756, 1523, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2757, 1526, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2758, 1529, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2759, 1532, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2760, 1535, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2761, 1538, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2762, 1541, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2763, 1544, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2764, 1547, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2765, 1550, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2766, 1553, 3, 30.00, 'Final Task', 'Desc', 10000.00, NULL, 0.00, 'not_started', NULL, NULL, '2025-12-30 15:49:09', NULL, 0, 0, NULL, NULL, NULL),
(2767, 1556, 1, 10.00, 'Phase 1', 'Phase 1 Description', 4750000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-01-31 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2768, 1556, 2, 10.00, 'Phase 2', 'Phase 2 Description', 4750000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-02-28 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2769, 1556, 3, 30.00, 'Phase 3', 'Phase 3 Description', 14250000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-03-28 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2770, 1556, 4, 30.00, 'Phase 4', 'Phase 4 Description', 14250000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-04-30 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2771, 1556, 5, 20.00, 'Phase 5', 'Phase 5 Description', 9500000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-05-29 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2772, 1557, 1, 80.00, 'item tite', 'jakananaaad', 34000000.00, NULL, 0.00, 'completed', NULL, 'completed', '2025-12-31 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(2773, 1557, 2, 20.00, 'haianaja', 'vskakaban', 8500000.00, NULL, 0.00, 'not_started', NULL, 'in_progress', '2027-12-14 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2774, 1558, 1, 50.00, '1st', 'Hahsshha', 100000000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-05-21 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2775, 1558, 2, 50.00, '2nd', 'Bzbabsbs', 100000000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-12-19 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2776, 1559, 1, 50.00, 'PHASE 1', 'PHASE 1 DESC', 27495000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-03-31 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2777, 1559, 2, 50.00, 'PHASE 2', 'PHASE 2 DESC', 27495000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-02-26 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2778, 1560, 1, 30.00, 'Foundation and Framework', 'it is what it is', 6000000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-04-17 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2779, 1560, 2, 50.00, 'Madami gagawin', 'Basta madami gagawin', 12000000.00, NULL, 0.00, 'completed', NULL, NULL, '2027-01-30 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2780, 1560, 3, 10.00, 'Tapos na to by this time', 'yes', 2000000.00, NULL, 0.00, 'completed', NULL, NULL, '2027-12-31 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2781, 1560, 4, 10.00, 'eeeeeeeeee', 'eeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', 1.00, NULL, 0.00, 'not_started', NULL, NULL, '2028-01-18 22:53:08', NULL, 0, 0, NULL, NULL, NULL),
(2786, 1563, 1, 50.00, 'giobgy', 'ginvf', 3000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-02-25 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2787, 1563, 2, 50.00, 'dyondsuig', '', 3000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-02-28 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2790, 1564, 1, 33.33, 'Foundations', 'Foundation ngani', 20000000.00, NULL, 0.00, 'in_progress', '2026-02-23 00:00:00', NULL, '2026-02-25 00:00:00', '2026-02-18 23:59:59', 1, 1, '2026-02-25', NULL, '2026-02-27 12:12:04'),
(2791, 1564, 2, 35.00, 'Doners', 'downers', 20000000.00, 21000000.00, 1000000.00, 'in_progress', '2026-02-26 00:00:00', NULL, '2026-03-02 00:00:00', '2026-02-28 23:59:59', 1, 1, '2026-03-02', NULL, '2026-02-27 12:12:04'),
(2792, 1564, 3, 31.67, 'extension', 'hdkdykkydkhdkhd', 19000000.00, NULL, 0.00, 'not_started', '2026-03-03 00:00:00', NULL, '2026-03-07 23:59:59', '2026-02-28 23:59:59', 1, 1, NULL, NULL, '2026-02-27 12:12:04');

-- --------------------------------------------------------

--
-- Table structure for table `milestone_item_updates`
--

CREATE TABLE `milestone_item_updates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `milestone_item_id` int(10) UNSIGNED NOT NULL COMMENT 'FK → milestone_items.item_id',
  `project_update_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK → project_updates.extension_id (nullable for standalone)',
  `proposed_start_date` date DEFAULT NULL,
  `proposed_end_date` date DEFAULT NULL,
  `proposed_cost` decimal(12,2) DEFAULT NULL,
  `proposed_title` varchar(255) DEFAULT NULL,
  `previous_start_date` date DEFAULT NULL,
  `previous_end_date` date DEFAULT NULL,
  `previous_cost` decimal(12,2) DEFAULT NULL,
  `previous_title` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `milestone_item_updates`
--

INSERT INTO `milestone_item_updates` (`id`, `milestone_item_id`, `project_update_id`, `proposed_start_date`, `proposed_end_date`, `proposed_cost`, `proposed_title`, `previous_start_date`, `previous_end_date`, `previous_cost`, `previous_title`, `status`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 2790, 3, '2026-02-23', NULL, NULL, NULL, NULL, '2026-02-26', 20000000.00, 'Foundations', 'pending', NULL, NULL, '2026-02-27 03:59:51', '2026-02-27 03:59:51'),
(2, 2791, 3, '2026-02-27', '2026-03-02', NULL, NULL, NULL, '2026-03-07', 21000000.00, 'Doners', 'pending', NULL, NULL, '2026-02-27 03:59:51', '2026-02-27 03:59:51'),
(3, 2792, 3, '2026-03-03', NULL, NULL, NULL, NULL, '2026-03-07', 19000000.00, 'extension', 'pending', NULL, NULL, '2026-02-27 03:59:51', '2026-02-27 03:59:51'),
(4, 2790, 4, '2026-02-23', '2026-02-25', NULL, NULL, NULL, '2026-02-26', 20000000.00, 'Foundations', 'approved', 379, '2026-02-27 04:12:04', '2026-02-27 04:07:06', '2026-02-27 04:12:04'),
(5, 2791, 4, '2026-02-26', '2026-03-02', NULL, NULL, NULL, '2026-03-07', 21000000.00, 'Doners', 'approved', 379, '2026-02-27 04:12:04', '2026-02-27 04:07:06', '2026-02-27 04:12:04'),
(6, 2792, 4, '2026-03-03', NULL, NULL, NULL, NULL, '2026-03-07', 19000000.00, 'extension', 'approved', 379, '2026-02-27 04:12:04', '2026-02-27 04:07:06', '2026-02-27 04:12:04');

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
(813, 2690, 1023, 1738, 2019, 25000.00, 'bank_transfer', 'TXN-2690', 'receipt.jpg', '2025-12-15', 'submitted', NULL, NULL),
(817, 2767, 1045, 1814, 2055, 750000.00, 'online_payment', '10302837282', 'payments/receipts/1766072244_69441fb4c3095.jpg', '2025-12-18', 'rejected', NULL, NULL),
(818, 2773, 1047, 1814, 2055, 750000.00, 'online_payment', '019172910101', 'payments/receipts/1766118805_6944d59563ee4.jpg', '2025-12-19', 'submitted', NULL, NULL),
(819, 2774, 1048, 1814, 2055, 75000.00, 'online_payment', '019282919', 'payments/receipts/1766121346_6944df82db104.jpg', '2025-12-19', 'approved', NULL, NULL),
(820, 2775, 1048, 1814, 2055, 199925000.00, 'online_payment', '1244', 'payments/receipts/1766121901_6944e1adb9deb.jpg', '2025-12-19', 'approved', NULL, NULL),
(821, 2776, 1049, 1814, 2055, 20000000.00, 'bank_transfer', '10982691001', 'payments/receipts/1766131826_69450872f242e.jpg', '2025-12-19', 'approved', NULL, NULL),
(822, 2778, 1054, 1814, 2055, 6000000.00, 'bank_transfer', '01927101662', 'payments/receipts/1769329448_6975d328ce6d8.jpg', '2026-01-25', 'approved', NULL, NULL),
(823, 2779, 1054, 1814, 2055, 12000000.00, 'bank_transfer', '00182629163', 'payments/receipts/1769329737_6975d4499041f.jpg', '2026-01-25', 'approved', NULL, NULL),
(824, 2780, 1054, 1814, 2055, 2000000.00, 'bank_transfer', '027292773291', 'payments/receipts/1769329763_6975d4639157a.jpg', '2026-01-25', 'approved', NULL, NULL),
(825, 2790, 1056, 1819, 2059, 19000000.00, 'bank_transfer', '01018273628190383', 'payments/receipts/1771850637_699c4b8d02e1c.jpg', '2026-02-23', 'approved', NULL, '2026-02-23 12:45:54');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `type` enum('Milestone Update','Bid Status','Payment Reminder','Project Alert','Progress Update','Dispute Update','Team Update','Payment Status') NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `delivery_method` enum('App','Email','Both') DEFAULT 'App',
  `priority` enum('critical','high','normal') NOT NULL DEFAULT 'normal',
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(10) UNSIGNED DEFAULT NULL,
  `dedup_key` varchar(100) DEFAULT NULL,
  `action_link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `message`, `title`, `type`, `is_read`, `delivery_method`, `priority`, `reference_type`, `reference_id`, `dedup_key`, `action_link`, `created_at`) VALUES
(3418, 1, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3419, 10, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3420, 100, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3421, 101, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3422, 102, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3423, 103, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3424, 104, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3425, 105, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3426, 106, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3427, 107, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3428, 108, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3429, 109, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3430, 11, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3431, 110, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3432, 111, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3433, 112, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3434, 113, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3435, 114, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3436, 115, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3437, 116, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3438, 117, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3439, 118, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3440, 119, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3441, 12, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3442, 120, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3443, 121, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3444, 122, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3445, 123, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3446, 124, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3447, 125, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3448, 126, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3449, 127, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3450, 128, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3451, 129, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3452, 13, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3453, 130, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3454, 131, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3455, 132, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3456, 133, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3457, 134, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3458, 135, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3459, 136, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3460, 137, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3461, 138, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3462, 139, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3463, 14, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3464, 140, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3465, 141, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3466, 142, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3467, 143, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3468, 144, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3469, 145, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3470, 146, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3471, 147, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3472, 148, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3473, 149, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3474, 15, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3475, 150, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3476, 151, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3477, 152, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3478, 153, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3479, 154, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3480, 155, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3481, 156, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3482, 157, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3483, 158, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3484, 159, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3485, 16, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3486, 160, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3487, 161, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3488, 162, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3489, 163, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3490, 164, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3491, 165, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3492, 166, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3493, 167, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3494, 168, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3495, 169, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3496, 17, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3497, 170, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3498, 171, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3499, 172, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3500, 173, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3501, 174, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3502, 175, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3503, 176, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3504, 177, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3505, 178, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3506, 179, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3507, 18, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3508, 180, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3509, 181, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3510, 182, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3511, 183, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3512, 184, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3513, 185, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3514, 186, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3515, 187, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3516, 188, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3517, 189, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3518, 19, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3519, 190, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3520, 191, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3521, 192, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3522, 193, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3523, 194, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3524, 195, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3525, 196, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3526, 197, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3527, 198, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3528, 199, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3529, 2, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3530, 20, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3531, 200, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3532, 201, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3533, 202, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3534, 203, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3535, 204, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3536, 205, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3537, 206, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3538, 207, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3539, 208, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3540, 209, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3541, 21, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3542, 210, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3543, 211, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3544, 212, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3545, 213, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3546, 214, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3547, 215, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3548, 216, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3549, 217, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3550, 218, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3551, 219, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3552, 22, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3553, 220, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3554, 23, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3555, 24, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3556, 25, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3557, 26, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3558, 27, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3559, 28, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3560, 29, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3561, 3, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3562, 30, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3563, 31, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3564, 32, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3565, 33, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3566, 34, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3567, 35, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3568, 36, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3569, 37, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3570, 38, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3571, 39, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3572, 4, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3573, 40, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3574, 41, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3575, 42, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3576, 43, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3577, 44, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3578, 45, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3579, 46, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3580, 47, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3581, 48, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3582, 49, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3583, 5, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3584, 50, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3585, 51, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3586, 52, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3587, 53, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3588, 54, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3589, 55, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3590, 56, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3591, 57, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3592, 58, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3593, 59, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3594, 6, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3595, 60, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3596, 61, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3597, 62, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3598, 63, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3599, 64, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3600, 65, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3601, 66, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3602, 67, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3603, 68, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3604, 69, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3605, 7, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3606, 70, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3607, 71, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3608, 72, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3609, 73, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3610, 74, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3611, 75, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3612, 76, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3613, 77, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3614, 78, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3615, 79, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3616, 8, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3617, 80, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3618, 81, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3619, 82, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3620, 83, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3621, 84, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3622, 85, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3623, 86, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3624, 87, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3625, 88, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3626, 89, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3627, 9, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3628, 90, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3629, 91, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3630, 92, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3631, 93, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3632, 94, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3633, 95, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3634, 96, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3635, 97, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3636, 98, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3637, 99, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3638, 221, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3639, 222, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3640, 223, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3641, 224, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3642, 225, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3643, 226, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3644, 227, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3645, 228, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3646, 229, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3647, 230, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3648, 231, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3649, 232, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3650, 233, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3651, 234, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3652, 235, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3653, 236, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3654, 237, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3655, 238, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3656, 239, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3657, 240, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3658, 241, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3659, 242, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3660, 243, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3661, 244, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3662, 245, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3663, 246, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3664, 247, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3665, 248, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3666, 249, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3667, 250, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3668, 251, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3669, 252, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3670, 253, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3671, 254, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3672, 255, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3673, 256, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3674, 257, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3675, 258, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3676, 259, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3677, 260, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3678, 261, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3679, 262, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3680, 263, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3681, 264, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3682, 265, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3683, 266, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3684, 267, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3685, 268, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3686, 269, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3687, 270, 'Welcome!', NULL, 'Project Alert', 0, 'App', 'normal', NULL, NULL, NULL, NULL, '2025-12-15 07:49:09'),
(3688, 371, 'A contractor has submitted a bid for \"jslaabxxbxsssss\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 267, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1055,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-14 10:45:22'),
(3689, 371, 'A contractor has submitted a bid for \"jslaabxxbxsssss\".', 'New Bid Received', 'Bid Status', 0, 'App', 'normal', 'bid', 268, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1055,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-15 22:20:11'),
(3690, 371, 'A contractor has submitted a bid for \"jslaabxxbxsssss\".', 'New Bid Received', 'Bid Status', 0, 'App', 'normal', 'bid', 269, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1055,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-15 22:21:27'),
(3691, 371, 'A contractor has submitted a bid for \"jslaabxxbxsssss\".', 'New Bid Received', 'Bid Status', 0, 'App', 'normal', 'bid', 270, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1055,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-15 22:25:42'),
(3692, 371, 'A contractor has submitted a bid for \"jslaabxxbxsssss\".', 'New Bid Received', 'Bid Status', 0, 'App', 'normal', 'bid', 271, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1055,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-15 22:43:13'),
(3693, 371, 'A contractor has submitted a bid for \"jslaabxxbxsssss\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 272, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1055,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-15 23:20:55'),
(3694, 371, 'A contractor has submitted a bid for \"jslaabxxbxsssss\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 273, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1055,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-16 01:23:58'),
(3695, 371, 'A contractor has submitted a bid for \"Project Images Testing\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 274, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1052,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-16 01:37:22'),
(3696, 371, 'A contractor has submitted a bid for \"Testing again\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 275, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1053,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-16 01:42:53'),
(3697, 371, 'A contractor has submitted a bid for \"Testing again\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 276, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1053,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-16 01:44:41'),
(3698, 371, 'A contractor has submitted a bid for \"Testing again\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 277, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1053,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-16 01:53:56'),
(3699, 371, 'A contractor has submitted a bid for \"Testing again\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 278, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1053,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-16 01:55:33'),
(3700, 371, 'A contractor has submitted a bid for \"Project Images Testing\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 279, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1052,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-16 01:56:03'),
(3701, 371, 'A contractor has submitted a bid for \"Project Images Testing\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 280, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1052,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-16 05:05:40'),
(3702, 371, 'A contractor has submitted a bid for \"Testing again\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 281, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1053,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-16 05:11:10'),
(3703, 371, 'A contractor has submitted a bid for \"Project Images Testing\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 282, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1052,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-16 05:15:24'),
(3704, 371, 'A contractor has submitted a bid for \"Testing again\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 283, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1053,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-16 05:19:05'),
(3705, 371, 'A contractor has submitted a bid for \"Project Images Testing\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 284, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1052,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-16 05:22:40'),
(3706, 154, 'A contractor has submitted a bid for \"Project 1027\".', 'New Bid Received', 'Bid Status', 0, 'App', 'normal', 'bid', 285, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1007,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-16 05:23:25'),
(3707, 371, 'A contractor has submitted a bid for \"Testing again\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 286, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1053,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-16 05:27:11'),
(3708, 102, 'A contractor has submitted a bid for \"Project 986\".', 'New Bid Received', 'Bid Status', 0, 'App', 'normal', 'bid', 287, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1027,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-16 06:07:58'),
(3709, 371, 'A contractor has submitted a bid for \"jslaabxxbxsssss\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 288, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1055,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-16 07:28:53'),
(3710, 123, 'A contractor has submitted a bid for \"Project 1003\".', 'New Bid Received', 'Bid Status', 0, 'App', 'normal', 'bid', 289, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":995,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-16 07:47:46'),
(3711, 371, 'A contractor has submitted a bid for \"Project Images Testing\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 290, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1052,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-16 07:56:39'),
(3712, 371, 'Contractor submitted a milestone plan for \"jslaabxxbxsssss\". Please review.', 'Milestone Submitted', 'Milestone Update', 1, 'App', 'normal', 'milestone', 1561, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1055,\"tab\":\"milestones\"},\"notification_sub_type\":\"milestone_submitted\"}', '2026-02-17 01:11:33'),
(3713, 371, 'Contractor submitted a milestone plan for \"jslaabxxbxsssss\". Please review.', 'Milestone Submitted', 'Milestone Update', 1, 'App', 'normal', 'milestone', 1562, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1055,\"tab\":\"milestones\"},\"notification_sub_type\":\"milestone_submitted\"}', '2026-02-17 01:48:12'),
(3714, 371, 'A contractor has submitted a bid for \"Testing again\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 291, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1053,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-19 03:42:10'),
(3715, 154, 'A contractor has submitted a bid for \"Project 1027\".', 'New Bid Received', 'Bid Status', 0, 'App', 'normal', 'bid', 292, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1007,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-20 04:09:01'),
(3716, 371, 'A contractor has submitted a bid for \"Testing again\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 293, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1053,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-20 06:11:56'),
(3717, 379, 'A contractor has submitted a bid for \"Commercial Building\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 294, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1056,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-21 01:57:19'),
(3718, 371, 'A contractor has submitted a bid for \"Testz\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 295, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1047,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-22 02:29:15'),
(3719, 372, 'The property owner has already chosen a contractor for \"Testz\". Thank you for your bid.', 'Bid Not Selected', 'Bid Status', 0, 'App', 'normal', 'bid', 260, NULL, '{\"screen\":\"MyBids\",\"params\":{\"projectId\":1047},\"notification_sub_type\":\"bid_rejected\"}', '2026-02-22 07:19:27'),
(3720, 371, 'Contractor submitted a milestone plan for \"Testz\". Please review.', 'Milestone Submitted', 'Milestone Update', 1, 'App', 'normal', 'milestone', 1563, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1047,\"tab\":\"milestones\"},\"notification_sub_type\":\"milestone_submitted\"}', '2026-02-22 07:51:59'),
(3721, 379, 'Contractor submitted a milestone plan for \"Commercial Building\". Please review.', 'Milestone Submitted', 'Milestone Update', 1, 'App', 'normal', 'milestone', 1564, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1056,\"tab\":\"milestones\"},\"notification_sub_type\":\"milestone_submitted\"}', '2026-02-23 00:12:07'),
(3722, 379, 'Contractor has modified and resubmitted the milestone setup for \"Commercial Building\". Please review the updated proposal.', 'Milestone Resubmitted', 'Milestone Update', 1, 'App', 'high', 'milestone', 1564, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1056,\"tab\":\"milestones\"},\"notification_sub_type\":\"milestone_resubmitted\"}', '2026-02-23 00:58:25'),
(3723, 379, 'Contractor uploaded progress for \"Foundations\" on \"Commercial Building\".', 'Progress Uploaded', 'Progress Update', 1, 'App', 'normal', 'progress', 832, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1056,\"tab\":\"progress\"},\"notification_sub_type\":\"progress_submitted\"}', '2026-02-23 04:29:49'),
(3725, 379, 'Your payment for \"Commercial Building\" has been approved by the contractor.', 'Payment Approved', 'Payment Status', 1, 'App', 'normal', 'payment', 825, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1056,\"tab\":\"payments\"},\"notification_sub_type\":\"payment_approved\"}', '2026-02-23 04:45:54'),
(3726, 379, 'Contractor uploaded progress for \"Doners\" on \"Commercial Building\".', 'Progress Uploaded', 'Progress Update', 1, 'App', 'normal', 'progress', 833, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1056,\"tab\":\"progress\"},\"notification_sub_type\":\"progress_submitted\"}', '2026-02-23 04:47:56'),
(3727, 380, 'Owner set a payment deadline of Feb 26, 2026 for \"Foundations\".', 'Payment Due Date Set', 'Payment Reminder', 1, 'App', 'high', 'milestone_item', 2790, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1056,\"tab\":\"payments\"},\"notification_sub_type\":\"payment_due\"}', '2026-02-24 23:54:58'),
(3730, 380, 'Your project update request has been approved. The project timeline and budget have been updated.', 'Budget Adjustment Approved', 'Project Alert', 1, 'App', 'high', 'project', 1056, NULL, '{\"screen\":\"ProjectTimeline\",\"params\":{\"projectId\":1056},\"notification_sub_type\":\"project_update\"}', '2026-02-25 07:15:26'),
(3731, 379, 'Contractor submitted a project update request for \"Commercial Building\". Please review.', 'Project Update Request Submitted', 'Project Alert', 0, 'App', 'high', 'project', 1056, NULL, '{\"screen\":\"ProjectTimeline\",\"params\":{\"projectId\":1056},\"notification_sub_type\":\"project_update\"}', '2026-02-27 03:59:51'),
(3732, 379, 'The contractor has withdrawn their update request for \"Commercial Building\".', 'Project Update Withdrawn', 'Project Alert', 0, 'App', 'normal', 'project', 1056, NULL, '{\"screen\":\"ProjectTimeline\",\"params\":{\"projectId\":1056},\"notification_sub_type\":\"project_update\"}', '2026-02-27 04:02:45'),
(3733, 379, 'Contractor submitted a project update request for \"Commercial Building\". Please review.', 'Project Update Request Submitted', 'Project Alert', 0, 'App', 'high', 'project', 1056, NULL, '{\"screen\":\"ProjectTimeline\",\"params\":{\"projectId\":1056},\"notification_sub_type\":\"project_update\"}', '2026-02-27 04:07:06'),
(3734, 380, 'Your project update request has been approved. The project timeline and budget have been updated.', 'Project Update Approved', 'Project Alert', 0, 'App', 'high', 'project', 1056, NULL, '{\"screen\":\"ProjectTimeline\",\"params\":{\"projectId\":1056},\"notification_sub_type\":\"project_update\"}', '2026-02-27 04:12:04'),
(3735, 379, 'A Delay dispute has been filed against you on \"Commercial Building\".', 'Dispute Filed', 'Dispute Update', 0, 'App', 'critical', 'dispute', 88, NULL, '{\"screen\":\"DisputeDetails\",\"params\":{\"disputeId\":88},\"notification_sub_type\":\"dispute_opened\"}', '2026-02-28 06:17:02');

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
-- Table structure for table `payment_adjustment_logs`
--

CREATE TABLE `payment_adjustment_logs` (
  `log_id` bigint(20) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `milestone_id` int(10) UNSIGNED NOT NULL,
  `source_item_id` int(10) UNSIGNED NOT NULL COMMENT 'Item that was over/under-paid',
  `target_item_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Next item that received carry-forward (NULL for overpayment)',
  `payment_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'The payment that triggered this adjustment (NULL for completion-triggered)',
  `adjustment_type` enum('overpayment','underpayment') NOT NULL COMMENT 'What kind of adjustment',
  `original_required` decimal(12,2) NOT NULL COMMENT 'Original required amount of source item',
  `total_paid` decimal(12,2) NOT NULL COMMENT 'Total approved payments on source item after this payment',
  `adjustment_amount` decimal(12,2) NOT NULL COMMENT 'The excess (overpay) or shortfall (underpay) amount',
  `target_original_cost` decimal(12,2) DEFAULT NULL COMMENT 'Target item original cost before adjustment',
  `target_adjusted_cost` decimal(12,2) DEFAULT NULL COMMENT 'Target item cost after adjustment',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_adjustment_logs`
--

INSERT INTO `payment_adjustment_logs` (`log_id`, `project_id`, `milestone_id`, `source_item_id`, `target_item_id`, `payment_id`, `adjustment_type`, `original_required`, `total_paid`, `adjustment_amount`, `target_original_cost`, `target_adjusted_cost`, `notes`, `created_at`) VALUES
(1, 1056, 1564, 2790, 2791, NULL, 'underpayment', 20000000.00, 19000000.00, 1000000.00, 20000000.00, 21000000.00, 'Data repair: carry-forward corrected from 2M to 1M (was doubled due to missing transaction + non-idempotent code). Shortfall of 1,000,000.00 carried from item 2790 to 2791.', '2026-02-23 07:03:48'),
(2, 1056, 1564, 2790, 2791, NULL, 'underpayment', 20000000.00, 19000000.00, 1000000.00, 20000000.00, 21000000.00, 'Shortfall of 1,000,000.00 carried forward on item completion to item #2 (Doners).', '2026-02-23 07:21:06');

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
(919, 1044, 1798, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 07:49:09', '2025-12-15 07:49:09'),
(920, 1045, 1809, 'downpayment', 50000000.00, 2500000.00, 0, '2025-12-17 14:57:31', '2025-12-17 14:57:31'),
(921, 1047, 1809, 'downpayment', 50000000.00, 7500000.00, 0, '2025-12-18 19:51:47', '2025-12-18 19:51:47'),
(922, 1048, 1809, 'full_payment', 200000000.00, 0.00, 0, '2025-12-18 21:10:50', '2025-12-18 21:10:50'),
(923, 1049, 1809, 'downpayment', 55000000.00, 10000.00, 0, '2025-12-18 23:59:41', '2025-12-18 23:59:41'),
(924, 1054, 1809, 'downpayment', 30000000.00, 10000000.00, 0, '2026-01-25 00:20:09', '2026-01-25 00:20:09'),
(925, 1055, 1809, 'downpayment', 8000000.00, 2000000.00, 0, '2026-02-17 01:11:33', '2026-02-17 01:11:33'),
(926, 1055, 1809, 'downpayment', 8000000.00, 2000000.00, 0, '2026-02-17 01:48:12', '2026-02-17 01:48:12'),
(927, 1047, 1810, 'downpayment', 6898.00, 898.00, 0, '2026-02-22 07:51:59', '2026-02-22 07:51:59'),
(928, 1056, 1810, 'downpayment', 60000000.00, 10000000.00, 0, '2026-02-23 00:12:07', '2026-02-27 04:12:04');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 372, 'mobile-app', '1d05033ca40695dee095426a6eb7aac16a7deb375f0424bff2b371da70788793', '[\"*\"]', NULL, NULL, '2025-12-17 16:34:20', '2025-12-17 16:34:20'),
(2, 'App\\Models\\User', 372, 'mobile-app', '1eb653fa8d5ff865e48207334d69f18203157a8bf0f90e8dec5bcd1708ff8fcd', '[\"*\"]', NULL, NULL, '2025-12-17 16:40:24', '2025-12-17 16:40:24'),
(3, 'App\\Models\\User', 372, 'mobile-app', '60080e79364196cfc80eb3d9dac807c39e31e0a1c28bd4c1a55c635dc87718d6', '[\"*\"]', NULL, NULL, '2025-12-17 16:47:36', '2025-12-17 16:47:36'),
(4, 'App\\Models\\User', 371, 'mobile-app', '8a04887902c3e5d33b3b09290c242175345a6ff77852ea68d03e5edc9456186a', '[\"*\"]', NULL, NULL, '2025-12-17 16:48:46', '2025-12-17 16:48:46'),
(5, 'App\\Models\\User', 371, 'mobile-app', 'cb573f899d8b83d0b0a875069215b22dfb9d6ee4f766ff64265354adf0207daa', '[\"*\"]', '2025-12-18 02:35:19', NULL, '2025-12-18 01:59:06', '2025-12-18 02:35:19'),
(6, 'App\\Models\\User', 371, 'mobile-app', 'f27822666ce4960abca25e0d650e087b325a93f37e74f51f62336c996ee0d7ba', '[\"*\"]', '2025-12-18 02:36:04', NULL, '2025-12-18 02:35:46', '2025-12-18 02:36:04'),
(7, 'App\\Models\\User', 371, 'mobile-app', '56481dfb81940e03867db653e5d0a725c32eb587b736e9110116007e6a8a47df', '[\"*\"]', NULL, NULL, '2025-12-18 06:43:25', '2025-12-18 06:43:25'),
(8, 'App\\Models\\User', 371, 'mobile-app', '1a5b21862489b6f3f0f1a82171f6d9986fd0b7b58ace3ca59317335e539449ca', '[\"*\"]', '2025-12-18 06:59:34', NULL, '2025-12-18 06:43:54', '2025-12-18 06:59:34'),
(9, 'App\\Models\\User', 372, 'mobile-app', 'a5628ebc39625c3b638e1df9efabe66a75e0b1a9e242457b532d1a5aba957c24', '[\"*\"]', '2025-12-18 09:50:47', NULL, '2025-12-18 07:03:51', '2025-12-18 09:50:47'),
(10, 'App\\Models\\User', 371, 'mobile-app', '328e73c2151ad5364f762d1df47f5aefa0a1b34ec543c1f0b7f7fd46f0baba9f', '[\"*\"]', '2025-12-18 10:20:03', NULL, '2025-12-18 07:08:37', '2025-12-18 10:20:03'),
(11, 'App\\Models\\User', 371, 'mobile-app', 'bf434ab2ec8b6a239e15a8ad9f1d79e20ac6e450685843b88c7fe2da20d73da4', '[\"*\"]', NULL, NULL, '2025-12-18 12:03:37', '2025-12-18 12:03:37'),
(12, 'App\\Models\\User', 372, 'mobile-app', '9ecaacbbedd1422fbcb721bcb22365422803f48ea6feec49115d6bfb166f3f9d', '[\"*\"]', NULL, NULL, '2025-12-18 12:04:10', '2025-12-18 12:04:10'),
(13, 'App\\Models\\User', 373, 'mobile-app', '036708be4778dd6444a3b35a9b20d4b042956400f7b6097d4dfeb10755494d8f', '[\"*\"]', NULL, NULL, '2025-12-18 12:08:18', '2025-12-18 12:08:18'),
(14, 'App\\Models\\User', 373, 'mobile-app', 'fa3b577b569f69c78bae69b14e6247beabc1924d494d17fb1eae80ba36910fe9', '[\"*\"]', NULL, NULL, '2025-12-18 12:37:44', '2025-12-18 12:37:44'),
(15, 'App\\Models\\User', 373, 'mobile-app', '4b26a80e51154cd5a18dc65974313bae90fdaa9e355e5ee857aee3ebfe9dcb55', '[\"*\"]', NULL, NULL, '2025-12-18 12:43:40', '2025-12-18 12:43:40'),
(16, 'App\\Models\\User', 373, 'mobile-app', '0726c1051cd3196c2b55924df1d877737c1d8ce363d6c039bb329fe42a7541a9', '[\"*\"]', NULL, NULL, '2025-12-18 14:51:24', '2025-12-18 14:51:24'),
(17, 'App\\Models\\User', 373, 'mobile-app', 'f2b51b73940a5cb2f20cda381b692629739d5b41f27872ff42d329c41ba18abc', '[\"*\"]', NULL, NULL, '2025-12-18 15:12:56', '2025-12-18 15:12:56'),
(18, 'App\\Models\\User', 373, 'mobile-app', '9223a7d9bd643d05fbbfe9561740629eb45d54c017214f753255e9a7f2248032', '[\"*\"]', NULL, NULL, '2025-12-18 15:21:19', '2025-12-18 15:21:19'),
(19, 'App\\Models\\User', 373, 'mobile-app', '828104444ae88b036160c51285bfacab01a3f6e6ca08a7ef5388265f58306803', '[\"*\"]', NULL, NULL, '2025-12-18 15:28:43', '2025-12-18 15:28:43'),
(20, 'App\\Models\\User', 373, 'mobile-app', 'acfe56bb5929af3b532257e0bed406a939d508a4e9416a911211965cc97b692b', '[\"*\"]', NULL, NULL, '2025-12-18 15:58:32', '2025-12-18 15:58:32'),
(21, 'App\\Models\\User', 373, 'mobile-app', '3dfe7ea51e8b0ed7cd1632a3fd58e34dc0694c223be00097a1819ed8bf17fb3a', '[\"*\"]', NULL, NULL, '2025-12-18 16:18:06', '2025-12-18 16:18:06'),
(22, 'App\\Models\\User', 1, 'mobile-app', 'f9b3a2865d829777af2265d712204b27afc4e4e2a8a527ce4063e99cc9b189ef', '[\"*\"]', NULL, NULL, '2025-12-18 18:42:50', '2025-12-18 18:42:50'),
(23, 'App\\Models\\User', 1, 'mobile-app', '6b1bd1577dfc1eb459fa1347d9c5e78402806b746195536822348a151dc82f0f', '[\"*\"]', NULL, NULL, '2025-12-18 18:46:03', '2025-12-18 18:46:03'),
(24, 'App\\Models\\User', 371, 'mobile-app', '4f6f06c40b15a1342c3731e24a26c7fb3d3bb4842f62eae48fa494c589e205cf', '[\"*\"]', NULL, NULL, '2025-12-18 18:46:29', '2025-12-18 18:46:29'),
(25, 'App\\Models\\User', 372, 'mobile-app', '558c2442a24378e4166484f0ee0be9ed1e67845952a59296dbd56935cd06adef', '[\"*\"]', NULL, NULL, '2025-12-18 18:48:08', '2025-12-18 18:48:08'),
(26, 'App\\Models\\User', 371, 'mobile-app', '2b88e5e253af2037fdaf573436c16713277df0369e65b736276ed6a876c9bcb5', '[\"*\"]', NULL, NULL, '2025-12-18 18:56:12', '2025-12-18 18:56:12'),
(27, 'App\\Models\\User', 371, 'mobile-app', 'd28c9ad63a68b43ef53606b03b056a4e165c4106f6c3fe378910d103b7b92090', '[\"*\"]', NULL, NULL, '2025-12-18 19:06:10', '2025-12-18 19:06:10'),
(28, 'App\\Models\\User', 371, 'mobile-app', 'acddff054787658b1396450ac7560b837741189d76938c5f4be4cfdef686d551', '[\"*\"]', NULL, NULL, '2025-12-18 19:16:00', '2025-12-18 19:16:00'),
(29, 'App\\Models\\User', 372, 'mobile-app', '508d3557fc2760044fe8275918c3cadaf064df89afd1a41c052ede748dc292c2', '[\"*\"]', NULL, NULL, '2025-12-18 19:19:51', '2025-12-18 19:19:51'),
(30, 'App\\Models\\User', 372, 'mobile-app', 'aa9898b03a3d6317c629755bc0d399563c28c3a6a0cfecbb95e66f08a61f54ce', '[\"*\"]', NULL, NULL, '2025-12-18 19:32:35', '2025-12-18 19:32:35'),
(31, 'App\\Models\\User', 372, 'mobile-app', '090db815d9ca0b58901cdbdd4565ce52d440f361873734f1e65b85da758b428d', '[\"*\"]', NULL, NULL, '2025-12-18 19:56:43', '2025-12-18 19:56:43'),
(32, 'App\\Models\\User', 371, 'mobile-app', '71d5d56c2f3e00d2bbeda310681813b5026bd1bc882af53c34f78590cb938b00', '[\"*\"]', NULL, NULL, '2025-12-18 19:58:39', '2025-12-18 19:58:39'),
(33, 'App\\Models\\User', 372, 'mobile-app', 'c476c181980d367dc180dbffdf334b15c8da015a12cff4a4de7353c22a348c94', '[\"*\"]', NULL, NULL, '2025-12-18 19:59:14', '2025-12-18 19:59:14'),
(34, 'App\\Models\\User', 372, 'mobile-app', 'f5cafa94322c5c139318e1e5dc873c4bd16a4f7fc2249d895454bf7adbd39500', '[\"*\"]', NULL, NULL, '2025-12-18 20:04:06', '2025-12-18 20:04:06'),
(35, 'App\\Models\\User', 372, 'mobile-app', '1d1b56aef93ebcc883b938737cf3784c4960e05bfaf7c6bdbe609cb77a3aea8e', '[\"*\"]', NULL, NULL, '2025-12-18 20:12:29', '2025-12-18 20:12:29'),
(36, 'App\\Models\\User', 371, 'mobile-app', 'd5a7fe1914f0bce7f8f3afe919243759e06f629b80061ad17c8796353a61686a', '[\"*\"]', NULL, NULL, '2025-12-18 20:16:45', '2025-12-18 20:16:45'),
(37, 'App\\Models\\User', 371, 'mobile-app', 'a44f123d00fc5a3b50ba240f4cf4f157ae84ccc557a1f01192b22a624be8f5a3', '[\"*\"]', NULL, NULL, '2025-12-18 20:20:59', '2025-12-18 20:20:59'),
(38, 'App\\Models\\User', 372, 'mobile-app', 'f7f73d6b76c021ca1c51dbe1761c4a2cb60a752fd5a876bc8bf036baa0e51af7', '[\"*\"]', NULL, NULL, '2025-12-18 20:26:19', '2025-12-18 20:26:19'),
(39, 'App\\Models\\User', 371, 'mobile-app', '417c2b459e65bb3ed8cfb2f65924af91803d3e859f7df9eff1087f3cdc97079b', '[\"*\"]', NULL, NULL, '2025-12-18 20:27:12', '2025-12-18 20:27:12'),
(40, 'App\\Models\\User', 371, 'mobile-app', '47916bb9311874b7d4d066a77dc9c770532105f3dd852022badbd96ec7d82503', '[\"*\"]', NULL, NULL, '2025-12-18 20:58:53', '2025-12-18 20:58:53'),
(41, 'App\\Models\\User', 371, 'mobile-app', '63341d63c9d6f0021a5e462a775c78fdfe2120ccf3b8cd267c3c97f41f7f7932', '[\"*\"]', NULL, NULL, '2025-12-18 21:01:40', '2025-12-18 21:01:40'),
(42, 'App\\Models\\User', 372, 'mobile-app', 'cf6deeea458ae14afd39350980aabcfad6577cd5805cd84197786c45260eb91a', '[\"*\"]', NULL, NULL, '2025-12-18 23:43:03', '2025-12-18 23:43:03'),
(43, 'App\\Models\\User', 371, 'mobile-app', '8a809328517003c8b97e70da433f1ae77cc544d793d75bc3faa65b0649618f45', '[\"*\"]', NULL, NULL, '2025-12-18 23:43:19', '2025-12-18 23:43:19'),
(44, 'App\\Models\\User', 371, 'mobile-app', '004c47fd7ca1a26d8462dee23b823eea965027c40ad40ec5940f566ea0ca5503', '[\"*\"]', NULL, NULL, '2026-01-15 00:54:20', '2026-01-15 00:54:20'),
(45, 'App\\Models\\User', 371, 'mobile-app', 'cbd195db90d85bd6be3d7bd6ecb5520d427a4ac3d09d40e650923b8b4af81213', '[\"*\"]', NULL, NULL, '2026-01-15 05:26:24', '2026-01-15 05:26:24'),
(46, 'App\\Models\\User', 372, 'mobile-app', '91b65b777ad7354f4b8474e28739847f48a939d10718108787ccdb781c6bc466', '[\"*\"]', NULL, NULL, '2026-01-17 23:35:33', '2026-01-17 23:35:33'),
(47, 'App\\Models\\User', 371, 'mobile-app', 'bd900c166df880c3dc415d5dec9c7be70380e3e51cc33bd877cd9d6bb14583b2', '[\"*\"]', NULL, NULL, '2026-01-17 23:43:26', '2026-01-17 23:43:26'),
(48, 'App\\Models\\User', 371, 'mobile-app', '5249f333eb0114a5c8fa8f7c35e8d604ba0690a938f9a10cbcb092cc180552c3', '[\"*\"]', NULL, NULL, '2026-01-17 23:46:36', '2026-01-17 23:46:36'),
(49, 'App\\Models\\User', 372, 'mobile-app', '551b345742803747b03626d5bf701ec36eae7bff88dcc3a2351680c34e737049', '[\"*\"]', NULL, NULL, '2026-01-17 23:46:59', '2026-01-17 23:46:59'),
(50, 'App\\Models\\User', 371, 'mobile-app', '613a58f45404ed02a5b15dec8528ae5e68fe8d89476ee781ad7374c07777c0ce', '[\"*\"]', NULL, NULL, '2026-01-18 23:12:37', '2026-01-18 23:12:37'),
(51, 'App\\Models\\User', 372, 'mobile-app', 'a91f25627d5cc66c0027d2f0bda3065325b77f62e1b52d852abaabcdf1e16ce4', '[\"*\"]', NULL, NULL, '2026-01-18 23:33:43', '2026-01-18 23:33:43'),
(52, 'App\\Models\\User', 371, 'mobile-app', 'd21976e1b07ea86dd552396c3c7a8cf450a18d8a0f33e8d15f089b6260e802bb', '[\"*\"]', NULL, NULL, '2026-01-19 00:33:59', '2026-01-19 00:33:59'),
(53, 'App\\Models\\User', 372, 'mobile-app', '429739217ae9dd5876577d29856d801add826b7611776ab201c2236254d44c12', '[\"*\"]', NULL, NULL, '2026-01-19 01:07:11', '2026-01-19 01:07:11'),
(54, 'App\\Models\\User', 371, 'mobile-app', '91ae749b23f1d27bd87c33f6f456c13aa7237c0a2da83030610ee54f3e8aab39', '[\"*\"]', NULL, NULL, '2026-01-19 01:33:19', '2026-01-19 01:33:19'),
(55, 'App\\Models\\User', 372, 'mobile-app', 'e696bb73317ea7ac48d2efaf59989ad3ef50ff25aafc8e74616dcaa0a6f76d1f', '[\"*\"]', NULL, NULL, '2026-01-19 01:47:35', '2026-01-19 01:47:35'),
(56, 'App\\Models\\User', 371, 'mobile-app', '8a9c2d9837990f2d4544d4a2e20a700c175f5d48d54dc357a9a3236dd3c7e25d', '[\"*\"]', NULL, NULL, '2026-01-19 07:11:33', '2026-01-19 07:11:33'),
(57, 'App\\Models\\User', 372, 'mobile-app', '03a228bb72f76129c37cfbffcfac87d2c34aebf21483c8a0191f6cb79909e058', '[\"*\"]', NULL, NULL, '2026-01-19 07:14:00', '2026-01-19 07:14:00'),
(58, 'App\\Models\\User', 371, 'mobile-app', 'd91605a897e810737faadb5575f2b75715f676f7c365b69c272803faaf27e214', '[\"*\"]', NULL, NULL, '2026-01-25 00:12:29', '2026-01-25 00:12:29'),
(59, 'App\\Models\\User', 372, 'mobile-app', 'd3ff36161ed242f09c4051e06e9d86067de690e3609da6c7b32f46ed3ec8d452', '[\"*\"]', NULL, NULL, '2026-01-25 00:14:57', '2026-01-25 00:14:57'),
(60, 'App\\Models\\User', 371, 'mobile-app', '73006434ec82b4ac363a3471fe18d55dd030f97a8aabcb2d4c1221e1f1e17169', '[\"*\"]', NULL, NULL, '2026-01-25 00:16:42', '2026-01-25 00:16:42'),
(61, 'App\\Models\\User', 372, 'mobile-app', '8b69efa45d92d8f86a8adbdd6d45bb73efa8052ef89ab0a40bb2018a4e85577f', '[\"*\"]', NULL, NULL, '2026-01-25 00:17:26', '2026-01-25 00:17:26'),
(62, 'App\\Models\\User', 371, 'mobile-app', 'f9aa8b168f5cc924ddaa37c3db14e73d5d1922f3b34de2236b732251f88b65dc', '[\"*\"]', NULL, NULL, '2026-01-25 00:20:32', '2026-01-25 00:20:32'),
(63, 'App\\Models\\User', 371, 'mobile-app', '49a7a2b052b652f7f7376eeeacfd56fd2c8f783d3c21137e6b4a8aad1a7e40ed', '[\"*\"]', NULL, NULL, '2026-01-25 00:21:41', '2026-01-25 00:21:41'),
(64, 'App\\Models\\User', 372, 'mobile-app', '05e3ed7534b4df30c00d228cbb83a4815ba5358c559d553778cdb8d20e0906ca', '[\"*\"]', NULL, NULL, '2026-01-25 00:22:07', '2026-01-25 00:22:07'),
(65, 'App\\Models\\User', 371, 'mobile-app', '9671c21f5f69a1eccfb8e68ceb191ae85f54a20cbee992b1748710ef92eae93e', '[\"*\"]', NULL, NULL, '2026-01-25 00:23:04', '2026-01-25 00:23:04'),
(66, 'App\\Models\\User', 372, 'mobile-app', '1534bdde4f50bcac5bb04c9bf231817266cb2d4cbc0c990901cce1bbf6095086', '[\"*\"]', NULL, NULL, '2026-01-25 00:24:53', '2026-01-25 00:24:53'),
(67, 'App\\Models\\User', 371, 'mobile-app', 'adb432d1efac55d585a8022d90e20e425466b108f87b74c5f4764c988f5fbd39', '[\"*\"]', NULL, NULL, '2026-01-25 00:25:34', '2026-01-25 00:25:34'),
(68, 'App\\Models\\User', 372, 'mobile-app', '576592e9e901ed7e698e31e2fa9ac678a2ebc0a4f1075dc8cd24336de57de7c2', '[\"*\"]', NULL, NULL, '2026-01-25 00:26:52', '2026-01-25 00:26:52'),
(69, 'App\\Models\\User', 371, 'mobile-app', '2de9a3319d56b47f016ea94e4164900fd24b4128989f03093ff42ac0db6c883f', '[\"*\"]', NULL, NULL, '2026-01-25 00:28:03', '2026-01-25 00:28:03'),
(70, 'App\\Models\\User', 372, 'mobile-app', '6ce38564f278bde52a51eee7405dfe2a34c766b62516d10998878a5f17ded06a', '[\"*\"]', NULL, NULL, '2026-01-25 00:29:43', '2026-01-25 00:29:43'),
(71, 'App\\Models\\User', 371, 'mobile-app', 'be81ba973169b09d662a2efed768f3ae79ad04dc6743e01d58420f4505c59804', '[\"*\"]', NULL, NULL, '2026-01-25 00:30:43', '2026-01-25 00:30:43'),
(72, 'App\\Models\\User', 371, 'mobile-app', 'a01093d82f77b9b760a35ecbc1894d703dee52d6843fea8afa770f25bf1cb021', '[\"*\"]', NULL, NULL, '2026-01-25 00:42:15', '2026-01-25 00:42:15'),
(73, 'App\\Models\\User', 372, 'mobile-app', 'c682006e63f10bfcad681181e8f69e0f7cf6fc625b2ddce95a04481562e2a057', '[\"*\"]', NULL, NULL, '2026-01-25 00:43:11', '2026-01-25 00:43:11'),
(74, 'App\\Models\\User', 371, 'mobile-app', 'b9c77af93d1f4c92ea39c2f1dee12eeb4f3d4caae738b7f92986a16349bfb6bb', '[\"*\"]', NULL, NULL, '2026-01-25 00:49:28', '2026-01-25 00:49:28'),
(75, 'App\\Models\\User', 371, 'mobile-app', '446679f546789c0babb615d4d55fe193f06df100b614471e60a9e7e6d227812e', '[\"*\"]', NULL, NULL, '2026-02-20 23:43:59', '2026-02-20 23:43:59'),
(76, 'App\\Models\\User', 379, 'mobile-app', 'e523e8f39e3be036ff8ba9949fb2df43105dc6676738209eab9a944bb17cf626', '[\"*\"]', NULL, NULL, '2026-02-21 01:06:47', '2026-02-21 01:06:47'),
(77, 'App\\Models\\User', 380, 'mobile-app', '7d3bd053a5bcd22e870928f32aa2a18a2c9c5d54a258461cb18e03813911984f', '[\"*\"]', NULL, NULL, '2026-02-21 01:38:50', '2026-02-21 01:38:50'),
(78, 'App\\Models\\User', 380, 'mobile-app', '146fc72a3614e0b3c68d4a50e073f087b7a069c49948dca87cd83f9f5998b47b', '[\"*\"]', NULL, NULL, '2026-02-21 01:39:23', '2026-02-21 01:39:23'),
(79, 'App\\Models\\User', 380, 'mobile-app', 'eaa408438e4364ce106594e4ae2c5be8b91c3025d31e255ee5c22bc972540f4b', '[\"*\"]', NULL, NULL, '2026-02-21 01:50:33', '2026-02-21 01:50:33'),
(80, 'App\\Models\\User', 379, 'mobile-app', 'c2ce0a110f6c0d993b318bb4a0eff8a9b33a886db00b8c1f617fd7465e4fdcb8', '[\"*\"]', NULL, NULL, '2026-02-21 01:51:19', '2026-02-21 01:51:19'),
(81, 'App\\Models\\User', 379, 'mobile-app', '4319301b534989c1f3f9518ba68711550a3b5b6473c649216731fdba748de35d', '[\"*\"]', NULL, NULL, '2026-02-21 01:54:47', '2026-02-21 01:54:47'),
(82, 'App\\Models\\User', 380, 'mobile-app', '5d026f396dbfe64e008911466e06bb74c1b6525961a8a22c478d02277e66212a', '[\"*\"]', NULL, NULL, '2026-02-21 01:55:18', '2026-02-21 01:55:18'),
(83, 'App\\Models\\User', 379, 'mobile-app', 'cb4c115d606ef2bc957fab7a65b0dbd50a909d783a77b2e830306b2c1f51f892', '[\"*\"]', NULL, NULL, '2026-02-21 01:57:42', '2026-02-21 01:57:42'),
(84, 'App\\Models\\User', 380, 'mobile-app', '189b5fbc2280b644a427d2e389185147b652d0bc3ac8515133756c0a286d0c94', '[\"*\"]', NULL, NULL, '2026-02-21 02:36:18', '2026-02-21 02:36:18'),
(85, 'App\\Models\\User', 380, 'mobile-app', '9b71c082d0185c04f6d511fe6917f5ff676e6d7293c5395ce68ac87289a2a6bf', '[\"*\"]', NULL, NULL, '2026-02-22 02:00:58', '2026-02-22 02:00:58'),
(86, 'App\\Models\\User', 371, 'mobile-app', '378b752977e2bf323c68fb0e45045c1890391a0d4ecdccb8d1bf07cf9bb635e2', '[\"*\"]', NULL, NULL, '2026-02-22 07:18:46', '2026-02-22 07:18:46'),
(87, 'App\\Models\\User', 372, 'mobile-app', 'd9681a96c46c752c5c290b8afd32a8ca4817ea33a2082d900506917a5745c8ca', '[\"*\"]', NULL, NULL, '2026-02-22 07:23:38', '2026-02-22 07:23:38'),
(88, 'App\\Models\\User', 372, 'mobile-app', '1d363cdc6527d7539c49de2894a2d3e2e546ce94c4e0ec676996c11d5fc153fa', '[\"*\"]', NULL, NULL, '2026-02-22 07:31:06', '2026-02-22 07:31:06'),
(89, 'App\\Models\\User', 380, 'mobile-app', '4ffaffc1ed76b6c31c2a937ea35e344dc1335e89f42baf3c8b4d5e0ee94d4930', '[\"*\"]', NULL, NULL, '2026-02-22 07:37:16', '2026-02-22 07:37:16'),
(90, 'App\\Models\\User', 371, 'mobile-app', 'e15d7ca04e4cb3690322323fcd9f3efc5fceaee9a6de109767d320e6f1ddfe91', '[\"*\"]', NULL, NULL, '2026-02-22 07:58:28', '2026-02-22 07:58:28'),
(91, 'App\\Models\\User', 380, 'mobile-app', '66fea00f980350762ab442fc147529d9e2873cbd8ada06b337b6143ed50fc0b3', '[\"*\"]', NULL, NULL, '2026-02-22 08:17:04', '2026-02-22 08:17:04'),
(92, 'App\\Models\\User', 371, 'mobile-app', 'b51392ae7c464e54ab7f9de7917ae83a6b8b5e390a6e8cfd5487a086d0cdc551', '[\"*\"]', NULL, NULL, '2026-02-22 08:20:33', '2026-02-22 08:20:33'),
(93, 'App\\Models\\User', 380, 'mobile-app', 'b5de621c0dd075b120e824961082b48f8fddb4b8abdd1e472c64b7fcd8ed3299', '[\"*\"]', NULL, NULL, '2026-02-22 08:25:00', '2026-02-22 08:25:00'),
(94, 'App\\Models\\User', 379, 'mobile-app', '6ad0804824affc5555eded38eee32df35a1447702ef1e194097cf81e8e72fb7b', '[\"*\"]', NULL, NULL, '2026-02-23 00:08:26', '2026-02-23 00:08:26'),
(95, 'App\\Models\\User', 380, 'mobile-app', '0803913ce84cc03d7faad639e5c332f51e9429cc8a01eee9f7a26b3b892fb6e1', '[\"*\"]', NULL, NULL, '2026-02-23 00:10:01', '2026-02-23 00:10:01'),
(96, 'App\\Models\\User', 379, 'mobile-app', '056961db58ce7e5b62c783600bba7e212c260bbf1bcae8d685bd3bae494473e2', '[\"*\"]', NULL, NULL, '2026-02-23 00:12:51', '2026-02-23 00:12:51'),
(97, 'App\\Models\\User', 380, 'mobile-app', 'b5ccbda1e9bcd8e67dc38ddc33c53b1c66b2c9a6e13d90b60b58efca1d2cd033', '[\"*\"]', NULL, NULL, '2026-02-23 00:14:19', '2026-02-23 00:14:19'),
(98, 'App\\Models\\User', 379, 'mobile-app', '2eb958c9a30acf3f9fdba3b90ca9656c06eb657455a98e028ce7105cfff775a0', '[\"*\"]', NULL, NULL, '2026-02-23 00:59:08', '2026-02-23 00:59:08'),
(99, 'App\\Models\\User', 380, 'mobile-app', '37b6f779102fc6ddcee6a5941554a7440692f3a8650c0e6f67785b7dfed070ba', '[\"*\"]', NULL, NULL, '2026-02-23 01:55:49', '2026-02-23 01:55:49'),
(100, 'App\\Models\\User', 379, 'mobile-app', '5fc92d935461a21aef7b2fcec64e45dcc5790788410b51b9188b25ed52ccdc02', '[\"*\"]', NULL, NULL, '2026-02-23 04:30:23', '2026-02-23 04:30:23'),
(101, 'App\\Models\\User', 380, 'mobile-app', '0f6a9e9d7874ef2c0bc87f16cb1040ccabb9314a9856cff249ed69140d614d17', '[\"*\"]', NULL, NULL, '2026-02-23 04:44:33', '2026-02-23 04:44:33'),
(102, 'App\\Models\\User', 379, 'mobile-app', 'c50ced955988aa705e768f93b718c50e8ef6f92341aeaa1e4e5136cf25a048a4', '[\"*\"]', NULL, NULL, '2026-02-23 04:46:27', '2026-02-23 04:46:27'),
(103, 'App\\Models\\User', 380, 'mobile-app', 'eebd3d0fa6350719d08cc0fd64c86ba897138ce2abd2d65aa86b5c9a952ea904', '[\"*\"]', NULL, NULL, '2026-02-23 04:47:06', '2026-02-23 04:47:06'),
(104, 'App\\Models\\User', 379, 'mobile-app', '98f7502efae06db5c046e8354fd1902fb5cab747acab45a478a2c3aec03cd651', '[\"*\"]', NULL, NULL, '2026-02-23 04:48:18', '2026-02-23 04:48:18'),
(105, 'App\\Models\\User', 380, 'mobile-app', 'cd666b8b96befd93780f4cc904b827e58723f1d27e8d2fa2a40de89376ac8d8c', '[\"*\"]', NULL, NULL, '2026-02-25 02:11:42', '2026-02-25 02:11:42'),
(106, 'App\\Models\\User', 379, 'mobile-app', 'ba33cc35929201437e6aaea848ce7f44e657931927365cbef144707d934c3d10', '[\"*\"]', NULL, NULL, '2026-02-25 04:17:15', '2026-02-25 04:17:15'),
(107, 'App\\Models\\User', 380, 'mobile-app', '47930c4117b9fe86cefb1fd0651217b424f98475bbdf88e6cd51c469212f8850', '[\"*\"]', NULL, NULL, '2026-02-25 06:01:49', '2026-02-25 06:01:49'),
(108, 'App\\Models\\User', 379, 'mobile-app', 'ff3d01e33b360fbe70dcff9552e6f7bdd55270b2abb65e2cd52158a06b6ecf82', '[\"*\"]', NULL, NULL, '2026-02-25 07:01:43', '2026-02-25 07:01:43'),
(109, 'App\\Models\\User', 380, 'mobile-app', 'd5900dcbc7ff80736749581913c83750291961bb487ffe35841473634cf30603', '[\"*\"]', NULL, NULL, '2026-02-25 23:22:18', '2026-02-25 23:22:18'),
(110, 'App\\Models\\User', 371, 'mobile-app', 'e7a20e719e96ddc5863ba4617c5bb4e8731174451f92e815e7b48f5d07e868ae', '[\"*\"]', NULL, NULL, '2026-02-26 03:43:53', '2026-02-26 03:43:53'),
(111, 'App\\Models\\User', 380, 'mobile-app', 'b475cdfc1255b967f1f74d46660a743f812cf6f8d185e180dd7bf392f04a1c75', '[\"*\"]', NULL, NULL, '2026-02-26 07:12:19', '2026-02-26 07:12:19'),
(112, 'App\\Models\\User', 372, 'mobile-app', '4f55ec7ce11edd1fafabfed25d3a4d2176410990bf85a92de936d3749e27a5f7', '[\"*\"]', NULL, NULL, '2026-02-26 07:17:25', '2026-02-26 07:17:25'),
(113, 'App\\Models\\User', 379, 'mobile-app', 'c4a91d18349d33031ad568a1d08e9f05c96481877e0116673d1eeb4058da0df4', '[\"*\"]', NULL, NULL, '2026-02-26 07:18:09', '2026-02-26 07:18:09'),
(114, 'App\\Models\\User', 380, 'mobile-app', '922d63055d9f80b182d107cf3a50be361b365fb3874abba1751ceed05bc9c8ec', '[\"*\"]', NULL, NULL, '2026-02-27 03:41:46', '2026-02-27 03:41:46'),
(115, 'App\\Models\\User', 380, 'mobile-app', 'b413ea6dfb6bff190d74a7252d7aae324c8a4abd36780d3c1db9227bebdd5a8d', '[\"*\"]', NULL, NULL, '2026-02-27 04:07:43', '2026-02-27 04:07:43'),
(116, 'App\\Models\\User', 379, 'mobile-app', 'd3a06e5cfa377e38f90340940968288a4f7974e620898d993228d5fd70025e87', '[\"*\"]', NULL, NULL, '2026-02-27 04:08:22', '2026-02-27 04:08:22'),
(117, 'App\\Models\\User', 380, 'mobile-app', '1a658f9a313249eae8bcbe5580d157791bc19cd3124c04fb3253a352d203c540', '[\"*\"]', NULL, NULL, '2026-03-01 02:50:33', '2026-03-01 02:50:33'),
(118, 'App\\Models\\User', 380, 'mobile-app', '3f9ed8abc25fce346a2628524b89e763cae1af581280175bec45cd0b1d93a877', '[\"*\"]', NULL, NULL, '2026-03-01 03:00:15', '2026-03-01 03:00:15'),
(119, 'App\\Models\\User', 371, 'mobile-app', '2d49a284d4fa04e511cfb5d09367c4941ee536f3721d421583b22aa844557daa', '[\"*\"]', NULL, NULL, '2026-03-01 03:00:55', '2026-03-01 03:00:55'),
(120, 'App\\Models\\User', 380, 'mobile-app', 'b83c8487e2a6640d898aa1312794718bb2d85cbe8cbe51962a51c7de37f31d44', '[\"*\"]', NULL, NULL, '2026-03-01 03:04:05', '2026-03-01 03:04:05');

-- --------------------------------------------------------

--
-- Table structure for table `platform_payments`
--

CREATE TABLE `platform_payments` (
  `platform_payment_id` int(11) NOT NULL,
  `subscriptionPlanId` bigint(20) UNSIGNED DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `contractor_id` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `transaction_number` varchar(100) DEFAULT NULL,
  `transaction_date` timestamp NULL DEFAULT current_timestamp(),
  `is_approved` tinyint(1) DEFAULT 0,
  `is_cancelled` tinyint(4) NOT NULL DEFAULT 0,
  `approved_by` int(11) DEFAULT NULL,
  `expiration_date` timestamp NULL DEFAULT NULL,
  `payment_type` varchar(255) DEFAULT NULL,
  `deactivation_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `platform_payments`
--

INSERT INTO `platform_payments` (`platform_payment_id`, `subscriptionPlanId`, `project_id`, `contractor_id`, `owner_id`, `amount`, `transaction_number`, `transaction_date`, `is_approved`, `is_cancelled`, `approved_by`, `expiration_date`, `payment_type`, `deactivation_reason`) VALUES
(90, 4, 1055, NULL, 1814, 49.00, 'cs_563785e374e74df22bf15ee4', '2026-02-19 00:07:16', 1, 0, NULL, '2026-02-26 00:07:16', 'PayMongo', NULL),
(91, 2, NULL, 1809, NULL, 1499.00, 'cs_88743ff4cc19941b12468aed', '2026-02-19 00:09:36', 0, 0, NULL, '2026-03-19 00:09:36', 'PayMongo', NULL),
(92, 4, 1053, NULL, 1814, 49.00, 'cs_126348af6114a956204d1202', '2026-02-19 00:14:45', 1, 0, NULL, '2026-02-26 00:14:45', 'PayMongo', NULL),
(94, 2, NULL, 1809, NULL, 1499.00, 'cs_83ccd03dcb453c420f9170d1', '2026-02-19 00:47:48', 0, 0, NULL, '2026-03-19 00:47:48', 'PayMongo', NULL),
(96, 1, NULL, 1809, NULL, 1999.00, 'cs_f8f6f0de7782cf24191133ff', '2026-02-19 01:45:57', 0, 0, NULL, '2026-03-19 01:45:57', 'PayMongo', NULL),
(97, 4, 1046, NULL, 1814, 49.00, 'cs_f5dab5282bbe15916c650ecf', '2026-02-19 01:48:17', 1, 0, NULL, '2026-02-26 01:48:17', 'PayMongo', NULL),
(99, 1, NULL, 1809, NULL, 1999.00, 'cs_a3296f7cc6cf84f80ad62ed9', '2026-02-20 04:07:32', 1, 0, NULL, '2026-03-20 04:07:32', 'PayMongo', NULL),
(100, 4, 1055, NULL, 1814, 49.00, 'cs_0fd13e153e20ec8cb6f70f59', '2026-02-26 03:44:59', 0, 0, NULL, '2026-03-05 03:44:59', 'PayMongo', NULL),
(102, 1, NULL, 1810, NULL, 1998.00, 'cs_13026498638949aee27b4ed0', '2026-02-28 09:17:11', 0, 0, NULL, '2026-03-28 09:17:11', 'PayMongo', NULL),
(103, 1, NULL, 1810, NULL, 1922.00, 'cs_91be49e8101b4de595ca80e1', '2026-02-28 10:48:45', 0, 0, NULL, '2026-03-28 10:48:45', 'PayMongo', NULL),
(104, 4, 1055, NULL, 1814, 50.00, 'cs_757175f49a65fe00ed93f6a1', '2026-02-28 19:54:35', 0, 0, NULL, '2026-03-07 19:54:35', 'PayMongo', NULL),
(105, 4, 1053, NULL, 1814, 50.00, 'cs_b2aefb8d3774a5ead6bb1ebc', '2026-02-28 19:54:56', 0, 0, NULL, '2026-03-07 19:54:56', 'PayMongo', NULL),
(106, 4, 1053, NULL, 1814, 50.00, 'cs_cdddf7af73dd78638f5e612c', '2026-02-28 20:22:14', 0, 0, NULL, '2026-03-07 20:22:14', 'PayMongo', NULL),
(107, 4, 1053, NULL, 1814, 50.00, 'cs_e40d945f9857e3d24b0d3a5c', '2026-02-28 20:24:16', 0, 0, NULL, '2026-03-07 20:24:16', 'PayMongo', NULL),
(109, 1, NULL, 1810, NULL, 1922.00, 'cs_398b263e5abb936979d7acac', '2026-02-28 20:40:09', 0, 0, NULL, '2026-03-31 20:40:09', 'PayMongo', NULL),
(112, 5, 1053, NULL, 1814, 500.00, 'cs_e0f022a5780370410c5dc44d', '2026-02-28 20:47:55', 0, 0, NULL, '2026-03-07 20:47:55', 'PayMongo', NULL),
(113, 4, 1055, NULL, 1814, 50.00, 'cs_b035f977f7e1ceb3423e07b9', '2026-02-28 20:48:20', 0, 0, NULL, '2026-03-07 20:48:20', 'PayMongo', NULL),
(114, 7, 1053, NULL, 1814, 213.00, 'cs_9f3da1dd54eb1f9496639344', '2026-02-28 21:29:58', 0, 0, NULL, '2026-03-07 21:29:58', 'PayMongo', NULL),
(115, 7, 1053, NULL, 1814, 213.00, 'cs_858e76b43f30cc4f8dbaff42', '2026-02-28 21:45:38', 0, 0, NULL, '2026-03-07 21:45:38', 'PayMongo', NULL),
(116, 4, 1053, NULL, 1814, 49.00, 'cs_3bc2b1faa70a3d9bf5ee9a52', '2026-02-28 21:57:49', 0, 1, NULL, '2026-03-10 21:57:49', 'PayMongo', 'ssssssssssssssssssssssssssssss'),
(117, 1, NULL, 1810, NULL, 1999.00, 'cs_51636cac5dcc8d7f869c53fd', '2026-02-28 21:58:45', 1, 0, NULL, '2026-03-12 21:58:45', 'PayMongo', NULL),
(118, 4, 1060, NULL, 1814, 49.00, 'cs_e06594950d7ece5c663d2f0f', '2026-03-01 03:01:17', 1, 0, NULL, '2026-03-08 03:01:17', 'PayMongo', NULL);

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
(813, 2690, 'Update', 'submitted', NULL, '2025-12-15 07:49:09', NULL),
(814, 2767, 'Progress report 1', 'approved', NULL, '2025-12-17 23:04:14', NULL),
(815, 2767, 'Progress Report 2', 'rejected', 'Wala lang', '2025-12-18 00:33:55', NULL),
(816, 2767, 'Progresss report the 3rd', 'approved', NULL, '2025-12-18 15:04:26', NULL),
(817, 2768, 'test', 'approved', NULL, '2025-12-18 17:40:29', NULL),
(818, 2769, 'testung', 'approved', NULL, '2025-12-18 17:41:16', NULL),
(819, 2770, 'tustent', 'approved', NULL, '2025-12-18 17:41:28', NULL),
(820, 2771, 'testinggg', 'approved', NULL, '2025-12-18 17:41:43', NULL),
(821, 2772, 'gig kg kgxkgx', 'approved', NULL, '2025-12-19 04:12:01', NULL),
(822, 2772, 'isosjsiaoa', 'rejected', 'kalanans', '2025-12-19 04:16:24', NULL),
(823, 2773, 'jsssgstsntns', 'approved', NULL, '2025-12-19 04:26:36', NULL),
(824, 2774, '1s', 'rejected', 'kakaksns', '2025-12-19 05:11:46', NULL),
(825, 2774, 'For the dispute', 'approved', NULL, '2025-12-19 05:14:27', NULL),
(826, 2775, '2nd and last', 'approved', NULL, '2025-12-19 05:23:48', NULL),
(827, 2776, 'Progress report 1', 'rejected', 'Di nagbabayad', '2025-12-19 08:01:16', NULL),
(828, 2776, 'Resolcing the Issue Report', 'approved', NULL, '2025-12-19 08:07:06', NULL),
(829, 2778, 'may nangyayare na', 'approved', NULL, '2026-01-25 08:22:31', NULL),
(830, 2779, 'para', 'approved', NULL, '2026-01-25 08:27:26', NULL),
(831, 2780, 'matapos na', 'approved', NULL, '2026-01-25 08:27:37', NULL),
(832, 2790, 'nagsisimula na', 'approved', NULL, '2026-02-23 12:29:48', NULL),
(833, 2791, 'hakding', 'approved', NULL, '2026-02-23 12:47:56', NULL);

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

--
-- Dumping data for table `progress_files`
--

INSERT INTO `progress_files` (`file_id`, `progress_id`, `file_path`, `original_name`) VALUES
(1, 814, 'progress_uploads/1766012654_694336ee9bec8.jpg', 'Screenshot_20251218-063857.jpg'),
(2, 814, 'progress_uploads/1766012654_694336ee9f295.jpg', 'Screenshot_20251218-063854.jpg'),
(3, 814, 'progress_uploads/1766012654_694336eea235a.pdf', 'Week-10-11-IT-131-Views-and-Triggers.pdf'),
(4, 815, 'progress_uploads/1766018036_69434bf4c3250.docx', 'Document%20(1).docx'),
(5, 816, 'progress_uploads/1766070266_694417fa0b598.docx', 'Document%20(1).docx'),
(6, 816, 'progress_uploads/1766070266_694417fa0bd84.jpg', 'IMG_20251218_222044_989.jpg'),
(7, 817, 'progress_uploads/1766079629_69443c8dc1693.jpg', 'IMG_20251218_222044_989.jpg'),
(8, 818, 'progress_uploads/1766079676_69443cbc78d76.jpg', 'IMG_20251218_222044_989.jpg'),
(9, 819, 'progress_uploads/1766079689_69443cc90018b.jpg', 'IMG_20251218_222044_989.jpg'),
(10, 820, 'progress_uploads/1766079703_69443cd7eb4b8.jpg', 'IMG_20251218_222044_989.jpg'),
(11, 821, 'progress_uploads/1766117523_6944d0931c4b5.jpg', 'Screenshot_20251219-085607.jpg'),
(12, 822, 'progress_uploads/1766117786_6944d19a3dbbf.jpg', 'Screenshot_20251219-085607.jpg'),
(13, 823, 'progress_uploads/1766118396_6944d3fc6193a.jpg', 'Screenshot_20251219-085607.jpg'),
(14, 824, 'progress_uploads/1766121106_6944de92ced40.jpg', '20251219_124515.jpg'),
(15, 825, 'progress_uploads/1766121267_6944df33d416b.jpg', '20251219_124514.jpg'),
(16, 826, 'progress_uploads/1766121829_6944e16503c66.jpg', '20251219_124514.jpg'),
(17, 826, 'progress_uploads/1766121829_6944e16506150.jpg', '20251219_124514.jpg'),
(18, 827, 'progress_uploads/1766131276_6945064c816ae.jpg', 'Document%204_2.jpg'),
(19, 828, 'progress_uploads/1766131626_694507aa9e295.jpg', 'Document%204_2.jpg'),
(20, 829, 'progress_uploads/1769329352_6975d2c80bf4b.jpg', 'Screenshot_20260125-154318.jpg'),
(21, 830, 'progress_uploads/1769329646_6975d3ee8ae24.jpg', 'Screenshot_20260125-000540.jpg'),
(22, 831, 'progress_uploads/1769329657_6975d3f9d6267.jpg', 'Screenshot_20260125-154318.jpg'),
(23, 832, 'progress_uploads/1771849789_699c483d7a0e4.jpg', 'Screenshot_20260223-110715.jpg'),
(24, 833, 'progress_uploads/1771850876_699c4c7c9a358.jpg', 'Screenshot_20260223-110715.jpg');

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
  `project_status` enum('open','bidding_closed','in_progress','completed','terminated','deleted_post','halt','deleted') DEFAULT 'open',
  `previous_status` varchar(50) DEFAULT NULL,
  `stat_reason` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `selected_contractor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`project_id`, `relationship_id`, `project_title`, `project_description`, `project_location`, `budget_range_min`, `budget_range_max`, `lot_size`, `floor_area`, `property_type`, `type_id`, `if_others_ctype`, `to_finish`, `project_status`, `previous_status`, `stat_reason`, `remarks`, `selected_contractor_id`) VALUES
(985, 987, 'Project 987', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'terminated', NULL, '', '', NULL),
(986, 988, 'Project 988', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, NULL, '', NULL),
(987, 989, 'Project 989', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(988, 990, 'Project 990', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(989, 994, 'Project 994', 'Description text.', 'Pasonanca, Zamboanga City', 1000000.00, 1500000.00, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(990, 995, 'Project 995', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(991, 997, 'Project 997', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(992, 998, 'Project 998', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(993, 999, 'Project 999', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(994, 1000, 'Project 1000', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(995, 1003, 'Project 1003', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, NULL, '', NULL),
(996, 1005, 'Project 1005', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(997, 1010, 'Project 1010', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(998, 1012, 'Project 1012', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(999, 1013, 'Project 1013', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(1000, 1018, 'Project 1018', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(1001, 1019, 'Project 1019', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(1002, 1020, 'Project 1020', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(1003, 1021, 'Project 1021', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(1004, 1022, 'Project 1022', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(1005, 1023, 'Project 1023', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(1006, 1024, 'Project 1024', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(1007, 1027, 'Project 1027', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, NULL, '', NULL),
(1008, 1032, 'Project 1032', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(1009, 1034, 'Project 1034', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(1010, 1035, 'Project 1035', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(1011, 1036, 'Project 1036', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(1012, 1037, 'Project 1037', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(1013, 1038, 'Project 1038', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(1014, 1042, 'Project 1042', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL),
(1015, 1017, 'Project 1017', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1690),
(1016, 1030, 'Project 1030', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1694),
(1017, 1025, 'Project 1025', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1700),
(1018, 1014, 'Project 1014', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1702),
(1019, 1040, 'Project 1040', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1702),
(1020, 991, 'Project 991', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1707),
(1021, 1004, 'Project 1004', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1715),
(1022, 1011, 'Project 1011', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1717),
(1023, 1026, 'Project 1026', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1717),
(1024, 1001, 'Project 1001', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1718),
(1025, 1043, 'Project 1043', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1718),
(1026, 1002, 'Project 1002', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1725),
(1027, 986, 'Project 986', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, NULL, '', 1727),
(1028, 1041, 'Project 1041', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1730),
(1029, 1008, 'Project 1008', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1733),
(1030, 1009, 'Project 1009', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1735),
(1031, 1039, 'Project 1039', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1739),
(1032, 1044, 'Project 1044', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1740),
(1033, 985, 'Project 985', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1745),
(1034, 1029, 'Project 1029', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1755),
(1035, 1028, 'Project 1028', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1763),
(1036, 1007, 'Project 1007', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1764),
(1037, 1031, 'Project 1031', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1768),
(1038, 1033, 'Project 1033', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1773),
(1039, 996, 'Project 996', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1777),
(1040, 1015, 'Project 1015', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1787),
(1041, 993, 'Project 993', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1789),
(1042, 992, 'Project 992', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1792),
(1043, 1006, 'Project 1006', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1794),
(1044, 1016, 'Project 1016', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, NULL, '', 1798),
(1045, 1045, 'Test Project', 'Test description', 'Anywhere , Ayala, Zamboanga City, Zamboanga del Sur', 50000000.00, 60000000.00, 500, 450, 'Residential', 6, NULL, NULL, 'open', NULL, NULL, '', 1809),
(1046, 1046, 'PROJECT FOR BID', 'Project test for bid', 'Anywhere, Arena Blanco, Zamboanga City, Zamboanga del Sur', 77000000.00, 80000000.00, 600, 550, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1687),
(1047, 1047, 'Testz', 'twstw', 'City of Zamboanga, Zamboanga Del Sur Sur', 5000.00, 6898.00, 64649, 94649, 'Residential', 8, NULL, NULL, 'bidding_closed', 'in_progress', 'wwwwwwwwwwwwwwwwwww', 'wwwwwwwwwwwwwwwwwwwww', 1810),
(1048, 1048, 'noche buena', 'uwi na pls', 'anywhere, Baluno, Zamboanga City, Zamboanga del Sur', 20000000.00, 30000000.00, 5000000, 4444444, 'Residential', 2, NULL, NULL, 'open', NULL, '', '', 1809),
(1049, 1049, 'Project', 'Test', 'Porcentro, Tumaga, Zamboanga City, Zamboanga del Sur', 50000000.00, 60000000.00, 500, 250, 'Residential', 6, NULL, NULL, 'open', NULL, '', '', 1809),
(1050, NULL, 'Test3', 'Test 3 Description', 'Street There, Arena Blanco, Zamboanga City, Zamboanga del Sur', 2500000.00, 5000000.00, 500, 250, 'Residential', 6, NULL, NULL, 'open', NULL, '', '', NULL),
(1051, NULL, 'Testing for Form', 'Form Description', 'Somewhere There, Arena Blanco, Zamboanga City, Zamboanga del Sur', 2000000.00, 5000000.00, 500, 250, 'Residential', 6, NULL, NULL, 'open', NULL, '', '', NULL),
(1052, 1052, 'Project Images Testing', 'Testing Images if it would show', 'Somewhere , Arena Blanco, Zamboanga City, Zamboanga del Sur', 2000000.00, 5000000.00, 500, 250, 'Residential', 6, NULL, NULL, 'open', NULL, '', '', NULL),
(1053, 1053, 'Testing again', 'tws', 'jakana, Arena Blanco, Zamboanga City, Zamboanga del Sur', 250.00, 500.00, 500, 250, 'Residential', 6, NULL, NULL, 'open', NULL, '', '', NULL),
(1054, 1054, 'Test Project Posting', 'Test Projects posting pages and flow', 'Anywhere There, Zamboanga Del Sur Sur', 25000000.00, 50000000.00, 500, 250, 'Residential', 5, NULL, NULL, 'halt', NULL, 'status reason ng isang nigger', 'si carl di nagbabayad banned ka na dito boi', 1809),
(1055, 1055, 'jslaabxxbxsssss', 'abahajaa', 'bzzjsskaaka, Zamboanga Del Sur Sur', 2433867.00, 6434994.00, 1001, 901, 'Residential', 2, NULL, NULL, 'open', NULL, NULL, '', NULL),
(1056, 1056, 'Commercial Building', 'ffrjookedowkjwwojdpjeonwozkwkspsw', '456 Oak Street Apt , Arena Blanco, Zamboanga City, Zamboanga del Sur', 25000000.00, 50000000.00, 500, 350, 'Commercial', 6, NULL, NULL, 'in_progress', NULL, NULL, NULL, 1810),
(1057, 1057, 'Try Bidding Here', 'This post is posted for testing the bidding Ranking system', 'Street There 143, Purok 67, Arena Blanco, Zamboanga City, Zamboanga del Sur', 50000000.00, 100000000.00, 1000, 800, 'Residential', 6, NULL, NULL, 'open', NULL, NULL, NULL, NULL),
(1058, 1058, 'For Boosting Purposes Test 1', 'Yes it is for Boosting Purposes testing Post 1', 'Somewhere Street, 069 Avenue, Baliwasan, Zamboanga City, Zamboanga del Sur', 70000000.00, 80000000.00, 1000, 800, 'Agricultural', 8, NULL, NULL, 'open', NULL, NULL, NULL, NULL),
(1059, 1059, 'For Boosting Purposes Test 2', 'Yes it is for Boosting Purposes testing Post 2', 'Somewhere Street, 069 Avenue, Baliwasan, Zamboanga City, Zamboanga del Sur', 70000000.00, 80000000.00, 1000, 800, 'Residential', 8, NULL, NULL, 'open', NULL, NULL, NULL, NULL),
(1060, 1060, 'Project Post Boosting Test Post 3', 'Project Post Boosting is the Purpose of this 3', 'Anywhere Street, Purok Dyan lang, Baluno, Zamboanga City, Zamboanga del Sur', 80000000.00, 90000000.00, 1000, 800, 'Residential', 5, NULL, NULL, 'open', NULL, NULL, NULL, NULL);

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

--
-- Dumping data for table `project_files`
--

INSERT INTO `project_files` (`file_id`, `project_id`, `file_type`, `file_path`, `uploaded_at`) VALUES
(30, 986, 'building permit', 'project_files/building_permit/intro_986_0.png', '2026-02-16 01:08:15'),
(31, 986, 'title', 'project_files/titles/intro_986_1.png', '2026-02-16 01:08:15'),
(32, 986, 'blueprint', 'project_files/blueprints/intro_986_2.png', '2026-02-16 01:08:15'),
(33, 986, 'desired design', 'project_files/designs/intro_986_3.png', '2026-02-16 01:08:15'),
(34, 986, 'others', 'project_files/others/intro_986_4.png', '2026-02-16 01:08:15'),
(35, 986, 'others', 'project_files/others/intro_986_5.png', '2026-02-16 01:08:15'),
(36, 987, 'building permit', 'project_files/building_permit/intro_987_0.png', '2026-02-16 01:08:15'),
(37, 987, 'title', 'project_files/titles/intro_987_1.png', '2026-02-16 01:08:15'),
(38, 987, 'blueprint', 'project_files/blueprints/intro_987_2.png', '2026-02-16 01:08:16'),
(39, 987, 'desired design', 'project_files/designs/intro_987_3.png', '2026-02-16 01:08:16'),
(40, 987, 'others', 'project_files/others/intro_987_4.png', '2026-02-16 01:08:16'),
(41, 987, 'others', 'project_files/others/intro_987_5.png', '2026-02-16 01:08:16'),
(42, 988, 'building permit', 'project_files/building_permit/intro_988_0.png', '2026-02-16 01:08:16'),
(43, 988, 'title', 'project_files/titles/intro_988_1.png', '2026-02-16 01:08:16'),
(44, 988, 'blueprint', 'project_files/blueprints/intro_988_2.png', '2026-02-16 01:08:16'),
(45, 988, 'desired design', 'project_files/designs/intro_988_3.png', '2026-02-16 01:08:16'),
(46, 988, 'others', 'project_files/others/intro_988_4.png', '2026-02-16 01:08:16'),
(47, 988, 'others', 'project_files/others/intro_988_5.png', '2026-02-16 01:08:16'),
(48, 989, 'building permit', 'project_files/building_permit/intro_989_0.png', '2026-02-16 01:08:16'),
(49, 989, 'title', 'project_files/titles/intro_989_1.png', '2026-02-16 01:08:16'),
(50, 989, 'blueprint', 'project_files/blueprints/intro_989_2.png', '2026-02-16 01:08:16'),
(51, 989, 'desired design', 'project_files/designs/intro_989_3.png', '2026-02-16 01:08:16'),
(52, 989, 'others', 'project_files/others/intro_989_4.png', '2026-02-16 01:08:16'),
(53, 989, 'others', 'project_files/others/intro_989_5.png', '2026-02-16 01:08:16'),
(54, 990, 'building permit', 'project_files/building_permit/intro_990_0.png', '2026-02-16 01:08:16'),
(55, 990, 'title', 'project_files/titles/intro_990_1.png', '2026-02-16 01:08:16'),
(56, 990, 'blueprint', 'project_files/blueprints/intro_990_2.png', '2026-02-16 01:08:16'),
(57, 990, 'desired design', 'project_files/designs/intro_990_3.png', '2026-02-16 01:08:16'),
(58, 990, 'others', 'project_files/others/intro_990_4.png', '2026-02-16 01:08:16'),
(59, 990, 'others', 'project_files/others/intro_990_5.png', '2026-02-16 01:08:16'),
(60, 991, 'building permit', 'project_files/building_permit/intro_991_0.png', '2026-02-16 01:08:16'),
(61, 991, 'title', 'project_files/titles/intro_991_1.png', '2026-02-16 01:08:16'),
(62, 991, 'blueprint', 'project_files/blueprints/intro_991_2.png', '2026-02-16 01:08:16'),
(63, 991, 'desired design', 'project_files/designs/intro_991_3.png', '2026-02-16 01:08:16'),
(64, 991, 'others', 'project_files/others/intro_991_4.png', '2026-02-16 01:08:16'),
(65, 991, 'others', 'project_files/others/intro_991_5.png', '2026-02-16 01:08:16'),
(66, 992, 'building permit', 'project_files/building_permit/intro_992_0.png', '2026-02-16 01:08:16'),
(67, 992, 'title', 'project_files/titles/intro_992_1.png', '2026-02-16 01:08:16'),
(68, 992, 'blueprint', 'project_files/blueprints/intro_992_2.png', '2026-02-16 01:08:16'),
(69, 992, 'desired design', 'project_files/designs/intro_992_3.png', '2026-02-16 01:08:16'),
(70, 992, 'others', 'project_files/others/intro_992_4.png', '2026-02-16 01:08:16'),
(71, 992, 'others', 'project_files/others/intro_992_5.png', '2026-02-16 01:08:16'),
(72, 993, 'building permit', 'project_files/building_permit/intro_993_0.png', '2026-02-16 01:08:16'),
(73, 993, 'title', 'project_files/titles/intro_993_1.png', '2026-02-16 01:08:16'),
(74, 993, 'blueprint', 'project_files/blueprints/intro_993_2.png', '2026-02-16 01:08:16'),
(75, 993, 'desired design', 'project_files/designs/intro_993_3.png', '2026-02-16 01:08:16'),
(76, 993, 'others', 'project_files/others/intro_993_4.png', '2026-02-16 01:08:16'),
(77, 993, 'others', 'project_files/others/intro_993_5.png', '2026-02-16 01:08:16'),
(78, 994, 'building permit', 'project_files/building_permit/intro_994_0.png', '2026-02-16 01:08:16'),
(79, 994, 'title', 'project_files/titles/intro_994_1.png', '2026-02-16 01:08:16'),
(80, 994, 'blueprint', 'project_files/blueprints/intro_994_2.png', '2026-02-16 01:08:16'),
(81, 994, 'desired design', 'project_files/designs/intro_994_3.png', '2026-02-16 01:08:16'),
(82, 994, 'others', 'project_files/others/intro_994_4.png', '2026-02-16 01:08:16'),
(83, 994, 'others', 'project_files/others/intro_994_5.png', '2026-02-16 01:08:16'),
(84, 995, 'building permit', 'project_files/building_permit/intro_995_0.png', '2026-02-16 01:08:16'),
(85, 995, 'title', 'project_files/titles/intro_995_1.png', '2026-02-16 01:08:16'),
(86, 995, 'blueprint', 'project_files/blueprints/intro_995_2.png', '2026-02-16 01:08:16'),
(87, 995, 'desired design', 'project_files/designs/intro_995_3.png', '2026-02-16 01:08:16'),
(88, 995, 'others', 'project_files/others/intro_995_4.png', '2026-02-16 01:08:16'),
(89, 995, 'others', 'project_files/others/intro_995_5.png', '2026-02-16 01:08:16'),
(90, 996, 'building permit', 'project_files/building_permit/intro_996_0.png', '2026-02-16 01:08:16'),
(91, 996, 'title', 'project_files/titles/intro_996_1.png', '2026-02-16 01:08:16'),
(92, 996, 'blueprint', 'project_files/blueprints/intro_996_2.png', '2026-02-16 01:08:16'),
(93, 996, 'desired design', 'project_files/designs/intro_996_3.png', '2026-02-16 01:08:16'),
(94, 996, 'others', 'project_files/others/intro_996_4.png', '2026-02-16 01:08:16'),
(95, 996, 'others', 'project_files/others/intro_996_5.png', '2026-02-16 01:08:16'),
(96, 997, 'building permit', 'project_files/building_permit/intro_997_0.png', '2026-02-16 01:08:16'),
(97, 997, 'title', 'project_files/titles/intro_997_1.png', '2026-02-16 01:08:16'),
(98, 997, 'blueprint', 'project_files/blueprints/intro_997_2.png', '2026-02-16 01:08:16'),
(99, 997, 'desired design', 'project_files/designs/intro_997_3.png', '2026-02-16 01:08:16'),
(100, 997, 'others', 'project_files/others/intro_997_4.png', '2026-02-16 01:08:16'),
(101, 997, 'others', 'project_files/others/intro_997_5.png', '2026-02-16 01:08:16'),
(102, 998, 'building permit', 'project_files/building_permit/intro_998_0.png', '2026-02-16 01:08:16'),
(103, 998, 'title', 'project_files/titles/intro_998_1.png', '2026-02-16 01:08:16'),
(104, 998, 'blueprint', 'project_files/blueprints/intro_998_2.png', '2026-02-16 01:08:16'),
(105, 998, 'desired design', 'project_files/designs/intro_998_3.png', '2026-02-16 01:08:16'),
(106, 998, 'others', 'project_files/others/intro_998_4.png', '2026-02-16 01:08:16'),
(107, 998, 'others', 'project_files/others/intro_998_5.png', '2026-02-16 01:08:16'),
(108, 999, 'building permit', 'project_files/building_permit/intro_999_0.png', '2026-02-16 01:08:16'),
(109, 999, 'title', 'project_files/titles/intro_999_1.png', '2026-02-16 01:08:16'),
(110, 999, 'blueprint', 'project_files/blueprints/intro_999_2.png', '2026-02-16 01:08:16'),
(111, 999, 'desired design', 'project_files/designs/intro_999_3.png', '2026-02-16 01:08:16'),
(112, 999, 'others', 'project_files/others/intro_999_4.png', '2026-02-16 01:08:16'),
(113, 999, 'others', 'project_files/others/intro_999_5.png', '2026-02-16 01:08:16'),
(114, 1000, 'building permit', 'project_files/building_permit/intro_1000_0.png', '2026-02-16 01:08:16'),
(115, 1000, 'title', 'project_files/titles/intro_1000_1.png', '2026-02-16 01:08:16'),
(116, 1000, 'blueprint', 'project_files/blueprints/intro_1000_2.png', '2026-02-16 01:08:16'),
(117, 1000, 'desired design', 'project_files/designs/intro_1000_3.png', '2026-02-16 01:08:16'),
(118, 1000, 'others', 'project_files/others/intro_1000_4.png', '2026-02-16 01:08:16'),
(119, 1000, 'others', 'project_files/others/intro_1000_5.png', '2026-02-16 01:08:16'),
(120, 1001, 'building permit', 'project_files/building_permit/intro_1001_0.png', '2026-02-16 01:08:16'),
(121, 1001, 'title', 'project_files/titles/intro_1001_1.png', '2026-02-16 01:08:16'),
(122, 1001, 'blueprint', 'project_files/blueprints/intro_1001_2.png', '2026-02-16 01:08:16'),
(123, 1001, 'desired design', 'project_files/designs/intro_1001_3.png', '2026-02-16 01:08:16'),
(124, 1001, 'others', 'project_files/others/intro_1001_4.png', '2026-02-16 01:08:16'),
(125, 1001, 'others', 'project_files/others/intro_1001_5.png', '2026-02-16 01:08:16'),
(126, 1002, 'building permit', 'project_files/building_permit/intro_1002_0.png', '2026-02-16 01:08:16'),
(127, 1002, 'title', 'project_files/titles/intro_1002_1.png', '2026-02-16 01:08:16'),
(128, 1002, 'blueprint', 'project_files/blueprints/intro_1002_2.png', '2026-02-16 01:08:16'),
(129, 1002, 'desired design', 'project_files/designs/intro_1002_3.png', '2026-02-16 01:08:16'),
(130, 1002, 'others', 'project_files/others/intro_1002_4.png', '2026-02-16 01:08:16'),
(131, 1002, 'others', 'project_files/others/intro_1002_5.png', '2026-02-16 01:08:16'),
(132, 1003, 'building permit', 'project_files/building_permit/intro_1003_0.png', '2026-02-16 01:08:16'),
(133, 1003, 'title', 'project_files/titles/intro_1003_1.png', '2026-02-16 01:08:16'),
(134, 1003, 'blueprint', 'project_files/blueprints/intro_1003_2.png', '2026-02-16 01:08:16'),
(135, 1003, 'desired design', 'project_files/designs/intro_1003_3.png', '2026-02-16 01:08:16'),
(136, 1003, 'others', 'project_files/others/intro_1003_4.png', '2026-02-16 01:08:16'),
(137, 1003, 'others', 'project_files/others/intro_1003_5.png', '2026-02-16 01:08:16'),
(138, 1004, 'building permit', 'project_files/building_permit/intro_1004_0.png', '2026-02-16 01:08:16'),
(139, 1004, 'title', 'project_files/titles/intro_1004_1.png', '2026-02-16 01:08:16'),
(140, 1004, 'blueprint', 'project_files/blueprints/intro_1004_2.png', '2026-02-16 01:08:16'),
(141, 1004, 'desired design', 'project_files/designs/intro_1004_3.png', '2026-02-16 01:08:16'),
(142, 1004, 'others', 'project_files/others/intro_1004_4.png', '2026-02-16 01:08:16'),
(143, 1004, 'others', 'project_files/others/intro_1004_5.png', '2026-02-16 01:08:16'),
(144, 1005, 'building permit', 'project_files/building_permit/intro_1005_0.png', '2026-02-16 01:08:16'),
(145, 1005, 'title', 'project_files/titles/intro_1005_1.png', '2026-02-16 01:08:16'),
(146, 1005, 'blueprint', 'project_files/blueprints/intro_1005_2.png', '2026-02-16 01:08:16'),
(147, 1005, 'desired design', 'project_files/designs/intro_1005_3.png', '2026-02-16 01:08:16'),
(148, 1005, 'others', 'project_files/others/intro_1005_4.png', '2026-02-16 01:08:16'),
(149, 1005, 'others', 'project_files/others/intro_1005_5.png', '2026-02-16 01:08:16'),
(150, 1006, 'building permit', 'project_files/building_permit/intro_1006_0.png', '2026-02-16 01:08:16'),
(151, 1006, 'title', 'project_files/titles/intro_1006_1.png', '2026-02-16 01:08:16'),
(152, 1006, 'blueprint', 'project_files/blueprints/intro_1006_2.png', '2026-02-16 01:08:16'),
(153, 1006, 'desired design', 'project_files/designs/intro_1006_3.png', '2026-02-16 01:08:16'),
(154, 1006, 'others', 'project_files/others/intro_1006_4.png', '2026-02-16 01:08:16'),
(155, 1006, 'others', 'project_files/others/intro_1006_5.png', '2026-02-16 01:08:16'),
(156, 1007, 'building permit', 'project_files/building_permit/intro_1007_0.png', '2026-02-16 01:08:16'),
(157, 1007, 'title', 'project_files/titles/intro_1007_1.png', '2026-02-16 01:08:16'),
(158, 1007, 'blueprint', 'project_files/blueprints/intro_1007_2.png', '2026-02-16 01:08:16'),
(159, 1007, 'desired design', 'project_files/designs/intro_1007_3.png', '2026-02-16 01:08:16'),
(160, 1007, 'others', 'project_files/others/intro_1007_4.png', '2026-02-16 01:08:16'),
(161, 1007, 'others', 'project_files/others/intro_1007_5.png', '2026-02-16 01:08:16'),
(162, 1008, 'building permit', 'project_files/building_permit/intro_1008_0.png', '2026-02-16 01:08:16'),
(163, 1008, 'title', 'project_files/titles/intro_1008_1.png', '2026-02-16 01:08:16'),
(164, 1008, 'blueprint', 'project_files/blueprints/intro_1008_2.png', '2026-02-16 01:08:16'),
(165, 1008, 'desired design', 'project_files/designs/intro_1008_3.png', '2026-02-16 01:08:16'),
(166, 1008, 'others', 'project_files/others/intro_1008_4.png', '2026-02-16 01:08:16'),
(167, 1008, 'others', 'project_files/others/intro_1008_5.png', '2026-02-16 01:08:16'),
(168, 1009, 'building permit', 'project_files/building_permit/intro_1009_0.png', '2026-02-16 01:08:16'),
(169, 1009, 'title', 'project_files/titles/intro_1009_1.png', '2026-02-16 01:08:16'),
(170, 1009, 'blueprint', 'project_files/blueprints/intro_1009_2.png', '2026-02-16 01:08:16'),
(171, 1009, 'desired design', 'project_files/designs/intro_1009_3.png', '2026-02-16 01:08:16'),
(172, 1009, 'others', 'project_files/others/intro_1009_4.png', '2026-02-16 01:08:16'),
(173, 1009, 'others', 'project_files/others/intro_1009_5.png', '2026-02-16 01:08:16'),
(174, 1010, 'building permit', 'project_files/building_permit/intro_1010_0.png', '2026-02-16 01:08:16'),
(175, 1010, 'title', 'project_files/titles/intro_1010_1.png', '2026-02-16 01:08:16'),
(176, 1010, 'blueprint', 'project_files/blueprints/intro_1010_2.png', '2026-02-16 01:08:16'),
(177, 1010, 'desired design', 'project_files/designs/intro_1010_3.png', '2026-02-16 01:08:16'),
(178, 1010, 'others', 'project_files/others/intro_1010_4.png', '2026-02-16 01:08:16'),
(179, 1010, 'others', 'project_files/others/intro_1010_5.png', '2026-02-16 01:08:16'),
(180, 1011, 'building permit', 'project_files/building_permit/intro_1011_0.png', '2026-02-16 01:08:16'),
(181, 1011, 'title', 'project_files/titles/intro_1011_1.png', '2026-02-16 01:08:16'),
(182, 1011, 'blueprint', 'project_files/blueprints/intro_1011_2.png', '2026-02-16 01:08:17'),
(183, 1011, 'desired design', 'project_files/designs/intro_1011_3.png', '2026-02-16 01:08:17'),
(184, 1011, 'others', 'project_files/others/intro_1011_4.png', '2026-02-16 01:08:17'),
(185, 1011, 'others', 'project_files/others/intro_1011_5.png', '2026-02-16 01:08:17'),
(186, 1012, 'building permit', 'project_files/building_permit/intro_1012_0.png', '2026-02-16 01:08:17'),
(187, 1012, 'title', 'project_files/titles/intro_1012_1.png', '2026-02-16 01:08:17'),
(188, 1012, 'blueprint', 'project_files/blueprints/intro_1012_2.png', '2026-02-16 01:08:17'),
(189, 1012, 'desired design', 'project_files/designs/intro_1012_3.png', '2026-02-16 01:08:17'),
(190, 1012, 'others', 'project_files/others/intro_1012_4.png', '2026-02-16 01:08:17'),
(191, 1012, 'others', 'project_files/others/intro_1012_5.png', '2026-02-16 01:08:17'),
(192, 1013, 'building permit', 'project_files/building_permit/intro_1013_0.png', '2026-02-16 01:08:17'),
(193, 1013, 'title', 'project_files/titles/intro_1013_1.png', '2026-02-16 01:08:17'),
(194, 1013, 'blueprint', 'project_files/blueprints/intro_1013_2.png', '2026-02-16 01:08:17'),
(195, 1013, 'desired design', 'project_files/designs/intro_1013_3.png', '2026-02-16 01:08:17'),
(196, 1013, 'others', 'project_files/others/intro_1013_4.png', '2026-02-16 01:08:17'),
(197, 1013, 'others', 'project_files/others/intro_1013_5.png', '2026-02-16 01:08:17'),
(198, 1014, 'building permit', 'project_files/building_permit/intro_1014_0.png', '2026-02-16 01:08:17'),
(199, 1014, 'title', 'project_files/titles/intro_1014_1.png', '2026-02-16 01:08:17'),
(200, 1014, 'blueprint', 'project_files/blueprints/intro_1014_2.png', '2026-02-16 01:08:17'),
(201, 1014, 'desired design', 'project_files/designs/intro_1014_3.png', '2026-02-16 01:08:17'),
(202, 1014, 'others', 'project_files/others/intro_1014_4.png', '2026-02-16 01:08:17'),
(203, 1014, 'others', 'project_files/others/intro_1014_5.png', '2026-02-16 01:08:17'),
(204, 1018, 'building permit', 'project_files/building_permit/intro_1018_0.png', '2026-02-16 01:08:17'),
(205, 1018, 'title', 'project_files/titles/intro_1018_1.png', '2026-02-16 01:08:17'),
(206, 1018, 'blueprint', 'project_files/blueprints/intro_1018_2.png', '2026-02-16 01:08:17'),
(207, 1018, 'desired design', 'project_files/designs/intro_1018_3.png', '2026-02-16 01:08:17'),
(208, 1018, 'others', 'project_files/others/intro_1018_4.png', '2026-02-16 01:08:17'),
(209, 1018, 'others', 'project_files/others/intro_1018_5.png', '2026-02-16 01:08:17'),
(210, 1021, 'building permit', 'project_files/building_permit/intro_1021_0.png', '2026-02-16 01:08:17'),
(211, 1021, 'title', 'project_files/titles/intro_1021_1.png', '2026-02-16 01:08:17'),
(212, 1021, 'blueprint', 'project_files/blueprints/intro_1021_2.png', '2026-02-16 01:08:17'),
(213, 1021, 'desired design', 'project_files/designs/intro_1021_3.png', '2026-02-16 01:08:17'),
(214, 1021, 'others', 'project_files/others/intro_1021_4.png', '2026-02-16 01:08:17'),
(215, 1021, 'others', 'project_files/others/intro_1021_5.png', '2026-02-16 01:08:17'),
(216, 1022, 'building permit', 'project_files/building_permit/intro_1022_0.png', '2026-02-16 01:08:17'),
(217, 1022, 'title', 'project_files/titles/intro_1022_1.png', '2026-02-16 01:08:17'),
(218, 1022, 'blueprint', 'project_files/blueprints/intro_1022_2.png', '2026-02-16 01:08:17'),
(219, 1022, 'desired design', 'project_files/designs/intro_1022_3.png', '2026-02-16 01:08:17'),
(220, 1022, 'others', 'project_files/others/intro_1022_4.png', '2026-02-16 01:08:17'),
(221, 1022, 'others', 'project_files/others/intro_1022_5.png', '2026-02-16 01:08:17'),
(222, 1024, 'building permit', 'project_files/building_permit/intro_1024_0.png', '2026-02-16 01:08:17'),
(223, 1024, 'title', 'project_files/titles/intro_1024_1.png', '2026-02-16 01:08:17'),
(224, 1024, 'blueprint', 'project_files/blueprints/intro_1024_2.png', '2026-02-16 01:08:17'),
(225, 1024, 'desired design', 'project_files/designs/intro_1024_3.png', '2026-02-16 01:08:17'),
(226, 1024, 'others', 'project_files/others/intro_1024_4.png', '2026-02-16 01:08:17'),
(227, 1024, 'others', 'project_files/others/intro_1024_5.png', '2026-02-16 01:08:17'),
(228, 1026, 'building permit', 'project_files/building_permit/intro_1026_0.png', '2026-02-16 01:08:17'),
(229, 1026, 'title', 'project_files/titles/intro_1026_1.png', '2026-02-16 01:08:17'),
(230, 1026, 'blueprint', 'project_files/blueprints/intro_1026_2.png', '2026-02-16 01:08:17'),
(231, 1026, 'desired design', 'project_files/designs/intro_1026_3.png', '2026-02-16 01:08:17'),
(232, 1026, 'others', 'project_files/others/intro_1026_4.png', '2026-02-16 01:08:17'),
(233, 1026, 'others', 'project_files/others/intro_1026_5.png', '2026-02-16 01:08:17'),
(234, 1027, 'building permit', 'project_files/building_permit/intro_1027_0.png', '2026-02-16 01:08:17'),
(235, 1027, 'title', 'project_files/titles/intro_1027_1.png', '2026-02-16 01:08:17'),
(236, 1027, 'blueprint', 'project_files/blueprints/intro_1027_2.png', '2026-02-16 01:08:17'),
(237, 1027, 'desired design', 'project_files/designs/intro_1027_3.png', '2026-02-16 01:08:17'),
(238, 1027, 'others', 'project_files/others/intro_1027_4.png', '2026-02-16 01:08:17'),
(239, 1027, 'others', 'project_files/others/intro_1027_5.png', '2026-02-16 01:08:17'),
(240, 1028, 'building permit', 'project_files/building_permit/intro_1028_0.png', '2026-02-16 01:08:17'),
(241, 1028, 'title', 'project_files/titles/intro_1028_1.png', '2026-02-16 01:08:17'),
(242, 1028, 'blueprint', 'project_files/blueprints/intro_1028_2.png', '2026-02-16 01:08:17'),
(243, 1028, 'desired design', 'project_files/designs/intro_1028_3.png', '2026-02-16 01:08:17'),
(244, 1028, 'others', 'project_files/others/intro_1028_4.png', '2026-02-16 01:08:17'),
(245, 1028, 'others', 'project_files/others/intro_1028_5.png', '2026-02-16 01:08:17'),
(246, 1031, 'building permit', 'project_files/building_permit/intro_1031_0.png', '2026-02-16 01:08:17'),
(247, 1031, 'title', 'project_files/titles/intro_1031_1.png', '2026-02-16 01:08:17'),
(248, 1031, 'blueprint', 'project_files/blueprints/intro_1031_2.png', '2026-02-16 01:08:17'),
(249, 1031, 'desired design', 'project_files/designs/intro_1031_3.png', '2026-02-16 01:08:17'),
(250, 1031, 'others', 'project_files/others/intro_1031_4.png', '2026-02-16 01:08:17'),
(251, 1031, 'others', 'project_files/others/intro_1031_5.png', '2026-02-16 01:08:17'),
(252, 1032, 'building permit', 'project_files/building_permit/intro_1032_0.png', '2026-02-16 01:08:17'),
(253, 1032, 'title', 'project_files/titles/intro_1032_1.png', '2026-02-16 01:08:17'),
(254, 1032, 'blueprint', 'project_files/blueprints/intro_1032_2.png', '2026-02-16 01:08:17'),
(255, 1032, 'desired design', 'project_files/designs/intro_1032_3.png', '2026-02-16 01:08:17'),
(256, 1032, 'others', 'project_files/others/intro_1032_4.png', '2026-02-16 01:08:17'),
(257, 1032, 'others', 'project_files/others/intro_1032_5.png', '2026-02-16 01:08:17'),
(258, 1035, 'building permit', 'project_files/building_permit/intro_1035_0.png', '2026-02-16 01:08:17'),
(259, 1035, 'title', 'project_files/titles/intro_1035_1.png', '2026-02-16 01:08:17'),
(260, 1035, 'blueprint', 'project_files/blueprints/intro_1035_2.png', '2026-02-16 01:08:17'),
(261, 1035, 'desired design', 'project_files/designs/intro_1035_3.png', '2026-02-16 01:08:17'),
(262, 1035, 'others', 'project_files/others/intro_1035_4.png', '2026-02-16 01:08:17'),
(263, 1035, 'others', 'project_files/others/intro_1035_5.png', '2026-02-16 01:08:17'),
(264, 1037, 'building permit', 'project_files/building_permit/intro_1037_0.png', '2026-02-16 01:08:17'),
(265, 1037, 'title', 'project_files/titles/intro_1037_1.png', '2026-02-16 01:08:17'),
(266, 1037, 'blueprint', 'project_files/blueprints/intro_1037_2.png', '2026-02-16 01:08:17'),
(267, 1037, 'desired design', 'project_files/designs/intro_1037_3.png', '2026-02-16 01:08:17'),
(268, 1037, 'others', 'project_files/others/intro_1037_4.png', '2026-02-16 01:08:17'),
(269, 1037, 'others', 'project_files/others/intro_1037_5.png', '2026-02-16 01:08:17'),
(270, 1038, 'building permit', 'project_files/building_permit/intro_1038_0.png', '2026-02-16 01:08:17'),
(271, 1038, 'title', 'project_files/titles/intro_1038_1.png', '2026-02-16 01:08:17'),
(272, 1038, 'blueprint', 'project_files/blueprints/intro_1038_2.png', '2026-02-16 01:08:17'),
(273, 1038, 'desired design', 'project_files/designs/intro_1038_3.png', '2026-02-16 01:08:17'),
(274, 1038, 'others', 'project_files/others/intro_1038_4.png', '2026-02-16 01:08:17'),
(275, 1038, 'others', 'project_files/others/intro_1038_5.png', '2026-02-16 01:08:17'),
(276, 1039, 'building permit', 'project_files/building_permit/intro_1039_0.png', '2026-02-16 01:08:17'),
(277, 1039, 'title', 'project_files/titles/intro_1039_1.png', '2026-02-16 01:08:17'),
(278, 1039, 'blueprint', 'project_files/blueprints/intro_1039_2.png', '2026-02-16 01:08:17'),
(279, 1039, 'desired design', 'project_files/designs/intro_1039_3.png', '2026-02-16 01:08:17'),
(280, 1039, 'others', 'project_files/others/intro_1039_4.png', '2026-02-16 01:08:17'),
(281, 1039, 'others', 'project_files/others/intro_1039_5.png', '2026-02-16 01:08:17'),
(282, 1040, 'building permit', 'project_files/building_permit/intro_1040_0.png', '2026-02-16 01:08:17'),
(283, 1040, 'title', 'project_files/titles/intro_1040_1.png', '2026-02-16 01:08:17'),
(284, 1040, 'blueprint', 'project_files/blueprints/intro_1040_2.png', '2026-02-16 01:08:17'),
(285, 1040, 'desired design', 'project_files/designs/intro_1040_3.png', '2026-02-16 01:08:17'),
(286, 1040, 'others', 'project_files/others/intro_1040_4.png', '2026-02-16 01:08:17'),
(287, 1040, 'others', 'project_files/others/intro_1040_5.png', '2026-02-16 01:08:17'),
(288, 1045, 'building permit', 'project_files/building_permit/intro_1045_0.png', '2026-02-16 01:08:17'),
(289, 1045, 'title', 'project_files/titles/intro_1045_1.png', '2026-02-16 01:08:17'),
(290, 1045, 'blueprint', 'project_files/blueprints/intro_1045_2.png', '2026-02-16 01:08:17'),
(291, 1045, 'desired design', 'project_files/designs/intro_1045_3.png', '2026-02-16 01:08:17'),
(292, 1045, 'others', 'project_files/others/intro_1045_4.png', '2026-02-16 01:08:17'),
(293, 1045, 'others', 'project_files/others/intro_1045_5.png', '2026-02-16 01:08:17'),
(294, 1046, 'building permit', 'project_files/building_permit/intro_1046_0.png', '2026-02-16 01:08:17'),
(295, 1046, 'title', 'project_files/titles/intro_1046_1.png', '2026-02-16 01:08:17'),
(296, 1046, 'blueprint', 'project_files/blueprints/intro_1046_2.png', '2026-02-16 01:08:17'),
(297, 1046, 'desired design', 'project_files/designs/intro_1046_3.png', '2026-02-16 01:08:17'),
(298, 1046, 'others', 'project_files/others/intro_1046_4.png', '2026-02-16 01:08:17'),
(299, 1046, 'others', 'project_files/others/intro_1046_5.png', '2026-02-16 01:08:17'),
(300, 1047, 'building permit', 'project_files/building_permit/intro_1047_0.png', '2026-02-16 01:08:17'),
(301, 1047, 'title', 'project_files/titles/intro_1047_1.png', '2026-02-16 01:08:17'),
(302, 1047, 'blueprint', 'project_files/blueprints/intro_1047_2.png', '2026-02-16 01:08:17'),
(303, 1047, 'desired design', 'project_files/designs/intro_1047_3.png', '2026-02-16 01:08:17'),
(304, 1047, 'others', 'project_files/others/intro_1047_4.png', '2026-02-16 01:08:17'),
(305, 1047, 'others', 'project_files/others/intro_1047_5.png', '2026-02-16 01:08:17'),
(306, 1048, 'building permit', 'project_files/building_permit/intro_1048_0.png', '2026-02-16 01:08:17'),
(307, 1048, 'title', 'project_files/titles/intro_1048_1.png', '2026-02-16 01:08:17'),
(308, 1048, 'blueprint', 'project_files/blueprints/intro_1048_2.png', '2026-02-16 01:08:17'),
(309, 1048, 'desired design', 'project_files/designs/intro_1048_3.png', '2026-02-16 01:08:17'),
(310, 1048, 'others', 'project_files/others/intro_1048_4.png', '2026-02-16 01:08:17'),
(311, 1048, 'others', 'project_files/others/intro_1048_5.png', '2026-02-16 01:08:17'),
(312, 1049, 'building permit', 'project_files/building_permit/intro_1049_0.png', '2026-02-16 01:08:17'),
(313, 1049, 'title', 'project_files/titles/intro_1049_1.png', '2026-02-16 01:08:17'),
(314, 1049, 'blueprint', 'project_files/blueprints/intro_1049_2.png', '2026-02-16 01:08:17'),
(315, 1049, 'desired design', 'project_files/designs/intro_1049_3.png', '2026-02-16 01:08:17'),
(316, 1049, 'others', 'project_files/others/intro_1049_4.png', '2026-02-16 01:08:17'),
(317, 1049, 'others', 'project_files/others/intro_1049_5.png', '2026-02-16 01:08:17'),
(318, 1050, 'building permit', 'project_files/building_permit/intro_1050_0.png', '2026-02-16 01:08:17'),
(319, 1050, 'title', 'project_files/titles/intro_1050_1.png', '2026-02-16 01:08:17'),
(320, 1050, 'blueprint', 'project_files/blueprints/intro_1050_2.png', '2026-02-16 01:08:17'),
(321, 1050, 'desired design', 'project_files/designs/intro_1050_3.png', '2026-02-16 01:08:17'),
(322, 1050, 'others', 'project_files/others/intro_1050_4.png', '2026-02-16 01:08:17'),
(323, 1050, 'others', 'project_files/others/intro_1050_5.png', '2026-02-16 01:08:18'),
(324, 1051, 'building permit', 'project_files/building_permit/intro_1051_0.png', '2026-02-16 01:08:18'),
(325, 1051, 'title', 'project_files/titles/intro_1051_1.png', '2026-02-16 01:08:18'),
(326, 1051, 'blueprint', 'project_files/blueprints/intro_1051_2.png', '2026-02-16 01:08:18'),
(327, 1051, 'desired design', 'project_files/designs/intro_1051_3.png', '2026-02-16 01:08:18'),
(328, 1051, 'others', 'project_files/others/intro_1051_4.png', '2026-02-16 01:08:18'),
(329, 1051, 'others', 'project_files/others/intro_1051_5.png', '2026-02-16 01:08:18'),
(330, 1052, 'building permit', 'project_files/building_permit/intro_1052_0.png', '2026-02-16 01:08:18'),
(331, 1052, 'title', 'project_files/titles/intro_1052_1.png', '2026-02-16 01:08:18'),
(332, 1052, 'blueprint', 'project_files/blueprints/intro_1052_2.png', '2026-02-16 01:08:18'),
(333, 1052, 'desired design', 'project_files/designs/intro_1052_3.png', '2026-02-16 01:08:18'),
(334, 1052, 'others', 'project_files/others/intro_1052_4.png', '2026-02-16 01:08:18'),
(335, 1052, 'others', 'project_files/others/intro_1052_5.png', '2026-02-16 01:08:18'),
(336, 1053, 'building permit', 'project_files/building_permit/intro_1053_0.png', '2026-02-16 01:08:18'),
(337, 1053, 'title', 'project_files/titles/intro_1053_1.png', '2026-02-16 01:08:18'),
(338, 1053, 'blueprint', 'project_files/blueprints/intro_1053_2.png', '2026-02-16 01:08:18'),
(339, 1053, 'desired design', 'project_files/designs/intro_1053_3.png', '2026-02-16 01:08:18'),
(340, 1053, 'others', 'project_files/others/intro_1053_4.png', '2026-02-16 01:08:18'),
(341, 1053, 'others', 'project_files/others/intro_1053_5.png', '2026-02-16 01:08:18'),
(342, 1055, 'building permit', 'project_files/building_permit/intro_1055_0.png', '2026-02-16 01:08:18'),
(343, 1055, 'title', 'project_files/titles/intro_1055_1.png', '2026-02-16 01:08:18'),
(344, 1055, 'blueprint', 'project_files/blueprints/intro_1055_2.png', '2026-02-16 01:08:18'),
(345, 1055, 'desired design', 'project_files/designs/intro_1055_3.png', '2026-02-16 01:08:18'),
(346, 1055, 'others', 'project_files/others/intro_1055_4.png', '2026-02-16 01:08:18'),
(347, 1055, 'others', 'project_files/others/intro_1055_5.png', '2026-02-16 01:08:18'),
(348, 1056, 'building permit', 'project_files/building_permit/ejLGkqw7mcCaFTmDH6z7xb2p0YyDdJJT39qBXNDe.jpg', '2026-02-21 01:53:41'),
(349, 1056, 'title', 'project_files/titles/oRUbNfgsUTI6sRSskXN3F2JQpCKt60E8jgZ5aXeR.jpg', '2026-02-21 01:53:41'),
(350, 1056, 'blueprint', 'project_files/blueprints/GizZiBzJXWesrXXccEvPIoxIgRr9dgTsCLaNwIqd.jpg', '2026-02-21 01:53:41'),
(351, 1056, 'desired design', 'project_files/designs/hlKaFFL24cESvQO1O3PEq6Qe27su6XSBiepWh3p1.jpg', '2026-02-21 01:53:41'),
(352, 1056, 'desired design', 'project_files/designs/Q41t0QkR9jnAHA5q6fipJ85nInUsczMOiXPReYCp.jpg', '2026-02-21 01:53:41'),
(353, 1056, 'desired design', 'project_files/designs/ufYXn4h3GhBhxT8dH3gjL1nnb8QJGKNPdm7CpSrE.jpg', '2026-02-21 01:53:41'),
(354, 1056, 'others', 'project_files/others/5yyXtH0K2hmfJLmuugSEJO6XHBO29jMCIqbWVMNX.jpg', '2026-02-21 01:53:41'),
(355, 1057, 'building permit', 'project_files/building_permit/gsCPXjSVy3UKoSqMkfLe8LNo2grZ8mLUYGcHQwRb.jpg', '2026-02-28 17:39:23'),
(356, 1057, 'title', 'project_files/titles/kVd2cWSnjZAASec2KaHu4vThHGf7xZPjUd8EWrWo.jpg', '2026-02-28 17:39:23'),
(357, 1057, 'blueprint', 'project_files/blueprints/pycIRHjAH2cWBspenqMSEjpYK1t5ChVPyjzaisMV.jpg', '2026-02-28 17:39:23'),
(358, 1057, 'desired design', 'project_files/designs/7Ugzlkq1lR9zGpL7KImUVRs8YOL7gXACdzsI9K7D.jpg', '2026-02-28 17:39:23'),
(359, 1057, 'others', 'project_files/others/Gq0qhCrWjuSNlbQg6mIO7QA69OsOuD4gnVmvbrMX.jpg', '2026-02-28 17:39:23'),
(360, 1057, 'others', 'project_files/others/Ot8fP7ZksET7xU71zy8Sfs9vE6aaZrYn0yIhKKUi.jpg', '2026-02-28 17:39:23'),
(361, 1058, 'building permit', 'project_files/building_permit/Pv8PK4LlmHT1Leq7i6XNMNs9DlW0ngjj3Y2FDdAh.jpg', '2026-02-28 18:25:11'),
(362, 1058, 'title', 'project_files/titles/CNYi856jgZc7QPhHAzzbxHR5DrgXYvGDsr1uup4x.jpg', '2026-02-28 18:25:11'),
(363, 1058, 'blueprint', 'project_files/blueprints/vrVgEIGz2tBh918dobEIJmaK33LJ2fDzjAWzu7Ux.jpg', '2026-02-28 18:25:11'),
(364, 1058, 'desired design', 'project_files/designs/fW6Q1njoKpEFU3R4V2BzgFNRi0lwciPn1pl1uyqE.jpg', '2026-02-28 18:25:11'),
(365, 1058, 'desired design', 'project_files/designs/76ZjUK7uf6vgYcxo6LxVOHAGAyd0Oi0hnbthB7AV.jpg', '2026-02-28 18:25:11'),
(366, 1058, 'others', 'project_files/others/O960Yb2wsboAR9eTT5TAwzn9CZtotY51CKGK6zhi.jpg', '2026-02-28 18:25:11'),
(367, 1059, 'building permit', 'project_files/building_permit/Dn0SxQEd9pfZ4y2ULeJEMjLuKl7V9DGy06ymikUP.jpg', '2026-02-28 18:25:12'),
(368, 1059, 'title', 'project_files/titles/YQUBfojt5o1oKbbMhz6D6M6VkWLcUxwwZ2a9CjqC.jpg', '2026-02-28 18:25:12'),
(369, 1059, 'blueprint', 'project_files/blueprints/o2jx7cdlDMyJDRjQaHEzCvqCQzdtrzoEC5LO0zY4.jpg', '2026-02-28 18:25:12'),
(370, 1059, 'desired design', 'project_files/designs/SUnGCoxeik1nk3Jf5lES6bXF2QRCS5SvZYGb277C.jpg', '2026-02-28 18:25:12'),
(371, 1059, 'desired design', 'project_files/designs/fLuDNuosKOopeuZcKWQvRnVjqcm1DPJaRs06X6A1.jpg', '2026-02-28 18:25:12'),
(372, 1059, 'others', 'project_files/others/BfZ3Qrvc4sYFu09eS37UI8UonhpzZEhGAx9zrsAx.jpg', '2026-02-28 18:25:12'),
(373, 1060, 'building permit', 'project_files/building_permit/cJACNbAduqL8zt0i8UA6xV6eKnGzmFRQF2uOJ6bb.jpg', '2026-02-28 18:31:05'),
(374, 1060, 'title', 'project_files/titles/fBXqDu5GGEWDlzrC0FEStSvZfGCXgyd8ZNkO6zxK.jpg', '2026-02-28 18:31:05'),
(375, 1060, 'blueprint', 'project_files/blueprints/qLSFw7XPTP9u2CMPHMcm4wYrXBxuVfBSvveTDKM2.jpg', '2026-02-28 18:31:05'),
(376, 1060, 'desired design', 'project_files/designs/kDKC3T40cBtMSEx9Bw6pHuKAHxhr4Rn4r8Kss5AJ.jpg', '2026-02-28 18:31:05'),
(377, 1060, 'desired design', 'project_files/designs/kEjasGMhlXv9w5oRBG7ch0KhZRMXnVrBaIQdBGXN.jpg', '2026-02-28 18:31:05'),
(378, 1060, 'others', 'project_files/others/eM9FlU4GODuqPiURCKjVjckxEk9Gkx80y6ppABaC.jpg', '2026-02-28 18:31:05');

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
  `bidding_due` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_relationships`
--

INSERT INTO `project_relationships` (`rel_id`, `owner_id`, `selected_contractor_id`, `project_post_status`, `admin_reason`, `bidding_due`, `created_at`, `updated_at`) VALUES
(985, 1687, 1745, 'approved', NULL, NULL, '2025-04-11 07:49:09', '2025-12-15 07:49:09'),
(986, 1688, 1727, 'approved', NULL, '2026-03-16', '2025-12-11 07:49:09', '2026-02-14 19:03:06'),
(987, 1689, NULL, 'approved', NULL, NULL, '2025-04-26 07:49:09', '2025-12-15 07:49:09'),
(988, 1690, NULL, 'approved', NULL, '2026-03-16', '2025-03-10 07:49:09', '2026-02-14 19:03:06'),
(989, 1691, NULL, 'approved', 'asqwearf r3w4etrfewtfg', '2026-03-16', '2025-05-15 07:49:09', '2026-02-14 19:03:06'),
(990, 1692, NULL, 'approved', 'AWERTAWERTWERTYEWSRTE', '2026-03-16', '2025-05-12 07:49:09', '2026-02-14 19:03:06'),
(991, 1693, 1707, 'approved', 'Violation.', NULL, '2025-05-28 07:49:09', '2025-12-15 07:49:09'),
(992, 1695, 1792, 'approved', 'Violation.', NULL, '2025-06-14 07:49:09', '2025-12-15 07:49:09'),
(993, 1697, 1789, 'approved', NULL, NULL, '2024-12-22 07:49:09', '2025-12-15 07:49:09'),
(994, 1698, NULL, 'approved', NULL, '2026-03-16', '2025-08-24 07:49:09', '2026-02-14 19:03:06'),
(995, 1699, NULL, 'approved', 'Violation.', NULL, '2025-08-02 07:49:09', '2025-12-15 07:49:09'),
(996, 1700, 1777, 'approved', NULL, '2026-03-16', '2025-07-31 07:49:09', '2026-02-14 19:03:06'),
(997, 1701, NULL, 'approved', NULL, '2026-03-16', '2025-12-10 07:49:09', '2026-02-14 19:03:06'),
(998, 1702, NULL, 'approved', 'dawfwer2w3er', '2026-03-16', '2025-04-13 07:49:09', '2026-02-14 19:03:06'),
(999, 1704, NULL, 'approved', NULL, NULL, '2025-04-19 07:49:09', '2025-12-15 07:49:09'),
(1000, 1705, NULL, 'approved', NULL, '2026-03-16', '2025-06-27 07:49:09', '2026-02-14 19:03:06'),
(1001, 1707, 1718, 'approved', NULL, '2026-03-16', '2025-01-04 07:49:09', '2026-02-14 19:03:06'),
(1002, 1708, 1725, 'approved', 'Violation.', '2026-03-16', '2025-08-18 07:49:09', '2026-02-14 19:03:06'),
(1003, 1709, NULL, 'approved', NULL, '2026-03-16', '2025-12-12 07:49:09', '2026-02-14 19:03:06'),
(1004, 1710, 1715, 'approved', 'Violation.', '2026-03-16', '2025-12-02 07:49:09', '2026-02-14 19:03:06'),
(1005, 1711, NULL, 'approved', NULL, '2026-03-16', '2025-11-12 07:49:09', '2026-02-14 19:03:06'),
(1006, 1713, 1794, 'approved', NULL, NULL, '2025-03-09 07:49:09', '2025-12-17 13:13:19'),
(1007, 1714, 1764, 'approved', NULL, NULL, '2025-06-04 07:49:09', '2025-12-15 07:49:09'),
(1008, 1715, 1733, 'approved', 'Violation.', NULL, '2025-03-31 07:49:09', '2025-12-15 07:49:09'),
(1009, 1717, 1735, 'approved', '', NULL, '2025-05-04 07:49:09', '2025-12-17 16:35:07'),
(1010, 1718, NULL, 'approved', NULL, '2026-03-16', '2025-12-08 07:49:09', '2026-02-14 19:03:06'),
(1011, 1719, 1717, 'approved', NULL, '2026-03-16', '2025-09-27 07:49:09', '2026-02-14 19:03:06'),
(1012, 1720, NULL, 'approved', NULL, NULL, '2025-06-25 07:49:09', '2025-12-15 07:49:09'),
(1013, 1722, NULL, 'approved', 'Violation.', NULL, '2025-10-04 07:49:09', '2025-12-15 07:49:09'),
(1014, 1723, 1702, 'approved', NULL, '2026-03-16', '2025-04-29 07:49:09', '2026-02-14 19:03:06'),
(1015, 1724, 1787, 'approved', NULL, '2026-03-16', '2025-05-12 07:49:09', '2026-02-14 19:03:06'),
(1016, 1725, 1798, 'approved', NULL, NULL, '2025-02-15 07:49:09', '2025-12-15 07:49:09'),
(1017, 1727, 1690, 'approved', NULL, NULL, '2025-08-11 07:49:09', '2025-12-17 17:19:54'),
(1018, 1728, NULL, 'approved', NULL, NULL, '2025-05-22 07:49:09', '2025-12-15 07:49:09'),
(1019, 1729, NULL, 'approved', 'Violation.', NULL, '2025-10-22 07:49:09', '2025-12-15 07:49:09'),
(1020, 1731, NULL, 'approved', NULL, '2026-03-16', '2025-03-29 07:49:09', '2026-02-14 19:03:06'),
(1021, 1732, NULL, 'approved', 'Violation.', NULL, '2025-10-20 07:49:09', '2025-12-15 07:49:09'),
(1022, 1733, NULL, 'approved', NULL, NULL, '2025-10-08 07:49:09', '2025-12-15 07:49:09'),
(1023, 1734, NULL, 'approved', 'sdfsfsfsfsf', '2026-03-16', '2025-04-16 07:49:09', '2026-02-14 19:03:06'),
(1024, 1735, NULL, 'approved', NULL, NULL, '2025-06-18 07:49:09', '2025-12-15 07:49:09'),
(1025, 1737, 1700, 'approved', NULL, NULL, '2025-10-30 07:49:09', '2025-12-15 07:49:09'),
(1026, 1738, 1717, 'approved', NULL, NULL, '2025-03-17 07:49:09', '2025-12-17 13:13:33'),
(1027, 1740, NULL, 'approved', NULL, '2026-03-16', '2025-12-14 07:49:09', '2026-02-14 19:03:06'),
(1028, 1741, 1763, 'approved', 'Violation.', '2026-03-16', '2025-06-16 07:49:09', '2026-02-14 19:03:06'),
(1029, 1742, 1755, 'approved', NULL, NULL, '2025-09-07 07:49:09', '2025-12-15 07:49:09'),
(1030, 1743, 1694, 'approved', NULL, NULL, '2025-12-10 07:49:09', '2025-12-15 07:49:09'),
(1031, 1744, 1768, 'approved', NULL, '2026-03-16', '2025-07-16 07:49:09', '2026-02-14 19:03:06'),
(1032, 1745, NULL, 'approved', 'Violation.', '2026-03-16', '2025-11-28 07:49:09', '2026-02-14 19:03:06'),
(1033, 1747, 1773, 'approved', 'Violation.', '2026-03-16', '2025-07-03 07:49:09', '2026-02-14 19:03:06'),
(1034, 1749, NULL, 'approved', 'fvdfffffggfgg', '2026-03-16', '2025-05-17 07:49:09', '2026-02-14 19:03:06'),
(1035, 1750, NULL, 'approved', NULL, '2026-03-16', '2025-05-26 07:49:09', '2026-02-14 19:03:06'),
(1036, 1751, NULL, 'approved', NULL, '2026-03-16', '2025-10-15 07:49:09', '2026-02-14 19:03:06'),
(1037, 1752, NULL, 'approved', NULL, '2026-03-16', '2025-01-13 07:49:09', '2026-02-14 19:03:06'),
(1038, 1753, NULL, 'approved', 'Violation.', NULL, '2025-04-05 07:49:09', '2025-12-15 07:49:09'),
(1039, 1754, 1739, 'approved', NULL, '2026-03-16', '2025-11-17 07:49:09', '2026-02-14 19:03:06'),
(1040, 1755, 1702, 'approved', NULL, NULL, '2025-05-23 07:49:09', '2025-12-15 07:49:09'),
(1041, 1758, 1730, 'approved', NULL, '2026-03-16', '2025-08-06 07:49:09', '2026-02-14 19:03:06'),
(1042, 1759, NULL, 'approved', NULL, '2026-03-16', '2025-09-08 07:49:09', '2026-02-14 19:03:06'),
(1043, 1760, 1718, 'approved', NULL, NULL, '2025-04-17 07:49:09', '2025-12-17 14:02:38'),
(1044, 1761, 1740, 'approved', 'aiinoway whattt wahha', '2026-03-16', '2025-02-17 07:49:09', '2026-02-14 19:03:06'),
(1045, 1814, NULL, 'approved', NULL, '2026-03-16', '2025-12-17 12:47:09', '2026-02-19 07:27:35'),
(1046, 1814, 1809, 'approved', NULL, '2026-03-16', '2025-12-18 10:24:52', '2026-02-14 19:03:06'),
(1047, 1814, 1810, 'approved', 'resfssdfsdfs', '2026-03-16', '2025-12-18 19:16:43', '2026-02-22 15:19:27'),
(1048, 1814, 1809, 'approved', NULL, '2026-03-16', '2025-12-18 21:06:43', '2026-02-14 19:03:06'),
(1049, 1814, 1809, 'approved', NULL, '2026-03-16', '2025-12-18 23:49:18', '2026-02-14 19:03:06'),
(1052, 1814, NULL, 'approved', NULL, '2026-03-16', '2026-01-19 01:46:38', '2026-02-14 19:03:06'),
(1053, 1814, NULL, 'approved', NULL, '2026-03-16', '2026-01-19 07:13:02', '2026-02-14 19:03:06'),
(1054, 1814, NULL, 'approved', NULL, '2027-01-01', '2026-01-25 00:13:57', '2026-02-19 07:27:48'),
(1055, 1814, NULL, 'approved', NULL, '2026-04-30', '2026-01-25 00:55:26', '2026-02-12 10:09:26'),
(1056, 1819, 1810, 'due', NULL, '2026-02-28', '2026-02-21 01:53:41', '2026-02-28 06:07:12'),
(1057, 1819, NULL, 'approved', NULL, '2026-03-31', '2026-02-28 17:39:23', '2026-03-01 01:41:17'),
(1058, 1819, NULL, 'approved', NULL, '2026-03-31', '2026-02-28 18:25:11', '2026-03-01 02:32:47'),
(1059, 1819, NULL, 'approved', NULL, '2026-03-31', '2026-02-28 18:25:12', '2026-03-01 02:32:41'),
(1060, 1814, NULL, 'approved', NULL, '2026-03-28', '2026-02-28 18:31:05', '2026-03-01 02:32:32');

-- --------------------------------------------------------

--
-- Table structure for table `project_updates`
--

CREATE TABLE `project_updates` (
  `extension_id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `contractor_user_id` int(10) UNSIGNED NOT NULL COMMENT 'user_id of the submitting contractor',
  `owner_user_id` int(10) UNSIGNED NOT NULL COMMENT 'user_id of the property owner',
  `current_end_date` date DEFAULT NULL COMMENT 'Project end date at time of request (nullable for milestone-only updates)',
  `proposed_end_date` date DEFAULT NULL COMMENT 'Requested new project end date (nullable for milestone-only updates)',
  `reason` text NOT NULL,
  `current_budget` decimal(12,2) DEFAULT NULL COMMENT 'Snapshot of total_project_cost at request time',
  `proposed_budget` decimal(12,2) DEFAULT NULL COMMENT 'Proposed new total contract value (null = no budget change)',
  `budget_change_type` enum('none','increase','decrease') NOT NULL DEFAULT 'none' COMMENT 'Auto-computed: none|increase|decrease',
  `has_additional_cost` tinyint(1) NOT NULL DEFAULT 0,
  `additional_amount` decimal(12,2) DEFAULT NULL COMMENT 'Only set when has_additional_cost = true',
  `milestone_changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON: {new_items:[], edited_items:[], deleted_item_ids:[]}' CHECK (json_valid(`milestone_changes`)),
  `allocation_mode` enum('percentage','exact') DEFAULT NULL COMMENT 'How item costs were allocated in this request',
  `status` enum('pending','approved','rejected','withdrawn','revision_requested') NOT NULL DEFAULT 'pending',
  `owner_response` text DEFAULT NULL COMMENT 'Owner rejection reason or approval note',
  `revision_notes` text DEFAULT NULL,
  `applied_at` timestamp NULL DEFAULT NULL COMMENT 'When the extension was actually applied to the project',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_updates`
--

INSERT INTO `project_updates` (`extension_id`, `project_id`, `contractor_user_id`, `owner_user_id`, `current_end_date`, `proposed_end_date`, `reason`, `current_budget`, `proposed_budget`, `budget_change_type`, `has_additional_cost`, `additional_amount`, `milestone_changes`, `allocation_mode`, `status`, `owner_response`, `revision_notes`, `applied_at`, `created_at`, `updated_at`) VALUES
(1, 1056, 380, 379, '2026-02-28', '2026-03-07', 'just to be safe hludludluxluuuuuhclhclhclhyhckykkkkgxkgxmgggxmgxmggxmgxmgxmgxmyxmyxk', 50000000.00, NULL, 'none', 0, NULL, '{\"new_items\":[],\"edited_items\":[],\"deleted_item_ids\":[]}', 'percentage', 'withdrawn', NULL, NULL, NULL, '2026-02-25 04:16:25', '2026-02-25 06:02:10'),
(2, 1056, 380, 379, '2026-02-28', '2026-03-07', 'uhvlhlyffulylfylxlhxxlhlhclhclhchclh', 50000000.00, 60000000.00, 'increase', 1, 10000000.00, '{\"new_items\":[{\"title\":\"extension\",\"description\":\"hdkdykkydkhdkhd\",\"cost\":19000000}],\"edited_items\":[],\"deleted_item_ids\":[],\"_deleted_items\":[],\"_snapshot_meta\":{\"current_budget\":50000000,\"proposed_budget\":60000000,\"budget_change\":\"increase\",\"allocation_mode\":\"exact\",\"snapshot_at\":\"2026-02-25T15:01:17+00:00\"}}', 'exact', 'approved', NULL, NULL, '2026-02-25 07:15:26', '2026-02-25 07:01:17', '2026-02-25 07:15:26'),
(3, 1056, 380, 379, '2026-03-07', NULL, 'hlchclhclhclhlchlchclhcl', 60000000.00, 60000000.00, 'none', 0, NULL, '{\"new_items\":[],\"edited_items\":[{\"item_id\":2790,\"start_date\":\"2026-02-23\",\"_original\":{\"title\":\"Foundations\",\"cost\":20000000,\"percentage\":33.33,\"start_date\":null,\"due_date\":\"2026-02-26\"}},{\"item_id\":2791,\"start_date\":\"2026-02-27\",\"due_date\":\"2026-03-02\",\"_original\":{\"title\":\"Doners\",\"cost\":21000000,\"percentage\":35,\"start_date\":null,\"due_date\":\"2026-03-07 23:59:59\"}},{\"item_id\":2792,\"start_date\":\"2026-03-03\",\"_original\":{\"title\":\"extension\",\"cost\":19000000,\"percentage\":31.67,\"start_date\":null,\"due_date\":\"2026-03-07 23:59:59\"}}],\"deleted_item_ids\":[],\"_deleted_items\":[],\"_snapshot_meta\":{\"current_budget\":60000000,\"proposed_budget\":60000000,\"budget_change\":\"none\",\"allocation_mode\":\"percentage\",\"snapshot_at\":\"2026-02-27T11:59:51+00:00\"}}', 'percentage', 'withdrawn', NULL, NULL, NULL, '2026-02-27 03:59:51', '2026-02-27 04:02:45'),
(4, 1056, 380, 379, '2026-03-07', NULL, 'kgzjgsktdkyddgkgkxhfl', 60000000.00, 60000000.00, 'none', 0, NULL, '{\"new_items\":[],\"edited_items\":[{\"item_id\":2790,\"start_date\":\"2026-02-23\",\"due_date\":\"2026-02-25\",\"_original\":{\"title\":\"Foundations\",\"cost\":20000000,\"percentage\":33.33,\"start_date\":null,\"due_date\":\"2026-02-26\"}},{\"item_id\":2791,\"start_date\":\"2026-02-26\",\"due_date\":\"2026-03-02\",\"_original\":{\"title\":\"Doners\",\"cost\":21000000,\"percentage\":35,\"start_date\":null,\"due_date\":\"2026-03-07 23:59:59\"}},{\"item_id\":2792,\"start_date\":\"2026-03-03\",\"_original\":{\"title\":\"extension\",\"cost\":19000000,\"percentage\":31.67,\"start_date\":null,\"due_date\":\"2026-03-07 23:59:59\"}}],\"deleted_item_ids\":[],\"_deleted_items\":[],\"_snapshot_meta\":{\"current_budget\":60000000,\"proposed_budget\":60000000,\"budget_change\":\"none\",\"allocation_mode\":\"percentage\",\"snapshot_at\":\"2026-02-27T12:07:06+00:00\"}}', 'percentage', 'approved', NULL, NULL, '2026-02-27 04:12:04', '2026-02-27 04:07:06', '2026-02-27 04:12:04');

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
  `bio` text DEFAULT NULL,
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
  `verification_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_owners`
--

INSERT INTO `property_owners` (`owner_id`, `user_id`, `last_name`, `middle_name`, `first_name`, `phone_number`, `bio`, `address`, `valid_id_id`, `valid_id_photo`, `valid_id_back_photo`, `police_clearance`, `date_of_birth`, `age`, `occupation_id`, `occupation_other`, `verification_status`, `is_active`, `suspension_until`, `rejection_reason`, `deletion_reason`, `suspension_reason`, `verification_date`, `created_at`) VALUES
(1687, 101, 'Tampus', NULL, 'Jeffslazir Augheight', '09990000101', NULL, 'Tetuan, Poblacion, Mankayan, Benguet 7000', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 3, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-18 20:07:36', '2025-04-06 07:49:09'),
(1688, 102, 'OwnerLast102', NULL, 'OwnerFirst102', '09990000102', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 21, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '2025-12-16 04:29:45', '2025-12-06 07:49:09'),
(1689, 103, 'OwnerLast103', NULL, 'OwnerFirst103', '09990000103', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 23, NULL, 'approved', 0, NULL, NULL, NULL, NULL, '2025-04-22 07:49:09', '2025-04-21 07:49:09'),
(1690, 104, 'OwnerLast104', NULL, 'OwnerFirst104', '09990000104', NULL, 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 20, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-03-06 07:49:09', '2025-03-05 07:49:09'),
(1691, 105, 'OwnerLast105', NULL, 'OwnerFirst105', '09990000105', NULL, 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 21, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-11 07:49:09', '2025-05-10 07:49:09'),
(1692, 106, 'OwnerLast106', NULL, 'OwnerFirst106', '09990000106', NULL, 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 3, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-08 07:49:09', '2025-05-07 07:49:09'),
(1693, 107, 'OwnerLast107', NULL, 'OwnerFirst107', '09990000107', NULL, 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 4, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-24 07:49:09', '2025-05-23 07:49:09'),
(1694, 108, 'OwnerLast108', NULL, 'OwnerFirst108', '09990000108', NULL, 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 21, NULL, 'pending', 0, NULL, '', NULL, NULL, '2025-12-15 08:06:48', '2025-03-29 07:49:09'),
(1695, 109, 'OwnerLast109', NULL, 'OwnerFirst109', '09990000109', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 18, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-06-10 07:49:09', '2025-06-09 07:49:09'),
(1696, 110, 'OwnerLast110', NULL, 'OwnerFirst110', '09990000110', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 3, NULL, 'rejected', 1, NULL, 'Invalid ID.', NULL, NULL, '2025-07-10 07:49:09', '2025-07-09 07:49:09'),
(1697, 111, 'OwnerLast111', NULL, 'OwnerFirst111', '09990000111', NULL, 'Tetuan, Tadiangan, Tuba, Benguet 2342', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 15, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2024-12-18 07:49:09', '2024-12-17 07:49:09'),
(1698, 112, 'OwnerLast112', NULL, 'OwnerFirst112', '09990000112', NULL, 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 4, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-08-20 07:49:09', '2025-08-19 07:49:09'),
(1699, 113, 'OwnerLast113', NULL, 'OwnerFirst113', '09990000113', NULL, 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 6, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-07-29 07:49:09', '2025-07-28 07:49:09'),
(1700, 114, 'OwnerLast114', NULL, 'OwnerFirst114', '09990000114', NULL, 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 23, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-07-27 07:49:09', '2025-07-26 07:49:09'),
(1701, 115, 'OwnerLast115', NULL, 'OwnerFirst115', '09990000115', NULL, 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 13, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-06 07:49:09', '2025-12-05 07:49:09'),
(1702, 116, 'OwnerLast116', NULL, 'OwnerFirst116', '09990000116', NULL, 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 18, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-04-09 07:49:09', '2025-04-08 07:49:09'),
(1703, 117, 'OwnerLast117', NULL, 'OwnerFirst117', '09990000117', NULL, 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 13, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-10-17 07:49:09'),
(1704, 118, 'OwnerLast118', NULL, 'OwnerFirst118', '09990000118', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 23, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-04-15 07:49:09', '2025-04-14 07:49:09'),
(1705, 119, 'OwnerLast119', NULL, 'OwnerFirst119', '09990000119', NULL, 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 1, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-06-23 07:49:09', '2025-06-22 07:49:09'),
(1706, 120, 'OwnerLast120', NULL, 'OwnerFirst120', '09990000120', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 20, NULL, 'rejected', 0, NULL, 'Invalid ID.', NULL, NULL, '2025-08-23 07:49:09', '2025-08-22 07:49:09'),
(1707, 121, 'OwnerLast121', NULL, 'OwnerFirst121', '09990000121', NULL, 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 1, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2024-12-31 07:49:09', '2024-12-30 07:49:09'),
(1708, 122, 'OwnerLast122', NULL, 'OwnerFirst122', '09990000122', NULL, 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 1, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-08-14 07:49:09', '2025-08-13 07:49:09'),
(1709, 123, 'OwnerLast123', NULL, 'OwnerFirst123', '09990000123', NULL, 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 25, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-08 07:49:09', '2025-12-07 07:49:09'),
(1710, 124, 'OwnerLast124', NULL, 'OwnerFirst124', '09990000124', NULL, 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 13, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-11-28 07:49:09', '2025-11-27 07:49:09'),
(1711, 125, 'OwnerLast125', NULL, 'OwnerFirst125', '09990000125', NULL, 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 14, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-11-08 07:49:09', '2025-11-07 07:49:09'),
(1712, 126, 'OwnerLast126', NULL, 'OwnerFirst126', '09990000126', NULL, 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 13, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-04-22 07:49:09'),
(1713, 127, 'OwnerLast127', NULL, 'OwnerFirst127', '09990000127', NULL, 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 19, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-03-05 07:49:09', '2025-03-04 07:49:09'),
(1714, 128, 'OwnerLast128', NULL, 'OwnerFirst128', '09990000128', NULL, 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 12, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-31 07:49:09', '2025-05-30 07:49:09'),
(1715, 129, 'OwnerLast129', NULL, 'OwnerFirst129', '09990000129', NULL, 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 25, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-03-27 07:49:09', '2025-03-26 07:49:09'),
(1716, 130, 'OwnerLast130', NULL, 'OwnerFirst130', '09990000130', NULL, 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 6, NULL, 'rejected', 1, NULL, 'Invalid ID.', NULL, NULL, '2025-06-13 07:49:09', '2025-06-12 07:49:09'),
(1717, 131, 'OwnerLast131', NULL, 'OwnerFirst131', '09990000131', NULL, 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 23, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-04-30 07:49:09', '2025-04-29 07:49:09'),
(1718, 132, 'OwnerLast132', NULL, 'OwnerFirst132', '09990000132', NULL, 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 1, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-04 07:49:09', '2025-12-03 07:49:09'),
(1719, 133, 'OwnerLast133', NULL, 'OwnerFirst133', '09990000133', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 10, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-09-23 07:49:09', '2025-09-22 07:49:09'),
(1720, 134, 'OwnerLast134', NULL, 'OwnerFirst134', '09990000134', NULL, 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 4, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-06-21 07:49:09', '2025-06-20 07:49:09'),
(1721, 135, 'OwnerLast135', NULL, 'OwnerFirst135', '09990000135', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 20, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-01-10 07:49:09'),
(1722, 136, 'OwnerLast136', NULL, 'OwnerFirst136', '09990000136', NULL, 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 25, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-09-30 07:49:09', '2025-09-29 07:49:09'),
(1723, 137, 'OwnerLast137', NULL, 'OwnerFirst137', '09990000137', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 1, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-04-25 07:49:09', '2025-04-24 07:49:09'),
(1724, 138, 'OwnerLast138', NULL, 'OwnerFirst138', '09990000138', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 13, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-08 07:49:09', '2025-05-07 07:49:09'),
(1725, 139, 'OwnerLast139', NULL, 'OwnerFirst139', '09990000139', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 20, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-02-11 07:49:09', '2025-02-10 07:49:09'),
(1726, 140, 'OwnerLast140', NULL, 'OwnerFirst140', '09990000140', NULL, 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 12, NULL, 'rejected', 0, NULL, 'Invalid ID.', NULL, NULL, '2025-03-07 07:49:09', '2025-03-06 07:49:09'),
(1727, 141, 'OwnerLast141', NULL, 'OwnerFirst141', '09990000141', NULL, 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 11, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-08-07 07:49:09', '2025-08-06 07:49:09'),
(1728, 142, 'OwnerLast142', NULL, 'OwnerFirst142', '09990000142', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 17, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-18 07:49:09', '2025-05-17 07:49:09'),
(1729, 143, 'OwnerLast143', NULL, 'OwnerFirst143', '09990000143', NULL, 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 16, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-10-18 07:49:09', '2025-10-17 07:49:09'),
(1730, 144, 'OwnerLast144', NULL, 'OwnerFirst144', '09990000144', NULL, 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 4, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-02-03 07:49:09'),
(1731, 145, 'OwnerLast145', NULL, 'OwnerFirst145', '09990000145', NULL, 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 3, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-03-25 07:49:09', '2025-03-24 07:49:09'),
(1732, 146, 'OwnerLast146', NULL, 'OwnerFirst146', '09990000146', NULL, 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 19, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-10-16 07:49:09', '2025-10-15 07:49:09'),
(1733, 147, 'OwnerLast147', NULL, 'OwnerFirst147', '09990000147', NULL, 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 1, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-10-04 07:49:09', '2025-10-03 07:49:09'),
(1734, 148, 'OwnerLast148', NULL, 'OwnerFirst148', '09990000148', NULL, 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 23, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-04-12 07:49:09', '2025-04-11 07:49:09'),
(1735, 149, 'OwnerLast149', NULL, 'OwnerFirst149', '09990000149', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 4, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-06-14 07:49:09', '2025-06-13 07:49:09'),
(1736, 150, 'OwnerLast150', NULL, 'OwnerFirst150', '09990000150', NULL, 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 19, NULL, 'rejected', 1, NULL, 'Invalid ID.', NULL, NULL, '2025-09-27 07:49:09', '2025-09-26 07:49:09'),
(1737, 151, 'OwnerLast151', NULL, 'OwnerFirst151', '09990000151', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 20, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-10-26 07:49:09', '2025-10-25 07:49:09'),
(1738, 152, 'OwnerLast152', NULL, 'OwnerFirst152', '09990000152', NULL, 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 8, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-03-13 07:49:09', '2025-03-12 07:49:09'),
(1739, 153, 'OwnerLast153', NULL, 'OwnerFirst153', '09990000153', NULL, 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 11, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-12-14 07:49:09'),
(1740, 154, 'OwnerLast154', NULL, 'OwnerFirst154', '09990000154', NULL, 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 22, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-10 07:49:09', '2025-12-09 07:49:09'),
(1741, 155, 'OwnerLast155', NULL, 'OwnerFirst155', '09990000155', NULL, 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 15, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-06-12 07:49:09', '2025-06-11 07:49:09'),
(1742, 156, 'OwnerLast156', NULL, 'OwnerFirst156', '09990000156', NULL, 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 14, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-09-03 07:49:09', '2025-09-02 07:49:09'),
(1743, 157, 'OwnerLast157', NULL, 'OwnerFirst157', '09990000157', NULL, 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 4, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-06 07:49:09', '2025-12-05 07:49:09'),
(1744, 158, 'OwnerLast158', NULL, 'OwnerFirst158', '09990000158', NULL, 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 6, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-07-12 07:49:09', '2025-07-11 07:49:09'),
(1745, 159, 'OwnerLast159', NULL, 'OwnerFirst159', '09990000159', NULL, 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 13, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-11-24 07:49:09', '2025-11-23 07:49:09'),
(1746, 160, 'OwnerLast160', NULL, 'OwnerFirst160', '09990000160', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 8, NULL, 'rejected', 0, NULL, 'Invalid ID.', NULL, NULL, '2025-10-12 07:49:09', '2025-10-11 07:49:09'),
(1747, 161, 'OwnerLast161', NULL, 'OwnerFirst161', '09990000161', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 26, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-06-29 07:49:09', '2025-06-28 07:49:09'),
(1748, 162, 'OwnerLast162', NULL, 'OwnerFirst162', '09990000162', NULL, 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 12, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-09-06 07:49:09'),
(1749, 163, 'OwnerLast163', NULL, 'OwnerFirst163', '09990000163', NULL, 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 2, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-13 07:49:09', '2025-05-12 07:49:09'),
(1750, 164, 'OwnerLast164', NULL, 'OwnerFirst164', '09990000164', NULL, 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 11, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-22 07:49:09', '2025-05-21 07:49:09'),
(1751, 165, 'OwnerLast165', NULL, 'OwnerFirst165', '09990000165', NULL, 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 15, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-10-11 07:49:09', '2025-10-10 07:49:09'),
(1752, 166, 'OwnerLast166', NULL, 'OwnerFirst166', '09990000166', NULL, 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 4, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-01-09 07:49:09', '2025-01-08 07:49:09'),
(1753, 167, 'OwnerLast167', NULL, 'OwnerFirst167', '09990000167', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 16, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-04-01 07:49:09', '2025-03-31 07:49:09'),
(1754, 168, 'OwnerLast168', NULL, 'OwnerFirst168', '09990000168', NULL, 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 8, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-11-13 07:49:09', '2025-11-12 07:49:09'),
(1755, 169, 'OwnerLast169', NULL, 'OwnerFirst169', '09990000169', NULL, 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 12, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-19 07:49:09', '2025-05-18 07:49:09'),
(1756, 170, 'OwnerLast170', NULL, 'OwnerFirst170', '09990000170', NULL, 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 23, NULL, 'rejected', 1, NULL, 'Invalid ID.', NULL, NULL, '2025-07-22 07:49:09', '2025-07-21 07:49:09'),
(1757, 171, 'OwnerLast171', NULL, 'OwnerFirst171', '09990000171', NULL, 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 5, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-05-17 07:49:09'),
(1758, 172, 'OwnerLast172', NULL, 'OwnerFirst172', '09990000172', NULL, 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 25, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-08-02 07:49:09', '2025-08-01 07:49:09'),
(1759, 173, 'OwnerLast173', NULL, 'OwnerFirst173', '09990000173', NULL, 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 13, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-09-04 07:49:09', '2025-09-03 07:49:09'),
(1760, 174, 'OwnerLast174', NULL, 'OwnerFirst174', '09990000174', NULL, 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 22, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-04-13 07:49:09', '2025-04-12 07:49:09'),
(1761, 175, 'OwnerLast175', NULL, 'OwnerFirst175', '09990000175', NULL, 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 23, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-02-13 07:49:09', '2025-02-12 07:49:09'),
(1762, 176, 'OwnerLast176', NULL, 'OwnerFirst176', '09990000176', NULL, 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 5, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-10-12 07:49:09', '2025-10-11 07:49:09'),
(1763, 177, 'OwnerLast177', NULL, 'OwnerFirst177', '09990000177', NULL, 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 7, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-09-08 07:49:09', '2025-09-07 07:49:09'),
(1764, 178, 'OwnerLast178', NULL, 'OwnerFirst178', '09990000178', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 19, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-02-04 07:49:09', '2025-02-03 07:49:09'),
(1765, 179, 'OwnerLast179', NULL, 'OwnerFirst179', '09990000179', NULL, 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 20, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-06-27 07:49:09', '2025-06-26 07:49:09'),
(1766, 180, 'OwnerLast180', NULL, 'OwnerFirst180', '09990000180', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 22, NULL, 'rejected', 0, NULL, 'Invalid ID.', NULL, NULL, '0000-00-00 00:00:00', '2025-11-28 07:49:09'),
(1767, 181, 'OwnerLast181', NULL, 'OwnerFirst181', '09990000181', NULL, 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 2, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-01-27 07:49:09', '2025-01-26 07:49:09'),
(1768, 182, 'OwnerLast182', NULL, 'OwnerFirst182', '09990000182', NULL, 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 6, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-10 07:49:09', '2025-05-09 07:49:09'),
(1769, 183, 'OwnerLast183', NULL, 'OwnerFirst183', '09990000183', NULL, 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 2, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-08 07:49:09', '2025-12-07 07:49:09'),
(1770, 184, 'OwnerLast184', NULL, 'OwnerFirst184', '09990000184', NULL, 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 8, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-03-11 07:49:09', '2025-03-10 07:49:09'),
(1771, 185, 'OwnerLast185', NULL, 'OwnerFirst185', '09990000185', NULL, 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 5, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-08-21 07:49:09', '2025-08-20 07:49:09'),
(1772, 186, 'OwnerLast186', NULL, 'OwnerFirst186', '09990000186', NULL, 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 1, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2024-12-19 07:49:09', '2024-12-18 07:49:09'),
(1773, 187, 'OwnerLast187', NULL, 'OwnerFirst187', '09990000187', NULL, 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 5, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-03-06 07:49:09', '2025-03-05 07:49:09'),
(1774, 188, 'OwnerLast188', NULL, 'OwnerFirst188', '09990000188', NULL, 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 11, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-23 07:49:09', '2025-05-22 07:49:09'),
(1775, 189, 'OwnerLast189', NULL, 'OwnerFirst189', '09990000189', NULL, 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 21, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-03-19 07:49:09'),
(1776, 190, 'OwnerLast190', NULL, 'OwnerFirst190', '09990000190', NULL, 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 19, NULL, 'rejected', 1, NULL, 'Invalid ID.', NULL, NULL, '2025-08-27 07:49:09', '2025-08-26 07:49:09'),
(1777, 191, 'OwnerLast191', NULL, 'OwnerFirst191', '09990000191', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 23, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-19 07:49:09', '2025-05-18 07:49:09'),
(1778, 192, 'OwnerLast192', NULL, 'OwnerFirst192', '09990000192', NULL, 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 15, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-11-06 07:49:09', '2025-11-05 07:49:09'),
(1779, 193, 'OwnerLast193', NULL, 'OwnerFirst193', '09990000193', NULL, 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 5, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-01-24 07:49:09', '2025-01-23 07:49:09'),
(1780, 194, 'OwnerLast194', NULL, 'OwnerFirst194', '09990000194', NULL, 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 3, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-06-17 07:49:09', '2025-06-16 07:49:09'),
(1781, 195, 'OwnerLast195', NULL, 'OwnerFirst195', '09990000195', NULL, 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 2, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-29 07:49:09', '2025-05-28 07:49:09'),
(1782, 196, 'OwnerLast196', NULL, 'OwnerFirst196', '09990000196', NULL, 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 3, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-09-21 07:49:09', '2025-09-20 07:49:09'),
(1783, 197, 'OwnerLast197', NULL, 'OwnerFirst197', '09990000197', NULL, 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 26, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-07-20 07:49:09', '2025-07-19 07:49:09'),
(1784, 198, 'OwnerLast198', NULL, 'OwnerFirst198', '09990000198', NULL, 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 7, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-12-03 07:49:09'),
(1785, 199, 'OwnerLast199', NULL, 'OwnerFirst199', '09990000199', NULL, 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 12, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-01-21 07:49:09', '2025-01-20 07:49:09'),
(1786, 200, 'OwnerLast200', NULL, 'OwnerFirst200', '09990000200', NULL, 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 18, NULL, 'rejected', 0, NULL, 'Invalid ID.', NULL, NULL, '2025-08-05 07:49:09', '2025-08-04 07:49:09'),
(1787, 201, 'OwnerLast201', NULL, 'OwnerFirst201', '09990000201', NULL, 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 20, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-08-15 07:49:09', '2025-08-14 07:49:09'),
(1788, 202, 'OwnerLast202', NULL, 'OwnerFirst202', '09990000202', NULL, 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 3, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-04-17 07:49:09', '2025-04-16 07:49:09'),
(1789, 203, 'OwnerLast203', NULL, 'OwnerFirst203', '09990000203', NULL, 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 24, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-09-05 07:49:09', '2025-09-04 07:49:09'),
(1790, 204, 'OwnerLast204', NULL, 'OwnerFirst204', '09990000204', NULL, 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 6, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-09-22 07:49:09', '2025-09-21 07:49:09'),
(1791, 205, 'OwnerLast205', NULL, 'OwnerFirst205', '09990000205', NULL, 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 10, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-30 07:49:09', '2025-05-29 07:49:09'),
(1792, 206, 'OwnerLast206', NULL, 'OwnerFirst206', '09990000206', NULL, 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 24, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-11-16 07:49:09', '2025-11-15 07:49:09'),
(1793, 207, 'OwnerLast207', NULL, 'OwnerFirst207', '09990000207', NULL, 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 24, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-04-16 07:49:09'),
(1794, 208, 'OwnerLast208', NULL, 'OwnerFirst208', '09990000208', NULL, 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 2, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-09 07:49:09', '2025-12-08 07:49:09'),
(1795, 209, 'OwnerLast209', NULL, 'OwnerFirst209', '09990000209', NULL, 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 12, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-07-16 07:49:09', '2025-07-15 07:49:09'),
(1796, 210, 'OwnerLast210', NULL, 'OwnerFirst210', '09990000210', NULL, 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 6, NULL, 'rejected', 1, NULL, 'Invalid ID.', NULL, NULL, '2025-06-19 07:49:09', '2025-06-18 07:49:09'),
(1797, 211, 'OwnerLast211', NULL, 'OwnerFirst211', '09990000211', NULL, 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 15, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-10-13 07:49:09', '2025-10-12 07:49:09'),
(1798, 212, 'OwnerLast212', NULL, 'OwnerFirst212', '09990000212', NULL, 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 15, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-02-17 07:49:09', '2025-02-16 07:49:09'),
(1799, 213, 'OwnerLast213', NULL, 'OwnerFirst213', '09990000213', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 26, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-05-18 07:49:09', '2025-05-17 07:49:09'),
(1800, 214, 'OwnerLast214', NULL, 'OwnerFirst214', '09990000214', NULL, 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 4, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-06-19 07:49:09', '2025-06-18 07:49:09'),
(1801, 215, 'OwnerLast215', NULL, 'OwnerFirst215', '09990000215', NULL, 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 17, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-08-13 07:49:09', '2025-08-12 07:49:09'),
(1802, 216, 'OwnerLast216', NULL, 'OwnerFirst216', '09990000216', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 25, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '2025-04-11 07:49:09'),
(1803, 217, 'OwnerLast217', NULL, 'OwnerFirst217', '09990000217', NULL, 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 3, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-01-15 07:49:09', '2025-01-14 07:49:09'),
(1804, 218, 'OwnerLast218', NULL, 'OwnerFirst218', '09990000218', NULL, 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 12, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-06-06 07:49:09', '2025-06-05 07:49:09'),
(1805, 219, 'OwnerLast219', NULL, 'OwnerFirst219', '09990000219', NULL, 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 3, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-03-10 07:49:09', '2025-03-09 07:49:09'),
(1806, 220, 'OwnerLast220', NULL, 'OwnerFirst220', '09990000220', NULL, 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police.jpg', '1990-01-01', 35, 17, NULL, 'rejected', 0, NULL, 'Invalid ID.', NULL, NULL, '2025-11-09 07:49:09', '2025-11-08 07:49:09'),
(1807, 351, 'Bongo', 'Duran', 'Chrysjann Theo', '09926314071', NULL, 'Sapphire Street, Baliwasan, City of Zamboanga, Zamboanga Del Sur 7000', 2, 'validID/front/tQ9mcgV1EIbDSijOfEFkbS00YfuRxHBIkPtORkDn.jpg', 'validID/back/TUqXZpXuuzBHWXneHWA9cjG7zCxzHFoAbqC9iFw2.jpg', 'policeClearance/laoVX4DumPxRgQ9hKsyFcjXzeq90pYpV5eGR8MW2.png', '2004-10-01', 21, NULL, 'Comshop', 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-16 03:04:28', '2025-12-15 18:13:01'),
(1808, 352, 'Padios', 'Ahat', 'Olive Faith', '09926354567', NULL, 'Unit No.3, Baliwasan, City of Zamboanga, Zamboanga Del Sur 7000', 4, 'validID/front/MvGX5e8pYjtVsFkylyaxHaueJ3HVwnIWvXRWkVm5.png', 'validID/back/5NkmiiyJDLqow0XzLRGQLYqFefcYAsQyaBs8gGJh.png', 'policeClearance/WW7HTg8kE6w1DvCN2adronrHrCmDHzKIYr470veX.png', '2000-02-09', 25, 4, NULL, 'deleted', 0, NULL, NULL, NULL, NULL, '2025-12-15 20:32:28', '2025-12-15 20:32:28'),
(1809, 353, 'Kulong', 'Gellecania', 'Rone Paullan', '09926354567', NULL, 'Bungiau, Baliwasan, City of Zamboanga, Zamboanga Del Sur 7000', 2, 'validID/front/WQFw3sDBljTwWxtdrWcwFsRISqJnM5sjGBKbCtAU.jpg', 'validID/back/mP8bq8qSm6avB2SubBvNv0jdW8DhgVECUFFlUv8l.jpg', 'policeClearance/zKsaydvtQFqb40O5FvYeNGbqdqkyXHrOOWAa4BE4.png', '2025-12-17', 0, NULL, 'Private Nurse', 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-16 08:15:37', '2025-12-16 00:10:43'),
(1810, 365, 'Tampus', NULL, 'Jeffslazir', '09990000104', NULL, 'Tetuan, Mampayag, Manolo Fortich, Bukidnon 7000', 4, 'validID/front/0twyJfLOF07DtNKKSIOrl5pujO6eCHt693gc9Td8.jpg', 'validID/back/XRNvfAUrKK07HDk5OXt7KVjM6eFkzN00ut98cIf0.jpg', 'policeClearance/hiSNt5ZlA2klKupTAr6gbHPoV0W7IJLUpfsiAuXm.jpg', '1998-02-03', 27, 20, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-17 08:20:13', '2025-12-17 08:20:13'),
(1814, 371, 'Test', NULL, 'Test', '09360211158', NULL, 'anywhere, 150706003, 150706000, 150700000, 7000', NULL, 'validID/Gr8y8x2b1bgXXMbvhGQVKamRm5YPbfmOYlX4JbBV.jpg', 'validID/QW2dEJivxK7kFXJtaPqbuurdaGqDEtPqnCdJxtZy.jpg', 'policeClearance/vkrbCkOsrrDCp2pbvi7U4Amd1PhSp91a0KCDI44h.jpg', '2007-12-18', 17, 9, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-17 20:17:25', '2025-12-17 12:16:07'),
(1815, 28, '', NULL, '', '', NULL, '', NULL, NULL, '', '', '0000-00-00', 0, NULL, NULL, 'approved', 0, NULL, NULL, NULL, NULL, '2025-12-18 14:45:33', '2025-12-18 14:45:33'),
(1816, 373, 'Tampus', NULL, 'Jeff', '09756420289', NULL, 'Anywhere, 148105004, 148105000, 148100000, 7000', NULL, 'validID/BI2w5cvODwtD48iS09cX8B6uujdADyZzk1ZDZEx9.jpg', 'validID/3g3bIGeuaNaJbDXZfil5uznjZcfZIv4Lcv3yccM0.jpg', 'policeClearance/exF5TQRqOsVdMpiY3soByLTBlRy2Ap9lSyiQKyDI.jpg', '2000-12-19', 24, 21, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2026-01-29 14:51:28', '2025-12-18 12:03:19'),
(1818, 375, 'Jimenez', NULL, 'Hart', '09360211156', NULL, 'Anywhere, 148106025, 148106000, 148100000, 7000', 4, 'validID/YlXxOQqkmL397keDUR2rLzJQVm4r5iheiCGWwOn2.jpg', 'validID/RlHDVSKWPEKxWjRxYBt43GWzyQySLRDJnnAcF71c.jpg', 'policeClearance/pFDmPT5EMupLCPaFUhRuudvwteTp0xYTMeg0ZCej.jpg', '2003-12-19', 22, 10, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '2025-12-19 00:39:57', '2025-12-18 16:39:57'),
(1819, 379, 'test3', 'test3', 'test3', '09360211158', NULL, '456 Oakd, 160202033, 160202000, 160200000, 7000', 4, 'validID/rYbiFg2gfxb6RCVJ3MW2eyVHBRGkrqnqMvquoQc3.jpg', 'validID/7gWklG7yvruN2dB4kBIzkO2ISUV1QulaJiV2rJ4r.jpg', 'policeClearance/XpC3e0OTf6PGURy3HH1TY2vTcawjz8ezGaIL8Aqr.jpg', '1998-02-21', 28, 22, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2026-02-21 09:06:36', '2026-02-21 00:54:11');

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
-- Table structure for table `subscription_plans`
--

CREATE TABLE `subscription_plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `plan_key` varchar(255) NOT NULL,
  `for_contractor` tinyint(4) NOT NULL,
  `name` varchar(255) NOT NULL,
  `amount` bigint(20) NOT NULL,
  `currency` varchar(255) NOT NULL DEFAULT 'PHP',
  `billing_cycle` varchar(255) NOT NULL DEFAULT 'monthly',
  `duration_days` int(11) DEFAULT NULL,
  `deletion_reason` text DEFAULT NULL,
  `benefits` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`benefits`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscription_plans`
--

INSERT INTO `subscription_plans` (`id`, `plan_key`, `for_contractor`, `name`, `amount`, `currency`, `billing_cycle`, `duration_days`, `deletion_reason`, `benefits`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'gold', 1, 'Gold Tier Subscriptions', 199900, 'PHP', 'monthly', NULL, NULL, '[\"Unlock AI driven analytics\",\"Unlimited Bids per month\",\"Boost Bids\"]', 1, 0, '2026-02-28 05:15:48', '2026-02-28 21:59:39'),
(2, 'silver', 1, 'Silver Tier Subscription', 149900, 'PHP', 'monthly', NULL, NULL, '[\"25 Bids per month\",\"Boost Bids\"]', 1, 0, '2026-02-28 05:15:48', '2026-02-28 21:27:29'),
(3, 'bronze', 1, 'Bronze Tier Subscription', 99900, 'PHP', 'monthly', NULL, NULL, '[\"10 Bids per month\"]', 1, 0, '2026-02-28 05:15:48', '2026-02-28 05:15:48'),
(4, 'boost', 0, 'Project Boost', 4900, 'PHP', 'one-time', 7, '', '[\"7 Days Visibility Boost\"]', 1, 0, '2026-02-28 05:15:48', '2026-02-28 21:59:43'),
(5, 'sssss', 0, 'dedede', 50000, 'PHP', 'monthly', NULL, 'aaaaaaaaaaaaaaaaaaaaaaaaaaaa', '[\"dededede\"]', 0, 1, '2026-02-28 10:30:34', '2026-02-28 21:10:56'),
(7, 'GRRR', 0, 'dedddddddddddddddddddddd', 21300, 'PHP', 'one-time', 23, 'dfdddddddddddddddddddddd', '[\"NIGGER\"]', 0, 1, '2026-02-28 21:25:35', '2026-02-28 22:05:50');

-- --------------------------------------------------------

--
-- Table structure for table `termination_proof`
--

CREATE TABLE `termination_proof` (
  `proof_id` int(11) NOT NULL,
  `termination_id` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp()
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
  `OTP_hash` varchar(255) DEFAULT NULL,
  `user_type` enum('contractor','property_owner','both','staff') NOT NULL,
  `preferred_role` enum('contractor','owner') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `profile_pic`, `cover_photo`, `username`, `email`, `password_hash`, `OTP_hash`, `user_type`, `preferred_role`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, 'owner1', 'owner1@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-07-30 07:49:09', '2025-12-15 21:13:42'),
(2, NULL, NULL, 'owner2', 'owner2@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-10-21 07:49:09', '2025-12-15 21:13:42'),
(3, NULL, NULL, 'owner3', 'owner3@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2024-12-17 07:49:09', '2025-12-15 21:13:42'),
(4, NULL, NULL, 'owner4', 'owner4@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-01-24 07:49:09', '2025-12-15 21:13:42'),
(5, NULL, NULL, 'owner5', 'owner5@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-02-19 07:49:09', '2025-12-15 21:13:42'),
(6, NULL, NULL, 'owner6', 'owner6@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-04-21 07:49:09', '2025-12-15 21:13:42'),
(7, NULL, NULL, 'owner7', 'owner7@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-11-25 07:49:09', '2025-12-15 21:13:42'),
(8, NULL, NULL, 'owner8', 'owner8@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-10-08 07:49:09', '2025-12-15 21:13:42'),
(9, NULL, NULL, 'owner9', 'owner9@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-03-26 07:49:09', '2025-12-15 21:13:42'),
(10, NULL, NULL, 'owner10', 'owner10@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-09-23 07:49:09', '2025-12-15 21:13:42'),
(11, NULL, NULL, 'owner11', 'owner11@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-05-10 07:49:09', '2025-12-15 21:13:42'),
(12, NULL, NULL, 'owner12', 'owner12@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-09-09 07:49:09', '2025-12-15 21:13:42'),
(13, NULL, NULL, 'owner13', 'owner13@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-07-06 07:49:09', '2025-12-15 21:13:42'),
(14, NULL, NULL, 'owner14', 'owner14@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-02-20 07:49:09', '2025-12-15 21:13:42'),
(15, NULL, NULL, 'owner15', 'owner15@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-01-08 07:49:09', '2025-12-15 21:13:42'),
(16, NULL, NULL, 'owner16', 'owner16@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-05-15 07:49:09', '2025-12-15 21:13:42'),
(17, NULL, NULL, 'owner17', 'owner17@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-04-29 07:49:09', '2025-12-15 21:13:42'),
(18, NULL, NULL, 'owner18', 'owner18@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-01-04 07:49:09', '2025-12-15 21:13:42'),
(19, NULL, NULL, 'owner19', 'owner19@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-05-29 07:49:09', '2025-12-15 21:13:42'),
(20, NULL, NULL, 'owner20', 'owner20@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-10-18 07:49:09', '2025-12-15 21:13:42'),
(21, NULL, NULL, 'owner21', 'owner21@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-01-22 07:49:09', '2025-12-15 21:13:42'),
(22, NULL, NULL, 'owner22', 'owner22@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-10-16 07:49:09', '2025-12-15 21:13:42'),
(23, NULL, NULL, 'owner23', 'owner23@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-03-16 07:49:09', '2025-12-15 21:13:42'),
(24, NULL, NULL, 'owner24', 'owner24@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-08-25 07:49:09', '2025-12-15 21:13:42'),
(25, NULL, NULL, 'owner25', 'owner25@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-05-01 07:49:09', '2025-12-15 21:13:42'),
(26, NULL, NULL, 'owner26', 'owner26@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-08-14 07:49:09', '2025-12-15 21:13:42'),
(27, NULL, NULL, 'owner27', 'owner27@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-11-06 07:49:09', '2025-12-15 21:13:42'),
(28, NULL, NULL, 'owner28', 'owner28@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-05-01 07:49:09', '2025-12-15 21:13:42'),
(29, NULL, NULL, 'owner29', 'owner29@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-07-19 07:49:09', '2025-12-15 21:13:42'),
(30, NULL, NULL, 'owner30', 'owner30@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-05-24 07:49:09', '2025-12-15 21:13:42'),
(31, NULL, NULL, 'owner31', 'owner31@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-11-08 07:49:09', '2025-12-15 21:13:42'),
(32, NULL, NULL, 'owner32', 'owner32@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-09-29 07:49:09', '2025-12-15 21:13:42'),
(33, NULL, NULL, 'owner33', 'owner33@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-02-04 07:49:09', '2025-12-15 21:13:42'),
(34, NULL, NULL, 'owner34', 'owner34@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-08-18 07:49:09', '2025-12-15 21:13:42'),
(35, NULL, NULL, 'owner35', 'owner35@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-04-22 07:49:09', '2025-12-15 21:13:42'),
(36, NULL, NULL, 'owner36', 'owner36@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-09-22 07:49:09', '2025-12-15 21:13:42'),
(37, NULL, NULL, 'owner37', 'owner37@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-05-10 07:49:09', '2025-12-15 21:13:42'),
(38, NULL, NULL, 'owner38', 'owner38@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-02-08 07:49:09', '2025-12-15 21:13:42'),
(39, NULL, NULL, 'owner39', 'owner39@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-07-21 07:49:09', '2025-12-15 21:13:42'),
(40, NULL, NULL, 'owner40', 'owner40@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-02-04 07:49:09', '2025-12-15 21:13:42'),
(41, NULL, NULL, 'owner41', 'owner41@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-02-08 07:49:09', '2025-12-15 21:13:42'),
(42, NULL, NULL, 'owner42', 'owner42@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-06-02 07:49:09', '2025-12-15 21:13:42'),
(43, NULL, NULL, 'owner43', 'owner43@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-05-21 07:49:09', '2025-12-15 21:13:42'),
(44, NULL, NULL, 'owner44', 'owner44@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-06-28 07:49:09', '2025-12-15 21:13:42'),
(45, NULL, NULL, 'owner45', 'owner45@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-04-02 07:49:09', '2025-12-15 21:13:42'),
(46, NULL, NULL, 'owner46', 'owner46@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-08-06 07:49:09', '2025-12-15 21:13:42'),
(47, NULL, NULL, 'owner47', 'owner47@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-07-12 07:49:09', '2025-12-15 21:13:42'),
(48, NULL, NULL, 'owner48', 'owner48@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-06-15 07:49:09', '2025-12-15 21:13:42'),
(49, NULL, NULL, 'owner49', 'company49@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-12-12 07:49:09', '2025-12-16 09:56:43'),
(50, NULL, NULL, 'owner50', 'owner50@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-08-24 07:49:09', '2025-12-15 21:13:42'),
(51, NULL, NULL, 'owner51', 'owner51@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-09-17 07:49:09', '2025-12-15 21:13:42'),
(52, NULL, NULL, 'owner52', 'owner52@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-08-27 07:49:09', '2025-12-15 21:13:42'),
(53, NULL, NULL, 'owner53', 'owner53@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-11-10 07:49:09', '2025-12-15 21:13:42'),
(54, NULL, NULL, 'owner54', 'owner54@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-01-13 07:49:09', '2025-12-15 21:13:42'),
(55, NULL, NULL, 'owner55', 'owner55@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-06-01 07:49:09', '2025-12-15 21:13:42'),
(56, NULL, NULL, 'owner56', 'owner56@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-03-14 07:49:09', '2025-12-15 21:13:42'),
(57, NULL, NULL, 'owner57', 'owner57@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-03-25 07:49:09', '2025-12-15 21:13:42'),
(58, NULL, NULL, 'owner58', 'owner58@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-01-23 07:49:09', '2025-12-15 21:13:42'),
(59, NULL, NULL, 'owner59', 'owner59@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-06-10 07:49:09', '2025-12-15 21:13:42'),
(60, NULL, NULL, 'owner60', 'owner60@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-10-15 07:49:09', '2025-12-15 21:13:42'),
(61, NULL, NULL, 'owner61', 'owner61@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2024-12-23 07:49:09', '2025-12-15 21:13:42'),
(62, NULL, NULL, 'owner62', 'owner62@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-01-16 07:49:09', '2025-12-15 21:13:42'),
(63, NULL, NULL, 'owner63', 'owner63@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-03-27 07:49:09', '2025-12-15 21:13:42'),
(64, NULL, NULL, 'owner64', 'owner64@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-08-19 07:49:09', '2025-12-15 21:13:42'),
(65, NULL, NULL, 'owner65', 'owner65@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-01-05 07:49:09', '2025-12-15 21:13:42'),
(66, NULL, NULL, 'owner66', 'owner66@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-10-19 07:49:09', '2025-12-15 21:13:42'),
(67, NULL, NULL, 'owner67', 'owner67@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2024-12-30 07:49:09', '2025-12-15 21:13:42'),
(68, NULL, NULL, 'owner68', 'owner68@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-06-24 07:49:09', '2025-12-15 21:13:42'),
(69, NULL, NULL, 'owner69', 'owner69@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-03-16 07:49:09', '2025-12-15 21:13:42'),
(70, NULL, NULL, 'owner70', 'owner70@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-07-27 07:49:09', '2025-12-15 21:13:42'),
(71, NULL, NULL, 'owner71', 'owner71@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-07-24 07:49:09', '2025-12-15 21:13:42'),
(72, NULL, NULL, 'owner72', 'owner72@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-04-26 07:49:09', '2025-12-15 21:13:42'),
(73, NULL, NULL, 'owner73', 'owner73@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-02-21 07:49:09', '2025-12-15 21:13:42'),
(74, NULL, NULL, 'owner74', 'owner74@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-01-29 07:49:09', '2025-12-15 21:13:42'),
(75, NULL, NULL, 'owner75', 'owner75@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-11-17 07:49:09', '2025-12-15 21:13:42'),
(76, NULL, NULL, 'owner76', 'owner76@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-10-05 07:49:09', '2025-12-15 21:13:42'),
(77, NULL, NULL, 'owner77', 'owner77@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-04-11 07:49:09', '2025-12-15 21:13:42'),
(78, NULL, NULL, 'owner78', 'owner78@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-12-05 07:49:09', '2025-12-15 21:13:42'),
(79, NULL, NULL, 'owner79', 'owner79@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-04-25 07:49:09', '2025-12-15 21:13:42'),
(80, NULL, NULL, 'owner80', 'owner80@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-02-08 07:49:09', '2025-12-15 21:13:42'),
(81, NULL, NULL, 'owner81', 'owner81@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-03-25 07:49:09', '2025-12-15 21:13:42'),
(82, NULL, NULL, 'owner82', 'owner82@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-01-05 07:49:09', '2025-12-15 21:13:42'),
(83, NULL, NULL, 'owner83', 'owner83@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2024-12-25 07:49:09', '2025-12-15 21:13:42'),
(84, NULL, NULL, 'owner84', 'owner84@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-03-13 07:49:09', '2025-12-15 21:13:42'),
(85, NULL, NULL, 'owner85', 'owner85@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2024-12-27 07:49:09', '2025-12-15 21:13:42'),
(86, NULL, NULL, 'owner86', 'owner86@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-08-26 07:49:09', '2025-12-15 21:13:42'),
(87, NULL, NULL, 'owner87', 'owner87@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-08-21 07:49:09', '2025-12-15 21:13:42'),
(88, NULL, NULL, 'owner88', 'owner88@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-09-22 07:49:09', '2025-12-15 21:13:42'),
(89, NULL, NULL, 'owner89', 'owner89@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-07-11 07:49:09', '2025-12-15 21:13:42'),
(90, NULL, NULL, 'owner90', 'owner90@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-04-07 07:49:09', '2025-12-15 21:13:42'),
(91, NULL, NULL, 'owner91', 'owner91@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-09-16 07:49:09', '2025-12-15 21:13:42'),
(92, NULL, NULL, 'owner92', 'owner92@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-10-09 07:49:09', '2025-12-15 21:13:42'),
(93, NULL, NULL, 'owner93', 'owner93@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-10-06 07:49:09', '2025-12-15 21:13:42'),
(94, NULL, NULL, 'owner94', 'owner94@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-05-24 07:49:09', '2025-12-15 21:13:42'),
(95, NULL, NULL, 'owner95', 'owner95@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-04-03 07:49:09', '2025-12-15 21:13:42'),
(96, NULL, NULL, 'owner96', 'owner96@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-11-30 07:49:09', '2025-12-15 21:13:42'),
(97, NULL, NULL, 'owner97', 'owner97@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-01-02 07:49:09', '2025-12-15 21:13:42'),
(98, NULL, NULL, 'owner98', 'owner98@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-10-23 07:49:09', '2025-12-15 21:13:42'),
(99, NULL, NULL, 'owner99', 'owner99@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-07-13 07:49:09', '2025-12-15 21:13:42'),
(100, NULL, NULL, 'owner100', 'owner100@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'contractor', NULL, '2025-07-02 07:49:09', '2025-12-15 21:13:42'),
(101, NULL, NULL, 'owner101', 'owner101@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-04-06 07:49:09', '2025-12-15 23:05:38'),
(102, NULL, NULL, 'owner102', 'owner102@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-12-06 07:49:09', '2025-12-15 21:13:42'),
(103, NULL, NULL, 'owner103', 'owner103@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-04-21 07:49:09', '2025-12-15 21:13:42'),
(104, NULL, NULL, 'owner104', 'owner104@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-03-05 07:49:09', '2025-12-15 21:13:42'),
(105, NULL, NULL, 'owner105', 'owner105@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-05-10 07:49:09', '2025-12-15 21:13:42'),
(106, NULL, NULL, 'owner106', 'owner106@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-05-07 07:49:09', '2025-12-15 21:13:42'),
(107, NULL, NULL, 'owner107', 'owner107@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-05-23 07:49:09', '2025-12-15 21:13:42'),
(108, NULL, NULL, 'owner108', 'owner108@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-03-29 07:49:09', '2025-12-15 21:13:42'),
(109, NULL, NULL, 'owner109', 'owner109@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-06-09 07:49:09', '2025-12-15 21:13:42'),
(110, NULL, NULL, 'owner110', 'owner110@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-07-09 07:49:09', '2025-12-15 21:13:42'),
(111, NULL, NULL, 'owner111', 'owner111@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2024-12-17 07:49:09', '2025-12-16 00:08:52'),
(112, NULL, NULL, 'owner112', 'owner112@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-08-19 07:49:09', '2025-12-15 21:13:42'),
(113, NULL, NULL, 'owner113', 'owner113@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-07-28 07:49:09', '2025-12-15 21:13:42'),
(114, NULL, NULL, 'owner114', 'owner114@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-07-26 07:49:09', '2025-12-15 21:13:42'),
(115, NULL, NULL, 'owner115', 'owner115@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-12-05 07:49:09', '2025-12-15 21:13:42'),
(116, NULL, NULL, 'owner116', 'owner116@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-04-08 07:49:09', '2025-12-15 21:13:42'),
(117, NULL, NULL, 'owner117', 'owner117@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-10-17 07:49:09', '2025-12-15 21:13:42'),
(118, NULL, NULL, 'owner118', 'owner118@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-04-14 07:49:09', '2025-12-15 21:13:42'),
(119, NULL, NULL, 'owner119', 'owner119@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-06-22 07:49:09', '2025-12-15 21:13:42'),
(120, NULL, NULL, 'owner120', 'owner120@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-08-22 07:49:09', '2025-12-15 21:13:42'),
(121, NULL, NULL, 'owner121', 'owner121@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2024-12-30 07:49:09', '2025-12-15 21:13:42'),
(122, NULL, NULL, 'owner122', 'owner122@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-08-13 07:49:09', '2025-12-15 21:13:42'),
(123, NULL, NULL, 'owner123', 'owner123@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-12-07 07:49:09', '2025-12-15 21:13:42'),
(124, NULL, NULL, 'owner124', 'owner124@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-11-27 07:49:09', '2025-12-15 21:13:42'),
(125, NULL, NULL, 'owner125', 'owner125@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-11-07 07:49:09', '2025-12-15 21:13:42'),
(126, NULL, NULL, 'owner126', 'owner126@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-04-22 07:49:09', '2025-12-15 21:13:42'),
(127, NULL, NULL, 'owner127', 'owner127@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-03-04 07:49:09', '2025-12-15 21:13:42'),
(128, NULL, NULL, 'owner128', 'owner128@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-05-30 07:49:09', '2025-12-15 21:13:42'),
(129, NULL, NULL, 'owner129', 'owner129@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-03-26 07:49:09', '2025-12-15 21:13:42'),
(130, NULL, NULL, 'owner130', 'owner130@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-06-12 07:49:09', '2025-12-15 21:13:42'),
(131, NULL, NULL, 'owner131', 'owner131@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-04-29 07:49:09', '2025-12-15 21:13:42'),
(132, NULL, NULL, 'owner132', 'owner132@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-12-03 07:49:09', '2025-12-15 21:13:42'),
(133, NULL, NULL, 'owner133', 'owner133@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-09-22 07:49:09', '2025-12-15 21:13:42'),
(134, NULL, NULL, 'owner134', 'owner134@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-06-20 07:49:09', '2025-12-15 21:13:42'),
(135, NULL, NULL, 'owner135', 'owner135@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-01-10 07:49:09', '2025-12-15 21:13:42'),
(136, NULL, NULL, 'owner136', 'owner136@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-09-29 07:49:09', '2025-12-15 21:13:42'),
(137, NULL, NULL, 'owner137', 'owner137@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-04-24 07:49:09', '2025-12-15 21:13:42'),
(138, NULL, NULL, 'owner138', 'owner138@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-05-07 07:49:09', '2025-12-15 21:13:42'),
(139, NULL, NULL, 'owner139', 'owner139@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-02-10 07:49:09', '2025-12-15 21:13:42'),
(140, NULL, NULL, 'owner140', 'owner140@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-03-06 07:49:09', '2025-12-15 21:13:42'),
(141, NULL, NULL, 'owner141', 'owner141@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-08-06 07:49:09', '2025-12-15 21:13:42'),
(142, NULL, NULL, 'owner142', 'owner142@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-05-17 07:49:09', '2025-12-15 21:13:42'),
(143, NULL, NULL, 'owner143', 'owner143@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-10-17 07:49:09', '2025-12-15 21:13:42'),
(144, NULL, NULL, 'owner144', 'owner144@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-02-03 07:49:09', '2025-12-15 21:13:42'),
(145, NULL, NULL, 'owner145', 'owner145@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-03-24 07:49:09', '2025-12-15 21:13:42'),
(146, NULL, NULL, 'owner146', 'owner146@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-10-15 07:49:09', '2025-12-15 21:13:42'),
(147, NULL, NULL, 'owner147', 'owner147@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-10-03 07:49:09', '2025-12-15 21:13:42'),
(148, NULL, NULL, 'owner148', 'owner148@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-04-11 07:49:09', '2025-12-15 21:13:42'),
(149, NULL, NULL, 'owner149', 'owner149@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-06-13 07:49:09', '2025-12-15 21:13:42'),
(150, NULL, NULL, 'owner150', 'owner150@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-09-26 07:49:09', '2025-12-15 21:13:42'),
(151, NULL, NULL, 'owner151', 'owner151@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-10-25 07:49:09', '2025-12-15 21:13:42'),
(152, NULL, NULL, 'owner152', 'owner152@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-03-12 07:49:09', '2025-12-15 21:13:42'),
(153, NULL, NULL, 'owner153', 'owner153@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-12-14 07:49:09', '2025-12-15 21:13:42'),
(154, NULL, NULL, 'owner154', 'owner154@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-12-09 07:49:09', '2025-12-15 21:13:42'),
(155, NULL, NULL, 'owner155', 'owner155@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-06-11 07:49:09', '2025-12-15 21:13:42'),
(156, NULL, NULL, 'owner156', 'owner156@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-09-02 07:49:09', '2025-12-15 21:13:42'),
(157, NULL, NULL, 'owner157', 'owner157@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-12-05 07:49:09', '2025-12-15 21:13:42'),
(158, NULL, NULL, 'owner158', 'owner158@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-07-11 07:49:09', '2025-12-15 21:13:42'),
(159, NULL, NULL, 'owner159', 'owner159@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-11-23 07:49:09', '2025-12-15 21:13:42'),
(160, NULL, NULL, 'owner160', 'owner160@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-10-11 07:49:09', '2025-12-15 21:13:42'),
(161, NULL, NULL, 'owner161', 'owner161@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-06-28 07:49:09', '2025-12-15 21:13:42'),
(162, NULL, NULL, 'owner162', 'owner162@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-09-06 07:49:09', '2025-12-15 21:13:42'),
(163, NULL, NULL, 'owner163', 'owner163@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-05-12 07:49:09', '2025-12-15 21:13:42'),
(164, NULL, NULL, 'owner164', 'owner164@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-05-21 07:49:09', '2025-12-15 21:13:42'),
(165, NULL, NULL, 'owner165', 'owner165@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-10-10 07:49:09', '2025-12-15 21:13:42'),
(166, NULL, NULL, 'owner166', 'owner166@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-01-08 07:49:09', '2025-12-15 21:13:42'),
(167, NULL, NULL, 'owner167', 'owner167@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-03-31 07:49:09', '2025-12-15 21:13:42'),
(168, NULL, NULL, 'owner168', 'owner168@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-11-12 07:49:09', '2025-12-15 21:13:42'),
(169, NULL, NULL, 'owner169', 'owner169@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-05-18 07:49:09', '2025-12-15 21:13:42'),
(170, NULL, NULL, 'owner170', 'owner170@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-07-21 07:49:09', '2025-12-15 21:13:42'),
(171, NULL, NULL, 'owner171', 'owner171@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-05-17 07:49:09', '2025-12-15 21:13:42'),
(172, NULL, NULL, 'owner172', 'owner172@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-08-01 07:49:09', '2025-12-15 21:13:42'),
(173, NULL, NULL, 'owner173', 'owner173@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-09-03 07:49:09', '2025-12-15 21:13:42'),
(174, NULL, NULL, 'owner174', 'owner174@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-04-12 07:49:09', '2025-12-15 21:13:42'),
(175, NULL, NULL, 'owner175', 'owner175@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-02-12 07:49:09', '2025-12-15 21:13:42'),
(176, NULL, NULL, 'owner176', 'owner176@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-10-11 07:49:09', '2025-12-15 21:13:42'),
(177, NULL, NULL, 'owner177', 'owner177@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-09-07 07:49:09', '2025-12-15 21:13:42'),
(178, NULL, NULL, 'owner178', 'owner178@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-02-03 07:49:09', '2025-12-15 21:13:42'),
(179, NULL, NULL, 'owner179', 'owner179@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-06-26 07:49:09', '2025-12-15 21:13:42'),
(180, NULL, NULL, 'owner180', 'owner180@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-11-28 07:49:09', '2025-12-15 21:13:42'),
(181, NULL, NULL, 'owner181', 'owner181@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-01-26 07:49:09', '2025-12-15 21:13:42'),
(182, NULL, NULL, 'owner182', 'owner182@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-05-09 07:49:09', '2025-12-15 21:13:42'),
(183, NULL, NULL, 'owner183', 'owner183@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-12-07 07:49:09', '2025-12-15 21:13:42'),
(184, NULL, NULL, 'owner184', 'owner184@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-03-10 07:49:09', '2025-12-15 21:13:42'),
(185, NULL, NULL, 'owner185', 'owner185@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-08-20 07:49:09', '2025-12-15 21:13:42'),
(186, NULL, NULL, 'owner186', 'owner186@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2024-12-18 07:49:09', '2025-12-15 21:13:42'),
(187, NULL, NULL, 'owner187', 'owner187@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-03-05 07:49:09', '2025-12-15 21:13:42'),
(188, NULL, NULL, 'owner188', 'owner188@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-05-22 07:49:09', '2025-12-15 21:13:42'),
(189, NULL, NULL, 'owner189', 'owner189@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-03-19 07:49:09', '2025-12-15 21:13:42'),
(190, NULL, NULL, 'owner190', 'owner190@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-08-26 07:49:09', '2025-12-15 21:13:42'),
(191, NULL, NULL, 'owner191', 'owner191@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-05-18 07:49:09', '2025-12-15 21:13:42'),
(192, NULL, NULL, 'owner192', 'owner192@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-11-05 07:49:09', '2025-12-15 21:13:42'),
(193, NULL, NULL, 'owner193', 'owner193@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-01-23 07:49:09', '2025-12-15 21:13:42'),
(194, NULL, NULL, 'owner194', 'owner194@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-06-16 07:49:09', '2025-12-15 21:13:42'),
(195, NULL, NULL, 'owner195', 'owner195@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-05-28 07:49:09', '2025-12-15 21:13:42'),
(196, NULL, NULL, 'owner196', 'owner196@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-09-20 07:49:09', '2025-12-15 21:13:42'),
(197, NULL, NULL, 'owner197', 'owner197@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-07-19 07:49:09', '2025-12-15 21:13:42'),
(198, NULL, NULL, 'owner198', 'owner198@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-12-03 07:49:09', '2025-12-15 21:13:42'),
(199, NULL, NULL, 'owner199', 'owner199@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-01-20 07:49:09', '2025-12-15 21:13:42'),
(200, NULL, NULL, 'owner200', 'owner200@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'property_owner', NULL, '2025-08-04 07:49:09', '2025-12-15 21:13:42'),
(201, NULL, NULL, 'owner201', 'owner201@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', NULL, '2025-08-14 07:49:09', '2025-12-15 21:13:42'),
(202, NULL, NULL, 'owner202', 'owner202@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', NULL, '2025-04-16 07:49:09', '2025-12-15 21:13:42'),
(203, NULL, NULL, 'owner203', 'owner203@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', NULL, '2025-09-04 07:49:09', '2025-12-15 21:13:42'),
(204, NULL, NULL, 'owner204', 'owner204@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', NULL, '2025-09-21 07:49:09', '2025-12-15 21:13:42'),
(205, NULL, NULL, 'owner205', 'owner205@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', NULL, '2025-05-29 07:49:09', '2025-12-15 21:13:42'),
(206, NULL, NULL, 'owner206', 'owner206@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', NULL, '2025-11-15 07:49:09', '2025-12-15 21:13:42'),
(207, NULL, NULL, 'owner207', 'owner207@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', NULL, '2025-04-16 07:49:09', '2025-12-15 21:13:42'),
(208, NULL, NULL, 'owner208', 'owner208@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', NULL, '2025-12-08 07:49:09', '2025-12-15 21:13:42'),
(209, NULL, NULL, 'owner209', 'owner209@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', NULL, '2025-07-15 07:49:09', '2025-12-15 21:13:42'),
(210, NULL, NULL, 'owner210', 'owner210@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', NULL, '2025-06-18 07:49:09', '2025-12-15 21:13:42'),
(211, NULL, NULL, 'owner211', 'owner211@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', NULL, '2025-10-12 07:49:09', '2025-12-15 21:13:42'),
(212, NULL, NULL, 'owner212', 'owner212@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', NULL, '2025-02-16 07:49:09', '2025-12-15 21:13:42'),
(213, NULL, NULL, 'owner213', 'owner213@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', NULL, '2025-05-17 07:49:09', '2025-12-15 21:13:42'),
(214, NULL, NULL, 'owner214', 'owner214@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', NULL, '2025-06-18 07:49:09', '2025-12-15 21:13:42'),
(215, NULL, NULL, 'owner215', 'owner215@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', NULL, '2025-08-12 07:49:09', '2025-12-15 21:13:42'),
(216, NULL, NULL, 'owner216', 'owner216@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', NULL, '2025-04-11 07:49:09', '2025-12-15 21:13:42'),
(217, NULL, NULL, 'owner217', 'owner217@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', NULL, '2025-01-14 07:49:09', '2025-12-15 21:13:42'),
(218, NULL, NULL, 'owner218', 'owner218@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', NULL, '2025-06-05 07:49:09', '2025-12-15 21:13:42'),
(219, NULL, NULL, 'owner219', 'owner219@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', NULL, '2025-03-09 07:49:09', '2025-12-15 21:13:42'),
(220, NULL, NULL, 'owner220', 'owner220@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'both', NULL, '2025-11-08 07:49:09', '2025-12-15 21:13:42'),
(221, NULL, NULL, 'staff221', 'staff221@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(222, NULL, NULL, 'staff222', 'staff222@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(223, NULL, NULL, 'staff223', 'staff223@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(224, NULL, NULL, 'staff224', 'staff224@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(225, NULL, NULL, 'staff225', 'staff225@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(226, NULL, NULL, 'staff226', 'staff226@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(227, NULL, NULL, 'staff227', 'staff227@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(228, NULL, NULL, 'staff228', 'staff228@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(229, NULL, NULL, 'staff229', 'staff229@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(230, NULL, NULL, 'staff230', 'staff230@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(231, NULL, NULL, 'staff231', 'staff231@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(232, NULL, NULL, 'staff232', 'staff232@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(233, NULL, NULL, 'staff233', 'staff233@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(234, NULL, NULL, 'staff234', 'staff234@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(235, NULL, NULL, 'staff235', 'staff235@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(236, NULL, NULL, 'staff236', 'staff236@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(237, NULL, NULL, 'staff237', 'staff237@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(238, NULL, NULL, 'staff238', 'staff238@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(239, NULL, NULL, 'staff239', 'staff239@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(240, NULL, NULL, 'staff240', 'staff240@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(241, NULL, NULL, 'staff241', 'staff241@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(242, NULL, NULL, 'staff242', 'staff242@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(243, NULL, NULL, 'staff243', 'staff243@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(244, NULL, NULL, 'staff244', 'staff244@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(245, NULL, NULL, 'staff245', 'staff245@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(246, NULL, NULL, 'staff246', 'staff246@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(247, NULL, NULL, 'staff247', 'staff247@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(248, NULL, NULL, 'staff248', 'staff248@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(249, NULL, NULL, 'staff249', 'staff249@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(250, NULL, NULL, 'staff250', 'staff250@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(251, NULL, NULL, 'staff251', 'staff251@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(252, NULL, NULL, 'staff252', 'staff252@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(253, NULL, NULL, 'staff253', 'staff253@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(254, NULL, NULL, 'staff254', 'staff254@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(255, NULL, NULL, 'staff255', 'staff255@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(256, NULL, NULL, 'staff256', 'staff256@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42');
INSERT INTO `users` (`user_id`, `profile_pic`, `cover_photo`, `username`, `email`, `password_hash`, `OTP_hash`, `user_type`, `preferred_role`, `created_at`, `updated_at`) VALUES
(257, NULL, NULL, 'staff257', 'staff257@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(258, NULL, NULL, 'staff258', 'staff258@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(259, NULL, NULL, 'staff259', 'staff259@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(260, NULL, NULL, 'staff260', 'staff260@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(261, NULL, NULL, 'staff261', 'staff261@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(262, NULL, NULL, 'staff262', 'staff262@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(263, NULL, NULL, 'staff263', 'staff263@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(264, NULL, NULL, 'staff264', 'staff264@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(265, NULL, NULL, 'staff265', 'staff265@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(266, NULL, NULL, 'staff266', 'staff266@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(267, NULL, NULL, 'staff267', 'staff267@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(268, NULL, NULL, 'staff268', 'staff268@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(269, NULL, NULL, 'staff269', 'staff269@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(270, NULL, NULL, 'staff270', 'staff270@example.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'otp_hash', 'staff', NULL, '2025-12-15 07:49:09', '2025-12-15 21:13:42'),
(351, 'profiles/dkoe1C0vh4hIVqBZyaCStCPlkWz5xD5JgpXlMOGq.jpg', NULL, 'owner_9202', 'ashssxeyn@gmail.com', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'admin_created', 'property_owner', NULL, '2025-12-15 18:13:01', '2025-12-19 05:24:40'),
(352, 'profiles/uzYGxzALdcUs45kPeM9T2vjyRhiOIhj1tDbfFDyo.jpg', NULL, 'owner_9302', 'HZ202300486@wmsu.edu.ph', '$2y$12$LNVf9WaeRaphMyEtNf3i/OV5YvqGHeYmdJCnT5IEC7GzlKC4Fzota', 'admin_created', 'property_owner', NULL, '2025-12-15 20:32:28', '2025-12-15 21:13:42'),
(353, NULL, NULL, 'owner_4153', 'shanehaWrt1001@gmail.com', '$2y$12$TcERXqXzMZfclAVNvDSL4eaom7Hj2TN00zoODHkbvfBh9VoGbHk4q', 'admin_created', 'property_owner', NULL, '2025-12-16 00:10:43', '2025-12-16 16:09:13'),
(360, NULL, NULL, 'contractor_4054', 'shanehart100q1@gmail.com', '$2y$12$Uj22aRA04XH.OiqcZej.nuxxeT3oyLaRKODB7EMJi8vK6Lw2yCDSS', 'admin_created', 'contractor', NULL, '2025-12-16 08:22:15', '2025-12-16 09:21:56'),
(361, 'profile_pics/N6NbFyTan68hUgpd9meLOly1afCTS0mX6q8v3dZm.jpg', 'profile_pics/5hGgtBXNnOwCHzbfj1KNioqPjQQx1zwMtvyC6S5N.jpg', 'contractor_9632', 'shanehart1001d@gmail.com', '$2y$12$cNMryBR0IsGQAbeUNQ/bkuGKy44A4veQ2OI5NYqEYYut3.N5I81DS', 'admin_created', 'contractor', NULL, '2025-12-16 08:40:00', '2025-12-16 20:49:54'),
(365, 'profiles/1Twen5pb0MvZ0hkjQU39J1Sb2gOWoylDK1N5gUvR.jpg', NULL, 'owner_1483', 'HZ2021528@wmsu.edu.ph', '$2y$12$.ToBRQGOoWi4g/dow6gbRO/MslShqipGG0IUaVCb.fDEG4VWdGMqy', 'admin_created', 'property_owner', NULL, '2025-12-17 08:20:13', '2025-12-18 19:48:02'),
(371, 'profiles/YHttWdw17tQVKfB3dprbX0fnaLtQ30kH3wfzX8dG.jpg', NULL, 'test1', 'shanehart1001@gmail.com', '$2y$12$I23UIvo1LPBhLJjAFaKOqONRSUzIs3HSoUQZPYaETr.bAODmkDbuy', '$2y$12$hf2aLN6aEwj2DFhHHbHrTOvtSnEqfkKzt6TjtJCc/ASmiWEKMkKVi', 'property_owner', NULL, '2025-12-17 12:16:07', '2025-12-19 05:25:17'),
(372, 'profiles/QCgGQLDwfeLz1aFX2zg3vFSzgxoKp6nUVSeYmgWC.jpg', NULL, 'test2', 'ashxeyn@gmail.com', '$2y$12$RCCdaH9TGLtCnAp7AQFWyuYlcoHHqnk2JvUQu4vCMrQRTK9tLNIOq', '$2y$12$0shaEjoJdsPrdl5KZmmHNuPPhrTbT8oZfbka2HsxcCFO6hRb3vwZC', 'contractor', NULL, '2025-12-17 13:58:50', '2025-12-19 05:24:59'),
(373, 'profiles/UiT3r2ZPyfFmE9azsGvdlbrjls5yOuqfGcbh643X.jpg', NULL, 'jeff', 'hz202301528@wmsu.edu.ph', '$2y$12$Xau9dnWCbF819X9HyJqfLeNmlK2vCbIaowSWvvhV6roEeHGw46hXK', '$2y$12$khOd0DlL9AX7KrYCeUw01uHEnX.rThxnGqOdAgWS.qAKKAgG6Il2O', 'property_owner', NULL, '2025-12-18 12:03:19', '2025-12-18 12:03:19'),
(375, 'profiles/RxtgbX4TuWXc9yiTQLhhTP9rJHIYTnQXtCKhS5Y9.jpg', NULL, 'hartj', 'phantomhiro143@gmail.com', '$2y$12$073h2ma9ombfcYjLtxWILOj8cUfH/TAtyfGB27qxCEjf9gz6lx6r2', '$2y$12$M1pAd.4SVs/r7tx8U7PmPu.YvUBQFSygXXut3/4yuIbNK0Dfdqgti', 'property_owner', NULL, '2025-12-18 16:39:57', '2025-12-18 16:39:57'),
(376, NULL, NULL, 'staff_7484', 'www@gmail.com', '$2y$12$/xCh4O9aA2eLxxFql7733.cZbLEFu632tnj37l74ldcsal3h8ItFa', 'admin_created', 'staff', NULL, '2026-01-27 06:39:40', '2026-01-27 06:39:40'),
(377, NULL, NULL, 'staff_3987', 'weqwdas@gmail.com', '$2y$12$ewimmmI7B/wacS/XgaD6w.YBAlgjBhyZbea4tqNWBd0PyFF3TPpUm', 'admin_created', 'staff', NULL, '2026-01-27 06:43:03', '2026-01-27 06:43:03'),
(378, 'team_members/VBsJKuTxBo5DZN7oA9YBy7TozufMrYlaCPTnX0o8.jpg', NULL, 'staff_2774', 'ditema1752@gamening.com', '$2y$12$Ygpc9NCqA4zYpscBwtT1Ku6sLm9jXSDq0AiZjL/jOmi8aldUB45YO', 'admin_created', 'staff', NULL, '2026-01-29 04:26:35', '2026-01-29 04:26:35'),
(379, 'profiles/PhbbqKnsc3fS1Nv7LERONCLhNcqWD331gUXLHNQV.jpg', NULL, 'test3', 'yelib38945@advarm.com', '$2y$12$2Cbv3LvvnvNIZpUIuKGgz.L56xz9OqxDkFLujjp2dl.MvnjQnxTuS', '$2y$12$y9VTPCjj6m4qOfJnD8wUJOS/kg4/FY8BwWdG/X7cmS8lEFGG99eNS', 'property_owner', NULL, '2026-02-21 00:54:11', '2026-02-21 00:54:11'),
(380, 'profiles/xLaCN2XuTkTXk8xvfyXBErZruRmXqAU5e15zM9U9.jpg', NULL, 'test4', 'joxego4264@advarm.com', '$2y$12$oy0SpEQMnFtlwTulClUVguhsYbZ20auAGsYIjpZ1KdmWxXvk7phqy', '$2y$12$1kC/giaAe0.Zk/XU1Gky7.42RItGVQ8kF/mqvgu1BoNcyc0Tli3QG', 'contractor', NULL, '2026-02-21 01:34:20', '2026-02-21 02:36:37');

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
-- Indexes for table `ai_prediction_logs`
--
ALTER TABLE `ai_prediction_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `bids`
--
ALTER TABLE `bids`
  ADD PRIMARY KEY (`bid_id`),
  ADD UNIQUE KEY `unique_project_contractor` (`project_id`,`contractor_id`),
  ADD KEY `contractor_id` (`contractor_id`),
  ADD KEY `idx_bids_project_status` (`project_id`,`bid_status`),
  ADD KEY `idx_bids_contractor_status` (`contractor_id`,`bid_status`);

--
-- Indexes for table `bid_files`
--
ALTER TABLE `bid_files`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `bid_id` (`bid_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

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
-- Indexes for table `contract_terminations`
--
ALTER TABLE `contract_terminations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_terminations_project` (`project_id`),
  ADD KEY `fk_terminations_contractor` (`contractor_id`),
  ADD KEY `fk_terminations_owner` (`owner_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`conversation_id`),
  ADD KEY `conversations_sender_id_index` (`sender_id`),
  ADD KEY `conversations_receiver_id_index` (`receiver_id`),
  ADD KEY `conversations_status_index` (`status`);

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
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `item_files`
--
ALTER TABLE `item_files`
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `conversation_id` (`conversation_id`),
  ADD KEY `messages_from_sender_index` (`from_sender`);

--
-- Indexes for table `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD PRIMARY KEY (`attachment_id`),
  ADD KEY `message_id` (`message_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `milestones`
--
ALTER TABLE `milestones`
  ADD PRIMARY KEY (`milestone_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `contractor_id` (`contractor_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `milestone_date_histories`
--
ALTER TABLE `milestone_date_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `milestone_date_histories_item_id_index` (`item_id`),
  ADD KEY `milestone_date_histories_extension_id_index` (`extension_id`);

--
-- Indexes for table `milestone_items`
--
ALTER TABLE `milestone_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `milestone_id` (`milestone_id`);

--
-- Indexes for table `milestone_item_updates`
--
ALTER TABLE `milestone_item_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `milestone_item_updates_milestone_item_id_index` (`milestone_item_id`),
  ADD KEY `milestone_item_updates_project_update_id_index` (`project_update_id`),
  ADD KEY `milestone_item_updates_project_update_id_status_index` (`project_update_id`,`status`);

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
  ADD UNIQUE KEY `idx_dedup` (`user_id`,`dedup_key`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_user_created` (`user_id`,`created_at`);

--
-- Indexes for table `occupations`
--
ALTER TABLE `occupations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_adjustment_logs`
--
ALTER TABLE `payment_adjustment_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `payment_adjustment_logs_project_id_index` (`project_id`),
  ADD KEY `payment_adjustment_logs_source_item_id_index` (`source_item_id`),
  ADD KEY `payment_adjustment_logs_target_item_id_index` (`target_item_id`),
  ADD KEY `payment_adjustment_logs_payment_id_index` (`payment_id`);

--
-- Indexes for table `payment_plans`
--
ALTER TABLE `payment_plans`
  ADD PRIMARY KEY (`plan_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `contractor_id` (`contractor_id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `platform_payments`
--
ALTER TABLE `platform_payments`
  ADD PRIMARY KEY (`platform_payment_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `contractor_id` (`contractor_id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `platform_payments_subscriptionplanid_foreign` (`subscriptionPlanId`),
  ADD KEY `idx_pp_boost_lookup` (`project_id`,`subscriptionPlanId`,`is_approved`,`is_cancelled`,`expiration_date`);

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
  ADD KEY `fk_projects_relationship_id` (`relationship_id`),
  ADD KEY `idx_projects_status_type` (`project_status`,`type_id`);

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
  ADD KEY `fk_projectrel_contractor` (`selected_contractor_id`),
  ADD KEY `idx_pr_status_due` (`project_post_status`,`bidding_due`);

--
-- Indexes for table `project_updates`
--
ALTER TABLE `project_updates`
  ADD PRIMARY KEY (`extension_id`),
  ADD KEY `project_extensions_project_id_index` (`project_id`),
  ADD KEY `project_extensions_project_id_status_index` (`project_id`,`status`);

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
-- Indexes for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subscription_plans_plan_key_unique` (`plan_key`);

--
-- Indexes for table `termination_proof`
--
ALTER TABLE `termination_proof`
  ADD PRIMARY KEY (`proof_id`),
  ADD KEY `fk_proof_termination_link` (`termination_id`);

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
-- AUTO_INCREMENT for table `ai_prediction_logs`
--
ALTER TABLE `ai_prediction_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bids`
--
ALTER TABLE `bids`
  MODIFY `bid_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=300;

--
-- AUTO_INCREMENT for table `bid_files`
--
ALTER TABLE `bid_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `contractors`
--
ALTER TABLE `contractors`
  MODIFY `contractor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1811;

--
-- AUTO_INCREMENT for table `contractor_types`
--
ALTER TABLE `contractor_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `contractor_users`
--
ALTER TABLE `contractor_users`
  MODIFY `contractor_user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2060;

--
-- AUTO_INCREMENT for table `contract_terminations`
--
ALTER TABLE `contract_terminations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `conversation_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=371000373;

--
-- AUTO_INCREMENT for table `disputes`
--
ALTER TABLE `disputes`
  MODIFY `dispute_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `dispute_files`
--
ALTER TABLE `dispute_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=336;

--
-- AUTO_INCREMENT for table `message_attachments`
--
ALTER TABLE `message_attachments`
  MODIFY `attachment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `milestones`
--
ALTER TABLE `milestones`
  MODIFY `milestone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1565;

--
-- AUTO_INCREMENT for table `milestone_date_histories`
--
ALTER TABLE `milestone_date_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `milestone_items`
--
ALTER TABLE `milestone_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2793;

--
-- AUTO_INCREMENT for table `milestone_item_updates`
--
ALTER TABLE `milestone_item_updates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `milestone_payments`
--
ALTER TABLE `milestone_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=826;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3736;

--
-- AUTO_INCREMENT for table `occupations`
--
ALTER TABLE `occupations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `payment_adjustment_logs`
--
ALTER TABLE `payment_adjustment_logs`
  MODIFY `log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment_plans`
--
ALTER TABLE `payment_plans`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=929;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `platform_payments`
--
ALTER TABLE `platform_payments`
  MODIFY `platform_payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT for table `progress`
--
ALTER TABLE `progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=834;

--
-- AUTO_INCREMENT for table `progress_files`
--
ALTER TABLE `progress_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1061;

--
-- AUTO_INCREMENT for table `project_files`
--
ALTER TABLE `project_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=379;

--
-- AUTO_INCREMENT for table `project_relationships`
--
ALTER TABLE `project_relationships`
  MODIFY `rel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1061;

--
-- AUTO_INCREMENT for table `project_updates`
--
ALTER TABLE `project_updates`
  MODIFY `extension_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `property_owners`
--
ALTER TABLE `property_owners`
  MODIFY `owner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1820;

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
-- AUTO_INCREMENT for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `termination_proof`
--
ALTER TABLE `termination_proof`
  MODIFY `proof_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=381;

--
-- AUTO_INCREMENT for table `valid_ids`
--
ALTER TABLE `valid_ids`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_prediction_logs`
--
ALTER TABLE `ai_prediction_logs`
  ADD CONSTRAINT `fk_ai_logs_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE;

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
-- Constraints for table `contract_terminations`
--
ALTER TABLE `contract_terminations`
  ADD CONSTRAINT `fk_terminations_contractor` FOREIGN KEY (`contractor_id`) REFERENCES `contractors` (`contractor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_terminations_owner` FOREIGN KEY (`owner_id`) REFERENCES `property_owners` (`owner_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_terminations_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE;

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
-- Constraints for table `item_files`
--
ALTER TABLE `item_files`
  ADD CONSTRAINT `item_files_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `milestone_items` (`item_id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`conversation_id`) ON DELETE CASCADE;

--
-- Constraints for table `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD CONSTRAINT `fk_message_attachments_message_id` FOREIGN KEY (`message_id`) REFERENCES `messages` (`message_id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `platform_payments_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `admin_users` (`admin_id`),
  ADD CONSTRAINT `platform_payments_subscriptionplanid_foreign` FOREIGN KEY (`subscriptionPlanId`) REFERENCES `subscription_plans` (`id`) ON DELETE SET NULL;

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

--
-- Constraints for table `termination_proof`
--
ALTER TABLE `termination_proof`
  ADD CONSTRAINT `fk_proof_termination_link` FOREIGN KEY (`termination_id`) REFERENCES `contract_terminations` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
