-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 25, 2026 at 11:24 AM
-- Server version: 11.4.3-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kratipho_db_mango`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `bookings_id` int(11) UNSIGNED NOT NULL,
  `booking_code` varchar(15) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `guest_name` varchar(255) DEFAULT NULL,
  `guest_email` varchar(255) DEFAULT NULL,
  `guest_phone` varchar(50) DEFAULT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `visitor_count` int(11) NOT NULL DEFAULT 1,
  `lunch_request` tinyint(1) DEFAULT 0,
  `price_total` decimal(10,2) DEFAULT 0.00,
  `deposit_amount` decimal(10,2) DEFAULT 0.00,
  `balance_amount` decimal(10,2) DEFAULT 0.00,
  `booking_type` varchar(50) NOT NULL DEFAULT 'private',
  `status` enum('pending','awaiting_payment','confirmed','cancelled') DEFAULT 'pending',
  `is_member_booking` tinyint(1) DEFAULT 0,
  `attachment_path` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_slip` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`bookings_id`, `booking_code`, `member_id`, `guest_name`, `guest_email`, `guest_phone`, `booking_date`, `booking_time`, `visitor_count`, `lunch_request`, `price_total`, `deposit_amount`, `balance_amount`, `booking_type`, `status`, `is_member_booking`, `attachment_path`, `created_at`, `updated_at`, `payment_slip`) VALUES
(106, 'GV202603156080', 22, 'หนึ่งเดียว', 'sb6640248131@lru.ac.th', '0651078576', '2026-03-20', '11:00:00', 10, 1, 6300.00, 1890.00, 4410.00, 'private', 'confirmed', 1, NULL, '2026-03-15 12:04:48', '2026-03-15 14:25:02', 'slip_106_1773559427.png'),
(107, 'GV202604249736', 1, 'หนึ่งเดียว เทียกสีบุญ', 'nanoone342@gmail.com', '0651078576', '2026-04-25', '08:00:00', 1, 1, 4950.00, 1485.00, 3465.00, 'private', 'pending', 1, NULL, '2026-04-24 14:55:17', '2026-04-24 14:55:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `courses_id` int(11) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `course_description` text NOT NULL,
  `image1` varchar(255) DEFAULT NULL,
  `image2` varchar(255) DEFAULT NULL,
  `image3` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`courses_id`, `course_name`, `course_description`, `image1`, `image2`, `image3`, `created_at`, `updated_at`) VALUES
(11, 'สอนทำกล้วยาบ', 'สอนกระบวนการทำทุกขั้นตอน', '6988553fe7c95_476231058_1312361306677902_2878473574434026044_n.jpg', '6988553fe7ca2_477451611_1315659203014779_5459439891038737551_n.jpg', '6988553fe7ca4_596816631_1544823580098339_4542154463419387407_n.jpg', '2026-02-08 09:20:01', '2026-02-18 02:34:42'),
(12, 'สอนชำกิ่งยอดมะม่วง', 'ลงมือทำจริงเพื่อให้เรียนรู้ได้เร็วและนำไปใช้จริงได้', '698855cfbf209_477906730_1316189056295127_3964836888198472688_n.jpg', '511581747_1410433740203991_3550394324443495874_n.jpg', '597852628_1544824313431599_1793304321408035365_n.jpg', '2026-02-08 09:22:25', '2026-02-08 10:56:40'),
(18, 'ห่อมะม่วง', 'สอนทำจริง', '699c7b9881a6a_LINE_ALBUM_สวนลุงเผือก_250612_26.jpg', NULL, NULL, '2026-02-23 16:08:58', '2026-02-23 16:08:58');

-- --------------------------------------------------------

--
-- Table structure for table `course_comments`
--

CREATE TABLE `course_comments` (
  `comment_id` int(11) NOT NULL,
  `courses_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `comment_text` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `name` varchar(30) DEFAULT NULL,
  `guest_identifier` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_comments`
--

INSERT INTO `course_comments` (`comment_id`, `courses_id`, `member_id`, `comment_text`, `created_at`, `name`, `guest_identifier`) VALUES
(25, 12, 1, 'สนุกมากค่ะ', '2026-02-24 14:23:42', 'หนึ่งเดียว เทียกสีบุญ', '7fe325919437ea6b054970d389ae1bef_1771917820'),
(26, 11, NULL, 'กกกกกกกกกกกกกกก', '2026-04-24 14:22:30', 'สุทัตตา', '859c62c49bb38838666a632f028159f7_1777015349');

-- --------------------------------------------------------

--
-- Table structure for table `course_rating`
--

CREATE TABLE `course_rating` (
  `rating_id` int(11) NOT NULL,
  `courses_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `rating` tinyint(4) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `guest_identifier` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_rating`
--

INSERT INTO `course_rating` (`rating_id`, `courses_id`, `member_id`, `rating`, `created_at`, `guest_identifier`) VALUES
(17, 12, 22, 4, '2026-02-17 15:10:46', '4b840862e5d245eb7a5272db493377a9_1771315845'),
(22, 18, NULL, 5, '2026-02-23 23:10:35', 'd8c3d26b4577e49dc76c660282c4ee08_1771863033'),
(23, 12, 1, 5, '2026-02-24 14:23:41', '7fe325919437ea6b054970d389ae1bef_1771917820'),
(24, 11, NULL, 1, '2026-04-24 14:22:29', '859c62c49bb38838666a632f028159f7_1777015349'),
(25, 11, NULL, 5, '2026-04-24 14:24:53', '859c62c49bb38838666a632f028159f7_1777015493');

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `attempted_at` datetime DEFAULT current_timestamp(),
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `success` tinyint(1) NOT NULL,
  `reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_logs`
--

INSERT INTO `login_logs` (`id`, `member_id`, `email`, `attempted_at`, `ip_address`, `user_agent`, `success`, `reason`) VALUES
(1, 1, 'nanoone342@gmail.com', '2025-12-18 15:43:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(2, 13, 'sakunrat2196@gmail.com', '2025-12-18 22:27:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(4, 11, 'sukandasomsiang543@gmail.com', '2025-12-18 22:34:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(5, 1, 'nanoone342@gmail.com', '2025-12-27 20:51:02', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 1, 'success'),
(6, 1, 'nanoone342@gmail.com', '2025-12-29 09:59:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 0, 'wrong_password'),
(7, 1, 'nanoone342@gmail.com', '2025-12-29 09:59:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(8, 1, 'nanoone342@gmail.com', '2025-12-29 15:08:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 0, 'wrong_password'),
(9, 1, 'nanoone342@gmail.com', '2025-12-29 15:08:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(10, 11, 'sukandasomsiang543@gmail.com', '2026-01-03 17:00:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(11, 11, 'sukandasomsiang543@gmail.com', '2026-01-07 12:42:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(12, 1, 'nanoone342@gmail.com', '2026-01-12 13:29:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 0, 'wrong_password'),
(13, 1, 'nanoone342@gmail.com', '2026-01-12 13:29:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 0, 'wrong_password'),
(14, 1, 'nanoone342@gmail.com', '2026-01-12 13:33:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(15, 11, 'sukandasomsiang543@gmail.com', '2026-01-12 13:52:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(16, 11, 'sukandasomsiang543@gmail.com', '2026-01-12 13:52:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(17, 1, 'nanoone342@gmail.com', '2026-01-13 10:19:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 0, 'wrong_password'),
(18, 1, 'nanoone342@gmail.com', '2026-01-13 10:19:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(19, 1, 'nanoone342@gmail.com', '2026-01-13 13:30:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(20, 11, 'sukandasomsiang543@gmail.com', '2026-01-15 09:58:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(21, 1, 'nanoone342@gmail.com', '2026-01-15 11:11:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(22, 1, 'nanoone342@gmail.com', '2026-01-16 15:15:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(23, 11, 'sukandasomsiang543@gmail.com', '2026-01-17 10:43:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(24, 1, 'nanoone342@gmail.com', '2026-01-20 11:03:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 0, 'wrong_password'),
(25, 1, 'nanoone342@gmail.com', '2026-01-20 11:03:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(26, 1, 'nanoone342@gmail.com', '2026-01-23 13:25:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, 'wrong_password'),
(27, 1, 'nanoone342@gmail.com', '2026-01-23 13:25:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(28, 1, 'nanoone342@gmail.com', '2026-01-23 14:34:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(29, 1, 'nanoone342@gmail.com', '2026-01-23 14:56:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(30, 1, 'nanoone342@gmail.com', '2026-01-23 15:27:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, 'disabled'),
(31, 1, 'nanoone342@gmail.com', '2026-01-23 15:27:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, 'disabled'),
(32, 1, 'nanoone342@gmail.com', '2026-01-23 15:28:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(33, 9, 'natdanai021046@gmail.com', '2026-01-23 15:32:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(34, 20, 'nik1915901@gmail.com', '2026-01-23 16:53:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(35, 1, 'nanoone342@gmail.com', '2026-01-23 17:32:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(36, 21, 'sb6740248101@lru.ac.th', '2026-01-23 17:40:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, 'disabled'),
(37, 21, 'sb6740248101@lru.ac.th', '2026-01-23 17:41:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(38, 18, 'tiw@gamil.com', '2026-01-23 17:50:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(39, 17, 'Dan@gmail.com', '2026-01-23 17:55:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(40, 1, 'nanoone342@gmail.com', '2026-01-23 18:40:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(41, 11, 'sukandasomsiang543@gmail.com', '2026-01-23 18:47:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(42, 11, 'sukandasomsiang543@gmail.com', '2026-01-23 20:20:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(43, 1, 'nanoone342@gmail.com', '2026-01-25 18:11:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(44, 11, 'sukandasomsiang543@gmail.com', '2026-01-25 19:26:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(45, 11, 'sukandasomsiang543@gmail.com', '2026-01-26 09:16:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(46, 11, 'sukandasomsiang543@gmail.com', '2026-01-26 20:29:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(47, 1, 'nanoone342@gmail.com', '2026-01-27 00:57:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(48, 22, 'sb6640248131@lru.ac.th', '2026-01-27 01:20:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(49, 11, 'sukandasomsiang543@gmail.com', '2026-01-27 04:28:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, 'success'),
(50, 23, 'kyliesandjenner@gmail.com', '2026-01-28 13:59:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(51, 22, 'sb6640248131@lru.ac.th', '2026-01-28 17:26:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(52, 24, 'sb6640248118@lur.ac.th', '2026-01-28 18:02:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(53, 22, 'sb6640248131@lru.ac.th', '2026-01-28 18:34:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(54, 11, 'sukandasomsiang543@gmail.com', '2026-01-28 20:58:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(55, 22, 'sb6640248131@lru.ac.th', '2026-01-29 09:28:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(56, 25, 'n72520448@gmail.com', '2026-01-29 16:15:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(57, 25, 'n72520448@gmail.com', '2026-01-30 01:20:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(58, 22, 'sb6640248131@lru.ac.th', '2026-01-30 01:25:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(59, 11, 'sukandasomsiang543@gmail.com', '2026-01-30 03:11:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(60, 1, 'nanoone342@gmail.com', '2026-01-30 03:41:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(61, 22, 'sb6640248131@lru.ac.th', '2026-01-30 07:35:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(62, 11, 'sukandasomsiang543@gmail.com', '2026-01-30 12:02:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(63, 11, 'sukandasomsiang543@gmail.com', '2026-01-30 12:56:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, 'wrong_password'),
(64, 11, 'sukandasomsiang543@gmail.com', '2026-01-30 12:57:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, 'wrong_password'),
(65, 11, 'sukandasomsiang543@gmail.com', '2026-01-30 12:58:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, 'wrong_password'),
(66, 11, 'sukandasomsiang543@gmail.com', '2026-01-30 12:58:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(67, 11, 'sukandasomsiang543@gmail.com', '2026-01-30 13:15:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(68, 22, 'sb6640248131@lru.ac.th', '2026-01-30 13:48:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(69, 26, 'sb6640248123@lru.ac.th', '2026-01-30 19:08:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(70, 22, 'sb6640248131@lru.ac.th', '2026-02-01 00:55:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(71, 25, 'n72520448@gmail.com', '2026-02-01 19:34:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(72, 22, 'sb6640248131@lru.ac.th', '2026-02-02 09:09:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(73, 0, 'One123one1@hotmail.com', '2026-02-02 12:43:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, 'not_found'),
(74, 0, 'nanoone343@gmail.com', '2026-02-02 12:44:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, 'not_found'),
(75, 1, 'nanoone342@gmail.com', '2026-02-02 12:46:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, 'wrong_password'),
(76, 27, 'natanon200404@gmail.com', '2026-02-02 13:02:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(77, 28, 'nonthawatphunklang@gmail.com', '2026-02-02 13:21:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(78, 28, 'nonthawatphunklang@gmail.com', '2026-02-02 13:32:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, 'disabled'),
(79, 28, 'nonthawatphunklang@gmail.com', '2026-02-02 13:34:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(80, 29, 'dedee30520@gmail.com', '2026-02-02 13:50:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(81, 25, 'n72520448@gmail.com', '2026-02-02 16:29:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(82, 22, 'sb6640248131@lru.ac.th', '2026-02-03 00:13:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(83, 22, 'sb6640248131@lru.ac.th', '2026-02-03 00:22:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(84, 29, 'dedee30520@gmail.com', '2026-02-03 00:26:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(85, 27, 'natanon200404@gmail.com', '2026-02-03 00:36:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(86, 28, 'nonthawatphunklang@gmail.com', '2026-02-03 00:39:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(87, 13, 'sakunrat2196@gmail.com', '2026-02-03 00:44:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(88, 9, 'natdanai021046@gmail.com', '2026-02-03 00:51:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(89, 1, 'nanoone342@gmail.com', '2026-02-03 01:37:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, 'wrong_password'),
(90, 22, 'sb6640248131@lru.ac.th', '2026-02-03 01:38:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(91, 1, 'nanoone342@gmail.com', '2026-02-03 01:38:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(92, 9, 'natdanai021046@gmail.com', '2026-02-06 11:36:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(93, 30, 'lolnotgt@gmail.com', '2026-02-06 13:54:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(94, 26, 'sb6640248123@lru.ac.th', '2026-02-06 20:41:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(95, 11, 'sukandasomsiang543@gmail.com', '2026-02-06 22:36:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(96, 26, 'sb6640248123@lru.ac.th', '2026-02-06 22:49:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(97, 11, 'sukandasomsiang543@gmail.com', '2026-02-07 22:16:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(98, 11, 'sukandasomsiang543@gmail.com', '2026-02-07 23:56:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(99, 1, 'nanoone342@gmail.com', '2026-02-08 14:59:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(100, 11, 'sukandasomsiang543@gmail.com', '2026-02-08 16:47:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(101, 9, 'natdanai021046@gmail.com', '2026-02-11 09:13:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(102, 13, 'sakunrat2196@gmail.com', '2026-02-11 09:41:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(103, 13, 'sakunrat2196@gmail.com', '2026-02-11 10:02:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, 'disabled'),
(104, 13, 'sakunrat2196@gmail.com', '2026-02-11 10:07:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(105, 13, 'sakunrat2196@gmail.com', '2026-02-11 10:22:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(106, 31, 'sb6640248126@lru.ac.th', '2026-02-11 13:34:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(107, 22, 'sb6640248131@lru.ac.th', '2026-02-11 13:55:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(108, 1, 'nanoone342@gmail.com', '2026-02-12 00:27:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, 'wrong_password'),
(109, 1, 'nanoone342@gmail.com', '2026-02-12 00:27:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(110, 1, 'nanoone342@gmail.com', '2026-02-12 22:43:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, 'wrong_password'),
(111, 1, 'nanoone342@gmail.com', '2026-02-12 22:43:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(112, 11, 'sukandasomsiang543@gmail.com', '2026-02-13 14:08:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(113, 1, 'nanoone342@gmail.com', '2026-02-13 15:08:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(114, 1, 'nanoone342@gmail.com', '2026-02-14 11:27:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(115, 11, 'sukandasomsiang543@gmail.com', '2026-02-14 17:16:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(116, 11, 'sukandasomsiang543@gmail.com', '2026-02-15 13:39:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(117, 1, 'nanoone342@gmail.com', '2026-02-15 13:48:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, 'wrong_password'),
(118, 1, 'nanoone342@gmail.com', '2026-02-15 13:48:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, 'wrong_password'),
(119, 1, 'nanoone342@gmail.com', '2026-02-15 13:48:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(120, 11, 'sukandasomsiang543@gmail.com', '2026-02-15 20:31:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(121, 11, 'sukandasomsiang543@gmail.com', '2026-02-16 03:03:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(122, 1, 'nanoone342@gmail.com', '2026-02-16 03:04:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(123, 1, 'nanoone342@gmail.com', '2026-02-16 16:02:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(124, 1, 'nanoone342@gmail.com', '2026-02-16 20:08:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, 'wrong_password'),
(125, 1, 'nanoone342@gmail.com', '2026-02-16 20:08:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(126, 22, 'sb6640248131@lru.ac.th', '2026-02-17 00:00:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, 'disabled'),
(127, 22, 'sb6640248131@lru.ac.th', '2026-02-17 00:02:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(128, 22, 'sb6640248131@lru.ac.th', '2026-02-17 14:54:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(129, 22, 'sb6640248131@lru.ac.th', '2026-02-17 15:01:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(130, 1, 'nanoone342@gmail.com', '2026-02-17 20:18:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(131, 22, 'sb6640248131@lru.ac.th', '2026-02-17 23:30:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(132, 0, 'sb6640248126@lru.ac.th', '2026-02-18 00:13:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, 'not_found'),
(133, 0, 'sb6640248126@lru.ac.th', '2026-02-18 00:13:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, 'not_found'),
(134, 13, 'sakunrat2196@gmail.com', '2026-02-18 00:14:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(135, 9, 'natdanai021046@gmail.com', '2026-02-18 00:25:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(136, 22, 'sb6640248131@lru.ac.th', '2026-02-18 09:51:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(137, 22, 'sb6640248131@lru.ac.th', '2026-02-18 16:19:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 1, 'success'),
(138, 1, 'nanoone342@gmail.com', '2026-02-23 16:42:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, 'success'),
(139, 1, 'nanoone342@gmail.com', '2026-02-24 14:09:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, 'success'),
(140, 1, 'nanoone342@gmail.com', '2026-03-15 10:54:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, 'success'),
(141, 22, 'sb6640248131@lru.ac.th', '2026-03-15 11:30:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, 'success'),
(142, 22, 'sb6640248131@lru.ac.th', '2026-03-15 14:07:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, 'success'),
(143, 11, 'sukandasomsiang543@gmail.com', '2026-04-08 09:04:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 1, 'success'),
(144, 11, 'sukandasomsiang543@gmail.com', '2026-04-14 14:02:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 1, 'success'),
(145, 11, 'sukandasomsiang543@gmail.com', '2026-04-21 12:19:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 1, 'success'),
(146, 11, 'sukandasomsiang543@gmail.com', '2026-04-21 13:41:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 1, 'success'),
(147, 11, 'sukandasomsiang543@gmail.com', '2026-04-24 13:43:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 1, 'success'),
(148, 1, 'nanoone342@gmail.com', '2026-04-24 14:52:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 1, 'success');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `member_id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `province_id` int(11) NOT NULL,
  `district_id` int(11) NOT NULL,
  `subdistrict_id` int(11) NOT NULL,
  `zipcode` varchar(5) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=active, 0=disabled',
  `verification_code` varchar(10) DEFAULT NULL,
  `code_expire` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`member_id`, `fullname`, `address`, `province_id`, `district_id`, `subdistrict_id`, `zipcode`, `phone`, `email`, `password`, `created_at`, `status`, `verification_code`, `code_expire`) VALUES
(1, 'หนึ่งเดียว เทียกสีบุญ', 'ดอนพยอม 74 หมู่ 4', 32, 4406, 440608, '44130', '0651078576', 'nanoone342@gmail.com', '$2y$12$9XEOiBvTahFuOeNLg9L/HOfa39YH1jRZ6viFZ8gQXGDC2vwrNdhSy', '2025-08-05 11:28:55', 1, NULL, NULL),
(9, 'ณัฐดนัย โถแพงจันทร์', 'บ้านริมชล 347/1 หมู่11', 30, 4201, 420107, '42000', '0987856709', 'natdanai021046@gmail.com', '$2y$12$cIom9XFMdmRcm4bjHR2KueWg89EygkFQEzv69.vKt4QvIFESlyG2e', '2025-10-03 11:32:23', 1, NULL, NULL),
(11, 'สุกานดา  สมเสียง', '34 หมู่4 บ้านห้วยสีดา', 30, 4203, 420308, '42110', '0615282330', 'sukandasomsiang543@gmail.com', '$2y$12$CLzABkWSwmKBqC1xVcZSb.M.4SpGsRn2rnWuYo0hwudOmN3hL6V.m', '2025-11-09 14:51:22', 1, NULL, NULL),
(13, 'สกุลรัตน์ บุผู', 'โนสววค์ 109/50', 32, 4406, 440608, '44130', '0630103946', 'sakunrat2196@gmail.com', '$2y$12$mUvjy0nhOaeguYCKL/liDuz9qeb2L1JtturTPG/RQbUPKcWaKyDLq', '2025-11-17 08:50:39', 1, NULL, NULL),
(14, 'ธีรภัทร์ ชุมคำ', 'ป่าไม้งาม', 27, 3901, 390114, '39000', '0639469205', 'Tiraphat@gmail.com', '$2y$12$MU58.N.L18x4awQp6q4KK.f7RSWm1SPUtQgOhHKy023PT69.8sC9S', '2025-11-18 10:53:50', 1, NULL, NULL),
(19, 'ธนพร แก้วมีสี', 'บ้านหนองผักก้าม', 30, 4201, 420101, '42000', '0650378320', 'thanaporn282548@gmail.com', '$2y$12$utljdRO1m0LqeUMylvS1ceOryn.pGcFxL2W/7tVIz2BIvuadE2WC2', '2025-11-28 13:33:14', 1, NULL, NULL),
(22, 'หนึ่งเดียว', 'บ้านดอนพยอม 74 หมู่4', 32, 4406, 440608, '44130', '0651078576', 'sb6640248131@lru.ac.th', '$2y$12$nNSJ3wLrElSmKqRz18akwO/OHrym.Zzg3o/pTWLfsLJVP8gHqx9Ui', '2026-01-27 01:20:24', 1, NULL, NULL),
(24, 'สุดหล่อของแม่ค้า', 'เมี่ยง 55/5 หมู่5', 1, 1007, 100704, '10330', '0651078576', 'sb6640248118@lru.ac.th', '$2y$12$W7omM7Vsp3Tmd3RY/IeJLuUe9j2xTFhTdA6w8YUXKBkPIVjmuqfbW', '2026-01-28 18:01:59', 1, NULL, NULL),
(25, 'สมหวัง ดังใจ', 'บ้านดอนพยอม', 32, 4406, 440608, '44130', '0651078576', 'n72520448@gmail.com', '$2y$12$/B3j.I642eqiD8k8SGeXI.iwqr6Vy0nS3QpApzWq1j/mY6GhAl0.O', '2026-01-29 16:15:40', 1, NULL, NULL),
(26, 'sukanda  somsiang', '34', 30, 4203, 420308, '42110', '0615282330', 'sb6640248123@lru.ac.th', '$2y$12$sw/Thr4UyVCJSubogaWZou6O7tymns32HWxEJvgX9pFsLtSwaQSuK', '2026-01-30 19:08:16', 1, NULL, NULL),
(27, 'Natanon Kongsuk', 'บ้านหนองผักกล้าม, 5 541', 30, 4201, 420102, '42000', '0857397511', 'natanon200404@gmail.com', '$2y$12$PzdkJ.H.Sbmnk7JewPeew.noyd5az8J0eA8gIYZU2y2u/DjoZ1a76', '2026-02-02 13:02:08', 1, NULL, NULL),
(28, 'นนทวัฒน์ พูลกลาง', 'บุญทัน', 27, 3902, 390207, '22222', '0825741700', 'nonthawatphunklang@gmail.com', '$2y$12$IxiXgv9uo6o7stndNSTTtOoSqkSTMBVw3H3TTt1PhqQkK9AAsb75S', '2026-02-02 13:20:06', 1, NULL, NULL),
(29, 'Phuriphat Srimongkon', 'บ.ท่าวังแคน 6 หมู่ 9', 30, 4201, 420113, '42100', '0923737194', 'dedee30520@gmail.com', '$2y$12$RPNNRub5RctWDhW1v/AeWe792GukDunLVbMQndAYEMOw6ZdS.JIxK', '2026-02-02 13:49:47', 1, NULL, NULL),
(30, 'ภวัต อยู่ภักดี', 'หนองหิน 49 หมู่1', 30, 4214, 421402, '42190', '0659711449', 'lolnotgt@gmail.com', '$2y$12$4RKtZyXgZW89rNHKo0Qyce0XcCFCKKhgQzWCnx2aC.t8hDdGF5.E6', '2026-02-06 13:54:02', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_code` varchar(50) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_address` text NOT NULL,
  `receive_type` enum('pickup','delivery') NOT NULL,
  `receive_datetime` datetime NOT NULL,
  `order_status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `order_date` datetime DEFAULT current_timestamp(),
  `admin_note` text DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_code`, `member_id`, `customer_name`, `customer_phone`, `customer_address`, `receive_type`, `receive_datetime`, `order_status`, `order_date`, `admin_note`, `total_amount`) VALUES
(63, 'ORD20260421073624576', 11, 'สุกานดา  สมเสียง', '0615282330', '', 'pickup', '2026-04-23 12:00:00', 'completed', '2026-04-21 14:36:25', NULL, 50.00),
(64, 'ORD20260424065235919', 11, 'สุกานดา  สมเสียง', '0615282330', '5555', 'pickup', '0000-00-00 00:00:00', 'pending', '2026-04-24 13:52:35', NULL, 90.00),
(65, 'ORD20260424070143322', NULL, 'น้องป๊อบ', '0615282330', 'เดี๋ยวเข้าไปรับนะคะ', 'pickup', '2026-04-24 13:00:00', 'approved', '2026-04-24 14:01:43', NULL, 70.00),
(66, 'ORD20260425034826277', NULL, 'เฟิร์น', '0000000000', '', 'pickup', '2026-04-25 10:00:00', 'pending', '2026-04-25 10:48:27', NULL, 60.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `product_name` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `quantity`, `price`, `product_name`) VALUES
(80, 63, 5, 1, 50.00, 'มะยงชิด'),
(81, 64, 8, 1, 40.00, 'ไข่ไก่'),
(82, 64, 7, 1, 50.00, 'มะม่วงเขียวสามรส'),
(83, 65, 16, 1, 70.00, 'เผือกทอด'),
(84, 66, 11, 1, 60.00, 'ปลาตะเพียนสด');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `seasonal` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `product_image` varchar(255) DEFAULT NULL,
  `product_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `category`, `price`, `unit`, `seasonal`, `status`, `product_image`, `product_description`) VALUES
(5, 'มะยงชิด', 'ผลไม้สด', 50.00, 'กิโลกรัม', 1, 'active', '1770377357_6985d08d7e5f0.jpg', 'หวานอมเปรี้ยว'),
(7, 'มะม่วงเขียวสามรส', 'ผลไม้สด', 50.00, 'กิโลกรัม', 1, 'active', '1770382781_6985e5bdb88d9.jpg', 'ดี'),
(8, 'ไข่ไก่', 'ไข่ไก่', 40.00, 'ถุง', 1, 'active', '1770460864_698716c04f6fa.jpg', 'ไข่ไก่คุณภาพดี ไขใบใหญ่ๆสดทุกวัน'),
(9, 'กล้วยฉาบ', 'ผลไม้แปรรูป', 50.00, 'ห่อ', 1, 'active', '1770460829_6987169d35765.jpg', 'ทอดใหม่ทุกวัน'),
(10, 'วัว', 'สัตว์', 1500.00, 'ตัว', 1, 'active', '1770460756_698716544187c.jpg', 'วัวอายุ 3เดือน กำลังโตเพศเมียเหมาะนำไปเลี้ยงทำแม่พันธ์ุ'),
(11, 'ปลาตะเพียนสด', 'ปลา', 60.00, 'แพ็ค', 1, 'active', '1770466625_69872d411db76.jpg', 'ปลาตะเพียนสด พึ่งตกจากบ่อตอนเช้า ตัวใหญ่เล็กคละไซต์'),
(13, 'ส้มโอ', 'ผลไม้สด', 50.00, 'กิโลกรัม', 1, 'active', '1771002213_698f59659e9ee.jpg', 'ส้มโอสีทองหวานอมเปรี้ยวเก็บสดๆจากสวน'),
(14, 'น้ำผึ้ง', 'น้ำผึ้ง', 250.00, 'ขวด', 1, 'inactive', '1771002446_698f5a4e1b5b5.jpg', 'นำผึ้งเดือนห้าแท้ สะอาดหวาน'),
(15, 'มะขามแช่อิ่ม', 'ผลไม้แปรรูป', 100.00, 'ถุง', 0, 'active', '1771002590_698f5adedd1a0.jpg', 'มะขามกรอบอร่อยไม่เปรี้ยวเกินไป ถุงละ 50 กรัม'),
(16, 'เผือกทอด', 'อาหารแปรรูป', 70.00, 'ถุง', 1, 'active', '1771862703_699c7aaf125c9.jpg', 'อร่อยสะอาด ปลอดภัย');

-- --------------------------------------------------------

--
-- Table structure for table `system_administrator`
--

CREATE TABLE `system_administrator` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('main','sub') NOT NULL DEFAULT 'sub',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_administrator`
--

INSERT INTO `system_administrator` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(4, 'หนึ่งเดียว', 'nanoone342@gmail.com', '$2y$10$.Lyt6Lbm3BvlJ/x.Dsly0eKQSo4RsbNxuiCKDNkneIIMPesJMWOvu', 'main', '2025-05-21 11:28:17'),
(10, 'ป๊อบปิ', 'Poppy@gmail.com', '$2y$12$YIDIoKCe6rMozvWgGwQYVufwDviyQZ0/o6WsMwWUWENq3nZoskNfm', 'main', '2025-11-09 07:22:19'),
(13, 'admin', 'n72520448@gmail.com', '$2y$12$gqAHbwAk8eWIYfF6D49Ive17RFx8XDCv1W2bF.8QKhVE8djhf06A2', 'sub', '2026-02-12 17:26:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`bookings_id`),
  ADD UNIQUE KEY `booking_code` (`booking_code`),
  ADD KEY `fk_bookings_member` (`member_id`),
  ADD KEY `idx_booking_date_status` (`booking_date`,`status`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`courses_id`);

--
-- Indexes for table `course_comments`
--
ALTER TABLE `course_comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `idx_comment_guest` (`courses_id`,`guest_identifier`);

--
-- Indexes for table `course_rating`
--
ALTER TABLE `course_rating`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `fk_rating_courses` (`courses_id`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `email` (`email`),
  ADD KEY `attempted_at` (`attempted_at`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`member_id`) USING BTREE,
  ADD UNIQUE KEY `email` (`email`) USING BTREE;

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `fk_order_member` (`member_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `fk_item_order` (`order_id`),
  ADD KEY `fk_item_product` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `system_administrator`
--
ALTER TABLE `system_administrator`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `username` (`username`) USING BTREE,
  ADD UNIQUE KEY `email` (`email`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `bookings_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `courses_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `course_comments`
--
ALTER TABLE `course_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `course_rating`
--
ALTER TABLE `course_rating`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `system_administrator`
--
ALTER TABLE `system_administrator`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE SET NULL;

--
-- Constraints for table `course_comments`
--
ALTER TABLE `course_comments`
  ADD CONSTRAINT `fk_comments_courses` FOREIGN KEY (`courses_id`) REFERENCES `courses` (`courses_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course_rating`
--
ALTER TABLE `course_rating`
  ADD CONSTRAINT `fk_rating_courses` FOREIGN KEY (`courses_id`) REFERENCES `courses` (`courses_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_item_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
