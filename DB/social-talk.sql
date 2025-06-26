-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 26, 2025 at 01:16 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `social-talk`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`comment_id`, `post_id`, `user_id`, `content`, `created_at`) VALUES
(1, 3, 6, 'test comment asdfds fsdf sdsdf f', '2025-06-24 04:13:05'),
(2, 3, 6, 'another comment', '2025-06-24 04:25:14'),
(3, 33, 6, 'break nen', '2025-06-26 05:31:23'),
(4, 3, 6, 'another comment', '2025-06-26 05:45:47'),
(5, 3, 6, 'sadfdsf', '2025-06-26 05:47:51');

-- --------------------------------------------------------

--
-- Table structure for table `education`
--

CREATE TABLE `education` (
  `education_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `institution_name` varchar(255) NOT NULL,
  `degree` varchar(100) NOT NULL,
  `field_of_study` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `friendships`
--

CREATE TABLE `friendships` (
  `friendship_id` int(11) NOT NULL,
  `user1_id` int(11) NOT NULL,
  `user2_id` int(11) NOT NULL,
  `status` enum('pending','accepted','declined') NOT NULL,
  `action_user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `friendships`
--

INSERT INTO `friendships` (`friendship_id`, `user1_id`, `user2_id`, `status`, `action_user_id`, `created_at`, `updated_at`) VALUES
(1, 4, 6, 'accepted', 4, '2025-05-31 06:06:12', NULL),
(2, 4, 8, 'accepted', 8, '2025-05-31 06:06:42', NULL),
(3, 6, 9, 'accepted', 9, '2025-05-31 06:06:58', NULL),
(4, 9, 11, 'accepted', 11, '2025-05-31 06:07:11', NULL),
(5, 6, 8, 'pending', 6, '2025-06-26 05:50:24', NULL),
(9, 6, 11, 'accepted', 11, '2025-06-26 06:52:26', '2025-06-26 12:53:15'),
(10, 6, 2, 'pending', 6, '2025-06-26 06:52:26', NULL),
(11, 6, 5, 'pending', 6, '2025-06-26 06:52:27', NULL),
(12, 6, 13, 'pending', 6, '2025-06-26 06:52:28', NULL),
(13, 6, 3, 'pending', 6, '2025-06-26 06:52:33', NULL),
(14, 6, 12, 'pending', 6, '2025-06-26 06:52:37', NULL),
(15, 6, 14, 'pending', 6, '2025-06-26 06:52:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `like_id` int(11) NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `comment_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`like_id`, `post_id`, `comment_id`, `user_id`, `created_at`) VALUES
(2, 3, NULL, 4, '2025-06-24 04:12:22'),
(3, 5, NULL, 6, '2025-06-24 05:20:54'),
(9, 10, NULL, 6, '2025-06-24 05:35:03'),
(11, 26, NULL, 6, '2025-06-24 05:42:41'),
(12, 32, NULL, 3, '2025-06-24 05:48:35'),
(13, 27, NULL, 6, '2025-06-24 05:53:49'),
(15, 31, NULL, 6, '2025-06-26 05:14:20'),
(16, 33, NULL, 6, '2025-06-26 05:14:22'),
(17, 30, NULL, 6, '2025-06-26 05:14:34'),
(19, 29, NULL, 6, '2025-06-26 05:19:54'),
(20, 28, NULL, 6, '2025-06-26 05:19:57'),
(23, 33, NULL, 11, '2025-06-26 07:11:21');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('friend_request','like','comment','message') NOT NULL,
  `source_id` int(11) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `type`, `source_id`, `is_read`, `created_at`) VALUES
(1, 8, 'friend_request', 6, 0, '2025-06-26 05:50:24'),
(2, 6, 'friend_request', 3, 0, '2025-06-26 05:53:28'),
(3, 14, 'friend_request', 3, 0, '2025-06-26 05:53:35'),
(4, 11, 'friend_request', 6, 0, '2025-06-26 06:38:31'),
(6, 6, 'friend_request', 11, 0, '2025-06-26 06:51:10'),
(7, 11, 'friend_request', 6, 0, '2025-06-26 06:52:26'),
(8, 2, 'friend_request', 6, 0, '2025-06-26 06:52:26'),
(9, 5, 'friend_request', 6, 0, '2025-06-26 06:52:27'),
(10, 13, 'friend_request', 6, 0, '2025-06-26 06:52:28'),
(11, 3, 'friend_request', 6, 0, '2025-06-26 06:52:33'),
(12, 12, 'friend_request', 6, 0, '2025-06-26 06:52:37'),
(13, 14, 'friend_request', 6, 0, '2025-06-26 06:52:38'),
(14, 6, 'friend_request', 11, 0, '2025-06-26 06:53:15');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `images` varchar(255) DEFAULT NULL,
  `visibility` enum('public','friends','private') DEFAULT 'public',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`post_id`, `user_id`, `content`, `images`, `visibility`, `created_at`, `updated_at`) VALUES
(1, 5, 'test post number 1 for testing  out post in test post', '', 'public', '2025-05-31 05:02:43', '2025-05-31 05:02:43'),
(2, 5, 'Your JavaScript preview script is correct and unrelated to why images aren’t uploading. If the images are previewing fine but not getting uploaded, the issue is likely in your PHP file handling or form setup.', 'img_683a8e7de33063.21047537.jpg,img_683a8e7de35040.39753478.jpg', 'public', '2025-05-31 05:07:09', '2025-05-31 05:07:09'),
(3, 4, 'This is our social media.which name social-talk 123', 'img_683a9a5c7dbe51.33889234.webp', 'public', '2025-05-31 05:57:48', '2025-06-24 04:27:56'),
(4, 8, 'hi.\r\nwe are testing socialtalk.', '', 'public', '2025-05-31 05:59:57', '2025-05-31 05:59:57'),
(5, 6, 'This is our social media which name is social-talk', 'img_683a9af07ca231.58964211.jpg', 'public', '2025-05-31 06:00:16', '2025-05-31 06:00:16'),
(6, 9, 'sfdgfhgfjhgkiojklkm,lhfyhrdse34eretfdgfghjhkjlk;l\';l\'p[[[[[[[[[[[[[[', 'img_683a9b8749b1b8.87455655.png', 'public', '2025-05-31 06:02:47', '2025-05-31 06:02:47'),
(7, 8, 'Hulk is my favorite hero....rddfgfdg', 'img_683a9bdb956a51.37816438.png', 'public', '2025-05-31 06:04:11', '2025-05-31 06:04:11'),
(8, 11, 'mdjkgfgnmdxfkkkkkkkkkkkkkfgvfdgvn', 'img_683a9c13d50fc6.18568980.PNG', 'public', '2025-05-31 06:05:07', '2025-05-31 06:05:07'),
(9, 12, 'hiiiiii..\r\nHow are youuuuuuu???', '', 'public', '2025-05-31 06:08:06', '2025-05-31 06:08:06'),
(10, 9, 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'img_683a9cec656cf6.84256051.jpg,img_683a9cec657e68.24737651.jpg', 'public', '2025-05-31 06:08:44', '2025-05-31 06:08:44'),
(11, 12, 'Iron Man Tony Stark Wallpaper Discover more Avengers, Endgame, Infinity War, Iron Man, Marvel wallpaper.', 'img_683a9d10933f57.18723852.jpg', 'public', '2025-05-31 06:09:20', '2025-05-31 06:09:20'),
(12, 14, 'ম্যারিনেশনের জন্য লাগবে: খাসির মাংস ১ কেজি, আদাবাটা ২ টেবিল চামচ, রসুনবাটা ২ টেবিল চামচ, টক দই আধা কাপ, হলুদগুঁড়া আধা চা-চামচ, শর্ষের তেল ২ টেবিল চামচ, লবণ সামান্য।\r\n\r\nরান্নার জন্য লাগবে: পেঁয়াজ ৫টি (পাতলা কুচি করে কাটা), টমেটো ২টি (কুচি করা), হলুদগুঁড়া আধা চা-চামচ, মরিচগুঁড়া ১ চা-চামচ, ধনেগুঁড়া ১ টেবিল চামচ, আস্ত কাঁচা মরিচ ৫টি, শুকনা মরিচ ২টি, তেজপাতা ২টি, দারুচিনি ৪টি, এলাচি ৪টি, লবঙ্গ ৪টি, জিরা ১ চা-চামচ, গরমমসলার গুঁড়া আধা চা-চামচ, লবণ স্বাদমতো, চিনি ১ চা-চামচ, শর্ষের তেল আধা কাপ।', '', 'public', '2025-05-31 06:11:18', '2025-05-31 06:11:18'),
(13, 13, 'The Avengers are a team of superheroes in the Marvel Cinematic Universe (MCU) and Marvel Comics, known for their ability to work together to protect Earth from various threats.. :)', '', 'public', '2025-05-31 06:12:07', '2025-05-31 06:12:07'),
(14, 15, 'লাহোরে কাল পাকিস্তানের বিপক্ষে ম্যাচের পর নিজের আউট নিয়ে প্রশ্ন শুনতে হয়েছে বাংলাদেশ অধিনায়ক লিটনকে। মেজাজ হারানোতেই কি অমন আউট, পুরস্কার বিতরণী অনুষ্ঠানে এমন প্রশ্নের উত্তরে লিটনের উত্তর, ‘না, এমন নয়।’\r\n\r\nতবে এরপর যা বললেন তাতে স্পষ্টই বোঝা গেল লিটন ঠিকই মেজাজ হারিয়েছিলেন, ‘ক্রিকেটে আপনার বেসিক জিনিসগুলো করতে হবে। এটা (রান নেওয়া) তো বেসিক। এ মুহূর্তে আমরা মৌলিক কাজগুলো অনুসরণ করছি না। আপনি যদি ব্যাক টু ব্যাক দুটি উইকেট দেখেন, আমরা ২টি রান নিইনি। আমি এটিকে দোষ দিচ্ছি না। তবে আমাদের এদিকে মনোযোগ দিতে হবে।’', '', 'public', '2025-05-31 06:13:25', '2025-05-31 06:13:25'),
(15, 13, 'The founding Avengers assembling in what is considered one of the pivotal moments of the MCU.', 'img_683a9e19143ba3.06478839.jpg', 'public', '2025-05-31 06:13:45', '2025-05-31 06:13:45'),
(16, 15, 'jhuma tmi onk kisu paro.ami kisu e pari na', 'img_683aa45f42f3a5.81655229.webp,img_683aa45f430a81.13297674.jpg,img_683aa45f4315f4.60954913.jpg,img_683aa45f431e01.44877154.webp,img_683aa45f432597.78364715.webp', 'public', '2025-05-31 06:40:31', '2025-05-31 06:40:31'),
(26, 6, 'asdfdsfdsfdsf sdfsdf sdf sdf sdfsd fsdaf df', 'img_683bd49c903d16.04538418.jpg,img_683bd49c9052b5.39474413.jpg', 'public', '2025-06-01 04:18:36', '2025-06-01 04:18:36'),
(27, 9, 'how are youuuuuuuuuuuuuuuuuuuuu', 'img_683bd647eb6705.68329368.jpg,img_683bd647eb7b13.86455728.jpg', 'public', '2025-06-01 04:25:43', '2025-06-01 04:25:43'),
(28, 6, 'sdaf sdf sdaf sdf dsf sdf sdf sdfa sdaf df dsf sdf c', 'img_683bd6af355872.98810739.jpg', 'public', '2025-06-01 04:27:27', '2025-06-01 04:27:27'),
(29, 6, 'after many test found the error, we were outputting the php as JSON', 'img_683bd7236f4328.09993509.jpg,img_683bd7236f70b3.77899478.jpg', 'public', '2025-06-01 04:29:23', '2025-06-01 04:29:23'),
(30, 6, 'test post test post test post test post test post', 'img_685a1e2b4d90f8.18129524.jpg,img_685a1e2b4daa57.73428013.jpg', 'public', '2025-06-24 03:40:27', '2025-06-24 03:40:27'),
(31, 6, 'sdafgdsfg dsfgsd fsdf dsf sdf df', 'img_685a2c2f2e5f02.36210058.jpg', 'public', '2025-06-24 04:40:15', '2025-06-24 04:40:15'),
(32, 3, 'qwertrytruyuyiuyoiuoiuooooooooooo', '', 'public', '2025-06-24 05:48:28', '2025-06-24 05:48:28'),
(33, 6, 'test post for loightbox for ACI products', 'img_685cd70ff2e5c2.86305181.jpg,img_685cd70ff30907.93539857.jpg,img_685cd70ff315d6.43184703.jpg,img_685cd70ff320b2.93226736.jpg,img_685cd70ff32ac7.59665280.jpg,img_685cd70ff34119.54267540.jpg,img_685cd70ff34be8.17852506.jpg,img_685cd710031492.72260001.jpg', 'public', '2025-06-26 05:13:52', '2025-06-26 05:13:52');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `reported_post_id` int(11) DEFAULT NULL,
  `reported_user_id` int(11) DEFAULT NULL,
  `reason` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `last_active` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shares`
--

CREATE TABLE `shares` (
  `share_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` set('user','admin') NOT NULL DEFAULT 'user',
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `status` enum('active','banned','deleted') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `role`, `email_verified`, `verification_token`, `created_at`, `last_login`, `status`) VALUES
(2, 'abdulaziz', 'abdulazizkhan1997@gmail.com', '$2y$10$2Qolzx01EKsF9d1jdcrHluNt0NP5ZEnCvArG2h2qSCcesgRuyqeOC', 'user', 0, NULL, '2025-05-25 20:22:23', NULL, 'active'),
(3, 'abdulazizkhan', 'abdulazizkhan.web@gmail.com', '$2y$10$XHqs1BvOgHDnjV0uzg.W4eVOE.m9o5JbP9lxReePfmTEz240lk7pC', 'user', 0, NULL, '2025-05-25 21:39:42', NULL, 'active'),
(4, 'mamun', 'mamun@gmail.com', '$2y$10$lz.IOuDGwAcq1pAjbhr2wO5eS9NuqpSXq8mCfpqju5anY/eihVXNK', 'user', 0, NULL, '2025-05-26 05:14:18', NULL, 'active'),
(5, 'user1', 'user1@gmail.com', '$2y$10$WraNi.GPNRabflfMizqhAOZFB5H.//iUkW0y2LHOHPn2QAvc1HxTS', 'user', 0, NULL, '2025-05-31 03:56:22', NULL, 'active'),
(6, 'Jhuma', 'mj@gmail.com', '$2y$10$JkDCRUpORpv6SJf8reiySum7Em80Mo//JkY4ETluBWTijWwxvA2y.', 'user', 0, NULL, '2025-05-31 04:16:11', NULL, 'active'),
(8, 'AbdulAziz1', 'abdulazizkhan2023@gmail.com', '$2y$10$UhKeRk4CZy9SykkJR3p2hOPEbTAlPeMT2qlhDZ44A1Uyi7YrQ3a/.', 'user', 0, NULL, '2025-05-31 05:58:23', NULL, 'active'),
(9, 'sumi', 'bisew.tahminasumi@gmail.com', '$2y$10$9BCe3Tyd52KPo643.TIXN.962Bq97cSJmoD/8SjZObY0RMSuPQ.9m', 'user', 0, NULL, '2025-05-31 06:01:21', NULL, 'active'),
(11, 'Shamima Naznin', 'rune182013@gmail.com', '$2y$10$Ej0XrD06s6mVu7.Ihq0EJ.jh92FQjHap6KlsmnWMSZD4yUaQkUopO', 'user', 0, NULL, '2025-05-31 06:04:37', NULL, 'active'),
(12, 'Aak', 'Aak@gmail.com', '$2y$10$5/ylCRzKTgJd8XhHKB5bnOlts/7xfNPkj7Gw14fyot3Zf2HUQ.UDe', 'user', 0, NULL, '2025-05-31 06:05:58', NULL, 'active'),
(13, 'Tony', 'tony@gmail.com', '$2y$10$giCkUv7sQ8RiZjRhHOlgWe3RBlOsyGTgADUeUbODF.rhTaC/PPS4m', 'user', 0, NULL, '2025-05-31 06:10:25', NULL, 'active'),
(14, ' Bonolota', 'bn@gmail.com', '$2y$10$I2TjF1NdINzpFDlOpdOIdOnheVyo3pPadFTWEkSuZKjlO1/S60RyC', 'user', 0, NULL, '2025-05-31 06:10:50', NULL, 'active'),
(15, 'charu', 'ch@gmail.com', '$2y$10$wwHBiacCJF3RTk3.hzpCke5mmwDhuIYB1VGFyZ8KfhydZwfsL8Dle', 'user', 0, NULL, '2025-05-31 06:12:17', NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `user_profile`
--

CREATE TABLE `user_profile` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `address_line1` varchar(255) DEFAULT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_picture` varchar(512) DEFAULT NULL,
  `cover_photo` varchar(512) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profile`
--

INSERT INTO `user_profile` (`user_id`, `first_name`, `last_name`, `blood_group`, `country`, `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `phone_number`, `bio`, `profile_picture`, `cover_photo`, `date_of_birth`, `gender`, `created_at`, `updated_at`) VALUES
(6, '666', '666', 'A-', 'US', '1236', '1236', '1236', '1236', '1233', '6666666666', '12312312323', 'assets/contentimages/6/profile_6_1750736378.png', 'assets/contentimages/6/cover_6_1750736378.jpg', '2025-06-02', 'Male', '2025-06-15 06:38:50', '2025-06-24 03:39:38');

-- --------------------------------------------------------

--
-- Table structure for table `work_history`
--

CREATE TABLE `work_history` (
  `work_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `job_title` varchar(100) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `education`
--
ALTER TABLE `education`
  ADD PRIMARY KEY (`education_id`),
  ADD KEY `idx_education_user_id` (`user_id`);

--
-- Indexes for table `friendships`
--
ALTER TABLE `friendships`
  ADD PRIMARY KEY (`friendship_id`),
  ADD UNIQUE KEY `unique_friendship` (`user1_id`,`user2_id`),
  ADD KEY `user2_id` (`user2_id`),
  ADD KEY `action_user_id` (`action_user_id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`like_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `comment_id` (`comment_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `idx_messages_sender` (`sender_id`),
  ADD KEY `idx_messages_receiver` (`receiver_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `idx_posts_user` (`user_id`),
  ADD KEY `idx_posts_created` (`created_at`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `reporter_id` (`reporter_id`),
  ADD KEY `reported_post_id` (`reported_post_id`),
  ADD KEY `reported_user_id` (`reported_user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shares`
--
ALTER TABLE `shares`
  ADD PRIMARY KEY (`share_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_profile`
--
ALTER TABLE `user_profile`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `idx_user_profile_country` (`country`),
  ADD KEY `idx_user_profile_city` (`city`);

--
-- Indexes for table `work_history`
--
ALTER TABLE `work_history`
  ADD PRIMARY KEY (`work_id`),
  ADD KEY `idx_work_history_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `education`
--
ALTER TABLE `education`
  MODIFY `education_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `friendships`
--
ALTER TABLE `friendships`
  MODIFY `friendship_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shares`
--
ALTER TABLE `shares`
  MODIFY `share_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `work_history`
--
ALTER TABLE `work_history`
  MODIFY `work_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `education`
--
ALTER TABLE `education`
  ADD CONSTRAINT `education_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `friendships`
--
ALTER TABLE `friendships`
  ADD CONSTRAINT `friendships_ibfk_1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friendships_ibfk_2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friendships_ibfk_3` FOREIGN KEY (`action_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`comment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`reported_post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_3` FOREIGN KEY (`reported_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `shares`
--
ALTER TABLE `shares`
  ADD CONSTRAINT `shares_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shares_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profile`
--
ALTER TABLE `user_profile`
  ADD CONSTRAINT `user_profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `work_history`
--
ALTER TABLE `work_history`
  ADD CONSTRAINT `work_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
