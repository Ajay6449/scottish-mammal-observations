<?php
/**
 * Database Migration - Create Users Table and Seed Admin
 * SET08101 Web Technologies Coursework
 */

require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = getDbConnection();
    
    // Create users table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `email` VARCHAR(100) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    // Seed the admin user if not already present
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    $stmt->execute(['admin']);
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
        // Bcrypt hash for 'Highlands2026!'
        $hash = password_hash('Highlands2026!', PASSWORD_BCRYPT, ['cost' => 12]);
        $insert->execute(['admin', 'shaikbashah20@gmail.com', $hash]);
        echo "Successfully created and seeded 'users' table with admin account.\n";
    } else {
        echo "Users table already seeded.\n";
    }
} catch (Exception $e) {
    die("Users table migration failed: " . $e->getMessage() . "\n");
}
