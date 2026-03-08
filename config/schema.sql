-- FundACause Database Schema
-- Run this SQL to set up the database

CREATE DATABASE IF NOT EXISTS fundacause CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fundacause;

-- Admin users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    status ENUM('active', 'pending', 'disabled') NOT NULL DEFAULT 'pending',
    recovery_token VARCHAR(64) DEFAULT NULL,
    recovery_expires DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Campaigns table
CREATE TABLE IF NOT EXISTS campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hex_id VARCHAR(8) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    beneficiary_name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    goal_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    raised_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    supporter_count INT NOT NULL DEFAULT 0,
    paypal_email VARCHAR(255) NOT NULL,
    status ENUM('active', 'paused', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_hex_id (hex_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Campaign media (images/videos)
CREATE TABLE IF NOT EXISTS campaign_media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type ENUM('image', 'video') DEFAULT 'image',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    INDEX idx_campaign (campaign_id)
) ENGINE=InnoDB;

-- Donations table
CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    donor_name VARCHAR(100) DEFAULT 'Anonymous',
    donor_email VARCHAR(255) DEFAULT NULL,
    amount DECIMAL(10,2) NOT NULL,
    paypal_transaction_id VARCHAR(100) DEFAULT NULL,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    INDEX idx_campaign (campaign_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, role, status) VALUES
('admin', 'admin@fundacause.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');
