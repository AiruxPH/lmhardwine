-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 19, 2026 at 04:25 PM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u130348899_lmhardwine`
--

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `varietal` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `vintage_year` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `color_style` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `type`, `varietal`, `price`, `vintage_year`, `description`, `color_style`) VALUES
(1, 'Crimson Impact', 'Red', 'Cabernet Sauvignon', 89.00, 2024, 'A full-bodied giant with notes of dark cherry, leather, and smoked oak.', 'linear-gradient(45deg, rgba(114, 14, 30, 0.1), transparent)'),
(2, 'Midnight Reserve', 'Red', 'Syrah Blend', 120.00, 2022, 'Velvety texture meets intense spice. Aged in charred barrels.', 'linear-gradient(45deg, rgba(80, 80, 80, 0.1), transparent)'),
(3, 'Liquid Gold', 'White', 'Chardonnay', 95.00, 2023, 'Unexpectedly crisp with a steel backbone. Notes of granite and lemon zest.', 'linear-gradient(45deg, rgba(212, 175, 55, 0.1), transparent)'),
(4, 'Obsidian Rose', 'Rose', 'Grenache', 75.00, 2024, 'Dry, tart, and dangerously drinkable. Not your average summer water.', 'linear-gradient(45deg, rgba(255, 105, 180, 0.1), transparent)'),
(5, 'Volcanic Ash', 'Red', 'Pinot Noir', 110.00, 2021, 'Grown in volcanic soil, earthy and complex with a smokey finish.', 'linear-gradient(45deg, rgba(100, 30, 22, 0.1), transparent)'),
(6, 'Frost Bite', 'White', 'Ice Wine', 150.00, 2023, 'Sweetness with a sharp edge. Harvested at the first deep freeze.', 'linear-gradient(45deg, rgba(200, 240, 255, 0.1), transparent)');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
