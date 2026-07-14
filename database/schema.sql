-- Database schema for Zee Tech Foundation campaign site

-- CREATE DATABASE IF NOT EXISTS donate_device CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
-- USE donate_device;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','donor','applicant','partner') NOT NULL DEFAULT 'donor',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS donations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    city VARCHAR(120) NOT NULL,
    device_type VARCHAR(60) NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    condition_notes TEXT,
    handover_preference VARCHAR(40) NOT NULL,
    terms_agreed TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    age INT UNSIGNED NOT NULL,
    email VARCHAR(180) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    applicant_type VARCHAR(80) NOT NULL,
    organisation VARCHAR(180),
    reason TEXT NOT NULL,
    location VARCHAR(180) NOT NULL,
    terms_agreed TINYINT(1) NOT NULL DEFAULT 1,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS partners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organisation_name VARCHAR(180) NOT NULL,
    website VARCHAR(180),
    contact_name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    partnership_type VARCHAR(80) NOT NULL,
    message TEXT,
    terms_agreed TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL,
    subject VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
