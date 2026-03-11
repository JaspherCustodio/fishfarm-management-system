-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 08, 2026 at 09:49 AM
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
-- Database: `users_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `stocking_id` int(11) NOT NULL,
  `cage_id` int(11) NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `quantity_delivered` int(11) NOT NULL,
  `sale_amount` decimal(10,2) DEFAULT NULL,
  `buyer_name` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_to` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('Pending','Ongoing','Completed','Cancelled') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`id`, `schedule_id`, `stocking_id`, `cage_id`, `delivery_date`, `quantity_delivered`, `sale_amount`, `buyer_name`, `remarks`, `created_at`, `assigned_to`, `status`) VALUES
(14, NULL, 38, 14, '2026-03-02', 800, 112000.00, 'Doctora Meldred', '', '2026-02-19 06:11:36', 5, 'Completed'),
(18, 221, 67, 15, '2026-02-24', 90, 0.00, '0', '0', '2026-02-23 07:04:15', 5, 'Pending'),
(20, NULL, 66, 15, '2026-02-23', 33, NULL, NULL, NULL, '2026-02-23 09:00:21', 5, 'Pending'),
(21, NULL, 55, 14, '2026-02-23', 5, NULL, NULL, NULL, '2026-02-23 09:00:38', 5, 'Pending'),
(22, NULL, 54, 14, '2026-02-23', 45, NULL, NULL, NULL, '2026-02-23 09:07:01', 5, 'Pending'),
(23, NULL, 69, 14, '2026-02-23', 45, NULL, NULL, NULL, '2026-02-23 09:07:42', 5, 'Pending'),
(24, NULL, 70, 14, '2026-02-23', 45, NULL, NULL, NULL, '2026-02-23 09:11:45', 5, 'Pending'),
(25, NULL, 54, 14, '2026-02-23', 3, NULL, NULL, NULL, '2026-02-23 09:12:05', 5, 'Pending'),
(26, NULL, 54, 14, '2026-02-23', 3, NULL, NULL, NULL, '2026-02-23 09:12:16', 5, 'Pending'),
(27, NULL, 57, 14, '2026-02-23', 49, NULL, NULL, NULL, '2026-02-23 09:12:48', 5, 'Pending'),
(28, NULL, 55, 14, '2026-02-23', 5, NULL, NULL, NULL, '2026-02-23 09:15:56', 5, 'Pending'),
(29, NULL, 56, 15, '2026-02-23', 50, NULL, NULL, NULL, '2026-02-23 09:17:56', 5, 'Pending'),
(30, NULL, 57, 14, '2026-02-24', 1, NULL, NULL, NULL, '2026-02-24 02:53:32', 5, 'Pending'),
(31, NULL, 57, 14, '2026-02-24', 50, NULL, NULL, NULL, '2026-02-24 02:58:59', 5, 'Pending'),
(33, NULL, 64, 15, NULL, 1, NULL, NULL, NULL, '2026-02-24 04:57:36', 5, 'Pending'),
(34, NULL, 74, 14, NULL, 67, NULL, NULL, NULL, '2026-02-27 05:11:16', 5, 'Pending'),
(35, NULL, 75, 14, '2026-02-27', 67, 100.00, '', '', '2026-02-27 05:12:29', 5, 'Completed'),
(36, NULL, 84, 14, NULL, 95, NULL, NULL, NULL, '2026-03-02 02:51:47', 5, 'Pending'),
(37, NULL, 89, 14, NULL, 333, NULL, NULL, NULL, '2026-03-02 03:11:12', 5, 'Pending'),
(38, NULL, 93, 13, '2026-03-06', 67, 0.00, '', '', '2026-03-02 03:14:25', 5, 'Completed'),
(39, NULL, 97, 14, '2026-03-06', 777, 0.00, '', '', '2026-03-06 12:01:12', 5, 'Ongoing');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `expense_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `expense_date`, `amount`, `category`, `description`, `created_by`, `created_at`) VALUES
(2, '2026-01-10', 6500.00, 'Equipment', NULL, 1, '2026-02-10 02:42:16'),
(3, '2026-01-06', 12000.00, 'Fish Stocks', NULL, 1, '2026-02-10 03:01:30'),
(6, '2026-01-12', 2800.00, 'Utilities', NULL, 1, '2026-02-12 05:50:11'),
(7, '2026-01-15', 3200.00, 'Transportation', NULL, 1, '2026-02-12 05:50:28'),
(8, '2026-01-18', 4000.00, 'Equipment Maintenance', NULL, 1, '2026-02-12 05:50:50'),
(9, '2026-01-20', 5000.00, 'Labor', NULL, 1, '2026-02-12 05:52:22'),
(10, '2026-01-22', 1500.00, 'Others', NULL, 1, '2026-02-12 05:52:43'),
(12, '2026-02-05', 2900.00, 'Utilities', NULL, 1, '2026-02-12 05:58:16'),
(13, '2026-02-08', 3800.00, 'Transportation', NULL, 1, '2026-02-12 05:58:58'),
(15, '2026-02-27', 1.00, 'Equipment', NULL, 1, '2026-02-27 15:16:11'),
(16, '2026-03-02', 95000.00, 'Fish Feeds', NULL, 1, '2026-02-27 23:53:29'),
(17, '2026-02-28', 12000.00, 'Fish Feeds', NULL, 1, '2026-02-27 23:53:38'),
(18, '2026-03-03', 5.00, 'Equipment', NULL, 1, '2026-03-03 06:49:45');

-- --------------------------------------------------------

--
-- Table structure for table `feedings`
--

CREATE TABLE `feedings` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `feeding_date` date DEFAULT NULL,
  `cage_id` int(11) NOT NULL,
  `feed_type` varchar(100) NOT NULL,
  `amount` decimal(6,2) NOT NULL,
  `unit` enum('kg','g') NOT NULL,
  `fed_time` time DEFAULT NULL,
  `status` enum('Pending','Ongoing','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_to` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedings`
--

INSERT INTO `feedings` (`id`, `schedule_id`, `feeding_date`, `cage_id`, `feed_type`, `amount`, `unit`, `fed_time`, `status`, `created_at`, `assigned_to`) VALUES
(2, 88, NULL, 13, 'Floating Feed', 8.00, 'kg', '06:51:00', 'Completed', '2026-02-13 10:48:55', NULL),
(3, 89, NULL, 14, 'Floating Feed', 18.00, 'kg', '06:57:00', 'Completed', '2026-02-13 10:49:45', NULL),
(4, 90, '2026-03-07', 15, 'Floating Feed', 8.00, 'kg', '18:47:00', 'Ongoing', '2026-02-13 10:50:16', 5),
(6, 92, NULL, 13, 'Floating Feed', 22.00, 'kg', '18:24:00', 'Completed', '2026-02-13 10:53:44', NULL),
(7, 93, NULL, 13, 'Floating Feed', 25.00, 'kg', '18:16:00', 'Completed', '2026-02-13 10:55:37', NULL),
(8, 140, '2026-02-21', 13, 'Floating Feed', 0.00, 'kg', '23:36:00', 'Completed', '2026-02-17 01:36:50', 5),
(9, 70, NULL, 13, '', 0.00, 'kg', NULL, 'Pending', '2026-02-21 03:34:39', 5),
(10, 140, NULL, 13, '', 0.00, 'kg', NULL, 'Pending', '2026-02-21 03:42:59', 5),
(11, 188, NULL, 18, '-', 0.00, 'kg', NULL, 'Pending', '2026-02-21 07:32:21', NULL),
(12, 228, NULL, 18, '-', 0.00, 'kg', NULL, 'Pending', '2026-02-27 06:52:07', NULL),
(13, 229, NULL, 17, '', 0.00, 'kg', NULL, 'Pending', '2026-02-27 06:53:40', NULL),
(14, 254, NULL, 13, '', 0.00, 'kg', NULL, 'Pending', '2026-03-06 11:56:58', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `fish_cages`
--

CREATE TABLE `fish_cages` (
  `id` int(11) NOT NULL,
  `cage_name` varchar(100) NOT NULL,
  `date_added` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fish_cages`
--

INSERT INTO `fish_cages` (`id`, `cage_name`, `date_added`) VALUES
(13, 'Nursery Cage', '2026-02-07 22:06:26'),
(14, 'Cage A', '2026-02-07 22:06:35'),
(15, 'Cage B', '2026-02-07 22:06:46'),
(16, 'Cage C', '2026-02-12 14:01:13'),
(17, 'Cage D', '2026-02-13 18:22:50'),
(18, 'Cage E', '2026-02-13 18:23:00');

-- --------------------------------------------------------

--
-- Table structure for table `fish_cage_management`
--

CREATE TABLE `fish_cage_management` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `result` varchar(100) NOT NULL,
  `optimum_level` varchar(100) NOT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('Pending','Ongoing','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_to` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fish_cage_management`
--

INSERT INTO `fish_cage_management` (`id`, `schedule_id`, `date`, `result`, `optimum_level`, `remarks`, `status`, `created_at`, `assigned_to`) VALUES
(1, 69, '2026-01-05', 'Avg Weight: 35g', '30-50g', 'Healthy fingerlings', 'Completed', '2026-02-09 00:54:19', 5),
(2, 80, '2026-01-07', 'Feed Consumption: 92%', '90-100%', 'Normal feeding activity', 'Completed', '2026-02-09 09:05:25', 0),
(3, 69, '2026-01-03', 'Salinity: 32 ppt', '28-35 ppt', 'Within normal marine range', 'Completed', '2026-02-13 10:27:56', 5),
(4, 81, '2026-02-17', 'Feeding delayed(weather)', 'On-time schedule', 'good', 'Pending', '2026-02-13 10:31:58', 16),
(5, 82, '2026-02-17', 'Salinity: 32 ppt', 'On-time schedule', 'good', 'Completed', '2026-02-13 10:32:24', 5),
(6, 80, '2026-02-21', 'Heavy marine algae', 'Clean surface required', 'Schedule cleaning team', 'Pending', '2026-02-13 10:33:33', 0),
(7, 83, '2026-02-20', 'good', 'good', 'q', 'Pending', '2026-02-13 10:34:49', 5),
(8, 83, NULL, '', '', '', 'Completed', '2026-02-13 10:35:45', 5),
(9, 134, '0000-00-00', '', '', NULL, 'Completed', '2026-02-14 03:32:15', 0),
(10, 138, '0000-00-00', '', '', NULL, 'Pending', '2026-02-17 01:35:38', 0),
(11, 183, '0000-00-00', '', '', NULL, 'Pending', '2026-02-20 08:35:27', 0),
(12, 184, NULL, '', '', '', 'Completed', '2026-02-20 08:38:31', 5),
(13, 69, NULL, '', '', '', 'Pending', '2026-02-20 09:42:54', 5),
(14, 69, NULL, '', '', '', 'Pending', '2026-02-20 09:43:12', 16),
(15, 83, '2026-02-20', '', '', '', 'Completed', '2026-02-20 09:47:06', 5),
(16, 183, '2026-02-20', '', '', '', 'Completed', '2026-02-20 10:07:02', 5),
(17, 185, NULL, '', '', '', 'Ongoing', '2026-02-20 10:12:47', 5),
(18, 185, '2026-02-21', '', '', '', 'Completed', '2026-02-21 02:50:59', 5),
(19, 185, '2026-02-21', '', '', '', 'Pending', '2026-02-21 02:51:18', 16),
(20, 69, NULL, '', '', '', 'Pending', '2026-02-21 02:53:33', 16),
(21, 185, NULL, '', '', '', 'Pending', '2026-02-21 02:53:46', 16),
(22, 69, NULL, '', '', '', 'Pending', '2026-02-21 03:43:31', 5),
(23, 186, NULL, '', '', NULL, 'Pending', '2026-02-21 07:31:48', 0),
(24, 230, NULL, '', '', NULL, 'Pending', '2026-02-27 06:57:08', 0),
(25, 252, NULL, '', '', NULL, 'Pending', '2026-03-06 11:56:22', 0);

-- --------------------------------------------------------

--
-- Table structure for table `net_checking`
--

CREATE TABLE `net_checking` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `status` enum('Pending','Ongoing','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_to` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `net_cleaning`
--

CREATE TABLE `net_cleaning` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `status` enum('Pending','Ongoing','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `net_cleaning`
--

INSERT INTO `net_cleaning` (`id`, `schedule_id`, `assigned_to`, `start_date`, `start_time`, `end_date`, `end_time`, `status`, `created_at`) VALUES
(2, 115, NULL, '2026-01-05', '10:17:00', '2026-01-05', '10:30:00', 'Completed', '2026-02-13 13:07:36'),
(3, 116, NULL, '2026-01-01', '07:16:00', '2026-01-01', '10:27:00', 'Ongoing', '2026-02-13 13:08:14'),
(4, 117, NULL, '2026-01-09', '09:18:00', '2026-01-09', '11:30:00', 'Completed', '2026-02-13 13:08:48'),
(5, 118, NULL, '2026-01-01', '07:19:00', '2026-01-01', '10:31:00', 'Completed', '2026-02-13 13:09:10'),
(6, 119, NULL, '2026-01-06', '07:20:00', '2026-01-06', '11:20:00', 'Completed', '2026-02-13 13:09:31'),
(7, 120, NULL, '2026-01-08', '07:20:00', '2026-01-08', '10:20:00', 'Completed', '2026-02-13 13:10:16'),
(8, 137, 5, '2026-02-24', '07:11:00', '2026-02-24', '20:12:00', 'Pending', '2026-02-17 00:51:19'),
(9, 139, NULL, NULL, NULL, NULL, NULL, 'Pending', '2026-02-17 01:36:24'),
(10, 66, 5, NULL, NULL, NULL, NULL, 'Completed', '2026-02-24 05:24:37'),
(12, 66, 5, NULL, NULL, NULL, NULL, 'Pending', '2026-02-27 01:12:27'),
(13, 233, NULL, NULL, NULL, NULL, NULL, 'Pending', '2026-02-27 07:01:17'),
(14, 234, NULL, NULL, NULL, NULL, NULL, 'Pending', '2026-02-27 07:21:53');

-- --------------------------------------------------------

--
-- Table structure for table `net_repairing`
--

CREATE TABLE `net_repairing` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `status` enum('Pending','Ongoing','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_to` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `net_repairing`
--

INSERT INTO `net_repairing` (`id`, `schedule_id`, `start_date`, `start_time`, `end_date`, `end_time`, `status`, `created_at`, `assigned_to`) VALUES
(2, 121, '2026-01-02', '07:30:00', '2026-01-02', '11:27:00', 'Pending', '2026-02-13 13:22:54', NULL),
(3, 122, '2026-01-08', '07:35:00', '2026-01-08', '11:32:00', 'Pending', '2026-02-13 13:23:13', NULL),
(4, 123, '2026-01-14', '07:33:00', '2026-01-14', '11:30:00', 'Pending', '2026-02-13 13:23:42', NULL),
(5, 124, '2026-01-21', '07:29:00', '2026-01-21', '10:34:00', 'Completed', '2026-02-13 13:24:10', NULL),
(7, 126, NULL, NULL, NULL, NULL, 'Completed', '2026-02-13 13:25:56', NULL),
(8, 72, '2026-02-27', '07:01:00', '2026-02-27', '22:17:00', 'Completed', '2026-02-27 00:51:16', 5),
(9, 223, NULL, NULL, NULL, NULL, 'Pending', '2026-02-27 01:07:30', NULL),
(10, 223, NULL, NULL, NULL, NULL, 'Completed', '2026-02-27 01:07:46', NULL),
(11, 232, NULL, NULL, NULL, NULL, 'Pending', '2026-02-27 07:00:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 2, 'New Task Assigned', 'You have been assigned a Net Cleaning task scheduled on Feb 07, 2026 10:03 PM', 1, '2026-02-07 10:03:26'),
(2, 2, 'New Task Assigned', 'You have been assigned a Net Checking task scheduled on Feb 07, 2026 10:10 PM', 1, '2026-02-07 10:10:49'),
(3, 5, 'New Task Assigned', 'You have been assigned a Net Cleaning task scheduled on Feb 07, 2026 10:18 PM', 1, '2026-02-07 10:18:10'),
(4, 2, 'New Task Assigned', 'You have been assigned a  task scheduled on Jan 01, 1970 01:00 AM', 1, '2026-02-07 14:01:49'),
(5, 2, 'New Task Assigned', 'You have been assigned a  task scheduled on Jan 01, 1970 01:00 AM', 1, '2026-02-07 14:03:00'),
(6, 2, 'New Task Assigned', 'You have been assigned a  task scheduled on Jan 01, 1970 01:00 AM', 1, '2026-02-07 17:22:50'),
(7, 2, 'New Task Assigned', 'You have been assigned a  task scheduled on Jan 01, 1970 01:00 AM', 1, '2026-02-07 17:52:16'),
(8, 2, 'New Task Assigned', 'You have been assigned a  task scheduled on Jan 01, 1970 01:00 AM', 1, '2026-02-07 17:54:16'),
(9, 2, 'New Task Assigned', 'You have been assigned a Net Cleaning task scheduled on Feb 09, 2026 10:30 AM', 1, '2026-02-07 22:07:54'),
(10, 2, 'New Task Assigned', 'You have been assigned a Sampling task scheduled on Feb 08, 2026 10:30 AM', 1, '2026-02-07 22:11:58'),
(11, 5, 'New Task Assigned', 'You have been assigned a Stocking task scheduled on Feb 10, 2026 10:30 AM', 1, '2026-02-07 22:18:54'),
(12, 2, 'New Task Assigned', 'You have been assigned a Fish Cage Management task scheduled on Feb 10, 2026 11:54 AM', 1, '2026-02-09 08:54:19'),
(13, 2, 'New Task Assigned', 'You have been assigned a Feeding task scheduled on Feb 14, 2026 10:29 PM', 1, '2026-02-09 08:54:52'),
(14, 2, 'New Task Assigned', 'You have been assigned a Stocking task scheduled on Feb 16, 2026 06:30 AM', 1, '2026-02-09 08:55:26'),
(15, 2, 'New Task Assigned', 'You have been assigned a  task scheduled on Jan 01, 1970 01:00 AM', 1, '2026-02-09 08:57:11'),
(16, 2, 'New Task Assigned', 'You have been assigned a  task scheduled on Jan 01, 1970 01:00 AM', 1, '2026-02-09 08:58:16'),
(17, 2, 'New Task Assigned', 'You have been assigned a Net Repairing task scheduled on Feb 22, 2026 10:00 PM', 1, '2026-02-09 08:58:46'),
(18, 2, 'New Task Assigned', 'You have been assigned a Net Checking task scheduled on Feb 24, 2026 08:00 AM', 1, '2026-02-09 08:59:25'),
(19, 2, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 09, 2026 12:00 AM', 1, '2026-02-09 10:46:48'),
(20, 2, 'New Task Assigned', 'You have been assigned a Delivery task scheduled on Feb 09, 2026 12:00 AM', 1, '2026-02-09 10:53:07'),
(21, 2, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 09, 2026 12:00 AM', 1, '2026-02-09 11:39:10'),
(22, 2, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 09, 2026 12:00 AM', 1, '2026-02-09 11:51:35'),
(23, 2, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 09, 2026 12:00 AM', 1, '2026-02-09 12:00:18'),
(24, 1, NULL, 'Janjan completed a Transferring task.', 1, '2026-02-09 16:49:59'),
(25, 1, NULL, 'Janjan completed a Transferring task.', 1, '2026-02-09 16:50:05'),
(26, 2, 'New Task Assigned', 'You have been assigned a Fish Cage Management task scheduled on Feb 09, 2026 05:05 PM', 1, '2026-02-09 17:05:25'),
(27, 1, NULL, 'Janjan completed a Fish Cage task.', 1, '2026-02-09 17:06:21'),
(28, 5, 'New Task Assigned', 'You have been assigned a Fish Cage Management task scheduled on Jan 11, 2026 08:10 AM', 1, '2026-02-13 18:31:58'),
(29, 5, 'New Task Assigned', 'You have been assigned a Fish Cage Management task scheduled on Feb 14, 2026 09:32 AM', 1, '2026-02-13 18:32:24'),
(30, 5, 'New Task Assigned', 'You have been assigned a Fish Cage Management task scheduled on Feb 24, 2026 11:01 PM', 1, '2026-02-13 18:34:49'),
(31, 5, 'New Task Assigned', 'You have been assigned a Sampling task scheduled on Jan 05, 2026 09:39 AM', 1, '2026-02-13 18:39:40'),
(32, 5, 'New Task Assigned', 'You have been assigned a Sampling task scheduled on Jan 13, 2026 08:25 AM', 1, '2026-02-13 18:40:08'),
(33, 5, 'New Task Assigned', 'You have been assigned a Sampling task scheduled on Feb 18, 2026 09:59 AM', 1, '2026-02-13 18:40:39'),
(34, 5, 'New Task Assigned', 'You have been assigned a Sampling task scheduled on Jan 29, 2026 09:40 AM', 1, '2026-02-13 18:41:16'),
(35, 5, 'New Task Assigned', 'You have been assigned a Feeding task scheduled on Jan 03, 2026 06:48 AM', 1, '2026-02-13 18:48:55'),
(36, 5, 'New Task Assigned', 'You have been assigned a Feeding task scheduled on Jan 04, 2026 06:49 AM', 1, '2026-02-13 18:49:45'),
(37, 5, 'New Task Assigned', 'You have been assigned a Feeding task scheduled on Jan 05, 2026 06:15 AM', 1, '2026-02-13 18:50:16'),
(38, 5, 'New Task Assigned', 'You have been assigned a Feeding task scheduled on Jan 05, 2026 06:25 AM', 1, '2026-02-13 18:52:59'),
(39, 5, 'New Task Assigned', 'You have been assigned a Feeding task scheduled on Jan 05, 2026 06:10 AM', 1, '2026-02-13 18:53:44'),
(40, 5, 'New Task Assigned', 'You have been assigned a Feeding task scheduled on Jan 03, 2026 06:22 AM', 1, '2026-02-13 18:55:37'),
(41, 5, 'New Task Assigned', 'You have been assigned a Stocking task scheduled on Mar 02, 2025 07:30 AM', 1, '2026-02-13 19:08:46'),
(42, 5, 'New Task Assigned', 'You have been assigned a Stocking task scheduled on Mar 20, 2025 08:50 AM', 1, '2026-02-13 19:09:17'),
(43, 5, 'New Task Assigned', 'You have been assigned a Stocking task scheduled on Mar 20, 2025 09:50 AM', 1, '2026-02-13 19:10:17'),
(44, 5, 'New Task Assigned', 'You have been assigned a Stocking task scheduled on Feb 20, 2026 09:35 AM', 1, '2026-02-13 19:10:53'),
(45, 5, 'New Task Assigned', 'You have been assigned a Stocking task scheduled on Feb 28, 2026 07:25 AM', 1, '2026-02-13 19:11:17'),
(46, 5, 'New Task Assigned', 'You have been assigned a Stocking task scheduled on Feb 28, 2026 07:11 AM', 1, '2026-02-13 19:12:04'),
(47, 5, 'New Task Assigned', 'You have been assigned a Stocking task scheduled on Apr 05, 2025 07:19 AM', 1, '2026-02-13 19:13:40'),
(48, 5, 'New Task Assigned', 'You have been assigned a Stocking task scheduled on Mar 20, 2025 07:33 AM', 1, '2026-02-13 19:14:27'),
(49, 5, 'New Task Assigned', 'You have been assigned a Stocking task scheduled on Mar 13, 2025 07:14 AM', 1, '2026-02-13 19:14:45'),
(50, 5, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Apr 02, 2025 12:00 AM', 1, '2026-02-13 19:28:35'),
(51, 5, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Apr 20, 2025 12:00 AM', 1, '2026-02-13 19:29:31'),
(52, 5, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Apr 20, 2025 12:00 AM', 1, '2026-02-13 19:30:46'),
(53, 5, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on May 05, 2025 12:00 AM', 1, '2026-02-13 19:32:02'),
(54, 5, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Apr 13, 2026 12:00 AM', 1, '2026-02-13 19:33:38'),
(55, 5, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Apr 13, 2025 12:00 AM', 1, '2026-02-13 19:34:36'),
(56, 5, 'New Task Assigned', 'You have been assigned a Delivery task scheduled on Aug 02, 2025 12:00 AM', 1, '2026-02-13 20:57:34'),
(57, 5, 'New Task Assigned', 'You have been assigned a Delivery task scheduled on Aug 20, 2025 12:00 AM', 1, '2026-02-13 20:58:41'),
(58, 5, 'New Task Assigned', 'You have been assigned a Delivery task scheduled on Sep 05, 2025 12:00 AM', 1, '2026-02-13 21:00:25'),
(59, 5, 'New Task Assigned', 'You have been assigned a Delivery task scheduled on Aug 20, 2025 12:00 AM', 1, '2026-02-13 21:01:24'),
(60, 5, 'New Task Assigned', 'You have been assigned a Delivery task scheduled on Aug 20, 2026 12:00 AM', 1, '2026-02-13 21:04:26'),
(61, 5, 'New Task Assigned', 'You have been assigned a Delivery task scheduled on Aug 13, 2025 12:00 AM', 1, '2026-02-13 21:05:57'),
(62, 5, 'New Task Assigned', 'You have been assigned a Net Cleaning task scheduled on Jan 01, 2026 07:01 AM', 1, '2026-02-13 21:07:36'),
(63, 5, 'New Task Assigned', 'You have been assigned a Net Cleaning task scheduled on Jan 01, 2026 07:10 AM', 1, '2026-02-13 21:08:14'),
(64, 5, 'New Task Assigned', 'You have been assigned a Net Cleaning task scheduled on Jan 01, 2026 07:01 AM', 1, '2026-02-13 21:08:48'),
(65, 5, 'New Task Assigned', 'You have been assigned a Net Cleaning task scheduled on Jan 01, 2026 07:00 AM', 1, '2026-02-13 21:09:10'),
(66, 5, 'New Task Assigned', 'You have been assigned a Net Cleaning task scheduled on Jan 01, 2026 07:09 AM', 1, '2026-02-13 21:09:31'),
(67, 5, 'New Task Assigned', 'You have been assigned a Net Cleaning task scheduled on Feb 01, 2026 07:00 AM', 1, '2026-02-13 21:10:16'),
(68, 5, 'New Task Assigned', 'You have been assigned a Net Repairing task scheduled on Jan 02, 2026 07:00 AM', 1, '2026-02-13 21:22:54'),
(69, 5, 'New Task Assigned', 'You have been assigned a Net Repairing task scheduled on Jan 08, 2026 06:20 AM', 1, '2026-02-13 21:23:13'),
(70, 5, 'New Task Assigned', 'You have been assigned a Net Repairing task scheduled on Jan 14, 2026 07:14 AM', 1, '2026-02-13 21:23:42'),
(71, 5, 'New Task Assigned', 'You have been assigned a Net Repairing task scheduled on Jan 21, 2026 07:09 PM', 1, '2026-02-13 21:24:10'),
(72, 5, 'New Task Assigned', 'You have been assigned a Net Repairing task scheduled on Jan 27, 2026 07:25 AM', 1, '2026-02-13 21:25:16'),
(73, 5, 'New Task Assigned', 'You have been assigned a Net Repairing task scheduled on Jan 24, 2026 07:25 AM', 1, '2026-02-13 21:25:56'),
(74, 5, 'New Task Assigned', 'You have been assigned a Net Checking task scheduled on Jan 09, 2026 07:06 AM', 1, '2026-02-13 21:32:24'),
(75, 5, 'New Task Assigned', 'You have been assigned a Net Checking task scheduled on Jan 07, 2026 07:10 AM', 1, '2026-02-13 21:32:45'),
(76, 5, 'New Task Assigned', 'You have been assigned a Net Checking task scheduled on Jan 21, 2026 07:10 AM', 1, '2026-02-13 21:33:15'),
(77, 5, 'New Task Assigned', 'You have been assigned a Net Checking task scheduled on Feb 11, 2026 07:00 AM', 1, '2026-02-13 21:33:45'),
(78, 5, 'New Task Assigned', 'You have been assigned a Net Checking task scheduled on Feb 01, 2026 07:01 AM', 1, '2026-02-13 21:34:14'),
(79, 5, 'New Task Assigned', 'You have been assigned a Net Checking task scheduled on Feb 07, 2026 07:02 AM', 1, '2026-02-13 21:34:35'),
(80, 1, NULL, 'Roberto Cesar completed a Net Repairing task', 1, '2026-02-13 21:39:28'),
(81, 5, 'New Task Assigned', 'You have been assigned a Stocking task scheduled on Dec 13, 2025 10:14 AM', 1, '2026-02-13 22:15:15'),
(82, 5, 'New Task Assigned', 'You have been assigned a Fish Cage Management task scheduled on Jan 01, 2026 11:32 AM', 1, '2026-02-14 11:32:15'),
(83, 1, NULL, 'Roberto Cesar completed a Fish Cage task', 1, '2026-02-14 11:33:42'),
(84, 1, NULL, 'Roberto Cesar completed a Fish Cage task', 1, '2026-02-14 15:51:29'),
(85, 1, NULL, 'Roberto Cesar completed a Sampling task', 1, '2026-02-14 15:52:11'),
(86, 1, NULL, 'Roberto Cesar completed a Feeding task', 1, '2026-02-14 15:52:19'),
(87, 1, NULL, 'Roberto Cesar completed a Stocking task', 1, '2026-02-14 15:52:25'),
(88, 1, NULL, 'Roberto Cesar completed a Stocking task', 1, '2026-02-14 15:52:29'),
(89, 1, NULL, 'Roberto Cesar completed a Stocking task', 1, '2026-02-14 15:52:38'),
(90, 1, NULL, 'Roberto Cesar completed a Transferring task', 1, '2026-02-14 15:52:49'),
(91, 1, NULL, 'Roberto Cesar completed a Delivering task', 1, '2026-02-14 15:52:54'),
(92, 1, NULL, 'Roberto Cesar completed a Net Cleaning task', 1, '2026-02-14 15:53:01'),
(93, 1, NULL, 'Roberto Cesar completed a Net Cleaning task', 1, '2026-02-14 15:53:04'),
(94, 1, NULL, 'Roberto Cesar completed a Net Repairing task', 1, '2026-02-14 15:53:11'),
(95, 1, NULL, 'Roberto Cesar completed a Net Checking task', 1, '2026-02-14 15:53:17'),
(96, 1, NULL, 'Roberto Cesar completed a Sampling task', 1, '2026-02-16 17:03:42'),
(97, 1, NULL, 'Roberto Cesar completed a Sampling task', 1, '2026-02-16 17:03:48'),
(98, 1, NULL, 'Roberto Cesar completed a Sampling task', 1, '2026-02-16 17:03:52'),
(99, 1, NULL, 'Roberto Cesar completed a Feeding task', 1, '2026-02-16 17:04:06'),
(100, 1, NULL, 'Roberto Cesar completed a Feeding task', 1, '2026-02-16 17:04:11'),
(101, 1, NULL, 'Roberto Cesar completed a Net Cleaning task', 1, '2026-02-16 17:04:21'),
(102, 1, NULL, 'Roberto Cesar completed a Net Repairing task', 1, '2026-02-16 17:04:28'),
(103, 1, NULL, 'Roberto Cesar completed a Net Repairing task', 1, '2026-02-16 17:04:32'),
(104, 1, NULL, 'Roberto Cesar completed a Net Checking task', 1, '2026-02-16 17:04:39'),
(105, 1, NULL, 'Roberto Cesar completed a Net Checking task', 1, '2026-02-16 17:05:03'),
(106, 1, NULL, 'Roberto Cesar completed a Delivering task', 1, '2026-02-16 17:05:12'),
(107, 1, NULL, 'Roberto Cesar completed a Net Repairing task', 1, '2026-02-16 17:09:22'),
(108, 1, NULL, 'Roberto Cesar completed a Net Checking task', 1, '2026-02-16 17:09:31'),
(109, 1, NULL, 'Roberto Cesar completed a Fish Cage task', 1, '2026-02-16 17:09:44'),
(110, 5, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 16, 2026 12:00 AM', 1, '2026-02-16 17:15:15'),
(111, 5, 'New Task Assigned', 'You have been assigned a Stocking task scheduled on Feb 16, 2026 06:11 PM', 1, '2026-02-16 18:11:42'),
(112, 5, 'New Task Assigned', 'You have been assigned a Net Cleaning task scheduled on Feb 17, 2026 08:51 AM', 1, '2026-02-17 08:51:19'),
(113, 5, 'New Task Assigned', 'You have been assigned a Fish Cage Management task scheduled on Jan 01, 2026 09:35 AM', 1, '2026-02-17 09:35:38'),
(114, 5, 'New Task Assigned', 'You have been assigned a Net Cleaning task scheduled on Feb 08, 2026 09:36 AM', 1, '2026-02-17 09:36:24'),
(115, 5, 'New Task Assigned', 'You have been assigned a Feeding task scheduled on Feb 18, 2026 09:36 AM', 1, '2026-02-17 09:36:50'),
(116, 5, 'New Task Assigned', 'You have been assigned a Delivery task scheduled on Feb 17, 2026 12:00 AM', 1, '2026-02-17 10:42:52'),
(117, 1, NULL, 'Roberto Cesar completed a Delivering task', 1, '2026-02-17 12:41:19'),
(118, 1, NULL, 'Roberto Cesar completed a Delivering task', 1, '2026-02-17 12:41:26'),
(119, 16, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 18, 2026 12:00 AM', 0, '2026-02-18 17:42:32'),
(120, 5, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 18, 2026 12:00 AM', 1, '2026-02-18 18:04:26'),
(121, 16, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 18, 2026 12:00 AM', 0, '2026-02-18 18:08:00'),
(122, 16, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 19, 2026 12:00 AM', 0, '2026-02-19 12:12:51'),
(123, 16, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 19, 2026 12:00 AM', 0, '2026-02-19 12:17:00'),
(124, 16, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 19, 2026 12:00 AM', 0, '2026-02-19 12:17:14'),
(125, 16, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 19, 2026 12:00 AM', 0, '2026-02-19 12:17:38'),
(126, 16, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 19, 2026 12:00 AM', 0, '2026-02-19 12:21:56'),
(127, 16, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 19, 2026 12:00 AM', 0, '2026-02-19 12:37:11'),
(128, 16, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 19, 2026 12:00 AM', 0, '2026-02-19 12:37:53'),
(129, 16, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 19, 2026 12:00 AM', 0, '2026-02-19 12:41:21'),
(130, 16, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 19, 2026 12:00 AM', 0, '2026-02-19 12:45:15'),
(131, 16, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 19, 2026 12:00 AM', 0, '2026-02-19 12:46:56'),
(132, 16, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 19, 2026 12:00 AM', 0, '2026-02-19 12:50:32'),
(133, 16, 'New Task Assigned', 'You have been assigned a Stocking task scheduled on Feb 19, 2026 12:51 PM', 0, '2026-02-19 12:51:25'),
(134, 16, 'New Task Assigned', 'You have been assigned a Stocking task scheduled on Feb 19, 2026 01:13 PM', 0, '2026-02-19 13:13:04'),
(135, 16, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 19, 2026 12:00 AM', 0, '2026-02-19 13:18:08'),
(136, 16, 'New Task Assigned', 'You have been assigned a Stocking task scheduled on Feb 19, 2026 01:31 PM', 0, '2026-02-19 13:31:12'),
(137, 16, 'New Task Assigned', 'You have been assigned a Stocking task scheduled on Feb 19, 2026 01:31 PM', 0, '2026-02-19 13:31:26'),
(138, 16, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 19, 2026 12:00 AM', 0, '2026-02-19 13:34:02'),
(139, 16, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 19, 2026 12:00 AM', 0, '2026-02-19 13:37:02'),
(140, 16, 'New Task Assigned', 'You have been assigned a Transfer task scheduled on Feb 19, 2026 12:00 AM', 0, '2026-02-19 13:37:30'),
(141, 5, NULL, 'You have been assigned a Stocking task.', 1, '2026-02-19 15:48:40'),
(142, 5, 'New Task Assigned', 'You have been assigned a Stocking task scheduled on Feb 19, 2026 03:53 PM', 1, '2026-02-19 15:53:30'),
(143, 5, 'New Task Assigned', 'You have been assigned a Stocking task scheduled on Feb 19, 2026 04:13 PM', 1, '2026-02-19 16:13:11'),
(144, 16, NULL, 'You have been assigned a Stocking task.', 0, '2026-02-19 16:26:08'),
(145, 5, 'New Fish Cage Task Assigned', 'You have been assigned a Fish Cage Management task for cage \"Cage B\" scheduled on Feb 24 2026 11:01 PM.', 1, '2026-02-20 17:47:06'),
(146, 5, 'New Fish Cage Task Assigned', 'You have been assigned a Fish Cage Management task for cage \"Nursery Cage\" scheduled on Feb 20 2026 04:35 PM', 1, '2026-02-20 18:07:02'),
(147, 5, 'New Task Assigned', 'You have been assigned a new Fish Cage Management task.', 1, '2026-02-20 19:02:54'),
(148, 5, 'New Task Assigned', 'You have been assigned a new Fish Cage Management task.', 1, '2026-02-20 19:03:04'),
(149, 5, 'New Task Assigned', 'You have been assigned a new Fish Cage Management task.', 1, '2026-02-20 19:13:13'),
(150, 1, 'Task Completed', 'Roberto Cesar completed a Fish Cage Management task.', 1, '2026-02-20 19:14:21'),
(151, 5, 'New Task Assigned', 'You have been assigned a new Fish Cage Management task.', 1, '2026-02-20 19:17:00'),
(152, 1, 'Task Completed', 'Roberto Cesar completed a Fish Cage Management task.', 1, '2026-02-20 19:21:17'),
(153, 5, 'New Task Assigned', 'You have been assigned a new Fish Cage Management task.', 1, '2026-02-20 19:29:59'),
(154, 1, 'Task Completed', 'Roberto Cesar completed a Fish Cage Management task.', 1, '2026-02-20 19:30:29'),
(155, 1, 'Task Completed', 'Roberto Cesar completed a Fish Cage Management task.', 1, '2026-02-20 19:30:42'),
(156, 1, 'Task Completed', 'Roberto Cesar completed a Fish Cage Management task.', 1, '2026-02-20 19:30:53'),
(157, 1, 'Task Completed', 'Roberto Cesar completed a Fish Cage Management task.', 1, '2026-02-20 19:39:16'),
(158, 5, 'New Sampling Task Assigned', 'You have been assigned a Sampling task for cage \"Nursery Cage\" scheduled on Feb 08 2026 10:30 AM.', 1, '2026-02-21 10:13:53'),
(159, 5, 'New Sampling Task Assigned', 'You have been assigned a new Sampling task.', 1, '2026-02-21 10:15:45'),
(160, 1, 'Sampling Task Completed', 'Roberto Cesar completed a Sampling task.', 1, '2026-02-21 10:16:15'),
(161, 16, 'New Task Assigned', 'You have been assigned a new Fish Cage Management task.', 0, '2026-02-21 10:16:36'),
(162, 16, 'New Sampling Task Assigned', 'You have been assigned a new Sampling task.', 0, '2026-02-21 10:18:00'),
(163, 5, 'New Task Assigned', 'You have been assigned a new Fish Cage Management task.', 1, '2026-02-21 10:50:53'),
(164, 5, 'New Fish Cage Task Assigned', 'You have been assigned a Fish Cage Management task for cage \"Cage C\" scheduled on Feb 25 2026 06:12 PM.', 1, '2026-02-21 10:50:59'),
(165, 16, 'New Fish Cage Task Assigned', 'You have been assigned a Fish Cage Management task for cage \"Cage C\" scheduled on Feb 25 2026 06:12 PM.', 0, '2026-02-21 10:51:18'),
(166, 16, 'New Sampling Task Assigned', 'You have been assigned a Sampling task for cage \"Nursery Cage\" scheduled on Feb 08 2026 10:30 AM.', 0, '2026-02-21 10:52:11'),
(167, 16, 'New Fish Cage Task Assigned', 'You have been assigned a Fish Cage Management task for cage \"Nursery Cage\" scheduled on Feb 10 2026 11:54 AM.', 0, '2026-02-21 10:53:33'),
(168, 16, 'New Fish Cage Task Assigned', 'You have been assigned a Fish Cage Management task for cage \"Cage C\" scheduled on Feb 25 2026 06:12 PM.', 0, '2026-02-21 10:53:46'),
(169, 5, 'New Feeding Task Assigned', 'You have been assigned a new Feeding task for cage #13 on .', 1, '2026-02-21 11:34:39'),
(170, 5, 'New Feeding Task Assigned', 'You have been assigned a new Feeding task.', 1, '2026-02-21 11:37:00'),
(171, 1, 'Task Completed', 'Roberto Cesar completed a Feeding task', 1, '2026-02-21 11:37:58'),
(172, 5, 'New Feeding Task Assigned', 'You have been assigned a new Feeding task for cage #13 on .', 1, '2026-02-21 11:42:59'),
(173, 5, 'New Fish Cage Task Assigned', 'You have been assigned a Fish Cage Management task for cage \"Nursery Cage\" scheduled on Feb 10 2026 11:54 AM.', 1, '2026-02-21 11:43:31'),
(174, 5, 'New Feeding Task Assigned', 'You have been assigned a new Feeding task.', 1, '2026-02-21 11:44:13'),
(175, 5, 'New Stocking Task Assigned', 'You have been assigned a Stocking task for cage \"Nursery Cage\" scheduled on Feb 10 2026 10:30 AM.', 1, '2026-02-21 14:44:10'),
(176, 5, 'New Stocking Task Assigned', 'You have been assigned a Stocking task for cage \"Nursery Cage\" scheduled on Feb 10 2026 10:30 AM.', 1, '2026-02-21 14:45:26'),
(177, 5, 'New Stocking Task Assigned', 'You have been assigned a Stocking task for cage \"Nursery Cage\" scheduled on Feb 10 2026 10:30 AM.', 1, '2026-02-21 14:45:55'),
(178, 5, 'New Stocking Task Assigned', 'You have been assigned a Stocking task for cage \"Nursery Cage\" scheduled on Feb 10 2026 10:30 AM.', 1, '2026-02-21 15:01:54'),
(179, 5, 'New Task Assigned', 'You have been assigned a new Stocking task.', 1, '2026-02-21 15:06:47'),
(180, 1, 'Task Completed', 'Roberto Cesar completed a Stocking task.', 1, '2026-02-21 15:10:35'),
(181, 16, 'New Task Assigned', 'You have been assigned a new Stocking task.', 0, '2026-02-21 15:10:54'),
(182, 1, 'Task Completed', 'Roberto Cesar completed a Stocking task.', 1, '2026-02-21 15:17:00'),
(183, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task from cage \"Nursery Cage\" scheduled on Nov 30 -0001 12:00 AM.', 1, '2026-02-21 16:22:35'),
(184, 16, 'New Transfer Task Assigned', 'You have been assigned a Transfer task from cage \"Cage D\" scheduled on Nov 30 -0001 12:00 AM.', 0, '2026-02-21 16:23:13'),
(185, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task from cage \"Cage E\" scheduled on Nov 30 -0001 12:00 AM.', 1, '2026-02-21 16:24:29'),
(186, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task from cage \"Nursery Cage\" scheduled on Feb 21 2026 09:39 AM.', 1, '2026-02-21 16:39:50'),
(187, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task from cage \"Nursery Cage\" scheduled on Feb 21 2026 09:40 AM.', 1, '2026-02-21 16:40:15'),
(188, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task from cage \"Nursery Cage\" scheduled on Feb 21 2026 09:40 AM.', 1, '2026-02-21 16:40:26'),
(189, 5, 'New Task Assigned', 'You have been assigned a new Stocking task.', 1, '2026-02-21 16:40:57'),
(190, 5, 'New Task Assigned', 'You have been assigned a new Stocking task.', 1, '2026-02-21 17:07:48'),
(191, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task from cage \"Cage E\" scheduled on Feb 21 2026 10:47 AM.', 1, '2026-02-21 17:47:39'),
(192, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task from cage \"\" scheduled on Jan 01 1970 01:00 AM.', 1, '2026-02-21 18:11:40'),
(193, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task from cage \"\" scheduled on Jan 01 1970 01:00 AM.', 1, '2026-02-21 18:25:12'),
(194, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 1, '2026-02-23 10:21:52'),
(195, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 1, '2026-02-23 10:23:20'),
(196, 1, NULL, 'Roberto Cesar completed a Transferring task', 1, '2026-02-23 11:01:36'),
(197, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 1, '2026-02-23 11:09:24'),
(198, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 1, '2026-02-23 11:12:03'),
(199, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 1, '2026-02-23 11:12:23'),
(200, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 1, '2026-02-23 11:18:31'),
(201, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 1, '2026-02-23 11:18:57'),
(202, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 1, '2026-02-23 11:21:55'),
(203, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 1, '2026-02-23 11:26:53'),
(204, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 1, '2026-02-23 11:35:58'),
(205, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 1, '2026-02-23 11:36:35'),
(206, 1, NULL, 'Roberto Cesar completed a Transferring task', 1, '2026-02-23 11:37:07'),
(207, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 1, '2026-02-23 11:44:13'),
(208, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 1, '2026-02-23 12:00:20'),
(209, 5, 'New Delivery Task Assigned', 'You have been assigned a delivery task.', 1, '2026-02-23 14:52:43'),
(210, 5, 'New Delivery Task Assigned', 'You have been assigned a delivery task.', 1, '2026-02-23 14:55:47'),
(211, 5, 'New Delivery Task Assigned', 'You have been assigned a delivery task.', 1, '2026-02-23 15:03:56'),
(212, 5, 'New Delivery Task Assigned', 'You have been assigned a delivery task.', 1, '2026-02-23 15:04:16'),
(213, 5, 'New Delivery Task Assigned', 'You have been assigned a delivery task.', 1, '2026-02-23 16:59:59'),
(214, 5, 'New Delivery Task Assigned', 'You have been assigned a delivery task.', 1, '2026-02-23 17:00:21'),
(215, 5, 'New Delivery Task Assigned', 'You have been assigned a delivery task.', 1, '2026-02-23 17:00:38'),
(216, 5, 'New Delivery Task Assigned', 'You have been assigned a delivery task.', 1, '2026-02-23 17:17:56'),
(217, 1, NULL, 'Roberto Cesar completed a Delivering task', 1, '2026-02-23 17:29:10'),
(218, 5, 'Delivery Task Updated', 'A delivery task has been updated and assigned to you.', 1, '2026-02-24 10:52:01'),
(219, 5, 'New Delivery Task Assigned', 'You have been assigned a delivery task.', 1, '2026-02-24 10:53:32'),
(220, 5, 'New Delivery Task Assigned', 'You have been assigned a delivery task.', 1, '2026-02-24 10:58:59'),
(221, 5, 'New Delivery Task Assigned', 'You have been assigned a delivery task.', 1, '2026-02-24 11:00:24'),
(222, 5, 'Delivery Task Updated', 'A delivery task has been updated and assigned to you.', 1, '2026-02-24 11:00:56'),
(223, 5, 'Delivery Task Updated', 'A delivery task has been updated and assigned to you.', 1, '2026-02-24 11:01:32'),
(224, 5, 'Delivery Task Updated', 'A delivery task has been updated and assigned to you.', 1, '2026-02-24 11:02:17'),
(225, 5, 'Delivery Task Updated', 'A delivery task has been updated and assigned to you.', 1, '2026-02-24 11:07:16'),
(226, 5, 'Delivery Task Updated', 'A delivery task has been updated and assigned to you.', 1, '2026-02-24 11:10:03'),
(227, 5, 'Delivery Task Updated', 'A delivery task has been updated and assigned to you.', 1, '2026-02-24 11:10:16'),
(228, 1, NULL, 'Roberto Cesar completed a Delivering task', 1, '2026-02-24 11:13:45'),
(229, 1, NULL, 'Roberto Cesar completed a Delivering task', 1, '2026-02-24 11:13:55'),
(230, 5, 'New Delivery Task Assigned', 'You have been assigned a delivery task.', 1, '2026-02-24 12:57:36'),
(231, 5, 'New Net Cleaning Task Assigned', 'You have been assigned a Net Cleaning task for cage \"Nursery Cage\" scheduled on Feb 09 2026 10:30 AM.', 1, '2026-02-24 13:24:37'),
(232, 5, 'New Task Assigned', 'You have been assigned a new Net Cleaning task.', 1, '2026-02-24 14:11:31'),
(233, 1, 'Task Completed', 'Roberto Cesar completed a Net Cleaning task.', 1, '2026-02-24 14:12:29'),
(234, 5, 'New Net Checking Task Assigned', 'You have been assigned a Net Checking task for cage \"Nursery Cage\" scheduled on Feb 24 2026 08:00 AM.', 1, '2026-02-26 19:41:22'),
(235, 5, 'New Task Assigned', 'You have been assigned a new Net Checking task.', 1, '2026-02-26 19:47:43'),
(236, 1, 'Task Completed', 'Roberto Cesar completed a Net Checking task.', 1, '2026-02-26 20:00:18'),
(237, 5, 'Delivery Task Updated', 'A delivery task has been updated and assigned to you.', 1, '2026-02-27 08:32:16'),
(238, 5, 'New Net Repairing Task Assigned', 'You have been assigned a Net Repairing task for cage \"Nursery Cage\" scheduled on Feb 22 2026 10:00 PM.', 1, '2026-02-27 08:51:16'),
(239, 5, 'New Net Repairing Task Assigned', 'You have been assigned a Net Repairing task for cage \"Nursery Cage\" scheduled on Feb 27 2026 09:07 AM.', 1, '2026-02-27 09:07:46'),
(240, 1, 'Task Completed', 'Roberto Cesar completed a Net Cleaning task.', 1, '2026-02-27 09:12:09'),
(241, 5, 'New Net Cleaning Task Assigned', 'You have been assigned a Net Cleaning task for cage \"Nursery Cage\" scheduled on Feb 09 2026 10:30 AM.', 1, '2026-02-27 09:12:27'),
(242, 1, 'Task Completed', 'Roberto Cesar completed a Net Repairing task.', 1, '2026-02-27 09:17:54'),
(243, 1, 'Task Completed', 'Roberto Cesar completed a Net Repairing task.', 1, '2026-02-27 12:05:25'),
(244, 5, 'New Stocking Task Assigned', 'You have been assigned a Stocking task for cage \"Nursery Cage\" scheduled on Feb 19 2026 12:51 PM.', 0, '2026-02-27 12:26:55'),
(245, 5, 'New Task Assigned', 'You have been assigned a new Stocking task.', 0, '2026-02-27 13:04:29'),
(246, 1, 'Task Completed', 'Roberto Cesar completed a Stocking task.', 1, '2026-02-27 13:05:08'),
(247, 5, 'New Task Assigned', 'You have been assigned a new Stocking task.', 0, '2026-02-27 13:07:59'),
(248, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-02-27 13:10:55'),
(249, 5, 'New Delivery Task Assigned', 'You have been assigned a delivery task.', 0, '2026-02-27 13:11:16'),
(250, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-02-27 13:12:13'),
(251, 5, 'New Delivery Task Assigned', 'You have been assigned a delivery task.', 0, '2026-02-27 13:12:29'),
(252, 5, 'New Task Assigned', 'You have been assigned a new Stocking task.', 0, '2026-02-27 13:14:07'),
(253, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-02-27 13:15:03'),
(254, 5, 'Delivery Task Updated', 'A delivery task has been updated and assigned to you.', 0, '2026-02-27 13:15:31'),
(255, 5, 'New Stocking Task Assigned', 'You have been assigned a Stocking task for cage \"Nursery Cage\" scheduled on Feb 19 2026 01:13 PM.', 0, '2026-02-27 14:54:02'),
(256, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-02-27 15:28:24'),
(257, 16, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-02-27 15:28:46'),
(258, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-02-27 15:29:41'),
(259, 16, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-02-27 15:30:26'),
(260, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-02-27 15:33:36'),
(261, 11, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-02-27 15:40:32'),
(262, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-02-27 15:43:08'),
(263, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-02-27 15:47:36'),
(264, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-02-27 15:55:52'),
(265, 5, 'New Task Assigned', 'You have been assigned a new Stocking task.', 0, '2026-02-27 16:01:22'),
(266, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-02-27 16:01:55'),
(267, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-02-27 16:05:41'),
(268, 5, 'New Task Assigned', 'You have been assigned a new Stocking task.', 0, '2026-02-27 16:17:15'),
(269, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-02-27 16:17:34'),
(270, 5, 'New Task Assigned', 'You have been assigned a new Stocking task.', 0, '2026-02-27 16:18:55'),
(271, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-02-27 16:19:06'),
(272, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-02-27 22:20:33'),
(273, 16, 'Delivery Task Updated', 'A delivery task has been updated and assigned to you.', 0, '2026-02-28 07:41:53'),
(274, 16, 'Delivery Task Updated', 'A delivery task has been updated and assigned to you.', 0, '2026-02-28 07:48:13'),
(275, 16, 'Delivery Task Updated', 'A delivery task has been updated and assigned to you.', 0, '2026-02-28 07:48:23'),
(276, 5, 'Delivery Task Updated', 'A delivery task has been updated and assigned to you.', 0, '2026-02-28 07:51:44'),
(277, 1, NULL, 'Roberto Cesar completed a Delivering task', 1, '2026-02-28 07:52:19'),
(278, 1, NULL, 'Roberto Cesar completed a Delivering task', 1, '2026-02-28 07:52:55'),
(279, 5, 'Delivery Task Updated', 'A delivery task has been updated and assigned to you.', 0, '2026-03-02 10:51:03'),
(280, 5, 'New Delivery Task Assigned', 'You have been assigned a delivery task.', 0, '2026-03-02 10:51:47'),
(281, 5, 'Delivery Task Updated', 'A delivery task has been updated and assigned to you.', 0, '2026-03-02 10:53:34'),
(282, 5, 'New Delivery Task Assigned', 'You have been assigned a delivery task.', 0, '2026-03-02 11:11:12'),
(283, 5, 'New Task Assigned', 'You have been assigned a new Stocking task.', 0, '2026-03-02 11:13:07'),
(284, 5, 'New Task Assigned', 'You have been assigned a new Stocking task.', 0, '2026-03-02 11:14:01'),
(285, 5, 'New Delivery Task Assigned', 'You have been assigned a delivery task.', 0, '2026-03-02 11:14:25'),
(286, 5, 'Delivery Task Updated', 'A delivery task has been updated and assigned to you.', 0, '2026-03-06 19:55:00'),
(287, 5, 'New Task Assigned', 'You have been assigned a new Stocking task.', 0, '2026-03-06 19:59:35'),
(288, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-03-06 19:59:52'),
(289, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-03-06 20:00:28'),
(290, 5, 'New Delivery Task Assigned', 'You have been assigned a delivery task.', 0, '2026-03-06 20:01:12'),
(291, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-03-06 22:08:00'),
(292, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-03-06 22:08:53'),
(293, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-03-06 22:09:14'),
(294, 5, 'Delivery Task Updated', 'A delivery task has been updated and assigned to you.', 0, '2026-03-06 22:09:32'),
(295, 5, 'Delivery Task Updated', 'A delivery task has been updated and assigned to you.', 0, '2026-03-07 12:09:43'),
(296, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-03-07 12:24:49'),
(297, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-03-07 13:13:20'),
(298, 5, 'Delivery Task Updated', 'A delivery task has been updated and assigned to you.', 0, '2026-03-07 13:13:43'),
(299, 5, 'New Task Assigned', 'You have been assigned a new Stocking task.', 0, '2026-03-07 14:00:34'),
(300, 5, 'New Transfer Task Assigned', 'You have been assigned a Transfer task.', 0, '2026-03-07 14:00:52'),
(301, 5, 'Delivery Task Updated', 'A delivery task has been updated and assigned to you.', 0, '2026-03-07 14:01:00'),
(302, 1, NULL, 'Roberto Cesar completed a Delivering task', 1, '2026-03-07 14:15:42');

-- --------------------------------------------------------

--
-- Table structure for table `samplings`
--

CREATE TABLE `samplings` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `cage_id` int(11) NOT NULL,
  `sampling_date` date DEFAULT NULL,
  `fish_type` varchar(100) NOT NULL,
  `avg_weight` decimal(6,2) NOT NULL,
  `weight_unit` enum('g','kg') NOT NULL,
  `avg_length` decimal(6,2) NOT NULL,
  `length_unit` enum('cm','mm') NOT NULL,
  `status` enum('Pending','Ongoing','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_to` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `samplings`
--

INSERT INTO `samplings` (`id`, `schedule_id`, `cage_id`, `sampling_date`, `fish_type`, `avg_weight`, `weight_unit`, `avg_length`, `length_unit`, `status`, `created_at`, `assigned_to`) VALUES
(1, 67, 13, '2026-02-09', 'Tilapia', 180.00, 'g', 23.00, '', 'Completed', '2026-02-07 14:11:58', NULL),
(2, 84, 13, '2026-02-05', 'Tilapia', 45.00, 'g', 11.00, '', 'Completed', '2026-02-13 10:39:40', NULL),
(3, 85, 13, '2026-02-18', 'Tilapia', 175.00, 'g', 23.00, '', 'Completed', '2026-02-13 10:40:08', NULL),
(4, 86, 14, '2026-02-18', 'Tilapia', 160.00, 'g', 21.00, '', 'Completed', '2026-02-13 10:40:39', 16),
(5, 87, 13, '2026-01-30', 'Tilapia', 35.00, 'g', 9.00, '', 'Completed', '2026-02-13 10:41:16', NULL),
(6, 67, 13, '2026-02-09', 'Tilapia', 185.00, 'g', 20.00, 'cm', 'Completed', '2026-02-13 10:54:52', NULL),
(7, 87, 13, NULL, '', 0.00, '', 0.00, '', 'Completed', '2026-02-13 10:58:30', NULL),
(9, 67, 13, '2026-02-27', 'Tilapia', 0.00, 'g', 0.00, '', 'Ongoing', '2026-02-21 02:13:53', 5),
(10, 67, 13, NULL, '', 0.00, 'g', 0.00, '', 'Pending', '2026-02-21 02:52:11', 16),
(11, 187, 18, NULL, '', 0.00, 'g', 0.00, 'cm', 'Pending', '2026-02-21 07:32:06', NULL),
(12, 253, 13, NULL, '', 0.00, 'g', 0.00, 'cm', 'Pending', '2026-03-06 11:56:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `fish_cage` varchar(100) NOT NULL,
  `task` varchar(100) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `schedule_datetime` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `fish_cage`, `task`, `assigned_to`, `schedule_datetime`, `created_by`, `created_at`) VALUES
(66, '13', 'Net Cleaning', 2, '2026-02-09 10:30:00', 1, '2026-02-07 14:07:54'),
(67, '13', 'Sampling', 2, '2026-02-08 10:30:00', 1, '2026-02-07 14:11:58'),
(68, '13', 'Stocking', 5, '2026-02-10 10:30:00', 1, '2026-02-07 14:18:54'),
(69, '13', 'Fish Cage Management', 2, '2026-02-10 11:54:00', 1, '2026-02-09 00:54:19'),
(70, '13', 'Feeding', 2, '2026-02-14 22:29:00', 1, '2026-02-09 00:54:52'),
(71, '13', 'Stocking', 2, '2026-02-16 06:30:00', 1, '2026-02-09 00:55:26'),
(72, '13', 'Net Repairing', 2, '2026-02-22 22:00:00', 1, '2026-02-09 00:58:46'),
(73, '13', 'Net Checking', 5, '2026-02-24 08:00:00', 1, '2026-02-09 00:59:25'),
(74, '13', 'Transfer', 2, '2026-02-09 00:00:00', 1, '2026-02-09 02:45:40'),
(75, '13', 'Transfer', 2, '2026-02-09 00:00:00', 1, '2026-02-09 02:46:48'),
(76, '13', 'Delivery', 2, '2026-02-09 00:00:00', 1, '2026-02-09 02:53:07'),
(77, '13', 'Transfer', 2, '2026-02-09 00:00:00', 1, '2026-02-09 03:39:10'),
(78, '13', 'Transfer', 2, '2026-02-09 00:00:00', 1, '2026-02-09 03:51:35'),
(79, '13', 'Transfer', 2, '2026-02-09 00:00:00', 1, '2026-02-09 04:00:17'),
(80, '14', 'Fish Cage Management', 2, '2026-02-09 17:05:00', 1, '2026-02-09 09:05:25'),
(81, '15', 'Fish Cage Management', 5, '2026-01-11 08:10:00', 1, '2026-02-13 10:31:58'),
(82, '16', 'Fish Cage Management', 5, '2026-02-14 09:32:00', 1, '2026-02-13 10:32:24'),
(83, '15', 'Fish Cage Management', 5, '2026-02-24 23:01:00', 1, '2026-02-13 10:34:49'),
(84, '13', 'Sampling', 5, '2026-01-05 09:39:00', 1, '2026-02-13 10:39:40'),
(85, '13', 'Sampling', 5, '2026-01-13 08:25:00', 1, '2026-02-13 10:40:08'),
(86, '14', 'Sampling', 5, '2026-02-18 09:59:00', 1, '2026-02-13 10:40:39'),
(87, '13', 'Sampling', 5, '2026-01-29 09:40:00', 1, '2026-02-13 10:41:16'),
(88, '13', 'Feeding', 5, '2026-01-03 06:48:00', 1, '2026-02-13 10:48:55'),
(89, '14', 'Feeding', 5, '2026-01-04 06:49:00', 1, '2026-02-13 10:49:45'),
(90, '15', 'Feeding', 5, '2026-01-05 06:15:00', 1, '2026-02-13 10:50:16'),
(91, '14', 'Feeding', 5, '2026-01-05 06:25:00', 1, '2026-02-13 10:52:59'),
(92, '13', 'Feeding', 5, '2026-01-05 06:10:00', 1, '2026-02-13 10:53:43'),
(93, '13', 'Feeding', 5, '2026-01-03 06:22:00', 1, '2026-02-13 10:55:37'),
(94, '13', 'Stocking', 5, '2025-03-02 07:30:00', 1, '2026-02-13 11:08:46'),
(95, '13', 'Stocking', 5, '2025-03-20 08:50:00', 1, '2026-02-13 11:09:17'),
(96, '15', 'Stocking', 5, '2025-03-20 09:50:00', 1, '2026-02-13 11:10:17'),
(97, '16', 'Stocking', 5, '2026-02-20 09:35:00', 1, '2026-02-13 11:10:53'),
(98, '13', 'Stocking', 5, '2026-02-28 07:25:00', 1, '2026-02-13 11:11:17'),
(99, '13', 'Stocking', 5, '2026-02-28 07:11:00', 1, '2026-02-13 11:12:04'),
(100, '13', 'Stocking', 5, '2025-04-05 07:19:00', 1, '2026-02-13 11:13:40'),
(101, '14', 'Stocking', 5, '2025-03-20 07:33:00', 1, '2026-02-13 11:14:27'),
(102, '16', 'Stocking', 5, '2025-03-13 07:14:00', 1, '2026-02-13 11:14:45'),
(103, '13', 'Transfer', 5, '2025-04-02 00:00:00', 1, '2026-02-13 11:28:35'),
(104, '13', 'Transfer', 5, '2025-04-20 00:00:00', 1, '2026-02-13 11:29:31'),
(105, '15', 'Transfer', 5, '2025-04-20 00:00:00', 1, '2026-02-13 11:30:46'),
(106, '13', 'Transfer', 5, '2025-05-05 00:00:00', 1, '2026-02-13 11:32:02'),
(107, '16', 'Transfer', 5, '2026-04-13 00:00:00', 1, '2026-02-13 11:33:38'),
(108, '13', 'Transfer', 5, '2025-04-13 00:00:00', 1, '2026-02-13 11:34:36'),
(109, '13', 'Delivery', 5, '2025-08-02 00:00:00', 1, '2026-02-13 12:57:34'),
(110, '13', 'Delivery', 5, '2025-08-20 00:00:00', 1, '2026-02-13 12:58:41'),
(111, '13', 'Delivery', 5, '2025-09-05 00:00:00', 1, '2026-02-13 13:00:25'),
(112, '15', 'Delivery', 5, '2025-08-20 00:00:00', 1, '2026-02-13 13:01:24'),
(113, '15', 'Delivery', 5, '2026-08-20 00:00:00', 1, '2026-02-13 13:04:26'),
(114, '16', 'Delivery', 5, '2025-08-13 00:00:00', 1, '2026-02-13 13:05:57'),
(115, '13', 'Net Cleaning', 5, '2026-01-01 07:01:00', 1, '2026-02-13 13:07:36'),
(116, '14', 'Net Cleaning', 5, '2026-01-01 07:10:00', 1, '2026-02-13 13:08:14'),
(117, '15', 'Net Cleaning', 5, '2026-01-01 07:01:00', 1, '2026-02-13 13:08:48'),
(118, '17', 'Net Cleaning', 5, '2026-01-01 07:00:00', 1, '2026-02-13 13:09:10'),
(119, '18', 'Net Cleaning', 5, '2026-01-01 07:09:00', 1, '2026-02-13 13:09:31'),
(120, '16', 'Net Cleaning', 5, '2026-02-01 07:00:00', 1, '2026-02-13 13:10:16'),
(121, '13', 'Net Repairing', 5, '2026-01-02 07:00:00', 1, '2026-02-13 13:22:54'),
(122, '13', 'Net Repairing', 5, '2026-01-08 06:20:00', 1, '2026-02-13 13:23:13'),
(123, '16', 'Net Repairing', 5, '2026-01-14 07:14:00', 1, '2026-02-13 13:23:42'),
(124, '15', 'Net Repairing', 5, '2026-01-21 19:09:00', 1, '2026-02-13 13:24:10'),
(125, '16', 'Net Repairing', 5, '2026-01-27 07:25:00', 1, '2026-02-13 13:25:16'),
(126, '13', 'Net Repairing', 5, '2026-01-24 07:25:00', 1, '2026-02-13 13:25:56'),
(127, '13', 'Net Checking', 5, '2026-01-09 07:06:00', 1, '2026-02-13 13:32:24'),
(128, '14', 'Net Checking', 5, '2026-01-07 07:10:00', 1, '2026-02-13 13:32:45'),
(129, '15', 'Net Checking', 5, '2026-01-21 07:10:00', 1, '2026-02-13 13:33:15'),
(130, '14', 'Net Checking', 5, '2026-02-11 07:00:00', 1, '2026-02-13 13:33:45'),
(131, '16', 'Net Checking', 5, '2026-02-01 07:01:00', 1, '2026-02-13 13:34:14'),
(132, '17', 'Net Checking', 5, '2026-02-07 07:02:00', 1, '2026-02-13 13:34:35'),
(133, '13', 'Stocking', 5, '2025-12-13 10:14:00', 1, '2026-02-13 14:15:15'),
(134, '13', 'Fish Cage Management', 5, '2026-01-01 11:32:00', 1, '2026-02-14 03:32:15'),
(135, '13', 'Transfer', 5, '2026-02-16 00:00:00', 1, '2026-02-16 09:15:15'),
(136, '13', 'Stocking', 5, '2026-02-16 18:11:00', 1, '2026-02-16 10:11:42'),
(137, '13', 'Net Cleaning', 5, '2026-02-17 08:51:00', 1, '2026-02-17 00:51:19'),
(138, '13', 'Fish Cage Management', 5, '2026-01-01 09:35:00', 1, '2026-02-17 01:35:38'),
(139, '13', 'Net Cleaning', 5, '2026-02-08 09:36:00', 1, '2026-02-17 01:36:24'),
(140, '13', 'Feeding', 5, '2026-02-18 09:36:00', 1, '2026-02-17 01:36:50'),
(141, '13', 'Delivery', 5, '2026-02-17 00:00:00', 1, '2026-02-17 02:42:52'),
(142, '16', 'Transfer', 16, '2026-02-18 00:00:00', 1, '2026-02-18 09:42:32'),
(143, '16', 'Transfer', 5, '2026-02-18 00:00:00', 1, '2026-02-18 10:04:26'),
(144, '17', 'Stocking', 5, '2026-02-18 00:00:00', 1, '2026-02-18 10:04:26'),
(145, '13', 'Transfer', 16, '2026-02-18 00:00:00', 1, '2026-02-18 10:08:00'),
(146, '14', 'Stocking', 16, '2026-02-18 00:00:00', 1, '2026-02-18 10:08:00'),
(147, '16', 'Transfer', 16, '2026-02-19 00:00:00', 1, '2026-02-19 04:12:51'),
(148, '17', 'Stocking', 16, '2026-02-19 00:00:00', 1, '2026-02-19 04:12:51'),
(149, '17', 'Transfer', 16, '2026-02-19 00:00:00', 1, '2026-02-19 04:17:00'),
(150, '17', 'Transfer', 16, '2026-02-19 00:00:00', 1, '2026-02-19 04:17:14'),
(151, '17', 'Transfer', 16, '2026-02-19 00:00:00', 1, '2026-02-19 04:17:38'),
(152, '17', 'Transfer', 16, '2026-02-19 00:00:00', 1, '2026-02-19 04:21:56'),
(153, '16', 'Transfer', 16, '2026-02-19 00:00:00', 1, '2026-02-19 04:37:11'),
(154, '16', 'Transfer', 16, '2026-02-19 00:00:00', 1, '2026-02-19 04:37:53'),
(155, '13', 'Transfer', 16, '2026-02-19 00:00:00', 1, '2026-02-19 04:41:21'),
(156, '13', 'Transfer', 16, '2026-02-19 00:00:00', 1, '2026-02-19 04:45:15'),
(157, '18', 'Stocking', 16, '2026-02-19 00:00:00', 1, '2026-02-19 04:45:15'),
(158, '13', 'Transfer', 16, '2026-02-19 00:00:00', 1, '2026-02-19 04:46:56'),
(159, '14', 'Transfer', 16, '2026-02-19 00:00:00', 1, '2026-02-19 04:50:32'),
(160, '13', 'Stocking', 16, '2026-02-19 12:51:00', 1, '2026-02-19 04:51:25'),
(161, '13', 'Stocking', 16, '2026-02-19 13:13:00', 1, '2026-02-19 05:13:04'),
(162, '13', 'Transfer', 16, '2026-02-19 00:00:00', 1, '2026-02-19 05:18:08'),
(163, '13', 'Stocking', 16, '2026-02-19 13:31:00', 1, '2026-02-19 05:31:12'),
(164, '13', 'Stocking', 16, '2026-02-19 13:31:00', 1, '2026-02-19 05:31:26'),
(165, '13', 'Transfer', 16, '2026-02-19 00:00:00', 1, '2026-02-19 05:34:02'),
(166, '14', 'Stocking', 16, '2026-02-19 00:00:00', 1, '2026-02-19 05:34:02'),
(167, '14', 'Transfer', 16, '2026-02-19 00:00:00', 1, '2026-02-19 05:37:02'),
(168, '15', 'Stocking', 16, '2026-02-19 00:00:00', 1, '2026-02-19 05:37:02'),
(169, '15', 'Transfer', 16, '2026-02-19 00:00:00', 1, '2026-02-19 05:37:30'),
(170, '18', 'Stocking', 16, '2026-02-19 00:00:00', 1, '2026-02-19 05:37:30'),
(171, '13', 'Transfer', 16, '2026-02-19 00:00:00', 1, '2026-02-19 05:50:32'),
(172, '14', 'Stocking', 16, '2026-02-19 00:00:00', 1, '2026-02-19 05:50:32'),
(173, '13', 'Transfer', 16, '2026-02-19 00:00:00', 1, '2026-02-19 06:50:51'),
(174, '18', 'Stocking', 16, '2026-02-19 00:00:00', 1, '2026-02-19 06:50:51'),
(175, '13', 'Transfer', 16, '2026-02-19 00:00:00', 1, '2026-02-19 06:51:48'),
(176, '18', 'Stocking', 16, '2026-02-19 00:00:00', 1, '2026-02-19 06:51:48'),
(177, '13', 'Transfer', 16, '2026-02-19 00:00:00', 1, '2026-02-19 06:53:28'),
(178, '18', 'Stocking', 16, '2026-02-19 00:00:00', 1, '2026-02-19 06:53:28'),
(179, '18', 'Transfer', 16, '2026-02-19 00:00:00', 1, '2026-02-19 06:54:01'),
(180, '17', 'Stocking', 16, '2026-02-19 00:00:00', 1, '2026-02-19 06:54:01'),
(181, '13', 'Stocking', 5, '2026-02-19 15:53:00', 1, '2026-02-19 07:53:30'),
(182, '13', 'Stocking', 5, '2026-02-19 16:13:00', 1, '2026-02-19 08:13:11'),
(183, '13', 'Fish Cage Management', NULL, '2026-02-20 16:35:00', 1, '2026-02-20 08:35:27'),
(184, '13', 'Fish Cage Management', NULL, '2026-02-20 16:38:00', 1, '2026-02-20 08:38:31'),
(185, '16', 'Fish Cage Management', NULL, '2026-02-25 18:12:00', 1, '2026-02-20 10:12:47'),
(186, '18', 'Fish Cage Management', NULL, '2026-02-21 15:31:00', 1, '2026-02-21 07:31:48'),
(187, '18', 'Sampling', NULL, '2026-02-21 15:32:00', 1, '2026-02-21 07:32:06'),
(188, '18', 'Feeding', NULL, '2026-02-21 15:32:00', 1, '2026-02-21 07:32:21'),
(189, '18', 'Stocking', NULL, '2026-02-21 15:33:00', 1, '2026-02-21 07:33:14'),
(190, '13', 'Transfer', 5, '2026-02-21 08:50:04', 1, '2026-02-21 07:50:04'),
(191, '13', 'Transfer', 5, '2026-02-21 08:51:03', 1, '2026-02-21 07:51:03'),
(195, '13', 'Transfer', 5, '2026-02-21 09:39:50', 1, '2026-02-21 08:39:50'),
(196, '13', 'Transfer', 5, '2026-02-21 09:40:15', 1, '2026-02-21 08:40:15'),
(197, '13', 'Transfer', 5, '2026-02-21 09:40:26', 1, '2026-02-21 08:40:26'),
(198, '18', 'Transfer', 5, '2026-02-21 10:33:06', 1, '2026-02-21 09:33:06'),
(199, '18', 'Transfer', 5, '2026-02-21 10:47:39', 1, '2026-02-21 09:47:39'),
(200, '18', 'Transfer', 5, '2026-02-21 00:00:00', 1, '2026-02-21 10:40:22'),
(201, '13', 'Stocking', 5, '2026-02-21 00:00:00', 1, '2026-02-21 10:40:22'),
(202, '17', 'Transfer', 5, '2026-02-21 00:00:00', 1, '2026-02-21 11:03:06'),
(203, '18', 'Stocking', 5, '2026-02-21 00:00:00', 1, '2026-02-21 11:03:06'),
(204, '18', 'Transfer', 5, '2026-02-21 00:00:00', 1, '2026-02-21 11:04:41'),
(205, '14', 'Stocking', 5, '2026-02-21 00:00:00', 1, '2026-02-21 11:04:41'),
(206, '14', 'Stocking', 5, '2026-02-23 10:17:27', 1, '2026-02-23 02:17:27'),
(207, '14', 'Stocking', 5, '2026-02-23 10:21:52', 1, '2026-02-23 02:21:52'),
(208, '15', 'Stocking', 5, '2026-02-23 10:23:20', 1, '2026-02-23 02:23:20'),
(209, '14', 'Stocking', 5, '2026-02-23 11:09:24', 1, '2026-02-23 03:09:24'),
(210, '15', 'Stocking', 5, '2026-02-23 11:12:03', 1, '2026-02-23 03:12:03'),
(211, '15', 'Stocking', 5, '2026-02-23 11:12:23', 1, '2026-02-23 03:12:23'),
(212, '15', 'Stocking', 5, '2026-02-23 11:18:31', 1, '2026-02-23 03:18:31'),
(213, '15', 'Stocking', 5, '2026-02-23 11:18:57', 1, '2026-02-23 03:18:57'),
(214, '15', 'Stocking', 5, '2026-02-23 11:21:55', 1, '2026-02-23 03:21:55'),
(215, '15', 'Stocking', 5, '2026-02-23 11:26:53', 1, '2026-02-23 03:26:53'),
(216, '15', 'Stocking', 5, '2026-02-23 11:35:58', 1, '2026-02-23 03:35:58'),
(217, '15', 'Stocking', NULL, '2026-02-23 11:37:07', 5, '2026-02-23 03:37:07'),
(218, '15', 'Delivery', 5, '2026-02-23 14:52:43', 0, '2026-02-23 06:52:43'),
(219, '15', 'Delivery', 5, '2026-02-23 14:55:47', 0, '2026-02-23 06:55:47'),
(220, '15', 'Delivery', 5, '2026-02-23 15:03:56', 0, '2026-02-23 07:03:56'),
(221, '15', 'Delivery', 5, '2026-02-23 15:04:15', 0, '2026-02-23 07:04:15'),
(222, '18', 'Net Cleaning', NULL, '2026-02-24 14:13:00', 1, '2026-02-24 06:13:41'),
(223, '13', 'Net Repairing', NULL, '2026-02-27 09:07:00', 1, '2026-02-27 01:07:30'),
(224, '14', 'Stocking', NULL, '2026-02-27 13:04:00', 1, '2026-02-27 05:04:11'),
(225, '18', 'Stocking', NULL, '2026-02-27 13:07:00', 1, '2026-02-27 05:07:40'),
(226, '14', 'Stocking', 5, '2026-02-27 13:10:55', 1, '2026-02-27 05:10:55'),
(227, '14', 'Stocking', 5, '2026-02-27 13:12:13', 1, '2026-02-27 05:12:13'),
(228, '18', 'Feeding', NULL, '2026-02-27 14:52:00', 1, '2026-02-27 06:52:07'),
(229, '17', 'Feeding', NULL, '2026-02-27 14:53:00', 1, '2026-02-27 06:53:40'),
(230, '17', 'Fish Cage Management', NULL, '2026-02-27 14:57:00', 1, '2026-02-27 06:57:08'),
(231, '18', 'Stocking', NULL, '2026-02-27 14:57:00', 1, '2026-02-27 06:57:41'),
(232, '14', 'Net Repairing', NULL, '2026-02-27 15:00:00', 1, '2026-02-27 07:00:53'),
(233, '18', 'Net Cleaning', NULL, '2026-02-27 15:01:00', 1, '2026-02-27 07:01:17'),
(234, '18', 'Net Cleaning', NULL, '2026-02-27 15:21:00', 1, '2026-02-27 07:21:53'),
(235, '14', 'Stocking', 5, '2026-02-27 15:28:24', 1, '2026-02-27 07:28:24'),
(236, '14', 'Stocking', 16, '2026-02-27 15:28:46', 1, '2026-02-27 07:28:46'),
(237, '13', 'Stocking', 5, '2026-02-27 15:29:41', 1, '2026-02-27 07:29:41'),
(238, '18', 'Stocking', 16, '2026-02-27 15:30:26', 1, '2026-02-27 07:30:26'),
(239, '14', 'Stocking', 5, '2026-02-27 15:33:36', 1, '2026-02-27 07:33:36'),
(240, '18', 'Stocking', 11, '2026-02-27 15:40:32', 1, '2026-02-27 07:40:32'),
(241, '14', 'Stocking', 5, '2026-02-27 15:43:08', 1, '2026-02-27 07:43:08'),
(242, '13', 'Stocking', 5, '2026-02-27 15:47:36', 1, '2026-02-27 07:47:36'),
(243, '15', 'Stocking', 5, '2026-02-27 15:55:52', 1, '2026-02-27 07:55:52'),
(244, '18', 'Stocking', NULL, '2026-02-27 16:01:00', 1, '2026-02-27 08:01:02'),
(245, '13', 'Stocking', 5, '2026-02-27 16:01:55', 1, '2026-02-27 08:01:55'),
(246, '14', 'Stocking', 5, '2026-02-27 16:05:41', 1, '2026-02-27 08:05:41'),
(247, '13', 'Stocking', NULL, '2026-02-27 16:16:00', 1, '2026-02-27 08:16:53'),
(248, '18', 'Stocking', 5, '2026-02-27 16:17:34', 1, '2026-02-27 08:17:34'),
(249, '14', 'Stocking', 5, '2026-02-27 16:19:06', 1, '2026-02-27 08:19:06'),
(250, '13', 'Stocking', NULL, '2026-03-02 11:12:00', 1, '2026-03-02 03:12:44'),
(251, '13', 'Stocking', NULL, '2026-03-02 11:13:00', 1, '2026-03-02 03:13:34'),
(252, '13', 'Fish Cage Management', NULL, '2026-03-06 19:56:00', 1, '2026-03-06 11:56:22'),
(253, '13', 'Sampling', NULL, '2026-03-06 19:56:00', 1, '2026-03-06 11:56:43'),
(254, '13', 'Feeding', NULL, '2026-03-06 19:56:00', 1, '2026-03-06 11:56:58'),
(255, '13', 'Stocking', NULL, '2026-03-06 19:57:00', 1, '2026-03-06 11:57:35'),
(256, '18', 'Stocking', 5, '2026-03-06 19:59:52', 1, '2026-03-06 11:59:52'),
(257, '14', 'Stocking', 5, '2026-03-06 20:00:28', 1, '2026-03-06 12:00:28'),
(258, '14', 'Stocking', 5, '2026-03-07 12:24:49', 1, '2026-03-07 04:24:49');

-- --------------------------------------------------------

--
-- Table structure for table `stocking`
--

CREATE TABLE `stocking` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `fish_type` enum('Tilapia','Bighead','Bangus') DEFAULT NULL,
  `date_stocked` date DEFAULT NULL,
  `source_of_fingerlings` varchar(255) NOT NULL,
  `source_stocking_id` int(11) DEFAULT NULL,
  `standard_fingerlings` int(11) NOT NULL,
  `number_of_fingerlings` int(11) NOT NULL,
  `status` enum('Pending','Ongoing','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `current_quantity` int(11) NOT NULL DEFAULT 0,
  `assigned_to` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stocking`
--

INSERT INTO `stocking` (`id`, `schedule_id`, `fish_type`, `date_stocked`, `source_of_fingerlings`, `source_stocking_id`, `standard_fingerlings`, `number_of_fingerlings`, `status`, `created_at`, `current_quantity`, `assigned_to`) VALUES
(34, 164, 'Tilapia', '2026-02-19', 'Bayugo, Jala-Jala, Rizal', NULL, 35, 900, 'Pending', '2026-02-19 05:31:26', 0, NULL),
(38, 172, 'Tilapia', '2026-02-19', 'Transfer', NULL, 35, 801, 'Completed', '2026-02-19 05:50:32', 0, NULL),
(39, 68, 'Tilapia', '2026-02-19', 'Bayugo, Jala-Jala, Rizal', NULL, 67, 1000, 'Pending', '2026-02-19 06:50:32', 0, NULL),
(40, 178, 'Tilapia', '2026-02-19', 'Transfer', NULL, 35, 50, 'Completed', '2026-02-19 06:53:28', 0, NULL),
(41, 180, 'Tilapia', '2026-02-19', 'Transfer', NULL, 35, 100, 'Pending', '2026-02-19 06:54:01', 0, 5),
(42, 68, 'Tilapia', '2026-02-19', 'Bayugo, Jala-Jala, Rizal', NULL, 67, 100, 'Pending', '2026-02-19 07:45:37', 0, 5),
(43, 68, 'Tilapia', '2026-02-19', 'Bayugo, Jala-Jala, Rizal', NULL, 1, 1, 'Pending', '2026-02-19 07:49:28', 0, NULL),
(44, 181, NULL, NULL, '', NULL, 0, 0, 'Pending', '2026-02-19 07:53:30', 0, NULL),
(45, 182, 'Tilapia', '2026-02-19', 'Bayugo, Jala-Jala, Rizal', NULL, 1, 2, 'Pending', '2026-02-19 08:13:11', 0, 16),
(46, 68, 'Tilapia', '2026-02-20', 'Bayugo, Jala-Jala, Rizal', NULL, 1, 1, 'Pending', '2026-02-20 08:27:00', 0, 5),
(47, 68, '', '0000-00-00', '', NULL, 0, 0, 'Pending', '2026-02-21 06:44:10', 0, 5),
(48, 68, 'Tilapia', '2026-02-21', 'Bayugo, Jala-Jala, Rizal', NULL, 57, 100, 'Pending', '2026-02-21 06:45:26', 0, 5),
(49, 68, 'Tilapia', '2026-02-21', 'Bayugo, Jala-Jala, Rizal', NULL, 0, 0, 'Completed', '2026-02-21 06:45:55', 0, 5),
(50, 68, 'Tilapia', '2026-02-21', 'Bayugo, Jala-Jala, Rizal', NULL, 0, 0, 'Completed', '2026-02-21 07:01:54', 0, 16),
(51, 189, 'Tilapia', '2026-02-21', 'Bayugo, Jala-Jala, Rizal', NULL, 33, 300, 'Completed', '2026-02-21 07:33:14', 0, 5),
(52, 201, 'Tilapia', '2026-02-21', 'Transfer', NULL, 33, 100, 'Completed', '2026-02-21 10:40:22', 0, NULL),
(53, 203, 'Tilapia', '2026-02-21', 'Transfer', NULL, 35, 51, 'Completed', '2026-02-21 11:03:06', -100, NULL),
(54, 205, 'Tilapia', '2026-02-21', 'Transfer', NULL, 35, 51, 'Completed', '2026-02-21 11:04:41', 0, NULL),
(55, 207, 'Tilapia', '2026-02-23', 'Transfer', NULL, 33, 100, 'Completed', '2026-02-23 02:21:52', 0, NULL),
(56, 208, 'Tilapia', '2026-02-23', 'Transfer', NULL, 33, 50, 'Completed', '2026-02-23 02:23:20', 0, NULL),
(57, 209, 'Tilapia', '2026-02-23', 'Transfer', NULL, 35, 100, 'Completed', '2026-02-23 03:09:24', 0, NULL),
(58, 210, 'Tilapia', '2026-02-23', 'Transfer', NULL, 33, 10, 'Completed', '2026-02-23 03:12:03', 0, NULL),
(59, 211, 'Tilapia', '2026-02-23', 'Transfer', NULL, 33, 100, 'Completed', '2026-02-23 03:12:23', 0, NULL),
(60, 212, 'Tilapia', '2026-02-23', 'Transfer', NULL, 33, 10, 'Completed', '2026-02-23 03:18:31', 0, NULL),
(61, 213, 'Tilapia', '2026-02-23', 'Transfer', NULL, 33, 100, 'Completed', '2026-02-23 03:18:57', 0, NULL),
(62, 214, 'Tilapia', '2026-02-23', 'Transfer', NULL, 33, 101, 'Completed', '2026-02-23 03:21:55', 0, NULL),
(63, 215, 'Tilapia', '2026-02-23', 'Transfer', NULL, 33, 90, 'Completed', '2026-02-23 03:26:53', 0, NULL),
(64, 216, 'Tilapia', '2026-02-23', 'Transfer', NULL, 33, 1, 'Completed', '2026-02-23 03:35:58', 0, NULL),
(65, 217, 'Tilapia', '2026-02-23', 'Transfer', NULL, 33, 1, 'Completed', '2026-02-23 03:37:07', 0, NULL),
(66, 219, 'Tilapia', '2026-02-23', 'Delivery', NULL, 100, 33, 'Completed', '2026-02-23 06:55:47', 0, NULL),
(67, 220, 'Tilapia', '2026-02-23', 'Delivery', NULL, 90, 90, 'Completed', '2026-02-23 07:03:56', 0, NULL),
(68, 221, 'Tilapia', '2026-02-23', 'Delivery', NULL, 90, 90, 'Completed', '2026-02-23 07:04:16', 0, NULL),
(69, 205, 'Tilapia', '2026-02-23', 'Delivery', NULL, 35, 45, 'Completed', '2026-02-23 09:07:01', 0, NULL),
(70, 205, 'Tilapia', '2026-02-23', 'Delivery', NULL, 35, 45, 'Completed', '2026-02-23 09:07:42', 0, NULL),
(71, 160, 'Tilapia', '2026-02-27', 'Bayugo, Jala-Jala, Rizal', NULL, 35, 666, 'Pending', '2026-02-27 04:26:55', 0, 5),
(72, 224, 'Tilapia', '2026-02-27', 'Bayugo, Jala-Jala, Rizal', NULL, 67, 666, 'Pending', '2026-02-27 05:04:11', 666, 5),
(73, 225, 'Bighead', '2026-02-27', 'Bayugo, Jala-Jala, Rizal', NULL, 35, 67, 'Completed', '2026-02-27 05:07:40', 0, 5),
(74, 226, 'Bighead', '2026-02-27', 'Transfer', NULL, 35, 67, 'Completed', '2026-02-27 05:10:55', 0, NULL),
(75, 227, 'Tilapia', '2026-02-27', 'Transfer', NULL, 33, 67, 'Pending', '2026-02-27 05:12:13', 0, 5),
(76, 161, NULL, NULL, '', NULL, 0, 0, 'Pending', '2026-02-27 06:54:02', 0, 5),
(77, 231, NULL, NULL, '', NULL, 0, 0, 'Pending', '2026-02-27 06:57:41', 0, NULL),
(78, 235, 'Tilapia', '2026-02-27', 'Transfer', NULL, 33, 33, 'Completed', '2026-02-27 07:28:24', 0, NULL),
(79, 236, 'Tilapia', '2026-02-27', 'Transfer', NULL, 33, 1, 'Completed', '2026-02-27 07:28:46', 0, NULL),
(80, 237, 'Tilapia', '2026-02-27', 'Transfer', NULL, 33, 1, 'Completed', '2026-02-27 07:29:41', 0, NULL),
(81, 238, 'Tilapia', '2026-02-27', 'Transfer', NULL, 33, 2, 'Completed', '2026-02-27 07:30:26', 2, NULL),
(82, 239, 'Tilapia', '2026-02-27', 'Transfer', NULL, 33, 3, 'Completed', '2026-02-27 07:33:36', 3, NULL),
(83, 240, 'Tilapia', '2026-02-27', 'Transfer', NULL, 33, 7, 'Completed', '2026-02-27 07:40:32', 7, NULL),
(84, 241, 'Tilapia', '2026-02-27', 'Transfer', NULL, 33, 99, 'Completed', '2026-02-27 07:43:08', 0, NULL),
(85, 242, 'Tilapia', '2026-02-27', 'Transfer', NULL, 33, 33, 'Completed', '2026-02-27 07:47:36', 33, NULL),
(86, 243, 'Tilapia', '2026-02-27', 'Transfer', NULL, 33, 4, 'Completed', '2026-02-27 07:55:52', 4, NULL),
(87, 244, 'Tilapia', '2026-02-27', 'Bayugo, Jala-Jala, Rizal', NULL, 66, 666, 'Completed', '2026-02-27 08:01:02', 0, 5),
(89, 246, 'Tilapia', '2026-02-27', 'Transfer', NULL, 66, 333, 'Completed', '2026-02-27 08:05:41', 0, NULL),
(90, 247, 'Tilapia', '2026-02-27', 'Bayugo, Jala-Jala, Rizal', NULL, 33, 50, 'Completed', '2026-02-27 08:16:53', 0, 5),
(91, 248, 'Tilapia', '2026-02-27', 'Bayugo, Jala-Jala, Rizal', NULL, 33, 50, 'Completed', '2026-02-27 08:17:34', 0, 5),
(92, 249, 'Tilapia', '2026-02-27', 'Transfer', NULL, 33, 50, 'Completed', '2026-02-27 08:19:06', 50, NULL),
(93, 250, 'Tilapia', '2026-03-02', 'Bayugo, Jala-Jala, Rizal', NULL, 35, 1000, 'Completed', '2026-03-02 03:12:44', 933, 5),
(94, 251, 'Tilapia', '2026-03-02', 'Transfer', NULL, 67, 100, 'Completed', '2026-03-02 03:13:34', 100, 5),
(95, 255, 'Bangus', '2026-03-06', 'Bayugo, Jala-Jala, Rizal', NULL, 67, 777, 'Completed', '2026-03-06 11:57:35', 0, 5),
(96, 256, 'Bangus', '2026-03-06', 'Transfer', NULL, 67, 777, 'Completed', '2026-03-06 11:59:52', 0, NULL),
(97, 257, 'Bangus', '2026-03-06', 'Transfer', NULL, 67, 777, 'Completed', '2026-03-06 12:00:28', 0, NULL),
(98, 258, 'Tilapia', '2026-03-07', 'Transfer', NULL, 33, 1, 'Ongoing', '2026-03-07 04:24:49', 1, 5);

-- --------------------------------------------------------

--
-- Table structure for table `transfers`
--

CREATE TABLE `transfers` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `stocking_id` int(11) NOT NULL,
  `from_cage` int(11) NOT NULL,
  `to_cage` int(11) DEFAULT NULL,
  `date_transferred` datetime DEFAULT NULL,
  `quantity_before` int(11) NOT NULL,
  `quantity_after` int(11) NOT NULL,
  `mortality` int(11) GENERATED ALWAYS AS (`quantity_before` - `quantity_after`) STORED,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_to` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('Pending','Ongoing','Completed') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transfers`
--

INSERT INTO `transfers` (`id`, `schedule_id`, `stocking_id`, `from_cage`, `to_cage`, `date_transferred`, `quantity_before`, `quantity_after`, `remarks`, `created_at`, `assigned_to`, `status`) VALUES
(31, 171, 34, 13, 14, '2026-02-19 00:00:00', 900, 802, '', '2026-02-19 05:50:32', 16, 'Completed'),
(32, 173, 39, 13, 18, '2026-02-19 00:00:00', 1000, 900, '', '2026-02-19 06:50:51', 16, 'Pending'),
(33, 175, 34, 13, 18, '2026-02-19 00:00:00', 100, 50, '', '2026-02-19 06:51:48', 16, 'Pending'),
(34, 177, 34, 13, 18, '2026-02-19 00:00:00', 50, 50, '', '2026-02-19 06:53:28', 16, 'Pending'),
(35, 179, 40, 18, 17, '2026-02-19 00:00:00', 50, 49, '', '2026-02-19 06:54:01', 16, 'Pending'),
(38, NULL, 39, 13, 14, '2026-02-27 00:00:00', 100, 100, '', '2026-02-21 08:22:35', 5, 'Pending'),
(41, 195, 42, 13, 14, '2026-02-21 00:00:00', 67, 67, '', '2026-02-21 08:39:50', 5, 'Pending'),
(42, 196, 43, 13, 14, '2026-02-21 00:00:00', 1, 1, '', '2026-02-21 08:40:15', 5, 'Pending'),
(43, 197, 46, 13, 14, '2026-02-21 00:00:00', 1, 1, '', '2026-02-21 08:40:26', 5, 'Pending'),
(45, 199, 51, 18, NULL, '2026-02-21 00:00:00', 200, 200, NULL, '2026-02-21 09:47:39', 5, 'Pending'),
(49, 200, 51, 18, 13, '2026-02-21 00:00:00', 100, 100, '', '2026-02-21 10:40:22', 5, 'Pending'),
(50, 202, 41, 17, 18, '2026-02-21 00:00:00', 51, 51, '', '2026-02-21 11:03:06', 5, 'Pending'),
(51, 204, 53, 18, 14, '2026-02-21 00:00:00', 51, 100, '', '2026-02-21 11:04:41', 5, 'Pending'),
(54, 207, 55, 14, 16, '2026-02-23 00:00:00', 100, 90, 'asd', '2026-02-23 02:23:20', 5, 'Completed'),
(55, 225, 73, 18, 14, '2026-02-27 00:00:00', 67, 67, '', '2026-02-27 05:10:55', 5, 'Completed'),
(62, 212, 60, 15, 18, NULL, 7, 7, NULL, '2026-02-27 07:40:32', 11, 'Pending'),
(63, 214, 62, 15, 14, NULL, 99, 99, NULL, '2026-02-27 07:43:08', 5, 'Pending'),
(64, 235, 78, 14, 13, NULL, 33, 33, NULL, '2026-02-27 07:47:36', 5, 'Pending'),
(65, 241, 84, 14, 15, NULL, 99, 4, NULL, '2026-02-27 07:55:52', 5, 'Pending'),
(66, 244, 87, 18, 13, NULL, 666, 333, NULL, '2026-02-27 08:01:55', 5, 'Pending'),
(67, 244, 87, 18, 14, NULL, 333, 333, NULL, '2026-02-27 08:05:41', 5, 'Pending'),
(68, 247, 90, 13, 18, NULL, 50, 50, NULL, '2026-02-27 08:17:34', 5, 'Pending'),
(69, 248, 91, 18, 14, NULL, 50, 50, NULL, '2026-02-27 08:19:06', 5, 'Pending'),
(70, 255, 95, 13, 18, '2026-03-07 00:00:00', 777, 777, '', '2026-03-06 11:59:52', 5, 'Completed'),
(71, 256, 96, 18, 14, '2026-03-06 00:00:00', 777, 777, '', '2026-03-06 12:00:28', 5, 'Ongoing'),
(72, 237, 80, 13, 14, NULL, 1, 1, NULL, '2026-03-07 04:24:49', 5, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobile_num` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `mobile_num`, `password`, `role`, `status`) VALUES
(1, 'Peter Aguilar', 'peteraguilar@gmail.com', '09317945346', '$2y$10$uP6YHN5xhF2DcBB/8KX3S.69oE6aByBaMN19rJENgPAcriitXP/JC', 'admin', 'approved'),
(2, 'Reyna Fe Galan', 'reynafegalan@gmail.com', '09317945347', '$2y$10$eg0cVl0hjccpYjQgvmQB6.Z.BLGB.V5jFR2elYdXXFobSRexR1yf6', 'admin', 'approved'),
(5, 'Roberto Cesar', 'robertocesar@gmail.com', '09876537894', '$2y$10$twA5qXy3QAfRs47aBawNW.NT.2kFwPCyu6NjlceM4NZqvu9Q7cCV2', 'user', 'approved'),
(11, 'Javar Matienzo', 'javarmatienzo@gmail.com', '09647890398', '$2y$10$E.05Ymr/JLUAq5DN9UtSzu4szsOUa8joPpZzmeWuE6A5BqB36Bn2q', 'user', 'approved'),
(15, 'Jestony Miranda', 'jestonymiranda@gmail.com', '09773854623', '$2y$10$0qUr9vX3ZtuQF9iU9APSj.cLUV6vZIXRJ/nZtUN8puYP.B7H1sGeO', 'user', 'approved'),
(16, 'Jaspher Custodio', 'jaspher@gmail.com', '09317945341', '$2y$10$V1P6lhwYvWRgus9pPlZTgOHtxs/hdXH7HKNi82t3qltp2oqWdFWXK', 'user', 'approved'),
(17, 'sho', 'sho@gmail.com', '12345', '$2y$10$/AHjkZF3Ei5qZeJwskV0xeXDJvqMlll6JVO5TABFTQfy1fNv9xf1.', 'user', 'approved');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stocking_id` (`stocking_id`),
  ADD KEY `cage_id` (`cage_id`),
  ADD KEY `fk_deliveries_assigned_to` (`assigned_to`),
  ADD KEY `fk_delivery_schedule` (`schedule_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedings`
--
ALTER TABLE `feedings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `cage_id` (`cage_id`);

--
-- Indexes for table `fish_cages`
--
ALTER TABLE `fish_cages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cage_name` (`cage_name`);

--
-- Indexes for table `fish_cage_management`
--
ALTER TABLE `fish_cage_management`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_fcm_schedule` (`schedule_id`);

--
-- Indexes for table `net_checking`
--
ALTER TABLE `net_checking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_net_checking_schedule` (`schedule_id`);

--
-- Indexes for table `net_cleaning`
--
ALTER TABLE `net_cleaning`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_net_cleaning_schedule` (`schedule_id`);

--
-- Indexes for table `net_repairing`
--
ALTER TABLE `net_repairing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_net_repair_schedule` (`schedule_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `samplings`
--
ALTER TABLE `samplings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `cage_id` (`cage_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stocking`
--
ALTER TABLE `stocking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `transfers`
--
ALTER TABLE `transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_transfer_stocking` (`stocking_id`),
  ADD KEY `fk_from_cage` (`from_cage`),
  ADD KEY `fk_to_cage` (`to_cage`),
  ADD KEY `fk_transfers_assigned_to` (`assigned_to`),
  ADD KEY `fk_transfer_schedule` (`schedule_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `feedings`
--
ALTER TABLE `feedings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `fish_cages`
--
ALTER TABLE `fish_cages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `fish_cage_management`
--
ALTER TABLE `fish_cage_management`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `net_checking`
--
ALTER TABLE `net_checking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `net_cleaning`
--
ALTER TABLE `net_cleaning`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `net_repairing`
--
ALTER TABLE `net_repairing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=303;

--
-- AUTO_INCREMENT for table `samplings`
--
ALTER TABLE `samplings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=259;

--
-- AUTO_INCREMENT for table `stocking`
--
ALTER TABLE `stocking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `transfers`
--
ALTER TABLE `transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`stocking_id`) REFERENCES `stocking` (`id`),
  ADD CONSTRAINT `deliveries_ibfk_2` FOREIGN KEY (`cage_id`) REFERENCES `fish_cages` (`id`),
  ADD CONSTRAINT `fk_deliveries_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_delivery_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `feedings`
--
ALTER TABLE `feedings`
  ADD CONSTRAINT `feedings_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`),
  ADD CONSTRAINT `feedings_ibfk_2` FOREIGN KEY (`cage_id`) REFERENCES `fish_cages` (`id`);

--
-- Constraints for table `fish_cage_management`
--
ALTER TABLE `fish_cage_management`
  ADD CONSTRAINT `fk_fcm_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `net_checking`
--
ALTER TABLE `net_checking`
  ADD CONSTRAINT `fk_net_checking_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `net_cleaning`
--
ALTER TABLE `net_cleaning`
  ADD CONSTRAINT `fk_net_cleaning_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `net_repairing`
--
ALTER TABLE `net_repairing`
  ADD CONSTRAINT `fk_net_repair_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `samplings`
--
ALTER TABLE `samplings`
  ADD CONSTRAINT `samplings_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`),
  ADD CONSTRAINT `samplings_ibfk_2` FOREIGN KEY (`cage_id`) REFERENCES `fish_cages` (`id`);

--
-- Constraints for table `stocking`
--
ALTER TABLE `stocking`
  ADD CONSTRAINT `stocking_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transfers`
--
ALTER TABLE `transfers`
  ADD CONSTRAINT `fk_from_cage` FOREIGN KEY (`from_cage`) REFERENCES `fish_cages` (`id`),
  ADD CONSTRAINT `fk_to_cage` FOREIGN KEY (`to_cage`) REFERENCES `fish_cages` (`id`),
  ADD CONSTRAINT `fk_transfer_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transfer_stocking` FOREIGN KEY (`stocking_id`) REFERENCES `stocking` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_transfers_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
