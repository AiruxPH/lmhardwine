-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 30, 2026 at 10:23 AM
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
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'admin',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$bv/10uJDsxjFRk9w1RYO5ObrrXUukeVmVxprOyooVPlKGNIzUEoa2', 'super_admin', '2026-01-20 15:53:25');

--
-- Triggers `admins`
--
DELIMITER $$
CREATE TRIGGER `before_admin_delete` BEFORE DELETE ON `admins` FOR EACH ROW BEGIN
    IF OLD.role = 'super_admin' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The Super Admin account cannot be deleted.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_admin_insert` BEFORE INSERT ON `admins` FOR EACH ROW BEGIN
    IF NEW.role = 'super_admin' AND (SELECT COUNT(*) FROM admins WHERE role = 'super_admin') >= 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Only one Super Admin is allowed.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_admin_update` BEFORE UPDATE ON `admins` FOR EACH ROW BEGIN
    IF NEW.role = 'super_admin' AND OLD.role != 'super_admin' AND (SELECT COUNT(*) FROM admins WHERE role = 'super_admin') >= 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Only one Super Admin is allowed.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-01-25 09:26:39', '2026-01-25 09:26:39');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_profiles`
--

CREATE TABLE `customer_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `default_shipping_address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_profiles`
--

INSERT INTO `customer_profiles` (`id`, `user_id`, `full_name`, `phone_number`, `default_shipping_address`) VALUES
(1, 1, 'Anecito Randy E. Calunod Jr.', '09168811468', 'Bañadero, Ozamiz, Misamis Occidental, Northern Mindanao, 7200, Philippines'),
(2, 3, 'john', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_address` text NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'Pending',
  `is_deleted` tinyint(1) DEFAULT 0,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `customer_email`, `customer_address`, `total_amount`, `order_date`, `status`, `is_deleted`, `user_id`) VALUES
(1, 'Anecito Randy E. Calunod Jr.', 'randythegreat000@gmail.com', 'Bañadero, Ozamiz, Misamis Occidental, Northern Mindanao, 7200, Philippines', 750.00, '2026-01-25 10:26:53', 'Delivered', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `order_history`
--

CREATE TABLE `order_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status_from` varchar(50) DEFAULT NULL,
  `status_to` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `changed_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_history`
--

INSERT INTO `order_history` (`id`, `order_id`, `status_from`, `status_to`, `notes`, `changed_at`) VALUES
(1, 1, 'Shipped', 'Pending', 'Pending', '2026-01-25 11:02:33'),
(2, 1, 'Pending', 'Processing', 'The item has been processed.', '2026-01-25 11:22:03'),
(3, 1, 'Processing', 'Shipped', 'The item had left the stock room.', '2026-01-25 11:22:43'),
(4, 1, 'Shipped', 'Delivered', 'The item has been delivered.', '2026-01-25 11:23:04');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `price_at_purchase` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price_at_purchase`, `quantity`, `is_deleted`) VALUES
(1, 1, 8, 'whiskey', 750.00, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `otp_code` varchar(10) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `color_style` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `stock_qty` int(11) DEFAULT 10,
  `seller_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `type`, `varietal`, `price`, `vintage_year`, `description`, `color_style`, `image_path`, `is_deleted`, `stock_qty`, `seller_id`) VALUES
(1, 'Crimson Impact', 'Red', 'Cabernet Sauvignon', 89.00, 2024, 'A full-bodied giant with notes of dark cherry, leather, and smoked oak.', 'linear-gradient(45deg, rgba(114, 14, 30, 0.1), transparent)', NULL, 0, 10, NULL),
(2, 'Midnight Reserve', 'Red', 'Syrah Blend', 120.00, 2022, 'Velvety texture meets intense spice. Aged in charred barrels.', 'linear-gradient(45deg, rgba(80, 80, 80, 0.1), transparent)', NULL, 0, 10, NULL),
(3, 'Liquid Gold', 'White', 'Chardonnay', 95.00, 2023, 'Unexpectedly crisp with a steel backbone. Notes of granite and lemon zest.', 'linear-gradient(45deg, rgba(212, 175, 55, 0.1), transparent)', NULL, 0, 10, NULL),
(4, 'Obsidian Rose', 'Rose', 'Grenache', 75.00, 2024, 'Dry, tart, and dangerously drinkable. Not your average summer water.', 'linear-gradient(45deg, rgba(255, 105, 180, 0.1), transparent)', NULL, 0, 10, NULL),
(5, 'Volcanic Ash', 'Red', 'Pinot Noir', 110.00, 2021, 'Grown in volcanic soil, earthy and complex with a smokey finish.', 'linear-gradient(45deg, rgba(100, 30, 22, 0.1), transparent)', NULL, 0, 10, NULL),
(6, 'Frost Bite', 'White', 'Ice Wine', 150.00, 2023, 'Sweetness with a sharp edge. Harvested at the first deep freeze.', 'linear-gradient(45deg, rgba(200, 240, 255, 0.1), transparent)', NULL, 0, 10, NULL),
(7, 'Smirnoff', 'Red', 'Smirnoff Red, White & Merry is a limited edition, seasonal flavored vodka with festive notes of cran', 500.00, 2023, 'Smirnoff is the world\'s No. 1 vodka, triple distilled and ten times filtered for a smooth, versatile and gold award-winning taste.', 'linear-gradient(45deg, rgba(114, 14, 30, 0.1), transparent)', 'wine_1768923022.webp', 0, 10, 2),
(8, 'whiskey', 'White', 'Whiskey varieties are defined by origin, grain type, and production method, with major types includi', 750.00, 2024, 'Whiskey (or whisky) is a spirit distilled from fermented grain mash (like barley, corn, rye, wheat) and matured in wooden barrels, typically oak, giving it complex flavors from sweet (vanilla, caramel) to spicy (cinnamon) or earthy (peat).', 'linear-gradient(45deg, rgba(212, 175, 55, 0.1), transparent)', 'uploads/wine_6975ccfc83197.jpg', 0, 9, 2),
(9, 'Smirnoff', 'Red', 'Smirnoff Red, White & Merry is a limited edition, seasonal flavored vodka with festive notes of cran', 500.00, 2026, '', 'linear-gradient(45deg, rgba(114, 14, 30, 0.1), transparent)', NULL, 1, 10, 2);

-- --------------------------------------------------------

--
-- Table structure for table `seller_profiles`
--

CREATE TABLE `seller_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `brand_name` varchar(100) NOT NULL,
  `brand_description` text DEFAULT NULL,
  `brand_logo_path` varchar(255) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `seller_profiles`
--

INSERT INTO `seller_profiles` (`id`, `user_id`, `brand_name`, `brand_description`, `brand_logo_path`, `contact_email`) VALUES
(1, 2, 'LM Hardwine', 'Best brand', 'uploads/logos/brand_6975b5ea55b23.png', 'lyndy@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('customer','seller') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `created_at`, `updated_at`) VALUES
(1, 'AiruxPH', 'randythegreat000@gmail.com', 'RandyBOY999999@@@', 'customer', '2026-01-25 05:44:20', '2026-01-25 08:18:01'),
(2, 'Lyndy', 'lyndy@gmail.com', 'NDR0dSy8h5', 'seller', '2026-01-25 06:08:10', '2026-01-25 06:08:10'),
(3, 'dog', 'john@gmail.com', 'lylyndymae2003@', 'customer', '2026-01-25 08:00:30', '2026-01-25 08:00:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_profiles`
--
ALTER TABLE `customer_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_customer` (`user_id`);

--
-- Indexes for table `order_history`
--
ALTER TABLE `order_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_seller` (`seller_id`);

--
-- Indexes for table `seller_profiles`
--
ALTER TABLE `seller_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_profiles`
--
ALTER TABLE `customer_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_history`
--
ALTER TABLE `order_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `seller_profiles`
--
ALTER TABLE `seller_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_profiles`
--
ALTER TABLE `customer_profiles`
  ADD CONSTRAINT `customer_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_customer` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_history`
--
ALTER TABLE `order_history`
  ADD CONSTRAINT `order_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `seller_profiles`
--
ALTER TABLE `seller_profiles`
  ADD CONSTRAINT `seller_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
