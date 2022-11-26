-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Nov 26, 2022 at 08:40 PM
-- Server version: 5.7.34
-- PHP Version: 7.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `emerails_test`
--
CREATE DATABASE IF NOT EXISTS `emerails_test` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `emerails_test`;

-- --------------------------------------------------------

--
-- Table structure for table `test_groups`
--

DROP TABLE IF EXISTS `test_groups`;
CREATE TABLE IF NOT EXISTS `test_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `test_groups`
--

INSERT INTO `test_groups` (`id`) VALUES
(1),
(2),
(3);

-- --------------------------------------------------------

--
-- Table structure for table `test_groups_test_models`
--

DROP TABLE IF EXISTS `test_groups_test_models`;
CREATE TABLE IF NOT EXISTS `test_groups_test_models` (
  `test_model_id` int(11) NOT NULL,
  `test_group_id` int(11) NOT NULL,
  `count` int(11) DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `test_group_id` (`test_group_id`,`test_model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `test_groups_test_models`
--

INSERT INTO `test_groups_test_models` (`test_model_id`, `test_group_id`, `count`) VALUES
(2, 1, 3),
(1, 2, 1),
(2, 2, 0);

-- --------------------------------------------------------

--
-- Table structure for table `test_models`
--

DROP TABLE IF EXISTS `test_models`;
CREATE TABLE IF NOT EXISTS `test_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(24) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=238 DEFAULT CHARSET=latin1 COMMENT='Test models for Emerails tests';

--
-- Dumping data for table `test_models`
--

INSERT INTO `test_models` (`id`, `name`) VALUES
(1, 'foo'),
(2, 'bar');

-- --------------------------------------------------------

--
-- Table structure for table `test_versions`
--

DROP TABLE IF EXISTS `test_versions`;
CREATE TABLE IF NOT EXISTS `test_versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `test_widget_id` int(11) NOT NULL,
  `version` varchar(24) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `widget_id` (`test_widget_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `test_versions`
--

INSERT INTO `test_versions` (`id`, `test_widget_id`, `version`) VALUES
(1, 1, '1.0'),
(2, 1, '1.1'),
(3, 1, '1.2'),
(4, 1, '2.0'),
(5, 2, '0.1');

-- --------------------------------------------------------

--
-- Table structure for table `test_widgets`
--

DROP TABLE IF EXISTS `test_widgets`;
CREATE TABLE IF NOT EXISTS `test_widgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `test_model_id` int(11) NOT NULL,
  `color` varchar(24) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `model_id` (`test_model_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `test_widgets`
--

INSERT INTO `test_widgets` (`id`, `test_model_id`, `color`) VALUES
(1, 1, 'red'),
(2, 2, 'blue'),
(3, 2, 'green');
COMMIT;
