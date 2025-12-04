-- Customer & Real-Time Trading Management System Database Schema
-- Database: fortestt_freelance

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Table structure for `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'staff') DEFAULT 'staff',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `customers`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `categories`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `products`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `category_id` INT DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `deals`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `deals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `deal_number` VARCHAR(50) NOT NULL UNIQUE,
  `customer_id` INT NOT NULL,
  `deal_date` DATE NOT NULL,
  `status` ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
  `total_buy_amount` DECIMAL(15, 2) DEFAULT 0.00,
  `total_sell_amount` DECIMAL(15, 2) DEFAULT 0.00,
  `profit` DECIMAL(15, 2) DEFAULT 0.00,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `deal_items`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `deal_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `deal_id` INT NOT NULL,
  `product_id` INT DEFAULT NULL,
  `product_name` VARCHAR(150) NOT NULL,
  `buy_quantity` DECIMAL(10, 2) NOT NULL DEFAULT 1,
  `buy_price` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `total_buy` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `sell_quantity` DECIMAL(10, 2) NOT NULL DEFAULT 1,
  `sell_price` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `total_sell` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `profit` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`deal_id`) REFERENCES `deals`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `payments`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `deal_id` INT NOT NULL,
  `customer_id` INT NOT NULL,
  `amount` DECIMAL(15, 2) NOT NULL,
  `payment_method` ENUM('cash', 'online', 'bank', 'other') DEFAULT 'cash',
  `payment_date` DATE NOT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`deal_id`) REFERENCES `deals`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `expense_categories`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `expense_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `expenses`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `expenses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT DEFAULT NULL,
  `amount` DECIMAL(15, 2) NOT NULL,
  `expense_date` DATE NOT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`category_id`) REFERENCES `expense_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `other_income`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `other_income` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `description` VARCHAR(255) NOT NULL,
  `amount` DECIMAL(15, 2) NOT NULL,
  `income_date` DATE NOT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Insert default admin user (password: admin123)
-- ⚠️ IMPORTANT: Change this password immediately after deployment!
-- --------------------------------------------------------
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- --------------------------------------------------------
-- Insert sample expense categories
-- --------------------------------------------------------
INSERT INTO `expense_categories` (`name`) VALUES
('Rent'),
('Internet'),
('Transport'),
('Utilities'),
('Office Supplies'),
('Marketing'),
('Other');

-- --------------------------------------------------------
-- Insert sample product categories
-- --------------------------------------------------------
INSERT INTO `categories` (`name`, `description`) VALUES
('Electronics', 'Electronic devices and accessories'),
('Clothing', 'Apparel and fashion items'),
('Home & Garden', 'Home improvement and garden products'),
('Food & Beverages', 'Food items and drinks'),
('Other', 'Miscellaneous items');

-- --------------------------------------------------------
-- Insert sample products
-- --------------------------------------------------------
INSERT INTO `products` (`name`, `category_id`, `notes`) VALUES
('iPhone 15', 1, 'Apple smartphone'),
('Samsung Galaxy S24', 1, 'Samsung flagship phone'),
('MacBook Pro', 1, 'Apple laptop'),
('Nike Air Max', 2, 'Sports shoes'),
('Levi\'s Jeans', 2, 'Denim jeans'),
('Sony Headphones', 1, 'Wireless headphones'),
('Coffee Maker', 3, 'Automatic coffee machine'),
('Green Tea Pack', 4, 'Organic green tea');

-- --------------------------------------------------------
-- Insert sample customers
-- --------------------------------------------------------
INSERT INTO `customers` (`name`, `phone`, `email`, `address`, `notes`) VALUES
('John Smith', '+1234567890', 'john@example.com', '123 Main Street, New York', 'Regular customer'),
('Jane Doe', '+1987654321', 'jane@example.com', '456 Oak Avenue, Los Angeles', 'VIP customer'),
('Bob Wilson', '+1122334455', 'bob@example.com', '789 Pine Road, Chicago', 'New customer'),
('Alice Johnson', '+1555666777', 'alice@example.com', '321 Elm Street, Houston', 'Business client'),
('Charlie Brown', '+1888999000', 'charlie@example.com', '654 Maple Drive, Phoenix', NULL);

-- --------------------------------------------------------
-- Insert sample deals
-- --------------------------------------------------------
INSERT INTO `deals` (`deal_number`, `customer_id`, `deal_date`, `status`, `total_buy_amount`, `total_sell_amount`, `profit`, `notes`) VALUES
('DEAL-0001', 1, CURDATE(), 'completed', 800.00, 999.00, 199.00, 'iPhone purchase'),
('DEAL-0002', 2, CURDATE(), 'completed', 1200.00, 1499.00, 299.00, 'MacBook purchase'),
('DEAL-0003', 3, CURDATE(), 'pending', 150.00, 199.00, 49.00, 'Headphones order'),
('DEAL-0004', 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'completed', 500.00, 650.00, 150.00, 'Samsung Galaxy'),
('DEAL-0005', 4, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'completed', 75.00, 110.00, 35.00, 'Nike shoes');

-- --------------------------------------------------------
-- Insert sample deal items
-- --------------------------------------------------------
INSERT INTO `deal_items` (`deal_id`, `product_id`, `product_name`, `buy_quantity`, `buy_price`, `total_buy`, `sell_quantity`, `sell_price`, `total_sell`, `profit`) VALUES
(1, 1, 'iPhone 15', 1, 800.00, 800.00, 1, 999.00, 999.00, 199.00),
(2, 3, 'MacBook Pro', 1, 1200.00, 1200.00, 1, 1499.00, 1499.00, 299.00),
(3, 6, 'Sony Headphones', 1, 150.00, 150.00, 1, 199.00, 199.00, 49.00),
(4, 2, 'Samsung Galaxy S24', 1, 500.00, 500.00, 1, 650.00, 650.00, 150.00),
(5, 4, 'Nike Air Max', 1, 75.00, 75.00, 1, 110.00, 110.00, 35.00);

-- --------------------------------------------------------
-- Insert sample payments
-- --------------------------------------------------------
INSERT INTO `payments` (`deal_id`, `customer_id`, `amount`, `payment_method`, `payment_date`, `notes`) VALUES
(1, 1, 500.00, 'cash', CURDATE(), 'Partial payment'),
(2, 2, 1499.00, 'bank', CURDATE(), 'Full payment'),
(4, 1, 650.00, 'online', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Full payment'),
(5, 4, 50.00, 'cash', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Partial payment');

-- --------------------------------------------------------
-- Insert sample expenses
-- --------------------------------------------------------
INSERT INTO `expenses` (`category_id`, `amount`, `expense_date`, `notes`) VALUES
(1, 500.00, CURDATE(), 'Monthly rent'),
(2, 50.00, CURDATE(), 'Internet bill'),
(3, 30.00, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Fuel cost'),
(5, 25.00, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Printer paper');
