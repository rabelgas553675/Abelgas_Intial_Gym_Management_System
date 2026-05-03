-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 20, 2026 at 07:35 AM
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
-- Database: `gym_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'NULL for staff/admin QR scans',
  `time_in` datetime NOT NULL,
  `time_out` datetime DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `scanned_by` varchar(64) DEFAULT NULL COMMENT 'QR token of the person who scanned (staff/admin)',
  `entry_method` enum('qr_scan','manual') NOT NULL DEFAULT 'qr_scan' COMMENT 'How the attendance was recorded',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `member_id` bigint(20) UNSIGNED DEFAULT NULL,
  `staff_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `duration_minutes` int(10) UNSIGNED DEFAULT NULL,
  `scanned_by` varchar(64) DEFAULT NULL,
  `entry_method` enum('qr_scan','manual') NOT NULL DEFAULT 'qr_scan',
  `status` varchar(255) NOT NULL DEFAULT 'Present',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id`, `member_id`, `staff_user_id`, `date`, `time_in`, `time_out`, `duration_minutes`, `scanned_by`, `entry_method`, `status`, `created_at`, `updated_at`) VALUES
(4, 1, NULL, '2026-04-14', '17:59:01', NULL, NULL, NULL, 'qr_scan', 'Present', '2026-04-14 09:59:01', '2026-04-14 09:59:01'),
(14, 1, NULL, '2026-04-16', '00:02:28', '00:14:56', 0, NULL, 'qr_scan', 'Present', '2026-04-15 16:02:28', '2026-04-15 16:14:56'),
(18, 1, NULL, '2026-04-16', '00:23:51', '00:43:28', 1, NULL, 'qr_scan', 'Present', '2026-04-15 16:23:52', '2026-04-15 16:43:28'),
(30, 1, NULL, '2026-04-16', '01:24:17', '01:24:35', 1, NULL, 'qr_scan', 'Present', '2026-04-15 17:24:17', '2026-04-15 17:24:35'),
(32, NULL, 3, '2026-04-16', '01:43:17', '01:54:04', 1, NULL, 'manual', 'Present', '2026-04-15 17:43:17', '2026-04-15 17:54:04'),
(33, NULL, 2, '2026-04-16', '01:43:25', '01:54:01', 1, NULL, 'manual', 'Present', '2026-04-15 17:43:25', '2026-04-15 17:54:01'),
(34, NULL, 4, '2026-04-16', '01:54:24', '03:43:15', 1, NULL, 'manual', 'Present', '2026-04-15 17:54:24', '2026-04-15 19:43:15'),
(36, NULL, 4, '2026-04-16', '03:43:06', '03:43:13', 1, NULL, 'manual', 'Present', '2026-04-15 19:43:06', '2026-04-15 19:43:13'),
(37, 1, NULL, '2026-04-16', '12:14:14', '12:14:20', 1, NULL, 'manual', 'Present', '2026-04-16 04:14:14', '2026-04-16 04:14:20'),
(38, NULL, 4, '2026-04-20', '12:47:22', '12:47:26', 1, NULL, 'manual', 'Present', '2026-04-20 04:47:22', '2026-04-20 04:47:26');

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
-- Table structure for table `instructor_fees`
--

CREATE TABLE `instructor_fees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `instructor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `membership_type` varchar(255) DEFAULT NULL,
  `fitness_plan` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('Active','Expired','Pending') NOT NULL DEFAULT 'Active',
  `role` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `qr_id` varchar(255) DEFAULT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL,
  `qr_token` varchar(64) DEFAULT NULL COMMENT 'Unique token encoded in member QR code',
  `qr_code` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `user_id`, `instructor_id`, `name`, `first_name`, `last_name`, `email`, `phone`, `gender`, `birthdate`, `address`, `membership_type`, `fitness_plan`, `start_date`, `end_date`, `fee`, `status`, `role`, `photo`, `qr_id`, `qr_code_path`, `qr_token`, `qr_code`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'richmon', 'richmon', 'abelgas', 'richmon@gym.com', '09485452265', 'Male', '2006-02-10', 'davao city', 'Monthly', 'Endurance', '2026-04-16', '2026-05-16', 800.00, 'Active', NULL, NULL, 'IFG-MEM-000001', 'qrcodes/IFG-MEM-000001.svg', 'MBR-73348BE832AA62F1522036AA713F260B', NULL, '2026-04-13 00:40:40', '2026-04-16 04:11:05');

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
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_04_04_042616_create_members_table', 1),
(5, '2026_04_04_130952_create_payments_table', 1),
(6, '2026_04_13_023849_create_workout_plans_table', 1),
(7, '2026_04_14_035714_add_qr_attendance_columns', 2),
(8, '2026_04_14_092454_create_attendances_table', 3),
(9, '2026_04_14_144830_add_qr_fields_to_users_table', 4),
(10, '2026_04_14_155433_add_qr_columns_to_members_table', 5),
(11, '2026_04_14_163440_add_qr_path_to_user_qr_tokens', 6),
(12, '2026_04_14_174415_add_staff_user_id_to_attendances_table', 7),
(13, '2026_04_14_183622_add_method_notes_to_payments', 8),
(14, '2026_04_16_030142_add_role_to_members_table', 9),
(15, '2026_04_16_114354_create_instructor_fees_table', 10);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `member_id` bigint(20) UNSIGNED NOT NULL,
  `instructor_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Set when this payment is a coach fee for an instructor',
  `platform_fee` decimal(10,2) DEFAULT NULL COMMENT 'Platform cut taken from total amount (admin earnings)',
  `payment_type` enum('gym_fee','coach_fee') NOT NULL DEFAULT 'gym_fee' COMMENT 'gym_fee = normal membership, coach_fee = instructor subscription',
  `processed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `receipt_number` varchar(255) NOT NULL,
  `fitness_plan` varchar(255) DEFAULT NULL,
  `membership_type` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `method` varchar(255) NOT NULL DEFAULT 'Cash',
  `status` enum('Paid','Pending','Expired') NOT NULL DEFAULT 'Paid',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `member_id`, `instructor_id`, `platform_fee`, `payment_type`, `processed_by`, `receipt_number`, `fitness_plan`, `membership_type`, `amount`, `payment_date`, `method`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NULL, 'gym_fee', NULL, 'RCP-69DCAD910310F', 'Calisthenics', 'Monthly', 1100.00, '2026-04-13', 'Cash', 'Paid', NULL, '2026-04-13 00:47:13', '2026-04-13 00:47:13'),
(2, 1, NULL, NULL, 'gym_fee', NULL, 'RCP-69DE0DDB09645', 'Calisthenics', 'Monthly', 1100.00, '2026-04-14', 'Cash', 'Paid', NULL, '2026-04-14 01:50:19', '2026-04-14 01:50:19'),
(3, 1, NULL, NULL, 'gym_fee', NULL, 'RCP-69DE0E0926BA8', 'Calisthenics', 'Monthly', 1100.00, '2026-04-14', 'Cash', 'Paid', NULL, '2026-04-14 01:51:05', '2026-04-14 01:51:05'),
(4, 1, NULL, NULL, 'gym_fee', NULL, 'RCP-69DE991FC96FD', 'Powerlifting', 'Monthly', 1100.00, '2026-04-14', 'Cash', 'Paid', 'Gym membership fee', '2026-04-14 11:44:31', '2026-04-14 11:44:31'),
(5, 1, 2, NULL, 'coach_fee', NULL, 'RCP-69DE991FCAE70', 'Powerlifting', 'Monthly', 300.00, '2026-04-14', 'Cash', 'Paid', 'Coach subscription fee for MIGS RAMOS', '2026-04-14 11:44:31', '2026-04-14 11:44:31'),
(6, 1, NULL, NULL, 'gym_fee', NULL, 'RCP-69DE99F132986', 'Powerlifting', 'Monthly', 1100.00, '2026-04-14', 'Cash', 'Paid', 'Gym membership fee', '2026-04-14 11:48:01', '2026-04-14 11:48:01'),
(7, 1, 2, NULL, 'coach_fee', NULL, 'RCP-69DE99F133C4F', 'Powerlifting', 'Monthly', 300.00, '2026-04-14', 'Cash', 'Paid', 'Coach subscription fee for MIGS RAMOS', '2026-04-14 11:48:01', '2026-04-14 11:48:01'),
(8, 1, NULL, NULL, 'gym_fee', NULL, 'RCP-69DE9A295BEEB', 'Powerlifting', 'Monthly', 1100.00, '2026-04-14', 'Cash', 'Paid', 'Gym membership fee', '2026-04-14 11:48:57', '2026-04-14 11:48:57'),
(9, 1, 2, NULL, 'coach_fee', NULL, 'RCP-69DE9A295CCE4', 'Powerlifting', 'Monthly', 300.00, '2026-04-14', 'Cash', 'Paid', 'Coach subscription fee for MIGS RAMOS', '2026-04-14 11:48:57', '2026-04-14 11:48:57'),
(10, 1, NULL, NULL, 'gym_fee', NULL, 'RCP-69DF5734CD611', 'Powerlifting', 'Monthly', 1100.00, '2026-04-15', 'Cash', 'Paid', 'Gym membership fee', '2026-04-15 09:15:32', '2026-04-15 09:15:32'),
(11, 1, 2, NULL, 'coach_fee', NULL, 'RCP-69DF5734CEB92', 'Powerlifting', 'Monthly', 300.00, '2026-04-15', 'Cash', 'Paid', 'Coach subscription fee for MIGS RAMOS', '2026-04-15 09:15:32', '2026-04-15 09:15:32'),
(12, 1, NULL, NULL, 'gym_fee', NULL, 'RCP-69DFA421F0DE5', 'Powerlifting', 'Monthly', 1100.00, '2026-04-15', 'Cash', 'Paid', 'Gym membership fee', '2026-04-15 14:43:45', '2026-04-15 14:43:45'),
(13, 1, 2, NULL, 'coach_fee', NULL, 'RCP-69DFA421F1EB0', 'Powerlifting', 'Monthly', 300.00, '2026-04-15', 'Cash', 'Paid', 'Coach subscription fee for MIGS RAMOS', '2026-04-15 14:43:45', '2026-04-15 14:43:45'),
(14, 1, NULL, NULL, 'gym_fee', NULL, 'RCP-69DFA6950292E', 'Powerlifting', 'Monthly', 1100.00, '2026-04-15', 'Cash', 'Paid', 'Gym membership fee', '2026-04-15 14:54:13', '2026-04-15 14:54:13'),
(15, 1, 2, NULL, 'coach_fee', NULL, 'RCP-69DFA69503974', 'Powerlifting', 'Monthly', 300.00, '2026-04-15', 'Cash', 'Paid', 'Coach subscription fee for MIGS RAMOS', '2026-04-15 14:54:13', '2026-04-15 14:54:13'),
(16, 1, NULL, NULL, 'gym_fee', NULL, 'RCP-69DFA9F761AF1', 'Powerlifting', 'Monthly', 1100.00, '2026-04-15', 'Cash', 'Paid', 'Gym membership fee', '2026-04-15 15:08:39', '2026-04-15 15:08:39'),
(17, 1, 2, NULL, 'coach_fee', NULL, 'RCP-69DFA9F762931', 'Powerlifting', 'Monthly', 300.00, '2026-04-15', 'Cash', 'Paid', 'Coach subscription fee for MIGS RAMOS', '2026-04-15 15:08:39', '2026-04-15 15:08:39'),
(18, 1, NULL, NULL, 'gym_fee', NULL, 'RCP-69DFAA63506DE', 'Powerlifting', 'Monthly', 1100.00, '2026-04-15', 'Cash', 'Paid', 'Gym membership fee', '2026-04-15 15:10:27', '2026-04-15 15:10:27'),
(19, 1, 2, NULL, 'coach_fee', NULL, 'RCP-69DFAA6351963', 'Powerlifting', 'Monthly', 300.00, '2026-04-15', 'Cash', 'Paid', 'Coach subscription fee for MIGS RAMOS', '2026-04-15 15:10:27', '2026-04-15 15:10:27'),
(20, 1, NULL, NULL, 'gym_fee', NULL, 'RCP-69DFAA870757E', 'Powerlifting', 'Monthly', 1100.00, '2026-04-15', 'Cash', 'Paid', 'Gym membership fee', '2026-04-15 15:11:03', '2026-04-15 15:11:03'),
(21, 1, 2, NULL, 'coach_fee', NULL, 'RCP-69DFAA870829D', 'Powerlifting', 'Monthly', 300.00, '2026-04-15', 'Cash', 'Paid', 'Coach subscription fee for MIGS RAMOS', '2026-04-15 15:11:03', '2026-04-15 15:11:03'),
(22, 1, NULL, NULL, 'gym_fee', NULL, 'RCP-69DFAD6ABC506', 'Calisthenics', 'Monthly', 1100.00, '2026-04-15', 'Cash', 'Paid', 'Gym membership fee', '2026-04-15 15:23:22', '2026-04-15 15:23:22'),
(23, 1, 2, NULL, 'coach_fee', NULL, 'RCP-69DFAD6ABD693', 'Calisthenics', 'Monthly', 300.00, '2026-04-15', 'Cash', 'Paid', 'Coach subscription fee for MIGS RAMOS', '2026-04-15 15:23:22', '2026-04-15 15:23:22'),
(24, 1, NULL, NULL, 'gym_fee', NULL, 'RCP-69DFAE8E6057D', 'Calisthenics', 'Monthly', 1100.00, '2026-04-15', 'Cash', 'Paid', 'Gym membership fee', '2026-04-15 15:28:14', '2026-04-15 15:28:14'),
(25, 1, 2, NULL, 'coach_fee', NULL, 'RCP-69DFAE8E6177B', 'Calisthenics', 'Monthly', 300.00, '2026-04-15', 'Cash', 'Paid', 'Coach subscription fee for MIGS RAMOS', '2026-04-15 15:28:14', '2026-04-15 15:28:14'),
(26, 1, NULL, NULL, 'gym_fee', NULL, 'RCP-69DFAEA652897', 'Calisthenics', 'Monthly', 1100.00, '2026-04-15', 'Cash', 'Paid', 'Gym membership fee', '2026-04-15 15:28:38', '2026-04-15 15:28:38'),
(27, 1, 2, NULL, 'coach_fee', NULL, 'RCP-69DFAEA65370E', 'Calisthenics', 'Monthly', 300.00, '2026-04-15', 'Cash', 'Paid', 'Coach subscription fee for MIGS RAMOS', '2026-04-15 15:28:38', '2026-04-15 15:28:38'),
(28, 1, NULL, NULL, 'gym_fee', NULL, 'RCP-69DFB080999C6', 'Calisthenics', 'Monthly', 1100.00, '2026-04-15', 'Cash', 'Paid', 'Gym membership fee', '2026-04-15 15:36:32', '2026-04-15 15:36:32'),
(29, 1, 2, NULL, 'coach_fee', NULL, 'RCP-69DFB0809A87A', 'Calisthenics', 'Monthly', 300.00, '2026-04-15', 'Cash', 'Paid', 'Coach subscription fee for MIGS RAMOS', '2026-04-15 15:36:32', '2026-04-15 15:36:32'),
(31, 1, 2, NULL, 'coach_fee', NULL, 'RCP-69DFD9AA06EF8', 'Endurance', 'Monthly', 300.00, '2026-04-16', 'Cash', 'Paid', 'Coach subscription fee for MIGS RAMOS', '2026-04-15 18:32:10', '2026-04-15 18:32:10'),
(32, 1, NULL, NULL, 'gym_fee', NULL, 'RCP-69DFD9C30CA97', 'Endurance', 'Monthly', 800.00, '2026-04-16', 'Cash', 'Paid', 'Gym membership fee', '2026-04-15 18:32:35', '2026-04-15 18:32:35'),
(33, 1, 2, NULL, 'coach_fee', NULL, 'RCP-69DFD9C30DAE2', 'Endurance', 'Monthly', 300.00, '2026-04-16', 'Cash', 'Paid', 'Coach subscription fee for MIGS RAMOS', '2026-04-15 18:32:35', '2026-04-15 18:32:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'member',
  `status` varchar(255) NOT NULL DEFAULT 'Active',
  `qr_id` varchar(255) DEFAULT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `status`, `qr_id`, `qr_code_path`, `phone`, `gender`, `birthdate`, `address`, `photo`, `specialization`, `experience_years`, `last_login_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'richmon', 'richmon@gym.com', NULL, '$2y$12$bgueqic2iE54efxgAsvSKeVVRXGKWh9ChElhSrBH1VpMvNwCkftB.', 'member', 'Active', 'IFG-MEM-000001', 'qrcodes/IFG-MEM-000001.svg', '09485452265', 'Male', '2005-01-01', 'Davao City', 'avatars/VeI5cGPBaOHjhoasTFMRqTFpMd3YeDbJvrB4peGI.jpg', NULL, NULL, '2026-04-13 00:54:08', NULL, '2026-04-13 00:31:40', '2026-04-16 04:12:29'),
(2, 'MIGS RAMOS', 'migs@gym.com', NULL, '$2y$12$zOuROSadUUYakl4b.TN/sep8raJjPOv21SXPOQWD371tV0xYK8Jza', 'instructor', 'Active', 'IFG-INS-000002', 'qrcodes/IFG-INS-000002.svg', '09485452265', NULL, NULL, 'Davao City', 'avatars/EwyH9XngQkNIahp8BHbTpnpFPrtp1WNBl37A8lY5.png', 'Calisthenics', 10, '2026-04-13 00:48:31', NULL, '2026-04-13 00:32:18', '2026-04-14 07:00:52'),
(3, 'Humphrey Tabanao', 'hump@gym.com', NULL, '$2y$12$cePr2HDlrl4kBoAZ5FNfuOKmN2VS61ZQxExzjuHFsi77pOIZ9XI42', 'staff', 'Active', 'IFG-STA-000003', 'qrcodes/IFG-STA-000003.svg', '09485452268', 'Male', '2005-01-01', 'Davao City', 'avatars/9Pp9jRO7iooQVCs5KMcv02EmQ2Z2ewVByTo79nkN.png', NULL, NULL, '2026-04-13 00:43:46', NULL, '2026-04-13 00:33:04', '2026-04-16 04:08:25'),
(4, 'admin', 'admin@gym.com', NULL, '$2y$12$x0jzcBh.OkHzpnomqef3reEuMqnqCcCWxY77JwQH/.J31wgFgTnNK', 'admin', 'Active', 'IFG-ADM-000004', 'qrcodes/IFG-ADM-000004.svg', '09485452265', 'Male', NULL, 'Davao City', 'profiles/KQnvX9uo2E42YDojOZPSAY7wq1u2YiriLZsqojzB.webp', NULL, NULL, '2026-04-13 00:45:15', NULL, '2026-04-13 00:35:55', '2026-04-16 04:06:11');

-- --------------------------------------------------------

--
-- Table structure for table `user_qr_tokens`
--

CREATE TABLE `user_qr_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `role` enum('admin','staff','instructor') NOT NULL,
  `name` varchar(255) NOT NULL,
  `qr_token` varchar(64) NOT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_qr_tokens`
--

INSERT INTO `user_qr_tokens` (`id`, `user_id`, `role`, `name`, `qr_token`, `qr_code_path`, `created_at`) VALUES
(1, 2, 'instructor', 'MIGS RAMOS', 'ISR-E1903CF459F97A525A7F29BDB3432ADF', 'qrcodes/staff/STAFF-2-1776268414.svg', '2026-04-14 15:14:58'),
(2, 3, 'staff', 'Humphrey Tabanao', 'SSR-F1C919113A37772F0762CE9050C38F12', 'qrcodes/staff/STAFF-3-1776268414.svg', '2026-04-14 15:14:58'),
(3, 4, 'admin', 'admin', 'ASR-5E015ED80DD3AFEA8DABE290C2FC2CA3', 'qrcodes/staff/STAFF-4-1776268414.svg', '2026-04-14 15:14:58');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_earnings_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_earnings_summary` (
`role` varchar(10)
,`user_id` decimal(20,0)
,`total_earned` decimal(32,2)
,`this_month` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `workout_plans`
--

CREATE TABLE `workout_plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `instructor_id` bigint(20) UNSIGNED NOT NULL,
  `member_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `scheduled_date` date NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `intensity` enum('Light','Moderate','Intense') NOT NULL DEFAULT 'Moderate',
  `exercises` text DEFAULT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `workout_plans`
--

INSERT INTO `workout_plans` (`id`, `instructor_id`, `member_id`, `title`, `description`, `scheduled_date`, `category`, `intensity`, `exercises`, `is_completed`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'upper body', NULL, '2026-04-13', 'Strength', 'Moderate', '[\"bench press 3 set x 12 reps\"]', 0, '2026-04-13 00:50:31', '2026-04-13 00:50:31'),
(2, 2, 1, 'whole body', NULL, '2026-04-14', 'Strength', 'Intense', '[\"squat\",\"barbell\",\"bicep\",\"push up\",\"pull up\",\"set up\"]', 0, '2026-04-14 02:32:25', '2026-04-14 02:32:25');

-- --------------------------------------------------------

--
-- Structure for view `v_earnings_summary`
--
DROP TABLE IF EXISTS `v_earnings_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_earnings_summary`  AS SELECT 'admin' AS `role`, NULL AS `user_id`, sum(case when `payments`.`payment_type` in ('gym_fee','platform_fee') then `payments`.`amount` else 0 end) AS `total_earned`, sum(case when `payments`.`payment_type` in ('gym_fee','platform_fee') and month(`payments`.`payment_date`) = month(curdate()) and year(`payments`.`payment_date`) = year(curdate()) then `payments`.`amount` else 0 end) AS `this_month` FROM `payments`union all select 'instructor' AS `role`,`payments`.`instructor_id` AS `user_id`,sum(`payments`.`amount`) AS `total_earned`,sum(case when month(`payments`.`payment_date`) = month(curdate()) and year(`payments`.`payment_date`) = year(curdate()) then `payments`.`amount` else 0 end) AS `this_month` from `payments` where `payments`.`payment_type` = 'coach_fee' and `payments`.`instructor_id` is not null group by `payments`.`instructor_id`  ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member_date` (`member_id`,`date`),
  ADD KEY `idx_date` (`date`);

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendances_member_id_foreign` (`member_id`),
  ADD KEY `attendances_staff_user_id_foreign` (`staff_user_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `instructor_fees`
--
ALTER TABLE `instructor_fees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `members_email_unique` (`email`),
  ADD UNIQUE KEY `qr_token` (`qr_token`),
  ADD KEY `members_user_id_foreign` (`user_id`),
  ADD KEY `members_instructor_id_foreign` (`instructor_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payments_receipt_number_unique` (`receipt_number`),
  ADD KEY `payments_member_id_foreign` (`member_id`),
  ADD KEY `payments_processed_by_foreign` (`processed_by`),
  ADD KEY `idx_payment_type` (`payment_type`),
  ADD KEY `idx_instructor_id` (`instructor_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_qr_id_unique` (`qr_id`);

--
-- Indexes for table `user_qr_tokens`
--
ALTER TABLE `user_qr_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qr_token` (`qr_token`),
  ADD UNIQUE KEY `uq_user_id` (`user_id`);

--
-- Indexes for table `workout_plans`
--
ALTER TABLE `workout_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `workout_plans_instructor_id_foreign` (`instructor_id`),
  ADD KEY `workout_plans_member_id_foreign` (`member_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `instructor_fees`
--
ALTER TABLE `instructor_fees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_qr_tokens`
--
ALTER TABLE `user_qr_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `workout_plans`
--
ALTER TABLE `workout_plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendances_staff_user_id_foreign` FOREIGN KEY (`staff_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `members_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `workout_plans`
--
ALTER TABLE `workout_plans`
  ADD CONSTRAINT `workout_plans_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `workout_plans_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
