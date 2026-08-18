<?php
/**
 * Configuration file for the Scottish Mammal Observations Database
 * SET08101 Web Technologies Coursework
 */

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

/**
 * Load environment variables from a .env file if it exists.
 */
function loadEnv(string $path): void {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            $value = trim($value, "\"'");
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Load environment variables from project root
loadEnv(__DIR__ . '/../.env');

// Database configuration with local environment fallbacks using $_ENV/$_SERVER (for shared hosting compatibility)
define('DB_HOST', $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? 'scottish_mammals');
define('DB_USER', $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? $_SERVER['DB_CHARSET'] ?? 'utf8mb4');

/**
 * HTML escape helper function
 * Always use this when outputting user data or database content to HTML
 *
 * @param string $string The string to escape
 * @return string The escaped string safe for HTML output
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
