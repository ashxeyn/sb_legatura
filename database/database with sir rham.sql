-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 10, 2026 at 03:57 PM
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
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

--
-- Dumping data for table `admin_notification_preferences`
--

INSERT INTO `admin_notification_preferences` (`id`, `admin_id`, `setting_key`, `is_enabled`, `updated_at`) VALUES
(1, 'ADMIN-1', 'user_registered', 1, '2026-03-08 13:20:45'),
(2, 'ADMIN-1', 'failed_login_attempt', 1, '2026-03-08 13:20:45'),
(3, 'ADMIN-1', 'project_reported', 1, '2026-03-08 13:20:45'),
(4, 'ADMIN-1', 'profile_updated', 1, '2026-03-08 13:20:45'),
(5, 'ADMIN-1', 'password_reset', 1, '2026-03-08 13:20:45'),
(6, 'ADMIN-1', 'email_verified', 1, '2026-03-08 13:20:45'),
(7, 'ADMIN-1', 'account_status_changed', 1, '2026-03-08 13:20:45'),
(8, 'ADMIN-1', 'channel_email', 1, '2026-03-08 13:20:45');

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
('ADMIN-1', 'admin123', 'admin@gmail.com', '$2y$12$wN6x17yJegWeKIUj3Lq8O.sAG6QfX.5OPQ13k0xSAY./xrDhej5Uq', 'admin', 'admin', 'admin', 1, '2025-10-25 15:19:23', NULL);

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

--
-- Dumping data for table `ai_prediction_logs`
--

INSERT INTO `ai_prediction_logs` (`id`, `project_id`, `prediction`, `delay_probability`, `weather_severity`, `ai_response_snapshot`, `created_at`, `updated_at`) VALUES
(1, 1057, 'ON-TIME', 0.1067, 0, '{\"prediction\":{\"delay_probability\":0.1067,\"prediction\":\"ON-TIME\",\"reason\":\"Standard AI analysis based on current metrics.\"},\"analysis_report\":{\"conclusion\":\"The project \'Wonderland\' is currently ahead of schedule. AI predicts a 10.7% probability of delay. Weather impact is Minimal (0.08mm rain). Holidays impact 0% of remaining time. Standard AI analysis based on current metrics.\",\"pacing_status\":{\"pacing_index\":1,\"avg_delay_days\":0,\"rejected_count\":0,\"details\":[{\"title\":\"First\",\"status\":\"No Submission\",\"days_variance\":0,\"pacing_label\":\"Pending\"},{\"title\":\"Secondt\",\"status\":\"No Submission\",\"days_variance\":0,\"pacing_label\":\"Pending\"}]},\"contractor_audit\":{\"experience\":\"5 Years\",\"historical_success\":\"39%\",\"flagged\":false}},\"weather\":{\"avg_temp\":28.5,\"avg_humidity\":74,\"avg_wind\":10.8,\"total_rain\":0.08,\"condition_text\":\"Patchy rain nearby\",\"condition_icon\":\"https:\\/\\/cdn.weatherapi.com\\/weather\\/64x64\\/day\\/176.png\",\"forecast\":[{\"date\":\"Wed, Mar 04\",\"temp_avg\":25.9,\"condition\":\"Patchy rain nearby\",\"icon\":\"https:\\/\\/cdn.weatherapi.com\\/weather\\/64x64\\/day\\/176.png\",\"rain_chance\":96},{\"date\":\"Thu, Mar 05\",\"temp_avg\":25.9,\"condition\":\"Patchy rain nearby\",\"icon\":\"https:\\/\\/cdn.weatherapi.com\\/weather\\/64x64\\/day\\/176.png\",\"rain_chance\":79},{\"date\":\"Fri, Mar 06\",\"temp_avg\":26.4,\"condition\":\"Patchy rain nearby\",\"icon\":\"https:\\/\\/cdn.weatherapi.com\\/weather\\/64x64\\/day\\/176.png\",\"rain_chance\":84}]},\"weather_severity\":0,\"dds_recommendations\":[],\"enso_state\":\"Neutral\"}', '2026-03-03 21:40:30', '2026-03-03 21:40:30'),
(2, 1048, 'DELAYED', 0.7500, 0, '{\"prediction\":{\"delay_probability\":0.75,\"prediction\":\"DELAYED\",\"reason\":\"CRITICAL: 1 active dispute(s) detected. Construction disputes typically slow or halt work progress.\"},\"analysis_report\":{\"conclusion\":\"The project \'noche buena\' is currently ahead of schedule. AI predicts a 75.0% probability of delay. Weather impact is Minimal (0.09mm rain). Holidays impact 0% of remaining time. CRITICAL: 1 active dispute(s) detected. Construction disputes typically slow or halt work progress.\",\"pacing_status\":{\"pacing_index\":1.2,\"avg_delay_days\":-259,\"rejected_count\":0,\"details\":[{\"title\":\"1st\",\"status\":\"approved\",\"days_variance\":-153,\"pacing_label\":\"ON-TIME\\/EARLY\"},{\"title\":\"2nd\",\"status\":\"approved\",\"days_variance\":-365,\"pacing_label\":\"ON-TIME\\/EARLY\"}]},\"contractor_audit\":{\"experience\":\"5 Years\",\"historical_success\":\"39%\",\"flagged\":false}},\"weather\":{\"avg_temp\":28.5,\"avg_humidity\":75,\"avg_wind\":10.4,\"total_rain\":0.09,\"condition_text\":\"Patchy rain nearby\",\"condition_icon\":\"https:\\/\\/cdn.weatherapi.com\\/weather\\/64x64\\/day\\/176.png\",\"forecast\":[{\"date\":\"Wed, Mar 04\",\"temp_avg\":25.8,\"condition\":\"Patchy rain nearby\",\"icon\":\"https:\\/\\/cdn.weatherapi.com\\/weather\\/64x64\\/day\\/176.png\",\"rain_chance\":96},{\"date\":\"Thu, Mar 05\",\"temp_avg\":25.8,\"condition\":\"Patchy rain nearby\",\"icon\":\"https:\\/\\/cdn.weatherapi.com\\/weather\\/64x64\\/day\\/176.png\",\"rain_chance\":86},{\"date\":\"Fri, Mar 06\",\"temp_avg\":26.3,\"condition\":\"Patchy rain nearby\",\"icon\":\"https:\\/\\/cdn.weatherapi.com\\/weather\\/64x64\\/day\\/176.png\",\"rain_chance\":89}]},\"weather_severity\":0,\"dds_recommendations\":[\"\\ud83d\\udfe2 PACING GOOD: Work is ahead of schedule. Ensure quality isn\'t being sacrificed for speed.\",\"\\u2696\\ufe0f LEGAL RISK: Active disputes detected. Assign a mediator immediately.\",\"\\ud83d\\udce2 MANAGEMENT ACTION: High Risk of Delay. Convene emergency meeting with contractor.\"],\"enso_state\":\"Neutral\"}', '2026-03-03 21:41:46', '2026-03-03 21:41:46');

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
(296, 1055, 1809, 6000000.00, 36, 'hsiaoajshss', 'cancelled', NULL, '2026-03-06 07:39:41', NULL),
(297, 1057, 1809, 9000000.00, 36, 'Miney Mouse Club House Wonderland we are the best', 'accepted', NULL, '2026-03-03 21:20:40', '2026-03-03 21:21:42'),
(298, 1052, 1809, 5000000.00, 36, 'yaahh we the best', 'submitted', NULL, '2026-03-06 07:07:54', NULL),
(302, 1061, 1687, 3800000.00, 7, 'We have extensive experience in modern residential construction. Our team can start immediately.', '', NULL, '2026-03-09 03:10:28', NULL),
(303, 1063, 1687, 3800000.00, 7, 'We have extensive experience in modern residential construction. Our team can start immediately.', '', NULL, '2026-03-09 03:12:51', NULL),
(304, 1065, 1687, 3800000.00, 7, 'We have extensive experience in modern residential construction. Our team can start immediately.', '', NULL, '2026-03-09 03:14:04', NULL),
(305, 1067, 1687, 3800000.00, 7, 'We have extensive experience in modern residential construction. Our team can start immediately.', '', NULL, '2026-03-09 03:16:10', NULL),
(306, 1069, 1687, 3800000.00, 7, 'We have extensive experience in modern residential construction. Our team can start immediately.', '', NULL, '2026-03-09 03:16:56', NULL),
(307, 1071, 1687, 3800000.00, 7, 'We have extensive experience in modern residential construction. Our team can start immediately.', '', NULL, '2026-03-09 03:18:30', NULL),
(308, 1073, 1687, 3800000.00, 7, 'We have extensive experience in modern residential construction. Our team can start immediately.', '', NULL, '2026-03-09 03:19:22', NULL),
(309, 1077, 1687, 3800000.00, 7, 'We have extensive experience in modern residential construction. Our team can start immediately.', '', NULL, '2026-03-09 03:19:53', NULL),
(312, 1078, 1687, 5250000.00, 10, 'We have extensive experience in commercial renovations. Our team can deliver quality work within the timeline.', 'accepted', NULL, '2026-01-09 21:08:43', '2026-01-12 16:00:00'),
(313, 1078, 1688, 5350000.00, 11, 'Competitive pricing with proven track record in similar projects.', 'rejected', NULL, '2026-01-11 21:08:43', NULL),
(314, 1078, 1689, 5500000.00, 11, 'Competitive pricing with proven track record in similar projects.', 'rejected', NULL, '2026-01-13 21:08:43', NULL),
(315, 1078, 1690, 4800000.00, 10, 'Competitive pricing with proven track record in similar projects.', 'rejected', NULL, '2026-01-15 21:08:43', NULL),
(316, 1078, 1691, 5250000.00, 12, 'Competitive pricing with proven track record in similar projects.', 'rejected', NULL, '2026-01-17 21:08:43', NULL);

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
(12, 296, 'id.pdf', 'bid_files/1772365006_id.pdf', NULL, '2026-03-01 03:36:46'),
(13, 296, '753fc4e1-2fff-400b-b27d-e27c967575dc.jpg', 'bid_files/1772365006_753fc4e1-2fff-400b-b27d-e27c967575dc.jpg', NULL, '2026-03-01 03:36:46'),
(14, 297, 'EMBEDDED-SYSTEMS-DESIGN-Lect.pdf', 'bid_files/1772601640_69a7c128a533a_EMBEDDED-SYSTEMS-DESIGN-Lect.pdf', NULL, '2026-03-03 21:20:40'),
(15, 298, 'Screenshot_20260304-025218.jpg', 'bid_files/1772809674_69aaedca42f63_Screenshot_20260304-025218.jpg', NULL, '2026-03-06 07:07:55'),
(16, 296, 'Screenshot_20260306-000642.jpg', 'bid_files/1772811581_69aaf53dbbcfd_Screenshot_20260306-000642.jpg', NULL, '2026-03-06 07:39:41');

-- --------------------------------------------------------

--
-- Table structure for table `content_reports`
--

CREATE TABLE `content_reports` (
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `reporter_user_id` bigint(20) UNSIGNED NOT NULL,
  `content_type` enum('project','showcase') NOT NULL,
  `content_id` bigint(20) UNSIGNED NOT NULL,
  `reason` varchar(120) NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','under_review','resolved','dismissed') NOT NULL DEFAULT 'pending',
  `reviewed_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
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
  `is_active` tinyint(1) DEFAULT 1,
  `suspension_until` date DEFAULT NULL,
  `suspension_reason` text DEFAULT NULL,
  `deletion_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Dumping data for table `contract_terminations`
--

INSERT INTO `contract_terminations` (`id`, `project_id`, `contractor_id`, `owner_id`, `remarks`, `reason`, `terminated_at`) VALUES
(3, 1081, 1687, 1687, 'Project was terminated after initial planning phase. No major construction work had commenced. All parties were notified and agreed to termination terms.', 'Client decided to cancel project due to business restructuring and budget constraints', '2026-02-07 04:31:34');

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
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_admin_conversation` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`conversation_id`, `sender_id`, `receiver_id`, `is_suspended`, `no_suspends`, `reason`, `suspended_until`, `status`, `created_at`, `updated_at`, `is_admin_conversation`) VALUES
(1000002, 1, 2, 0, 0, NULL, NULL, 'active', '2026-03-02 22:23:21', '2026-03-02 22:23:21', 1),
(1000372, 1, 372, 0, 0, NULL, NULL, 'active', '2026-03-02 22:38:22', '2026-03-02 22:38:22', 1),
(2000371, 371, 2, 0, 4, NULL, NULL, 'active', '2026-02-07 19:29:05', '2026-02-07 19:29:05', 0),
(2000372, 372, 2, 0, 0, NULL, NULL, 'active', '2026-03-02 23:03:00', '2026-03-02 23:03:00', 0),
(3000372, 372, 3, 0, 0, NULL, NULL, 'active', '2026-02-08 07:47:21', '2026-02-08 07:47:21', 0),
(103000371, 371, 103, 0, 2, NULL, NULL, 'active', '2026-02-08 06:44:37', '2026-02-08 06:44:37', 0),
(352000372, 372, 352, 0, 1, NULL, NULL, 'active', '2026-02-08 04:39:09', '2026-02-08 04:39:09', 0),
(371000372, 371, 372, 0, 1, NULL, NULL, 'active', '2026-02-07 19:29:05', '2026-02-07 19:29:05', 0),
(372000392, 372, 392, 0, 0, NULL, NULL, 'active', '2026-03-03 21:23:49', '2026-03-03 21:23:49', 0);

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
(88, 1056, 380, 379, 1564, 2790, 'Delay', NULL, NULL, 'qqqqqqqqqqqqqqqqqqqqqqqqqqq', 'open', '', '', NULL, '2026-02-28 06:17:02', NULL),
(89, 1049, 372, 371, 1559, 2776, 'Halt', NULL, NULL, 'aaaaaaaaaaaaaa', 'open', '', '', NULL, '2026-03-01 08:03:02', NULL),
(91, 1080, 101, 1, NULL, NULL, 'Halt', NULL, NULL, 'Concrete quality does not meet specifications. Structural engineer found issues with concrete strength in ground floor columns.', 'under_review', 'Quality concerns - concrete strength below specified standards', '', NULL, '2026-03-09 03:19:53', NULL);

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
(5, 88, 'disputes/evidence/1772288222_69a2f8de919b7.pdf', 'id.pdf', 'application/pdf', 220855, '2026-02-28 06:17:02'),
(6, 89, 'disputes/evidence/1772380982_69a46336bfcce.pdf', 'id.pdf', 'application/pdf', 220855, '2026-03-01 08:03:03');

-- --------------------------------------------------------

--
-- Table structure for table `downpayment_payments`
--

CREATE TABLE `downpayment_payments` (
  `dp_payment_id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `owner_id` int(10) UNSIGNED NOT NULL,
  `contractor_user_id` int(10) UNSIGNED NOT NULL,
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

--
-- Dumping data for table `downpayment_payments`
--

INSERT INTO `downpayment_payments` (`dp_payment_id`, `project_id`, `owner_id`, `contractor_user_id`, `amount`, `payment_type`, `transaction_number`, `receipt_photo`, `transaction_date`, `payment_status`, `reason`, `created_at`, `updated_at`) VALUES
(1, 1053, 1814, 2055, 4444444.00, 'bank_transfer', '70493826472', 'payments/downpayment/1772724249_dp_69a9a0190da2e.jpg', '2026-03-05', 'approved', NULL, '2026-03-05 15:24:09', '2026-03-05 15:45:57'),
(3, 1057, 1829, 2055, 50000.00, 'online_payment', '95363663696', 'payments/downpayment/1772868067_dp_69abd1e379729.jpg', '2026-03-07', 'submitted', NULL, '2026-03-07 07:21:08', NULL),
(4, 1066, 1687, 1, 1300000.00, 'bank_transfer', 'TXN-2024-001', 'receipts/downpayment_001.jpg', '2026-01-13', 'approved', NULL, '2026-03-09 03:14:04', NULL),
(5, 1068, 1687, 1, 1300000.00, 'bank_transfer', 'TXN-2024-001', 'receipts/downpayment_001.jpg', '2026-01-13', 'approved', NULL, '2026-03-09 03:16:10', NULL),
(6, 1070, 1687, 1, 1300000.00, 'bank_transfer', 'TXN-2024-001', 'receipts/downpayment_001.jpg', '2026-01-13', 'approved', NULL, '2026-03-09 03:16:56', NULL),
(7, 1072, 1687, 1876, 1300000.00, 'bank_transfer', 'TXN-2024-001', 'receipts/downpayment_001.jpg', '2026-01-13', 'approved', NULL, '2026-03-09 03:18:30', NULL),
(8, 1074, 1687, 1876, 1300000.00, 'bank_transfer', 'TXN-2024-001', 'receipts/downpayment_001.jpg', '2026-01-13', 'approved', NULL, '2026-03-09 03:19:22', NULL),
(9, 1076, 1687, 1876, 3000000.00, 'bank_transfer', 'TXN-HALT-001', 'receipts/halt_downpayment.jpg', '2025-12-14', 'approved', NULL, '2026-03-09 03:19:22', NULL),
(10, 1078, 1687, 1876, 1300000.00, 'bank_transfer', 'TXN-2024-001', 'receipts/downpayment_001.jpg', '2026-01-13', 'approved', NULL, '2026-03-09 03:19:53', NULL),
(11, 1080, 1687, 1876, 3000000.00, 'bank_transfer', 'TXN-HALT-001', 'receipts/halt_downpayment.jpg', '2025-12-14', 'approved', NULL, '2026-03-09 03:19:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `item_files`
--

CREATE TABLE `item_files` (
  `file_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_files`
--

INSERT INTO `item_files` (`file_id`, `item_id`, `file_path`) VALUES
(1, 2795, 'milestone_items/jiaBhBN028G1GLxgXApICwggfJMRvZmsSfpS28GM.jpg'),
(2, 2796, 'milestone_items/Uwk27nM4pjAQhpNCrSdbxteGWg665fUdraAGJzLZ.pdf'),
(3, 2797, 'milestone_items/Myc9WL0ApLmIXZ1Y1bQVCPZZViWA2ad8X6UfpZA2.pdf'),
(4, 2798, 'milestone_items/lMpHOc87MWCpLYsxogWVxjyvXwKU1tytkvT7kPeM.pdf');

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
(243, 2000371, 1, 'hi po free po ba kayo ngayon', 0, 0, NULL, '2026-02-07 19:29:05', '2026-02-07 19:29:05'),
(244, 371000372, 1, 'hi po free po ba kayo ngayon', 1, 0, NULL, '2026-02-07 19:29:05', '2026-02-07 19:30:25'),
(245, 371000372, 0, 'nuyan', 1, 0, NULL, '2026-02-07 19:30:32', '2026-02-07 19:30:37'),
(246, 371000372, 0, 'bb', 1, 0, NULL, '2026-02-07 19:33:43', '2026-02-07 19:33:49'),
(247, 371000372, 1, 'ano yan baby', 1, 0, NULL, '2026-02-07 19:33:58', '2026-02-07 19:34:09'),
(248, 371000372, 1, '', 1, 0, NULL, '2026-02-07 19:34:05', '2026-02-07 19:34:09'),
(249, 371000372, 0, 'hi beh', 1, 0, NULL, '2026-02-07 23:29:34', '2026-02-07 23:29:51'),
(250, 371000372, 0, 'hi', 1, 0, NULL, '2026-02-07 23:30:17', '2026-02-07 23:34:30'),
(252, 371000372, 0, 'hi', 1, 0, NULL, '2026-02-07 23:34:25', '2026-02-07 23:34:30'),
(253, 371000372, 0, 'kumain ka na?', 1, 0, NULL, '2026-02-07 23:35:05', '2026-02-07 23:35:15'),
(254, 371000372, 0, 'kamusta', 1, 0, NULL, '2026-02-07 23:35:23', '2026-02-07 23:35:30'),
(259, 371000372, 0, 'hmm', 1, 0, NULL, '2026-02-07 23:44:28', '2026-02-07 23:44:32'),
(260, 371000372, 1, 'hmmm', 1, 0, NULL, '2026-02-07 23:44:34', '2026-02-07 23:44:46'),
(261, 371000372, 0, 'hhhhh', 1, 0, NULL, '2026-02-07 23:44:50', '2026-02-07 23:44:55'),
(266, 371000372, 0, 'ddd', 1, 0, NULL, '2026-02-07 23:53:10', '2026-02-07 23:53:15'),
(269, 371000372, 1, 'sss', 1, 0, NULL, '2026-02-07 23:59:24', '2026-02-08 00:06:32'),
(271, 371000372, 1, 'hello', 1, 0, NULL, '2026-02-07 23:59:44', '2026-02-08 00:06:32'),
(273, 371000372, 1, 'yow', 1, 0, NULL, '2026-02-08 00:06:28', '2026-02-08 00:06:32'),
(274, 371000372, 0, 'hi', 1, 0, NULL, '2026-02-08 00:06:34', '2026-02-08 00:08:20'),
(282, 371000372, 0, 'free kaba bukas?', 1, 0, NULL, '2026-02-08 00:16:42', '2026-02-08 00:16:46'),
(283, 371000372, 0, 'hi ngga]\\', 1, 0, NULL, '2026-02-08 00:20:32', '2026-02-08 00:20:34'),
(284, 371000372, 1, 'okay ka lang?4', 1, 0, NULL, '2026-02-08 00:23:40', '2026-02-08 00:23:44'),
(285, 371000372, 0, 'yea okay lang naman po', 1, 0, NULL, '2026-02-08 00:23:49', '2026-02-08 00:23:50'),
(291, 371000372, 1, 'nigga', 1, 0, NULL, '2026-02-08 03:54:56', '2026-02-08 03:55:14'),
(294, 371000372, 1, 'negro', 1, 0, NULL, '2026-02-08 04:02:31', '2026-02-08 04:02:41'),
(295, 371000372, 0, 'musta', 1, 0, NULL, '2026-02-08 04:07:10', '2026-02-08 04:07:14'),
(296, 371000372, 0, 'negro', 1, 0, NULL, '2026-02-08 04:10:01', '2026-02-08 04:10:05'),
(297, 352000372, 1, 'girl nigga', 0, 0, NULL, '2026-02-08 04:39:09', '2026-02-08 04:39:09'),
(298, 2000371, 1, 'nigggggggggggga', 0, 0, NULL, '2026-02-08 05:05:58', '2026-02-08 05:05:58'),
(299, 2000371, 1, 'nigga', 0, 0, NULL, '2026-02-08 05:06:04', '2026-02-08 05:06:04'),
(300, 371000372, 0, 'sorry beh', 1, 0, NULL, '2026-02-08 05:31:06', '2026-02-08 05:31:13'),
(303, 371000372, 1, 'hi', 1, 0, NULL, '2026-02-08 06:38:54', '2026-02-08 06:39:01'),
(304, 371000372, 0, 'sss', 1, 0, NULL, '2026-02-08 06:39:04', '2026-02-08 06:39:08'),
(305, 371000372, 1, 'nigga', 1, 0, NULL, '2026-02-08 06:39:11', '2026-02-08 06:39:15'),
(306, 103000371, 1, 'sss', 0, 0, NULL, '2026-02-08 06:44:37', '2026-02-08 06:44:37'),
(307, 103000371, 1, 'nigga', 0, 0, NULL, '2026-02-08 06:44:53', '2026-02-08 06:44:53'),
(308, 103000371, 1, 'nigga', 0, 0, NULL, '2026-02-08 06:45:29', '2026-02-08 06:45:29'),
(310, 371000372, 0, 'nigga', 1, 0, NULL, '2026-02-08 07:22:01', '2026-02-08 07:23:50'),
(311, 371000372, 1, 'test', 1, 0, NULL, '2026-02-08 07:23:55', '2026-02-08 07:23:59'),
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
(329, 371000372, 1, 'hi', 1, 0, NULL, '2026-02-12 20:49:55', '2026-02-12 20:50:11'),
(330, 371000372, 1, 'hi', 1, 0, NULL, '2026-02-12 20:49:58', '2026-02-12 20:50:11'),
(331, 371000372, 1, 'hi', 1, 0, NULL, '2026-02-12 20:50:00', '2026-02-12 20:50:11'),
(332, 371000372, 0, 'nigger', 1, 0, NULL, '2026-02-12 20:51:02', '2026-02-12 20:51:04'),
(333, 371000372, 0, 'n i g g a', 1, 0, NULL, '2026-02-12 20:51:18', '2026-02-12 20:51:20'),
(334, 371000372, 0, 'fuck you', 1, 0, NULL, '2026-02-12 20:51:24', '2026-02-12 20:51:26'),
(335, 371000372, 0, 'buysit', 1, 0, NULL, '2026-02-12 20:51:45', '2026-02-12 20:51:47'),
(336, 371000372, 0, 'Buysir', 1, 0, NULL, '2026-03-01 01:25:24', '2026-03-01 01:31:41'),
(337, 371000372, 0, 'Fuck you', 1, 0, NULL, '2026-03-01 01:25:48', '2026-03-01 01:31:41'),
(338, 371000372, 1, 'fuck you', 1, 0, NULL, '2026-03-01 01:32:24', '2026-03-01 01:32:25'),
(339, 371000372, 0, 'Kgkg', 1, 0, NULL, '2026-03-01 01:33:48', '2026-03-01 01:33:51'),
(340, 371000372, 1, 'Gyuiu', 1, 0, NULL, '2026-03-01 01:35:38', '2026-03-01 01:35:40'),
(341, 371000372, 0, 'Jggh', 1, 0, NULL, '2026-03-01 01:35:50', '2026-03-01 01:35:57'),
(342, 371000372, 1, 'nigger', 1, 0, NULL, '2026-03-01 01:37:55', '2026-03-01 01:37:57'),
(343, 371000372, 1, 'nigga', 1, 0, NULL, '2026-03-01 01:38:22', '2026-03-01 01:38:32'),
(344, 371000372, 1, 'Jeff freekava', 1, 0, NULL, '2026-03-01 02:18:08', '2026-03-01 02:18:09'),
(345, 371000372, 1, 'Jnd akk frre bukss', 1, 0, NULL, '2026-03-01 02:18:25', '2026-03-01 02:18:26'),
(346, 371000372, 0, 'Bald free kvs Bald kbsdj', 1, 0, NULL, '2026-03-01 02:18:38', '2026-03-01 02:18:41'),
(347, 371000372, 1, 'Hi teh free jva vujad o hnd', 1, 0, NULL, '2026-03-01 02:23:23', '2026-03-01 02:23:25'),
(348, 371000372, 0, 'JV dldudzgh', 1, 0, NULL, '2026-03-01 02:23:36', '2026-03-01 02:23:39'),
(349, 371000372, 0, 'Jjfjfuxueuxghbguhghhjfhvçheycfgxjvf', 1, 0, NULL, '2026-03-01 02:24:48', '2026-03-01 02:24:50'),
(350, 371000372, 1, 'sfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsfsfsdfsdfsdfsfsfsfs', 1, 0, NULL, '2026-03-01 02:26:16', '2026-03-01 02:26:18'),
(351, 371000372, 0, 'gcash', 1, 0, NULL, '2026-03-02 03:33:00', '2026-03-02 12:33:51'),
(352, 371000372, 0, 'facebook', 1, 0, NULL, '2026-03-02 03:33:07', '2026-03-02 12:33:51'),
(353, 371000372, 0, 'gmail', 1, 0, NULL, '2026-03-02 03:33:28', '2026-03-02 12:33:51'),
(354, 371000372, 0, 'nigga', 1, 0, NULL, '2026-03-02 03:33:34', '2026-03-02 12:33:51'),
(355, 371000372, 0, 'fuck', 1, 0, NULL, '2026-03-02 03:33:38', '2026-03-02 12:33:51'),
(356, 371000372, 0, 'puta', 1, 0, NULL, '2026-03-02 03:33:43', '2026-03-02 12:33:51'),
(357, 371000372, 0, 'ulol', 1, 0, NULL, '2026-03-02 03:36:56', '2026-03-02 12:33:51'),
(358, 371000372, 0, 'pokpok', 1, 0, NULL, '2026-03-02 03:37:10', '2026-03-02 12:33:51'),
(359, 371000372, 0, 'pokpok', 1, 0, NULL, '2026-03-02 03:45:47', '2026-03-02 12:33:51'),
(360, 371000372, 0, 'niiger', 1, 0, NULL, '2026-03-02 03:45:52', '2026-03-02 12:33:51'),
(361, 371000372, 0, 'nigger', 1, 0, NULL, '2026-03-02 03:45:56', '2026-03-02 12:33:51'),
(362, 371000372, 0, 'pok pok\\', 1, 0, NULL, '2026-03-02 06:56:03', '2026-03-02 12:33:51'),
(363, 371000372, 0, 'pok pok', 1, 0, NULL, '2026-03-02 06:56:05', '2026-03-02 12:33:51'),
(364, 371000372, 0, 'poki', 1, 0, NULL, '2026-03-02 06:56:11', '2026-03-02 12:33:51'),
(365, 371000372, 0, 'puki', 1, 0, NULL, '2026-03-02 06:56:15', '2026-03-02 12:33:51'),
(366, 371000372, 0, 'poki', 1, 0, NULL, '2026-03-02 21:10:43', '2026-03-02 21:11:43'),
(367, 371000372, 0, 'pok pok', 1, 0, NULL, '2026-03-02 21:10:48', '2026-03-02 21:11:43'),
(368, 371000372, 0, 'pokpok', 1, 0, NULL, '2026-03-02 21:10:52', '2026-03-02 21:11:43'),
(369, 371000372, 1, 'puki', 1, 0, NULL, '2026-03-02 21:11:48', '2026-03-02 21:11:51'),
(371, 371000372, 0, 'pokpok', 1, 0, NULL, '2026-03-02 21:12:26', '2026-03-02 21:12:30'),
(372, 371000372, 0, 'poki', 1, 0, NULL, '2026-03-02 21:12:46', '2026-03-02 21:12:49'),
(374, 371000372, 0, 'puki', 1, 0, NULL, '2026-03-02 21:13:14', '2026-03-02 21:13:18'),
(375, 371000372, 0, 'puki', 1, 0, NULL, '2026-03-02 21:29:05', '2026-03-02 21:29:09'),
(376, 371000372, 0, 'nigga', 1, 0, NULL, '2026-03-02 21:30:18', '2026-03-02 21:30:22'),
(377, 371000372, 1, 'jiggs', 1, 0, NULL, '2026-03-02 21:30:28', '2026-03-02 21:30:34'),
(378, 1000002, 1, 'asdasdasdadadsasd', 0, 0, NULL, '2026-03-02 22:23:21', '2026-03-02 22:23:21'),
(379, 371000372, 0, 'hanalul', 1, 0, NULL, '2026-03-02 22:37:53', '2026-03-02 22:37:59'),
(380, 371000372, 1, 'eyos', 1, 0, NULL, '2026-03-02 22:38:04', '2026-03-02 22:38:07'),
(381, 1000372, 1, 'hi', 1, 0, NULL, '2026-03-02 22:38:22', '2026-03-02 22:43:05'),
(382, 1000372, 0, 'hi sir', 1, 0, NULL, '2026-03-02 22:43:10', '2026-03-02 22:43:22'),
(383, 371000372, 1, 'teh', 1, 0, NULL, '2026-03-02 22:43:31', '2026-03-02 22:43:37'),
(384, 1000372, 1, 'bro', 1, 0, NULL, '2026-03-02 22:49:54', '2026-03-02 22:49:59'),
(385, 1000372, 0, 'wtf', 1, 1, 'System: Suspicious Keyword Detected', '2026-03-02 22:50:03', '2026-03-02 22:52:36'),
(386, 371000372, 0, 'dd', 1, 0, NULL, '2026-03-02 22:52:43', '2026-03-02 23:08:39'),
(387, 1000372, 1, 'hello', 1, 0, NULL, '2026-03-02 22:59:51', '2026-03-02 22:59:58'),
(388, 1000372, 0, 'hi', 1, 0, NULL, '2026-03-02 23:00:02', '2026-03-02 23:00:06'),
(389, 2000372, 1, 'sds', 0, 0, NULL, '2026-03-02 23:03:00', '2026-03-02 23:03:00'),
(390, 371000372, 1, 'niiger', 1, 0, NULL, '2026-03-02 23:12:01', '2026-03-02 23:12:06'),
(391, 371000372, 1, 'nigga', 1, 0, NULL, '2026-03-02 23:12:12', '2026-03-02 23:12:18'),
(392, 371000372, 1, 'nigga', 1, 0, NULL, '2026-03-02 23:12:15', '2026-03-02 23:12:18'),
(393, 371000372, 0, 'nigga', 1, 0, NULL, '2026-03-02 23:28:36', '2026-03-02 23:29:36'),
(394, 371000372, 0, 'nigga', 1, 0, NULL, '2026-03-02 23:28:41', '2026-03-02 23:29:36'),
(395, 1000372, 1, 'hello', 1, 0, NULL, '2026-03-03 05:09:27', '2026-03-03 05:09:42'),
(396, 1000372, 0, 'hi bro', 1, 0, NULL, '2026-03-03 05:09:47', '2026-03-03 05:09:49'),
(397, 371000372, 0, 'yehey not na suspende', 1, 1, 'System: Suspicious Keyword Detected', '2026-03-03 05:10:45', '2026-03-03 05:10:57'),
(398, 371000372, 0, 'yehey not na suspended', 1, 1, 'System: Suspicious Keyword Detected', '2026-03-03 05:10:47', '2026-03-03 05:10:57'),
(399, 371000372, 0, 'suspended', 1, 1, 'System: Suspicious Keyword Detected', '2026-03-03 05:11:07', '2026-03-03 05:11:11'),
(400, 371000372, 0, 'yehey', 1, 0, NULL, '2026-03-03 05:11:13', '2026-03-03 05:11:17'),
(401, 371000372, 0, 'gcash', 1, 0, NULL, '2026-03-03 05:11:23', '2026-03-03 05:11:27'),
(402, 371000372, 0, 'viber', 1, 0, NULL, '2026-03-03 05:13:11', '2026-03-03 05:13:15'),
(403, 371000372, 1, 'hi beh', 1, 0, NULL, '2026-03-03 10:15:48', '2026-03-03 10:24:45'),
(404, 371000372, 1, 'beh', 1, 0, NULL, '2026-03-03 10:16:01', '2026-03-03 10:24:45'),
(405, 371000372, 1, 'ss', 1, 0, NULL, '2026-03-03 10:17:58', '2026-03-03 10:24:45'),
(406, 371000372, 1, 'ssss', 1, 0, NULL, '2026-03-03 10:18:09', '2026-03-03 10:24:45'),
(407, 371000372, 1, 'ss', 1, 0, NULL, '2026-03-03 10:24:29', '2026-03-03 10:24:45'),
(408, 371000372, 0, 'hi', 1, 0, NULL, '2026-03-03 10:25:50', '2026-03-03 18:30:40'),
(409, 371000372, 0, 'dfg', 1, 0, NULL, '2026-03-03 18:30:06', '2026-03-03 18:30:40'),
(410, 371000372, 1, 'sss', 1, 0, NULL, '2026-03-03 18:30:46', '2026-03-03 18:30:48'),
(411, 371000372, 1, 'sdsdfsdfsdfsf', 1, 0, NULL, '2026-03-03 18:33:51', '2026-03-03 18:39:49'),
(412, 371000372, 1, 'sdsds', 1, 0, NULL, '2026-03-03 18:37:30', '2026-03-03 18:39:49'),
(413, 371000372, 0, 'asdad', 1, 0, NULL, '2026-03-03 18:44:36', '2026-03-03 18:44:39'),
(414, 371000372, 1, 'Hello thereee hakfok', 1, 0, NULL, '2026-03-03 20:31:59', '2026-03-03 20:32:08'),
(416, 371000372, 1, 'Uuhyuuh', 1, 0, NULL, '2026-03-03 20:32:43', '2026-03-03 20:32:46'),
(417, 371000372, 1, '', 1, 0, NULL, '2026-03-03 20:40:12', '2026-03-03 20:40:23'),
(418, 371000372, 0, 'ck cutie', 1, 0, NULL, '2026-03-03 20:45:14', '2026-03-03 20:45:29'),
(419, 371000372, 0, 'ack', 1, 0, NULL, '2026-03-03 20:45:40', '2026-03-03 20:45:44'),
(420, 371000372, 0, 'heyaaa', 1, 0, NULL, '2026-03-03 20:46:49', '2026-03-03 20:49:35'),
(421, 371000372, 0, 'hi nigga free kbabtosa', 1, 1, 'System: Suspicious Keyword Detected', '2026-03-03 20:49:56', '2026-03-03 20:49:59'),
(422, 372000392, 1, 'hello', 1, 0, NULL, '2026-03-03 21:23:49', '2026-03-03 21:24:21');

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
(7, 246, 'messages/ypyI3pkZkvu6VQtu9idy6v3X6P6eSGlibrCQsD3R.pdf', 'epic.pdf', 'application/pdf', '2026-02-08 03:33:43'),
(8, 248, 'messages/mXFY5qs6wlYturTaNNxDobHlp9vzETNwiK5LdwTO.docx', 'JIMENEZ, SHANE HART D..docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '2026-02-08 03:34:05'),
(10, 314, 'messages/1f1XraBKAeJtpNtExDhUzerIRXSosrWJSEFjngbk.docx', 'JIMENEZ, SHANE HART D..docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '2026-02-08 15:57:43'),
(11, 339, 'messages/QPDfnsnG4wWEdHdcydB1Xs1BWxd6EcQ8CzJ0Woc3.jpg', 'IMG_20251212_212838.jpg', 'image/jpeg', '2026-03-01 09:33:48'),
(12, 378, 'messages/5mEpnIiguYqhXW5FU8Y582ImE8g3tEneUBC8XH5g.jpg', 'c59d053f-94c9-4085-9380-7e85badcaf9e.jpg', 'image/jpeg', '2026-03-03 06:23:21'),
(13, 417, 'messages/re71ltyTXf2LTDUn6wngt74XYxYyyo3QIZprE6LX.jpg', 'IMG_20260302_101315.jpg', 'image/jpeg', '2026-03-04 04:40:12');

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
(6, '2026_03_02_000001_add_feature_columns_and_project_posts', 5),
(7, '2026_03_03_000002_create_admin_activity_logs_table', 5),
(8, '2026_03_04_042038_fix_item_files_file_id_auto_increment', 5),
(9, '2026_03_05_151421_create_downpayment_payments_table', 6),
(10, '2026_03_06_000001_rename_project_posts_to_showcases', 7),
(11, '2026_03_06_000002_rename_tagged_user_to_tagged_project_in_showcases', 8),
(12, '2026_03_06_000003_drop_tagged_project_id_from_showcases', 9),
(13, '2026_03_06_000004_update_showcase_statuses_for_moderation', 10),
(14, '2026_03_06_000005_create_content_reports_table', 11),
(15, '2026_03_06_000006_rename_post_report_columns_to_post_type_and_post_id', 12),
(16, '2026_03_08_071739_create_review_reports_and_report_attachments_tables', 13);

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
(1564, 1056, 1810, 928, 'Project Batumbakal', 'Project Batumbakal', 'not_started', NULL, '2026-02-23 00:00:00', '2026-03-07 23:59:59', NULL, NULL, 'approved', NULL, '2026-02-23 00:12:07', '2026-02-25 07:15:26'),
(1567, 1053, 1809, 931, 'Project Batumbakal', 'Project Batumbakal', 'not_started', NULL, '2026-03-04 00:00:00', '2026-04-30 23:59:59', NULL, NULL, 'approved', NULL, '2026-03-03 20:22:42', '2026-03-05 06:20:44'),
(1568, 1057, 1809, 932, 'Winter Wonderland', 'Winter Wonderland', 'not_started', NULL, '2026-03-04 00:00:00', '2026-04-30 23:59:59', NULL, NULL, 'approved', NULL, '2026-03-03 21:29:46', '2026-03-03 21:34:56'),
(1574, 1068, 1687, 936, 'Phase 1: Structural Assessment & Demolition', 'Initial assessment and demolition of old structures', 'completed', NULL, '2026-01-18 11:16:10', '2026-01-28 11:16:10', NULL, NULL, 'approved', NULL, '2026-03-09 03:16:10', '2026-03-09 03:16:10'),
(1575, 1068, 1687, 936, 'Phase 2: Structural Repairs', 'Foundation and structural repairs', 'completed', NULL, '2026-01-29 11:16:10', '2026-02-12 11:16:10', NULL, NULL, 'approved', NULL, '2026-03-09 03:16:10', '2026-03-09 03:16:10'),
(1576, 1068, 1687, 936, 'Phase 3: Electrical & Plumbing', 'Complete electrical and plumbing installation', 'in_progress', NULL, '2026-02-13 11:16:10', '2026-03-19 11:16:10', NULL, NULL, 'approved', NULL, '2026-03-09 03:16:10', '2026-03-09 03:16:10'),
(1577, 1068, 1687, 936, 'Phase 4: Interior Finishing', 'Painting, flooring, and interior fixtures', 'not_started', NULL, '2026-03-20 11:16:10', '2026-04-13 11:16:10', NULL, NULL, 'approved', NULL, '2026-03-09 03:16:10', '2026-03-09 03:16:10'),
(1578, 1068, 1687, 936, 'Phase 5: Final Inspection & Handover', 'Final inspection and project handover', 'not_started', NULL, '2026-04-14 11:16:10', '2026-04-23 11:16:10', NULL, NULL, 'approved', NULL, '2026-03-09 03:16:10', '2026-03-09 03:16:10'),
(1579, 1070, 1687, 937, 'Phase 1: Structural Assessment & Demolition', 'Initial assessment and demolition of old structures', 'completed', NULL, '2026-01-18 11:16:56', '2026-01-28 11:16:56', NULL, NULL, 'approved', NULL, '2026-03-09 03:16:56', '2026-03-09 03:16:56'),
(1580, 1070, 1687, 937, 'Phase 2: Structural Repairs', 'Foundation and structural repairs', 'completed', NULL, '2026-01-29 11:16:56', '2026-02-12 11:16:56', NULL, NULL, 'approved', NULL, '2026-03-09 03:16:56', '2026-03-09 03:16:56'),
(1581, 1070, 1687, 937, 'Phase 3: Electrical & Plumbing', 'Complete electrical and plumbing installation', 'in_progress', NULL, '2026-02-13 11:16:56', '2026-03-19 11:16:56', NULL, NULL, 'approved', NULL, '2026-03-09 03:16:56', '2026-03-09 03:16:56'),
(1582, 1070, 1687, 937, 'Phase 4: Interior Finishing', 'Painting, flooring, and interior fixtures', 'not_started', NULL, '2026-03-20 11:16:56', '2026-04-13 11:16:56', NULL, NULL, 'approved', NULL, '2026-03-09 03:16:56', '2026-03-09 03:16:56'),
(1583, 1070, 1687, 937, 'Phase 5: Final Inspection & Handover', 'Final inspection and project handover', 'not_started', NULL, '2026-04-14 11:16:56', '2026-04-23 11:16:56', NULL, NULL, 'approved', NULL, '2026-03-09 03:16:56', '2026-03-09 03:16:56'),
(1584, 1072, 1687, 938, 'Phase 1: Structural Assessment & Demolition', 'Initial assessment and demolition of old structures', 'completed', NULL, '2026-01-18 11:18:30', '2026-01-28 11:18:30', NULL, NULL, 'approved', NULL, '2026-03-09 03:18:30', '2026-03-09 03:18:30'),
(1585, 1072, 1687, 938, 'Phase 2: Structural Repairs', 'Foundation and structural repairs', 'completed', NULL, '2026-01-29 11:18:30', '2026-02-12 11:18:30', NULL, NULL, 'approved', NULL, '2026-03-09 03:18:30', '2026-03-09 03:18:30'),
(1586, 1072, 1687, 938, 'Phase 3: Electrical & Plumbing', 'Complete electrical and plumbing installation', 'in_progress', NULL, '2026-02-13 11:18:30', '2026-03-19 11:18:30', NULL, NULL, 'approved', NULL, '2026-03-09 03:18:30', '2026-03-09 03:18:30'),
(1587, 1072, 1687, 938, 'Phase 4: Interior Finishing', 'Painting, flooring, and interior fixtures', 'not_started', NULL, '2026-03-20 11:18:30', '2026-04-13 11:18:30', NULL, NULL, 'approved', NULL, '2026-03-09 03:18:30', '2026-03-09 03:18:30'),
(1588, 1072, 1687, 938, 'Phase 5: Final Inspection & Handover', 'Final inspection and project handover', 'not_started', NULL, '2026-04-14 11:18:30', '2026-04-23 11:18:30', NULL, NULL, 'approved', NULL, '2026-03-09 03:18:30', '2026-03-09 03:18:30'),
(1589, 1074, 1687, 939, 'Phase 1: Structural Assessment & Demolition', 'Initial assessment and demolition of old structures', 'completed', NULL, '2026-01-18 11:19:22', '2026-01-28 11:19:22', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:22', '2026-03-09 03:19:22'),
(1590, 1074, 1687, 939, 'Phase 2: Structural Repairs', 'Foundation and structural repairs', 'completed', NULL, '2026-01-29 11:19:22', '2026-02-12 11:19:22', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:22', '2026-03-09 03:19:22'),
(1591, 1074, 1687, 939, 'Phase 3: Electrical & Plumbing', 'Complete electrical and plumbing installation', 'in_progress', NULL, '2026-02-13 11:19:22', '2026-03-19 11:19:22', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:22', '2026-03-09 03:19:22'),
(1592, 1074, 1687, 939, 'Phase 4: Interior Finishing', 'Painting, flooring, and interior fixtures', 'not_started', NULL, '2026-03-20 11:19:22', '2026-04-13 11:19:22', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:22', '2026-03-09 03:19:22'),
(1593, 1074, 1687, 939, 'Phase 5: Final Inspection & Handover', 'Final inspection and project handover', 'not_started', NULL, '2026-04-14 11:19:22', '2026-04-23 11:19:22', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:22', '2026-03-09 03:19:22'),
(1594, 1075, 1687, 940, 'Foundation & Groundwork', 'Site preparation and foundation work', 'completed', NULL, '2025-09-20 11:19:22', '2025-10-10 11:19:22', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:22', '2026-03-09 03:19:22'),
(1595, 1075, 1687, 940, 'Structural Framework', 'Building framework and roofing', 'completed', NULL, '2025-10-11 11:19:22', '2025-11-09 11:19:22', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:22', '2026-03-09 03:19:22'),
(1596, 1075, 1687, 940, 'MEP Installation', 'Mechanical, Electrical, and Plumbing systems', 'completed', NULL, '2025-11-10 11:19:22', '2025-12-19 11:19:22', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:22', '2026-03-09 03:19:22'),
(1597, 1075, 1687, 940, 'Interior & Exterior Finishing', 'Complete finishing work', 'completed', NULL, '2025-12-20 11:19:22', '2026-01-28 11:19:22', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:22', '2026-03-09 03:19:22'),
(1598, 1075, 1687, 940, 'Landscaping & Pool', 'Garden landscaping and pool construction', 'completed', NULL, '2026-01-29 11:19:22', '2026-02-22 11:19:22', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:22', '2026-03-09 03:19:22'),
(1599, 1075, 1687, 940, 'Final Handover', 'Final inspection and handover', 'completed', NULL, '2026-02-23 11:19:22', '2026-02-27 11:19:22', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:22', '2026-03-09 03:19:22'),
(1600, 1076, 1687, 941, 'Basement & Foundation', 'Excavation and foundation work', 'completed', NULL, '2025-12-19 11:19:22', '2026-01-08 11:19:22', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:22', '2026-03-08 21:57:21'),
(1601, 1076, 1687, 941, 'Ground Floor Structure', 'Ground floor concrete work', 'in_progress', NULL, '2026-01-09 11:19:22', '2026-03-29 11:19:22', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:22', '2026-03-08 21:57:21'),
(1602, 1076, 1687, 941, 'Upper Floors', 'Construction of floors 2-4', 'not_started', NULL, '2026-03-30 11:19:22', '2026-05-29 00:00:00', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:22', '2026-03-08 21:57:21'),
(1603, 1078, 1687, 942, 'Phase 1: Structural Assessment & Demolition', 'Initial assessment and demolition of old structures', 'completed', NULL, '2026-01-18 11:19:53', '2026-01-28 11:19:53', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:53', '2026-03-09 03:19:53'),
(1604, 1078, 1687, 942, 'Phase 2: Structural Repairs', 'Foundation and structural repairs', 'completed', NULL, '2026-01-29 11:19:53', '2026-02-12 11:19:53', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:53', '2026-03-09 03:19:53'),
(1605, 1078, 1687, 942, 'Phase 3: Electrical & Plumbing', 'Complete electrical and plumbing installation', 'in_progress', NULL, '2026-02-13 11:19:53', '2026-03-19 11:19:53', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:53', '2026-03-09 03:19:53'),
(1606, 1078, 1687, 942, 'Phase 4: Interior Finishing', 'Painting, flooring, and interior fixtures', 'not_started', NULL, '2026-03-20 11:19:53', '2026-04-13 11:19:53', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:53', '2026-03-09 03:19:53'),
(1607, 1078, 1687, 942, 'Phase 5: Final Inspection & Handover', 'Final inspection and project handover', 'not_started', NULL, '2026-04-14 11:19:53', '2026-04-23 11:19:53', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:53', '2026-03-09 03:19:53'),
(1608, 1079, 1687, 943, 'Foundation & Groundwork', 'Site preparation and foundation work', 'completed', NULL, '2025-09-20 11:19:53', '2025-10-10 11:19:53', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:53', '2026-03-09 03:19:53'),
(1609, 1079, 1687, 943, 'Structural Framework', 'Building framework and roofing', 'completed', NULL, '2025-10-11 11:19:53', '2025-11-09 11:19:53', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:53', '2026-03-09 03:19:53'),
(1610, 1079, 1687, 943, 'MEP Installation', 'Mechanical, Electrical, and Plumbing systems', 'completed', NULL, '2025-11-10 11:19:53', '2025-12-19 11:19:53', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:53', '2026-03-09 03:19:53'),
(1611, 1079, 1687, 943, 'Interior & Exterior Finishing', 'Complete finishing work', 'completed', NULL, '2025-12-20 11:19:53', '2026-01-28 11:19:53', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:53', '2026-03-09 03:19:53'),
(1612, 1079, 1687, 943, 'Landscaping & Pool', 'Garden landscaping and pool construction', 'completed', NULL, '2026-01-29 11:19:53', '2026-02-22 11:19:53', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:53', '2026-03-09 03:19:53'),
(1613, 1079, 1687, 943, 'Final Handover', 'Final inspection and handover', 'completed', NULL, '2026-02-23 11:19:53', '2026-02-27 11:19:53', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:53', '2026-03-09 03:19:53'),
(1614, 1080, 1687, 944, 'Basement & Foundation', 'Excavation and foundation work', 'completed', NULL, '2025-12-19 11:19:53', '2026-01-08 11:19:53', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:53', '2026-03-09 03:19:53'),
(1615, 1080, 1687, 944, 'Ground Floor Structure', 'Ground floor concrete work', 'in_progress', NULL, '2026-01-09 11:19:53', '2026-03-29 11:19:53', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:53', '2026-03-09 03:19:53'),
(1616, 1080, 1687, 944, 'Upper Floors', 'Construction of floors 2-4', 'not_started', NULL, '2026-03-30 11:19:53', '2026-05-29 00:00:00', NULL, NULL, 'approved', NULL, '2026-03-09 03:19:53', '2026-03-09 05:36:41');

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
(5, 2791, '2026-03-02 00:00:00', '2026-03-02 00:00:00', 4, 379, '2026-02-27 12:12:04', 'project_update_approved', '2026-02-27 04:12:04', '2026-02-27 04:12:04'),
(6, 2797, '2026-03-18 23:59:59', '2026-04-17 23:59:59', 5, 392, '2026-03-04 05:34:56', 'Project update #5 approved', '2026-03-03 21:34:56', '2026-03-03 21:34:56'),
(7, 2798, '2026-03-31 23:59:59', '2026-04-30 23:59:59', 5, 392, '2026-03-04 05:34:56', 'Project update #5 approved', '2026-03-03 21:34:56', '2026-03-03 21:34:56'),
(8, 2822, '2026-03-04 11:19:22', '2026-03-19 11:19:22', NULL, 1876, '2026-03-04 11:19:22', 'Timeline extended due to weather delays', NULL, NULL),
(9, 2823, '2026-03-29 11:19:22', '2026-04-13 11:19:22', NULL, 1876, '2026-03-04 11:19:22', 'Timeline extended due to weather delays', NULL, NULL),
(10, 2824, '2026-04-08 11:19:22', '2026-04-23 11:19:22', NULL, 1876, '2026-03-04 11:19:22', 'Timeline extended due to weather delays', NULL, NULL),
(11, 2839, '2026-03-04 11:19:53', '2026-03-19 11:19:53', NULL, 1876, '2026-03-04 11:19:53', 'Timeline extended due to weather delays', NULL, NULL),
(12, 2840, '2026-03-29 11:19:53', '2026-04-13 11:19:53', NULL, 1876, '2026-03-04 11:19:53', 'Timeline extended due to weather delays', NULL, NULL),
(13, 2841, '2026-04-08 11:19:53', '2026-04-23 11:19:53', NULL, 1876, '2026-03-04 11:19:53', 'Timeline extended due to weather delays', NULL, NULL),
(14, 2841, '2026-04-23 11:19:53', '2026-04-24 00:00:00', 11, 101, '2026-03-09 05:29:15', 'asdasdasdasdasdasdasdasdasdas', '2026-03-08 21:29:15', '2026-03-08 21:29:15'),
(15, 2841, '2026-04-24 00:00:00', '2026-04-25 00:00:00', 12, 101, '2026-03-09 05:31:24', 'asdasdadasdadasdadasdasdas', '2026-03-08 21:31:24', '2026-03-08 21:31:24'),
(16, 2853, '2026-05-28 11:19:53', '2026-05-29 00:00:00', 13, 101, '2026-03-09 05:36:41', 'asdadasdasdasdasdas', '2026-03-08 21:36:41', '2026-03-08 21:36:41'),
(17, 2836, '2026-05-28 11:19:22', '2026-05-29 00:00:00', 14, 101, '2026-03-09 05:57:21', 'adasdasdasdasdasdasdasda', '2026-03-08 21:57:21', '2026-03-08 21:57:21'),
(18, 2840, '2026-04-13 11:19:53', '2026-04-13 00:00:00', NULL, 1, '2026-03-09 06:16:15', 'Manual date adjustment by admin', NULL, NULL),
(23, 2852, '2026-03-29 11:19:53', '2026-03-31 00:00:00', NULL, 1, '2026-03-09 06:31:59', 'Bulk adjustment: HOTDOGHOTDOGHOTDOGHOTDOGHOTDOGHOTDOGHOTDOG', '2026-03-08 22:31:59', '2026-03-08 22:31:59');

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
(2776, 1559, 1, 50.00, 'PHASE 1', 'PHASE 1 DESC', 27495000.00, NULL, 0.00, 'in_progress', NULL, NULL, '2026-03-31 23:59:59', NULL, 0, 0, NULL, NULL, '2026-03-05 14:19:52'),
(2777, 1559, 2, 50.00, 'PHASE 2', 'PHASE 2 DESC', 27495000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-02-26 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2778, 1560, 1, 30.00, 'Foundation and Framework', 'it is what it is', 6000000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-04-17 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2779, 1560, 2, 50.00, 'Madami gagawin', 'Basta madami gagawin', 12000000.00, NULL, 0.00, 'completed', NULL, NULL, '2027-01-30 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2780, 1560, 3, 10.00, 'Tapos na to by this time', 'yes', 2000000.00, NULL, 0.00, 'completed', NULL, NULL, '2027-12-31 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2781, 1560, 4, 10.00, 'eeeeeeeeee', 'eeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', 1.00, NULL, 0.00, 'not_started', NULL, NULL, '2028-01-18 22:53:08', NULL, 0, 0, NULL, NULL, NULL),
(2786, 1563, 1, 50.00, 'giobgy', 'ginvf', 3000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-02-25 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2787, 1563, 2, 50.00, 'dyondsuig', '', 3000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-02-28 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2790, 1564, 1, 33.33, 'Foundations', 'Foundation ngani', 20000000.00, NULL, 0.00, 'in_progress', '2026-02-23 00:00:00', NULL, '2026-02-25 00:00:00', '2026-02-18 23:59:59', 1, 1, '2026-02-25', NULL, '2026-02-27 12:12:04'),
(2791, 1564, 2, 35.00, 'Doners', 'downers', 20000000.00, 21000000.00, 1000000.00, 'in_progress', '2026-02-26 00:00:00', NULL, '2026-03-02 00:00:00', '2026-02-28 23:59:59', 1, 1, '2026-03-02', NULL, '2026-02-27 12:12:04'),
(2792, 1564, 3, 31.67, 'extension', 'hdkdykkydkhdkhd', 19000000.00, NULL, 0.00, 'not_started', '2026-03-03 00:00:00', NULL, '2026-03-07 23:59:59', '2026-02-28 23:59:59', 1, 1, NULL, NULL, '2026-02-27 12:12:04'),
(2795, 1567, 1, 50.00, 'First', 'foundations', 15000000.00, NULL, 0.00, 'not_started', '2026-03-04 00:00:00', NULL, '2026-03-31 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2796, 1567, 2, 50.00, 'Second', 'finishing', 15000000.00, NULL, 0.00, 'not_started', '2026-04-01 00:00:00', NULL, '2026-04-30 23:59:59', NULL, 0, 0, NULL, NULL, NULL),
(2797, 1568, 1, 49.72, 'First', 'yes first', 4475000.00, NULL, 0.00, 'not_started', '2026-03-05 00:00:00', NULL, '2026-04-17 23:59:59', '2026-03-18 23:59:59', 1, 1, NULL, NULL, '2026-03-04 05:34:56'),
(2798, 1568, 2, 49.72, 'Secondt', 'second the motion', 4475000.00, NULL, 0.00, 'not_started', '2026-03-19 00:00:00', NULL, '2026-04-30 23:59:59', '2026-03-31 23:59:59', 1, 1, NULL, NULL, '2026-03-04 05:34:56'),
(2799, 1574, 1, 100.00, 'Site Assessment & Planning', 'Complete site assessment and demolition work', 800000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-01-28 11:16:10', NULL, 0, 0, NULL, NULL, NULL),
(2800, 1575, 2, 100.00, 'Foundation Repair', 'Repair and strengthen foundation', 1200000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-02-12 11:16:10', NULL, 0, 0, NULL, NULL, NULL),
(2801, 1576, 3, 60.00, 'Electrical Wiring Installation', 'Install complete electrical system', 1500000.00, NULL, 0.00, 'in_progress', NULL, NULL, '2026-03-19 11:16:10', NULL, 0, 0, NULL, NULL, NULL),
(2802, 1577, 4, 0.00, 'Wall Painting & Finishing', 'Paint all walls and install fixtures', 1800000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-04-13 11:16:10', NULL, 0, 0, NULL, NULL, NULL),
(2803, 1578, 5, 0.00, 'Final Quality Check', 'Conduct final inspection', 700000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-04-23 11:16:10', NULL, 0, 0, NULL, NULL, NULL),
(2806, 1579, 1, 100.00, 'Site Assessment & Planning', 'Complete site assessment and demolition work', 800000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-01-28 11:16:56', NULL, 0, 0, NULL, NULL, NULL),
(2807, 1580, 2, 100.00, 'Foundation Repair', 'Repair and strengthen foundation', 1200000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-02-12 11:16:56', NULL, 0, 0, NULL, NULL, NULL),
(2808, 1581, 3, 60.00, 'Electrical Wiring Installation', 'Install complete electrical system', 1500000.00, NULL, 0.00, 'in_progress', NULL, NULL, '2026-03-19 11:16:56', NULL, 0, 0, NULL, NULL, NULL),
(2809, 1582, 4, 0.00, 'Wall Painting & Finishing', 'Paint all walls and install fixtures', 1800000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-04-13 11:16:56', NULL, 0, 0, NULL, NULL, NULL),
(2810, 1583, 5, 0.00, 'Final Quality Check', 'Conduct final inspection', 700000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-04-23 11:16:56', NULL, 0, 0, NULL, NULL, NULL),
(2813, 1584, 1, 100.00, 'Site Assessment & Planning', 'Complete site assessment and demolition work', 800000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-01-28 11:18:30', NULL, 0, 0, NULL, NULL, NULL),
(2814, 1585, 2, 100.00, 'Foundation Repair', 'Repair and strengthen foundation', 1200000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-02-12 11:18:30', NULL, 0, 0, NULL, NULL, NULL),
(2815, 1586, 3, 60.00, 'Electrical Wiring Installation', 'Install complete electrical system', 1500000.00, NULL, 0.00, 'in_progress', NULL, NULL, '2026-03-19 11:18:30', NULL, 0, 0, NULL, NULL, NULL),
(2816, 1587, 4, 0.00, 'Wall Painting & Finishing', 'Paint all walls and install fixtures', 1800000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-04-13 11:18:30', NULL, 0, 0, NULL, NULL, NULL),
(2817, 1588, 5, 0.00, 'Final Quality Check', 'Conduct final inspection', 700000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-04-23 11:18:30', NULL, 0, 0, NULL, NULL, NULL),
(2820, 1589, 1, 100.00, 'Site Assessment & Planning', 'Complete site assessment and demolition work', 800000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-01-28 11:19:22', NULL, 0, 0, NULL, NULL, NULL),
(2821, 1590, 2, 100.00, 'Foundation Repair', 'Repair and strengthen foundation', 1200000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-02-12 11:19:22', NULL, 0, 0, NULL, NULL, NULL),
(2822, 1591, 3, 60.00, 'Electrical Wiring Installation', 'Install complete electrical system', 1500000.00, NULL, 0.00, 'in_progress', NULL, NULL, '2026-03-19 11:19:22', NULL, 0, 0, NULL, NULL, NULL),
(2823, 1592, 4, 0.00, 'Wall Painting & Finishing', 'Paint all walls and install fixtures', 1800000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-04-13 11:19:22', NULL, 0, 0, NULL, NULL, NULL),
(2824, 1593, 5, 0.00, 'Final Quality Check', 'Conduct final inspection', 700000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-04-23 11:19:22', NULL, 0, 0, NULL, NULL, NULL),
(2827, 1594, 1, 100.00, 'Excavation & Foundation', 'Complete excavation and foundation work', 1500000.00, NULL, 0.00, 'completed', NULL, NULL, '2025-10-10 11:19:22', NULL, 0, 0, NULL, NULL, NULL),
(2828, 1595, 2, 100.00, 'Framework Construction', 'Build complete structural framework', 2000000.00, NULL, 0.00, 'completed', NULL, NULL, '2025-11-09 11:19:22', NULL, 0, 0, NULL, NULL, NULL),
(2829, 1596, 3, 100.00, 'Electrical & Plumbing', 'Install all MEP systems', 2500000.00, NULL, 0.00, 'completed', NULL, NULL, '2025-12-19 11:19:22', NULL, 0, 0, NULL, NULL, NULL),
(2830, 1597, 4, 100.00, 'Interior Finishing', 'Complete all finishing work', 2000000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-01-28 11:19:22', NULL, 0, 0, NULL, NULL, NULL),
(2831, 1598, 5, 100.00, 'Swimming Pool Construction', 'Construct swimming pool and landscaping', 1000000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-02-22 11:19:22', NULL, 0, 0, NULL, NULL, NULL),
(2832, 1599, 6, 100.00, 'Final Inspection', 'Final quality inspection', 500000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-02-27 11:19:22', NULL, 0, 0, NULL, NULL, NULL),
(2834, 1600, 1, 100.00, 'Foundation Work', 'Complete basement and foundation', 4000000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-01-08 11:19:22', NULL, 0, 0, NULL, NULL, NULL),
(2835, 1601, 2, 40.00, 'Ground Floor Concrete', 'Ground floor structural work', 3500000.00, NULL, 0.00, 'in_progress', NULL, NULL, '2026-03-29 11:19:22', NULL, 0, 0, NULL, NULL, NULL),
(2836, 1602, 3, 0.00, 'Upper Floor Construction', 'Construct floors 2-4', 6500000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-05-29 00:00:00', '2026-05-28 11:19:22', 1, 1, NULL, NULL, NULL),
(2837, 1603, 1, 100.00, 'Site Assessment & Planning', 'Complete site assessment and demolition work', 800000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-01-28 11:19:53', NULL, 0, 0, NULL, NULL, NULL),
(2838, 1604, 2, 100.00, 'Foundation Repair', 'Repair and strengthen foundation', 1200000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-02-12 11:19:53', NULL, 0, 0, NULL, NULL, NULL),
(2839, 1605, 3, 60.00, 'Electrical Wiring Installation', 'Install complete electrical system', 1500000.00, NULL, 0.00, 'in_progress', NULL, NULL, '2026-03-19 11:19:53', NULL, 0, 0, NULL, NULL, NULL),
(2840, 1606, 4, 0.00, 'Wall Painting & Finishing', 'Paint all walls and install fixturesaa', 1800000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-04-13 00:00:00', '2026-04-13 11:19:53', 1, 1, NULL, NULL, NULL),
(2841, 1607, 5, 0.00, 'Final Quality Check', 'Conduct final inspection', 700000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-04-25 00:00:00', '2026-04-23 11:19:53', 1, 2, NULL, NULL, NULL),
(2844, 1608, 1, 100.00, 'Excavation & Foundation', 'Complete excavation and foundation work', 1500000.00, NULL, 0.00, 'completed', NULL, NULL, '2025-10-10 11:19:53', NULL, 0, 0, NULL, NULL, NULL),
(2845, 1609, 2, 100.00, 'Framework Construction', 'Build complete structural framework', 2000000.00, NULL, 0.00, 'completed', NULL, NULL, '2025-11-09 11:19:53', NULL, 0, 0, NULL, NULL, NULL),
(2846, 1610, 3, 100.00, 'Electrical & Plumbing', 'Install all MEP systems', 2500000.00, NULL, 0.00, 'completed', NULL, NULL, '2025-12-19 11:19:53', NULL, 0, 0, NULL, NULL, NULL),
(2847, 1611, 4, 100.00, 'Interior Finishing', 'Complete all finishing work', 2000000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-01-28 11:19:53', NULL, 0, 0, NULL, NULL, NULL),
(2848, 1612, 5, 100.00, 'Swimming Pool Construction', 'Construct swimming pool and landscaping', 1000000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-02-22 11:19:53', NULL, 0, 0, NULL, NULL, NULL),
(2849, 1613, 6, 100.00, 'Final Inspection', 'Final quality inspection', 500000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-02-27 11:19:53', NULL, 0, 0, NULL, NULL, NULL),
(2851, 1614, 1, 100.00, 'Foundation Work', 'Complete basement and foundatison', 4000000.00, NULL, 0.00, 'completed', NULL, NULL, '2026-01-08 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(2852, 1615, 2, 40.00, 'Ground Floor Concrete', 'Ground floor structural work', 3500000.00, NULL, 0.00, 'in_progress', NULL, NULL, '2026-03-31 00:00:00', '2026-03-29 11:19:53', 1, 1, NULL, NULL, NULL),
(2853, 1616, 3, 0.00, 'Upper Floor Construction', 'Construct floors 2-4', 6500000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-05-29 00:00:00', '2026-05-28 11:19:53', 1, 1, NULL, NULL, NULL);

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
(825, 2790, 1056, 1819, 2059, 19000000.00, 'bank_transfer', '01018273628190383', 'payments/receipts/1771850637_699c4b8d02e1c.jpg', '2026-02-23', 'approved', NULL, '2026-02-23 12:45:54'),
(828, 2813, 1072, 1687, 1876, 800000.00, 'bank_transfer', 'TXN-2024-001', 'receipts/milestone_2813.jpg', '2026-01-18', 'approved', NULL, NULL),
(829, 2814, 1072, 1687, 1876, 1200000.00, 'bank_transfer', 'TXN-2024-002', 'receipts/milestone_2814.jpg', '2026-01-28', 'approved', NULL, NULL),
(831, 2820, 1074, 1687, 1876, 800000.00, 'bank_transfer', 'TXN-2024-001', 'receipts/milestone_2820.jpg', '2026-01-18', 'approved', NULL, NULL),
(832, 2821, 1074, 1687, 1876, 1200000.00, 'bank_transfer', 'TXN-2024-002', 'receipts/milestone_2821.jpg', '2026-01-28', 'approved', NULL, NULL),
(834, 2827, 1075, 1687, 1876, 1500000.00, 'bank_transfer', 'TXN-COMP-001', 'receipts/completed_2827.jpg', '2025-10-12', 'approved', NULL, NULL),
(835, 2828, 1075, 1687, 1876, 2000000.00, 'bank_transfer', 'TXN-COMP-002', 'receipts/completed_2828.jpg', '2025-11-11', 'approved', NULL, NULL),
(836, 2829, 1075, 1687, 1876, 2500000.00, 'bank_transfer', 'TXN-COMP-003', 'receipts/completed_2829.jpg', '2025-12-21', 'approved', NULL, NULL),
(837, 2830, 1075, 1687, 1876, 2000000.00, 'bank_transfer', 'TXN-COMP-004', 'receipts/completed_2830.jpg', '2026-01-30', 'approved', NULL, NULL),
(838, 2831, 1075, 1687, 1876, 1000000.00, 'bank_transfer', 'TXN-COMP-005', 'receipts/completed_2831.jpg', '2026-02-24', 'approved', NULL, NULL),
(839, 2832, 1075, 1687, 1876, 500000.00, 'bank_transfer', 'TXN-COMP-006', 'receipts/completed_2832.jpg', '2026-03-01', 'approved', NULL, NULL),
(841, 2834, 1076, 1687, 1876, 4000000.00, 'bank_transfer', 'TXN-HALT-MS-001', 'receipts/halt_milestone_1.jpg', '2026-01-13', 'approved', NULL, NULL),
(842, 2837, 1078, 1687, 1876, 800000.00, 'bank_transfer', 'TXN-2024-001', 'receipts/milestone_2837.jpg', '2026-01-18', 'approved', NULL, NULL),
(843, 2838, 1078, 1687, 1876, 1200000.00, 'bank_transfer', 'TXN-2024-002', 'receipts/milestone_2838.jpg', '2026-01-28', 'approved', NULL, NULL),
(845, 2844, 1079, 1687, 1876, 1500000.00, 'bank_transfer', 'TXN-COMP-001', 'receipts/completed_2844.jpg', '2025-10-12', 'approved', NULL, NULL),
(846, 2845, 1079, 1687, 1876, 2000000.00, 'bank_transfer', 'TXN-COMP-002', 'receipts/completed_2845.jpg', '2025-11-11', 'approved', NULL, NULL),
(847, 2846, 1079, 1687, 1876, 2500000.00, 'bank_transfer', 'TXN-COMP-003', 'receipts/completed_2846.jpg', '2025-12-21', 'approved', NULL, NULL),
(848, 2847, 1079, 1687, 1876, 2000000.00, 'bank_transfer', 'TXN-COMP-004', 'receipts/completed_2847.jpg', '2026-01-30', 'approved', NULL, NULL),
(849, 2848, 1079, 1687, 1876, 1000000.00, 'bank_transfer', 'TXN-COMP-005', 'receipts/completed_2848.jpg', '2026-02-24', 'approved', NULL, NULL),
(850, 2849, 1079, 1687, 1876, 500000.00, 'bank_transfer', 'TXN-COMP-006', 'receipts/completed_2849.jpg', '2026-03-01', 'approved', NULL, NULL),
(852, 2851, 1080, 1687, 1876, 4000000.00, 'bank_transfer', 'TXN-HALT-MS-001', 'receipts/halt_milestone_1.jpg', '2026-01-13', 'approved', NULL, NULL);

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
(3689, 371, 'A contractor has submitted a bid for \"jslaabxxbxsssss\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 268, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1055,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-15 22:20:11'),
(3690, 371, 'A contractor has submitted a bid for \"jslaabxxbxsssss\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 269, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1055,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-15 22:21:27'),
(3691, 371, 'A contractor has submitted a bid for \"jslaabxxbxsssss\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 270, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1055,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-15 22:25:42'),
(3692, 371, 'A contractor has submitted a bid for \"jslaabxxbxsssss\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 271, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1055,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-02-15 22:43:13'),
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
(3719, 372, 'The property owner has already chosen a contractor for \"Testz\". Thank you for your bid.', 'Bid Not Selected', 'Bid Status', 1, 'App', 'normal', 'bid', 260, NULL, '{\"screen\":\"MyBids\",\"params\":{\"projectId\":1047},\"notification_sub_type\":\"bid_rejected\"}', '2026-02-22 07:19:27'),
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
(3735, 379, 'A Delay dispute has been filed against you on \"Commercial Building\".', 'Dispute Filed', 'Dispute Update', 0, 'App', 'critical', 'dispute', 88, NULL, '{\"screen\":\"DisputeDetails\",\"params\":{\"disputeId\":88},\"notification_sub_type\":\"dispute_opened\"}', '2026-02-28 06:17:02'),
(3736, 371, 'A contractor has submitted a bid for \"jslaabxxbxsssss\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 296, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1055,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-03-01 03:36:46'),
(3737, 371, 'A Halt dispute has been filed against you on \"Project\".', 'Dispute Filed', 'Dispute Update', 1, 'App', 'critical', 'dispute', 89, NULL, '{\"screen\":\"DisputeDetails\",\"params\":{\"disputeId\":89},\"notification_sub_type\":\"dispute_opened\"}', '2026-03-01 08:03:03'),
(3738, 372, 'Your bid for \"Modern Residential House Construction\" has been accepted.', 'Bid Accepted! 🎉', 'Bid Status', 1, 'App', 'high', 'bid', 1, NULL, '{\"notification_sub_type\":\"bid_accepted\"}', '2026-03-03 10:06:57'),
(3739, 372, 'Your bid for \"Commercial Building Renovation\" was not selected.', 'Bid Not Selected', 'Bid Status', 1, 'App', 'normal', 'bid', 2, NULL, '{\"notification_sub_type\":\"bid_rejected\"}', '2026-03-03 10:06:57'),
(3740, 372, 'Milestone \"Foundation Work\" has been approved by the property owner.', 'Milestone Approved ✓', 'Milestone Update', 1, 'App', 'normal', 'milestone', 1, NULL, '{\"notification_sub_type\":\"milestone_approved\"}', '2026-03-03 10:06:57'),
(3741, 372, 'Milestone \"Roofing Installation\" needs revisions. Check feedback.', 'Milestone Rejected', 'Milestone Update', 1, 'App', 'high', 'milestone', 2, NULL, '{\"notification_sub_type\":\"milestone_rejected\"}', '2026-03-03 10:06:57'),
(3742, 372, 'Milestone \"Electrical Wiring\" has been marked as completed.', 'Milestone Completed! ✓', 'Milestone Update', 1, 'App', 'normal', 'milestone', 3, NULL, '{\"notification_sub_type\":\"milestone_completed\"}', '2026-03-03 10:06:57'),
(3743, 372, 'Property owner updated the requirements for \"Plumbing Installation\".', 'Milestone Updated', 'Milestone Update', 1, 'App', 'normal', 'milestone', 4, NULL, '{\"notification_sub_type\":\"milestone_updated\"}', '2026-03-03 10:06:57'),
(3744, 372, 'Payment of ₱150,000 for Milestone 1 has been approved and processed.', 'Payment Received 💰', 'Payment Status', 1, 'App', 'high', 'payment', 1, NULL, '{\"notification_sub_type\":\"payment_approved\"}', '2026-03-03 10:06:57'),
(3745, 372, 'Payment request for ₱75,000 requires additional documentation.', 'Payment Issue', 'Payment Status', 1, 'App', 'high', 'payment', 2, NULL, '{\"notification_sub_type\":\"payment_rejected\"}', '2026-03-03 10:06:57'),
(3746, 372, 'Milestone 2 payment has been fully paid (₱200,000).', 'Payment Complete ✓', 'Payment Status', 1, 'App', 'normal', 'payment', 3, NULL, '{\"notification_sub_type\":\"payment_fully_paid\"}', '2026-03-03 10:06:57'),
(3747, 372, 'A dispute has been opened regarding \"Quality of Materials\".', 'Dispute Filed', 'Dispute Update', 1, 'App', 'critical', 'dispute', 1, NULL, '{\"notification_sub_type\":\"dispute_opened\"}', '2026-03-03 10:06:57'),
(3748, 372, 'Admin is reviewing the dispute case #DR-2024-001.', 'Dispute Under Review', 'Dispute Update', 1, 'App', 'high', 'dispute', 1, NULL, '{\"notification_sub_type\":\"dispute_under_review\"}', '2026-03-03 10:06:57'),
(3749, 372, 'Dispute case #DR-2024-001 has been successfully resolved.', 'Dispute Resolved ✓', 'Dispute Update', 1, 'App', 'high', 'dispute', 1, NULL, '{\"notification_sub_type\":\"dispute_resolved\"}', '2026-03-03 10:06:57'),
(3750, 372, 'Congratulations! \"Modern Residential House Construction\" has been successfully completed.', 'Project Completed! 🎊', 'Project Alert', 1, 'App', 'high', 'project', 1, NULL, '{\"notification_sub_type\":\"project_completed\"}', '2026-03-03 10:06:57'),
(3751, 372, 'Project \"Commercial Building Renovation\" has been temporarily halted.', 'Project Halted', 'Project Alert', 1, 'App', 'critical', 'project', 2, NULL, '{\"notification_sub_type\":\"project_halted\"}', '2026-03-03 10:06:57'),
(3752, 372, 'Project \"Office Building Construction\" has been terminated by the owner.', 'Project Terminated', 'Project Alert', 1, 'App', 'critical', 'project', 3, NULL, '{\"notification_sub_type\":\"project_terminated\"}', '2026-03-03 10:06:57'),
(3753, 372, 'You have been invited to join the project team for \"Luxury Villa Construction\".', 'Team Invitation 👥', 'Team Update', 1, 'App', 'normal', 'team', 1, NULL, '{\"notification_sub_type\":\"team_invite\"}', '2026-03-03 10:06:57'),
(3754, 372, 'You have been removed from the team for \"Residential Complex Development\".', 'Removed from Team', 'Team Update', 1, 'App', 'high', 'team', 2, NULL, '{\"notification_sub_type\":\"team_removed\"}', '2026-03-03 10:06:57'),
(3755, 372, 'Your role has been updated to \"Lead Engineer\" in the project team.', 'Role Updated', 'Team Update', 1, 'App', 'normal', 'team', 3, NULL, '{\"notification_sub_type\":\"team_role_changed\"}', '2026-03-03 10:06:57'),
(3756, 372, 'Milestone item \"Install Water Lines\" has been completed.', 'Task Completed', 'Milestone Update', 1, 'App', 'normal', 'milestone_item', 5, NULL, '{\"notification_sub_type\":\"milestone_item_completed\"}', '2026-03-03 10:06:57'),
(3757, 372, 'Milestone \"Landscaping\" has been removed from the project.', 'Milestone Removed', 'Milestone Update', 1, 'App', 'normal', 'milestone', 6, NULL, '{\"notification_sub_type\":\"milestone_deleted\"}', '2026-03-03 10:06:57'),
(3758, 372, 'Your payment request for ₱100,000 has been submitted for approval.', 'Payment Request Submitted', 'Payment Status', 1, 'App', 'normal', 'payment', 4, NULL, '{\"notification_sub_type\":\"payment_submitted\"}', '2026-03-03 10:06:57');
INSERT INTO `notifications` (`notification_id`, `user_id`, `message`, `title`, `type`, `is_read`, `delivery_method`, `priority`, `reference_type`, `reference_id`, `dedup_key`, `action_link`, `created_at`) VALUES
(3759, 372, 'Payment details for Milestone 3 have been updated.', 'Payment Updated', 'Payment Status', 1, 'App', 'normal', 'payment', 5, NULL, '{\"notification_sub_type\":\"payment_updated\"}', '2026-03-03 10:06:57'),
(3760, 372, 'Test Test: ss', 'New Message 💬', 'Project Alert', 1, 'App', 'normal', 'conversation', 371000372, NULL, '{\"screen\":\"messages\",\"params\":{\"conversationId\":371000372},\"notification_sub_type\":\"message_received\"}', '2026-03-03 10:24:30'),
(3761, 371, 'test2: hi', 'New Message 💬', 'Project Alert', 1, 'App', 'normal', 'conversation', 371000372, NULL, '{\"screen\":\"messages\",\"params\":{\"conversationId\":371000372},\"notification_sub_type\":\"message_received\"}', '2026-03-03 10:25:51'),
(3762, 371, 'test2: dfg', 'New Message 💬', 'Project Alert', 1, 'App', 'normal', 'conversation', 371000372, NULL, '{\"screen\":\"messages\",\"params\":{\"conversationId\":371000372},\"notification_sub_type\":\"message_received\"}', '2026-03-03 18:30:06'),
(3763, 372, 'Test Test: sss', 'New Message 💬', 'Project Alert', 1, 'App', 'normal', 'conversation', 371000372, NULL, '{\"screen\":\"messages\",\"params\":{\"conversationId\":371000372},\"notification_sub_type\":\"message_received\"}', '2026-03-03 18:30:46'),
(3764, 372, 'Test Test: sdsds', 'New Message 💬', 'Project Alert', 1, 'App', 'normal', 'conversation', 371000372, NULL, '{\"screen\":\"messages\",\"params\":{\"conversationId\":371000372},\"notification_sub_type\":\"message_received\"}', '2026-03-03 18:37:30'),
(3765, 371, 'test2: asdad', 'New Message 💬', '', 1, 'App', 'normal', 'conversation', 371000372, NULL, '{\"screen\":\"messages\",\"params\":{\"conversationId\":371000372},\"notification_sub_type\":\"message_received\"}', '2026-03-03 18:44:36'),
(3766, 371, 'Contractor submitted a milestone plan for \"Testing again\". Please review.', 'Milestone Submitted', 'Milestone Update', 1, 'App', 'normal', 'milestone', 1567, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1053,\"tab\":\"milestones\"},\"notification_sub_type\":\"milestone_submitted\"}', '2026-03-03 20:22:42'),
(3767, 372, 'Test Test: Hello thereee hakfok', 'New Message 💬', 'Project Alert', 1, 'App', 'normal', 'conversation', 371000372, NULL, '{\"screen\":\"messages\",\"params\":{\"conversationId\":371000372},\"notification_sub_type\":\"message_received\"}', '2026-03-03 20:32:07'),
(3768, 371, 'test2: soo hello goodevening', 'New Message 💬', 'Project Alert', 1, 'App', 'normal', 'conversation', 371000372, NULL, '{\"screen\":\"messages\",\"params\":{\"conversationId\":371000372},\"notification_sub_type\":\"message_received\"}', '2026-03-03 20:32:12'),
(3769, 372, 'Test Test: Uuhyuuh', 'New Message 💬', 'Project Alert', 1, 'App', 'normal', 'conversation', 371000372, NULL, '{\"screen\":\"messages\",\"params\":{\"conversationId\":371000372},\"notification_sub_type\":\"message_received\"}', '2026-03-03 20:32:45'),
(3770, 371, 'test2: ck cutie', 'New Message 💬', 'Project Alert', 1, 'App', 'normal', 'conversation', 371000372, NULL, '{\"screen\":\"messages\",\"params\":{\"conversationId\":371000372},\"notification_sub_type\":\"message_received\"}', '2026-03-03 20:45:18'),
(3771, 371, 'test2: ack', 'New Message 💬', 'Project Alert', 1, 'App', 'normal', 'conversation', 371000372, NULL, '{\"screen\":\"messages\",\"params\":{\"conversationId\":371000372},\"notification_sub_type\":\"message_received\"}', '2026-03-03 20:45:43'),
(3772, 371, 'test2: heyaaa', 'New Message 💬', 'Project Alert', 1, 'App', 'normal', 'conversation', 371000372, NULL, '{\"screen\":\"messages\",\"params\":{\"conversationId\":371000372},\"notification_sub_type\":\"message_received\"}', '2026-03-03 20:46:51'),
(3773, 371, 'test2: hi nigga free kbabtosa', 'New Message 💬', 'Project Alert', 1, 'App', 'normal', 'conversation', 371000372, NULL, '{\"screen\":\"messages\",\"params\":{\"conversationId\":371000372},\"notification_sub_type\":\"message_received\"}', '2026-03-03 20:49:58'),
(3774, 392, 'Your project post \"Wonderland\" has been approved and is now visible to contractors.', 'Project Post Approved', 'Project Alert', 0, 'App', 'high', 'project', 1057, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1057},\"notification_sub_type\":\"project_update\"}', '2026-03-03 21:17:13'),
(3775, 392, 'A contractor has submitted a bid for \"Wonderland\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 297, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1057,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-03-03 21:20:40'),
(3776, 372, 'Your bid for \"Wonderland\" has been accepted!', 'Bid Accepted', 'Bid Status', 1, 'App', 'high', 'project', 1057, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1057},\"notification_sub_type\":\"bid_accepted\"}', '2026-03-03 21:21:42'),
(3777, 392, 'test2: hello', 'New Message 💬', 'Project Alert', 0, 'App', 'normal', 'conversation', 372000392, NULL, '{\"screen\":\"messages\",\"params\":{\"conversationId\":372000392},\"notification_sub_type\":\"message_received\"}', '2026-03-03 21:23:51'),
(3778, 392, 'Contractor submitted a milestone plan for \"Wonderland\". Please review.', 'Milestone Submitted', 'Milestone Update', 1, 'App', 'normal', 'milestone', 1568, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1057,\"tab\":\"milestones\"},\"notification_sub_type\":\"milestone_submitted\"}', '2026-03-03 21:29:46'),
(3779, 372, 'Your milestone \"Winter Wonderland\" has been approved by the owner.', 'Milestone Approved', 'Milestone Update', 1, 'App', 'normal', 'milestone', 1568, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1057,\"tab\":\"milestones\"},\"notification_sub_type\":\"milestone_approved\"}', '2026-03-03 21:29:58'),
(3780, 392, 'Contractor submitted a project update request for \"Wonderland\". Please review.', 'Project Update Request Submitted', 'Project Alert', 1, 'App', 'high', 'project', 1057, NULL, '{\"screen\":\"ProjectTimeline\",\"params\":{\"projectId\":1057},\"notification_sub_type\":\"project_update\"}', '2026-03-03 21:34:19'),
(3781, 372, 'Your project update request has been approved. The project timeline and budget have been updated.', 'Project Update Approved', 'Project Alert', 1, 'App', 'high', 'project', 1057, NULL, '{\"screen\":\"ProjectTimeline\",\"params\":{\"projectId\":1057},\"notification_sub_type\":\"project_update\"}', '2026-03-03 21:34:56'),
(3782, 372, 'The project \"noche buena\" has been marked as completed. Congratulations!', 'Project Completed', 'Project Alert', 0, 'App', 'high', 'project', 1048, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1048},\"notification_sub_type\":\"project_completed\"}', '2026-03-05 04:52:03'),
(3783, 371, 'How was your experience with the contractor on \"noche buena\"? Tap here to leave a review.', 'Leave a Review', 'Project Alert', 0, 'App', 'high', 'project', 1048, NULL, '{\"screen\":\"review\",\"params\":{\"projectId\":1048,\"revieweeUserId\":372},\"notification_sub_type\":\"review_prompt\"}', '2026-03-05 04:52:03'),
(3784, 372, 'How was your experience with the property owner on \"noche buena\"? Tap here to leave a review.', 'Leave a Review', 'Project Alert', 0, 'App', 'high', 'project', 1048, NULL, '{\"screen\":\"review\",\"params\":{\"projectId\":1048,\"revieweeUserId\":371},\"notification_sub_type\":\"review_prompt\"}', '2026-03-05 04:52:03'),
(3785, 372, 'test1 left a review on \"noche buena\".', 'New Review Received', 'Project Alert', 0, 'App', 'normal', 'review', 40, NULL, '{\"screen\":\"profile\",\"params\":{\"tab\":\"reviews\"},\"notification_sub_type\":\"review_submitted\"}', '2026-03-05 04:52:38'),
(3786, 372, 'Your milestone \"Project Batumbakal\" has been approved by the owner.', 'Milestone Approved', 'Milestone Update', 0, 'App', 'normal', 'milestone', 1567, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1053,\"tab\":\"milestones\"},\"notification_sub_type\":\"milestone_approved\"}', '2026-03-05 06:20:44'),
(3787, 371, 'A contractor has submitted a bid for \"Project Images Testing\".', 'New Bid Received', 'Bid Status', 0, 'App', 'normal', 'bid', 298, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1052,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-03-06 07:07:55'),
(3788, 371, 'A contractor has submitted a bid for \"jslaabxxbxsssss\".', 'New Bid Received', 'Bid Status', 0, 'App', 'normal', 'bid', 296, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1055,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-03-06 07:39:41'),
(3789, 394, 'You have been added as a architect to a contractor team.', 'Welcome to the Team', 'Project Alert', 0, 'App', 'normal', 'user', 394, NULL, '{\"screen\":\"Dashboard\",\"notification_sub_type\":\"team_member_added\"}', '2026-03-06 08:29:43'),
(3790, 392, 'Your project post \"New SM Mall\" has been approved and is now visible to contractors.', 'Project Post Approved', 'Project Alert', 0, 'App', 'high', 'project', 1058, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1058},\"notification_sub_type\":\"project_update\"}', '2026-03-07 01:27:15'),
(3791, 392, 'Your project post \"Luxury hotel\" has been approved and is now visible to contractors.', 'Project Post Approved', 'Project Alert', 0, 'App', 'high', 'project', 1059, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1059},\"notification_sub_type\":\"project_update\"}', '2026-03-07 01:56:56'),
(3792, 392, 'Your project post \"thrashings\" has been approved and is now visible to contractors.', 'Project Post Approved', 'Project Alert', 0, 'App', 'high', 'project', 1060, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":1060},\"notification_sub_type\":\"project_update\"}', '2026-03-07 04:57:35'),
(3793, 1, 'All milestone dates for \"Office Building Construction\" have been extended by 2 days by admin. Reason: HOTDOGHOTDOGHOTDOGHOTDOGHOTDOGHOTDOGHOTDOG', 'Project Timeline Adjusted', 'Project Alert', 0, 'App', 'high', NULL, NULL, NULL, NULL, '2026-03-08 22:31:59'),
(3794, 101, 'All milestone dates for \"Office Building Construction\" have been extended by 2 days by admin.', 'Project Timeline Adjusted', 'Project Alert', 0, 'App', 'high', NULL, NULL, NULL, NULL, '2026-03-08 22:31:59'),
(3795, 396, 'Your contractor account has been verified and approved. You can now access all platform features.', 'Account Verified', 'Project Alert', 0, 'App', 'high', NULL, NULL, NULL, '{\"screen\":\"Home\",\"params\":[],\"notification_sub_type\":\"project_update\"}', '2026-03-09 18:57:58'),
(3796, 396, 'Your contractor account has been verified and approved. You can now access all platform features.', 'Account Verified', 'Project Alert', 0, 'App', 'high', NULL, NULL, NULL, '{\"screen\":\"Home\",\"params\":[],\"notification_sub_type\":\"project_update\"}', '2026-03-09 18:58:09'),
(3797, 396, 'Your contractor account has been verified and approved. You can now access all platform features.', 'Account Verified', 'Project Alert', 0, 'App', 'high', NULL, NULL, NULL, '{\"screen\":\"Home\",\"params\":[],\"notification_sub_type\":\"project_update\"}', '2026-03-09 18:58:18'),
(3798, 396, 'Your contractor account has been verified and approved. You can now access all platform features.', 'Account Verified', 'Project Alert', 0, 'App', 'high', NULL, NULL, NULL, '{\"screen\":\"Home\",\"params\":[],\"notification_sub_type\":\"project_update\"}', '2026-03-09 18:58:26'),
(3799, 396, 'Your contractor account has been verified and approved. You can now access all platform features.', 'Account Verified', 'Project Alert', 0, 'App', 'high', NULL, NULL, NULL, '{\"screen\":\"Home\",\"params\":[],\"notification_sub_type\":\"project_update\"}', '2026-03-09 18:58:35'),
(3800, 379, 'Your contractor account has been verified and approved. You can now access all platform features.', 'Account Verified', 'Project Alert', 0, 'App', 'high', NULL, NULL, NULL, '{\"screen\":\"Home\",\"params\":[],\"notification_sub_type\":\"project_update\"}', '2026-03-09 19:05:44'),
(3801, 379, 'Your contractor account has been verified and approved. You can now access all platform features.', 'Account Verified', 'Project Alert', 0, 'App', 'high', NULL, NULL, NULL, '{\"screen\":\"Home\",\"params\":[],\"notification_sub_type\":\"project_update\"}', '2026-03-09 19:05:52');

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
(928, 1056, 1810, 'downpayment', 60000000.00, 10000000.00, 0, '2026-02-23 00:12:07', '2026-02-27 04:12:04'),
(931, 1053, 1809, 'downpayment', 34444444.00, 4444444.00, 0, '2026-03-03 20:22:42', '2026-03-03 20:22:42'),
(932, 1057, 1809, 'downpayment', 9000000.00, 50000.00, 0, '2026-03-03 21:29:46', '2026-03-03 21:34:56'),
(934, 1064, 1687, 'downpayment', 6000000.00, 1300000.00, 0, '2026-03-09 03:12:51', '2026-03-09 03:12:51'),
(935, 1066, 1687, 'downpayment', 6000000.00, 1300000.00, 0, '2026-03-09 03:14:04', '2026-03-09 03:14:04'),
(936, 1068, 1687, 'downpayment', 6000000.00, 1300000.00, 0, '2026-03-09 03:16:10', '2026-03-09 03:16:10'),
(937, 1070, 1687, 'downpayment', 6000000.00, 1300000.00, 0, '2026-03-09 03:16:56', '2026-03-09 03:16:56'),
(938, 1072, 1687, 'downpayment', 6000000.00, 1300000.00, 0, '2026-03-09 03:18:30', '2026-03-09 03:18:30'),
(939, 1074, 1687, 'downpayment', 6000000.00, 1300000.00, 0, '2026-03-09 03:19:22', '2026-03-09 03:19:22'),
(940, 1075, 1687, 'full_payment', 9500000.00, 0.00, 0, '2026-03-09 03:19:22', '2026-03-09 03:19:22'),
(941, 1076, 1687, 'downpayment', 14000000.00, 3000000.00, 0, '2026-03-09 03:19:22', '2026-03-09 03:19:22'),
(942, 1078, 1687, 'downpayment', 6000000.00, 1300000.00, 0, '2026-03-09 03:19:53', '2026-03-09 03:19:53'),
(943, 1079, 1687, 'full_payment', 9500000.00, 0.00, 0, '2026-03-09 03:19:53', '2026-03-09 03:19:53'),
(944, 1080, 1687, 'downpayment', 14000000.00, 3000000.00, 0, '2026-03-09 03:19:53', '2026-03-09 03:19:53');

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
(117, 'App\\Models\\User', 380, 'mobile-app', '37166ebd4a543ccc681c8ff08dcb9aea4f327870b34a9e4327b58efebaadaa8b', '[\"*\"]', NULL, NULL, '2026-03-01 00:18:39', '2026-03-01 00:18:39'),
(118, 'App\\Models\\User', 372, 'mobile-app', '3fe1a26b378e3bb1abbbd1de7349cfeec9827e1dbeff0571d861f16bc90a7bea', '[\"*\"]', NULL, NULL, '2026-03-01 01:16:35', '2026-03-01 01:16:35'),
(119, 'App\\Models\\User', 371, 'mobile-app', '86f2f3d08ebe8fff63f75c26d515a71adbab9d7dc300eefcd0eefe88b3dc68e0', '[\"*\"]', NULL, NULL, '2026-03-01 01:31:29', '2026-03-01 01:31:29'),
(120, 'App\\Models\\User', 372, 'mobile-app', '795a968371dd1bf8fd28eebe2c3f95da4650952db5253577587d34811767ee15', '[\"*\"]', NULL, NULL, '2026-03-02 11:44:44', '2026-03-02 11:44:44'),
(121, 'App\\Models\\User', 371, 'mobile-app', 'c090770b981bd7c471ccd010a467f6a0aa661f1da1c73f54c76426b3978c66cb', '[\"*\"]', NULL, NULL, '2026-03-02 12:32:23', '2026-03-02 12:32:23'),
(122, 'App\\Models\\User', 372, 'mobile-app', '371371c2e24952710b64d902567febfa1be9d71a40af52a0a531f6e57be1f968', '[\"*\"]', NULL, NULL, '2026-03-03 20:08:19', '2026-03-03 20:08:19'),
(123, 'App\\Models\\User', 371, 'mobile-app', '58d3ecc838f9a557a347b0987b380d654673536444c67738d1cde846fe199c91', '[\"*\"]', NULL, NULL, '2026-03-03 20:30:28', '2026-03-03 20:30:28'),
(124, 'App\\Models\\User', 371, 'mobile-app', '20ced9fafd26b384f3caf900c77973275242f19ad8a22a565f0af6d1f4c1fe61', '[\"*\"]', NULL, NULL, '2026-03-03 21:04:53', '2026-03-03 21:04:53'),
(125, 'App\\Models\\User', 392, 'mobile-app', 'd80f910cd717ae69e5876c4939694472a59b13ec62048eaf617a90d30d877b06', '[\"*\"]', NULL, NULL, '2026-03-03 21:13:50', '2026-03-03 21:13:50'),
(126, 'App\\Models\\User', 372, 'mobile-app', '5a6e83303169bf6d61e1c68121673684451332ac92328dc038b2d4c6a87fd59c', '[\"*\"]', NULL, NULL, '2026-03-03 21:17:26', '2026-03-03 21:17:26'),
(127, 'App\\Models\\User', 392, 'mobile-app', '855b339750c099a5dcf4af413ea5a819d9c059f1fc4574546e18f49d32f05e88', '[\"*\"]', NULL, NULL, '2026-03-03 21:21:07', '2026-03-03 21:21:07'),
(128, 'App\\Models\\User', 372, 'mobile-app', 'a2e6a6063a60643ea074ca7f70a6e4c54da665bbe2b2eb7b4b75700cf8bbeecb', '[\"*\"]', NULL, NULL, '2026-03-03 21:21:58', '2026-03-03 21:21:58'),
(129, 'App\\Models\\User', 392, 'mobile-app', 'a8ba448765d8c01ea398f6536145111f4e5f354d4adf864a46d2bfb9ea38ba92', '[\"*\"]', NULL, NULL, '2026-03-03 21:23:03', '2026-03-03 21:23:03'),
(130, 'App\\Models\\User', 371, 'mobile-app', '9621e7d08c0e0e5a6110828e6183d9d049a43928b1c260594331b75b70f2640d', '[\"*\"]', NULL, NULL, '2026-03-05 00:54:02', '2026-03-05 00:54:02'),
(131, 'App\\Models\\User', 371, 'mobile-app', 'f296708e4b47b00ca9153409882b7cf2d68b170107847c9ba75ad0761d4ec5dc', '[\"*\"]', NULL, NULL, '2026-03-05 00:55:44', '2026-03-05 00:55:44'),
(132, 'App\\Models\\User', 380, 'mobile-app', '536a57cba3010d4d462639ff4f87c9ea062a69c5881f89c27c4d99f05a5793d3', '[\"*\"]', NULL, NULL, '2026-03-05 01:12:43', '2026-03-05 01:12:43'),
(133, 'App\\Models\\User', 371, 'mobile-app', 'e4d9c2ec75f8d61b8b2a338d0cfefe7f7a6bc6b9d684fd693997ddae2a222823', '[\"*\"]', NULL, NULL, '2026-03-05 01:36:14', '2026-03-05 01:36:14'),
(134, 'App\\Models\\User', 371, 'mobile-app', 'bf0c93398ecec1f57e1e984f2cee25b0365c9612b9e3cdb35dc2448e949bd5cb', '[\"*\"]', NULL, NULL, '2026-03-05 04:44:10', '2026-03-05 04:44:10'),
(135, 'App\\Models\\User', 371, 'mobile-app', '7ca5c0bc82423a472edc9b17e3db4a75a5a5eab9835695c47351c08ed2844547', '[\"*\"]', NULL, NULL, '2026-03-05 04:48:47', '2026-03-05 04:48:47'),
(136, 'App\\Models\\User', 371, 'mobile-app', 'e9b7b7c052831944a9ab36f206a1490d149ac5a2c37c788de20e0bda4a5c9203', '[\"*\"]', NULL, NULL, '2026-03-05 06:19:06', '2026-03-05 06:19:06'),
(137, 'App\\Models\\User', 372, 'mobile-app', '09e69c10b715f33ebe7375244a0087ac31aad987728c00f5615a256b56eb4d1d', '[\"*\"]', NULL, NULL, '2026-03-05 07:45:21', '2026-03-05 07:45:21'),
(138, 'App\\Models\\User', 394, 'mobile-app', '95dde0cc833a03e09aa18a826429b95ad68e90b7f9bcfea7e05749986446ee0c', '[\"*\"]', NULL, NULL, '2026-03-06 08:31:38', '2026-03-06 08:31:38'),
(139, 'App\\Models\\User', 394, 'mobile-app', 'e9417bd3df49886c4e259bc5218f1b7bded6e40a2f4a63827cef50f95af932b4', '[\"*\"]', NULL, NULL, '2026-03-06 08:41:24', '2026-03-06 08:41:24'),
(140, 'App\\Models\\User', 372, 'mobile-app', 'd129251c1053426d62269f8c3ecfb72e90e619ad020b9c08b69d1527554c66ed', '[\"*\"]', NULL, NULL, '2026-03-06 08:54:38', '2026-03-06 08:54:38'),
(141, 'App\\Models\\User', 394, 'mobile-app', 'ce3072871669ad747dc609273c41c0ffd2ddf1f08f160dba3c460046933f2ae7', '[\"*\"]', NULL, NULL, '2026-03-06 08:59:29', '2026-03-06 08:59:29'),
(142, 'App\\Models\\User', 372, 'mobile-app', '5a1a1479ec3f307a8efb6b0ed8953043d350aae34e885cefa0b2439599cd544d', '[\"*\"]', NULL, NULL, '2026-03-06 22:43:09', '2026-03-06 22:43:09'),
(143, 'App\\Models\\User', 392, 'mobile-app', 'd61d0448355b77ce2a79b0ac871d429dbcd2d525b55bfb0f05362c1290e40473', '[\"*\"]', NULL, NULL, '2026-03-06 23:20:30', '2026-03-06 23:20:30'),
(144, 'App\\Models\\User', 372, 'mobile-app', '4ecb9b4b8546224858dfabe4302c21a6653d91d2a361f2b81fa167e452be139b', '[\"*\"]', NULL, NULL, '2026-03-06 23:22:19', '2026-03-06 23:22:19'),
(145, 'App\\Models\\User', 392, 'mobile-app', 'fa2b5fabeb9fa77b8cd72c6280160f5455e2af823b898763492e31fdf7645476', '[\"*\"]', NULL, NULL, '2026-03-07 01:22:54', '2026-03-07 01:22:54'),
(146, 'App\\Models\\User', 372, 'mobile-app', '790e7a29b26fd216cb094bc3925d6d4f487d29f2a2833fcefde9d8100694d899', '[\"*\"]', NULL, NULL, '2026-03-07 01:27:36', '2026-03-07 01:27:36'),
(147, 'App\\Models\\User', 392, 'mobile-app', '47d547ab31925deb69dbc47a6f3bc401385739ccc98d65e4f827ae7a78d417cd', '[\"*\"]', NULL, NULL, '2026-03-07 01:41:23', '2026-03-07 01:41:23'),
(148, 'App\\Models\\User', 372, 'mobile-app', '2c3c7c4852a726b546b476a78be3a8635cc25d2aca0fd57afa4f105e0f7af616', '[\"*\"]', NULL, NULL, '2026-03-07 01:57:12', '2026-03-07 01:57:12'),
(149, 'App\\Models\\User', 392, 'mobile-app', '29df16b066ab6cb49df0c17e9dc73f67e44bb3fb19fd8f9f6c975a3befe54bbd', '[\"*\"]', NULL, NULL, '2026-03-07 04:50:01', '2026-03-07 04:50:01'),
(150, 'App\\Models\\User', 372, 'mobile-app', 'eead02a83c52aaaa42bb263cce9300ec2b9a58defe262cd3299587e08f90aa27', '[\"*\"]', NULL, NULL, '2026-03-07 04:57:57', '2026-03-07 04:57:57'),
(151, 'App\\Models\\User', 392, 'mobile-app', 'c44000e636ec22508b928b8b9f86006d335fd1dd102a057667d8718c44822a12', '[\"*\"]', NULL, NULL, '2026-03-08 04:06:52', '2026-03-08 04:06:52'),
(152, 'App\\Models\\User', 372, 'mobile-app', 'd2690d849f7111477a8828cf4eb352885f94464de2385a3143a70798e0bac393', '[\"*\"]', NULL, NULL, '2026-03-08 08:03:33', '2026-03-08 08:03:33'),
(153, 'App\\Models\\User', 394, 'mobile-app', '932751882079f2b5034286f63f72b8ebe86624e45bff5d48bcaed69b8455da87', '[\"*\"]', NULL, NULL, '2026-03-08 08:05:51', '2026-03-08 08:05:51'),
(154, 'App\\Models\\User', 372, 'mobile-app', 'bb1fbffc62a39b06c4f00959493a7c993423071b12c0b118fda1c3a512c15488', '[\"*\"]', NULL, NULL, '2026-03-08 09:09:14', '2026-03-08 09:09:14'),
(155, 'App\\Models\\user', 372, 'mobile-app', '79e6a6d51ba0e7591baeb2aee02a5ce7a8773051db9ba14abd5198ba63365f11', '[\"*\"]', NULL, NULL, '2026-03-09 18:41:03', '2026-03-09 18:41:03'),
(156, 'App\\Models\\user', 396, 'mobile-app', 'b7be915e3bf3c1fbaf3bebc8d34fba6a0d5a6f6f65ee0d9e52d0fd2df729e074', '[\"*\"]', NULL, NULL, '2026-03-09 18:58:47', '2026-03-09 18:58:47'),
(157, 'App\\Models\\user', 396, 'mobile-app', '27e4b3ec6c3a597c6111bf946bf09497182ade7a25bd22afd0c890b548be4533', '[\"*\"]', NULL, NULL, '2026-03-09 19:01:23', '2026-03-09 19:01:23'),
(158, 'App\\Models\\user', 371, 'mobile-app', '13597211c54a848a79c2245124a7872ac2f6771fe5b40bc43cea2f678f7be4f0', '[\"*\"]', NULL, NULL, '2026-03-09 19:02:33', '2026-03-09 19:02:33'),
(159, 'App\\Models\\user', 379, 'mobile-app', '8d461f04350b468795bc6af1da0aed98fa12977a12d9b877c02f5728be384923', '[\"*\"]', NULL, NULL, '2026-03-09 19:03:04', '2026-03-09 19:03:04');

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
(90, 4, 1055, NULL, 1814, 49.00, 'cs_563785e374e74df22bf15ee4', '2026-02-19 00:07:16', 1, 0, NULL, '2026-02-26 00:07:16', 'PayMongo', NULL),
(91, 2, NULL, 1809, NULL, 1499.00, 'cs_88743ff4cc19941b12468aed', '2026-02-19 00:09:36', 0, 0, NULL, '2026-03-19 00:09:36', 'PayMongo', NULL),
(92, 4, 1053, NULL, 1814, 49.00, 'cs_126348af6114a956204d1202', '2026-02-19 00:14:45', 1, 0, NULL, '2026-02-26 00:14:45', 'PayMongo', NULL),
(94, 2, NULL, 1809, NULL, 1499.00, 'cs_83ccd03dcb453c420f9170d1', '2026-02-19 00:47:48', 0, 0, NULL, '2026-03-19 00:47:48', 'PayMongo', NULL),
(96, 1, NULL, 1809, NULL, 1999.00, 'cs_f8f6f0de7782cf24191133ff', '2026-02-19 01:45:57', 0, 0, NULL, '2026-03-19 01:45:57', 'PayMongo', NULL),
(97, 4, 1046, NULL, 1814, 49.00, 'cs_f5dab5282bbe15916c650ecf', '2026-02-19 01:48:17', 1, 0, NULL, '2026-02-26 01:48:17', 'PayMongo', NULL),
(99, 1, NULL, 1809, NULL, 1999.00, 'cs_a3296f7cc6cf84f80ad62ed9', '2026-02-20 04:07:32', 0, 1, NULL, '2026-03-20 04:07:32', 'PayMongo', NULL),
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
(118, 1, NULL, 1809, NULL, 1999.00, 'cs_ef47c01aad36aab5abdea355', '2026-03-03 21:36:42', 1, 0, NULL, '2026-04-03 21:36:42', 'PayMongo', NULL);

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

--
-- Dumping data for table `post_reports`
--

INSERT INTO `post_reports` (`report_id`, `reporter_user_id`, `post_type`, `post_id`, `reason`, `details`, `status`, `reviewed_by_user_id`, `admin_notes`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(1, 372, 'showcase', 1, 'Other', 'Funny lang kasi di pagkain siya eh', 'pending', NULL, NULL, NULL, '2026-03-06 06:28:57', '2026-03-06 06:28:57'),
(2, 372, 'showcase', 1, 'Inappropriate Content', NULL, 'pending', NULL, NULL, NULL, '2026-03-06 06:32:56', '2026-03-06 06:32:56'),
(3, 372, 'project', 1060, 'False Information', NULL, 'pending', NULL, NULL, NULL, '2026-03-07 23:47:34', '2026-03-07 23:47:34'),
(4, 372, 'project', 1060, 'Scam / Fraud', NULL, 'pending', NULL, NULL, NULL, '2026-03-07 23:54:35', '2026-03-07 23:54:35');

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
  `selected_contractor_id` int(11) DEFAULT NULL,
  `is_highlighted` tinyint(1) NOT NULL DEFAULT 0,
  `highlighted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`project_id`, `relationship_id`, `project_title`, `project_description`, `project_location`, `budget_range_min`, `budget_range_max`, `lot_size`, `floor_area`, `property_type`, `type_id`, `if_others_ctype`, `to_finish`, `project_status`, `previous_status`, `stat_reason`, `remarks`, `selected_contractor_id`, `is_highlighted`, `highlighted_at`) VALUES
(985, 987, 'Project 987', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'terminated', NULL, '', '', NULL, 0, NULL),
(986, 988, 'Project 988', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, NULL, '', NULL, 0, NULL),
(987, 989, 'Project 989', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(988, 990, 'Project 990', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(989, 994, 'Project 994', 'Description text.', 'Pasonanca, Zamboanga City', 1000000.00, 1500000.00, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(990, 995, 'Project 995', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(991, 997, 'Project 997', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(992, 998, 'Project 998', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(993, 999, 'Project 999', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(994, 1000, 'Project 1000', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(995, 1003, 'Project 1003', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, NULL, '', NULL, 0, NULL),
(996, 1005, 'Project 1005', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(997, 1010, 'Project 1010', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(998, 1012, 'Project 1012', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(999, 1013, 'Project 1013', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(1000, 1018, 'Project 1018', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(1001, 1019, 'Project 1019', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(1002, 1020, 'Project 1020', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(1003, 1021, 'Project 1021', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(1004, 1022, 'Project 1022', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(1005, 1023, 'Project 1023', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(1006, 1024, 'Project 1024', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(1007, 1027, 'Project 1027', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'deleted', 'open', 'aaaaaaaaaaaaaaaaaaaaaa', '', NULL, 0, NULL),
(1008, 1032, 'Project 1032', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(1009, 1034, 'Project 1034', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(1010, 1035, 'Project 1035', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(1011, 1036, 'Project 1036', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(1012, 1037, 'Project 1037', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(1013, 1038, 'Project 1038', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(1014, 1042, 'Project 1042', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(1015, 1017, 'Project 1017', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1690, 0, NULL),
(1016, 1030, 'Project 1030', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1694, 0, NULL),
(1017, 1025, 'Project 1025', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1700, 0, NULL),
(1018, 1014, 'Project 1014', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1702, 0, NULL),
(1019, 1040, 'Project 1040', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1702, 0, NULL),
(1020, 991, 'Project 991', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1707, 0, NULL),
(1021, 1004, 'Project 1004', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1715, 0, NULL),
(1022, 1011, 'Project 1011', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1717, 0, NULL),
(1023, 1026, 'Project 1026', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1717, 0, NULL),
(1024, 1001, 'Project 1001', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1718, 0, NULL),
(1025, 1043, 'Project 1043', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1718, 0, NULL),
(1026, 1002, 'Project 1002', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1725, 0, NULL),
(1027, 986, 'Project 986', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, NULL, '', 1727, 0, NULL),
(1028, 1041, 'Project 1041', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1730, 0, NULL),
(1029, 1008, 'Project 1008', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1733, 0, NULL),
(1030, 1009, 'Project 1009', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1735, 0, NULL),
(1031, 1039, 'Project 1039', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1739, 0, NULL),
(1032, 1044, 'Project 1044', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1740, 0, NULL),
(1033, 985, 'Project 985', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1745, 0, NULL),
(1034, 1029, 'Project 1029', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1755, 0, NULL),
(1035, 1028, 'Project 1028', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1763, 0, NULL),
(1036, 1007, 'Project 1007', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1764, 0, NULL),
(1037, 1031, 'Project 1031', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1768, 0, NULL),
(1038, 1033, 'Project 1033', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1773, 0, NULL),
(1039, 996, 'Project 996', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1777, 0, NULL),
(1040, 1015, 'Project 1015', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1787, 0, NULL),
(1041, 993, 'Project 993', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1789, 0, NULL),
(1042, 992, 'Project 992', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1792, 0, NULL),
(1043, 1006, 'Project 1006', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, '', '', 1794, 0, NULL),
(1044, 1016, 'Project 1016', 'Description text.', 'Pasonanca, Zamboanga City', NULL, NULL, 150, 80, 'Residential', 1, NULL, NULL, 'in_progress', NULL, NULL, '', 1798, 0, NULL),
(1045, 1045, 'Test Project', 'Test description', 'Anywhere , Ayala, Zamboanga City, Zamboanga del Sur', 50000000.00, 60000000.00, 500, 450, 'Residential', 6, NULL, NULL, 'open', NULL, NULL, '', 1809, 0, NULL),
(1046, 1046, 'PROJECT FOR BID', 'Project test for bid', 'Anywhere, Arena Blanco, Zamboanga City, Zamboanga del Sur', 77000000.00, 80000000.00, 600, 550, 'Residential', 1, NULL, NULL, 'open', NULL, '', '', 1687, 0, NULL),
(1047, 1047, 'Testz', 'twstw', 'City of Zamboanga, Zamboanga Del Sur Sur', 5000.00, 6898.00, 64649, 94649, 'Residential', 8, NULL, NULL, 'bidding_closed', 'in_progress', 'wwwwwwwwwwwwwwwwwww', 'wwwwwwwwwwwwwwwwwwwww', 1810, 0, NULL),
(1048, 1048, 'noche buena', 'uwi na pls', 'anywhere, Baluno, Zamboanga City, Zamboanga del Sur', 20000000.00, 30000000.00, 5000000, 4444444, 'Residential', 2, NULL, NULL, 'completed', NULL, '', '', 1809, 0, NULL),
(1049, 1049, 'Project', 'Test', 'Porcentro, Tumaga, Zamboanga City, Zamboanga del Sur', 50000000.00, 60000000.00, 500, 250, 'Residential', 6, NULL, NULL, 'open', NULL, '', '', 1809, 0, NULL),
(1050, NULL, 'Test3', 'Test 3 Description', 'Street There, Arena Blanco, Zamboanga City, Zamboanga del Sur', 2500000.00, 5000000.00, 500, 250, 'Residential', 6, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(1051, NULL, 'Testing for Form', 'Form Description', 'Somewhere There, Arena Blanco, Zamboanga City, Zamboanga del Sur', 2000000.00, 5000000.00, 500, 250, 'Residential', 6, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(1052, 1052, 'Project Images Testing', 'Testing Images if it would show', 'Somewhere , Arena Blanco, Zamboanga City, Zamboanga del Sur', 2000000.00, 5000000.00, 500, 250, 'Residential', 6, NULL, NULL, 'open', NULL, '', '', NULL, 0, NULL),
(1053, 1053, 'Testing again', 'tws', 'jakana, Arena Blanco, Zamboanga City, Zamboanga del Sur', 250.00, 500.00, 500, 250, 'Residential', 6, NULL, NULL, 'in_progress', NULL, '', '', NULL, 0, NULL),
(1054, 1054, 'Test Project Posting', 'Test Projects posting pages and flow', 'Anywhere There, Zamboanga Del Sur Sur', 25000000.00, 50000000.00, 500, 250, 'Residential', 5, NULL, NULL, 'in_progress', NULL, 'status reason ng isang nigger', 'si carl di nagbabayad banned ka na dito boi', 1809, 0, NULL),
(1055, 1055, 'jslaabxxbxsssss', 'abahajaa', 'bzzjsskaaka, Zamboanga Del Sur Sur', 2433867.00, 6434994.00, 1001, 901, 'Residential', 2, NULL, NULL, 'open', NULL, NULL, '', NULL, 0, NULL),
(1056, 1056, 'Commercial Building', 'ffrjookedowkjwwojdpjeonwozkwkspsw', '456 Oak Street Apt , Arena Blanco, Zamboanga City, Zamboanga del Sur', 25000000.00, 50000000.00, 500, 350, 'Commercial', 6, NULL, NULL, 'in_progress', NULL, NULL, NULL, 1810, 0, NULL),
(1057, 1057, 'Wonderland', 'Onde ta keda el Disney', 'Purok 234, Disney Princess, Arena Blanco, Zamboanga City, Zamboanga del Sur', 5000000.00, 10000000.00, 3000, 2500, 'Commercial', 6, NULL, NULL, 'in_progress', NULL, NULL, NULL, 1809, 0, NULL),
(1058, 1058, 'New SM Mall', 'yung mas malaki pa sa inaakala niyo', 'Purok 235, Oak View, Baliwasan, Zamboanga City, Zamboanga del Sur', 5000000.00, 10000000.00, 5000, 4500, 'Commercial', 6, NULL, NULL, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(1059, 1059, 'Luxury hotel', '5 star in All Philippines', 'Purok 365 Atuphai Street, Baluno, Zamboanga City, Zamboanga del Sur', 100000000.00, 200000000.00, 10000, 9000, 'Commercial', 5, NULL, NULL, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(1060, 1060, 'thrashings', 'uhhgssgglgslddhd&j', 'Zamboanga City, Baliwasan, Zamboanga City, Zamboanga del Sur', 5000000.00, 10000000.00, 5000, 4500, 'Residential', 7, NULL, NULL, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(1061, 1062, 'Modern Residential House Construction', 'Construction of a 2-storey modern residential house with 4 bedrooms, 3 bathrooms, living room, dining area, and kitchen. Includes landscaping and perimeter fence.', 'Purok 123 Main Street, Tetuan, Zamboanga City, Zamboanga del Sur', 3500000.00, 4500000.00, 250, 180, 'Residential', 1, NULL, 8, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(1062, 1063, 'Commercial Building Renovation', 'Complete renovation of a 3-storey commercial building including structural repairs, electrical rewiring, plumbing upgrades, and interior finishing.', 'Purok 456 Business Avenue, Baluno, Zamboanga City, Zamboanga del Sur', 5000000.00, 6500000.00, 400, 600, 'Commercial', 2, NULL, 10, 'in_progress', NULL, NULL, NULL, 1687, 0, NULL),
(1063, 1064, 'Modern Residential House Construction', 'Construction of a 2-storey modern residential house with 4 bedrooms, 3 bathrooms, living room, dining area, and kitchen. Includes landscaping and perimeter fence.', 'Purok 123 Main Street, Tetuan, Zamboanga City, Zamboanga del Sur', 3500000.00, 4500000.00, 250, 180, 'Residential', 1, NULL, 8, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(1064, 1065, 'Commercial Building Renovation', 'Complete renovation of a 3-storey commercial building including structural repairs, electrical rewiring, plumbing upgrades, and interior finishing.', 'Purok 456 Business Avenue, Baluno, Zamboanga City, Zamboanga del Sur', 5000000.00, 6500000.00, 400, 600, 'Commercial', 2, NULL, 10, 'in_progress', NULL, NULL, NULL, 1687, 0, NULL),
(1065, 1066, 'Modern Residential House Construction', 'Construction of a 2-storey modern residential house with 4 bedrooms, 3 bathrooms, living room, dining area, and kitchen. Includes landscaping and perimeter fence.', 'Purok 123 Main Street, Tetuan, Zamboanga City, Zamboanga del Sur', 3500000.00, 4500000.00, 250, 180, 'Residential', 1, NULL, 8, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(1066, 1067, 'Commercial Building Renovation', 'Complete renovation of a 3-storey commercial building including structural repairs, electrical rewiring, plumbing upgrades, and interior finishing.', 'Purok 456 Business Avenue, Baluno, Zamboanga City, Zamboanga del Sur', 5000000.00, 6500000.00, 400, 600, 'Commercial', 2, NULL, 10, 'in_progress', NULL, NULL, NULL, 1687, 0, NULL),
(1067, 1068, 'Modern Residential House Construction', 'Construction of a 2-storey modern residential house with 4 bedrooms, 3 bathrooms, living room, dining area, and kitchen. Includes landscaping and perimeter fence.', 'Purok 123 Main Street, Tetuan, Zamboanga City, Zamboanga del Sur', 3500000.00, 4500000.00, 250, 180, 'Residential', 1, NULL, 8, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(1068, 1069, 'Commercial Building Renovation', 'Complete renovation of a 3-storey commercial building including structural repairs, electrical rewiring, plumbing upgrades, and interior finishing.', 'Purok 456 Business Avenue, Baluno, Zamboanga City, Zamboanga del Sur', 5000000.00, 6500000.00, 400, 600, 'Commercial', 2, NULL, 10, 'in_progress', NULL, NULL, NULL, 1687, 0, NULL),
(1069, 1070, 'Modern Residential House Construction', 'Construction of a 2-storey modern residential house with 4 bedrooms, 3 bathrooms, living room, dining area, and kitchen. Includes landscaping and perimeter fence.', 'Purok 123 Main Street, Tetuan, Zamboanga City, Zamboanga del Sur', 3500000.00, 4500000.00, 250, 180, 'Residential', 1, NULL, 8, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(1070, 1071, 'Commercial Building Renovation', 'Complete renovation of a 3-storey commercial building including structural repairs, electrical rewiring, plumbing upgrades, and interior finishing.', 'Purok 456 Business Avenue, Baluno, Zamboanga City, Zamboanga del Sur', 5000000.00, 6500000.00, 400, 600, 'Commercial', 2, NULL, 10, 'in_progress', NULL, NULL, NULL, 1687, 0, NULL),
(1071, 1072, 'Modern Residential House Constructionss', 'Construction of a 2-storey modern residential house with 4 bedrooms, 3 bathrooms, living room, dining area, and kitchen. Includes landscaping and perimeter fence.', 'Purok 123 Main Street, Tetuan, City of Zamboanga, Zamboanga Del Sur', 3500000.00, 4500000.00, 250, 180, 'Residential', 1, NULL, 8, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(1072, 1073, 'Commercial Building Renovation', 'Complete renovation of a 3-storey commercial building including structural repairs, electrical rewiring, plumbing upgrades, and interior finishing.', 'Purok 456 Business Avenue, Baluno, Zamboanga City, Zamboanga del Sur', 5000000.00, 6500000.00, 400, 600, 'Commercial', 2, NULL, 10, 'in_progress', NULL, NULL, NULL, 1687, 0, NULL),
(1073, 1074, 'Modern Residential House Construction', 'Construction of a 2-storey modern residential house with 4 bedrooms, 3 bathrooms, living room, dining area, and kitchen. Includes landscaping and perimeter fence.', 'Purok 123 Main Street, Tetuan, Zamboanga City, Zamboanga del Sur', 3500000.00, 4500000.00, 250, 180, 'Residential', 1, NULL, 8, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(1074, 1075, 'Commercial Building Renovation', 'Complete renovation of a 3-storey commercial building including structural repairs, electrical rewiring, plumbing upgrades, and interior finishing.', 'Purok 456 Business Avenue, Baluno, Zamboanga City, Zamboanga del Sur', 5000000.00, 6500000.00, 400, 600, 'Commercial', 2, NULL, 10, 'in_progress', NULL, NULL, NULL, 1687, 0, NULL),
(1075, 1076, 'Luxury Villa Construction', 'Construction of a luxury 2-storey villa with swimming pool, landscaped garden, and modern amenities. Includes smart home automation system.', 'Purok 789 Sunset Drive, Tetuan, Zamboanga City, Zamboanga del Sur', 8000000.00, 10000000.00, 500, 350, 'Residential', 1, NULL, 12, 'completed', NULL, NULL, NULL, 1687, 0, NULL),
(1076, 1077, 'Office Building Construction', 'Construction of a 4-storey office building with parking basement, elevator, and modern office spaces.', 'Purok 321 Corporate Plaza, Baluno, Zamboanga City, Zamboanga del Sur', 12000000.00, 15000000.00, 600, 800, 'Commercial', 2, NULL, 14, 'halt', 'in_progress', 'Quality issues with concrete work', 'Project halted pending resolution of structural concerns raised by property owner.', 1687, 0, NULL),
(1077, 1078, 'Modern Residential House Construction', 'Construction of a 2-storey modern residential house with 4 bedrooms, 3 bathrooms, living room, dining area, and kitchen. Includes landscaping and perimeter fence.', 'Purok 123 Main Street, Tetuan, Zamboanga City, Zamboanga del Sur', 3500000.00, 4500000.00, 250, 180, 'Residential', 1, NULL, 8, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(1078, 1079, 'Commercial Building Renovation', 'Complete renovation of a 3-storey commercial building including structural repairs, electrical rewiring, plumbing upgrades, and interior finishing.', 'Purok 456 Business Avenue, Baluno, Zamboanga City, Zamboanga del Sur', 5000000.00, 6500000.00, 400, 600, 'Commercial', 2, NULL, 10, 'in_progress', NULL, NULL, NULL, 1687, 0, NULL),
(1079, 1080, 'Luxury Villa Construction', 'Construction of a luxury 2-storey villa with swimming pool, landscaped garden, and modern amenities. Includes smart home automation system.', 'Purok 789 Sunset Drive, Tetuan, Zamboanga City, Zamboanga del Sur', 8000000.00, 10000000.00, 500, 350, 'Residential', 1, NULL, 12, 'completed', NULL, NULL, NULL, 1687, 0, NULL),
(1080, 1081, 'Office Building Construction', 'Construction of a 4-storey office building with parking basement, elevator, and modern office spaces.', 'Purok 321 Corporate Plaza, Baluno, Zamboanga City, Zamboanga del Sur', 12000000.00, 15000000.00, 600, 800, 'Commercial', 2, NULL, 14, 'halt', 'in_progress', 'Quality issues with concrete work', 'Project halted pending resolution of structural concerns raised by property owner.', 1687, 0, NULL),
(1081, 1082, 'Warehouse Expansion Project', 'Expansion of existing warehouse facility with additional storage area and loading docks.', 'Purok 555 Industrial Zone, Tetuan, Zamboanga City, Zamboanga del Sur', 7000000.00, 9000000.00, 1000, 800, 'Industrial', 3, NULL, 10, 'terminated', 'in_progress', 'Client decided to cancel project due to business restructuring and budget constraints.', NULL, 1687, 0, NULL);

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
(355, 1057, 'building permit', 'project_files/building_permit/kA6bRHU7B3bVmjKA6k9xHw2scjJG0ow2HTW8Lasx.jpg', '2026-03-03 21:16:54'),
(356, 1057, 'title', 'project_files/titles/VpEVWJcRyiHIwE2xyAamxDmWgwkWEEMmKyomZ9Ir.jpg', '2026-03-03 21:16:54'),
(357, 1057, 'blueprint', 'project_files/blueprints/h3iNVM7Kv1MjsH9gAWf8qjDmxRJbSaV2YSODV6xS.jpg', '2026-03-03 21:16:54'),
(358, 1057, 'desired design', 'project_files/designs/pqh3UpNNEFurkoLVkeROECI1OFtNY8F72OALQYkY.jpg', '2026-03-03 21:16:54'),
(359, 1057, 'desired design', 'project_files/designs/aNGpk7zsa8ZqRoHmi8hD3nVpFugoktwqIiZWvdpW.jpg', '2026-03-03 21:16:54'),
(360, 1057, 'desired design', 'project_files/designs/mFEh97IIpUuUdTfR4rrrpHQJvAVGgUUbEvP0TjgB.jpg', '2026-03-03 21:16:54'),
(361, 1057, 'desired design', 'project_files/designs/NBo7uF3sZYkkR3P9WMU1mbXlYfdIrvVTGsqLMkjP.jpg', '2026-03-03 21:16:54'),
(362, 1057, 'others', 'project_files/others/7Z8RlPlL9g78QR7tTcCg551DMDuTSynoEQ8rdVeM.jpg', '2026-03-03 21:16:54'),
(363, 1057, 'others', 'project_files/others/ILrrePWra4cMhBSYa2prSGgfNURdEDGdYmJH30nR.jpg', '2026-03-03 21:16:54'),
(364, 1057, 'others', 'project_files/others/t7G2szktEX2kIwfoKh6cJYhs7I2IQZIpaN8OQC2A.jpg', '2026-03-03 21:16:54'),
(365, 1057, 'others', 'project_files/others/tyiX3hqRp4KtaflQAh5U3sSEFUoGo2hj7hT74Pia.jpg', '2026-03-03 21:16:54'),
(366, 1057, 'others', 'project_files/others/EhmXPIFrUuHpoQdub4WbFDtyAobupTOnsHJL5oal.jpg', '2026-03-03 21:16:54'),
(367, 1058, 'building permit', 'project_files/building_permit/dhXznMjXNJvao4Q5KNQ3gU6I7w3cOoY0r3dxWxOu.jpg', '2026-03-07 01:26:09'),
(368, 1058, 'title', 'project_files/titles/Rl5IpeceM4LVaZA5DN64dh1vYVnXIaRQoVvzSaHm.jpg', '2026-03-07 01:26:09'),
(369, 1058, 'blueprint', 'project_files/blueprints/nXVFSAdObmyCDYBmir6lctZ4IXOKpPq5Fd7JwwXa.jpg', '2026-03-07 01:26:09'),
(370, 1058, 'blueprint', 'project_files/blueprints/nZopdwW1S564GCj4AIPhX995YrqN32fd5os0Nxoc.jpg', '2026-03-07 01:26:09'),
(371, 1058, 'blueprint', 'project_files/blueprints/TmrutMbdKqOIDkSIYIxWOSwQ7AWvVBU4PUEoxxhA.jpg', '2026-03-07 01:26:09'),
(372, 1058, 'blueprint', 'project_files/blueprints/UNCgIc9zWu8Lt9qlBss10F8cKSdQr48IyWH01Eiq.jpg', '2026-03-07 01:26:09'),
(373, 1058, 'desired design', 'project_files/designs/ls4bmID2JhuRX4pucDnuBBc8yxxGOIhThYcwfsEB.jpg', '2026-03-07 01:26:09'),
(374, 1058, 'desired design', 'project_files/designs/aduaYRgVmis9IVAd4Xg165IzMy2o3c2mUzsx6199.jpg', '2026-03-07 01:26:09'),
(375, 1058, 'desired design', 'project_files/designs/Joz0ErEklVS0i4xxGnr97eYUgE6ko3XxhDWWXZyP.jpg', '2026-03-07 01:26:09'),
(376, 1058, 'desired design', 'project_files/designs/V8Ezt2cwJNweGaHWBDUiGsxAKIATfjeUxNAIy37Z.jpg', '2026-03-07 01:26:09'),
(377, 1058, 'desired design', 'project_files/designs/Grl04Ea6hDhVFftOKfipCMqAY5OX98mCiizm25RE.jpg', '2026-03-07 01:26:09'),
(378, 1058, 'desired design', 'project_files/designs/91wyg0WIHu4BZcyocrPTklJI2k9MCtXx2sGvMf5o.jpg', '2026-03-07 01:26:09'),
(379, 1058, 'others', 'project_files/others/bgVoAUSyPUNQFofMf2SjikrtC7drrxf7roE0XcmY.jpg', '2026-03-07 01:26:09'),
(380, 1058, 'others', 'project_files/others/nu8ehEqdJT2XdKnrbvVuP6udSEYfW7h3UfnGJZV7.jpg', '2026-03-07 01:26:09'),
(381, 1058, 'others', 'project_files/others/NzoLHA9lWLYhge4vu3We5znutZb4QAfFigMCxKtZ.jpg', '2026-03-07 01:26:09'),
(382, 1058, 'others', 'project_files/others/y2bYdqnh5MZEgEcTSA96hg2m1dskdHs8XvTHK7bp.jpg', '2026-03-07 01:26:09'),
(383, 1058, 'others', 'project_files/others/57tPppx5XNcSBzBKaSo7NVz3FK9mKIAD7aTo3g8t.jpg', '2026-03-07 01:26:09'),
(384, 1059, 'building permit', 'project_files/building_permit/Vdc3qbzpaToQnEnv472rlnsFMnw5mRbdOw950wSU.pdf', '2026-03-07 01:56:13'),
(385, 1059, 'title', 'project_files/titles/ANfdpcr0u8lEaKbL3hZY6yN3O5Pg3jkL7FfOSblv.docx', '2026-03-07 01:56:13'),
(386, 1059, 'blueprint', 'project_files/blueprints/W2UkPcxktYMDxH4M5eo93nyTH5GH93hkoVjn86Cp.jpg', '2026-03-07 01:56:13'),
(387, 1059, 'desired design', 'project_files/designs/Jc9cYbL1dj0kz0b9lR9zwd4bP7C3AnspYYKZ5CLj.jpg', '2026-03-07 01:56:13'),
(388, 1059, 'desired design', 'project_files/designs/p9VUneVetkAiE5qUGHZWPmwOiMhxpbzxgqAPXx7T.jpg', '2026-03-07 01:56:13'),
(389, 1059, 'desired design', 'project_files/designs/jirEcAjEYcix2z2QxPukzulop1NQyoy64FFzsAl0.jpg', '2026-03-07 01:56:13'),
(390, 1059, 'desired design', 'project_files/designs/K81jo3QIG3llaSd0He9jyxK63iwKbfBrcGztSYuf.jpg', '2026-03-07 01:56:13'),
(391, 1059, 'others', 'project_files/others/CnDnVlGXrDCyKVTman4Acq0LjY9p7TTC4Y8g69x1.jpg', '2026-03-07 01:56:13'),
(392, 1059, 'others', 'project_files/others/rVp3DQ9Nr2Fua1frqKSUgjpGfmuSNoo7l5895hoN.jpg', '2026-03-07 01:56:13'),
(393, 1059, 'others', 'project_files/others/44e6GSdMN0lsLU8nK28ScTtvW08918o89XUpaYno.jpg', '2026-03-07 01:56:13'),
(394, 1059, 'others', 'project_files/others/eqHfkg3iGDeLmEUfoCtRWKqXAtIRDXvv40T76EF0.jpg', '2026-03-07 01:56:13'),
(395, 1060, 'building permit', 'project_files/building_permit/ZAbozTlcCAwkwp9ESGevy2sM6Lliu1qlumAWFFid.jpg', '2026-03-07 04:56:06'),
(396, 1060, 'title', 'project_files/titles/h4TpBHM8KWjrG5GFicJH8RrHsIiNpFcaS40rXqjD.docx', '2026-03-07 04:56:06'),
(397, 1060, 'blueprint', 'project_files/blueprints/hHAcdfaqFFCfzZigvsl3yOP5GOoEDcdv2Sx7HUaI.jpg', '2026-03-07 04:56:06'),
(398, 1060, 'blueprint', 'project_files/blueprints/o7iHjvCY32PD6YfJuzVxeB3KcCbJAr9a5oHnXoVJ.jpg', '2026-03-07 04:56:06'),
(399, 1060, 'blueprint', 'project_files/blueprints/RRxbbo39Yoqpy4vyjeakhaSeBEANw2NZWqI2XiHE.jpg', '2026-03-07 04:56:06'),
(400, 1060, 'blueprint', 'project_files/blueprints/r7Fwx1ZpYwYAvaskAVflzrTndPVkaFugCyB6WHt9.jpg', '2026-03-07 04:56:06'),
(401, 1060, 'desired design', 'project_files/designs/xpcHY9y56WaXv4s2UXl4rzQ6AdhRi4MAO65WQvLf.jpg', '2026-03-07 04:56:06'),
(402, 1060, 'desired design', 'project_files/designs/8Lc5OmgSUuciMPemZUrv4JYr0BlX4X2CSi6AqjJp.jpg', '2026-03-07 04:56:06'),
(403, 1060, 'desired design', 'project_files/designs/jryg2FCaBwHj0zLLUZIiRsAFKLZ6NGn7rn90u5at.jpg', '2026-03-07 04:56:06'),
(404, 1060, 'others', 'project_files/others/mzFAMqIwqL2qaVWvQ2N5QCBYpz7dCoctfgw9q1No.jpg', '2026-03-07 04:56:06'),
(405, 1060, 'others', 'project_files/others/Xb6rLMT5XjVKve4G4qUfaFKzWilrQ8nMJRt1TkZS.jpg', '2026-03-07 04:56:06'),
(406, 1060, 'others', 'project_files/others/uDZibUJLUpoFdwDLYZ3QLBh2MMVrK7wu0zzOsCkU.jpg', '2026-03-07 04:56:06'),
(407, 1060, 'others', 'project_files/others/WvcenrVv8cq2sYf5S8ib1cjwcqhUka9YKok6NdTW.jpg', '2026-03-07 04:56:06');

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
(1057, 1829, 1809, 'approved', NULL, '2026-03-31', '2026-03-03 21:16:54', '2026-03-04 05:21:42'),
(1058, 1829, NULL, 'approved', NULL, '2026-03-31', '2026-03-07 01:26:09', '2026-03-07 09:27:15'),
(1059, 1829, NULL, 'approved', NULL, '2026-03-31', '2026-03-07 01:56:13', '2026-03-07 09:56:56'),
(1060, 1829, NULL, 'approved', NULL, '2026-03-27', '2026-03-07 04:56:06', '2026-03-07 12:57:35'),
(1061, 1687, NULL, 'approved', NULL, '2026-03-23', '2026-03-09 03:06:55', '2026-03-09 03:06:55'),
(1062, 1687, NULL, 'approved', NULL, '2026-03-23', '2026-03-09 03:10:28', '2026-03-09 03:10:28'),
(1063, 1687, 1687, 'due', NULL, '2026-01-08', '2026-03-09 03:10:28', '2026-03-09 18:40:16'),
(1064, 1687, NULL, 'approved', NULL, '2026-03-23', '2026-03-09 03:12:51', '2026-03-09 03:12:51'),
(1065, 1687, 1687, 'due', NULL, '2026-01-08', '2026-03-09 03:12:51', '2026-03-09 18:40:16'),
(1066, 1687, NULL, 'approved', NULL, '2026-03-23', '2026-03-09 03:14:04', '2026-03-09 03:14:04'),
(1067, 1687, 1687, 'due', NULL, '2026-01-08', '2026-03-09 03:14:04', '2026-03-09 18:40:16'),
(1068, 1687, NULL, 'approved', NULL, '2026-03-23', '2026-03-09 03:16:10', '2026-03-09 03:16:10'),
(1069, 1687, 1687, 'due', NULL, '2026-01-08', '2026-03-09 03:16:10', '2026-03-09 18:40:16'),
(1070, 1687, NULL, 'approved', NULL, '2026-03-23', '2026-03-09 03:16:56', '2026-03-09 03:16:56'),
(1071, 1687, 1687, 'due', NULL, '2026-01-08', '2026-03-09 03:16:56', '2026-03-09 18:40:16'),
(1072, 1687, NULL, 'approved', NULL, '2026-03-23', '2026-03-09 03:18:30', '2026-03-09 03:18:30'),
(1073, 1687, 1687, 'due', NULL, '2026-01-08', '2026-03-09 03:18:30', '2026-03-09 18:40:16'),
(1074, 1687, NULL, 'approved', NULL, '2026-03-23', '2026-03-09 03:19:22', '2026-03-09 03:19:22'),
(1075, 1687, 1687, 'due', NULL, '2026-01-08', '2026-03-09 03:19:22', '2026-03-09 18:40:16'),
(1076, 1687, 1687, 'due', NULL, '2025-09-10', '2026-03-09 03:19:22', '2026-03-09 18:40:16'),
(1077, 1687, 1687, 'due', NULL, '2025-12-09', '2026-03-09 03:19:22', '2026-03-09 18:40:16'),
(1078, 1687, NULL, 'approved', NULL, '2026-03-23', '2026-03-09 03:19:53', '2026-03-09 03:19:53'),
(1079, 1687, 1687, 'due', NULL, '2026-01-08', '2026-03-09 03:19:53', '2026-03-09 18:40:16'),
(1080, 1687, 1687, 'due', NULL, '2025-09-10', '2026-03-09 03:19:53', '2026-03-09 18:40:16'),
(1081, 1687, 1687, 'due', NULL, '2025-12-09', '2026-03-09 03:19:53', '2026-03-09 18:40:16'),
(1082, 1687, 1687, 'due', NULL, '2025-11-09', '2026-03-09 03:19:53', '2026-03-09 18:40:16');

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
(4, 1056, 380, 379, '2026-03-07', NULL, 'kgzjgsktdkyddgkgkxhfl', 60000000.00, 60000000.00, 'none', 0, NULL, '{\"new_items\":[],\"edited_items\":[{\"item_id\":2790,\"start_date\":\"2026-02-23\",\"due_date\":\"2026-02-25\",\"_original\":{\"title\":\"Foundations\",\"cost\":20000000,\"percentage\":33.33,\"start_date\":null,\"due_date\":\"2026-02-26\"}},{\"item_id\":2791,\"start_date\":\"2026-02-26\",\"due_date\":\"2026-03-02\",\"_original\":{\"title\":\"Doners\",\"cost\":21000000,\"percentage\":35,\"start_date\":null,\"due_date\":\"2026-03-07 23:59:59\"}},{\"item_id\":2792,\"start_date\":\"2026-03-03\",\"_original\":{\"title\":\"extension\",\"cost\":19000000,\"percentage\":31.67,\"start_date\":null,\"due_date\":\"2026-03-07 23:59:59\"}}],\"deleted_item_ids\":[],\"_deleted_items\":[],\"_snapshot_meta\":{\"current_budget\":60000000,\"proposed_budget\":60000000,\"budget_change\":\"none\",\"allocation_mode\":\"percentage\",\"snapshot_at\":\"2026-02-27T12:07:06+00:00\"}}', 'percentage', 'approved', NULL, NULL, '2026-02-27 04:12:04', '2026-02-27 04:07:06', '2026-02-27 04:12:04'),
(5, 1057, 372, 392, '2026-03-31', '2026-04-30', 'Kasi wala budget! reason ko yon bakit ba', 9000000.00, 9000000.00, 'none', 0, NULL, '{\"new_items\":[],\"edited_items\":[],\"deleted_item_ids\":[],\"_deleted_items\":[],\"_snapshot_meta\":{\"current_budget\":9000000,\"proposed_budget\":9000000,\"budget_change\":\"none\",\"allocation_mode\":\"percentage\",\"snapshot_at\":\"2026-03-04T05:34:19+00:00\"}}', 'percentage', 'approved', NULL, NULL, '2026-03-03 21:34:56', '2026-03-03 21:34:19', '2026-03-03 21:34:56'),
(6, 1074, 1876, 101, '2026-04-08', '2026-04-23', 'Weather delays due to heavy rainfall affected the electrical installation phase. Additional 15 days needed to complete the work safely.', NULL, NULL, 'none', 0, NULL, NULL, NULL, 'approved', NULL, NULL, '2026-03-04 03:19:22', NULL, NULL),
(7, 1075, 1876, 101, NULL, NULL, 'Additional costs for premium smart home automation system and upgraded pool filtration system as requested by client.', 9000000.00, 9500000.00, 'increase', 0, NULL, NULL, NULL, 'approved', NULL, NULL, '2025-11-29 03:19:22', NULL, NULL),
(8, 1078, 1876, 101, '2026-04-08', '2026-04-23', 'Weather delays due to heavy rainfall affected the electrical installation phase. Additional 15 days needed to complete the work safely.', NULL, NULL, 'none', 0, NULL, NULL, NULL, 'approved', NULL, NULL, '2026-03-04 03:19:53', NULL, NULL),
(9, 1079, 1876, 101, NULL, NULL, 'Additional costs for premium smart home automation system and upgraded pool filtration system as requested by client.', 9000000.00, 9500000.00, 'increase', 0, NULL, NULL, NULL, 'approved', NULL, NULL, '2025-11-29 03:19:53', NULL, NULL),
(11, 1078, 1, 101, '2026-04-23', '2026-04-25', 'asdasdasdasdasdasdasdasdasdas', 6000000.00, 6000000.00, 'none', 0, NULL, NULL, NULL, 'approved', 'Admin override extension', NULL, '2026-03-08 21:29:15', '2026-03-08 21:29:15', '2026-03-08 21:29:15'),
(12, 1078, 1, 101, '2026-04-24', '2026-04-25', 'asdasdadasdadasdadasdasdas', 6000000.00, 6000000.00, 'none', 0, NULL, NULL, NULL, 'approved', 'Admin override extension', NULL, '2026-03-08 21:31:24', '2026-03-08 21:31:24', '2026-03-08 21:31:24'),
(13, 1080, 1, 101, '2026-05-28', '2026-05-30', 'asdadasdasdasdasdas', 14000000.00, 14000000.00, 'none', 0, NULL, NULL, NULL, 'approved', 'Admin override extension', NULL, '2026-03-08 21:36:41', '2026-03-08 21:36:41', '2026-03-08 21:36:41'),
(14, 1076, 1, 101, '2026-05-28', '2026-05-30', 'adasdasdasdasdasdasdasda', 14000000.00, 14000000.00, 'none', 0, NULL, NULL, NULL, 'approved', 'Admin override extension', NULL, '2026-03-08 21:57:21', '2026-03-08 21:57:21', '2026-03-08 21:57:21');

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

--
-- Dumping data for table `report_attachments`
--

INSERT INTO `report_attachments` (`attachment_id`, `report_type`, `report_id`, `original_name`, `file_path`, `mime_type`, `file_size`, `created_at`, `updated_at`) VALUES
(1, 'post_report', 3, '6443.jpg', 'report_attachments/post_report/3/d087e5ca-3f6f-48fd-938e-8e390a9b260b.jpg', 'image/jpeg', 242522, '2026-03-07 23:47:36', '2026-03-07 23:47:36'),
(2, 'post_report', 4, '6443.jpg', 'report_attachments/post_report/4/72c2c06c-9646-4582-a58d-1313283e9465.jpg', 'image/jpeg', 242522, '2026-03-07 23:54:35', '2026-03-07 23:54:35');

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
(40, 1015, 141, 4, 4, 'The contractor was easy to work with. Pricing was fair and the results are beautiful.', 0, NULL, '2026-02-27 00:39:16'),
(41, 1015, 4, 141, 4, 'Smooth coordination and clear vision. Made our job much easier.', 0, NULL, '2026-02-27 00:39:16'),
(42, 1016, 157, 8, 4, 'Good experience overall. There were some minor delays but the communication was great.', 1, 'sdfsrwef', '2026-03-02 00:39:16'),
(43, 1016, 8, 157, 4, 'The property owner was very collaborative. Smooth project from beginning to end.', 0, NULL, '2026-02-26 00:39:16'),
(44, 1017, 151, 14, 5, 'Excellent work! The project was completed on time and the quality is top-notch.', 0, NULL, '2026-02-24 00:39:16'),
(45, 1017, 14, 151, 5, 'Great client! Clear instructions and prompt payments. Looking forward to more projects.', 0, NULL, '2026-03-02 00:39:16'),
(46, 1018, 137, 16, 5, 'Excellent work! The project was completed on time and the quality is top-notch.', 0, NULL, '2026-02-28 00:39:16'),
(47, 1018, 16, 137, 5, 'The property owner was very collaborative. Smooth project from beginning to end.', 1, 'assssssssss', '2026-03-05 00:39:16'),
(48, 1019, 169, 16, 5, 'Good experience overall. There were some minor delays but the communication was great.', 1, 'asdasdasda', '2026-03-04 00:39:16'),
(49, 1019, 16, 169, 5, 'Excellent communication throughout the renovation. A pleasure to work with.', 0, NULL, '2026-03-01 00:39:16'),
(50, 1020, 107, 21, 4, 'Excellent work! The project was completed on time and the quality is top-notch.', 0, NULL, '2026-03-01 00:39:16'),
(51, 1020, 21, 107, 4, 'Excellent communication throughout the renovation. A pleasure to work with.', 0, NULL, '2026-02-27 00:39:16'),
(52, 1021, 124, 29, 5, 'Excellent work! The project was completed on time and the quality is top-notch.', 0, NULL, '2026-02-24 00:39:16'),
(53, 1021, 29, 124, 5, 'Smooth coordination and clear vision. Made our job much easier.', 0, NULL, '2026-02-25 00:39:16'),
(54, 1022, 133, 31, 5, 'The contractor was easy to work with. Pricing was fair and the results are beautiful.', 0, NULL, '2026-02-27 00:39:16'),
(55, 1022, 31, 133, 4, 'Good client, very reasonable with timelines and change requests.', 1, 'Testing toast design', '2026-03-03 00:39:16'),
(56, 1023, 152, 31, 4, 'Good experience overall. There were some minor delays but the communication was great.', 0, NULL, '2026-02-25 00:39:16'),
(57, 1023, 31, 152, 4, 'The property owner was very collaborative. Smooth project from beginning to end.', 0, NULL, '2026-02-25 00:39:16'),
(58, 1024, 121, 32, 5, 'Excellent work! The project was completed on time and the quality is top-notch.', 0, NULL, '2026-02-26 00:39:16'),
(59, 1024, 32, 121, 4, 'Good client, very reasonable with timelines and change requests.', 0, NULL, '2026-02-24 00:39:16'),
(60, 1025, 174, 32, 4, 'Very professional team. They listened to our requirements and delivered exactly what we needed.', 0, NULL, '2026-02-24 00:39:16'),
(61, 1025, 32, 174, 4, 'The property owner was very collaborative. Smooth project from beginning to end.', 0, NULL, '2026-02-26 00:39:16'),
(62, 1026, 122, 39, 5, 'The contractor was easy to work with. Pricing was fair and the results are beautiful.', 0, NULL, '2026-02-26 00:39:16'),
(63, 1026, 39, 122, 5, 'Excellent communication throughout the renovation. A pleasure to work with.', 0, NULL, '2026-02-26 00:39:16'),
(64, 1027, 102, 41, 5, 'Excellent work! The project was completed on time and the quality is top-notch.', 0, NULL, '2026-02-24 00:39:16'),
(65, 1027, 41, 102, 4, 'Excellent communication throughout the renovation. A pleasure to work with.', 0, NULL, '2026-02-24 00:39:16'),
(66, 1028, 172, 44, 5, 'The contractor was easy to work with. Pricing was fair and the results are beautiful.', 0, NULL, '2026-02-26 00:39:16'),
(67, 1028, 44, 172, 4, 'Good client, very reasonable with timelines and change requests.', 1, 'Testing deletion', '2026-03-04 00:39:16'),
(68, 1029, 129, 47, 5, 'Excellent work! The project was completed on time and the quality is top-notch.', 0, NULL, '2026-02-25 00:39:16'),
(69, 1029, 47, 129, 4, 'The property owner was very collaborative. Smooth project from beginning to end.', 0, NULL, '2026-03-01 00:39:16'),
(70, 1030, 131, 49, 4, 'Excellent work! The project was completed on time and the quality is top-notch.', 1, 'niggers', '2026-03-03 00:39:16'),
(71, 1030, 49, 131, 5, 'Excellent communication throughout the renovation. A pleasure to work with.', 0, NULL, '2026-03-01 00:39:16'),
(72, 1031, 168, 53, 5, 'Very professional team. They listened to our requirements and delivered exactly what we needed.', 0, NULL, '2026-03-02 00:39:16'),
(73, 1031, 53, 168, 4, 'Excellent communication throughout the renovation. A pleasure to work with.', 0, NULL, '2026-03-02 00:39:16'),
(74, 1032, 175, 54, 4, 'Highly recommended for home renovations. They handled everything from start to finish.', 0, NULL, '2026-03-01 00:39:16'),
(75, 1032, 54, 175, 5, 'Great client! Clear instructions and prompt payments. Looking forward to more projects.', 1, 'Test deletion', '2026-03-05 00:39:16'),
(76, 1033, 101, 59, 5, 'Good experience overall. There were some minor delays but the communication was great.', 0, NULL, '2026-02-26 00:39:16'),
(77, 1034, 156, 69, 4, 'Excellent work! The project was completed on time and the quality is top-notch.', 0, NULL, '2026-02-27 00:39:16'),
(78, 1034, 69, 156, 5, 'Good client, very reasonable with timelines and change requests.', 0, NULL, '2026-03-02 00:39:16');

-- --------------------------------------------------------

--
-- Table structure for table `review_reports`
--

CREATE TABLE `review_reports` (
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `reporter_user_id` bigint(20) UNSIGNED NOT NULL,
  `review_id` bigint(20) UNSIGNED NOT NULL,
  `reason` varchar(120) NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','under_review','resolved','dismissed') NOT NULL DEFAULT 'pending',
  `reviewed_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 371, 'Testing Purposes Test', 'gnjejtbttbfheejggjjgeeyyyjiij', NULL, 'Zamboanga City', 'approved', 1, '2026-03-05 01:46:22', NULL, '', NULL, '2026-03-05 01:46:09', '2026-03-05 01:46:22'),
(2, 372, 'Modern world', 'just because okay', NULL, 'Zamboanga City', 'approved', 1, '2026-03-07 10:29:40', NULL, '', NULL, '2026-03-07 10:22:48', '2026-03-07 10:29:40');

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
(1, 1, 'post_images/1772703969_post_1_0.jpg', '6362.jpg', 0, '2026-03-05 01:46:09', '2026-03-05 01:46:09'),
(2, 1, 'post_images/1772703969_post_1_1.jpg', '6363.jpg', 1, '2026-03-05 01:46:09', '2026-03-05 01:46:09'),
(3, 1, 'post_images/1772703969_post_1_2.jpg', '6366.jpg', 2, '2026-03-05 01:46:09', '2026-03-05 01:46:09'),
(4, 1, 'post_images/1772703969_post_1_3.jpg', '6364.jpg', 3, '2026-03-05 01:46:09', '2026-03-05 01:46:09'),
(5, 1, 'post_images/1772703969_post_1_4.jpg', '6365.jpg', 4, '2026-03-05 01:46:09', '2026-03-05 01:46:09'),
(6, 2, 'post_images/1772907768_post_2_0.jpg', '6388.jpg', 0, '2026-03-07 10:22:48', '2026-03-07 10:22:48'),
(7, 2, 'post_images/1772907768_post_2_1.jpg', '6389.jpg', 1, '2026-03-07 10:22:48', '2026-03-07 10:22:48'),
(8, 2, 'post_images/1772907768_post_2_2.jpg', '6390.jpg', 2, '2026-03-07 10:22:48', '2026-03-07 10:22:48'),
(9, 2, 'post_images/1772907768_post_2_3.jpg', '6391.jpg', 3, '2026-03-07 10:22:48', '2026-03-07 10:22:48'),
(10, 2, 'post_images/1772907768_post_2_4.jpg', '6394.jpg', 4, '2026-03-07 10:22:48', '2026-03-07 10:22:48'),
(11, 2, 'post_images/1772907768_post_2_5.jpg', '6395.jpg', 5, '2026-03-07 10:22:48', '2026-03-07 10:22:48'),
(12, 2, 'post_images/1772907768_post_2_6.jpg', '6393.jpg', 6, '2026-03-07 10:22:48', '2026-03-07 10:22:48'),
(13, 2, 'post_images/1772907768_post_2_7.jpg', '6392.jpg', 7, '2026-03-07 10:22:48', '2026-03-07 10:22:48'),
(14, 2, 'post_images/1772907768_post_2_8.jpg', '6396.jpg', 8, '2026-03-07 10:22:48', '2026-03-07 10:22:48'),
(15, 2, 'post_images/1772907768_post_2_9.jpg', '6397.jpg', 9, '2026-03-07 10:22:48', '2026-03-07 10:22:48');

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
(1, 'gold', 1, 'Gold Tier Subscriptions', 199900, 'PHP', 'monthly', NULL, NULL, '[\"Unlock AI driven analytics\",\"Unlimited Bids per month\",\"Boost Bids\"]', 1, 0, '2026-02-28 05:15:48', '2026-03-02 20:41:31'),
(2, 'silver', 1, 'Silver Tier Subscription', 149900, 'PHP', 'monthly', NULL, NULL, '[\"25 Bids per month\",\"Boost Bids\"]', 1, 0, '2026-02-28 05:15:48', '2026-02-28 21:27:29'),
(3, 'bronze', 1, 'Bronze Tier Subscription', 99900, 'PHP', 'monthly', NULL, NULL, '[\"10 Bids per month\"]', 1, 0, '2026-02-28 05:15:48', '2026-02-28 05:15:48'),
(4, 'boost', 0, 'Project Boost', 4900, 'PHP', 'one-time', 7, '', '[\"7 Days Visibility Boost\"]', 1, 0, '2026-02-28 05:15:48', '2026-02-28 21:59:43'),
(5, 'sssss', 0, 'dedede', 50000, 'PHP', 'monthly', NULL, 'aaaaaaaaaaaaaaaaaaaaaaaaaaaa', '[\"dededede\"]', 0, 1, '2026-02-28 10:30:34', '2026-02-28 21:10:56'),
(7, 'GRRR', 0, 'dedddddddddddddddddddddd', 21300, 'PHP', 'one-time', 23, 'dfdddddddddddddddddddddd', '[\"NIGGER\"]', 0, 1, '2026-02-28 21:25:35', '2026-02-28 22:05:50'),
(10, 'sdffsd', 0, 'Shibal', 1231200, 'PHP', 'one-time', 34, 'adsdasdsd', '[\"sfsdfsdfdsf\"]', 0, 1, '2026-03-02 20:42:19', '2026-03-02 20:42:25');

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

--
-- Dumping data for table `termination_proof`
--

INSERT INTO `termination_proof` (`proof_id`, `termination_id`, `file_path`, `uploaded_at`) VALUES
(1, 3, '1', '2026-02-07 04:31:34'),
(2, 3, '1', '2026-02-08 04:31:34');

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
(1, 'failed_login_attempt', 372, NULL, NULL, '{\"attempts\":1,\"ip\":\"127.0.0.1\"}', 0, '2026-03-09 03:43:57'),
(2, 'failed_login_attempt', NULL, NULL, NULL, '{\"attempts\":2,\"ip\":\"127.0.0.1\"}', 0, '2026-03-09 03:44:40'),
(3, 'failed_login_attempt', NULL, NULL, NULL, '{\"attempts\":3,\"ip\":\"127.0.0.1\"}', 0, '2026-03-09 03:44:59'),
(4, 'failed_login_attempt', NULL, NULL, NULL, '{\"attempts\":4,\"ip\":\"127.0.0.1\"}', 0, '2026-03-09 04:23:33'),
(5, 'failed_login_attempt', 372, NULL, NULL, '{\"attempts\":5,\"ip\":\"127.0.0.1\"}', 0, '2026-03-09 04:23:56');

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
  ADD PRIMARY KEY (`report_id`);

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
  ADD KEY `idx_dp_status` (`payment_status`);

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
  ADD KEY `project_extensions_project_id_status_index` (`project_id`,`status`);

--
-- Indexes for table `property_owners`
--
ALTER TABLE `property_owners`
  ADD PRIMARY KEY (`owner_id`),
  ADD KEY `fk_owner_user` (`user_id`);

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
  ADD KEY `review_reports_status_index` (`status`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_notification_preferences`
--
ALTER TABLE `admin_notification_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `admin_sent_notifications`
--
ALTER TABLE `admin_sent_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_prediction_logs`
--
ALTER TABLE `ai_prediction_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bids`
--
ALTER TABLE `bids`
  MODIFY `bid_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=317;

--
-- AUTO_INCREMENT for table `bid_files`
--
ALTER TABLE `bid_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `content_reports`
--
ALTER TABLE `content_reports`
  MODIFY `report_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contractors`
--
ALTER TABLE `contractors`
  MODIFY `contractor_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contractor_staff`
--
ALTER TABLE `contractor_staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contractor_types`
--
ALTER TABLE `contractor_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `contract_terminations`
--
ALTER TABLE `contract_terminations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `conversation_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=372000393;

--
-- AUTO_INCREMENT for table `disputes`
--
ALTER TABLE `disputes`
  MODIFY `dispute_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `dispute_files`
--
ALTER TABLE `dispute_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `downpayment_payments`
--
ALTER TABLE `downpayment_payments`
  MODIFY `dp_payment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `item_files`
--
ALTER TABLE `item_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=423;

--
-- AUTO_INCREMENT for table `message_attachments`
--
ALTER TABLE `message_attachments`
  MODIFY `attachment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `milestones`
--
ALTER TABLE `milestones`
  MODIFY `milestone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1627;

--
-- AUTO_INCREMENT for table `milestone_date_histories`
--
ALTER TABLE `milestone_date_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `milestone_items`
--
ALTER TABLE `milestone_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2859;

--
-- AUTO_INCREMENT for table `milestone_item_updates`
--
ALTER TABLE `milestone_item_updates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `milestone_payments`
--
ALTER TABLE `milestone_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=853;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3802;

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
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=947;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT for table `platform_payments`
--
ALTER TABLE `platform_payments`
  MODIFY `platform_payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT for table `post_reports`
--
ALTER TABLE `post_reports`
  MODIFY `report_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1085;

--
-- AUTO_INCREMENT for table `project_files`
--
ALTER TABLE `project_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=408;

--
-- AUTO_INCREMENT for table `project_relationships`
--
ALTER TABLE `project_relationships`
  MODIFY `rel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1086;

--
-- AUTO_INCREMENT for table `project_updates`
--
ALTER TABLE `project_updates`
  MODIFY `extension_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `property_owners`
--
ALTER TABLE `property_owners`
  MODIFY `owner_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_attachments`
--
ALTER TABLE `report_attachments`
  MODIFY `attachment_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `review_reports`
--
ALTER TABLE `review_reports`
  MODIFY `report_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `showcases`
--
ALTER TABLE `showcases`
  MODIFY `post_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `showcase_images`
--
ALTER TABLE `showcase_images`
  MODIFY `image_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `termination_proof`
--
ALTER TABLE `termination_proof`
  MODIFY `proof_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- Constraints for table `post_reports`
--
ALTER TABLE `post_reports`
  ADD CONSTRAINT `post_reports_reporter_user_id_foreign` FOREIGN KEY (`reporter_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_reports_reviewed_by_user_id_foreign` FOREIGN KEY (`reviewed_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

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
  ADD CONSTRAINT `fk_owner_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`reviewer_user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`reviewee_user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `showcases`
--
ALTER TABLE `showcases`
  ADD CONSTRAINT `project_posts_linked_project_id_foreign` FOREIGN KEY (`linked_project_id`) REFERENCES `projects` (`project_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `showcase_images`
--
ALTER TABLE `showcase_images`
  ADD CONSTRAINT `project_post_images_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `showcases` (`post_id`) ON DELETE CASCADE;

--
-- Constraints for table `termination_proof`
--
ALTER TABLE `termination_proof`
  ADD CONSTRAINT `fk_proof_termination_link` FOREIGN KEY (`termination_id`) REFERENCES `contract_terminations` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
