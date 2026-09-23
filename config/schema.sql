-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 23, 2026 at 04:33 PM
-- Server version: 8.0.43
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `movie_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `actor`
--

DROP TABLE IF EXISTS `actor`;
CREATE TABLE IF NOT EXISTS `actor` (
  `actor_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`actor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `actor`
--

INSERT INTO `actor` (`actor_id`, `name`, `photo_path`) VALUES
(1, 'Elena Marsh', NULL),
(2, 'Tobias Reign', NULL),
(3, 'Priya Nandan', NULL),
(4, 'Cole Whitfield', NULL),
(5, 'Peter Parker', NULL),
(6, 'Tom Holland', NULL),
(7, 'Zendaya', NULL),
(8, 'Sadie Sink', NULL),
(9, 'Jacob Batalon', NULL),
(10, 'Jon Bernthal', NULL),
(11, 'Mark Ruffalo', NULL),
(12, 'Sam Worthington', NULL),
(13, 'Zoe Saldaña', NULL),
(14, 'Stephen Lang', NULL),
(15, 'Kate Winslet', NULL),
(16, 'Cliff Curtis', NULL),
(17, 'Oona Chaplin', NULL),
(18, 'Alan Ritchson', NULL),
(19, 'Owen Wilson', NULL),
(20, 'Kate Box', NULL),
(21, 'Leila George', NULL),
(22, 'Rodrigo Santoro', NULL),
(23, 'Brad Pitt', NULL),
(24, 'Damson Idris', NULL),
(25, 'Javier Bardem', NULL),
(26, 'Kerry Condon', NULL),
(27, 'Jack Black', NULL),
(28, 'Jason Momoa', NULL),
(29, 'Emma Myers', NULL),
(30, 'Danielle Brooks', NULL),
(31, 'Sebastian Hansen', NULL),
(32, 'Matt Damon', NULL),
(33, 'Anne Hathaway', NULL),
(34, 'Robert Pattinson', NULL),
(35, 'Catherine Lagaʻaia', NULL),
(36, 'Dwayne Johnson', NULL),
(37, 'Rena Owen', NULL),
(38, 'John Tui', NULL),
(39, 'Frankie Adams', NULL),
(40, 'Jemaine Clement', NULL),
(41, 'Pedro Pascal', NULL),
(42, 'Sigourney Weaver', NULL),
(43, 'Jeremy Allen White', NULL),
(44, 'Brendan Wayne', NULL),
(45, 'Lateef Crowder', NULL),
(46, 'Jaafar Jackson', NULL),
(47, 'Nia Long', NULL),
(48, 'Miles Teller', NULL),
(49, 'Juliano Krue Valdi', NULL),
(50, 'Colman Domingo', NULL),
(51, 'Karl Urban', NULL),
(52, 'Lewis Tan', NULL),
(53, 'Jessica McNamee', NULL),
(54, 'Josh Lawson', NULL),
(55, 'Hiroyuki Sanada', NULL),
(56, 'Tadanobu Asano', NULL),
(57, 'Adeline Rudolph', NULL),
(58, 'Ralph Fiennes', NULL),
(59, 'Jack O\'Connell', NULL),
(60, 'Alfie Williams', NULL),
(61, 'Erin Kellyman', NULL),
(62, 'Chi Lewis-Parry', NULL),
(63, 'Vanessa Kirby', NULL),
(64, 'Joseph Quinn', NULL),
(65, 'Ebon Moss-Bachrach', NULL),
(66, 'Julia Garner', NULL),
(67, 'David Corenswet', NULL),
(68, 'Rachel Brosnahan', NULL),
(69, 'Nicholas Hoult', NULL),
(70, 'Isabela Merced', NULL),
(71, 'Edi Gathegi', NULL),
(72, 'Nathan Fillion', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

DROP TABLE IF EXISTS `booking`;
CREATE TABLE IF NOT EXISTS `booking` (
  `booking_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `booking_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('Pending','Confirmed','Cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `offer_id` int DEFAULT NULL,
  PRIMARY KEY (`booking_id`),
  KEY `user_id` (`user_id`),
  KEY `offer_id` (`offer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branch`
--

DROP TABLE IF EXISTS `branch`;
CREATE TABLE IF NOT EXISTS `branch` (
  `branch_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`branch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branch`
--

INSERT INTO `branch` (`branch_id`, `name`, `email`, `location`, `contact_number`) VALUES
(1, 'Cinemax City Centre', 'citycentre@cinemax.com', '123 Main Street, Colombo', '0112345678'),
(2, 'Cinemax Galle Road', 'galleroad@cinemax.com', '45 Galle Road, Colombo', '0112223344');

-- --------------------------------------------------------

--
-- Table structure for table `hall`
--

DROP TABLE IF EXISTS `hall`;
CREATE TABLE IF NOT EXISTS `hall` (
  `hall_id` int NOT NULL AUTO_INCREMENT,
  `branch_id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacity` int NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`hall_id`),
  KEY `branch_id` (`branch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hall`
--

INSERT INTO `hall` (`hall_id`, `branch_id`, `name`, `location`, `capacity`, `type`) VALUES
(1, 1, 'Hall 1', 'Ground Floor', 40, '2D'),
(2, 1, 'Hall 2 - IMAX', '1st Floor', 60, 'IMAX'),
(3, 2, 'Hall 1', 'Ground Floor', 35, '2D');

-- --------------------------------------------------------

--
-- Table structure for table `loyalty`
--

DROP TABLE IF EXISTS `loyalty`;
CREATE TABLE IF NOT EXISTS `loyalty` (
  `transaction_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `points` int NOT NULL,
  `type` enum('Earn','Redeem') COLLATE utf8mb4_unicode_ci NOT NULL,
  `time` time DEFAULT NULL,
  `date` date DEFAULT NULL,
  `booking_id` int DEFAULT NULL,
  PRIMARY KEY (`transaction_id`),
  KEY `user_id` (`user_id`),
  KEY `booking_id` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `movie`
--

DROP TABLE IF EXISTS `movie`;
CREATE TABLE IF NOT EXISTS `movie` (
  `movie_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration` int NOT NULL,
  `imdb_rate` decimal(3,1) DEFAULT NULL,
  `genre` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `language` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `poster_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`movie_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `movie`
--

INSERT INTO `movie` (`movie_id`, `title`, `duration`, `imdb_rate`, `genre`, `description`, `language`, `poster_path`, `created_at`) VALUES
(1, 'Nebula Rising', 128, 8.2, 'Sci-Fi, Action', 'A stranded pilot must navigate a dying star system to bring a warning home.', 'English', 'assets/uploads/nebula_rising.jpg', '2026-09-20 10:43:59'),
(2, 'The Quiet Harbor', 104, 7.6, 'Drama', 'Three siblings return to their coastal hometown to confront a decades-old secret.', 'English', 'assets/uploads/quiet_harbor.jpg', '2026-09-20 10:43:59'),
(3, 'Laugh Track', 96, 7.2, 'Comedy', 'A washed-up sitcom writer gets one last shot when a sitcom is revived for streaming.', 'English', 'assets/uploads/laugh_track.jpg', '2026-09-20 10:43:59'),
(4, 'Spider-Man: Brand New Day', 145, 8.0, 'Action, Adventure, Sci-Fi', 'Peter Parker fights crime full-time as Spider-Man in a world that no longer remembers him, while a dangerous new threat emerges in New York.', 'English', 'assets/uploads/movie_6ab3d55ded332.jpg', '2026-09-23 13:34:21'),
(5, 'Avatar: Fire and Ash', 197, 7.2, 'Action, Adventure, Drama, Fantasy, Sci-Fi', 'Jake and Neytiri\'s family faces new challenges after tragedy as they encounter the aggressive Ash People and a new conflict develops on Pandora.', 'English', 'assets/uploads/movie_6ab3d6db7c039.jpg', '2026-09-23 13:40:43'),
(6, 'Runner', 97, 6.7, 'Action, Comedy, Thriller', 'A former soldier and his unlikely partner become targets of a ruthless cartel while racing to complete a critical medical delivery and save the life of a young girl.', 'English', 'assets/uploads/movie_6ab3d7d5176fe.jpg', '2026-09-23 13:44:53'),
(7, 'F1: The Movie', 155, 7.6, 'Action, Drama, Sport', 'A former Formula One driver returns to racing to mentor a young rookie while fighting to save a struggling team and earn one last chance at redemption.', 'English', 'assets/uploads/movie_6ab3dcbb700b9.jpg', '2026-09-23 14:05:47'),
(8, 'A Minecraft Movie', 101, 5.6, 'Action, Adventure, Comedy, Fantasy', 'Four ordinary people are pulled through a mysterious portal into the strange and dangerous world of Minecraft, where they must work together to survive and find their way home.', 'English', 'assets/uploads/movie_6ab3dd88b96c8.jpg', '2026-09-23 14:09:12'),
(9, 'The Odyssey', 172, 8.4, 'Action, Adventure, Fantasy, Drama', 'After the Trojan War, Odysseus begins a dangerous journey home to Ithaca, facing mythical creatures, powerful forces and countless challenges along the way', 'English', 'assets/uploads/movie_6ab3de018a588.jpg', '2026-09-23 14:11:13'),
(10, 'Moana', 115, 5.8, 'Action, Adventure, Comedy, Family, Fantasy', 'Moana sets out across the ocean with the legendary demigod Maui on a dangerous journey to break a curse and restore prosperity to her people.', 'English', 'assets/uploads/movie_6ab3de8d73eef.jpg', '2026-09-23 14:13:33'),
(11, 'Star Wars: The Mandalorian and Grogu', 132, 6.8, 'Action, Adventure, Fantasy, Sci-Fi', 'Mandalorian bounty hunter Din Djarin and his young apprentice Grogu embark on a new mission as they face dangerous enemies and help protect the emerging New Republic.', 'English', 'assets/uploads/movie_6ab3df3fa9ec9.jpg', '2026-09-23 14:16:31'),
(12, 'Michael', 127, 7.4, 'Biography, Drama, Music', 'A biographical drama following Michael Jackson\'s journey from his early days with the Jackson Five to his rise as one of the world\'s most influential entertainers.', 'English', 'assets/uploads/movie_6ab3dfb345265.jpg', '2026-09-23 14:18:27'),
(13, 'Mortal Kombat II', 116, 6.3, 'Action, Adventure, Fantasy, Martial Arts', 'The champions of Earthrealm join forces with Johnny Cage to face powerful enemies and battle Shao Kahn in a fight that could determine the fate of their world.', 'English', 'assets/uploads/movie_6ab3e13d9415e.jpg', '2026-09-23 14:25:01'),
(14, '28 Years Later: The Bone Temple', 109, 7.2, 'Horror, Sci-Fi, Thriller', 'As Spike becomes involved with a dangerous group on the mainland, Dr. Kelson makes a discovery that could change the future of humanity.', 'English', 'assets/uploads/movie_6ab3e1b72b979.jpg', '2026-09-23 14:27:03'),
(15, 'The Fantastic Four: First Steps', 115, 6.8, 'Action, Adventure, Sci-Fi', 'The Fantastic Four must balance their lives as a family with their responsibilities as heroes while defending Earth from the cosmic threat of Galactus and his mysterious Herald, the Silver Surfer.', 'English', 'assets/uploads/movie_6ab3e2d5587f4.jpg', '2026-09-23 14:31:49'),
(16, 'Superman', 129, 7.0, 'Action, Adventure, Sci-Fi', 'Superman struggles to balance his Kryptonian heritage with his human identity as Clark Kent while defending his ideals in a world that increasingly questions them.', 'English', 'assets/uploads/movie_6ab3e37e34482.jpg', '2026-09-23 14:34:38');

-- --------------------------------------------------------

--
-- Table structure for table `movie_actor`
--

DROP TABLE IF EXISTS `movie_actor`;
CREATE TABLE IF NOT EXISTS `movie_actor` (
  `movie_id` int NOT NULL,
  `actor_id` int NOT NULL,
  `character_type` enum('main','sub') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'main',
  PRIMARY KEY (`movie_id`,`actor_id`),
  KEY `actor_id` (`actor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `movie_actor`
--

INSERT INTO `movie_actor` (`movie_id`, `actor_id`, `character_type`) VALUES
(1, 1, 'main'),
(1, 2, 'sub'),
(2, 3, 'main'),
(2, 4, 'sub'),
(3, 2, 'main'),
(4, 6, 'main'),
(4, 7, 'sub'),
(4, 8, 'sub'),
(4, 9, 'sub'),
(4, 10, 'sub'),
(4, 11, 'sub'),
(5, 12, 'main'),
(5, 13, 'main'),
(5, 14, 'sub'),
(5, 15, 'sub'),
(5, 16, 'sub'),
(5, 17, 'sub'),
(6, 18, 'main'),
(6, 19, 'sub'),
(6, 20, 'sub'),
(6, 21, 'sub'),
(6, 22, 'sub'),
(7, 23, 'main'),
(7, 24, 'main'),
(7, 25, 'sub'),
(7, 26, 'sub'),
(8, 27, 'main'),
(8, 28, 'main'),
(8, 29, 'main'),
(8, 30, 'main'),
(8, 31, 'main'),
(9, 6, 'main'),
(9, 7, 'main'),
(9, 32, 'main'),
(9, 33, 'main'),
(9, 34, 'main'),
(10, 35, 'main'),
(10, 36, 'main'),
(10, 37, 'main'),
(10, 38, 'main'),
(10, 39, 'main'),
(10, 40, 'main'),
(11, 41, 'main'),
(11, 42, 'main'),
(11, 43, 'main'),
(11, 44, 'main'),
(11, 45, 'main'),
(12, 46, 'main'),
(12, 47, 'main'),
(12, 48, 'main'),
(12, 49, 'main'),
(12, 50, 'main'),
(13, 51, 'main'),
(13, 52, 'main'),
(13, 53, 'main'),
(13, 54, 'main'),
(13, 55, 'main'),
(13, 56, 'main'),
(13, 57, 'main'),
(14, 58, 'main'),
(14, 59, 'main'),
(14, 60, 'main'),
(14, 61, 'main'),
(14, 62, 'main'),
(15, 41, 'main'),
(15, 63, 'main'),
(15, 64, 'main'),
(15, 65, 'main'),
(15, 66, 'main'),
(16, 67, 'main'),
(16, 68, 'main'),
(16, 69, 'main'),
(16, 70, 'main'),
(16, 71, 'main'),
(16, 72, 'main');

-- --------------------------------------------------------

--
-- Table structure for table `offer`
--

DROP TABLE IF EXISTS `offer`;
CREATE TABLE IF NOT EXISTS `offer` (
  `offer_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_pct` decimal(5,2) NOT NULL,
  `start_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`offer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `offer`
--

INSERT INTO `offer` (`offer_id`, `title`, `discount_pct`, `start_date`, `expiry_date`, `is_active`) VALUES
(1, 'Weekday Special', 10.00, '2026-09-20', '2026-10-20', 1),
(2, 'Student Discount', 15.00, '2026-09-20', '2026-11-19', 1);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

DROP TABLE IF EXISTS `payment`;
CREATE TABLE IF NOT EXISTS `payment` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_time` time DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  UNIQUE KEY `booking_id` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

DROP TABLE IF EXISTS `review`;
CREATE TABLE IF NOT EXISTS `review` (
  `review_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `movie_id` int NOT NULL,
  `rating` tinyint NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  UNIQUE KEY `uniq_user_movie_review` (`user_id`,`movie_id`),
  KEY `movie_id` (`movie_id`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `seat`
--

DROP TABLE IF EXISTS `seat`;
CREATE TABLE IF NOT EXISTS `seat` (
  `seat_id` int NOT NULL AUTO_INCREMENT,
  `hall_id` int NOT NULL,
  `seat_number` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `row_label` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('Box','Standard') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Standard',
  PRIMARY KEY (`seat_id`),
  UNIQUE KEY `uniq_seat_per_hall` (`hall_id`,`seat_number`)
) ENGINE=InnoDB AUTO_INCREMENT=190 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `seat`
--

INSERT INTO `seat` (`seat_id`, `hall_id`, `seat_number`, `row_label`, `category`) VALUES
(1, 1, 'D1', 'D', 'Box'),
(2, 1, 'C1', 'C', 'Standard'),
(3, 1, 'B1', 'B', 'Standard'),
(4, 1, 'A1', 'A', 'Standard'),
(5, 1, 'D2', 'D', 'Box'),
(6, 1, 'C2', 'C', 'Standard'),
(7, 1, 'B2', 'B', 'Standard'),
(8, 1, 'A2', 'A', 'Standard'),
(9, 1, 'D3', 'D', 'Box'),
(10, 1, 'C3', 'C', 'Standard'),
(11, 1, 'B3', 'B', 'Standard'),
(12, 1, 'A3', 'A', 'Standard'),
(13, 1, 'D4', 'D', 'Box'),
(14, 1, 'C4', 'C', 'Standard'),
(15, 1, 'B4', 'B', 'Standard'),
(16, 1, 'A4', 'A', 'Standard'),
(17, 1, 'D5', 'D', 'Box'),
(18, 1, 'C5', 'C', 'Standard'),
(19, 1, 'B5', 'B', 'Standard'),
(20, 1, 'A5', 'A', 'Standard'),
(21, 1, 'D6', 'D', 'Box'),
(22, 1, 'C6', 'C', 'Standard'),
(23, 1, 'B6', 'B', 'Standard'),
(24, 1, 'A6', 'A', 'Standard'),
(25, 1, 'D7', 'D', 'Box'),
(26, 1, 'C7', 'C', 'Standard'),
(27, 1, 'B7', 'B', 'Standard'),
(28, 1, 'A7', 'A', 'Standard'),
(29, 1, 'D8', 'D', 'Box'),
(30, 1, 'C8', 'C', 'Standard'),
(31, 1, 'B8', 'B', 'Standard'),
(32, 1, 'A8', 'A', 'Standard'),
(33, 1, 'D9', 'D', 'Box'),
(34, 1, 'C9', 'C', 'Standard'),
(35, 1, 'B9', 'B', 'Standard'),
(36, 1, 'A9', 'A', 'Standard'),
(37, 1, 'D10', 'D', 'Box'),
(38, 1, 'C10', 'C', 'Standard'),
(39, 1, 'B10', 'B', 'Standard'),
(40, 1, 'A10', 'A', 'Standard'),
(64, 2, 'F1', 'F', 'Box'),
(65, 2, 'E1', 'E', 'Standard'),
(66, 2, 'D1', 'D', 'Standard'),
(67, 2, 'C1', 'C', 'Standard'),
(68, 2, 'B1', 'B', 'Standard'),
(69, 2, 'A1', 'A', 'Standard'),
(70, 2, 'F2', 'F', 'Box'),
(71, 2, 'E2', 'E', 'Standard'),
(72, 2, 'D2', 'D', 'Standard'),
(73, 2, 'C2', 'C', 'Standard'),
(74, 2, 'B2', 'B', 'Standard'),
(75, 2, 'A2', 'A', 'Standard'),
(76, 2, 'F3', 'F', 'Box'),
(77, 2, 'E3', 'E', 'Standard'),
(78, 2, 'D3', 'D', 'Standard'),
(79, 2, 'C3', 'C', 'Standard'),
(80, 2, 'B3', 'B', 'Standard'),
(81, 2, 'A3', 'A', 'Standard'),
(82, 2, 'F4', 'F', 'Box'),
(83, 2, 'E4', 'E', 'Standard'),
(84, 2, 'D4', 'D', 'Standard'),
(85, 2, 'C4', 'C', 'Standard'),
(86, 2, 'B4', 'B', 'Standard'),
(87, 2, 'A4', 'A', 'Standard'),
(88, 2, 'F5', 'F', 'Box'),
(89, 2, 'E5', 'E', 'Standard'),
(90, 2, 'D5', 'D', 'Standard'),
(91, 2, 'C5', 'C', 'Standard'),
(92, 2, 'B5', 'B', 'Standard'),
(93, 2, 'A5', 'A', 'Standard'),
(94, 2, 'F6', 'F', 'Box'),
(95, 2, 'E6', 'E', 'Standard'),
(96, 2, 'D6', 'D', 'Standard'),
(97, 2, 'C6', 'C', 'Standard'),
(98, 2, 'B6', 'B', 'Standard'),
(99, 2, 'A6', 'A', 'Standard'),
(100, 2, 'F7', 'F', 'Box'),
(101, 2, 'E7', 'E', 'Standard'),
(102, 2, 'D7', 'D', 'Standard'),
(103, 2, 'C7', 'C', 'Standard'),
(104, 2, 'B7', 'B', 'Standard'),
(105, 2, 'A7', 'A', 'Standard'),
(106, 2, 'F8', 'F', 'Box'),
(107, 2, 'E8', 'E', 'Standard'),
(108, 2, 'D8', 'D', 'Standard'),
(109, 2, 'C8', 'C', 'Standard'),
(110, 2, 'B8', 'B', 'Standard'),
(111, 2, 'A8', 'A', 'Standard'),
(112, 2, 'F9', 'F', 'Box'),
(113, 2, 'E9', 'E', 'Standard'),
(114, 2, 'D9', 'D', 'Standard'),
(115, 2, 'C9', 'C', 'Standard'),
(116, 2, 'B9', 'B', 'Standard'),
(117, 2, 'A9', 'A', 'Standard'),
(118, 2, 'F10', 'F', 'Box'),
(119, 2, 'E10', 'E', 'Standard'),
(120, 2, 'D10', 'D', 'Standard'),
(121, 2, 'C10', 'C', 'Standard'),
(122, 2, 'B10', 'B', 'Standard'),
(123, 2, 'A10', 'A', 'Standard'),
(127, 3, 'E1', 'E', 'Box'),
(128, 3, 'D1', 'D', 'Standard'),
(129, 3, 'C1', 'C', 'Standard'),
(130, 3, 'B1', 'B', 'Standard'),
(131, 3, 'A1', 'A', 'Standard'),
(132, 3, 'E2', 'E', 'Box'),
(133, 3, 'D2', 'D', 'Standard'),
(134, 3, 'C2', 'C', 'Standard'),
(135, 3, 'B2', 'B', 'Standard'),
(136, 3, 'A2', 'A', 'Standard'),
(137, 3, 'E3', 'E', 'Box'),
(138, 3, 'D3', 'D', 'Standard'),
(139, 3, 'C3', 'C', 'Standard'),
(140, 3, 'B3', 'B', 'Standard'),
(141, 3, 'A3', 'A', 'Standard'),
(142, 3, 'E4', 'E', 'Box'),
(143, 3, 'D4', 'D', 'Standard'),
(144, 3, 'C4', 'C', 'Standard'),
(145, 3, 'B4', 'B', 'Standard'),
(146, 3, 'A4', 'A', 'Standard'),
(147, 3, 'E5', 'E', 'Box'),
(148, 3, 'D5', 'D', 'Standard'),
(149, 3, 'C5', 'C', 'Standard'),
(150, 3, 'B5', 'B', 'Standard'),
(151, 3, 'A5', 'A', 'Standard'),
(152, 3, 'E6', 'E', 'Box'),
(153, 3, 'D6', 'D', 'Standard'),
(154, 3, 'C6', 'C', 'Standard'),
(155, 3, 'B6', 'B', 'Standard'),
(156, 3, 'A6', 'A', 'Standard'),
(157, 3, 'E7', 'E', 'Box'),
(158, 3, 'D7', 'D', 'Standard'),
(159, 3, 'C7', 'C', 'Standard'),
(160, 3, 'B7', 'B', 'Standard'),
(161, 3, 'A7', 'A', 'Standard');

-- --------------------------------------------------------

--
-- Table structure for table `showtime_seat`
--

DROP TABLE IF EXISTS `showtime_seat`;
CREATE TABLE IF NOT EXISTS `showtime_seat` (
  `showtime_seat_id` int NOT NULL AUTO_INCREMENT,
  `show_id` int NOT NULL,
  `seat_id` int NOT NULL,
  `booking_id` int DEFAULT NULL,
  `status` enum('Available','Hold','Booked','CheckedIn') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Available',
  `hold_expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`showtime_seat_id`),
  UNIQUE KEY `uniq_seat_per_showtime` (`show_id`,`seat_id`),
  KEY `seat_id` (`seat_id`),
  KEY `booking_id` (`booking_id`)
) ENGINE=InnoDB AUTO_INCREMENT=256 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `showtime_seat`
--

INSERT INTO `showtime_seat` (`showtime_seat_id`, `show_id`, `seat_id`, `booking_id`, `status`, `hold_expires_at`) VALUES
(1, 3, 4, NULL, 'Available', NULL),
(2, 3, 40, NULL, 'Available', NULL),
(3, 3, 8, NULL, 'Available', NULL),
(4, 3, 12, NULL, 'Available', NULL),
(5, 3, 16, NULL, 'Available', NULL),
(6, 3, 20, NULL, 'Available', NULL),
(7, 3, 24, NULL, 'Available', NULL),
(8, 3, 28, NULL, 'Available', NULL),
(9, 3, 32, NULL, 'Available', NULL),
(10, 3, 36, NULL, 'Available', NULL),
(11, 3, 3, NULL, 'Available', NULL),
(12, 3, 39, NULL, 'Available', NULL),
(13, 3, 7, NULL, 'Available', NULL),
(14, 3, 11, NULL, 'Available', NULL),
(15, 3, 15, NULL, 'Available', NULL),
(16, 3, 19, NULL, 'Available', NULL),
(17, 3, 23, NULL, 'Available', NULL),
(18, 3, 27, NULL, 'Available', NULL),
(19, 3, 31, NULL, 'Available', NULL),
(20, 3, 35, NULL, 'Available', NULL),
(21, 3, 2, NULL, 'Available', NULL),
(22, 3, 38, NULL, 'Available', NULL),
(23, 3, 6, NULL, 'Available', NULL),
(24, 3, 10, NULL, 'Available', NULL),
(25, 3, 14, NULL, 'Available', NULL),
(26, 3, 18, NULL, 'Available', NULL),
(27, 3, 22, NULL, 'Available', NULL),
(28, 3, 26, NULL, 'Available', NULL),
(29, 3, 30, NULL, 'Available', NULL),
(30, 3, 34, NULL, 'Available', NULL),
(31, 3, 1, NULL, 'Available', NULL),
(32, 3, 37, NULL, 'Available', NULL),
(33, 3, 5, NULL, 'Available', NULL),
(34, 3, 9, NULL, 'Available', NULL),
(35, 3, 13, NULL, 'Available', NULL),
(36, 3, 17, NULL, 'Available', NULL),
(37, 3, 21, NULL, 'Available', NULL),
(38, 3, 25, NULL, 'Available', NULL),
(39, 3, 29, NULL, 'Available', NULL),
(40, 3, 33, NULL, 'Available', NULL),
(41, 1, 69, NULL, 'Available', NULL),
(42, 1, 123, NULL, 'Available', NULL),
(43, 1, 75, NULL, 'Available', NULL),
(44, 1, 81, NULL, 'Available', NULL),
(45, 1, 87, NULL, 'Available', NULL),
(46, 1, 93, NULL, 'Available', NULL),
(47, 1, 99, NULL, 'Available', NULL),
(48, 1, 105, NULL, 'Available', NULL),
(49, 1, 111, NULL, 'Available', NULL),
(50, 1, 117, NULL, 'Available', NULL),
(51, 1, 68, NULL, 'Available', NULL),
(52, 1, 122, NULL, 'Available', NULL),
(53, 1, 74, NULL, 'Available', NULL),
(54, 1, 80, NULL, 'Available', NULL),
(55, 1, 86, NULL, 'Available', NULL),
(56, 1, 92, NULL, 'Available', NULL),
(57, 1, 98, NULL, 'Available', NULL),
(58, 1, 104, NULL, 'Available', NULL),
(59, 1, 110, NULL, 'Available', NULL),
(60, 1, 116, NULL, 'Available', NULL),
(61, 1, 67, NULL, 'Available', NULL),
(62, 1, 121, NULL, 'Available', NULL),
(63, 1, 73, NULL, 'Available', NULL),
(64, 1, 79, NULL, 'Available', NULL),
(65, 1, 85, NULL, 'Available', NULL),
(66, 1, 91, NULL, 'Available', NULL),
(67, 1, 97, NULL, 'Available', NULL),
(68, 1, 103, NULL, 'Available', NULL),
(69, 1, 109, NULL, 'Available', NULL),
(70, 1, 115, NULL, 'Available', NULL),
(71, 1, 66, NULL, 'Available', NULL),
(72, 1, 120, NULL, 'Available', NULL),
(73, 1, 72, NULL, 'Available', NULL),
(74, 1, 78, NULL, 'Available', NULL),
(75, 1, 84, NULL, 'Available', NULL),
(76, 1, 90, NULL, 'Available', NULL),
(77, 1, 96, NULL, 'Available', NULL),
(78, 1, 102, NULL, 'Available', NULL),
(79, 1, 108, NULL, 'Available', NULL),
(80, 1, 114, NULL, 'Available', NULL),
(81, 1, 65, NULL, 'Available', NULL),
(82, 1, 119, NULL, 'Available', NULL),
(83, 1, 71, NULL, 'Available', NULL),
(84, 1, 77, NULL, 'Available', NULL),
(85, 1, 83, NULL, 'Available', NULL),
(86, 1, 89, NULL, 'Available', NULL),
(87, 1, 95, NULL, 'Available', NULL),
(88, 1, 101, NULL, 'Available', NULL),
(89, 1, 107, NULL, 'Available', NULL),
(90, 1, 113, NULL, 'Available', NULL),
(91, 1, 64, NULL, 'Available', NULL),
(92, 1, 118, NULL, 'Available', NULL),
(93, 1, 70, NULL, 'Available', NULL),
(94, 1, 76, NULL, 'Available', NULL),
(95, 1, 82, NULL, 'Available', NULL),
(96, 1, 88, NULL, 'Available', NULL),
(97, 1, 94, NULL, 'Available', NULL),
(98, 1, 100, NULL, 'Available', NULL),
(99, 1, 106, NULL, 'Available', NULL),
(100, 1, 112, NULL, 'Available', NULL),
(101, 2, 69, NULL, 'Available', NULL),
(102, 2, 123, NULL, 'Available', NULL),
(103, 2, 75, NULL, 'Available', NULL),
(104, 2, 81, NULL, 'Available', NULL),
(105, 2, 87, NULL, 'Available', NULL),
(106, 2, 93, NULL, 'Available', NULL),
(107, 2, 99, NULL, 'Available', NULL),
(108, 2, 105, NULL, 'Available', NULL),
(109, 2, 111, NULL, 'Available', NULL),
(110, 2, 117, NULL, 'Available', NULL),
(111, 2, 68, NULL, 'Available', NULL),
(112, 2, 122, NULL, 'Available', NULL),
(113, 2, 74, NULL, 'Available', NULL),
(114, 2, 80, NULL, 'Available', NULL),
(115, 2, 86, NULL, 'Available', NULL),
(116, 2, 92, NULL, 'Available', NULL),
(117, 2, 98, NULL, 'Available', NULL),
(118, 2, 104, NULL, 'Available', NULL),
(119, 2, 110, NULL, 'Available', NULL),
(120, 2, 116, NULL, 'Available', NULL),
(121, 2, 67, NULL, 'Available', NULL),
(122, 2, 121, NULL, 'Available', NULL),
(123, 2, 73, NULL, 'Available', NULL),
(124, 2, 79, NULL, 'Available', NULL),
(125, 2, 85, NULL, 'Available', NULL),
(126, 2, 91, NULL, 'Available', NULL),
(127, 2, 97, NULL, 'Available', NULL),
(128, 2, 103, NULL, 'Available', NULL),
(129, 2, 109, NULL, 'Available', NULL),
(130, 2, 115, NULL, 'Available', NULL),
(131, 2, 66, NULL, 'Available', NULL),
(132, 2, 120, NULL, 'Available', NULL),
(133, 2, 72, NULL, 'Available', NULL),
(134, 2, 78, NULL, 'Available', NULL),
(135, 2, 84, NULL, 'Available', NULL),
(136, 2, 90, NULL, 'Available', NULL),
(137, 2, 96, NULL, 'Available', NULL),
(138, 2, 102, NULL, 'Available', NULL),
(139, 2, 108, NULL, 'Available', NULL),
(140, 2, 114, NULL, 'Available', NULL),
(141, 2, 65, NULL, 'Available', NULL),
(142, 2, 119, NULL, 'Available', NULL),
(143, 2, 71, NULL, 'Available', NULL),
(144, 2, 77, NULL, 'Available', NULL),
(145, 2, 83, NULL, 'Available', NULL),
(146, 2, 89, NULL, 'Available', NULL),
(147, 2, 95, NULL, 'Available', NULL),
(148, 2, 101, NULL, 'Available', NULL),
(149, 2, 107, NULL, 'Available', NULL),
(150, 2, 113, NULL, 'Available', NULL),
(151, 2, 64, NULL, 'Available', NULL),
(152, 2, 118, NULL, 'Available', NULL),
(153, 2, 70, NULL, 'Available', NULL),
(154, 2, 76, NULL, 'Available', NULL),
(155, 2, 82, NULL, 'Available', NULL),
(156, 2, 88, NULL, 'Available', NULL),
(157, 2, 94, NULL, 'Available', NULL),
(158, 2, 100, NULL, 'Available', NULL),
(159, 2, 106, NULL, 'Available', NULL),
(160, 2, 112, NULL, 'Available', NULL),
(161, 4, 131, NULL, 'Available', NULL),
(162, 4, 136, NULL, 'Available', NULL),
(163, 4, 141, NULL, 'Available', NULL),
(164, 4, 146, NULL, 'Available', NULL),
(165, 4, 151, NULL, 'Available', NULL),
(166, 4, 156, NULL, 'Available', NULL),
(167, 4, 161, NULL, 'Available', NULL),
(168, 4, 130, NULL, 'Available', NULL),
(169, 4, 135, NULL, 'Available', NULL),
(170, 4, 140, NULL, 'Available', NULL),
(171, 4, 145, NULL, 'Available', NULL),
(172, 4, 150, NULL, 'Available', NULL),
(173, 4, 155, NULL, 'Available', NULL),
(174, 4, 160, NULL, 'Available', NULL),
(175, 4, 129, NULL, 'Available', NULL),
(176, 4, 134, NULL, 'Available', NULL),
(177, 4, 139, NULL, 'Available', NULL),
(178, 4, 144, NULL, 'Available', NULL),
(179, 4, 149, NULL, 'Available', NULL),
(180, 4, 154, NULL, 'Available', NULL),
(181, 4, 159, NULL, 'Available', NULL),
(182, 4, 128, NULL, 'Available', NULL),
(183, 4, 133, NULL, 'Available', NULL),
(184, 4, 138, NULL, 'Available', NULL),
(185, 4, 143, NULL, 'Available', NULL),
(186, 4, 148, NULL, 'Available', NULL),
(187, 4, 153, NULL, 'Available', NULL),
(188, 4, 158, NULL, 'Available', NULL),
(189, 4, 127, NULL, 'Available', NULL),
(190, 4, 132, NULL, 'Available', NULL),
(191, 4, 137, NULL, 'Available', NULL),
(192, 4, 142, NULL, 'Available', NULL),
(193, 4, 147, NULL, 'Available', NULL),
(194, 4, 152, NULL, 'Available', NULL),
(195, 4, 157, NULL, 'Available', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `show_time`
--

DROP TABLE IF EXISTS `show_time`;
CREATE TABLE IF NOT EXISTS `show_time` (
  `show_id` int NOT NULL AUTO_INCREMENT,
  `movie_id` int NOT NULL,
  `hall_id` int NOT NULL,
  `experience_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  PRIMARY KEY (`show_id`),
  KEY `movie_id` (`movie_id`),
  KEY `hall_id` (`hall_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `show_time`
--

INSERT INTO `show_time` (`show_id`, `movie_id`, `hall_id`, `experience_type`, `date`, `start_time`, `end_time`) VALUES
(1, 1, 2, 'IMAX', '2026-09-20', '14:00:00', '16:08:00'),
(2, 1, 2, 'IMAX', '2026-09-20', '19:00:00', '21:08:00'),
(3, 2, 1, '2D', '2026-09-20', '17:30:00', '19:14:00'),
(4, 3, 3, '2D', '2026-09-20', '20:00:00', '21:36:00');

-- --------------------------------------------------------

--
-- Table structure for table `ticket`
--

DROP TABLE IF EXISTS `ticket`;
CREATE TABLE IF NOT EXISTS `ticket` (
  `ticket_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `unique_qr_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `generated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ticket_id`),
  UNIQUE KEY `booking_id` (`booking_id`),
  UNIQUE KEY `unique_qr_code` (`unique_qr_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('customer','admin','counter_staff') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `name`, `email`, `password`, `phone`, `role`, `created_at`) VALUES
(1, 'Admin User', 'admin@cinema.com', '$2y$10$Risi6H0bP5rmfkp3iK314uVVGdrxSD0QkwhU0ThL6vGq7WAasQSKG', '0770000000', 'admin', '2026-09-20 10:43:58'),
(2, 'Counter Staff', 'staff@cinema.com', '$2y$10$isT.fe7DdJXTgqvWsCo8fuR6oIKAdkYJPVi2ztfHhl8VmLQe0uToS', '0770000002', 'counter_staff', '2026-09-20 10:43:58');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`offer_id`) REFERENCES `offer` (`offer_id`) ON DELETE SET NULL;

--
-- Constraints for table `hall`
--
ALTER TABLE `hall`
  ADD CONSTRAINT `hall_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`branch_id`) ON DELETE CASCADE;

--
-- Constraints for table `loyalty`
--
ALTER TABLE `loyalty`
  ADD CONSTRAINT `loyalty_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loyalty_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`booking_id`) ON DELETE SET NULL;

--
-- Constraints for table `movie_actor`
--
ALTER TABLE `movie_actor`
  ADD CONSTRAINT `movie_actor_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movie` (`movie_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `movie_actor_ibfk_2` FOREIGN KEY (`actor_id`) REFERENCES `actor` (`actor_id`) ON DELETE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`booking_id`) ON DELETE CASCADE;

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movie` (`movie_id`) ON DELETE CASCADE;

--
-- Constraints for table `seat`
--
ALTER TABLE `seat`
  ADD CONSTRAINT `seat_ibfk_1` FOREIGN KEY (`hall_id`) REFERENCES `hall` (`hall_id`) ON DELETE CASCADE;

--
-- Constraints for table `showtime_seat`
--
ALTER TABLE `showtime_seat`
  ADD CONSTRAINT `showtime_seat_ibfk_1` FOREIGN KEY (`show_id`) REFERENCES `show_time` (`show_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `showtime_seat_ibfk_2` FOREIGN KEY (`seat_id`) REFERENCES `seat` (`seat_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `showtime_seat_ibfk_3` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`booking_id`) ON DELETE SET NULL;

--
-- Constraints for table `show_time`
--
ALTER TABLE `show_time`
  ADD CONSTRAINT `show_time_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movie` (`movie_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `show_time_ibfk_2` FOREIGN KEY (`hall_id`) REFERENCES `hall` (`hall_id`) ON DELETE CASCADE;

--
-- Constraints for table `ticket`
--
ALTER TABLE `ticket`
  ADD CONSTRAINT `ticket_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`booking_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
