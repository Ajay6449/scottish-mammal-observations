<?php
/**
 * Database connection file
 * SET08101 Web Technologies Coursework
 */

require_once __DIR__ . '/config.php';

/**
 * Get a PDO database connection
 *
 * @return PDO The database connection object
 */
function getDbConnection() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In production, log this error instead of displaying it
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    return $pdo;
}
