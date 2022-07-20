-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 19, 2022 at 03:08 PM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 8.0.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

GRANT SELECT, INSERT, UPDATE, DELETE, RELOAD, SHUTDOWN, PROCESS, FILE, CREATE TEMPORARY TABLES, CREATE VIEW, SHOW VIEW ON *.* TO `moderator`@`localhost` IDENTIFIED BY PASSWORD '*C584D0E630F18DBC2BB729F8AED8F4C393D8EACF';

GRANT SELECT, INSERT, UPDATE, DELETE ON *.* TO `user`@`localhost` IDENTIFIED BY PASSWORD '*7CB706DF9B526885FED503B7F08913EAB991030A';
GRANT SELECT, INSERT, UPDATE, DELETE ON `njctattoodb`.* TO `user`@`localhost`;

GRANT ALL PRIVILEGES ON *.* TO `admin`@`localhost` IDENTIFIED BY PASSWORD '*4C80F1B455257E202220D4D9C704AB09E0507E92' WITH GRANT OPTION;
GRANT ALL PRIVILEGES ON `njctattoodb`.* TO `admin`@`localhost` WITH GRANT OPTION;

--
-- Database: `njctattoodb`
--
CREATE DATABASE IF NOT EXISTS `njctattoodb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `njctattoodb`;

-- --------------------------------------------------------

--
-- Table structure for table `card`
--

CREATE TABLE `card` (
  `card_payment_id` char(22) NOT NULL,
  `payment_id` char(22) NOT NULL,
  `card_number` varchar(16) NOT NULL,
  `card_holder_fname` varchar(50) NOT NULL,
  `card_holder_lname` varchar(50) NOT NULL,
  `bank_name` varchar(50) NOT NULL,
  `card_type` enum('Credit','Debit','Prepaid') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `card`
--

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `client_id` char(22) NOT NULL,
  `client_fname` varchar(50) NOT NULL,
  `client_mi` varchar(2) DEFAULT NULL,
  `client_lname` varchar(50) NOT NULL,
  `home_address` varchar(300) DEFAULT NULL,
  `contact_number` char(11) DEFAULT NULL,
  `birthdate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`client_id`, `client_fname`, `client_mi`, `client_lname`, `home_address`, `contact_number`, `birthdate`) VALUES
('85af1b64a1a4db30ce16cb', 'Admin', NULL, 'Admin', NULL, NULL, '2022-03-18');

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE `order_item` (
  `item_id` char(22) NOT NULL,
  `order_id` char(22) NOT NULL,
  `tattoo_id` char(22) NOT NULL,
  `tattoo_quantity` tinyint(2) NOT NULL,
  `tattoo_width` tinyint(2) NOT NULL,
  `tattoo_height` tinyint(2) NOT NULL,
  `paid` enum('Unpaid','Partially Paid','Fully Paid') NOT NULL DEFAULT 'Unpaid',
  `item_status` enum('Standing','Reserved','Applied') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order_item`
--

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` char(22) NOT NULL,
  `order_id` char(22) NOT NULL,
  `payment_method` enum('Cash','Card','Check') NOT NULL,
  `payment_date` date NOT NULL DEFAULT current_timestamp(),
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_change` decimal(10,2) DEFAULT NULL,
  `client_fname` varchar(50) NOT NULL,
  `client_lname` varchar(50) NOT NULL,
  `street_address` varchar(255) NOT NULL,
  `city` varchar(35) NOT NULL,
  `province` varchar(35) NOT NULL,
  `zip` char(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `payment`
--

-- --------------------------------------------------------

--
-- Table structure for table `referral`
--

CREATE TABLE `referral` (
  `referral_id` char(22) NOT NULL,
  `client_id` char(22) NOT NULL,
  `order_id` char(22) NOT NULL,
  `referral_fname` varchar(50) NOT NULL,
  `referral_mi` varchar(2) NOT NULL,
  `referral_lname` varchar(50) NOT NULL,
  `referral_contact_no` char(11) NOT NULL,
  `referral_email` varchar(62) NOT NULL,
  `referral_age` int(3) NOT NULL,
  `confirmation_status` enum('Pending','Confirmed','Declined') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `referral`
--

-- --------------------------------------------------------

--
-- Table structure for table `reservation`
--

CREATE TABLE `reservation` (
  `reservation_id` char(22) NOT NULL,
  `item_id` varchar(22) NOT NULL,
  `reservation_status` enum('Pending','Confirmed') NOT NULL,
  `service_type` enum('Home service','Walk-in') NOT NULL,
  `reservation_address` varchar(255) NOT NULL,
  `reservation_description` varchar(300) NOT NULL,
  `scheduled_date` date NOT NULL,
  `scheduled_time` time NOT NULL,
  `amount_addon` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `reservation`
--

-- --------------------------------------------------------

--
-- Table structure for table `tattoo`
--

CREATE TABLE `tattoo` (
  `tattoo_id` char(22) NOT NULL,
  `tattoo_name` varchar(50) NOT NULL,
  `tattoo_price` decimal(10,2) NOT NULL,
  `tattoo_width` tinyint(2) NOT NULL,
  `tattoo_height` tinyint(2) NOT NULL,
  `tattoo_image` varchar(255) NOT NULL,
  `tattoo_description` varchar(300) NOT NULL,
  `color_scheme` enum('Monochrome','Multicolor') NOT NULL,
  `complexity_level` enum('Simple','Complex') NOT NULL,
  `cataloged` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tattoo`
--

INSERT INTO `tattoo` (`tattoo_id`, `tattoo_name`, `tattoo_price`, `tattoo_width`, `tattoo_height`, `tattoo_image`, `tattoo_description`, `color_scheme`, `complexity_level`, `cataloged`) VALUES
('10dd60e46f0ac26ee63e4a', 'Wise Owl', '540.00', 4, 7, '../images/uploads/15780799_1061740423955744_4413519727791422973_n.jpg', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillu', 'Monochrome', 'Complex', '2021-12-12 20:53:15'),
('1892042b3d927db5e83b76', 'Lighthouse', '700.00', 4, 10, '../images/uploads/123342788_3212057078924057_5416588542655299133_n.jpg', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillu', 'Monochrome', 'Complex', '2022-02-02 17:41:31'),
('25f398049719e2174ea6ef', 'Black Rose', '640.00', 2, 8, '../images/uploads/26907619_1399060816890368_1338040946270657769_n_blackrose.jpg', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillu', 'Monochrome', 'Simple', '2022-02-02 17:43:57'),
('38796b760849792efd36c6', 'Wukong', '370.00', 15, 15, '../images/uploads/15747889_1061583180638135_1193110947681960361_n.jpg', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillu', 'Multicolor', 'Complex', '2021-12-12 20:51:31'),
('4bf457f33eb25c5c22c112', 'Falcon', '550.00', 5, 7, '../images/uploads/15726485_1061582760638177_3110108616556778775_n.jpg', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillu', 'Monochrome', 'Complex', '2021-12-12 20:54:26'),
('661df3b4dc2755ea946e31', 'Sun', '300.00', 5, 5, '../images/uploads/15822879_1068286719967781_2434531283591267031_n.jpg', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillu', 'Multicolor', 'Simple', '2021-12-12 20:44:34'),
('6bd7b01ad2984ec1ee4245', 'Treasure Key', '400.00', 8, 13, '../images/uploads/15825764_1068286726634447_5703023164502817586_n.jpg', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillu', 'Monochrome', 'Complex', '2021-12-12 20:46:47'),
('bab904aa6b68c3761252be', 'Direwolf', '760.00', 1, 4, '../images/uploads/36398108_1568855383244243_1238202886337331200_n.jpg', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillu', 'Monochrome', 'Complex', '2022-02-02 17:45:50'),
('cbf64a4cefc121fa62f135', 'Howling Mist Dragon', '900.00', 4, 7, '../images/uploads/36336052_1568855623244219_7535254631723565056_n.jpg', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillu', 'Monochrome', 'Complex', '2022-02-02 17:42:58'),
('e1e65420a82b2ba0df5435', 'Native', '440.00', 10, 15, '../images/uploads/15726383_1061741057289014_2701360413810545874_n.jpg', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillu', 'Multicolor', 'Complex', '2021-12-12 20:55:21'),
('e373ce822a3a3b0776bdf2', 'Wings', '410.00', 23, 5, '../images/uploads/15741123_1061583193971467_8977369476092813072_n.jpg', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillu', 'Multicolor', 'Complex', '2021-12-12 20:49:58'),
('ef519051df4d5299639a6e', 'Spade', '370.00', 10, 10, '../images/uploads/15747554_1061740863955700_989799240189377030_n.jpg', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillu', 'Multicolor', 'Simple', '2021-12-12 20:48:24');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` char(22) NOT NULL,
  `client_id` char(22) DEFAULT NULL,
  `user_email` varchar(62) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_avatar` varchar(255) NOT NULL DEFAULT '../images/default-avatar.png',
  `user_type` enum('User','Admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `client_id`, `user_email`, `user_password`, `user_avatar`, `user_type`) VALUES
('256c26a79c2c83c3fb3e6b', '85af1b64a1a4db30ce16cb', 'admin@njctattoodb.com', '$2y$10$j5sjgoM6irYtjXjgTRn9G.FgKUVusuvE2868i0EMA76PE/18jsk/2', '../images/default-avatar.png', 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `workorder`
--

CREATE TABLE `workorder` (
  `order_id` char(22) NOT NULL,
  `client_id` char(22) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `amount_due_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `incentive` enum('None','15% discount','Free 3x3 tattoo') NOT NULL,
  `status` enum('Ongoing','Finished') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `workorder`
--

-- --------------------------------------------------------

--
-- Table structure for table `worksession`
--

CREATE TABLE `worksession` (
  `session_id` char(22) NOT NULL,
  `reservation_id` char(22) NOT NULL,
  `session_date` date NOT NULL,
  `session_status` enum('In Session','Finished') NOT NULL,
  `session_start_time` time NOT NULL DEFAULT current_timestamp(),
  `session_end_time` time NOT NULL,
  `session_address` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `worksession`
--

--
-- Indexes for dumped tables
--

--
-- Indexes for table `card`
--
ALTER TABLE `card`
  ADD PRIMARY KEY (`card_payment_id`),
  ADD KEY `payment_id_fk` (`payment_id`);

--
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`client_id`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `orderItem_workorderId_fk` (`order_id`),
  ADD KEY `orderItem_tattooId_fk` (`tattoo_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id_fk` (`order_id`);

--
-- Indexes for table `referral`
--
ALTER TABLE `referral`
  ADD PRIMARY KEY (`referral_id`),
  ADD KEY `referral_clientId_fk` (`client_id`),
  ADD KEY `referral_workorderId_fk` (`order_id`);

--
-- Indexes for table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `item_id_fk` (`item_id`);

--
-- Indexes for table `tattoo`
--
ALTER TABLE `tattoo`
  ADD PRIMARY KEY (`tattoo_id`),
  ADD UNIQUE KEY `tattoo_name` (`tattoo_name`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_email` (`user_email`),
  ADD KEY `user_clientId_fk` (`client_id`);

--
-- Indexes for table `workorder`
--
ALTER TABLE `workorder`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `workorder_clientId_fk` (`client_id`);

--
-- Indexes for table `worksession`
--
ALTER TABLE `worksession`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `worksession_reservationId_fk` (`reservation_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `card`
--
ALTER TABLE `card`
  ADD CONSTRAINT `payment_id_fk` FOREIGN KEY (`payment_id`) REFERENCES `payment` (`payment_id`);

--
-- Constraints for table `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `orderItem_tattooId_fk` FOREIGN KEY (`tattoo_id`) REFERENCES `tattoo` (`tattoo_id`),
  ADD CONSTRAINT `orderItem_workorderId_fk` FOREIGN KEY (`order_id`) REFERENCES `workorder` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `order_id_fk` FOREIGN KEY (`order_id`) REFERENCES `workorder` (`order_id`);

--
-- Constraints for table `referral`
--
ALTER TABLE `referral`
  ADD CONSTRAINT `referral_clientId_fk` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`),
  ADD CONSTRAINT `referral_workorderId_fk` FOREIGN KEY (`order_id`) REFERENCES `workorder` (`order_id`);

--
-- Constraints for table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `item_id_fk` FOREIGN KEY (`item_id`) REFERENCES `order_item` (`item_id`);

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_clientId_fk` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`);

--
-- Constraints for table `workorder`
--
ALTER TABLE `workorder`
  ADD CONSTRAINT `workorder_clientId_fk` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`);

--
-- Constraints for table `worksession`
--
ALTER TABLE `worksession`
  ADD CONSTRAINT `worksession_reservationId_fk` FOREIGN KEY (`reservation_id`) REFERENCES `reservation` (`reservation_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
