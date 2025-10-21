-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 01, 2022 at 11:58 AM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 7.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jamakol`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `activity_time` datetime NOT NULL,
  `phone` varchar(20) NOT NULL,
  `activity` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`activity_time`, `phone`, `activity`) VALUES
('2022-08-01 13:23:49', '9445321790', 'Jamakol|Chennai|2022-08-01|13:19:05'),
('2022-08-01 13:24:09', '9445321790', 'Jamakol|Chennai|2022-08-01|17:23:05'),
('2022-08-01 13:35:55', '9445321790', 'ThaaraPalan'),
('2022-08-01 13:39:19', '9445321790', 'Hora'),
('2022-08-01 13:40:52', '9445321790', 'PMRJama|Chennai|2022-08-01|13:39:53'),
('2022-08-01 13:41:27', '9445321790', 'BNN|Chennai|2022-08-01|13:41:22|2022-08-01|13:41:25'),
('2022-08-01 13:41:46', '9445321790', 'BNN|Chennai|1976-07-17|14:14:22|2022-08-01|13:41:25'),
('2022-08-01 13:50:58', '9445321790', 'ThaaraPalan');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`activity_time`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
