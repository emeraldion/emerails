-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Jul 04, 2023 at 01:20 PM
-- Server version: 5.7.34
-- PHP Version: 7.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `emerails_test`
--
CREATE DATABASE IF NOT EXISTS `emerails_test` DEFAULT CHARACTER SET latin1 COLLATE latin1_general_cs;
USE `emerails_test`;

-- --------------------------------------------------------

--
-- Table structure for table `athletes`
--

DROP TABLE IF EXISTS `athletes`;
CREATE TABLE `athletes` (
  `id` int(11) NOT NULL,
  `weight` int(11) NOT NULL DEFAULT '0' COMMENT 'Weight in kg',
  `height` float NOT NULL DEFAULT '0' COMMENT 'Height in cm',
  `name` varchar(24) COLLATE latin1_general_cs NOT NULL,
  `foo` datetime DEFAULT NULL,
  `bar` timestamp NULL DEFAULT NULL,
  `baz` tinyint(4) DEFAULT NULL,
  `shirt_color` enum('red','green','blue') COLLATE latin1_general_cs DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `coches`
--

DROP TABLE IF EXISTS `coches`;
CREATE TABLE `coches` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `coches`
--

INSERT INTO `coches` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `motores`
--

DROP TABLE IF EXISTS `motores`;
CREATE TABLE `motores` (
  `id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `motores`
--

INSERT INTO `motores` (`id`, `car_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `test_groups`
--

DROP TABLE IF EXISTS `test_groups`;
CREATE TABLE `test_groups` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `test_groups`
--

INSERT INTO `test_groups` (`id`, `created_at`) VALUES
(1, '2023-01-05 22:21:48'),
(2, '2023-01-05 22:21:48'),
(3, '2023-01-05 22:21:48');

-- --------------------------------------------------------

--
-- Table structure for table `test_groups_test_models`
--

DROP TABLE IF EXISTS `test_groups_test_models`;
CREATE TABLE `test_groups_test_models` (
  `id` int(11) NOT NULL,
  `test_model_id` int(11) NOT NULL,
  `test_group_id` int(11) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  `color` enum('red','green','blue') CHARACTER SET latin1 COLLATE latin1_general_cs DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `test_groups_test_models`
--

INSERT INTO `test_groups_test_models` (`id`, `test_model_id`, `test_group_id`, `count`, `color`, `created_at`) VALUES
(1, 2, 1, 3, NULL, '2023-01-05 22:21:48'),
(2, 1, 2, 1, NULL, '2023-01-05 22:21:48'),
(3, 2, 2, 0, NULL, '2023-01-05 22:21:48');

-- --------------------------------------------------------

--
-- Table structure for table `test_models`
--

DROP TABLE IF EXISTS `test_models`;
CREATE TABLE `test_models` (
  `id` int(11) NOT NULL,
  `name` varchar(24) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Test models for Emerails tests';

--
-- Dumping data for table `test_models`
--

INSERT INTO `test_models` (`id`, `name`, `created_at`) VALUES
(1, 'foo', '2023-01-05 22:21:48'),
(2, 'bar', '2023-01-05 22:21:48');

-- --------------------------------------------------------

--
-- Table structure for table `test_versions`
--

DROP TABLE IF EXISTS `test_versions`;
CREATE TABLE `test_versions` (
  `id` int(11) NOT NULL,
  `test_widget_id` int(11) DEFAULT NULL,
  `version` varchar(24) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `test_versions`
--

INSERT INTO `test_versions` (`id`, `test_widget_id`, `version`, `created_at`) VALUES
(1, 1, '1.0', '2023-01-05 22:21:48'),
(2, 1, '1.1', '2023-01-05 22:21:48'),
(3, 1, '1.2', '2023-01-05 22:21:48'),
(4, 1, '2.0', '2023-01-05 22:21:48'),
(5, 2, '0.1', '2023-01-05 22:21:48');

-- --------------------------------------------------------

--
-- Table structure for table `test_widgets`
--

DROP TABLE IF EXISTS `test_widgets`;
CREATE TABLE `test_widgets` (
  `id` int(11) NOT NULL,
  `test_model_id` int(11) DEFAULT NULL,
  `color` varchar(24) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `test_widgets`
--

INSERT INTO `test_widgets` (`id`, `test_model_id`, `color`, `created_at`) VALUES
(1, 1, 'red', '2023-01-05 22:21:48'),
(2, 2, 'blue', '2023-01-05 22:21:48'),
(3, 2, 'green', '2023-01-05 22:21:48');

-- --------------------------------------------------------

--
-- Table structure for table `user_accounts`
--

DROP TABLE IF EXISTS `user_accounts`;
CREATE TABLE `user_accounts` (
  `id` int(11) NOT NULL,
  `username` varchar(24) NOT NULL,
  `password` varchar(24) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

DROP TABLE IF EXISTS `user_profiles`;
CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `username` varchar(24) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `athletes`
--
ALTER TABLE `athletes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coches`
--
ALTER TABLE `coches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `motores`
--
ALTER TABLE `motores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `car_id` (`car_id`);

--
-- Indexes for table `test_groups`
--
ALTER TABLE `test_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `test_groups_test_models`
--
ALTER TABLE `test_groups_test_models`
  ADD PRIMARY KEY (`id`),
  ADD KEY `test_group_id` (`test_group_id`,`test_model_id`);

--
-- Indexes for table `test_models`
--
ALTER TABLE `test_models`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `test_versions`
--
ALTER TABLE `test_versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `widget_id` (`test_widget_id`);

--
-- Indexes for table `test_widgets`
--
ALTER TABLE `test_widgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `model_id` (`test_model_id`);

--
-- Indexes for table `user_accounts`
--
ALTER TABLE `user_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `athletes`
--
ALTER TABLE `athletes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `coches`
--
ALTER TABLE `coches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `motores`
--
ALTER TABLE `motores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `test_groups`
--
ALTER TABLE `test_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=407;

--
-- AUTO_INCREMENT for table `test_groups_test_models`
--
ALTER TABLE `test_groups_test_models`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `test_models`
--
ALTER TABLE `test_models`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3785;

--
-- AUTO_INCREMENT for table `test_versions`
--
ALTER TABLE `test_versions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=285;

--
-- AUTO_INCREMENT for table `test_widgets`
--
ALTER TABLE `test_widgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=252;

--
-- AUTO_INCREMENT for table `user_accounts`
--
ALTER TABLE `user_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;
COMMIT;
