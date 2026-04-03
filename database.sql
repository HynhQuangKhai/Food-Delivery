-- ============================================================
-- Food Delivery System - Database Structure
-- XAMPP/MySQL
-- Run this SQL in phpMyAdmin or MySQL console
-- ============================================================

-- Create database
CREATE DATABASE IF NOT EXISTS food_delivery;
USE food_delivery;

-- ============================================================
-- Table: users
-- Stores user information for authentication
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,  -- Store hashed password
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- Table: food_items
-- Stores available food items for ordering
-- ============================================================
CREATE TABLE IF NOT EXISTS food_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(50),
    available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- Table: orders
-- Stores order information
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    food_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    total_price DECIMAL(10, 2) NOT NULL,
    delivery_address TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',  -- pending, confirmed, delivered, cancelled
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (food_item_id) REFERENCES food_items(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS vouchers (...);
INSERT INTO vouchers (code, discount_percent, expiry_date, status) 
VALUES ('SAVE10', 10.00, '2026-12-31 23:59:59', 'active');

-- ============================================================
-- Insert Sample Data
-- ============================================================

-- Insert sample users (password: 'password123' hashed with MD5 for demo)
-- In production, use password_hash() in PHP
INSERT INTO users (username, password, email, full_name, phone, address) VALUES
('john_doe', MD5('password123'), 'john@example.com', 'John Doe', '0123456789', '123 Main St, City'),
('jane_smith', MD5('password123'), 'jane@example.com', 'Jane Smith', '0987654321', '456 Oak Ave, Town'),
('demo_user', MD5('demo123'), 'demo@example.com', 'Demo User', '5555555555', '789 Pine Rd, Village');

-- Insert sample food items
INSERT INTO food_items (name, description, price, category) VALUES
('Margherita Pizza', 'Classic tomato and mozzarella pizza', 12.99, 'Pizza'),
('Pepperoni Pizza', 'Pizza with pepperoni toppings', 14.99, 'Pizza'),
('Cheeseburger', 'Beef patty with cheese, lettuce, and tomato', 9.99, 'Burger'),
('Chicken Burger', 'Grilled chicken with mayo and lettuce', 10.99, 'Burger'),
('Caesar Salad', 'Romaine lettuce with Caesar dressing and croutons', 8.99, 'Salad'),
('Greek Salad', 'Fresh vegetables with feta cheese and olives', 9.99, 'Salad'),
('Spaghetti Bolognese', 'Pasta with meat sauce', 13.99, 'Pasta'),
('Chicken Alfredo', 'Creamy pasta with grilled chicken', 15.99, 'Pasta'),
('Sushi Roll', 'Fresh salmon and avocado roll', 11.99, 'Sushi'),
('Tempura', 'Deep-fried shrimp and vegetables', 10.99, 'Japanese');
