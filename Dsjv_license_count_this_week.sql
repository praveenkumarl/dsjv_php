-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 01, 2024 at 09:33 AM
-- Server version: 10.11.7-MariaDB-cll-lve
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u835731528_dsjv`
--

-- --------------------------------------------------------

--
-- Structure for view `Dsjv license count this week`
--

CREATE ALGORITHM=UNDEFINED DEFINER=`u835731528_dsjv`@`%` SQL SECURITY DEFINER VIEW `Dsjv license count this week`  AS SELECT count(0) AS `THIS WEEK TOTAL` FROM `user` WHERE `user`.`user_type` in ('PAID','SUPER') AND `user`.`name` not like '%TEST%' AND week(`user`.`registered_date`) = week(current_timestamp()) ;

--
-- VIEW `Dsjv license count this week`
-- Data: None
--

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
