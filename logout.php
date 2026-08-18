<?php
/**
 * Scottish Mammal Observations Database - Admin Logout Handler
 * Securely destroys session parameters
 *
 * SET08101 Web Technologies Coursework
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

session_destroy();

header('Location: index.php');
exit;
