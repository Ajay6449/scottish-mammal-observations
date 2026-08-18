<?php
/**
 * Database Configuration and Connection Handler
 * Uses PDO for secure prepared statements.
 */

/**
 * Load environment variables from a .env file if it exists.
 * 
 * @param string $path Path to the .env file
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
            // Remove optional quotes surrounding value
            $value = trim($value, "\"'");
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Load environment variables from project root
loadEnv(__DIR__ . '/../../.env');

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'scottish_mammals');
define('DB_USER', getenv('DB_USER') ?: 'mammals_user');
define('DB_PASS', getenv('DB_PASS') ?: 'ScotWild2026!');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

/**
 * Returns a PDO database connection instance.
 * Catches connection errors and displays a user-friendly message.
 * 
 * @return PDO
 */
function getDBConnection(): PDO {
    static $pdo = null;
    
    if ($pdo !== null) {
        return $pdo;
    }
    
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (\PDOException $e) {
        // Log the error message internally (e.g. error_log($e->getMessage()))
        // Display a clean, generic error message to the user for security.
        http_response_code(500);
        die("
            <!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Database Connection Error | Scottish Mammal Observations</title>
                <style>
                    body {
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                        background: #faf8f5;
                        color: #1a1917;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        height: 100vh;
                        margin: 0;
                    }
                    .error-container {
                        background: #fff;
                        padding: 2.5rem;
                        border-radius: 8px;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
                        border-top: 4px solid #1e3f20;
                        max-width: 480px;
                        text-align: center;
                    }
                    h1 { color: #1e3f20; font-size: 1.5rem; margin-top: 0; }
                    p { color: #8c8780; line-height: 1.6; }
                    .btn {
                        display: inline-block;
                        background: #1e3f20;
                        color: #fff;
                        padding: 0.75rem 1.5rem;
                        text-decoration: none;
                        border-radius: 4px;
                        margin-top: 1.5rem;
                        font-weight: bold;
                    }
                </style>
            </head>
            <body>
                <div class='error-container'>
                    <h1>System Maintenance</h1>
                    <p>We are currently experiencing technical difficulties connecting to our database. Please try again shortly.</p>
                    <p><small>If you are the administrator, please check your database service and config credentials.</small></p>
                </div>
            </body>
            </html>
        ");
    }
}
