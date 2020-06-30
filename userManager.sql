-- phpMyAdmin SQL Dump
-- version 4.4.10
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Aug 17, 2019 at 02:04 PM
-- Server version: 5.5.42
-- PHP Version: 7.0.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `user_manager`
--

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `posts_id` int(10) NOT NULL,
  `posts_usero_id` int(10) NOT NULL,
  `posts_text` text COLLATE utf8_unicode_ci NOT NULL,
  `posts_date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `posts_date_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `replies`
--

CREATE TABLE `replies` (
  `replies_id` int(10) NOT NULL,
  `replies_usero_id` int(10) NOT NULL,
  `replies_posts_id` int(10) NOT NULL,
  `replies_text` text COLLATE utf8_unicode_ci NOT NULL,
  `replies_date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `replies_date_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usero`
--

CREATE TABLE `usero` (
  `usero_id` int(10) NOT NULL,
  `usero_firstname` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `usero_surname` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `usero_email` varchar(40) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `users_id` int(10) unsigned NOT NULL,
  `users_type` enum('member','admin') COLLATE utf8_swedish_ci NOT NULL,
  `users_username` varchar(15) COLLATE utf8_swedish_ci NOT NULL,
  `users_pass` blob NOT NULL,
  `users_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `users_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`users_id`, `users_type`, `users_username`, `users_pass`, `users_updated`, `users_created`) VALUES
(1, 'admin', 'admin123', 0xd9c05f47acf76e1d30be210f557ce92a, '2019-06-21 18:23:32', '2019-06-21 18:23:32'),
(2, 'admin', 'fritz', 0xd9c05f47acf76e1d30be210f557ce92a, '2019-07-25 09:35:09', '2015-01-04 20:51:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`posts_id`),
  ADD KEY `posts_fk0` (`posts_usero_id`);

--
-- Indexes for table `replies`
--
ALTER TABLE `replies`
  ADD PRIMARY KEY (`replies_id`);

--
-- Indexes for table `usero`
--
ALTER TABLE `usero`
  ADD PRIMARY KEY (`usero_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`users_id`),
  ADD UNIQUE KEY `username` (`users_username`),
  ADD UNIQUE KEY `username_2` (`users_username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `posts_id` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `replies`
--
ALTER TABLE `replies`
  MODIFY `replies_id` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `usero`
--
ALTER TABLE `usero`
  MODIFY `usero_id` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `users_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=36;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_fk0` FOREIGN KEY (`posts_usero_id`) REFERENCES `usero` (`usero_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
