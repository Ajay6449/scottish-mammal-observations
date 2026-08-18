-- SQL Schema for Scottish Mammal Observations Platform
-- MySQL 8.0+ / MariaDB compatible

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS observations;
DROP TABLE IF EXISTS species;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Species Table
CREATE TABLE species (
    id INT AUTO_INCREMENT PRIMARY KEY,
    common_name VARCHAR(100) NOT NULL,
    scientific_name VARCHAR(100) UNIQUE NOT NULL,
    habitat VARCHAR(100) NOT NULL,
    conservation_status VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    diet VARCHAR(100) NOT NULL,
    lifespan VARCHAR(50) NOT NULL,
    average_weight VARCHAR(50) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_common_name (common_name),
    INDEX idx_habitat (habitat)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Observations Table
CREATE TABLE observations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    species_id INT NOT NULL,
    observer_name VARCHAR(100) NOT NULL,
    observation_date DATE NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    location_name VARCHAR(255) NOT NULL,
    notes TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
    observation_type ENUM('imported', 'user_submitted') NOT NULL DEFAULT 'user_submitted',
    source_dataset VARCHAR(255) NULL,
    source_record_id VARCHAR(100) NULL,
    source_url VARCHAR(255) NULL,
    licence VARCHAR(100) NULL,
    data_provider VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (species_id) REFERENCES species(id) ON DELETE CASCADE,
    INDEX idx_observation_date (observation_date),
    INDEX idx_species_id (species_id),
    INDEX idx_location (location_name(100)),
    INDEX idx_observation_type (observation_type),
    UNIQUE KEY uq_source_record (source_record_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
