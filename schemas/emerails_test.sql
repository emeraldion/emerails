-- phpMyAdmin SQL Dump
-- version 4.4.10
-- http://www.phpmyadmin.net
--
-- Host: localhost:8889
-- Generation Time: Apr 02, 2016 at 02:03 AM
-- Server version: 5.5.42
-- PHP Version: 5.6.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `test_groups_test_models`
--

DROP TABLE IF EXISTS `test_groups_test_models`;
CREATE TABLE IF NOT EXISTS `test_groups_test_models` (
  `test_model_id` int(11) NOT NULL,
  `test_group_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `test_groups_test_models`
--

INSERT INTO `test_groups_test_models` (`test_model_id`, `test_group_id`) VALUES
(2, 1),
(1, 2),
(2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `test_models`
--

DROP TABLE IF EXISTS `test_models`;
CREATE TABLE IF NOT EXISTS `test_models` (
  `id` int(11) NOT NULL,
  `name` varchar(24) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COMMENT='Test models for Emerails tests';

--
-- Dumping data for table `test_models`
--

INSERT INTO `test_models` (`id`, `name`) VALUES
(1, 'foo'),
(2, 'bar');

-- --------------------------------------------------------

--
-- Table structure for table `test_widgets`
--

DROP TABLE IF EXISTS `test_widgets`;
CREATE TABLE IF NOT EXISTS `test_widgets` (
  `id` int(11) NOT NULL,
  `test_model_id` int(11) NOT NULL,
  `color` varchar(24) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `test_widgets`
--

INSERT INTO `test_widgets` (`id`, `test_model_id`, `color`) VALUES
(1, 1, 'red'),
(2, 2, 'blue');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `test_groups`
--
ALTER TABLE `test_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `test_groups_test_models`
--
ALTER TABLE `test_groups_test_models`
  ADD KEY `test_group_id` (`test_group_id`,`test_model_id`);

--
-- Indexes for table `test_models`
--
ALTER TABLE `test_models`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `test_widgets`
--
ALTER TABLE `test_widgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `model_id` (`test_model_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `test_groups`
--
ALTER TABLE `test_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `test_models`
--
ALTER TABLE `test_models`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `test_widgets`
--
ALTER TABLE `test_widgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
