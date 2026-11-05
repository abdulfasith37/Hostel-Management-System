CREATE DATABASE IF NOT EXISTS `hostel_db`;
USE `hostel_db`;

CREATE TABLE `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL
);

-- Default login: admin / Admin@123
INSERT INTO `admins` (`username`, `password`) VALUES 
('admin', '$2y$10$wvjGVdQ76V4bsVjh7IZjjurDXKQrIPpm5UIYz6fV7EpxXWoCfa9O6');

CREATE TABLE `hostels` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `gender_type` ENUM('Male', 'Female') NOT NULL
);

CREATE TABLE `blocks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `hostel_id` INT NOT NULL,
    `block_name` VARCHAR(50) NOT NULL,
    FOREIGN KEY (`hostel_id`) REFERENCES `hostels`(`id`) ON DELETE CASCADE
);

CREATE TABLE `rooms` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `block_id` INT NOT NULL,
    `room_no` VARCHAR(20) NOT NULL,
    `floor_no` INT NOT NULL,
    `capacity` INT NOT NULL DEFAULT 2,
    FOREIGN KEY (`block_id`) REFERENCES `blocks`(`id`) ON DELETE CASCADE
);

CREATE TABLE `beds` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `room_id` INT NOT NULL,
    `bed_label` VARCHAR(20) NOT NULL,
    `is_occupied` TINYINT(1) DEFAULT 0,
    FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
);

CREATE TABLE `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `reg_no` VARCHAR(50) NOT NULL UNIQUE,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `gender` ENUM('Male', 'Female') NOT NULL,
    `batch_year` INT NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `status` ENUM('Active', 'Vacated') DEFAULT 'Active'
);

CREATE TABLE `allocations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL UNIQUE,
    `bed_id` INT NOT NULL UNIQUE,
    `allocated_at` DATE NOT NULL,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`bed_id`) REFERENCES `beds`(`id`) ON DELETE CASCADE
);

CREATE TABLE `complaints` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `category` ENUM('Plumbing', 'Electrical', 'Furniture', 'Other') NOT NULL,
    `description` TEXT NOT NULL,
    `status` ENUM('Pending', 'In Progress', 'Resolved') DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
);

-- Seed initial records
INSERT INTO `hostels` (`name`, `gender_type`) VALUES 
('Boys Hostel', 'Male'),
('Girls Hostel', 'Female');

INSERT INTO `blocks` (`hostel_id`, `block_name`) VALUES 
(1, 'Block A');

INSERT INTO `rooms` (`block_id`, `room_no`, `floor_no`, `capacity`) VALUES 
(1, '01', 1, 4), (1, '02', 1, 4), (1, '03', 1, 4);

INSERT INTO `beds` (`room_id`, `bed_label`, `is_occupied`) VALUES 
(1, 'Bed 01-A', 1), (1, 'Bed 01-B', 0),
(1, 'Bed 01-C', 0),(1, 'Bed 01-D', 0);

INSERT INTO `students` (`reg_no`, `first_name`, `last_name`, `gender`, `batch_year`, `phone`, `email`) VALUES 
('2023/ICT/001', 'Kamal', 'Perera', 'Male', 2023, '0771234567', 'kamal@uni.lk'),
('2023/ASP/001', 'Nimal', 'Silva', 'Male', 2023, '0719876543', 'nimal@uni.lk');

INSERT INTO `allocations` (`student_id`, `bed_id`, `allocated_at`) VALUES 
(1, 1, '2026-01-15');