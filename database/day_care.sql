-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 27, 2026 at 12:05 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `day_care`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity`
--

CREATE TABLE `activity` (
  `activity_id` int(11) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `child_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity`
--

INSERT INTO `activity` (`activity_id`, `activity_type`, `description`, `date`, `time`, `child_id`) VALUES
(1, 'Art Class', 'Painting and drawing activities for kids', '2025-01-21', '10:00:00', 1),
(2, 'Music Class', 'Learning basic music instruments', '2025-01-22', '11:00:00', 2),
(3, 'Story Time', 'Reading children’s books', '2025-01-23', '09:30:00', 3),
(4, 'Outdoor Games', 'Running and playing outdoors', '2025-01-24', '14:00:00', 4);

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('Present','Absent') NOT NULL,
  `child_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `date`, `status`, `child_id`) VALUES
(1, '2025-01-21', 'Present', 1),
(2, '2025-01-22', 'Absent', 2),
(3, '2025-01-23', 'Present', 3),
(4, '2025-01-24', 'Present', 4);

-- --------------------------------------------------------

--
-- Table structure for table `bill_payment`
--

CREATE TABLE `bill_payment` (
  `bill_id` int(11) NOT NULL,
  `section_type` varchar(50) NOT NULL,
  `section_description` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_date` date NOT NULL,
  `payment_status` enum('Paid','Pending','Overdue') NOT NULL,
  `user_id` int(11) NOT NULL,
  `child_type` enum('Normal','Special') NOT NULL,
  `receipt_document` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bill_payment`
--

INSERT INTO `bill_payment` (`bill_id`, `section_type`, `section_description`, `total_amount`, `paid_amount`, `payment_date`, `payment_status`, `user_id`, `child_type`, `receipt_document`) VALUES
(1, 'Tuition', 'Payment for the semester', 500.00, 500.00, '2025-01-20', 'Paid', 1, 'Normal', NULL),
(2, 'Meal', 'Meals for children during the month', 300.00, 300.00, '2025-01-19', 'Paid', 2, 'Special', NULL),
(3, 'Daycare Booking', 'Booked 4 day(s) for Clark Kent at $20/day. Dates: 2026-08-31, 2026-09-01, 2026-09-02, 2026-09-04', 0.00, 0.00, '2026-08-31', 'Paid', 5, '', '1788145406_John-Doe-Payment-Recipt.pdf'),
(4, 'Daycare Booking', 'Booked 5 day(s) for Mia Carson at $20/day. Dates: 2026-09-07, 2026-09-08, 2026-09-09, 2026-09-10, 2026-09-11', 0.00, 0.00, '2026-09-02', 'Paid', 1, '', NULL),
(5, 'Daycare Booking', 'Booked 1 day(s) for Clark Kent at $20/day. Dates: ', 20.00, 0.00, '2026-09-02', '', 5, '', NULL),
(6, 'Daycare Booking', 'Booked 31 day(s) for Adam Daniels at $20/day. Dates: 2026-10-01, 2026-10-02, 2026-10-03, 2026-10-04, 2026-10-05, 2026-10-06, 2026-10-07, 2026-10-08, 2026-10-09, 2026-10-10, 2026-10-11, 2026-10-12, 2026-10-13, 2026-10-14, 2026-10-15, 2026-10-18, 2026-10-25, 2026-10-26, 2026-10-19, 2026-10-20, 2026-10-27, 2026-10-28, 2026-10-21, 2026-10-22, 2026-10-29, 2026-10-30, 2026-10-23, 2026-10-16, 2026-10-17, 2026-10-24, 2026-10-31', 620.00, 620.00, '2026-09-03', 'Paid', 6, '', NULL),
(7, 'Daycare Booking', 'Booked 31 day(s) for Casey Daniels at $20/day. Dates: 2026-10-01, 2026-10-02, 2026-10-03, 2026-10-04, 2026-10-05, 2026-10-06, 2026-10-07, 2026-10-08, 2026-10-09, 2026-10-10, 2026-10-11, 2026-10-12, 2026-10-13, 2026-10-14, 2026-10-15, 2026-10-16, 2026-10-17, 2026-10-18, 2026-10-19, 2026-10-20, 2026-10-21, 2026-10-22, 2026-10-23, 2026-10-24, 2026-10-25, 2026-10-26, 2026-10-27, 2026-10-28, 2026-10-29, 2026-10-30, 2026-10-31', 620.00, 620.00, '2026-09-03', 'Paid', 6, '', NULL),
(8, 'Daycare Booking', 'Booked 4 day(s) for Marryane May at $20/day. Dates: 2026-10-22, 2026-10-06, 2026-10-07, 2026-10-08', 80.00, 0.00, '2026-09-03', '', 8, '', NULL),
(9, '', 'Booking for Marryane May (ID: 10) – 4 day(s) on 2026-10-22,  2026-10-06,  2026-10-07,  2026-10-08. Payment: Cash', 80.00, 80.00, '2026-09-03', 'Paid', 8, 'Normal', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `caregivers`
--

CREATE TABLE `caregivers` (
  `caregiver_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `qualification` varchar(255) NOT NULL,
  `experience` int(11) NOT NULL,
  `special_training` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `caregivers`
--

INSERT INTO `caregivers` (`caregiver_id`, `name`, `email`, `password`, `qualification`, `experience`, `special_training`, `profile_image`) VALUES
(1, 'Emma Green', 'emma.green@example.com', '$2y$10$qnWBwFnYP45n7s2AEdIYAOz1kwh6/V2AvFipWZa9OgAvm6.Bk366.', 'Nursing', 5, 'Pediatric Care', 'emma_green.jpg'),
(2, 'Olivia Brown', 'olivia.brown@example.com', '$2y$10$wu8NBruUEGFrn.wGfHucaOBpX8.3e.bIO1nAti90AN2RFZ6qlKncS', 'Psychology', 3, 'Child Therapy', 'olivia_brown.jpg'),
(3, 'John Cena', 'johncena@gmail.com', '$2y$10$T1jBUyJhybv9bxNlnpEkMOd8ex0gYriw1Fd0xvZznxkWJGiQSoGHO', 'Teacher', 2, 'Physical Education', 'profile_6a7fc33176eb03.93968432.jpg'),
(16, 'Liam Hemsworth', 'liam@gmail.com', '', 'Physical Education Teacher', 2, 'First Aid', 'liam.jpg'),
(18, 'Will Smith', 'willsmith@gmail.com', '', 'Acting and Drama', 4, 'Drama School', 'will-smith.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `child`
--

CREATE TABLE `child` (
  `child_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `medical_history` text DEFAULT NULL,
  `special_needs` enum('Yes','No') NOT NULL,
  `user_id` int(11) NOT NULL,
  `child_image` varchar(255) DEFAULT NULL,
  `medical_records` varchar(255) DEFAULT NULL,
  `medical_reports` varchar(255) DEFAULT NULL,
  `birth_certificate` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `child`
--

INSERT INTO `child` (`child_id`, `name`, `date_of_birth`, `gender`, `medical_history`, `special_needs`, `user_id`, `child_image`, `medical_records`, `medical_reports`, `birth_certificate`) VALUES
(1, 'Alice Doe', '2010-01-01', 'female', 'None', 'No', 1, NULL, NULL, NULL, NULL),
(2, 'Charlie Doe', '2012-02-15', 'male', 'Asthma', 'Yes', 1, NULL, NULL, NULL, NULL),
(3, 'Sophia Smith', '2013-04-20', 'female', 'None', 'No', 2, NULL, NULL, NULL, NULL),
(4, 'Liam Smith', '2015-06-30', 'male', 'Allergies', 'Yes', 2, NULL, NULL, NULL, NULL),
(5, 'Mark Henry', '2024-02-22', 'male', 'Obese', 'Yes', 4, NULL, NULL, NULL, NULL),
(6, 'Clark Kent', '2025-05-22', 'male', 'Sinus', 'Yes', 5, '1788141518_johncena.jpg', NULL, NULL, NULL),
(7, 'Mia Carson', '2021-07-12', 'female', 'None', 'No', 1, '1788318493_mia.jpg', NULL, NULL, NULL),
(8, 'Adam Daniels', '2023-06-03', 'male', 'Asthmatic', 'Yes', 6, '1788353253_adam.jpg', NULL, NULL, NULL),
(9, 'Casey Daniels', '2025-02-02', 'female', 'No', 'No', 6, '1788353339_casey.jpg', NULL, NULL, NULL),
(10, 'Marryane May', '2025-06-04', 'female', 'Pet Allergies', 'Yes', 8, NULL, 'med_6a98486ef08fc.pdf', 'report_6a98486ef1e66.pdf', 'bc_6a98486eef9d8.pdf'),
(13, 'Meli Brown', '2024-01-17', 'male', 'None', 'No', 2, NULL, 'med_6a98b85f4c5e1.pdf', 'report_6a98b85f4d3ca.pdf', 'bc_6a98b85f4b6ee.pdf'),
(14, 'Jennet', '2020-10-03', 'female', '', 'No', 19, NULL, 'med_6a98d2acefc70.pdf', NULL, 'bc_6a98d2aced894.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `child_caregiver`
--

CREATE TABLE `child_caregiver` (
  `child_id` int(11) NOT NULL,
  `caregiver_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `child_caregiver`
--

INSERT INTO `child_caregiver` (`child_id`, `caregiver_id`) VALUES
(1, 1),
(2, 1),
(3, 2),
(4, 2),
(6, 2),
(7, 2),
(9, 16);

-- --------------------------------------------------------

--
-- Table structure for table `learning`
--

CREATE TABLE `learning` (
  `learning_id` int(11) NOT NULL,
  `learning_type` varchar(255) NOT NULL,
  `progress_notes` text DEFAULT NULL,
  `date` date NOT NULL,
  `child_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `learning`
--

INSERT INTO `learning` (`learning_id`, `learning_type`, `progress_notes`, `date`, `child_id`) VALUES
(1, 'Math', 'Good progress in arithmetic', '2025-01-20', 1),
(2, 'Science', 'Needs improvement in experiments', '2025-01-19', 2),
(3, 'Reading', 'Improved comprehension', '2025-01-18', 3),
(4, 'Writing', 'Needs help with handwriting', '2025-01-17', 4);

-- --------------------------------------------------------

--
-- Table structure for table `meal`
--

CREATE TABLE `meal` (
  `meal_id` int(11) NOT NULL,
  `meal_name` varchar(100) NOT NULL,
  `allergies` varchar(255) DEFAULT NULL,
  `date` date NOT NULL,
  `child_id` int(11) NOT NULL,
  `bill_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal`
--

INSERT INTO `meal` (`meal_id`, `meal_name`, `allergies`, `date`, `child_id`, `bill_id`) VALUES
(1, 'Pasta', 'None', '2025-01-21', 1, 1),
(2, 'Sandwich', 'Peanut', '2025-01-22', 2, 2),
(3, 'Fruit Salad', 'None', '2025-01-23', 3, 1),
(4, 'Rice and Chicken', 'None', '2025-01-24', 4, 2);

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notification_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `status` enum('Unread','Read') NOT NULL,
  `created_at` datetime NOT NULL,
  `sent_at` datetime DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `child_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`notification_id`, `message`, `notification_type`, `status`, `created_at`, `sent_at`, `read_at`, `user_id`, `child_id`) VALUES
(1, 'Your child Alice has completed the art class.', 'Activity Completed', 'Unread', '2025-01-21 10:00:00', NULL, NULL, 1, 1),
(2, 'Your child Sophia has been marked present.', 'Attendance', 'Read', '2025-01-23 09:00:00', '2025-01-23 09:05:00', '2025-01-23 09:10:00', 2, 3);

-- --------------------------------------------------------

--
-- Table structure for table `pickdrop`
--

CREATE TABLE `pickdrop` (
  `pickdrop_id` int(11) NOT NULL,
  `time` time NOT NULL,
  `status` enum('Picked','Dropped') NOT NULL,
  `notification_status` enum('Sent','Not Sent') NOT NULL,
  `child_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pickdrop`
--

INSERT INTO `pickdrop` (`pickdrop_id`, `time`, `status`, `notification_status`, `child_id`) VALUES
(1, '08:00:00', 'Picked', 'Sent', 1),
(2, '08:30:00', 'Dropped', 'Sent', 2),
(3, '09:00:00', 'Picked', 'Not Sent', 3),
(4, '09:30:00', 'Dropped', 'Not Sent', 4);

-- --------------------------------------------------------

--
-- Table structure for table `specialcare`
--

CREATE TABLE `specialcare` (
  `special_care_id` int(11) NOT NULL,
  `medical_condition` text NOT NULL,
  `support_type` varchar(255) NOT NULL,
  `child_id` int(11) NOT NULL,
  `bill_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `specialcare`
--

INSERT INTO `specialcare` (`special_care_id`, `medical_condition`, `support_type`, `child_id`, `bill_id`) VALUES
(1, 'Asthma', 'Inhaler Usage', 2, 2),
(2, 'Allergies', 'Avoidance of certain foods', 4, 2);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('Parent','Admin','Caregiver') NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact_info` varchar(15) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `role`, `password`, `contact_info`, `profile_image`) VALUES
(1, 'John Doe', 'john.doe@example.com', 'Parent', 'password123', '123-456-7890', 'john_doe.jpg'),
(2, 'Jane Smith', 'jane.smith@example.com', 'Parent', 'password123', '987-654-3210', 'jane_smith.jpg'),
(3, 'Admin', 'admin@gmail.com', 'Admin', 'admin123', '555-123-4567', 'admin.jpg'),
(4, 'Big Show', 'bigshow@gmail.com', 'Parent', '$2y$10$MdA4p5DBIpvoryYDrlDbH.P.rlQdRRJYpqJ96zaDLlCnJFE76dDNy', '6792933211', 'johncena.jpg'),
(5, 'John Doe', 'johndoe@gmail.com', 'Parent', '$2y$10$ppbV3gow8KwKwMODTpvLQu2RfLbgotQX6kYdegOu3AIJ7MpDDGe1S', '6673401', 'johncena.jpg'),
(6, 'William Daniels', 'wdaniel@gmail.com', 'Parent', '$2y$10$l4qEZwZHFE3a0W5hdcSya.5iqhCUMIieJErGCetPGZFGf775X1BPm', '6798187473', 'William.jpg'),
(7, 'Jayden Monroe', 'jaydenM@gmail.com', 'Caregiver', '$2y$10$2KQHzKjaa3dAwWXdgqVv6OQFzTfBbTw8lJtM3nTQQA4rO5JuVQQ7y', 'N/A', 'jayden.jpg'),
(8, 'Liana May', 'anamay@gmail.com', 'Parent', '$2y$10$8kQv6UpvczdiNjjpJgMZneIhpie0/SFvmC4jsoxQd0RWwEyCE7MTy', '35679876', 'liana.jpg'),
(16, 'Liam Hemsworth', 'liam@gmail.com', 'Caregiver', '$2y$10$/7w.4DsITSDJap0I/DrCXuPDLQhCmiNLTFSoBgHutIc0u7tE4ei9G', 'N/A', 'liam.jpg'),
(18, 'Will Smith', 'willsmith@gmail.com', 'Caregiver', '$2y$10$G8DCs7v3KreOKrJQvK/0QuQ2iGM.NOTkIA7XaXO8i1BY1xjKQbDNS', '6673401', 'will-smith.jpg'),
(19, 'Annie Willows', 'annwillow@gmail.com', 'Parent', '$2y$10$VphVBg/zPP.FvLH3BnS9POWNPnzvV1eFvcSEMMQQb3XjghBf/hO5C', '8399424', 'liana.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity`
--
ALTER TABLE `activity`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `child_id` (`child_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `child_id` (`child_id`);

--
-- Indexes for table `bill_payment`
--
ALTER TABLE `bill_payment`
  ADD PRIMARY KEY (`bill_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `caregivers`
--
ALTER TABLE `caregivers`
  ADD PRIMARY KEY (`caregiver_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `child`
--
ALTER TABLE `child`
  ADD PRIMARY KEY (`child_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `child_caregiver`
--
ALTER TABLE `child_caregiver`
  ADD PRIMARY KEY (`child_id`,`caregiver_id`),
  ADD KEY `caregiver_id` (`caregiver_id`);

--
-- Indexes for table `learning`
--
ALTER TABLE `learning`
  ADD PRIMARY KEY (`learning_id`),
  ADD KEY `child_id` (`child_id`);

--
-- Indexes for table `meal`
--
ALTER TABLE `meal`
  ADD PRIMARY KEY (`meal_id`),
  ADD KEY `child_id` (`child_id`),
  ADD KEY `bill_id` (`bill_id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `child_id` (`child_id`);

--
-- Indexes for table `pickdrop`
--
ALTER TABLE `pickdrop`
  ADD PRIMARY KEY (`pickdrop_id`),
  ADD KEY `child_id` (`child_id`);

--
-- Indexes for table `specialcare`
--
ALTER TABLE `specialcare`
  ADD PRIMARY KEY (`special_care_id`),
  ADD KEY `child_id` (`child_id`),
  ADD KEY `bill_id` (`bill_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity`
--
ALTER TABLE `activity`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `bill_payment`
--
ALTER TABLE `bill_payment`
  MODIFY `bill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `caregivers`
--
ALTER TABLE `caregivers`
  MODIFY `caregiver_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `child`
--
ALTER TABLE `child`
  MODIFY `child_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `learning`
--
ALTER TABLE `learning`
  MODIFY `learning_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `meal`
--
ALTER TABLE `meal`
  MODIFY `meal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pickdrop`
--
ALTER TABLE `pickdrop`
  MODIFY `pickdrop_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `specialcare`
--
ALTER TABLE `specialcare`
  MODIFY `special_care_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity`
--
ALTER TABLE `activity`
  ADD CONSTRAINT `activity_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `child` (`child_id`);

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `child` (`child_id`);

--
-- Constraints for table `bill_payment`
--
ALTER TABLE `bill_payment`
  ADD CONSTRAINT `bill_payment_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `child`
--
ALTER TABLE `child`
  ADD CONSTRAINT `child_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `child_caregiver`
--
ALTER TABLE `child_caregiver`
  ADD CONSTRAINT `child_caregiver_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `child` (`child_id`),
  ADD CONSTRAINT `child_caregiver_ibfk_2` FOREIGN KEY (`caregiver_id`) REFERENCES `caregivers` (`caregiver_id`);

--
-- Constraints for table `learning`
--
ALTER TABLE `learning`
  ADD CONSTRAINT `learning_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `child` (`child_id`);

--
-- Constraints for table `meal`
--
ALTER TABLE `meal`
  ADD CONSTRAINT `meal_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `child` (`child_id`),
  ADD CONSTRAINT `meal_ibfk_2` FOREIGN KEY (`bill_id`) REFERENCES `bill_payment` (`bill_id`);

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `notification_ibfk_2` FOREIGN KEY (`child_id`) REFERENCES `child` (`child_id`);

--
-- Constraints for table `pickdrop`
--
ALTER TABLE `pickdrop`
  ADD CONSTRAINT `pickdrop_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `child` (`child_id`);

--
-- Constraints for table `specialcare`
--
ALTER TABLE `specialcare`
  ADD CONSTRAINT `specialcare_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `child` (`child_id`),
  ADD CONSTRAINT `specialcare_ibfk_2` FOREIGN KEY (`bill_id`) REFERENCES `bill_payment` (`bill_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
