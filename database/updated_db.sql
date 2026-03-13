-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 13, 2026 at 06:33 AM
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
-- Table structure for table `admin_activity_logs`
--

CREATE TABLE `admin_activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` varchar(20) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_activity_logs`
--

INSERT INTO `admin_activity_logs` (`id`, `admin_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 'ADMIN-1', 'admin_login', NULL, '127.0.0.1', '2026-03-12 09:33:08'),
(2, 'ADMIN-1', 'admin_login', NULL, '127.0.0.1', '2026-03-12 09:35:07'),
(3, 'ADMIN-1', 'admin_login', NULL, '127.0.0.1', '2026-03-12 09:42:23'),
(4, 'ADMIN-1', 'admin_login', NULL, '127.0.0.1', '2026-03-12 09:54:05'),
(5, 'ADMIN-1', 'admin_login', NULL, '127.0.0.1', '2026-03-12 09:58:34'),
(6, 'ADMIN-1', 'timeline_extended', '{\"project_id\":\"2001\",\"new_end_date\":\"2026-06-18\",\"reason\":\"I just need more time to fix everything.\"}', '127.0.0.1', '2026-03-12 10:22:31'),
(7, 'ADMIN-1', 'admin_login', NULL, '127.0.0.1', '2026-03-12 19:19:34');

-- --------------------------------------------------------

--
-- Table structure for table `admin_notification_preferences`
--

CREATE TABLE `admin_notification_preferences` (
  `id` int(11) NOT NULL,
  `admin_id` varchar(20) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_sent_notifications`
--

CREATE TABLE `admin_sent_notifications` (
  `id` int(11) NOT NULL,
  `admin_id` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `delivery_method` enum('in-app','email','both') NOT NULL DEFAULT 'both',
  `target_type` enum('all','targeted') NOT NULL DEFAULT 'all',
  `target_user_ids` text DEFAULT NULL COMMENT 'Comma-separated user_ids for targeted sends',
  `recipient_count` int(11) NOT NULL DEFAULT 0,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `admin_id` varchar(20) NOT NULL DEFAULT '',
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`admin_id`, `username`, `email`, `password_hash`, `last_name`, `middle_name`, `first_name`, `is_active`, `created_at`, `profile_pic`) VALUES
('ADMIN-1', 'legatura_hq2026', 'sandbox.info.official@gmail.com', '$2y$12$.2ali6CucAVDUpSsjlvlPucDtp2j8gI4Ml7pbKtyZ0fpCiml.nVVe', 'Ph', NULL, 'Legatura', 1, '2026-03-05 00:07:36', NULL);

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
(1, 2001, 52, 5800000.00, 180, 'We have extensive experience with residential complexes. Our team can deliver quality work on schedule with competitive pricing.', 'accepted', NULL, '2026-03-01 00:00:00', '2026-03-05 06:30:00'),
(2, 2001, 53, 6200000.00, 200, 'Experienced team ready to start immediately.', 'rejected', 'Higher cost and longer timeline compared to selected contractor', '2026-03-01 01:15:00', '2026-03-05 06:30:00'),
(3, 2001, 54, 6500000.00, 210, 'Quality assured work with premium materials.', 'rejected', 'Cost exceeds budget range', '2026-03-01 02:30:00', '2026-03-05 06:30:00'),
(4, 2002, 55, 2400000.00, 60, 'Specialized in commercial renovations. Quick turnaround with excellent quality.', 'accepted', NULL, '2026-02-14 23:00:00', '2026-02-20 03:00:00'),
(5, 2002, 56, 2700000.00, 75, 'Professional team with good track record.', 'rejected', 'Higher cost and longer timeline', '2026-02-15 00:30:00', '2026-02-20 03:00:00'),
(6, 2002, 57, 2900000.00, 90, 'Premium quality renovation services.', 'rejected', 'Cost exceeds budget', '2026-02-15 01:45:00', '2026-02-20 03:00:00'),
(7, 2003, 58, 8800000.00, 150, 'Industrial construction specialists. Proven track record with warehouse projects.', 'accepted', NULL, '2026-02-09 22:30:00', '2026-02-18 02:15:00'),
(8, 2003, 59, 9200000.00, 170, 'Experienced industrial contractors.', 'rejected', 'Higher cost and longer timeline', '2026-02-09 23:45:00', '2026-02-18 02:15:00'),
(9, 2003, 60, 9500000.00, 180, 'Quality industrial work guaranteed.', 'rejected', 'Cost exceeds budget', '2026-02-10 01:00:00', '2026-02-18 02:15:00'),
(10, 2004, 62, 1750000.00, 90, 'Agricultural facility specialists. Efficient and cost-effective solutions.', 'accepted', NULL, '2026-02-20 00:00:00', '2026-02-25 05:45:00'),
(11, 2004, 61, 1950000.00, 110, 'Experienced in agricultural projects.', 'rejected', 'Higher cost and longer timeline', '2026-02-20 01:15:00', '2026-02-25 05:45:00'),
(12, 2004, 63, 2100000.00, 120, 'Premium agricultural construction.', 'rejected', 'Cost exceeds budget', '2026-02-20 02:30:00', '2026-02-25 05:45:00'),
(13, 2005, 64, 17500000.00, 300, 'Large-scale residential development experts. Proven capability with 50+ unit projects.', 'accepted', NULL, '2026-02-24 23:30:00', '2026-03-03 07:20:00'),
(14, 2005, 65, 18500000.00, 330, 'Experienced residential developers.', 'rejected', 'Higher cost and longer timeline', '2026-02-25 00:45:00', '2026-03-03 07:20:00'),
(15, 2005, 66, 19200000.00, 350, 'Premium residential construction.', 'rejected', 'Cost exceeds budget', '2026-02-25 02:00:00', '2026-03-03 07:20:00'),
(16, 2021, 67, 7800000.00, 160, 'Residential complex specialists with proven track record.', 'accepted', NULL, '2026-02-28 00:15:00', '2026-03-04 04:30:00'),
(17, 2021, 68, 8200000.00, 180, 'Experienced residential contractors.', 'rejected', 'Higher cost and longer timeline', '2026-02-28 01:30:00', '2026-03-04 04:30:00'),
(18, 2021, 69, 8600000.00, 200, 'Premium quality residential work.', 'rejected', 'Cost exceeds budget', '2026-02-28 02:45:00', '2026-03-04 04:30:00'),
(19, 2022, 70, 5800000.00, 140, 'Retail center construction specialists.', 'accepted', NULL, '2026-02-28 23:45:00', '2026-03-05 03:15:00'),
(20, 2022, 71, 6200000.00, 160, 'Commercial construction experts.', 'rejected', 'Higher cost and longer timeline', '2026-03-01 01:00:00', '2026-03-05 03:15:00'),
(21, 2022, 72, 6600000.00, 180, 'Premium commercial construction.', 'rejected', 'Cost exceeds budget', '2026-03-01 02:15:00', '2026-03-05 03:15:00'),
(22, 2024, 75, 9200000.00, 170, 'Office building construction experts with modern design capabilities.', 'accepted', NULL, '2026-03-02 00:30:00', '2026-03-06 06:00:00'),
(23, 2024, 76, 9800000.00, 190, 'Experienced office building contractors.', 'rejected', 'Higher cost and longer timeline', '2026-03-02 01:45:00', '2026-03-06 06:00:00'),
(24, 2024, 77, 10400000.00, 210, 'Premium office construction services.', 'rejected', 'Cost exceeds budget', '2026-03-02 03:00:00', '2026-03-06 06:00:00'),
(25, 2006, 78, 11500000.00, 240, 'Luxury residential specialists with premium finishes.', 'submitted', NULL, '2026-03-03 00:00:00', NULL),
(26, 2006, 79, 12000000.00, 260, 'High-end residential construction.', 'submitted', NULL, '2026-03-03 01:30:00', NULL),
(27, 2006, 80, 12500000.00, 280, 'Luxury home builders.', 'submitted', NULL, '2026-03-03 03:00:00', NULL),
(28, 2007, 81, 55000000.00, 450, 'Large-scale commercial development specialists.', 'submitted', NULL, '2026-03-03 23:30:00', NULL),
(29, 2007, 52, 58000000.00, 480, 'Shopping mall construction experts.', 'submitted', NULL, '2026-03-04 01:00:00', NULL),
(30, 2007, 53, 60000000.00, 500, 'Premium mall development.', 'submitted', NULL, '2026-03-04 02:30:00', NULL),
(31, 2008, 54, 35000000.00, 360, 'High-rise residential specialists.', 'submitted', NULL, '2026-03-05 00:15:00', NULL),
(32, 2008, 55, 38000000.00, 390, 'Condo tower construction experts.', 'submitted', NULL, '2026-03-05 01:45:00', NULL),
(33, 2008, 56, 40000000.00, 420, 'Premium high-rise construction.', 'submitted', NULL, '2026-03-05 03:15:00', NULL),
(34, 2009, 57, 22000000.00, 280, 'Medical facility construction specialists.', 'submitted', NULL, '2026-03-05 23:45:00', NULL),
(35, 2009, 58, 24000000.00, 300, 'Healthcare construction experts.', 'submitted', NULL, '2026-03-06 01:15:00', NULL),
(36, 2009, 59, 26000000.00, 320, 'Premium medical center construction.', 'submitted', NULL, '2026-03-06 02:45:00', NULL),
(37, 2010, 60, 9000000.00, 200, 'Educational building specialists.', 'submitted', NULL, '2026-03-07 00:00:00', NULL),
(38, 2010, 61, 9500000.00, 220, 'School construction experts.', 'submitted', NULL, '2026-03-07 01:30:00', NULL),
(39, 2010, 62, 10000000.00, 240, 'Premium educational construction.', 'submitted', NULL, '2026-03-07 03:00:00', NULL),
(40, 2011, 63, 6500000.00, 150, 'Townhouse development specialists.', 'submitted', NULL, '2026-03-08 00:30:00', NULL),
(41, 2011, 64, 7000000.00, 170, 'Residential townhouse experts.', 'submitted', NULL, '2026-03-08 02:00:00', NULL),
(42, 2011, 65, 7500000.00, 190, 'Premium townhouse construction.', 'submitted', NULL, '2026-03-08 03:30:00', NULL),
(43, 2012, 66, 3500000.00, 120, 'Community center construction specialists.', 'submitted', NULL, '2026-03-09 00:15:00', NULL),
(44, 2012, 67, 3800000.00, 140, 'Multi-purpose facility experts.', 'submitted', NULL, '2026-03-09 01:45:00', NULL),
(45, 2012, 68, 4000000.00, 160, 'Premium community center construction.', 'submitted', NULL, '2026-03-09 03:15:00', NULL),
(46, 2013, 69, 13500000.00, 280, 'Retail complex specialists.', 'submitted', NULL, '2026-03-10 00:00:00', NULL),
(47, 2013, 70, 14500000.00, 300, 'Shopping complex experts.', 'submitted', NULL, '2026-03-10 01:30:00', NULL),
(48, 2013, 71, 15500000.00, 320, 'Premium retail construction.', 'submitted', NULL, '2026-03-10 03:00:00', NULL),
(49, 2014, 72, 4500000.00, 140, 'Agricultural processing specialists.', 'submitted', NULL, '2026-03-11 00:30:00', NULL),
(50, 2014, 73, 4800000.00, 160, 'Agricultural facility experts.', 'submitted', NULL, '2026-03-11 02:00:00', NULL),
(51, 2014, 74, 5000000.00, 180, 'Premium agricultural construction.', 'submitted', NULL, '2026-03-11 03:30:00', NULL),
(52, 2015, 75, 20000000.00, 360, 'Industrial complex specialists.', 'submitted', NULL, '2026-03-12 00:15:00', NULL),
(53, 2015, 76, 22000000.00, 390, 'Large industrial facility experts.', 'submitted', NULL, '2026-03-12 01:45:00', NULL),
(54, 2015, 77, 24000000.00, 420, 'Premium industrial construction.', 'submitted', NULL, '2026-03-12 03:15:00', NULL),
(55, 2016, 78, 28000000.00, 400, 'Residential estate specialists.', 'submitted', NULL, '2026-03-13 00:00:00', NULL),
(56, 2016, 79, 30000000.00, 430, 'Large estate development experts.', 'submitted', NULL, '2026-03-13 01:30:00', NULL),
(57, 2016, 80, 32000000.00, 460, 'Premium estate construction.', 'submitted', NULL, '2026-03-13 03:00:00', NULL),
(58, 2017, 81, 45000000.00, 500, 'High-rise office specialists.', 'submitted', NULL, '2026-03-14 00:30:00', NULL),
(59, 2017, 52, 48000000.00, 530, 'Commercial tower experts.', 'submitted', NULL, '2026-03-14 02:00:00', NULL),
(60, 2017, 53, 50000000.00, 560, 'Premium office tower construction.', 'submitted', NULL, '2026-03-14 03:30:00', NULL),
(61, 2018, 54, 55000000.00, 520, 'Industrial complex specialists.', 'submitted', NULL, '2026-03-15 00:15:00', NULL),
(62, 2018, 55, 58000000.00, 550, 'Large industrial experts.', 'submitted', NULL, '2026-03-15 01:45:00', NULL),
(63, 2018, 56, 60000000.00, 580, 'Premium industrial construction.', 'submitted', NULL, '2026-03-15 03:15:00', NULL),
(64, 2019, 57, 9500000.00, 240, 'Agricultural farm specialists.', 'submitted', NULL, '2026-03-16 00:00:00', NULL),
(65, 2019, 58, 10500000.00, 270, 'Large farm development experts.', 'submitted', NULL, '2026-03-16 01:30:00', NULL),
(66, 2019, 59, 11500000.00, 300, 'Premium agricultural development.', 'submitted', NULL, '2026-03-16 03:00:00', NULL),
(67, 2020, 60, 40000000.00, 450, 'Mixed-use development specialists.', 'submitted', NULL, '2026-03-17 00:30:00', NULL),
(68, 2020, 61, 43000000.00, 480, 'Complex development experts.', 'submitted', NULL, '2026-03-17 02:00:00', NULL),
(69, 2020, 62, 45000000.00, 510, 'Premium mixed-use construction.', 'submitted', NULL, '2026-03-17 03:30:00', NULL),
(70, 2023, 63, 14000000.00, 320, 'Residential subdivision specialists.', 'submitted', NULL, '2026-03-18 00:15:00', NULL),
(71, 2023, 64, 15000000.00, 350, 'Subdivision development experts.', 'submitted', NULL, '2026-03-18 01:45:00', NULL),
(72, 2023, 65, 16000000.00, 380, 'Premium subdivision construction.', 'submitted', NULL, '2026-03-18 03:15:00', NULL),
(73, 2025, 66, 7000000.00, 180, 'Warehouse and logistics specialists.', 'submitted', NULL, '2026-03-19 00:00:00', NULL),
(74, 2025, 67, 7500000.00, 200, 'Logistics facility experts.', 'submitted', NULL, '2026-03-19 01:30:00', NULL),
(75, 2025, 68, 8000000.00, 220, 'Premium warehouse construction.', 'submitted', NULL, '2026-03-19 03:00:00', NULL);

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
-- Table structure for table `content_reports`
--

CREATE TABLE `content_reports` (
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `reporter_user_id` int(11) NOT NULL,
  `content_type` enum('project','showcase') NOT NULL,
  `content_id` bigint(20) UNSIGNED NOT NULL,
  `reason` varchar(120) NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','under_review','resolved','dismissed') NOT NULL DEFAULT 'pending',
  `reviewed_by_user_id` int(11) DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contractors`
--

CREATE TABLE `contractors` (
  `contractor_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `company_banner` varchar(255) DEFAULT NULL,
  `company_name` varchar(200) NOT NULL,
  `company_start_date` date NOT NULL DEFAULT current_timestamp(),
  `years_of_experience` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `contractor_type_other` varchar(200) DEFAULT NULL,
  `services_offered` text NOT NULL,
  `business_address` text NOT NULL,
  `company_email` varchar(100) NOT NULL,
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

INSERT INTO `contractors` (`contractor_id`, `owner_id`, `company_logo`, `company_banner`, `company_name`, `company_start_date`, `years_of_experience`, `type_id`, `contractor_type_other`, `services_offered`, `business_address`, `company_email`, `company_website`, `company_social_media`, `company_description`, `picab_number`, `picab_category`, `picab_expiration_date`, `business_permit_number`, `business_permit_city`, `business_permit_expiration`, `tin_business_reg_number`, `dti_sec_registration_photo`, `verification_status`, `verification_date`, `is_active`, `suspension_until`, `suspension_reason`, `deletion_reason`, `rejection_reason`, `completed_projects`, `created_at`, `updated_at`) VALUES
(52, 201, NULL, NULL, 'Dingdong Builders', '2024-01-15', 8, 1, NULL, 'Residential Construction', 'Tetuan, Poblacion, Mankayan, Benguet 2600', 'dingdong@builders.com', NULL, NULL, 'Quality residential construction with 8 years experience', 'PCAB-301', 'A', '2027-06-15', 'BP-301', 'Mankayan', '2027-03-15', 'TIN-301', 'dti_dingdong.jpg', 'approved', '2026-02-01 02:00:00', 1, NULL, NULL, NULL, NULL, 12, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(53, 202, NULL, NULL, 'Anne Builders Corp', '2023-06-20', 10, 1, NULL, 'Commercial Construction', 'Magsaysay, Barangay Poblacion, Baguio City, Benguet 2600', 'anne@builders.com', NULL, NULL, 'Commercial and residential projects', 'PCAB-302', 'AA', '2027-12-20', 'BP-302', 'Baguio', '2027-06-20', 'TIN-302', 'dti_anne.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 8, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(54, 203, NULL, NULL, 'Alden Construction', '2022-03-10', 12, 1, NULL, 'Heavy Infrastructure', 'Session Road, Barangay Asin, Baguio City, Benguet 2600', 'alden@construction.com', NULL, NULL, 'Infrastructure and heavy construction', 'PCAB-303', 'AAA', '2028-03-10', 'BP-303', 'Baguio', '2027-09-10', 'TIN-303', 'dti_alden.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, 'Incomplete documentation', 5, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(55, 204, NULL, NULL, 'Arjo Builders', '2023-11-05', 7, 2, NULL, 'Electrical & Mechanical', 'Burnham Road, Barangay Irisan, Baguio City, Benguet 2600', 'arjo@builders.com', NULL, NULL, 'Specialized electrical and mechanical work', 'PCAB-304', 'B', '2027-11-05', 'BP-304', 'Baguio', '2027-05-05', 'TIN-304', 'dti_arjo.jpg', 'approved', '2026-02-15 06:30:00', 1, NULL, NULL, NULL, NULL, 9, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(56, 205, NULL, NULL, 'Aga Sarao Construction', '2024-02-28', 6, 3, NULL, 'Landscaping & Masonry', 'Marcos Highway, Barangay Loakan, Baguio City, Benguet 2600', 'aga@sarao.com', NULL, NULL, 'Landscaping and masonry services', 'PCAB-305', 'C', '2027-02-28', 'BP-305', 'Baguio', '2027-08-28', 'TIN-305', 'dti_aga.jpg', 'deleted', NULL, 0, NULL, NULL, 'Company closure', NULL, 3, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(57, 206, NULL, NULL, 'Angelica Builders', '2023-09-12', 9, 1, NULL, 'Residential Projects', 'Naguilian Road, Barangay Pinsao, Baguio City, Benguet 2600', 'angelica@builders.com', NULL, NULL, 'Premium residential construction', 'PCAB-306', 'AA', '2027-09-12', 'BP-306', 'Baguio', '2027-03-12', 'TIN-306', 'dti_angelica.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 7, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(58, 207, NULL, NULL, 'Andi Builders', '2022-07-18', 11, 1, NULL, 'Mixed Construction', 'Bokawkan Road, Barangay Cabayan, Baguio City, Benguet 2600', 'andi@builders.com', NULL, NULL, 'Mixed residential and commercial', 'PCAB-307', 'A', '2028-07-18', 'BP-307', 'Baguio', '2027-01-18', 'TIN-307', 'dti_andi.jpg', 'approved', '2026-01-20 01:15:00', 1, NULL, NULL, NULL, NULL, 15, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(59, 208, NULL, NULL, 'Gretchen Builders', '2023-05-22', 8, 2, NULL, 'Interior Design', 'Asin Road, Barangay Asin, Baguio City, Benguet 2600', 'gretchen@builders.com', NULL, NULL, 'Interior design and finishing', 'PCAB-308', 'B', '2027-05-22', 'BP-308', 'Baguio', '2027-11-22', 'TIN-308', 'dti_gretchen.jpg', 'approved', '2026-02-10 03:45:00', 1, NULL, NULL, NULL, NULL, 6, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(60, 209, NULL, NULL, 'Graceful Structures', '2024-04-03', 5, 3, NULL, 'Roofing Specialist', 'Outlook Drive, Barangay Outlook, Baguio City, Benguet 2600', 'graceful@structures.com', NULL, NULL, 'Roofing and waterproofing', 'PCAB-309', 'C', '2027-04-03', 'BP-309', 'Baguio', '2027-10-03', 'TIN-309', 'dti_graceful.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, 'Unverified credentials', 2, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(61, 210, NULL, NULL, 'Gringo Builders', '2023-08-14', 9, 1, NULL, 'General Construction', 'Mines View Road, Barangay Mines View, Baguio City, Benguet 2600', 'gringo@builders.com', NULL, NULL, 'General construction services', 'PCAB-310', 'AA', '2027-08-14', 'BP-310', 'Baguio', '2027-02-14', 'TIN-310', 'dti_gringo.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 8, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(62, 211, NULL, NULL, 'Gino Builders', '2022-12-01', 13, 1, NULL, 'High-Rise Construction', 'Trancoville, Barangay Trancoville, Baguio City, Benguet 2600', 'gino@builders.com', NULL, NULL, 'High-rise and complex projects', 'PCAB-311', 'AAA', '2028-12-01', 'BP-311', 'Baguio', '2027-06-01', 'TIN-311', 'dti_gino.jpg', 'approved', '2026-01-15 05:20:00', 1, NULL, NULL, NULL, NULL, 18, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(63, 212, NULL, NULL, 'Giancarlo Builders', '2023-10-09', 7, 2, NULL, 'Mechanical Systems', 'Pacdal Road, Barangay Pacdal, Baguio City, Benguet 2600', 'giancarlo@builders.com', NULL, NULL, 'HVAC and mechanical systems', 'PCAB-312', 'B', '2027-10-09', 'BP-312', 'Baguio', '2027-04-09', 'TIN-312', 'dti_giancarlo.jpg', 'deleted', NULL, 0, NULL, NULL, 'Voluntary closure', NULL, 4, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(64, 213, NULL, NULL, 'Giselle Builders', '2024-01-25', 6, 3, NULL, 'Landscaping', 'Inya Road, Barangay Inya, Baguio City, Benguet 2600', 'giselle@builders.com', NULL, NULL, 'Landscape design and installation', 'PCAB-313', 'C', '2027-01-25', 'BP-313', 'Baguio', '2027-07-25', 'TIN-313', 'dti_giselle.jpg', 'approved', '2026-02-20 07:00:00', 1, NULL, NULL, NULL, NULL, 5, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(65, 214, NULL, NULL, 'Glenda Builders', '2023-04-17', 10, 1, NULL, 'Residential Complex', 'Kisad Road, Barangay Kisad, Baguio City, Benguet 2600', 'glenda@builders.com', NULL, NULL, 'Residential complex development', 'PCAB-314', 'AA', '2027-04-17', 'BP-314', 'Baguio', '2027-10-17', 'TIN-314', 'dti_glenda.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 11, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(66, 215, NULL, NULL, 'Glaiza Builders', '2022-09-06', 12, 1, NULL, 'Infrastructure', 'Chanum Road, Barangay Chanum, Baguio City, Benguet 2600', 'glaiza@builders.com', NULL, NULL, 'Infrastructure development', 'PCAB-315', 'AAA', '2028-09-06', 'BP-315', 'Baguio', '2027-03-06', 'TIN-315', 'dti_glaiza.jpg', 'approved', '2026-01-25 02:30:00', 1, NULL, NULL, NULL, NULL, 16, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(67, 216, NULL, NULL, 'Gladys Builders', '2023-11-28', 8, 2, NULL, 'Electrical Works', 'Shuntug Road, Barangay Shuntug, Baguio City, Benguet 2600', 'gladys@builders.com', NULL, NULL, 'Electrical installation and maintenance', 'PCAB-316', 'B', '2027-11-28', 'BP-316', 'Baguio', '2027-05-28', 'TIN-316', 'dti_gladys.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, 'Failed inspection', 3, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(68, 217, NULL, NULL, 'Glenda Reyes Builders', '2024-03-11', 5, 3, NULL, 'Painting Services', 'Loakan Road, Barangay Loakan, Baguio City, Benguet 2600', 'glenda.reyes@builders.com', NULL, NULL, 'Professional painting and coating', 'PCAB-317', 'C', '2027-03-11', 'BP-317', 'Baguio', '2027-09-11', 'TIN-317', 'dti_glenda_reyes.jpg', 'approved', '2026-02-05 06:15:00', 1, NULL, NULL, NULL, NULL, 4, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(69, 218, NULL, NULL, 'Glenda Santos Builders', '2023-07-19', 9, 1, NULL, 'General Contractor', 'Pinsao Road, Barangay Pinsao, Baguio City, Benguet 2600', 'glenda.santos@builders.com', NULL, NULL, 'Full-service general contracting', 'PCAB-318', 'AA', '2027-07-19', 'BP-318', 'Baguio', '2027-01-19', 'TIN-318', 'dti_glenda_santos.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 10, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(70, 219, NULL, NULL, 'Glenda Villanueva Builders', '2022-05-08', 14, 1, NULL, 'Major Projects', 'Cabayan Road, Barangay Cabayan, Baguio City, Benguet 2600', 'glenda.villanueva@builders.com', NULL, NULL, 'Large-scale project execution', 'PCAB-319', 'AAAA', '2028-05-08', 'BP-319', 'Baguio', '2027-11-08', 'TIN-319', 'dti_glenda_villanueva.jpg', 'approved', '2026-01-10 01:45:00', 1, NULL, NULL, NULL, NULL, 22, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(71, 220, NULL, NULL, 'Glenda Gonzales Builders', '2023-02-14', 8, 2, NULL, 'Plumbing Systems', 'Asin Road, Barangay Asin, Baguio City, Benguet 2600', 'glenda.gonzales@builders.com', NULL, NULL, 'Plumbing design and installation', 'PCAB-320', 'B', '2027-02-14', 'BP-320', 'Baguio', '2027-08-14', 'TIN-320', 'dti_glenda_gonzales.jpg', 'deleted', NULL, 0, NULL, NULL, 'Business relocation', NULL, 6, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(72, 221, NULL, NULL, 'Glenda Fernandez Builders', '2024-06-03', 4, 3, NULL, 'Carpentry', 'Outlook Drive, Barangay Outlook, Baguio City, Benguet 2600', 'glenda.fernandez@builders.com', NULL, NULL, 'Custom carpentry and woodwork', 'PCAB-321', 'C', '2027-06-03', 'BP-321', 'Baguio', '2027-12-03', 'TIN-321', 'dti_glenda_fernandez.jpg', 'approved', '2026-02-28 03:20:00', 1, NULL, NULL, NULL, NULL, 3, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(73, 222, NULL, NULL, 'Glenda Mercado Builders', '2023-08-22', 9, 1, NULL, 'Renovation Services', 'Mines View Road, Barangay Mines View, Baguio City, Benguet 2600', 'glenda.mercado@builders.com', NULL, NULL, 'Building renovation and restoration', 'PCAB-322', 'AA', '2027-08-22', 'BP-322', 'Baguio', '2027-02-22', 'TIN-322', 'dti_glenda_mercado.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, 'Documentation issues', 7, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(74, 223, NULL, NULL, 'Glenda Ramos Builders', '2022-10-11', 11, 1, NULL, 'Commercial Projects', 'Trancoville, Barangay Trancoville, Baguio City, Benguet 2600', 'glenda.ramos@builders.com', NULL, NULL, 'Commercial building construction', 'PCAB-323', 'AAA', '2028-10-11', 'BP-323', 'Baguio', '2027-04-11', 'TIN-323', 'dti_glenda_ramos.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 14, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(75, 224, NULL, NULL, 'Glenda Reyes Builders II', '2023-01-30', 7, 2, NULL, 'HVAC Systems', 'Pacdal Road, Barangay Pacdal, Baguio City, Benguet 2600', 'glenda.reyes2@builders.com', NULL, NULL, 'Heating and cooling systems', 'PCAB-324', 'B', '2027-01-30', 'BP-324', 'Baguio', '2027-07-30', 'TIN-324', 'dti_glenda_reyes2.jpg', 'approved', '2026-02-12 08:00:00', 1, NULL, NULL, NULL, NULL, 8, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(76, 225, NULL, NULL, 'Glenda Santos Builders II', '2024-04-19', 5, 3, NULL, 'Masonry Work', 'Inya Road, Barangay Inya, Baguio City, Benguet 2600', 'glenda.santos2@builders.com', NULL, NULL, 'Brick and stone masonry', 'PCAB-325', 'C', '2027-04-19', 'BP-325', 'Baguio', '2027-10-19', 'TIN-325', 'dti_glenda_santos2.jpg', 'deleted', NULL, 0, NULL, NULL, 'Inactive status', NULL, 2, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(77, 226, NULL, NULL, 'Glenda Villanueva Builders II', '2023-09-05', 10, 1, NULL, 'Structural Work', 'Kisad Road, Barangay Kisad, Baguio City, Benguet 2600', 'glenda.villanueva2@builders.com', NULL, NULL, 'Structural engineering and construction', 'PCAB-326', 'AA', '2027-09-05', 'BP-326', 'Baguio', '2027-03-05', 'TIN-326', 'dti_glenda_villanueva2.jpg', 'approved', '2026-01-30 04:30:00', 1, NULL, NULL, NULL, NULL, 13, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(78, 227, NULL, NULL, 'Glenda Gonzales Builders II', '2022-06-14', 13, 1, NULL, 'Bridge Construction', 'Chanum Road, Barangay Chanum, Baguio City, Benguet 2600', 'glenda.gonzales2@builders.com', NULL, NULL, 'Bridge and overpass construction', 'PCAB-327', 'AAA', '2028-06-14', 'BP-327', 'Baguio', '2027-12-14', 'TIN-327', 'dti_glenda_gonzales2.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 19, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(79, 228, NULL, NULL, 'Glenda Fernandez Builders II', '2023-03-27', 8, 2, NULL, 'Welding Services', 'Shuntug Road, Barangay Shuntug, Baguio City, Benguet 2600', 'glenda.fernandez2@builders.com', NULL, NULL, 'Structural steel welding', 'PCAB-328', 'B', '2027-03-27', 'BP-328', 'Baguio', '2027-09-27', 'TIN-328', 'dti_glenda_fernandez2.jpg', 'approved', '2026-02-08 05:45:00', 1, NULL, NULL, NULL, NULL, 9, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(80, 229, NULL, NULL, 'Glenda Mercado Builders II', '2024-05-06', 6, 3, NULL, 'Concrete Work', 'Loakan Road, Barangay Loakan, Baguio City, Benguet 2600', 'glenda.mercado2@builders.com', NULL, NULL, 'Concrete pouring and finishing', 'PCAB-329', 'C', '2027-05-06', 'BP-329', 'Baguio', '2027-11-06', 'TIN-329', 'dti_glenda_mercado2.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, 'Quality concerns', 4, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(81, 230, NULL, NULL, 'Glenda Ramos Builders II', '2023-10-16', 9, 1, NULL, 'Finishing Works', 'Pinsao Road, Barangay Pinsao, Baguio City, Benguet 2600', 'glenda.ramos2@builders.com', NULL, NULL, 'Interior finishing and details', 'PCAB-330', 'AA', '2027-10-16', 'BP-330', 'Baguio', '2027-04-16', 'TIN-330', 'dti_glenda_ramos2.jpg', 'approved', '2026-02-18 02:15:00', 1, NULL, NULL, NULL, NULL, 11, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(82, 231, NULL, NULL, 'Glenda Reyes Builders III', '2022-11-23', 12, 1, NULL, 'Restoration Work', 'Mines View Road, Barangay Mines View, Baguio City, Benguet 2600', 'glenda.reyes3@builders.com', NULL, NULL, 'Heritage restoration and preservation', 'PCAB-331', 'AAA', '2028-11-23', 'BP-331', 'Baguio', '2027-05-23', 'TIN-331', 'dti_glenda_reyes3.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 17, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(83, 232, NULL, NULL, 'Glenda Santos Builders III', '2023-04-02', 7, 2, NULL, 'Insulation Work', 'Trancoville, Barangay Trancoville, Baguio City, Benguet 2600', 'glenda.santos3@builders.com', NULL, NULL, 'Thermal and acoustic insulation', 'PCAB-332', 'B', '2027-04-02', 'BP-332', 'Baguio', '2027-10-02', 'TIN-332', 'dti_glenda_santos3.jpg', 'approved', '2026-02-22 06:50:00', 1, NULL, NULL, NULL, NULL, 5, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(84, 233, NULL, NULL, 'Glenda Villanueva Builders III', '2024-07-11', 5, 3, NULL, 'Demolition Services', 'Pacdal Road, Barangay Pacdal, Baguio City, Benguet 2600', 'glenda.villanueva3@builders.com', NULL, NULL, 'Safe demolition and site clearing', 'PCAB-333', 'C', '2027-07-11', 'BP-333', 'Baguio', '2027-01-11', 'TIN-333', 'dti_glenda_villanueva3.jpg', 'deleted', NULL, 0, NULL, NULL, 'Permit expiration', NULL, 1, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(85, 234, NULL, NULL, 'Glenda Gonzales Builders III', '2023-12-08', 8, 1, NULL, 'Renovation', 'Inya Road, Barangay Inya, Baguio City, Benguet 2600', 'glenda.gonzales3@builders.com', NULL, NULL, 'Complete renovation services', 'PCAB-334', 'AA', '2027-12-08', 'BP-334', 'Baguio', '2027-06-08', 'TIN-334', 'dti_glenda_gonzales3.jpg', 'approved', '2026-01-28 07:30:00', 1, NULL, NULL, NULL, NULL, 10, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(86, 235, NULL, NULL, 'Glenda Fernandez Builders III', '2022-08-19', 11, 1, NULL, 'Expansion Projects', 'Kisad Road, Barangay Kisad, Baguio City, Benguet 2600', 'glenda.fernandez3@builders.com', NULL, NULL, 'Building expansion and extension', 'PCAB-335', 'AAA', '2028-08-19', 'BP-335', 'Baguio', '2027-02-19', 'TIN-335', 'dti_glenda_fernandez3.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, 'Safety violations', 8, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(87, 236, NULL, NULL, 'Glenda Mercado Builders III', '2023-06-27', 9, 2, NULL, 'Facade Work', 'Chanum Road, Barangay Chanum, Baguio City, Benguet 2600', 'glenda.mercado3@builders.com', NULL, NULL, 'Building facade and cladding', 'PCAB-336', 'B', '2027-06-27', 'BP-336', 'Baguio', '2027-12-27', 'TIN-336', 'dti_glenda_mercado3.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 6, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(88, 237, NULL, NULL, 'Glenda Ramos Builders III', '2024-02-05', 6, 3, NULL, 'Landscaping', 'Shuntug Road, Barangay Shuntug, Baguio City, Benguet 2600', 'glenda.ramos3@builders.com', NULL, NULL, 'Landscape design and maintenance', 'PCAB-337', 'C', '2027-02-05', 'BP-337', 'Baguio', '2027-08-05', 'TIN-337', 'dti_glenda_ramos3.jpg', 'approved', '2026-02-25 03:00:00', 1, NULL, NULL, NULL, NULL, 4, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(89, 238, NULL, NULL, 'Glenda Reyes Builders IV', '2023-09-14', 8, 1, NULL, 'Maintenance Services', 'Loakan Road, Barangay Loakan, Baguio City, Benguet 2600', 'glenda.reyes4@builders.com', NULL, NULL, 'Building maintenance and repairs', 'PCAB-338', 'AA', '2027-09-14', 'BP-338', 'Baguio', '2027-03-14', 'TIN-338', 'dti_glenda_reyes4.jpg', 'deleted', NULL, 0, NULL, NULL, 'Voluntary withdrawal', NULL, 7, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(90, 239, NULL, NULL, 'Glenda Santos Builders IV', '2022-10-03', 13, 1, NULL, 'Infrastructure', 'Pinsao Road, Barangay Pinsao, Baguio City, Benguet 2600', 'glenda.santos4@builders.com', NULL, NULL, 'Infrastructure development', 'PCAB-339', 'AAAA', '2028-10-03', 'BP-339', 'Baguio', '2027-04-03', 'TIN-339', 'dti_glenda_santos4.jpg', 'approved', '2026-01-05 01:20:00', 1, NULL, NULL, NULL, NULL, 20, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(91, 240, NULL, NULL, 'Glenda Villanueva Builders IV', '2023-05-21', 7, 2, NULL, 'Plumbing', 'Asin Road, Barangay Asin, Baguio City, Benguet 2600', 'glenda.villanueva4@builders.com', NULL, NULL, 'Plumbing systems and fixtures', 'PCAB-340', 'B', '2027-05-21', 'BP-340', 'Baguio', '2027-11-21', 'TIN-340', 'dti_glenda_villanueva4.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 5, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(92, 241, NULL, NULL, 'Glenda Gonzales Builders IV', '2024-03-10', 5, 3, NULL, 'Tiling Work', 'Outlook Drive, Barangay Outlook, Baguio City, Benguet 2600', 'glenda.gonzales4@builders.com', NULL, NULL, 'Ceramic and tile installation', 'PCAB-341', 'C', '2027-03-10', 'BP-341', 'Baguio', '2027-09-10', 'TIN-341', 'dti_glenda_gonzales4.jpg', 'approved', '2026-02-14 08:45:00', 1, NULL, NULL, NULL, NULL, 3, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(93, 242, NULL, NULL, 'Glenda Fernandez Builders IV', '2023-11-07', 9, 1, NULL, 'General Contracting', 'Mines View Road, Barangay Mines View, Baguio City, Benguet 2600', 'glenda.fernandez4@builders.com', NULL, NULL, 'Full-service general contracting', 'PCAB-342', 'AA', '2027-11-07', 'BP-342', 'Baguio', '2027-05-07', 'TIN-342', 'dti_glenda_fernandez4.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, 'Compliance issues', 9, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(94, 243, NULL, NULL, 'Glenda Mercado Builders IV', '2022-07-25', 12, 1, NULL, 'Complex Projects', 'Trancoville, Barangay Trancoville, Baguio City, Benguet 2600', 'glenda.mercado4@builders.com', NULL, NULL, 'Complex construction projects', 'PCAB-343', 'AAA', '2028-07-25', 'BP-343', 'Baguio', '2027-01-25', 'TIN-343', 'dti_glenda_mercado4.jpg', 'approved', '2026-02-01 04:15:00', 1, NULL, NULL, NULL, NULL, 18, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(95, 244, NULL, NULL, 'Glenda Ramos Builders IV', '2023-08-13', 8, 2, NULL, 'Electrical', 'Pacdal Road, Barangay Pacdal, Baguio City, Benguet 2600', 'glenda.ramos4@builders.com', NULL, NULL, 'Electrical systems and wiring', 'PCAB-344', 'B', '2027-08-13', 'BP-344', 'Baguio', '2027-02-13', 'TIN-344', 'dti_glenda_ramos4.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 7, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(96, 245, NULL, NULL, 'Glenda Reyes Builders V', '2024-01-22', 6, 3, NULL, 'Painting', 'Inya Road, Barangay Inya, Baguio City, Benguet 2600', 'glenda.reyes5@builders.com', NULL, NULL, 'Professional painting services', 'PCAB-345', 'C', '2027-01-22', 'BP-345', 'Baguio', '2027-07-22', 'TIN-345', 'dti_glenda_reyes5.jpg', 'deleted', NULL, 0, NULL, NULL, 'Inactive account', NULL, 2, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(97, 246, NULL, NULL, 'Glenda Santos Builders V', '2023-04-30', 10, 1, NULL, 'Residential', 'Kisad Road, Barangay Kisad, Baguio City, Benguet 2600', 'glenda.santos5@builders.com', NULL, NULL, 'Residential construction', 'PCAB-346', 'AA', '2027-04-30', 'BP-346', 'Baguio', '2027-10-30', 'TIN-346', 'dti_glenda_santos5.jpg', 'approved', '2026-01-18 05:30:00', 1, NULL, NULL, NULL, NULL, 12, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(98, 247, NULL, NULL, 'Glenda Villanueva Builders V', '2022-09-12', 11, 1, NULL, 'Commercial', 'Chanum Road, Barangay Chanum, Baguio City, Benguet 2600', 'glenda.villanueva5@builders.com', NULL, NULL, 'Commercial building construction', 'PCAB-347', 'AAA', '2028-09-12', 'BP-347', 'Baguio', '2027-03-12', 'TIN-347', 'dti_glenda_villanueva5.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, 'Unresolved disputes', 14, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(99, 248, NULL, NULL, 'Glenda Gonzales Builders V', '2023-06-08', 8, 2, NULL, 'Mechanical', 'Shuntug Road, Barangay Shuntug, Baguio City, Benguet 2600', 'glenda.gonzales5@builders.com', NULL, NULL, 'Mechanical systems installation', 'PCAB-348', 'B', '2027-06-08', 'BP-348', 'Baguio', '2027-12-08', 'TIN-348', 'dti_glenda_gonzales5.jpg', 'approved', '2026-02-10 02:00:00', 1, NULL, NULL, NULL, NULL, 6, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(100, 249, NULL, NULL, 'Glenda Fernandez Builders V', '2024-02-19', 5, 3, NULL, 'Carpentry', 'Loakan Road, Barangay Loakan, Baguio City, Benguet 2600', 'glenda.fernandez5@builders.com', NULL, NULL, 'Custom carpentry and woodwork', 'PCAB-349', 'C', '2027-02-19', 'BP-349', 'Baguio', '2027-08-19', 'TIN-349', 'dti_glenda_fernandez5.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, 3, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(101, 250, NULL, NULL, 'Glenda Mercado Builders V', '2023-10-26', 9, 1, NULL, 'Mixed Services', 'Pinsao Road, Barangay Pinsao, Baguio City, Benguet 2600', 'glenda.mercado5@builders.com', NULL, NULL, 'Mixed construction and services', 'PCAB-350', 'AA', '2027-10-26', 'BP-350', 'Baguio', '2027-04-26', 'TIN-350', 'dti_glenda_mercado5.jpg', 'approved', '2026-02-03 06:20:00', 1, NULL, NULL, NULL, NULL, 11, '2026-03-11 16:00:00', '2026-03-11 16:00:00');

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
(18, 52, 251, 'representative', NULL, 'manager', 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(19, 53, 252, 'engineer', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(20, 54, 253, 'architect', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(21, 55, 254, 'representative', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(22, 56, 255, 'others', 'Site Supervisor', NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(23, 57, 256, 'manager', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(24, 58, 257, 'engineer', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(25, 59, 258, 'architect', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(26, 60, 259, 'representative', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(27, 61, 260, 'others', 'Quality Assurance', NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(28, 62, 261, 'manager', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(29, 63, 262, 'engineer', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(30, 64, 263, 'architect', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(31, 65, 264, 'representative', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(32, 66, 265, 'others', 'Safety Officer', NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(33, 67, 266, 'manager', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(34, 68, 267, 'engineer', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(35, 69, 268, 'architect', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(36, 70, 269, 'representative', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(37, 71, 270, 'others', 'Procurement Officer', NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(38, 72, 271, 'manager', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(39, 73, 272, 'engineer', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(40, 74, 273, 'architect', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(41, 75, 274, 'representative', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(42, 76, 275, 'others', 'Logistics Coordinator', NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(43, 77, 276, 'manager', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(44, 78, 277, 'engineer', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(45, 79, 278, 'architect', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(46, 80, 279, 'representative', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(47, 81, 280, 'others', 'Health & Safety Manager', NULL, 1, 0, NULL, NULL, NULL, '2026-03-11 16:00:00'),
(48, 52, 117, 'engineer', NULL, NULL, 1, 0, NULL, NULL, NULL, '2026-03-12 10:10:04');

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
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `is_suspended` tinyint(11) DEFAULT 0,
  `no_suspends` int(11) NOT NULL DEFAULT 0,
  `reason` varchar(255) DEFAULT NULL,
  `suspended_until` datetime DEFAULT NULL,
  `status` enum('active','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_admin_conversation` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`conversation_id`, `sender_id`, `receiver_id`, `is_suspended`, `no_suspends`, `reason`, `suspended_until`, `status`, `created_at`, `updated_at`, `is_admin_conversation`) VALUES
(1001001001, 1001, 1001, 0, 0, NULL, NULL, 'active', '2026-03-12 20:44:28', '2026-03-12 20:44:28', 1),
(1003001003, 1003, 1003, 0, 0, NULL, NULL, 'active', '2026-03-12 20:34:01', '2026-03-12 20:34:01', 1);

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
(1, 2003, 3007, 3003, 3, 9, 'Quality', 'Foundation Work Quality Dispute', NULL, 'The foundation and base slab work was rejected due to alleged quality issues. However, the contractor believes the work meets all specifications and industry standards. The rejection appears to be based on subjective assessment rather than objective measurements. We request a third-party inspection to verify the quality of work completed.', 'open', NULL, 'Request independent quality inspection and reconsideration of rejection', NULL, '2026-03-12 04:00:00', NULL),
(2, 2001, 3001, 3001, 1, 1, 'Payment', 'Payment Rejection - Check Number Discrepancy', NULL, 'Payment for site preparation and excavation work was rejected citing check number not matching records. The contractor submitted payment with check number CHK-2001-001 which was issued by the bank and is valid. The property owner appears to have incorrect records. We have bank confirmation of the check validity and request immediate payment approval.', 'open', NULL, 'Provide bank verification documents and approve payment', NULL, '2026-04-15 02:30:00', NULL),
(3, 2002, 3004, 3004, 2, 5, 'Payment', 'Cash Payment Documentation Dispute', NULL, 'The cash payment for demolition and cleanup work was rejected for lack of proper documentation. However, the contractor provided all required receipts and documentation at the time of payment. The property owner is requesting additional documentation that was not part of the original agreement. This appears to be an unreasonable demand and delays project completion.', 'open', NULL, 'Accept submitted documentation or specify exact requirements needed', NULL, '2026-01-20 03:00:00', NULL);

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
-- Table structure for table `downpayment_payments`
--

CREATE TABLE `downpayment_payments` (
  `dp_payment_id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `owner_id` int(10) UNSIGNED NOT NULL,
  `contractor_user_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_type` enum('cash','check','bank_transfer','online_payment') NOT NULL,
  `transaction_number` varchar(100) DEFAULT NULL,
  `receipt_photo` varchar(255) DEFAULT NULL,
  `transaction_date` date DEFAULT NULL,
  `payment_status` enum('submitted','approved','rejected','deleted') NOT NULL DEFAULT 'submitted',
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
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
(1, 1003001003, 0, 'dfsdfs', 0, 0, NULL, '2026-03-12 20:34:01', '2026-03-12 20:34:01'),
(2, 1003001003, 0, 'hi', 0, 0, NULL, '2026-03-12 20:41:12', '2026-03-12 20:41:12'),
(3, 1001001001, 0, 'aaaaa', 0, 0, NULL, '2026-03-12 20:44:28', '2026-03-12 20:44:28'),
(4, 1001001001, 0, 'dd', 0, 0, NULL, '2026-03-12 21:21:59', '2026-03-12 21:21:59');

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
(1, 2001, 52, 1, 'Foundation & Structural Work', 'Foundation laying and structural framework completion', 'in_progress', NULL, '2026-03-15 00:00:00', '2026-06-18 00:00:00', NULL, NULL, 'approved', NULL, '2026-03-12 16:01:15', '2026-03-12 10:22:31'),
(2, 2002, 55, 2, 'Office Renovation & Fit-out', 'Complete office renovation and interior fit-out', 'completed', NULL, '2026-01-10 00:00:00', '2026-03-10 00:00:00', NULL, NULL, 'approved', NULL, '2026-03-12 16:01:15', '2026-03-12 16:01:15'),
(3, 2003, 58, 3, 'Warehouse Construction Phase 1', 'First phase of warehouse construction', '', NULL, '2026-02-01 00:00:00', '2026-05-01 00:00:00', NULL, NULL, 'approved', NULL, '2026-03-12 16:01:15', '2026-03-12 16:01:15'),
(4, 2004, 62, 4, 'Agricultural Facility Setup', 'Setup of agricultural storage facility', '', NULL, '2026-02-15 00:00:00', '2026-04-15 00:00:00', NULL, NULL, 'approved', NULL, '2026-03-12 16:01:15', '2026-03-12 16:01:15'),
(5, 2005, 64, 5, 'Subdivision Development Phase 1', 'Development of first phase with 25 units', 'in_progress', NULL, '2026-03-20 00:00:00', '2026-09-20 00:00:00', NULL, NULL, 'approved', NULL, '2026-03-12 16:01:15', '2026-03-12 16:01:15'),
(6, 2021, 67, 6, 'Foundation & Structural Work', 'Foundation laying and structural framework completion', 'in_progress', NULL, '2026-03-15 00:00:00', '2026-06-15 00:00:00', NULL, NULL, 'approved', NULL, '2026-03-12 16:01:27', '2026-03-12 16:01:27'),
(7, 2022, 70, 7, 'Retail Center Construction', 'Construction of retail center with multiple shops', 'in_progress', NULL, '2026-03-10 00:00:00', '2026-07-10 00:00:00', NULL, NULL, 'approved', NULL, '2026-03-12 16:01:27', '2026-03-12 16:01:27'),
(8, 2024, 75, 8, 'Office Building Development', 'Development of modern office building', 'in_progress', NULL, '2026-03-18 00:00:00', '2026-08-18 00:00:00', NULL, NULL, 'approved', NULL, '2026-03-12 16:01:27', '2026-03-12 16:01:27');

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
  `changed_by` int(11) NOT NULL COMMENT 'user_id who triggered the change',
  `changed_at` datetime NOT NULL COMMENT 'When the change was applied',
  `change_reason` varchar(500) DEFAULT NULL COMMENT 'e.g. "Project update #2 approved"',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `milestone_date_histories`
--

INSERT INTO `milestone_date_histories` (`id`, `item_id`, `previous_date`, `new_date`, `extension_id`, `changed_by`, `changed_at`, `change_reason`, `created_at`, `updated_at`) VALUES
(1, 4, '2026-06-15 00:00:00', '2026-06-18 00:00:00', 1, 3001, '2026-03-12 18:22:31', 'I just need more time to fix everything.', '2026-03-12 10:22:31', '2026-03-12 10:22:31');

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
(1, 1, 1, 25.00, 'Site Preparation & Excavation', 'Site clearing and excavation work', 1500000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-04-15 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(2, 1, 2, 25.00, 'Foundation Laying', 'Foundation concrete pouring and curing', 1500000.00, NULL, 0.00, 'in_progress', NULL, NULL, '2026-05-15 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(3, 1, 3, 25.00, 'Structural Framework', 'Steel and concrete structural framework', 1500000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-06-01 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(4, 1, 4, 25.00, 'Ground Floor Completion', 'Ground floor slab and finishing', 1500000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-06-18 00:00:00', '2026-06-15 00:00:00', 1, 1, NULL, NULL, NULL),
(5, 2, 1, 33.33, 'Demolition & Cleanup', 'Remove old fixtures and cleanup', 833333.00, NULL, 0.00, 'completed', NULL, NULL, '2026-01-20 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(6, 2, 2, 33.33, 'Electrical & Plumbing Installation', 'New electrical and plumbing systems', 833333.00, NULL, 0.00, 'completed', NULL, NULL, '2026-02-15 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(7, 2, 3, 33.34, 'Interior Finishing & Painting', 'Walls, flooring, and final touches', 833334.00, NULL, 0.00, 'completed', NULL, NULL, '2026-03-10 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(8, 3, 1, 25.00, 'Site Preparation', 'Land clearing and leveling', 2250000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-02-15 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(9, 3, 2, 25.00, 'Foundation & Base Slab', 'Foundation work and concrete base', 2250000.00, NULL, 0.00, 'in_progress', NULL, NULL, '2026-03-15 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(10, 3, 3, 25.00, 'Walls & Roof Structure', 'Wall construction and roof framing', 2250000.00, NULL, 0.00, 'halt', NULL, NULL, '2026-04-15 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(11, 3, 4, 25.00, 'Interior & Finishing', 'Interior work and final finishing', 2250000.00, NULL, 0.00, 'halt', NULL, NULL, '2026-05-01 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(12, 4, 1, 50.00, 'Site Preparation & Foundation', 'Prepare site and lay foundation', 875000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-03-01 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(13, 4, 2, 50.00, 'Storage Structure & Equipment', 'Build storage structure and install equipment', 875000.00, NULL, 0.00, '', NULL, NULL, '2026-04-15 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(14, 5, 1, 20.00, 'Site Development & Infrastructure', 'Roads, utilities, and site infrastructure', 3500000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-04-20 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(15, 5, 2, 20.00, 'Foundation & Structural Work (Units 1-5)', 'Foundation and structure for first 5 units', 3500000.00, NULL, 0.00, 'in_progress', NULL, NULL, '2026-05-20 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(16, 5, 3, 20.00, 'Foundation & Structural Work (Units 6-15)', 'Foundation and structure for units 6-15', 3500000.00, NULL, 0.00, 'in_progress', NULL, NULL, '2026-06-20 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(17, 5, 4, 20.00, 'Interior & Finishing (Units 1-15)', 'Interior work and finishing for units 1-15', 3500000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-08-20 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(18, 5, 5, 20.00, 'Final Inspection & Handover', 'Final inspection and unit handover', 3500000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-09-20 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(19, 6, 1, 25.00, 'Site Preparation & Excavation', 'Site clearing and excavation work', 2000000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-04-15 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(20, 6, 2, 25.00, 'Foundation Laying', 'Foundation concrete pouring and curing', 2000000.00, NULL, 0.00, 'in_progress', NULL, NULL, '2026-05-15 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(21, 6, 3, 25.00, 'Structural Framework', 'Steel and concrete structural framework', 2000000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-06-01 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(22, 6, 4, 25.00, 'Ground Floor Completion', 'Ground floor slab and finishing', 2000000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-06-15 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(23, 7, 1, 33.33, 'Site Preparation & Foundation', 'Site preparation and foundation work', 2000000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-04-10 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(24, 7, 2, 33.33, 'Structural & Walls Construction', 'Structural framework and wall construction', 2000000.00, NULL, 0.00, 'in_progress', NULL, NULL, '2026-05-20 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(25, 7, 3, 33.34, 'Interior Finishing & Retail Setup', 'Interior finishing and retail space setup', 2000000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-07-10 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(26, 8, 1, 20.00, 'Site Development & Infrastructure', 'Site development and utility infrastructure', 1900000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-04-18 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(27, 8, 2, 20.00, 'Foundation & Ground Floor', 'Foundation and ground floor construction', 1900000.00, NULL, 0.00, 'in_progress', NULL, NULL, '2026-05-18 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(28, 8, 3, 20.00, 'Upper Floors & Structural Work', 'Upper floors and structural completion', 1900000.00, NULL, 0.00, 'in_progress', NULL, NULL, '2026-06-18 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(29, 8, 4, 20.00, 'MEP Installation & Interior', 'Mechanical, electrical, plumbing and interior work', 1900000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-07-18 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(30, 8, 5, 20.00, 'Final Finishing & Handover', 'Final finishing and project handover', 1900000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-08-18 00:00:00', NULL, 0, 0, NULL, NULL, NULL);

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
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `milestone_payments`
--

CREATE TABLE `milestone_payments` (
  `payment_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `contractor_id` int(11) NOT NULL,
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

INSERT INTO `milestone_payments` (`payment_id`, `item_id`, `project_id`, `owner_id`, `contractor_id`, `amount`, `payment_type`, `transaction_number`, `receipt_photo`, `transaction_date`, `payment_status`, `reason`, `updated_at`) VALUES
(1, 1, 2001, 251, 52, 1500000.00, 'bank_transfer', 'TXN-2001-001', 'receipt_2001_001.jpg', '2026-04-15', 'approved', NULL, '2026-04-15 14:30:00'),
(2, 5, 2002, 252, 55, 833333.00, 'bank_transfer', 'TXN-2002-001', 'receipt_2002_001.jpg', '2026-01-20', 'approved', NULL, '2026-01-20 16:00:00'),
(3, 6, 2002, 252, 55, 833333.00, 'bank_transfer', 'TXN-2002-002', 'receipt_2002_002.jpg', '2026-02-15', 'approved', NULL, '2026-02-15 16:45:00'),
(4, 7, 2002, 252, 55, 833334.00, 'bank_transfer', 'TXN-2002-003', 'receipt_2002_003.jpg', '2026-03-10', 'approved', NULL, '2026-03-10 17:30:00'),
(5, 8, 2003, 253, 58, 2250000.00, 'bank_transfer', 'TXN-2003-001', 'receipt_2003_001.jpg', '2026-02-15', 'approved', NULL, '2026-02-15 14:15:00'),
(6, 12, 2004, 254, 62, 875000.00, 'bank_transfer', 'TXN-2004-001', 'receipt_2004_001.jpg', '2026-03-01', 'approved', NULL, '2026-03-01 13:00:00'),
(7, 14, 2005, 255, 64, 3500000.00, 'bank_transfer', 'TXN-2005-001', 'receipt_2005_001.jpg', '2026-04-20', 'approved', NULL, '2026-04-20 14:45:00'),
(8, 19, 2021, 271, 67, 2000000.00, 'bank_transfer', 'TXN-2021-001', 'receipt_2021_001.jpg', '2026-04-15', 'approved', NULL, '2026-04-15 13:50:00'),
(9, 23, 2022, 272, 70, 2000000.00, 'bank_transfer', 'TXN-2022-001', 'receipt_2022_001.jpg', '2026-04-10', 'approved', NULL, '2026-04-10 13:15:00'),
(10, 26, 2024, 274, 75, 1900000.00, 'bank_transfer', 'TXN-2024-001', 'receipt_2024_001.jpg', '2026-04-18', 'approved', NULL, '2026-04-18 15:00:00'),
(11, 1, 2001, 251, 52, 1500000.00, 'check', 'CHK-2001-001', 'receipt_2001_rejected.jpg', '2026-04-14', 'rejected', 'Check number not matching records', '2026-04-15 10:00:00'),
(12, 5, 2002, 252, 55, 833333.00, 'cash', 'CASH-2002-001', 'receipt_2002_rejected.jpg', '2026-01-19', 'rejected', 'Cash payment without proper documentation', '2026-01-20 09:30:00'),
(13, 8, 2003, 253, 58, 2250000.00, 'online_payment', 'ONLINE-2003-001', 'receipt_2003_rejected.jpg', '2026-02-14', 'rejected', 'Transaction ID not verified', '2026-02-15 08:45:00'),
(14, 14, 2005, 255, 64, 3500000.00, 'check', 'CHK-2005-001', 'receipt_2005_rejected.jpg', '2026-04-19', 'rejected', 'Insufficient funds in account', '2026-04-20 09:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `type` enum('Milestone Update','Bid Status','Payment Reminder','Project Alert','Progress Update','Dispute Update','Team Update','Payment Status','Admin Announcement') NOT NULL,
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
(1, 2001, 'The timeline for \"Modern Residential Complex\" has been extended by 3 days by admin.', 'Project Timeline Extended', 'Project Alert', 0, 'App', 'high', NULL, NULL, NULL, NULL, '2026-03-12 10:22:31'),
(2, 3001, 'The timeline for \"Modern Residential Complex\" has been extended by 3 days by admin.', 'Project Timeline Extended', 'Project Alert', 0, 'App', 'high', NULL, NULL, NULL, NULL, '2026-03-12 10:22:31');

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
  `project_id` int(11) NOT NULL,
  `milestone_id` int(11) NOT NULL,
  `source_item_id` int(11) NOT NULL,
  `target_item_id` int(11) DEFAULT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `adjustment_type` enum('overpayment','underpayment') NOT NULL COMMENT 'What kind of adjustment',
  `original_required` decimal(12,2) NOT NULL COMMENT 'Original required amount of source item',
  `total_paid` decimal(12,2) NOT NULL COMMENT 'Total approved payments on source item after this payment',
  `adjustment_amount` decimal(12,2) NOT NULL COMMENT 'The excess (overpay) or shortfall (underpay) amount',
  `target_original_cost` decimal(12,2) DEFAULT NULL COMMENT 'Target item original cost before adjustment',
  `target_adjusted_cost` decimal(12,2) DEFAULT NULL COMMENT 'Target item cost after adjustment',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 2001, 52, 'downpayment', 6000000.00, 1500000.00, 1, '2026-03-12 16:01:15', '2026-03-12 16:01:15'),
(2, 2002, 55, 'full_payment', 2500000.00, 0.00, 1, '2026-03-12 16:01:15', '2026-03-12 16:01:15'),
(3, 2003, 58, 'downpayment', 9000000.00, 2250000.00, 1, '2026-03-12 16:01:15', '2026-03-12 16:01:15'),
(4, 2004, 62, 'full_payment', 1750000.00, 0.00, 1, '2026-03-12 16:01:15', '2026-03-12 16:01:15'),
(5, 2005, 64, 'downpayment', 17500000.00, 4375000.00, 1, '2026-03-12 16:01:15', '2026-03-12 16:01:15'),
(6, 2021, 67, 'downpayment', 8000000.00, 2000000.00, 1, '2026-03-12 16:01:27', '2026-03-12 16:01:27'),
(7, 2022, 70, 'full_payment', 6000000.00, 0.00, 1, '2026-03-12 16:01:27', '2026-03-12 16:01:27'),
(8, 2024, 75, 'downpayment', 9500000.00, 2375000.00, 1, '2026-03-12 16:01:27', '2026-03-12 16:01:27');

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
  `approved_by` varchar(20) DEFAULT NULL,
  `expiration_date` timestamp NULL DEFAULT NULL,
  `payment_type` varchar(255) DEFAULT NULL,
  `deactivation_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `platform_payments`
--

INSERT INTO `platform_payments` (`platform_payment_id`, `subscriptionPlanId`, `project_id`, `contractor_id`, `owner_id`, `amount`, `transaction_number`, `transaction_date`, `is_approved`, `is_cancelled`, `approved_by`, `expiration_date`, `payment_type`, `deactivation_reason`) VALUES
(1, 1, NULL, 52, NULL, 199900.00, 'TXN-SUB-001', '2026-03-01 00:00:00', 1, 0, 'ADMIN-1', '2026-04-01 00:00:00', 'bank_transfer', NULL),
(2, 1, NULL, 55, NULL, 199900.00, 'TXN-SUB-002', '2026-03-02 01:30:00', 1, 0, 'ADMIN-1', '2026-04-02 01:30:00', 'bank_transfer', NULL),
(3, 1, NULL, 58, NULL, 199900.00, 'TXN-SUB-003', '2026-03-03 02:15:00', 1, 0, 'ADMIN-1', '2026-04-03 02:15:00', 'bank_transfer', NULL),
(4, 1, NULL, 62, NULL, 199900.00, 'TXN-SUB-004', '2026-03-04 03:00:00', 1, 0, 'ADMIN-1', '2026-04-04 03:00:00', 'bank_transfer', NULL),
(5, 2, NULL, 64, NULL, 149900.00, 'TXN-SUB-005', '2026-03-05 00:30:00', 1, 0, 'ADMIN-1', '2026-04-05 00:30:00', 'bank_transfer', NULL),
(6, 2, NULL, 67, NULL, 149900.00, 'TXN-SUB-006', '2026-03-06 01:15:00', 1, 0, 'ADMIN-1', '2026-04-06 01:15:00', 'bank_transfer', NULL),
(7, 2, NULL, 70, NULL, 149900.00, 'TXN-SUB-007', '2026-03-07 02:00:00', 1, 0, 'ADMIN-1', '2026-04-07 02:00:00', 'bank_transfer', NULL),
(8, 2, NULL, 75, NULL, 149900.00, 'TXN-SUB-008', '2026-03-08 03:30:00', 1, 0, 'ADMIN-1', '2026-04-08 03:30:00', 'bank_transfer', NULL),
(9, 2, NULL, 78, NULL, 149900.00, 'TXN-SUB-009', '2026-03-09 00:45:00', 1, 0, 'ADMIN-1', '2026-04-09 00:45:00', 'bank_transfer', NULL),
(10, 3, NULL, 81, NULL, 99900.00, 'TXN-SUB-010', '2026-03-10 01:00:00', 1, 0, 'ADMIN-1', '2026-04-10 01:00:00', 'bank_transfer', NULL),
(11, 3, NULL, 52, NULL, 99900.00, 'TXN-SUB-011', '2026-03-11 02:30:00', 1, 0, 'ADMIN-1', '2026-04-11 02:30:00', 'bank_transfer', NULL),
(12, 3, NULL, 53, NULL, 99900.00, 'TXN-SUB-012', '2026-03-12 03:15:00', 1, 0, 'ADMIN-1', '2026-04-12 03:15:00', 'bank_transfer', NULL),
(13, 3, NULL, 54, NULL, 99900.00, 'TXN-SUB-013', '2026-03-13 00:30:00', 1, 0, 'ADMIN-1', '2026-04-13 00:30:00', 'bank_transfer', NULL),
(14, 3, NULL, 56, NULL, 99900.00, 'TXN-SUB-014', '2026-03-14 01:45:00', 1, 0, 'ADMIN-1', '2026-04-14 01:45:00', 'bank_transfer', NULL),
(15, 3, NULL, 59, NULL, 99900.00, 'TXN-SUB-015', '2026-03-15 02:20:00', 1, 0, 'ADMIN-1', '2026-04-15 02:20:00', 'bank_transfer', NULL),
(16, 4, 2006, NULL, 256, 4900.00, 'TXN-BOOST-001', '2026-03-05 00:00:00', 1, 0, 'ADMIN-1', '2026-03-12 00:00:00', 'bank_transfer', NULL),
(17, 4, 2007, NULL, 257, 4900.00, 'TXN-BOOST-002', '2026-03-06 01:30:00', 1, 0, 'ADMIN-1', '2026-03-13 01:30:00', 'bank_transfer', NULL),
(18, 4, 2008, NULL, 258, 4900.00, 'TXN-BOOST-003', '2026-03-07 02:15:00', 1, 0, 'ADMIN-1', '2026-03-14 02:15:00', 'bank_transfer', NULL),
(19, 4, 2009, NULL, 259, 4900.00, 'TXN-BOOST-004', '2026-03-08 03:00:00', 1, 0, 'ADMIN-1', '2026-03-15 03:00:00', 'bank_transfer', NULL),
(20, 4, 2010, NULL, 260, 4900.00, 'TXN-BOOST-005', '2026-03-09 00:30:00', 1, 0, 'ADMIN-1', '2026-03-16 00:30:00', 'bank_transfer', NULL),
(21, 4, 2011, NULL, 261, 4900.00, 'TXN-BOOST-006', '2026-03-10 01:15:00', 1, 0, 'ADMIN-1', '2026-03-17 01:15:00', 'bank_transfer', NULL),
(22, 4, 2012, NULL, 262, 4900.00, 'TXN-BOOST-007', '2026-03-11 02:00:00', 1, 0, 'ADMIN-1', '2026-03-18 02:00:00', 'bank_transfer', NULL),
(23, 4, 2013, NULL, 263, 4900.00, 'TXN-BOOST-008', '2026-03-12 03:30:00', 1, 0, 'ADMIN-1', '2026-03-19 03:30:00', 'bank_transfer', NULL),
(24, 4, 2014, NULL, 264, 4900.00, 'TXN-BOOST-009', '2026-03-13 00:45:00', 1, 0, 'ADMIN-1', '2026-03-20 00:45:00', 'bank_transfer', NULL),
(25, 4, 2015, NULL, 265, 4900.00, 'TXN-BOOST-010', '2026-03-14 01:30:00', 1, 0, 'ADMIN-1', '2026-03-21 01:30:00', 'bank_transfer', NULL),
(26, 4, 2016, NULL, 266, 4900.00, 'TXN-BOOST-011', '2026-03-15 02:15:00', 1, 0, 'ADMIN-1', '2026-03-22 02:15:00', 'bank_transfer', NULL),
(27, 4, 2017, NULL, 267, 4900.00, 'TXN-BOOST-012', '2026-03-16 03:00:00', 1, 0, 'ADMIN-1', '2026-03-23 03:00:00', 'bank_transfer', NULL),
(28, 4, 2018, NULL, 268, 4900.00, 'TXN-BOOST-013', '2026-03-17 00:30:00', 1, 0, 'ADMIN-1', '2026-03-24 00:30:00', 'bank_transfer', NULL),
(29, 4, 2019, NULL, 269, 4900.00, 'TXN-BOOST-014', '2026-03-18 01:15:00', 1, 0, 'ADMIN-1', '2026-03-25 01:15:00', 'bank_transfer', NULL),
(30, 4, 2020, NULL, 270, 4900.00, 'TXN-BOOST-015', '2026-03-19 02:00:00', 1, 0, 'ADMIN-1', '2026-03-26 02:00:00', 'bank_transfer', NULL),
(31, 4, 2023, NULL, 273, 4900.00, 'TXN-BOOST-016', '2026-03-20 03:30:00', 1, 0, 'ADMIN-1', '2026-03-27 03:30:00', 'bank_transfer', NULL),
(32, 4, 2025, NULL, 275, 4900.00, 'TXN-BOOST-017', '2026-03-21 00:45:00', 1, 0, 'ADMIN-1', '2026-03-28 00:45:00', 'bank_transfer', NULL),
(33, 1, NULL, 65, NULL, 199900.00, 'TXN-SUB-016', '2026-03-16 00:00:00', 1, 0, 'ADMIN-1', '2026-04-16 00:00:00', 'bank_transfer', NULL),
(34, 1, NULL, 68, NULL, 199900.00, 'TXN-SUB-017', '2026-03-17 01:30:00', 1, 0, 'ADMIN-1', '2026-04-17 01:30:00', 'bank_transfer', NULL),
(35, 1, NULL, 71, NULL, 199900.00, 'TXN-SUB-018', '2026-03-18 02:15:00', 1, 0, 'ADMIN-1', '2026-04-18 02:15:00', 'bank_transfer', NULL),
(36, 1, NULL, 74, NULL, 199900.00, 'TXN-SUB-019', '2026-03-19 03:00:00', 1, 0, 'ADMIN-1', '2026-04-19 03:00:00', 'bank_transfer', NULL),
(37, 1, NULL, 77, NULL, 199900.00, 'TXN-SUB-020', '2026-03-20 00:30:00', 1, 0, 'ADMIN-1', '2026-04-20 00:30:00', 'bank_transfer', NULL),
(38, 2, NULL, 60, NULL, 149900.00, 'TXN-SUB-021', '2026-03-21 01:15:00', 1, 0, 'ADMIN-1', '2026-04-21 01:15:00', 'bank_transfer', NULL),
(39, 2, NULL, 63, NULL, 149900.00, 'TXN-SUB-022', '2026-03-22 02:00:00', 1, 0, 'ADMIN-1', '2026-04-22 02:00:00', 'bank_transfer', NULL),
(40, 2, NULL, 66, NULL, 149900.00, 'TXN-SUB-023', '2026-03-23 03:30:00', 1, 0, 'ADMIN-1', '2026-04-23 03:30:00', 'bank_transfer', NULL),
(41, 2, NULL, 69, NULL, 149900.00, 'TXN-SUB-024', '2026-03-24 00:45:00', 1, 0, 'ADMIN-1', '2026-04-24 00:45:00', 'bank_transfer', NULL),
(42, 2, NULL, 72, NULL, 149900.00, 'TXN-SUB-025', '2026-03-25 01:30:00', 1, 0, 'ADMIN-1', '2026-04-25 01:30:00', 'bank_transfer', NULL),
(43, 2, NULL, 76, NULL, 149900.00, 'TXN-SUB-026', '2026-03-26 02:15:00', 1, 0, 'ADMIN-1', '2026-04-26 02:15:00', 'bank_transfer', NULL),
(44, 2, NULL, 79, NULL, 149900.00, 'TXN-SUB-027', '2026-03-27 03:00:00', 1, 0, 'ADMIN-1', '2026-04-27 03:00:00', 'bank_transfer', NULL),
(45, 3, NULL, 57, NULL, 99900.00, 'TXN-SUB-028', '2026-03-28 00:30:00', 1, 0, 'ADMIN-1', '2026-04-28 00:30:00', 'bank_transfer', NULL),
(46, 3, NULL, 61, NULL, 99900.00, 'TXN-SUB-029', '2026-03-29 01:15:00', 1, 0, 'ADMIN-1', '2026-04-29 01:15:00', 'bank_transfer', NULL),
(47, 3, NULL, 73, NULL, 99900.00, 'TXN-SUB-030', '2026-03-30 02:00:00', 1, 0, 'ADMIN-1', '2026-04-30 02:00:00', 'bank_transfer', NULL),
(48, 3, NULL, 80, NULL, 99900.00, 'TXN-SUB-031', '2026-03-31 03:30:00', 1, 0, 'ADMIN-1', '2026-05-01 03:30:00', 'bank_transfer', NULL),
(49, 3, NULL, 82, NULL, 99900.00, 'TXN-SUB-032', '2026-04-01 00:45:00', 1, 0, 'ADMIN-1', '2026-05-02 00:45:00', 'bank_transfer', NULL),
(50, 3, NULL, 85, NULL, 99900.00, 'TXN-SUB-033', '2026-04-02 01:30:00', 1, 0, 'ADMIN-1', '2026-05-03 01:30:00', 'bank_transfer', NULL),
(51, 3, NULL, 88, NULL, 99900.00, 'TXN-SUB-034', '2026-04-03 02:15:00', 1, 0, 'ADMIN-1', '2026-05-04 02:15:00', 'bank_transfer', NULL),
(52, 3, NULL, 91, NULL, 99900.00, 'TXN-SUB-035', '2026-04-04 03:00:00', 1, 0, 'ADMIN-1', '2026-05-05 03:00:00', 'bank_transfer', NULL),
(53, 3, NULL, 94, NULL, 99900.00, 'TXN-SUB-036', '2026-04-05 00:30:00', 1, 0, 'ADMIN-1', '2026-05-06 00:30:00', 'bank_transfer', NULL),
(54, 3, NULL, 97, NULL, 99900.00, 'TXN-SUB-037', '2026-04-06 01:15:00', 1, 0, 'ADMIN-1', '2026-05-07 01:15:00', 'bank_transfer', NULL),
(55, 3, NULL, 100, NULL, 99900.00, 'TXN-SUB-038', '2026-04-07 02:00:00', 1, 0, 'ADMIN-1', '2026-05-08 02:00:00', 'bank_transfer', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `post_reports`
--

CREATE TABLE `post_reports` (
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `reporter_user_id` int(11) NOT NULL,
  `post_type` enum('project','showcase') NOT NULL,
  `post_id` int(11) NOT NULL,
  `reason` varchar(120) NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','under_review','resolved','dismissed') NOT NULL DEFAULT 'pending',
  `reviewed_by_user_id` int(11) DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `progress`
--

CREATE TABLE `progress` (
  `progress_id` int(11) NOT NULL,
  `milestone_item_id` int(11) NOT NULL,
  `submitted_by_owner_id` int(11) DEFAULT NULL,
  `purpose` varchar(255) NOT NULL,
  `progress_status` enum('submitted','approved','rejected','deleted') DEFAULT 'submitted',
  `delete_reason` text DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `progress`
--

INSERT INTO `progress` (`progress_id`, `milestone_item_id`, `submitted_by_owner_id`, `purpose`, `progress_status`, `delete_reason`, `submitted_at`, `updated_at`) VALUES
(1, 1, 3001, 'Site preparation and excavation completed as per schedule', 'approved', NULL, '2026-04-15 02:30:00', '2026-04-15 06:00:00'),
(2, 2, 3001, 'Foundation laying in progress, 60% complete', 'submitted', NULL, '2026-05-10 01:15:00', NULL),
(3, 5, 3004, 'Demolition and cleanup completed successfully', 'approved', NULL, '2026-01-20 03:00:00', '2026-01-20 07:30:00'),
(4, 6, 3004, 'Electrical and plumbing systems installed and tested', 'approved', NULL, '2026-02-15 00:45:00', '2026-02-15 08:20:00'),
(5, 7, 3004, 'Interior finishing and painting completed', 'approved', NULL, '2026-03-10 02:00:00', '2026-03-10 09:00:00'),
(6, 8, 3007, 'Site preparation and leveling completed', 'approved', NULL, '2026-02-15 01:30:00', '2026-02-15 05:45:00'),
(7, 9, 3007, 'Foundation and base slab work in progress, 50% complete', 'submitted', NULL, '2026-03-15 02:15:00', NULL),
(8, 12, 3011, 'Site preparation and foundation completed', 'approved', NULL, '2026-03-01 00:00:00', '2026-03-01 04:30:00'),
(9, 14, 3013, 'Site development and infrastructure completed', 'approved', NULL, '2026-04-20 01:00:00', '2026-04-20 06:15:00'),
(10, 15, 3013, 'Foundation and structural work for units 1-5 in progress, 70% complete', 'submitted', NULL, '2026-05-20 02:30:00', NULL),
(11, 16, 3013, 'Foundation and structural work for units 6-15 in progress, 40% complete', 'submitted', NULL, '2026-06-15 03:00:00', NULL),
(12, 19, 3016, 'Site preparation and excavation completed', 'approved', NULL, '2026-04-15 01:45:00', '2026-04-15 05:20:00'),
(13, 20, 3016, 'Foundation laying in progress, 55% complete', 'submitted', NULL, '2026-05-15 02:00:00', NULL),
(14, 23, 3019, 'Site preparation and foundation work completed', 'approved', NULL, '2026-04-10 00:30:00', '2026-04-10 04:45:00'),
(15, 24, 3019, 'Structural and walls construction in progress, 65% complete', 'submitted', NULL, '2026-05-20 01:15:00', NULL),
(16, 26, 3024, 'Site development and infrastructure completed', 'approved', NULL, '2026-04-18 02:00:00', '2026-04-18 06:30:00'),
(17, 27, 3024, 'Foundation and ground floor construction in progress, 50% complete', 'submitted', NULL, '2026-05-18 01:30:00', NULL),
(18, 28, 3024, 'Upper floors and structural work in progress, 35% complete', 'submitted', NULL, '2026-06-18 02:45:00', NULL),
(19, 9, 3007, 'Foundation and base slab work - REJECTED due to quality issues', 'rejected', NULL, '2026-03-10 00:00:00', '2026-03-12 03:30:00');

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
  `project_status` enum('open','bidding_closed','in_progress','completed','terminated','deleted_post','halt','deleted') DEFAULT 'open',
  `previous_status` varchar(50) DEFAULT NULL,
  `stat_reason` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `is_highlighted` tinyint(1) NOT NULL DEFAULT 0,
  `highlighted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`project_id`, `relationship_id`, `project_title`, `project_description`, `project_location`, `budget_range_min`, `budget_range_max`, `lot_size`, `floor_area`, `property_type`, `type_id`, `if_others_ctype`, `to_finish`, `project_status`, `previous_status`, `stat_reason`, `remarks`, `is_highlighted`, `highlighted_at`) VALUES
(2001, 2001, 'Modern Residential Complex', 'Construction of a 3-storey residential building with modern amenities', 'Tetuan, Poblacion, Mankayan, Benguet 2600', 5000000.00, 7000000.00, 500, 400, 'Residential', 1, NULL, 60, 'in_progress', NULL, NULL, NULL, 0, NULL),
(2002, 2002, 'Commercial Office Space', 'Renovation and fit-out of commercial office space', 'Magsaysay, Barangay Poblacion, Baguio City, Benguet 2600', 2000000.00, 3000000.00, 200, 180, 'Commercial', 2, NULL, NULL, 'completed', NULL, NULL, NULL, 0, NULL),
(2003, 2003, 'Industrial Warehouse', 'Construction of industrial warehouse facility', 'Session Road, Barangay Asin, Baguio City, Benguet 2600', 8000000.00, 10000000.00, 1000, 800, 'Industrial', 3, NULL, 45, 'halt', NULL, NULL, NULL, 0, NULL),
(2004, 2004, 'Agricultural Storage Facility', 'Storage facility for agricultural products', 'Burnham Road, Barangay Irisan, Baguio City, Benguet 2600', 1500000.00, 2000000.00, 300, 250, 'Agricultural', 4, NULL, NULL, 'terminated', NULL, NULL, NULL, 0, NULL),
(2005, 2005, 'Residential Subdivision Phase 1', 'Development of residential subdivision with 50 units', 'Marcos Highway, Barangay Loakan, Baguio City, Benguet 2600', 15000000.00, 20000000.00, 2000, 1500, 'Residential', 1, NULL, 75, 'in_progress', NULL, NULL, NULL, 0, NULL),
(2006, 2006, 'Luxury Residential Mansion', 'Construction of luxury residential mansion with pool and garden', 'Naguilian Road, Barangay Pinsao, Baguio City, Benguet 2600', 10000000.00, 15000000.00, 800, 600, 'Residential', 1, NULL, 90, 'open', NULL, NULL, NULL, 0, NULL),
(2007, 2007, 'Shopping Mall Development', 'Development of modern shopping mall with retail spaces', 'Bokawkan Road, Barangay Cabayan, Baguio City, Benguet 2600', 50000000.00, 75000000.00, 5000, 4000, 'Commercial', 2, NULL, 120, 'bidding_closed', NULL, NULL, NULL, 0, NULL),
(2008, 2008, 'Residential Condo Tower', 'High-rise residential condominium with 200 units', 'Asin Road, Barangay Asin, Baguio City, Benguet 2600', 30000000.00, 50000000.00, 3000, 2500, 'Residential', 1, NULL, 100, 'open', NULL, NULL, NULL, 0, NULL),
(2009, 2009, 'Medical Center Complex', 'Construction of modern medical center with hospital facilities', 'Outlook Drive, Barangay Outlook, Baguio City, Benguet 2600', 20000000.00, 30000000.00, 2000, 1800, 'Commercial', 2, NULL, 110, 'bidding_closed', NULL, NULL, NULL, 0, NULL),
(2010, 2010, 'Educational Institution Building', 'Construction of school building with classrooms and facilities', 'Mines View Road, Barangay Mines View, Baguio City, Benguet 2600', 8000000.00, 12000000.00, 1200, 1000, 'Commercial', 2, NULL, 80, 'open', NULL, NULL, NULL, 0, NULL),
(2011, 2011, 'Residential Townhouse Development', 'Development of 20-unit townhouse complex with modern design', 'Trancoville, Barangay Trancoville, Baguio City, Benguet 2600', 6000000.00, 8000000.00, 600, 500, 'Residential', 1, NULL, 85, 'open', NULL, NULL, NULL, 0, NULL),
(2012, 2012, 'Community Center Building', 'Construction of multi-purpose community center with facilities', 'Pacdal Road, Barangay Pacdal, Baguio City, Benguet 2600', 3000000.00, 4000000.00, 400, 350, 'Commercial', 2, NULL, 70, 'open', NULL, NULL, NULL, 0, NULL),
(2013, 2013, 'Retail Shopping Complex', 'Modern retail shopping complex with parking facilities', 'Inya Road, Barangay Inya, Baguio City, Benguet 2600', 12000000.00, 16000000.00, 1500, 1200, 'Commercial', 2, NULL, 95, 'open', NULL, NULL, NULL, 0, NULL),
(2014, 2014, 'Agricultural Processing Plant', 'Processing facility for agricultural products', 'Kisad Road, Barangay Kisad, Baguio City, Benguet 2600', 4000000.00, 5000000.00, 800, 600, 'Agricultural', 4, NULL, 65, 'open', NULL, NULL, NULL, 0, NULL),
(2015, 2015, 'Industrial Manufacturing Facility', 'Manufacturing facility with modern equipment', 'Chanum Road, Barangay Chanum, Baguio City, Benguet 2600', 18000000.00, 25000000.00, 2500, 2000, 'Industrial', 3, NULL, 105, 'open', NULL, NULL, NULL, 0, NULL),
(2016, 2016, 'Residential Estate Project', 'Large residential estate development', 'Shuntug Road, Barangay Shuntug, Baguio City, Benguet 2600', 25000000.00, 35000000.00, 3000, 2500, 'Residential', 1, NULL, NULL, 'deleted_post', NULL, NULL, NULL, 0, NULL),
(2017, 2017, 'Commercial Office Tower', 'High-rise commercial office building', 'Loakan Road, Barangay Loakan, Baguio City, Benguet 2600', 40000000.00, 60000000.00, 4000, 3500, 'Commercial', 2, NULL, NULL, 'deleted_post', NULL, NULL, NULL, 0, NULL),
(2018, 2018, 'Industrial Complex Development', 'Large industrial complex with multiple facilities', 'Pinsao Road, Barangay Pinsao, Baguio City, Benguet 2600', 50000000.00, 75000000.00, 5000, 4000, 'Industrial', 3, NULL, NULL, 'deleted_post', NULL, NULL, NULL, 0, NULL),
(2019, 2019, 'Agricultural Farm Development', 'Large-scale agricultural farm with infrastructure', 'Asin Road, Barangay Asin, Baguio City, Benguet 2600', 8000000.00, 12000000.00, 2000, 500, 'Agricultural', 4, NULL, NULL, 'deleted_post', NULL, NULL, NULL, 0, NULL),
(2020, 2020, 'Mixed-Use Development Project', 'Mixed residential and commercial development', 'Outlook Drive, Barangay Outlook, Baguio City, Benguet 2600', 35000000.00, 50000000.00, 3500, 3000, 'Residential', 1, NULL, NULL, 'deleted_post', NULL, NULL, NULL, 0, NULL),
(2021, 2021, 'Residential Apartment Complex', 'Modern apartment complex with amenities', 'Mines View Road, Barangay Mines View, Baguio City, Benguet 2600', 7000000.00, 9000000.00, 700, 600, 'Residential', 1, NULL, 55, 'in_progress', NULL, NULL, NULL, 0, NULL),
(2022, 2022, 'Commercial Retail Center', 'Retail center with multiple shops and restaurants', 'Trancoville, Barangay Trancoville, Baguio City, Benguet 2600', 5000000.00, 7000000.00, 500, 450, 'Commercial', 2, NULL, 40, 'in_progress', NULL, NULL, NULL, 0, NULL),
(2023, 2023, 'Residential Subdivision Phase 2', 'Second phase of residential subdivision with 40 units', 'Pacdal Road, Barangay Pacdal, Baguio City, Benguet 2600', 12000000.00, 16000000.00, 1800, 1400, 'Residential', 1, NULL, 88, 'bidding_closed', NULL, NULL, NULL, 0, NULL),
(2024, 2024, 'Office Building Development', 'Modern office building with conference facilities', 'Inya Road, Barangay Inya, Baguio City, Benguet 2600', 8000000.00, 11000000.00, 900, 800, 'Commercial', 2, NULL, 72, 'in_progress', NULL, NULL, NULL, 0, NULL),
(2025, 2025, 'Warehouse and Logistics Center', 'Large warehouse facility for logistics operations', 'Kisad Road, Barangay Kisad, Baguio City, Benguet 2600', 6000000.00, 8000000.00, 1200, 1000, 'Industrial', 3, NULL, 50, 'bidding_closed', NULL, NULL, NULL, 0, NULL);

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
  `bidding_due` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_relationships`
--

INSERT INTO `project_relationships` (`rel_id`, `owner_id`, `selected_contractor_id`, `project_post_status`, `admin_reason`, `bidding_due`, `created_at`, `updated_at`) VALUES
(2001, 251, 52, 'approved', NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2002, 252, 55, 'approved', NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2003, 253, 58, 'approved', NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2004, 254, 62, 'approved', NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2005, 255, 64, 'approved', NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2006, 256, NULL, 'approved', NULL, '2026-04-15', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2007, 257, NULL, 'approved', NULL, '2026-04-20', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2008, 258, NULL, 'approved', NULL, '2026-04-25', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2009, 259, NULL, 'approved', NULL, '2026-05-01', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2010, 260, NULL, 'approved', NULL, '2026-05-05', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2011, 261, NULL, 'under_review', NULL, '2026-04-10', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2012, 262, NULL, 'under_review', NULL, '2026-04-12', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2013, 263, NULL, 'under_review', NULL, '2026-04-15', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2014, 264, NULL, 'under_review', NULL, '2026-04-18', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2015, 265, NULL, 'under_review', NULL, '2026-04-20', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2016, 266, NULL, 'rejected', 'Incomplete documentation and missing permits', NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2017, 267, NULL, 'rejected', 'Project location not compliant with zoning regulations', NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2018, 268, NULL, 'rejected', 'Budget exceeds maximum allowable amount', NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2019, 269, NULL, 'rejected', 'Environmental impact assessment required', NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2020, 270, NULL, 'rejected', 'Owner verification failed', NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2021, 271, 67, 'due', NULL, '2026-02-28', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2022, 272, 70, 'due', NULL, '2026-03-01', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2023, 273, NULL, 'due', NULL, '2026-03-05', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2024, 274, 75, 'due', NULL, '2026-03-08', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2025, 275, NULL, 'due', NULL, '2026-03-10', '2026-03-11 16:00:00', '2026-03-11 16:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `project_updates`
--

CREATE TABLE `project_updates` (
  `extension_id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `contractor_user_id` int(11) NOT NULL COMMENT 'user_id of the submitting contractor',
  `owner_user_id` int(11) NOT NULL COMMENT 'user_id of the property owner',
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
(1, 2001, 2001, 3001, '2026-06-15', '2026-06-18', 'I just need more time to fix everything.', 6000000.00, 6000000.00, 'none', 0, NULL, NULL, NULL, 'approved', 'Admin override extension', NULL, '2026-03-12 10:22:31', '2026-03-12 10:22:31', '2026-03-12 10:22:31');

-- --------------------------------------------------------

--
-- Table structure for table `property_owners`
--

CREATE TABLE `property_owners` (
  `owner_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `cover_photo` varchar(255) DEFAULT NULL,
  `address` varchar(500) NOT NULL,
  `date_of_birth` date NOT NULL,
  `age` int(11) NOT NULL,
  `occupation_id` int(11) DEFAULT NULL,
  `occupation_other` varchar(200) DEFAULT NULL,
  `valid_id_id` int(11) DEFAULT NULL,
  `valid_id_photo` varchar(255) DEFAULT NULL,
  `valid_id_back_photo` varchar(255) NOT NULL,
  `police_clearance` varchar(255) NOT NULL,
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

INSERT INTO `property_owners` (`owner_id`, `user_id`, `profile_pic`, `cover_photo`, `address`, `date_of_birth`, `age`, `occupation_id`, `occupation_other`, `valid_id_id`, `valid_id_photo`, `valid_id_back_photo`, `police_clearance`, `verification_status`, `is_active`, `suspension_until`, `rejection_reason`, `deletion_reason`, `suspension_reason`, `verification_date`, `created_at`) VALUES
(101, 1001, NULL, NULL, 'Makati Avenue, Barangay Bel-Air, Makati City, Metro Manila, 1200', '1990-01-15', 36, 3, NULL, 2, 'valid_id_shane.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(102, 1002, NULL, NULL, 'Ayala Avenue, Barangay Makati, Makati City, Metro Manila, 1226', '1988-03-22', 38, 7, NULL, 4, 'valid_id_anne.jpg', 'id_back.jpg', 'pc.jpg', 'pending', 0, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(103, 1003, NULL, NULL, 'Roxas Boulevard, Barangay Ermita, Manila City, Metro Manila, 1000', '1992-05-10', 34, 1, NULL, 1, 'valid_id_dingdong.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(104, 1004, NULL, NULL, 'EDSA, Barangay Mandaluyong, Mandaluyong City, Metro Manila, 1550', '1985-07-18', 41, 12, NULL, 3, 'valid_id_marian.jpg', 'id_back.jpg', 'pc.jpg', 'rejected', 0, NULL, 'Invalid ID documents', NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(105, 1005, NULL, NULL, 'Ortigas Avenue, Barangay Pasig, Pasig City, Metro Manila, 1600', '1991-09-25', 35, 5, NULL, 5, 'valid_id_john.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(106, 1006, NULL, NULL, 'Osmeña Boulevard, Barangay Pob., Cebu City, Cebu, 6000', '1987-11-30', 39, 9, NULL, 2, 'valid_id_bea.jpg', 'id_back.jpg', 'pc.jpg', 'deleted', 0, NULL, NULL, 'Account deleted by user', NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(107, 1007, NULL, NULL, 'Roxas Avenue, Barangay Pob., Davao City, Davao del Sur, 8000', '1993-02-14', 33, 4, NULL, 1, 'valid_id_alden.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(108, 1008, NULL, NULL, 'Velez Street, Barangay Pob., Cagayan de Oro City, Misamis Oriental, 9000', '1989-04-08', 37, 11, NULL, 4, 'valid_id_maine.jpg', 'id_back.jpg', 'pc.jpg', 'pending', 0, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(109, 1009, NULL, NULL, 'Iznart Street, Barangay Pob., Iloilo City, Iloilo, 5000', '1994-06-20', 32, 6, NULL, 3, 'valid_id_arjo.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(110, 1010, NULL, NULL, 'Lacson Street, Barangay Pob., Bacolod City, Negros Occidental, 6100', '1986-08-12', 40, 13, NULL, 5, 'valid_id_liza.jpg', 'id_back.jpg', 'pc.jpg', 'rejected', 0, NULL, 'Incomplete documents', NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(111, 1011, NULL, NULL, 'Mayor Jaldon Avenue, Barangay Pob., Zamboanga City, Zamboanga del Sur, 7000', '1995-10-05', 31, 2, NULL, 2, 'valid_id_enrique.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(112, 1012, NULL, NULL, 'Osmena Boulevard, Barangay Pob., General Santos City, South Cotabato, 9500', '1990-12-17', 36, 8, NULL, 1, 'valid_id_toni.jpg', 'id_back.jpg', 'pc.jpg', 'pending', 0, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(113, 1013, NULL, NULL, 'Session Road, Barangay Pob., Baguio City, Benguet, 2600', '1988-01-28', 38, 14, NULL, 4, 'valid_id_piolo.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(114, 1014, NULL, NULL, 'Claro M. Recto Avenue, Barangay Pob., Angeles City, Pampanga, 2009', '1992-03-11', 34, 10, NULL, 3, 'valid_id_judy.jpg', 'id_back.jpg', 'pc.jpg', 'deleted', 0, NULL, NULL, 'Requested deletion', NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(115, 1015, NULL, NULL, 'Magsaysay Drive, Barangay Pob., Olongapo City, Zambales, 2200', '1987-05-23', 39, 15, NULL, 5, 'valid_id_ryan.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(116, 1016, NULL, NULL, 'Maharlika Highway, Barangay Pob., Lucena City, Quezon, 4301', '1991-07-14', 35, 11, NULL, 2, 'valid_id_angelica.jpg', 'id_back.jpg', 'pc.jpg', 'rejected', 0, NULL, 'Failed background check', NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(117, 1017, NULL, NULL, 'Burgos Street, Barangay Pob., Cabanatuan City, Nueva Ecija, 3100', '1989-09-08', 37, 6, NULL, 1, 'valid_id_jericho.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(118, 1018, NULL, NULL, 'Bonifacio Avenue, Barangay Pob., Malolos, Bulacan, 3000', '1993-11-19', 33, 9, NULL, 4, 'valid_id_kristine.jpg', 'id_back.jpg', 'pc.jpg', 'pending', 0, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(119, 1019, NULL, NULL, 'Aguinaldo Highway, Barangay Pob., Tagaytay, Cavite, 4120', '1986-02-25', 40, 13, NULL, 3, 'valid_id_ogie.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(120, 1020, NULL, NULL, 'Marcos Highway, Barangay Antipolo, Antipolo City, Rizal, 1870', '1994-04-30', 32, 7, NULL, 5, 'valid_id_regine.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(121, 1021, NULL, NULL, 'Commonwealth Avenue, Barangay Quezon City, Quezon City, Metro Manila, 1121', '1990-06-12', 36, 4, NULL, 2, 'valid_id_vilma.jpg', 'id_back.jpg', 'pc.jpg', 'rejected', 0, NULL, 'Suspicious activity detected', NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(122, 1022, NULL, NULL, 'Katipunan Avenue, Barangay Pob., Quezon City, Metro Manila, 1108', '1988-08-17', 38, 12, NULL, 1, 'valid_id_nora.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(123, 1023, NULL, NULL, 'Epifanio de los Santos Avenue, Barangay Pob., Quezon City, Metro Manila, 1100', '1992-10-22', 34, 5, NULL, 4, 'valid_id_maricel.jpg', 'id_back.jpg', 'pc.jpg', 'pending', 0, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(124, 1024, NULL, NULL, 'Paseo de Santa Rosa, Barangay Pob., Pasay City, Metro Manila, 1300', '1985-12-05', 41, 8, NULL, 3, 'valid_id_jaclyn.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(125, 1025, NULL, NULL, 'Bonifacio Global City, Barangay Taguig, Taguig City, Metro Manila, 1634', '1991-01-11', 35, 14, NULL, 5, 'valid_id_sheryl.jpg', 'id_back.jpg', 'pc.jpg', 'deleted', 0, NULL, NULL, 'Account suspended permanently', NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(126, 1026, NULL, NULL, 'Ortigas Avenue, Barangay Pasig, Pasig City, Metro Manila, 1600', '1987-03-16', 39, 2, NULL, 2, 'valid_id_ara.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(127, 1027, NULL, NULL, 'EDSA, Barangay Mandaluyong, Mandaluyong City, Metro Manila, 1550', '1993-05-21', 33, 10, NULL, 1, 'valid_id_gretchen.jpg', 'id_back.jpg', 'pc.jpg', 'rejected', 0, NULL, 'Document verification failed', NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(128, 1028, NULL, NULL, 'Ayala Avenue, Barangay Makati, Makati City, Metro Manila, 1226', '1989-07-26', 37, 6, NULL, 4, 'valid_id_margie.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(129, 1029, NULL, NULL, 'Makati Avenue, Barangay Bel-Air, Makati City, Metro Manila, 1200', '1994-09-03', 32, 11, NULL, 3, 'valid_id_aiko.jpg', 'id_back.jpg', 'pc.jpg', 'pending', 0, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(130, 1030, NULL, NULL, 'Roxas Boulevard, Barangay Ermita, Manila City, Metro Manila, 1000', '1986-11-08', 40, 9, NULL, 5, 'valid_id_ruffa.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(131, 1031, NULL, NULL, 'Luneta Park, Barangay Ermita, Manila City, Metro Manila, 1000', '1995-01-13', 31, 13, NULL, 2, 'valid_id_andi.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(132, 1032, NULL, NULL, 'Intramuros, Barangay Intramuros, Manila City, Metro Manila, 1002', '1990-03-18', 36, 7, NULL, 1, 'valid_id_boots.jpg', 'id_back.jpg', 'pc.jpg', 'rejected', 0, NULL, 'Duplicate account detected', NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(133, 1033, NULL, NULL, 'Binondo, Barangay Binondo, Manila City, Metro Manila, 1006', '1988-05-23', 38, 4, NULL, 4, 'valid_id_camille.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(134, 1034, NULL, NULL, 'Quiapo, Barangay Quiapo, Manila City, Metro Manila, 1001', '1992-07-28', 34, 12, NULL, 3, 'valid_id_diether.jpg', 'id_back.jpg', 'pc.jpg', 'pending', 0, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(135, 1035, NULL, NULL, 'Sampaloc, Barangay Sampaloc, Manila City, Metro Manila, 1008', '1985-09-02', 41, 5, NULL, 5, 'valid_id_erich.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(136, 1036, NULL, NULL, 'Ermita, Barangay Ermita, Manila City, Metro Manila, 1000', '1991-11-07', 35, 8, NULL, 2, 'valid_id_florian.jpg', 'id_back.jpg', 'pc.jpg', 'deleted', 0, NULL, NULL, 'User requested deletion', NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(137, 1037, NULL, NULL, 'Malate, Barangay Malate, Manila City, Metro Manila, 1004', '1987-01-12', 39, 14, NULL, 1, 'valid_id_gina.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(138, 1038, NULL, NULL, 'Paco, Barangay Paco, Manila City, Metro Manila, 1007', '1993-03-17', 33, 6, NULL, 4, 'valid_id_hilda.jpg', 'id_back.jpg', 'pc.jpg', 'rejected', 0, NULL, 'Address verification failed', NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(139, 1039, NULL, NULL, 'San Miguel, Barangay San Miguel, Manila City, Metro Manila, 1005', '1989-05-22', 37, 10, NULL, 3, 'valid_id_irene.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(140, 1040, NULL, NULL, 'Tondo, Barangay Tondo, Manila City, Metro Manila, 1012', '1994-07-27', 32, 11, NULL, 5, 'valid_id_jacqueline.jpg', 'id_back.jpg', 'pc.jpg', 'pending', 0, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(141, 1041, NULL, NULL, 'Santa Cruz, Barangay Santa Cruz, Manila City, Metro Manila, 1014', '1986-09-01', 40, 9, NULL, 2, 'valid_id_kris.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(142, 1042, NULL, NULL, 'Pandacan, Barangay Pandacan, Manila City, Metro Manila, 1011', '1995-11-06', 31, 13, NULL, 1, 'valid_id_lorna.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(143, 1043, NULL, NULL, 'Sta. Ana, Barangay Sta. Ana, Manila City, Metro Manila, 1015', '1990-02-11', 36, 7, NULL, 4, 'valid_id_mylene.jpg', 'id_back.jpg', 'pc.jpg', 'rejected', 0, NULL, 'ID expired', NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(144, 1044, NULL, NULL, 'Maynila, Barangay Maynila, Manila City, Metro Manila, 1003', '1988-04-16', 38, 4, NULL, 3, 'valid_id_nadia.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(145, 1045, NULL, NULL, 'Balut, Barangay Balut, Orani, Bataan, 2100', '1992-06-21', 34, 12, NULL, 5, 'valid_id_obet.jpg', 'id_back.jpg', 'pc.jpg', 'pending', 0, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(146, 1046, NULL, NULL, 'Tetuan, Barangay Tetuan, Orani, Bataan, 2100', '1985-08-26', 41, 5, NULL, 2, 'valid_id_pops.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(147, 1047, NULL, NULL, 'Sarao, Barangay Sarao, Orani, Bataan, 2100', '1991-10-31', 35, 8, NULL, 1, 'valid_id_queen.jpg', 'id_back.jpg', 'pc.jpg', 'deleted', 0, NULL, NULL, 'Fraud suspected', NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(148, 1048, NULL, NULL, 'Parang, Barangay Parang, Orani, Bataan, 2100', '1987-12-05', 39, 14, NULL, 4, 'valid_id_ricky.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(149, 1049, NULL, NULL, 'Limay, Barangay Limay, Limay, Bataan, 2100', '1993-02-10', 33, 6, NULL, 3, 'valid_id_sam.jpg', 'id_back.jpg', 'pc.jpg', 'rejected', 0, NULL, 'Incomplete police clearance', NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(150, 1050, NULL, NULL, 'Hermosa, Barangay Hermosa, Hermosa, Bataan, 2100', '1989-04-15', 37, 10, NULL, 5, 'valid_id_tanya.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(201, 2001, NULL, NULL, 'Makati Avenue, Barangay Bel-Air, Makati City, Metro Manila, 1200', '1990-01-15', 36, 3, NULL, 2, 'valid_id_coco.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(202, 2002, NULL, NULL, 'Ayala Avenue, Barangay Makati, Makati City, Metro Manila, 1226', '1988-03-22', 38, 7, NULL, 4, 'valid_id_vilma.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(203, 2003, NULL, NULL, 'Roxas Boulevard, Barangay Ermita, Manila City, Metro Manila, 1000', '1992-05-10', 34, 1, NULL, 1, 'valid_id_nora.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(204, 2004, NULL, NULL, 'EDSA, Barangay Mandaluyong, Mandaluyong City, Metro Manila, 1550', '1985-07-18', 41, 12, NULL, 3, 'valid_id_maricel.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(205, 2005, NULL, NULL, 'Ortigas Avenue, Barangay Pasig, Pasig City, Metro Manila, 1600', '1991-09-25', 35, 5, NULL, 5, 'valid_id_jaclyn.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(206, 2006, NULL, NULL, 'Osmeña Boulevard, Barangay Pob., Cebu City, Cebu, 6000', '1987-11-30', 39, 9, NULL, 2, 'valid_id_sheryl.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(207, 2007, NULL, NULL, 'Roxas Avenue, Barangay Pob., Davao City, Davao del Sur, 8000', '1993-02-14', 33, 4, NULL, 1, 'valid_id_ara.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(208, 2008, NULL, NULL, 'Velez Street, Barangay Pob., Cagayan de Oro City, Misamis Oriental, 9000', '1989-04-08', 37, 11, NULL, 4, 'valid_id_gretchen.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(209, 2009, NULL, NULL, 'Iznart Street, Barangay Pob., Iloilo City, Iloilo, 5000', '1994-06-20', 32, 6, NULL, 3, 'valid_id_margie.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(210, 2010, NULL, NULL, 'Lacson Street, Barangay Pob., Bacolod City, Negros Occidental, 6100', '1986-08-12', 40, 13, NULL, 5, 'valid_id_aiko.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(211, 2011, NULL, NULL, 'Mayor Jaldon Avenue, Barangay Pob., Zamboanga City, Zamboanga del Sur, 7000', '1995-10-05', 31, 2, NULL, 2, 'valid_id_ruffa.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(212, 2012, NULL, NULL, 'Osmena Boulevard, Barangay Pob., General Santos City, South Cotabato, 9500', '1990-12-17', 36, 8, NULL, 1, 'valid_id_andi.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(213, 2013, NULL, NULL, 'Session Road, Barangay Pob., Baguio City, Benguet, 2600', '1988-01-28', 38, 14, NULL, 4, 'valid_id_boots.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(214, 2014, NULL, NULL, 'Claro M. Recto Avenue, Barangay Pob., Angeles City, Pampanga, 2009', '1992-03-11', 34, 10, NULL, 3, 'valid_id_camille.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(215, 2015, NULL, NULL, 'Magsaysay Drive, Barangay Pob., Olongapo City, Zambales, 2200', '1987-05-23', 39, 15, NULL, 5, 'valid_id_diether.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(216, 2016, NULL, NULL, 'Maharlika Highway, Barangay Pob., Lucena City, Quezon, 4301', '1991-07-14', 35, 11, NULL, 2, 'valid_id_erich.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(217, 2017, NULL, NULL, 'Burgos Street, Barangay Pob., Cabanatuan City, Nueva Ecija, 3100', '1989-09-08', 37, 6, NULL, 1, 'valid_id_florian.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(218, 2018, NULL, NULL, 'Bonifacio Avenue, Barangay Pob., Malolos, Bulacan, 3000', '1993-11-19', 33, 9, NULL, 4, 'valid_id_gina.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(219, 2019, NULL, NULL, 'Aguinaldo Highway, Barangay Pob., Tagaytay, Cavite, 4120', '1986-02-25', 40, 13, NULL, 3, 'valid_id_hilda.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(220, 2020, NULL, NULL, 'Marcos Highway, Barangay Antipolo, Antipolo City, Rizal, 1870', '1994-04-30', 32, 7, NULL, 5, 'valid_id_irene.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(221, 2021, NULL, NULL, 'Commonwealth Avenue, Barangay Quezon City, Quezon City, Metro Manila, 1121', '1990-06-12', 36, 4, NULL, 2, 'valid_id_jacqueline.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(222, 2022, NULL, NULL, 'Katipunan Avenue, Barangay Pob., Quezon City, Metro Manila, 1108', '1988-08-17', 38, 12, NULL, 1, 'valid_id_kris.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(223, 2023, NULL, NULL, 'Epifanio de los Santos Avenue, Barangay Pob., Quezon City, Metro Manila, 1100', '1992-10-22', 34, 5, NULL, 4, 'valid_id_lorna.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(224, 2024, NULL, NULL, 'Paseo de Santa Rosa, Barangay Pob., Pasay City, Metro Manila, 1300', '1985-12-05', 41, 8, NULL, 3, 'valid_id_mylene.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(225, 2025, NULL, NULL, 'Bonifacio Global City, Barangay Taguig, Taguig City, Metro Manila, 1634', '1991-01-11', 35, 14, NULL, 5, 'valid_id_nadia.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(226, 2026, NULL, NULL, 'Ortigas Avenue, Barangay Pasig, Pasig City, Metro Manila, 1600', '1987-03-16', 39, 2, NULL, 2, 'valid_id_obet.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(227, 2027, NULL, NULL, 'EDSA, Barangay Mandaluyong, Mandaluyong City, Metro Manila, 1550', '1993-05-21', 33, 10, NULL, 1, 'valid_id_pops.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(228, 2028, NULL, NULL, 'Ayala Avenue, Barangay Makati, Makati City, Metro Manila, 1226', '1989-07-26', 37, 6, NULL, 4, 'valid_id_queen.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(229, 2029, NULL, NULL, 'Makati Avenue, Barangay Bel-Air, Makati City, Metro Manila, 1200', '1994-09-03', 32, 11, NULL, 3, 'valid_id_ricky.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(230, 2030, NULL, NULL, 'Roxas Boulevard, Barangay Ermita, Manila City, Metro Manila, 1000', '1986-11-08', 40, 9, NULL, 5, 'valid_id_sam.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(231, 2031, NULL, NULL, 'Luneta Park, Barangay Ermita, Manila City, Metro Manila, 1000', '1995-01-13', 31, 13, NULL, 2, 'valid_id_tanya.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(232, 2032, NULL, NULL, 'Intramuros, Barangay Intramuros, Manila City, Metro Manila, 1002', '1990-03-18', 36, 7, NULL, 1, 'valid_id_ula.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(233, 2033, NULL, NULL, 'Binondo, Barangay Binondo, Manila City, Metro Manila, 1006', '1988-05-23', 38, 4, NULL, 4, 'valid_id_vina.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(234, 2034, NULL, NULL, 'Quiapo, Barangay Quiapo, Manila City, Metro Manila, 1001', '1992-07-28', 34, 12, NULL, 3, 'valid_id_wally.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(235, 2035, NULL, NULL, 'Sampaloc, Barangay Sampaloc, Manila City, Metro Manila, 1008', '1985-09-02', 41, 5, NULL, 5, 'valid_id_xander.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(236, 2036, NULL, NULL, 'Ermita, Barangay Ermita, Manila City, Metro Manila, 1000', '1991-11-07', 35, 8, NULL, 2, 'valid_id_yassi.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(237, 2037, NULL, NULL, 'Malate, Barangay Malate, Manila City, Metro Manila, 1004', '1987-01-12', 39, 14, NULL, 1, 'valid_id_zoren.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(238, 2038, NULL, NULL, 'Paco, Barangay Paco, Manila City, Metro Manila, 1007', '1993-03-17', 33, 6, NULL, 4, 'valid_id_alyssa.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(239, 2039, NULL, NULL, 'San Miguel, Barangay San Miguel, Manila City, Metro Manila, 1005', '1989-05-22', 37, 10, NULL, 3, 'valid_id_bea.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(240, 2040, NULL, NULL, 'Tondo, Barangay Tondo, Manila City, Metro Manila, 1012', '1994-07-27', 32, 11, NULL, 5, 'valid_id_carla.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(241, 2041, NULL, NULL, 'Santa Cruz, Barangay Santa Cruz, Manila City, Metro Manila, 1014', '1986-09-01', 40, 9, NULL, 2, 'valid_id_denise.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(242, 2042, NULL, NULL, 'Pandacan, Barangay Pandacan, Manila City, Metro Manila, 1011', '1995-11-06', 31, 13, NULL, 1, 'valid_id_ella.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(243, 2043, NULL, NULL, 'Sta. Ana, Barangay Sta. Ana, Manila City, Metro Manila, 1015', '1990-02-11', 36, 7, NULL, 4, 'valid_id_faye.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(244, 2044, NULL, NULL, 'Maynila, Barangay Maynila, Manila City, Metro Manila, 1003', '1988-04-16', 38, 4, NULL, 3, 'valid_id_giselle.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(245, 2045, NULL, NULL, 'Balut, Barangay Balut, Orani, Bataan, 2100', '1992-06-21', 34, 12, NULL, 5, 'valid_id_hannah.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(246, 2046, NULL, NULL, 'Tetuan, Barangay Tetuan, Orani, Bataan, 2100', '1985-08-26', 41, 5, NULL, 2, 'valid_id_isabelle.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(247, 2047, NULL, NULL, 'Sarao, Barangay Sarao, Orani, Bataan, 2100', '1991-10-31', 35, 8, NULL, 1, 'valid_id_julia.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(248, 2048, NULL, NULL, 'Parang, Barangay Parang, Orani, Bataan, 2100', '1987-12-05', 39, 14, NULL, 4, 'valid_id_kim.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(249, 2049, NULL, NULL, 'Limay, Barangay Limay, Limay, Bataan, 2100', '1993-02-10', 33, 6, NULL, 3, 'valid_id_lj.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(250, 2050, NULL, NULL, 'Hermosa, Barangay Hermosa, Hermosa, Bataan, 2100', '1989-04-15', 37, 10, NULL, 5, 'valid_id_megan.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(251, 3001, NULL, NULL, 'Tetuan, Poblacion, Mankayan, Benguet 2600', '1990-03-15', 36, 3, NULL, 2, 'valid_id_john.jpg', 'id_back_john.jpg', 'pc_john.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(252, 3002, NULL, NULL, 'Magsaysay, Barangay Poblacion, Baguio City, Benguet 2600', '1988-07-22', 37, 7, NULL, 4, 'valid_id_maria.jpg', 'id_back_maria.jpg', 'pc_maria.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(253, 3003, NULL, NULL, 'Session Road, Barangay Asin, Baguio City, Benguet 2600', '1992-11-08', 33, 1, NULL, 1, 'valid_id_robert.jpg', 'id_back_robert.jpg', 'pc_robert.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(254, 3004, NULL, NULL, 'Burnham Road, Barangay Irisan, Baguio City, Benguet 2600', '1995-05-12', 30, 5, NULL, 3, 'valid_id_anna.jpg', 'id_back_anna.jpg', 'pc_anna.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(255, 3005, NULL, NULL, 'Marcos Highway, Barangay Loakan, Baguio City, Benguet 2600', '1987-09-18', 38, 2, NULL, 5, 'valid_id_carlos.jpg', 'id_back_carlos.jpg', 'pc_carlos.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(256, 3006, NULL, NULL, 'Naguilian Road, Poblacion, Buguias, Benguet 2600', '1991-02-25', 35, 4, NULL, 2, 'valid_id_diana.jpg', 'id_back_diana.jpg', 'pc_diana.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 18:06:13', '2026-03-11 16:00:00'),
(257, 3007, NULL, NULL, 'Bokawkan Road, Barangay Cabayan, Baguio City, Benguet 2600', '1989-06-30', 36, 6, NULL, 1, 'valid_id_miguel.jpg', 'id_back_miguel.jpg', 'pc_miguel.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(258, 3008, NULL, NULL, 'Asin Road, Barangay Asin, Baguio City, Benguet 2600', '1993-12-14', 32, 8, NULL, 4, 'valid_id_elena.jpg', 'id_back_elena.jpg', 'pc_elena.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(259, 3009, NULL, NULL, 'Outlook Drive, Barangay Outlook, Baguio City, Benguet 2600', '1986-04-20', 39, 9, NULL, 3, 'valid_id_francisco.jpg', 'id_back_francisco.jpg', 'pc_francisco.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(260, 3010, NULL, NULL, 'Mines View Road, Barangay Mines View, Baguio City, Benguet 2600', '1994-08-05', 31, 10, NULL, 5, 'valid_id_rosa.jpg', 'id_back_rosa.jpg', 'pc_rosa.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(261, 3011, NULL, NULL, 'Trancoville, Barangay Trancoville, Baguio City, Benguet 2600', '1990-10-11', 35, 11, NULL, 2, 'valid_id_antonio.jpg', 'id_back_antonio.jpg', 'pc_antonio.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(262, 3012, NULL, NULL, 'Pacdal Road, Barangay Pacdal, Baguio City, Benguet 2600', '1992-01-17', 34, 12, NULL, 1, 'valid_id_lucia.jpg', 'id_back_lucia.jpg', 'pc_lucia.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(263, 3013, NULL, NULL, 'Inya Road, Barangay Inya, Baguio City, Benguet 2600', '1988-03-23', 37, 13, NULL, 4, 'valid_id_manuel.jpg', 'id_back_manuel.jpg', 'pc_manuel.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(264, 3014, NULL, NULL, 'Kisad Road, Barangay Kisad, Baguio City, Benguet 2600', '1991-07-09', 34, 14, NULL, 3, 'valid_id_teresa.jpg', 'id_back_teresa.jpg', 'pc_teresa.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(265, 3015, NULL, NULL, 'Chanum Road, Barangay Chanum, Baguio City, Benguet 2600', '1989-11-28', 36, 15, NULL, 5, 'valid_id_ramon.jpg', 'id_back_ramon.jpg', 'pc_ramon.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(266, 3016, NULL, NULL, 'Shuntug Road, Barangay Shuntug, Baguio City, Benguet 2600', '1993-05-14', 32, 1, NULL, 2, 'valid_id_carmen.jpg', 'id_back_carmen.jpg', 'pc_carmen.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(267, 3017, NULL, NULL, 'Loakan Road, Barangay Loakan, Baguio City, Benguet 2600', '1987-09-02', 38, 2, NULL, 1, 'valid_id_luis.jpg', 'id_back_luis.jpg', 'pc_luis.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(268, 3018, NULL, NULL, 'Pinsao Road, Barangay Pinsao, Baguio City, Benguet 2600', '1994-02-19', 31, 3, NULL, 4, 'valid_id_isabel.jpg', 'id_back_isabel.jpg', 'pc_isabel.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(269, 3019, NULL, NULL, 'Asin Road, Barangay Asin, Baguio City, Benguet 2600', '1990-06-27', 35, 4, NULL, 3, 'valid_id_pedro.jpg', 'id_back_pedro.jpg', 'pc_pedro.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(270, 3020, NULL, NULL, 'Outlook Drive, Barangay Outlook, Baguio City, Benguet 2600', '1992-10-03', 33, 5, NULL, 5, 'valid_id_beatriz.jpg', 'id_back_beatriz.jpg', 'pc_beatriz.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(271, 3021, NULL, NULL, 'Mines View Road, Barangay Mines View, Baguio City, Benguet 2600', '1989-04-16', 36, 6, NULL, 2, 'valid_id_jorge.jpg', 'id_back_jorge.jpg', 'pc_jorge.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(272, 3022, NULL, NULL, 'Trancoville, Barangay Trancoville, Baguio City, Benguet 2600', '1991-08-22', 34, 7, NULL, 1, 'valid_id_gloria.jpg', 'id_back_gloria.jpg', 'pc_gloria.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(273, 3023, NULL, NULL, 'Pacdal Road, Barangay Pacdal, Baguio City, Benguet 2600', '1993-12-10', 32, 8, NULL, 4, 'valid_id_enrique.jpg', 'id_back_enrique.jpg', 'pc_enrique.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(274, 3024, NULL, NULL, 'Inya Road, Barangay Inya, Baguio City, Benguet 2600', '1988-05-07', 37, 9, NULL, 3, 'valid_id_patricia.jpg', 'id_back_patricia.jpg', 'pc_patricia.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(275, 3025, NULL, NULL, 'Kisad Road, Barangay Kisad, Baguio City, Benguet 2600', '1990-09-13', 35, 10, NULL, 5, 'valid_id_victor.jpg', 'id_back_victor.jpg', 'pc_victor.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(276, 3026, NULL, NULL, 'Chanum Road, Barangay Chanum, Baguio City, Benguet 2600', '1992-03-21', 33, 11, NULL, 2, 'valid_id_sandra.jpg', 'id_back_sandra.jpg', 'pc_sandra.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(277, 3027, NULL, NULL, 'Shuntug Road, Barangay Shuntug, Baguio City, Benguet 2600', '1989-07-08', 36, 12, NULL, 1, 'valid_id_alberto.jpg', 'id_back_alberto.jpg', 'pc_alberto.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(278, 3028, NULL, NULL, 'Loakan Road, Barangay Loakan, Baguio City, Benguet 2600', '1991-11-26', 34, 13, NULL, 4, 'valid_id_veronica.jpg', 'id_back_veronica.jpg', 'pc_veronica.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(279, 3029, NULL, NULL, 'Pinsao Road, Barangay Pinsao, Baguio City, Benguet 2600', '1994-04-12', 31, 14, NULL, 3, 'valid_id_salvador.jpg', 'id_back_salvador.jpg', 'pc_salvador.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00'),
(280, 3030, NULL, NULL, 'Asin Road, Barangay Asin, Baguio City, Benguet 2600', '1987-08-19', 38, 15, NULL, 5, 'valid_id_natalia.jpg', 'id_back_natalia.jpg', 'pc_natalia.jpg', 'approved', 1, NULL, NULL, NULL, NULL, '2026-03-12 15:37:40', '2026-03-11 16:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `report_attachments`
--

CREATE TABLE `report_attachments` (
  `attachment_id` bigint(20) UNSIGNED NOT NULL,
  `report_type` varchar(30) NOT NULL,
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `is_deleted` tinyint(4) DEFAULT 0,
  `deletion_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `project_id`, `reviewer_user_id`, `reviewee_user_id`, `rating`, `comment`, `is_deleted`, `deletion_reason`, `created_at`) VALUES
(1, 2002, 3004, 3002, 5, 'Excellent property owner to work with! Very responsive to communications, provided clear project requirements, and made timely decisions throughout the renovation project. Payment was processed promptly upon completion. Highly recommend working with this owner for future projects.', 0, NULL, '2026-03-11 02:30:00'),
(2, 2002, 3002, 3004, 5, 'Outstanding contractor! The commercial office renovation was completed on time and within budget. The quality of work exceeded our expectations. The team was professional, courteous, and kept us informed throughout the project. All work was done to the highest standards. Would definitely hire them again for future projects.', 0, NULL, '2026-03-11 03:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `review_reports`
--

CREATE TABLE `review_reports` (
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `reporter_user_id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `reason` varchar(120) NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','under_review','resolved','dismissed') NOT NULL DEFAULT 'pending',
  `reviewed_by_user_id` int(11) DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `review_reports`
--

INSERT INTO `review_reports` (`report_id`, `reporter_user_id`, `review_id`, `reason`, `details`, `status`, `reviewed_by_user_id`, `admin_notes`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(1, 3002, 1, 'Inappropriate content', 'The review contains language that may be considered promotional rather than genuine feedback. The reviewer appears to have a conflict of interest as they are also the contractor on the project.', 'pending', NULL, NULL, NULL, '2026-03-12 06:30:00', '2026-03-12 06:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `showcases`
--

CREATE TABLE `showcases` (
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `linked_project_id` int(11) DEFAULT NULL,
  `location` varchar(500) DEFAULT NULL,
  `status` enum('pending','approved','rejected','closed','deleted') NOT NULL DEFAULT 'pending',
  `is_highlighted` tinyint(1) NOT NULL DEFAULT 0,
  `highlighted_at` timestamp NULL DEFAULT NULL,
  `boost_tier` varchar(20) DEFAULT NULL,
  `rejection_reason` text NOT NULL,
  `boost_expiration` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `showcases`
--

INSERT INTO `showcases` (`post_id`, `user_id`, `title`, `content`, `linked_project_id`, `location`, `status`, `is_highlighted`, `highlighted_at`, `boost_tier`, `rejection_reason`, `boost_expiration`, `created_at`, `updated_at`) VALUES
(1, 3004, 'Commercial Office Space Renovation - Project 2002', 'Successfully completed a comprehensive commercial office renovation project in Baguio City. This project involved complete demolition of old fixtures, installation of modern electrical and plumbing systems, and premium interior finishing with professional painting and flooring. The renovation was completed on schedule and within budget, delivering a state-of-the-art office space that exceeds client expectations. The project showcases our expertise in commercial renovations and our commitment to quality workmanship. Key highlights: - Complete office renovation with modern design - Professional electrical and plumbing installation - Premium interior finishing and painting - On-time project completion - Budget-friendly execution - High-quality materials and craftsmanship', 2002, 'Magsaysay, Barangay Poblacion, Baguio City, Benguet 2600', 'approved', 1, '2026-03-12 02:00:00', 'premium', '', NULL, '2026-03-11 04:00:00', '2026-03-12 02:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `showcase_images`
--

CREATE TABLE `showcase_images` (
  `image_id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `showcase_images`
--

INSERT INTO `showcase_images` (`image_id`, `post_id`, `file_path`, `original_name`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'showcases/project_2002_before.jpg', 'project_2002_before.jpg', 0, '2026-03-11 04:00:00', '2026-03-11 04:00:00'),
(2, 1, 'showcases/project_2002_during.jpg', 'project_2002_during.jpg', 1, '2026-03-11 04:00:00', '2026-03-11 04:00:00'),
(3, 1, 'showcases/project_2002_after.jpg', 'project_2002_after.jpg', 2, '2026-03-11 04:00:00', '2026-03-11 04:00:00'),
(4, 1, 'showcases/project_2002_interior.jpg', 'project_2002_interior.jpg', 3, '2026-03-11 04:00:00', '2026-03-11 04:00:00');

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
(1, 'gold', 1, 'Gold Tier Subscription', 199900, 'PHP', 'monthly', NULL, NULL, '[\"Unlock AI driven analytics\",\"Unlimited Bids per month\",\"Boost Bids\"]', 1, 0, '2026-03-05 01:11:04', '2026-03-05 01:11:04'),
(2, 'silver', 1, 'Silver Tier Subscription', 149900, 'PHP', 'monthly', NULL, NULL, '[\"25 Bids per month\",\"Boost Bids\"]', 1, 0, '2026-03-05 01:11:04', '2026-03-05 01:11:04'),
(3, 'bronze', 1, 'Bronze Tier Subscription', 99900, 'PHP', 'monthly', NULL, NULL, '[\"10 Bids per month\"]', 1, 0, '2026-03-05 01:11:04', '2026-03-05 01:11:04'),
(4, 'boost', 0, 'Project Boost', 4900, 'PHP', 'one-time', 7, NULL, '[\"7 Days Visibility Boost\"]', 1, 0, '2026-03-05 01:11:04', '2026-03-05 01:11:04');

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
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `OTP_hash` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `user_type` enum('contractor','property_owner','both') NOT NULL,
  `preferred_role` enum('contractor','owner') DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `OTP_hash`, `bio`, `user_type`, `preferred_role`, `first_name`, `middle_name`, `last_name`, `created_at`, `updated_at`) VALUES
(1001, 'shane_owner', 'shane@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Shane', NULL, 'Gillis', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1002, 'anne_owner', 'anne@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Anne', NULL, 'Curtis', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1003, 'dingdong_owner', 'dingdong@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Dingdong', NULL, 'Dantes', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1004, 'marian_owner', 'marian@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Marian', NULL, 'Rivera', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1005, 'john_owner', 'john@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'John', NULL, 'Lloyd', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1006, 'bea_owner', 'bea@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Bea', NULL, 'Alonzo', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1007, 'alden_owner', 'alden@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Alden', NULL, 'Richards', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1008, 'maine_owner', 'maine@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Maine', NULL, 'Mendoza', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1009, 'arjo_owner', 'arjo@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Arjo', NULL, 'Atayde', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1010, 'liza_owner', 'liza@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Liza', NULL, 'Soberano', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1011, 'enrique_owner', 'enrique@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Enrique', NULL, 'Gil', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1012, 'toni_owner', 'toni@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Toni', NULL, 'Gonzaga', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1013, 'piolo_owner', 'piolo@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Piolo', NULL, 'Pascual', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1014, 'judy_owner', 'judy@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Judy', NULL, 'Ann', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1015, 'ryan_owner', 'ryan@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Ryan', NULL, 'Agoncillo', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1016, 'angelica_owner', 'angelica@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Angelica', NULL, 'Panganiban', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1017, 'jericho_owner', 'jericho@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Jericho', NULL, 'Rosales', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1018, 'kristine_owner', 'kristine@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Kristine', NULL, 'Hermosa', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1019, 'ogie_owner', 'ogie@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Ogie', NULL, 'Alcasid', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1020, 'regine_owner', 'regine@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Regine', NULL, 'Velasquez', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1021, 'vilma_owner', 'vilma@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Vilma', NULL, 'Santos', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1022, 'nora_owner', 'nora@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Nora', NULL, 'Aunor', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1023, 'maricel_owner', 'maricel@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Maricel', NULL, 'Soriano', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1024, 'jaclyn_owner', 'jaclyn@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Jaclyn', NULL, 'Jose', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1025, 'sheryl_owner', 'sheryl@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Sheryl', NULL, 'Cruz', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1026, 'ara_owner', 'ara@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Ara', NULL, 'Mina', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1027, 'gretchen_owner', 'gretchen@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Gretchen', NULL, 'Barretto', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1028, 'margie_owner', 'margie@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Margie', NULL, 'Moran', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1029, 'aiko_owner', 'aiko@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Aiko', NULL, 'Melendez', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1030, 'ruffa_owner', 'ruffa@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Ruffa', NULL, 'Gutierrez', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1031, 'andi_owner', 'andi@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Andi', NULL, 'Eigenmann', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1032, 'boots_owner', 'boots@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Boots', NULL, 'Anson', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1033, 'camille_owner', 'camille@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Camille', NULL, 'Prats', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1034, 'diether_owner', 'diether@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Diether', NULL, 'Ocampo', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1035, 'erich_owner', 'erich@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Erich', NULL, 'Gonzales', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1036, 'florian_owner', 'florian@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Florian', NULL, 'Carandang', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1037, 'gina_owner', 'gina@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Gina', NULL, 'Pareño', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1038, 'hilda_owner', 'hilda@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Hilda', NULL, 'Koronel', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1039, 'irene_owner', 'irene@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Irene', NULL, 'Razal', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1040, 'jacqueline_owner', 'jacqueline@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Jacqueline', NULL, 'Fernandez', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1041, 'kris_owner', 'kris@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Kris', NULL, 'Aquino', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1042, 'lorna_owner', 'lorna@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Lorna', NULL, 'Tolentino', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1043, 'mylene_owner', 'mylene@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Mylene', NULL, 'Dizon', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1044, 'nadia_owner', 'nadia@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Nadia', NULL, 'Montenegro', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1045, 'obet_owner', 'obet@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Obet', NULL, 'Lim', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1046, 'pops_owner', 'pops@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Pops', NULL, 'Fernandez', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1047, 'queen_owner', 'queen@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Queen', NULL, 'Marquez', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1048, 'ricky_owner', 'ricky@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Ricky', NULL, 'Davao', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1049, 'sam_owner', 'sam@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Sam', NULL, 'Milby', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(1050, 'tanya_owner', 'tanya@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Tanya', NULL, 'Gomez', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2001, 'coco_both', 'coco.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Coco', NULL, 'Martin', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2002, 'vilma_both', 'vilma.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Vilma', NULL, 'Santos', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2003, 'nora_both', 'nora.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Nora', NULL, 'Aunor', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2004, 'maricel_both', 'maricel.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Maricel', NULL, 'Soriano', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2005, 'jaclyn_both', 'jaclyn.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Jaclyn', NULL, 'Jose', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2006, 'sheryl_both', 'sheryl.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Sheryl', NULL, 'Cruz', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2007, 'ara_both', 'ara.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Ara', NULL, 'Mina', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2008, 'gretchen_both', 'gretchen.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Gretchen', NULL, 'Barretto', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2009, 'margie_both', 'margie.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Margie', NULL, 'Moran', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2010, 'aiko_both', 'aiko.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Aiko', NULL, 'Melendez', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2011, 'ruffa_both', 'ruffa.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Ruffa', NULL, 'Gutierrez', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2012, 'andi_both', 'andi.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Andi', NULL, 'Eigenmann', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2013, 'boots_both', 'boots.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Boots', NULL, 'Anson', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2014, 'camille_both', 'camille.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Camille', NULL, 'Prats', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2015, 'diether_both', 'diether.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Diether', NULL, 'Ocampo', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2016, 'erich_both', 'erich.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Erich', NULL, 'Gonzales', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2017, 'florian_both', 'florian.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Florian', NULL, 'Carandang', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2018, 'gina_both', 'gina.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Gina', NULL, 'Pareño', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2019, 'hilda_both', 'hilda.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Hilda', NULL, 'Koronel', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2020, 'irene_both', 'irene.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Irene', NULL, 'Razal', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2021, 'jacqueline_both', 'jacqueline.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Jacqueline', NULL, 'Fernandez', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2022, 'kris_both', 'kris.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Kris', NULL, 'Aquino', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2023, 'lorna_both', 'lorna.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Lorna', NULL, 'Tolentino', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2024, 'mylene_both', 'mylene.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Mylene', NULL, 'Dizon', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2025, 'nadia_both', 'nadia.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Nadia', NULL, 'Montenegro', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2026, 'obet_both', 'obet.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Obet', NULL, 'Lim', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2027, 'pops_both', 'pops.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Pops', NULL, 'Fernandez', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2028, 'queen_both', 'queen.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Queen', NULL, 'Marquez', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2029, 'ricky_both', 'ricky.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Ricky', NULL, 'Davao', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2030, 'sam_both', 'sam.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Sam', NULL, 'Milby', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2031, 'tanya_both', 'tanya.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Tanya', NULL, 'Gomez', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2032, 'ula_both', 'ula.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Ula', NULL, 'Legaspi', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2033, 'vina_both', 'vina.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Vina', NULL, 'Morales', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2034, 'wally_both', 'wally.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Wally', NULL, 'Bayola', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2035, 'xander_both', 'xander.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Xander', NULL, 'Ford', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2036, 'yassi_both', 'yassi.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Yassi', NULL, 'Pressman', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2037, 'zoren_both', 'zoren.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Zoren', NULL, 'Legaspi', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2038, 'alyssa_both', 'alyssa.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Alyssa', NULL, 'Valdez', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2039, 'bea_both', 'bea.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Bea', NULL, 'Binene', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2040, 'carla_both', 'carla.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Carla', NULL, 'Abellana', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2041, 'denise_both', 'denise.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Denise', NULL, 'Laurel', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2042, 'ella_both', 'ella.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Ella', NULL, 'Cruz', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2043, 'faye_both', 'faye.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Faye', NULL, 'Hall', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2044, 'giselle_both', 'giselle.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Giselle', NULL, 'Toenges', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2045, 'hannah_both', 'hannah.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Hannah', NULL, 'Delacuz', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2046, 'isabelle_both', 'isabelle.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Isabelle', NULL, 'Daza', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2047, 'julia_both', 'julia.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Julia', NULL, 'Barretto', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2048, 'kim_both', 'kim.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Kim', NULL, 'Chiu', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2049, 'lj_both', 'lj.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'LJ', NULL, 'Reyes', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(2050, 'megan_both', 'megan.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Megan', NULL, 'Young', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3001, 'staff_john_001', 'john.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'John', 'Michael', 'Santos', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3002, 'staff_maria_001', 'maria.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Maria', 'Grace', 'Cruz', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3003, 'staff_robert_001', 'robert.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Robert', 'James', 'Reyes', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3004, 'staff_anna_001', 'anna.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Anna', 'Marie', 'Gonzales', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3005, 'staff_carlos_001', 'carlos.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Carlos', 'Antonio', 'Fernandez', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3006, 'staff_diana_001', 'diana.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Diana', 'Rose', 'Mercado', '2026-03-11 16:00:00', '2026-03-12 10:06:13'),
(3007, 'staff_miguel_001', 'miguel.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Miguel', 'Luis', 'Ramos', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3008, 'staff_elena_001', 'elena.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Elena', 'Victoria', 'Villanueva', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3009, 'staff_francisco_001', 'francisco.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Francisco', 'Xavier', 'Aquino', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3010, 'staff_rosa_001', 'rosa.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Rosa', 'Magdalena', 'Tolentino', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3011, 'staff_antonio_001', 'antonio.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Antonio', 'Benito', 'Magsaysay', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3012, 'staff_lucia_001', 'lucia.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Lucia', 'Esperanza', 'Bonifacio', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3013, 'staff_manuel_001', 'manuel.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Manuel', 'Emilio', 'Aguinaldo', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3014, 'staff_teresa_001', 'teresa.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Teresa', 'Josefina', 'Pacquiao', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3015, 'staff_ramon_001', 'ramon.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Ramon', 'Domingo', 'Rizal', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3016, 'staff_carmen_001', 'carmen.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Carmen', 'Soledad', 'Quezon', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3017, 'staff_luis_001', 'luis.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Luis', 'Fernando', 'Laurel', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3018, 'staff_isabel_001', 'isabel.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Isabel', 'Catalina', 'Marcos', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3019, 'staff_pedro_001', 'pedro.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Pedro', 'Alejandro', 'Osmeña', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3020, 'staff_beatriz_001', 'beatriz.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Beatriz', 'Angelica', 'Macapagal', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3021, 'staff_jorge_001', 'jorge.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Jorge', 'Salvador', 'Recto', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3022, 'staff_gloria_001', 'gloria.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Gloria', 'Herminia', 'Macapagal', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3023, 'staff_enrique_001', 'enrique.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Enrique', 'Roberto', 'Estrada', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3024, 'staff_patricia_001', 'patricia.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Patricia', 'Adriana', 'Arroyo', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3025, 'staff_victor_001', 'victor.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Victor', 'Guillermo', 'Aquino', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3026, 'staff_sandra_001', 'sandra.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Sandra', 'Margarita', 'Duterte', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3027, 'staff_alberto_001', 'alberto.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Alberto', 'Ignacio', 'Sotto', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3028, 'staff_veronica_001', 'veronica.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Veronica', 'Francesca', 'Hontiveros', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3029, 'staff_salvador_001', 'salvador.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Salvador', 'Bartolome', 'Padilla', '2026-03-11 16:00:00', '2026-03-11 16:00:00'),
(3030, 'staff_natalia_001', 'natalia.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, '', NULL, 'Natalia', 'Gabriela', 'Bernardo', '2026-03-11 16:00:00', '2026-03-11 16:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_logs`
--

CREATE TABLE `user_activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `activity_type` enum('user_registered','failed_login_attempt','project_reported','profile_updated','password_reset','email_verified','account_status_changed') NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'User who triggered the activity',
  `subject_id` int(11) DEFAULT NULL COMMENT 'Affected entity id (project_id, dispute_id, etc.)',
  `subject_type` varchar(50) DEFAULT NULL COMMENT 'project | dispute | user | null',
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Extra context: {ip, attempts, reason, old_status, new_status}' CHECK (json_valid(`meta`)),
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_activity_logs`
--

INSERT INTO `user_activity_logs` (`id`, `activity_type`, `user_id`, `subject_id`, `subject_type`, `meta`, `is_read`, `created_at`) VALUES
(1, 'failed_login_attempt', NULL, NULL, NULL, '{\"attempts\":1,\"ip\":\"127.0.0.1\"}', 0, '2026-03-12 09:31:26'),
(2, 'failed_login_attempt', NULL, NULL, NULL, '{\"attempts\":2,\"ip\":\"127.0.0.1\"}', 0, '2026-03-12 09:32:45'),
(3, 'failed_login_attempt', NULL, NULL, NULL, '{\"attempts\":3,\"ip\":\"127.0.0.1\"}', 0, '2026-03-12 09:36:23'),
(4, 'failed_login_attempt', NULL, NULL, NULL, '{\"attempts\":4,\"ip\":\"127.0.0.1\"}', 0, '2026-03-12 09:41:07');

-- --------------------------------------------------------

--
-- Table structure for table `user_reports`
--

CREATE TABLE `user_reports` (
  `report_id` int(11) NOT NULL,
  `reporter_user_id` int(11) NOT NULL,
  `reported_user_id` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','reviewed','resolved','dismissed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indexes for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_created` (`admin_id`,`created_at`);

--
-- Indexes for table `admin_notification_preferences`
--
ALTER TABLE `admin_notification_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_admin_setting` (`admin_id`,`setting_key`),
  ADD KEY `idx_admin_pref_admin` (`admin_id`);

--
-- Indexes for table `admin_sent_notifications`
--
ALTER TABLE `admin_sent_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_asn_admin` (`admin_id`),
  ADD KEY `idx_asn_sent` (`sent_at`);

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
  ADD KEY `contractor_id` (`contractor_id`);

--
-- Indexes for table `bid_files`
--
ALTER TABLE `bid_files`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `bid_id` (`bid_id`);

--
-- Indexes for table `content_reports`
--
ALTER TABLE `content_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `fk_content_reports_reporter` (`reporter_user_id`),
  ADD KEY `fk_content_reports_reviewer` (`reviewed_by_user_id`);

--
-- Indexes for table `contractors`
--
ALTER TABLE `contractors`
  ADD PRIMARY KEY (`contractor_id`),
  ADD KEY `fk_contractor_owner` (`owner_id`);

--
-- Indexes for table `contractor_staff`
--
ALTER TABLE `contractor_staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD KEY `fk_staff_company` (`contractor_id`),
  ADD KEY `fk_staff_owner` (`owner_id`);

--
-- Indexes for table `contractor_types`
--
ALTER TABLE `contractor_types`
  ADD PRIMARY KEY (`type_id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

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
-- Indexes for table `downpayment_payments`
--
ALTER TABLE `downpayment_payments`
  ADD PRIMARY KEY (`dp_payment_id`),
  ADD KEY `idx_dp_project` (`project_id`),
  ADD KEY `idx_dp_owner` (`owner_id`),
  ADD KEY `idx_dp_status` (`payment_status`),
  ADD KEY `fk_dp_contractor` (`contractor_user_id`);

--
-- Indexes for table `item_files`
--
ALTER TABLE `item_files`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `item_id` (`item_id`);

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
  ADD KEY `milestone_date_histories_extension_id_index` (`extension_id`),
  ADD KEY `fk_milestone_date_histories_changed_by` (`changed_by`);

--
-- Indexes for table `milestone_items`
--
ALTER TABLE `milestone_items`
  ADD PRIMARY KEY (`item_id`),
  ADD UNIQUE KEY `idx_item_id_unique` (`item_id`),
  ADD KEY `idx_milestone_id` (`milestone_id`),
  ADD KEY `idx_item_status` (`item_status`);

--
-- Indexes for table `milestone_item_updates`
--
ALTER TABLE `milestone_item_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `milestone_item_updates_milestone_item_id_index` (`milestone_item_id`),
  ADD KEY `milestone_item_updates_project_update_id_index` (`project_update_id`),
  ADD KEY `milestone_item_updates_project_update_id_status_index` (`project_update_id`,`status`),
  ADD KEY `fk_milestone_item_updates_approved_by` (`approved_by`);

--
-- Indexes for table `milestone_payments`
--
ALTER TABLE `milestone_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `fk_payment_contractor` (`contractor_id`);

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
  ADD KEY `payment_adjustment_logs_payment_id_index` (`payment_id`),
  ADD KEY `fk_payment_adjustment_milestone` (`milestone_id`);

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
  ADD KEY `platform_payments_subscriptionplanid_foreign` (`subscriptionPlanId`);

--
-- Indexes for table `post_reports`
--
ALTER TABLE `post_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `post_reports_reviewed_by_user_id_foreign` (`reviewed_by_user_id`),
  ADD KEY `cr_status_idx` (`status`),
  ADD KEY `cr_reporter_idx` (`reporter_user_id`),
  ADD KEY `pr_post_idx` (`post_type`,`post_id`);

--
-- Indexes for table `progress`
--
ALTER TABLE `progress`
  ADD PRIMARY KEY (`progress_id`),
  ADD KEY `fk_progress_milestone_item` (`milestone_item_id`),
  ADD KEY `progress_submitted_by_owner_idx` (`submitted_by_owner_id`);

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
  ADD KEY `projects_is_highlighted_idx` (`is_highlighted`,`highlighted_at`),
  ADD KEY `projects_status_idx` (`project_status`),
  ADD KEY `projects_type_id_idx` (`type_id`);

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
-- Indexes for table `project_updates`
--
ALTER TABLE `project_updates`
  ADD PRIMARY KEY (`extension_id`),
  ADD KEY `project_extensions_project_id_index` (`project_id`),
  ADD KEY `project_extensions_project_id_status_index` (`project_id`,`status`),
  ADD KEY `fk_project_updates_contractor` (`contractor_user_id`),
  ADD KEY `fk_project_updates_owner` (`owner_user_id`);

--
-- Indexes for table `property_owners`
--
ALTER TABLE `property_owners`
  ADD PRIMARY KEY (`owner_id`),
  ADD KEY `fk_owner_user` (`user_id`),
  ADD KEY `fk_owner_occupation` (`occupation_id`),
  ADD KEY `fk_owner_valid_id` (`valid_id_id`);

--
-- Indexes for table `report_attachments`
--
ALTER TABLE `report_attachments`
  ADD PRIMARY KEY (`attachment_id`),
  ADD KEY `report_attachments_report_type_report_id_index` (`report_type`,`report_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `reviews_reviewer_project_unique` (`reviewer_user_id`,`project_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `reviewer_user_id` (`reviewer_user_id`),
  ADD KEY `reviewee_user_id` (`reviewee_user_id`),
  ADD KEY `reviews_reviewee_rating_idx` (`reviewee_user_id`,`rating`);

--
-- Indexes for table `review_reports`
--
ALTER TABLE `review_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `review_reports_reporter_user_id_index` (`reporter_user_id`),
  ADD KEY `review_reports_review_id_index` (`review_id`),
  ADD KEY `review_reports_status_index` (`status`),
  ADD KEY `fk_review_reports_reviewer` (`reviewed_by_user_id`);

--
-- Indexes for table `showcases`
--
ALTER TABLE `showcases`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `pp_user_id_idx` (`user_id`),
  ADD KEY `pp_status_idx` (`status`),
  ADD KEY `pp_created_at_idx` (`created_at`),
  ADD KEY `pp_highlight_idx` (`is_highlighted`,`highlighted_at`),
  ADD KEY `pp_linked_project_idx` (`linked_project_id`);

--
-- Indexes for table `showcase_images`
--
ALTER TABLE `showcase_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `ppi_post_id_idx` (`post_id`);

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
-- Indexes for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ual_type_idx` (`activity_type`),
  ADD KEY `ual_user_idx` (`user_id`),
  ADD KEY `ual_created_idx` (`created_at`),
  ADD KEY `ual_read_idx` (`is_read`);

--
-- Indexes for table `user_reports`
--
ALTER TABLE `user_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `fk_user_reports_reporter` (`reporter_user_id`),
  ADD KEY `fk_user_reports_reported` (`reported_user_id`);

--
-- Indexes for table `valid_ids`
--
ALTER TABLE `valid_ids`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `admin_notification_preferences`
--
ALTER TABLE `admin_notification_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_sent_notifications`
--
ALTER TABLE `admin_sent_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_prediction_logs`
--
ALTER TABLE `ai_prediction_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bids`
--
ALTER TABLE `bids`
  MODIFY `bid_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `bid_files`
--
ALTER TABLE `bid_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `content_reports`
--
ALTER TABLE `content_reports`
  MODIFY `report_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contractors`
--
ALTER TABLE `contractors`
  MODIFY `contractor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `contractor_staff`
--
ALTER TABLE `contractor_staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `contractor_types`
--
ALTER TABLE `contractor_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `contract_terminations`
--
ALTER TABLE `contract_terminations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `conversation_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1003001004;

--
-- AUTO_INCREMENT for table `disputes`
--
ALTER TABLE `disputes`
  MODIFY `dispute_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `dispute_files`
--
ALTER TABLE `dispute_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `downpayment_payments`
--
ALTER TABLE `downpayment_payments`
  MODIFY `dp_payment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `item_files`
--
ALTER TABLE `item_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `message_attachments`
--
ALTER TABLE `message_attachments`
  MODIFY `attachment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `milestones`
--
ALTER TABLE `milestones`
  MODIFY `milestone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `milestone_date_histories`
--
ALTER TABLE `milestone_date_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `milestone_items`
--
ALTER TABLE `milestone_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `milestone_item_updates`
--
ALTER TABLE `milestone_item_updates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `milestone_payments`
--
ALTER TABLE `milestone_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `occupations`
--
ALTER TABLE `occupations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `payment_adjustment_logs`
--
ALTER TABLE `payment_adjustment_logs`
  MODIFY `log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_plans`
--
ALTER TABLE `payment_plans`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `platform_payments`
--
ALTER TABLE `platform_payments`
  MODIFY `platform_payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `post_reports`
--
ALTER TABLE `post_reports`
  MODIFY `report_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `progress`
--
ALTER TABLE `progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `progress_files`
--
ALTER TABLE `progress_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2026;

--
-- AUTO_INCREMENT for table `project_files`
--
ALTER TABLE `project_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_relationships`
--
ALTER TABLE `project_relationships`
  MODIFY `rel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2026;

--
-- AUTO_INCREMENT for table `project_updates`
--
ALTER TABLE `project_updates`
  MODIFY `extension_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `property_owners`
--
ALTER TABLE `property_owners`
  MODIFY `owner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=281;

--
-- AUTO_INCREMENT for table `report_attachments`
--
ALTER TABLE `report_attachments`
  MODIFY `attachment_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `review_reports`
--
ALTER TABLE `review_reports`
  MODIFY `report_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `showcases`
--
ALTER TABLE `showcases`
  MODIFY `post_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `showcase_images`
--
ALTER TABLE `showcase_images`
  MODIFY `image_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `termination_proof`
--
ALTER TABLE `termination_proof`
  MODIFY `proof_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3031;

--
-- AUTO_INCREMENT for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_reports`
--
ALTER TABLE `user_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `valid_ids`
--
ALTER TABLE `valid_ids`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD CONSTRAINT `fk_admin_activity_logs_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_notification_preferences`
--
ALTER TABLE `admin_notification_preferences`
  ADD CONSTRAINT `fk_admin_notification_preferences_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_sent_notifications`
--
ALTER TABLE `admin_sent_notifications`
  ADD CONSTRAINT `fk_admin_sent_notifications_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`admin_id`) ON DELETE CASCADE;

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
-- Constraints for table `content_reports`
--
ALTER TABLE `content_reports`
  ADD CONSTRAINT `fk_content_reports_reporter` FOREIGN KEY (`reporter_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_content_reports_reviewer` FOREIGN KEY (`reviewed_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `contractors`
--
ALTER TABLE `contractors`
  ADD CONSTRAINT `fk_contractor_owner` FOREIGN KEY (`owner_id`) REFERENCES `property_owners` (`owner_id`) ON DELETE CASCADE;

--
-- Constraints for table `contractor_staff`
--
ALTER TABLE `contractor_staff`
  ADD CONSTRAINT `fk_staff_company` FOREIGN KEY (`contractor_id`) REFERENCES `contractors` (`contractor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_staff_owner` FOREIGN KEY (`owner_id`) REFERENCES `property_owners` (`owner_id`) ON DELETE CASCADE;

--
-- Constraints for table `contract_terminations`
--
ALTER TABLE `contract_terminations`
  ADD CONSTRAINT `fk_terminations_contractor` FOREIGN KEY (`contractor_id`) REFERENCES `contractors` (`contractor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_terminations_owner` FOREIGN KEY (`owner_id`) REFERENCES `property_owners` (`owner_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_terminations_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE;

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

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
-- Constraints for table `downpayment_payments`
--
ALTER TABLE `downpayment_payments`
  ADD CONSTRAINT `fk_dp_contractor` FOREIGN KEY (`contractor_user_id`) REFERENCES `contractors` (`contractor_id`) ON DELETE CASCADE;

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
-- Constraints for table `milestone_date_histories`
--
ALTER TABLE `milestone_date_histories`
  ADD CONSTRAINT `fk_milestone_date_histories_changed_by` FOREIGN KEY (`changed_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_milestone_date_histories_extension` FOREIGN KEY (`extension_id`) REFERENCES `project_updates` (`extension_id`) ON DELETE SET NULL;

--
-- Constraints for table `milestone_item_updates`
--
ALTER TABLE `milestone_item_updates`
  ADD CONSTRAINT `fk_milestone_item_updates_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_milestone_item_updates_project_update` FOREIGN KEY (`project_update_id`) REFERENCES `project_updates` (`extension_id`) ON DELETE SET NULL;

--
-- Constraints for table `milestone_payments`
--
ALTER TABLE `milestone_payments`
  ADD CONSTRAINT `fk_payment_contractor` FOREIGN KEY (`contractor_id`) REFERENCES `contractors` (`contractor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `milestone_payments_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `milestone_items` (`item_id`),
  ADD CONSTRAINT `milestone_payments_ibfk_3` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`),
  ADD CONSTRAINT `milestone_payments_ibfk_4` FOREIGN KEY (`owner_id`) REFERENCES `property_owners` (`owner_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `payment_adjustment_logs`
--
ALTER TABLE `payment_adjustment_logs`
  ADD CONSTRAINT `fk_payment_adjustment_milestone` FOREIGN KEY (`milestone_id`) REFERENCES `milestones` (`milestone_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payment_adjustment_payment` FOREIGN KEY (`payment_id`) REFERENCES `milestone_payments` (`payment_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_payment_adjustment_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payment_adjustment_source_item` FOREIGN KEY (`source_item_id`) REFERENCES `milestone_items` (`item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payment_adjustment_target_item` FOREIGN KEY (`target_item_id`) REFERENCES `milestone_items` (`item_id`) ON DELETE SET NULL;

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
-- Constraints for table `post_reports`
--
ALTER TABLE `post_reports`
  ADD CONSTRAINT `post_reports_reporter_user_id_foreign` FOREIGN KEY (`reporter_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_reports_reviewed_by_user_id_foreign` FOREIGN KEY (`reviewed_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `progress`
--
ALTER TABLE `progress`
  ADD CONSTRAINT `fk_progress_milestone_item` FOREIGN KEY (`milestone_item_id`) REFERENCES `milestone_items` (`item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_progress_submitted_by_owner` FOREIGN KEY (`submitted_by_owner_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

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
-- Constraints for table `project_updates`
--
ALTER TABLE `project_updates`
  ADD CONSTRAINT `fk_project_updates_contractor` FOREIGN KEY (`contractor_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_project_updates_owner` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `property_owners`
--
ALTER TABLE `property_owners`
  ADD CONSTRAINT `fk_owner_occupation` FOREIGN KEY (`occupation_id`) REFERENCES `occupations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_owner_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_owner_valid_id` FOREIGN KEY (`valid_id_id`) REFERENCES `valid_ids` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`reviewer_user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`reviewee_user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `review_reports`
--
ALTER TABLE `review_reports`
  ADD CONSTRAINT `fk_review_reports_reporter` FOREIGN KEY (`reporter_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_reports_review` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`review_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_reports_reviewer` FOREIGN KEY (`reviewed_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `showcases`
--
ALTER TABLE `showcases`
  ADD CONSTRAINT `fk_showcases_project` FOREIGN KEY (`linked_project_id`) REFERENCES `projects` (`project_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_showcases_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_posts_linked_project_id_foreign` FOREIGN KEY (`linked_project_id`) REFERENCES `projects` (`project_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `showcase_images`
--
ALTER TABLE `showcase_images`
  ADD CONSTRAINT `fk_showcase_images_post` FOREIGN KEY (`post_id`) REFERENCES `showcases` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_post_images_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `showcases` (`post_id`) ON DELETE CASCADE;

--
-- Constraints for table `termination_proof`
--
ALTER TABLE `termination_proof`
  ADD CONSTRAINT `fk_proof_termination_link` FOREIGN KEY (`termination_id`) REFERENCES `contract_terminations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD CONSTRAINT `fk_user_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `user_reports`
--
ALTER TABLE `user_reports`
  ADD CONSTRAINT `fk_user_reports_reported` FOREIGN KEY (`reported_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_reports_reporter` FOREIGN KEY (`reporter_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_reports_ibfk_1` FOREIGN KEY (`reporter_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_reports_ibfk_2` FOREIGN KEY (`reported_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
