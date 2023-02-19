-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 19, 2023 at 03:48 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `user_manager`
--
CREATE DATABASE IF NOT EXISTS `user_manager` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `user_manager`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `users_id` int(10) UNSIGNED NOT NULL,
  `users_type` enum('member','admin') NOT NULL,
  `users_first_name` varchar(20) NOT NULL,
  `users_last_name` varchar(20) NOT NULL,
  `users_username` varchar(15) NOT NULL,
  `users_phone_number` varchar(15) DEFAULT NULL,
  `users_email` varchar(30) NOT NULL,
  `users_pass` blob NOT NULL,
  `users_emailverified` enum('no','yes') NOT NULL DEFAULT 'no',
  `users_eactivationcode` varchar(100) DEFAULT NULL,
  `users_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `users_created` timestamp(6) NOT NULL DEFAULT current_timestamp(6)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`users_id`, `users_type`, `users_first_name`, `users_last_name`, `users_username`, `users_phone_number`, `users_email`, `users_pass`, `users_emailverified`, `users_eactivationcode`, `users_updated`, `users_created`) VALUES
(44, 'admin', 'fritz', 'Frezo', 'fritz', NULL, 'fritz@camcom.com', 0xc8cde018de3e91d82112fce52b853412, 'yes', NULL, '2023-02-19 14:29:33', '2023-01-23 17:23:41.247104'),
(45, 'member', 'john', 'Colon', 'john', NULL, 'john@colon.com', 0xc8cde018de3e91d82112fce52b853412, 'yes', NULL, '2023-02-19 14:29:20', '2023-01-23 17:23:41.247104');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`users_id`),
  ADD UNIQUE KEY `username` (`users_username`,`users_email`),
  ADD UNIQUE KEY `username_2` (`users_username`),
  ADD UNIQUE KEY `email` (`users_email`),
  ADD KEY `emailverify` (`users_emailverified`),
  ADD KEY `phone_number` (`users_phone_number`),
  ADD KEY `eactivationcode` (`users_eactivationcode`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `users_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
