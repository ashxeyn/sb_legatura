-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 05, 2026 at 10:24 AM
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
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`admin_id`, `username`, `email`, `password_hash`, `last_name`, `middle_name`, `first_name`, `is_active`, `created_at`) VALUES
('ADMIN-1', 'legatura_hq2026', 'sandbox.info.official@gmail.com', '$2y$12$.2ali6CucAVDUpSsjlvlPucDtp2j8gI4Ml7pbKtyZ0fpCiml.nVVe', 'Ph', '', 'Legatura', 1, '2026-03-05 00:07:36');

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
-- Table structure for table `construction_types`
--

CREATE TABLE `construction_types` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `construction_types`
--

INSERT INTO `construction_types` (`type_id`, `type_name`) VALUES
(1, 'House Construction'),
(2, 'Building Construction'),
(3, 'Renovation'),
(4, 'Extension'),
(5, 'Repair'),
(6, 'Commercial Construction'),
(7, 'Industrial Construction'),
(8, 'Infrastructure'),
(9, 'General Contractor');

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
(2, 1000, NULL, NULL, NULL, 'Alexander Mitchell Construction Co.', '2018-03-05', 16, 2, NULL, 'General Construction, Renovation, Repair Services', 'Zamboanga City, Philippines', 'alexandermitchell.construction1000@business.com', '09171886575', NULL, NULL, NULL, 'PCAB-16499', 'A', '2028-03-05', 'BP-4798', 'Zamboanga City', '2027-03-05', 'TIN-797877', 'dti_cert_1000.jpg', 'approved', '2025-12-21 00:21:23', 1, NULL, NULL, NULL, NULL, 34, '2025-04-12 00:21:23', '2026-02-07 00:21:23'),
(3, 1001, NULL, NULL, NULL, 'Sophia Davis Construction Co.', '2020-03-05', 24, 4, NULL, 'General Construction, Renovation, Repair Services', 'Zamboanga City, Philippines', 'sophiadavis.construction1001@business.com', '09176335721', NULL, NULL, NULL, 'PCAB-36069', 'AAAA', '2028-03-05', 'BP-4936', 'Zamboanga City', '2027-03-05', 'TIN-947531', 'dti_cert_1001.jpg', 'pending', NULL, 0, NULL, NULL, NULL, NULL, 0, '2025-10-31 00:21:23', '2026-02-09 00:21:23'),
(4, 1002, NULL, NULL, NULL, 'Liam Clark Construction Co.', '2005-03-05', 18, 6, NULL, 'General Construction, Renovation, Repair Services', 'Zamboanga City, Philippines', 'liamclark.construction1002@business.com', '09174422377', NULL, NULL, NULL, 'PCAB-28608', 'AA', '2028-03-05', 'BP-9480', 'Zamboanga City', '2027-03-05', 'TIN-559716', 'dti_cert_1002.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, 'Incomplete documentation', 0, '2025-07-12 00:21:23', '2026-02-23 00:21:23'),
(5, 1003, NULL, NULL, NULL, 'Olivia Martinez Construction Co.', '2021-03-05', 15, 5, NULL, 'General Construction, Renovation, Repair Services', 'Zamboanga City, Philippines', 'oliviamartinez.construction1003@business.com', '09173474276', NULL, NULL, NULL, 'PCAB-54587', 'AAA', '2028-03-05', 'BP-2222', 'Zamboanga City', '2027-03-05', 'TIN-382950', 'dti_cert_1003.jpg', 'approved', '2026-02-08 00:21:23', 1, NULL, NULL, NULL, NULL, 4, '2025-05-06 00:21:23', '2026-02-14 00:21:23'),
(6, 1004, NULL, NULL, NULL, 'Noah Rodriguez Construction Co.', '2001-03-05', 15, 1, NULL, 'General Construction, Renovation, Repair Services', 'Zamboanga City, Philippines', 'noahrodriguez.construction1004@business.com', '09179726668', NULL, NULL, NULL, 'PCAB-53941', 'C', '2028-03-05', 'BP-2399', 'Zamboanga City', '2027-03-05', 'TIN-895088', 'dti_cert_1004.jpg', 'pending', NULL, 0, NULL, NULL, NULL, NULL, 0, '2025-06-17 00:21:24', '2026-02-22 00:21:24'),
(7, 1005, NULL, NULL, NULL, 'Emma Turner Construction Co.', '2001-03-05', 11, 7, NULL, 'General Construction, Renovation, Repair Services', 'Zamboanga City, Philippines', 'emmaturner.construction1005@business.com', '09173573999', NULL, NULL, NULL, 'PCAB-98882', 'AA', '2028-03-05', 'BP-7972', 'Zamboanga City', '2027-03-05', 'TIN-871503', 'dti_cert_1005.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, 'Incomplete documentation', 0, '2025-06-27 00:21:24', '2026-02-21 00:21:24'),
(8, 1006, NULL, NULL, NULL, 'Elijah White Construction Co.', '2006-03-05', 16, 1, NULL, 'General Construction, Renovation, Repair Services', 'Zamboanga City, Philippines', 'elijahwhite.construction1006@business.com', '09174743447', NULL, NULL, NULL, 'PCAB-84715', 'AAA', '2028-03-05', 'BP-3197', 'Zamboanga City', '2027-03-05', 'TIN-527423', 'dti_cert_1006.jpg', 'approved', '2026-02-24 00:21:24', 1, NULL, NULL, NULL, NULL, 35, '2025-12-10 00:21:24', '2026-02-15 00:21:24'),
(9, 1007, NULL, NULL, NULL, 'Ava Thompson Construction Co.', '2008-03-05', 7, 8, NULL, 'General Construction, Renovation, Repair Services', 'Zamboanga City, Philippines', 'avathompson.construction1007@business.com', '09177978286', NULL, NULL, NULL, 'PCAB-57468', 'A', '2028-03-05', 'BP-9551', 'Zamboanga City', '2027-03-05', 'TIN-133130', 'dti_cert_1007.jpg', 'pending', NULL, 0, NULL, NULL, NULL, NULL, 0, '2025-12-17 00:21:24', '2026-02-06 00:21:24'),
(10, 1008, NULL, NULL, NULL, 'Mateo Sanchez Construction Co.', '2023-03-05', 14, 7, NULL, 'General Construction, Renovation, Repair Services', 'Zamboanga City, Philippines', 'mateosanchez.construction1008@business.com', '09176706018', NULL, NULL, NULL, 'PCAB-18807', 'C', '2028-03-05', 'BP-7597', 'Zamboanga City', '2027-03-05', 'TIN-486013', 'dti_cert_1008.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, 'Incomplete documentation', 0, '2026-01-26 00:21:24', '2026-02-14 00:21:24'),
(11, 1009, NULL, NULL, NULL, 'Isabella Walker Construction Co.', '2019-03-05', 4, 9, NULL, 'General Construction, Renovation, Repair Services', 'Zamboanga City, Philippines', 'isabellawalker.construction1009@business.com', '09177080530', NULL, NULL, NULL, 'PCAB-15569', 'AAA', '2028-03-05', 'BP-5462', 'Zamboanga City', '2027-03-05', 'TIN-276422', 'dti_cert_1009.jpg', 'approved', '2026-01-01 00:21:25', 1, NULL, NULL, NULL, NULL, 17, '2025-07-28 00:21:25', '2026-02-17 00:21:25'),
(12, 1010, NULL, NULL, NULL, 'Lucas Wright Construction Co.', '2024-03-05', 4, 7, NULL, 'General Construction, Renovation, Repair Services', 'Zamboanga City, Philippines', 'lucaswright.construction1010@business.com', '09174018209', NULL, NULL, NULL, 'PCAB-38694', 'C', '2028-03-05', 'BP-3653', 'Zamboanga City', '2027-03-05', 'TIN-269796', 'dti_cert_1010.jpg', 'pending', NULL, 0, NULL, NULL, NULL, NULL, 0, '2026-02-25 00:21:25', '2026-02-16 00:21:25'),
(13, 1011, NULL, NULL, NULL, 'Mia Scott Construction Co.', '2003-03-05', 16, 4, NULL, 'General Construction, Renovation, Repair Services', 'Zamboanga City, Philippines', 'miascott.construction1011@business.com', '09173429316', NULL, NULL, NULL, 'PCAB-31358', 'Trade/E', '2028-03-05', 'BP-9206', 'Zamboanga City', '2027-03-05', 'TIN-450530', 'dti_cert_1011.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, 'Incomplete documentation', 0, '2025-10-18 00:21:25', '2026-02-20 00:21:25'),
(14, 1012, NULL, NULL, NULL, 'Ethan Green Construction Co.', '2023-03-05', 21, 7, NULL, 'General Construction, Renovation, Repair Services', 'Zamboanga City, Philippines', 'ethangreen.construction1012@business.com', '09171039365', NULL, NULL, NULL, 'PCAB-42993', 'B', '2028-03-05', 'BP-8112', 'Zamboanga City', '2027-03-05', 'TIN-181029', 'dti_cert_1012.jpg', 'approved', '2025-12-12 00:21:25', 1, NULL, NULL, NULL, NULL, 38, '2025-09-08 00:21:25', '2026-02-25 00:21:25'),
(15, 1013, NULL, NULL, NULL, 'Charlotte Adams Construction Co.', '2009-03-05', 22, 1, NULL, 'General Construction, Renovation, Repair Services', 'Zamboanga City, Philippines', 'charlotteadams.construction1013@business.com', '09173638012', NULL, NULL, NULL, 'PCAB-88667', 'AA', '2028-03-05', 'BP-5474', 'Zamboanga City', '2027-03-05', 'TIN-347789', 'dti_cert_1013.jpg', 'pending', NULL, 0, NULL, NULL, NULL, NULL, 0, '2025-05-06 00:21:25', '2026-02-26 00:21:25'),
(16, 1014, NULL, NULL, NULL, 'Mason Baker Construction Co.', '2017-03-05', 11, 9, NULL, 'General Construction, Renovation, Repair Services', 'Zamboanga City, Philippines', 'masonbaker.construction1014@business.com', '09178435517', NULL, NULL, NULL, 'PCAB-50733', 'D', '2028-03-05', 'BP-6326', 'Zamboanga City', '2027-03-05', 'TIN-292523', 'dti_cert_1014.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, 'Incomplete documentation', 0, '2025-10-25 00:21:26', '2026-03-01 00:21:26'),
(17, 1015, NULL, NULL, NULL, 'Amelia Nelson Construction Co.', '2005-03-05', 2, 5, NULL, 'General Construction, Renovation, Repair Services', 'Zamboanga City, Philippines', 'amelianelson.construction1015@business.com', '09177855694', NULL, NULL, NULL, 'PCAB-16541', 'AAAA', '2028-03-05', 'BP-8842', 'Zamboanga City', '2027-03-05', 'TIN-482524', 'dti_cert_1015.jpg', 'deleted', NULL, 1, NULL, NULL, 'User requested account deletion', NULL, 0, '2025-10-22 00:21:26', '2026-02-19 00:21:26'),
(18, 1016, NULL, NULL, NULL, 'Logan Carter Construction Co.', '2001-03-05', 12, 4, NULL, 'General Construction, Renovation, Repair Services', 'Zamboanga City, Philippines', 'logancarter.construction1016@business.com', '09177989607', NULL, NULL, NULL, 'PCAB-96926', 'C', '2028-03-05', 'BP-6018', 'Zamboanga City', '2027-03-05', 'TIN-987135', 'dti_cert_1016.jpg', 'pending', NULL, 0, NULL, NULL, NULL, NULL, 0, '2025-08-07 00:21:26', '2026-02-07 00:21:26'),
(19, 1017, NULL, NULL, NULL, 'Harper Perez Construction Co.', '2006-03-05', 13, 5, NULL, 'General Construction, Renovation, Repair Services', 'Zamboanga City, Philippines', 'harperperez.construction1017@business.com', '09175393943', NULL, NULL, NULL, 'PCAB-77000', 'AAAA', '2028-03-05', 'BP-9532', 'Zamboanga City', '2027-03-05', 'TIN-394785', 'dti_cert_1017.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, 'Incomplete documentation', 0, '2025-07-23 00:21:26', '2026-02-21 00:21:26'),
(20, 1018, NULL, NULL, NULL, 'Jackson Roberts Construction Co.', '2011-03-05', 21, 4, NULL, 'General Construction, Renovation, Repair Services', 'Zamboanga City, Philippines', 'jacksonroberts.construction1018@business.com', '09173846190', NULL, NULL, NULL, 'PCAB-63671', 'C', '2028-03-05', 'BP-2057', 'Zamboanga City', '2027-03-05', 'TIN-574557', 'dti_cert_1018.jpg', 'approved', '2026-02-14 00:21:27', 1, '2026-04-04', 'Temporary suspension for review', NULL, NULL, 31, '2025-09-21 00:21:27', '2026-02-09 00:21:27'),
(21, 1019, NULL, NULL, NULL, 'Evelyn Flores Construction Co.', '2003-03-05', 17, 3, NULL, 'General Construction, Renovation, Repair Services', 'Zamboanga City, Philippines', 'evelynflores.construction1019@business.com', '09177677978', NULL, NULL, NULL, 'PCAB-90077', 'B', '2028-03-05', 'BP-5821', 'Zamboanga City', '2027-03-05', 'TIN-618192', 'dti_cert_1019.jpg', 'pending', NULL, 0, NULL, NULL, NULL, NULL, 0, '2025-03-05 00:21:27', '2026-02-03 00:21:27'),
(22, 1040, NULL, NULL, NULL, 'Christopher Sanders Multi-Services', '2024-03-05', 7, 9, NULL, 'Construction, Property Management, Consulting', 'Zamboanga City, Philippines', 'christophersanders.services1040@business.com', '09171919259', NULL, NULL, NULL, 'PCAB-42934', 'C', '2028-03-05', 'BP-4433', 'Zamboanga City', '2027-03-05', 'TIN-231761', 'dti_cert_1040.jpg', 'pending', NULL, 0, NULL, NULL, NULL, NULL, 0, '2025-04-02 00:21:31', '2026-02-20 00:21:31'),
(23, 1041, NULL, NULL, NULL, 'Stella Price Multi-Services', '2015-03-05', 8, 6, NULL, 'Construction, Property Management, Consulting', 'Zamboanga City, Philippines', 'stellaprice.services1041@business.com', '09178862112', NULL, NULL, NULL, 'PCAB-90714', 'C', '2028-03-05', 'BP-6145', 'Zamboanga City', '2027-03-05', 'TIN-482837', 'dti_cert_1041.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, 'Insufficient business documentation', 0, '2025-11-06 00:21:31', '2026-02-27 00:21:31'),
(24, 1042, NULL, NULL, NULL, 'Josiah Bennett Multi-Services', '2018-03-05', 8, 9, NULL, 'Construction, Property Management, Consulting', 'Zamboanga City, Philippines', 'josiahbennett.services1042@business.com', '09171946617', NULL, NULL, NULL, 'PCAB-47833', 'C', '2028-03-05', 'BP-5565', 'Zamboanga City', '2027-03-05', 'TIN-710215', 'dti_cert_1042.jpg', 'approved', '2026-02-28 00:21:32', 1, NULL, NULL, NULL, NULL, 24, '2025-08-20 00:21:32', '2026-02-08 00:21:32'),
(25, 1043, NULL, NULL, NULL, 'Hazel Wood Multi-Services', '2013-03-05', 16, 1, NULL, 'Construction, Property Management, Consulting', 'Zamboanga City, Philippines', 'hazelwood.services1043@business.com', '09179116983', NULL, NULL, NULL, 'PCAB-82016', 'Trade/E', '2028-03-05', 'BP-6848', 'Zamboanga City', '2027-03-05', 'TIN-147228', 'dti_cert_1043.jpg', 'pending', NULL, 0, NULL, NULL, NULL, NULL, 0, '2025-03-24 00:21:32', '2026-02-23 00:21:32'),
(26, 1044, NULL, NULL, NULL, 'Andrew Barnes Multi-Services', '2006-03-05', 8, 2, NULL, 'Construction, Property Management, Consulting', 'Zamboanga City, Philippines', 'andrewbarnes.services1044@business.com', '09178863256', NULL, NULL, NULL, 'PCAB-67399', 'D', '2028-03-05', 'BP-7896', 'Zamboanga City', '2027-03-05', 'TIN-740262', 'dti_cert_1044.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, 'Insufficient business documentation', 0, '2025-07-08 00:21:32', '2026-02-06 00:21:32'),
(27, 1045, NULL, NULL, NULL, 'Aurora Ross Multi-Services', '2008-03-05', 19, 3, NULL, 'Construction, Property Management, Consulting', 'Zamboanga City, Philippines', 'auroraross.services1045@business.com', '09175783276', NULL, NULL, NULL, 'PCAB-88633', 'C', '2028-03-05', 'BP-8137', 'Zamboanga City', '2027-03-05', 'TIN-238399', 'dti_cert_1045.jpg', 'approved', '2026-02-10 00:21:32', 1, NULL, NULL, NULL, NULL, 23, '2025-04-25 00:21:32', '2026-02-14 00:21:32'),
(28, 1046, NULL, NULL, NULL, 'Thomas Henderson Multi-Services', '2015-03-05', 3, 1, NULL, 'Construction, Property Management, Consulting', 'Zamboanga City, Philippines', 'thomashenderson.services1046@business.com', '09171596327', NULL, NULL, NULL, 'PCAB-24715', 'Trade/E', '2028-03-05', 'BP-1027', 'Zamboanga City', '2027-03-05', 'TIN-295457', 'dti_cert_1046.jpg', 'pending', NULL, 0, NULL, NULL, NULL, NULL, 0, '2025-08-14 00:21:32', '2026-02-13 00:21:32'),
(29, 1047, NULL, NULL, NULL, 'Lucy Coleman Multi-Services', '2024-03-05', 11, 3, NULL, 'Construction, Property Management, Consulting', 'Zamboanga City, Philippines', 'lucycoleman.services1047@business.com', '09171673997', NULL, NULL, NULL, 'PCAB-74155', 'D', '2028-03-05', 'BP-4671', 'Zamboanga City', '2027-03-05', 'TIN-694135', 'dti_cert_1047.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, 'Insufficient business documentation', 0, '2025-09-06 00:21:33', '2026-02-07 00:21:33'),
(30, 1048, NULL, NULL, NULL, 'Caleb Jenkins Multi-Services', '2023-03-05', 13, 4, NULL, 'Construction, Property Management, Consulting', 'Zamboanga City, Philippines', 'calebjenkins.services1048@business.com', '09174979801', NULL, NULL, NULL, 'PCAB-85416', 'Trade/E', '2028-03-05', 'BP-5737', 'Zamboanga City', '2027-03-05', 'TIN-988551', 'dti_cert_1048.jpg', 'approved', '2026-01-18 00:21:33', 1, NULL, NULL, NULL, NULL, 14, '2026-01-03 00:21:33', '2026-02-20 00:21:33'),
(31, 1049, NULL, NULL, NULL, 'Savannah Perry Multi-Services', '2020-03-05', 9, 1, NULL, 'Construction, Property Management, Consulting', 'Zamboanga City, Philippines', 'savannahperry.services1049@business.com', '09173577396', NULL, NULL, NULL, 'PCAB-45448', 'D', '2028-03-05', 'BP-8545', 'Zamboanga City', '2027-03-05', 'TIN-845411', 'dti_cert_1049.jpg', 'pending', NULL, 0, NULL, NULL, NULL, NULL, 0, '2025-06-16 00:21:33', '2026-03-03 00:21:33'),
(32, 1050, NULL, NULL, NULL, 'Ryan Powell Multi-Services', '2014-03-05', 18, 3, NULL, 'Construction, Property Management, Consulting', 'Zamboanga City, Philippines', 'ryanpowell.services1050@business.com', '09178187579', NULL, NULL, NULL, 'PCAB-50110', 'B', '2028-03-05', 'BP-3097', 'Zamboanga City', '2027-03-05', 'TIN-752844', 'dti_cert_1050.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, 'Insufficient business documentation', 0, '2025-11-17 00:21:33', '2026-02-07 00:21:33'),
(33, 1051, NULL, NULL, NULL, 'Brooklyn Long Multi-Services', '2015-03-05', 14, 8, NULL, 'Construction, Property Management, Consulting', 'Zamboanga City, Philippines', 'brooklynlong.services1051@business.com', '09172245287', NULL, NULL, NULL, 'PCAB-68865', 'B', '2028-03-05', 'BP-1688', 'Zamboanga City', '2027-03-05', 'TIN-733295', 'dti_cert_1051.jpg', 'approved', '2026-01-05 00:21:33', 1, NULL, NULL, NULL, NULL, 13, '2025-10-13 00:21:33', '2026-03-04 00:21:33'),
(34, 1052, NULL, NULL, NULL, 'Nathan Patterson Multi-Services', '2012-03-05', 12, 2, NULL, 'Construction, Property Management, Consulting', 'Zamboanga City, Philippines', 'nathanpatterson.services1052@business.com', '09176174873', NULL, NULL, NULL, 'PCAB-81840', 'C', '2028-03-05', 'BP-5805', 'Zamboanga City', '2027-03-05', 'TIN-461769', 'dti_cert_1052.jpg', 'pending', NULL, 0, NULL, NULL, NULL, NULL, 0, '2025-11-21 00:21:34', '2026-02-09 00:21:34'),
(35, 1053, NULL, NULL, NULL, 'Bella Hughes Multi-Services', '2013-03-05', 19, 2, NULL, 'Construction, Property Management, Consulting', 'Zamboanga City, Philippines', 'bellahughes.services1053@business.com', '09174292019', NULL, NULL, NULL, 'PCAB-43160', 'C', '2028-03-05', 'BP-4169', 'Zamboanga City', '2027-03-05', 'TIN-502911', 'dti_cert_1053.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, 'Insufficient business documentation', 0, '2025-06-16 00:21:34', '2026-02-03 00:21:34'),
(36, 1054, NULL, NULL, NULL, 'Christian Washington Multi-Services', '2022-03-05', 4, 5, NULL, 'Construction, Property Management, Consulting', 'Zamboanga City, Philippines', 'christianwashington.services1054@business.com', '09176991016', NULL, NULL, NULL, 'PCAB-35444', 'C', '2028-03-05', 'BP-5329', 'Zamboanga City', '2027-03-05', 'TIN-316030', 'dti_cert_1054.jpg', 'approved', '2026-02-14 00:21:34', 1, NULL, NULL, NULL, NULL, 20, '2025-08-16 00:21:34', '2026-03-02 00:21:34'),
(37, 1055, NULL, NULL, NULL, 'Skylar Butler Multi-Services', '2019-03-05', 11, 9, NULL, 'Construction, Property Management, Consulting', 'Zamboanga City, Philippines', 'skylarbutler.services1055@business.com', '09176126530', NULL, NULL, NULL, 'PCAB-10104', 'Trade/E', '2028-03-05', 'BP-7957', 'Zamboanga City', '2027-03-05', 'TIN-160172', 'dti_cert_1055.jpg', 'deleted', NULL, 0, NULL, NULL, 'Business closure', NULL, 0, '2025-12-11 00:21:34', '2026-02-14 00:21:34'),
(38, 1056, NULL, NULL, NULL, 'Hunter Simmons Multi-Services', '2025-03-05', 10, 2, NULL, 'Construction, Property Management, Consulting', 'Zamboanga City, Philippines', 'huntersimmons.services1056@business.com', '09174890703', NULL, NULL, NULL, 'PCAB-83739', 'Trade/E', '2028-03-05', 'BP-4835', 'Zamboanga City', '2027-03-05', 'TIN-673090', 'dti_cert_1056.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, 'Insufficient business documentation', 0, '2026-01-20 00:21:35', '2026-02-03 00:21:35'),
(39, 1057, NULL, NULL, NULL, 'Paisley Foster Multi-Services', '2015-03-05', 14, 7, NULL, 'Construction, Property Management, Consulting', 'Zamboanga City, Philippines', 'paisleyfoster.services1057@business.com', '09174141711', NULL, NULL, NULL, 'PCAB-90408', 'B', '2028-03-05', 'BP-5720', 'Zamboanga City', '2027-03-05', 'TIN-944613', 'dti_cert_1057.jpg', 'approved', '2025-12-23 00:21:35', 1, NULL, NULL, NULL, NULL, 3, '2025-04-24 00:21:35', '2026-02-09 00:21:35'),
(40, 1058, NULL, NULL, NULL, 'Aaron Gonzales Multi-Services', '2006-03-05', 15, 7, NULL, 'Construction, Property Management, Consulting', 'Zamboanga City, Philippines', 'aarongonzales.services1058@business.com', '09171808450', NULL, NULL, NULL, 'PCAB-49076', 'D', '2028-03-05', 'BP-2324', 'Zamboanga City', '2027-03-05', 'TIN-853207', 'dti_cert_1058.jpg', 'pending', NULL, 0, NULL, NULL, NULL, NULL, 0, '2025-11-19 00:21:35', '2026-02-27 00:21:35'),
(41, 1059, NULL, NULL, NULL, 'Leah Bryant Multi-Services', '2017-03-05', 17, 7, NULL, 'Construction, Property Management, Consulting', 'Zamboanga City, Philippines', 'leahbryant.services1059@business.com', '09178420078', NULL, NULL, NULL, 'PCAB-60127', 'C', '2028-03-05', 'BP-2581', 'Zamboanga City', '2027-03-05', 'TIN-125852', 'dti_cert_1059.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, 'Insufficient business documentation', 0, '2026-01-19 00:21:35', '2026-02-09 00:21:35');

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
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_admin_conversation` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `type` enum('Milestone Update','Bid Status','Payment Reminder','Project Alert','Progress Update','Dispute Update','Team Update','Payment Status','Message') NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `delivery_method` enum('App','Email','Both') DEFAULT 'App',
  `priority` enum('critical','high','normal') NOT NULL DEFAULT 'normal',
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(10) UNSIGNED DEFAULT NULL,
  `dedup_key` varchar(100) DEFAULT NULL,
  `action_link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `selected_contractor_id` int(11) DEFAULT NULL,
  `is_highlighted` tinyint(1) NOT NULL DEFAULT 0,
  `highlighted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`project_id`, `relationship_id`, `project_title`, `project_description`, `project_location`, `budget_range_min`, `budget_range_max`, `lot_size`, `floor_area`, `property_type`, `type_id`, `if_others_ctype`, `to_finish`, `project_status`, `previous_status`, `stat_reason`, `remarks`, `selected_contractor_id`, `is_highlighted`, `highlighted_at`) VALUES
(1, 1, 'Farm Building Construction', 'Agricultural building construction for livestock and crop storage. Need contractors experienced in agricultural construction projects.', 'Manicahan, Zamboanga City, Zamboanga del Sur', 2590696.00, 7078003.00, 406, 85, 'Agricultural', 7, NULL, NULL, 'bidding_closed', NULL, NULL, NULL, NULL, 0, NULL),
(2, 2, 'Office Complex Development', 'Planning to develop a modern office complex suitable for business operations. Need contractors specializing in commercial construction.', 'Baluno, Zamboanga City, Zamboanga del Sur', 1964214.00, 4163299.00, 473, 115, 'Commercial', 1, NULL, NULL, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(3, 3, 'Manufacturing Facility', 'Building a manufacturing facility with specialized equipment area and office spaces. Looking for industrial construction specialists.', 'San Roque, Zamboanga City, Zamboanga del Sur', 2192528.00, 4849783.00, 256, 74, 'Industrial', 6, NULL, NULL, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(4, 4, 'Manufacturing Facility', 'Building a manufacturing facility with specialized equipment area and office spaces. Looking for industrial construction specialists.', 'Recodo, Zamboanga City, Zamboanga del Sur', 4238345.00, 5754539.00, 278, 57, 'Industrial', 3, NULL, NULL, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(5, 5, 'Warehouse Construction Chloe Cox', 'Industrial warehouse construction project with specific storage and operational requirements. Need contractors experienced in industrial construction.', 'San Roque, Zamboanga City, Zamboanga del Sur', 3458405.00, 6681216.00, 236, 137, 'Industrial', 8, NULL, NULL, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(6, 6, 'Lincoln Ramirez Commercial Building', 'Construction of a multi-purpose commercial building for retail and office spaces. Looking for experienced commercial contractors with proven track record.', 'Pasonanca, Zamboanga City, Zamboanga del Sur', 1959804.00, 6263826.00, 102, 70, 'Commercial', 6, NULL, NULL, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(7, 7, 'Warehouse Construction Zoe Brooks', 'Industrial warehouse construction project with specific storage and operational requirements. Need contractors experienced in industrial construction.', 'Arena Blanco, Zamboanga City, Zamboanga del Sur', 1665096.00, 3069106.00, 441, 267, 'Industrial', 8, NULL, NULL, 'open', NULL, NULL, NULL, NULL, 0, NULL);

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
(1, 1, 'building permit', 'project_files/building_permits/building_permit_1_sample.png', '2026-03-03 01:06:11'),
(2, 1, 'blueprint', 'project_files/blueprints/blueprint_1_sample.png', '2026-03-02 01:06:11'),
(3, 1, 'desired design', 'project_files/desired_designs/desired_design_1_sample.png', '2026-03-01 01:06:11'),
(4, 1, 'title', 'project_files/titles/title_1_sample.png', '2026-02-28 01:06:11'),
(5, 1, 'others', 'project_files/otherss/others_1_sample.png', '2026-03-05 01:06:11'),
(6, 2, 'building permit', 'project_files/building_permits/building_permit_2_sample.png', '2026-03-05 01:06:11'),
(7, 2, 'blueprint', 'project_files/blueprints/blueprint_2_sample.png', '2026-02-28 01:06:11'),
(8, 2, 'desired design', 'project_files/desired_designs/desired_design_2_sample.png', '2026-03-02 01:06:11'),
(9, 2, 'title', 'project_files/titles/title_2_sample.png', '2026-03-01 01:06:11'),
(10, 2, 'others', 'project_files/otherss/others_2_sample.png', '2026-02-28 01:06:11'),
(11, 3, 'building permit', 'project_files/building_permits/building_permit_3_sample.png', '2026-03-03 01:06:11'),
(12, 3, 'blueprint', 'project_files/blueprints/blueprint_3_sample.png', '2026-03-05 01:06:11'),
(13, 3, 'desired design', 'project_files/desired_designs/desired_design_3_sample.png', '2026-03-01 01:06:11'),
(14, 3, 'title', 'project_files/titles/title_3_sample.png', '2026-02-28 01:06:11'),
(15, 3, 'others', 'project_files/otherss/others_3_sample.png', '2026-03-05 01:06:11'),
(16, 4, 'building permit', 'project_files/building_permits/building_permit_4_sample.png', '2026-03-02 01:06:11'),
(17, 4, 'blueprint', 'project_files/blueprints/blueprint_4_sample.png', '2026-03-02 01:06:11'),
(18, 4, 'desired design', 'project_files/desired_designs/desired_design_4_sample.png', '2026-03-03 01:06:11'),
(19, 4, 'title', 'project_files/titles/title_4_sample.png', '2026-03-02 01:06:11'),
(20, 4, 'others', 'project_files/otherss/others_4_sample.png', '2026-03-03 01:06:11'),
(21, 5, 'building permit', 'project_files/building_permits/building_permit_5_sample.png', '2026-02-28 01:06:11'),
(22, 5, 'blueprint', 'project_files/blueprints/blueprint_5_sample.png', '2026-03-05 01:06:11'),
(23, 5, 'desired design', 'project_files/desired_designs/desired_design_5_sample.png', '2026-03-03 01:06:11'),
(24, 5, 'title', 'project_files/titles/title_5_sample.png', '2026-03-02 01:06:11'),
(25, 5, 'others', 'project_files/otherss/others_5_sample.png', '2026-02-28 01:06:11'),
(26, 6, 'building permit', 'project_files/building_permits/building_permit_6_sample.png', '2026-03-03 01:06:11'),
(27, 6, 'blueprint', 'project_files/blueprints/blueprint_6_sample.png', '2026-03-01 01:06:11'),
(28, 6, 'desired design', 'project_files/desired_designs/desired_design_6_sample.png', '2026-03-04 01:06:11'),
(29, 6, 'title', 'project_files/titles/title_6_sample.png', '2026-02-28 01:06:11'),
(30, 6, 'others', 'project_files/otherss/others_6_sample.png', '2026-02-28 01:06:11'),
(31, 7, 'building permit', 'project_files/building_permits/building_permit_7_sample.png', '2026-03-02 01:06:11'),
(32, 7, 'blueprint', 'project_files/blueprints/blueprint_7_sample.png', '2026-03-03 01:06:11'),
(33, 7, 'desired design', 'project_files/desired_designs/desired_design_7_sample.png', '2026-03-04 01:06:11'),
(34, 7, 'title', 'project_files/titles/title_7_sample.png', '2026-03-03 01:06:11'),
(35, 7, 'others', 'project_files/otherss/others_7_sample.png', '2026-03-05 01:06:11');

-- --------------------------------------------------------

--
-- Table structure for table `project_posts`
--

CREATE TABLE `project_posts` (
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `tagged_user_id` int(11) DEFAULT NULL,
  `linked_project_id` int(11) DEFAULT NULL,
  `location` varchar(500) DEFAULT NULL,
  `status` enum('open','closed','deleted') NOT NULL DEFAULT 'open',
  `is_highlighted` tinyint(1) NOT NULL DEFAULT 0,
  `highlighted_at` timestamp NULL DEFAULT NULL,
  `boost_tier` varchar(20) DEFAULT NULL,
  `boost_expiration` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_post_images`
--

CREATE TABLE `project_post_images` (
  `image_id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 2, NULL, 'approved', NULL, '2026-05-08', '2026-02-15 01:06:11', '2026-03-03 01:06:11'),
(2, 5, NULL, 'approved', NULL, '2026-04-27', '2026-02-14 01:06:11', '2026-03-03 01:06:11'),
(3, 8, NULL, 'approved', NULL, '2026-05-24', '2026-02-20 01:06:11', '2026-03-02 01:06:11'),
(4, 11, NULL, 'approved', NULL, '2026-04-11', '2026-02-17 01:06:11', '2026-03-02 01:06:11'),
(5, 14, NULL, 'approved', NULL, '2026-04-18', '2026-02-21 01:06:11', '2026-03-05 01:06:11'),
(6, 17, NULL, 'under_review', NULL, '2026-04-09', '2026-02-03 01:06:11', '2026-03-03 01:06:11'),
(7, 20, NULL, 'approved', NULL, '2026-04-16', '2026-02-12 01:06:11', '2026-03-05 01:06:11');

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
(1, 1020, 'Rivera', 'Benjamin', 'Aiden', '09993945418', NULL, 'Tetuan, Zamboanga City', 3, 'id_front_1020.jpg', 'id_back_1020.jpg', 'police_1020.jpg', '1978-03-05', 50, 9, NULL, 'rejected', 0, NULL, 'Invalid identification documents', NULL, NULL, '2026-01-22 00:21:27', '2025-08-28 00:21:27'),
(2, 1021, 'Gomez', 'Mae', 'Abigail', '09991063165', NULL, 'Tetuan, Zamboanga City', 1, 'id_front_1021.jpg', 'id_back_1021.jpg', 'police_1021.jpg', '1994-03-05', 60, 21, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-09-20 00:21:27', '2025-10-13 00:21:27'),
(3, 1022, 'Cooper', 'Luke', 'Sebastian', '09992322794', NULL, 'Tetuan, Zamboanga City', 3, 'id_front_1022.jpg', 'id_back_1022.jpg', 'police_1022.jpg', '1989-03-05', 61, 4, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '2026-02-24 00:21:27', '2025-05-11 00:21:27'),
(4, 1023, 'Morgan', 'Faith', 'Emily', '09998481524', NULL, 'Tetuan, Zamboanga City', 2, 'id_front_1023.jpg', 'id_back_1023.jpg', 'police_1023.jpg', '1997-03-05', 61, 18, NULL, 'rejected', 0, NULL, 'Invalid identification documents', NULL, NULL, '2025-11-26 00:21:28', '2025-12-28 00:21:28'),
(5, 1024, 'Peterson', 'Isaac', 'Julian', '09995473678', NULL, 'Tetuan, Zamboanga City', 6, 'id_front_1024.jpg', 'id_back_1024.jpg', 'police_1024.jpg', '1974-03-05', 47, 5, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-01 00:21:28', '2025-07-15 00:21:28'),
(6, 1025, 'Reed', 'Hope', 'Madison', '09992956904', NULL, 'Tetuan, Zamboanga City', 4, 'id_front_1025.jpg', 'id_back_1025.jpg', 'police_1025.jpg', '1966-03-05', 52, 12, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '2025-10-06 00:21:28', '2025-07-20 00:21:28'),
(7, 1026, 'Bailey', 'Anthony', 'Wyatt', '09994039385', NULL, 'Tetuan, Zamboanga City', 4, 'id_front_1026.jpg', 'id_back_1026.jpg', 'police_1026.jpg', '1990-03-05', 31, 2, NULL, 'rejected', 0, NULL, 'Invalid identification documents', NULL, NULL, '2026-02-23 00:21:28', '2025-05-13 00:21:28'),
(8, 1027, 'Bell', 'Claire', 'Elizabeth', '09991730178', NULL, 'Tetuan, Zamboanga City', 2, 'id_front_1027.jpg', 'id_back_1027.jpg', 'police_1027.jpg', '1997-03-05', 28, 5, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2026-01-17 00:21:28', '2025-05-06 00:21:28'),
(9, 1028, 'Gonzalez', 'John', 'Carter', '09999954665', NULL, 'Tetuan, Zamboanga City', 3, 'id_front_1028.jpg', 'id_back_1028.jpg', 'police_1028.jpg', '1961-03-05', 28, 19, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '2025-11-30 00:21:29', '2025-12-05 00:21:29'),
(10, 1029, 'Murphy', 'Quinn', 'Avery', '09992141148', NULL, 'Tetuan, Zamboanga City', 5, 'id_front_1029.jpg', 'id_back_1029.jpg', 'police_1029.jpg', '1982-03-05', 29, 24, NULL, 'rejected', 0, NULL, 'Invalid identification documents', NULL, NULL, '2025-12-20 00:21:29', '2026-02-23 00:21:29'),
(11, 1030, 'Kelly', 'Richard', 'Owen', '09997930468', NULL, 'Tetuan, Zamboanga City', 4, 'id_front_1030.jpg', 'id_back_1030.jpg', 'police_1030.jpg', '1979-03-05', 42, 9, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-09-27 00:21:29', '2026-02-11 00:21:29'),
(12, 1031, 'Howard', 'Nicole', 'Sofia', '09998491055', NULL, 'Tetuan, Zamboanga City', 1, 'id_front_1031.jpg', 'id_back_1031.jpg', 'police_1031.jpg', '1991-03-05', 32, 24, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '2025-09-17 00:21:29', '2025-11-10 00:21:29'),
(13, 1032, 'Richardson', 'Edward', 'Gabriel', '09997518007', NULL, 'Tetuan, Zamboanga City', 4, 'id_front_1032.jpg', 'id_back_1032.jpg', 'police_1032.jpg', '1981-03-05', 64, 7, NULL, 'rejected', 0, NULL, 'Invalid identification documents', NULL, NULL, '2025-10-24 00:21:29', '2025-09-10 00:21:29'),
(14, 1033, 'Cox', 'Isabelle', 'Chloe', '09997304249', NULL, 'Tetuan, Zamboanga City', 4, 'id_front_1033.jpg', 'id_back_1033.jpg', 'police_1033.jpg', '1964-03-05', 34, 2, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-07 00:21:30', '2025-10-03 00:21:30'),
(15, 1034, 'Ward', 'Charles', 'Dylan', '09995039200', NULL, 'Tetuan, Zamboanga City', 4, 'id_front_1034.jpg', 'id_back_1034.jpg', 'police_1034.jpg', '1991-03-05', 30, 12, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '2026-02-03 00:21:30', '2025-11-08 00:21:30'),
(16, 1035, 'Torres', 'Paige', 'Victoria', '09992556025', NULL, 'Tetuan, Zamboanga City', 1, 'id_front_1035.jpg', 'id_back_1035.jpg', 'police_1035.jpg', '1969-03-05', 30, 3, NULL, 'deleted', 0, NULL, NULL, 'Requested account closure', NULL, '2025-11-18 00:21:30', '2025-08-13 00:21:30'),
(17, 1036, 'Ramirez', 'Paul', 'Lincoln', '09999220491', NULL, 'Tetuan, Zamboanga City', 2, 'id_front_1036.jpg', 'id_back_1036.jpg', 'police_1036.jpg', '1998-03-05', 35, 25, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-12-19 00:21:30', '2025-07-26 00:21:30'),
(18, 1037, 'James', 'Olivia', 'Grace', '09994143454', NULL, 'Tetuan, Zamboanga City', 5, 'id_front_1037.jpg', 'id_back_1037.jpg', 'police_1037.jpg', '1986-03-05', 32, 2, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '2025-12-08 00:21:31', '2025-06-19 00:21:31'),
(19, 1038, 'Watson', 'William', 'Asher', '09996560711', NULL, 'Tetuan, Zamboanga City', 1, 'id_front_1038.jpg', 'id_back_1038.jpg', 'police_1038.jpg', '1995-03-05', 56, 16, NULL, 'rejected', 0, NULL, 'Invalid identification documents', NULL, NULL, '2025-11-04 00:21:31', '2025-11-18 00:21:31'),
(20, 1039, 'Brooks', 'Penelope', 'Zoe', '09991406032', NULL, 'Tetuan, Zamboanga City', 6, 'id_front_1039.jpg', 'id_back_1039.jpg', 'police_1039.jpg', '1987-03-05', 41, 26, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-11-02 00:21:31', '2025-04-04 00:21:31'),
(21, 1040, 'Sanders', 'Ryan', 'Christopher', '09993050389', NULL, 'Zamboanga City, Philippines', 2, 'id_front_1040.jpg', 'id_back_1040.jpg', 'police_1040.jpg', '1978-03-05', 33, 22, NULL, 'rejected', 0, NULL, 'Background check failed', NULL, NULL, '2025-09-15 00:21:31', '2025-08-10 00:21:31'),
(22, 1041, 'Price', 'Margaret', 'Stella', '09992662470', NULL, 'Zamboanga City, Philippines', 5, 'id_front_1041.jpg', 'id_back_1041.jpg', 'police_1041.jpg', '1978-03-05', 52, 8, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-11-04 00:21:31', '2025-06-20 00:21:31'),
(23, 1042, 'Bennett', 'Aaron', 'Josiah', '09999778038', NULL, 'Zamboanga City, Philippines', 2, 'id_front_1042.jpg', 'id_back_1042.jpg', 'police_1042.jpg', '1975-03-05', 55, 26, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '2025-12-28 00:21:32', '2025-08-31 00:21:32'),
(24, 1043, 'Wood', 'Eleanor', 'Hazel', '09994828537', NULL, 'Zamboanga City, Philippines', 1, 'id_front_1043.jpg', 'id_back_1043.jpg', 'police_1043.jpg', '1977-03-05', 30, 1, NULL, 'rejected', 0, NULL, 'Background check failed', NULL, NULL, '2025-09-08 00:21:32', '2025-08-24 00:21:32'),
(25, 1044, 'Barnes', 'Jonathan', 'Andrew', '09997131358', NULL, 'Zamboanga City, Philippines', 2, 'id_front_1044.jpg', 'id_back_1044.jpg', 'police_1044.jpg', '1973-03-05', 40, 21, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2026-01-20 00:21:32', '2025-08-31 00:21:32'),
(26, 1045, 'Ross', 'Violet', 'Aurora', '09994187471', NULL, 'Zamboanga City, Philippines', 5, 'id_front_1045.jpg', 'id_back_1045.jpg', 'police_1045.jpg', '1999-03-05', 55, 4, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '2025-11-04 00:21:32', '2026-02-20 00:21:32'),
(27, 1046, 'Henderson', 'Christian', 'Thomas', '09991110782', NULL, 'Zamboanga City, Philippines', 2, 'id_front_1046.jpg', 'id_back_1046.jpg', 'police_1046.jpg', '1971-03-05', 31, 2, NULL, 'rejected', 0, NULL, 'Background check failed', NULL, NULL, '2025-12-08 00:21:32', '2025-06-14 00:21:32'),
(28, 1047, 'Coleman', 'Caroline', 'Lucy', '09992983790', NULL, 'Zamboanga City, Philippines', 3, 'id_front_1047.jpg', 'id_back_1047.jpg', 'police_1047.jpg', '1993-03-05', 59, 10, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2026-01-19 00:21:33', '2025-03-23 00:21:33'),
(29, 1048, 'Jenkins', 'Nathaniel', 'Caleb', '09992054942', NULL, 'Zamboanga City, Philippines', 5, 'id_front_1048.jpg', 'id_back_1048.jpg', 'police_1048.jpg', '1982-03-05', 44, 12, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '2026-03-01 00:21:33', '2025-08-31 00:21:33'),
(30, 1049, 'Perry', 'Audrey', 'Savannah', '09996305993', NULL, 'Zamboanga City, Philippines', 5, 'id_front_1049.jpg', 'id_back_1049.jpg', 'police_1049.jpg', '1981-03-05', 42, 3, NULL, 'rejected', 0, NULL, 'Background check failed', NULL, NULL, '2025-12-06 00:21:33', '2025-12-23 00:21:33'),
(31, 1050, 'Powell', 'Patrick', 'Ryan', '09996455929', NULL, 'Zamboanga City, Philippines', 4, 'id_front_1050.jpg', 'id_back_1050.jpg', 'police_1050.jpg', '1974-03-05', 43, 8, NULL, 'deleted', 1, NULL, NULL, 'User account merged', NULL, '2025-10-24 00:21:33', '2025-09-24 00:21:33'),
(32, 1051, 'Long', 'Harper', 'Brooklyn', '09997575648', NULL, 'Zamboanga City, Philippines', 1, 'id_front_1051.jpg', 'id_back_1051.jpg', 'police_1051.jpg', '1974-03-05', 26, 3, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '2025-10-28 00:21:33', '2025-04-30 00:21:33'),
(33, 1052, 'Patterson', 'Bradley', 'Nathan', '09995302617', NULL, 'Zamboanga City, Philippines', 4, 'id_front_1052.jpg', 'id_back_1052.jpg', 'police_1052.jpg', '1974-03-05', 38, 1, NULL, 'rejected', 0, NULL, 'Background check failed', NULL, NULL, '2026-02-08 00:21:34', '2025-03-11 00:21:34'),
(34, 1053, 'Hughes', 'Savannah', 'Bella', '09995047872', NULL, 'Zamboanga City, Philippines', 5, 'id_front_1053.jpg', 'id_back_1053.jpg', 'police_1053.jpg', '1985-03-05', 59, 1, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2026-02-13 00:21:34', '2025-07-19 00:21:34'),
(35, 1054, 'Washington', 'Tyler', 'Christian', '09997789859', NULL, 'Zamboanga City, Philippines', 5, 'id_front_1054.jpg', 'id_back_1054.jpg', 'police_1054.jpg', '1971-03-05', 37, 2, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '2026-02-02 00:21:34', '2025-12-25 00:21:34'),
(36, 1055, 'Butler', 'Genesis', 'Skylar', '09991223074', NULL, 'Zamboanga City, Philippines', 4, 'id_front_1055.jpg', 'id_back_1055.jpg', 'police_1055.jpg', '1975-03-05', 58, 15, NULL, 'rejected', 0, NULL, 'Background check failed', NULL, NULL, '2026-01-30 00:21:34', '2025-09-09 00:21:34'),
(37, 1056, 'Simmons', 'Aaron', 'Hunter', '09993293427', NULL, 'Zamboanga City, Philippines', 5, 'id_front_1056.jpg', 'id_back_1056.jpg', 'police_1056.jpg', '1989-03-05', 42, 6, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2025-11-07 00:21:35', '2025-04-16 00:21:35'),
(38, 1057, 'Foster', 'Autumn', 'Paisley', '09991576704', NULL, 'Zamboanga City, Philippines', 1, 'id_front_1057.jpg', 'id_back_1057.jpg', 'police_1057.jpg', '2000-03-05', 55, 16, NULL, 'pending', 0, NULL, NULL, NULL, NULL, '2025-09-23 00:21:35', '2025-08-23 00:21:35'),
(39, 1058, 'Gonzales', 'Jacob', 'Aaron', '09993554335', NULL, 'Zamboanga City, Philippines', 3, 'id_front_1058.jpg', 'id_back_1058.jpg', 'police_1058.jpg', '1990-03-05', 56, 10, NULL, 'rejected', 0, NULL, 'Background check failed', NULL, NULL, '2025-09-14 00:21:35', '2026-02-24 00:21:35'),
(40, 1059, 'Bryant', 'Brooklyn', 'Leah', '09997562589', NULL, 'Zamboanga City, Philippines', 6, 'id_front_1059.jpg', 'id_back_1059.jpg', 'police_1059.jpg', '1981-03-05', 28, 2, NULL, 'approved', 1, NULL, NULL, NULL, NULL, '2026-02-15 00:21:35', '2025-09-18 00:21:35');

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
(151, NULL, NULL, 'alexandermitchell1', 'alexander.mitchell@legatura.com', '$2y$12$xIJULwIwDqJAyX/Kljw/7ukdx1m2XSXRC.Ens0Tr/tSm36GzrwbnK', NULL, 'contractor', NULL, '2025-07-11 00:17:07', '2026-03-01 00:17:07'),
(1000, NULL, NULL, 'alexandermitchell_contractor_1000', 'alexander.mitchell.contractor1000@legatura.com', '$2y$12$26aQYp4et.0Q1qWiMfhTZOZD/0PLNzENSubeVdMIWbimVZHrwIqEW', NULL, 'contractor', NULL, '2025-07-25 00:21:23', '2026-02-06 00:21:23'),
(1001, NULL, NULL, 'sophiadavis_contractor_1001', 'sophia.davis.contractor1001@legatura.com', '$2y$12$E358AJOarS7Cj.jwLaaCpu4SYlScqdAGQLyBIbHFU/GYucH9C.yLK', NULL, 'contractor', NULL, '2026-02-05 00:21:23', '2026-02-14 00:21:23'),
(1002, NULL, NULL, 'liamclark_contractor_1002', 'liam.clark.contractor1002@legatura.com', '$2y$12$7M5bW/Wiy.xMfa0zhNkV6emAnFyfe2/S9D./N6Cwk8CcLRueL43i6', NULL, 'contractor', NULL, '2025-10-02 00:21:23', '2026-02-11 00:21:23'),
(1003, NULL, NULL, 'oliviamartinez_contractor_1003', 'olivia.martinez.contractor1003@legatura.com', '$2y$12$h1VN74rQOGva8d8Es5qpXuQ.nlGjF5GrQmkJr2tt04Q1YrPJbWCJu', NULL, 'contractor', NULL, '2026-01-07 00:21:23', '2026-02-03 00:21:23'),
(1004, NULL, NULL, 'noahrodriguez_contractor_1004', 'noah.rodriguez.contractor1004@legatura.com', '$2y$12$RUmDtTiz3nEZgTEbwpcvSuj09S8ios8W1RP.xD/LlGl28d7HU2Nly', NULL, 'contractor', NULL, '2025-10-20 00:21:24', '2026-02-10 00:21:24'),
(1005, NULL, NULL, 'emmaturner_contractor_1005', 'emma.turner.contractor1005@legatura.com', '$2y$12$.KDrSpKVMC0CD4nlsIC6AOdEOSzwH1hsXqcE8eyNSOgU48AYoNZWa', NULL, 'contractor', NULL, '2025-08-04 00:21:24', '2026-02-06 00:21:24'),
(1006, NULL, NULL, 'elijahwhite_contractor_1006', 'elijah.white.contractor1006@legatura.com', '$2y$12$jN39ABAbByNj1146Iy6i9usUFDgbKff5NKpjkU6RsX0hvNeQzhMs2', NULL, 'contractor', NULL, '2025-10-01 00:21:24', '2026-02-14 00:21:24'),
(1007, NULL, NULL, 'avathompson_contractor_1007', 'ava.thompson.contractor1007@legatura.com', '$2y$12$KbhgtDEcLpzRv8btALlbfuCg3W9lw3qvQkiQO0HXZCj82qWlsWGLu', NULL, 'contractor', NULL, '2025-09-25 00:21:24', '2026-02-23 00:21:24'),
(1008, NULL, NULL, 'mateosanchez_contractor_1008', 'mateo.sanchez.contractor1008@legatura.com', '$2y$12$itJ55SZLcHNcVOoTEsJD1uZ5G3BtZ6HWQW3RqC0H/VmDetpYMmkUK', NULL, 'contractor', NULL, '2026-01-17 00:21:24', '2026-02-10 00:21:24'),
(1009, NULL, NULL, 'isabellawalker_contractor_1009', 'isabella.walker.contractor1009@legatura.com', '$2y$12$5Ne9k7qqWD/GiDJ5gTEQI.OIkH.cKlx7NyeFxT8oewsJ6fo/Q7JyG', NULL, 'contractor', NULL, '2025-08-01 00:21:25', '2026-02-17 00:21:25'),
(1010, NULL, NULL, 'lucaswright_contractor_1010', 'lucas.wright.contractor1010@legatura.com', '$2y$12$7usYuVIfXXKsGnMHb6JzIeIzCLhsMXYOwpsox9VVaHH1neEOwkWDC', NULL, 'contractor', NULL, '2025-04-05 00:21:25', '2026-02-24 00:21:25'),
(1011, NULL, NULL, 'miascott_contractor_1011', 'mia.scott.contractor1011@legatura.com', '$2y$12$jEl7KB2o4wpThUHPF8zBVuKjuw.GhJTZLIjgGjkOYE7ROctYyBU6.', NULL, 'contractor', NULL, '2025-10-13 00:21:25', '2026-02-08 00:21:25'),
(1012, NULL, NULL, 'ethangreen_contractor_1012', 'ethan.green.contractor1012@legatura.com', '$2y$12$xnV7sS9axdEoDbaOLW36K./3BnLJLIshua4MHJFb7iySLKWIAQYDK', NULL, 'contractor', NULL, '2025-05-23 00:21:25', '2026-02-09 00:21:25'),
(1013, NULL, NULL, 'charlotteadams_contractor_1013', 'charlotte.adams.contractor1013@legatura.com', '$2y$12$jyx3ptJLJ4lHu9iFJ5eZSOuIrvUlkU9tC7fZ3xE219m8tKstRDJO.', NULL, 'contractor', NULL, '2025-04-26 00:21:25', '2026-02-05 00:21:25'),
(1014, NULL, NULL, 'masonbaker_contractor_1014', 'mason.baker.contractor1014@legatura.com', '$2y$12$0MZm96//iVhbts6sNSxtEeG2oaGXa62lr8qM3G/U9M6k/Lw6e3clS', NULL, 'contractor', NULL, '2025-06-12 00:21:26', '2026-02-07 00:21:26'),
(1015, NULL, NULL, 'amelianelson_contractor_1015', 'amelia.nelson.contractor1015@legatura.com', '$2y$12$JAufuldcLAPNmNQrC.NT0ed1aTIxvrNQwL8vk3dV6lfFHnczWEqRG', NULL, 'contractor', NULL, '2025-11-03 00:21:26', '2026-02-21 00:21:26'),
(1016, NULL, NULL, 'logancarter_contractor_1016', 'logan.carter.contractor1016@legatura.com', '$2y$12$9fGhB7tBU5qB05PLpiC/Q.MWe/VPrCNDVxCJoqu9dL7wPovr/RCtG', NULL, 'contractor', NULL, '2025-05-15 00:21:26', '2026-02-03 00:21:26'),
(1017, NULL, NULL, 'harperperez_contractor_1017', 'harper.perez.contractor1017@legatura.com', '$2y$12$PN4CyBCIQMs9e1ENAs3LSOmVHFQrsNoILlsJmhKphhsNxioFcDyDC', NULL, 'contractor', NULL, '2025-10-03 00:21:26', '2026-02-06 00:21:26'),
(1018, NULL, NULL, 'jacksonroberts_contractor_1018', 'jackson.roberts.contractor1018@legatura.com', '$2y$12$RkDrD1F4ApfPHA7qYdX4ZuP/IsxbgNii5hE8ISUmuFDR1K42UMqAK', NULL, 'contractor', NULL, '2025-12-05 00:21:27', '2026-02-28 00:21:27'),
(1019, NULL, NULL, 'evelynflores_contractor_1019', 'evelyn.flores.contractor1019@legatura.com', '$2y$12$WKscCiHstZEwE8zfJzLoweXHEXnyeKVwy0zE/UYrKHFcWxba452Cm', NULL, 'contractor', NULL, '2025-11-17 00:21:27', '2026-02-25 00:21:27'),
(1020, NULL, NULL, 'aidenrivera_owner_1020', 'aiden.rivera.owner1020@legatura.com', '$2y$12$y6EqFTUXrs1QJcu2TohIHOfTwfOlaZK74lxkp9AgGLA.4ybhTCkFu', NULL, 'property_owner', NULL, '2025-10-11 00:21:27', '2026-02-07 00:21:27'),
(1021, NULL, NULL, 'abigailgomez_owner_1021', 'abigail.gomez.owner1021@legatura.com', '$2y$12$s7ZLCmFqeFsL3up1YMMgSe.ZuDrmqUqOUK2D4uLD7/VLQQJ2fWGJS', NULL, 'property_owner', NULL, '2026-01-19 00:21:27', '2026-02-17 00:21:27'),
(1022, NULL, NULL, 'sebastiancooper_owner_1022', 'sebastian.cooper.owner1022@legatura.com', '$2y$12$BE6muiUldLMgbuS5v.mIAORXDZ3/NfJw6d7liF9Bhg.4pXjlBJApS', NULL, 'property_owner', NULL, '2025-12-29 00:21:27', '2026-02-15 00:21:27'),
(1023, NULL, NULL, 'emilymorgan_owner_1023', 'emily.morgan.owner1023@legatura.com', '$2y$12$fCy5NhYx.J5AMP0HXCP14u4rTxdc.E.9msamJ6OfO/NlTQOI2IxmW', NULL, 'property_owner', NULL, '2025-10-25 00:21:28', '2026-02-08 00:21:28'),
(1024, NULL, NULL, 'julianpeterson_owner_1024', 'julian.peterson.owner1024@legatura.com', '$2y$12$QOX7cJTpRU/GTdtlIxoV8OD81OM81XxjpsVNydFdllY0ODWLYYWPi', NULL, 'property_owner', NULL, '2025-07-25 00:21:28', '2026-02-07 00:21:28'),
(1025, NULL, NULL, 'madisonreed_owner_1025', 'madison.reed.owner1025@legatura.com', '$2y$12$83OzjFPtBdEodA0F6hR45uUmZPyj.A6ESBR5fCFDLsUFxFjZavaAG', NULL, 'property_owner', NULL, '2026-01-02 00:21:28', '2026-02-28 00:21:28'),
(1026, NULL, NULL, 'wyattbailey_owner_1026', 'wyatt.bailey.owner1026@legatura.com', '$2y$12$DfborytV7F2zB3cW6.LGYejI1/hvaTBA.MOMkoH4qbd/6b1YgWtrm', NULL, 'property_owner', NULL, '2025-03-16 00:21:28', '2026-03-01 00:21:28'),
(1027, NULL, NULL, 'elizabethbell_owner_1027', 'elizabeth.bell.owner1027@legatura.com', '$2y$12$DTvEf0Fv6H4uVlOUJxVwteUp2/akWz3o.3tyutCVjo3wu993y7gSO', NULL, 'property_owner', NULL, '2025-12-15 00:21:28', '2026-03-03 00:21:28'),
(1028, NULL, NULL, 'cartergonzalez_owner_1028', 'carter.gonzalez.owner1028@legatura.com', '$2y$12$N7AygSeW3S/HmLsJcN2yTOyC95Noy/M9Z/1s0SbLOcxyUuxHSmDtC', NULL, 'property_owner', NULL, '2026-02-06 00:21:29', '2026-02-26 00:21:29'),
(1029, NULL, NULL, 'averymurphy_owner_1029', 'avery.murphy.owner1029@legatura.com', '$2y$12$J8l4J7r6C.ytEkdL.Lu.veHn16Bxw5UfLblG3TRxw5YfshdjTU4EO', NULL, 'property_owner', NULL, '2025-05-28 00:21:29', '2026-02-18 00:21:29'),
(1030, NULL, NULL, 'owenkelly_owner_1030', 'owen.kelly.owner1030@legatura.com', '$2y$12$b8ETAUq.FhwbcqAENZ2nLOTSNqTVtLK0JmPsAiR3FWQuQZDS8Co0e', NULL, 'property_owner', NULL, '2025-04-19 00:21:29', '2026-02-05 00:21:29'),
(1031, NULL, NULL, 'sofiahoward_owner_1031', 'sofia.howard.owner1031@legatura.com', '$2y$12$yUt29h5k1dl92xWSTOmtxur1UkxjCl0abihhILVxOPlHipgbqXCFe', NULL, 'property_owner', NULL, '2025-08-22 00:21:29', '2026-02-18 00:21:29'),
(1032, NULL, NULL, 'gabrielrichardson_owner_1032', 'gabriel.richardson.owner1032@legatura.com', '$2y$12$YWTEE5tFoR9bCpi/lbaz5e1FBQAU5YO.rk4OBChJHwrfQ7Z4eMBYq', NULL, 'property_owner', NULL, '2025-08-14 00:21:29', '2026-02-08 00:21:29'),
(1033, NULL, NULL, 'chloecox_owner_1033', 'chloe.cox.owner1033@legatura.com', '$2y$12$BowOo0nP61oEdyI7U/W0SeMzJXW3YANbs/l/nBH5xQuDuwm.6tSoy', NULL, 'property_owner', NULL, '2025-11-27 00:21:30', '2026-02-21 00:21:30'),
(1034, NULL, NULL, 'dylanward_owner_1034', 'dylan.ward.owner1034@legatura.com', '$2y$12$pREY7WJnzitVq7IiHpi1jOhEQCATiqn5ox44FU8GH6bAEFVbo7lei', NULL, 'property_owner', NULL, '2025-08-23 00:21:30', '2026-02-22 00:21:30'),
(1035, NULL, NULL, 'victoriatorres_owner_1035', 'victoria.torres.owner1035@legatura.com', '$2y$12$HEXhNTVBniIRyEyViK5BIeX0Wy0frgsDxFbEEpTxexjUvgByz8HS2', NULL, 'property_owner', NULL, '2026-02-19 00:21:30', '2026-02-21 00:21:30'),
(1036, NULL, NULL, 'lincolnramirez_owner_1036', 'lincoln.ramirez.owner1036@legatura.com', '$2y$12$oiTqaR2Nh.yC15tRVvJcReD.ieWm.Vhu9iRmfhCX6l/8LBs/HiGHe', NULL, 'property_owner', NULL, '2025-07-07 00:21:30', '2026-02-09 00:21:30'),
(1037, NULL, NULL, 'gracejames_owner_1037', 'grace.james.owner1037@legatura.com', '$2y$12$ARutR8YbW/f79HTt1RtHge.8L9dhpEEPi8XOFN9Bk7pnZgCr3GXGC', NULL, 'property_owner', NULL, '2025-05-28 00:21:31', '2026-02-07 00:21:31'),
(1038, NULL, NULL, 'asherwatson_owner_1038', 'asher.watson.owner1038@legatura.com', '$2y$12$CCWeI5ekCsEIn.LZA/N47.X1jIOrlwY3MKWOyCdkkvxFlgovGx6BO', NULL, 'property_owner', NULL, '2025-09-29 00:21:31', '2026-02-18 00:21:31'),
(1039, NULL, NULL, 'zoebrooks_owner_1039', 'zoe.brooks.owner1039@legatura.com', '$2y$12$n2rB0X5e76Mw9z0S6qhrxeQ2fpRoYLp.Ng0e9XjyVwmHwcv7AoJG6', NULL, 'property_owner', NULL, '2025-09-24 00:21:31', '2026-02-06 00:21:31'),
(1040, NULL, NULL, 'christophersanders_both_1040', 'christopher.sanders.both1040@legatura.com', '$2y$12$RPzwgWoL62Uuv9/Sqh2dkOFuvT5h0g7nfKbclxLsgKLSZxD0bXG1a', NULL, 'both', 'contractor', '2025-08-18 00:21:31', '2026-02-07 00:21:31'),
(1041, NULL, NULL, 'stellaprice_both_1041', 'stella.price.both1041@legatura.com', '$2y$12$0we7XP60pcNVu0CsEEJaduDuN1VOaC4DOMYGNSGiE8ci0Vd99DszS', NULL, 'both', 'owner', '2026-01-04 00:21:31', '2026-02-06 00:21:31'),
(1042, NULL, NULL, 'josiahbennett_both_1042', 'josiah.bennett.both1042@legatura.com', '$2y$12$W7QJD4DmKbN.p360R23bh.eBX6uvEN5VkesxUO4NcrrC8EjGqpO72', NULL, 'both', 'contractor', '2026-01-21 00:21:32', '2026-03-03 00:21:32'),
(1043, NULL, NULL, 'hazelwood_both_1043', 'hazel.wood.both1043@legatura.com', '$2y$12$fMk9REzdcF1RnUcicvB1fOwh/7.k.u4bfjT4pm62g4RjyHWMQzU5G', NULL, 'both', 'owner', '2025-09-02 00:21:32', '2026-02-25 00:21:32'),
(1044, NULL, NULL, 'andrewbarnes_both_1044', 'andrew.barnes.both1044@legatura.com', '$2y$12$ocFIWpSrr6CctQDrRNqN1.MhGj.ONWibo3I9QpuU/CE6uFOFXV.T6', NULL, 'both', 'owner', '2025-06-11 00:21:32', '2026-03-03 00:21:32'),
(1045, NULL, NULL, 'auroraross_both_1045', 'aurora.ross.both1045@legatura.com', '$2y$12$WC4Vl6BaOAwV5JVtRAOSPO4f1x3QxJlVr.FGCBtZVXr84aoKsnASi', NULL, 'both', 'contractor', '2025-08-21 00:21:32', '2026-02-11 00:21:32'),
(1046, NULL, NULL, 'thomashenderson_both_1046', 'thomas.henderson.both1046@legatura.com', '$2y$12$PcAlz6lvOvf.appd7GVDT.M5AqHOisRYRMEvUatO060EZT/VNbqma', NULL, 'both', 'owner', '2026-01-02 00:21:32', '2026-02-19 00:21:32'),
(1047, NULL, NULL, 'lucycoleman_both_1047', 'lucy.coleman.both1047@legatura.com', '$2y$12$Z22wz.Wj4.ZTOneDUA6SRuYBsuJSKO2FUXNWpC52zgxaAsklPDgQ.', NULL, 'both', 'contractor', '2025-11-30 00:21:33', '2026-02-12 00:21:33'),
(1048, NULL, NULL, 'calebjenkins_both_1048', 'caleb.jenkins.both1048@legatura.com', '$2y$12$nlnz4M.m8wGpkGwrORnv/OeqZD7ZcrbpJwDCYF6H9qJOCXeUyMbrK', NULL, 'both', 'owner', '2025-07-15 00:21:33', '2026-02-14 00:21:33'),
(1049, NULL, NULL, 'savannahperry_both_1049', 'savannah.perry.both1049@legatura.com', '$2y$12$7P14iTn2x4y3Q2b2/7aZkencTovh2e23hWtDT09NGCWKWLu9zuR.S', NULL, 'both', 'owner', '2025-03-15 00:21:33', '2026-02-17 00:21:33'),
(1050, NULL, NULL, 'ryanpowell_both_1050', 'ryan.powell.both1050@legatura.com', '$2y$12$/GqVsmG2mSPgCzrTz2yT5e4N8NdMv4rqseqC/OEcDWfPILi.jGLA6', NULL, 'both', 'contractor', '2025-03-07 00:21:33', '2026-02-10 00:21:33'),
(1051, NULL, NULL, 'brooklynlong_both_1051', 'brooklyn.long.both1051@legatura.com', '$2y$12$k2cGA38VbdcjVfB4My9ifeOr6gaUkIS0NeDbrryZfDw5fqai44yly', NULL, 'both', 'contractor', '2026-02-26 00:21:33', '2026-02-25 00:21:33'),
(1052, NULL, NULL, 'nathanpatterson_both_1052', 'nathan.patterson.both1052@legatura.com', '$2y$12$7XvWyBmqB.qSP4Ilo6HXj.co5RPqM3RktllDmQab.OtSZUaON2ghK', NULL, 'both', 'owner', '2025-11-10 00:21:34', '2026-02-14 00:21:34'),
(1053, NULL, NULL, 'bellahughes_both_1053', 'bella.hughes.both1053@legatura.com', '$2y$12$Yn9fzhe41RI3C2OwZ9UyDuQhW3MITwbR0/IP9Ve.SyMFdsNBuZ1f2', NULL, 'both', 'contractor', '2025-12-20 00:21:34', '2026-03-01 00:21:34'),
(1054, NULL, NULL, 'christianwashington_both_1054', 'christian.washington.both1054@legatura.com', '$2y$12$HjEQH2gfgRd8Q.Fi5B9zJefEnTHFQgz6iuGRIIqnvwi4/lWpoJA3G', NULL, 'both', 'owner', '2025-07-21 00:21:34', '2026-02-09 00:21:34'),
(1055, NULL, NULL, 'skylarbutler_both_1055', 'skylar.butler.both1055@legatura.com', '$2y$12$Yz0i0SwKECBC8BT/I4MiBeavF/nLL4sYNykdmqoDwexTGytJlThcy', NULL, 'both', 'owner', '2025-10-08 00:21:34', '2026-02-10 00:21:34'),
(1056, NULL, NULL, 'huntersimmons_both_1056', 'hunter.simmons.both1056@legatura.com', '$2y$12$xzbittpCFsQJHMN4V2zoyujLJsoYGBXacH87wL5UVcQTkd/zEcPQi', NULL, 'both', 'contractor', '2025-05-25 00:21:35', '2026-02-07 00:21:35'),
(1057, NULL, NULL, 'paisleyfoster_both_1057', 'paisley.foster.both1057@legatura.com', '$2y$12$11xKUikvLgI4lz4gVxWxSOYlmQZciKULIfi.z19uWlOoCWf.6Luo6', NULL, 'both', 'owner', '2025-04-25 00:21:35', '2026-02-03 00:21:35'),
(1058, NULL, NULL, 'aarongonzales_both_1058', 'aaron.gonzales.both1058@legatura.com', '$2y$12$h.KvS54Byqnzxyo03LX5JeHqSA6hkEi5m7ep9OgF/nJHAsY9Q0peq', NULL, 'both', 'owner', '2025-07-01 00:21:35', '2026-02-07 00:21:35'),
(1059, NULL, NULL, 'leahbryant_both_1059', 'leah.bryant.both1059@legatura.com', '$2y$12$TAaOoTdPHJ3qhb66GSStuOcrospEbj/pzACcMS6KX4B0URsllZp1e', NULL, 'both', 'owner', '2025-12-18 00:21:35', '2026-03-02 00:21:35');

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
-- Indexes for table `construction_types`
--
ALTER TABLE `construction_types`
  ADD PRIMARY KEY (`type_id`);

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
-- Indexes for table `project_posts`
--
ALTER TABLE `project_posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `pp_user_id_idx` (`user_id`),
  ADD KEY `pp_status_idx` (`status`),
  ADD KEY `pp_created_at_idx` (`created_at`),
  ADD KEY `pp_highlight_idx` (`is_highlighted`,`highlighted_at`),
  ADD KEY `pp_linked_project_idx` (`linked_project_id`),
  ADD KEY `pp_tagged_user_idx` (`tagged_user_id`);

--
-- Indexes for table `project_post_images`
--
ALTER TABLE `project_post_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `ppi_post_id_idx` (`post_id`);

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
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `valid_id_id` (`valid_id_id`),
  ADD KEY `occupation_id` (`occupation_id`);

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
-- AUTO_INCREMENT for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_prediction_logs`
--
ALTER TABLE `ai_prediction_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bids`
--
ALTER TABLE `bids`
  MODIFY `bid_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bid_files`
--
ALTER TABLE `bid_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `construction_types`
--
ALTER TABLE `construction_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `contractors`
--
ALTER TABLE `contractors`
  MODIFY `contractor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `contractor_types`
--
ALTER TABLE `contractor_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `contractor_users`
--
ALTER TABLE `contractor_users`
  MODIFY `contractor_user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contract_terminations`
--
ALTER TABLE `contract_terminations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `conversation_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `disputes`
--
ALTER TABLE `disputes`
  MODIFY `dispute_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dispute_files`
--
ALTER TABLE `dispute_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `item_files`
--
ALTER TABLE `item_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message_attachments`
--
ALTER TABLE `message_attachments`
  MODIFY `attachment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `milestones`
--
ALTER TABLE `milestones`
  MODIFY `milestone_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `milestone_date_histories`
--
ALTER TABLE `milestone_date_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `milestone_items`
--
ALTER TABLE `milestone_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `milestone_item_updates`
--
ALTER TABLE `milestone_item_updates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `milestone_payments`
--
ALTER TABLE `milestone_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `platform_payments`
--
ALTER TABLE `platform_payments`
  MODIFY `platform_payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `progress`
--
ALTER TABLE `progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `progress_files`
--
ALTER TABLE `progress_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `project_files`
--
ALTER TABLE `project_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `project_posts`
--
ALTER TABLE `project_posts`
  MODIFY `post_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_post_images`
--
ALTER TABLE `project_post_images`
  MODIFY `image_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_relationships`
--
ALTER TABLE `project_relationships`
  MODIFY `rel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `project_updates`
--
ALTER TABLE `project_updates`
  MODIFY `extension_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_owners`
--
ALTER TABLE `property_owners`
  MODIFY `owner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `termination_proof`
--
ALTER TABLE `termination_proof`
  MODIFY `proof_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1060;

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
-- Constraints for table `project_posts`
--
ALTER TABLE `project_posts`
  ADD CONSTRAINT `project_posts_linked_project_id_foreign` FOREIGN KEY (`linked_project_id`) REFERENCES `projects` (`project_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_posts_tagged_user_id_foreign` FOREIGN KEY (`tagged_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `project_post_images`
--
ALTER TABLE `project_post_images`
  ADD CONSTRAINT `project_post_images_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `project_posts` (`post_id`) ON DELETE CASCADE;

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
