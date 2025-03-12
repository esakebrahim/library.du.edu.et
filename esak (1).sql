-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 07, 2025 at 07:19 PM
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
-- Database: `esak`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `isbn` varchar(50) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `published_year` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('available','checked_out','reserved','Lost') DEFAULT NULL,
  `location` varchar(1000) NOT NULL,
  `edition` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `parent_book_id` int(11) DEFAULT NULL,
  `copy_number` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `isbn`, `category_id`, `branch_id`, `published_year`, `created_at`, `status`, `location`, `edition`, `price`, `parent_book_id`, `copy_number`) VALUES
(7, 'Javascript best - very best pdf', 'bh', '9787654321891', 6, 1, 2000, '2025-02-02 09:17:03', 'available', '116.231 edition1 2025 C.1', 1, 95.00, NULL, 1),
(8, 'Javascript best', 'bh', '576', 6, 1, 2000, '2025-02-02 09:50:03', 'Lost', '048.231 edition1 2025 C.1', 1, 262.00, NULL, 1),
(12, 'The Great Gatsby', 'F. Scott Fitzgerald', '9780743273565', 5, 2, 2000, '2025-02-02 11:56:18', 'available', '048.231 edition1 2025 C.1', 1, 205.00, NULL, 1),
(13, 'To Kill a Mockingbird', 'Harper Lee', '9780060935467', 5, 2, 2000, '2025-02-02 11:56:18', 'available', '048.231 edition1 2025 C.1', 1, 188.00, NULL, 1),
(15, 'Pride and Prejudice', 'Jane Austen', '9780141439518', 5, 2, 2000, '2025-02-02 11:56:18', 'reserved', '048.231 edition1 2025 C.1', 1, 276.00, NULL, 1),
(32, 'Javascript best - very best pdf', 'bhs', '1234567891235', 5, 3, 2000, '2025-02-08 14:58:31', 'available', '048.231 edition1 2025 C.1', 1, 267.00, NULL, 1),
(33, 'esak', 'esak', '9781503280678', 5, 1, 2000, '2025-02-09 07:13:58', 'Lost', '048.231 edition1 2025 C.1', 1, 209.00, NULL, 1),
(34, 'bekam', 'bekM', '9781503280688', 5, 1, 2000, '2025-02-09 07:16:54', 'available', '048.231 edition1 2025 C.1', 1, 195.00, NULL, 1),
(41, 'munira', 'semira', '9787654321890', 5, 2, 2000, '2025-02-12 17:38:17', 'checked_out', '048.231 edition1 2025 C.1', 1, 297.00, NULL, 1),
(44, 'fikir ena hiwot', 'abiy ahmed', '9787654321911', 5, 1, 2000, '2025-02-22 08:11:23', 'available', '048.231 edition1 2025 C.1', 1, 102.00, NULL, 1),
(45, 'fikir ena hiwot', 'abiy ahmed', '9787654321912', 5, 1, 2000, '2025-02-22 09:21:13', 'available', '048.231 edition1 2025 C.1', 1, 68.00, NULL, 1),
(46, 'fikir ena hiwot', 'abiy ahmed', '9787654321919', 5, 1, 2000, '2025-02-22 11:22:43', 'available', '048.231 edition1 2025 C.1', 1, 235.00, NULL, 1),
(47, 'fikir ena hiwot', 'abiy ahmed', '9787654321910', 5, 1, 2000, '2025-02-25 12:32:49', 'available', '048.231 edition1 2025 C.1', 1, 400.00, NULL, 1),
(49, 'fikir ena hiwot', 'bh', '9787654321895', 6, 1, 2000, '2025-02-25 13:59:44', 'available', '048.231 edition1 2025 C.1', 1, 65.00, NULL, 1),
(50, 'fikir ena hiwot', 'bh', '9787654321894', 6, 1, 2000, '2025-02-25 14:05:03', 'available', '048.231 edition1 2025 C.1', 1, 65.00, 49, 1),
(53, 'fikir', 'bh', '9787654321891', 13, 2, 2000, '2025-02-26 09:36:50', 'available', '561.231 edition1 2025 C.1', 1, 65.00, NULL, 1),
(54, 'fikir', 'bh', '9787654321891', 6, 2, 2000, '2025-02-26 17:10:05', 'available', '561.231 edition1 2025 C.1', 1, 65.00, 53, 2),
(55, 'fikir', 'bh', '9787654321891', 13, 2, 2000, '2025-02-26 17:40:57', 'available', '561.231 edition1 2025 C.1', 1, 65.00, 53, 3),
(56, 'fikir', 'bh', '9787654321891', 13, 2, 2000, '2025-02-26 17:54:01', 'available', '561.231 edition1 2025 C.1', 1, 65.00, 53, 4),
(57, 'fikirs', 'bh', '9787654321891', 13, 2, 2000, '2025-02-26 17:56:29', 'available', '561.231 edition1 2025 C.1', 1, 65.00, NULL, 1),
(58, 'kemankes', 'bh', '9787654321891', 13, 2, 2000, '2025-02-26 18:13:41', 'available', '561.231 edition1 2025 C.1', 1, 65.00, NULL, 1),
(59, 'kemankes', 'bh', '9787654321891', 13, 2, 2000, '2025-02-26 18:14:58', 'available', '561.231 edition1 2025 C.1', 1, 65.00, 58, 2),
(60, 'kemank', 'bh', '9787654321891', 13, 2, 2000, '2025-02-26 18:17:00', 'available', '561.231 edition1 2025 C.1', 1, 65.00, NULL, 1),
(61, 'kemankesjhg', 'bh', '9787654321891', 13, 2, 2000, '2025-02-26 18:36:44', 'available', '561.231 edition1 2025 C.1', 1, 65.00, NULL, 1),
(62, 'kemankesjhg', 'bh', '9787654321891', 13, 2, 2000, '2025-02-26 18:53:01', 'available', '561.231 edition1 2025 C.1', 1, 65.00, 61, 2),
(63, 'kemankesjh', 'bh', '9787654321891', 13, 2, 2000, '2025-02-26 18:59:16', 'available', '561.231 edition1 2025 C.1', 1, 65.00, NULL, 1),
(64, 'kemankesjh', 'bh', '9787654321891', 13, 2, 2000, '2025-02-26 19:01:13', 'available', '561.231 edition1 2025 C.1', 1, 65.00, 63, 2),
(65, 'kemankesjh', 'bh', '9787654321891', 13, 2, 2000, '2025-02-28 18:51:24', 'available', '561.231 edition1 2025 C.1', 1, 65.00, 63, 3),
(66, 'kemankesj', 'bh', '9787654321894', 13, 2, 2000, '2025-02-28 18:52:58', 'available', '561.231 edition1 2025 C.1', 1, 65.00, NULL, 1),
(67, 'kemal', 'bh', '9787654321894', 13, 2, 2000, '2025-02-28 18:55:11', 'available', '561.231 edition1 2025 C.1', 1, 65.00, NULL, 1),
(68, 'kema', 'bh', '9787654321894', 13, 2, 2000, '2025-02-28 19:18:44', 'available', '561.231 edition1 2025 C.1', 1, 65.00, NULL, 1),
(69, 'kema', 'bh', '9787654321894', 13, 2, 2000, '2025-02-28 19:30:41', 'available', '561.231 edition1 2025 C.1', 1, 65.00, 68, 2),
(70, 'kema', 'bh', '9787654321894', 13, 2, 2000, '2025-02-28 19:31:16', 'available', '561.231 edition1 2025 C.1', 1, 65.00, 68, 3),
(71, 'kema', 'bh', '9787654321894', 13, 2, 2000, '2025-02-28 19:34:21', 'available', '561.231 edition1 2025 C.1', 1, 65.00, 68, 4),
(72, 'kema', 'bh', '9787654321894', 13, 2, 2000, '2025-02-28 19:34:52', 'available', '561.231 edition1 2025 C.1', 1, 65.00, 68, 5),
(73, 'kema', 'bh', '9787654321894', 13, 2, 2000, '2025-02-28 20:04:46', 'available', '561.231 edition1 2025 C.1', 1, 65.00, 68, 6),
(74, 'ayda', 'bh', '9781503280681', 13, 1, 2000, '2025-02-28 20:05:21', 'available', '561.231 edition1 2025 C.1', 1, 65.00, NULL, 1),
(75, 'ayda', 'bh', '9781503280681', 12, 1, 2000, '2025-02-28 20:06:38', 'available', '561.231 edition1 2025 C.1', 1, 65.00, 74, 2),
(76, 'ayda', 'bh', '9781503280681', 13, 1, 2000, '2025-03-01 06:39:14', 'available', '561.231 edition1 2025 C.1', 1, 65.00, 74, 3),
(77, '89', 'jhgf', '9787654321841', 14, 1, 0, '2025-03-01 07:34:43', 'checked_out', '900.231 edition1 2025 C.1', 1, 65.00, NULL, 1),
(78, '1236', 'kljh', '9787654321891', 11, 1, 0, '2025-03-01 07:38:11', 'available', '625.231 edition1 2025 C.1', 1, 67.00, NULL, 1),
(79, 'jhgf', 'jhg', '9787654321881', 11, 1, 2035, '2025-03-01 08:01:43', 'available', '625.231 edition1 2035 C.1', 1, 8.00, NULL, 1),
(80, 'jg', 'ghf', '9787654321889', 11, 1, 2035, '2025-03-01 08:07:56', 'available', '625.231 edition1 2035 C.1', 1, 67.00, NULL, 1),
(81, 'kujhg', 'g', '9787654321885', 11, 1, 2035, '2025-03-01 08:10:17', 'available', '625.231 edition1 2035 C.1', 1, 123.00, NULL, 1),
(82, 'thfd', 'hbbv', '9787654321883', 11, 1, 2021, '2025-03-01 09:11:58', 'available', '625.231 edition1 2021 C.1', 1, 123.00, NULL, 1),
(83, 'uhg', 'kjhg', '9787654321884', 11, 1, 2024, '2025-03-01 09:19:05', 'available', '625.231 edition1 2024 C.1', 1, 123.00, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `borrow_requests`
--

CREATE TABLE `borrow_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `book_id` int(11) DEFAULT NULL,
  `request_date` date NOT NULL DEFAULT current_timestamp(),
  `status` enum('borrow_pending','return_pending','borrow_accept','borrow_reject','return_accept','return_reject','lost') DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `paid` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow_requests`
--

INSERT INTO `borrow_requests` (`id`, `user_id`, `book_id`, `request_date`, `status`, `due_date`, `return_date`, `paid`) VALUES
(58, 16, 7, '2025-02-06', 'return_accept', '2025-02-10', '2025-02-23', 0),
(59, 16, 8, '2025-02-06', 'borrow_reject', NULL, NULL, 0),
(62, 16, 8, '2025-02-07', 'borrow_reject', NULL, NULL, 0),
(63, 16, 8, '2025-02-07', 'borrow_accept', '2025-02-10', NULL, 0),
(64, 77, 12, '2025-02-08', 'return_accept', '2025-02-19', '2025-03-04', 0),
(65, 81, 13, '2025-02-09', 'return_accept', '2025-02-09', '2025-02-12', 0),
(74, 81, 33, '2025-02-09', 'lost', '2025-02-19', NULL, 0),
(85, 13, 7, '2025-02-13', 'return_accept', '2025-02-22', '2025-02-23', 0),
(87, 77, 34, '2025-02-14', 'borrow_pending', NULL, NULL, 0),
(95, 81, 7, '2025-03-03', 'return_accept', '2025-03-06', '2025-03-07', 0),
(98, 81, 12, '2025-03-04', 'return_pending', '2025-03-07', '2025-03-04', 0),
(99, 81, 12, '2025-03-04', 'return_pending', '2025-03-07', NULL, 0),
(104, 81, 41, '2025-03-04', 'return_reject', '2025-03-07', NULL, 0),
(105, 81, 12, '2025-03-04', 'borrow_reject', NULL, NULL, 0),
(112, 13, 78, '2025-03-07', 'return_accept', '2025-03-10', '2025-03-07', 0),
(113, 13, 77, '2025-03-07', 'borrow_reject', NULL, NULL, 0),
(114, 13, 74, '2025-03-07', 'borrow_reject', NULL, NULL, 0),
(115, 13, 77, '2025-03-07', 'borrow_accept', '2025-03-10', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `campuses`
--

CREATE TABLE `campuses` (
  `id` int(11) NOT NULL,
  `name` enum('Main','Hasedilla','Semera','Health Campus') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `campuses`
--

INSERT INTO `campuses` (`id`, `name`) VALUES
(2, 'Main'),
(1, 'Hasedilla'),
(3, 'Semera'),
(4, 'Health Campus');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(12, 'Arts & recreation'),
(5, 'Computer science, information & general work'),
(14, 'History & geography'),
(9, 'Language'),
(13, 'Literature'),
(6, 'philosophy and psychology'),
(7, 'Religion'),
(10, 'Science'),
(8, 'Social sciences'),
(11, 'Technology');

-- --------------------------------------------------------

--
-- Table structure for table `extension_requests`
--

CREATE TABLE `extension_requests` (
  `id` int(11) NOT NULL,
  `borrow_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `extension_requests`
--

INSERT INTO `extension_requests` (`id`, `borrow_id`, `user_id`, `status`, `request_date`) VALUES
(1, 85, 13, 'approved', '2025-02-14 18:37:02');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `feedback_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','responded') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `student_id`, `feedback_text`, `created_at`, `status`) VALUES
(2, 77, 'I Love You', '2025-02-07 07:04:58', 'responded'),
(3, 77, 'thanks', '2025-02-07 08:21:27', 'responded'),
(4, 77, 'testing', '2025-02-07 12:17:11', 'responded'),
(5, 77, 'testing', '2025-02-07 12:17:28', 'responded'),
(6, 77, 'testing', '2025-02-07 12:18:34', 'responded'),
(7, 77, 'testing', '2025-02-07 12:39:05', 'pending'),
(8, 77, 'hi', '2025-02-07 13:52:05', 'responded'),
(9, 77, 'whats wrong with you?', '2025-02-10 07:10:55', 'pending'),
(10, 81, 'bekam', '2025-02-10 07:28:22', 'responded'),
(11, 77, 'i am teacher', '2025-02-22 08:55:37', 'responded'),
(12, 77, 'hi', '2025-02-22 09:17:47', 'responded'),
(13, 81, 'hi', '2025-02-22 09:33:10', 'responded'),
(14, 13, 'hi', '2025-02-22 09:38:54', 'responded');

-- --------------------------------------------------------

--
-- Table structure for table `journals`
--

CREATE TABLE `journals` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `editor` varchar(255) DEFAULT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `publication_date` date DEFAULT NULL,
  `volume` int(11) DEFAULT NULL,
  `issue` int(11) DEFAULT NULL,
  `issn` varchar(20) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('available','checked_out','reserved') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `librarian_actions_log`
--

CREATE TABLE `librarian_actions_log` (
  `id` int(11) NOT NULL,
  `librarian_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `book_id` int(11) DEFAULT NULL,
  `library_branch_id` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `librarian_actions_log`
--

INSERT INTO `librarian_actions_log` (`id`, `librarian_id`, `action`, `book_id`, `library_branch_id`, `timestamp`) VALUES
(2, 82, 'Add', 32, 3, '2025-02-08 14:58:31'),
(22, 82, 'Deleted book: Javascript best - very best pdf', 32, 3, '2025-02-08 17:46:55'),
(26, 82, 'Deleted book: The Great Gatsby', 12, 2, '2025-02-08 18:00:11'),
(27, 82, 'Deleted book: Fahrenheit 451', 18, 2, '2025-02-08 18:01:23'),
(28, 81, 'Confirmed borrow request for book ID: 12 by user ID: 77', 12, 1, '2025-02-08 18:13:52'),
(29, 41, 'Confirmed borrow request for book ID: 32 by user ID: 41', 32, 1, '2025-02-08 19:01:18'),
(30, 41, 'Confirmed borrow request for book ID: 32 by user ID: 41', 32, 1, '2025-02-08 19:21:39'),
(31, 41, 'Add', 33, 1, '2025-02-09 07:13:58'),
(32, 41, 'Add', 34, 1, '2025-02-09 07:16:54'),
(33, 16, 'Confirmed borrow request for book ID: 33 by user ID: 81', 33, 1, '2025-02-09 15:20:28'),
(34, 41, 'Confirmed borrow request for book ID: 13 by user ID: 81', 13, 1, '2025-02-09 17:26:06'),
(35, 41, 'Confirmed return of book ID 12', 12, NULL, '2025-02-10 06:42:11'),
(36, 82, 'Confirmed return of book ID 7', 7, NULL, '2025-02-10 15:16:52'),
(37, 82, 'Enforced a fine of 100 Birr for book (ID: 13) for reason: lost', NULL, NULL, '2025-02-10 18:44:19'),
(39, 41, 'Confirmed return of book ID 13', 13, NULL, '2025-02-11 11:43:54'),
(40, 41, 'Book with id = \'\' added', 35, 2, '2025-02-12 17:24:23'),
(41, 41, 'Book with id = \'\' added', 35, 2, '2025-02-12 17:24:23'),
(42, 41, 'Book with id = \'\' added', 36, 2, '2025-02-12 17:26:46'),
(43, 41, 'Book with id = \'\' added', 38, 2, '2025-02-12 17:28:07'),
(44, 41, 'Book with ID = \'41\' added', 41, 2, '2025-02-12 17:38:17'),
(45, 41, 'Confirmed borrow request for book ID: 7 by user ID: 13', 7, 1, '2025-02-14 17:19:19'),
(46, 41, 'Book with ID = \'44\' added', 44, 1, '2025-02-22 08:11:27'),
(47, 41, 'Book with ID = \'45\' added', 45, 1, '2025-02-22 09:21:16'),
(48, 41, 'Book with ID = \'46\' added', 46, 1, '2025-02-22 11:22:45'),
(49, 41, 'Confirmed return of book ID 7', 7, NULL, '2025-02-22 19:34:20'),
(50, 41, 'Confirmed return of book ID 7', 7, NULL, '2025-02-22 19:34:27'),
(51, 41, 'Book with ID = \'47\' added', 47, 1, '2025-02-25 12:32:51'),
(52, 41, 'Added book copy: fikir (Copy 2)', 55, 1, '2025-02-26 17:40:57'),
(53, 41, 'Added book copy: fikir (Copy 3)', 56, 1, '2025-02-26 17:54:01'),
(54, 41, 'Added book copy: fikirs (Copy 1)', 57, 1, '2025-02-26 17:56:29'),
(55, 41, 'Added book copy: kemankes (Copy 1)', 58, 1, '2025-02-26 18:13:41'),
(56, 41, 'Added book copy: kemankes (Copy 2)', 59, 1, '2025-02-26 18:14:58'),
(57, 41, 'Added book copy: kemank (Copy 1)', 60, 1, '2025-02-26 18:17:00'),
(58, 41, 'Added book copy: kemankesjhg (Copy 1)', 61, 1, '2025-02-26 18:36:44'),
(59, 41, 'Added book copy: kemankesjhg (Copy 2)', 62, 1, '2025-02-26 18:53:01'),
(60, 41, 'Added book copy: kemankesjh (Copy 1)', 63, 1, '2025-02-26 18:59:17'),
(61, 41, 'Added book copy: kemankesjh (Copy 2)', 64, 1, '2025-02-26 19:01:13'),
(62, 41, 'Added book copy: kemankesjh (Copy 3)', 65, 1, '2025-02-28 18:51:25'),
(63, 41, 'Added book copy: kemankesj (Copy 1)', 66, 1, '2025-02-28 18:52:58'),
(64, 41, 'Added book copy: kemal (Copy 1)', 67, 1, '2025-02-28 18:55:11'),
(65, 41, 'Added book copy: kema (Copy 1)', 68, 1, '2025-02-28 19:18:46'),
(66, 41, 'Added book copy: kema (Copy 2)', 69, 1, '2025-02-28 19:30:41'),
(67, 41, 'Added book copy: kema (Copy 3)', 70, 1, '2025-02-28 19:31:16'),
(68, 41, 'Added book copy: kema (Copy 4)', 71, 1, '2025-02-28 19:34:21'),
(69, 41, 'Added book copy: kema (Copy 5)', 72, 1, '2025-02-28 19:34:52'),
(70, 41, 'Added book copy: kema (Copy 6)', 73, 1, '2025-02-28 20:04:46'),
(71, 41, 'Added book copy: kemagh (Copy 1)', 74, 1, '2025-02-28 20:05:22'),
(72, 41, 'Added book copy: kemagh (Copy 2)', 75, 1, '2025-02-28 20:06:39'),
(73, 41, 'Added book copy: kemagh (Copy 3)', 76, 1, '2025-03-01 06:39:14'),
(74, 41, 'Added book copy: 8976 (Copy 1)', 77, 1, '2025-03-01 07:34:43'),
(75, 41, 'Added book copy: jkhg (Copy 1)', 78, 1, '2025-03-01 07:38:11'),
(76, 41, 'Added book copy: jhgf (Copy 1)', 79, 1, '2025-03-01 08:01:45'),
(77, 41, 'Added book copy: jg (Copy 1)', 80, 1, '2025-03-01 08:07:56'),
(78, 41, 'Added book copy: kujhg (Copy 1)', 81, 1, '2025-03-01 08:10:18'),
(79, 41, 'Update', 78, 1, '2025-03-01 08:40:12'),
(80, 41, 'Update', 78, 1, '2025-03-01 08:41:49'),
(81, 41, 'Update', 78, 1, '2025-03-01 08:44:32'),
(82, 41, 'Update', 78, 1, '2025-03-01 08:44:39'),
(83, 41, 'Update', 78, 1, '2025-03-01 08:44:50'),
(84, 41, 'Update', 78, 1, '2025-03-01 08:44:54'),
(85, 41, 'Update', 77, 1, '2025-03-01 08:48:22'),
(86, 41, 'Update', 77, 1, '2025-03-01 08:48:34'),
(87, 41, 'Update', 77, 1, '2025-03-01 08:51:22'),
(88, 41, 'Update', 77, 1, '2025-03-01 08:51:33'),
(89, 41, 'Update', 77, 1, '2025-03-01 08:51:54'),
(90, 41, 'Added book copy: thfd (Copy 1)', 82, 1, '2025-03-01 09:11:58'),
(91, 41, 'Added book copy: uhg (Copy 1)', 83, 1, '2025-03-01 09:19:05'),
(92, 41, 'Update', 74, 2, '2025-03-01 13:06:37'),
(93, 41, 'Update', 74, 2, '2025-03-01 13:23:31'),
(94, 41, 'Update', 74, 1, '2025-03-01 13:40:21'),
(95, 41, 'Update', 74, 1, '2025-03-01 13:41:44'),
(96, 41, 'Update', 74, 1, '2025-03-01 13:42:35'),
(97, 41, 'Update', 74, 1, '2025-03-01 13:51:29'),
(98, 41, 'Update', 74, 1, '2025-03-01 13:51:37'),
(99, 41, 'Update', 74, 1, '2025-03-01 13:55:22'),
(100, 41, 'Update', 74, 1, '2025-03-01 13:55:28'),
(101, 81, 'Confirmed borrow request for book ID: 7 by user ID: 81', 7, 1, '2025-03-02 14:13:10'),
(102, 81, 'Confirmed borrow request for book ID: 12 by user ID: 81', 12, 1, '2025-03-03 14:13:07'),
(103, 41, 'Confirmed return of book ID 12', 12, NULL, '2025-03-03 14:15:00'),
(104, 41, 'Confirmed return of book ID 12', 12, NULL, '2025-03-03 14:15:05'),
(105, 81, 'Confirmed borrow request for book ID: 12 by user ID: 81', 12, 1, '2025-03-03 14:30:59'),
(106, 41, 'Confirmed return of book ID 12', 12, NULL, '2025-03-03 14:31:14'),
(107, 41, 'Confirmed borrow request for book ID: 41 by user ID: 81', 41, NULL, '2025-03-03 15:50:39'),
(108, 41, 'Confirmed borrow request for the book \'munira\' by user ID: 81', 41, NULL, '2025-03-03 15:57:22'),
(109, 41, 'Rejected borrow request for the book \'The Great Gatsby\' by user ID: 81', 12, NULL, '2025-03-03 17:10:27'),
(110, 41, 'Rejected return of book ID 41', 41, NULL, '2025-03-03 19:16:45'),
(111, 82, 'Rejected borrow request for the book \'89\' by user ID: 13', 77, NULL, '2025-03-06 14:39:32'),
(112, 82, 'Rejected borrow request for the book \'ayda\' by user ID: 13', 74, NULL, '2025-03-06 14:40:09'),
(113, 82, 'Confirmed borrow request for the book \'89\' by user ID: 13', 77, NULL, '2025-03-06 14:44:55'),
(114, 82, 'Confirmed borrow request for the book \'1236\' by user ID: 13', 78, NULL, '2025-03-06 14:44:57'),
(115, 82, 'Confirmed return of book ID 78', 78, NULL, '2025-03-06 14:51:54'),
(116, 82, 'Confirmed return of book ID 7', 7, NULL, '2025-03-06 14:51:56');

-- --------------------------------------------------------

--
-- Table structure for table `librarian_branches`
--

CREATE TABLE `librarian_branches` (
  `id` int(11) NOT NULL,
  `librarian_id` int(11) DEFAULT NULL,
  `library_branch_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `librarian_branches`
--

INSERT INTO `librarian_branches` (`id`, `librarian_id`, `library_branch_id`) VALUES
(14, 82, 1),
(18, 41, 2);

-- --------------------------------------------------------

--
-- Table structure for table `librarian_responses`
--

CREATE TABLE `librarian_responses` (
  `id` int(11) NOT NULL,
  `feedback_id` int(11) NOT NULL,
  `librarian_id` int(11) NOT NULL,
  `response_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `librarian_responses`
--

INSERT INTO `librarian_responses` (`id`, `feedback_id`, `librarian_id`, `response_text`, `created_at`) VALUES
(3, 2, 77, 'I Love You Too', '2025-02-07 07:18:46'),
(4, 2, 77, 'I Love You Too', '2025-02-07 08:21:06'),
(5, 3, 77, 'nop', '2025-02-07 08:21:39'),
(6, 4, 77, 'fuck you', '2025-02-07 13:01:32'),
(7, 5, 77, 'also testing', '2025-02-07 13:50:40'),
(8, 8, 77, 'hi', '2025-02-07 13:52:37'),
(9, 6, 41, 'test', '2025-02-09 19:52:53'),
(10, 10, 82, 'check', '2025-02-10 07:28:43'),
(11, 11, 41, 'ena mn ytebes', '2025-02-22 08:56:27'),
(12, 12, 41, 'hi', '2025-02-22 09:18:05'),
(13, 13, 41, 'hi', '2025-02-22 09:33:25'),
(14, 14, 41, 'hi', '2025-02-22 09:39:21');

-- --------------------------------------------------------

--
-- Table structure for table `librarian_roles`
--

CREATE TABLE `librarian_roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `librarian_roles`
--

INSERT INTO `librarian_roles` (`id`, `role_name`, `description`) VALUES
(1, 'Cataloging', 'adding and managing books feedback'),
(2, 'Circulation', 'issuing and returning book feedbacks'),
(3, 'Acquisition', '');

-- --------------------------------------------------------

--
-- Table structure for table `library_branches`
--

CREATE TABLE `library_branches` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `campus_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `library_branches`
--

INSERT INTO `library_branches` (`id`, `name`, `campus_id`) VALUES
(1, 'Law', 3),
(2, 'Techno', 1),
(3, 'Fb', 2);

-- --------------------------------------------------------

--
-- Table structure for table `lost_books`
--

CREATE TABLE `lost_books` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `status` enum('pending','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lost_books`
--

INSERT INTO `lost_books` (`id`, `user_id`, `book_id`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 81, 33, 'lost', 'pending', '2025-03-02 12:58:56', '2025-03-02 12:58:56'),
(2, 81, 33, 'ytgfd', 'pending', '2025-03-03 10:24:02', '2025-03-03 10:24:02'),
(3, 81, 33, 'hi', 'pending', '2025-03-03 10:41:57', '2025-03-03 10:41:57'),
(4, 81, 33, 'hi', 'pending', '2025-03-03 10:43:04', '2025-03-03 10:43:04');

-- --------------------------------------------------------

--
-- Table structure for table `magazines`
--

CREATE TABLE `magazines` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `editor` varchar(255) DEFAULT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `publication_date` date DEFAULT NULL,
  `volume` int(11) DEFAULT NULL,
  `issue` int(11) DEFAULT NULL,
  `issn` varchar(20) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('available','checked_out','reserved') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `file_path` varchar(1000) DEFAULT NULL,
  `created_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `file_path`, `created_at`) VALUES
(8, 55, 41, 'New Library Report Available. Click to download.', '/Library system/reports/1740901181_67c40b3d5dadd.pdf', '2025-03-02'),
(9, 55, 82, 'New Library Report Available. Click to download.', '/Library system/reports/1740901181_67c40b3d5dadd.pdf', '2025-03-02');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `status` enum('read','unread') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` varchar(50) NOT NULL DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `status`, `created_at`, `type`) VALUES
(1, 77, 'Your feedback has been responded to. Please check the response.', 'read', '2025-02-07 07:18:46', 'feedback'),
(2, 77, 'Your feedback has been responded to. Please check the response.', 'read', '2025-02-07 08:21:06', 'feedback'),
(3, 77, 'Your feedback has been responded to. Please check the response.', 'read', '2025-02-07 08:21:39', 'feedback'),
(6, 77, 'Your feedback has been responded to. Please check the response.', 'read', '2025-02-07 13:01:35', 'general'),
(7, 77, 'Your feedback has been responded to. Please check the response.', 'read', '2025-02-07 13:50:40', 'general'),
(8, 77, 'Your feedback has been responded to. Please check the response.', 'read', '2025-02-07 13:52:38', 'general'),
(9, 81, 'Your borrowed book \'To Kill a Mockingbird\' is overdue!', 'read', '2025-02-09 19:05:56', 'general'),
(21, 77, 'Your feedback has been responded to. Please check the response.', 'read', '2025-02-09 19:52:54', 'feedback'),
(22, 81, 'Your reservation for \'Pride and Prejudice\' has expired.', 'read', '2025-02-10 07:23:43', 'general'),
(23, 81, 'Your borrowed book \'To Kill a Mockingbird\' is overdue!', 'read', '2025-02-10 07:23:53', 'general'),
(24, 81, 'Your feedback has been responded to. Please check the response.', 'read', '2025-02-10 07:28:44', 'feedback'),
(25, 81, 'Your reservation for \'Javascript best - very best pdf\' has expired.', 'read', '2025-02-11 11:28:20', 'general'),
(26, 16, 'Your borrowed book \'Javascript best\' is overdue!', 'unread', '2025-02-11 11:28:26', 'general'),
(27, 81, 'Your borrowed book \'To Kill a Mockingbird\' is overdue!', 'read', '2025-02-11 11:28:33', 'general'),
(28, 16, 'Your borrowed book \'Javascript best\' is overdue!', 'unread', '2025-02-11 11:32:27', 'general'),
(29, 81, 'Your borrowed book \'To Kill a Mockingbird\' is overdue!', 'read', '2025-02-11 11:32:31', 'general'),
(30, 16, 'Your borrowed book \'Javascript best\' is overdue!', 'unread', '2025-02-11 11:49:21', 'general'),
(31, 13, 'Your reservation for \'To Kill a Mockingbird\' has expired.', 'read', '2025-02-20 08:01:58', 'general'),
(32, 16, 'Your borrowed book \'Javascript best\' is overdue!', 'unread', '2025-02-20 08:02:06', 'general'),
(33, 81, 'Your borrowed book \'esak\' is overdue!', 'read', '2025-02-20 08:02:09', 'general'),
(34, 13, 'Your borrowed book \'Javascript best - very best pdf\' is overdue!', 'read', '2025-02-20 08:02:12', 'general'),
(35, 16, 'Your borrowed book \'Javascript best\' is overdue!', 'unread', '2025-02-20 11:05:19', 'general'),
(36, 81, 'Your borrowed book \'esak\' is overdue!', 'read', '2025-02-20 11:05:25', 'general'),
(37, 13, 'Your borrowed book \'Javascript best - very best pdf\' is overdue!', 'read', '2025-02-20 11:05:28', 'general'),
(38, 16, 'Your borrowed book \'Javascript best\' is overdue!', 'unread', '2025-02-20 11:13:27', 'general'),
(39, 81, 'Your borrowed book \'esak\' is overdue!', 'read', '2025-02-20 11:13:30', 'general'),
(40, 13, 'Your borrowed book \'Javascript best - very best pdf\' is overdue!', 'read', '2025-02-20 11:13:33', 'general'),
(41, 13, 'New book added: fikir ena hiwot by abiy ahmed', 'read', '2025-02-22 08:11:24', 'general'),
(42, 16, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 08:11:24', 'general'),
(43, 25, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 08:11:25', 'general'),
(44, 27, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 08:11:25', 'general'),
(45, 28, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 08:11:25', 'general'),
(46, 32, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 08:11:25', 'general'),
(47, 33, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 08:11:25', 'general'),
(48, 34, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 08:11:25', 'general'),
(49, 39, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 08:11:25', 'general'),
(50, 40, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 08:11:26', 'general'),
(51, 52, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 08:11:26', 'general'),
(52, 53, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 08:11:26', 'general'),
(53, 54, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 08:11:26', 'general'),
(54, 58, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 08:11:26', 'general'),
(55, 77, 'New book added: fikir ena hiwot by abiy ahmed', 'read', '2025-02-22 08:11:26', 'general'),
(56, 78, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 08:11:26', 'general'),
(57, 79, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 08:11:26', 'general'),
(58, 81, 'New book added: fikir ena hiwot by abiy ahmed', 'read', '2025-02-22 08:11:27', 'general'),
(59, 83, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 08:11:27', 'general'),
(60, 77, 'Your feedback has been responded to. Please check the response.', 'read', '2025-02-22 08:56:28', 'feedback'),
(61, 77, 'Your feedback has been responded to. Please check the response.', 'read', '2025-02-22 09:18:05', 'feedback'),
(62, 13, 'New book added: fikir ena hiwot by abiy ahmed', 'read', '2025-02-22 09:21:13', 'general'),
(63, 16, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 09:21:14', 'general'),
(64, 25, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 09:21:14', 'general'),
(65, 27, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 09:21:15', 'general'),
(66, 28, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 09:21:15', 'general'),
(67, 32, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 09:21:15', 'general'),
(68, 33, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 09:21:15', 'general'),
(69, 34, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 09:21:15', 'general'),
(70, 39, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 09:21:15', 'general'),
(71, 40, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 09:21:15', 'general'),
(72, 52, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 09:21:15', 'general'),
(73, 53, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 09:21:15', 'general'),
(74, 54, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 09:21:15', 'general'),
(75, 58, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 09:21:15', 'general'),
(76, 77, 'New book added: fikir ena hiwot by abiy ahmed', 'read', '2025-02-22 09:21:15', 'general'),
(77, 78, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 09:21:16', 'general'),
(78, 79, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 09:21:16', 'general'),
(79, 81, 'New book added: fikir ena hiwot by abiy ahmed', 'read', '2025-02-22 09:21:16', 'general'),
(80, 83, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-22 09:21:16', 'general'),
(81, 81, 'Your feedback has been responded to. Please check the response.', 'read', '2025-02-22 09:33:27', 'feedback'),
(82, 13, 'Your feedback has been responded to. Please check the response.', 'read', '2025-02-22 09:39:22', 'feedback'),
(83, 13, 'New book added: fikir ena hiwot by abiy ahmed, Edition: 3, Location: bnw', 'read', '2025-02-22 11:22:43', 'general'),
(84, 16, 'New book added: fikir ena hiwot by abiy ahmed, Edition: 3, Location: bnw', 'unread', '2025-02-22 11:22:43', 'general'),
(85, 25, 'New book added: fikir ena hiwot by abiy ahmed, Edition: 3, Location: bnw', 'unread', '2025-02-22 11:22:44', 'general'),
(86, 27, 'New book added: fikir ena hiwot by abiy ahmed, Edition: 3, Location: bnw', 'unread', '2025-02-22 11:22:44', 'general'),
(87, 28, 'New book added: fikir ena hiwot by abiy ahmed, Edition: 3, Location: bnw', 'unread', '2025-02-22 11:22:44', 'general'),
(88, 32, 'New book added: fikir ena hiwot by abiy ahmed, Edition: 3, Location: bnw', 'unread', '2025-02-22 11:22:45', 'general'),
(89, 33, 'New book added: fikir ena hiwot by abiy ahmed, Edition: 3, Location: bnw', 'unread', '2025-02-22 11:22:45', 'general'),
(90, 34, 'New book added: fikir ena hiwot by abiy ahmed, Edition: 3, Location: bnw', 'unread', '2025-02-22 11:22:45', 'general'),
(91, 39, 'New book added: fikir ena hiwot by abiy ahmed, Edition: 3, Location: bnw', 'unread', '2025-02-22 11:22:45', 'general'),
(92, 40, 'New book added: fikir ena hiwot by abiy ahmed, Edition: 3, Location: bnw', 'unread', '2025-02-22 11:22:45', 'general'),
(93, 52, 'New book added: fikir ena hiwot by abiy ahmed, Edition: 3, Location: bnw', 'unread', '2025-02-22 11:22:45', 'general'),
(94, 53, 'New book added: fikir ena hiwot by abiy ahmed, Edition: 3, Location: bnw', 'unread', '2025-02-22 11:22:45', 'general'),
(95, 54, 'New book added: fikir ena hiwot by abiy ahmed, Edition: 3, Location: bnw', 'unread', '2025-02-22 11:22:45', 'general'),
(96, 58, 'New book added: fikir ena hiwot by abiy ahmed, Edition: 3, Location: bnw', 'unread', '2025-02-22 11:22:45', 'general'),
(97, 77, 'New book added: fikir ena hiwot by abiy ahmed, Edition: 3, Location: bnw', 'unread', '2025-02-22 11:22:45', 'general'),
(98, 78, 'New book added: fikir ena hiwot by abiy ahmed, Edition: 3, Location: bnw', 'unread', '2025-02-22 11:22:45', 'general'),
(99, 79, 'New book added: fikir ena hiwot by abiy ahmed, Edition: 3, Location: bnw', 'unread', '2025-02-22 11:22:45', 'general'),
(100, 81, 'New book added: fikir ena hiwot by abiy ahmed, Edition: 3, Location: bnw', 'read', '2025-02-22 11:22:45', 'general'),
(101, 83, 'New book added: fikir ena hiwot by abiy ahmed, Edition: 3, Location: bnw', 'unread', '2025-02-22 11:22:45', 'general'),
(102, 13, 'New book added: fikir ena hiwot by abiy ahmed', 'read', '2025-02-25 12:32:49', 'general'),
(103, 16, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-25 12:32:49', 'general'),
(104, 25, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-25 12:32:49', 'general'),
(105, 27, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-25 12:32:50', 'general'),
(106, 28, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-25 12:32:50', 'general'),
(107, 32, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-25 12:32:50', 'general'),
(108, 33, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-25 12:32:50', 'general'),
(109, 34, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-25 12:32:50', 'general'),
(110, 39, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-25 12:32:50', 'general'),
(111, 40, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-25 12:32:50', 'general'),
(112, 52, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-25 12:32:50', 'general'),
(113, 53, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-25 12:32:50', 'general'),
(114, 54, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-25 12:32:50', 'general'),
(115, 58, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-25 12:32:50', 'general'),
(116, 77, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-25 12:32:50', 'general'),
(117, 78, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-25 12:32:50', 'general'),
(118, 79, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-25 12:32:50', 'general'),
(119, 81, 'New book added: fikir ena hiwot by abiy ahmed', 'read', '2025-02-25 12:32:51', 'general'),
(120, 83, 'New book added: fikir ena hiwot by abiy ahmed', 'unread', '2025-02-25 12:32:51', 'general'),
(121, 13, 'New book (Edition 3) added successfully!', 'read', '2025-02-25 13:59:45', 'general'),
(122, 16, 'New book (Edition 3) added successfully!', 'unread', '2025-02-25 13:59:45', 'general'),
(123, 25, 'New book (Edition 3) added successfully!', 'unread', '2025-02-25 13:59:45', 'general'),
(124, 27, 'New book (Edition 3) added successfully!', 'unread', '2025-02-25 13:59:45', 'general'),
(125, 28, 'New book (Edition 3) added successfully!', 'unread', '2025-02-25 13:59:45', 'general'),
(126, 32, 'New book (Edition 3) added successfully!', 'unread', '2025-02-25 13:59:45', 'general'),
(127, 33, 'New book (Edition 3) added successfully!', 'unread', '2025-02-25 13:59:45', 'general'),
(128, 34, 'New book (Edition 3) added successfully!', 'unread', '2025-02-25 13:59:45', 'general'),
(129, 39, 'New book (Edition 3) added successfully!', 'unread', '2025-02-25 13:59:45', 'general'),
(130, 40, 'New book (Edition 3) added successfully!', 'unread', '2025-02-25 13:59:45', 'general'),
(131, 52, 'New book (Edition 3) added successfully!', 'unread', '2025-02-25 13:59:45', 'general'),
(132, 53, 'New book (Edition 3) added successfully!', 'unread', '2025-02-25 13:59:45', 'general'),
(133, 54, 'New book (Edition 3) added successfully!', 'unread', '2025-02-25 13:59:45', 'general'),
(134, 58, 'New book (Edition 3) added successfully!', 'unread', '2025-02-25 13:59:45', 'general'),
(135, 77, 'New book (Edition 3) added successfully!', 'unread', '2025-02-25 13:59:45', 'general'),
(136, 78, 'New book (Edition 3) added successfully!', 'unread', '2025-02-25 13:59:45', 'general'),
(137, 79, 'New book (Edition 3) added successfully!', 'unread', '2025-02-25 13:59:45', 'general'),
(138, 81, 'New book (Edition 3) added successfully!', 'read', '2025-02-25 13:59:46', 'general'),
(139, 83, 'New book (Edition 3) added successfully!', 'unread', '2025-02-25 13:59:46', 'general'),
(140, 13, 'New book (Edition 4) added successfully!', 'read', '2025-02-25 14:05:03', 'general'),
(141, 16, 'New book (Edition 4) added successfully!', 'unread', '2025-02-25 14:05:03', 'general'),
(142, 25, 'New book (Edition 4) added successfully!', 'unread', '2025-02-25 14:05:04', 'general'),
(143, 27, 'New book (Edition 4) added successfully!', 'unread', '2025-02-25 14:05:04', 'general'),
(144, 28, 'New book (Edition 4) added successfully!', 'unread', '2025-02-25 14:05:04', 'general'),
(145, 32, 'New book (Edition 4) added successfully!', 'unread', '2025-02-25 14:05:04', 'general'),
(146, 33, 'New book (Edition 4) added successfully!', 'unread', '2025-02-25 14:05:04', 'general'),
(147, 34, 'New book (Edition 4) added successfully!', 'unread', '2025-02-25 14:05:04', 'general'),
(148, 39, 'New book (Edition 4) added successfully!', 'unread', '2025-02-25 14:05:04', 'general'),
(149, 40, 'New book (Edition 4) added successfully!', 'unread', '2025-02-25 14:05:04', 'general'),
(150, 52, 'New book (Edition 4) added successfully!', 'unread', '2025-02-25 14:05:04', 'general'),
(151, 53, 'New book (Edition 4) added successfully!', 'unread', '2025-02-25 14:05:04', 'general'),
(152, 54, 'New book (Edition 4) added successfully!', 'unread', '2025-02-25 14:05:04', 'general'),
(153, 58, 'New book (Edition 4) added successfully!', 'unread', '2025-02-25 14:05:04', 'general'),
(154, 77, 'New book (Edition 4) added successfully!', 'unread', '2025-02-25 14:05:05', 'general'),
(155, 78, 'New book (Edition 4) added successfully!', 'unread', '2025-02-25 14:05:05', 'general'),
(156, 79, 'New book (Edition 4) added successfully!', 'unread', '2025-02-25 14:05:05', 'general'),
(157, 81, 'New book (Edition 4) added successfully!', 'read', '2025-02-25 14:05:05', 'general'),
(158, 83, 'New book (Edition 4) added successfully!', 'unread', '2025-02-25 14:05:05', 'general'),
(159, 13, 'New book fikir (Edition 1) added successfully!', 'read', '2025-02-26 09:36:50', 'general'),
(160, 16, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 09:36:50', 'general'),
(161, 25, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 09:36:50', 'general'),
(162, 27, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 09:36:50', 'general'),
(163, 28, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 09:36:51', 'general'),
(164, 32, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 09:36:51', 'general'),
(165, 33, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 09:36:51', 'general'),
(166, 34, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 09:36:51', 'general'),
(167, 39, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 09:36:51', 'general'),
(168, 40, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 09:36:51', 'general'),
(169, 52, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 09:36:51', 'general'),
(170, 53, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 09:36:51', 'general'),
(171, 54, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 09:36:51', 'general'),
(172, 58, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 09:36:51', 'general'),
(173, 77, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 09:36:51', 'general'),
(174, 78, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 09:36:51', 'general'),
(175, 79, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 09:36:51', 'general'),
(176, 81, 'New book fikir (Edition 1) added successfully!', 'read', '2025-02-26 09:36:51', 'general'),
(177, 83, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 09:36:51', 'general'),
(178, 13, 'New book fikir (Edition 1) added successfully!', 'read', '2025-02-26 17:10:17', 'general'),
(179, 16, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 17:10:18', 'general'),
(180, 25, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 17:10:18', 'general'),
(181, 27, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 17:10:19', 'general'),
(182, 28, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 17:10:20', 'general'),
(183, 32, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 17:10:21', 'general'),
(184, 33, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 17:10:22', 'general'),
(185, 34, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 17:10:22', 'general'),
(186, 39, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 17:10:22', 'general'),
(187, 40, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 17:10:23', 'general'),
(188, 52, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 17:10:23', 'general'),
(189, 53, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 17:10:23', 'general'),
(190, 54, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 17:10:23', 'general'),
(191, 58, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 17:10:24', 'general'),
(192, 77, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 17:10:24', 'general'),
(193, 78, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 17:10:24', 'general'),
(194, 79, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 17:10:26', 'general'),
(195, 81, 'New book fikir (Edition 1) added successfully!', 'read', '2025-02-26 17:10:27', 'general'),
(196, 83, 'New book fikir (Edition 1) added successfully!', 'unread', '2025-02-26 17:10:28', 'general'),
(197, 13, 'New copy of fikir (Edition 1, Copy 2) added successfully!', 'read', '2025-02-26 17:40:58', 'general'),
(198, 16, 'New copy of fikir (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 17:40:58', 'general'),
(199, 25, 'New copy of fikir (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 17:40:58', 'general'),
(200, 27, 'New copy of fikir (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 17:40:58', 'general'),
(201, 28, 'New copy of fikir (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 17:40:58', 'general'),
(202, 32, 'New copy of fikir (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 17:40:58', 'general'),
(203, 33, 'New copy of fikir (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 17:40:58', 'general'),
(204, 34, 'New copy of fikir (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 17:40:58', 'general'),
(205, 39, 'New copy of fikir (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 17:40:58', 'general'),
(206, 40, 'New copy of fikir (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 17:40:58', 'general'),
(207, 52, 'New copy of fikir (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 17:40:58', 'general'),
(208, 53, 'New copy of fikir (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 17:40:58', 'general'),
(209, 54, 'New copy of fikir (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 17:40:59', 'general'),
(210, 58, 'New copy of fikir (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 17:40:59', 'general'),
(211, 77, 'New copy of fikir (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 17:40:59', 'general'),
(212, 78, 'New copy of fikir (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 17:40:59', 'general'),
(213, 79, 'New copy of fikir (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 17:40:59', 'general'),
(214, 81, 'New copy of fikir (Edition 1, Copy 2) added successfully!', 'read', '2025-02-26 17:40:59', 'general'),
(215, 83, 'New copy of fikir (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 17:40:59', 'general'),
(216, 13, 'New copy of fikir (Edition 1, Copy 3) added successfully!', 'read', '2025-02-26 17:54:01', 'general'),
(217, 16, 'New copy of fikir (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-26 17:54:01', 'general'),
(218, 25, 'New copy of fikir (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-26 17:54:01', 'general'),
(219, 27, 'New copy of fikir (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-26 17:54:01', 'general'),
(220, 28, 'New copy of fikir (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-26 17:54:01', 'general'),
(221, 32, 'New copy of fikir (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-26 17:54:02', 'general'),
(222, 33, 'New copy of fikir (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-26 17:54:02', 'general'),
(223, 34, 'New copy of fikir (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-26 17:54:02', 'general'),
(224, 39, 'New copy of fikir (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-26 17:54:02', 'general'),
(225, 40, 'New copy of fikir (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-26 17:54:02', 'general'),
(226, 52, 'New copy of fikir (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-26 17:54:02', 'general'),
(227, 53, 'New copy of fikir (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-26 17:54:02', 'general'),
(228, 54, 'New copy of fikir (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-26 17:54:02', 'general'),
(229, 58, 'New copy of fikir (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-26 17:54:02', 'general'),
(230, 77, 'New copy of fikir (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-26 17:54:02', 'general'),
(231, 78, 'New copy of fikir (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-26 17:54:02', 'general'),
(232, 79, 'New copy of fikir (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-26 17:54:03', 'general'),
(233, 81, 'New copy of fikir (Edition 1, Copy 3) added successfully!', 'read', '2025-02-26 17:54:03', 'general'),
(234, 83, 'New copy of fikir (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-26 17:54:03', 'general'),
(235, 13, 'New copy of fikirs (Edition 1, Copy 1) added successfully!', 'read', '2025-02-26 17:56:30', 'general'),
(236, 16, 'New copy of fikirs (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 17:56:30', 'general'),
(237, 25, 'New copy of fikirs (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 17:56:30', 'general'),
(238, 27, 'New copy of fikirs (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 17:56:30', 'general'),
(239, 28, 'New copy of fikirs (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 17:56:30', 'general'),
(240, 32, 'New copy of fikirs (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 17:56:30', 'general'),
(241, 33, 'New copy of fikirs (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 17:56:30', 'general'),
(242, 34, 'New copy of fikirs (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 17:56:30', 'general'),
(243, 39, 'New copy of fikirs (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 17:56:30', 'general'),
(244, 40, 'New copy of fikirs (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 17:56:31', 'general'),
(245, 52, 'New copy of fikirs (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 17:56:31', 'general'),
(246, 53, 'New copy of fikirs (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 17:56:31', 'general'),
(247, 54, 'New copy of fikirs (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 17:56:31', 'general'),
(248, 58, 'New copy of fikirs (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 17:56:31', 'general'),
(249, 77, 'New copy of fikirs (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 17:56:31', 'general'),
(250, 78, 'New copy of fikirs (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 17:56:31', 'general'),
(251, 79, 'New copy of fikirs (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 17:56:31', 'general'),
(252, 81, 'New copy of fikirs (Edition 1, Copy 1) added successfully!', 'read', '2025-02-26 17:56:31', 'general'),
(253, 83, 'New copy of fikirs (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 17:56:31', 'general'),
(254, 13, 'New copy of kemankes (Edition 1, Copy 1) added successfully!', 'read', '2025-02-26 18:13:41', 'general'),
(255, 16, 'New copy of kemankes (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:13:41', 'general'),
(256, 25, 'New copy of kemankes (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:13:41', 'general'),
(257, 27, 'New copy of kemankes (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:13:41', 'general'),
(258, 28, 'New copy of kemankes (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:13:41', 'general'),
(259, 32, 'New copy of kemankes (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:13:41', 'general'),
(260, 33, 'New copy of kemankes (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:13:42', 'general'),
(261, 34, 'New copy of kemankes (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:13:42', 'general'),
(262, 39, 'New copy of kemankes (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:13:42', 'general'),
(263, 40, 'New copy of kemankes (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:13:42', 'general'),
(264, 52, 'New copy of kemankes (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:13:42', 'general'),
(265, 53, 'New copy of kemankes (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:13:42', 'general'),
(266, 54, 'New copy of kemankes (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:13:42', 'general'),
(267, 58, 'New copy of kemankes (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:13:42', 'general'),
(268, 77, 'New copy of kemankes (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:13:42', 'general'),
(269, 78, 'New copy of kemankes (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:13:42', 'general'),
(270, 79, 'New copy of kemankes (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:13:42', 'general'),
(271, 81, 'New copy of kemankes (Edition 1, Copy 1) added successfully!', 'read', '2025-02-26 18:13:42', 'general'),
(272, 83, 'New copy of kemankes (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:13:42', 'general'),
(273, 13, 'New copy of kemankes (Edition 1, Copy 2) added successfully!', 'read', '2025-02-26 18:14:58', 'general'),
(274, 16, 'New copy of kemankes (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:14:58', 'general'),
(275, 25, 'New copy of kemankes (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:14:59', 'general'),
(276, 27, 'New copy of kemankes (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:14:59', 'general'),
(277, 28, 'New copy of kemankes (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:14:59', 'general'),
(278, 32, 'New copy of kemankes (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:14:59', 'general'),
(279, 33, 'New copy of kemankes (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:14:59', 'general'),
(280, 34, 'New copy of kemankes (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:14:59', 'general'),
(281, 39, 'New copy of kemankes (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:14:59', 'general'),
(282, 40, 'New copy of kemankes (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:14:59', 'general'),
(283, 52, 'New copy of kemankes (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:14:59', 'general'),
(284, 53, 'New copy of kemankes (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:14:59', 'general'),
(285, 54, 'New copy of kemankes (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:14:59', 'general'),
(286, 58, 'New copy of kemankes (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:14:59', 'general'),
(287, 77, 'New copy of kemankes (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:14:59', 'general'),
(288, 78, 'New copy of kemankes (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:14:59', 'general'),
(289, 79, 'New copy of kemankes (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:14:59', 'general'),
(290, 81, 'New copy of kemankes (Edition 1, Copy 2) added successfully!', 'read', '2025-02-26 18:15:00', 'general'),
(291, 83, 'New copy of kemankes (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:15:00', 'general'),
(292, 13, 'New copy of kemank (Edition 1, Copy 1) added successfully!', 'read', '2025-02-26 18:17:00', 'general'),
(293, 16, 'New copy of kemank (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:17:00', 'general'),
(294, 25, 'New copy of kemank (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:17:00', 'general'),
(295, 27, 'New copy of kemank (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:17:00', 'general'),
(296, 28, 'New copy of kemank (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:17:00', 'general'),
(297, 32, 'New copy of kemank (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:17:00', 'general'),
(298, 33, 'New copy of kemank (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:17:00', 'general'),
(299, 34, 'New copy of kemank (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:17:00', 'general'),
(300, 39, 'New copy of kemank (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:17:00', 'general'),
(301, 40, 'New copy of kemank (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:17:00', 'general'),
(302, 52, 'New copy of kemank (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:17:01', 'general'),
(303, 53, 'New copy of kemank (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:17:01', 'general'),
(304, 54, 'New copy of kemank (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:17:01', 'general'),
(305, 58, 'New copy of kemank (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:17:01', 'general'),
(306, 77, 'New copy of kemank (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:17:01', 'general'),
(307, 78, 'New copy of kemank (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:17:01', 'general'),
(308, 79, 'New copy of kemank (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:17:01', 'general'),
(309, 81, 'New copy of kemank (Edition 1, Copy 1) added successfully!', 'read', '2025-02-26 18:17:01', 'general'),
(310, 83, 'New copy of kemank (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:17:01', 'general'),
(311, 13, 'New copy of kemankesjhg (Edition 1, Copy 1) added successfully!', 'read', '2025-02-26 18:36:44', 'general'),
(312, 16, 'New copy of kemankesjhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:36:44', 'general'),
(313, 25, 'New copy of kemankesjhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:36:45', 'general'),
(314, 27, 'New copy of kemankesjhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:36:45', 'general'),
(315, 28, 'New copy of kemankesjhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:36:45', 'general'),
(316, 32, 'New copy of kemankesjhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:36:45', 'general'),
(317, 33, 'New copy of kemankesjhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:36:45', 'general'),
(318, 34, 'New copy of kemankesjhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:36:45', 'general'),
(319, 39, 'New copy of kemankesjhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:36:45', 'general'),
(320, 40, 'New copy of kemankesjhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:36:45', 'general'),
(321, 52, 'New copy of kemankesjhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:36:45', 'general'),
(322, 53, 'New copy of kemankesjhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:36:45', 'general'),
(323, 54, 'New copy of kemankesjhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:36:45', 'general'),
(324, 58, 'New copy of kemankesjhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:36:45', 'general'),
(325, 77, 'New copy of kemankesjhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:36:45', 'general'),
(326, 78, 'New copy of kemankesjhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:36:45', 'general'),
(327, 79, 'New copy of kemankesjhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:36:45', 'general'),
(328, 81, 'New copy of kemankesjhg (Edition 1, Copy 1) added successfully!', 'read', '2025-02-26 18:36:45', 'general'),
(329, 83, 'New copy of kemankesjhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:36:46', 'general'),
(330, 13, 'New copy of kemankesjhg (Edition 1, Copy 2) added successfully!', 'read', '2025-02-26 18:53:02', 'general'),
(331, 16, 'New copy of kemankesjhg (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:53:02', 'general'),
(332, 25, 'New copy of kemankesjhg (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:53:02', 'general'),
(333, 27, 'New copy of kemankesjhg (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:53:02', 'general'),
(334, 28, 'New copy of kemankesjhg (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:53:02', 'general'),
(335, 32, 'New copy of kemankesjhg (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:53:02', 'general'),
(336, 33, 'New copy of kemankesjhg (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:53:02', 'general'),
(337, 34, 'New copy of kemankesjhg (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:53:02', 'general'),
(338, 39, 'New copy of kemankesjhg (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:53:02', 'general'),
(339, 40, 'New copy of kemankesjhg (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:53:02', 'general'),
(340, 52, 'New copy of kemankesjhg (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:53:02', 'general'),
(341, 53, 'New copy of kemankesjhg (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:53:02', 'general'),
(342, 54, 'New copy of kemankesjhg (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:53:02', 'general'),
(343, 58, 'New copy of kemankesjhg (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:53:02', 'general'),
(344, 77, 'New copy of kemankesjhg (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:53:02', 'general'),
(345, 78, 'New copy of kemankesjhg (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:53:03', 'general'),
(346, 79, 'New copy of kemankesjhg (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:53:03', 'general'),
(347, 81, 'New copy of kemankesjhg (Edition 1, Copy 2) added successfully!', 'read', '2025-02-26 18:53:03', 'general'),
(348, 83, 'New copy of kemankesjhg (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 18:53:03', 'general'),
(349, 13, 'New copy of kemankesjh (Edition 1, Copy 1) added successfully!', 'read', '2025-02-26 18:59:17', 'general'),
(350, 16, 'New copy of kemankesjh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:59:17', 'general'),
(351, 25, 'New copy of kemankesjh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:59:17', 'general'),
(352, 27, 'New copy of kemankesjh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:59:17', 'general'),
(353, 28, 'New copy of kemankesjh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:59:17', 'general'),
(354, 32, 'New copy of kemankesjh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:59:17', 'general'),
(355, 33, 'New copy of kemankesjh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:59:17', 'general'),
(356, 34, 'New copy of kemankesjh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:59:17', 'general'),
(357, 39, 'New copy of kemankesjh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:59:17', 'general'),
(358, 40, 'New copy of kemankesjh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:59:17', 'general'),
(359, 52, 'New copy of kemankesjh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:59:17', 'general'),
(360, 53, 'New copy of kemankesjh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:59:17', 'general'),
(361, 54, 'New copy of kemankesjh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:59:17', 'general'),
(362, 58, 'New copy of kemankesjh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:59:17', 'general'),
(363, 77, 'New copy of kemankesjh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:59:18', 'general'),
(364, 78, 'New copy of kemankesjh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:59:18', 'general'),
(365, 79, 'New copy of kemankesjh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:59:18', 'general'),
(366, 81, 'New copy of kemankesjh (Edition 1, Copy 1) added successfully!', 'read', '2025-02-26 18:59:18', 'general'),
(367, 83, 'New copy of kemankesjh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-26 18:59:18', 'general'),
(368, 13, 'New copy of kemankesjh (Edition 1, Copy 2) added successfully!', 'read', '2025-02-26 19:01:14', 'general'),
(369, 16, 'New copy of kemankesjh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 19:01:14', 'general'),
(370, 25, 'New copy of kemankesjh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 19:01:14', 'general'),
(371, 27, 'New copy of kemankesjh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 19:01:14', 'general'),
(372, 28, 'New copy of kemankesjh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 19:01:14', 'general'),
(373, 32, 'New copy of kemankesjh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 19:01:14', 'general'),
(374, 33, 'New copy of kemankesjh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 19:01:14', 'general'),
(375, 34, 'New copy of kemankesjh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 19:01:14', 'general'),
(376, 39, 'New copy of kemankesjh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 19:01:14', 'general'),
(377, 40, 'New copy of kemankesjh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 19:01:14', 'general'),
(378, 52, 'New copy of kemankesjh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 19:01:14', 'general'),
(379, 53, 'New copy of kemankesjh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 19:01:14', 'general'),
(380, 54, 'New copy of kemankesjh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 19:01:14', 'general'),
(381, 58, 'New copy of kemankesjh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 19:01:14', 'general'),
(382, 77, 'New copy of kemankesjh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 19:01:15', 'general'),
(383, 78, 'New copy of kemankesjh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 19:01:15', 'general'),
(384, 79, 'New copy of kemankesjh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 19:01:15', 'general'),
(385, 81, 'New copy of kemankesjh (Edition 1, Copy 2) added successfully!', 'read', '2025-02-26 19:01:15', 'general'),
(386, 83, 'New copy of kemankesjh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-26 19:01:15', 'general'),
(387, 13, 'New copy of kemankesjh (Edition 1, Copy 3) added successfully!', 'read', '2025-02-28 18:51:25', 'general'),
(388, 16, 'New copy of kemankesjh (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 18:51:25', 'general'),
(389, 25, 'New copy of kemankesjh (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 18:51:25', 'general'),
(390, 27, 'New copy of kemankesjh (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 18:51:26', 'general'),
(391, 28, 'New copy of kemankesjh (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 18:51:26', 'general'),
(392, 32, 'New copy of kemankesjh (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 18:51:26', 'general'),
(393, 33, 'New copy of kemankesjh (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 18:51:26', 'general'),
(394, 34, 'New copy of kemankesjh (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 18:51:26', 'general'),
(395, 39, 'New copy of kemankesjh (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 18:51:26', 'general'),
(396, 40, 'New copy of kemankesjh (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 18:51:26', 'general'),
(397, 52, 'New copy of kemankesjh (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 18:51:26', 'general'),
(398, 53, 'New copy of kemankesjh (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 18:51:26', 'general'),
(399, 54, 'New copy of kemankesjh (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 18:51:26', 'general'),
(400, 58, 'New copy of kemankesjh (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 18:51:26', 'general'),
(401, 77, 'New copy of kemankesjh (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 18:51:26', 'general'),
(402, 78, 'New copy of kemankesjh (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 18:51:26', 'general'),
(403, 79, 'New copy of kemankesjh (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 18:51:26', 'general'),
(404, 81, 'New copy of kemankesjh (Edition 1, Copy 3) added successfully!', 'read', '2025-02-28 18:51:26', 'general'),
(405, 83, 'New copy of kemankesjh (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 18:51:26', 'general'),
(406, 13, 'New copy of kemankesj (Edition 1, Copy 1) added successfully!', 'read', '2025-02-28 18:52:58', 'general'),
(407, 16, 'New copy of kemankesj (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:52:59', 'general'),
(408, 25, 'New copy of kemankesj (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:52:59', 'general'),
(409, 27, 'New copy of kemankesj (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:52:59', 'general'),
(410, 28, 'New copy of kemankesj (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:52:59', 'general'),
(411, 32, 'New copy of kemankesj (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:52:59', 'general'),
(412, 33, 'New copy of kemankesj (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:52:59', 'general'),
(413, 34, 'New copy of kemankesj (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:52:59', 'general'),
(414, 39, 'New copy of kemankesj (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:52:59', 'general'),
(415, 40, 'New copy of kemankesj (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:52:59', 'general'),
(416, 52, 'New copy of kemankesj (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:52:59', 'general'),
(417, 53, 'New copy of kemankesj (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:52:59', 'general'),
(418, 54, 'New copy of kemankesj (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:52:59', 'general'),
(419, 58, 'New copy of kemankesj (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:52:59', 'general'),
(420, 77, 'New copy of kemankesj (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:52:59', 'general'),
(421, 78, 'New copy of kemankesj (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:53:00', 'general'),
(422, 79, 'New copy of kemankesj (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:53:00', 'general'),
(423, 81, 'New copy of kemankesj (Edition 1, Copy 1) added successfully!', 'read', '2025-02-28 18:53:00', 'general'),
(424, 83, 'New copy of kemankesj (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:53:00', 'general'),
(425, 13, 'New copy of kemal (Edition 1, Copy 1) added successfully!', 'read', '2025-02-28 18:55:12', 'general'),
(426, 16, 'New copy of kemal (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:55:12', 'general'),
(427, 25, 'New copy of kemal (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:55:12', 'general'),
(428, 27, 'New copy of kemal (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:55:12', 'general'),
(429, 28, 'New copy of kemal (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:55:12', 'general'),
(430, 32, 'New copy of kemal (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:55:12', 'general'),
(431, 33, 'New copy of kemal (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:55:12', 'general'),
(432, 34, 'New copy of kemal (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:55:12', 'general'),
(433, 39, 'New copy of kemal (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:55:12', 'general'),
(434, 40, 'New copy of kemal (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:55:12', 'general'),
(435, 52, 'New copy of kemal (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:55:12', 'general'),
(436, 53, 'New copy of kemal (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:55:12', 'general'),
(437, 54, 'New copy of kemal (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:55:12', 'general'),
(438, 58, 'New copy of kemal (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:55:13', 'general'),
(439, 77, 'New copy of kemal (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:55:13', 'general'),
(440, 78, 'New copy of kemal (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:55:13', 'general'),
(441, 79, 'New copy of kemal (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:55:13', 'general'),
(442, 81, 'New copy of kemal (Edition 1, Copy 1) added successfully!', 'read', '2025-02-28 18:55:13', 'general'),
(443, 83, 'New copy of kemal (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 18:55:13', 'general'),
(444, 13, 'New copy of kema (Edition 1, Copy 1) added successfully!', 'read', '2025-02-28 19:18:47', 'general'),
(445, 16, 'New copy of kema (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 19:18:47', 'general'),
(446, 25, 'New copy of kema (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 19:18:47', 'general'),
(447, 27, 'New copy of kema (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 19:18:47', 'general'),
(448, 28, 'New copy of kema (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 19:18:47', 'general'),
(449, 32, 'New copy of kema (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 19:18:48', 'general'),
(450, 33, 'New copy of kema (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 19:18:48', 'general'),
(451, 34, 'New copy of kema (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 19:18:48', 'general'),
(452, 39, 'New copy of kema (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 19:18:48', 'general'),
(453, 40, 'New copy of kema (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 19:18:48', 'general'),
(454, 52, 'New copy of kema (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 19:18:48', 'general'),
(455, 53, 'New copy of kema (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 19:18:48', 'general'),
(456, 54, 'New copy of kema (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 19:18:48', 'general'),
(457, 58, 'New copy of kema (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 19:18:48', 'general'),
(458, 77, 'New copy of kema (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 19:18:48', 'general'),
(459, 78, 'New copy of kema (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 19:18:48', 'general');
INSERT INTO `notifications` (`id`, `user_id`, `message`, `status`, `created_at`, `type`) VALUES
(460, 79, 'New copy of kema (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 19:18:48', 'general'),
(461, 81, 'New copy of kema (Edition 1, Copy 1) added successfully!', 'read', '2025-02-28 19:18:48', 'general'),
(462, 83, 'New copy of kema (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 19:18:48', 'general'),
(463, 13, 'New copy of kema (Edition 1, Copy 2) added successfully!', 'read', '2025-02-28 19:30:42', 'general'),
(464, 16, 'New copy of kema (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 19:30:42', 'general'),
(465, 25, 'New copy of kema (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 19:30:42', 'general'),
(466, 27, 'New copy of kema (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 19:30:42', 'general'),
(467, 28, 'New copy of kema (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 19:30:42', 'general'),
(468, 32, 'New copy of kema (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 19:30:42', 'general'),
(469, 33, 'New copy of kema (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 19:30:42', 'general'),
(470, 34, 'New copy of kema (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 19:30:42', 'general'),
(471, 39, 'New copy of kema (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 19:30:42', 'general'),
(472, 40, 'New copy of kema (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 19:30:42', 'general'),
(473, 52, 'New copy of kema (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 19:30:42', 'general'),
(474, 53, 'New copy of kema (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 19:30:42', 'general'),
(475, 54, 'New copy of kema (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 19:30:42', 'general'),
(476, 58, 'New copy of kema (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 19:30:43', 'general'),
(477, 77, 'New copy of kema (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 19:30:43', 'general'),
(478, 78, 'New copy of kema (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 19:30:43', 'general'),
(479, 79, 'New copy of kema (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 19:30:43', 'general'),
(480, 81, 'New copy of kema (Edition 1, Copy 2) added successfully!', 'read', '2025-02-28 19:30:43', 'general'),
(481, 83, 'New copy of kema (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 19:30:43', 'general'),
(482, 13, 'New copy of kema (Edition 1, Copy 3) added successfully!', 'read', '2025-02-28 19:31:17', 'general'),
(483, 16, 'New copy of kema (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 19:31:17', 'general'),
(484, 25, 'New copy of kema (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 19:31:17', 'general'),
(485, 27, 'New copy of kema (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 19:31:17', 'general'),
(486, 28, 'New copy of kema (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 19:31:17', 'general'),
(487, 32, 'New copy of kema (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 19:31:17', 'general'),
(488, 33, 'New copy of kema (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 19:31:17', 'general'),
(489, 34, 'New copy of kema (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 19:31:17', 'general'),
(490, 39, 'New copy of kema (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 19:31:17', 'general'),
(491, 40, 'New copy of kema (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 19:31:17', 'general'),
(492, 52, 'New copy of kema (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 19:31:18', 'general'),
(493, 53, 'New copy of kema (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 19:31:18', 'general'),
(494, 54, 'New copy of kema (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 19:31:18', 'general'),
(495, 58, 'New copy of kema (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 19:31:18', 'general'),
(496, 77, 'New copy of kema (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 19:31:18', 'general'),
(497, 78, 'New copy of kema (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 19:31:18', 'general'),
(498, 79, 'New copy of kema (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 19:31:18', 'general'),
(499, 81, 'New copy of kema (Edition 1, Copy 3) added successfully!', 'read', '2025-02-28 19:31:18', 'general'),
(500, 83, 'New copy of kema (Edition 1, Copy 3) added successfully!', 'unread', '2025-02-28 19:31:18', 'general'),
(501, 13, 'New copy of kema (Edition 1, Copy 4) added successfully!', 'read', '2025-02-28 19:34:21', 'general'),
(502, 16, 'New copy of kema (Edition 1, Copy 4) added successfully!', 'unread', '2025-02-28 19:34:21', 'general'),
(503, 25, 'New copy of kema (Edition 1, Copy 4) added successfully!', 'unread', '2025-02-28 19:34:21', 'general'),
(504, 27, 'New copy of kema (Edition 1, Copy 4) added successfully!', 'unread', '2025-02-28 19:34:21', 'general'),
(505, 28, 'New copy of kema (Edition 1, Copy 4) added successfully!', 'unread', '2025-02-28 19:34:21', 'general'),
(506, 32, 'New copy of kema (Edition 1, Copy 4) added successfully!', 'unread', '2025-02-28 19:34:21', 'general'),
(507, 33, 'New copy of kema (Edition 1, Copy 4) added successfully!', 'unread', '2025-02-28 19:34:22', 'general'),
(508, 34, 'New copy of kema (Edition 1, Copy 4) added successfully!', 'unread', '2025-02-28 19:34:22', 'general'),
(509, 39, 'New copy of kema (Edition 1, Copy 4) added successfully!', 'unread', '2025-02-28 19:34:22', 'general'),
(510, 40, 'New copy of kema (Edition 1, Copy 4) added successfully!', 'unread', '2025-02-28 19:34:22', 'general'),
(511, 52, 'New copy of kema (Edition 1, Copy 4) added successfully!', 'unread', '2025-02-28 19:34:22', 'general'),
(512, 53, 'New copy of kema (Edition 1, Copy 4) added successfully!', 'unread', '2025-02-28 19:34:23', 'general'),
(513, 54, 'New copy of kema (Edition 1, Copy 4) added successfully!', 'unread', '2025-02-28 19:34:23', 'general'),
(514, 58, 'New copy of kema (Edition 1, Copy 4) added successfully!', 'unread', '2025-02-28 19:34:23', 'general'),
(515, 77, 'New copy of kema (Edition 1, Copy 4) added successfully!', 'unread', '2025-02-28 19:34:23', 'general'),
(516, 78, 'New copy of kema (Edition 1, Copy 4) added successfully!', 'unread', '2025-02-28 19:34:23', 'general'),
(517, 79, 'New copy of kema (Edition 1, Copy 4) added successfully!', 'unread', '2025-02-28 19:34:23', 'general'),
(518, 81, 'New copy of kema (Edition 1, Copy 4) added successfully!', 'read', '2025-02-28 19:34:23', 'general'),
(519, 83, 'New copy of kema (Edition 1, Copy 4) added successfully!', 'unread', '2025-02-28 19:34:23', 'general'),
(520, 13, 'New copy of kema (Edition 1, Copy 5) added successfully!', 'read', '2025-02-28 19:34:53', 'general'),
(521, 16, 'New copy of kema (Edition 1, Copy 5) added successfully!', 'unread', '2025-02-28 19:34:53', 'general'),
(522, 25, 'New copy of kema (Edition 1, Copy 5) added successfully!', 'unread', '2025-02-28 19:34:53', 'general'),
(523, 27, 'New copy of kema (Edition 1, Copy 5) added successfully!', 'unread', '2025-02-28 19:34:53', 'general'),
(524, 28, 'New copy of kema (Edition 1, Copy 5) added successfully!', 'unread', '2025-02-28 19:34:53', 'general'),
(525, 32, 'New copy of kema (Edition 1, Copy 5) added successfully!', 'unread', '2025-02-28 19:34:53', 'general'),
(526, 33, 'New copy of kema (Edition 1, Copy 5) added successfully!', 'unread', '2025-02-28 19:34:53', 'general'),
(527, 34, 'New copy of kema (Edition 1, Copy 5) added successfully!', 'unread', '2025-02-28 19:34:53', 'general'),
(528, 39, 'New copy of kema (Edition 1, Copy 5) added successfully!', 'unread', '2025-02-28 19:34:53', 'general'),
(529, 40, 'New copy of kema (Edition 1, Copy 5) added successfully!', 'unread', '2025-02-28 19:34:53', 'general'),
(530, 52, 'New copy of kema (Edition 1, Copy 5) added successfully!', 'unread', '2025-02-28 19:34:53', 'general'),
(531, 53, 'New copy of kema (Edition 1, Copy 5) added successfully!', 'unread', '2025-02-28 19:34:53', 'general'),
(532, 54, 'New copy of kema (Edition 1, Copy 5) added successfully!', 'unread', '2025-02-28 19:34:53', 'general'),
(533, 58, 'New copy of kema (Edition 1, Copy 5) added successfully!', 'unread', '2025-02-28 19:34:54', 'general'),
(534, 77, 'New copy of kema (Edition 1, Copy 5) added successfully!', 'unread', '2025-02-28 19:34:54', 'general'),
(535, 78, 'New copy of kema (Edition 1, Copy 5) added successfully!', 'unread', '2025-02-28 19:34:54', 'general'),
(536, 79, 'New copy of kema (Edition 1, Copy 5) added successfully!', 'unread', '2025-02-28 19:34:54', 'general'),
(537, 81, 'New copy of kema (Edition 1, Copy 5) added successfully!', 'read', '2025-02-28 19:34:54', 'general'),
(538, 83, 'New copy of kema (Edition 1, Copy 5) added successfully!', 'unread', '2025-02-28 19:34:54', 'general'),
(539, 13, 'New copy of kema (Edition 1, Copy 6) added successfully!', 'read', '2025-02-28 20:04:47', 'general'),
(540, 16, 'New copy of kema (Edition 1, Copy 6) added successfully!', 'unread', '2025-02-28 20:04:47', 'general'),
(541, 25, 'New copy of kema (Edition 1, Copy 6) added successfully!', 'unread', '2025-02-28 20:04:47', 'general'),
(542, 27, 'New copy of kema (Edition 1, Copy 6) added successfully!', 'unread', '2025-02-28 20:04:47', 'general'),
(543, 28, 'New copy of kema (Edition 1, Copy 6) added successfully!', 'unread', '2025-02-28 20:04:48', 'general'),
(544, 32, 'New copy of kema (Edition 1, Copy 6) added successfully!', 'unread', '2025-02-28 20:04:48', 'general'),
(545, 33, 'New copy of kema (Edition 1, Copy 6) added successfully!', 'unread', '2025-02-28 20:04:48', 'general'),
(546, 34, 'New copy of kema (Edition 1, Copy 6) added successfully!', 'unread', '2025-02-28 20:04:48', 'general'),
(547, 39, 'New copy of kema (Edition 1, Copy 6) added successfully!', 'unread', '2025-02-28 20:04:48', 'general'),
(548, 40, 'New copy of kema (Edition 1, Copy 6) added successfully!', 'unread', '2025-02-28 20:04:48', 'general'),
(549, 52, 'New copy of kema (Edition 1, Copy 6) added successfully!', 'unread', '2025-02-28 20:04:48', 'general'),
(550, 53, 'New copy of kema (Edition 1, Copy 6) added successfully!', 'unread', '2025-02-28 20:04:48', 'general'),
(551, 54, 'New copy of kema (Edition 1, Copy 6) added successfully!', 'unread', '2025-02-28 20:04:48', 'general'),
(552, 58, 'New copy of kema (Edition 1, Copy 6) added successfully!', 'unread', '2025-02-28 20:04:48', 'general'),
(553, 77, 'New copy of kema (Edition 1, Copy 6) added successfully!', 'unread', '2025-02-28 20:04:48', 'general'),
(554, 78, 'New copy of kema (Edition 1, Copy 6) added successfully!', 'unread', '2025-02-28 20:04:48', 'general'),
(555, 79, 'New copy of kema (Edition 1, Copy 6) added successfully!', 'unread', '2025-02-28 20:04:48', 'general'),
(556, 81, 'New copy of kema (Edition 1, Copy 6) added successfully!', 'read', '2025-02-28 20:04:48', 'general'),
(557, 83, 'New copy of kema (Edition 1, Copy 6) added successfully!', 'unread', '2025-02-28 20:04:48', 'general'),
(558, 13, 'New copy of kemagh (Edition 1, Copy 1) added successfully!', 'read', '2025-02-28 20:05:22', 'general'),
(559, 16, 'New copy of kemagh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 20:05:22', 'general'),
(560, 25, 'New copy of kemagh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 20:05:22', 'general'),
(561, 27, 'New copy of kemagh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 20:05:22', 'general'),
(562, 28, 'New copy of kemagh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 20:05:23', 'general'),
(563, 32, 'New copy of kemagh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 20:05:23', 'general'),
(564, 33, 'New copy of kemagh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 20:05:23', 'general'),
(565, 34, 'New copy of kemagh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 20:05:23', 'general'),
(566, 39, 'New copy of kemagh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 20:05:23', 'general'),
(567, 40, 'New copy of kemagh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 20:05:23', 'general'),
(568, 52, 'New copy of kemagh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 20:05:23', 'general'),
(569, 53, 'New copy of kemagh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 20:05:23', 'general'),
(570, 54, 'New copy of kemagh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 20:05:23', 'general'),
(571, 58, 'New copy of kemagh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 20:05:23', 'general'),
(572, 77, 'New copy of kemagh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 20:05:23', 'general'),
(573, 78, 'New copy of kemagh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 20:05:23', 'general'),
(574, 79, 'New copy of kemagh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 20:05:23', 'general'),
(575, 81, 'New copy of kemagh (Edition 1, Copy 1) added successfully!', 'read', '2025-02-28 20:05:23', 'general'),
(576, 83, 'New copy of kemagh (Edition 1, Copy 1) added successfully!', 'unread', '2025-02-28 20:05:23', 'general'),
(577, 13, 'New copy of kemagh (Edition 1, Copy 2) added successfully!', 'read', '2025-02-28 20:06:39', 'general'),
(578, 16, 'New copy of kemagh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 20:06:39', 'general'),
(579, 25, 'New copy of kemagh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 20:06:39', 'general'),
(580, 27, 'New copy of kemagh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 20:06:39', 'general'),
(581, 28, 'New copy of kemagh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 20:06:39', 'general'),
(582, 32, 'New copy of kemagh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 20:06:40', 'general'),
(583, 33, 'New copy of kemagh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 20:06:40', 'general'),
(584, 34, 'New copy of kemagh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 20:06:40', 'general'),
(585, 39, 'New copy of kemagh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 20:06:40', 'general'),
(586, 40, 'New copy of kemagh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 20:06:40', 'general'),
(587, 52, 'New copy of kemagh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 20:06:40', 'general'),
(588, 53, 'New copy of kemagh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 20:06:40', 'general'),
(589, 54, 'New copy of kemagh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 20:06:40', 'general'),
(590, 58, 'New copy of kemagh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 20:06:40', 'general'),
(591, 77, 'New copy of kemagh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 20:06:40', 'general'),
(592, 78, 'New copy of kemagh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 20:06:40', 'general'),
(593, 79, 'New copy of kemagh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 20:06:40', 'general'),
(594, 81, 'New copy of kemagh (Edition 1, Copy 2) added successfully!', 'read', '2025-02-28 20:06:40', 'general'),
(595, 83, 'New copy of kemagh (Edition 1, Copy 2) added successfully!', 'unread', '2025-02-28 20:06:40', 'general'),
(596, 13, 'New copy of kemagh (Edition 1, Copy 3) added successfully!', 'read', '2025-03-01 06:39:15', 'general'),
(597, 16, 'New copy of kemagh (Edition 1, Copy 3) added successfully!', 'unread', '2025-03-01 06:39:15', 'general'),
(598, 25, 'New copy of kemagh (Edition 1, Copy 3) added successfully!', 'unread', '2025-03-01 06:39:15', 'general'),
(599, 27, 'New copy of kemagh (Edition 1, Copy 3) added successfully!', 'unread', '2025-03-01 06:39:15', 'general'),
(600, 28, 'New copy of kemagh (Edition 1, Copy 3) added successfully!', 'unread', '2025-03-01 06:39:15', 'general'),
(601, 32, 'New copy of kemagh (Edition 1, Copy 3) added successfully!', 'unread', '2025-03-01 06:39:15', 'general'),
(602, 33, 'New copy of kemagh (Edition 1, Copy 3) added successfully!', 'unread', '2025-03-01 06:39:15', 'general'),
(603, 34, 'New copy of kemagh (Edition 1, Copy 3) added successfully!', 'unread', '2025-03-01 06:39:15', 'general'),
(604, 39, 'New copy of kemagh (Edition 1, Copy 3) added successfully!', 'unread', '2025-03-01 06:39:15', 'general'),
(605, 40, 'New copy of kemagh (Edition 1, Copy 3) added successfully!', 'unread', '2025-03-01 06:39:15', 'general'),
(606, 52, 'New copy of kemagh (Edition 1, Copy 3) added successfully!', 'unread', '2025-03-01 06:39:15', 'general'),
(607, 53, 'New copy of kemagh (Edition 1, Copy 3) added successfully!', 'unread', '2025-03-01 06:39:15', 'general'),
(608, 54, 'New copy of kemagh (Edition 1, Copy 3) added successfully!', 'unread', '2025-03-01 06:39:15', 'general'),
(609, 58, 'New copy of kemagh (Edition 1, Copy 3) added successfully!', 'unread', '2025-03-01 06:39:16', 'general'),
(610, 77, 'New copy of kemagh (Edition 1, Copy 3) added successfully!', 'unread', '2025-03-01 06:39:16', 'general'),
(611, 78, 'New copy of kemagh (Edition 1, Copy 3) added successfully!', 'unread', '2025-03-01 06:39:16', 'general'),
(612, 79, 'New copy of kemagh (Edition 1, Copy 3) added successfully!', 'unread', '2025-03-01 06:39:16', 'general'),
(613, 81, 'New copy of kemagh (Edition 1, Copy 3) added successfully!', 'read', '2025-03-01 06:39:16', 'general'),
(614, 83, 'New copy of kemagh (Edition 1, Copy 3) added successfully!', 'unread', '2025-03-01 06:39:16', 'general'),
(615, 13, 'New copy of 8976 (Edition 1, Copy 1) added successfully!', 'read', '2025-03-01 07:34:43', 'general'),
(616, 16, 'New copy of 8976 (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:34:43', 'general'),
(617, 25, 'New copy of 8976 (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:34:44', 'general'),
(618, 27, 'New copy of 8976 (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:34:44', 'general'),
(619, 28, 'New copy of 8976 (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:34:44', 'general'),
(620, 32, 'New copy of 8976 (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:34:45', 'general'),
(621, 33, 'New copy of 8976 (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:34:47', 'general'),
(622, 34, 'New copy of 8976 (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:34:47', 'general'),
(623, 39, 'New copy of 8976 (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:34:47', 'general'),
(624, 40, 'New copy of 8976 (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:34:47', 'general'),
(625, 52, 'New copy of 8976 (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:34:48', 'general'),
(626, 53, 'New copy of 8976 (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:34:48', 'general'),
(627, 54, 'New copy of 8976 (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:34:48', 'general'),
(628, 58, 'New copy of 8976 (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:34:48', 'general'),
(629, 77, 'New copy of 8976 (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:34:48', 'general'),
(630, 78, 'New copy of 8976 (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:34:48', 'general'),
(631, 79, 'New copy of 8976 (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:34:48', 'general'),
(632, 81, 'New copy of 8976 (Edition 1, Copy 1) added successfully!', 'read', '2025-03-01 07:34:48', 'general'),
(633, 83, 'New copy of 8976 (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:34:48', 'general'),
(634, 13, 'New copy of jkhg (Edition 1, Copy 1) added successfully!', 'read', '2025-03-01 07:38:11', 'general'),
(635, 16, 'New copy of jkhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:38:12', 'general'),
(636, 25, 'New copy of jkhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:38:12', 'general'),
(637, 27, 'New copy of jkhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:38:12', 'general'),
(638, 28, 'New copy of jkhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:38:12', 'general'),
(639, 32, 'New copy of jkhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:38:12', 'general'),
(640, 33, 'New copy of jkhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:38:12', 'general'),
(641, 34, 'New copy of jkhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:38:12', 'general'),
(642, 39, 'New copy of jkhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:38:12', 'general'),
(643, 40, 'New copy of jkhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:38:12', 'general'),
(644, 52, 'New copy of jkhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:38:12', 'general'),
(645, 53, 'New copy of jkhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:38:12', 'general'),
(646, 54, 'New copy of jkhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:38:12', 'general'),
(647, 58, 'New copy of jkhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:38:12', 'general'),
(648, 77, 'New copy of jkhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:38:13', 'general'),
(649, 78, 'New copy of jkhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:38:13', 'general'),
(650, 79, 'New copy of jkhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:38:13', 'general'),
(651, 81, 'New copy of jkhg (Edition 1, Copy 1) added successfully!', 'read', '2025-03-01 07:38:13', 'general'),
(652, 83, 'New copy of jkhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 07:38:13', 'general'),
(653, 13, 'New copy of jhgf (Edition 1, Copy 1) added successfully!', 'read', '2025-03-01 08:01:45', 'general'),
(654, 16, 'New copy of jhgf (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:01:46', 'general'),
(655, 25, 'New copy of jhgf (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:01:46', 'general'),
(656, 27, 'New copy of jhgf (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:01:46', 'general'),
(657, 28, 'New copy of jhgf (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:01:46', 'general'),
(658, 32, 'New copy of jhgf (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:01:47', 'general'),
(659, 33, 'New copy of jhgf (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:01:47', 'general'),
(660, 34, 'New copy of jhgf (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:01:47', 'general'),
(661, 39, 'New copy of jhgf (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:01:47', 'general'),
(662, 40, 'New copy of jhgf (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:01:47', 'general'),
(663, 52, 'New copy of jhgf (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:01:47', 'general'),
(664, 53, 'New copy of jhgf (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:01:47', 'general'),
(665, 54, 'New copy of jhgf (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:01:47', 'general'),
(666, 58, 'New copy of jhgf (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:01:47', 'general'),
(667, 77, 'New copy of jhgf (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:01:47', 'general'),
(668, 78, 'New copy of jhgf (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:01:47', 'general'),
(669, 79, 'New copy of jhgf (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:01:47', 'general'),
(670, 81, 'New copy of jhgf (Edition 1, Copy 1) added successfully!', 'read', '2025-03-01 08:01:47', 'general'),
(671, 83, 'New copy of jhgf (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:01:47', 'general'),
(672, 13, 'New copy of jg (Edition 1, Copy 1) added successfully!', 'read', '2025-03-01 08:07:56', 'general'),
(673, 16, 'New copy of jg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:07:56', 'general'),
(674, 25, 'New copy of jg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:07:57', 'general'),
(675, 27, 'New copy of jg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:07:57', 'general'),
(676, 28, 'New copy of jg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:07:57', 'general'),
(677, 32, 'New copy of jg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:07:57', 'general'),
(678, 33, 'New copy of jg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:07:57', 'general'),
(679, 34, 'New copy of jg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:07:57', 'general'),
(680, 39, 'New copy of jg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:07:57', 'general'),
(681, 40, 'New copy of jg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:07:57', 'general'),
(682, 52, 'New copy of jg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:07:57', 'general'),
(683, 53, 'New copy of jg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:07:57', 'general'),
(684, 54, 'New copy of jg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:07:57', 'general'),
(685, 58, 'New copy of jg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:07:57', 'general'),
(686, 77, 'New copy of jg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:07:57', 'general'),
(687, 78, 'New copy of jg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:07:57', 'general'),
(688, 79, 'New copy of jg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:07:57', 'general'),
(689, 81, 'New copy of jg (Edition 1, Copy 1) added successfully!', 'read', '2025-03-01 08:07:57', 'general'),
(690, 83, 'New copy of jg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:07:57', 'general'),
(691, 13, 'New copy of kujhg (Edition 1, Copy 1) added successfully!', 'read', '2025-03-01 08:10:18', 'general'),
(692, 16, 'New copy of kujhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:10:18', 'general'),
(693, 25, 'New copy of kujhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:10:19', 'general'),
(694, 27, 'New copy of kujhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:10:19', 'general'),
(695, 28, 'New copy of kujhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:10:19', 'general'),
(696, 32, 'New copy of kujhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:10:19', 'general'),
(697, 33, 'New copy of kujhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:10:19', 'general'),
(698, 34, 'New copy of kujhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:10:19', 'general'),
(699, 39, 'New copy of kujhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:10:19', 'general'),
(700, 40, 'New copy of kujhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:10:20', 'general'),
(701, 52, 'New copy of kujhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:10:20', 'general'),
(702, 53, 'New copy of kujhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:10:20', 'general'),
(703, 54, 'New copy of kujhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:10:20', 'general'),
(704, 58, 'New copy of kujhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:10:20', 'general'),
(705, 77, 'New copy of kujhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:10:20', 'general'),
(706, 78, 'New copy of kujhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:10:20', 'general'),
(707, 79, 'New copy of kujhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:10:20', 'general'),
(708, 81, 'New copy of kujhg (Edition 1, Copy 1) added successfully!', 'read', '2025-03-01 08:10:20', 'general'),
(709, 83, 'New copy of kujhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 08:10:20', 'general'),
(710, 13, 'New copy of thfd (Edition 1, Copy 1) added successfully!', 'read', '2025-03-01 09:11:58', 'general'),
(711, 16, 'New copy of thfd (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:11:59', 'general'),
(712, 25, 'New copy of thfd (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:11:59', 'general'),
(713, 27, 'New copy of thfd (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:11:59', 'general'),
(714, 28, 'New copy of thfd (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:11:59', 'general'),
(715, 32, 'New copy of thfd (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:11:59', 'general'),
(716, 33, 'New copy of thfd (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:11:59', 'general'),
(717, 34, 'New copy of thfd (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:11:59', 'general'),
(718, 39, 'New copy of thfd (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:11:59', 'general'),
(719, 40, 'New copy of thfd (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:11:59', 'general'),
(720, 52, 'New copy of thfd (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:11:59', 'general'),
(721, 53, 'New copy of thfd (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:12:00', 'general'),
(722, 54, 'New copy of thfd (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:12:00', 'general'),
(723, 58, 'New copy of thfd (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:12:00', 'general'),
(724, 77, 'New copy of thfd (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:12:00', 'general'),
(725, 78, 'New copy of thfd (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:12:00', 'general'),
(726, 79, 'New copy of thfd (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:12:00', 'general'),
(727, 81, 'New copy of thfd (Edition 1, Copy 1) added successfully!', 'read', '2025-03-01 09:12:01', 'general'),
(728, 83, 'New copy of thfd (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:12:01', 'general'),
(729, 13, 'New copy of uhg (Edition 1, Copy 1) added successfully!', 'read', '2025-03-01 09:19:06', 'general'),
(730, 16, 'New copy of uhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:19:06', 'general'),
(731, 25, 'New copy of uhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:19:06', 'general'),
(732, 27, 'New copy of uhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:19:06', 'general'),
(733, 28, 'New copy of uhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:19:06', 'general'),
(734, 32, 'New copy of uhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:19:06', 'general'),
(735, 33, 'New copy of uhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:19:06', 'general'),
(736, 34, 'New copy of uhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:19:07', 'general'),
(737, 39, 'New copy of uhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:19:07', 'general'),
(738, 40, 'New copy of uhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:19:07', 'general'),
(739, 52, 'New copy of uhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:19:07', 'general'),
(740, 53, 'New copy of uhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:19:07', 'general'),
(741, 54, 'New copy of uhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:19:07', 'general'),
(742, 58, 'New copy of uhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:19:07', 'general'),
(743, 77, 'New copy of uhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:19:07', 'general'),
(744, 78, 'New copy of uhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:19:07', 'general'),
(745, 79, 'New copy of uhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:19:07', 'general'),
(746, 81, 'New copy of uhg (Edition 1, Copy 1) added successfully!', 'read', '2025-03-01 09:19:07', 'general'),
(747, 83, 'New copy of uhg (Edition 1, Copy 1) added successfully!', 'unread', '2025-03-01 09:19:07', 'general'),
(748, 13, 'Your reservation for \'To Kill a Mockingbird\' has expired.', 'read', '2025-03-01 09:21:17', 'general'),
(749, 16, 'Your borrowed book \'Javascript best\' is overdue!', 'unread', '2025-03-01 09:21:26', 'general'),
(750, 81, 'Your borrowed book \'esak\' is overdue!', 'read', '2025-03-01 09:21:29', 'general'),
(751, 81, 'Your fine for the book \'esak\' due to \'lost\' has been successfully paid.', 'read', '2025-03-03 12:41:07', 'general'),
(752, 82, 'report is sent to your email', 'read', '2025-03-03 15:12:37', 'general'),
(753, 41, 'A new library report has been sent to your email.', 'read', '2025-03-03 15:16:02', 'general'),
(754, 82, 'A new library report has been sent to your email.', 'read', '2025-03-03 15:16:07', 'general'),
(755, 41, 'A book has been borrowed from your branch.', 'read', '2025-03-03 15:43:22', 'general'),
(756, 81, 'Dear esak Ebrahim, your borrow request for Book ID: 41 has been confirmed. Please collect your book.', 'read', '2025-03-03 15:50:39', 'general'),
(757, 41, 'A book has been requested for borrowing.', 'read', '2025-03-03 15:57:07', 'general'),
(758, 81, 'Dear esak Ebrahim, your borrow request for the book \'munira\' has been confirmed. Please collect your book.', 'read', '2025-03-03 15:57:22', 'general'),
(759, 41, 'A book has been requested for borrowing.', 'read', '2025-03-03 17:10:07', 'general'),
(760, 81, 'Dear esak Ebrahim, your borrow request for the book \'The Great Gatsby\' has been rejected.', 'read', '2025-03-03 17:10:27', 'general'),
(761, 41, 'Student with ID 81 has requested to return the book (ID: 41).', 'read', '2025-03-03 19:00:36', 'general'),
(762, 41, 'esak Ebrahim has requested to return the book \'munira\'.', 'read', '2025-03-03 19:06:29', 'general'),
(763, 81, 'Your return request for the book \'munira\' has been rejected.', 'read', '2025-03-03 19:16:45', 'general'),
(764, 41, 'A book has been requested for borrowing.', 'read', '2025-03-06 14:20:15', 'general'),
(765, 41, 'A book has been requested for borrowing.', 'read', '2025-03-06 14:21:11', 'general'),
(766, 82, 'A book has been requested for borrowing.', 'read', '2025-03-06 14:27:11', 'general'),
(767, 82, 'A book has been requested for borrowing.', 'read', '2025-03-06 14:27:17', 'general'),
(768, 82, 'A book has been requested for borrowing.', 'read', '2025-03-06 14:30:11', 'general'),
(769, 82, 'A book has been requested for borrowing.', 'read', '2025-03-06 14:30:15', 'general'),
(770, 82, 'A book has been requested for borrowing.', 'read', '2025-03-06 14:33:59', 'general'),
(771, 82, 'A book has been requested for borrowing.', 'read', '2025-03-06 14:35:26', 'general'),
(772, 13, 'Dear bg, your borrow request for the book \'89\' has been rejected.', 'read', '2025-03-06 14:39:32', 'general'),
(773, 82, 'A book has been requested for borrowing.', 'read', '2025-03-06 14:39:45', 'general'),
(774, 13, 'Dear bg, your borrow request for the book \'ayda\' has been rejected.', 'read', '2025-03-06 14:40:09', 'general'),
(775, 82, 'A book has been requested for borrowing.', 'read', '2025-03-06 14:40:15', 'general'),
(776, 13, 'Dear bg, your borrow request for the book \'89\' has been confirmed. Please collect your book.', 'read', '2025-03-06 14:44:55', 'general'),
(777, 13, 'Dear bg, your borrow request for the book \'1236\' has been confirmed. Please collect your book.', 'read', '2025-03-06 14:44:57', 'general'),
(778, 82, 'bg has requested to return the book \'1236\'.', 'read', '2025-03-06 14:51:05', 'general'),
(779, 13, 'Your return request for the book \'1236\' has been confirmed.', 'read', '2025-03-06 14:51:54', 'general'),
(780, 81, 'Your return request for the book \'Javascript best - very best pdf\' has been confirmed.', 'unread', '2025-03-06 14:51:56', 'general');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` enum('overdue','damaged','lost') NOT NULL,
  `status` enum('pending','paid') DEFAULT 'pending',
  `payment_date` datetime DEFAULT NULL,
  `paid` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `book_id`, `amount`, `reason`, `status`, `payment_date`, `paid`) VALUES
(3, 16, 8, 30.00, 'lost', 'paid', '2025-02-11 06:18:55', 0),
(10, 81, 13, 2.00, 'overdue', 'paid', '2025-02-12 01:44:04', 0),
(15, 81, 33, 627.00, 'lost', 'paid', '2025-03-04 02:41:07', 0);

-- --------------------------------------------------------

--
-- Table structure for table `research_papers`
--

CREATE TABLE `research_papers` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `institution` varchar(255) DEFAULT NULL,
  `published_date` date DEFAULT NULL,
  `doi` varchar(50) DEFAULT NULL,
  `abstract` text DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('available','checked_out','reserved') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservation`
--

CREATE TABLE `reservation` (
  `reservation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `reservation_date` datetime DEFAULT current_timestamp(),
  `expiration_date` datetime NOT NULL,
  `status` enum('active','fulfilled','expired') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation`
--

INSERT INTO `reservation` (`reservation_id`, `user_id`, `book_id`, `reservation_date`, `expiration_date`, `status`) VALUES
(15, 16, 8, '2025-02-06 20:33:13', '2025-02-13 07:33:13', 'fulfilled'),
(16, 16, 7, '2025-02-06 20:33:57', '2025-02-13 07:33:57', 'fulfilled'),
(18, 16, 13, '2025-02-06 20:39:44', '2025-02-13 07:39:44', 'fulfilled'),
(21, 16, 8, '2025-02-07 04:28:15', '2025-02-13 15:28:15', 'fulfilled'),
(23, 81, 15, '2025-02-09 21:11:11', '2025-02-09 08:11:10', 'expired'),
(24, 81, 32, '2025-02-09 21:11:17', '2025-02-11 08:11:17', 'expired'),
(25, 13, 7, '2025-02-14 03:06:17', '2025-02-15 14:06:17', 'fulfilled'),
(26, 13, 13, '2025-02-15 06:31:37', '2025-02-16 17:31:37', 'expired'),
(27, 77, 15, '2025-02-19 21:58:07', '2025-02-20 08:58:07', 'expired'),
(28, 13, 13, '2025-02-20 22:05:03', '2025-02-22 09:05:03', 'expired'),
(29, 81, 7, '2025-03-03 04:10:42', '2025-03-04 15:10:42', 'fulfilled');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `type` enum('student','teacher','librarian','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved') DEFAULT 'pending',
  `verification_code` varchar(255) DEFAULT NULL,
  `is_confirmed` tinyint(1) DEFAULT 0,
  `role_id` int(11) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `type`, `created_at`, `status`, `verification_code`, `is_confirmed`, `role_id`, `last_name`) VALUES
(13, 'bg', 'esakebrahim94@gmail.com', '$2y$10$SECP8cvzXJ2fO.OL3hm5e./7e16pDOho65BPiDGvWPcTVE4p/.3Ua', 'teacher', '2025-02-01 12:28:34', 'approved', NULL, 1, NULL, 'bereket'),
(16, 'bs', 'esak@gmail.com', '$2y$10$SECP8cvzXJ2fO.OL3hm5e./7e16pDOho65BPiDGvWPcTVE4p/.3Ua', 'student', '2025-02-01 14:38:21', 'approved', NULL, 1, NULL, 'bereket'),
(25, 'esak', 'esak737@gmail.com', '$2y$10$SECP8cvzXJ2fO.OL3hm5e./7e16pDOho65BPiDGvWPcTVE4p/.3Ua', 'student', '2025-02-01 17:46:34', 'approved', '329331', 0, NULL, 'bereket'),
(27, 'esak', 'esak37@gmail.com', '$2y$10$SECP8cvzXJ2fO.OL3hm5e./7e16pDOho65BPiDGvWPcTVE4p/.3Ua', 'student', '2025-02-01 17:48:27', 'approved', NULL, 0, NULL, 'bereket'),
(28, 'esak Ebrahim', 'esak87@gmail.com', '$2y$10$SECP8cvzXJ2fO.OL3hm5e./7e16pDOho65BPiDGvWPcTVE4p/.3Ua', 'student', '2025-02-01 18:23:11', 'pending', NULL, 0, NULL, 'bereket'),
(32, 'esak Ebrahim', 'bkamgbdaw@gmail.com', '$2y$10$SECP8cvzXJ2fO.OL3hm5e./7e16pDOho65BPiDGvWPcTVE4p/.3Ua', 'teacher', '2025-02-01 19:02:20', 'approved', NULL, 0, NULL, 'bereket'),
(33, 'hg', 'ak8733@gmail.com', '$2y$10$SECP8cvzXJ2fO.OL3hm5e./7e16pDOho65BPiDGvWPcTVE4p/.3Ua', 'teacher', '2025-02-01 19:05:00', 'approved', NULL, 0, NULL, 'bereket'),
(34, 'hg', 'ak733@gmail.com', '$2y$10$SECP8cvzXJ2fO.OL3hm5e./7e16pDOho65BPiDGvWPcTVE4p/.3Ua', 'teacher', '2025-02-01 19:07:33', 'approved', NULL, 0, NULL, 'bereket'),
(39, 'jkfg', 'abc@gmail.com', '$2y$10$SECP8cvzXJ2fO.OL3hm5e./7e16pDOho65BPiDGvWPcTVE4p/.3Ua', 'student', '2025-02-02 05:33:44', 'approved', NULL, 0, NULL, 'bereket'),
(40, 'jkfg', 'abcd@gmail.com', '$2y$10$SECP8cvzXJ2fO.OL3hm5e./7e16pDOho65BPiDGvWPcTVE4p/.3Ua', 'teacher', '2025-02-02 13:25:46', 'approved', NULL, 0, NULL, 'bereket'),
(41, 'jkfg', 'esak66866@gmail.com', '$2y$10$SECP8cvzXJ2fO.OL3hm5e./7e16pDOho65BPiDGvWPcTVE4p/.3Ua', 'librarian', '2025-02-02 13:26:56', 'approved', NULL, 1, 2, 'bereket'),
(52, 'esak Ebrahim', 'bekmgbda@gmail.com', '$2y$10$SECP8cvzXJ2fO.OL3hm5e./7e16pDOho65BPiDGvWPcTVE4p/.3Ua', 'student', '2025-02-03 09:47:56', 'pending', NULL, 0, NULL, 'bereket'),
(53, 'esak Ebrahim', 'bekmhnda@gmail.com', '$2y$10$SECP8cvzXJ2fO.OL3hm5e./7e16pDOho65BPiDGvWPcTVE4p/.3Ua', 'student', '2025-02-03 09:58:15', 'pending', NULL, 0, NULL, 'bereket'),
(54, 'esak Ebrahim', 'bekmnda@gmail.com', '$2y$10$SECP8cvzXJ2fO.OL3hm5e./7e16pDOho65BPiDGvWPcTVE4p/.3Ua', 'student', '2025-02-03 10:01:40', 'pending', NULL, 0, NULL, 'bereket'),
(55, 'semira', 'semira@gmail.com', '$2y$10$SECP8cvzXJ2fO.OL3hm5e./7e16pDOho65BPiDGvWPcTVE4p/.3Ua', 'admin', '2025-02-04 17:10:09', 'approved', NULL, 1, NULL, 'bereket'),
(58, 'esak Ebrahim', 'esak837@gmail.com', '$2y$10$SECP8cvzXJ2fO.OL3hm5e./7e16pDOho65BPiDGvWPcTVE4p/.3Ua', 'student', '2025-02-06 14:52:47', 'approved', NULL, 0, NULL, 'bereket'),
(77, 'Esak Ebrahim', 'esak8737@gmail.com', '$2y$10$SECP8cvzXJ2fO.OL3hm5e./7e16pDOho65BPiDGvWPcTVE4p/.3Ua', 'student', '2025-02-07 04:33:16', 'approved', '720819', 1, NULL, 'bereket'),
(78, 'esak Ebrahim', 'esak7@gmail.com', '$2y$10$SECP8cvzXJ2fO.OL3hm5e./7e16pDOho65BPiDGvWPcTVE4p/.3Ua', 'student', '2025-02-07 18:46:38', 'pending', NULL, 0, NULL, 'bereket'),
(79, 'ghj', 'jhgf@g', '$2y$10$SECP8cvzXJ2fO.OL3hm5e./7e16pDOho65BPiDGvWPcTVE4p/.3Ua', 'student', '2025-02-07 18:48:27', 'approved', NULL, 0, NULL, 'bereket'),
(81, 'esak Ebrahim', 'bekamgbdaw@gmail.com', '$2y$10$SECP8cvzXJ2fO.OL3hm5e./7e16pDOho65BPiDGvWPcTVE4p/.3Ua', 'student', '2025-02-08 07:46:24', 'approved', NULL, 1, NULL, 'bereket'),
(82, 'bekam', 'ananiyadimpil@gmail.com', '$2y$10$SECP8cvzXJ2fO.OL3hm5e./7e16pDOho65BPiDGvWPcTVE4p/.3Ua', 'librarian', '2025-02-08 13:48:28', 'approved', NULL, 1, 2, 'bereket'),
(83, 'edxcef', 'esak89@gmail.com', '$2y$10$lx17dXjrZJF.Clx2u8Zwy.6jaDn0OagdyFQLTDMm9etm/L76GmOSW', 'student', '2025-02-16 18:20:22', 'pending', '163095', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_status`
--

CREATE TABLE `user_status` (
  `user_id` int(11) NOT NULL,
  `last_active` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('online','offline') DEFAULT 'offline'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `parent_book_id` (`parent_book_id`);

--
-- Indexes for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `campuses`
--
ALTER TABLE `campuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `extension_requests`
--
ALTER TABLE `extension_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `borrow_id` (`borrow_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `journals`
--
ALTER TABLE `journals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `librarian_actions_log`
--
ALTER TABLE `librarian_actions_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `librarian_id` (`librarian_id`),
  ADD KEY `library_branch_id` (`library_branch_id`),
  ADD KEY `fk_book` (`book_id`);

--
-- Indexes for table `librarian_branches`
--
ALTER TABLE `librarian_branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `librarian_id` (`librarian_id`),
  ADD KEY `library_branch_id` (`library_branch_id`);

--
-- Indexes for table `librarian_responses`
--
ALTER TABLE `librarian_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `feedback_id` (`feedback_id`),
  ADD KEY `librarian_id` (`librarian_id`);

--
-- Indexes for table `librarian_roles`
--
ALTER TABLE `librarian_roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `library_branches`
--
ALTER TABLE `library_branches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `campus_id` (`campus_id`);

--
-- Indexes for table `lost_books`
--
ALTER TABLE `lost_books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `magazines`
--
ALTER TABLE `magazines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `research_papers`
--
ALTER TABLE `research_papers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `user_status`
--
ALTER TABLE `user_status`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `campuses`
--
ALTER TABLE `campuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `extension_requests`
--
ALTER TABLE `extension_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `journals`
--
ALTER TABLE `journals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `librarian_actions_log`
--
ALTER TABLE `librarian_actions_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT for table `librarian_branches`
--
ALTER TABLE `librarian_branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `librarian_responses`
--
ALTER TABLE `librarian_responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `librarian_roles`
--
ALTER TABLE `librarian_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `library_branches`
--
ALTER TABLE `library_branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lost_books`
--
ALTER TABLE `lost_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `magazines`
--
ALTER TABLE `magazines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=781;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `research_papers`
--
ALTER TABLE `research_papers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `books_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `library_branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `books_ibfk_3` FOREIGN KEY (`parent_book_id`) REFERENCES `books` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  ADD CONSTRAINT `borrow_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `borrow_requests_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`);

--
-- Constraints for table `extension_requests`
--
ALTER TABLE `extension_requests`
  ADD CONSTRAINT `extension_requests_ibfk_1` FOREIGN KEY (`borrow_id`) REFERENCES `borrow_requests` (`id`),
  ADD CONSTRAINT `extension_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `journals`
--
ALTER TABLE `journals`
  ADD CONSTRAINT `journals_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `library_branches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `librarian_actions_log`
--
ALTER TABLE `librarian_actions_log`
  ADD CONSTRAINT `librarian_actions_log_ibfk_1` FOREIGN KEY (`librarian_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `librarian_actions_log_ibfk_3` FOREIGN KEY (`library_branch_id`) REFERENCES `library_branches` (`id`);

--
-- Constraints for table `librarian_branches`
--
ALTER TABLE `librarian_branches`
  ADD CONSTRAINT `librarian_branches_ibfk_1` FOREIGN KEY (`librarian_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `librarian_branches_ibfk_2` FOREIGN KEY (`library_branch_id`) REFERENCES `library_branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `librarian_responses`
--
ALTER TABLE `librarian_responses`
  ADD CONSTRAINT `librarian_responses_ibfk_1` FOREIGN KEY (`feedback_id`) REFERENCES `feedback` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `librarian_responses_ibfk_2` FOREIGN KEY (`librarian_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `library_branches`
--
ALTER TABLE `library_branches`
  ADD CONSTRAINT `library_branches_ibfk_1` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lost_books`
--
ALTER TABLE `lost_books`
  ADD CONSTRAINT `lost_books_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `lost_books_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`);

--
-- Constraints for table `magazines`
--
ALTER TABLE `magazines`
  ADD CONSTRAINT `magazines_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `library_branches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`);

--
-- Constraints for table `research_papers`
--
ALTER TABLE `research_papers`
  ADD CONSTRAINT `research_papers_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `library_branches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `librarian_roles` (`id`);

--
-- Constraints for table `user_status`
--
ALTER TABLE `user_status`
  ADD CONSTRAINT `user_status_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
