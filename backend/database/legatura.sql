-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 15, 2025 at 06:42 AM
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

INSERT INTO `contractors` (`contractor_id`, `user_id`, `company_name`, `years_of_experience`, `type_id`, `contractor_type_other`, `services_offered`, `business_address`, `company_email`, `company_phone`, `company_website`, `company_social_media`, `company_description`, `picab_number`, `picab_category`, `picab_expiration_date`, `business_permit_number`, `business_permit_city`, `business_permit_expiration`, `tin_business_reg_number`, `dti_sec_registration_photo`, `verification_status`, `verification_date`, `rejection_reason`, `completed_projects`, `created_at`, `updated_at`) VALUES
(1052, 1, 'Construction Co 1', 19, 9, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company1@example.com', '09170000001', NULL, NULL, NULL, 'PCAB-25431', 'A', '2026-12-31', 'BP-1', 'Zamboanga', '2026-01-01', 'TIN-1', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 35, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1053, 2, 'Construction Co 2', 5, 8, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company2@example.com', '09170000002', NULL, NULL, NULL, 'PCAB-16264', 'AAA', '2026-12-31', 'BP-2', 'Zamboanga', '2026-01-01', 'TIN-2', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 39, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1054, 3, 'Construction Co 3', 20, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company3@example.com', '09170000003', NULL, NULL, NULL, 'PCAB-89718', 'B', '2026-12-31', 'BP-3', 'Zamboanga', '2026-01-01', 'TIN-3', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 39, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1055, 4, 'Construction Co 4', 19, 5, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company4@example.com', '09170000004', NULL, NULL, NULL, 'PCAB-17545', 'C', '2026-12-31', 'BP-4', 'Zamboanga', '2026-01-01', 'TIN-4', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 46, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1056, 5, 'Construction Co 5', 3, 7, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company5@example.com', '09170000005', NULL, NULL, NULL, 'PCAB-15111', 'C', '2026-12-31', 'BP-5', 'Zamboanga', '2026-01-01', 'TIN-5', 'dti_cert.jpg', 'pending', NULL, NULL, 20, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1057, 6, 'Construction Co 6', 6, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company6@example.com', '09170000006', NULL, NULL, NULL, 'PCAB-21615', 'C', '2026-12-31', 'BP-6', 'Zamboanga', '2026-01-01', 'TIN-6', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 10, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1058, 7, 'Construction Co 7', 6, 3, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company7@example.com', '09170000007', NULL, NULL, NULL, 'PCAB-63093', 'A', '2026-12-31', 'BP-7', 'Zamboanga', '2026-01-01', 'TIN-7', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 37, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1059, 8, 'Construction Co 8', 17, 5, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company8@example.com', '09170000008', NULL, NULL, NULL, 'PCAB-34872', 'A', '2026-12-31', 'BP-8', 'Zamboanga', '2026-01-01', 'TIN-8', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 17, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1060, 9, 'Construction Co 9', 10, 7, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company9@example.com', '09170000009', NULL, NULL, NULL, 'PCAB-24806', 'C', '2026-12-31', 'BP-9', 'Zamboanga', '2026-01-01', 'TIN-9', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 21, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1061, 10, 'Construction Co 10', 16, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company10@example.com', '09170000010', NULL, NULL, NULL, 'PCAB-22590', 'AAA', '2026-12-31', 'BP-10', 'Zamboanga', '2026-01-01', 'TIN-10', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 48, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1062, 11, 'Construction Co 11', 12, 2, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company11@example.com', '09170000011', NULL, NULL, NULL, 'PCAB-74205', 'B', '2026-12-31', 'BP-11', 'Zamboanga', '2026-01-01', 'TIN-11', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 19, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1063, 12, 'Construction Co 12', 10, 6, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company12@example.com', '09170000012', NULL, NULL, NULL, 'PCAB-74664', 'B', '2026-12-31', 'BP-12', 'Zamboanga', '2026-01-01', 'TIN-12', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 20, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1064, 13, 'Construction Co 13', 19, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company13@example.com', '09170000013', NULL, NULL, NULL, 'PCAB-56556', 'AAA', '2026-12-31', 'BP-13', 'Zamboanga', '2026-01-01', 'TIN-13', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 26, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1065, 14, 'Construction Co 14', 13, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company14@example.com', '09170000014', NULL, NULL, NULL, 'PCAB-63231', 'A', '2026-12-31', 'BP-14', 'Zamboanga', '2026-01-01', 'TIN-14', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 0, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1066, 15, 'Construction Co 15', 20, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company15@example.com', '09170000015', NULL, NULL, NULL, 'PCAB-30689', 'A', '2026-12-31', 'BP-15', 'Zamboanga', '2026-01-01', 'TIN-15', 'dti_cert.jpg', 'pending', NULL, NULL, 25, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1067, 16, 'Construction Co 16', 5, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company16@example.com', '09170000016', NULL, NULL, NULL, 'PCAB-89774', 'AAA', '2026-12-31', 'BP-16', 'Zamboanga', '2026-01-01', 'TIN-16', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 43, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1068, 17, 'Construction Co 17', 1, 5, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company17@example.com', '09170000017', NULL, NULL, NULL, 'PCAB-10196', 'A', '2026-12-31', 'BP-17', 'Zamboanga', '2026-01-01', 'TIN-17', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 10, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1069, 18, 'Construction Co 18', 15, 6, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company18@example.com', '09170000018', NULL, NULL, NULL, 'PCAB-68856', 'C', '2026-12-31', 'BP-18', 'Zamboanga', '2026-01-01', 'TIN-18', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 47, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1070, 19, 'Construction Co 19', 16, 6, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company19@example.com', '09170000019', NULL, NULL, NULL, 'PCAB-65624', 'C', '2026-12-31', 'BP-19', 'Zamboanga', '2026-01-01', 'TIN-19', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 49, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1071, 20, 'Construction Co 20', 1, 7, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company20@example.com', '09170000020', NULL, NULL, NULL, 'PCAB-56783', 'A', '2026-12-31', 'BP-20', 'Zamboanga', '2026-01-01', 'TIN-20', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 44, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1072, 21, 'Construction Co 21', 12, 8, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company21@example.com', '09170000021', NULL, NULL, NULL, 'PCAB-69096', 'C', '2026-12-31', 'BP-21', 'Zamboanga', '2026-01-01', 'TIN-21', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 41, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1073, 22, 'Construction Co 22', 18, 5, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company22@example.com', '09170000022', NULL, NULL, NULL, 'PCAB-64758', 'C', '2026-12-31', 'BP-22', 'Zamboanga', '2026-01-01', 'TIN-22', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 5, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1074, 23, 'Construction Co 23', 12, 7, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company23@example.com', '09170000023', NULL, NULL, NULL, 'PCAB-58508', 'C', '2026-12-31', 'BP-23', 'Zamboanga', '2026-01-01', 'TIN-23', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 20, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1075, 24, 'Construction Co 24', 13, 4, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company24@example.com', '09170000024', NULL, NULL, NULL, 'PCAB-46742', 'B', '2026-12-31', 'BP-24', 'Zamboanga', '2026-01-01', 'TIN-24', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 19, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1076, 25, 'Construction Co 25', 17, 8, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company25@example.com', '09170000025', NULL, NULL, NULL, 'PCAB-78684', 'A', '2026-12-31', 'BP-25', 'Zamboanga', '2026-01-01', 'TIN-25', 'dti_cert.jpg', 'pending', NULL, NULL, 35, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1077, 26, 'Construction Co 26', 7, 9, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company26@example.com', '09170000026', NULL, NULL, NULL, 'PCAB-32655', 'AAA', '2026-12-31', 'BP-26', 'Zamboanga', '2026-01-01', 'TIN-26', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 24, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1078, 27, 'Construction Co 27', 7, 3, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company27@example.com', '09170000027', NULL, NULL, NULL, 'PCAB-29698', 'C', '2026-12-31', 'BP-27', 'Zamboanga', '2026-01-01', 'TIN-27', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 15, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1079, 28, 'Construction Co 28', 12, 7, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company28@example.com', '09170000028', NULL, NULL, NULL, 'PCAB-18989', 'C', '2026-12-31', 'BP-28', 'Zamboanga', '2026-01-01', 'TIN-28', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 44, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1080, 29, 'Construction Co 29', 19, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company29@example.com', '09170000029', NULL, NULL, NULL, 'PCAB-94734', 'C', '2026-12-31', 'BP-29', 'Zamboanga', '2026-01-01', 'TIN-29', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 29, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1081, 30, 'Construction Co 30', 6, 5, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company30@example.com', '09170000030', NULL, NULL, NULL, 'PCAB-16767', 'B', '2026-12-31', 'BP-30', 'Zamboanga', '2026-01-01', 'TIN-30', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 41, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1082, 31, 'Construction Co 31', 7, 6, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company31@example.com', '09170000031', NULL, NULL, NULL, 'PCAB-72131', 'B', '2026-12-31', 'BP-31', 'Zamboanga', '2026-01-01', 'TIN-31', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 22, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1083, 32, 'Construction Co 32', 15, 5, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company32@example.com', '09170000032', NULL, NULL, NULL, 'PCAB-49236', 'A', '2026-12-31', 'BP-32', 'Zamboanga', '2026-01-01', 'TIN-32', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 35, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1084, 33, 'Construction Co 33', 7, 2, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company33@example.com', '09170000033', NULL, NULL, NULL, 'PCAB-98393', 'B', '2026-12-31', 'BP-33', 'Zamboanga', '2026-01-01', 'TIN-33', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 38, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1085, 34, 'Construction Co 34', 13, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company34@example.com', '09170000034', NULL, NULL, NULL, 'PCAB-73142', 'AAA', '2026-12-31', 'BP-34', 'Zamboanga', '2026-01-01', 'TIN-34', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 48, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1086, 35, 'Construction Co 35', 17, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company35@example.com', '09170000035', NULL, NULL, NULL, 'PCAB-86411', 'C', '2026-12-31', 'BP-35', 'Zamboanga', '2026-01-01', 'TIN-35', 'dti_cert.jpg', 'pending', NULL, NULL, 47, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1087, 36, 'Construction Co 36', 2, 2, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company36@example.com', '09170000036', NULL, NULL, NULL, 'PCAB-41937', 'AAA', '2026-12-31', 'BP-36', 'Zamboanga', '2026-01-01', 'TIN-36', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 39, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1088, 37, 'Construction Co 37', 10, 4, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company37@example.com', '09170000037', NULL, NULL, NULL, 'PCAB-26877', 'AAA', '2026-12-31', 'BP-37', 'Zamboanga', '2026-01-01', 'TIN-37', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 14, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1089, 38, 'Construction Co 38', 19, 3, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company38@example.com', '09170000038', NULL, NULL, NULL, 'PCAB-98117', 'C', '2026-12-31', 'BP-38', 'Zamboanga', '2026-01-01', 'TIN-38', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 0, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1090, 39, 'Construction Co 39', 15, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company39@example.com', '09170000039', NULL, NULL, NULL, 'PCAB-47866', 'A', '2026-12-31', 'BP-39', 'Zamboanga', '2026-01-01', 'TIN-39', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 15, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1091, 40, 'Construction Co 40', 12, 9, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company40@example.com', '09170000040', NULL, NULL, NULL, 'PCAB-43698', 'C', '2026-12-31', 'BP-40', 'Zamboanga', '2026-01-01', 'TIN-40', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 40, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1092, 41, 'Construction Co 41', 3, 7, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company41@example.com', '09170000041', NULL, NULL, NULL, 'PCAB-69217', 'A', '2026-12-31', 'BP-41', 'Zamboanga', '2026-01-01', 'TIN-41', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 47, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1093, 42, 'Construction Co 42', 13, 4, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company42@example.com', '09170000042', NULL, NULL, NULL, 'PCAB-58822', 'AAA', '2026-12-31', 'BP-42', 'Zamboanga', '2026-01-01', 'TIN-42', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 48, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1094, 43, 'Construction Co 43', 11, 3, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company43@example.com', '09170000043', NULL, NULL, NULL, 'PCAB-40736', 'A', '2026-12-31', 'BP-43', 'Zamboanga', '2026-01-01', 'TIN-43', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 8, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1095, 44, 'Construction Co 44', 11, 8, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company44@example.com', '09170000044', NULL, NULL, NULL, 'PCAB-59591', 'AAA', '2026-12-31', 'BP-44', 'Zamboanga', '2026-01-01', 'TIN-44', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 3, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1096, 45, 'Construction Co 45', 18, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company45@example.com', '09170000045', NULL, NULL, NULL, 'PCAB-13612', 'B', '2026-12-31', 'BP-45', 'Zamboanga', '2026-01-01', 'TIN-45', 'dti_cert.jpg', 'pending', NULL, NULL, 33, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1097, 46, 'Construction Co 46', 14, 9, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company46@example.com', '09170000046', NULL, NULL, NULL, 'PCAB-83520', 'C', '2026-12-31', 'BP-46', 'Zamboanga', '2026-01-01', 'TIN-46', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 6, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1098, 47, 'Construction Co 47', 19, 9, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company47@example.com', '09170000047', NULL, NULL, NULL, 'PCAB-19641', 'A', '2026-12-31', 'BP-47', 'Zamboanga', '2026-01-01', 'TIN-47', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 0, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1099, 48, 'Construction Co 48', 13, 6, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company48@example.com', '09170000048', NULL, NULL, NULL, 'PCAB-79811', 'AAA', '2026-12-31', 'BP-48', 'Zamboanga', '2026-01-01', 'TIN-48', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 46, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1100, 49, 'Construction Co 49', 10, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company49@example.com', '09170000049', NULL, NULL, NULL, 'PCAB-70153', 'B', '2026-12-31', 'BP-49', 'Zamboanga', '2026-01-01', 'TIN-49', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 7, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1101, 50, 'Construction Co 50', 18, 3, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company50@example.com', '09170000050', NULL, NULL, NULL, 'PCAB-79708', 'B', '2026-12-31', 'BP-50', 'Zamboanga', '2026-01-01', 'TIN-50', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 36, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1102, 51, 'Construction Co 51', 16, 8, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company51@example.com', '09170000051', NULL, NULL, NULL, 'PCAB-74557', 'B', '2026-12-31', 'BP-51', 'Zamboanga', '2026-01-01', 'TIN-51', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 8, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1103, 52, 'Construction Co 52', 18, 9, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company52@example.com', '09170000052', NULL, NULL, NULL, 'PCAB-31253', 'B', '2026-12-31', 'BP-52', 'Zamboanga', '2026-01-01', 'TIN-52', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 16, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1104, 53, 'Construction Co 53', 18, 7, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company53@example.com', '09170000053', NULL, NULL, NULL, 'PCAB-81990', 'B', '2026-12-31', 'BP-53', 'Zamboanga', '2026-01-01', 'TIN-53', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 47, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1105, 54, 'Construction Co 54', 14, 7, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company54@example.com', '09170000054', NULL, NULL, NULL, 'PCAB-40544', 'B', '2026-12-31', 'BP-54', 'Zamboanga', '2026-01-01', 'TIN-54', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 33, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1106, 55, 'Construction Co 55', 15, 5, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company55@example.com', '09170000055', NULL, NULL, NULL, 'PCAB-11298', 'AAA', '2026-12-31', 'BP-55', 'Zamboanga', '2026-01-01', 'TIN-55', 'dti_cert.jpg', 'pending', NULL, NULL, 38, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1107, 56, 'Construction Co 56', 8, 4, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company56@example.com', '09170000056', NULL, NULL, NULL, 'PCAB-68394', 'C', '2026-12-31', 'BP-56', 'Zamboanga', '2026-01-01', 'TIN-56', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 20, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1108, 57, 'Construction Co 57', 10, 5, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company57@example.com', '09170000057', NULL, NULL, NULL, 'PCAB-29383', 'A', '2026-12-31', 'BP-57', 'Zamboanga', '2026-01-01', 'TIN-57', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 17, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1109, 58, 'Construction Co 58', 12, 9, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company58@example.com', '09170000058', NULL, NULL, NULL, 'PCAB-44485', 'AAA', '2026-12-31', 'BP-58', 'Zamboanga', '2026-01-01', 'TIN-58', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 41, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1110, 59, 'Construction Co 59', 12, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company59@example.com', '09170000059', NULL, NULL, NULL, 'PCAB-92311', 'B', '2026-12-31', 'BP-59', 'Zamboanga', '2026-01-01', 'TIN-59', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 20, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1111, 60, 'Construction Co 60', 4, 5, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company60@example.com', '09170000060', NULL, NULL, NULL, 'PCAB-65776', 'AAA', '2026-12-31', 'BP-60', 'Zamboanga', '2026-01-01', 'TIN-60', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 17, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1112, 61, 'Construction Co 61', 20, 7, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company61@example.com', '09170000061', NULL, NULL, NULL, 'PCAB-97391', 'B', '2026-12-31', 'BP-61', 'Zamboanga', '2026-01-01', 'TIN-61', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 28, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1113, 62, 'Construction Co 62', 15, 6, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company62@example.com', '09170000062', NULL, NULL, NULL, 'PCAB-44997', 'B', '2026-12-31', 'BP-62', 'Zamboanga', '2026-01-01', 'TIN-62', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 11, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1114, 63, 'Construction Co 63', 3, 5, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company63@example.com', '09170000063', NULL, NULL, NULL, 'PCAB-54848', 'B', '2026-12-31', 'BP-63', 'Zamboanga', '2026-01-01', 'TIN-63', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 2, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1115, 64, 'Construction Co 64', 3, 5, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company64@example.com', '09170000064', NULL, NULL, NULL, 'PCAB-62682', 'B', '2026-12-31', 'BP-64', 'Zamboanga', '2026-01-01', 'TIN-64', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 47, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1116, 65, 'Construction Co 65', 11, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company65@example.com', '09170000065', NULL, NULL, NULL, 'PCAB-28702', 'AAA', '2026-12-31', 'BP-65', 'Zamboanga', '2026-01-01', 'TIN-65', 'dti_cert.jpg', 'pending', NULL, NULL, 49, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1117, 66, 'Construction Co 66', 11, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company66@example.com', '09170000066', NULL, NULL, NULL, 'PCAB-51063', 'C', '2026-12-31', 'BP-66', 'Zamboanga', '2026-01-01', 'TIN-66', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 22, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1118, 67, 'Construction Co 67', 19, 3, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company67@example.com', '09170000067', NULL, NULL, NULL, 'PCAB-44503', 'B', '2026-12-31', 'BP-67', 'Zamboanga', '2026-01-01', 'TIN-67', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 6, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1119, 68, 'Construction Co 68', 14, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company68@example.com', '09170000068', NULL, NULL, NULL, 'PCAB-53289', 'C', '2026-12-31', 'BP-68', 'Zamboanga', '2026-01-01', 'TIN-68', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 22, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1120, 69, 'Construction Co 69', 18, 5, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company69@example.com', '09170000069', NULL, NULL, NULL, 'PCAB-54994', 'B', '2026-12-31', 'BP-69', 'Zamboanga', '2026-01-01', 'TIN-69', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 37, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1121, 70, 'Construction Co 70', 16, 7, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company70@example.com', '09170000070', NULL, NULL, NULL, 'PCAB-62865', 'AAA', '2026-12-31', 'BP-70', 'Zamboanga', '2026-01-01', 'TIN-70', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 9, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1122, 71, 'Construction Co 71', 9, 9, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company71@example.com', '09170000071', NULL, NULL, NULL, 'PCAB-17016', 'B', '2026-12-31', 'BP-71', 'Zamboanga', '2026-01-01', 'TIN-71', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 25, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1123, 72, 'Construction Co 72', 6, 9, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company72@example.com', '09170000072', NULL, NULL, NULL, 'PCAB-99453', 'C', '2026-12-31', 'BP-72', 'Zamboanga', '2026-01-01', 'TIN-72', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 47, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1124, 73, 'Construction Co 73', 16, 5, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company73@example.com', '09170000073', NULL, NULL, NULL, 'PCAB-43258', 'B', '2026-12-31', 'BP-73', 'Zamboanga', '2026-01-01', 'TIN-73', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 12, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1125, 74, 'Construction Co 74', 4, 6, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company74@example.com', '09170000074', NULL, NULL, NULL, 'PCAB-38869', 'AAA', '2026-12-31', 'BP-74', 'Zamboanga', '2026-01-01', 'TIN-74', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 6, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1126, 75, 'Construction Co 75', 12, 9, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company75@example.com', '09170000075', NULL, NULL, NULL, 'PCAB-71304', 'C', '2026-12-31', 'BP-75', 'Zamboanga', '2026-01-01', 'TIN-75', 'dti_cert.jpg', 'pending', NULL, NULL, 34, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1127, 76, 'Construction Co 76', 13, 5, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company76@example.com', '09170000076', NULL, NULL, NULL, 'PCAB-44726', 'B', '2026-12-31', 'BP-76', 'Zamboanga', '2026-01-01', 'TIN-76', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 17, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1128, 77, 'Construction Co 77', 14, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company77@example.com', '09170000077', NULL, NULL, NULL, 'PCAB-90777', 'B', '2026-12-31', 'BP-77', 'Zamboanga', '2026-01-01', 'TIN-77', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 41, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1129, 78, 'Construction Co 78', 1, 4, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company78@example.com', '09170000078', NULL, NULL, NULL, 'PCAB-43670', 'AAA', '2026-12-31', 'BP-78', 'Zamboanga', '2026-01-01', 'TIN-78', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 19, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1130, 79, 'Construction Co 79', 13, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company79@example.com', '09170000079', NULL, NULL, NULL, 'PCAB-89965', 'AAA', '2026-12-31', 'BP-79', 'Zamboanga', '2026-01-01', 'TIN-79', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 29, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1131, 80, 'Construction Co 80', 17, 6, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company80@example.com', '09170000080', NULL, NULL, NULL, 'PCAB-93812', 'B', '2026-12-31', 'BP-80', 'Zamboanga', '2026-01-01', 'TIN-80', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 0, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1132, 81, 'Construction Co 81', 9, 2, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company81@example.com', '09170000081', NULL, NULL, NULL, 'PCAB-55249', 'AAA', '2026-12-31', 'BP-81', 'Zamboanga', '2026-01-01', 'TIN-81', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 36, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1133, 82, 'Construction Co 82', 18, 3, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company82@example.com', '09170000082', NULL, NULL, NULL, 'PCAB-36064', 'A', '2026-12-31', 'BP-82', 'Zamboanga', '2026-01-01', 'TIN-82', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 40, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1134, 83, 'Construction Co 83', 2, 3, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company83@example.com', '09170000083', NULL, NULL, NULL, 'PCAB-18334', 'AAA', '2026-12-31', 'BP-83', 'Zamboanga', '2026-01-01', 'TIN-83', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 40, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1135, 84, 'Construction Co 84', 9, 5, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company84@example.com', '09170000084', NULL, NULL, NULL, 'PCAB-15708', 'A', '2026-12-31', 'BP-84', 'Zamboanga', '2026-01-01', 'TIN-84', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 7, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1136, 85, 'Construction Co 85', 20, 9, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company85@example.com', '09170000085', NULL, NULL, NULL, 'PCAB-24800', 'A', '2026-12-31', 'BP-85', 'Zamboanga', '2026-01-01', 'TIN-85', 'dti_cert.jpg', 'pending', NULL, NULL, 20, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1137, 86, 'Construction Co 86', 19, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company86@example.com', '09170000086', NULL, NULL, NULL, 'PCAB-54030', 'C', '2026-12-31', 'BP-86', 'Zamboanga', '2026-01-01', 'TIN-86', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 18, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1138, 87, 'Construction Co 87', 19, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company87@example.com', '09170000087', NULL, NULL, NULL, 'PCAB-41830', 'A', '2026-12-31', 'BP-87', 'Zamboanga', '2026-01-01', 'TIN-87', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 17, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1139, 88, 'Construction Co 88', 7, 2, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company88@example.com', '09170000088', NULL, NULL, NULL, 'PCAB-38364', 'A', '2026-12-31', 'BP-88', 'Zamboanga', '2026-01-01', 'TIN-88', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 30, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1140, 89, 'Construction Co 89', 2, 7, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company89@example.com', '09170000089', NULL, NULL, NULL, 'PCAB-10537', 'C', '2026-12-31', 'BP-89', 'Zamboanga', '2026-01-01', 'TIN-89', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 41, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1141, 90, 'Construction Co 90', 6, 3, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company90@example.com', '09170000090', NULL, NULL, NULL, 'PCAB-97953', 'B', '2026-12-31', 'BP-90', 'Zamboanga', '2026-01-01', 'TIN-90', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 7, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1142, 91, 'Construction Co 91', 18, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company91@example.com', '09170000091', NULL, NULL, NULL, 'PCAB-10587', 'B', '2026-12-31', 'BP-91', 'Zamboanga', '2026-01-01', 'TIN-91', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 32, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1143, 92, 'Construction Co 92', 2, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company92@example.com', '09170000092', NULL, NULL, NULL, 'PCAB-53029', 'B', '2026-12-31', 'BP-92', 'Zamboanga', '2026-01-01', 'TIN-92', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 9, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1144, 93, 'Construction Co 93', 7, 8, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company93@example.com', '09170000093', NULL, NULL, NULL, 'PCAB-94981', 'C', '2026-12-31', 'BP-93', 'Zamboanga', '2026-01-01', 'TIN-93', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 44, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1145, 94, 'Construction Co 94', 13, 9, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company94@example.com', '09170000094', NULL, NULL, NULL, 'PCAB-63706', 'B', '2026-12-31', 'BP-94', 'Zamboanga', '2026-01-01', 'TIN-94', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 16, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1146, 95, 'Construction Co 95', 16, 2, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company95@example.com', '09170000095', NULL, NULL, NULL, 'PCAB-90126', 'A', '2026-12-31', 'BP-95', 'Zamboanga', '2026-01-01', 'TIN-95', 'dti_cert.jpg', 'pending', NULL, NULL, 23, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1147, 96, 'Construction Co 96', 2, 4, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company96@example.com', '09170000096', NULL, NULL, NULL, 'PCAB-83118', 'A', '2026-12-31', 'BP-96', 'Zamboanga', '2026-01-01', 'TIN-96', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 41, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1148, 97, 'Construction Co 97', 16, 8, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company97@example.com', '09170000097', NULL, NULL, NULL, 'PCAB-25460', 'AAA', '2026-12-31', 'BP-97', 'Zamboanga', '2026-01-01', 'TIN-97', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 26, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1149, 98, 'Construction Co 98', 13, 8, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company98@example.com', '09170000098', NULL, NULL, NULL, 'PCAB-86062', 'AAA', '2026-12-31', 'BP-98', 'Zamboanga', '2026-01-01', 'TIN-98', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 32, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1150, 99, 'Construction Co 99', 11, 7, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company99@example.com', '09170000099', NULL, NULL, NULL, 'PCAB-69163', 'A', '2026-12-31', 'BP-99', 'Zamboanga', '2026-01-01', 'TIN-99', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 41, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1151, 100, 'Construction Co 100', 3, 6, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company100@example.com', '09170000100', NULL, NULL, NULL, 'PCAB-42726', 'B', '2026-12-31', 'BP-100', 'Zamboanga', '2026-01-01', 'TIN-100', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 24, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1152, 201, 'Construction Co 201', 18, 2, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company201@example.com', '09170000201', NULL, NULL, NULL, 'PCAB-18652', 'A', '2026-12-31', 'BP-201', 'Zamboanga', '2026-01-01', 'TIN-201', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 36, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1153, 202, 'Construction Co 202', 9, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company202@example.com', '09170000202', NULL, NULL, NULL, 'PCAB-76126', 'A', '2026-12-31', 'BP-202', 'Zamboanga', '2026-01-01', 'TIN-202', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 6, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1154, 203, 'Construction Co 203', 18, 3, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company203@example.com', '09170000203', NULL, NULL, NULL, 'PCAB-80844', 'C', '2026-12-31', 'BP-203', 'Zamboanga', '2026-01-01', 'TIN-203', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 15, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1155, 204, 'Construction Co 204', 15, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company204@example.com', '09170000204', NULL, NULL, NULL, 'PCAB-50885', 'B', '2026-12-31', 'BP-204', 'Zamboanga', '2026-01-01', 'TIN-204', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 35, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1156, 205, 'Construction Co 205', 15, 4, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company205@example.com', '09170000205', NULL, NULL, NULL, 'PCAB-40549', 'C', '2026-12-31', 'BP-205', 'Zamboanga', '2026-01-01', 'TIN-205', 'dti_cert.jpg', 'pending', NULL, NULL, 17, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1157, 206, 'Construction Co 206', 3, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company206@example.com', '09170000206', NULL, NULL, NULL, 'PCAB-20758', 'A', '2026-12-31', 'BP-206', 'Zamboanga', '2026-01-01', 'TIN-206', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 2, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1158, 207, 'Construction Co 207', 8, 8, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company207@example.com', '09170000207', NULL, NULL, NULL, 'PCAB-13226', 'B', '2026-12-31', 'BP-207', 'Zamboanga', '2026-01-01', 'TIN-207', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 31, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1159, 208, 'Construction Co 208', 10, 5, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company208@example.com', '09170000208', NULL, NULL, NULL, 'PCAB-76634', 'AAA', '2026-12-31', 'BP-208', 'Zamboanga', '2026-01-01', 'TIN-208', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 40, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1160, 209, 'Construction Co 209', 1, 4, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company209@example.com', '09170000209', NULL, NULL, NULL, 'PCAB-71246', 'B', '2026-12-31', 'BP-209', 'Zamboanga', '2026-01-01', 'TIN-209', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 29, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1161, 210, 'Construction Co 210', 8, 9, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company210@example.com', '09170000210', NULL, NULL, NULL, 'PCAB-34077', 'C', '2026-12-31', 'BP-210', 'Zamboanga', '2026-01-01', 'TIN-210', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 2, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1162, 211, 'Construction Co 211', 8, 7, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company211@example.com', '09170000211', NULL, NULL, NULL, 'PCAB-84395', 'AAA', '2026-12-31', 'BP-211', 'Zamboanga', '2026-01-01', 'TIN-211', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 35, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1163, 212, 'Construction Co 212', 17, 1, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company212@example.com', '09170000212', NULL, NULL, NULL, 'PCAB-86231', 'C', '2026-12-31', 'BP-212', 'Zamboanga', '2026-01-01', 'TIN-212', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 36, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1164, 213, 'Construction Co 213', 3, 3, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company213@example.com', '09170000213', NULL, NULL, NULL, 'PCAB-42944', 'B', '2026-12-31', 'BP-213', 'Zamboanga', '2026-01-01', 'TIN-213', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 24, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1165, 214, 'Construction Co 214', 6, 2, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company214@example.com', '09170000214', NULL, NULL, NULL, 'PCAB-53739', 'A', '2026-12-31', 'BP-214', 'Zamboanga', '2026-01-01', 'TIN-214', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 22, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1166, 215, 'Construction Co 215', 2, 6, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company215@example.com', '09170000215', NULL, NULL, NULL, 'PCAB-79818', 'AAA', '2026-12-31', 'BP-215', 'Zamboanga', '2026-01-01', 'TIN-215', 'dti_cert.jpg', 'pending', NULL, NULL, 7, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1167, 216, 'Construction Co 216', 10, 2, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company216@example.com', '09170000216', NULL, NULL, NULL, 'PCAB-76301', 'AAA', '2026-12-31', 'BP-216', 'Zamboanga', '2026-01-01', 'TIN-216', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 48, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1168, 217, 'Construction Co 217', 3, 8, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company217@example.com', '09170000217', NULL, NULL, NULL, 'PCAB-78607', 'AAA', '2026-12-31', 'BP-217', 'Zamboanga', '2026-01-01', 'TIN-217', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 42, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1169, 218, 'Construction Co 218', 11, 8, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company218@example.com', '09170000218', NULL, NULL, NULL, 'PCAB-94154', 'C', '2026-12-31', 'BP-218', 'Zamboanga', '2026-01-01', 'TIN-218', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 47, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1170, 219, 'Construction Co 219', 18, 4, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company219@example.com', '09170000219', NULL, NULL, NULL, 'PCAB-88946', 'B', '2026-12-31', 'BP-219', 'Zamboanga', '2026-01-01', 'TIN-219', 'dti_cert.jpg', 'approved', '2025-12-15 05:39:51', NULL, 49, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1171, 220, 'Construction Co 220', 8, 9, NULL, 'General Construction, Renovation', 'Zamboanga City', 'company220@example.com', '09170000220', NULL, NULL, NULL, 'PCAB-10662', 'A', '2026-12-31', 'BP-220', 'Zamboanga', '2026-01-01', 'TIN-220', 'dti_cert.jpg', 'rejected', NULL, 'Permit expired.', 24, '2025-12-15 05:39:51', '2025-12-15 05:39:51');

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
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contractor_users`
--

INSERT INTO `contractor_users` (`contractor_user_id`, `contractor_id`, `user_id`, `authorized_rep_lname`, `authorized_rep_mname`, `authorized_rep_fname`, `phone_number`, `role`, `is_active`, `created_at`) VALUES
(1052, 1052, 1, 'RepLast1', NULL, 'RepFirst1', '09170000001', 'manager', 1, '2025-12-15 05:39:51'),
(1053, 1053, 2, 'RepLast2', NULL, 'RepFirst2', '09170000002', 'manager', 1, '2025-12-15 05:39:51'),
(1054, 1054, 3, 'RepLast3', NULL, 'RepFirst3', '09170000003', 'owner', 1, '2025-12-15 05:39:51'),
(1055, 1055, 4, 'RepLast4', NULL, 'RepFirst4', '09170000004', 'manager', 1, '2025-12-15 05:39:51'),
(1056, 1056, 5, 'RepLast5', NULL, 'RepFirst5', '09170000005', 'engineer', 1, '2025-12-15 05:39:51'),
(1057, 1057, 6, 'RepLast6', NULL, 'RepFirst6', '09170000006', 'engineer', 1, '2025-12-15 05:39:51'),
(1058, 1058, 7, 'RepLast7', NULL, 'RepFirst7', '09170000007', 'engineer', 1, '2025-12-15 05:39:51'),
(1059, 1059, 8, 'RepLast8', NULL, 'RepFirst8', '09170000008', 'engineer', 1, '2025-12-15 05:39:51'),
(1060, 1060, 9, 'RepLast9', NULL, 'RepFirst9', '09170000009', 'manager', 1, '2025-12-15 05:39:51'),
(1061, 1061, 10, 'RepLast10', NULL, 'RepFirst10', '09170000010', 'owner', 1, '2025-12-15 05:39:51'),
(1062, 1062, 11, 'RepLast11', NULL, 'RepFirst11', '09170000011', 'owner', 1, '2025-12-15 05:39:51'),
(1063, 1063, 12, 'RepLast12', NULL, 'RepFirst12', '09170000012', 'manager', 1, '2025-12-15 05:39:51'),
(1064, 1064, 13, 'RepLast13', NULL, 'RepFirst13', '09170000013', 'engineer', 1, '2025-12-15 05:39:51'),
(1065, 1065, 14, 'RepLast14', NULL, 'RepFirst14', '09170000014', 'owner', 1, '2025-12-15 05:39:51'),
(1066, 1066, 15, 'RepLast15', NULL, 'RepFirst15', '09170000015', 'manager', 1, '2025-12-15 05:39:51'),
(1067, 1067, 16, 'RepLast16', NULL, 'RepFirst16', '09170000016', 'owner', 1, '2025-12-15 05:39:51'),
(1068, 1068, 17, 'RepLast17', NULL, 'RepFirst17', '09170000017', 'engineer', 1, '2025-12-15 05:39:51'),
(1069, 1069, 18, 'RepLast18', NULL, 'RepFirst18', '09170000018', 'owner', 1, '2025-12-15 05:39:51'),
(1070, 1070, 19, 'RepLast19', NULL, 'RepFirst19', '09170000019', 'owner', 1, '2025-12-15 05:39:51'),
(1071, 1071, 20, 'RepLast20', NULL, 'RepFirst20', '09170000020', 'manager', 1, '2025-12-15 05:39:51'),
(1072, 1072, 21, 'RepLast21', NULL, 'RepFirst21', '09170000021', 'engineer', 1, '2025-12-15 05:39:51'),
(1073, 1073, 22, 'RepLast22', NULL, 'RepFirst22', '09170000022', 'engineer', 1, '2025-12-15 05:39:51'),
(1074, 1074, 23, 'RepLast23', NULL, 'RepFirst23', '09170000023', 'owner', 1, '2025-12-15 05:39:51'),
(1075, 1075, 24, 'RepLast24', NULL, 'RepFirst24', '09170000024', 'engineer', 1, '2025-12-15 05:39:51'),
(1076, 1076, 25, 'RepLast25', NULL, 'RepFirst25', '09170000025', 'manager', 1, '2025-12-15 05:39:51'),
(1077, 1077, 26, 'RepLast26', NULL, 'RepFirst26', '09170000026', 'owner', 1, '2025-12-15 05:39:51'),
(1078, 1078, 27, 'RepLast27', NULL, 'RepFirst27', '09170000027', 'manager', 1, '2025-12-15 05:39:51'),
(1079, 1079, 28, 'RepLast28', NULL, 'RepFirst28', '09170000028', 'engineer', 1, '2025-12-15 05:39:51'),
(1080, 1080, 29, 'RepLast29', NULL, 'RepFirst29', '09170000029', 'manager', 1, '2025-12-15 05:39:51'),
(1081, 1081, 30, 'RepLast30', NULL, 'RepFirst30', '09170000030', 'engineer', 1, '2025-12-15 05:39:51'),
(1082, 1082, 31, 'RepLast31', NULL, 'RepFirst31', '09170000031', 'owner', 1, '2025-12-15 05:39:51'),
(1083, 1083, 32, 'RepLast32', NULL, 'RepFirst32', '09170000032', 'manager', 1, '2025-12-15 05:39:51'),
(1084, 1084, 33, 'RepLast33', NULL, 'RepFirst33', '09170000033', 'manager', 1, '2025-12-15 05:39:51'),
(1085, 1085, 34, 'RepLast34', NULL, 'RepFirst34', '09170000034', 'owner', 1, '2025-12-15 05:39:51'),
(1086, 1086, 35, 'RepLast35', NULL, 'RepFirst35', '09170000035', 'manager', 1, '2025-12-15 05:39:51'),
(1087, 1087, 36, 'RepLast36', NULL, 'RepFirst36', '09170000036', 'engineer', 1, '2025-12-15 05:39:51'),
(1088, 1088, 37, 'RepLast37', NULL, 'RepFirst37', '09170000037', 'engineer', 1, '2025-12-15 05:39:51'),
(1089, 1089, 38, 'RepLast38', NULL, 'RepFirst38', '09170000038', 'manager', 1, '2025-12-15 05:39:51'),
(1090, 1090, 39, 'RepLast39', NULL, 'RepFirst39', '09170000039', 'owner', 1, '2025-12-15 05:39:51'),
(1091, 1091, 40, 'RepLast40', NULL, 'RepFirst40', '09170000040', 'engineer', 1, '2025-12-15 05:39:51'),
(1092, 1092, 41, 'RepLast41', NULL, 'RepFirst41', '09170000041', 'engineer', 1, '2025-12-15 05:39:51'),
(1093, 1093, 42, 'RepLast42', NULL, 'RepFirst42', '09170000042', 'manager', 1, '2025-12-15 05:39:51'),
(1094, 1094, 43, 'RepLast43', NULL, 'RepFirst43', '09170000043', 'manager', 1, '2025-12-15 05:39:51'),
(1095, 1095, 44, 'RepLast44', NULL, 'RepFirst44', '09170000044', 'manager', 1, '2025-12-15 05:39:51'),
(1096, 1096, 45, 'RepLast45', NULL, 'RepFirst45', '09170000045', 'owner', 1, '2025-12-15 05:39:51'),
(1097, 1097, 46, 'RepLast46', NULL, 'RepFirst46', '09170000046', 'owner', 1, '2025-12-15 05:39:51'),
(1098, 1098, 47, 'RepLast47', NULL, 'RepFirst47', '09170000047', 'engineer', 1, '2025-12-15 05:39:51'),
(1099, 1099, 48, 'RepLast48', NULL, 'RepFirst48', '09170000048', 'engineer', 1, '2025-12-15 05:39:51'),
(1100, 1100, 49, 'RepLast49', NULL, 'RepFirst49', '09170000049', 'engineer', 1, '2025-12-15 05:39:51'),
(1101, 1101, 50, 'RepLast50', NULL, 'RepFirst50', '09170000050', 'owner', 1, '2025-12-15 05:39:51'),
(1102, 1102, 51, 'RepLast51', NULL, 'RepFirst51', '09170000051', 'engineer', 1, '2025-12-15 05:39:51'),
(1103, 1103, 52, 'RepLast52', NULL, 'RepFirst52', '09170000052', 'engineer', 1, '2025-12-15 05:39:51'),
(1104, 1104, 53, 'RepLast53', NULL, 'RepFirst53', '09170000053', 'engineer', 1, '2025-12-15 05:39:51'),
(1105, 1105, 54, 'RepLast54', NULL, 'RepFirst54', '09170000054', 'manager', 1, '2025-12-15 05:39:51'),
(1106, 1106, 55, 'RepLast55', NULL, 'RepFirst55', '09170000055', 'owner', 1, '2025-12-15 05:39:51'),
(1107, 1107, 56, 'RepLast56', NULL, 'RepFirst56', '09170000056', 'engineer', 1, '2025-12-15 05:39:51'),
(1108, 1108, 57, 'RepLast57', NULL, 'RepFirst57', '09170000057', 'manager', 1, '2025-12-15 05:39:51'),
(1109, 1109, 58, 'RepLast58', NULL, 'RepFirst58', '09170000058', 'owner', 1, '2025-12-15 05:39:51'),
(1110, 1110, 59, 'RepLast59', NULL, 'RepFirst59', '09170000059', 'manager', 1, '2025-12-15 05:39:51'),
(1111, 1111, 60, 'RepLast60', NULL, 'RepFirst60', '09170000060', 'engineer', 1, '2025-12-15 05:39:51'),
(1112, 1112, 61, 'RepLast61', NULL, 'RepFirst61', '09170000061', 'owner', 1, '2025-12-15 05:39:51'),
(1113, 1113, 62, 'RepLast62', NULL, 'RepFirst62', '09170000062', 'owner', 1, '2025-12-15 05:39:51'),
(1114, 1114, 63, 'RepLast63', NULL, 'RepFirst63', '09170000063', 'owner', 1, '2025-12-15 05:39:51'),
(1115, 1115, 64, 'RepLast64', NULL, 'RepFirst64', '09170000064', 'manager', 1, '2025-12-15 05:39:51'),
(1116, 1116, 65, 'RepLast65', NULL, 'RepFirst65', '09170000065', 'owner', 1, '2025-12-15 05:39:51'),
(1117, 1117, 66, 'RepLast66', NULL, 'RepFirst66', '09170000066', 'owner', 1, '2025-12-15 05:39:51'),
(1118, 1118, 67, 'RepLast67', NULL, 'RepFirst67', '09170000067', 'engineer', 1, '2025-12-15 05:39:51'),
(1119, 1119, 68, 'RepLast68', NULL, 'RepFirst68', '09170000068', 'owner', 1, '2025-12-15 05:39:51'),
(1120, 1120, 69, 'RepLast69', NULL, 'RepFirst69', '09170000069', 'owner', 1, '2025-12-15 05:39:51'),
(1121, 1121, 70, 'RepLast70', NULL, 'RepFirst70', '09170000070', 'owner', 1, '2025-12-15 05:39:51'),
(1122, 1122, 71, 'RepLast71', NULL, 'RepFirst71', '09170000071', 'engineer', 1, '2025-12-15 05:39:51'),
(1123, 1123, 72, 'RepLast72', NULL, 'RepFirst72', '09170000072', 'owner', 1, '2025-12-15 05:39:51'),
(1124, 1124, 73, 'RepLast73', NULL, 'RepFirst73', '09170000073', 'engineer', 1, '2025-12-15 05:39:51'),
(1125, 1125, 74, 'RepLast74', NULL, 'RepFirst74', '09170000074', 'manager', 1, '2025-12-15 05:39:51'),
(1126, 1126, 75, 'RepLast75', NULL, 'RepFirst75', '09170000075', 'owner', 1, '2025-12-15 05:39:51'),
(1127, 1127, 76, 'RepLast76', NULL, 'RepFirst76', '09170000076', 'manager', 1, '2025-12-15 05:39:51'),
(1128, 1128, 77, 'RepLast77', NULL, 'RepFirst77', '09170000077', 'owner', 1, '2025-12-15 05:39:51'),
(1129, 1129, 78, 'RepLast78', NULL, 'RepFirst78', '09170000078', 'manager', 1, '2025-12-15 05:39:51'),
(1130, 1130, 79, 'RepLast79', NULL, 'RepFirst79', '09170000079', 'manager', 1, '2025-12-15 05:39:51'),
(1131, 1131, 80, 'RepLast80', NULL, 'RepFirst80', '09170000080', 'manager', 1, '2025-12-15 05:39:51'),
(1132, 1132, 81, 'RepLast81', NULL, 'RepFirst81', '09170000081', 'owner', 1, '2025-12-15 05:39:51'),
(1133, 1133, 82, 'RepLast82', NULL, 'RepFirst82', '09170000082', 'manager', 1, '2025-12-15 05:39:51'),
(1134, 1134, 83, 'RepLast83', NULL, 'RepFirst83', '09170000083', 'engineer', 1, '2025-12-15 05:39:51'),
(1135, 1135, 84, 'RepLast84', NULL, 'RepFirst84', '09170000084', 'owner', 1, '2025-12-15 05:39:51'),
(1136, 1136, 85, 'RepLast85', NULL, 'RepFirst85', '09170000085', 'manager', 1, '2025-12-15 05:39:51'),
(1137, 1137, 86, 'RepLast86', NULL, 'RepFirst86', '09170000086', 'manager', 1, '2025-12-15 05:39:51'),
(1138, 1138, 87, 'RepLast87', NULL, 'RepFirst87', '09170000087', 'engineer', 1, '2025-12-15 05:39:51'),
(1139, 1139, 88, 'RepLast88', NULL, 'RepFirst88', '09170000088', 'owner', 1, '2025-12-15 05:39:51'),
(1140, 1140, 89, 'RepLast89', NULL, 'RepFirst89', '09170000089', 'engineer', 1, '2025-12-15 05:39:51'),
(1141, 1141, 90, 'RepLast90', NULL, 'RepFirst90', '09170000090', 'owner', 1, '2025-12-15 05:39:51'),
(1142, 1142, 91, 'RepLast91', NULL, 'RepFirst91', '09170000091', 'manager', 1, '2025-12-15 05:39:51'),
(1143, 1143, 92, 'RepLast92', NULL, 'RepFirst92', '09170000092', 'engineer', 1, '2025-12-15 05:39:51'),
(1144, 1144, 93, 'RepLast93', NULL, 'RepFirst93', '09170000093', 'manager', 1, '2025-12-15 05:39:51'),
(1145, 1145, 94, 'RepLast94', NULL, 'RepFirst94', '09170000094', 'engineer', 1, '2025-12-15 05:39:51'),
(1146, 1146, 95, 'RepLast95', NULL, 'RepFirst95', '09170000095', 'owner', 1, '2025-12-15 05:39:51'),
(1147, 1147, 96, 'RepLast96', NULL, 'RepFirst96', '09170000096', 'manager', 1, '2025-12-15 05:39:51'),
(1148, 1148, 97, 'RepLast97', NULL, 'RepFirst97', '09170000097', 'owner', 1, '2025-12-15 05:39:51'),
(1149, 1149, 98, 'RepLast98', NULL, 'RepFirst98', '09170000098', 'manager', 1, '2025-12-15 05:39:51'),
(1150, 1150, 99, 'RepLast99', NULL, 'RepFirst99', '09170000099', 'owner', 1, '2025-12-15 05:39:51'),
(1151, 1151, 100, 'RepLast100', NULL, 'RepFirst100', '09170000100', 'manager', 1, '2025-12-15 05:39:51'),
(1152, 1152, 201, 'RepLast201', NULL, 'RepFirst201', '09170000201', 'owner', 1, '2025-12-15 05:39:51'),
(1153, 1153, 202, 'RepLast202', NULL, 'RepFirst202', '09170000202', 'engineer', 1, '2025-12-15 05:39:51'),
(1154, 1154, 203, 'RepLast203', NULL, 'RepFirst203', '09170000203', 'manager', 1, '2025-12-15 05:39:51'),
(1155, 1155, 204, 'RepLast204', NULL, 'RepFirst204', '09170000204', 'manager', 1, '2025-12-15 05:39:51'),
(1156, 1156, 205, 'RepLast205', NULL, 'RepFirst205', '09170000205', 'manager', 1, '2025-12-15 05:39:51'),
(1157, 1157, 206, 'RepLast206', NULL, 'RepFirst206', '09170000206', 'owner', 1, '2025-12-15 05:39:51'),
(1158, 1158, 207, 'RepLast207', NULL, 'RepFirst207', '09170000207', 'owner', 1, '2025-12-15 05:39:51'),
(1159, 1159, 208, 'RepLast208', NULL, 'RepFirst208', '09170000208', 'owner', 1, '2025-12-15 05:39:51'),
(1160, 1160, 209, 'RepLast209', NULL, 'RepFirst209', '09170000209', 'owner', 1, '2025-12-15 05:39:51'),
(1161, 1161, 210, 'RepLast210', NULL, 'RepFirst210', '09170000210', 'engineer', 1, '2025-12-15 05:39:51'),
(1162, 1162, 211, 'RepLast211', NULL, 'RepFirst211', '09170000211', 'engineer', 1, '2025-12-15 05:39:51'),
(1163, 1163, 212, 'RepLast212', NULL, 'RepFirst212', '09170000212', 'engineer', 1, '2025-12-15 05:39:51'),
(1164, 1164, 213, 'RepLast213', NULL, 'RepFirst213', '09170000213', 'engineer', 1, '2025-12-15 05:39:51'),
(1165, 1165, 214, 'RepLast214', NULL, 'RepFirst214', '09170000214', 'manager', 1, '2025-12-15 05:39:51'),
(1166, 1166, 215, 'RepLast215', NULL, 'RepFirst215', '09170000215', 'owner', 1, '2025-12-15 05:39:51'),
(1167, 1167, 216, 'RepLast216', NULL, 'RepFirst216', '09170000216', 'manager', 1, '2025-12-15 05:39:51'),
(1168, 1168, 217, 'RepLast217', NULL, 'RepFirst217', '09170000217', 'manager', 1, '2025-12-15 05:39:51'),
(1169, 1169, 218, 'RepLast218', NULL, 'RepFirst218', '09170000218', 'owner', 1, '2025-12-15 05:39:51'),
(1170, 1170, 219, 'RepLast219', NULL, 'RepFirst219', '09170000219', 'owner', 1, '2025-12-15 05:39:51'),
(1171, 1171, 220, 'RepLast220', NULL, 'RepFirst220', '09170000220', 'owner', 1, '2025-12-15 05:39:51');

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
(44, 697, 142, 4, NULL, NULL, 'Delay', NULL, 'Project is delayed by 2 weeks.', 'open', NULL, NULL, '2025-12-15 05:39:52', NULL),
(45, 698, 158, 6, NULL, NULL, 'Delay', NULL, 'Project is delayed by 2 weeks.', 'open', NULL, NULL, '2025-12-15 05:39:52', NULL),
(46, 699, 196, 14, NULL, NULL, 'Delay', NULL, 'Project is delayed by 2 weeks.', 'open', NULL, NULL, '2025-12-15 05:39:52', NULL),
(47, 700, 195, 17, NULL, NULL, 'Delay', NULL, 'Project is delayed by 2 weeks.', 'open', NULL, NULL, '2025-12-15 05:39:52', NULL),
(48, 701, 202, 28, NULL, NULL, 'Delay', NULL, 'Project is delayed by 2 weeks.', 'open', NULL, NULL, '2025-12-15 05:39:52', NULL);

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
(831, 697, 1055, 575, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(832, 697, 1055, 575, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(833, 697, 1055, 575, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(834, 698, 1057, 576, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(835, 698, 1057, 576, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(836, 698, 1057, 576, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(837, 699, 1065, 577, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(838, 699, 1065, 577, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(839, 699, 1065, 577, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(840, 700, 1068, 578, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(841, 700, 1068, 578, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(842, 700, 1068, 578, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(843, 701, 1079, 579, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(844, 701, 1079, 579, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(845, 701, 1079, 579, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(846, 702, 1083, 580, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(847, 702, 1083, 580, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(848, 702, 1083, 580, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(849, 703, 1083, 581, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(850, 703, 1083, 581, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(851, 703, 1083, 581, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(852, 704, 1084, 582, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(853, 704, 1084, 582, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(854, 704, 1084, 582, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(855, 705, 1089, 583, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(856, 705, 1089, 583, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(857, 705, 1089, 583, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(858, 706, 1089, 584, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(859, 706, 1089, 584, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(860, 706, 1089, 584, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(861, 707, 1089, 585, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(862, 707, 1089, 585, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(863, 707, 1089, 585, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(864, 708, 1090, 586, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(865, 708, 1090, 586, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(866, 708, 1090, 586, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(867, 709, 1094, 587, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(868, 709, 1094, 587, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(869, 709, 1094, 587, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(870, 710, 1098, 588, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(871, 710, 1098, 588, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(872, 710, 1098, 588, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(873, 711, 1100, 589, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(874, 711, 1100, 589, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(875, 711, 1100, 589, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(876, 712, 1105, 590, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(877, 712, 1105, 590, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(878, 712, 1105, 590, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(879, 713, 1107, 591, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(880, 713, 1107, 591, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(881, 713, 1107, 591, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(882, 714, 1109, 592, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(883, 714, 1109, 592, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(884, 714, 1109, 592, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(885, 715, 1123, 593, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(886, 715, 1123, 593, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(887, 715, 1123, 593, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(888, 716, 1127, 594, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(889, 716, 1127, 594, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(890, 716, 1127, 594, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(891, 717, 1132, 595, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(892, 717, 1132, 595, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(893, 717, 1132, 595, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(894, 718, 1133, 596, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(895, 718, 1133, 596, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(896, 718, 1133, 596, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(897, 719, 1133, 597, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(898, 719, 1133, 597, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(899, 719, 1133, 597, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(900, 720, 1135, 598, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(901, 720, 1135, 598, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(902, 720, 1135, 598, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(903, 721, 1145, 599, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(904, 721, 1145, 599, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(905, 721, 1145, 599, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(906, 722, 1147, 600, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(907, 722, 1147, 600, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(908, 722, 1147, 600, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(909, 723, 1149, 601, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(910, 723, 1149, 601, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(911, 723, 1149, 601, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(912, 724, 1150, 602, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(913, 724, 1150, 602, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(914, 724, 1150, 602, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(915, 725, 1159, 603, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(916, 725, 1159, 603, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(917, 725, 1159, 603, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(918, 726, 1162, 604, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(919, 726, 1162, 604, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(920, 726, 1162, 604, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(921, 727, 1163, 605, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(922, 727, 1163, 605, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(923, 727, 1163, 605, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(924, 728, 1164, 606, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(925, 728, 1164, 606, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(926, 728, 1164, 606, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(927, 729, 1167, 607, 'Milestone 1', 'Foundation', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(928, 729, 1167, 607, 'Milestone 2', 'Structure', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(929, 729, 1167, 607, 'Milestone 3', 'Finishing', 'in_progress', '2025-12-15 13:39:51', '2026-01-14 13:39:51', NULL, NULL, 'approved', NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51');

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
(1152, 831, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1153, 832, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1154, 833, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1155, 834, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1156, 835, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1157, 836, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1158, 837, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1159, 838, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1160, 839, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1161, 840, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1162, 841, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1163, 842, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1164, 843, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1165, 844, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1166, 845, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1167, 846, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1168, 847, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1169, 848, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1170, 849, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1171, 850, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1172, 851, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1173, 852, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1174, 853, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1175, 854, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1176, 855, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1177, 856, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1178, 857, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1179, 858, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1180, 859, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1181, 860, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1182, 861, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1183, 862, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1184, 863, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1185, 864, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1186, 865, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1187, 866, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1188, 867, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1189, 868, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1190, 869, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1191, 870, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1192, 871, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1193, 872, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1194, 873, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1195, 874, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1196, 875, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1197, 876, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1198, 877, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1199, 878, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1200, 879, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1201, 880, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1202, 881, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1203, 882, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1204, 883, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1205, 884, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1206, 885, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1207, 886, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1208, 887, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1209, 888, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1210, 889, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1211, 890, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1212, 891, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1213, 892, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1214, 893, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1215, 894, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1216, 895, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1217, 896, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1218, 897, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1219, 898, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1220, 899, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1221, 900, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1222, 901, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1223, 902, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1224, 903, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1225, 904, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1226, 905, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1227, 906, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1228, 907, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1229, 908, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1230, 909, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1231, 910, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1232, 911, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1233, 912, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1234, 913, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1235, 914, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1236, 915, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1237, 916, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1238, 917, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1239, 918, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1240, 919, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1241, 920, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1242, 921, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1243, 922, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1244, 923, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1245, 924, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1246, 925, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1247, 926, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1248, 927, 1, 100.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1249, 928, 1, 50.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1250, 929, 1, 40.00, 'Primary Task', 'Main deliverable description.', 25000.00, '2025-12-25 13:39:51'),
(1279, 832, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1280, 833, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1281, 835, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1282, 836, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1283, 838, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1284, 839, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1285, 841, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1286, 842, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1287, 844, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1288, 845, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1289, 847, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1290, 848, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1291, 850, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1292, 851, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1293, 853, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1294, 854, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1295, 856, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1296, 857, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1297, 859, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1298, 860, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1299, 862, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1300, 863, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1301, 865, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1302, 866, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1303, 868, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1304, 869, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1305, 871, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1306, 872, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1307, 874, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1308, 875, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1309, 877, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1310, 878, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1311, 880, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1312, 881, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1313, 883, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1314, 884, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1315, 886, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1316, 887, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1317, 889, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1318, 890, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1319, 892, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1320, 893, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1321, 895, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1322, 896, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1323, 898, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1324, 899, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1325, 901, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1326, 902, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1327, 904, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1328, 905, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1329, 907, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1330, 908, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1331, 910, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1332, 911, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1333, 913, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1334, 914, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1335, 916, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1336, 917, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1337, 919, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1338, 920, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1339, 922, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1340, 923, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1341, 925, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1342, 926, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1343, 928, 2, 50.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1344, 929, 2, 30.00, 'Secondary Task', 'Follow-up task description.', 15000.00, '2026-01-04 13:39:51'),
(1406, 833, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1407, 836, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1408, 839, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1409, 842, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1410, 845, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1411, 848, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1412, 851, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1413, 854, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1414, 857, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1415, 860, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1416, 863, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1417, 866, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1418, 869, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1419, 872, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1420, 875, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1421, 878, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1422, 881, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1423, 884, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1424, 887, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1425, 890, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1426, 893, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1427, 896, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1428, 899, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1429, 902, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1430, 905, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1431, 908, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1432, 911, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1433, 914, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1434, 917, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1435, 920, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1436, 923, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1437, 926, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51'),
(1438, 929, 3, 30.00, 'Final Task', 'Closing task description.', 10000.00, '2026-01-14 13:39:51');

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
(449, 1152, 697, 1093, 1055, 25000.00, 'bank_transfer', 'TXN-13422', 'receipt.jpg', '2025-12-15', 'submitted', 'Receipt unclear', NULL),
(450, 1153, 697, 1093, 1055, 25000.00, 'bank_transfer', 'TXN-85514', 'receipt.jpg', '2025-12-15', 'approved', 'Receipt unclear', NULL),
(451, 1279, 697, 1093, 1055, 25000.00, 'bank_transfer', 'TXN-63965', 'receipt.jpg', '2025-12-15', 'submitted', 'Receipt unclear', NULL),
(452, 1154, 697, 1093, 1055, 25000.00, 'bank_transfer', 'TXN-85713', 'receipt.jpg', '2025-12-15', 'rejected', 'Receipt unclear', NULL),
(453, 1280, 697, 1093, 1055, 25000.00, 'bank_transfer', 'TXN-62869', 'receipt.jpg', '2025-12-15', 'approved', 'Receipt unclear', NULL),
(454, 1406, 697, 1093, 1055, 25000.00, 'bank_transfer', 'TXN-63100', 'receipt.jpg', '2025-12-15', 'approved', 'Receipt unclear', NULL),
(455, 1155, 698, 1109, 1057, 25000.00, 'bank_transfer', 'TXN-93073', 'receipt.jpg', '2025-12-15', 'rejected', 'Receipt unclear', NULL),
(456, 1156, 698, 1109, 1057, 25000.00, 'bank_transfer', 'TXN-30502', 'receipt.jpg', '2025-12-15', 'submitted', 'Receipt unclear', NULL),
(457, 1281, 698, 1109, 1057, 25000.00, 'bank_transfer', 'TXN-11888', 'receipt.jpg', '2025-12-15', 'rejected', 'Receipt unclear', NULL),
(458, 1157, 698, 1109, 1057, 25000.00, 'bank_transfer', 'TXN-41362', 'receipt.jpg', '2025-12-15', 'rejected', 'Receipt unclear', NULL),
(459, 1282, 698, 1109, 1057, 25000.00, 'bank_transfer', 'TXN-48880', 'receipt.jpg', '2025-12-15', 'submitted', 'Receipt unclear', NULL),
(460, 1407, 698, 1109, 1057, 25000.00, 'bank_transfer', 'TXN-16473', 'receipt.jpg', '2025-12-15', 'submitted', 'Receipt unclear', NULL),
(461, 1158, 699, 1147, 1065, 25000.00, 'bank_transfer', 'TXN-53078', 'receipt.jpg', '2025-12-15', 'rejected', 'Receipt unclear', NULL),
(462, 1159, 699, 1147, 1065, 25000.00, 'bank_transfer', 'TXN-50219', 'receipt.jpg', '2025-12-15', 'submitted', 'Receipt unclear', NULL),
(463, 1283, 699, 1147, 1065, 25000.00, 'bank_transfer', 'TXN-26467', 'receipt.jpg', '2025-12-15', 'submitted', 'Receipt unclear', NULL),
(464, 1160, 699, 1147, 1065, 25000.00, 'bank_transfer', 'TXN-50906', 'receipt.jpg', '2025-12-15', 'submitted', 'Receipt unclear', NULL),
(465, 1284, 699, 1147, 1065, 25000.00, 'bank_transfer', 'TXN-99007', 'receipt.jpg', '2025-12-15', 'submitted', 'Receipt unclear', NULL),
(466, 1408, 699, 1147, 1065, 25000.00, 'bank_transfer', 'TXN-72990', 'receipt.jpg', '2025-12-15', 'submitted', 'Receipt unclear', NULL),
(467, 1161, 700, 1146, 1068, 25000.00, 'bank_transfer', 'TXN-45760', 'receipt.jpg', '2025-12-15', 'rejected', 'Receipt unclear', NULL),
(468, 1162, 700, 1146, 1068, 25000.00, 'bank_transfer', 'TXN-32522', 'receipt.jpg', '2025-12-15', 'submitted', 'Receipt unclear', NULL),
(469, 1285, 700, 1146, 1068, 25000.00, 'bank_transfer', 'TXN-26083', 'receipt.jpg', '2025-12-15', 'approved', 'Receipt unclear', NULL),
(470, 1163, 700, 1146, 1068, 25000.00, 'bank_transfer', 'TXN-22881', 'receipt.jpg', '2025-12-15', 'rejected', 'Receipt unclear', NULL),
(471, 1286, 700, 1146, 1068, 25000.00, 'bank_transfer', 'TXN-19990', 'receipt.jpg', '2025-12-15', 'approved', 'Receipt unclear', NULL),
(472, 1409, 700, 1146, 1068, 25000.00, 'bank_transfer', 'TXN-83891', 'receipt.jpg', '2025-12-15', 'rejected', 'Receipt unclear', NULL),
(473, 1164, 701, 1153, 1079, 25000.00, 'bank_transfer', 'TXN-64021', 'receipt.jpg', '2025-12-15', 'approved', 'Receipt unclear', NULL),
(474, 1165, 701, 1153, 1079, 25000.00, 'bank_transfer', 'TXN-11385', 'receipt.jpg', '2025-12-15', 'approved', 'Receipt unclear', NULL),
(475, 1287, 701, 1153, 1079, 25000.00, 'bank_transfer', 'TXN-90135', 'receipt.jpg', '2025-12-15', 'submitted', 'Receipt unclear', NULL),
(476, 1166, 701, 1153, 1079, 25000.00, 'bank_transfer', 'TXN-80094', 'receipt.jpg', '2025-12-15', 'submitted', 'Receipt unclear', NULL),
(477, 1288, 701, 1153, 1079, 25000.00, 'bank_transfer', 'TXN-80574', 'receipt.jpg', '2025-12-15', 'rejected', 'Receipt unclear', NULL),
(478, 1410, 701, 1153, 1079, 25000.00, 'bank_transfer', 'TXN-90058', 'receipt.jpg', '2025-12-15', 'rejected', 'Receipt unclear', NULL),
(479, 1167, 702, 1064, 1083, 25000.00, 'bank_transfer', 'TXN-85945', 'receipt.jpg', '2025-12-15', 'approved', 'Receipt unclear', NULL),
(480, 1168, 702, 1064, 1083, 25000.00, 'bank_transfer', 'TXN-12441', 'receipt.jpg', '2025-12-15', 'approved', 'Receipt unclear', NULL),
(481, 1289, 702, 1064, 1083, 25000.00, 'bank_transfer', 'TXN-92879', 'receipt.jpg', '2025-12-15', 'rejected', 'Receipt unclear', NULL),
(482, 1169, 702, 1064, 1083, 25000.00, 'bank_transfer', 'TXN-32730', 'receipt.jpg', '2025-12-15', 'rejected', 'Receipt unclear', NULL),
(483, 1290, 702, 1064, 1083, 25000.00, 'bank_transfer', 'TXN-55974', 'receipt.jpg', '2025-12-15', 'rejected', 'Receipt unclear', NULL),
(484, 1411, 702, 1064, 1083, 25000.00, 'bank_transfer', 'TXN-48936', 'receipt.jpg', '2025-12-15', 'submitted', 'Receipt unclear', NULL),
(485, 1170, 703, 1125, 1083, 25000.00, 'bank_transfer', 'TXN-66014', 'receipt.jpg', '2025-12-15', 'approved', 'Receipt unclear', NULL),
(486, 1171, 703, 1125, 1083, 25000.00, 'bank_transfer', 'TXN-93844', 'receipt.jpg', '2025-12-15', 'rejected', 'Receipt unclear', NULL),
(487, 1291, 703, 1125, 1083, 25000.00, 'bank_transfer', 'TXN-18917', 'receipt.jpg', '2025-12-15', 'approved', 'Receipt unclear', NULL),
(488, 1172, 703, 1125, 1083, 25000.00, 'bank_transfer', 'TXN-54823', 'receipt.jpg', '2025-12-15', 'rejected', 'Receipt unclear', NULL),
(489, 1292, 703, 1125, 1083, 25000.00, 'bank_transfer', 'TXN-61390', 'receipt.jpg', '2025-12-15', 'approved', 'Receipt unclear', NULL),
(490, 1412, 703, 1125, 1083, 25000.00, 'bank_transfer', 'TXN-43008', 'receipt.jpg', '2025-12-15', 'approved', 'Receipt unclear', NULL),
(491, 1173, 704, 1088, 1084, 25000.00, 'bank_transfer', 'TXN-84652', 'receipt.jpg', '2025-12-15', 'approved', 'Receipt unclear', NULL),
(492, 1174, 704, 1088, 1084, 25000.00, 'bank_transfer', 'TXN-51323', 'receipt.jpg', '2025-12-15', 'submitted', 'Receipt unclear', NULL),
(493, 1293, 704, 1088, 1084, 25000.00, 'bank_transfer', 'TXN-33276', 'receipt.jpg', '2025-12-15', 'rejected', 'Receipt unclear', NULL),
(494, 1175, 704, 1088, 1084, 25000.00, 'bank_transfer', 'TXN-80335', 'receipt.jpg', '2025-12-15', 'submitted', 'Receipt unclear', NULL),
(495, 1294, 704, 1088, 1084, 25000.00, 'bank_transfer', 'TXN-56892', 'receipt.jpg', '2025-12-15', 'submitted', 'Receipt unclear', NULL),
(496, 1413, 704, 1088, 1084, 25000.00, 'bank_transfer', 'TXN-87929', 'receipt.jpg', '2025-12-15', 'submitted', 'Receipt unclear', NULL),
(497, 1176, 705, 1146, 1089, 25000.00, 'bank_transfer', 'TXN-78636', 'receipt.jpg', '2025-12-15', 'approved', 'Receipt unclear', NULL),
(498, 1177, 705, 1146, 1089, 25000.00, 'bank_transfer', 'TXN-72731', 'receipt.jpg', '2025-12-15', 'rejected', 'Receipt unclear', NULL);

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
(1375, 1, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1376, 10, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1377, 100, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1378, 101, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1379, 102, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1380, 103, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1381, 104, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1382, 105, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1383, 106, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1384, 107, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1385, 108, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1386, 109, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1387, 11, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1388, 110, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1389, 111, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1390, 112, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1391, 113, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1392, 114, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1393, 115, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1394, 116, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1395, 117, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1396, 118, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1397, 119, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1398, 12, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1399, 120, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1400, 121, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1401, 122, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1402, 123, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1403, 124, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1404, 125, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1405, 126, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1406, 127, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1407, 128, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1408, 129, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1409, 13, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1410, 130, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1411, 131, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1412, 132, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1413, 133, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1414, 134, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1415, 135, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1416, 136, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1417, 137, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1418, 138, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1419, 139, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1420, 14, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1421, 140, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1422, 141, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1423, 142, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1424, 143, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1425, 144, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1426, 145, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1427, 146, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1428, 147, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1429, 148, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1430, 149, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1431, 15, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1432, 150, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1433, 151, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1434, 152, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1435, 153, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1436, 154, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1437, 155, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1438, 156, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1439, 157, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1440, 158, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1441, 159, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1442, 16, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1443, 160, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1444, 161, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1445, 162, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1446, 163, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1447, 164, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1448, 165, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1449, 166, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1450, 167, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1451, 168, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1452, 169, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1453, 17, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1454, 170, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1455, 171, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1456, 172, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1457, 173, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1458, 174, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1459, 175, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1460, 176, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1461, 177, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1462, 178, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1463, 179, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1464, 18, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1465, 180, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1466, 181, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1467, 182, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1468, 183, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1469, 184, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1470, 185, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1471, 186, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1472, 187, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1473, 188, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1474, 189, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1475, 19, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1476, 190, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1477, 191, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1478, 192, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1479, 193, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1480, 194, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1481, 195, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1482, 196, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1483, 197, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1484, 198, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1485, 199, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1486, 2, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1487, 20, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1488, 200, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1489, 201, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1490, 202, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1491, 203, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1492, 204, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1493, 205, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1494, 206, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1495, 207, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1496, 208, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1497, 209, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1498, 21, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1499, 210, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1500, 211, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1501, 212, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1502, 213, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1503, 214, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1504, 215, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1505, 216, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1506, 217, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1507, 218, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1508, 219, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1509, 22, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1510, 220, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1511, 23, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1512, 24, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1513, 25, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1514, 26, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1515, 27, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1516, 28, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1517, 29, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1518, 3, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1519, 30, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1520, 31, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1521, 32, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1522, 33, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1523, 34, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1524, 35, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1525, 36, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1526, 37, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1527, 38, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1528, 39, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1529, 4, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1530, 40, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1531, 41, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1532, 42, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1533, 43, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1534, 44, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1535, 45, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1536, 46, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1537, 47, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1538, 48, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1539, 49, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1540, 5, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1541, 50, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1542, 51, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1543, 52, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1544, 53, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1545, 54, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1546, 55, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1547, 56, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1548, 57, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1549, 58, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1550, 59, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1551, 6, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1552, 60, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1553, 61, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1554, 62, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1555, 63, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1556, 64, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1557, 65, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1558, 66, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1559, 67, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1560, 68, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1561, 69, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1562, 7, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1563, 70, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1564, 71, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1565, 72, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1566, 73, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1567, 74, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1568, 75, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1569, 76, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1570, 77, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1571, 78, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1572, 79, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1573, 8, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1574, 80, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1575, 81, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1576, 82, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1577, 83, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1578, 84, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1579, 85, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1580, 86, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1581, 87, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1582, 88, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1583, 89, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1584, 9, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1585, 90, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1586, 91, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1587, 92, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1588, 93, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1589, 94, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1590, 95, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1591, 96, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1592, 97, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1593, 98, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51'),
(1594, 99, 'Welcome to Legatura! Please verify your profile.', 'Project Alert', 0, 'App', NULL, '2025-12-15 05:39:51');

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
(575, 697, 1055, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(576, 698, 1057, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(577, 699, 1065, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(578, 700, 1068, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(579, 701, 1079, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(580, 702, 1083, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(581, 703, 1083, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(582, 704, 1084, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(583, 705, 1089, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(584, 706, 1089, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(585, 707, 1089, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(586, 708, 1090, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(587, 709, 1094, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(588, 710, 1098, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(589, 711, 1100, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(590, 712, 1105, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(591, 713, 1107, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(592, 714, 1109, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(593, 715, 1123, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(594, 716, 1127, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(595, 717, 1132, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(596, 718, 1133, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(597, 719, 1133, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(598, 720, 1135, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(599, 721, 1145, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(600, 722, 1147, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(601, 723, 1149, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(602, 724, 1150, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(603, 725, 1159, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(604, 726, 1162, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(605, 727, 1163, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(606, 728, 1164, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(607, 729, 1167, 'downpayment', 500000.00, 100000.00, 1, '2025-12-15 05:39:51', '2025-12-15 05:39:51');

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
(449, 1152, 'Weekly Site Update', 'approved', 'Images too dark', '2025-12-15 05:39:51', NULL),
(450, 1153, 'Weekly Site Update', 'rejected', 'Images too dark', '2025-12-15 05:39:51', NULL),
(451, 1279, 'Weekly Site Update', 'rejected', 'Images too dark', '2025-12-15 05:39:51', NULL),
(452, 1154, 'Weekly Site Update', 'submitted', 'Images too dark', '2025-12-15 05:39:51', NULL),
(453, 1280, 'Weekly Site Update', 'approved', 'Images too dark', '2025-12-15 05:39:51', NULL),
(454, 1406, 'Weekly Site Update', 'approved', 'Images too dark', '2025-12-15 05:39:51', NULL),
(455, 1155, 'Weekly Site Update', 'approved', 'Images too dark', '2025-12-15 05:39:51', NULL),
(456, 1156, 'Weekly Site Update', 'approved', 'Images too dark', '2025-12-15 05:39:51', NULL),
(457, 1281, 'Weekly Site Update', 'rejected', 'Images too dark', '2025-12-15 05:39:51', NULL),
(458, 1157, 'Weekly Site Update', 'submitted', 'Images too dark', '2025-12-15 05:39:51', NULL),
(459, 1282, 'Weekly Site Update', 'approved', 'Images too dark', '2025-12-15 05:39:51', NULL),
(460, 1407, 'Weekly Site Update', 'rejected', 'Images too dark', '2025-12-15 05:39:51', NULL),
(461, 1158, 'Weekly Site Update', 'rejected', 'Images too dark', '2025-12-15 05:39:51', NULL),
(462, 1159, 'Weekly Site Update', 'submitted', 'Images too dark', '2025-12-15 05:39:51', NULL),
(463, 1283, 'Weekly Site Update', 'rejected', 'Images too dark', '2025-12-15 05:39:51', NULL),
(464, 1160, 'Weekly Site Update', 'rejected', 'Images too dark', '2025-12-15 05:39:51', NULL),
(465, 1284, 'Weekly Site Update', 'rejected', 'Images too dark', '2025-12-15 05:39:51', NULL),
(466, 1408, 'Weekly Site Update', 'approved', 'Images too dark', '2025-12-15 05:39:51', NULL),
(467, 1161, 'Weekly Site Update', 'approved', 'Images too dark', '2025-12-15 05:39:51', NULL),
(468, 1162, 'Weekly Site Update', 'rejected', 'Images too dark', '2025-12-15 05:39:51', NULL),
(469, 1285, 'Weekly Site Update', 'approved', 'Images too dark', '2025-12-15 05:39:51', NULL),
(470, 1163, 'Weekly Site Update', 'submitted', 'Images too dark', '2025-12-15 05:39:51', NULL),
(471, 1286, 'Weekly Site Update', 'rejected', 'Images too dark', '2025-12-15 05:39:51', NULL),
(472, 1409, 'Weekly Site Update', 'rejected', 'Images too dark', '2025-12-15 05:39:51', NULL),
(473, 1164, 'Weekly Site Update', 'rejected', 'Images too dark', '2025-12-15 05:39:51', NULL),
(474, 1165, 'Weekly Site Update', 'rejected', 'Images too dark', '2025-12-15 05:39:51', NULL),
(475, 1287, 'Weekly Site Update', 'submitted', 'Images too dark', '2025-12-15 05:39:51', NULL),
(476, 1166, 'Weekly Site Update', 'approved', 'Images too dark', '2025-12-15 05:39:51', NULL),
(477, 1288, 'Weekly Site Update', 'approved', 'Images too dark', '2025-12-15 05:39:51', NULL),
(478, 1410, 'Weekly Site Update', 'rejected', 'Images too dark', '2025-12-15 05:39:51', NULL),
(479, 1167, 'Weekly Site Update', 'rejected', 'Images too dark', '2025-12-15 05:39:51', NULL),
(480, 1168, 'Weekly Site Update', 'approved', 'Images too dark', '2025-12-15 05:39:51', NULL),
(481, 1289, 'Weekly Site Update', 'approved', 'Images too dark', '2025-12-15 05:39:51', NULL),
(482, 1169, 'Weekly Site Update', 'submitted', 'Images too dark', '2025-12-15 05:39:51', NULL),
(483, 1290, 'Weekly Site Update', 'approved', 'Images too dark', '2025-12-15 05:39:51', NULL),
(484, 1411, 'Weekly Site Update', 'submitted', 'Images too dark', '2025-12-15 05:39:51', NULL),
(485, 1170, 'Weekly Site Update', 'rejected', 'Images too dark', '2025-12-15 05:39:51', NULL),
(486, 1171, 'Weekly Site Update', 'approved', 'Images too dark', '2025-12-15 05:39:51', NULL),
(487, 1291, 'Weekly Site Update', 'approved', 'Images too dark', '2025-12-15 05:39:51', NULL),
(488, 1172, 'Weekly Site Update', 'approved', 'Images too dark', '2025-12-15 05:39:51', NULL),
(489, 1292, 'Weekly Site Update', 'rejected', 'Images too dark', '2025-12-15 05:39:51', NULL),
(490, 1412, 'Weekly Site Update', 'submitted', 'Images too dark', '2025-12-15 05:39:51', NULL),
(491, 1173, 'Weekly Site Update', 'submitted', 'Images too dark', '2025-12-15 05:39:51', NULL),
(492, 1174, 'Weekly Site Update', 'rejected', 'Images too dark', '2025-12-15 05:39:51', NULL),
(493, 1293, 'Weekly Site Update', 'approved', 'Images too dark', '2025-12-15 05:39:51', NULL),
(494, 1175, 'Weekly Site Update', 'approved', 'Images too dark', '2025-12-15 05:39:51', NULL),
(495, 1294, 'Weekly Site Update', 'rejected', 'Images too dark', '2025-12-15 05:39:51', NULL),
(496, 1413, 'Weekly Site Update', 'approved', 'Images too dark', '2025-12-15 05:39:51', NULL),
(497, 1176, 'Weekly Site Update', 'approved', 'Images too dark', '2025-12-15 05:39:51', NULL),
(498, 1177, 'Weekly Site Update', 'submitted', 'Images too dark', '2025-12-15 05:39:51', NULL);

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
  `project_status` enum('open','bidding_closed','in_progress','completed','terminated','deleted_post') DEFAULT 'open',
  `selected_contractor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`project_id`, `relationship_id`, `project_title`, `project_description`, `project_location`, `budget_range_min`, `budget_range_max`, `lot_size`, `floor_area`, `property_type`, `type_id`, `if_others_ctype`, `to_finish`, `project_status`, `selected_contractor_id`) VALUES
(670, 671, 'Project 671', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 8, NULL, NULL, 'open', NULL),
(671, 674, 'Project 674', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(672, 676, 'Project 676', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 5, NULL, NULL, 'open', NULL),
(673, 677, 'Project 677', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 9, NULL, NULL, 'open', NULL),
(674, 679, 'Project 679', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 4, NULL, NULL, 'open', NULL),
(675, 680, 'Project 680', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(676, 682, 'Project 682', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 4, NULL, NULL, 'open', NULL),
(677, 684, 'Project 684', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 7, NULL, NULL, 'open', NULL),
(678, 694, 'Project 694', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 8, NULL, NULL, 'open', NULL),
(679, 695, 'Project 695', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 9, NULL, NULL, 'open', NULL),
(680, 696, 'Project 696', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(681, 697, 'Project 697', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 7, NULL, NULL, 'open', NULL),
(682, 698, 'Project 698', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 2, NULL, NULL, 'open', NULL),
(683, 699, 'Project 699', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 5, NULL, NULL, 'open', NULL),
(684, 704, 'Project 704', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL),
(685, 705, 'Project 705', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 9, NULL, NULL, 'open', NULL),
(686, 708, 'Project 708', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 5, NULL, NULL, 'open', NULL),
(687, 713, 'Project 713', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 6, NULL, NULL, 'open', NULL),
(688, 715, 'Project 715', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 4, NULL, NULL, 'open', NULL),
(689, 718, 'Project 718', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 9, NULL, NULL, 'open', NULL),
(690, 719, 'Project 719', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 4, NULL, NULL, 'open', NULL),
(691, 720, 'Project 720', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 4, NULL, NULL, 'open', NULL),
(692, 721, 'Project 721', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 3, NULL, NULL, 'open', NULL),
(693, 722, 'Project 722', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 8, NULL, NULL, 'open', NULL),
(694, 723, 'Project 723', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 7, NULL, NULL, 'open', NULL),
(695, 726, 'Project 726', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 8, NULL, NULL, 'open', NULL),
(696, 727, 'Project 727', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 3, NULL, NULL, 'open', NULL),
(697, 724, 'Project 724', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1055),
(698, 678, 'Project 678', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 4, NULL, NULL, 'in_progress', 1057),
(699, 692, 'Project 692', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 4, NULL, NULL, 'in_progress', 1065),
(700, 706, 'Project 706', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 6, NULL, NULL, 'in_progress', 1068),
(701, 725, 'Project 725', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 3, NULL, NULL, 'in_progress', 1079),
(702, 693, 'Project 693', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 4, NULL, NULL, 'in_progress', 1083),
(703, 710, 'Project 710', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 7, NULL, NULL, 'in_progress', 1083),
(704, 691, 'Project 691', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 5, NULL, NULL, 'in_progress', 1084),
(705, 690, 'Project 690', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 9, NULL, NULL, 'in_progress', 1089),
(706, 711, 'Project 711', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 7, NULL, NULL, 'in_progress', 1089),
(707, 728, 'Project 728', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 4, NULL, NULL, 'in_progress', 1089),
(708, 707, 'Project 707', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 2, NULL, NULL, 'in_progress', 1090),
(709, 712, 'Project 712', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 2, NULL, NULL, 'in_progress', 1094),
(710, 729, 'Project 729', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 3, NULL, NULL, 'in_progress', 1098),
(711, 703, 'Project 703', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 2, NULL, NULL, 'in_progress', 1100),
(712, 709, 'Project 709', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1105),
(713, 686, 'Project 686', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1107),
(714, 673, 'Project 673', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1109),
(715, 685, 'Project 685', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 4, NULL, NULL, 'in_progress', 1123),
(716, 717, 'Project 717', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 8, NULL, NULL, 'in_progress', 1127),
(717, 672, 'Project 672', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 3, NULL, NULL, 'in_progress', 1132),
(718, 687, 'Project 687', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 6, NULL, NULL, 'in_progress', 1133),
(719, 716, 'Project 716', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 6, NULL, NULL, 'in_progress', 1133),
(720, 675, 'Project 675', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 5, NULL, NULL, 'in_progress', 1135),
(721, 714, 'Project 714', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 8, NULL, NULL, 'in_progress', 1145),
(722, 689, 'Project 689', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 9, NULL, NULL, 'in_progress', 1147),
(723, 701, 'Project 701', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 5, NULL, NULL, 'in_progress', 1149),
(724, 670, 'Project 670', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 7, NULL, NULL, 'in_progress', 1150),
(725, 700, 'Project 700', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', 1159),
(726, 688, 'Project 688', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 3, NULL, NULL, 'in_progress', 1162),
(727, 702, 'Project 702', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 9, NULL, NULL, 'in_progress', 1163),
(728, 683, 'Project 683', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 6, NULL, NULL, 'in_progress', 1164),
(729, 681, 'Project 681', 'Standard residential project description.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 6, NULL, NULL, 'in_progress', 1167);

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
(670, 1114, 1150, 'rejected', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(671, 1165, NULL, 'under_review', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(672, 1139, 1132, 'approved', 'Violation of posting rules.', NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(673, 1110, 1109, 'rejected', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(674, 1162, NULL, 'approved', 'Violation of posting rules.', NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(675, 1088, 1135, 'approved', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(676, 1135, NULL, 'approved', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(677, 1126, NULL, 'under_review', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(678, 1109, 1057, 'rejected', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(679, 1069, NULL, 'rejected', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(680, 1080, NULL, 'approved', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(681, 1079, 1167, 'approved', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(682, 1088, NULL, 'under_review', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(683, 1100, 1164, 'under_review', 'Violation of posting rules.', NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(684, 1169, NULL, 'rejected', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(685, 1078, 1123, 'under_review', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(686, 1150, 1107, 'approved', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(687, 1160, 1133, 'rejected', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(688, 1147, 1162, 'under_review', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(689, 1088, 1147, 'approved', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(690, 1146, 1089, 'approved', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(691, 1088, 1084, 'under_review', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(692, 1147, 1065, 'approved', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(693, 1064, 1083, 'under_review', 'Violation of posting rules.', NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(694, 1058, NULL, 'approved', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(695, 1117, NULL, 'rejected', 'Violation of posting rules.', NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(696, 1152, NULL, 'under_review', 'Violation of posting rules.', NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(697, 1085, NULL, 'under_review', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(698, 1055, NULL, 'approved', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(699, 1166, NULL, 'under_review', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(700, 1103, 1159, 'approved', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(701, 1089, 1149, 'rejected', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(702, 1169, 1163, 'under_review', 'Violation of posting rules.', NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(703, 1124, 1100, 'rejected', 'Violation of posting rules.', NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(704, 1083, NULL, 'under_review', 'Violation of posting rules.', NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(705, 1127, NULL, 'rejected', 'Violation of posting rules.', NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(706, 1146, 1068, 'rejected', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(707, 1106, 1090, 'rejected', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(708, 1138, NULL, 'under_review', 'Violation of posting rules.', NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(709, 1144, 1105, 'under_review', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(710, 1125, 1083, 'approved', 'Violation of posting rules.', NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(711, 1073, 1089, 'rejected', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(712, 1056, 1094, 'rejected', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(713, 1107, NULL, 'under_review', 'Violation of posting rules.', NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(714, 1083, 1145, 'approved', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(715, 1053, NULL, 'approved', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(716, 1108, 1133, 'rejected', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(717, 1055, 1127, 'approved', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(718, 1063, NULL, 'under_review', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(719, 1136, NULL, 'under_review', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(720, 1160, NULL, 'under_review', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(721, 1128, NULL, 'approved', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(722, 1120, NULL, 'approved', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(723, 1116, NULL, 'under_review', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(724, 1093, 1055, 'rejected', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(725, 1153, 1079, 'rejected', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(726, 1052, NULL, 'rejected', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(727, 1053, NULL, 'under_review', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(728, 1082, 1089, 'under_review', 'Violation of posting rules.', NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(729, 1108, 1098, 'under_review', NULL, NULL, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51');

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
  `verification_status` enum('pending','rejected','approved','') NOT NULL DEFAULT 'pending',
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `rejection_reason` text DEFAULT NULL,
  `verification_date` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_owners`
--

INSERT INTO `property_owners` (`owner_id`, `user_id`, `last_name`, `middle_name`, `first_name`, `phone_number`, `address`, `valid_id_id`, `valid_id_photo`, `valid_id_back_photo`, `police_clearance`, `date_of_birth`, `age`, `occupation_id`, `occupation_other`, `verification_status`, `is_active`, `rejection_reason`, `verification_date`, `created_at`) VALUES
(1052, 101, 'OwnerLast101', NULL, 'OwnerFirst101', '09990000101', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 9, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1053, 102, 'OwnerLast102', NULL, 'OwnerFirst102', '09990000102', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 5, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1054, 103, 'OwnerLast103', NULL, 'OwnerFirst103', '09990000103', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 26, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1055, 104, 'OwnerLast104', NULL, 'OwnerFirst104', '09990000104', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 9, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1056, 105, 'OwnerLast105', NULL, 'OwnerFirst105', '09990000105', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 9, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1057, 106, 'OwnerLast106', NULL, 'OwnerFirst106', '09990000106', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 14, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1058, 107, 'OwnerLast107', NULL, 'OwnerFirst107', '09990000107', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 18, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1059, 108, 'OwnerLast108', NULL, 'OwnerFirst108', '09990000108', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 15, NULL, 'pending', 1, NULL, '0000-00-00 00:00:00', '2025-12-15 05:39:51'),
(1060, 109, 'OwnerLast109', NULL, 'OwnerFirst109', '09990000109', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 3, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1061, 110, 'OwnerLast110', NULL, 'OwnerFirst110', '09990000110', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 20, NULL, 'rejected', 1, 'Invalid ID.', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1062, 111, 'OwnerLast111', NULL, 'OwnerFirst111', '09990000111', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 3, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1063, 112, 'OwnerLast112', NULL, 'OwnerFirst112', '09990000112', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 25, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1064, 113, 'OwnerLast113', NULL, 'OwnerFirst113', '09990000113', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 26, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1065, 114, 'OwnerLast114', NULL, 'OwnerFirst114', '09990000114', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 24, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1066, 115, 'OwnerLast115', NULL, 'OwnerFirst115', '09990000115', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 23, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1067, 116, 'OwnerLast116', NULL, 'OwnerFirst116', '09990000116', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 2, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1068, 117, 'OwnerLast117', NULL, 'OwnerFirst117', '09990000117', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 23, NULL, 'pending', 1, NULL, '0000-00-00 00:00:00', '2025-12-15 05:39:51'),
(1069, 118, 'OwnerLast118', NULL, 'OwnerFirst118', '09990000118', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 11, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1070, 119, 'OwnerLast119', NULL, 'OwnerFirst119', '09990000119', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 12, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1071, 120, 'OwnerLast120', NULL, 'OwnerFirst120', '09990000120', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 18, NULL, 'rejected', 0, 'Invalid ID.', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1072, 121, 'OwnerLast121', NULL, 'OwnerFirst121', '09990000121', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 10, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1073, 122, 'OwnerLast122', NULL, 'OwnerFirst122', '09990000122', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 14, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1074, 123, 'OwnerLast123', NULL, 'OwnerFirst123', '09990000123', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 20, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1075, 124, 'OwnerLast124', NULL, 'OwnerFirst124', '09990000124', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 2, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1076, 125, 'OwnerLast125', NULL, 'OwnerFirst125', '09990000125', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 5, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1077, 126, 'OwnerLast126', NULL, 'OwnerFirst126', '09990000126', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 25, NULL, 'pending', 1, NULL, '0000-00-00 00:00:00', '2025-12-15 05:39:51'),
(1078, 127, 'OwnerLast127', NULL, 'OwnerFirst127', '09990000127', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 21, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1079, 128, 'OwnerLast128', NULL, 'OwnerFirst128', '09990000128', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 7, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1080, 129, 'OwnerLast129', NULL, 'OwnerFirst129', '09990000129', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 25, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1081, 130, 'OwnerLast130', NULL, 'OwnerFirst130', '09990000130', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 24, NULL, 'rejected', 1, 'Invalid ID.', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1082, 131, 'OwnerLast131', NULL, 'OwnerFirst131', '09990000131', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 12, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1083, 132, 'OwnerLast132', NULL, 'OwnerFirst132', '09990000132', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 4, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1084, 133, 'OwnerLast133', NULL, 'OwnerFirst133', '09990000133', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 21, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1085, 134, 'OwnerLast134', NULL, 'OwnerFirst134', '09990000134', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 9, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1086, 135, 'OwnerLast135', NULL, 'OwnerFirst135', '09990000135', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 10, NULL, 'pending', 1, NULL, '0000-00-00 00:00:00', '2025-12-15 05:39:51'),
(1087, 136, 'OwnerLast136', NULL, 'OwnerFirst136', '09990000136', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 11, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1088, 137, 'OwnerLast137', NULL, 'OwnerFirst137', '09990000137', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 16, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1089, 138, 'OwnerLast138', NULL, 'OwnerFirst138', '09990000138', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 11, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1090, 139, 'OwnerLast139', NULL, 'OwnerFirst139', '09990000139', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 23, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1091, 140, 'OwnerLast140', NULL, 'OwnerFirst140', '09990000140', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 4, NULL, 'rejected', 0, 'Invalid ID.', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1092, 141, 'OwnerLast141', NULL, 'OwnerFirst141', '09990000141', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 23, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1093, 142, 'OwnerLast142', NULL, 'OwnerFirst142', '09990000142', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 10, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1094, 143, 'OwnerLast143', NULL, 'OwnerFirst143', '09990000143', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 22, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1095, 144, 'OwnerLast144', NULL, 'OwnerFirst144', '09990000144', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 2, NULL, 'pending', 1, NULL, '0000-00-00 00:00:00', '2025-12-15 05:39:51'),
(1096, 145, 'OwnerLast145', NULL, 'OwnerFirst145', '09990000145', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 25, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1097, 146, 'OwnerLast146', NULL, 'OwnerFirst146', '09990000146', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 20, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1098, 147, 'OwnerLast147', NULL, 'OwnerFirst147', '09990000147', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 18, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1099, 148, 'OwnerLast148', NULL, 'OwnerFirst148', '09990000148', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 26, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1100, 149, 'OwnerLast149', NULL, 'OwnerFirst149', '09990000149', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 5, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1101, 150, 'OwnerLast150', NULL, 'OwnerFirst150', '09990000150', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 12, NULL, 'rejected', 1, 'Invalid ID.', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1102, 151, 'OwnerLast151', NULL, 'OwnerFirst151', '09990000151', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 3, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1103, 152, 'OwnerLast152', NULL, 'OwnerFirst152', '09990000152', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 10, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1104, 153, 'OwnerLast153', NULL, 'OwnerFirst153', '09990000153', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 13, NULL, 'pending', 1, NULL, '0000-00-00 00:00:00', '2025-12-15 05:39:51'),
(1105, 154, 'OwnerLast154', NULL, 'OwnerFirst154', '09990000154', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 5, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1106, 155, 'OwnerLast155', NULL, 'OwnerFirst155', '09990000155', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 22, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1107, 156, 'OwnerLast156', NULL, 'OwnerFirst156', '09990000156', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 22, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1108, 157, 'OwnerLast157', NULL, 'OwnerFirst157', '09990000157', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 15, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1109, 158, 'OwnerLast158', NULL, 'OwnerFirst158', '09990000158', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 21, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1110, 159, 'OwnerLast159', NULL, 'OwnerFirst159', '09990000159', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 11, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1111, 160, 'OwnerLast160', NULL, 'OwnerFirst160', '09990000160', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 23, NULL, 'rejected', 0, 'Invalid ID.', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1112, 161, 'OwnerLast161', NULL, 'OwnerFirst161', '09990000161', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 6, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1113, 162, 'OwnerLast162', NULL, 'OwnerFirst162', '09990000162', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 21, NULL, 'pending', 1, NULL, '0000-00-00 00:00:00', '2025-12-15 05:39:51'),
(1114, 163, 'OwnerLast163', NULL, 'OwnerFirst163', '09990000163', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 20, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1115, 164, 'OwnerLast164', NULL, 'OwnerFirst164', '09990000164', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 16, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1116, 165, 'OwnerLast165', NULL, 'OwnerFirst165', '09990000165', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 26, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1117, 166, 'OwnerLast166', NULL, 'OwnerFirst166', '09990000166', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 18, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1118, 167, 'OwnerLast167', NULL, 'OwnerFirst167', '09990000167', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 4, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1119, 168, 'OwnerLast168', NULL, 'OwnerFirst168', '09990000168', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 18, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1120, 169, 'OwnerLast169', NULL, 'OwnerFirst169', '09990000169', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 1, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1121, 170, 'OwnerLast170', NULL, 'OwnerFirst170', '09990000170', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 18, NULL, 'rejected', 1, 'Invalid ID.', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1122, 171, 'OwnerLast171', NULL, 'OwnerFirst171', '09990000171', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 14, NULL, 'pending', 1, NULL, '0000-00-00 00:00:00', '2025-12-15 05:39:51'),
(1123, 172, 'OwnerLast172', NULL, 'OwnerFirst172', '09990000172', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 19, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1124, 173, 'OwnerLast173', NULL, 'OwnerFirst173', '09990000173', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 15, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1125, 174, 'OwnerLast174', NULL, 'OwnerFirst174', '09990000174', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 20, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1126, 175, 'OwnerLast175', NULL, 'OwnerFirst175', '09990000175', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 20, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1127, 176, 'OwnerLast176', NULL, 'OwnerFirst176', '09990000176', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 2, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1128, 177, 'OwnerLast177', NULL, 'OwnerFirst177', '09990000177', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 12, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1129, 178, 'OwnerLast178', NULL, 'OwnerFirst178', '09990000178', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 6, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1130, 179, 'OwnerLast179', NULL, 'OwnerFirst179', '09990000179', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 1, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1131, 180, 'OwnerLast180', NULL, 'OwnerFirst180', '09990000180', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 22, NULL, 'rejected', 0, 'Invalid ID.', '0000-00-00 00:00:00', '2025-12-15 05:39:51'),
(1132, 181, 'OwnerLast181', NULL, 'OwnerFirst181', '09990000181', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 25, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1133, 182, 'OwnerLast182', NULL, 'OwnerFirst182', '09990000182', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 17, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1134, 183, 'OwnerLast183', NULL, 'OwnerFirst183', '09990000183', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 7, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1135, 184, 'OwnerLast184', NULL, 'OwnerFirst184', '09990000184', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 24, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1136, 185, 'OwnerLast185', NULL, 'OwnerFirst185', '09990000185', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 9, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1137, 186, 'OwnerLast186', NULL, 'OwnerFirst186', '09990000186', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 8, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1138, 187, 'OwnerLast187', NULL, 'OwnerFirst187', '09990000187', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 10, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1139, 188, 'OwnerLast188', NULL, 'OwnerFirst188', '09990000188', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 19, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1140, 189, 'OwnerLast189', NULL, 'OwnerFirst189', '09990000189', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 23, NULL, 'pending', 1, NULL, '0000-00-00 00:00:00', '2025-12-15 05:39:51'),
(1141, 190, 'OwnerLast190', NULL, 'OwnerFirst190', '09990000190', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 3, NULL, 'rejected', 1, 'Invalid ID.', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1142, 191, 'OwnerLast191', NULL, 'OwnerFirst191', '09990000191', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 19, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1143, 192, 'OwnerLast192', NULL, 'OwnerFirst192', '09990000192', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 25, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1144, 193, 'OwnerLast193', NULL, 'OwnerFirst193', '09990000193', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 22, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1145, 194, 'OwnerLast194', NULL, 'OwnerFirst194', '09990000194', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 19, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1146, 195, 'OwnerLast195', NULL, 'OwnerFirst195', '09990000195', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 10, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1147, 196, 'OwnerLast196', NULL, 'OwnerFirst196', '09990000196', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 21, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1148, 197, 'OwnerLast197', NULL, 'OwnerFirst197', '09990000197', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 13, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1149, 198, 'OwnerLast198', NULL, 'OwnerFirst198', '09990000198', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 11, NULL, 'pending', 1, NULL, '0000-00-00 00:00:00', '2025-12-15 05:39:51'),
(1150, 199, 'OwnerLast199', NULL, 'OwnerFirst199', '09990000199', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 3, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1151, 200, 'OwnerLast200', NULL, 'OwnerFirst200', '09990000200', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 21, NULL, 'rejected', 0, 'Invalid ID.', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1152, 201, 'OwnerLast201', NULL, 'OwnerFirst201', '09990000201', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 6, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1153, 202, 'OwnerLast202', NULL, 'OwnerFirst202', '09990000202', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 18, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1154, 203, 'OwnerLast203', NULL, 'OwnerFirst203', '09990000203', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 25, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1155, 204, 'OwnerLast204', NULL, 'OwnerFirst204', '09990000204', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 14, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1156, 205, 'OwnerLast205', NULL, 'OwnerFirst205', '09990000205', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 25, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1157, 206, 'OwnerLast206', NULL, 'OwnerFirst206', '09990000206', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 6, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1158, 207, 'OwnerLast207', NULL, 'OwnerFirst207', '09990000207', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 18, NULL, 'pending', 1, NULL, '0000-00-00 00:00:00', '2025-12-15 05:39:51'),
(1159, 208, 'OwnerLast208', NULL, 'OwnerFirst208', '09990000208', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 18, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1160, 209, 'OwnerLast209', NULL, 'OwnerFirst209', '09990000209', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 20, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1161, 210, 'OwnerLast210', NULL, 'OwnerFirst210', '09990000210', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 10, NULL, 'rejected', 1, 'Invalid ID.', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1162, 211, 'OwnerLast211', NULL, 'OwnerFirst211', '09990000211', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 17, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1163, 212, 'OwnerLast212', NULL, 'OwnerFirst212', '09990000212', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 1, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1164, 213, 'OwnerLast213', NULL, 'OwnerFirst213', '09990000213', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 22, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1165, 214, 'OwnerLast214', NULL, 'OwnerFirst214', '09990000214', 'Tetuan, Zamboanga City', 6, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 15, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1166, 215, 'OwnerLast215', NULL, 'OwnerFirst215', '09990000215', 'Tetuan, Zamboanga City', 2, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 5, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1167, 216, 'OwnerLast216', NULL, 'OwnerFirst216', '09990000216', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 4, NULL, 'pending', 1, NULL, '0000-00-00 00:00:00', '2025-12-15 05:39:51'),
(1168, 217, 'OwnerLast217', NULL, 'OwnerFirst217', '09990000217', 'Tetuan, Zamboanga City', 3, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 23, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1169, 218, 'OwnerLast218', NULL, 'OwnerFirst218', '09990000218', 'Tetuan, Zamboanga City', 4, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 26, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1170, 219, 'OwnerLast219', NULL, 'OwnerFirst219', '09990000219', 'Tetuan, Zamboanga City', 1, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 4, NULL, 'approved', 1, NULL, '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(1171, 220, 'OwnerLast220', NULL, 'OwnerFirst220', '09990000220', 'Tetuan, Zamboanga City', 5, 'id_front.jpg', 'id_back.jpg', 'police_clearance.jpg', '1990-01-01', 35, 22, NULL, 'rejected', 0, 'Invalid ID.', '2025-12-15 05:39:51', '2025-12-15 05:39:51');

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
  `user_type` enum('contractor','property_owner','both') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `profile_pic`, `cover_photo`, `username`, `email`, `password_hash`, `OTP_hash`, `user_type`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, 'user1', 'user1@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(2, NULL, NULL, 'user2', 'user2@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(3, NULL, NULL, 'user3', 'user3@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(4, NULL, NULL, 'user4', 'user4@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(5, NULL, NULL, 'user5', 'user5@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(6, NULL, NULL, 'user6', 'user6@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(7, NULL, NULL, 'user7', 'user7@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(8, NULL, NULL, 'user8', 'user8@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(9, NULL, NULL, 'user9', 'user9@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(10, NULL, NULL, 'user10', 'user10@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(11, NULL, NULL, 'user11', 'user11@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(12, NULL, NULL, 'user12', 'user12@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(13, NULL, NULL, 'user13', 'user13@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(14, NULL, NULL, 'user14', 'user14@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(15, NULL, NULL, 'user15', 'user15@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(16, NULL, NULL, 'user16', 'user16@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(17, NULL, NULL, 'user17', 'user17@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(18, NULL, NULL, 'user18', 'user18@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(19, NULL, NULL, 'user19', 'user19@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(20, NULL, NULL, 'user20', 'user20@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(21, NULL, NULL, 'user21', 'user21@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(22, NULL, NULL, 'user22', 'user22@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(23, NULL, NULL, 'user23', 'user23@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(24, NULL, NULL, 'user24', 'user24@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(25, NULL, NULL, 'user25', 'user25@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(26, NULL, NULL, 'user26', 'user26@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(27, NULL, NULL, 'user27', 'user27@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(28, NULL, NULL, 'user28', 'user28@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(29, NULL, NULL, 'user29', 'user29@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(30, NULL, NULL, 'user30', 'user30@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(31, NULL, NULL, 'user31', 'user31@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(32, NULL, NULL, 'user32', 'user32@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(33, NULL, NULL, 'user33', 'user33@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(34, NULL, NULL, 'user34', 'user34@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(35, NULL, NULL, 'user35', 'user35@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(36, NULL, NULL, 'user36', 'user36@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(37, NULL, NULL, 'user37', 'user37@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(38, NULL, NULL, 'user38', 'user38@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(39, NULL, NULL, 'user39', 'user39@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(40, NULL, NULL, 'user40', 'user40@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(41, NULL, NULL, 'user41', 'user41@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(42, NULL, NULL, 'user42', 'user42@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(43, NULL, NULL, 'user43', 'user43@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(44, NULL, NULL, 'user44', 'user44@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(45, NULL, NULL, 'user45', 'user45@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(46, NULL, NULL, 'user46', 'user46@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(47, NULL, NULL, 'user47', 'user47@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(48, NULL, NULL, 'user48', 'user48@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(49, NULL, NULL, 'user49', 'user49@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(50, NULL, NULL, 'user50', 'user50@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(51, NULL, NULL, 'user51', 'user51@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(52, NULL, NULL, 'user52', 'user52@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(53, NULL, NULL, 'user53', 'user53@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(54, NULL, NULL, 'user54', 'user54@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(55, NULL, NULL, 'user55', 'user55@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(56, NULL, NULL, 'user56', 'user56@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(57, NULL, NULL, 'user57', 'user57@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(58, NULL, NULL, 'user58', 'user58@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(59, NULL, NULL, 'user59', 'user59@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(60, NULL, NULL, 'user60', 'user60@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(61, NULL, NULL, 'user61', 'user61@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(62, NULL, NULL, 'user62', 'user62@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(63, NULL, NULL, 'user63', 'user63@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(64, NULL, NULL, 'user64', 'user64@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(65, NULL, NULL, 'user65', 'user65@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(66, NULL, NULL, 'user66', 'user66@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(67, NULL, NULL, 'user67', 'user67@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(68, NULL, NULL, 'user68', 'user68@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(69, NULL, NULL, 'user69', 'user69@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(70, NULL, NULL, 'user70', 'user70@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(71, NULL, NULL, 'user71', 'user71@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(72, NULL, NULL, 'user72', 'user72@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(73, NULL, NULL, 'user73', 'user73@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(74, NULL, NULL, 'user74', 'user74@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(75, NULL, NULL, 'user75', 'user75@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(76, NULL, NULL, 'user76', 'user76@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(77, NULL, NULL, 'user77', 'user77@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(78, NULL, NULL, 'user78', 'user78@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(79, NULL, NULL, 'user79', 'user79@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(80, NULL, NULL, 'user80', 'user80@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(81, NULL, NULL, 'user81', 'user81@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(82, NULL, NULL, 'user82', 'user82@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(83, NULL, NULL, 'user83', 'user83@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(84, NULL, NULL, 'user84', 'user84@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(85, NULL, NULL, 'user85', 'user85@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(86, NULL, NULL, 'user86', 'user86@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(87, NULL, NULL, 'user87', 'user87@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(88, NULL, NULL, 'user88', 'user88@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(89, NULL, NULL, 'user89', 'user89@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(90, NULL, NULL, 'user90', 'user90@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(91, NULL, NULL, 'user91', 'user91@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(92, NULL, NULL, 'user92', 'user92@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(93, NULL, NULL, 'user93', 'user93@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(94, NULL, NULL, 'user94', 'user94@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(95, NULL, NULL, 'user95', 'user95@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(96, NULL, NULL, 'user96', 'user96@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(97, NULL, NULL, 'user97', 'user97@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(98, NULL, NULL, 'user98', 'user98@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(99, NULL, NULL, 'user99', 'user99@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(100, NULL, NULL, 'user100', 'user100@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'contractor', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(101, NULL, NULL, 'user101', 'user101@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(102, NULL, NULL, 'user102', 'user102@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(103, NULL, NULL, 'user103', 'user103@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(104, NULL, NULL, 'user104', 'user104@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(105, NULL, NULL, 'user105', 'user105@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(106, NULL, NULL, 'user106', 'user106@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(107, NULL, NULL, 'user107', 'user107@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(108, NULL, NULL, 'user108', 'user108@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(109, NULL, NULL, 'user109', 'user109@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(110, NULL, NULL, 'user110', 'user110@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(111, NULL, NULL, 'user111', 'user111@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(112, NULL, NULL, 'user112', 'user112@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(113, NULL, NULL, 'user113', 'user113@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(114, NULL, NULL, 'user114', 'user114@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(115, NULL, NULL, 'user115', 'user115@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(116, NULL, NULL, 'user116', 'user116@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(117, NULL, NULL, 'user117', 'user117@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(118, NULL, NULL, 'user118', 'user118@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(119, NULL, NULL, 'user119', 'user119@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(120, NULL, NULL, 'user120', 'user120@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(121, NULL, NULL, 'user121', 'user121@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(122, NULL, NULL, 'user122', 'user122@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(123, NULL, NULL, 'user123', 'user123@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(124, NULL, NULL, 'user124', 'user124@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(125, NULL, NULL, 'user125', 'user125@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(126, NULL, NULL, 'user126', 'user126@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(127, NULL, NULL, 'user127', 'user127@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(128, NULL, NULL, 'user128', 'user128@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(129, NULL, NULL, 'user129', 'user129@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(130, NULL, NULL, 'user130', 'user130@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(131, NULL, NULL, 'user131', 'user131@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(132, NULL, NULL, 'user132', 'user132@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(133, NULL, NULL, 'user133', 'user133@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(134, NULL, NULL, 'user134', 'user134@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(135, NULL, NULL, 'user135', 'user135@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(136, NULL, NULL, 'user136', 'user136@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(137, NULL, NULL, 'user137', 'user137@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(138, NULL, NULL, 'user138', 'user138@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(139, NULL, NULL, 'user139', 'user139@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(140, NULL, NULL, 'user140', 'user140@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(141, NULL, NULL, 'user141', 'user141@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(142, NULL, NULL, 'user142', 'user142@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(143, NULL, NULL, 'user143', 'user143@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(144, NULL, NULL, 'user144', 'user144@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(145, NULL, NULL, 'user145', 'user145@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(146, NULL, NULL, 'user146', 'user146@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(147, NULL, NULL, 'user147', 'user147@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(148, NULL, NULL, 'user148', 'user148@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(149, NULL, NULL, 'user149', 'user149@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(150, NULL, NULL, 'user150', 'user150@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(151, NULL, NULL, 'user151', 'user151@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(152, NULL, NULL, 'user152', 'user152@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(153, NULL, NULL, 'user153', 'user153@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(154, NULL, NULL, 'user154', 'user154@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(155, NULL, NULL, 'user155', 'user155@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(156, NULL, NULL, 'user156', 'user156@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(157, NULL, NULL, 'user157', 'user157@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(158, NULL, NULL, 'user158', 'user158@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(159, NULL, NULL, 'user159', 'user159@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(160, NULL, NULL, 'user160', 'user160@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(161, NULL, NULL, 'user161', 'user161@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(162, NULL, NULL, 'user162', 'user162@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(163, NULL, NULL, 'user163', 'user163@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(164, NULL, NULL, 'user164', 'user164@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(165, NULL, NULL, 'user165', 'user165@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(166, NULL, NULL, 'user166', 'user166@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(167, NULL, NULL, 'user167', 'user167@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(168, NULL, NULL, 'user168', 'user168@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(169, NULL, NULL, 'user169', 'user169@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(170, NULL, NULL, 'user170', 'user170@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(171, NULL, NULL, 'user171', 'user171@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(172, NULL, NULL, 'user172', 'user172@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(173, NULL, NULL, 'user173', 'user173@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(174, NULL, NULL, 'user174', 'user174@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(175, NULL, NULL, 'user175', 'user175@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(176, NULL, NULL, 'user176', 'user176@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(177, NULL, NULL, 'user177', 'user177@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(178, NULL, NULL, 'user178', 'user178@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(179, NULL, NULL, 'user179', 'user179@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(180, NULL, NULL, 'user180', 'user180@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(181, NULL, NULL, 'user181', 'user181@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(182, NULL, NULL, 'user182', 'user182@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(183, NULL, NULL, 'user183', 'user183@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(184, NULL, NULL, 'user184', 'user184@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(185, NULL, NULL, 'user185', 'user185@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(186, NULL, NULL, 'user186', 'user186@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(187, NULL, NULL, 'user187', 'user187@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(188, NULL, NULL, 'user188', 'user188@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(189, NULL, NULL, 'user189', 'user189@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(190, NULL, NULL, 'user190', 'user190@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(191, NULL, NULL, 'user191', 'user191@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(192, NULL, NULL, 'user192', 'user192@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(193, NULL, NULL, 'user193', 'user193@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(194, NULL, NULL, 'user194', 'user194@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(195, NULL, NULL, 'user195', 'user195@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(196, NULL, NULL, 'user196', 'user196@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(197, NULL, NULL, 'user197', 'user197@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(198, NULL, NULL, 'user198', 'user198@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(199, NULL, NULL, 'user199', 'user199@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(200, NULL, NULL, 'user200', 'user200@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'property_owner', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(201, NULL, NULL, 'user201', 'user201@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'both', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(202, NULL, NULL, 'user202', 'user202@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'both', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(203, NULL, NULL, 'user203', 'user203@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'both', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(204, NULL, NULL, 'user204', 'user204@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'both', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(205, NULL, NULL, 'user205', 'user205@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'both', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(206, NULL, NULL, 'user206', 'user206@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'both', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(207, NULL, NULL, 'user207', 'user207@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'both', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(208, NULL, NULL, 'user208', 'user208@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'both', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(209, NULL, NULL, 'user209', 'user209@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'both', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(210, NULL, NULL, 'user210', 'user210@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'both', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(211, NULL, NULL, 'user211', 'user211@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'both', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(212, NULL, NULL, 'user212', 'user212@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'both', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(213, NULL, NULL, 'user213', 'user213@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'both', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(214, NULL, NULL, 'user214', 'user214@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'both', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(215, NULL, NULL, 'user215', 'user215@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'both', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(216, NULL, NULL, 'user216', 'user216@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'both', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(217, NULL, NULL, 'user217', 'user217@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'both', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(218, NULL, NULL, 'user218', 'user218@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'both', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(219, NULL, NULL, 'user219', 'user219@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'both', '2025-12-15 05:39:51', '2025-12-15 05:39:51'),
(220, NULL, NULL, 'user220', 'user220@example.com', '$2y$12$YourPlaceholderHashHereToPreventErrors', 'otp_hash', 'both', '2025-12-15 05:39:51', '2025-12-15 05:39:51');

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
  MODIFY `contractor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1179;

--
-- AUTO_INCREMENT for table `contractor_types`
--
ALTER TABLE `contractor_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `contractor_users`
--
ALTER TABLE `contractor_users`
  MODIFY `contractor_user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1179;

--
-- AUTO_INCREMENT for table `disputes`
--
ALTER TABLE `disputes`
  MODIFY `dispute_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

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
  MODIFY `milestone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=958;

--
-- AUTO_INCREMENT for table `milestone_items`
--
ALTER TABLE `milestone_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1469;

--
-- AUTO_INCREMENT for table `milestone_payments`
--
ALTER TABLE `milestone_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=512;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1630;

--
-- AUTO_INCREMENT for table `occupations`
--
ALTER TABLE `occupations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `payment_plans`
--
ALTER TABLE `payment_plans`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=638;

--
-- AUTO_INCREMENT for table `platform_payments`
--
ALTER TABLE `platform_payments`
  MODIFY `platform_payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `progress`
--
ALTER TABLE `progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=512;

--
-- AUTO_INCREMENT for table `progress_files`
--
ALTER TABLE `progress_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=733;

--
-- AUTO_INCREMENT for table `project_files`
--
ALTER TABLE `project_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_relationships`
--
ALTER TABLE `project_relationships`
  MODIFY `rel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=733;

--
-- AUTO_INCREMENT for table `property_owners`
--
ALTER TABLE `property_owners`
  MODIFY `owner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1179;

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
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=221;

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
