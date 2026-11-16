-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 19, 2026 at 12:32 PM
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
-- Database: `aasra`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `provider_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `offering_id` int(10) UNSIGNED DEFAULT NULL,
  `service_name` varchar(150) DEFAULT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `requirements` text DEFAULT NULL,
  `service_address` varchar(500) DEFAULT NULL,
  `charges` decimal(10,2) NOT NULL,
  `status` enum('Pending','Accepted','Rejected','Confirmed','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `cancellation_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `provider_id`, `category_id`, `offering_id`, `service_name`, `booking_date`, `booking_time`, `requirements`, `service_address`, `charges`, `status`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 2, NULL, NULL, '2026-10-23', '03:31:00', '', NULL, 50000.00, 'Completed', NULL, '2026-10-13 06:27:30', '2026-10-13 06:47:15'),
(2, 3, 1, 1, NULL, NULL, '2026-10-30', '12:38:00', '', NULL, 2000.00, 'Completed', NULL, '2026-10-13 07:35:42', '2026-10-13 07:36:27'),
(3, 3, 1, 2, NULL, NULL, '2026-10-23', '20:15:00', '', NULL, 50000.00, 'Cancelled', 'Cancelled by user', '2026-10-13 10:12:02', '2026-10-13 11:07:32'),
(4, 3, 1, 2, NULL, NULL, '2026-10-23', '21:12:00', 'i want this service .', NULL, 50000.00, 'Completed', NULL, '2026-10-13 11:08:45', '2026-10-13 11:09:14'),
(5, 3, 1, 1, NULL, NULL, '2026-10-30', '20:13:00', 'i am try to use this service.', NULL, 2000.00, 'Completed', NULL, '2026-10-13 11:10:18', '2026-10-13 11:11:29'),
(6, 3, 1, 2, NULL, NULL, '2026-11-04', '02:07:00', '', NULL, 50000.00, 'Rejected', NULL, '2026-10-14 06:04:43', '2026-10-14 06:50:32'),
(7, 3, 1, 1, NULL, NULL, '2026-10-29', '02:54:00', 'i want this service', NULL, 3000.00, 'Completed', NULL, '2026-10-14 06:50:16', '2026-10-14 11:49:33'),
(8, 3, 1, 2, NULL, NULL, '2026-10-30', '21:51:00', '', NULL, 50000.00, 'Completed', NULL, '2026-10-14 11:47:47', '2026-10-15 05:36:39'),
(9, 3, 3, 1, NULL, NULL, '2026-10-21', '03:38:00', 'i want this service', NULL, 8000.00, 'Cancelled', 'Cancelled by user', '2026-10-15 06:34:26', '2026-10-15 06:35:48'),
(10, 3, 3, 1, NULL, NULL, '2026-10-30', '11:42:00', 'use this service.', NULL, 8000.00, 'Completed', NULL, '2026-10-15 06:38:35', '2026-10-15 06:39:29'),
(11, 3, 3, 1, NULL, NULL, '2026-10-29', '03:49:00', '', NULL, 8000.00, 'Rejected', NULL, '2026-10-15 06:44:41', '2026-10-15 06:45:04'),
(12, 3, 2, 3, NULL, NULL, '2026-10-29', '16:10:00', '', NULL, 7000.00, 'Rejected', NULL, '2026-10-15 07:07:04', '2026-10-15 07:09:35'),
(13, 3, 2, 3, NULL, NULL, '2026-10-28', '16:11:00', '', NULL, 7000.00, 'Completed', NULL, '2026-10-15 07:07:18', '2026-10-15 07:10:11'),
(14, 3, 2, 3, NULL, NULL, '2026-10-29', '16:10:00', '', NULL, 7000.00, 'Cancelled', 'Cancelled by user', '2026-10-15 07:07:55', '2026-10-15 07:08:29'),
(15, 3, 1, 2, NULL, NULL, '2026-10-30', '15:30:00', '', NULL, 50000.00, 'Cancelled', 'Cancelled by user', '2026-10-15 07:27:02', '2026-10-15 07:27:56'),
(16, 3, 1, 2, NULL, NULL, '2026-10-30', '15:33:00', '', NULL, 50000.00, 'Cancelled', 'Cancelled by user', '2026-10-15 07:29:24', '2026-10-15 07:30:15'),
(17, 3, 1, 2, NULL, NULL, '2026-10-30', '14:40:00', '', NULL, 50000.00, 'Pending', NULL, '2026-10-15 07:40:07', '2026-10-15 07:40:07'),
(18, 3, 1, 2, NULL, NULL, '2026-10-30', '14:40:00', '', NULL, 50000.00, 'Completed', NULL, '2026-10-15 07:40:22', '2026-10-15 08:10:42'),
(19, 7, 1, 2, 2, 'second', '2026-10-30', '18:50:00', '', NULL, 50000.00, 'Cancelled', NULL, '2026-10-15 09:48:58', '2026-10-15 11:34:15'),
(20, 7, 1, 2, 2, 'second', '2026-10-30', '18:51:00', '', NULL, 50000.00, 'Cancelled', 'Cancelled by user', '2026-10-15 09:49:24', '2026-10-15 11:34:15'),
(21, 7, 1, 2, 2, 'second', '2026-10-30', '20:54:00', '', NULL, 50000.00, 'Completed', NULL, '2026-10-15 09:57:37', '2026-10-15 11:34:15'),
(22, 7, 1, 1, 1, 'first', '2026-10-29', '20:06:00', 'want this services', NULL, 2000.00, 'Pending', NULL, '2026-10-15 11:03:38', '2026-10-15 11:34:15'),
(23, 7, 1, 2, 2, 'second', '2026-10-29', '20:08:00', 'A Google Account lets you sign in to Gmail and other Google services, such as YouTube, Google Play, Google Drive, and Photos.A Google Account lets you sign in to Gmail and other Google services, such as YouTube, Google Play, Google Drive, and Photos.A Google Account lets you sign in to Gmail and other Google services, such as YouTube, Google Play, Google Drive, and Photos.', NULL, 50000.00, 'Accepted', NULL, '2026-10-15 11:04:50', '2026-10-15 11:34:15'),
(24, 7, 1, 2, 2, 'second', '2026-10-28', '19:40:00', 'Your profile address is filled in automatically. You can change the service location for this booking only. Your profile address will remain unchangedYour profile address is filled in automatically. You can change the service location for this booking only. Your profile address will remain unchanged', 'fareed town', 50000.00, 'Completed', NULL, '2026-10-15 11:38:20', '2026-10-15 11:40:39'),
(25, 13, 1, 1, 7, 'Fall prevention and safety monitoring', '2026-10-22', '14:58:00', 'keep sure during vist take all your necessary items.', 'bahria Town lahore', 5500.00, 'Completed', NULL, '2026-10-19 05:55:58', '2026-10-19 05:57:35'),
(26, 13, 1, 1, 7, 'Fall prevention and safety monitoring', '2026-10-30', '15:02:00', '', 'bahira town phase 8', 5500.00, 'Cancelled', 'Cancelled by user', '2026-10-19 06:00:05', '2026-10-19 06:00:12'),
(27, 13, 1, 1, 7, 'Fall prevention and safety monitoring', '2026-10-30', '16:05:00', '', 'fareed town', 5500.00, 'Rejected', NULL, '2026-10-19 06:01:23', '2026-10-19 06:01:34'),
(28, 3, 1, 1, 7, 'Fall prevention and safety monitoring', '2026-10-30', '03:35:00', '', '100A/WM', 5500.00, 'Cancelled', 'Cancelled by user', '2026-10-19 06:31:37', '2026-10-19 06:31:52'),
(29, 3, 1, 1, 7, 'Fall prevention and safety monitoring', '2026-10-30', '03:49:00', '', '100A/WM', 5500.00, 'Cancelled', 'Cancelled by user', '2026-10-19 06:45:38', '2026-10-19 06:45:46'),
(30, 3, 1, 1, 7, 'Fall prevention and safety monitoring', '2026-10-30', '02:51:00', '', '100A/WM', 5500.00, 'Rejected', NULL, '2026-10-19 06:46:17', '2026-10-19 06:46:36'),
(31, 3, 1, 1, 7, 'Fall prevention and safety monitoring', '2026-10-30', '01:50:00', '', '100A/WM', 5500.00, 'Completed', NULL, '2026-10-19 06:46:57', '2026-10-19 06:47:20'),
(32, 3, 1, 2, 2, 'second', '2026-10-31', '15:37:00', '', '100A/WM', 50000.00, 'Pending', NULL, '2026-10-19 07:32:17', '2026-10-19 07:32:17'),
(33, 3, 4, 2, 8, 'Companion Service', '2026-10-30', '16:01:00', '', '100A/WM', 1000.00, 'Completed', NULL, '2026-10-19 07:59:23', '2026-10-19 08:00:49'),
(34, 3, 5, 1, 10, 'Basic Nursing Care', '2026-10-30', '04:01:00', '', '100A/WM', 1500.00, 'Completed', NULL, '2026-10-19 07:59:50', '2026-10-19 08:01:20'),
(35, 3, 6, 3, 9, 'Patient Care at Home', '2026-10-31', '17:01:00', '', '100A/WM', 2500.00, 'Completed', NULL, '2026-10-19 08:00:10', '2026-10-19 08:01:39'),
(36, 3, 1, 1, 7, 'Fall prevention and safety monitoring', '2026-10-30', '17:19:00', '', '100A/WM', 5500.00, 'Completed', NULL, '2026-10-19 08:15:59', '2026-10-19 08:16:22'),
(37, 3, 1, 1, 7, 'Fall prevention and safety monitoring', '2026-10-29', '17:19:00', '', '100A/WM', 5500.00, 'Cancelled', 'Cancelled by user', '2026-10-19 08:17:22', '2026-10-19 08:17:31'),
(38, 3, 1, 1, 7, 'Fall prevention and safety monitoring', '2026-10-29', '13:21:00', '', '100A/WM', 5500.00, 'Rejected', NULL, '2026-10-19 08:17:58', '2026-10-19 08:18:04'),
(39, 3, 1, 1, 7, 'Fall prevention and safety monitoring', '2026-10-27', '16:21:00', '', '100A/WM', 5500.00, 'Rejected', NULL, '2026-10-19 08:18:18', '2026-10-19 08:18:34'),
(40, 3, 1, 1, 7, 'Fall prevention and safety monitoring', '2026-10-28', '19:47:00', '', '100A/WM', 5500.00, 'Completed', NULL, '2026-10-19 09:44:27', '2026-10-19 09:46:11'),
(41, 3, 1, 1, 1, 'Post-Surgery Assistance', '2026-10-30', '20:18:00', '', '155 home bahira town', 2000.00, 'Accepted', NULL, '2026-10-19 10:15:48', '2026-10-19 10:16:05');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_name` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`, `description`, `image`, `status`, `created_at`) VALUES
(1, 'Nursing', 'Access professional nursing care for health monitoring, medication assistance, wound care, and other nursing needs. Services are provided by qualified healthcare professionals according to individual care requirements.', 'uploads/services/ce22bc8a4350b73fc266aecf2ce769c1.jpg', 'Active', '2026-10-13 06:22:25'),
(2, 'Personal Assistance', 'Get reliable support with daily activities, personal tasks, mobility, and household needs. Our assistance services help individuals maintain their independence, comfort, and quality of life.', 'uploads/services/521ddc9db6cc149605e5dadc51c7f2c7.jpg', 'Active', '2026-10-13 06:22:38'),
(3, 'Daily Care', 'Receive compassionate and personalized care for elderly individuals and people who need extra support in their daily lives. Our care services focus on comfort, dignity, safety, and overall well-being.', 'uploads/services/fcc62df7b5dfd064abae23875b4cc9f9.jpg', 'Active', '2026-10-14 06:55:42'),
(4, 'Grocery & Shopping', 'Help with grocery shopping, purchasing essentials, and running errands.', 'uploads/services/70c102a203bbb15b50cb65da6ece2519.jpg', 'Active', '2026-10-19 10:20:53'),
(5, 'Transportation Assistance', 'Assistance with travelling to hospitals, appointments, and essential destinations.', 'uploads/services/4eb92aed6dd5a8b7c1bca91033cb88e4.jpg', 'Active', '2026-10-19 10:23:01'),
(6, 'Meal Preparation', 'Assistance with preparing meals and arranging food according to client needs.', 'uploads/services/4fd68843fe21930685cb444de2034f57.jpg', 'Active', '2026-10-19 10:24:19');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `provider_id` int(10) UNSIGNED DEFAULT NULL,
  `booking_id` int(10) UNSIGNED DEFAULT NULL,
  `subject` varchar(180) NOT NULL,
  `description` text NOT NULL,
  `status` enum('Pending','Under Review','Resolved') NOT NULL DEFAULT 'Pending',
  `admin_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `user_id`, `provider_id`, `booking_id`, `subject`, `description`, `status`, `admin_response`, `created_at`, `resolved_at`) VALUES
(1, 3, NULL, NULL, 'book', 'sadfafs werweqr fweqr wqerqewr', 'Under Review', NULL, '2026-10-13 10:13:14', NULL),
(2, 3, 1, 1, 'time issue', 'The provider\'s response time is incorrect.', 'Resolved', NULL, '2026-10-13 10:23:31', '2026-10-14 12:50:14'),
(3, 3, 1, 31, 'booking cancel without any reason', 'first confirm booking then cancel it or mujy batahia bi ni pyly confirm kr di thi', 'Resolved', 'we warn Ahamd hassan of this action.', '2026-10-19 06:50:26', '2026-10-19 11:51:39'),
(4, 3, NULL, NULL, 'wfeerf', 'dsfdsfewqfrqg', 'Pending', NULL, '2026-10-19 07:27:27', NULL),
(5, 3, 1, 31, 'sfdagf', 'dsgfadsfsadfasf', 'Pending', NULL, '2026-10-19 07:30:36', NULL),
(6, 3, 1, 30, 'sadfdsafsda', 'sadfsafwqg3resfdgef', 'Pending', NULL, '2026-10-19 07:30:45', NULL),
(7, 3, 6, 35, 'timing issue', 'yar ye banda na time sy ni poynhca', 'Resolved', 'i checked your response i will take strick action against it. i checked your response i will take strick action against it. i checked your response i will take strick action against it. i checked your response i will take strick action against it.', '2026-10-19 08:09:02', '2026-10-19 13:10:58');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `provider_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `provider_id`, `created_at`) VALUES
(8, 3, 1, '2026-10-13 11:35:31'),
(9, 7, 2, '2026-10-15 09:58:00');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `provider_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(180) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'system',
  `related_type` varchar(20) DEFAULT NULL,
  `related_id` int(10) UNSIGNED DEFAULT NULL,
  `event_key` varchar(120) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `provider_id`, `title`, `message`, `type`, `related_type`, `related_id`, `event_key`, `is_read`, `created_at`) VALUES
(1, NULL, 1, 'Verification approved', 'Your verification status is now Approved.', 'verification', NULL, NULL, NULL, 1, '2026-10-13 06:24:10'),
(2, NULL, 1, 'Verification approved', 'Your verification status is now Approved.', 'verification', NULL, NULL, NULL, 1, '2026-10-13 06:24:15'),
(3, NULL, 1, 'Verification approved', 'Your verification status is now Approved.', 'verification', NULL, NULL, NULL, 1, '2026-10-13 06:24:17'),
(4, NULL, 1, 'Verification approved', 'Your verification status is now Approved.', 'verification', NULL, NULL, NULL, 1, '2026-10-13 06:24:17'),
(5, NULL, 1, 'Verification approved', 'Your verification status is now Approved.', 'verification', NULL, NULL, NULL, 1, '2026-10-13 06:24:17'),
(6, NULL, 1, 'New booking request', 'Ahmad Hassan sent you a booking request for 23 Oct 2026.', 'booking', NULL, NULL, NULL, 1, '2026-10-13 06:27:30'),
(7, 3, NULL, 'Booking accepted', 'Ahmad Hassan marked your booking on 23 Oct 2026 as Accepted.', 'booking', NULL, NULL, NULL, 1, '2026-10-13 06:47:13'),
(8, 3, NULL, 'Booking completed', 'Ahmad Hassan marked your booking on 23 Oct 2026 as Completed.', 'booking', NULL, NULL, NULL, 1, '2026-10-13 06:47:15'),
(9, NULL, 1, 'New booking request', 'Ahmad Hassan sent you a booking request for 30 Oct 2026.', 'booking', NULL, NULL, NULL, 1, '2026-10-13 07:35:42'),
(10, 3, NULL, 'Booking accepted', 'Ahmad Hassan marked your booking on 30 Oct 2026 as Accepted.', 'booking', NULL, NULL, NULL, 1, '2026-10-13 07:36:20'),
(11, 3, NULL, 'Booking completed', 'Ahmad Hassan marked your booking on 30 Oct 2026 as Completed.', 'booking', NULL, NULL, NULL, 1, '2026-10-13 07:36:27'),
(12, NULL, 1, 'New review', 'Ahmad Hassan rated you 3 out of 5.', 'rating', NULL, NULL, NULL, 0, '2026-10-13 07:37:22'),
(13, NULL, 1, 'New review', 'Ahmad Hassan rated you 5 out of 5.', 'rating', NULL, NULL, NULL, 0, '2026-10-13 07:37:29'),
(14, NULL, 1, 'New booking request', 'Ahmad Hassan sent you a booking request for 23 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-13 10:12:02'),
(15, 3, NULL, 'Complaint update', 'Your complaint #1 is now Under Review.', 'complaint', NULL, NULL, NULL, 1, '2026-10-13 10:24:51'),
(16, 3, NULL, 'Cash payment confirmed', 'The provider confirmed your cash payment for the booking on 30 Oct 2026. It is now waiting for Admin verification.', 'payment', NULL, NULL, NULL, 1, '2026-10-13 11:05:54'),
(17, 3, NULL, 'Cash payment confirmed', 'The provider confirmed your cash payment for the booking on 23 Oct 2026. It is now waiting for Admin verification.', 'payment', NULL, NULL, NULL, 1, '2026-10-13 11:05:57'),
(18, 3, NULL, 'Payment verified', 'Your cash payment for the booking on 23 Oct 2026 was verified.', 'payment', NULL, NULL, NULL, 1, '2026-10-13 11:06:52'),
(19, NULL, 1, 'Payment verified', 'The cash payment for the booking on 23 Oct 2026 was verified. The full amount is yours; AASRA charges no commission.', 'payment', NULL, NULL, NULL, 0, '2026-10-13 11:06:52'),
(20, 3, NULL, 'Payment verified', 'Your cash payment for the booking on 30 Oct 2026 was verified.', 'payment', NULL, NULL, NULL, 1, '2026-10-13 11:06:55'),
(21, NULL, 1, 'Payment verified', 'The cash payment for the booking on 30 Oct 2026 was verified. The full amount is yours; AASRA charges no commission.', 'payment', NULL, NULL, NULL, 0, '2026-10-13 11:06:55'),
(22, NULL, 1, 'Booking cancelled', 'Ahmad Hassan cancelled the booking on 23 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-13 11:07:32'),
(23, NULL, 1, 'New booking request', 'Ahmad Hassan sent you a booking request for 23 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-13 11:08:45'),
(24, 3, NULL, 'Booking accepted', 'Ahmad Hassan marked your booking on 23 Oct 2026 as Accepted.', 'booking', NULL, NULL, NULL, 1, '2026-10-13 11:09:08'),
(25, 3, NULL, 'Booking completed', 'Ahmad Hassan marked your booking on 23 Oct 2026 as Completed.', 'booking', NULL, NULL, NULL, 1, '2026-10-13 11:09:14'),
(26, NULL, 1, 'New booking request', 'Ahmad Hassan sent you a booking request for 30 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-13 11:10:18'),
(27, 3, NULL, 'Booking accepted', 'Ahmad Hassan marked your booking on 30 Oct 2026 as Accepted.', 'booking', NULL, NULL, NULL, 1, '2026-10-13 11:10:59'),
(28, 3, NULL, 'Booking completed', 'Ahmad Hassan marked your booking on 30 Oct 2026 as Completed.', 'booking', NULL, NULL, NULL, 1, '2026-10-13 11:11:29'),
(29, 3, NULL, 'Cash payment confirmed', 'The provider confirmed your cash payment for the booking on 30 Oct 2026. It is now waiting for Admin verification.', 'payment', NULL, NULL, NULL, 1, '2026-10-13 11:12:01'),
(30, NULL, 1, 'Payment confirmation rejected', 'The cash confirmation for the booking on 30 Oct 2026 was rejected: user said it was not paid', 'payment', NULL, NULL, NULL, 0, '2026-10-13 11:13:55'),
(31, 3, NULL, 'Payment confirmation rejected', 'The provider\'s cash confirmation for the booking on 30 Oct 2026 was not verified: user said it was not paid', 'payment', NULL, NULL, NULL, 1, '2026-10-13 11:13:55'),
(32, 3, NULL, 'Cash payment confirmed', 'The provider confirmed your cash payment for the booking on 30 Oct 2026. It is now waiting for Admin verification.', 'payment', NULL, NULL, NULL, 1, '2026-10-13 11:14:56'),
(33, NULL, 1, 'New booking request', 'Ahmad Hassan sent you a booking request for 04 Nov 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-14 06:04:43'),
(34, NULL, 1, 'New booking request', 'Ahmad Hassan sent you a booking request for 29 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-14 06:50:16'),
(35, 3, NULL, 'Booking rejected', 'Ahmad Hassan marked your booking on 04 Nov 2026 as Rejected.', 'booking', NULL, NULL, NULL, 1, '2026-10-14 06:50:32'),
(36, 3, NULL, 'Complaint update', 'Your complaint #2 is now Resolved.', 'complaint', NULL, NULL, NULL, 1, '2026-10-14 07:50:14'),
(37, NULL, 2, 'Verification approved', 'Your verification status is now Approved.', 'verification', NULL, NULL, NULL, 0, '2026-10-14 07:50:26'),
(38, NULL, 3, 'Verification approved', 'Your verification status is now Approved.', 'verification', NULL, NULL, NULL, 1, '2026-10-14 08:04:00'),
(39, NULL, 1, 'New booking request', 'Ali Khan sent you a booking request for 30 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-14 11:47:47'),
(40, 3, NULL, 'Booking accepted', 'Ahmad Hassan marked your booking on 29 Oct 2026 as Accepted.', 'booking', NULL, NULL, NULL, 1, '2026-10-14 11:49:17'),
(41, 3, NULL, 'Booking completed', 'Ahmad Hassan marked your booking on 29 Oct 2026 as Completed.', 'booking', NULL, NULL, NULL, 1, '2026-10-14 11:49:33'),
(42, 3, NULL, 'Cash payment confirmed', 'The provider confirmed your cash payment for the booking on 29 Oct 2026. It is now waiting for Admin verification.', 'payment', NULL, NULL, NULL, 1, '2026-10-14 11:50:36'),
(43, 3, NULL, 'Booking accepted', 'Ahmad Hassan marked your booking on 30 Oct 2026 as Accepted.', 'booking', NULL, NULL, NULL, 1, '2026-10-15 05:36:33'),
(44, 3, NULL, 'Booking completed', 'Ahmad Hassan marked your booking on 30 Oct 2026 as Completed.', 'booking', NULL, NULL, NULL, 1, '2026-10-15 05:36:39'),
(45, NULL, 3, 'New booking request', 'Ali Khan sent you a booking request for 21 Oct 2026.', 'booking', NULL, NULL, NULL, 1, '2026-10-15 06:34:26'),
(46, NULL, 3, 'Booking cancelled', 'Ali Khan cancelled the booking on 21 Oct 2026.', 'booking', NULL, NULL, NULL, 1, '2026-10-15 06:35:48'),
(47, NULL, 3, 'New booking request', 'Ali Khan sent you a booking request for 30 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 06:38:35'),
(48, 3, NULL, 'Booking accepted', 'hamza marked your booking on 30 Oct 2026 as Accepted.', 'booking', NULL, NULL, NULL, 1, '2026-10-15 06:39:07'),
(49, 3, NULL, 'Booking completed', 'hamza marked your booking on 30 Oct 2026 as Completed.', 'booking', NULL, NULL, NULL, 1, '2026-10-15 06:39:29'),
(50, 3, NULL, 'Cash payment confirmed', 'The provider confirmed receiving your cash payment for the booking on 30 Oct 2026. The payment is now marked Paid.', 'payment', NULL, NULL, NULL, 1, '2026-10-15 06:39:36'),
(51, NULL, 3, 'New booking request', 'Ali Khan sent you a booking request for 29 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 06:44:41'),
(52, 3, NULL, 'Booking rejected', 'hamza marked your booking on 29 Oct 2026 as Rejected.', 'booking', NULL, NULL, NULL, 1, '2026-10-15 06:45:04'),
(53, NULL, 2, 'New booking request', 'Ali Khan sent you a booking request for 29 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 07:07:04'),
(54, NULL, 2, 'New booking request', 'Ali Khan sent you a booking request for 28 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 07:07:18'),
(55, NULL, 2, 'New booking request', 'Ali Khan sent you a booking request for 29 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 07:07:55'),
(56, NULL, 2, 'Booking cancelled', 'Ali Khan cancelled the booking on 29 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 07:08:29'),
(57, 3, NULL, 'Booking rejected', 'arslan marked your booking on 29 Oct 2026 as Rejected.', 'booking', NULL, NULL, NULL, 1, '2026-10-15 07:09:35'),
(58, 3, NULL, 'Booking accepted', 'arslan marked your booking on 28 Oct 2026 as Accepted.', 'booking', NULL, NULL, NULL, 1, '2026-10-15 07:10:08'),
(59, 3, NULL, 'Booking completed', 'arslan marked your booking on 28 Oct 2026 as Completed.', 'booking', NULL, NULL, NULL, 1, '2026-10-15 07:10:11'),
(60, 3, NULL, 'Cash payment confirmed', 'The provider confirmed receiving your cash payment for the booking on 28 Oct 2026. The payment is now marked Paid.', 'payment', NULL, NULL, NULL, 1, '2026-10-15 07:10:28'),
(61, NULL, 1, 'New booking request', 'Ali Khan sent you a booking request for 30 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 07:27:02'),
(62, NULL, 1, 'Booking cancelled', 'Ali Khan cancelled the booking on 30 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 07:27:56'),
(63, NULL, 1, 'New booking request', 'Ali Khan sent you a booking request for 30 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 07:29:24'),
(64, NULL, 1, 'Booking cancelled', 'Ali Khan cancelled the booking on 30 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 07:30:15'),
(65, NULL, 1, 'New booking request', 'Ali Khan sent you a booking request for 30 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 07:40:07'),
(66, NULL, 1, 'New booking request', 'Ali Khan sent you a booking request for 30 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 07:40:22'),
(67, 3, NULL, 'Booking accepted', 'Ahmad Hassan marked your booking on 30 Oct 2026 as Accepted.', 'booking', NULL, NULL, NULL, 1, '2026-10-15 08:10:19'),
(68, 3, NULL, 'Booking completed', 'Ahmad Hassan marked your booking on 30 Oct 2026 as Completed.', 'booking', NULL, NULL, NULL, 1, '2026-10-15 08:10:42'),
(69, 3, NULL, 'Cash payment confirmed', 'The provider confirmed receiving your cash payment for the booking on 30 Oct 2026. The payment is now marked Paid.', 'payment', NULL, NULL, NULL, 1, '2026-10-15 08:11:15'),
(70, NULL, 1, 'New booking request', 'arsal sent you a booking request for 30 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 09:48:58'),
(71, NULL, 1, 'New booking request', 'arsal sent you a booking request for 30 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 09:49:24'),
(72, NULL, 1, 'Booking cancelled', 'arsal cancelled the booking on 30 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 09:56:51'),
(73, NULL, 1, 'New booking request', 'arsal sent you a booking request for 30 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 09:57:37'),
(74, 7, NULL, 'Booking accepted', 'Ahmad Hassan marked your booking on 30 Oct 2026 as Accepted.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 10:24:33'),
(75, 7, NULL, 'Booking completed', 'Ahmad Hassan marked your booking on 30 Oct 2026 as Completed.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 10:25:08'),
(76, 7, NULL, 'Cash payment confirmed', 'The provider confirmed receiving your cash payment for the booking on 30 Oct 2026. The payment is now marked Paid.', 'payment', NULL, NULL, NULL, 0, '2026-10-15 10:25:40'),
(77, 7, NULL, 'Booking accepted', 'Ahmad Hassan marked your booking on 30 Oct 2026 as Accepted.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 10:36:39'),
(78, NULL, 1, 'New booking request', 'arsal sent you a booking request for 29 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 11:03:38'),
(79, NULL, 1, 'New booking request', 'arsal sent you a booking request for 29 Oct 2026.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 11:04:50'),
(80, 7, NULL, 'Booking cancelled', 'Ahmad Hassan marked your booking on 30 Oct 2026 as Cancelled.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 11:07:24'),
(81, 7, NULL, 'Booking accepted', 'Ahmad Hassan marked your booking on 29 Oct 2026 as Accepted.', 'booking', NULL, NULL, NULL, 0, '2026-10-15 11:32:28'),
(82, NULL, 1, 'New booking request', 'You received a new booking request #24 from arsal for second on 28 Oct 2026 at 7:40 PM.', 'booking', 'booking', 24, 'p:booking:24:requested', 0, '2026-10-15 11:38:20'),
(83, 7, NULL, 'Booking accepted', 'Your booking #24 for second with Ahmad Hassan on 28 Oct 2026 at 7:40 PM has been accepted.', 'booking', 'booking', 24, 'u:booking:24:accepted', 0, '2026-10-15 11:39:48'),
(84, 7, NULL, 'Booking completed', 'Ahmad Hassan marked booking #24 for second as Completed.', 'booking', 'booking', 24, 'u:booking:24:completed', 0, '2026-10-15 11:40:39'),
(85, 7, NULL, 'Cash payment confirmed', 'Ahmad Hassan confirmed receiving your cash payment of PKR 50,000 for booking #24 (second). Payment status: Paid.', 'payment', 'payment', 32, 'u:payment:24:paid', 0, '2026-10-15 11:41:11'),
(86, NULL, 1, 'Payment marked Paid', 'Payment for booking #24 from arsal (second) has been marked Paid. Amount: PKR 50,000.', 'payment', 'payment', 32, 'p:payment:24:paid', 0, '2026-10-15 11:41:11'),
(87, NULL, 1, 'New booking request', 'You received a new booking request #25 from saqib for Fall prevention and safety monitoring on 22 Oct 2026 at 2:58 PM.', 'booking', 'booking', 25, 'p:booking:25:requested', 0, '2026-10-19 05:55:58'),
(88, 13, NULL, 'Booking accepted', 'Your booking #25 for Fall prevention and safety monitoring with Ahmad Hassan on 22 Oct 2026 at 2:58 PM has been accepted.', 'booking', 'booking', 25, 'u:booking:25:accepted', 0, '2026-10-19 05:56:56'),
(89, 13, NULL, 'Booking completed', 'Ahmad Hassan marked booking #25 for Fall prevention and safety monitoring as Completed.', 'booking', 'booking', 25, 'u:booking:25:completed', 0, '2026-10-19 05:57:35'),
(90, 13, NULL, 'Cash payment confirmed', 'Ahmad Hassan confirmed receiving your cash payment of PKR 5,500 for booking #25 (Fall prevention and safety monitoring). Payment status: Paid.', 'payment', 'payment', 34, 'u:payment:25:paid', 0, '2026-10-19 05:58:08'),
(91, NULL, 1, 'Payment marked Paid', 'Payment for booking #25 from saqib (Fall prevention and safety monitoring) has been marked Paid. Amount: PKR 5,500.', 'payment', 'payment', 34, 'p:payment:25:paid', 0, '2026-10-19 05:58:08'),
(92, 3, NULL, 'Cash payment confirmed', 'Ahmad Hassan confirmed receiving your cash payment of PKR 50,000 for booking #4 (Assistance). Payment status: Paid.', 'payment', 'payment', 4, 'u:payment:4:paid', 1, '2026-10-19 05:58:21'),
(93, NULL, 1, 'Payment marked Paid', 'Payment for booking #4 from Ali Khan (Assistance) has been marked Paid. Amount: PKR 50,000.', 'payment', 'payment', 4, 'p:payment:4:paid', 0, '2026-10-19 05:58:21'),
(94, 3, NULL, 'Cash payment confirmed', 'Ahmad Hassan confirmed receiving your cash payment of PKR 50,000 for booking #8 (Assistance). Payment status: Paid.', 'payment', 'payment', 10, 'u:payment:8:paid', 1, '2026-10-19 05:58:23'),
(95, NULL, 1, 'Payment marked Paid', 'Payment for booking #8 from Ali Khan (Assistance) has been marked Paid. Amount: PKR 50,000.', 'payment', 'payment', 10, 'p:payment:8:paid', 0, '2026-10-19 05:58:23'),
(96, NULL, 1, 'New review', 'saqib rated you 3 out of 5.', 'rating', NULL, NULL, NULL, 0, '2026-10-19 05:59:02'),
(97, NULL, 1, 'New booking request', 'You received a new booking request #26 from saqib for Fall prevention and safety monitoring on 30 Oct 2026 at 3:02 PM.', 'booking', 'booking', 26, 'p:booking:26:requested', 0, '2026-10-19 06:00:05'),
(98, NULL, 1, 'Booking cancelled', 'Booking #26 from saqib for Fall prevention and safety monitoring on 30 Oct 2026 has been cancelled.', 'booking', 'booking', 26, 'p:booking:26:cancelled', 0, '2026-10-19 06:00:12'),
(99, NULL, 1, 'New booking request', 'You received a new booking request #27 from saqib for Fall prevention and safety monitoring on 30 Oct 2026 at 4:05 PM.', 'booking', 'booking', 27, 'p:booking:27:requested', 0, '2026-10-19 06:01:23'),
(100, 13, NULL, 'Booking rejected', 'Your booking #27 for Fall prevention and safety monitoring with Ahmad Hassan has been rejected.', 'booking', 'booking', 27, 'u:booking:27:rejected', 0, '2026-10-19 06:01:34'),
(101, NULL, 1, 'New booking request', 'You received a new booking request #28 from Ali Khan for Fall prevention and safety monitoring on 30 Oct 2026 at 3:35 AM.', 'booking', 'booking', 28, 'p:booking:28:requested', 0, '2026-10-19 06:31:37'),
(102, NULL, 1, 'Booking cancelled', 'Booking #28 from Ali Khan for Fall prevention and safety monitoring on 30 Oct 2026 has been cancelled.', 'booking', 'booking', 28, 'p:booking:28:cancelled', 0, '2026-10-19 06:31:52'),
(103, NULL, 1, 'New booking request', 'You received a new booking request #29 from Ali Khan for Fall prevention and safety monitoring on 30 Oct 2026 at 3:49 AM.', 'booking', 'booking', 29, 'p:booking:29:requested', 0, '2026-10-19 06:45:38'),
(104, NULL, 1, 'Booking cancelled', 'Booking #29 from Ali Khan for Fall prevention and safety monitoring on 30 Oct 2026 has been cancelled.', 'booking', 'booking', 29, 'p:booking:29:cancelled', 0, '2026-10-19 06:45:46'),
(105, NULL, 1, 'New booking request', 'You received a new booking request #30 from Ali Khan for Fall prevention and safety monitoring on 30 Oct 2026 at 2:51 AM.', 'booking', 'booking', 30, 'p:booking:30:requested', 0, '2026-10-19 06:46:17'),
(106, 3, NULL, 'Booking rejected', 'Your booking #30 for Fall prevention and safety monitoring with Ahmad Hassan has been rejected.', 'booking', 'booking', 30, 'u:booking:30:rejected', 1, '2026-10-19 06:46:36'),
(107, NULL, 1, 'New booking request', 'You received a new booking request #31 from Ali Khan for Fall prevention and safety monitoring on 30 Oct 2026 at 1:50 AM.', 'booking', 'booking', 31, 'p:booking:31:requested', 0, '2026-10-19 06:46:57'),
(108, 3, NULL, 'Booking accepted', 'Your booking #31 for Fall prevention and safety monitoring with Ahmad Hassan on 30 Oct 2026 at 1:50 AM has been accepted.', 'booking', 'booking', 31, 'u:booking:31:accepted', 1, '2026-10-19 06:47:04'),
(109, 3, NULL, 'Booking completed', 'Ahmad Hassan marked booking #31 for Fall prevention and safety monitoring as Completed.', 'booking', 'booking', 31, 'u:booking:31:completed', 1, '2026-10-19 06:47:20'),
(110, 3, NULL, 'Cash payment confirmed', 'Ahmad Hassan confirmed receiving your cash payment of PKR 5,500 for booking #31 (Fall prevention and safety monitoring). Payment status: Paid.', 'payment', 'payment', 41, 'u:payment:31:paid', 1, '2026-10-19 06:47:37'),
(111, NULL, 1, 'Payment marked Paid', 'Payment for booking #31 from Ali Khan (Fall prevention and safety monitoring) has been marked Paid. Amount: PKR 5,500.', 'payment', 'payment', 41, 'p:payment:31:paid', 0, '2026-10-19 06:47:37'),
(112, 3, NULL, 'Complaint update', 'Your complaint #3 regarding booking #31 has been updated. Status: Resolved. Response: we warn Ahamd hassan of this action.', 'complaint', 'complaint', 3, NULL, 0, '2026-10-19 06:51:39'),
(113, NULL, 1, 'Complaint update', 'Complaint #3 related to booking #31 has been updated. Status: Resolved.', 'complaint', 'complaint', 3, NULL, 0, '2026-10-19 06:51:39'),
(114, NULL, 3, 'New review', 'Ali Khan rated you 4 out of 5.', 'rating', NULL, NULL, NULL, 0, '2026-10-19 07:02:56'),
(115, NULL, 2, 'New review', 'Ali Khan rated you 2 out of 5.', 'rating', NULL, NULL, NULL, 0, '2026-10-19 07:03:10'),
(116, NULL, 1, 'New review', 'Ali Khan rated you 5 out of 5.', 'rating', NULL, NULL, NULL, 0, '2026-10-19 07:03:29'),
(117, NULL, 6, 'Verification approved', 'Your provider verification status has changed to Approved. Your account is active and you are visible to users.', 'verification', 'verification', 6, NULL, 0, '2026-10-19 07:06:32'),
(118, NULL, 5, 'Verification approved', 'Your provider verification status has changed to Approved. Your account is active and you are visible to users.', 'verification', 'verification', 5, NULL, 0, '2026-10-19 07:07:12'),
(119, NULL, 4, 'Verification approved', 'Your provider verification status has changed to Approved. Your account is active and you are visible to users.', 'verification', 'verification', 4, NULL, 0, '2026-10-19 07:08:27'),
(120, NULL, 1, 'New booking request', 'You received a new booking request #32 from Ali Khan for second on 31 Oct 2026 at 3:37 PM.', 'booking', 'booking', 32, 'p:booking:32:requested', 0, '2026-10-19 07:32:17'),
(121, NULL, 4, 'New booking request', 'You received a new booking request #33 from Ali Khan for Companion Service on 30 Oct 2026 at 4:01 PM.', 'booking', 'booking', 33, 'p:booking:33:requested', 0, '2026-10-19 07:59:23'),
(122, NULL, 5, 'New booking request', 'You received a new booking request #34 from Ali Khan for Basic Nursing Care on 30 Oct 2026 at 4:01 AM.', 'booking', 'booking', 34, 'p:booking:34:requested', 0, '2026-10-19 07:59:50'),
(123, NULL, 6, 'New booking request', 'You received a new booking request #35 from Ali Khan for Patient Care at Home on 31 Oct 2026 at 5:01 PM.', 'booking', 'booking', 35, 'p:booking:35:requested', 0, '2026-10-19 08:00:10'),
(124, 3, NULL, 'Booking accepted', 'Your booking #33 for Companion Service with islam on 30 Oct 2026 at 4:01 PM has been accepted.', 'booking', 'booking', 33, 'u:booking:33:accepted', 0, '2026-10-19 08:00:48'),
(125, 3, NULL, 'Booking completed', 'islam marked booking #33 for Companion Service as Completed.', 'booking', 'booking', 33, 'u:booking:33:completed', 0, '2026-10-19 08:00:49'),
(126, 3, NULL, 'Cash payment confirmed', 'islam confirmed receiving your cash payment of PKR 1,000 for booking #33 (Companion Service). Payment status: Paid.', 'payment', 'payment', 44, 'u:payment:33:paid', 0, '2026-10-19 08:00:53'),
(127, NULL, 4, 'Payment marked Paid', 'Payment for booking #33 from Ali Khan (Companion Service) has been marked Paid. Amount: PKR 1,000.', 'payment', 'payment', 44, 'p:payment:33:paid', 0, '2026-10-19 08:00:53'),
(128, 3, NULL, 'Booking accepted', 'Your booking #34 for Basic Nursing Care with imran on 30 Oct 2026 at 4:01 AM has been accepted.', 'booking', 'booking', 34, 'u:booking:34:accepted', 0, '2026-10-19 08:01:18'),
(129, 3, NULL, 'Booking completed', 'imran marked booking #34 for Basic Nursing Care as Completed.', 'booking', 'booking', 34, 'u:booking:34:completed', 0, '2026-10-19 08:01:20'),
(130, 3, NULL, 'Cash payment confirmed', 'imran confirmed receiving your cash payment of PKR 1,500 for booking #34 (Basic Nursing Care). Payment status: Paid.', 'payment', 'payment', 45, 'u:payment:34:paid', 0, '2026-10-19 08:01:23'),
(131, NULL, 5, 'Payment marked Paid', 'Payment for booking #34 from Ali Khan (Basic Nursing Care) has been marked Paid. Amount: PKR 1,500.', 'payment', 'payment', 45, 'p:payment:34:paid', 0, '2026-10-19 08:01:23'),
(132, 3, NULL, 'Booking accepted', 'Your booking #35 for Patient Care at Home with salman on 31 Oct 2026 at 5:01 PM has been accepted.', 'booking', 'booking', 35, 'u:booking:35:accepted', 0, '2026-10-19 08:01:38'),
(133, 3, NULL, 'Booking completed', 'salman marked booking #35 for Patient Care at Home as Completed.', 'booking', 'booking', 35, 'u:booking:35:completed', 0, '2026-10-19 08:01:39'),
(134, 3, NULL, 'Cash payment confirmed', 'salman confirmed receiving your cash payment of PKR 2,500 for booking #35 (Patient Care at Home). Payment status: Paid.', 'payment', 'payment', 46, 'u:payment:35:paid', 0, '2026-10-19 08:01:42'),
(135, NULL, 6, 'Payment marked Paid', 'Payment for booking #35 from Ali Khan (Patient Care at Home) has been marked Paid. Amount: PKR 2,500.', 'payment', 'payment', 46, 'p:payment:35:paid', 0, '2026-10-19 08:01:42'),
(136, NULL, 6, 'New review', 'Ali Khan rated you 3 out of 5.', 'rating', NULL, NULL, NULL, 0, '2026-10-19 08:02:02'),
(137, NULL, 5, 'New review', 'Ali Khan rated you 3 out of 5.', 'rating', NULL, NULL, NULL, 0, '2026-10-19 08:02:23'),
(138, NULL, 4, 'New review', 'Ali Khan rated you 2 out of 5.', 'rating', NULL, NULL, NULL, 0, '2026-10-19 08:02:49'),
(139, NULL, 7, 'Verification approved', 'Your provider verification status has changed to Approved. Your account is active and you are visible to users.', 'verification', 'verification', 7, NULL, 0, '2026-10-19 08:04:06'),
(140, 3, NULL, 'Complaint update', 'Your complaint #7 regarding booking #35 (Patient Care at Home) has been updated. Status: Resolved. Response: i checked your response i will take strick action against it. i checked your response i will take strick action against it. i checked your response i will take strick action against it. i checked your response i will take strick action against it.', 'complaint', 'complaint', 7, NULL, 0, '2026-10-19 08:10:58'),
(141, NULL, 6, 'Complaint update', 'Complaint #7 related to booking #35 (Patient Care at Home) has been updated. Status: Resolved.', 'complaint', 'complaint', 7, NULL, 0, '2026-10-19 08:10:58'),
(142, NULL, 1, 'New booking request', 'You received a new booking request #36 from Ali Khan for Fall prevention and safety monitoring on 30 Oct 2026 at 5:19 PM.', 'booking', 'booking', 36, 'p:booking:36:requested', 0, '2026-10-19 08:15:59'),
(143, 3, NULL, 'Booking accepted', 'Your booking #36 for Fall prevention and safety monitoring with Ahmad Hassan on 30 Oct 2026 at 5:19 PM has been accepted.', 'booking', 'booking', 36, 'u:booking:36:accepted', 0, '2026-10-19 08:16:20'),
(144, 3, NULL, 'Booking completed', 'Ahmad Hassan marked booking #36 for Fall prevention and safety monitoring as Completed.', 'booking', 'booking', 36, 'u:booking:36:completed', 0, '2026-10-19 08:16:22'),
(145, 3, NULL, 'Cash payment confirmed', 'Ahmad Hassan confirmed receiving your cash payment of PKR 5,500 for booking #36 (Fall prevention and safety monitoring). Payment status: Paid.', 'payment', 'payment', 50, 'u:payment:36:paid', 0, '2026-10-19 08:16:27'),
(146, NULL, 1, 'Payment marked Paid', 'Payment for booking #36 from Ali Khan (Fall prevention and safety monitoring) has been marked Paid. Amount: PKR 5,500.', 'payment', 'payment', 50, 'p:payment:36:paid', 0, '2026-10-19 08:16:27'),
(147, NULL, 1, 'New booking request', 'You received a new booking request #37 from Ali Khan for Fall prevention and safety monitoring on 29 Oct 2026 at 5:19 PM.', 'booking', 'booking', 37, 'p:booking:37:requested', 0, '2026-10-19 08:17:22'),
(148, NULL, 1, 'Booking cancelled', 'Booking #37 from Ali Khan for Fall prevention and safety monitoring on 29 Oct 2026 has been cancelled.', 'booking', 'booking', 37, 'p:booking:37:cancelled', 0, '2026-10-19 08:17:31'),
(149, NULL, 1, 'New booking request', 'You received a new booking request #38 from Ali Khan for Fall prevention and safety monitoring on 29 Oct 2026 at 1:21 PM.', 'booking', 'booking', 38, 'p:booking:38:requested', 0, '2026-10-19 08:17:58'),
(150, 3, NULL, 'Booking rejected', 'Your booking #38 for Fall prevention and safety monitoring with Ahmad Hassan has been rejected.', 'booking', 'booking', 38, 'u:booking:38:rejected', 0, '2026-10-19 08:18:04'),
(151, NULL, 1, 'New booking request', 'You received a new booking request #39 from Ali Khan for Fall prevention and safety monitoring on 27 Oct 2026 at 4:21 PM.', 'booking', 'booking', 39, 'p:booking:39:requested', 0, '2026-10-19 08:18:18'),
(152, 3, NULL, 'Booking rejected', 'Your booking #39 for Fall prevention and safety monitoring with Ahmad Hassan has been rejected.', 'booking', 'booking', 39, 'u:booking:39:rejected', 0, '2026-10-19 08:18:34'),
(153, NULL, 1, 'New booking request', 'You received a new booking request #40 from Ali Khan for Fall prevention and safety monitoring on 28 Oct 2026 at 7:47 PM.', 'booking', 'booking', 40, 'p:booking:40:requested', 0, '2026-10-19 09:44:27'),
(154, 3, NULL, 'Booking accepted', 'Your booking #40 for Fall prevention and safety monitoring with Ahmad Hassan on 28 Oct 2026 at 7:47 PM has been accepted.', 'booking', 'booking', 40, 'u:booking:40:accepted', 0, '2026-10-19 09:44:34'),
(155, 3, NULL, 'Booking completed', 'Ahmad Hassan marked booking #40 for Fall prevention and safety monitoring as Completed.', 'booking', 'booking', 40, 'u:booking:40:completed', 0, '2026-10-19 09:46:11'),
(156, NULL, 8, 'Account inactive', 'An administrator set your provider account to Inactive.', 'account', NULL, NULL, NULL, 0, '2026-10-19 10:03:16'),
(157, NULL, 7, 'Verification rejected', 'Your provider verification status has changed to Rejected. Please upload corrected documents.', 'verification', 'verification', 7, NULL, 0, '2026-10-19 10:03:30'),
(158, NULL, 1, 'New booking request', 'You received a new booking request #41 from Ali Khan for Post-Surgery Assistance on 30 Oct 2026 at 8:18 PM.', 'booking', 'booking', 41, 'p:booking:41:requested', 0, '2026-10-19 10:15:48'),
(159, 3, NULL, 'Booking accepted', 'Your booking #41 for Post-Surgery Assistance with Ahmad Hassan on 30 Oct 2026 at 8:18 PM has been accepted.', 'booking', 'booking', 41, 'u:booking:41:accepted', 0, '2026-10-19 10:16:05');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `booking_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash') NOT NULL DEFAULT 'Cash',
  `payment_status` enum('Pending','Verification Requested','Paid','Rejected','Failed') NOT NULL DEFAULT 'Pending',
  `transaction_reference` varchar(120) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `provider_confirmed_at` datetime DEFAULT NULL,
  `verified_by` int(10) UNSIGNED DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `admin_note` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `amount`, `payment_method`, `payment_status`, `transaction_reference`, `paid_at`, `provider_confirmed_at`, `verified_by`, `verified_at`, `admin_note`, `created_at`, `updated_at`) VALUES
(1, 1, 50000.00, 'Cash', 'Paid', NULL, '2026-10-13 16:06:52', '2026-10-13 16:05:57', 1, '2026-10-13 16:06:52', NULL, '2026-10-13 06:47:15', '2026-10-13 11:06:52'),
(2, 2, 2000.00, 'Cash', 'Paid', NULL, '2026-10-13 16:06:55', '2026-10-13 16:05:54', 1, '2026-10-13 16:06:55', NULL, '2026-10-13 07:36:27', '2026-10-13 11:06:55'),
(3, 3, 50000.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-13 11:04:21', '2026-10-13 11:04:21'),
(4, 4, 50000.00, 'Cash', 'Paid', NULL, '2026-10-19 10:58:21', '2026-10-19 10:58:21', NULL, NULL, NULL, '2026-10-13 11:08:45', '2026-10-19 05:58:21'),
(6, 5, 2000.00, 'Cash', 'Paid', NULL, '2026-10-13 16:14:56', '2026-10-13 16:14:56', NULL, NULL, NULL, '2026-10-13 11:10:18', '2026-10-15 07:12:36'),
(8, 6, 50000.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-14 06:04:43', '2026-10-14 06:04:43'),
(9, 7, 3000.00, 'Cash', 'Paid', NULL, '2026-10-14 16:50:36', '2026-10-14 16:50:36', NULL, NULL, NULL, '2026-10-14 06:50:16', '2026-10-15 07:12:36'),
(10, 8, 50000.00, 'Cash', 'Paid', NULL, '2026-10-19 10:58:23', '2026-10-19 10:58:23', NULL, NULL, NULL, '2026-10-14 11:47:47', '2026-10-19 05:58:23'),
(13, 9, 8000.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-15 06:34:26', '2026-10-15 06:34:26'),
(14, 10, 8000.00, 'Cash', 'Paid', NULL, '2026-10-15 11:39:36', '2026-10-15 11:39:36', NULL, NULL, NULL, '2026-10-15 06:38:35', '2026-10-15 06:39:36'),
(16, 11, 8000.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-15 06:44:41', '2026-10-15 06:44:41'),
(17, 12, 7000.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-15 07:07:04', '2026-10-15 07:07:04'),
(18, 13, 7000.00, 'Cash', 'Paid', NULL, '2026-10-15 12:10:28', '2026-10-15 12:10:28', NULL, NULL, NULL, '2026-10-15 07:07:18', '2026-10-15 07:10:28'),
(19, 14, 7000.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-15 07:07:55', '2026-10-15 07:07:55'),
(21, 15, 50000.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-15 07:27:02', '2026-10-15 07:27:02'),
(22, 16, 50000.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-15 07:29:24', '2026-10-15 07:29:24'),
(23, 17, 50000.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-15 07:40:07', '2026-10-15 07:40:07'),
(24, 18, 50000.00, 'Cash', 'Paid', NULL, '2026-10-15 13:11:15', '2026-10-15 13:11:15', NULL, NULL, NULL, '2026-10-15 07:40:22', '2026-10-15 08:11:15'),
(26, 19, 50000.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-15 09:48:58', '2026-10-15 09:48:58'),
(27, 20, 50000.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-15 09:49:24', '2026-10-15 09:49:24'),
(28, 21, 50000.00, 'Cash', 'Paid', NULL, '2026-10-15 15:25:40', '2026-10-15 15:25:40', NULL, NULL, NULL, '2026-10-15 09:57:37', '2026-10-15 10:25:40'),
(30, 22, 2000.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-15 11:03:38', '2026-10-15 11:03:38'),
(31, 23, 50000.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-15 11:04:50', '2026-10-15 11:04:50'),
(32, 24, 50000.00, 'Cash', 'Paid', NULL, '2026-10-15 16:41:11', '2026-10-15 16:41:11', NULL, NULL, NULL, '2026-10-15 11:38:20', '2026-10-15 11:41:11'),
(34, 25, 5500.00, 'Cash', 'Paid', NULL, '2026-10-19 10:58:08', '2026-10-19 10:58:08', NULL, NULL, NULL, '2026-10-19 05:55:58', '2026-10-19 05:58:08'),
(36, 26, 5500.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-19 06:00:05', '2026-10-19 06:00:05'),
(37, 27, 5500.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-19 06:01:23', '2026-10-19 06:01:23'),
(38, 28, 5500.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-19 06:31:37', '2026-10-19 06:31:37'),
(39, 29, 5500.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-19 06:45:38', '2026-10-19 06:45:38'),
(40, 30, 5500.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-19 06:46:17', '2026-10-19 06:46:17'),
(41, 31, 5500.00, 'Cash', 'Paid', NULL, '2026-10-19 11:47:37', '2026-10-19 11:47:37', NULL, NULL, NULL, '2026-10-19 06:46:57', '2026-10-19 06:47:37'),
(43, 32, 50000.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-19 07:32:17', '2026-10-19 07:32:17'),
(44, 33, 1000.00, 'Cash', 'Paid', NULL, '2026-10-19 13:00:53', '2026-10-19 13:00:53', NULL, NULL, NULL, '2026-10-19 07:59:23', '2026-10-19 08:00:53'),
(45, 34, 1500.00, 'Cash', 'Paid', NULL, '2026-10-19 13:01:23', '2026-10-19 13:01:23', NULL, NULL, NULL, '2026-10-19 07:59:50', '2026-10-19 08:01:23'),
(46, 35, 2500.00, 'Cash', 'Paid', NULL, '2026-10-19 13:01:42', '2026-10-19 13:01:42', NULL, NULL, NULL, '2026-10-19 08:00:10', '2026-10-19 08:01:42'),
(50, 36, 5500.00, 'Cash', 'Paid', NULL, '2026-10-19 13:16:27', '2026-10-19 13:16:27', NULL, NULL, NULL, '2026-10-19 08:15:59', '2026-10-19 08:16:27'),
(52, 37, 5500.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-19 08:17:22', '2026-10-19 08:17:22'),
(53, 38, 5500.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-19 08:17:58', '2026-10-19 08:17:58'),
(54, 39, 5500.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-19 08:18:18', '2026-10-19 08:18:18'),
(55, 40, 5500.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-19 09:44:27', '2026-10-19 09:44:27'),
(57, 41, 2000.00, 'Cash', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-19 10:15:48', '2026-10-19 10:15:48');

-- --------------------------------------------------------

--
-- Table structure for table `payment_history`
--

CREATE TABLE `payment_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `payment_id` int(10) UNSIGNED NOT NULL,
  `event` enum('created','provider_confirmed','provider_resubmitted','admin_verified','admin_rejected','booking_cancelled_by_user','booking_rejected_by_provider','booking_cancelled_by_provider') NOT NULL,
  `from_status` varchar(30) DEFAULT NULL,
  `to_status` varchar(30) NOT NULL,
  `actor_role` enum('system','user','provider','admin') NOT NULL DEFAULT 'system',
  `actor_user_id` int(10) UNSIGNED DEFAULT NULL,
  `note` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_history`
--

INSERT INTO `payment_history` (`id`, `payment_id`, `event`, `from_status`, `to_status`, `actor_role`, `actor_user_id`, `note`, `created_at`) VALUES
(1, 1, 'created', NULL, 'Pending', 'system', NULL, 'Existing record at migration', '2026-10-13 11:04:21'),
(2, 2, 'created', NULL, 'Pending', 'system', NULL, 'Existing record at migration', '2026-10-13 11:04:21'),
(3, 3, 'created', NULL, 'Pending', 'system', NULL, 'Existing record at migration', '2026-10-13 11:04:21'),
(4, 2, 'provider_confirmed', 'Pending', 'Verification Requested', 'provider', 2, NULL, '2026-10-13 11:05:54'),
(5, 1, 'provider_confirmed', 'Pending', 'Verification Requested', 'provider', 2, NULL, '2026-10-13 11:05:57'),
(6, 1, 'admin_verified', 'Verification Requested', 'Paid', 'admin', 1, NULL, '2026-10-13 11:06:52'),
(7, 2, 'admin_verified', 'Verification Requested', 'Paid', 'admin', 1, NULL, '2026-10-13 11:06:55'),
(8, 4, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-13 11:08:45'),
(9, 6, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-13 11:10:18'),
(10, 6, 'provider_confirmed', 'Pending', 'Verification Requested', 'provider', 2, NULL, '2026-10-13 11:12:01'),
(11, 6, 'admin_rejected', 'Verification Requested', 'Rejected', 'admin', 1, 'user said it was not paid', '2026-10-13 11:13:55'),
(12, 6, 'provider_resubmitted', 'Rejected', 'Verification Requested', 'provider', 2, NULL, '2026-10-13 11:14:56'),
(13, 8, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-14 06:04:43'),
(14, 9, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-14 06:50:16'),
(15, 10, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-14 11:47:47'),
(16, 9, 'provider_confirmed', 'Pending', 'Verification Requested', 'provider', 2, NULL, '2026-10-14 11:50:36'),
(17, 13, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-15 06:34:26'),
(18, 14, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-15 06:38:35'),
(19, 14, 'provider_confirmed', 'Pending', 'Paid', 'provider', 6, NULL, '2026-10-15 06:39:36'),
(20, 16, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-15 06:44:41'),
(21, 17, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-15 07:07:04'),
(22, 18, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-15 07:07:18'),
(23, 19, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-15 07:07:55'),
(24, 18, 'provider_confirmed', 'Pending', 'Paid', 'provider', 5, NULL, '2026-10-15 07:10:28'),
(25, 21, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-15 07:27:02'),
(26, 22, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-15 07:29:24'),
(27, 23, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-15 07:40:07'),
(28, 24, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-15 07:40:22'),
(29, 24, 'provider_confirmed', 'Pending', 'Paid', 'provider', 2, NULL, '2026-10-15 08:11:15'),
(30, 26, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-15 09:48:58'),
(31, 27, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-15 09:49:24'),
(32, 28, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-15 09:57:37'),
(33, 28, 'provider_confirmed', 'Pending', 'Paid', 'provider', 2, NULL, '2026-10-15 10:25:40'),
(34, 30, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-15 11:03:38'),
(35, 31, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-15 11:04:50'),
(36, 32, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-15 11:38:20'),
(37, 32, 'provider_confirmed', 'Pending', 'Paid', 'provider', 2, NULL, '2026-10-15 11:41:11'),
(38, 34, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-19 05:55:58'),
(39, 34, 'provider_confirmed', 'Pending', 'Paid', 'provider', 2, NULL, '2026-10-19 05:58:08'),
(40, 4, 'provider_confirmed', 'Pending', 'Paid', 'provider', 2, NULL, '2026-10-19 05:58:21'),
(41, 10, 'provider_confirmed', 'Pending', 'Paid', 'provider', 2, NULL, '2026-10-19 05:58:23'),
(42, 36, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-19 06:00:05'),
(43, 36, 'booking_cancelled_by_user', 'Pending', 'Pending', 'user', 13, NULL, '2026-10-19 06:00:12'),
(44, 37, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-19 06:01:23'),
(45, 37, 'booking_rejected_by_provider', 'Pending', 'Pending', 'provider', 2, NULL, '2026-10-19 06:01:34'),
(46, 38, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-19 06:31:37'),
(47, 38, 'booking_cancelled_by_user', 'Pending', 'Pending', 'user', 3, NULL, '2026-10-19 06:31:52'),
(48, 39, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-19 06:45:38'),
(49, 39, 'booking_cancelled_by_user', 'Pending', 'Pending', 'user', 3, NULL, '2026-10-19 06:45:46'),
(50, 40, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-19 06:46:17'),
(51, 40, 'booking_rejected_by_provider', 'Pending', 'Pending', 'provider', 2, NULL, '2026-10-19 06:46:36'),
(52, 41, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-19 06:46:57'),
(53, 41, 'provider_confirmed', 'Pending', 'Paid', 'provider', 2, NULL, '2026-10-19 06:47:37'),
(54, 43, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-19 07:32:17'),
(55, 44, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-19 07:59:23'),
(56, 45, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-19 07:59:50'),
(57, 46, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-19 08:00:10'),
(58, 44, 'provider_confirmed', 'Pending', 'Paid', 'provider', 14, NULL, '2026-10-19 08:00:53'),
(59, 45, 'provider_confirmed', 'Pending', 'Paid', 'provider', 15, NULL, '2026-10-19 08:01:23'),
(60, 46, 'provider_confirmed', 'Pending', 'Paid', 'provider', 16, NULL, '2026-10-19 08:01:42'),
(61, 50, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-19 08:15:59'),
(62, 50, 'provider_confirmed', 'Pending', 'Paid', 'provider', 2, NULL, '2026-10-19 08:16:27'),
(63, 52, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-19 08:17:22'),
(64, 52, 'booking_cancelled_by_user', 'Pending', 'Pending', 'user', 3, NULL, '2026-10-19 08:17:31'),
(65, 53, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-19 08:17:58'),
(66, 53, 'booking_rejected_by_provider', 'Pending', 'Pending', 'provider', 2, NULL, '2026-10-19 08:18:04'),
(67, 54, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-19 08:18:18'),
(68, 54, 'booking_rejected_by_provider', 'Pending', 'Pending', 'provider', 2, NULL, '2026-10-19 08:18:34'),
(69, 55, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-19 09:44:27'),
(70, 57, 'created', NULL, 'Pending', 'system', NULL, NULL, '2026-10-19 10:15:48');

-- --------------------------------------------------------

--
-- Table structure for table `providers`
--

CREATE TABLE `providers` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `location` varchar(190) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `verification_status` enum('Not Submitted','Pending','Approved','Rejected') NOT NULL DEFAULT 'Not Submitted',
  `verification_note` varchar(500) DEFAULT NULL,
  `account_status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `providers`
--

INSERT INTO `providers` (`id`, `user_id`, `name`, `email`, `phone`, `location`, `photo`, `bio`, `skills`, `verification_status`, `verification_note`, `account_status`, `created_at`) VALUES
(1, 2, 'Ahmad Hassan', 'provider@gmail.com', '03250794101', 'sahiwal', 'uploads/providers/d194dbe11f3317bc74f0ee7b9c7087bc.jpg', 'A developer biodata is a short, structured document that lists your personal details, skills, education, and work experience, often used for job applications and professional profiles.', 'deveolper', 'Approved', NULL, 'Active', '2026-10-13 06:23:07'),
(2, 5, 'arslan', 'provider2@gmail.com', '03245893453', 'lahore', 'uploads/providers/939e017744d69b33209f68d022e17887.webp', 'just  a demo id for teting.', 'care , assitance', 'Approved', NULL, 'Active', '2026-10-14 07:45:52'),
(3, 6, 'hamza', 'provider3@gmail.com', '03335647823', 'okara', NULL, 'A nursing bio is a professional summary that highlights your education, experience, skills, and personal qualities, helping you present yourself effectively to employers, colleagues, or patients.', 'nursing', 'Approved', NULL, 'Active', '2026-10-14 08:00:06'),
(4, 14, 'islam', 'islam@gmail.com', NULL, NULL, NULL, NULL, NULL, 'Approved', NULL, 'Active', '2026-10-19 07:04:57'),
(5, 15, 'imran', 'imran@gmail.com', NULL, NULL, NULL, NULL, NULL, 'Approved', NULL, 'Active', '2026-10-19 07:05:28'),
(6, 16, 'salman', 'salman@gmail.com', NULL, NULL, NULL, NULL, NULL, 'Approved', NULL, 'Active', '2026-10-19 07:05:52'),
(7, 17, 'kashif', 'kashif@gmail.com', NULL, NULL, NULL, NULL, NULL, 'Rejected', NULL, 'Active', '2026-10-19 08:03:40'),
(8, 18, 'umer', 'umer@gmail.com', NULL, NULL, NULL, NULL, NULL, 'Pending', NULL, 'Inactive', '2026-10-19 08:09:51');

-- --------------------------------------------------------

--
-- Table structure for table `provider_categories`
--

CREATE TABLE `provider_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `provider_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `provider_categories`
--

INSERT INTO `provider_categories` (`id`, `provider_id`, `category_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(4, 2, 3),
(5, 3, 1),
(8, 4, 2),
(10, 5, 1),
(9, 6, 3),
(11, 7, 2);

-- --------------------------------------------------------

--
-- Table structure for table `provider_documents`
--

CREATE TABLE `provider_documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `provider_id` int(10) UNSIGNED NOT NULL,
  `document_type` varchar(80) NOT NULL,
  `document_path` varchar(255) NOT NULL,
  `verification_status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `provider_documents`
--

INSERT INTO `provider_documents` (`id`, `provider_id`, `document_type`, `document_path`, `verification_status`, `uploaded_at`) VALUES
(1, 1, 'Experience Certificate', 'uploads/documents/00bc80b0b72503e8b670e6809091de04.jpg', 'Approved', '2026-10-15 11:10:06'),
(2, 1, 'Other', 'uploads/documents/640813699ed9f4a37d02e7860195af02.png', 'Approved', '2026-10-15 11:10:14');

-- --------------------------------------------------------

--
-- Table structure for table `provider_offerings`
--

CREATE TABLE `provider_offerings` (
  `id` int(10) UNSIGNED NOT NULL,
  `provider_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `availability` varchar(255) DEFAULT NULL,
  `experience` varchar(120) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `provider_offerings`
--

INSERT INTO `provider_offerings` (`id`, `provider_id`, `category_id`, `title`, `description`, `price`, `availability`, `experience`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Post-Surgery Assistance', 'this is first serice form me', 2000.00, 'Mon-Fri, 9 AM - 5 PM', '2 year', '2026-10-13 06:25:06', '2026-10-19 10:14:42'),
(2, 1, 2, 'Household Support', NULL, 50000.00, 'Mon-Fri, 9 AM - 5 PM', '3 year', '2026-10-13 06:25:23', '2026-10-19 10:14:25'),
(3, 1, 1, 'Disability Support Service', 'i ma fantastic in this service.', 3000.00, 'Mon-Friday_Any-Time', '1 year', '2026-10-14 06:49:23', '2026-10-19 10:14:12'),
(4, 2, 3, 'physiotherapy', 'Dr. Yawar Hussain has the following degrees: DPT (Doctor of Physical Therapy), Postgraduate Diploma In Pain Management', 7000.00, 'Mon-Friday_Any-Time', '3 years', '2026-10-14 07:49:22', '2026-10-14 07:49:22'),
(5, 3, 1, 'overnight care', 'Overnight care services provide a range of support to ensure the safety and comfort of individuals, particularly seniors or those with health challenges.', 8000.00, 'Mon-Friday_Any-Time', '8 yeas', '2026-10-14 08:03:11', '2026-10-14 08:03:11'),
(7, 1, 1, 'Fall prevention and safety monitoring', 'Fall prevention and safety monitoring involve identifying fall risks, reducing hazards, and using trained personnel or systems to protect individuals—especially patients, older adults, and workers—from fall-related injuries.', 5500.00, 'mondy-friday', '2 years', '2026-10-19 05:53:43', '2026-10-19 07:44:08'),
(8, 4, 2, 'Companion Service', 'Companionship, conversation, and emotional support for clients.', 1000.00, 'Mon-Fri, 9 AM - 5 PM', '2 year', '2026-10-19 07:15:40', '2026-10-19 07:15:40'),
(9, 6, 3, 'Patient Care at Home', 'Assistance with patient comfort, mobility, and daily care needs.', 2500.00, 'Mon-Fri, 9 AM - 5 PM', '3 year', '2026-10-19 07:18:01', '2026-10-19 07:18:01'),
(10, 5, 1, 'Basic Nursing Care', 'Basic nursing assistance and routine patient care at home.', 1500.00, 'Mon-Fri, 9 AM - 5 PM', '4 years', '2026-10-19 07:19:04', '2026-10-19 07:19:04'),
(11, 7, 2, 'Dily Activity Help', NULL, 1700.00, NULL, '4 years', '2026-10-19 08:05:10', '2026-10-19 08:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(10) UNSIGNED NOT NULL,
  `booking_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `provider_id` int(10) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `booking_id`, `user_id`, `provider_id`, `rating`, `review`, `created_at`) VALUES
(1, 2, 3, 1, 3, 'average', '2026-10-13 07:37:22'),
(2, 1, 3, 1, 5, 'good', '2026-10-13 07:37:29'),
(3, 25, 13, 1, 3, 'trusted person and more caring services.', '2026-10-19 05:59:02'),
(4, 10, 3, 3, 4, 'nice service', '2026-10-19 07:02:56'),
(5, 13, 3, 2, 2, 'average servie', '2026-10-19 07:03:10'),
(6, 31, 3, 1, 5, 'excellent', '2026-10-19 07:03:29'),
(7, 35, 3, 6, 3, 'woh good services', '2026-10-19 08:02:02'),
(8, 34, 3, 5, 3, 'descipline services', '2026-10-19 08:02:23'),
(9, 33, 3, 4, 2, 'average but not fantastic services', '2026-10-19 08:02:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `is_handicapped` tinyint(1) DEFAULT NULL,
  `accessibility_category` enum('disability','age_50_plus','none') DEFAULT NULL,
  `role` enum('user','provider','admin') NOT NULL DEFAULT 'user',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `date_of_birth`, `is_handicapped`, `accessibility_category`, `role`, `status`, `created_at`) VALUES
(1, 'AASRA Admin', 'admin@gmail.com', '$2y$10$uW4fvksMr6w6fEqhL7yowO.G5l9Lls4yaELowMwAHIXsNQFgac2ju', NULL, NULL, NULL, NULL, NULL, 'admin', 'Active', '2026-10-13 06:21:26'),
(2, 'Ahmad Hassan', 'provider@gmail.com', '$2y$10$YdPGknwKTIkTutTQCPee/ewAvGjLXLwaxZKEcE9vcis9w0rTI3N2K', '03250794101', 'sahiwal', NULL, NULL, NULL, 'provider', 'Active', '2026-10-13 06:23:07'),
(3, 'Ali Khan', 'user@gmail.com', '$2y$10$023H02mjph4Uao2XLyEIleIo3kYlPyesU6rLm6ALP5Tx/qxL7flTy', '03250794101', 'bahria town phase 8', NULL, NULL, NULL, 'user', 'Active', '2026-10-13 06:25:44'),
(5, 'arslan', 'provider2@gmail.com', '$2y$10$VGACRhpoWX4NxnUfRiVLt.18uAdbUCX9wPKzwUOsg9YZm6F7nW04m', '03245893453', 'lahore', NULL, NULL, NULL, 'provider', 'Active', '2026-10-14 07:45:52'),
(6, 'hamza', 'provider3@gmail.com', '$2y$10$eSzWXaENh.u0sSe6y77h8O6OBf./JqMMSjVES65DYl3RUuUUTvUPC', '03335647823', 'okara', NULL, NULL, NULL, 'provider', 'Active', '2026-10-14 08:00:06'),
(7, 'arsal', 'arsal@gmail.com', '$2y$10$JF1JVXOtaLxRGL/7WNwneuH60vYK4N1s7W/qe/DeVy6nwATlpCUgq', '03250794101', '100A/WM', NULL, NULL, 'age_50_plus', 'user', 'Active', '2026-10-15 09:45:57'),
(13, 'saqib', 'newuser@gmail.com', '$2y$10$jT57i2A2TOePRyaEomtLu.18S2qJdvJzOx8wlgDz3oGhXKKZGXzFO', NULL, NULL, '2026-10-08', 1, NULL, 'user', 'Active', '2026-10-19 05:49:55'),
(14, 'islam', 'islam@gmail.com', '$2y$10$LXv8ws0NqYlszDU2rYRfh.Pym4JfFEvmUjes6pkrufs3ZNDztbjDW', NULL, NULL, NULL, NULL, NULL, 'provider', 'Active', '2026-10-19 07:04:57'),
(15, 'imran', 'imran@gmail.com', '$2y$10$PBjIOU6M/YhxJWtBc66zp.WVNNZAZnGgETHehDlol.2QqeQSJV23y', NULL, NULL, NULL, NULL, NULL, 'provider', 'Active', '2026-10-19 07:05:28'),
(16, 'salman', 'salman@gmail.com', '$2y$10$X9c5Dz9uIoy7ozPONfqsDu6JFU9G0oyM212rta1Nd2GLckENoQum.', NULL, NULL, NULL, NULL, NULL, 'provider', 'Active', '2026-10-19 07:05:52'),
(17, 'kashif', 'kashif@gmail.com', '$2y$10$3jngAe54DaGdBGT4bsWty.sr0jnQuAh2TZL.ZKKAmUB0VV0lmtAH.', NULL, NULL, NULL, NULL, NULL, 'provider', 'Active', '2026-10-19 08:03:40'),
(18, 'umer', 'umer@gmail.com', '$2y$10$j/8cK7XltLqmJUfH6S52VewK/h9dORCrQ/NJ2xSbMzUl6xkK18h0W', NULL, NULL, NULL, NULL, NULL, 'provider', 'Inactive', '2026-10-19 08:09:51');

-- --------------------------------------------------------

--
-- Table structure for table `user_documents`
--

CREATE TABLE `user_documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `document_path` varchar(255) NOT NULL,
  `original_name` varchar(150) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_documents`
--

INSERT INTO `user_documents` (`id`, `user_id`, `document_path`, `original_name`, `uploaded_at`) VALUES
(2, 13, 'uploads/user_documents/d2b8cf0b3f1cf2e8330f29fafa0bb8fb.jpg', '01 gettyimages-1129788053_resized', '2026-10-19 05:49:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `provider_id` (`provider_id`,`status`),
  ADD KEY `user_id` (`user_id`,`status`),
  ADD KEY `idx_bookings_duplicate_check` (`user_id`,`provider_id`,`offering_id`,`booking_date`,`booking_time`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `provider_id` (`provider_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `status` (`status`,`created_at`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_fav` (`user_id`,`provider_id`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_notifications_event_key` (`event_key`),
  ADD KEY `user_id` (`user_id`,`is_read`),
  ADD KEY `provider_id` (`provider_id`,`is_read`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_id` (`booking_id`),
  ADD KEY `idx_payments_status` (`payment_status`),
  ADD KEY `fk_payments_verified_by` (`verified_by`);

--
-- Indexes for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payment_history_payment` (`payment_id`,`created_at`),
  ADD KEY `fk_payment_history_actor` (`actor_user_id`);

--
-- Indexes for table `providers`
--
ALTER TABLE `providers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `verification_status` (`verification_status`,`account_status`);

--
-- Indexes for table `provider_categories`
--
ALTER TABLE `provider_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pc` (`provider_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `provider_documents`
--
ALTER TABLE `provider_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Indexes for table `provider_offerings`
--
ALTER TABLE `provider_offerings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_offering_title` (`provider_id`,`category_id`,`title`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_id` (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role` (`role`,`status`);

--
-- Indexes for table `user_documents`
--
ALTER TABLE `user_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_documents_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `providers`
--
ALTER TABLE `providers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `provider_categories`
--
ALTER TABLE `provider_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `provider_documents`
--
ALTER TABLE `provider_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `provider_offerings`
--
ALTER TABLE `provider_offerings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `user_documents`
--
ALTER TABLE `user_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`),
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaints_ibfk_3` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD CONSTRAINT `fk_payment_history_actor` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_payment_history_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `providers`
--
ALTER TABLE `providers`
  ADD CONSTRAINT `providers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `provider_categories`
--
ALTER TABLE `provider_categories`
  ADD CONSTRAINT `provider_categories_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `provider_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `provider_documents`
--
ALTER TABLE `provider_documents`
  ADD CONSTRAINT `provider_documents_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `provider_offerings`
--
ALTER TABLE `provider_offerings`
  ADD CONSTRAINT `fk_offering_listing` FOREIGN KEY (`provider_id`,`category_id`) REFERENCES `provider_categories` (`provider_id`, `category_id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ratings_ibfk_3` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`);

--
-- Constraints for table `user_documents`
--
ALTER TABLE `user_documents`
  ADD CONSTRAINT `fk_user_documents_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
