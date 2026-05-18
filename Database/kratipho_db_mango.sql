-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 18, 2026 at 02:54 PM
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
  `payment_slip` varchar(255) DEFAULT NULL,
  `payment_qr_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`bookings_id`, `booking_code`, `member_id`, `guest_name`, `guest_email`, `guest_phone`, `booking_date`, `booking_time`, `visitor_count`, `lunch_request`, `price_total`, `deposit_amount`, `balance_amount`, `booking_type`, `status`, `is_member_booking`, `attachment_path`, `created_at`, `updated_at`, `payment_slip`, `payment_qr_path`) VALUES
(218, 'BK250101', 1, 'หนึ่งเดียว เทียกสีบุญ', 'nanoone342@gmail.com', '0651078576', '2025-01-15', '12:00:00', 2, 1, 800.00, 300.00, 500.00, 'private', 'confirmed', 1, NULL, '2025-01-10 09:30:00', '2026-05-15 15:39:14', NULL, NULL),
(219, 'BK250102', 9, 'ณัฐดนัย โถแพงจันทร์', 'natdanai021046@gmail.com', '0987856709', '2025-01-28', '18:30:00', 4, 0, 1500.00, 500.00, 1000.00, 'private', 'confirmed', 1, NULL, '2025-01-25 14:20:00', '2026-05-15 15:39:14', NULL, NULL),
(220, 'BK250201', 11, 'สุกานดา สมเสียง', 'sukandasomsiang543@gmail.com', '0615282330', '2025-02-14', '19:00:00', 2, 1, 1200.00, 1200.00, 0.00, 'private', 'confirmed', 1, NULL, '2025-02-11 11:15:00', '2026-05-15 15:39:14', NULL, NULL),
(221, 'BK250301', 13, 'สกุลรัตน์ บุผู', 'sakunrat2196@gmail.com', '0630103946', '2025-03-10', '13:00:00', 5, 0, 2000.00, 500.00, 1500.00, 'corporate', 'cancelled', 1, NULL, '2025-03-05 10:00:00', '2026-05-15 15:39:14', NULL, NULL),
(222, 'BK250302', 14, 'ธีรภัทร์ ชุมคำ', 'Tiraphat@gmail.com', '0639469205', '2025-03-22', '12:30:00', 3, 1, 1050.00, 350.00, 700.00, 'private', 'confirmed', 1, NULL, '2025-03-18 16:40:00', '2026-05-15 15:39:14', NULL, NULL),
(223, 'BK250401', 19, 'ธนพร แก้วมีสี', 'thanaporn282548@gmail.com', '0650378320', '2025-04-13', '11:00:00', 6, 1, 2400.00, 1000.00, 1400.00, 'private', 'confirmed', 1, NULL, '2025-04-10 09:12:00', '2026-05-15 15:39:14', NULL, NULL),
(224, 'BK250501', 22, 'หนึ่งเดียว', 'sb6640248131@lru.ac.th', '0651078576', '2025-05-05', '17:00:00', 4, 0, 1600.00, 600.00, 1000.00, 'private', 'confirmed', 1, NULL, '2025-05-01 13:22:00', '2026-05-15 15:39:14', NULL, NULL),
(225, 'BK250502', 24, 'สุดหล่อของแม่ค้า', 'sb6640248118@lru.ac.th', '0651078576', '2025-05-20', '18:00:00', 10, 1, 4500.00, 1500.00, 3000.00, 'corporate', 'confirmed', 1, NULL, '2025-05-15 11:05:00', '2026-05-15 15:39:14', NULL, NULL),
(226, 'BK250601', 25, 'สมหวัง ดังใจ', 'n72520448@gmail.com', '0651078576', '2025-06-15', '12:00:00', 3, 1, 900.00, 300.00, 600.00, 'private', 'cancelled', 1, NULL, '2025-06-12 14:50:00', '2026-05-15 15:39:14', NULL, NULL),
(227, 'BK250701', 26, 'sukanda somsiang', 'sb6640248123@lru.ac.th', '0615282330', '2025-07-07', '13:30:00', 2, 0, 700.00, 200.00, 500.00, 'private', 'confirmed', 1, NULL, '2025-07-04 10:30:00', '2026-05-15 15:39:14', NULL, NULL),
(228, 'BK250801', 27, 'Natanon Kongsuk', 'natanon200404@gmail.com', '0857397511', '2025-08-12', '12:00:00', 5, 1, 1800.00, 500.00, 1300.00, 'private', 'confirmed', 1, NULL, '2025-08-08 09:00:00', '2026-05-15 15:39:14', NULL, NULL),
(229, 'BK250901', 28, 'นนทวัฒน์ พูลกลาง', 'nonthawatphunklang@gmail.com', '0825741700', '2025-09-20', '18:30:00', 4, 0, 1400.00, 400.00, 1000.00, 'private', 'confirmed', 1, NULL, '2025-09-15 15:10:00', '2026-05-15 15:39:14', NULL, NULL),
(230, 'BK251001', 29, 'Phuriphat Srimongkon', 'dedee30520@gmail.com', '0923737194', '2025-10-23', '11:30:00', 8, 1, 3200.00, 1000.00, 2200.00, 'corporate', 'confirmed', 1, NULL, '2025-10-18 11:00:00', '2026-05-15 15:39:14', NULL, NULL),
(231, 'BK251101', 32, 'อินทิรา ไชโสดา', 'sb6640248128@lru.ac.th', '0840878100', '2025-11-15', '12:00:00', 3, 1, 1050.00, 350.00, 700.00, 'private', 'confirmed', 1, NULL, '2025-11-10 14:15:00', '2026-05-15 15:39:14', NULL, NULL),
(232, 'BK251102', 1, 'หนึ่งเดียว เทียกสีบุญ', 'nanoone342@gmail.com', '0651078576', '2025-11-28', '17:30:00', 2, 0, 750.00, 750.00, 0.00, 'private', 'cancelled', 1, NULL, '2025-11-25 09:20:00', '2026-05-15 15:39:14', NULL, NULL),
(233, 'BK251201', 9, 'ณัฐดนัย โถแพงจันทร์', 'natdanai021046@gmail.com', '0987856709', '2025-12-25', '19:00:00', 6, 1, 2800.00, 1000.00, 1800.00, 'private', 'confirmed', 1, NULL, '2025-12-20 16:30:00', '2026-05-15 15:39:14', NULL, NULL),
(234, 'BK251202', 11, 'สุกานดา สมเสียง', 'sukandasomsiang543@gmail.com', '0615282330', '2025-12-31', '21:00:00', 12, 1, 5500.00, 2000.00, 3500.00, 'corporate', 'confirmed', 1, NULL, '2025-12-28 10:00:00', '2026-05-15 15:39:14', NULL, NULL),
(235, 'BK260101', 13, 'สกุลรัตน์ บุผู', 'sakunrat2196@gmail.com', '0630103946', '2026-01-10', '12:00:00', 4, 1, 1600.00, 500.00, 1100.00, 'private', 'confirmed', 1, NULL, '2026-01-05 11:00:00', '2026-05-15 15:39:14', NULL, NULL),
(236, 'BK260102', 14, 'ธีรภัทร์ ชุมคำ', 'Tiraphat@gmail.com', '0639469205', '2026-01-20', '18:00:00', 2, 0, 800.00, 0.00, 800.00, 'private', 'cancelled', 1, NULL, '2026-01-15 14:00:00', '2026-05-15 15:39:14', NULL, NULL),
(237, 'BK260201', 19, 'ธนพร แก้วมีสี', 'thanaporn282548@gmail.com', '0650378320', '2026-02-14', '19:30:00', 2, 1, 1500.00, 500.00, 1000.00, 'private', 'confirmed', 1, NULL, '2026-02-10 15:30:00', '2026-05-15 15:39:14', NULL, NULL),
(238, 'BK260202', 22, 'หนึ่งเดียว', 'sb6640248131@lru.ac.th', '0651078576', '2026-02-25', '13:00:00', 5, 0, 1800.00, 1800.00, 0.00, 'private', 'confirmed', 1, NULL, '2026-02-22 09:15:00', '2026-05-15 15:39:14', NULL, NULL),
(239, 'BK260301', 24, 'สุดหล่อของแม่ค้า', 'sb6640248118@lru.ac.th', '0651078576', '2026-03-03', '12:00:00', 3, 1, 1050.00, 350.00, 700.00, 'private', 'confirmed', 1, NULL, '2026-03-01 10:20:00', '2026-05-15 15:39:14', NULL, NULL),
(240, 'BK260302', 25, 'สมหวัง ดังใจ', 'n72520448@gmail.com', '0651078576', '2026-03-18', '17:30:00', 7, 1, 2900.00, 1000.00, 1900.00, 'corporate', 'confirmed', 1, NULL, '2026-03-14 16:00:00', '2026-05-15 15:39:14', NULL, NULL),
(241, 'BK260401', 26, 'sukanda somsiang', 'sb6640248123@lru.ac.th', '0615282330', '2026-04-13', '11:00:00', 10, 1, 4000.00, 1000.00, 3000.00, 'corporate', 'confirmed', 1, NULL, '2026-04-09 08:45:00', '2026-05-15 15:39:14', NULL, NULL),
(242, 'BK260402', 27, 'Natanon Kongsuk', 'natanon200404@gmail.com', '0857397511', '2026-04-25', '18:00:00', 4, 0, 1400.00, 400.00, 1000.00, 'private', 'cancelled', 1, NULL, '2026-04-20 13:10:00', '2026-05-15 15:39:14', NULL, NULL),
(243, 'BK260501', 28, 'นนทวัฒน์ พูลกลาง', 'nonthawatphunklang@gmail.com', '0825741700', '2026-05-01', '12:30:00', 2, 1, 850.00, 300.00, 550.00, 'private', 'confirmed', 1, NULL, '2026-04-28 11:15:00', '2026-05-15 15:39:14', NULL, NULL),
(244, 'BK260502', 29, 'Phuriphat Srimongkon', 'dedee30520@gmail.com', '0923737194', '2026-05-14', '13:00:00', 6, 1, 2400.00, 1000.00, 1400.00, 'private', 'awaiting_payment', 1, NULL, '2026-05-12 15:40:00', '2026-05-15 15:39:14', NULL, NULL),
(245, 'BK260503', 32, 'อินทิรา ไชโสดา', 'sb6640248128@lru.ac.th', '0840878100', '2026-05-20', '19:00:00', 3, 0, 1050.00, 0.00, 1050.00, 'private', 'pending', 1, NULL, '2026-05-15 10:30:00', '2026-05-15 15:39:14', NULL, NULL),
(246, 'BK260601', 1, 'หนึ่งเดียว เทียกสีบุญ', 'nanoone342@gmail.com', '0651078576', '2026-06-05', '12:00:00', 5, 1, 1800.00, 500.00, 1300.00, 'private', 'confirmed', 1, NULL, '2026-05-10 09:00:00', '2026-05-15 15:39:14', NULL, NULL),
(247, 'BK260602', 9, 'ณัฐดนัย โถแพงจันทร์', 'natdanai021046@gmail.com', '0987856709', '2026-06-18', '18:30:00', 4, 0, 1400.00, 0.00, 1400.00, 'private', 'pending', 1, NULL, '2026-05-14 11:20:00', '2026-05-15 15:39:14', NULL, NULL),
(248, 'BK260701', 11, 'สุกานดา สมเสียง', 'sukandasomsiang543@gmail.com', '0615282330', '2026-07-10', '13:00:00', 8, 1, 3500.00, 1000.00, 2500.00, 'corporate', 'awaiting_payment', 1, NULL, '2026-05-11 16:50:00', '2026-05-15 15:39:14', NULL, NULL),
(249, 'BK260702', 13, 'สกุลรัตน์ บุผู', 'sakunrat2196@gmail.com', '0630103946', '2026-07-26', '17:00:00', 2, 0, 700.00, 200.00, 500.00, 'private', 'confirmed', 1, NULL, '2026-05-05 14:00:00', '2026-05-15 15:39:14', NULL, NULL),
(250, 'BK260801', 14, 'ธีรภัทร์ ชุมคำ', 'Tiraphat@gmail.com', '0639469205', '2026-08-12', '11:30:00', 4, 1, 1600.00, 500.00, 1100.00, 'private', 'confirmed', 1, NULL, '2026-05-02 10:00:00', '2026-05-15 15:39:14', NULL, NULL),
(251, 'BK260802', 19, 'ธนพร แก้วมีสี', 'thanaporn282548@gmail.com', '0650378320', '2026-08-30', '18:00:00', 5, 1, 2000.00, 0.00, 2000.00, 'private', 'pending', 1, NULL, '2026-05-15 13:45:00', '2026-05-15 15:39:14', NULL, NULL),
(252, 'BK260901', 22, 'หนึ่งเดียว', 'sb6640248131@lru.ac.th', '0651078576', '2026-09-15', '12:00:00', 3, 0, 900.00, 300.00, 600.00, 'private', 'confirmed', 1, NULL, '2026-05-12 09:15:00', '2026-05-15 15:39:14', NULL, NULL),
(253, 'BK261001', 24, 'สุดหล่อของแม่ค้า', 'sb6640248118@lru.ac.th', '0651078576', '2026-10-10', '13:30:00', 10, 1, 4500.00, 1500.00, 3000.00, 'corporate', 'awaiting_payment', 1, NULL, '2026-05-01 16:20:00', '2026-05-15 15:39:14', NULL, NULL),
(254, 'BK261002', 25, 'สมหวัง ดังใจ', 'n72520448@gmail.com', '0651078576', '2026-10-23', '17:00:00', 4, 0, 1400.00, 400.00, 1000.00, 'private', 'confirmed', 1, NULL, '2026-05-14 11:00:00', '2026-05-15 15:39:14', NULL, NULL),
(255, 'BK261101', 26, 'sukanda somsiang', 'sb6640248123@lru.ac.th', '0615282330', '2026-11-15', '12:30:00', 3, 1, 1100.00, 0.00, 1100.00, 'private', 'pending', 1, NULL, '2026-05-15 14:10:00', '2026-05-15 15:39:14', NULL, NULL),
(256, 'BK261201', 27, 'Natanon Kongsuk', 'natanon200404@gmail.com', '0857397511', '2026-12-05', '18:00:00', 6, 1, 2500.00, 1000.00, 1500.00, 'private', 'confirmed', 1, NULL, '2026-05-10 10:00:00', '2026-05-15 15:39:14', NULL, NULL),
(257, 'BK261202', 28, 'นนทวัฒน์ พูลกลาง', 'nonthawatphunklang@gmail.com', '0825741700', '2026-12-31', '20:00:00', 15, 1, 7500.00, 2500.00, 5000.00, 'corporate', 'confirmed', 1, NULL, '2026-05-13 15:30:00', '2026-05-15 15:39:14', NULL, NULL),
(258, 'BK270101', 29, 'Phuriphat Srimongkon', 'dedee30520@gmail.com', '0923737194', '2027-01-01', '12:00:00', 4, 1, 1800.00, 500.00, 1300.00, 'private', 'confirmed', 1, NULL, '2026-05-12 11:00:00', '2026-05-15 15:39:14', NULL, NULL),
(259, 'BK270102', 32, 'อินทิรา ไชโสดา', 'sb6640248128@lru.ac.th', '0840878100', '2027-01-20', '18:30:00', 2, 0, 700.00, 0.00, 700.00, 'private', 'pending', 1, NULL, '2026-05-15 09:15:00', '2026-05-15 15:39:14', NULL, NULL),
(260, 'BK270201', 1, 'หนึ่งเดียว เทียกสีบุญ', 'nanoone342@gmail.com', '0651078576', '2027-02-14', '19:00:00', 2, 1, 1500.00, 500.00, 1000.00, 'private', 'awaiting_payment', 1, NULL, '2026-05-14 16:30:00', '2026-05-15 15:39:14', NULL, NULL),
(261, 'BK270301', 9, 'ณัฐดนัย โถแพงจันทร์', 'natdanai021046@gmail.com', '0987856709', '2027-03-15', '13:00:00', 5, 0, 1750.00, 0.00, 1750.00, 'private', 'pending', 1, NULL, '2026-05-15 15:22:00', '2026-05-15 15:39:14', NULL, NULL),
(262, 'BK270401', 11, 'สุกานดา สมเสียง', 'sukandasomsiang543@gmail.com', '0615282330', '2027-04-13', '12:00:00', 20, 1, 9500.00, 3000.00, 6500.00, 'corporate', 'awaiting_payment', 1, NULL, '2026-05-15 11:40:00', '2026-05-15 15:39:14', NULL, NULL),
(263, 'BK270501', 13, 'สกุลรัตน์ บุผู', 'sakunrat2196@gmail.com', '0630103946', '2027-05-05', '11:30:00', 3, 1, 1050.00, 0.00, 1050.00, 'private', 'pending', 1, NULL, '2026-05-15 15:30:00', '2026-05-15 15:39:14', NULL, NULL),
(264, 'BK270601', 14, 'ธีรภัทร์ ชุมคำ', 'Tiraphat@gmail.com', '0639469205', '2027-06-06', '18:00:00', 6, 1, 2600.00, 0.00, 2600.00, 'private', 'pending', 1, NULL, '2026-05-15 15:35:00', '2026-05-15 15:39:14', NULL, NULL),
(265, 'BK270701', 19, 'ธนพร แก้วมีสี', 'thanaporn282548@gmail.com', '0650378320', '2027-07-07', '12:00:00', 4, 0, 1400.00, 0.00, 1400.00, 'private', 'pending', 1, NULL, '2026-05-15 15:36:00', '2026-05-15 15:39:14', NULL, NULL),
(266, 'BK270801', 22, 'หนึ่งเดียว', 'sb6640248131@lru.ac.th', '0651078576', '2027-08-12', '13:00:00', 5, 1, 1900.00, 0.00, 1900.00, 'private', 'pending', 1, NULL, '2026-05-15 15:37:00', '2026-05-15 15:39:14', NULL, NULL),
(271, 'GV202605185094', 25, 'ทดสอบ', 'n72520448@gmail.com', '0651078576', '2026-05-28', '08:00:00', 100, 1, 19800.00, 5940.00, 13860.00, 'organization', 'confirmed', 1, 'uploads/1779084981_f37819fe2dfa.pdf', '2026-05-18 13:16:21', '2026-05-18 13:22:20', 'slip_271_1779085125.png', NULL),
(272, 'GV202605180236', 25, 'ทดสอบ', 'n72520448@gmail.com', '0651078576', '2026-05-30', '11:00:00', 1, 1, 4950.00, 1485.00, 3465.00, 'private', 'awaiting_payment', 1, NULL, '2026-05-18 14:01:54', '2026-05-18 14:03:24', NULL, NULL);

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
(11, 'สร้างอาชีพด้วยการแปรรูปกล้วยฉาบ', 'กลุ่มวิทยากรและผู้เข้าอบรมร่วมกันเรียนรู้ขั้นตอนการทำกล้วยฉาบอย่างใกล้ชิด ตั้งแต่การคัดเลือกกล้วยที่เหมาะสม การปอกเปลือก และเทคนิคการฝานกล้วยให้เป็นแผ่นบางสม่ำเสมอ ซึ่งเป็นหัวใจสำคัญที่ทำให้กล้วยฉาบมีความกรอบอร่อย กิจกรรมนี้มุ่งเน้นการเพิ่มมูลค่าผลผลิตทางการเกษตรในท้องถิ่น และส่งเสริมทักษะการแปรรูปอาหารเพื่อสร้างรายได้เสริมให้กับคนในชุมชน', '6988553fe7c95_476231058_1312361306677902_2878473574434026044_n.jpg', '6988553fe7ca2_477451611_1315659203014779_5459439891038737551_n.jpg', '6988553fe7ca4_596816631_1544823580098339_4542154463419387407_n.jpg', '2026-02-08 09:20:01', '2026-05-17 14:23:44'),
(18, 'เทคนิคการห่อมะม่วงคุณภาพ เพื่อผลผลิตที่สวยและปลอดภัย', 'กิจกรรมลงพื้นที่เรียนรู้วิธีการดูแลผลมะม่วงในระยะก่อนเก็บเกี่ยว โดยเน้นการ \"สังเกตลักษณะมะม่วงแก่\" เพื่อเลือกช่วงเวลาที่เหมาะสมที่สุดในการห่อ จากนั้นสาธิตการใช้ \"กระดาษคาร์บอน\" ในการห่อหุ้มผลมะม่วง ซึ่งมีคุณสมบัติเด่นในการช่วยป้องกันแมลงศัตรูพืช ลดการใช้สารเคมี และช่วยปรับผิวของมะม่วงให้มีสีนวลสวย สม่ำเสมอ เป็นที่ต้องการของตลาด', '699c7b9881a6a_LINE_ALBUM_สวนลุงเผือก_250612_26.jpg', NULL, NULL, '2026-02-23 16:08:58', '2026-05-17 14:29:37'),
(19, 'การเรียนรู้การขยายพันธุ์และอนุบาลต้นกล้ามะม่วง', 'กิจกรรมนี้มุ่งเน้นให้ผู้เรียนเข้าใจกระบวนการเปลี่ยนจาก \"เมล็ด\" สู่ \"ต้นพันธุ์ดี\" โดยมีหัวข้อการสอนหลักๆ ดังนี้:\r\n-การเตรียมต้นตอ (Stock Preparation): สอนการคัดเลือกและดูแลต้นมะม่วงพื้นเมืองที่เพาะจากเมล็ดให้แข็งแรง เพื่อใช้เป็นฐานรากที่ทนทานต่อโรคและสภาพอากาศ\r\n-เทคนิคการขยายพันธุ์ (Grafting Techniques): สาธิตวิธีการเปลี่ยนยอดหรือการทาบกิ่ง เพื่อให้ได้มะม่วงที่ให้ผลผลิตเร็วและตรงตามสายพันธุ์ที่ต้องการ (ไม่กลายพันธุ์)\r\n-การจัดการหลังการขยายพันธุ์: เรียนรู้วิธีการค้ำกิ่งด้วยไม้ไผ่เพื่อพยุงลำต้น และการติดป้ายบันทึกวันที่ (Labeling) เพื่อติดตามการเจริญเติบโตอย่างเป็นระบบ\r\n-การใช้เทคโนโลยีชีวภาพ (EM Technology): การประยุกต์ใช้จุลินทรีย์ที่มีประสิทธิภาพ (EM) ในการบำรุงดินและกระตุ้นระบบราก ช่วยให้ต้นกล้าฟื้นตัวได้ไวและมีอัตราการรอดชีวิตสูง', 'course_6a02fb6d564007.00668167.jpg', 'course_6a02fb6d581704.62753591.jpg', 'course_6a02fb6d5903f9.28113788.jpg', '2026-05-12 10:05:32', '2026-05-12 10:05:32'),
(20, 'เปลี่ยนวิถีเกษตร สู่ธรรมชาติบำบัดด้วยน้ำหมัก EM5', 'วิถีการทำเกษตรแบบพึ่งพาตนเอง โดยการผลิต \"น้ำหมักสมุนไพรขับไล่แมลง (EM5)\" ซึ่งเป็นการนำจุลินทรีย์ที่มีประสิทธิภาพ (EM) มาหมักร่วมกับกากน้ำตาล เหล้าขาว และน้ำส้มสายชู เพื่อใช้ฉีดพ่นป้องกันศัตรูพืชแทนการใช้สารเคมีอันตราย นอกจากจะช่วยลดต้นทุนแล้ว ยังช่วยรักษาสมดุลของระบบนิเวศในสวนและปลอดภัยต่อทั้งผู้ผลิตและผู้บริโภคอีกด้วย', 'course_6a0401a6578f14.32094571.jpg', 'course_6a0401a65a7bf5.19555885.jpg', 'course_6a0401a65cf6f1.44762544.jpg', '2026-05-13 04:44:22', '2026-05-17 14:17:32');

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
(31, 11, NULL, 'สอนดีมากค่ะ มีบอกเทคนิคดีด้วย ชอบกิจจกรรมนี้ค่ะ', '2026-05-12 15:33:25', 'สุวรรณา', '45a86db1a642f31f8234650f57ecf0b4_171'),
(32, 11, NULL, 'สนุกดีค่ะ', '2026-05-13 12:28:09', 'ป๊อบ', 'efcf1580ac290126e2a91c80a6612a0a_171'),
(34, 19, NULL, 'ได้ลงมือทำจริงค่ะ จากที่ไม่เคยทำมาก่อน ลุงสอนดีมาก ไม่ยากอย่างที่คิดเลยค่ะ ชอบมากๆ', '2026-05-13 17:50:13', 'สุกานดา  สมเสียง', 'd910c50882d0adf4e322d24a90bbd876_171'),
(35, 20, NULL, 'ได้เห็นวิธีห่อมะม่วงจริงๆจากมืออาชีพ ที่มีถุงหอโดยเฉพาะที่ทำให้มะม่วงสีสวยได้ความรู้ใหม่ ดีมากค่ะ', '2026-05-13 17:52:26', 'สุกานดา สมเสียง', '93402f0da40c466751ce97a905271a2b_171');

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
(22, 18, NULL, 5, '2026-02-23 23:10:35', 'd8c3d26b4577e49dc76c660282c4ee08_1771863033'),
(24, 11, NULL, 1, '2026-04-24 14:22:29', '859c62c49bb38838666a632f028159f7_1777015349'),
(25, 11, NULL, 5, '2026-04-24 14:24:53', '859c62c49bb38838666a632f028159f7_1777015493'),
(26, 18, NULL, 2, '2026-04-25 13:23:02', '176fcec33489a72d7ae474f82adcb8dc_106'),
(27, 11, NULL, 4, '2026-04-25 18:42:27', 'aa6e9aa7e46fe2ea929670fda319f246_106'),
(29, 11, NULL, 5, '2026-05-12 15:33:24', '45a86db1a642f31f8234650f57ecf0b4_171'),
(30, 11, NULL, 4, '2026-05-13 12:28:08', 'efcf1580ac290126e2a91c80a6612a0a_171'),
(31, 18, NULL, 2, '2026-05-13 15:06:10', '89132777e96d5cb8fc6c568fe58dfa6e_171'),
(32, 19, NULL, 5, '2026-05-13 17:50:13', 'd910c50882d0adf4e322d24a90bbd876_171'),
(33, 20, NULL, 5, '2026-05-13 17:52:26', '93402f0da40c466751ce97a905271a2b_171');

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
(171, 1, 'nanoone342@gmail.com', '2026-05-12 10:01:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 1, 'success'),
(172, 11, 'sukandasomsiang543@gmail.com', '2026-05-12 11:28:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 1, 'success'),
(173, 11, 'sukandasomsiang543@gmail.com', '2026-05-12 11:43:57', '182.232.93.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 1, 'success'),
(174, 1, 'nanoone342@gmail.com', '2026-05-12 12:35:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 1, 'success'),
(175, 1, 'nanoone342@gmail.com', '2026-05-12 14:18:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 1, 'success'),
(176, 11, 'sukandasomsiang543@gmail.com', '2026-05-12 14:25:12', '202.29.6.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 1, 'success'),
(177, 1, 'nanoone342@gmail.com', '2026-05-12 14:40:27', '202.176.112.66', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', 1, 'success'),
(178, 11, 'sukandasomsiang543@gmail.com', '2026-05-12 14:51:13', '202.29.6.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 1, 'success'),
(179, 1, 'nanoone342@gmail.com', '2026-05-13 15:01:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 1, 'success'),
(180, 1, 'nanoone342@gmail.com', '2026-05-14 01:23:57', '118.174.52.237', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', 0, 'wrong_password'),
(181, 1, 'nanoone342@gmail.com', '2026-05-14 01:24:01', '118.174.52.237', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', 0, 'wrong_password'),
(182, 1, 'nanoone342@gmail.com', '2026-05-14 01:24:05', '118.174.52.237', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', 0, 'wrong_password'),
(183, 1, 'nanoone342@gmail.com', '2026-05-14 01:24:32', '118.174.52.237', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', 1, 'success'),
(184, 11, 'sukandasomsiang543@gmail.com', '2026-05-14 12:26:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 1, 'success'),
(185, 11, 'sukandasomsiang543@gmail.com', '2026-05-14 15:18:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 1, 'success'),
(186, 1, 'nanoone342@gmail.com', '2026-05-14 15:25:41', '182.232.93.32', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 0, 'wrong_password'),
(187, 1, 'nanoone342@gmail.com', '2026-05-14 15:25:49', '182.232.93.32', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 1, 'success'),
(188, 11, 'sukandasomsiang543@gmail.com', '2026-05-14 15:36:38', '202.29.6.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 1, 'success'),
(189, 1, 'nanoone342@gmail.com', '2026-05-15 13:11:55', '182.53.86.8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 1, 'success'),
(190, 1, 'nanoone342@gmail.com', '2026-05-15 13:33:56', '182.53.86.8', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', 0, 'wrong_password'),
(191, 1, 'nanoone342@gmail.com', '2026-05-15 13:34:00', '182.53.86.8', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', 0, 'wrong_password'),
(192, 1, 'nanoone342@gmail.com', '2026-05-15 13:34:18', '182.53.86.8', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', 1, 'success'),
(193, 1, 'nanoone342@gmail.com', '2026-05-16 10:33:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 1, 'success'),
(194, 1, 'nanoone342@gmail.com', '2026-05-16 11:17:31', '182.53.87.232', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 0, 'wrong_password'),
(195, 1, 'nanoone342@gmail.com', '2026-05-16 11:17:39', '182.53.87.232', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 1, 'success'),
(196, 1, 'nanoone342@gmail.com', '2026-05-16 11:30:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 1, 'success'),
(197, 1, 'nanoone342@gmail.com', '2026-05-16 21:05:03', '182.53.87.232', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 1, 'success'),
(198, 25, 'n72520448@gmail.com', '2026-05-18 13:04:12', '202.29.6.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 1, 'success'),
(199, 25, 'n72520448@gmail.com', '2026-05-18 14:00:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 1, 'success');

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
(11, 'สุกานดา  สมเสียง', '34 หมู่4 บ้านห้วยสีดา', 30, 4203, 420308, '42110', '0615282330', 'sukandasomsiang543@gmail.com', '$2y$12$CLzABkWSwmKBqC1xVcZSb.M.4SpGsRn2rnWuYo0hwudOmN3hL6V.m', '2025-11-09 14:51:22', 1, '782093', '2026-05-11 16:44:24'),
(13, 'สกุลรัตน์ บุผู', 'โนสววค์ 109/50', 32, 4406, 440608, '44130', '0630103946', 'sakunrat2196@gmail.com', '$2y$12$mUvjy0nhOaeguYCKL/liDuz9qeb2L1JtturTPG/RQbUPKcWaKyDLq', '2025-11-17 08:50:39', 1, NULL, NULL),
(14, 'ธีรภัทร์ ชุมคำ', 'ป่าไม้งาม', 27, 3901, 390114, '39000', '0639469205', 'Tiraphat@gmail.com', '$2y$12$MU58.N.L18x4awQp6q4KK.f7RSWm1SPUtQgOhHKy023PT69.8sC9S', '2025-11-18 10:53:50', 1, NULL, NULL),
(19, 'ธนพร แก้วมีสี', 'บ้านหนองผักก้าม', 30, 4201, 420101, '42000', '0650378320', 'thanaporn282548@gmail.com', '$2y$12$utljdRO1m0LqeUMylvS1ceOryn.pGcFxL2W/7tVIz2BIvuadE2WC2', '2025-11-28 13:33:14', 1, NULL, NULL),
(22, 'หนึ่งเดียว', 'บ้านดอนพยอม 74 หมู่4', 32, 4406, 440608, '44130', '0651078576', 'sb6640248131@lru.ac.th', '$2y$12$nNSJ3wLrElSmKqRz18akwO/OHrym.Zzg3o/pTWLfsLJVP8gHqx9Ui', '2026-01-27 01:20:24', 1, NULL, NULL),
(24, 'สุดหล่อของแม่ค้า', 'เมี่ยง 55/5 หมู่5', 1, 1007, 100704, '10330', '0651078576', 'sb6640248118@lru.ac.th', '$2y$12$W7omM7Vsp3Tmd3RY/IeJLuUe9j2xTFhTdA6w8YUXKBkPIVjmuqfbW', '2026-01-28 18:01:59', 1, NULL, NULL),
(25, 'ทดสอบ', 'บ้านดอนพยอม', 32, 4406, 440608, '44130', '0651078576', 'n72520448@gmail.com', '$2y$12$/B3j.I642eqiD8k8SGeXI.iwqr6Vy0nS3QpApzWq1j/mY6GhAl0.O', '2026-01-29 16:15:40', 1, NULL, NULL),
(26, 'sukanda  somsiang', '34', 30, 4203, 420308, '42110', '0615282330', 'sb6640248123@lru.ac.th', '$2y$12$sw/Thr4UyVCJSubogaWZou6O7tymns32HWxEJvgX9pFsLtSwaQSuK', '2026-01-30 19:08:16', 1, NULL, NULL),
(27, 'Natanon Kongsuk', 'บ้านหนองผักกล้าม, 5 541', 30, 4201, 420102, '42000', '0857397511', 'natanon200404@gmail.com', '$2y$12$PzdkJ.H.Sbmnk7JewPeew.noyd5az8J0eA8gIYZU2y2u/DjoZ1a76', '2026-02-02 13:02:08', 1, NULL, NULL),
(28, 'นนทวัฒน์ พูลกลาง', 'บุญทัน', 27, 3902, 390207, '22222', '0825741700', 'nonthawatphunklang@gmail.com', '$2y$12$IxiXgv9uo6o7stndNSTTtOoSqkSTMBVw3H3TTt1PhqQkK9AAsb75S', '2026-02-02 13:20:06', 1, NULL, NULL),
(29, 'Phuriphat Srimongkon', 'บ.ท่าวังแคน 6 หมู่ 9', 30, 4201, 420113, '42100', '0923737194', 'dedee30520@gmail.com', '$2y$12$RPNNRub5RctWDhW1v/AeWe792GukDunLVbMQndAYEMOw6ZdS.JIxK', '2026-02-02 13:49:47', 1, NULL, NULL),
(30, 'ภวัต อยู่ภักดี', 'หนองหิน 49 หมู่1', 30, 4214, 421402, '42190', '0659711449', 'lolnotgt@gmail.com', '$2y$12$4RKtZyXgZW89rNHKo0Qyce0XcCFCKKhgQzWCnx2aC.t8hDdGF5.E6', '2026-02-06 13:54:02', 0, NULL, NULL),
(32, 'อินทิรา ไชโสดา', '1 บ่านโป่งป่าติ้ว', 30, 4201, 420104, '42000', '0840878100', 'sb6640248128@lru.ac.th', '$2y$10$p5HI9kh9.7E1zqBh6rFXoOJQkjrvnhSLFirMjL3DnXiFiV8nEorES', '2026-05-11 16:47:02', 1, NULL, NULL);

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
(71, 'ORD202604280755482571FC', NULL, 'เฟิร์น', '0000000000', '', 'pickup', '2026-04-28 15:00:00', 'completed', '2026-04-28 14:55:47', NULL, 50.00),
(72, 'ORD202604280757258E7CF3', NULL, 'สุกานดา  สมเสียง', '0634425813', '', 'pickup', '2026-04-28 15:00:00', 'completed', '2026-04-28 14:57:25', NULL, 1500.00),
(73, 'ORD202604280759323EFD51', NULL, 'สุกานดา  สมเสียง', '0634425813', '', 'pickup', '2026-04-28 17:00:00', 'rejected', '2026-04-28 14:59:32', 'hhh', 50.00),
(74, 'ORD20260429140240AB22DF', 11, 'สุกานดา  สมเสียง', '0615282330', '', 'pickup', '2026-04-29 17:00:00', 'completed', '2026-04-29 21:02:42', NULL, 100.00),
(75, 'ORD20260429140410D171B2', 11, 'สุกานดา  สมเสียง', '0615282330', '', 'pickup', '2026-04-30 13:00:00', 'completed', '2026-04-29 21:04:12', NULL, 100.00),
(76, 'ORD20260429153346E914B8', 11, 'สุกานดา  สมเสียง', '0615282330', '', 'pickup', '2026-04-29 10:00:00', 'completed', '2026-04-29 22:33:49', NULL, 250.00),
(78, 'ORD202605071205510588A5', NULL, 'test', '0615282330', '', 'pickup', '2026-05-07 10:00:00', 'completed', '2026-05-07 19:05:53', NULL, 50.00),
(79, 'ORD20260507121135E2761A', NULL, 'เฟิร์น', '0615282330', '', 'pickup', '2026-05-08 15:00:00', 'rejected', '2026-05-07 19:11:37', 'นน', 50.00),
(80, 'ORD20260507121938D0B475', NULL, 'สุกานดา  สมเสียง', '0615282330', '', 'pickup', '2026-05-08 08:30:00', 'completed', '2026-05-07 19:19:39', NULL, 320.00),
(81, 'ORD202605071436286D8106', NULL, 'เฟิร์น', 'ffffff', '', 'pickup', '2026-05-07 10:00:00', 'completed', '2026-05-07 21:36:30', NULL, 50.00),
(82, 'ORD20260507143957AD369B', NULL, 'เฟิร์น', '0615282330', 'ffffff', 'delivery', '2026-05-08 13:00:00', 'completed', '2026-05-07 21:39:58', NULL, 500.00),
(83, 'ORD2026050805455694DA7B', NULL, 'sukanda  somsiang', 'กำำำำ', 'บ้านน้อย', 'delivery', '2026-05-08 13:00:00', 'completed', '2026-05-08 12:45:56', NULL, 3270.00),
(84, 'ORD202605081702153F2226', NULL, 'น้องป๊อบ', '0615282330', 'แก่งคุดคู้', 'delivery', '2026-05-09 10:00:00', 'completed', '2026-05-09 00:02:15', NULL, 1950.00),
(85, 'ORD20260509022654096D33', NULL, 'น้องป๊อบ', '0815282330', 'แก่งคุดคู้', 'delivery', '2026-05-10 10:00:00', 'completed', '2026-05-09 09:26:55', NULL, 1680.00),
(86, 'ORD202605090228557B7620', NULL, 'สุกานดา  สมเสียง', '0615282330', 'กกกกกกกกกก', 'delivery', '2026-05-10 13:00:00', 'completed', '2026-05-09 09:28:55', NULL, 500.00),
(87, 'ORD260509C814', NULL, 'test', '0000000000', '', 'pickup', '2026-05-09 13:00:00', 'completed', '2026-05-09 12:56:12', NULL, 100.00),
(88, 'ORD260509C754', NULL, 'test', '0615282330', 'fffff', 'delivery', '2026-05-10 10:00:00', 'completed', '2026-05-09 14:33:29', NULL, 1800.00),
(89, 'ORD2605099738', 11, 'สุกานดา  สมเสียง', '0615282330', '', 'pickup', '2026-05-10 10:00:00', 'completed', '2026-05-09 15:01:29', NULL, 170.00),
(90, 'ORD260509C425', 11, 'สุกานดา  สมเสียง', '0615282330', '10 โมง จะเข้าไปเอานะคะ', 'pickup', '2026-05-10 10:00:00', 'completed', '2026-05-09 15:50:16', NULL, 1820.00),
(91, 'ORD2605091C1C', 11, 'สุกานดา  สมเสียง', '0615282330', 'บ้านน้อยซอย 5 ขับเข้ามา 200 เมตร บ้านหลังสีขาว', 'delivery', '2026-05-10 13:00:00', 'completed', '2026-05-09 21:05:42', NULL, 660.00),
(92, 'ORD2605091012', 11, 'สุกานดา  สมเสียง', '0615282330', '', 'pickup', '2026-05-10 10:00:00', 'completed', '2026-05-09 21:32:19', NULL, 3690.00),
(93, 'ORD260509128A', 11, 'สุกานดา  สมเสียง', '0615282330', '', 'pickup', '2026-05-10 10:00:00', 'completed', '2026-05-09 21:55:03', NULL, 250.00),
(94, 'ORD260509C410', 11, 'สุกานดา  สมเสียง', '0615282330', '', 'pickup', '2026-05-10 13:00:00', 'completed', '2026-05-09 21:55:35', NULL, 250.00),
(95, 'ORD2605108FC1', NULL, 'นาโน', '0919994445', '', 'pickup', '2026-05-11 10:00:00', 'completed', '2026-05-11 00:15:49', NULL, 50.00),
(96, 'ORD2605109F30', 11, 'สุกานดา  สมเสียง', '0615282330', '', 'pickup', '2026-05-11 13:00:00', 'completed', '2026-05-11 00:55:55', NULL, 1500.00),
(97, 'ORD260510C56F', 11, 'สุกานดา  สมเสียง', '0615282330', '', 'pickup', '2026-05-11 10:00:00', 'completed', '2026-05-11 01:03:02', NULL, 1800.00),
(98, 'ORD2605113FC5', NULL, 'เฟิร์น', '0815282330', '', 'pickup', '2026-05-12 13:00:00', 'pending', '2026-05-11 10:25:36', NULL, 170.00),
(99, 'ORD26051187B6', NULL, 'test', '0000000000', '', 'pickup', '2026-05-12 10:00:00', 'pending', '2026-05-11 10:53:57', NULL, 250.00),
(100, 'ORD260511F89F', NULL, 'เตอร์', '0815282330', 'บ้านน้อย', 'delivery', '2026-05-14 10:00:00', 'pending', '2026-05-11 11:04:01', NULL, 1500.00),
(101, 'ORD26051120C5', 11, 'สุกานดา  สมเสียง', '0615282330', '', 'pickup', '2026-05-12 13:00:00', 'pending', '2026-05-11 13:04:13', NULL, 50.00),
(102, 'ORD2605111582', 11, 'สุกานดา  สมเสียง', '0615282330', 'บ้านน้อย', 'delivery', '2026-05-12 10:00:00', 'pending', '2026-05-11 13:07:35', NULL, 1500.00),
(103, 'ORD2605112EF5', 11, 'สุกานดา  สมเสียง', '0615282330', 'เชียงคาน ซอย 17', 'delivery', '2026-05-12 13:00:00', 'pending', '2026-05-11 13:15:14', NULL, 2320.00),
(104, 'ORD2605110032', 11, 'สุกานดา  สมเสียง', '0615282330', 'เชียงคาน ซอย 17', 'delivery', '2026-05-12 10:00:00', 'pending', '2026-05-11 13:22:28', NULL, 2290.00),
(105, 'ORD2605111772', 11, 'สุกานดา  สมเสียง', '0615282330', '', 'pickup', '2026-05-12 13:00:00', 'rejected', '2026-05-11 15:24:40', 'หมด', 100.00),
(106, 'ORD2605113582', 11, 'สุกานดา  สมเสียง', '0615282330', 'บ้านน้อย', 'delivery', '2026-05-12 17:00:00', 'completed', '2026-05-11 15:29:42', NULL, 1500.00),
(107, 'ORD2605116E83', 1, 'หนึ่งเดียว เทียกสีบุญ', '0651078576', '', 'pickup', '2026-05-12 13:00:00', 'approved', '2026-05-11 16:36:07', NULL, 110.00),
(108, 'ORD260511C6F8', 1, 'หนึ่งเดียว เทียกสีบุญ', '0651078576', 'ทดสอบนะ5555', 'delivery', '2026-05-12 18:00:00', 'completed', '2026-05-11 17:25:39', NULL, 500.00),
(109, 'ORD260512AD7A', 11, 'สุกานดา  สมเสียง', '0615282330', '', 'pickup', '2026-05-13 10:00:00', 'pending', '2026-05-12 11:44:13', NULL, 600.00),
(110, 'ORD260512C493', NULL, 'หนึ่งเดียว เทียกสีบุญ', '0651078576', 'มหาสารคาม', 'pickup', '2026-05-13 10:00:00', 'pending', '2026-05-12 11:51:34', NULL, 700.00),
(111, 'ORD26051258B2', NULL, 'ป๊อบ', '0615282330', '', 'pickup', '2026-05-13 17:00:00', 'pending', '2026-05-12 11:51:48', NULL, 70.00),
(112, 'ORD2605129D86', 11, 'สุกานดา  สมเสียง', '0615282330', 'แก่งคุดคู้', 'delivery', '2026-05-12 13:00:00', 'completed', '2026-05-12 12:32:02', NULL, 2270.00),
(113, 'ORD260512BCC7', NULL, 'น้องป๊อบ', '0615282330', 'บ้าบอุมุง', 'delivery', '2026-05-13 16:00:00', 'pending', '2026-05-12 18:24:58', NULL, 1550.00),
(114, 'ORD260513D339', NULL, 'กนกพร', '0815282330', '', 'pickup', '2026-05-14 13:00:00', 'pending', '2026-05-13 10:21:53', NULL, 250.00),
(115, 'ORD2605132558', NULL, 'มะกอก', '0815282330', '', 'pickup', '2026-05-14 10:00:00', 'pending', '2026-05-13 10:56:05', NULL, 100.00),
(116, 'ORD2605133592', NULL, 'สุกานดา  สมเสียง', '0815282330', 'บ้านน้อย', 'delivery', '2026-05-14 13:00:00', 'pending', '2026-05-13 11:40:17', NULL, 2320.00),
(117, 'ORD2605132348', NULL, 'นาโน', '0919994445', '', 'pickup', '2026-05-14 10:00:00', 'pending', '2026-05-13 15:56:34', NULL, 250.00),
(118, 'ORD260513BA1D', NULL, 'สมจิตร อยู่ยง', '0945434112', '', 'pickup', '2026-05-14 13:06:00', 'pending', '2026-05-13 17:56:13', NULL, 350.00),
(119, 'ORD2605147880', NULL, 'สกาย', '0854534112', '', 'pickup', '2026-05-14 13:00:00', 'pending', '2026-05-14 10:38:50', NULL, 270.00),
(120, 'ORD260514B03D', NULL, 'จุ๋ม', '0634425813', '', 'pickup', '2026-05-15 13:00:00', 'completed', '2026-05-14 10:40:51', NULL, 150.00),
(121, 'ORD260514A5FA', NULL, 'แบม', '0615282330', 'บ้านน้อย', 'delivery', '2026-05-15 13:00:00', 'completed', '2026-05-14 11:20:32', NULL, 1500.00),
(122, 'ORD2605158600', NULL, 'พิมพ์ฤดี  สมเสียง', '0908397762', 'บ้านน้อย ซอย 5', 'delivery', '2026-05-16 13:00:00', 'completed', '2026-05-15 11:49:19', NULL, 750.00),
(123, 'ORD2605159088', NULL, 'น้องป๊อบ', '0615282330', '', 'pickup', '2026-05-16 13:00:00', 'pending', '2026-05-15 12:20:50', NULL, 100.00),
(124, 'ORD26051552BD', NULL, 'น้องป๊อบ', '0854534112', '', 'pickup', '2026-05-16 13:00:00', 'pending', '2026-05-15 13:30:28', NULL, 450.00),
(125, 'ORD260515450F', NULL, 'ปป', '0987654321', '', 'pickup', '2026-05-16 13:00:00', 'completed', '2026-05-15 14:53:36', NULL, 100.00),
(325, 'ORD260517C18B', NULL, 'น้องงอแง', '0908397762', 'ห่อกระดาษให้ด้วยนะคะ', 'pickup', '2026-05-18 10:00:00', 'completed', '2026-05-17 21:35:19', NULL, 500.00),
(326, 'ORD26051865F6', NULL, 'นาโน', '0919994445', 'บ้านน้อย', 'delivery', '2026-05-18 14:50:00', 'completed', '2026-05-18 14:34:11', NULL, 640.00);

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
(92, 71, 9, 1, 50.00, 'กล้วยฉาบ'),
(93, 72, 10, 1, 1500.00, 'วัว'),
(94, 73, 7, 1, 50.00, 'มะม่วงเขียวสามรส'),
(95, 74, 15, 1, 100.00, 'มะขามแช่อิ่ม'),
(96, 75, 15, 1, 100.00, 'มะขามแช่อิ่ม'),
(97, 76, 14, 1, 250.00, 'น้ำผึ้ง'),
(99, 78, 5, 1, 50.00, 'มะยงชิด'),
(100, 79, 13, 1, 50.00, 'ส้มโอ'),
(101, 80, 14, 1, 250.00, 'น้ำผึ้ง'),
(102, 80, 16, 1, 70.00, 'เผือกทอด'),
(103, 81, 9, 1, 50.00, 'กล้วยฉาบ'),
(104, 82, 9, 10, 50.00, 'กล้วยฉาบ'),
(105, 83, 10, 2, 1500.00, 'วัว'),
(106, 83, 8, 3, 40.00, 'ไข่ไก่'),
(107, 83, 5, 1, 50.00, 'มะยงชิด'),
(108, 83, 15, 1, 100.00, 'มะขามแช่อิ่ม'),
(109, 84, 15, 1, 100.00, 'มะขามแช่อิ่ม'),
(110, 84, 14, 1, 250.00, 'น้ำผึ้ง'),
(111, 84, 5, 1, 50.00, 'มะยงชิด'),
(112, 84, 9, 1, 50.00, 'กล้วยฉาบ'),
(113, 84, 10, 1, 1500.00, 'วัว'),
(114, 85, 10, 1, 1500.00, 'วัว'),
(115, 85, 5, 1, 50.00, 'มะยงชิด'),
(116, 85, 13, 1, 50.00, 'ส้มโอ'),
(117, 85, 8, 2, 40.00, 'ไข่ไก่'),
(118, 86, 15, 5, 100.00, 'มะขามแช่อิ่ม'),
(119, 87, 9, 1, 50.00, 'กล้วยฉาบ'),
(120, 87, 5, 1, 50.00, 'มะยงชิด'),
(121, 88, 10, 1, 1500.00, 'วัว'),
(122, 88, 9, 1, 50.00, 'กล้วยฉาบ'),
(123, 88, 14, 1, 250.00, 'น้ำผึ้ง'),
(124, 89, 15, 1, 100.00, 'มะขามแช่อิ่ม'),
(125, 89, 16, 1, 70.00, 'เผือกทอด'),
(126, 90, 16, 1, 70.00, 'เผือกทอด'),
(127, 90, 14, 1, 250.00, 'น้ำผึ้ง'),
(128, 90, 10, 1, 1500.00, 'วัว'),
(129, 91, 16, 1, 70.00, 'เผือกทอด'),
(130, 91, 14, 1, 250.00, 'น้ำผึ้ง'),
(131, 91, 17, 1, 100.00, 'มะพร้าวแก้ว'),
(132, 91, 13, 1, 50.00, 'ส้มโอ'),
(133, 91, 9, 1, 50.00, 'กล้วยฉาบ'),
(134, 91, 5, 1, 50.00, 'มะยงชิด'),
(135, 91, 7, 1, 50.00, 'มะม่วงเขียวสามรส'),
(136, 91, 8, 1, 40.00, 'ไข่ไก่'),
(137, 92, 10, 2, 1500.00, 'วัว'),
(138, 92, 17, 1, 100.00, 'มะพร้าวแก้ว'),
(139, 92, 14, 1, 250.00, 'น้ำผึ้ง'),
(140, 92, 13, 4, 50.00, 'ส้มโอ'),
(141, 92, 9, 1, 50.00, 'กล้วยฉาบ'),
(142, 92, 7, 1, 50.00, 'มะม่วงเขียวสามรส'),
(143, 92, 8, 1, 40.00, 'ไข่ไก่'),
(144, 93, 14, 1, 250.00, 'น้ำผึ้ง'),
(145, 94, 14, 1, 250.00, 'น้ำผึ้ง'),
(146, 95, 5, 1, 50.00, 'มะยงชิด'),
(147, 96, 10, 1, 1500.00, 'วัว'),
(148, 97, 10, 1, 1500.00, 'วัว'),
(149, 97, 13, 1, 50.00, 'ส้มโอ'),
(150, 97, 11, 1, 60.00, 'ปลาตะเพียนสด'),
(151, 97, 9, 1, 50.00, 'กล้วยฉาบ'),
(152, 97, 5, 1, 50.00, 'มะยงชิด'),
(153, 97, 7, 1, 50.00, 'มะม่วงเขียวสามรส'),
(154, 97, 8, 1, 40.00, 'ไข่ไก่'),
(155, 98, 15, 1, 100.00, 'มะขามแช่อิ่ม'),
(156, 98, 16, 1, 70.00, 'เผือกทอด'),
(157, 99, 14, 1, 250.00, 'น้ำผึ้ง'),
(158, 100, 10, 1, 1500.00, 'วัว'),
(159, 101, 9, 1, 50.00, 'กล้วยฉาบ'),
(160, 102, 10, 1, 1500.00, 'วัว'),
(161, 103, 15, 1, 100.00, 'มะขามแช่อิ่ม'),
(162, 103, 16, 1, 70.00, 'เผือกทอด'),
(163, 103, 17, 1, 100.00, 'มะพร้าวแก้ว'),
(164, 103, 14, 1, 250.00, 'น้ำผึ้ง'),
(165, 103, 9, 1, 50.00, 'กล้วยฉาบ'),
(166, 103, 10, 1, 1500.00, 'วัว'),
(167, 103, 11, 1, 60.00, 'ปลาตะเพียนสด'),
(168, 103, 13, 1, 50.00, 'ส้มโอ'),
(169, 103, 5, 1, 50.00, 'มะยงชิด'),
(170, 103, 7, 1, 50.00, 'มะม่วงเขียวสามรส'),
(171, 103, 8, 1, 40.00, 'ไข่ไก่'),
(172, 104, 15, 1, 100.00, 'มะขามแช่อิ่ม'),
(173, 104, 14, 1, 250.00, 'น้ำผึ้ง'),
(174, 104, 10, 1, 1500.00, 'วัว'),
(175, 104, 11, 1, 60.00, 'ปลาตะเพียนสด'),
(176, 104, 13, 1, 50.00, 'ส้มโอ'),
(177, 104, 9, 2, 50.00, 'กล้วยฉาบ'),
(178, 104, 5, 2, 50.00, 'มะยงชิด'),
(179, 104, 7, 1, 50.00, 'มะม่วงเขียวสามรส'),
(180, 104, 8, 2, 40.00, 'ไข่ไก่'),
(181, 105, 15, 1, 100.00, 'มะขามแช่อิ่ม'),
(182, 106, 10, 1, 1500.00, 'วัว'),
(183, 107, 13, 1, 50.00, 'ส้มโอ'),
(184, 107, 11, 1, 60.00, 'ปลาตะเพียนสด'),
(185, 108, 17, 5, 100.00, 'มะพร้าวแก้ว'),
(186, 109, 14, 2, 250.00, 'น้ำผึ้ง'),
(187, 109, 15, 1, 100.00, 'มะขามแช่อิ่ม'),
(188, 110, 17, 7, 100.00, 'มะพร้าวแก้ว'),
(189, 111, 16, 1, 70.00, 'เผือกทอด'),
(190, 112, 5, 1, 50.00, 'มะยงชิด'),
(191, 112, 7, 1, 50.00, 'มะม่วงเขียวสามรส'),
(192, 112, 8, 1, 40.00, 'ไข่ไก่'),
(193, 112, 9, 1, 50.00, 'กล้วยฉาบ'),
(194, 112, 10, 1, 1500.00, 'วัว'),
(195, 112, 11, 1, 60.00, 'ปลาตะเพียนสด'),
(196, 112, 14, 1, 250.00, 'น้ำผึ้ง'),
(197, 112, 15, 1, 100.00, 'มะขามแช่อิ่ม'),
(198, 112, 16, 1, 70.00, 'เผือกทอด'),
(199, 112, 17, 1, 100.00, 'มะพร้าวแก้ว'),
(200, 113, 7, 1, 50.00, 'มะม่วงเขียวสามรส'),
(201, 113, 10, 1, 1500.00, 'วัว'),
(202, 114, 14, 1, 250.00, 'น้ำผึ้ง'),
(203, 115, 15, 1, 100.00, 'มะขามแช่อิ่ม'),
(204, 116, 14, 1, 250.00, 'น้ำผึ้ง'),
(205, 116, 15, 1, 100.00, 'มะขามแช่อิ่ม'),
(206, 116, 16, 1, 70.00, 'เผือกทอด'),
(207, 116, 17, 1, 100.00, 'มะพร้าวแก้ว'),
(208, 116, 13, 1, 50.00, 'ส้มโอ'),
(209, 116, 11, 1, 60.00, 'ปลาตะเพียนสด'),
(210, 116, 10, 1, 1500.00, 'วัว'),
(211, 116, 9, 1, 50.00, 'กล้วยฉาบ'),
(212, 116, 5, 1, 50.00, 'มะยงชิด'),
(213, 116, 7, 1, 50.00, 'มะม่วงเขียวสามรส'),
(214, 116, 8, 1, 40.00, 'ไข่ไก่'),
(215, 117, 14, 1, 250.00, 'น้ำผึ้ง'),
(216, 118, 11, 1, 60.00, 'ปลาตะเพียนสด'),
(217, 118, 14, 1, 250.00, 'น้ำผึ้ง'),
(218, 118, 8, 1, 40.00, 'ไข่ไก่'),
(219, 119, 16, 1, 70.00, 'เผือกทอด'),
(220, 119, 17, 2, 100.00, 'มะพร้าวแก้ว'),
(221, 120, 15, 1, 100.00, 'มะขามแช่อิ่ม'),
(222, 120, 9, 1, 50.00, 'กล้วยฉาบ'),
(223, 121, 10, 1, 1500.00, 'วัว'),
(224, 122, 9, 3, 50.00, 'กล้วยฉาบ'),
(225, 122, 7, 5, 50.00, 'มะม่วงเขียวสามรส'),
(226, 122, 8, 3, 40.00, 'ไข่ไก่'),
(227, 122, 13, 1, 50.00, 'ส้มโอ'),
(228, 122, 11, 3, 60.00, 'ปลาตะเพียนสด'),
(229, 123, 15, 1, 100.00, 'มะขามแช่อิ่ม'),
(230, 124, 17, 2, 100.00, 'มะพร้าวแก้ว'),
(231, 124, 14, 1, 250.00, 'น้ำผึ้ง'),
(232, 125, 7, 1, 50.00, 'มะม่วงเขียวสามรส'),
(233, 125, 9, 1, 50.00, 'กล้วยฉาบ'),
(404, 325, 7, 10, 50.00, 'มะม่วงเขียวสามรส'),
(405, 326, 7, 1, 50.00, 'มะม่วงเขียวสามรส'),
(406, 326, 5, 1, 50.00, 'มะยงชิด'),
(407, 326, 8, 1, 40.00, 'ไข่ไก่'),
(408, 326, 15, 1, 50.00, 'มะขามแช่อิ่ม'),
(409, 326, 14, 1, 250.00, 'น้ำผึ้ง'),
(410, 326, 13, 1, 50.00, 'ส้มโอ'),
(411, 326, 9, 3, 50.00, 'กล้วยฉาบ');

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
(10, 'วัว', 'สัตว์', 1500.00, 'ตัว', 1, 'inactive', '1770460756_698716544187c.jpg', 'วัวอายุ 3เดือน กำลังโตเพศเมียเหมาะนำไปเลี้ยงทำแม่พันธ์ุ'),
(11, 'ปลาตะเพียนสด', 'ปลา', 60.00, 'แพ็ค', 1, 'inactive', '1770466625_69872d411db76.jpg', 'ปลาตะเพียนสด พึ่งตกจากบ่อตอนเช้า ตัวใหญ่เล็กคละไซต์'),
(13, 'ส้มโอ', 'ผลไม้สด', 50.00, 'กิโลกรัม', 1, 'active', '1771002213_698f59659e9ee.jpg', 'ส้มโอสีทองหวานอมเปรี้ยวเก็บสดๆจากสวน'),
(14, 'น้ำผึ้ง', 'น้ำผึ้ง', 250.00, 'ขวด', 1, 'active', '1771002446_698f5a4e1b5b5.jpg', 'สดใหม่จากรัง! น้ำผึ้งแท้คุณภาพดีที่เราเก็บเองกับมือในช่วงเดือน 5 ซึ่งเป็นช่วงที่น้ำผึ้งมีความเข้มข้นที่สุด ความชื้นน้อยที่สุด ทำให้เก็บไว้ได้นานโดยไม่เสียรสชาติ รสหวานเข้มข้น หอมกลิ่นเกสรดอกไม้ป่าธรรมชาติแท้ๆ\r\n\r\n✅ ของแท้แน่นอน ตรวจสอบได้\r\n\r\n✅ ราคามิตรภาพ เพราะส่งตรงจากคนเลี้ยง\r\n\r\n✅ บรรจุขวดสะอาด ปลอดภัย\r\n\r\n“หวานแท้ไม่หลอกลวง ลองแล้วจะรู้ว่าน้ำผึ้งดีๆ เป็นยังไง”'),
(15, 'มะขามแช่อิ่ม', 'ผลไม้แปรรูป', 50.00, 'กล่อง', 0, 'active', '1771002590_698f5adedd1a0.jpg', 'สัมผัสความกรอบสนั่น รสชาติเปรี้ยวอมหวานที่ลงตัวที่สุด! คัดเฉพาะมะขามฝักใหญ่ เนื้อแน่น นำมาผ่านกระบวนการแช่อิ่มแบบโบราณจนเข้าเนื้อ สะอาด ปลอดภัย ไร้สารกันบูด'),
(16, 'เผือกทอด', 'อาหารแปรรูป', 70.00, 'ห่อ', 1, 'inactive', '1771862703_699c7aaf125c9.jpg', 'อร่อยสะอาด ปลอดภัย'),
(17, 'มะพร้าวแก้ว', 'ผลไม้แปรรูป', 100.00, 'กล่อง', 1, 'inactive', '1778253898_69fe004ad58f8.jpg', 'มะพร้าวแก้วเชียงคาน ทำใหม่สด สะอาด กลิ่นหอม เกรด A หวานพอดี เนื้อนุ่ม รับประกันความอร่อย ทำจากมะพร้าวเชียงคาน 100% \r\nต้องลองที่สวนลุงเผือก ราคาดีมาก หมดแล้วหมดเลย');

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
(13, 'admin', 'n72520448@gmail.com', '$2y$12$02BRZvRY.ZbplJkL53RRw.CXfG0viheNszyIEix31OYGfBg0H74nG', 'sub', '2026-02-12 17:26:13');

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
  MODIFY `bookings_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=273;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `courses_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `course_comments`
--
ALTER TABLE `course_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `course_rating`
--
ALTER TABLE `course_rating`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=200;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=327;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=412;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

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
