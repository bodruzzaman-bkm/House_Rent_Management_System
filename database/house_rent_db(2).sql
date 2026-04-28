-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2026 at 05:54 AM
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
-- Database: `house_rent_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `agreement`
--

CREATE TABLE `agreement` (
  `agreement_id` int(11) NOT NULL,
  `advance` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `owner_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaint`
--

CREATE TABLE `complaint` (
  `complaint_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `issue` varchar(1000) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `flat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `flat`
--

CREATE TABLE `flat` (
  `flat_id` int(11) NOT NULL,
  `area` int(11) NOT NULL,
  `location` varchar(100) NOT NULL,
  `asking_rent` int(11) NOT NULL,
  `bedroom` int(11) NOT NULL,
  `floor` int(11) NOT NULL,
  `detailed_location` varchar(2000) NOT NULL,
  `status` varchar(50) NOT NULL,
  `owner_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `flat`
--

INSERT INTO `flat` (`flat_id`, `area`, `location`, `asking_rent`, `bedroom`, `floor`, `detailed_location`, `status`, `owner_id`) VALUES
(1, 1200, 'South Badda', 23000, 2, 6, 'Rajnandan, Ka, 3/1, Titas Road, South Badda, Dhaka', 'Available', 2),
(2, 1200, 'Merul Badda', 20000, 3, 2, 'House No: 12, Road No: 16, DIT Project, Merul Badda, Dhaka', 'Available', 2),
(3, 20000, 'Adabor', 30000, 3, 5, 'Adabor-1, Shompa Market', 'Available', 2),
(4, 1000, 'Mohammadpur', 15000, 2, 5, 'Nurjahan Road, Mohammadpur', 'Available', 2),
(5, 1200, 'Nakhalpara', 15000, 2, 6, 'Lucas Mor, Nakhalpara', 'Available', 2);

-- --------------------------------------------------------

--
-- Table structure for table `links`
--

CREATE TABLE `links` (
  `agreement_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `flat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `monthly_bill`
--

CREATE TABLE `monthly_bill` (
  `agreement_id` int(11) NOT NULL,
  `billing_month` varchar(50) NOT NULL,
  `base_rent` int(11) NOT NULL,
  `maintanance` int(11) NOT NULL,
  `electricity` int(11) NOT NULL,
  `gas` int(11) NOT NULL,
  `payment_status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `owner`
--

CREATE TABLE `owner` (
  `user_id` int(11) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `account_number` varchar(20) NOT NULL,
  `branch_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owner`
--

INSERT INTO `owner` (`user_id`, `bank_name`, `account_number`, `branch_name`) VALUES
(2, 'Brac Bank', '125874963587', 'Mohakhali'),
(3, 'Estern Bank', '522147932587458', 'Mohammadpur, Dhaka');

-- --------------------------------------------------------

--
-- Table structure for table `rating`
--

CREATE TABLE `rating` (
  `rating_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `review_text` varchar(2000) NOT NULL,
  `rating_by` varchar(100) NOT NULL,
  `agreement_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `request`
--

CREATE TABLE `request` (
  `request_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `request_status` varchar(50) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `flat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenant`
--

CREATE TABLE `tenant` (
  `user_id` int(11) NOT NULL,
  `permanent_address` varchar(255) NOT NULL,
  `occupation` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenant`
--

INSERT INTO `tenant` (`user_id`, `permanent_address`, `occupation`) VALUES
(1, 'Dhaka', 'Student');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `nid` char(10) NOT NULL,
  `phone` char(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `name`, `nid`, `phone`, `email`, `password`) VALUES
(1, 'Bodruzzaman', '1234567890', '01234567891', 'abc@abc.com', '$2y$10$QV/.wTLc5lqoYjqV7qrPfuoKJ6Z3P7sOEShMgMS6eHWdB3ZWvIrSG'),
(2, 'Hasanuzzaman', '5412635871', '01254698724', 'def@def.com', '$2y$10$ZqFIumnxDjoilGz0vSD7/.aLQey1QrPo2.BL2RnAh9q0C26R40ezO'),
(3, 'Nazrul Islam', '2541896357', '15789632874', 'nazrul@abc.com', '$2y$10$KZG7p/blLyDx7XQdhbAjgOBWo3ri0SzGWl8NkVEVt12m.9mmX90XW');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agreement`
--
ALTER TABLE `agreement`
  ADD PRIMARY KEY (`agreement_id`),
  ADD KEY `fk_agreement_owner_user_id` (`owner_id`);

--
-- Indexes for table `complaint`
--
ALTER TABLE `complaint`
  ADD PRIMARY KEY (`complaint_id`),
  ADD KEY `fk_complaint_tenant_user_id` (`tenant_id`),
  ADD KEY `fk_complaint_flat_flat_id` (`flat_id`);

--
-- Indexes for table `flat`
--
ALTER TABLE `flat`
  ADD PRIMARY KEY (`flat_id`),
  ADD KEY `fk_flat_owner_user_id` (`owner_id`);

--
-- Indexes for table `links`
--
ALTER TABLE `links`
  ADD PRIMARY KEY (`agreement_id`,`tenant_id`,`flat_id`),
  ADD KEY `fk_links_flat_flat_id` (`flat_id`),
  ADD KEY `fk_links_tenant_user_id` (`tenant_id`);

--
-- Indexes for table `monthly_bill`
--
ALTER TABLE `monthly_bill`
  ADD PRIMARY KEY (`agreement_id`,`billing_month`);

--
-- Indexes for table `owner`
--
ALTER TABLE `owner`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `fk_rating_agreement_agreement_id` (`agreement_id`);

--
-- Indexes for table `request`
--
ALTER TABLE `request`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `fk_request_flat_flat_id` (`flat_id`),
  ADD KEY `fk_request_tenant_user_id` (`tenant_id`);

--
-- Indexes for table `tenant`
--
ALTER TABLE `tenant`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `nid` (`nid`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agreement`
--
ALTER TABLE `agreement`
  MODIFY `agreement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaint`
--
ALTER TABLE `complaint`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `flat`
--
ALTER TABLE `flat`
  MODIFY `flat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rating`
--
ALTER TABLE `rating`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `request`
--
ALTER TABLE `request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agreement`
--
ALTER TABLE `agreement`
  ADD CONSTRAINT `fk_agreement_owner_user_id` FOREIGN KEY (`owner_id`) REFERENCES `owner` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `complaint`
--
ALTER TABLE `complaint`
  ADD CONSTRAINT `fk_complaint_flat_flat_id` FOREIGN KEY (`flat_id`) REFERENCES `flat` (`flat_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_complaint_tenant_user_id` FOREIGN KEY (`tenant_id`) REFERENCES `tenant` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `flat`
--
ALTER TABLE `flat`
  ADD CONSTRAINT `fk_flat_owner_user_id` FOREIGN KEY (`owner_id`) REFERENCES `owner` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `links`
--
ALTER TABLE `links`
  ADD CONSTRAINT `fk_links_agreement_agreement_id` FOREIGN KEY (`agreement_id`) REFERENCES `agreement` (`agreement_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_links_flat_flat_id` FOREIGN KEY (`flat_id`) REFERENCES `flat` (`flat_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_links_tenant_user_id` FOREIGN KEY (`tenant_id`) REFERENCES `tenant` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `monthly_bill`
--
ALTER TABLE `monthly_bill`
  ADD CONSTRAINT `fk_monthly_bill_agreement_agreement_id` FOREIGN KEY (`agreement_id`) REFERENCES `agreement` (`agreement_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `owner`
--
ALTER TABLE `owner`
  ADD CONSTRAINT `fk_owner_user_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rating`
--
ALTER TABLE `rating`
  ADD CONSTRAINT `fk_rating_agreement_agreement_id` FOREIGN KEY (`agreement_id`) REFERENCES `agreement` (`agreement_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `request`
--
ALTER TABLE `request`
  ADD CONSTRAINT `fk_request_flat_flat_id` FOREIGN KEY (`flat_id`) REFERENCES `flat` (`flat_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_request_tenant_user_id` FOREIGN KEY (`tenant_id`) REFERENCES `tenant` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tenant`
--
ALTER TABLE `tenant`
  ADD CONSTRAINT `fk_tenant_user_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
