-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2025 at 07:32 AM
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
(1, 34, 6, 'xcccccccccccccccc', '2025-06-28 03:03:35'),
(2, 38, 6, 'tggggggggg', '2025-06-28 03:25:21'),
(3, 39, 6, 'ggggggggggggggg', '2025-06-28 03:25:34'),
(4, 41, 19, 'sdfffffffffffff', '2025-06-28 03:46:08'),
(5, 6, 6, 'sssssssssssssssssssss', '2025-06-28 04:05:28'),
(6, 41, 9, 'hhhhhhhhhhhhhhhhhhhh', '2025-06-28 04:15:17'),
(7, 40, 9, 'jumla', '2025-06-28 07:53:51'),
(8, 40, 9, 'ki dichen eigula kisu hoynai', '2025-06-28 07:54:13'),
(9, 42, 21, 'zxxxxxxxxxxxxxxxxxxxx', '2025-06-29 03:30:40');

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

--
-- Dumping data for table `education`
--

INSERT INTO `education` (`education_id`, `user_id`, `institution_name`, `degree`, `field_of_study`, `location`, `start_date`, `end_date`, `description`, `created_at`, `updated_at`) VALUES
(1, 6, 'bedonamohon', 'MSS', 'jfhncjkhmncgkl', 'sdnfbvgjdfnb', '2023-06-29', '2024-10-20', 'szdbvfjdxkm', '2025-06-30 05:23:37', '2025-06-30 05:23:37'),
(2, 3, 'usdfgdhjkgh', 'jgdsjafj', 'jhfdjhgdxf', 'jgsjdfhj', '2023-05-15', '2025-06-25', 'fghgjhgjkhk', '2025-06-30 05:46:16', '2025-06-30 05:46:16'),
(3, 3, 'usdfgdhjkgh', 'jgdsjafj', '', 'jgsjdfhj', '2024-07-18', '2025-06-25', 'dsgdfyhgfujhgjkjhlokj;', '2025-06-30 06:07:01', '2025-06-30 06:07:01'),
(4, 26, 'sefsef', 'dfgvzx', 'zdfgv', 'zdfgvzx', '2024-11-20', '2025-06-23', 'sxdgdhcffffffffffgh', '2025-06-30 06:29:08', '2025-06-30 06:29:08');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `friendships`
--

INSERT INTO `friendships` (`friendship_id`, `user1_id`, `user2_id`, `status`, `action_user_id`, `created_at`, `updated_at`) VALUES
(1, 4, 6, 'accepted', 4, '2025-05-31 06:06:12', '2025-06-24 18:09:51'),
(2, 4, 8, 'accepted', 8, '2025-05-31 06:06:42', '2025-06-24 18:09:51'),
(3, 6, 9, 'accepted', 9, '2025-05-31 06:06:58', '2025-06-24 18:09:51'),
(4, 9, 11, 'accepted', 11, '2025-05-31 06:07:11', '2025-06-24 18:09:51'),
(5, 4, 14, 'pending', 4, '2025-06-25 03:11:06', '2025-06-25 03:11:06'),
(6, 4, 16, 'accepted', 16, '2025-06-25 03:11:08', '2025-06-26 06:30:22'),
(7, 4, 3, 'accepted', 3, '2025-06-25 03:11:10', '2025-06-25 03:18:35'),
(8, 4, 17, 'pending', 4, '2025-06-25 03:11:13', '2025-06-25 03:11:13'),
(9, 4, 12, 'pending', 4, '2025-06-25 03:11:17', '2025-06-25 03:11:17'),
(10, 4, 11, 'pending', 4, '2025-06-25 03:11:19', '2025-06-25 03:11:19'),
(11, 4, 18, 'pending', 4, '2025-06-25 03:11:21', '2025-06-25 03:11:21'),
(12, 4, 9, 'pending', 4, '2025-06-25 03:11:22', '2025-06-25 03:11:22'),
(13, 4, 2, 'pending', 4, '2025-06-25 03:17:43', '2025-06-25 03:17:43'),
(14, 4, 15, 'pending', 4, '2025-06-25 03:17:45', '2025-06-25 03:17:45'),
(15, 4, 13, 'pending', 4, '2025-06-25 03:17:47', '2025-06-25 03:17:47'),
(16, 4, 5, 'pending', 4, '2025-06-25 03:17:48', '2025-06-25 03:17:48'),
(17, 6, 8, 'pending', 6, '2025-06-26 06:28:47', '2025-06-26 06:28:47'),
(18, 6, 11, 'pending', 6, '2025-06-26 06:28:48', '2025-06-26 06:28:48'),
(19, 6, 3, 'accepted', 3, '2025-06-26 06:28:49', '2025-06-26 06:29:44'),
(20, 6, 5, 'pending', 6, '2025-06-26 06:28:51', '2025-06-26 06:28:51'),
(21, 6, 13, 'pending', 6, '2025-06-26 06:28:53', '2025-06-26 06:28:53'),
(22, 6, 15, 'pending', 6, '2025-06-26 06:28:54', '2025-06-26 06:28:54'),
(23, 6, 12, 'pending', 6, '2025-06-26 06:28:55', '2025-06-26 06:28:55'),
(24, 6, 17, 'pending', 6, '2025-06-26 06:28:56', '2025-06-26 06:28:56'),
(25, 6, 18, 'pending', 6, '2025-06-26 06:29:01', '2025-06-26 06:29:01'),
(27, 9, 12, 'pending', 9, '2025-06-28 03:00:22', '2025-06-28 03:00:22'),
(28, 9, 18, 'pending', 9, '2025-06-28 03:00:25', '2025-06-28 03:00:25'),
(29, 19, 18, 'pending', 19, '2025-06-28 03:46:33', '2025-06-28 03:46:33'),
(30, 19, 9, 'pending', 19, '2025-06-28 03:46:40', '2025-06-28 03:46:40'),
(31, 19, 8, 'pending', 19, '2025-06-28 03:46:51', '2025-06-28 03:46:51'),
(34, 9, 20, 'accepted', 20, '2025-06-28 02:25:56', '2025-06-29 03:31:50'),
(35, 9, 25, 'pending', 9, '2025-06-28 07:54:49', '2025-06-28 07:54:49'),
(36, 9, 3, 'accepted', 3, '2025-06-28 07:55:12', '2025-06-28 07:56:59'),
(37, 20, 21, 'pending', 20, '2025-06-28 23:32:57', '2025-06-29 03:32:57'),
(39, 3, 20, 'pending', 3, '2025-06-30 02:05:20', '2025-06-30 06:05:20'),
(40, 3, 16, 'pending', 3, '2025-06-30 06:43:04', '2025-06-30 06:43:04');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `like_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`like_id`, `post_id`, `user_id`, `created_at`) VALUES
(1, 1, 16, '2025-06-25 04:02:59'),
(2, 1, 3, '2025-06-25 05:02:47'),
(3, 36, 3, '2025-06-25 05:05:58'),
(5, 8, 9, '2025-06-25 06:42:15'),
(7, 6, 9, '2025-06-25 06:42:21'),
(8, 5, 9, '2025-06-25 06:42:23'),
(9, 3, 9, '2025-06-25 06:42:26'),
(10, 38, 9, '2025-06-25 06:42:52'),
(18, 36, 6, '2025-06-28 03:03:05'),
(19, 35, 6, '2025-06-28 03:03:12'),
(20, 34, 6, '2025-06-28 03:03:21'),
(23, 37, 6, '2025-06-28 03:04:18'),
(25, 38, 6, '2025-06-28 03:07:31'),
(27, 39, 6, '2025-06-28 03:25:07'),
(29, 32, 9, '2025-06-28 03:31:22'),
(32, 39, 9, '2025-06-28 03:31:42'),
(33, 37, 9, '2025-06-28 03:34:20'),
(34, 41, 19, '2025-06-28 03:45:58'),
(35, 6, 6, '2025-06-28 04:05:04'),
(38, 41, 9, '2025-06-28 05:48:04'),
(39, 40, 9, '2025-06-28 05:48:21'),
(44, 42, 21, '2025-06-29 03:30:30');

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

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `receiver_id`, `content`, `is_read`, `created_at`) VALUES
(1, 3, 20, 'hi', 1, '2025-06-28 08:05:23'),
(2, 3, 20, 'hcccccccccccccccccccccccccccccccccccccc', 1, '2025-06-28 08:05:38'),
(3, 3, 6, 'kkkkkkkkkkkkkkkkkkkkkkkkkkkkk', 1, '2025-06-28 08:06:03'),
(4, 3, 6, 'kjjkkkkkkkkkkkkkkkkkkkkkkk', 1, '2025-06-28 08:06:19'),
(5, 6, 3, 'how kjdsssssssssssssssssss', 1, '2025-06-28 08:07:15'),
(6, 9, 21, 'hello', 1, '2025-06-29 03:31:52'),
(7, 9, 21, 'reply den', 1, '2025-06-29 03:32:43'),
(8, 21, 9, 'hi', 1, '2025-06-29 03:33:42'),
(9, 20, 3, 'hiiii', 1, '2025-06-29 03:33:53'),
(10, 21, 9, 'dilam to', 1, '2025-06-29 03:34:21'),
(11, 21, 9, 'kemon achen', 1, '2025-06-29 03:34:29'),
(12, 3, 6, 'hi', 1, '2025-06-30 05:42:16'),
(13, 6, 3, 'hello', 1, '2025-06-30 05:42:38'),
(14, 6, 3, 'jhfvjdudif', 1, '2025-06-30 05:49:46'),
(15, 3, 6, 'rew;itortgreot', 1, '2025-06-30 05:50:08'),
(16, 3, 6, 'erwiyuyre', 1, '2025-06-30 05:50:14'),
(17, 6, 3, 'mvfnmgjkfdgnjdsffhbszuydfnjfdklx', 1, '2025-06-30 05:50:16'),
(18, 6, 3, 'game ta ses koren', 1, '2025-06-30 05:50:32');

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
(1, 14, 'friend_request', 4, 0, '2025-06-25 03:11:06'),
(2, 16, 'friend_request', 4, 0, '2025-06-25 03:11:08'),
(3, 3, 'friend_request', 4, 0, '2025-06-25 03:11:10'),
(4, 17, 'friend_request', 4, 0, '2025-06-25 03:11:13'),
(5, 12, 'friend_request', 4, 0, '2025-06-25 03:11:17'),
(6, 11, 'friend_request', 4, 0, '2025-06-25 03:11:19'),
(7, 18, 'friend_request', 4, 0, '2025-06-25 03:11:21'),
(8, 9, 'friend_request', 4, 1, '2025-06-25 03:11:22'),
(9, 2, 'friend_request', 4, 0, '2025-06-25 03:17:43'),
(10, 15, 'friend_request', 4, 0, '2025-06-25 03:17:45'),
(11, 13, 'friend_request', 4, 0, '2025-06-25 03:17:47'),
(12, 5, 'friend_request', 4, 0, '2025-06-25 03:17:48'),
(13, 4, 'friend_request', 3, 0, '2025-06-25 03:18:35'),
(14, 8, 'friend_request', 6, 0, '2025-06-26 06:28:47'),
(15, 11, 'friend_request', 6, 0, '2025-06-26 06:28:48'),
(16, 3, 'friend_request', 6, 0, '2025-06-26 06:28:49'),
(17, 5, 'friend_request', 6, 0, '2025-06-26 06:28:51'),
(18, 13, 'friend_request', 6, 0, '2025-06-26 06:28:53'),
(19, 15, 'friend_request', 6, 0, '2025-06-26 06:28:54'),
(20, 12, 'friend_request', 6, 0, '2025-06-26 06:28:55'),
(21, 17, 'friend_request', 6, 0, '2025-06-26 06:28:56'),
(22, 18, 'friend_request', 6, 0, '2025-06-26 06:29:01'),
(23, 6, 'friend_request', 3, 0, '2025-06-26 06:29:44'),
(24, 4, 'friend_request', 16, 0, '2025-06-26 06:30:22'),
(25, 3, 'friend_request', 9, 0, '2025-06-28 03:00:20'),
(26, 12, 'friend_request', 9, 0, '2025-06-28 03:00:22'),
(27, 18, 'friend_request', 9, 0, '2025-06-28 03:00:25'),
(28, 18, 'friend_request', 19, 0, '2025-06-28 03:46:33'),
(29, 9, 'friend_request', 19, 1, '2025-06-28 03:46:40'),
(30, 8, 'friend_request', 19, 0, '2025-06-28 03:46:51'),
(31, 20, 'friend_request', 9, 0, '2025-06-28 00:11:38'),
(32, 20, 'friend_request', 9, 0, '2025-06-28 00:14:56'),
(33, 20, 'friend_request', 9, 0, '2025-06-28 02:25:56'),
(34, 25, 'friend_request', 9, 0, '2025-06-28 07:54:49'),
(35, 3, 'friend_request', 9, 0, '2025-06-28 07:55:12'),
(36, 9, 'friend_request', 3, 0, '2025-06-28 07:56:59'),
(37, 20, 'message', 3, 0, '2025-06-28 08:05:23'),
(38, 20, 'message', 3, 0, '2025-06-28 08:05:38'),
(39, 6, 'message', 3, 0, '2025-06-28 08:06:03'),
(40, 6, 'message', 3, 0, '2025-06-28 08:06:19'),
(41, 3, 'message', 6, 0, '2025-06-28 08:07:15'),
(42, 9, 'friend_request', 20, 0, '2025-06-29 03:31:50'),
(43, 21, 'message', 9, 0, '2025-06-29 03:31:52'),
(44, 21, 'message', 9, 0, '2025-06-29 03:32:43'),
(45, 21, 'friend_request', 20, 0, '2025-06-28 23:32:57'),
(46, 9, 'message', 21, 0, '2025-06-29 03:33:42'),
(47, 3, 'message', 20, 0, '2025-06-29 03:33:53'),
(48, 9, 'message', 21, 0, '2025-06-29 03:34:21'),
(49, 9, 'message', 21, 0, '2025-06-29 03:34:29'),
(50, 16, 'friend_request', 9, 0, '2025-06-30 00:51:54'),
(51, 6, 'message', 3, 0, '2025-06-30 05:42:16'),
(52, 3, 'message', 6, 0, '2025-06-30 05:42:38'),
(53, 3, 'message', 6, 0, '2025-06-30 05:49:46'),
(54, 6, 'message', 3, 0, '2025-06-30 05:50:08'),
(55, 6, 'message', 3, 0, '2025-06-30 05:50:14'),
(56, 3, 'message', 6, 0, '2025-06-30 05:50:16'),
(57, 3, 'message', 6, 0, '2025-06-30 05:50:32'),
(58, 20, 'friend_request', 3, 0, '2025-06-30 02:05:20'),
(59, 16, 'friend_request', 3, 0, '2025-06-30 06:43:04');

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
(3, 6, 'This is our social media.which name social-talk', 'img_683a9a5c7dbe51.33889234.webp', 'public', '2025-05-31 05:57:48', '2025-05-31 05:57:48'),
(4, 8, 'hi.\r\nwe are testing socialtalk.', '', 'public', '2025-05-31 05:59:57', '2025-05-31 05:59:57'),
(5, 6, 'This is our social media which name is social-talk', 'img_683a9af07ca231.58964211.jpg', 'public', '2025-05-31 06:00:16', '2025-05-31 06:00:16'),
(6, 9, 'sfdgfhgfjhgkiojklkm,lhfyhrdse34eretfdgfghjhkjlk;l\';l\'p[[[[[[[[[[[[[[', 'img_683a9b8749b1b8.87455655.png', 'public', '2025-05-31 06:02:47', '2025-05-31 06:02:47'),
(7, 8, 'Hulk is my favorite hero....rddfgfdg', 'img_683a9bdb956a51.37816438.png', 'public', '2025-05-31 06:04:11', '2025-05-31 06:04:11'),
(8, 11, 'mdjkgfgnmdxfkkkkkkkkkkkkkfgvfdgvn', 'img_683a9c13d50fc6.18568980.PNG', 'public', '2025-05-31 06:05:07', '2025-05-31 06:05:07'),
(15, 12, 'iugfhniughsdjfogkd;shgojjolkosdjfgdfshfgjhgkjlk', '', 'public', '2025-06-21 16:30:07', '2025-06-21 16:30:07'),
(22, 3, 'sdfhjhgkj;lk\';l\'jljkgljjjjjjjjjjjjjjjjjjjjjjjjjjjghhhhhhhhhhhhhhhhhhhhhhh', 'img_685985cfc23451.61371896.jpg,img_685985cfc26870.61284607.jpg,img_685985cfc271d1.81936253.jpg,img_685985cfc279e3.37330586.jpg,img_685985cfc280e2.26234866.jpg,img_685985cfc28925.92373076.jpg,img_685985cfc58959.16282776.jpg,img_685985cfc59f48.23506539.jpg', 'public', '2025-06-23 16:50:23', '2025-06-23 16:50:23'),
(23, 13, 'Captain America is a superhero created by Joe Simon and Jack Kirby who appears in American comic books published by Marvel Comics. The character first appeared in Captain America', 'img_685a27f00a1a08.12495460.jpg', 'public', '2025-06-24 04:22:08', '2025-06-24 04:22:08'),
(24, 13, 'Captain America is a superhero created by Joe Simon and Jack Kirby who appears in American comic books published by Marvel Comics. The character first appeared in Captain America', 'img_685a27feb5a5c2.01183254.jpg', 'public', '2025-06-24 04:22:22', '2025-06-24 04:22:22'),
(25, 14, 'The Avengers are a team of superheroes from Marvel Comics. Some of the most well-known members include Iron Man, Captain America, Thor, Hulk, Black Widow, and Hawkeye. Other prominent members include Scarlet Witch, Vision, War Machine, Falcon, Spider-Man, Black Panther, Ant-Man, and Captain Marvel. They often work together to defend Earth from various threats. ', 'img_685a2c09648052.46709801.jpg', 'public', '2025-06-24 04:39:37', '2025-06-24 04:39:37'),
(26, 14, 'The Avengers are a team of superheroes from Marvel Comics. Some of the most well-known members include Iron Man, Captain America, Thor, Hulk, Black Widow, and Hawkeye. Other prominent members include Scarlet Witch, Vision, War Machine, Falcon, Spider-Man, Black Panther, Ant-Man, and Captain Marvel. They often work together to defend Earth from various threats. ', 'img_685a2c1b3870e8.22681606.jpg', 'public', '2025-06-24 04:39:55', '2025-06-24 04:39:55'),
(27, 15, 'Black Widow is a superhero appearing in American comic books published by Marvel Comics. Created by editor Stan Lee, scripter Don Rico, and artist Don Heck, the character debuted as an enemy of Iron Man in Tales', 'img_685a2d5c87d2f2.92163550.jpg', 'public', '2025-06-24 04:45:16', '2025-06-24 04:45:16'),
(28, 15, 'Black Widow is a superhero appearing in American comic books published by Marvel Comics. Created by editor Stan Lee, scripter Don Rico, and artist Don Heck, the character debuted as an enemy of Iron Man in Tales', 'img_685a2d686b8213.36789486.jpg', 'public', '2025-06-24 04:45:28', '2025-06-24 04:45:28'),
(29, 16, 'The first iteration of the team, consisting of Iron Man, Captain America, Hulk, Thor, Black Widow and Hawkeye, defeated Loki in the Battle of New York to stop ...', 'img_685a2efab79513.12224751.jpg', 'public', '2025-06-24 04:52:10', '2025-06-24 04:52:10'),
(30, 16, 'The first iteration of the team, consisting of Iron Man, Captain America, Hulk, Thor, Black Widow and Hawkeye, defeated Loki in the Battle of New York to stop ...', 'img_685a2f061d7f80.36269839.jpg', 'public', '2025-06-24 04:52:22', '2025-06-24 04:52:22'),
(31, 17, 'At Ant-Man\'s suggestion, the five heroes agreed to form a team, and Wasp quickly named them “the Avengers.” During one of their earliest adventures, the team ...', 'img_685a3384d5c0e7.01795962.jpg', 'public', '2025-06-24 05:11:32', '2025-06-24 05:11:32'),
(32, 17, 'At Ant-Man\'s suggestion, the five heroes agreed to form a team, and Wasp quickly named them “the Avengers.” During one of their earliest adventures, the team ...', 'img_685a339771d673.80647692.jpg', 'public', '2025-06-24 05:11:51', '2025-06-24 05:11:51'),
(34, 18, 'At Ant-Man\'s suggestion, the five heroes agreed to form a team, and Wasp quickly named them “the Avengers.” During one of their earliest adventures, the team .', 'img_685a51e7895600.39364870.jpg', 'public', '2025-06-24 07:21:11', '2025-06-24 07:21:11'),
(35, 18, 'At Ant-Man\'s suggestion, the five heroes agreed to form a team, and Wasp quickly named them “the Avengers.” During one of their earliest adventures, the team .', '', 'public', '2025-06-24 07:21:28', '2025-06-24 07:21:28'),
(36, 3, 'etytytytytytytytytytytytytytytytytytytytytytytytytytytytytytytytytytyty', '', 'friends', '2025-06-24 11:43:37', '2025-06-24 15:43:37'),
(37, 4, 'At Ant-Man\'s suggestion, the five heroes agreed to form a team, and Wasp quickly named them “the Avengers.” During one of their earliest adventures, the team .', '', 'public', '2025-06-24 23:20:15', '2025-06-25 03:20:15'),
(38, 9, 'yeffffffffffffffffffffffffffffffff', '', 'public', '2025-06-25 06:42:49', '2025-06-25 06:42:49'),
(39, 6, 'fgggggggggggggggggggggggggggggggggggggggg', '', 'public', '2025-06-25 06:51:06', '2025-06-25 06:51:06'),
(40, 6, 'fffffffffffvvvvvvvvvvvvvvvvvvvvvvvvv', '', 'public', '2025-06-28 03:25:48', '2025-06-28 03:25:48'),
(41, 20, 'shfhsdggvbxhcvjkyuadtguyfgfgfgfgfgfgcbnzsc', 'img_685f648cc8d6b8.18167629.jpg', 'public', '2025-06-28 03:42:04', '2025-06-28 03:42:04'),
(42, 20, 'cvbflkxhnppppppppppppppppppghiooooooojmn', 'img_6860b31c54ed29.16678898.jpg', 'public', '2025-06-29 03:29:32', '2025-06-29 03:29:32');

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

--
-- Dumping data for table `shares`
--

INSERT INTO `shares` (`share_id`, `user_id`, `post_id`, `created_at`) VALUES
(1, 3, 36, '2025-06-24 15:23:51'),
(2, 4, 37, '2025-06-24 23:29:39'),
(3, 16, 30, '2025-06-25 00:08:14');

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
(13, 'Captain-America', 'Captain-America@gmail.com', '$2y$10$D4itnarPnP5SbFWL8f2Ow.VhFCdSb9lhOdhZDX82Kq1a7qfo3r1.e', 'user', 0, NULL, '2025-06-24 04:16:47', NULL, 'active'),
(14, 'Thor', 'Thor@gmail.com', '$2y$10$PuzYsNjS0hmOBIQuUo69AudvGLzW9BYIE.2CST3OaE3NU0HDXgbe6', 'user', 0, NULL, '2025-06-24 04:23:21', NULL, 'active'),
(15, 'Black Widow', 'Black-Widow@gmail.com', '$2y$10$IPSz46eaeqqlvU414qYLpu26PicU4CUK3n59WQASbhO/14VL459Q.', 'user', 0, NULL, '2025-06-24 04:41:43', NULL, 'active'),
(16, 'Hulk', 'Hulk@gmail.com', '$2y$10$rp.jxFyJ1SRMqvWdBuZJQuAtZq9Hjo.c27H2qWfS087l4KDdCSivy', 'user', 0, NULL, '2025-06-24 04:49:05', NULL, 'active'),
(17, 'Ant-Man', 'Ant-Man@gmail.com', '$2y$10$h3z6TPoTR8EEIi3ycPS3VelzdX7frT/UaVp2IguDkTDVZdkW0o4yG', 'user', 0, NULL, '2025-06-24 04:57:33', NULL, 'active'),
(18, 'Iron-Man', 'Iron-Man@gmail.com', '$2y$10$nizi7mp.e9FG9va4ngjXwe/iKv9NdmcS1KXvVKgEdVG76EEV79MQu', 'user', 0, NULL, '2025-06-24 05:15:46', NULL, 'active'),
(19, 'rima', 'rima@gmail.com', '$2y$10$BUhMGCZN8dO8xDntl7MtkeQUe4oQSXv1ArYiuodyoV6b6yprA5ZUC', 'user', 0, NULL, '2025-06-28 03:40:15', NULL, 'active'),
(20, 'Ruby', 'ruby@gmail.com', '$2y$10$3RtYZKuS/oxDBNIRUChwk.5gg029Ar0JZCrOJHetfHlw//NjVixkW', 'user', 0, NULL, '2025-06-28 03:40:34', NULL, 'active'),
(21, 'rita ', 'rita@gmail.com', '$2y$10$XtcPUNI7gM1OVUccD64mmuqebbfmRXTJvtrUGtz6jJ5HTwmIKQlmy', 'user', 0, NULL, '2025-06-28 03:41:04', NULL, 'active'),
(22, 'Nur islam', 'nurislam@gmail.com', '$2y$10$PbQkM.V7gXedaJ1AqzudHONlgXWgUOxB.HJIcrPIfzLfjSMaB3kXq', 'user', 0, NULL, '2025-06-28 03:42:16', NULL, 'active'),
(23, 'megla', 'megla@gmail.com', '$2y$10$iOJXK67zE0P6xm.ZRLZANeAxolKRF9tOrtKtoue3EsguE9Qk16t8u', 'user', 0, NULL, '2025-06-28 03:43:05', NULL, 'active'),
(24, 'imran', 'imran@gmail.com', '$2y$10$G5SkKfewHOTQ/hY0vZ1bQOFxNz.DVXbu3U0bBXs6RvByLmhpbyRYO', 'user', 0, NULL, '2025-06-28 03:44:11', NULL, 'active'),
(25, 'Rupa', 'rupa@gmail.com', '$2y$10$kECNtsLGbgiM.CnaRij61.qippGxSw84NjESVcx.PPJi2AUalmJGi', 'user', 0, NULL, '2025-06-28 03:45:16', NULL, 'active'),
(26, 'nowsin', 'nowsin@gmail.com', '$2y$10$fAVlhieapC0lETQPBdLiOeYnkQQIhsrfYTmo7gpfRHuNmbw4zifjm', 'user', 0, NULL, '2025-06-30 06:27:58', NULL, 'active'),
(27, 'nus', 'nus@gmail.com', '$2y$10$H9rjMmfLqc28PrSlX8ZPpucO80DrKzJ1KQ9XcphfW/xIWS9KZ5lK.', 'user', 0, NULL, '2025-06-30 06:50:50', NULL, 'active');

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
  `profile_picture` varchar(255) DEFAULT NULL,
  `cover_photo` varchar(128) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `relationship` enum('Single','In a relationship','Married','Divorced','complicated') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profile`
--

INSERT INTO `user_profile` (`user_id`, `first_name`, `last_name`, `blood_group`, `country`, `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `phone_number`, `bio`, `profile_picture`, `cover_photo`, `date_of_birth`, `gender`, `relationship`, `created_at`, `updated_at`) VALUES
(3, 'Abdul', 'Aziz', 'AB+', '', 'framgate', 'framgate', 'Dhaka', 'Dhaka', '3600', '01907717145', 'hdvbfjfehdkgsdhngkdfl jgh lfgfdmglgfhgujdfsa', 'assets/contentimages/3/profile_3_1750694860.jpg', 'assets/contentimages/3/cover_3_1750694860.jpg', '1999-01-01', 'Male', 'Single', '2025-06-02 18:28:35', '2025-06-23 16:07:40'),
(9, 'sumi', 'Akter', 'O-', 'BANGLADESH', 'framgate', 'sdhgfsdytrjd', 'Dhaka', 'Dhaka', '3600', '1662319633', 'school teacher', 'assets/contentimages/9/profile_9_1751258184.jpg', 'assets/contentimages/9/cover_9_1751258184.jpg', '2020-06-30', 'Female', 'Married', '2025-06-30 04:36:24', '2025-06-30 04:37:14'),
(12, 'Abdul', 'Aziz', 'AB+', 'Bangladesh', 'framgate', 'framgate', 'Dhaka', 'Dhaka', '3600', '01907717145', 'zfvxdjnmghk,jbnjdfzgsdfvzsdvxd', 'assets/contentimages/12/profile_12_1750526371.jpg', 'assets/contentimages/12/cover_12_1750526371.jpg', '2020-12-28', 'Male', 'Single', '2025-06-21 16:32:35', '2025-06-21 17:19:31'),
(13, 'Captain-America', 'America', 'AB+', 'USA', 'New York City', 'New York City', 'New York City', 'New York City', '3600', '12345678902', 'Captain America is a superhero created by Joe Simon and Jack Kirby who appears in American comic books published by Marvel Comics', 'assets/contentimages/13/profile_13_1750738841.jpg', 'assets/contentimages/13/cover_13_1750738841.jpg', '2015-02-03', 'Male', 'Single', '2025-06-24 04:20:41', '2025-06-24 04:20:41'),
(14, 'Thor', 'Thor', 'AB-', 'USA', 'New York City', 'New York City', 'New York City', 'New York City', '3600', '12345678902', 'Thor Odinson is a superhero appearing in American comic books published by Marvel Comics, based on the god from Old Norse religion and mythology, Thor.', 'assets/contentimages/14/profile_14_1750739932.jpg', 'assets/contentimages/14/cover_14_1750739932.jpg', '2018-01-01', 'Male', 'Single', '2025-06-24 04:38:52', '2025-06-24 04:38:52'),
(15, 'Black', 'Widow', 'AB+', 'USA', 'New York City', 'New York City', 'New York City', 'New York City', '3600', '12345678902', 'Black Widow is a superhero appearing in American comic books published by Marvel Comics. Created by editor Stan Lee, scripter Don Rico, and artist Don Heck, the character debuted as an enemy of Iron Man in Tales', 'assets/contentimages/15/profile_15_1750740288.jpg', 'assets/contentimages/15/cover_15_1750740288.jpg', '2016-06-07', 'Female', 'Single', '2025-06-24 04:44:48', '2025-06-24 04:44:48'),
(16, 'Hulk', 'Hulk', 'AB+', 'USA', 'New York City', 'New York City', 'New York City', 'New York City', '3600', '12345678902', 'The first iteration of the team, consisting of Iron Man, Captain America, Hulk, Thor, Black Widow and Hawkeye, defeated Loki in the Battle of New York to stop ...', 'assets/contentimages/16/profile_16_1750740713.jpg', 'assets/contentimages/16/cover_16_1750740713.jpg', '2012-01-30', 'Male', 'Single', '2025-06-24 04:51:53', '2025-06-24 04:51:53'),
(17, 'Ant', 'Man', 'AB+', 'USA', 'New York City', 'New York City', 'New York City', 'New York City', '3600', '12345678902', 'At Ant-Man\'s suggestion, the five heroes agreed to form a team, and Wasp quickly named them “the Avengers.” During one of their earliest adventures, the team ...', 'assets/contentimages/17/profile_17_1750741594.jpg', 'assets/contentimages/17/cover_17_1750741594.jpg', '2015-06-15', 'Male', 'Single', '2025-06-24 05:06:34', '2025-06-24 05:06:34'),
(18, 'Iron', 'Man', 'AB-', 'USA', 'New York City', 'New York City', 'New York City', 'New York City', '3600', '12345678902', 'Iron Man, Thor, and even Hulk readily agreed, while the Wasp named the team “the Avengers.” Some Assembly Required. Unlike many Super Hero teams, the Avengers ...', 'assets/contentimages/18/profile_18_1750749597.jpg', 'assets/contentimages/18/cover_18_1750749597.jpg', '2016-01-11', 'Male', 'Single', '2025-06-24 07:09:56', '2025-06-24 07:19:57'),
(19, 'rima', 'akter', 'O+', 'Bangladesh', 'mirpur', 'kazipara', 'dhaka', 'dhaka', '1234', '1214564654+654+', '', 'assets/contentimages/19/profile_19_1751082505.jpg', 'assets/contentimages/19/cover_19_1751082505.jpg', '2025-06-05', 'Female', 'Single', '2025-06-28 03:48:25', '2025-06-28 03:48:25'),
(20, 'Ruby', 'akter', 'B+', 'bangladesh', 'mirpur', 'kazipara', 'dhaka', 'dhaka', '324', '0153453234', '', 'assets/contentimages/20/profile_20_1751167739.jpg', 'assets/contentimages/20/cover_20_1751167739.jpg', '2025-06-07', 'Female', 'Single', '2025-06-29 03:28:59', '2025-06-29 03:28:59'),
(21, 'rita', 'akter', 'AB-', 'Bangladesh', 'mirpur', 'kazipara', 'dhaka', 'dhaka', '1234', '017555555555', '4essssssssssssssssssss', 'assets/contentimages/21/profile_21_1751167678.jpg', 'assets/contentimages/21/cover_21_1751167678.jpg', '2025-06-13', 'Female', 'Single', '2025-06-29 03:27:58', '2025-06-29 03:27:58'),
(25, 'Rupa', 'Rupa', 'B+', '', 'mirpur', '', 'dhaka', '', '', '0153453234', '', 'assets/contentimages/25/profile_25_1751082412.jpg', 'assets/contentimages/25/cover_25_1751082412.jpg', '2025-06-12', 'Female', 'Single', '2025-06-28 03:46:52', '2025-06-28 03:46:52');

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
-- Dumping data for table `work_history`
--

INSERT INTO `work_history` (`work_id`, `user_id`, `company_name`, `job_title`, `location`, `start_date`, `end_date`, `description`, `created_at`, `updated_at`) VALUES
(1, 6, 'sjfhdgjfd', 'fkjhnmb', 'fjdrgjdhbj', '2024-01-16', '2025-06-30', 'ngfjkfhmggggggggggj', '2025-06-30 05:22:19', '2025-06-30 05:22:19'),
(2, 3, 'asfdfgdgds', 'sdfsgfhgfh', 'sghffshgggg', '2021-06-23', '2023-06-06', '', '2025-06-30 06:15:34', '2025-06-30 06:15:34'),
(3, 26, 'zdgfdg', 'fdgx', 'zgfffsxdrt', '2024-07-11', '2025-06-24', '', '2025-06-30 06:29:38', '2025-06-30 06:29:38');

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
  ADD UNIQUE KEY `unique_like` (`post_id`,`user_id`),
  ADD KEY `likes_ibfk_2` (`user_id`);

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
  ADD UNIQUE KEY `unique_share` (`post_id`,`user_id`),
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
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `education`
--
ALTER TABLE `education`
  MODIFY `education_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `friendships`
--
ALTER TABLE `friendships`
  MODIFY `friendship_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

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
  MODIFY `share_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `work_history`
--
ALTER TABLE `work_history`
  MODIFY `work_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

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
