-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 16, 2026 at 08:51 PM
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
(1, 'ADMIN-1', 'subscription_plan_updated', '{\"plan_id\":\"1\",\"name\":\"Gold Tier Subscription\"}', '127.0.0.1', '2026-03-12 22:33:43'),
(2, 'ADMIN-1', 'subscription_plan_updated', '{\"plan_id\":\"1\",\"name\":\"Gold Tier Subscription\"}', '127.0.0.1', '2026-03-12 22:33:51'),
(3, 'ADMIN-1', 'admin_login', NULL, '127.0.0.1', '2026-03-13 04:19:56'),
(4, 'ADMIN-1', 'admin_login', NULL, '127.0.0.1', '2026-03-13 21:41:10'),
(5, 'ADMIN-1', 'admin_direct_hide_project', '{\"project_id\":2001,\"reason\":\"pwewow\"}', '127.0.0.1', '2026-03-13 22:11:57'),
(6, 'ADMIN-1', 'admin_direct_unhide_project', '{\"project_id\":2001,\"reason\":\"Restored by admin after moderation review\"}', '127.0.0.1', '2026-03-13 22:12:02'),
(7, 'ADMIN-1', 'bid_accepted', '{\"bid_id\":\"28\"}', '127.0.0.1', '2026-03-13 23:31:17'),
(8, 'ADMIN-1', 'bid_accepted', '{\"bid_id\":\"28\"}', '127.0.0.1', '2026-03-13 23:31:21'),
(9, 'ADMIN-1', 'bid_accepted', '{\"bid_id\":\"28\"}', '127.0.0.1', '2026-03-13 23:31:25'),
(10, 'ADMIN-1', 'bid_accepted', '{\"bid_id\":\"34\"}', '127.0.0.1', '2026-03-13 23:42:50'),
(11, 'ADMIN-1', 'bid_accepted', '{\"bid_id\":\"30\"}', '127.0.0.1', '2026-03-13 23:43:55'),
(12, 'ADMIN-1', 'bidder_changed', '{\"project_id\":\"2007\",\"new_bid_id\":29}', '127.0.0.1', '2026-03-14 00:07:15'),
(13, 'ADMIN-1', 'bidder_changed', '{\"project_id\":\"2007\",\"new_bid_id\":29}', '127.0.0.1', '2026-03-14 00:07:17'),
(14, 'ADMIN-1', 'bidder_changed', '{\"project_id\":\"2007\",\"new_bid_id\":29}', '127.0.0.1', '2026-03-14 00:07:32'),
(15, 'ADMIN-1', 'bidder_changed', '{\"project_id\":\"2007\",\"new_bid_id\":30}', '127.0.0.1', '2026-03-14 00:08:58'),
(16, 'ADMIN-1', 'bidder_changed', '{\"project_id\":\"2007\",\"new_bid_id\":29}', '127.0.0.1', '2026-03-14 00:11:01'),
(17, 'ADMIN-1', 'bidder_changed', '{\"project_id\":\"2007\",\"new_bid_id\":28}', '127.0.0.1', '2026-03-14 00:11:09'),
(18, 'ADMIN-1', 'bid_accepted', '{\"bid_id\":\"38\"}', '127.0.0.1', '2026-03-14 01:38:16'),
(19, 'ADMIN-1', 'bidder_changed', '{\"project_id\":\"2010\",\"new_bid_id\":37}', '127.0.0.1', '2026-03-14 01:38:24'),
(20, 'ADMIN-1', 'dispute_approved_for_review', '{\"dispute_id\":\"2\"}', '127.0.0.1', '2026-03-14 08:35:37'),
(21, 'ADMIN-1', 'dispute_finalized', '{\"dispute_id\":\"2\",\"penalty_applied\":false}', '127.0.0.1', '2026-03-14 08:36:20'),
(22, 'ADMIN-1', 'report_status_updated', '{\"source\":\"review\",\"report_id\":\"1\",\"new_status\":\"under_review\"}', '127.0.0.1', '2026-03-14 08:36:49'),
(23, 'ADMIN-1', 'case_resolution_action_applied', '{\"source\":\"review\",\"report_id\":\"1\",\"reported_user_id\":3002,\"action_type\":\"warning\",\"admin_action\":\"Warned\",\"ban_until\":null}', '127.0.0.1', '2026-03-14 08:37:01'),
(24, 'ADMIN-1', 'dispute_approved_for_review', '{\"dispute_id\":\"3\"}', '127.0.0.1', '2026-03-14 08:38:18'),
(25, 'ADMIN-1', 'dispute_approved_for_review', '{\"dispute_id\":\"1\"}', '127.0.0.1', '2026-03-14 08:39:36'),
(26, 'ADMIN-1', 'subscription_plan_created', '{\"name\":\"asdasd\"}', '127.0.0.1', '2026-03-14 10:24:36'),
(27, 'ADMIN-1', 'subscription_plan_deleted', '{\"plan_id\":\"5\",\"reason\":\"adasdadasdad\"}', '127.0.0.1', '2026-03-14 10:24:43'),
(28, 'ADMIN-1', 'subscription_plan_updated', '{\"plan_id\":\"1\",\"name\":\"Gold Tier Subscriptiona\"}', '127.0.0.1', '2026-03-14 10:43:46'),
(29, 'ADMIN-1', 'subscription_plan_updated', '{\"plan_id\":\"1\",\"name\":\"Gold Tier Subscriptiona\"}', '127.0.0.1', '2026-03-14 10:43:52'),
(30, 'ADMIN-1', 'subscription_plan_created', '{\"name\":\"asdasdsd\"}', '127.0.0.1', '2026-03-14 10:44:08'),
(31, 'ADMIN-1', 'subscription_plan_updated', '{\"plan_id\":\"6\",\"name\":\"asdasdsd\"}', '127.0.0.1', '2026-03-14 10:44:15'),
(32, 'ADMIN-1', 'subscription_plan_updated', '{\"plan_id\":\"2\",\"name\":\"Silver Tier Subscription\"}', '127.0.0.1', '2026-03-14 10:46:07'),
(33, 'ADMIN-1', 'subscription_plan_updated', '{\"plan_id\":\"6\",\"name\":\"asdasdsdssssssssss\"}', '127.0.0.1', '2026-03-14 10:46:17'),
(34, 'ADMIN-1', 'subscription_deactivated', '{\"subscription_id\":\"55\",\"reason\":\"asdasdsadsadad\"}', '127.0.0.1', '2026-03-14 10:46:58'),
(35, 'ADMIN-1', 'subscription_plan_updated', '{\"plan_id\":\"6\",\"name\":\"asdasdsdssssssssss\"}', '127.0.0.1', '2026-03-14 10:47:28'),
(36, 'ADMIN-1', 'subscription_plan_updated', '{\"plan_id\":\"6\",\"name\":\"asdasdsdssssssssssAHAHHAHAHA\"}', '127.0.0.1', '2026-03-14 10:49:29'),
(37, 'ADMIN-1', 'subscription_plan_updated', '{\"plan_id\":\"6\",\"name\":\"asdasd\"}', '127.0.0.1', '2026-03-14 10:49:35'),
(38, 'ADMIN-1', 'subscription_reactivated', '{\"subscription_id\":\"55\"}', '127.0.0.1', '2026-03-14 10:51:37'),
(39, 'ADMIN-1', 'subscription_plan_created', '{\"name\":\"asdasd\"}', '127.0.0.1', '2026-03-14 10:59:45'),
(40, 'ADMIN-1', 'subscription_plan_deleted', '{\"plan_id\":\"7\",\"reason\":\"asdasdasdad\"}', '127.0.0.1', '2026-03-14 10:59:52'),
(41, 'ADMIN-1', 'subscription_plan_deleted', '{\"plan_id\":\"6\",\"reason\":\"asddasdasdas\"}', '127.0.0.1', '2026-03-14 11:00:06'),
(42, 'ADMIN-1', 'subscription_deactivated', '{\"subscription_id\":\"55\",\"reason\":\"asdasdadsdsdsd\"}', '127.0.0.1', '2026-03-14 11:17:09'),
(43, 'ADMIN-1', 'payment_verified', '{\"payment_id\":\"7\",\"amount\":\"3500000.00\"}', '127.0.0.1', '2026-03-14 11:32:03'),
(44, 'ADMIN-1', 'payment_rejected', '{\"payment_id\":\"7\",\"reason\":null}', '127.0.0.1', '2026-03-14 11:33:31'),
(45, 'ADMIN-1', 'payment_deleted', '{\"payment_id\":\"7\"}', '127.0.0.1', '2026-03-14 11:36:49'),
(46, 'ADMIN-1', 'ai_analysis_run', '{\"project_id\":\"2001\"}', '127.0.0.1', '2026-03-14 13:30:19'),
(47, 'ADMIN-1', 'ai_analysis_run', '{\"project_id\":\"2002\"}', '127.0.0.1', '2026-03-14 13:31:19'),
(48, 'ADMIN-1', 'ai_analysis_run', '{\"project_id\":\"2001\"}', '127.0.0.1', '2026-03-14 13:32:18'),
(49, 'ADMIN-1', 'admin_login', NULL, '192.168.1.26', '2026-03-14 14:03:47'),
(50, 'ADMIN-1', 'admin_login', NULL, '10.11.34.248', '2026-03-14 15:22:54'),
(51, 'ADMIN-1', 'posting_approved', '{\"project_id\":\"2026\"}', '10.11.34.248', '2026-03-14 15:50:49'),
(52, 'ADMIN-1', 'dispute_approved_for_review', '{\"dispute_id\":\"11\"}', '10.11.34.248', '2026-03-14 16:26:40'),
(53, 'ADMIN-1', 'dispute_approved_for_review', '{\"dispute_id\":\"12\"}', '10.11.34.248', '2026-03-14 16:28:34'),
(54, 'ADMIN-1', 'dispute_approved_for_review', '{\"dispute_id\":\"13\"}', '10.11.34.248', '2026-03-14 16:32:16'),
(55, 'ADMIN-1', 'admin_login', NULL, '192.168.1.30', '2026-03-14 23:08:52'),
(56, 'ADMIN-1', 'admin_login', NULL, '192.168.254.111', '2026-03-15 08:22:08'),
(57, 'ADMIN-1', 'admin_login', NULL, '192.168.254.111', '2026-03-15 21:31:44'),
(58, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_login\",\"activity_id\":2,\"user_id\":3031,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-15 22:21:48'),
(59, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_login\",\"activity_id\":5,\"user_id\":3031,\"source\":\"web\"}', '127.0.0.1', '2026-03-15 22:37:22'),
(60, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"bid_submitted\",\"activity_id\":6,\"user_id\":3031,\"source\":\"web\"}', '127.0.0.1', '2026-03-15 22:37:44'),
(61, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"project_created\",\"activity_id\":7,\"user_id\":3031,\"source\":\"web\"}', '127.0.0.1', '2026-03-15 22:37:44'),
(62, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"milestone_approved\",\"activity_id\":8,\"user_id\":3031,\"source\":\"web\"}', '127.0.0.1', '2026-03-15 22:37:44'),
(63, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"dispute_filed\",\"activity_id\":9,\"user_id\":3031,\"source\":\"web\"}', '127.0.0.1', '2026-03-15 22:37:44'),
(64, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"post_created\",\"activity_id\":10,\"user_id\":3031,\"source\":\"web\"}', '127.0.0.1', '2026-03-15 22:37:44'),
(65, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_reported\",\"activity_id\":11,\"user_id\":3031,\"source\":\"web\"}', '127.0.0.1', '2026-03-15 22:37:44'),
(66, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"message_reported\",\"activity_id\":12,\"user_id\":3031,\"source\":\"web\"}', '127.0.0.1', '2026-03-15 22:37:44'),
(67, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"bid_submitted\",\"activity_id\":13,\"user_id\":3031,\"source\":\"web\"}', '127.0.0.1', '2026-03-15 22:56:32'),
(68, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"bid_rejected\",\"activity_id\":14,\"user_id\":3031,\"source\":\"web\"}', '127.0.0.1', '2026-03-15 22:56:32'),
(69, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"project_completed\",\"activity_id\":15,\"user_id\":3031,\"source\":\"web\"}', '127.0.0.1', '2026-03-15 22:56:32'),
(70, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_login\",\"activity_id\":16,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-15 23:04:19'),
(71, 'ADMIN-1', 'posting_approved', '{\"project_id\":\"2027\"}', '192.168.254.111', '2026-03-15 23:07:54'),
(72, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"bid_submitted\",\"activity_id\":17,\"user_id\":3031,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-15 23:12:13'),
(73, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"bid_rejected\",\"activity_id\":18,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-15 23:14:01'),
(74, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"account_status_changed\",\"activity_id\":19,\"user_id\":3031,\"source\":\"web\"}', '127.0.0.1', '2026-03-15 23:28:37'),
(75, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_registered\",\"activity_id\":20,\"user_id\":3031,\"source\":\"web\"}', '127.0.0.1', '2026-03-15 23:28:37'),
(76, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"password_reset\",\"activity_id\":21,\"user_id\":3031,\"source\":\"web\"}', '127.0.0.1', '2026-03-15 23:28:37'),
(77, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_login\",\"activity_id\":22,\"user_id\":3031,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-15 23:36:21'),
(78, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_login\",\"activity_id\":23,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-15 23:38:36'),
(79, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_logout\",\"activity_id\":24,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-15 23:51:59'),
(80, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"account_status_changed\",\"activity_id\":25,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-15 23:52:18'),
(81, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"account_status_changed\",\"activity_id\":26,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-15 23:52:37'),
(82, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"account_status_changed\",\"activity_id\":27,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-15 23:53:00'),
(83, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"bid_cancelled\",\"activity_id\":28,\"user_id\":3031,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-15 23:53:55'),
(84, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"bid_updated\",\"activity_id\":29,\"user_id\":3031,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-15 23:54:16'),
(85, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_login\",\"activity_id\":30,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 00:20:18'),
(86, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_logout\",\"activity_id\":31,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 01:27:26'),
(87, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_login\",\"activity_id\":32,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 01:27:48'),
(88, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"account_status_changed\",\"activity_id\":33,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 02:29:11'),
(89, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_logout\",\"activity_id\":34,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 02:29:38'),
(90, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_login\",\"activity_id\":35,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 02:29:57'),
(91, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"account_status_changed\",\"activity_id\":36,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 02:30:13'),
(92, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"account_status_changed\",\"activity_id\":37,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 02:31:12'),
(93, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_logout\",\"activity_id\":38,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 02:32:01'),
(94, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_login\",\"activity_id\":39,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 02:39:30'),
(95, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"account_status_changed\",\"activity_id\":40,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 02:42:18'),
(96, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_logout\",\"activity_id\":41,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 02:42:32'),
(97, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"account_status_changed\",\"activity_id\":42,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 02:42:51'),
(98, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_login\",\"activity_id\":43,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 02:43:13'),
(99, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"account_status_changed\",\"activity_id\":44,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 02:44:09'),
(100, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_logout\",\"activity_id\":45,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 02:44:21'),
(101, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_login\",\"activity_id\":46,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 02:52:36'),
(102, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"account_status_changed\",\"activity_id\":47,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 03:06:18'),
(103, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_logout\",\"activity_id\":48,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 09:19:14'),
(104, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_login\",\"activity_id\":49,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 09:54:44'),
(105, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_logout\",\"activity_id\":50,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 09:55:22'),
(106, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_login\",\"activity_id\":51,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 10:34:10'),
(107, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_logout\",\"activity_id\":52,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 11:11:25'),
(108, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_login\",\"activity_id\":53,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 11:11:45'),
(109, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_logout\",\"activity_id\":54,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 11:36:04'),
(110, 'ADMIN-1', 'user_activity_notification_generated', '{\"activity_type\":\"user_login\",\"activity_id\":55,\"user_id\":3032,\"source\":\"mobile\"}', '192.168.254.111', '2026-03-16 11:44:50');

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

--
-- Dumping data for table `admin_sent_notifications`
--

INSERT INTO `admin_sent_notifications` (`id`, `admin_id`, `title`, `message`, `delivery_method`, `target_type`, `target_user_ids`, `recipient_count`, `sent_at`) VALUES
(1, 'ADMIN-1', 'gggg', 'shane_owner', 'both', 'targeted', '1001', 1, '2026-03-13 22:01:48'),
(2, 'ADMIN-1', 'are you there?', 'Hello there', 'both', 'targeted', '3031', 1, '2026-03-15 10:27:44');

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
('ADMIN-1', 'legatura_hq2026', 'sandbox.info.official@gmail.com', '$2y$12$.2ali6CucAVDUpSsjlvlPucDtp2j8gI4Ml7pbKtyZ0fpCiml.nVVe', 'Ph', NULL, 'Legatura', 1, '2026-03-05 00:07:36', 'profiles/logo_test.svg');

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
(1, 2001, 'DELAYED', 0.7500, 1, '{\"prediction\":{\"delay_probability\":0.75,\"prediction\":\"DELAYED\",\"reason\":\"CRITICAL: 1 active dispute(s) detected. Construction disputes typically slow or halt work progress.\"},\"analysis_report\":{\"conclusion\":\"The project \'Modern Residential Complex\' is currently ahead of schedule. AI predicts a 75.0% probability of delay. Weather impact is Minimal (0.0mm rain). Holidays impact 0% of remaining time. CRITICAL: 1 active dispute(s) detected. Construction disputes typically slow or halt work progress.\",\"pacing_status\":{\"pacing_index\":1.01,\"avg_delay_days\":-1.2,\"rejected_count\":0,\"details\":[{\"title\":\"Site Preparation & Excavation\",\"status\":\"approved\",\"days_variance\":0,\"pacing_label\":\"ON-TIME\\/EARLY\"},{\"title\":\"Foundation Laying\",\"status\":\"submitted\",\"days_variance\":-5,\"pacing_label\":\"ON-TIME\\/EARLY\"},{\"title\":\"Structural Framework\",\"status\":\"No Submission\",\"days_variance\":0,\"pacing_label\":\"Pending\"},{\"title\":\"Ground Floor Completion\",\"status\":\"No Submission\",\"days_variance\":0,\"pacing_label\":\"Pending\"}]},\"contractor_audit\":{\"experience\":\"8 Years\",\"historical_success\":\"59%\",\"flagged\":false}},\"weather\":{\"avg_temp\":4,\"avg_humidity\":95,\"avg_wind\":6.1,\"total_rain\":0,\"condition_text\":\"Mist\",\"condition_icon\":\"https:\\/\\/cdn.weatherapi.com\\/weather\\/64x64\\/night\\/143.png\",\"forecast\":[{\"date\":\"Sun, Mar 15\",\"temp_avg\":13,\"condition\":\"Partly Cloudy \",\"icon\":\"https:\\/\\/cdn.weatherapi.com\\/weather\\/64x64\\/day\\/116.png\",\"rain_chance\":0},{\"date\":\"Mon, Mar 16\",\"temp_avg\":14.9,\"condition\":\"Patchy rain nearby\",\"icon\":\"https:\\/\\/cdn.weatherapi.com\\/weather\\/64x64\\/day\\/176.png\",\"rain_chance\":82},{\"date\":\"Tue, Mar 17\",\"temp_avg\":17,\"condition\":\"Patchy rain nearby\",\"icon\":\"https:\\/\\/cdn.weatherapi.com\\/weather\\/64x64\\/day\\/176.png\",\"rain_chance\":80}]},\"weather_severity\":1,\"dds_recommendations\":[\"\\u2696\\ufe0f LEGAL RISK: Active disputes detected. Assign a mediator immediately.\",\"\\ud83d\\udce2 MANAGEMENT ACTION: High Risk of Delay. Convene emergency meeting with contractor.\"],\"enso_state\":\"Neutral\"}', '2026-03-14 13:30:19', '2026-03-14 13:30:19'),
(2, 2002, 'DELAYED', 0.7500, 0, '{\"prediction\":{\"delay_probability\":0.75,\"prediction\":\"DELAYED\",\"reason\":\"CRITICAL: 2 active dispute(s) detected. Construction disputes typically slow or halt work progress.\"},\"analysis_report\":{\"conclusion\":\"The project \'Commercial Office Space\' is currently ahead of schedule. AI predicts a 75.0% probability of delay. Weather impact is Minimal (0.0mm rain). Holidays impact 0% of remaining time. CRITICAL: 2 active dispute(s) detected. Construction disputes typically slow or halt work progress.\",\"pacing_status\":{\"pacing_index\":0.9,\"avg_delay_days\":0,\"rejected_count\":0,\"details\":[{\"title\":\"Demolition & Cleanup\",\"status\":\"approved\",\"days_variance\":0,\"pacing_label\":\"ON-TIME\\/EARLY\"},{\"title\":\"Electrical & Plumbing Installation\",\"status\":\"approved\",\"days_variance\":0,\"pacing_label\":\"ON-TIME\\/EARLY\"},{\"title\":\"Interior Finishing & Painting\",\"status\":\"approved\",\"days_variance\":0,\"pacing_label\":\"ON-TIME\\/EARLY\"}]},\"contractor_audit\":{\"experience\":\"7 Years\",\"historical_success\":\"85%\",\"flagged\":false}},\"weather\":{\"avg_temp\":15.8,\"avg_humidity\":76,\"avg_wind\":9.7,\"total_rain\":0,\"condition_text\":\"Clear\",\"condition_icon\":\"https:\\/\\/cdn.weatherapi.com\\/weather\\/64x64\\/night\\/113.png\",\"forecast\":[{\"date\":\"Sun, Mar 15\",\"temp_avg\":18.4,\"condition\":\"Sunny\",\"icon\":\"https:\\/\\/cdn.weatherapi.com\\/weather\\/64x64\\/day\\/113.png\",\"rain_chance\":0},{\"date\":\"Mon, Mar 16\",\"temp_avg\":19.2,\"condition\":\"Sunny\",\"icon\":\"https:\\/\\/cdn.weatherapi.com\\/weather\\/64x64\\/day\\/113.png\",\"rain_chance\":0},{\"date\":\"Tue, Mar 17\",\"temp_avg\":20.6,\"condition\":\"Patchy rain nearby\",\"icon\":\"https:\\/\\/cdn.weatherapi.com\\/weather\\/64x64\\/day\\/176.png\",\"rain_chance\":83}]},\"weather_severity\":0,\"dds_recommendations\":[\"\\u2696\\ufe0f LEGAL RISK: Active disputes detected. Assign a mediator immediately.\",\"\\ud83d\\udce2 MANAGEMENT ACTION: High Risk of Delay. Convene emergency meeting with contractor.\"],\"enso_state\":\"Neutral\"}', '2026-03-14 13:31:19', '2026-03-14 13:31:19'),
(3, 2001, 'DELAYED', 0.7500, 1, '{\"prediction\":{\"delay_probability\":0.75,\"prediction\":\"DELAYED\",\"reason\":\"CRITICAL: 1 active dispute(s) detected. Construction disputes typically slow or halt work progress.\"},\"analysis_report\":{\"conclusion\":\"The project \'Modern Residential Complex\' is currently ahead of schedule. AI predicts a 75.0% probability of delay. Weather impact is Minimal (0.0mm rain). Holidays impact 0% of remaining time. CRITICAL: 1 active dispute(s) detected. Construction disputes typically slow or halt work progress.\",\"pacing_status\":{\"pacing_index\":1.01,\"avg_delay_days\":-1.2,\"rejected_count\":0,\"details\":[{\"title\":\"Site Preparation & Excavation\",\"status\":\"approved\",\"days_variance\":0,\"pacing_label\":\"ON-TIME\\/EARLY\"},{\"title\":\"Foundation Laying\",\"status\":\"submitted\",\"days_variance\":-5,\"pacing_label\":\"ON-TIME\\/EARLY\"},{\"title\":\"Structural Framework\",\"status\":\"No Submission\",\"days_variance\":0,\"pacing_label\":\"Pending\"},{\"title\":\"Ground Floor Completion\",\"status\":\"No Submission\",\"days_variance\":0,\"pacing_label\":\"Pending\"}]},\"contractor_audit\":{\"experience\":\"8 Years\",\"historical_success\":\"59%\",\"flagged\":false}},\"weather\":{\"avg_temp\":4,\"avg_humidity\":95,\"avg_wind\":6.1,\"total_rain\":0,\"condition_text\":\"Mist\",\"condition_icon\":\"https:\\/\\/cdn.weatherapi.com\\/weather\\/64x64\\/night\\/143.png\",\"forecast\":[{\"date\":\"Sun, Mar 15\",\"temp_avg\":13,\"condition\":\"Partly Cloudy \",\"icon\":\"https:\\/\\/cdn.weatherapi.com\\/weather\\/64x64\\/day\\/116.png\",\"rain_chance\":0},{\"date\":\"Mon, Mar 16\",\"temp_avg\":14.9,\"condition\":\"Patchy rain nearby\",\"icon\":\"https:\\/\\/cdn.weatherapi.com\\/weather\\/64x64\\/day\\/176.png\",\"rain_chance\":82},{\"date\":\"Tue, Mar 17\",\"temp_avg\":17,\"condition\":\"Patchy rain nearby\",\"icon\":\"https:\\/\\/cdn.weatherapi.com\\/weather\\/64x64\\/day\\/176.png\",\"rain_chance\":80}]},\"weather_severity\":1,\"dds_recommendations\":[\"\\u2696\\ufe0f LEGAL RISK: Active disputes detected. Assign a mediator immediately.\",\"\\ud83d\\udce2 MANAGEMENT ACTION: High Risk of Delay. Convene emergency meeting with contractor.\"],\"enso_state\":\"Neutral\"}', '2026-03-14 13:32:18', '2026-03-14 13:32:18');

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
(1, 2001, 52, 5800000.00, 180, 'We have extensive experience with residential complexes. Our team can deliver quality work on schedule with competitive pricing.', 'accepted', NULL, '2026-03-01 08:00:00', '2026-03-05 14:30:00'),
(2, 2001, 53, 6200000.00, 200, 'Experienced team ready to start immediately.', 'rejected', 'Higher cost and longer timeline compared to selected contractor', '2026-03-01 09:15:00', '2026-03-05 14:30:00'),
(3, 2001, 54, 6500000.00, 210, 'Quality assured work with premium materials.', 'rejected', 'Cost exceeds budget range', '2026-03-01 10:30:00', '2026-03-05 14:30:00'),
(4, 2002, 55, 2400000.00, 60, 'Specialized in commercial renovations. Quick turnaround with excellent quality.', 'accepted', NULL, '2026-02-15 07:00:00', '2026-02-20 11:00:00'),
(5, 2002, 56, 2700000.00, 75, 'Professional team with good track record.', 'rejected', 'Higher cost and longer timeline', '2026-02-15 08:30:00', '2026-02-20 11:00:00'),
(6, 2002, 57, 2900000.00, 90, 'Premium quality renovation services.', 'rejected', 'Cost exceeds budget', '2026-02-15 09:45:00', '2026-02-20 11:00:00'),
(7, 2003, 58, 8800000.00, 150, 'Industrial construction specialists. Proven track record with warehouse projects.', 'accepted', NULL, '2026-02-10 06:30:00', '2026-02-18 10:15:00'),
(8, 2003, 59, 9200000.00, 170, 'Experienced industrial contractors.', 'rejected', 'Higher cost and longer timeline', '2026-02-10 07:45:00', '2026-02-18 10:15:00'),
(9, 2003, 60, 9500000.00, 180, 'Quality industrial work guaranteed.', 'rejected', 'Cost exceeds budget', '2026-02-10 09:00:00', '2026-02-18 10:15:00'),
(10, 2004, 62, 1750000.00, 90, 'Agricultural facility specialists. Efficient and cost-effective solutions.', 'accepted', NULL, '2026-02-20 08:00:00', '2026-02-25 13:45:00'),
(11, 2004, 61, 1950000.00, 110, 'Experienced in agricultural projects.', 'rejected', 'Higher cost and longer timeline', '2026-02-20 09:15:00', '2026-02-25 13:45:00'),
(12, 2004, 63, 2100000.00, 120, 'Premium agricultural construction.', 'rejected', 'Cost exceeds budget', '2026-02-20 10:30:00', '2026-02-25 13:45:00'),
(13, 2005, 64, 17500000.00, 300, 'Large-scale residential development experts. Proven capability with 50+ unit projects.', 'rejected', 'Contractor changed by administrator', '2026-02-25 07:30:00', '2026-03-14 00:13:11'),
(14, 2005, 65, 18500000.00, 330, 'Experienced residential developers.', 'rejected', 'Higher cost and longer timeline', '2026-02-25 08:45:00', '2026-03-03 15:20:00'),
(15, 2005, 66, 19200000.00, 350, 'Premium residential construction.', 'accepted', 'Cost exceeds budget', '2026-02-25 10:00:00', '2026-03-14 00:13:11'),
(16, 2021, 67, 7800000.00, 160, 'Residential complex specialists with proven track record.', 'accepted', NULL, '2026-02-28 08:15:00', '2026-03-04 12:30:00'),
(17, 2021, 68, 8200000.00, 180, 'Experienced residential contractors.', 'rejected', 'Higher cost and longer timeline', '2026-02-28 09:30:00', '2026-03-04 12:30:00'),
(18, 2021, 69, 8600000.00, 200, 'Premium quality residential work.', 'rejected', 'Cost exceeds budget', '2026-02-28 10:45:00', '2026-03-04 12:30:00'),
(19, 2022, 70, 5800000.00, 140, 'Retail center construction specialists.', 'accepted', NULL, '2026-03-01 07:45:00', '2026-03-05 11:15:00'),
(20, 2022, 71, 6200000.00, 160, 'Commercial construction experts.', 'rejected', 'Higher cost and longer timeline', '2026-03-01 09:00:00', '2026-03-05 11:15:00'),
(21, 2022, 72, 6600000.00, 180, 'Premium commercial construction.', 'rejected', 'Cost exceeds budget', '2026-03-01 10:15:00', '2026-03-05 11:15:00'),
(22, 2024, 75, 9200000.00, 170, 'Office building construction experts with modern design capabilities.', 'accepted', NULL, '2026-03-02 08:30:00', '2026-03-06 14:00:00'),
(23, 2024, 76, 9800000.00, 190, 'Experienced office building contractors.', 'rejected', 'Higher cost and longer timeline', '2026-03-02 09:45:00', '2026-03-06 14:00:00'),
(24, 2024, 77, 10400000.00, 210, 'Premium office construction services.', 'rejected', 'Cost exceeds budget', '2026-03-02 11:00:00', '2026-03-06 14:00:00'),
(25, 2006, 78, 11500000.00, 240, 'Luxury residential specialists with premium finishes.', 'submitted', NULL, '2026-03-03 08:00:00', NULL),
(26, 2006, 79, 12000000.00, 260, 'High-end residential construction.', 'submitted', NULL, '2026-03-03 09:30:00', NULL),
(27, 2006, 80, 12500000.00, 280, 'Luxury home builders.', 'submitted', NULL, '2026-03-03 11:00:00', NULL),
(28, 2007, 81, 55000000.00, 450, 'Large-scale commercial development specialists.', 'accepted', NULL, '2026-03-04 07:30:00', '2026-03-14 00:11:09'),
(29, 2007, 52, 58000000.00, 480, 'Shopping mall construction experts.', 'rejected', NULL, '2026-03-04 09:00:00', '2026-03-14 00:11:09'),
(30, 2007, 53, 60000000.00, 500, 'Premium mall development.', 'rejected', NULL, '2026-03-04 10:30:00', '2026-03-14 00:11:01'),
(31, 2008, 54, 35000000.00, 360, 'High-rise residential specialists.', 'submitted', NULL, '2026-03-05 08:15:00', NULL),
(32, 2008, 55, 38000000.00, 390, 'Condo tower construction experts.', 'submitted', NULL, '2026-03-05 09:45:00', NULL),
(33, 2008, 56, 40000000.00, 420, 'Premium high-rise construction.', 'submitted', NULL, '2026-03-05 11:15:00', NULL),
(34, 2009, 57, 22000000.00, 280, 'Medical facility construction specialists.', 'submitted', NULL, '2026-03-06 07:45:00', NULL),
(35, 2009, 58, 24000000.00, 300, 'Healthcare construction experts.', 'submitted', NULL, '2026-03-06 09:15:00', NULL),
(36, 2009, 59, 26000000.00, 320, 'Premium medical center construction.', 'submitted', NULL, '2026-03-06 10:45:00', NULL),
(37, 2010, 60, 9000000.00, 200, 'Educational building specialists.', 'accepted', NULL, '2026-03-07 08:00:00', '2026-03-14 01:38:24'),
(38, 2010, 61, 9500000.00, 220, 'School construction experts.', 'rejected', NULL, '2026-03-07 09:30:00', '2026-03-14 01:38:24'),
(39, 2010, 62, 10000000.00, 240, 'Premium educational construction.', 'rejected', NULL, '2026-03-07 11:00:00', '2026-03-14 01:38:16'),
(40, 2011, 63, 6500000.00, 150, 'Townhouse development specialists.', 'submitted', NULL, '2026-03-08 08:30:00', NULL),
(41, 2011, 64, 7000000.00, 170, 'Residential townhouse experts.', 'submitted', NULL, '2026-03-08 10:00:00', NULL),
(42, 2011, 65, 7500000.00, 190, 'Premium townhouse construction.', 'submitted', NULL, '2026-03-08 11:30:00', NULL),
(43, 2012, 66, 3500000.00, 120, 'Community center construction specialists.', 'submitted', NULL, '2026-03-09 08:15:00', NULL),
(44, 2012, 67, 3800000.00, 140, 'Multi-purpose facility experts.', 'submitted', NULL, '2026-03-09 09:45:00', NULL),
(45, 2012, 68, 4000000.00, 160, 'Premium community center construction.', 'submitted', NULL, '2026-03-09 11:15:00', NULL),
(46, 2013, 69, 13500000.00, 280, 'Retail complex specialists.', 'submitted', NULL, '2026-03-10 08:00:00', NULL),
(47, 2013, 70, 14500000.00, 300, 'Shopping complex experts.', 'submitted', NULL, '2026-03-10 09:30:00', NULL),
(48, 2013, 71, 15500000.00, 320, 'Premium retail construction.', 'submitted', NULL, '2026-03-10 11:00:00', NULL),
(49, 2014, 72, 4500000.00, 140, 'Agricultural processing specialists.', 'submitted', NULL, '2026-03-11 08:30:00', NULL),
(50, 2014, 73, 4800000.00, 160, 'Agricultural facility experts.', 'submitted', NULL, '2026-03-11 10:00:00', NULL),
(51, 2014, 74, 5000000.00, 180, 'Premium agricultural construction.', 'submitted', NULL, '2026-03-11 11:30:00', NULL),
(52, 2015, 75, 20000000.00, 360, 'Industrial complex specialists.', 'submitted', NULL, '2026-03-12 08:15:00', NULL),
(53, 2015, 76, 22000000.00, 390, 'Large industrial facility experts.', 'submitted', NULL, '2026-03-12 09:45:00', NULL),
(54, 2015, 77, 24000000.00, 420, 'Premium industrial construction.', 'submitted', NULL, '2026-03-12 11:15:00', NULL),
(55, 2016, 78, 28000000.00, 400, 'Residential estate specialists.', 'submitted', NULL, '2026-03-13 08:00:00', NULL),
(56, 2016, 79, 30000000.00, 430, 'Large estate development experts.', 'submitted', NULL, '2026-03-13 09:30:00', NULL),
(57, 2016, 80, 32000000.00, 460, 'Premium estate construction.', 'submitted', NULL, '2026-03-13 11:00:00', NULL),
(58, 2017, 81, 45000000.00, 500, 'High-rise office specialists.', 'submitted', NULL, '2026-03-14 08:30:00', NULL),
(59, 2017, 52, 48000000.00, 530, 'Commercial tower experts.', 'submitted', NULL, '2026-03-14 10:00:00', NULL),
(60, 2017, 53, 50000000.00, 560, 'Premium office tower construction.', 'submitted', NULL, '2026-03-14 11:30:00', NULL),
(61, 2018, 54, 55000000.00, 520, 'Industrial complex specialists.', 'submitted', NULL, '2026-03-15 08:15:00', NULL),
(62, 2018, 55, 58000000.00, 550, 'Large industrial experts.', 'submitted', NULL, '2026-03-15 09:45:00', NULL),
(63, 2018, 56, 60000000.00, 580, 'Premium industrial construction.', 'submitted', NULL, '2026-03-15 11:15:00', NULL),
(64, 2019, 57, 9500000.00, 240, 'Agricultural farm specialists.', 'submitted', NULL, '2026-03-16 08:00:00', NULL),
(65, 2019, 58, 10500000.00, 270, 'Large farm development experts.', 'submitted', NULL, '2026-03-16 09:30:00', NULL),
(66, 2019, 59, 11500000.00, 300, 'Premium agricultural development.', 'submitted', NULL, '2026-03-16 11:00:00', NULL),
(67, 2020, 60, 40000000.00, 450, 'Mixed-use development specialists.', 'submitted', NULL, '2026-03-17 08:30:00', NULL),
(68, 2020, 61, 43000000.00, 480, 'Complex development experts.', 'submitted', NULL, '2026-03-17 10:00:00', NULL),
(69, 2020, 62, 45000000.00, 510, 'Premium mixed-use construction.', 'submitted', NULL, '2026-03-17 11:30:00', NULL),
(70, 2023, 63, 14000000.00, 320, 'Residential subdivision specialists.', 'submitted', NULL, '2026-03-18 08:15:00', NULL),
(71, 2023, 64, 15000000.00, 350, 'Subdivision development experts.', 'submitted', NULL, '2026-03-18 09:45:00', NULL),
(72, 2023, 65, 16000000.00, 380, 'Premium subdivision construction.', 'submitted', NULL, '2026-03-18 11:15:00', NULL),
(73, 2025, 66, 7000000.00, 180, 'Warehouse and logistics specialists.', 'submitted', NULL, '2026-03-19 08:00:00', NULL),
(74, 2025, 67, 7500000.00, 200, 'Logistics facility experts.', 'submitted', NULL, '2026-03-19 09:30:00', NULL),
(75, 2025, 68, 8000000.00, 220, 'Premium warehouse construction.', 'submitted', NULL, '2026-03-19 11:00:00', NULL),
(76, 2026, 102, 100000.00, 24, 'mgzkgdkydkgxmgxnyr', 'accepted', NULL, '2026-03-14 15:52:06', '2026-03-14 15:52:37'),
(77, 2001, 102, 7000000.00, 24, 'auksgdldlisdhfkjwbvushfsdgvusgf', 'cancelled', NULL, '2026-03-15 22:22:32', NULL),
(78, 2006, 102, 15000000.00, 36, 'werfartgdyxfghnfthvsrveay', 'submitted', NULL, '2026-03-15 23:54:16', NULL),
(79, 2008, 102, 50000000.00, 24, 'akjsgbcoisjdv;iehf', 'submitted', NULL, '2026-03-15 22:42:07', NULL),
(80, 2027, 102, 4565.00, 24, 'gggggvbbnbjhhffdd', 'rejected', 'kasi', '2026-03-15 23:12:09', '2026-03-15 23:13:57');

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
(1, 76, 'received_967294015879217.jpeg', 'bid_files/1773532326_69b5f4a65216e_received_967294015879217.jpeg', NULL, '2026-03-14 15:52:06'),
(2, 77, 'IMG_20260314_173002.jpg', 'bid_files/1773642152_69b7a1a8269c5_IMG_20260314_173002.jpg', NULL, '2026-03-15 22:22:33'),
(3, 78, 'IMG_20260314_173008.jpg', 'bid_files/1773642681_69b7a3b907451_IMG_20260314_173008.jpg', NULL, '2026-03-15 22:31:21'),
(4, 79, 'IMG_20260314_173002.jpg', 'bid_files/1773643327_69b7a63fba641_IMG_20260314_173002.jpg', NULL, '2026-03-15 22:42:07'),
(5, 80, 'IMG_20260314_173002.jpg', 'bid_files/1773645129_69b7ad495ba68_IMG_20260314_173002.jpg', NULL, '2026-03-15 23:12:09');

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
  `admin_action` varchar(100) DEFAULT NULL,
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
  `deletion_scheduled_at` timestamp NULL DEFAULT NULL,
  `deactivation_reason` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `completed_projects` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contractors`
--

INSERT INTO `contractors` (`contractor_id`, `owner_id`, `company_logo`, `company_banner`, `company_name`, `company_start_date`, `years_of_experience`, `type_id`, `contractor_type_other`, `services_offered`, `business_address`, `company_email`, `company_website`, `company_social_media`, `company_description`, `picab_number`, `picab_category`, `picab_expiration_date`, `business_permit_number`, `business_permit_city`, `business_permit_expiration`, `tin_business_reg_number`, `dti_sec_registration_photo`, `verification_status`, `verification_date`, `is_active`, `suspension_until`, `suspension_reason`, `deletion_reason`, `deletion_scheduled_at`, `deactivation_reason`, `rejection_reason`, `completed_projects`, `created_at`, `updated_at`) VALUES
(52, 201, NULL, NULL, 'Dingdong Builders', '2024-01-15', 8, 1, NULL, 'Residential Construction', 'Tetuan, Poblacion, Mankayan, Benguet 2600', 'dingdong@builders.com', NULL, NULL, 'Quality residential construction with 8 years experience', 'PCAB-301', 'A', '2027-06-15', 'BP-301', 'Mankayan', '2027-03-15', 'TIN-301', 'dti_dingdong.jpg', 'approved', '2026-02-01 10:00:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 12, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(53, 202, NULL, NULL, 'Anne Builders Corp', '2023-06-20', 10, 1, NULL, 'Commercial Construction', 'Magsaysay, Barangay Poblacion, Baguio City, Benguet 2600', 'anne@builders.com', NULL, NULL, 'Commercial and residential projects', 'PCAB-302', 'AA', '2027-12-20', 'BP-302', 'Baguio', '2027-06-20', 'TIN-302', 'dti_anne.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 8, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(54, 203, NULL, NULL, 'Alden Construction', '2022-03-10', 12, 1, NULL, 'Heavy Infrastructure', 'Session Road, Barangay Asin, Baguio City, Benguet 2600', 'alden@construction.com', NULL, NULL, 'Infrastructure and heavy construction', 'PCAB-303', 'AAA', '2028-03-10', 'BP-303', 'Baguio', '2027-09-10', 'TIN-303', 'dti_alden.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, NULL, NULL, 'Incomplete documentation', 5, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(55, 204, NULL, NULL, 'Arjo Builders', '2023-11-05', 7, 2, NULL, 'Electrical & Mechanical', 'Burnham Road, Barangay Irisan, Baguio City, Benguet 2600', 'arjo@builders.com', NULL, NULL, 'Specialized electrical and mechanical work', 'PCAB-304', 'B', '2027-11-05', 'BP-304', 'Baguio', '2027-05-05', 'TIN-304', 'dti_arjo.jpg', 'approved', '2026-02-15 14:30:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 9, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(56, 205, NULL, NULL, 'Aga Sarao Construction', '2024-02-28', 6, 3, NULL, 'Landscaping & Masonry', 'Marcos Highway, Barangay Loakan, Baguio City, Benguet 2600', 'aga@sarao.com', NULL, NULL, 'Landscaping and masonry services', 'PCAB-305', 'C', '2027-02-28', 'BP-305', 'Baguio', '2027-08-28', 'TIN-305', 'dti_aga.jpg', 'deleted', NULL, 0, NULL, NULL, 'Company closure', NULL, NULL, NULL, 3, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(57, 206, NULL, NULL, 'Angelica Builders', '2023-09-12', 9, 1, NULL, 'Residential Projects', 'Naguilian Road, Barangay Pinsao, Baguio City, Benguet 2600', 'angelica@builders.com', NULL, NULL, 'Premium residential construction', 'PCAB-306', 'AA', '2027-09-12', 'BP-306', 'Baguio', '2027-03-12', 'TIN-306', 'dti_angelica.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 7, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(58, 207, NULL, NULL, 'Andi Builders', '2022-07-18', 11, 1, NULL, 'Mixed Construction', 'Bokawkan Road, Barangay Cabayan, Baguio City, Benguet 2600', 'andi@builders.com', NULL, NULL, 'Mixed residential and commercial', 'PCAB-307', 'A', '2028-07-18', 'BP-307', 'Baguio', '2027-01-18', 'TIN-307', 'dti_andi.jpg', 'approved', '2026-01-20 09:15:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 15, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(59, 208, NULL, NULL, 'Gretchen Builders', '2023-05-22', 8, 2, NULL, 'Interior Design', 'Asin Road, Barangay Asin, Baguio City, Benguet 2600', 'gretchen@builders.com', NULL, NULL, 'Interior design and finishing', 'PCAB-308', 'B', '2027-05-22', 'BP-308', 'Baguio', '2027-11-22', 'TIN-308', 'dti_gretchen.jpg', 'approved', '2026-02-10 11:45:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 6, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(60, 209, NULL, NULL, 'Graceful Structures', '2024-04-03', 5, 3, NULL, 'Roofing Specialist', 'Outlook Drive, Barangay Outlook, Baguio City, Benguet 2600', 'graceful@structures.com', NULL, NULL, 'Roofing and waterproofing', 'PCAB-309', 'C', '2027-04-03', 'BP-309', 'Baguio', '2027-10-03', 'TIN-309', 'dti_graceful.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, NULL, NULL, 'Unverified credentials', 2, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(61, 210, NULL, NULL, 'Gringo Builders', '2023-08-14', 9, 1, NULL, 'General Construction', 'Mines View Road, Barangay Mines View, Baguio City, Benguet 2600', 'gringo@builders.com', NULL, NULL, 'General construction services', 'PCAB-310', 'AA', '2027-08-14', 'BP-310', 'Baguio', '2027-02-14', 'TIN-310', 'dti_gringo.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 8, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(62, 211, NULL, NULL, 'Gino Builders', '2022-12-01', 13, 1, NULL, 'High-Rise Construction', 'Trancoville, Barangay Trancoville, Baguio City, Benguet 2600', 'gino@builders.com', NULL, NULL, 'High-rise and complex projects', 'PCAB-311', 'AAA', '2028-12-01', 'BP-311', 'Baguio', '2027-06-01', 'TIN-311', 'dti_gino.jpg', 'approved', '2026-01-15 13:20:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 18, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(63, 212, NULL, NULL, 'Giancarlo Builders', '2023-10-09', 7, 2, NULL, 'Mechanical Systems', 'Pacdal Road, Barangay Pacdal, Baguio City, Benguet 2600', 'giancarlo@builders.com', NULL, NULL, 'HVAC and mechanical systems', 'PCAB-312', 'B', '2027-10-09', 'BP-312', 'Baguio', '2027-04-09', 'TIN-312', 'dti_giancarlo.jpg', 'deleted', NULL, 0, NULL, NULL, 'Voluntary closure', NULL, NULL, NULL, 4, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(64, 213, NULL, NULL, 'Giselle Builders', '2024-01-25', 6, 3, NULL, 'Landscaping', 'Inya Road, Barangay Inya, Baguio City, Benguet 2600', 'giselle@builders.com', NULL, NULL, 'Landscape design and installation', 'PCAB-313', 'C', '2027-01-25', 'BP-313', 'Baguio', '2027-07-25', 'TIN-313', 'dti_giselle.jpg', 'approved', '2026-02-20 15:00:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 5, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(65, 214, NULL, NULL, 'Glenda Builders', '2023-04-17', 10, 1, NULL, 'Residential Complex', 'Kisad Road, Barangay Kisad, Baguio City, Benguet 2600', 'glenda@builders.com', NULL, NULL, 'Residential complex development', 'PCAB-314', 'AA', '2027-04-17', 'BP-314', 'Baguio', '2027-10-17', 'TIN-314', 'dti_glenda.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 11, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(66, 215, NULL, NULL, 'Glaiza Builders', '2022-09-06', 12, 1, NULL, 'Infrastructure', 'Chanum Road, Barangay Chanum, Baguio City, Benguet 2600', 'glaiza@builders.com', NULL, NULL, 'Infrastructure development', 'PCAB-315', 'AAA', '2028-09-06', 'BP-315', 'Baguio', '2027-03-06', 'TIN-315', 'dti_glaiza.jpg', 'approved', '2026-01-25 10:30:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 16, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(67, 216, NULL, NULL, 'Gladys Builders', '2023-11-28', 8, 2, NULL, 'Electrical Works', 'Shuntug Road, Barangay Shuntug, Baguio City, Benguet 2600', 'gladys@builders.com', NULL, NULL, 'Electrical installation and maintenance', 'PCAB-316', 'B', '2027-11-28', 'BP-316', 'Baguio', '2027-05-28', 'TIN-316', 'dti_gladys.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, NULL, NULL, 'Failed inspection', 3, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(68, 217, NULL, NULL, 'Glenda Reyes Builders', '2024-03-11', 5, 3, NULL, 'Painting Services', 'Loakan Road, Barangay Loakan, Baguio City, Benguet 2600', 'glenda.reyes@builders.com', NULL, NULL, 'Professional painting and coating', 'PCAB-317', 'C', '2027-03-11', 'BP-317', 'Baguio', '2027-09-11', 'TIN-317', 'dti_glenda_reyes.jpg', 'approved', '2026-02-05 14:15:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 4, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(69, 218, NULL, NULL, 'Glenda Santos Builders', '2023-07-19', 9, 1, NULL, 'General Contractor', 'Pinsao Road, Barangay Pinsao, Baguio City, Benguet 2600', 'glenda.santos@builders.com', NULL, NULL, 'Full-service general contracting', 'PCAB-318', 'AA', '2027-07-19', 'BP-318', 'Baguio', '2027-01-19', 'TIN-318', 'dti_glenda_santos.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 10, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(70, 219, NULL, NULL, 'Glenda Villanueva Builders', '2022-05-08', 14, 1, NULL, 'Major Projects', 'Cabayan Road, Barangay Cabayan, Baguio City, Benguet 2600', 'glenda.villanueva@builders.com', NULL, NULL, 'Large-scale project execution', 'PCAB-319', 'AAAA', '2028-05-08', 'BP-319', 'Baguio', '2027-11-08', 'TIN-319', 'dti_glenda_villanueva.jpg', 'approved', '2026-01-10 09:45:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 22, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(71, 220, NULL, NULL, 'Glenda Gonzales Builders', '2023-02-14', 8, 2, NULL, 'Plumbing Systems', 'Asin Road, Barangay Asin, Baguio City, Benguet 2600', 'glenda.gonzales@builders.com', NULL, NULL, 'Plumbing design and installation', 'PCAB-320', 'B', '2027-02-14', 'BP-320', 'Baguio', '2027-08-14', 'TIN-320', 'dti_glenda_gonzales.jpg', 'deleted', NULL, 0, NULL, NULL, 'Business relocation', NULL, NULL, NULL, 6, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(72, 221, NULL, NULL, 'Glenda Fernandez Builders', '2024-06-03', 4, 3, NULL, 'Carpentry', 'Outlook Drive, Barangay Outlook, Baguio City, Benguet 2600', 'glenda.fernandez@builders.com', NULL, NULL, 'Custom carpentry and woodwork', 'PCAB-321', 'C', '2027-06-03', 'BP-321', 'Baguio', '2027-12-03', 'TIN-321', 'dti_glenda_fernandez.jpg', 'approved', '2026-02-28 11:20:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(73, 222, NULL, NULL, 'Glenda Mercado Builders', '2023-08-22', 9, 1, NULL, 'Renovation Services', 'Mines View Road, Barangay Mines View, Baguio City, Benguet 2600', 'glenda.mercado@builders.com', NULL, NULL, 'Building renovation and restoration', 'PCAB-322', 'AA', '2027-08-22', 'BP-322', 'Baguio', '2027-02-22', 'TIN-322', 'dti_glenda_mercado.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, NULL, NULL, 'Documentation issues', 7, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(74, 223, NULL, NULL, 'Glenda Ramos Builders', '2022-10-11', 11, 1, NULL, 'Commercial Projects', 'Trancoville, Barangay Trancoville, Baguio City, Benguet 2600', 'glenda.ramos@builders.com', NULL, NULL, 'Commercial building construction', 'PCAB-323', 'AAA', '2028-10-11', 'BP-323', 'Baguio', '2027-04-11', 'TIN-323', 'dti_glenda_ramos.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 14, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(75, 224, NULL, NULL, 'Glenda Reyes Builders II', '2023-01-30', 7, 2, NULL, 'HVAC Systems', 'Pacdal Road, Barangay Pacdal, Baguio City, Benguet 2600', 'glenda.reyes2@builders.com', NULL, NULL, 'Heating and cooling systems', 'PCAB-324', 'B', '2027-01-30', 'BP-324', 'Baguio', '2027-07-30', 'TIN-324', 'dti_glenda_reyes2.jpg', 'approved', '2026-02-12 16:00:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 8, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(76, 225, NULL, NULL, 'Glenda Santos Builders II', '2024-04-19', 5, 3, NULL, 'Masonry Work', 'Inya Road, Barangay Inya, Baguio City, Benguet 2600', 'glenda.santos2@builders.com', NULL, NULL, 'Brick and stone masonry', 'PCAB-325', 'C', '2027-04-19', 'BP-325', 'Baguio', '2027-10-19', 'TIN-325', 'dti_glenda_santos2.jpg', 'deleted', NULL, 0, NULL, NULL, 'Inactive status', NULL, NULL, NULL, 2, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(77, 226, NULL, NULL, 'Glenda Villanueva Builders II', '2023-09-05', 10, 1, NULL, 'Structural Work', 'Kisad Road, Barangay Kisad, Baguio City, Benguet 2600', 'glenda.villanueva2@builders.com', NULL, NULL, 'Structural engineering and construction', 'PCAB-326', 'AA', '2027-09-05', 'BP-326', 'Baguio', '2027-03-05', 'TIN-326', 'dti_glenda_villanueva2.jpg', 'approved', '2026-01-30 12:30:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 13, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(78, 227, NULL, NULL, 'Glenda Gonzales Builders II', '2022-06-14', 13, 1, NULL, 'Bridge Construction', 'Chanum Road, Barangay Chanum, Baguio City, Benguet 2600', 'glenda.gonzales2@builders.com', NULL, NULL, 'Bridge and overpass construction', 'PCAB-327', 'AAA', '2028-06-14', 'BP-327', 'Baguio', '2027-12-14', 'TIN-327', 'dti_glenda_gonzales2.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 19, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(79, 228, NULL, NULL, 'Glenda Fernandez Builders II', '2023-03-27', 8, 2, NULL, 'Welding Services', 'Shuntug Road, Barangay Shuntug, Baguio City, Benguet 2600', 'glenda.fernandez2@builders.com', NULL, NULL, 'Structural steel welding', 'PCAB-328', 'B', '2027-03-27', 'BP-328', 'Baguio', '2027-09-27', 'TIN-328', 'dti_glenda_fernandez2.jpg', 'approved', '2026-02-08 13:45:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 9, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(80, 229, NULL, NULL, 'Glenda Mercado Builders II', '2024-05-06', 6, 3, NULL, 'Concrete Work', 'Loakan Road, Barangay Loakan, Baguio City, Benguet 2600', 'glenda.mercado2@builders.com', NULL, NULL, 'Concrete pouring and finishing', 'PCAB-329', 'C', '2027-05-06', 'BP-329', 'Baguio', '2027-11-06', 'TIN-329', 'dti_glenda_mercado2.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, NULL, NULL, 'Quality concerns', 4, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(81, 230, NULL, NULL, 'Glenda Ramos Builders II', '2023-10-16', 9, 1, NULL, 'Finishing Works', 'Pinsao Road, Barangay Pinsao, Baguio City, Benguet 2600', 'glenda.ramos2@builders.com', NULL, NULL, 'Interior finishing and details', 'PCAB-330', 'AA', '2027-10-16', 'BP-330', 'Baguio', '2027-04-16', 'TIN-330', 'dti_glenda_ramos2.jpg', 'approved', '2026-02-18 10:15:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 11, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(82, 231, NULL, NULL, 'Glenda Reyes Builders III', '2022-11-23', 12, 1, NULL, 'Restoration Work', 'Mines View Road, Barangay Mines View, Baguio City, Benguet 2600', 'glenda.reyes3@builders.com', NULL, NULL, 'Heritage restoration and preservation', 'PCAB-331', 'AAA', '2028-11-23', 'BP-331', 'Baguio', '2027-05-23', 'TIN-331', 'dti_glenda_reyes3.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 17, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(83, 232, NULL, NULL, 'Glenda Santos Builders III', '2023-04-02', 7, 2, NULL, 'Insulation Work', 'Trancoville, Barangay Trancoville, Baguio City, Benguet 2600', 'glenda.santos3@builders.com', NULL, NULL, 'Thermal and acoustic insulation', 'PCAB-332', 'B', '2027-04-02', 'BP-332', 'Baguio', '2027-10-02', 'TIN-332', 'dti_glenda_santos3.jpg', 'approved', '2026-02-22 14:50:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 5, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(84, 233, NULL, NULL, 'Glenda Villanueva Builders III', '2024-07-11', 5, 3, NULL, 'Demolition Services', 'Pacdal Road, Barangay Pacdal, Baguio City, Benguet 2600', 'glenda.villanueva3@builders.com', NULL, NULL, 'Safe demolition and site clearing', 'PCAB-333', 'C', '2027-07-11', 'BP-333', 'Baguio', '2027-01-11', 'TIN-333', 'dti_glenda_villanueva3.jpg', 'deleted', NULL, 0, NULL, NULL, 'Permit expiration', NULL, NULL, NULL, 1, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(85, 234, NULL, NULL, 'Glenda Gonzales Builders III', '2023-12-08', 8, 1, NULL, 'Renovation', 'Inya Road, Barangay Inya, Baguio City, Benguet 2600', 'glenda.gonzales3@builders.com', NULL, NULL, 'Complete renovation services', 'PCAB-334', 'AA', '2027-12-08', 'BP-334', 'Baguio', '2027-06-08', 'TIN-334', 'dti_glenda_gonzales3.jpg', 'approved', '2026-01-28 15:30:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 10, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(86, 235, NULL, NULL, 'Glenda Fernandez Builders III', '2022-08-19', 11, 1, NULL, 'Expansion Projects', 'Kisad Road, Barangay Kisad, Baguio City, Benguet 2600', 'glenda.fernandez3@builders.com', NULL, NULL, 'Building expansion and extension', 'PCAB-335', 'AAA', '2028-08-19', 'BP-335', 'Baguio', '2027-02-19', 'TIN-335', 'dti_glenda_fernandez3.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, NULL, NULL, 'Safety violations', 8, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(87, 236, NULL, NULL, 'Glenda Mercado Builders III', '2023-06-27', 9, 2, NULL, 'Facade Work', 'Chanum Road, Barangay Chanum, Baguio City, Benguet 2600', 'glenda.mercado3@builders.com', NULL, NULL, 'Building facade and cladding', 'PCAB-336', 'B', '2027-06-27', 'BP-336', 'Baguio', '2027-12-27', 'TIN-336', 'dti_glenda_mercado3.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 6, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(88, 237, NULL, NULL, 'Glenda Ramos Builders III', '2024-02-05', 6, 3, NULL, 'Landscaping', 'Shuntug Road, Barangay Shuntug, Baguio City, Benguet 2600', 'glenda.ramos3@builders.com', NULL, NULL, 'Landscape design and maintenance', 'PCAB-337', 'C', '2027-02-05', 'BP-337', 'Baguio', '2027-08-05', 'TIN-337', 'dti_glenda_ramos3.jpg', 'approved', '2026-02-25 11:00:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 4, '2026-03-12 00:00:00', '2026-03-14 17:12:05'),
(89, 238, NULL, NULL, 'Glenda Reyes Builders IV', '2023-09-14', 8, 1, NULL, 'Maintenance Services', 'Loakan Road, Barangay Loakan, Baguio City, Benguet 2600', 'glenda.reyes4@builders.com', NULL, NULL, 'Building maintenance and repairs', 'PCAB-338', 'AA', '2027-09-14', 'BP-338', 'Baguio', '2027-03-14', 'TIN-338', 'dti_glenda_reyes4.jpg', 'deleted', NULL, 0, NULL, NULL, 'Voluntary withdrawal', NULL, NULL, NULL, 7, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(90, 239, NULL, NULL, 'Glenda Santos Builders IV', '2022-10-03', 13, 1, NULL, 'Infrastructure', 'Pinsao Road, Barangay Pinsao, Baguio City, Benguet 2600', 'glenda.santos4@builders.com', NULL, NULL, 'Infrastructure development', 'PCAB-339', 'AAAA', '2028-10-03', 'BP-339', 'Baguio', '2027-04-03', 'TIN-339', 'dti_glenda_santos4.jpg', 'approved', '2026-01-05 09:20:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 20, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(91, 240, NULL, NULL, 'Glenda Villanueva Builders IV', '2023-05-21', 7, 2, NULL, 'Plumbing', 'Asin Road, Barangay Asin, Baguio City, Benguet 2600', 'glenda.villanueva4@builders.com', NULL, NULL, 'Plumbing systems and fixtures', 'PCAB-340', 'B', '2027-05-21', 'BP-340', 'Baguio', '2027-11-21', 'TIN-340', 'dti_glenda_villanueva4.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 5, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(92, 241, NULL, NULL, 'Glenda Gonzales Builders IV', '2024-03-10', 5, 3, NULL, 'Tiling Work', 'Outlook Drive, Barangay Outlook, Baguio City, Benguet 2600', 'glenda.gonzales4@builders.com', NULL, NULL, 'Ceramic and tile installation', 'PCAB-341', 'C', '2027-03-10', 'BP-341', 'Baguio', '2027-09-10', 'TIN-341', 'dti_glenda_gonzales4.jpg', 'approved', '2026-02-14 16:45:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(93, 242, NULL, NULL, 'Glenda Fernandez Builders IV', '2023-11-07', 9, 1, NULL, 'General Contracting', 'Mines View Road, Barangay Mines View, Baguio City, Benguet 2600', 'glenda.fernandez4@builders.com', NULL, NULL, 'Full-service general contracting', 'PCAB-342', 'AA', '2027-11-07', 'BP-342', 'Baguio', '2027-05-07', 'TIN-342', 'dti_glenda_fernandez4.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, NULL, NULL, 'Compliance issues', 9, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(94, 243, NULL, NULL, 'Glenda Mercado Builders IV', '2022-07-25', 12, 1, NULL, 'Complex Projects', 'Trancoville, Barangay Trancoville, Baguio City, Benguet 2600', 'glenda.mercado4@builders.com', NULL, NULL, 'Complex construction projects', 'PCAB-343', 'AAA', '2028-07-25', 'BP-343', 'Baguio', '2027-01-25', 'TIN-343', 'dti_glenda_mercado4.jpg', 'approved', '2026-02-01 12:15:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 18, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(95, 244, NULL, NULL, 'Glenda Ramos Builders IV', '2023-08-13', 8, 2, NULL, 'Electrical', 'Pacdal Road, Barangay Pacdal, Baguio City, Benguet 2600', 'glenda.ramos4@builders.com', NULL, NULL, 'Electrical systems and wiring', 'PCAB-344', 'B', '2027-08-13', 'BP-344', 'Baguio', '2027-02-13', 'TIN-344', 'dti_glenda_ramos4.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, NULL, NULL, 'RESUBMISSION: sdfsdfsdfsd', 7, '2026-03-12 00:00:00', '2026-03-14 16:23:55'),
(96, 245, NULL, NULL, 'Glenda Reyes Builders V', '2024-01-22', 6, 3, NULL, 'Painting', 'Inya Road, Barangay Inya, Baguio City, Benguet 2600', 'glenda.reyes5@builders.com', NULL, NULL, 'Professional painting services', 'PCAB-345', 'C', '2027-01-22', 'BP-345', 'Baguio', '2027-07-22', 'TIN-345', 'dti_glenda_reyes5.jpg', 'deleted', NULL, 0, NULL, NULL, 'Inactive account', NULL, NULL, NULL, 2, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(97, 246, NULL, NULL, 'Glenda Santos Builders V', '2023-04-30', 10, 1, NULL, 'Residential', 'Kisad Road, Barangay Kisad, Baguio City, Benguet 2600', 'glenda.santos5@builders.com', NULL, NULL, 'Residential construction', 'PCAB-346', 'AA', '2027-04-30', 'BP-346', 'Baguio', '2027-10-30', 'TIN-346', 'dti_glenda_santos5.jpg', 'approved', '2026-01-18 13:30:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 12, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(98, 247, NULL, NULL, 'Glenda Villanueva Builders V', '2022-09-12', 11, 1, NULL, 'Commercial', 'Chanum Road, Barangay Chanum, Baguio City, Benguet 2600', 'glenda.villanueva5@builders.com', NULL, NULL, 'Commercial building construction', 'PCAB-347', 'AAA', '2028-09-12', 'BP-347', 'Baguio', '2027-03-12', 'TIN-347', 'dti_glenda_villanueva5.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, NULL, NULL, 'Unresolved disputes', 14, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(99, 248, NULL, NULL, 'Glenda Gonzales Builders V', '2023-06-08', 8, 2, NULL, 'Mechanical', 'Shuntug Road, Barangay Shuntug, Baguio City, Benguet 2600', 'glenda.gonzales5@builders.com', NULL, NULL, 'Mechanical systems installation', 'PCAB-348', 'B', '2027-06-08', 'BP-348', 'Baguio', '2027-12-08', 'TIN-348', 'dti_glenda_gonzales5.jpg', 'approved', '2026-02-10 10:00:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 6, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(100, 249, NULL, NULL, 'Glenda Fernandez Builders V', '2024-02-19', 5, 3, NULL, 'Carpentry', 'Loakan Road, Barangay Loakan, Baguio City, Benguet 2600', 'glenda.fernandez5@builders.com', NULL, NULL, 'Custom carpentry and woodwork', 'PCAB-349', 'C', '2027-02-19', 'BP-349', 'Baguio', '2027-08-19', 'TIN-349', 'dti_glenda_fernandez5.jpg', 'rejected', NULL, 0, NULL, NULL, NULL, NULL, NULL, 'RESUBMISSION: Blurry dai', 3, '2026-03-12 00:00:00', '2026-03-14 15:14:29'),
(101, 250, NULL, NULL, 'Glenda Mercado Builders V', '2023-10-26', 9, 1, NULL, 'Mixed Services', 'Pinsao Road, Barangay Pinsao, Baguio City, Benguet 2600', 'glenda.mercado5@builders.com', NULL, NULL, 'Mixed construction and services', 'PCAB-350', 'AA', '2027-10-26', 'BP-350', 'Baguio', '2027-04-26', 'TIN-350', 'dti_glenda_mercado5.jpg', 'approved', '2026-02-03 14:20:00', 0, '9999-12-31', 'Nigger', NULL, NULL, NULL, NULL, 11, '2026-03-12 00:00:00', '2026-03-14 17:34:47'),
(102, 281, 'profile_pics/1773526275_logo_company_logo.jpg', 'profile_pics/1773526275_banner_company_banner.jpg', 'Apex Architects and Construction Corporation', '2026-03-15', 29, 6, NULL, 'We are the best', 'Tumaga Por Centro, Agong-ong, Buenavista, Agusan Del Norte 7000', 'tilahe5886@bigonla.com', NULL, NULL, NULL, '2037261991', 'AA', '2027-03-04', '01836327911', 'Abra De Ilog', '2027-03-04', '01836159202', 'contractor_documents/1773526275_dti_sec_dti_sec_registration_photo.jpg', 'approved', '2026-03-14 14:12:27', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-03-14 14:11:44', '2026-03-14 22:12:27'),
(103, 283, 'profile_pics/1773561097_logo_company_logo.jpg', 'profile_pics/1773561097_banner_company_banner.jpg', 'Hakdok Company', '2026-03-15', 24, 6, NULL, 'kbckhdkpbejwvx', 'y, Apayao 7000', 'lopavem379@bigonla.com', NULL, NULL, NULL, '91836282', 'C', '2027-03-04', '01038463729', 'Adams', '2027-03-04', '9293746361', 'contractor_documents/1773561097_dti_sec_dti_sec_registration_photo.jpg', 'pending', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-03-14 23:52:02', '2026-03-14 23:52:02'),
(104, 282, 'profile_pics/1773666806_logo_company_logo.jpg', 'profile_pics/1773666806_banner_company_banner.jpg', 'Predator Architects and Construction', '2026-03-16', 32, 1, NULL, 'AIFISHVIODRG', 'TURCYVGBHNJ, Cerrudo, Banga, Aklan 700', 'viwopi4272@indevgo.com', NULL, NULL, NULL, '4564357567', 'A', '2027-03-04', '457567456847', 'Abulug', '2027-03-04', '565679673457', 'contractor_documents/1773666806_dti_sec_dti_sec_registration_photo.jpg', 'rejected', '2026-03-16 07:05:54', 1, NULL, NULL, NULL, NULL, NULL, 'gfevbifnakrf', 0, '2026-03-16 05:17:07', '2026-03-16 19:45:14');

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
  `deletion_scheduled_at` timestamp NULL DEFAULT NULL,
  `deactivation_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contractor_staff`
--

INSERT INTO `contractor_staff` (`staff_id`, `contractor_id`, `owner_id`, `company_role`, `role_if_others`, `company_role_before`, `is_active`, `is_suspended`, `suspension_until`, `suspension_reason`, `deletion_reason`, `deletion_scheduled_at`, `deactivation_reason`, `created_at`) VALUES
(18, 52, 251, 'manager', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(19, 53, 252, 'engineer', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(20, 54, 253, 'architect', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(21, 55, 254, 'representative', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(22, 56, 255, 'others', 'Site Supervisor', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(23, 57, 256, 'manager', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(24, 58, 257, 'engineer', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(25, 59, 258, 'architect', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(26, 60, 259, 'representative', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(27, 61, 260, 'others', 'Quality Assurance', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(28, 62, 261, 'manager', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(29, 63, 262, 'engineer', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(30, 64, 263, 'architect', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(31, 65, 264, 'representative', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(32, 66, 265, 'others', 'Safety Officer', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(33, 67, 266, 'manager', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(34, 68, 267, 'engineer', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(35, 69, 268, 'architect', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(36, 70, 269, 'representative', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(37, 71, 270, 'others', 'Procurement Officer', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(38, 72, 271, 'manager', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(39, 73, 272, 'engineer', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(40, 74, 273, 'architect', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(41, 75, 274, 'representative', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(42, 76, 275, 'others', 'Logistics Coordinator', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(43, 77, 276, 'manager', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(44, 78, 277, 'engineer', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(45, 79, 278, 'architect', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(46, 80, 279, 'representative', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(47, 81, 280, 'others', 'Health & Safety Manager', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00'),
(48, 102, 281, 'manager', NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-14 14:11:44'),
(49, 102, 101, 'representative', NULL, NULL, 0, 0, NULL, NULL, 'just beccause', NULL, NULL, '2026-03-14 16:05:39'),
(51, 102, 101, 'representative', NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-15 09:18:55'),
(52, 102, 282, 'engineer', NULL, 'architect', 0, 0, NULL, NULL, 'hgfhf', NULL, NULL, '2026-03-15 23:37:45'),
(53, 102, 282, 'engineer', NULL, NULL, 0, 0, NULL, NULL, 'Removed by company owner', NULL, NULL, '2026-03-16 02:26:30'),
(54, 102, 282, 'engineer', NULL, NULL, 0, 0, NULL, NULL, 'Removed by company owner', NULL, NULL, '2026-03-16 02:40:52'),
(55, 102, 282, 'manager', NULL, NULL, 0, 0, NULL, NULL, 'Removed by company owner', NULL, NULL, '2026-03-16 02:51:51'),
(56, 104, 282, 'manager', NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-16 05:17:07'),
(57, 102, 282, 'engineer', NULL, NULL, 0, 0, NULL, NULL, 'srextcfvgbhn', NULL, NULL, '2026-03-16 07:17:51'),
(58, 102, 282, 'others', 'Eat Bulaga', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-16 07:35:24'),
(59, 102, 205, 'manager', NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-03-16 07:35:28');

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
  `is_admin_conversation` tinyint(1) NOT NULL DEFAULT 0,
  `contractor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`conversation_id`, `sender_id`, `receiver_id`, `is_suspended`, `no_suspends`, `reason`, `suspended_until`, `status`, `created_at`, `updated_at`, `is_admin_conversation`, `contractor_id`) VALUES
(102000001, 3031, 3031, 0, 0, NULL, NULL, 'active', '2026-03-14 18:59:26', '2026-03-14 18:59:26', 1, 102),
(3032003032, 3032, 3032, 0, 0, NULL, NULL, 'active', '2026-03-14 19:06:11', '2026-03-14 19:06:11', 1, NULL);

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
  `dispute_type` enum('Payment','Delay','Quality','Others','Halt','Terminate') NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `if_others_distype` varchar(255) DEFAULT NULL,
  `dispute_desc` text NOT NULL,
  `dispute_status` enum('open','under_review','resolved','closed','cancelled') DEFAULT 'open',
  `admin_action` varchar(100) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `requested_action` text NOT NULL DEFAULT '',
  `admin_response` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `disputes`
--

INSERT INTO `disputes` (`dispute_id`, `project_id`, `raised_by_user_id`, `against_user_id`, `milestone_id`, `milestone_item_id`, `dispute_type`, `title`, `if_others_distype`, `dispute_desc`, `dispute_status`, `admin_action`, `reason`, `requested_action`, `admin_response`, `created_at`, `resolved_at`) VALUES
(1, 2003, 3007, 3003, 3, 9, 'Halt', 'Foundation Work Quality Dispute', NULL, 'The foundation and base slab work was rejected due to alleged quality issues. However, the contractor believes the work meets all specifications and industry standards. The rejection appears to be based on subjective assessment rather than objective measurements. We request a third-party inspection to verify the quality of work completed.', 'under_review', NULL, NULL, 'Request independent quality inspection and reconsideration of rejection', NULL, '2026-03-12 12:00:00', NULL),
(2, 2001, 3001, 3001, 1, 1, 'Halt', 'Payment Rejection - Check Number Discrepancy', NULL, 'Payment for site preparation and excavation work was rejected citing check number not matching records. The contractor submitted payment with check number CHK-2001-001 which was issued by the bank and is valid. The property owner appears to have incorrect records. We have bank confirmation of the check validity and request immediate payment approval.', 'resolved', 'Warned', NULL, 'Provide bank verification documents and approve payment', 'hhhjhjhjhhjh', '2026-04-15 10:30:00', '2026-03-14 08:36:20'),
(3, 2002, 3004, 3004, 2, 5, 'Halt', 'Cash Payment Documentation Dispute', NULL, 'The cash payment for demolition and cleanup work was rejected for lack of proper documentation. However, the contractor provided all required receipts and documentation at the time of payment. The property owner is requesting additional documentation that was not part of the original agreement. This appears to be an unreasonable demand and delays project completion.', 'under_review', NULL, NULL, 'Accept submitted documentation or specify exact requirements needed', NULL, '2026-01-20 11:00:00', NULL),
(6, 2001, 1018, 1019, NULL, NULL, 'Delay', 'Project Delay Complaint', NULL, 'Contractor has missed the agreed milestone deadline.', 'open', NULL, NULL, '', NULL, '2026-03-14 16:41:58', NULL),
(7, 2002, 1020, 1021, NULL, NULL, 'Quality', 'Poor Work Quality', NULL, 'Submitted work does not meet the agreed requirements.', 'under_review', NULL, NULL, '', NULL, '2026-03-14 16:41:58', NULL),
(8, 2003, 1022, 1023, NULL, NULL, 'Payment', 'Payment Dispute', NULL, 'Client has not released the milestone payment.', 'open', NULL, NULL, '', NULL, '2026-03-14 16:41:58', NULL),
(9, 2004, 1024, 1025, NULL, NULL, 'Halt', 'Request to Halt Project', NULL, 'Repeated missed deadlines require the project to be halted.', 'under_review', NULL, NULL, '', NULL, '2026-03-14 16:41:58', NULL),
(10, 2005, 1026, 1027, NULL, NULL, 'Others', 'Contract Violation', 'Unauthorized subcontracting', 'Contractor hired another developer without permission.', 'open', NULL, NULL, '', NULL, '2026-03-14 16:41:58', NULL),
(13, 2026, 3032, 3031, 9, 31, 'Halt', NULL, NULL, 'Bdudbyvjvtdugycicydyv', 'resolved', 'Terminated', '', '', 'niggsrrffffffffy', '2026-03-14 16:31:36', '2026-03-14 16:32:57');

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

--
-- Dumping data for table `item_files`
--

INSERT INTO `item_files` (`file_id`, `item_id`, `file_path`) VALUES
(1, 31, 'milestone_items/jeYnPeGcXVmZrvALZ9I26yJuYlayLlEO46tnyo9z.jpg');

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
(92, 102000001, 0, 'nigger', 0, 1, 'System: Suspicious Keyword Detected', '2026-03-14 18:59:26', '2026-03-14 18:59:26'),
(93, 102000001, 0, 'hi', 0, 0, NULL, '2026-03-14 18:59:39', '2026-03-14 18:59:39'),
(94, 102000001, 1, 'hu', 1, 0, NULL, '2026-03-14 19:05:15', '2026-03-14 19:05:37'),
(95, 102000001, 0, 'hello grl', 0, 0, NULL, '2026-03-14 19:05:28', '2026-03-14 19:05:28'),
(96, 3032003032, 0, 'HELLOOOOO', 1, 0, NULL, '2026-03-14 19:06:11', '2026-03-14 19:06:24'),
(97, 3032003032, 1, 'Hiiiiiiiiiiiiiiii', 0, 0, NULL, '2026-03-14 19:06:30', '2026-03-14 19:06:30'),
(101, 3032003032, 1, 'Hui', 0, 0, NULL, '2026-03-14 19:12:36', '2026-03-14 19:12:36'),
(102, 102000001, 1, 'hui', 1, 0, NULL, '2026-03-14 19:12:51', '2026-03-14 19:13:06'),
(103, 3032003032, 1, 'Woiii', 0, 0, NULL, '2026-03-14 19:13:17', '2026-03-14 19:13:17');

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

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_03_16_000001_change_activity_type_to_varchar', 1),
(2, '2026_03_17_000001_add_account_management_columns_to_users', 2);

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
(1, 2001, 52, 1, 'Foundation & Structural Work', 'Foundation laying and structural framework completion', 'in_progress', NULL, '2026-03-15 00:00:00', '2026-06-15 00:00:00', NULL, NULL, 'approved', NULL, '2026-03-12 16:02:27', '2026-03-12 16:02:27'),
(2, 2002, 55, 2, 'Office Renovation & Fit-out', 'Complete office renovation and interior fit-out', 'completed', NULL, '2026-01-10 00:00:00', '2026-03-10 00:00:00', NULL, NULL, 'approved', NULL, '2026-03-12 16:02:27', '2026-03-12 16:02:27'),
(3, 2003, 58, 3, 'Warehouse Construction Phase 1', 'First phase of warehouse construction', '', NULL, '2026-02-01 00:00:00', '2026-05-01 00:00:00', NULL, NULL, 'approved', NULL, '2026-03-12 16:02:27', '2026-03-12 16:02:27'),
(4, 2004, 62, 4, 'Agricultural Facility Setup', 'Setup of agricultural storage facility', '', NULL, '2026-02-15 00:00:00', '2026-04-15 00:00:00', NULL, NULL, 'approved', NULL, '2026-03-12 16:02:27', '2026-03-12 16:02:27'),
(5, 2005, 66, 5, 'Subdivision Development Phase 1', 'Development of first phase with 25 units', 'in_progress', NULL, '2026-03-20 00:00:00', '2026-09-20 00:00:00', NULL, NULL, 'approved', NULL, '2026-03-12 16:02:27', '2026-03-14 08:13:11'),
(6, 2021, 67, 6, 'Foundation & Structural Work', 'Foundation laying and structural framework completion', 'in_progress', NULL, '2026-03-15 00:00:00', '2026-06-15 00:00:00', NULL, NULL, 'approved', NULL, '2026-03-12 16:02:38', '2026-03-12 16:02:38'),
(7, 2022, 70, 7, 'Retail Center Construction', 'Construction of retail center with multiple shops', 'in_progress', NULL, '2026-03-10 00:00:00', '2026-07-10 00:00:00', NULL, NULL, 'approved', NULL, '2026-03-12 16:02:38', '2026-03-12 16:02:38'),
(8, 2024, 75, 8, 'Office Building Development', 'Development of modern office building', 'in_progress', NULL, '2026-03-18 00:00:00', '2026-08-18 00:00:00', NULL, NULL, 'approved', NULL, '2026-03-12 16:02:38', '2026-03-12 16:02:38'),
(9, 2026, 102, 9, 'Tahe', 'Tahe', 'in_progress', NULL, '2026-03-15 00:00:00', '2026-03-31 23:59:59', NULL, NULL, 'approved', NULL, '2026-03-14 15:53:34', '2026-03-15 00:30:20');

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
(4, 1, 4, 25.00, 'Ground Floor Completion', 'Ground floor slab and finishing', 1500000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-06-15 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
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
(30, 8, 5, 20.00, 'Final Finishing & Handover', 'Final finishing and project handover', 1900000.00, NULL, 0.00, 'not_started', NULL, NULL, '2026-08-18 00:00:00', NULL, 0, 0, NULL, NULL, NULL),
(31, 9, 1, 100.00, 'gdoska', 'sjakskavs', 100000.00, NULL, 0.00, 'cancelled', '2026-03-15 00:00:00', 'in_progress', '2026-03-31 23:59:59', NULL, 0, 0, NULL, NULL, NULL);

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
(7, 14, 2005, 255, 64, 3500000.00, 'bank_transfer', 'TXN-2005-001', 'receipt_2005_001.jpg', '2026-04-20', 'deleted', 'asdadasdasdadasdadas', '2026-03-14 19:36:49'),
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
(1, 1001, 'shane_owner', 'gggg', 'Project Alert', 0, 'App', 'high', 'targeted', NULL, 'admin_targeted_1773468093_1001', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-13 22:01:33'),
(2, 3001, 'Your project post (ID #2001) has been hidden by an administrator. Reason: pwewow', 'Your Project Post Has Been Hidden', 'Admin Announcement', 0, 'App', 'high', 'project', 2001, NULL, NULL, '2026-03-13 22:11:57'),
(3, 3001, 'Your project post (ID #2001) is visible again after admin review. Note: Restored by admin after moderation review', 'Your Project Post Has Been Restored', 'Admin Announcement', 0, 'App', 'high', 'project', 2001, NULL, NULL, '2026-03-13 22:12:02'),
(4, 1045, 'Your property owner account requires resubmission of documents. Reason: Blurred photo. Please log in and resubmit.', 'Account Verification — Resubmission Required', 'Project Alert', 0, 'App', 'high', NULL, NULL, NULL, '{\"screen\":\"Home\",\"params\":[],\"notification_sub_type\":\"project_update\"}', '2026-03-14 08:28:14'),
(5, 3001, 'hhhjhjhjhhjh', 'Official Warning from Admin', 'Admin Announcement', 0, 'App', 'high', 'dispute', 2, NULL, NULL, '2026-03-14 08:36:16'),
(6, 3002, 'A moderation warning has been issued on your account. Reason: jkjkkjknknknknknknk', 'Official Warning from Admin', 'Admin Announcement', 0, 'App', 'high', NULL, NULL, NULL, NULL, '2026-03-14 08:37:01'),
(7, 3032, 'Your project post \"khdkydjgdjttd\" has been approved and is now visible to contractors.', 'Project Post Approved', 'Project Alert', 1, 'App', 'high', 'project', 2026, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":2026},\"notification_sub_type\":\"project_update\"}', '2026-03-14 15:50:43'),
(8, 3032, 'A contractor has submitted a bid for \"khdkydjgdjttd\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 76, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":2026,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-03-14 15:52:06'),
(9, 3031, 'Your bid for \"khdkydjgdjttd\" has been accepted!', 'Bid Accepted', 'Bid Status', 1, 'App', 'high', 'project', 2026, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":2026},\"notification_sub_type\":\"bid_accepted\"}', '2026-03-14 15:52:37'),
(10, 3032, 'Contractor submitted a milestone plan for \"khdkydjgdjttd\". Please review.', 'Milestone Submitted', 'Milestone Update', 1, 'App', 'normal', 'milestone', 9, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":2026,\"tab\":\"milestones\"},\"notification_sub_type\":\"milestone_submitted\"}', '2026-03-14 15:53:34'),
(11, 3031, 'Your milestone \"Tahe\" has been approved by the owner.', 'Milestone Approved', 'Milestone Update', 1, 'App', 'normal', 'milestone', 9, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":2026,\"tab\":\"milestones\"},\"notification_sub_type\":\"milestone_approved\"}', '2026-03-14 15:54:05'),
(12, 1001, 'You have been invited to join Apex Architects and Construction Corporation as representative. Please accept or decline the invitation.', 'Company Staff Invitation', 'Project Alert', 0, 'App', 'normal', 'contractor_staff', 49, NULL, '{\"screen\":\"StaffInvitations\",\"staff_id\":49,\"notification_sub_type\":\"staff_invitation\"}', '2026-03-14 16:05:39'),
(13, 3031, 'A Halt dispute has been filed against you on \"khdkydjgdjttd\".', 'Dispute Filed', 'Dispute Update', 1, 'App', 'critical', 'dispute', 11, NULL, '{\"screen\":\"DisputeDetails\",\"params\":{\"disputeId\":11},\"notification_sub_type\":\"dispute_opened\"}', '2026-03-14 16:25:44'),
(14, 3031, 'A Halt dispute has been filed against you on \"khdkydjgdjttd\".', 'Dispute Filed', 'Dispute Update', 1, 'App', 'critical', 'dispute', 12, NULL, '{\"screen\":\"DisputeDetails\",\"params\":{\"disputeId\":12},\"notification_sub_type\":\"dispute_opened\"}', '2026-03-14 16:27:42'),
(15, 3031, 'A Halt dispute has been filed against you on \"khdkydjgdjttd\".', 'Dispute Filed', 'Dispute Update', 1, 'App', 'critical', 'dispute', 13, NULL, '{\"screen\":\"DisputeDetails\",\"params\":{\"disputeId\":13},\"notification_sub_type\":\"dispute_opened\"}', '2026-03-14 16:31:36'),
(16, 3033, 'Your property owner account requires resubmission of documents. Reason: Malabo kasi siya so it\'s not gviing. Please log in and resubmit.', 'Account Verification — Resubmission Required', 'Project Alert', 0, 'App', 'high', NULL, NULL, NULL, '{\"screen\":\"Home\",\"params\":[],\"notification_sub_type\":\"project_update\"}', '2026-03-14 23:09:40'),
(17, 3033, 'Your property owner verification documents have been resubmitted and are now pending admin review.', 'Documents Resubmitted', 'Project Alert', 0, 'App', 'high', NULL, NULL, NULL, '{\"screen\":\"Home\",\"params\":[],\"notification_sub_type\":\"project_update\"}', '2026-03-14 23:31:50'),
(18, 3033, 'Your property owner account has been verified and approved. You can now access all platform features.', 'Account Verified', 'Project Alert', 0, 'App', 'high', NULL, NULL, NULL, '{\"screen\":\"Home\",\"params\":[],\"notification_sub_type\":\"project_update\"}', '2026-03-14 23:32:57'),
(19, 1001, 'Your invitation to join Apex Architects and Construction Corporation has been cancelled. Reason: just beccause', 'Invitation Cancelled', 'Project Alert', 0, 'App', 'normal', 'contractor_staff', 49, NULL, '{\"screen\":\"Dashboard\",\"notification_sub_type\":\"staff_invitation_cancelled\"}', '2026-03-15 09:18:24'),
(20, 1001, 'You have been invited to join Apex Architects and Construction Corporation as representative. Please accept or decline the invitation.', 'Company Staff Invitation', 'Project Alert', 0, 'App', 'normal', 'contractor_staff', 51, NULL, '{\"screen\":\"StaffInvitations\",\"staff_id\":51,\"notification_sub_type\":\"staff_invitation\"}', '2026-03-15 09:18:55'),
(21, 2010, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_2010', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:05'),
(22, 1029, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_1029', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:16'),
(23, 3027, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_3027', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:18'),
(24, 1007, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_1007', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:20'),
(25, 2038, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_2038', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:21'),
(26, 2012, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_2012', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:23'),
(27, 1031, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_1031', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:25'),
(28, 1016, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_1016', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:26'),
(29, 3004, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_3004', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:28'),
(30, 1002, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_1002', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:30'),
(31, 3011, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_3011', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:32'),
(32, 2007, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_2007', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:34'),
(33, 1026, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_1026', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:36'),
(34, 1009, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_1009', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:38'),
(35, 2039, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_2039', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:40'),
(36, 1006, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_1006', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:42'),
(37, 3020, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_3020', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:44'),
(38, 2013, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_2013', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:45'),
(39, 1032, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_1032', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:47'),
(40, 2014, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_2014', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:49'),
(41, 1033, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_1033', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:51'),
(42, 2040, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_2040', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:52'),
(43, 3005, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_3005', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:54'),
(44, 3016, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_3016', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:56'),
(45, 2001, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_2001', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:25:58'),
(46, 2041, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_2041', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:26:00'),
(47, 3006, 'Here we go', 'Testing', 'Project Alert', 0, 'App', 'high', 'announcement', NULL, 'admin_announcement_1773599105_3006', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:26:03'),
(48, 3031, 'Hello there', 'are you there?', 'Project Alert', 1, 'App', 'high', 'targeted', NULL, 'admin_targeted_1773599256_3031', '{\"screen\":\"Announcements\",\"notification_sub_type\":\"admin_announcement\"}', '2026-03-15 10:27:36'),
(49, 3001, 'A contractor has submitted a bid for \"Modern Residential Complex\".', 'New Bid Received', 'Bid Status', 0, 'App', 'normal', 'bid', 77, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":2001,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-03-15 22:22:33'),
(50, 3006, 'A contractor has submitted a bid for \"Luxury Residential Mansion\".', 'New Bid Received', 'Bid Status', 0, 'App', 'normal', 'bid', 78, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":2006,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-03-15 22:31:21'),
(51, 3008, 'A contractor has submitted a bid for \"Residential Condo Tower\".', 'New Bid Received', 'Bid Status', 0, 'App', 'normal', 'bid', 79, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":2008,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-03-15 22:42:07'),
(52, 3032, 'Your project post \"jhkjgbjnkml,.\" has been approved and is now visible to contractors.', 'Project Post Approved', 'Project Alert', 1, 'App', 'high', 'project', 2027, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":2027},\"notification_sub_type\":\"project_update\"}', '2026-03-15 23:07:48'),
(53, 3032, 'A contractor has submitted a bid for \"jhkjgbjnkml,.\".', 'New Bid Received', 'Bid Status', 1, 'App', 'normal', 'bid', 80, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":2027,\"tab\":\"bids\"},\"notification_sub_type\":\"bid_received\"}', '2026-03-15 23:12:09'),
(54, 3031, 'Your bid for \"jhkjgbjnkml,.\" was not accepted.', 'Bid Rejected', 'Bid Status', 1, 'App', 'normal', 'project', 2027, NULL, '{\"screen\":\"ProjectDetails\",\"params\":{\"projectId\":2027},\"notification_sub_type\":\"bid_rejected\"}', '2026-03-15 23:13:57'),
(55, 3032, 'You have been invited to join Apex Architects and Construction Corporation as manager. Please accept or decline the invitation.', 'Company Staff Invitation', 'Project Alert', 1, 'App', 'normal', 'contractor_staff', 52, NULL, '{\"screen\":\"StaffInvitations\",\"staff_id\":52,\"notification_sub_type\":\"staff_invitation\"}', '2026-03-15 23:37:45'),
(56, 3031, 'Jayz Jayz has accepted your invitation to join Apex Architects and Construction Corporation.', 'Invitation Accepted', 'Project Alert', 1, 'App', 'normal', 'contractor_staff', 52, NULL, '{\"screen\":\"CompanyMembers\",\"notification_sub_type\":\"staff_invitation_accepted\"}', '2026-03-15 23:39:15'),
(57, 3032, 'Your access to Apex Architects and Construction Corporation has been suspended. Reason: maybe?', 'Account Suspended', 'Project Alert', 1, 'App', 'high', 'contractor_staff', 52, NULL, '{\"screen\":\"Dashboard\",\"notification_sub_type\":\"staff_suspended\"}', '2026-03-15 23:40:59'),
(58, 3032, 'Your access to Apex Architects and Construction Corporation has been restored.', 'Account Reactivated', 'Project Alert', 1, 'App', 'normal', 'contractor_staff', 52, NULL, '{\"screen\":\"Dashboard\",\"notification_sub_type\":\"staff_reactivated\"}', '2026-03-15 23:52:13'),
(59, 3032, 'Your access to Apex Architects and Construction Corporation has been suspended. Reason: sfwef', 'Account Suspended', 'Project Alert', 1, 'App', 'high', 'contractor_staff', 52, NULL, '{\"screen\":\"Dashboard\",\"notification_sub_type\":\"staff_suspended\"}', '2026-03-15 23:52:34'),
(60, 3032, 'Your access to Apex Architects and Construction Corporation has been restored.', 'Account Reactivated', 'Project Alert', 1, 'App', 'normal', 'contractor_staff', 52, NULL, '{\"screen\":\"Dashboard\",\"notification_sub_type\":\"staff_reactivated\"}', '2026-03-15 23:52:56'),
(61, 3032, 'Your role in Apex Architects and Construction Corporation was changed from engineer to architect. Please accept or decline this change.', 'Role Change Request', 'Team Update', 1, 'App', 'high', 'contractor_staff', 52, NULL, '{\"screen\":\"CompanyMembers\",\"notification_sub_type\":\"team_role_changed\"}', '2026-03-16 00:22:01'),
(62, 3031, 'Jayz Jayz accepted the new role assignment in Apex Architects and Construction Corporation.', 'Role Change Accepted', 'Team Update', 1, 'App', 'normal', 'contractor_staff', 52, NULL, '{\"screen\":\"CompanyMembers\",\"notification_sub_type\":\"staff_invitation_accepted\"}', '2026-03-16 00:22:19'),
(63, 3032, 'Your role in Apex Architects and Construction Corporation was changed from architect to engineer. Please accept or decline this change.', 'Role Change Request', 'Team Update', 1, 'App', 'high', 'contractor_staff', 52, NULL, '{\"screen\":\"CompanyMembers\",\"notification_sub_type\":\"team_role_changed\"}', '2026-03-16 00:22:53'),
(64, 3032, 'Your invitation to join Apex Architects and Construction Corporation has been cancelled. Reason: hgfhf', 'Invitation Cancelled', 'Team Update', 1, 'App', 'normal', 'contractor_staff', 52, NULL, '{\"screen\":\"Dashboard\",\"notification_sub_type\":\"staff_invitation_cancelled\"}', '2026-03-16 00:23:22'),
(65, 3032, 'You have been invited to join Apex Architects and Construction Corporation as engineer. Please accept or decline the invitation.', 'Company Staff Invitation', 'Team Update', 1, 'App', 'normal', 'contractor_staff', 53, NULL, '{\"screen\":\"StaffInvitations\",\"staff_id\":53,\"notification_sub_type\":\"team_invite\"}', '2026-03-16 02:26:30'),
(66, 3031, 'Jayz Jayz has accepted your invitation to join Apex Architects and Construction Corporation.', 'Invitation Accepted', 'Team Update', 0, 'App', 'normal', 'contractor_staff', 53, NULL, '{\"screen\":\"CompanyMembers\",\"notification_sub_type\":\"staff_invitation_accepted\"}', '2026-03-16 02:26:48'),
(67, 3032, 'Your access to Apex Architects and Construction Corporation has been suspended. Reason: yes suspended ka na', 'Account Suspended', 'Team Update', 1, 'App', 'high', 'contractor_staff', 53, NULL, '{\"screen\":\"Dashboard\",\"notification_sub_type\":\"staff_suspended\"}', '2026-03-16 02:29:06'),
(68, 3032, 'Your access to Apex Architects and Construction Corporation has been restored.', 'Account Reactivated', 'Team Update', 0, 'App', 'normal', 'contractor_staff', 53, NULL, '{\"screen\":\"Dashboard\",\"notification_sub_type\":\"staff_reactivated\"}', '2026-03-16 02:30:09'),
(69, 3032, 'You have been removed from Apex Architects and Construction Corporation. Reason: Removed by company owner', 'Removed from Company', 'Team Update', 0, 'App', 'high', 'contractor_staff', 53, NULL, '{\"screen\":\"Dashboard\",\"notification_sub_type\":\"staff_removed\"}', '2026-03-16 02:31:07'),
(70, 3032, 'You have been invited to join Apex Architects and Construction Corporation as engineer. Please accept or decline the invitation.', 'Company Staff Invitation', 'Team Update', 1, 'App', 'normal', 'contractor_staff', 54, NULL, '{\"screen\":\"StaffInvitations\",\"staff_id\":54,\"notification_sub_type\":\"team_invite\"}', '2026-03-16 02:40:52'),
(71, 3031, 'Jayz Jayz has accepted your invitation to join Apex Architects and Construction Corporation.', 'Invitation Accepted', 'Team Update', 0, 'App', 'normal', 'contractor_staff', 54, NULL, '{\"screen\":\"CompanyMembers\",\"notification_sub_type\":\"staff_invitation_accepted\"}', '2026-03-16 02:41:09'),
(72, 3032, 'Your access to Apex Architects and Construction Corporation has been suspended. Reason: dfgdfh', 'Account Suspended', 'Team Update', 0, 'App', 'high', 'contractor_staff', 54, NULL, '{\"screen\":\"Dashboard\",\"notification_sub_type\":\"staff_suspended\"}', '2026-03-16 02:42:14'),
(73, 3032, 'Your access to Apex Architects and Construction Corporation has been restored.', 'Account Reactivated', 'Team Update', 0, 'App', 'normal', 'contractor_staff', 54, NULL, '{\"screen\":\"Dashboard\",\"notification_sub_type\":\"staff_reactivated\"}', '2026-03-16 02:42:47'),
(74, 3032, 'You have been removed from Apex Architects and Construction Corporation. Reason: Removed by company owner', 'Removed from Company', 'Team Update', 0, 'App', 'high', 'contractor_staff', 54, NULL, '{\"screen\":\"Dashboard\",\"notification_sub_type\":\"staff_removed\"}', '2026-03-16 02:44:04'),
(75, 3032, 'You have been invited to join Apex Architects and Construction Corporation as manager. Please accept or decline the invitation.', 'Company Staff Invitation', 'Team Update', 1, 'App', 'normal', 'contractor_staff', 55, NULL, '{\"screen\":\"StaffInvitations\",\"staff_id\":55,\"notification_sub_type\":\"team_invite\"}', '2026-03-16 02:51:51'),
(76, 3031, 'Jayz Jayz has accepted your invitation to join Apex Architects and Construction Corporation.', 'Invitation Accepted', 'Team Update', 0, 'App', 'normal', 'contractor_staff', 55, NULL, '{\"screen\":\"CompanyMembers\",\"notification_sub_type\":\"staff_invitation_accepted\"}', '2026-03-16 02:52:55'),
(77, 3032, 'You have been removed from Apex Architects and Construction Corporation. Reason: Removed by company owner', 'Removed from Company', 'Team Update', 0, 'App', 'high', 'contractor_staff', 55, NULL, '{\"screen\":\"Dashboard\",\"notification_sub_type\":\"staff_removed\"}', '2026-03-16 03:06:10'),
(78, 3032, 'You have been invited to join Apex Architects and Construction Corporation as engineer. Please accept or decline the invitation.', 'Company Staff Invitation', 'Team Update', 0, 'App', 'normal', 'contractor_staff', 57, NULL, '{\"screen\":\"StaffInvitations\",\"staff_id\":57,\"notification_sub_type\":\"team_invite\"}', '2026-03-16 07:17:51'),
(79, 3032, 'Your invitation to join Apex Architects and Construction Corporation has been cancelled. Reason: srextcfvgbhn', 'Invitation Cancelled', 'Team Update', 0, 'App', 'normal', 'contractor_staff', 57, NULL, '{\"screen\":\"Dashboard\",\"notification_sub_type\":\"staff_invitation_cancelled\"}', '2026-03-16 07:34:50'),
(80, 3032, 'You have been invited to join Apex Architects and Construction Corporation as others. Please accept or decline the invitation.', 'Company Staff Invitation', 'Team Update', 1, 'App', 'normal', 'contractor_staff', 58, NULL, '{\"screen\":\"StaffInvitations\",\"staff_id\":58,\"notification_sub_type\":\"team_invite\"}', '2026-03-16 07:35:24'),
(81, 2005, 'You have been invited to join Apex Architects and Construction Corporation as manager. Please accept or decline the invitation.', 'Company Staff Invitation', 'Team Update', 0, 'App', 'normal', 'contractor_staff', 59, NULL, '{\"screen\":\"StaffInvitations\",\"staff_id\":59,\"notification_sub_type\":\"team_invite\"}', '2026-03-16 07:35:28'),
(82, 3031, 'Jayz Jayz has accepted your invitation to join Apex Architects and Construction Corporation.', 'Invitation Accepted', 'Team Update', 0, 'App', 'normal', 'contractor_staff', 58, NULL, '{\"screen\":\"CompanyMembers\",\"notification_sub_type\":\"staff_invitation_accepted\"}', '2026-03-16 07:35:49');

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
(1, 2001, 52, 'downpayment', 6000000.00, 1500000.00, 1, '2026-03-12 16:02:27', '2026-03-12 16:02:27'),
(2, 2002, 55, 'full_payment', 2500000.00, 0.00, 1, '2026-03-12 16:02:27', '2026-03-12 16:02:27'),
(3, 2003, 58, 'downpayment', 9000000.00, 2250000.00, 1, '2026-03-12 16:02:27', '2026-03-12 16:02:27'),
(4, 2004, 62, 'full_payment', 1750000.00, 0.00, 1, '2026-03-12 16:02:27', '2026-03-12 16:02:27'),
(5, 2005, 64, 'downpayment', 17500000.00, 4375000.00, 1, '2026-03-12 16:02:27', '2026-03-12 16:02:27'),
(6, 2021, 67, 'downpayment', 8000000.00, 2000000.00, 1, '2026-03-12 16:02:38', '2026-03-12 16:02:38'),
(7, 2022, 70, 'full_payment', 6000000.00, 0.00, 1, '2026-03-12 16:02:38', '2026-03-12 16:02:38'),
(8, 2024, 75, 'downpayment', 9500000.00, 2375000.00, 1, '2026-03-12 16:02:38', '2026-03-12 16:02:38'),
(9, 2026, 102, 'full_payment', 100000.00, 0.00, 0, '2026-03-14 15:53:34', '2026-03-14 15:53:34');

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
(1, 'App\\Models\\user', 3031, 'mobile-app', '779f550f1050a17236c437645f352d47b13c79e7f78afee9bfae5141c0794ef3', '[\"*\"]', NULL, NULL, '2026-03-14 14:07:46', '2026-03-14 14:07:46'),
(2, 'App\\Models\\user', 3032, 'mobile-app', '5314a779c8fca605951c51bc7995104d5c928c04837a5506d7dd6ae41c8ee6ef', '[\"*\"]', NULL, NULL, '2026-03-14 14:41:01', '2026-03-14 14:41:01'),
(3, 'App\\Models\\user', 3032, 'mobile-app', 'c79ecbf48ea1f065d190d7b41baead9d94300a5360eec48aff1eb86b7fd344d5', '[\"*\"]', NULL, NULL, '2026-03-14 15:20:23', '2026-03-14 15:20:23'),
(4, 'App\\Models\\user', 3031, 'mobile-app', '4bef87d7d77a647bd9e7eb9f5148c1945d31aa991ef96a08669be3e44085f6b6', '[\"*\"]', NULL, NULL, '2026-03-14 15:24:11', '2026-03-14 15:24:11'),
(5, 'App\\Models\\user', 3032, 'mobile-app', '2cea93657485a4744aabecafffba9d697d4a10b92dc623db95eb8692d6d99b18', '[\"*\"]', NULL, NULL, '2026-03-14 15:49:25', '2026-03-14 15:49:25'),
(6, 'App\\Models\\user', 3032, 'mobile-app', '25a100e7e1d2ea99cb9db49472cfa0954572a4b955d8d0aaabe54ec2deed9300', '[\"*\"]', NULL, NULL, '2026-03-14 22:55:55', '2026-03-14 22:55:55'),
(7, 'App\\Models\\user', 3033, 'mobile-app', '5ac5cda97b7d90d0318cb0ffe9bb8729bc661127b904b6c75e3c6d8c63b17ef6', '[\"*\"]', '2026-03-14 23:11:03', NULL, '2026-03-14 23:10:28', '2026-03-14 23:11:03'),
(8, 'App\\Models\\user', 3033, 'mobile-app', '2143b6f55728bacef41d74d3e431a8a77c5bd09ca28c782c5598c2df3a02809e', '[\"*\"]', NULL, NULL, '2026-03-14 23:19:30', '2026-03-14 23:19:30'),
(9, 'App\\Models\\user', 3033, 'mobile-app', 'd56c6254a7c462f556075c1168c25d9dbf69e328ea9c0bb3ade4ac112ceee3a8', '[\"*\"]', NULL, NULL, '2026-03-14 23:21:17', '2026-03-14 23:21:17'),
(10, 'App\\Models\\user', 3033, 'mobile-app', 'd1081dcea5f5d11817ab941587fc055d9b52d22d6831276883732ebc8ae5c118', '[\"*\"]', '2026-03-14 23:31:49', NULL, '2026-03-14 23:31:15', '2026-03-14 23:31:49'),
(11, 'App\\Models\\user', 3033, 'mobile-app', '762eb6fb1eeaf6620bbdbaa89dac1c4a9bca40f62be19ae0ee2648d7c661f675', '[\"*\"]', NULL, NULL, '2026-03-14 23:33:09', '2026-03-14 23:33:09'),
(12, 'App\\Models\\user', 3032, 'mobile-app', 'd4deb6aec5da800aac485c52f5a2497a483125a67fa8dfe1f426c56d5cdf25c0', '[\"*\"]', NULL, NULL, '2026-03-15 08:15:18', '2026-03-15 08:15:18'),
(13, 'App\\Models\\user', 3033, 'mobile-app', '17d00c7ef3e40829e9c7fa746b19fa7be08569a054ee55c8985d556920be016d', '[\"*\"]', NULL, NULL, '2026-03-15 08:23:28', '2026-03-15 08:23:28'),
(14, 'App\\Models\\user', 3031, 'mobile-app', '52423984610954a623509a4b122395adc25e7c5ae5428db8c60f3fa3c0194fcd', '[\"*\"]', NULL, NULL, '2026-03-15 08:24:14', '2026-03-15 08:24:14'),
(15, 'App\\Models\\user', 3031, 'mobile-app', '3225480a6def4dbfbd27300b65eaa8b83a3a2b5feeba921130fd9ae2b98bc32b', '[\"*\"]', NULL, NULL, '2026-03-15 21:32:59', '2026-03-15 21:32:59'),
(16, 'App\\Models\\user', 3031, 'mobile-app', '7ad3a166fb4be13e856cad25d6e51306cb5c01b486aae0edca19c39f1927f808', '[\"*\"]', NULL, NULL, '2026-03-15 22:11:13', '2026-03-15 22:11:13'),
(17, 'App\\Models\\user', 3031, 'mobile-app', '223390e095a93b8edb3362a359871fc790bae19538514b23edd472d09e6f9cf7', '[\"*\"]', NULL, NULL, '2026-03-15 22:21:47', '2026-03-15 22:21:47'),
(18, 'App\\Models\\user', 3032, 'mobile-app', 'ce228c67271afe2d4e665904f1b57f81745955801df32588df447d19e01397ee', '[\"*\"]', NULL, NULL, '2026-03-15 23:04:18', '2026-03-15 23:04:18'),
(19, 'App\\Models\\user', 3031, 'mobile-app', '6f3216ee14dad139828f2943644dd765595dc66424bd585dec5a3b21b8209eee', '[\"*\"]', NULL, NULL, '2026-03-15 23:36:20', '2026-03-15 23:36:20'),
(30, 'App\\Models\\user', 3032, 'mobile-app', 'c3ae253a76e4561998acde3a231a3178ff6189a71ec7a00d05f2a3cfb14d9134', '[\"*\"]', NULL, NULL, '2026-03-16 11:44:49', '2026-03-16 11:44:49');

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
(1, 1, NULL, 52, NULL, 1999.00, 'TXN-SUB-001', '2026-03-01 00:00:00', 1, 0, 'ADMIN-1', '2026-04-01 00:00:00', 'bank_transfer', NULL),
(2, 1, NULL, 55, NULL, 1999.00, 'TXN-SUB-002', '2026-03-02 01:30:00', 1, 0, 'ADMIN-1', '2026-04-02 01:30:00', 'bank_transfer', NULL),
(3, 1, NULL, 58, NULL, 1999.00, 'TXN-SUB-003', '2026-03-03 02:15:00', 1, 0, 'ADMIN-1', '2026-04-03 02:15:00', 'bank_transfer', NULL),
(4, 1, NULL, 62, NULL, 1999.00, 'TXN-SUB-004', '2026-03-04 03:00:00', 1, 0, 'ADMIN-1', '2026-04-04 03:00:00', 'bank_transfer', NULL),
(5, 2, NULL, 64, NULL, 1499.00, 'TXN-SUB-005', '2026-03-05 00:30:00', 1, 0, 'ADMIN-1', '2026-04-05 00:30:00', 'bank_transfer', NULL),
(6, 2, NULL, 67, NULL, 1499.00, 'TXN-SUB-006', '2026-03-06 01:15:00', 1, 0, 'ADMIN-1', '2026-04-06 01:15:00', 'bank_transfer', NULL),
(7, 2, NULL, 70, NULL, 1499.00, 'TXN-SUB-007', '2026-03-07 02:00:00', 1, 0, 'ADMIN-1', '2026-04-07 02:00:00', 'bank_transfer', NULL),
(8, 2, NULL, 75, NULL, 1499.00, 'TXN-SUB-008', '2026-03-08 03:30:00', 1, 0, 'ADMIN-1', '2026-04-08 03:30:00', 'bank_transfer', NULL),
(9, 2, NULL, 78, NULL, 1499.00, 'TXN-SUB-009', '2026-03-09 00:45:00', 1, 0, 'ADMIN-1', '2026-04-09 00:45:00', 'bank_transfer', NULL),
(10, 3, NULL, 81, NULL, 999.00, 'TXN-SUB-010', '2026-03-10 01:00:00', 1, 0, 'ADMIN-1', '2026-04-10 01:00:00', 'bank_transfer', NULL),
(11, 3, NULL, 52, NULL, 999.00, 'TXN-SUB-011', '2026-03-11 02:30:00', 1, 0, 'ADMIN-1', '2026-04-11 02:30:00', 'bank_transfer', NULL),
(12, 3, NULL, 53, NULL, 999.00, 'TXN-SUB-012', '2026-03-12 03:15:00', 1, 0, 'ADMIN-1', '2026-04-12 03:15:00', 'bank_transfer', NULL),
(13, 3, NULL, 54, NULL, 999.00, 'TXN-SUB-013', '2026-03-13 00:30:00', 1, 0, 'ADMIN-1', '2026-04-13 00:30:00', 'bank_transfer', NULL),
(14, 3, NULL, 56, NULL, 999.00, 'TXN-SUB-014', '2026-03-14 01:45:00', 1, 0, 'ADMIN-1', '2026-04-14 01:45:00', 'bank_transfer', NULL),
(15, 3, NULL, 59, NULL, 999.00, 'TXN-SUB-015', '2026-03-15 02:20:00', 1, 0, 'ADMIN-1', '2026-04-15 02:20:00', 'bank_transfer', NULL),
(16, 4, 2006, NULL, 256, 49.00, 'TXN-BOOST-001', '2026-03-05 00:00:00', 1, 0, 'ADMIN-1', '2026-03-12 00:00:00', 'bank_transfer', NULL),
(17, 4, 2007, NULL, 257, 49.00, 'TXN-BOOST-002', '2026-03-06 01:30:00', 1, 0, 'ADMIN-1', '2026-03-13 01:30:00', 'bank_transfer', NULL),
(18, 4, 2008, NULL, 258, 49.00, 'TXN-BOOST-003', '2026-03-07 02:15:00', 1, 0, 'ADMIN-1', '2026-03-14 02:15:00', 'bank_transfer', NULL),
(19, 4, 2009, NULL, 259, 49.00, 'TXN-BOOST-004', '2026-03-08 03:00:00', 1, 0, 'ADMIN-1', '2026-03-15 03:00:00', 'bank_transfer', NULL),
(20, 4, 2010, NULL, 260, 49.00, 'TXN-BOOST-005', '2026-03-09 00:30:00', 1, 0, 'ADMIN-1', '2026-03-16 00:30:00', 'bank_transfer', NULL),
(21, 4, 2011, NULL, 261, 49.00, 'TXN-BOOST-006', '2026-03-10 01:15:00', 1, 0, 'ADMIN-1', '2026-03-17 01:15:00', 'bank_transfer', NULL),
(22, 4, 2012, NULL, 262, 49.00, 'TXN-BOOST-007', '2026-03-11 02:00:00', 1, 0, 'ADMIN-1', '2026-03-18 02:00:00', 'bank_transfer', NULL),
(23, 4, 2013, NULL, 263, 49.00, 'TXN-BOOST-008', '2026-03-12 03:30:00', 1, 0, 'ADMIN-1', '2026-03-19 03:30:00', 'bank_transfer', NULL),
(24, 4, 2014, NULL, 264, 49.00, 'TXN-BOOST-009', '2026-03-13 00:45:00', 1, 0, 'ADMIN-1', '2026-03-20 00:45:00', 'bank_transfer', NULL),
(25, 4, 2015, NULL, 265, 49.00, 'TXN-BOOST-010', '2026-03-14 01:30:00', 1, 0, 'ADMIN-1', '2026-03-21 01:30:00', 'bank_transfer', NULL),
(26, 4, 2016, NULL, 266, 49.00, 'TXN-BOOST-011', '2026-03-15 02:15:00', 1, 0, 'ADMIN-1', '2026-03-22 02:15:00', 'bank_transfer', NULL),
(27, 4, 2017, NULL, 267, 49.00, 'TXN-BOOST-012', '2026-03-16 03:00:00', 1, 0, 'ADMIN-1', '2026-03-23 03:00:00', 'bank_transfer', NULL),
(28, 4, 2018, NULL, 268, 49.00, 'TXN-BOOST-013', '2026-03-17 00:30:00', 1, 0, 'ADMIN-1', '2026-03-24 00:30:00', 'bank_transfer', NULL),
(29, 4, 2019, NULL, 269, 49.00, 'TXN-BOOST-014', '2026-03-18 01:15:00', 1, 0, 'ADMIN-1', '2026-03-25 01:15:00', 'bank_transfer', NULL),
(30, 4, 2020, NULL, 270, 49.00, 'TXN-BOOST-015', '2026-03-19 02:00:00', 1, 0, 'ADMIN-1', '2026-03-26 02:00:00', 'bank_transfer', NULL),
(31, 4, 2023, NULL, 273, 49.00, 'TXN-BOOST-016', '2026-03-20 03:30:00', 1, 0, 'ADMIN-1', '2026-03-27 03:30:00', 'bank_transfer', NULL),
(32, 4, 2025, NULL, 275, 49.00, 'TXN-BOOST-017', '2026-03-21 00:45:00', 1, 0, 'ADMIN-1', '2026-03-28 00:45:00', 'bank_transfer', NULL),
(33, 1, NULL, 65, NULL, 1999.00, 'TXN-SUB-016', '2026-03-16 00:00:00', 1, 0, 'ADMIN-1', '2026-04-16 00:00:00', 'bank_transfer', NULL),
(34, 1, NULL, 68, NULL, 1999.00, 'TXN-SUB-017', '2026-03-17 01:30:00', 1, 0, 'ADMIN-1', '2026-04-17 01:30:00', 'bank_transfer', NULL),
(35, 1, NULL, 71, NULL, 1999.00, 'TXN-SUB-018', '2026-03-18 02:15:00', 1, 0, 'ADMIN-1', '2026-04-18 02:15:00', 'bank_transfer', NULL),
(36, 1, NULL, 74, NULL, 1999.00, 'TXN-SUB-019', '2026-03-19 03:00:00', 1, 0, 'ADMIN-1', '2026-04-19 03:00:00', 'bank_transfer', NULL),
(37, 1, NULL, 77, NULL, 1999.00, 'TXN-SUB-020', '2026-03-20 00:30:00', 1, 0, 'ADMIN-1', '2026-04-20 00:30:00', 'bank_transfer', NULL),
(38, 2, NULL, 60, NULL, 1499.00, 'TXN-SUB-021', '2026-03-21 01:15:00', 1, 0, 'ADMIN-1', '2026-04-21 01:15:00', 'bank_transfer', NULL),
(39, 2, NULL, 63, NULL, 1499.00, 'TXN-SUB-022', '2026-03-22 02:00:00', 1, 0, 'ADMIN-1', '2026-04-22 02:00:00', 'bank_transfer', NULL),
(40, 2, NULL, 66, NULL, 1499.00, 'TXN-SUB-023', '2026-03-23 03:30:00', 1, 0, 'ADMIN-1', '2026-04-23 03:30:00', 'bank_transfer', NULL),
(41, 2, NULL, 69, NULL, 1499.00, 'TXN-SUB-024', '2026-03-24 00:45:00', 1, 0, 'ADMIN-1', '2026-04-24 00:45:00', 'bank_transfer', NULL),
(42, 2, NULL, 72, NULL, 1499.00, 'TXN-SUB-025', '2026-03-25 01:30:00', 1, 0, 'ADMIN-1', '2026-04-25 01:30:00', 'bank_transfer', NULL),
(43, 2, NULL, 76, NULL, 1499.00, 'TXN-SUB-026', '2026-03-26 02:15:00', 1, 0, 'ADMIN-1', '2026-04-26 02:15:00', 'bank_transfer', NULL),
(44, 2, NULL, 79, NULL, 1499.00, 'TXN-SUB-027', '2026-03-27 03:00:00', 1, 0, 'ADMIN-1', '2026-04-27 03:00:00', 'bank_transfer', NULL),
(45, 3, NULL, 57, NULL, 999.00, 'TXN-SUB-028', '2026-03-28 00:30:00', 1, 0, 'ADMIN-1', '2026-04-28 00:30:00', 'bank_transfer', NULL),
(46, 3, NULL, 61, NULL, 999.00, 'TXN-SUB-029', '2026-03-29 01:15:00', 1, 0, 'ADMIN-1', '2026-04-29 01:15:00', 'bank_transfer', NULL),
(47, 3, NULL, 73, NULL, 999.00, 'TXN-SUB-030', '2026-03-30 02:00:00', 1, 0, 'ADMIN-1', '2026-04-30 02:00:00', 'bank_transfer', NULL),
(48, 3, NULL, 80, NULL, 999.00, 'TXN-SUB-031', '2026-03-31 03:30:00', 1, 0, 'ADMIN-1', '2026-05-01 03:30:00', 'bank_transfer', NULL),
(49, 3, NULL, 82, NULL, 999.00, 'TXN-SUB-032', '2026-04-01 00:45:00', 1, 0, 'ADMIN-1', '2026-05-02 00:45:00', 'bank_transfer', NULL),
(50, 3, NULL, 85, NULL, 999.00, 'TXN-SUB-033', '2026-04-02 01:30:00', 1, 0, 'ADMIN-1', '2026-05-03 01:30:00', 'bank_transfer', NULL),
(51, 3, NULL, 88, NULL, 999.00, 'TXN-SUB-034', '2026-04-03 02:15:00', 1, 0, 'ADMIN-1', '2026-05-04 02:15:00', 'bank_transfer', NULL),
(52, 3, NULL, 91, NULL, 999.00, 'TXN-SUB-035', '2026-04-04 03:00:00', 1, 0, 'ADMIN-1', '2026-05-05 03:00:00', 'bank_transfer', NULL),
(53, 3, NULL, 94, NULL, 999.00, 'TXN-SUB-036', '2026-04-05 00:30:00', 1, 0, 'ADMIN-1', '2026-05-06 00:30:00', 'bank_transfer', NULL),
(54, 3, NULL, 97, NULL, 999.00, 'TXN-SUB-037', '2026-04-06 01:15:00', 1, 0, 'ADMIN-1', '2026-05-07 01:15:00', 'bank_transfer', NULL),
(55, 3, NULL, 100, NULL, 999.00, 'TXN-SUB-038', '2026-04-07 02:00:00', 0, 1, 'ADMIN-1', '2026-05-08 02:00:00', 'bank_transfer', 'asdasdadsdsdsd'),
(56, 3, NULL, 102, NULL, 999.00, 'cs_30adfe7a6cf6c43af1cbcfed', '2026-03-15 22:39:12', 1, 0, NULL, '2026-04-15 22:39:12', 'PayMongo', NULL);

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
  `admin_action` varchar(100) DEFAULT NULL,
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
(1, 1, 3001, 'Site preparation and excavation completed as per schedule', 'approved', NULL, '2026-04-15 10:30:00', '2026-04-15 14:00:00'),
(2, 2, 3001, 'Foundation laying in progress, 60% complete', 'submitted', NULL, '2026-05-10 09:15:00', NULL),
(3, 5, 3002, 'Demolition and cleanup completed successfully', 'approved', NULL, '2026-01-20 11:00:00', '2026-01-20 15:30:00'),
(4, 6, 3002, 'Electrical and plumbing systems installed and tested', 'approved', NULL, '2026-02-15 08:45:00', '2026-02-15 16:20:00'),
(5, 7, 3002, 'Interior finishing and painting completed', 'approved', NULL, '2026-03-10 10:00:00', '2026-03-10 17:00:00'),
(6, 8, 3003, 'Site preparation and leveling completed', 'approved', NULL, '2026-02-15 09:30:00', '2026-02-15 13:45:00'),
(7, 9, 3003, 'Foundation and base slab work in progress, 50% complete', 'submitted', NULL, '2026-03-15 10:15:00', NULL),
(8, 12, 3004, 'Site preparation and foundation completed', 'approved', NULL, '2026-03-01 08:00:00', '2026-03-01 12:30:00'),
(9, 14, 3005, 'Site development and infrastructure completed', 'approved', NULL, '2026-04-20 09:00:00', '2026-04-20 14:15:00'),
(10, 15, 3005, 'Foundation and structural work for units 1-5 in progress, 70% complete', 'submitted', NULL, '2026-05-20 10:30:00', NULL),
(11, 16, 3005, 'Foundation and structural work for units 6-15 in progress, 40% complete', 'submitted', NULL, '2026-06-15 11:00:00', NULL),
(12, 19, 3021, 'Site preparation and excavation completed', 'approved', NULL, '2026-04-15 09:45:00', '2026-04-15 13:20:00'),
(13, 20, 3021, 'Foundation laying in progress, 55% complete', 'submitted', NULL, '2026-05-15 10:00:00', NULL),
(14, 23, 3022, 'Site preparation and foundation work completed', 'approved', NULL, '2026-04-10 08:30:00', '2026-04-10 12:45:00'),
(15, 24, 3022, 'Structural and walls construction in progress, 65% complete', 'submitted', NULL, '2026-05-20 09:15:00', NULL),
(16, 26, 3024, 'Site development and infrastructure completed', 'approved', NULL, '2026-04-18 10:00:00', '2026-04-18 14:30:00'),
(17, 27, 3024, 'Foundation and ground floor construction in progress, 50% complete', 'submitted', NULL, '2026-05-18 09:30:00', NULL),
(18, 28, 3024, 'Upper floors and structural work in progress, 35% complete', 'submitted', NULL, '2026-06-18 10:45:00', NULL),
(19, 9, 3003, 'Foundation and base slab work - REJECTED due to quality issues', 'rejected', NULL, '2026-03-10 08:00:00', '2026-03-12 11:30:00');

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
(2001, 2001, 'Modern Residential Complex', 'Construction of a 3-storey residential building with modern amenities', 'Tetuan, Poblacion, Mankayan, Benguet 2600', 5000000.00, 7000000.00, 500, 400, 'Residential', 1, NULL, 60, 'open', NULL, NULL, NULL, 52, 0, NULL),
(2002, 2002, 'Commercial Office Space', 'Renovation and fit-out of commercial office space', 'Magsaysay, Barangay Poblacion, Baguio City, Benguet 2600', 2000000.00, 3000000.00, 200, 180, 'Commercial', 2, NULL, NULL, 'completed', NULL, NULL, NULL, 55, 0, NULL),
(2003, 2003, 'Industrial Warehouse', 'Construction of industrial warehouse facility', 'Session Road, Barangay Asin, Baguio City, Benguet 2600', 8000000.00, 10000000.00, 1000, 800, 'Industrial', 3, NULL, 45, 'halt', NULL, NULL, NULL, 58, 0, NULL),
(2004, 2004, 'Agricultural Storage Facility', 'Storage facility for agricultural products', 'Burnham Road, Barangay Irisan, Baguio City, Benguet 2600', 1500000.00, 2000000.00, 300, 250, 'Agricultural', 4, NULL, NULL, 'terminated', NULL, NULL, NULL, 62, 0, NULL),
(2005, 2005, 'Residential Subdivision Phase 1', 'Development of residential subdivision with 50 units', 'Marcos Highway, Benguet', 15000000.00, 20000000.00, 2000, 1500, 'Residential', 1, NULL, 75, 'in_progress', NULL, NULL, NULL, 66, 0, NULL),
(2006, 2006, 'Luxury Residential Mansion', 'Construction of luxury residential mansion with pool and garden', 'Naguilian Road, Barangay Pinsao, Baguio City, Benguet 2600', 10000000.00, 15000000.00, 800, 600, 'Residential', 1, NULL, 90, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(2007, 2007, 'Shopping Mall Development', 'Development of modern shopping mall with retail spaces', 'Bokawkan Road, Barangay Cabayan, Baguio City, Benguet 2600', 50000000.00, 75000000.00, 5000, 4000, 'Commercial', 2, NULL, 120, 'bidding_closed', NULL, NULL, NULL, 81, 0, NULL),
(2008, 2008, 'Residential Condo Tower', 'High-rise residential condominium with 200 units', 'Asin Road, Barangay Asin, Baguio City, Benguet 2600', 30000000.00, 50000000.00, 3000, 2500, 'Residential', 1, NULL, 100, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(2009, 2009, 'Medical Center Complex', 'Construction of modern medical center with hospital facilities', 'Outlook Drive, Barangay Outlook, Baguio City, Benguet 2600', 20000000.00, 30000000.00, 2000, 1800, 'Commercial', 2, NULL, 110, 'bidding_closed', NULL, NULL, NULL, NULL, 0, NULL),
(2010, 2010, 'Educational Institution Building', 'Construction of school building with classrooms and facilities', 'Mines View Road, Barangay Mines View, Baguio City, Benguet 2600', 8000000.00, 12000000.00, 1200, 1000, 'Commercial', 2, NULL, 80, 'bidding_closed', NULL, NULL, NULL, 60, 0, NULL),
(2011, 2011, 'Residential Townhouse Development', 'Development of 20-unit townhouse complex with modern design', 'Trancoville, Barangay Trancoville, Baguio City, Benguet 2600', 6000000.00, 8000000.00, 600, 500, 'Residential', 1, NULL, 85, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(2012, 2012, 'Community Center Building', 'Construction of multi-purpose community center with facilities', 'Pacdal Road, Barangay Pacdal, Baguio City, Benguet 2600', 3000000.00, 4000000.00, 400, 350, 'Commercial', 2, NULL, 70, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(2013, 2013, 'Retail Shopping Complex', 'Modern retail shopping complex with parking facilities', 'Inya Road, Barangay Inya, Baguio City, Benguet 2600', 12000000.00, 16000000.00, 1500, 1200, 'Commercial', 2, NULL, 95, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(2014, 2014, 'Agricultural Processing Plant', 'Processing facility for agricultural products', 'Kisad Road, Barangay Kisad, Baguio City, Benguet 2600', 4000000.00, 5000000.00, 800, 600, 'Agricultural', 4, NULL, 65, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(2015, 2015, 'Industrial Manufacturing Facility', 'Manufacturing facility with modern equipment', 'Chanum Road, Barangay Chanum, Baguio City, Benguet 2600', 18000000.00, 25000000.00, 2500, 2000, 'Industrial', 3, NULL, 105, 'open', NULL, NULL, NULL, NULL, 0, NULL),
(2016, 2016, 'Residential Estate Project', 'Large residential estate development', 'Shuntug Road, Barangay Shuntug, Baguio City, Benguet 2600', 25000000.00, 35000000.00, 3000, 2500, 'Residential', 1, NULL, NULL, 'deleted_post', NULL, NULL, NULL, NULL, 0, NULL),
(2017, 2017, 'Commercial Office Tower', 'High-rise commercial office building', 'Loakan Road, Barangay Loakan, Baguio City, Benguet 2600', 40000000.00, 60000000.00, 4000, 3500, 'Commercial', 2, NULL, NULL, 'deleted_post', NULL, NULL, NULL, NULL, 0, NULL),
(2018, 2018, 'Industrial Complex Development', 'Large industrial complex with multiple facilities', 'Pinsao Road, Barangay Pinsao, Baguio City, Benguet 2600', 50000000.00, 75000000.00, 5000, 4000, 'Industrial', 3, NULL, NULL, 'deleted_post', NULL, NULL, NULL, NULL, 0, NULL),
(2019, 2019, 'Agricultural Farm Development', 'Large-scale agricultural farm with infrastructure', 'Asin Road, Barangay Asin, Baguio City, Benguet 2600', 8000000.00, 12000000.00, 2000, 500, 'Agricultural', 4, NULL, NULL, 'deleted_post', NULL, NULL, NULL, NULL, 0, NULL),
(2020, 2020, 'Mixed-Use Development Project', 'Mixed residential and commercial development', 'Outlook Drive, Barangay Outlook, Baguio City, Benguet 2600', 35000000.00, 50000000.00, 3500, 3000, 'Residential', 1, NULL, NULL, 'deleted_post', NULL, NULL, NULL, NULL, 0, NULL),
(2021, 2021, 'Residential Apartment Complex', 'Modern apartment complex with amenities', 'Mines View Road, Barangay Mines View, Baguio City, Benguet 2600', 7000000.00, 9000000.00, 700, 600, 'Residential', 1, NULL, 55, 'in_progress', NULL, NULL, NULL, 67, 0, NULL),
(2022, 2022, 'Commercial Retail Center', 'Retail center with multiple shops and restaurants', 'Trancoville, Barangay Trancoville, Baguio City, Benguet 2600', 5000000.00, 7000000.00, 500, 450, 'Commercial', 2, NULL, 40, 'in_progress', NULL, NULL, NULL, 70, 0, NULL),
(2023, 2023, 'Residential Subdivision Phase 2', 'Second phase of residential subdivision with 40 units', 'Pacdal Road, Barangay Pacdal, Baguio City, Benguet 2600', 12000000.00, 16000000.00, 1800, 1400, 'Residential', 1, NULL, 88, 'bidding_closed', NULL, NULL, NULL, NULL, 0, NULL),
(2024, 2024, 'Office Building Development', 'Modern office building with conference facilities', 'Inya Road, Barangay Inya, Baguio City, Benguet 2600', 8000000.00, 11000000.00, 900, 800, 'Commercial', 2, NULL, 72, 'in_progress', NULL, NULL, NULL, 75, 0, NULL),
(2025, 2025, 'Warehouse and Logistics Center', 'Large warehouse facility for logistics operations', 'Kisad Road, Barangay Kisad, Baguio City, Benguet 2600', 6000000.00, 8000000.00, 1200, 1000, 'Industrial', 3, NULL, 50, 'bidding_closed', NULL, NULL, NULL, NULL, 0, NULL),
(2026, 2026, 'khdkydjgdjttd', 'jgdjggdkgxky', 'itdkyditdktd, Arena Blanco, Zamboanga City, Zamboanga del Sur', 50000.00, 100000.00, 1000, 800, 'Residential', 8, NULL, NULL, 'terminated', 'in_progress', 'niggsrrffffffffy', NULL, 102, 0, NULL),
(2027, 2027, 'jhkjgbjnkml,.', '\';klhvcgfxgfcgvjhbjkml', 'kjkjhfgbfdzsfcvhjk, Arena Blanco, Zamboanga City, Zamboanga del Sur', 500.00, 1000.00, 500, 400, 'Residential', 6, NULL, NULL, 'open', NULL, NULL, NULL, NULL, 0, NULL);

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
(1, 2026, 'building permit', 'project_files/building_permit/ujkAZnwtNQOVaMxdB0TamoY7jPzyKmHhLHLFIvla.jpg', '2026-03-14 15:21:36'),
(2, 2026, 'title', 'project_files/titles/jbjaKOexLqONspg1i33V4dnugggTEMP40HLMD3rj.jpg', '2026-03-14 15:21:36'),
(3, 2026, 'blueprint', 'project_files/blueprints/CnXoFEuYsE92a6HhBpgl8WhB0Gnd9pbmhE4asbKC.jpg', '2026-03-14 15:21:36'),
(4, 2026, 'desired design', 'project_files/designs/r3sdvDSIhQ2tyAiMeuGXCNpQDXffVf8QqjKV6LBG.jpg', '2026-03-14 15:21:36'),
(5, 2026, 'others', 'project_files/others/iHMFyz7z3FvCA7oCvdqnc8kYu4MTxGdUYsD986zP.jpg', '2026-03-14 15:21:36'),
(6, 2027, 'building permit', 'project_files/building_permit/Ibbd9fWPnloPlPjFE7H3RkAUgahhlQrHnokUwV50.jpg', '2026-03-15 23:06:53'),
(7, 2027, 'title', 'project_files/titles/ZDAJyFuIsO49oluP12Yo1SEqM3FxBurt28U7RmS5.jpg', '2026-03-15 23:06:53'),
(8, 2027, 'blueprint', 'project_files/blueprints/JG3jeuInhX4LGqrYJF77hfaMtqIun89Q56xFcdFZ.jpg', '2026-03-15 23:06:53'),
(9, 2027, 'desired design', 'project_files/designs/gLFVwVDIUcNIGRSxTZRzukQEQVg9NkkJdkma92vb.jpg', '2026-03-15 23:06:53'),
(10, 2027, 'desired design', 'project_files/designs/RYg4gfqBL3LlRaFePRc5JSzOqnmd7n666NS5uzPm.jpg', '2026-03-15 23:06:53'),
(11, 2027, 'others', 'project_files/others/dBc7ElnWFaVFVGNZFHJ8ZiFtWZjoWXz42NgRPgQi.jpg', '2026-03-15 23:06:53'),
(12, 2027, 'others', 'project_files/others/l1EfsLJZ6xRhghYeWKFckGfSMXOQtGVN9lxhBP0H.jpg', '2026-03-15 23:06:53');

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
(2001, 251, 52, 'approved', NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2002, 252, 55, 'approved', NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2003, 253, 58, 'approved', NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2004, 254, 62, 'approved', NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2005, 255, 66, 'approved', NULL, NULL, '2026-03-12 00:00:00', '2026-03-14 08:13:11'),
(2006, 256, NULL, 'approved', NULL, '2026-04-15', '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2007, 257, 81, 'approved', NULL, '2026-04-20', '2026-03-12 00:00:00', '2026-03-14 08:11:09'),
(2008, 258, NULL, 'approved', NULL, '2026-04-25', '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2009, 259, NULL, 'approved', NULL, '2026-05-01', '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2010, 260, 60, 'approved', NULL, '2026-05-05', '2026-03-12 00:00:00', '2026-03-14 09:38:24'),
(2011, 261, NULL, 'under_review', NULL, '2026-04-10', '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2012, 262, NULL, 'under_review', NULL, '2026-04-12', '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2013, 263, NULL, 'under_review', NULL, '2026-04-15', '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2014, 264, NULL, 'under_review', NULL, '2026-04-18', '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2015, 265, NULL, 'under_review', NULL, '2026-04-20', '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2016, 266, NULL, 'rejected', 'Incomplete documentation and missing permits', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2017, 267, NULL, 'rejected', 'Project location not compliant with zoning regulations', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2018, 268, NULL, 'rejected', 'Budget exceeds maximum allowable amount', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2019, 269, NULL, 'rejected', 'Environmental impact assessment required', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2020, 270, NULL, 'rejected', 'Owner verification failed', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2021, 271, 67, 'due', NULL, '2026-02-28', '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2022, 272, 70, 'due', NULL, '2026-03-01', '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2023, 273, NULL, 'due', NULL, '2026-03-05', '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2024, 274, 75, 'due', NULL, '2026-03-08', '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2025, 275, NULL, 'due', NULL, '2026-03-10', '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2026, 282, 102, 'approved', NULL, '2026-03-30', '2026-03-14 15:21:36', '2026-03-14 23:52:37'),
(2027, 282, NULL, 'approved', NULL, '2026-03-31', '2026-03-15 23:06:53', '2026-03-16 07:07:48');

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
  `deletion_scheduled_at` timestamp NULL DEFAULT NULL,
  `deactivation_reason` text DEFAULT NULL,
  `suspension_reason` text DEFAULT NULL,
  `verification_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_owners`
--

INSERT INTO `property_owners` (`owner_id`, `user_id`, `profile_pic`, `cover_photo`, `address`, `date_of_birth`, `age`, `occupation_id`, `occupation_other`, `valid_id_id`, `valid_id_photo`, `valid_id_back_photo`, `police_clearance`, `verification_status`, `is_active`, `suspension_until`, `rejection_reason`, `deletion_reason`, `deletion_scheduled_at`, `deactivation_reason`, `suspension_reason`, `verification_date`, `created_at`) VALUES
(101, 1001, NULL, NULL, 'Makati Avenue, Barangay Bel-Air, Makati City, Metro Manila, 1200', '1990-01-15', 36, 3, NULL, 2, 'valid_id_shane.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(102, 1002, NULL, NULL, 'Ayala Avenue, Barangay Makati, Makati City, Metro Manila, 1226', '1988-03-22', 38, 7, NULL, 4, 'valid_id_anne.jpg', 'id_back.jpg', 'pc.jpg', 'pending', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(103, 1003, NULL, NULL, 'Roxas Boulevard, Barangay Ermita, Manila City, Metro Manila, 1000', '1992-05-10', 34, 1, NULL, 1, 'valid_id_dingdong.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(104, 1004, NULL, NULL, 'EDSA, Barangay Mandaluyong, Mandaluyong City, Metro Manila, 1550', '1985-07-18', 41, 12, NULL, 3, 'valid_id_marian.jpg', 'id_back.jpg', 'pc.jpg', 'rejected', 0, NULL, 'Invalid ID documents', NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(105, 1005, NULL, NULL, 'Ortigas Avenue, Barangay Pasig, Pasig City, Metro Manila, 1600', '1991-09-25', 35, 5, NULL, 5, 'valid_id_john.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(106, 1006, NULL, NULL, 'Osmeña Boulevard, Barangay Pob., Cebu City, Cebu, 6000', '1987-11-30', 39, 9, NULL, 2, 'valid_id_bea.jpg', 'id_back.jpg', 'pc.jpg', 'deleted', 0, NULL, NULL, 'Account deleted by user', NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(107, 1007, NULL, NULL, 'Roxas Avenue, Barangay Pob., Davao City, Davao del Sur, 8000', '1993-02-14', 33, 4, NULL, 1, 'valid_id_alden.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(108, 1008, NULL, NULL, 'Velez Street, Barangay Pob., Cagayan de Oro City, Misamis Oriental, 9000', '1989-04-08', 37, 11, NULL, 4, 'valid_id_maine.jpg', 'id_back.jpg', 'pc.jpg', 'pending', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(109, 1009, NULL, NULL, 'Iznart Street, Barangay Pob., Iloilo City, Iloilo, 5000', '1994-06-20', 32, 6, NULL, 3, 'valid_id_arjo.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(110, 1010, NULL, NULL, 'Lacson Street, Barangay Pob., Bacolod City, Negros Occidental, 6100', '1986-08-12', 40, 13, NULL, 5, 'valid_id_liza.jpg', 'id_back.jpg', 'pc.jpg', 'rejected', 0, NULL, 'Incomplete documents', NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(111, 1011, NULL, NULL, 'Mayor Jaldon Avenue, Barangay Pob., Zamboanga City, Zamboanga del Sur, 7000', '1995-10-05', 31, 2, NULL, 2, 'valid_id_enrique.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(112, 1012, NULL, NULL, 'Osmena Boulevard, Barangay Pob., General Santos City, South Cotabato, 9500', '1990-12-17', 36, 8, NULL, 1, 'valid_id_toni.jpg', 'id_back.jpg', 'pc.jpg', 'pending', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(113, 1013, NULL, NULL, 'Session Road, Barangay Pob., Baguio City, Benguet, 2600', '1988-01-28', 38, 14, NULL, 4, 'valid_id_piolo.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(114, 1014, NULL, NULL, 'Claro M. Recto Avenue, Barangay Pob., Angeles City, Pampanga, 2009', '1992-03-11', 34, 10, NULL, 3, 'valid_id_judy.jpg', 'id_back.jpg', 'pc.jpg', 'deleted', 0, NULL, NULL, 'Requested deletion', NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(115, 1015, NULL, NULL, 'Magsaysay Drive, Barangay Pob., Olongapo City, Zambales, 2200', '1987-05-23', 39, 15, NULL, 5, 'valid_id_ryan.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(116, 1016, NULL, NULL, 'Maharlika Highway, Barangay Pob., Lucena City, Quezon, 4301', '1991-07-14', 35, 11, NULL, 2, 'valid_id_angelica.jpg', 'id_back.jpg', 'pc.jpg', 'rejected', 0, NULL, 'Failed background check', NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(117, 1017, NULL, NULL, 'Burgos Street, Barangay Pob., Cabanatuan City, Nueva Ecija, 3100', '1989-09-08', 37, 6, NULL, 1, 'valid_id_jericho.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(118, 1018, NULL, NULL, 'Bonifacio Avenue, Barangay Pob., Malolos, Bulacan, 3000', '1993-11-19', 33, 9, NULL, 4, 'valid_id_kristine.jpg', 'id_back.jpg', 'pc.jpg', 'pending', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(119, 1019, NULL, NULL, 'Aguinaldo Highway, Barangay Pob., Tagaytay, Cavite, 4120', '1986-02-25', 40, 13, NULL, 3, 'valid_id_ogie.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(120, 1020, NULL, NULL, 'Marcos Highway, Barangay Antipolo, Antipolo City, Rizal, 1870', '1994-04-30', 32, 7, NULL, 5, 'valid_id_regine.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(121, 1021, NULL, NULL, 'Commonwealth Avenue, Barangay Quezon City, Quezon City, Metro Manila, 1121', '1990-06-12', 36, 4, NULL, 2, 'valid_id_vilma.jpg', 'id_back.jpg', 'pc.jpg', 'rejected', 0, NULL, 'Suspicious activity detected', NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(122, 1022, NULL, NULL, 'Katipunan Avenue, Barangay Pob., Quezon City, Metro Manila, 1108', '1988-08-17', 38, 12, NULL, 1, 'valid_id_nora.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(123, 1023, NULL, NULL, 'Epifanio de los Santos Avenue, Barangay Pob., Quezon City, Metro Manila, 1100', '1992-10-22', 34, 5, NULL, 4, 'valid_id_maricel.jpg', 'id_back.jpg', 'pc.jpg', 'pending', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(124, 1024, NULL, NULL, 'Paseo de Santa Rosa, Barangay Pob., Pasay City, Metro Manila, 1300', '1985-12-05', 41, 8, NULL, 3, 'valid_id_jaclyn.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(125, 1025, NULL, NULL, 'Bonifacio Global City, Barangay Taguig, Taguig City, Metro Manila, 1634', '1991-01-11', 35, 14, NULL, 5, 'valid_id_sheryl.jpg', 'id_back.jpg', 'pc.jpg', 'deleted', 0, NULL, NULL, 'Account suspended permanently', NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(126, 1026, NULL, NULL, 'Ortigas Avenue, Barangay Pasig, Pasig City, Metro Manila, 1600', '1987-03-16', 39, 2, NULL, 2, 'valid_id_ara.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(127, 1027, NULL, NULL, 'EDSA, Barangay Mandaluyong, Mandaluyong City, Metro Manila, 1550', '1993-05-21', 33, 10, NULL, 1, 'valid_id_gretchen.jpg', 'id_back.jpg', 'pc.jpg', 'rejected', 0, NULL, 'Document verification failed', NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(128, 1028, NULL, NULL, 'Ayala Avenue, Barangay Makati, Makati City, Metro Manila, 1226', '1989-07-26', 37, 6, NULL, 4, 'valid_id_margie.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(129, 1029, NULL, NULL, 'Makati Avenue, Barangay Bel-Air, Makati City, Metro Manila, 1200', '1994-09-03', 32, 11, NULL, 3, 'valid_id_aiko.jpg', 'id_back.jpg', 'pc.jpg', 'pending', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(130, 1030, NULL, NULL, 'Roxas Boulevard, Barangay Ermita, Manila City, Metro Manila, 1000', '1986-11-08', 40, 9, NULL, 5, 'valid_id_ruffa.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(131, 1031, 'profiles/logo_test.svg', NULL, 'Luneta Park, Barangay Ermita, Manila City, Metro Manila, 1000', '1995-01-13', 31, 13, NULL, 2, 'valid_id_andi.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-13 09:21:31', '2026-03-12 00:00:00'),
(132, 1032, NULL, NULL, 'Intramuros, Barangay Intramuros, Manila City, Metro Manila, 1002', '1990-03-18', 36, 7, NULL, 1, 'valid_id_boots.jpg', 'id_back.jpg', 'pc.jpg', 'rejected', 0, NULL, 'Duplicate account detected', NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(133, 1033, NULL, NULL, 'Binondo, Barangay Binondo, Manila City, Metro Manila, 1006', '1988-05-23', 38, 4, NULL, 4, 'valid_id_camille.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(134, 1034, NULL, NULL, 'Quiapo, Barangay Quiapo, Manila City, Metro Manila, 1001', '1992-07-28', 34, 12, NULL, 3, 'valid_id_diether.jpg', 'id_back.jpg', 'pc.jpg', 'pending', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(135, 1035, NULL, NULL, 'Sampaloc, Barangay Sampaloc, Manila City, Metro Manila, 1008', '1985-09-02', 41, 5, NULL, 5, 'valid_id_erich.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(136, 1036, NULL, NULL, 'Ermita, Barangay Ermita, Manila City, Metro Manila, 1000', '1991-11-07', 35, 8, NULL, 2, 'valid_id_florian.jpg', 'id_back.jpg', 'pc.jpg', 'deleted', 0, NULL, NULL, 'User requested deletion', NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(137, 1037, NULL, NULL, 'Malate, Barangay Malate, Manila City, Metro Manila, 1004', '1987-01-12', 39, 14, NULL, 1, 'valid_id_gina.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(138, 1038, NULL, NULL, 'Paco, Barangay Paco, Manila City, Metro Manila, 1007', '1993-03-17', 33, 6, NULL, 4, 'valid_id_hilda.jpg', 'id_back.jpg', 'pc.jpg', 'rejected', 0, NULL, 'Address verification failed', NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(139, 1039, NULL, NULL, 'San Miguel, Barangay San Miguel, Manila City, Metro Manila, 1005', '1989-05-22', 37, 10, NULL, 3, 'valid_id_irene.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(140, 1040, NULL, NULL, 'Tondo, Barangay Tondo, Manila City, Metro Manila, 1012', '1994-07-27', 32, 11, NULL, 5, 'valid_id_jacqueline.jpg', 'id_back.jpg', 'pc.jpg', 'pending', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(141, 1041, NULL, NULL, 'Santa Cruz, Barangay Santa Cruz, Manila City, Metro Manila, 1014', '1986-09-01', 40, 9, NULL, 2, 'valid_id_kris.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(142, 1042, NULL, NULL, 'Pandacan, Barangay Pandacan, Manila City, Metro Manila, 1011', '1995-11-06', 31, 13, NULL, 1, 'valid_id_lorna.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(143, 1043, NULL, NULL, 'Sta. Ana, Barangay Sta. Ana, Manila City, Metro Manila, 1015', '1990-02-11', 36, 7, NULL, 4, 'valid_id_mylene.jpg', 'id_back.jpg', 'pc.jpg', 'rejected', 0, NULL, 'ID expired', NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(144, 1044, NULL, NULL, 'Maynila, Barangay Maynila, Manila City, Metro Manila, 1003', '1988-04-16', 38, 4, NULL, 3, 'valid_id_nadia.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(145, 1045, NULL, NULL, 'Balut, Barangay Balut, Orani, Bataan, 2100', '1992-06-21', 34, 12, NULL, 5, 'valid_id_obet.jpg', 'id_back.jpg', 'pc.jpg', 'rejected', 0, NULL, 'RESUBMISSION: Blurred photo', NULL, NULL, NULL, NULL, '2026-03-14 08:28:14', '2026-03-12 00:00:00'),
(146, 1046, NULL, NULL, 'Tetuan, Barangay Tetuan, Orani, Bataan, 2100', '1985-08-26', 41, 5, NULL, 2, 'valid_id_pops.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(147, 1047, NULL, NULL, 'Sarao, Barangay Sarao, Orani, Bataan, 2100', '1991-10-31', 35, 8, NULL, 1, 'valid_id_queen.jpg', 'id_back.jpg', 'pc.jpg', 'deleted', 0, NULL, NULL, 'Fraud suspected', NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(148, 1048, NULL, NULL, 'Parang, Pantalan Luma (Pob.), Orani, Bataan 2100', '1987-12-05', 38, 14, NULL, 4, 'valid_id_ricky.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-14 20:18:24', '2026-03-12 00:00:00'),
(149, 1049, NULL, NULL, 'Limay, Barangay Limay, Limay, Bataan, 2100', '1993-02-10', 33, 6, NULL, 3, 'valid_id_sam.jpg', 'id_back.jpg', 'pc.jpg', 'rejected', 0, NULL, 'Incomplete police clearance', NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(150, 1050, NULL, NULL, 'Hermosa, Barangay Hermosa, Hermosa, Bataan, 2100', '1989-04-15', 37, 10, NULL, 5, 'valid_id_tanya.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 0, '9999-12-31', NULL, NULL, NULL, NULL, 'Nigger', '2026-03-14 17:34:32', '2026-03-12 00:00:00'),
(201, 2001, NULL, NULL, 'Makati Avenue, Barangay Bel-Air, Makati City, Metro Manila, 1200', '1990-01-15', 36, 3, NULL, 2, 'valid_id_coco.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(202, 2002, NULL, NULL, 'Ayala Avenue, Barangay Makati, Makati City, Metro Manila, 1226', '1988-03-22', 38, 7, NULL, 4, 'valid_id_vilma.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(203, 2003, NULL, NULL, 'Roxas Boulevard, Barangay Ermita, Manila City, Metro Manila, 1000', '1992-05-10', 34, 1, NULL, 1, 'valid_id_nora.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(204, 2004, NULL, NULL, 'EDSA, Barangay Mandaluyong, Mandaluyong City, Metro Manila, 1550', '1985-07-18', 41, 12, NULL, 3, 'valid_id_maricel.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(205, 2005, NULL, NULL, 'Ortigas Avenue, Barangay Pasig, Pasig City, Metro Manila, 1600', '1991-09-25', 35, 5, NULL, 5, 'valid_id_jaclyn.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(206, 2006, NULL, NULL, 'Osmeña Boulevard, Barangay Pob., Cebu City, Cebu, 6000', '1987-11-30', 39, 9, NULL, 2, 'valid_id_sheryl.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(207, 2007, NULL, NULL, 'Roxas Avenue, Barangay Pob., Davao City, Davao del Sur, 8000', '1993-02-14', 33, 4, NULL, 1, 'valid_id_ara.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(208, 2008, NULL, NULL, 'Velez Street, Barangay Pob., Cagayan de Oro City, Misamis Oriental, 9000', '1989-04-08', 37, 11, NULL, 4, 'valid_id_gretchen.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(209, 2009, NULL, NULL, 'Iznart Street, Barangay Pob., Iloilo City, Iloilo, 5000', '1994-06-20', 32, 6, NULL, 3, 'valid_id_margie.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(210, 2010, NULL, NULL, 'Lacson Street, Barangay Pob., Bacolod City, Negros Occidental, 6100', '1986-08-12', 40, 13, NULL, 5, 'valid_id_aiko.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(211, 2011, NULL, NULL, 'Mayor Jaldon Avenue, Barangay Pob., Zamboanga City, Zamboanga del Sur, 7000', '1995-10-05', 31, 2, NULL, 2, 'valid_id_ruffa.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(212, 2012, NULL, NULL, 'Osmena Boulevard, Barangay Pob., General Santos City, South Cotabato, 9500', '1990-12-17', 36, 8, NULL, 1, 'valid_id_andi.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(213, 2013, NULL, NULL, 'Session Road, Barangay Pob., Baguio City, Benguet, 2600', '1988-01-28', 38, 14, NULL, 4, 'valid_id_boots.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(214, 2014, NULL, NULL, 'Claro M. Recto Avenue, Barangay Pob., Angeles City, Pampanga, 2009', '1992-03-11', 34, 10, NULL, 3, 'valid_id_camille.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(215, 2015, NULL, NULL, 'Magsaysay Drive, Barangay Pob., Olongapo City, Zambales, 2200', '1987-05-23', 39, 15, NULL, 5, 'valid_id_diether.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(216, 2016, NULL, NULL, 'Maharlika Highway, Barangay Pob., Lucena City, Quezon, 4301', '1991-07-14', 35, 11, NULL, 2, 'valid_id_erich.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(217, 2017, NULL, NULL, 'Burgos Street, Barangay Pob., Cabanatuan City, Nueva Ecija, 3100', '1989-09-08', 37, 6, NULL, 1, 'valid_id_florian.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(218, 2018, NULL, NULL, 'Bonifacio Avenue, Barangay Pob., Malolos, Bulacan, 3000', '1993-11-19', 33, 9, NULL, 4, 'valid_id_gina.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(219, 2019, NULL, NULL, 'Aguinaldo Highway, Barangay Pob., Tagaytay, Cavite, 4120', '1986-02-25', 40, 13, NULL, 3, 'valid_id_hilda.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(220, 2020, NULL, NULL, 'Marcos Highway, Barangay Antipolo, Antipolo City, Rizal, 1870', '1994-04-30', 32, 7, NULL, 5, 'valid_id_irene.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(221, 2021, NULL, NULL, 'Commonwealth Avenue, Barangay Quezon City, Quezon City, Metro Manila, 1121', '1990-06-12', 36, 4, NULL, 2, 'valid_id_jacqueline.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(222, 2022, NULL, NULL, 'Katipunan Avenue, Barangay Pob., Quezon City, Metro Manila, 1108', '1988-08-17', 38, 12, NULL, 1, 'valid_id_kris.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(223, 2023, NULL, NULL, 'Epifanio de los Santos Avenue, Barangay Pob., Quezon City, Metro Manila, 1100', '1992-10-22', 34, 5, NULL, 4, 'valid_id_lorna.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(224, 2024, NULL, NULL, 'Paseo de Santa Rosa, Barangay Pob., Pasay City, Metro Manila, 1300', '1985-12-05', 41, 8, NULL, 3, 'valid_id_mylene.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(225, 2025, NULL, NULL, 'Bonifacio Global City, Barangay Taguig, Taguig City, Metro Manila, 1634', '1991-01-11', 35, 14, NULL, 5, 'valid_id_nadia.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(226, 2026, NULL, NULL, 'Ortigas Avenue, Barangay Pasig, Pasig City, Metro Manila, 1600', '1987-03-16', 39, 2, NULL, 2, 'valid_id_obet.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(227, 2027, NULL, NULL, 'EDSA, Barangay Mandaluyong, Mandaluyong City, Metro Manila, 1550', '1993-05-21', 33, 10, NULL, 1, 'valid_id_pops.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(228, 2028, NULL, NULL, 'Ayala Avenue, Barangay Makati, Makati City, Metro Manila, 1226', '1989-07-26', 37, 6, NULL, 4, 'valid_id_queen.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(229, 2029, NULL, NULL, 'Makati Avenue, Barangay Bel-Air, Makati City, Metro Manila, 1200', '1994-09-03', 32, 11, NULL, 3, 'valid_id_ricky.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(230, 2030, NULL, NULL, 'Roxas Boulevard, Barangay Ermita, Manila City, Metro Manila, 1000', '1986-11-08', 40, 9, NULL, 5, 'valid_id_sam.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(231, 2031, NULL, NULL, 'Luneta Park, Barangay Ermita, Manila City, Metro Manila, 1000', '1995-01-13', 31, 13, NULL, 2, 'valid_id_tanya.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(232, 2032, NULL, NULL, 'Intramuros, Barangay Intramuros, Manila City, Metro Manila, 1002', '1990-03-18', 36, 7, NULL, 1, 'valid_id_ula.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(233, 2033, NULL, NULL, 'Binondo, Barangay Binondo, Manila City, Metro Manila, 1006', '1988-05-23', 38, 4, NULL, 4, 'valid_id_vina.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(234, 2034, NULL, NULL, 'Quiapo, Barangay Quiapo, Manila City, Metro Manila, 1001', '1992-07-28', 34, 12, NULL, 3, 'valid_id_wally.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(235, 2035, NULL, NULL, 'Sampaloc, Barangay Sampaloc, Manila City, Metro Manila, 1008', '1985-09-02', 41, 5, NULL, 5, 'valid_id_xander.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(236, 2036, NULL, NULL, 'Ermita, Barangay Ermita, Manila City, Metro Manila, 1000', '1991-11-07', 35, 8, NULL, 2, 'valid_id_yassi.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(237, 2037, NULL, NULL, 'Malate, Barangay Malate, Manila City, Metro Manila, 1004', '1987-01-12', 39, 14, NULL, 1, 'valid_id_zoren.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 0, '9999-12-31', NULL, NULL, NULL, NULL, 'Yede puit', '2026-03-14 17:11:11', '2026-03-12 00:00:00'),
(238, 2038, NULL, NULL, 'Paco, Barangay Paco, Manila City, Metro Manila, 1007', '1993-03-17', 33, 6, NULL, 4, 'valid_id_alyssa.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(239, 2039, NULL, NULL, 'San Miguel, Barangay San Miguel, Manila City, Metro Manila, 1005', '1989-05-22', 37, 10, NULL, 3, 'valid_id_bea.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(240, 2040, NULL, NULL, 'Tondo, Barangay Tondo, Manila City, Metro Manila, 1012', '1994-07-27', 32, 11, NULL, 5, 'valid_id_carla.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(241, 2041, NULL, NULL, 'Santa Cruz, Barangay Santa Cruz, Manila City, Metro Manila, 1014', '1986-09-01', 40, 9, NULL, 2, 'valid_id_denise.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(242, 2042, NULL, NULL, 'Pandacan, Barangay Pandacan, Manila City, Metro Manila, 1011', '1995-11-06', 31, 13, NULL, 1, 'valid_id_ella.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(243, 2043, NULL, NULL, 'Sta. Ana, Barangay Sta. Ana, Manila City, Metro Manila, 1015', '1990-02-11', 36, 7, NULL, 4, 'valid_id_faye.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(244, 2044, NULL, NULL, 'Maynila, Barangay Maynila, Manila City, Metro Manila, 1003', '1988-04-16', 38, 4, NULL, 3, 'valid_id_giselle.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(245, 2045, NULL, NULL, 'Balut, Barangay Balut, Orani, Bataan, 2100', '1992-06-21', 34, 12, NULL, 5, 'valid_id_hannah.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(246, 2046, NULL, NULL, 'Tetuan, Barangay Tetuan, Orani, Bataan, 2100', '1985-08-26', 41, 5, NULL, 2, 'valid_id_isabelle.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(247, 2047, NULL, NULL, 'Sarao, Barangay Sarao, Orani, Bataan, 2100', '1991-10-31', 35, 8, NULL, 1, 'valid_id_julia.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(248, 2048, NULL, NULL, 'Parang, Barangay Parang, Orani, Bataan, 2100', '1987-12-05', 39, 14, NULL, 4, 'valid_id_kim.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(249, 2049, NULL, NULL, 'Limay, Barangay Limay, Limay, Bataan, 2100', '1993-02-10', 33, 6, NULL, 3, 'valid_id_lj.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(250, 2050, NULL, NULL, 'Hermosa, Barangay Hermosa, Hermosa, Bataan, 2100', '1989-04-15', 37, 10, NULL, 5, 'valid_id_megan.jpg', 'id_back.jpg', 'pc.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(251, 3001, NULL, NULL, 'Tetuan, Poblacion, Mankayan, Benguet 2600', '1990-03-15', 36, 3, NULL, 2, 'valid_id_john.jpg', 'id_back_john.jpg', 'pc_john.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(252, 3002, NULL, NULL, 'Magsaysay, Barangay Poblacion, Baguio City, Benguet 2600', '1988-07-22', 37, 7, NULL, 4, 'valid_id_maria.jpg', 'id_back_maria.jpg', 'pc_maria.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(253, 3003, NULL, NULL, 'Session Road, Barangay Asin, Baguio City, Benguet 2600', '1992-11-08', 33, 1, NULL, 1, 'valid_id_robert.jpg', 'id_back_robert.jpg', 'pc_robert.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(254, 3004, NULL, NULL, 'Burnham Road, Barangay Irisan, Baguio City, Benguet 2600', '1995-05-12', 30, 5, NULL, 3, 'valid_id_anna.jpg', 'id_back_anna.jpg', 'pc_anna.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(255, 3005, NULL, NULL, 'Marcos Highway, Barangay Loakan, Baguio City, Benguet 2600', '1987-09-18', 38, 2, NULL, 5, 'valid_id_carlos.jpg', 'id_back_carlos.jpg', 'pc_carlos.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(256, 3006, NULL, NULL, 'Naguilian Road, Barangay Pinsao, Baguio City, Benguet 2600', '1991-02-25', 35, 4, NULL, 2, 'valid_id_diana.jpg', 'id_back_diana.jpg', 'pc_diana.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(257, 3007, NULL, NULL, 'Bokawkan Road, Barangay Cabayan, Baguio City, Benguet 2600', '1989-06-30', 36, 6, NULL, 1, 'valid_id_miguel.jpg', 'id_back_miguel.jpg', 'pc_miguel.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(258, 3008, NULL, NULL, 'Asin Road, Barangay Asin, Baguio City, Benguet 2600', '1993-12-14', 32, 8, NULL, 4, 'valid_id_elena.jpg', 'id_back_elena.jpg', 'pc_elena.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(259, 3009, NULL, NULL, 'Outlook Drive, Barangay Outlook, Baguio City, Benguet 2600', '1986-04-20', 39, 9, NULL, 3, 'valid_id_francisco.jpg', 'id_back_francisco.jpg', 'pc_francisco.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(260, 3010, NULL, NULL, 'Mines View Road, Barangay Mines View, Baguio City, Benguet 2600', '1994-08-05', 31, 10, NULL, 5, 'valid_id_rosa.jpg', 'id_back_rosa.jpg', 'pc_rosa.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(261, 3011, NULL, NULL, 'Trancoville, Barangay Trancoville, Baguio City, Benguet 2600', '1990-10-11', 35, 11, NULL, 2, 'valid_id_antonio.jpg', 'id_back_antonio.jpg', 'pc_antonio.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(262, 3012, NULL, NULL, 'Pacdal Road, Barangay Pacdal, Baguio City, Benguet 2600', '1992-01-17', 34, 12, NULL, 1, 'valid_id_lucia.jpg', 'id_back_lucia.jpg', 'pc_lucia.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(263, 3013, NULL, NULL, 'Inya Road, Barangay Inya, Baguio City, Benguet 2600', '1988-03-23', 37, 13, NULL, 4, 'valid_id_manuel.jpg', 'id_back_manuel.jpg', 'pc_manuel.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(264, 3014, NULL, NULL, 'Kisad Road, Barangay Kisad, Baguio City, Benguet 2600', '1991-07-09', 34, 14, NULL, 3, 'valid_id_teresa.jpg', 'id_back_teresa.jpg', 'pc_teresa.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(265, 3015, NULL, NULL, 'Chanum Road, Barangay Chanum, Baguio City, Benguet 2600', '1989-11-28', 36, 15, NULL, 5, 'valid_id_ramon.jpg', 'id_back_ramon.jpg', 'pc_ramon.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(266, 3016, NULL, NULL, 'Shuntug Road, Barangay Shuntug, Baguio City, Benguet 2600', '1993-05-14', 32, 1, NULL, 2, 'valid_id_carmen.jpg', 'id_back_carmen.jpg', 'pc_carmen.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(267, 3017, NULL, NULL, 'Loakan Road, Barangay Loakan, Baguio City, Benguet 2600', '1987-09-02', 38, 2, NULL, 1, 'valid_id_luis.jpg', 'id_back_luis.jpg', 'pc_luis.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(268, 3018, NULL, NULL, 'Pinsao Road, Barangay Pinsao, Baguio City, Benguet 2600', '1994-02-19', 31, 3, NULL, 4, 'valid_id_isabel.jpg', 'id_back_isabel.jpg', 'pc_isabel.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(269, 3019, NULL, NULL, 'Asin Road, Barangay Asin, Baguio City, Benguet 2600', '1990-06-27', 35, 4, NULL, 3, 'valid_id_pedro.jpg', 'id_back_pedro.jpg', 'pc_pedro.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(270, 3020, NULL, NULL, 'Outlook Drive, Barangay Outlook, Baguio City, Benguet 2600', '1992-10-03', 33, 5, NULL, 5, 'valid_id_beatriz.jpg', 'id_back_beatriz.jpg', 'pc_beatriz.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(271, 3021, NULL, NULL, 'Mines View Road, Barangay Mines View, Baguio City, Benguet 2600', '1989-04-16', 36, 6, NULL, 2, 'valid_id_jorge.jpg', 'id_back_jorge.jpg', 'pc_jorge.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(272, 3022, NULL, NULL, 'Trancoville, Barangay Trancoville, Baguio City, Benguet 2600', '1991-08-22', 34, 7, NULL, 1, 'valid_id_gloria.jpg', 'id_back_gloria.jpg', 'pc_gloria.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(273, 3023, NULL, NULL, 'Pacdal Road, Barangay Pacdal, Baguio City, Benguet 2600', '1993-12-10', 32, 8, NULL, 4, 'valid_id_enrique.jpg', 'id_back_enrique.jpg', 'pc_enrique.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(274, 3024, NULL, NULL, 'Inya Road, Barangay Inya, Baguio City, Benguet 2600', '1988-05-07', 37, 9, NULL, 3, 'valid_id_patricia.jpg', 'id_back_patricia.jpg', 'pc_patricia.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(275, 3025, NULL, NULL, 'Kisad Road, Barangay Kisad, Baguio City, Benguet 2600', '1990-09-13', 35, 10, NULL, 5, 'valid_id_victor.jpg', 'id_back_victor.jpg', 'pc_victor.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(276, 3026, NULL, NULL, 'Chanum Road, Barangay Chanum, Baguio City, Benguet 2600', '1992-03-21', 33, 11, NULL, 2, 'valid_id_sandra.jpg', 'id_back_sandra.jpg', 'pc_sandra.jpg', 'approved', 0, '9999-12-31', NULL, NULL, NULL, NULL, 'Nigger', '2026-03-14 17:13:48', '2026-03-12 00:00:00'),
(277, 3027, NULL, NULL, 'Shuntug Road, Barangay Shuntug, Baguio City, Benguet 2600', '1989-07-08', 36, 12, NULL, 1, 'valid_id_alberto.jpg', 'id_back_alberto.jpg', 'pc_alberto.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(278, 3028, NULL, NULL, 'Loakan Road, Barangay Loakan, Baguio City, Benguet 2600', '1991-11-26', 34, 13, NULL, 4, 'valid_id_veronica.jpg', 'id_back_veronica.jpg', 'pc_veronica.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(279, 3029, NULL, NULL, 'Pinsao Road, Barangay Pinsao, Baguio City, Benguet 2600', '1994-04-12', 31, 14, NULL, 3, 'valid_id_salvador.jpg', 'id_back_salvador.jpg', 'pc_salvador.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-12 15:38:12', '2026-03-12 00:00:00'),
(280, 3030, NULL, NULL, 'Asin Road, Barangay Asin, Baguio City, Benguet 2600', '1987-08-19', 38, 15, NULL, 5, 'valid_id_natalia.jpg', 'id_back_natalia.jpg', 'pc_natalia.jpg', 'approved', 0, '9999-12-31', NULL, NULL, NULL, NULL, 'Niggers', '2026-03-14 17:09:09', '2026-03-12 00:00:00'),
(281, 3031, NULL, NULL, 'Sapphire Street, Agtangao, Bangued, Abra 7000', '1988-02-16', 38, 22, NULL, 2, 'validID/front/aHUZ7fT2KJ5P5VQsVUQeg3giMPtzIGT57Kj5qobB.jpg', 'validID/back/8B5IyTblim2ELQCJJgFszFl06ZzozpTi17cVUDs6.jpg', 'policeClearance/FjgytI5Wk76eR6UQrl1h6Dn7DQOfoQjQYes1zf0o.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-14 14:06:09', '2026-03-14 14:06:09'),
(282, 3032, NULL, NULL, 'Sapphire Street, Mabini Ext. (Pob.), Dinalupihan, Bataan 7000', '1992-03-27', 33, 22, NULL, 2, 'validID/front/2C2gb0aBPZAjucmr0GTIK5BGQuRCEbtg7fTDBGoK.jpg', 'validID/back/fmmF99WFkYwoliQeszfoTTm4U3yNCGmGbnyru4Cs.jpg', 'policeClearance/Tl8ex3aEVIJFXpfpZv7MUqhnrGBlfyZuS1LNAm0u.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-14 14:23:43', '2026-03-14 14:23:43'),
(283, 3033, 'profiles/bylicJi5BtnF167Jj1Ll0YMNPGXI6bJYXEFNSYuZ.jpg', 'cover_photos/mg9JH0M87r2amEyfwS2CUBKiUDkBPlbzXXtddxfc.jpg', '456 Oak Avenue, Amparo, City of Butuan, Agusan Del Norte, 7000', '1964-03-15', 62, 23, NULL, 4, 'validID/IqTvNH4cJaQHNV5slAmKDAvp9uRqn2Q0wgru9bDk.jpg', 'validID/bNe2KGbO9kqU8W55PobbITSI9V3zM3bwzPAkN9Ft.jpg', 'policeClearance/joBJ7XQJ7dJxX5WKp2hu4Kr8f4SoKoCDcLaSe4Ns.jpg', 'approved', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-14 23:32:57', '2026-03-14 23:07:19');

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
(1, 2002, 3004, 3002, 5, 'Excellent property owner to work with! Very responsive to communications, provided clear project requirements, and made timely decisions throughout the renovation project. Payment was processed promptly upon completion. Highly recommend working with this owner for future projects.', 1, 'Report confirmed by admin', '2026-03-11 10:30:00'),
(2, 2002, 3002, 3004, 5, 'Outstanding contractor! The commercial office renovation was completed on time and within budget. The quality of work exceeded our expectations. The team was professional, courteous, and kept us informed throughout the project. All work was done to the highest standards. Would definitely hire them again for future projects.', 0, NULL, '2026-03-11 11:15:00');

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
  `admin_action` varchar(100) DEFAULT NULL,
  `reviewed_by_user_id` int(11) DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `review_reports`
--

INSERT INTO `review_reports` (`report_id`, `reporter_user_id`, `review_id`, `reason`, `details`, `status`, `admin_action`, `reviewed_by_user_id`, `admin_notes`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(1, 3002, 1, 'Inappropriate content', 'The review contains language that may be considered promotional rather than genuine feedback. The reviewer appears to have a conflict of interest as they are also the contractor on the project.', 'resolved', 'Warned', NULL, 'jkjkkjknknknknknknk', '2026-03-14 08:37:01', '2026-03-12 14:30:00', '2026-03-14 08:37:01');

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
(1, 3004, 'Commercial Office Space Renovation - Project 2002', 'Successfully completed a comprehensive commercial office renovation project in Baguio City. This project involved complete demolition of old fixtures, installation of modern electrical and plumbing systems, and premium interior finishing with professional painting and flooring. The renovation was completed on schedule and within budget, delivering a state-of-the-art office space that exceeds client expectations. The project showcases our expertise in commercial renovations and our commitment to quality workmanship. Key highlights: - Complete office renovation with modern design - Professional electrical and plumbing installation - Premium interior finishing and painting - On-time project completion - Budget-friendly execution - High-quality materials and craftsmanship', 2002, 'Magsaysay, Barangay Poblacion, Baguio City, Benguet 2600', 'approved', 1, '2026-03-12 10:00:00', 'premium', '', NULL, '2026-03-11 12:00:00', '2026-03-12 10:00:00');

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
(1, 1, 'showcases/project_2002_before.jpg', 'project_2002_before.jpg', 0, '2026-03-11 12:00:00', '2026-03-11 12:00:00'),
(2, 1, 'showcases/project_2002_during.jpg', 'project_2002_during.jpg', 1, '2026-03-11 12:00:00', '2026-03-11 12:00:00'),
(3, 1, 'showcases/project_2002_after.jpg', 'project_2002_after.jpg', 2, '2026-03-11 12:00:00', '2026-03-11 12:00:00'),
(4, 1, 'showcases/project_2002_interior.jpg', 'project_2002_interior.jpg', 3, '2026-03-11 12:00:00', '2026-03-11 12:00:00');

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
(1, 'gold', 1, 'Gold Tier Subscriptiona', 199900, 'PHP', 'monthly', NULL, NULL, '[\"Unlock AI driven analytics\",\"Unlimited Bids per month\",\"Boost Bids\"]', 1, 0, '2026-03-05 01:11:04', '2026-03-14 10:43:52'),
(2, 'silver', 1, 'Silver Tier Subscription', 149900, 'PHP', 'monthly', NULL, NULL, '[\"25 Bids per month\",\"Boost Bids\"]', 1, 0, '2026-03-05 01:11:04', '2026-03-14 10:46:07'),
(3, 'bronze', 1, 'Bronze Tier Subscription', 99900, 'PHP', 'monthly', NULL, NULL, '[\"10 Bids per month\"]', 1, 0, '2026-03-05 01:11:04', '2026-03-05 01:11:04'),
(4, 'boost', 0, 'Project Boost', 4900, 'PHP', 'one-time', 7, NULL, '[\"7 Days Visibility Boost\"]', 1, 0, '2026-03-05 01:11:04', '2026-03-05 01:11:04'),
(5, 'GRRR', 0, 'asdasd', 23400, 'PHP', 'monthly', NULL, 'adasdadasdad', '[\"asdad\"]', 0, 1, '2026-03-14 10:24:36', '2026-03-14 10:24:43'),
(6, 'asdadasd', 0, 'asdasd', 34200, 'PHP', 'monthly', NULL, 'asddasdasdas', '[\"asdda\"]', 0, 1, '2026-03-14 10:44:08', '2026-03-14 11:00:06'),
(7, 'asdasd', 0, 'asdasd', 23400, 'PHP', 'one-time', 3, 'asdasdasdad', '[\"asdasd\"]', 0, 1, '2026-03-14 10:59:45', '2026-03-14 10:59:52');

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
  `user_type` enum('property_owner','both','owner_staff') NOT NULL,
  `preferred_role` enum('contractor','owner') DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `OTP_hash`, `bio`, `user_type`, `preferred_role`, `first_name`, `middle_name`, `last_name`, `phone_number`, `created_at`, `updated_at`) VALUES
(1001, 'shane_owner', 'shane@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Shane', NULL, 'Gillis', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1002, 'anne_owner', 'anne@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Anne', NULL, 'Curtis', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1003, 'dingdong_owner', 'dingdong@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Dingdong', NULL, 'Dantes', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1004, 'marian_owner', 'marian@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Marian', NULL, 'Rivera', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1005, 'john_owner', 'john@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'John', NULL, 'Lloyd', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1006, 'bea_owner', 'bea@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Bea', NULL, 'Alonzo', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1007, 'alden_owner', 'alden@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Alden', NULL, 'Richards', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1008, 'maine_owner', 'maine@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Maine', NULL, 'Mendoza', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1009, 'arjo_owner', 'arjo@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Arjo', NULL, 'Atayde', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1010, 'liza_owner', 'liza@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Liza', NULL, 'Soberano', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1011, 'enrique_owner', 'enrique@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Enrique', NULL, 'Gil', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1012, 'toni_owner', 'toni@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Toni', NULL, 'Gonzaga', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1013, 'piolo_owner', 'piolo@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Piolo', NULL, 'Pascual', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1014, 'judy_owner', 'judy@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Judy', NULL, 'Ann', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1015, 'ryan_owner', 'ryan@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Ryan', NULL, 'Agoncillo', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1016, 'angelica_owner', 'angelica@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Angelica', NULL, 'Panganiban', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1017, 'jericho_owner', 'jericho@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Jericho', NULL, 'Rosales', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1018, 'kristine_owner', 'kristine@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Kristine', NULL, 'Hermosa', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1019, 'ogie_owner', 'ogie@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Ogie', NULL, 'Alcasid', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1020, 'regine_owner', 'regine@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Regine', NULL, 'Velasquez', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1021, 'vilma_owner', 'vilma@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Vilma', NULL, 'Santos', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1022, 'nora_owner', 'nora@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Nora', NULL, 'Aunor', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1023, 'maricel_owner', 'maricel@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Maricel', NULL, 'Soriano', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1024, 'jaclyn_owner', 'jaclyn@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Jaclyn', NULL, 'Jose', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1025, 'sheryl_owner', 'sheryl@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Sheryl', NULL, 'Cruz', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1026, 'ara_owner', 'ara@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Ara', NULL, 'Mina', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1027, 'gretchen_owner', 'gretchen@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Gretchen', NULL, 'Barretto', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1028, 'margie_owner', 'margie@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Margie', NULL, 'Moran', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1029, 'aiko_owner', 'aiko@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Aiko', NULL, 'Melendez', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1030, 'ruffa_owner', 'ruffa@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Ruffa', NULL, 'Gutierrez', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1031, 'andi_owner', 'andi@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Andi', NULL, 'Eigenmann', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1032, 'boots_owner', 'boots@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Boots', NULL, 'Anson', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1033, 'camille_owner', 'camille@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Camille', NULL, 'Prats', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1034, 'diether_owner', 'diether@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Diether', NULL, 'Ocampo', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1035, 'erich_owner', 'erich@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Erich', NULL, 'Gonzales', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1036, 'florian_owner', 'florian@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Florian', NULL, 'Carandang', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1037, 'gina_owner', 'gina@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Gina', NULL, 'Pareño', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1038, 'hilda_owner', 'hilda@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Hilda', NULL, 'Koronel', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1039, 'irene_owner', 'irene@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Irene', NULL, 'Razal', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1040, 'jacqueline_owner', 'jacqueline@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Jacqueline', NULL, 'Fernandez', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1041, 'kris_owner', 'kris@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Kris', NULL, 'Aquino', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1042, 'lorna_owner', 'lorna@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Lorna', NULL, 'Tolentino', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1043, 'mylene_owner', 'mylene@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Mylene', NULL, 'Dizon', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1044, 'nadia_owner', 'nadia@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Nadia', NULL, 'Montenegro', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1045, 'obet_owner', 'obet@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Obet', NULL, 'Lim', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1046, 'pops_owner', 'pops@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Pops', NULL, 'Fernandez', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1047, 'queen_owner', 'queen@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Queen', NULL, 'Marquez', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1048, 'ricky_owner', 'ricky@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Ricky', NULL, 'Davao', NULL, '2026-03-12 00:00:00', '2026-03-14 12:18:24'),
(1049, 'sam_owner', 'sam@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Sam', NULL, 'Milby', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(1050, 'tanya_owner', 'tanya@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'property_owner', NULL, 'Tanya', NULL, 'Gomez', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2001, 'coco_both', 'coco.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Coco', NULL, 'Martin', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2002, 'vilma_both', 'vilma.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Vilma', NULL, 'Santos', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2003, 'nora_both', 'nora.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Nora', NULL, 'Aunor', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2004, 'maricel_both', 'maricel.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Maricel', NULL, 'Soriano', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2005, 'jaclyn_both', 'jaclyn.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Jaclyn', NULL, 'Jose', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2006, 'sheryl_both', 'sheryl.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Sheryl', NULL, 'Cruz', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2007, 'ara_both', 'ara.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Ara', NULL, 'Mina', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2008, 'gretchen_both', 'gretchen.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Gretchen', NULL, 'Barretto', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2009, 'margie_both', 'margie.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Margie', NULL, 'Moran', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2010, 'aiko_both', 'aiko.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Aiko', NULL, 'Melendez', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2011, 'ruffa_both', 'ruffa.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Ruffa', NULL, 'Gutierrez', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2012, 'andi_both', 'andi.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Andi', NULL, 'Eigenmann', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2013, 'boots_both', 'boots.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Boots', NULL, 'Anson', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2014, 'camille_both', 'camille.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Camille', NULL, 'Prats', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2015, 'diether_both', 'diether.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Diether', NULL, 'Ocampo', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2016, 'erich_both', 'erich.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Erich', NULL, 'Gonzales', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2017, 'florian_both', 'florian.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Florian', NULL, 'Carandang', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2018, 'gina_both', 'gina.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Gina', NULL, 'Pareño', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2019, 'hilda_both', 'hilda.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Hilda', NULL, 'Koronel', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2020, 'irene_both', 'irene.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Irene', NULL, 'Razal', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2021, 'jacqueline_both', 'jacqueline.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Jacqueline', NULL, 'Fernandez', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2022, 'kris_both', 'kris.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Kris', NULL, 'Aquino', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2023, 'lorna_both', 'lorna.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Lorna', NULL, 'Tolentino', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2024, 'mylene_both', 'mylene.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Mylene', NULL, 'Dizon', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2025, 'nadia_both', 'nadia.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Nadia', NULL, 'Montenegro', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2026, 'obet_both', 'obet.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Obet', NULL, 'Lim', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2027, 'pops_both', 'pops.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Pops', NULL, 'Fernandez', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2028, 'queen_both', 'queen.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Queen', NULL, 'Marquez', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2029, 'ricky_both', 'ricky.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Ricky', NULL, 'Davao', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2030, 'sam_both', 'sam.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Sam', NULL, 'Milby', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2031, 'tanya_both', 'tanya.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Tanya', NULL, 'Gomez', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2032, 'ula_both', 'ula.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Ula', NULL, 'Legaspi', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2033, 'vina_both', 'vina.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Vina', NULL, 'Morales', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2034, 'wally_both', 'wally.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Wally', NULL, 'Bayola', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2035, 'xander_both', 'xander.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Xander', NULL, 'Ford', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2036, 'yassi_both', 'yassi.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Yassi', NULL, 'Pressman', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2037, 'zoren_both', 'zoren.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Zoren', NULL, 'Legaspi', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2038, 'alyssa_both', 'alyssa.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Alyssa', NULL, 'Valdez', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2039, 'bea_both', 'bea.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Bea', NULL, 'Binene', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2040, 'carla_both', 'carla.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Carla', NULL, 'Abellana', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2041, 'denise_both', 'denise.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Denise', NULL, 'Laurel', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2042, 'ella_both', 'ella.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Ella', NULL, 'Cruz', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2043, 'faye_both', 'faye.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Faye', NULL, 'Hall', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2044, 'giselle_both', 'giselle.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Giselle', NULL, 'Toenges', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2045, 'hannah_both', 'hannah.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Hannah', NULL, 'Delacuz', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2046, 'isabelle_both', 'isabelle.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Isabelle', NULL, 'Daza', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2047, 'julia_both', 'julia.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Julia', NULL, 'Barretto', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2048, 'kim_both', 'kim.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Kim', NULL, 'Chiu', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2049, 'lj_both', 'lj.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'LJ', NULL, 'Reyes', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(2050, 'megan_both', 'megan.both@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'both', NULL, 'Megan', NULL, 'Young', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3001, 'staff_john_001', 'john.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'John', 'Michael', 'Santos', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3002, 'staff_maria_001', 'maria.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Maria', 'Grace', 'Cruz', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3003, 'staff_robert_001', 'robert.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Robert', 'James', 'Reyes', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3004, 'staff_anna_001', 'anna.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Anna', 'Marie', 'Gonzales', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3005, 'staff_carlos_001', 'carlos.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Carlos', 'Antonio', 'Fernandez', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3006, 'staff_diana_001', 'diana.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Diana', 'Rose', 'Mercado', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3007, 'staff_miguel_001', 'miguel.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Miguel', 'Luis', 'Ramos', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3008, 'staff_elena_001', 'elena.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Elena', 'Victoria', 'Villanueva', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3009, 'staff_francisco_001', 'francisco.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Francisco', 'Xavier', 'Aquino', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3010, 'staff_rosa_001', 'rosa.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Rosa', 'Magdalena', 'Tolentino', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3011, 'staff_antonio_001', 'antonio.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Antonio', 'Benito', 'Magsaysay', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3012, 'staff_lucia_001', 'lucia.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Lucia', 'Esperanza', 'Bonifacio', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3013, 'staff_manuel_001', 'manuel.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Manuel', 'Emilio', 'Aguinaldo', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3014, 'staff_teresa_001', 'teresa.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Teresa', 'Josefina', 'Pacquiao', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3015, 'staff_ramon_001', 'ramon.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Ramon', 'Domingo', 'Rizal', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3016, 'staff_carmen_001', 'carmen.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Carmen', 'Soledad', 'Quezon', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3017, 'staff_luis_001', 'luis.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Luis', 'Fernando', 'Laurel', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3018, 'staff_isabel_001', 'isabel.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Isabel', 'Catalina', 'Marcos', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3019, 'staff_pedro_001', 'pedro.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Pedro', 'Alejandro', 'Osmeña', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3020, 'staff_beatriz_001', 'beatriz.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Beatriz', 'Angelica', 'Macapagal', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3021, 'staff_jorge_001', 'jorge.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Jorge', 'Salvador', 'Recto', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3022, 'staff_gloria_001', 'gloria.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Gloria', 'Herminia', 'Macapagal', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3023, 'staff_enrique_001', 'enrique.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Enrique', 'Roberto', 'Estrada', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3024, 'staff_patricia_001', 'patricia.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Patricia', 'Adriana', 'Arroyo', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3025, 'staff_victor_001', 'victor.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Victor', 'Guillermo', 'Aquino', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3026, 'staff_sandra_001', 'sandra.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Sandra', 'Margarita', 'Duterte', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3027, 'staff_alberto_001', 'alberto.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Alberto', 'Ignacio', 'Sotto', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3028, 'staff_veronica_001', 'veronica.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Veronica', 'Francesca', 'Hontiveros', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3029, 'staff_salvador_001', 'salvador.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Salvador', 'Bartolome', 'Padilla', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3030, 'staff_natalia_001', 'natalia.staff001@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'owner_staff', NULL, 'Natalia', 'Gabriela', 'Bernardo', NULL, '2026-03-12 00:00:00', '2026-03-12 00:00:00'),
(3031, 'owner_8082', 'tilahe5886@bigonla.com', '$2y$12$FEyUyszZdoo9J44rMGWkgecpBcb.RaliavjxSGjjKLuzr5/TCfmWS', 'admin_created', NULL, 'property_owner', 'contractor', 'John', 'D', 'Cruz', NULL, '2026-03-14 14:06:09', '2026-03-16 02:25:56'),
(3032, 'owner_6881', 'viwopi4272@indevgo.com', '$2y$12$nUcSNcfPOrQwwkE2b.2PsuzwtWl9gU4R8ujsKfWN8ac/XbB4Teg9i', 'admin_created', NULL, 'property_owner', 'contractor', 'Jayz', 'J', 'Jayz', NULL, '2026-03-14 14:23:43', '2026-03-16 11:45:14'),
(3033, 'astag', 'lopavem379@bigonla.com', '$2y$12$w2Yq6JbF0NiSyFIes1.8h.va.faBowaWlboDwGF2bZ49K1FcFIpwq', '$2y$12$Nzum/3OwxGl8Aku0iBDnnOkWgRxrIoM680wniHo4mq00p2LWj2rHC', NULL, 'property_owner', NULL, 'Astag', 'Astag', 'Astag', NULL, '2026-03-14 23:07:18', '2026-03-14 23:07:18');

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_logs`
--

CREATE TABLE `user_activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `activity_type` varchar(60) NOT NULL,
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
(1, 'failed_login_attempt', NULL, NULL, NULL, '{\"attempts\":1,\"ip\":\"127.0.0.1\"}', 1, '2026-03-13 04:19:46'),
(2, 'user_login', 3031, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 1, '2026-03-15 22:21:48'),
(16, 'user_login', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 1, '2026-03-15 23:04:19'),
(17, 'bid_submitted', 3031, 80, 'bid', '{\"project_id\":2027,\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 1, '2026-03-15 23:12:13'),
(18, 'bid_rejected', 3032, 80, 'bid', '{\"project_id\":2027,\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 1, '2026-03-15 23:14:01'),
(22, 'user_login', 3031, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 1, '2026-03-15 23:36:21'),
(23, 'user_login', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 1, '2026-03-15 23:38:36'),
(24, 'user_logout', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 1, '2026-03-15 23:51:59'),
(25, 'account_status_changed', 3032, NULL, NULL, '{\"new_status\":\"unsuspended\",\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 1, '2026-03-15 23:52:18'),
(26, 'account_status_changed', 3032, NULL, NULL, '{\"new_status\":\"suspended\",\"reason\":\"sfwef\",\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 1, '2026-03-15 23:52:37'),
(27, 'account_status_changed', 3032, NULL, NULL, '{\"new_status\":\"unsuspended\",\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 1, '2026-03-15 23:53:00'),
(28, 'bid_cancelled', 3031, 77, 'bid', '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 1, '2026-03-15 23:53:55'),
(29, 'bid_updated', 3031, 78, 'bid', '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 1, '2026-03-15 23:54:16'),
(30, 'user_login', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 1, '2026-03-16 00:20:18'),
(31, 'user_logout', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 1, '2026-03-16 01:27:26'),
(32, 'user_login', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 1, '2026-03-16 01:27:48'),
(33, 'account_status_changed', 3032, NULL, NULL, '{\"new_status\":\"suspended\",\"reason\":\"yes suspended ka na\",\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 02:29:11'),
(34, 'user_logout', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 02:29:38'),
(35, 'user_login', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 02:29:57'),
(36, 'account_status_changed', 3032, NULL, NULL, '{\"new_status\":\"unsuspended\",\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 02:30:13'),
(37, 'account_status_changed', 3032, NULL, NULL, '{\"new_status\":\"deleted\",\"reason\":\"Removed by company owner\",\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 02:31:12'),
(38, 'user_logout', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 02:32:01'),
(39, 'user_login', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 02:39:30'),
(40, 'account_status_changed', 3032, NULL, NULL, '{\"new_status\":\"suspended\",\"reason\":\"dfgdfh\",\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 02:42:18'),
(41, 'user_logout', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 02:42:32'),
(42, 'account_status_changed', 3032, NULL, NULL, '{\"new_status\":\"unsuspended\",\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 02:42:51'),
(43, 'user_login', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 02:43:13'),
(44, 'account_status_changed', 3032, NULL, NULL, '{\"new_status\":\"deleted\",\"reason\":\"Removed by company owner\",\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 02:44:09'),
(45, 'user_logout', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 02:44:21'),
(46, 'user_login', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 02:52:36'),
(47, 'account_status_changed', 3032, NULL, NULL, '{\"new_status\":\"deleted\",\"reason\":\"Removed by company owner\",\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 03:06:18'),
(48, 'user_logout', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 09:19:14'),
(49, 'user_login', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 09:54:44'),
(50, 'user_logout', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 09:55:22'),
(51, 'user_login', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 10:34:10'),
(52, 'user_logout', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 11:11:25'),
(53, 'user_login', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 11:11:45'),
(54, 'user_logout', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 11:36:04'),
(55, 'user_login', 3032, NULL, NULL, '{\"ip\":\"192.168.254.111\",\"source\":\"mobile\"}', 0, '2026-03-16 11:44:50');

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
  `admin_action` varchar(100) DEFAULT NULL,
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
  ADD KEY `conversations_status_index` (`status`),
  ADD KEY `fk_contractor_id` (`contractor_id`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `admin_notification_preferences`
--
ALTER TABLE `admin_notification_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_sent_notifications`
--
ALTER TABLE `admin_sent_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ai_prediction_logs`
--
ALTER TABLE `ai_prediction_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bids`
--
ALTER TABLE `bids`
  MODIFY `bid_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `bid_files`
--
ALTER TABLE `bid_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `content_reports`
--
ALTER TABLE `content_reports`
  MODIFY `report_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contractors`
--
ALTER TABLE `contractors`
  MODIFY `contractor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `contractor_staff`
--
ALTER TABLE `contractor_staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

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
  MODIFY `conversation_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102003034033;

--
-- AUTO_INCREMENT for table `disputes`
--
ALTER TABLE `disputes`
  MODIFY `dispute_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `message_attachments`
--
ALTER TABLE `message_attachments`
  MODIFY `attachment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `milestones`
--
ALTER TABLE `milestones`
  MODIFY `milestone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `milestone_date_histories`
--
ALTER TABLE `milestone_date_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `milestone_items`
--
ALTER TABLE `milestone_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

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
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

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
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `platform_payments`
--
ALTER TABLE `platform_payments`
  MODIFY `platform_payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

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
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2028;

--
-- AUTO_INCREMENT for table `project_files`
--
ALTER TABLE `project_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `project_relationships`
--
ALTER TABLE `project_relationships`
  MODIFY `rel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2028;

--
-- AUTO_INCREMENT for table `project_updates`
--
ALTER TABLE `project_updates`
  MODIFY `extension_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_owners`
--
ALTER TABLE `property_owners`
  MODIFY `owner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=284;

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
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3034;

--
-- AUTO_INCREMENT for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

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
  ADD CONSTRAINT `conversations_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_contractor_id` FOREIGN KEY (`contractor_id`) REFERENCES `contractors` (`contractor_id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
